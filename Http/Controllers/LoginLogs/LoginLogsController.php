<?php

namespace App\Http\Controllers\LoginLogs;

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
use App\Models\Entry\LoginLog;
use App\Models\JobFunction\JobFunction;

class LoginLogsController extends Controller
{
    
       public function LoginLogs(Request $req)
	   {
		  
		$EmpName=Employee::where("status",1)->get();
				
		return view("LoginLogs/LoginLogs",compact('EmpName'));
	   }
	   
	   public function AllLogsData(Request $request)
	   {
		    $whereraw = '';
			$whereraw1 = '';
		
 
			if(!empty($request->session()->get('login_page_limit')))
				{
					$paginationValue = $request->session()->get('login_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if(!empty($request->session()->get('cname_login_filter_inner_list')) && $request->session()->get('cname_login_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_login_filter_inner_list');
					
					 if($whereraw == '')
					{
						$whereraw = 'user_id IN('.$cname.')';
					}
					else
					{
						$whereraw .= ' And user_id IN('.$cname.')';
					}
				}
				if(!empty($request->session()->get('empid_login_filter_inner_list')) && $request->session()->get('empid_login_filter_inner_list') != 'All')
				{
					$empid = $request->session()->get('empid_login_filter_inner_list');
					
					 if($whereraw == '')
					{
						$whereraw = 'user_id IN('.$empid.')';
					}
					else
					{
						$whereraw .= ' And user_id IN('.$empid.')';
					}
				}
				if(!empty($request->session()->get('datefrom_login_filter_inner_list')) && $request->session()->get('datefrom_login_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_login_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_login_filter_inner_list')) && $request->session()->get('dateto_login_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_login_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				//echo $whereraw;
				if($whereraw != '')
						{
							$loginlog = LoginLog::whereRaw($whereraw)->orderBy("id", "DESC")->paginate($paginationValue);
					
							$reportsCount = LoginLog::whereRaw($whereraw)->get()->count();
						}
						else
						{
							$loginlog = LoginLog::orderBy("id", "DESC")->paginate($paginationValue);
					
							$reportsCount = LoginLog::get()->count();
						}
				//$loginlog = LoginLog::paginate($paginationValue);
					
	
				$loginlog->setPath(config('app.url/AllLogsData'));
				
		
		
		
		return view("LoginLogs/LoginLogList",compact('loginlog','paginationValue','reportsCount'));
	   }
	   
	  public function setOffSetForLoginLogs(Request $request)
	   {
		   $offset = $request->offset;
		   
		  $request->session()->put('login_page_limit',$offset);
	   } 
	   
	 public static function getUserName($id)
	   {
		  
		   return Employee::where("id",$id)->first()->fullname;
	   } 
public static function getUserID($id)
	   {
		  
		   return Employee::where("id",$id)->first()->employee_id;
	   } 

public function LoginLogbyfilter(Request $request)
		{
			$namearray = $request->input('candidatename');
			//print_r($namearray);
			if($request->input('candidatename') != '')
			{
				$namearray  = array_filter($namearray);
			}
			if($namearray!=''){
			$name=implode(",", $namearray);
			}
			else{
				$name='';
			}
			$empIdarray = $request->input('empId');
			//print_r($namearray);
			if($request->input('empId') != '')
			{
				$empIdarray  = array_filter($empIdarray);
			}
			if($empIdarray!=''){
			$empid=implode(",", $empIdarray);
			}
			else{
				$empid='';
			}
			
			
			$dateto = $request->dateto;
			$datefrom = $request->datefrom;
			
			//echo $RecruiterName;exit;
			$request->session()->put('cname_login_filter_inner_list',$name);
			$request->session()->put('empid_login_filter_inner_list',$empid);			
			$request->session()->put('dateto_login_filter_inner_list',$dateto);
			$request->session()->put('datefrom_login_filter_inner_list',$datefrom);
			
			
		}
		public function LoginLogresetfilter(Request $request)
		{
			
			$request->session()->put('cname_login_filter_inner_list','');
			$request->session()->put('empid_login_filter_inner_list','');			
			$request->session()->put('dateto_login_filter_inner_list','');
			$request->session()->put('datefrom_login_filter_inner_list','');
			
			
			
			
		}
public static function getJobFunction($uid){
	
		$empid=Employee::where("id",$uid)->first();
		if($empid!=''){
			$empdetails=Employee_details::where("emp_id",$empid->employee_id)->first();
			if($empdetails!=''){
				$departmentDetails = JobFunction::where("id",$empdetails->job_function)->first();
			   if($departmentDetails != '')
			   {
					return $departmentDetails->name;
			   }
			   else
			   {
				   return '';
			   }
			}  
		}
	}
public static function getDesignation($uid){
			$empid=Employee::where("id",$uid)->first();
			if($empid!=''){
				$empdetails=Employee_details::where("emp_id",$empid->employee_id)->first();
				if($empdetails!=''){
					$designationMod = Designation::where("id",$empdetails->designation_by_doc_collection)->first();
					if($designationMod != '')
					  {
					  
					  return $designationMod->name;
					  }
					  else{
						 return ''; 
					  }
				}	  
			}  
		}
public static function getDepartmentID($uid){
			$empid=Employee::where("id",$uid)->first();
			if($empid!=''){
				$empdetails=Employee_details::where("emp_id",$empid->employee_id)->first();
				if($empdetails!=''){
					$designationMod = Department::where("id",$empdetails->dept_id)->first();
					if($designationMod != '')
					  {
					  
					  return $designationMod->department_name;
					  }
					  else{
						 return ''; 
					  }
				}	  
			}  
		}		
}
