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
            $incident = Incident::create([
                'type' => $request->type,
                'stage' => 'SIR',
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
                'stage' => 'SIR',
                'description' => $incident->description,
                'title' => $incident->title,
                'type' => $incident->type,
                'location' => $incident->location,
                'incident_date' => $incident->incident_date,
                'reported_by' => $incident->reported_by,
                'images' => $imagePaths,
            ]);
        });

        return back()->with('success', 'Report created and initial snapshot saved!');
    }

    // 3. SHOW SINGLE REPORT
    public function show($id)
    {
        $incident = Incident::with(['history', 'images'])->findOrFail($id);
        return view('incidents.show', compact('incident'));
    }

    // 4. WORKFLOW STATUS UPDATES
    public function updateStatus(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);

        if ($request->action === 'return') {
            $incident->update([
                'status' => 'Returned',
                'admin_remarks' => $request->remarks,
            ]);

            return back()->with('error', 'Report returned to officer for revision.');
        }

        if ($request->action === 'approve') {
            DB::transaction(function () use ($incident) {
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

                if ($incident->stage === 'SIR') {
                    $nextStage = 'PIR';
                } elseif ($incident->stage === 'PIR') {
                    $nextStage = 'FIR';
                } elseif (in_array($incident->stage, ['FIR', 'MDFI'])) {
                    $status = 'Case Closed';
                }

                $incident->update([
                    'stage' => $nextStage,
                    'status' => $status,
                    'admin_remarks' => null,
                ]);
            });

            return back()->with('success', 'Stage approved and progressed.');
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
            $incident->update([
                'type' => $request->type,
                'title' => $request->title,
                'incident_date' => $request->date . ' ' . $request->time,
                'location' => $request->location,
                'description' => $request->description,
                'status' => $incident->status === 'Returned' ? 'Pending' : $incident->status,
                'admin_remarks' => null,
            ]);

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

    // 6. IMPORT CSV
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);

        $rows = array_map('str_getcsv', file($request->file->getRealPath()));
        array_shift($rows);

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                if (count($row) < 6) continue;

                $incident = Incident::create([
                    'type' => $row[0],
                    'title' => $row[1],
                    'incident_date' => $row[2] . ' ' . $row[3],
                    'location' => $row[4],
                    'description' => $row[5],
                    'stage' => 'FIR',
                    'status' => 'Case Closed',
                    'reported_by' => Auth::user()->name ?? 'System Import',
                    'alarm_level' => 'Low',
                ]);

                IncidentHistory::create([
                    'incident_id' => $incident->id,
                    'stage' => 'FIR',
                    'description' => $row[5],
                    'title' => $row[1],
                    'type' => $row[0],
                    'location' => $row[4],
                    'incident_date' => $incident->incident_date,
                    'reported_by' => 'System Import',
                    'images' => [],
                ]);
            }
        });

        return back()->with('success', 'Historical incidents imported successfully!');
    }

    // 7. DOWNLOAD PDF (FINAL or HISTORY) ✅ UPDATED
    public function download(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);
        $userRole = Auth::user()->role;

        // Security Check
        if (!in_array($userRole, ['admin', 'clerk'])) {
            abort(403, 'Unauthorized access.');
        }

        // HISTORY VERSION
        if ($request->has('history_id')) {
            $data = IncidentHistory::findOrFail($request->history_id);

            $images = $data->images ?? [];
            $viewData = $data;
            $filename = 'Report_History_' . $data->stage . '_' . $incident->id . '.pdf';
        }
        // FINAL VERSION
        else {
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
