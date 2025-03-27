<?php
namespace App\Http\Controllers\AdvancedPay;

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

use App\Models\ChangeDepartment\ChangeDepartmentRequestLog;

use App\User;
use App\Models\Entry\Employee;
class AdvancedPayController extends Controller
{
	public  function Index(Request $request)
	{
        //$empDetails = Employee_details::orderBy('id', 'desc')->get(); 
        
        $empDetailsIndex = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();

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



        return view("AdvancedPay/index",compact('empDetailsIndex','departmentLists','designationLists','tL_details'));
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

    public function allAdvancedPayListing(Request $request)
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
        
        
        if(!empty($request->session()->get('advancedPayRequest_page_limit')))
        {
            $paginationValue = $request->session()->get('advancedPayRequest_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('advancedpay_requests_emp_name')) && $request->session()->get('advancedpay_requests_emp_name') != 'All')
        {
            $fname = $request->session()->get('advancedpay_requests_emp_name');
            if($fname==',')
            {               
            }
            else
            {
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
            }


           
        }


        if(!empty($request->session()->get('advancedpay_requests_emp_id')) && $request->session()->get('advancedpay_requests_emp_id') != 'All')
        {
            $empId = $request->session()->get('advancedpay_requests_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('advancedpay_requests_dept')) && $request->session()->get('advancedpay_requests_dept') != 'All')
        {
            $deptid = $request->session()->get('advancedpay_requests_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('advancedpay_requests_designation')) && $request->session()->get('advancedpay_requests_designation') != 'All')
        {
            $desigid = $request->session()->get('advancedpay_requests_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('advancedpay_requests_tl')) && $request->session()->get('advancedpay_requests_tl') != 'All')
        {
            $tlid = $request->session()->get('advancedpay_requests_tl');
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

       

        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
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
                
                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id','desc')
                ->get()->count();
                
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();


                $empDetails = Employee_details::whereRaw($whereraw)->where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                
                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id','desc')
                ->get()->count();
            }
        }
        else
        {
            if($empData==1) // all
            {
                $requestDetails = AdvancedPayRequest::orderBy('id', 'desc')
                //->toSql();	 
                //dd($documentCollectiondetails);						
                ->paginate($paginationValue);	
                
                $reportsCount = AdvancedPayRequest::orderBy('id','desc')
                ->get()->count();
            }
            else // specific dept
            {
                
                 $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
                
                
                $empDetails = Employee_details::where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }


                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id', 'desc')
                //->toSql();	 
                //dd($documentCollectiondetails);						
                ->paginate($paginationValue);
                
                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id','desc')
                ->get()->count();
            }
        }

        //return $requestDetails;
        
        $requestDetails->setPath(config('app.url/listingAll'));
        return view("AdvancedPay/listingAll",compact('requestDetails','paginationValue','reportsCount'));
    }


    public static function addrequestPopData(Request $request)
    {
        $empDetails = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();

        return view("AdvancedPay/addRequest",compact('empDetails'));

    }

    public static function getEmpContentData(Request $request)
    {
        $empid=$request->empid;
        $empDetails='';
        $empAdvancedDetails = AdvancedPayRequest::where('emp_id',$empid)->where('approved_reject_status',1)->orderBy('id', 'desc')->get();

        if($empAdvancedDetails)
        {
            $empDetails = $empAdvancedDetails;
        }

        return view("AdvancedPay/addRequestContent",compact('empDetails'));

    }


    

    public function advancedPayRequestPostSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
			'advancedamt' => 'required|numeric',
            'addRequestEmp' => 'required',
           // 'teamleaders' => 'required', 
        ],
		[
			'advancedamt.required'=> 'Please Fill Amount',
            'advancedamt.numeric'=> 'Amount must be a number.',
		 	'addRequestEmp.required'=> 'Please Select Employee from List',
			//'teamleaders.required'=> 'Please Select Team Leader from List',
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            //return $request->all();
            $usersessionId=$request->session()->get('EmployeeId');
            //$transferRequestData = AdvancedPayRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

            $advancedPayRequest = new AdvancedPayRequest();
            $advancedPayRequest->emp_id = $request->addRequestEmp;
            $advancedPayRequest->requested_advanced_amt = $request->advancedamt;
            $advancedPayRequest->requested_at = date('Y-m-d H:i:s');
            $advancedPayRequest->requested_by = $usersessionId; 
            $advancedPayRequest->request_status = 1;
            $advancedPayRequest->request_comments = $request->requestComments;
            $advancedPayRequest->status = 1;
            $advancedPayRequest->save();


            

            // $transferRequestLogs = new ChangeDepartmentRequestLog();
			// $transferRequestLogs->emp_id = $request->empid;
            // $transferRequestLogs->event_at = date('Y-m-d H:i:s');;
            // $transferRequestLogs->event_by = $usersessionId;
            // $transferRequestLogs->event = 2;
            // $transferRequestLogs->save();


            return response()->json(['success'=>'Advanced Pay Request Added Successfully.']);
        }
    }

    public function advancedPayRequestsListing(Request $request)
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
        
        if(!empty($request->session()->get('advancedPayRequest_page_limit')))
        {
            $paginationValue = $request->session()->get('advancedPayRequest_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	



        if(!empty($request->session()->get('advancedpay_requests_emp_name')) && $request->session()->get('advancedpay_requests_emp_name') != 'All')
        {
            $fname = $request->session()->get('advancedpay_requests_emp_name');
            if($fname==',')
            {               
            }
            else
            {
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
            }
        }


        if(!empty($request->session()->get('advancedpay_requests_emp_id')) && $request->session()->get('advancedpay_requests_emp_id') != 'All')
        {
            $empId = $request->session()->get('advancedpay_requests_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }

        if(!empty($request->session()->get('advancedpay_requests_dept')) && $request->session()->get('advancedpay_requests_dept') != 'All')
        {
            $deptid = $request->session()->get('advancedpay_requests_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('advancedpay_requests_designation')) && $request->session()->get('advancedpay_requests_designation') != 'All')
        {
            $desigid = $request->session()->get('advancedpay_requests_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('advancedpay_requests_tl')) && $request->session()->get('advancedpay_requests_tl') != 'All')
        {
            $tlid = $request->session()->get('advancedpay_requests_tl');
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

        

        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
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
                
                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')
                ->get()->count();
                
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();

                $empDetails = Employee_details::whereRaw($whereraw)->where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')
                ->get()->count();
            }
        }
        else
        {
            if($empData==1) // all
            {
                $requestDetails = AdvancedPayRequest::where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')						
                ->paginate($paginationValue);	
                
                $reportsCount = AdvancedPayRequest::where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')
                ->get()->count();
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
                
                $empDetails = Employee_details::where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }

                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')						
                ->paginate($paginationValue);
                
                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')
                ->get()->count();
            }
        }
        
        $requestDetails->setPath(config('app.url/listingAdvancedPayRequests'));
        return view("AdvancedPay/listingAdvancedPayRequests",compact('requestDetails','paginationValue','reportsCount'));
    }


    public function getAdvancedPayRequestContent(Request $request)
    {
        $empid=$request->empid;
        $rowid=$request->rowid;

        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();

        $requestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();

        return view("AdvancedPay/advancedPayRequestContent",compact('requestData','departmentLists','tL_details'));
    }

    public function advancedPayRequestSubmitApproved(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
			'approvedamt' => 'required|numeric',
            //'addRequestEmp' => 'required',
           // 'teamleaders' => 'required', 
        ],
		[
			'approvedamt.required'=> 'Please Fill Amount',
            'approvedamt.numeric'=> 'Amount must be a number.',
		 	//'addRequestEmp.required'=> 'Please Select Employee from List',
			//'teamleaders.required'=> 'Please Select Team Leader from List',
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            //return $request->all();
            $usersessionId=$request->session()->get('EmployeeId');
            $advancedPayRequest = AdvancedPayRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

            
            //$advancedPayRequest->emp_id = $request->addRequestEmp;
            $advancedPayRequest->approved_advanced_amt = $request->approvedamt;
            $advancedPayRequest->approved_reject_at = date('Y-m-d H:i:s');
            $advancedPayRequest->approved_reject_by = $usersessionId; 
            $advancedPayRequest->approved_reject_status = 1;
            $advancedPayRequest->approved_reject_comments = $request->requestUpdatedComments;
            $advancedPayRequest->status = 2;

            
            $advancedPayRequest->save();


            

            // $transferRequestLogs = new ChangeDepartmentRequestLog();
			// $transferRequestLogs->emp_id = $request->empid;
            // $transferRequestLogs->event_at = date('Y-m-d H:i:s');;
            // $transferRequestLogs->event_by = $usersessionId;
            // $transferRequestLogs->event = 2;
            // $transferRequestLogs->save();


            return response()->json(['success'=>'Advanced Pay Request Approved.']);
        }
    }


    public function advancedPayRequestSubmitRejected(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
			'requestUpdatedComments' => 'required',
            //'addRequestEmp' => 'required',
           // 'teamleaders' => 'required', 
        ],
		[
			'requestUpdatedComments.required'=> 'Please Write Some Comments',
		 	//'addRequestEmp.required'=> 'Please Select Employee from List',
			//'teamleaders.required'=> 'Please Select Team Leader from List',
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            //return $request->all();
            $usersessionId=$request->session()->get('EmployeeId');
            $advancedPayRequest = AdvancedPayRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

            
            //$advancedPayRequest->emp_id = $request->addRequestEmp;
            $advancedPayRequest->approved_advanced_amt = $request->approvedamt;
            $advancedPayRequest->approved_reject_at = date('Y-m-d H:i:s');
            $advancedPayRequest->approved_reject_by = $usersessionId; 
            $advancedPayRequest->approved_reject_status = 2;
            $advancedPayRequest->approved_reject_comments = $request->requestUpdatedComments;
            $advancedPayRequest->status = 2;

            
            $advancedPayRequest->save();


            

            // $transferRequestLogs = new ChangeDepartmentRequestLog();
			// $transferRequestLogs->emp_id = $request->empid;
            // $transferRequestLogs->event_at = date('Y-m-d H:i:s');;
            // $transferRequestLogs->event_by = $usersessionId;
            // $transferRequestLogs->event = 2;
            // $transferRequestLogs->save();


            return response()->json(['success'=>'Advanced Pay Request Rejected.']);
        }
    }
    

    public function advancedPayFinalListing(Request $request)
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
        
        if(!empty($request->session()->get('advancedPayRequest_page_limit')))
        {
            $paginationValue = $request->session()->get('advancedPayRequest_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	



        if(!empty($request->session()->get('advancedpay_requests_emp_name')) && $request->session()->get('advancedpay_requests_emp_name') != 'All')
        {
            $fname = $request->session()->get('advancedpay_requests_emp_name');
            if($fname==',')
            {               
            }
            else
            {
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
            }
        }


        if(!empty($request->session()->get('advancedpay_requests_emp_id')) && $request->session()->get('advancedpay_requests_emp_id') != 'All')
        {
            $empId = $request->session()->get('advancedpay_requests_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }

        if(!empty($request->session()->get('advancedpay_requests_dept')) && $request->session()->get('advancedpay_requests_dept') != 'All')
        {
            $deptid = $request->session()->get('advancedpay_requests_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('advancedpay_requests_designation')) && $request->session()->get('advancedpay_requests_designation') != 'All')
        {
            $desigid = $request->session()->get('advancedpay_requests_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('advancedpay_requests_tl')) && $request->session()->get('advancedpay_requests_tl') != 'All')
        {
            $tlid = $request->session()->get('advancedpay_requests_tl');
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

        



        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
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
                
                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')
                ->get()->count();
                
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();

                $empDetails = Employee_details::whereRaw($whereraw)->where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')
                ->get()->count();
            }
        }
        else
        {
            if($empData==1) // all
            {
                
               


                $requestPayDetails = AdvancedPayRequest::where('request_status',1)->where('approved_reject_status',1)->groupBy('emp_id')
                ->get();
                
                $newResult=array();
                foreach($requestPayDetails as $value)
                {
                    $newResult[]=$value->id;
                }
                
                
                $requestDetails = AdvancedPayRequest::whereIn('id',$newResult)->orderBy('id', 'desc')						
                ->paginate($paginationValue);	
                
                $reportsCount = AdvancedPayRequest::whereIn('id',$newResult)->orderBy('id', 'desc')
                ->get()->count();


                
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
                
                $empDetails = Employee_details::where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }

                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')						
                ->paginate($paginationValue);
                
                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')
                ->get()->count();
            }
        }





        
        $requestDetails->setPath(config('app.url/listingAdvancedPayFinalRequests'));
        return view("AdvancedPay/listingAdvancedPayFinalRequests",compact('requestDetails','paginationValue','reportsCount'));
    }
    

    public function searchAdvancedPayRequestFilter(Request $request)
	{
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}

            $newDepartment='';
			if($request->input('newDepartment')!=''){
			 
			 $newDepartment=implode(",", $request->input('newDepartment'));
			}


			$teamlaed='';
			if($request->input('teamlaed')!=''){
			 
			 $teamlaed=implode(",", $request->input('teamlaed'));
			}
			$dateto = $request->input('dateto');
			$datefrom = $request->input('datefrom');
			$name='';
			if($request->input('emp_name')!=''){
			 
			 $name=implode(",", $request->input('emp_name'));
			}
			//$name = $request->input('emp_name');
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
			if($request->input('rangeid')!=''){
			 
			 $rangeid=implode(",", $request->input('rangeid'));
			}

			$request->session()->put('advancedpay_requests_emp_name',$name);
            $request->session()->put('advancedpay_requests_emp_id',$empId);
            $request->session()->put('advancedpay_requests_dept',$department);
            $request->session()->put('transfer_requests_new_dept',$newDepartment);
            $request->session()->put('advancedpay_requests_designation',$design);
            $request->session()->put('advancedpay_requests_tl',$teamlaed);




            $request->session()->put('emp_leaves_fromdate',$datefrom);
            $request->session()->put('emp_leaves_todate',$dateto);


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

    public function resetAdvancedPayRequestFilter(Request $request)
    {
        $request->session()->put('advancedpay_requests_emp_name','');
        $request->session()->put('advancedpay_requests_emp_id','');
        $request->session()->put('advancedpay_requests_dept','');
        $request->session()->put('transfer_requests_new_dept','');
        $request->session()->put('advancedpay_requests_designation','');
        $request->session()->put('advancedpay_requests_tl','');



        


        $request->session()->put('emp_leaves_fromdate','');
		$request->session()->put('emp_leaves_todate','');
        
        
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

    public static function getRequestStatus($empid,$rowid)
    {
        $requestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();

        if($requestData)
        {
            if($requestData->approved_reject_status==1)
            {
                return "Request Approved";
            }
            if($requestData->approved_reject_status==2)
            {
                return "Request Rejected";
            }
            if($requestData->request_status==1 && $requestData->approved_reject_status==NULL)
            {
                return "Pending for Approval/Reject";
            }
        }
        else
        {
            return '--';
        }
    }

    public function summaryTabWithFullViewAjax(Request $request)
	   {
		    $empid = $request->empid;
			$rowid = $request->rowid;

            $advancedPayRequestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();

			$completedStep = 0;
			$OnboardingProgress = '';
			$stepsAll = array();
			/*Step1*/
		    



            $stepsAll[0]['name'] = 'Approved/Reject'; 
            if($advancedPayRequestData->request_status == 1  && $advancedPayRequestData->approved_reject_status == 0)
            {
                $stepsAll[0]['stage'] = 'inprogress';
                $stepsAll[0]['Tab'] = 'active'; 
            }
            else
            {
                $completedStep++;
                $stepsAll[0]['stage'] = 'active'; 
                $OnboardingProgress = 'Approved/Reject';
                $stepsAll[0]['Tab'] = 'active'; 
            }
            $stepsAll[0]['slagURL'] = 'tab2'; 
            //$stepsAll[0]['tab'] = 'active'; 
            $stepsAll[0]['onclick'] = 'tab2Panel();';            
            $OnboardingProgress = 'Approved/Reject';
            /*Step1*/	



            $stepsAll[1]['name'] = 'Final Summary'; 
            
            if($advancedPayRequestData->request_status == 1  && $advancedPayRequestData->approved_reject_status == 1 && $advancedPayRequestData->status==2)
            {
                $completedStep++;
                $stepsAll[1]['stage'] = 'active'; 
                $OnboardingProgress = 'Final Summary';
                $stepsAll[1]['Tab'] = 'active'; 
            }
            else 
            {
                //$completedStep++;
                $OnboardingProgress = 'Final Summary';
                $stepsAll[1]['stage'] = 'pending'; 
                $stepsAll[1]['Tab'] = 'disabled-tab';  

            }
            $stepsAll[1]['slagURL'] = 'tab2'; 
            $stepsAll[1]['onclick'] = 'tab2Panel();'; 
            $OnboardingProgress = 'Final Summary';


		    
			
			$totalStep = 3;
			$p = $completedStep/$totalStep;
			$percentange = round($p*100);

			//return $percentange;
			
			
			return view("AdvancedPay/summaryTabWithFullViewAjax",compact('advancedPayRequestData','stepsAll','percentange','OnboardingProgress'));
	   }

       public function approvedSummaryTabData(Request $request)
       {
            $empid = $request->empid;
            $rowid = $request->rowid;

            $advancedPayRequestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();
            return view("AdvancedPay/secondTabRequestInfo",compact('advancedPayRequestData'));             
       }

       public function finalSummaryTabData(Request $request)
       {
            $empid = $request->empid;
            $rowid = $request->rowid;

            $advancedPayRequestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();
            return view("AdvancedPay/thirdTabRequestinfo",compact('advancedPayRequestData'));             
       }

       public function recoveryRequestData(Request $request)
       {
            $empid = $request->empid;
            $rowid = $request->rowid;

            $advancedPayRequestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();
            return view("AdvancedPay/recoveryRequestContent",compact('advancedPayRequestData')); 
       }



       public function recoveryRequestUpdatePostSubmit(Request $request)
       {
           $validator = Validator::make($request->all(), 
           [			
               'recoveryamt' => 'required|numeric',
               //'addRequestEmp' => 'required',
              // 'teamleaders' => 'required', 
           ],
           [
               'recoveryamt.required'=> 'Please Fill Amount',
               'recoveryamt.numeric'=> 'Amount must be in Number',
               //'teamleaders.required'=> 'Please Select Team Leader from List',
                   
           ]);
   
           if(($validator->fails()))
           {
               return response()->json(['error'=>$validator->errors()]);
           }
           else
           {
               //return $request->all();
               $usersessionId=$request->session()->get('EmployeeId');
               //$advancedPayRequest = AdvancedPayRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();
   
               


               
               $recoveryAmtRequest = new RecoveryAmt();
               $recoveryAmtRequest->emp_id = $request->empid;
               $recoveryAmtRequest->recovery_amt = $request->recoveryamt;
               $recoveryAmtRequest->recovery_at = date('Y-m-d H:i:s');
               $recoveryAmtRequest->recovery_by = $usersessionId; 
               $recoveryAmtRequest->recovery_comment = $request->recoveryComments;               
               $recoveryAmtRequest->save();
   
   
               
   
               // $transferRequestLogs = new ChangeDepartmentRequestLog();
               // $transferRequestLogs->emp_id = $request->empid;
               // $transferRequestLogs->event_at = date('Y-m-d H:i:s');;
               // $transferRequestLogs->event_by = $usersessionId;
               // $transferRequestLogs->event = 2;
               // $transferRequestLogs->save();
   
   
               return response()->json(['success'=>'Recovered Successfully.']);
           }
       }

       
       public static function getSumRequestedAmt($empid)
       {
            $requestedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('requested_advanced_amt');

            if($requestedAmt)
            {
                return $requestedAmt;
            }
            else
            {
                return 0;
            }
       }

       public static function getSumApprovedAmt($empid)
       {
            $approvedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('approved_advanced_amt');

            if($approvedAmt)
            {
                return $approvedAmt;
            }
            else
            {
                return 0;
            }
       }

       public static function getSumRecoveredAmt($empid)
       {
            $recoveredAmt = RecoveryAmt::where('emp_id',$empid)->sum('recovery_amt');

            if($recoveredAmt)
            {
                return $recoveredAmt;
            }
            else
            {
                return 0;
            }
       }

       

       public static function getBalancedAmt($empid)
       {

        $requestedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('requested_advanced_amt');

        $approvedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('approved_advanced_amt');

        $recoveredAmt = RecoveryAmt::where('emp_id',$empid)->sum('recovery_amt');


        $balancedAmt = $approvedAmt - $recoveredAmt;

        return $balancedAmt;


           
       }

       
       















































































































































































































    
    


  

























    public function requestinProcessListingData(Request $request)
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
        
        if(!empty($request->session()->get('advancedPayRequest_page_limit')))
        {
            $paginationValue = $request->session()->get('advancedPayRequest_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('advancedpay_requests_emp_name')) && $request->session()->get('advancedpay_requests_emp_name') != 'All')
        {
            $fname = $request->session()->get('advancedpay_requests_emp_name');
            if($fname==',')
            {               
            }
            else
            {
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
            }
        }


        if(!empty($request->session()->get('advancedpay_requests_emp_id')) && $request->session()->get('advancedpay_requests_emp_id') != 'All')
        {
            $empId = $request->session()->get('advancedpay_requests_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }


        if(!empty($request->session()->get('advancedpay_requests_dept')) && $request->session()->get('advancedpay_requests_dept') != 'All')
        {
            $deptid = $request->session()->get('advancedpay_requests_dept');
                if($whereraw == '')
            {
                $whereraw = 'old_dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And old_dept_id IN ('.$deptid.')';
            }
        }



        if(!empty($request->session()->get('transfer_requests_new_dept')) && $request->session()->get('transfer_requests_new_dept') != 'All')
        {
            $newdeptid = $request->session()->get('transfer_requests_new_dept');
                if($whereraw == '')
            {
                $whereraw = 'new_dept_id IN ('.$newdeptid.')';
            }
            else
            {
                $whereraw .= ' And new_dept_id IN ('.$newdeptid.')';
            }
        }


        
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        if($whereraw != '')
		{
            if($request->session()->get('advancedpay_requests_dept')!='' || $request->session()->get('transfer_requests_new_dept')!='')
            {
                $empDetails = ChangeDepartmentRequest::whereRaw($whereraw)->orderBy('id', 'desc')
                ->get();               
    
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
            }
            else
            {
                $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
                // ->toSql();	 
                // dd($empDetails);
                ->get();
    
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
            }
            
            if($empData==1) // all
            {
                $requestDetails = ChangeDepartmentRequest::where('request_status',2)->where('approved_reject_status',1)->whereIn('emp_id',$newResult)->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = ChangeDepartmentRequest::where('request_status',2)->where('approved_reject_status',1)->whereIn('emp_id',$newResult)->orderBy('id','desc')
                ->get()->count();
                
            }
            else // specific dept
            {
                $requestDetails = ChangeDepartmentRequest::where('request_status',2)->where('approved_reject_status',1)->whereIn('emp_id',$newResult)->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                $reportsCount = ChangeDepartmentRequest::where('request_status',2)->where('approved_reject_status',1)->whereIn('emp_id',$newResult)->orderBy('id','desc')
                ->get()->count();
            }
        }
        else
        {
            if($empData==1) // all
            {
                $requestDetails = ChangeDepartmentRequest::where('request_status',2)->where('approved_reject_status',1)->orderBy('id', 'desc')						
                ->paginate($paginationValue);	
                
                $reportsCount = ChangeDepartmentRequest::where('request_status',2)->where('approved_reject_status',1)->orderBy('id','desc')
                ->get()->count();
            }
            else // specific dept
            {
                $requestDetails = ChangeDepartmentRequest::where('request_status',2)->where('approved_reject_status',1)->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                
                $reportsCount = ChangeDepartmentRequest::where('request_status',2)->where('approved_reject_status',1)->orderBy('id','desc')
                ->get()->count();
            }
        }

        //return $requestDetails;
        
        $requestDetails->setPath(config('app.url/listinginProcessRequest'));
        return view("ChangeDepartment/listinginProcessRequest",compact('requestDetails','paginationValue','reportsCount'));
    }


    public function confirmRequestTabListingData(Request $request)
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
        
        
        if(!empty($request->session()->get('advancedPayRequest_page_limit')))
        {
            $paginationValue = $request->session()->get('advancedPayRequest_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('advancedpay_requests_emp_name')) && $request->session()->get('advancedpay_requests_emp_name') != 'All')
        {
            $fname = $request->session()->get('advancedpay_requests_emp_name');
            if($fname==',')
            {               
            }
            else
            {
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
            }


           
        }


        if(!empty($request->session()->get('advancedpay_requests_emp_id')) && $request->session()->get('advancedpay_requests_emp_id') != 'All')
        {
            $empId = $request->session()->get('advancedpay_requests_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }


        if(!empty($request->session()->get('advancedpay_requests_dept')) && $request->session()->get('advancedpay_requests_dept') != 'All')
        {
            $deptid = $request->session()->get('advancedpay_requests_dept');
                if($whereraw == '')
            {
                $whereraw = 'old_dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And old_dept_id IN ('.$deptid.')';
            }
        }



        if(!empty($request->session()->get('transfer_requests_new_dept')) && $request->session()->get('transfer_requests_new_dept') != 'All')
        {
            $newdeptid = $request->session()->get('transfer_requests_new_dept');
                if($whereraw == '')
            {
                $whereraw = 'new_dept_id IN ('.$newdeptid.')';
            }
            else
            {
                $whereraw .= ' And new_dept_id IN ('.$newdeptid.')';
            }
        }


        //$whereraw='';
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        if($whereraw != '')
		{
            if($request->session()->get('advancedpay_requests_dept')!='' || $request->session()->get('transfer_requests_new_dept')!='')
            {
                $empDetails = ChangeDepartmentRequest::whereRaw($whereraw)->orderBy('id', 'desc')
                ->get();               
    
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
            }
            else
            {
                $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
                // ->toSql();	 
                // dd($empDetails);
                ->get();
    
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
            }
            if($empData==1) // all
            {
                $requestDetails = ChangeDepartmentRequest::where('request_status',3)->where('transfer_formalities_status',1)->whereIn('emp_id',$newResult)->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = ChangeDepartmentRequest::where('request_status',3)->where('transfer_formalities_status',1)->whereIn('emp_id',$newResult)->orderBy('id','desc')
                ->get()->count();
                
            }
            else // specific dept
            {
                $requestDetails = ChangeDepartmentRequest::where('request_status',3)->where('transfer_formalities_status',1)->whereIn('emp_id',$newResult)->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                $reportsCount = ChangeDepartmentRequest::where('request_status',3)->where('transfer_formalities_status',1)->whereIn('emp_id',$newResult)->orderBy('id','desc')
                ->get()->count();
            }
        }
        else
        {
            if($empData==1) // all
            {
                $requestDetails = ChangeDepartmentRequest::where('request_status',3)->where('transfer_formalities_status',1)->orderBy('id', 'desc')
                //->toSql();	 
                //dd($documentCollectiondetails);						
                ->paginate($paginationValue);	
                
                $reportsCount = ChangeDepartmentRequest::where('request_status',3)->where('transfer_formalities_status',1)->orderBy('id', 'desc')
                ->get()->count();
            }
            else // specific dept
            {
                
                $requestDetails = ChangeDepartmentRequest::where('request_status',3)->where('transfer_formalities_status',1)->orderBy('id', 'desc')						
                ->paginate($paginationValue);
                
                $reportsCount = ChangeDepartmentRequest::where('request_status',3)->where('transfer_formalities_status',1)->orderBy('id', 'desc')
                ->get()->count();
            }
        }
        
        $requestDetails->setPath(config('app.url/listingConfirmedTransferRequest'));
        return view("ChangeDepartment/listingConfirmedTransferRequest",compact('requestDetails','paginationValue','reportsCount'));
    }
    
    



    public function getTransferFormalitiesData(Request $request)
    {
        $empid=$request->empid;
        $rowid=$request->rowid;

        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();



        $transferRequestData = ChangeDepartmentRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();



        $designationdetailsdata = Designation::where("department_id",$transferRequestData->new_dept_id)->get();


        return view("ChangeDepartment/transferFormalitiesContent",compact('transferRequestData','departmentLists','tL_details','designationdetailsdata'));
    }

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


    public function transferRequestApprovedPost(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
			'department' => 'required',
            'designation' => 'required',
           // 'teamleaders' => 'required', 
        ],
		[
			'department.required'=> 'Please Select department from list',
		 	'designation.required'=> 'Please Select New Designation from List',
			//'teamleaders.required'=> 'Please Select Team Leader from List',
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            //return $request->all();
            $usersessionId=$request->session()->get('EmployeeId');
            $transferRequestData = ChangeDepartmentRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

            $transferRequestData->new_dept_id = $request->department;
            $transferRequestData->new_designation = $request->designation;
            $transferRequestData->new_tl = $request->teamleaders;
            $transferRequestData->approved_reject_at = date('Y-m-d H:i:s'); 
            $transferRequestData->approved_reject_by = $usersessionId;
            $transferRequestData->comments = $request->comments;
            $transferRequestData->request_status = 2;  
            $transferRequestData->approved_reject_status = 1;
            $transferRequestData->salary_change = $request->salaryChange;
            $transferRequestData->location_change = $request->locationChange;            
            // $transferRequestData->old_dept_id = $request->old_dept;
            // $transferRequestData->old_tl = $request->old_tl;
            // $transferRequestData->old_designation = $request->old_designation;
            
            $transferRequestData->save();


            

            $transferRequestLogs = new ChangeDepartmentRequestLog();
			$transferRequestLogs->emp_id = $request->empid;
            $transferRequestLogs->event_at = date('Y-m-d H:i:s');;
            $transferRequestLogs->event_by = $usersessionId;
            $transferRequestLogs->event = 2;
            $transferRequestLogs->save();


            return response()->json(['success'=>'Transfer Request Approved.']);
        }
    }

    public function transferRequestRejectPost(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
			'comments' => 'required',
            //'designation' => 'required',
            //'teamleaders' => 'required', 
        ],
		[
			'comments.required'=> 'Please write some to proceed for rejection',
		 	//'designation.required'=> 'Please Select New Designation from List',
			//'teamleaders.required'=> 'Please Select Team Leader from List',
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            //return $request->all();
            $usersessionId=$request->session()->get('EmployeeId');
            $transferRequestData = ChangeDepartmentRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

            $transferRequestData->new_dept_id = $request->department;
            $transferRequestData->new_designation = $request->designation;
            $transferRequestData->new_tl = $request->teamleaders;
            $transferRequestData->approved_reject_at = date('Y-m-d H:i:s'); 
            $transferRequestData->approved_reject_by = $usersessionId;
            $transferRequestData->comments = $request->comments;
            $transferRequestData->request_status = 2;  
            $transferRequestData->approved_reject_status = 2;     
            $transferRequestData->salary_change = $request->salaryChange;
            $transferRequestData->location_change = $request->locationChange;       
            // $transferRequestData->old_dept_id = $request->old_dept;
            // $transferRequestData->old_tl = $request->old_tl;
            // $transferRequestData->old_designation = $request->old_designation;
            $transferRequestData->save();

            $transferRequestLogs = new ChangeDepartmentRequestLog();
			$transferRequestLogs->emp_id = $request->empid;
            $transferRequestLogs->event_at = date('Y-m-d H:i:s');;
            $transferRequestLogs->event_by = $usersessionId;
            $transferRequestLogs->event = 3;
            $transferRequestLogs->save();

            return response()->json(['success'=>'Transfer Request Rejected.']);
        }
    }


    public function submitTransferFormalitiesRequestPost(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
			'customFile' => 'required|mimes:csv,txt,pdf,jpeg,png,jpg',
            'editdepartment' => 'required',
            'editdesignation' => 'required',
           // 'teamleaders' => 'required', 
        ],
		[
			//'customFile' => '',
            'editdepartment.required'=> 'Please Select department from list',
		 	'editdesignation.required'=> 'Please Select New Designation from List',
			//'teamleaders.required'=> 'Please Select Team Leader from List',
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            //return $request->all();

            
            $file = $request->file('customFile');
            $filename = time().'_'.$file->getClientOriginalName();

            if(file_exists(public_path('transferDocs/'.$filename)))
            {
                unlink(public_path('transferDocs/'.$filename));
            }
            
            // File extension
            $extension = $file->getClientOriginalExtension();

            // File upload location
            $location = 'transferDocs';

            // Upload file
            $file->move(public_path('transferDocs/'), $filename);

            // File path
            $filepath = url('transferDocs/'.$filename);


            $usersessionId=$request->session()->get('EmployeeId');
            $transferRequestData = ChangeDepartmentRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

            $transferRequestData->new_dept_id = $request->department;
            $transferRequestData->new_designation = $request->designation;
            if($filename!='')
            {
                $transferRequestData->transfer_doc = $filename;
            }	

            $transferRequestData->new_dept_id = $request->editdepartment;
            $transferRequestData->new_designation = $request->editdesignation;
            $transferRequestData->transfer_formalities_at = date('Y-m-d H:i:s'); 
            $transferRequestData->transfer_formalities_by = $usersessionId;
            $transferRequestData->request_status = 3;  
            $transferRequestData->transfer_formalities_status = 1;
            $transferRequestData->transfer_formalities_comment = $request->editcomments;
            $transferRequestData->final_status = 1;

            $transferRequestData->save();


            

            $transferRequestLogs = new ChangeDepartmentRequestLog();
			$transferRequestLogs->emp_id = $request->empid;
            $transferRequestLogs->event_at = date('Y-m-d H:i:s');;
            $transferRequestLogs->event_by = $usersessionId;
            $transferRequestLogs->event = 4;
            $transferRequestLogs->save();


            return response()->json(['success'=>'Transfer Formalities Successfully Done.']);
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




    public static function getNewDepartment($dept_id)
    {
            $deptInfo = Department::where('id',$dept_id)->orderBy('id', 'desc')->first();
            if($deptInfo)
            {
                return $deptInfo->department_name;
            }
            else
            {
                return '--';
            }
       
    }

    public static function getNewTL($tl_id)
    {
       
            $tL_details = Employee_details::where("job_role","Team Leader")->where("id",$tl_id)->orderBy("id","ASC")->first();

            if($tL_details)
            {
                return $tL_details->emp_name;
            }
            else
            {
                return '--';
            }
       
    }

    public static function getNewDesignation($designation_id)
    {
        $designationdetailsdata = Designation::where("id",$designation_id)->orderBy("id","ASC")->first();

        if($designationdetailsdata)
        {
            return $designationdetailsdata->name;
        }
        else
        {
            return '--';
        }
    }

    

    public static function getOldDepartment($empid)
    {
        $empInfo = Employee_details::where('emp_id',$empid)->orderBy('id', 'desc')->first();

        if($empInfo)
        {
            return $empInfo->dept_id;
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

    public static function getOldTL($empid)
    {
        $empInfo = Employee_details::where('emp_id',$empid)->orderBy('id', 'desc')->first();

        if($empInfo)
        {
            return $empInfo->tl_id;
            
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
		$request->session()->put('advancedPayRequest_page_limit',$offset);
	}


    public function getDesignationsListData(Request $request)
    {
        $deptid = $request->deptid;

        // $design=Designation::where("tlsm",2)->where("department_id",9)->where("status",1)->get();
        // $designarray=array();
        // foreach($design as $_design){
        //     $designarray[]=$_design->id;
        // }
        // $finalarray=implode(",",$designarray);
        //echo $finalarray;
        //print_r($sourcecodeArray);exit;
        //echo $whereraw;//exit;
        $designationdetailsdata = Designation::where("department_id",$deptid)->get();
        //return $teamLeadersdetailsdata;

        return view("ChangeDepartment/designationsList",compact('designationdetailsdata'));


        //
    }


    public function getEditDesignationsListData(Request $request)
    {
        $deptid = $request->deptid;

        // $design=Designation::where("tlsm",2)->where("department_id",9)->where("status",1)->get();
        // $designarray=array();
        // foreach($design as $_design){
        //     $designarray[]=$_design->id;
        // }
        // $finalarray=implode(",",$designarray);
        //echo $finalarray;
        //print_r($sourcecodeArray);exit;
        //echo $whereraw;//exit;
        $designationdetailsdata = Designation::where("department_id",$deptid)->get();
        //return $teamLeadersdetailsdata;

        return view("ChangeDepartment/editdesignationsList",compact('designationdetailsdata'));

        


        //
    }




   






    public function transferDeptRequestLogsTabData(Request $request)
    {
        $empid = $request->empid;
        $rowid = $request->rowid;

        //return 'Emp_id: '.$empid.' Row_id: '.$rowid;

        $transferRequestData = ChangeDepartmentRequestLog::where('emp_id',$empid)->orderBy('id','desc')->get();


        

        return view("ChangeDepartment/transferRequestLogsDetails",compact('transferRequestData')); 
      
    }













    // summary tab controls functions start


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

  



       

      

       public function finalTransferSummaryTabData(Request $request)
       {
            $empid = $request->empid;
            $rowid = $request->rowid;

            $transferRequestData = ChangeDepartmentRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();
            return view("ChangeDepartment/finalTransferRequestSummaryTab",compact('transferRequestData'));             
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


       public function downloadFile(Request $request)
	   {
			   $file =  $request->filename;

			   $extension = pathinfo($file, PATHINFO_EXTENSION);			   

			   
			   $fileName = public_path("/transferDocs");
			   $newf = $fileName."/".$file;


			   if($extension=='pdf')
			   {
				$headers = ['Content-Type: application/pdf'];
				$newName = 'transferLetter-'.time().'.pdf';
			   }
			   if($extension=='doc')
			   {
				$headers = ['Content-Type: application/pdf'];
				$newName = 'transferLetter-'.time().'.doc';
			   }
			   if($extension=='txt')
			   {
				$headers = ['Content-Type: text/plain'];
				$newName = 'transferLetter-'.time().'.txt';
			   }			   

				if($extension=='docx')
				{

				$headers = ['Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
				$newName = 'transferLetter-'.time().'.docx';
			   }

            if($extension=='csv')
			{
				$headers = ['Content-Type: text/csv'];
				$newName = 'transferLetter-'.time().'.csv';
			}

            if($extension=='jpeg')
			{
				$headers = ['Content-Type: image/jpeg'];
				$newName = 'transferLetter-'.time().'.jpeg';
			}

            if($extension=='jpg')
			{
				$headers = ['Content-Type: image/jpg'];
				$newName = 'transferLetter-'.time().'.jpg';
			}

            if($extension=='png')
			{
				$headers = ['Content-Type: image/png'];
				$newName = 'transferLetter-'.time().'.png';
			}
	   
			   //return $newf;

			 
			   return response()->download($newf, $newName, $headers);
	   }


       public function logsCron(Request $request)
       {
            $usersessionId=$request->session()->get('EmployeeId');
            $transferRequestData = ChangeDepartmentRequest::where('cron_status',0)->orderBy('id', 'asc')->get();

            if (count($transferRequestData) === 0) 
            {
                return response()->json(['success'=>'No records found to update.']);
            }
            else
            {
                foreach($transferRequestData as $data)
                {    
                    $transferRequestInfo = ChangeDepartmentRequest::where('emp_id',$data->emp_id)->orderBy('id', 'asc')->first();
    
                    $transferRequestInfo->old_dept_id=$this->getOldDepartment($data->emp_id);
                    $transferRequestInfo->old_tl=$this->getOldTL($data->emp_id);
                    $transferRequestInfo->old_designation=$this->getDesignation($data->emp_id);       
                    $transferRequestInfo->cron_status=1;
                    $transferRequestInfo->save();
                }
                return response()->json(['success'=>'Cron Data Updated.']);
            }

            
       }


       public static function getEmpChangeDeptStatus($empid)
       {
            $transferRequestInfo = ChangeDepartmentRequest::where('emp_id',$empid)->orderBy('id', 'desc')->first();

            if($transferRequestInfo)
            {
                if($transferRequestInfo->final_status==1)
                {
                    return 1;
                }
                elseif($transferRequestInfo->approved_reject_status==2)
                {
                    return 2;
                }
                else
                {
                    return 4;
                }
            }
            else
            {
                return 3;
            }

       }




       public function updateTeamManagementDataCron(Request $request)
	   {
            // $empid = $request->empid;
            // $rowid = $request->rowid;	
            $usersessionId=$request->session()->get('EmployeeId');   

            $transferRequestData = ChangeDepartmentRequest::where('final_status',1)->where('update_data_cron_status',0)->orderBy('id', 'desc')->get();

            if (count($transferRequestData) === 0) 
            {
                return response()->json(['success'=>'No records found to update.']);
            }
            else
            {
                foreach($transferRequestData as $data)
                {
                    $empdetails =  Employee_details::where("emp_id",$data->emp_id)->first();			
                    $empdetails->tl_id=NULL;
                    $empdetails->source_code=NULL;			 
                    $empdetails->dept_id=$data->new_dept_id;
                    $empdetails->designation_by_doc_collection=	$data->new_designation;	
                    $empdetails->save();
    
                    $empattributesMod = Employee_attribute::where('emp_id',$data->emp_id)->get();							
                    if(!empty($empattributesMod))
                    {
                        foreach($empattributesMod as $updatedept)
                        {
                            $empattributes = Employee_attribute::find($updatedept->id);
                            $empattributes->dept_id = $data->new_dept_id;
                            $empattributes->save();
                        }
                    }
                     
                    $transferRequestinfoData = ChangeDepartmentRequest::where('emp_id',$data->emp_id)->where('id',$data->id)->where('update_data_cron_status',0)->orderBy('id', 'desc')->first();
                    $transferRequestinfoData->update_data_cron_status=1;
                    $transferRequestinfoData->save();
    
    
                    $transferRequestLogs = new ChangeDepartmentRequestLog();
                    $transferRequestLogs->emp_id = $data->emp_id;
                    $transferRequestLogs->event_at = date('Y-m-d H:i:s');;
                    $transferRequestLogs->event_by = $usersessionId;
                    $transferRequestLogs->event = 5;
                    $transferRequestLogs->save();
                }
                return response()->json(['success'=>'Employee Data Updated Successfully using Cron.']);
            }

            

            

			
			
			
			
			
			
		}


}