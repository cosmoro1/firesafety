<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'barangay',
        'owner_name',
        'type',
        'address',
        'contact_person',
        'contact_number',
        'structure_data',
        'checklist_data',
        'hazards',
        'compliance_score',
        'risk_level',
        'remarks', // <--- ADD THIS LINE
        'auditor_id',
    ];

    protected $casts = [
        'structure_data' => 'array',
        'checklist_data' => 'array',
    ];

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }


// 3. IMPORT CSV
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $fileData = array_map('str_getcsv', file($file));
        
        // Remove Header Row
        array_shift($fileData); 

        foreach ($fileData as $row) {
            // Expected CSV Format:
            // 0: barangay, 1: owner_name, 2: type, 3: address, 
            // 4: contact_person, 5: is_wood (Yes/No), 6: violations (comma separated)

            if(count($row) < 7) continue;

            $barangay = $row[0];
            $owner = $row[1];
            $type = $row[2];
            $address = $row[3];
            $contact = $row[4];
            $isWood = strtolower(trim($row[5])) === 'yes';
            $violationsRaw = $row[6];

            // --- BUILD STRUCTURE DATA ---
            // If CSV says "Yes" to wood, make the EXTERIOR WALL wood
            $material = $isWood ? 'wood' : 'cement';
            $structureData = [
                'EXTERIOR WALL' => ['material' => $material],
                'ROOF' => ['material' => 'metal'], // Default
                'MAIN DOOR' => ['material' => 'wood'] // Default
            ];

            // --- CALCULATE SCORE ---
            // Logic: Start at 100.
            // If Wood: -20 points.
            // For every violation listed: -10 points.
            
            $score = 100;
            if($isWood) $score -= 20;

            $violationList = array_map('trim', explode(',', $violationsRaw));
            $violationCount = 0;
            
            // Just for testing purposes, we count violations
            foreach($violationList as $v) {
                if(!empty($v)) {
                    $score -= 10;
                    $violationCount++;
                }
            }

            // --- DETERMINE RISK ---
            $risk = ($score < 65) ? 'High' : 'Low';
            
            // Override for Safety Net (3+ violations = High)
            if($violationCount >= 3) $risk = 'High';

            // --- SAVE ---
            SiteAudit::create([
                'barangay' => $barangay,
                'owner_name' => $owner,
                'type' => $type,
                'address' => $address,
                'contact_person' => $contact,
                'structure_data' => $structureData,
                'checklist_data' => [], // Empty for imported bulk data
                'hazards' => $violationsRaw, // Save raw text as hazards
                'compliance_score' => max(0, $score),
                'risk_level' => $risk,
                'remarks' => "Imported Data. $risk Risk. " . $violationsRaw,
                'auditor_id' => Auth::id(),
            ]);
        }

        return redirect()->back()->with('success', 'Data Imported Successfully!');
    }
}