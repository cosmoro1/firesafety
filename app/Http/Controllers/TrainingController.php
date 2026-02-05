<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Training;
use Illuminate\Support\Facades\Mail;
use App\Mail\TrainingCertificate;

class TrainingController extends Controller
{
    // --- 1. DISPLAY LIST & SEARCH ---
    public function index(Request $request)
    {
        // Security Check
        if (!in_array(auth()->user()->role, ['admin', 'clerk'])) {
            abort(403, 'Unauthorized access.');
        }

        // Start Query
        $query = Training::query();

        // Search Logic
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('representative_name', 'like', "%{$search}%")
                  ->orWhere('company_id', 'like', "%{$search}%");
            });
        }

        // Industry Filter
        if ($request->has('industry') && $request->industry != 'All Industries') {
            $query->where('industry_type', $request->industry);
        }

        // Get Results
        $trainings = $query->latest('date_conducted')->paginate(10);

        return view('training', compact('trainings'));
    }

    // --- 2. STORE NEW SEMINAR ---
    public function store(Request $request)
    {
        // Security Check
        if (!in_array(auth()->user()->role, ['admin', 'clerk'])) {
            abort(403, 'Unauthorized access.');
        }

        // Validate
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_id'   => 'required|string|max:50',
            'industry_type'=> 'required|string|in:Commercial,Industrial',
            'representative_name' => 'required|string|max:255',
            'representative_email' => 'required|email|max:255',
            'representative_position' => 'nullable|string|max:255',
            'topic' => 'required|string|max:255',
            'date_conducted' => 'required|date',
            'attendees_count' => 'required|integer|min:1',
        ]);

        $validated['status'] = 'Scheduled';

        Training::create($validated);

        return redirect()->route('training.index')
            ->with('success', 'New seminar scheduled successfully!');
    }

    // --- 3. SEND EMAIL (UPDATED FOR MULTIPLE FILES) ---
    public function sendEmail(Request $request, Training $training)
    {
        // Security Check
        if (!in_array(auth()->user()->role, ['admin', 'clerk'])) {
            abort(403, 'Unauthorized access.');
        }

        // 1. Validate Array of Files
        $request->validate([
            'certificate_files' => 'required', 
            'certificate_files.*' => 'file|mimes:pdf,jpg,png,jpeg|max:5120',
        ]);

        // 2. Check if email exists
        if (!$training->representative_email) {
            return back()->with('error', 'No email address found for this representative.');
        }

        // 3. Process Files
        $filesData = [];
        if($request->hasFile('certificate_files')) {
            foreach($request->file('certificate_files') as $file) {
                $filesData[] = [
                    'path' => $file->getRealPath(),
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                ];
            }
        }

        // 4. Send Email
        Mail::to($training->representative_email)->send(
            new TrainingCertificate($training, $filesData)
        );

        // 5. Update Status
        $training->update(['status' => 'Issued']);

        return back()->with('success', 'Certificates emailed successfully to ' . $training->representative_email);
    }

    // --- 4. UPDATE SEMINAR ---
    public function update(Request $request, Training $training)
    {
        if (!in_array(auth()->user()->role, ['admin', 'clerk'])) {
            abort(403);
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_id'   => 'required|string|max:50',
            'industry_type'=> 'required|string|in:Commercial,Industrial',
            'representative_name' => 'required|string|max:255',
            'representative_email' => 'required|email|max:255',
            'topic' => 'required|string|max:255',
            'date_conducted' => 'required|date',
            'attendees_count' => 'required|integer|min:1',
            'status' => 'required|string', 
        ]);

        $training->update($validated);

        return redirect()->route('training.index')
            ->with('success', 'Seminar details updated successfully.');
    }

    // --- 5. DELETE SEMINAR ---
    public function destroy(Training $training)
    {
        if (!in_array(auth()->user()->role, ['admin', 'clerk'])) {
            abort(403);
        }

        $training->delete();

        return redirect()->route('training.index')
            ->with('success', 'Seminar record deleted successfully.');
    }
}