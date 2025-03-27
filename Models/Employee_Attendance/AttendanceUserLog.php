<?php

namespace App\Models\Employee_Attendance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceUserLog extends Model
{
    use SoftDeletes;
    protected $table = 'attendance_user_log';
}
