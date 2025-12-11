<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingAttendance extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'meeting_attendance';

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function leader()
    {
        return $this->belongsTo(LeaderShipModel::class, 'leader_id');
    }
} 