<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dividend extends Model
{
    use HasFactory;

    protected $table = 'dividends';
    protected $guarded = [];

    /**
     * Get the member who owns this dividend.
     */
    public function member()
    {
        return $this->belongsTo(ClientsModel::class, 'member_id', 'id');
    }
} 