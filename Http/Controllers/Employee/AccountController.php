<?php

namespace App\Http\Controllers\Employee;

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
use Crypt;
use App\User;
use App\Models\Permission\Usergroup;

class AccountController extends Controller
{
    
       public function CreateAccount()
	   {
			$emp_details = Employee_details::where("status",1)->orderBy("first_name",'ASC')->get();
            $group_details = Usergroup::get();
			return view("Employee/CreateAccount",compact('emp_details','group_details'));
	   }
	   
	   public function AccountGrid()
	   {
			$user_details = User::where("status",1)->orderBy("id",'DESC')->get();
            //$user_details = User::join('group_permission', 'users.group_id', '=', 'group_permission.id')->get();
			return view("Employee/Accountgrid",compact('user_details'));
	   }
	   public function accountPassData($uid){
			$user_details = User::where("status",1)->where("id",$uid)->orderBy("id",'DESC')->first();
			//print_r($user_details->id);exit;
            //$user_details = User::join('group_permission', 'users.group_id', '=', 'group_permission.id')->get();
			return view("Employee/UserPass",compact('user_details'));  
	   }
	   

	   public function registerUser(Request $request) 
	   {
            $emp_details = Employee_details::select("*")->where("id", "=", $request->uid)->first();
            $empid = $emp_details['emp_id'];
            $fname = $emp_details['first_name'];
            $mname = $emp_details['middle_name'];
            $lname = $emp_details['last_name'];
            $emp_designation = Employee_attribute::select("*")->where("emp_id", "=", $empid)->where("attribute_code", "=", 'DESIGN')->first();
            $emp_designation = $emp_designation['attribute_values'];
            $encryptedpwd = Crypt::encrypt($request->Password);  // for encryption 

            $userArray  =   array( 
                "fullname"    		=>      $fname.' '.$mname.' '.$lname,
                "username"    		=>      $request->UserName,
                "password"     		=>      $encryptedpwd,
                "designation"       =>      $emp_designation,
                "status"         	=>      1,
                "employee_id"    	=> 		$empid,
                "group_id"          =>      $request->groupList,
            );

            $user       =       User::create($userArray);
            if(!is_null($user)) 
            { 
                $request->session()->flash('statusmsg','Registration completed successfully');
                return redirect()->route('accountgrid');
            }
            else {
                return back()->with("failed", "Registration failed. Try again.");
            }
	   }

        public function deleteUser(Request $req)
        {
            $user=User::find($req->id);
            $user->delete(); //returns true/false
            $req->session()->flash('statusmsg','User deleted Successfully.');
            return redirect()->route('accountgrid');
        }

		public function EditAccount(Request $request)
		{
            $user_details = User::where('id', $request->id)->first();
            $group_details = Usergroup::get();
            return view("Employee/Editaccount",compact('user_details','group_details'));	
		}
    
        public function updateUserAccount(Request $request)
        {
            $encryptedpwd = Crypt::encrypt($request->Password);
            $userinfo = User::find($request->uid);            
            $userinfo->username = $request->UserName;
            $userinfo->password = $encryptedpwd;
            $userinfo->group_id = $request->groupList;
            
            if($userinfo->save()) 
            { 
                $request->session()->flash('statusmsg','User Account Updated successfully');
                return redirect()->route('accountgrid');
            }
            else 
            {
                return back()->with("failed", "Updation failed. Try again.");
            }            
           
        }

        public static function getuserGroupName($uGroupId)
        {
           
            $userGroup_details = Usergroup::where('id', $uGroupId)->first();
            // echo "<pre>";
            // print_r($userGroup_details);
            // echo $userGroup_details->group_name;die;
            if (!empty($userGroup_details))
            {
                return $userGroupName = $userGroup_details->group_name;
            }
            else{
                return $userGroupName = 'N/A';
            }
            
        }

        public static function getuserGroups()
        {
            $userGroups = Usergroup::get();
            //$userGroup_details = Usergroup::where('id', $uGroupId)->first();
            if (!empty($userGroups))
            {
                return $userGroups;
            }
            else{
                return $userGroups = 'N/A';
            }
            
        }

        public function getEmpData()
        {
            $id = $_GET['id'];

            $emp_details = Employee_details::select("*")->where("id", "=", $id)->first();
            $empid = $emp_details['emp_id'];

            $user_details = User::where('employee_id', $empid)->first();
            if(!empty($user_details))
            {
                return 1;
            }

        }

        public function chkUserData()
        {
            $uname = $_GET['uname'];
            $user_details = User::where('username', $uname)->first();
            //print_r($user_details);
            if(!empty($user_details))
            {
                return 1;
            }
        }
       
    
    
}
