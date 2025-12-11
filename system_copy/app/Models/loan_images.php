<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class loan_images extends Model
{
    use HasFactory;
    protected $fillable = ['filename','url','category','document_descriptions','loan_id'];
}
