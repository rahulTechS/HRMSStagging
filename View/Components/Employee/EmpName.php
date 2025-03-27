<?php

namespace App\View\Components\Employee;

use Illuminate\View\Component;
use App\Models\Employee\Employee_details;

class EmpName extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $eId;
    public function __construct($eId)
    {
        $eMod = Employee_details::where('id',$eId)->first();
        $this->eId = $eMod->first_name.' '.$eMod->last_name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Employee.get-emp-name');
    }
}
