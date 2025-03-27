<?php

namespace App\View\Components\Offer;

use Illuminate\View\Component;

use App\Models\Offerletter\SalaryBreakup;
class CapName extends Component
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
		
       
		if(!empty($capMod))
			$this->cId = $capMod->caption;
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
        return view('components.Offer.cap_name');
    }
}
