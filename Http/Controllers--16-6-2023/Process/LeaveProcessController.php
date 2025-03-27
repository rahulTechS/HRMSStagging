<?php

namespace App\Http\Controllers\Process;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\Employee\Employee_details;
use App\Models\Payroll\AnnualLeaveDetails;
use App\Models\Payroll\AnnualLeave;
use App\Models\Employee\Employee_attribute;



class LeaveProcessController extends Controller
{
    public function annualLeaveProcess()
	{
		
		$leaveProcessArray = array();
		$empDetails = Employee_details::where("status",1)->get();
		$index = 0;
		foreach($empDetails as $_emp)
		{
			$leaveProcessArray[$index]['empId'] = $_emp->id;
			
			$doj = Employee_attribute::where("emp_id",$_emp->emp_id)->where("attribute_code","DOJ")->first()->attribute_values;
			$total_gross_salary = Employee_attribute::where("emp_id",$_emp->emp_id)->where("attribute_code","total_gross_salary")->first()->attribute_values;
			$leaveProcessArray[$index]['DOJ'] = $doj;
			$leaveProcessArray[$index]['monthly_salary'] = $_emp->actual_salary;
			$index++;
		}
		
		/*
		*calculate Leave for year of each employee
		*/
		
		foreach($leaveProcessArray as $_leave)
		{
			$eid = $_leave['empId'];
			$checkExist = AnnualLeave::where("emp_id",$eid)->first();
			if($_leave['DOJ'] != '' && $_leave['monthly_salary'] != '' && $checkExist == '')
			{
				
				$aLMod = new AnnualLeave();
				
				$monthOfJoining = explode("/",$_leave['DOJ']);
				if($monthOfJoining[2] == date("Y"))
				{
					
				$monthOfJoining = $monthOfJoining[0];
				$monthOfJoiningNext = $monthOfJoining;
				if($monthOfJoiningNext > 12)
				{
					$monthOfJoiningNext = $monthOfJoiningNext -12;
				}
				$restMonthforLeave =  12-$monthOfJoiningNext;
				$restMonthforLeaveFinal = $restMonthforLeave+1;
				$leaveTotal = $restMonthforLeaveFinal*2.5;
				$aLMod->emp_id = $_leave['empId'];
				$aLMod->total_leave = $leaveTotal;
				$aLMod->remaining_leave = $leaveTotal;
				$aLMod->DOJ = $_leave['DOJ'];
				$aLMod->monthly_salary = $_leave['monthly_salary'];
				$aLMod->settlement_status = 1;
				$aLMod->leave_taken = 0;
				$aLMod->leave_taken_cash = 0;
				$aLMod->leave_taken_carry = 0;
				$aLMod->year = date("Y");
				$aLMod->save();
				}
				else
				{
					$aLMod->emp_id = $_leave['empId'];
				$aLMod->total_leave = 30;
				$aLMod->remaining_leave = 30;
				$aLMod->DOJ = $_leave['DOJ'];
				$aLMod->monthly_salary = $_leave['monthly_salary'];
				$aLMod->settlement_status = 1;
				$aLMod->leave_taken = 0;
				$aLMod->leave_taken_cash = 0;
				$aLMod->leave_taken_carry = 0;
				$aLMod->year = date("Y");
				$aLMod->save();
				}
				
			}
		}
		
		/*
		*calculate Leave for year of each employee
		*/
		echo "process done";
		exit;
	}
       
}
