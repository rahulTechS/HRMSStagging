<?php

namespace App\Http\Controllers\Dashboard\Home;

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
use App\Models\DocumentCollectionVisaDetailsProcess\DocumentCollectionVisaDetailsProcess;



class VisaDetailProcessLayoutController extends Controller
{
	
	public function searchVisaDetailProcess(Request $request)
	{
		$parametersInput = $request->input();
		//print_r($parametersInput);//exit;
		
		$widgetID = $parametersInput['widgetID'];
		
		
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
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function resetsearchVisaDetailProcess(Request $request)
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
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function reloadmeVisaDetailProcess(Request $request)
	{
		 $wid = $request->wid;
		
		//return view("components/SoftwareAdoption/reloadmesalestime",compact('wid'));
	return view("components/Home/reloadmevisadetailprocess",compact('wid'));

	}
	
	
	public function expandVisaDetailProcess(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'Yes');
		return redirect('reloadmeVisaDetailProcess/'.$wid);
	}
	
	public function compressVisaDetailProcess(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'');
		return redirect('reloadmeVisaDetailProcess/'.$wid);
	}
	public function VisaDetailProcessBySM(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYSM['.$widgetID.']','SM');	
		$request->session()->put('widgetFilterBYSP['.$widgetID.']','');	
			
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function  VisaDetailProcessBySP(Request $request)
	{
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYSM['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYSP['.$widgetID.']','SP');
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}



	public function searchVisaDetailProcessCM(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','current_month');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][date_salesTime]','');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function searchVisaDetailProcessLM(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
	$sessionfromDate = date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.date("Y",strtotime("-1 month ".date("Y-m-d")));
		
		$request->session()->put('widgetFilterHiring['.$widgetID.'][date_salesTime]',$sessionfromDate);
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','last_month');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function searchVisaDetailProcess3M(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','month_3');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
		
		
	}


	
	
}