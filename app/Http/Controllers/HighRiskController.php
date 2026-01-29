<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\SiteAudit;

class HighRiskController extends Controller
{
    public function index(Request $request)
    {
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

        $data = [];
        $highRiskCount = 0;
        $lowRiskCount = 0;

        // --- 1. DYNAMIC BASIS CALCULATION ---
        // Instead of 150,000, we check the ACTUAL database size.
        $totalAuditsInDB = SiteAudit::count();
        
        // Safety: If DB is empty, default to 1 to avoid division by zero
        if ($totalAuditsInDB == 0) $totalAuditsInDB = 1;

        // Calculate the "Average Load" per barangay based on CURRENT data.
        // Example: If you have 10k rows, Avg = 10,000 / 54 = 185.
        $avgBarangaySize = $totalAuditsInDB / count($barangays); 

        // Pre-fetch data
        $allIncidentCounts = [];
        $allAudits = [];

        foreach ($barangays as $barangay) {
            $allIncidentCounts[$barangay] = Incident::where('location', 'LIKE', "%$barangay%")->count();
            $allAudits[$barangay] = SiteAudit::where('barangay', $barangay)->get();
        }

        foreach ($barangays as $barangay) {
            $incidentCount = $allIncidentCounts[$barangay];
            $audits = $allAudits[$barangay];
            $totalAudits = $audits->count();
            $highRiskAudits = $audits->where('risk_level', 'High')->count();

            if ($incidentCount == 0 && $totalAudits == 0) continue;

            // --- 2. AUDIT SCORE (Dynamic Volume) ---
            // Formula: (High Risk Count / Dynamic Average) * 50
            
            // Example with your 10k Dataset:
            // Bagong Kalsada has 53 failures.
            // Average Barangay Size (10k / 54) = 185.
            // Ratio = 53 / 185 = 0.28 (28%)
            // Score = 0.28 * 50 = 14 Points. (Correctly scaled!)
            
            // Example with 150k Dataset:
            // Bagong Kalsada has 53 failures.
            // Average Size = 2,777.
            // Ratio = 53 / 2777 = 0.019 (1.9%)
            // Score = 1.9 * 50 = 1 Point. (Correctly scaled!)
            
            $auditRiskRatio = $highRiskAudits / $avgBarangaySize; 
            $auditScore = min($auditRiskRatio * 50, 50); // Cap at 50 pts

            // --- 3. STRUCTURAL SCORE (Max 30 Points) ---
            $woodenStructures = 0;
            foreach ($audits as $audit) {
                $struct = $audit->structure_data;
                if (isset($struct['EXTERIOR WALL']['material']) && $struct['EXTERIOR WALL']['material'] === 'wood') {
                    $woodenStructures++;
                }
            }
            $woodPercent = $totalAudits > 0 ? ($woodenStructures / $totalAudits) : 0;
            $structureScore = $woodPercent * 30;

            // --- 4. INCIDENT SCORE (Max 20 Points - CAPPED) ---
            $incidentScore = min($incidentCount * 2, 20);

            // --- TOTAL ---
            $totalScore = $auditScore + $structureScore + $incidentScore;

            // --- THRESHOLD ---
            if ($totalScore >= 60) {
                $status = 'High';
                $statusColor = 'bg-red-100 text-red-700 border border-red-200';
                $highRiskCount++;
                
                if ($incidentScore >= 20) {
                     $reason = "High Risk: Significant fire history ($incidentCount incidents).";
                } elseif ($auditScore >= 25) {
                     $reason = "High Risk: Disproportionately high audit failures compared to other areas.";
                } else {
                     $reason = "High Risk: Combined structural and fire hazards.";
                }
            } else {
                $status = 'Low';
                $statusColor = 'bg-green-100 text-green-700 border border-green-200';
                $lowRiskCount++;
                $reason = "Low Risk. Audit failures ($highRiskAudits) are within normal range.";
            }

            $data[] = [
                'name' => $barangay,
                'incidents' => $incidentCount,
                'high_risk_count' => $highRiskAudits,
                'total_audits' => $totalAudits,
                'status' => $status,
                'status_color' => $statusColor,
                'analysis' => $reason,
                'wood_percent' => round($woodPercent * 100, 1)
            ];
        }

        usort($data, function($a, $b) {
            if ($a['status'] === $b['status']) return strcmp($a['name'], $b['name']);
            return $a['status'] === 'High' ? -1 : 1;
        });

        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = strtolower($request->input('search'));
            $data = array_filter($data, function ($row) use ($searchTerm) {
                return str_contains(strtolower($row['name']), $searchTerm);
            });
        }

        return view('high_risk', compact('data', 'highRiskCount', 'lowRiskCount'));
    }
}