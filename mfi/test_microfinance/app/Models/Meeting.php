<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function attendance()
    {
        return $this->hasMany(MeetingAttendance::class);
    }

    public function documents()
    {
        return $this->hasMany(MeetingDocument::class);
    }
} 