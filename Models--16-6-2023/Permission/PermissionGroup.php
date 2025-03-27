<?php

namespace App\Models\Permission;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class PermissionGroup extends Model
{
    //use HasFactory;
    protected $table='permission_group';

    protected $fillable = [
        'group_id', 'module_id', 'action_ids','status'
    ];
}
