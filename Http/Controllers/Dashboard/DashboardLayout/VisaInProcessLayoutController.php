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


class VisaInProcessLayoutController extends Controller
{
	
	public function searchVisaInProcess(Request $request)
	{
		$parametersInput = $request->input();
		//print_r($parametersInput);//exit;
		$recruiterCat = $parametersInput['recruiterCat'];
		$widgetID = $parametersInput['widgetID'];
		
		if(isset($parametersInput['onboarding_status']) && $parametersInput['onboarding_status'] != '' && $parametersInput['onboarding_status'] != NULL )
		{
			if(isset($parametersInput['onboarding_status'])!=''){
			$onboarding_status = $parametersInput['onboarding_status'];
			}
			else{
				$onboarding_status ='';
			}
			$request->session()->put('onboard_status_filter_inner_list['.$widgetID.']',$onboarding_status);	
		}
		else
		{
			$request->session()->put('onboard_status_filter_inner_list['.$widgetID.']','');	
		}
		
		if(isset($parametersInput['team']) && $parametersInput['team'] != '' && $parametersInput['team'] != NULL )
		{
			if(isset($parametersInput['team'])!=''){
			$team = implode(",",$parametersInput['team']);
			}
			else{
				$team ='';
			}
			$request->session()->put('widgetFiltermolTeam['.$widgetID.']',$team);	
		}
		else
		{
			$request->session()->put('widgetFiltermolTeam['.$widgetID.']','');	
		}
		if(isset($parametersInput['processor']) && $parametersInput['processor'] != '' && $parametersInput['processor'] != NULL )
		{
			$processor = implode(",",$parametersInput['processor']);
			$request->session()->put('widgetFilterprocessor['.$widgetID.']',$processor);	
		}
		else
		{
			$request->session()->put('widgetFilterprocessor['.$widgetID.']','');	
		}
		if(isset($parametersInput['recruiterCat']) && $parametersInput['recruiterCat'] != '' && $parametersInput['recruiterCat'] != NULL )
		{
			
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterCat]',$recruiterCat);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterCat]','');	
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
		
		if(isset($parametersInput['data_type']) && $parametersInput['data_type'] != '' && $parametersInput['data_type'] != NULL )
		{
			$data_type = $parametersInput['data_type'];
			$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]',$data_type);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','');	
		}
		
		
		if(isset($parametersInput['from_salesTime']) && $parametersInput['from_salesTime'] != '' && $parametersInput['from_salesTime'] != NULL )
		{
			$from_salesTime = $parametersInput['from_salesTime'];
			
			$request->session()->put('widgetFilterHiring['.$widgetID.'][from_salesTime]',$from_salesTime);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][from_salesTime]','');	
		}
		
		
		if(isset($parametersInput['to_salesTime']) && $parametersInput['to_salesTime'] != '' && $parametersInput['to_salesTime'] != NULL )
		{
			$to_salesTime = $parametersInput['to_salesTime'];
			$request->session()->put('widgetFilterHiring['.$widgetID.'][to_salesTime]',$to_salesTime);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][to_salesTime]','');	
		}
		//echo "hello";exit;
		return redirect('reloadmeVisaDocsReceived/'.$widgetID);
	}
	
	public function resetsearchVisaInProcess(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFiltermolTeam['.$widgetID.']','');	
		$request->session()->put('widgetFilterprocessor['.$widgetID.']','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][from_salesTime]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][to_salesTime]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][job_opening]','');	
		$request->session()->put('widgetFiltermolDept['.$widgetID.']','');
		$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterCat]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterId]','');
		$request->session()->put('onboard_status_filter_inner_list['.$widgetID.']','');
		
		return redirect('reloadmeVisaDocsReceived/'.$widgetID);
	}
	
	public function reloadmeVisaInProcess(Request $request)
	{
		 $wid = $request->wid;
		
		return view("components/OnboardingReport/reloadmevisainprocess",compact('wid'));
	}
	
	
	public function expandVisaInProcess(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'Yes');
		return redirect('reloadmeVisaInProcess/'.$wid);
	}
	
	public function compressVisaInProcess(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'');
		return redirect('reloadmeVisaInProcess/'.$wid);
	}
	
	public function VisaInProcessLastMonth(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','last_month');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function candidateVisaInProcessLink(Request $request)
	{
		//echo "hello";exit;
		$dept = $request->dept;
		$wid = $request->wid;
		$request->session()->put('departmentId_visainprocessall_filter_inner_list',$dept);
		//$request->session()->put('filtervisa_documents_status_filter_inner_list',2);
		$request->session()->put('tabOpenByWidget',"visa");
		$request->session()->put('subtabOpenByWidget',"VisaInProcess");		
		return redirect('documentcollectionAjax');
	}
	public function candidateVisaInProcessTotelLink(Request $request)
	{
		//echo "hello";exit;
		
		$wid = $request->wid;
		//$request->session()->put('filtervisa_documents_status_filter_inner_list',2);
		$request->session()->put('tabOpenByWidget',"visa");
		$request->session()->put('subtabOpenByWidget',"VisaInProcess");		
		return redirect('documentcollectionAjax');
	}
	public function VisaInProcessAll(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYAll['.$widgetID.']','All');	
		$request->session()->put('widgetFilterBYS1['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYS2['.$widgetID.']','');
		$request->session()->put('widgetFilterBYIncomplete['.$widgetID.']','');		
		return redirect('reloadmeVisaInProcess/'.$widgetID);
	}
	public function VisaInProcessS1(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYAll['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYS1['.$widgetID.']','S1');	
		$request->session()->put('widgetFilterBYS2['.$widgetID.']','');
		$request->session()->put('widgetFilterBYIncomplete['.$widgetID.']','Incomplete');		
		return redirect('reloadmeVisaInProcess/'.$widgetID);
	}
	public function VisaInProcessS2(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYAll['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYS1['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYS2['.$widgetID.']','S2');
		$request->session()->put('widgetFilterBYIncomplete['.$widgetID.']','Incomplete');		
		return redirect('reloadmeVisaInProcess/'.$widgetID);
	}
	public function VisaInProcessIncomplete(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYAll['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYS1['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYS2['.$widgetID.']','');
		$request->session()->put('widgetFilterBYIncomplete['.$widgetID.']','Incomplete');		
		return redirect('reloadmeVisaInProcess/'.$widgetID);
	}
	public function candidateVisaInProcessFlightTKT(Request $request)
	{
		//echo "hello";exit;
		$dept = $request->dept;
		$wid = $request->wid;
		$request->session()->put('departmentId_visainprocessall_filter_inner_list',$dept);
		$request->session()->put('flighttkt_list_visainprocessall',1);
		$request->session()->put('tabOpenByWidget',"visa");
		$request->session()->put('subtabOpenByWidget',"VisaInProcess");		
		return redirect('documentcollectionAjax');
	}
	public function candidateVisaInProcessTotelTKT(Request $request)
	{
		//echo "hello";exit;
		
		$wid = $request->wid;
		//$request->session()->put('filtervisa_documents_status_filter_inner_list',2);
		$request->session()->put('tabOpenByWidget',"visa");
		$request->session()->put('subtabOpenByWidget',"VisaInProcess");	
		$request->session()->put('flighttkt_list_visainprocessall',1);		
		return redirect('documentcollectionAjax');
	}
	
}