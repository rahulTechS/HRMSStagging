<?php

namespace App\Http\Controllers\PayRoll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\Job\JobOpening;
use App\Models\Job\JobOpeningTarget;
use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use App\Models\Employee\EmployeeAttendanceModel;
use App\Models\Payroll\AnnualLeaveDetails;
use App\Models\Payroll\AnnualLeave;
use App\Models\Payroll\CashLeavePayment;
use App\Models\Payroll\AlLeaveSetting;
use App\Models\Entry\Employee;
use App\Models\Employee\EmployeeAttendance;


class LeaveController extends Controller
{
    
       public function annualLeaveSetting()
	   {
			$departmentDetails = Department::where("status",1)->get();
		    return view("PayRoll/Leave/annualLeaveSetting",compact('departmentDetails'));
	   }
	   public function searchme()
	   {
		    return view("PayRoll/Leave/searchme");
	   }
	   
	   public function getEmployeeData(Request $request)
	   {
		   $deptId = $request->deptId;
		   $employeeDetail = Employee_details::where("dept_id",$deptId )->get();
		    return view("PayRoll/Leave/getEmployeeData",compact('employeeDetail'));
	   }
	   public function getEmployeeAnnualLeaveDetails(Request $request)
	   {
		   $empId = $request->empId;
		   $currentYear = date("Y");
		   $annualLeaveDetails = AnnualLeave::where("emp_id",$empId)->where("year",$currentYear)->where("settlement_status",1)->first();
		    return view("PayRoll/Leave/getEmployeeAnnualLeaveDetails",compact('annualLeaveDetails','currentYear'));
	   }
	   
	   public static function getEmployeeNameById($empid)
	   {
		  $empData =  Employee_details::where("id",$empid)->first();
		  return $empData->first_name.'&nbsp;'.$empData->middle_name.'&nbsp;'.$empData->last_name;
	   }
	   
	   public static function getAmtAsPerRemainingLeave($remainingL,$montlySalary)
	   {
		   $annualPackage = $montlySalary*12;
		   $perdaySalary = round($annualPackage/365,2);
		   if($remainingL == 30)
			    return $montlySalary.' AED';
		   else
		   return round($perdaySalary*$remainingL,2).' AED';
	   }
	    public static function getPerDaySalary($montlySalary)
	   {
		   $annualPackage = $montlySalary*12;
		   $perdaySalary = round($annualPackage/365,2);
		   return round($perdaySalary,2);
	   }
	   
	   
	   public function detailAnnualLeave(Request $request)
	   {
		   $empId = $request->empId;
		   $annualLeaveDetails = AnnualLeaveDetails::where("emp_id",$empId)->orderBy("id","DESC")->get();
		   return view("PayRoll/Leave/detailAnnualLeave",compact('annualLeaveDetails'));
	   }
	   
	   public static function getUserNameById($userId)
	   {
		   return Employee::where("id",$userId)->first()->fullname;
	   }
	   
	   public function completeHistroyYearVise(Request $request)
	   {
			  $empId = $request->empId;
			  $annualLeaveDetails = AnnualLeave::where("emp_id",$empId)->orderBy("id","DESC")->get();
			  return view("PayRoll/Leave/annual_leave_histroy",compact('annualLeaveDetails'));
	   }
	   public function manageAL(Request $request)
	   {
		    $empId = $request->empId;
			$currentYear = date("Y");
			$annualLeaveDetails = AnnualLeave::where("emp_id",$empId)->where("year",$currentYear)->where("settlement_status",1)->first();
			return view("PayRoll/Leave/getManageAL",compact('annualLeaveDetails','currentYear'));
	   }
	   
	   public function doCashleave(Request $request)
	   {
		   $parameterInput = $request->input();
			
		   $cash_leave_no = $parameterInput['cash_leave_no'];
		   $leaveId = $parameterInput['leave_id'];
		   $leavedata = AnnualLeave::where("id",$leaveId)->first();
		   $updateLeave = AnnualLeave::find($leaveId);
		   $updateLeave->leave_taken_cash = $leavedata->leave_taken_cash+$cash_leave_no;
		   $updateLeave->remaining_leave = $leavedata->remaining_leave-$cash_leave_no;
		   $updateLeave->save();
		   /*
		   *making payment ship
		   */
		   $cashPaymentModel = new CashLeavePayment();
		   $cashPaymentModel->leave_id =  $leaveId;
		   $cashPaymentModel->amt =  $parameterInput['amtforcash'];
		   $cashPaymentModel->cash_no =  $cash_leave_no;
		   $cashPaymentModel->handle_by =  $request->session()->get('EmployeeId');
		   $cashPaymentModel->status =  1;
		   $cashPaymentModel->save();
		   /*
		   *making payment ship
		   */
		   $request	->session()->flash('message','annual leave requested successfully for cash. Use "Cash Slip " button for status.');
		   return  redirect()->back();
	   }
	   
	   public function cashSlipDetails(Request $request)
	   {
		   $leaveId = $request->leave_id;
		   $cashPaymentDetails =  CashLeavePayment::where("leave_id",$leaveId)->orderBy("id","DESC")->get();
		   return view("PayRoll/Leave/cashSlipDetails",compact('cashPaymentDetails'));
	   }
	   
	   public function SettingAL()
	   {
		   $alLeaveData = AlLeaveSetting::where("id",1)->first();
		   return view("PayRoll/Leave/SettingAL",compact('alLeaveData'));
	   }
	   
	   public function doAlSetting(Request $request)
	   {
		   $requestParameter = $request->input();
		   $id = $requestParameter['id'];
		   $leave_per_month = $requestParameter['leave_per_month'];
			$alMod = AlLeaveSetting::find($id);	
			$alMod->leave_per_month = $leave_per_month;
			$alMod->save();
			$request	->session()->flash('message','annual leave Setting Updated.');
		   return  redirect()->back();
	   }
	   public function EmpDepartment($eId)
		{
			$emp = Employee_details::where("id",$eId)->first();
			$dept_id = $emp->dept_id;
			$dMod = Department::where('id',$dept_id)->first();
			return $dMod->department_name;
		}
       public function lApprovalSetting(Request $request)
	   {
		    $eid = $request->eid;
			
			
			/*
			*Leave Details Code
			*/
			$empdetails = new Employee_details();
			$empdetailsListing = $empdetails->where("id",$eid)->first();
			$_departmentEmp = $this->EmpDepartment($eid);
			$employeeDetails['name'] =  $empdetailsListing->first_name.' '.$empdetailsListing->last_name;
			$employeeDetails['department'] =  $_departmentEmp;
			$employeeDetails['selectFrom'] =  '';
			$employeeDetails['selectTo'] =  '';
			
			$employeeDetailsAsPerSelectedDates = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_approved",1)->orderBy("id",'DESC')->get();
			
			
			$totalLeaveTaken  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_approved",2)->count();
			$leaveTypeCount = array();
			$leaveTypeCount['casual_leave']  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_type","casual_leave")->where("leave_approved",2)->count();
			$leaveTypeCount['annual_leave']  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_type","annual_leave")->where("leave_approved",2)->count();
			$leaveTypeCount['sick_leave']  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_type","sick_leave")->where("leave_approved",2)->count();
			$leaveTypeCount['public_holiday']  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_type","public_holiday")->where("leave_approved",2)->count();
			$leaveTypeCount['emergency_leave']  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_type","emergency_leave")->where("leave_approved",2)->count();
			$leaveTypeCount['half_day']  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_type","half_day")->where("leave_approved",2)->count();
			
			/*
			*Leave Details Code
			*/
		   return view("PayRoll/Leave/lApprovalSetting",compact('employeeDetails','employeeDetailsAsPerSelectedDates','totalLeaveTaken','leaveTypeCount'));
	   }
	   
	   public function lApproval()
	   {
		   $departmentDetails = Department::where("status",1)->get();
		   
		   return view("PayRoll/Leave/lApproval",compact('departmentDetails'));
	   }
	   
	   public function createPreHistory(Request $request)
	   {
		   $emp_id = $request->emp_id;
		   $currentYear = date("Y");
		   $annualLeaveDetails = AnnualLeave::where("emp_id",$emp_id)->where("year",$currentYear)->where("settlement_status",1)->first();
		   /*
		   *Check Leave data created
		   *From DOJ
           *start code		   
		   */
		   $leaveDetailsAsPerYear = array();
		   $joiningDate = $annualLeaveDetails->DOJ;
					 $joiningDateArr = explode("/",$joiningDate);
					 
					 $year = $joiningDateArr[2];
					 $currentYear = date("Y");
		    
			 for($i=$currentYear;$i>=$year;$i--)
				{
					 $annualLeaveDetailsYearVise = AnnualLeave::where("emp_id",$emp_id)->where("year",$i)->first();
					 if( $annualLeaveDetailsYearVise != '')
					 {
						 $leaveDetailsAsPerYear[$i]['year'] = $i;		
						 $leaveDetailsAsPerYear[$i]['total_leave'] = $annualLeaveDetailsYearVise->total_leave;		
						 $leaveDetailsAsPerYear[$i]['leave_taken'] = $annualLeaveDetailsYearVise->leave_taken;			
						 $leaveDetailsAsPerYear[$i]['leave_taken_cash'] = $annualLeaveDetailsYearVise->leave_taken_cash;		
						 $leaveDetailsAsPerYear[$i]['leave_taken_carry'] = $annualLeaveDetailsYearVise->leave_taken_carry;	
						 $leaveDetailsAsPerYear[$i]['remaining_leave'] = $annualLeaveDetailsYearVise->remaining_leave;	
						 $leaveDetailsAsPerYear[$i]['monthly_salary'] = $annualLeaveDetailsYearVise->monthly_salary;
						 $leaveDetailsAsPerYear[$i]['status'] = 1;
					 }
					 else
					 {
						 $leaveDetailsAsPerYear[$i]['year'] = $i;		
						 $leaveDetailsAsPerYear[$i]['total_leave'] = "No Data";		
						 $leaveDetailsAsPerYear[$i]['leave_taken'] = "No Data";			
						 $leaveDetailsAsPerYear[$i]['leave_taken_cash'] = "No Data";		
						 $leaveDetailsAsPerYear[$i]['leave_taken_carry'] = "No Data";		
						 $leaveDetailsAsPerYear[$i]['remaining_leave'] = "No Data";	
						 $leaveDetailsAsPerYear[$i]['monthly_salary'] = "No Data";	
						 $leaveDetailsAsPerYear[$i]['status'] = 2;
					 }
				  	
				}
		   /*
		   *Check Leave data created
		   *From DOJ
           *end code
		   */
		  
		   
		   return view("PayRoll/Leave/createPreHistory",compact('annualLeaveDetails','leaveDetailsAsPerYear'));
	   }
	   
	   public function leaveDataCreation(Request $request)
	   {
		   $emp_id = $request->emp_id;
		   $year = $request->year;
		   $empDetails = Employee_details::where("id",$emp_id)->first();
		  /*  echo '<pre>';
		   print_r($empDetails);
		   exit; */
		   $empLeaveDetails = AnnualLeave::where("emp_id",$emp_id)->where("year",$year)->first();
		   return view("PayRoll/Leave/leaveDataCreation",compact('empDetails','empLeaveDetails','year'));
	   }
	   
	   function getDatesFromRange($start, $end) {
      

  
    // Use loop to store date into array
    while(date("Y-m-d",strtotime($start)) <= date("Y-m-d",strtotime($end))) {  
	
		 $dayName =  date('D', strtotime($start));
		if($dayName != 'Sun')
		{
        $array[] = date("d-m-Y",strtotime($start)); 
		
		}
		$start = date('d-m-Y', strtotime($start . ' +1 day'));
	}
  
    // Return the array elements
    return $array;
}


function getDatesFromRangeOppsite($start, $end) {
      

  
    // Use loop to store date into array
    while(date("Y-m-d",strtotime($start)) <= date("Y-m-d",strtotime($end))) {  
	
		 $dayName =  date('D', strtotime($start));
		if($dayName != 'Sun')
		{
        $array[] = date("Y-m-d",strtotime($start)); 
		
		}
		$start = date('d-m-Y', strtotime($start . ' +1 day'));
	}
  
    // Return the array elements
    return $array;
}
	   public function updateAnnualLeave(Request $request)
	   {
		   $inputParameters = $request->input();
			 /*   echo '<pre>';
			   print_r($inputParameters); */
			  /*  exit; */
			   /*
			   *get Remaining Leave
			   *start code
			   */
			   $emp_id = $inputParameters['updateLeave']['emp_id'];
			   $year = $inputParameters['updateLeave']['year'];
			   $redirect_type = $inputParameters['updateLeave']['redirect_type'];
			   $annualLeaveDataMainMod = AnnualLeave::where("emp_id",$emp_id)->where("year",$year)->first();
			   $remainingLeave = $annualLeaveDataMainMod->remaining_leave;
			   $annualLeaveId = $annualLeaveDataMainMod->id;
			   
			   if($remainingLeave != 0 && !empty($remainingLeave))
			   {
				   $fromDate = $inputParameters['updateLeave']['selectFrom'];
				   $toDate = $inputParameters['updateLeave']['selectTo'];
				   $dateArray = $this->getDatesFromRange( $fromDate,$toDate);
				   
				   $daysOfAnnualLeave = count($dateArray);
				   if($daysOfAnnualLeave >$remainingLeave)
				   {
					   $request->session()->flash('error',"Annual Leave Count cann't be greater than remaining leave.");
					   return  redirect()->back();
				   }
				   else
				   {
					   /*
					   * start Marking Annual Leave in Attendance Sheet
					   * Start Coding
					   */
						
						
						/*employee department*/
						$employeeDepartmentId = Employee_details::where("id",$emp_id)->first()->dept_id;
						/* echo $employeeDepartmentId;
						exit; */
						$dateArray = array();
						$fromDateM = date("Y-m-d",strtotime($inputParameters['updateLeave']['selectFrom']));
						$toDateM = date("Y-m-d",strtotime($inputParameters['updateLeave']['selectTo']));
						$dateArray = $this->getDatesFromRangeOppsite($fromDateM,$toDateM);
						
						$existDateAttendance = array();
						$issueToMarkAttendance = array();
						$markAttendanceCount= 0;
 						foreach($dateArray as $dateOfLeave)
						{
							$employeeAttanceExistCheck = EmployeeAttendance::where("emp_id",$emp_id)->where("attendance_date",$dateOfLeave)->first();
							if($employeeAttanceExistCheck == '')
							{
								$employeeAttendance = new EmployeeAttendance();
								$employeeAttendance->dept_id = $employeeDepartmentId;
								$employeeAttendance->emp_id = $emp_id;
								$employeeAttendance->attendance_date = $dateOfLeave;
								$employeeAttendance->mark_attendance = 'leave';
								$employeeAttendance->leave_type = 'annual_leave';
								$employeeAttendance->leave_approved = 2;
								$employeeAttendance->created_by = $request->session()->get('EmployeeId');
								$employeeAttendance->over_ride_sandwich = 0;
								if($employeeAttendance->save())
								{
								/* making Histroy*/
								 $annualLeaveData = new AnnualLeaveDetails;
								 $annualLeaveData->emp_id = $emp_id;
								 $annualLeaveData->leave_date = $dateOfLeave;
								 $annualLeaveData->approved_by = $request->session()->get('EmployeeId');
								 $annualLeaveData->save();
								 $markAttendanceCount++;
								/* making Histroy*/
								}
								else
								{
									 $issueToMarkAttendance[] = date("d M Y",strtotime($dateOfLeave));
								}
							}
							else
							{
									 $existDateAttendance[] = date("d M Y",strtotime($dateOfLeave));
							}
							
						}
						
						if($markAttendanceCount > 0)
						{
							$updateAnnualLeaveOBJ = AnnualLeave::find($annualLeaveId);
							$existTakenLeave = $annualLeaveDataMainMod->leave_taken;
							$newTakenLeave = $existTakenLeave+$markAttendanceCount;
							$updateAnnualLeaveOBJ->leave_taken = $newTakenLeave;
							$existRemainingLeave = $annualLeaveDataMainMod->remaining_leave;
							$newRemainingLeave = $existRemainingLeave-$markAttendanceCount;
							$updateAnnualLeaveOBJ->remaining_leave = $newRemainingLeave;
							$updateAnnualLeaveOBJ->save();
						}
						if($markAttendanceCount > 0)
						{
							if(count($issueToMarkAttendance) == 0 && count($existDateAttendance) == 0)
							{
							 $request->session()->flash('success',"All Annual Leave Updated.");
							}
							else if(count($issueToMarkAttendance) > 0 && count($existDateAttendance) == 0)
							{
								$request->session()->flash('success',"Some Annual Leave Updated.");
								$request->session()->flash('error',implode(",",$issueToMarkAttendance)." these dates are not marked.");
							}
							else if(count($issueToMarkAttendance) == 0 && count($existDateAttendance) > 0)
							{
								$request->session()->flash('success',"Some Annual Leave Updated.");
								$request->session()->flash('error',implode(",",$existDateAttendance)." these dates are already marked.");
							}
							else
							{
								$request->session()->flash('error',implode(",",$issueToMarkAttendance)." these dates are not marked. ".implode(",",$existDateAttendance)." these dates are already marked.");
							}
							 
						}
						else if($markAttendanceCount==  0)
						{
							if(count($issueToMarkAttendance) == 0 && count($existDateAttendance) == 0)
							{
							 $request->session()->flash('error',"issue marked in attendance.");
							}
							else if(count($issueToMarkAttendance) > 0 && count($existDateAttendance) == 0)
							{
								
								$request->session()->flash('error',implode(",",$issueToMarkAttendance)." these dates are not marked.");
							}
							else if(count($issueToMarkAttendance) == 0 && count($existDateAttendance) > 0)
							{
								
								$request->session()->flash('error',implode(",",$existDateAttendance)." these dates are already marked.");
							}
							else
							{
								$request->session()->flash('error',implode(",",$issueToMarkAttendance)." these dates are not marked. ".implode(",",$existDateAttendance)." these dates are already marked.");
							}
						}
						/*employee department*/
					   
					   /*
					   * start Marking Annual Leave in Attendance Sheet
					   * End Coding
					   */
					  
					   if( $redirect_type == 'SC')
						   {
								
								return  redirect()->back();
						   }
						   else
						   {
								
								return redirect('createPreHistory/'.$emp_id);
						   }
				   }
			   }
			   else
			   {
				    $request->session()->flash('error',"there are no remaining leave.");
					   return  redirect()->back();
			   }
			   /*
			   *get Remaining Leave
			   *end code
			   */
	   }
	   
	   public function updateAnnualTotalLeave(Request $request)
	   {
		     $inputParameters = $request->input();
			   
			   $totalLeaveNew = $inputParameters['updateLeaveTotal']['total_leave'];
			   $emp_id = $inputParameters['updateLeaveTotal']['emp_id'];
			   $redirect_type = $inputParameters['updateLeaveTotal']['redirect_type'];
			   $year = $inputParameters['updateLeaveTotal']['year'];
			   /*
			   *get Id and TotalLeave Existing
			   *start Coding
			   */
			   $annualLeaveData = AnnualLeave::where("emp_id",$emp_id)->where("year",$year)->first();
			   if($annualLeaveData != '')
			   {
				   $totalLeave = $annualLeaveData->total_leave;
				   if($totalLeaveNew>$totalLeave)
				   {
					  $interval =  $totalLeaveNew-$totalLeave;
					  $remaining_leave = $annualLeaveData->remaining_leave;
					  $newRemainingLeave = $remaining_leave+$interval;
				   }
				   elseif($totalLeaveNew<$totalLeave)
				   {
					    $interval =  $totalLeave-$totalLeaveNew;
						$remaining_leave = $annualLeaveData->remaining_leave;
						$newRemainingLeave = $remaining_leave-$interval;
				   }
				   else
				   {
						$newRemainingLeave  =  $annualLeaveData->remaining_leave;
				   }
				    $annualLeaveObj = AnnualLeave::find($annualLeaveData->id);
					 $annualLeaveObj->total_leave = $totalLeaveNew;
					 $annualLeaveObj->remaining_leave = $newRemainingLeave;
					 $annualLeaveObj->save();
			   }
			   else
			   {
				     $annualLeaveDataExist = AnnualLeave::where("emp_id",$emp_id)->first();
				     $newRemainingLeave = $totalLeaveNew;
				     $annualLeaveObj = new AnnualLeave();
					 $annualLeaveObj->total_leave = $totalLeaveNew;
					 $annualLeaveObj->remaining_leave = $newRemainingLeave;
					 $annualLeaveObj->year = $year;
					 $annualLeaveObj->emp_id = $emp_id;
					 $annualLeaveObj->leave_taken = 0;
					 $annualLeaveObj->leave_taken_cash = 0;
					 $annualLeaveObj->leave_taken_carry = 0;
					 $annualLeaveObj->settlement_status = 1;
					 $annualLeaveObj->monthly_salary = $annualLeaveDataExist->monthly_salary;
					 $annualLeaveObj->DOJ = $annualLeaveDataExist->DOJ;
					 $annualLeaveObj->save();
			   }
			   if( $redirect_type == 'SC')
			   {
				    $request->session()->flash('message','Leave Data Updated.');
					return  redirect()->back();
			   }
			   else
			   {
				    $request->session()->flash('message','Leave Data Updated.');
					return redirect('createPreHistory/'.$emp_id);
			   }
			    /*
			   *get Id and TotalLeave Existing
			   *end Coding
			   */
			  
			  
	   }
	   
	   public function updateAnnualLeaveCash(Request $request)
	   {
		   $inputParameters = $request->input();
		   $leaveId = $inputParameters['updateLeave']['leave_id'];
		   $cash_leave_no = $inputParameters['updateLeave']['cashLeave'];
		   $redirect_type = $inputParameters['updateLeave']['redirect_type'];
		   $emp_id = $inputParameters['updateLeave']['emp_id'];
		  
		  
		   
		   
		   
		    $leavedata = AnnualLeave::where("id",$leaveId)->first();
		   $updateLeave = AnnualLeave::find($leaveId);
		   $updateLeave->leave_taken_cash = $leavedata->leave_taken_cash+$cash_leave_no;
		   $updateLeave->remaining_leave = $leavedata->remaining_leave-$cash_leave_no;
		   $updateLeave->save();
		   /*
		   *making payment ship
		   */
		   $cashPaymentModel = new CashLeavePayment();
		   $cashPaymentModel->leave_id =  $leaveId;
		   $cashPaymentModel->amt =  $inputParameters['updateLeave']['amtforcash'];
		   $cashPaymentModel->cash_no =  $cash_leave_no;
		   $cashPaymentModel->handle_by =  $request->session()->get('EmployeeId');
		   $cashPaymentModel->status =  1;
		   $cashPaymentModel->save();
		   /*
		   *making payment ship
		   */
		   $request	->session()->flash('message','annual leave requested successfully for cash. Use "Cash Slip " button for status.');
		  

			if( $redirect_type == 'SC')
			   {
				  
					return  redirect()->back();
			   }
			   else
			   {
				  
					return redirect('createPreHistory/'.$emp_id);
			   }
		  return  redirect()->back();
		  
	   }
	   
	   public function updateAnnualLeaveCarry(Request $request)
	   {
		    $inputParameters = $request->input();
			
			 $leaveId = $inputParameters['updateLeave']['leave_id'];
		   $carryLeave = $inputParameters['updateLeave']['carryLeave'];
		   $redirect_type = $inputParameters['updateLeave']['redirect_type'];
		   $emp_id = $inputParameters['updateLeave']['emp_id'];
		   
		    $leavedata = AnnualLeave::where("id",$leaveId)->first();
		   $updateLeave = AnnualLeave::find($leaveId);
		   $updateLeave->leave_taken_carry = $leavedata->leave_taken_carry+$carryLeave;
		   $updateLeave->remaining_leave = $leavedata->remaining_leave-$carryLeave;
		   $updateLeave->save();
		   $request	->session()->flash('message','annual leave moved successfully to Carry leave.');
		  

			if( $redirect_type == 'SC')
			   {
				  
					return  redirect()->back();
			   }
			   else
			   {
				  
					return redirect('createPreHistory/'.$emp_id);
			   }
		
	   }
	   
	  public function leaveDataView(Request $request)
	  {
		  $year = $request->year;
		  $emp_id = $request->emp_id;
		  /*
		  * Leave taken records
		  * Start code
		  */
		  $employeeAttendanceDetails = EmployeeAttendance::where("emp_id",$emp_id)->whereYear('attendance_date',$year)->get();
	      $empDetails = Employee_details::where("id",$emp_id)->first();
		  /*
		  * Leave taken records
		  * Start code
		  */
		  $annualLeaveDetails = AnnualLeave::where("emp_id",$emp_id)->where('year',$year)->first();
		  $leaveId = $annualLeaveDetails->id;
		  
		  /*
		  * cash Annual Leave 
		  * start coding
		  */
		  $cashLeavePaymentDetails = CashLeavePayment::where("leave_id",$leaveId)->get();
		  /*
		  * cash Annual Leave 
		  * end coding
		  */
		  return view("PayRoll/Leave/leaveDataView",compact('employeeAttendanceDetails','year','empDetails','emp_id','leaveId','cashLeavePaymentDetails','annualLeaveDetails')); 
	  }
	  
	  public function removeAnnualLeaveTaken(Request $request)
	  {
		  $attendanceId = $request->attendanceId;
		  $leaveId = $request->leaveId;
		  EmployeeAttendance::find($attendanceId)->delete();
		  $annualLeaveDetails = AnnualLeave::where("id",$leaveId)->first();
		  $leaveTaken = $annualLeaveDetails->leave_taken;
		  $remaining_leave = $annualLeaveDetails->remaining_leave;
		  $finalLeaveTaken = $leaveTaken-1;
		  $finalRemainingLeave = $remaining_leave+1;
		  $annualUpdateObj = AnnualLeave::find($leaveId);
		  $annualUpdateObj->leave_taken = $finalLeaveTaken;
		  $annualUpdateObj->remaining_leave = $finalRemainingLeave;
		  $annualUpdateObj->save();
		  $request->session()->flash('success','Remove Annual Leave from records.');
		  return  redirect()->back();
	  }
	  
	  public function removeAnnualLeaveCash(Request $request)
	  {
		  $cashPaymentId = $request->cashPaymentId;
		  $detailsCashLeave = CashLeavePayment::where("id",$cashPaymentId)->first();
		  CashLeavePayment::find($cashPaymentId)->delete();
		  $leaveId = $detailsCashLeave->leave_id;
		  $cash_no = $detailsCashLeave->cash_no;
		  $annualLeaveDetails = AnnualLeave::where("id",$leaveId)->first();
		  $leave_taken_cash = $annualLeaveDetails->leave_taken_cash;
		  $remaining_leave = $annualLeaveDetails->remaining_leave;
		   $finalLeaveTakenCash = $leave_taken_cash-$cash_no;
		  $finalRemainingLeave = $remaining_leave+$cash_no;
		  $annualUpdateObj = AnnualLeave::find($leaveId);
		  $annualUpdateObj->leave_taken_cash = $finalLeaveTakenCash;
		  $annualUpdateObj->remaining_leave = $finalRemainingLeave;
		  $annualUpdateObj->save();
		  $request->session()->flash('success','Remove Annual Cash Leave from records.');
		  return  redirect()->back();
	  }
	  
	  public function removeCarryLeave(Request $request)
	  {
		   $cancelCarryLeave = $request->cancelCarryLeave;
		  $leaveId = $request->leaveId;
		  
		  $annualLeaveDetails = AnnualLeave::where("id",$leaveId)->first();
		  $leave_taken_carry = $annualLeaveDetails->leave_taken_carry;
		  $remaining_leave = $annualLeaveDetails->remaining_leave;
		   $finalLeaveTakenCarry = $leave_taken_carry-$cancelCarryLeave;
		  $finalRemainingLeave = $remaining_leave+$cancelCarryLeave;
		  $annualUpdateObj = AnnualLeave::find($leaveId);
		  $annualUpdateObj->leave_taken_carry = $finalLeaveTakenCarry;
		  $annualUpdateObj->remaining_leave = $finalRemainingLeave;
		  $annualUpdateObj->save();
		  $request->session()->flash('success','Remove Annual Carry Leave from records.');
		  return  redirect()->back();
	  }
	  
	  public function updateAnnualSalary(Request $request)
	  {
		  $parameters = $request->input();
		
		  $redirect_type = $parameters['updateSalary']['redirect_type'];
		  $emp_id = $parameters['updateSalary']['emp_id'];
		  $leave_id = $parameters['updateSalary']['leave_id'];
		  $monthly_salary = $parameters['updateSalary']['monthly_salary'];
		   $annualUpdateObj = AnnualLeave::find($leave_id);
		    $annualUpdateObj->monthly_salary = $monthly_salary;
			$annualUpdateObj->save();
		   if($redirect_type == 'SC')
			   {
				    $request->session()->flash('message','Leave Data Updated.');
					return  redirect()->back();
			   }
			   else
			   {
				    $request->session()->flash('message','Leave Data Updated.');
					return redirect('createPreHistory/'.$emp_id);
			   }
	  }
}
