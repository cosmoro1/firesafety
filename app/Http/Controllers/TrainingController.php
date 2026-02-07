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

        // Get Results (Latest scheduled date first)
        $trainings = $query->orderBy('date_conducted', 'desc')->paginate(10);

        return view('training', compact('trainings'));
    }

    // --- 2. STORE NEW SEMINAR ---
    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role, ['admin', 'clerk'])) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_id'   => 'required|string|max:50',
            'industry_type'=> 'required|string|in:Commercial,Industrial',
            'representative_name' => 'required|string|max:255',
            'representative_email' => 'required|email|max:255',
            'topic' => 'required|string|max:255',
            // VALIDATION: Prevents selecting a past date
            'date_conducted' => 'required|date|after_or_equal:today', 
            'attendees_count' => 'required|integer|min:1',
        ], [
            'date_conducted.after_or_equal' => 'You cannot schedule a seminar in the past. Please select today or a future date.'
        ]);

        $validated['status'] = 'Scheduled';

        Training::create($validated);

        return redirect()->route('training.index')
            ->with('success', 'New seminar scheduled successfully!');
    }

    // --- 3. SEND EMAIL ---
    public function sendEmail(Request $request, Training $training)
    {
        // Increase timeout for file uploads/email sending (5 minutes)
        set_time_limit(300); 

        if (!in_array(auth()->user()->role, ['admin', 'clerk'])) {
            abort(403, 'Unauthorized access.');
        }

        // 1. Validate Files
        $request->validate([
            'certificate_files' => 'required', 
            'certificate_files.*' => 'file|mimes:pdf,jpg,png,jpeg|max:10240', // 10MB Max per file
        ]);

        // 2. Determine Recipient Email
        // Uses the one from the form first; falls back to database record if form is empty
        $recipientEmail = $request->input('representative_email') ?: $training->representative_email;

        if (empty($recipientEmail)) {
            return back()->with('error', 'No email address found for this representative. Please edit the record first.');
        }

        // 3. Process Files for Attachment
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
        try {
            Mail::to($recipientEmail)->send(
                new TrainingCertificate($training, $filesData)
            );

            // 5. Update Status & Save Email if missing
            $updateData = ['status' => 'Issued'];
            
            if(empty($training->representative_email)) {
                $updateData['representative_email'] = $recipientEmail;
            }
            
            $training->update($updateData);

            return back()->with('success', 'Certificates emailed successfully to ' . $recipientEmail);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
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
            // VALIDATION: Prevents selecting a past date during edit
            'date_conducted' => 'required|date|after_or_equal:today',
            'attendees_count' => 'required|integer|min:1',
            'status' => 'required|string', 
        ], [
            'date_conducted.after_or_equal' => 'You cannot move a seminar to a past date.'
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