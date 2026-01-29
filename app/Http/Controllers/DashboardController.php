<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\SiteAudit;
use Carbon\Carbon;

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
        
        // Percentage Growth calculation
        $lastYearIncidents = Incident::whereYear('created_at', Carbon::now()->subYear()->year)->count();
        $thisYearIncidents = Incident::whereYear('created_at', Carbon::now()->year)->count();
        $incidentGrowth = $lastYearIncidents > 0 
            ? round((($thisYearIncidents - $lastYearIncidents) / $lastYearIncidents) * 100) 
            : 100;

        // Pending Reports (Incidents from last 7 days)
        $pendingReports = Incident::where('status', 'Pending')->count();

        // Urgent Reports (High Alarm Level)
        $urgentReports = Incident::where('alarm_level', 'High')->count(); 
        
        // Audit Compliance Rate
        $totalAudits = SiteAudit::count();
        $passedAudits = SiteAudit::where('compliance_score', '>=', 80)->count();
        $complianceRate = $totalAudits > 0 ? round(($passedAudits / $totalAudits) * 100) : 0;

        // 3. RECENT ACTIVITY (Get latest 3)
        $recentIncidents = Incident::latest()->take(3)->get();

        // 4. HIGH RISK BARANGAYS CALCULATION
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
            
            // Risk Formula: (Incidents x 10) + (Failed Audits x 5)
            $score = ($incidents * 10) + ($failedAudits * 5);

            if ($score > 0) {
                $riskData[] = [
                    'name' => $barangay,
                    'incidents' => $incidents,
                    'audits' => SiteAudit::where('barangay', $barangay)->count(),
                    'failed_audits' => $failedAudits,
                    'score' => $score
                ];
            }
        }
        
        // Sort by Score (High to Low) and take top 3
        usort($riskData, function($a, $b) { return $b['score'] <=> $a['score']; });
        $topRisks = array_slice($riskData, 0, 3);

        return view('dashboard', compact(
            'totalIncidents', 
            'incidentGrowth', 
            'pendingReports', 
            'urgentReports', 
            'totalAudits', 
            'complianceRate', 
            'recentIncidents', 
            'topRisks'
        ));
    }
}