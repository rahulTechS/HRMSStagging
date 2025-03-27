<?php

namespace App\View\Components\Payout;

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
use App\Models\SEPayout\WorkTimeRange;
use App\Models\SEPayout\AgentPayoutMashreq;
class PayoutDataMashreq extends Component
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
		$data=WorkTimeRange::get();
		
		//echo "<pre>";
		//print_r($tlname);exit;
		
				
					$empdetails=AgentPayoutMashreq::distinct()->get(['tl_name']);
					
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
        return view('components.Payout.payoutdatamashreq');
    }
	
	
	
	public static function getAveragecardRev($tlname,$range_id)
	{
		
		$agentpayout=AgentPayoutMashreq::where("tl_name",$tlname)->where("range_id",$range_id)->get();
		$agentpayoutcount=AgentPayoutMashreq::where("tl_name",$tlname)->where("range_id",$range_id)->get()->count();
		$totalsum='';
		$cardrev=array();
		foreach($agentpayout as $_agentpayout){
			if($_agentpayout->card_rev==null){
			$cardrev[]=0;	
			}
			else{
			$cardrev[]=$_agentpayout->card_rev;
			}
			
		}
		//print_r(array_sum($cardrev));exit;
		if($agentpayoutcount==0){
			$agentpayoutcount=1;
		}
		$counttotalsum=array_sum($cardrev);
		 $Totaldatasum=round(($counttotalsum/$agentpayoutcount),2);
		return $Totaldatasum;
		//exit;
		
	}
	public static function getAverageExcess($tlname,$range_id)
	{
		
		$agentpayout=AgentPayoutMashreq::where("tl_name",$tlname)->where("range_id",$range_id)->get();
		$agentpayoutcount=AgentPayoutMashreq::where("tl_name",$tlname)->where("range_id",$range_id)->get()->count();
		$totalsumexcess=array();
		foreach($agentpayout as $_agentpayout){
			if($_agentpayout->excess==null){
			$totalsumexcess[]=0;	
			}
			else{
			$totalsumexcess[]=$_agentpayout->excess;
			}
			
		}
		if($agentpayoutcount==0){
			$agentpayoutcount=1;
		}
		$totaldata=array_sum($totalsumexcess);
		$Totaldatasumexcess=round(($totaldata/$agentpayoutcount),2);
		return $Totaldatasumexcess;
		
	}
	public static function getAveragecardRevGrandTotal($range_id)
	{
		$tldata=AgentPayoutMashreq::distinct()->get(['tl_name']);
		$tlnamearray=array();
		foreach($tldata as $_tlname){
		//$tlnamearray[]=	$_tlname->tl_name;
		$tlnamecount=AgentPayoutMashreq::where("range_id",$range_id)->where("tl_name",$_tlname->tl_name)->count();
		$agentpayout=AgentPayoutMashreq::where("range_id",$range_id)->where("tl_name",$_tlname->tl_name)->get();
		$totalrevarray=array();
		foreach($agentpayout as $_totalcard){
		if($_totalcard->card_rev==null){
			$totalrevarray[]=0;	
			}
			else{
			$totalrevarray[]=$_totalcard->card_rev;
			}	
		}
		if($tlnamecount==0){
			$tlnamecount=1;
		}
		//echo $tlnamecount;
		$counttotalsum=array_sum($totalrevarray);//exit;
		  $Totaldatasum =round(($counttotalsum/$tlnamecount),2);//exit;
		 $tlnamearray[]=$Totaldatasum;
		}
		
		return array_sum($tlnamearray);
		
	}
	public static function getAverageExcessGrandTotal($range_id)
	{
		$tldata=AgentPayoutMashreq::distinct()->get(['tl_name']);
		$tlnamearray=array();
		foreach($tldata as $_tlname){
		//$tlnamearray[]=	$_tlname->tl_name;
		$tlnamecount=AgentPayoutMashreq::where("range_id",$range_id)->where("tl_name",$_tlname->tl_name)->count();
		$agentpayout=AgentPayoutMashreq::where("range_id",$range_id)->where("tl_name",$_tlname->tl_name)->get();
		$totalrevarray=array();
		foreach($agentpayout as $_totalcard){
		if($_totalcard->excess==null){
			$totalrevarray[]=0;	
			}
			else{
			$totalrevarray[]=$_totalcard->excess;
			}	
		}
		if($tlnamecount==0){
			$tlnamecount=1;
		}
		//echo $tlnamecount;
		$counttotalsum=array_sum($totalrevarray);//exit;
		  $Totaldatasum =round(($counttotalsum/$tlnamecount),2);//exit;
		 $tlnamearray[]=$Totaldatasum;
		}
		
		return array_sum($tlnamearray);
		
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
