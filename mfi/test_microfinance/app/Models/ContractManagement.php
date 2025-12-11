<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractManagement extends Model
{
    use HasFactory;
    protected $table='contract_managements';
    protected $fillable=['contract_file_path'];
}
