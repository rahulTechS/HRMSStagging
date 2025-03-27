<?php

namespace App\Http\Controllers\StampingDeadline;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use App\Models\Company\Department;
use App\Models\Company\Product;
use App\Models\Recruiter\Designation;
use App\Models\Offerletter\SalaryBreakup;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionAttributes;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use App\Models\Onboarding\KycDocuments;
use App\Models\Onboarding\HiringSourceDetails;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Onboarding\VisaDetails;
use App\Models\Onboarding\DocumentCollectionBackout;
use App\Models\Onboarding\DocumentVisaStageStatus;
use App\Models\Onboarding\IncentiveLetterDetails;
use Illuminate\Support\Facades\Validator;
use  App\Models\Attribute\AttributeType;
use App\Models\Offerletter\OfferletterDetails;
use App\Models\Visa\visaType;
use App\Models\Visa\VisaStage;
use App\Models\Visa\Visaprocess;
use App\Models\Onboarding\TrainingProcess;
use UserPermissionAuth;
use App\Models\Entry\Employee;
use App\Models\Employee\Employee_details;
use App\Models\Job\JobOpening;
use App\Models\Employee\Employee_attribute;
use  App\Models\Attribute\Attributes;
use App\Models\Logs\DocumentCollectionDetailsLog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Onboarding\DepartmentPermission;
use App\Models\MIS\WpCountries;
use App\Models\Onboarding\OnboardCandidateKyc;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Onboarding\SpecialCommentLog;
use App\Models\Question\Question;
use App\Models\Onboarding\OnboardFeedBack;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\EmpProcess\JobFunctionPermission;

class StampingDeadlineController extends Controller
{
    public function StampingDeadline()
	{
	
	return view("StampingDeadline/StampingDeadline");
	}
	public function StampingDeadlineCalRenderTab(Request $request)
	{
	 $monthSelected = $request->m;
	 $yearSelected = $request->y;
	 return view("StampingDeadline/StampingDeadlineCalRenderTab",compact('monthSelected','yearSelected'));
	 
	}
	
       public function documentcollection(Request $req)
	   {
		  
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
		$visastagestatuslist=DocumentVisaStageStatus::get();
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$jobOpning=JobOpening::where("status",1)->get();
		$documentCollectiondetailsforDepartment = DocumentCollectionDetails::orderBy("id","DESC")->get();
		$departmentIdArray = array();
		foreach($documentCollectiondetailsforDepartment as $_dpart)
		{
			$departmentIdArray[$_dpart->department] = Department::where("id",$_dpart->department)->first()->department_name;
		}
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
				/*
				*consultancy Code
				*/
				$r_id = 0;
				$empsessionIdGet=$req->session()->get('EmployeeId');
				$jobfunctiondetails = JobFunctionPermission::where("user_id",$empsessionIdGet)->first();
				 //echo $jobfunctiondetails->job_function_id;exit;
				 if($jobfunctiondetails!='' && ($jobfunctiondetails->job_function_id==3)){
					 $dept=Employee_details::where("emp_id",$jobfunctiondetails->emp_id)->first();
					 if($dept!=''){
						$req->session()->put('salesdept_emp_filter_inner_list',$dept->dept_id);
						//$req->session()->put('company_RecruiterName_filter_inner_list',$recuterdata->id);
					 }
					 else{
						 $req->session()->put('salesdept_emp_filter_inner_list',''); 
					 }
				 }
				 else{
					 $req->session()->put('salesdept_emp_filter_inner_list','');
				 }
				$empDataGetting = Employee::where("id",$empsessionIdGet)->first();
				if($empDataGetting != '')
				{
				
					if($empDataGetting->group_id == 22)
					{
						if($empDataGetting->r_id != '' && $empDataGetting->r_id != NULL)
						{
						$r_id = $empDataGetting->r_id;
						$req->session()->put('company_RecruiterName_filter_inner_list',$r_id);
						}
					}
				}
				/*
				*consultancy Code
				*/
				
				$documentColectionId = DocumentCollectionDetails::whereIn("visa_process_status",array(2,4))->where("onboard_status",2)->get();
				$visastagearray=array();
				foreach($documentColectionId as $doc){
					$visastageId = Visaprocess::where("document_id",$doc->id)->orderBy('id','DESC')->first();
					if($visastageId!=''){
					$stage=VisaStage::where("id",$visastageId->visa_stage)->orderBy('id','DESC')->first();
					if($stage!=''){
						$visastagearray[$visastageId->visa_stage]=$stage->stage_name;
					}
					
					}
					
				}
				$EmpName = DocumentCollectionDetails::groupBy('emp_name')->selectRaw('count(*) as emp_name, emp_name')->get();
				$recdata=RecruiterCategory::get();
				//print_r($EmpName);exit;
		return view("OnboardingAjax/documentcollectionajax",compact("recdata",'EmpName','visastagearray','r_id','visastagestatuslist','jobOpning','jobRecruiterDetails','departmentLists','productDetails','designationDetails','filterList','salaryBreakUpdetails','departmentIdArray'));
	   }
	   public static function getDeadlineValue($data){
		 $Documentdata = DocumentCollectionDetailsValues::where("attribute_value",$data)->where("attribute_code",91)->count(); 
			return $Documentdata;
			
		   
	   }
	   public static function getStampingDeadlineValue($data){
		 $Documentdataval = DocumentCollectionDetailsValues::where("attribute_value",$data)->where("attribute_code",92)->count(); 
			return $Documentdataval;
			
		   
	   }
	   public function getAllMonthStampingDeadlineData($date){
		 $StampingDeadline = DocumentCollectionDetailsValues::where("attribute_value",$date)->where("attribute_code",92)->get();
		 $ChangeStatusDeadline = DocumentCollectionDetailsValues::where("attribute_value",$date)->where("attribute_code",91)->get();
		return view("StampingDeadline/allMonthFileLog",compact("StampingDeadline",'ChangeStatusDeadline'));		 
	   }
	   public static function getStampingDeadlineEmpName($id){
		   
	   if($id != NULL)
	   {
			return DocumentCollectionDetails::where("id",$id)->first()->emp_name;
	   }
	   else
	   {
			return "--";
	   }
		   
	   }
	   public static function getChangeStatusDeadlineEmpName($id){
		   
	   if($id != NULL)
	   {
			return DocumentCollectionDetails::where("id",$id)->first()->emp_name;
	   }
	   else
	   {
			return "--";
	   }
		   
	   }
	   

}
