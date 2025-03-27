<?php

namespace App\View\Components\Employee;

use Illuminate\View\Component;

use App\Models\Employee\EmployeeAttendanceModel;
use Illuminate\Http\Request;
class LeaveDays extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $eId;
    public function __construct($eId,Request $request)
    {
		if(!empty($request->session()->get('applied_month')))
			{
			
				$month = $request->session()->get('applied_month');
			}
			else
			{
				$month = date('m');
			}
		$empdetails = EmployeeAttendanceModel::whereMonth("attendance_date",$month)->where("emp_id",$eId)->where("attendance_value","L")->selectraw("count(id) as totalAttendance,emp_id")->groupBy('emp_id')->first();
        if(!empty($empdetails))
		{
			$this->eId = $empdetails->totalAttendance;
		}
		else
		{
			 $this->eId = 0;
		}
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Employee.get-leave-days');
    }
}
