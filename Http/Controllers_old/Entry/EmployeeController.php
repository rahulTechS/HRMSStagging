<?php

namespace App\Http\Controllers\Entry;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entry\Employee;
use Crypt;
use Session;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use App\Models\MIS\ENDBCARDStatus;
use App\Models\MIS\MainMisReport;

class EmployeeController extends Controller
{

	public function __construct()
	{
		
		
	}
	
    public function checkpass()
	{
		
		$username = 'rahulpr';
		
		 $password = '123456';
		echo $pwd = Crypt::encrypt($password);exit;
	}

	public function checkEmployeeEntry(Request $request)
	{
		
		
		 return view('Entry/Employee/checkEmployeeEntry');

	}

	public function checkEmployeePost(Request $req)
	{
		$username =  $req->input('username');
		$password = $req->input('password');
		$getmatch =  Employee::where('username',$username)->where("status",1)->get()->count();
		
		if($getmatch >0)
		{
			$employeeList = Employee::where('username',$username)->first();
			$passwordFromUsername = Crypt::decrypt($employeeList->password);
			if( $passwordFromUsername == $password)
			{
				
			$req->session()->put('EmployeeId',$employeeList->id);
			$req->session()->put('EmployeeDesignation',$employeeList->designation);
			$req->session()->get('EmployeeId');
			/*
			*check employee degination
			*/
			/* if($employeeList->designation == 'Admin')
			{ */
				return redirect('dashboard');
			/* }
			if($employeeList->designation != 'Admin' || )
			{
				return redirect('dashboard');
			}
			if($employeeList->designation == 'Consultancy')
			{
				return redirect('registeredConsultancy');
			}
			
			if($employeeList->designation == 'Recruiter')
			{
				return redirect('registeredRecruiter');
			} */
			/*
			*check employee degination
			*/
			
			
			}
			else
			{
				$req->session()->flash('message','username or password is not correct.');
				return redirect()->back();
			}
		}
		else
		{
			$req->session()->flash('message','username or password is not correct.');
				return redirect()->back();
		}
		
		exit;

	}

	public function dashboard(Request $req)
	{
		$empId = $req->session()->get('EmployeeId');
		$empDetails = Employee::where("id",$empId)->first();
		$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				$tL_details = Employee_details::where("job_role",'Team Leader')->where("dept_id",9)->get();
				$status = ENDBCARDStatus::where("status",1)->get();
				if($empId == 60)
				{
					return view('Home/Dashboard/dashboardReport',compact('empDetails','tL_details','status'));
				}
				else if($empId == 1)
				{
					//return view('Home/DeptDashboard/dashboardReportDept',compact('empDetails','tL_details','status'));
					return view('Home/Dashboard/dashboardReport',compact('empDetails','tL_details','status'));
				}
				else
				{
					return view('Home/Dashboard/dashboard',compact('empDetails','tL_details','status'));
				}
	}

	public function leaveEmployee(Request $request)
	{
		$request->session()->forget('EmployeeId');
			$request->session()->forget('EmployeeUsername');
			return redirect('/');
	}
	
	public function findSEDashBoard(Request $request)
		   {
			    $tlId = $request->tlId;
				$agent_details = array();
				
				if($tlId != 'All')
				{
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->where("tl_id",$tlId)->get();
				}
				return view("Home/Dashboard/findSEDashBoard",compact('agent_details'));
		   }
		public function findSEDashBoard_Ale(Request $request)
		   {
			    $tlId = $request->tlId;
				$agent_details = array();
				
				if($tlId != 'All')
				{
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->where("tl_id",$tlId)->get();
				}
				return view("Home/Dashboard/AleNale/findSEDashBoard_Ale",compact('agent_details'));
		   }   
		public function findSEDashBoard_avg(Request $request)
		   {
			    $tlId = $request->tlId;
				$agent_details = array();
				
				if($tlId != 'All')
				{
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->where("tl_id",$tlId)->get();
				}
				return view("Home/Dashboard/SalesAvg/findSEDashBoard_avg",compact('agent_details'));
		   }    

public function findSEDashBoard_joining(Request $request)
		   {
			    $tlId = $request->tlId;
				$agent_details = array();
				
				if($tlId != 'All')
				{
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->where("tl_id",$tlId)->get();
				}
				return view("Home/Dashboard/Joining/findSEDashBoard_joining",compact('agent_details'));
		   } 

public function findSEDashBoard_sub_ageing(Request $request)
		   {
			    $tlId = $request->tlId;
				$agent_details = array();
				
				if($tlId != 'All')
				{
					$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->where("tl_id",$tlId)->get();
				}
				/* echo '<pre>';
				print_r($agent_details);
				exit; */
				return view("Home/Dashboard/Ageing/findSEDashBoard_sub_ageing",compact('agent_details'));
		   } 

		   
		   
	public function LoadCardsLoginTL(Request $request)
	{
		$type = $request->type;
		$quarter = $request->quarter;
		$cards_status = $request->cards_status;
		$tL_details = Employee_details::where("job_role",'Team Leader')->where("dept_id",9)->get();
		return view('Home/Dashboard/LoadCardsLoginTL',compact('tL_details','type','quarter','cards_status'));
	}
	
	public function LoadCardsLoginSE(Request $request)
	{
		$type = $request->type;
		$quarter = $request->quarter;
		$tlId = $request->tlId;
		$SE = $request->SE;
		$cards_status = $request->cards_status;
		if($SE == 'All')
		{
		$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->where("tl_id",$tlId)->get();
		}
		else
		{
			$seArray = explode("_",$SE);
			
			$agent_details = Employee_details::where("source_code",$seArray[1])->get();
		}
		return view('Home/Dashboard/LoadCardsLoginSE',compact('agent_details','type','quarter','cards_status'));
	}
	
	
	
	
	
	public function LoadCardsLoginTL_ale(Request $request)
	{
		$type = $request->type;
		$quarter = $request->quarter;
		$cards_ale = $request->cards_ale;
		$tL_details = Employee_details::where("job_role",'Team Leader')->where("dept_id",9)->get();
		return view('Home/Dashboard/AleNale/LoadCardsLoginTL_ale',compact('tL_details','type','quarter','cards_ale'));
	}
	
	public function LoadCardsLoginSE_ale(Request $request)
	{
		$type = $request->type;
		$quarter = $request->quarter;
		$tlId = $request->tlId;
		$SE = $request->SE;
		$cards_ale = $request->cards_ale;
		if($SE == 'All')
		{
		$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->where("tl_id",$tlId)->get();
		}
		else
		{
			$seArray = explode("_",$SE);
			
			$agent_details = Employee_details::where("source_code",$seArray[1])->get();
		}
		return view('Home/Dashboard/AleNale/LoadCardsLoginSE_ale',compact('agent_details','type','quarter','cards_ale'));
	}
	
	
	
	public function LoadCardsLoginTL_avg(Request $request)
	{
		$type = $request->type;
		$quarter = $request->quarter;
		
		$tL_details = Employee_details::where("job_role",'Team Leader')->where("dept_id",9)->get();
		return view('Home/Dashboard/SalesAvg/LoadCardsLoginTL_avg',compact('tL_details','type','quarter'));
	}
	
	public function LoadCardsLoginSE_avg(Request $request)
	{
		$type = $request->type;
		$quarter = $request->quarter;
		$tlId = $request->tlId;
		$SE = $request->SE;
		
		if($SE == 'All')
		{
		$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->where("tl_id",$tlId)->get();
		}
		else
		{
			$seArray = explode("_",$SE);
			
			$agent_details = Employee_details::where("source_code",$seArray[1])->get();
		}
		return view('Home/Dashboard/SalesAvg/LoadCardsLoginSE_avg',compact('agent_details','type','quarter'));
	}
	
	
	public function LoadCardsLoginTL_joining(Request $request)
	{
		
		
		$tL_details = Employee_details::where("job_role",'Team Leader')->where("dept_id",9)->get();
		return view('Home/Dashboard/Joining/LoadCardsLoginTL_joining',compact('tL_details'));
	}
	
	public function LoadCardsLoginSE_joining(Request $request)
	{
		
		$tlId = $request->tlId;
		$SE = $request->SE;
		
		if($SE == 'All')
		{
		$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->where("tl_id",$tlId)->get();
		}
		else
		{
			$seArray = explode("_",$SE);
			
			$agent_details = Employee_details::where("source_code",$seArray[1])->get();
		}
		return view('Home/Dashboard/Joining/LoadCardsLoginSE_joining',compact('agent_details'));
	}
	
	public function submissionAgeing(Request $request)
	{
		$type_sub_ageing = $request->type_sub_ageing;
		$status_ageing = $request->status_ageing;
		$status = $status_ageing;
		$tL_details = Employee_details::where("job_role",'Team Leader')->where("dept_id",9)->get();
		return view('Home/Dashboard/Ageing/submissionAgeing',compact('tL_details','type_sub_ageing','status'));
	}
	
	public function submissionAgeingDetailed(Request $request)
	{
		$type_sub_ageing = $request->type_sub_ageing;
		$tlId = $request->tlId;
		$SE = $request->SE;
		$status_ageing = $request->status_ageing;
		
		if($type_sub_ageing != 'all')
		{
			if($SE == 'All')
			{
				if($status_ageing == 'wip')
				{
					$listOfMis  = MainMisReport::where("file_source",$type_sub_ageing)->where('match_status',2)->where("approved_notapproved",7)->where("TL",$tlId)->orderBy("last_updated_date","ASC")->get();
				}
				else
				{
					$listOfMis  = MainMisReport::where("file_source",$type_sub_ageing)->where('match_status',2)->whereIn("approved_notapproved",array(1,6))->where("TL",$tlId)->orderBy("last_updated_date","ASC")->get();
				}
			}
			else
			{
				if($status_ageing == 'wip')
				{
					$listOfMis  = MainMisReport::where("file_source",$type_sub_ageing)->where('match_status',2)->where("approved_notapproved",7)->where("TL",$tlId)->where("employee_id",$SE)->orderBy("last_updated_date","ASC")->get();
				}
				else
				{
					$listOfMis  = MainMisReport::where("file_source",$type_sub_ageing)->where('match_status',2)->where("approved_notapproved",array(1,6))->where("TL",$tlId)->where("employee_id",$SE)->orderBy("last_updated_date","ASC")->get();
				}
			}
		}
		else
		{
			if($SE == 'All')
			{
				if($status_ageing == 'wip')
				{
				$listOfMis  = MainMisReport::where('match_status',2)->where("approved_notapproved",7)->where("TL",$tlId)->orderBy("last_updated_date","ASC")->get();
				}
				else
				{
					$listOfMis  = MainMisReport::where('match_status',2)->where("approved_notapproved",array(1,6))->where("TL",$tlId)->orderBy("last_updated_date","ASC")->get();
				}
			}
			else
			{
				if($status_ageing == 'wip')
				{
					$listOfMis  = MainMisReport::where('match_status',2)->where("approved_notapproved",7)->where("TL",$tlId)->where("employee_id",$SE)->orderBy("last_updated_date","ASC")->get();
				}
				else
				{
					$listOfMis  = MainMisReport::where('match_status',2)->where("approved_notapproved",array(1,6))->where("TL",$tlId)->where("employee_id",$SE)->orderBy("last_updated_date","ASC")->get();
				}
			}
		}
		return view('Home/Dashboard/Ageing/submissionAgeingDetailed',compact('listOfMis'));
	}
}
