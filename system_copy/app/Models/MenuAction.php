<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'name',
        'description'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function roleMenuActions()
    {
        return $this->hasMany(RoleMenuAction::class);
    }
}
