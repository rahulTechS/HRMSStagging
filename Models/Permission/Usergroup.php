<?php

namespace App\Models\Permission;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Usergroup extends Model
{
    //use HasFactory;
    protected $table='group_permission';

    protected $fillable = [
        'group_name', 'group_des','group_caption','permission_setting_status','group_status'
    ];
}
