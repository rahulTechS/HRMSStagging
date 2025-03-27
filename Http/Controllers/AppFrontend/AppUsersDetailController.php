<?php
namespace App\Http\Controllers\AppFrontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Employee\EmpAppAccess;
use App\Models\Company\Subsidiary;
use App\Models\Company\Divison;
use App\Models\Company\Department;
use  App\Models\Attribute\Attributes;
use App\Models\Employee\Employee_attribute;
use App\Models\EmpProcess\Emp_joining_data;
use App\Models\EmpOffline\EmpOffline;
use App\Models\Employee\Employee_details;
use App\Models\Employee\EmployeeImportFiles;
use App\Models\Employee\EmployeeAttendanceModel;
use App\Models\Payroll\AnnualLeaveDetails;
use App\Models\Payroll\AnnualLeave;
use App\Models\MIS\WpCountries;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionAttributes;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use App\Models\Recruiter\Designation;
use App\Models\Job\JobOpening;
use Session;
use App\Models\EmpProcess\EmpChangeLog;
use App\Models\Entry\Employee;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\EmpProcess\JobFunctionPermission;
use App\Models\JobFunction\JobFunction;
use App\Models\Employee\ExportDataLog;
use App\Models\SEPayout\SalaryStruture;
use App\Models\Visa\Visaprocess;
use App\Models\Visa\visaType;
use App\User;
use App\Models\EmpProcess\ExportEmployeeStatus;








use Codedge\Fpdf\Fpdf\Fpdf;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;

use App\Models\SIF\SifTemplateDetails;
use App\Models\SIF\RandomPadddingSif;


use App\Models\WarningLetter\WarningLetterRequest;






class AppUsersDetailController extends Controller
{
    
  
		public function AppUsersDetail(Request $request)
		{			
			$Designation=Designation::where("status",1)->get();
			$dept=Department::where("status",1)->get();
			$empId=Employee_details::whereNotIn('emp_id', array(102392,102547))->get();
			$EmpName=Employee_details::whereNotIn('emp_id', array(102392,102547))->get();
			$empsessionIdGet=$request->session()->get('EmployeeId');
			$empDataGetting = Employee::where("id",$empsessionIdGet)->first();
			$design='';
			if($empDataGetting!='' && $empDataGetting->	employee_id!=''){
				$empid=Employee_details::where("emp_id",$empDataGetting->	employee_id)->first();
				if($empid!=''){
					$design=$empid->dept_id;
				}
			}
			$jobfun=JobFunction::where("status",1)->get();
			$recdata=RecruiterDetails::where("status",1)->get();
			$Designationdata=Designation::where("tlsm",2)->where("status",1)->get();
				$designarray=array();
				foreach($Designationdata as $_design){
					$designarray[]=$_design->id;
				}
				 $EmpTLlist = Employee_details::whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->orderBy("id","ASC")->get();
			//$EmpTLlist=Employee_details::where('job_function',3)->get();
			return view("AppFrontend/AppUsersDetail",compact('Designation','empId','EmpName','design','jobfun','recdata','EmpTLlist','dept'));
			
		}
		public function AjaxAppEmpList(Request $request)
		{		
			$paginationValue=10;
			$empdetails=EmpAppAccess::orderByRaw("fullname ASC")->paginate($paginationValue);
			$dept=Department::where("status",1)->get();
			$empId=Employee_details::whereNotIn('emp_id', array(102392,102547))->get();
			$EmpName=Employee_details::whereNotIn('emp_id', array(102392,102547))->get();
			$empsessionIdGet=$request->session()->get('EmployeeId');
			$empDataGetting = Employee::where("id",$empsessionIdGet)->first();
			$design='';
			if($empDataGetting!='' && $empDataGetting->	employee_id!=''){
				$empid=Employee_details::where("emp_id",$empDataGetting->	employee_id)->first();
				if($empid!=''){
					$design=$empid->dept_id;
				}
			}
			$jobfun=JobFunction::where("status",1)->get();
			$recdata=RecruiterDetails::where("status",1)->get();
			$Designationdata=Designation::where("tlsm",2)->where("status",1)->get();
				$designarray=array();
				foreach($Designationdata as $_design){
					$designarray[]=$_design->id;
				}
				// $EmpTLlist = Employee_details::whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->orderBy("id","ASC")->get();
			//$EmpTLlist=Employee_details::where('job_function',3)->get();
			return view("AppFrontend/AjaxAppEmpList",compact('empdetails','empId','EmpName','design','jobfun','recdata','dept','paginationValue'));
			
		}
		public function AjaxAppEmpListDept(Request $request)
		{		
			$depart_id = $request->id;
			$paginationValue=10;
			$empdetails=EmpAppAccess::where("dept_id",$depart_id)->orderByRaw("fullname ASC")->paginate($paginationValue);
			$dept=Department::where("status",1)->get();
			$empId=Employee_details::whereNotIn('emp_id', array(102392,102547))->get();
			$EmpName=Employee_details::whereNotIn('emp_id', array(102392,102547))->get();
			$empsessionIdGet=$request->session()->get('EmployeeId');
			$empDataGetting = Employee::where("id",$empsessionIdGet)->first();
			$design='';
			if($empDataGetting!='' && $empDataGetting->	employee_id!=''){
				$empid=Employee_details::where("emp_id",$empDataGetting->	employee_id)->first();
				if($empid!=''){
					$design=$empid->dept_id;
				}
			}
			$jobfun=JobFunction::where("status",1)->get();
			$recdata=RecruiterDetails::where("status",1)->get();
			$Designationdata=Designation::where("tlsm",2)->where("status",1)->get();
				$designarray=array();
				foreach($Designationdata as $_design){
					$designarray[]=$_design->id;
				}
				// $EmpTLlist = Employee_details::whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->orderBy("id","ASC")->get();
			//$EmpTLlist=Employee_details::where('job_function',3)->get();
			return view("AppFrontend/AjaxAppEmpList",compact('empdetails','empId','EmpName','design','jobfun','recdata','dept','paginationValue'));
			
		}
		public static function getdesignation($designtaionID)
		{		
			$designtaion=Designation::where("id",$designtaionID)->first();
			if($designtaion!= ''){
				return $designtaion->name;
			}else{
				return '';
			}
			
		}
		
		// New Code End 16-05-2024
}
