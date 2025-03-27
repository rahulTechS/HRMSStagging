<?php

namespace App\View\Components\Recruiter;

use Illuminate\View\Component;
use App\Models\Consultancy\ConsultancyModel;

class ConsultancyName extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $cId;
    public function __construct($cId)
    {
        $cMod = ConsultancyModel::where('id',$cId)->first();
        $this->cId = $cMod->consultancy_name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Recruiter.get-consultancy-name');
    }
}
