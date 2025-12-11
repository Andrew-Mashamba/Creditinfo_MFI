<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationModel extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'member_id',
        'title',
        'message',
        'type',
        'status',
        'action_url',
        'action_text',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(ClientsModel::class, 'member_id');
    }
} 