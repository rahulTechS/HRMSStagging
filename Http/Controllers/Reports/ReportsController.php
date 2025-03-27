<?php
namespace App\Http\Controllers\Reports;

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
use App\Models\AdvancedPay\AdvancedPayLogs;
use App\Models\Reportissue\Reportissue;

use App\Models\Reports\ReportsDepartment;
use App\Models\Reports\Reports;
use App\Models\Reports\ReportsList;
use App\Models\Reports\UploadReport;
use App\Models\Reports\ReportsUserList;
use App\Models\Reports\ReportReminder;




class ReportsController extends Controller
{
	
    public  function Index(Request $request)
	{
        $responsibleEmpDetails = Reports::orderBy('id', 'desc')->get();

        $newResult=array();
        foreach($responsibleEmpDetails as $value)
        {
            $newResult[]=$value->responsibility_emp_id;
        }

        $empDetailsIndex = Employee_details::whereIn('emp_id',$newResult)->orderBy('id', 'desc')->get();
        
        
        $reportLists =  ReportsList::where("status",1)->orderBy("id","DESC")->get();


        $reportingFrequencyDetails = Reports::groupBy('frequency')->orderBy('id', 'desc')->get();


        
        //$empDetails = Employee_details::orderBy('id', 'desc')->get(); 
        //$empDetailsIndex = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        // $designationLists=Designation::where("status",1)->get();
        $moduleLists=Reportissue::groupBy('module')->orderBy('id', 'desc')->get();

        $reportsDepartmentLists=ReportsDepartment::where('status',1)->orderBy('id', 'desc')->get();

        $empsessionId=$request->session()->get('EmployeeId');
        $loggedinEmpDetails=User::where('id',$empsessionId)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $loggedinEmpReportDetails=Reports::where('responsibility_emp_id',$loggedinEmpDetails->employee_id)->orderBy('id', 'desc')->get();

                foreach($loggedinEmpReportDetails as $empReport)
                {
                    //$loggedinEmpReportDetails=UploadReport::where('responsible_emp_id',$loggedinEmpDetails->employee_id)->where('report_id')->orderBy('id', 'desc')->get();
                }
                $loginEmp = $loggedinEmpDetails->employee_id;
            }
            
        }


        $empAccessDetails=ReportsUserList::whereRaw('FIND_IN_SET('.$empsessionId.',user_ids)')->where('status',1)->orderBy('id', 'desc')->first();


        //return $empAccessDetails;
        $userAccess=0;
        if($empAccessDetails)
        {
            if($empAccessDetails->role_id==1)
            {
                $userAccess=1;
            }
            if($empAccessDetails->role_id==2)
            {
                $userAccess=2;
            }
        }

        



        $deptDetails=Reports::where('responsibility_emp_id',$loggedinEmpDetails->employee_id)->orWhere('reportingto_emp_id',$loggedinEmpDetails->employee_id)->groupBy('department_id')->orderBy('id', 'desc')->get();

        $reportdeptarr = array();
        foreach($deptDetails as $reportdept)
        {
            $reportdeptarr[]=$reportdept->department_id;
        }






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
        return view("Reports/index",compact('empDetailsIndex','departmentLists','moduleLists','tL_details','reportsDepartmentLists','reportdeptarr','reportLists','reportingFrequencyDetails','loginEmp','userAccess'));
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


    public function allReportsListing(Request $request)
    {
        $whereraw = '';
        $whererawother = '';
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


        if(!empty($request->session()->get('manageexcelReports_emp_name')) && $request->session()->get('manageexcelReports_emp_name') != 'All')
        {
            $fname = $request->session()->get('manageexcelReports_emp_name');
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
                $newResult=array();
                $empDetails = Employee_details::whereIn('emp_name',$cnameArray)->orderBy('id', 'desc')->get();               
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                $newempid2 = implode(",",$newResult);

                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'responsibility_emp_id IN ('.$newempid2.')';
                }
                else
                {
                    $whereraw .= ' And responsibility_emp_id IN ('.$newempid2.')';
                }
            }


           
        }


        if(!empty($request->session()->get('manageexcelReports_report_name')) && $request->session()->get('manageexcelReports_report_name') != 'All')
        {
            $empId = $request->session()->get('manageexcelReports_report_name');

            if($whereraw == '')
            {
                $whereraw = 'report_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And report_id IN ('.$empId.')';
            }
        }





        if(!empty($request->session()->get('manageexcelReports_reporting_frequency')) && $request->session()->get('manageexcelReports_reporting_frequency') != 'All')
        {
            $fname = $request->session()->get('manageexcelReports_reporting_frequency');
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
                    $whereraw = 'frequency IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And frequency IN('.$finalcname.')';
                }
            }


           
        }



        if(!empty($request->session()->get('reportIssues_dept')) && $request->session()->get('reportIssues_dept') != 'All')
        {
            $deptid = $request->session()->get('reportIssues_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('reported_issues_modules')) && $request->session()->get('reported_issues_modules') != 'All')
        {
            $desigid = $request->session()->get('reported_issues_modules');
            if($whereraw == '')
            {
                $whereraw = "module  IN ('".$desigid."')";
                //$whereraw = "emp_id  IN ('".$desigid."')";
            }
            else
            {
                $whereraw .= " And module  IN ('".$desigid."')";
                //$whereraw .= " emp_id  IN ('".$desigid."')";
            }
        }




        if(!empty($request->session()->get('manageexcelReports_fromdate')) && $request->session()->get('manageexcelReports_fromdate') != 'All')
        {
            $datefrom = $request->session()->get('manageexcelReports_fromdate');
             if($whereraw == '')
            {
                $whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
            }
        }
        if(!empty($request->session()->get('manageexcelReports_todate')) && $request->session()->get('manageexcelReports_todate') != 'All')
        {
            $dateto = $request->session()->get('manageexcelReports_todate');
             if($whereraw == '')
            {
                $whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
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

        $whereuser='';
        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $whereuser = 'responsibility_emp_id IN('.$loggedinEmpDetails->employee_id.')';
            }
            else
            {
            }
        }

        $whereuserReprtTo='';
        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $whereuserReprtTo = 'reportingto_emp_id IN('.$loggedinEmpDetails->employee_id.')';
            }
            else
            {
            }
        }

        $empAccessDetails=ReportsUserList::whereRaw('FIND_IN_SET('.$loggedinUserid.',user_ids)')->where('status',1)->orderBy('id', 'desc')->first();
        $userAccess=0;
        if($empAccessDetails)
        {
            if($empAccessDetails->role_id==1)
            {
                $userAccess=1;
            }
            if($empAccessDetails->role_id==2)
            {
                $userAccess=2;
            }
        }



        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
            // exit;
            if($userAccess==1) // admin Users
            {
                $requestDetails = Reports::whereRaw($whereraw)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = Reports::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }
            else // Sub Users
            {
                $requestDetails = Reports::whereRaw($whereuser)->orWhereRaw($whereuserReprtTo)->whereRaw($whereraw)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = Reports::whereRaw($whereuser)->orWhereRaw($whereuserReprtTo)->whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }
        }        
        else
        {
            if($userAccess==1) // admin Users
            {
                $requestDetails = Reports::orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = Reports::orderBy('id','desc')->get()->count();
            }
            else // Sub Users
            {
                $requestDetails = Reports::whereRaw($whereuser)->orWhereRaw($whereuserReprtTo)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = Reports::whereRaw($whereuser)->orWhereRaw($whereuserReprtTo)->orderBy('id','desc')->get()->count();
            }
        }

        //return $requestDetails;
        
        $requestDetails->setPath(config('app.url/listingAll'));
        return view("Reports/listingAll",compact('requestDetails','paginationValue','reportsCount'));
    }

    
    public static function addDeptRequestPopData(Request $request)
    {
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empRequested = AdvancedPayRequest::select('emp_id')->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id','desc')->get();

        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        if($departmentDetails != '')
        {
            $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
            if($empDetails!='')
            {
				$departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();

                $empDetails = Employee_details::where('dept_id',$empDetails->dept_id)->where('offline_status',1)->whereNotIn('emp_id', $empRequested)->orderBy('id', 'desc')
                ->get();
            }
        }
        else
        {
			$empDetails = Employee_details::where('offline_status',1)->whereNotIn('emp_id', $empRequested)->orderBy('id', 'desc')->get();		
        }
        
        //$empDetails = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();

        return view("Reports/addDeptRequest",compact('empDetails'));
    }

    public static function getEmpContentData(Request $request)
    {
        $empid=$request->empid;
        $empDetails='';
        $empAdvancedDetails = AdvancedPayRequest::where('emp_id',$empid)->where('approved_reject_status',1)->orderBy('id', 'desc')->get();

        $empRecoveryDetails = RecoveryAmt::where('emp_id',$empid)->orderBy('id', 'desc')->get();

        if($empAdvancedDetails)
        {
            $empDetails = $empAdvancedDetails;
        }


        $approvedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('approved_advanced_amt');
        $recoveredAmt = RecoveryAmt::where('emp_id',$empid)->sum('recovery_amt');
        $balancedAmt = $approvedAmt - $recoveredAmt;

        return view("AdvancedPay/addRequestContent",compact('empDetails','empRecoveryDetails','balancedAmt'));

    }

    public function addDepartmentRequestPostSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
			//'advancedamt' => 'required|numeric',
            'departmentName' => 'required',
           // 'teamleaders' => 'required', 
        ],
		[
			'departmentName.required'=> 'Please Add Department Name',
            //'advancedamt.numeric'=> 'Amount must be a number.',
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
            //$transferRequestData = AdvancedPayRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

            $addDepartmentRequest = new ReportsDepartment();
            $addDepartmentRequest->department_name = $request->departmentName;
            $addDepartmentRequest->created_at = date('Y-m-d H:i:s');
            $addDepartmentRequest->created_by = $usersessionId; 
            $addDepartmentRequest->status = 1;
            $addDepartmentRequest->comments = $request->requestComments;
            $addDepartmentRequest->save();

            return response()->json(['success'=>'New Department Added Successfully.']);
        }
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

    


    


   

    public function searchManageReportsFilterData(Request $request)
	{
			$frequency='';
			if($request->input('manageexcelReports_reporting_frequency')!=''){
			 
			 $frequency=implode(",", $request->input('manageexcelReports_reporting_frequency'));
			}

            $newDepartment='';
			if($request->input('manageexcelReports_department')!=''){
			 
			 $newDepartment=implode(",", $request->input('manageexcelReports_department'));
			}
			$empName='';
			if($request->input('manageexcelReportsUpload_emp_name')!=''){
			 
			 $empName=implode(",", $request->input('manageexcelReportsUpload_emp_name'));
			}
            $reportName='';
			if($request->input('manageexcelReportsUpload_empId')!=''){
			 
			 $reportName=implode(",", $request->input('manageexcelReportsUpload_empId'));
			}
			$dateto = $request->input('dateto');
			$datefrom = $request->input('datefrom');
            $uploaddateto = $request->input('uploaddateto');
			$uploaddatefrom = $request->input('uploaddatefrom');
			$name='';
			if($request->input('manageexcelReports_emp_name')!=''){
			 
			 $name=implode(",", $request->input('manageexcelReports_emp_name'));
			}
			//$name = $request->input('emp_name');
			$reportId='';
			if($request->input('manageexcelReports_empId')!=''){
			 
			 $reportId=implode(",", $request->input('manageexcelReports_empId'));
			}
			
			
			
			

			$request->session()->put('manageexcelReports_emp_name',$name);
            $request->session()->put('manageexcelReports_report_name',$reportId);
            $request->session()->put('manageexcelReports_reporting_frequency',$frequency);
            $request->session()->put('manageexcelReports_department_name',$newDepartment);
            // $request->session()->put('reported_issues_modules',$design);
            // $request->session()->put('advancedpay_requests_tl',$teamlaed);

            $request->session()->put('manageexcelReports_fromdate',$datefrom);
            $request->session()->put('manageexcelReports_todate',$dateto);
            $request->session()->put('manageexcelUploadReports_fromdate',$uploaddatefrom);
            $request->session()->put('manageexcelUploadReports_todate',$uploaddateto);           
			
			$request->session()->put('manageexcelReports_emp_nameUpload',$empName);
			$request->session()->put('manageexcelReportsUpload_report_name',$reportName);
			
			 //return  redirect('listingPageonboarding');	
	}

    public function resetManageReportsFilterData(Request $request)
    {
        $request->session()->put('manageexcelReports_emp_name','');
        $request->session()->put('manageexcelReports_report_name','');
        $request->session()->put('manageexcelReports_reporting_frequency','');
        $request->session()->put('manageexcelReports_department_name','');
        $request->session()->put('manageexcelReports_emp_nameUpload','');
        $request->session()->put('manageexcelReportsUpload_report_name','');

        $request->session()->put('manageexcelReports_fromdate','');
		$request->session()->put('manageexcelReports_todate','');
        $request->session()->put('manageexcelUploadReports_fromdate','');
		$request->session()->put('manageexcelUploadReports_todate','');
        
        
    }



    public function resetManageUploadReportsFilterData(Request $request)
    {
        // $request->session()->put('manageexcelReports_emp_name','');
        // $request->session()->put('manageexcelReports_report_name','');
        // $request->session()->put('manageexcelReports_reporting_frequency','');
        $request->session()->put('manageexcelReports_department_name','');
        $request->session()->put('manageexcelReports_emp_nameUpload','');
        $request->session()->put('manageexcelReportsUpload_report_name','');

  
        $request->session()->put('manageexcelUploadReports_fromdate','');
		$request->session()->put('manageexcelUploadReports_todate','');
        
        
    }


    public static function getReportDepartment($reportDeptid)
    {
        
        $reportDeptDetails = ReportsDepartment::where("id",$reportDeptid)->where('status',1)->orderBy("id","desc")->first();

        if($reportDeptDetails)
        {
            return $reportDeptDetails->department_name;
        }
        else
        {
            return "--";
        }
 
    }

    public static function getReportDepartmentid($reportid)
    {
        
        $reportDetails = Reports::where("report_id",$reportid)->where('status',1)->orderBy("id","desc")->first();

        if($reportDetails)
        {
            return $reportDetails->department_id;
        }
        else
        {
            return "--";
        }
 
    }

    public static function getReportName($reportid)
    {
        
        $reportDetails = ReportsList::where("id",$reportid)->where('status',1)->orderBy("id","desc")->first();

        if($reportDetails)
        {
            return $reportDetails->report_name;
        }
        else
        {
            return "--";
        }
 
    }


    public static function getReportReminderDuedate($reportid)
    {
        
        $reportDetails = Reports::where("report_id",$reportid)->where('status',1)->orderBy("id","desc")->first();

        if($reportDetails)
        {
            return $reportDetails->due_date;
        }
        else
        {
            return "--";
        }
 
    }
    public static function getReportReminderuploderfordate($reportid)
    {
        
        $reportDetails = UploadReport::where("report_id",$reportid)->where('status',1)->orderBy("id","desc")->first();

        if($reportDetails)
        {
            return $reportDetails->uploaded_for;
        }
        else
        {
            return "--";
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

        //$reportsDepartmentData = ReportsDepartment::where('status',1)->orderBy('id', 'desc')->get();

        return view("AdvancedPay/thirdTabRequestinfo",compact('advancedPayRequestData'));             
    }

    public function addReportsData(Request $request)
    {
         $empid = $request->empid;
         $rowid = $request->rowid;

         
        $reportsDepartmentData = ReportsDepartment::where('status',1)->orderBy('id', 'desc')->get();


         //$advancedPayRequestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();
         return view("Reports/addReportRequest",compact('reportsDepartmentData')); 
    }



    public function addReportsPostSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
            //'recoveryamt' => 'required|numeric|lte:totalRemaining',
            'reportDepartment' => 'required',
            'reportName' => 'required', 
        ],
        [
            'reportName.required'=> 'Please Add Report Name',
            // 'recoveryamt.numeric'=> 'Amount must be in Number',
            'reportDepartment.required'=> 'Please Select Report Department',
            // 'recoveryamt.lte'=> 'The Recovery Amount must be less than or equal to Total Remaining Amount',
                
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

            
            $addReportsList = new ReportsList();
            $addReportsList->report_name = $request->reportName;
            $addReportsList->dept_id = $request->reportDepartment;
            $addReportsList->created_at = date('Y-m-d H:i:s');
            $addReportsList->created_by = $usersessionId; 
            $addReportsList->status = 1;
            $addReportsList->comments = $request->reportComments;
            $addReportsList->save();


         


            return response()->json(['success'=>'New Report Added Successfully.']);
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

       
    public function advancedPayLogsTabData(Request $request)
    {
        $empid = $request->empid;
        $rowid = $request->rowid;
        //return 'Emp_id: '.$empid.' Row_id: '.$rowid;
        $advancedPayRequestData = AdvancedPayLogs::where('emp_id',$empid)->orderBy('id','desc')->get();
        return view("AdvancedPay/advancedPayLogsDetails",compact('advancedPayRequestData')); 
        
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


    public static function getResponsibleEmpName($reportid)
    {
        
        
        $reportsData = Reports::where('report_id',$reportid)->orderBy('id', 'desc')->first();

        if($reportsData)
        {
            $empData = Employee_details::where('emp_id', $reportsData->responsibility_emp_id)->orderBy('id','desc')->first();

            if($empData)
            {
                return $empData->emp_name;
            }
            else
            {
                return "--";
            }
        }
        else
        {
            return "--";
        }

        
        
        
        
        
    }

    public static function getDesignation($empid,$from)
    {
        
        if($from!='')
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
        else
        {
            $empData = User::where('id', $empid)->orderBy('id','desc')->first();
            if($empData)
            {
                $empInfo = Employee_attribute::where('emp_id',$empData->employee_id)->where('attribute_code','DESIGN')->orderBy('id', 'desc')->first();

                if($empInfo)
                {
                    return $empInfo->attribute_values;
                }
                else
                {
                    return '--';
                }
            }
            else
            {
                return "--";
            }
        }
        
        
        
        
    }

    public static function getTL($empid,$from)
    {
        if($from!='')
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
        else
        {
            $empData = User::where('id', $empid)->orderBy('id','desc')->first();
            if($empData)
            {
                $empInfo = Employee_details::where('emp_id',$empData->employee_id)->orderBy('id', 'desc')->first();

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
            else
            {
                return '--';
            }
            
            
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


    public static function getEmpid($empid,$from)
    {

        if($from!='')
        {
            return $empid;
        }
        else
        {
            $empData = User::where('id', $empid)->orderBy('id','desc')->first();
            if($empData)
            {
                return $empData->employee_id;
            }
            else
            {
                return "--";
            }
        }
        
    }


    public function addReportsResponsibilityData(Request $request)
    {
         
        
        $employeeData = Employee_details::where("offline_status",1)
			->join('department_details', 'employee_details.dept_id', '=', 'department_details.id')
			->select('employee_details.emp_id', 'department_details.department_name', 'employee_details.emp_name')
			->get();
        
        $reportsDepartmentData = ReportsDepartment::where('status',1)->orderBy('id', 'desc')->get();

        $reportsListData = ReportsList::where('status',1)->orderBy('id', 'desc')->get();

        return view("Reports/addReportsResponsibilityRequest",compact('reportsDepartmentData','reportsListData','employeeData')); 
    }


    public function addResponsibilityReportRequestPostData(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
            'reportingEmp' => 'required',
            'reportingtoEmp' => 'required',
            'reportDept' => 'required',
            'reportName' => 'required',
            'reportingFrequency' => 'required',
            //'amtRecoverydate' => 'required|date',
            //'reportName' => 'required', 
            'reportingDays' => 'required_if:reportingFrequency,weekly',
            'reportingDate' => 'required_if:reportingFrequency,monthly',
            
        ],
        [
            'reportingEmp.required'=> 'Please Select Responsible Employee',
            'reportingtoEmp.required'=> 'Please Select Replorting To Employee',
            'reportDept.required'=> 'Please Select Department',
            'reportName.required'=> 'Please Select Report',
            'reportingFrequency.required'=> 'Please Select Reporting Frequency',
            'reportingDays.required_if'=> 'Please Select Day Value',
            'reportingDate.required_if'=> 'Please Select Date',
            // 'reportingEmp.required'=> 'Please Select Employee',
            // 'recoveryamt.numeric'=> 'Amount must be in Number',
            // 'amtRecoverydate.required'=> 'Please Select recovery date',
            // 'recoveryamt.lte'=> 'The Recovery Amount must be less than or equal to Total Remaining Amount',
                
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

            
            $addReportsList = new Reports();
            $addReportsList->responsibility_emp_id = $request->reportingEmp;
            $addReportsList->reportingto_emp_id = $request->reportingtoEmp;
            $addReportsList->department_id = $request->reportDept;
            $addReportsList->report_id = $request->reportName;
            $addReportsList->frequency = $request->reportingFrequency;
            if($request->reportingFrequency=='monthly')
            {
                $addReportsList->days_date = $request->reportingDate;

                $Todaydate = date('d');                           
                if($request->reportingDate > $Todaydate)
                {
                    $oldreportDueDate = date('Y-m-'.$request->reportingDate);
                    $reportDueDate = date('Y-m-d', strtotime($oldreportDueDate));


                }
                else
                {
                    $oldreportDueDate = date('Y-m-'.$request->reportingDate, strtotime('+1 month'));
                    $enabledate1 = date('Y-m-d', strtotime(" +1 month",strtotime($oldreportDueDate)));

                }
            }
            else
            {
                $addReportsList->days_date = $request->reportingDays;

                if($request->reportingDays==1)
                {
                    $reportDueDate =  date('Y-m-d', strtotime('next Sunday'));
                }
                elseif($request->reportingDays==2)
                {
                    
                    $reportDueDate =  date('Y-m-d', strtotime('next Monday'));
                }
                elseif($request->reportingDays==3)
                {
                    
                    $reportDueDate =  date('Y-m-d', strtotime('next Tuesday'));
                }
                elseif($request->reportingDays==4)
                {
                    
                    $reportDueDate =  date('Y-m-d', strtotime('next Wednesday'));
                }
                elseif($request->reportingDays==5)
                {
                    
                    $reportDueDate =  date('Y-m-d', strtotime('next Thursday'));
                }
                elseif($request->reportingDays==6)
                {
                    
                    $reportDueDate =  date('Y-m-d', strtotime('next Friday'));
                }
                elseif($request->reportingDays==7)
                {
                    
                    $reportDueDate = date('Y-m-d', strtotime('next Saturday'));
                }
            }
            $addReportsList->due_date = $reportDueDate;            
            $addReportsList->created_at = date('Y-m-d H:i:s');
            $addReportsList->created_by = $usersessionId; 
            $addReportsList->status = 1;
            $addReportsList->comments = $request->responsibilityComments;
            $addReportsList->save();



            $usersEmpData = User::where('employee_id',$request->reportingEmp)->orderBy('id', 'desc')->first();
            $empAccessDetails=ReportsUserList::whereRaw('FIND_IN_SET('.$usersEmpData->id.',user_ids)')->where('role_id',2)->orderBy('id', 'desc')->first();
            if($empAccessDetails)
            {
            }
            else
            {
                $reportsUsersRole = ReportsUserList::where('role_id',2)->orderBy('id', 'desc')->first();
                $reportsUsersRole->user_ids = $reportsUsersRole->user_ids.','.$usersEmpData->id;
                $reportsUsersRole->save();
            }




            // $usersEmpDataTo = User::where('employee_id',$request->reportingtoEmp)->orderBy('id', 'desc')->first();
            // $empAccessDetailsTo=ReportsUserList::whereRaw('FIND_IN_SET('.$usersEmpDataTo->id.',user_ids)')->where('role_id',3)->orderBy('id', 'desc')->first();
            // if($empAccessDetailsTo)
            // {
            // }
            // else
            // {
            //     $reportsUsersRoleTo = ReportsUserList::where('role_id',3)->orderBy('id', 'desc')->first();
            //     $reportsUsersRoleTo->user_ids = $reportsUsersRoleTo->user_ids.','.$usersEmpDataTo->id;
            //     $reportsUsersRoleTo->save();
            // }

            

         


            return response()->json(['success'=>'New Report Added Successfully.']);
        }
    }


    public function Sales1Listingdata(Request $request)
    {
        
        $deptid=$request->deptid;
        
        
        
        $whereraw = '';
        $whererawother = '';
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



        if(!empty($request->session()->get('manageexcelReports_emp_name')) && $request->session()->get('manageexcelReports_emp_name') != 'All')
        {
            $fname = $request->session()->get('manageexcelReports_emp_name');
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
                $newResult=array();
                $empDetails = Employee_details::whereIn('emp_name',$cnameArray)->orderBy('id', 'desc')->get();               
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                $newempid2 = implode(",",$newResult);

                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'responsibility_emp_id IN ('.$newempid2.')';
                }
                else
                {
                    $whereraw .= ' And responsibility_emp_id IN ('.$newempid2.')';
                }
            }


           
        }


        if(!empty($request->session()->get('manageexcelReports_report_name')) && $request->session()->get('manageexcelReports_report_name') != 'All')
        {
            $empId = $request->session()->get('manageexcelReports_report_name');

            if($whereraw == '')
            {
                $whereraw = 'report_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And report_id IN ('.$empId.')';
            }
        }





        if(!empty($request->session()->get('manageexcelReports_reporting_frequency')) && $request->session()->get('manageexcelReports_reporting_frequency') != 'All')
        {
            $fname = $request->session()->get('manageexcelReports_reporting_frequency');
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
                    $whereraw = 'frequency IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And frequency IN('.$finalcname.')';
                }
            }


           
        }



        if(!empty($request->session()->get('reported_issues_modules')) && $request->session()->get('reported_issues_modules') != 'All')
        {
            $desigid = $request->session()->get('reported_issues_modules');
            if($whereraw == '')
            {
                $whereraw = "module  IN ('".$desigid."')";
                //$whereraw = "emp_id  IN ('".$desigid."')";
            }
            else
            {
                $whereraw .= " And module  IN ('".$desigid."')";
                //$whereraw .= " emp_id  IN ('".$desigid."')";
            }
        }




        if(!empty($request->session()->get('manageexcelReports_fromdate')) && $request->session()->get('manageexcelReports_fromdate') != 'All')
        {
            $datefrom = $request->session()->get('manageexcelReports_fromdate');
            $newResult=array();
            $uploadreportDetails = UploadReport::where('created_at','>=',$datefrom.' 00:00:00')->orderBy('id', 'desc')->get();

            if (count($uploadreportDetails) === 0) 
            {
                $newResult[]=0;
            }
            else
            {
                foreach($uploadreportDetails as $value)
                {
                    $newResult[]=$value->report_id;
                }
            }
            $newid2 = implode(",",$newResult);


            if($whereraw == '')
            {
                $whereraw = 'report_id IN ('.$newid2.')';
            }
            else
            {
                $whereraw .= ' And report_id IN ('.$newid2.')';
            }
        }



        if(!empty($request->session()->get('manageexcelReports_todate')) && $request->session()->get('manageexcelReports_todate') != 'All')
        {
            $dateto = $request->session()->get('manageexcelReports_todate');
            $newResult=array();
            $uploadreportDetails = UploadReport::where('created_at','<=',$dateto.' 00:00:00')->orderBy('id', 'desc')->get();

            if (count($uploadreportDetails) === 0) 
            {
                $newResult[]=0;
            }
            else
            {
                foreach($uploadreportDetails as $value)
                {
                    $newResult[]=$value->report_id;
                }
            }
            $newid2 = implode(",",$newResult);


            if($whereraw == '')
            {
                $whereraw = 'report_id IN ('.$newid2.')';
            }
            else
            {
                $whereraw .= ' And report_id IN ('.$newid2.')';
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


        $whereuser='';
        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $whereuser = 'responsibility_emp_id IN('.$loggedinEmpDetails->employee_id.')';
            }
            else
            {
            }
        }

        $whereuserReprtTo='';
        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $whereuserReprtTo = 'reportingto_emp_id IN('.$loggedinEmpDetails->employee_id.')';
            }
            else
            {
            }
        }
        


        $empAccessDetails=ReportsUserList::whereRaw('FIND_IN_SET('.$loggedinUserid.',user_ids)')->where('status',1)->orderBy('id', 'desc')->first();
        $userAccess=0;
        if($empAccessDetails)
        {
            if($empAccessDetails->role_id==1)
            {
                $userAccess=1;
            }
            if($empAccessDetails->role_id==2)
            {
                $userAccess=2;
            }
        }


        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
            // exit;
            if($userAccess==1) // admin Users
            {
                $requestDetails = Reports::where('department_id',$deptid)->whereRaw($whereraw)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = Reports::where('department_id',$deptid)->whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }
            else // Sub Users
            {
                $requestDetails = Reports::where(function ($query) use ($whereuser, $whereuserReprtTo) { 
                    $query->whereRaw($whereuser) 
                        ->orWhereRaw($whereuserReprtTo);
                     })->where(function ($query) use ($deptid) { 
                        $query->where('department_id',$deptid); 
                })->where(function ($query) use ($whereraw) { 
                    $query->whereRaw($whereraw); 
            })->orderBy('id', 'desc')->paginate($paginationValue);


                $reportsCount = Reports::where(function ($query) use ($whereuser, $whereuserReprtTo) { 
                    $query->whereRaw($whereuser) 
                        ->orWhereRaw($whereuserReprtTo);
                     })->where(function ($query) use ($deptid) { 
                        $query->where('department_id',$deptid); 
                })->where(function ($query) use ($whereraw) { 
                    $query->whereRaw($whereraw); 
            })->orderBy('id', 'desc')->get()->count();

                

            }
        }        
        else
        {
            if($userAccess==1) // admin Users
            {
                $requestDetails = Reports::where('department_id',$deptid)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = Reports::where('department_id',$deptid)->orderBy('id','desc')->get()->count();
            }
            else // Sub Users
            {
                //echo $deptid;
                //exit;
                //$requestDetails = Reports::whereRaw($whereuser)->orWhereRaw($whereuserReprtTo)->where('department_id',$deptid)->orderBy('id', 'desc')
                //->paginate($paginationValue);
                //->toSql();
                //dd($requestDetails);

                $requestDetails = Reports::where(function ($query) use ($whereuser, $whereuserReprtTo) { 
                    $query->whereRaw($whereuser) 
                        ->orWhereRaw($whereuserReprtTo);
                     })->where(function ($query) use ($deptid) { 
                        $query->where('department_id',$deptid); 
                })->orderBy('id', 'desc')->paginate($paginationValue);


                $reportsCount = Reports::where(function ($query) use ($whereuser, $whereuserReprtTo) { 
                    $query->whereRaw($whereuser) 
                        ->orWhereRaw($whereuserReprtTo);
                     })->where(function ($query) use ($deptid) { 
                        $query->where('department_id',$deptid); 
                })->orderBy('id', 'desc')
                ->get()->count();


                



            }
        }

        //return $requestDetails;
        
        $requestDetails->setPath(config('app.url/listingSales'));
        return view("Reports/listingSales",compact('requestDetails','paginationValue','reportsCount','userAccess'));
    }

    public function uploadReportFormData(Request $request)
    {
        $reportid=$request->reportid;
        $deptid=$request->deptid;
        $empid=$request->empid;

        $reportsData = Reports::where('report_id',$reportid)->where('department_id',$deptid)->orderBy('id', 'desc')->first();

        //return "Helloooo".$reportsData;
        
        return view("Reports/uploadReportForm",compact('reportid','deptid','empid','reportsData'));

    }

    public function uploadReportFormDataviaTop(Request $request)
    {
        // $reportid=$request->reportid;
        // $deptid=$request->deptid;
        // $empid=$request->empid;
        $loggedinUserid=$request->session()->get('EmployeeId');
        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $loginempid = $loggedinEmpDetails->employee_id;
                $reportsUsersData = Reports::where('responsibility_emp_id',$loginempid)->orderBy('id', 'desc')->get();

            }
            else
            {
                $loginempid = '';

            }
        }
        $reportsid=array();
        foreach($reportsUsersData as $report)
        {
            $reportsid[] = $report->report_id;
        }



        $reportsData = ReportsList::where('status',1)->whereIn('id',$reportsid)->orderBy('id', 'desc')->get();
        
        return view("Reports/uploadReportFormviaTop",compact('reportsData'));

    }

    public function getReportDetailsData(Request $request)
    {
        $reportid=$request->reportid;
        // $deptid=$request->deptid;
        // $empid=$request->empid;

        $reportsDetailsData = Reports::where('report_id',$reportid)->orderBy('id', 'desc')->first();


        if($reportsDetailsData)
        {
            
            if($reportsDetailsData->frequency=="monthly")
            {
                $reportUploadDate = $reportsDetailsData->due_date;
                $Todaydate = date('d');      
                if($reportsDetailsData->days_date > $Todaydate)
                {
                    //echo $data->days_date.' '.date('M, Y');
                    //$enabledate = date('Y-m-'.$reportsDetailsData->days_date);
                    $enabledate = date('d-m-Y', strtotime($reportsDetailsData->due_date));
                    $enabledate1 = date('d-m-Y', strtotime(" +1 month",strtotime($reportsDetailsData->due_date)));
                    $enabledate2 = date('d-m-Y', strtotime(" -1 month",strtotime($reportsDetailsData->due_date)));

                    //$newenabledate = "$enabledate.','.$enabledate1";



                }
                else
                {
                    //$enabledate = date('Y-m-'.$reportsDetailsData->days_date, strtotime('+1 month'));
                    $enabledate = date('d-m-Y', strtotime($reportsDetailsData->due_date));
                    $enabledate1 = date('d-m-Y', strtotime(" +1 month",strtotime($reportsDetailsData->due_date)));
                    $enabledate2 = date('d-m-Y', strtotime(" -1 month",strtotime($reportsDetailsData->due_date)));



                }
                return [$reportsDetailsData, $reportUploadDate, $enabledate, $enabledate1, $enabledate2];
            }
            else
            {
                if($reportsDetailsData->days_date==1)
                {
                    $disabledays = "1,2,3,4,5,6";
                    //$reportUploadDate = date('Y-m-d', strtotime('next Sunday'));
                    $reportUploadDate = $reportsDetailsData->due_date;

                    
                }
                elseif($reportsDetailsData->days_date==2)
                {
                    //$reportUploadDate = date('Y-m-d', strtotime('next Monday'));
                    $reportUploadDate = $reportsDetailsData->due_date;
                    $disabledays = "0,2,3,4,5,6";
                }
                elseif($reportsDetailsData->days_date==3)
                {
                    //$reportUploadDate = date('Y-m-d', strtotime('next Tuesday'));
                    $reportUploadDate = $reportsDetailsData->due_date;
                    $disabledays = "0,1,3,4,5,6";
                }
                elseif($reportsDetailsData->days_date==4)
                {
                    //$reportUploadDate = date('Y-m-d', strtotime('next Wednesday'));
                    $reportUploadDate = $reportsDetailsData->due_date;
                    $disabledays = "0,1,2,4,5,6";
                }
                elseif($reportsDetailsData->days_date==5)
                {
                    //$reportUploadDate = date('Y-m-d', strtotime('next Thursday'));
                    $reportUploadDate = $reportsDetailsData->due_date;
                    $disabledays = "0,1,2,3,5,6";
                }
                elseif($reportsDetailsData->days_date==6)
                {
                    //$reportUploadDate = date('Y-m-d', strtotime('next Friday'));
                    $reportUploadDate = $reportsDetailsData->due_date;
                    $disabledays = "0,1,2,3,4,6";
                }
                elseif($reportsDetailsData->days_date==7)
                {
                    //$reportUploadDate = date('Y-m-d', strtotime('next Saturday'));
                    $reportUploadDate = $reportsDetailsData->due_date;
                    $disabledays = "0,1,2,3,4,5";
                }
                return [$reportsDetailsData, $disabledays, $reportUploadDate];

            }


        }
        else
        {
            return response()->json(['reporterror'=>1]);

        }
        
        //return $reportsDetailsData;



    }


    




    public function uploadReportRequestPostData(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
            'file_report' => 'required|mimes:pdf,xlsx',
           
            
        ],
        [
            
                
        ]);

        if(($validator->fails()))
        {
            return response()->json(['error'=>$validator->errors()]);
        }
        else
        {
            //return $request->all();



            $file = $request->file('file_report');

           

            $filename = $request->reportid.'_Report_'.date("Y-m-d_h-i-s").'.xlsx';  
            //$filename = time().'_'.$file->getClientOriginalName();

            if(file_exists(public_path('uploadReports/'.$filename)))
            {
                unlink(public_path('uploadReports/'.$filename));
            }
            
           

            // File upload location
            $location = 'uploadReports';

             // File extension
             $extension = $file->getClientOriginalExtension();

            // Upload file
            $file->move(public_path('uploadReports/'), $filename);

            // File path
            $filepath = url('uploadReports/'.$filename);


            $usersessionId=$request->session()->get('EmployeeId');


            $chkUploadedReport = UploadReport::where('report_id',$request->reportid)->where('responsible_emp_id',$request->empid)->where('uploaded_for',$request->reportingDatefor)->orderBy('id', 'desc')->first();

            if($chkUploadedReport)
            {
                $chkUploadedReport->report_id = $request->reportid;
                $chkUploadedReport->dept_id = $request->deptid;
                $chkUploadedReport->uploaded_report = $filename;
                $chkUploadedReport->responsible_emp_id = $request->empid;
                $chkUploadedReport->uploaded_for = $request->reportingDatefor;
                //$chkUploadedReport->created_at = date('Y-m-d H:i:s');
                $chkUploadedReport->uploaded_by = $usersessionId; 
                $chkUploadedReport->status = 1;
                $chkUploadedReport->comments = $request->uploadComments;
                $chkUploadedReport->reportingto_emp_id = $request->reportingToemp;
                $chkUploadedReport->report_created_by = $request->reportCreated;
                $chkUploadedReport->save();
            }
            else
            {
                $addReportsList = new UploadReport();
                $addReportsList->report_id = $request->reportid;
                $addReportsList->dept_id = $request->deptid;
                $addReportsList->uploaded_report = $filename;
                $addReportsList->responsible_emp_id = $request->empid;
                $addReportsList->uploaded_for = $request->reportingDatefor;
                $addReportsList->created_at = date('Y-m-d H:i:s');
                $addReportsList->uploaded_by = $usersessionId; 
                $addReportsList->status = 1;
                $addReportsList->comments = $request->uploadComments;
                $addReportsList->reportingto_emp_id = $request->reportingToemp;
                $addReportsList->report_created_by = $request->reportCreated;
                $addReportsList->save();
            }

            
            

            $reportsDetailsData = Reports::where('report_id',$request->reportid)->where('department_id',$request->deptid)->orderBy('id', 'desc')->first();

            
            
            if($reportsDetailsData)
            {
                if($reportsDetailsData->frequency=='weekly')
                {
                    $nextDueDate = date('Y-m-d', strtotime(" +1 week",strtotime($request->reportingDatefor)));
                    $reportsDetailsData->due_date = $nextDueDate;
                    $reportsDetailsData->report_read_upload_status = 1;
                    $reportsDetailsData->save();
                }
                if($reportsDetailsData->frequency=='monthly')
                {
                    $nextDueDate = date('Y-m-d', strtotime(" +1 month",strtotime($request->reportingDatefor)));
                    $reportsDetailsData->due_date = $nextDueDate;
                    $reportsDetailsData->report_read_upload_status = 1;
                    $reportsDetailsData->save();

                }
                //return $nextDueDate;
                //

            }

         


            return response()->json(['success'=>'Report Uploaded Successfully.']);
        }
    }


    public function uploadReportRequestPostDatafromTop(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
            'file_report' => 'required|mimes:pdf,xlsx',
            //'recoveryamt' => 'required|numeric|lte:totalRemaining',
            'reportName' => 'required',

           
            
        ],
        [
            'reportName.required'=> 'Please Add Report Name',

                
        ]);

        if(($validator->fails()))
        {
            return response()->json(['error'=>$validator->errors()]);
        }
        else
        {
            //return $request->all();


            $reportsDetailsData = Reports::where('report_id',$request->reportid)->orderBy('id', 'desc')->first();

            




            $file = $request->file('file_report');

           

            $filename = $request->reportid.'_Report_'.date("Y-m-d_h-i-s").'.xlsx';  
            //$filename = time().'_'.$file->getClientOriginalName();

            if(file_exists(public_path('uploadReports/'.$filename)))
            {
                unlink(public_path('uploadReports/'.$filename));
            }
            
           

            // File upload location
            $location = 'uploadReports';

             // File extension
             $extension = $file->getClientOriginalExtension();

            // Upload file
            $file->move(public_path('uploadReports/'), $filename);

            // File path
            $filepath = url('uploadReports/'.$filename);


            $usersessionId=$request->session()->get('EmployeeId');

            if($reportsDetailsData->frequency=='monthly')
            {
                $chkUploadedReport = UploadReport::where('report_id',$request->reportid)->where('responsible_emp_id',$request->empid)->where('uploaded_for',$request->reportingDateforMonth)->orderBy('id', 'desc')->first();
            }
            if($reportsDetailsData->frequency=='weekly')
            {
                $chkUploadedReport = UploadReport::where('report_id',$request->reportid)->where('responsible_emp_id',$request->empid)->where('uploaded_for',$request->reportingDateforWeek)->orderBy('id', 'desc')->first();
            }

            

            if($chkUploadedReport)
            {
                $chkUploadedReport->report_id = $request->reportid;
                $chkUploadedReport->dept_id = $request->deptid;
                $chkUploadedReport->uploaded_report = $filename;
                $chkUploadedReport->responsible_emp_id = $request->empid;
                if($reportsDetailsData->frequency=='monthly')
                {
                    $chkUploadedReport->uploaded_for = $request->reportingDateforMonth;
                }
                if($reportsDetailsData->frequency=='weekly')
                {
                    $chkUploadedReport->uploaded_for = $request->reportingDateforWeek;
                }
                //$chkUploadedReport->created_at = date('Y-m-d H:i:s');
                $chkUploadedReport->uploaded_by = $usersessionId; 
                $chkUploadedReport->status = 1;
                $chkUploadedReport->comments = $request->uploadComments;
                $chkUploadedReport->reportingto_emp_id = $request->reportingToemp;
                $chkUploadedReport->report_created_by = $request->reportCreated;
                $chkUploadedReport->save();
            }
            else
            {
                $addReportsList = new UploadReport();
                $addReportsList->report_id = $request->reportid;
                $addReportsList->dept_id = $request->deptid;
                $addReportsList->uploaded_report = $filename;
                $addReportsList->responsible_emp_id = $request->empid;
                if($reportsDetailsData->frequency=='monthly')
                {
                    $addReportsList->uploaded_for = $request->reportingDateforMonth;
                }
                if($reportsDetailsData->frequency=='weekly')
                {
                    $addReportsList->uploaded_for = $request->reportingDateforWeek;
                }
                $addReportsList->created_at = date('Y-m-d H:i:s');
                $addReportsList->uploaded_by = $usersessionId; 
                $addReportsList->status = 1;
                $addReportsList->comments = $request->uploadComments;
                $addReportsList->reportingto_emp_id = $request->reportingToemp;
                $addReportsList->report_created_by = $request->reportCreated;
                $addReportsList->save();
            }
            
            

            $reportsDetailsData = Reports::where('report_id',$request->reportid)->where('department_id',$request->deptid)->orderBy('id', 'desc')->first();

            
            
            if($reportsDetailsData)
            {
                if($reportsDetailsData->frequency=='weekly')
                {
                    $nextDueDate = date('Y-m-d', strtotime(" +1 week",strtotime($request->reportingDateforWeek)));
                    $reportsDetailsData->due_date = $nextDueDate;
                    $reportsDetailsData->report_read_upload_status = 1;
                    $reportsDetailsData->save();
                }
                if($reportsDetailsData->frequency=='monthly')
                {
                    $nextDueDate = date('Y-m-d', strtotime(" +1 month",strtotime($request->reportingDateforMonth)));
                    $reportsDetailsData->due_date = $nextDueDate;
                    $reportsDetailsData->report_read_upload_status = 1;
                    $reportsDetailsData->save();

                }
                //return $nextDueDate;
                //

            }
         


            return response()->json(['success'=>'Report Uploaded Successfully.']);
        }
    }



    public static function getPreviousReprtDate($reportid)
	{
        $reportsUploadData = UploadReport::where('report_id',$reportid)->orderBy('id', 'desc')->first();

        if($reportsUploadData)
        {
            //return $reportsUploadData->created_at;
            return date('d M, Y', strtotime($reportsUploadData->created_at));
        }
        else
        {
            return 1;
        }
	}


    public static function chkReportUploadStatus($reportid)
	{
        $reportsUploadData = Reports::where('report_id',$reportid)->orderBy('id', 'desc')->first();

        if($reportsUploadData)
        {
            return $reportsUploadData->report_read_upload_status;
        }
        else
        {
            return 3;
        }
	}








    // uploaded Reports start

    public  function uploadedReportsIndex(Request $request)
	{
        
        
        $responsibleEmpDetails = Reports::orderBy('id', 'desc')->get();

        $newResult=array();
        foreach($responsibleEmpDetails as $value)
        {
            $newResult[]=$value->responsibility_emp_id;
        }

        $empDetailsIndex = Employee_details::whereIn('emp_id',$newResult)->orderBy('id', 'desc')->get();
        
        
        $reportLists =  ReportsList::where("status",1)->orderBy("id","DESC")->get();


        $reportingFrequencyDetails = Reports::groupBy('frequency')->orderBy('id', 'desc')->get();
        
        
        $reportDepartmentDetails = ReportsDepartment::where('status',1)->orderBy('id', 'desc')->get();

        
        
        
        
        
        
        
        //$empDetails = Employee_details::orderBy('id', 'desc')->get(); 
        //$empDetailsIndex = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        // $designationLists=Designation::where("status",1)->get();
        $moduleLists=Reportissue::groupBy('module')->orderBy('id', 'desc')->get();

        $reportsDepartmentLists=ReportsDepartment::where('status',1)->orderBy('id', 'desc')->get();

        $empsessionId=$request->session()->get('EmployeeId');



        $empAccessDetails=ReportsUserList::whereRaw('FIND_IN_SET('.$empsessionId.',user_ids)')->where('status',1)->orderBy('id', 'desc')->first();


        //return $empAccessDetails;
        $userAccess=0;
        if($empAccessDetails)
        {
            if($empAccessDetails->role_id==1)
            {
                $userAccess=1;
            }
            if($empAccessDetails->role_id==2)
            {
                $userAccess=2;
            }
        }










        $loggedinEmpDetails=User::where('id',$empsessionId)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $loggedinEmpReportDetails=Reports::where('responsibility_emp_id',$loggedinEmpDetails->employee_id)->orderBy('id', 'desc')->get();

                foreach($loggedinEmpReportDetails as $empReport)
                {
                    //$loggedinEmpReportDetails=UploadReport::where('responsible_emp_id',$loggedinEmpDetails->employee_id)->where('report_id')->orderBy('id', 'desc')->get();
                }
                $loginEmp = $loggedinEmpDetails->employee_id;
            }
            
        }		
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
        return view("Reports/uploadedReportsIndex",compact('empDetailsIndex','departmentLists','moduleLists','tL_details','reportsDepartmentLists','reportLists','reportingFrequencyDetails','reportDepartmentDetails','loginEmp','userAccess'));
    }


    public function uploadedAllReportsListing(Request $request)
    {
        $whereraw = '';
        $whererawother = '';
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


        if(!empty($request->session()->get('manageexcelReports_emp_nameUpload')) && $request->session()->get('manageexcelReports_emp_nameUpload') != 'All')
        {
            $fname = $request->session()->get('manageexcelReports_emp_nameUpload');
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
                $newResult=array();
                $empDetails = Employee_details::whereIn('emp_name',$cnameArray)->orderBy('id', 'desc')->get();               
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                $newempid2 = implode(",",$newResult);

                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'responsible_emp_id IN ('.$newempid2.')';
                }
                else
                {
                    $whereraw .= ' And responsible_emp_id IN ('.$newempid2.')';
                }
            }


           
        }


        if(!empty($request->session()->get('manageexcelReportsUpload_report_name')) && $request->session()->get('manageexcelReportsUpload_report_name') != 'All')
        {
            $empId = $request->session()->get('manageexcelReportsUpload_report_name');

            if($whereraw == '')
            {
                $whereraw = 'report_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And report_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('manageexcelReports_department_name')) && $request->session()->get('manageexcelReports_department_name') != 'All')
        {
            $empId = $request->session()->get('manageexcelReports_department_name');

            if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$empId.')';
            }
        }

        


        if(!empty($request->session()->get('reported_issues_modules')) && $request->session()->get('reported_issues_modules') != 'All')
        {
            $desigid = $request->session()->get('reported_issues_modules');
            if($whereraw == '')
            {
                $whereraw = "module  IN ('".$desigid."')";
                //$whereraw = "emp_id  IN ('".$desigid."')";
            }
            else
            {
                $whereraw .= " And module  IN ('".$desigid."')";
                //$whereraw .= " emp_id  IN ('".$desigid."')";
            }
        }




        if(!empty($request->session()->get('manageexcelUploadReports_fromdate')) && $request->session()->get('manageexcelUploadReports_fromdate') != 'All')
        {
            $datefrom = $request->session()->get('manageexcelUploadReports_fromdate');
             if($whereraw == '')
            {
                $whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
            }
        }
        if(!empty($request->session()->get('manageexcelUploadReports_todate')) && $request->session()->get('manageexcelUploadReports_todate') != 'All')
        {
            $dateto = $request->session()->get('manageexcelUploadReports_todate');
             if($whereraw == '')
            {
                $whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
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

        $whereuser='';
        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $whereuser = 'responsible_emp_id IN('.$loggedinEmpDetails->employee_id.')';
            }
            else
            {
            }
        }


        $whereuserReprtTo='';
        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $whereuserReprtTo = 'reportingto_emp_id IN('.$loggedinEmpDetails->employee_id.')';
            }
            else
            {
            }
        }

        $empAccessDetails=ReportsUserList::whereRaw('FIND_IN_SET('.$loggedinUserid.',user_ids)')->where('status',1)->orderBy('id', 'desc')->first();
        $userAccess=0;
        if($empAccessDetails)
        {
            if($empAccessDetails->role_id==1)
            {
                $userAccess=1;
            }
            if($empAccessDetails->role_id==2)
            {
                $userAccess=2;
            }
        }

        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
            // exit;
            if($userAccess==1) // admin Users
            {
                $requestDetails = UploadReport::whereRaw($whereraw)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = UploadReport::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }
            else // Sub Users
            {
                $requestDetails = UploadReport::whereRaw($whereuser)->orWhereRaw($whereuserReprtTo)->whereRaw($whereraw)->orderBy('id', 'desc')->paginate($paginationValue);

                $reportsCount = UploadReport::whereRaw($whereuser)->orWhereRaw($whereuserReprtTo)->whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }
        }        
        else
        {
            if($userAccess==1) // admin Users
            {
                $requestDetails = UploadReport::orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = UploadReport::orderBy('id','desc')->get()->count();
            }
            else // Sub Users
            {
                $requestDetails = UploadReport::whereRaw($whereuser)->orWhereRaw($whereuserReprtTo)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = UploadReport::whereRaw($whereuser)->orWhereRaw($whereuserReprtTo)->orderBy('id','desc')->get()->count();
            }
        }

        //return $requestDetails;
        
        $requestDetails->setPath(config('app.url/uploadedReportsListing'));
        return view("Reports/uploadedReportsListing",compact('requestDetails','paginationValue','reportsCount'));
    }





    public function downloadReportsFile(Request $request)
    {
            $file =  $request->filename;
            $reportid =  $request->reportid;


            $extension = pathinfo($file, PATHINFO_EXTENSION);			   

            
            $fileName = public_path("/uploadReports");
            $newf = $fileName."/".$file;


            if($extension=='pdf')
            {
                $headers = ['Content-Type: application/pdf'];
                $newName = 'incrementFile-'.time().'.pdf';
            }
            if($extension=='doc')
            {
                $headers = ['Content-Type: application/pdf'];
                $newName = 'incrementFile-'.time().'.doc';
            }
            if($extension=='txt')
            {
                $headers = ['Content-Type: text/plain'];
                $newName = 'incrementFile-'.time().'.txt';
            }			   

            if($extension=='docx')
            {

                $headers = ['Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $newName = 'incrementFile-'.time().'.docx';
            }

            if($extension=='xlsx')
            {

                $headers = ['Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
                $newName = $file.'.xlsx';
            }



           // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    
            //return $newf;


            $empsessionId=$request->session()->get('EmployeeId');
            $loggedinEmpDetails=User::where('id',$empsessionId)->orderBy('id', 'desc')->first();

            if($loggedinEmpDetails)
            {
                if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
                {
                    $loginEmp = $loggedinEmpDetails->employee_id;
                }                
            }

            //$reportsDetailsData = Reports::where('report_id',$reportid)->orderBy('id', 'desc')->first();
            $reportsDetailsData=Reports::where('report_id',$reportid)->where('reportingto_emp_id',$loginEmp)->orderBy('id', 'desc')->first();

            if($reportsDetailsData)
            {
                $reportsDetailsData->report_read_upload_status = 2;
                $reportsDetailsData->save();
            }


            


            
            return response()->download($newf, $newName, $headers);
    }


    public function getReportDetailsDatabyDepartment(Request $request)
    {
        $deptid = $request->deptid;  

        $reportsData = ReportsList::where('dept_id',$deptid)->orderBy('id', 'desc')->get();

        $reportlistArr = array();

        foreach($reportsData as $reports)
        {
            $reportsListData = ReportsList::where('id',$reports->id)->where('dept_id',$deptid)->orderBy('id', 'desc')->first();
            $reportlistArr[$reportsListData->id]=$reportsListData->report_name;
        }

        // if($reportsData)
        // {
        //     $reportsListData = ReportsList::where('report_id',$reportsData->report_id)->orderBy('id', 'desc')->get();

        //     if()
        //     {

        //     }

        // }

        return $reportlistArr;

    }



    public function viewReportDetailsData(Request $request)
    {
        $reportid=$request->reportid;
        $deptid=$request->deptid;

        $reportsData = Reports::where('report_id',$reportid)->where('department_id',$deptid)->orderBy('id', 'desc')->first();
        
        return view("Reports/viewReportDetails",compact('reportid','deptid','reportsData'));

    }



    public function reportDueStatusDataChkOld(Request $request)
    {
        //$loggedinEmpid=$request->useremp;
        $loggedinUserid=$request->session()->get('EmployeeId');

        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();
        if($loggedinEmpDetails)
        {
            $loggedinEmpid=$loggedinEmpDetails->employee_id;
        }
        

        $deptid=$request->chkstatus;
        $currentDate = date('Y-m-d');

        $reportsData = Reports::where('responsibility_emp_id',$loggedinEmpid)->orderBy('id', 'desc')->get();

        $reportsDataforadmin = Reports::where('reportingto_emp_id',$loggedinEmpid)->orderBy('id', 'desc')->get();

        $showPopup=0;
        $showAdminPopup=0;
        $reportidArr=array();
        $reportDuedateArr=array();
        $reportidAdminArr=array();
        $reportDuedateAdminArr=array();
        foreach($reportsData as $chkreport)
        {            
            //echo "Hello".$chkreport->department_id;
            $chkUploadReportsData = UploadReport::where('responsible_emp_id',$chkreport->responsibility_emp_id)->where('report_id',$chkreport->report_id)->orderBy('id', 'desc')->first();

            if($chkUploadReportsData)
            {
            }
            else
            {
                $reportDueDate = $chkreport->due_date;
                $date1 = new DateTime($reportDueDate);
                $date2 = new DateTime($currentDate);
                $interval = $date1->diff($date2);
                if($interval->days <= 3)
                {
                    // display pop up
                    $showPopup=1;
                    $reportidArr[]=$chkreport->report_id;
                    $reportDuedateArr[]=$chkreport->due_date;


                }
                else
                {
                    //echo "Sample";
                }
            }

        }

        foreach($reportsDataforadmin as $chkreportforadmin)
        {            
            $chkUploadReportsData = UploadReport::where('report_id',$chkreportforadmin->report_id)->orderBy('id', 'desc')->first();

            if($chkUploadReportsData)
            {
                $reportsUpChk = Reports::where('reportingto_emp_id',$loggedinEmpid)->where('report_read_upload_status',1)->orderBy('id', 'desc')->first();

                if($reportsUpChk)
                {
                    $showAdminPopup=1;
                    //$showPopup=1;
                    
                    $reportidAdminArr[]=$chkreportforadmin->report_id;
                    $reportDuedateAdminArr[]=$chkUploadReportsData->uploaded_for;
                }
            }
            else
            {
                // $reportDueDate = $chkreportforadmin->due_date;
                // $date1 = new DateTime($reportDueDate);
                // $date2 = new DateTime($currentDate);
                // $interval = $date1->diff($date2);
                // if($interval->days <= 3)
                // {
                //     // display pop up
                //     $showAdminPopup=1;
                //     $reportidAdminArr[]=$chkreportforadmin->report_id;
                //     $reportDuedateAdminArr[]=$chkreportforadmin->due_date;


                // }
                // else
                // {
                //     //echo "Sample";
                // }
            }

        }


        $loggedinUserid=$request->session()->get('EmployeeId');
        $empAccessDetails=ReportsUserList::whereRaw('FIND_IN_SET('.$loggedinUserid.',user_ids)')->where('status',1)->orderBy('id', 'desc')->first();

        $userAccess=0;
        if($empAccessDetails)
        {
            if($empAccessDetails->role_id==1)
            {
                $userAccess=1;
            }
            if($empAccessDetails->role_id==2)
            {
                $userAccess=2;
            }
            if($empAccessDetails->role_id==3)
            {
                $userAccess=3;
            }
        }

        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $loginEmp = $loggedinEmpDetails->employee_id;
            }            
        }


        if($showPopup==1)
        {
            $chkUploadReminderPop = ReportReminder::where('user_emp_id',$chkreport->responsibility_emp_id)->where('display_date_for',$currentDate)->orderBy('id', 'desc')->first();

            if($chkUploadReminderPop)
            {
                return response()->json(['success'=>'Already Showed for Today']);
            }
            else
            {
                $view = view('Reports/dueDateReminder', ['reportidArr' => $reportidArr, 'reportDuedateArr' => $reportDuedateArr, 'loginemp' => $loggedinEmpid, 'deptid' => $chkreport->department_id, 'userAccess' => $userAccess])->render();
                return response()->json(['view'=> $view, 'show' => 1]);
            }
        }
        else
        {
            return response()->json(['success'=>'Not Show']);
        }









        if($userAccess==3)
        {
            if($showAdminPopup==1)
            {
                $chkUploadReminderPop = ReportReminder::where('user_emp_id',$loginEmp)->where('display_date_for',$currentDate)->orderBy('id', 'desc')->first();

                if($chkUploadReminderPop)
                {
                    return response()->json(['success'=>'Already Showed for Today']);
                }
                else
                {
                    $view = view('Reports/dueDateReminder', ['reportidArr' => $reportidAdminArr, 'reportDuedateArr' => $reportDuedateAdminArr, 'userAccess' => $userAccess, 'loginemp' => $loginEmp])->render();
                    return response()->json(['view'=> $view, 'show' => 1]);
                }
            }
            else
            {
                return response()->json(['success'=>'Not Show']);
            }
        }
        else
        {
            if($showPopup==1)
            {
                $chkUploadReminderPop = ReportReminder::where('user_emp_id',$chkreport->responsibility_emp_id)->where('display_date_for',$currentDate)->orderBy('id', 'desc')->first();

                if($chkUploadReminderPop)
                {
                    return response()->json(['success'=>'Already Showed for Today']);
                }
                else
                {
                    $view = view('Reports/dueDateReminder', ['reportidArr' => $reportidArr, 'reportDuedateArr' => $reportDuedateArr, 'loginemp' => $loggedinEmpid, 'deptid' => $chkreport->department_id, 'userAccess' => $userAccess])->render();
                    return response()->json(['view'=> $view, 'show' => 1]);
                }
            }
            else
            {
                return response()->json(['success'=>'Not Show']);
            }
        }


        // if($showPopup==1)
        // {
        //     $chkUploadReminderPop = ReportReminder::where('user_emp_id',$chkreport->responsibility_emp_id)->where('display_date_for',$currentDate)->orderBy('id', 'desc')->first();

        //     if($chkUploadReminderPop)
        //     {
        //         return response()->json(['success'=>'Already Showed for Today']);
        //     }
        //     else
        //     {
        //         $view = view('Reports/dueDateReminder', ['reportidArr' => $reportidArr, 'reportDuedateArr' => $reportDuedateArr, 'loginemp' => $loggedinEmpid, 'deptid' => $chkreport->department_id])->render();
        //         return response()->json(['view'=> $view, 'show' => 1]);
        //     }
        // }
        // else
        // {
        //     return response()->json(['success'=>'Not Show']);
        // }
        
        //return view("Reports/viewReportDetails",compact('reportid','deptid','reportsData'));

    }


    public function reportDueStatusDataChk(Request $request)
    {
        //$loggedinEmpid=$request->useremp;
        $loggedinUserid=$request->session()->get('EmployeeId');

        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();
        if($loggedinEmpDetails)
        {
            $loggedinEmpid=$loggedinEmpDetails->employee_id;
        }
        
        $currentDate = date('Y-m-d');
        $reportsData = Reports::where('responsibility_emp_id',$loggedinEmpid)->orderBy('id', 'desc')->get();
        $reportsToData = Reports::where('reportingto_emp_id',$loggedinEmpid)->where('report_read_upload_status',1)->orderBy('id', 'desc')->get();


        $showPopup=0;
        $showAdminPopup=0;
        $reportidArr=array();
        $reportDuedateArr=array();
        $reportidAdminArr=array();
        $reportResponsibleArr=array();
        foreach($reportsData as $chkreport)
        {            
            $chkUploadReportsData = UploadReport::where('responsible_emp_id',$chkreport->responsibility_emp_id)->where('report_id',$chkreport->report_id)->orderBy('id', 'desc')->first();

            if($chkUploadReportsData)
            {
            }
            else
            {
                $reportDueDate = $chkreport->due_date;
                $date1 = new DateTime($reportDueDate);
                $date2 = new DateTime($currentDate);
                $interval = $date1->diff($date2);
                if($interval->days <= 3)
                {
                    // display pop up
                    $showPopup=1;
                    $reportidArr[]=$chkreport->report_id;
                    $reportDuedateArr[]=$chkreport->due_date;
                }
                else
                {
                    //echo "Sample";
                }
            }

        }



        foreach($reportsToData as $chkreport)
        {            
            $chkUploadReportsData = UploadReport::where('report_id',$chkreport->report_id)->orderBy('id', 'desc')->first();

            if($chkUploadReportsData)
            {
                $showPopup=1;
                $reportidAdminArr[] = $chkUploadReportsData->report_id;
                $reportResponsibleArr[] = $chkUploadReportsData->responsible_emp_id;

            }
            else
            {
            }

        }













        if($showPopup==1)
        {
            $chkUploadReminderPop = ReportReminder::where('user_emp_id',$loggedinEmpid)->where('display_date_for',$currentDate)->orderBy('id', 'desc')->first();

            //return $chkUploadReminderPop;

            if($chkUploadReminderPop)
            {
                return response()->json(['success'=>'Already Showed for Today']);
            }
            else
            {
                $view = view('Reports/dueDateReminder', ['reportidArr' => $reportidArr, 'reportDuedateArr' => $reportDuedateArr, 'reportidAdminArr' => $reportidAdminArr, 'reportResponsibleArr' => $reportResponsibleArr, 'loginemp' => $loggedinEmpid])->render();
                return response()->json(['view'=> $view, 'show' => 1]);
            }
        }
        else
        {
            return response()->json(['success'=>'Not Show']);
        }

        
    }




    public function submitwithLaterPostData(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
            // 'reportingEmp' => 'required',
            // 'reportingtoEmp' => 'required',
            // 'reportDept' => 'required',
            // 'reportName' => 'required',
            // 'reportingFrequency' => 'required',
            // //'amtRecoverydate' => 'required|date',
            // //'reportName' => 'required', 
            // 'reportingDays' => 'required_if:reportingFrequency,weekly',
            // 'reportingDate' => 'required_if:reportingFrequency,monthly',
            
        ],
        [
            // 'reportingEmp.required'=> 'Please Select Responsible Employee',
            // 'reportingtoEmp.required'=> 'Please Select Replorting To Employee',
            // 'reportDept.required'=> 'Please Select Department',
            // 'reportName.required'=> 'Please Select Report',
            // 'reportingFrequency.required'=> 'Please Select Reporting Frequency',
            // 'reportingDays.required_if'=> 'Please Select Day Value',
            // 'reportingDate.required_if'=> 'Please Select Date',
            // // 'reportingEmp.required'=> 'Please Select Employee',
            // // 'recoveryamt.numeric'=> 'Amount must be in Number',
            // // 'amtRecoverydate.required'=> 'Please Select recovery date',
            // // 'recoveryamt.lte'=> 'The Recovery Amount must be less than or equal to Total Remaining Amount',
                
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

            
            $addReportsList = new ReportReminder();
            $addReportsList->user_emp_id = $request->loginempid;
            $addReportsList->display_date_for = date('Y-m-d');;       
            $addReportsList->created_at = date('Y-m-d H:i:s');
            //$addReportsList->created_by = $usersessionId; 
            $addReportsList->close_status = 1;
            //$addReportsList->comments = $request->responsibilityComments;
            $addReportsList->save();


         


            return response()->json(['success'=>'I will do it Later.']);
        }
    }




    // New Admin Work


    public  function uploadedReportsIndexAdmin(Request $request)
	{
        
        
        $responsibleEmpDetails = Reports::orderBy('id', 'desc')->get();

        $newResult=array();
        foreach($responsibleEmpDetails as $value)
        {
            $newResult[]=$value->responsibility_emp_id;
        }

        $empDetailsIndex = Employee_details::whereIn('emp_id',$newResult)->orderBy('id', 'desc')->get();
        
        
        $reportLists =  ReportsList::where("status",1)->orderBy("id","DESC")->get();


        $reportingFrequencyDetails = Reports::groupBy('frequency')->orderBy('id', 'desc')->get();
        
        
        $reportDepartmentDetails = ReportsDepartment::where('status',1)->orderBy('id', 'desc')->get();

        $empsessionId=$request->session()->get('EmployeeId');
        $loggedinEmpDetails=User::where('id',$empsessionId)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $loggedinEmpReportDetails=Reports::where('responsibility_emp_id',$loggedinEmpDetails->employee_id)->orderBy('id', 'desc')->get();

                foreach($loggedinEmpReportDetails as $empReport)
                {
                    //$loggedinEmpReportDetails=UploadReport::where('responsible_emp_id',$loggedinEmpDetails->employee_id)->where('report_id')->orderBy('id', 'desc')->get();
                }
                $loginEmp = $loggedinEmpDetails->employee_id;
            }
            
        }
        
        $deptDetails=Reports::where('created_by',$empsessionId)->groupBy('department_id')->orderBy('id', 'desc')->get();

        $reportdeptarr = array();
        foreach($deptDetails as $reportdept)
        {
            $reportdeptarr[]=$reportdept->department_id;
        }


        
        
        
        
        
        //$empDetails = Employee_details::orderBy('id', 'desc')->get(); 
        //$empDetailsIndex = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        // $designationLists=Designation::where("status",1)->get();
        $moduleLists=Reportissue::groupBy('module')->orderBy('id', 'desc')->get();

        $reportsDepartmentLists=ReportsDepartment::where('status',1)->orderBy('id', 'desc')->get();

        $empsessionId=$request->session()->get('EmployeeId');



        $empAccessDetails=ReportsUserList::whereRaw('FIND_IN_SET('.$empsessionId.',user_ids)')->where('status',1)->orderBy('id', 'desc')->first();


        //return $empAccessDetails;
        $userAccess=0;
        if($empAccessDetails)
        {
            if($empAccessDetails->role_id==1)
            {
                $userAccess=1;
            }
            if($empAccessDetails->role_id==2)
            {
                $userAccess=2;
            }
        }










        $loggedinEmpDetails=User::where('id',$empsessionId)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $loggedinEmpReportDetails=Reports::where('responsibility_emp_id',$loggedinEmpDetails->employee_id)->orderBy('id', 'desc')->get();

                foreach($loggedinEmpReportDetails as $empReport)
                {
                    //$loggedinEmpReportDetails=UploadReport::where('responsible_emp_id',$loggedinEmpDetails->employee_id)->where('report_id')->orderBy('id', 'desc')->get();
                }
                $loginEmp = $loggedinEmpDetails->employee_id;
            }
            
        }		
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
        return view("Reports/manageReportResponsibilityIndex",compact('empDetailsIndex','departmentLists','moduleLists','tL_details','reportsDepartmentLists','reportLists','reportingFrequencyDetails','reportDepartmentDetails','loginEmp','userAccess','reportdeptarr'));
    }


    public function uploadedAllReportsListingAdmin(Request $request)
    {
        $whereraw = '';
        $whererawother = '';
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


        if(!empty($request->session()->get('manageexcelReports_emp_nameUpload')) && $request->session()->get('manageexcelReports_emp_nameUpload') != 'All')
        {
            $fname = $request->session()->get('manageexcelReports_emp_nameUpload');
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
                $newResult=array();
                $empDetails = Employee_details::whereIn('emp_name',$cnameArray)->orderBy('id', 'desc')->get();               
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                $newempid2 = implode(",",$newResult);

                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'responsible_emp_id IN ('.$newempid2.')';
                }
                else
                {
                    $whereraw .= ' And responsible_emp_id IN ('.$newempid2.')';
                }
            }


           
        }


        if(!empty($request->session()->get('manageexcelReportsUpload_report_name')) && $request->session()->get('manageexcelReportsUpload_report_name') != 'All')
        {
            $empId = $request->session()->get('manageexcelReportsUpload_report_name');

            if($whereraw == '')
            {
                $whereraw = 'report_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And report_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('manageexcelReports_department_name')) && $request->session()->get('manageexcelReports_department_name') != 'All')
        {
            $empId = $request->session()->get('manageexcelReports_department_name');

            if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$empId.')';
            }
        }

        


        if(!empty($request->session()->get('reported_issues_modules')) && $request->session()->get('reported_issues_modules') != 'All')
        {
            $desigid = $request->session()->get('reported_issues_modules');
            if($whereraw == '')
            {
                $whereraw = "module  IN ('".$desigid."')";
                //$whereraw = "emp_id  IN ('".$desigid."')";
            }
            else
            {
                $whereraw .= " And module  IN ('".$desigid."')";
                //$whereraw .= " emp_id  IN ('".$desigid."')";
            }
        }




        if(!empty($request->session()->get('manageexcelUploadReports_fromdate')) && $request->session()->get('manageexcelUploadReports_fromdate') != 'All')
        {
            $datefrom = $request->session()->get('manageexcelUploadReports_fromdate');
             if($whereraw == '')
            {
                $whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
            }
        }
        if(!empty($request->session()->get('manageexcelUploadReports_todate')) && $request->session()->get('manageexcelUploadReports_todate') != 'All')
        {
            $dateto = $request->session()->get('manageexcelUploadReports_todate');
             if($whereraw == '')
            {
                $whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
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

        $whereuser='';
        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $whereuser = 'responsible_emp_id IN('.$loggedinEmpDetails->employee_id.')';
            }
            else
            {
            }
        }

        $empAccessDetails=ReportsUserList::whereRaw('FIND_IN_SET('.$loggedinUserid.',user_ids)')->where('status',1)->orderBy('id', 'desc')->first();
        $userAccess=0;
        if($empAccessDetails)
        {
            if($empAccessDetails->role_id==1)
            {
                $userAccess=1;
            }
            if($empAccessDetails->role_id==2)
            {
                $userAccess=2;
            }
        }

        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
            // exit;
            if($userAccess==1) // admin Users
            {
                $requestDetails = UploadReport::whereRaw($whereraw)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = UploadReport::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }
            else // Sub Users
            {
                $requestDetails = UploadReport::whereRaw($whereuser)->whereRaw($whereraw)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = UploadReport::whereRaw($whereuser)->whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }
        }        
        else
        {
            if($userAccess==1) // admin Users
            {
                $requestDetails = UploadReport::orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = UploadReport::orderBy('id','desc')->get()->count();
            }
            else // Sub Users
            {
                $requestDetails = UploadReport::whereRaw($whereuser)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = UploadReport::whereRaw($whereuser)->orderBy('id','desc')->get()->count();
            }
        }

        //return $requestDetails;
        
        $requestDetails->setPath(config('app.url/uploadedReportsListing'));
        return view("Reports/uploadedReportsListing",compact('requestDetails','paginationValue','reportsCount'));
    }

    public function allDepartmentListingData(Request $request)
    {
        
        $deptid=$request->deptid;
        
        
        
        $whereraw = '';
        $whererawother = '';
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



        if(!empty($request->session()->get('manageexcelReports_emp_name')) && $request->session()->get('manageexcelReports_emp_name') != 'All')
        {
            $fname = $request->session()->get('manageexcelReports_emp_name');
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
                $newResult=array();
                $empDetails = Employee_details::whereIn('emp_name',$cnameArray)->orderBy('id', 'desc')->get();               
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                $newempid2 = implode(",",$newResult);

                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'responsibility_emp_id IN ('.$newempid2.')';
                }
                else
                {
                    $whereraw .= ' And responsibility_emp_id IN ('.$newempid2.')';
                }
            }


           
        }


        if(!empty($request->session()->get('manageexcelReports_report_name')) && $request->session()->get('manageexcelReports_report_name') != 'All')
        {
            $empId = $request->session()->get('manageexcelReports_report_name');

            if($whereraw == '')
            {
                $whereraw = 'report_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And report_id IN ('.$empId.')';
            }
        }





        if(!empty($request->session()->get('manageexcelReports_reporting_frequency')) && $request->session()->get('manageexcelReports_reporting_frequency') != 'All')
        {
            $fname = $request->session()->get('manageexcelReports_reporting_frequency');
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
                    $whereraw = 'frequency IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And frequency IN('.$finalcname.')';
                }
            }


           
        }



        if(!empty($request->session()->get('reported_issues_modules')) && $request->session()->get('reported_issues_modules') != 'All')
        {
            $desigid = $request->session()->get('reported_issues_modules');
            if($whereraw == '')
            {
                $whereraw = "module  IN ('".$desigid."')";
                //$whereraw = "emp_id  IN ('".$desigid."')";
            }
            else
            {
                $whereraw .= " And module  IN ('".$desigid."')";
                //$whereraw .= " emp_id  IN ('".$desigid."')";
            }
        }




        if(!empty($request->session()->get('manageexcelReports_fromdate')) && $request->session()->get('manageexcelReports_fromdate') != 'All')
        {
            $datefrom = $request->session()->get('manageexcelReports_fromdate');
            $newResult=array();
            $uploadreportDetails = UploadReport::where('created_at','>=',$datefrom.' 00:00:00')->orderBy('id', 'desc')->get();

            if (count($uploadreportDetails) === 0) 
            {
                $newResult[]=0;
            }
            else
            {
                foreach($uploadreportDetails as $value)
                {
                    $newResult[]=$value->report_id;
                }
            }
            $newid2 = implode(",",$newResult);


            if($whereraw == '')
            {
                $whereraw = 'report_id IN ('.$newid2.')';
            }
            else
            {
                $whereraw .= ' And report_id IN ('.$newid2.')';
            }
        }



        if(!empty($request->session()->get('manageexcelReports_todate')) && $request->session()->get('manageexcelReports_todate') != 'All')
        {
            $dateto = $request->session()->get('manageexcelReports_todate');
            $newResult=array();
            $uploadreportDetails = UploadReport::where('created_at','<=',$dateto.' 00:00:00')->orderBy('id', 'desc')->get();

            if (count($uploadreportDetails) === 0) 
            {
                $newResult[]=0;
            }
            else
            {
                foreach($uploadreportDetails as $value)
                {
                    $newResult[]=$value->report_id;
                }
            }
            $newid2 = implode(",",$newResult);


            if($whereraw == '')
            {
                $whereraw = 'report_id IN ('.$newid2.')';
            }
            else
            {
                $whereraw .= ' And report_id IN ('.$newid2.')';
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


        $whereuser='';
        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            
                $whereuser = 'created_by IN('.$loggedinUserid.')';
           
        }


        $empAccessDetails=ReportsUserList::whereRaw('FIND_IN_SET('.$loggedinUserid.',user_ids)')->where('status',1)->orderBy('id', 'desc')->first();
        $userAccess=0;
        if($empAccessDetails)
        {
            if($empAccessDetails->role_id==1)
            {
                $userAccess=1;
            }
            if($empAccessDetails->role_id==2)
            {
                $userAccess=2;
            }
        }


        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
            // exit;
            
                

                if($userAccess==1) // admin Users
                {
                    $requestDetails = Reports::where('department_id',$deptid)->whereRaw($whereraw)->orderBy('id', 'desc')->paginate($paginationValue);
                    $reportsCount = Reports::where('department_id',$deptid)->whereRaw($whereraw)->orderBy('id','desc')->get()->count();
                }
                else // Sub Users
                {
                    $requestDetails = Reports::where('department_id',$deptid)->whereRaw($whereraw)->whereRaw($whereuser)->orderBy('id', 'desc')->paginate($paginationValue);
                    $reportsCount = Reports::where('department_id',$deptid)->whereRaw($whereraw)->whereRaw($whereuser)->orderBy('id','desc')->get()->count();
                }
            
            
        }        
        else
        {
            
                


                if($userAccess==1) // admin Users
                {
                    $requestDetails = Reports::where('department_id',$deptid)->orderBy('id', 'desc')->paginate($paginationValue);
                    $reportsCount = Reports::where('department_id',$deptid)->orderBy('id','desc')->get()->count();
                }
                else // Sub Users
                {
                    $requestDetails = Reports::where('department_id',$deptid)->whereRaw($whereuser)->orderBy('id', 'desc')->paginate($paginationValue);
                    $reportsCount = Reports::where('department_id',$deptid)->whereRaw($whereuser)->orderBy('id','desc')->get()->count();
                }
            
        }

        //return $requestDetails;
        
        $requestDetails->setPath(config('app.url/listingDepartmentReports'));
        return view("Reports/listingDepartmentReports",compact('requestDetails','paginationValue','reportsCount','userAccess'));
    }

    public function allReportsListingAdminData(Request $request)
    {
        $whereraw = '';
        $whererawother = '';
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


        if(!empty($request->session()->get('manageexcelReports_emp_name')) && $request->session()->get('manageexcelReports_emp_name') != 'All')
        {
            $fname = $request->session()->get('manageexcelReports_emp_name');
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
                $newResult=array();
                $empDetails = Employee_details::whereIn('emp_name',$cnameArray)->orderBy('id', 'desc')->get();               
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                $newempid2 = implode(",",$newResult);

                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'responsibility_emp_id IN ('.$newempid2.')';
                }
                else
                {
                    $whereraw .= ' And responsibility_emp_id IN ('.$newempid2.')';
                }
            }


           
        }


        if(!empty($request->session()->get('manageexcelReports_report_name')) && $request->session()->get('manageexcelReports_report_name') != 'All')
        {
            $empId = $request->session()->get('manageexcelReports_report_name');

            if($whereraw == '')
            {
                $whereraw = 'report_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And report_id IN ('.$empId.')';
            }
        }





        if(!empty($request->session()->get('manageexcelReports_reporting_frequency')) && $request->session()->get('manageexcelReports_reporting_frequency') != 'All')
        {
            $fname = $request->session()->get('manageexcelReports_reporting_frequency');
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
                    $whereraw = 'frequency IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And frequency IN('.$finalcname.')';
                }
            }


           
        }



        if(!empty($request->session()->get('reportIssues_dept')) && $request->session()->get('reportIssues_dept') != 'All')
        {
            $deptid = $request->session()->get('reportIssues_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('reported_issues_modules')) && $request->session()->get('reported_issues_modules') != 'All')
        {
            $desigid = $request->session()->get('reported_issues_modules');
            if($whereraw == '')
            {
                $whereraw = "module  IN ('".$desigid."')";
                //$whereraw = "emp_id  IN ('".$desigid."')";
            }
            else
            {
                $whereraw .= " And module  IN ('".$desigid."')";
                //$whereraw .= " emp_id  IN ('".$desigid."')";
            }
        }




        if(!empty($request->session()->get('manageexcelReports_fromdate')) && $request->session()->get('manageexcelReports_fromdate') != 'All')
        {
            $datefrom = $request->session()->get('manageexcelReports_fromdate');
             if($whereraw == '')
            {
                $whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
            }
        }
        if(!empty($request->session()->get('manageexcelReports_todate')) && $request->session()->get('manageexcelReports_todate') != 'All')
        {
            $dateto = $request->session()->get('manageexcelReports_todate');
             if($whereraw == '')
            {
                $whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
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

        $whereuser='';
        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            $whereuser = 'created_by IN('.$loggedinUserid.')';
        }

        $empAccessDetails=ReportsUserList::whereRaw('FIND_IN_SET('.$loggedinUserid.',user_ids)')->where('status',1)->orderBy('id', 'desc')->first();
        $userAccess=0;
        if($empAccessDetails)
        {
            if($empAccessDetails->role_id==1)
            {
                $userAccess=1;
            }
            if($empAccessDetails->role_id==2)
            {
                $userAccess=2;
            }
        }



        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
            // exit;

            if($userAccess==1) // admin Users
            {
                $requestDetails = Reports::whereRaw($whereraw)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = Reports::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }
            else // Sub Users
            {
                $requestDetails = Reports::whereRaw($whereraw)->whereRaw($whereuser)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = Reports::whereRaw($whereraw)->whereRaw($whereuser)->orderBy('id','desc')->get()->count();
            }
            
                
           
        }        
        else
        {
            if($userAccess==1) // admin Users
            {
                $requestDetails = Reports::orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = Reports::orderBy('id','desc')->get()->count();
            }
            else // Sub Users
            {
                $requestDetails = Reports::whereRaw($whereuser)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = Reports::whereRaw($whereuser)->orderBy('id','desc')->get()->count();
            }
            
        }

        //return $requestDetails;
        
        $requestDetails->setPath(config('app.url/listingAllAdmin'));
        return view("Reports/listingAllAdmin",compact('requestDetails','paginationValue','reportsCount'));
    }



    public  function viewReportsIndexAdmin(Request $request)
	{
        
        
        $responsibleEmpDetails = Reports::orderBy('id', 'desc')->get();

        $newResult=array();
        foreach($responsibleEmpDetails as $value)
        {
            $newResult[]=$value->responsibility_emp_id;
        }

        $empDetailsIndex = Employee_details::whereIn('emp_id',$newResult)->orderBy('id', 'desc')->get();
        
        
        $reportLists =  ReportsList::where("status",1)->orderBy("id","DESC")->get();


        $reportingFrequencyDetails = Reports::groupBy('frequency')->orderBy('id', 'desc')->get();
        
        
        $reportDepartmentDetails = ReportsDepartment::where('status',1)->orderBy('id', 'desc')->get();

        
        
        
        
        
        
        
        //$empDetails = Employee_details::orderBy('id', 'desc')->get(); 
        //$empDetailsIndex = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        // $designationLists=Designation::where("status",1)->get();
        $moduleLists=Reportissue::groupBy('module')->orderBy('id', 'desc')->get();

        $reportsDepartmentLists=ReportsDepartment::where('status',1)->orderBy('id', 'desc')->get();

        $empsessionId=$request->session()->get('EmployeeId');



        $empAccessDetails=ReportsUserList::whereRaw('FIND_IN_SET('.$empsessionId.',user_ids)')->where('status',1)->orderBy('id', 'desc')->first();


        //return $empAccessDetails;
        $userAccess=0;
        if($empAccessDetails)
        {
            if($empAccessDetails->role_id==1)
            {
                $userAccess=1;
            }
            if($empAccessDetails->role_id==2)
            {
                $userAccess=2;
            }
        }










        $loggedinEmpDetails=User::where('id',$empsessionId)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $loggedinEmpReportDetails=Reports::where('responsibility_emp_id',$loggedinEmpDetails->employee_id)->orderBy('id', 'desc')->get();

                foreach($loggedinEmpReportDetails as $empReport)
                {
                    //$loggedinEmpReportDetails=UploadReport::where('responsible_emp_id',$loggedinEmpDetails->employee_id)->where('report_id')->orderBy('id', 'desc')->get();
                }
                $loginEmp = $loggedinEmpDetails->employee_id;
            }
            
        }		
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
        return view("Reports/viewUploadedreportsIndex",compact('empDetailsIndex','departmentLists','moduleLists','tL_details','reportsDepartmentLists','reportLists','reportingFrequencyDetails','reportDepartmentDetails','loginEmp','userAccess'));
    }


    public function viewAllUploadreportsData(Request $request)
    {
        $whereraw = '';
        $whererawother = '';
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


        if(!empty($request->session()->get('manageexcelReports_emp_nameUpload')) && $request->session()->get('manageexcelReports_emp_nameUpload') != 'All')
        {
            $fname = $request->session()->get('manageexcelReports_emp_nameUpload');
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
                $newResult=array();
                $empDetails = Employee_details::whereIn('emp_name',$cnameArray)->orderBy('id', 'desc')->get();               
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                $newempid2 = implode(",",$newResult);

                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'responsible_emp_id IN ('.$newempid2.')';
                }
                else
                {
                    $whereraw .= ' And responsible_emp_id IN ('.$newempid2.')';
                }
            }


           
        }


        if(!empty($request->session()->get('manageexcelReportsUpload_report_name')) && $request->session()->get('manageexcelReportsUpload_report_name') != 'All')
        {
            $empId = $request->session()->get('manageexcelReportsUpload_report_name');

            if($whereraw == '')
            {
                $whereraw = 'report_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And report_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('manageexcelReports_department_name')) && $request->session()->get('manageexcelReports_department_name') != 'All')
        {
            $empId = $request->session()->get('manageexcelReports_department_name');

            if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$empId.')';
            }
        }

        


        if(!empty($request->session()->get('reported_issues_modules')) && $request->session()->get('reported_issues_modules') != 'All')
        {
            $desigid = $request->session()->get('reported_issues_modules');
            if($whereraw == '')
            {
                $whereraw = "module  IN ('".$desigid."')";
                //$whereraw = "emp_id  IN ('".$desigid."')";
            }
            else
            {
                $whereraw .= " And module  IN ('".$desigid."')";
                //$whereraw .= " emp_id  IN ('".$desigid."')";
            }
        }




        if(!empty($request->session()->get('manageexcelUploadReports_fromdate')) && $request->session()->get('manageexcelUploadReports_fromdate') != 'All')
        {
            $datefrom = $request->session()->get('manageexcelUploadReports_fromdate');
             if($whereraw == '')
            {
                $whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
            }
        }
        if(!empty($request->session()->get('manageexcelUploadReports_todate')) && $request->session()->get('manageexcelUploadReports_todate') != 'All')
        {
            $dateto = $request->session()->get('manageexcelUploadReports_todate');
             if($whereraw == '')
            {
                $whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
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

        $whereuser='';
        $loggedinEmpDetails=User::where('id',$loggedinUserid)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            
                $whereuser = 'reportingto_emp_id IN('.$loggedinEmpDetails->employee_id.')';
            
        }

        $empAccessDetails=ReportsUserList::whereRaw('FIND_IN_SET('.$loggedinUserid.',user_ids)')->where('status',1)->orderBy('id', 'desc')->first();
        $userAccess=0;
        if($empAccessDetails)
        {
            if($empAccessDetails->role_id==1)
            {
                $userAccess=1;
            }
            if($empAccessDetails->role_id==2)
            {
                $userAccess=2;
            }
        }

        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
            // exit;
            if($userAccess==1) // admin Users
            {
                $requestDetails = UploadReport::whereRaw($whereraw)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = UploadReport::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }
            else // Sub Users
            {
                $requestDetails = UploadReport::whereRaw($whereuser)->whereRaw($whereraw)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = UploadReport::whereRaw($whereuser)->whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }
        }        
        else
        {
            if($userAccess==1) // admin Users
            {
                $requestDetails = UploadReport::orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = UploadReport::orderBy('id','desc')->get()->count();
            }
            else // Sub Users
            {
                $requestDetails = UploadReport::whereRaw($whereuser)->orderBy('id', 'desc')->paginate($paginationValue);
                $reportsCount = UploadReport::whereRaw($whereuser)->orderBy('id','desc')->get()->count();
            }
        }

        //return $requestDetails;
        
        $requestDetails->setPath(config('app.url/uploadedReportsListingView'));
        return view("Reports/uploadedReportsListingView",compact('requestDetails','paginationValue','reportsCount'));
    }

    public static function chkReportingTo($reportid,$userid)
    {
        //return $userid;
       // $empsessionId=$request->session()->get('EmployeeId');
        $loggedinEmpDetails=User::where('id',$userid)->orderBy('id', 'desc')->first();

        if($loggedinEmpDetails)
        {
            if($loggedinEmpDetails->employee_id != NULL || $loggedinEmpDetails->employee_id != '')
            {
                $loginEmp = $loggedinEmpDetails->employee_id;
            }
            
        }
        // return 3;
        
        $reportDetailsto=Reports::where('report_id',$reportid)->where('reportingto_emp_id',$loginEmp)->orderBy('id', 'desc')->first();

        if($reportDetailsto)
        {
            return 1;
        }
        else
        {
            return 2;
        }

    }

}