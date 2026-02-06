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
        'remarks',
        'auditor_id',
    ];

    // AUTOMATICALLY CONVERT JSON TO ARRAY
    protected $casts = [
        'structure_data' => 'array',
        'checklist_data' => 'array',
    ];

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }
}