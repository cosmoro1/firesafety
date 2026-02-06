<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $table = 'trainings'; // Ensure this matches your DB table name

    protected $fillable = [
        'company_name',
        'company_id',
        'industry_type',
        'representative_name',
        'representative_email', // <--- MAKE SURE THIS IS ADDED
        'representative_position',
        'topic',
        'date_conducted',
        'attendees_count',
        'status',
    ];
}