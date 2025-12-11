<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'interviewer_id',
        'interview_date',
        'interview_time',
        'interview_type',
        'notes',
        'status',
        'feedback',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function interviewer()
    {
        return $this->belongsTo(Employee::class, 'interviewer_id');
    }
} 