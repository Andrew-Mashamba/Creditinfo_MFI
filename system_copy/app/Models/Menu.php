<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menus';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'menu_name',
        'description',
        'menu_title',
        'status',
    ];

    /**
     * Get the role menu actions for the menu.
     */
    public function roleMenuActions()
    {
        return $this->hasMany(RoleMenuAction::class, 'menu_id');
    }

    public function menuActions()
    {
        return $this->hasMany(MenuAction::class);
    }
}
