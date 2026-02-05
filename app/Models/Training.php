<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_name',
        'company_id',
        'industry_type',
        'representative_name',
        'representative_position',
        'topic',
        'date_conducted',
        'attendees_count',
        'status',
    ];
}