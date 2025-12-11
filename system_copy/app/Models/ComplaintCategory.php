<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // Accessors
    public function getComplaintsCountAttribute()
    {
        return $this->complaints()->count();
    }

    public function getActiveComplaintsCountAttribute()
    {
        return $this->complaints()->whereNotIn('status_id', [3, 4])->count(); // Exclude resolved and closed
    }
}
