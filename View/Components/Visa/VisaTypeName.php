<?php

namespace App\View\Components\Visa;

use Illuminate\View\Component;
use App\Models\Visa\visaType;
class VisaTypeName extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
	public $typeId;
	
    public function __construct($typeId)
    {
		if(!empty($typeId) && $typeId != 0)
		{
		$typeMod = visaType::where('id',$typeId)->first();
        $this->typeId = $typeMod->title;
		}
		else
		{
			$this->typeId = $typeId;
		}
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.visa.visa-type-name');
    }
}
