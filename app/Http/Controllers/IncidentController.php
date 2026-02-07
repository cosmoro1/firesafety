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
    // 1. VIEW ALL REPORTS
    public function index(Request $request)
    {
        $query = Incident::with('images')->latest();
        $userRole = Auth::user()->role;

        if ($userRole === 'clerk') {
            $query->where('status', 'Case Closed');
        } elseif ($request->filled('status') && $request->status !== 'all') {
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

        $incidents = $query->paginate(10);
        $incidents->appends($request->all());

        return view('incidents', compact('incidents'));
    }

    // 2. SAVE NEW REPORT (FIXED)
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'title' => 'required',
            'date' => 'required',
            'time' => 'required',
            'location' => 'required',
            'description' => 'required',
            'evidence.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        DB::transaction(function () use ($request) {
            // FIX: Use $request->stage instead of hardcoding 'SIR'
            $stage = $request->stage ?? 'SIR';

            $incident = Incident::create([
                'type' => $request->type,
                'stage' => $stage, // <--- CHANGED THIS
                'title' => $request->title,
                'location' => $request->location,
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
                'stage' => $stage, // <--- CHANGED THIS TO MATCH
                'description' => $incident->description,
                'title' => $incident->title,
                'type' => $incident->type,
                'location' => $incident->location,
                'incident_date' => $incident->incident_date,
                'reported_by' => $incident->reported_by,
                'images' => $imagePaths,
            ]);
        });

        return back()->with('success', 'Report created successfully!');
    }

    // 3. SHOW SINGLE REPORT
    public function show($id)
    {
        $incident = Incident::with(['history', 'images'])->findOrFail($id);
        return view('incidents.show', compact('incident'));
    }

    // 4. WORKFLOW STATUS UPDATES
    // 4. WORKFLOW STATUS UPDATES
public function updateStatus(Request $request, $id)
{
    $incident = Incident::findOrFail($id);

    // Prevent any changes if the case is already closed
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
            // Save Snapshot of CURRENT state before moving forward
            IncidentHistory::create([
                'incident_id' => $incident->id,
                'stage' => $incident->stage,
                'description' => $incident->description,
                'title' => $incident->title,
                'type' => $incident->type,
                'location' => $incident->location,
                'incident_date' => $incident->incident_date,
                'reported_by' => $incident->reported_by,
                'images' => $incident->images->pluck('image_path')->toArray(),
            ]);

            $nextStage = $incident->stage;
            $status = 'Pending';

            // Logic: SIR -> PIR -> FIR -> Closed
            if ($incident->stage === 'SIR') {
                $nextStage = 'PIR';
            } elseif ($incident->stage === 'PIR') {
                $nextStage = 'FIR';
            } elseif ($incident->stage === 'FIR') {
                $status = 'Case Closed'; // Finalize here
            } elseif ($incident->stage === 'MDFI') {
                $status = 'Case Closed'; // MDFI also closes immediately
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

    // 5. UPDATE EXISTING REPORT
    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required',
            'title' => 'required',
            'date' => 'required',
            'time' => 'required',
            'location' => 'required',
            'description' => 'required',
            'evidence.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $incident = Incident::findOrFail($id);

        DB::transaction(function () use ($request, $incident) {
            $updateData = [
                'type' => $request->type,
                'title' => $request->title,
                'incident_date' => $request->date . ' ' . $request->time,
                'location' => $request->location,
                'description' => $request->description,
                'admin_remarks' => null,
            ];

            // If updating, respect the current stage logic
            // If it was 'Returned', set back to 'Pending' for re-approval
            if ($incident->status === 'Returned') {
                $updateData['status'] = 'Pending';
            }
            
            // Allow stage change only if it was SIR or MDFI (Initial stages)
            if ($request->has('stage') && in_array($incident->stage, ['SIR', 'MDFI'])) {
                 $updateData['stage'] = $request->stage;
            }

            $incident->update($updateData);

            if ($request->hasFile('evidence')) {
                foreach ($request->file('evidence') as $file) {
                    $path = $file->store('evidence', 'public');
                    IncidentImage::create([
                        'incident_id' => $incident->id,
                        'image_path' => $path,
                    ]);
                }
            }
        });

        return back()->with('success', 'Report updated and resubmitted.');
    }

   // 6. IMPORT CSV (Fixed: Matches Manual Entry Defaults)
   public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);

        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        array_shift($rows); 

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                if (count($row) < 6) continue;

                $type        = $row[0];
                $title       = $row[1];
                $date        = $row[2];
                $time        = $row[3];
                $location    = $row[4];
                $description = $row[5];
                $stage       = $row[6] ?? 'SIR';
                $status      = $row[7] ?? 'Pending';

                // 1. Create the Incident
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

                // 2. BACKFILL HISTORY LOGS
                // Define the sequence of stages
                $workflow = ['SIR', 'PIR', 'FIR'];
                
                foreach ($workflow as $step) {
                    // Create history for this step
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

                    // Stop once we have reached the stage defined in the CSV
                    if ($step === $stage) {
                        break;
                    }
                }

                // Special handling for MDFI which is outside the SIR/PIR/FIR loop
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

        return back()->with('success', 'Incidents imported with complete historical timelines.');
    }

    // 7. DOWNLOAD PDF
    public function download(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);
        $userRole = Auth::user()->role;

        if (!in_array($userRole, ['admin', 'clerk'])) {
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