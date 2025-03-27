<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Company\ParentCompany;

class Common extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $parentCompany;
  
    public function __construct($parentCompany)
    {
        $parentCompanyObj = ParentCompany::where("id",$parentCompany)->first();

        $this->parentCompany = $parentCompanyObj->parent_companyname;
        
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.common');
    }
}
