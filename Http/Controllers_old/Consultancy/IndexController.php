<?php

namespace App\Http\Controllers\Consultancy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Consultancy\ConsultancyModel;
use App\Models\Consultancy\Resumedetails;
use App\Models\Entry\Employee;
use Crypt;
use Session;
class IndexController extends Controller
{
	
		public function manageConsultancy()
		{
			$consultancyLists = array();
			$c_obj = new ConsultancyModel();
			$consultancyLists = $c_obj->where("status",1)->orWhere("status",2)->orderBy("id","DESC")->get();
			return view("Consultancy/manageConsultancy",compact('consultancyLists'));
		}
    
        public function addConsultancy()
		{
			return view("Consultancy/addConsultancy");
		}
		
		public function addConsultancyPost(Request $req)
		{
			/*
			*checking username existance in employee model
			*start code
			*/
			$emplists = Employee::where('username',$req->input('username'))->get();
			$emplistsCount = $emplists->count();
			if($emplistsCount >0)
			{
				$req->session()->flash('message','consultancy username already exists.');
				 //redirect()->back();
				return redirect()->back()->withInput();
			}
			/*
			*checking username existance in employee model
			*end code
			*/
			$e_obj = new Employee();
			$e_obj->username = $req->input('username');
			$e_obj->password = Crypt::encrypt($req->input('password'));
			$e_obj->fullname = $req->input('consultancy_name');
			$e_obj->passwordtxt = $req->input('password');
			$e_obj->designation = 'Consultancy';
			$e_obj->pics = 'user-profile.png';
			$e_obj->status = 1;
			$e_obj->save();
			
			$c_obj = new ConsultancyModel();
            $c_obj->consultancy_name = $req->input('consultancy_name');
            $c_obj->cantact_name = $req->input('cantact_name');
           
            $c_obj->contact_number = $req->input('contact_number');
            $c_obj->Flat_commision_per_person = $req->input('Flat_commision_per_person');
			$c_obj->commission_currency = $req->input('commission_currency');
			$c_obj->address1 = $req->input('address1');
			$c_obj->address2 = $req->input('address2');
			$c_obj->country = $req->input('country');
			$c_obj->h_contact_number = $req->input('h_contact_number');
			$c_obj->email = $req->input('email');
			
			
			$c_obj->username = $req->input('username');
			$c_obj->password = Crypt::encrypt($req->input('password'));
			$c_obj->passwordtxt = $req->input('password');
			$c_obj->status = $req->input('status');
			$c_obj->employee_id = $e_obj->id;
            $c_obj->save();
            $req->session()->flash('message','Consultancy Saved Successfully.');
            return redirect('manageConsultancy');
		}
		
		public function deleteConsultancy(Request $req)
		{
			$consultancy_obj = ConsultancyModel::find($req->id);
			$consultancy_obj->status =3;
			$consultancy_obj->save();
			/*
			*
			*delete access from employee
			*/
			 $consultancy_data = ConsultancyModel::where('id',$req->id)->first();
			 $employeeId = $consultancy_data->employee_id;
			 
			 /*
			*
			*@employee model updation for status
			*
			*/
			
			$e_obj =  Employee::find($employeeId);
			
			$e_obj->status = 3;
			$e_obj->save();
			/*
			*
			*@employee model updation for status
			*
			*/
			 /*
			*
			*delete access from employee
			*/
			$req->session()->flash('message','Consultancy Deleted Successfully.');
			return redirect('manageConsultancy');
		}
		
		public function updateConsultancy(Request $req)
		{
		   $consultancy_data = ConsultancyModel::where('id',$req->id)->first();
		   
		   return view("Consultancy/updateConsultancy",compact('consultancy_data'));
		}
		
		public function updateConsultancyPost(Request $req)
		{
			$parameterDetails = $req->input();
			
			$c_obj =  ConsultancyModel::find($req->input('id'));
			
           $c_obj->consultancy_name = $req->input('consultancy_name');
            $c_obj->cantact_name = $req->input('cantact_name');
           
            $c_obj->contact_number = $req->input('contact_number');
            $c_obj->Flat_commision_per_person = $req->input('Flat_commision_per_person');
			$c_obj->commission_currency = $req->input('commission_currency');
			$c_obj->address1 = $req->input('address1');
			$c_obj->address2 = $req->input('address2');
			$c_obj->country = $req->input('country');
			$c_obj->h_contact_number = $req->input('h_contact_number');
			$c_obj->email = $req->input('email');
            $c_obj->status = $req->input('status');
			$c_obj->save();
			/*
			*
			*@employee model updation for status
			*
			*/
			
			$e_obj =  Employee::find($req->input('employee_id'));
			
			$e_obj->status = $req->input('status');
			$e_obj->save();
			/*
			*
			*@employee model updation for status
			*
			*/
			
			
            $req->session()->flash('message','Consultancy Updated Successfully.');
            return redirect('manageConsultancy');
		}
		
		public function changeConsultancyAccess(Request $req)
		{
			$consultancy_data = ConsultancyModel::where('id',$req->id)->first();
		   
		    return view("Consultancy/changeConsultancyAccess",compact('consultancy_data'));
		}
		
		public function changepassConsultancyPost(Request $req)
		{
			$parameterDetails = $req->input();
			
			$c_obj =  ConsultancyModel::find($req->input('id'));
			
            $c_obj->password = Crypt::encrypt($req->input('password'));
			$c_obj->passwordtxt = $req->input('password');
			$c_obj->save();
			/*
			*
			*@employee model updation for password
			*
			*/
			
			$e_obj =  Employee::find($req->input('employee_id'));
			$e_obj->password = Crypt::encrypt($req->input('password'));
			$e_obj->passwordtxt = $req->input('password');
			$e_obj->save();
			/*
			*
			*@employee model updation for password
			*
			*/
            $req->session()->flash('message','Consultancy Password Updated Successfully.');
            return redirect('manageConsultancy');
		}
		
		public function registeredConsultancy(Request $req)
		{
			return view('Consultancy/DashboardConsultancy');
		}
		
		public function  resumeConsultancy(Request $req)
		{
			$consultancy_data = ConsultancyModel::where('status',1)->get();
			$employeeDesignation = $req->session()->get('EmployeeDesignation');
			$layoutName = '';
			if($employeeDesignation == 'Recruiter')
			{	
			$layoutName = 'layouts.recruiterLayout';
			}
			else
			{	
			$layoutName = 'layouts.hrmLayout';
			}
		
			return view('Consultancy/resumeConsultancy',compact('consultancy_data','layoutName'));
		}
}
