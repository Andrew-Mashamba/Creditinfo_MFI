<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalComment extends Model
{
    protected $fillable = [
        'approval_id',
        'user_id',
        'comment',
        'level',
        'action'
    ];

    public function approval()
    {
        return $this->belongsTo(Approval::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 