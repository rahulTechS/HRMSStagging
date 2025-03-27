<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;
use App\Models\Entry\Employee;
use Illuminate\Http\Request;
class Block1 extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
	public $customerDetails;
    public function __construct(Request $request)
    {
        $userId = $request->session()->get('EmployeeId');
		$empDetails = Employee::where('id',$userId)->first();
		$details = array();
		$details['fullname'] = $empDetails->fullname;
		$details['designation'] = $empDetails->designation;
		$details['pics'] = $empDetails->pics;
        $this->customerDetails = $details;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.dashboard.block1');
    }
}
