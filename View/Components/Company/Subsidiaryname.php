<?php

namespace App\View\Components\Company;

use Illuminate\View\Component;
use App\Models\Company\Subsidiary;
class Subsidiaryname extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
   public  $subsidiaryID;
    public function __construct($subsidiaryID)
    {
       $subsidiaryD =  Subsidiary::where("id",$subsidiaryID)->first();
        $this->subsidiaryID = $subsidiaryD->s_name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.company.get-subsidiary-name');
    }
}
