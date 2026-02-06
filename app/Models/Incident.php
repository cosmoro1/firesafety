<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'location',
        'incident_date',
        'description',
        'status',
        'reported_by',
        'alarm_level',
        'stage',
        'image_path',
        'admin_remarks',
    ];

    /**
     * Get the history of investigation stages for this incident.
     */
    public function history()
    {
        // This links the Incident to multiple IncidentHistory snapshots
        return $this->hasMany(IncidentHistory::class)->orderBy('created_at', 'asc');
    }

    public function images()
{
    return $this->hasMany(IncidentImage::class);
    
}
  


}
