<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingDocument extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function uploader()
    {
        return $this->belongsTo(LeaderShipModel::class, 'uploaded_by');
    }
} 