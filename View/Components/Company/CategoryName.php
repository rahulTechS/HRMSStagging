<?php

namespace App\View\Components\Company;

use Illuminate\View\Component;
use App\Models\Company\category;

class CategoryName extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $categoryId;
    public function __construct($categoryId)
    {
        $cateMod = category::where('id',$categoryId)->first();
        $this->categoryId = $cateMod->category_name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.company.get-category-name');
    }
}
