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
        $totalAuditsInDB = SiteAudit::count();
        if ($totalAuditsInDB == 0) $totalAuditsInDB = 1;
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

            // --- 2. SCORES ---
            $auditRiskRatio = $highRiskAudits / $avgBarangaySize; 
            $auditScore = min($auditRiskRatio * 50, 50); 

            $woodenStructures = 0;
            foreach ($audits as $audit) {
                $struct = $audit->structure_data;
                if (isset($struct['EXTERIOR WALL']['material']) && $struct['EXTERIOR WALL']['material'] === 'wood') {
                    $woodenStructures++;
                }
            }
            $woodPercent = $totalAudits > 0 ? ($woodenStructures / $totalAudits) : 0;
            $structureScore = $woodPercent * 30;

            $incidentScore = min($incidentCount * 2, 20);
            $totalScore = $auditScore + $structureScore + $incidentScore;

            if ($totalScore >= 60) {
                $status = 'High';
                $statusColor = 'bg-red-100 text-red-700 border border-red-200';
                $highRiskCount++;
            } else {
                $status = 'Low';
                $statusColor = 'bg-green-100 text-green-700 border border-green-200';
                $lowRiskCount++;
            }

            // --- 3. GENERATE DESCRIPTIVE ANALYSIS (UPDATED) ---
            $analysisParts = [];

            // Context 1: Incidents
            if ($incidentCount > 10) {
                $analysisParts[] = "This barangay has a concerning history of fire activity, with $incidentCount confirmed incidents recorded.";
            } elseif ($incidentCount > 0) {
                $analysisParts[] = "Historical data shows sporadic fire incidents ($incidentCount) in the area.";
            } else {
                $analysisParts[] = "No recent fire incidents have been reported here.";
            }

            // Context 2: Audits
            if ($highRiskAudits > 0) {
                $analysisParts[] = "Furthermore, inspections reveal that $highRiskAudits establishments failed to meet safety standards. Common violations include faulty electrical wiring and obstruction of designated fire exits.";
            }

            // Context 3: Structure (Descriptive, not percentage-based)
            if ($woodPercent > 0.50) {
                $analysisParts[] = "The risk is critically amplified by the structural composition of the area; a majority of the houses and buildings here are constructed from light materials (wood), which accelerates fire spread.";
            } elseif ($woodPercent > 0.20) {
                $analysisParts[] = "The area features a mix of concrete and wooden structures, creating moderate vulnerability to fire propagation.";
            } else {
                $analysisParts[] = "Most structures in the vicinity are built with concrete and fire-resistant materials, providing a natural buffer.";
            }

            $reason = implode(" ", $analysisParts);

            $data[] = [
                'name' => $barangay,
                'incidents' => $incidentCount,
                'high_risk_count' => $highRiskAudits,
                'total_audits' => $totalAudits,
                'status' => $status,
                'status_color' => $statusColor,
                'analysis' => $reason,
                // Passing this is optional if you remove the line from your blade file
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