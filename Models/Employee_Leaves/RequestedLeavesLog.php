<?php

namespace App\Models\Employee_Leaves;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestedLeavesLog extends Model
{
    
    protected $table = 'employee_leaves_history_log';
}