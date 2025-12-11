<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ClientsModel;

class general_ledger extends Model
{
    use HasFactory;
    protected $table = 'general_ledger';
    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(AccountsModel::class, 'record_on_account_number', 'account_number');
    }

   

}
