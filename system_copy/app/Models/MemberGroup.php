<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberGroup extends Model
{
    use HasFactory;

    protected $table = 'member_groups';

    protected $fillable = [
        'group_id',
        'group_name',
        'bank_name',
        'bank_account',
        'payrol_date',
        'status'
    ];

    /**
     * Get the clients that belong to this member group.
     */
    public function clients()
    {
        return $this->hasMany(ClientsModel::class, 'member_group_id');
    }
}