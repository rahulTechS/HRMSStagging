<?php

namespace App\View\Components\Masterpayoutwidget;

use Illuminate\View\Component;
use App\Models\Entry\Employee;
use Request;

use App\Models\Dashboard\WidgetCreation;

use App\Models\Dashboard\Widgetlayouts\WidgetOnboardingHiring;
use App\Models\Job\JobOpening;
use App\Models\Company\Department;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\Employee\Employee_details;
use App\Models\Dashboard\MasterPayout;
use App\Models\EmpProcess\JobFunctionPermission;
use Session;
class MasterPayoutData extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */


	public $widgetName;
	public $widgetId;
	public $payoutdata;
	
	
    public function __construct($widgetId)
    {
		$whereraw='';
		$empsessionId=Request::session()->get('EmployeeId');
		$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   
							$whereraw = 'dept_id IN('.$empdata->dept_id.')';
							
					   }
				   }
					else{
						
					}
		if($whereraw!=''){			
		$payoutdata = MasterPayout::whereRaw($whereraw)->get();
		}
		else{
		$payoutdata = MasterPayout::get();	
		}
		$widget_name = WidgetCreation::where("id",$widgetId)->first()->widget_name;
		
		$this->widgetName = $widget_name;
		$this->widgetId = $widgetId;
		$this->payoutdata = $payoutdata;
        
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.masterpayoutwidget.master_payout_data');
    }
	
	
	public static function getJobOpeningName($jobId)
	{
		$data  =  JobOpening::where("id",$jobId)->first();
		if($data != '')
		{
			$departmentName = Department::where("id",$data->department)->first()->department_name;
			return $data->name.'<br/>'.$departmentName.'-'.$data->location;
		}
		else
		{
			return "No Name";
		}
		
	}
	public static function getJobOpeningDptId($jobId)
	{
		$data  =  JobOpening::where("id",$jobId)->first();
		if($data != '')
		{
			
			return $data->department;
		}
		else
		{
			return "No Name";
		}
		
	}
	public static function getCheckLoginUserData($userid){
		 $departmentDetails = JobFunctionPermission::where("user_id",$userid)->first();
		   if($departmentDetails != '')
		   {
			   return $departmentDetails->job_function_id;
		   }
		   else
		   {
			   return 'All';
		   }
		  
	}
	

}
