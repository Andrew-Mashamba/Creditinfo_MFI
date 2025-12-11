<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientDocument extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'document_type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'description',
        'is_verified',
        'verified_at',
        'verified_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'file_size' => 'integer'
    ];

    /**
     * Get the client that owns the document.
     */
    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_id');
    }

    /**
     * Get the user who verified the document.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope a query to only include verified documents.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope a query to only include unverified documents.
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope a query to only include documents of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Mark the document as verified.
     */
    public function markAsVerified(User $user)
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $user->id
        ]);
    }

    /**
     * Mark the document as unverified.
     */
    public function markAsUnverified()
    {
        $this->update([
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null
        ]);
    }

    /**
     * Get the full URL to the document.
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Get the human-readable file size.
     */
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }
}
