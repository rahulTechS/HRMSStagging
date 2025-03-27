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


class HiringOnboardingLayoutController extends Controller
{
  

	
	public function loadRecruiterInPanel(Request $request)
	{
		$catID =  $request->catID;
		$wid =  $request->wid;
		$recruiterModel = RecruiterDetails::where("recruit_cat",$catID)->where("status",1)->get();
		return view("components/Hiring/loadRecruiterInPanel",compact('wid','recruiterModel'));
	}
	
	public function searchHiringOnboarding(Request $request)
	{
		$parametersInput = $request->input();
		//print_r($parametersInput);//exit;
		$widgetID = $parametersInput['widgetID'];
		$recruiterCat = $parametersInput['recruiterCat'];
		$department = isset($parametersInput['department']);
		if(isset($parametersInput['department']) && $parametersInput['department'] != '' && $parametersInput['department'] != NULL )
		{
			if(isset($parametersInput['department'])!=''){
			$departmentData = implode(",",$parametersInput['department']);
			}
			else{
				$departmentData ='';
			}
			$request->session()->put('widgetFilterHiringDept['.$widgetID.']',$departmentData);	
		}
		else
		{
			$request->session()->put('widgetFilterHiringDept['.$widgetID.']','');	
		}
		
		if(isset($parametersInput['recruiterId']) && $parametersInput['recruiterId'] != '' && $parametersInput['recruiterId'] != NULL )
		{
			$recruiterIdData = implode(",",$parametersInput['recruiterId']);
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterId]',$recruiterIdData);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterId]','');	
		}
		
		if(isset($parametersInput['recruiterCat']) && $parametersInput['recruiterCat'] != '' && $parametersInput['recruiterCat'] != NULL )
		{
			
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterCat]',$recruiterCat);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterCat]','');	
		}
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function resetHiringOnboarding(Request $request)
	{
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterCat]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterId]','');
		$request->session()->put('widgetFilterHiringDept['.$widgetID.']','');		
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function reloadmeHiring(Request $request)
	{
		$wid = $request->wid;
		return view("components/Hiring/reloadmeHiring",compact('wid'));
	}
	
	public function candidateCotactedLink(Request $request)
	{
		$jobid = $request->jobid;
		$wid = $request->wid;
		$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
		$currentDate = date("Y-m-d");
		$date30DaysBack = date("Y").'-'.date("m").'-'.'01';
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
		//echo "hello";exit;
		$jobid = $request->jobid;
		$wid = $request->wid;
		$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
		$currentDate = date("Y-m-d");
		$date30DaysBack = date("Y").'-'.date("m").'-'.'01';
		$request->session()->put('datefrom_candAll_filter_inner_list',$date30DaysBack);
		$request->session()->put('dateto_candAll_filter_inner_list',$currentDate);
		$request->session()->put('opening_cand_filter_inner_list',$jobid);
		$request->session()->put('company_RecruiterName_filter_inner_list',$recu);
		
		return redirect('documentcollectionAjax');
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
		$date30DaysBack = date("Y").'-'.date("m").'-'.'01';
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
		$currentDate = date("Y-m-d");
		$date30DaysBack = date("Y").'-'.date("m").'-'.'01';
		$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
		
	
		$request->session()->put('opening_cand_filter_inner_list',$jobid);
		$request->session()->put('company_RecruiterName_filter_inner_list',$recu);
		$request->session()->put('filtercompleteofferletterbg_filter_inner_list','');
		$request->session()->put('tabOpenByWidget',"visa");
		$request->session()->put('subtabOpenByWidget',"step1");
		$request->session()->put('datefrom_visainprocessallstage1_moldate_filter_inner_list',$date30DaysBack);
		$request->session()->put('dateto_visainprocessallstage1_moldate_filter_inner_list',$currentDate);
		return redirect('documentcollectionAjax');
	}
	
	
	public function evisaLink(Request $request)
	{
		$jobid = $request->jobid;
		$wid = $request->wid;
		$currentDate = date("Y-m-d");
		$date30DaysBack = date("Y").'-'.date("m").'-'.'01';
		$recu = $request->session()->get('widgetFilterHiring['.$wid.'][recruiterId]');	
		
	
		$request->session()->put('opening_cand_filter_inner_list',$jobid);
		$request->session()->put('company_RecruiterName_filter_inner_list',$recu);
		$request->session()->put('filtercompleteofferletterbg_filter_inner_list','');
		$request->session()->put('tabOpenByWidget',"visa");
		$request->session()->put('subtabOpenByWidget',"step2");
		$request->session()->put('datefrom_visainprocessallstage2_evisa_filter_inner_list',$date30DaysBack);
		$request->session()->put('dateto_visainprocessallstage2_evisa_filter_inner_list',$currentDate);
		return redirect('documentcollectionAjax');
	}
	
}