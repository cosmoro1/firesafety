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
            <button class="bg-white border border-gray-200 text-gray-600 px-4 py-2 rounded-lg font-medium shadow-sm flex items-center hover:bg-gray-50 transition">
                <i class="fa-regular fa-calendar mr-2"></i> Last 30 Days
            </button>
            <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm transition flex items-center">
                <i class="fa-solid fa-download mr-2"></i> Export Report
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-500 text-sm font-medium">Total Incidents (30 Days)</h3>
                <span class="p-2 bg-red-50 text-red-600 rounded-lg"><i class="fa-solid fa-chart-line"></i></span>
            </div>
            <div class="flex items-end justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">24</h2>
                    <p class="text-red-600 text-sm flex items-center mt-1">
                        <i class="fa-solid fa-arrow-trend-up mr-1"></i> 15% Increase
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-500 text-sm font-medium">Avg. Compliance Rate</h3>
                <span class="p-2 bg-green-50 text-green-600 rounded-lg"><i class="fa-solid fa-clipboard-check"></i></span>
            </div>
             <div class="flex items-end justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">82.5%</h2>
                    <p class="text-green-600 text-sm flex items-center mt-1">
                        <i class="fa-solid fa-arrow-trend-up mr-1"></i> 5% Improvement
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-500 text-sm font-medium">High-Risk Locations</h3>
                <span class="p-2 bg-orange-50 text-orange-600 rounded-lg"><i class="fa-solid fa-triangle-exclamation"></i></span>
            </div>
             <div class="flex items-end justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">15</h2>
                    <p class="text-gray-500 text-sm flex items-center mt-1">Identified this month</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <div class="space-y-8">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="font-bold text-gray-800 mb-6">Incidents by Type</h3>
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
                            <th class="px-6 py-3 text-center">Risk Level</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">Barangay Parian</td>
                            <td class="px-6 py-4 text-center font-bold">8</td>
                            <td class="px-6 py-4 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">High</span></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">Barangay Real</td>
                            <td class="px-6 py-4 text-center font-bold">6</td>
                            <td class="px-6 py-4 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Medium</span></td>
                        </tr>
                         <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">Barangay Turbina</td>
                            <td class="px-6 py-4 text-center font-bold">5</td>
                            <td class="px-6 py-4 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Medium</span></td>
                        </tr>
                         <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">Barangay Canlubang</td>
                            <td class="px-6 py-4 text-center font-bold">3</td>
                            <td class="px-6 py-4 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Low</span></td>
                        </tr>
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
                    <div class="flex items-center p-3 bg-red-50 border border-red-100 rounded-lg">
                        <div class="h-8 w-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center mr-3 font-bold">1</div>
                        <div class="flex-1">
                            <h5 class="text-sm font-semibold text-gray-900">Faulty Electrical Wiring</h5>
                            <p class="text-xs text-gray-500">Found in 32 establishments</p>
                        </div>
                        <i class="fa-solid fa-bolt text-red-400"></i>
                    </div>
                    <div class="flex items-center p-3 bg-orange-50 border border-orange-100 rounded-lg">
                        <div class="h-8 w-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mr-3 font-bold">2</div>
                        <div class="flex-1">
                            <h5 class="text-sm font-semibold text-gray-900">Blocked Fire Exits</h5>
                            <p class="text-xs text-gray-500">Found in 24 establishments</p>
                        </div>
                        <i class="fa-solid fa-door-closed text-orange-400"></i>
                    </div>
                    <div class="flex items-center p-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                        <div class="h-8 w-8 rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center mr-3 font-bold">3</div>
                        <div class="flex-1">
                            <h5 class="text-sm font-semibold text-gray-900">Expired Fire Extinguishers</h5>
                            <p class="text-xs text-gray-500">Found in 18 establishments</p>
                        </div>
                        <i class="fa-solid fa-fire-extinguisher text-yellow-500"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. Incidents by Type (Bar Chart)
            const ctx1 = document.getElementById('incidentTypeChart').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: ['Residential', 'Grass/Rubbish', 'Commercial', 'Industrial', 'Vehicle'],
                    datasets: [{
                        label: '# of Incidents',
                        data: [12, 6, 4, 2, 1],
                        backgroundColor: [
                            '#EF4444', // Red
                            '#F97316', // Orange
                            '#EAB308', // Yellow
                            '#3B82F6', // Blue
                            '#6B7280'  // Gray
                        ],
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 2. Audit Risk Overview (Doughnut Chart)
            const ctx2 = document.getElementById('auditRiskChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Low Risk', 'Medium Risk', 'High Risk'],
                    datasets: [{
                        data: [46, 28, 15],
                        backgroundColor: [
                            '#22c55e', // Green
                            '#f97316', // Orange
                            '#ef4444'  // Red
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, padding: 20 }
                        }
                    }
                }
            });

        });
    </script>
</x-layout>