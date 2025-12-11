<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class departmentsList extends Model
{
    use HasFactory;

    /**
     * @var mixed|null
     */

    protected $table = 'departments';
    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'department_id', 'id');
    }
}
