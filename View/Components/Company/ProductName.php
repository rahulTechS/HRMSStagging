<?php

namespace App\View\Components\Company;

use Illuminate\View\Component;
use App\Models\Company\Product;

class ProductName extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $pId;
    public function __construct($pId)
    {
        $pMod = Product::where('id',$pId)->first();
        $this->pId = $pMod->product_name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.company.get-product-name');
    }
}
