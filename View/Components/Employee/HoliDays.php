<?php

namespace App\View\Components\Employee;

use Illuminate\View\Component;


use App\Models\Employee\EmployeeAttendanceModel;
class HoliDays extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $eId;
    public function __construct($eId)
    {
			$month = (int)$eId;
			$year = 2022;	
			$emp_obj = new EmployeeAttendanceModel();
			$empdetails = EmployeeAttendanceModel::whereMonth("attendance_date",$month)->where("attendance_value",'H')->selectraw("count(id) as totalHolidays")->first();
			 $this->eId = $empdetails->totalHolidays;
		
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Employee.get-valid-days');
    }
}
