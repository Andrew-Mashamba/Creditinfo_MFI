<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grant extends Model
{
    use HasFactory;
    protected $table = 'grants';
    public $timestamps = true;
    protected $fillable = [
        'title',
        'description',
        'read_more_link',
        'deadline',
    ];

}
