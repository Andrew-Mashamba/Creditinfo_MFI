<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpeAuditTrail extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'ppe_id', 'action', 'old_values', 'new_values', 
        'user_id', 'user_name', 'ip_address', 'notes'
    ];
    
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];
    
    public $timestamps = false;
    
    protected $dates = ['created_at'];
    
    public function ppe()
    {
        return $this->belongsTo(PPE::class, 'ppe_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}