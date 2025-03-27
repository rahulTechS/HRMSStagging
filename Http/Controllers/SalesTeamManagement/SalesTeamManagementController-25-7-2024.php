<?php

namespace App\Http\Controllers\SalesTeamManagement;

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
use App\Models\Dashboard\MasterPayout;
use App\Models\EmpProcess\TLUpdateLog;

use App\Models\ChangeDepartment\ChangeDepartmentRequest;
use App\Models\ChangeDepartment\ChangeDepartmentRequestLog;

class SalesTeamManagementController extends Controller
{
    
       public function SalesTeamManagement(Request $request)
		{			
		
			$Designation=Designation::where("status",1)->get();
			$empIdData=Employee_details::whereNull('tl_id')->where("job_function",2)->get();
			$EmpName=Employee_details::whereNull('tl_id')->where("job_function",2)->get();
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
			return view("SalesTeamManagement/UpdateTL",compact('Designation','empIdData','EmpName','design','jobfun','recdata'));
			
		}
	   
	   
	   
	   		public function SalesTeamManagementEmpList(Request $request){
			
			$deptID = '';
			
			
				
				$whereraw='';
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 97 || $empsessionId== 123){
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
				$design=Designation::where("tlsm",2)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);
				
				if($whereraw != '')
				{
				$empdetailsdata = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->get();
				$reportsCount = Employee_details::whereRaw($whereraw)->whereNotNull('tl_id')->where("offline_status",1)->where("job_function",2)->get()->count();
								
				}
				else
				{
					
					$empdetailsdata = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->get();
					//print_r($empdetailsdata);
					$reportsCount = Employee_details::where("offline_status",1)->whereNotNull('tl_id')->where("job_function",2)->get()->count();	
										
				}
			//echo "<pre>";
			//print_r($empdetailsdata);
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			//$empdetails->setPath(config('app.url/SalesTeamManagementEmpList'));
			$empdetails=array();
			//echo count($empdetailsdata);exit;
			if(count($empdetailsdata)>0){
				
				foreach($empdetailsdata as $_Tldata){
					$tL_salesData = Employee_details::where("tl_id",$_Tldata->id)->where("offline_status",1)->where("job_function",2)->get();
					if($tL_salesData!=''){
					$empdetails[$_Tldata->id]=$tL_salesData;
					}
				}
				
				
			}
			
			return view("SalesTeamManagement/UpdateTLList",compact('empdetails','departmentLists','deptID','reportsCount'));
		}
		public function updateSalesTeamManagementData(Request $request){
		$rowId=$request->rowId;
		$empLists = Employee_details::where('emp_id',$rowId)->first();
		 $empsessionId=$request->session()->get('EmployeeId');
			$jobfunctiondetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
			 //echo $jobfunctiondetails->job_function_id;exit;
			 if($jobfunctiondetails!='' && ($jobfunctiondetails->job_function_id==3 || $jobfunctiondetails->job_function_id==4)){
				$data = Employee_details::where("emp_id",$jobfunctiondetails->emp_id)->orderBy("id","ASC")->first();
				if($data!=''){
				$deptId=$data->dept_id;
				$design=Designation::where("tlsm",2)->where("department_id",$deptId)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$tL_details = Employee_details::whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->where("status",1)->orderBy("id","ASC")->get();
				}
				else{
					$tL_details ='';
				}
			 }
			 else{
				 $design=Designation::where("tlsm",2)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				 $tL_details = Employee_details::whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->orderBy("id","ASC")->get();
			 }
		//$tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();
		return view("SalesTeamManagement/UpdateTLFormUpdate",compact('empLists','tL_details'));
		
	}
	public function updateSalesTeamManagementDataDataPost(Request $req)
		{
			$inputData = $req->input();
		/* 	echo '<pre>';
			print_r($inputData);
			exit; */
			$tlIds=$req->input('teamlead');
			$EMPdetailsdata =  Employee_details::where("id",$req->input('rowId'))->first();
			$Tldetails =  Employee_details::where("id",$tlIds)->first();
			$location=$Tldetails->work_location;
			$tldept=$Tldetails->dept_id;
			if($req->input('designation')!=''){
			$empdetails =  Employee_details::find($req->input('rowId'));			
			$empdetails->tl_id=$req->input('teamlead');
			$empdetails->work_location=$location;
			$empdetails->dept_id=$tldept;
			$empdetails->designation_by_doc_collection=	$req->input('designation');		
			$empdetails->save();
			$empAttrExist = Employee_attribute::where("emp_id",$Tldetails->emp_id)->where("attribute_code","work_location")->first();
					if($empAttrExist != '')
					{
						$updateEmpAttr = Employee_attribute::find($empAttrExist->id);
						
					}
					else
					{
						$updateEmpAttr = new Employee_attribute();
					}
					$updateEmpAttr->dept_id = $tldept;
					$updateEmpAttr->emp_id = $Tldetails->emp_id;
					$updateEmpAttr->attribute_code = 'work_location';
					$updateEmpAttr->attribute_values = $location;
					$updateEmpAttr->status = 1;
					$updateEmpAttr->save();		
					
				$logObj = new TLUpdateLog();
				$logObj->empid =$req->input('rowId');
				$logObj->old_dept =$EMPdetailsdata->dept_id;
				$logObj->current_dept =$tldept;
				$logObj->old_design =$EMPdetailsdata->designation_by_doc_collection;
				$logObj->current_design =$req->input('designation');
				$logObj->old_location =$EMPdetailsdata->work_location;
				$logObj->current_location =$location;
				$logObj->created_date =date("Y-m-d");
				$logObj->createdBY =$req->session()->get('EmployeeId');			
				$logObj->old_tl_id=$EMPdetailsdata->tl_id;
				$logObj->current_tl_id=$req->input('teamlead');
				$logObj->title="Sales Team Management";
				$logObj->save();
			
			$empattributesMod = Employee_attribute::where('emp_id',$EMPdetailsdata->emp_id)->get();							
			if(!empty($empattributesMod))
			{
			foreach($empattributesMod as $updatedept){
			$empattributes = Employee_attribute::find($updatedept->id);
			$empattributes->dept_id = $tldept;
			$empattributes->save();
			}
			}
			
			}else{
			
			$empdetails =  Employee_details::find($req->input('rowId'));			
			$empdetails->tl_id=$req->input('teamlead');
			$empdetails->work_location=$location;
			$empdetails->save();
				$logObj = new TLUpdateLog();
				$logObj->empid =$req->input('rowId');
				$logObj->old_dept =$EMPdetailsdata->dept_id;
				$logObj->current_dept =$tldept;
				$logObj->old_location =$EMPdetailsdata->work_location;
				$logObj->current_location =$location;
				$logObj->created_date =date("Y-m-d");;
				$logObj->createdBY =$req->session()->get('EmployeeId');			
				$logObj->old_tl_id=$EMPdetailsdata->tl_id;
				$logObj->current_tl_id=$req->input('teamlead');
				$logObj->title="Sales Team Management";
				$logObj->save();
			}
			$req->session()->flash('message','Data Saved Successfully.');
            echo "Done";
			
		}			
		public function searchbyempNameSalesTeamManagement(Request $request)
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
			$request->session()->put('SalesTeamManagement_empid_emp_filter_inner_list',$empid);
			$request->session()->put('SalesTeamManagement_fname_emp_filter_inner_list',$fname);
			$request->session()->put('SalesTeamManagement_lname_emp_filter_inner_list',$lname);
			$request->session()->put('SalesTeamManagement_scode_emp_filter_inner_list',$source);
			$request->session()->put('SalesTeamManagement_location_emp_filter_inner_list',$location);
			$request->session()->put('SalesTeamManagement_design_emp_filter_inner_list',$designation);
			$request->session()->put('SalesTeamManagement_jobfunction_emp_filter_inner_list',$jobfunction);
			$request->session()->put('SalesTeamManagement_RecruiterName_emp_filter_inner_list',$RecruiterName);
			
			
			
			
			 
		}
		public function empFilterresetSalesTeamManagement(Request $request)
		{
			
			$request->session()->put('SalesTeamManagement_fname_emp_filter_inner_list','');
			$request->session()->put('SalesTeamManagement_lname_emp_filter_inner_list','');
			$request->session()->put('SalesTeamManagement_scode_emp_filter_inner_list','');
			$request->session()->put('SalesTeamManagement_location_emp_filter_inner_list','');
			$request->session()->put('SalesTeamManagement_design_emp_filter_inner_list','');
			$request->session()->put('SalesTeamManagement_empid_emp_filter_inner_list','');
			$request->session()->put('SalesTeamManagement_jobfunction_emp_filter_inner_list','');
			$request->session()->put('SalesTeamManagement_RecruiterName_emp_filter_inner_list','');
			 	
		}
		
		 public function setOffSetForEMPSalesTeamManagement(Request $request)
	   {
		   $offset = $request->offset;
		  $request->session()->put('onboading_page_limitall',$offset);
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
public static function getTeamLeaderdata($empid = NULL)
	   {
		  
			    $emp_details = Employee_details::where("emp_id",$empid)->first(); 
				if($emp_details!=''){
					
					
						 return $emp_details->emp_name;
					}
					else{
						return "--";
					}
		   
	   }	   
	   
	   
	   
		
		
		
		
	  
	   
	   
		
	  
	   public static function department_permission($uid)
	   {
		   $departmentDetails = DepartmentPermission::where("user_id",$uid)->first();
		   if($departmentDetails != '')
		   {
			  $departmentIdsArray =  explode(",",$departmentDetails->department_id);
			   return $departmentIdsArray;
		   }
		   else
		   {
			   return 'All';
		   }
	   }
	   
	   protected function department_permissionInhouse($uid)
	   {
		   $departmentDetails = DepartmentPermission::where("user_id",$uid)->first();
		   if($departmentDetails != '')
		   {
			   return $departmentDetails->department_id;
		   }
		   else
		   {
			   return 'All';
		   }
	   }

	public static function getDocumentofferLetterStatus ($id=NULL){
		
		$documentValuescv = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",14)->first();
		
		$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
		if(($documentValuescv!='' && $documentValuescv!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL)){
			return "Documents Received";
		}
		else{
			return "Documents Not Received";
		}
	}
	public static function getDocumentofferLettercheck($id=NULL){
		
		$documentValuescv = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",14)->first();
		
		$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
		if(($documentValuescv!='' && $documentValuescv!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL)){
			return "1";
		}
		else{
			return "2";
		}
	}
		
	public static function getCandidateName($id)
	   {
		   $documentValues = DocumentCollectionDetails::where("id",$id)->first();
		   //print_r($documentValues);
		   if($documentValues !='' && $documentValues !=NULL)
		   { 
			return $documentValues->emp_name;
		   }
		   else
		   {
			    return "--";
		   }
	   }
	   public static function getVisaTypeName($id = NULL)
	   {
		   $visaType = visaType::where("id",$id)->first();
		   if($visaType !='' && $visaType !=NULL)
		   { 
			return $visaType->title;
		   }
		   else
		   {
			    return "--";
		   }
	   }
	    public static function getVisaStageName($id = NULL)
	   {
		   $VisaStage = VisaStage::where("id",$id)->first();
		   if($VisaStage !='' && $VisaStage !=NULL)
		   { 
			return $VisaStage->stage_name;
		   }
		   else
		   {
			    return "--";
		   }
	   }
	   public static function getJobOpening($jobOpeningId = NULL)
	   {
		    $documentValues = DocumentCollectionDetails::where("id",$jobOpeningId)->first();
		   if($documentValues !='' && $documentValues !=NULL)
		   { 
			   $job=JobOpening::where("id",$documentValues->job_opening)->first();
			   if($job!=''){
				   $departmentName = Department::where("id",$job->department)->first()->department_name;
				 return $job->name.'-'.$departmentName.'-'.$job->location;  
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
	   public static function getdepartment($jobOpeningId = NULL)
	   {
		    $documentValues = DocumentCollectionDetails::where("id",$jobOpeningId)->first();
		   if($documentValues !='' && $documentValues !=NULL)
		   { 
			   $Department=Department::where("id",$documentValues->department)->first();
			   if($Department!=''){
				 return $Department->department_name;  
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
	public function updateVisaLedgerData(Request $request){
		$rowId=$request->rowId;
		$visatypeLists = Visaprocess::where('id',$rowId)->first();
		return view("VisaLedger/VisaLedgerrFormUpdate",compact('visatypeLists'));
		
	}
	public function updatevisaLedgerDataPost(Request $req)
		{
			
			$inputData = $req->input();
			$id=$inputData['rowId'];
			$ObjData = Visaprocess::find($id);
			$ObjData->cost=$inputData['cost'];
			$ObjData->cost_fine=$inputData['cost_fine'];
			$ObjData->save();
			$req->session()->flash('message','Data Saved Successfully.');
            echo "Done";
           
			
		}
		public function updateSalesTeamManagementDataAssignTL(Request $req)
		{
			
			$rowId=$req->rowId;
			$empsessionId=$req->session()->get('EmployeeId');
			$data = Employee::where('id',$empsessionId)->orderBy("id","DESC")->first();
			if($data!=''){
				$empdata=Employee_details::where('emp_id',$data->employee_id)->first();
				if($empdata!=''){
				$empdetails =  Employee_details::find($rowId);
			
				$empdetails->tl_id=$empdata->id;
			
				$empdetails->save();
				}
			}
		
			$req->session()->flash('message','Data Saved Successfully.');
            echo "Done";
			
		}
		public function listingSalesTeamManagementListENBDTL(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			
				$whereraw='';
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 97 || $empsessionId== 123){
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
				$design=Designation::where("tlsm",2)->where("department_id",9)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);
				//echo $finalarray;
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetailsdata = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",9)->where("offline_status",1)->get();
				$reportsCountenbd = Employee_details::whereRaw($whereraw)->whereNotNull('tl_id')->where("dept_id",9)->where("offline_status",1)->where("job_function",2)->get()->count();
					
				}
				else
				{
					$empdetailsdata = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",9)->where("offline_status",1)->get();
					$reportsCountenbd = Employee_details::where("offline_status",1)->whereNotNull('tl_id')->where("dept_id",9)->where("job_function",2)->get()->count();	
									
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			//$empdetails->setPath(config('app.url/listingSalesTeamManagementListENBDTL'));
			$empdetails=array();
			//echo count($empdetailsdata);exit;
			if(count($empdetailsdata)>0){
				
				foreach($empdetailsdata as $_Tldata){
					$tL_salesData = Employee_details::where("tl_id",$_Tldata->id)->where("offline_status",1)->where("job_function",2)->get();
					if($tL_salesData!=''){
					$empdetails[$_Tldata->id]=$tL_salesData;
					}
				}
				
				
			}
			$salestime=MasterPayout::select('sales_time')->where("dept_id",9)->orderBy("sort_order","DESC")->get()->unique('sales_time');
			$deptId=9;
			return view("SalesTeamManagement/listingUpdateTLListENBDTL",compact('empdetails','reportsCountenbd','salestime','deptId'));
		}
		public function listingSalesTeamManagementListdeemTL(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			
				$whereraw='';
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 97 || $empsessionId== 123){
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
				$design=Designation::where("tlsm",2)->where("department_id",8)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);
				//echo $finalarray;
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetailsdata = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",8)->where("offline_status",1)->get();
				$reportsCountdeem = Employee_details::whereRaw($whereraw)->whereNotNull('tl_id')->where("dept_id",8)->where("offline_status",1)->where("job_function",2)->get()->count();
					
				}
				else
				{
					$empdetailsdata = Employee_details::orderBy("id","DESC")->where("dept_id",8)->whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->get();
					$reportsCountdeem = Employee_details::where("offline_status",1)->whereNotNull('tl_id')->where("dept_id",8)->where("job_function",2)->get()->count();	
					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails=array();
			//print_r($empdetailsdata);
			//echo count($empdetailsdata);exit;
			if(count($empdetailsdata)>0){
				
				foreach($empdetailsdata as $_Tldata){
					$tL_salesData = Employee_details::where("tl_id",$_Tldata->id)->where("offline_status",1)->where("job_function",2)->get();
					if($tL_salesData!=''){
					$empdetails[$_Tldata->id]=$tL_salesData;
					}
				}
				
				
			}
			$salestime=MasterPayout::select('sales_time')->where("dept_id",8)->orderBy("sort_order","DESC")->get()->unique('sales_time');
			$deptId=8;
			return view("SalesTeamManagement/listingUpdateTLListdeemTL",compact('empdetails','reportsCountdeem','salestime','deptId'));
		}
		public function listingSalesTeamManagementListmashreqTL(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			
				$whereraw='';
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 97 || $empsessionId== 123){
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
				$design=Designation::where("tlsm",2)->where("department_id",36)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);
			    //echo $finalarray;
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetailsdata = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",36)->where("offline_status",1)->get();
				$reportsCountmashreq = Employee_details::whereRaw($whereraw)->whereNotNull('tl_id')->where("dept_id",36)->where("offline_status",1)->where("job_function",2)->get()->count();
				}
				else
				{
					$empdetailsdata = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",36)->where("offline_status",1)->get();
					$reportsCountmashreq = Employee_details::where("offline_status",1)->whereNotNull('tl_id')->where("dept_id",36)->where("job_function",2)->get()->count();	
					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails=array();
			//echo count($empdetailsdata);exit;
			if(count($empdetailsdata)>0){
				
				foreach($empdetailsdata as $_Tldata){
					$tL_salesData = Employee_details::where("tl_id",$_Tldata->id)->where("offline_status",1)->where("job_function",2)->get();
					if($tL_salesData!=''){
					$empdetails[$_Tldata->id]=$tL_salesData;
					}
				}
				
				
			}
			$salestime=MasterPayout::select('sales_time')->where("dept_id",36)->orderBy("sort_order","DESC")->get()->unique('sales_time');
			$deptId=36;
			return view("SalesTeamManagement/listingUpdateTLListmashreqTL",compact('empdetails','reportsCountmashreq','salestime','deptId'));
		}
		public function listingSalesTeamManagementListaafaqTL(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			
				$whereraw='';
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 97 || $empsessionId== 123){
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
				$design=Designation::where("tlsm",2)->where("department_id",43)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);
				//echo $finalarray;
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetailsdata = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",43)->where("offline_status",1)->get();
				$reportsCountaafaq = Employee_details::whereRaw($whereraw)->whereNotNull('tl_id')->where("dept_id",43)->where("offline_status",1)->where("job_function",2)->get()->count();				
				}
				else
				{
					$empdetailsdata = Employee_details::orderBy("id","DESC")->whereNull('tl_id')->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",43)->where("offline_status",1)->get();
					$reportsCountaafaq = Employee_details::where("offline_status",1)->whereNotNull('tl_id')->where("job_function",2)->where("dept_id",43)->get()->count();	
					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails=array();
			//echo count($empdetailsdata);exit;
			if(count($empdetailsdata)>0){
				
				foreach($empdetailsdata as $_Tldata){
					$tL_salesData = Employee_details::where("tl_id",$_Tldata->id)->where("offline_status",1)->where("job_function",2)->get();
					if($tL_salesData!=''){
					$empdetails[$_Tldata->id]=$tL_salesData;
					}
				}
				
				
			}
			
			return view("SalesTeamManagement/listingUpdateTLListaafaqTL",compact('empdetails','reportsCountaafaq'));
		}
		public function listingSalesTeamManagementListdibTL(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';

				$whereraw='';
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 97 || $empsessionId== 123){
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
				$design=Designation::where("tlsm",2)->where("department_id",46)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);
				//echo $finalarray;
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetailsdata = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",46)->where("offline_status",1)->get();
				$reportsCountdib = Employee_details::whereRaw($whereraw)->whereNotNull('tl_id')->where("dept_id",46)->where("offline_status",1)->where("job_function",2)->get()->count();				
				}
				else
				{
					$empdetailsdata = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",46)->where("offline_status",1)->get();
					$reportsCountdib = Employee_details::where("offline_status",1)->whereNotNull('tl_id')->where("job_function",2)->where("dept_id",46)->get()->count();	
					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails=array();
			//echo count($empdetailsdata);exit;
			if(count($empdetailsdata)>0){
				
				foreach($empdetailsdata as $_Tldata){
					$tL_salesData = Employee_details::where("tl_id",$_Tldata->id)->where("offline_status",1)->where("job_function",2)->get();
					if($tL_salesData!=''){
					$empdetails[$_Tldata->id]=$tL_salesData;
					}
				}
				
				
			}
			$salestime=MasterPayout::select('sales_time')->where("dept_id",8)->orderBy("sort_order","DESC")->get()->unique('sales_time');
			$deptId=8;
			return view("SalesTeamManagement/listingUpdateTLListdibTL",compact('empdetails','reportsCountdib','salestime','deptId'));
		}
		public function listingSalesTeamManagementListscbTL(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			
				$whereraw='';
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 97 || $empsessionId== 123){
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
				$design=Designation::where("tlsm",2)->where("department_id",47)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetailsdata = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",47)->where("offline_status",1)->get();
				$reportsCountscb = Employee_details::whereRaw($whereraw)->whereNotNull('tl_id')->where("dept_id",47)->where("offline_status",1)->where("job_function",2)->get()->count();
				}
				else
				{
					$empdetailsdata = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",47)->where("offline_status",1)->get();
					$reportsCountscb = Employee_details::where("offline_status",1)->whereNotNull('tl_id')->where("dept_id",47)->where("job_function",2)->get()->count();	
									
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails=array();
			//echo count($empdetailsdata);exit;
			if(count($empdetailsdata)>0){
				
				foreach($empdetailsdata as $_Tldata){
					$tL_salesData = Employee_details::where("tl_id",$_Tldata->id)->where("offline_status",1)->where("job_function",2)->get();
					if($tL_salesData!=''){
					$empdetails[$_Tldata->id]=$tL_salesData;
					}
				}
				
				
			}
			$salestime=MasterPayout::select('sales_time')->where("dept_id",47)->orderBy("sort_order","DESC")->get()->unique('sales_time');
			$deptId=47;
			return view("SalesTeamManagement/listingUpdateTLListscbTL",compact('empdetails','reportsCountscb','salestime','deptId'));
		}
		public function listingSalesTeamManagementListcbdTL(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			
				$whereraw='';
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 97 || $empsessionId== 123){
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
				$design=Designation::where("tlsm",2)->where("department_id",49)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetailsdata = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",49)->where("offline_status",1)->get();
				$reportsCountcbd = Employee_details::whereRaw($whereraw)->whereNotNull('tl_id')->where("dept_id",49)->where("offline_status",1)->where("job_function",2)->get()->count();
				}
				else
				{
					$empdetailsdata = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",49)->where("offline_status",1)->get();
					$reportsCountcbd = Employee_details::where("offline_status",1)->whereNotNull('tl_id')->where("dept_id",49)->where("job_function",2)->get()->count();	
										
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails=array();
			//echo count($empdetailsdata);exit;
			if(count($empdetailsdata)>0){
				
				foreach($empdetailsdata as $_Tldata){
					$tL_salesData = Employee_details::where("tl_id",$_Tldata->id)->where("offline_status",1)->where("job_function",2)->get();
					if($tL_salesData!=''){
					$empdetails[$_Tldata->id]=$tL_salesData;
					}
				}
				
				
			}
			$salestime=MasterPayout::select('sales_time')->where("dept_id",49)->orderBy("sort_order","DESC")->get()->unique('sales_time');
			$deptId=49;
			return view("SalesTeamManagement/listingUpdateTLListcbdTL",compact('empdetails','reportsCountcbd','salestime','deptId'));
		}
		public function getEMPDeptIdSalesTeamManagement($currenttl,$tlid){
			$currenttldetails = Employee_details::where("id",$currenttl)->first();
			$tldetails = Employee_details::where("id",$tlid)->first();
			if($currenttldetails !='' && $tldetails!=''){
				$empDPT=$currenttldetails->dept_id;
				$tlDPT=$tldetails->dept_id;
				if($empDPT!=$tlDPT){
				$designationMod = Designation::where("department_id",$tlDPT)->where("status",1)->get();
				
				return view("SalesTeamManagement/designationdropdown",compact('designationMod'));	
				}
			}
			
	
		}
public static function getempName($empid)
			{	
			
			//echo $attributecode;//exit;
			  $attr = Employee_details::where('id',$empid)->first();
			  if($attr != '')
			  {
			  return $attr->first_name.' '.$attr->middle_name.' '.$attr->last_name;
			  }
			  else
			  {
			  return '';
			  }
			}	
		public function getSelectsalesTimeData($salestime,$deptId){
			
			$sales_timedata=$salestime;
				$sales="'".$salestime."'";
				$var='dept_id='.$deptId.' And sales_time='.$sales;
				$tlname=MasterPayout::select('tl_name')->whereRaw($var)->get()->unique('tl_name');
				
				
				$tlnamearray=array();

				$empdetails=array();
				if(count($tlname)>0){
				foreach($tlname as $_tlname){
					$tln=$_tlname['tl_name'];
					$vardata='dept_id='.$deptId.' And sales_time="'.$salestime.'" And tl_name="'.$tln.'"';
					$tlnameempdata=MasterPayout::select('employee_id')->whereRaw($vardata)->get()->unique('employee_id');
					
					if(count($tlnameempdata)>0){
					$empidarray=array();
					foreach($tlnameempdata as $_tlnameempdata){
						$empidarray[]=$_tlnameempdata['employee_id'];
						
					}
				
					}
					//echo count($empidarray)."<br>";//exit;
						$tL_salesData = Employee_details::whereIn("emp_id",$empidarray)->get();
						if($tL_salesData!=''){
						$empdetails[$tln]=$tL_salesData;
						}
					}
					
				
					
				}
				
				
					$reportsCountenbd = Employee_details::whereNotNull('tl_id')->where("dept_id",$deptId)->get()->count();	
				
			
			$salestime=MasterPayout::select('sales_time')->where("dept_id",$deptId)->orderBy("sort_order","DESC")->get()->unique('sales_time');
			
			return view("SalesTeamManagement/listingUpdateTLListSalesTime",compact('deptId','empdetails','reportsCountenbd','salestime','sales_timedata'));
		}
		public static function checkTlSameWithPayout($deptId){
		//$lasted= MasterPayout::orderby("sort_order","DESC")->first();
		//echo $lasted;	
		$payouttl=PayoutTlMapping::where("bank_id",$deptId)->get();
		$payoutarray=array();
		if(count($payouttl)>0){
			
			foreach($payouttl as $_payouttl){
			$payoutarray[]=$_payouttl->tl_id;
			}
		}
		
		$design=Designation::where("tlsm",2)->where("department_id",$deptId)->where("status",1)->get();
		$designarray=array();
		foreach($design as $_design){
			$designarray[]=$_design->id;
		}
		
		$empdetailsdata = Employee_details::whereIn("designation_by_doc_collection",$designarray)->where("dept_id",$deptId)->where("offline_status",1)->get();
		$empdetails=array();
		foreach($empdetailsdata as $_Tldata){
			
			$empdetails[]=$_Tldata->id;
			
		}
		sort($payoutarray);
		sort($empdetails);
		//print_r($payoutarray);
		//print_r($empdetails);exit;
		 //$empdetails=array(1,2,3,4);
		 //$paoutagentarray=array(1,2,3,4);
		  // Check for equality
		  if ($payoutarray == $empdetails)
			  return 1;
		  else
			  return 2;
		}
		
	public function getsalesteammanagementtlData($deptId){
		$payouttl=PayoutTlMapping::where("bank_id",$deptId)->get();
		$payoutarray=array();
		if(count($payouttl)>0){
			
			foreach($payouttl as $_payouttl){
			$payoutarray[]=$_payouttl->tl_id;
			}
		}
		
		$design=Designation::where("tlsm",2)->where("department_id",$deptId)->where("status",1)->get();
		$designarray=array();
		foreach($design as $_design){
			$designarray[]=$_design->id;
		}
		
		$empdetailsdata = Employee_details::whereIn("designation_by_doc_collection",$designarray)->where("dept_id",$deptId)->where("offline_status",1)->get();
		$empdetails=array();
		foreach($empdetailsdata as $_Tldata){
			
			$empdetails[]=$_Tldata->id;
			
		}
		return view("SalesTeamManagement/tldatalist",compact('empdetails','payoutarray'));
	}	
 public static function checkTlSameWithPayoutAgent($tlId,$deptId){
	$lasted= MasterPayout::orderby("sort_order","DESC")->first();
	$lastestMonthSOrtOrder = $lasted->sort_order;//exit;
	$payoutdata=MasterPayout::where("sort_order",$lastestMonthSOrtOrder)->where("dept_id",$deptId)->where("tl_id",$tlId)->get();
	$paoutagentarray=array();
	if(count($payoutdata)>0){
	foreach($payoutdata as $_payoutdata){
			$paoutagentarray[]=$_payoutdata->employee_id;
		}	
	}
	
	$empdetailsdata = Employee_details::where("tl_id",$tlId)->where("dept_id",$deptId)->where("offline_status",1)->get();
		$empdetails=array();
		foreach($empdetailsdata as $_Tldata){
			
			$empdetails[]=$_Tldata->emp_id;
			
		}
		sort($paoutagentarray);
		  sort($empdetails);
		 //$empdetails=array(1,2,3,4);
		 //$paoutagentarray=array(1,2,3,4);
		  // Check for equality
		  //print_r($paoutagentarray);
		  //print_r($empdetails);exit;
		  if ($paoutagentarray == $empdetails)
			  return 1;
		  else
			  return 2;
 }
 public function getsalesteammanagementtlDataAgent($deptId,$tlId){
	$lasted= MasterPayout::orderby("sort_order","DESC")->first();
	$lastestMonthSOrtOrder = $lasted->sort_order;//exit;
	$payoutdata=MasterPayout::where("sort_order",$lastestMonthSOrtOrder)->where("dept_id",$deptId)->where("tl_id",$tlId)->get();
	$paoutagentarray=array();
	if(count($payoutdata)>0){
	foreach($payoutdata as $_payoutdata){
			$paoutagentarray[]=$_payoutdata->employee_id;
		}	
	}
	$design=Designation::where("tlsm",2)->where("department_id",$deptId)->where("status",1)->get();
		$designarray=array();
		foreach($design as $_design){
			$designarray[]=$_design->id;
		}
	$empdetailsdata = Employee_details::where("tl_id",$tlId)->where("dept_id",$deptId)->where("offline_status",1)->get();
		$empdetails=array();
		foreach($empdetailsdata as $_Tldata){
			
			$empdetails[]=$_Tldata->emp_id;
			
		}
		return view("SalesTeamManagement/tldatalistAgent",compact('empdetails','paoutagentarray'));
 }
 public function updateSalesTeamManagementDataOutSideDept(Request $request){
		$rowId=$request->rowId;
		$empLists = Employee_details::where('emp_id',$rowId)->first();



		// Transfer Request added
		$userid = $request->session()->get('EmployeeId');
		$transferRequestData = ChangeDepartmentRequest::where('emp_id',$empLists->emp_id)->orderBy('id', 'desc')->first();

		if($transferRequestData)
		{
			if($transferRequestData->final_status==1)
			{
				$transferRequest = new ChangeDepartmentRequest();
				$transferRequest->emp_id = $empLists->emp_id;
				$transferRequest->created_at = date('Y-m-d H:i:s');
				$transferRequest->request_added_at = date('Y-m-d H:i:s');
				$transferRequest->request_added_by = $userid;			
				$transferRequest->save();
			}
			elseif($transferRequestData->approved_reject_status==2)
			{
				$transferRequest = new ChangeDepartmentRequest();
				$transferRequest->emp_id = $empLists->emp_id;
				$transferRequest->created_at = date('Y-m-d H:i:s');
				$transferRequest->request_added_at = date('Y-m-d H:i:s');
				$transferRequest->request_added_by = $userid;			
				$transferRequest->save();
			}
			else
			{
				return response()->json(['success'=>'This Employee Request Already Raised for Change Department.']);
			}
		}
		else
		{
            $transferRequest = new ChangeDepartmentRequest();
			$transferRequest->emp_id = $empLists->emp_id;
            $transferRequest->created_at = date('Y-m-d H:i:s');
			$transferRequest->request_added_at = date('Y-m-d H:i:s');
			$transferRequest->request_added_by = $userid;			
            $transferRequest->save();
		}

		$transferRequestLogs = new ChangeDepartmentRequestLog();
		$transferRequestLogs->emp_id = $empLists->emp_id;
		$transferRequestLogs->event_at = date('Y-m-d H:i:s');
		$transferRequestLogs->event_by = $userid;
		$transferRequestLogs->event = 1;
		$transferRequestLogs->save(); 
		// Transfer Request added
		 
				 
		$tL_details = Department::where("status",1)->orderBy('id','DESC')->get();
			 
		//$tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();
		return view("SalesTeamManagement/UpdateTLFormUpdatOutside",compact('empLists','tL_details'));
		
	}
public static function getdepartmentdata($deptiId = NULL)
	   {
		    
			   $Department=Department::where("id",$deptiId)->first();
			   if($Department!=''){
				 return $Department->department_name;  
			   }
			   else
			   {
					return "--";
			   }
		   
	   }
public function getEMPDeptIdSalesTeamManagementoutside($currenttl,$tlid){
			
				$designationMod = Designation::where("department_id",$tlid)->where("status",1)->get();
				
				return view("SalesTeamManagement/designationdropdown",compact('designationMod'));	

		}
public function updateSalesTeamManagementDataOutSideDeptPost(Request $req)
		{
			$inputData = $req->input();
		/* 	echo '<pre>';
			print_r($inputData);
			exit; */
			$dept_id=$req->input('dept_id');
			$EMPdetailsdata =  Employee_details::where("id",$req->input('rowId'))->first();
			$Tldetails =  Employee_details::where("dept_id",$dept_id)->first();
			$location=$Tldetails->work_location;
			$tldept=$dept_id;
			$empdetails =  Employee_details::find($req->input('rowId'));			
			$empdetails->tl_id=NULL;
			$empdetails->work_location=$location;
			$empdetails->dept_id=$tldept;
			$empdetails->designation_by_doc_collection=	$req->input('designation');		
			$empdetails->save();
				$logObj = new TLUpdateLog();
				$logObj->empid =$req->input('rowId');
				$logObj->old_dept =$EMPdetailsdata->dept_id;
				$logObj->current_dept =$tldept;
				$logObj->old_design =$EMPdetailsdata->designation_by_doc_collection;
				$logObj->current_design =$req->input('designation');
				$logObj->old_location =$EMPdetailsdata->work_location;
				$logObj->current_location =$location;
				$logObj->created_date =date("Y-m-d");
				$logObj->createdBY =$req->session()->get('EmployeeId');			
				$logObj->title="Sales Team Management";
				$logObj->save();
			
			$empattributesMod = Employee_attribute::where('emp_id',$EMPdetailsdata->emp_id)->get();							
			if(!empty($empattributesMod))
			{
			foreach($empattributesMod as $updatedept){
			$empattributes = Employee_attribute::find($updatedept->id);
			$empattributes->dept_id = $tldept;
			$empattributes->save();
			}
			}
			
			
			$req->session()->flash('message','Data Saved Successfully.');
            echo "Done";
			
		}
		public function listingSalesTeamManagementListeibTL(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			
				$whereraw='';
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 97 || $empsessionId== 123){
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
				$design=Designation::where("tlsm",2)->where("department_id",52)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetailsdata = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",52)->where("offline_status",1)->get();
				$reportsCounteib = Employee_details::whereRaw($whereraw)->whereNotNull('tl_id')->where("dept_id",52)->where("offline_status",1)->where("job_function",2)->get()->count();
				}
				else
				{
					$empdetailsdata = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",52)->where("offline_status",1)->get();
					$reportsCounteib = Employee_details::where("offline_status",1)->whereNotNull('tl_id')->where("dept_id",52)->where("job_function",2)->get()->count();	
										
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails=array();
			//echo count($empdetailsdata);exit;
			if(count($empdetailsdata)>0){
				
				foreach($empdetailsdata as $_Tldata){
					$tL_salesData = Employee_details::where("tl_id",$_Tldata->id)->where("offline_status",1)->where("job_function",2)->get();
					if($tL_salesData!=''){
					$empdetails[$_Tldata->id]=$tL_salesData;
					}
				}
				
				
			}
			$salestime=MasterPayout::select('sales_time')->where("dept_id",52)->orderBy("sort_order","DESC")->get()->unique('sales_time');
			$deptId=52;
			return view("SalesTeamManagement/listingUpdateTLListeibTL",compact('empdetails','reportsCounteib','salestime','deptId'));
		}
		public function listingSalesTeamManagementListhsbcTL(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			
				$whereraw='';
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 97 || $empsessionId== 123){
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
				$design=Designation::where("tlsm",2)->where("department_id",54)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetailsdata = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",54)->where("offline_status",1)->get();
				$reportsCounthsbc = Employee_details::whereRaw($whereraw)->whereNotNull('tl_id')->where("dept_id",54)->where("offline_status",1)->where("job_function",2)->get()->count();
				}
				else
				{
					$empdetailsdata = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",54)->where("offline_status",1)->get();
					$reportsCounthsbc = Employee_details::where("offline_status",1)->whereNotNull('tl_id')->where("dept_id",54)->where("job_function",2)->get()->count();	
										
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails=array();
			//echo count($empdetailsdata);exit;
			if(count($empdetailsdata)>0){
				
				foreach($empdetailsdata as $_Tldata){
					$tL_salesData = Employee_details::where("tl_id",$_Tldata->id)->where("offline_status",1)->where("job_function",2)->get();
					if($tL_salesData!=''){
					$empdetails[$_Tldata->id]=$tL_salesData;
					}
				}
				
				
			}
			$salestime=MasterPayout::select('sales_time')->where("dept_id",54)->orderBy("sort_order","DESC")->get()->unique('sales_time');
			$deptId=54;
			return view("SalesTeamManagement/listingUpdateTLListhsbcTL",compact('empdetails','reportsCounthsbc','salestime','deptId'));
		}
		
}
