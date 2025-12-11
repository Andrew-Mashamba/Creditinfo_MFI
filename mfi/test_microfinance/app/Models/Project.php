<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'project';

    protected $fillable = [
        'project_type',
        'tender_no',
        'procuring_entity',
        'supplier_name',
        'award_date',
        'award_amount',
        'lot_name',
        'expected_end_date',
        'project_summary',
        'status',
    ];
}
