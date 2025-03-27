<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company\Subsidiary;
use App\Models\Company\Divison;
use App\Models\Company\Department;
use  App\Models\Attribute\Attributes;
use App\Models\Employee\Employee_attribute;
use App\Models\Employee\Employee_details;
use App\Models\Employee\EmployeeImportFiles;
use App\Models\Employee\EmployeeAttendanceModel;
use App\Models\Entry\Employee;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use App\Models\Permission\Aclmodule;
use Illuminate\Support\Facades\Validator;
use App\Models\Permission\PermissionGroup;
use App\Models\Permission\Usergroup;
use App\Models\Permission\HeaderMenu;

use UserPermissionAuth;



class PermissionController extends Controller
{
    
       public function groupPermission(Request $request)
	   {
		    $groupId = $request->groupId;
			$modules = Aclmodule::select('*')
			->where('status', 1)
			->where('parent_id', 0)
			->orderBy('id', 'DESC')
			->get();

		
			$modulesdata = Aclmodule::join('permission_group', 'module_details.id', '=', 'permission_group.module_id')
			->where("permission_group.group_id", $groupId)->where("permission_group.status", 1)->orderBy('permission_group.id', 'DESC')->get(['module_details.*', 'permission_group.*']);

			return view("Permission/groupPermission",compact('modules', 'modulesdata','groupId'));
	   }
	 
	   public function moduleRegister(Request $request)
	   {
		   //$moduleList = UserPermissionAuth::modulepermission(120,$request->session()->get('EmployeeId'));
		   /* echo '<pre>';
		   print_r($moduleList);
		   exit; */
			$module_details = Aclmodule::where('parent_id', 0)->orderBy("id",'DESC')->get();
			$headerMenu =HeaderMenu::get();
			$parentModule  = Aclmodule::where("parent_module_id",0)->where('parent_id', 0)->get();
			return view("Permission/moduleRegister",compact('module_details','headerMenu','parentModule'));
	   }

	   public function saveModule(Request $request)
	   {
			

			$modulesArray  =   array( 
				"module_name"   =>  $request->module_name,
				"parent_id"     =>  0,
				"header_menu_id" =>$request->header_menu_id,
				"parent_module_id" =>$request->parent_module_id,
				"pathInfoDetails" =>$request->pathInfoDetails,
				"awesome_font_icons" =>$request->awesome_font_icons,
				"status"        => 1,
			);
			

			$lastid = Aclmodule::create($modulesArray)->id;

			if($request->moduleaction[0]=='all')
			{
				$actionarr = array_slice($request->moduleaction, 1);

				foreach($actionarr as $action)
				{
					$aclaction=new Aclmodule;
					$aclaction->action_name = $action;
					$aclaction->parent_id = $lastid;
					$aclaction->status = 1;
					$aclaction->module_name = $request->module_name;
					$aclaction->header_menu_id = $request->header_menu_id;
					$aclaction->parent_module_id = $request->parent_module_id;
					
					$aclaction->save();
					
				}
			}
			else{

				foreach($request->moduleaction as $action)
				{
					$aclaction=new Aclmodule;
					$aclaction->action_name = $action;
					$aclaction->parent_id = $lastid;
					$aclaction->status = 1;
					$aclaction->module_name = $request->module_name;
					$aclaction->header_menu_id = $request->header_menu_id;
					$aclaction->parent_module_id = $request->parent_module_id;
					
					$aclaction->save();
					
				}
			}

			 
				$request->session()->flash('statusmsg','Module Created Successfully');
				return redirect()->route('modulesGrid');
			
			
			
	   }


	   


//app\Http\Controllers\HomeController.php
//Create controller (HomeController) if not already created using php artisan make:controller HomeController and use below function in it.
		public static function getActionById($id)
		{
		
			$action_details = Aclmodule::where('parent_id', $id)->get();
			return $action_details;
		}		

		public function unregisterModuleAction(Request $request)
		{
			$modules = Aclmodule::select('*')
        	->where('id', $request->id)
        	->get();
			$modulename = $modules[0]['module_name'];
			$moduleUpdate = Aclmodule::where("module_name", $modulename)->update(["status" => 2]);			

			if($moduleUpdate) 
			{ 
		$moduleIdValue = $request->id;
		/*
		*Module unregister from permission group as well
		*/
		$moduleInpermission = PermissionGroup::where("module_id",$moduleIdValue)->get();
		foreach($moduleInpermission as $permissionM)
		{
			$permissionId = $permissionM->id;
			$groupId = $permissionM->group_id;
			$permissionModuleUpdate = PermissionGroup::find($permissionId);
			$permissionModuleUpdate->status = 2;
			$permissionModuleUpdate->save();
			
			/*
			*check for group update
			*/
			$groupCount = PermissionGroup::where("group_id",$groupId)->where('status',1)->count();
			if($groupCount == 0)
			{
				$userGroupMod = Usergroup::find($groupId); 
				$userGroupMod->permission_setting_status = 0;
				$userGroupMod->save();
			}
			/*
			*check for group update
			*/
		}
		/*
		*Module unregister from permission group as well
		*/
				$request->session()->flash('statusmsg','Module Unregistered successfully');
				return redirect()->route('modulesGrid');
			}
			else 
			{
				return back()->with("failed", "Updation failed. Try again.");
			} 

		}

public function undoModule(Request $request)
		{
			$modules = Aclmodule::select('*')
        	->where('id', $request->id)
        	->get();
			$modulename = $modules[0]['module_name'];
			$moduleUpdate = Aclmodule::where("module_name", $modulename)->update(["status" => 1]);			

			if($moduleUpdate) 
			{ 
		$moduleIdValue = $request->id;
		/*
		*Module unregister from permission group as well
		*/
		$moduleInpermission = PermissionGroup::where("module_id",$moduleIdValue)->get();
		foreach($moduleInpermission as $permissionM)
		{
			$permissionId = $permissionM->id;
			$groupId = $permissionM->group_id;
			$permissionModuleUpdate = PermissionGroup::find($permissionId);
			$permissionModuleUpdate->status = 1;
			$permissionModuleUpdate->save();
			
			/*
			*check for group update
			*/
			
				$userGroupMod = Usergroup::find($groupId); 
				$userGroupMod->permission_setting_status = 1;
				$userGroupMod->save();
			}
			/*
			*check for group update
			*/
				$request->session()->flash('statusmsg','Module undo successfully');
				return redirect()->route('modulesGrid');
			}
			else 
			{
				return back()->with("failed", "Updation failed. Try again.");
			} 

		}
	public function showModule($id)
    {
        //echo $id; die;
		// $modules = Aclmodule::select('*')
        // 	->where('id', $id)
        // 	->get();

			$where = array('id' => $id);
			$modules  = Aclmodule::where($where)->first();


			// $modulesinfo = Aclmodule::select('*')
        	// ->where('parent_id', $id)
        	// ->get();
			


        return response()->json($modules);
		
		

    }


	public function showModulesAction($id)
    {
        //echo $id; exit;
		// $modules = Aclmodule::select('*')
        // 	->where('id', $id)
        // 	->get();

			// $where = array('id' => $id);
			// $modules  = Aclmodule::where($where)->first();


			$modulesinfo = Aclmodule::select('id', 'action_name')
        	->where('parent_id', $id)
        	->get();

			// echo "<pre>";
			// print_r($modulesinfo);
			// exit;
			


        return response()->json($modulesinfo);
		//return $modulesinfo;
		
		

    }


	public function modulesActionSave(Request $request)
	{
		// echo "Hello";
		$input = $request->all();
		// echo "<pre>";
		// print_r($input['datachk']);


		$modulechk = PermissionGroup::where('module_id', '=', $input['mid'])->where('group_id', '=', $input['gid'])->first();

		// echo "<pre>";
		// print_r($modulechk['id']);
		// die;

		if(!empty($modulechk))
		{
			$actionList = implode(',', $input['datachk']);

			$userGroupInfo = PermissionGroup::find($modulechk['id']);            
			$userGroupInfo->group_id = $input['gid'];
			$userGroupInfo->module_id = $input['mid'];
			$userGroupInfo->action_ids = $actionList;
			$userGroupInfo->status = 1;
			//die;
			
			if($userGroupInfo->save()) 
			{ 
				$groupId = $input['gid'];
				$modulesdata = Aclmodule::join('permission_group', 'module_details.id', '=', 'permission_group.module_id')->where("group_id",$groupId)
               ->get(['module_details.*', 'permission_group.*']);

				//$modulesdata = PermissionGroup::select('*')
				//->where('parent_id', $id)
				//->get();
				 $arrayDisplay = array();
				 $counter = 0;
				foreach($modulesdata as $_module)
				{
				
				$arrayDisplay[$counter]['modulename'] = $_module->module_name;
				$arrayDisplay[$counter]['privilegesName'] = '';
				$privilages = $_module->action_ids;
				$arrayprivilages = explode(",",$privilages);
				foreach($arrayprivilages as $_privilagesID)
				{
					$action_name = Aclmodule::where("id",$_privilagesID)->first()->action_name;
					if(empty($arrayDisplay[$counter]['privilegesName']))
					{
						
						$arrayDisplay[$counter]['privilegesName'] = $action_name;
					}
					else
					{
						$arrayDisplay[$counter]['privilegesName'] = $arrayDisplay[$counter]['privilegesName'].','.$action_name;
					}
				}
				$counter++;
				}
				/*
				*Update group permission status
				*/
					$userGroupMod = Usergroup::find($groupId); 
					$userGroupMod->permission_setting_status = 1;
					$userGroupMod->save();
				/*
				*Update group permission status
				*/
				return response()->json($arrayDisplay);
			}
			else 
			{
				return back()->with("failed", "Updation failed. Try again.");
			}



		}
		else{

			$actionList = implode(',', $input['datachk']);
			//die;
	
			$moduleactionArray  =   array( 
				"group_id"   =>  $input['gid'],
				"module_id"    =>  $input['mid'],
				"action_ids"   => $actionList,
				"status"       => 1,
			);
	
			$modpermissionGroup = PermissionGroup::create($moduleactionArray);
			if(!is_null($modpermissionGroup)) 
			{ 
				$groupId = $input['gid'];
				$modulesdata = Aclmodule::join('permission_group', 'module_details.id', '=', 'permission_group.module_id')->where("group_id",$groupId)
				->get(['module_details.*', 'permission_group.*']);
				
				
				 $arrayDisplay = array();
				 $counter = 0;
				foreach($modulesdata as $_module)
				{
				
				$arrayDisplay[$counter]['modulename'] = $_module->module_name;
				$arrayDisplay[$counter]['privilegesName'] = '';
				$privilages = $_module->action_ids;
				$arrayprivilages = explode(",",$privilages);
				foreach($arrayprivilages as $_privilagesID)
				{
					$action_name = Aclmodule::where("id",$_privilagesID)->first()->action_name;
					if(empty($arrayDisplay[$counter]['privilegesName']))
					{
						
						$arrayDisplay[$counter]['privilegesName'] = $action_name;
					}
					else
					{
						$arrayDisplay[$counter]['privilegesName'] = $arrayDisplay[$counter]['privilegesName'].','.$action_name;
					}
				}
				$counter++;
				}
				/*
				*Update group permission status
				*/
					$userGroupMod = Usergroup::find($groupId); 
					$userGroupMod->permission_setting_status = 1;
					$userGroupMod->save();
				/*
				*Update group permission status
				*/
				return response()->json($arrayDisplay);
				//return response()->json(['success'=>'Saved Successfully']);
	
			}
			else {
				return back()->with("failed", "Failed. Try again.");
			}


		}










	}



	// public function moduleShow()
	// {
		
	// 	$modulesdata = Aclmodule::join('permission_group', 'module_details.id', '=', 'permission_group.module_id')
	// 	->get(['module_details.*', 'permission_group.*']);
	// 	return view("Permission/groupPermission",compact('modulesdata'));

	// }



public function deletePermission(Request $request)
{
	$permissionId =  $request->permissionId;
	$groupId = $request->groupId;
	/*
	*update table group
	*/
	$permissionGroupCount = PermissionGroup::where("group_id",$groupId)->where("status",1)->count();
	if($permissionGroupCount == 1)
	{
		$userGroupMod = Usergroup::find($groupId); 
		$userGroupMod->permission_setting_status = 0;
		$userGroupMod->save();
	}
	/*
	*update table group
	*/
	PermissionGroup::find($permissionId)->delete();
	
	
	$request->session()->flash('statusmsg','Module permission deleted successfully.');
	return redirect()->back();
}
public function editRegisterModule($moduleId)
{
	$moduleDetails = Aclmodule::where('id', $moduleId)->first();
	$actionDetails = Aclmodule::where('parent_id', $moduleId)->get();
	
	$existModule = array();
	foreach($actionDetails as $_actions)
	{
		$existModule[$_actions->action_name] = $_actions->id;
	}
	$headerMenu =HeaderMenu::get();
	$parentModule  = Aclmodule::where("parent_module_id",0)->where('parent_id', 0)->get();
	return view("Permission/editRegisterModule",compact('moduleDetails','existModule','headerMenu','parentModule'));
}


public function updateModules(Request $request)
{
	$inputData = $request->input();
	
	$module_id = $inputData['module_id'];
	$actionDetails = Aclmodule::where('parent_id', $module_id)->get();
	$aclModuleMain = Aclmodule::find($module_id);
	$aclModuleMain->module_name = $request->module_name;
	$aclModuleMain->header_menu_id = $request->header_menu_id;
	$aclModuleMain->parent_module_id = $request->parent_module_id;
	$aclModuleMain->pathInfoDetails = $request->pathInfoDetails;
	$aclModuleMain->awesome_font_icons = $request->awesome_font_icons;
	$aclModuleMain->save();
	foreach($actionDetails as $_action)
	{
		
		$actionId = $_action->id;
		Aclmodule::find($actionId)->delete();
	}
	
	if($request->moduleaction[0]=='all')
			{
				$actionarr = array_slice($request->moduleaction, 1);

				foreach($actionarr as $action)
				{
					$aclaction=new Aclmodule;
					$aclaction->action_name = $action;
					$aclaction->parent_id = $module_id;
					$aclaction->status = 1;
					$aclaction->module_name = $request->module_name;
					$aclaction->header_menu_id = $request->header_menu_id;
					$aclaction->parent_module_id = $request->parent_module_id;
					
					$aclaction->save();
					
				}
			}
			else{

				foreach($request->moduleaction as $action)
				{
					$aclaction=new Aclmodule;
					$aclaction->action_name = $action;
					$aclaction->parent_id = $module_id;
					$aclaction->status = 1;
					$aclaction->module_name = $request->module_name;
					$aclaction->header_menu_id = $request->header_menu_id;
					$aclaction->parent_module_id = $request->parent_module_id;
					
					$aclaction->save();
					
				}
			}

			 
				$request->session()->flash('statusmsg','Module Updated Successfully');
				return redirect()->route('modulesGrid');
}

public static function getSubMenuById($id)
{
	$Aclmodule =  Aclmodule::where("id",$id)->first();
	if($Aclmodule != '')
	{
		return $Aclmodule->module_name;
	}
	else
	{
		return "None";
	}
}

public static function getMainMenuById($id)
{
	$headerMenu =  HeaderMenu::where("id",$id)->first();
	if($headerMenu != '')
	{
		return $headerMenu->name;
	}
	else
	{
		return "--";
	}
}


public static function getMenuListing($employeeId)
{
	//echo UserPermissionAuth::modulepermission(120,$employeeId);exit;
	$headerMenu =  HeaderMenu::get();
	$menuTree = array();
	foreach($headerMenu as $_header)
	{
		if($_header->id != 6)
		{
		$header_menu_id = $_header->id;
		$headerMenuName = $_header->name;
		$menuLabel1Count = Aclmodule::where("header_menu_id",$header_menu_id)->count();
			if($menuLabel1Count > 0)
			{
				$menuLabel1List = Aclmodule::where("header_menu_id",$header_menu_id)
									->where("parent_id",0)
									->where("parent_module_id",0)
									->orderBy("sort_order","ASC")
									->get();
				if($menuLabel1List != '')
				{
					foreach($menuLabel1List as $label1)
					{
						$menuLabel2List = Aclmodule::where("header_menu_id",$header_menu_id)
										->where("parent_id",0)
										->where("parent_module_id",$label1->id)
										->orderBy("sort_order","ASC")
										->get();
						/* echo '<pre>';
						print_r($label1);
						exit; */
						if($label1->pathInfoDetails == '')
						{
							if(UserPermissionAuth::modulepermission($label1->id,$employeeId,'direct'))
								{
								$menuTree[$headerMenuName][$label1->module_name]['label1']['name']	= $label1->module_name;
								$menuTree[$headerMenuName][$label1->module_name]['label1']['pathInfoDetails']= $label1->pathInfoDetails;
								$menuTree[$headerMenuName][$label1->module_name]['label1']['menuId']	= $label1->id;
								$menuTree[$headerMenuName][$label1->module_name]['label1']['awesome_font_icons']	= $label1->awesome_font_icons;
								}
						}
						else
						{
							if(UserPermissionAuth::modulepermission($label1->id,$employeeId,'normal'))
								{
									$menuTree[$headerMenuName][$label1->module_name]['label1']['name']	= $label1->module_name;
									$menuTree[$headerMenuName][$label1->module_name]['label1']['pathInfoDetails']= $label1->pathInfoDetails;
									$menuTree[$headerMenuName][$label1->module_name]['label1']['menuId']	= $label1->id;
									$menuTree[$headerMenuName][$label1->module_name]['label1']['awesome_font_icons']	= $label1->awesome_font_icons;
								}
						}
						if($menuLabel2List != '')
						{
							foreach($menuLabel2List as $label2)		
							{
								
								if(UserPermissionAuth::modulepermission($label2->id,$employeeId,'normal'))
								{
								$menuTree[$headerMenuName][$label1->module_name]['label2']['name'][]	= $label2->module_name;
					$menuTree[$headerMenuName][$label1->module_name]['label2']['pathInfoDetails'][]= $label2->pathInfoDetails;;
								$menuTree[$headerMenuName][$label1->module_name]['label2']['menuId'][]	= $label2->id;
								$menuTree[$headerMenuName][$label1->module_name]['label2']['awesome_font_icons'][]	= $label2->awesome_font_icons;
								}
							}					
						}						
						
					}
				}
			}
		}
		
	}
	

	return $menuTree;
}


public static function getModuleActionPermission($moduleId,$employeeId,$actionType)
{
	$employeeDetails = Employee::where('id',$employeeId)->first();
	$groupId = $employeeDetails->group_id;
	if($employeeDetails->designation == 'Admin')
		{
			
			return true;
		}
	else
		{
			$moduleActionDetails = Aclmodule::where("parent_id",$moduleId)->where("action_name",$actionType)->first();
			if($moduleActionDetails == '')
			{
				return false;
			}
			else
			{
				$actionId =  $moduleActionDetails->id;
				$permissionActionDetails = PermissionGroup::where('group_id',$groupId)->where("module_id",$moduleId)->first();
				if($permissionActionDetails != '')
				{
					$actionStr = $permissionActionDetails->action_ids;
					$actionArray = explode(",",$actionStr);
					if(in_array($actionId,$actionArray))
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			}
		}
}

}
