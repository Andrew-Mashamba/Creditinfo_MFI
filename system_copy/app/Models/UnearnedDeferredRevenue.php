<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnearnedDeferredRevenue extends Model
{
    use HasFactory;

    protected $table ='unearned_deferred_revenue';

    protected $guarded=[];
}
