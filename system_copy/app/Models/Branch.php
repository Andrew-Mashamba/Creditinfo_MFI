<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MonthlyPaymentsStatus;
use App\Models\Expenses;
use App\Models\departmentsList;


class Branch extends Model
{
    use HasFactory;
    protected $table = 'branches';
    protected $guarded = [];

    public function departments()
    {
        return $this->hasMany(departmentsList::class);
    }

}
