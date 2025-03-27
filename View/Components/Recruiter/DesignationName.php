<?php

namespace App\View\Components\Recruiter;

use Illuminate\View\Component;
use App\Models\Recruiter\Designation;

class DesignationName extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $dId;
    public function __construct($dId)
    {
        $eMod = Designation::where('id',$dId)->first();
        $this->dId = $eMod->name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Recruiter.get-designation-name');
    }
}
