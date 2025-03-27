<?php

namespace App\Http\Controllers\EmpOfflineProcess;

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
use App\Models\EmpOffline\EmpOffline;
use App\Models\EmpOffline\QuestionForLeaving;
use App\Models\Question\Question;
use App\Models\SettelementAttribute\SettelementAttribute;
use App\Models\CompanyAssets\CompanyAssets;
use App\Models\SettelementCheckList\SettelementCheckList;
use App\Models\EmpOffline\SettelementAttributes;
use App\Models\ReasonsForLeaving\ReasonsForLeaving;
use App\Models\EmpOffline\OffboardEMPData;
use App\Models\EmpOffline\SettelementLogs;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\EmpOffline\CancelationVisaProcess;
use App\Models\EmpProcess\JobFunctionPermission;
use App\Models\JobFunction\JobFunction;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Dashboard\MasterPayout;
use App\Models\SEPayout\RangeDetailsVintage;
use App\Models\Finance\VisaExpensesDetailsUpdated;
use App\Models\Employee\ExportDataLog;

use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Common\MashreqLoginMIS;
use  App\Models\Common\MashreqBookingMIS;

class EmpOfflineProcessController extends Controller
{
    
       public function EmpOffBoardProcess(Request $req)
	   {
		$ReasonsForLeavingDetails = ReasonsForLeaving::where("status",1)->get();
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();
		$empId=EmpOffline::get();
		
		$Designation=Designation::where("status",1)->get();
		return view("EmpOfflineProcess/EmpOfflineProcess",compact('ReasonsForLeavingDetails','departmentLists','tL_details','empId','Designation'));
	   }
	   
	   public function listingEmpOfflineProcessConditionforLeaving(Request $request)
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
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				if(!empty($request->session()->get('leaving_offboardtype_filter_inner_list')) && $request->session()->get('leaving_offboardtype_filter_inner_list') != 'All')
				{
					$type = $request->session()->get('leaving_offboardtype_filter_inner_list');
					
					
					 if($whereraw == '')
					{
						$whereraw = 'leaving_type = "'.$type.'"';
					}
					else
					{
						$whereraw .= ' And leaving_type = "'.$type.'"';
					}
				}
				
				//echo $whereraw;exit;
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				//$request->session()->put('cname_emp_filter_inner_list','');
				
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('email_cand_filter_inner_list')) && $request->session()->get('email_cand_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_cand_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('leaving_datefrom_offboard_lastworkingday_list')) && $request->session()->get('leaving_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('leaving_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('leaving_dateto_offboard_lastworkingday_list')) && $request->session()->get('leaving_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('leaving_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('leaving_datefrom_offboard_dort_list')) && $request->session()->get('leaving_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('leaving_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('leaving_dateto_offboard_dort_list')) && $request->session()->get('leaving_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('leaving_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
			if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
				if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}
				
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_cand_filter_inner_list')) && $request->session()->get('status_cand_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_cand_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_cand_filter_inner_list')) && $request->session()->get('vintage_cand_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_cand_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}	
				//echo $whereraw;
				$todayDate = date('Y-m-d');
					
					
				
				if($whereraw != '')
				{
					//echo $whereraw;
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->where("condition_leaving",1)->where("retain",2)->where("exit_interview_approved",2)->where("fnf_approved",2)->whereNull("list_remove")->orderBy("created_at","DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw)->where("condition_leaving",1)->where("retain",2)->where("exit_interview_approved",2)->where("fnf_approved",2)->whereNull("list_remove")->get()->count();
					
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = EmpOffline::where("condition_leaving",1)->where("retain",2)->where("exit_interview_approved",2)->where("fnf_approved",2)->whereNull("list_remove")->orderBy("created_at","DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::where("condition_leaving",1)->where("retain",2)->where("exit_interview_approved",2)->where("fnf_approved",2)->whereNull("list_remove")->get()->count();
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$documentCollectiondetails->setPath(config('app.url/listingEmpOfflineProcessConditionforLeaving'));
				
		
		
		 
		return view("EmpOfflineProcess/listingEmpOfflineProcessConditionforLeaving",compact('departmentLists','productDetails','paginationValue','designationDetails','documentCollectiondetails','reportsCount'));
	   }
	   
	   
	   
	    public function listingEmpOfflineProcessAll(Request $request)
	   {
		   //$request->session()->put('company_RecruiterNameAll_filter_inner_list','');
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
			
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		//$request->session()->put('cname_empAll_filter_inner_list','');
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  //$whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 100;
				}
				
				
				if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
				{
					$retained = $request->session()->get('offboardall_retained_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'retain = "'.$retained.'"';
					}
					else
					{
						$whereraw .= ' And retain = "'.$retained.'"';
					}
				}
				
				if(!empty($request->session()->get('offboardall_LWD_filter_inner_list')) && $request->session()->get('offboardall_LWD_filter_inner_list') != 'All')
				{
					$lwdate = $request->session()->get('offboardall_LWD_filter_inner_list');
					if($lwdate==2){
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign IS NULL';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign IS NULL';
					}
					}
					else if($lwdate==1){
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign IS NOT NULL';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign IS NOT NULL';
						}
					}
				}
				
				
				
				
				if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
				{
					$exittype = $request->session()->get('offboardall_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'leaving_type = "'.$exittype.'"';
					}
					else
					{
						$whereraw .= ' And leaving_type = "'.$exittype.'"';
					}
				}
				if(!empty($request->session()->get('all_datefrom_offboard_lastworkingday_list')) && $request->session()->get('all_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('all_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('all_dateto_offboard_lastworkingday_list')) && $request->session()->get('all_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('all_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('all_datefrom_offboard_dort_list')) && $request->session()->get('all_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('all_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('all_dateto_offboard_dort_list')) && $request->session()->get('all_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('all_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				
				if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candAll_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candAll_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('datefrom_offboard_dort_list')) && $request->session()->get('datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_dort_list')) && $request->session()->get('dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortto.'"';
					}
				}
if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				
				
				if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candAll_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candAll_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				
				
				
				
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 97){
					$interviewarr=array(9);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'department IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND department IN('.$interviewdetails.')';
					}
				}
				else if($empsessionId== 94 || $empsessionId== 95){
					$interviewarr=array(8,36,43);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'department IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND department IN('.$interviewdetails.')';
					}
				}
				else{
					/*nothings to do*/
									//$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   //print_r($empdata);exit;
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				}
				//echo $whereraw;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = EmpOffline::orderBy("id","DESC")->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = EmpOffline::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = EmpOffline::get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingEmpOfflineProcessAll'));
				
		//print_r($documentCollectiondetails);exit;
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("EmpOfflineProcess/listingEmpOfflineProcessAll",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	   }
	   
	   public function listingEmpOfflineProcessExitInterview(Request $request)
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
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				if(!empty($request->session()->get('offboard_retained_filter_inner_list')) && $request->session()->get('offboard_retained_filter_inner_list') != 'All')
				{
					$retained = $request->session()->get('offboard_retained_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'retain = "'.$retained.'"';
					}
					else
					{
						$whereraw .= ' And retain = "'.$retained.'"';
					}
				}
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				if(!empty($request->session()->get('offboardexittype_filter_inner_list')) && $request->session()->get('offboardexittype_filter_inner_list') != 'All')
				{
					$exittype = $request->session()->get('offboardexittype_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'leaving_type = "'.$exittype.'"';
					}
					else
					{
						$whereraw .= ' And leaving_type = "'.$exittype.'"';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				
				if(!empty($request->session()->get('company_candDeem_filter_inner_list')) && $request->session()->get('company_candDeem_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candDeem_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candDeem_filter_inner_list')) && $request->session()->get('email_candDeem_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candDeem_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_lastworkingday_list')) && $request->session()->get('exit_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('exit_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_lastworkingday_list')) && $request->session()->get('exit_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('exit_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_dort_list')) && $request->session()->get('exit_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('exit_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_dort_list')) && $request->session()->get('exit_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('exit_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('dept_candDeem_filter_inner_list')) && $request->session()->get('dept_candDeem_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candDeem_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candDeem_filter_inner_list')) && $request->session()->get('status_candDeem_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candDeem_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candDeem_filter_inner_list')) && $request->session()->get('vintage_candDeem_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candDeem_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				
				
				
				
				$CandidateNameArray = array();
				if($whereraw == '')
				{
					/* echo "sddf";
					exit; */
				$c_namedata = EmpOffline::where("department",8)->get();
				}
				else
				{
					
					$c_namedata = EmpOffline::whereRaw($whereraw)->where("department",8)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = EmpOffline::where("department",8)->get();
				}
				else
				{
					
					$email = EmpOffline::whereRaw($whereraw)->where("department",8)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = EmpOffline::where("department",8)->get();
				}
				else
				{
					
					$visa = EmpOffline::whereRaw($whereraw)->where("department",8)->get();
					
				}
				foreach($visa as $_company)
				{
					//echo $_f->first_name;exit;
					if($_company->company_visa!=''){
					$companyvisaArray[$_company->company_visa] = $_company->company_visa;
					}
				}
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = EmpOffline::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = EmpOffline::whereRaw($whereraw)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
					
				}
				foreach($recruter_details as $_recruter_details)
				{
					//echo $_f->first_name;exit;
					$CandidateRecruiterArray[$_recruter_details->id] = $_recruter_details->name;
				}
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = EmpOffline::where("department",8)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = EmpOffline::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = EmpOffline::whereRaw($whereraw)->where("department",8)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				
				$DesignationArray = array();
				if($whereraw == '')
				{
					$depidArray = EmpOffline::where("department",8)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = EmpOffline::whereRaw($whereraw)->where("department",8)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
					
				}
				foreach($desc as $_desc)
				{
					//echo $_f->first_name;exit;
					$DesignationArray[$_desc->id] = $_desc->name;
				}
				
				$OpeningArray = array();
				if($whereraw == '')
				{
				$jobArray = EmpOffline::where("department",8)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = EmpOffline::whereRaw($whereraw)->where("department",8)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
					$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
					
				}
				foreach($opening as $_opening)
				{
					//echo $_f->first_name;exit;
					//$OpeningArray[$_opening->id] = $_opening->name;
					$dept=Department::where("id",$_opening->department)->first();
					//echo $_f->first_name;exit;
					$OpeningArray[$_opening->id] = $_opening->name ." (".$dept->department_name." - ".$_opening->location.")";
				}
				$StatusArray = array();
				if($whereraw == '')
				{
				$status =  EmpOffline::where("department",8)->get();
				}
				else
				{
					$status =  EmpOffline::whereRaw($whereraw)->where("department",8)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = EmpOffline::where("department",8)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = EmpOffline::whereRaw($whereraw)->where("department",8)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
					$department =Department::whereIn('id',array_unique($dpetList))->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$DepartmentArray[$_dptname->id] = $_dptname->department_name;
				}
				//echo $whereraw;
				if($whereraw == '')
					{
						$whereraw = 'condition_leaving = 2 AND last_working_day_resign IS NULL AND retain=2';
					}
					else
					{
						$whereraw .= ' And condition_leaving = 2 AND last_working_day_resign IS NULL AND retain=2';
					}
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->orderBy("created_at","DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw)->get()->count();
					//print_r($documentCollectiondetails);
				}
				else
				{
					//echo "hello1";
					$whereraw1 = 'condition_leaving = 2 AND last_working_day_resign IS NULL';
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw1)->orderBy("created_at","DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw1)->get()->count();
					
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$documentCollectiondetails->setPath(config('app.url/listingEmpOfflineProcessExitInterview'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("EmpOfflineProcess/listingEmpOfflineProcessExitInterview",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
	   
	   
	   
	   public function offboardVisaCancellation(Request $request)
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
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				
				if(!empty($request->session()->get('company_candmashreq_filter_inner_list')) && $request->session()->get('company_candmashreq_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candmashreq_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candmashreq_filter_inner_list')) && $request->session()->get('email_candmashreq_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candmashreq_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_datefrom_offboard_lastworkingday_list')) && $request->session()->get('cancelvisa_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('cancelvisa_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_dateto_offboard_lastworkingday_list')) && $request->session()->get('cancelvisa_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('cancelvisa_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_datefrom_offboard_dort_list')) && $request->session()->get('cancelvisa_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('cancelvisa_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_dateto_offboard_dort_list')) && $request->session()->get('cancelvisa_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('cancelvisa_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
				
				if(!empty($request->session()->get('fnf_offboard_visacancellation_filter_list')) && $request->session()->get('fnf_offboard_visacancellation_filter_list') != 'All')
				{
					$visacancellation = $request->session()->get('fnf_offboard_visacancellation_filter_list');
					
					 if($visacancellation==1){
						$offlinestatusdata= EmpOffline::where('visa_process_status',5)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($visacancellation==2){
						 $offlinestatusdata= EmpOffline::where('visa_process_status',5)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
				
				
if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}
				
				if(!empty($request->session()->get('empoffboard_ffstatus_filter_list')) && $request->session()->get('empoffboard_ffstatus_filter_list') != 'All')
				{
					$offboard_ffstatus = $request->session()->get('empoffboard_ffstatus_filter_list');
					
					 $offboard_ffstatusArray = explode(",",$offboard_ffstatus);
					 $offlinestatusdata= OffboardEMPData::whereIn('settelement_confirmation_status',$offboard_ffstatusArray)->get();
					 $ffstatusarray=array();
					 foreach($offlinestatusdata as $_ffstatus){
						 $ffstatusarray[]=$_ffstatus->emp_id;
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalffstatus=implode(",", $ffstatusarray);
					if($whereraw == '')
					{
						$whereraw = 'id IN('.$finalffstatus.')';
					}
					else
					{
						$whereraw .= ' And id IN('.$finalffstatus.')';
					}
				}
				//echo $whereraw;
				
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('dept_candmashreq_filter_inner_list')) && $request->session()->get('dept_candmashreq_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candmashreq_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candmashreq_filter_inner_list')) && $request->session()->get('status_candmashreq_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candmashreq_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candmashreq_filter_inner_list')) && $request->session()->get('vintage_candmashreq_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candmashreq_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				
				
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = EmpOffline::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = EmpOffline::whereRaw($whereraw)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
					
				}
				foreach($recruter_details as $_recruter_details)
				{
					//echo $_f->first_name;exit;
					$CandidateRecruiterArray[$_recruter_details->id] = $_recruter_details->name;
				}
				
				$CandidateNameArray = array();
				if($whereraw == '')
				{
					/* echo "sddf";
					exit; */
				$c_namedata = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$c_namedata = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$email = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$visa = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($visa as $_company)
				{
					//echo $_f->first_name;exit;
					if($_company->company_visa!=''){
					$companyvisaArray[$_company->company_visa] = $_company->company_visa;
					}
				}
				
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = EmpOffline::where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = EmpOffline::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = EmpOffline::whereRaw($whereraw)->where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				
				$DesignationArray = array();
				if($whereraw == '')
				{
					$depidArray = EmpOffline::where("department",36)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
					
				}
				foreach($desc as $_desc)
				{
					//echo $_f->first_name;exit;
					$DesignationArray[$_desc->id] = $_desc->name;
				}
				
				$OpeningArray = array();
				if($whereraw == '')
				{
				$jobArray = EmpOffline::where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
					$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
					
				}
				foreach($opening as $_opening)
				{
					//echo $_f->first_name;exit;
					//$OpeningArray[$_opening->id] = $_opening->name;
					$dept=Department::where("id",$_opening->department)->first();
					//echo $_f->first_name;exit;
					$OpeningArray[$_opening->id] = $_opening->name ." (".$dept->department_name." - ".$_opening->location.")";
				}
				$StatusArray = array();
				if($whereraw == '')
				{
				$status =  EmpOffline::where("department",36)->get();
				}
				else
				{
					$status =  EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = EmpOffline::where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
					$department =Department::whereIn('id',array_unique($dpetList))->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$DepartmentArray[$_dptname->id] = $_dptname->department_name;
				}
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::whereIn("condition_leaving",array(3,4,5))->whereRaw($whereraw)->orderBy("created_at","DESC")->paginate($paginationValue);
				}
				else
				{					
					$documentCollectiondetails = EmpOffline::whereIn("condition_leaving",array(3,4,5))->orderBy("created_at","DESC")->paginate($paginationValue);
					
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = EmpOffline::whereIn("condition_leaving",array(3,4,5))->whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = EmpOffline::whereIn("condition_leaving",array(3,4,5))->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/offboardVisaCancellation'));
				
				//print_r($documentCollectiondetails);exit;
		return view("EmpOfflineProcess/offboardVisaCancellation",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
	   public function offboardFullandFinalSettelement(Request $request)
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
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				if(!empty($request->session()->get('company_candmashreq_filter_inner_list')) && $request->session()->get('company_candmashreq_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candmashreq_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candmashreq_filter_inner_list')) && $request->session()->get('email_candmashreq_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candmashreq_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('fnf_datefrom_offboard_lastworkingday_list')) && $request->session()->get('fnf_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('fnf_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('fnf_dateto_offboard_lastworkingday_list')) && $request->session()->get('fnf_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('fnf_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('fnf_datefrom_offboard_dort_list')) && $request->session()->get('fnf_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('fnf_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('fnf_dateto_offboard_dort_list')) && $request->session()->get('fnf_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('fnf_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}	
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('fnf_empoffboard_ffstatus_filter_list')) && $request->session()->get('fnf_empoffboard_ffstatus_filter_list') != 'All')
				{
					$offboard_ffstatus = $request->session()->get('fnf_empoffboard_ffstatus_filter_list');
					
					 $offboard_ffstatusArray = explode(",",$offboard_ffstatus);
					 $offlinestatusdata= OffboardEMPData::whereIn('settelement_confirmation_status',$offboard_ffstatusArray)->get();
					 $ffstatusarray=array();
					 foreach($offlinestatusdata as $_ffstatus){
						 $ffstatusarray[]=$_ffstatus->emp_id;
						 
						 
					 }
					 //print_r($ffstatusarray);//exit;
					 $finalffstatus=implode(",", $ffstatusarray);
					if($whereraw == '')
					{
						$whereraw = 'id IN('.$finalffstatus.')';
					}
					else
					{
						$whereraw .= ' And id IN('.$finalffstatus.')';
					}
				}
				
				
				if(!empty($request->session()->get('fnf_offboard_hrstatus_filter_list')) && $request->session()->get('fnf_offboard_hrstatus_filter_list') != 'All')
				{
					$hrstatus = $request->session()->get('fnf_offboard_hrstatus_filter_list');
					
					 if($hrstatus==1){
						$offlinestatusdata= OffboardEMPData::where('settelement_hr_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($hrstatus==2){
						 $offlinestatusdata= OffboardEMPData::where('settelement_hr_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
				if(!empty($request->session()->get('fnf_offboard_finance_filter_list')) && $request->session()->get('fnf_offboard_finance_filter_list') != 'All')
				{
					$finance = $request->session()->get('fnf_offboard_finance_filter_list');
					
					 if($finance==1){
						$offlinestatusdata= OffboardEMPData::where('settelement_finance_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($finance==2){
						 $offlinestatusdata= OffboardEMPData::where('settelement_finance_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
				
				if(!empty($request->session()->get('fnf_offboard_amount_filter_list')) && $request->session()->get('fnf_offboard_amount_filter_list') != 'All')
				{
					$amount = $request->session()->get('fnf_offboard_amount_filter_list');
					
					 if($amount==1){
						//$offlinestatusdata= OffboardEMPData::where('settelement_finance_status',1)->where('settelement_hr_status',1)->get();
						$offlinestatusdata = EmpOffline::whereIn("condition_leaving",array(3,4,5,6))->orderBy("created_at","DESC")->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 //$ffstatusarray[]=$_ffstatus->emp_id;
							 $data=OffboardEMPData::where("emp_id",$_ffstatus->id)->first();
							 //print_r($data);//exit;
							 if($data!='' && $_ffstatus->salary_deduction==$data->salary_deduction_total_finance){
							 $ffstatusarray[]=$_ffstatus->id;
							 }
							 
							 
						 } 
						// print_r($statusarraydata);exit;
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($amount==2){
						 $offlinestatusdata = EmpOffline::whereIn("condition_leaving",array(3,4,5,6))->orderBy("created_at","DESC")->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $data=OffboardEMPData::where("emp_id",$_ffstatus->id)->first();
							 if($data!=''){
							 if($_ffstatus->salary_deduction!=$data->salary_deduction_total_finance){
							 $ffstatusarray[]=$_ffstatus->id;
							 }
							 }
							 else{
								$ffstatusarray[]=$_ffstatus->id; 
							 }
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
				
				
				
				
				
				
				
				
				
				//echo $whereraw;
				if(!empty($request->session()->get('dept_candmashreq_filter_inner_list')) && $request->session()->get('dept_candmashreq_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candmashreq_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candmashreq_filter_inner_list')) && $request->session()->get('status_candmashreq_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candmashreq_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candmashreq_filter_inner_list')) && $request->session()->get('vintage_candmashreq_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candmashreq_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = EmpOffline::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = EmpOffline::whereRaw($whereraw)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
					
				}
				foreach($recruter_details as $_recruter_details)
				{
					//echo $_f->first_name;exit;
					$CandidateRecruiterArray[$_recruter_details->id] = $_recruter_details->name;
				}
				
				$CandidateNameArray = array();
				if($whereraw == '')
				{
					/* echo "sddf";
					exit; */
				$c_namedata = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$c_namedata = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$email = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$visa = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($visa as $_company)
				{
					//echo $_f->first_name;exit;
					if($_company->company_visa!=''){
					$companyvisaArray[$_company->company_visa] = $_company->company_visa;
					}
				}
				
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = EmpOffline::where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = EmpOffline::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = EmpOffline::whereRaw($whereraw)->where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				
				$DesignationArray = array();
				if($whereraw == '')
				{
					$depidArray = EmpOffline::where("department",36)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
					
				}
				foreach($desc as $_desc)
				{
					//echo $_f->first_name;exit;
					$DesignationArray[$_desc->id] = $_desc->name;
				}
				
				$OpeningArray = array();
				if($whereraw == '')
				{
				$jobArray = EmpOffline::where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
					$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
					
				}
				foreach($opening as $_opening)
				{
					//echo $_f->first_name;exit;
					//$OpeningArray[$_opening->id] = $_opening->name;
					$dept=Department::where("id",$_opening->department)->first();
					//echo $_f->first_name;exit;
					$OpeningArray[$_opening->id] = $_opening->name ." (".$dept->department_name." - ".$_opening->location.")";
				}
				$StatusArray = array();
				if($whereraw == '')
				{
				$status =  EmpOffline::where("department",36)->get();
				}
				else
				{
					$status =  EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = EmpOffline::where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
					$department =Department::whereIn('id',array_unique($dpetList))->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$DepartmentArray[$_dptname->id] = $_dptname->department_name;
				}
				//echo $whereraw ;
				//echo $val=EmpOffline::whereRaw($whereraw)->whereIn("condition_leaving",array(3,4,5,6))->orderBy("created_at","DESC")->toSql();
				if($whereraw != '')
				{
					//echo "hello";exit;exit_interview_date
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->whereIn("condition_leaving",array(3,4,5,6))->orderBy("created_at","DESC")->paginate($paginationValue);
				}
				else
				{
					
					$documentCollectiondetails = EmpOffline::whereIn("condition_leaving",array(3,4,5,6))->orderBy("created_at","DESC")->paginate($paginationValue);
					
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
				
				
					$reportsCount = EmpOffline::whereRaw($whereraw)->whereIn("condition_leaving",array(3,4,5,6))->get()->count();
				}
				else
				{
					$reportsCount = EmpOffline::whereIn("condition_leaving",array(3,4,5,6))->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/offboardFullandFinalSettelement'));
				
				
		return view("EmpOfflineProcess/offboardFullandFinalSettelement",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
	   public function filterByCandidateNameEmpOfflineProcess(Request $request)
		{
			$cname = $request->cname;
			$request->session()->put('cname_emp_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailEmpOfflineProcess(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_cand_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationEmpOfflineProcess(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_cand_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentEmpOfflineProcess(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_cand_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningEmpOfflineProcess(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_cand_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatussEmpOfflineProcess(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_cand_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintageEmpOfflineProcess(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_cand_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanyEmpOfflineProcess(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_cand_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
		
		//Start deem mashreq
		public function filterByCandidateNameDeemEmpOfflineProcess(Request $request)
		{
			$cname = $request->cname;
			$request->session()->put('cname_empDeem_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailDeemEmpOfflineProcess(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candDeem_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationDeemEmpOfflineProcess(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candDeem_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentDeemEmpOfflineProcess(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candDeem_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningDeemEmpOfflineProcess(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candDeem_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatussDeemEmpOfflineProcess(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candDeem_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintageDeemEmpOfflineProcess(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candDeem_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanyDeemEmpOfflineProcess(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candDeem_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
		
		//Start All
		public function filterByCandidateNameAllEmpOfflineProcess(Request $request)
		{
			$cname = $request->cname;
			$request->session()->put('cname_empAll_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailAllEmpOfflineProcess(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candAll_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationAllEmpOfflineProcess(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candAll_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentAllEmpOfflineProcess(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candAll_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningAllEmpOfflineProcess(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candAll_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatussAllEmpOfflineProcess(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candAll_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintageAllEmpOfflineProcess(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candAll_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanyAllEmpOfflineProcess(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candAll_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
		
		
		//Start All
		public function filterByCandidateNameAafaqEmpOfflineProcess(Request $request)
		{
			$cname = $request->cname;
			$request->session()->put('cname_empAafaq_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailAafaqEmpOfflineProcess(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candAafaq_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationAafaqEmpOfflineProcess(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candAafaq_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentAafaqEmpOfflineProcess(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candAafaq_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningAafaqEmpOfflineProcess(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candAafaq_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatussAafaqEmpOfflineProcess(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candAafaq_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintageAafaqEmpOfflineProcess(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candAafaq_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanyAafaqEmpOfflineProcess(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candAafaq_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
	   //masr
	   public function filterByCandidateNamemashreqEmpOfflineProcess(Request $request)
		{
			$cname = $request->cname;
			//echo $cname;exit;
			$request->session()->put('cname_empmashreq_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailmashreqEmpOfflineProcess(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candmashreq_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationmashreqEmpOfflineProcess(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candmashreq_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentmashreqEmpOfflineProcess(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candmashreq_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningmashreqEmpOfflineProcess(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candmashreq_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatusmashreqEmpOfflineProcess(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candmashreq_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintagemashreqEmpOfflineProcess(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candmashreq_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanymashreqEmpOfflineProcess(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candmashreq_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByRecruiterNameAllEmpOfflineProcess(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNameAll_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByRecruiterNamemashreqEmpOfflineProcess(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNamemashreq_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByRecruiterNameenbdEmpOfflineProcess(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNameenbd_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
	   public function filterByRecruiterNameaafaqEmpOfflineProcess(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNameaafaq_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByRecruiterNamedeemEmpOfflineProcess(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNamedeem_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
	   public function filterByRecruiterNamevisapipelineEmpOfflineProcess(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNamevisapipeline_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
	   
	   
	   
	   

		public function appliedFilterOnDocumentCollection(Request $request)
			{
						$selectedFilter = $request->input();
						$request->session()->put('emp_name',$selectedFilter['emp_name']);		
						$request->session()->put('department',$selectedFilter['department']);
						$request->session()->put('caption',$selectedFilter['caption']);
						
						$request->session()->put('designation',$selectedFilter['designation']);
						$request->session()->put('status',$selectedFilter['status']);
						$request->session()->put('serialized_id',$selectedFilter['serialized_id']);
						$request->session()->put('visa_process_status',$selectedFilter['visa_process_status']);
						return redirect('documentcollection');
					
			}
		public function resetDocumentCollectionFilter(Request $request)
		{
					$request->session()->put('emp_name','');		
			
					$request->session()->put('department','');
					$request->session()->put('caption','');
					
					$request->session()->put('designation','');
					$request->session()->put('status','');
					$request->session()->put('serialized_id','');
					$request->session()->put('visa_process_status','');
					$request->session()->flash('message','Filters Reset Successfully.');
					return redirect('documentcollection');
		}
		
		public function deleteDocumentCollection(Request $request)
		{
			$documentCollectionId = $request->documentCollectionId;
			$documentCollectionModel = EmpOffline::find($documentCollectionId);
			$documentCollectionModel->delete();
			/* delete From values*/
			$documentValues = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectionId)->get();
			foreach($documentValues as $_values)
			{
				DocumentCollectionDetailsValues::find($_values->id)->delete();
			}
			
			$visas = Visaprocess::where("document_id",$documentCollectionId)->get();
			foreach($visas as $_v)
			{
				Visaprocess::find($_v->id)->delete();
			}
			
			$trainingSets = TrainingProcess::where("document_id",$documentCollectionId)->get();
			foreach($trainingSets as $_t)
			{
				TrainingProcess::find($_t->id)->delete();
			}
			/* delete From values*/
			$request->session()->flash('message','Document Collection Deleted Successfully.');
			return redirect('documentcollection');
		}
		
		public function addCollectionAttributes()
		{
			$attributeTypeDetails = AttributeType::orderBy('attribute_type_id','DESC')->get();
			$deptLists = Department::where("status",1)->orderBy('id','DESC')->get();
			return view("Onboarding/addAttributeCollection",compact('attributeTypeDetails','deptLists'));
		}
		
		public function addDocumentCollectionAttrPost(Request $request)
		{
			$selectedFilterInput = $request->input();
		
			$documentAttributeModel = new DocumentCollectionAttributes();
			$documentAttributeModel->attribute_name = $selectedFilterInput['attribute_name'];
			$documentAttributeModel->attribute_code = $selectedFilterInput['attribute_code'];
			$documentAttributeModel->attrbute_type_id = $selectedFilterInput['attrbute_type_id'];
			if($selectedFilterInput['attrbute_type_id'] == 3)
			{
				$documentAttributeModel->opt = implode(",",$selectedFilterInput['opt']);
			}
			$documentAttributeModel->attribute_requirement = $selectedFilterInput['attribute_requirement'];
			$documentAttributeModel->sort_order = $selectedFilterInput['sort_order'];
			$documentAttributeModel->status = $selectedFilterInput['status'];
			$documentAttributeModel->attribute_area = $selectedFilterInput['attribute_area'];
			if($selectedFilterInput['attribute_area'] == 'kyc')
			{
				$documentAttributeModel->department_id = $selectedFilterInput['department_id'];
			}
			$documentAttributeModel->save();
			$request->session()->flash('message','Attribute Saved Successfully.');
            return redirect('dCollectionAttributes');
		}
		
		
		
		public function dCollectionAttributes(Request $req)
	   {
		  
			$filterList = array();
			$filterList['attribute_name'] = '';
			$filterList['attrbute_type_id'] = '';
			$filterList['attribute_area'] = '';
			$filterList['department_id'] = '';
			$documentCollectiondetailsAttr = DocumentCollectionAttributes::orderBy("id","DESC");
			if(!empty($req->session()->get('attribute_name')))
			{
			
				$attribute_name = $req->session()->get('attribute_name');
				$filterList['attribute_name'] = $attribute_name;
				$documentCollectiondetailsAttr = $documentCollectiondetailsAttr->where("attribute_name","like",$attribute_name."%");
			}
		
			if(!empty($req->session()->get('attribute_area')))
			{
			
				$attribute_area = $req->session()->get('attribute_area');
				$filterList['attribute_area'] = $attribute_area;
				$documentCollectiondetailsAttr = $documentCollectiondetailsAttr->where("attribute_area",$attribute_area);
			}	
			if(!empty($req->session()->get('attrbute_type_id')))
			{
			
				$attrbute_type_id = $req->session()->get('attrbute_type_id');
				$filterList['attrbute_type_id'] = $attrbute_type_id;
				$documentCollectiondetailsAttr = $documentCollectiondetailsAttr->where("attrbute_type_id",$attrbute_type_id);
			}	
			if(!empty($req->session()->get('department_id')))
			{
			
				$department_id = $req->session()->get('department_id');
				$filterList['department_id'] = $department_id;
				$documentCollectiondetailsAttr = $documentCollectiondetailsAttr->where("department_id",$department_id);
			}					
			$documentCollectiondetailsAttr = $documentCollectiondetailsAttr->get();
		
		
			$attributeTypeDetails = AttributeType::orderBy('attribute_type_id','DESC')->get();
			$deptLists = Department::where("status",1)->orderBy('id','DESC')->get();
			return view("Onboarding/dCollectionAttributes",compact('documentCollectiondetailsAttr','filterList','attributeTypeDetails','deptLists'));
	   }
	   
	   public function editDocumentCollectionAttr(Request $request)
	   {
		    $attributeId = $request->attrId;
			$attributeTypeDetails = AttributeType::orderBy('attribute_type_id','DESC')->get();
			$documentCollectionDetails =  DocumentCollectionAttributes::where("id",$attributeId)->first();
			$optionArray = array();
			if($documentCollectionDetails->attrbute_type_id == 3)
			{
				$optionsTxt = $documentCollectionDetails->opt;
				$optionArray = explode(",",$optionsTxt);
			}
			$deptLists = Department::where("status",1)->orderBy('id','DESC')->get();
			return view("Onboarding/editDocumentCollectionAttr",compact('documentCollectionDetails','attributeTypeDetails','optionArray','deptLists'));
	   }


	   public function resetDocumentCollectionFilterAttr(Request $request)
	   {
		   $request->session()->put('attribute_name','');		
		   $request->session()->put('attrbute_type_id','');
		    $request->session()->put('attribute_area','');
			$request->session()->put('department_id','');
		   $request->session()->flash('message','Filters Reset Successfully.');
		   return redirect('dCollectionAttributes');
	   }
 
	   public static function getHiringSourceName($hiringSourceId)
	   {
		  return HiringSourceDetails::where("id",$hiringSourceId)->first()->name;
	   }
	   public static function getRecruiterName($recruiterId)
	   {
		   if($recruiterId!=''){
		   $data= RecruiterDetails::where("id",$recruiterId)->first();
		   if($data!=''){
			   return $data->name;
		   }
		   else{
			    return "--";
		   }
		   }
		   else return "--";
	   }
	   public static function getOfferId($documentId)
	   {
		  $offerLetterMod =  OfferletterDetails::where("document_id",$documentId)->first();
		  return $offerLetterMod->id;
	   }
	   
	   public function getLocation()
	   {
		   return view("Onboarding/getLocation");
	   }

	    public function checkforIncentiveLetter(Request $request)
		   {
			   $documentCollectId = $request->documentCollectionId;
			   
			   $documentCollectionDetails = EmpOffline::where("id",$documentCollectId)->first();
			   $departmentId = $documentCollectionDetails->department;
			   $designationId = $documentCollectionDetails->designation;
			   $location = $documentCollectionDetails->location;
			  $incentiveLetterDetails = IncentiveLetterDetails::where("department_id",$departmentId)->where("designation_id",$designationId)->where("location",$location)->first();
			  if($incentiveLetterDetails == '')
			  {
				   echo "Not Allowed";
			  }
			  else
			  {
				   $pathToIncentiveLetter = $incentiveLetterDetails->path.'/'.$incentiveLetterDetails->location.'/'.$documentCollectId;
				   echo $pathToIncentiveLetter;
				   
			  }
			   
			   exit;
		   }
	   public function collectionDetailsTab1(Request $request)
	   {
		    $documentCollectId = $request->documentCollectionId;
		    $documentCollectionDetails = EmpOffline::where("id",$documentCollectId)->first();
			
			/*
			*upload document values with label
			*start code
			*/
				$documentCollectionValues = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectId)->get();
				/* echo '<pre>';
				print_r($documentCollectionValues);
				exit; */
				$docCollectionDetails = array();
				foreach($documentCollectionValues as $_docCollectionValue)
				{
					
					$attrId = $_docCollectionValue->attribute_code;
					$docAttributes = DocumentCollectionAttributes::where("id",$attrId)->first();
					$attributeName = $docAttributes->attribute_name.'^'.$docAttributes->attrbute_type_id;
					
					
					$attributeValue = $_docCollectionValue->attribute_value;
					$docCollectionDetails[$attributeName] = $attributeValue;
				}
				
			/*
			*upload document values with label
			*end code
			*/
			$visaProcessLists = Visaprocess::where("document_id",$documentCollectId)->orderBy('id','DESC')->get();
			return view("Onboarding/collectionDetailsTab1",compact('documentCollectionDetails','docCollectionDetails','visaProcessLists'));
	   }
	   
	   public static function getFilterValueName($filterCode,$filterValue)
	   {
		  
		   $returnName = 'no';
		   switch($filterCode)
		   {
			   case 'deptID':
				  $returnName = Department::where("id",$filterValue)->first()->department_name;
				 
			   Break;
			   case 'productID':
			    $returnName = Product::where("id",$filterValue)->first()->product_name;
			   Break;
			   case 'designationID':
				$returnName = Designation::where("id",$filterValue)->first()->name;
				
			   Break;
			   case 'emp_name':
				$returnName = $filterValue;
			   Break;
			   case 'caption':
			  
			   $returnName =$filterValue;
			   Break;
			   case 'status':
				if($filterValue == 1)
				{
					$returnName = 'OfferLetter Document Pending';
				}
				else if($filterValue == 2)
				{
					$returnName = 'Ready for Offer Letter';
				}
				else if($filterValue == 4)
				{
					$returnName = 'Offer Letter Generated';
				}
				else if($filterValue == 5)
				{
					$returnName = 'Signed Offerletter Uploaded';
				}
				else if($filterValue == 6)
				{
					$returnName = 'Visa Document Uploaded';
				}
				else if($filterValue == 7)
				{
					$returnName = 'Ready for Onboarding';
				}
				else if($filterValue == 8)
				{
					$returnName = 'On-boarded';
				}
				else
				{
					$returnName = 'Pending';
				}
			   Break;
			   case 'serialized_id':
				$returnName = $filterValue;
			   Break;
			   case 'visa_process_status':
			   if($filterValue == 1)
				{
					$returnName = 'Pending';
				}
				else if($filterValue == 2)
				{
					$returnName = 'Inprogress';
				}
				else if($filterValue == 4)
				{
					$returnName = 'Completed';
				}
				else
				{
					$returnName = 'Pending';
				}
			   Break;
			   
			   
		   }
		   return $returnName;
	   }
	   
	   public function resetRequestFilterStep(Request $request)
	   {
		   $filtername =  $request->nameFilter;
		    switch($filtername)
		   {
			   case 'deptID':
				   $request->session()->put('department','');
				 
			   Break;
			   
			   case 'productID':
			    $request->session()->put('department','');
			   Break;
			   
			   case 'designationID':
				  $request->session()->put('designation','');
				
			   Break;
			   
			   case 'emp_name':
				  $request->session()->put( $filtername,'');
			   Break;
			   
			   case 'caption':
					$request->session()->put( $filtername,'');
			   Break;
			   
			   case 'status':
				$request->session()->put( $filtername,'');
			   Break;
			   
			   case 'serialized_id':
				$request->session()->put( $filtername,'');
			   Break;
			   
			   case 'visa_process_status':
			  $request->session()->put( $filtername,'');
				
			   Break;
			   
			   
		   }
		  
		    return back();
	   }
	   
	   public static function getDesignationForSelectedDepartment($depId)
	   {
		  
		   return Designation::where("department_id",$depId)->where("status",1)->get();
	   }
	   
	   public static function getCreatedByNameFromId($id)
	   {
		  
		   return Employee::where("id",$id)->first()->fullname;
	   }
	   
	   public function bankCodeGenerationAjax(Request $request)
	   {
		   $documentCollectionID = $request->documentCollectionId;
		   $documentDetails = EmpOffline::where("id",$documentCollectionID)->first();
		   return view("OnboardingAjax/bankCodeGenerationAjax",compact('documentDetails'));
	   }
	   
	    public function saveBankCodeAjax(Request $request)
	   {
		   $parameterInput = $request->input();
		   $documentCollectionID = $parameterInput['documentCollectionID'];
		   $bankGeneratedCode = $parameterInput['bank_generated_code'];
		   $docCollectionMod = EmpOffline::find($documentCollectionID);
		   $docCollectionMod->bank_generated_code=$bankGeneratedCode;
		 
		   $docCollectionMod->serialized_id = 'ReadyForOnboarding-000'.$documentCollectionID;
		   $docCollectionMod->save();
		    /*
		   *updating in main employee table
		   */
		    $documentDetails = EmpOffline::where("id",$documentCollectionID)->first();
			if($documentDetails->onboard_status == 2)
			{
				
				$employeeMod =  Employee_details::where("document_collection_id",$documentCollectionID)->first();
				if($employeeMod != '')
				{
					$mainEmpMod = Employee_details::find($employeeMod->id);
					$mainEmpMod->source_code = $bankGeneratedCode;
					$mainEmpMod->save();
					
					/*
					*checking for emp attributeId
					*/
					$empAttrExist = Employee_attribute::where("emp_id",$employeeMod->emp_id)->where("dept_id",$employeeMod->dept_id)->where("attribute_code","source_code")->first();
					if($empAttrExist != '')
					{
						$updateEmpAttr = Employee_attribute::find($empAttrExist->id);
						
					}
					else
					{
						$updateEmpAttr = new Employee_attribute();
					}
					$updateEmpAttr->dept_id = $employeeMod->dept_id;
					$updateEmpAttr->emp_id = $employeeMod->emp_id;
					$updateEmpAttr->attribute_code = 'source_code';
					$updateEmpAttr->attribute_values = $bankGeneratedCode;
					$updateEmpAttr->status = 1;
					$updateEmpAttr->save();
					/*
					*checking for emp attributeId
					*/
				}
			}
		    /*
		   *updating in main employee table
		   */
		   echo 'Bank Generated Code Saved Successfully.';
		   exit;
	   }
	   
	   public function finalizationOnboarding(Request $request)
	   {
		    $documentCollectionId = $request->documentCollectionId;
			$documentCollectionDetails = EmpOffline::where("id",$documentCollectionId)->first();
		     return view("Onboarding/finalizationOnboarding",compact('documentCollectionId','documentCollectionDetails'));
	   }
	   public static function getDesignation($designId = NULL)
	   {
		  
					$des=Designation::where("id",$designId)->first();
					if($des!=''){
						 return $des->name;
					}
					else{
						return "--";
					}
					
				
		   
	   }
	   public static function getTeamLeader($id = NULL)
	   {
		  
			    $emp_details = Employee_details::where("id",$id)->first(); 
				if($emp_details!=''){
					
					
						 return $emp_details->emp_name;
					}
					else{
						return "--";
					}
		   
	   }
	   
	   public function setOffSetForEmpOfflineProcess(Request $request)
	   {
		   $offset = $request->offset;
		  $request->session()->put('onboading_page_limit',$offset);
	   }
	   
	   public function filterReportAsPerDepartmentr(Request $request)
	   {
		   $deptid = $request->deptid;
		    $request->session()->put('onboarding_department_filter','');
		    $request->session()->put('onboarding_department_filter',$deptid);
	   }
	   
	   public function updateFilterOnBoarding(Request $request)
	   {
		    $filterList = array();
				
				$filterList['department'] = '';
			    if(!empty($request->session()->get('onboarding_department_filter')))
				  { 
						$_dpartId= $request->session()->get('onboarding_department_filter');
					  
					   $filterList['department'] =Department::where("id",$_dpartId)->first()->department_name;
				  }
		   return view("OnboardingAjax/updateFilterOnBoarding",compact('filterList'));
	   }
	   
	   public function cancelFiltersOnboard(Request $request)
	   {
		   $request->session()->put('onboarding_department_filter','');
	   }
	   public static function getonboardingAges($createAT)
			{
				//echo $createAT;exit;
				if($createAT != '')
				{
					$doj = createAT;
					if($doj == NULL || $doj == '')
					{
						return "Not Decleared";
					}
					else
					{
						$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 return  $returnData;
					}
					
				}
				else
				{
					return "Not Decleared";
				}
			}
	public function updateVintage(Request $req){
		 $dateC = date("Y-m-d");
		 
		 $Collection  = EmpOffline::whereDate("vintage_updated_date","<",$dateC)->get();
		 if(count($Collection)>0)
			{
			foreach($Collection as $_coll)
			{
				$details = EmpOffline::where("id",$_coll->id)->first();
				
				/*update Obj*/
				$updateOBJ = EmpOffline::find($_coll->id);
				/*update Obj*/								
				$createdAT = $details->created_at;
				/*				
				$days INterbakl
				
				*/
				$doj = str_replace("/","-",$createdAT);
				$date1 = date("Y-m-d",strtotime($doj));
				$daysInterval = abs(strtotime($dateC)-strtotime($doj))/ (60 * 60 * 24);
				//echo $diff;exit;
				//$daysInterval=
				$updateOBJ->Vintage_days = $daysInterval;
				$updateOBJ->Vintage_updated_date = $dateC;
				$updateOBJ->save();
				
			}
			}
			else
			{
				//echo "All DONe";
				exit;
			}
	
	}		
	public function uploadofferletterIncentiveLetterDocumentStartAjax(Request $request)
	   {
		   $selectedFilter = $request->input();
		/*   echo '<pre>';
		  print_r($selectedFilter);
		  exit; */
		  //print_r($_FILES);exit;
		   $saveData = array();
		  
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		   
		   $num = $documentCollectionId;
		    unset($selectedFilter['_token']);
		    unset($selectedFilter['status']);
		    unset($selectedFilter['documentCollectionID']);
		    unset($selectedFilter['_url']);
			
			
		   
			
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				if($request->file($key))
				{
					
				 $filenameWithExt = $request->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$request->file($key)->getClientOriginalExtension();
				$vKey = $key;
				 $newFileName = $key.'-'.$num.'.'.$fileExtension;
			   
				    if(file_exists(public_path('documentCollectionFiles/'.$newFileName))){

					  unlink(public_path('documentCollectionFiles/'.$newFileName));

					}
				
				/*
				*Updating File Name
				*/
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $request->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				$request->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
				$fileIndex++;
				}
			}
			
			
			
			foreach($selectedFilter as $key=>$value)
			{
				if($value != '' && $value != 'undefined')
				{
				$existDocument = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectionId)->where("attribute_code",$key)->first();
				if($existDocument != '')
				{
					$objDocument= DocumentCollectionDetailsValues::find($existDocument->id);
				}
				else
				{
				$objDocument = new DocumentCollectionDetailsValues();	
				}	
				
				$objDocument->document_collection_id = $documentCollectionId;
				$objDocument->attribute_code = $key;
				$objDocument->attribute_value = $value;
				$objDocument->save();
				}
				
			}
			foreach($keys as $key)
			{
				if(in_array($key,$listOfAttribute))
				{
					
					$existDocument = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectionId)->where("attribute_code",$key)->first();
					if($existDocument != '')
					{
						$objDocument= DocumentCollectionDetailsValues::find($existDocument->id);
					}
					else
					{
						$objDocument = new DocumentCollectionDetailsValues();
					}
					$objDocument->document_collection_id = $documentCollectionId;
					$objDocument->attribute_code = $key;
					$objDocument->attribute_value = $filesAttributeInfo[$key];
					$objDocument->save();
					
				}
			}
			
		
			
			echo "Document Upload Successfully.";
			exit;
	   }
	   
	   public function visadeatlsformStartAjax(Request $request)
	   {
		   $selectedFilter = $request->input();
		/*   echo '<pre>';
		  print_r($selectedFilter);
		  exit; */
		  //print_r($_FILES);exit;
		   $saveData = array();
		  
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		   $visatype = $selectedFilter['visatype'];
		   
		   $num = $documentCollectionId;
		    unset($selectedFilter['_token']);
		    unset($selectedFilter['status']);
		    unset($selectedFilter['documentCollectionID']);
			unset($selectedFilter['visatype']);
		    unset($selectedFilter['_url']);
			
			
		   
			
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				if($request->file($key))
				{
					
				 $filenameWithExt = $request->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$request->file($key)->getClientOriginalExtension();
				$vKey = $key;
				 $newFileName = $key.'-'.$num.'.'.$fileExtension;
			   
				    if(file_exists(public_path('documentCollectionFiles/'.$newFileName))){

					  unlink(public_path('documentCollectionFiles/'.$newFileName));

					}
				
				/*
				*Updating File Name
				*/
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $request->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				$request->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
				$fileIndex++;
				}
			}
			
			
			
			foreach($selectedFilter as $key=>$value)
			{
				if($value != '' && $value != 'undefined')
				{
				$existDocument = VisaDetails::where("document_collection_id",$documentCollectionId)->where("visa_type_id",$visatype)->where("attribute_code",$key)->first();
				if($existDocument != '')
				{
					$objDocument= VisaDetails::find($existDocument->id);
				}
				else
				{
				$objDocument = new VisaDetails();	
				}	
				
				$objDocument->document_collection_id = $documentCollectionId;
				$objDocument->visa_type_id = $visatype;
				$objDocument->attribute_code = $key;
				$objDocument->attribute_value = $value;
				$objDocument->save();
				}
				
			}
			foreach($keys as $key)
			{
				if(in_array($key,$listOfAttribute))
				{
					
					$existDocument = VisaDetails::where("document_collection_id",$documentCollectionId)->where("visa_type_id",$visatype)->where("attribute_code",$key)->first();
					if($existDocument != '')
					{
						$objDocument= VisaDetails::find($existDocument->id);
					}
					else
					{
						$objDocument = new VisaDetails();
					}
					$objDocument->document_collection_id = $documentCollectionId;
					$objDocument->visa_type_id = $visatype;
					$objDocument->attribute_code = $key;
					$objDocument->attribute_value = $filesAttributeInfo[$key];
					$objDocument->save();
					
				}
			}
			$doccollection =EmpOffline::where("id",$documentCollectionId)->first();
			if($doccollection!=''){
				$onboard_status=$doccollection->onboard_status;
				if($onboard_status==2){
						$visadetailList = VisaDetails::where("document_collection_id",$documentCollectionId)->where("visa_type_id",$visatype)->get();
						if($visadetailList!=''){
							foreach($visadetailList as $_attribute){
							$attribute_id=$_attribute->attribute_code;
							$attributedetails = Attributes::where("attribute_id",$attribute_id)->first();
							$attribute_code=$attributedetails->attribute_code;
							$empdetails=Employee_details::where("document_collection_id",$documentCollectionId)->first();
							$emp_id=$empdetails->emp_id;
							$dept_id=$empdetails->dept_id;
							// exist emp_id,dept_id,attribute_code then update
							$existempattribute = Employee_attribute::where("emp_id",$emp_id)->where("dept_id",$dept_id)->where("attribute_code",$attribute_code)->first();
								if($existempattribute != '')
								{
									$employeeAttribute= Employee_attribute::find($existempattribute->id);
								}
								else
								{
									$employeeAttribute = new Employee_attribute();
								}
							
							$employeeAttribute->emp_id = $emp_id;
							$employeeAttribute->dept_id = $dept_id;
							$employeeAttribute->attribute_code = $attribute_code;
							$employeeAttribute->attribute_values = $_attribute->attribute_value;
							$employeeAttribute->status = 1;
							$employeeAttribute->save();
							
							}
						}
						
						
					}
				
			}
		
			
			$response['code'] = '200';
			$response['visaId'] = $documentCollectionId;
			
			echo json_encode($response);
		   exit;
	   }
	   public function filterByCandidateNamevisapipeline(Request $request)
		{
			$cname = $request->cname;
			//echo $cname;exit;
			$request->session()->put('cname_empvisapipeline_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailvisapipeline(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candvisapipeline_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationvisapipeline(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candvisapipeline_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentvisapipeline(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candvisapipeline_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningvisapipeline(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candvisapipeline_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatusvisapipeline(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candvisapipeline_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintagevisapipeline(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candvisapipeline_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanyvisapipeline(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candvisapipeline_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
		public function okForVisaPost(Request $request){
			$docid=$request->docId;
			$detailsObj = EmpOffline::find($docid);
			$detailsObj->ok_visa = 2; 
			$detailsObj->save();
		}
		public function documentcollectionbyfilterEmpOfflineProcess(Request $request)
		{
			
			//print_r($request->input());exit;
			$name = $request->input('candidatename');
			
			$job_openingarray = $request->input('job_opening');
			if($job_openingarray!=''){
			$job_opening=implode(",", $job_openingarray);
			}
			else{
				$job_opening='';
			}
			$RecruiterNamearray=$request->input('recruiterName');
			if($RecruiterNamearray!=''){
			$RecruiterName=implode(",", $RecruiterNamearray);
			}
			else{
				$RecruiterName='';
			}
			//echo $RecruiterName;exit;
			$request->session()->put('cname_emp_filter_inner_list',$name);
			$request->session()->put('opening_cand_filter_inner_list',$job_opening);
			$request->session()->put('company_RecruiterName_filter_inner_list',$RecruiterName);
			
			
			
		}
		public function documentresetfilterEmpOfflineProcess(Request $request)
		{
			
			$request->session()->put('cname_emp_filter_inner_list','');
			$request->session()->put('opening_cand_filter_inner_list','');
			$request->session()->put('company_RecruiterName_filter_inner_list','');
		}
			public function listingEmpOfflineProcessRequestedVisapipeline(Request $request)
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
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				if(!empty($request->session()->get('date_emp_filter_inner_list')) && $request->session()->get('date_emp_filter_inner_list') != 'All')
				{
					$date_emp = $request->session()->get('date_emp_filter_inner_list');
					
					 if($whereraw == '')
					{
						$whereraw = 'onboarding_date = "'.$date_emp.'"';
					}
					else
					{
						$whereraw .= ' And onboarding_date = "'.$date_emp.'"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'emp_name like "%'.$fname.'%"';
					}
					else
					{
						$whereraw .= ' And emp_name like "%'.$fname.'%"';
					}
				}
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					 $selectedFilter['CNAME'] = $cname;
					 if($whereraw == '')
					{
						$whereraw = 'emp_name like "%'.$cname.'%"';
					}
					else
					{
						$whereraw .= ' And emp_name like "%'.$cname.'%"';
					}
				}
				if(!empty($request->session()->get('company_candvisapipeline_filter_inner_list')) && $request->session()->get('company_candvisapipeline_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candvisapipeline_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candvisapipeline_filter_inner_list')) && $request->session()->get('email_candvisapipeline_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candvisapipeline_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('desc_candvisapipeline_filter_inner_list')) && $request->session()->get('desc_candvisapipeline_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candvisapipeline_filter_inner_list');
					 $selectedFilter['DESC'] = $desc;
					 if($whereraw == '')
					{
						$whereraw = 'designation = "'.$desc.'"';
					}
					else
					{
						$whereraw .= ' And designation = "'.$desc.'"';
					}
				}
				if(!empty($request->session()->get('dept_candvisapipeline_filter_inner_list')) && $request->session()->get('dept_candvisapipeline_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candvisapipeline_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candvisapipeline_filter_inner_list')) && $request->session()->get('status_candvisapipeline_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candvisapipeline_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candvisapipeline_filter_inner_list')) && $request->session()->get('vintage_candvisapipeline_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candvisapipeline_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				
				
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = EmpOffline::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = EmpOffline::whereRaw($whereraw)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
					
				}
				foreach($recruter_details as $_recruter_details)
				{
					//echo $_f->first_name;exit;
					$CandidateRecruiterArray[$_recruter_details->id] = $_recruter_details->name;
				}
				
				$CandidateNameArray = array();
				if($whereraw == '')
				{
					/* echo "sddf";
					exit; */
				$c_namedata = EmpOffline::where("ok_visa",3)->get();
				}
				else
				{
					
					$c_namedata = EmpOffline::whereRaw($whereraw)->where("ok_visa",3)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = EmpOffline::where("ok_visa",3)->get();
				}
				else
				{
					
					$email = EmpOffline::whereRaw($whereraw)->where("ok_visa",3)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = EmpOffline::where("ok_visa",3)->get();
				}
				else
				{
					
					$visa = EmpOffline::whereRaw($whereraw)->where("ok_visa",3)->get();
					
				}
				foreach($visa as $_company)
				{
					//echo $_f->first_name;exit;
					if($_company->company_visa!=''){
					$companyvisaArray[$_company->company_visa] = $_company->company_visa;
					}
				}
				
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = EmpOffline::where("ok_visa",3)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = EmpOffline::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = EmpOffline::whereRaw($whereraw)->where("ok_visa",3)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				
				$DesignationArray = array();
				if($whereraw == '')
				{
					$depidArray = EmpOffline::where("ok_visa",3)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = EmpOffline::whereRaw($whereraw)->where("ok_visa",3)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
					
				}
				foreach($desc as $_desc)
				{
					//echo $_f->first_name;exit;
					$DesignationArray[$_desc->id] = $_desc->name;
				}
				
				$OpeningArray = array();
				if($whereraw == '')
				{
				$jobArray = EmpOffline::where("ok_visa",3)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = EmpOffline::whereRaw($whereraw)->where("ok_visa",3)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
					$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
					
				}
				foreach($opening as $_opening)
				{
					//echo $_f->first_name;exit;
					//$OpeningArray[$_opening->id] = $_opening->name;
					$dept=Department::where("id",$_opening->department)->first();
					//echo $_f->first_name;exit;
					$OpeningArray[$_opening->id] = $_opening->name ." (".$dept->department_name." - ".$_opening->location.")";
				}
				$StatusArray = array();
				if($whereraw == '')
				{
				$status =  EmpOffline::where("ok_visa",3)->get();
				}
				else
				{
					$status =  EmpOffline::whereRaw($whereraw)->where("ok_visa",3)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = EmpOffline::where("ok_visa",3)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = EmpOffline::whereRaw($whereraw)->where("ok_visa",3)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
					$department =Department::whereIn('id',array_unique($dpetList))->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$DepartmentArray[$_dptname->id] = $_dptname->department_name;
				}
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::orderByRaw("-visa_expiry_date DESC")->whereRaw($whereraw)->where("ok_visa",3)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = EmpOffline::where("ok_visa",3)->orderByRaw("-visa_expiry_date DESC")->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = EmpOffline::whereRaw($whereraw)->where("ok_visa",3)->get()->count();
				}
				else
				{
					$reportsCount = EmpOffline::where("ok_visa",3)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingRequestedVisapipeline'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("EmpOfflineProcess/listingEmpOfflineProcessrequestedvisapipeline",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
		
	   public function CondationforLeavingPost(Request $request){
		   //print_r($request->input());exit;
			$keys = array_keys($_FILES);
					
					$filesAttributeInfo = array();
					$listOfAttribute = array();
					$newFileName='';
					$fileIndex = 0;
					foreach($keys as $key)
					{
						
						if(!empty($request->file($key)))
						{
						$filenameWithExt = $request->file($key)->getClientOriginalName ();
						$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
						$fileExtension =$request->file($key)->getClientOriginalExtension();
						$vKey = $key;
						$newFileName = $key.'-'.md5(uniqid()).'.'.$fileExtension;
						if(file_exists(public_path('OffboardDoc/'.$newFileName))){

							  unlink(public_path('OffboardDoc/'.$newFileName));

							}
						/*
						*Updating File Name
						*/
						$filesAttributeInfo[$vKey] = $newFileName;
						$listOfAttribute[] = $vKey;
						/*
						*Updating File Name
						*/
						// Get just Extension
						$extension = $request->file($key)->getClientOriginalExtension();
						// Filename To store
						$fileNameToStore = $filename. '_'. time().'.'.$extension;
						
						
						$request->file($key)->move(public_path('OffboardDoc/'), $newFileName);
						$fileIndex++;
						}
						else
						{
							
							$vKey = $keys[$fileIndex];
							$filesAttributeInfo[$vKey] = '';
							$listOfAttribute[] = $vKey;
							$fileIndex++;
							
						}
					}
					
			$rowId=$request->rowId;
			$leaving_type=$request->leaving_type;
			$date_of_joining=$request->date_of_joining;
	
			$detailsObj = EmpOffline::find($rowId);
			$detailsObj->condition_leaving = 2;
			$detailsObj->leaving_type = $leaving_type;
			if($newFileName!=''){
			$detailsObj->leaving_upload_doc =$newFileName;
			}
			/*$detailsObj->date_of_joining =$date_of_joining;
			$detailsObj->condition_leaving_date =date("Y-m-d H:i:s");	
			$detailsObj->condition_leavingBY =$request->session()->get('EmployeeId');				
			$detailsObj->date_of_resign=$request->input('date_of_resign');
			$detailsObj->last_working_day_resign=$request->last_working_day_resign;
			$detailsObj->date_of_terminate=$request->date_of_terminate;
			$detailsObj->last_working_day_terminate=$request->last_working_day_terminate;
			$detailsObj->reasons_of_terminate=$request->reasons_of_terminate;
			$detailsObj->reasons_for_leaving_resign=$request->input('reasonsForLeaving_resign');
			$detailsObj->reasons_for_leaving_terminate=$request->input('reasonsForLeaving_terminate');
			*/
			$detailsObj->leaving_type_status=1;
			$detailsObj->save();
			echo "Save Data..";
		}
	public function addExitInterviewData($offboardingId)
	{
		//$managerList=HiringManager::get();
		$offlinedata = EmpOffline::where("id",$offboardingId)->first();
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		return view("EmpOfflineProcess/InterviewForm",compact('offlinedata','jobRecruiterDetails','offboardingId'));
	}	
	public function addHRInterviewfrmPost(Request $rq)
	{
		//print_r($rq->input());exit;
			$formdata=$rq->input();
		
			$department=$rq->input('deptId');
			//$designation=$jobOpning->designation;
			$detailsObj = EmpOffline::find($rq->input('offboardingId'));
			$detailsObj->hr_interviewer_name = $rq->input('interviewer_name');
			$detailsObj->hr_description = $rq->input('description');
			
			$detailsObj->exit_interview_status = 2;
			if($rq->input('retain')==2){
			$detailsObj->condition_leaving = 3;
			}
			$detailsObj->retain=$rq->input('retain');
			$detailsObj->hr_status = $rq->input('status');
			$detailsObj->hr_interview_date =date("Y-m-d");	
			$detailsObj->hr_interview_createdBy =$rq->session()->get('EmployeeId');
			if($detailsObj->save()){
				
				if($rq->input('retain')==1){
				$detailsObj1 = EmpOffline::find($rq->input('offboardingId'));
				$detailsObj1->condition_leaving = 1;
				$detailsObj1->save();
				$offlinedata=EmpOffline::where("id",$rq->input('offboardingId'))->first();
				$empdata=Employee_details::where("emp_id",$offlinedata->emp_id)->first();
				if($empdata!=''){
					$empOBJ=Employee_details::find($empdata->id);
					$empOBJ->offline_status=1;
					$empOBJ->offboard_status=4;
					$empOBJ->save();
				}
				
				}
			}
			
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		
			
			echo json_encode($response);
		    exit;

	}
	public function addfinalInterviewfrmPost(Request $rq)
	{
		//print_r($rq->input());exit;
			$formdata=$rq->input();
		//echo $rq->input('offboardingId');exit;
			//$departmentname=$jobOpning->department;
			//$designation=$jobOpning->designation;
			$detailsObj = EmpOffline::find($rq->input('offboardingId'));			
			$detailsObj->final_interviewer_name = $rq->input('interviewer_name');
			$detailsObj->final_description = $rq->input('description');
			$detailsObj->final_status = $rq->input('status');
			
			$detailsObj->exit_interview_status = 2;
			
			if($rq->input('retain')==2){
			$detailsObj->condition_leaving = 3;
			}
			$detailsObj->retain=$rq->input('retain');
			$detailsObj->exit_interview_date =date("Y-m-d H:i:s");	
			$detailsObj->exit_interviewBY =$rq->session()->get('EmployeeId');
			if($detailsObj->save()){
				
				if($rq->input('retain')==1){
				$detailsObj1 = EmpOffline::find($rq->input('offboardingId'));
				$detailsObj1->condition_leaving = 1;
				$detailsObj1->save();
				$offlinedata=EmpOffline::where("id",$rq->input('offboardingId'))->first();
				$empdata=Employee_details::where("emp_id",$offlinedata->emp_id)->first();
				if($empdata!=''){
					$empOBJ=Employee_details::find($empdata->id);
					$empOBJ->offline_status=1;
					$empOBJ->offboard_status=4;
					$empOBJ->save();
				}
				
				}
			}
			
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		
			
			echo json_encode($response);
		    exit;

	}
	public function addInterviewfrmPost3(Request $rq)
	{
		//print_r($rq->input());exit;
			$formdata=$rq->input();
		//echo $rq->input('offboardingId');exit;
			//$departmentname=$jobOpning->department;
			//$designation=$jobOpning->designation;
			$detailsObj = EmpOffline::find($rq->input('offboardingId'));			
			$detailsObj->interviewer_name_3 = $rq->input('interviewer_name');
			$detailsObj->description_3 = $rq->input('description');
			$detailsObj->status_3 = $rq->input('status');
			
			$detailsObj->exit_interview_status = 2;
			
			if($rq->input('retain')==2){
			$detailsObj->condition_leaving = 3;
			}
			$detailsObj->retain=$rq->input('retain');
			$detailsObj->exit_interview_date_3 =date("Y-m-d H:i:s");	
			$detailsObj->exit_interviewBY_3 =$rq->session()->get('EmployeeId');
			if($detailsObj->save()){
				
				if($rq->input('retain')==1){
				$detailsObj1 = EmpOffline::find($rq->input('offboardingId'));
				$detailsObj1->condition_leaving = 1;
				$detailsObj1->save();
				$offlinedata=EmpOffline::where("id",$rq->input('offboardingId'))->first();
				$empdata=Employee_details::where("emp_id",$offlinedata->emp_id)->first();
				if($empdata!=''){
					$empOBJ=Employee_details::find($empdata->id);
					$empOBJ->offline_status=1;
					$empOBJ->offboard_status=4;
					$empOBJ->save();
				}
				
				}
			}
			
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		
			
			echo json_encode($response);
		    exit;

	}
		public function addInterviewfrmPost4(Request $rq)
	{
		//print_r($rq->input());exit;
			$formdata=$rq->input();
		//echo $rq->input('offboardingId');exit;
			//$departmentname=$jobOpning->department;
			//$designation=$jobOpning->designation;
			$detailsObj = EmpOffline::find($rq->input('offboardingId'));			
			$detailsObj->interviewer_name_4 = $rq->input('interviewer_name');
			$detailsObj->description_4 = $rq->input('description');
			$detailsObj->status_4 = $rq->input('status');
			
			$detailsObj->exit_interview_status = 2;
			if($rq->input('status')==1){
			$detailsObj->condition_leaving = 3;
			}
			if($rq->input('retain')==2){
			$detailsObj->condition_leaving = 3;
			}
			$detailsObj->retain=$rq->input('retain');
			$detailsObj->exit_interview_date_4 =date("Y-m-d H:i:s");	
			$detailsObj->exit_interviewBY_4 =$rq->session()->get('EmployeeId');
			if($detailsObj->save()){
				
				if($rq->input('retain')==1){
				$detailsObj1 = EmpOffline::find($rq->input('offboardingId'));
				$detailsObj1->condition_leaving = 1;
				$detailsObj1->save();
				$offlinedata=EmpOffline::where("id",$rq->input('offboardingId'))->first();
				$empdata=Employee_details::where("emp_id",$offlinedata->emp_id)->first();
				if($empdata!=''){
					$empOBJ=Employee_details::find($empdata->id);
					$empOBJ->offline_status=1;
					$empOBJ->offboard_status=4;
					$empOBJ->save();
				}
				
				}
			}
			
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		
			
			echo json_encode($response);
		    exit;

	}
	public function addFullandFinalSettelement($offboardingId)
	{
		$offlinedata = EmpOffline::where("id",$offboardingId)->first();
		$Settelementdata=SettelementAttribute::where("status",1)->where("attribute_type","Hr")->get();
		$Settelementdatafinance=SettelementAttribute::where("status",1)->where("attribute_type","Finance")->get();
		$CompanyAssets=CompanyAssets::where("status",1)->get();
		$SettelementCheckList=SettelementCheckList::where("status",1)->get();
		$Settelementattribute = SettelementAttributes::where('offline_id',$offboardingId)->get();
		$attributedata = OffboardEMPData::where('emp_id',$offboardingId)->first();
		$SettelementLogs=SettelementLogs::where('offboard_id',$offboardingId)->get();
		return view("EmpOfflineProcess/SettelementForm",compact('Settelementdatafinance','SettelementLogs','attributedata','SettelementCheckList','CompanyAssets','Settelementdata','offboardingId','offlinedata','Settelementattribute'));
	}
public function SettelementfromPost(Request $rq)
	{
		
			//$formdata=$rq->input();
			$attributesValues = $rq->input();
			
			$keys = array_keys($_FILES);
					
					$filesAttributeInfo = array();
					$listOfAttribute = array();
					$newFileName='';
					$fileIndex = 0;
					foreach($keys as $key)
					{
						//echo $rq->file($key);exit;
						if(!empty($rq->file($key)))
						{
							if($key=="filearea_upload"){
							$filenameWithExt = $rq->file($key)->getClientOriginalName ();
							$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
							$fileExtension =$rq->file($key)->getClientOriginalExtension();
							$vKey = $key;
							$newFileName = $key.'-'.md5(uniqid()).'.'.$fileExtension;
							if(file_exists(public_path('OffboardDoc/'.$newFileName))){

								  unlink(public_path('OffboardDoc/'.$newFileName));

								}
							/*
							*Updating File Name
							*/
							$filesAttributeInfo[$vKey] = $newFileName;
							$listOfAttribute[] = $vKey;
							/*
							*Updating File Name
							*/
							// Get just Extension
							$extension = $rq->file($key)->getClientOriginalExtension();
							// Filename To store
							$fileNameToStore = $filename. '_'. time().'.'.$extension;
							
							
							$rq->file($key)->move(public_path('OffboardDoc/'), $newFileName);
							$fileIndex++;
							
							$detailsObjimg = EmpOffline::find($rq->input('offboardingId'));
							if($newFileName!=''){
							$detailsObjimg->upload_doc =$newFileName;
							$detailsObjimg->save();
							}
							}
							
							else if($key=="filearea_upload1"){
							$filenameWithExt = $rq->file($key)->getClientOriginalName ();
							$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
							$fileExtension =$rq->file($key)->getClientOriginalExtension();
							$vKey = $key;
							$newFileName = $key.'-'.md5(uniqid()).'.'.$fileExtension;
							if(file_exists(public_path('OffboardDoc/'.$newFileName))){

								  unlink(public_path('OffboardDoc/'.$newFileName));

								}
							/*
							*Updating File Name
							*/
							$filesAttributeInfo[$vKey] = $newFileName;
							$listOfAttribute[] = $vKey;
							/*
							*Updating File Name
							*/
							// Get just Extension
							$extension = $rq->file($key)->getClientOriginalExtension();
							// Filename To store
							$fileNameToStore = $filename. '_'. time().'.'.$extension;
							
							
							$rq->file($key)->move(public_path('OffboardDoc/'), $newFileName);
							$fileIndex++;
							
							$detailsObjimg = EmpOffline::find($rq->input('offboardingId'));
							if($newFileName!=''){
							$detailsObjimg->company_liabilities =$newFileName;
							$detailsObjimg->save();
							}
							
							}
							
						}
							else
							{
								
								$vKey = $keys[$fileIndex];
								$filesAttributeInfo[$vKey] = '';
								$listOfAttribute[] = $vKey;
								$fileIndex++;
								
							}
						
					}
					

			$detailsObj = EmpOffline::find($rq->input('offboardingId'));
			$detailsObj->checklist = $rq->input('CheckList');
			$detailsObj->companyassets = $rq->input('Assets');
			$detailsObj->salary_paid = $rq->input('salary_paid_total');
			$detailsObj->salary_deduction = $rq->input('salary_deduction_total');
			$detailsObj->salary_total = $rq->input('salary_total');
			$detailsObj->LiabilityAmount = $rq->input('LiabilityAmount');
			$detailsObj->ReceivingAmount = $rq->input('ReceivingAmount');
			
			$detailsObj->condition_leaving = 4;
			$detailsObj->settelement_date=date("Y-m-d");	
			$detailsObj->settelementBY =$rq->session()->get('EmployeeId');
			if($detailsObj->save()){
			$existDocument = OffboardEMPData::where("emp_id",$rq->input('offboardingId'))->first();
				if($existDocument != '')
				{
					$offboarddata= OffboardEMPData::find($existDocument->id);
				}
				else
				{
				$offboarddata=new OffboardEMPData();	
				}	
			
			$offboarddata->emp_id=$rq->input('offboardingId');
			$offboarddata->settelement_hr_status = 1;
			$offboarddata->settelement_hr_date=date("Y-m-d H:i:s");	
			$offboarddata->settelement_hr_createdBY =$rq->session()->get('EmployeeId');
			$offboarddata->save();
			
			unset($attributesValues['_token']);
			unset($attributesValues['_url']);
			unset($attributesValues['CheckList']);
			unset($attributesValues['Assets']);
			unset($attributesValues['salary_paid_total']);
			unset($attributesValues['salary_deduction_total']);
			unset($attributesValues['salary_total']);
			unset($attributesValues['offboardingId']);
			unset($attributesValues['LiabilityAmount']);
			unset($attributesValues['ReceivingAmount']);
			unset($attributesValues['filearea_upload']);
			//print_r($attributesValues);
			foreach($attributesValues as $key=>$value)
			{
			if($value!=''){
			$empattributesMod = SettelementAttributes::where('offline_id',$rq->input('offboardingId'))->where('attribute_code',$key)->first();
											
						if(!empty($empattributesMod))
						{					
						$attributeObj = SettelementAttributes::find($empattributesMod->id);
						}
						else
						{
							$attributeObj=new SettelementAttributes();
						}
			
			$attributeObj->offline_id=$rq->input('offboardingId');
			$attributeObj->attribute_code=$key;
			$attributeObj->attribute_value=$value;
			$attributeObj->status=1;
			$attributeObj->attribute_status=1;
			$attributeObj->createBy=$rq->session()->get('EmployeeId');
			if($attributeObj->save()){
				
					$logObj = new SettelementLogs();
					$logObj->offboard_id =$rq->input('offboardingId');
					$logObj->created_by=$rq->session()->get('EmployeeId');
					$logObj->title =$key;
					$logObj->response =$value;
					$logObj->category ="HR";
					$logObj->save();
			}
				}
			}
			
			}
			
			
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		
			
			echo json_encode($response);
		    exit;
			

	}
	public function addVisaCancellation($offboardingId)
	{
		$offlinedata = EmpOffline::where("id",$offboardingId)->first();
		return view("EmpOfflineProcess/VisaCancellationForm",compact('offboardingId','offlinedata'));
	}
public function VisaCancellationFormPost(Request $rq)
	{
		
			$formdata=$rq->input();
			$detailsObj = EmpOffline::find($rq->input('offboardingId'));			
			$detailsObj->visacancellation = $rq->input('visacancellation');
			$detailsObj->visacancellation_date =date("Y-m-d H:i:s");	
			$detailsObj->visacancellationBY =$rq->session()->get('EmployeeId');
			$detailsObj->save();
			
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		
			
			echo json_encode($response);
		    exit;
			

	}	
public function addQuestionnaireDataData($offboardingId)
	{
		//$managerList=HiringManager::get();
		$offlinedata = EmpOffline::where("id",$offboardingId)->first();
		$attributeDetail = Question::where("attrbute_type",1)->where('status',1)->get();
		//$Questionanswer=QuestionForLeaving::where("offline_id",$offboardingId)->orderBy("id","DESC")->get();
		$Questionanswer=QuestionForLeaving::groupBy('question_id')->selectRaw('count(*) as total, question_id')->get();
		
		if($Questionanswer!=''){
			$qanswerData=array();
			foreach($Questionanswer as $_answer){
				$answer=QuestionForLeaving::where("offline_id",$offboardingId)->where("question_id",$_answer->question_id)->get();
				if($answer!='' && count($answer)>0){
				$qanswerData[$_answer->question_id]=$answer;
				}
			}
			
		}
		//echo "<pre>";
		//print_r($qanswerData);exit;
		//$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		return view("EmpOfflineProcess/QuestionForm",compact('offlinedata','offboardingId','attributeDetail','qanswerData'));
	}
public function addQuestionDataSavePost(Request $rq)
	{
		//print_r($rq->input());
		$offlineId=$rq->input('offboardingId');
			$question_id=$rq->input('question_id');
			foreach($question_id as $_question){
				$ans=$rq->input($_question);
				if(!is_null($ans)){	
				if(count($ans)>0){
				foreach($ans as $_ans){
					$questinObj = new QuestionForLeaving();
					$questinObj->question_answer = $_ans;
					$questinObj->question_id = $_question;
					$questinObj->offline_id = $rq->input('offboardingId');
					$questinObj->createBy = $rq->session()->get('EmployeeId');
					$questinObj->question_for_leaving_status = 1;
					$questinObj->save();
				}	
				}
				}
			}
			
			
			
			$detailsObj = EmpOffline::find($rq->input('offboardingId'));			
			$detailsObj->exit_interview_status = 1;
			$detailsObj->exit_interview_question_status = 1;
			$detailsObj->question_submit_date =date("Y-m-d H:i:s");	
			$detailsObj->question_submit_by =$rq->session()->get('EmployeeId');
			$detailsObj->save();
			
			
			session()->flash('message', 'Thank you! We wish you all the best!'); 
			return redirect("/QuestionnaireDataExternalLink/$offlineId");
			

	}
	
	public function QuestionnaireDataExternalLink(Request $request){
		 $offboardingId =  $request->offboardingId;
		 $offlinedata = EmpOffline::where("id",$offboardingId)->first();
		 $attributeDetail = Question::where("attrbute_type",1)->where('status',1)->get();
		return view("EmpOfflineProcess/QuestionFormExternal",compact('offboardingId','attributeDetail','offlinedata'));  
	   }
 public static function getQuestionName($questionId)
	   {
		   $question=Question::where("id",$questionId)->first();
		   if($question!=''){
			   return $question->question;
		   }
		   else{
			    return "--";
		   }
		   
	   }
		public static function getAttributeListValueData($offlineId,$attributecode)
			{	
			
			  $attr = SettelementAttributes::where('offline_id',$offlineId)->where("attribute_code",$attributecode)->first();
			  
			  if($attr != '')
			  {
			  return $attr->attribute_value;
			  }
			  else
			  {
				$data=explode("_",$attributecode);
				array_pop($data);
				array_push($data,'hr');
				$val=implode("_",$data);				
				$attrelsedata = SettelementAttributes::where('offline_id',$offlineId)->where("attribute_code",$val)->first();
			  
			  if($attrelsedata != '')
			  {
			  return $attrelsedata->attribute_value;
			  }	
			  else{
				  return '';
			  }			
		
			  
			  }
			}	   
	public function SettelementDataFinancePost(Request $rq)
	{
		
			//$formdata=$rq->input();
			print_r($formdata=$rq->input());
			$attributesValues = $rq->input();
			$detailsObj = EmpOffline::find($rq->input('offboardingId'));
			$detailsObj->checklist = $rq->input('CheckList');
			$detailsObj->companyassets = $rq->input('Assets');
			$detailsObj->condition_leaving = 4;
			
			if($detailsObj->save()){
				
			$empdata = OffboardEMPData::where('emp_id',$rq->input('offboardingId'))->first();
										
					if(!empty($empdata))
					{					
					$offboarddata = OffboardEMPData::find($empdata->id);
					}
					else
					{
						$offboarddata=new OffboardEMPData();
					}
			
			$offboarddata->emp_id=$rq->input('offboardingId');
			$offboarddata->settelement_finance_status = 1;
			$offboarddata->settelement_finance_date=date("Y-m-d");	
			$offboarddata->settelement_finance_createdBY =$rq->session()->get('EmployeeId');
			
			$offboarddata->LiabilityAmount_finance=$rq->input('LiabilityAmount_finance');
			$offboarddata->salary_paid_total_finance=$rq->input('salary_paid_total_finance');
			$offboarddata->ReceivingAmount_finance=$rq->input('ReceivingAmount_finance');
			$offboarddata->salary_deduction_total_finance=$rq->input('salary_deduction_total_finance');
			$offboarddata->finance_payment_confirmation_date=$rq->input('finance_payment_confirmation_date');
			$offboarddata->finance_payment_confirmation_note=$rq->input('finance_payment_confirmation_note');
			$offboarddata->save();
			
			unset($attributesValues['_token']);
			unset($attributesValues['_url']);
			unset($attributesValues['CheckList']);
			unset($attributesValues['Assets']);
			unset($attributesValues['salary_paid_total']);
			unset($attributesValues['salary_deduction_total']);
			unset($attributesValues['salary_total']);
			unset($attributesValues['offboardingId']);
			unset($attributesValues['LiabilityAmount_finance']);
			unset($attributesValues['salary_paid_total_finance']);
			unset($attributesValues['ReceivingAmount_finance']);
			unset($attributesValues['salary_deduction_total_finance']);
			unset($attributesValues['finance_payment_confirmation_date']);
			unset($attributesValues['finance_payment_confirmation_note']);
			//print_r($attributesValues);
			foreach($attributesValues as $key=>$value)
			{
			if($value!=''){
			$empattributesMod = SettelementAttributes::where('offline_id',$rq->input('offboardingId'))->where('attribute_code',$key)->first();
											
						if(!empty($empattributesMod))
						{					
						$attributeObj = SettelementAttributes::find($empattributesMod->id);
						}
						else
						{
							$attributeObj=new SettelementAttributes();
						}
			
			$attributeObj->offline_id=$rq->input('offboardingId');
			$attributeObj->attribute_code=$key;
			$attributeObj->attribute_value=$value;
			$attributeObj->status=1;
			$attributeObj->attribute_status=2;
			$attributeObj->createBy=$rq->session()->get('EmployeeId');
			if($attributeObj->save()){
				$logObj = new SettelementLogs();
					$logObj->offboard_id =$rq->input('offboardingId');
					$logObj->created_by=$rq->session()->get('EmployeeId');
					$logObj->title =$key;
					$logObj->response =$value;
					$logObj->category ="Finance";
					$logObj->save();
			}
				}
			}
			
			}
			
			
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		
			
			echo json_encode($response);
		    exit;
			

	} 
public function SettelementDataConfirmationPost(Request $rq){
	//print_r($rq->input());exit;
	
			$attributesValues = $rq->input();
			$keys = array_keys($_FILES);
					
					$filesAttributeInfo = array();
					$listOfAttribute = array();
					$newFileName='';
					$fileIndex = 0;
					foreach($keys as $key)
					{
						
						if(!empty($rq->file($key)))
						{
						$filenameWithExt = $rq->file($key)->getClientOriginalName ();
						$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
						$fileExtension =$rq->file($key)->getClientOriginalExtension();
						$vKey = $key;
						$newFileName = $key.'-'.md5(uniqid()).'.'.$fileExtension;
						if(file_exists(public_path('OffboardDoc/'.$newFileName))){

							  unlink(public_path('OffboardDoc/'.$newFileName));

							}
						/*
						*Updating File Name
						*/
						$filesAttributeInfo[$vKey] = $newFileName;
						$listOfAttribute[] = $vKey;
						/*
						*Updating File Name
						*/
						// Get just Extension
						$extension = $rq->file($key)->getClientOriginalExtension();
						// Filename To store
						$fileNameToStore = $filename. '_'. time().'.'.$extension;
						
						
						$rq->file($key)->move(public_path('OffboardDoc/'), $newFileName);
						$fileIndex++;
						}
						else
						{
							
							$vKey = $keys[$fileIndex];
							$filesAttributeInfo[$vKey] = '';
							$listOfAttribute[] = $vKey;
							$fileIndex++;
							
						}
					} 			
			$empdata = OffboardEMPData::where('emp_id',$rq->input('offboardingId'))->first();
										
					if(!empty($empdata))
					{					
					$offboarddata = OffboardEMPData::find($empdata->id);
					}
					else
					{
						$offboarddata=new OffboardEMPData();
					}
			if($rq->input('confirmation')==1){
				$status='Complete';
			}
			else{
				$status='Inprocess';
			}
			$offboarddata->emp_id=$rq->input('offboardingId');
			$offboarddata->settelement_confirmation_status = $rq->input('confirmation');
			$offboarddata->settelement_confirmation_date=date("Y-m-d");	
			$offboarddata->settelement_confirmation_createdBY =$rq->session()->get('EmployeeId');
			if($newFileName!=''){
			$offboarddata->upload_doc =$newFileName;
			}
			
			$offboarddata->finance_payment_confirmation_amount =$rq->input('finance_payment_confirmation_amount');
			$offboarddata->payment_confirmation_note =$rq->input('payment_confirmation_note');
			$offboarddata->hrsettelement =$rq->input('hrsettelement');
			$offboarddata->financefettelement =$rq->input('financefettelement');
			
			if($offboarddata->save()){
					$logObj = new SettelementLogs();
					$logObj->offboard_id =$rq->input('offboardingId');
					$logObj->created_by=$rq->session()->get('EmployeeId');
					$logObj->title ="Update Confirmation ";
					$logObj->response =$status;
					$logObj->category ="Confirmation";
					$logObj->save();
			}
			$detailsObj = EmpOffline::find($rq->input('offboardingId'));
			$detailsObj->settelement_status = 4;
			$detailsObj->save();

			
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		
			
			echo json_encode($response);
		    exit;
}


public function SettelementDataConfirmationDisputePost(Request $rq){
	//print_r($rq->input());exit;
	
			$attributesValues = $rq->input();
			 			
			$empdata = OffboardEMPData::where('emp_id',$rq->input('offboardingId'))->first();
										
					if(!empty($empdata))
					{					
					$offboarddata = OffboardEMPData::find($empdata->id);
					}
					else
					{
						$offboarddata=new OffboardEMPData();
					}
			
			$offboarddata->emp_id=$rq->input('offboardingId');
			$offboarddata->dispute_status = 1;
			$offboarddata->dispute_date=date("Y-m-d");	
			$offboarddata->dispute_createdBY =$rq->session()->get('EmployeeId');
			
			if($offboarddata->save()){
					$logObj = new SettelementLogs();
					$logObj->offboard_id =$rq->input('offboardingId');
					$logObj->created_by=$rq->session()->get('EmployeeId');
					$logObj->title ="Dispute ";
					$logObj->response ="Dispute";
					$logObj->category ="Confirmation";
					$logObj->save();
			}
			$detailsObj = EmpOffline::find($rq->input('offboardingId'));
			$detailsObj->settelement_status = 4;
			$detailsObj->save();

			
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		
			
			echo json_encode($response);
		    exit;
}




public function offboardpaymentconfirm(Request $request)
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
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at> "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_a<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('empoffboard_ffstatus_filter_list')) && $request->session()->get('empoffboard_ffstatus_filter_list') != 'All')
				{
					$offboard_ffstatus = $request->session()->get('empoffboard_ffstatus_filter_list');
					
					 $offboard_ffstatusArray = explode(",",$offboard_ffstatus);
					 $offlinestatusdata= OffboardEMPData::whereIn('settelement_confirmation_status',$offboard_ffstatusArray)->get();
					 $ffstatusarray=array();
					 foreach($offlinestatusdata as $_ffstatus){
						 $ffstatusarray[]=$_ffstatus->emp_id;
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalffstatus=implode(",", $ffstatusarray);
					if($whereraw == '')
					{
						$whereraw = 'id IN('.$finalffstatus.')';
					}
					else
					{
						$whereraw .= ' And id IN('.$finalffstatus.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				if(!empty($request->session()->get('paymentconfirm_datefrom_offboard_lastworkingday_list')) && $request->session()->get('paymentconfirm_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('paymentconfirm_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('paymentconfirm_dateto_offboard_lastworkingday_list')) && $request->session()->get('paymentconfirm_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('paymentconfirm_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('paymentconfirm_datefrom_offboard_dort_list')) && $request->session()->get('paymentconfirm_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('paymentconfirm_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('paymentconfirm_dateto_offboard_dort_list')) && $request->session()->get('paymentconfirm_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('paymentconfirm_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
				if(!empty($request->session()->get('fnf_offboard_paymentconfirm_filter_list')) && $request->session()->get('fnf_offboard_paymentconfirm_filter_list') != 'All')
				{
					$amount = $request->session()->get('fnf_offboard_paymentconfirm_filter_list');
					
					 if($amount==1){
						$offlinestatusdata= OffboardEMPData::where('payment_confirmation_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($amount==2){
						 $offlinestatusdata= OffboardEMPData::where('payment_confirmation_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}	
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candmashreq_filter_inner_list')) && $request->session()->get('email_candmashreq_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candmashreq_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('desc_candmashreq_filter_inner_list')) && $request->session()->get('desc_candmashreq_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candmashreq_filter_inner_list');
					 $selectedFilter['DESC'] = $desc;
					 if($whereraw == '')
					{
						$whereraw = 'designation = "'.$desc.'"';
					}
					else
					{
						$whereraw .= ' And designation = "'.$desc.'"';
					}
				}
				if(!empty($request->session()->get('dept_candmashreq_filter_inner_list')) && $request->session()->get('dept_candmashreq_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candmashreq_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candmashreq_filter_inner_list')) && $request->session()->get('status_candmashreq_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candmashreq_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candmashreq_filter_inner_list')) && $request->session()->get('vintage_candmashreq_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candmashreq_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				
				
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = EmpOffline::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = EmpOffline::whereRaw($whereraw)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
					
				}
				foreach($recruter_details as $_recruter_details)
				{
					//echo $_f->first_name;exit;
					$CandidateRecruiterArray[$_recruter_details->id] = $_recruter_details->name;
				}
				
				$CandidateNameArray = array();
				if($whereraw == '')
				{
					/* echo "sddf";
					exit; */
				$c_namedata = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$c_namedata = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$email = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$visa = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($visa as $_company)
				{
					//echo $_f->first_name;exit;
					if($_company->company_visa!=''){
					$companyvisaArray[$_company->company_visa] = $_company->company_visa;
					}
				}
				
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = EmpOffline::where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = EmpOffline::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = EmpOffline::whereRaw($whereraw)->where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				
				$DesignationArray = array();
				if($whereraw == '')
				{
					$depidArray = EmpOffline::where("department",36)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
					
				}
				foreach($desc as $_desc)
				{
					//echo $_f->first_name;exit;
					$DesignationArray[$_desc->id] = $_desc->name;
				}
				
				$OpeningArray = array();
				if($whereraw == '')
				{
				$jobArray = EmpOffline::where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
					$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
					
				}
				foreach($opening as $_opening)
				{
					//echo $_f->first_name;exit;
					//$OpeningArray[$_opening->id] = $_opening->name;
					$dept=Department::where("id",$_opening->department)->first();
					//echo $_f->first_name;exit;
					$OpeningArray[$_opening->id] = $_opening->name ." (".$dept->department_name." - ".$_opening->location.")";
				}
				$StatusArray = array();
				if($whereraw == '')
				{
				$status =  EmpOffline::where("department",36)->get();
				}
				else
				{
					$status =  EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = EmpOffline::where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
					$department =Department::whereIn('id',array_unique($dpetList))->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$DepartmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::whereIn("condition_leaving",array(4,5,6))->whereRaw($whereraw)->orderBy("settelement_date", "DESC")->paginate($paginationValue);
				}
				else
				{
					
					$documentCollectiondetails = EmpOffline::whereIn("condition_leaving",array(4,5,6))->orderBy("settelement_date", "DESC")->paginate($paginationValue);
					
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = EmpOffline::whereIn("condition_leaving",array(4,5,6))->whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = EmpOffline::whereIn("condition_leaving",array(4,5,6))->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/offboardpaymentconfirm'));
				
		return view("EmpOfflineProcess/offboardpaymentconfirm",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
public function addpaymentconfirmation($offboardingId)
	{
		$offlinedata = EmpOffline::where("id",$offboardingId)->first();
		$attributedata = OffboardEMPData::where('emp_id',$offboardingId)->first();
		return view("EmpOfflineProcess/addpaymentconfirmationForm",compact('offboardingId','offlinedata','attributedata'));
	}
public function offboardPaymentConfirmPost(Request $rq){
	//print_r($rq->input());exit;
			$attributesValues = $rq->input();
			//print_r($_FILES);exit;
			$keys = array_keys($_FILES);
					
					$filesAttributeInfo = array();
					$listOfAttribute = array();
					$newFileName='';
					$fileIndex = 0;
					foreach($keys as $key)
					{
						
						if(!empty($rq->file($key)))
						{
						$filenameWithExt = $rq->file($key)->getClientOriginalName ();
						$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
						$fileExtension =$rq->file($key)->getClientOriginalExtension();
						$vKey = $key;
						$newFileName = $key.'-'.md5(uniqid()).'.'.$fileExtension;
						if(file_exists(public_path('OffboardDoc/'.$newFileName))){

							  unlink(public_path('OffboardDoc/'.$newFileName));

							}
						/*
						*Updating File Name
						*/
						$filesAttributeInfo[$vKey] = $newFileName;
						$listOfAttribute[] = $vKey;
						/*
						*Updating File Name
						*/
						// Get just Extension
						$extension = $rq->file($key)->getClientOriginalExtension();
						// Filename To store
						$fileNameToStore = $filename. '_'. time().'.'.$extension;
						
						
						$rq->file($key)->move(public_path('OffboardDoc/'), $newFileName);
						$fileIndex++;
						}
						else
						{
							
							$vKey = $keys[$fileIndex];
							$filesAttributeInfo[$vKey] = '';
							$listOfAttribute[] = $vKey;
							$fileIndex++;
							
						}
					}			
			$empdata = OffboardEMPData::where('emp_id',$rq->input('offboardingId'))->first();
										
					if(!empty($empdata))
					{					
					$offboarddata = OffboardEMPData::find($empdata->id);
					}
					else
					{
						$offboarddata=new OffboardEMPData();
					}
		
			$offboarddata->payment_date = $rq->input('payment_date');
			$offboarddata->payment_confirmation_status=$rq->input('payment_confirm');
			$offboarddata->payment_confirmation_created_date=date("Y-m-d");	
			$offboarddata->payment_confirmation_createdBY =$rq->session()->get('EmployeeId');
			$offboarddata->payment_comment =$rq->input('payment_Comment');
			if($newFileName!=''){
			$offboarddata->upload_doc =$newFileName;
			}
			
			$offboarddata->finance_payment_confirmation_amount =$rq->input('finance_payment_confirmation_amount');
			
			$offboarddata->save();
			if($rq->input('payment_confirm')==1){
			$offlinedata = EmpOffline::find($rq->input('offboardingId'));
			$offlinedata->condition_leaving=6;
			$offlinedata->save();
			}
			
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		
			
			echo json_encode($response);
		    exit;
}	
public function searchOffBoardData(Request $request)
		{
			//print_r($request->input());
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
			
			$request->session()->put('name_emp_offboard_filter_inner_list',$name);
			$request->session()->put('empid_emp_offboard_filter_inner_list',$empId);
			$request->session()->put('datefrom_offboard_filter_inner_list',$datefrom);
			$request->session()->put('dateto_offboard_filter_inner_list',$dateto);
			$request->session()->put('departmentId_filter_inner_list',$department);
			$request->session()->put('teamleader_filter_inner_list',$teamlaed);
			
			$request->session()->put('design_empoffboard_filter_inner_list',$design);
			$request->session()->put('dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			
			$request->session()->put('ReasonofAttrition_empoffboard_filter_list',$ReasonofAttrition);
			$request->session()->put('empoffboard_status_filter_list',$offboardstatus);
			$request->session()->put('datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('dateto_offboard_dort_list',$datetodort);
			$request->session()->put('empoffboard_ffstatus_filter_list',$offboardffstatus);
			 //return  redirect('listingPageonboarding');	
		}
		public function resetfilterOffboardData(Request $request){
			$request->session()->put('datefrom_offboard_filter_inner_list','');
			$request->session()->put('dateto_offboard_filter_inner_list','');
			$request->session()->put('departmentId_filter_inner_list','');
			$request->session()->put('teamleader_filter_inner_list','');
			$request->session()->put('name_emp_offboard_filter_inner_list','');
			$request->session()->put('empid_emp_offboard_filter_inner_list','');
			$request->session()->put('design_empoffboard_filter_inner_list','');
			$request->session()->put('dateto_offboard_lastworkingday_list','');
			$request->session()->put('datefrom_offboard_lastworkingday_list','');
			$request->session()->put('ReasonofAttrition_empoffboard_filter_list','');
			$request->session()->put('empoffboard_status_filter_list','');
			$request->session()->put('datefrom_offboard_dort_list','');
			$request->session()->put('dateto_offboard_dort_list','');
			$request->session()->put('empoffboard_ffstatus_filter_list','');
		}
		public function offboardDataFilterLeaving(Request $request)
		{
			//print_r($request->input());
			
			$type=$request->input('type');
			$datetolastworkingday = $request->input('datetolastworkingday');
			$datefromlastworkingday = $request->input('datefromlastworkingday');
			$datetodort = $request->input('datetodort');
			$datefromdort = $request->input('datefromdort');
			$request->session()->put('leaving_dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('leaving_datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			$request->session()->put('leaving_datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('leaving_dateto_offboard_dort_list',$datetodort);
			$request->session()->put('leaving_offboardtype_filter_inner_list',$type);
			
		}
		public function resetoffboardListDataFilterLeaving(Request $request){
			$request->session()->put('leaving_offboardtype_filter_inner_list','');
			$request->session()->put('leaving_datefrom_offboard_dort_list','');
			$request->session()->put('leaving_dateto_offboard_lastworkingday_list','');
			$request->session()->put('leaving_datefrom_offboard_lastworkingday_list','');
			
			$request->session()->put('dateto_offboard_dort_list','');
		}
		
		public function offboardDataFilterexit(Request $request)
		{
			//print_r($request->input());
			
			$exittype=$request->input('exittype');
			$retained=$request->input('retained');
			$datetolastworkingday = $request->input('datetolastworkingday');
			$datefromlastworkingday = $request->input('datefromlastworkingday');
			$datetodort = $request->input('datetodort');
			$datefromdort = $request->input('datefromdort');
			$request->session()->put('offboardexittype_filter_inner_list',$exittype);
			$request->session()->put('exit_dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('exit_datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			$request->session()->put('exit_datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('exit_dateto_offboard_dort_list',$datetodort);
			$request->session()->put('offboard_retained_filter_inner_list',$retained);
			
		}
		public function resetoffboardListDataFilterexit(Request $request){
			$request->session()->put('offboardexittype_filter_inner_list','');
			$request->session()->put('exit_dateto_offboard_lastworkingday_list','');
			$request->session()->put('exit_datefrom_offboard_lastworkingday_list','');
			$request->session()->put('exit_datefrom_offboard_dort_list','');
			$request->session()->put('exit_dateto_offboard_dort_list','');
			$request->session()->put('offboard_retained_filter_inner_list','');
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
		public static function getTitleName($code)
		{	

		   $attr = SettelementAttribute::where("code",$code)->first();
			  
			  if($attr != '')
			  {
			  return $attr->name;
			  }
			  else
			  {
			  return $code;
			  }
		}
		
		public static function getVintageData($empid = NULL)
	   {
		  
			//echo $empid;exit;
			$offboarddata=EmpOffline::where("emp_id",$empid)->first();
			if($offboarddata!=''){
			$lasworkingday='';
			if($offboarddata->last_working_day_resign!='' && $offboarddata->last_working_day_resign!=NULL){
				$lasworkingday=date("Y-m-d",strtotime($offboarddata->last_working_day_resign));
			}
			else if($offboarddata->last_working_day_terminate!='' && $offboarddata->last_working_day_terminate!=NULL){
				$lasworkingday=date("Y-m-d",strtotime($offboarddata->last_working_day_terminate));
			}
			else{
				$lasworkingday=date("Y-m-d",strtotime($offboarddata->created_at));
			}
			
	
				$empdat=Employee_details::where("emp_id",$empid)->first();		 
				if($empdat!='' && $empdat->doj!=''){
				 $doj=date("Y-m-d",strtotime(str_replace("/","-",$empdat->doj)));
				  }else{
					$doj='';  
				  }		
				
				if($doj !='' && $lasworkingday!=''){
					$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  $lasworkingday;

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 //echo   $returnData;


					 return $returnData;
				}
				else{
					return "--";
				}
			}
				else{
					return "--";
				}
   
	   }	
	public function OffBoardFullAndFinalStaus(Request $rq){
	//print_r($rq->input());exit;
			$attributesValues = $rq->input();
						
			$empdata = OffboardEMPData::where('emp_id',$rq->input('offboardingId'))->first();
										
					if(!empty($empdata))
					{					
					$offboarddata = OffboardEMPData::find($empdata->id);
					}
					else
					{
						$offboarddata=new OffboardEMPData();
					}
			
			$offboarddata->emp_id=$rq->input('offboardingId');
			$offboarddata->ff_hr_pending_status = $rq->input('hr_pending');
			$offboarddata->ff_finance_pending_status = $rq->input('finance_pending');
			$offboarddata->ff_confirmed_status = $rq->input('f_f_confirmed');
			$offboarddata->ff_paid_status = $rq->input('f_f_paid');
			$offboarddata->ff_status = $rq->input('status');
			
			$offboarddata->ff_status_date=date("Y-m-d");	
			$offboarddata->ff_status_createdBY =$rq->session()->get('EmployeeId');
			if($offboarddata->save()){
					$logObj = new SettelementLogs();
					$logObj->offboard_id =$rq->input('offboardingId');
					$logObj->created_by=$rq->session()->get('EmployeeId');
					$logObj->title ="Update F&F Status ";
					$logObj->response =$rq->input('status');
					$logObj->category ="F&F Status";
					$logObj->save();
			}
			$detailsObj = EmpOffline::find($rq->input('offboardingId'));
			$detailsObj->settelement_status = 4;
			$detailsObj->save();

			
			$response['code'] = '200';
			$response['message'] = "F&F Status Save  Successfully.";		
			
			echo json_encode($response);
		    exit;
}	
public static function getFAndFData($id){
	$empdata = OffboardEMPData::where('emp_id',$id)->first();
	$ststusdata='';
	if($empdata!=''){
				if($empdata->ff_hr_pending_status!=''){
					$ststusdata .= "<p>HR Pending</p>";
				}
				if($empdata->ff_finance_pending_status!=''){
				$ststusdata .= "<p>Finance Pending</p>";
				}
				
				if($empdata->ff_confirmed_status!=''){
				$ststusdata .= "<p>F&F Confirmed</p>";
				}
				
				if($empdata->ff_paid_status!=''){
					
				$ststusdata .= "<p>F&F Paid</p>";
				}
				return $ststusdata;
	}
	else{
		return "-";
	}
}
public static function getsSttelementHr($id)
		{	

		   $attr = OffboardEMPData::where("emp_id",$id)->first();
			  
			  if($attr != '')
			  {
			  return $attr->settelement_hr_status;
			  }
			  else
			  {
			  return '';
			  }
		}
	public static function getsSttelementFinance($id)
		{	

		   $attr = OffboardEMPData::where("emp_id",$id)->first();
			  
			  if($attr != '')
			  {
			  return $attr->settelement_finance_status;
			  }
			  else
			  {
			  return '';
			  }
		}
	public static function getsSttelementPayment($id)
		{	
			$offboarddata=EmpOffline::where("id",$id)->first();
		   $attr = OffboardEMPData::where("emp_id",$id)->first();
			  
			  if($attr != '')
			  {
				 if(trim($offboarddata->salary_deduction)==trim($attr->salary_deduction_total_finance)) {
				return 1;
				 }else{
					return 2; 
				 }
			  }
			  else
			  {
			  return '';
			  }
		}
		public static function getsSttelementPaymentDispute($id)
		{	
			
		   $attr = OffboardEMPData::where("emp_id",$id)->first();
			  
			  if($attr != '')
			  {
				 
				return $attr->dispute_status;
				 
			  }
			  else
			  {
			  return '';
			  }
		}
		public static function getsSttelementPaymentStatus($id)
		{	
			
		   $attr = OffboardEMPData::where("emp_id",$id)->first();
			  
			  if($attr != '')
			  {
				 
				return $attr->payment_confirmation_status;
				 
			  }
			  else
			  {
			  return '';
			  }
		}
		public static function getsSttelementPaymentPaid($id){
			$attr = OffboardEMPData::where("emp_id",$id)->first();
			  
			  if($attr != '')
			  {
				 
				return $attr->payment_confirmation_status;
				 
			  }
			  else
			  {
			  return '';
			  }
		}
	public function getleavingTypePopupData($offboardId)
	{
		$offlinedata = EmpOffline::where("id",$offboardId)->first();
		
		return view("EmpOfflineProcess/LeavingType",compact('offboardId','offlinedata'));
	}
	public function exportEmpOffBoardReport(Request $request){
		$parameters = $request->input(); 
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'offboard_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet(); 
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:W1');
			$sheet->setCellValue('A1', 'EMP List - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			
			$currentmonthdate = date("Y-m-d");
			$currentmDate = date('M', strtotime('-1 month', strtotime($currentmonthdate)));
			$LMoth = date('M', strtotime('first day of -2 month', strtotime($currentmonthdate)));
			$LofMoth = date('M', strtotime('-3 month', strtotime($currentmonthdate)));
			
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Contact Number'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Off boarding Date'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Last Working Date'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Date of Resign/Termination'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Department'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Designation'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('TL Name'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Reason of Attrition'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Retain Status'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Status'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Tenure'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Date of joing'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Visa Expens'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Recruiter'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Interview Final Discussion'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Performance Month ('.$currentmDate.")"))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('Performance Month ('.$LMoth.")"))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('Performance Month ('.$LofMoth.")"))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('Range Id'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('Suggestion'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				//echo $sid;
				 $misData = EmpOffline::where("id",$sid)->first();
				 $tldata=$misData->tl_se;
				 $tlname='';
				 if($tldata!=''){
				 $tld=Employee_details::where("id",$tldata)->first();
				 if($tld!=''){
				 $tlname=$tld->first_name.' '.$tld->last_name;
				 }else{
					$tlname=''; 
				 }
				 }
				  $offboarddate=date("d-M-Y",strtotime(str_replace("/","-",$misData->created_at)));
				  if($misData->last_working_day_resign!=''){
				 $lastworking=date("d-M-Y",strtotime(str_replace("/","-",$misData->last_working_day_resign)));
				  }elseif($misData->last_working_day_terminate!=''){
					 $lastworking=date("d-M-Y",strtotime(str_replace("/","-",$misData->last_working_day_terminate)));  
				  }
				  else{
					 $lastworking=''; 
				  }
				  if($misData->date_of_resign!=''){
				 $dateofresign=date("d-M-Y",strtotime(str_replace("/","-",$misData->date_of_resign)));
				  }elseif($misData->date_of_terminate!=''){
					 $dateofresign=date("d-M-Y",strtotime(str_replace("/","-",$misData->date_of_terminate)));  
				  }
				  else{
					 $dateofresign=''; 
				  }
				 $designationMod = Designation::where("id",$misData->designation)->first();
					if($designationMod != '')
					  {
					  $designation= $designationMod->name;
					  
					  }
					  else{
						 $designation=''; 
					  }
				 $indexCounter++; 	
				 $departmentMod = Department::where("id",$misData->department)->first();
				 if($departmentMod!=''){
				 $deptname=$departmentMod->department_name;
				}else{
					$deptname='';
				}
				if($misData->leaving_type==1){
						$Reason="Resign";
				}else if($misData->leaving_type==2){
					$Reason="Terminate";
				}
				else{
					$Reason="";
				}
				
				if($misData->retain == 1){
					 $Retain="Retained";					
					}else{	
					$Retain="Not Retained";
					}
				$Status='';
				$attr = OffboardEMPData::where("emp_id",$misData->id)->first();
			  
				  if($attr != '')
				  {
				  $hrstatus =$attr->settelement_hr_status;
				  $financestatus= $attr->settelement_finance_status;
				  $paymentstatusdispute=$attr->dispute_status;
				  $paymentpaid=$attr->payment_confirmation_status;
				  }
				  else
				  {
				  $hrstatus ='';
				  $financestatus='';
				  $paymentstatusdispute='';
				  $paymentpaid='';
				  

				  }
				  
				$offboarddata=EmpOffline::where("id",$misData->id)->first();
			  
			  if($attr != '')
			  {
				 if($offboarddata->salary_deduction==$attr->salary_deduction_total_finance) {
				$paymentstatus=1;
				 }else{
					$paymentstatus= 2; 
				 }
			  }
			  	
				

				 
				if($hrstatus == '' && $financestatus == ''){
					 $status="F&F Calculation Pending \n";					
				}else if($paymentstatus == 1){
					 $status="Amount Confirmed \n";
				}else if($paymentstatusdispute == 1 && $paymentstatus != 1){
					 $status=" F&F Calculation Disputed \n";
				}else if($paymentpaid==1){
					$status=" F&F Calculation Paid \n";
				}else{
					 $status="F&F Calculation Pending \n";	
				}
					if($misData->condition_leaving == 4){	
					$status1=" Visa Cancellation Pending \n";
				    }else if($misData->condition_leaving == 5){
					$status1= "Visa Cancellation Complete \n";
					}else{
					$status1='';
					}
					 
					if($misData->last_working_day_resign!='' && $misData->last_working_day_resign!=NULL){
					$lwd=date("Y-m-d",strtotime($misData->last_working_day_resign));
					}else if($misData->reasons_for_leaving_terminate!=''&& $misData->reasons_for_leaving_terminate!=NULL){
					$lwd=date("Y-m-d",strtotime($misData->reasons_for_leaving_terminate));
					}
					else{
					$lwd='';	
					}
				 //echo $lwd;
				 if($lwd!=''){
					$lwd = str_replace("/","-",$lwd);
					$date1 = date("Y-m-d",strtotime($lwd));

					$date2 =  date("Y-m-d");

					 $diff = strtotime($date2)-strtotime($date1);
					 $days = floor($diff / (60 * 60 * 24));
				 }
				 else{
					$days=0; 
				 }
				
						 if($days>=30){
						$status2="Last Working Day Complete \n";
						 }else{
						$status2="Last Working Day Incomplete \n";
						 }
						
					if($misData->exit_interview_status == '' || $misData->exit_interview_status ==NULL){
					 $status3="Questionnaire Pending \n";
					}else if($misData->exit_interview_status == 1){
						$status3="Questionnaire Complete and Waiting Interview \n";
					}else if($misData->exit_interview_status == 2){
					$status3="Interview Complete";
					}	
					if($misData->condition_leaving == 1){
					$status4="Condition for Leaving Not Selected \n";
					}else{
					$status4="Condition for Leaving Selected \n";
					}
						
					$finalstatus=$status4. ' '.$status3." ".$status." ".$status1;	
					$empsessionId=$request->session()->get('EmployeeId');
				$jobfunctiondetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				 //echo $jobfunctiondetails->job_function_id;exit;
				 if($jobfunctiondetails!='' && ($jobfunctiondetails->job_function_id==3 || $jobfunctiondetails->job_function_id==4)){
					$LocalContactNumber='';
					$basicSalary='';
				 }
				 else{
				 $LocalContactNumber=$misData->mobile_no;
				 }
		
			
			//$lasworkingday='';
			if($misData->last_working_day_resign!='' && $misData->last_working_day_resign!=NULL){
				$lasworkingday=date("Y-m-d",strtotime($misData->last_working_day_resign));
			}
			else if($misData->last_working_day_terminate!='' && $misData->last_working_day_terminate!=NULL){
				$lasworkingday=date("Y-m-d",strtotime($misData->last_working_day_terminate));
			}
			else{
				$lasworkingday=date("Y-m-d",strtotime($misData->created_at));
			}
			
	
				$empdat=Employee_details::where("emp_id",$misData->emp_id)->first();		 
				if($empdat!='' && $empdat->doj!=''){
				 $doj=date("Y-m-d",strtotime(str_replace("/","-",$empdat->doj)));
				  }else{
					$doj='';  
				  }	
				
				if($doj !='' && $lasworkingday!=''){
					$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  $lasworkingday;

						$diff = abs(strtotime($date2)-strtotime($date1));
						$numberDays = round($diff / (60 * 60 * 24));
						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 //echo   $returnData;


					 $vintage= $returnData;
				}
				else{
					$vintage= "--";
					$numberDays='';
				}
				if($numberDays<710){
				$worktime=RangeDetailsVintage::where("vintage",$numberDays)->first();
				if($worktime!=''){
				$worktimedata=$worktime->range_id;	
				}
				}else{
					$worktimedata=24;
				}
				$empdat=Employee_details::where("emp_id",$misData->emp_id)->first();		 
				if($empdat!='' && $empdat->doj!=''){
				 $dojdata=date("d-M-Y",strtotime(str_replace("/","-",$empdat->doj)));
				  }else{
					$dojdata='';  
				  }
			
			if($misData->document_collection_id!='' ){
			$visas = Visaprocess::where("document_id",$misData->document_collection_id)->sum('cost');
			$visasfine = Visaprocess::where("document_id",$misData->document_collection_id)->sum('cost_fine');	
				$expens=$visas+$visasfine;	
						
			}
			else{
				$data=VisaExpensesDetailsUpdated::where("Employee_id",$misData->emp_id)->first();
				if($data!=''){
				$expens=$data->employeer_visa_cost;	
				}
				else{
					$expens= 0;
				}
			}
			$empdatdata=Employee_details::where("emp_id",$misData->emp_id)->first();
			if($empdatdata!=''){
				$recuter=RecruiterDetails::where("id",$empdatdata->recruiter)->first();
				if($recuter!=""){
				$recuter=$recuter->name;
				}
				else{
					$recuter='';
				}
			}
			else{
					$recuter='';
				}
			if($misData->document_collection_id!=''){
				//echo $misData->document_collection_id."<br>";
			$interview=DocumentCollectionDetails::where("id",$misData->document_collection_id)->first();
				if($interview!=''){
				$interview_id=$interview->interview_id;
				$interviewdata=InterviewDetailsProcess::where("interview_id",$interview_id)->where("interview_type","final discussion")->first();
					if($interviewdata!=''){
					$interviewr= RecruiterDetails::where("id",$interviewdata->interviewer_name)->first()->name;	
					}
					else{
						$interviewr='';
					}
				}
				else{
				$interviewr='';
				}
			}
			else{
				$interviewr='';
			}
				//$ctoDate= $lastmonthdate= date('n-Y', strtotime('first day of last month'));
			$ctoDatec = date("Y-m-d"); // The date to get the previous month from, in YYYY-MM-DD format
			 $ctoDate = date('n-Y', strtotime('-1 month', strtotime($ctoDatec)));
				
			$currentmonth=MasterPayout::where("sales_time",$ctoDate)->where("employee_id",$misData->emp_id)->first();
			if($currentmonth!=''){
			$currentmonthdata=$currentmonth->total_revenue;;	
			}
			else{
				$currentmonthdata=0;
			}
			
			$lastdateString = date("Y-m-d"); // The date to get the previous month from, in YYYY-MM-DD format
			//$lastmonthdate = date('n-Y', strtotime('-2 month', strtotime($lastdateString)));
			$lastmonthdate= date('n-Y', strtotime('first day of -2 month', strtotime($lastdateString)));
			//echo $lastmonthdate;exit;
			
			$last1month=MasterPayout::where("sales_time",$lastmonthdate)->where("employee_id",$misData->emp_id)->first();
			if($last1month!=''){
				$last1monthdata=$last1month->total_revenue;
			}else{
			$last1monthdata=0;	
			}
			$dateString = date("Y-m-d"); // The date to get the previous month from, in YYYY-MM-DD format
			 $prevMonth = date('n-Y', strtotime('-3 month', strtotime($dateString)));
			//echo date('Y-m-d"',strtotime('-3 months'));
			$last2month=MasterPayout::where("sales_time",$prevMonth)->where("employee_id",$misData->emp_id)->first();
				/* echo $prevMonth;
				echo "<br>";
				echo $misData->emp_id;
				exit; */
				if($last2month!=''){
				$last2monthdata=$last2month->total_revenue;	
				}
				else{
				$last2monthdata=0;	
				}




				if($misData->zeeshan_suggested_status==1)
				{
					$suggestion="Exit Interview";
				}
				elseif($misData->zeeshan_suggested_status==2)
				{
					$suggestion="F&F";
				}
				else
				{
					$suggestion="None";
				}
				if($misData->suggested_user!='' && $misData->suggested_at!='')
				{
					$suggestedUser = $this->getUserName($misData->suggested_user);
					$suggested_at=date("d M Y",strtotime($misData->suggested_at));

					$suggestionRow='('.$suggestedUser.' at '.$suggested_at.')';
				}
				else
				{
					$suggestedUser='';
					$suggested_at='';
					$suggestionRow='';
				}					






				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($misData->emp_name))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $LocalContactNumber)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $offboarddate)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $lastworking)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $dateofresign)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $deptname)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('I'.$indexCounter, $designation)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('J'.$indexCounter, $tlname)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('K'.$indexCounter, $Reason)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('L'.$indexCounter, $Retain)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('M'.$indexCounter, $finalstatus)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('N'.$indexCounter, $vintage)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				
				$sheet->setCellValue('O'.$indexCounter, $dojdata)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('P'.$indexCounter, $expens)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('Q'.$indexCounter, $recuter)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('R'.$indexCounter, $interviewr)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('S'.$indexCounter, $currentmonthdata)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('T'.$indexCounter, $last1monthdata)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('U'.$indexCounter, $last2monthdata)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('V'.$indexCounter, $worktimedata)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $suggestion.$suggestionRow)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'W'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:V1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','W') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="EmpOffboard";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
		}	
public function offboardDataFilterfnf(Request $request)
		{
			//print_r($request->input());
			
			$offboardffstatus='';
			if($request->input('offboardffstatus')!=''){
			 
			 $offboardffstatus=implode(",", $request->input('offboardffstatus'));
			}
			$datetolastworkingday = $request->input('datetolastworkingday');
			$datefromlastworkingday = $request->input('datefromlastworkingday');
			$datetodort = $request->input('datetodort');
			$datefromdort = $request->input('datefromdort');
			$hrstatus = $request->input('offboardhrstatus');
			$financestatus = $request->input('financestatus');
			$amountstatus = $request->input('amountstatus');
			$request->session()->put('fnf_empoffboard_ffstatus_filter_list',$offboardffstatus);
			$request->session()->put('fnf_dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('fnf_datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			$request->session()->put('fnf_datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('fnf_dateto_offboard_dort_list',$datetodort);
			
			$request->session()->put('fnf_offboard_hrstatus_filter_list',$hrstatus);
			$request->session()->put('fnf_offboard_finance_filter_list',$financestatus);
			$request->session()->put('fnf_offboard_amount_filter_list',$amountstatus);
			
		}
		public function resetoffboardListDataFilterfnf(Request $request){
			$request->session()->put('fnf_empoffboard_ffstatus_filter_list','');
			$request->session()->put('fnf_dateto_offboard_lastworkingday_list','');
			$request->session()->put('fnf_datefrom_offboard_lastworkingday_list','');
			$request->session()->put('fnf_datefrom_offboard_dort_list','');
			$request->session()->put('fnf_dateto_offboard_dort_list','');
			$request->session()->put('fnf_offboard_hrstatus_filter_list','');
			$request->session()->put('fnf_offboard_finance_filter_list','');
			$request->session()->put('fnf_offboard_amount_filter_list','');
		}
		
		public function offboardDataFiltercancelvisa(Request $request)
		{
			//print_r($request->input());
			
			
			$datetolastworkingday = $request->input('datetolastworkingday');
			$datefromlastworkingday = $request->input('datefromlastworkingday');
			$datetodort = $request->input('datetodort');
			$datefromdort = $request->input('datefromdort');
			$visacancellation = $request->input('visacancellation');
			
			$request->session()->put('cancelvisa_dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('cancelvisa_datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			$request->session()->put('cancelvisa_datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('cancelvisa_dateto_offboard_dort_list',$datetodort);
			$request->session()->put('fnf_offboard_visacancellation_filter_list',$visacancellation);
			
		}
		public function resetoffboardListDataFiltercancelvisa(Request $request){
			$request->session()->put('cancelvisa_empoffboard_ffstatus_filter_list','');
			$request->session()->put('cancelvisa_dateto_offboard_lastworkingday_list','');
			$request->session()->put('cancelvisa_datefrom_offboard_lastworkingday_list','');
			$request->session()->put('cancelvisa_datefrom_offboard_dort_list','');
			$request->session()->put('cancelvisa_dateto_offboard_dort_list','');
			$request->session()->put('fnf_offboard_visacancellation_filter_list','');
		}
		public function offboardDataFilterpaymentconfirm(Request $request)
		{
			//print_r($request->input());
			
			
			$datetolastworkingday = $request->input('datetolastworkingday');
			$datefromlastworkingday = $request->input('datefromlastworkingday');
			$datetodort = $request->input('datetodort');
			$datefromdort = $request->input('datefromdort');
			$paymentconfirm = $request->input('paymentconfirm');
			
			$request->session()->put('paymentconfirm_dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('paymentconfirm_datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			$request->session()->put('paymentconfirm_datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('paymentconfirm_dateto_offboard_dort_list',$datetodort);
			$request->session()->put('fnf_offboard_paymentconfirm_filter_list',$paymentconfirm);
			
		}
		public function resetoffboardListDataFilterpaymentconfirm(Request $request){
			$request->session()->put('paymentconfirm_empoffboard_ffstatus_filter_list','');
			$request->session()->put('paymentconfirm_dateto_offboard_lastworkingday_list','');
			$request->session()->put('paymentconfirm_datefrom_offboard_lastworkingday_list','');
			$request->session()->put('paymentconfirm_datefrom_offboard_dort_list','');
			$request->session()->put('paymentconfirm_dateto_offboard_dort_list','');
			$request->session()->put('fnf_offboard_paymentconfirm_filter_list','');
		}
		public function OffboardLogsTab(Request $request){
		
			$id = $request->offboardid;
			  $visadata = CancelationVisaProcess::where("document_id",$id)->orderBy('id','ASC')->get();
			  $Documentdata=EmpOffline::where("id",$id)->first();
			  $empOffboardData=OffboardEMPData::where("emp_id",$id)->first();
  
			return view("EmpOfflineProcess/OffboardLogsDetails",compact('visadata','Documentdata','empOffboardData')); 
	}
	public function listingEmpOfflineProcessLastWorkingDate(Request $request)
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
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				if(!empty($request->session()->get('offboardtype_filter_inner_list')) && $request->session()->get('offboardtype_filter_inner_list') != 'All')
				{
					$type = $request->session()->get('offboardtype_filter_inner_list');
					
					
					 if($whereraw == '')
					{
						$whereraw = 'leaving_type = "'.$type.'"';
					}
					else
					{
						$whereraw .= ' And leaving_type = "'.$type.'"';
					}
				}
				
				//echo $whereraw;exit;
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				//$request->session()->put('cname_emp_filter_inner_list','');
				
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('email_cand_filter_inner_list')) && $request->session()->get('email_cand_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_cand_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('leaving_datefrom_offboard_lastworkingday_list')) && $request->session()->get('leaving_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('leaving_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('leaving_dateto_offboard_lastworkingday_list')) && $request->session()->get('leaving_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('leaving_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('leaving_datefrom_offboard_dort_list')) && $request->session()->get('leaving_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('leaving_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('leaving_dateto_offboard_dort_list')) && $request->session()->get('leaving_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('leaving_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
			if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
				if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}
				
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_cand_filter_inner_list')) && $request->session()->get('status_cand_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_cand_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_cand_filter_inner_list')) && $request->session()->get('vintage_cand_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_cand_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign is not null ';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign is not null';
					}
				
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				//echo $whereraw;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->paginate($paginationValue);
					
					
				}
				else
				{
					//echo "hello1";
					$whereraw1 = 'last_working_day_resign is not null';
					$documentCollectiondetails = EmpOffline::orderBy("id","DESC")->whereRaw($whereraw1)->paginate($paginationValue);
					
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = EmpOffline::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$whereraw1 = 'last_working_day_resign is not null';
					$reportsCount = EmpOffline::whereRaw($whereraw1)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingEmpOfflineProcessLastWorkingDate'));
				
		
		
		 
		return view("EmpOfflineProcess/listingEmpOfflineProcessLastWorkingDate",compact('departmentLists','productDetails','paginationValue','designationDetails','documentCollectiondetails','reportsCount'));
	   }
	public function listingEmpOfflineProcessExitInterviewQuestionnaire(Request $request)
	   {
		    $whereraw = '';
			$whereraw1 = '';
			
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				if(!empty($request->session()->get('offboard_retained_filter_inner_list')) && $request->session()->get('offboard_retained_filter_inner_list') != 'All')
				{
					$retained = $request->session()->get('offboard_retained_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'retain = "'.$retained.'"';
					}
					else
					{
						$whereraw .= ' And retain = "'.$retained.'"';
					}
				}
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				if(!empty($request->session()->get('offboardexittype_filter_inner_list')) && $request->session()->get('offboardexittype_filter_inner_list') != 'All')
				{
					$exittype = $request->session()->get('offboardexittype_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'leaving_type = "'.$exittype.'"';
					}
					else
					{
						$whereraw .= ' And leaving_type = "'.$exittype.'"';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				
				if(!empty($request->session()->get('company_candDeem_filter_inner_list')) && $request->session()->get('company_candDeem_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candDeem_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candDeem_filter_inner_list')) && $request->session()->get('email_candDeem_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candDeem_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_lastworkingday_list')) && $request->session()->get('exit_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('exit_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_lastworkingday_list')) && $request->session()->get('exit_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('exit_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_dort_list')) && $request->session()->get('exit_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('exit_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_dort_list')) && $request->session()->get('exit_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('exit_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
			if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
				if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}
				
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('dept_candDeem_filter_inner_list')) && $request->session()->get('dept_candDeem_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candDeem_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candDeem_filter_inner_list')) && $request->session()->get('status_candDeem_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candDeem_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candDeem_filter_inner_list')) && $request->session()->get('vintage_candDeem_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candDeem_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				
				
				
				if($whereraw == '')
					{
						$whereraw = 'condition_leaving = 2 AND last_working_day_resign IS NULL AND exit_interview_question_status IS NULL';
					}
					else
					{
						$whereraw .= ' And condition_leaving = 2 AND last_working_day_resign IS NULL AND exit_interview_question_status IS NULL';
					}
				//echo $whereraw;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->orderBy("condition_leaving_date", "DESC")->where("retain",2)->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw)->where("retain",2)->get()->count();
					//print_r($documentCollectiondetails);
				}
				else
				{
					//echo "hello1";
					$whereraw1 = 'condition_leaving = 2 AND last_working_day_resign IS NULL AND exit_interview_question_status IS NULL';
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw1)->orderBy("condition_leaving_date", "DESC")->where("retain",2)->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw1)->where("retain",2)->get()->count();
					
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$documentCollectiondetails->setPath(config('app.url/listingPanelExitInterviewQuestionnaire'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("EmpOfflineProcess/listingEmpOfflineProcessExitInterviewQuestionnaire",compact('designationDetails','documentCollectiondetails','reportsCount','paginationValue'));
	   }
	public function listingEmpOfflineProcessExitInterviewAwaiting(Request $request)
	   {
		    $whereraw = '';
			$whereraw1 = '';
			
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				if(!empty($request->session()->get('offboard_retained_filter_inner_list')) && $request->session()->get('offboard_retained_filter_inner_list') != 'All')
				{
					$retained = $request->session()->get('offboard_retained_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'retain = "'.$retained.'"';
					}
					else
					{
						$whereraw .= ' And retain = "'.$retained.'"';
					}
				}
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				if(!empty($request->session()->get('offboardexittype_filter_inner_list')) && $request->session()->get('offboardexittype_filter_inner_list') != 'All')
				{
					$exittype = $request->session()->get('offboardexittype_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'leaving_type = "'.$exittype.'"';
					}
					else
					{
						$whereraw .= ' And leaving_type = "'.$exittype.'"';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				
				if(!empty($request->session()->get('company_candDeem_filter_inner_list')) && $request->session()->get('company_candDeem_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candDeem_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candDeem_filter_inner_list')) && $request->session()->get('email_candDeem_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candDeem_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_lastworkingday_list')) && $request->session()->get('exit_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('exit_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_lastworkingday_list')) && $request->session()->get('exit_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('exit_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_dort_list')) && $request->session()->get('exit_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('exit_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_dort_list')) && $request->session()->get('exit_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('exit_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
			if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
				if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}
				
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('dept_candDeem_filter_inner_list')) && $request->session()->get('dept_candDeem_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candDeem_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candDeem_filter_inner_list')) && $request->session()->get('status_candDeem_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candDeem_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candDeem_filter_inner_list')) && $request->session()->get('vintage_candDeem_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candDeem_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				
				
				
				if($whereraw == '')
					{
						$whereraw = 'condition_leaving = 2 AND last_working_day_resign IS NULL AND exit_interview_question_status=1';
					}
					else
					{
						$whereraw .= ' And condition_leaving = 2 AND last_working_day_resign IS NULL AND exit_interview_question_status=1';
					}
				//echo $whereraw;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->orderBy("condition_leaving_date", "DESC")->where("retain",2)->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw)->where("retain",2)->get()->count();
					//print_r($documentCollectiondetails);
				}
				else
				{
					//echo "hello1";
					$whereraw1 = 'condition_leaving = 2 AND last_working_day_resign IS NULL AND exit_interview_question_status =1';
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw1)->orderBy("condition_leaving_date", "DESC")->where("retain",2)->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw1)->where("retain",2)->get()->count();
					
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$documentCollectiondetails->setPath(config('app.url/listingEmpOfflineProcessExitInterviewAwaiting'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("EmpOfflineProcess/listingEmpOfflineProcessExitInterviewAwaiting",compact('designationDetails','documentCollectiondetails','reportsCount','paginationValue'));
	   }
	public function listingEmpOfflineProcessExitInterviewRetained(Request $request)
	   {
		    $whereraw = '';
			$whereraw1 = '';
			
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				if(!empty($request->session()->get('offboard_retained_filter_inner_list_Retained')) && $request->session()->get('offboard_retained_filter_inner_list_Retained') != 'All')
				{
					$retained = $request->session()->get('offboard_retained_filter_inner_list_Retained');
					 if($whereraw == '')
					{
						$whereraw = 'retain = "'.$retained.'"';
					}
					else
					{
						$whereraw .= ' And retain = "'.$retained.'"';
					}
				}
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list_Retained')) && $request->session()->get('datefrom_offboard_filter_inner_list_Retained') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list_Retained');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				if(!empty($request->session()->get('offboardexittype_filter_inner_list_Retained')) && $request->session()->get('offboardexittype_filter_inner_list_Retained') != 'All')
				{
					$exittype = $request->session()->get('offboardexittype_filter_inner_list_Retained');
					 if($whereraw == '')
					{
						$whereraw = 'leaving_type = "'.$exittype.'"';
					}
					else
					{
						$whereraw .= ' And leaving_type = "'.$exittype.'"';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				
				if(!empty($request->session()->get('company_candDeem_filter_inner_list')) && $request->session()->get('company_candDeem_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candDeem_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candDeem_filter_inner_list')) && $request->session()->get('email_candDeem_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candDeem_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_lastworkingday_list_Retained')) && $request->session()->get('exit_datefrom_offboard_lastworkingday_list_Retained') != 'All')
				{
					$lastworkingday = $request->session()->get('exit_datefrom_offboard_lastworkingday_list_Retained');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_lastworkingday_list_Retained')) && $request->session()->get('exit_dateto_offboard_lastworkingday_list_Retained') != 'All')
				{
					$dateto = $request->session()->get('exit_dateto_offboard_lastworkingday_list_Retained');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_dort_list_Retained')) && $request->session()->get('exit_datefrom_offboard_dort_list_Retained') != 'All')
				{
					$dortfrom = $request->session()->get('exit_datefrom_offboard_dort_list_Retained');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_dort_list_Retained')) && $request->session()->get('exit_dateto_offboard_dort_list_Retained') != 'All')
				{
					$dortto = $request->session()->get('exit_dateto_offboard_dort_list_Retained');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
			if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
				if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}
				
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('dept_candDeem_filter_inner_list')) && $request->session()->get('dept_candDeem_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candDeem_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candDeem_filter_inner_list')) && $request->session()->get('status_candDeem_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candDeem_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candDeem_filter_inner_list')) && $request->session()->get('vintage_candDeem_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candDeem_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				
				
				
				
				
				//echo $whereraw;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->where("retain",1)->orderBy("condition_leaving_date", "DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw)->where("retain",1)->get()->count();
					//print_r($documentCollectiondetails);
				}
				else
				{
					//echo "hello1";
					
					$documentCollectiondetails = EmpOffline::where("retain",1)->orderBy("condition_leaving_date", "DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::where("retain",1)->get()->count();
					
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$documentCollectiondetails->setPath(config('app.url/listingEmpOfflineProcessExitInterviewRetained'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("EmpOfflineProcess/listingEmpOfflineProcessExitInterviewRetained",compact('designationDetails','documentCollectiondetails','reportsCount','paginationValue'));
	   }
	public function listingEmpOfflineProcessExitInterviewProceedfnf(Request $request)
	   {
		    $whereraw = '';
			$whereraw1 = '';
			
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				if(!empty($request->session()->get('offboard_retained_filter_inner_list')) && $request->session()->get('offboard_retained_filter_inner_list') != 'All')
				{
					$retained = $request->session()->get('offboard_retained_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'retain = "'.$retained.'"';
					}
					else
					{
						$whereraw .= ' And retain = "'.$retained.'"';
					}
				}
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				if(!empty($request->session()->get('offboardexittype_filter_inner_list')) && $request->session()->get('offboardexittype_filter_inner_list') != 'All')
				{
					$exittype = $request->session()->get('offboardexittype_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'leaving_type = "'.$exittype.'"';
					}
					else
					{
						$whereraw .= ' And leaving_type = "'.$exittype.'"';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				
				if(!empty($request->session()->get('company_candDeem_filter_inner_list')) && $request->session()->get('company_candDeem_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candDeem_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candDeem_filter_inner_list')) && $request->session()->get('email_candDeem_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candDeem_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_lastworkingday_list')) && $request->session()->get('exit_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('exit_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_lastworkingday_list')) && $request->session()->get('exit_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('exit_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_dort_list')) && $request->session()->get('exit_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('exit_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_dort_list')) && $request->session()->get('exit_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('exit_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
			if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
				if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}
				
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('dept_candDeem_filter_inner_list')) && $request->session()->get('dept_candDeem_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candDeem_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candDeem_filter_inner_list')) && $request->session()->get('status_candDeem_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candDeem_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candDeem_filter_inner_list')) && $request->session()->get('vintage_candDeem_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candDeem_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				
				
				
				//echo $whereraw;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->where("condition_leaving",3)->where("retain",2)->orderBy("condition_leaving_date", "DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw)->where("condition_leaving",3)->where("retain",2)->get()->count();
					//print_r($documentCollectiondetails);
				}
				else
				{
					//echo "hello1";
					
					$documentCollectiondetails = EmpOffline::where("condition_leaving",3)->where("retain",2)->orderBy("condition_leaving_date", "DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::where("condition_leaving",3)->where("retain",2)->get()->count();
					
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$documentCollectiondetails->setPath(config('app.url/listingEmpOfflineProcessExitInterviewProceedfnf'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("EmpOfflineProcess/listingEmpOfflineProcessExitInterviewProceedfnf",compact('designationDetails','documentCollectiondetails','reportsCount','paginationValue'));
	   }
	public function getRetainEMPData($id){
		$detailsObj = EmpOffline::find($id);		
		$detailsObj->retain=1;		
		if($detailsObj->save()){

				$offlinedata=EmpOffline::where("id",$id)->first();
				$empdata=Employee_details::where("emp_id",$offlinedata->emp_id)->first();
				if($empdata!=''){
					$empOBJ=Employee_details::find($empdata->id);
					$empOBJ->offline_status=1;
					$empOBJ->offboard_status=4;
					$empOBJ->save();
				}
		}
		echo "Done";

	}
public function offboardDataFilterall(Request $request)
		{
			//print_r($request->input());
			
			$exittype=$request->input('exittype');
			$retained=$request->input('retained');
			$datetolastworkingday = $request->input('datetolastworkingdayall');
			$datefromlastworkingday = $request->input('datefromlastworkingdayall');
			$datetodort = $request->input('datetodortall');
			$datefromdort = $request->input('datefromdortall');
			$lwdate = $request->input('lwdate');
			$request->session()->put('offboardall_filter_inner_list',$exittype);
			$request->session()->put('all_dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('all_datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			$request->session()->put('all_datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('all_dateto_offboard_dort_list',$datetodort);
			$request->session()->put('offboardall_retained_filter_inner_list',$retained);
			$request->session()->put('offboardall_LWD_filter_inner_list',$lwdate);
			
		}
		public function resetoffboardListDataFilterall(Request $request){
			$request->session()->put('offboardall_filter_inner_list','');
			$request->session()->put('all_dateto_offboard_lastworkingday_list','');
			$request->session()->put('all_datefrom_offboard_lastworkingday_list','');
			$request->session()->put('all_datefrom_offboard_dort_list','');
			$request->session()->put('all_dateto_offboard_dort_list','');
			$request->session()->put('offboardall_retained_filter_inner_list','');
			$request->session()->put('offboardall_LWD_filter_inner_list','');
		}	
		public static function gettotalvisapaymentData($empId){
		$paymetdata = EmpOffline::where("emp_id",$empId)->first();
			if($paymetdata!='' && $paymetdata->document_collection_id!='' ){
			$visascost = Visaprocess::where("document_id",$paymetdata->document_collection_id)->sum('cost');
			$visasfine = Visaprocess::where("document_id",$paymetdata->document_collection_id)->sum('cost_fine');
			$visas=	$visascost+$visasfine;
				return $visas;	
						
			}
			else{
				$data=VisaExpensesDetailsUpdated::where("Employee_id",$empId)->first();
				if($data!=''){
				return $data->employeer_visa_cost;	
				}
				else{
					return 0;
				}
			}
								
		}
	public function getlastworkingPopupData($offboardId)
	{
		$offlinedata = EmpOffline::where("id",$offboardId)->first();
		
		return view("EmpOfflineProcess/lastworkingdate",compact('offboardId','offlinedata'));
	}
	public function LastworkingdatePostData(Request $request){
		$id=$request->input('docId');
		$detailsObj = EmpOffline::find($id);		
		$detailsObj->last_working_day_resign=$request->input('lastworkingdate');
		
		$detailsObj->save();
		echo "Done";
	}
public static function getOffboardRecruiterName($empid)
	   {
		   $empdatdata=Employee_details::where("emp_id",$empid)->first();
			if($empdatdata!=''){
				$recuter=RecruiterDetails::where("id",$empdatdata->recruiter)->first();
				if($recuter!=""){
				return $recuter=$recuter->name;
				}
				else{
					return $recuter='';
				}
			}
			else{
					return $recuter='';
				}
	   }
public static function getVisaStageNameOffBoard($id)
	{	
	
	$empdatdata=Employee_details::where("emp_id",$id)->first();
	if($empdatdata!=''){
	$data = Visaprocess::where("document_id",$empdatdata->document_collection_id)->orderBy('id','DESC')->first();
	  
	  //print_r($data);
	  if($data != '')
	  {
		  $visa_stage = VisaStage::where("id",$data->visa_stage)->first();
		  if($visa_stage!=''){
			return "<p>Current Visa Stage: ".$visa_stage->stage_name."</p><p> Current Visa Stage Date: ".date("Y-m-d",strtotime($data->closing_date))."</p>";
		  }else{
			  return '';
		  }
	  }
	  else
	  {
	  return '';
	  }
	} else{
		return '';
	}
	}	
public function SaveDataExitinterview($id){
	//echo $id;exit;
		$detailsObj = EmpOffline::find($id);		
		$detailsObj->exit_interview_approved=1;
		$detailsObj->condition_leaving=2;			
		if($detailsObj->save()){

				$offlinedata=EmpOffline::where("id",$id)->first();
				$empdata=Employee_details::where("emp_id",$offlinedata->emp_id)->first();
				if($empdata!=''){
					$empOBJ=Employee_details::find($empdata->id);
					$empOBJ->offline_status=2;
					$empOBJ->offboard_status=3;
					$empOBJ->save();
				}
		}
		echo "Done";

	}
public function SaveDataFNF($id){
		$detailsObj = EmpOffline::find($id);		
		$detailsObj->fnf_approved=1;
		$detailsObj->condition_leaving=3;			
		if($detailsObj->save()){

				$offlinedata=EmpOffline::where("id",$id)->first();
				$empdata=Employee_details::where("emp_id",$offlinedata->emp_id)->first();
				if($empdata!=''){
					$empOBJ=Employee_details::find($empdata->id);
					$empOBJ->offline_status=2;
					$empOBJ->offboard_status=3;
					$empOBJ->save();
				}
		}
		echo "Done";

	}	
public function offboardFullandFinalSettelementAmountConfirmed(Request $request)
	   {
		   //$request->session()->put('fnf_offboard_amount_filter_list',1);
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
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				if(!empty($request->session()->get('company_candmashreq_filter_inner_list')) && $request->session()->get('company_candmashreq_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candmashreq_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candmashreq_filter_inner_list')) && $request->session()->get('email_candmashreq_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candmashreq_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('fnf_datefrom_offboard_lastworkingday_list')) && $request->session()->get('fnf_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('fnf_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('fnf_dateto_offboard_lastworkingday_list')) && $request->session()->get('fnf_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('fnf_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('fnf_datefrom_offboard_dort_list')) && $request->session()->get('fnf_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('fnf_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('fnf_dateto_offboard_dort_list')) && $request->session()->get('fnf_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('fnf_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}	
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('fnf_empoffboard_ffstatus_filter_list')) && $request->session()->get('fnf_empoffboard_ffstatus_filter_list') != 'All')
				{
					$offboard_ffstatus = $request->session()->get('fnf_empoffboard_ffstatus_filter_list');
					
					 $offboard_ffstatusArray = explode(",",$offboard_ffstatus);
					 $offlinestatusdata= OffboardEMPData::whereIn('settelement_confirmation_status',$offboard_ffstatusArray)->get();
					 $ffstatusarray=array();
					 foreach($offlinestatusdata as $_ffstatus){
						 $ffstatusarray[]=$_ffstatus->emp_id;
						 
						 
					 }
					 //print_r($ffstatusarray);//exit;
					 $finalffstatus=implode(",", $ffstatusarray);
					if($whereraw == '')
					{
						$whereraw = 'id IN('.$finalffstatus.')';
					}
					else
					{
						$whereraw .= ' And id IN('.$finalffstatus.')';
					}
				}
				
				
				if(!empty($request->session()->get('fnf_offboard_hrstatus_filter_list')) && $request->session()->get('fnf_offboard_hrstatus_filter_list') != 'All')
				{
					$hrstatus = $request->session()->get('fnf_offboard_hrstatus_filter_list');
					
					 if($hrstatus==1){
						$offlinestatusdata= OffboardEMPData::where('settelement_hr_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($hrstatus==2){
						 $offlinestatusdata= OffboardEMPData::where('settelement_hr_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
				if(!empty($request->session()->get('fnf_offboard_finance_filter_list')) && $request->session()->get('fnf_offboard_finance_filter_list') != 'All')
				{
					$finance = $request->session()->get('fnf_offboard_finance_filter_list');
					
					 if($finance==1){
						$offlinestatusdata= OffboardEMPData::where('settelement_finance_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($finance==2){
						 $offlinestatusdata= OffboardEMPData::where('settelement_finance_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
				
				if(!empty($request->session()->get('fnf_offboard_amount_filter_list')) && $request->session()->get('fnf_offboard_amount_filter_list') != 'All')
				{
					$amount = $request->session()->get('fnf_offboard_amount_filter_list');
					
					 if($amount==1){
						$offlinestatusdata= OffboardEMPData::where('settelement_finance_status',1)->where('settelement_hr_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($amount==2){
						 $offlinestatusdata= OffboardEMPData::where('settelement_finance_status',1)->where('settelement_hr_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
				
				
				
				
				
				
				
				
				
				//echo $whereraw;
				if(!empty($request->session()->get('dept_candmashreq_filter_inner_list')) && $request->session()->get('dept_candmashreq_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candmashreq_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candmashreq_filter_inner_list')) && $request->session()->get('status_candmashreq_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candmashreq_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candmashreq_filter_inner_list')) && $request->session()->get('vintage_candmashreq_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candmashreq_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = EmpOffline::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = EmpOffline::whereRaw($whereraw)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
					
				}
				foreach($recruter_details as $_recruter_details)
				{
					//echo $_f->first_name;exit;
					$CandidateRecruiterArray[$_recruter_details->id] = $_recruter_details->name;
				}
				
				$CandidateNameArray = array();
				if($whereraw == '')
				{
					/* echo "sddf";
					exit; */
				$c_namedata = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$c_namedata = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$email = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$visa = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($visa as $_company)
				{
					//echo $_f->first_name;exit;
					if($_company->company_visa!=''){
					$companyvisaArray[$_company->company_visa] = $_company->company_visa;
					}
				}
				
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = EmpOffline::where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = EmpOffline::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = EmpOffline::whereRaw($whereraw)->where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				
				$DesignationArray = array();
				if($whereraw == '')
				{
					$depidArray = EmpOffline::where("department",36)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
					
				}
				foreach($desc as $_desc)
				{
					//echo $_f->first_name;exit;
					$DesignationArray[$_desc->id] = $_desc->name;
				}
				
				$OpeningArray = array();
				if($whereraw == '')
				{
				$jobArray = EmpOffline::where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
					$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
					
				}
				foreach($opening as $_opening)
				{
					//echo $_f->first_name;exit;
					//$OpeningArray[$_opening->id] = $_opening->name;
					$dept=Department::where("id",$_opening->department)->first();
					//echo $_f->first_name;exit;
					$OpeningArray[$_opening->id] = $_opening->name ." (".$dept->department_name." - ".$_opening->location.")";
				}
				$StatusArray = array();
				if($whereraw == '')
				{
				$status =  EmpOffline::where("department",36)->get();
				}
				else
				{
					$status =  EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = EmpOffline::where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
					$department =Department::whereIn('id',array_unique($dpetList))->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$DepartmentArray[$_dptname->id] = $_dptname->department_name;
				}
				//echo $whereraw ;
				//echo $val=EmpOffline::whereRaw($whereraw)->whereIn("condition_leaving",array(3,4,5,6))->orderBy("created_at","DESC")->toSql();
				
				
				
				if($whereraw != '')
				{
					
					$documentCollectiondetail = EmpOffline::whereIn("condition_leaving",array(3,4,5,6))->whereRaw($whereraw)->orderBy("created_at","DESC")->get();
					//$reportsCount = EmpOffline::whereIn("condition_leaving",array(3,4,5))->whereRaw($whereraw)->get()->count();
				}
				else
				{	
						
								
					$documentCollectiondetail = EmpOffline::whereIn("condition_leaving",array(3,4,5,6))->orderBy("created_at","DESC")->get();
					//$reportsCount = EmpOffline::whereRaw($whereraw2)->whereIn("condition_leaving",array(3,4,5))->get()->count();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$statusarraydata=array();
				foreach($documentCollectiondetail as $_documentCollectiondetail){
							 $data=OffboardEMPData::where("emp_id",$_documentCollectiondetail->id)->first();
							 if($data!='' && $_documentCollectiondetail->salary_deduction==$data->salary_deduction_total_finance){
							 $statusarraydata[]=$_documentCollectiondetail->id;
							 }
							 
							 
						 } 
						 $documentCollectiondetails = EmpOffline::whereIn("id",$statusarraydata)->orderBy("created_at","DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereIn("id",$statusarraydata)->get()->count();
				$documentCollectiondetails->setPath(config('app.url/offboardFullandFinalSettelementAmountConfirmed'));
				
				
		return view("EmpOfflineProcess/offboardFullandFinalSettelementAmountConfirmed",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
public function offboardFullandFinalSettelementAmountNotConfirmed(Request $request)
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
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				if(!empty($request->session()->get('company_candmashreq_filter_inner_list')) && $request->session()->get('company_candmashreq_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candmashreq_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candmashreq_filter_inner_list')) && $request->session()->get('email_candmashreq_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candmashreq_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('fnf_datefrom_offboard_lastworkingday_list')) && $request->session()->get('fnf_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('fnf_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('fnf_dateto_offboard_lastworkingday_list')) && $request->session()->get('fnf_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('fnf_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('fnf_datefrom_offboard_dort_list')) && $request->session()->get('fnf_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('fnf_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('fnf_dateto_offboard_dort_list')) && $request->session()->get('fnf_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('fnf_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}	
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('fnf_empoffboard_ffstatus_filter_list')) && $request->session()->get('fnf_empoffboard_ffstatus_filter_list') != 'All')
				{
					$offboard_ffstatus = $request->session()->get('fnf_empoffboard_ffstatus_filter_list');
					
					 $offboard_ffstatusArray = explode(",",$offboard_ffstatus);
					 $offlinestatusdata= OffboardEMPData::whereIn('settelement_confirmation_status',$offboard_ffstatusArray)->get();
					 $ffstatusarray=array();
					 foreach($offlinestatusdata as $_ffstatus){
						 $ffstatusarray[]=$_ffstatus->emp_id;
						 
						 
					 }
					 //print_r($ffstatusarray);//exit;
					 $finalffstatus=implode(",", $ffstatusarray);
					if($whereraw == '')
					{
						$whereraw = 'id IN('.$finalffstatus.')';
					}
					else
					{
						$whereraw .= ' And id IN('.$finalffstatus.')';
					}
				}
				
				
				if(!empty($request->session()->get('fnf_offboard_hrstatus_filter_list')) && $request->session()->get('fnf_offboard_hrstatus_filter_list') != 'All')
				{
					$hrstatus = $request->session()->get('fnf_offboard_hrstatus_filter_list');
					
					 if($hrstatus==1){
						$offlinestatusdata= OffboardEMPData::where('settelement_hr_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($hrstatus==2){
						 $offlinestatusdata= OffboardEMPData::where('settelement_hr_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
				if(!empty($request->session()->get('fnf_offboard_finance_filter_list')) && $request->session()->get('fnf_offboard_finance_filter_list') != 'All')
				{
					$finance = $request->session()->get('fnf_offboard_finance_filter_list');
					
					 if($finance==1){
						$offlinestatusdata= OffboardEMPData::where('settelement_finance_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($finance==2){
						 $offlinestatusdata= OffboardEMPData::where('settelement_finance_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
				
				if(!empty($request->session()->get('fnf_offboard_amount_filter_list')) && $request->session()->get('fnf_offboard_amount_filter_list') != 'All')
				{
					$amount = $request->session()->get('fnf_offboard_amount_filter_list');
					
					 if($amount==1){
						$offlinestatusdata= OffboardEMPData::where('settelement_finance_status',1)->where('settelement_hr_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($amount==2){
						 $offlinestatusdata= OffboardEMPData::where('settelement_finance_status',1)->where('settelement_hr_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
				
				
				
				
				
				
				
				
				
				//echo $whereraw;
				if(!empty($request->session()->get('dept_candmashreq_filter_inner_list')) && $request->session()->get('dept_candmashreq_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candmashreq_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candmashreq_filter_inner_list')) && $request->session()->get('status_candmashreq_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candmashreq_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candmashreq_filter_inner_list')) && $request->session()->get('vintage_candmashreq_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candmashreq_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = EmpOffline::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = EmpOffline::whereRaw($whereraw)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
					
				}
				foreach($recruter_details as $_recruter_details)
				{
					//echo $_f->first_name;exit;
					$CandidateRecruiterArray[$_recruter_details->id] = $_recruter_details->name;
				}
				
				$CandidateNameArray = array();
				if($whereraw == '')
				{
					/* echo "sddf";
					exit; */
				$c_namedata = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$c_namedata = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$email = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$visa = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($visa as $_company)
				{
					//echo $_f->first_name;exit;
					if($_company->company_visa!=''){
					$companyvisaArray[$_company->company_visa] = $_company->company_visa;
					}
				}
				
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = EmpOffline::where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = EmpOffline::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = EmpOffline::whereRaw($whereraw)->where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				
				$DesignationArray = array();
				if($whereraw == '')
				{
					$depidArray = EmpOffline::where("department",36)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
					
				}
				foreach($desc as $_desc)
				{
					//echo $_f->first_name;exit;
					$DesignationArray[$_desc->id] = $_desc->name;
				}
				
				$OpeningArray = array();
				if($whereraw == '')
				{
				$jobArray = EmpOffline::where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
					$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
					
				}
				foreach($opening as $_opening)
				{
					//echo $_f->first_name;exit;
					//$OpeningArray[$_opening->id] = $_opening->name;
					$dept=Department::where("id",$_opening->department)->first();
					//echo $_f->first_name;exit;
					$OpeningArray[$_opening->id] = $_opening->name ." (".$dept->department_name." - ".$_opening->location.")";
				}
				$StatusArray = array();
				if($whereraw == '')
				{
				$status =  EmpOffline::where("department",36)->get();
				}
				else
				{
					$status =  EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = EmpOffline::where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
					$department =Department::whereIn('id',array_unique($dpetList))->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$DepartmentArray[$_dptname->id] = $_dptname->department_name;
				}
				//echo $whereraw ;
				//echo $val=EmpOffline::whereRaw($whereraw)->whereIn("condition_leaving",array(3,4,5,6))->orderBy("created_at","DESC")->toSql();
				if($whereraw != '')
				{
					$offlinestatusdata= OffboardEMPData::where('settelement_finance_status',1)->where('settelement_hr_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
					$documentCollectiondetail = EmpOffline::whereRaw($whereraw)->whereIn("condition_leaving",array(3,4,5,6))->orderBy("created_at","DESC")->get();
				//$reportsCount = EmpOffline::whereRaw($whereraw)->whereIn("condition_leaving",array(3,4,5,6))->get()->count();
				}
				else
				{
					
					
					$documentCollectiondetail = EmpOffline::whereIn("condition_leaving",array(3,4,5,6))->orderBy("created_at","DESC")->get();
					//$reportsCount = EmpOffline::whereRaw($whereraw2)->whereIn("condition_leaving",array(3,4,5,6))->get()->count();
				}
				
				$statusarraydata=array();
				foreach($documentCollectiondetail as $_documentCollectiondetail){
							 $data=OffboardEMPData::where("emp_id",$_documentCollectiondetail->id)->first();
							 if($data!=''){
							 if($_documentCollectiondetail->salary_deduction!=$data->salary_deduction_total_finance){
							 $statusarraydata[]=$_documentCollectiondetail->id;
							 }
							 }
							 else{
								$statusarraydata[]=$_documentCollectiondetail->id; 
							 }
							 
							 
						 } 
						 $documentCollectiondetails = EmpOffline::whereIn("id",$statusarraydata)->orderBy("created_at","DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereIn("id",$statusarraydata)->get()->count();
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$documentCollectiondetails->setPath(config('app.url/offboardFullandFinalSettelement'));
				
				
		return view("EmpOfflineProcess/offboardFullandFinalSettelementAmountNotConfirmed",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
	   public function offboardVisaCancellationAmountConfirmed(Request $request)
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
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				
				if(!empty($request->session()->get('company_candmashreq_filter_inner_list')) && $request->session()->get('company_candmashreq_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candmashreq_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candmashreq_filter_inner_list')) && $request->session()->get('email_candmashreq_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candmashreq_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_datefrom_offboard_lastworkingday_list')) && $request->session()->get('cancelvisa_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('cancelvisa_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_dateto_offboard_lastworkingday_list')) && $request->session()->get('cancelvisa_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('cancelvisa_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_datefrom_offboard_dort_list')) && $request->session()->get('cancelvisa_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('cancelvisa_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_dateto_offboard_dort_list')) && $request->session()->get('cancelvisa_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('cancelvisa_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
				
				if(!empty($request->session()->get('fnf_offboard_visacancellation_filter_list')) && $request->session()->get('fnf_offboard_visacancellation_filter_list') != 'All')
				{
					$visacancellation = $request->session()->get('fnf_offboard_visacancellation_filter_list');
					
					 if($visacancellation==1){
						$offlinestatusdata= EmpOffline::where('visa_process_status',5)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($visacancellation==2){
						 $offlinestatusdata= EmpOffline::where('visa_process_status',5)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
				
				
if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}
				
				if(!empty($request->session()->get('empoffboard_ffstatus_filter_list')) && $request->session()->get('empoffboard_ffstatus_filter_list') != 'All')
				{
					$offboard_ffstatus = $request->session()->get('empoffboard_ffstatus_filter_list');
					
					 $offboard_ffstatusArray = explode(",",$offboard_ffstatus);
					 $offlinestatusdata= OffboardEMPData::whereIn('settelement_confirmation_status',$offboard_ffstatusArray)->get();
					 $ffstatusarray=array();
					 foreach($offlinestatusdata as $_ffstatus){
						 $ffstatusarray[]=$_ffstatus->emp_id;
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalffstatus=implode(",", $ffstatusarray);
					if($whereraw == '')
					{
						$whereraw = 'id IN('.$finalffstatus.')';
					}
					else
					{
						$whereraw .= ' And id IN('.$finalffstatus.')';
					}
				}
				//echo $whereraw;
				
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('dept_candmashreq_filter_inner_list')) && $request->session()->get('dept_candmashreq_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candmashreq_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candmashreq_filter_inner_list')) && $request->session()->get('status_candmashreq_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candmashreq_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candmashreq_filter_inner_list')) && $request->session()->get('vintage_candmashreq_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candmashreq_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				
				
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = EmpOffline::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = EmpOffline::whereRaw($whereraw)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
					
				}
				foreach($recruter_details as $_recruter_details)
				{
					//echo $_f->first_name;exit;
					$CandidateRecruiterArray[$_recruter_details->id] = $_recruter_details->name;
				}
				
				$CandidateNameArray = array();
				if($whereraw == '')
				{
					/* echo "sddf";
					exit; */
				$c_namedata = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$c_namedata = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$email = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$visa = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($visa as $_company)
				{
					//echo $_f->first_name;exit;
					if($_company->company_visa!=''){
					$companyvisaArray[$_company->company_visa] = $_company->company_visa;
					}
				}
				
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = EmpOffline::where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = EmpOffline::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = EmpOffline::whereRaw($whereraw)->where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				
				$DesignationArray = array();
				if($whereraw == '')
				{
					$depidArray = EmpOffline::where("department",36)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
					
				}
				foreach($desc as $_desc)
				{
					//echo $_f->first_name;exit;
					$DesignationArray[$_desc->id] = $_desc->name;
				}
				
				$OpeningArray = array();
				if($whereraw == '')
				{
				$jobArray = EmpOffline::where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
					$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
					
				}
				foreach($opening as $_opening)
				{
					//echo $_f->first_name;exit;
					//$OpeningArray[$_opening->id] = $_opening->name;
					$dept=Department::where("id",$_opening->department)->first();
					//echo $_f->first_name;exit;
					$OpeningArray[$_opening->id] = $_opening->name ." (".$dept->department_name." - ".$_opening->location.")";
				}
				$StatusArray = array();
				if($whereraw == '')
				{
				$status =  EmpOffline::where("department",36)->get();
				}
				else
				{
					$status =  EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = EmpOffline::where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
					$department =Department::whereIn('id',array_unique($dpetList))->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$DepartmentArray[$_dptname->id] = $_dptname->department_name;
				}
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
					
					$documentCollectiondetail = EmpOffline::whereIn("condition_leaving",array(3,4,5))->whereRaw($whereraw)->orderBy("created_at","DESC")->get();
					//$reportsCount = EmpOffline::whereIn("condition_leaving",array(3,4,5))->whereRaw($whereraw)->get()->count();
				}
				else
				{	
						
								
					$documentCollectiondetail = EmpOffline::whereIn("condition_leaving",array(3,4,5))->orderBy("created_at","DESC")->get();
					//$reportsCount = EmpOffline::whereRaw($whereraw2)->whereIn("condition_leaving",array(3,4,5))->get()->count();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				
				$statusarraydata=array();
				foreach($documentCollectiondetail as $_documentCollectiondetail){
							 $data=OffboardEMPData::where("emp_id",$_documentCollectiondetail->id)->first();
							 if($data!='' && $_documentCollectiondetail->salary_deduction==$data->salary_deduction_total_finance){
							 $statusarraydata[]=$_documentCollectiondetail->id;
							 }
							 
							 
						 } 
						 $documentCollectiondetails = EmpOffline::whereIn("id",$statusarraydata)->orderBy("created_at","DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereIn("id",$statusarraydata)->get()->count();
				$documentCollectiondetails->setPath(config('app.url/offboardVisaCancellation'));
				
				//print_r($documentCollectiondetails);exit;
		return view("EmpOfflineProcess/offboardVisaCancellationAmountConfirmed",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
	   public function offboardVisaCancellationAmountNotConfirmed(Request $request)
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
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				
				if(!empty($request->session()->get('company_candmashreq_filter_inner_list')) && $request->session()->get('company_candmashreq_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candmashreq_filter_inner_list');
					 $selectedFilter['Company'] = $company;
					 if($whereraw == '')
					{
						$whereraw = 'company_visa = "'.$company.'"';
					}
					else
					{
						$whereraw .= ' And company_visa = "'.$company.'"';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candmashreq_filter_inner_list')) && $request->session()->get('email_candmashreq_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candmashreq_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_datefrom_offboard_lastworkingday_list')) && $request->session()->get('cancelvisa_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('cancelvisa_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_dateto_offboard_lastworkingday_list')) && $request->session()->get('cancelvisa_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('cancelvisa_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_datefrom_offboard_dort_list')) && $request->session()->get('cancelvisa_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('cancelvisa_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_dateto_offboard_dort_list')) && $request->session()->get('cancelvisa_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('cancelvisa_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
				
				if(!empty($request->session()->get('fnf_offboard_visacancellation_filter_list')) && $request->session()->get('fnf_offboard_visacancellation_filter_list') != 'All')
				{
					$visacancellation = $request->session()->get('fnf_offboard_visacancellation_filter_list');
					
					 if($visacancellation==1){
						$offlinestatusdata= EmpOffline::where('visa_process_status',5)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($visacancellation==2){
						 $offlinestatusdata= EmpOffline::where('visa_process_status',5)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
				
				
if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}
				
				if(!empty($request->session()->get('empoffboard_ffstatus_filter_list')) && $request->session()->get('empoffboard_ffstatus_filter_list') != 'All')
				{
					$offboard_ffstatus = $request->session()->get('empoffboard_ffstatus_filter_list');
					
					 $offboard_ffstatusArray = explode(",",$offboard_ffstatus);
					 $offlinestatusdata= OffboardEMPData::whereIn('settelement_confirmation_status',$offboard_ffstatusArray)->get();
					 $ffstatusarray=array();
					 foreach($offlinestatusdata as $_ffstatus){
						 $ffstatusarray[]=$_ffstatus->emp_id;
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalffstatus=implode(",", $ffstatusarray);
					if($whereraw == '')
					{
						$whereraw = 'id IN('.$finalffstatus.')';
					}
					else
					{
						$whereraw .= ' And id IN('.$finalffstatus.')';
					}
				}
				//echo $whereraw;
				
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('dept_candmashreq_filter_inner_list')) && $request->session()->get('dept_candmashreq_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candmashreq_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candmashreq_filter_inner_list')) && $request->session()->get('status_candmashreq_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candmashreq_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candmashreq_filter_inner_list')) && $request->session()->get('vintage_candmashreq_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candmashreq_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				
				
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = EmpOffline::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = EmpOffline::whereRaw($whereraw)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
					
				}
				foreach($recruter_details as $_recruter_details)
				{
					//echo $_f->first_name;exit;
					$CandidateRecruiterArray[$_recruter_details->id] = $_recruter_details->name;
				}
				
				$CandidateNameArray = array();
				if($whereraw == '')
				{
					/* echo "sddf";
					exit; */
				$c_namedata = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$c_namedata = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$email = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$visa = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($visa as $_company)
				{
					//echo $_f->first_name;exit;
					if($_company->company_visa!=''){
					$companyvisaArray[$_company->company_visa] = $_company->company_visa;
					}
				}
				
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = EmpOffline::where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = EmpOffline::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = EmpOffline::whereRaw($whereraw)->where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				
				$DesignationArray = array();
				if($whereraw == '')
				{
					$depidArray = EmpOffline::where("department",36)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
					
				}
				foreach($desc as $_desc)
				{
					//echo $_f->first_name;exit;
					$DesignationArray[$_desc->id] = $_desc->name;
				}
				
				$OpeningArray = array();
				if($whereraw == '')
				{
				$jobArray = EmpOffline::where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
					$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
					
				}
				foreach($opening as $_opening)
				{
					//echo $_f->first_name;exit;
					//$OpeningArray[$_opening->id] = $_opening->name;
					$dept=Department::where("id",$_opening->department)->first();
					//echo $_f->first_name;exit;
					$OpeningArray[$_opening->id] = $_opening->name ." (".$dept->department_name." - ".$_opening->location.")";
				}
				$StatusArray = array();
				if($whereraw == '')
				{
				$status =  EmpOffline::where("department",36)->get();
				}
				else
				{
					$status =  EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = EmpOffline::where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
					$department =Department::whereIn('id',array_unique($dpetList))->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$DepartmentArray[$_dptname->id] = $_dptname->department_name;
				}
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
					//echo "hello";exit;
					
					$documentCollectiondetail = EmpOffline::whereIn("condition_leaving",array(3,4,5))->whereRaw($whereraw)->orderBy("created_at","DESC")->get();
				//$reportsCount = EmpOffline::whereIn("condition_leaving",array(3,4,5))->whereRaw($whereraw)->get()->count();
				}
				else
				{	
						
								
					$documentCollectiondetail = EmpOffline::whereIn("condition_leaving",array(3,4,5))->orderBy("created_at","DESC")->get();
					//$reportsCount = EmpOffline::whereRaw($whereraw2)->whereIn("condition_leaving",array(3,4,5))->get()->count();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				$statusarraydata=array();
				foreach($documentCollectiondetail as $_documentCollectiondetail){
							 $data=OffboardEMPData::where("emp_id",$_documentCollectiondetail->id)->first();
							 if($data!=''){
							 if($_documentCollectiondetail->salary_deduction!=$data->salary_deduction_total_finance){
							 $statusarraydata[]=$_documentCollectiondetail->id;
							 }
							 }
							 else{
								$statusarraydata[]=$_documentCollectiondetail->id; 
							 }
							 
							 
						 } 
						 $documentCollectiondetails = EmpOffline::whereIn("id",$statusarraydata)->orderBy("created_at","DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereIn("id",$statusarraydata)->get()->count();
				$documentCollectiondetails->setPath(config('app.url/offboardVisaCancellation'));
				
				//print_r($documentCollectiondetails);exit;
		return view("EmpOfflineProcess/offboardVisaCancellationAmountNotConfirmed",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }	
	   
	   






	// New code Start (29-05-2024)

	   public function suggestionActionProcess(Request $request)
	   {
		   $emp_id = $request->empid;
		   $actionid = $request->action;
		   $userid=$request->session()->get('EmployeeId');

			if($userid!='')
			{
				$failedmsg='';
				$offlineempinfo = EmpOffline::where("emp_id",$emp_id)->orderBy('id','DESC')->first();
   
				if($offlineempinfo->zeeshan_suggested_status==1)
				{
					$successmsg="Suggestion for this Request already Sent.";
					return view("EmpOfflineProcess/email_process",compact('successmsg','failedmsg'));
				}
				elseif($offlineempinfo->zeeshan_suggested_status==2)
				{
					$successmsg="Suggestion for this Request already Sent.";
					return view("EmpOfflineProcess/email_process",compact('successmsg','failedmsg'));
				}
				else
				{
					if($emp_id !='' && $actionid !='')
					{
						if($actionid==1)
						{
							$offlineEmpData = EmpOffline::where("emp_id",$emp_id)->orderBy('id','DESC')->first();
							
							$offlineEmpData->zeeshan_suggested_status =1;
							$offlineEmpData->suggested_user =$userid;
							$offlineEmpData->suggested_at =date('Y-m-d H:i:s');
							$offlineEmpData->save();
		
							$successmsg="Request Suggestion (Exit Interview) Sent Successfully.";
							return view("EmpOfflineProcess/email_process",compact('successmsg','failedmsg'));
		
						}
		
						if($actionid==2)
						{
							$offlineEmpData = EmpOffline::where("emp_id",$emp_id)->orderBy('id','DESC')->first();
							
							$offlineEmpData->zeeshan_suggested_status =2;
							$offlineEmpData->suggested_user =$userid;
							$offlineEmpData->suggested_at =date('Y-m-d H:i:s');
							$offlineEmpData->save();
		
							$successmsg="Request Suggestion (Proceed to F&F) Sent Successfully.";
							return view("EmpOfflineProcess/email_process",compact('successmsg','failedmsg'));
						}
					}
					else
					{
						return response()->json(['success'=>'Employee not Found.']);
		
					}
				}

			}
			else
			{
				$successmsg='';
				$failedmsg="To Approved/Disapproved request, Please firstly login in Portal and then again click on Approved/Disapproved through Email.";
				//return response()->json(['success'=>'Please firstly login in Portal.']);
				return view("EmpOfflineProcess/email_process",compact('successmsg','failedmsg'));
			}
   
   
		   
	   }
   
   
  
   
	   public function cronOneProcess(Request $request)
	   {
		   $empData = DepartmentFormEntry::where("form_id",1)->whereNull("cron_status_login")->orderBy('id','ASC')->get();

		   if (count($empData) === 0) 
		   {
				return response()->json(['success'=>'No Any records found to Update.']);
	   	   }
		   else
		   {
				foreach($empData as $data)
				{
					$empnewData = MashreqLoginMIS::where("ref_no",$data->ref_no)->orderBy('id','DESC')->first();
					
					if($empnewData)
					{
						$empparentData = DepartmentFormEntry::where("ref_no",$data->ref_no)->orderBy('id','DESC')->first();
						$empparentData->cron_status_login = 1; 	
						$empparentData->customername_login = $empnewData->customer_name;			                       
						$empparentData->save();		
						echo "Success";
					}
					else
					{
						echo "Failed";
					}					
				}
				return response()->json(['success'=>'Records Updated Successfully.']);
		   }		   
	   }
   
   
   
   
	   public function cronBookingProcess(Request $request)
	   {
		   $empData = DepartmentFormEntry::where("form_id",1)->whereNull("cron_status_booking")->orderBy('id','ASC')->get();

		   if (count($empData) === 0) 
		   {
				return response()->json(['success'=>'No Any records found to Update.']);
	   	   }
		   else
		   {
				foreach($empData as $data)
				{
					$empnewData = MashreqBookingMIS::where("instanceid",$data->application_id)->orderBy('id','DESC')->first();
					
					if($empnewData)
					{
						$empparentData = DepartmentFormEntry::where("application_id",$data->application_id)->orderBy('id','DESC')->first();
						$empparentData->cron_status_booking = 1; 	
						$empparentData->date_of_disbursal = $empnewData->dateofdisbursal;			                       
						$empparentData->save();		
		
						echo "Success";
					}
					else
					{
						echo "Failed";
					}					
				}

				return response()->json(['success'=>'Records Updated Successfully.']);
		   }		   
	   }
   
   


 	// New code End (29-05-2024)

public function offboardpaymentconfirmComplete(Request $request)
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
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at> "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_a<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('empoffboard_ffstatus_filter_list')) && $request->session()->get('empoffboard_ffstatus_filter_list') != 'All')
				{
					$offboard_ffstatus = $request->session()->get('empoffboard_ffstatus_filter_list');
					
					 $offboard_ffstatusArray = explode(",",$offboard_ffstatus);
					 $offlinestatusdata= OffboardEMPData::whereIn('settelement_confirmation_status',$offboard_ffstatusArray)->get();
					 $ffstatusarray=array();
					 foreach($offlinestatusdata as $_ffstatus){
						 $ffstatusarray[]=$_ffstatus->emp_id;
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalffstatus=implode(",", $ffstatusarray);
					if($whereraw == '')
					{
						$whereraw = 'id IN('.$finalffstatus.')';
					}
					else
					{
						$whereraw .= ' And id IN('.$finalffstatus.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				if(!empty($request->session()->get('paymentconfirm_datefrom_offboard_lastworkingday_list')) && $request->session()->get('paymentconfirm_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('paymentconfirm_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('paymentconfirm_dateto_offboard_lastworkingday_list')) && $request->session()->get('paymentconfirm_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('paymentconfirm_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('paymentconfirm_datefrom_offboard_dort_list')) && $request->session()->get('paymentconfirm_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('paymentconfirm_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('paymentconfirm_dateto_offboard_dort_list')) && $request->session()->get('paymentconfirm_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('paymentconfirm_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
				if(!empty($request->session()->get('fnf_offboard_paymentconfirm_filter_list')) && $request->session()->get('fnf_offboard_paymentconfirm_filter_list') != 'All')
				{
					$amount = $request->session()->get('fnf_offboard_paymentconfirm_filter_list');
					
					 if($amount==1){
						$offlinestatusdata= OffboardEMPData::where('payment_confirmation_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($amount==2){
						 $offlinestatusdata= OffboardEMPData::where('payment_confirmation_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}	
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candmashreq_filter_inner_list')) && $request->session()->get('email_candmashreq_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candmashreq_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('desc_candmashreq_filter_inner_list')) && $request->session()->get('desc_candmashreq_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candmashreq_filter_inner_list');
					 $selectedFilter['DESC'] = $desc;
					 if($whereraw == '')
					{
						$whereraw = 'designation = "'.$desc.'"';
					}
					else
					{
						$whereraw .= ' And designation = "'.$desc.'"';
					}
				}
				if(!empty($request->session()->get('dept_candmashreq_filter_inner_list')) && $request->session()->get('dept_candmashreq_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candmashreq_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candmashreq_filter_inner_list')) && $request->session()->get('status_candmashreq_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candmashreq_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candmashreq_filter_inner_list')) && $request->session()->get('vintage_candmashreq_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candmashreq_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				
				
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = EmpOffline::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = EmpOffline::whereRaw($whereraw)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
					
				}
				foreach($recruter_details as $_recruter_details)
				{
					//echo $_f->first_name;exit;
					$CandidateRecruiterArray[$_recruter_details->id] = $_recruter_details->name;
				}
				
				$CandidateNameArray = array();
				if($whereraw == '')
				{
					/* echo "sddf";
					exit; */
				$c_namedata = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$c_namedata = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$email = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$visa = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($visa as $_company)
				{
					//echo $_f->first_name;exit;
					if($_company->company_visa!=''){
					$companyvisaArray[$_company->company_visa] = $_company->company_visa;
					}
				}
				
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = EmpOffline::where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = EmpOffline::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = EmpOffline::whereRaw($whereraw)->where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				
				$DesignationArray = array();
				if($whereraw == '')
				{
					$depidArray = EmpOffline::where("department",36)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
					
				}
				foreach($desc as $_desc)
				{
					//echo $_f->first_name;exit;
					$DesignationArray[$_desc->id] = $_desc->name;
				}
				
				$OpeningArray = array();
				if($whereraw == '')
				{
				$jobArray = EmpOffline::where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
					$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
					
				}
				foreach($opening as $_opening)
				{
					//echo $_f->first_name;exit;
					//$OpeningArray[$_opening->id] = $_opening->name;
					$dept=Department::where("id",$_opening->department)->first();
					//echo $_f->first_name;exit;
					$OpeningArray[$_opening->id] = $_opening->name ." (".$dept->department_name." - ".$_opening->location.")";
				}
				$StatusArray = array();
				if($whereraw == '')
				{
				$status =  EmpOffline::where("department",36)->get();
				}
				else
				{
					$status =  EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = EmpOffline::where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
					$department =Department::whereIn('id',array_unique($dpetList))->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$DepartmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetail = EmpOffline::whereIn("condition_leaving",array(4,5,6))->whereRaw($whereraw)->orderBy("settelement_date", "DESC")->get();
				}
				else
				{
					
					$documentCollectiondetail = EmpOffline::whereIn("condition_leaving",array(4,5,6))->orderBy("settelement_date", "DESC")->get();
					
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				
				 $ffstatusarray=array();
				 foreach($documentCollectiondetail as $_ffstatus){
					// echo  $_ffstatus;exit;
					 $offlinestatusdata= OffboardEMPData::where("emp_id",$_ffstatus->id)->where('payment_confirmation_status',1)->first();
					 if($offlinestatusdata!=''){
					 $ffstatusarray[]=$offlinestatusdata->emp_id;
					 }
					 
					 
				 } 
				
				//print_r($ffstatusarray);exit;
				$documentCollectiondetails = EmpOffline::whereIn("id",$ffstatusarray)->paginate($paginationValue);
				$reportsCount=EmpOffline::whereIn("id",$ffstatusarray)->get()->count();
				$documentCollectiondetails->setPath(config('app.url/offboardpaymentconfirmComplete'));
				
		return view("EmpOfflineProcess/offboardpaymentconfirmComplete",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
	  public function offboardpaymentconfirmNotComplete(Request $request)
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
		//$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  $whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('datefrom_offboard_filter_inner_list')) && $request->session()->get('datefrom_offboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at> "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_filter_inner_list')) && $request->session()->get('dateto_offboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_a<= "'.$dateto.' 23:59:59"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 23:59:59"';
					}
				}
				if(!empty($request->session()->get('departmentId_filter_inner_list')) && $request->session()->get('departmentId_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('departmentId_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'department IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And department IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('teamleader_filter_inner_list')) && $request->session()->get('teamleader_filter_inner_list') != 'All')
				{
					$teamlead = $request->session()->get('teamleader_filter_inner_list');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'tl_se IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And tl_se IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('empid_emp_offboard_filter_inner_list')) && $request->session()->get('empid_emp_offboard_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_offboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('empoffboard_ffstatus_filter_list')) && $request->session()->get('empoffboard_ffstatus_filter_list') != 'All')
				{
					$offboard_ffstatus = $request->session()->get('empoffboard_ffstatus_filter_list');
					
					 $offboard_ffstatusArray = explode(",",$offboard_ffstatus);
					 $offlinestatusdata= OffboardEMPData::whereIn('settelement_confirmation_status',$offboard_ffstatusArray)->get();
					 $ffstatusarray=array();
					 foreach($offlinestatusdata as $_ffstatus){
						 $ffstatusarray[]=$_ffstatus->emp_id;
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalffstatus=implode(",", $ffstatusarray);
					if($whereraw == '')
					{
						$whereraw = 'id IN('.$finalffstatus.')';
					}
					else
					{
						$whereraw .= ' And id IN('.$finalffstatus.')';
					}
				}
				if(!empty($request->session()->get('name_emp_offboard_filter_inner_list')) && $request->session()->get('name_emp_offboard_filter_inner_list') != 'All')
				{
					$fname = $request->session()->get('name_emp_offboard_filter_inner_list');
					 $cnameArray = explode(",",$fname);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
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
				if(!empty($request->session()->get('paymentconfirm_datefrom_offboard_lastworkingday_list')) && $request->session()->get('paymentconfirm_datefrom_offboard_lastworkingday_list') != 'All')
				{
					$lastworkingday = $request->session()->get('paymentconfirm_datefrom_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('paymentconfirm_dateto_offboard_lastworkingday_list')) && $request->session()->get('paymentconfirm_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('paymentconfirm_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('paymentconfirm_datefrom_offboard_dort_list')) && $request->session()->get('paymentconfirm_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('paymentconfirm_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('paymentconfirm_dateto_offboard_dort_list')) && $request->session()->get('paymentconfirm_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('paymentconfirm_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"';
					}
				}
				if(!empty($request->session()->get('fnf_offboard_paymentconfirm_filter_list')) && $request->session()->get('fnf_offboard_paymentconfirm_filter_list') != 'All')
				{
					$amount = $request->session()->get('fnf_offboard_paymentconfirm_filter_list');
					
					 if($amount==1){
						$offlinestatusdata= OffboardEMPData::where('payment_confirmation_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalffstatus.')';
						}
					 }
					 else if($amount==2){
						 $offlinestatusdata= OffboardEMPData::where('payment_confirmation_status',1)->get();
						 $ffstatusarray=array();
						 foreach($offlinestatusdata as $_ffstatus){
							 $ffstatusarray[]=$_ffstatus->emp_id;
							 
							 
						 } 
						 $finalffstatus=implode(",", $ffstatusarray);
						if($whereraw == '')
						{
							$whereraw = 'id Not IN('.$finalffstatus.')';
						}
						else
						{
							$whereraw .= ' And id Not IN('.$finalffstatus.')';
						}
						 
					 }
					 else{
						 
					 }
					 
					 //print_r($ffstatusarray);//exit;
					 
				}
if(!empty($request->session()->get('empoffboard_status_filter_list')) && $request->session()->get('empoffboard_status_filter_list') != 'All')
				{
					$status = $request->session()->get('empoffboard_status_filter_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'condition_leaving IN('.$status.')';
					}
					else
					{
						$whereraw .= ' And condition_leaving IN('.$status.')';
					}
				}
if(!empty($request->session()->get('ReasonofAttrition_empoffboard_filter_list')) && $request->session()->get('ReasonofAttrition_empoffboard_filter_list') != 'All')
				{
					$ReasonofAttrition = $request->session()->get('ReasonofAttrition_empoffboard_filter_list');
					 $ReasonofAttritionArray = explode(",",$ReasonofAttrition);
					 $ReasonofAttritionfinalarray=array();
					 foreach($ReasonofAttritionArray as $resign){
						 $ReasonofAttritionfinalarray[]="'".$resign."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalresign=implode(",", $ReasonofAttritionfinalarray);
					if($whereraw == '')
					{
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.')';
					}
				}	
				if(!empty($request->session()->get('design_empoffboard_filter_inner_list')) && $request->session()->get('design_empoffboard_filter_inner_list') != 'All')
				{
					$designd = $request->session()->get('design_empoffboard_filter_inner_list');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'designation IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And designation IN('.$designd.')';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN('.$rec_id.')';
					}
				}
				//echo $cname;exit;
				if(!empty($request->session()->get('email_candmashreq_filter_inner_list')) && $request->session()->get('email_candmashreq_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candmashreq_filter_inner_list');
					 $selectedFilter['CEMAIL'] = $email;
					 if($whereraw == '')
					{
						$whereraw = 'email = "'.$email.'"';
					}
					else
					{
						$whereraw .= ' And email = "'.$email.'"';
					}
				}
				if(!empty($request->session()->get('desc_candmashreq_filter_inner_list')) && $request->session()->get('desc_candmashreq_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candmashreq_filter_inner_list');
					 $selectedFilter['DESC'] = $desc;
					 if($whereraw == '')
					{
						$whereraw = 'designation = "'.$desc.'"';
					}
					else
					{
						$whereraw .= ' And designation = "'.$desc.'"';
					}
				}
				if(!empty($request->session()->get('dept_candmashreq_filter_inner_list')) && $request->session()->get('dept_candmashreq_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candmashreq_filter_inner_list');
					 $selectedFilter['DEPT'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$dept.'"';
					}
				}
				if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
				{
					$opening = $request->session()->get('opening_cand_filter_inner_list');
					 $selectedFilter['OPENING'] = $opening;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening IN('.$opening.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$opening.')';
					}
				}
				if(!empty($request->session()->get('status_candmashreq_filter_inner_list')) && $request->session()->get('status_candmashreq_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candmashreq_filter_inner_list');
					 $selectedFilter['STATUS'] = $status;
					 if($whereraw == '')
					{
						$whereraw = 'status = "'.$status.'"';
					}
					else
					{
						$whereraw .= ' And status = "'.$status.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('vintage_candmashreq_filter_inner_list')) && $request->session()->get('vintage_candmashreq_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candmashreq_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				
				
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = EmpOffline::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = EmpOffline::whereRaw($whereraw)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
					
				}
				foreach($recruter_details as $_recruter_details)
				{
					//echo $_f->first_name;exit;
					$CandidateRecruiterArray[$_recruter_details->id] = $_recruter_details->name;
				}
				
				$CandidateNameArray = array();
				if($whereraw == '')
				{
					/* echo "sddf";
					exit; */
				$c_namedata = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$c_namedata = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$email = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = EmpOffline::where("department",36)->get();
				}
				else
				{
					
					$visa = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($visa as $_company)
				{
					//echo $_f->first_name;exit;
					if($_company->company_visa!=''){
					$companyvisaArray[$_company->company_visa] = $_company->company_visa;
					}
				}
				
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = EmpOffline::where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = EmpOffline::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = EmpOffline::whereRaw($whereraw)->where("department",36)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				
				$DesignationArray = array();
				if($whereraw == '')
				{
					$depidArray = EmpOffline::where("department",36)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					/* echo '<pre>';
					print_r($depidArray);
					exit; */
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
					
				}
				foreach($desc as $_desc)
				{
					//echo $_f->first_name;exit;
					$DesignationArray[$_desc->id] = $_desc->name;
				}
				
				$OpeningArray = array();
				if($whereraw == '')
				{
				$jobArray = EmpOffline::where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
					$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
					
				}
				foreach($opening as $_opening)
				{
					//echo $_f->first_name;exit;
					//$OpeningArray[$_opening->id] = $_opening->name;
					$dept=Department::where("id",$_opening->department)->first();
					//echo $_f->first_name;exit;
					$OpeningArray[$_opening->id] = $_opening->name ." (".$dept->department_name." - ".$_opening->location.")";
				}
				$StatusArray = array();
				if($whereraw == '')
				{
				$status =  EmpOffline::where("department",36)->get();
				}
				else
				{
					$status =  EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = EmpOffline::where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = EmpOffline::whereRaw($whereraw)->where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
					$department =Department::whereIn('id',array_unique($dpetList))->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$DepartmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}
				
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetail = EmpOffline::whereIn("condition_leaving",array(4,5,6))->whereRaw($whereraw)->orderBy("settelement_date", "DESC")->get();
				}
				else
				{
					
					$documentCollectiondetail = EmpOffline::whereIn("condition_leaving",array(4,5,6))->orderBy("settelement_date", "DESC")->get();
					
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$ffstatusarray=array();
				 foreach($documentCollectiondetail as $_ffstatus){
					// echo  $_ffstatus;exit;
					 $offlinestatusdata= OffboardEMPData::where("emp_id",$_ffstatus->id)->whereNull('payment_confirmation_status')->first();
					 if($offlinestatusdata!=''){
					 $ffstatusarray[]=$offlinestatusdata->emp_id;
					 }
					 
					 
				 } 
				
				//print_r($ffstatusarray);exit;
				$documentCollectiondetails = EmpOffline::whereIn("id",$ffstatusarray)->paginate($paginationValue);
				$reportsCount=EmpOffline::whereIn("id",$ffstatusarray)->get()->count();
				$documentCollectiondetails->setPath(config('app.url/offboardpaymentconfirmNotComplete'));
				
		return view("EmpOfflineProcess/offboardpaymentconfirmNotComplete",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
	   
	   public function offboardDataFilterexitRetained(Request $request)
		{
			//print_r($request->input());
			
			$exittype=$request->input('exittype');
			$retained=$request->input('retained');
			$datetolastworkingday = $request->input('datetolastworkingday');
			$datefromlastworkingday = $request->input('datefromlastworkingday');
			$datetodort = $request->input('datetodort');
			$datefromdort = $request->input('datefromdort');
			$request->session()->put('offboardexittype_filter_inner_list_Retained',$exittype);
			$request->session()->put('exit_dateto_offboard_lastworkingday_list_Retained',$datetolastworkingday);
			$request->session()->put('exit_datefrom_offboard_lastworkingday_list_Retained',$datefromlastworkingday);
			$request->session()->put('exit_datefrom_offboard_dort_list_Retained',$datefromdort);
			$request->session()->put('exit_dateto_offboard_dort_list_Retained',$datetodort);
			$request->session()->put('offboard_retained_filter_inner_list_Retained',$retained);
			
		}
		public function resetoffboardListDataFilterexitRetained(Request $request){
			$request->session()->put('offboardexittype_filter_inner_list_Retained','');
			$request->session()->put('exit_dateto_offboard_lastworkingday_list_Retained','');
			$request->session()->put('exit_datefrom_offboard_lastworkingday_list_Retained','');
			$request->session()->put('exit_datefrom_offboard_dort_list_Retained','');
			$request->session()->put('exit_dateto_offboard_dort_list_Retained','');
			$request->session()->put('offboard_retained_filter_inner_list_Retained','');
		}





}
