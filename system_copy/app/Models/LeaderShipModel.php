<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaderShipModel extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $table='leaderships';

    public function committeeMemberships()
    {
        return $this->hasMany(\App\Models\CommitteeMember::class, 'leader_id');
    }

    public function committees()
    {
        return $this->belongsToMany(\App\Models\Committee::class, 'committee_members', 'leader_id', 'committee_id');
    }

    public function meetingAttendance()
    {
        return $this->hasMany(\App\Models\MeetingAttendance::class, 'leader_id');
    }

    public function uploadedMeetingDocuments()
    {
        return $this->hasMany(\App\Models\MeetingDocument::class, 'uploaded_by');
    }
}
