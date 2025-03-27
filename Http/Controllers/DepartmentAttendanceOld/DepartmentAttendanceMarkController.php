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

           
            //$requestedLeaves->request_by = $usersessionId;
            $attendanceData->save(); 
           

            return response()->json(['success'=>'Attendance Marked Successfully.']);
			
		} 
	}

	public static function getAttendanceStatus($emp_id,$gDate)
	{
		$tdate = date('Y-m-d');
		$attendanceData = EmpAttendance::where('emp_id',$emp_id)->where('attribute_code','attendance')->where('attendance_date',$gDate)->orderBy('id','desc')->first();

		

		if($attendanceData)
		{
			return $attendanceData->attribute_value;
		}
		else{
			return 1;
		}
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

		return view("DepartmentAttendance/index",compact('empData','departmentLists'));
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
			
			if($whereraw=="emp_name IN('','','')")
			{
				$whereraw='';
			}
        }

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









		
        if(!empty($request->session()->get('emp_leaves_todate')) && $request->session()->get('emp_leaves_todate') != 'All')
        {
            $dateto = $request->session()->get('emp_leaves_todate');
            if($whereraw == '')
            {
                $whereraw = 'leaves_request.created_at<= "'.$dateto.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And leaves_request.created_at<= "'.$dateto.' 00:00:00"';
            }
        }



        if($whereraw != '')
		{
           
			// print_r($whereraw);
			// exit;
			
			
			$loggedinUserid=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $requestedLeaves = Employee_details::whereRaw($whereraw)
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('id', 'desc')
                    //->toSql();
                    //dd($requestedLeaves);
                    ->paginate($paginationValue);

                    $reportsCount = Employee_details::whereRaw($whereraw)
                    
                    ->orderBy('id', 'desc')
                    ->get()->count();
                }
            }
            else
            {
                $empData = Employee_details::whereRaw($whereraw)->orderBy('id','desc')
                 //->toSql();
                // dd($empData);
                ->paginate($paginationValue);

                $reportsCount = Employee_details::whereRaw($whereraw)
                ->get()->count();
            }
            
            
            
            

        }
        else
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
				$empData = Employee_details::orderBy('id','desc')
				//->get();
				->paginate($paginationValue);
				$reportsCount = Employee_details::orderBy('id', 'desc')
                ->get()->count();
			}

			//return view("DepartmentAttendance/departmentMarkAttendance",compact('empData'));
			
		}

		if($request->session()->get('attendance_month_filter')!='')
		{
			$attendanceDate = $request->session()->get('attendance_month_filter');
		}
		else
		{
			$attendanceDate = '';
		}
        
        
			
		//$empData->setPath(config('app.url/listingAttendance'));		
	    return view("DepartmentAttendance/listingAttendance",compact('empData','paginationValue','reportsCount','attendanceDate'));
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


			$request->session()->put('range_filter_inner_list',$rangeid);
			$request->session()->put('empid_emp_offboard_filter_inner_list',$empId);
			
			$request->session()->put('attendance_department_filter',$department);
			// $request->session()->put('teamleader_filter_inner_list',$teamlaed);
			
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
        
        
        
        $request->session()->put('attendance_department_filter','');
        // $request->session()->put('teamleader_filter_inner_list','');
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
				$sheet->mergeCells('A1:AI1');
				$sheet->setCellValue('A1', 'Attendance List - '.$month.'/'.$year)->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
				$indexCounter = 2;
				$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper('Team Leader'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper('Designation'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, strtoupper('Department'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				


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
				$j=7;
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


					



					$indexCounter++; 
					
					
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $misData->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $teamLeader)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $designation)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $dept)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					// $sheet->setCellValue('H'.$indexCounter, $vintage)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					// $sheet->setCellValue('I'.$indexCounter, $misData->passport_number)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					// $sheet->setCellValue('J'.$indexCounter, $pstatus)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$j=7;
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
				$sheet->mergeCells('A1:AI1');
				$sheet->setCellValue('A1', 'Attendance List - '.$month.'/'.$year)->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
				$indexCounter = 2;
				$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper('Team Leader'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper('Designation'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, strtoupper('Department'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				


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
				$j=7;
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


					



					$indexCounter++; 
					
					
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $misData->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $teamLeader)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $designation)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $dept)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					// $sheet->setCellValue('H'.$indexCounter, $vintage)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					// $sheet->setCellValue('I'.$indexCounter, $misData->passport_number)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					// $sheet->setCellValue('J'.$indexCounter, $pstatus)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$j=7;
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

		return view("DepartmentAttendance/lead_index",compact('empData','departmentLists'));
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
			
			if($whereraw=="emp_name IN('','','')")
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

		if(!empty($request->session()->get('attendance_department_filter_Lead')) && $request->session()->get('attendance_department_filter_Lead') != 'All')
		{
			$dept = $request->session()->get('attendance_department_filter_Lead');
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









		
        if(!empty($request->session()->get('emp_leaves_todate')) && $request->session()->get('emp_leaves_todate') != 'All')
        {
            $dateto = $request->session()->get('emp_leaves_todate');
            if($whereraw == '')
            {
                $whereraw = 'leaves_request.created_at<= "'.$dateto.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And leaves_request.created_at<= "'.$dateto.' 00:00:00"';
            }
        }



        if($whereraw != '')
		{
           
			// print_r($whereraw);
			// exit;
			
			
			$loggedinUserid=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $requestedLeaves = Employee_details::whereRaw($whereraw)
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('id', 'desc')
                    //->toSql();
                    //dd($requestedLeaves);
                    ->paginate($paginationValue);

                    $reportsCount = Employee_details::whereRaw($whereraw)
                    
                    ->orderBy('id', 'desc')
                    ->get()->count();
                }
            }
            else
            {
                $empData = Employee_details::whereRaw($whereraw)->orderBy('id','desc')
                 //->toSql();
                // dd($empData);
                ->paginate($paginationValue);

                $reportsCount = Employee_details::whereRaw($whereraw)
                ->get()->count();
            }
            
            
            
            

        }
        else
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
				$empData = Employee_details::orderBy('id','desc')
				//->get();
				->paginate($paginationValue);
				$reportsCount = Employee_details::orderBy('id', 'desc')
                ->get()->count();
			}

			//return view("DepartmentAttendance/departmentMarkAttendance",compact('empData'));
			
		}

		if($request->session()->get('attendance_month_filter_Lead')!='')
		{
			$attendanceDate = $request->session()->get('attendance_month_filter_Lead');
		}
		else
		{
			$attendanceDate = '';
		}
        
        
			
		//$empData->setPath(config('app.url/listingAttendance'));		
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


			$request->session()->put('range_filter_inner_list',$rangeid);
			$request->session()->put('empid_emp_offboard_filter_inner_list',$empId);
			
			$request->session()->put('attendance_department_filter_Lead',$department);
			// $request->session()->put('teamleader_filter_inner_list',$teamlaed);
			
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
        
        
        
        $request->session()->put('attendance_department_filter_Lead','');
        // $request->session()->put('teamleader_filter_inner_list','');
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
 
}
