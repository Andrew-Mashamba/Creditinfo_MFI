<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_title',
        'department',
        'location',
        'job_type',
        'description',
        'requirements',
        'salary',
        'status',
    ];

    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }

    public function onboarding()
    {
        return $this->hasMany(Onboarding::class);
    }
} 