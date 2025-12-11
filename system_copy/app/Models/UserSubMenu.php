<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubMenu extends Model
{
    use HasFactory;

    protected $table = 'user_sub_menus'; // table name in the database
    protected $fillable = ['user_id', 'menu_id', 'sub_menu_id', 'permission'];


}
