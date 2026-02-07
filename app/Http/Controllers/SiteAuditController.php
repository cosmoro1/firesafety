<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteAudit;
use Illuminate\Support\Facades\Auth;

class SiteAuditController extends Controller
{
    /* =========================================================
     * CENTRALIZED RISK LOGIC (DO NOT CHANGE BEHAVIOR)
     * ========================================================= */
    private function evaluateRisk(float $score, int $severity3Count): array
    {
        // 1. Critical Hazard Rule: 3 or more Severity 3 issues = HIGH
        if ($severity3Count >= 3) {
            return [
                'level'  => 'High',
                'reason' => 'Multiple critical fire hazards detected'
            ];
        }

        // 2. Score Rule: Below 65% = HIGH
        if ($score < 65) {
            return [
                'level'  => 'High',
                'reason' => 'Low compliance score'
            ];
        }

        // 3. Default = LOW
        return [
            'level'  => 'Low',
            'reason' => 'Meets minimum safety requirements'
        ];
    }

    /* =========================================================
     * 1. VIEW AUDITS
     * ========================================================= */
    public function index(Request $request)
    {
        $query = SiteAudit::latest();

        // Filter by Risk
        if ($request->filled('risk') && $request->risk !== 'all') {
            $query->where('risk_level', $request->risk);
        }

        // Search Logic
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('owner_name', 'like', "%$search%")
                  ->orWhere('barangay', 'like', "%$search%")
                  ->orWhere('id', $search);
            });
        }

        $audits = $query->paginate(10)->appends($request->all());

        return view('site_audit', compact('audits'));
    }

    /* =========================================================
     * 2. STORE AUDIT (MANUAL ENTRY)
     * ========================================================= */
    public function store(Request $request)
    {
        $checklistResponses = $request->input('checklist', []);

        // CHECKLIST RULES
        $checklistRules = [
            1  => ['safe' => 'Yes'],
            2  => ['safe' => 'Yes', 'critical' => true, 'severity' => 2, 'label' => 'Improper storage of flammables'],
            3  => ['safe' => 'Yes'],
            4  => ['safe' => 'Yes'],
            5  => ['safe' => 'Yes'],
            6  => ['safe' => 'Yes'],
            7  => ['safe' => 'Yes'],
            8  => ['safe' => 'Yes'],
            9  => ['safe' => 'No',  'critical' => true, 'severity' => 3, 'label' => 'Smoking indoors'],
            10 => ['safe' => 'Yes', 'critical' => true, 'severity' => 3, 'label' => 'No circuit breaker'],
            11 => ['safe' => 'Yes', 'critical' => true, 'severity' => 3, 'label' => 'Exposed wiring'],
            12 => ['safe' => 'Yes'],
            13 => ['safe' => 'Yes', 'critical' => true, 'severity' => 2, 'label' => 'Outlet overloading'],
            14 => ['safe' => 'Yes'],
            15 => ['safe' => 'Yes'],
            16 => ['safe' => 'Yes'],
            17 => ['safe' => 'Yes'],
            18 => ['safe' => 'Yes'],
            19 => ['safe' => 'Yes'],
            20 => ['safe' => 'Yes'],
            21 => ['safe' => 'Yes', 'critical' => true, 'severity' => 3, 'label' => 'Unattended cooking'],
            22 => ['safe' => 'Yes', 'critical' => true, 'severity' => 3, 'label' => 'Improper LPG storage'],
            23 => ['safe' => 'Yes'],
            24 => ['safe' => 'Yes'],
            25 => ['safe' => 'Yes'],
            26 => ['safe' => 'Yes'],
            27 => ['safe' => 'Yes'],
            28 => ['safe' => 'Yes', 'critical' => true, 'severity' => 2, 'label' => 'Improper candle storage'],
            29 => ['safe' => 'Yes', 'critical' => true, 'severity' => 3, 'label' => 'Blocked exits'],
            30 => ['safe' => 'Yes'],
            31 => ['safe' => 'Yes', 'critical' => true, 'severity' => 3, 'label' => 'Poor emergency access'],
            32 => ['safe' => 'Yes'],
            33 => ['safe' => 'Yes'],
            34 => ['safe' => 'Yes'],
        ];

        $rawScore = 0;
        $severity3Count = 0;
        $penaltyPoints = 0;
        $violationNotes = [];

        // Calculate Checklist Score
        foreach ($checklistRules as $id => $rule) {
            $answer = $checklistResponses[$id] ?? null;

            if ($answer === $rule['safe']) {
                $rawScore++;
            }

            if (($rule['critical'] ?? false) && $answer !== $rule['safe']) {
                if (($rule['severity'] ?? 0) === 3) {
                    $severity3Count++;
                }
                $penaltyPoints += ($rule['severity'] ?? 0);
                $violationNotes[] = $rule['label'];
            }
        }

        // STRUCTURAL SCORING
        $structureData = $request->input('struct', []);
        $structureScore = 0;
        $structurePossible = 0;

        foreach ($structureData as $part) {
            if (isset($part['material'])) {
                $structurePossible++;
                if (in_array($part['material'], ['cement', 'metal'])) {
                    $structureScore++;
                }
            }
        }

        if ($structurePossible === 0) $structurePossible = 1;

        // FINAL SCORE CALCULATION
        $totalRaw = $rawScore + $structureScore;
        $totalPossible = count($checklistRules) + $structurePossible;
        $basePercent = ($totalRaw / $totalPossible) * 100;
        $penalty = min($penaltyPoints * 2.5, 35);
        $finalScore = max(0, $basePercent - $penalty);

        // RISK EVALUATION
        $riskData = $this->evaluateRisk($finalScore, $severity3Count);

        // GENERATE REMARKS
        $remarks = "{$riskData['level']} Risk — {$riskData['reason']}. Final Score: " . round($finalScore, 1) . "%.";
        
        if (!empty($violationNotes)) {
             $remarks .= " Hazards: " . implode(', ', $violationNotes) . ".";
        }

        // SAVE
        SiteAudit::create([
            'barangay' => $request->barangay,
            'owner_name' => $request->owner_name,
            'type' => $request->type,
            'address' => $request->address,
            'contact_person' => $request->contact_person,
            'contact_number' => $request->contact_number,
            'structure_data' => $structureData,
            'checklist_data' => $checklistResponses,
            'hazards' => implode(', ', $violationNotes),
            'compliance_score' => round($finalScore, 2),
            'risk_level' => $riskData['level'],
            'remarks' => $remarks,
            'auditor_id' => Auth::id(),
        ]);

        return back()->with(
            'success',
            "Audit Saved! {$riskData['level']} Risk — " . round($finalScore, 1) . "%"
        );
    }

    /* =========================================================
     * 3. IMPORT CSV
     * ========================================================= */
    public function import(Request $request)
    {

        set_time_limit(0);

        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        array_shift($rows); // remove header

        foreach ($rows as $row) {
            // Ensure row has data
            if (count($row) < 6) continue;

            // Safely extract columns using array_pad
            [$barangay, $owner, $type, $address, $contact, $isWoodRaw, $violationsRaw] = 
                array_pad($row, 7, '');

            $isWood = strtolower(trim($isWoodRaw)) === 'yes';

            // Reconstruct structure data for consistency
            $structureData = [
                'EXTERIOR WALL' => ['material' => $isWood ? 'wood' : 'cement'],
                'ROOF' => ['material' => 'metal'],
                'MAIN DOOR' => ['material' => 'wood'],
            ];

            // Parse Violations from CSV string
            $violations = array_filter(array_map('trim', explode(',', $violationsRaw)));
            $severity3Count = count($violations); // Assume all CSV violations are critical for safety

            // Calculate Score (Simple logic for imports)
            $score = ($isWood ? 80 : 100) - ($severity3Count * 10);
            $score = max(0, $score);

            // RISK EVALUATION
            $riskData = $this->evaluateRisk($score, $severity3Count);

            SiteAudit::create([
                'barangay' => $barangay,
                'owner_name' => $owner,
                'type' => $type,
                'address' => $address,
                'contact_person' => $contact,
                'contact_number' => $contact,
                'structure_data' => $structureData,
                'checklist_data' => [], // Empty checklist for imports
                'hazards' => $violationsRaw,
                'compliance_score' => $score,
                'risk_level' => $riskData['level'],
                'remarks' => "Imported Data — {$riskData['reason']}. Score: $score%.",
                'auditor_id' => Auth::id(),
            ]);
        }

        return back()->with('success', 'CSV Data Imported Successfully!');
    }
}
