<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentHistory extends Model {
    
    protected $fillable = [
        'incident_id', 
        'stage', 
        'description',
        'title',
        'type',
        'location',
        'incident_date',
        'reported_by',
        'images' // <--- 1. ADD THIS
    ];

    protected $casts = [
        'images' => 'array', // <--- 2. ADD THIS
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}