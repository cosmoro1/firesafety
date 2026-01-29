<x-layout>
    <div class="space-y-6">
        
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="font-bold text-xl text-slate-800">Incident Trends</h3>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Annual Frequency Analysis (2025)</p>
                        </div>
                        <div class="bg-slate-100 px-3 py-1 rounded-lg text-sm font-bold text-slate-600 border border-slate-200">
                            <i class="fa-regular fa-calendar mr-2"></i> Year View
                        </div>
                    </div>
                    <div class="flex items-end justify-between h-56 gap-2 mt-6 px-2 border-b border-slate-100 pb-2 relative">
                         <div class="w-full bg-slate-100 rounded-t-sm h-[30%] hover:bg-red-300 transition-all"></div>
                        <div class="w-full bg-slate-100 rounded-t-sm h-[45%] hover:bg-red-300 transition-all"></div>
                        <div class="w-full bg-slate-100 rounded-t-sm h-[80%] bg-gradient-to-t from-red-600 to-red-400 shadow-lg shadow-red-200/50"></div>
                        <div class="w-full bg-slate-100 rounded-t-sm h-[60%] hover:bg-red-300 transition-all"></div>
                        <div class="w-full bg-slate-100 rounded-t-sm h-[40%] hover:bg-red-300 transition-all"></div>
                        <div class="w-full bg-slate-100 rounded-t-sm h-[25%] hover:bg-red-300 transition-all"></div>
                        <div class="w-full bg-slate-100 rounded-t-sm h-[35%] hover:bg-red-300 transition-all"></div>
                        <div class="w-full bg-slate-100 rounded-t-sm h-[50%] hover:bg-red-300 transition-all"></div>
                        <div class="w-full bg-slate-100 rounded-t-sm h-[65%] hover:bg-red-300 transition-all"></div>
                        <div class="w-full bg-slate-100 rounded-t-sm h-[55%] hover:bg-red-300 transition-all"></div>
                    </div>
                     <div class="flex justify-between text-[10px] font-bold text-slate-400 mt-3 px-1 uppercase tracking-wider">
                        <span>Jan</span><span>Mar</span><span>May</span><span>Jul</span><span>Sep</span><span>Nov</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <h3 class="font-bold text-lg text-slate-800">Recent Activity</h3>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Latest logged incidents</p>
                        </div>
                        <a href="/incidents" class="text-sm font-bold text-red-600 hover:text-red-700 bg-red-50 px-4 py-2 rounded-lg transition flex items-center">View All <i class="fa-solid fa-chevron-right ml-2 text-xs"></i></a>
                    </div>
                    <table class="w-full text-left">
    {{-- TABLE HEADER --}}
    <thead class="bg-slate-100/80 text-xs uppercase tracking-wider text-slate-500 font-bold">
        <tr>
            <th class="px-6 py-4">Type</th>
            <th class="px-6 py-4">Location</th>
            <th class="px-6 py-4 text-right">Details</th>
        </tr>
    </thead>

    {{-- TABLE BODY --}}
    <tbody class="divide-y divide-slate-50 text-sm font-medium">
        @forelse($recentIncidents as $incident)
        <tr class="hover:bg-slate-50 transition-colors">
            
            {{-- TYPE & ICON --}}
            <td class="px-6 py-4 flex items-center gap-3">
                <div class="h-8 w-8 rounded-full {{ $incident->type == 'Structural' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center">
                    <i class="fa-solid {{ $incident->type == 'Structural' ? 'fa-house-fire' : 'fa-fire' }}"></i>
                </div>
                <span class="text-slate-900">{{ $incident->type ?? 'General Incident' }}</span>
            </td>

            {{-- LOCATION --}}
            <td class="px-6 py-4 text-slate-500">{{ Str::limit($incident->location, 30) }}</td>

            {{-- ACTIONS / DETAILS --}}
            <td class="px-6 py-4 text-right">
                <button class="text-slate-400 hover:text-red-600 transition" title="View Details">
                    <i class="fa-regular fa-eye text-lg"></i>
                </button>
            </td>
        </tr>
        @empty
        <tr>
            {{-- Adjusted colspan to 3 since we removed a column --}}
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

            <div class="space-y-6">
                
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
                     <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-red-50 to-white">
                        <h3 class="font-bold text-lg text-slate-800 flex items-center">
                            <i class="fa-solid fa-shield-heart text-red-500 mr-2"></i> High Risk Barangays
                        </h3>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mt-1">Based on Incidents & Audit Compliance</p>
                    </div>
                    <div class="p-5 space-y-5">
                        @forelse($topRisks as $index => $risk)
                            @if($loop->first)
                                <div class="relative p-4 bg-white rounded-xl shadow-sm border-2 border-red-100 group hover:border-red-300 transition-all">
                                    <div class="absolute top-0 right-0 bg-red-100 text-red-700 text-xs font-extrabold px-3 py-1 rounded-bl-xl rounded-tr-lg">#1 Ranked</div>
                                    <div class="mb-4">
                                        <h4 class="text-lg font-extrabold text-slate-800">{{ $risk['name'] }}</h4>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="bg-slate-50 p-2 rounded-lg border border-slate-100 flex items-center gap-3">
                                            <div class="bg-red-100 text-red-600 h-8 w-8 rounded-md flex items-center justify-center">
                                                <i class="fa-solid fa-fire font-bold"></i>
                                            </div>
                                            <div>
                                                <span class="block text-xl font-extrabold text-slate-800">{{ $risk['incidents'] }}</span>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase">Past Incidents</span>
                                            </div>
                                        </div>
                                        <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                            <div class="flex justify-between items-center mb-1.5">
                                                 <span class="text-[10px] font-bold text-slate-400 uppercase"><i class="fa-solid fa-clipboard-check mr-1"></i> Failed Audits</span>
                                            </div>
                                            <div class="w-full bg-slate-200 rounded-full h-3.5 shadow-inner">
                                                @php $failRate = $risk['audits'] > 0 ? ($risk['failed_audits'] / $risk['audits']) * 100 : 0; @endphp
                                                <div class="bg-red-500 h-3.5 rounded-full relative group" style="width: {{ $failRate }}%">
                                                    <span class="absolute inset-0 flex items-center justify-end pr-1 text-[9px] font-bold text-white">{{ $risk['failed_audits'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center p-3 bg-slate-50 rounded-lg border border-slate-100">
                                    <div class="h-10 w-10 bg-white rounded-lg flex items-center justify-center font-extrabold text-slate-400 border border-slate-100 shadow-sm mr-4">#{{ $index + 1 }}</div>
                                    <div class="flex-1">
                                        <h5 class="font-bold text-slate-800">{{ $risk['name'] }}</h5>
                                        <div class="flex items-center gap-4 mt-1 text-xs font-medium">
                                            <span class="text-slate-500 flex items-center"><i class="fa-solid fa-fire text-orange-400 mr-1.5"></i> {{ $risk['incidents'] }} Incidents</span>
                                             <span class="flex items-center gap-1.5">
                                                <i class="fa-solid fa-clipboard text-orange-400"></i>
                                                <span class="text-slate-400">{{ $risk['failed_audits'] }} Failed</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="text-center text-slate-400 py-4">No high risk data available yet.</div>
                        @endforelse
                    </div>
                </div>

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
</x-layout>