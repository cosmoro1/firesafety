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
                <button class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white text-sm font-bold px-6 py-2 rounded-xl shadow-md shadow-red-200 transition-all transform hover:scale-105 flex items-center">
                    <i class="fa-solid fa-fire-flame-curved mr-2 animate-pulse"></i> Report Incident
                </button>
            </div>
        </div>

        {{-- KPI CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                        <a href="/incidents" class="text-sm font-bold text-red-600 hover:text-red-700 bg-red-50 px-4 py-2 rounded-lg transition flex items-center">
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
                                    <button class="text-slate-400 hover:text-red-600 transition" title="View Details">
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
                
                {{-- HIGH RISK BARANGAYS (CLEAN LIST) --}}
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
                                {{-- No Numbering, Just Content --}}
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
                    <a href="#" class="group bg-white p-4 rounded-2xl shadow-sm border border-slate-200/60 hover:border-red-300 hover:shadow-md transition-all flex flex-col items-center text-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-red-50 to-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="h-12 w-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center text-xl mb-3 relative z-10 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-folder-plus"></i>
                        </div>
                        <span class="font-bold text-slate-700 text-sm relative z-10">Incidents Record</span>
                    </a>
                    <a href="/site-audit" class="group bg-white p-4 rounded-2xl shadow-sm border border-slate-200/60 hover:border-blue-300 hover:shadow-md transition-all flex flex-col items-center text-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="h-12 w-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl mb-3 relative z-10 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-clipboard-list"></i>
                        </div>
                        <span class="font-bold text-slate-700 text-sm relative z-10">Site Audit</span>
                    </a>
                    <a href="/analytics" class="group bg-white p-4 rounded-2xl shadow-sm border border-slate-200/60 hover:border-purple-300 hover:shadow-md transition-all flex flex-col items-center text-center relative overflow-hidden col-span-2">
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

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('dashboardTrendChart').getContext('2d');
            
            // Gradient for the bars
            let gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, '#DC2626'); // Red-600
            gradient.addColorStop(1, '#FECACA'); // Red-200

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
                            grid: {
                                color: '#F1F5F9',
                                borderDash: [5, 5]
                            },
                            ticks: {
                                font: { size: 11, weight: 'bold' },
                                color: '#94A3B8'
                            },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 12, weight: 'bold' },
                                color: '#64748B' 
                            },
                            border: { display: false }
                        }
                    }
                }
            });
        });
    </script>
</x-layout>