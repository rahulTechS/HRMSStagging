<?php

namespace App\Models\Employee_Attendance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpAttendance2 extends Model
{
    use SoftDeletes;
    protected $table = 'emp_attendance2';
}
