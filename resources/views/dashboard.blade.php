<x-layout>
    <div class="space-y-6">
        
        {{-- HEADER SECTION --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Dashboard</h2>
                <p class="text-slate-500 text-sm font-medium">Operational oversight and real-time safety intelligence.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white text-slate-600 text-sm font-semibold px-4 py-2 rounded-xl border border-slate-200 shadow-sm flex items-center">
                    <i class="fa-solid fa-calendar-day text-red-500 mr-2"></i>
                    {{ date('F d, Y') }}
                </div>
                {{-- REPORT INCIDENT BUTTON (Links to Incidents Page) --}}
                <button onclick="window.location.href='{{ route('incidents.index') }}'" class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white text-sm font-bold px-6 py-2 rounded-xl shadow-md shadow-red-200 transition-all transform hover:scale-105 flex items-center">
                    <i class="fa-solid fa-fire-flame-curved mr-2 animate-pulse"></i> Report Incident
                </button>
            </div>
        </div>

        {{-- KPI CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Total Incidents --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border-t-4 border-red-500 relative overflow-hidden group transition-all hover:-translate-y-1">
                <i class="fa-solid fa-fire-burner absolute -right-6 -bottom-6 text-8xl text-red-50 opacity-50 group-hover:opacity-100 transition-opacity group-hover:rotate-12"></i>
                <div class="relative z-10 font-bold text-slate-500 text-sm uppercase tracking-wider mb-2">Total Incidents</div>
                <div class="relative z-10 flex items-baseline gap-3">
                    <h3 class="text-5xl font-extrabold text-slate-800">{{ $totalIncidents }}</h3>
                </div>
                <div class="relative z-10 flex items-center mt-4 text-sm font-bold">
                    <span class="text-green-600 bg-green-100/80 px-2 py-0.5 rounded-md mr-2 flex items-center">
                        <i class="fa-solid fa-arrow-trend-up mr-1"></i> {{ $incidentGrowth }}%
                    </span>
                    <span class="text-slate-400 font-medium">vs last year</span>
                </div>
            </div>

            {{-- Pending Reports --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border-t-4 border-orange-500 relative overflow-hidden group transition-all hover:-translate-y-1">
                <i class="fa-solid fa-file-contract absolute -right-6 -bottom-6 text-8xl text-orange-50 opacity-50 group-hover:opacity-100 transition-opacity group-hover:-rotate-12"></i>
                <div class="relative z-10 font-bold text-slate-500 text-sm uppercase tracking-wider mb-2">Pending Reports</div>
                <div class="relative z-10 flex items-baseline gap-3">
                    <h3 class="text-5xl font-extrabold text-slate-800">{{ $pendingReports }}</h3>
                </div>
                <div class="relative z-10 flex items-center mt-4 text-sm font-bold">
                    <span class="text-orange-600 bg-orange-100/80 px-2 py-0.5 rounded-md mr-2 flex items-center">
                        <i class="fa-regular fa-clock mr-1"></i> {{ $urgentReports }} Urgent
                    </span>
                    <span class="text-slate-400 font-medium">Require action</span>
                </div>
            </div>

            {{-- Completed Audits --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border-t-4 border-blue-500 relative overflow-hidden group transition-all hover:-translate-y-1">
                <i class="fa-solid fa-clipboard-list absolute -right-6 -bottom-6 text-8xl text-blue-50 opacity-50 group-hover:opacity-100 transition-opacity group-hover:rotate-12"></i>
                <div class="relative z-10 font-bold text-slate-500 text-sm uppercase tracking-wider mb-2">Completed Audits</div>
                <div class="relative z-10 flex items-baseline gap-3">
                    <h3 class="text-5xl font-extrabold text-slate-800">{{ $totalAudits }}</h3>
                </div>
                <div class="relative z-10 flex items-center mt-4 text-sm font-bold">
                    <span class="text-blue-600 bg-blue-100/80 px-2 py-0.5 rounded-md mr-2 flex items-center">
                        <i class="fa-solid fa-check-double mr-1"></i> {{ $complianceRate }}%
                    </span>
                    <span class="text-slate-400 font-medium">Compliance rate</span>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- LEFT COLUMN (2/3 width) --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- INCIDENT TRENDS CHART --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <h3 class="font-bold text-lg text-slate-800">Incident History</h3>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">5-Year Frequency Overview</p>
                        </div>
                        <div class="bg-white px-3 py-1 rounded-lg text-sm font-bold text-slate-600 border border-slate-200 shadow-sm">
                            <i class="fa-solid fa-chart-simple mr-2"></i> Annual View
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="relative h-72 w-full">
                            <canvas id="dashboardTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- RECENT ACTIVITY TABLE --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <h3 class="font-bold text-lg text-slate-800">Recent Activity</h3>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Latest logged incidents</p>
                        </div>
                        <a href="{{ route('incidents.index') }}" class="text-sm font-bold text-red-600 hover:text-red-700 bg-red-50 px-4 py-2 rounded-lg transition flex items-center">
                            View All <i class="fa-solid fa-chevron-right ml-2 text-xs"></i>
                        </a>
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-slate-100/80 text-xs uppercase tracking-wider text-slate-500 font-bold">
                            <tr>
                                <th class="px-6 py-4">Type</th>
                                <th class="px-6 py-4">Location</th>
                                <th class="px-6 py-4 text-right">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-sm font-medium">
                            @forelse($recentIncidents as $incident)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full {{ $incident->type == 'Structural' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center">
                                        <i class="fa-solid {{ $incident->type == 'Structural' ? 'fa-house-fire' : 'fa-fire' }}"></i>
                                    </div>
                                    <span class="text-slate-900">{{ $incident->type ?? 'General Incident' }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ Str::limit($incident->location, 30) }}</td>
                                <td class="px-6 py-4 text-right">
                                    {{-- MODAL TRIGGER BUTTON --}}
                                    <button onclick="openIncidentModal(this)"
                                            data-id="{{ $incident->id }}"
                                            data-type="{{ $incident->type }}"
                                            data-location="{{ $incident->location }}"
                                            data-date="{{ $incident->created_at->format('M d, Y • h:i A') }}"
                                            data-description="{{ $incident->description ?? 'No additional details provided.' }}"
                                            data-status="{{ $incident->status ?? 'Pending' }}"
                                            class="text-slate-400 hover:text-red-600 transition" title="View Details">
                                        <i class="fa-regular fa-eye text-lg"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-slate-400 italic">
                                    <i class="fa-regular fa-folder-open mb-2 text-2xl block opacity-50"></i>
                                    No recent incidents logged.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- RIGHT COLUMN (1/3 width) --}}
            <div class="space-y-6">
                
                {{-- HIGH RISK BARANGAYS --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
                     <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-red-50 to-white">
                        <h3 class="font-bold text-lg text-slate-800 flex items-center">
                            <i class="fa-solid fa-shield-heart text-red-500 mr-2"></i> High Activity Areas
                        </h3>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mt-1">Most Incidents & Failed Audits</p>
                    </div>
                    <div class="p-5 space-y-4">
                        @forelse($topRisks as $risk)
                            <div class="flex items-center p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-red-200 transition-colors">
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-2">
                                        <h5 class="font-bold text-slate-800 text-base">{{ $risk['name'] }}</h5>
                                        <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded-full">High Risk</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="flex items-center gap-2 text-xs font-medium text-slate-600 bg-white p-1.5 rounded-md border border-slate-200/50">
                                            <i class="fa-solid fa-fire text-orange-500"></i>
                                            <span>{{ $risk['incidents'] }} Incidents</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs font-medium text-slate-600 bg-white p-1.5 rounded-md border border-slate-200/50">
                                            <i class="fa-solid fa-clipboard-check text-red-500"></i>
                                            <span>{{ $risk['failed_audits'] }} Failed</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-slate-400 py-4">No data available yet.</div>
                        @endforelse
                    </div>
                </div>

                {{-- QUICK LINKS GRID --}}
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('incidents.index') }}" class="group bg-white p-4 rounded-2xl shadow-sm border border-slate-200/60 hover:border-red-300 hover:shadow-md transition-all flex flex-col items-center text-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-red-50 to-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="h-12 w-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center text-xl mb-3 relative z-10 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-folder-plus"></i>
                        </div>
                        <span class="font-bold text-slate-700 text-sm relative z-10">Incidents Record</span>
                    </a>
                    <a href="{{ route('site_audit.index') }}" class="group bg-white p-4 rounded-2xl shadow-sm border border-slate-200/60 hover:border-blue-300 hover:shadow-md transition-all flex flex-col items-center text-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="h-12 w-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl mb-3 relative z-10 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-clipboard-list"></i>
                        </div>
                        <span class="font-bold text-slate-700 text-sm relative z-10">Site Audit</span>
                    </a>
                    <a href="{{ route('analytics') }}" class="group bg-white p-4 rounded-2xl shadow-sm border border-slate-200/60 hover:border-purple-300 hover:shadow-md transition-all flex flex-col items-center text-center relative overflow-hidden col-span-2">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="flex items-center gap-3 relative z-10">
                            <div class="h-10 w-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-chart-pie"></i>
                            </div>
                             <span class="font-bold text-slate-700 text-sm">View Full Analytics Suite</span>
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- INCIDENT DETAIL MODAL --}}
    <div id="incidentModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeIncidentModal()"></div>
        
        {{-- Modal Panel --}}
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
                    
                    {{-- Header --}}
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800" id="modalTitle">Incident Details</h3>
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider" id="modalDate">Jan 01, 2024 • 12:00 PM</p>
                        </div>
                        <button type="button" onclick="closeIncidentModal()" class="text-slate-400 hover:text-slate-600 transition bg-white rounded-full p-1 hover:bg-slate-200">
                            <i class="fa-solid fa-xmark text-xl h-6 w-6 flex items-center justify-center"></i>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-6 space-y-4">
                        
                        {{-- Type & Status --}}
                        <div class="flex gap-4">
                            <div class="flex-1 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <span class="text-xs font-bold text-slate-400 uppercase">Type</span>
                                <div class="font-bold text-slate-800 flex items-center mt-1">
                                    <i class="fa-solid fa-fire text-red-500 mr-2"></i>
                                    <span id="modalType">Fire</span>
                                </div>
                            </div>
                            <div class="flex-1 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <span class="text-xs font-bold text-slate-400 uppercase">Status</span>
                                <div class="font-bold text-slate-800 flex items-center mt-1">
                                    <span id="modalStatusBadge" class="bg-orange-100 text-orange-700 text-xs px-2 py-0.5 rounded-full">Pending</span>
                                </div>
                            </div>
                        </div>

                        {{-- Location --}}
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Location</label>
                            <div class="text-sm font-semibold text-slate-700 bg-white border border-slate-200 p-3 rounded-xl flex items-start">
                                <i class="fa-solid fa-location-dot text-red-500 mt-1 mr-2"></i>
                                <span id="modalLocation">123 Main St.</span>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase block mb-1">Description</label>
                            <div class="text-sm text-slate-600 bg-slate-50 border border-slate-100 p-4 rounded-xl min-h-[80px]" id="modalDescription">
                                No details provided.
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-slate-100">
                        {{-- VIEW FULL REPORT BUTTON (Href updated via JS) --}}
                        <a href="#" id="modalFullLink" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition">
                            View Full Report
                        </a>
                        <button type="button" onclick="closeIncidentModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // CHART LOGIC
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('dashboardTrendChart').getContext('2d');
            
            let gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, '#DC2626'); 
            gradient.addColorStop(1, '#FECACA'); 

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($trendLabels), 
                    datasets: [{
                        label: 'Total Incidents',
                        data: @json($trendData),
                        backgroundColor: gradient,
                        borderRadius: 6,
                        barThickness: 'flex',
                        maxBarThickness: 50 
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1E293B',
                            padding: 12,
                            titleFont: { size: 13 },
                            bodyFont: { size: 14, weight: 'bold' },
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    return 'Year: ' + context[0].label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#F1F5F9', borderDash: [5, 5] },
                            ticks: { font: { size: 11, weight: 'bold' }, color: '#94A3B8' },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 12, weight: 'bold' }, color: '#64748B' },
                            border: { display: false }
                        }
                    }
                }
            });
        });

        // MODAL LOGIC
        function openIncidentModal(button) {
            // 1. Get data from attributes
            const id = button.getAttribute('data-id'); // <--- CRITICAL: Get ID
            const type = button.getAttribute('data-type');
            const location = button.getAttribute('data-location');
            const date = button.getAttribute('data-date');
            const description = button.getAttribute('data-description');
            const status = button.getAttribute('data-status');

            // 2. Populate Modal Elements
            document.getElementById('modalType').innerText = type;
            document.getElementById('modalLocation').innerText = location;
            document.getElementById('modalDate').innerText = date;
            document.getElementById('modalDescription').innerText = description;
            
            // 3. Status Badge Styling
            const statusBadge = document.getElementById('modalStatusBadge');
            statusBadge.innerText = status;
            
            // Reset classes
            statusBadge.className = 'text-xs px-2 py-0.5 rounded-full font-bold uppercase tracking-wider ';
            
            // Apply colors based on status
            if(status === 'Resolved' || status === 'Case Closed') {
                statusBadge.classList.add('bg-green-100', 'text-green-700');
            } else if (status === 'Pending' || status === 'Under Investigation') {
                statusBadge.classList.add('bg-orange-100', 'text-orange-700');
            } else {
                statusBadge.classList.add('bg-slate-100', 'text-slate-700');
            }

            // 4. UPDATE LINK TO FULL REPORT
            // Uses the ID to construct the URL for the show route
            document.getElementById('modalFullLink').href = `/incidents/${id}`; 

            // 5. Show Modal
            document.getElementById('incidentModal').classList.remove('hidden');
        }

        function closeIncidentModal() {
            document.getElementById('incidentModal').classList.add('hidden');
        }
    </script>
</x-layout>