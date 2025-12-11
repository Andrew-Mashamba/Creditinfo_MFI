<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchesModel extends Model
{
    use HasFactory;
    use Search;

    protected $guarded = [];
    protected $table = 'branches';

    protected $fillable = [
        'branch_number',
        'name',
        'region',
        'wilaya',
        'status',
        'branch_type',
        'branch_manager',
        'email',
        'phone_number',
        'address',
        'opening_date',
        'operating_hours',
        'cit_provider_id',
        'institution_id',
        'services_offered',
        'vault_account',
        'till_account',
        'petty_cash_account'
    ];

    protected $casts = [
        'opening_date' => 'date',
        'services_offered' => 'array'
    ];

    /**
     * Get the CIT provider for this branch.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function citProvider()
    {
        return $this->belongsTo(CashInTransitProvider::class, 'cit_provider_id');
    }

    /**
     * Get the branch manager user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'branch_manager');
    }
}