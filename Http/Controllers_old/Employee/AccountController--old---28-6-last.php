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
use Illuminate\Support\Facades\Crypt;
use App\User;
//use Illuminate\Support\Facades\Session;




class AccountController extends Controller
{
    
       public function index()
	   {
			$emp_details = Employee_details::where("status",1)->orderBy("first_name",'ASC')->get();
			//echo "<pre>";
			//print_r($emp_details);
			//echo $emp_details->emp_id;
			//die;
			return view("Employee/CreateAccount",compact('emp_details'));			
			//return view("Employee/CreateAccount");

	   }
	   public function index2()
	   {
			return view("Employee/CreateAccount2");

	   }
	   public function AccountGrid()
	   {
			$user_details = User::where("status",1)->orderBy("id",'DESC')->get();
			return view("Employee/Accountgrid",compact('user_details'));			

			

	   }

	   public function registerUser(Request $request) 
	   {
		//echo "Hello";

		//$emp_details = Employee_details::where("id",$request->uid)->get();
		$emp_details = Employee_details::select("*")->where("id", "=", $request->uid)->first();

		$empid = $emp_details['emp_id'];
		$fname = $emp_details['first_name'];
		$mname = $emp_details['middle_name'];
		$lname = $emp_details['last_name'];

		$emp_designation = Employee_attribute::select("*")->where("emp_id", "=", $empid)->where("attribute_code", "=", 'DESIGN')->first();

		//echo "<pre>";
		//print_r($emp_designation);
		$emp_designation = $emp_designation['attribute_values'];
		//die;



		//echo "<pre>";
		//print_r($emp_details);
		//echo $request->UserName;
		$encryptedpwd = crypt::encryptString($request->Password);  // for encryption 

		
		//die;


		$userArray  =   array( 
			"fullname"    		=>      $fname.' '.$mname.' '.$lname,
            "username"    		=>      $request->UserName,
            "password"     		=>      $encryptedpwd,
            "designation"       =>      $emp_designation,
            "status"         	=>      1,
			"employee_id"    	=> 		$empid,
        );
		//done yahan tak

        $user       =       User::create($userArray);
        if(!is_null($user)) { 
			//echo "Successs";
            //return back()->with("success", "Registration completed successfully");
			//return redirect()->route('/CreateAct3')->withSuccess(['Registration completed successfully']);
			$request->session()->flash('statusmsg','Registration completed successfully');
			return redirect()->route('accountgrid');


        }

        else {
			//echo "Fail";
            return back()->with("failed", "Registration failed. Try again.");
        }
		//die;


	   }

	   public function deleteUser(Request $req)
		{
		// 	$users = User::find($req->id);       
        // $users->status = 3;       
        // $users->save();

		$user=User::find($req->id);
		$user->delete(); //returns true/false
        $req->session()->flash('statusmsg','User deleted Successfully.');
        return redirect()->route('accountgrid');
		}

		public function EditAccount(Request $request)
		{
			//echo $request->id; die;
            //$user_details = User::select("*")->where("id", "=", $request->id)->first();
            $user_details = User::where('id', $request->id)->first();
            //return view("Employee/Editaccount");
            return view("Employee/Editaccount",compact('user_details'));	
		}
    
        public function updateUserAccount(Request $request)
        {
            //echo "Hello";
            //echo $qw = User::where('id', '=', $request->id)->update(array('username' => $request->UserName));
            //$uid = (int)$request->id;
            $encryptedpwd = crypt::encryptString($request->Password);
            $userinfo = User::find($request->uid);            
            $userinfo->username = $request->UserName;
            $userinfo->password = $encryptedpwd;
            //echo $userinfo->save();
           // $flight = Flight::find(1);
//            echo "<pre>";
//            print_r($userinfo);
//            die;
            
            if($userinfo->save()) 
            { 
                $request->session()->flash('statusmsg','User Account Updated successfully');
                return redirect()->route('accountgrid');
            }
            else 
            {
                //echo "Fail";
                return back()->with("failed", "Updation failed. Try again.");
            }
            
            
           
        }
    
    
}
