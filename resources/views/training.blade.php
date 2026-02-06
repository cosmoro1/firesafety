<x-layout>

    {{-- CHECK PERMISSIONS: Admin OR Clerk --}}
    @php
        $hasFullAccess = in_array(auth()->user()->role, ['admin', 'clerk']);
    @endphp

    @if($hasFullAccess)
        
        {{-- SUCCESS & ERROR MESSAGES --}}
        @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
            <i class="fa-solid fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center">
            <i class="fa-solid fa-circle-exclamation mr-2"></i>
            {{ session('error') }}
        </div>
        @endif

        {{-- MANAGEMENT VIEW HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Company Training Compliance</h2>
                <p class="text-gray-500 text-sm">
                    <span class="bg-red-100 text-red-800 text-xs font-bold px-2 py-0.5 rounded uppercase mr-2">{{ auth()->user()->role }} VIEW</span>
                    Manage seminars and certificate issuance.
                </p>
            </div>
            
            <button onclick="openScheduleModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition flex items-center">
                <i class="fa-solid fa-plus mr-2"></i> Schedule New Seminar
            </button>
        </div>

        {{-- TABLE SECTION --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
            <form method="GET" action="{{ route('training.index') }}" class="p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="w-full relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by company, representative, or ID..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
                </div>
                <div class="flex gap-2">
                    <select name="industry" onchange="this.form.submit()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg border-none focus:ring-0 cursor-pointer">
                        <option {{ request('industry') == 'All Industries' ? 'selected' : '' }}>All Industries</option>
                        <option {{ request('industry') == 'Commercial' ? 'selected' : '' }}>Commercial</option>
                        <option {{ request('industry') == 'Industrial' ? 'selected' : '' }}>Industrial</option>
                    </select>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold">
                            <th class="px-6 py-4">Establishment</th>
                            <th class="px-6 py-4">Representative</th>
                            <th class="px-6 py-4">Details</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        
                        @forelse($trainings as $training)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-lg {{ $training->industry_type == 'Industrial' ? 'bg-orange-50 text-orange-600' : 'bg-red-50 text-red-600' }} flex items-center justify-center text-lg mr-3">
                                        <i class="fa-solid {{ $training->industry_type == 'Industrial' ? 'fa-industry' : 'fa-burger' }}"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $training->company_name }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $training->company_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $training->representative_name }}</p>
                                <p class="text-xs text-gray-500">{{ $training->representative_email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-700 font-medium">{{ $training->topic }}</span>
                                <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                    <span><i class="fa-solid fa-users mr-1"></i> {{ $training->attendees_count }}</span>
                                    <span>•</span>
                                    <span>{{ \Carbon\Carbon::parse($training->date_conducted)->format('M d, Y') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($training->status == 'Issued')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fa-solid fa-check-circle mr-1.5"></i> Issued</span>
                                @elseif($training->status == 'Scheduled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fa-solid fa-calendar mr-1.5"></i> Scheduled</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fa-solid fa-clock mr-1.5"></i> {{ $training->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{-- View/Email Button --}}
                                <button onclick="openCompanyModal(
                                    {{ $training->id }}, 
                                    '{{ addslashes($training->company_name) }}', 
                                    '{{ $training->industry_type }}', 
                                    '{{ addslashes($training->representative_name) }}', 
                                    '{{ $training->representative_email }}', {{-- ADDED EMAIL HERE --}}
                                    '{{ $training->status }}', 
                                    '{{ \Carbon\Carbon::parse($training->date_conducted)->format('M d, Y') }}',
                                    {{ $training->attendees_count }}
                                )" class="text-gray-400 hover:text-blue-600 transition mx-1" title="View & Email">
                                    <i class="fa-regular fa-eye"></i>
                                </button>

                                {{-- Edit Button --}}
                                <button onclick="openEditModal({{ $training }})" class="text-gray-400 hover:text-orange-500 transition mx-1" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <p>No training records found.</p>
                            </td>
                        </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
            
            @if($trainings->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-xl flex justify-center">{{ $trainings->withQueryString()->links() }}</div>
            @endif
        </div>

        {{-- 1. SCHEDULE MODAL --}}
        <div id="scheduleModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeScheduleModal()"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <form action="{{ route('training.store') }}" method="POST">
                            @csrf
                            <div class="bg-white px-6 py-6">
                                <div class="flex justify-between items-center mb-5">
                                    <h3 class="text-lg font-bold text-gray-900">Schedule New Seminar</h3>
                                    <button type="button" onclick="closeScheduleModal()" class="text-gray-400 hover:text-gray-500"><i class="fa-solid fa-xmark text-xl"></i></button>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                                        <input type="text" name="company_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Company ID</label>
                                            <input type="text" name="company_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                                            <select name="industry_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                                <option>Commercial</option>
                                                <option>Industrial</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Representative</label>
                                            <input type="text" name="representative_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                            <input type="email" name="representative_email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Topic</label>
                                        <input type="text" name="topic" value="Annual Fire Safety Seminar" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                            <input type="date" name="date_conducted" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Attendees</label>
                                            <input type="number" name="attendees_count" value="1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse">
                                <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Confirm</button>
                                <button type="button" onclick="closeScheduleModal()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. VIEW & EMAIL MODAL --}}
        <div id="companyModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeCompanyModal()"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <div class="bg-slate-800 px-6 py-6 text-white flex justify-between items-start">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-lg bg-white/10 flex items-center justify-center text-2xl"><i class="fa-solid fa-building text-red-400"></i></div>
                                <div><h3 class="text-lg font-bold" id="modalCompany">Name</h3><p class="text-slate-300 text-xs uppercase" id="modalIndustry">Type</p></div>
                            </div>
                            <button onclick="closeCompanyModal()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
                        </div>
                        <div class="px-6 py-6">
                            {{-- Info Card --}}
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 mb-6">
                                <div class="flex justify-between items-center mb-3 border-b border-gray-200 pb-2">
                                    <span class="text-xs font-bold text-gray-500 uppercase">Status</span>
                                    <span class="text-xs font-bold px-2 py-1 rounded bg-green-100 text-green-700" id="modalStatus">Issued</span>
                                </div>
                                <div class="space-y-2">
                                    <p class="text-sm"><i class="fa-solid fa-user-tie text-blue-500 mr-2"></i> <span id="modalRep">Rep Name</span></p>
                                    {{-- ADDED EMAIL DISPLAY --}}
                                    <p class="text-sm"><i class="fa-solid fa-envelope text-gray-500 mr-2"></i> <span id="modalEmail" class="text-gray-700">email@example.com</span></p>
                                    <p class="text-sm"><i class="fa-solid fa-calendar text-red-500 mr-2"></i> <span id="modalDate">Date</span></p>
                                    <p class="text-sm"><i class="fa-solid fa-users text-orange-500 mr-2"></i> <span id="modalAttendees">0</span> Attendees</p>
                                </div>
                            </div>

                            {{-- Email Form --}}
                            <form id="emailForm" method="POST" action="" enctype="multipart/form-data">
                                @csrf
                                {{-- ADDED HIDDEN EMAIL INPUT --}}
                                <input type="hidden" name="representative_email" id="hiddenEmail">
                                
                                <input type="file" name="certificate_files[]" id="certificateFiles" class="hidden" multiple accept=".pdf,.jpg,.png,.jpeg" onchange="handleFileSelect(this)">
                                
                                <button type="button" onclick="document.getElementById('certificateFiles').click()" class="w-full flex items-center justify-center px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-sm font-bold text-gray-500 hover:border-red-500 hover:text-red-500 transition mb-3">
                                    <i class="fa-solid fa-images mr-2"></i> Select Certificates
                                </button>

                                <div id="previewContainer" class="grid grid-cols-3 gap-2 mb-4 hidden"></div>

                                <button type="submit" id="sendBtn" class="hidden w-full flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition shadow-sm">
                                    <i class="fa-solid fa-paper-plane mr-2"></i> Send Files
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. EDIT MODAL (FIXED DESIGN) --}}
        <div id="editModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeEditModal()"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        
                        {{-- Header --}}
                        <div class="bg-gray-100 px-6 py-4 flex justify-between items-center border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Edit Seminar Details</h3>
                            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
                        </div>

                        {{-- Update Form --}}
                        <form id="editForm" method="POST" action="">
                            @csrf
                            @method('PUT')
                            
                            <div class="px-6 py-6 space-y-4">
                                {{-- Company Name --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
                                    <input type="text" name="company_name" id="edit_company_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                </div>
                                
                                {{-- ID & Industry --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">ID</label>
                                        <input type="text" name="company_id" id="edit_company_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                                        <select name="industry_type" id="edit_industry_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                            <option value="Commercial">Commercial</option>
                                            <option value="Industrial">Industrial</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Representative & Email --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Representative</label>
                                        <input type="text" name="representative_name" id="edit_representative_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <input type="email" name="representative_email" id="edit_representative_email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                    </div>
                                </div>

                                {{-- Topic --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Topic</label>
                                    <input type="text" name="topic" id="edit_topic" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                </div>

                                {{-- Status, Date, Attendees --}}
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select name="status" id="edit_status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                            <option value="Scheduled">Scheduled</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Issued">Issued</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                        <input type="date" name="date_conducted" id="edit_date_conducted" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Attendees</label>
                                        <input type="number" name="attendees_count" id="edit_attendees_count" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Buttons --}}
                            <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-200">
                                {{-- Delete Button --}}
                                <button type="button" onclick="confirmDelete()" class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-red-100 rounded-lg hover:bg-red-200 transition">
                                    <i class="fa-solid fa-trash-can mr-2"></i> Delete
                                </button>

                                <div class="flex gap-2">
                                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 shadow-sm transition">Save Changes</button>
                                </div>
                            </div>
                        </form>

                        {{-- Hidden Delete Form --}}
                        <form id="deleteForm" method="POST" action="">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>
        </div>

    @endif

    <script>
        // --- VIEW / EMAIL MODAL ---
        function openCompanyModal(id, company, industry, rep, email, status, date, attendees) {
            document.getElementById('modalCompany').innerText = company;
            document.getElementById('modalIndustry').innerText = industry;
            document.getElementById('modalRep').innerText = rep;
            document.getElementById('modalEmail').innerText = email; // Show Email
            document.getElementById('modalStatus').innerText = status;
            document.getElementById('modalDate').innerText = date;
            document.getElementById('modalAttendees').innerText = attendees;

            // Reset Email Form
            document.getElementById('emailForm').action = "/training/" + id + "/email";
            document.getElementById('hiddenEmail').value = email; // Populate hidden field
            document.getElementById('certificateFiles').value = ""; 
            document.getElementById('previewContainer').innerHTML = ""; 
            document.getElementById('previewContainer').classList.add('hidden'); 
            document.getElementById('sendBtn').classList.add('hidden');

            // Status Badge Color
            const badge = document.getElementById('modalStatus');
            badge.className = status === 'Issued' ? "text-xs font-bold px-2 py-1 rounded bg-green-100 text-green-700" : 
                             (status === 'Scheduled' ? "text-xs font-bold px-2 py-1 rounded bg-blue-100 text-blue-700" : 
                             "text-xs font-bold px-2 py-1 rounded bg-yellow-100 text-yellow-700");

            document.getElementById('companyModal').classList.remove('hidden');
        }
        function closeCompanyModal() { document.getElementById('companyModal').classList.add('hidden'); }

        // --- SCHEDULE MODAL ---
        function openScheduleModal() { document.getElementById('scheduleModal').classList.remove('hidden'); }
        function closeScheduleModal() { document.getElementById('scheduleModal').classList.add('hidden'); }

        // --- EDIT MODAL ---
        function openEditModal(data) {
            document.getElementById('edit_company_name').value = data.company_name;
            document.getElementById('edit_company_id').value = data.company_id;
            document.getElementById('edit_industry_type').value = data.industry_type;
            document.getElementById('edit_representative_name').value = data.representative_name;
            document.getElementById('edit_representative_email').value = data.representative_email || '';
            document.getElementById('edit_topic').value = data.topic;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_date_conducted').value = data.date_conducted;
            document.getElementById('edit_attendees_count').value = data.attendees_count;

            document.getElementById('editForm').action = "/training/" + data.id;
            document.getElementById('deleteForm').action = "/training/" + data.id;

            document.getElementById('editModal').classList.remove('hidden');
        }
        function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); }
        function confirmDelete() {
            if(confirm("Are you sure? This cannot be undone.")) document.getElementById('deleteForm').submit();
        }

        // --- FILE HANDLING ---
        function handleFileSelect(input) {
            const container = document.getElementById('previewContainer');
            const sendBtn = document.getElementById('sendBtn');
            container.innerHTML = ''; 
            
            if (input.files.length > 0) {
                container.classList.remove('hidden');
                sendBtn.classList.remove('hidden');
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'relative border border-gray-200 rounded-lg overflow-hidden h-20 bg-gray-50 flex items-center justify-center';
                        div.innerHTML = file.type.startsWith('image/') ? 
                            `<img src="${e.target.result}" class="w-full h-full object-cover">` : 
                            `<div class="flex flex-col items-center justify-center text-gray-500 p-1"><i class="fa-solid fa-file-pdf text-red-500 text-xl mb-1"></i><span class="text-[9px] text-center leading-tight w-full truncate px-1">${file.name}</span></div>`;
                        container.appendChild(div);
                    }
                    reader.readAsDataURL(file);
                });
            } else {
                container.classList.add('hidden');
                sendBtn.classList.add('hidden');
            }
        }
    </script>

</x-layout>