<x-layout>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Analytics & Reports</h2>
            <p class="text-gray-500 text-sm">Data-driven insights on fire safety operations and compliance</p>
        </div>
        <div class="flex items-center gap-2">
             <button class="bg-white border border-gray-200 text-gray-600 px-4 py-2 rounded-lg font-medium shadow-sm flex items-center hover:bg-gray-50 transition">
                <i class="fa-solid fa-filter mr-2"></i> Filter
            </button>
            
            {{-- YEAR DISPLAY BUTTON --}}
            <button class="bg-white border border-gray-200 text-gray-600 px-4 py-2 rounded-lg font-medium shadow-sm flex items-center hover:bg-gray-50 transition">
                <i class="fa-regular fa-calendar mr-2"></i> {{ $selectedYear }}
            </button>

            <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center">
                <i class="fa-solid fa-download mr-2"></i> Export Report
            </button>
        </div>
    </div>

    {{-- FIRE TRENDS CHART WITH YEAR FILTER --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">Fire Incidents Trend</h3>
                <p class="text-sm text-gray-500">Comparison: {{ $selectedYear }} vs {{ $previousYear }}</p>
            </div>
            
            {{-- YEAR FILTER FORM --}}
            <form method="GET" action="{{ route('analytics') }}">
                <select name="year" onchange="this.form.submit()" class="text-sm border-gray-200 rounded-lg text-gray-600 focus:border-red-500 focus:ring-red-500 cursor-pointer">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="relative h-80 w-full">
            <canvas id="fireTrendChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <div class="space-y-8">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-6">Incidents by Type ({{ $selectedYear }})</h3>
                <div class="relative h-64 w-full">
                    <canvas id="incidentTypeChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                 <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Incident Density by Barangay</h3>
                    <select class="text-sm border-gray-200 rounded-lg text-gray-600">
                        <option>Top 5</option>
                    </select>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 font-semibold uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Barangay</th>
                            <th class="px-6 py-3 text-center">Incidents</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topBarangays as $bgy)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $bgy->location }}</td>
                            <td class="px-6 py-4 text-center font-bold">{{ $bgy->total }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-gray-500">No data available for {{ $selectedYear }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-8">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-6">Audit Risk Overview</h3>
                <div class="relative h-64 w-full flex justify-center">
                    <canvas id="auditRiskChart"></canvas>
                </div>
            </div>

             <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-4">Most Common Hazards Found</h3>
                <div class="space-y-3">
                    @php $i = 1; @endphp
                    @forelse($topHazards as $hazard => $count)
                    <div class="flex items-center p-3 {{ $i == 1 ? 'bg-red-50 border border-red-100' : ($i == 2 ? 'bg-orange-50 border border-orange-100' : 'bg-yellow-50 border border-yellow-100') }} rounded-lg">
                        <div class="h-8 w-8 rounded-full {{ $i == 1 ? 'bg-red-100 text-red-600' : ($i == 2 ? 'bg-orange-100 text-orange-600' : 'bg-yellow-100 text-yellow-700') }} flex items-center justify-center mr-3 font-bold">
                            {{ $i }}
                        </div>
                        <div class="flex-1">
                            <h5 class="text-sm font-semibold text-gray-900">{{ $hazard }}</h5>
                            <p class="text-xs text-gray-500">Found in {{ $count }} establishments</p>
                        </div>
                        <i class="fa-solid fa-triangle-exclamation {{ $i == 1 ? 'text-red-400' : ($i == 2 ? 'text-orange-400' : 'text-yellow-500') }}"></i>
                    </div>
                    @php $i++; @endphp
                    @empty
                    <div class="text-center py-4 text-gray-500">No hazard data available yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. Fire Incidents Trend (COMPARISON CHART)
            const ctxTrend = document.getElementById('fireTrendChart').getContext('2d');
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [
                        {
                            label: '{{ $selectedYear }}', // Current Selection
                            data: @json($currentYearTrend), // <--- FIXED VARIABLE
                            borderColor: '#DC2626', // Red
                            backgroundColor: 'rgba(220, 38, 38, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#DC2626',
                            pointRadius: 4
                        },
                        {
                            label: '{{ $previousYear }}', // Comparison Year
                            data: @json($previousYearTrend), // <--- FIXED VARIABLE
                            borderColor: '#9CA3AF', // Gray
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            borderDash: [5, 5], // Dashed Line
                            tension: 0.4,
                            pointRadius: 0, // Hide points for cleaner look
                            pointHoverRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: true, position: 'top', align: 'end' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. Incidents by Type
            const ctx1 = document.getElementById('incidentTypeChart').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: @json($typeLabels),
                    datasets: [{
                        label: '# of Incidents',
                        data: @json($typeData),
                        backgroundColor: ['#EF4444', '#F97316', '#EAB308', '#3B82F6', '#6B7280'],
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 3. Audit Risk Overview
            const ctx2 = document.getElementById('auditRiskChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Low Risk', 'Medium Risk', 'High Risk'],
                    datasets: [{
                        data: @json($riskData),
                        backgroundColor: ['#22c55e', '#f97316', '#ef4444'],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                    }
                }
            });

        });
    </script>
</x-layout>