<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\SiteAudit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. SECURITY CHECK
        if (auth()->user()->role !== 'admin') {
            return redirect('/training');
        }

        // 2. STATS CALCULATION
        $totalIncidents = Incident::count();
        
        // Percentage Growth (Using incident_date)
        $lastYearIncidents = Incident::whereYear('incident_date', Carbon::now()->subYear()->year)->count();
        $thisYearIncidents = Incident::whereYear('incident_date', Carbon::now()->year)->count();
        
        $incidentGrowth = $lastYearIncidents > 0 
            ? round((($thisYearIncidents - $lastYearIncidents) / $lastYearIncidents) * 100) 
            : 100;

        $pendingReports = Incident::where('status', 'Pending')->count();
        $urgentReports = Incident::where('alarm_level', 'High')->count(); 
        
        $totalAudits = SiteAudit::count();
        $passedAudits = SiteAudit::where('compliance_score', '>=', 80)->count();
        $complianceRate = $totalAudits > 0 ? round(($passedAudits / $totalAudits) * 100) : 0;

        // 3. RECENT ACTIVITY
        $recentIncidents = Incident::latest()->take(3)->get();

        // 4. HIGH RISK BARANGAYS (Simplified: Sort by Incidents, then Failed Audits)
        $barangays = [
            'Bagong Kalsada', 'Bañadero', 'Banlic', 'Barandal', 'Barangay 1 (Poblacion)',
            'Barangay 2 (Poblacion)', 'Barangay 3 (Poblacion)', 'Barangay 4 (Poblacion)',
            'Barangay 5 (Poblacion)', 'Barangay 6 (Poblacion)', 'Barangay 7 (Poblacion)',
            'Batino', 'Bubuyan', 'Bucal', 'Bunggo', 'Burol', 'Camaligan', 'Canlubang',
            'Halang', 'Hornalan', 'Kay-Anlog', 'La Mesa', 'Laguerta', 'Lawa', 'Lecheria',
            'Lingga', 'Looc', 'Mabato', 'Majada Labas', 'Makiling', 'Mapagong', 'Masili',
            'Maunong', 'Mayapa', 'Paciano Rizal', 'Palingon', 'Palo-Alto', 'Pansol', 'Parian',
            'Prinza', 'Punta', 'Puting Lupa', 'Real', 'Saimsim', 'Sampiruhan', 'San Cristobal',
            'San Jose', 'San Juan', 'Sirang Lupa', 'Sucol', 'Tulo', 'Turbina', 'Ulango', 'Uwisan'
        ];

        $riskData = [];
        foreach ($barangays as $barangay) {
            $incidents = Incident::where('location', 'LIKE', "%$barangay%")->count();
            $failedAudits = SiteAudit::where('barangay', $barangay)->where('risk_level', 'High')->count();
            
            // Only add if there is activity
            if ($incidents > 0 || $failedAudits > 0) {
                $riskData[] = [
                    'name' => $barangay,
                    'incidents' => $incidents,
                    'audits' => SiteAudit::where('barangay', $barangay)->count(),
                    'failed_audits' => $failedAudits,
                ];
            }
        }
        
        // SORT LOGIC: Primary = Most Incidents, Secondary = Most Failed Audits
        usort($riskData, function($a, $b) {
            if ($b['incidents'] == $a['incidents']) {
                return $b['failed_audits'] <=> $a['failed_audits'];
            }
            return $b['incidents'] <=> $a['incidents'];
        });

        $topRisks = array_slice($riskData, 0, 3);


        // 5. ANNUAL TREND (5-YEAR HISTORY)
        $currentYear = date('Y');
        $years = range($currentYear - 4, $currentYear);
        
        $yearlyData = Incident::selectRaw('YEAR(incident_date) as year, COUNT(*) as count')
            ->whereYear('incident_date', '>=', $currentYear - 4)
            ->groupBy('year')
            ->pluck('count', 'year')
            ->toArray();

        $trendLabels = [];
        $trendData = [];

        foreach ($years as $year) {
            $trendLabels[] = (string)$year; 
            $trendData[] = $yearlyData[$year] ?? 0; 
        }

        return view('dashboard', compact(
            'totalIncidents', 'incidentGrowth', 'pendingReports', 'urgentReports', 
            'totalAudits', 'complianceRate', 'recentIncidents', 'topRisks',
            'trendLabels', 'trendData'
        ));
    }
}