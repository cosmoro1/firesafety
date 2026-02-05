<x-layout>
    {{-- 1. HEADER SECTION --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Incident Reporting</h2>
            <p class="text-gray-500 text-sm">
                @if(auth()->user()->role === 'clerk')
                    <span class="bg-purple-100 text-purple-800 text-xs font-bold px-2 py-0.5 rounded uppercase mr-2">RECORDS VIEW</span>
                    Retrieve and download finalized case reports.
                @elseif(auth()->user()->role === 'admin')
                    Review reports, manage investigation timelines, and encode new incidents.
                @else
                    Encode new incidents and track investigation status.
                @endif
            </p>
        </div>
        
        {{-- HIDE ACTION BUTTONS FOR RECORDS CLERK --}}
        @if(auth()->user()->role !== 'clerk')
        <div class="flex gap-2">
            <button onclick="openImportModal()" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm transition flex items-center">
                <i class="fa-solid fa-file-csv mr-2"></i> Import CSV
            </button>

            <button onclick="openNewReportModal()" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm transition flex items-center">
                <i class="fa-solid fa-plus mr-2"></i> New Report
            </button>
        </div>
        @endif
    </div>

    {{-- 2. SEARCH & FILTER SECTION --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6">
        <div class="p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="w-full md:w-96 relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search ID, location, or officer..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
            </div>

            {{-- HIDE FILTERS FOR RECORDS CLERK (They only see 'Closed' anyway) --}}
            @if(auth()->user()->role !== 'clerk')
            <div class="flex items-center gap-2 overflow-x-auto pb-2">
                <button onclick="window.location.href='{{ route('incidents.index', ['status' => 'all']) }}'" 
                        class="shrink-0 whitespace-nowrap px-4 py-1.5 text-sm font-medium rounded-md shadow-sm transition {{ request('status') == 'all' || !request('status') ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    All
                </button>
                <button onclick="window.location.href='{{ route('incidents.index', ['status' => 'Returned']) }}'" 
                        class="shrink-0 whitespace-nowrap px-4 py-1.5 text-sm font-medium rounded-md shadow-sm transition {{ request('status') == 'Returned' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Returned
                </button>
                <button onclick="window.location.href='{{ route('incidents.index', ['status' => 'Pending']) }}'" 
                        class="shrink-0 whitespace-nowrap px-4 py-1.5 text-sm font-medium rounded-md shadow-sm transition {{ request('status') == 'Pending' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Pending
                </button>
                <button onclick="window.location.href='{{ route('incidents.index', ['status' => 'Case Closed']) }}'" 
                        class="shrink-0 whitespace-nowrap px-4 py-1.5 text-sm font-medium rounded-md shadow-sm transition {{ request('status') == 'Case Closed' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Closed
                </button>
            </div>
            @endif
        </div>

        {{-- 3. TABLE SECTION --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold">
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Stage</th>
                        <th class="px-6 py-4">Title / Location</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Officer</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="incidentTableBody" class="divide-y divide-gray-100 text-sm">
                    @forelse($incidents as $incident)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">INC-{{ $incident->id }}</td>
                            
                            <td class="px-6 py-4">
                                @php
                                    $stageColors = [
                                        'SIR' => 'bg-blue-100 text-blue-700 border-blue-200',
                                        'PIR' => 'bg-purple-100 text-purple-700 border-purple-200',
                                        'FIR' => 'bg-orange-100 text-orange-700 border-orange-200',
                                        'MDFI' => 'bg-green-100 text-green-700 border-green-200'
                                    ];
                                    $stageClass = $stageColors[$incident->stage] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded border text-xs font-bold {{ $stageClass }}">
                                    {{ $incident->stage }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ Str::limit($incident->title, 30) }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $incident->location }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($incident->incident_date)->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $incident->reported_by }}</td>
                            
                            <td class="px-6 py-4">
                                @if($incident->status === 'Returned')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                        <i class="fa-solid fa-rotate-left"></i> Returned
                                    </span>
                                @elseif($incident->status === 'Case Closed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                        <i class="fa-solid fa-lock"></i> Closed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                                        <i class="fa-regular fa-clock"></i> Pending
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    
                                    {{-- TIMELINE (Visible to all) --}}
                                    <button onclick="openTimelineModal('{{ $incident->id }}', '{{ $incident->stage }}', '{{ $incident->status }}', '{{ addslashes($incident->admin_remarks) }}', {{ json_encode($incident->history) }})" 
                                        class="text-blue-600 hover:text-blue-800 bg-blue-50 p-2 rounded-lg transition" title="View Timeline">
                                        <i class="fa-solid fa-list-check"></i>
                                    </button>

                                    {{-- VIEW BUTTON (Visible to all) --}}
                                    <button onclick="openViewModal(
                                        '{{ $incident->id }}', 
                                        '{{ addslashes($incident->title) }}', 
                                        '{{ $incident->type }}', 
                                        '{{ $incident->incident_date }}', 
                                        '{{ addslashes($incident->reported_by) }}', 
                                        '{{ $incident->status }}',
                                        '{{ addslashes(str_replace(array("\r", "\n"), " ", $incident->description)) }}',
                                        '{{ addslashes($incident->admin_remarks) }}',
                                        {{ json_encode($incident->images->pluck('image_path')) }}
                                    )" class="text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition" title="View Details">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>

                                    {{-- EDIT BUTTON (Hidden for Clerk & Closed Cases) --}}
                                    @if(auth()->user()->role !== 'clerk' && $incident->status !== 'Case Closed')
                                    <button onclick="openEditModal(
                                        '{{ $incident->id }}', 
                                        '{{ addslashes($incident->title) }}', 
                                        '{{ date('Y-m-d', strtotime($incident->incident_date)) }}', 
                                        '{{ date('H:i', strtotime($incident->incident_date)) }}', 
                                        '{{ $incident->location }}', 
                                        '{{ $incident->type }}', 
                                        '{{ addslashes(str_replace(array("\r", "\n"), " ", $incident->description)) }}'
                                    )" class="text-orange-500 hover:text-orange-700 p-2 rounded-lg hover:bg-orange-50 transition" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    @endif

                                    {{-- DOWNLOAD BUTTON (Only for Admin/Clerk & Closed Cases) --}}
                                    @if(in_array(auth()->user()->role, ['admin', 'clerk']) && $incident->status === 'Case Closed')
                                    
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                <i class="fa-regular fa-folder-open text-3xl mb-3 block text-gray-300"></i>
                                No reports found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t border-gray-100">
            {{ $incidents->links() }}
        </div>
    </div>

    {{-- MODALS SECTION --}}

    {{-- TIMELINE MODAL --}}
    <dialog id="timelineModal" class="modal rounded-2xl shadow-2xl p-0 w-full max-w-2xl backdrop:bg-slate-900/50">
        <div class="bg-white">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-lg text-gray-800">Investigation Timeline</h3>
                <button onclick="document.getElementById('timelineModal').close()" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <div class="p-8">
                <div class="relative flex justify-between items-center w-full mb-8 px-4">
                    <div class="absolute top-1/2 left-0 w-full h-1 bg-gray-200 -z-0"></div>
                    
                    <div class="relative z-10 flex flex-col items-center group">
                        <div id="step-SIR" class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-4 bg-white border-gray-300 text-gray-400 transition-all cursor-pointer">1</div>
                        <span class="text-xs font-bold mt-2 text-gray-500">SIR</span>
                    </div>
                    <div class="relative z-10 flex flex-col items-center group">
                        <div id="step-PIR" class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-4 bg-white border-gray-300 text-gray-400 transition-all cursor-pointer">2</div>
                        <span class="text-xs font-bold mt-2 text-gray-500">PIR</span>
                    </div>
                    <div class="relative z-10 flex flex-col items-center group">
                        <div id="step-FIR" class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-4 bg-white border-gray-300 text-gray-400 transition-all cursor-pointer">3</div>
                        <span class="text-xs font-bold mt-2 text-gray-500">Final</span>
                    </div>
                </div>

                <div id="historyDisplayArea" class="mb-6 min-h-[120px] transition-all duration-300">
                    <div class="p-6 text-center border-2 border-dashed border-slate-100 rounded-xl">
                        <p class="text-sm text-slate-400 italic">Click a stage above to view report details.</p>
                    </div>
                </div>

                <div id="timelineRemarksBox" class="hidden bg-red-50 border border-red-100 p-4 rounded-xl mb-6 flex gap-3">
                    <i class="fa-solid fa-circle-exclamation text-red-500 mt-1"></i>
                    <div>
                        <h4 class="font-bold text-red-700 text-sm">Returned for Revision</h4>
                        <p id="timelineRemarksText" class="text-red-600 text-sm mt-1"></p>
                    </div>
                </div>

                @if(auth()->user()->role === 'admin')
                <div id="adminActions" class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button onclick="showReturnForm()" class="px-4 py-2 text-red-600 font-bold text-sm border border-red-200 rounded-lg hover:bg-red-50 transition">
                        <i class="fa-solid fa-rotate-left mr-2"></i> Return to Officer
                    </button>
                    
                    <form id="approveForm" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" id="nextStageBtn" class="px-6 py-2 bg-blue-600 text-white font-bold text-sm rounded-lg hover:bg-blue-700 shadow-md transition">
                            Approve & Next Stage <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </form>
                </div>

                <form id="returnForm" method="POST" class="hidden mt-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="return">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Reason for Return:</label>
                    <textarea name="remarks" rows="3" class="w-full border-gray-300 rounded-lg text-sm mb-3 p-2" placeholder="e.g. Missing evidence..."></textarea>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="hideReturnForm()" class="text-gray-500 text-sm font-bold px-3">Cancel</button>
                        <button type="submit" class="bg-red-600 text-white text-sm font-bold px-4 py-2 rounded-lg">Confirm Return</button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </dialog>

    {{-- VIEW REPORT MODAL --}}
    <div id="viewReportModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeViewModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-2xl">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">Incident Details</h3>
                        <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-500"><i class="fa-solid fa-xmark text-xl"></i></button>
                    </div>
                    <div class="px-6 py-6 space-y-4">
                        <div id="viewReturnAlert" class="hidden bg-red-50 p-3 rounded-lg border border-red-100 text-sm text-red-700 font-medium mb-4">
                            <i class="fa-solid fa-triangle-exclamation mr-2"></i> <span id="viewReturnText"></span>
                        </div>

                        <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                            <div>
                                <p class="text-sm text-gray-500">Incident ID</p>
                                <p id="viewIdDisplay" class="text-lg font-bold text-gray-900"></p>
                            </div>
                            <div id="viewStatusBadge"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><p class="text-sm text-gray-500">Report Type</p><p id="viewType" class="font-medium text-gray-900"></p></div>
                            <div><p class="text-sm text-gray-500">Date Reported</p><p id="viewDate" class="font-medium text-gray-900"></p></div>
                            <div><p class="text-sm text-gray-500">Reported By</p><p id="viewOfficer" class="font-medium text-gray-900"></p></div>
                        </div>
                        
                        <div id="viewImageContainer" class="hidden mb-4">
                            <p class="text-sm text-gray-500 mb-2">Attached Evidence</p>
                            <div id="viewImageGrid" class="grid grid-cols-2 md:grid-cols-3 gap-2"></div>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Incident Title / Location</p>
                            <p id="viewTitle" class="font-medium text-gray-900"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Description</p>
                            <div id="viewDescription" class="bg-gray-50 p-3 rounded-lg text-sm text-gray-700 border border-gray-100 whitespace-pre-wrap"></div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex justify-between items-center">
                        {{-- DOWNLOAD BUTTON IN MODAL (Dynamic) --}}
                        @if(in_array(auth()->user()->role, ['admin', 'clerk']))
                            <a id="viewDownloadBtn" href="#" target="_blank" class="hidden inline-flex items-center justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                                <i class="fa-solid fa-file-pdf mr-2"></i> Download Official Report
                            </a>
                        @else
                            <div></div> {{-- Spacer if button not shown --}}
                        @endif

                        <button onclick="closeViewModal()" class="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:w-auto">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- IMPORT MODAL (Hidden for Clerks) --}}
    @if(auth()->user()->role !== 'clerk')
    <div id="importModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeImportModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-lg">
                    <form action="{{ route('incidents.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 mb-4">Import Historical Incidents</h3>
                            <div class="mb-4 bg-yellow-50 p-3 rounded-lg border border-yellow-100 text-xs text-yellow-700">
                                <strong>Format:</strong> Type, Title, Date (YYYY-MM-DD), Time (HH:MM), Location, Description, Status (optional)
                            </div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload CSV File</label>
                            <input type="file" name="file" accept=".csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"/>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:ml-3 sm:w-auto">Upload & Import</button>
                            <button type="button" onclick="closeImportModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- NEW/EDIT FORM MODAL --}}
    <div id="formModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeFormModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl">
                    <form id="incidentForm" action="{{ route('incidents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod" value="POST">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-xl font-semibold leading-6 text-gray-900" id="formModalTitle">New Incident Report</h3>
                            <button type="button" onclick="closeFormModal()" class="text-gray-400 hover:text-gray-500"><i class="fa-solid fa-xmark text-xl"></i></button>
                        </div>
                        <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nature of Fire <span class="text-red-500">*</span></label>
                                <select name="type" id="inputTypeSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500 bg-white" required>
                                    <option value="Structural">Structural</option>
                                    <option value="Non-Structural">Non-Structural</option>
                                    <option value="Vehicular">Vehicular</option>
                                </select>
                            </div>
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Investigation Stage</label>
                                <input type="hidden" name="stage" id="inputStage" value="SIR"> 
                                <div class="grid grid-cols-2 gap-3">
                                    <button type="button" onclick="selectStage(this, 'SIR')" class="stage-btn border-2 border-red-500 bg-red-50 text-red-700 font-bold py-2 rounded-lg text-sm w-full transition shadow-sm">SIR (Standard)</button>
                                    <button type="button" onclick="selectStage(this, 'MDFI')" class="stage-btn border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium py-2 rounded-lg text-sm w-full transition">MDFI (Minor)</button>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-2 italic">Note: PIR and FIR stages are managed by Admin based on investigation progress.</p>
                            </div>
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Incident Title <span class="text-red-500">*</span></label>
                                <input type="text" name="title" id="inputTitle" placeholder="e.g., Residential Fire" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500" required>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                                    <input type="date" name="date" id="inputDate" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Time <span class="text-red-500">*</span></label>
                                    <input type="time" name="time" id="inputTime" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                                </div>
                            </div>
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location (Barangay) <span class="text-red-500">*</span></label>
                                <select name="location" id="inputLocation" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white" required>
                                    <option value="">Select Barangay</option>
                                    <option value="Bagong Kalsada">Bagong Kalsada</option>
                                    <option value="Bañadero">Bañadero</option>
                                    <option value="Banlic">Banlic</option>
                                    <option value="Barandal">Barandal</option>
                                    <option value="Barangay 1 (Poblacion)">Barangay 1 (Poblacion)</option>
                                    <option value="Barangay 2 (Poblacion)">Barangay 2 (Poblacion)</option>
                                    <option value="Barangay 3 (Poblacion)">Barangay 3 (Poblacion)</option>
                                    <option value="Barangay 4 (Poblacion)">Barangay 4 (Poblacion)</option>
                                    <option value="Barangay 5 (Poblacion)">Barangay 5 (Poblacion)</option>
                                    <option value="Barangay 6 (Poblacion)">Barangay 6 (Poblacion)</option>
                                    <option value="Barangay 7 (Poblacion)">Barangay 7 (Poblacion)</option>
                                    <option value="Batino">Batino</option>
                                    <option value="Bubuyan">Bubuyan</option>
                                    <option value="Bucal">Bucal</option>
                                    <option value="Bunggo">Bunggo</option>
                                    <option value="Burol">Burol</option>
                                    <option value="Camaligan">Camaligan</option>
                                    <option value="Canlubang">Canlubang</option>
                                    <option value="Halang">Halang</option>
                                    <option value="Hornalan">Hornalan</option>
                                    <option value="Kay-Anlog">Kay-Anlog</option>
                                    <option value="La Mesa">La Mesa</option>
                                    <option value="Laguerta">Laguerta</option>
                                    <option value="Lawa">Lawa</option>
                                    <option value="Lecheria">Lecheria</option>
                                    <option value="Lingga">Lingga</option>
                                    <option value="Looc">Looc</option>
                                    <option value="Mabato">Mabato</option>
                                    <option value="Majada Labas">Majada Labas</option>
                                    <option value="Makiling">Makiling</option>
                                    <option value="Mapagong">Mapagong</option>
                                    <option value="Masili">Masili</option>
                                    <option value="Maunong">Maunong</option>
                                    <option value="Mayapa">Mayapa</option>
                                    <option value="Paciano Rizal">Paciano Rizal</option>
                                    <option value="Palingon">Palingon</option>
                                    <option value="Palo-Alto">Palo-Alto</option>
                                    <option value="Pansol">Pansol</option>
                                    <option value="Parian">Parian</option>
                                    <option value="Prinza">Prinza</option>
                                    <option value="Punta">Punta</option>
                                    <option value="Puting Lupa">Puting Lupa</option>
                                    <option value="Real">Real</option>
                                    <option value="Saimsim">Saimsim</option>
                                    <option value="Sampiruhan">Sampiruhan</option>
                                    <option value="San Cristobal">San Cristobal</option>
                                    <option value="San Jose">San Jose</option>
                                    <option value="San Juan">San Juan</option>
                                    <option value="Sirang Lupa">Sirang Lupa</option>
                                    <option value="Sucol">Sucol</option>
                                    <option value="Tulo">Tulo</option>
                                    <option value="Turbina">Turbina</option>
                                    <option value="Ulango">Ulango</option>
                                    <option value="Uwisan">Uwisan</option>
                                </select>
                            </div>
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Attach Photo Evidence (Multiple allowed)</label>
                                <input type="file" name="evidence[]" multiple id="inputEvidence" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100"/>
                                <p class="text-xs text-gray-400 mt-1">Select multiple files (PNG, JPG). Max 5MB each.</p>
                            </div>
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                                <textarea name="description" id="inputDescription" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500" required></textarea>
                            </div>
                        </div> 
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-100">
                            <button type="submit" id="formSubmitBtn" class="inline-flex w-full justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Submit Report</button>
                            <button type="button" onclick="closeFormModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>
    // 1. IMPORT MODAL
    function openImportModal() { document.getElementById('importModal').classList.remove('hidden'); }
    function closeImportModal() { document.getElementById('importModal').classList.add('hidden'); }

    // 2. STAGE SELECTION
    function selectStage(btn, value) {
        document.getElementById('inputStage').value = value;
        document.querySelectorAll('.stage-btn').forEach(b => {
            b.className = 'stage-btn border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium py-2 rounded-lg text-sm w-full transition';
        });
        btn.className = 'stage-btn border-2 border-red-500 bg-red-50 text-red-700 font-semibold py-2 rounded-lg text-sm w-full transition shadow-sm';
    }

    // 3. TIMELINE MODAL LOGIC (Kept same as provided)
    let activeHistory = [];
    function openTimelineModal(id, stage, status, remarks, historyData) {
        const modal = document.getElementById('timelineModal');
        activeHistory = historyData || [];
        
        const approveForm = document.getElementById('approveForm');
        if (approveForm) approveForm.action = "/incidents/" + id + "/status";
        const returnForm = document.getElementById('returnForm');
        if (returnForm) returnForm.action = "/incidents/" + id + "/status";

        const steps = ['SIR', 'PIR', 'FIR'];
        steps.forEach((s) => {
            const el = document.getElementById('step-' + s);
            el.className = "w-10 h-10 rounded-full flex items-center justify-center font-bold border-4 bg-white border-gray-300 text-gray-400 transition-all cursor-pointer hover:border-blue-400 hover:scale-105";
            el.innerHTML = steps.indexOf(s) + 1;
            el.onclick = () => showHistoryContent(s);
        });

        let activeIndex = steps.indexOf(stage);
        if (stage === 'MDFI') activeIndex = 2; 

        for (let i = 0; i <= activeIndex; i++) {
            const el = document.getElementById('step-' + steps[i]);
            if(i < activeIndex) {
                el.className = "w-10 h-10 rounded-full flex items-center justify-center font-bold border-4 bg-green-500 border-green-200 text-white transition-all cursor-pointer";
                el.innerHTML = '<i class="fa-solid fa-check"></i>';
            } else {
                el.className = "w-10 h-10 rounded-full flex items-center justify-center font-bold border-4 bg-blue-600 border-blue-200 text-white transition-all cursor-pointer";
            }
        }

        const nextBtn = document.getElementById('nextStageBtn');
        if(nextBtn) {
            if(stage === 'SIR') nextBtn.innerHTML = 'Approve SIR & Move to PIR <i class="fa-solid fa-arrow-right ml-2"></i>';
            else if(stage === 'PIR') nextBtn.innerHTML = 'Approve PIR & Move to FIR <i class="fa-solid fa-arrow-right ml-2"></i>';
            else nextBtn.innerHTML = 'Finalize & Close Case <i class="fa-solid fa-lock ml-2"></i>';
        }

        showHistoryContent(stage === 'MDFI' ? 'FIR' : stage);

        if (status === 'Returned') {
            document.getElementById('timelineRemarksBox').classList.remove('hidden');
            document.getElementById('timelineRemarksText').innerText = remarks;
            const currentStepId = stage === 'MDFI' ? 'step-FIR' : 'step-' + stage;
            const currentStepEl = document.getElementById(currentStepId);
            currentStepEl.className = "w-10 h-10 rounded-full flex items-center justify-center font-bold border-4 bg-red-500 border-red-200 text-white transition-all";
            currentStepEl.innerHTML = '<i class="fa-solid fa-exclamation"></i>';
        } else {
            document.getElementById('timelineRemarksBox').classList.add('hidden');
        }
        modal.showModal();
    }

    function showHistoryContent(stageName) {
        const displayArea = document.getElementById('historyDisplayArea');
        const record = activeHistory.find(h => h.stage === stageName);
        if (record) {
            const dateObj = new Date(record.incident_date);
            const formattedDate = dateObj.toLocaleDateString() + ' ' + dateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            const savedDate = new Date(record.created_at).toLocaleDateString();
            
            let imagesHtml = '';
            if (record.images && Array.isArray(record.images) && record.images.length > 0) {
                imagesHtml = `<div class="mt-4 pt-4 border-t border-slate-200"><p class="text-[10px] text-slate-400 uppercase font-bold mb-2">Attached Evidence (${record.images.length})</p><div class="grid grid-cols-3 gap-2">`;
                record.images.forEach(path => {
                    imagesHtml += `<img src="/storage/${path}" onclick="window.open(this.src)" class="h-20 w-full object-cover rounded-md border border-slate-200 cursor-pointer hover:opacity-80">`;
                });
                imagesHtml += `</div></div>`;
            }

            displayArea.innerHTML = `<div class="bg-slate-50 border border-slate-200 rounded-xl overflow-hidden transition-all duration-300">
                <div class="bg-slate-100 px-4 py-2 border-b border-slate-200 flex justify-between items-center"><h4 class="font-bold text-slate-700 text-xs uppercase tracking-wider"><i class="fa-solid fa-camera-retro mr-1"></i> ${stageName} Snapshot</h4><span class="text-[10px] text-slate-400 italic">Archived on ${savedDate}</span></div>
                <div class="p-4 grid grid-cols-2 gap-4 text-sm">
                    <div class="col-span-2"><p class="text-[10px] text-slate-400 uppercase font-bold">Title</p><p class="font-semibold text-slate-800">${record.title || 'N/A'}</p></div>
                    <div><p class="text-[10px] text-slate-400 uppercase font-bold">Type</p><p class="text-slate-700">${record.type || 'N/A'}</p></div>
                    <div><p class="text-[10px] text-slate-400 uppercase font-bold">Location</p><p class="text-slate-700">${record.location || 'N/A'}</p></div>
                    <div><p class="text-[10px] text-slate-400 uppercase font-bold">Date of Incident</p><p class="text-slate-700">${formattedDate}</p></div>
                    <div><p class="text-[10px] text-slate-400 uppercase font-bold">Reported By</p><p class="text-slate-700">${record.reported_by || 'N/A'}</p></div>
                    <div class="col-span-2 mt-2 pt-2 border-t border-slate-200"><p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Description</p><p class="text-slate-600 leading-relaxed whitespace-pre-wrap">${record.description}</p></div>
                    <div class="col-span-2">${imagesHtml}</div>
                </div></div>`;
        } else {
            displayArea.innerHTML = `<div class="p-6 text-center border-2 border-dashed border-slate-100 rounded-xl"><p class="text-sm text-slate-400 italic">No recorded data for the ${stageName} stage yet.</p></div>`;
        }
    }
    
    function showReturnForm() { document.getElementById('returnForm').classList.remove('hidden'); document.getElementById('adminActions').classList.add('hidden'); }
    function hideReturnForm() { document.getElementById('returnForm').classList.add('hidden'); document.getElementById('adminActions').classList.remove('hidden'); }

    // 4. VIEW MODAL
    function openViewModal(id, title, type, date, officer, status, description, remarks, imagesData) {
        document.getElementById('viewIdDisplay').innerText = 'INC-' + id;
        document.getElementById('viewTitle').innerText = title;
        document.getElementById('viewType').innerText = type;
        document.getElementById('viewDate').innerText = date;
        document.getElementById('viewOfficer').innerText = officer;
        document.getElementById('viewDescription').innerText = description;
        
        if(status === 'Returned' && remarks) {
            document.getElementById('viewReturnAlert').classList.remove('hidden');
            document.getElementById('viewReturnText').innerText = remarks;
        } else {
            document.getElementById('viewReturnAlert').classList.add('hidden');
        }

        // --- DYNAMIC DOWNLOAD BUTTON LOGIC ---
        const dlBtn = document.getElementById('viewDownloadBtn');
        if (dlBtn) {
            if (status === 'Case Closed') {
                dlBtn.href = "/incidents/" + id + "/download"; 
                dlBtn.classList.remove('hidden');
            } else {
                dlBtn.classList.add('hidden');
            }
        }

        const imgContainer = document.getElementById('viewImageContainer');
        const imgGrid = document.getElementById('viewImageGrid');
        const images = imagesData || [];
        imgGrid.innerHTML = '';

        if (images.length > 0) {
            imgContainer.classList.remove('hidden');
            images.forEach(path => {
                const img = document.createElement('img');
                img.src = "/storage/" + path;
                img.className = "w-full h-32 object-cover rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:opacity-90 transition";
                img.onclick = function() { window.open(this.src, '_blank'); };
                imgGrid.appendChild(img);
            });
        } else {
            imgContainer.classList.add('hidden');
        }

        let badgeClass = status === 'Case Closed' ? 'bg-gray-100 text-gray-600' : 'bg-blue-100 text-blue-600';
        if(status === 'Returned') badgeClass = 'bg-red-100 text-red-600';
        
        document.getElementById('viewStatusBadge').innerHTML = `<span class="px-2.5 py-1 rounded-full text-xs font-semibold ${badgeClass}">${status}</span>`;
        document.getElementById('viewReportModal').classList.remove('hidden');
    }
    function closeViewModal() { document.getElementById('viewReportModal').classList.add('hidden'); }

    // 5. NEW / EDIT MODAL
    function openNewReportModal() {
        document.getElementById('formModalTitle').innerText = "New Incident Report";
        document.getElementById('formSubmitBtn').innerText = "Submit Report";
        document.getElementById('incidentForm').reset();
        document.getElementById('inputEvidence').value = ""; 
        document.getElementById('inputStage').value = "SIR";
        const sirBtn = document.querySelector('.stage-btn');
        if(sirBtn) selectStage(sirBtn, 'SIR');
        const form = document.getElementById('incidentForm');
        form.action = "{{ route('incidents.store') }}"; 
        document.getElementById('formMethod').value = "POST";
        document.getElementById('formModal').classList.remove('hidden');
    }

    function openEditModal(id, title, date, time, location, type, description) {
        document.getElementById('formModalTitle').innerText = "Edit Incident Report";
        document.getElementById('formSubmitBtn').innerText = "Update Report";
        document.getElementById('incidentForm').reset();
        document.getElementById('inputEvidence').value = ""; 
        document.getElementById('inputTitle').value = title;
        document.getElementById('inputDate').value = date;
        document.getElementById('inputTime').value = time;
        document.getElementById('inputLocation').value = location;
        document.getElementById('inputTypeSelect').value = type;
        document.getElementById('inputDescription').value = description;
        const form = document.getElementById('incidentForm');
        form.action = "/incidents/" + id; 
        document.getElementById('formMethod').value = "PUT";
        document.getElementById('formModal').classList.remove('hidden');
    }

    function closeFormModal() { document.getElementById('formModal').classList.add('hidden'); }

    function searchTable() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let rows = document.querySelectorAll('#incidentTableBody tr');
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
        });
    }
</script>
</x-layout>