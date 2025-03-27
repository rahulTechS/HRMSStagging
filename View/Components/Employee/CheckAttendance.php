<?php

namespace App\View\Components\Employee;

use Illuminate\View\Component;

use App\Models\Employee\EmployeeAttendanceModel;

class CheckAttendance extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $eId;
	public $edays;
  
    public function __construct($eId,$eDate,$edays)
    {
		$eDateValue = date("Y-m-d",strtotime($eDate));
		/*
		*get for holiday
		*/
		$empDetailsHoliday = EmployeeAttendanceModel::where("attendance_value","H")->where('attendance_date',$eDateValue)->first();
		if($empDetailsHoliday != '')
		{
			$this->eId = 'H';
		}
		else
		{
		/*
		*get for holiday
		*/
		$empDetails = EmployeeAttendanceModel::where("emp_id",$eId)->where('attendance_date',$eDateValue)->first();
			if($empDetails != '')
			{
			$this->eId = $empDetails->attendance_value;
			}
			else
			{
				$this->eId = 'NO';
			}
			
		}
		$this->edays = $edays;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Employee.get-check-attendance');
    }
}
