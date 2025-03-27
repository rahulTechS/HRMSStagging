<?php

namespace App\View\Components\Recruiter;

use Illuminate\View\Component;
use App\Models\Recruiter\Stages;

class StageName extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $stageId;
    public function __construct($stageId)
    {
        $eMod = Stages::where('id',$stageId)->first();
        $this->stageId = $eMod->name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Recruiter.get-stages-name');
    }
}
