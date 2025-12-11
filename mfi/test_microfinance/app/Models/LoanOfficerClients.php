<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanOfficerClients extends Model
{
    use HasFactory;
    protected $table = 'loan_officer_clients';
    protected $guarded = [];
}
