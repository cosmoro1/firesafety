<x-layout>

    {{-- CHECK PERMISSIONS: Admin OR Clerk --}}
    @php
        $hasFullAccess = in_array(auth()->user()->role, ['admin', 'clerk']);
    @endphp

    @if($hasFullAccess)
        
        {{-- MANAGEMENT VIEW --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Company Training Compliance</h2>
                <p class="text-gray-500 text-sm">
                    <span class="bg-red-100 text-red-800 text-xs font-bold px-2 py-0.5 rounded uppercase mr-2">{{ auth()->user()->role }} VIEW</span>
                    Manage seminars and batch certificate issuance.
                </p>
            </div>
            <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition flex items-center">
                <i class="fa-solid fa-plus mr-2"></i> Schedule New Seminar
            </button>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
             <div class="p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="w-full relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" placeholder="Search by company, representative, or ID..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
                </div>
                <div class="flex gap-2">
                    <select class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg border-none focus:ring-0 cursor-pointer">
                        <option>All Industries</option>
                        <option>Commercial</option>
                        <option>Industrial</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold">
                            <th class="px-6 py-4">Establishment</th>
                            <th class="px-6 py-4">Representative</th>
                            <th class="px-6 py-4">Training Details</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        
                        {{-- ROW 1 --}}
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-lg mr-3">
                                        <i class="fa-solid fa-burger"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900">Jollibee - San Pedro</div>
                                        <div class="text-xs text-gray-500">ID: BIZ-2024-882</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">Ana Marie Doe</p>
                                <p class="text-xs text-gray-500">Branch Manager</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-700 font-medium">Annual Fire Safety</span>
                                <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                    <span><i class="fa-solid fa-users mr-1"></i> 12 Attendees</span>
                                    <span>•</span>
                                    <span>Jan 15, 2024</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fa-solid fa-check-circle mr-1.5"></i> Issued
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{-- Pass the attendee count (12) to the modal --}}
                                <button onclick="openCompanyModal('Jollibee - San Pedro', 'Commercial', 'Ana Marie Doe', 'Issued', 'Jan 15, 2024', 12)" class="text-gray-400 hover:text-red-600 transition mx-1" title="View Details">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                <button class="text-gray-400 hover:text-blue-600 transition mx-1" title="Email Certificates">
                                    <i class="fa-solid fa-envelope-open-text"></i>
                                </button>
                            </td>
                        </tr>

                        {{-- ROW 2 --}}
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center text-lg mr-3">
                                        <i class="fa-solid fa-industry"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900">Shell Refinery Inc.</div>
                                        <div class="text-xs text-gray-500">ID: BIZ-2024-991</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">Engr. Mark Smith</p>
                                <p class="text-xs text-gray-500">Safety Officer</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-700 font-medium">HazMat Handling</span>
                                <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                    <span><i class="fa-solid fa-users mr-1"></i> 25 Attendees</span>
                                    <span>•</span>
                                    <span>Feb 02, 2024</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fa-solid fa-clock mr-1.5"></i> Pending Approval
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="openCompanyModal('Shell Refinery Inc.', 'Industrial', 'Engr. Mark Smith', 'Pending', 'Feb 02, 2024', 25)" class="text-gray-400 hover:text-red-600 transition mx-1" title="Review">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-xl flex justify-center">
                <span class="text-xs text-gray-500">Showing 2 of 145 registered establishments</span>
            </div>
        </div>

        {{-- COMPANY DETAILS MODAL --}}
        <div id="companyModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeCompanyModal()"></div>
            
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        
                        {{-- Modal Header --}}
                        <div class="bg-slate-800 px-6 py-6 text-white flex justify-between items-start">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-lg bg-white/10 flex items-center justify-center text-2xl">
                                    <i class="fa-solid fa-building text-red-400"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold" id="modalCompany">Company Name</h3>
                                    <p class="text-slate-300 text-xs uppercase tracking-wide" id="modalIndustry">Industry Type</p>
                                </div>
                            </div>
                            <button onclick="closeCompanyModal()" class="text-slate-400 hover:text-white transition"><i class="fa-solid fa-xmark text-xl"></i></button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-6 py-6">
                            
                            {{-- Info Card --}}
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 mb-6">
                                <div class="flex justify-between items-center mb-3 border-b border-gray-200 pb-2">
                                    <span class="text-xs font-bold text-gray-500 uppercase">Training Status</span>
                                    <span class="text-xs font-bold px-2 py-1 rounded bg-green-100 text-green-700" id="modalStatus">Issued</span>
                                </div>
                                
                                <div class="space-y-3">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 text-red-500"><i class="fa-solid fa-chalkboard-user"></i></div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">Annual Fire Safety Seminar</p>
                                            <p class="text-xs text-gray-500" id="modalDate">Jan 15, 2024</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 text-blue-500"><i class="fa-solid fa-user-tie"></i></div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900" id="modalRep">Representative Name</p>
                                            <p class="text-xs text-gray-500">Authorized Recipient</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 text-orange-500"><i class="fa-solid fa-users"></i></div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900"><span id="modalCount">0</span> Employees Attended</p>
                                            <p class="text-xs text-gray-500">Full attendance list logged</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="space-y-3">
                                {{-- Primary Action: Send to Representative --}}
                                <button class="w-full flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition shadow-sm group">
                                    <i class="fa-solid fa-envelope mr-2 group-hover:animate-pulse"></i> 
                                    Email All <span id="btnCount" class="mx-1"></span> Certificates to Rep
                                </button>
                                
                                {{-- Secondary Actions --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <button class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                        <i class="fa-solid fa-list-check mr-2"></i> Attendance
                                    </button>
                                    <button class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                        <i class="fa-solid fa-file-pdf mr-2"></i> Batch PDF
                                    </button>
                                </div>
                            </div>

                            <p class="text-xs text-center text-gray-400 mt-4">
                                <i class="fa-solid fa-circle-info mr-1"></i> 
                                The representative will receive a zipped file containing certificates for all attendees.
                            </p>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif

    <script>
        function openCompanyModal(company, industry, rep, status, date, count) {
            // Populate Modal Data
            document.getElementById('modalCompany').innerText = company;
            document.getElementById('modalIndustry').innerText = industry;
            document.getElementById('modalRep').innerText = rep;
            document.getElementById('modalStatus').innerText = status;
            document.getElementById('modalDate').innerText = date;
            document.getElementById('modalCount').innerText = count;
            document.getElementById('btnCount').innerText = count; // Update button text

            // Dynamic Status Badge Color
            const statusBadge = document.getElementById('modalStatus');
            if(status === 'Issued') {
                statusBadge.className = "text-xs font-bold px-2 py-1 rounded bg-green-100 text-green-700";
                statusBadge.innerHTML = '<i class="fa-solid fa-check mr-1"></i> Issued';
            } else {
                statusBadge.className = "text-xs font-bold px-2 py-1 rounded bg-yellow-100 text-yellow-700";
                statusBadge.innerHTML = '<i class="fa-solid fa-clock mr-1"></i> Pending';
            }
            
            document.getElementById('companyModal').classList.remove('hidden');
        }

        function closeCompanyModal() {
            document.getElementById('companyModal').classList.add('hidden');
        }
    </script>

</x-layout>