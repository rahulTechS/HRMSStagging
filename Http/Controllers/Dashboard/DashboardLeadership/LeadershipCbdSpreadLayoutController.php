<?php

namespace App\Http\Controllers\Dashboard\DashboardLeadership;

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
use App\Models\Employee\Employee_attribute;

use App\Models\Employee\Employee_details;
use App\Models\Common\MashreqLoginMIS;
use App\Models\Common\MashreqBankMIS;
use App\Models\Common\MashreqBookingMIS;
use App\Models\Common\MashreqMTDMIS;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Dashboard\MasterPayout;
use App\Models\Dashboard\MasterPayoutPre;


class LeadershipCbdSpreadLayoutController extends Controller
{
	
	public function searchLeadershipCbdSpreadWid(Request $request)
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
		
		
		if(isset($parametersInput['range']) && $parametersInput['range'] != '' && $parametersInput['range'] != NULL )
		{
			
			//$widgetIDrang=$parametersInput['widgetIDrange'];
			if(isset($parametersInput['range'])!=''){
			$team = implode(",",$parametersInput['range']);
			}
			else{
				$team ='';
			}
			$request->session()->put('widgetFiltermolRange['.$widgetID.']',$team);	
		}
		else
		{
			$request->session()->put('widgetFiltermolRange['.$widgetID.']','');	
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
	
	
		
	
	public function resetsearchLeadershipCbdSpread(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFiltermolTeam['.$widgetID.']','');	
		$request->session()->put('widgetFiltermolRange['.$widgetID.']','');	
		$request->session()->put('widgetFilterprocessor['.$widgetID.']','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][from_salesTime]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][to_salesTime]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][job_opening]','');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][date_salesTime]','');
		$request->session()->put('widgetFiltermolDept['.$widgetID.']','');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function reloadmeLeadershipCbdSpread(Request $request)
	{
		 $wid = $request->wid;
		//echo $wid;exit;
		return view("components/DashboardLeadership/reloadmeLeadershipcbdspread",compact('wid'));
	}
	
	
	public function expandLeadershipCbdSpread(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'Yes');
		return redirect('reloadmeLeadershipCbdSpread/'.$wid);
	}
	
	public function compressLeadershipCbdSpread(Request $request)
	{
		$wid = $request->wid;
		$request->session()->put('open_section_status_'.$wid,'');
		return redirect('reloadmeLeadershipCbdSpread/'.$wid);
	}
	
	
	
	public function datesearchLeadershipCbdSpread(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$salestime = $request->salestime;
		if($salestime!=''){
		$request->session()->put('widgetFilterHiring['.$widgetID.'][date_salesTime]',$salestime);	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]',"custom");
		}else{
		$request->session()->put('widgetFilterHiring['.$widgetID.'][date_salesTime]','');	
		}
		//return redirect('widgetLoadOnDashboardHome/'.$widgetID);
		return redirect('reloadmeLeadershipCbdSpread/'.$widgetID);
	}
	
	
	
	public function LeadershipCbdspreadByProcessor(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYProcessorNew['.$widgetID.']','Processor');	
		$request->session()->put('widgetFilterBYTable['.$widgetID.']','');	
			
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function LeadershipCbdByTable(Request $request)
	{
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterBYProcessorNew['.$widgetID.']','');	
		$request->session()->put('widgetFilterBYTable['.$widgetID.']','Table');
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function LeadershipCbdspreadLastMonth(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','last_month');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	public function SearchLeadershipCbdspreadCM(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','current_month');	
		$request->session()->put('widgetFilterHiring['.$widgetID.'][date_salesTime]','');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function SearchLeadershipCbdspreadLM(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
	$sessionfromDate = date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.date("Y",strtotime("-1 month ".date("Y-m-d")));
		
		$request->session()->put('widgetFilterHiring['.$widgetID.'][date_salesTime]',$sessionfromDate);
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','last_month');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
	}
	
	public function SearchLeadershipCbdspread3M(Request $request)
	{
		//echo "hello";exit;
		$widgetID = $request->wid;
		$request->session()->put('widgetFilterHiring['.$widgetID.'][data_type]','month_3');	
		return redirect('widgetLoadOnDashboard/'.$widgetID);
		
		
	}
	
	
	
	
	
	   public static function getCurrentmonth($cardcount,$range,$salestime)
	   {
		
		$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';

		 $empid=$cardcount;
		
		//$salestimedata=$request->salestime;
		$range=$range;
//echo $salestime;exit;
		if($salestime!='' && $salestime!='1-1970' )
		{
			$datatype=$salestime;
		}
		else
		{
			$datatype='';
		}

		//return $datatype;


		//return $range;
		if($range!=0){
		if($range==1)
		{
			$arryRange=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		if($range==2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		if($range==3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		if($range==4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		if($range==5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}

		
		if($whereraw == '')
			{
				$whereraw = 'range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whereraw .= ' And range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
			}
			if($whererawsales == '')
			{
				$whererawsales = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whererawsales .= 'And range_id IN ('.$finalSales.')';
				
			}
		}
		


//return $datatype;

		if($datatype == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			
		}
		elseif($datatype == 'last_month')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-30 days"));
			$fromDate = date("Y-m-01",strtotime("-1 Months")); 
		}
		elseif($datatype == 'month_3')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			$fromDate = date("Y-m-01",strtotime("-2 Months")); 
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$date='01-'.$datatype;
			$salestime=date("n-Y", strtotime($date));
		}

		//$salestime=date("n-Y", strtotime($fromDate));
		//$salestime=$datatype;
		if($whereraw == '')
			{
				$whereraw = "approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			else
			{
				$whereraw .= " And approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
			
			if($datatype == 'current_month' || $datatype == ''){
				//echo "hello";
				//echo $whereraw ;exit;
				if($whereraw != '')
			{
			return	$totalemp=DepartmentFormEntry::where("emp_id",$empid)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw)->get()->count();
			
			}
			else{
				return	$totalemp=DepartmentFormEntry::where("emp_id",$empid)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->get()->count();
			
			}
			}
			else{
				if($whererawsales != '')
				{
				
				return $totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("agent_id",$empid)->sum('tc');
				}else{
				return $totalmastercard=MasterPayoutPre::where("agent_id",$empid)->sum('tc');
				}
			}			
				//print_r($totalemp);

				
	
		
	}
	
	
	
	
	public static function getLastmonth($cardcount,$range,$salestime)
	   {
		
		$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';

		$empid=$cardcount;
		
		//$salestimedata=$request->salestime;
		$range=$range;

			if($salestime!='' && $salestime!='1-1970' )
		{
			$datatype=$salestime;
		}
		else
		{
			$datatype='';
		}

		//return $datatype;


		//return $range;
		if($range!=0){
		if($range==1)
		{
			$arryRange=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		if($range==2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		if($range==3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		if($range==4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		if($range==5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}

		
		if($whereraw == '')
			{
				$whereraw = 'range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whereraw .= ' And range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
			}
			if($whererawsales == '')
			{
				$whererawsales = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whererawsales .= 'And range_id IN ('.$finalSales.')';
				
			}
		}
		


//return $datatype;

		if($datatype == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			
		}
		elseif($datatype == 'last_month')
		{
			$fromDate = date("Y-m-01",strtotime("-1 Months"));
			$toDate = date("Y-m-d",strtotime("last day of last month"));
		}
		elseif($datatype == 'month_3')
		{
			
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			$fromDate = date("Y-m-01",strtotime("-2 Months")); 
			$toDate = date("Y-m-t", strtotime($fromDatelast));
		}
		else
		{
			$fromDate = date("Y-m-01",strtotime("-1 Months"));
			$toDate = date("Y-m-d",strtotime("last day of last month"));
			$date='01-'.$datatype;
			$fromsales = date('Y-m-d', strtotime(" -1 month",strtotime($date)));
			$salestime=date("n-Y", strtotime($fromsales));
		}

		//$salestime=date("n-Y", strtotime($fromDate));
		//$salestime=$datatype;
		if($whereraw == '')
			{
				$whereraw = "approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			else
			{
				$whereraw .= " And approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
			//echo $whererawsales;exit;
			if($datatype == 'current_month' || $datatype == ''){
				if($whereraw != '')
			{
			return	$totalemp=DepartmentFormEntry::where("emp_id",$empid)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw)->get()->count();
			
			}
			else{
				return	$totalemp=DepartmentFormEntry::where("emp_id",$empid)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->get()->count();
			
			}
			}
			else{
				if($whererawsales != '')
				{
				
				return $totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("agent_id",$empid)->sum('tc');
				}else{
				return $totalmastercard=MasterPayoutPre::where("agent_id",$empid)->sum('tc');
				}
			}
				
		
	}
	
	
	
	
	
	
	public function getSpreadEmployeeCbdCountDetailszeroData(Request $request)
	{
		$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';

		$team=$request->team;
		$widget=$request->widget;
		//$salestimedata=$request->salestime;
		$range=$request->range;

		if($request->salestime)
		{
			$datatype=$request->salestime;
		}
		else
		{
			$datatype='';
		}

		//return $datatype;


		//return $range;
		if($range!=0){
		if($range==1)
		{
			$arryRange=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		if($range==2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		if($range==3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		if($range==4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		if($range==5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}

		
		if($whereraw == '')
			{
				$whereraw = 'range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whereraw .= ' And range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
			}
			if($whererawsales == '')
			{
				$whererawsales = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whererawsales .= 'And range_id IN ('.$finalSales.')';
				
			}
		}
		


//return $datatype;

		if($datatype == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			
		}
		elseif($datatype == 'last_month')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-30 days"));
			$fromDate = date("Y-m-01",strtotime("-1 Months")); 
		}
		elseif($datatype == 'month_3')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			$fromDate = date("Y-m-01",strtotime("-2 Months")); 
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$date='01-'.$datatype;
			$salestime=date("n-Y", strtotime($date));
		}

		//$salestime=date("n-Y", strtotime($fromDate));
		//$salestime=$datatype;
		if($whereraw == '')
			{
				$whereraw = "approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			else
			{
				$whereraw .= " And approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		

		//return $fromDate.$toDate;




		if($datatype == 'current_month' || $datatype == ''){

		//echo "hello";exit;
		//echo $whereraw;
		if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("ref_no","!=",NULL)->where("form_id",2)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("ref_no","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",2)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))
->whereRaw($whereraw)->groupBy('emp_id')->get();	
//print_r($totaldata);exit;
		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			$finalarrayempid[]=$_countdata->emp_id;

		}
		//print_r($finalarrayempid);
		//$count;
		if($whererawrange != '')
			{
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',49)->where('job_function',3)->first();
				
					
					$totalemp  = Employee_details::where('tl_id',$empdata->id)->whereNotIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('dept_id',49)->get();
					
					
			} 
			else{
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',49)->where('job_function',3)->first();
			
					$totalemp  = Employee_details::where('tl_id',$empdata->id)->where('offline_status',1)->whereNotIn('emp_id',$finalarrayempid)->where('dept_id',49)->get();
					
					}
	

		}
		else{
			return 0;
		}
	}
	else{
	
			//echo $whererawsales;
			//echo $whereraw;
			//echo $team;
			if($whererawsales != '')
			{
			$totalemp=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->where("tc","=",0)->get();
			$totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->get();
			}else{
			$totalemp=MasterPayoutPre::where("TL",$team)->where("tc","=",0)->get();
			$totalmastercard=MasterPayoutPre::where("TL",$team)->get();
			}
			
		}


		return view("components/DashboardLeadership/empCountCbdDetailspopup",compact('totalemp','range','salestime'));


	}

	
	
	
	

	public function getSpreadEmployeeCbdCountDetailsData(Request $request)
	{
		//print_r($request);exit;
		$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';

		$team=$request->team;
		$widget=$request->widget;
		$range=$request->range;

		if($request->salestime)
		{
			$datatype=$request->salestime;
		}
		else
		{
			$datatype='';
		}

		//return $datatype;


		//return $range;
		if($range!=0){


			
		
		if($range==2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		if($range==3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		if($range==4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		if($range==5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}

		
		if($whereraw == '')
			{
				$whereraw = 'range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whereraw .= ' And range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
			}
			if($whererawsales == '')
			{
				$whererawsales = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whererawsales .= 'And range_id IN ('.$finalSales.')';
				
			}
		}
		


//return $datatype;

		if($datatype == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			
		}
		elseif($datatype == 'last_month')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-30 days"));
			$fromDate = date("Y-m-01",strtotime("-1 Months")); 
		}
		elseif($datatype == 'month_3')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			$fromDate = date("Y-m-01",strtotime("-2 Months")); 
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$date='01-'.$datatype;
			$salestime=date("n-Y", strtotime($date));
		}

		//$salestime=date("n-Y", strtotime($fromDate));
		//$salestime=$datatype;
		if($whereraw == '')
			{
				$whereraw = "approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			else
			{
				$whereraw .= " And approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		

		//return $fromDate.$toDate;





		if($datatype=='' || $datatype == 'current_month' || $datatype == 'undefined')
		{
//echo $whereraw;exit;
if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("ref_no","!=",NULL)->where("form_id",2)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("ref_no","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",2)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw)->groupBy('emp_id')->get();	
//print_r($totaldata);exit;
		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			if($_countdata->total<=3){
				$finalarrayempid[]=$_countdata->emp_id;

			}

		}
		//$count;
		//print_r($finalarrayempid);
		if($whererawrange != '')
			{
				//echo "h1";
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',49)->where('job_function',3)->first();
				
					$totalemp  = Employee_details::where('tl_id',$empdata->id)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('dept_id',49)->get();
					
					
			} else{
				//echo "h2";
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',49)->where('job_function',3)->first();
			
					$totalemp  = Employee_details::where('tl_id',$empdata->id)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->where('dept_id',49)->get();
				
			
			}
			
			
			
			
			
			
			
			
//exit;			
			//return $count;
		
		
		}
		else{
			return 0;
		}
			
			
		}
		else
		{
			//echo "h2";exit;
			if($whererawsales != '')
			{
				$totalemp=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->where('dept_id',49)->whereBetween('tc', [1,3])->get();
			}
			else
			{
				$totalemp=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->where('dept_id',49)->whereBetween('tc', [1,3])->get();
				//$totalmasterTL=MasterPayoutPre::where("TL",$team)->where("tc","<=",3)->get()->count();
			}
			
			
			
			
			
			
			
			
			
			

		}



		return view("components/DashboardLeadership/empCountCbdDetailspopup",compact('totalemp','range','salestime'));


	}

	public function getSpreadEmployeeCbdCountDetails4to6Data(Request $request)
	{
		$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';

		$team=$request->team;
		$widget=$request->widget;
		$range=$request->range;

		if($request->salestime)
		{
			$datatype=$request->salestime;
		}
		else
		{
			$datatype='';
		}

		//return $datatype;


		//return $range;
		if($range!=0){
		if($range==1)
		{
			$arryRange=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		if($range==2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		if($range==3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		if($range==4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		if($range==5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}

		
		if($whereraw == '')
			{
				$whereraw = 'range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whereraw .= ' And range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
			}
			if($whererawsales == '')
			{
				$whererawsales = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whererawsales .= 'And range_id IN ('.$finalSales.')';
				
			}
		}
		


//return $datatype;

		if($datatype == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			
		}
		elseif($datatype == 'last_month')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-30 days"));
			$fromDate = date("Y-m-01",strtotime("-1 Months")); 
		}
		elseif($datatype == 'month_3')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			$fromDate = date("Y-m-01",strtotime("-2 Months")); 
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$date='01-'.$datatype;
			$salestime=date("n-Y", strtotime($date));
		}

		//$salestime=date("n-Y", strtotime($fromDate));
		//$salestime=$datatype;
		if($whereraw == '')
			{
				$whereraw = "approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			else
			{
				$whereraw .= " And approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		

		

		//return $fromDate.$toDate;





		if($datatype=='' || $datatype == 'current_month' || $datatype == 'undefined')
		{

			if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("ref_no","!=",NULL)->where("form_id",2)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("ref_no","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",2)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw)->groupBy('emp_id')->get();	
//print_r($totaldata);exit;
		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			if($_countdata->total>=4 && $_countdata->total<=6){
				$finalarrayempid[]=$_countdata->emp_id;

			}

		}
		//$count;
		if($whererawrange != '')
			{
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',49)->where('job_function',3)->first();
				
					$totalemp  = Employee_details::where('tl_id',$empdata->id)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('dept_id',49)->get();
					
					
			} else{
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',49)->where('job_function',3)->first();
			
					$totalemp  = Employee_details::where('tl_id',$empdata->id)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->where('dept_id',49)->get();
				
			
			}	
			//return $count;
		
		
		}
		else{
			return 0;
		}
			
			
		}
		else
		{
			
			
			//$whereraw = 'range_id IN ('.$finalSales.')';
				//echo $whereraw;exit;
			//$salestime=date("n-Y", strtotime($fromDate));


			if($whererawsales != '')
			{
				$totalemp=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->where('dept_id',49)->whereBetween('tc', [4, 6])->get();
			}
			else
			{
				$totalemp=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->where('dept_id',49)->whereBetween('tc', [4, 6])->get();
				//$totalmasterTL=MasterPayoutPre::where("TL",$team)->where("tc","<=",3)->get()->count();
			}
			
			
			
		}
		

		



		return view("components/DashboardLeadership/empCountCbdDetailspopup",compact('totalemp','range','salestime'));


	}


	public function getSpreadEmployeeCbdCountDetails7to10Data(Request $request)
	{
		$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';

		$team=$request->team;
		$widget=$request->widget;
		$range=$request->range;

		if($request->salestime)
		{
			$datatype=$request->salestime;
		}
		else
		{
			$datatype='';
		}

		//return $datatype;


		//return $range;
		if($range!=0){
		if($range==1)
		{
			$arryRange=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		if($range==2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		if($range==3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		if($range==4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		if($range==5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}

		
		if($whereraw == '')
			{
				$whereraw = 'range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whereraw .= ' And range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
			}
			if($whererawsales == '')
			{
				$whererawsales = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whererawsales .= 'And range_id IN ('.$finalSales.')';
				
			}
		}
		


//return $datatype;

		if($datatype == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			
		}
		elseif($datatype == 'last_month')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-30 days"));
			$fromDate = date("Y-m-01",strtotime("-1 Months")); 
		}
		elseif($datatype == 'month_3')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			$fromDate = date("Y-m-01",strtotime("-2 Months")); 
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$date='01-'.$datatype;
			$salestime=date("n-Y", strtotime($date));
		}

		//$salestime=date("n-Y", strtotime($fromDate));
		//$salestime=$datatype;
		if($whereraw == '')
			{
				$whereraw = "approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			else
			{
				$whereraw .= " And approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		

		

		//return $fromDate.$toDate;





		if($datatype=='' || $datatype == 'current_month' || $datatype == 'undefined')
		{
			
			if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("ref_no","!=",NULL)->where("form_id",2)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("ref_no","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",2)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw)->groupBy('emp_id')->get();	
//print_r($totaldata);exit;
		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			if($_countdata->total>=7 && $_countdata->total<=10){
				$finalarrayempid[]=$_countdata->emp_id;

			}

		}
		//$count;
if($whererawrange != '')
			{
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',49)->where('job_function',3)->first();
				
					$totalemp  = Employee_details::where('tl_id',$empdata->id)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('dept_id',49)->get();
					
					
			} else{
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',49)->where('job_function',3)->first();
			
					$totalemp  = Employee_details::where('tl_id',$empdata->id)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->where('dept_id',49)->get();
				
			
			}	
			//return $count;
		
		
		}
		else{
			return 0;
		}
			
			
		}
			else
		{
			//$whereraw = 'range_id IN ('.$finalSales.')';
				//echo $whereraw;exit;
			//$salestime=date("n-Y", strtotime($fromDate));

//echo $whererawsales;
			if($whererawsales != '')
			{
				$totalemp=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->where('dept_id',49)->whereBetween('tc', [7, 10])->get();
			}
			else
			{
				$totalemp=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->where('dept_id',49)->whereBetween('tc', [7, 10])->get();
				//$totalmasterTL=MasterPayoutPre::where("TL",$team)->where("tc","<=",3)->get()->count();
			}
			
			
			
		}


		



		return view("components/DashboardLeadership/empCountCbdDetailspopup",compact('totalemp','range','salestime'));


	}


	public function getSpreadEmployeeCbdCountDetails10plusData(Request $request)
	{
		$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';

		$team=$request->team;
		$widget=$request->widget;
		$range=$request->range;

		if($request->salestime)
		{
			$datatype=$request->salestime;
		}
		else
		{
			$datatype='';
		}

		//return $datatype;


		//return $range;
		if($range!=0){
		if($range==1)
		{
			$arryRange=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		if($range==2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		if($range==3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		if($range==4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		if($range==5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}

		
		if($whereraw == '')
			{
				$whereraw = 'range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whereraw .= ' And range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
			}
			if($whererawsales == '')
			{
				$whererawsales = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whererawsales .= 'And range_id IN ('.$finalSales.')';
				
			}
		}
		


//return $datatype;

		if($datatype == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			
		}
		elseif($datatype == 'last_month')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-30 days"));
			$fromDate = date("Y-m-01",strtotime("-1 Months")); 
		}
		elseif($datatype == 'month_3')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			$fromDate = date("Y-m-01",strtotime("-2 Months")); 
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$date='01-'.$datatype;
			$salestime=date("n-Y", strtotime($date));
		}

		//$salestime=date("n-Y", strtotime($fromDate));
		//$salestime=$datatype;
		if($whereraw == '')
			{
				$whereraw = "approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			else
			{
				$whereraw .= " And approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
				
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		

		

		//return $fromDate.$toDate;





		if($datatype=='' || $datatype == 'current_month' || $datatype == 'undefined')
		{


if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("ref_no","!=",NULL)->where("form_id",2)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("ref_no","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",2)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw)->groupBy('emp_id')->get();	
//print_r($totaldata);exit;
		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
				if($_countdata->total>10){
		
				$finalarrayempid[]=$_countdata->emp_id;

			}

		}
		//$count;

if($whererawrange != '')
			{
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',49)->where('job_function',3)->first();
				
					$totalemp  = Employee_details::where('tl_id',$empdata->id)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('dept_id',49)->get();
					
					
			} else{
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',49)->where('job_function',3)->first();
			
					$totalemp  = Employee_details::where('tl_id',$empdata->id)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->where('dept_id',49)->get();
				
			
			}	
			//return $count;
		
		
		}
		else{
			return 0;
		}
			
			
		}
		else
		{


			
			//$whereraw = 'range_id IN ('.$finalSales.')';
				//echo $whereraw;exit;
			//$salestime=date("n-Y", strtotime($fromDate));






			if($whererawsales != '')
			{
				$totalemp=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->where('dept_id',49)->where("tc",">",10)->get();
			}
			else
			{
				$totalemp=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->where('dept_id',49)->where("tc",">",10)->get();
				//$totalmasterTL=MasterPayoutPre::where("TL",$team)->where("tc","<=",3)->get()->count();
			}
			
			
			
		
		

		}



		return view("components/DashboardLeadership/empCountCbdDetailspopup",compact('totalemp','range','salestime'));


	}










public function getSpreadEmployeeCountDetailsDatabyProcessorlesszeroData(Request $request)
	{
		

		$processer=$request->proc;

		if($processer=="Mahwish"){
			$teamdataval=array('Ajay','Mujahid','Anas','Shahnawaz');
		}
		elseif($processer=="Umar"){
			$teamdataval=array('Arsalan','Zubair');
			
		}
		elseif($processer=="Tapash Dahal"){
			$teamdataval=array('Mohsin','Sahir');
			
		}


$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';

		$team=$request->team;
		$widget=$request->widget;
		$range=$request->range;

		if($request->salestime)
		{
			$datatype=$request->salestime;
		}
		else
		{
			$datatype='';
		}

		//return $datatype;


		//return $range;
		if($range!=0){
		if($range==1)
		{
			$arryRange=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		if($range==2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		if($range==3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		if($range==4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		if($range==5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}

		
		if($whereraw == '')
			{
				$whereraw = 'range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whereraw .= ' And range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
			}
			
			if($whererawsales == '')
			{
				$whererawsales = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whererawsales .= 'And range_id IN ('.$finalSales.')';
				
			}
		}
		


//return $datatype;

		if($datatype == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			
		}
		elseif($datatype == 'last_month')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-30 days"));
			$fromDate = date("Y-m-01",strtotime("-1 Months")); 
		}
		elseif($datatype == 'month_3')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			$fromDate = date("Y-m-01",strtotime("-2 Months")); 
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$date='01-'.$datatype;
			$salestime=date("n-Y", strtotime($date));
		}

		//$salestime=date("n-Y", strtotime($fromDate));
		//$salestime=$datatype;
		if($whereraw == '')
			{
				$whereraw = "date_of_disbursal >= '".$fromDate."' and date_of_disbursal <= '".$toDate."'";
				
			}
			else
			{
				$whereraw .= " And date_of_disbursal >= '".$fromDate."' and date_of_disbursal <= '".$toDate."'";
				
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		

		
		//return $fromDate.$toDate;




		if($datatype=='' || $datatype == 'current_month' || $datatype == 'undefined')
		{

		if($whereraw != '')
		{		

		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->whereIn("team",$teamdataval)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->whereIn("team",$teamdataval)->groupBy('emp_id')->get();

			
		//$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	
//print_r($totaldata);exit;
		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			
			$finalarrayempid[]=$_countdata->emp_id;
		}
		
		$empdata  = Employee_details::whereIn('sales_name',$teamdataval)->where('dept_id',36)->where('job_function',3)->get();
				 //print_r($empdata);exit;
				 $ids=array();
				 
					 foreach($empdata as $_id){
					$ids[]= $_id->id;
					}
		
		//$count;
		if($whererawrange != '')
			{
			
	$totalemp  = Employee_details::whereIn('tl_id',$ids)->whereNotIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('job_function',2)->where('dept_id',36)->get();

			
	//$totalemp  = Employee_details::whereIn('tl_id',$ids)->whereNotIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('dept_id',36)->get();

					
					
					
					
			} 
			else{
			
$totalemp  = Employee_details::whereIn('tl_id',$ids)->whereNotIn('emp_id',$finalarrayempid)->where('offline_status',1)->where('job_function',2)->where('dept_id',36)->get();



					
					}
	

		}
		else{
			return 0;
		}
			
			
		}
		else
		{
			//$whereraw = 'range_id IN ('.$finalSales.')';
				//echo $$whererawsales;exit;
			//$salestime=date("n-Y", strtotime($fromDate));



	if($whererawsales != '')
			{
			$totalemp=MasterPayoutPre::whereRaw($whererawsales)->whereIn("TL",$teamdataval)->where("tc","=",0)->get();
			
			}else{
			$totalemp=MasterPayoutPre::whereRaw($whererawsales)->whereIn("TL",$teamdataval)->where("tc","=",0)->get();
			
			}
	
		}

		return view("components/DashboardLeadership/empCountCbdDetailspopup",compact('totalemp','range','salestime'));


	


	}



	public function getSpreadEmployeeCountDetailsDatabyProcessorless3Data(Request $request)
	{
		$whereraw='';

		$processer=$request->proc;

		if($processer=="Mahwish"){
			$teamdataval=array('Ajay','Mujahid','Anas','Shahnawaz');
		}
		elseif($processer=="Umar"){
			$teamdataval=array('Arsalan','Zubair');
			
		}
		elseif($processer=="Tapash Dahal"){
			$teamdataval=array('Mohsin','Sahir');
			
		}


		$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';

		$team=$request->team;
		$widget=$request->widget;
		$range=$request->range;

		if($request->salestime)
		{
			$datatype=$request->salestime;
		}
		else
		{
			$datatype='';
		}

		//return $datatype;


		//return $range;
		if($range!=0){
		if($range==1)
		{
			$arryRange=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		if($range==2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		if($range==3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		if($range==4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		if($range==5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}

		
		if($whereraw == '')
			{
				$whereraw = 'range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whereraw .= ' And range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
			}
			if($whererawsales == '')
			{
				$whererawsales = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whererawsales .= 'And range_id IN ('.$finalSales.')';
				
			}
		}
		


//return $datatype;

		if($datatype == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			
		}
		elseif($datatype == 'last_month')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-30 days"));
			$fromDate = date("Y-m-01",strtotime("-1 Months")); 
		}
		elseif($datatype == 'month_3')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			$fromDate = date("Y-m-01",strtotime("-2 Months")); 
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$date='01-'.$datatype;
			$salestime=date("n-Y", strtotime($date));
		}

		//$salestime=date("n-Y", strtotime($fromDate));
		//$salestime=$datatype;
		if($whereraw == '')
			{
				$whereraw = "date_of_disbursal >= '".$fromDate."' and date_of_disbursal <= '".$toDate."'";
				
			}
			else
			{
				$whereraw .= " And date_of_disbursal >= '".$fromDate."' and date_of_disbursal <= '".$toDate."'";
				
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		


		

		//return $fromDate.$toDate;





		if($datatype=='' || $datatype == 'current_month' || $datatype == 'undefined')
		{

		if($whereraw != '')
		{		

		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->whereIn("team",$teamdataval)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->whereIn("team",$teamdataval)->groupBy('emp_id')->get();

			
		//$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	
//print_r($totaldata);exit;
		}
		
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			if($_countdata->total<=3){
				$finalarrayempid[]=$_countdata->emp_id;
//$totalreturndata=$count;
			}

		}
		$empdata  = Employee_details::whereIn('sales_name',$teamdataval)->where('dept_id',36)->where('job_function',3)->get();
				 //print_r($empdata);exit;
				$ids=array();
				 
					 foreach($empdata as $_id){
					$ids[]= $_id->id;
					}
					
		
		//$count;
		if($whererawrange != '')
			{
			
	      $totalemp  = Employee_details::whereIn('tl_id',$ids)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('job_function',2)->where('dept_id',36)->get();

			
	//$totalemp  = Employee_details::whereIn('tl_id',$ids)->whereNotIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('dept_id',36)->get()
	      
	      
	      
			
	//$totalemp  = Employee_details::whereIn('tl_id',$ids)->whereNotIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('dept_id',36)->get();		
					
					
			} 
			else{
			
    $totalemp  = Employee_details::whereIn('tl_id',$ids)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->where('job_function',2)->where('dept_id',36)->get();



					
					}
	

		}
		else{
			return 0;
		}
			
			
		}

			
			else
		{
			//$whereraw = 'range_id IN ('.$finalSales.')';
				//echo $$whererawsales;exit;
			//$salestime=date("n-Y", strtotime($fromDate));



	if($whererawsales != '')
			{
			$totalemp=MasterPayoutPre::whereRaw($whererawsales)->whereIn("TL",$teamdataval)->whereBetween('tc', [1, 3])->get();
			
			}else{
			$totalemp=MasterPayoutPre::whereRaw($whererawsales)->whereIn("TL",$teamdataval)->whereBetween('tc', [1, 3])->get();
			
			}
	
		}

		return view("components/DashboardLeadership/empCountCbdDetailspopup",compact('totalemp','range','salestime'));


		


	}
			
			
			


	public function getSpreadEmployeeCountDetailsDatabyProcessor4to6Data(Request $request)
	{
		$whereraw='';

		$processer=$request->proc;

		if($processer=="Mahwish"){
			$teamdataval=array('Ajay','Mujahid','Anas','Shahnawaz');
		}
		elseif($processer=="Umar"){
			$teamdataval=array('Arsalan','Zubair');
			
		}
		elseif($processer=="Tapash Dahal"){
			$teamdataval=array('Mohsin','Sahir');
			
		}




				$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';

		$team=$request->team;
		$widget=$request->widget;
		$range=$request->range;

		if($request->salestime)
		{
			$datatype=$request->salestime;
		}
		else
		{
			$datatype='';
		}

		//return $datatype;


		//return $range;
		if($range!=0){
		if($range==1)
		{
			$arryRange=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		if($range==2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		if($range==3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		if($range==4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		if($range==5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}

		
		if($whereraw == '')
			{
				$whereraw = 'range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whereraw .= ' And range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
			}
			if($whererawsales == '')
			{
				$whererawsales = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whererawsales .= 'And range_id IN ('.$finalSales.')';
				
			}
		}
		


//return $datatype;

		if($datatype == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			
		}
		elseif($datatype == 'last_month')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-30 days"));
			$fromDate = date("Y-m-01",strtotime("-1 Months")); 
		}
		elseif($datatype == 'month_3')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			$fromDate = date("Y-m-01",strtotime("-2 Months")); 
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$date='01-'.$datatype;
			$salestime=date("n-Y", strtotime($date));
		}

		//$salestime=date("n-Y", strtotime($fromDate));
		//$salestime=$datatype;
		if($whereraw == '')
			{
				$whereraw = "date_of_disbursal >= '".$fromDate."' and date_of_disbursal <= '".$toDate."'";
				
			}
			else
			{
				$whereraw .= " And date_of_disbursal >= '".$fromDate."' and date_of_disbursal <= '".$toDate."'";
				
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		


		

		//return $fromDate.$toDate;





		if($datatype=='' || $datatype == 'current_month' || $datatype == 'undefined')
		{

		if($whereraw != '')
		{		

		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->whereIn("team",$teamdataval)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->whereIn("team",$teamdataval)->groupBy('emp_id')->get();

			
		//$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	
//print_r($totaldata);exit;
		}
		
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			if($_countdata->total>=4 && $_countdata->total<=6){
				$finalarrayempid[]=$_countdata->emp_id;
//$totalreturndata=$count;
			}

		}
		$empdata  = Employee_details::whereIn('sales_name',$teamdataval)->where('dept_id',36)->where('job_function',3)->get();
				 //print_r($empdata);exit;
				$ids=array();
				 
					 foreach($empdata as $_id){
					$ids[]= $_id->id;
					}
					
		
		//$count;
		if($whererawrange != '')
			{
			
	      $totalemp  = Employee_details::whereIn('tl_id',$ids)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('job_function',2)->where('dept_id',36)->get();

			
	//$totalemp  = Employee_details::whereIn('tl_id',$ids)->whereNotIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('dept_id',36)->get()
	      
	      
	      
			
	//$totalemp  = Employee_details::whereIn('tl_id',$ids)->whereNotIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('dept_id',36)->get();		
					
					
			} 
			else{
			
    $totalemp  = Employee_details::whereIn('tl_id',$ids)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->where('job_function',2)->where('dept_id',36)->get();



					
					}
	

		}
		else{
			return 0;
		}
			
			
		}

			
			else
		{
			//$whereraw = 'range_id IN ('.$finalSales.')';
				//echo $$whererawsales;exit;
			//$salestime=date("n-Y", strtotime($fromDate));



	if($whererawsales != '')
			{
			$totalemp=MasterPayoutPre::whereRaw($whererawsales)->whereIn("TL",$teamdataval)->whereBetween('tc', [4, 6])->get();
			
			}else{
			$totalemp=MasterPayoutPre::whereRaw($whererawsales)->whereIn("TL",$teamdataval)->whereBetween('tc', [4, 6])->get();
			
			}
	
		}



		return view("components/DashboardLeadership/empCountCbdDetailspopup",compact('totalemp','range','salestime'));


	}


	public function getSpreadEmployeeCountDetailsDatabyProcessor7to10Data(Request $request)
	{
		$whereraw='';

		$processer=$request->proc;

		if($processer=="Mahwish"){
			$teamdataval=array('Ajay','Mujahid','Anas','Shahnawaz');
		}
		elseif($processer=="Umar"){
			$teamdataval=array('Arsalan','Zubair');
			
		}
		elseif($processer=="Tapash Dahal"){
			$teamdataval=array('Mohsin','Sahir');
			
		}



				$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';

		$team=$request->team;
		$widget=$request->widget;
		$range=$request->range;

		if($request->salestime)
		{
			$datatype=$request->salestime;
		}
		else
		{
			$datatype='';
		}

		//return $datatype;


		//return $range;
		if($range!=0){
		if($range==1)
		{
			$arryRange=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		if($range==2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		if($range==3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		if($range==4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		if($range==5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}

		
		if($whereraw == '')
			{
				$whereraw = 'range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whereraw .= ' And range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
			}
			if($whererawsales == '')
			{
				$whererawsales = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whererawsales .= 'And range_id IN ('.$finalSales.')';
				
			}
		}
		


//return $datatype;

		if($datatype == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			
		}
		elseif($datatype == 'last_month')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-30 days"));
			$fromDate = date("Y-m-01",strtotime("-1 Months")); 
		}
		elseif($datatype == 'month_3')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			$fromDate = date("Y-m-01",strtotime("-2 Months")); 
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$date='01-'.$datatype;
			$salestime=date("n-Y", strtotime($date));
		}

		//$salestime=date("n-Y", strtotime($fromDate));
		//$salestime=$datatype;
		if($whereraw == '')
			{
				$whereraw = "date_of_disbursal >= '".$fromDate."' and date_of_disbursal <= '".$toDate."'";
				
			}
			else
			{
				$whereraw .= " And date_of_disbursal >= '".$fromDate."' and date_of_disbursal <= '".$toDate."'";
				
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		


		

		//return $fromDate.$toDate;





		if($datatype=='' || $datatype == 'current_month' || $datatype == 'undefined')
		{

		if($whereraw != '')
		{		

		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->whereIn("team",$teamdataval)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->whereIn("team",$teamdataval)->groupBy('emp_id')->get();

			
		//$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	
//print_r($totaldata);exit;
		}
		
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
				if($_countdata->total>=7 && $_countdata->total<=10){
			$finalarrayempid[]=$_countdata->emp_id;
//$totalreturndata=$count;
			}
		}
		
		$empdata  = Employee_details::whereIn('sales_name',$teamdataval)->where('dept_id',36)->where('job_function',3)->get();
				 //print_r($empdata);exit;
				$ids=array();
				 
					 foreach($empdata as $_id){
					$ids[]= $_id->id;
					}
					
		
		//$count;
		if($whererawrange != '')
			{
			
	      $totalemp  = Employee_details::whereIn('tl_id',$ids)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('job_function',2)->where('dept_id',36)->get();

			
	//$totalemp  = Employee_details::whereIn('tl_id',$ids)->whereNotIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('dept_id',36)->get()
	      
	      
	      
			
					
					
			} 
			else{
			
    $totalemp  = Employee_details::whereIn('tl_id',$ids)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->where('job_function',2)->where('dept_id',36)->get();



					
					}
	

		}
		else{
			return 0;
		}
			
			
		}

			
			else
		{
			//$whereraw = 'range_id IN ('.$finalSales.')';
				//echo $$whererawsales;exit;
			//$salestime=date("n-Y", strtotime($fromDate));



	if($whererawsales != '')
			{
			$totalemp=MasterPayoutPre::whereRaw($whererawsales)->whereIn("TL",$teamdataval)->whereBetween('tc', [7, 10])->get();
			
			}else{
			$totalemp=MasterPayoutPre::whereRaw($whererawsales)->whereIn("TL",$teamdataval)->whereBetween('tc', [7, 10])->get();
			
			}
	
		}

		

		return view("components/DashboardLeadership/empCountCbdDetailspopup",compact('totalemp','range','salestime'));


	}


	public function getSpreadEmployeeCountDetailsDatabyProcessor10plusData(Request $request)
	{
		$whereraw='';

		$processer=$request->proc;

		if($processer=="Mahwish"){
			$teamdataval=array('Ajay','Mujahid','Anas','Shahnawaz');
		}
		elseif($processer=="Umar"){
			$teamdataval=array('Arsalan','Zubair');
			
		}
		elseif($processer=="Tapash Dahal"){
			$teamdataval=array('Mohsin','Sahir');
			
		}




		$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';

		$team=$request->team;
		$widget=$request->widget;
		$range=$request->range;

		if($request->salestime)
		{
			$datatype=$request->salestime;
		}
		else
		{
			$datatype='';
		}

		//return $datatype;


		//return $range;
		if($range!=0){
		if($range==1)
		{
			$arryRange=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		if($range==2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		if($range==3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		if($range==4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		if($range==5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}

		
		if($whereraw == '')
			{
				$whereraw = 'range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whereraw .= ' And range_disbursal IN ('.$finalSales.')';
				
				$whererawrange = 'range_id IN ('.$finalSales.')';
			}
			if($whererawsales == '')
			{
				$whererawsales = 'range_id IN ('.$finalSales.')';
				
			}
			else
			{
				$whererawsales .= 'And range_id IN ('.$finalSales.')';
				
			}
		}
		


//return $datatype;

		if($datatype == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			
		}
		elseif($datatype == 'last_month')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-30 days"));
			$fromDate = date("Y-m-01",strtotime("-1 Months")); 
		}
		elseif($datatype == 'month_3')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			$fromDate = date("Y-m-01",strtotime("-2 Months")); 
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$date='01-'.$datatype;
			$salestime=date("n-Y", strtotime($date));
		}

		//$salestime=date("n-Y", strtotime($fromDate));
		//$salestime=$datatype;
		if($whereraw == '')
			{
				$whereraw = "date_of_disbursal >= '".$fromDate."' and date_of_disbursal <= '".$toDate."'";
				
			}
			else
			{
				$whereraw .= " And date_of_disbursal >= '".$fromDate."' and date_of_disbursal <= '".$toDate."'";
				
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		


		//return $fromDate.$toDate;





		if($datatype=='' || $datatype == 'current_month' || $datatype == 'undefined')
		{

			
			//$whereraw = 'range_id IN ('.$finalSales.')';
				//echo $whereraw;exit;
			//$salestime=date("n-Y", strtotime($fromDate));


if($whereraw != '')
		{		

		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->whereIn("team",$teamdataval)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->whereIn("team",$teamdataval)->groupBy('emp_id')->get();

			
		//$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	
//print_r($totaldata);exit;
		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			if($_countdata->total>=11){
			$finalarrayempid[]=$_countdata->emp_id;
//$totalreturndata=$count;
			}
		}
		
		$empdata  = Employee_details::whereIn('sales_name',$teamdataval)->where('dept_id',36)->where('job_function',3)->get();
				 //print_r($empdata);exit;
				 $ids=array();
				 
					 foreach($empdata as $_id){
					$ids[]= $_id->id;
					}
		
		//$count;
		if($whererawrange != '')
			{
			
	$totalemp  = Employee_details::whereIn('tl_id',$ids)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('job_function',2)->where('dept_id',36)->get();

			
	//$totalemp  = Employee_details::whereIn('tl_id',$ids)->whereNotIn('emp_id',$finalarrayempid)->where('offline_status',1)->whereRaw($whererawrange)->where('dept_id',36)->get();

					
					
					
					
			} 
			else{
			
$totalemp  = Employee_details::whereIn('tl_id',$ids)->whereIn('emp_id',$finalarrayempid)->where('offline_status',1)->where('job_function',2)->where('dept_id',36)->get();



					
					}
	

		}
		else{
			return 0;
		}
			
			
		}
		else
		{
			//$whereraw = 'range_id IN ('.$finalSales.')';
				//echo $$whererawsales;exit;
			//$salestime=date("n-Y", strtotime($fromDate));



	if($whererawsales != '')
			{
			$totalemp=MasterPayoutPre::whereRaw($whererawsales)->whereIn("TL",$teamdataval)->where("tc",">",10)->get();
			
			}else{
			$totalemp=MasterPayoutPre::whereRaw($whererawsales)->whereIn("TL",$teamdataval)->where("tc",">",10)->get();
			
			}
	
		}

		return view("components/DashboardLeadership/empCountCbdDetailspopup",compact('totalemp','range','salestime'));


		}
		
		
		public static function getVintageData($empid)
{
   
	 //echo $empid;exit;
	 $empIddata = Employee_details::where("emp_id",$empid)->first();
			if($empIddata!=''){
				$empId=$empIddata->emp_id;
				$empDOJObj  = Employee_attribute::where("attribute_code","DOJ")->where('emp_id',$empId)->first();
				if($empDOJObj != '')
				{
					$doj = $empDOJObj->attribute_values;
					if($doj == NULL || $doj == '')
					{
						return "Not Decleared";
					}
					else
					{
						$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 return  $returnData;
					}
					
				}
				else
				{
					return "--";
				}
			}
}
		
public static function getcheckOfflinestatus($empid){
	$totalemp  = Employee_details::where('emp_id',$empid)->first();
	if($totalemp!=''){
		if($totalemp->offline_status==2 || $totalemp->offboard_status==3){
			return "*";
			}
			else{
				return "";
			}
	}
	else{
		return "";
		}

}

	}





