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
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use App\Models\Permission\Usergroup;
use Helper;
use UserPermissionAuth;



class GroupController extends Controller
{
    
       public function index()
	   {
			$emp_details = Employee_details::where("group_status",1)->orderBy("first_name",'ASC')->get();
			return view("Employee/CreateAccount",compact('emp_details'));
	   }
	   public function CreateGroup()
	   {
			return view("Permission/createGroup");
	   }
	   public function saveGroup(Request $request)
	   {
		   
			$userGroupArray  =   array( 
				"group_name"   =>  $request->userGroupName,
				"group_des"    =>  $request->groupDescription,
				"group_caption"    =>  $request->userGroupCaption,
				"permission_setting_status" => 0,
				"group_status" =>1
			);
			
			$userGroup = Usergroup::create($userGroupArray);
			if(!is_null($userGroup)) 
			{ 
				$request->session()->flash('statusmsg','User Group created successfully');
				return redirect()->route('userGroupsGrid');
			}
			else {
				return back()->with("failed", "Failed. Try again.");
			}
			
	   }
	   public function ListGroup(Request $request)
	   {
		//echo UserPermissionAuth::modulepermission($request->session()->get('EmployeeId'));exit;
		   //Helper::shout('now i\'m using my helper class in a controller!!');
			$group_details = Usergroup::orderBy("id",'DESC')->get();
			return view("Permission/listGroup",compact('group_details'));
	   }
	   public function ViewGroup()
	   {
			return view("Permission/viewGroup");
	   }
	   public function EditGroup(Request $request)
	   {
			//echo $request->id;
			$userGroup_details = Usergroup::where('id', $request->id)->first();
			// echo "<pre>";
			// print_r($userGroup_details);
			// die;
			return view("Permission/editGroup",compact('userGroup_details'));
	   }
	   public function deleteUserGroup(Request $request)
	   {
			$usergroup=Usergroup::find($request->id);
			$usergroup->delete(); //returns true/false
			$request->session()->flash('statusmsg','User Group deleted Successfully.');
			return redirect()->route('userGroupsGrid');
	   }
	   
	   public function updateUserGroup(Request $request)
	   {			
			$userGroupInfo = Usergroup::find($request->id);            
			$userGroupInfo->group_name = $request->userGroupName;
			$userGroupInfo->group_des = $request->groupDescription;
			$userGroupInfo->group_caption    =  $request->userGroupCaption;
			//die;
			
			if($userGroupInfo->save()) 
			{ 
				$request->session()->flash('statusmsg','User Group Updated successfully');
				return redirect()->route('userGroupsGrid');
			}
			else 
			{
				return back()->with("failed", "Updation failed. Try again.");
			}          
	   }
	   
}
