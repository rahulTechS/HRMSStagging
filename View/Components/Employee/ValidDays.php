<?php

namespace App\View\Components\Employee;

use Illuminate\View\Component;

use App\Models\Employee\EmployeeAttendanceModel;

class ValidDays extends Component
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
			$daysInMonth = $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
			/*
			*getting sunday
			*/
			 $sundays=0;
			$total_days=$daysInMonth;
			for($i=1;$i<=$total_days;$i++)
			{
				if(date('N',strtotime($year.'-'.$month.'-'.$i))==7)
				{	
					$sundays++;
				}
			}
			/*
			*getting sunday
			*/
			$validd = $daysInMonth - $sundays;
			
			/*
			*get Holiday in months
			*/
			$month = (int)$eId;
			$year = 2022;	
			$emp_obj = new EmployeeAttendanceModel();
			$empdetails = EmployeeAttendanceModel::whereMonth("attendance_date",$month)->where("attendance_value",'H')->selectraw("count(id) as totalHolidays")->first();
			/*
			*get Holiday in months
			*/
			$holidaysCount = $empdetails->totalHolidays;
			$newValidDays = $validd-$holidaysCount;
			 $this->eId = $newValidDays;
		
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
