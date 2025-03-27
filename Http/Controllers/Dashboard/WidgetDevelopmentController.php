<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\Dashboard\DashboardCreation;
use App\Models\Dashboard\WidgetCreation;
use App\Models\Dashboard\DashboardParentMenu;
use App\Models\Dashboard\WidgetLayout;
use App\Models\Dashboard\WidgetLeadershipDetails;
use App\Models\Dashboard\Widgetlayouts\WidgetOnboardingHiring;
use App\Models\Dashboard\Widgetlayouts\WidgetBarOnboarded;
use App\Models\Dashboard\Widgetlayouts\WidgetBarMol;
use App\Models\Dashboard\Widgetlayouts\WidgetBarEvisa;
use App\Models\Dashboard\Widgetlayouts\WidgetBarShortlisted;
use App\Models\Entry\Employee;
use App\Models\Job\JobOpening;


class WidgetDevelopmentController extends Controller
{
  
	public function widgetCreation()
	{
		
		$widgetList = WidgetCreation::orderBy("id","DESC")->get();
		
		 return view("dashboard/widget_creation",compact('widgetList')); 
	}
	
	public function addWidget()
	{
		$listDashboard = DashboardCreation::where("status",1)->get();
		$userList = Employee::where("status",1)->get();
		return view("dashboard/addWidget",compact('listDashboard','userList'));
	}
	
	public function editWidget(Request $request)
	{
		$wid = $request->wid;
		/* echo $wid;
		exit; */
		$widgetData = WidgetCreation::where("id",$wid)->first();
		$listDashboard = DashboardCreation::where("status",1)->get();
		$userList = Employee::where("status",1)->get();
		return view("dashboard/editWidget",compact('listDashboard','userList','widgetData'));
	}
	
	public function addWidgetPost(Request $request)
	{
		$parametersInput = $request->input();
		
		$widget_name = $parametersInput['widget_name'];
		$widget_description = $parametersInput['widget_description'];
		$dashboard_list = implode(",",$parametersInput['dashboard_list']);
		$user_list =  implode(",",$parametersInput['user_list']);
		$status = $parametersInput['status'];
		$widgetSaveObj = new WidgetCreation();
		$widgetSaveObj->widget_name = $widget_name;
		$widgetSaveObj->widget_description = $widget_description;
		$widgetSaveObj->dashboard_list = $dashboard_list;
		$widgetSaveObj->user_list = $user_list;
		$widgetSaveObj->status = $status;
		$widgetSaveObj->widget_layout = 1;
		$widgetSaveObj->save();
		$request->session()->flash('message','Widget Added Successfully.');
        return redirect('widgetCreation');
	}
	
	
	public function updateWidgetPost(Request $request)
	{
		$parametersInput = $request->input();
	
		$widget_name = $parametersInput['widget_name'];
		$wid = $parametersInput['wid'];
		$widget_description = $parametersInput['widget_description'];
		$dashboard_list = implode(",",$parametersInput['dashboard_list']);
		$user_list =  implode(",",$parametersInput['user_list']);
		$status = $parametersInput['status'];
		$widgetUpdateObj = WidgetCreation::find($wid);
		$widgetUpdateObj->widget_name = $widget_name;
		$widgetUpdateObj->widget_description = $widget_description;
		$widgetUpdateObj->dashboard_list = $dashboard_list;
		$widgetUpdateObj->user_list = $user_list;
		$widgetUpdateObj->status = $status;
		/* $widgetUpdateObj->widget_layout = 1; */
		$widgetUpdateObj->save();
		$request->session()->flash('message','Widget Updated Successfully.');
        return redirect('widgetCreation');
	}
	public static function getListDashboard($dashboardList)
	{
		$dashboardListArray = explode(",",$dashboardList);
		$dashboardModel = DashboardCreation::whereIn("id",$dashboardListArray)->get();
	    $dashboardName = '';
		foreach($dashboardModel as $dash)
		{
			if($dashboardName == '')
			{
				$dashboardName = $dash->d_name;
			}
			else
			{
			 $dashboardName =  $dashboardName.','.$dash->d_name;
			}
		}
		return $dashboardName;
	}
	
	
	public static function getUserDashboard($userList)
	{
		$userListArray = explode(",",$userList);
		$userModel = Employee::whereIn("id",$userListArray)->get();
	    $userName = '';
		foreach($userModel as $user)
		{
			if($userName == '')
			{
				$userName = $user->fullname;
			}
			else
			{
			 $userName =  $userName.','.$user->fullname;
			}
		}
		return $userName;
	}
	
	public function widgetLayout(Request $request)
	{
		$wid =  $request->wid;
		$widgetData = WidgetCreation::where("id",$wid)->first();
		$widgetLayoutDetails = WidgetLayout::where("status",1)->get();
		return view("dashboard/widgetLayout",compact('widgetData','widgetLayoutDetails'));
	}
	
	public function widgetLayoutEdit(Request $request)
	{
		$wid =  $request->wid;
		$widgetData = WidgetCreation::where("id",$wid)->first();
		$widgetLayoutDetails = WidgetLayout::where("status",1)->get();
		if($widgetData->widget_layout_id == 1)
		{
		$widgetLeaderShipDetails = WidgetLeadershipDetails::where("widget_id",$wid)->first();
		}
		else if($widgetData->widget_layout_id == 2)
		{
		$widgetLeaderShipDetails = WidgetOnboardingHiring::where("widget_id",$wid)->first();
		}
		else if($widgetData->widget_layout_id == 3)
		{
		$widgetLeaderShipDetails = WidgetBarShortlisted::where("widget_id",$wid)->first();
		}
		else if($widgetData->widget_layout_id == 4)
		{
		$widgetLeaderShipDetails = WidgetBarMol::where("widget_id",$wid)->first();
		}
		else if($widgetData->widget_layout_id == 5)
		{
		$widgetLeaderShipDetails = WidgetBarOnboarded::where("widget_id",$wid)->first();
		}
		else if($widgetData->widget_layout_id == 35)
		{
		$widgetLeaderShipDetails = WidgetBarEvisa::where("widget_id",$wid)->first();
		}
		else 
		{
		$widgetLeaderShipDetails = '';
		}
		return view("dashboard/widgetLayoutEdit",compact('widgetData','widgetLayoutDetails','widgetLeaderShipDetails'));
	}
	
	public function reloadmeBarShortlistedJobopning(Request $request){
		$wid=$request->wid;
		$deptId=$request->deptId;
		$deptIdarray=explode(",",$deptId);
		$widgetData = WidgetBarShortlisted::where("widget_id",$wid)->first();
		if($request->session()->get('widgetFilterHiring['.$wid.'][job_opening]') != '' && $request->session()->get('widgetFilterHiring['.$wid.'][job_opening]') != NULL)	
		{
			$jobOpeningArray = $request->session()->get('widgetFilterHiring['.$wid.'][job_opening]');
		}
		else
		{
			$jobOpeningArray = explode(",",$widgetData->job_opening);
		}
		if($deptId==0){
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->get();
			
		}else{
			if(in_array(1,$deptIdarray)){
				
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->get();
			}
			else{
				
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->whereIn("department",$deptIdarray)->get();	
			}
		}
		return view("dashboard/widget/jobdata",compact('jobOpeningLists','wid','jobOpeningArray','deptId'));
	}
	public function reloadmeonboardShortlistedJobopning(Request $request){
		$wid=$request->wid;
		$deptId=$request->deptId;
		$deptIdarray=explode(",",$deptId);
		//$widgetData = WidgetBarShortlisted::where("widget_id",$wid)->first();
		$widgetData = WidgetBarOnboarded::where("widget_id",$wid)->first();
	
	   if($request->session()->get('widgetFilterHiring['.$wid.'][job_opening]') != '' && $request->session()->get('widgetFilterHiring['.$wid.'][job_opening]') != NULL)	
		{
			$jobOpeningArray = $request->session()->get('widgetFilterHiring['.$wid.'][job_opening]');
		}
		else
		{
			$jobOpeningArray = explode(",",$widgetData->job_opening);
		}
		if($deptId==0){
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->get();
			
		}else{
			if(in_array(1,$deptIdarray)){
				
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->get();
			}
			else{
				
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->whereIn("department",$deptIdarray)->get();	
			}
		}
		return view("dashboard/widget/jobdata",compact('jobOpeningLists','wid','jobOpeningArray','deptId'));
	}
	public function reloadmemolShortlistedJobopning(Request $request){
		$wid=$request->wid;
		$deptId=$request->deptId;
		$deptIdarray=explode(",",$deptId);
		//$widgetData = WidgetBarShortlisted::where("widget_id",$wid)->first();
		$widgetData = WidgetBarMol::where("widget_id",$wid)->first();
	  
	   if($request->session()->get('widgetFilterHiring['.$wid.'][job_opening]') != '' && $request->session()->get('widgetFilterHiring['.$wid.'][job_opening]') != NULL)	
		{
			$jobOpeningArray = $request->session()->get('widgetFilterHiring['.$wid.'][job_opening]');
		}
		else
		{
			$jobOpeningArray = explode(",",$widgetData->job_opening);
		}
		if($deptId==0){
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->get();
			
		}else{
			if(in_array(1,$deptIdarray)){
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->get();
			}
			else{
			if(in_array(1,$deptIdarray)){
				
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->get();
			}
			else{
				
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->whereIn("department",$deptIdarray)->get();	
			}	
			}
		}
		return view("dashboard/widget/jobdata",compact('jobOpeningLists','wid','jobOpeningArray','deptId'));
	}
	public function reloadmemolShortlistedJobopningEvisa(Request $request){
		$wid=$request->wid;
		$deptId=$request->deptId;
		$deptIdarray=explode(",",$deptId);
		//$widgetData = WidgetBarShortlisted::where("widget_id",$wid)->first();
		$widgetData = WidgetBarEvisa::where("widget_id",$wid)->first();
	  
	   if($request->session()->get('widgetFilterHiring['.$wid.'][job_opening]') != '' && $request->session()->get('widgetFilterHiring['.$wid.'][job_opening]') != NULL)	
		{
			$jobOpeningArray = $request->session()->get('widgetFilterHiring['.$wid.'][job_opening]');
		}
		else
		{
			$jobOpeningArray = explode(",",$widgetData->job_opening);
		}
		if($deptId==0){
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->get();
			
		}else{
			if(in_array(1,$deptIdarray)){
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->get();
			}
			else{
			if(in_array(1,$deptIdarray)){
				
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->get();
			}
			else{
				
			$jobOpeningLists = JobOpening::where("status",1)->where("job_function",2)->whereIn("department",$deptIdarray)->get();	
			}	
			}
		}
		return view("dashboard/widget/jobdata",compact('jobOpeningLists','wid','jobOpeningArray','deptId'));
	}
	
}