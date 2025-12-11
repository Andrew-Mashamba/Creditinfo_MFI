<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'type',
        'title',
        'message',
        'status'
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
} 