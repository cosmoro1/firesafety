<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Storage; 
use App\Models\IncidentImage;
use App\Models\IncidentHistory;

class IncidentController extends Controller
{
    // 1. VIEW ALL REPORTS
    public function index(Request $request)
    {
        $query = Incident::with('images')->latest();

        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $incidents = $query->paginate(10);
        $incidents->appends($request->all());

        return view('incidents', compact('incidents'));
    }

    // 2. SAVE NEW REPORT
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
            
            // A. Create the Incident
            $incident = Incident::create([
                'type' => $request->type,
                'stage' => $request->stage ?? 'SIR',
                'title' => $request->title,
                'location' => $request->location,
                'incident_date' => $request->date . ' ' . $request->time,
                'description' => $request->description,
                'status' => 'Pending',
                'reported_by' => Auth::user()->name ?? 'Officer',
                'alarm_level' => 'Low',
            ]);

            // B. Handle File Uploads
            $imagePaths = []; 
            
            if ($request->hasFile('evidence')) {
                foreach ($request->file('evidence') as $file) {
                    $path = $file->store('evidence', 'public');
                    
                    IncidentImage::create([
                        'incident_id' => $incident->id,
                        'image_path' => $path
                    ]);
                    
                    $imagePaths[] = $path; 
                }
            }

            // C. Create INITIAL History Snapshot
            IncidentHistory::create([
                'incident_id' => $incident->id,
                'stage' => $incident->stage ?? 'SIR',
                'description' => $incident->description,
                'title' => $incident->title,
                'type' => $incident->type,
                'location' => $incident->location,
                'incident_date' => $incident->incident_date,
                'reported_by' => $incident->reported_by,
                'images' => $imagePaths, 
            ]);
        });

        return redirect()->back()->with('success', 'Report created and initial snapshot saved!');
    }

    // 3. WORKFLOW STATUS UPDATES
    public function updateStatus(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);
        $action = $request->input('action');

        // A. Return to Officer
        if ($action === 'return') {
            $incident->update([
                'status' => 'Returned',
                'admin_remarks' => $request->input('remarks'),
            ]);
            return redirect()->back()->with('error', 'Report returned to officer for revision.');
        }

        // B. Approve (Advance Stage)
        if ($action === 'approve') {
            
            DB::transaction(function () use ($incident) {
                // Snapshot History
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

                // Determine Next Stage
                $currentStage = $incident->stage;
                $nextStage = $currentStage;
                $status = 'Pending';

                if ($currentStage === 'SIR') {
                    $nextStage = 'PIR';
                } elseif ($currentStage === 'PIR') {
                    $nextStage = 'FIR';
                } elseif ($currentStage === 'FIR' || $currentStage === 'MDFI') {
                    $status = 'Case Closed';
                }

                $incident->update([
                    'status' => $status,
                    'stage' => $nextStage,
                    'admin_remarks' => null,
                ]);
            });

            return redirect()->back()->with('success', "Stage approved. Moved to " . $incident->stage . ".");
        }
    }

    // 4. UPDATE EXISTING REPORT
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'date' => 'required',
            'time' => 'required',
            'location' => 'required',
            'type' => 'required',
            'description' => 'required',
            'evidence.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', 
        ]);

        $incident = Incident::findOrFail($id);

        DB::transaction(function () use ($request, $incident) {
            
            $updateData = [
                'title' => $request->title,
                'incident_date' => $request->date . ' ' . $request->time,
                'location' => $request->location,
                'type' => $request->type,
                'description' => $request->description,
            ];

            // Reset status if it was "Returned"
            if ($incident->status === 'Returned') {
                $updateData['status'] = 'Pending';
                $updateData['admin_remarks'] = null;
            }

            $incident->update($updateData);

            if ($request->hasFile('evidence')) {
                foreach ($request->file('evidence') as $file) {
                    $path = $file->store('evidence', 'public');
                    IncidentImage::create([
                        'incident_id' => $incident->id,
                        'image_path' => $path
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Report updated and resubmitted for review!');
    }

    // 5. IMPORT CSV (NEW METHOD)
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        
        // Read CSV
        $fileData = array_map('str_getcsv', file($file->getRealPath()));
        
        // Remove Header
        if (count($fileData) > 0) {
            array_shift($fileData); 
        }

        DB::transaction(function () use ($fileData) {
            foreach ($fileData as $row) {
                // Expected Columns: 
                // 0:Type, 1:Title, 2:Date(Y-m-d), 3:Time(H:i), 4:Location, 5:Description, 6:Status(Optional)
                
                if (count($row) < 6) continue;

                $type = $row[0];
                $title = $row[1];
                $date = $row[2];
                $time = $row[3];
                $location = $row[4];
                $description = $row[5];
                $status = isset($row[6]) && !empty($row[6]) ? $row[6] : 'Case Closed';
                
                // Create Incident
                $incident = Incident::create([
                    'type' => $type,
                    'stage' => 'FIR', // Assume historical data is finalized
                    'title' => $title,
                    'location' => $location,
                    'incident_date' => $date . ' ' . $time,
                    'description' => $description,
                    'status' => $status,
                    'reported_by' => Auth::user()->name ?? 'System Import',
                    'alarm_level' => 'Low',
                ]);

                // Create History Snapshot (Required for Timeline)
                IncidentHistory::create([
                    'incident_id' => $incident->id,
                    'stage' => 'FIR',
                    'description' => $description,
                    'title' => $title,
                    'type' => $type,
                    'location' => $location,
                    'incident_date' => $incident->incident_date,
                    'reported_by' => 'System Import',
                    'images' => [], 
                ]);
            }
        });

        return redirect()->back()->with('success', 'Historical Incidents Imported Successfully!');
    }
}