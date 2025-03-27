<?php

namespace App\Http\Controllers\Dashboard\ComparePreference;

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


class LastMonthMashreqCumulativeSubmissionsLayoutController extends Controller
{
	
	public function searchMashreqSubmissionsLastMonth(Request $request)
	{
		$parametersInput = $request->input();
		//print_r($parametersInput);exit;
		
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
		return redirect('reloadmePerformanceMashreqCumulativeSubmissionsHome/'.$widgetID); 
	}
	
	public function resetsearchMashreqSubmissionsLastMonth(Request $request)
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
		return redirect('reloadmeMashreqSubmissionsLastMonth/'.$widgetID);
	}
	
	
	public function reloadmeMashreqSubmissionsLastMonth(Request $request)
	{
		 $wid = $request->wid;
		
		return view("components/ComparePreference/reloadmashreqcumulativesubmissionslastmonth",compact('wid'));
	}
	
	
	public function expandMashreqSubmissionsLastMonth(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'Yes');
		return redirect('reloadmeMashreqSubmissionsLastMonth/'.$wid);
	}
	
	public function compressMashreqSubmissionsLastMonth(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'');
		return redirect('reloadmeMashreqSubmissionsLastMonth/'.$wid);
	}
	public function PerformanceMashreqCumulativeSubmissionsByDateLastMonth(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYDate['.$widgetID.']','ByDate');	
		$request->session()->put('widgetFilterBYProcessor['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYTeam['.$widgetID.']','');	
		//return redirect('widgetLoadOnDashboardHome/'.$widgetID);
		return redirect('reloadmeMashreqSubmissionsLastMonth/'.$widgetID);
	}
	public function PerformanceMashreqCumulativeSubmissionsByTeamLastMonth(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYDate['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYProcessor['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYTeam['.$widgetID.']','BYTeam');	
		//return redirect('widgetLoadOnDashboardHome/'.$widgetID);
		return redirect('reloadmeMashreqSubmissionsLastMonth/'.$widgetID);
	}
	public function PerformanceMashreqCumulativeSubmissionsByProcessorLastMonth(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYDate['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYProcessor['.$widgetID.']','ByProcessor');	
		$request->session()->put('widgetFilterBYTeam['.$widgetID.']','');	
		//return redirect('widgetLoadOnDashboardHome/'.$widgetID);
		return redirect('reloadmeMashreqSubmissionsLastMonth/'.$widgetID);
	}
	public function PerformanceMashreqCumulativeBookingLastMonth(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','last_month');	
		//return redirect('widgetLoadOnDashboardHome/'.$widgetID);
		return redirect('reloadmeMashreqSubmissionsLastMonth/'.$widgetID);
	}
	public function datesearchMashreqSubmissionsLastMonth(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$salestime = $request->salestime;
		if($salestime!=''){
		$request->session()->put('widgetFilterHiring['.$widgetID.'][date_salesTime]',$salestime);	
		}else{
		$request->session()->put('widgetFilterHiring['.$widgetID.'][date_salesTime]','');	
		}
		//return redirect('widgetLoadOnDashboardHome/'.$widgetID);
		return redirect('reloadmeMashreqSubmissionsLastMonth/'.$widgetID);
	}
	public function PerformanceMashreqIncomeBookings(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYBookings['.$widgetID.']','Bookings');	
		$request->session()->put('widgetFilterBYSubmissions['.$widgetID.']','');	
		
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function PerformanceMashreqIncomeSubmissions(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYBookings['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYSubmissions['.$widgetID.']','Submissions');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
}