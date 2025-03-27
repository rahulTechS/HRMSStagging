<?php

namespace App\View\Components\Header;

use Illuminate\View\Component;
use Illuminate\Http\Request;
use App\Models\Entry\Employee;
class CustomerName extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
	public $customerId;
    public function __construct(Request $request)
    {
		$userId = $request->session()->get('EmployeeId');
		$empDetails = Employee::where('id',$userId)->first();
        $this->customerId = $empDetails->fullname;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.header.customer-name');
    }
}
