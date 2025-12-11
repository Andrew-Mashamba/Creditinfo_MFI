<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
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
        return $this->hasMany(Complaint::class, 'status_id');
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

    public function getColorClassAttribute()
    {
        // Convert hex color to Tailwind-like class
        $colorMap = [
            '#F59E0B' => 'bg-yellow-100 text-yellow-800',
            '#3B82F6' => 'bg-blue-100 text-blue-800',
            '#10B981' => 'bg-green-100 text-green-800',
            '#6B7280' => 'bg-gray-100 text-gray-800',
            '#EF4444' => 'bg-red-100 text-red-800',
        ];

        return $colorMap[$this->color] ?? 'bg-gray-100 text-gray-800';
    }
}
