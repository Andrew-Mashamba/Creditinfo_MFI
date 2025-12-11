<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Onboarding extends Model
{
    use HasFactory;

    protected $table = 'onboarding';

    protected $fillable = [
        'employee_id',
        'applicant_id',
        'job_posting_id',
        'start_date',
        'status',
        'notes',
        'cv_path',
        'national_id_path',
        'passport_photo_path',
        'employment_contract_path',
        'bank_account_details_path',
        'full_name',
        'date_of_birth',
        'nationality',
        'nida_number',
        'tin_number',
        'physical_address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'workstation_id',
        'email_created',
        'system_access',
        'id_badge',
        'created_by',
    ];

    protected $casts = [
        'email_created' => 'boolean',
        'system_access' => 'boolean',
        'id_badge' => 'boolean',
        'start_date' => 'date',
        'date_of_birth' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }
} 