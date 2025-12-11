<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function leader()
    {
        return $this->belongsTo(LeaderShipModel::class, 'leader_id');
    }
} 