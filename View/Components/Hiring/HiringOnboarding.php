<?php

namespace App\View\Components\Hiring;

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
use Session;
class HiringOnboarding extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */


	public $widgetName;
	public $widgetId;
	public $widgetData;
	public $recruiters;
	public $recruiterCategory;
	public $recruitersSelected;
	public $recruiterCategorySelected;
	public $departmentLists;
	public $DepartmentSelected;
	
    public function __construct($widgetId)
    {
        $widgetData = WidgetOnboardingHiring::where("widget_id",$widgetId)->first();
		$widget_name = WidgetCreation::where("id",$widgetId)->first()->widget_name;
		$recruiters = RecruiterDetails::where("status",1)->get();
		$recruiterCategory = RecruiterCategory::where("status",1)->get();
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $this->widgetName = $widget_name;
        $this->widgetId = $widgetId;
        $this->widgetData = $widgetData;
        $this->recruiters = $recruiters;
        $this->recruiterCategory = $recruiterCategory;
		$this->departmentLists = $departmentLists;
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]') != NULL)	
		{
			$this->recruiterCategorySelected = Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]');
		}
		else
		{
			$this->recruiterCategorySelected = '';
		}
		
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)	
		{
			$this->recruitersSelected = explode(",",Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]'));
		}
		else
		{
			$this->recruitersSelected = '';
		}

		if(Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != '' && Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != NULL)	
		{
			$this->DepartmentSelected = explode(",",Request::session()->get('widgetFilterHiringDept['.$widgetId.']'));
		}
		else
		{
			$this->DepartmentSelected ='';
		}
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Hiring.hiring_onboarding_com');
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
	public static function getdeptName($dept){
		//$departmentName = Department::where("id",$data->department)->first()->department_name;
		$name = '';
		foreach($dept as $r)
		{
			if($name == '')
			{
				$name = Department::where("id",$r)->first()->department_name;
			}
			else
			{
				$name = $name.','.Department::where("id",$r)->first()->department_name;
			}
		}
		return $name;
	}
	
	public static function getCandidateContacted($jobId,$widgetId)
	{
		$currentDate = date("Y-m-d");
		$date30DaysBack = date("Y").'-'.date("m").'-'.'01';
		//echo $date30DaysBack;exit;
		$whereraw='';
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			if($whereraw == '')
			{
			$whereraw = 'recruiter IN('.$recruiterIds.')';
			}
			else
			{
				$whereraw .= ' AND recruiter IN('.$recruiterIds.')';
			}
			
			
			
		}
		if(Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != '' && Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != NULL)
		{
			$deptIds =  Request::session()->get('widgetFilterHiringDept['.$widgetId.']');
			
			$deptIdsArray = explode(",",$deptIds);
			if($whereraw == '')
			{
			$whereraw = 'department IN('.$deptIds.')';
			}
			else
			{
				$whereraw .= ' AND department IN('.$deptIds.')';
			}
			
			
			
		}
		
		
		if($whereraw != '')	{	
		return InterviewProcess::whereRaw($whereraw)->whereBetween("created_at", [$date30DaysBack, $currentDate])->where("job_opening",$jobId)->get()->count();
		}
		else
		{
		return InterviewProcess::whereBetween("created_at", [$date30DaysBack, $currentDate])->where("job_opening",$jobId)->get()->count();
		}
	}
	
	
	public static function getCandidateFinalDiscussion($jobId,$widgetId)
	{
		$currentDate = date("Y-m-d");
		$date30DaysBack = date("Y").'-'.date("m").'-'.'01';
		$whereraw='';
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			if($whereraw == '')
			{
			$whereraw = 'recruiter_name IN('.$recruiterIds.')';
			}
			else
			{
				$whereraw .= ' AND recruiter_name IN('.$recruiterIds.')';
			}
					
		}
		
		if(Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != '' && Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != NULL)
		{
			$deptIds =  Request::session()->get('widgetFilterHiringDept['.$widgetId.']');
			
			$deptIdsArray = explode(",",$deptIds);
			if($whereraw == '')
			{
			$whereraw = 'department IN('.$deptIds.')';
			}
			else
			{
				$whereraw .= ' AND department IN('.$deptIds.')';
			}
		}	
		if($whereraw!=''){	
		return DocumentCollectionDetails::whereRaw($whereraw)->whereBetween("created_at", [$date30DaysBack, $currentDate])->where("job_opening",$jobId)->get()->count();				
		}
		else
		{
			/*return  InterviewProcess::select("interview_info.id","interview_details.created_at","interview_details.status","interview_details.interview_type")
				->join("interview_details","interview_details.interview_id","=","interview_info.id")
				->whereBetween("interview_details.created_at", [$date30DaysBack, $currentDate])
				->where("interview_details.interview_type","final discussion")
				->where("interview_details.status",2)
				->where("interview_info.job_opening",$jobId)->get()->count();*/
				return DocumentCollectionDetails::whereBetween("created_at", [$date30DaysBack, $currentDate])->where("job_opening",$jobId)->get()->count();
		}
		
	}
	
	public static function getofferletterPending($jobId,$widgetId)
	{
		$whereraw='';
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			if($whereraw == '')
			{
			$whereraw = 'recruiter_name IN('.$recruiterIds.')';
			}
			else
			{
				$whereraw .= ' AND recruiter_name IN('.$recruiterIds.')';
			}
					
		}
		
		if(Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != '' && Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != NULL)
		{
			$deptIds =  Request::session()->get('widgetFilterHiringDept['.$widgetId.']');
			
			$deptIdsArray = explode(",",$deptIds);
			if($whereraw == '')
			{
			$whereraw = 'department IN('.$deptIds.')';
			}
			else
			{
				$whereraw .= ' AND department IN('.$deptIds.')';
			}
		}	
		if($whereraw!='')
		{
			
			return DocumentCollectionDetails::whereRaw($whereraw)->where("offer_letter_onboarding_status",1)->where("job_opening",$jobId)->whereIn("visa_process_status",array(0,1))->whereIn("ok_visa",array(1,4))->where("backout_status",1)->get()->count();
		}
		else
		{
			return DocumentCollectionDetails::where("offer_letter_onboarding_status",1)->where("job_opening",$jobId)->whereIn("visa_process_status",array(0,1))->whereIn("ok_visa",array(1,4))->where("backout_status",1)->get()->count();
		}
	}
	
	public static function getofferletterCompleted($jobId,$widgetId)
	{
		$whereraw='';
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			if($whereraw == '')
			{
			$whereraw = 'recruiter_name IN('.$recruiterIds.')';
			}
			else
			{
				$whereraw .= ' AND recruiter_name IN('.$recruiterIds.')';
			}
					
		}
		
		if(Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != '' && Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != NULL)
		{
			$deptIds =  Request::session()->get('widgetFilterHiringDept['.$widgetId.']');
			
			$deptIdsArray = explode(",",$deptIds);
			if($whereraw == '')
			{
			$whereraw = 'department IN('.$deptIds.')';
			}
			else
			{
				$whereraw .= ' AND department IN('.$deptIds.')';
			}
		}
		if($whereraw!='')
		{
			
			
			return DocumentCollectionDetails::whereRaw($whereraw)->where("offer_letter_onboarding_status",2)->where("job_opening",$jobId)->whereIn("visa_process_status",array(0,1))->whereIn("ok_visa",array(1,4))->where("backout_status",1)->get()->count();
		}
		else
		{
			return DocumentCollectionDetails::where("offer_letter_onboarding_status",2)->where("job_opening",$jobId)->whereIn("visa_process_status",array(0,1))->whereIn("ok_visa",array(1,4))->where("backout_status",1)->get()->count();
		}
	}
	
	public static function getBVGPending($jobId,$widgetId)
	{
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			return DocumentCollectionDetails::whereIn("bgverification_status",array(5,3))->where("job_opening",$jobId)->where("backout_status",1)->where("offer_letter_onboarding_status",1)->whereIn("recruiter_name",$recruiterIdsArray)->get()->count();
		}
		else
		{
			return DocumentCollectionDetails::whereIn("bgverification_status",array(5,3))->where("job_opening",$jobId)->where("backout_status",1)->where("offer_letter_onboarding_status",1)->get()->count();
		}
	}
	
	public static function getMOLTyped($jobId,$widgetId)
	{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$whereraw='';
			if($whereraw==''){
			$whereraw = "mol_date >= '".$fromDate."' and mol_date <= '".$toDate."'";
			}
			else{
				$whereraw = " And mol_date >= '".$fromDate."' and mol_date <= '".$toDate."'";
			}
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			if($whereraw == '')
			{
			$whereraw = 'recruiter_name IN('.$recruiterIds.')';
			}
			else
			{
				$whereraw .= ' AND recruiter_name IN('.$recruiterIds.')';
			}
					
		}
		
		if(Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != '' && Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != NULL)
		{
			$deptIds =  Request::session()->get('widgetFilterHiringDept['.$widgetId.']');
			
			$deptIdsArray = explode(",",$deptIds);
			if($whereraw == '')
			{
			$whereraw = 'department IN('.$deptIds.')';
			}
			else
			{
				$whereraw .= ' AND department IN('.$deptIds.')';
			}
		}
		if($whereraw!='')
		{
			
			return DocumentCollectionDetails::where("mol_date","!=",NULL)->whereRaw($whereraw)->where("job_opening",$jobId)->where("backout_status",1)->where("visa_process_status","!=",4)->get()->count();
		}
		else
		{
			return DocumentCollectionDetails::where("mol_date","!=",NULL)->whereRaw($whereraw)->where("job_opening",$jobId)->where("backout_status",1)->where("visa_process_status","!=",4)->get()->count();
		}
	}
	
	public static function getEVisa($jobId,$widgetId)
	{
		$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$whereraw='';
			if($whereraw==''){
			$whereraw = "evisa_start_date >= '".$fromDate."' and evisa_start_date <= '".$toDate."'";
			}
			else{
				$whereraw = " And evisa_start_date >= '".$fromDate."' and evisa_start_date <= '".$toDate."'";
			}
			if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
			{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			if($whereraw == '')
			{
			$whereraw = 'recruiter_name IN('.$recruiterIds.')';
			}
			else
			{
				$whereraw .= ' AND recruiter_name IN('.$recruiterIds.')';
			}
					
			}
		
		if(Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != '' && Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != NULL)
		{
			$deptIds =  Request::session()->get('widgetFilterHiringDept['.$widgetId.']');
			
			$deptIdsArray = explode(",",$deptIds);
			if($whereraw == '')
			{
			$whereraw = 'department IN('.$deptIds.')';
			}
			else
			{
				$whereraw .= ' AND department IN('.$deptIds.')';
			}
		}
		if($whereraw!='')
		{
		
			return DocumentCollectionDetails::whereRaw($whereraw)->where("job_opening",$jobId)->where("backout_status",1)->get()->count();
		}
		else
		{
			return DocumentCollectionDetails::whereRaw($whereraw)->where("job_opening",$jobId)->where("backout_status",1)->get()->count();
		}
	}
	
	public static function getOnboarded($jobId,$widgetId)
	{
		$currentDate = date("Y-m-d");
		$date30DaysBack =date("Y").'-'.date("m").'-'.'01';
		$whereraw='';
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
			{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			if($whereraw == '')
			{
			$whereraw = 'recruiter IN('.$recruiterIds.')';
			}
			else
			{
				$whereraw .= ' AND recruiter IN('.$recruiterIds.')';
			}
					
			}
		
		if(Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != '' && Request::session()->get('widgetFilterHiringDept['.$widgetId.']') != NULL)
		{
			$deptIds =  Request::session()->get('widgetFilterHiringDept['.$widgetId.']');
			
			$deptIdsArray = explode(",",$deptIds);
			if($whereraw == '')
			{
			$whereraw = 'dept_id IN('.$deptIds.')';
			}
			else
			{
				$whereraw .= ' AND dept_id IN('.$deptIds.')';
			}
		}
		if($whereraw!='')
		{
		
			return Employee_details::whereRaw($whereraw)->where("job_opening_id",$jobId)->whereBetween("doj", [$date30DaysBack, $currentDate])->get()->count();
		}
		else
		{
			return Employee_details::where("job_opening_id",$jobId)->whereBetween("doj", [$date30DaysBack, $currentDate])->get()->count();
		}
	}
	public static function getBackOut($jobId,$widgetId)
	{
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			return DocumentCollectionDetails::where("job_opening",$jobId)->where("backout_status",2)->whereIn("recruiter_name",$recruiterIdsArray)->get()->count();
		}
		else
		{
			return DocumentCollectionDetails::where("job_opening",$jobId)->where("backout_status",2)->get()->count();
		}
	}
	
	public static function getRecruiterList($catID)
	{
		return RecruiterDetails::where("recruit_cat",$catID)->where("status",1)->get();
	}
	
	
	public static function getRecruiterNameList($rArray)
	{
		$name = '';
		foreach($rArray as $r)
		{
			if($name == '')
			{
				$name = RecruiterDetails::where("id",$r)->first()->name;
			}
			else
			{
				$name = $name.','.RecruiterDetails::where("id",$r)->first()->name;
			}
		}
		return $name;
	}
	
	public static function getRecruiterCategory($catId)
	{
		if($catId != 'All'  && $catId != '' && $catId != NULL)
		{
		return RecruiterCategory::where("id",$catId)->first()->name;
		}
	}

}
