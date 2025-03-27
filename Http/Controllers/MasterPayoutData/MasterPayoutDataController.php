<?php

namespace App\Http\Controllers\MasterPayoutData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
use App\Models\SEPayout\PayoutTlMapping;
use App\Models\EmpProcess\TLUpdateLog;
use App\Models\Dashboard\MasterPayout;

class MasterPayoutDataController extends Controller
{
    
       public function MasterPayoutData(Request $request)
		{			
		
			
			$empId=MasterPayout::whereNotNull('employee_id')->get();
			$EmpName=MasterPayout::whereNotNull('agent_name')->get();
			$recdata=RecruiterDetails::where("status",1)->get();
			return view("MasterPayoutData/MasterPayoutData",compact('empId','EmpName','recdata'));
			
		}
	   
	   
	   
	   		public function listingMasterPayoutDataAll(Request $request){
			
			$deptID = '';
			
			if(!empty($request->session()->get('onboading_page_limitall')) && $request->session()->get('onboading_page_limitall')!="undefined")
				{
					$paginationValue = $request->session()->get('onboading_page_limitall');
				}
				else
				{
					$paginationValue = 10;
				}
				
				$whereraw='';
				$whereraw1 = '';
				 $selectedFilter['EMPID'] = '';
				 $selectedFilter['f_name'] = '';
				 $selectedFilter['l_name'] = '';
				  $selectedFilter['designation'] = '';
				  $selectedFilter['sourcecode'] = '';
				  $selectedFilter['department'] = '';
				  $selectedFilter['vintage'] = '';
				  $selectedFilter['Location'] = '';
				  $selectedFilter['VisaUnderCompany'] = '';
				if(!empty($request->session()->get('dept_filter_for_emp')) && $request->session()->get('dept_filter_for_emp') != 'All'){
				
				//$filesource='';
					$deptID = $request->session()->get('dept_filter_for_emp');
					$selectedFilter['department'] = $deptID;
				if($deptID !=''){
				
					$whereraw = 'dept_id = "'.$deptID.'"';
				//$whereraw = 'type,Team Leader';
				}
				}
				else{
					//$empdetails = Employee_details::paginate($paginationValue);	
					//$reportsCount = Employee_details::get()->count();
					//$activeCount = Employee_details::where('status',1)->get()->count();
					//$inactiveCount = Employee_details::where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('MasterPayoutData_empid_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'employee_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And employee_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_fname_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'agent_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And agent_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_location_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('MasterPayoutData_location_emp_filter_inner_list');
					 $locationArray = explode(",",$location);
					 $locationfinalarray=array();
					 foreach($locationArray as $locationarray){
						 $locationfinalarray[]="'".$locationarray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $locationfinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'location IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And location IN('.$finalcname.')';
					}
				}
				
				
				if(!empty($request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_id IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter_id IN('.$RecruiterName.')';
					}
				}
				
				
				
				
				
				
						//echo $whereraw;//exit;		
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 96 || $empsessionId== 97 || $empsessionId== 123){
					$interviewarr=array(9);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
					$interviewarr=array(8,36,43);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else{
					/*nothings to do*/
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'dept_id IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND dept_id IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}				   
				}
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = MasterPayout::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue);
				$reportsCount = MasterPayout::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$empdetails = MasterPayout::orderBy("id","DESC")->paginate($paginationValue);
					$reportsCount = MasterPayout::get()->count();	
										
				}
			
			
			$empdetails->setPath(config('app.url/listingMasterPayoutDataAll'));
			
			
			return view("MasterPayoutData/listingMasterPayoutDataAll",compact('empdetails','paginationValue','reportsCount'));
		}
					
		public function searchbyempNameMasterPayoutData(Request $request)
		{
			$selectedFilter = $request->input();
			//print_r($selectedFilter);exit;
			//$fname = $request->emp_filtername;
			$fname='';
			if($request->emp_filtername!=''){
			$fnamearray=array_filter($request->emp_filtername);		
			$fname=implode(",", $fnamearray);
			}
			$lname = $request->emp_lastname;
			$source = $request->emp_sourcecode;
			$location='';
			if($request->locationdata!=''){
			$locationarray=array_filter($request->locationdata);		
			$location=implode(",", $locationarray);
			}
			$designation='';
			if($request->designationdata!=''){
			$designationarray=array_filter($request->designationdata);			
			$designation=implode(",", $designationarray);				
			}
			$empid='';
			if($request->empId!=''){
			$empIdarray=array_filter($request->empId);		
			$empid=implode(",", $empIdarray);
			}
			$jobfunction='';
			if($request->jobfunction!=''){
			$jobfunctionarray=array_filter($request->jobfunction);		
			$jobfunction=implode(",", $jobfunctionarray);
			}
			$RecruiterName='';
			if($request->RecruiterName!=''){
			$RecruiterNamearray=array_filter($request->RecruiterName);		
			$RecruiterName=implode(",", $RecruiterNamearray);
			}
			$request->session()->put('MasterPayoutData_empid_emp_filter_inner_list',$empid);
			$request->session()->put('MasterPayoutData_fname_emp_filter_inner_list',$fname);
			$request->session()->put('MasterPayoutData_lname_emp_filter_inner_list',$lname);
			$request->session()->put('MasterPayoutData_scode_emp_filter_inner_list',$source);
			$request->session()->put('MasterPayoutData_location_emp_filter_inner_list',$location);
			$request->session()->put('MasterPayoutData_design_emp_filter_inner_list',$designation);
			$request->session()->put('MasterPayoutData_jobfunction_emp_filter_inner_list',$jobfunction);
			$request->session()->put('MasterPayoutData_RecruiterName_emp_filter_inner_list',$RecruiterName);
			
			
			
			
			 
		}
		public function empFilterresetMasterPayoutData(Request $request)
		{
			
			$request->session()->put('MasterPayoutData_fname_emp_filter_inner_list','');
			$request->session()->put('MasterPayoutData_lname_emp_filter_inner_list','');
			$request->session()->put('MasterPayoutData_scode_emp_filter_inner_list','');
			$request->session()->put('MasterPayoutData_location_emp_filter_inner_list','');
			$request->session()->put('MasterPayoutData_design_emp_filter_inner_list','');
			$request->session()->put('MasterPayoutData_empid_emp_filter_inner_list','');
			$request->session()->put('MasterPayoutData_jobfunction_emp_filter_inner_list','');
			$request->session()->put('MasterPayoutData_RecruiterName_emp_filter_inner_list','');
			 	
		}
		
		
		
		
		
		
		
public static function getJobFunction($jobid){
		$departmentDetails = JobFunction::where("id",$jobid)->first();
		   if($departmentDetails != '')
		   {
				return $departmentDetails->name;
		   }
		   else
		   {
			   return '';
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
		public function listingMasterPayoutDataENBD(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('onboading_page_limitall')) && $request->session()->get('onboading_page_limitall')!="undefined")
				{
					$paginationValue = $request->session()->get('onboading_page_limitall');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				 $selectedFilter['EMPID'] = '';
				 $selectedFilter['f_name'] = '';
				 $selectedFilter['l_name'] = '';
				  $selectedFilter['designation'] = '';
				  $selectedFilter['sourcecode'] = '';
				  $selectedFilter['department'] = '';
				  $selectedFilter['vintage'] = '';
				  $selectedFilter['Location'] = '';
				  $selectedFilter['VisaUnderCompany'] = '';
				if(!empty($request->session()->get('dept_filter_for_emp')) && $request->session()->get('dept_filter_for_emp') != 'All'){
				
				//$filesource='';
					$deptID = $request->session()->get('dept_filter_for_emp');
					$selectedFilter['department'] = $deptID;
				if($deptID !=''){
				
					$whereraw = 'dept_id = "'.$deptID.'"';
				//$whereraw = 'type,Team Leader';
				}
				}
				else{
					//$empdetails = Employee_details::paginate($paginationValue);	
					//$reportsCount = Employee_details::get()->count();
					//$activeCount = Employee_details::where('status',1)->get()->count();
					//$inactiveCount = Employee_details::where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('MasterPayoutData_empid_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'employee_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And employee_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_fname_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'agent_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And agent_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_location_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('MasterPayoutData_location_emp_filter_inner_list');
					 $locationArray = explode(",",$location);
					 $locationfinalarray=array();
					 foreach($locationArray as $locationarray){
						 $locationfinalarray[]="'".$locationarray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $locationfinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'location IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And location IN('.$finalcname.')';
					}
				}
				
				
				if(!empty($request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_id IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter_id IN('.$RecruiterName.')';
					}
				}
						//echo $whereraw;//exit;		
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 96 || $empsessionId== 97 || $empsessionId== 123){
					$interviewarr=array(9);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
					$interviewarr=array(8,36,43);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else{
					/*nothings to do*/
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'dept_id IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND dept_id IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}				   
				}
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = MasterPayout::orderBy("id","DESC")->whereRaw($whereraw)->where("dept_id",9)->paginate($paginationValue);
				$reportsCountenbd = MasterPayout::whereRaw($whereraw)->where("dept_id",9)->get()->count();
				}
				else
				{
					$empdetails = MasterPayout::orderBy("id","DESC")->where("dept_id",9)->paginate($paginationValue);
					$reportsCountenbd = MasterPayout::where("dept_id",9)->get()->count();	
					}
			
			
			$empdetails->setPath(config('app.url/listingMasterPayoutDataENBD'));
			
			return view("MasterPayoutData/listingMasterPayoutDataENBD",compact('empdetails','paginationValue','reportsCountenbd'));
		}
		public function listingMasterPayoutDatadeem(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('onboading_page_limitall')) && $request->session()->get('onboading_page_limitall')!="undefined")
				{
					$paginationValue = $request->session()->get('onboading_page_limitall');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				 $selectedFilter['EMPID'] = '';
				 $selectedFilter['f_name'] = '';
				 $selectedFilter['l_name'] = '';
				  $selectedFilter['designation'] = '';
				  $selectedFilter['sourcecode'] = '';
				  $selectedFilter['department'] = '';
				  $selectedFilter['vintage'] = '';
				  $selectedFilter['Location'] = '';
				  $selectedFilter['VisaUnderCompany'] = '';
				if(!empty($request->session()->get('dept_filter_for_emp')) && $request->session()->get('dept_filter_for_emp') != 'All'){
				
				//$filesource='';
					$deptID = $request->session()->get('dept_filter_for_emp');
					$selectedFilter['department'] = $deptID;
				if($deptID !=''){
				
					$whereraw = 'dept_id = "'.$deptID.'"';
				//$whereraw = 'type,Team Leader';
				}
				}
				else{
					//$empdetails = Employee_details::paginate($paginationValue);	
					//$reportsCount = Employee_details::get()->count();
					//$activeCount = Employee_details::where('status',1)->get()->count();
					//$inactiveCount = Employee_details::where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('MasterPayoutData_empid_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'employee_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And employee_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_fname_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'agent_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And agent_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_location_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('MasterPayoutData_location_emp_filter_inner_list');
					 $locationArray = explode(",",$location);
					 $locationfinalarray=array();
					 foreach($locationArray as $locationarray){
						 $locationfinalarray[]="'".$locationarray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $locationfinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'location IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And location IN('.$finalcname.')';
					}
				}
				
				
				if(!empty($request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_id IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter_id IN('.$RecruiterName.')';
					}
				}
						//echo $whereraw;//exit;		
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 96 || $empsessionId== 97 || $empsessionId== 123){
					$interviewarr=array(9);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
					$interviewarr=array(8,36,43);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else{
					/*nothings to do*/
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'dept_id IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND dept_id IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}				   
				}
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = MasterPayout::orderBy("id","DESC")->whereRaw($whereraw)->where("dept_id",8)->paginate($paginationValue);
				$reportsCountdeem = MasterPayout::whereRaw($whereraw)->where("dept_id",8)->get()->count();
					
				}
				else
				{
					$empdetails = MasterPayout::orderBy("id","DESC")->where("dept_id",8)->paginate($paginationValue);
					$reportsCountdeem = MasterPayout::where("dept_id",8)->get()->count();	
					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/listingMasterPayoutDatadeem'));
			
			
			return view("UpdateTL/listingUpdateTLListdeemTL",compact('empdetails','paginationValue','reportsCountdeem'));
		}
		public function listingMasterPayoutDatamashreq(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('onboading_page_limitall')) && $request->session()->get('onboading_page_limitall')!="undefined" )
				{
					$paginationValue = $request->session()->get('onboading_page_limitall');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				 $selectedFilter['EMPID'] = '';
				 $selectedFilter['f_name'] = '';
				 $selectedFilter['l_name'] = '';
				  $selectedFilter['designation'] = '';
				  $selectedFilter['sourcecode'] = '';
				  $selectedFilter['department'] = '';
				  $selectedFilter['vintage'] = '';
				  $selectedFilter['Location'] = '';
				  $selectedFilter['VisaUnderCompany'] = '';
				if(!empty($request->session()->get('dept_filter_for_emp')) && $request->session()->get('dept_filter_for_emp') != 'All'){
				
				//$filesource='';
					$deptID = $request->session()->get('dept_filter_for_emp');
					$selectedFilter['department'] = $deptID;
				if($deptID !=''){
				
					$whereraw = 'dept_id = "'.$deptID.'"';
				//$whereraw = 'type,Team Leader';
				}
				}
				else{
					//$empdetails = Employee_details::paginate($paginationValue);	
					//$reportsCount = Employee_details::get()->count();
					//$activeCount = Employee_details::where('status',1)->get()->count();
					//$inactiveCount = Employee_details::where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('MasterPayoutData_empid_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'employee_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And employee_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_fname_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'agent_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And agent_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_location_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('MasterPayoutData_location_emp_filter_inner_list');
					 $locationArray = explode(",",$location);
					 $locationfinalarray=array();
					 foreach($locationArray as $locationarray){
						 $locationfinalarray[]="'".$locationarray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $locationfinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'location IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And location IN('.$finalcname.')';
					}
				}
				
				
				if(!empty($request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_id IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter_id IN('.$RecruiterName.')';
					}
				}
						//echo $whereraw;//exit;		
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 96 || $empsessionId== 97 || $empsessionId== 123){
					$interviewarr=array(9);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
					$interviewarr=array(8,36,43);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else{
					/*nothings to do*/
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'dept_id IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND dept_id IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}				   
				}
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = MasterPayout::orderBy("id","DESC")->whereRaw($whereraw)->where("dept_id",36)->paginate($paginationValue);
				$reportsCountmashreq = MasterPayout::whereRaw($whereraw)->where("dept_id",36)->get()->count();
				}
				else
				{
					$empdetails = MasterPayout::orderBy("id","DESC")->where("dept_id",36)->paginate($paginationValue);
					$reportsCountmashreq = MasterPayout::where("dept_id",36)->get()->count();	
				}
			
			
			$empdetails->setPath(config('app.url/listingMasterPayoutDatamashreq'));
			
			return view("MasterPayoutData/listingMasterPayoutDatamashreq",compact('empdetails','paginationValue','reportsCountmashreq'));
		}
		public function listingMasterPayoutDataaafaq(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('onboading_page_limitall')) && $request->session()->get('onboading_page_limitall')!="undefined")
				{
					$paginationValue = $request->session()->get('onboading_page_limitall');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				 $selectedFilter['EMPID'] = '';
				 $selectedFilter['f_name'] = '';
				 $selectedFilter['l_name'] = '';
				  $selectedFilter['designation'] = '';
				  $selectedFilter['sourcecode'] = '';
				  $selectedFilter['department'] = '';
				  $selectedFilter['vintage'] = '';
				  $selectedFilter['Location'] = '';
				  $selectedFilter['VisaUnderCompany'] = '';
				if(!empty($request->session()->get('dept_filter_for_emp')) && $request->session()->get('dept_filter_for_emp') != 'All'){
				
				//$filesource='';
					$deptID = $request->session()->get('dept_filter_for_emp');
					$selectedFilter['department'] = $deptID;
				if($deptID !=''){
				
					$whereraw = 'dept_id = "'.$deptID.'"';
				//$whereraw = 'type,Team Leader';
				}
				}
				else{
					//$empdetails = Employee_details::paginate($paginationValue);	
					//$reportsCount = Employee_details::get()->count();
					//$activeCount = Employee_details::where('status',1)->get()->count();
					//$inactiveCount = Employee_details::where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('MasterPayoutData_empid_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'employee_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And employee_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_fname_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'agent_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And agent_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_location_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('MasterPayoutData_location_emp_filter_inner_list');
					 $locationArray = explode(",",$location);
					 $locationfinalarray=array();
					 foreach($locationArray as $locationarray){
						 $locationfinalarray[]="'".$locationarray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $locationfinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'location IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And location IN('.$finalcname.')';
					}
				}
				
				
				if(!empty($request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_id IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter_id IN('.$RecruiterName.')';
					}
				}
						//echo $whereraw;//exit;		
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 96 || $empsessionId== 97 || $empsessionId== 123){
					$interviewarr=array(9);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
					$interviewarr=array(8,36,43);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else{
					/*nothings to do*/
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'dept_id IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND dept_id IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}				   
				}
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = MasterPayout::orderBy("id","DESC")->whereRaw($whereraw)->where("dept_id",43)->paginate($paginationValue);
				$reportsCountaafaq = MasterPayout::whereRaw($whereraw)->where("dept_id",43)->get()->count();
					
				}
				else
				{
					$empdetails = MasterPayout::orderBy("id","DESC")->where("dept_id",43)->paginate($paginationValue);
					$reportsCountaafaq = MasterPayout::where("dept_id",43)->get()->count();	
					
					
				}
			
			
			$empdetails->setPath(config('app.url/listingMasterPayoutDataaafaq'));
			
			
			return view("MasterPayoutData/listingUpdateTLListaafaqTL",compact('empdetails','paginationValue','reportsCountaafaq'));
		}
		public function listingMasterPayoutDatadib(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('onboading_page_limitall')) && $request->session()->get('onboading_page_limitall')!="undefined")
				{
					$paginationValue = $request->session()->get('onboading_page_limitall');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				 $selectedFilter['EMPID'] = '';
				 $selectedFilter['f_name'] = '';
				 $selectedFilter['l_name'] = '';
				  $selectedFilter['designation'] = '';
				  $selectedFilter['sourcecode'] = '';
				  $selectedFilter['department'] = '';
				  $selectedFilter['vintage'] = '';
				  $selectedFilter['Location'] = '';
				  $selectedFilter['VisaUnderCompany'] = '';
				if(!empty($request->session()->get('dept_filter_for_emp')) && $request->session()->get('dept_filter_for_emp') != 'All'){
				
				//$filesource='';
					$deptID = $request->session()->get('dept_filter_for_emp');
					$selectedFilter['department'] = $deptID;
				if($deptID !=''){
				
					$whereraw = 'dept_id = "'.$deptID.'"';
				//$whereraw = 'type,Team Leader';
				}
				}
				else{
					//$empdetails = Employee_details::paginate($paginationValue);	
					//$reportsCount = Employee_details::get()->count();
					//$activeCount = Employee_details::where('status',1)->get()->count();
					//$inactiveCount = Employee_details::where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('MasterPayoutData_empid_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'employee_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And employee_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_fname_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'agent_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And agent_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_location_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('MasterPayoutData_location_emp_filter_inner_list');
					 $locationArray = explode(",",$location);
					 $locationfinalarray=array();
					 foreach($locationArray as $locationarray){
						 $locationfinalarray[]="'".$locationarray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $locationfinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'location IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And location IN('.$finalcname.')';
					}
				}
				
				
				if(!empty($request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_id IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter_id IN('.$RecruiterName.')';
					}
				}
						//echo $whereraw;//exit;		
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 96 || $empsessionId== 97 || $empsessionId== 123){
					$interviewarr=array(9);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
					$interviewarr=array(8,36,43);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else{
					/*nothings to do*/
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'dept_id IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND dept_id IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}				   
				}
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = MasterPayout::orderBy("id","DESC")->whereRaw($whereraw)->where("dept_id",46)->paginate($paginationValue);
				$reportsCountdib = MasterPayout::whereRaw($whereraw)->where("dept_id",46)->get()->count();
					}
				else
				{
					$empdetails = MasterPayout::orderBy("id","DESC")->where("dept_id",46)->paginate($paginationValue);
					$reportsCountdib = MasterPayout::where("dept_id",46)->get()->count();	
					}
			
			
			$empdetails->setPath(config('app.url/listingMasterPayoutDatadib'));
			
			return view("MasterPayoutData/listingMasterPayoutDatadib",compact('empdetails','paginationValue','reportsCountdib'));
		}
		public function listingMasterPayoutDatacbd(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('onboading_page_limitall')) && $request->session()->get('onboading_page_limitall')!="undefined")
				{
					$paginationValue = $request->session()->get('onboading_page_limitall');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				 $selectedFilter['EMPID'] = '';
				 $selectedFilter['f_name'] = '';
				 $selectedFilter['l_name'] = '';
				  $selectedFilter['designation'] = '';
				  $selectedFilter['sourcecode'] = '';
				  $selectedFilter['department'] = '';
				  $selectedFilter['vintage'] = '';
				  $selectedFilter['Location'] = '';
				  $selectedFilter['VisaUnderCompany'] = '';
				if(!empty($request->session()->get('dept_filter_for_emp')) && $request->session()->get('dept_filter_for_emp') != 'All'){
				
				//$filesource='';
					$deptID = $request->session()->get('dept_filter_for_emp');
					$selectedFilter['department'] = $deptID;
				if($deptID !=''){
				
					$whereraw = 'dept_id = "'.$deptID.'"';
				//$whereraw = 'type,Team Leader';
				}
				}
				else{
					//$empdetails = Employee_details::paginate($paginationValue);	
					//$reportsCount = Employee_details::get()->count();
					//$activeCount = Employee_details::where('status',1)->get()->count();
					//$inactiveCount = Employee_details::where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('MasterPayoutData_empid_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'employee_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And employee_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_fname_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'agent_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And agent_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_location_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('MasterPayoutData_location_emp_filter_inner_list');
					 $locationArray = explode(",",$location);
					 $locationfinalarray=array();
					 foreach($locationArray as $locationarray){
						 $locationfinalarray[]="'".$locationarray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $locationfinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'location IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And location IN('.$finalcname.')';
					}
				}
				
				
				if(!empty($request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_id IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter_id IN('.$RecruiterName.')';
					}
				}
						//echo $whereraw;//exit;		
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 96 || $empsessionId== 97 || $empsessionId== 123){
					$interviewarr=array(9);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
					$interviewarr=array(8,36,43);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else{
					/*nothings to do*/
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'dept_id IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND dept_id IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}				   
				}
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = MasterPayout::orderBy("id","DESC")->whereRaw($whereraw)->where("dept_id",47)->paginate($paginationValue);
				$reportsCountscb = MasterPayout::whereRaw($whereraw)->where("dept_id",47)->get()->count();
				}
				else
				{
					$empdetails = MasterPayout::orderBy("id","DESC")->where("dept_id",47)->paginate($paginationValue);
					$reportsCountscb = MasterPayout::where("dept_id",47)->get()->count();	
				}
			
			
			$empdetails->setPath(config('app.url/listingMasterPayoutDatacbd'));
			
			return view("MasterPayoutData/listingMasterPayoutDatascb",compact('empdetails','paginationValue','reportsCountscb'));
		}
		public function listingMasterPayoutDatascb(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('onboading_page_limitall')) && $request->session()->get('onboading_page_limitall')!="undefined")
				{
					$paginationValue = $request->session()->get('onboading_page_limitall');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				 $selectedFilter['EMPID'] = '';
				 $selectedFilter['f_name'] = '';
				 $selectedFilter['l_name'] = '';
				  $selectedFilter['designation'] = '';
				  $selectedFilter['sourcecode'] = '';
				  $selectedFilter['department'] = '';
				  $selectedFilter['vintage'] = '';
				  $selectedFilter['Location'] = '';
				  $selectedFilter['VisaUnderCompany'] = '';
				if(!empty($request->session()->get('dept_filter_for_emp')) && $request->session()->get('dept_filter_for_emp') != 'All'){
				
				//$filesource='';
					$deptID = $request->session()->get('dept_filter_for_emp');
					$selectedFilter['department'] = $deptID;
				if($deptID !=''){
				
					$whereraw = 'dept_id = "'.$deptID.'"';
				//$whereraw = 'type,Team Leader';
				}
				}
				else{
					//$empdetails = Employee_details::paginate($paginationValue);	
					//$reportsCount = Employee_details::get()->count();
					//$activeCount = Employee_details::where('status',1)->get()->count();
					//$inactiveCount = Employee_details::where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('MasterPayoutData_empid_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('MasterPayoutData_empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'employee_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And employee_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_fname_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('MasterPayoutData_fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'agent_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And agent_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('MasterPayoutData_location_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('MasterPayoutData_location_emp_filter_inner_list');
					 $locationArray = explode(",",$location);
					 $locationfinalarray=array();
					 foreach($locationArray as $locationarray){
						 $locationfinalarray[]="'".$locationarray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $locationfinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'location IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And location IN('.$finalcname.')';
					}
				}
				
				
				if(!empty($request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list')) && $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('MasterPayoutData_RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_id IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter_id IN('.$RecruiterName.')';
					}
				}
						//echo $whereraw;//exit;		
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 96 || $empsessionId== 97 || $empsessionId== 123){
					$interviewarr=array(9);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
					$interviewarr=array(8,36,43);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else{
					/*nothings to do*/
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'dept_id IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND dept_id IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}				   
				}
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = MasterPayout::orderBy("id","DESC")->whereRaw($whereraw)->where("dept_id",49)->paginate($paginationValue);
				$reportsCountcbd = MasterPayout::whereRaw($whereraw)->where("dept_id",49)->get()->count();
				}
				else
				{
					$empdetails = MasterPayout::orderBy("id","DESC")->where("dept_id",49)->paginate($paginationValue);
					$reportsCountcbd = MasterPayout::where("dept_id",49)->get()->count();	
				}
			
			
			$empdetails->setPath(config('app.url/listingMasterPayoutDatascb'));
			
			return view("MasterPayoutData/listingMasterPayoutDatacbd",compact('empdetails','paginationValue','reportsCountcbd'));
		}
		public function getEMPDeptId($empid,$tlid){
			$empdetails = Employee_details::where("id",$empid)->first();
			$tldetails = Employee_details::where("id",$tlid)->first();
			if($empdetails !='' && $tldetails!=''){
				$empDPT=$empdetails->dept_id;
				$tlDPT=$tldetails->dept_id;
				if($empDPT!=$tlDPT){
				$designationMod = Designation::where("department_id",$tlDPT)->where("status",1)->get();
				//$tL_details = Employee_details::where("job_role","Team Leader")->where("dept_id",$deptId)->orderBy("id","ASC")->get();
				return view("UpdateTL/designationdropdown",compact('designationMod'));	
				}
			}
			
	
		}
	public function setOffSetForEMPMasterPayoutData(Request $request)
	   {
		   $offset = $request->offset;
		  $request->session()->put('onboading_page_limitall',$offset);
	   }
	   public static function getCheckLoginUserData($userid){
		 $departmentDetails = JobFunctionPermission::where("user_id",$userid)->first();
		   if($departmentDetails != '')
		   {
			   return $departmentDetails->job_function_id;
		   }
		   else
		   {
			   return 'All';
		   }
		  
	}
	

		
}
