<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uploader extends Model
{
    use HasFactory;
    protected $fillable = ['filename','url','category','loan_id'];
}
