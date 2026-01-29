<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteAudit;
use Illuminate\Support\Facades\Auth;

class SiteAuditController extends Controller
{
    // 1. VIEW AUDITS
    public function index(Request $request)
    {
        $query = SiteAudit::latest();

        if ($request->has('risk') && $request->input('risk') !== 'all') {
            $query->where('risk_level', $request->input('risk'));
        }

        if ($request->has('search') && $request->input('search') != '') {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('owner_name', 'like', "%{$search}%")
                  ->orWhere('barangay', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        $audits = $query->paginate(10)->appends($request->all());

        return view('site_audit', compact('audits'));
    }

    // 2. STORE AUDIT (Manual Entry)
    public function store(Request $request)
    {
        $checklistResponses = $request->input('checklist', []);

        // CHECKLIST RULES
        $checklistRules = [
            // A. KAAYUSAN SA BAHAY
            1  => ['safe' => 'Yes'],
            2  => ['safe' => 'Yes', 'critical' => true, 'severity' => 2, 'label' => 'Improper storage of flammables'],
            3  => ['safe' => 'Yes'],
            4  => ['safe' => 'Yes'],
            5  => ['safe' => 'Yes'],
            6  => ['safe' => 'Yes'],
            7  => ['safe' => 'Yes'],
            8  => ['safe' => 'Yes'],
            9  => ['safe' => 'No',  'critical' => true, 'severity' => 3, 'label' => 'Smoking indoors detected'],

            // B. KONEKSYONG ELEKTRIKAL
            10 => ['safe' => 'Yes', 'critical' => true, 'severity' => 3, 'label' => 'No Circuit Breaker'],
            11 => ['safe' => 'Yes', 'critical' => true, 'severity' => 3, 'label' => 'Exposed wiring or panels'],
            12 => ['safe' => 'Yes'],
            13 => ['safe' => 'Yes', 'critical' => true, 'severity' => 2, 'label' => 'Outlet overloading'],
            14 => ['safe' => 'Yes'],
            15 => ['safe' => 'Yes'],
            16 => ['safe' => 'Yes'],
            17 => ['safe' => 'Yes'],
            18 => ['safe' => 'Yes'],
            19 => ['safe' => 'Yes'],
            20 => ['safe' => 'Yes'],

            // C. KAAYUSAN SA KUSINA
            21 => ['safe' => 'Yes', 'critical' => true, 'severity' => 3, 'label' => 'Unattended cooking risks'],
            22 => ['safe' => 'Yes', 'critical' => true, 'severity' => 3, 'label' => 'Improper LPG storage'],
            23 => ['safe' => 'Yes'],
            24 => ['safe' => 'Yes'],
            25 => ['safe' => 'Yes'],
            26 => ['safe' => 'Yes'],
            27 => ['safe' => 'Yes'],
            28 => ['safe' => 'Yes', 'critical' => true, 'severity' => 2, 'label' => 'Improper candle/lighter storage'],

            // D. DAANAN O LABASAN
            29 => ['safe' => 'Yes', 'critical' => true, 'severity' => 3, 'label' => 'Blocked exits/windows'],
            30 => ['safe' => 'Yes'],
            31 => ['safe' => 'Yes', 'critical' => true, 'severity' => 3, 'label' => 'Poor emergency exit access'],
            32 => ['safe' => 'Yes'],
            33 => ['safe' => 'Yes'],
            34 => ['safe' => 'Yes'],
        ];

        // SCORING
        $rawScore = 0;
        $severity3Count = 0;
        $penaltyPoints = 0;
        $violationNotes = [];

        foreach ($checklistRules as $id => $rule) {
            $answer = $checklistResponses[$id] ?? null;

            if ($answer === $rule['safe']) {
                $rawScore++;
            }

            if (($rule['critical'] ?? false) && $answer !== $rule['safe']) {
                if ($rule['severity'] == 3) {
                    $severity3Count++;
                }
                $penaltyPoints += $rule['severity'];
                $violationNotes[] = $rule['label'];
            }
        }

        // STRUCTURAL SCORING
        $structureData = $request->input('struct', []);
        $structureScore = 0;
        $structurePossible = 0;
        $structuralParts = ['ROOF', 'CEILING', 'ROOM PARTITIONS', 'TRUSSES', 'WINDOWS', 'CORRIDOR WALLS', 'COLUMNS', 'MAIN DOOR', 'EXTERIOR WALL', 'BEAMS'];

        foreach ($structuralParts as $part) {
            if (isset($structureData[$part]['material'])) {
                $structurePossible++;
                if (in_array($structureData[$part]['material'], ['cement', 'metal'])) {
                    $structureScore++;
                }
            }
        }
        if ($structurePossible == 0) $structurePossible = 1;

        // FINAL CALCULATION
        $totalRawScore = $rawScore + $structureScore;
        $totalPossible = count($checklistRules) + $structurePossible;
        $basePercentage = ($totalRawScore / $totalPossible) * 100;
        $penaltyDeduction = min($penaltyPoints * 2.5, 35);
        $finalScore = max(0, $basePercentage - $penaltyDeduction);

        // RISK LEVEL (High / Low Only)
        if ($finalScore < 65) {
            $riskLevel = 'High';
        } else {
            $riskLevel = 'Low';
        }

        // Safety Net
        if ($severity3Count >= 3) {
            $riskLevel = 'High';
        }

        // Remarks
        $remarks = $riskLevel . " Risk: Final score " . round($finalScore, 0) . "%. ";
        if (!empty($violationNotes)) {
            $remarks .= "Critical hazards detected: " . implode(', ', $violationNotes) . ".";
        } else {
            $remarks .= "No critical hazards detected.";
        }

        SiteAudit::create([
            'barangay' => $request->barangay,
            'owner_name' => $request->owner_name,
            'type' => $request->type,
            'address' => $request->address,
            'contact_person' => $request->contact_person,
            'contact_number' => $request->contact_number,
            'structure_data' => $structureData,
            'checklist_data' => $checklistResponses,
            'hazards' => $request->hazards,
            'compliance_score' => round($finalScore, 2),
            'risk_level' => $riskLevel,
            'remarks' => $remarks,
            'auditor_id' => Auth::id(),
        ]);

        return redirect()->back()->with(
            'success',
            "Audit Saved! Final Score: " . round($finalScore, 2) . "% ({$riskLevel} Risk)"
        );
    }

    // 3. IMPORT CSV (The Missing Method)
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        
        // Read file into array
        $fileData = array_map('str_getcsv', file($file->getRealPath()));
        
        // Remove Header Row
        if (count($fileData) > 0) {
            array_shift($fileData); 
        }

        foreach ($fileData as $row) {
            // CSV Columns: 
            // 0: barangay, 1: owner_name, 2: type, 3: address, 
            // 4: contact_person, 5: is_wood (Yes/No), 6: violations
            
            // Skip empty or invalid rows
            if(count($row) < 6) continue;

            $barangay = $row[0];
            $owner = $row[1];
            $type = $row[2];
            $address = $row[3];
            $contact = $row[4];
            $isWood = isset($row[5]) && strtolower(trim($row[5])) === 'yes';
            
            // Handle violations (column 6 might be empty or missing depending on csv export)
            $violationsRaw = isset($row[6]) ? $row[6] : '';

            // --- BUILD STRUCTURE DATA ---
            $material = $isWood ? 'wood' : 'cement';
            $structureData = [
                'EXTERIOR WALL' => ['material' => $material],
                'ROOF' => ['material' => 'metal'], 
                'MAIN DOOR' => ['material' => 'wood'] 
            ];

            // --- CALCULATE SCORE ---
            $score = 100;
            if($isWood) $score -= 20; // Big penalty for wood

            $violationList = array_map('trim', explode(',', $violationsRaw));
            $violationCount = 0;
            
            foreach($violationList as $v) {
                if(!empty($v)) {
                    $score -= 10;
                    $violationCount++;
                }
            }

            // --- DETERMINE RISK ---
            $risk = ($score < 65) ? 'High' : 'Low';
            
            // Safety Net: 3+ violations = Auto High
            if($violationCount >= 3) $risk = 'High';

            // --- SAVE ---
            SiteAudit::create([
                'barangay' => $barangay,
                'owner_name' => $owner,
                'type' => $type,
                'address' => $address,
                'contact_person' => $contact,
                'contact_number' => $contact,
                'structure_data' => $structureData, // This is critical for HighRiskController logic
                'checklist_data' => [], 
                'hazards' => $violationsRaw,
                'compliance_score' => max(0, $score),
                'risk_level' => $risk,
                'remarks' => "Imported Data. $risk Risk. " . $violationsRaw,
                'auditor_id' => Auth::id(),
            ]);
        }

        return redirect()->back()->with('success', 'CSV Data Imported Successfully!');
    }
}