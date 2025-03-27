<?php

namespace App\Http\Controllers\Dashboard\DashboardLayout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;

use App\Models\Dashboard\WidgetCreation;

use App\Models\Dashboard\Widgetlayouts\WidgetOnboardingHiring;
use App\Models\Job\JobOpening;
use App\Models\Company\Department;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\SEPayout\WorkTimeRange;
use App\Models\SEPayout\AgentPayoutMashreq;

class PayoutLayoutController extends Controller
{
 
	
	
	
	
	
	public function searchShortlistedMashrwq(Request $request)
	{
		$parametersInput = $request->input();
		
		$widgetID = $parametersInput['widgetID'];
		
		if(isset($parametersInput['from_salesTime']) && $parametersInput['from_salesTime'] != '' && $parametersInput['from_salesTime'] != NULL )
		{
			$from_salesTime = $parametersInput['from_salesTime'];
			
			$request->session()->put('widgetFilterhca['.$widgetID.'][from_salesTime]',$from_salesTime);	
		}
		else
		{
			$request->session()->put('widgetFilterhca['.$widgetID.'][from_salesTime]','');	
		}
		
		
		if(isset($parametersInput['to_salesTime']) && $parametersInput['to_salesTime'] != '' && $parametersInput['to_salesTime'] != NULL )
		{
			$to_salesTime = $parametersInput['to_salesTime'];
			$request->session()->put('widgetFilterhca['.$widgetID.'][to_salesTime]',$to_salesTime);	
		}
		else
		{
			$request->session()->put('widgetFilterhca['.$widgetID.'][to_salesTime]','');	
		}
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function resetShortlistedMashrwq(Request $request)
	{
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterhca['.$widgetID.'][recruiterCat]','');	
		$request->session()->put('widgetFilterhca['.$widgetID.'][recruiterId]','');	
		$request->session()->put('widgetFilterhca['.$widgetID.'][data_type]','');	
		$request->session()->put('widgetFilterhca['.$widgetID.'][from_salesTime]','');
		$request->session()->put('widgetFilterhca['.$widgetID.'][to_salesTime]','');	
		$request->session()->put('widgetFilterhca['.$widgetID.'][shortlist_by]','');	
		$request->session()->put('widgetFilterhca['.$widgetID.'][job_opening]','');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function payoutmashreqTabledataLoad(Request $request)
	{
		$widgetId = $request->widgetId;
		$rangeId = $request->rangeId;
		$empdetails=AgentPayoutMashreq::distinct()->get(['tl_name']);
		if(isset($rangeId) && $rangeId != '' && $rangeId != NULL )
		{
			$rangeid = $rangeId;
			
			$request->session()->put('widgetpayoutmashreq['.$widgetId.']',$rangeid);	
		}
		else
		{
			$request->session()->put('widgetpayoutmashreq['.$widgetId.']','');	
		}
		return view("components/Payout/payoutTable",compact('widgetId','rangeId','empdetails'));
	}
	
	public function candidateCotactedLink(Request $request)
	{
		$jobid = $request->jobid;
		$wid = $request->wid;
		$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
		$currentDate = date("Y-m-d");
		$date30DaysBack = date("Y-m-d",strtotime("-30 days"));
		$request->session()->put('datefrom_filter_inner_list',$date30DaysBack);
		$request->session()->put('dateto_filter_inner_list',$currentDate);
		$request->session()->put('interview_jobopning_filter_inner_list',$jobid);
		$request->session()->put('interview_recruiter_filter_inner_list',$recu);
			$request->session()->put('interview_currentinterview_filter_inner_list','');
		$request->session()->put('interview_currentstatus_filter_inner_list','');
		return redirect('InterviewProcess');
	}
	
	public function candidatefinalLink(Request $request)
	{
		$jobid = $request->jobid;
		$wid = $request->wid;
		$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
		$currentDate = date("Y-m-d");
		$date30DaysBack = date("Y-m-d",strtotime("-30 days"));
		$request->session()->put('datefrom_filter_inner_list',$date30DaysBack);
		$request->session()->put('dateto_filter_inner_list',$currentDate);
		$request->session()->put('interview_jobopning_filter_inner_list',$jobid);
		$request->session()->put('interview_recruiter_filter_inner_list',$recu);
		$request->session()->put('interview_currentinterview_filter_inner_list','final discussion');
		$request->session()->put('interview_currentstatus_filter_inner_list',2);
		return redirect('InterviewProcess');
	}
	
	public function offerletterpendingLink(Request $request)
	{
		$jobid = $request->jobid;
		$wid = $request->wid;
		$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
		
	
		$request->session()->put('opening_cand_filter_inner_list',$jobid);
		$request->session()->put('company_RecruiterName_filter_inner_list',$recu);
		$request->session()->put('tabOpenByWidget',"OfferletterPending");
		return redirect('documentcollectionAjax');
	}
	
	public function offerlettercompletedLink(Request $request)
	{
		$jobid = $request->jobid;
		$wid = $request->wid;
		$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
		
	
		$request->session()->put('opening_cand_filter_inner_list',$jobid);
		$request->session()->put('company_RecruiterName_filter_inner_list',$recu);
		$request->session()->put('tabOpenByWidget',"OfferletterComplete");
		
		return redirect('documentcollectionAjax');
	}
	
	
	public function onboardedLink(Request $request)
	{
		$currentDate = date("Y-m-d");
		$date30DaysBack = date("Y-m-d",strtotime("-30 days"));
		$jobid = $request->jobid;
		$wid = $request->wid;
		$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
		
	
		$request->session()->put('opening_cand_filter_inner_list',$jobid);
		$request->session()->put('company_RecruiterName_filter_inner_list',$recu);
		$request->session()->put('datefrom_candonboard_filter_inner_list',$date30DaysBack);
		$request->session()->put('dateto_candonboard_filter_inner_list',$currentDate);
		$request->session()->put('tabOpenByWidget',"onboard");
		
		return redirect('documentcollectionAjax');
	}
	
	
	
	public function backoutLink(Request $request)
	{
		$jobid = $request->jobid;
		$wid = $request->wid;
		$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
		
	
		$request->session()->put('opening_cand_filter_inner_list',$jobid);
		$request->session()->put('company_RecruiterName_filter_inner_list',$recu);
		$request->session()->put('tabOpenByWidget',"backout");
		return redirect('documentcollectionAjax');
	}
	
	public function BGVLink(Request $request)
	{
		$jobid = $request->jobid;
		$wid = $request->wid;
		$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
		
	
		$request->session()->put('opening_cand_filter_inner_list',$jobid);
		$request->session()->put('company_RecruiterName_filter_inner_list',$recu);
		$request->session()->put('filtercompleteofferletterbg_filter_inner_list','5,4');
		$request->session()->put('tabOpenByWidget',"OfferletterPending");
		return redirect('documentcollectionAjax');
	}
	
	
	public function molTypedLink(Request $request)
	{
		$jobid = $request->jobid;
		$wid = $request->wid;
		$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
		
	
		$request->session()->put('opening_cand_filter_inner_list',$jobid);
		$request->session()->put('company_RecruiterName_filter_inner_list',$recu);
		$request->session()->put('filtercompleteofferletterbg_filter_inner_list','');
		$request->session()->put('tabOpenByWidget',"visa");
		$request->session()->put('subtabOpenByWidget',"step1");
		return redirect('documentcollectionAjax');
	}
	
	
	public function evisaLink(Request $request)
	{
		$jobid = $request->jobid;
		$wid = $request->wid;
		$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
		
	
		$request->session()->put('opening_cand_filter_inner_list',$jobid);
		$request->session()->put('company_RecruiterName_filter_inner_list',$recu);
		$request->session()->put('filtercompleteofferletterbg_filter_inner_list','');
		$request->session()->put('tabOpenByWidget',"visa");
		$request->session()->put('subtabOpenByWidget',"step2");
		return redirect('documentcollectionAjax');
	}
	
	public function resetShortlistedMashrwqListData(Request $request){
		$empId=$request->empId;
		$widgetId=$request->widgetId;
		$rowId=$request->rowId;
		$name=$request->name;
		if($request->session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != '' && $request->session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]') != NULL)	
		{
			$date30DaysBack = $request->session()->get('widgetFilterhca['.$widgetId.'][from_salesTime]');
		}		
		else{
		
		$date30DaysBack = date("Y-m-d",strtotime("-30 days"));
		}
		if($request->session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != '' && $request->session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]') != NULL)	
		{
			$currentDate = $request->session()->get('widgetFilterhca['.$widgetId.'][to_salesTime]');
		}
		else{
			$currentDate = date("Y-m-d");
		}
		
		$totel=DepartmentFormEntry::whereBetween("submission_date", [$date30DaysBack, $currentDate])->where("emp_id",$empId)->where("form_id",1)->get()->count();
		$wip=DepartmentFormEntry::whereBetween("submission_date", [$date30DaysBack, $currentDate])->where("emp_id",$empId)->where("form_id",1)->where("form_status","WIP")->get()->count();
		$booked=DepartmentFormEntry::whereBetween("submission_date", [$date30DaysBack, $currentDate])->where("emp_id",$empId)->where("form_id",1)->where("form_status",'like',"%booked%")->get()->count();
		$decliend=DepartmentFormEntry::whereBetween("submission_date", [$date30DaysBack, $currentDate])->where("emp_id",$empId)->where("form_id",1)->where("form_status","declined")->get()->count();
		$terminated=DepartmentFormEntry::whereBetween("submission_date", [$date30DaysBack, $currentDate])->where("emp_id",$empId)->where("form_id",1)->where("form_status","terminated")->get()->count();
		return view("components/Hcanalytics/tabledatamashreq",compact('name','totel','wip','booked','decliend','terminated'));
	}
}