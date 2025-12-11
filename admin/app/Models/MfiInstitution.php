<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MfiInstitution extends Model
{
    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'contact_email', 
        'contact_phone',
        'address',
        'license_number',
        'database_name',
        'port',
        'folder_path',
        'admin_email',
        'status',
        'configuration'
    ];

    protected $casts = [
        'configuration' => 'array',
    ];
}
