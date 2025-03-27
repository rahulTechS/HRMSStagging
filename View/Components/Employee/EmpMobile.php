<?php

namespace App\View\Components\Employee;

use Illuminate\View\Component;
use App\Models\Employee\Employee_attribute;
use App\Models\Employee\Employee_details;

class EmpMobile extends Component
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
		$empCode = $emp->emp_id;
        $eMod = Employee_attribute::where('emp_id',$empCode)->where("attribute_code","LC_Number")->first();
		if(!empty($eMod))
			$this->eId = $eMod->attribute_values;
	    else
			$this->eId = '--';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Employee.get-emp-no');
    }
}
