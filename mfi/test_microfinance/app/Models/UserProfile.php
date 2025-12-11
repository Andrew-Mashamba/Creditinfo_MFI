<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'job_title',
        'phone_number',
        'emergency_contact',
        'date_of_birth',
        'hire_date',
        'employment_status',
        'employment_type',
        'salary_grade',
        'reporting_manager_id',
        'skills',
        'certifications',
        'education',
        'work_experience',
        'preferences',
        'language_preference',
        'timezone',
        'notification_preferences',
        'profile_completion_percentage'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'skills' => 'array',
        'certifications' => 'array',
        'education' => 'array',
        'work_experience' => 'array',
        'preferences' => 'array',
        'notification_preferences' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reportingManager()
    {
        return $this->belongsTo(User::class, 'reporting_manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(UserProfile::class, 'reporting_manager_id', 'user_id');
    }

    public function getFullName()
    {
        return $this->user->name;
    }

    public function getReportingChain()
    {
        $chain = collect();
        $current = $this;

        while ($current->reportingManager) {
            $chain->push($current->reportingManager);
            $current = $current->reportingManager->profile;
        }

        return $chain;
    }

    public function getTeamMembers()
    {
        return UserProfile::where('reporting_manager_id', $this->user_id)->get();
    }

    public function updateProfileCompletion()
    {
        $requiredFields = [
            'job_title',
            'phone_number',
            'emergency_contact',
            'date_of_birth',
            'hire_date',
            'employment_status',
            'employment_type',
            'salary_grade'
        ];

        $completedFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }

        $this->profile_completion_percentage = ($completedFields / count($requiredFields)) * 100;
        $this->save();
    }

    public function getSkillsList()
    {
        return collect($this->skills)->pluck('name')->join(', ');
    }

    public function getCertificationsList()
    {
        return collect($this->certifications)->pluck('name')->join(', ');
    }

    public function getEducationHistory()
    {
        return collect($this->education)->sortByDesc('end_date');
    }

    public function getWorkExperience()
    {
        return collect($this->work_experience)->sortByDesc('end_date');
    }

    public function scopeActive($query)
    {
        return $query->where('employment_status', 'active');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->whereHas('user', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }

    public function scopeByJobTitle($query, $jobTitle)
    {
        return $query->where('job_title', $jobTitle);
    }

    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }
} 