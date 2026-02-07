<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\SiteAudit;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Define Allowed Types Globally for consistency
        $allowedTypes = ['Structural', 'Non-Structural', 'Vehicular'];

        // --- 1. SETUP YEARS & FILTER ---
        
        // Get all unique years from incidents to populate the dropdown
        $availableYears = Incident::selectRaw('YEAR(incident_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [date('Y')];
        }

        $selectedYear = $request->input('year', $availableYears[0]);
        $previousYear = $selectedYear - 1;


        // --- 2. FIRE INCIDENTS TREND (Comparison Logic) ---

        // Passed $allowedTypes to the closure with 'use'
        $getMonthlyData = function ($year) use ($allowedTypes) {
            $counts = Incident::selectRaw('MONTH(incident_date) as month, COUNT(*) as total')
                ->whereYear('incident_date', $year)
                ->whereIn('type', $allowedTypes) // <--- ADDED FILTER
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            $data = [];
            for ($i = 1; $i <= 12; $i++) {
                $data[] = $counts[$i] ?? 0;
            }
            return $data;
        };

        $currentYearTrend = $getMonthlyData($selectedYear);
        $previousYearTrend = $getMonthlyData($previousYear);


        // --- 3. OTHER INCIDENT CHARTS (Filtered by Selected Year) ---

        // A. Incidents by Type
        $incidentTypes = Incident::select('type', DB::raw('count(*) as total'))
            ->whereYear('incident_date', $selectedYear)
            ->whereIn('type', $allowedTypes) // <--- ADDED FILTER
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();
        
        $typeLabels = array_keys($incidentTypes);
        $typeData = array_values($incidentTypes);

        // B. Incident Density by Barangay (Top 5)
        $topBarangays = Incident::select('location', DB::raw('count(*) as total'))
            ->whereYear('incident_date', $selectedYear)
            ->whereIn('type', $allowedTypes) // <--- ADDED FILTER
            ->groupBy('location')
            ->orderByDesc('total')
            ->take(5)
            ->get();


        // --- 4. AUDIT DATA (Unchanged as this is for Site Audits, not Incidents) ---

        $auditRisks = SiteAudit::select('risk_level', DB::raw('count(*) as total'))
            ->groupBy('risk_level')
            ->pluck('total', 'risk_level')
            ->toArray();

        $riskData = [
            $auditRisks['Low'] ?? 0,
            $auditRisks['Medium'] ?? 0,
            $auditRisks['High'] ?? 0,
        ];

        // B. Most Common Hazards
        $allHazards = SiteAudit::pluck('hazards')->toArray(); 
        $hazardCounts = [];

        foreach ($allHazards as $hazardString) {
            if (!$hazardString) continue;
            
            $items = preg_split('/[,\n]+/', $hazardString);
            
            foreach ($items as $item) {
                $clean = trim(ucfirst(strtolower($item))); 
                if (strlen($clean) > 2 && $clean !== 'Nan') { 
                    $hazardCounts[$clean] = ($hazardCounts[$clean] ?? 0) + 1;
                }
            }
        }
        arsort($hazardCounts);
        $topHazards = array_slice($hazardCounts, 0, 5);


        // --- 5. RETURN VIEW ---
        return view('analytics', compact(
            'availableYears',
            'selectedYear',
            'previousYear',
            'currentYearTrend',
            'previousYearTrend',
            'typeLabels',
            'typeData',
            'topBarangays',
            'riskData',
            'topHazards'
        ));
    }
}