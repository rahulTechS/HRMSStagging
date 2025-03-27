<?php

namespace App\View\Components\Header;

use Illuminate\View\Component;
use Illuminate\Http\Request;
use App\Models\Entry\Employee;
use App\Models\Consultancy\Resumedetails;
class NotificationLists extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
	public $arrayList;
    public function __construct(Request $request)
    {
		
			$empId = $request->session()->get('EmployeeId');
			$resumeDetails = Resumedetails::where("emp_id",$empId)->get();
			$resumeDetailsShortlisted = Resumedetails::where("emp_id",$empId)->where("resume_status",2)->get();
			$resumeDetailsRejected = Resumedetails::where("emp_id",$empId)->where("resume_status",3)->get();
			$resumeDetailsPending = Resumedetails::where("emp_id",$empId)->where("resume_status",1)->get();
			
			$arrayResume = array();
			$arrayResume['totalResume'] = '<li>
                                <a href="#"><div class="yellow">
			<i class="fa fa-circle"></i>
			'.count($resumeDetails).' resume submitted by you
			</div></a>
                            </li>';
			$arrayResume['shortlisted'] = '<li>
                                <a href="#"><div class="green">
			<i class="fa fa-circle"></i>
			'.count($resumeDetailsShortlisted).' resume shortlisted by admin
			</div></a>
                            </li>';
			
			$arrayResume['rejected'] = '<li>
                                <a href="#"><div class="red">
			<i class="fa fa-circle"></i>
			'.count($resumeDetailsRejected).' resume rejected by admin
			</div></a>
                            </li>';
			$arrayResume['yellow'] = '<li>
                                <a href="#"><div class="yellow">
			<i class="fa fa-circle"></i>
			'.count($resumeDetailsPending).' resume review pending by admin
			</div></a>
                            </li>';
        $this->arrayList = $arrayResume;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.header.notification-lists');
    }
}
