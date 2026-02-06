<x-layout>
    {{-- BREADCRUMB & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-xs"></i>
                <a href="{{ route('incidents.index') }}" class="hover:text-blue-600">Incidents</a>
                <i class="fa-solid fa-chevron-right text-xs"></i>
                <span>Case #{{ str_pad($incident->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <h2 class="text-2xl font-bold text-slate-900">Incident Report Details</h2>
        </div>
        
        <div class="flex items-center gap-3">
            {{-- EXPORT BUTTON (Dynamic ID added) --}}
            <a id="exportPdfBtn" href="{{ route('incidents.download', $incident->id) }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold px-4 py-2 rounded-lg text-sm shadow-sm transition flex items-center">
                <i class="fa-solid fa-file-pdf mr-2 text-red-500"></i> <span id="exportBtnText">Export Current PDF</span>
            </a>
            <a href="{{ route('incidents.index') }}" class="bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold px-4 py-2 rounded-lg text-sm transition">
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- LEFT COLUMN: MAIN REPORT --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- STATUS CARD --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden transition-all" id="mainStatusCard">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800" id="viewTitle">Current Case Status</h3>
                    
                    {{-- STATUS BADGE (Dynamic ID added) --}}
                    <span id="statusBadge" class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border bg-slate-100 text-slate-600">
                        {{ $incident->status }}
                    </span>
                </div>
                <div class="p-6 grid grid-cols-2 gap-6">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Date Reported</label>
                        <p id="dateReported" class="text-slate-800 font-semibold">{{ $incident->created_at->format('F d, Y') }}</p>
                        <p id="timeReported" class="text-slate-500 text-sm">{{ $incident->created_at->format('h:i A') }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Incident Type</label>
                        <div class="flex items-center gap-2">
                            {{-- ICON CONTAINER (Dynamic ID added) --}}
                            <div id="typeIconContainer" class="h-6 w-6 rounded-full {{ $incident->type == 'Structural' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center text-xs">
                                <i id="typeIcon" class="fa-solid {{ $incident->type == 'Structural' ? 'fa-house-fire' : 'fa-fire' }}"></i>
                            </div>
                            <span id="incidentType" class="font-bold text-slate-800">{{ $incident->type }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DETAILS CARD --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Incident Details</h3>
                    {{-- RESET BUTTON (Hidden by default, shows when viewing history) --}}
                    <button id="resetViewBtn" onclick="resetToCurrent()" class="hidden text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline">
                        Return to Current View
                    </button>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase block mb-2">Location</label>
                        <div class="flex items-start bg-slate-50 p-3 rounded-lg border border-slate-100">
                            <i class="fa-solid fa-location-dot text-red-500 mt-1 mr-3"></i>
                            <span id="locationText" class="text-slate-700 font-medium">{{ $incident->location }}</span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase block mb-2">Description</label>
                        <div id="descriptionText" class="bg-slate-50 p-4 rounded-lg border border-slate-100 text-slate-700 leading-relaxed text-sm min-h-[80px]">
                            {{ $incident->description ?? 'No detailed description provided.' }}
                        </div>
                    </div>

                    {{-- PHOTOS CONTAINER --}}
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase block mb-2">Evidence Photos</label>
                        <div id="photoContainer" class="grid grid-cols-2 gap-4">
                            {{-- Photos will be injected here via JS --}}
                            @if($incident->images && $incident->images->count() > 0)
                                @foreach($incident->images as $img)
                                <div class="rounded-lg overflow-hidden border border-slate-200">
                                    <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-48 object-cover hover:scale-105 transition-transform">
                                </div>
                                @endforeach
                            @else
                                <p class="text-sm text-slate-400 italic">No photos available for this version.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: HISTORY & META --}}
        <div class="space-y-6">
            
            {{-- REPORTER INFO --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-sm">Reported By</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold border border-blue-200">
                            {{ substr($incident->reported_by ?? 'O', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ $incident->reported_by }}</p>
                            <p class="text-xs text-slate-500">Official Encoder</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- INTERACTIVE TIMELINE --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800 text-sm">Investigation History</h3>
                    <span class="bg-slate-200 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded-md">{{ $incident->history->count() }} Versions</span>
                </div>
                <div class="p-6">
                    <p class="text-xs text-slate-400 mb-4 font-medium italic">Click a stage below to view its details.</p>
                    
                    <ol class="relative border-l border-slate-200 ml-2 space-y-6">
                        {{-- 1. CURRENT LIVE VERSION --}}
                        <li class="ml-6 cursor-pointer group" onclick="resetToCurrent()">
                            <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-4 ring-white bg-green-100 text-green-600 shadow-sm group-hover:bg-green-200 transition">
                                <i class="fa-solid fa-circle-dot text-[10px]"></i>
                            </span>
                            <div class="bg-white p-3 rounded-lg border border-green-200 shadow-sm group-hover:border-green-400 transition">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-bold text-slate-800 text-xs uppercase">Current (Live)</h3>
                                    <span class="text-[10px] bg-green-100 text-green-700 px-1.5 rounded font-bold">NOW</span>
                                </div>
                            </div>
                        </li>

                        {{-- 2. HISTORY LOOP --}}
                        @foreach($incident->history as $history)
                        <li class="ml-6 cursor-pointer group" onclick="loadHistoryVersion({{ $history->id }})">
                            <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-4 ring-white bg-slate-100 text-slate-400 shadow-sm group-hover:bg-blue-100 group-hover:text-blue-500 transition">
                                <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                            </span>
                            
                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 group-hover:bg-white group-hover:border-blue-300 group-hover:shadow-md transition-all">
                                <div class="flex justify-between items-start mb-1">
                                    <h3 class="font-bold text-slate-700 text-xs uppercase group-hover:text-blue-700">{{ $history->stage }} Report</h3>
                                    <i class="fa-solid fa-eye text-slate-300 group-hover:text-blue-500 text-xs"></i>
                                </div>
                                <p class="text-xs text-slate-500 mb-2">Updated {{ $history->created_at->format('M d, h:i A') }}</p>
                                
                                <div class="text-xs text-slate-600 italic border-l-2 border-slate-200 pl-2 group-hover:border-blue-200">
                                    "{{ Str::limit($history->description, 40) }}"
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>

        </div>
    </div>

    {{-- INTERACTIVE SCRIPT --}}
    <script>
        // 1. PREPARE DATA FROM LARAVEL
        const incidentId = {{ $incident->id }};
        const storagePath = "{{ asset('storage') }}/";

        // Current Live Data
        const currentData = {
            id: null, // null means current
            status: "{{ $incident->status }}",
            created_at: "{{ $incident->created_at->format('F d, Y') }}",
            time_at: "{{ $incident->created_at->format('h:i A') }}",
            type: "{{ $incident->type }}",
            location: "{{ $incident->location }}",
            description: `{!! addslashes($incident->description) !!}`,
            // Map live collection to array of paths to match history structure
            images: @json($incident->images->pluck('image_path'))
        };

        // History Data (Collection)
        const historyData = @json($incident->history);

        // Status Colors Map
        const statusColors = {
            'Pending': 'bg-orange-100 text-orange-700 border-orange-200',
            'Verified': 'bg-blue-100 text-blue-700 border-blue-200',
            'Resolved': 'bg-green-100 text-green-700 border-green-200',
            'Case Closed': 'bg-slate-800 text-white border-slate-900',
            'Returned': 'bg-red-100 text-red-700 border-red-200',
            'default': 'bg-slate-100 text-slate-600'
        };

        // 2. FUNCTION TO LOAD VERSION
        function loadHistoryVersion(historyId) {
            // Find the history object
            const data = historyData.find(h => h.id == historyId);
            if (!data) return;

            // Update UI Text
            updateUI(data, true);

            // Update Export Link
            const exportBtn = document.getElementById('exportPdfBtn');
            const exportText = document.getElementById('exportBtnText');
            exportBtn.href = `/incidents/${incidentId}/download?history_id=${historyId}`;
            exportBtn.className = "bg-blue-50 border border-blue-300 text-blue-700 hover:bg-blue-100 font-semibold px-4 py-2 rounded-lg text-sm shadow-sm transition flex items-center"; // Change style to show it's history
            exportText.innerText = `Export ${data.stage} Version`;

            // Show Reset Button
            document.getElementById('resetViewBtn').classList.remove('hidden');
            
            // Highlight active visual (optional, visual cue only)
            document.getElementById('viewTitle').innerText = `Historical View: ${data.stage}`;
            document.getElementById('viewTitle').className = "font-bold text-blue-700";
        }

        // 3. FUNCTION TO RESET
        function resetToCurrent() {
            updateUI(currentData, false);

            // Reset Export Link
            const exportBtn = document.getElementById('exportPdfBtn');
            const exportText = document.getElementById('exportBtnText');
            exportBtn.href = `/incidents/${incidentId}/download`; // No query param = current
            exportBtn.className = "bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold px-4 py-2 rounded-lg text-sm shadow-sm transition flex items-center";
            exportText.innerText = "Export Current PDF";

            // Hide Reset Button
            document.getElementById('resetViewBtn').classList.add('hidden');
            
            // Reset Title
            document.getElementById('viewTitle').innerText = "Current Case Status";
            document.getElementById('viewTitle').className = "font-bold text-slate-800";
        }

        // 4. HELPER TO UPDATE DOM
        function updateUI(data, isHistory) {
            // Text Fields
            document.getElementById('dateReported').innerText = formatDate(data.incident_date || data.created_at); // Handle date variance
            document.getElementById('incidentType').innerText = data.type;
            document.getElementById('locationText').innerText = data.location;
            document.getElementById('descriptionText').innerText = data.description;

            // Status Badge (History usually doesn't save 'status', uses 'stage' as proxy or logic)
            // If it's history, we display the STAGE instead of Status
            const badge = document.getElementById('statusBadge');
            const statusText = isHistory ? (data.stage + ' Record') : data.status;
            badge.innerText = statusText;
            
            // Reset Badge Classes
            badge.className = "px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border ";
            if(isHistory) {
                badge.classList.add('bg-blue-100', 'text-blue-600', 'border-blue-200');
            } else {
                badge.className += (statusColors[data.status] || statusColors['default']);
            }

            // Type Icon
            const iconContainer = document.getElementById('typeIconContainer');
            const icon = document.getElementById('typeIcon');
            if (data.type === 'Structural') {
                iconContainer.className = "h-6 w-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs";
                icon.className = "fa-solid fa-house-fire";
            } else {
                iconContainer.className = "h-6 w-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs";
                icon.className = "fa-solid fa-fire";
            }

            // Photos
            const photoContainer = document.getElementById('photoContainer');
            photoContainer.innerHTML = ''; // Clear existing

            let images = data.images || []; // History images are array, Current images are array of paths
            
            if (images.length > 0) {
                images.forEach(path => {
                    const div = document.createElement('div');
                    div.className = "rounded-lg overflow-hidden border border-slate-200 animate-fade-in";
                    div.innerHTML = `<img src="${storagePath}${path}" class="w-full h-48 object-cover hover:scale-105 transition-transform">`;
                    photoContainer.appendChild(div);
                });
            } else {
                photoContainer.innerHTML = `<p class="text-sm text-slate-400 italic col-span-2">No photos archived for this version.</p>`;
            }
        }

        // Simple date formatter helper
        function formatDate(dateString) {
            // This is a basic implementation. Ideally use a library or clean parsing.
            // Assuming string is compatible with Date() or is already formatted text.
            if(!dateString) return 'N/A';
            if(dateString.includes(',')) return dateString; // Already formatted
            const d = new Date(dateString);
            return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        }
    </script>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
    </style>
</x-layout>