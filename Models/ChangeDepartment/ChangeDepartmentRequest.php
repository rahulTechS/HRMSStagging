<?php

namespace App\Models\ChangeDepartment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChangeDepartmentRequest extends Model
{
    use SoftDeletes;
    protected $table = 'change_department_request';
}
