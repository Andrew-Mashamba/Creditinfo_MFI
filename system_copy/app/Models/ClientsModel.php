<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use App\Models\Guarantor;
use App\Models\Loan;
use App\Models\Saving;
use App\Models\Bill;
use App\Models\AccountsModel;
use App\Models\ShareTransaction;
use App\Models\SharesModel;

class ClientsModel extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    use Search;
    protected $table = 'clients';
    protected $guarded = [];
    protected $searchable = [
        'client_number',
        'first_name',
        'last_name',
        'middle_name',
        'business_name',
        'incorporation_number',
        'mobile_phone_number',
        'email',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'income_available' => 'decimal:2',
        'hisa' => 'decimal:2',
        'akiba' => 'decimal:2',
        'amana' => 'decimal:2'
    ];

    /**
     * Get all documents associated with the client.
     */
    public function documents()
    {
        return $this->hasMany(ClientDocument::class, 'client_id');
    }

    /**
     * Get the client's profile photo.
     */
    public function profilePhoto()
    {
        return $this->hasOne(ClientDocument::class, 'client_id')
            ->where('document_type', 'profile_photo');
    }

    /**
     * Get the client's application letter.
     */
    public function applicationLetter()
    {
        return $this->hasOne(ClientDocument::class, 'client_id')
            ->where('document_type', 'application_letter');
    }

    /**
     * Get the client's guarantor.
     */
    public function guarantor()
    {
        return $this->hasOne(Guarantor::class, 'client_id');
    }

    /**
     * Get the client's branch.
     */
    public function branch()
    {
        return $this->belongsTo(BranchesModel::class, 'branch_id');
    }

    /**
     * Get the client's member group.
     */
    public function memberGroup()
    {
        return $this->belongsTo(MemberGroup::class, 'member_group_id');
    }

    /**
     * Get the client's loans.
     */
    public function loans()
    {
        return $this->hasMany(Loan::class, 'client_number', 'client_number');
    }

    /**
     * Get the client's savings.
     */
    public function savings()
    {
        return $this->hasMany(Saving::class, 'client_number', 'client_number')->savings();
    }

    /**
     * Get the client's shares.
     */
    public function shares()
    {
        return $this->hasMany(Share::class, 'client_number', 'client_number')->shares();
    }

    /**
     * Get the client's bills.
     */
    public function bills()
    {
        return $this->hasMany(Bill::class, 'client_number', 'client_number');
    }

    /**
     * Get the client's accounts.
     */
    public function accounts()
    {
        return $this->hasMany(AccountsModel::class, 'client_number', 'client_number');
    }

    /**
     * Get the client's dividends.
     */
    public function dividends()
    {
        return $this->hasMany(Dividend::class, 'member_id', 'id');
    }

    /**
     * Get the client's interest payables.
     */
    public function interestPayables()
    {
        return $this->hasMany(InterestPayable::class, 'member_id', 'id');
    }

    /**
     * Get the full name of the client.
     */
    public function getFullNameAttribute()
    {
        if ($this->membership_type === 'Individual') {
            return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
        }
        return $this->business_name;
    }

    /**
     * Get the name of the client (alias for full_name).
     */
    public function getNameAttribute()
    {
        return $this->full_name;
    }

    /**
     * Scope a query to only include active clients.
     */
    public function scopeActive($query)
    {
        return $query->where('client_status', 'ACTIVE');
    }

    /**
     * Scope a query to only include pending clients.
     */
    public function scopePending($query)
    {
        return $query->where('client_status', 'PENDING');
    }

    /**
     * Scope a query to only include blocked clients.
     */
    public function scopeBlocked($query)
    {
        return $query->where('client_status', 'BLOCKED');
    }

    public function scopeNew($query)
    {
        return $query->where('client_status', 'NEW CLIENT');
    }

    public function scopeWithTrashed($query)
    {
        return $query->withTrashed();
    }

    public function scopeOnlyTrashed($query)
    {
        return $query->onlyTrashed();
    }

    public function shareTransactions()
    {
        return $this->hasManyThrough(
            ShareTransaction::class,
            SharesModel::class,
            'member_id',
            'share_id',
            'id',
            'id'
        );
    }
}
