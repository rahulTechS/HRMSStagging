<?php

namespace App\View\Components\Employee;

use Illuminate\View\Component;

use App\Models\Employee\Employee_details;
use App\Models\Company\Department;

class EmpDepartment extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $eId;
    public function __construct($eId)
    {
		$emp = Employee_details::where("id",$eId)->first();
		$dept_id = $emp->dept_id;
        $dMod = Department::where('id',$dept_id)->first();
        $this->eId = $dMod->department_name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Employee.get-department_name');
    }
}
