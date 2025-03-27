<?php

namespace App\View\Components\Visa;

use Illuminate\View\Component;
use App\Models\Visa\VisaStage;
class VisaStageName extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
	public $stageId;
	
    public function __construct($stageId)
    {
		$visaStagedObj = VisaStage::where('id',$stageId)->first();
        $this->stageId = $visaStagedObj->stage_name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.visa.visa-stage-name');
    }
}
