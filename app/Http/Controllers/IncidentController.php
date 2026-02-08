<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\IncidentImage;
use App\Models\IncidentHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class IncidentController extends Controller
{
    // =========================================================
    // 1. VIEW ALL REPORTS
    // =========================================================
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Incident::with('images')->latest();

        if ($user->role === 'clerk') {
            $query->where('status', 'Case Closed');
        } 
        elseif ($user->role !== 'admin') {
            $query->where('reported_by', $user->name);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('reported_by', 'like', "%{$search}%");
            });
        }

        $incidents = $query->paginate(10)->appends($request->all());

        return view('incidents', compact('incidents'));
    }

    // =========================================================
    // 2. SAVE NEW REPORT
    // =========================================================
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'title' => 'required',
            'date' => 'required',
            'time' => 'required',
            'barangay' => 'required',
            'street_address' => 'required',
            'description' => 'required',
            'evidence.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        DB::transaction(function () use ($request) {
            $stage = $request->stage ?? 'SIR';
            $combinedLocation = $request->street_address . ', ' . $request->barangay;

            $incident = Incident::create([
                'type' => $request->type,
                'stage' => $stage,
                'title' => $request->title,
                'location' => $combinedLocation,
                'incident_date' => $request->date . ' ' . $request->time,
                'description' => $request->description,
                'status' => 'Pending',
                'reported_by' => Auth::user()->name ?? 'Officer',
                'alarm_level' => 'Low',
            ]);

            $imagePaths = [];

            if ($request->hasFile('evidence')) {
                foreach ($request->file('evidence') as $file) {
                    $path = $file->store('evidence', 'public');
                    IncidentImage::create([
                        'incident_id' => $incident->id,
                        'image_path' => $path,
                    ]);
                    $imagePaths[] = $path;
                }
            }

            IncidentHistory::create([
                'incident_id' => $incident->id,
                'stage' => $stage,
                'description' => $incident->description,
                'title' => $incident->title,
                'type' => $incident->type,
                'location' => $combinedLocation,
                'incident_date' => $incident->incident_date,
                'reported_by' => $incident->reported_by,
                'images' => $imagePaths,
            ]);
        });

        return back()->with('success', 'Report created successfully!');
    }

    // =========================================================
    // 3. SHOW SINGLE REPORT
    // =========================================================
    public function show($id)
    {
        $incident = Incident::with(['history', 'images'])->findOrFail($id);
        
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->role !== 'clerk' && $incident->reported_by !== $user->name) {
            abort(403, 'Unauthorized access to this report.');
        }

        return view('incidents.show', compact('incident'));
    }

    // =========================================================
    // 4. WORKFLOW STATUS UPDATES
    // =========================================================
    public function updateStatus(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);

        if ($incident->status === 'Case Closed') {
            return back()->with('error', 'This case is already finalized and closed.');
        }

        if ($request->action === 'return') {
            $incident->update([
                'status' => 'Returned',
                'admin_remarks' => $request->remarks,
            ]);
            return back()->with('error', 'Report returned to officer for revision.');
        }

        if ($request->action === 'approve') {
            DB::transaction(function () use ($incident) {
                // Ensure current stage is captured in history before moving
                IncidentHistory::updateOrCreate(
                    [
                        'incident_id' => $incident->id,
                        'stage' => $incident->stage
                    ],
                    [
                        'description' => $incident->description,
                        'title' => $incident->title,
                        'type' => $incident->type,
                        'location' => $incident->location,
                        'incident_date' => $incident->incident_date,
                        'reported_by' => $incident->reported_by,
                        'images' => $incident->images->pluck('image_path')->toArray(),
                    ]
                );

                $nextStage = $incident->stage;
                $status = 'Pending';

                if ($incident->stage === 'SIR') {
                    $nextStage = 'PIR';
                } elseif ($incident->stage === 'PIR') {
                    $nextStage = 'FIR';
                } elseif ($incident->stage === 'FIR') {
                    $status = 'Case Closed';
                } elseif ($incident->stage === 'MDFI') {
                    $status = 'Case Closed';
                }

                $incident->update([
                    'stage' => $nextStage,
                    'status' => $status,
                    'admin_remarks' => null,
                ]);
            });

            return back()->with('success', $incident->status === 'Case Closed' 
                ? 'Report finalized and Case Closed.' 
                : 'Stage approved. Moved to ' . $incident->stage . '.');
        }
    }

    // =========================================================
    // 5. UPDATE EXISTING REPORT (CORRECTED LOCKING LOGIC)
    // =========================================================
    public function update(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $incident->reported_by !== Auth::user()->name) {
             abort(403, 'Unauthorized action.');
        }

        // 1. Validation Rules
        $rules = [
            'title' => 'required',
            'date' => 'required',
            'time' => 'required',
            'description' => 'required',
            'evidence.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ];

        // Only require Location/Type if the stage is SIR or MDFI (Initial stages)
        // If stage is PIR or FIR, we ignore these inputs anyway, so validation isn't needed
        if (in_array($incident->stage, ['SIR', 'MDFI'])) {
            $rules['type'] = 'required';
            $rules['barangay'] = 'required';
            $rules['street_address'] = 'required';
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $incident) {
            
            // 2. Base Update Data (Fields that are ALWAYS editable)
            $updateData = [
                'title' => $request->title,
                'incident_date' => $request->date . ' ' . $request->time,
                'description' => $request->description,
                'admin_remarks' => null,
            ];

            // 3. Conditional Update (LOCKING LOGIC)
            // Only allow updating 'location' and 'type' if we are in the initial SIR or MDFI stage.
            // Once in PIR or FIR, these fields are LOCKED and the user inputs are ignored.
            if (in_array($incident->stage, ['SIR', 'MDFI'])) {
                $combinedLocation = $request->street_address . ', ' . $request->barangay;
                $updateData['location'] = $combinedLocation;
                $updateData['type'] = $request->type; // <--- MOVED INSIDE THE IF BLOCK
            }

            if ($incident->status === 'Returned') {
                $updateData['status'] = 'Pending';
            }
            
            // Allow stage switching only between SIR <-> MDFI
            if ($request->has('stage') && in_array($incident->stage, ['SIR', 'MDFI'])) {
                 $updateData['stage'] = $request->stage;
            }

            // 4. Update Database
            $incident->update($updateData);

            // 5. Handle Evidence
            if ($request->hasFile('evidence')) {
                foreach ($request->file('evidence') as $file) {
                    $path = $file->store('evidence', 'public');
                    IncidentImage::create([
                        'incident_id' => $incident->id,
                        'image_path' => $path,
                    ]);
                }
            }

            // 6. Sync Timeline
            IncidentHistory::updateOrCreate(
                [
                    'incident_id' => $incident->id,
                    'stage' => $incident->stage
                ],
                [
                    'title' => $incident->title,
                    'type' => $incident->type,
                    'location' => $incident->location,
                    'description' => $incident->description,
                    'incident_date' => $incident->incident_date,
                    'reported_by' => $incident->reported_by,
                    'images' => $incident->images()->pluck('image_path')->toArray(),
                ]
            );
        });

        return back()->with('success', 'Report updated and resubmitted.');
    }

    // =========================================================
    // 6. IMPORT CSV
    // =========================================================
    public function import(Request $request)
    {
        set_time_limit(0); 

        $request->validate(['file' => 'required|mimes:csv,txt']);

        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        array_shift($rows); 

        $allowedTypes = ['Structural', 'Non-Structural', 'Vehicular'];

        DB::transaction(function () use ($rows, $allowedTypes) {
            foreach ($rows as $row) {
                if (count($row) < 6) continue;

                $type = trim($row[0]); 

                if (!in_array($type, $allowedTypes)) continue; 

                $title       = $row[1];
                $date        = $row[2];
                $time        = $row[3];
                $location    = $row[4];
                $description = $row[5];
                $stage       = $row[6] ?? 'SIR';
                $status      = $row[7] ?? 'Pending';

                $incident = Incident::create([
                    'type'          => $type,
                    'title'         => $title,
                    'incident_date' => $date . ' ' . $time,
                    'location'      => $location,
                    'description'   => $description,
                    'stage'         => $stage,
                    'status'        => $status,
                    'reported_by'   => Auth::user()->name ?? 'System Import',
                    'alarm_level'   => 'Low',
                ]);

                $workflow = ['SIR', 'PIR', 'FIR'];
                foreach ($workflow as $step) {
                    IncidentHistory::create([
                        'incident_id'   => $incident->id,
                        'stage'         => $step,
                        'description'   => $description,
                        'title'         => $title,
                        'type'          => $type,
                        'location'      => $location,
                        'incident_date' => $incident->incident_date,
                        'reported_by'   => 'Historical Import',
                        'images'        => [],
                    ]);

                    if ($step === $stage) break;
                }

                if ($stage === 'MDFI' && !in_array('MDFI', $workflow)) {
                     IncidentHistory::create([
                        'incident_id'   => $incident->id,
                        'stage'         => 'MDFI',
                        'description'   => $description,
                        'title'         => $title,
                        'type'          => $type,
                        'location'      => $location,
                        'incident_date' => $incident->incident_date,
                        'reported_by'   => 'Historical Import',
                        'images'        => [],
                    ]);
                }
            }
        });

        return back()->with('success', 'Import completed! Only Structural, Non-Structural, and Vehicular incidents were saved.');
    }

    // =========================================================
    // 7. DOWNLOAD PDF
    // =========================================================
    public function download(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);
        $userRole = Auth::user()->role;

        if (!in_array($userRole, ['admin', 'clerk']) && $incident->reported_by !== Auth::user()->name) {
            abort(403, 'Unauthorized access.');
        }

        if ($request->has('history_id')) {
            $data = IncidentHistory::findOrFail($request->history_id);
            $images = $data->images ?? [];
            $viewData = $data;
            $filename = 'Report_History_' . $data->stage . '_' . $incident->id . '.pdf';
        } else {
            if ($incident->status !== 'Case Closed') {
                abort(403, 'Report is not yet finalized.');
            }
            $images = $incident->images->pluck('image_path')->toArray();
            $viewData = $incident;
            $filename = 'Official_Report_INC-' . $incident->id . '.pdf';
        }

        $pdf = Pdf::loadView('incidents.pdf', [
            'incident' => $viewData,
            'imagePaths' => $images,
        ]);

        return $pdf->download($filename);
    }
}