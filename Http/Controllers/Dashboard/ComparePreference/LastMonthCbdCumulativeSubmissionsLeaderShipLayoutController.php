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
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Employee\Employee_details;


class LastMonthCbdCumulativeSubmissionsLeaderShipLayoutController extends Controller
{
	
	public function searchCbdSubmissionsLastMonthLeaderShip(Request $request)
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
		return redirect('reloadmeCbdSubmissionsLastMonthLeaderShip/'.$widgetID); 
	}
	
	public function resetsearchCbdSubmissionsLastMonthLeaderShip(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFiltermolTeam['.$widgetID.']','');	
		$request->session()->put('widgetFilterprocessor['.$widgetID.']','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][from_salesTime]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][to_salesTime]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][job_opening]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][date_salesTime]','');
		$request->session()->put('widgetFiltermolDept['.$widgetID.']','');	
		return redirect('reloadmeCbdSubmissionsLastMonthLeaderShip/'.$widgetID);
	}
	
	
	public function reloadmeCbdSubmissionsLastMonthLeaderShip(Request $request)
	{
		 $wid = $request->wid;
		
		return view("components/ComparePreference/reloadcbdcumulativesubmissionslastmonthleadership",compact('wid'));
	}
	
	
	public function expandCbdSubmissionsLastMonthLeaderShip(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'Yes');
		return redirect('reloadmeCbdSubmissionsLastMonthLeaderShip/'.$wid);
	}
	
	public function compressCbdSubmissionsLastMonthLeaderShip(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'');
		return redirect('reloadmeCbdSubmissionsLastMonthLeaderShip/'.$wid);
	}
	public function PerformanceCbdCumulativeSubmissionsByDateLastMonthLeaderShip(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYDate['.$widgetID.']','ByDate');	
		$request->session()->put('widgetFilterBYProcessor['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYTeam['.$widgetID.']','');	
		//return redirect('widgetLoadOnDashboardHome/'.$widgetID);
		return redirect('reloadmeCbdSubmissionsLastMonthLeaderShip/'.$widgetID);
	}
	public function PerformanceCbdCumulativeSubmissionsByTeamLastMonthLeaderShipLeaderShip(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYDate['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYProcessor['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYTeam['.$widgetID.']','BYTeam');	
		//return redirect('widgetLoadOnDashboardHome/'.$widgetID);
		return redirect('reloadmeCbdSubmissionsLastMonthLeaderShip/'.$widgetID);
	}
	public function PerformanceCbdCumulativeSubmissionsByProcessorLastMonthLeaderShip(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYDate['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYProcessor['.$widgetID.']','ByProcessor');	
		$request->session()->put('widgetFilterBYTeam['.$widgetID.']','');	
		//return redirect('widgetLoadOnDashboardHome/'.$widgetID);
		return redirect('reloadmeCbdSubmissionsLastMonthLeaderShip/'.$widgetID);
	}
	public function PerformanceCbdCumulativeBookingLastMonthLeaderShip(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','last_month');	
		//return redirect('widgetLoadOnDashboardHome/'.$widgetID);
		return redirect('reloadmeCbdSubmissionsLastMonthLeaderShip/'.$widgetID);
	}
	public function datesearchCbdSubmissionsLastMonthLeaderShip(Request $request)
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
		return redirect('reloadmeCbdSubmissionsLastMonthLeaderShip/'.$widgetID);
	}
	public function PerformanceCbdIncomeBookingsLeaderShip(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYBookings['.$widgetID.']','Bookings');	
		$request->session()->put('widgetFilterBYSubmissions['.$widgetID.']','');	
		
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function PerformanceCbdIncomeSubmissionsLeaderShip(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYBookings['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYSubmissions['.$widgetID.']','Submissions');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	
	public function LastMonthCbdCumulativeSubmissionsLayoutByCMLeaderShip(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','current_month');
		$request->session()->put('widgetFilterHiring['.$widgetID.'][date_salesTime]','');		
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function LastMonthCbdCumulativeSubmissionsLayoutByLMLeaderShip(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$sessionfromDate = date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.date("Y",strtotime("-1 month ".date("Y-m-d")));
		
		$request->session()->put('widgetFilterHiring['.$widgetID.'][date_salesTime]',$sessionfromDate);
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','last_month');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function LastMonthCbdCumulativeSubmissionsLayoutBy3MLeaderShip(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$sessionfromDate = date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.date("Y",strtotime("-2 month ".date("Y-m-d")));
		
		$request->session()->put('widgetFilterHiring['.$widgetID.'][date_salesTime]',$sessionfromDate);
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','month_3');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function getTLdataforcbdbestcase(Request $request){
		$team = $request->team;
		$whereraw1 = '';
		$widgetID = $request->wid;
		$widgetId=$request->wid;
		$datatype=$request->session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				//$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-1 month'));
			//$fromDate = $m.'-'.'01';
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-3 month'));
			$fromDate = $m.'-'.'01';
			}
			else{
				if($request->session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]')!=''){
				$fromdateValue = $request->session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = $request->session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				}
				else{
				$dates =  $request->session()->get('widgetFilterHiring['.$widgetId.'][date_salesTime]');
			
				$dd="01-".$dates;
				$date=date("Y-m-d",strtotime($dd));

				 $fromDate = date("Y-m-d",strtotime($date)); //2023-01-01
				$toDate = date("Y-m-t",strtotime($date)); //2023-01-31	
				}
				
			}
			if($whereraw1 == '')
			{
				$whereraw1 = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw1 .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
			//$toDate = date("Y-m-d");
			//$fromDate = date("Y").'-'.date("m").'-'.'01';
			$fromDate= date('Y-m-d', strtotime('first day of last month'));


			$toDate= date('Y-m-d', strtotime('last day of last month'));	
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			if($whereraw1 == '')
			{
				$whereraw1 = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw1 .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			}
			
			
			if($request->session()->get('widgetFiltermolTeam['.$widgetId.']') != '' && $request->session()->get('widgetFiltermolTeam['.$widgetId.']') != NULL )
		{
			$deptIds =  $request->session()->get('widgetFiltermolTeam['.$widgetId.']');
			
			$cnameArray = explode(",",$deptIds);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
			
			if($whereraw1 == '')
			{
			$whereraw1 = 'team IN('.$finalcname.')';
			}
			else
			{
				$whereraw1 .= ' AND team IN('.$finalcname.')';
			}
		}
		
		
		
		
				
			$totalempdata= Employee_details::where('tl_id',$team)->where('dept_id',49)->where("offline_status",1)->where("job_function",2)->get();
		
		
		$finalemp=array();
			foreach($totalempdata as $emp)
			{
				$finalemp[]=$emp->emp_id;
			}
		//echo $whereraw;
		//print_r($finalemp);exit;
		
		
		$widgetgraphData= DepartmentFormEntry::whereIn("emp_id",$finalemp)->where("form_id",2)->whereRaw($whereraw1)->groupBy('emp_id')->get();
		
		//$widgetId=$wid;
		
			
		return view("components/ComparePreference/tablecbdbestcase",compact('team','widgetgraphData','widgetId'));
		
	}
	
}