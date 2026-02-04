<x-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Risk Identification</h2>
        <p class="text-gray-500 text-sm">Classification of barangays based on incident history and audit compliance.</p>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center">
            <div class="h-12 w-12 rounded-lg bg-red-50 flex items-center justify-center text-red-600 mr-4">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">High Risk Areas</p>
                <h3 class="text-3xl font-extrabold text-gray-900">{{ $highRiskCount }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center">
            <div class="h-12 w-12 rounded-lg bg-green-50 flex items-center justify-center text-green-600 mr-4">
                <i class="fa-solid fa-shield-check text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Low Risk Areas</p>
                <h3 class="text-3xl font-extrabold text-gray-900">{{ $lowRiskCount }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center">
            <div class="h-12 w-12 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 mr-4">
                <i class="fa-solid fa-map-location-dot text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Monitored Areas</p>
                <h3 class="text-3xl font-extrabold text-gray-900">{{ count($data) }}</h3>
            </div>
        </div>
    </div>

    {{-- CLASSIFICATION TABLE --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        
        <div class="p-5 border-b border-gray-100 bg-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h3 class="font-bold text-gray-800">Barangay Risk Status</h3>
            <form action="{{ route('high_risk.index') }}" method="GET" class="w-full md:w-1/3 relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search barangay..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 shadow-sm">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-white border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold">
                    <tr>
                        <th class="px-6 py-4">Barangay</th>
                        <th class="px-6 py-4 text-center">Fire Incidents</th>
                        <th class="px-6 py-4 text-center">Failed Audits</th>
                        <th class="px-6 py-4 text-center">Risk Classification</th>
                        <th class="px-6 py-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($data as $row)
                        {{-- Row is clickable, passes 'analysis' string to modal --}}
                        <tr onclick="openAnalyticsModal(
                            '{{ $row['name'] }}', 
                            '{{ $row['status'] }}', 
                            '{{ $row['incidents'] }}', 
                            '{{ $row['high_risk_count'] }}', 
                            '{{ $row['total_audits'] }}',
                            '{{ addslashes($row['analysis']) }}'
                        )" class="hover:bg-blue-50 transition cursor-pointer group">
                            
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $row['name'] }}</td>
                            <td class="px-6 py-4 text-center font-bold text-gray-800">{{ $row['incidents'] }}</td>
                            
                            <td class="px-6 py-4 text-center">
                                @if($row['high_risk_count'] > 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-red-50 text-red-700 border border-red-100">
                                        <i class="fa-solid fa-xmark mr-1.5 text-xs"></i><span class="font-bold">{{ $row['high_risk_count'] }}</span>
                                    </span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide {{ $row['status_color'] }}">
                                    @if($row['status'] === 'High') <i class="fa-solid fa-triangle-exclamation mr-1"></i> @else <i class="fa-solid fa-check-circle mr-1"></i> @endif
                                    {{ $row['status'] }} Risk
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center text-gray-400 group-hover:text-blue-600">
                                <i class="fa-solid fa-chart-pie text-lg"></i>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-magnifying-glass text-4xl text-gray-300 mb-3 opacity-50"></i>
                                    <p class="font-medium text-gray-500">No matching records found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ANALYTICS MODAL --}}
    <div id="analyticsModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity" onclick="closeAnalyticsModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-lg">
                    
                    {{-- Header --}}
                    <div id="modalHeader" class="px-6 py-5 border-b border-gray-100 flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900" id="modalBarangay">Barangay Name</h3>
                            <span id="modalBadge" class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wide">
                                Risk Level
                            </span>
                        </div>
                        <button onclick="closeAnalyticsModal()" class="text-gray-400 hover:text-gray-500 bg-gray-100 rounded-full p-1"><i class="fa-solid fa-xmark text-lg w-6 h-6 flex items-center justify-center"></i></button>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-6">
                        
                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-3 gap-3 mb-6">
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 text-center">
                                <span class="block text-2xl font-bold text-gray-800" id="modalIncidents">0</span>
                                <span class="text-[10px] text-gray-500 uppercase font-bold">Fire Incidents</span>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 text-center">
                                <span class="block text-2xl font-bold text-red-600" id="modalFailed">0</span>
                                <span class="text-[10px] text-gray-500 uppercase font-bold">Failed Audits</span>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 text-center">
                                <span class="block text-2xl font-bold text-blue-600" id="modalTotal">0</span>
                                <span class="text-[10px] text-gray-500 uppercase font-bold">Total Insp.</span>
                            </div>
                        </div>

                        {{-- AI / Descriptive Analysis Box --}}
                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 relative overflow-hidden">
                            <i class="fa-solid fa-robot absolute -right-3 -bottom-3 text-6xl text-blue-200 opacity-50"></i>
                            
                            <h4 class="text-sm font-bold text-blue-900 uppercase tracking-wide mb-2 relative z-10">
                                <i class="fa-solid fa-magnifying-glass-chart mr-2"></i> Risk Analysis
                            </h4>
                            
                            {{-- THE SENTENCE APPEARS HERE --}}
                            <p id="modalAnalysis" class="text-sm text-blue-800 font-medium leading-relaxed relative z-10">
                                Loading analysis...
                            </p>
                        </div>

                        <div class="mt-4 text-xs text-gray-500 italic text-center">
                            * Automated analysis based on structural data and audit checklist results.
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openAnalyticsModal(name, status, incidents, failed, total, analysis) {
            document.getElementById('modalBarangay').innerText = name;
            document.getElementById('modalIncidents').innerText = incidents;
            document.getElementById('modalFailed').innerText = failed;
            document.getElementById('modalTotal').innerText = total;
            document.getElementById('modalAnalysis').innerText = analysis;

            // Set Badge Color
            const badge = document.getElementById('modalBadge');
            if(status === 'High') {
                badge.className = "mt-1 inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wide bg-red-100 text-red-700";
                badge.innerText = "High Risk";
            } else {
                badge.className = "mt-1 inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wide bg-green-100 text-green-700";
                badge.innerText = "Low Risk";
            }

            document.getElementById('analyticsModal').classList.remove('hidden');
        }

        function closeAnalyticsModal() {
            document.getElementById('analyticsModal').classList.add('hidden');
        }
    </script>

</x-layout>