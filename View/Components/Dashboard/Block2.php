<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;
use App\Models\Entry\Employee;
use Illuminate\Http\Request;
class Block2 extends Component
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
		$details['father_name'] = $empDetails->father_name;
		$details['email'] = $empDetails->email;
		$details['contact_no'] = $empDetails->contact_no;
		$details['local_address'] = $empDetails->local_address;
		$details['permanent_address'] = $empDetails->permanent_address;
		$details['Dob'] = $empDetails->Dob;
        $this->customerDetails = $details;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.dashboard.block2');
    }
}
