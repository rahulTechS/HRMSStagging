<?php

namespace App\Models\Employee_Leaves;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestedLeaves extends Model
{
    use SoftDeletes;
    protected $table = 'leaves_request';
}
