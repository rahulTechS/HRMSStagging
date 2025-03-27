<?php

namespace App\Http\Controllers\Dashboard\MyTeam;

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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Models\Entry\Employee;



class CrossSellMyTeamLayoutController extends Controller
{
	
	public function searchCrossSell(Request $request)
	{
		$parametersInput = $request->input();
		//print_r($parametersInput);//exit;
		
		$widgetID = $parametersInput['widgetID'];


		
		
		
		
		
		
		
		if(isset($parametersInput['data_type']) && $parametersInput['data_type'] != '' && $parametersInput['data_type'] != NULL )
		{
			$data_type = $parametersInput['data_type'];
			
			$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]',$data_type);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','');	
		}
		


		if(isset($parametersInput['recruiterWarnLetter']) && $parametersInput['recruiterWarnLetter'] != '' && $parametersInput['recruiterWarnLetter'] != NULL )
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterWarnLetter]',$parametersInput['recruiterWarnLetter']);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterWarnLetter]','');	
		}

		if(isset($parametersInput['department']) && $parametersInput['department'] != '' && $parametersInput['department'] != NULL )
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][department]',$parametersInput['department']);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][department]','');	
		}

		if(isset($parametersInput['teamLeaders']) && $parametersInput['teamLeaders'] != '' && $parametersInput['teamLeaders'] != NULL )
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][teamLeaders]',$parametersInput['teamLeaders']);	
		}
		else
		{
			$request->session()->put('widgetFilterHiring['.$widgetID.'][teamLeaders]','');	
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
		return redirect('reloadCrossSellMyTeam/'.$widgetID);
	}
	
	public function resetCrossSell(Request $request)
	{
		$widgetID = $request->wid;
		$request->session()->put('widgetFiltermolTeam['.$widgetID.']','');	
		$request->session()->put('widgetFilterprocessor['.$widgetID.']','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][from_salesTime]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][to_salesTime]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][job_opening]','');	
		$request->session()->put('widgetFiltermolDept['.$widgetID.']','');
		$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterCat]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][recruiterWarnLetter]','');
		$request->session()->put('widgetFilterHiring['.$widgetID.'][department]','');
		$request->session()->put('widgetFilterHiring['.$widgetID.'][teamLeaders]','');			
		return redirect('reloadCrossSellMyTeam/'.$widgetID);
	}
	
	public function reloadCrossSell(Request $request)
	{
		 $wid = $request->wid;
		
		return view("components/MyTeam/reloadcrosssell",compact('wid'));
	}
	
	
	public function expandWarning(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'Yes');
		return redirect('reloadCrossSellMyTeam/'.$wid);
	}
	
	public function compressCrossSell(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'');
		return redirect('reloadCrossSellMyTeam/'.$wid);
	}
	
	
	
	public function EmployeeCrossSellMyTeamLayoutByCM(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','current_month');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function EmployeeCrossSellMyTeamLayoutByLM(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','last_month');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function EmployeeCrossSellMyTeamLayoutBy3M(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','month_3');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	
	
}