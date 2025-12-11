<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guarantor extends Model
{
    protected $table = 'guarantors';
    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_id');
    }
} 