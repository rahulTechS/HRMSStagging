<?php

namespace App\View\Components\Offer;

use Illuminate\View\Component;

use App\Models\Offerletter\SalaryBreakup;
use App\Models\Recruiter\Designation;

class DesignationName extends Component
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
		$designID = $capMod->designation;
		$designMod = Designation::where("id",$designID)->first();
		if(!empty($designMod))
			$this->cId = $designMod->name;
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
        return view('components.Offer.designation_name');
    }
}
