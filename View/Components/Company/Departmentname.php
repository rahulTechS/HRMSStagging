<?php

namespace App\View\Components\Company;

use Illuminate\View\Component;
use App\Models\Company\Department;
class Departmentname extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $departmentId;
    public function __construct($departmentId)
    {
        $departmentD = Department::where("id",$departmentId)->first();
        $this->departmentId = $departmentD->department_name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.company.get-department-name');
    }
}
