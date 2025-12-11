<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChequeBookModel extends Model
{
    use HasFactory;
    protected $table = 'cheque_books';
    protected $guarded = [];
}
