<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with('uploader')->latest();

        // Server-side filtering
        if ($request->has('category') && $request->category != 'all') {
            $query->where('category', $request->category);
        }
        
        // Search functionality
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Sorting
        if ($request->has('sort')) {
            if ($request->sort == 'oldest') {
                $query->oldest();
            } elseif ($request->sort == 'az') {
                $query->orderBy('title', 'asc');
            } else {
                $query->latest();
            }
        }

        $documents = $query->get();
        
        // Get counts for sidebar badges (Removed Training & Announcement)
        $counts = [
            'all' => Document::count(),
            'memo' => Document::where('category', 'memo')->count(),
            'policy' => Document::where('category', 'policy')->count(),
            'circular' => Document::where('category', 'circular')->count(),
            'sop' => Document::where('category', 'sop')->count(),
        ];

        return view('documents', compact('documents', 'counts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'file_upload' => 'required|file|max:10240', // Max 10MB
        ]);

        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents', $filename, 'public');
            
            Document::create([
                'title' => $request->title,
                'category' => $request->category,
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'description' => $request->description,
                'uploaded_by' => Auth::id(),
            ]);

            return redirect()->back()->with('success', 'Document uploaded successfully!');
        }

        return redirect()->back()->with('error', 'File upload failed.');
    }

    public function download(Document $document)
    {
        $document->increment('downloads');
        return Storage::disk('public')->download($document->file_path);
    }
    
    public function preview(Document $document)
    {
        $path = storage_path('app/public/' . $document->file_path);
        
        if (!file_exists($path)) {
            abort(404);
        }
        
        return response()->file($path);
    }
}