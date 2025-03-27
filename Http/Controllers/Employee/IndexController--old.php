<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company\Department;
use  App\Models\Attribute\Attributes;

class IndexController extends Controller
{
    
        public function addEmp()
		{
			$departmentMod = Department::orderBy("id",'DESC')->get();
			
			return view("Employee/addemp",compact('departmentMod'));
		}
		
		public function employeeForm($id=NULL)
		{
			$attributesDetails = Attributes::where(["department_id"=>$id,"parent_attribute"=>0])->orwhere(["department_id"=>'All',"parent_attribute"=>0])->get();
			return view("Employee/employeeform",compact('attributesDetails'));
		}
}
