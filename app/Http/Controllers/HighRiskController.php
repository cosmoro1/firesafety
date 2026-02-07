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

        foreach ($barangays as $barangay) {
            // Count Incidents (Using LIKE matches your preference)
            $incidentCount = Incident::where('location', 'LIKE', "%$barangay%")->count();
            
            // Get Audits
            $audits = SiteAudit::where('barangay', $barangay)->get();
            $totalAudits = $audits->count();
            $highRiskAudits = $audits->where('risk_level', 'High')->count();

            // Skip empty records
            if ($incidentCount == 0 && $totalAudits == 0) continue;

            // --- 2. SCORES (Your Specific Formula) ---
            $auditRiskRatio = $avgBarangaySize > 0 ? ($highRiskAudits / $avgBarangaySize) : 0;
            $auditScore = min($auditRiskRatio * 50, 50);

            $woodenStructures = 0;
            $violationStats = []; 

            foreach ($audits as $audit) {
                // Parse Structure Data
                $struct = is_string($audit->structure_data) ? json_decode($audit->structure_data, true) : $audit->structure_data;
                
                if (isset($struct['EXTERIOR WALL']['material']) && strtolower($struct['EXTERIOR WALL']['material']) === 'wood') {
                    $woodenStructures++;
                }

                // Violation Stats
                $violations = $this->getViolations($audit);
                foreach($violations as $v) {
                    if(!isset($violationStats[$v])) $violationStats[$v] = 0;
                    $violationStats[$v]++;
                }
            }

            $woodPercent = $totalAudits > 0 ? ($woodenStructures / $totalAudits) : 0;
            $structureScore = $woodPercent * 30;

            $incidentScore = min($incidentCount * 2, 20);
            
            $totalScore = $auditScore + $structureScore + $incidentScore;

            // --- 3. DETERMINE STATUS ---
            if ($totalScore >= 60) {
                $status = 'High';
                $statusColor = 'bg-red-100 text-red-700 border border-red-200';
                $highRiskCount++;
            } else {
                $status = 'Low';
                $statusColor = 'bg-green-100 text-green-700 border border-green-200';
                $lowRiskCount++;
            }

            // --- 4. DESCRIPTIVE ANALYTICS ---
            $analysisParts = [];
            
            // Get Top Issues
            $issueString = "varied safety lapses";
            if (!empty($violationStats)) {
                arsort($violationStats);
                $topIssues = array_keys(array_slice($violationStats, 0, 2));
                $issueString = "'" . implode("' and '", $topIssues) . "'";
            }

            if ($status === 'High') {
                if ($incidentCount > 10) {
                    $analysisParts[] = "This area exhibits a high frequency of fire occurrences, with " . number_format($incidentCount) . " confirmed incidents on record.";
                } else {
                    $analysisParts[] = "Risk metrics are elevated due to combined structural and compliance factors.";
                }

                if ($woodPercent > 0.40) {
                    $analysisParts[] = "A predominant portion of the structures are composed of light materials (wood), which increases the area's fuel load density.";
                }

                if ($highRiskAudits > 0) {
                    $analysisParts[] = "Audit data reveals a significant gap in safety standards, with " . number_format($highRiskAudits) . " establishments failing inspection. The most prevalent non-compliance issues are $issueString.";
                }
            } else {
                $analysisParts[] = "Current data indicates a managed risk profile for this area.";

                if ($incidentCount > 10) {
                    $analysisParts[] = "Although " . number_format($incidentCount) . " fire incidents are recorded, the overall risk score remains lower due to stronger structural integrity or higher audit compliance.";
                } elseif ($incidentCount > 0) {
                    $analysisParts[] = "Fire activity in this area has been sporadic (" . number_format($incidentCount) . " incidents).";
                }

                if ($woodPercent < 0.40) {
                    $analysisParts[] = "The presence of concrete and mixed-material structures provides a higher resistance to fire spread.";
                } else {
                    $analysisParts[] = "Structural density is moderate.";
                }

                if ($highRiskAudits > 0) {
                    $analysisParts[] = "While overall compliance is sufficient, specific deviations were noted in " . number_format($highRiskAudits) . " establishments, primarily involving $issueString.";
                } else {
                    $analysisParts[] = "Site audit records show a high rate of adherence to safety protocols.";
                }
            }

            $reason = implode(" ", $analysisParts);

            $data[] = [
                'name' => $barangay,
                'status' => $status,
                'status_color' => $statusColor,
                // Added number_format here for UI consistency
                'incidents' => number_format($incidentCount),
                'high_risk_count' => number_format($highRiskAudits),
                'total_audits' => number_format($totalAudits),
                'analysis' => $reason,
                'wood_percent' => round($woodPercent * 100, 1)
            ];
        }

        // Sort: High Risk first, then by Incidents
        usort($data, function($a, $b) {
            if ($a['status'] === $b['status']) {
                $incA = (int)str_replace(',', '', $a['incidents']);
                $incB = (int)str_replace(',', '', $b['incidents']);
                return $incB <=> $incA;
            }
            return $a['status'] === 'High' ? -1 : 1;
        });

        // Search Filter
        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = strtolower($request->input('search'));
            $data = array_filter($data, function ($row) use ($searchTerm) {
                return str_contains(strtolower($row['name']), $searchTerm);
            });
        }

        return view('high_risk', compact('data', 'highRiskCount', 'lowRiskCount'));
    }

    /**
     * Helper: Reads BOTH CSV text ('hazards') AND Manual Answers ('checklist_data')
     */
    private function getViolations($audit) {
        $checklist = is_string($audit->checklist_data) ? json_decode($audit->checklist_data, true) : $audit->checklist_data;
        $violations = [];

        // 1. CSV SOURCE
        if (!empty($audit->hazards)) {
            $rawList = explode(',', $audit->hazards);
            foreach($rawList as $raw) {
                $trimmed = trim($raw);
                if(!empty($trimmed)) {
                    $violations[] = $trimmed; 
                }
            }
        }
        
        // 2. MANUAL SOURCE
        if ($checklist) {
            $labels = [
                1 => 'General Disorder', 
                2 => 'Improper storage of flammables', 
                3 => 'Clutter near Outlets', 
                4 => 'Poor Arrangement', 
                5 => 'Trash Accumulation', 
                6 => 'Disorganized Items', 
                7 => 'Lack of Safety Knowledge', 
                8 => 'No Evacuation Plan', 
                9 => 'Smoking indoors detected', 
                10 => 'No Circuit Breaker', 
                11 => 'Exposed wiring or panels', 
                12 => 'Bad Extension Cords', 
                13 => 'Outlet overloading', 
                14 => 'Exposed Cords', 
                15 => 'Broken Switches', 
                16 => 'Daisy-Chaining', 
                17 => 'Wrong Wire Gauge', 
                18 => 'Wiring Issues', 
                19 => 'Appliances Plugged In', 
                20 => 'No Safety Switch', 
                21 => 'Unattended cooking risks', 
                22 => 'Improper LPG storage', 
                23 => 'LPG Left Open', 
                24 => 'Kitchen Leakages', 
                25 => 'Flammables near Stove', 
                26 => 'Poor Maintenance', 
                27 => 'Poor Ventilation', 
                28 => 'Improper candle/lighter storage', 
                29 => 'Blocked exits/windows', 
                30 => 'Debris Outside', 
                31 => 'Poor emergency exit access', 
                32 => 'Far from Road', 
                33 => 'Blocked Hallways', 
                34 => 'Poor Lighting'
            ];

            foreach ($checklist as $id => $ans) {
                if ($id == 9 && $ans == 'Yes') {
                    $violations[] = $labels[9];
                } elseif ($id != 9 && $ans == 'No' && isset($labels[$id])) {
                    $violations[] = $labels[$id];
                }
            }
        }
        
        return array_unique($violations);
    }
}