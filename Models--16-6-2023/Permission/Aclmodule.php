<?php

namespace App\Models\Permission;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Aclmodule extends Model
{
    //use HasFactory;
    protected $table='module_details';

    protected $fillable = [
        'module_name', 'parent_id','status','header_menu_id','parent_module_id','pathInfoDetails','awesome_font_icons'
    ];
}
