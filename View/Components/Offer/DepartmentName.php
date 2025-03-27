<?php

namespace App\View\Components\Offer;

use Illuminate\View\Component;

use App\Models\Offerletter\SalaryBreakup;
use App\Models\Company\Department;

class DepartmentName extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $cId;
    public function __construct($cId)
    {
		$capMod = SalaryBreakup::where("id",$cId)->first();
		$deptID = $capMod->dept_id;
		$deptMod = Department::where("id",$deptID)->first();
		if(!empty($deptMod))
			$this->cId = $deptMod->department_name;
	    else
			$this->cId = '--';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Offer.department_name');
    }
}
