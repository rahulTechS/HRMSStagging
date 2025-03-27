<?php

namespace App\Http\Controllers\DepartmentAttendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\DepartmentForm;

use App\Models\Attribute\FormProduct;
use App\Models\Attribute\MasterAttribute;
use App\Models\Attribute\AttributeType;
use App\Models\Attribute\FormSection;


use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Http\Controllers\Attribute\DepartmentFormController;

use App\Models\Bank\SCB\SCBDepartmentFormChildEntry;

use App\Models\Bank\SCB\SCBDepartmentFormParentEntry;
use App\Models\Bank\SCB\SCBImportFile;
use App\Models\Bank\SCB\SCBBankMis;
use App\Models\Dashboard\MasterPayout;
use App\Models\SEPayout\RangeDetailsVintage;
use App\Models\Recruiter\Designation;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\EmpProcess\JobFunctionPermission;
use App\Models\Employee_Attendance\EmpAttendance;
use App\Models\Employee_Attendance\Attendance;
use Illuminate\Support\Facades\Validator;
use App\Models\EmpOffline\EmpOffline;
use App\Models\Employee_Attendance\EmpAttendanceCron;
use App\Models\Employee_Attendance\EmpAttendance2;
use DateTime;
use App\Models\Employee_Attendance\AttendanceUserLog;
use App\User;
use App\Models\Employee_Leaves\RequestedLeaves;
use App\Models\Employee_Leaves\LeaveTypes;
use App\Models\Employee_Attendance\HolidayList;




use Session;

class DepartmentAttendanceMarkController extends Controller
{
	public  function departmentMarkAttendance(Request $request)
	{
		$loggedinUserid=$request->session()->get('EmployeeId');
        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        if($departmentDetails != '')
        {
            $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
            if($empDetails!='')
            {
                $empData = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
            }
        }
        else
        {
            $empData = Employee_details::orderBy('id','desc')->take(5)->get();
        }

		return view("DepartmentAttendance/departmentMarkAttendance",compact('empData'));
	}

	public static function getDepartment($empid)
	{
		$empDetails = Employee_details::where("emp_id",$empid)->orderBy('id','desc')->first();
		
		if($empDetails)
		{
			$departmentDetails = Department::where("id",$empDetails->dept_id)->first();
			if($departmentDetails != '')
			{
				return $departmentDetails->department_name;
			}
			else{
				 return '--'; 
			}

		}
		else{
			return '--';
		}
	  
	}

	public static function getDesignation($empid)
	{
		$empDetails = Employee_details::where("emp_id",$empid)->orderBy('id','desc')->first(); 
		//return $empDetails;

		if($empDetails)
		{
			$designationDetails = Designation::where("id",$empDetails->designation_by_doc_collection)->first();
			if($designationDetails != '')
			{
				return $designationDetails->name;
			}
			else{
				 return '--'; 
			}
		}
		else{
			return '--';
		}
				  
	}


	public static function getTeamLeader($empid)
	{
		
		//return $empid;
		$empDetails = Employee_details::where("emp_id",$empid)->orderBy('id','desc')->first(); 


		if($empDetails)
		{
			$emp_details = Employee_details::where("id",$empDetails->tl_id)->first(); 
			if($emp_details!='')
			{
				return $emp_details->emp_name;
			}
			else
			{
				return "--";
			}
		}
		else
		{
			return '--';
		}
	 
		
		
	}

	public function getMarkAttendanceForm(Request $request)
	{
		
		$emp_id = $request->emp_id;
		$attendanceDate = $request->gdate;
		
		//return $attendanceDate;
		$attendanceData = Attendance::orderBy('id','desc')->get();
		return view("DepartmentAttendance/attendanceFormContent",compact('attendanceData','emp_id','attendanceDate'));
	}

	public function requestAttendancePostData(Request $request)
	{
		//return $request->all();

		

		$validator = Validator::make($request->all(), 
        [			
			'attendanceType' => 'required',
           
        ],
		[
			
            'attendanceType.required'=> 'Please Mark Attendance',
			
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			$usersessionId=$request->session()->get('EmployeeId');
			$attendanceDate = date("Y-m-d", $request->attendanceDate);
            $attendanceData = new EmpAttendance();
			$attendanceData->emp_id = $request->emp_id;
            $attendanceData->attribute_code = 'attendance';
            $attendanceData->attribute_value = $request->attendanceType;
            $attendanceData->attendance_date = $attendanceDate;
            $attendanceData->created_at = date('Y-m-d H:i:s');
            $attendanceData->status = 1;
			$attendanceData->attendance_mark_by = $usersessionId;
			$attendanceData->attendance_mark_on = date('Y-m-d');

           
            //$requestedLeaves->request_by = $usersessionId;
            $attendanceData->save(); 
           

            return response()->json(['success'=>'Attendance Marked Successfully.']);
			
		} 
	}

	public static function getAttendanceStatus($emp_id,$gDate)
	{
		$tdate = date('Y-m-d');
		$attendanceData = EmpAttendance::select('attribute_value')->where('emp_id',$emp_id)->where('attribute_code','attendance')->where('attendance_date',$gDate)->orderBy('id','desc')->first();

		

		if($attendanceData)
		{
			return $attendanceData->attribute_value;
		}
		else{
			return 1;
		}
	}

	public static function checkLastDay($emp_id)
	{
		$tdate = date('Y-m-d');
		$offlineEmpData = EmpOffline::where('emp_id',$emp_id)->orderBy('id','desc')->first();
		

		if($offlineEmpData)
		{
			if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
			{
				return $offlineEmpData->last_working_day_resign;
			}
			elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
			{
				return $offlineEmpData->last_working_day_terminate;
			}
			else
			{
				
				$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
				return $new_date;
			}
			
		}
		else{
			return "NA";
		}
	}
	

	// mass attendance start 16-2

	public function massAttendanceFormContent(Request $request)
	{
		$attendanceData = Attendance::orderBy('id','desc')->take(4)->get();

		$loggedinUserid=$request->session()->get('EmployeeId');
        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        if($departmentDetails != '')
        {
            $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
            if($empDetails!='')
            {
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();

					$tdate = date('Y-m-d');
					$lastworkingdate='';
					$offmarkEmp=array();
					foreach($empData_details as $emp)
					{
						if($emp->offline_status==2)
						{
								$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();							

								if($offlineEmpData)
								{
									if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
									{
										$lastworkingdate = $offlineEmpData->last_working_day_resign;
									}
									elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
									{
										$lastworkingdate = $offlineEmpData->last_working_day_terminate;
									}
									else
									{
										$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
										$lastworkingdate = $new_date;
									}							
								}

								if($lastworkingdate)
								{
									//return "Hello";
									if($tdate > $lastworkingdate)
									{
										//echo "Hide";
										$offmarkEmp[]=$emp->emp_id;
									}

								}
								

						}
						else
						{
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;
							}
							
						}
					}

					$empData = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')->get();
            }
        }
        else
        {
            
					$empData_details = Employee_details::orderBy('id','desc')
					->get();
					$tdate = date('Y-m-d');
					$lastworkingdate='';
					$offmarkEmp=array();
					foreach($empData_details as $emp)
					{
						if($emp->offline_status==2)
						{
								$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();							

								if($offlineEmpData)
								{
									if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
									{
										$lastworkingdate = $offlineEmpData->last_working_day_resign;
									}
									elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
									{
										$lastworkingdate = $offlineEmpData->last_working_day_terminate;
									}
									else
									{
										$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
										$lastworkingdate = $new_date;
									}							
								}

								if($lastworkingdate)
								{
									//return "Hello";
									if($tdate > $lastworkingdate)
									{
										//echo "Hide";
										$offmarkEmp[]=$emp->emp_id;
									}

								}
								

						}
						else
						{
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;
							}
							
						}
					}
			
			$empData = Employee_details::whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')->get();
        }

		$empData='';

		return view("DepartmentAttendance/massAttendanceform",compact('empData','attendanceData'));
	} 



	public function getEmpStatusonDateData(Request $request)
	{
		$attendanceData = Attendance::orderBy('id','desc')->take(4)->get();
		$tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();
		$parameters = $request->input();
		
		$selectedDate = $parameters['selectedDate'];
		

			$loggedinUserid=$request->session()->get('EmployeeId');

			$empData = $this->getLoggedinUser($loggedinUserid);
			if($empData==1)
			{
				$tdate = $selectedDate;

				$empData_details = Employee_details::orderBy('id','desc')->get();
				$lastworkingdate='';
				$offmarkEmp=array();
				foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							if($lastworkingdate < $tdate || $emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;
							}

						}
					}
					else
					{
						if($emp->doj)
						{
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;									
							}
						}
					}
				}

				$empData = Employee_details::whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				->get();

				
			}
			else
			{
				$tdate = $selectedDate;
				
				$departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
				$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
				$empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
				$lastworkingdate='';
				$offmarkEmp=array();
				foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							if($lastworkingdate < $tdate  || $emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;
							}
						}

					}
					else
					{
							if($emp->doj)
							{
								if($emp->doj > $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
							}
					}
				}
				

				$empData = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				->get();
			}
		return view("DepartmentAttendance/massEmpinfoContent",compact('empData','attendanceData','tL_details'));
	}


	public function massMarkAttendanceRequestPostData(Request $request)
	{
		$validator = Validator::make($request->all(), 
        [			
			'attendanceDate' => 'required|date',
			
			// "team_leader_emp"    => "required|array",
    		// "team_leader_emp.*"  => "required",     
        ],
		[
            'attendanceDate.required'=> 'Please Select Date',
			//'team_leader_emp.required'=> 'Please Select Team Leaders',
			// 'attendanceToDate.required'=> 'Please Select To Date',				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			
			//return $request->all();

			$usersessionId=$request->session()->get('EmployeeId');
			$request->team_leader_emp = array_filter($request->team_leader_emp);
			$empAttendanceData = Employee_details::whereIn('tl_id',$request->team_leader_emp)->orderBy('id','desc')->get();

			foreach($empAttendanceData as $emp)
			{
				$empAttendanceData = EmpAttendance::where('emp_id',$emp->emp_id)->where('attribute_code','attendance')->where('attendance_date',$request->attendanceDate)->orderBy('id','desc')->first();

				if($empAttendanceData)
				{
					$empAttendanceData = EmpAttendance::where('attendance_date',$request->attendanceDate)->where('emp_id',$emp->emp_id)->delete();


					$timestamp = strtotime($request->attendanceDate);
					$weakoffday = date('N', $timestamp);

					if($weakoffday==7)
					{
						continue;
					}
					
					$attendanceData = new EmpAttendance();
					$attendanceData->emp_id = $emp->emp_id;
					$attendanceData->attribute_code = 'attendance';
					$attendanceData->attribute_value = 'P';
					$attendanceData->attendance_date = $request->attendanceDate;
					$attendanceData->created_at = date('Y-m-d H:i:s');
					$attendanceData->status = 1;
					$attendanceData->attendance_mark_by = $usersessionId;
					$attendanceData->attendance_mark_on = date('Y-m-d');
					$attendanceData->save(); 
				}
				else
				{
					$timestamp = strtotime($request->attendanceDate);
					$weakoffday = date('N', $timestamp);

					if($weakoffday==7)
					{
						continue;
					}
					
					$attendanceData = new EmpAttendance();
					$attendanceData->emp_id = $emp->emp_id;
					$attendanceData->attribute_code = 'attendance';
					$attendanceData->attribute_value = 'P';
					$attendanceData->attendance_date = $request->attendanceDate;
					$attendanceData->created_at = date('Y-m-d H:i:s');
					$attendanceData->status = 1;
					$attendanceData->attendance_mark_by = $usersessionId;
					$attendanceData->attendance_mark_on = date('Y-m-d');
					$attendanceData->save(); 
				}
			}




			$emp_markoff = array_merge($request->team_leader_emp);

			// for other employees start
			
			// for other employees End

            return response()->json(['success'=>'Attendance Marked Successfully.']);
			
		} 
	}




	// mass attendance end 16-2



	public function massMarkAttendanceFormData(Request $request)
	{
		$attendanceData = Attendance::orderBy('id','desc')->get();
		$empid=array();
		foreach($request->selectedIds as $empid)
		{
			$empDetails = Employee_details::where("id",$empid)->orderBy('id','desc')->first();
			$empids[] = $empDetails->emp_id;			
		}

		return view("DepartmentAttendance/massAttendanceformContentPage",compact('empids','attendanceData'));
	} 


	public function massRequestAttendancePostData(Request $request)
	{
		$validator = Validator::make($request->all(), 
        [			
			'attendanceType' => 'required',
			'attendanceFromDate' => 'required|date',
			'attendanceToDate' => 'required|date',          
        ],
		[
            'attendanceType.required'=> 'Please Mark Attendance',
			'attendanceFromDate.required'=> 'Please Select From Date',
			'attendanceToDate.required'=> 'Please Select To Date',				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			$lastworkingdate='';
			$request_emp_ids = explode (",", $request->emp_id[0]);

			$usersessionId=$request->session()->get('EmployeeId');

			foreach($request_emp_ids as $emp)
			{
				for($attendancedate=$request->attendanceFromDate;$attendancedate<=$request->attendanceToDate;$attendancedate++)
				{
					$emp_details = Employee_details::where("emp_id",$emp)->first(); 
					

					if($emp_details)
					{
						if($emp_details->offline_status==2)
						{
							$offlineEmpData = EmpOffline::where('emp_id',$emp)->orderBy('id','desc')->first();	
							

							if($offlineEmpData)
							{
								if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
								{
									$lastworkingdate = $offlineEmpData->last_working_day_resign;
								}
								elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
								{
									$lastworkingdate = $offlineEmpData->last_working_day_terminate;
								}
								else
								{
									$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
									$lastworkingdate = $new_date;
								}
								
							}
							else{
								// return "NA";
							}
						}
						else
						{
							if($emp_details->doj <= $attendancedate)
							{
								
								$timestamp = strtotime($attendancedate);
								$weakoffday = date('N', $timestamp);

								if($weakoffday==7)
								{
									continue;
								}
								
								$attendanceData = new EmpAttendance();
								$attendanceData->emp_id = $emp;
								$attendanceData->attribute_code = 'attendance';
								$attendanceData->attribute_value = $request->attendanceType;
								$attendanceData->attendance_date = $attendancedate;
								$attendanceData->created_at = date('Y-m-d H:i:s');
								$attendanceData->status = 1;
								$attendanceData->attendance_mark_by = $usersessionId;
								$attendanceData->attendance_mark_on = date('Y-m-d');
								$attendanceData->save(); 
							}
							else
							{
								
								
							}
						}
					}
					
					


					if($lastworkingdate)
					{
						if($attendancedate > $lastworkingdate)
						{
							//echo "Hide";
						}
						else
						{
							
							$timestamp = strtotime($attendancedate);
							$weakoffday = date('N', $timestamp);

							if($weakoffday==7)
							{
								continue;
							}
							
							$attendanceData = new EmpAttendance();
							$attendanceData->emp_id = $emp;
							$attendanceData->attribute_code = 'attendance';
							$attendanceData->attribute_value = $request->attendanceType;
							$attendanceData->attendance_date = $attendancedate;
							$attendanceData->created_at = date('Y-m-d H:i:s');
							$attendanceData->status = 1;
							$attendanceData->attendance_mark_by = $usersessionId;
							$attendanceData->attendance_mark_on = date('Y-m-d');
							$attendanceData->save(); 
						}
					}
					else
					{
						
					}



					
					
					
					
					
					
				}
				
			}

            return response()->json(['success'=>'Attendance Marked Successfully.']);
			
		} 
	}

	public static function getLoggedinUser($loggedinUserid)
	{
        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        if($departmentDetails != '')
        {
            $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
            if($empDetails!='')
            {
				$employeeData=2;
            }
        }
        else
        {
			$employeeData=1;		
        }
		return $employeeData;
	}















	public  function Index(Request $request)
	{
		$loggedinUserid=$request->session()->get('EmployeeId');
        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        if($departmentDetails != '')
        {
            $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
            if($empDetails!='')
            {
                $empData = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
            }
        }
        else
        {
			$empData = Employee_details::orderBy('id','desc')->get();			
        }

		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$designation=Designation::where("status",1)->get();






		$empsessionId=$request->session()->get('EmployeeId');
		$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
		if($departmentDetails != '')
		{
			
			//return "Hello".$empDetails->dept_id;
			$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
			if($empDetails!='')
			{
				//return "Hello".$empDetails->dept_id;47
				$design=Designation::where("tlsm",2)->where("department_id",$empDetails->dept_id)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);
				
				$tL_details = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",$empDetails->dept_id)->where("offline_status",1)->get();
				
			}
		}
		else{
			

			$design=Designation::where("tlsm",2)->where("status",1)->get();
			$designarray=array();
			foreach($design as $_design){
				$designarray[]=$_design->id;
			}
			$finalarray=implode(",",$designarray);
			
			$tL_details = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->get();

		}





			// New code Start for user log
			$loggedinUserdetails = User::where('id',$empsessionId)->orderBy("id","DESC")->first();
			$loggedinEmpdetails = Employee_details::where('emp_id',$loggedinUserdetails->employee_id)->orderBy("id","DESC")->first();
			
				if($loggedinEmpdetails->dept_id != NULL)
				{
					$dept_id = $loggedinEmpdetails->dept_id;
				}
				else
				{
					$dept_id = NULL;
				}
				if($loggedinEmpdetails->designation_by_doc_collection != NULL)
				{
					$designation_id = $loggedinEmpdetails->designation_by_doc_collection;
				}
				else
				{
					$designation_id = NULL;
				}
				if($loggedinEmpdetails->job_role != NULL)
				{
					$job_role = $loggedinEmpdetails->job_role;
				}
				else
				{
					$job_role = NULL;
				}
	
				$attendanceUserLogs = new AttendanceUserLog();
				$attendanceUserLogs->userid = $empsessionId;
				$attendanceUserLogs->emp_id = $loggedinUserdetails->employee_id;
				$attendanceUserLogs->emp_name = $loggedinEmpdetails->emp_name;				
				$attendanceUserLogs->dept_id = $dept_id; 
				$attendanceUserLogs->designation_id = $designation_id;
				$attendanceUserLogs->job_role = $job_role;
				$attendanceUserLogs->enter_at = date('Y-m-d H:i:s');
				$attendanceUserLogs->created_at = date('Y-m-d H:i:s');            
				$attendanceUserLogs->save();

				// New code End for user log
			
				
		



		//$tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();

		return view("DepartmentAttendance/index",compact('empData','departmentLists','designation','tL_details'));
	}


	public function attendanceListingData(Request $request)
	{		
		$whereraw = '';
		$whereraw1 = '';
		$selectedFilter['CNAME'] = '';
		$selectedFilter['CEMAIL'] = '';
		$selectedFilter['DESC'] = '';
		$selectedFilter['DEPT'] = '';
		$selectedFilter['OPENING'] = '';
		$selectedFilter['STATUS'] = '';
		$selectedFilter['vintage'] = '';
		$selectedFilter['Company'] = '';
		$selectedFilter['Recruiter'] = '';
		
        
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';

        if(!empty($request->session()->get('attendance_page_limit')))
        {
            $paginationValue = $request->session()->get('attendance_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }


        if(!empty($request->session()->get('attendance_emp_name')) && $request->session()->get('attendance_emp_name') != 'All')
        {
            $fname = $request->session()->get('attendance_emp_name');
            $cnameArray = explode(",",$fname);
                
            $namefinalarray=array();
            foreach($cnameArray as $namearray){
                $namefinalarray[]="'".$namearray."'";                
            }
			



            $finalcname=implode(",", $namefinalarray);

			

            if($whereraw == '')
            {
                //$whereraw = 'emp_name like "%'.$fname.'%"';
               $whereraw = 'emp_name IN('.$finalcname.')';
            }
            else
            {
                $whereraw .= ' And emp_name IN('.$finalcname.')';
            }
			// echo $whereraw;
			// exit;
			
			if($whereraw=="emp_name IN('','','')" || $whereraw=="emp_name IN('','')")  
			{
				$whereraw='';
			}
        }

		//echo $whereraw;

        if(!empty($request->session()->get('attendance_emp_id')) && $request->session()->get('attendance_emp_id') != 'All')
        {
            $empId = $request->session()->get('attendance_emp_id');
            if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }

		if(!empty($request->session()->get('attendance_department_filter')) && $request->session()->get('attendance_department_filter') != 'All')
		{
			$dept = $request->session()->get('attendance_department_filter');
				//$departmentArray = explode(",",$dept);
			if($whereraw == '')
			{
				$whereraw = 'dept_id IN('.$dept.')';
			}
			else
			{
				$whereraw .= ' And dept_id IN('.$dept.')';
			}
		}



		if(!empty($request->session()->get('attendance_designation')) && $request->session()->get('attendance_designation') != 'All')
		{
			$designd = $request->session()->get('attendance_designation');
				//$departmentArray = explode(",",$designd);
			if($whereraw == '')
			{
				$whereraw = 'designation_by_doc_collection IN('.$designd.')';
			}
			else
			{
				$whereraw .= ' And designation_by_doc_collection IN('.$designd.')';
			}
		}

		if(!empty($request->session()->get('attendance_teamleader')) && $request->session()->get('attendance_teamleader') != 'All')
		{
			$teamlead = $request->session()->get('attendance_teamleader');
				//$departmentArray = explode(",",$dept);
			if($whereraw == '')
			{
				$whereraw = 'tl_id IN('.$teamlead.')';
			}
			else
			{
				$whereraw .= ' And tl_id IN('.$teamlead.')';
			}
		}





        if(!empty($request->session()->get('attendance_month_filter')) && $request->session()->get('attendance_month_filter') != 'All')
        {
            $datefrom = $request->session()->get('attendance_month_filter');
			//echo $whereraw;

			// $attendanceView = explode("-",$datefrom);
			// //print_r($attendanceView);

			// $month=$attendanceView[0];
			// $year=$attendanceView[1];



			// $empData = EmpAttendance::whereYear('attendance_date', '=', $year)
            // ->whereMonth('attendance_date', '=', $month)
            // ->get();
			// $empid=array();
			// foreach($empData as $emp)
			// {
			// 	$empid[] = $emp->emp_id;
			// }

			// if (empty($empid)) 
			// {
				
				
				
			// 	$finalempid=implode(",", $empid);

			


			
			// 	if($whereraw == '')
			// 	{
			// 		$whereraw = 'emp_id IN("'.$finalempid.'")';
					
			// 	}
			// 	else
			// 	{
			// 		$whereraw .= ' And emp_id IN('.$finalempid.')';
			// 	}
			// } 
			// else 
			// {
				

			// 	$finalempid=implode(",", $empid);

			


			
			// 	if($whereraw == '')
			// 	{
			// 		$whereraw = 'emp_id IN('.$finalempid.')';
					
			// 	}
			// 	else
			// 	{
			// 		$whereraw .= ' And emp_id IN('.$finalempid.')';
			// 	}


			// }
			


            
        }









		
        // if(!empty($request->session()->get('emp_leaves_todate')) && $request->session()->get('emp_leaves_todate') != 'All')
        // {
        //     $dateto = $request->session()->get('emp_leaves_todate');
        //     if($whereraw == '')
        //     {
        //         $whereraw = 'leaves_request.created_at<= "'.$dateto.' 00:00:00"';
        //     }
        //     else
        //     {
        //         $whereraw .= ' And leaves_request.created_at<= "'.$dateto.' 00:00:00"';
        //     }
        // }


		$loggedinUserid=$request->session()->get('EmployeeId');
        if($whereraw != '')
		{
			
			
			
			$empData = $this->getLoggedinUser($loggedinUserid);
			if($empData==1)
			{
				if(!empty($request->session()->get('attendance_month_filter')))
				{
					$attendanceView = explode("-",$datefrom);
					$month=$attendanceView[0];
					$year=$attendanceView[1];
					$tdate = $year.'-'.$month;
				}
				else
				{
					$tdate = date('Y-m-d');
				}

				$empData_details = Employee_details::orderBy('id','desc')->get();
				$lastworkingdate='';
				$offmarkEmp=array();
				foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							if(!empty($request->session()->get('attendance_month_filter')))
							{
								$attendanceView = explode("-",$lastworkingdate);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$lastworkdate = $year.'-'.$month;
								if($lastworkdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
																
							}
							else
							{
								
								
								if($lastworkingdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;
								}
								
							}

						}
					}
					else
					{
						if(!empty($request->session()->get('attendance_month_filter')))
						{
							if($emp->doj)
							{
								$attendanceView = explode("-",$emp->doj);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$joiningdate = $year.'-'.$month;
								if($joiningdate > $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
							}
							
							
						}
						else
						{
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;									
							}
							
						}
					}
				}
				
				$empData = Employee_details::whereRaw($whereraw)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				->paginate($paginationValue);

				$reportsCount = Employee_details::whereRaw($whereraw)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
                ->get()->count();
			}
			else
			{
				if(!empty($request->session()->get('attendance_month_filter')))
				{
					$attendanceView = explode("-",$datefrom);
					$month=$attendanceView[0];
					$year=$attendanceView[1];
					$tdate = $year.'-'.$month;
				}
				else
				{
					$tdate = date('Y-m-d');
				}

				$departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
				$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
				$empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
				$lastworkingdate='';
				$offmarkEmp=array();
				foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							if(!empty($request->session()->get('attendance_month_filter')))
							{
								$attendanceView = explode("-",$lastworkingdate);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$lastworkdate = $year.'-'.$month;
								if($lastworkdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
																
							}
							else
							{
								if($lastworkingdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;
								}
								
							}

						}

					}
					else
					{
						if(!empty($request->session()->get('attendance_month_filter')))
						{
							if($emp->doj)
							{
								$attendanceView = explode("-",$emp->doj);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$joiningdate = $year.'-'.$month;
								if($joiningdate > $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
							}
							
							
						}
						else
						{
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;									
							}
							
						}
					}
				}

				$empData = Employee_details::whereRaw($whereraw)->where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				->paginate($paginationValue);

				$reportsCount = Employee_details::whereRaw($whereraw)->where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
                ->get()->count();
			}
        }
        else
        {
			$empData = $this->getLoggedinUser($loggedinUserid);
			if($empData==1)
			{
				if(!empty($request->session()->get('attendance_month_filter')))
				{
					$attendanceView = explode("-",$datefrom);
					$month=$attendanceView[0];
					$year=$attendanceView[1];
					$tdate = $year.'-'.$month;
				}
				else
				{
					$tdate = date('Y-m-d');
				}

				$empData_details = Employee_details::orderBy('id','desc')->get();
				$lastworkingdate='';
				$offmarkEmp=array();
				foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							if(!empty($request->session()->get('attendance_month_filter')))
							{
								$attendanceView = explode("-",$lastworkingdate);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$lastworkdate = $year.'-'.$month;
								if($lastworkdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
																
							}
							else
							{
								if($lastworkingdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;
								}
								
							}

						}
					}
					else
					{
						if(!empty($request->session()->get('attendance_month_filter')))
						{
							if($emp->doj)
							{
								$attendanceView = explode("-",$emp->doj);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$joiningdate = $year.'-'.$month;
								if($joiningdate > $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
							}
							
							
						}
						else
						{
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;									
							}
							
						}
					}
				}

				$empData = Employee_details::whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				->paginate($paginationValue);

				$reportsCount = Employee_details::whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
                ->get()->count();
			}
			else
			{
				if(!empty($request->session()->get('attendance_month_filter')))
				{
					$attendanceView = explode("-",$datefrom);
					$month=$attendanceView[0];
					$year=$attendanceView[1];
					$tdate = $year.'-'.$month;
				}
				else
				{
					$tdate = date('Y-m-d');
				}

				$departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
				$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
				$empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
				$lastworkingdate='';
				$offmarkEmp=array();
				foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							if(!empty($request->session()->get('attendance_month_filter')))
							{
								$attendanceView = explode("-",$lastworkingdate);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$lastworkdate = $year.'-'.$month;
								if($lastworkdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
																
							}
							else
							{
								if($lastworkingdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;
								}
								
							}

						}

					}
					else
					{
						if(!empty($request->session()->get('attendance_month_filter')))
						{
							if($emp->doj)
							{
								$attendanceView = explode("-",$emp->doj);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$joiningdate = $year.'-'.$month;
								if($joiningdate > $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
							}
							
							
						}
						else
						{
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;									
							}
							
						}
					}
				}

				$empData = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				->paginate($paginationValue);

				$reportsCount = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
                ->get()->count();
			}

				
		}

			
			
		
		

		

		if($request->session()->get('attendance_month_filter')!='')
		{
			$attendanceDate = $request->session()->get('attendance_month_filter');
		}
		else
		{
			$attendanceDate = '';
		}

		if(!empty($request->session()->get('attendance_month_filter')))
		{
			$attendanceView = explode("-",$datefrom);
			$month=$attendanceView[0];
			$year=$attendanceView[1];
			$searchdate = $year.'-'.$month;
		}
		else
		{
			$searchdate = date('Y-m');
		}
		
		$holidayDetails = HolidayList::where('from_date','LIKE',"%{$searchdate}%")->where('to_date','LIKE',"%{$searchdate}%")->get();

		//return $holidayDetails;

		
		$holidayarray = array();
		foreach($holidayDetails as $holiday)
		{
			
			
			
			// Declare two dates 
			$Date1 = $holiday->from_date; 
			$Date2 = $holiday->to_date; 
			
			// Declare an empty array 
			 
			
			// Use strtotime function 
			$Variable1 = strtotime($Date1); 
			$Variable2 = strtotime($Date2); 
			
			// Use for loop to store dates into array 
			// 86400 sec = 24 hrs = 60*60*24 = 1 day 
			for ($currentDate = $Variable1; $currentDate <= $Variable2; $currentDate += (86400)) 
			{                      
				$Store = date('Y-m-d', $currentDate); 
				$holidayarray[] = $Store; 
			} 
			
			// Display the dates in array format
			
		}



// echo "<pre>"; 
// print_r($holidayarray); 
 
// exit;
// return 1;









		
		
		// exit;
		// return "Hello";
        

		$empData->setPath(config('app.url/listingAttendance'));
        
			
				
	    return view("DepartmentAttendance/listingAttendance",compact('empData','paginationValue','reportsCount','attendanceDate','holidayDetails','holidayarray'));
	}


	public function setPageLimitProcess(Request $request)
	{
		$offset = $request->offset;
		$request->session()->put('attendance_page_limit',$offset);
	}


	public function searchAttendanceFilter(Request $request)
	{
			
		//return $request->all();
		
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$teamlaed='';
			if($request->input('teamlaed')!=''){
			 
			 $teamlaed=implode(",", $request->input('teamlaed'));
			}
			$dateto = $request->input('dateto');
			$datefrom = $request->input('datefrom');

			$name='';
			if($request->input('emp_name')!='')
			{
			 	$name=implode(",",$request->input('emp_name'));
			}
			
			$empId='';
			if($request->input('empId')!=''){
			 
			 $empId=implode(",", $request->input('empId'));
			}
			$design='';
			if($request->input('designationdata')!=''){
			 
			 $design=implode(",", $request->input('designationdata'));
			}
			$datetolastworkingday = $request->input('datetolastworkingday');
			$datefromlastworkingday = $request->input('datefromlastworkingday');
			//02-9-2023
			$ReasonofAttrition='';
			if($request->input('ReasonofAttrition')!=''){
			 
			 $ReasonofAttrition=implode(",", $request->input('ReasonofAttrition'));
			}
			$offboardstatus='';
			if($request->input('offboardstatus')!=''){
			 
			 $offboardstatus=implode(",", $request->input('offboardstatus'));
			}
			$datetodort = $request->input('datetodort');
			$datefromdort = $request->input('datefromdort');
			
			$offboardffstatus='';
			if($request->input('offboardffstatus')!=''){
			 
			 $offboardffstatus=implode(",", $request->input('offboardffstatus'));
			}

			$rangeid='';
			if($request->input('rangeid')!='')
			{
			 
			 $rangeid=implode(",", $request->input('rangeid'));
			}
			//return "Test".$name;

			$request->session()->put('attendance_emp_name',$name);
            $request->session()->put('attendance_emp_id',$empId);
            $request->session()->put('attendance_month_filter',$datefrom);
            $request->session()->put('emp_leaves_todate',$dateto);
			$request->session()->put('attendance_designation',$design);

			
			
			$request->session()->put('attendance_department_filter',$department);
			$request->session()->put('attendance_teamleader',$teamlaed);
			
			// $request->session()->put('design_empoffboard_filter_inner_list',$design);
			// $request->session()->put('dateto_offboard_lastworkingday_list',$datetolastworkingday);
			// $request->session()->put('datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			
			// $request->session()->put('ReasonofAttrition_empoffboard_filter_list',$ReasonofAttrition);
			// $request->session()->put('empoffboard_status_filter_list',$offboardstatus);
			// $request->session()->put('datefrom_offboard_dort_list',$datefromdort);
			// $request->session()->put('dateto_offboard_dort_list',$datetodort);
			// $request->session()->put('empoffboard_ffstatus_filter_list',$offboardffstatus);
			 //return  redirect('listingPageonboarding');	
	}

    public function resetAttendanceFilter(Request $request)
    {
        $request->session()->put('attendance_emp_name','');
        $request->session()->put('attendance_emp_id','');
        $request->session()->put('attendance_month_filter','');
		$request->session()->put('emp_leaves_todate','');
        
        $request->session()->put('attendance_designation','');
        
        $request->session()->put('attendance_department_filter','');
        $request->session()->put('attendance_teamleader','');
        // $request->session()->put('name_emp_offboard_filter_inner_list','');
        // $request->session()->put('empid_emp_offboard_filter_inner_list','');
        // $request->session()->put('design_empoffboard_filter_inner_list','');
        // $request->session()->put('dateto_offboard_lastworkingday_list','');
        // $request->session()->put('datefrom_offboard_lastworkingday_list','');
        // $request->session()->put('ReasonofAttrition_empoffboard_filter_list','');
        // $request->session()->put('empoffboard_status_filter_list','');
        // $request->session()->put('datefrom_offboard_dort_list','');
        // $request->session()->put('dateto_offboard_dort_list','');
        // $request->session()->put('empoffboard_ffstatus_filter_list','');
    }




	public function exportAttendanceReport(Request $request)
	{
			//return $request->all();
			$parameters = $request->input(); 
				 $selectedId = $parameters['selectedIds'];
				 $month = $parameters['month'];
				 $year = $parameters['year'];
				 
				$filename = 'attendance_report_'.date("d-m-Y").'.xlsx';
				$spreadsheet = new Spreadsheet(); 
				$sheet = $spreadsheet->getActiveSheet();
				$sheet->mergeCells('A1:AL1');
				$sheet->setCellValue('A1', 'Attendance List - '.$month.'/'.$year)->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
				$indexCounter = 2;
				$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper('Team Leader'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper('Designation'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, strtoupper('Department'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, strtoupper('Date of Joining'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				


				function getNameFromNumber($num) 
				{
					$numeric = ($num - 1) % 26;
					$letter = chr(65 + $numeric);
					$num2 = intval(($num - 1) / 26);
					if ($num2 > 0) {
						return getNameFromNumber($num2) . $letter;
					} else {
						return $letter;
					}
				}

				$list=array();
				$tlist=array();
				for($d=1; $d<=31; $d++)
				{
					$time=mktime(12, 0, 0, $month, $d, $year);          
					if (date('m', $time)==$month)   
					{
						// $list[]=date('d F - l', $time);
						$list[]=date('d M - D', $time);
						$tlist[]=date('Y-m-d', $time);
					}    
					
				}
				$j=8;
				foreach($list as $daysList)
				{
					$daysList;					
					$h = getNameFromNumber($j);
					$sheet->setCellValue($h.$indexCounter, strtoupper($daysList))->getStyle($h.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$j++;
				}
				//return "Hello";


				// $sheet->setCellValue('H'.$indexCounter, strtoupper('Vintage Days'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('I'.$indexCounter, strtoupper('Passport Number'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('J'.$indexCounter, strtoupper('Passport Status'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn = 1;
				foreach ($selectedId as $sid) 
				{
					//echo $sid;
					$misData = Employee_details::where("id",$sid)->first();

					//$empName = $this->getEmployeeName($misData->emp_id);
					$teamLeader = $this->getTeamLeader($misData->emp_id);
					$designation = $this->getDesignation($misData->emp_id);
					$dept = $this->getDepartment($misData->emp_id);
					// $location = $this->getWorkLocation($misData->emp_id);
					// $vintage = $this->getVintage($misData->emp_id);

					if($misData->doj)
					{
						$fromDate = new DateTime($misData->doj);
						$newdate = $fromDate->format('Y-m-d');
						$date = DateTime::createFromFormat('Y-m-d', $newdate);
						$dofjoin = $date->format('d M, Y');
					}
					else
					{
						$dofjoin = "--";
					}


					



					$indexCounter++; 
					
					
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $misData->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $teamLeader)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $designation)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $dept)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $dofjoin)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					//$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					// $sheet->setCellValue('H'.$indexCounter, $vintage)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					// $sheet->setCellValue('I'.$indexCounter, $misData->passport_number)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					// $sheet->setCellValue('J'.$indexCounter, $pstatus)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$j=8;
					foreach($tlist as $daysList)
					{
						$timestamp = strtotime($daysList);
						$weakoffday = date('N', $timestamp);
						if($weakoffday==7)
						{
							$attendvalue= "Week Off";
						}
						else
						{
							$attendanceData = EmpAttendance::where('emp_id',$misData->emp_id)->where('attribute_code','attendance')->where('attendance_date',$daysList)->orderBy('id','desc')->first();

							if($attendanceData)
							{
								$attendvalue = $attendanceData->attribute_value;
							}
							else{
								$attendvalue= "NA";
							}
						}
						
						
						
						$daysList;					
						$h = getNameFromNumber($j);
						$sheet->setCellValue($h.$indexCounter, $attendvalue)->getStyle($h.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$j++;
					}	
					
					$sn++;
					
				}
				
				
				  for($col = 'A'; $col !== 'AI'; $col++) {
				   $sheet->getColumnDimension($col)->setAutoSize(true);
				}
				
				$spreadsheet->getActiveSheet()->getStyle('A1:AI1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
					
					for($index=1;$index<=$indexCounter;$index++)
					{
						  foreach (range('A','AI') as $col) {
								$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
						  }
					}
					$writer = new Xlsx($spreadsheet);
					$writer->save(public_path('uploads/exportAttendance/'.$filename));	
					echo $filename;
					exit;
	}

	public function exportAttendanceReportAdmin(Request $request)
	{
			//return $request->all();
			$parameters = $request->input(); 
				 $selectedId = $parameters['selectedIds'];
				 $month = $parameters['month'];
				 $year = $parameters['year'];
				 
				$filename = 'attendance_report_'.date("d-m-Y").'.xlsx';
				$spreadsheet = new Spreadsheet(); 
				$sheet = $spreadsheet->getActiveSheet();
				$sheet->mergeCells('A1:AL1');
				$sheet->setCellValue('A1', 'Attendance List - '.$month.'/'.$year)->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
				$indexCounter = 2;
				$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper('Team Leader'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper('Designation'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, strtoupper('Department'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, strtoupper('Date of Joining'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');


				function getNameFromNumber2($num) 
				{
					$numeric = ($num - 1) % 26;
					$letter = chr(65 + $numeric);
					$num2 = intval(($num - 1) / 26);
					if ($num2 > 0) {
						return getNameFromNumber2($num2) . $letter;
					} else {
						return $letter;
					}
				}

				$list=array();
				$tlist=array();
				for($d=1; $d<=31; $d++)
				{
					$time=mktime(12, 0, 0, $month, $d, $year);          
					if (date('m', $time)==$month)   
					{
						// $list[]=date('d F - l', $time);
						$list[]=date('d M - D', $time);
						$tlist[]=date('Y-m-d', $time);
					}    
					
				}
				$j=8;
				foreach($list as $daysList)
				{
					$daysList;					
					$h = getNameFromNumber2($j);
					$sheet->setCellValue($h.$indexCounter, strtoupper($daysList))->getStyle($h.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$j++;
				}
				//return "Hello";


				// $sheet->setCellValue('H'.$indexCounter, strtoupper('Vintage Days'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('I'.$indexCounter, strtoupper('Passport Number'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('J'.$indexCounter, strtoupper('Passport Status'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn = 1;
				foreach ($selectedId as $sid) 
				{
					//echo $sid;
					$misData = Employee_details::where("id",$sid)->first();

					//$empName = $this->getEmployeeName($misData->emp_id);
					$teamLeader = $this->getTeamLeader($misData->emp_id);
					$designation = $this->getDesignation($misData->emp_id);
					$dept = $this->getDepartment($misData->emp_id);
					// $location = $this->getWorkLocation($misData->emp_id);
					// $vintage = $this->getVintage($misData->emp_id);
					if($misData->doj)
					{
						$fromDate = new DateTime($misData->doj);
						$newdate = $fromDate->format('Y-m-d');
						$date = DateTime::createFromFormat('Y-m-d', $newdate);
						$dofjoin = $date->format('d M, Y');
					}
					else
					{
						$dofjoin = "--";
					}

					



					$indexCounter++; 
					
					
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $misData->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $teamLeader)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $designation)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $dept)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $dofjoin)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					//$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					// $sheet->setCellValue('H'.$indexCounter, $vintage)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					// $sheet->setCellValue('I'.$indexCounter, $misData->passport_number)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					// $sheet->setCellValue('J'.$indexCounter, $pstatus)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$j=8;
					foreach($tlist as $daysList)
					{
						$timestamp = strtotime($daysList);
						$weakoffday = date('N', $timestamp);
						if($weakoffday==7)
						{
							$attendvalue= "Week Off";
						}
						else
						{
							$attendanceData = EmpAttendance::where('emp_id',$misData->emp_id)->where('attribute_code','attendance')->where('attendance_date',$daysList)->orderBy('id','desc')->first();

							if($attendanceData)
							{
								$attendvalue = $attendanceData->attribute_value;
							}
							else{
								$attendvalue= "NA";
							}
						}
						
						
						
						$daysList;					
						$h = getNameFromNumber2($j);
						$sheet->setCellValue($h.$indexCounter, $attendvalue)->getStyle($h.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$j++;
					}	
					
					$sn++;
					
				}
				
				
				  for($col = 'A'; $col !== 'AI'; $col++) {
				   $sheet->getColumnDimension($col)->setAutoSize(true);
				}
				
				$spreadsheet->getActiveSheet()->getStyle('A1:AI1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
					
					for($index=1;$index<=$indexCounter;$index++)
					{
						  foreach (range('A','AI') as $col) {
								$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
						  }
					}
					$writer = new Xlsx($spreadsheet);
					$writer->save(public_path('uploads/exportadminAttendance/'.$filename));	
					echo $filename;
					exit;
	}
	



	// for lead view
	// ===========================

	public  function IndexLeadViewData(Request $request)
	{
		$loggedinUserid=$request->session()->get('EmployeeId');
        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        if($departmentDetails != '')
        {
            $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
            if($empDetails!='')
            {
                $empData = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
            }
        }
        else
        {
            $empData = Employee_details::orderBy('id','desc')->get();
        }

		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$designation=Designation::where("status",1)->get();

		$empsessionId=$request->session()->get('EmployeeId');
		$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
		if($departmentDetails != '')
		{
			
			//return "Hello".$empDetails->dept_id;
			$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
			if($empDetails!='')
			{
				//return "Hello".$empDetails->dept_id;47
				$design=Designation::where("tlsm",2)->where("department_id",$empDetails->dept_id)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);
				
				$tL_details = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",$empDetails->dept_id)->where("offline_status",1)->get();
				
			}
		}
		else{
			

			$design=Designation::where("tlsm",2)->where("status",1)->get();
			$designarray=array();
			foreach($design as $_design){
				$designarray[]=$_design->id;
			}
			$finalarray=implode(",",$designarray);
			
			$tL_details = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->get();

		}


		return view("DepartmentAttendance/lead_index",compact('empData','departmentLists','designation','tL_details'));
	}


	public function attendanceListingDataforLead(Request $request)
	{		
		$whereraw = '';
		$whereraw1 = '';
		$selectedFilter['CNAME'] = '';
		$selectedFilter['CEMAIL'] = '';
		$selectedFilter['DESC'] = '';
		$selectedFilter['DEPT'] = '';
		$selectedFilter['OPENING'] = '';
		$selectedFilter['STATUS'] = '';
		$selectedFilter['vintage'] = '';
		$selectedFilter['Company'] = '';
		$selectedFilter['Recruiter'] = '';
		
        
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';

        if(!empty($request->session()->get('attendance_page_limit')))
        {
            $paginationValue = $request->session()->get('attendance_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }


        if(!empty($request->session()->get('attendance_emp_name_Lead')) && $request->session()->get('attendance_emp_name_Lead') != 'All')
        {
            $fname = $request->session()->get('attendance_emp_name_Lead');
            $cnameArray = explode(",",$fname);
                
            $namefinalarray=array();
            foreach($cnameArray as $namearray){
                $namefinalarray[]="'".$namearray."'";                
            }
			



            $finalcname=implode(",", $namefinalarray);

			

            if($whereraw == '')
            {
                //$whereraw = 'emp_name like "%'.$fname.'%"';
               $whereraw = 'emp_name IN('.$finalcname.')';
            }
            else
            {
                $whereraw .= ' And emp_name IN('.$finalcname.')';
            }
			// echo $whereraw;
			// exit;
			
			// if($whereraw=="emp_name IN('','','')")
			// {
			// 	$whereraw='';
			// }
			if($whereraw=="emp_name IN('','','')" || $whereraw=="emp_name IN('','')")  
			{
				$whereraw='';
			}
        }

        if(!empty($request->session()->get('attendance_emp_id_Lead')) && $request->session()->get('attendance_emp_id_Lead') != 'All')
        {
            $empId = $request->session()->get('attendance_emp_id_Lead');
            if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }

		if(!empty($request->session()->get('attendance_department_filter_lead_attendance')) && $request->session()->get('attendance_department_filter_lead_attendance') != 'All')
		{
			
			$dept = $request->session()->get('attendance_department_filter_lead_attendance');
				//$departmentArray = explode(",",$dept);
			if($whereraw == '')
			{
				$whereraw = 'dept_id IN('.$dept.')';
			}
			else
			{
				$whereraw .= ' And dept_id IN('.$dept.')';
			}
		}

		if(!empty($request->session()->get('attendance_designation_lead')) && $request->session()->get('attendance_designation_lead') != 'All')
		{
			$designd = $request->session()->get('attendance_designation_lead');
				//$departmentArray = explode(",",$designd);
			if($whereraw == '')
			{
				$whereraw = 'designation_by_doc_collection IN('.$designd.')';
			}
			else
			{
				$whereraw .= ' And designation_by_doc_collection IN('.$designd.')';
			}
		}


		if(!empty($request->session()->get('attendance_teamleader_lead')) && $request->session()->get('attendance_teamleader_lead') != 'All')
		{
			$teamlead = $request->session()->get('attendance_teamleader_lead');
				//$departmentArray = explode(",",$dept);
			if($whereraw == '')
			{
				$whereraw = 'tl_id IN('.$teamlead.')';
			}
			else
			{
				$whereraw .= ' And tl_id IN('.$teamlead.')';
			}
		}





        if(!empty($request->session()->get('attendance_month_filter_Lead')) && $request->session()->get('attendance_month_filter_Lead') != 'All')
        {
            $datefrom = $request->session()->get('attendance_month_filter_Lead');
			//echo $whereraw;

			// $attendanceView = explode("-",$datefrom);
			// //print_r($attendanceView);

			// $month=$attendanceView[0];
			// $year=$attendanceView[1];



			// $empData = EmpAttendance::whereYear('attendance_date', '=', $year)
            // ->whereMonth('attendance_date', '=', $month)
            // ->get();
			// $empid=array();
			// foreach($empData as $emp)
			// {
			// 	$empid[] = $emp->emp_id;
			// }

			// if (empty($empid)) 
			// {
				
				
				
			// 	$finalempid=implode(",", $empid);

			


			
			// 	if($whereraw == '')
			// 	{
			// 		$whereraw = 'emp_id IN("'.$finalempid.'")';
					
			// 	}
			// 	else
			// 	{
			// 		$whereraw .= ' And emp_id IN('.$finalempid.')';
			// 	}
			// } 
			// else 
			// {
				

			// 	$finalempid=implode(",", $empid);

			


			
			// 	if($whereraw == '')
			// 	{
			// 		$whereraw = 'emp_id IN('.$finalempid.')';
					
			// 	}
			// 	else
			// 	{
			// 		$whereraw .= ' And emp_id IN('.$finalempid.')';
			// 	}


			// }
			


            
        }









		
        // if(!empty($request->session()->get('emp_leaves_todate')) && $request->session()->get('emp_leaves_todate') != 'All')
        // {
        //     $dateto = $request->session()->get('emp_leaves_todate');
        //     if($whereraw == '')
        //     {
        //         $whereraw = 'leaves_request.created_at<= "'.$dateto.' 00:00:00"';
        //     }
        //     else
        //     {
        //         $whereraw .= ' And leaves_request.created_at<= "'.$dateto.' 00:00:00"';
        //     }
        // }




		$loggedinUserid=$request->session()->get('EmployeeId');
        if($whereraw != '')
		{
			
			// echo "<pre>";
			// print_r($whereraw);
			// exit;
			$empData = $this->getLoggedinUser($loggedinUserid);
			if($empData==1)
			{
				if(!empty($request->session()->get('attendance_month_filter_Lead')))
				{
					$attendanceView = explode("-",$datefrom);
					$month=$attendanceView[0];
					$year=$attendanceView[1];
					$tdate = $year.'-'.$month;
				}
				else
				{
					$tdate = date('Y-m-d');
				}

				$empData_details = Employee_details::orderBy('id','desc')->get();
				$lastworkingdate='';
				$offmarkEmp=array();
				foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							if(!empty($request->session()->get('attendance_month_filter_Lead')))
							{
								$attendanceView = explode("-",$lastworkingdate);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$lastworkdate = $year.'-'.$month;
								if($lastworkdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
																
							}
							else
							{
								if($lastworkingdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;
								}
								
							}

						}
					}
					else
					{
						if(!empty($request->session()->get('attendance_month_filter_Lead')))
						{
							if($emp->doj)
							{
								$attendanceView = explode("-",$emp->doj);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$joiningdate = $year.'-'.$month;
								if($joiningdate > $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
							}
							
							
						}
						else
						{
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;									
							}
							
						}
					}
				}

				$empData = Employee_details::whereRaw($whereraw)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				->paginate($paginationValue);

				$reportsCount = Employee_details::whereRaw($whereraw)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
                ->get()->count();
			}
			else
			{
				if(!empty($request->session()->get('attendance_month_filter_Lead')))
				{
					$attendanceView = explode("-",$datefrom);
					$month=$attendanceView[0];
					$year=$attendanceView[1];
					$tdate = $year.'-'.$month;
				}
				else
				{
					$tdate = date('Y-m-d');
				}

				$departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
				$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
				$empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
				$lastworkingdate='';
				$offmarkEmp=array();
				foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							if(!empty($request->session()->get('attendance_month_filter_Lead')))
							{
								$attendanceView = explode("-",$lastworkingdate);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$lastworkdate = $year.'-'.$month;
								if($lastworkdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
																
							}
							else
							{
								if($lastworkingdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;
								}
								
							}

						}

					}
					else
					{
						if(!empty($request->session()->get('attendance_month_filter_Lead')))
						{
							if($emp->doj)
							{
								$attendanceView = explode("-",$emp->doj);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$joiningdate = $year.'-'.$month;
								if($joiningdate > $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
							}
							
							
						}
						else
						{
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;									
							}
							
						}
					}
				}

				$empData = Employee_details::whereRaw($whereraw)->where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				->paginate($paginationValue);

				$reportsCount = Employee_details::whereRaw($whereraw)->where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
                ->get()->count();
			}
        }
        else
        {
			$empData = $this->getLoggedinUser($loggedinUserid);
			if($empData==1)
			{
				if(!empty($request->session()->get('attendance_month_filter_Lead')))
				{
					$attendanceView = explode("-",$datefrom);
					$month=$attendanceView[0];
					$year=$attendanceView[1];
					$tdate = $year.'-'.$month;
				}
				else
				{
					$tdate = date('Y-m-d');
				}

				$empData_details = Employee_details::orderBy('id','desc')->get();
				$lastworkingdate='';
				$offmarkEmp=array();
				foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							if(!empty($request->session()->get('attendance_month_filter_Lead')))
							{
								$attendanceView = explode("-",$lastworkingdate);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$lastworkdate = $year.'-'.$month;
								if($lastworkdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
																
							}
							else
							{
								if($lastworkingdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;
								}
								
							}

						}
					}
					else
					{
						if(!empty($request->session()->get('attendance_month_filter_Lead')))
						{
							if($emp->doj)
							{
								$attendanceView = explode("-",$emp->doj);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$joiningdate = $year.'-'.$month;
								if($joiningdate > $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
							}
							
							
						}
						else
						{
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;									
							}
							
						}
					}
				}

				$empData = Employee_details::whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				->paginate($paginationValue);

				$reportsCount = Employee_details::whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
                ->get()->count();
			}
			else
			{
				if(!empty($request->session()->get('attendance_month_filter_Lead')))
				{
					$attendanceView = explode("-",$datefrom);
					$month=$attendanceView[0];
					$year=$attendanceView[1];
					$tdate = $year.'-'.$month;
				}
				else
				{
					$tdate = date('Y-m-d');
				}

				$departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
				$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
				$empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
				$lastworkingdate='';
				$offmarkEmp=array();
				foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							if(!empty($request->session()->get('attendance_month_filter_Lead')))
							{
								$attendanceView = explode("-",$lastworkingdate);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$lastworkdate = $year.'-'.$month;
								if($lastworkdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
																
							}
							else
							{
								if($lastworkingdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;
								}
								
							}

						}

					}
					else
					{
						if(!empty($request->session()->get('attendance_month_filter_Lead')))
						{
							if($emp->doj)
							{
								$attendanceView = explode("-",$emp->doj);
								$year=$attendanceView[0];
								$month=$attendanceView[1];
								$joiningdate = $year.'-'.$month;
								if($joiningdate > $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
							}
							
							
						}
						else
						{
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;									
							}
							
						}
					}
				}

				$empData = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				->paginate($paginationValue);

				$reportsCount = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
                ->get()->count();
			}

				
		}




		if($request->session()->get('attendance_month_filter_Lead')!='')
		{
			$attendanceDate = $request->session()->get('attendance_month_filter_Lead');
		}
		else
		{
			$attendanceDate = '';
		}
        
        
		$empData->setPath(config('app.url/listingAttendanceforLead'));
				
	    return view("DepartmentAttendance/listingAttendanceforLead",compact('empData','paginationValue','reportsCount','attendanceDate'));
	}

	public function searchAttendanceFilterLead(Request $request)
	{
			
		//return $request->all();
		
			$department='';
			if($request->input('department')!=''){


			 
			 $department=implode(",", $request->input('department'));
			}
			$teamlaed='';
			if($request->input('teamlaed')!=''){
			 
			 $teamlaed=implode(",", $request->input('teamlaed'));
			}
			$dateto = $request->input('dateto');
			$datefrom = $request->input('datefrom');

			$name='';
			if($request->input('emp_name')!='')
			{
			 	$name=implode(",",$request->input('emp_name'));
			}
			
			$empId='';
			if($request->input('empId')!=''){
			 
			 $empId=implode(",", $request->input('empId'));
			}
			$design='';
			if($request->input('designationdata')!=''){
			 
			 $design=implode(",", $request->input('designationdata'));
			}
			$datetolastworkingday = $request->input('datetolastworkingday');
			$datefromlastworkingday = $request->input('datefromlastworkingday');
			//02-9-2023
			$ReasonofAttrition='';
			if($request->input('ReasonofAttrition')!=''){
			 
			 $ReasonofAttrition=implode(",", $request->input('ReasonofAttrition'));
			}
			$offboardstatus='';
			if($request->input('offboardstatus')!=''){
			 
			 $offboardstatus=implode(",", $request->input('offboardstatus'));
			}
			$datetodort = $request->input('datetodort');
			$datefromdort = $request->input('datefromdort');
			
			$offboardffstatus='';
			if($request->input('offboardffstatus')!=''){
			 
			 $offboardffstatus=implode(",", $request->input('offboardffstatus'));
			}

			$rangeid='';
			if($request->input('rangeid')!='')
			{
			 
			 $rangeid=implode(",", $request->input('rangeid'));
			}
			//return "Test".$name;

			$request->session()->put('attendance_emp_name_Lead',$name);
            $request->session()->put('attendance_emp_id_Lead',$empId);
            $request->session()->put('attendance_month_filter_Lead',$datefrom);
            $request->session()->put('emp_leaves_todate',$dateto);


			$request->session()->put('attendance_designation_lead',$design);
			
			
			$request->session()->put('attendance_department_filter_lead_attendance',$department);
			$request->session()->put('attendance_teamleader_lead',$teamlaed);
			
			// $request->session()->put('design_empoffboard_filter_inner_list',$design);
			// $request->session()->put('dateto_offboard_lastworkingday_list',$datetolastworkingday);
			// $request->session()->put('datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			
			// $request->session()->put('ReasonofAttrition_empoffboard_filter_list',$ReasonofAttrition);
			// $request->session()->put('empoffboard_status_filter_list',$offboardstatus);
			// $request->session()->put('datefrom_offboard_dort_list',$datefromdort);
			// $request->session()->put('dateto_offboard_dort_list',$datetodort);
			// $request->session()->put('empoffboard_ffstatus_filter_list',$offboardffstatus);
			 //return  redirect('listingPageonboarding');	
	}

    public function resetAttendanceFilterLead(Request $request)
    {
        $request->session()->put('attendance_emp_name_Lead','');
        $request->session()->put('attendance_emp_id_Lead','');
        $request->session()->put('attendance_month_filter_Lead','');
		$request->session()->put('emp_leaves_todate','');
        
        $request->session()->put('attendance_designation_lead','');
        
        $request->session()->put('attendance_department_filter_lead_attendance','');
        $request->session()->put('attendance_teamleader_lead','');
        // $request->session()->put('name_emp_offboard_filter_inner_list','');
        // $request->session()->put('empid_emp_offboard_filter_inner_list','');
        // $request->session()->put('design_empoffboard_filter_inner_list','');
        // $request->session()->put('dateto_offboard_lastworkingday_list','');
        // $request->session()->put('datefrom_offboard_lastworkingday_list','');
        // $request->session()->put('ReasonofAttrition_empoffboard_filter_list','');
        // $request->session()->put('empoffboard_status_filter_list','');
        // $request->session()->put('datefrom_offboard_dort_list','');
        // $request->session()->put('dateto_offboard_dort_list','');
        // $request->session()->put('empoffboard_ffstatus_filter_list','');
    }


	public static function getEmpName($emp_id)
	{
		$empData = Employee_details::where('emp_id',$emp_id)->orderBy('id','desc')->first();		

		if($empData)
		{
			return $empData->emp_name;
		}
		else{
			return 'NA';
		}
	}
	public function editAttendanceData(Request $request)
	{
		$emp_id = $request->empid;
		$attendanceDate = $request->adate;
		$attendanceDate = date("Y-m-d",$attendanceDate);
		
		//return $attendanceDate;
		$attendanceData = Attendance::orderBy('id','desc')->get();

		$empAttendanceData = EmpAttendance::where('emp_id',$emp_id)->where('attendance_date',$attendanceDate)->orderBy('id','desc')->first();

		if($empAttendanceData)
		{
			$empAttendanceData = $empAttendanceData;
		}
		else
		{
			$empAttendanceData='';
		}


		return view("DepartmentAttendance/editAttendance",compact('attendanceData','emp_id','attendanceDate','empAttendanceData'));
	}

	public function updateAttendancePostData(Request $request)
	{
		$validator = Validator::make($request->all(), 
        [			
			'editattendanceType' => 'required',
			   
        ],
		[
            'editattendanceType.required'=> 'Please Mark Attendance',
							
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			//return $request->all();
			
			
			$usersessionId=$request->session()->get('EmployeeId');
			$empAttendanceData = EmpAttendance::where('emp_id',$request->emp_id)->where('attendance_date',$request->attendanceDate)->orderBy('id','desc')->first();		
			
			
			//$empAttendanceData->emp_id = $request->emp_id;
			//$empAttendanceData->attribute_code = 'attendance';
			$empAttendanceData->attribute_value = $request->editattendanceType;
			//$empAttendanceData->attendance_date = $request->attendanceDate;
			//$empAttendanceData->created_at = date('Y-m-d H:i:s');
			//$empAttendanceData->status = 1;
			$empAttendanceData->attendance_mark_by = $usersessionId;
			$empAttendanceData->attendance_mark_on = date('Y-m-d');
			$empAttendanceData->save(); 
		}
	}




	public function markAttendanceCronOLD(Request $request)
	{
		//$todayDate = date('Y-m-d');

		echo $todayDate = date('2024-04-30');
		
		$requestedLeavesPreCheck = RequestedLeaves::where('final_status',1)->get();
		$newRowResult=array();
		$newEmpResult=array();
		foreach($requestedLeavesPreCheck as $value)
		{
			if($value->updated_from_date==NULL && $value->updated_to_date==NULL)
			{
				if($value->from_date <= $todayDate && $value->to_date >= $todayDate)
				{
					$newRowResult[]=$value->id;
					$newEmpResult[]=$value->emp_id;
				}                       
			}
			else
			{
				if($value->updated_from_date <= $todayDate && $value->updated_to_date >= $todayDate)
				{
					$newRowResult[]=$value->id;
					$newEmpResult[]=$value->emp_id;
				}
			}
		}
		
		echo "<pre>";
		print_r($newRowResult);
		print_r($newEmpResult);
		//exit;

		$empAttendanceCron = EmpAttendanceCron::where('attendance_date',$todayDate)->orderBy('id','desc')->first();

		//return $empAttendanceCron;
		
		if($empAttendanceCron)
		{
			return response()->json(['success'=>'Cron already Run for '.$todayDate]);
		}
		else
		{
			$empData_details = Employee_details::orderBy('id','desc')->get();
			$lastworkingdate='';
			$offmarkEmp=array();
			foreach($empData_details as $emp)
			{
				if($emp->offline_status==2)
				{
					$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
					if($offlineEmpData)
					{
						if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
						{
							$lastworkingdate = $offlineEmpData->last_working_day_resign;
						}
						elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
						{
							$lastworkingdate = $offlineEmpData->last_working_day_terminate;
						}
						else
						{
							$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
							$lastworkingdate = $new_date;
						}							
					}

					if($lastworkingdate)
					{
						if($lastworkingdate < $todayDate)
						{
							$offmarkEmp[]=$emp->emp_id;
						}
						if($emp->doj > $todayDate)
						{
							$offmarkEmp[]=$emp->emp_id;									
						}
					}
				}
				else
				{
					if($emp->doj > $todayDate)
					{
						$offmarkEmp[]=$emp->emp_id;									
					}
				}
			}

			//$empData = Employee_details::whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')->get();

			$usersessionId=$request->session()->get('EmployeeId');
			//$spec = array('101752');
			$empAttendanceData = Employee_details::whereNotIn('emp_id', $offmarkEmp)->whereNotIn('emp_id', $newEmpResult)->orderBy('id','desc')->get();
			//$empAttendanceData = Employee_details::where('emp_id', '102097')->orderBy('id','desc')->get();


			//echo "<pre>";
			//print_r($empAttendanceData);
			//exit;

			$newResult=array();
			foreach($empAttendanceData as $emp)
			{
				$empAttendanceData = EmpAttendance::where('emp_id',$emp->emp_id)->where('attribute_code','attendance')->where('attendance_date',$todayDate)->orderBy('id','desc')->first();

				if($empAttendanceData)
				{
					

					
				}
				else
				{					
					$timestamp = strtotime($todayDate);
					$weakoffday = date('N', $timestamp);

					if($weakoffday==7)
					{
						continue;
					}
					
					$attendanceData = new EmpAttendance();
					$attendanceData->emp_id = $emp->emp_id;
					$attendanceData->attribute_code = 'attendance';
					$attendanceData->attribute_value = 'P';
					$attendanceData->attendance_date = $todayDate;
					$attendanceData->created_at = date('Y-m-d H:i:s');
					$attendanceData->status = 1;
					$attendanceData->attendance_mark_by = $usersessionId;
					$attendanceData->attendance_mark_on = date('Y-m-d');
					$attendanceData->attendance_mark_using = 'Cron';
					$attendanceData->save();
					
					

					
				}
			}



			// check employee for leave Start
			$requestedLeaves = RequestedLeaves::whereIn('id',$newRowResult)->get();

			$empAttendanceDataLeave = EmpAttendance::where('emp_id',$emp->emp_id)->where('attribute_code','attendance')->where('attendance_date',$todayDate)->orderBy('id','desc')->first();

			

			//print_r($requestedLeaves);
			foreach($requestedLeaves as $empLeave)
			{
				
				if($empAttendanceDataLeave)
				{
					$empAttendanceDataforLeave = EmpAttendance::where('attendance_date',$todayDate)->where('emp_id',$empLeave->emp_id)->delete();


					$requestedLeaveTypes = LeaveTypes::where('id',$empLeave->leave_id)->first();
					$timestamp = strtotime($todayDate);
					$weakoffday = date('N', $timestamp);
	
					if($weakoffday==7)
					{
						continue;
					}
					$attendanceDataLeave = new EmpAttendance();
					$attendanceDataLeave->emp_id = $empLeave->emp_id;
					$attendanceDataLeave->attribute_code = 'attendance';
					$attendanceDataLeave->attribute_value = $requestedLeaveTypes->sort_title;
					$attendanceDataLeave->attendance_date = $todayDate;
					$attendanceDataLeave->created_at = date('Y-m-d H:i:s');
					$attendanceDataLeave->status = 1;
					$attendanceDataLeave->attendance_mark_by = $usersessionId;
					$attendanceDataLeave->attendance_mark_on = date('Y-m-d');
					$attendanceDataLeave->attendance_mark_using = 'Cron';
					$attendanceDataLeave->save(); 

				}
				else
				{
					$requestedLeaveTypes = LeaveTypes::where('id',$empLeave->leave_id)->first();
					$timestamp = strtotime($todayDate);
					$weakoffday = date('N', $timestamp);
	
					if($weakoffday==7)
					{
						continue;
					}
					$attendanceDataLeave = new EmpAttendance();
					$attendanceDataLeave->emp_id = $empLeave->emp_id;
					$attendanceDataLeave->attribute_code = 'attendance';
					$attendanceDataLeave->attribute_value = $requestedLeaveTypes->sort_title;
					$attendanceDataLeave->attendance_date = $todayDate;
					$attendanceDataLeave->created_at = date('Y-m-d H:i:s');
					$attendanceDataLeave->status = 1;
					$attendanceDataLeave->attendance_mark_by = $usersessionId;
					$attendanceDataLeave->attendance_mark_on = date('Y-m-d');
					$attendanceDataLeave->attendance_mark_using = 'Cron';
					$attendanceDataLeave->save(); 
				}

				
				
				
				
				
				
				
				// echo $empLeave->emp_id;
				// exit;
				
				// echo $empLeave->id;
				// exit;
				
			}
			// check employee for leave End





			//print_r($newResult);
			//return $newResult;
			//exit;
			// cron table insert

			$timestamp = strtotime($todayDate);
			$weakoffday = date('N', $timestamp);
	
			// if($weakoffday==7)
			// {				
			// }
			// else
			// {
			// 	$attendanceCron = new EmpAttendanceCron();
			// 	$attendanceCron->attendance_date = $todayDate;
			// 	$attendanceCron->created_at = date('Y-m-d H:i:s');
			// 	$attendanceCron->save(); 
			// }
			 
		}
		return response()->json(['success'=>'Attendance Marked Successfully using Cron for '.$todayDate]);
	}




	public function markAttendanceCron(Request $request)
	{
		//$todayDate = date('Y-m-d');
		$todayDate = date('2024-04-30');


		$requestedLeavesPreCheck = RequestedLeaves::where('final_status',1)->get();
		$newRowResult=array();
		$newEmpResult=array();
		foreach($requestedLeavesPreCheck as $value)
		{
			if($value->updated_from_date==NULL && $value->updated_to_date==NULL)
			{
				if($value->from_date <= $todayDate && $value->to_date >= $todayDate)
				{
					$newRowResult[]=$value->id;
					$newEmpResult[]=$value->emp_id;
				}                       
			}
			else
			{
				if($value->updated_from_date <= $todayDate && $value->updated_to_date >= $todayDate)
				{
					$newRowResult[]=$value->id;
					$newEmpResult[]=$value->emp_id;
				}
			}
		}
		
		$empAttendanceCron = EmpAttendanceCron::where('attendance_date',$todayDate)->orderBy('id','desc')->first();
		
		if($empAttendanceCron)
		{
			return response()->json(['success'=>'Cron already Run for '.$todayDate]);
		}
		else
		{
			$empData_details = Employee_details::orderBy('id','desc')->get();
			$lastworkingdate='';
			$offmarkEmp=array();
			foreach($empData_details as $emp)
			{
				if($emp->offline_status==2)
				{
					$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
					if($offlineEmpData)
					{
						if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
						{
							$lastworkingdate = $offlineEmpData->last_working_day_resign;
						}
						elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
						{
							$lastworkingdate = $offlineEmpData->last_working_day_terminate;
						}
						else
						{
							$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
							$lastworkingdate = $new_date;
						}							
					}

					if($lastworkingdate)
					{
						if($lastworkingdate < $todayDate)
						{
							$offmarkEmp[]=$emp->emp_id;
						}
						if($emp->doj > $todayDate)
						{
							$offmarkEmp[]=$emp->emp_id;									
						}
					}
				}
				else
				{
					if($emp->doj > $todayDate)
					{
						$offmarkEmp[]=$emp->emp_id;									
					}
				}
			}


			$usersessionId=$request->session()->get('EmployeeId');
			$empAttendanceData = Employee_details::whereNotIn('emp_id', $offmarkEmp)->whereNotIn('emp_id', $newEmpResult)->orderBy('id','desc')->get();
			$newResult=array();
			foreach($empAttendanceData as $emp)
			{
				$empAttendanceData = EmpAttendance::where('emp_id',$emp->emp_id)->where('attribute_code','attendance')->where('attendance_date',$todayDate)->orderBy('id','desc')->first();

				if($empAttendanceData)
				{
					$empAttendanceData = EmpAttendance::where('attendance_date',$todayDate)->where('emp_id',$emp->emp_id)->delete();


					$timestamp = strtotime($todayDate);
					$weakoffday = date('N', $timestamp);

					if($weakoffday==7)
					{
						continue;
					}
					
					$attendanceData = new EmpAttendance();
					$attendanceData->emp_id = $emp->emp_id;
					$attendanceData->attribute_code = 'attendance';
					$attendanceData->attribute_value = 'P';
					$attendanceData->attendance_date = $todayDate;
					$attendanceData->created_at = date('Y-m-d H:i:s');
					$attendanceData->status = 1;
					$attendanceData->attendance_mark_by = $usersessionId;
					$attendanceData->attendance_mark_on = date('Y-m-d');
					$attendanceData->attendance_mark_using = 'Cron';					
					$attendanceData->save();

				}
				else
				{					
					$timestamp = strtotime($todayDate);
					$weakoffday = date('N', $timestamp);

					if($weakoffday==7)
					{
						continue;
					}
					
					$attendanceData = new EmpAttendance();
					$attendanceData->emp_id = $emp->emp_id;
					$attendanceData->attribute_code = 'attendance';
					$attendanceData->attribute_value = 'P';
					$attendanceData->attendance_date = $todayDate;
					$attendanceData->created_at = date('Y-m-d H:i:s');
					$attendanceData->status = 1;
					$attendanceData->attendance_mark_by = $usersessionId;
					$attendanceData->attendance_mark_on = date('Y-m-d');
					$attendanceData->attendance_mark_using = 'Cron';
					$attendanceData->save(); 
				}
			}


			// Employee on Leave Attendance Mark Start
			$requestedLeaves = RequestedLeaves::whereIn('id',$newRowResult)->get();
			$empAttendanceDataLeave = EmpAttendance::where('emp_id',$emp->emp_id)->where('attribute_code','attendance')->where('attendance_date',$todayDate)->orderBy('id','desc')->first();			

			foreach($requestedLeaves as $empLeave)
			{
				if($empAttendanceDataLeave)
				{
					$empAttendanceDataforLeave = EmpAttendance::where('attendance_date',$todayDate)->where('emp_id',$empLeave->emp_id)->delete();
					$requestedLeaveTypes = LeaveTypes::where('id',$empLeave->leave_id)->first();
					$timestamp = strtotime($todayDate);
					$weakoffday = date('N', $timestamp);
	
					if($weakoffday==7)
					{
						continue;
					}
					$attendanceDataLeave = new EmpAttendance();
					$attendanceDataLeave->emp_id = $empLeave->emp_id;
					$attendanceDataLeave->attribute_code = 'attendance';
					$attendanceDataLeave->attribute_value = $requestedLeaveTypes->sort_title;
					$attendanceDataLeave->attendance_date = $todayDate;
					$attendanceDataLeave->created_at = date('Y-m-d H:i:s');
					$attendanceDataLeave->status = 1;
					$attendanceDataLeave->attendance_mark_by = $usersessionId;
					$attendanceDataLeave->attendance_mark_on = date('Y-m-d');
					$attendanceDataLeave->attendance_mark_using = 'Cron';
					$attendanceDataLeave->save(); 

				}
				else
				{
					$requestedLeaveTypes = LeaveTypes::where('id',$empLeave->leave_id)->first();
					$timestamp = strtotime($todayDate);
					$weakoffday = date('N', $timestamp);
	
					if($weakoffday==7)
					{
						continue;
					}
					$attendanceDataLeave = new EmpAttendance();
					$attendanceDataLeave->emp_id = $empLeave->emp_id;
					$attendanceDataLeave->attribute_code = 'attendance';
					$attendanceDataLeave->attribute_value = $requestedLeaveTypes->sort_title;
					$attendanceDataLeave->attendance_date = $todayDate;
					$attendanceDataLeave->created_at = date('Y-m-d H:i:s');
					$attendanceDataLeave->status = 1;
					$attendanceDataLeave->attendance_mark_by = $usersessionId;
					$attendanceDataLeave->attendance_mark_on = date('Y-m-d');
					$attendanceDataLeave->attendance_mark_using = 'Cron';
					$attendanceDataLeave->save(); 
				}

			}
			// Employee on Leave Attendance Mark End


			
			// cron table insert

			$timestamp = strtotime($todayDate);
			$weakoffday = date('N', $timestamp);
	
			if($weakoffday==7)
			{				
			}
			else
			{
				$attendanceCron = new EmpAttendanceCron();
				$attendanceCron->attendance_date = $todayDate;
				$attendanceCron->created_at = date('Y-m-d H:i:s');
				$attendanceCron->save(); 
			}
			 
		}
		return response()->json(['success'=>'Attendance Marked Successfully using Cron for '.$todayDate]);
	}





	public function markAttendanceCron2(Request $request)
	{
		$todayDate = date('2024-02-29');
		



		// $empAttendanceCron = EmpAttendanceCron::where('attendance_date',$todayDate)->orderBy('id','desc')->first();

		// //return $empAttendanceCron;
		
		// if($empAttendanceCron)
		// {
		// 	return response()->json(['success'=>'Cron already Run for '.$todayDate]);
		// }
		// else
		// {
			
			$loggedinUserid=$request->session()->get('EmployeeId');

			//$wemp=array('100944','101801','101798','100939');
			

				$departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
				$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
				//$wdeptid=37;
				$empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
				
				//return $empData_details;

				$reportsCount = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')
                ->get()->count();

				
				//return $empData_details;
				$lastworkingdate='';
				$offmarkEmp=array();
				foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							
								if($lastworkingdate < $todayDate)
								{
									$offmarkEmp[]=$emp->emp_id;
								}
								if($emp->doj > $todayDate)
								{
									$offmarkEmp[]=$emp->emp_id;									
								}
								
						

						}

					}
					else
					{
						

						if($emp->doj)
						{
							if($emp->doj > $todayDate)
							{
								$offmarkEmp[]=$emp->emp_id;									
							}
						}
						
							
						
					}
				}
				// echo "<pre>";
				// print_r($offmarkEmp);
				// exit;
				

				//$empData = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				//->paginate($paginationValue);

			//$empData = Employee_details::whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')->get();

			$usersessionId=$request->session()->get('EmployeeId');
			$empAttendanceData = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')->get();

			foreach($empAttendanceData as $emp)
			{
				$empAttendanceData = EmpAttendance::where('emp_id',$emp->emp_id)->where('attribute_code','attendance')->where('attendance_date',$todayDate)->orderBy('id','desc')->first();

				if($empAttendanceData)
				{
					$empAttendanceData = EmpAttendance::where('attendance_date',$todayDate)->where('emp_id',$emp->emp_id)->delete();


					$timestamp = strtotime($todayDate);
					$weakoffday = date('N', $timestamp);

					if($weakoffday==7)
					{
						continue;
					}
					
					$attendanceData = new EmpAttendance();
					$attendanceData->emp_id = $emp->emp_id;
					$attendanceData->attribute_code = 'attendance';
					$attendanceData->attribute_value = 'P';
					$attendanceData->attendance_date = $todayDate;
					$attendanceData->created_at = date('Y-m-d H:i:s');
					$attendanceData->status = 1;
					$attendanceData->attendance_mark_by = $usersessionId;
					$attendanceData->attendance_mark_on = date('Y-m-d');
					$attendanceData->attendance_mark_using = 'Cron 8th March';					
					$attendanceData->save(); 
				}
				else
				{
					$timestamp = strtotime($todayDate);
					$weakoffday = date('N', $timestamp);

					if($weakoffday==7)
					{
						continue;
					}
					
					$attendanceData = new EmpAttendance();
					$attendanceData->emp_id = $emp->emp_id;
					$attendanceData->attribute_code = 'attendance';
					$attendanceData->attribute_value = 'P';
					$attendanceData->attendance_date = $todayDate;
					$attendanceData->created_at = date('Y-m-d H:i:s');
					$attendanceData->status = 1;
					$attendanceData->attendance_mark_by = $usersessionId;
					$attendanceData->attendance_mark_on = date('Y-m-d');
					$attendanceData->attendance_mark_using = 'Cron';
					$attendanceData->save(); 
				}
			}

			// cron table insert

			$timestamp = strtotime($todayDate);
			$weakoffday = date('N', $timestamp);
	
			// if($weakoffday==7)
			// {				
			// }
			// else
			// {
			// 	$attendanceCron = new EmpAttendanceCron();
			// 	$attendanceCron->attendance_date = $todayDate;
			// 	$attendanceCron->created_at = date('Y-m-d H:i:s');
			// 	$attendanceCron->save(); 
			// }
			 
		//}
		return response()->json(['success'=>'Attendance Marked Successfully using Cron for '.$todayDate.' Records Count: '.$reportsCount]);
	}
	
 
}
