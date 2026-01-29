<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\SiteAudit;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        $currentYear = date('Y');

        // --- 1. KPI CARDS DATA ---
        $totalIncidents = Incident::whereYear('created_at', $currentYear)->count();
        
        // Growth Calculation
        $lastYearIncidents = Incident::whereYear('created_at', $currentYear - 1)->count();
        $incidentGrowth = $lastYearIncidents > 0 
            ? round((($totalIncidents - $lastYearIncidents) / $lastYearIncidents) * 100) 
            : 0;
        
        // Compliance Rate
        $totalAudits = SiteAudit::count();
        $passedAudits = SiteAudit::where('compliance_score', '>=', 80)->count();
        $complianceRate = $totalAudits > 0 ? round(($passedAudits / $totalAudits) * 100, 1) : 0;

        // High Risk Count
        $highRiskCount = SiteAudit::where('risk_level', 'High')->count();


        // --- 2. CHARTS DATA ---

        // A. FIRE INCIDENTS TREND (Monthly for Current Year)
        $incidentsByMonth = Incident::select(
            DB::raw('COUNT(id) as count'), 
            DB::raw('MONTH(created_at) as month')
        )
        ->whereYear('created_at', $currentYear)
        ->groupBy('month')
        ->pluck('count', 'month')
        ->toArray();

        // Ensure all 12 months exist (fill with 0 if empty)
        $monthlyTrend = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyTrend[] = $incidentsByMonth[$i] ?? 0;
        }

        // B. INCIDENTS BY TYPE
        $incidentTypes = Incident::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();
        
        $typeLabels = array_keys($incidentTypes);
        $typeData = array_values($incidentTypes);

        // C. AUDIT RISK OVERVIEW
        $auditRisks = SiteAudit::select('risk_level', DB::raw('count(*) as total'))
            ->groupBy('risk_level')
            ->pluck('total', 'risk_level')
            ->toArray();

        // Ensure specific order for chart colors: Low, Medium, High
        $riskData = [
            $auditRisks['Low'] ?? 0,
            $auditRisks['Medium'] ?? 0,
            $auditRisks['High'] ?? 0,
        ];


        // --- 3. TABLES & LISTS ---

        // A. MOST COMMON HAZARDS (Text Analysis)
        $allHazards = SiteAudit::pluck('hazards')->toArray();
        $hazardCounts = [];

        foreach ($allHazards as $hazardString) {
            if (!$hazardString) continue;
            // Split by comma or newline
            $items = preg_split('/[,\n]+/', $hazardString);
            foreach ($items as $item) {
                $clean = trim(ucfirst(strtolower($item))); 
                if (strlen($clean) > 2) { 
                    $hazardCounts[$clean] = ($hazardCounts[$clean] ?? 0) + 1;
                }
            }
        }
        arsort($hazardCounts);
        $topHazards = array_slice($hazardCounts, 0, 5);

        // B. INCIDENT DENSITY BY BARANGAY (Top 5)
        $topBarangays = Incident::select('location', DB::raw('count(*) as total'))
            ->groupBy('location')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('analytics', compact(
            'totalIncidents', 'incidentGrowth', 'complianceRate', 'highRiskCount',
            'monthlyTrend', 'typeLabels', 'typeData', 'riskData', 
            'topHazards', 'topBarangays'
        ));
    }
}