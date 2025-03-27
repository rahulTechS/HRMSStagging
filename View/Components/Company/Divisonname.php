<?php

namespace App\View\Components\Company;

use Illuminate\View\Component;
use App\Models\Company\Divison;
class Divisonname extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $divisonID;
    public function __construct($divisonID)
    {
       $divisonD =  Divison::where("id",$divisonID)->first();
        $this->divisonID = $divisonD->divison_name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.company.get-divison-name');
    }
}
