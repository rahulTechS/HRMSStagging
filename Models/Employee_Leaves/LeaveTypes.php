<?php

namespace App\Models\Employee_Leaves;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveTypes extends Model
{
    use SoftDeletes;
    protected $table = 'leave_types';
}
