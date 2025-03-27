<?php
namespace App\Http\Controllers\Attendance;

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
use Session;
use App\Models\AdvancedPay\AdvancedPayRequest;
use App\Models\AdvancedPay\RecoveryAmt;
use Artisan;
use App\Models\ChangeDepartment\ChangeDepartmentRequestLog;

use App\User;
use App\Models\Entry\Employee;
use App\Models\AdvancedPay\AdvancedPayLogs;

class AppAttendanceController extends Controller
{
	public  function Index(Request $request)
	{
        
 
        //$empDetailsIndex = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();
        $empDetailsIndex = EmpAttendance::join('employee_details', 'employee_details.emp_id', '=', 'emp_attendance.emp_id')->groupBy('emp_attendance.emp_id')->orderBy('emp_attendance.id', 'desc')->get();

        //$empDetailsIndex = EmpAttendance::groupBy('emp_id')->orderBy('id', 'desc')->get();



        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $designationLists=Designation::where("status",1)->get();

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
		else
		{
			$design=Designation::where("tlsm",2)->where("status",1)->get();
			$designarray=array();
			foreach($design as $_design){
				$designarray[]=$_design->id;
			}
			$finalarray=implode(",",$designarray);			
			$tL_details = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->get();
		}
        return view("Attendance/index",compact('empDetailsIndex','departmentLists','designationLists','tL_details'));
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


    public function allAttendanceDataListing(Request $request)
    {
        $whereraw = '';
        $whererawattendanceDate = '';
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
        
        
        if(!empty($request->session()->get('appAttendanceRequest_page_limit')))
        {
            $paginationValue = $request->session()->get('appAttendanceRequest_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('appAttendance_requests_emp_name')) && $request->session()->get('appAttendance_requests_emp_name') != 'All')
        {
            $fname = $request->session()->get('appAttendance_requests_emp_name');
            
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                //print_r($cnameArray);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
                //print_r($namefinalarray);

                // if (count($cnameArray) === 0) 
                // {
                //     // list is empty.
                //     echo "Sample";
                // }
                // else
                // {
                //     echo "Hello";
                // }
                // exit;
    
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
            }


           
        }


        if(!empty($request->session()->get('appAttendance_requests_emp_id')) && $request->session()->get('appAttendance_requests_emp_id') != 'All')
        {
            $empId = $request->session()->get('appAttendance_requests_emp_id');
            if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('appAttendance_fromdate')) && $request->session()->get('appAttendance_fromdate') != 'All')
        {
            $datefrom = $request->session()->get('appAttendance_fromdate');
            if($whererawattendanceDate == '')
            {
                $whererawattendanceDate = 'attendance_date>= "'.$datefrom.'"';
            }
            else
            {
                $whererawattendanceDate .= ' And attendance_date>= "'.$datefrom.'"';
            }
        }
        if(!empty($request->session()->get('appAttendance_todate')) && $request->session()->get('appAttendance_todate') != 'All')
        {
            $dateto = $request->session()->get('appAttendance_todate');
            if($whererawattendanceDate == '')
            {
                $whererawattendanceDate = 'attendance_date<= "'.$dateto.'"';
            }
            else
            {
                $whererawattendanceDate .= ' And attendance_date<= "'.$dateto.'"';
            }
        }



        if(!empty($request->session()->get('appAttendnace_requests_dept')) && $request->session()->get('appAttendnace_requests_dept') != 'All')
        {
            $deptid = $request->session()->get('appAttendnace_requests_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('appAttendnace_requests_designation')) && $request->session()->get('appAttendnace_requests_designation') != 'All')
        {
            $desigid = $request->session()->get('appAttendnace_requests_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('appAttendance_requests_tl')) && $request->session()->get('appAttendance_requests_tl') != 'All')
        {
            $tlid = $request->session()->get('appAttendance_requests_tl');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }


        //$whereraw='';
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);


        if($whereraw != '' && $whererawattendanceDate =='')
		{
            // echo "<pre>";
            // echo "Hello";
            // print_r($whereraw);
            // print_r($whererawattendanceDate);
            // exit;
            if($empData==1) // all
            {
				
                $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                $requestDetails = EmpAttendance::whereIn('emp_id',$newResult)->where('approve_status',1)->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = EmpAttendance::whereIn('emp_id',$newResult)->where('approve_status',1)->orderBy('id','desc')
                ->get()->count();
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();                
                
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();


                // $empDetails = Employee_details::whereRaw($whereraw)->where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                // ->get();

                $empDetails = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                
                $requestDetails = EmpAttendance::whereIn('emp_id',$newResult)->where('approve_status',1)->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                $reportsCount = EmpAttendance::whereIn('emp_id',$newResult)->where('approve_status',1)->orderBy('id','desc')
                ->get()->count();
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();
            }
        }
        elseif($whereraw != '' && $whererawattendanceDate !='')
		{
            // echo "<pre>";
            // echo "Sample";
            // print_r($whereraw);
            // print_r($whererawattendanceDate);
            // exit;
            if($empData==1) // all
            {
                $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                $requestDetails = EmpAttendance::whereIn('emp_id',$newResult)->whereRaw($whererawattendanceDate)->where('approve_status',1)->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = EmpAttendance::whereIn('emp_id',$newResult)->whereRaw($whererawattendanceDate)->where('approve_status',1)->orderBy('id','desc')
                ->get()->count();
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();                
                
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();


                //$empDetails = Employee_details::whereRaw($whereraw)->where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                //->get();

                $empDetails = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                
                $requestDetails = EmpAttendance::whereIn('emp_id',$newResult)->whereRaw($whererawattendanceDate)->where('approve_status',1)->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                $reportsCount = EmpAttendance::whereIn('emp_id',$newResult)->whereRaw($whererawattendanceDate)->where('approve_status',1)->orderBy('id','desc')
                ->get()->count();
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();
            }
        }
        elseif($whereraw == '' && $whererawattendanceDate !='')
		{
            // echo "<pre>";
            // echo "Lorem lipsum";
            // print_r($whereraw);
            // print_r($whererawattendanceDate);
            // exit;
            if($empData==1) // all
            {
                // $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
                // ->get();
                
                // $newResult=array();
                // foreach($empDetails as $value)
                // {
                //     $newResult[]=$value->emp_id;
                // }
                
                $requestDetails = EmpAttendance::whereRaw($whererawattendanceDate)->where('approve_status',1)->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = EmpAttendance::whereRaw($whererawattendanceDate)->where('approve_status',1)->orderBy('id','desc')
                ->get()->count();
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();                
                
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();


                // $empDetails = Employee_details::where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                // ->get();

                $empDetails = Employee_details::where('tl_id',$empDetails->id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                
                $requestDetails = EmpAttendance::whereIn('emp_id',$newResult)->whereRaw($whererawattendanceDate)->where('approve_status',1)->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                $reportsCount = EmpAttendance::whereIn('emp_id',$newResult)->whereRaw($whererawattendanceDate)->where('approve_status',1)->orderBy('id','desc')
                ->get()->count();
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();
            }
        }
        else
        {
			
            // echo "Demo";
            // exit;
            if($empData==1) // all
            {
				
                $currDate = date('Y-m-d');
                //$currDate = date('2024-11-25');
                //$emparr=array(102482,102686);
                //$requestDetails = EmpAttendance::where('attendance_date','2024-11-25')->whereIn('emp_id',$emparr)->orderBy('id', 'desc')
                $requestDetails = EmpAttendance::where('attendance_date',$currDate)->where('approve_status',1)->orderBy('id', 'desc')
                //->toSql();	 
                //dd($documentCollectiondetails);						
                ->paginate($paginationValue);
                
              /*   print_r($requestDetails);
				exit; */
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();
                
                $reportsCount = EmpAttendance::where('attendance_date',$currDate)->where('approve_status',1)->orderBy('id', 'desc')
                ->get()->count();
            }
            else // specific dept
            {
                $currDate = date('Y-m-d');
                //$currDate = date('2024-11-25');
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                //$empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
                
                
                // $empDetails = Employee_details::where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                // ->get();



                $empDetails = Employee_details::where('tl_id',$empDetails->id)->orderBy('id', 'desc')
                ->get();


                // echo "<pre>";
                // print_r($empDetails);
                // exit;
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }


                $requestDetails = EmpAttendance::whereIn('emp_id',$newResult)->where('attendance_date',$currDate)->where('approve_status',1)->orderBy('id', 'desc')
                //->toSql();	 
                //dd($documentCollectiondetails);						
                ->paginate($paginationValue);
                
                $reportsCount = EmpAttendance::whereIn('emp_id',$newResult)->where('attendance_date',$currDate)->where('approve_status',1)->orderBy('id','desc')
                ->get()->count();

                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();
            }
        }

        //return $requestDetails;
        
        $requestDetails->setPath(config('app.url/listingAll'));
        return view("Attendance/listingAll",compact('requestDetails','paginationValue','reportsCount','attendanceDetails'));
    }

    
    // public static function addrequestPopData(Request $request)
    // {
    //     $loggedinUserid=$request->session()->get('EmployeeId');
    //     $empRequested = AdvancedPayRequest::select('emp_id')->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id','desc')->get();

    //     $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
    //     if($departmentDetails != '')
    //     {
    //         $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
    //         if($empDetails!='')
    //         {
	// 			$departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
    //             $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
    //             $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();

    //             $empDetails = Employee_details::where('dept_id',$empDetails->dept_id)->where('offline_status',1)->whereNotIn('emp_id', $empRequested)->orderBy('id', 'desc')
    //             ->get();
    //         }
    //     }
    //     else
    //     {
	// 		$empDetails = Employee_details::where('offline_status',1)->whereNotIn('emp_id', $empRequested)->orderBy('id', 'desc')->get();		
    //     }
        
    //     //$empDetails = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();

    //     return view("AdvancedPay/addRequest",compact('empDetails'));
    // }

    // public static function getEmpContentData(Request $request)
    // {
    //     $empid=$request->empid;
    //     $empDetails='';
    //     $empAdvancedDetails = AdvancedPayRequest::where('emp_id',$empid)->where('approved_reject_status',1)->orderBy('id', 'desc')->get();

    //     $empRecoveryDetails = RecoveryAmt::where('emp_id',$empid)->orderBy('id', 'desc')->get();

    //     if($empAdvancedDetails)
    //     {
    //         $empDetails = $empAdvancedDetails;
    //     }


    //     $approvedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('approved_advanced_amt');
    //     $recoveredAmt = RecoveryAmt::where('emp_id',$empid)->sum('recovery_amt');
    //     $balancedAmt = $approvedAmt - $recoveredAmt;

    //     return view("AdvancedPay/addRequestContent",compact('empDetails','empRecoveryDetails','balancedAmt'));

    // }

    // public function advancedPayRequestPostSubmit(Request $request)
    // {
    //     $validator = Validator::make($request->all(), 
    //     [			
	// 		'advancedamt' => 'required|numeric|integer|gte:1',
    //         'addRequestEmp' => 'required',
    //        // 'teamleaders' => 'required', 
    //     ],
	// 	[
	// 		'advancedamt.required'=> 'Please Fill Amount',
    //         'advancedamt.numeric'=> 'Amount must be a number.',
	// 	 	'addRequestEmp.required'=> 'Please Select Employee from List',
    //         'advancedamt.integer'=> 'The Advanced Amount must be an integer',
    //         'advancedamt.gte'=> 'The Advanced Amount must be greater than or equal 1',
				
	// 	]);

	// 	if(($validator->fails()))
	// 	{
	// 		return response()->json(['error'=>$validator->errors()]);
	// 	}
	// 	else
	// 	{
    //         //return $request->all();
    //         $usersessionId=$request->session()->get('EmployeeId');
    //         //$transferRequestData = AdvancedPayRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

    //         $advancedPayRequest = new AdvancedPayRequest();
    //         $advancedPayRequest->emp_id = $request->addRequestEmp;
    //         $advancedPayRequest->requested_advanced_amt = $request->advancedamt;
    //         $advancedPayRequest->requested_at = date('Y-m-d H:i:s');
    //         $advancedPayRequest->requested_by = $usersessionId; 
    //         $advancedPayRequest->request_status = 1;
    //         $advancedPayRequest->request_comments = $request->requestComments;
    //         $advancedPayRequest->status = 1;
    //         $advancedPayRequest->save();


            

    //         $advancedPayLogs = new AdvancedPayLogs();
	// 		$advancedPayLogs->emp_id = $request->empid;
    //         $advancedPayLogs->event_at = date('Y-m-d H:i:s');;
    //         $advancedPayLogs->event_by = $usersessionId;
    //         $advancedPayLogs->event = 1;
    //         $advancedPayLogs->requested_amt = $request->advancedamt;            
    //         $advancedPayLogs->save();


    //         return response()->json(['success'=>'Advanced Pay Request Added Successfully.']);
    //     }
    // }


    public function approvedAttendancesListingData(Request $request)
    {
        $whereraw = '';
		$whereraw1 = '';
        $whererawattendanceDate = '';
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
        
        if(!empty($request->session()->get('appAttendanceRequest_page_limit')))
        {
            $paginationValue = $request->session()->get('appAttendanceRequest_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	



        if(!empty($request->session()->get('appAttendance_requests_emp_name')) && $request->session()->get('appAttendance_requests_emp_name') != 'All')
        {
            $fname = $request->session()->get('appAttendance_requests_emp_name');
            
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                //print_r($cnameArray);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
                //print_r($namefinalarray);

                // if (count($cnameArray) === 0) 
                // {
                //     // list is empty.
                //     echo "Sample";
                // }
                // else
                // {
                //     echo "Hello";
                // }
                // exit;
    
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
            }


           
        }


        if(!empty($request->session()->get('appAttendance_requests_emp_id')) && $request->session()->get('appAttendance_requests_emp_id') != 'All')
        {
            $empId = $request->session()->get('appAttendance_requests_emp_id');
            if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('appAttendance_fromdate')) && $request->session()->get('appAttendance_fromdate') != 'All')
        {
            $datefrom = $request->session()->get('appAttendance_fromdate');
            if($whererawattendanceDate == '')
            {
                $whererawattendanceDate = 'attendance_date>= "'.$datefrom.'"';
            }
            else
            {
                $whererawattendanceDate .= ' And attendance_date>= "'.$datefrom.'"';
            }
        }
        if(!empty($request->session()->get('appAttendance_todate')) && $request->session()->get('appAttendance_todate') != 'All')
        {
            $dateto = $request->session()->get('appAttendance_todate');
            if($whererawattendanceDate == '')
            {
                $whererawattendanceDate = 'attendance_date<= "'.$dateto.'"';
            }
            else
            {
                $whererawattendanceDate .= ' And attendance_date<= "'.$dateto.'"';
            }
        }

        if(!empty($request->session()->get('appAttendnace_requests_dept')) && $request->session()->get('appAttendnace_requests_dept') != 'All')
        {
            $deptid = $request->session()->get('appAttendnace_requests_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('appAttendnace_requests_designation')) && $request->session()->get('appAttendnace_requests_designation') != 'All')
        {
            $desigid = $request->session()->get('appAttendnace_requests_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('appAttendance_requests_tl')) && $request->session()->get('appAttendance_requests_tl') != 'All')
        {
            $tlid = $request->session()->get('appAttendance_requests_tl');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }

        
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        

        
        if($whereraw != '' && $whererawattendanceDate =='')
		{
            // echo "<pre>";
            // echo "Hello";
            // print_r($whereraw);
            // print_r($whererawattendanceDate);
            // exit;
            if($empData==1) // all
            {
                $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                $requestDetails = EmpAttendance::whereIn('emp_id',$newResult)->where('approve_status',2)->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = EmpAttendance::whereIn('emp_id',$newResult)->where('approve_status',2)->orderBy('id','desc')
                ->get()->count();
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();                
                
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();


                // $empDetails = Employee_details::whereRaw($whereraw)->where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                // ->get();

                $empDetails = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                
                $requestDetails = EmpAttendance::whereIn('emp_id',$newResult)->where('approve_status',2)->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                $reportsCount = EmpAttendance::whereIn('emp_id',$newResult)->where('approve_status',2)->orderBy('id','desc')
                ->get()->count();
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();
            }
        }
        elseif($whereraw != '' && $whererawattendanceDate !='')
		{
            // echo "<pre>";
            // echo "Sample";
            // print_r($whereraw);
            // print_r($whererawattendanceDate);
            // exit;
            if($empData==1) // all
            {
                $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                $requestDetails = EmpAttendance::whereIn('emp_id',$newResult)->whereRaw($whererawattendanceDate)->where('approve_status',2)->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = EmpAttendance::whereIn('emp_id',$newResult)->whereRaw($whererawattendanceDate)->where('approve_status',2)->orderBy('id','desc')
                ->get()->count();
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();                
                
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();


                //$empDetails = Employee_details::whereRaw($whereraw)->where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                //->get();

                $empDetails = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                
                $requestDetails = EmpAttendance::whereIn('emp_id',$newResult)->whereRaw($whererawattendanceDate)->where('approve_status',2)->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                $reportsCount = EmpAttendance::whereIn('emp_id',$newResult)->whereRaw($whererawattendanceDate)->where('approve_status',2)->orderBy('id','desc')
                ->get()->count();
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();
            }
        }
        elseif($whereraw == '' && $whererawattendanceDate !='')
		{
            // echo "<pre>";
            // echo "Lorem lipsum";
            // print_r($whereraw);
            // print_r($whererawattendanceDate);
            // exit;
            if($empData==1) // all
            {
                // $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
                // ->get();
                
                // $newResult=array();
                // foreach($empDetails as $value)
                // {
                //     $newResult[]=$value->emp_id;
                // }
                
                $requestDetails = EmpAttendance::whereRaw($whererawattendanceDate)->where('approve_status',2)->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = EmpAttendance::whereRaw($whererawattendanceDate)->where('approve_status',2)->orderBy('id','desc')
                ->get()->count();
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();                
                
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();


                // $empDetails = Employee_details::where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                // ->get();

                $empDetails = Employee_details::where('tl_id',$empDetails->id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                
                $requestDetails = EmpAttendance::whereIn('emp_id',$newResult)->whereRaw($whererawattendanceDate)->where('approve_status',2)->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                $reportsCount = EmpAttendance::whereIn('emp_id',$newResult)->whereRaw($whererawattendanceDate)->where('approve_status',2)->orderBy('id','desc')
                ->get()->count();
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();
            }
        }
        else
        {
            // echo "Demo";
            // exit;
            if($empData==1) // all
            {
                //$currDate = date('Y-m-d');
                $currDate = date('2024-11-25');
                //$emparr=array(102482,102686);
                //$requestDetails = EmpAttendance::where('attendance_date','2024-11-25')->whereIn('emp_id',$emparr)->orderBy('id', 'desc')
                $requestDetails = EmpAttendance::where('attendance_date',$currDate)->where('approve_status',2)->orderBy('id', 'desc')
                //->toSql();	 
                //dd($documentCollectiondetails);						
                ->paginate($paginationValue);
                
                
                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();
                
                $reportsCount = EmpAttendance::where('attendance_date',$currDate)->where('approve_status',2)->orderBy('id', 'desc')
                ->get()->count();
            }
            else // specific dept
            {
                $currDate = date('2024-11-25');
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                //$empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
                
                
                // $empDetails = Employee_details::where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                // ->get();



                $empDetails = Employee_details::where('tl_id',$empDetails->id)->orderBy('id', 'desc')
                ->get();


                // echo "<pre>";
                // print_r($empDetails);
                // exit;
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }


                $requestDetails = EmpAttendance::whereIn('emp_id',$newResult)->where('attendance_date',$currDate)->where('approve_status',2)->orderBy('id', 'desc')
                //->toSql();	 
                //dd($documentCollectiondetails);						
                ->paginate($paginationValue);
                
                $reportsCount = EmpAttendance::whereIn('emp_id',$newResult)->where('attendance_date',$currDate)->where('approve_status',2)->orderBy('id','desc')
                ->get()->count();

                $attendanceDetails = Attendance::orderBy('id', 'desc')->get();
            }
        }



        
        $requestDetails->setPath(config('app.url/listingApprovedAttendnace'));
        return view("Attendance/listingApprovedAttendnace",compact('requestDetails','paginationValue','reportsCount'));
    }


    // public function getAdvancedPayRequestContent(Request $request)
    // {
    //     $empid=$request->empid;
    //     $rowid=$request->rowid;

    //     $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
    //     $tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();

    //     $requestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();

    //     return view("AdvancedPay/advancedPayRequestContent",compact('requestData','departmentLists','tL_details'));
    // }

    // public function advancedPayRequestSubmitApproved(Request $request)
    // {
    //     $validator = Validator::make($request->all(), 
    //     [			
	// 		'approvedamt' => 'required|numeric|integer|lte:requestedamount|gte:1',
    //         'amtReleasedate' => 'required|date',
    //        // 'teamleaders' => 'required', 
    //     ],
	// 	[
	// 		'approvedamt.required'=> 'Please Fill Amount',
    //         'approvedamt.numeric'=> 'Amount must be a number.',
	// 	 	'amtReleasedate.required'=> 'Please Select Amount Release Date',
	// 		'approvedamt.lte'=> 'The Approved Amount must be less than or equal to Requested Amount',
    //         'approvedamt.integer'=> 'The Approved Amount must be an integer',
    //         'approvedamt.gte'=> 'The Approved Amount must be greater than or equal 1',
				
	// 	]);

	// 	if(($validator->fails()))
	// 	{
	// 		return response()->json(['error'=>$validator->errors()]);
	// 	}
	// 	else
	// 	{
    //         //return $request->all();
    //         $usersessionId=$request->session()->get('EmployeeId');
    //         $advancedPayRequest = AdvancedPayRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

            
    //         //$advancedPayRequest->emp_id = $request->addRequestEmp;
    //         $advancedPayRequest->approved_advanced_amt = $request->approvedamt;
    //         $advancedPayRequest->approved_reject_at = date('Y-m-d H:i:s');
    //         $advancedPayRequest->amt_release_date = $request->amtReleasedate;
    //         $advancedPayRequest->approved_reject_by = $usersessionId; 
    //         $advancedPayRequest->approved_reject_status = 1;
    //         $advancedPayRequest->approved_reject_comments = $request->requestUpdatedComments;
    //         $advancedPayRequest->status = 2;
    //         $advancedPayRequest->save();

    //         $advancedPayLogs = new AdvancedPayLogs();
	// 		$advancedPayLogs->emp_id = $request->empid;
    //         $advancedPayLogs->event_at = date('Y-m-d H:i:s');;
    //         $advancedPayLogs->event_by = $usersessionId;
    //         $advancedPayLogs->event = 2;
    //         $advancedPayLogs->approved_amt = $request->approvedamt;            
    //         $advancedPayLogs->save();


    //         return response()->json(['success'=>'Advanced Pay Request Approved.']);
    //     }
    // }


    // public function advancedPayRequestSubmitRejected(Request $request)
    // {
    //     $validator = Validator::make($request->all(), 
    //     [			
	// 		'requestUpdatedComments' => 'required',
    //         //'addRequestEmp' => 'required',
    //        // 'teamleaders' => 'required', 
    //     ],
	// 	[
	// 		'requestUpdatedComments.required'=> 'Please Write Some Comments',
	// 	 	//'addRequestEmp.required'=> 'Please Select Employee from List',
	// 		//'teamleaders.required'=> 'Please Select Team Leader from List',
				
	// 	]);

	// 	if(($validator->fails()))
	// 	{
	// 		return response()->json(['error'=>$validator->errors()]);
	// 	}
	// 	else
	// 	{
    //         //return $request->all();
    //         $usersessionId=$request->session()->get('EmployeeId');
    //         $advancedPayRequest = AdvancedPayRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

    //         $advancedPayRequest->approved_reject_at = date('Y-m-d H:i:s');
    //         $advancedPayRequest->approved_reject_by = $usersessionId; 
    //         $advancedPayRequest->approved_reject_status = 2;
    //         $advancedPayRequest->approved_reject_comments = $request->requestUpdatedComments;
    //         $advancedPayRequest->status = 2;
    //         $advancedPayRequest->save();


            

    //         $advancedPayLogs = new AdvancedPayLogs();
	// 		$advancedPayLogs->emp_id = $request->empid;
    //         $advancedPayLogs->event_at = date('Y-m-d H:i:s');;
    //         $advancedPayLogs->event_by = $usersessionId;
    //         $advancedPayLogs->event = 3;            
    //         $advancedPayLogs->save();


    //         return response()->json(['success'=>'Advanced Pay Request Rejected.']);
    //     }
    // }


    // public function advancedPayFinalListing(Request $request)
    // {
    //     $whereraw = '';
	// 	$whereraw1 = '';
	// 	$selectedFilter['CNAME'] = '';
	// 	$selectedFilter['CEMAIL'] = '';
	// 	$selectedFilter['DESC'] = '';
	// 	$selectedFilter['DEPT'] = '';
	// 	$selectedFilter['OPENING'] = '';
	// 	$selectedFilter['STATUS'] = '';
	// 	$selectedFilter['vintage'] = '';
	// 	$selectedFilter['Company'] = '';
	// 	$selectedFilter['Recruiter'] = '';
		
        
    //     $filterList = array();
    //     $filterList['deptID'] = '';
    //     $filterList['productID'] = '';
    //     $filterList['designationID'] = '';
    //     $filterList['emp_name'] = '';
    //     $filterList['caption'] = '';
    //     $filterList['status'] = '';
    //     $filterList['serialized_id'] = '';
    //     $filterList['visa_process_status'] = '';
        
    //     if(!empty($request->session()->get('appAttendanceRequest_page_limit')))
    //     {
    //         $paginationValue = $request->session()->get('appAttendanceRequest_page_limit');
    //     }
    //     else
    //     {
    //         $paginationValue = 100;
    //     }	



    //     if(!empty($request->session()->get('advancedpay_requests_emp_name')) && $request->session()->get('advancedpay_requests_emp_name') != 'All')
    //     {
    //         $fname = $request->session()->get('advancedpay_requests_emp_name');
    //         if($fname==',')
    //         {               
    //         }
    //         else
    //         {
    //             $cnameArray = explode(",",$fname);
                
    //             $namefinalarray=array();
    //             foreach($cnameArray as $namearray){
    //                 $namefinalarray[]="'".$namearray."'";                
    //             }
    
    //             $finalcname=implode(",", $namefinalarray);
                
    //             if($whereraw == '')
    //             {
    //                 //$whereraw = 'emp_name like "%'.$fname.'%"';
    //                 $whereraw = 'emp_name IN('.$finalcname.')';
    //             }
    //             else
    //             {
    //                 $whereraw .= ' And emp_name IN('.$finalcname.')';
    //             }
    //         }
    //     }


    //     if(!empty($request->session()->get('advancedpay_requests_emp_id')) && $request->session()->get('advancedpay_requests_emp_id') != 'All')
    //     {
    //         $empId = $request->session()->get('advancedpay_requests_emp_id');
    //             if($whereraw == '')
    //         {
    //             $whereraw = 'emp_id IN ('.$empId.')';
    //         }
    //         else
    //         {
    //             $whereraw .= ' And emp_id IN ('.$empId.')';
    //         }
    //     }

    //     if(!empty($request->session()->get('advancedpay_requests_dept')) && $request->session()->get('advancedpay_requests_dept') != 'All')
    //     {
    //         $deptid = $request->session()->get('advancedpay_requests_dept');
    //             if($whereraw == '')
    //         {
    //             $whereraw = 'dept_id IN ('.$deptid.')';
    //         }
    //         else
    //         {
    //             $whereraw .= ' And dept_id IN ('.$deptid.')';
    //         }
    //     }


    //     if(!empty($request->session()->get('advancedpay_requests_designation')) && $request->session()->get('advancedpay_requests_designation') != 'All')
    //     {
    //         $desigid = $request->session()->get('advancedpay_requests_designation');
    //             if($whereraw == '')
    //         {
    //             $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
    //         }
    //         else
    //         {
    //             $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
    //         }
    //     }

    //     if(!empty($request->session()->get('advancedpay_requests_tl')) && $request->session()->get('advancedpay_requests_tl') != 'All')
    //     {
    //         $tlid = $request->session()->get('advancedpay_requests_tl');
    //             if($whereraw == '')
    //         {
    //             $whereraw = 'tl_id  IN ('.$tlid.')';
    //         }
    //         else
    //         {
    //             $whereraw .= ' And tl_id  IN ('.$tlid.')';
    //         }
    //     }

        
    //     $loggedinUserid=$request->session()->get('EmployeeId');
    //     $empData = $this->getLoggedinUser($loggedinUserid);

    //     if($whereraw != '')
	// 	{
    //         // echo "<pre>";
    //         // print_r($whereraw);
    //         // exit;

    //         if($empData==1) // all
    //         {
    //             $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
    //             ->get();
                
    //             $newResult=array();
    //             foreach($empDetails as $value)
    //             {
    //                 $newResult[]=$value->emp_id;
    //             }

    //             // new code
    //             $requestPayDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->groupBy('emp_id')
    //             ->get();
                
    //             $newResultLast=array();
    //             foreach($requestPayDetails as $value)
    //             {
    //                 $newResultLast[]=$value->id;
    //             }
                
    //             $requestDetails = AdvancedPayRequest::whereIn('id',$newResultLast)->orderBy('id', 'desc')						
    //             ->paginate($paginationValue);	
                
    //             $reportsCount = AdvancedPayRequest::whereIn('id',$newResultLast)->orderBy('id', 'desc')
    //             ->get()->count();
    //             // new code
                
    //             // $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')					
    //             // ->paginate($paginationValue);

    //             // $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')
    //             // ->get()->count();
                
    //         }
    //         else // specific dept
    //         {
    //             $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
    //             $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
    //             $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();

    //             $empDetails = Employee_details::whereRaw($whereraw)->where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
    //             ->get();
                
    //             $newResult=array();
    //             foreach($empDetails as $value)
    //             {
    //                 $newResult[]=$value->emp_id;
    //             }


    //              // new code
    //              $requestPayDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->groupBy('emp_id')
    //              ->get();
                 
    //              $newResultLast=array();
    //              foreach($requestPayDetails as $value)
    //              {
    //                  $newResultLast[]=$value->id;
    //              }
                 
    //              $requestDetails = AdvancedPayRequest::whereIn('id',$newResultLast)->orderBy('id', 'desc')						
    //              ->paginate($paginationValue);	
                 
    //              $reportsCount = AdvancedPayRequest::whereIn('id',$newResultLast)->orderBy('id', 'desc')
    //              ->get()->count();
    //              // new code
                
    //             // $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')					
    //             // ->paginate($paginationValue);
    //             // $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')
    //             // ->get()->count();
    //         }
    //     }
    //     else
    //     {
    //         if($empData==1) // all
    //         {
    //             $requestPayDetails = AdvancedPayRequest::where('request_status',1)->where('approved_reject_status',1)->groupBy('emp_id')
    //             ->get();
                
    //             $newResult=array();
    //             foreach($requestPayDetails as $value)
    //             {
    //                 $newResult[]=$value->id;
    //             }
                
    //             $requestDetails = AdvancedPayRequest::whereIn('id',$newResult)->orderBy('id', 'desc')						
    //             ->paginate($paginationValue);	
                
    //             $reportsCount = AdvancedPayRequest::whereIn('id',$newResult)->orderBy('id', 'desc')
    //             ->get()->count();

    //         }
    //         else // specific dept
    //         {
    //             $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
    //             $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
    //             $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
                
    //             $empDetails = Employee_details::where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
    //             ->get();
                
    //             $newResult=array();
    //             foreach($empDetails as $value)
    //             {
    //                 $newResult[]=$value->emp_id;
    //             }

    //             //new code start
                
    //             $requestPayDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->groupBy('emp_id')
    //             ->get();
                
    //             $newResultFinal=array();
    //             foreach($requestPayDetails as $value)
    //             {
    //                 $newResultFinal[]=$value->id;
    //             }
                
                
    //             $requestDetails = AdvancedPayRequest::whereIn('id',$newResultFinal)->orderBy('id', 'desc')						
    //             ->paginate($paginationValue);

    //             $reportsCount = AdvancedPayRequest::whereIn('id',$newResultFinal)->orderBy('id', 'desc')
    //             ->get()->count();
    //             // new code end

    //         }
    //     }
        
    //     $requestDetails->setPath(config('app.url/listingAdvancedPayFinalRequests'));
    //     return view("AdvancedPay/listingAdvancedPayFinalRequests",compact('requestDetails','paginationValue','reportsCount'));
    // }

    public function searchAppAttendanceRequestFilter(Request $request)
	{
			$department='';
			if($request->input('appAttendnacedepartment')!=''){
			 
			 $department=implode(",", $request->input('appAttendnacedepartment'));
			}

            $newDepartment='';
			if($request->input('newDepartment')!=''){
			 
			 $newDepartment=implode(",", $request->input('newDepartment'));
			}


			$teamlaed='';
			if($request->input('appAttendancteamlaed')!=''){
			 
			 $teamlaed=implode(",", $request->input('appAttendancteamlaed'));
			}
			$dateto = $request->input('datetoappAttendance');
			$datefrom = $request->input('datefromappAttendance');
			$name='';
			if($request->input('appAttendanceemp_name')!=''){
			 
			 $name=implode(",", $request->input('appAttendanceemp_name'));
			}
			//$name = $request->input('emp_name');
			$empId='';
			if($request->input('appAttendanceempId')!=''){
			 
			 $empId=implode(",", $request->input('appAttendanceempId'));
			}
			$design='';
			if($request->input('appAttendnacedesignationdata')!=''){
			 
			 $design=implode(",", $request->input('appAttendnacedesignationdata'));
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
			if($request->input('rangeid')!=''){
			 
			 $rangeid=implode(",", $request->input('rangeid'));
			}

			$request->session()->put('appAttendance_requests_emp_name',$name);
            $request->session()->put('appAttendance_requests_emp_id',$empId);
            $request->session()->put('appAttendnace_requests_dept',$department);
            $request->session()->put('transfer_requests_new_dept',$newDepartment);
            $request->session()->put('appAttendnace_requests_designation',$design);
            $request->session()->put('appAttendance_requests_tl',$teamlaed);




            $request->session()->put('appAttendance_fromdate',$datefrom);
            $request->session()->put('appAttendance_todate',$dateto);


			$request->session()->put('range_filter_inner_list',$rangeid);
			$request->session()->put('empid_emp_offboard_filter_inner_list',$empId);
			
			//$request->session()->put('departmentId_filter_inner_list',$department);
			
			
			
			$request->session()->put('dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			
			$request->session()->put('ReasonofAttrition_empoffboard_filter_list',$ReasonofAttrition);
			$request->session()->put('empoffboard_status_filter_list',$offboardstatus);
			$request->session()->put('datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('dateto_offboard_dort_list',$datetodort);
			$request->session()->put('empoffboard_ffstatus_filter_list',$offboardffstatus);
			 //return  redirect('listingPageonboarding');	
	}

    public function resetAppAttendanceRequestFilter(Request $request)
    {
        $request->session()->put('appAttendance_requests_emp_name','');
        $request->session()->put('appAttendance_requests_emp_id','');
        $request->session()->put('appAttendnace_requests_dept','');
        $request->session()->put('transfer_requests_new_dept','');
        $request->session()->put('appAttendnace_requests_designation','');
        $request->session()->put('appAttendance_requests_tl','');



        


        $request->session()->put('appAttendance_fromdate','');
		$request->session()->put('appAttendance_todate','');
        
        
    }


    public static function getDepartment($empid)
    {
            $emp_details = Employee_details::where("emp_id",$empid)->orderBy("id","desc")->first();

            if($emp_details)
            {
                $deptInfo = Department::where('id',$emp_details->dept_id)->orderBy('id', 'desc')->first();
                if($deptInfo)
                {
                    return $deptInfo->department_name;
                }
                else
                {
                    return '--';
                }
            }
            else
            {
                return '--';
            }
    }


    // public static function getRequestStatus($empid,$rowid)
    // {
    //     $requestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();

    //     if($requestData)
    //     {
    //         if($requestData->approved_reject_status==1)
    //         {
    //             return "Request Approved";
    //         }
    //         if($requestData->approved_reject_status==2)
    //         {
    //             return "Request Rejected";
    //         }
    //         if($requestData->request_status==1 && $requestData->approved_reject_status==NULL)
    //         {
    //             return "Pending for Approval/Reject";
    //         }
    //     }
    //     else
    //     {
    //         return '--';
    //     }
    // }

    // public function summaryTabWithFullViewAjax(Request $request)
	// {
	// 	    $empid = $request->empid;
	// 		$rowid = $request->rowid;

    //         $advancedPayRequestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();

	// 		$completedStep = 0;
	// 		$OnboardingProgress = '';
	// 		$stepsAll = array();
	// 		/*Step1*/
		    



    //         $stepsAll[0]['name'] = 'Approved/Reject'; 
    //         if($advancedPayRequestData->request_status == 1  && $advancedPayRequestData->approved_reject_status == 0)
    //         {
    //             $stepsAll[0]['stage'] = 'inprogress';
    //             $stepsAll[0]['Tab'] = 'active'; 
    //         }
    //         else
    //         {
    //             $completedStep++;
    //             $stepsAll[0]['stage'] = 'active'; 
    //             $OnboardingProgress = 'Approved/Reject';
    //             $stepsAll[0]['Tab'] = 'active'; 
    //         }
    //         $stepsAll[0]['slagURL'] = 'tab2'; 
    //         //$stepsAll[0]['tab'] = 'active'; 
    //         $stepsAll[0]['onclick'] = 'tab2Panel();';            
    //         $OnboardingProgress = 'Approved/Reject';
    //         /*Step1*/	



    //         $stepsAll[1]['name'] = 'Final Summary'; 
            
    //         if($advancedPayRequestData->request_status == 1  && $advancedPayRequestData->approved_reject_status == 1 && $advancedPayRequestData->status==2)
    //         {
    //             $completedStep++;
    //             $stepsAll[1]['stage'] = 'active'; 
    //             $OnboardingProgress = 'Final Summary';
    //             $stepsAll[1]['Tab'] = 'active'; 
    //         }
    //         else 
    //         {
    //             //$completedStep++;
    //             $OnboardingProgress = 'Final Summary';
    //             $stepsAll[1]['stage'] = 'pending'; 
    //             $stepsAll[1]['Tab'] = 'disabled-tab';  

    //         }
    //         $stepsAll[1]['slagURL'] = 'tab2'; 
    //         $stepsAll[1]['onclick'] = 'tab2Panel();'; 
    //         $OnboardingProgress = 'Final Summary';


		    
			
	// 		$totalStep = 2;
	// 		$p = $completedStep/$totalStep;
	// 		$percentange = round($p*100);

	// 		//return $percentange;
			
			
	// 		return view("AdvancedPay/summaryTabWithFullViewAjax",compact('advancedPayRequestData','stepsAll','percentange','OnboardingProgress'));
	// }

    // public function approvedSummaryTabData(Request $request)
    // {
    //     $empid = $request->empid;
    //     $rowid = $request->rowid;

    //     $advancedPayRequestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();
    //     return view("AdvancedPay/secondTabRequestInfo",compact('advancedPayRequestData'));             
    // }

    // public function finalSummaryTabData(Request $request)
    // {
    //     $empid = $request->empid;
    //     $rowid = $request->rowid;

    //     $advancedPayRequestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();
    //     return view("AdvancedPay/thirdTabRequestinfo",compact('advancedPayRequestData'));             
    // }

    // public function recoveryRequestData(Request $request)
    // {
    //      $empid = $request->empid;
    //      $rowid = $request->rowid;

    //      $advancedPayRequestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();
    //      return view("AdvancedPay/recoveryRequestContent",compact('advancedPayRequestData')); 
    // }



    // public function recoveryRequestUpdatePostSubmit(Request $request)
    // {
    //     $validator = Validator::make($request->all(), 
    //     [			
    //         'recoveryamt' => 'required|numeric|integer|lte:totalRemaining|gte:1',
    //         'amtRecoverydate' => 'required|date',
    //          //'teamleaders' => 'required', 
    //     ],
    //     [
    //         'recoveryamt.required'=> 'Please Fill Amount',
    //         'recoveryamt.numeric'=> 'Amount must be in Number',
    //         'amtRecoverydate.required'=> 'Please Select recovery date',
    //         'recoveryamt.lte'=> 'The Recovery Amount must be less than or equal to Total Remaining Amount',
    //         'recoveryamt.gte'=> 'The Recovery Amount must be greater than or equal 1',
    //         'recoveryamt.integer'=> 'The Recovery Amount must be an Integer',
                
    //     ]);

    //     if(($validator->fails()))
    //     {
    //         return response()->json(['error'=>$validator->errors()]);
    //     }
    //     else
    //     {
    //         //return $request->all();
    //         $usersessionId=$request->session()->get('EmployeeId');
    //         //$advancedPayRequest = AdvancedPayRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

            
    //         $recoveryAmtRequest = new RecoveryAmt();
    //         $recoveryAmtRequest->emp_id = $request->empid;
    //         $recoveryAmtRequest->recovery_amt = $request->recoveryamt;
    //         $recoveryAmtRequest->recovery_at = $request->amtRecoverydate;
    //         $recoveryAmtRequest->recovery_by = $usersessionId; 
    //         $recoveryAmtRequest->recovery_comment = $request->recoveryComments;               
    //         $recoveryAmtRequest->save();


    //      $advancedPayLogs = new AdvancedPayLogs();
    //      $advancedPayLogs->emp_id = $request->empid;
    //      $advancedPayLogs->event_at = date('Y-m-d H:i:s');;
    //      $advancedPayLogs->event_by = $usersessionId;
    //      $advancedPayLogs->event = 4;
    //      $advancedPayLogs->recovered_amt = $request->recoveryamt;            
    //      $advancedPayLogs->save();


    //         return response()->json(['success'=>'Recovered Successfully.']);
    //     }
    // }

    // public static function getRequestedAmt($empid)
    // {
    //      $requestedAmt = AdvancedPayRequest::where('emp_id',$empid)->where('approved_reject_status',1)->orderBy("id","DESC")->first();

    //      if($requestedAmt)
    //      {
    //          return $requestedAmt->requested_advanced_amt;
    //      }
    //      else
    //      {
    //          return 0;
    //      }
    // }

    // public static function getApprovedAmt($empid)
    // {
    //      $approvedAmt = AdvancedPayRequest::where('emp_id',$empid)->where('approved_reject_status',1)->orderBy("id","DESC")->first();

    //      //return $approvedAmt;

    //      if($approvedAmt)
    //      {
    //          return $approvedAmt->approved_advanced_amt;
    //      }
    //      else
    //      {
    //          return 0;
    //      }
    // }
    

    


    // public static function getSumRequestedAmt($empid)
    // {
    //      $requestedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('requested_advanced_amt');

    //      if($requestedAmt)
    //      {
    //          return $requestedAmt;
    //      }
    //      else
    //      {
    //          return 0;
    //      }
    // }

    // public static function getSumApprovedAmt($empid)
    // {
    //      $approvedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('approved_advanced_amt');

    //      if($approvedAmt)
    //      {
    //          return $approvedAmt;
    //      }
    //      else
    //      {
    //          return 0;
    //      }
    // }

    // public static function getSumRecoveredAmt($empid)
    // {
    //      $recoveredAmt = RecoveryAmt::where('emp_id',$empid)->sum('recovery_amt');

    //      if($recoveredAmt)
    //      {
    //          return $recoveredAmt;
    //      }
    //      else
    //      {
    //          return 0;
    //      }
    // }


    // public static function getBalancedAmt($empid)
    // {
    //     $requestedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('requested_advanced_amt');
    //     $approvedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('approved_advanced_amt');
    //     $recoveredAmt = RecoveryAmt::where('emp_id',$empid)->sum('recovery_amt');
    //     $balancedAmt = $approvedAmt - $recoveredAmt;
    //     return $balancedAmt;
    // }

       
    // public function advancedPayLogsTabData(Request $request)
    // {
    //     $empid = $request->empid;
    //     $rowid = $request->rowid;
    //     //return 'Emp_id: '.$empid.' Row_id: '.$rowid;
    //     $advancedPayRequestData = AdvancedPayLogs::where('emp_id',$empid)->orderBy('id','desc')->get();
    //     return view("AdvancedPay/advancedPayLogsDetails",compact('advancedPayRequestData')); 
        
    // }

    public static function getEmpName($empid)
    {
        $empData = Employee_details::where('emp_id', $empid)->orderBy('id','desc')->first();

        if($empData)
        {
            return $empData->emp_name;
        }
        else
        {
            return "--";
        }
    }

    public static function getDesignation($empid)
    {
        $empInfo = Employee_attribute::where('emp_id',$empid)->where('attribute_code','DESIGN')->orderBy('id', 'desc')->first();

        if($empInfo)
        {
            return $empInfo->attribute_values;
        }
        else
        {
            return '--';
        }
    }

    public static function getTL($empid)
    {
        $empInfo = Employee_details::where('emp_id',$empid)->orderBy('id', 'desc')->first();

        if($empInfo)
        {
            //$tL_details = Employee_details::where("job_role","Team Leader")->where("id",$empInfo->tl_id)->orderBy("id","ASC")->first();
            $tL_details = Employee_details::where("id",$empInfo->tl_id)->orderBy("id","ASC")->first();


            if($tL_details)
            {
                return $tL_details->emp_name;
            }
            else
            {
                return '--';
            }
        }
        else
        {
            return '--';
        }
    }


    public static function getWorkLocation($empid)
    {
        $empInfo = Employee_attribute::where('emp_id',$empid)->where('attribute_code','work_location')->orderBy('id', 'desc')->first();

        if($empInfo)
        {
            return $empInfo->attribute_values;
        }
        else
        {
            return '--';
        }
    }

    public function setPageLimitProcess(Request $request)
	{
		$offset = $request->offset;
		$request->session()->put('appAttendanceRequest_page_limit',$offset);
	}
    public static function getlocalMobileNo($empid,$attributecode)
    {
        $attrval = Employee_attribute::where('emp_id',$empid)->where("attribute_code",$attributecode)->first();
        if($attrval!=''){
            $data=substr ($attrval->attribute_values, -9);
            $finaldata="+971 ". $data;
            return $finaldata;
        }
        else{
            return "";
        }
    }


    public static function getUserName($id)
    {
        $data = Employee::where('id',$id)->orderBy("id","DESC")->first();
        //print_r($data);
        if($data != '')
        {
            return $data->fullname;
        }
        else
        {
            return '';
        }
    }


    // public function exportAdvancedPayReport(Request $request)
	// {
    //     //return $request->all();
    //     $parameters = $request->input(); 
    //     $selectedId = $parameters['selectedIds'];
        
            
    //     $filename = 'advanced_pay_report_'.date("d-m-Y").'.xlsx';
    //     $spreadsheet = new Spreadsheet(); 
    //     $sheet = $spreadsheet->getActiveSheet();
    //     $sheet->mergeCells('A1:L1');
    //     $sheet->setCellValue('A1', 'Advanced Pay List - '.date("d-m-Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
    //     $indexCounter = 2;
    //     $sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //     $sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //     $sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //     $sheet->setCellValue('D'.$indexCounter, strtoupper('Team Leader'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //     $sheet->setCellValue('E'.$indexCounter, strtoupper('Designation'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //     $sheet->setCellValue('F'.$indexCounter, strtoupper('Department'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //     $sheet->setCellValue('G'.$indexCounter, strtoupper('Current Requested Amount'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //     $sheet->setCellValue('H'.$indexCounter, strtoupper('Current Approved Amount'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //     $sheet->setCellValue('I'.$indexCounter, strtoupper('Total Requested Amount'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //     $sheet->setCellValue('J'.$indexCounter, strtoupper('Total Approved Amount'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //     $sheet->setCellValue('K'.$indexCounter, strtoupper('Total Recovered Amount'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //     $sheet->setCellValue('L'.$indexCounter, strtoupper('Total Remaining Amount'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            

    //     $sn = 1;
    //     foreach ($selectedId as $sid) 
    //     {
    //         //echo $sid;
    //         $misData = AdvancedPayRequest::where("id",$sid)->first();

    //         //$empName = $this->getEmployeeName($misData->emp_id);
    //         $teamLeader = $this->getTL($misData->emp_id);
    //         $designation = $this->getDesignation($misData->emp_id);
    //         $dept = $this->getDepartment($misData->emp_id);
    //         $empname = $this->getEmpName($misData->emp_id);
    //         $currentRequested = $this->getRequestedAmt($misData->emp_id);
    //         $currentApproved = $this->getApprovedAmt($misData->emp_id);
    //         $sumRequested = $this->getSumRequestedAmt($misData->emp_id);
    //         $sumApproved = $this->getSumApprovedAmt($misData->emp_id);
    //         $sumRecovered = $this->getSumRecoveredAmt($misData->emp_id);
    //         $balancedAmt = $this->getBalancedAmt($misData->emp_id);

            
            

            



    //         $indexCounter++; 
            
            
            
    //         $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //         $sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //         $sheet->setCellValue('C'.$indexCounter, $empname)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //         $sheet->setCellValue('D'.$indexCounter, $teamLeader)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //         $sheet->setCellValue('E'.$indexCounter, $designation)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //         $sheet->setCellValue('F'.$indexCounter, $dept)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //         $sheet->setCellValue('G'.$indexCounter, $currentRequested)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //         $sheet->setCellValue('H'.$indexCounter, $currentApproved)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //         $sheet->setCellValue('I'.$indexCounter, $sumRequested)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //         $sheet->setCellValue('J'.$indexCounter, $sumApproved)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //         $sheet->setCellValue('K'.$indexCounter, $sumRecovered)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
    //         $sheet->setCellValue('L'.$indexCounter, $balancedAmt)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');



            
            
            
    //         $sn++;
            
    //     }
            
            
    //     for($col = 'A'; $col !== 'L'; $col++) {
    //         $sheet->getColumnDimension($col)->setAutoSize(true);
    //     }
            
    //     $spreadsheet->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
                
    //     for($index=1;$index<=$indexCounter;$index++)
    //     {
    //             foreach (range('A','L') as $col) {
    //                 $spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
    //             }
    //     }
    //     $writer = new Xlsx($spreadsheet);
    //     $writer->save(public_path('uploads/exportAdvancedPay/'.$filename));	
    //     echo $filename;
    //     exit;
	// }



    // public function changeStatusActionProcess(Request $request)
	// {
	// 	$rowid = $request->rowid;
	// 	$actionid = $request->action;
    //     $userid=$request->session()->get('EmployeeId');


    //     if($userid!='')
	// 	{
    //         $failedmsg='';
    //         $advancedPayRequest = AdvancedPayRequest::where('id',$rowid)->orderBy('id', 'desc')->first();

    //         if($advancedPayRequest->approved_reject_status==1)
    //         {
    //             $successmsg="This Advanced Payment Request Already Approved.";
    //             return view("AdvancedPay/email_process",compact('successmsg','failedmsg'));
    //         }
    //         elseif($advancedPayRequest->approved_reject_status==2)
    //         {
    //             $successmsg="This Advanced Payment Request Already Rejected.";
    //             return view("AdvancedPay/email_process",compact('successmsg','failedmsg'));
    //         }
    //         else
    //         {
    //             if($rowid !='' && $actionid !='')
    //             {
    //                 if($actionid==1)
    //                 {
    //                     $usersessionId=$request->session()->get('EmployeeId');
    //                     $advancedPayRequest = AdvancedPayRequest::where('id',$rowid)->orderBy('id', 'desc')->first();
        
                        
    //                     $advancedPayRequest->approved_advanced_amt = $advancedPayRequest->requested_advanced_amt;
    //                     $advancedPayRequest->approved_reject_at = date('Y-m-d H:i:s');
    //                     //$advancedPayRequest->amt_release_date = $request->amtReleasedate;
    //                     $advancedPayRequest->approved_reject_by = $usersessionId; 
    //                     $advancedPayRequest->approved_reject_status = 1;
    //                     //$advancedPayRequest->approved_reject_comments = $request->requestUpdatedComments;
    //                     $advancedPayRequest->status = 2;
    //                     $advancedPayRequest->save();
        
    //                     $advancedPayLogs = new AdvancedPayLogs();
    //                     $advancedPayLogs->emp_id = $advancedPayRequest->empid;
    //                     $advancedPayLogs->event_at = date('Y-m-d H:i:s');;
    //                     $advancedPayLogs->event_by = $usersessionId;
    //                     $advancedPayLogs->event = 2;
    //                     $advancedPayLogs->approved_amt = $advancedPayRequest->requested_advanced_amt;            
    //                     $advancedPayLogs->save();
        
    //                     $successmsg="Advanced Pay Request Approved Successfully.";
    //                     return view("AdvancedPay/email_process",compact('successmsg','failedmsg'));
        
    //                 }
        
    //                 if($actionid==2)
    //                 {
    //                     $usersessionId=$request->session()->get('EmployeeId');
    //                     $advancedPayRequest = AdvancedPayRequest::where('id',$rowid)->orderBy('id', 'desc')->first();
        
    //                     $advancedPayRequest->approved_reject_at = date('Y-m-d H:i:s');
    //                     $advancedPayRequest->approved_reject_by = $usersessionId; 
    //                     $advancedPayRequest->approved_reject_status = 2;
    //                     //$advancedPayRequest->approved_reject_comments = $request->requestUpdatedComments;
    //                     $advancedPayRequest->status = 2;
    //                     $advancedPayRequest->save();
        
        
    //                     $advancedPayLogs = new AdvancedPayLogs();
    //                     $advancedPayLogs->emp_id = $advancedPayRequest->empid;
    //                     $advancedPayLogs->event_at = date('Y-m-d H:i:s');;
    //                     $advancedPayLogs->event_by = $usersessionId;
    //                     $advancedPayLogs->event = 3;            
    //                     $advancedPayLogs->save();
    
    //                     $successmsg="Advanced Pay Request Rejected.";
    //                     return view("AdvancedPay/email_process",compact('successmsg','failedmsg'));
    //                 }
    //             }
    //             else
    //             {
    //                 return response()->json(['success'=>'Id not Found.']);
    //             }
                
               
    
    //         }
    //     }
    //     else
    //     {
    //         $successmsg='';
	// 		$failedmsg="To Approved/Disapproved request, Please firstly login in Portal and then again click on Approved/Disapproved through Email.";
	// 		return view("AdvancedPay/email_process",compact('failedmsg','successmsg'));
    //     }


       










		
            
    // }





    public function markAttendanceApprovedProcess(Request $request)
	{
        //return $request->all();
        $parameters = $request->input(); 
        $selectedId = $parameters['selectedIds'];

        // echo "<pre>";
        // print_r($selectedId);

        // exit;
        
            
        

        $sn = 1;
        foreach ($selectedId as $sid) 
        {
            //echo $sid;
            $misData = EmpAttendance::where("id",$sid)->first();

            if($misData->modified_attribute_value==NULL || $misData->modified_attribute_value=='')
            {
                $misData->approve_status = 2;           
                $misData->save();
            }
            else
            {
                $misData->attribute_value = $misData->modified_attribute_value;
                $misData->approve_status = 2;           
                $misData->save();
            }

            

            //$empName = $this->getEmployeeName($misData->emp_id);
            // $teamLeader = $this->getTL($misData->emp_id);
            // $designation = $this->getDesignation($misData->emp_id);
            // $dept = $this->getDepartment($misData->emp_id);
            // $empname = $this->getEmpName($misData->emp_id);
            // $currentRequested = $this->getRequestedAmt($misData->emp_id);
            // $currentApproved = $this->getApprovedAmt($misData->emp_id);
            // $sumRequested = $this->getSumRequestedAmt($misData->emp_id);
            // $sumApproved = $this->getSumApprovedAmt($misData->emp_id);
            // $sumRecovered = $this->getSumRecoveredAmt($misData->emp_id);
            // $balancedAmt = $this->getBalancedAmt($misData->emp_id);

            
            


            
            
            
            $sn++;
            
        }
        
            
            
        
        return response()->json(['success'=>'Marked Approved Successfully.']);
	}

    public function updateAttendanceDatathroughApp(Request $request)
    {
        //echo "<pre>";
        //print_r($request->empAttendanceData);
        $modifiedData = explode("-",$request->empAttendanceData);
        //exit;

        $attendanceModifiedRequestData = EmpAttendance::where('id',$modifiedData[0])->orderBy('id', 'desc')->first();
        $attendanceModifiedRequestData->modified_attribute_value = $modifiedData[1];           
        $attendanceModifiedRequestData->save();

        return response()->json(['success'=>'Updated Successfully.']);
    }

    public static function getAttendanceValue($attendance)
    {
        
        $attendanceDetailsData = Attendance::where('name',$attendance)->orderBy('id', 'desc')->first();

        if($attendanceDetailsData)
        {
            return $attendanceDetailsData->title;
        }
        else
        {
            return "--";
        }
    }


    public function setAttendanceMarkApprovedData(Request $request)
    {
        $rowid=$request->rowid;

        $misData = EmpAttendance::where("id",$rowid)->first();

        if($misData->modified_attribute_value==NULL || $misData->modified_attribute_value=='')
        {
            $misData->approve_status = 2;           
            $misData->save();
        }
        else
        {
            $misData->attribute_value = $misData->modified_attribute_value;
            $misData->approve_status = 2;           
            $misData->save();
        }

        return response()->json(['success'=>'Attendance Marked Approved Successfully.']);
    }




    public function getDetailsbyidDataOLD(Request $request)
    {
        $rowid=$request->rowid;

        $misData = EmpAttendance::where("id",$rowid)->first();

        if($misData->modified_attribute_value==NULL || $misData->modified_attribute_value=='')
        {
            $misData->approve_status = 2;           
            $misData->save();
        }
        else
        {
            $misData->attribute_value = $misData->modified_attribute_value;
            $misData->approve_status = 2;           
            $misData->save();
        }

        return response()->json(['success'=>'Attendance Marked Approved Successfully.']);
    }


    public static function getDetailsbyidData(Request $request)
    {
        $rowid=$request->rowid;
        $loggedinUserid=$request->session()->get('EmployeeId');
        $misData = EmpAttendance::where("id",$rowid)->first();

        if($misData)
        {
            $empDetails = $misData;
        }
        else
        {
            return response()->json(['success'=>'Not Found.']);
        }
        
        //$empDetails = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();

        return view("Attendance/getDetails",compact('empDetails'));
    }

}