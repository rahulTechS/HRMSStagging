<?php

namespace App\View\Components\Hcanalytics;

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
use App\Models\Recruiter\Designation;
use App\Models\Attribute\DepartmentFormEntry;
class HcAnalyticsMashreq extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */


	public $widgetName;
	public $widgetId;
	
	public $empdetails;
	public $from_salesTime_shortlist;
	public $to_salesTime_shortlist;
	public $recruiterCategorySelected;
	
    public function __construct($widgetId)
    {
		if(Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != '' && Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != NULL)	
		{
			$this->from_salesTime_shortlist = Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]');
		}
		else
		{
			$this->from_salesTime_shortlist = '';
		}
		
		
		if(Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != '' && Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != NULL)	
		{
			$this->to_salesTime_shortlist = Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]');
		}
		else
		{
			$this->to_salesTime_shortlist = '';
		}
		$design=Designation::where("tlsm",2)->where("department_id",36)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				//print_r($designarray);exit;
		$empdetailsdata = Employee_details::whereIn("designation_by_doc_collection",$designarray)->where("dept_id",36)->where("offline_status",1)->orderBy("id","ASC")->get();
        $empdetails=array();
		if(count($empdetailsdata)>0){
				
				foreach($empdetailsdata as $_Tldata){
					$tL_salesData = Employee_details::where("tl_id",$_Tldata->id)->where("offline_status",1)->get();
					if($tL_salesData!=''){
					$empdetails[$_Tldata->id]=$tL_salesData;
					}
				}
				
				
			}
		//print_r($empdetails);exit;
		
		$widget_name = WidgetCreation::where("id",$widgetId)->first()->widget_name;
		
		/* echo $widget_name;
		exit; */
        $this->widgetName = $widget_name;
        $this->widgetId = $widgetId;
        
		$this->empdetails=$empdetails;
        
		
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
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Hcanalytics.hcanalyticsmashreq');
    }
	
	
	
	public static function getwipdata($empId,$widgetId)
	{
		if(Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != '' && Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != NULL)	
		{
			$date30DaysBack = Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]');
		}		
		else{
		
		$date30DaysBack = date("Y-m-d",strtotime("-30 days"));
		}
		if(Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != '' && Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != NULL)	
		{
			$currentDate = Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]');
		}
		else{
			$currentDate = date("Y-m-d");
		}
		
		return DepartmentFormEntry::whereBetween("submission_date", [$date30DaysBack, $currentDate])->where("emp_id",$empId)->where("form_id",1)->where("form_status","WIP")->get()->count();
		
	}
	public static function getBookeddata($empId,$widgetId)
	{
		if(Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != '' && Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != NULL)	
		{
			$date30DaysBack = Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]');
		}		
		else{
		
		$date30DaysBack = date("Y-m-d",strtotime("-30 days"));
		}
		if(Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != '' && Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != NULL)	
		{
			$currentDate = Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]');
		}
		else{
			$currentDate = date("Y-m-d");
		}
		
		return DepartmentFormEntry::whereBetween("submission_date", [$date30DaysBack, $currentDate])->where("emp_id",$empId)->where("form_id",1)->where("form_status",'like',"%booked%")->get()->count();
		
	}
	public static function getDeclineddata($empId,$widgetId)
	{
		if(Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != '' && Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != NULL)	
		{
			$date30DaysBack = Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]');
		}		
		else{
		
		$date30DaysBack = date("Y-m-d",strtotime("-30 days"));
		}
		if(Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != '' && Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != NULL)	
		{
			$currentDate = Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]');
		}
		else{
			$currentDate = date("Y-m-d");
		}
		
		return DepartmentFormEntry::whereBetween("submission_date", [$date30DaysBack, $currentDate])->where("emp_id",$empId)->where("form_id",1)->where("form_status","declined")->get()->count();
		
	}
	
	public static function getterminateddata($empId,$widgetId)
	{
		if(Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != '' && Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != NULL)	
		{
			$date30DaysBack = Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]');
		}		
		else{
		
		$date30DaysBack = date("Y-m-d",strtotime("-30 days"));
		}
		if(Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != '' && Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != NULL)	
		{
			$currentDate = Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]');
		}
		else{
			$currentDate = date("Y-m-d");
		}
		
		return DepartmentFormEntry::whereBetween("submission_date", [$date30DaysBack, $currentDate])->where("emp_id",$empId)->where("form_id",1)->where("form_status","terminated")->get()->count();
		
	}
	public static function getTotaldata($empId,$widgetId)
	{
		if(Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != '' && Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != NULL)	
		{
			$date30DaysBack = Request::session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]');
		}		
		else{
		
		$date30DaysBack = date("Y-m-d",strtotime("-30 days"));
		}
		if(Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != '' && Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != NULL)	
		{
			$currentDate = Request::session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]');
		}
		else{
			$currentDate = date("Y-m-d");
		}
		$totel=DepartmentFormEntry::whereBetween("submission_date", [$date30DaysBack, $currentDate])->where("emp_id",$empId)->where("form_id",1)->get()->count();
		return $totel;
	}
	
	
	public static function getCandidateFinalDiscussion($jobId,$widgetId)
	{
		$currentDate = date("Y-m-d");
		$date30DaysBack = date("Y-m-d",strtotime("-30 days"));
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			return  InterviewProcess::select("interview_info.id","interview_details.created_at","interview_details.status","interview_details.interview_type")
				->join("interview_details","interview_details.interview_id","=","interview_info.id")
				->whereBetween("interview_details.created_at", [$date30DaysBack, $currentDate])
				->where("interview_details.interview_type","final discussion")
				->where("interview_details.status",2)
				->where("interview_info.job_opening",$jobId)->whereIn("interview_info.recruiter",$recruiterIdsArray)->get()->count();
			
		}
		else
		{
			return  InterviewProcess::select("interview_info.id","interview_details.created_at","interview_details.status","interview_details.interview_type")
				->join("interview_details","interview_details.interview_id","=","interview_info.id")
				->whereBetween("interview_details.created_at", [$date30DaysBack, $currentDate])
				->where("interview_details.interview_type","final discussion")
				->where("interview_details.status",2)
				->where("interview_info.job_opening",$jobId)->get()->count();
		}
		
	}
	
	public static function getofferletterPending($jobId,$widgetId)
	{
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			return DocumentCollectionDetails::where("offer_letter_onboarding_status",1)->where("job_opening",$jobId)->where("backout_status",1)->whereIn("recruiter_name",$recruiterIdsArray)->get()->count();
		}
		else
		{
			return DocumentCollectionDetails::where("offer_letter_onboarding_status",1)->where("job_opening",$jobId)->where("backout_status",1)->get()->count();
		}
	}
	
	public static function getofferletterCompleted($jobId,$widgetId)
	{
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			return DocumentCollectionDetails::where("offer_letter_onboarding_status",2)->where("job_opening",$jobId)->where("backout_status",1)->whereIn("recruiter_name",$recruiterIdsArray)->get()->count();
		}
		else
		{
			return DocumentCollectionDetails::where("offer_letter_onboarding_status",2)->where("job_opening",$jobId)->where("backout_status",1)->get()->count();
		}
	}
	
	public static function getBVGPending($jobId,$widgetId)
	{
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			return DocumentCollectionDetails::where("bgverification_status",5)->where("job_opening",$jobId)->where("backout_status",1)->whereIn("recruiter_name",$recruiterIdsArray)->get()->count();
		}
		else
		{
			return DocumentCollectionDetails::where("bgverification_status",5)->where("job_opening",$jobId)->where("backout_status",1)->get()->count();
		}
	}
	
	public static function getMOLTyped($jobId,$widgetId)
	{
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			return DocumentCollectionDetails::where("mol_date","!=",NULL)->where("job_opening",$jobId)->where("onboard_status",1)->where("backout_status",1)->where("visa_process_status","!=",4)->whereIn("recruiter_name",$recruiterIdsArray)->get()->count();
		}
		else
		{
			return DocumentCollectionDetails::where("mol_date","!=",NULL)->where("job_opening",$jobId)->where("onboard_status",1)->where("backout_status",1)->where("visa_process_status","!=",4)->get()->count();
		}
	}
	
	public static function getEVisa($jobId,$widgetId)
	{
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			return DocumentCollectionDetails::where("evisa_status",1)->where("job_opening",$jobId)->where("onboard_status",1)->where("backout_status",1)->where("visa_process_status","!=",4)->whereIn("recruiter_name",$recruiterIdsArray)->get()->count();
		}
		else
		{
			return DocumentCollectionDetails::where("evisa_status",1)->where("job_opening",$jobId)->where("onboard_status",1)->where("backout_status",1)->where("visa_process_status","!=",4)->get()->count();
		}
	}
	
	public static function getOnboarded($jobId,$widgetId)
	{
		$currentDate = date("Y-m-d");
		$date30DaysBack = date("Y-m-d",strtotime("-30 days"));
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			return Employee_details::where("job_opening_id",$jobId)->whereBetween("doj", [$date30DaysBack, $currentDate])->whereIn("recruiter",$recruiterIdsArray)->get()->count();
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
