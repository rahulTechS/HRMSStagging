<?php

namespace App\Models\Employee_Attendance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HolidayList extends Model
{
    use SoftDeletes;
    protected $table = 'holiday_list';
}