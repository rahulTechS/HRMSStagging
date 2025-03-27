<?php

namespace App\Http\Controllers\ChangeSalaryNew;

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
use App\Models\Employee\SalaryRequest;
use App\Models\Employee\ChangeSalary;
use App\Models\EmpProcess\JobFunctionPermission;
use App\Models\Changesalary\Employee_details_change_salary;
use App\Models\Changesalary\Change_Salary_logs;
use App\Services\LoggerFactory;

use App\Models\Employee\ExportDataLog;




class ChangeSalaryController extends Controller
{
    public function __construct(LoggerFactory $logFactory)
    {
        $this->log = $logFactory->setPath('logs/changesalary')->createLogger('changesalary'); 
    }
       public function Index(Request $req)
	   {
		$ReasonsForLeavingDetails = ReasonsForLeaving::where("status",1)->get();
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();
		//$empId=EmpOffline::get();
		$empId = Employee_details::where("offline_status",1)->get();

		$requestTypedata = SalaryRequest::orderBy('id','asc')->get();

		
		$Designation=Designation::where("status",1)->get();
		return view("ChangeSalaryNew/Index",compact('ReasonsForLeavingDetails','departmentLists','tL_details','empId','Designation','requestTypedata'));
	   }

	public static function getAgentSalary($empId)
	{
		//$empDetailsModel = Employee_attribute::where("emp_id",$empId)->where("attribute_code","total_gross_salary")->first();

		$empDetails = Employee_details::where("emp_id",$empId)->whereNotNull('actual_salary')->first();

		if($empDetails)
		{
			return $empDetails->actual_salary;
		}
		else
		{
			$empDetailsModel = Employee_attribute::where("emp_id",$empId)->where("attribute_code","actual_salary")->first();
			if($empDetailsModel != '')
			{
				$basic_salary = $empDetailsModel->attribute_values;
				if($basic_salary != '' && $basic_salary != NULL)
				{
					return $basic_salary;
				}
				else
				{
					return 0;
				}
			}
			else
			{
				return 0;
			}
		}



		
	}

	public static function getEmpName($empId)
	{
		$empDetails = Employee_details::where("emp_id",$empId)->first();

		if($empDetails)
		{
			return $empDetails->emp_name;
		}
		else
		{
			return "--";
		}
	}
	   
	   public function listingRequestedEmployee(Request $request)
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
			
			if(!empty($request->session()->get('changesalary_page_limit')))
				{
					$paginationValue = $request->session()->get('changesalary_page_limit');
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
				
				
				if(!empty($request->session()->get('change_salary_fromdate')) && $request->session()->get('change_salary_fromdate') != 'All')
				{
					$datefrom = $request->session()->get('change_salary_fromdate');
					 if($whereraw == '')
					{
						$whereraw = 'change_salary_request.created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And change_salary_request.created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_todate')) && $request->session()->get('change_salary_todate') != 'All')
				{
					$dateto = $request->session()->get('change_salary_todate');
					 if($whereraw == '')
					{
						$whereraw = 'change_salary_request.created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And change_salary_request.created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
				{
					$dept = $request->session()->get('change_salary_department');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'employee_details.dept_id IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('change_salary_teamleader');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
					}
					else
					{
						$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
					}
				}
				if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
				{
					$empId = $request->session()->get('change_salary_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'change_salary_request.emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And change_salary_request.emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
				{
					$fname = $request->session()->get('chnage_salary_emp_name');
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
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'" OR  last_working_day_terminate>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'" OR last_working_day_terminate>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('leaving_dateto_offboard_lastworkingday_list')) && $request->session()->get('leaving_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('leaving_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('change_salary_designation')) && $request->session()->get('change_salary_designation') != 'All')
				{
					$designd = $request->session()->get('change_salary_designation');
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
				if(!empty($request->session()->get('leaving_datefrom_offboard_dort_list')) && $request->session()->get('leaving_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('leaving_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'" OR  date_of_terminate>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'" OR date_of_terminate>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('leaving_dateto_offboard_dort_list')) && $request->session()->get('leaving_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('leaving_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
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
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
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
				
				

				
				
				if($whereraw != '')
				{
					$empsessionId=$request->session()->get('EmployeeId');
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
					if($departmentDetails != '')
					{
						$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
						if($empDetails!='')
						{
							$documentCollectiondetails = Employee_details::
							join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
							->select('employee_details.*', 'change_salary_request.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->whereRaw($whereraw)
							->where('change_salary_request.incrementstatus', 0)
							->where('change_salary_request.finalstatus', 0)
							->where('change_salary_request.approvedrejectstatus', 0)
							
							->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
		
							$reportsCount = Employee_details::
							join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
							->select('employee_details.*', 'change_salary_request.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->whereRaw($whereraw)
							->where('change_salary_request.incrementstatus', 0)
							->where('change_salary_request.finalstatus', 0)
							->where('change_salary_request.approvedrejectstatus', 0)
							
							->get()->count();
						}
					}
					else{
						$documentCollectiondetails = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->whereRaw($whereraw)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.finalstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 0)
						
						->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
	
						$reportsCount = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->whereRaw($whereraw)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.finalstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 0)
						
						->get()->count();
					}
					
					
					
					
					
					
					
					

					

				}
				else
				{
					$empsessionId=$request->session()->get('EmployeeId');
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
					if($departmentDetails != '')
					{
						$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
						if($empDetails!='')
						{
							$documentCollectiondetails = Employee_details::
							join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
							->select('employee_details.*', 'change_salary_request.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->where('change_salary_request.incrementstatus', 0)
							->where('change_salary_request.finalstatus', 0)
							->where('change_salary_request.approvedrejectstatus', 0)
							->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);

							$reportsCount = Employee_details::
							join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
							->select('employee_details.*', 'change_salary_request.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->where('change_salary_request.incrementstatus', 0)
							->where('change_salary_request.finalstatus', 0)
							->where('change_salary_request.approvedrejectstatus', 0)
							
							->orderBy('change_salary_request.id', 'desc')->get()->count();
						}
					}
					else{
						$documentCollectiondetails = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.finalstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 0)
						->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);

						$reportsCount = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.finalstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 0)
						
						->orderBy('change_salary_request.id', 'desc')->get()->count();
					}


					
					

				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
				$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
				$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				$documentCollectiondetails->setPath(config('app.url/listingEmpRequested'));						

				return view("ChangeSalaryNew/listingEmpRequested",compact('departmentLists','productDetails','paginationValue','designationDetails','documentCollectiondetails','reportsCount'));
	   }
	   
	   
	   
	    public function listingChangeSalaryAll(Request $request)
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
			
				if(!empty($request->session()->get('changesalary_page_limit')))
				{
					$paginationValue = $request->session()->get('changesalary_page_limit');
				}
				else
				{
					$paginationValue = 100;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('change_salary_fromdate')) && $request->session()->get('change_salary_fromdate') != 'All')
				{
					$datefrom = $request->session()->get('change_salary_fromdate');
					 if($whereraw == '')
					{
						$whereraw = 'change_salary_request.created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And change_salary_request.created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_todate')) && $request->session()->get('change_salary_todate') != 'All')
				{
					$dateto = $request->session()->get('change_salary_todate');
					 if($whereraw == '')
					{
						$whereraw = 'change_salary_request.created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And change_salary_request.created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
				{
					$dept = $request->session()->get('change_salary_department');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'change_salary_request.dept_id IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And change_salary_request.dept_id IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('change_salary_teamleader');
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
				if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
				{
					$empId = $request->session()->get('change_salary_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'change_salary_request.emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And change_salary_request.emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
				{
					$fname = $request->session()->get('chnage_salary_emp_name');
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
						$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('change_salary_designation')) && $request->session()->get('change_salary_designation') != 'All')
				{
					$designd = $request->session()->get('change_salary_designation');
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
				if(!empty($request->session()->get('datefrom_offboard_dort_list')) && $request->session()->get('datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign< "'.$dortfrom.'" OR  date_of_terminate< "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign< "'.$dortfrom.'" OR date_of_terminate< "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_dort_list')) && $request->session()->get('dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign> "'.$dortto.'"  OR  date_of_terminate> "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign> "'.$dortto.'"  OR  date_of_terminate> "'.$dortto.'"';
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
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
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
				}




				if(!empty($request->session()->get('change_salary_requestType_status')) && $request->session()->get('change_salary_requestType_status') != 'All')
				{
					$requestType = $request->session()->get('change_salary_requestType_status');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'change_salary_request.request_type IN('.$requestType.')';
					}
					else
					{
						$whereraw .= ' And change_salary_request.request_type IN('.$requestType.')';
					}
				}




				




				if($whereraw != '')
				{
					
					// echo "<pre>";
					// print_r($whereraw);
					// exit;



					$empsessionId=$request->session()->get('EmployeeId');
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
					if($departmentDetails != '')
					{
						$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
						if($empDetails!='')
						{
							$documentCollectiondetails = Employee_details::
							join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
							->select('employee_details.*', 'change_salary_request.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->whereRaw($whereraw)
							->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
		
							$reportsCount = Employee_details::
							join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
							->select('employee_details.*', 'change_salary_request.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->whereRaw($whereraw)
							->orderBy('change_salary_request.id', 'desc')->get()->count();
						}
					}
					else{
						$documentCollectiondetails = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->whereRaw($whereraw)
						->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
	
						$reportsCount = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->whereRaw($whereraw)
						->orderBy('change_salary_request.id', 'desc')->get()->count();
					}

					
					
					
					//echo "hello";exit;

					

					
				}
				else
				{
					$empsessionId=$request->session()->get('EmployeeId');
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
					if($departmentDetails != '')
					{
						$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
						if($empDetails!='')
						{
							$documentCollectiondetails = Employee_details::
							join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
							->select('employee_details.*', 'change_salary_request.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
		
							$reportsCount = Employee_details::
							join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
							->select('employee_details.*', 'change_salary_request.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->orderBy('change_salary_request.id', 'desc')->get()->count();
						}
					}
					else{
						$documentCollectiondetails = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
	
						$reportsCount = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->orderBy('change_salary_request.id', 'desc')->get()->count();
					}





					
				}
					$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				

				$documentCollectiondetails->setPath(config('app.url/listingAllEmployee'));
				
		//print_r($documentCollectiondetails);exit;
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("ChangeSalaryNew/listingAllEmployee",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	   }





	   public static function getTimeFromJoining($empid)
	   {
		  // echo $empid;
		   $empId = Employee_details::where("emp_id",$empid)->first();
		   $empDOJObj  = Employee_attribute::where("attribute_code","DOJ")->where('emp_id',$empid)->first();
		   //return $empDOJObj;
		   if($empDOJObj != '')
		   {
			   $doj = $empDOJObj->attribute_values;
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




	//    public static function getTimeFromJoiningOLD($empid)
	// 		{
	// 			//echo $empid;
	// 			$empId = Employee_details::where("id",$empid)->first();
	// 			$empDOJObj  = Employee_attribute::where("attribute_code","DOJ")->where('emp_id',$empId)->first();
	// 			if($empDOJObj != '')
	// 			{
	// 				$doj = $empDOJObj->attribute_values;
	// 				if($doj == NULL || $doj == '')
	// 				{
	// 					return "Not Decleared";
	// 				}
	// 				else
	// 				{
	// 					$doj = str_replace("/","-",$doj);
	// 					$date1 = date("Y-m-d",strtotime($doj));

	// 					$date2 =  date("Y-m-d");

	// 					$diff = abs(strtotime($date2)-strtotime($date1));

	// 					$years = floor($diff / (365*60*60*24));

	// 					$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

	// 					$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
	// 					$returnData = '';
	// 					if($years != 0)
	// 					{
	// 					$returnData .=  $years." Years, ";
	// 					}
	// 					if($months != 0)
	// 					{
	// 					$returnData .=  $months." months, ";
	// 					}
	// 					 $returnData .= $days." days.";
	// 					 return  $returnData;
	// 				}
					
	// 			}
	// 			else
	// 			{
	// 				return "Not Decleared";
	// 			}
	// 		}
	   
	   public function listingIncrement(Request $request)
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
			
			if(!empty($request->session()->get('changesalary_page_limit')))
				{
					$paginationValue = $request->session()->get('changesalary_page_limit');
				}
				else
				{
					$paginationValue = 100;
				}
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('change_salary_fromdate')) && $request->session()->get('change_salary_fromdate') != 'All')
				{
					$datefrom = $request->session()->get('change_salary_fromdate');
					 if($whereraw == '')
					{
						$whereraw = 'change_salary_request.created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And change_salary_request.created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_todate')) && $request->session()->get('change_salary_todate') != 'All')
				{
					$dateto = $request->session()->get('change_salary_todate');
					 if($whereraw == '')
					{
						$whereraw = 'change_salary_request.created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And change_salary_request.created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
				{
					$dept = $request->session()->get('change_salary_department');
					 //$departmentArray = explode(",",$dept);
					if($whereraw == '')
					{
						$whereraw = 'change_salary_request.dept_id IN('.$dept.')';
					}
					else
					{
						$whereraw .= ' And change_salary_request.dept_id IN('.$dept.')';
					}
				}
				if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('change_salary_teamleader');
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
				if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
				{
					$empId = $request->session()->get('change_salary_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'change_salary_request.emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And change_salary_request.emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
				{
					$fname = $request->session()->get('chnage_salary_emp_name');
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
						$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('change_salary_designation')) && $request->session()->get('change_salary_designation') != 'All')
				{
					$designd = $request->session()->get('change_salary_designation');
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
				if(!empty($request->session()->get('datefrom_offboard_dort_list')) && $request->session()->get('datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign< "'.$dortfrom.'" OR  date_of_terminate< "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign< "'.$dortfrom.'" OR date_of_terminate< "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('dateto_offboard_dort_list')) && $request->session()->get('dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign> "'.$dortto.'"  OR  date_of_terminate> "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign> "'.$dortto.'"  OR  date_of_terminate> "'.$dortto.'"';
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
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
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
				}



				





				if($whereraw != '')
				{
					
					
					$empsessionId=$request->session()->get('EmployeeId');
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
					if($departmentDetails != '')
					{
						$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
						if($empDetails!='')
						{
							$documentCollectiondetails = Employee_details::
							join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
							->select('employee_details.*', 'change_salary_request.*')
							
							->whereRaw($whereraw)
							->where('change_salary_request.request_type', 1)
							->where('change_salary_request.incrementstatus', 0)
							->where('change_salary_request.approvedrejectstatus', 1)
							->where('employee_details.dept_id',$empDetails->dept_id)
							->orWhere('change_salary_request.request_type', 3)
							->where('change_salary_request.incrementstatus', 0)
							->where('change_salary_request.approvedrejectstatus', 1)
							->where('employee_details.dept_id',$empDetails->dept_id)
							->orWhere('change_salary_request.request_type', 4)
							->where('change_salary_request.incrementstatus', 0)
							->where('change_salary_request.approvedrejectstatus', 1)
							->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
		
							$reportsCount = Employee_details::
							join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
							->select('employee_details.*', 'change_salary_request.*')
							
							->whereRaw($whereraw)
							->where('change_salary_request.request_type', 1)
							->where('change_salary_request.incrementstatus', 0)
							->where('change_salary_request.approvedrejectstatus', 1)
							->where('employee_details.dept_id',$empDetails->dept_id)
							->orWhere('change_salary_request.request_type', 3)
							->where('change_salary_request.incrementstatus', 0)
							->where('change_salary_request.approvedrejectstatus', 1)
							->where('employee_details.dept_id',$empDetails->dept_id)
							->orWhere('change_salary_request.request_type', 4)
							->where('change_salary_request.incrementstatus', 0)
							->where('change_salary_request.approvedrejectstatus', 1)
							->orderBy('change_salary_request.id', 'desc')->get()->count();
						}
					}
					else{
						$documentCollectiondetails = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->whereRaw($whereraw)
						->where('change_salary_request.request_type', 1)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)
						->orWhere('change_salary_request.request_type', 3)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)
							//->where('change_salary_request.incrementstatus', 2)
							->orWhere('change_salary_request.request_type', 4)
							->where('change_salary_request.incrementstatus', 0)
							->where('change_salary_request.approvedrejectstatus', 1)
						->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
	
						$reportsCount = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->whereRaw($whereraw)
						->where('change_salary_request.request_type', 1)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)
						->orWhere('change_salary_request.request_type', 3)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)
							//->where('change_salary_request.incrementstatus', 2)
							->orWhere('change_salary_request.request_type', 4)
							->where('change_salary_request.incrementstatus', 0)
							->where('change_salary_request.approvedrejectstatus', 1)
						->orderBy('change_salary_request.id', 'desc')->get()->count();
					}
					
					
					//echo "hello";exit;
					



					
				}
				else
				{
					
					$empsessionId=$request->session()->get('EmployeeId');
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
					if($departmentDetails != '')
					{
						$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
						if($empDetails!='')
						{
							$documentCollectiondetails = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->where('change_salary_request.request_type', 1)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)
						->where('employee_details.dept_id',$empDetails->dept_id)
						->orWhere('change_salary_request.request_type', 3)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)
						->where('employee_details.dept_id',$empDetails->dept_id)
						->orWhere('change_salary_request.request_type', 4)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)
						->orderBy('change_salary_request.id', 'desc')
						//->toSql();
						//dd($documentCollectiondetails);
						

						
						->paginate($paginationValue);
	
						$reportsCount = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->where('change_salary_request.request_type', 1)
						->where('change_salary_request.incrementstatus', 0)					
						->where('change_salary_request.approvedrejectstatus', 1)
						->where('employee_details.dept_id',$empDetails->dept_id)
						->orWhere('change_salary_request.request_type', 3)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)	
						->where('employee_details.dept_id',$empDetails->dept_id)
						->orWhere('change_salary_request.request_type', 4)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)					
						->orderBy('change_salary_request.id', 'desc')
						->get()->count();
						}
					}
					else{
						$documentCollectiondetails = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->where('change_salary_request.request_type', 1)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)
						->orWhere('change_salary_request.request_type', 3)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)
						->orWhere('change_salary_request.request_type', 4)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)
						->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
	
						$reportsCount = Employee_details::
						join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
						->select('employee_details.*', 'change_salary_request.*')
						->where('change_salary_request.request_type', 1)
						->where('change_salary_request.incrementstatus', 0)					
						->where('change_salary_request.approvedrejectstatus', 1)
						->orWhere('change_salary_request.request_type', 3)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)		
						->orWhere('change_salary_request.request_type', 4)
						->where('change_salary_request.incrementstatus', 0)
						->where('change_salary_request.approvedrejectstatus', 1)			
						->orderBy('change_salary_request.id', 'desc')
						->get()->count();
					}
					
					
					
					
					
					// echo "hello1";exit;

					




					
				}
					$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();







					$documentCollectiondetails->setPath(config('app.url/listingEmpIncrement'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("ChangeSalaryNew/listingEmpIncrement",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter'));
				

				
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
				
				if(!empty($request->session()->get('change_salary_fromdate')) && $request->session()->get('change_salary_fromdate') != 'All')
				{
					$datefrom = $request->session()->get('change_salary_fromdate');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_todate')) && $request->session()->get('change_salary_todate') != 'All')
				{
					$dateto = $request->session()->get('change_salary_todate');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
				{
					$dept = $request->session()->get('change_salary_department');
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
				if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('change_salary_teamleader');
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
				if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
				{
					$empId = $request->session()->get('change_salary_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
				{
					$fname = $request->session()->get('chnage_salary_emp_name');
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
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'" OR  last_working_day_terminate>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'" OR last_working_day_terminate>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_dateto_offboard_lastworkingday_list')) && $request->session()->get('cancelvisa_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('cancelvisa_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_datefrom_offboard_dort_list')) && $request->session()->get('cancelvisa_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('cancelvisa_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'" OR  date_of_terminate>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'" OR date_of_terminate>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('cancelvisa_dateto_offboard_dort_list')) && $request->session()->get('cancelvisa_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('cancelvisa_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
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
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
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
				
				if(!empty($request->session()->get('change_salary_designation')) && $request->session()->get('change_salary_designation') != 'All')
				{
					$designd = $request->session()->get('change_salary_designation');
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
	   public function listingMOLEmployee(Request $request)
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
		
		if(!empty($request->session()->get('changesalary_page_limit')))
			{
				$paginationValue = $request->session()->get('changesalary_page_limit');
			}
			else
			{
				$paginationValue = 100;
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
			
			
			if(!empty($request->session()->get('change_salary_fromdate')) && $request->session()->get('change_salary_fromdate') != 'All')
			{
				$datefrom = $request->session()->get('change_salary_fromdate');
				 if($whereraw == '')
				{
					$whereraw = 'change_salary_request.created_at>= "'.$datefrom.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And change_salary_request.created_at>= "'.$datefrom.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('change_salary_todate')) && $request->session()->get('change_salary_todate') != 'All')
			{
				$dateto = $request->session()->get('change_salary_todate');
				 if($whereraw == '')
				{
					$whereraw = 'change_salary_request.created_at<= "'.$dateto.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And change_salary_request.created_at<= "'.$dateto.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
			{
				$dept = $request->session()->get('change_salary_department');
				 //$departmentArray = explode(",",$dept);
				if($whereraw == '')
				{
					$whereraw = 'employee_details.dept_id IN('.$dept.')';
				}
				else
				{
					$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
				}
			}
			if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
			{
				$teamlead = $request->session()->get('change_salary_teamleader');
				 //$departmentArray = explode(",",$dept);
				if($whereraw == '')
				{
					$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
				}
				else
				{
					$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
				}
			}
			if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
			{
				$empId = $request->session()->get('change_salary_emp_id');
				 if($whereraw == '')
				{
					$whereraw = 'change_salary_request.emp_id IN ('.$empId.')';
				}
				else
				{
					$whereraw .= ' And change_salary_request.emp_id IN ('.$empId.')';
				}
			}
			if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
			{
				$fname = $request->session()->get('chnage_salary_emp_name');
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
					$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'" OR  last_working_day_terminate>= "'.$lastworkingday.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'" OR last_working_day_terminate>= "'.$lastworkingday.'"';
				}
			}
			if(!empty($request->session()->get('leaving_dateto_offboard_lastworkingday_list')) && $request->session()->get('leaving_dateto_offboard_lastworkingday_list') != 'All')
			{
				$dateto = $request->session()->get('leaving_dateto_offboard_lastworkingday_list');
				 if($whereraw == '')
				{
					$whereraw = 'last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
				}
			}
			if(!empty($request->session()->get('change_salary_designation')) && $request->session()->get('change_salary_designation') != 'All')
			{
				$designd = $request->session()->get('change_salary_designation');
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
			if(!empty($request->session()->get('leaving_datefrom_offboard_dort_list')) && $request->session()->get('leaving_datefrom_offboard_dort_list') != 'All')
			{
				$dortfrom = $request->session()->get('leaving_datefrom_offboard_dort_list');
				 if($whereraw == '')
				{
					$whereraw = 'date_of_resign>= "'.$dortfrom.'" OR  date_of_terminate>= "'.$dortfrom.'"';
				}
				else
				{
					$whereraw .= ' And date_of_resign>= "'.$dortfrom.'" OR date_of_terminate>= "'.$dortfrom.'"';
				}
			}
			if(!empty($request->session()->get('leaving_dateto_offboard_dort_list')) && $request->session()->get('leaving_dateto_offboard_dort_list') != 'All')
			{
				$dortto = $request->session()->get('leaving_dateto_offboard_dort_list');
				 if($whereraw == '')
				{
					$whereraw = 'date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
				}
				else
				{
					$whereraw .= ' And date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
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
					$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
				}
				else
				{
					$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
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
			
			
			
			// if($whereraw == '')
			// 	{
			// 		$whereraw = 'condition_leaving = 1 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL';
			// 	}
			// 	else
			// 	{
			// 		$whereraw .= ' And condition_leaving = 1 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL';
			// 	}
			






			if($whereraw != '')
			{
				
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				if($departmentDetails != '')
				{
					$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					if($empDetails!='')
					{
						$documentCollectiondetails = Employee_details::
					join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')
					
					->whereRaw($whereraw)
					->where('change_salary_request.request_type', 2)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					->where('employee_details.dept_id',$empDetails->dept_id)
					->orWhere('change_salary_request.request_type', 3)
					->whereRaw($whereraw)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					->where('employee_details.dept_id',$empDetails->dept_id)
					->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
		
		
					//$reportsCount = Employee_details::whereRaw($whereraw)->where("offline_status",1)->get()->count();
		
					$reportsCount = Employee_details::
					join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')
					->whereRaw($whereraw)
					->where('change_salary_request.request_type', 2)
					->where('change_salary_request.molstatus', 0)			
					->where('change_salary_request.approvedrejectstatus', 1)
					->where('employee_details.dept_id',$empDetails->dept_id)
					->orWhere('change_salary_request.request_type', 3)
					->whereRaw($whereraw)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					->where('employee_details.dept_id',$empDetails->dept_id)
					->get()->count();
					}
				}
				else{
					$documentCollectiondetails = Employee_details::
					join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')
					->whereRaw($whereraw)
					->where('change_salary_request.request_type', 2)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					->orWhere('change_salary_request.request_type', 3)
					->whereRaw($whereraw)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					//->where('change_salary_request.incrementstatus', 2)
					->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
		
		
					//$reportsCount = Employee_details::whereRaw($whereraw)->where("offline_status",1)->get()->count();
		
					$reportsCount = Employee_details::
					join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')
					->whereRaw($whereraw)
					->where('change_salary_request.request_type', 2)
					->where('change_salary_request.molstatus', 0)			
					->where('change_salary_request.approvedrejectstatus', 1)
					->orWhere('change_salary_request.request_type', 3)
					->whereRaw($whereraw)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					//->where('change_salary_request.incrementstatus', 2)
					->get()->count();
				}
				
				
				
				
				
				
				
				
				//echo $whereraw;
				//echo "hello";exit;
				//$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->orderBy("created_at","DESC")->paginate($paginationValue);


				
			

			//$reportsCount = ChangeSalary::get()->count();

				
			}
			else
			{
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				if($departmentDetails != '')
				{
					$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					if($empDetails!='')
					{
						$documentCollectiondetails = Employee_details::
					join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')
					
					->where('change_salary_request.request_type', 2)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					->where('employee_details.dept_id',$empDetails->dept_id)
					->orWhere('change_salary_request.request_type', 3)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					->where('employee_details.dept_id',$empDetails->dept_id)
					->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
		
						//$documentCollectiondetails = EmpOffline::whereRaw($whereraw1)->orderBy("created_at","DESC")->paginate($paginationValue);
					$reportsCount = Employee_details::
					join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')	
					
					->where('change_salary_request.request_type', 2)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					->where('employee_details.dept_id',$empDetails->dept_id)		
					->orWhere('change_salary_request.request_type', 3)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					->where('employee_details.dept_id',$empDetails->dept_id)		
					->get()->count();
					}
				}
				else{
					$documentCollectiondetails = Employee_details::
					join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')
					->where('change_salary_request.request_type', 2)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					->orWhere('change_salary_request.request_type', 3)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					//->where('change_salary_request.incrementstatus', 2)
					->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
		
						//$documentCollectiondetails = EmpOffline::whereRaw($whereraw1)->orderBy("created_at","DESC")->paginate($paginationValue);
					$reportsCount = Employee_details::
					join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')			
					->where('change_salary_request.request_type', 2)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					->orWhere('change_salary_request.request_type', 3)
					->where('change_salary_request.molstatus', 0)
					->where('change_salary_request.approvedrejectstatus', 1)
					//->where('change_salary_request.incrementstatus', 2)
					->get()->count();
				}
				
				
				
				
				
				
				
				
			

				
			
				
				//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
				//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
				//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
			}
			$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
				$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
				$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();



				$documentCollectiondetails->setPath(config('app.url/listingEmpMol'));
				
				
		return view("ChangeSalaryNew/listingEmpMol",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','paginationValue','selectedFilter'));
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
	   public function saveBankCode(Request $request)
	   {
		   $parameterInput = $request->input();
		   $documentCollectionID = $parameterInput['documentCollectionID'];
		   $bankGeneratedCode = $parameterInput['bank_generated_code'];
		   $docCollectionMod = EmpOffline::find($documentCollectionID);
		   $docCollectionMod->bank_generated_code=$bankGeneratedCode;
		   $docCollectionMod->status=7;
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
					$updateEmpAttr->save();
					/*
					*checking for emp attributeId
					*/
				}
			}
		    /*
		   *updating in main employee table
		   */
		   $request->session()->flash('message','Bank Generated Code Saved Successfully.');
		
		   return redirect('documentcollection');
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
	   public static function getDesignation2($designId = NULL)
	   {
		  
					$des=Designation::where("department_id",$designId)->first();
					if($des!=''){
						 return $des->name;
					}
					else{
						return "--";
					}
					
				
		   
	   }

	   public static function getStatus($empid = NULL,$rowid = NULL)
	   {
		$empdata = ChangeSalary::where("emp_id",$empid)->where("id",$rowid)->orderBy('id','DESC')->first();

		if(!$empdata)
		{
			return 'Request Not Initeated';
		}

		if($empdata && $empdata->approvedrejectstatus==0)
		{
			return 'Request Initeated';
		}
		if($empdata && $empdata->approvedrejectstatus==1 && $empdata->incrementstatus==0 && $empdata->molstatus==0)
		{
			return 'Request Approved';
		}
		if($empdata && $empdata->approvedrejectstatus==2 && $empdata->incrementstatus==0 && $empdata->molstatus==0)
		{
			return 'Request Rejected';
		}
		if($empdata && $empdata->incrementstatus==1 && $empdata->molstatus==0)
		{
			if($empdata->request_type==4)
			{
				return 'Decrement Done';
			}
			else
			{
				return 'Increment Done';
			}
		}
		if($empdata && $empdata->incrementstatus==2 && $empdata->molstatus==0)
		{
			if($empdata->request_type==4)
			{
				return 'Decrement Rejected';
			}
			else
			{
				return 'Increment Rejected';
			}
		}
		if($empdata && $empdata->molstatus==1 && $empdata->finalstatus==0)
		{
			return 'MOL Done';
		}
		if($empdata && $empdata->finalstatus==1)
		{
			return 'Confirmation Done';
		}








					
				
		   
	   }



	   public static function getIncrementStatus($empid = NULL, $rowid = NULL)
	   {
		$empdata = ChangeSalary::where("emp_id",$empid)->where("id",$rowid)->orderBy('id','DESC')->first();

			if(!$empdata)
			{
				return 'Request Not Initeated';
			}

			if($empdata && $empdata->incrementstatus==0)
			{
				if($empdata->request_type==4)
				{
					return 'Decrement Pending';
				}
				else
				{
					return 'Increment Pending';
				}
			}
			if($empdata && $empdata->incrementstatus==1)
			{
				if($empdata->request_type==4)
				{
					return 'Decrement Done';
				}
				else
				{
					return 'Increment Done';
				}
			}
			if($empdata && $empdata->incrementstatus==2)
			{
				if($empdata->request_type==4)
				{
					return 'Decrement Rejected';
				}
				else
				{
					return 'Increment Rejected';
				}
			}
		
	   }




 public static function getRequestType($rqid = NULL)
	   {
		$rqdata = SalaryRequest::where("id",$rqid)->orderBy('id','DESC')->first();

		if($rqdata!=''){
			return $rqdata->name;
		}else{
			return "";
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
				echo $createAT;exit;
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
	   public function uploadonboardDocumentStartAjax(Request $request)
	   {
		   $selectedFilter = $request->input();
		 /*   echo '<pre>';
		  print_r($selectedFilter);
		  exit; */ 
		   $saveData = array();
		  
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		  
		   
		   $num = $documentCollectionId;
		    unset($selectedFilter['_token']);
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
			
			/*
			*onboarding Process
			*/
			if($selectedFilter[84] == "YES")
			{
				
				$documentDetailsForOnboarding = EmpOffline::where("id",$documentCollectionId)->first();
			 	/* echo '<pre>';
				print_r($documentDetailsForOnboarding);
				exit; */
				/*
				*creating Employee In main Table
				*/
				$newEmpModel = new Employee_details();
				 /*get New Emp ID*/
				$empId =  Employee_details::orderBy("emp_id","DESC")->first();
				if($empId != '')
				{
					
					$EMPID = $empId->emp_id;
					$newEMPID = $EMPID+1;
					$newEmpModel->emp_id = $EMPID+1;
					$newEmpModel->dept_id = $documentDetailsForOnboarding->department;
					$empName = $documentDetailsForOnboarding->emp_name;
					
					$empNameArray = explode(" ",$empName);
				
					if(count($empNameArray) >1)
					{
						$newEmpModel->first_name = $empNameArray[0];
						$newEmpModel->last_name = $empNameArray[1];
					}
					else
					{
						$newEmpModel->first_name = $documentDetailsForOnboarding->emp_name;
					}
					$newEmpModel->onboarding_status = 1;
					$newEmpModel->document_collection_id = $documentCollectionId;
					$newEmpModel->interview_id =$documentDetailsForOnboarding->interview_id;
					$newEmpModel->work_location = $documentDetailsForOnboarding->location;
					$newEmpModel->status = 1;
					$newEmpModel->source_code = $documentDetailsForOnboarding->bank_generated_code;
					$newEmpModel->designation_by_doc_collection = $documentDetailsForOnboarding->designation;
					/*
					*get Designation
					*/
					$designationOnboard  = $documentDetailsForOnboarding->designation;
					if($designationOnboard  != '' && $designationOnboard != NULL)
					{
						$designationMod = Designation::where("id",$designationOnboard)->first();
						if($designationMod != '')
						{
							$newEmpModel->job_role = $designationMod->name;
						}
					}
					/*
					*get Designation
					*/
					if($newEmpModel->save())
					{
						/*
						*employee Attribute
						*/
						
						
						$deptId = $documentDetailsForOnboarding->department;	
						
						if($designationOnboard  != '' && $designationOnboard != NULL)
							{
								$designationMod = Designation::where("id",$designationOnboard)->first();
								if($designationMod != '')
								{
									
									$designationValue = '';
									if(trim($designationMod->name) == 'Relationship Officer- Cards')
									{
										$designationValue = 'RELATIONSHIP OFFICER';
									}
									elseif(trim($designationMod->name) == 'Sales Manager')
									{
										$designationValue = 'SALES MANAGER';
									}
									elseif(trim($designationMod->name) == 'Relationship Officer- Loans')
									{
										$designationValue = 'RELATIONSHIP OFFICER';
									}
									else
									{
										$designationValue = 'NA';
									}
										$employeeAttribute = new Employee_attribute();
										$employeeAttribute->emp_id = $newEMPID;
										$employeeAttribute->dept_id = $deptId;
										$employeeAttribute->attribute_code = 'DESIGN';
										$employeeAttribute->attribute_values = $designationValue;
										$employeeAttribute->status = 1;
										$employeeAttribute->save();
								}
							}
									if($documentDetailsForOnboarding->mobile_no != '')
									{
									$employeeAttribute = new Employee_attribute();
									$employeeAttribute->emp_id = $newEMPID;
									$employeeAttribute->dept_id = $deptId;
									$employeeAttribute->attribute_code = 'CONTACT_NUMBER';
									$employeeAttribute->attribute_values = $documentDetailsForOnboarding->mobile_no;
									$employeeAttribute->status = 1;
									$employeeAttribute->save();
									}
									
									if($documentDetailsForOnboarding->email != '')
									{
									$employeeAttribute = new Employee_attribute();
									$employeeAttribute->emp_id = $newEMPID;
									$employeeAttribute->dept_id = $deptId;
									$employeeAttribute->attribute_code = 'email';
									$employeeAttribute->attribute_values = $documentDetailsForOnboarding->email;
									$employeeAttribute->status = 1;
									$employeeAttribute->save();
									}
									
									if($documentDetailsForOnboarding->location != '')
									{
										
									$employeeAttribute = new Employee_attribute();
									$employeeAttribute->emp_id = $newEMPID;
									$employeeAttribute->dept_id = $deptId;
									$employeeAttribute->attribute_code = 'work_location';
									if($documentDetailsForOnboarding->location == 'AUH')
									{
										$employeeAttribute->attribute_values = 'ABU DHABI';
									}
									elseif($documentDetailsForOnboarding->location == 'DXB')
									{
									$employeeAttribute->attribute_values = 'DUBAI';
									}
									else
									{
										$employeeAttribute->attribute_values = 'NA';
									}
									$employeeAttribute->status = 1;
									$employeeAttribute->save();
									}
									
									$employeeAttribute = new Employee_attribute();
									$employeeAttribute->emp_id = $newEMPID;
									$employeeAttribute->dept_id = $deptId;
									$employeeAttribute->attribute_code = 'DOJ';
									$employeeAttribute->attribute_values = $selectedFilter[83];
									$employeeAttribute->status = 1;
									$employeeAttribute->save();
									
									$visaProcess = Visaprocess::where("document_id",$documentCollectionId)->orderBy('id','DESC')->first();
									
									if($visaProcess!=''){
										$visatypeId=$visaProcess->visa_type;
										$visadetailList = VisaDetails::where("document_collection_id",$documentCollectionId)->where("visa_type_id",$visatypeId)->get();
										if($visadetailList!=''){
											foreach($visadetailList as $_attribute){
											$attribute_id=$_attribute->attribute_code;
											$attributedetails = Attributes::where("attribute_id",$attribute_id)->first();
											$attribute_code=$attributedetails->attribute_code;
											
											$employeeAttribute = new Employee_attribute();
											$employeeAttribute->emp_id = $newEMPID;
											$employeeAttribute->dept_id = $deptId;
											$employeeAttribute->attribute_code = $attribute_code;
											$employeeAttribute->attribute_values = $_attribute->attribute_value;
											$employeeAttribute->status = 1;
											$employeeAttribute->save();
											
											}
										}
											$visaTypeData = visaType::where("id",$visatypeId)->first();
											if($visaTypeData != '')
											{
											$employeeAttribute = new Employee_attribute();
											$employeeAttribute->emp_id = $newEMPID;
											$employeeAttribute->dept_id = $deptId;
											$employeeAttribute->attribute_code = 'visa_type';
											$employeeAttribute->attribute_values = $visaTypeData->title;
											$employeeAttribute->status = 1;
											$employeeAttribute->save();
											}
									}
									$detailsObj = EmpOffline::find($documentCollectionId);
									$detailsObj->status = 8;
									$detailsObj->onboard_status=2; 
									$detailsObj->save();
									
						/*
						*employee Attribute
						*/
					}
					
					
					
				}					
				/*get New Emp ID*/
				 
				/*
				*creating Employee In main Table
				*/
			}
			/*
			*onboarding Process
			*/
			
			
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
	   public function listingEmpOfflineProcessVisapipeline(Request $request)
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
				if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
				{
					$dept = $request->session()->get('change_salary_department');
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
				if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('change_salary_teamleader');
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
				if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
				{
					$empId = $request->session()->get('change_salary_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
				{
					$fname = $request->session()->get('chnage_salary_emp_name');
					 
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
				$c_namedata = EmpOffline::where("ok_visa",2)->get();
				}
				else
				{
					
					$c_namedata = EmpOffline::whereRaw($whereraw)->where("ok_visa",2)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = EmpOffline::where("ok_visa",2)->get();
				}
				else
				{
					
					$email = EmpOffline::whereRaw($whereraw)->where("ok_visa",2)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = EmpOffline::where("ok_visa",2)->get();
				}
				else
				{
					
					$visa = EmpOffline::whereRaw($whereraw)->where("ok_visa",2)->get();
					
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
					$ventArray = EmpOffline::where("ok_visa",2)->orderBy("id", "DESC")->get();
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
					$ventArray = EmpOffline::whereRaw($whereraw)->where("ok_visa",2)->orderBy("id", "DESC")->get();
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
					$depidArray = EmpOffline::where("ok_visa",2)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = EmpOffline::whereRaw($whereraw)->where("ok_visa",2)->get();
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
				$jobArray = EmpOffline::where("ok_visa",2)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = EmpOffline::whereRaw($whereraw)->where("ok_visa",2)->get();
					
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
				$status =  EmpOffline::where("ok_visa",2)->get();
				}
				else
				{
					$status =  EmpOffline::whereRaw($whereraw)->where("ok_visa",2)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = EmpOffline::where("ok_visa",2)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = EmpOffline::whereRaw($whereraw)->where("ok_visa",2)->get();
					
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
					$documentCollectiondetails = EmpOffline::orderByRaw("-visa_expiry_date DESC")->whereRaw($whereraw)->where("ok_visa",2)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = EmpOffline::where("ok_visa",2)->orderByRaw("-visa_expiry_date DESC")->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = EmpOffline::whereRaw($whereraw)->where("ok_visa",2)->get()->count();
				}
				else
				{
					$reportsCount = EmpOffline::where("ok_visa",2)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingVisapipeline'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("EmpOfflineProcess/listingEmpOfflineProcessvisapipeline",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
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
				if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
				{
					$dept = $request->session()->get('change_salary_department');
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
				if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('change_salary_teamleader');
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
				if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
				{
					$empId = $request->session()->get('change_salary_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
				{
					$fname = $request->session()->get('chnage_salary_emp_name');
					 
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
			if($rq->input('status')==1){
			$detailsObj->condition_leaving = 3;
			}
			$detailsObj->exit_interview_status = 2;
			
			$detailsObj->retain=$rq->input('retain');
			$detailsObj->date_of_resign=$rq->input('date_of_resign');
			$detailsObj->last_working_day_resign=$rq->input('last_working_day_resign');
			$detailsObj->interview_note=$rq->input('interview_note');
			$detailsObj->hr_status = $rq->input('status');
			$detailsObj->hr_interview_date =date("Y-m-d");	
			$detailsObj->hr_interview_createdBy =$rq->session()->get('EmployeeId');
			if($detailsObj->save()){
				if($rq->input('retain')==1){
				$detailsObj1 = EmpOffline::find($rq->input('offboardingId'));
				$detailsObj1->condition_leaving = 2;
				$detailsObj1->save();
				$offlinedata=EmpOffline::where("id",$rq->input('offboardingId'))->first();
				$empdata=Employee_details::where("emp_id",$offlinedata->emp_id)->first();
				if($empdata!=''){
					$empOBJ=Employee_details::find($empdata->id);
					$empOBJ->offline_status=1;
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
			
			if($rq->input('status')==1){
			$detailsObj->condition_leaving = 3;
			}
			$detailsObj->exit_interview_date =date("Y-m-d H:i:s");	
			$detailsObj->exit_interviewBY =$rq->session()->get('EmployeeId');
			$detailsObj->save();
			
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		
			
			echo json_encode($response);
		    exit;

	}
	public function addFullandFinalSettelement($empid,$rowid)
	{
		
		
		
		
		// $offlinedata = EmpOffline::where("id",$offboardingId)->first();
		// $Settelementdata=SettelementAttribute::where("status",1)->where("attribute_type","Hr")->get();
		// $Settelementdatafinance=SettelementAttribute::where("status",1)->where("attribute_type","Finance")->get();
		// $CompanyAssets=CompanyAssets::where("status",1)->get();
		// $SettelementCheckList=SettelementCheckList::where("status",1)->get();
		// $Settelementattribute = SettelementAttributes::where('offline_id',$offboardingId)->get();
		// $attributedata = OffboardEMPData::where('emp_id',$offboardingId)->first();
		// $SettelementLogs=SettelementLogs::where('offboard_id',$offboardingId)->get();

		$salaryRequestdata = ChangeSalary::where("emp_id",$empid)->where("id",$rowid)->orderBy('id','DESC')->first();


		$empData = Employee_details::orderBy("id","DESC")->where("offline_status",1)->where('emp_id',$empid)->first();
		$requestTypes = SalaryRequest::where("status",1)->get();
		return view("ChangeSalaryNew/SettelementForm",compact('empData','salaryRequestdata'));
	}
public function SettelementfromPost(Request $rq)
	{
		
			//$formdata=$rq->input();
			$attributesValues = $rq->input();
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
			$offboarddata=new OffboardEMPData();
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
	public function addVisaCancellation(Request $request)
	{
		//$offlinedata = EmpOffline::where("id",$offboardingId)->first();
		$offboardingId = $request->offboardingId;
		$rowid = $request->rowid;

		$offlinedata = Employee_details::orderBy("id","DESC")->where("offline_status",1)->where('emp_id',$offboardingId)->first();




		return view("ChangeSalaryNew/VisaCancellationForm",compact('offboardingId','offlinedata','rowid'));
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
			  return '';
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
				
				if(!empty($request->session()->get('change_salary_fromdate')) && $request->session()->get('change_salary_fromdate') != 'All')
				{
					$datefrom = $request->session()->get('change_salary_fromdate');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at> "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_todate')) && $request->session()->get('change_salary_todate') != 'All')
				{
					$dateto = $request->session()->get('change_salary_todate');
					 if($whereraw == '')
					{
						$whereraw = 'created_a<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
				{
					$dept = $request->session()->get('change_salary_department');
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
				if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('change_salary_teamleader');
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
				if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
				{
					$empId = $request->session()->get('change_salary_emp_id');
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
				if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
				{
					$fname = $request->session()->get('chnage_salary_emp_name');
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
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'" OR  last_working_day_terminate>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'" OR last_working_day_terminate>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('paymentconfirm_dateto_offboard_lastworkingday_list')) && $request->session()->get('paymentconfirm_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('paymentconfirm_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('paymentconfirm_datefrom_offboard_dort_list')) && $request->session()->get('paymentconfirm_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('paymentconfirm_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'" OR  date_of_terminate>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'" OR date_of_terminate>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('paymentconfirm_dateto_offboard_dort_list')) && $request->session()->get('paymentconfirm_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('paymentconfirm_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
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
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
				}	
				if(!empty($request->session()->get('change_salary_designation')) && $request->session()->get('change_salary_designation') != 'All')
				{
					$designd = $request->session()->get('change_salary_designation');
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
		// $offlinedata = EmpOffline::where("id",$offboardingId)->first();
		// $attributedata = OffboardEMPData::where('emp_id',$offboardingId)->first();



		$empdata = ChangeSalary::where("emp_id",$offboardingId)->orderBy('id','DESC')->first();


		$documentCollectionDetails = Employee_details::where("offline_status",1)
	   ->leftjoin('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
	   ->select('employee_details.*', 'change_salary_request.*')
	   ->where('change_salary_request.emp_id', $offboardingId)
	   //->orWhere('change_salary_request.request_type', 3)
	   ->orderBy('employee_details.id', 'desc')->first();








		return view("ChangeSalaryNew/addpaymentconfirmationForm",compact('documentCollectionDetails','empdata'));
	}
public function offboardPaymentConfirmPost(Request $rq)
{


	//return $rq->all();
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
						if(file_exists(public_path('incrementDoc/'.$newFileName))){

							  unlink(public_path('incrementDoc/'.$newFileName));

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
						
						
						$rq->file($key)->move(public_path('incrementDoc/'), $newFileName);
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
					$empdata = ChangeSalary::where("emp_id",$rq->input('emp_id'))->orderBy('id','DESC')->first();
					//return $empdata;
					$usersessionId=$rq->session()->get('EmployeeId');


					if($rq->input('incrementstatus')==1)
					{
						$rq->request->add(['increment_request_done_by' => $usersessionId]); //add request
						$rq->request->add(['increment_request_done_at' => date('Y-m-d H:i:s')]); //add request

						$this->log->info("Increment Request: " . json_encode($rq->all()));
			
						$empdata->increment_comment =$rq->input('increment_comment');
						$empdata->incrementstatus =1;
						$empdata->incrementby =$usersessionId;
						$empdata->incrementon =date('Y-m-d H:i:s');
						

	
						if($newFileName!=''){
						$empdata->upload_doc =$newFileName;
						}					
						$empdata->request_status =3;
						//$empdata->new_salary_effective_from =$rq->input('effectivefromdate');

						$changesalaryLogs = new Change_Salary_logs();
						$changesalaryLogs->emp_id = $rq->input('emp_id');
						//$changesalaryLogs->request = $request->requesttype;
						if($empdata->request_type==4)
						{
							$changesalaryLogs->request_event =7;
						}
						else
						{
							$changesalaryLogs->request_event =4;
						}
						$changesalaryLogs->user_id =$usersessionId;
						$changesalaryLogs->event_at =date('Y-m-d');
						$changesalaryLogs->save();
					}

					if($rq->input('incrementstatus')==2)
					{
						//$rq->rq->add(['increment_request_reject_by' => $usersessionId]); //add request
						//$rq->request->add(['increment_request_reject_at' => date('Y-m-d H:i:s')]); //add request

						//$this->log->info("Increment Request: " . json_encode($rq->all()));

						$empdata->increment_comment =$rq->input('increment_comment');
						$empdata->incrementstatus =2;
						$empdata->incrementby =$usersessionId;
						$empdata->incrementon =date('Y-m-d H:i:s');
	
						if($newFileName!=''){
						$empdata->upload_doc =$newFileName;
						}					
						$empdata->request_status =3;
						//$empdata->new_salary_effective_from =$rq->input('effectivefromdate');

						$changesalaryLogs = new Change_Salary_logs();
						$changesalaryLogs->emp_id = $rq->input('emp_id');
						//$changesalaryLogs->request = $request->requesttype;
						if($empdata->request_type==4)
						{
							$changesalaryLogs->request_event =9;
						}
						else
						{
							$changesalaryLogs->request_event =5;
						}
						$changesalaryLogs->user_id =$usersessionId;
						$changesalaryLogs->event_at =date('Y-m-d');
						$changesalaryLogs->save();
					}
										
			

					$empdata->save();
			
			$response['code'] = '200';
			$response['message'] = " Saved  Successfully.";		
			
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

			$requestdata='';
			if($request->input('requestdata')!=''){
			 
			 $requestdata=implode(",", $request->input('requestdata'));
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
			
			$request->session()->put('chnage_salary_emp_name',$name);
			$request->session()->put('change_salary_emp_id',$empId);
			$request->session()->put('change_salary_fromdate',$datefrom);
			$request->session()->put('change_salary_todate',$dateto);
			$request->session()->put('change_salary_department',$department);
			$request->session()->put('change_salary_teamleader',$teamlaed);
			
			$request->session()->put('change_salary_designation',$design);
			$request->session()->put('change_salary_requestType_status',$requestdata);

			
			// $request->session()->put('dateto_offboard_lastworkingday_list',$datetolastworkingday);
			// $request->session()->put('datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			
			// $request->session()->put('ReasonofAttrition_empoffboard_filter_list',$ReasonofAttrition);
			// $request->session()->put('empoffboard_status_filter_list',$offboardstatus);
			// $request->session()->put('datefrom_offboard_dort_list',$datefromdort);
			// $request->session()->put('dateto_offboard_dort_list',$datetodort);
			// $request->session()->put('empoffboard_ffstatus_filter_list',$offboardffstatus);
			 //return  redirect('listingPageonboarding');	
		}
		public function resetfilterOffboardData(Request $request){
			$request->session()->put('change_salary_fromdate','');
			$request->session()->put('change_salary_todate','');
			$request->session()->put('change_salary_department','');
			$request->session()->put('change_salary_teamleader','');
			$request->session()->put('chnage_salary_emp_name','');
			$request->session()->put('change_salary_emp_id','');
			$request->session()->put('change_salary_designation','');
			$request->session()->put('change_salary_requestType_status','');
			// $request->session()->put('dateto_offboard_lastworkingday_list','');
			// $request->session()->put('datefrom_offboard_lastworkingday_list','');
			// $request->session()->put('ReasonofAttrition_empoffboard_filter_list','');
			// $request->session()->put('empoffboard_status_filter_list','');
			// $request->session()->put('datefrom_offboard_dort_list','');
			// $request->session()->put('dateto_offboard_dort_list','');
			// $request->session()->put('empoffboard_ffstatus_filter_list','');
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
			
			
				
				$doj = $offboarddata->doj;	
				
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
				 if($offboarddata->salary_deduction==$attr->salary_deduction_total_finance) {
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
		public function getleavingTypePopupData(Request $request)
		{
			$requestTypes = SalaryRequest::where("status",1)->get();			
			$empDataFirst = Employee_details_change_salary::select('emp_id')->orderBy('id','desc')->get();
			//return $empDataFirst;
			$completedEmpid = array();
			foreach($empDataFirst as $emp)
			{
				$requestTypes = ChangeSalary::where("emp_id",$emp->emp_id)->orderBy('id','desc')->first();
				//return $requestTypes->approvedrejectstatus;
				if($requestTypes)
				{
					
						if($requestTypes->approvedrejectstatus==2)
						{
							$completedEmpid[]=$emp->emp_id;
						}
						if($requestTypes->approvedrejectstatus==1)
						{
							
							$completedEmpid[]=$emp->emp_id;
							
						}
						// if($requestTypes->approvedrejectstatus==1)
						// {
						// 	if($requestTypes->request_type==1 && ($requestTypes->incrementstatus==1 || $requestTypes->incrementstatus==2))
						// 	{
						// 		$completedEmpid[]=$emp->emp_id;
						// 	}
						// 	if($requestTypes->request_type==4 && ($requestTypes->incrementstatus==1 || $requestTypes->incrementstatus==2))
						// 	{
						// 		$completedEmpid[]=$emp->emp_id;
						// 	}
						// 	if($requestTypes->request_type==2 && ($requestTypes->molstatus==1 || $requestTypes->molstatus==2))
						// 	{
						// 		$completedEmpid[]=$emp->emp_id;
						// 	}
						// 	if($requestTypes->request_type==3 && ($requestTypes->molstatus==1 || $requestTypes->molstatus==2) && ($requestTypes->incrementstatus==1 || $requestTypes->incrementstatus==2))
						// 	{
						// 		$completedEmpid[]=$emp->emp_id;
						// 	}
						// }

						
												
					
				}
			}
			$result=Employee_details_change_salary::whereIn('emp_id',$completedEmpid)->delete();




			
			
				

			$userid=$request->session()->get('EmployeeId');
			//$departmentDetails = JobFunctionPermission::where("user_id",$userid)->first();


			$userData = User::where("id",$userid)->orderBy('id', 'desc')->first();
			//return $userData;
			// $empDetails = Employee_details::where("emp_id",$userData->employee_id)
			// ->where("job_function",3)
			// ->orWhere('job_function', 4)
			// ->orderBy('id', 'desc')
			// // ->toSql();
			// // dd($empDetails);
			// ->get();





			$empsessionId=$request->session()->get('EmployeeId');
			$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
			if($departmentDetails != '')
			{
				$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
				if($empDetails!='')
				{
					$empData = Employee_details::where("offline_status",1)
					->join('department_details', 'employee_details.dept_id', '=', 'department_details.id')
					->select('employee_details.emp_id', 'department_details.department_name', 'employee_details.emp_name')
					->where('employee_details.dept_id',$empDetails->dept_id)
					->whereNotIn('emp_id', $empDataFirst)
					->get();
				}
			}
			else{
				$empData = Employee_details::where("offline_status",1)
				->join('department_details', 'employee_details.dept_id', '=', 'department_details.id')
				->select('employee_details.emp_id', 'department_details.department_name', 'employee_details.emp_name')
				->whereNotIn('emp_id', $empDataFirst)
				->get();
			}

			

			//return $empDetails;


			// if($empDetails)
			// {
			// 	$empData = Employee_details::where("offline_status",1)
			// 	->join('department_details', 'employee_details.dept_id', '=', 'department_details.id')
			// 	->select('employee_details.emp_id', 'department_details.department_name', 'employee_details.emp_name')
			// 	->where('employee_details.dept_id',$empDetails->dept_id)
			// 	->whereNotIn('emp_id', $empDataFirst)
			// 	->get();
			// }
			// else
			// {
			// 	$empData = Employee_details::where("offline_status",1)
			// 	->join('department_details', 'employee_details.dept_id', '=', 'department_details.id')
			// 	->select('employee_details.emp_id', 'department_details.department_name', 'employee_details.emp_name')
			// 	->whereNotIn('emp_id', $empDataFirst)
			// 	->get();
			// }





	
				
			
			return view("ChangeSalaryNew/LeavingType",compact('requestTypes','empData'));
		}
	
	
	
		public function getRequestEmpData($empid)
		{
			//$empDataFirst = Employee_details_change_salary::select('emp_id')->get()->toArray();
			$empData = Employee_details::where('emp_id',$empid)->first();
			$requestTypes = SalaryRequest::where("status",1)->get();


	
			return view("ChangeSalaryNew/empDetailsfillpop",compact('requestTypes','empData'));
	
	
	
		}
	public function exportEmpOffBoardReport(Request $request){
		$parameters = $request->input(); 
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'offboard_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet(); 
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:J1');
			$sheet->setCellValue('A1', 'EMP List - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
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
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				//echo $sid;
				 $misData = EmpOffline::where("id",$sid)->first();
				 $tldata=$misData->tl_id;
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
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($misData->emp_name))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $misData->mobile_no)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $offboarddate)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $lastworking)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $dateofresign)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $deptname)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('I'.$indexCounter, $designation)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('J'.$indexCounter, $tlname)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'J'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','J') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
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
			$request->session()->put('fnf_empoffboard_ffstatus_filter_list',$offboardffstatus);
			$request->session()->put('fnf_dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('fnf_datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			$request->session()->put('fnf_datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('fnf_dateto_offboard_dort_list',$datetodort);
			
		}
		public function resetoffboardListDataFilterfnf(Request $request){
			$request->session()->put('fnf_empoffboard_ffstatus_filter_list','');
			$request->session()->put('fnf_dateto_offboard_lastworkingday_list','');
			$request->session()->put('fnf_datefrom_offboard_lastworkingday_list','');
			$request->session()->put('fnf_datefrom_offboard_dort_list','');
			$request->session()->put('fnf_dateto_offboard_dort_list','');
		}
		
		public function offboardDataFiltercancelvisa(Request $request)
		{
			//print_r($request->input());
			
			
			$datetolastworkingday = $request->input('datetolastworkingday');
			$datefromlastworkingday = $request->input('datefromlastworkingday');
			$datetodort = $request->input('datetodort');
			$datefromdort = $request->input('datefromdort');
			
			$request->session()->put('cancelvisa_dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('cancelvisa_datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			$request->session()->put('cancelvisa_datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('cancelvisa_dateto_offboard_dort_list',$datetodort);
			
		}
		public function resetoffboardListDataFiltercancelvisa(Request $request){
			$request->session()->put('cancelvisa_empoffboard_ffstatus_filter_list','');
			$request->session()->put('cancelvisa_dateto_offboard_lastworkingday_list','');
			$request->session()->put('cancelvisa_datefrom_offboard_lastworkingday_list','');
			$request->session()->put('cancelvisa_datefrom_offboard_dort_list','');
			$request->session()->put('cancelvisa_dateto_offboard_dort_list','');
		}
		public function offboardDataFilterpaymentconfirm(Request $request)
		{
			//print_r($request->input());
			
			
			$datetolastworkingday = $request->input('datetolastworkingday');
			$datefromlastworkingday = $request->input('datefromlastworkingday');
			$datetodort = $request->input('datetodort');
			$datefromdort = $request->input('datefromdort');
			
			$request->session()->put('paymentconfirm_dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('paymentconfirm_datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			$request->session()->put('paymentconfirm_datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('paymentconfirm_dateto_offboard_dort_list',$datetodort);
			
		}
		public function resetoffboardListDataFilterpaymentconfirm(Request $request){
			$request->session()->put('paymentconfirm_empoffboard_ffstatus_filter_list','');
			$request->session()->put('paymentconfirm_dateto_offboard_lastworkingday_list','');
			$request->session()->put('paymentconfirm_datefrom_offboard_lastworkingday_list','');
			$request->session()->put('paymentconfirm_datefrom_offboard_dort_list','');
			$request->session()->put('paymentconfirm_dateto_offboard_dort_list','');
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
				
				
				if(!empty($request->session()->get('change_salary_fromdate')) && $request->session()->get('change_salary_fromdate') != 'All')
				{
					$datefrom = $request->session()->get('change_salary_fromdate');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_todate')) && $request->session()->get('change_salary_todate') != 'All')
				{
					$dateto = $request->session()->get('change_salary_todate');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
				{
					$dept = $request->session()->get('change_salary_department');
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
				if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('change_salary_teamleader');
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
				if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
				{
					$empId = $request->session()->get('change_salary_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
				{
					$fname = $request->session()->get('chnage_salary_emp_name');
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
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'" OR  last_working_day_terminate>= "'.$lastworkingday.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'" OR last_working_day_terminate>= "'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('leaving_dateto_offboard_lastworkingday_list')) && $request->session()->get('leaving_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('leaving_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('change_salary_designation')) && $request->session()->get('change_salary_designation') != 'All')
				{
					$designd = $request->session()->get('change_salary_designation');
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
						$whereraw = 'date_of_resign>= "'.$dortfrom.'" OR  date_of_terminate>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'" OR date_of_terminate>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('leaving_dateto_offboard_dort_list')) && $request->session()->get('leaving_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('leaving_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
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
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
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
						$whereraw = 'last_working_day_resign is not null OR last_working_day_terminate is not null ';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign is not null OR last_working_day_terminate is not null ';
					}
				
				
				
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->paginate($paginationValue);
					
					
				}
				else
				{
					//echo "hello1";
					$whereraw1 = 'last_working_day_resign is not null OR last_working_day_terminate is not null ';
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
					$whereraw1 = 'last_working_day_resign is not null OR last_working_day_terminate is not null ';
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
				
				if(!empty($request->session()->get('change_salary_fromdate')) && $request->session()->get('change_salary_fromdate') != 'All')
				{
					$datefrom = $request->session()->get('change_salary_fromdate');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_todate')) && $request->session()->get('change_salary_todate') != 'All')
				{
					$dateto = $request->session()->get('change_salary_todate');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
				{
					$dept = $request->session()->get('change_salary_department');
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
				if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('change_salary_teamleader');
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
				if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
				{
					$empId = $request->session()->get('change_salary_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
				{
					$fname = $request->session()->get('chnage_salary_emp_name');
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
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'" OR  last_working_day_terminate>= "'.$lastworkingday.'" ';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'" OR last_working_day_terminate>="'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_lastworkingday_list')) && $request->session()->get('exit_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('exit_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_dort_list')) && $request->session()->get('exit_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('exit_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'" OR  date_of_terminate>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'" OR date_of_terminate>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_dort_list')) && $request->session()->get('exit_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('exit_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
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
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
				}
				
				if(!empty($request->session()->get('change_salary_designation')) && $request->session()->get('change_salary_designation') != 'All')
				{
					$designd = $request->session()->get('change_salary_designation');
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
				
				
				
				
				
				
				
				if($whereraw == '')
					{
						$whereraw = 'condition_leaving = 2 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL AND exit_interview_question_status IS NULL';
					}
					else
					{
						$whereraw .= ' And condition_leaving = 2 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL AND exit_interview_question_status IS NULL';
					}
				//echo $whereraw;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->orderBy("condition_leaving_date", "DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw)->get()->count();
					//print_r($documentCollectiondetails);
				}
				else
				{
					//echo "hello1";
					$whereraw1 = 'condition_leaving = 2 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL AND exit_interview_question_status IS NULL';
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw1)->orderBy("condition_leaving_date", "DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw1)->get()->count();
					
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
				
				if(!empty($request->session()->get('change_salary_fromdate')) && $request->session()->get('change_salary_fromdate') != 'All')
				{
					$datefrom = $request->session()->get('change_salary_fromdate');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_todate')) && $request->session()->get('change_salary_todate') != 'All')
				{
					$dateto = $request->session()->get('change_salary_todate');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
				{
					$dept = $request->session()->get('change_salary_department');
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
				if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('change_salary_teamleader');
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
				if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
				{
					$empId = $request->session()->get('change_salary_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
				{
					$fname = $request->session()->get('chnage_salary_emp_name');
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
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'" OR  last_working_day_terminate>= "'.$lastworkingday.'" ';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'" OR last_working_day_terminate>="'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_lastworkingday_list')) && $request->session()->get('exit_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('exit_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_dort_list')) && $request->session()->get('exit_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('exit_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'" OR  date_of_terminate>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'" OR date_of_terminate>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_dort_list')) && $request->session()->get('exit_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('exit_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
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
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
				}
				
				if(!empty($request->session()->get('change_salary_designation')) && $request->session()->get('change_salary_designation') != 'All')
				{
					$designd = $request->session()->get('change_salary_designation');
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
				
				
				
				
				
				
				
				if($whereraw == '')
					{
						$whereraw = 'condition_leaving = 2 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL AND exit_interview_question_status=1';
					}
					else
					{
						$whereraw .= ' And condition_leaving = 2 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL AND exit_interview_question_status=1';
					}
				//echo $whereraw;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->orderBy("condition_leaving_date", "DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw)->get()->count();
					//print_r($documentCollectiondetails);
				}
				else
				{
					//echo "hello1";
					$whereraw1 = 'condition_leaving = 2 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL AND exit_interview_question_status =1';
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw1)->orderBy("condition_leaving_date", "DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw1)->get()->count();
					
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
				
				if(!empty($request->session()->get('change_salary_fromdate')) && $request->session()->get('change_salary_fromdate') != 'All')
				{
					$datefrom = $request->session()->get('change_salary_fromdate');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_todate')) && $request->session()->get('change_salary_todate') != 'All')
				{
					$dateto = $request->session()->get('change_salary_todate');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
				{
					$dept = $request->session()->get('change_salary_department');
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
				if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('change_salary_teamleader');
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
				if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
				{
					$empId = $request->session()->get('change_salary_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
				{
					$fname = $request->session()->get('chnage_salary_emp_name');
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
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'" OR  last_working_day_terminate>= "'.$lastworkingday.'" ';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'" OR last_working_day_terminate>="'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_lastworkingday_list')) && $request->session()->get('exit_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('exit_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_dort_list')) && $request->session()->get('exit_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('exit_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'" OR  date_of_terminate>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'" OR date_of_terminate>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_dort_list')) && $request->session()->get('exit_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('exit_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
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
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
				}
				
				if(!empty($request->session()->get('change_salary_designation')) && $request->session()->get('change_salary_designation') != 'All')
				{
					$designd = $request->session()->get('change_salary_designation');
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
				
				if(!empty($request->session()->get('change_salary_fromdate')) && $request->session()->get('change_salary_fromdate') != 'All')
				{
					$datefrom = $request->session()->get('change_salary_fromdate');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_todate')) && $request->session()->get('change_salary_todate') != 'All')
				{
					$dateto = $request->session()->get('change_salary_todate');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
				{
					$dept = $request->session()->get('change_salary_department');
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
				if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('change_salary_teamleader');
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
				if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
				{
					$empId = $request->session()->get('change_salary_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
				{
					$fname = $request->session()->get('chnage_salary_emp_name');
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
						$whereraw = 'last_working_day_resign>= "'.$lastworkingday.'" OR  last_working_day_terminate>= "'.$lastworkingday.'" ';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign>= "'.$lastworkingday.'" OR last_working_day_terminate>="'.$lastworkingday.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_lastworkingday_list')) && $request->session()->get('exit_dateto_offboard_lastworkingday_list') != 'All')
				{
					$dateto = $request->session()->get('exit_dateto_offboard_lastworkingday_list');
					 if($whereraw == '')
					{
						$whereraw = 'last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And last_working_day_resign<= "'.$dateto.'"  OR  last_working_day_terminate<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('exit_datefrom_offboard_dort_list')) && $request->session()->get('exit_datefrom_offboard_dort_list') != 'All')
				{
					$dortfrom = $request->session()->get('exit_datefrom_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign>= "'.$dortfrom.'" OR  date_of_terminate>= "'.$dortfrom.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign>= "'.$dortfrom.'" OR date_of_terminate>= "'.$dortfrom.'"';
					}
				}
				if(!empty($request->session()->get('exit_dateto_offboard_dort_list')) && $request->session()->get('exit_dateto_offboard_dort_list') != 'All')
				{
					$dortto = $request->session()->get('exit_dateto_offboard_dort_list');
					 if($whereraw == '')
					{
						$whereraw = 'date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
					}
					else
					{
						$whereraw .= ' And date_of_resign<= "'.$dortto.'"  OR  date_of_terminate<= "'.$dortto.'"';
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
						$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
					else
					{
						$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
					}
				}
				
				if(!empty($request->session()->get('change_salary_designation')) && $request->session()->get('change_salary_designation') != 'All')
				{
					$designd = $request->session()->get('change_salary_designation');
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
				
				
				
				
				
				
				
				
				//echo $whereraw;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->where("condition_leaving",3)->orderBy("condition_leaving_date", "DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::whereRaw($whereraw)->where("condition_leaving",3)->get()->count();
					//print_r($documentCollectiondetails);
				}
				else
				{
					//echo "hello1";
					
					$documentCollectiondetails = EmpOffline::where("condition_leaving",3)->orderBy("condition_leaving_date", "DESC")->paginate($paginationValue);
					$reportsCount = EmpOffline::where("condition_leaving",3)->get()->count();
					
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
		$detailsObj->condition_leaving = 2;		
		if($detailsObj->save()){

				$offlinedata=EmpOffline::where("id",$id)->first();
				$empdata=Employee_details::where("emp_id",$offlinedata->emp_id)->first();
				if($empdata!=''){
					$empOBJ=Employee_details::find($empdata->id);
					$empOBJ->offline_status=1;
					$empOBJ->save();
				}
		}
		echo "Done";

	}	   


	public function changeSalaryRequestPost(Request $request)
	{
		//return $request->all();
		

		$changesalaryRequest = ChangeSalary::where("emp_id",$request->empid)->orderBy('id','DESC')->first();

		// if($changesalaryRequest)
		// {
		// 	//return "Already Request Placed.";

		// 	return '<div class="alert alert-danger" role="alert">Already Submitted Request.</div>';
		// }
		// else
		// {
			$empsessionId=$request->session()->get('EmployeeId');
			$request->request->add(['request_add_by' => $empsessionId]); //add request
			$this->log->info("Request to add employee into Change salary Process: " . json_encode($request->all()));


			
			$userData = User::where("id",$empsessionId)->orderBy('id','DESC')->first();
			$usersids = array(101456,101042,100762,102723);

			//return $userData->employee_id;s

			$changesalaryRequest = new ChangeSalary();
			$changesalaryRequest->emp_id = $request->empid;
			$changesalaryRequest->oldsalary = $request->oldsalary;
			$changesalaryRequest->newsalary = $request->newsalary;
			$changesalaryRequest->request_type =$request->requesttype;
			$changesalaryRequest->dept_id =$request->deptid;
			$changesalaryRequest->tl_id =$request->tlid;
			$changesalaryRequest->status =1;
			$changesalaryRequest->request_status =1;
			$changesalaryRequest->createdby =$empsessionId;
			$changesalaryRequest->created_at =date('Y-m-d H:i:s');
			$changesalaryRequest->new_salary_effective_from =$request->effectivedate;
			

			$changesalaryLogs = new Change_Salary_logs();
			$changesalaryLogs->emp_id = $request->empid;
			$changesalaryLogs->request = $request->requesttype;
			$changesalaryLogs->request_event =1;
			$changesalaryLogs->user_id =$empsessionId;
			$changesalaryLogs->event_at =date('Y-m-d');
			$changesalaryLogs->save();

			if (in_array($userData->employee_id, $usersids))
			{
				$changesalaryRequest->approvedrejectstatus =1;
				$changesalaryRequest->request_status =2;
				$changesalaryRequest->approvedrejectby =$empsessionId;
				$changesalaryRequest->approvedrejecton =date('Y-m-d H:i:s');


				$request->request->add(['request_auto_approved_by' => $empsessionId]); //add request
				$request->request->add(['request_auto_approved_at' => date('Y-m-d H:i:s')]); //add request


				$this->log->info("Request auto approved: " . json_encode($request->all()));

				$changesalaryLogs = new Change_Salary_logs();
				$changesalaryLogs->emp_id = $request->empid;
				$changesalaryLogs->request = $request->requesttype;
				$changesalaryLogs->request_event =2;
				$changesalaryLogs->user_id =$empsessionId;
				$changesalaryLogs->event_at =date('Y-m-d');
				$changesalaryLogs->save();

			}

			$changesalaryRequest->save();

			// New Code start
			$empData = Employee_details::where('emp_id',$request->empid)->first();
			$empChangeSalaryData = Employee_details_change_salary::where('emp_id',$request->empid)->first();

			if(!$empChangeSalaryData)
			{
				$changesalaryRequest = new Employee_details_change_salary();
				$changesalaryRequest->emp_id = $empData->emp_id;
				$changesalaryRequest->company_id = $empData->company_id;
				$changesalaryRequest->dept_id =$empData->dept_id;
				$changesalaryRequest->onboarding_status =$empData->onboarding_status;
				
				$changesalaryRequest->first_name =$empData->first_name;
				$changesalaryRequest->middle_name =$empData->middle_name;
				$changesalaryRequest->last_name =$empData->last_name;
				$changesalaryRequest->document_collection_id =$empData->document_collection_id;
				$changesalaryRequest->interview_id =$empData->interview_id;
				$changesalaryRequest->status =$empData->status;

				$changesalaryRequest->source_code =$empData->source_codes;
				$changesalaryRequest->basic_salary =$empData->basic_salary;
				$changesalaryRequest->others_mol =$empData->others_mol;
				$changesalaryRequest->gross_mol =$empData->gross_mol;
				$changesalaryRequest->actual_salary =$empData->actual_salary;
				$changesalaryRequest->job_role =$empData->job_role;
				$changesalaryRequest->function_name =$empData->function_name;
				
				$changesalaryRequest->employee_status =$empData->employee_status;
				$changesalaryRequest->location =$empData->location;
				$changesalaryRequest->tl_id =$empData->tl_id;
				$changesalaryRequest->tl_name =$empData->tl_name;
				$changesalaryRequest->country =$empData->country;
				$changesalaryRequest->work_location =$empData->work_location;
				$changesalaryRequest->vintage_days =$empData->vintage_days;
				$changesalaryRequest->vintage_updated_date =$empData->vintage_updated_date;
				$changesalaryRequest->designation_by_doc_collection =$empData->designation_by_doc_collection;
				$changesalaryRequest->emp_check_payout_status =$empData->emp_check_payout_status;				
				
				$changesalaryRequest->offline_status =$empData->offline_status;
				$changesalaryRequest->emp_name =$empData->emp_name;
				$changesalaryRequest->job_function =$empData->job_function;
				$changesalaryRequest->job_function_name =$empData->job_function_name;

				$changesalaryRequest->save();
			}
			// New Code End

			$response['code'] = '200';
			$response['message'] = "Data Saved Successfully.";
			echo json_encode($response);
		//}
	}






	
	public function updateSalaryChangeRequest(Request $request)
	{
		//return $request->all();
		$changesalaryRequest = ChangeSalary::where("emp_id",$request->empid)->where("id",$request->rowid)->orderBy('id','DESC')->first();
		$userid=$request->session()->get('EmployeeId');
		$request->request->add(['approved_by' => $userid]); //add request

		$this->log->info("Request for change salary approved: " . json_encode($request->all()));



		if(!$changesalaryRequest)
		{
			return '<div class="alert alert-danger" role="alert">No data found.</div>';
		}
		else
		{
		   // $changesalaryRequest = new ChangeSalary();
			$changesalaryRequest->emp_id = $request->empid;
			$changesalaryRequest->newsalary = $request->newsalary;
			$changesalaryRequest->request_type =$request->requesttype;
			$changesalaryRequest->dept_id =$request->deptid;
			$changesalaryRequest->tl_id =$request->tlid;
			$changesalaryRequest->status =1;
			$changesalaryRequest->request_status =2;
			$changesalaryRequest->approvedrejectstatus =1;
			$changesalaryRequest->approvedrejectby =$userid;
			$changesalaryRequest->approvedrejecton =date('Y-m-d H:i:s');

			$changesalaryRequest->comments =$request->comment;
			$changesalaryRequest->behalfoff_user =$request->behalf_user;
			$changesalaryRequest->new_salary_effective_from =$request->effectivefromdate;
			$changesalaryRequest->save();


			$changesalaryLogs = new Change_Salary_logs();
			$changesalaryLogs->emp_id = $request->empid;
			$changesalaryLogs->request = $request->requesttype;
			$changesalaryLogs->request_event =2;
			$changesalaryLogs->user_id =$userid;
			$changesalaryLogs->event_at =date('Y-m-d');
			$changesalaryLogs->save();

			$response['code'] = '200';
			$response['message'] = "Data Updated Successfully.";
			echo json_encode($response);
		}	   
	}


	public function updateSalaryChangeRequestReject(Request $request)
	{
		//return $request->all();
		$changesalaryRequest = ChangeSalary::where("emp_id",$request->empid)->where("id",$request->rowid)->orderBy('id','DESC')->first();
		$userid=$request->session()->get('EmployeeId');

		$request->request->add(['reject_by' => $userid]); //add request

		$this->log->info("Request for change salary reject: " . json_encode($request->all()));
		if(!$changesalaryRequest)
		{
			return '<div class="alert alert-danger" role="alert">No data found.</div>';
		}
		else
		{
		   // $changesalaryRequest = new ChangeSalary();
			$changesalaryRequest->emp_id = $request->empid;
			$changesalaryRequest->newsalary = $request->newsalary;
			$changesalaryRequest->request_type =$request->requesttype;
			$changesalaryRequest->dept_id =$request->deptid;
			$changesalaryRequest->tl_id =$request->tlid;
			$changesalaryRequest->status =1;
			$changesalaryRequest->request_status =1;
			$changesalaryRequest->comments =$request->comment;
			$changesalaryRequest->approvedrejectstatus =2;
			$changesalaryRequest->approvedrejectby =$userid;
			$changesalaryRequest->approvedrejecton =date('Y-m-d H:i:s');
			$changesalaryRequest->behalfoff_user =$request->behalf_user;
			$changesalaryRequest->save();   

			$changesalaryLogs = new Change_Salary_logs();
			$changesalaryLogs->emp_id = $request->empid;
			$changesalaryLogs->request = $request->requesttype;
			$changesalaryLogs->request_event =3;
			$changesalaryLogs->user_id =$userid;
			$changesalaryLogs->event_at =date('Y-m-d');
			$changesalaryLogs->save();


			$response['code'] = '200';
			$response['message'] = "Data Updated Successfully.";
			echo json_encode($response);
		}	   
	}





	public function listingConfirmtabData(Request $request)
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
		
		if(!empty($request->session()->get('changesalary_page_limit')))
			{
				$paginationValue = $request->session()->get('changesalary_page_limit');
			}
			else
			{
				$paginationValue = 100;
			}
			
			
			
			
			//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
			
			if(!empty($request->session()->get('change_salary_fromdate')) && $request->session()->get('change_salary_fromdate') != 'All')
			{
				$datefrom = $request->session()->get('change_salary_fromdate');
				 if($whereraw == '')
				{
					$whereraw = 'change_salary_request.created_at>= "'.$datefrom.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And change_salary_request.created_at>= "'.$datefrom.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('change_salary_todate')) && $request->session()->get('change_salary_todate') != 'All')
			{
				$dateto = $request->session()->get('change_salary_todate');
				 if($whereraw == '')
				{
					$whereraw = 'change_salary_request.created_at<= "'.$dateto.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And change_salary_request.created_at<= "'.$dateto.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('change_salary_department')) && $request->session()->get('change_salary_department') != 'All')
			{
				$dept = $request->session()->get('change_salary_department');
				 //$departmentArray = explode(",",$dept);
				if($whereraw == '')
				{
					$whereraw = 'change_salary_request.dept_id IN('.$dept.')';
				}
				else
				{
					$whereraw .= ' And change_salary_request.dept_id IN('.$dept.')';
				}
			}
			if(!empty($request->session()->get('change_salary_teamleader')) && $request->session()->get('change_salary_teamleader') != 'All')
			{
				$teamlead = $request->session()->get('change_salary_teamleader');
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
			if(!empty($request->session()->get('change_salary_emp_id')) && $request->session()->get('change_salary_emp_id') != 'All')
			{
				$empId = $request->session()->get('change_salary_emp_id');
				 if($whereraw == '')
				{
					$whereraw = 'change_salary_request.emp_id IN ('.$empId.')';
				}
				else
				{
					$whereraw .= ' And change_salary_request.emp_id IN ('.$empId.')';
				}
			}
			if(!empty($request->session()->get('chnage_salary_emp_name')) && $request->session()->get('chnage_salary_emp_name') != 'All')
			{
				$fname = $request->session()->get('chnage_salary_emp_name');
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
					$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
				}
			}
			if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
			{
				$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
				 if($whereraw == '')
				{
					$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
				}
			}
			if(!empty($request->session()->get('change_salary_designation')) && $request->session()->get('change_salary_designation') != 'All')
			{
				$designd = $request->session()->get('change_salary_designation');
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
			if(!empty($request->session()->get('datefrom_offboard_dort_list')) && $request->session()->get('datefrom_offboard_dort_list') != 'All')
			{
				$dortfrom = $request->session()->get('datefrom_offboard_dort_list');
				 if($whereraw == '')
				{
					$whereraw = 'date_of_resign< "'.$dortfrom.'" OR  date_of_terminate< "'.$dortfrom.'"';
				}
				else
				{
					$whereraw .= ' And date_of_resign< "'.$dortfrom.'" OR date_of_terminate< "'.$dortfrom.'"';
				}
			}
			if(!empty($request->session()->get('dateto_offboard_dort_list')) && $request->session()->get('dateto_offboard_dort_list') != 'All')
			{
				$dortto = $request->session()->get('dateto_offboard_dort_list');
				 if($whereraw == '')
				{
					$whereraw = 'date_of_resign> "'.$dortto.'"  OR  date_of_terminate> "'.$dortto.'"';
				}
				else
				{
					$whereraw .= ' And date_of_resign> "'.$dortto.'"  OR  date_of_terminate> "'.$dortto.'"';
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
					$whereraw = 'reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
				}
				else
				{
					$whereraw .= ' And reasons_for_leaving_resign IN('.$finalresign.') OR reasons_for_leaving_terminate IN('.$finalresign.')';
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
			}



			








			if($whereraw != '')
			{
				
				
				
				$empsessionId=$request->session()->get('EmployeeId');
			$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
			if($departmentDetails != '')
			{
				$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
				if($empDetails!='')
				{
					// $documentCollectiondetails = Employee_details::join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					// ->select('employee_details.*', 'change_salary_request.*')
					// ->where('employee_details.dept_id',$empDetails->dept_id)
					// ->whereRaw($whereraw)
					// ->where('change_salary_request.request_type', 1)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->orWhere('change_salary_request.request_type', 4)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->orWhere('change_salary_request.request_type', 2)
					// ->whereRaw($whereraw)
					// ->where('change_salary_request.molstatus', 1)
					// ->orWhere('change_salary_request.request_type', 3)
					// ->whereRaw($whereraw)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->where('change_salary_request.molstatus', 1)
					
	
					// ->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
	
					// $reportsCount = Employee_details::join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					// ->select('employee_details.*', 'change_salary_request.*')
					// ->where('employee_details.dept_id',$empDetails->dept_id)
					// ->whereRaw($whereraw)
					// ->where('change_salary_request.request_type', 1)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->orWhere('change_salary_request.request_type', 4)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->orWhere('change_salary_request.request_type', 2)
					// ->whereRaw($whereraw)
					// ->where('change_salary_request.molstatus', 1)
					// ->orWhere('change_salary_request.request_type', 3)
					// ->whereRaw($whereraw)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->where('change_salary_request.molstatus', 1)
	
					// ->orderBy('change_salary_request.id', 'desc')->get()->count();


					$documentCollectiondetails = Employee_details::join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')
					->where('employee_details.dept_id',$empDetails->dept_id)
					->whereRaw($whereraw)
					->where('change_salary_request.approvedrejectstatus', 1)	
					->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
	
					$reportsCount = Employee_details::join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')
					->where('employee_details.dept_id',$empDetails->dept_id)
					->whereRaw($whereraw)
					->where('change_salary_request.approvedrejectstatus', 1)	
					->orderBy('change_salary_request.id', 'desc')->get()->count();




				}
			}
			else{
				// $documentCollectiondetails = Employee_details::join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
				// 	->select('employee_details.*', 'change_salary_request.*')
				// 	->whereRaw($whereraw)
				// 	->where('change_salary_request.request_type', 1)
				// 	->where('change_salary_request.incrementstatus', 1)
				// 	->orWhere('change_salary_request.request_type', 4)
				// 	->where('change_salary_request.incrementstatus', 1)
				// 	->orWhere('change_salary_request.request_type', 2)
				// 	->whereRaw($whereraw)
				// 	->where('change_salary_request.molstatus', 1)
				// 	->orWhere('change_salary_request.request_type', 3)
				// 	->whereRaw($whereraw)
				// 	->where('change_salary_request.incrementstatus', 1)
				// 	->where('change_salary_request.molstatus', 1)
					
	
				// 	->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);


				$documentCollectiondetails = Employee_details::join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')
					->whereRaw($whereraw)
					->where('change_salary_request.approvedrejectstatus', 1)	
					->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
	
					// $reportsCount = Employee_details::join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					// ->select('employee_details.*', 'change_salary_request.*')
					// ->whereRaw($whereraw)
					// ->where('change_salary_request.request_type', 1)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->orWhere('change_salary_request.request_type', 4)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->orWhere('change_salary_request.request_type', 2)
					// ->whereRaw($whereraw)
					// ->where('change_salary_request.molstatus', 1)
					// ->orWhere('change_salary_request.request_type', 3)
					// ->whereRaw($whereraw)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->where('change_salary_request.molstatus', 1)
	
					// ->orderBy('change_salary_request.id', 'desc')->get()->count();


					$reportsCount = Employee_details::join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')
					->whereRaw($whereraw)
					->where('change_salary_request.approvedrejectstatus', 1)	
					->orderBy('change_salary_request.id', 'desc')->get()->count();

					
			}
				
				
				
				
				
				
				
				
				
				
				
				
				


				
			}
			else
			{
				
				
				
				
				$empsessionId=$request->session()->get('EmployeeId');
			$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
			if($departmentDetails != '')
			{
				$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
				if($empDetails!='')
				{
					// $documentCollectiondetails = Employee_details::where("offline_status",1)
					// ->join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					// ->select('employee_details.*', 'change_salary_request.*')
					// ->where('employee_details.dept_id',$empDetails->dept_id)
					// ->where('change_salary_request.request_type', 1)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->orWhere('change_salary_request.request_type', 4)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->orWhere('change_salary_request.request_type', 2)
					// ->where('change_salary_request.molstatus', 1)
					// ->orWhere('change_salary_request.request_type', 3)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->where('change_salary_request.molstatus', 1)
					// ->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);

					$documentCollectiondetails = Employee_details::where("offline_status",1)
					->join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')
					->where('employee_details.dept_id',$empDetails->dept_id)
					->where('change_salary_request.approvedrejectstatus', 1)
					->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
	
	
					// $reportsCount = Employee_details::where("offline_status",1)
					// ->join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					// ->select('employee_details.*', 'change_salary_request.*')
					// ->where('employee_details.dept_id',$empDetails->dept_id)
					// ->where('change_salary_request.request_type', 1)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->orWhere('change_salary_request.request_type', 4)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->orWhere('change_salary_request.request_type', 2)
					// ->where('change_salary_request.molstatus', 1)
					// ->orWhere('change_salary_request.request_type', 3)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->where('change_salary_request.molstatus', 1)
					// ->orderBy('change_salary_request.id', 'desc')->get()->count();

					$reportsCount = Employee_details::where("offline_status",1)
					->join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')
					->where('employee_details.dept_id',$empDetails->dept_id)
					->where('change_salary_request.approvedrejectstatus', 1)
					->orderBy('change_salary_request.id', 'desc')->get()->count();
				}
			}
			else{
				// $documentCollectiondetails = Employee_details::where("offline_status",1)
				// 	->join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
				// 	->select('employee_details.*', 'change_salary_request.*','employee_details.id as rowid')
				// 	->where('change_salary_request.request_type', 1)
				// 	->where('change_salary_request.incrementstatus', 1)
				// 	->orWhere('change_salary_request.request_type', 4)
				// 	->where('change_salary_request.incrementstatus', 1)
				// 	->orWhere('change_salary_request.request_type', 2)
				// 	->where('change_salary_request.molstatus', 1)
				// 	->orWhere('change_salary_request.request_type', 3)
				// 	->where('change_salary_request.incrementstatus', 1)
				// 	->where('change_salary_request.molstatus', 1)
				// 	->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);
	
	
					// $reportsCount = Employee_details::where("offline_status",1)
					// ->join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					// ->select('employee_details.*', 'change_salary_request.*')
					// ->where('change_salary_request.request_type', 1)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->orWhere('change_salary_request.request_type', 4)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->orWhere('change_salary_request.request_type', 2)
					// ->where('change_salary_request.molstatus', 1)
					// ->orWhere('change_salary_request.request_type', 3)
					// ->where('change_salary_request.incrementstatus', 1)
					// ->where('change_salary_request.molstatus', 1)
					// ->orderBy('change_salary_request.id', 'desc')->get()->count();


					$documentCollectiondetails = Employee_details::
					join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*','employee_details.id as rowid')
					->where('change_salary_request.approvedrejectstatus', 1)
					->orderBy('change_salary_request.id', 'desc')->paginate($paginationValue);

					$reportsCount = Employee_details::
					join('change_salary_request', 'employee_details.emp_id', '=', 'change_salary_request.emp_id')
					->select('employee_details.*', 'change_salary_request.*')
					->where('change_salary_request.approvedrejectstatus', 1)
					->orderBy('change_salary_request.id', 'desc')->get()->count();
			}
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
			

				
			}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
				$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
				$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
			


		
		
		
		$documentCollectiondetails->setPath(config('app.url/listingConfirmtab'));
				
				
		return view("ChangeSalaryNew/listingConfirmtab",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','paginationValue'));
	   }


















	   public function finalRequestApproved(Request $request)
	   {
	   
	   
		   //return $rq->all();
		  
						   $empdata = ChangeSalary::where("emp_id",$request->empid)->orderBy('id','DESC')->first();
						   //return $empdata;
						   $usersessionId=$request->session()->get('EmployeeId');

				   
						   $empdata->finalcomment =$request->comment;
						   //$empdata->incrementstatus =1;					  
						   $empdata->request_status =5;
						   $empdata->finalstatus =1;

						   $empdata->finalby =$usersessionId;
						   $empdata->finalon =date('Y-m-d H:i:s');

						   $empdata->save();
				   
				   $response['code'] = '200';
				   $response['message'] = " Saved  Successfully.";		
				   
				   echo json_encode($response);
				   exit;
	   }


	   public function finalRequestReject(Request $request)
	   {
	   
	   
		   //return $rq->all();
		  
						   $empdata = ChangeSalary::where("emp_id",$request->empid)->orderBy('id','DESC')->first();
						   //return $empdata;
											   
						   $usersessionId=$request->session()->get('EmployeeId');

						   $empdata->finalcomment =$request->comment;
						   //$empdata->incrementstatus =1;					  
						   $empdata->request_status =5;
						   $empdata->finalstatus =2;

						   $empdata->finalby =$usersessionId;
						   $empdata->finalon =date('Y-m-d H:i:s');

						   $empdata->save();
				   
				   $response['code'] = '200';
				   $response['message'] = " Saved  Successfully.";		
				   
				   echo json_encode($response);
				   exit;
	   }


	   public function getallEmployeeData()
	   {
			//$empData = Employee_details::select('emp_id', 'dept_id', 'emp_name')->get();

			

			$empDataFirst = Employee_details_change_salary::select('emp_id')->get()->toArray();
			//return $empDataFirst;


			$empData = Employee_details::where("offline_status",1)
			->join('department_details', 'employee_details.dept_id', '=', 'department_details.id')
			->select('employee_details.emp_id', 'department_details.department_name', 'employee_details.emp_name')
			->whereNotIn('emp_id', $empDataFirst)
			->get();

			

			return view("ChangeSalaryNew/empDetailsPop",compact('empData'));
	   }

	   public function saveEmployeeData(Request $request)
	   {
			//return $request->empid;

			$empData = Employee_details::where('emp_id',$request->empid)->first();
			//return $empData->emp_id;

			$changesalaryRequest = new Employee_details_change_salary();
			$changesalaryRequest->emp_id = $empData->emp_id;
			$changesalaryRequest->company_id = $empData->company_id;
			$changesalaryRequest->dept_id =$empData->dept_id;
			$changesalaryRequest->onboarding_status =$empData->onboarding_status;

			
			$changesalaryRequest->first_name =$empData->first_name;
			$changesalaryRequest->middle_name =$empData->middle_name;
			$changesalaryRequest->last_name =$empData->last_name;
			$changesalaryRequest->document_collection_id =$empData->document_collection_id;
			$changesalaryRequest->interview_id =$empData->interview_id;
			$changesalaryRequest->status =$empData->status;

			$changesalaryRequest->source_code =$empData->source_codes;

			$changesalaryRequest->basic_salary =$empData->basic_salary;
			$changesalaryRequest->others_mol =$empData->others_mol;
			$changesalaryRequest->gross_mol =$empData->gross_mol;
			$changesalaryRequest->actual_salary =$empData->actual_salary;
			$changesalaryRequest->job_role =$empData->job_role;
			$changesalaryRequest->function_name =$empData->function_name;

			
			$changesalaryRequest->employee_status =$empData->employee_status;
			$changesalaryRequest->location =$empData->location;
			$changesalaryRequest->tl_id =$empData->tl_id;
			$changesalaryRequest->tl_name =$empData->tl_name;
			$changesalaryRequest->country =$empData->country;
			$changesalaryRequest->work_location =$empData->work_location;
			$changesalaryRequest->vintage_days =$empData->vintage_days;
			$changesalaryRequest->vintage_updated_date =$empData->vintage_updated_date;
			$changesalaryRequest->designation_by_doc_collection =$empData->designation_by_doc_collection;
			$changesalaryRequest->emp_check_payout_status =$empData->emp_check_payout_status;

			
			
			
			$changesalaryRequest->offline_status =$empData->offline_status;
			$changesalaryRequest->emp_name =$empData->emp_name;
			$changesalaryRequest->job_function =$empData->job_function;
			$changesalaryRequest->job_function_name =$empData->job_function_name;



			$changesalaryRequest->save();   
			$response['code'] = '200';
			$response['message'] = " Saved  Successfully.";		
			
			echo json_encode($response);



	   }

	   public static function checkRequested($emp_id)
	   {
			$empdata = ChangeSalary::where("emp_id",$emp_id)->orderBy('id','DESC')->first();
			if($empdata)
			{
				return 1;
			}
			
	   }

	   public function getEmployeeDetailsData($empid)
	   {
			$empData = Employee_details::where("offline_status",1)->where('emp_id',$empid)->first();
			$requestTypes = SalaryRequest::where("status",1)->get();

			//return $empData;
			return view("ChangeSalaryNew/empDetailsfillpop",compact('empData','requestTypes'));

	   }


	   public function downloadFile(Request $request)
	   {
			   $file =  $request->filename;

			   $extension = pathinfo($file, PATHINFO_EXTENSION);			   

			   
			   $fileName = public_path("/incrementDoc");
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
	   
			   //return $newf;

			 
			   return response()->download($newf, $newName, $headers);
	   }




	   

	public function setPageLimitProcess(Request $request)
	{
		$offset = $request->offset;
		$request->session()->put('changesalary_page_limit',$offset);
	}

	public static function getEmpSalaryRequestStatus($empid)
	{
		$empdata = ChangeSalary::where("emp_id",$empid)->orderBy('id','DESC')->first();
		return $empdata;
	}


	public function applySalaryChange(Request $request)
	{
		
		
		$usersessionId=$request->session()->get('EmployeeId');
		$noChange=0;

		$todaydate = date('Y-m');
		
		

		//$nempid = array(100969,102036);

		$changesalaryRequest = ChangeSalary::whereNull('cronUpdateStatus')->where('approvedrejectstatus',1)->orderBy('id','desc')->get();

		//return $changesalaryRequest;

		if (count($changesalaryRequest) === 0) 
		{
			return response()->json(['success'=>'No Records found for Updation.']);
		}
		else
		{
			
			
			foreach($changesalaryRequest as $salaryRequest)
			{
				if($salaryRequest->new_salary_effective_from==$todaydate)
				{
					//Updation in salary process

					
						$empDetailsAttribute = Employee_attribute::where("emp_id",$salaryRequest->emp_id)->where("attribute_code","actual_salary")->first();
						//return $empDetailsAttribute;

						if($empDetailsAttribute)
						{
							if($empDetailsAttribute->attribute_values!=$salaryRequest->newsalary)
							{
								$empDetailsAttribute->attribute_values = $salaryRequest->newsalary;
								$empDetailsAttribute->save();
							}
						}
		
						$empDetails = Employee_details::where("emp_id",$salaryRequest->emp_id)->first();
						if($empDetails)
						{
							if($empDetails->actual_salary!=$salaryRequest->newsalary)
							{
								$empDetails->actual_salary = $salaryRequest->newsalary;
								$empDetails->save();
							}
						}

						$usersessionId=$request->session()->get('EmployeeId');
						$changesalaryRequestUpdated = ChangeSalary::where('emp_id',$salaryRequest->emp_id)->orderBy('id','DESC')->first();
						$changesalaryRequestUpdated->cronUpdateStatus = 1;
						$changesalaryRequestUpdated->save();

						$changesalaryLogs = new Change_Salary_logs();
						$changesalaryLogs->emp_id = $salaryRequest->emp_id;
						$changesalaryLogs->request_event =8;
						$changesalaryLogs->user_id =$usersessionId;
						$changesalaryLogs->event_at =date('Y-m-d');
						$changesalaryLogs->save();
					
				}
				else
				{
					// No Updation in salary
					$noChange=1;
				}
			}

			if($noChange==1)
			{
				return response()->json(['success'=>'No Records found to Update.']);
			}
			else
			{
				return response()->json(['success'=>'New Salary Updated Successfully.']);
			}
			

		}
		


		
		

		

	}


	public function changeStatusActionProcess(Request $request)
	{
		$rowid = $request->rowid;
		$actionid = $request->action;
		$userid=$request->session()->get('EmployeeId');

		if($userid!='')
		{
			$failedmsg='';
			$changesalaryRequestinfo = ChangeSalary::where("id",$rowid)->orderBy('id','DESC')->first();

			if($changesalaryRequestinfo->approvedrejectstatus==1)
			{
				$successmsg="This Change Salary Request Already Approved.";
				return view("ChangeSalaryNew/email_process",compact('successmsg','failedmsg'));
			}
			elseif($changesalaryRequestinfo->approvedrejectstatus==2)
			{
				$successmsg="This Change Salary Request Already Rejected.";
				return view("ChangeSalaryNew/email_process",compact('successmsg','failedmsg'));
			}
			else
			{
				if($rowid !='' && $actionid !='')
				{
					if($actionid==1)
					{
						$changesalaryRequest = ChangeSalary::where("id",$rowid)->orderBy('id','DESC')->first();
						
						$changesalaryRequest->status =1;
						$changesalaryRequest->request_status =2;
						$changesalaryRequest->approvedrejectstatus =1;
						$changesalaryRequest->approvedrejectby =$userid;
						$changesalaryRequest->approvedrejecton =date('Y-m-d H:i:s');

						//$changesalaryRequest->comments =$request->comment;
						//$changesalaryRequest->behalfoff_user =$request->behalf_user;
						$changesalaryRequest->save();


						$changesalaryLogs = new Change_Salary_logs();
						$changesalaryLogs->emp_id = $changesalaryRequest->emp_id;
						$changesalaryLogs->request = $changesalaryRequest->request_type;
						$changesalaryLogs->request_event =2;
						$changesalaryLogs->user_id =$userid;
						$changesalaryLogs->event_at =date('Y-m-d');
						$changesalaryLogs->save();

						$successmsg="Salary Change Request Approved Successfully.";
						return view("ChangeSalaryNew/email_process",compact('successmsg','failedmsg'));

					}

					if($actionid==2)
					{
						$changesalaryRequest = ChangeSalary::where("id",$rowid)->orderBy('id','DESC')->first();
						
						$changesalaryRequest->status =1;
						$changesalaryRequest->request_status =1;
						$changesalaryRequest->approvedrejectstatus =2;
						$changesalaryRequest->approvedrejectby =$userid;
						$changesalaryRequest->approvedrejecton =date('Y-m-d H:i:s');
						$changesalaryRequest->save();   

						$changesalaryLogs = new Change_Salary_logs();
						$changesalaryLogs->emp_id = $request->empid;
						$changesalaryLogs->request = $request->requesttype;
						$changesalaryLogs->request_event =3;
						$changesalaryLogs->user_id =$userid;
						$changesalaryLogs->event_at =date('Y-m-d');
						$changesalaryLogs->save();

						$successmsg="Salary Change Request Rejected.";
						return view("ChangeSalaryNew/email_process",compact('successmsg','failedmsg'));
					}
				}
				else
				{
					return response()->json(['success'=>'Id not Found.']);

				}
			}
		}
		else
		{
			$successmsg='';
			$failedmsg="To Approved/Disapproved request, Please firstly login in Portal and then again click on Approved/Disapproved through Email.";
			return view("ChangeSalaryNew/email_process",compact('failedmsg','successmsg'));
		}


		




		

		
		
		
	}







	public function exportChangeSalaryReport(Request $request)
	{
        //return $request->all();
        $parameters = $request->input(); 
        $selectedId = $parameters['selectedIds'];
        
            
        $filename = 'change_salary_report_'.date("d-m-Y").'.xlsx';
        $spreadsheet = new Spreadsheet(); 
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'Change Salary List - '.date("d-m-Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
        $indexCounter = 2;
        $sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('D'.$indexCounter, strtoupper('Team Leader'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('E'.$indexCounter, strtoupper('Designation'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('F'.$indexCounter, strtoupper('Department'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('G'.$indexCounter, strtoupper('Old Salary'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('H'.$indexCounter, strtoupper('New Salary'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('I'.$indexCounter, strtoupper('Location'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('J'.$indexCounter, strtoupper('Request Type'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('K'.$indexCounter, strtoupper('Status'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('L'.$indexCounter, strtoupper('Salary Effective from'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            

        $sn = 1;
        foreach ($selectedId as $sid) 
        {
            //echo $sid;
            $misData = ChangeSalary::where("id",$sid)->first();

            //$empName = $this->getEmployeeName($misData->emp_id);
            $teamLeader = $this->getTL($misData->emp_id);
            $designation = $this->getDesignationSalary($misData->emp_id);
            $dept = $this->getDepartment($misData->emp_id);
            $empname = $this->getEmpNameSalary($misData->emp_id);
            $workLocation = $this->getAttributeListValue($misData->emp_id,'work_location');
            $request1 = $this->getRequestType($misData->request_type);
            $requeststatus = $this->getStatus($misData->emp_id,$misData->id);
            // $sumApproved = $this->getSumApprovedAmt($misData->emp_id);
            // $sumRecovered = $this->getSumRecoveredAmt($misData->emp_id);
            // $balancedAmt = $this->getBalancedAmt($misData->emp_id);

			if($misData->new_salary_effective_from!='')
			{
				$effectiveDate = date("F, Y", strtotime($misData->new_salary_effective_from));
			}
			else
			{
				$effectiveDate = '--';
			}
			
			
			
            
            

            



            $indexCounter++; 
            
            
            
            $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('C'.$indexCounter, $empname)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('D'.$indexCounter, $teamLeader)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('E'.$indexCounter, $designation)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('F'.$indexCounter, $dept)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('G'.$indexCounter, $misData->oldsalary)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('H'.$indexCounter, $misData->newsalary)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('I'.$indexCounter, $workLocation)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('J'.$indexCounter, $request1)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('K'.$indexCounter, $requeststatus)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('L'.$indexCounter, $effectiveDate)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');



            
            
            
            $sn++;
            
        }
            
            
        for($col = 'A'; $col !== 'L'; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
            
        $spreadsheet->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
                
        for($index=1;$index<=$indexCounter;$index++)
        {
                foreach (range('A','L') as $col) {
                    $spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
                }
        }
		
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="Salary Change Complete";					
				$logObj->save();
        $writer = new Xlsx($spreadsheet);
        $writer->save(public_path('uploads/exportChangeSalary/'.$filename));	
        echo $filename;
        exit;
	}








	public function exportChangeSalaryAllReport(Request $request)
	{
        //return $request->all();
        $parameters = $request->input(); 
        $selectedId = $parameters['selectedIds'];
        
            
        $filename = 'change_salary_all_report_'.date("d-m-Y").'.xlsx';
        $spreadsheet = new Spreadsheet(); 
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'Change Salary List - '.date("d-m-Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
        $indexCounter = 2;
        $sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('D'.$indexCounter, strtoupper('Team Leader'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('E'.$indexCounter, strtoupper('Designation'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('F'.$indexCounter, strtoupper('Department'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('G'.$indexCounter, strtoupper('Old Salary'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('H'.$indexCounter, strtoupper('New Salary'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('I'.$indexCounter, strtoupper('Location'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('J'.$indexCounter, strtoupper('Request Type'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('K'.$indexCounter, strtoupper('Status'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('L'.$indexCounter, strtoupper('Increment/Decrement Status'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('M'.$indexCounter, strtoupper('MOL Status'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('N'.$indexCounter, strtoupper('Final Status'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('O'.$indexCounter, strtoupper('Salary Effective from'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('P'.$indexCounter, strtoupper('Request Status'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('Q'.$indexCounter, strtoupper('Request Created'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            

        $sn = 1;
        foreach ($selectedId as $sid) 
        {
            //echo $sid;
            $misData = ChangeSalary::where("id",$sid)->first();

            //$empName = $this->getEmployeeName($misData->emp_id);
            $teamLeader = $this->getTL($misData->emp_id);
            $designation = $this->getDesignationSalary($misData->emp_id);
            $dept = $this->getDepartment($misData->emp_id);
            $empname = $this->getEmpNameSalary($misData->emp_id);
            $workLocation = $this->getAttributeListValue($misData->emp_id,'work_location');
            $request1 = $this->getRequestType($misData->request_type);
            $requeststatus = $this->getStatus($misData->emp_id,$misData->id);
            $incrementStatus = $this->getRequestIncrementStatus($misData->emp_id,$misData->id,$misData->request_type);
            $molStatus = $this->getRequestMolStatus($misData->emp_id,$misData->id,$misData->request_type);
            // $balancedAmt = $this->getBalancedAmt($misData->emp_id);

			
			if(($misData->request_type==1 && $misData->incrementstatus==1) || ($misData->request_type==4 && $misData->incrementstatus==1))
			{
				$fStatus = "Approved";
			}			
			elseif($misData->request_type==2 && $misData->molstatus==1)
			{
				$fStatus = "Approved";
			}			
			elseif($misData->request_type==3 && $misData->molstatus==1 && $misData->incrementstatus==1)
			{
				$fStatus = "Approved";
			}			
			else
			{
				$fStatus = "Pending";
			}




			if($misData->new_salary_effective_from!='')
			{
				$effectiveDate = date("F, Y", strtotime($misData->new_salary_effective_from));
			}
			else
			{
				$effectiveDate = '--';
			}



			if($misData->approvedrejectstatus==1)
			{
				$reqStatus = 'Approved';
			}
			else
			{
				$reqStatus = '--';
			}



			if($misData->created_at!='')
			{
				$createdDate = date("d F, Y", strtotime($misData->created_at));
			}
			else
			{
				$createdDate = '--';
			}
			
			
            
            

            



            $indexCounter++; 
            
            
            
            $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('C'.$indexCounter, $empname)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('D'.$indexCounter, $teamLeader)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('E'.$indexCounter, $designation)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('F'.$indexCounter, $dept)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('G'.$indexCounter, $misData->oldsalary)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('H'.$indexCounter, $misData->newsalary)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('I'.$indexCounter, $workLocation)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('J'.$indexCounter, $request1)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('K'.$indexCounter, $requeststatus)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, $incrementStatus)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, $molStatus)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, $fStatus)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

            $sheet->setCellValue('O'.$indexCounter, $effectiveDate)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, $reqStatus)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, $createdDate)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');



            
            
            
            $sn++;
            
        }
            
            
        for($col = 'A'; $col !== 'O'; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
            
        $spreadsheet->getActiveSheet()->getStyle('A1:N1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
                
        for($index=1;$index<=$indexCounter;$index++)
        {
                foreach (range('A','O') as $col) {
                    $spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
                }
        }
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="Salary Change All";					
				$logObj->save();
        $writer = new Xlsx($spreadsheet);
        $writer->save(public_path('uploads/exportChangeSalary/'.$filename));	
        echo $filename;
        exit;
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

	public static function getDesignationSalary($empid)
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

	public static function getEmpNameSalary($empid)
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

	public static function getAttributeListValue($empid,$attributecode)
	{	
	//echo $empid;
	//echo $attributecode;//exit;
		$attr = Employee_attribute::where('emp_id',$empid)->where("attribute_code",$attributecode)->first();
		if($attr != '')
		{
		return $attr->attribute_values;
		}
		else
		{
		return '';
		}
	}



	public static function getRequestIncrementStatus($empid = NULL,$rowid = NULL,$requesttype = NULL)
	{
		if($requesttype==1 || $requesttype==3)
		{
			$empdata = ChangeSalary::where("emp_id",$empid)->where("id",$rowid)->where('request_type',$requesttype)->orderBy('id','DESC')->first();

			if($empdata && $empdata->incrementstatus==1 && $empdata->molstatus==0)
			{
				return 'Increment Done';
			}
			elseif($empdata && $empdata->incrementstatus==0 && $empdata->molstatus==0)
			{
				return 'Increment Pending';
			}
			else
			{
				return "--";
			}
		}
		elseif($requesttype==4)
		{
			$empdata = ChangeSalary::where("emp_id",$empid)->where("id",$rowid)->where('request_type',$requesttype)->orderBy('id','DESC')->first();

			if($empdata && $empdata->incrementstatus==1 && $empdata->molstatus==0)
			{
				return 'Decrement Done';
			}
			elseif($empdata && $empdata->incrementstatus==0 && $empdata->molstatus==0)
			{
				return 'Decrement Pending';
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

	public static function getRequestMolStatus($empid = NULL,$rowid = NULL,$requesttype = NULL)
	{
		if($requesttype==2 || $requesttype==3)
		{
			$empdata = ChangeSalary::where("emp_id",$empid)->where("id",$rowid)->where('request_type',$requesttype)->orderBy('id','DESC')->first();

			if($empdata && $empdata->molstatus==1 && $empdata->finalstatus==0)
			{
				return 'MOL Done';
			}
			elseif($empdata && $empdata->molstatus==0 && $empdata->finalstatus==0)
			{
				return 'MOL Pending';
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



	public function updateFinalStatus(Request $request)
	{
		$salaryData = ChangeSalary::whereNull("updateby_cron")->orderBy('id','DESC')->get();

		
		if(!empty($salaryData))
		{
			foreach($salaryData as $salary)
			{
				$salaryRequestData = ChangeSalary::where("id",$salary->id)->orderBy('id','DESC')->first();

				if($salaryRequestData->request_type==1 && $salaryRequestData->incrementstatus==1 && $salaryRequestData->approvedrejectstatus==1)
				{
					$salaryRequestData->final_salary_status = 1;
					$salaryRequestData->updateby_cron = 1;
					$salaryRequestData->save(); 
				}
				if($salaryRequestData->request_type==2 && $salaryRequestData->molstatus==1 && $salaryRequestData->approvedrejectstatus==1)
				{
					$salaryRequestData->final_salary_status = 1;
					$salaryRequestData->updateby_cron = 1;
					$salaryRequestData->save(); 
				}
				if(($salaryRequestData->request_type==3) && ($salaryRequestData->molstatus==1 && $salaryRequestData->incrementstatus==1) && $salaryRequestData->approvedrejectstatus==1)
				{
					$salaryRequestData->final_salary_status = 1;
					$salaryRequestData->updateby_cron = 1;
					$salaryRequestData->save(); 
				}
				if($salaryRequestData->request_type==4 && $salaryRequestData->incrementstatus==1 && $salaryRequestData->approvedrejectstatus==1)
				{
					$salaryRequestData->final_salary_status = 1;
					$salaryRequestData->updateby_cron = 1;
					$salaryRequestData->save(); 
				}

			}
			return response()->json(['success'=>'Records Updated Successfully']);
		} 
		else
		{
			return response()->json(['success'=>'No Records found to Update']);
		}
     
 

    

		
	}


	public function getEffectiveDateFormContent(Request $request)
	{
		$empid = $request->empid;
		$rowid = $request->rowid;

		$requestData = ChangeSalary::where("emp_id",$empid)->where("id",$rowid)->orderBy('id','DESC')->first();

		return view("ChangeSalaryNew/effectiveDateRequest",compact('requestData'));

	}

	public function effectiveMolRequestPostSubmit(Request $request)
	{
		$validator = Validator::make($request->all(), 
        [			
			'effectivefromdatemol' => 'required',
        ],
		[
			'effectivefromdatemol.required'=> 'Please Select Effective Salary Date',				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			$requestData = ChangeSalary::where("emp_id",$request->empid)->where("id",$request->rowid)->orderBy('id','DESC')->first();
			$requestData->new_salary_effective_from = $request->effectivefromdatemol;
			$requestData->save(); 
			return response()->json(['success'=>'Effective Date Added Successfully.']);
		}

	}


























	public function changeStatusActionProcessTestTest(Request $request)
	{
		$rowid = $request->rowid;
		$actionid = $request->action;
		$userid=$request->session()->get('EmployeeId');

		if($userid!='')
		{
			$failedmsg='';
			$changesalaryRequestinfo = ChangeSalary::where("id",$rowid)->orderBy('id','DESC')->first();

			if($changesalaryRequestinfo->approvedrejectstatus==1)
			{
				$successmsg="This Change Salary Request Already Approved.";
				return view("ChangeSalaryNew/email_process_1",compact('successmsg','failedmsg'));
			}
			elseif($changesalaryRequestinfo->approvedrejectstatus==2)
			{
				$successmsg="This Change Salary Request Already Rejected.";
				return view("ChangeSalaryNew/email_process_1",compact('successmsg','failedmsg'));
			}
			else
			{
				if($rowid !='' && $actionid !='')
				{
					if($actionid==1)
					{
						$changesalaryRequest = ChangeSalary::where("id",$rowid)->orderBy('id','DESC')->first();
						
						$changesalaryRequest->status =1;
						$changesalaryRequest->request_status =2;
						$changesalaryRequest->approvedrejectstatus =1;
						$changesalaryRequest->approvedrejectby =$userid;
						$changesalaryRequest->approvedrejecton =date('Y-m-d H:i:s');

						//$changesalaryRequest->comments =$request->comment;
						//$changesalaryRequest->behalfoff_user =$request->behalf_user;
						$changesalaryRequest->save();


						$changesalaryLogs = new Change_Salary_logs();
						$changesalaryLogs->emp_id = $changesalaryRequest->emp_id;
						$changesalaryLogs->request = $changesalaryRequest->request_type;
						$changesalaryLogs->request_event =2;
						$changesalaryLogs->user_id =$userid;
						$changesalaryLogs->event_at =date('Y-m-d');
						$changesalaryLogs->save();

						$successmsg="Salary Change Request Approved Successfully.";
						return view("ChangeSalaryNew/email_process_1",compact('successmsg','failedmsg'));

					}

					if($actionid==2)
					{
						$changesalaryRequest = ChangeSalary::where("id",$rowid)->orderBy('id','DESC')->first();
						
						$changesalaryRequest->status =1;
						$changesalaryRequest->request_status =1;
						$changesalaryRequest->approvedrejectstatus =2;
						$changesalaryRequest->approvedrejectby =$userid;
						$changesalaryRequest->approvedrejecton =date('Y-m-d H:i:s');
						$changesalaryRequest->save();   

						$changesalaryLogs = new Change_Salary_logs();
						$changesalaryLogs->emp_id = $request->empid;
						$changesalaryLogs->request = $request->requesttype;
						$changesalaryLogs->request_event =3;
						$changesalaryLogs->user_id =$userid;
						$changesalaryLogs->event_at =date('Y-m-d');
						$changesalaryLogs->save();

						$successmsg="Salary Change Request Rejected.";
						return view("ChangeSalaryNew/email_process_1",compact('successmsg','failedmsg'));
					}
				}
				else
				{
					return response()->json(['success'=>'Id not Found.']);

				}
			}
		}
		else
		{
			$successmsg='';
			$failedmsg="To Approved/Disapproved request, Please firstly login in Portal and then again click on Approved/Disapproved.";
			return view("ChangeSalaryNew/email_process_1",compact('failedmsg','successmsg'));
			//return response()->json(['success'=>'Please firstly login in Portal.']);
		}
		
	}

	



}
