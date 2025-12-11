<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempPermissions extends Model
{
    use HasFactory;
    protected $table = 'temp_permissions';
    protected $guarded = [];
}
