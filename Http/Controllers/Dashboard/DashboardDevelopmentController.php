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
use App\Models\Dashboard\DashboardParentMenu;
use App\Models\Entry\Employee;
use App\Models\Dashboard\WidgetCreation;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use App\Models\Employee_Leaves\RequestedLeaves;
use App\Models\Employee_Leaves\LeaveTypes;
use App\Models\Employee_Attendance\HolidayList;
use App\Models\Employee_Attendance\EmpAttendance;
use App\Models\Company\Department;
use App\Models\JobFunction\JobFunction;
use App\Models\Dashboard\WidgetCreationHome;
use App\Models\SearchEngineWidget\SearchResultWidget;





class DashboardDevelopmentController extends Controller
{
  
	public function DashboardCreation()
	{
		$dashboardList = DashboardCreation::orderBy("id","DESC")->get();
		
		return view("dashboard/dashboard_creation",compact('dashboardList'));
	}
	
	public function  addDashboard()
	{
		$listSubMenu = DashboardParentMenu::where("parent_status",1)->get();
		$userList = Employee::where("status",1)->get();
		return view("dashboard/addDashboard",compact('listSubMenu','userList'));
	}
	public function editDashboard(Request $request)
	{
		$did = $request->did;
		/* echo $did;
		exit; */
		$dashboardC = DashboardCreation::where("id",$did)->first();
		$listSubMenu = DashboardParentMenu::where("parent_status",1)->get();
			$userList = Employee::where("status",1)->get();
		return view("dashboard/editDashboard",compact('dashboardC','listSubMenu','userList'));
	}
	public function menuSetting()
	{
		$dashboardParentMenuList = DashboardParentMenu::where("parent_status",2)->get();	
		$pid ='';
		return view("dashboard/menuSetting",compact('dashboardParentMenuList','pid'));
	}
	public function menuSettingUpdate(Request $request)
	{
		$pid = $request->pid;
		$dashboardParentMenuList = DashboardParentMenu::where("parent_status",2)->get();	
		return view("dashboard/menuSetting",compact('dashboardParentMenuList','pid'));
	}
	
	
	
	public function addDashboardPost(Request $request)
	{
		$requestInput =  $request->input();
	/* 	echo "<pre>";
		print_r($requestInput);
		exit; */
		$d_name = $requestInput['d_name'];
		$user_list_dashboard = implode(",",$requestInput['user_list_dashboard']);
		$menu_type = $requestInput['menu_type'];
		$sub_menu_id = $requestInput['sub_menu_id'];
		
		$menu_name = $requestInput['menu_name'];
		$status = $requestInput['status'];
		/*
		*Creating Parent Menu id
		*/
		if($menu_type == 2)
		{
			if(isset($requestInput['parentMenuId']) && $requestInput['parentMenuId'] != '' && $requestInput['parentMenuId'] == 'new')
			{
			$parentMenuId = $requestInput['parentMenuId'];
			$parent_menu_name = $requestInput['parent_menu_name'];
			$parentMenuObj = new  DashboardParentMenu();
			$parentMenuObj->name = $requestInput['parent_menu_name'];
			$parentMenuObj->parent_status = 2;
			$parentMenuObj->status = 1;
			$parentMenuObj->save();
			$parentMenuId = $parentMenuObj->id;
			}
		}
		if($sub_menu_id == 'newSubMenu')
		{
			$user_list_menu = implode(",",$requestInput['user_list_menu']);
			
			$parentMenuObj = new  DashboardParentMenu();
			$parentMenuObj->name = $menu_name;
			$parentMenuObj->parent_status = 1;
			$parentMenuObj->user_list_menu = $user_list_menu;
			$parentMenuObj->status = 1;
			$parentMenuObj->save();
			$subMenuId = $parentMenuObj->id;
			
			/*
		*Creating Parent Menu id
		*/
		$dashboardCreationSave = new DashboardCreation();
		$dashboardCreationSave->d_name = $d_name;
		$dashboardCreationSave->sub_menu_id = $subMenuId;
		$dashboardCreationSave->menu_type = $menu_type;
		if($menu_type == 2)
		{
		if($requestInput['parentMenuId'] == 'new')
			{
				$dashboardCreationSave->parent_menu_id = $parentMenuId;
			}
			else
			{
				$dashboardCreationSave->parent_menu_id = $requestInput['parentMenuId'];
			}
		}
		$dashboardCreationSave->status = $status;
		$dashboardCreationSave->sub_menu_status = 1;
		$dashboardCreationSave->Parent_menu_status = 1;
		$dashboardCreationSave->user_list_dashboard = $user_list_dashboard;
		$dashboardCreationSave->user_list_menu = $user_list_menu;
		$dashboardCreationSave->save();
		}
		else
		{
			$dashData = DashboardCreation::where("sub_menu_id",$sub_menu_id)->first();
			$subMenuId =$sub_menu_id;
			/*
		*Creating Parent Menu id
		*/
		$dashboardCreationSave = new DashboardCreation();
		$dashboardCreationSave->d_name = $d_name;
		$dashboardCreationSave->sub_menu_id = $subMenuId;
		$dashboardCreationSave->menu_type = $dashData->menu_type;
		$dashboardCreationSave->parent_menu_id = $dashData->parent_menu_id;
		
		$dashboardCreationSave->status = $status;
	
		$dashboardCreationSave->sub_menu_status = 1;
		$dashboardCreationSave->Parent_menu_status = 1;
		$dashboardCreationSave->user_list_dashboard = $user_list_dashboard;
		$dashboardCreationSave->user_list_menu = $dashData->user_list_menu;
		$dashboardCreationSave->save();
		}
		
		
		
		 $request->session()->flash('message','Dashboard Added Successfully.');
        return redirect('DashboardCreation');
	}
	
	
	public function updateDashboardPost(Request $request)
	{
		$requestInput =  $request->input();
		/* echo "<pre>";
		print_r($requestInput);
		exit; */ 
		$d_name = $requestInput['d_name'];
		$user_list_dashboard = implode(",",$requestInput['user_list_dashboard']);
		$user_list_menu = implode(",",$requestInput['user_list_menu']);
		$menu_type = $requestInput['menu_type'];
		$dashboardID = $requestInput['dashboardID'];
		$sub_menu_id = $requestInput['sub_menu_id'];
		$menu_name = $requestInput['menu_name'];
		$status = $requestInput['status'];
		//echo $requestInput['parent_menu_name'];exit;
		/*
		*Creating Parent Menu id
		*/
		if($menu_type == 2)
		{
			if(isset($requestInput['parentMenuId']) && $requestInput['parentMenuId'] != '' && $requestInput['parentMenuId'] == 'new')
			{
			$parentMenuId = $requestInput['parentMenuId'];
			$parent_menu_name = $requestInput['parent_menu_name'];
			$parentMenuObj = new  DashboardParentMenu();
			$parentMenuObj->name = $requestInput['parent_menu_name'];
			$parentMenuObj->parent_status = 2;
			$parentMenuObj->save();
			$parentMenuId = $parentMenuObj->id;
			}
		}
		if($sub_menu_id == 'newSubMenu')
		{
			
			$parentMenuObj = new  DashboardParentMenu();
			$parentMenuObj->name = $menu_name;
			$parentMenuObj->user_list_menu = $user_list_menu;
			$parentMenuObj->parent_status = 1;
			$parentMenuObj->save();
			$subMenuId = $parentMenuObj->id;
		}
		else
		{
			$subMenuId =$sub_menu_id;
		}
		
		/*
		*Creating Parent Menu id
		*/
		$dashboardCreationSave = DashboardCreation::find($dashboardID);
		$dashboardCreationSave->d_name = $d_name;
		$dashboardCreationSave->sub_menu_id = $subMenuId;
		$dashboardCreationSave->menu_type = $menu_type;
		if($menu_type == 2)
		{
			if($requestInput['parentMenuId'] == 'new')
			{
				$dashboardCreationSave->parent_menu_id = $parentMenuId;
			}
			else
			{
				$dashboardCreationSave->parent_menu_id = $requestInput['parentMenuId'];
			}
		}
		$dashboardCreationSave->status = $status;
		$dashboardCreationSave->user_list_dashboard = $user_list_dashboard;
		$dashboardCreationSave->user_list_menu = $user_list_menu;
		/*
		*Menu Update
		*/
		$updateMenu = DashboardParentMenu::find($subMenuId);
		$updateMenu->user_list_menu = $user_list_menu;
		$updateMenu->save();
		/*
		*Menu Update
		*/
		$dashboardCreationSave->save();
		 $request->session()->flash('message','Dashboard Added Successfully.');
        return redirect('DashboardCreation');
	}
	
	public static function getparentMenuName($parent_menu_id)
	{
		if($parent_menu_id != NULL && $parent_menu_id != '')
		{
			return DashboardParentMenu::where("id",$parent_menu_id)->first()->name;
			
		}
		else
		{
			return "None";
		}
	}
	
	public static function getMenuName($menuID)
	{
		if($menuID != NULL && $menuID != '')
		{
			$data=DashboardParentMenu::where("id",$menuID)->first();
			if($data!=''){
				return $data->name;
			}
			else{
				return "None";
			}
		}
		else
		{
			return "None";
		}
	}
	
	
	public function parentdashboard(Request $request)
	{
		$dashboardCreationData = DashboardCreation::where("status",1)->get();
		$empSessionId = $request->session()->get('EmployeeId');
		$users=Employee::where("id",$empSessionId)->first();
		if($users!=''){
		$empid=$users->employee_id;
		$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
		}
		else{
			$empRequiredDetails ='';
		}
		$menuParent = array(); 
		$menuSub = array(); 
		$parentIndex =0;
		$parentManuCreated = array();
		foreach($dashboardCreationData as $_dash)
		{
			if($_dash->parent_menu_id == NULL || $_dash->parent_menu_id == '')
			{
				if($_dash->user_list_menu != NULL && $_dash->user_list_menu != '')
				{
					if($_dash->sub_menu_status == 1 && $_dash->Parent_menu_status == 1)
					{
						$allowUsers = $_dash->user_list_menu;
						$allowUsersArr = explode(",",$allowUsers);
						if(in_array($empSessionId,$allowUsersArr))
						{
							if(!in_array($_dash->sub_menu_id,$parentManuCreated))
								{
								$menuParent[$parentIndex]['parent']['name'] = DashboardDevelopmentController::getMenuName($_dash->sub_menu_id);
								$menuParent[$parentIndex]['parent']['id'] = $_dash->sub_menu_id;
								$menuParent[$parentIndex]['parent']['user_list_menu'] = $_dash->user_list_menu;
								$parentManuCreated[] = $_dash->sub_menu_id;
								}
								$parentIndex++;
						}
					}
				}
			}
			else
			{
				if($_dash->user_list_menu != NULL && $_dash->user_list_menu != '')
				{
					if($_dash->sub_menu_status == 1 && $_dash->Parent_menu_status == 1)
					{
						$allowUsers = $_dash->user_list_menu;
						$allowUsersArr = explode(",",$allowUsers);
						if(in_array($empSessionId,$allowUsersArr))
						{
								$menuSub[$_dash->parent_menu_id][$_dash->sub_menu_id]['child']['name'] = DashboardDevelopmentController::getMenuName($_dash->sub_menu_id);
								$menuSub[$_dash->parent_menu_id][$_dash->sub_menu_id]['child']['id'] = $_dash->sub_menu_id;
								$menuSub[$_dash->parent_menu_id][$_dash->sub_menu_id]['child']['user_list_menu'] = $_dash->user_list_menu;
						}
					}
				}
			}
		}
	   /*  echo "<pre>";
		print_r($menuParent);
		exit; */
		/*
		*get all active User
		*/
		$userDetails = Employee::where("status",1)->whereNotNull("employee_id")->get();
		
		$switchUsersList = array();
		foreach($userDetails as $userD)
		{
			$empData = Employee_details::where("emp_id",$userD->employee_id)->first();
			if($empData != '')
			{
				$switchUsersList[$userD->id] = $userD->username.'-('.$this->deptName($empData->dept_id).')-('.$this->jobFunc($empData->job_function).')';
			}
		}
	
		/*
		*get all active User
		*/
		
		return view("dashboard/parentdashboard",compact('dashboardCreationData','menuParent','menuSub','empRequiredDetails','switchUsersList'));

		//return view("dashboard/newHomePage",compact('dashboardCreationData','menuParent','menuSub','empRequiredDetails'));


		
	}


protected function deptName($deptId)
{
	$dataDepartment = Department::where("id",$deptId)->first();
	if($dataDepartment != '')
	{
		return $dataDepartment->department_name;
	}
	else
	{
		return 'not found';
	}
}

protected function jobFunc($jobId)
{
	$dataJob = JobFunction::where("id",$jobId)->first();
	if($dataJob != '')
	{
		return $dataJob->name;
	}
	else
	{
		return 'not found';
	}
		
}
// new code for dashboard start


public function parentdashboardNew(Request $request)
	{
		$dashboardCreationData = DashboardCreation::where("status",1)->get();
		$empSessionId = $request->session()->get('EmployeeId');
		$users=Employee::where("id",$empSessionId)->first();
		if($users!=''){
		$empid=$users->employee_id;
		$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
		}
		else{
			$empRequiredDetails ='';
		}
		$menuParent = array(); 
		$menuSub = array(); 
		$parentIndex =0;
		$parentManuCreated = array();
		foreach($dashboardCreationData as $_dash)
		{
			if($_dash->parent_menu_id == NULL || $_dash->parent_menu_id == '')
			{
				if($_dash->user_list_menu != NULL && $_dash->user_list_menu != '')
				{
					if($_dash->sub_menu_status == 1 && $_dash->Parent_menu_status == 1)
					{
						$allowUsers = $_dash->user_list_menu;
						$allowUsersArr = explode(",",$allowUsers);
						if(in_array($empSessionId,$allowUsersArr))
						{
							if(!in_array($_dash->sub_menu_id,$parentManuCreated))
								{
								$menuParent[$parentIndex]['parent']['name'] = DashboardDevelopmentController::getMenuName($_dash->sub_menu_id);
								$menuParent[$parentIndex]['parent']['id'] = $_dash->sub_menu_id;
								$menuParent[$parentIndex]['parent']['user_list_menu'] = $_dash->user_list_menu;
								$parentManuCreated[] = $_dash->sub_menu_id;
								}
								$parentIndex++;
						}
					}
				}
			}
			else
			{
				if($_dash->user_list_menu != NULL && $_dash->user_list_menu != '')
				{
					if($_dash->sub_menu_status == 1 && $_dash->Parent_menu_status == 1)
					{
						$allowUsers = $_dash->user_list_menu;
						$allowUsersArr = explode(",",$allowUsers);
						if(in_array($empSessionId,$allowUsersArr))
						{
								$menuSub[$_dash->parent_menu_id][$_dash->sub_menu_id]['child']['name'] = DashboardDevelopmentController::getMenuName($_dash->sub_menu_id);
								$menuSub[$_dash->parent_menu_id][$_dash->sub_menu_id]['child']['id'] = $_dash->sub_menu_id;
								$menuSub[$_dash->parent_menu_id][$_dash->sub_menu_id]['child']['user_list_menu'] = $_dash->user_list_menu;
						}
					}
				}
			}
		}
	   /*  echo "<pre>";
		print_r($menuParent);
		exit; */
		//return view("dashboard/parentdashboard",compact('dashboardCreationData','menuParent','menuSub','empRequiredDetails'));

		return view("dashboard/newHomePage",compact('dashboardCreationData','menuParent','menuSub','empRequiredDetails'));


		
	}








	public function dashboardLoadingFucNew(Request $request)
	{
		//return $request->all();
		// return 123;
		
		$empSessionId = $request->session()->get('EmployeeId');
		$users=Employee::where("id",$empSessionId)->first();
		if($users!=''){
		$empid=$users->employee_id;
		$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
		}
		else{
			$empRequiredDetails ='';
		}
		$mid =  $request->mid;
		
		$listData = DashboardCreation::where("sub_menu_id",$mid)->where("status",1)->get();
		$listDash = array();
		foreach($listData as $list)
		{
			$allowUsers = $list->user_list_dashboard;
			$allowUsersArr = explode(",",$allowUsers);
			if(in_array($empSessionId,$allowUsersArr))
				{
					$listDash[] = $list;
				}
			
		}


		$empCount = Employee_details::where('offline_status',1)->orderBy('id','desc')->get()->count();


		$todayDate = date('Y-m-d');
		$empAttendanceData = EmpAttendance::where('attribute_code','attendance')->where('attribute_value','P')->where('attendance_date',$todayDate)->orderBy('id','desc')->get()->count();

		if($empAttendanceData)
		{
			$empAttendance = $empAttendanceData;
		}
		else
		{
			$empAttendance=0;
		}

		$tDate = date('Y-m-d');
		$requestedLeaves = RequestedLeaves::where('final_status',1)->get();
		$newResult=array();
		foreach($requestedLeaves as $value)
		{
			if($value->updated_from_date==NULL && $value->updated_to_date==NULL)
			{
				if($value->from_date <= $tDate && $value->to_date >= $tDate)
				{
					$newResult[]=$value->id;
				}                       
			}
			else
			{
				if($value->updated_from_date <= $tDate && $value->updated_to_date >= $tDate)
				{
					$newResult[]=$value->id;
				}
			}
		}


		$empLeavesCount = RequestedLeaves::whereIn('leaves_request.id',$newResult)->get()->count();


		//return $listDash;


		//return view("dashboard/dashboardLoadingFucNew",compact('listDash','empRequiredDetails','empCount','empAttendance','empLeavesCount'));
		//return view("dashboard/newHomePage",compact('listDash','empRequiredDetails'));
		return view("dashboard/dashboardLoadingFucNew",compact('listDash','empRequiredDetails'));
	}



// new code for dashboard End











	
	public function removeDashboard(Request $request)
	{
		$did =  $request->did;
		$updateDash = DashboardCreation::find($did);
		$updateDash->delete();
		 $request->session()->flash('message','Dashboard Deleted Successfully.');
        return redirect('DashboardCreation');
	}
	
	public function dashboardLoadingFuc(Request $request)
	{
		//return $request->all();
		// return 123;
		
		$empSessionId = $request->session()->get('EmployeeId');
		$users=Employee::where("id",$empSessionId)->first();
		if($users!=''){
		$empid=$users->employee_id;
		$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
		}
		else{
			$empRequiredDetails ='';
		}
		$mid =  $request->mid;
		$listData = DashboardCreation::where("sub_menu_id",$mid)->where("status",1)->get();
		$listDash = array();
		foreach($listData as $list)
		{
			$allowUsers = $list->user_list_dashboard;
			$allowUsersArr = explode(",",$allowUsers);
			if(in_array($empSessionId,$allowUsersArr))
				{
					$listDash[] = $list;
				}
			
		}


		$empCount = Employee_details::where('offline_status',1)->orderBy('id','desc')->get()->count();


		$todayDate = date('Y-m-d');
		$empAttendanceData = EmpAttendance::where('attribute_code','attendance')->where('attribute_value','P')->where('attendance_date',$todayDate)->orderBy('id','desc')->get()->count();

		if($empAttendanceData)
		{
			$empAttendance = $empAttendanceData;
		}
		else
		{
			$empAttendance=0;
		}

		$tDate = date('Y-m-d');
		$requestedLeaves = RequestedLeaves::where('final_status',1)->get();
		$newResult=array();
		foreach($requestedLeaves as $value)
		{
			if($value->updated_from_date==NULL && $value->updated_to_date==NULL)
			{
				if($value->from_date <= $tDate && $value->to_date >= $tDate)
				{
					$newResult[]=$value->id;
				}                       
			}
			else
			{
				if($value->updated_from_date <= $tDate && $value->updated_to_date >= $tDate)
				{
					$newResult[]=$value->id;
				}
			}
		}


		$empLeavesCount = RequestedLeaves::whereIn('leaves_request.id',$newResult)->get()->count();


		//return view("dashboard/dashboardLoadingFucNew",compact('listDash','empRequiredDetails','empCount','empAttendance','empLeavesCount'));
		//return view("dashboard/newHomePage",compact('listDash','empRequiredDetails'));
		return view("dashboard/dashboardLoadingFuc",compact('listDash','empRequiredDetails'));
	}
	
	public function updateMenuDashboard(Request $request)
	{
		$menuId = $request->menuId;
		$menuType = $request->menuType;
		$menuData = DashboardParentMenu::where('id',$menuId)->first();
		return view("dashboard/updateMenuDashboard",compact('menuId','menuType','menuData'));
	}
	
	public function updateMenuContent(Request $request)
	{
		$parametersInput = $request->input();
		
		$menu_id = $parametersInput['menu_id'];
		$name = $parametersInput['name'];
		$status = $parametersInput['status'];
		$menuType = $parametersInput['menuType'];
		$dashModelUpdate = DashboardParentMenu::find($menu_id);
		$dashModelUpdate->name = $name;
		$dashModelUpdate->status = $status;
		if($dashModelUpdate->save())
		{
			if($menuType == 'menu')	
			{
				$allData = DashboardCreation::where("sub_menu_id",$menu_id)->get();
				foreach($allData as $data)
				{
					$updateMod = DashboardCreation::find($data->id);
					$updateMod->sub_menu_status = $status;
					$updateMod->save();
				}
			}
			else
			{
				$allData = DashboardCreation::where("parent_menu_id",$menu_id)->get();
				foreach($allData as $data)
				{
					$updateMod = DashboardCreation::find($data->id);
					$updateMod->Parent_menu_status = $status;
					$updateMod->save();
				}
			}
		}
		
		$request->session()->flash('message','Menu Updated Successfully.');
        return redirect('DashboardCreation');
	}
	public function widgetLoadOnDashboard(Request $request)
	{
		$empSessionId = $request->session()->get('EmployeeId');
		$dashId = $request->dashId;
		
		/*
		*get widget list as per dashboard
		*start code
		*/
		$widgetList = WidgetCreation::whereRaw('FIND_IN_SET('.$dashId.', dashboard_list)')->where("widget_layout",2)->where("status",1)->get();
	
		$widgetDashboardDetails = array(); 
		$index = 0;
		$dataType = array();
		$from_salesTime = array();
		$to_salesTime = array();
		foreach($widgetList as $widgetData)
		{
			

					$allowUsers = $widgetData->user_list;
						$allowUsersArr = explode(",",$allowUsers);
						if(in_array($empSessionId,$allowUsersArr))
						{
						$widgetDashboardDetails[$index]['layout'] =  $widgetData->widget_layout_id;
						$widgetDashboardDetails[$index]['wid'] =  $widgetData->id;
						$widgetDashboardDetails[$index]['name'] =  $widgetData->widget_name;
						$widgetDashboardDetails[$index]['user_list'] =  $widgetData->user_list;
						
						
						
						if($request->session()->get('widgetFilter['.$widgetData->id.'][data_type]') != '')
						{
							$dataType[$widgetData->id] =  $request->session()->get('widgetFilter['.$widgetData->id.'][data_type]');
							
						}
						else
						{
							$dataType[$widgetData->id] = 'current_month';
						}
						
						if($request->session()->get('widgetFilter['.$widgetData->id.'][from_salesTime]') != '')
						{
							$from_salesTime[$widgetData->id] =  $request->session()->get('widgetFilter['.$widgetData->id.'][from_salesTime]');
							
						}
						else
						{
							$from_salesTime[$widgetData->id] = '';
						}
						
						if($request->session()->get('widgetFilter['.$widgetData->id.'][to_salesTime]') != '')
						{
							$to_salesTime[$widgetData->id] =  $request->session()->get('widgetFilter['.$widgetData->id.'][to_salesTime]');
							
						}
						else
						{
							$to_salesTime[$widgetData->id] = '';
						}
					
						$index++;
						}
		}
		/*
		*get widget list as per dashboard
		*end code
		*/
			/* echo "<pre>";
		print_r($widgetDashboardDetails);
		exit; */
		/* echo "<pre>";
		print_r($dataType);
		exit; */
		return view("dashboard/widget/widgetLoadOnDashboard",compact('widgetDashboardDetails','dataType','from_salesTime','to_salesTime'));
	}
	
	public function widgetLoadOnDashboardWithFilter(Request $request)
	{
		
		  $selectedFilter = $request->input();
		$widget_id = $selectedFilter['widget_id'];
		foreach($selectedFilter as $key => $filter)
		{
			$request->session()->put('widgetFilter['.$widget_id.']['.$key.']',$filter);	
			
		}		
		$session = $request->session();
		/* echo "<pre>";
		print_r($session);
		exit; */
			return redirect('widgetLoadOnDashboard/'.$widget_id);
	}
	
	public function resetLeaderFilter(Request $request)
	{
		$widget_id =  $request->wid;
		$request->session()->put('widgetFilter['.$widget_id.'][data_type]','');	
		$request->session()->put('widgetFilter['.$widget_id.'][from_salesTime]','');	
		$request->session()->put('widgetFilter['.$widget_id.'][to_salesTime]','');	
		return redirect('widgetLoadOnDashboard/'.$widget_id);
	}
	
	public function updateWidgetDataLeaderShip(Request $request)
	{
		$wId = $request->wId;
		$data_type = $request->data_type;
		$salesTimeFrom = $request->salesTimeFrom;
		$salesTimeTo = $request->salesTimeTo;
		return view("dashboard/widget/leaderShip/updateWidgetDataLeaderShip",compact('wId','data_type','salesTimeFrom','salesTimeTo'));
	}








	// NeW home page design start

	public function newHomeDashboard(Request $request)
	{
		$dashboardCreationData = DashboardCreation::where("status",1)->get();
		$empSessionId = $request->session()->get('EmployeeId');
		$users=Employee::where("id",$empSessionId)->first();
		if($users!=''){
		$empid=$users->employee_id;
		$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
		$empCount = Employee_details::where('offline_status',1)->orderBy('id','desc')->get()->count();
		}
		else{
			$empRequiredDetails ='';
		}
		$todayDate = date('Y-m-d');
		$empAttendanceData = EmpAttendance::where('attribute_code','attendance')->where('attribute_value','P')->where('attendance_date',$todayDate)->orderBy('id','desc')->get()->count();

		if($empAttendanceData)
		{
			$empAttendance = $empAttendanceData;
		}
		else
		{
			$empAttendance=0;
		}





		$menuParent = array(); 
		$menuSub = array(); 
		$parentIndex =0;
		$parentManuCreated = array();
		foreach($dashboardCreationData as $_dash)
		{
			if($_dash->parent_menu_id == NULL || $_dash->parent_menu_id == '')
			{
				if($_dash->user_list_menu != NULL && $_dash->user_list_menu != '')
				{
					if($_dash->sub_menu_status == 1 && $_dash->Parent_menu_status == 1)
					{
						$allowUsers = $_dash->user_list_menu;
						$allowUsersArr = explode(",",$allowUsers);
						if(in_array($empSessionId,$allowUsersArr))
						{
							if(!in_array($_dash->sub_menu_id,$parentManuCreated))
								{
								$menuParent[$parentIndex]['parent']['name'] = DashboardDevelopmentController::getMenuName($_dash->sub_menu_id);
								$menuParent[$parentIndex]['parent']['id'] = $_dash->sub_menu_id;
								$menuParent[$parentIndex]['parent']['user_list_menu'] = $_dash->user_list_menu;
								$parentManuCreated[] = $_dash->sub_menu_id;
								}
								$parentIndex++;
						}
					}
				}
			}
			else
			{
				if($_dash->user_list_menu != NULL && $_dash->user_list_menu != '')
				{
					if($_dash->sub_menu_status == 1 && $_dash->Parent_menu_status == 1)
					{
						$allowUsers = $_dash->user_list_menu;
						$allowUsersArr = explode(",",$allowUsers);
						if(in_array($empSessionId,$allowUsersArr))
						{
								$menuSub[$_dash->parent_menu_id][$_dash->sub_menu_id]['child']['name'] = DashboardDevelopmentController::getMenuName($_dash->sub_menu_id);
								$menuSub[$_dash->parent_menu_id][$_dash->sub_menu_id]['child']['id'] = $_dash->sub_menu_id;
								$menuSub[$_dash->parent_menu_id][$_dash->sub_menu_id]['child']['user_list_menu'] = $_dash->user_list_menu;
						}
					}
				}
			}
		}






		$tDate = date('Y-m-d');
		$requestedLeaves = RequestedLeaves::where('final_status',1)->get();
		$newResult=array();
		foreach($requestedLeaves as $value)
		{
			if($value->updated_from_date==NULL && $value->updated_to_date==NULL)
			{
				if($value->from_date <= $tDate && $value->to_date >= $tDate)
				{
					$newResult[]=$value->id;
				}                       
			}
			else
			{
				if($value->updated_from_date <= $tDate && $value->updated_to_date >= $tDate)
				{
					$newResult[]=$value->id;
				}
			}
		}


		$empLeavesCount = RequestedLeaves::whereIn('leaves_request.id',$newResult)->get()->count();



		







	   /*  echo "<pre>";
		print_r($menuParent);
		exit; */
		return view("dashboard/newHomePage46246323",compact('dashboardCreationData','menuParent','menuSub','empRequiredDetails','empCount','empAttendance','empLeavesCount'));
	}

	// New homepage design End



public function accountSwitch(Request $request)
{
	
			$request->session()->forget('EmployeeId');
			$request->session()->forget('EmployeeUsername');
	 $request->session()->put('EmployeeId',$request->actSwitch);
	 if($request->session()->get('ParentEmployeeId') == '')
	 {
			$request->session()->put('ParentEmployeeId',$request->loginId); 
	 }
	return redirect('/');
   
}
public function dashboardLoadingFucHome(Request $request)
	{
		//return $request->all();
		// return 123;
		
		$empSessionId = $request->session()->get('EmployeeId');
		$users=Employee::where("id",$empSessionId)->first();
		if($users!=''){
		$empid=$users->employee_id;
		$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
		}
		else{
			$empRequiredDetails ='';
		}
		$mid =  $request->mid;
		$listData = DashboardCreation::where("sub_menu_id",$mid)->where("status",1)->get();
		$listDash = array();
		foreach($listData as $list)
		{
			$allowUsers = $list->user_list_dashboard;
			$allowUsersArr = explode(",",$allowUsers);
			if(in_array($empSessionId,$allowUsersArr))
				{
					$listDash[] = $list;
				}
			
		}


		$empCount = Employee_details::where('offline_status',1)->orderBy('id','desc')->get()->count();


		$todayDate = date('Y-m-d');
		$empAttendanceData = EmpAttendance::where('attribute_code','attendance')->where('attribute_value','P')->where('attendance_date',$todayDate)->orderBy('id','desc')->get()->count();

		if($empAttendanceData)
		{
			$empAttendance = $empAttendanceData;
		}
		else
		{
			$empAttendance=0;
		}

		$tDate = date('Y-m-d');
		$requestedLeaves = RequestedLeaves::where('final_status',1)->get();
		$newResult=array();
		foreach($requestedLeaves as $value)
		{
			if($value->updated_from_date==NULL && $value->updated_to_date==NULL)
			{
				if($value->from_date <= $tDate && $value->to_date >= $tDate)
				{
					$newResult[]=$value->id;
				}                       
			}
			else
			{
				if($value->updated_from_date <= $tDate && $value->updated_to_date >= $tDate)
				{
					$newResult[]=$value->id;
				}
			}
		}


		$empLeavesCount = RequestedLeaves::whereIn('leaves_request.id',$newResult)->get()->count();


		
		return view("dashboard/dashboardLoadingFucHome",compact('listDash','empRequiredDetails'));
	}
	public function widgetLoadOnDashboardHome(Request $request)
	{
		$empSessionId = $request->session()->get('EmployeeId');
		$dashName = $request->dashName;
		
		/*
		*get widget list as per dashboard
		*start code
		*/
		
		$widgetListhome = WidgetCreationHome::where("widget_name",$dashName)->first();
		$homeexp=explode(",",$widgetListhome->widget_ids);
		//print_r($homeexp);exit;
		$widgetDashboardDetails = array(); 
		$index = 0;
		$dataType = array();
		$from_salesTime = array();
		$to_salesTime = array();
		foreach($homeexp as $ids){
		$widgetList = WidgetCreation::where('id',$ids)->where("widget_layout",2)->where("status",1)->get();
	
		
		foreach($widgetList as $widgetData)
		{
			

					$allowUsers = $widgetData->user_list;
						$allowUsersArr = explode(",",$allowUsers);
						if(in_array($empSessionId,$allowUsersArr))
						{
						$widgetDashboardDetails[$index]['layout'] =  $widgetData->widget_layout_id;
						$widgetDashboardDetails[$index]['wid'] =  $widgetData->id;
						$widgetDashboardDetails[$index]['name'] =  $widgetData->widget_name;
						$widgetDashboardDetails[$index]['user_list'] =  $widgetData->user_list;
						
						
						
						if($request->session()->get('widgetFilter['.$widgetData->id.'][data_type]') != '')
						{
							$dataType[$widgetData->id] =  $request->session()->get('widgetFilter['.$widgetData->id.'][data_type]');
							
						}
						else
						{
							$dataType[$widgetData->id] = 'current_month';
						}
						
						if($request->session()->get('widgetFilter['.$widgetData->id.'][from_salesTime]') != '')
						{
							$from_salesTime[$widgetData->id] =  $request->session()->get('widgetFilter['.$widgetData->id.'][from_salesTime]');
							
						}
						else
						{
							$from_salesTime[$widgetData->id] = '';
						}
						
						if($request->session()->get('widgetFilter['.$widgetData->id.'][to_salesTime]') != '')
						{
							$to_salesTime[$widgetData->id] =  $request->session()->get('widgetFilter['.$widgetData->id.'][to_salesTime]');
							
						}
						else
						{
							$to_salesTime[$widgetData->id] = '';
						}
					
						
						}
						$index++;
		}
		
	}
	$userList = Employee::where("id",$empSessionId)->first();
		if($userList!=''){
		$empid=$userList->employee_id;
		$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
		if($empRequiredDetails!=''){
			$empdpt=$empRequiredDetails->dept_id;
		}
		else{
			$empdpt='';
		}
		}
		/*
		*get widget list as per dashboard
		*end code
		
			 echo "<pre>";
		print_r($widgetDashboardDetails);
		exit; 
		/* echo "<pre>";
		print_r($dataType);
		exit; */
		//print_r($dataType);exit;
		//echo $empdpt;exit;
		$user=$request->session()->get('EmployeeId');
		$widgetlist=SearchResultWidget::where("user_id",$user)->where("tabname",$dashName)->get();
		return view("dashboard/widget/widgetLoadOnDashboardHome",compact('widgetlist','widgetDashboardDetails','dataType','from_salesTime','to_salesTime','dashName','empdpt'));
	}


	public function widgetLoadOnFileDashboardHome(Request $request)
	{

		//echo "hello";exit;
		$widgetid = $request->wid;
		$widgetlayout = $request->layout;
		$widgetdatatypeid = $request->datatypeid;
		
		
		return view("dashboard/widget/widgetLoadOnFileDashboard",compact('widgetid','widgetlayout','widgetdatatypeid'));
	}


	public function widgetLoadOnFileHomeDashboardHome(Request $request)
	{

		//echo "hello";exit;
		$widgetid = $request->wid;
		$widgetlayout = $request->layout;
		$widgetdatatypeid = $request->datatypeid;
		$widgetdashName = $request->dashName;
		$widgetempid = $request->deptid;
		
		
		$user=$request->session()->get('EmployeeId');
		$widgetlist=SearchResultWidget::where("user_id",$user)->where("tabname",$widgetdashName)->get();
		
		return view("dashboard/widget/widgetLoadOnFileDashboardHome",compact('widgetlist','widgetid','widgetlayout','widgetdatatypeid','widgetdashName','widgetempid'));
	}






	
	public static function getEMPValueDEPT($uid){
		$userList = Employee::where("id",$uid)->first();
		$data='';
		if($userList!=''){
		$empid=$userList->employee_id;
		$empRequiredDetails =  Employee_details::where('emp_id',$empid)->where('job_function',3)->first();
			if($empRequiredDetails!=''){
				if($empRequiredDetails->dept_id==36 || $empRequiredDetails->dept_id==47 || $empRequiredDetails->dept_id==49 || $empRequiredDetails->dept_id==52){
					$data= 1;
				}
				else{
					$data= 2;
				}
		
			}
			else{
					$data= 2;
				}
		}
		return $data;
		
	}
	public function SearchEngineWidgetTabs(Request $request){
		
		//$rulesList=SearchEngineRules::
		$user=$request->session()->get('EmployeeId');
		$widgetlist=SearchResultWidget::where("user_id",$user)->get();
		return view("SearchEngineWidget/SearchEngineWidget" ,compact('widgetlist'));
	   
	   
	}
	public function parentdashboardmashreq(Request $request)
	{
		$dashboardCreationData = DashboardCreation::where("status",1)->get();
		$empSessionId = $request->session()->get('EmployeeId');
		$users=Employee::where("id",$empSessionId)->first();
		if($users!=''){
		$empid=$users->employee_id;
		$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
		}
		else{
			$empRequiredDetails ='';
		}
		$menuParent = array(); 
		$menuSub = array(); 
		$parentIndex =0;
		$parentManuCreated = array();
		foreach($dashboardCreationData as $_dash)
		{
			if($_dash->parent_menu_id == NULL || $_dash->parent_menu_id == '')
			{
				if($_dash->user_list_menu != NULL && $_dash->user_list_menu != '')
				{
					if($_dash->sub_menu_status == 1 && $_dash->Parent_menu_status == 1)
					{
						$allowUsers = $_dash->user_list_menu;
						$allowUsersArr = explode(",",$allowUsers);
						if(in_array($empSessionId,$allowUsersArr))
						{
							if(!in_array($_dash->sub_menu_id,$parentManuCreated))
								{
								$menuParent[$parentIndex]['parent']['name'] = DashboardDevelopmentController::getMenuName($_dash->sub_menu_id);
								$menuParent[$parentIndex]['parent']['id'] = $_dash->sub_menu_id;
								$menuParent[$parentIndex]['parent']['user_list_menu'] = $_dash->user_list_menu;
								$parentManuCreated[] = $_dash->sub_menu_id;
								}
								$parentIndex++;
						}
					}
				}
			}
			else
			{
				if($_dash->user_list_menu != NULL && $_dash->user_list_menu != '')
				{
					if($_dash->sub_menu_status == 1 && $_dash->Parent_menu_status == 1)
					{
						$allowUsers = $_dash->user_list_menu;
						$allowUsersArr = explode(",",$allowUsers);
						if(in_array($empSessionId,$allowUsersArr))
						{
								$menuSub[$_dash->parent_menu_id][$_dash->sub_menu_id]['child']['name'] = DashboardDevelopmentController::getMenuName($_dash->sub_menu_id);
								$menuSub[$_dash->parent_menu_id][$_dash->sub_menu_id]['child']['id'] = $_dash->sub_menu_id;
								$menuSub[$_dash->parent_menu_id][$_dash->sub_menu_id]['child']['user_list_menu'] = $_dash->user_list_menu;
						}
					}
				}
			}
		}
	   /*  echo "<pre>";
		print_r($menuParent);
		exit; */
		/*
		*get all active User
		*/
		$userDetails = Employee::where("status",1)->whereNotNull("employee_id")->get();
		
		$switchUsersList = array();
		foreach($userDetails as $userD)
		{
			$empData = Employee_details::where("emp_id",$userD->employee_id)->first();
			if($empData != '')
			{
				$switchUsersList[$userD->id] = $userD->username.'-('.$this->deptName($empData->dept_id).')-('.$this->jobFunc($empData->job_function).')';
			}
		}
	
		/*
		*get all active User
		*/
		
		return view("dashboard/parentdashboardmashreq",compact('dashboardCreationData','menuParent','menuSub','empRequiredDetails','switchUsersList'));

		//return view("dashboard/newHomePage",compact('dashboardCreationData','menuParent','menuSub','empRequiredDetails'));


		
	}
	public function dashboardLoadingFucHomeMashreq(Request $request)
	{
		//return $request->all();
		// return 123;
		
		$empSessionId = $request->session()->get('EmployeeId');
		$users=Employee::where("id",$empSessionId)->first();
		if($users!=''){
		$empid=$users->employee_id;
		$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
		}
		else{
			$empRequiredDetails ='';
		}
		$mid =  $request->mid;
		$listData = DashboardCreation::where("sub_menu_id",$mid)->where("status",1)->get();
		$listDash = array();
		foreach($listData as $list)
		{
			$allowUsers = $list->user_list_dashboard;
			$allowUsersArr = explode(",",$allowUsers);
			if(in_array($empSessionId,$allowUsersArr))
				{
					$listDash[] = $list;
				}
			
		}


		$empCount = Employee_details::where('offline_status',1)->orderBy('id','desc')->get()->count();


		$todayDate = date('Y-m-d');
		$empAttendanceData = EmpAttendance::where('attribute_code','attendance')->where('attribute_value','P')->where('attendance_date',$todayDate)->orderBy('id','desc')->get()->count();

		if($empAttendanceData)
		{
			$empAttendance = $empAttendanceData;
		}
		else
		{
			$empAttendance=0;
		}

		$tDate = date('Y-m-d');
		$requestedLeaves = RequestedLeaves::where('final_status',1)->get();
		$newResult=array();
		foreach($requestedLeaves as $value)
		{
			if($value->updated_from_date==NULL && $value->updated_to_date==NULL)
			{
				if($value->from_date <= $tDate && $value->to_date >= $tDate)
				{
					$newResult[]=$value->id;
				}                       
			}
			else
			{
				if($value->updated_from_date <= $tDate && $value->updated_to_date >= $tDate)
				{
					$newResult[]=$value->id;
				}
			}
		}


		$empLeavesCount = RequestedLeaves::whereIn('leaves_request.id',$newResult)->get()->count();


		
		return view("dashboard/dashboardLoadingFucHomeMashreq",compact('listDash','empRequiredDetails'));
	}
	public function widgetLoadOnDashboardHomeMashreq(Request $request)
	{
		$empSessionId = $request->session()->get('EmployeeId');
		$dashName = $request->dashName;
		
		/*
		*get widget list as per dashboard
		*start code
		*/
		
		$widgetListhome = WidgetCreationHome::where("widget_name",$dashName)->first();
		$homeexp=explode(",",$widgetListhome->widget_ids);
		//print_r($homeexp);exit;
		$widgetDashboardDetails = array(); 
		$index = 0;
		$dataType = array();
		$from_salesTime = array();
		$to_salesTime = array();
		foreach($homeexp as $ids){
		$widgetList = WidgetCreation::where('id',$ids)->where("widget_layout",2)->where("status",1)->get();
	
		
		foreach($widgetList as $widgetData)
		{
			

					$allowUsers = $widgetData->user_list;
						$allowUsersArr = explode(",",$allowUsers);
						if(in_array($empSessionId,$allowUsersArr))
						{
						$widgetDashboardDetails[$index]['layout'] =  $widgetData->widget_layout_id;
						$widgetDashboardDetails[$index]['wid'] =  $widgetData->id;
						$widgetDashboardDetails[$index]['name'] =  $widgetData->widget_name;
						$widgetDashboardDetails[$index]['user_list'] =  $widgetData->user_list;
						
						
						
						if($request->session()->get('widgetFilter['.$widgetData->id.'][data_type]') != '')
						{
							$dataType[$widgetData->id] =  $request->session()->get('widgetFilter['.$widgetData->id.'][data_type]');
							
						}
						else
						{
							$dataType[$widgetData->id] = 'current_month';
						}
						
						if($request->session()->get('widgetFilter['.$widgetData->id.'][from_salesTime]') != '')
						{
							$from_salesTime[$widgetData->id] =  $request->session()->get('widgetFilter['.$widgetData->id.'][from_salesTime]');
							
						}
						else
						{
							$from_salesTime[$widgetData->id] = '';
						}
						
						if($request->session()->get('widgetFilter['.$widgetData->id.'][to_salesTime]') != '')
						{
							$to_salesTime[$widgetData->id] =  $request->session()->get('widgetFilter['.$widgetData->id.'][to_salesTime]');
							
						}
						else
						{
							$to_salesTime[$widgetData->id] = '';
						}
					
						
						}
						$index++;
		}
		
	}
	$userList = Employee::where("id",$empSessionId)->first();
		if($userList!=''){
		$empid=$userList->employee_id;
		$empRequiredDetails =  Employee_details::where('emp_id',$empid)->where('job_function',3)->first();
		if($empRequiredDetails!=''){
			$empdpt=$empRequiredDetails->dept_id;
		}
		else{
			$empdpt='';
		}
		}
		/*
		*get widget list as per dashboard
		*end code
		
			 echo "<pre>";
		print_r($widgetDashboardDetails);
		exit; 
		/* echo "<pre>";
		print_r($dataType);
		exit; */
		$user=$request->session()->get('EmployeeId');
		$widgetlist=SearchResultWidget::where("user_id",$user)->where("tabname",$dashName)->get();
		return view("dashboard/widget/widgetLoadOnDashboardHomeMashreq",compact('widgetlist','widgetDashboardDetails','dataType','from_salesTime','to_salesTime','dashName','empdpt'));
	}	
}