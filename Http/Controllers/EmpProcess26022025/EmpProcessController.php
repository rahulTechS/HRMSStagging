<?php
namespace App\Http\Controllers\EmpProcess;

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
use App\Models\EmpProcess\ExportEmployeeStatus;
use App\Models\JobFunction\JobFunction;
use App\Models\Employee\ExportDataLog;
use App\Models\SEPayout\SalaryStruture;
use App\Models\Visa\Visaprocess;
use App\Models\Visa\visaType;
use App\User;




use Codedge\Fpdf\Fpdf\Fpdf;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;

use App\Models\SIF\SifTemplateDetails;
use App\Models\SIF\RandomPadddingSif;


use App\Models\WarningLetter\WarningLetterRequest;


class EmpProcessController extends Controller
{
    
        public function viewEmployeeData($empid=NULL)
	{
		$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','p_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();

		$empDetailsSection2 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','v_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	

		$empDetailsSection3 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','deploy_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empDetailsSection4 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','b_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			$empDetailswarningletter = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','warning_letter')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
					   /* echo "<pre>";
					   print_r($empRequiredDetails);
					  exit;  */
					 $document_collection_id = $empRequiredDetails->document_collection_id;
					  if($document_collection_id != '' && $document_collection_id != NULL)
					  {
			$kycSection5 = DocumentCollectionAttributes::join('kyc_documents', 'kyc_documents.attribute_code', '=', 'document_collection_attributes.id')
              		->where('kyc_documents.document_collection_id',$document_collection_id)
					->where('document_collection_attributes.attribute_area','kyc')
					
					  ->orderBy('document_collection_attributes.sort_order', 'ASC')
					  ->get();
					  }
					  else
					  {
						 $kycSection5 = array(); 
					  } 
					  
			return view("EmpProcess/viewEmployee",compact('empDetails'),compact('empDetailswarningletter','empRequiredDetails','empDetailsSection2','empDetailsSection3','empDetailsSection4','kycSection5'));
	
	}
	
	
	public function viewEmployeeProfile($empid=NULL)
	{
		
		$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','p_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();

		$empDetailsSection2 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','v_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	

		$empDetailsSection3 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','deploy_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empDetailsSection4 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','b_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			$empDetailswarningletter = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','warning_letter')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
					   /* echo "<pre>";
					   print_r($empRequiredDetails);
					  exit;  */
					 $document_collection_id = $empRequiredDetails->document_collection_id;
					  if($document_collection_id != '' && $document_collection_id != NULL)
					  {
			$kycSection5 = DocumentCollectionAttributes::join('kyc_documents', 'kyc_documents.attribute_code', '=', 'document_collection_attributes.id')
              		->where('kyc_documents.document_collection_id',$document_collection_id)
					->where('document_collection_attributes.attribute_area','kyc')
					
					  ->orderBy('document_collection_attributes.sort_order', 'ASC')
					  ->get();
					  }
					  else
					  {
						 $kycSection5 = array(); 
					  } 
					 $emp_detailsPhoto = Employee_details::where("emp_id",$empid)->first();  
			return view("EmpProcessProfile/viewEmployeeProfile",compact('empDetails','emp_detailsPhoto'),compact('empDetailswarningletter','empRequiredDetails','empDetailsSection2','empDetailsSection3','empDetailsSection4','kycSection5'));
	
	}
	
	
		public function EmpProcessList(Request $request)
		{			
			$Designation=Designation::where("status",1)->get();
			$empId=Employee_details::whereNotIn('emp_id', array(102392))->get();
			$EmpName=Employee_details::whereNotIn('emp_id', array(102392))->get();
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
			return view("EmpProcess/EmpList",compact('Designation','empId','EmpName','design','jobfun','recdata'));
			
		}
		public function setOffSetForEMP(Request $request)
			{
				$offset = $request->offset;
				$request->session()->put('offset_emp_filter',$offset);
				 return  redirect('AjaxEmpList');
			}
		public function setDptFilterforemp(Request $request)
		   {
			   //print_r($request);//exit;
			   $request->session()->put('dept_filter_for_emp',$request->dept_id);
			   $request->session()->put('empid_emp_filter_inner_list','All');
				$request->session()->put('fname_emp_filter_inner_list','All');
				$request->session()->put('lname_emp_filter_inner_list','All');
				$request->session()->put('design_emp_filter_inner_list','All');
				$request->session()->put('scode_emp_filter_inner_list','All');
			   return  redirect('AjaxEmpList');
		   }
		   public function ShowprogressbarData($rowId=NULL){
			   //echo $rowId;exit;
		   $emp_details = Employee_details::where("emp_id",$rowId)->first();
		   $array = array();

				$array[] = 'p_d';
				$array[] = 'b_d';
				$array[] = 'c_d';
				$array[] = 'v_d';
				
				$attributescount = Attributes::whereIn("tab_name",$array)->where("status",1)->count();
				$attributecount = Employee_attribute::where("emp_id",$rowId)->count()+4;
				$percentage = round(($attributecount / $attributescount) * 100);
				return view("EmpProcess/UpdateprogressbaarForm",compact('emp_details','percentage'));
		   }
		   public function getjobrolebasedsales($dept_id=NULL){
					$tL_details = Employee_details::where("dept_id",$dept_id)->where("job_role","Sales Executive")->orderBy("id","ASC")->get();
					
					return view("EmpProcess/DropdownForm",compact('tL_details'));
		 }
		 public function updateEmppopupdat(Request $request){
			 
			 $emp_id=$request->input('rowId');
			 $status=$request->input('status');
			 $bank_generated_code=$request->input('bank_generated_code');
			 $addmorekey=$request->input('addmorekey');
			 
			 $addmorevalue=$request->input('addmorevalue');
			 $handover=$request->input('handover');
			
			 
			 if($addmorekey!=''){
				 
			 $i="u". 1;
			 foreach($addmorekey as $data){
			 $keys[] =$i;
			 $i++;
			 }
			  $keys = $keys;
			 $finaldata= array_merge_recursive(
				array_combine($keys, $addmorekey),
				array_combine($keys, $addmorevalue),
				array_combine($keys, $handover)
			);
			 }
			 else{
				 $finaldata='';
			 }
			 $empdetails =Employee_details::where("emp_id",$emp_id)->first();
			 $empdetails->status=$status;
			 $empdetails->save();
			 $dept_id=$empdetails->dept_id;
			 if($finaldata!=''){
				 //print_r($finaldata);
			 foreach($finaldata as $value){
				 //print_r($value);exit;
				
				 $empdata = new Emp_joining_data();
				 $empdata->emp_id=$emp_id;
				 $empdata->keydata=$value[0];
				 $empdata->keyvalue=$value[1];
				 $empdata->handover=$value[2];
				 
				 $empdata->status=1;
				 $empdata->save();
				
			 }
			 }
			 
			 
			$empattributesMod = Employee_attribute::where('emp_id',$emp_id)->where('attribute_code','bank_generated_code')->where('dept_id',$dept_id)->first();							
			if(!empty($empattributesMod))
			{
			$empattributes = Employee_attribute::find($empattributesMod->id);
			$empattributes->attribute_code = 'bank_generated_code';
			$empattributes->attribute_values = $bank_generated_code;
			$empattributes->status = 1;
			$empattributes->emp_id = $emp_id;
			$empattributes->dept_id = $dept_id;
			$empattributes->save();
			}
			else
			{
				$empattributes = new Employee_attribute();
				$empattributes->attribute_code = 'bank_generated_code';
				$empattributes->attribute_values = $bank_generated_code;
				$empattributes->status = 1;
				$empattributes->emp_id = $emp_id;
				$empattributes->dept_id = $dept_id;
				$empattributes->save();
			}
				$response['code'] = '200';
			   $response['message'] = "Data Saved Successfully.";
			   //$response['empid'] = $empIdPadding;
			   
				echo json_encode($response);
			   exit;
			
			
		 }
		 public function PopupEmpdeactivate($rowId=NULL){
			 
			 //$emp_id=$request->input('rowId');
			 $empdetails =Employee_details::where("emp_id",$rowId)->first();
			 $empdetails->status=2;
			 $empdetails->save();
			$response['code'] = '200';
			   $response['message'] = "Data Saved Successfully.";
			   //$response['empid'] = $empIdPadding;
			   
				echo json_encode($response);
			   exit;
			
			
		 }

		 public function getpopupdata($rowId=NULL)
			{	
			$bankcode1 = Employee_attribute::where('emp_id',$rowId)->where('attribute_code','bank_generated_code')->first();
			
			if(!empty($bankcode1)){
				//echo "hi1";
				$bankcode=$bankcode1->attribute_values;
			}
			else{
				$bankcode=' ';
				//echo "hello";
			}			
			
			//print_r($bankcode1);exit;
			$empdetails =Employee_details::where("emp_id",$rowId)->first();
			$empdata =Emp_joining_data::where("emp_id",$rowId)->where("status",1)->get();
			return view("EmpProcess/PopupForm",compact('empdata','rowId','bankcode','empdetails'));
			}
			public function deletepopupdata($rowId=NULL)
			{	
			$divisonObj = Emp_joining_data::find($rowId);
			$divisonObj->status = 2;
            $divisonObj->save();
			$response['code'] = '200';
		   $response['message'] = "Data Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
			echo json_encode($response);
		   exit;
			//$req->session()->flash('message','Data Deleted Successfully.');
            //return redirect('EmpProcess/PopupForm');
			
			//return view("EmpProcess/PopupForm",compact('empdata','rowId'));
			}
		 public function getUpdatejobrolebasedsales($dept_id=NULL,$emp_id=NULL){
			 //echo $dept_id;exit;
					$tL_details = Employee_details::where("dept_id",$dept_id)->where("job_role","Sales Executive")->orderBy("id","ASC")->get();
					$emp = Employee_details::where("emp_id",$emp_id)->first();
					
					return view("EmpProcess/UpdateDropdownForm",compact('tL_details','emp'));
		 }
		 public function filterByEmpid(Request $request)
		{
			$appid = $request->empid;
			$request->session()->put('empid_emp_filter_inner_list',$appid);
			 //return  redirect('AjaxEmpList');	
		}
		public function setFilterbyFName(Request $request)
		{
			$fname = $request->fname;
			$request->session()->put('fname_emp_filter_inner_list',$fname);
			 //return  redirect('AjaxEmpList');	
		}
		public function filterByVintageEMP(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_emp_filter_inner_list',$vintage);
			//return  redirect('AjaxEmpList');	
		}
		public function filterByLocationEMP(Request $request)
		{
			$location = $request->location;
			$request->session()->put('location_emp_filter_inner_list',$location);
			//return  redirect('AjaxEmpList');	
		}
		public function filterByVisaUnderCompany(Request $request)
		{
			$visacompany = $request->visacompany;
			$request->session()->put('visacompany_emp_filter_inner_list',$visacompany);
			//return  redirect('AjaxEmpList');	
		}
		public function setFilterbyLName(Request $request)
		{
			$lname = $request->lname;
			$request->session()->put('lname_emp_filter_inner_list',$lname);
			 //return  redirect('AjaxEmpList');	
		}
		public function setFilterbyDesignation(Request $request)
		{
			$design = $request->design;
			$request->session()->put('design_emp_filter_inner_list',$design);
			 //return  redirect('AjaxEmpList');	
		}
		public function setFilterbySourceCode(Request $request)
		{
			$scode = $request->scode;
			$request->session()->put('scode_emp_filter_inner_list',$scode);
			 //return  redirect('AjaxEmpList');	
		}
		public function AjaxEmpList(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('offset_emp_filter')))
				{
					$paginationValue = $request->session()->get('offset_emp_filter');
					/*if($paginationValue>100){
					$paginationValue = 10;
					}
					else{
					$paginationValue = $paginationValue;	
					}*/
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
				if(!empty($request->session()->get('empid_emp_filter_inner_list')) && $request->session()->get('empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('fname_emp_filter_inner_list')) && $request->session()->get('fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				
				if(!empty($request->session()->get('lname_emp_filter_inner_list')) && $request->session()->get('lname_emp_filter_inner_list') != 'All')
				{
					$lname = $request->session()->get('lname_emp_filter_inner_list');
					 $selectedFilter['l_name'] = $lname;
					 if($whereraw == '')
					{
						$whereraw = 'last_name like "%'.$lname.'%"';
					}
					else
					{
						$whereraw .= ' And last_name like "%'.$lname.'%"';
					}
				}
				if(!empty($request->session()->get('design_emp_filter_inner_list')) && $request->session()->get('design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('jobfunction_emp_filter_inner_list')) && $request->session()->get('jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('RecruiterName_emp_filter_inner_list')) && $request->session()->get('RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				
				
				
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
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
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					
					 $locationArray = explode(",",$location);
					 $finallocationArray=array();
					 foreach($locationArray as $_locationArray){
						 $finallocationArray[]="'".$_locationArray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $finallocationArray);
					 if($whereraw == '')
					{
						$whereraw = 'work_location IN('.$finalloc.')';
					}
					else
					{
						$whereraw .= ' And work_location IN('.$finalloc.')';
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				
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
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderByRaw("-vintage_days ASC")->whereRaw($whereraw)->where("offline_status",1)->whereNotIn('emp_id', array(102392))->paginate($paginationValue);
				$reportsCount = Employee_details::whereRaw($whereraw)->where("offline_status",1)->whereNotIn('emp_id', array(102392))->get()->count();
					$activeCount = Employee_details::whereRaw($whereraw)->where("offline_status",1)->where('status',1)->whereNotIn('emp_id', array(102392))->get()->count();
					$inactiveCount = Employee_details::whereRaw($whereraw)->where("offline_status",1)->where('status',2)->whereNotIn('emp_id', array(102392))->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::orderByRaw("-vintage_days DESC")->where("offline_status",1)->whereNotIn('emp_id', array(102392))->paginate($paginationValue);
					$reportsCount = Employee_details::where("offline_status",1)->whereNotIn('emp_id', array(102392))->get()->count();	
					$activeCount = Employee_details::where('status',1)->where("offline_status",1)->whereNotIn('emp_id', array(102392))->get()->count();
					$inactiveCount = Employee_details::where('status',2)->where("offline_status",1)->whereNotIn('emp_id', array(102392))->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/AjaxEmpList'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));
			$exportemployeestatus=ExportEmployeeStatus::where('id',1)->first();
			
			return view("EmpProcess/AjaxEmpListall",compact('empdetails','paginationValue','departmentLists','deptID','reportsCount','activeCount','inactiveCount','exportemployeestatus'));
		}
		
		public function addEmpProcess(Request $request)
		{
			//echo "hello";exit;
			$departmentMod = Department::where("status",1)->orderBy("id",'DESC')->get();
			
			return view("EmpProcess/addempProcess",compact('departmentMod'));
		}
		 
		public function PersonalDetailForm(Request $request)
			{
				$cList = WpCountries::get();
				$design = Attributes::where("attribute_code","DESIGN")->get();
				$attributesDetailspd = Attributes::where("department_id",'All')->where("status",1)->where(["parent_attribute"=>0])->where("tab_name","p_d")->orderBy("sort_order","ASC")->get();		
				
				return view("EmpProcess/PersonalDetailForm",compact( 'attributesDetailspd', 'design','cList'));
			}
			public function VisaDocumentDetailsForm($empid=NULL)
			{
				$attributesDetailsvd = Attributes::where("department_id",'All')->where("status",1)->where(["parent_attribute"=>0])->where("tab_name","v_d")->orderBy("sort_order","ASC")->get();							
				return view("EmpProcess/VisaDocumentDetailsForm",compact( 'attributesDetailsvd','empid'));
			}
			public function CompanyDetailsFormForm($empid=NULL)
			{
				$attributesDetailscd = Attributes::where("department_id",'All')->where("status",1)->where(["parent_attribute"=>0])->where("tab_name","c_d")->orderBy("sort_order","ASC")->get();							
				return view("EmpProcess/CompanyDetailsForm",compact( 'attributesDetailscd','empid'));
			}
			public function BankDetailsFormForm($empid=NULL)
			{
				$attributesDetailsbd = Attributes::where("department_id",'All')->where("status",1)->where(["parent_attribute"=>0])->where("tab_name","b_d")->orderBy("sort_order","ASC")->get();	
				return view("EmpProcess/BankDetailsForm",compact( 'attributesDetailsbd','empid'));
			}
			public static function getlocalMobileNo($empid,$attributecode){
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
			
			public static function getTimeFromJoining($empid)
			{
				//echo $empid;
				$empId = Employee_details::where("id",$empid)->first()->emp_id;
				$empDOJObj  = Employee_attribute::where("attribute_code","DOJ")->where('emp_id',$empId)->first();
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
		
		public function updateEmpProfileimg(Request $req){
			
					$inputData = $req->input();
					
					$num = $req->input('empid');
					$empdetails =Employee_details::where("emp_id",$num)->first();
					
					$empId = $empdetails->emp_id;
					$dept_id=$empdetails->dept_id;

					$empIdPadding = $empId;
					$keys = array_keys($_FILES);
					
					$filesAttributeInfo = array();
					$listOfAttribute = array();
					$fileIndex = 0;
					foreach($keys as $key)
					{
						
						if(!empty($req->file($key)))
						{
						$filenameWithExt = $req->file($key)->getClientOriginalName ();
						$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
						$fileExtension =$req->file($key)->getClientOriginalExtension();
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
						$extension = $req->file($key)->getClientOriginalExtension();
						// Filename To store
						$fileNameToStore = $filename. '_'. time().'.'.$extension;
						
						
						$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
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


					
					$attributesValues = $req->input();	
					unset($attributesValues['_token']);
					unset($attributesValues['file_source']);
					unset($attributesValues['onboarding_status']);
					unset($attributesValues['first_name']);
					unset($attributesValues['middle_name']);
					unset($attributesValues['last_name']);
					unset($attributesValues['_url']);
					unset($attributesValues['empid']);
					unset($attributesValues['country']);
					unset($attributesValues['city']);
					foreach($attributesValues as $key=>$value)
			
				
				/*echo "<pre>";
				print_r($attributesValues);
				print_r($value);
			print_r($key);exit;*/
				
			foreach($keys as $key)
			{
			if(in_array($key,$listOfAttribute))
				{
				if($filesAttributeInfo[$key] != '')
					{
						$dpid = $dept_id;
							$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
						$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$attributes_code)
												->where('dept_id',$dept_id)
												->first();
						if(!empty($empattributesMod)){
							$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $attributes_code;
							$empattributes->attribute_values = $filesAttributeInfo[$key];
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
						}
						else{						
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
						}
					}
				}
			}
					
								$response['code'] = '200';
							   $response['message'] = "Data Saved Successfully.";
							   $response['empid'] = $empIdPadding;
							   
								echo json_encode($response);
							   exit;
			
		}
		public function saveEmpPersonalDetail(Request $req)
		{			
			$inputData = $req->input();	
			if($req->input('city')==''){
				$work_clocation=$req->input('work_location');
			}
			else{
			$work_clocation=$req->input('city');
			}
			$inputData['work_location']=$work_clocation;
			//echo $work_clocation;
			//echo "<pre>";
			//print_r($inputData);exit;
			$emplid = 10001;
			$empdetails = new Employee_details();
			$maxempid = Employee_details::max('emp_id');
			if($maxempid=='')
			{
				$num = $emplid;
			}
			else{
				$num = $maxempid+1;
			}
			$empdetails->emp_id=$num;
			$empdetails->dept_id=$req->input('file_source');
			$empdetails->onboarding_status=1;
			$empdetails->first_name=$req->input('first_name');
			$empdetails->middle_name=$req->input('middle_name');
			$empdetails->last_name=$req->input('last_name');
			$empdetails->location=$req->input('work_location');
			$empdetails->job_role=$req->input('DESIGN');
			$empdetails->tl_id=$req->input('teamlead');
			$empdetails->country=$req->input('country');
			$empdetails->work_location=$work_clocation;
			$empdetails->status=1;
			$empdetails->save();
			$LastInsertEmpId = $empdetails->emp_id;
			$keys = array_keys($_FILES);
			//print_r($keys);exit;
			//$keys = array_keys($_FILES);
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				
				if(!empty($req->file($key)))
				{
				$filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
				$vKey = $key;
				$newFileName = $key.'-'.$num.'.'.$fileExtension;
				
				/*
				$fileExtension =$request->file($key)->getClientOriginalExtension();
				$vKey = $key;
				 $newFileName = $key.'-'.$num.'.'.$fileExtension;
				*Updating File Name
				*/
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
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
//print_r($filesAttributeInfo['37']);exit;
		   /*  echo '<pre>';
			print_r($filesAttributeInfo);
			echo "==================";
			print_r($listOfAttribute);
			exit; */
			
			$attributesValues = $req->input();	
			
			/*unset($attributesValues['37']);
			unset($attributesValues['78']);
			unset($attributesValues['79']);
			unset($attributesValues['80']);
			unset($attributesValues['83']);
			unset($attributesValues['84']);
			unset($attributesValues['113']);
			unset($attributesValues['116']);
			unset($attributesValues['117']);*/
			unset($attributesValues['city']);
			unset($attributesValues['_token']);
			unset($attributesValues['file_source']);
			unset($attributesValues['onboarding_status']);
			unset($attributesValues['first_name']);
			unset($attributesValues['middle_name']);
			unset($attributesValues['last_name']);
			unset($attributesValues['_url']);
			unset($attributesValues['teamlead']);
			unset($attributesValues['country']);
			
			
			
			foreach($attributesValues as $key=>$value)
			{
				
				/*echo "<pre>";
				print_r($attributesValues);
				print_r($value);
			print_r($key);exit;*/
				if(in_array($key,$listOfAttribute))
				{
				
					if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $LastInsertEmpId;
						$empattributes->dept_id = $req->input('file_source');
						$empattributes->save();
					}
					
				}
				else{
				 if(!empty($value))
					{
						if($value != 'undefined')
				{
					$empattributes = new Employee_attribute();
					$empattributes->attribute_code = $key;
					$empattributes->attribute_values = $value;
					$empattributes->status = 1;
					$empattributes->emp_id = $LastInsertEmpId;
					$empattributes->dept_id = $req->input('file_source');
					$empattributes->save();
					} 
				}
				}
				
			}
			foreach($keys as $key)
			{
			if(in_array($key,$listOfAttribute))
				{
				if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $LastInsertEmpId;
						$empattributes->dept_id = $req->input('file_source');
						$empattributes->save();
					}
				}
			}


					$response['code'] = '200';
					   $response['message'] = "Data Saved Successfully.";
					   $response['savedid'] = $LastInsertEmpId;
					   
			echo json_encode($response);
					   exit;
            
			//echo "DAta Saved";
			//exit;
		}
		public function saveEmpVisaDocumentDetails(Request $req)
		{
			$inputData = $req->input();
			$num = $req->input('empid');
			$empData =Employee_details::where("emp_id",$num)->first();
			$empId = $empData->emp_id;
			$dept_id=$empData->dept_id;

			$empIdPadding = $empId;
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				
				if(!empty($req->file($key)))
				{
				$filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
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
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
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


			
			$attributesValues = $req->input();	
			
			unset($attributesValues['_token']);
			unset($attributesValues['dept_id']);
			unset($attributesValues['onboarding_status']);
			unset($attributesValues['first_name']);
			unset($attributesValues['middle_name']);
			unset($attributesValues['last_name']);
			unset($attributesValues['_url']);
			unset($attributesValues['id']);
			unset($attributesValues['empid']);
			
			foreach($attributesValues as $key=>$value)
			{
				
				/*echo "<pre>";
				print_r($attributesValues);
				print_r($value);
			print_r($key);exit;*/
				if(in_array($key,$listOfAttribute))
				{
				
					if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
					}
					
				}
				else{
				 if(!empty($value))
					{
						if($value != 'undefined')
				{
					$empattributes = new Employee_attribute();
					$empattributes->attribute_code = $key;
					$empattributes->attribute_values = $value;
					$empattributes->status = 1;
					$empattributes->emp_id = $empId;
					$empattributes->dept_id = $dept_id;
					$empattributes->save();
					} 
				}
				}
				
			}
			foreach($keys as $key)
			{
			if(in_array($key,$listOfAttribute))
				{
				if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
					}
				}
			}
			
						$response['code'] = '200';
					   $response['message'] = "Data Saved Successfully.";
					   $response['empid'] = $empId;
					   
						echo json_encode($response);
					   exit;
		}
		
		public function saveEmpCompanyDetails(Request $req)
		{
			$inputData = $req->input();
			$num = $req->input('empid');
			$empData =Employee_details::where("emp_id",$num)->first();
			$empData->job_role=$req->input('DESIGN');
			$empData->source_code=$req->input('bank_generated_code');
			$empData->basic_salary=$req->input('basic_salary_mol');
			
			$empData->gross_mol=$req->input('total_gross_salary');
			$empData->others_mol=$req->input('others_mol');
			$empData->actual_salary=$req->input('actual_salary');
			$empData->save();
			$empId = $empData->emp_id;
			$dept_id=$empData->dept_id;

			$empIdPadding = $empId;
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				
				if(!empty($req->file($key)))
				{
				$filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
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
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
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


			
			$attributesValues = $req->input();
			//echo "<pre>";
            //print_r($attributesValues);exit;			
			
			unset($attributesValues['_token']);
			unset($attributesValues['dept_id']);
			unset($attributesValues['onboarding_status']);
			unset($attributesValues['first_name']);
			unset($attributesValues['middle_name']);
			unset($attributesValues['last_name']);
			unset($attributesValues['_url']);
			unset($attributesValues['id']);
			unset($attributesValues['empid']);
			
			
			
			foreach($attributesValues as $key=>$value)
			{
				
				/*echo "<pre>";
				print_r($attributesValues);
				print_r($value);
			print_r($key);exit;*/
				if(in_array($key,$listOfAttribute))
				{
				
					if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
					}
					
				}
				else{
				 if(!empty($value))
					{
						if($value != 'undefined')
				{
					$empattributes = new Employee_attribute();
					$empattributes->attribute_code = $key;
					$empattributes->attribute_values = $value;
					$empattributes->status = 1;
					$empattributes->emp_id = $empId;
					$empattributes->dept_id = $dept_id;
					$empattributes->save();
					} 
				}
				}
				
			}
			foreach($keys as $key)
			{
			if(in_array($key,$listOfAttribute))
				{
				if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
					}
				}
			}
			
			
			
			
			
						$response['code'] = '200';
					   $response['message'] = "Data Saved Successfully.";
					   $response['empid'] = $empId;
					   
						echo json_encode($response);
					   exit;
		}
		public function saveEmpBankDetails(Request $req)
		{
			$inputData = $req->input();
			$num = $req->input('empid');
			$empData =Employee_details::where("emp_id",$num)->first();
			$empId = $empData->emp_id;
			$dept_id=$empData->dept_id;

			$empIdPadding = $empId;
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				
				if(!empty($req->file($key)))
				{
				$filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
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
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
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


			
			$attributesValues = $req->input();
			//echo "<pre>";
            //print_r($attributesValues);exit;			
			
			unset($attributesValues['_token']);
			unset($attributesValues['dept_id']);
			unset($attributesValues['onboarding_status']);
			unset($attributesValues['first_name']);
			unset($attributesValues['middle_name']);
			unset($attributesValues['last_name']);
			unset($attributesValues['_url']);
			unset($attributesValues['id']);
			unset($attributesValues['empid']);
			
			
			
			foreach($attributesValues as $key=>$value)
			{
				
				/*echo "<pre>";
				print_r($attributesValues);
				print_r($value);
			print_r($key);exit;*/
				if(in_array($key,$listOfAttribute))
				{
				
					if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
					}
					
				}
				else{
				 if(!empty($value))
					{
						if($value != 'undefined')
				{
					$empattributes = new Employee_attribute();
					$empattributes->attribute_code = $key;
					$empattributes->attribute_values = $value;
					$empattributes->status = 1;
					$empattributes->emp_id = $empId;
					$empattributes->dept_id = $dept_id;
					$empattributes->save();
					} 
				}
				}
				
			}
			foreach($keys as $key)
			{
			if(in_array($key,$listOfAttribute))
				{
				if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
					}
				}
			}
			
			

		
						$req->session()->flash('message','Data Updated Successfully.');
						//return redirect('EmpProcessList');
						$response['code'] = '200';
					   $response['message'] = "Data Saved Successfully.";
					   //$response['empid'] = $empIdPadding;
					   
					echo json_encode($response);
					   exit;
		}
		public function UpdateEMPPersonalData(Request $request)
		   {
			    $empid = $request->empid;
				$cList = WpCountries::get();
				$array = array();

				$array[] = 'p_d';
				$array[] = 'b_d';
				$array[] = 'c_d';
				$array[] = 'v_d';
				$uploadDetails=array();
				$attributescount = Attributes::whereIn("tab_name",$array)->where("status",1)->count();
				$attributecount = Employee_attribute::where("emp_id",$empid)->count()+4;
				$percentage = round(($attributecount / $attributescount) * 100);
				
				$attribute = Employee_attribute::where("emp_id",$empid)->get();
				$emp_details = Employee_details::where("emp_id",$empid)->first();
				$attributesDetailspd = Attributes::where("department_id",'All')->where(["parent_attribute"=>0])->where("tab_name","p_d")->where("status",1)->orderBy("sort_order","ASC")->get();		
				$design = Attributes::where("attribute_code","DESIGN")->get();
				$attributesDetailsvd = Attributes::where("department_id",'All')->where(["parent_attribute"=>0])->where("tab_name","v_d")->where("status",1)->orderBy("sort_order","ASC")->get();		
				$attributesDetailscd = Attributes::where("department_id",'All')->where(["parent_attribute"=>0])->where("tab_name","c_d")->where("status",1)->orderBy("sort_order","ASC")->get();		
				$attributesDetailsbd = Attributes::where("department_id",'All')->where(["parent_attribute"=>0])->where("tab_name","b_d")->where("status",1)->orderBy("sort_order","ASC")->get();		
				$attributesDetailshiring = Attributes::where("department_id",'All')->where(["parent_attribute"=>0])->where("tab_name","hiring_d")->where("status",1)->orderBy("sort_order","ASC")->get();		
				$attributesDetailsdeploy = Attributes::where("department_id",'All')->where(["parent_attribute"=>0])->where("tab_name","deploy_d")->where("status",1)->orderBy("sort_order","ASC")->get();		
				$bgverification = DocumentCollectionAttributes::where("attribute_area","bgverification")->where("status",1)->orderBy("sort_order","ASC")->get();
				$attributesDetailswarningletter1 = Attributes::where("department_id",'All')->where(["parent_attribute"=>0])->where("tab_name","warning_letter1")->where("status",1)->orderBy("sort_order","ASC")->get();
				$attributesDetailswarningletter2 = Attributes::where("department_id",'All')->where(["parent_attribute"=>0])->where("tab_name","warning_letter2")->where("status",1)->orderBy("sort_order","ASC")->get();
				$attributesDetailswarningletter3 = Attributes::where("department_id",'All')->where(["parent_attribute"=>0])->where("tab_name","warning_letter3")->where("status",1)->orderBy("sort_order","ASC")->get();
				$documentid=$emp_details->document_collection_id;
				$documentDetails = DocumentCollectionDetails::where("id",$documentid)->first();
				$documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$documentid)->get();
			   foreach($documentAttributesDetails as $_documentCUpload)
			   {
				   if($_documentCUpload->attribute_value != 'undefined')
				   {
				   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
				   }
			   }
				/*
				*get Divison
				*/
				$departmentList = Department::where("status",1)->get();
				
				$DivisonList = Divison::where("status",1)->get();
				
				
				$divisonId = Department::where("id",$emp_details->dept_id)->first();
				/*
				*get Divison
				*/
				$tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();
				
				return view("EmpProcess/UpdatePersonalDetailForm",compact('tL_details','attributesDetailswarningletter1','attributesDetailswarningletter2','attributesDetailswarningletter3','uploadDetails','documentDetails','bgverification','attributesDetailsbd','attributesDetailscd','attributesDetailspd','attributesDetailshiring','attributesDetailsdeploy','attributesDetailsvd','emp_details','attribute','design','cList','percentage','divisonId','DivisonList','departmentList'));
		   }
		   
		   public static function getAttributeValue($empid,$attributecode)
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
			public static function getpopupdataforupdate($empid)
			{	
			echo $empid;exit;
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
			public function updateEmpdata(Request $req)
				{
					//print_r($_FILES);exit;
					//print_r($req->input());exit;
					$inputData = $req->input();
					if($req->input('city')==''){
					$work_clocation=$req->input('work_location');
					}
					else{
					$work_clocation=$req->input('city');
					}
					$inputData['work_location']=$work_clocation;
					
					//$teamlead=$req->input('teamlead');
					//echo "<pre>";
					//print_r($inputData);exit;
					$num = $req->input('empid');
					$empdetails =Employee_details::where("emp_id",$num)->first();
					
					//$empdetails =  Employee_details::find($req->input('empid'));
					//print_r($empdetails);exit;
					$empdetails->onboarding_status=$req->input('onboarding_status');
					$empdetails->first_name=$req->input('first_name');
					$empdetails->middle_name=$req->input('middle_name');
					$empdetails->last_name=$req->input('last_name');
					$empdetails->location=$work_clocation;
					$empdetails->country=$req->input('country');
					$empdetails->work_location=$work_clocation;
					$empdetails->status=1;
					$empdetails->save();
					
					
					
					$empId = $empdetails->emp_id;
					$dept_id=$empdetails->dept_id;

					$empIdPadding = $empId;
					$keys = array_keys($_FILES);
					
					$filesAttributeInfo = array();
					$listOfAttribute = array();
					$fileIndex = 0;
					foreach($keys as $key)
					{
						
						if(!empty($req->file($key)))
						{
						$filenameWithExt = $req->file($key)->getClientOriginalName ();
						$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
						$fileExtension =$req->file($key)->getClientOriginalExtension();
						$vKey = $key;
						$newFileName = $key.'-'.$num."-".time().'.'.$fileExtension;
						//echo $newFileName;exit;
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
						$extension = $req->file($key)->getClientOriginalExtension();
						// Filename To store
						$fileNameToStore = $filename. '_'. time().'.'.$extension;
						
						
						$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
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


					
					$attributesValues = $req->input();	
					unset($attributesValues['_token']);
					unset($attributesValues['file_source']);
					unset($attributesValues['onboarding_status']);
					unset($attributesValues['first_name']);
					unset($attributesValues['middle_name']);
					unset($attributesValues['last_name']);
					unset($attributesValues['_url']);
					unset($attributesValues['empid']);
					unset($attributesValues['country']);
					unset($attributesValues['city']);
					unset($attributesValues['teamlead']);
					
					foreach($attributesValues as $key=>$value)
			{
				
				/*echo "<pre>";
				print_r($attributesValues);
				print_r($value);
			print_r($key);exit;*/
			//print_r($listOfAttribute);exit;
				if(in_array($key,$listOfAttribute))
				{
				
					if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
					
						$dpid = $dept_id;
							$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$attributes_code)
												->where('dept_id',$dept_id)
												->first();
												
							if(!empty($empattributesMod))
							{					
							$empattributes = Employee_attribute::find($empattributesMod->id);
							}
							else
							{
								$empattributes = new Employee_attribute();
							}
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
					}
					
				}
				else{
				 if(!empty($value))
					{
					if($value != 'undefined')
				{
					
							$dpid = $dept_id;
							$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$key)
												->where('dept_id',$dept_id)
												->first();
												
							
							if(!empty($empattributesMod))
							{
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $key;
							$empattributes->attribute_values = $value;
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
							}
							else
							{
								$empattributes = new Employee_attribute();
								$empattributes->attribute_code = $key;
								$empattributes->attribute_values = $attributesValues[$key];
								$empattributes->status = 1;
								$empattributes->emp_id = $empIdPadding;
								$empattributes->dept_id = $dept_id;
								$empattributes->save();
							}
				} 
				}
				}
				
			}
			foreach($keys as $key)
			{
			if(in_array($key,$listOfAttribute))
				{
				if($filesAttributeInfo[$key] != '')
					{
						$dpid = $dept_id;
							$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
						$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$attributes_code)
												->where('dept_id',$dept_id)
												->first();
						if(!empty($empattributesMod)){
							$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $attributes_code;
							$empattributes->attribute_values = $filesAttributeInfo[$key];
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
						}
						else{						
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
						}
					}
				}
			}
					
					
					
					

					
					
					
								$response['code'] = '200';
							   $response['message'] = "Data Saved Successfully.";
							   $response['empid'] = $empIdPadding;
							   
								echo json_encode($response);
							   exit;
				}
		public function UpdateVisaDocumentDetailsForm(Request $request)
		   {
			    $empid = $request->empid;			   
			    $attribute = Employee_attribute::where("emp_id",$empid)->get();
				$emp_details = Employee_details::where("emp_id",$empid)->first();
				$array = array();

				$array[] = 'p_d';
				$array[] = 'b_d';
				$array[] = 'c_d';
				$array[] = 'v_d';
				
				$attributescount = Attributes::whereIn("tab_name",$array)->where("status",1)->count();
				$attributecount = Employee_attribute::where("emp_id",$empid)->count()+4;
				$percentage = round(($attributecount / $attributescount) * 100);
				$attributesDetailsvd = Attributes::where("department_id",'All')->where(["parent_attribute"=>0])->where("tab_name","v_d")->where("status",1)->orderBy("sort_order","ASC")->get();		
				
				return view("EmpProcess/UpdateVisaDocumentDetailsForm",compact('attributesDetailsvd','emp_details','attribute','percentage'));
		   }
		public function updateEmpVisaDocumentDetails(Request $req)
			{
			$inputData = $req->input();
			$num = $req->input('empid');
			$empData =Employee_details::where("emp_id",$num)->first();
			$empId = $empData->emp_id;
			$dept_id=$empData->dept_id;
			$total_gross_salary = Employee_attribute::where('emp_id',$empId)->where('attribute_code','total_gross_salary')->where('dept_id',$dept_id)->first();
			 if($total_gross_salary!=''){
				 //$deptdata=$dpdata->dept_id;
					$logObjbank = new EmpChangeLog();
					$logObjbank->emp_id =$empId;
					$logObjbank->change_attribute_value =$total_gross_salary->attribute_values;
					$logObjbank->change_attribute_name ="Change Total Gross Salary";					
					$logObjbank->createdBY=$req->session()->get('EmployeeId');
					$logObjbank->save();
			 }
			 $visa_type = Employee_attribute::where('emp_id',$empId)->where('attribute_code','visa_type')->where('dept_id',$dept_id)->first();
			 if($visa_type!=''){
				 //$deptdata=$dpdata->dept_id;
					$logObjbank = new EmpChangeLog();
					$logObjbank->emp_id =$empId;
					$logObjbank->change_attribute_value =$visa_type->attribute_values;
					$logObjbank->change_attribute_name ="Visa Type";					
					$logObjbank->createdBY=$req->session()->get('EmployeeId');
					$logObjbank->save();
			 }
			 
			 
			$empIdPadding = $empId;
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				
				if(!empty($req->file($key)))
				{
				$filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
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
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
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


			
			$attributesValues = $req->input();	
			unset($attributesValues['_token']);
			unset($attributesValues['dept_id']);
			unset($attributesValues['onboarding_status']);
			unset($attributesValues['first_name']);
			unset($attributesValues['middle_name']);
			unset($attributesValues['last_name']);
			unset($attributesValues['_url']);
			unset($attributesValues['id']);
			unset($attributesValues['empid']);
			
			
			
								foreach($attributesValues as $key=>$value)
			{
				
				/*echo "<pre>";
				print_r($attributesValues);
				print_r($value);
			print_r($key);exit;*/
				if(in_array($key,$listOfAttribute))
				{
				
					if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
					}
					
				}
				else{
				 if(!empty($value))
					{
					if($value != 'undefined')
				{
							$dpid = $dept_id;
							$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$key)
												->where('dept_id',$dept_id)
												->first();
												
							
							if(!empty($empattributesMod))
							{
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $key;
							$empattributes->attribute_values = $value;
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
							}
							else
							{
								$empattributes = new Employee_attribute();
								$empattributes->attribute_code = $key;
								$empattributes->attribute_values = $attributesValues[$key];
								$empattributes->status = 1;
								$empattributes->emp_id = $empIdPadding;
								$empattributes->dept_id = $dept_id;
								$empattributes->save();
							}
				} 
				}
				}
				
			}
			foreach($keys as $key)
			{
			if(in_array($key,$listOfAttribute))
				{
				if($filesAttributeInfo[$key] != '')
					{
						$dpid = $dept_id;
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$attributes_code)
												->where('dept_id',$dept_id)
												->first();
						if(!empty($empattributesMod)){
							$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $attributes_code;
							$empattributes->attribute_values = $filesAttributeInfo[$key];
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
						}
						else{
						
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
						}
					}
				}
			}


			
			
			
						$response['code'] = '200';
					   $response['message'] = "Data Saved Successfully.";
					   $response['empid'] = $empIdPadding;
					   
						echo json_encode($response);
					   exit;
		}
		
		public function updateEmphiringDocumentDetails(Request $req)
			{
			$inputData = $req->input();
			$num = $req->input('empid');
			$empData =Employee_details::where("emp_id",$num)->first();
			$empId = $empData->emp_id;
			$dept_id=$empData->dept_id;

			$empIdPadding = $empId;
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				
				if(!empty($req->file($key)))
				{
				$filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
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
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
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


			
			$attributesValues = $req->input();	
			unset($attributesValues['_token']);
			unset($attributesValues['dept_id']);
			unset($attributesValues['onboarding_status']);
			unset($attributesValues['first_name']);
			unset($attributesValues['middle_name']);
			unset($attributesValues['last_name']);
			unset($attributesValues['_url']);
			unset($attributesValues['id']);
			unset($attributesValues['empid']);
			
			
			
								foreach($attributesValues as $key=>$value)
			{
				
				/*echo "<pre>";
				print_r($attributesValues);
				print_r($value);
			print_r($key);exit;*/
				if(in_array($key,$listOfAttribute))
				{
				
					if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
					}
					
				}
				else{
				 if(!empty($value))
					{
					if($value != 'undefined')
				{
							$dpid = $dept_id;
							$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$key)
												->where('dept_id',$dept_id)
												->first();
												
							
							if(!empty($empattributesMod))
							{
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $key;
							$empattributes->attribute_values = $value;
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
							}
							else
							{
								$empattributes = new Employee_attribute();
								$empattributes->attribute_code = $key;
								$empattributes->attribute_values = $attributesValues[$key];
								$empattributes->status = 1;
								$empattributes->emp_id = $empIdPadding;
								$empattributes->dept_id = $dept_id;
								$empattributes->save();
							}
				} 
				}
				}
				
			}
			foreach($keys as $key)
			{
			if(in_array($key,$listOfAttribute))
				{
				if($filesAttributeInfo[$key] != '')
					{
						$dpid = $dept_id;
						$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
						$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$attributes_code)
												->where('dept_id',$dept_id)
												->first();
						if(!empty($empattributesMod)){
							$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $attributes_code;
							$empattributes->attribute_values = $filesAttributeInfo[$key];
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
						}
						else{						
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
						}
					}
				}
			}


			
			
			
						$response['code'] = '200';
					   $response['message'] = "Data Saved Successfully.";
					   $response['empid'] = $empIdPadding;
					   
						echo json_encode($response);
					   exit;
		}
		
		
		public function updatefrmdeploydocument(Request $req)
			{
			$inputData = $req->input();
			$num = $req->input('empid');
			$empData =Employee_details::where("emp_id",$num)->first();
			$empId = $empData->emp_id;
			$dept_id=$empData->dept_id;
			$designation=$req->input('designation');
			$teamlead=$req->input('teamlead');
			$atl=$req->input('atl');
			$sourcecode=$req->input('source_code');
			$num = $req->input('empid');
			$work_location=$req->input('work_location');
			$official_email=$req->input('official_email');
			$empdetails =Employee_details::where("emp_id",$num)->first();
			
			 if($empdetails!=''){
				 $tlid = $empdetails->tl_id;
				 $dpdata =Employee_details::where("id",$tlid)->first();
				 if($dpdata!='' || $dpdata!=NULL){
					 $TLname=$dpdata->first_name.' '.$dpdata->middle_name.' '.$dpdata->last_name;
				 }
				 else{
					 $TLname='NA';
				 }
				 
					$logObj = new EmpChangeLog();
					$logObj->emp_id =$num;
					$logObj->change_attribute_value =$TLname;
					$logObj->change_attribute_name ="Previous Team lead";					
					$logObj->createdBY=$req->session()->get('EmployeeId');
					$logObj->save();
			 }
			if($teamlead!=''){
						$dpdatanew =Employee_details::where("id",$teamlead)->first();
						 if($dpdatanew!='' || $dpdatanew!=NULL){
							 $TLnamenew=$dpdatanew->first_name.' '.$dpdatanew->middle_name.' '.$dpdatanew->last_name;
						 }
						 else{
							 $TLnamenew='NA';
						 }
					$logObjnew = new EmpChangeLog();
					$logObjnew->emp_id =$num;
					$logObjnew->change_attribute_value =$TLnamenew;
					$logObjnew->change_attribute_name ="Current Team lead";					
					$logObjnew->createdBY=$req->session()->get('EmployeeId');
					$logObjnew->save();
					}
			
			
							
			$designdata =Employee_details::where("designation_by_doc_collection",$designation)->where("emp_id",$num)->first();
			if(empty($designdata) && $designdata==''){
				if($designation!=''){
				$designationMod = Designation::where("id",$designation)->first();
					if($designationMod != '')
					  {
					  $jobfunction= $designationMod->job_function;
					  $updatejob = Employee_details::where("emp_id",$num)->first();
						
						$updatejob->job_function=$jobfunction;
						$updatejob->save();
					  }
			}
				//echo "hello";exit;
				$data=Employee::where('employee_id',$num)->orderBy("id","DESC")->first();
				if($data!=''){
				$userOBJ=Employee::find($data->id);
				$userOBJ->status=1;
				if($jobfunction==3){
				 $userOBJ->group_id=27;
				}
				if($jobfunction==4){
				 $userOBJ->group_id=28;
				}	
				$userOBJ->save();
				}
				
				if($jobfunction==3 || $jobfunction==4){
					
					$departmentDetails = JobFunctionPermission::where("emp_id",$num)->first();
					if($departmentDetails!=''){
						$jobObj=JobFunctionPermission::find($departmentDetails->id);
						$jobObj->save();
					}
				}
				
			}
			$updateOBJ = Employee_details::where("emp_id",$num)->first();
			$updateOBJ->designation_by_doc_collection=$designation;
			$updateOBJ->work_location=$work_location;
			$updateOBJ->tl_id=$teamlead;
			$updateOBJ->atl_id=$atl;
			$updateOBJ->source_code=$sourcecode;
			$updateOBJ->official_email=$official_email;
			$updateOBJ->save();
			
			$dpdata = Employee_attribute::where('emp_id',$empId)->where('attribute_code','official_email')->where('dept_id',$dept_id)->first();
			 if($dpdata!=''){
				 //$deptdata=$dpdata->dept_id;
					$logObj = new EmpChangeLog();
					$logObj->emp_id =$empId;
					$logObj->change_attribute_value =$dpdata->attribute_values;
					$logObj->change_attribute_name ="official_email";					
					$logObj->createdBY=$req->session()->get('EmployeeId');
					$logObj->save();
			 }
			
			$dpdata = Employee_attribute::where('emp_id',$empId)->where('attribute_code','work_location')->where('dept_id',$dept_id)->first();
			 if($dpdata!=''){
				 //$deptdata=$dpdata->dept_id;
					$logObj = new EmpChangeLog();
					$logObj->emp_id =$empId;
					$logObj->change_attribute_value =$dpdata->attribute_values;
					$logObj->change_attribute_name ="Location";					
					$logObj->createdBY=$req->session()->get('EmployeeId');
					$logObj->save();
			 }
			 $bankcode = Employee_attribute::where('emp_id',$empId)->where('attribute_code','source_code')->where('dept_id',$dept_id)->first();
			 if($bankcode!=''){
				 //$deptdata=$dpdata->dept_id;
					$logObjbank = new EmpChangeLog();
					$logObjbank->emp_id =$empId;
					$logObjbank->change_attribute_value =$bankcode->attribute_values;
					$logObjbank->change_attribute_name ="Bank Code";					
					$logObjbank->createdBY=$req->session()->get('EmployeeId');
					$logObjbank->save();
			 }
			
			
			
			$empIdPadding = $empId;
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				
				if(!empty($req->file($key)))
				{
				$filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
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
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
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


			
			$attributesValues = $req->input();	
			unset($attributesValues['_token']);
			unset($attributesValues['dept_id']);
			unset($attributesValues['designation']);
			
			unset($attributesValues['onboarding_status']);
			unset($attributesValues['first_name']);
			unset($attributesValues['middle_name']);
			unset($attributesValues['last_name']);
			unset($attributesValues['_url']);
			unset($attributesValues['id']);
			unset($attributesValues['empid']);
			
			
			
								foreach($attributesValues as $key=>$value)
			{
				
				/*echo "<pre>";
				print_r($attributesValues);
				print_r($value);
			print_r($key);exit;*/
				if(in_array($key,$listOfAttribute))
				{
				
					if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
					}
					
				}
				else{
				 if(!empty($value))
					{
					if($value != 'undefined')
				{
							$dpid = $dept_id;
							$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$key)
												->where('dept_id',$dept_id)
												->first();
												
							
							if(!empty($empattributesMod))
							{
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $key;
							$empattributes->attribute_values = $value;
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							
							$empattributes->save();
							}
							else
							{
								$empattributes = new Employee_attribute();
								$empattributes->attribute_code = $key;
								$empattributes->attribute_values = $attributesValues[$key];
								$empattributes->status = 1;
								$empattributes->emp_id = $empIdPadding;
								$empattributes->dept_id = $dept_id;
								$empattributes->save();
							}
				} 
				}
				}
				
			}
			foreach($keys as $key)
			{
			if(in_array($key,$listOfAttribute))
				{
				if($filesAttributeInfo[$key] != '')
					{
						$dpid = $dept_id;
						$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
						$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$attributes_code)
												->where('dept_id',$dept_id)
												->first();
						if(!empty($empattributesMod)){
							$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $attributes_code;
							$empattributes->attribute_values = $filesAttributeInfo[$key];
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
						}
						else{						
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
						}
					}
				}
			}


			
			
			
						$response['code'] = '200';
					   $response['message'] = "Data Saved Successfully.";
					   $response['empid'] = $empIdPadding;
					   
						echo json_encode($response);
					   exit;
		}
		public function updateCompanyDetailsFormForm(Request $request)
		   {
			    $empid = $request->empid;			   
			    $attribute = Employee_attribute::where("emp_id",$empid)->get();
				$emp_details = Employee_details::where("emp_id",$empid)->first();
				$array = array();

				$array[] = 'p_d';
				$array[] = 'b_d';
				$array[] = 'c_d';
				$array[] = 'v_d';
				
				$attributescount = Attributes::whereIn("tab_name",$array)->where("status",1)->count();
				$attributecount = Employee_attribute::where("emp_id",$empid)->count()+4;
				$percentage = round(($attributecount / $attributescount) * 100);
				$attributesDetailscd = Attributes::where("department_id",'All')->where(["parent_attribute"=>0])->where("tab_name","c_d")->where("status",1)->orderBy("sort_order","ASC")->get();		
				
				return view("EmpProcess/UpdateCompanyDetailsForm",compact('attributesDetailscd','emp_details','attribute','percentage'));
		   }
		public function updateEmpCompanyDetails(Request $req)
		{
			//print_r($req->input());exit;
			$inputData = $req->input();
			$num = $req->input('empid');
			$empData =Employee_details::where("emp_id",$num)->first();
			$empData->job_role=$req->input('DESIGN');
			$empData->source_code=$req->input('bank_generated_code');
			$basicSalary = $req->input('basic_salary_mol');
			$basicSalary =  str_replace("AED","",$basicSalary);
			$basicSalary =  str_replace(",","",$basicSalary);
			$basicSalary = trim($basicSalary);
			$empData->basic_salary=$basicSalary;
			
			$totalGrossSalary = $req->input('total_gross_salary');
			$totalGrossSalary =  str_replace("AED","",$totalGrossSalary);
			$totalGrossSalary =  str_replace(",","",$totalGrossSalary);
			$totalGrossSalary = trim($totalGrossSalary);
			$empData->gross_mol=$totalGrossSalary;
			
			
			$othersMol = $req->input('others_mol');
			$othersMol =  str_replace("AED","",$othersMol);
			$othersMol =  str_replace(",","",$othersMol);
			$othersMol = trim($othersMol);
			$empData->others_mol=$othersMol;
			
			$actualSalary = $req->input('actual_salary');
			$actualSalary =  str_replace("AED","",$actualSalary);
			$actualSalary =  str_replace(",","",$actualSalary);
			$actualSalary = trim($actualSalary);
			$empData->actual_salary=$actualSalary;
			$empData->save();
			$empId = $empData->emp_id;
			$dept_id=$empData->dept_id;

			$empIdPadding = $empId;
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				
				if(!empty($req->file($key)))
				{
				$filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
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
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
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


			
			$attributesValues = $req->input();
			//echo "<pre>";
            //print_r($attributesValues);exit;			
			unset($attributesValues['_token']);
			unset($attributesValues['dept_id']);
			unset($attributesValues['onboarding_status']);
			unset($attributesValues['first_name']);
			unset($attributesValues['middle_name']);
			unset($attributesValues['last_name']);
			unset($attributesValues['_url']);
			unset($attributesValues['id']);
			unset($attributesValues['empid']);
			
			
			
								foreach($attributesValues as $key=>$value)
			{
				
				/*echo "<pre>";
				print_r($attributesValues);
				print_r($value);
			print_r($key);exit;*/
				if(in_array($key,$listOfAttribute))
				{
				
					if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
					}
					
				}
				else{
				 if(!empty($value))
					{
					if($value != 'undefined')
				{
							$dpid = $dept_id;
							$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$key)
												->where('dept_id',$dept_id)
												->first();
												
							
							if(!empty($empattributesMod))
							{
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $key;
							$empattributes->attribute_values = $value;
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
							}
							else
							{
								$empattributes = new Employee_attribute();
								$empattributes->attribute_code = $key;
								$empattributes->attribute_values = $attributesValues[$key];
								$empattributes->status = 1;
								$empattributes->emp_id = $empIdPadding;
								$empattributes->dept_id = $dept_id;
								$empattributes->save();
							}
				} 
				}
				}
				
			}
			foreach($keys as $key)
			{
			if(in_array($key,$listOfAttribute))
				{
				if($filesAttributeInfo[$key] != '')
					{
						$dpid = $dept_id;
						$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
						$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$attributes_code)
												->where('dept_id',$dept_id)
												->first();
						if(!empty($empattributesMod)){
							$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $attributes_code;
							$empattributes->attribute_values = $filesAttributeInfo[$key];
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
						}
						else{						
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
						}
					}
				}
			}


			
			
			
						$response['code'] = '200';
					   $response['message'] = "Data Saved Successfully.";
					   $response['empid'] = $empIdPadding;
					   
			echo json_encode($response);
					   exit;
		}
		public function updateBankDetailsForm(Request $request)
		   {
			    $empid = $request->empid;			   
			    $attribute = Employee_attribute::where("emp_id",$empid)->get();
				$emp_details = Employee_details::where("emp_id",$empid)->first();
				$array = array();

				$array[] = 'p_d';
				$array[] = 'b_d';
				$array[] = 'c_d';
				$array[] = 'v_d';
				
				$attributescount = Attributes::whereIn("tab_name",$array)->where("status",1)->count();
				$attributecount = Employee_attribute::where("emp_id",$empid)->count()+4;
				$percentage = round(($attributecount / $attributescount) * 100);
				$attributesDetailsbd = Attributes::where("department_id",'All')->where(["parent_attribute"=>0])->where("tab_name","b_d")->where("status",1)->orderBy("sort_order","ASC")->get();		
				
				return view("EmpProcess/UpdateBankDetailsForm",compact('attributesDetailsbd','emp_details','attribute','percentage'));
		   }
		public function updateEmpBankDetails(Request $req)
		{
			$inputData = $req->input();
			$num = $req->input('empid');
			$empData =Employee_details::where("emp_id",$num)->first();
			$empId = $empData->emp_id;
			$dept_id=$empData->dept_id;

			$empIdPadding = $empId;
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				
				if(!empty($req->file($key)))
				{
				$filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
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
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
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


			
			$attributesValues = $req->input();
			//echo "<pre>";
            //print_r($attributesValues['']);exit;			
			unset($attributesValues['_token']);
			unset($attributesValues['dept_id']);
			unset($attributesValues['onboarding_status']);
			unset($attributesValues['first_name']);
			unset($attributesValues['middle_name']);
			unset($attributesValues['last_name']);
			unset($attributesValues['_url']);
			unset($attributesValues['id']);
			unset($attributesValues['emp_id']);
			
			
			
								foreach($attributesValues as $key=>$value)
			{
				
				/*echo "<pre>";
				print_r($attributesValues);
				print_r($value);
			print_r($key);exit;*/
				if(in_array($key,$listOfAttribute))
				{
				
					if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
					}
					
				}
				else{
				 if(!empty($value))
					{
					if($value != 'undefined')
				{
							$dpid = $dept_id;
							$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$key)
												->where('dept_id',$dept_id)
												->first();
												
							
							if(!empty($empattributesMod))
							{
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $key;
							$empattributes->attribute_values = $value;
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
							}
							else
							{
								$empattributes = new Employee_attribute();
								$empattributes->attribute_code = $key;
								$empattributes->attribute_values = $attributesValues[$key];
								$empattributes->status = 1;
								$empattributes->emp_id = $empIdPadding;
								$empattributes->dept_id = $dept_id;
								$empattributes->save();
							}
				} 
				}
				}
				
			}
			foreach($keys as $key)
			{
			if(in_array($key,$listOfAttribute))
				{
				if($filesAttributeInfo[$key] != '')
					{
						$dpid = $dept_id;
						$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
						$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$attributes_code)
												->where('dept_id',$dept_id)
												->first();
						if(!empty($empattributesMod)){
							$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $attributes_code;
							$empattributes->attribute_values = $filesAttributeInfo[$key];
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
						}
						else{						
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
						}
					}
				}
			}

		if($attributesValues['actual_salary']!=''){
			$empData =Employee_details::where("emp_id",$num)->first();
			
			$empOBJ=Employee_details::find($empData->id);
			$empOBJ->actual_salary=$attributesValues['actual_salary'];
			$empOBJ->save();
			
		}
		
						$req->session()->flash('message','Data Updated Successfully.');
						//return redirect('EmpProcessList');
						$response['code'] = '200';
					   $response['message'] = "Data Saved Successfully.";
					   //$response['empid'] = $empIdPadding;
					   
					echo json_encode($response);
					   exit;
		}

		public function exportEmpReport(Request $request){
		$parameters = $request->input(); 
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'emp_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AK1');
			$sheet->setCellValue('A1', 'EMP List - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('First Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Middle Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Last Name'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Bank Code'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Local Contact Number'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Date of Joining'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Designation'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Work Location'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Department'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('TL Name'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Proposed Salary'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Recruiter'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Job Function'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Basic Salary'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Date Of Birth'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Basic Salary MOL'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Others MOL'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('Total Gross Salary (MOL)'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('Target'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('Product'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('Location By attribute'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Complete Name'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('passport number'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('Emirates ID number'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('Visa Number'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('Age'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter, strtoupper('Gender'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AD'.$indexCounter, strtoupper('Marital Status'))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AE'.$indexCounter, strtoupper('Nationality'))->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AF'.$indexCounter, strtoupper('UID no'))->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AG'.$indexCounter, strtoupper('Emirates Visa Issued'))->getStyle('AG'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AH'.$indexCounter, strtoupper('Personal Email ID'))->getStyle('AH'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AI'.$indexCounter, strtoupper('Person Contact number'))->getStyle('AI'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AJ'.$indexCounter, strtoupper('Establishment Name'))->getStyle('AJ'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AK'.$indexCounter, strtoupper('Emirates Application NO'))->getStyle('AK'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				//echo $sid;
				 $misData = Employee_details::where("id",$sid)->first();
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
				 $empattributesMod = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','DOJ')->where('dept_id',$misData->dept_id)->first();
				 if(!empty($empattributesMod)){
				 $doj=date("d-M-Y",strtotime(str_replace("/","-",$empattributesMod->attribute_values)));
				 }
				 else{
					 $doj='';
				 }
				 $empsessionId=$request->session()->get('EmployeeId');
				$jobfunctiondetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				 //echo $jobfunctiondetails->job_function_id;exit;
				 if($jobfunctiondetails!='' && ($jobfunctiondetails->job_function_id==3 || $jobfunctiondetails->job_function_id==4)){
					$LocalContactNumber='';
					$basicSalary='';
				 }
				 else{
					 $CONTACT_NUMBER = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','CONTACT_NUMBER')->where('dept_id',$misData->dept_id)->first();
					 if($CONTACT_NUMBER!=''){
						 if($empsessionId==1 || $empsessionId==61){
					 $LocalContactNumber=$CONTACT_NUMBER->attribute_values;
						 }else{
							$LocalContactNumber=''; 
						 }
					 }
					 else{
						 $LocalContactNumber='';
					 } 
					 $total_gross_salary = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','total_gross_salary')->where('dept_id',$misData->dept_id)->first();
					 if(!empty($total_gross_salary)){
					 $basicSalary=$total_gross_salary->attribute_values;
					 }
					 else{
						 $basicSalary='';
					 }
					 
				 }
				 $basicSalary = $misData->actual_salary;
				 
				 
				 $work_location = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','work_location')->where('dept_id',$misData->dept_id)->first();
				 if(!empty($work_location)){
				 $worklocation_attribute=$work_location->attribute_values;
				 }
				 else{
					 $worklocation_attribute='';
				 }
				 
				 $worklocation=$misData->work_location;
				 
				 $source_code = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','source_code')->where('dept_id',$misData->dept_id)->first();
				 if(!empty($source_code)){
				 $source_val=$source_code->attribute_values;
				 }
				 else{
					 $source_val='';
				 }
				 
				 $designationMod = Designation::where("id",$misData->designation_by_doc_collection)->first();
					if($designationMod != '')
					  {
					  $designation_by_doc_collection= $designationMod->name;
					  
					  }
					  else{
						 $designation_by_doc_collection=''; 
					  }
				 $salary=DocumentCollectionDetails::where("id",$misData->document_collection_id)->first();
				 if($salary!=''){
					$fsalary =$salary->proposed_salary;
				 }
				 else{
					 $fsalary ='';
				 }
				 $Recruiter =RecruiterDetails::where("id",$misData->recruiter)->first();
			  
					  if($Recruiter != '')
					  {
						if($Recruiter->employee_id != '' && $Recruiter->employee_id != NULL)
						{
							$dataEMP  = Employee_details::where("emp_id",$Recruiter->employee_id)->first();
							if($dataEMP  != '')
							{
								$RecruiterDetails= $dataEMP->emp_name;
							}
							else
							{
								$RecruiterDetails= $Recruiter->name;
							}
							
						}
						else
						{
							$RecruiterDetails= $Recruiter->name;
						}
					  }
					  else
					  {
					  $RecruiterDetails= '';
					  }
					  $jobfunDetails = JobFunction::where("id",$misData->job_function)->first();
					   if($jobfunDetails != '')
					   {
							$jobfunction= $jobfunDetails->name;
					   }
					   else
					   {
						  $jobfunction= '';
					   }
				 $EMPDOB = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','EMPDOB')->where('dept_id',$misData->dept_id)->first();
				 if($EMPDOB!=''){
				 $EMPDOB_val=date("d-M-Y",strtotime(str_replace("/","-",$EMPDOB->attribute_values)));
				 $EMPDOB_val_str=date("Y-m-d",strtotime(str_replace("/","-",$EMPDOB->attribute_values)));
				 }
				 else{
					 $EMPDOB_val='';
					 $EMPDOB_val_str='';
				 }
				 
				 
				 
				  $basicSalaryMOL = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','basic_salary_mol')->first();
				 if(!empty($basicSalaryMOL)){
				 $basicSalaryMOLValue=$basicSalaryMOL->attribute_values;
				 }
				 else{
					 $basicSalaryMOLValue='';
				 }
				 
				 
				   $othersMol = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','others_mol')->first();
				 if(!empty($othersMol)){
				 $othersMolValue=$othersMol->attribute_values;
				 }
				 else{
					 $othersMolValue='';
				 }
				 
				 
				  $totalGrossSalaryMol = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','total_gross_salary')->first();
				 if(!empty($totalGrossSalaryMol)){
				 $totalGrossSalaryMolValue=$totalGrossSalaryMol->attribute_values;
				 }
				 else{
					 $totalGrossSalaryMolValue='';
				 }
				 
				 
				  $PP_NO = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','PP_NO')->first();
				 if(!empty($PP_NO)){
				 $PP_NOValue=$PP_NO->attribute_values;
				 }
				 else{
					 $PP_NOValue='';
				 }
				 
				 
				 $emirates_id_no = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','emirates_id_no')->first();
				 if(!empty($emirates_id_no)){
				 $emirates_id_noValue=$emirates_id_no->attribute_values;
				 }
				 else{
					 $emirates_id_noValue='';
				 }
				 $emirates_application_no = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','emirates_application_no')->first();
				 if(!empty($emirates_application_no)){
				 $emirates_application_no=$emirates_application_no->attribute_values;
				 }
				 else{
					 $emirates_application_no='';
				 }
				 
				 $PVISA_NUMBER = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','PVISA_NUMBER')->first();
				 if(!empty($PVISA_NUMBER)){
				 $PVISA_NUMBERValue=$PVISA_NUMBER->attribute_values;
				 }
				 else{
					 $PVISA_NUMBERValue='';
				 }
				 /*
				 *Age
				 */
				 if($EMPDOB_val_str != '' && $EMPDOB_val_str != NULL)
				 {
					 $current_date = date("Y-m-d");
					 $vintageDays = abs(strtotime($current_date)-strtotime($EMPDOB_val_str))/ (60 * 60 * 24);
					 $yearsBirth = round($vintageDays/365);
				 }
				 else
				 {
				  $yearsBirth = ''; 
				 }
				 /*
				 *Age
				 */
				 $GNDR = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','GNDR')->first();
				 if(!empty($GNDR)){
				 $GNDRValue=$GNDR->attribute_values;
				 }
				 else{
					 $GNDRValue='';
				 }
				 
				 
				  $NAT = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','NAT')->first();
				 if(!empty($NAT)){
				 $NATValue=$NAT->attribute_values;
				 }
				 else{
					 $NATValue='';
				 }
				 
				  $visa_uid_no = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','visa_uid_no')->first();
				 if(!empty($visa_uid_no)){
				 $visa_uid_noValue=$visa_uid_no->attribute_values;
				 }
				 else{
					 $visa_uid_noValue='';
				 }
				 
				  $emailMod = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','email')->first();
				 if(!empty($emailMod)){
				 $emailValue=$emailMod->attribute_values;
				 }
				 else{
					 $emailValue='';
				 }
				 /*
				 *get ESTABLISHMENT NAME
				 */
				 $ESTABLISHMENTNAME = '';
				 $dataDoc=Visaprocess::where("document_id",$misData->document_collection_id)->orderBy("id","DESC")->first();
				 if($dataDoc !='')
				 {
					 $visatypeID = $dataDoc->visa_type;
					 $visaTYoeOBj = visaType::where("id",$visatypeID)->first();
					 if($visaTYoeOBj != '')
					 {
						 $ESTABLISHMENTNAME = $visaTYoeOBj->title;
					 }
				 }
				/*
				 *get ESTABLISHMENT NAME
				 */
				 $indexCounter++; 	
				 $departmentMod = Department::where("id",$misData->dept_id)->first();
				 $deptname=$departmentMod->department_name;
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($misData->first_name))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($misData->middle_name))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper($misData->last_name))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $source_val)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $LocalContactNumber)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $doj)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $designation_by_doc_collection)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $worklocation)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $deptname)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('L'.$indexCounter, $tlname)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('M'.$indexCounter, "AED".$fsalary)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $RecruiterDetails)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $jobfunction)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $basicSalary)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $EMPDOB_val)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('R'.$indexCounter, $basicSalaryMOLValue)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('S'.$indexCounter, $othersMolValue)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('T'.$indexCounter, $totalGrossSalaryMolValue)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('U'.$indexCounter, $misData->target)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('V'.$indexCounter, $misData->product)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('W'.$indexCounter, $worklocation_attribute)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('X'.$indexCounter, strtoupper($misData->emp_name))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				
				$sheet->setCellValue('Y'.$indexCounter, strtoupper($PP_NOValue))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('Z'.$indexCounter, strtoupper($emirates_id_noValue))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('AA'.$indexCounter, strtoupper($PVISA_NUMBERValue))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('AB'.$indexCounter, strtoupper($yearsBirth))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('AC'.$indexCounter, strtoupper($GNDRValue))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('AD'.$indexCounter, strtoupper(''))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('AE'.$indexCounter, strtoupper($NATValue))->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('AF'.$indexCounter, strtoupper($visa_uid_noValue))->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('AG'.$indexCounter, strtoupper(''))->getStyle('AG'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('AH'.$indexCounter, strtoupper($emailValue))->getStyle('AH'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('AI'.$indexCounter, strtoupper($LocalContactNumber))->getStyle('AI'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('AJ'.$indexCounter, strtoupper($ESTABLISHMENTNAME))->getStyle('AJ'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('AK'.$indexCounter, $emirates_application_no)->getStyle('AK'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'AK'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:AK1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AK') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="EmpProcess";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
		}






		public function EmpdetailsData($empid=NULL)
		{
			$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();

					//   echo "<pre>";
					//   print_r($empDetails);
					//   exit;
			
			$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
			return view("Employee/Empdetails",compact('empDetails'),compact('empRequiredDetails'));
	

		}
		public function showconditionalhtml($selectedValue,$attribute_code)
		{
			/* echo $selectedValue.'----'.$attribute_code;
			exit; */
			$parentAttrMod = Attributes::where('attribute_code',$attribute_code)->first();
			$parentAttrId = $parentAttrMod->attribute_id;
			
			/*
			*child attribute details
			*/
			$attributes = Attributes::where("status",1)->where('parent_attribute',$parentAttrId)->get();
			$attributeArray = array();
			
			foreach($attributes as $_attrMod)
			{
				$parentAttrOpt = json_decode($_attrMod->parent_attr_opt);
				
				if(in_array($selectedValue, $parentAttrOpt))
				{
					$attributeArray[] = $_attrMod;
				}
			}
			
			/*
			*child attribute details
			*/
			return view("Employee/showconditionalhtml",compact('attributeArray'));
			
			
		}
		
		public function showallowAttribute($deptId=NULL,$onboardingStatusId=NULL)
		{
			$attributesDetails = Attributes::whereIn("department_id",array($deptId,'All'))->where("onboarding_status",array($onboardingStatusId))->where(["parent_attribute"=>0])->where(["status"=>1])->orderBy("sort_order","ASC")->get();			
			return view("Employee/showallowattr",compact('attributesDetails'));	
		}
		
		public function updateEmp($empId = NULL)
		{
			$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empId)
					->where('attributes.status',1)
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();

					//   echo "<pre>";
					//   print_r($empDetails);
					//   exit;
			
			$empRequiredDetails =  Employee_details::where('emp_id',$empId)->first();
			return view("Employee/updateEmp",compact('empDetails'),compact('empRequiredDetails'));
		}
		public function editallowAttribute($deptId=NULL,$onboardingStatusId=NULL,$empId = NULL,$mode =NULL)
		{
			
			if($mode == 'A')
			{
			$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empId)
					->where('attributes.status',1)
					 ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
					  $attributesDetails = Attributes::whereIn("department_id",array($deptId,'All'))->where("onboarding_status",array($onboardingStatusId))->where(["parent_attribute"=>0])->where(["status"=>1])->orderBy("sort_order","ASC")->get();			
			}
			else
			{
				$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empId)
					->where('attributes.status',1)
					->where('attributes.tab_name',$mode)
					 ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
					  $attributesDetails = Attributes::whereIn("department_id",array($deptId,'All'))->where("onboarding_status",array($onboardingStatusId))->where(["parent_attribute"=>0])->where(["status"=>1])->where('tab_name',$mode)->orderBy("sort_order","ASC")->get();			
			}
			
			return view("Employee/editallowAttribute",compact('attributesDetails'),compact('empDetails'));	
		}
		
		public function showconditionalhtmlUpdate($selectedValue,$attribute_code,$empId)
		{
			/* echo $selectedValue.'----'.$attribute_code;
			exit; */
			
			$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empId)
					->where('attributes.status',1)
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			$parentAttrMod = Attributes::where('attribute_code',$attribute_code)->first();
			$parentAttrId = $parentAttrMod->attribute_id;
			
			/*
			*child attribute details
			*/
			$attributes = Attributes::where('parent_attribute',$parentAttrId)->get();
			$attributeArray = array();
			
			foreach($attributes as $_attrMod)
			{
				$parentAttrOpt = json_decode($_attrMod->parent_attr_opt);
				
				if(in_array($selectedValue, $parentAttrOpt))
				{
					$attributeArray[] = $_attrMod;
				}
			}
			
			/*
			*child attribute details
			*/
			$empDetailsArray = array();
			foreach($empDetails as $emp_m)
			{
				$empDetailsArray[$emp_m->attribute_code] = $emp_m->attribute_values;
			}
			
			return view("Employee/showconditionalhtmlUpdate",compact('attributeArray'),compact('empDetailsArray'));
			
			
		}
		
		public function updateEmployeeData(Request $req)
		{
			$inputData = $req->input();
		/* 	echo '<pre>';
			print_r($inputData);
			exit; */
			$empdetails =  Employee_details::find($req->input('id'));
			
			$empdetails->onboarding_status=$req->input('onboarding_status');
			$empdetails->first_name=$req->input('first_name');
			$empdetails->middle_name=$req->input('middle_name');
			$empdetails->last_name=$req->input('last_name');
			$empdetails->status=1;
			$empdetails->save();
			
			$empIdPadding = $req->input('emp_id');
			$num = $req->input('emp_id');
			/*
			*delete rows from attribute]
			*start code
			*/
			
			//Employee_attribute::where('emp_id', $empIdPadding)->delete();
			
			/*
			*delete rows from attribute]
			*end code
			*/
			$keys = array_keys($_FILES);
			
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				
				if(!empty($req->file($key)))
				{
				$filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
				$vKey = $keys[$fileIndex];
				$newFileName = $keys[$fileIndex].'-'.$num.'.'.$fileExtension;
				
				/*
				*Updating File Name
				*/
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
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


			
			$attributesValues = $req->input();	
			
			unset($attributesValues['_token']);
			unset($attributesValues['dept_id']);
			unset($attributesValues['onboarding_status']);
			unset($attributesValues['first_name']);
			unset($attributesValues['middle_name']);
			unset($attributesValues['last_name']);
			unset($attributesValues['_url']);
			unset($attributesValues['id']);
			unset($attributesValues['emp_id']);
			
			
			
			foreach($attributesValues as $key=>$value)
			{
				if(in_array($key,$listOfAttribute))
				{
					if($filesAttributeInfo[$key] != '')
					{
					$dpid = $req->input('dept_id');
					$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
										->where('attribute_code',$key)
										->where('dept_id',$dpid)
										->first();
							if(!empty($empattributesMod))
							{						
								$empattributes = Employee_attribute::find($empattributesMod->id);
								$empattributes->attribute_code = $key;
								$empattributes->attribute_values = $filesAttributeInfo[$key];
								$empattributes->status = 1;
								$empattributes->emp_id = $empIdPadding;
								$empattributes->dept_id = $req->input('dept_id');
								$empattributes->save();
							}
							else
							{
								$empattributes = new Employee_attribute();
								$empattributes->attribute_code = $key;
								$empattributes->attribute_values = $filesAttributeInfo[$key];
								$empattributes->status = 1;
								$empattributes->emp_id = $empIdPadding;
								$empattributes->dept_id = $req->input('dept_id');
								$empattributes->save();
							}
					}
					
				}
				else{
					
					$dpid = $req->input('dept_id');
					$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
										->where('attribute_code',$key)
										->where('dept_id',$dpid)
										->first();
										
					
					if(!empty($empattributesMod))
					{
					$empattributes = Employee_attribute::find($empattributesMod->id);
					$empattributes->attribute_code = $key;
					$empattributes->attribute_values = $value;
					$empattributes->status = 1;
					$empattributes->emp_id = $empIdPadding;
					$empattributes->dept_id = $req->input('dept_id');
					$empattributes->save();
					}
					else if(empty($empattributesMod) && !empty($attributesValues[$key]))
					{
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $key;
						$empattributes->attribute_values = $attributesValues[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empIdPadding;
						$empattributes->dept_id = $dpid;
						$empattributes->save();
					}
					else
					{
						//nothing to do
					}
					
				}
				
			}


			
			
			
			$req->session()->flash('message','Data Updated Successfully.');
            return redirect('listEmp');
		}
		
		public function deleteEmp(Request $req)
		{
			$employee_obj = Employee_details::find($req->id);
       
        $employee_obj->status = 3;
       
        $employee_obj->save();
        $req->session()->flash('message','Employee deleted Successfully.');
        return redirect('listEmp');
		}
		
		public function importEmp()
		{
			$empFImport = EmployeeImportFiles::orderBy("id","DESC")->get();
			$attrFImport = array();
            return view("Employee/importEmp",compact('empFImport','empFImport') );
		}
		
		public function empFileUpload(Request $request)
        {
			
          $request->validate([

            'file' => 'required|mimes:csv,txt|max:2048',

        ]);

  

        $fileName = time().'_Employee.csv';  

   

        $request->file->move(public_path('uploads/empImport'), $fileName);

			$empObjImport = new EmployeeImportFiles();
            $empObjImport->file_name = $fileName;
            $empObjImport->save();

        return back()

            ->with('success','You have successfully upload file.')

            ->with('file',$fileName);
        }
		
		public function empFileImport(Request $request)
		{
			$detailsV = $request->input();
			$attr_f_import = $detailsV['attr_f_import'];
			$empDetailsDat = EmployeeImportFiles::find($attr_f_import);
			$filename = $empDetailsDat->file_name;
			$uploadPath = '/srv/www/htdocs/hrm/public/uploads/empImport/';
			$fullpathFileName = $uploadPath . $filename;
			$file = fopen($fullpathFileName, "r");
			$i = 1;
			$dataFromCsv = array();
			while (!feof($file)) {

				$dataFromCsv[$i] = fgetcsv($file);

				$i++;
			}

			fclose($file);
			/* echo '<pre>';
			print_r($dataFromCsv);
			exit; */
			$empdetails = new Employee_details();
			$empAttrMod = new Employee_attribute();
			$iCsv = 0;
			$iCsvIndex = 0;
			$arrayDat = array();
			$arrayDatAttribute = array();
			 /* echo '<pre>';
			print_r($dataFromCsv);
			exit;   */
			$valuesCheck = array();
			foreach ($dataFromCsv as $fromCsv) {
				if ($iCsv != 0 && $fromCsv[1] != '') {
					/* echo '<pre>';
					print_r($fromCsv);
					exit; */
					$arrayDat[$iCsv]['emp_id'] = $fromCsv[0];
					$arrayDat[$iCsv]['dept_id'] = $fromCsv[1];
					$arrayDat[$iCsv]['onboarding_status'] = $fromCsv[2];
					$arrayDat[$iCsv]['first_name'] = trim($fromCsv[3]);
					$arrayDat[$iCsv]['middle_name'] = trim($fromCsv[4]);
					$arrayDat[$iCsv]['last_name'] = trim($fromCsv[5]);
					$arrayDat[$iCsv]['source_code'] = trim($fromCsv[32]);
					$arrayDat[$iCsv]['basic_salary'] = round(trim($fromCsv[23]),2);
					$arrayDat[$iCsv]['others_mol'] = round(trim($fromCsv[24]),2);
					$arrayDat[$iCsv]['gross_mol'] = round(trim($fromCsv[25]),2);
					$arrayDat[$iCsv]['actual_salary'] = round(trim($fromCsv[33]),2);
					$arrayDat[$iCsv]['status'] = 1;
					
					/*
					*LOC_ADD
					*/
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'email';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[6]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					
					$iCsvIndex++;
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'PVISA_NUMBER';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[7]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					
					
					$iCsvIndex++;
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'visa_uid_no';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[8]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					
					
					$iCsvIndex++;
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'labour_expiry_date';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[9]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'LC_Number';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[10]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'person_code';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[11]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'emirates_id_no';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[12]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'PP_NO';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[13]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'GNDR';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[14]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'NAT';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[15]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					$nat = trim($fromCsv[15]);
					
					$localNumber = trim($fromCsv[16]);
					if($localNumber != '')
					{
						$localNumber = '+971'.$localNumber;
					}
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'CONTACT_NUMBER';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = $localNumber;
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					$h_contactNo = trim($fromCsv[17]);
					$h_contactNo = round($h_contactNo);
					if($h_contactNo  != '')
					{
					if($nat == 'INDIA')
					{
						$h_contactNo = '+91'.$h_contactNo;
					}
					else if($nat == 'PAKISTAN')
					{
						$h_contactNo = '+92'.$h_contactNo;
					}
					else if($nat == 'PHILIPPINES')
					{
						$h_contactNo = '+63'.$h_contactNo;
					}
					else if($nat == 'EGYPT')
					{
						$h_contactNo = '+20'.$h_contactNo;
					}
					else if($nat == 'SRILANKA')
					{
						$h_contactNo = '+94'.$h_contactNo;
					}
					else if($nat == 'NEPAL')
					{
						$h_contactNo = '+977'.$h_contactNo;
					}
					else if($nat == 'BANGLADESH')
					{
						$h_contactNo = '+880'.$h_contactNo;
					}
					else if($nat == 'MOROCCO')
					{
						$h_contactNo = '+212'.$h_contactNo;
					}
					else if($nat == 'EMIRATES')
					{
						$h_contactNo = '+971'.$h_contactNo;
					}
					else if($nat == 'INDONESIA')
					{
						$h_contactNo = '+62'.$h_contactNo;
					}
					else
					{
						$h_contactNo = $h_contactNo;
					}
					}
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'HC_CONTACT_NUMBER';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = $h_contactNo;
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'LOC_ADD';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[18]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'HOM_ADD';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[19]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'EMPDOB';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[20]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'residence_stamp_start_date';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[21]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'residence_stamp_expiry_date';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[22]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					$basicSalary = 'AED '.$this->numberFormat(trim($fromCsv[23]),2);
					//$valuesCheck[] = $basicSalary;
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'basic_salary_mol';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = $basicSalary;
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					$othersMol = 'AED '.$this->numberFormat(trim($fromCsv[24]),2);
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'others_mol';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = $othersMol;
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					$grossSalary = 'AED '.$this->numberFormat(trim($fromCsv[25]),2);
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'total_gross_salary';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = $grossSalary;
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'insurance';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[26]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'work_location';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[27]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'DOJ';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[28]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					$valuesCheck[] = trim($fromCsv[29]);
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'PERMOL';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[29]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'DESIGN';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[30]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'effects';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[31]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'source_code';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[32]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					$actualPrice = 'AED '.$this->numberFormat(trim($fromCsv[33]),2);
					//$valuesCheck[] = $actualPrice;
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'actual_salary';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = $actualPrice;
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'entity';
					$entity = trim($fromCsv[34]);
					$entityArray = explode("-",$entity);
					if(count($entityArray) >1)
					{
						$entity = $entityArray[1];
					}
					
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($entity);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $fromCsv[1];
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'residence_visa_no';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[35]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
									
					
				}
				$iCsv++;
			}
			/* echo '<pre>';
			print_r($valuesCheck);
			exit;  */
			//$empdetails->insert($arrayDat);
			//$empAttrMod->insert($arrayDatAttribute); 
			echo "yes - DONE- Rahul";
			exit;
			
		}
		
		
		function numberFormat($number, $decimals=0)
    {

        // $number = 555;
        // $decimals=0;
        // $number = 555.000;
        // $number = 555.123456;

        if (strpos($number,'.')!=null)
        {
            $decimalNumbers = substr($number, strpos($number,'.'));
            $decimalNumbers = substr($decimalNumbers, 1, $decimals);
        }
        else
        {
            $decimalNumbers = 0;
            for ($i = 2; $i <=$decimals ; $i++)
            {
                $decimalNumbers = $decimalNumbers.'0';
            }
        }
        // return $decimalNumbers;



        $number = (int) $number;
        // reverse
        $number = strrev($number);

        $n = '';
        $stringlength = strlen($number);

        for ($i = 0; $i < $stringlength; $i++)
        {
            if ($i%2==0 && $i!=$stringlength-1 && $i>1)
            {
                $n = $n.$number[$i].',';
            }
            else
            {
                $n = $n.$number[$i];
            }
        }

        $number = $n;
        // reverse
        $number = strrev($number);

        ($decimals!=0)? $number=$number.'.'.$decimalNumbers : $number ;

        return $number;
    }

		
		
		public function empFileImport_update(Request $request)
		{
			$detailsV = $request->input();
			$attr_f_import = $detailsV['attr_f_import'];
			$empDetailsDat = EmployeeImportFiles::find($attr_f_import);
			$filename = $empDetailsDat->file_name;
			$uploadPath = '/srv/www/htdocs/hrm/public/uploads/empImport/';
			$fullpathFileName = $uploadPath . $filename;
			$file = fopen($fullpathFileName, "r");
			$i = 1;
			$dataFromCsv = array();
			while (!feof($file)) {

				$dataFromCsv[$i] = fgetcsv($file);

				$i++;
			}

			fclose($file);
			/*   echo '<pre>';
			print_r($dataFromCsv);
			exit;   */
			$keyValues = array('Sur_name','product','deputed','PVISA_NUMBER','visa_uid_no','visa_issue_date','visa_expiry_date','labour_issue_date','labour_expiry_date','LC_Number','person_code','emirates_id_no','PP_NO','GNDR','NAT','PER_VISA_STATUS','permanent_visa_issuances','contract','VS','EMPDOB','dha_mem_no');
			$empdetails = new Employee_details();
			$empAttrMod = new Employee_attribute();
			$iCsv = 0;
			
			$arrayDat = array();
			$arrayDatAttribute = array();
			/* echo '<pre>';
			print_r($dataFromCsv);
			exit;  */ 
			
			$valuesIndex = 1;
			foreach ($dataFromCsv as $fromCsv) {
				if ($iCsv != 0 ) {
					if(!empty($fromCsv[0]))
					{
						$empIdPadding = $fromCsv[0];
						$getDept = Employee_details::where('emp_id',$empIdPadding)->first();
						
						
						
						$dpid = $getDept->dept_id;
						$keyValue = 1;
						$iCsvIndex = 0;
						foreach($keyValues as $_key)
						{
							$key = $_key;
							$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
											->where('attribute_code',$key)
											->where('dept_id',$dpid)
											->first();
							
							if(empty($empattributesMod))
							{
								
									$empattributes = new Employee_attribute();
									$empattributes->attribute_code = $key;
									if($key == 'labour_expiry_date' || $key == 'labour_issue_date' || $key == 'visa_expiry_date' || $key == 'visa_issue_date' || $key == 'EMPDOB')
										{
											
											if($key == 'EMPDOB')
											{
											$keyD = str_replace("/","-",$fromCsv[$keyValue]);
											$dateP = trim($keyD);
											}
											else
											{
												$dateP = trim($fromCsv[$keyValue]);
											}
											
											$empattributes->attribute_values = date("Y-m-d",strtotime($dateP));
										}
										else
										{
											$empattributes->attribute_values = trim($fromCsv[$keyValue]);
										}
									$empattributes->status = 1;
									$empattributes->emp_id = $empIdPadding;
									$empattributes->dept_id = $dpid;
									$empattributes->save();	
							}	
							else
							{
									
									$empattributes = Employee_attribute::find($empattributesMod->id);
									$empattributes->attribute_code = $key;
									if($key == 'labour_expiry_date' || $key == 'labour_issue_date' || $key == 'visa_expiry_date' || $key == 'visa_issue_date' || $key == 'EMPDOB')
										{
											if($key == 'EMPDOB')
											{
											$keyD = str_replace("/","-",$fromCsv[$keyValue]);
											$dateP = trim($keyD);
											}
											else
											{
												$dateP = trim($fromCsv[$keyValue]);
											}
											$empattributes->attribute_values = date("Y-m-d",strtotime($dateP));
										}
										else
										{
											$empattributes->attribute_values = trim($fromCsv[$keyValue]);
										}
									$empattributes->status = 1;
									$empattributes->emp_id = $empIdPadding;
									$empattributes->dept_id = $dpid;
									$empattributes->save();
							}
						
							$keyValue++;
							$iCsvIndex++;
						}
						
						
					}
					
					
					
								
					
				}
				$iCsv++;
			}
			echo 'done';
						exit;
			echo '<pre>';
						print_r($arrayDatAttribute);
						exit;
			//$empdetails->insert($arrayDat);
			//$empAttrMod->insert($arrayDatAttribute); 
			echo "yes";
			exit;
			
		}
		
		
		
		public function empFileImport_OldUpdate(Request $request)
		{
			
			$detailsV = $request->input();
			$attr_f_import = $detailsV['attr_f_import'];
			$empDetailsDat = EmployeeImportFiles::find($attr_f_import);
			$filename = $empDetailsDat->file_name;
			$uploadPath = '/srv/www/htdocs/hrm/public/uploads/empImport/';
			$fullpathFileName = $uploadPath . $filename;
			$file = fopen($fullpathFileName, "r");
			$i = 1;
			$dataFromCsv = array();
			while (!feof($file)) {

				$dataFromCsv[$i] = fgetcsv($file);

				$i++;
			}

			fclose($file);
			/* echo '<pre>';
			print_r($dataFromCsv);
			exit;  */   
			$keyValues = array('Sur_name','product','deputed','PVISA_NUMBER','visa_uid_no','visa_issue_date','visa_expiry_date','labour_issue_date','labour_expiry_date','LC_Number','person_code','emirates_id_no','PP_NO','GNDR','NAT','PER_VISA_STATUS','permanent_visa_issuances','contract','VS','EMPDOB','dha_mem_no','PERMOL');
			$empdetails = new Employee_details();
			$empAttrMod = new Employee_attribute();
			$iCsv = 0;
			
			$arrayDat = array();
			$arrayDatAttribute = array();
			/*  echo '<pre>';
			print_r($dataFromCsv);
			exit;  */ 
			
			$valuesIndex = 6;
			foreach ($dataFromCsv as $fromCsv) {
				if ($iCsv != 0 ) {
					if(!empty($fromCsv[0]))
					{
						
						$empdata = new Employee_details();
						$empdata->emp_id = $fromCsv[0];
						$empdata->dept_id = $fromCsv[1];
						$empdata->onboarding_status = 1;
						$empdata->first_name = $fromCsv[3];
						$empdata->middle_name = $fromCsv[4];
						$empdata->last_name = $fromCsv[5];
						$empdata->status = 1;
						
						$empdata->save();
						
						$empIdPadding = $fromCsv[0];
						
						
						
						
						$dpid = $fromCsv[1];
						$keyValue = 6;
						$iCsvIndex = 0;
						foreach($keyValues as $_key)
						{
							$key = $_key;
							$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
											->where('attribute_code',$key)
											->where('dept_id',$dpid)
											->first();
							
							if(empty($empattributesMod))
							{
								
									$empattributes = new Employee_attribute();
									$empattributes->attribute_code = $key;
									if($key == 'labour_expiry_date' || $key == 'labour_issue_date' || $key == 'visa_expiry_date' || $key == 'visa_issue_date' || $key == 'EMPDOB')
										{
											
											if($key == 'EMPDOB')
											{
											$keyD = str_replace("/","-",$fromCsv[$keyValue]);
											$dateP = trim($keyD);
											}
											else
											{
												$dateP = trim($fromCsv[$keyValue]);
											}
											
											$empattributes->attribute_values = date("Y-m-d",strtotime($dateP));
										}
										else
										{
											$empattributes->attribute_values = trim($fromCsv[$keyValue]);
										}
									$empattributes->status = 1;
									$empattributes->emp_id = $empIdPadding;
									$empattributes->dept_id = $dpid;
									$empattributes->save();	
							}	
							else
							{
									
									$empattributes = Employee_attribute::find($empattributesMod->id);
									$empattributes->attribute_code = $key;
									if($key == 'labour_expiry_date' || $key == 'labour_issue_date' || $key == 'visa_expiry_date' || $key == 'visa_issue_date' || $key == 'EMPDOB')
										{
											if($key == 'EMPDOB')
											{
											$keyD = str_replace("/","-",$fromCsv[$keyValue]);
											$dateP = trim($keyD);
											}
											else
											{
												$dateP = trim($fromCsv[$keyValue]);
											}
											$empattributes->attribute_values = date("Y-m-d",strtotime($dateP));
										}
										else
										{
											$empattributes->attribute_values = trim($fromCsv[$keyValue]);
										}
									$empattributes->status = 1;
									$empattributes->emp_id = $empIdPadding;
									$empattributes->dept_id = $dpid;
									$empattributes->save();
							}
						
							$keyValue++;
							$iCsvIndex++;
						}
						
						
					}
					
					
					
					
				}
				$iCsv++;
				
			}
			echo 'done';
						exit;
			echo '<pre>';
						print_r($arrayDatAttribute);
						exit;
			//$empdetails->insert($arrayDat);
			//$empAttrMod->insert($arrayDatAttribute); 
			echo "yes";
			exit;
			
		}
		
		public function updateEmployeeValues()
		{
			/* echo "DONE";
			exit; */
			$empAttrMod = new Employee_attribute();
			$filename = 'MBM-updated.csv';
			$uploadPath = '/srv/www/htdocs/hrm/public/uploads/empImport/updated/';
			$fullpathFileName = $uploadPath . $filename;
			$file = fopen($fullpathFileName, "r");
			$i = 1;
			$dataFromCsv = array();
			while (!feof($file)) {

				$dataFromCsv[$i] = fgetcsv($file);

				$i++;
			}

			fclose($file);
		     /* echo '<pre>';
			print_r($dataFromCsv);
			exit;   */ 
			$iIndex=0;
			$updateIndex = 0;
			$updateTable = array();
			foreach($dataFromCsv as $datefCSV)
			{
				if($iIndex !=0 && is_array($datefCSV))
				{
					
					$updateTable[$updateIndex]['emp_id'] = $datefCSV[0];
					$updateTable[$updateIndex]['dept_id'] = $datefCSV[1];
					$updateTable[$updateIndex]['attribute_code'] = 'VS';
					$updateTable[$updateIndex]['attribute_values'] = $datefCSV[24];
					$updateTable[$updateIndex]['status'] = 1;
					$updateIndex++;
					$updateTable[$updateIndex]['emp_id'] = $datefCSV[0];
					$updateTable[$updateIndex]['dept_id'] = $datefCSV[1];
					$updateTable[$updateIndex]['attribute_code'] = 'PER_VISA_STATUS';
					$updateTable[$updateIndex]['attribute_values'] = $datefCSV[23];
					$updateTable[$updateIndex]['status'] = 1;
					$updateIndex++;
					
				}
				$iIndex++;
			}
			 echo "<pre>";
			print_r($updateTable);
			exit;   
			//$empAttrMod->insert($updateTable);
			echo "DONE";
			exit;
			
		}
		
		public function employeeAttendance(Request $request)
		{
			$empdetailsListing = array();
			$checkSelectFilter = 0;
			$dept = 0;
			$selectFrom = '';
			$selectTo = '';
			$emp_id = '';
			if(!empty($request->session()->get('dept_id')))
			{
				$checkSelectFilter = 1;
				$dept = $request->session()->get('dept_id');
			}
			
			$emp_obj = new EmployeeAttendanceModel();
			
			
			if(!empty($request->session()->get('selectFrom')))
			{
				$checkSelectFilter = 1;
				$selectFrom =$request->session()->get('selectFrom');
			}
			if(!empty($request->session()->get('selectTo')))
			{
				$checkSelectFilter = 1;
				$selectTo =$request->session()->get('selectTo');
			}
			if(!empty($request->session()->get('emp_id')))
			{
				
				$emp_id =$request->session()->get('emp_id');
			}
			/*
			*get Department name
			*/
				$departmentA = array();
				$departmentLists = Department::where("status",1)->orderBy("id",'DESC')->get();
				foreach($departmentLists as $_dept)
				{
					$departmentA[$_dept->id] = $_dept->department_name;
				}
			/*
			*get Department name
			*/
			$DateRange = array();
			if(!empty($request->session()->get('selectFrom')) && !empty($request->session()->get('selectTo')))
			{
				$selectFrom =$request->session()->get('selectFrom');
				$selectTo =$request->session()->get('selectTo');
				$DateRange = $this->getDatesFromRangeLists($selectFrom, $selectTo);
			}
			
			if(!empty($request->session()->get('dept_id')))
			{
				$deptId = $request->session()->get('dept_id');
				$empdetails = new Employee_details();
				if($deptId == 'all' && $emp_id == '')
				{
					$empdetailsListing = $empdetails->where("status",1)->get();
				}
				else if($deptId == 'all' && $emp_id != '')
				{
					$empdetailsListing = $empdetails->where("status",1)->where("emp_id",'like',$emp_id.'%')->get();
				}
				else if($deptId != 'all' && $emp_id == '')
				{
					$empdetailsListing = $empdetails->where("status",1)->where("dept_id",$deptId)->get();
				}
				else
				{
					$empdetailsListing = $empdetails->where("status",1)->where("emp_id",'like',$emp_id.'%')->where("dept_id",$deptId)->get();
				}
			}
			
			
			/*
			* check Attendance Existance
			*start coding
			*/
		
			$existanceCheck = array();
			foreach($empdetailsListing as $_emp)
			{
				foreach($DateRange as $_date)
				{
					$_dateSet = date('Y-m-d',strtotime($_date));
					/*
					*check for holiday
					*start coding
					*/
						$goprocess = 1;
					
						$detailsHoliday = EmployeeAttendanceModel::where("attendance_date",$_dateSet)->where("mark_attendance","Holiday")->first();
						if(!empty($detailsHoliday))
						{
							$existanceCheck[$_emp->id][$_date]['allowAttendance'] = 'No';
							$markAttend = 'H';
							$existanceCheck[$_emp->id][$_date]['attendanceMark'] = $markAttend;
							$goprocess = 2;
						}
					
					
					/*
					*check for holiday
					*End coding
					*/
					$details = EmployeeAttendanceModel::where("dept_id",$deptId)->where("emp_id",$_emp->id)->where("attendance_date",$_dateSet)->where("over_ride_sandwich",0)->first();
					if($goprocess == 1 || (!empty($details) && $details->mark_attendance == 'sandwich'))
					{
					if(!empty($details))
					{
						$existanceCheck[$_emp->id][$_date]['allowAttendance'] = 'No';
						if($details->mark_attendance == 'present')
						{
							$markAttend = 'P';
							
						}
						else if($details->mark_attendance == 'absent')
						{
							$markAttend = 'A';
							
						}
						else if($details->mark_attendance == 'late')
						{
							$markAttend = 'L';
							
						}
						else if($details->mark_attendance == 'sandwich')
						{
							$markAttend = 'S';
						}
						else if($details->mark_attendance == 'leave')
						{
							$markAttend = 'Leave';
						}
						else
						{
							$markAttend = 'Leave';
						}
						$existanceCheck[$_emp->id][$_date]['attendanceMark'] = $markAttend;
						$leaveType = '';
						if($details->leave_type == 'casual_leave')
						{
							$leaveType = 'CL';
						}
						else if($details->leave_type == 'annual_leave')
						{
							$leaveType = 'AL';
						}
						else if($details->leave_type == 'sick_leave')
						{
							$leaveType = 'SL';
						}
						else if($details->leave_type == 'public_holiday')
						{
							$leaveType = 'PH';
						}
						else if($details->leave_type == 'emergency_leave')
						{
							$leaveType = 'EL';
						}
						else if($details->leave_type == 'half_day')
						{
							$leaveType = 'HD';
						}
						else
						{
							
						}
						$existanceCheck[$_emp->id][$_date]['attendanceLeaveType'] = $leaveType;
						$existanceCheck[$_emp->id][$_date]['leave_approved'] = $details->leave_approved;
 					}
					else
					{
						$existanceCheck[$_emp->id][$_date]['allowAttendance'] = 'Yes';
					}
					}
				}
			}
			
			/*
			* check Attendance Existance
			*end coding
			*/
			$departmentName = '';
			if(!empty($request->session()->get('dept_id')))
			{
			$departmentObj = new Department();
			$departmentDetails = Department::where("id",$deptId)->first();
			$departmentName= $departmentDetails->department_name;
			}
			/*
			*get List of holidays
			*/
			$detailsHoliday = EmployeeAttendanceModel::where("mark_attendance",'H')->get();
			$holidayList = array();
			foreach($detailsHoliday as $_holiday)
			{
				$attendanceD = $_holiday->attendance_date;
				$holidayList[] = date("d-m-Y",strtotime($attendanceD));
			}
			
			/*
			*get List of holidays
			*/
			return view("Employee/employeeAttendance",compact('empdetailsListing','departmentA','dept','checkSelectFilter','DateRange','selectFrom','selectTo','existanceCheck','departmentName','holidayList','emp_id') );
		}
		
		public function addAttendance()
		{
			
			/*
			*@Description - get department from DataBase
			*@Start Coding
			*/
			$departmentMod = Department::where("status",1)->orderBy("id",'DESC')->get();
			/*
			*@Description - get department from DataBase
			*@End Coding
			*/
			/*
			*get List of holidays
			*/
			$detailsHoliday = EmployeeAttendanceModel::where("attendance_value",'H')->get();
			$holidayList = array();
			foreach($detailsHoliday as $_holiday)
			{
				$attendanceD = $_holiday->attendance_date;
				$holidayList[] = date("dd-mm-yyyy",strtotime($attendanceD));
			}
			echo '<pre>';
			print_r($holidayList);
			exit;
			/*
			*get List of holidays
			*/
			return view("Employee/addAttendance",compact('departmentMod'));
		}
		
		public function addAttendance1()
		{
			
			/*
			*@Description - get department from DataBase
			*@Start Coding
			*/
			$departmentMod = Department::where("status",1)->orderBy("id",'DESC')->get();
			/*
			*@Description - get department from DataBase
			*@End Coding
			*/
			/*
			*get List of holidays
			*/
			$detailsHoliday = EmployeeAttendanceModel::where("mark_attendance",'H')->get();
			$holidayList = array();
			foreach($detailsHoliday as $_holiday)
			{
				$attendanceD = $_holiday->attendance_date;
				$holidayList[] = date("d-m-Y",strtotime($attendanceD));
			}
			
			/*
			*get List of holidays
			*/
			
			
			return view("Employee/addAttendance1",compact('departmentMod','holidayList'));
		}
		
		public function empajaxlist(Request $req)
		{
			$deptId = $req->departmentid;
			
			$empdetails = new Employee_details();
			if($deptId == 'all')
			{
				$empdetailsListing = $empdetails->where("status",1)->get();
			}
			else
			{
			    $empdetailsListing = $empdetails->where("status",1)->where("dept_id",$deptId)->get();
			}
			return view("Employee/empajaxlist",compact('empdetailsListing'));
		}
		
		public function empajaxlistNew(Request $req)
		{
			$deptId = $req->departmentid;
			$selectedDateFrom = $req->selectedDateFrom;
			$selectedDateTo = $req->selectedDateTo;
			$DateRange = $this->getDatesFromRange($selectedDateFrom, $selectedDateTo);
			
			$empdetails = new Employee_details();
			if($deptId == 'all')
			{
				$empdetailsListing = $empdetails->where("status",1)->get();
			}
			else
			{
			    $empdetailsListing = $empdetails->where("status",1)->where("dept_id",$deptId)->get();
			}
			/*
			*check Attendance existance for employee
			*start code
			*/
			$specificDate = date('Y-m-d',strtotime($selectedDateFrom));
			$empAttendanceDetails = EmployeeAttendanceModel::where("dept_id",$deptId)->where("attendance_date",$specificDate)->get();
			$existEmpAsPerDate = array();
			foreach($empAttendanceDetails as $_emp)
			{
				$existEmpAsPerDate[] = $_emp->emp_id;
			}
			$existEmpAsPerDate = array();
			/*
			*check Attendance existance for employee
			*end code
			*/
			/*
			* check Attendance Existance
			*start coding
			*/
		
			$existanceCheck = array();
			foreach($empdetailsListing as $_emp)
			{
				foreach($DateRange as $_date)
				{
					
					$_dateSet = date('Y-m-d',strtotime($_date));
						/*
					*check for holiday
					*start coding
					*/
						$goprocess = 1;
					
						$detailsHoliday = EmployeeAttendanceModel::where("attendance_date",$_dateSet)->where("mark_attendance","Holiday")->first();
						if(!empty($detailsHoliday))
						{
							$existanceCheck[$_emp->id][$_date]['allowAttendance'] = 'No';
							$markAttend = 'H';
							$existanceCheck[$_emp->id][$_date]['attendanceMark'] = $markAttend;
							$goprocess = 2;
						}
					
					
					/*
					*check for holiday
					*End coding
					*/
					$details = EmployeeAttendanceModel::where("dept_id",$deptId)->where("emp_id",$_emp->id)->where("attendance_date",$_dateSet)->where("over_ride_sandwich",0)->first();
					if($goprocess == 1 || (!empty($details) && $details->mark_attendance == 'sandwich'))
					{
					
					
					if(!empty($details))
					{
					
					
						$existanceCheck[$_emp->id][$_date]['allowAttendance'] = 'No';
						if($details->mark_attendance == 'present')
						{
							$markAttend = 'P';
							
						}
						else if($details->mark_attendance == 'absent')
						{
							$markAttend = 'A';
							
						}
						else if($details->mark_attendance == 'late')
						{
							$markAttend = 'L';
							
						}
						else if($details->mark_attendance == 'sandwich')
						{
							$markAttend = 'S';
						}
						else if($details->mark_attendance == 'leave')
						{
							$markAttend = 'Leave';
						}
						else
						{
							$markAttend = 'Leave';
						}
						$existanceCheck[$_emp->id][$_date]['attendanceMark'] = $markAttend;
						$leaveType = '';
						if($details->leave_type == 'casual_leave')
						{
							$leaveType = 'CL';
						}
						else if($details->leave_type == 'annual_leave')
						{
							$leaveType = 'AL';
						}
						else if($details->leave_type == 'sick_leave')
						{
							$leaveType = 'SL';
						}
						else if($details->leave_type == 'public_holiday')
						{
							$leaveType = 'PH';
						}
						else if($details->leave_type == 'emergency_leave')
						{
							$leaveType = 'EL';
						}
						else if($details->leave_type == 'half_day')
						{
							$leaveType = 'HD';
						}
						else
						{
							
						}
						$existanceCheck[$_emp->id][$_date]['attendanceLeaveType'] = $leaveType;
						$existanceCheck[$_emp->id][$_date]['leave_approved'] = $details->leave_approved;
					
 					}
					else
					{
						$existanceCheck[$_emp->id][$_date]['allowAttendance'] = 'Yes';
					}
					}
					
				}
			}
			/*
			*get List of holidays
			*/
			$detailsHoliday = EmployeeAttendanceModel::where("mark_attendance",'H')->get();
			$holidayList = array();
			foreach($detailsHoliday as $_holiday)
			{
				$attendanceD = $_holiday->attendance_date;
				$holidayList[] = date("d-m-Y",strtotime($attendanceD));
			}
			
			/*
			*get List of holidays
			*/
			/*
			* check Attendance Existance
			*end coding
			*/
			return view("Employee/empajaxlistNew",compact('empdetailsListing','existEmpAsPerDate','DateRange','existanceCheck','holidayList'));
		}
		
		// Function to get all the dates in given range
	function getDatesFromRange($start, $end) {
      

  
    // Use loop to store date into array
    while(date("Y-m-d",strtotime($start)) <= date("Y-m-d",strtotime($end))) {  
	
		 $dayName =  date('D', strtotime($start));
		if($dayName != 'Sun')
		{
        $array[] = date("d-m-Y",strtotime($start)); 
		
		}
		$start = date('d-m-Y', strtotime($start . ' +1 day'));
	}
  
    // Return the array elements
    return $array;
}


function getDatesFromRangeSandwich($start, $end) {
      
  
  
    // Use loop to store date into array
    while(date("Y-m-d",strtotime($start)) <= date("Y-m-d",strtotime($end))) {  
	
		$dayName =  date('D', strtotime($start));
		
        $array[] = date("d-m-Y",strtotime($start)); 
		
		
		$start = date('d-m-Y', strtotime($start . ' +1 day'));
	}
  
    // Return the array elements
    return $array;
}

function getDatesFromRangeLists($start, $end) {
      
  
  
    // Use loop to store date into array
    while(date("Y-m-d",strtotime($start)) <= date("Y-m-d",strtotime($end))) {  
	
		$dayName =  date('D', strtotime($start));
		
        $array[] = date("d-m-Y",strtotime($start)); 
		
		
		$start = date('d-m-Y', strtotime($start . ' +1 day'));
	}
  
    // Return the array elements
    return $array;
}
		public function addEmployeeAttendancePost(Request $request)
		{
			$attendanceValue = $request->input();
			
			
			/**
			*@description - inserted Attendance Details as per employee
			*start code
			*/
			$deptId = $attendanceValue['dept_id'];
			$selectedEmps = $attendanceValue['selectedEmp'];
			foreach($selectedEmps as $_empId)
			{
				
				$listOfMarkAttendanceforemployee = $attendanceValue['addAttendanceFrm'][$_empId];
				foreach($listOfMarkAttendanceforemployee as $empValue)
				{
					
					$empAttendanceObj = new EmployeeAttendanceModel();
					$empAttendanceObj->dept_id = $deptId;
					$empAttendanceObj->emp_id = $_empId;
					$empAttendanceObj->attendance_date = date('Y-m-d',strtotime($empValue['mark_date']));
					$empAttendanceObj->mark_attendance = $empValue['mark_attendance'];
					$empAttendanceObj->leave_type = $empValue['leave_type'];
					$empAttendanceObj->over_ride_sandwich = 0;
					if($empValue['mark_attendance'] == 'leave')
					{
						$empAttendanceObj->leave_approved = 1;
					}
					else
					{
						$empAttendanceObj->leave_approved = 0;
					}
					$empAttendanceObj->created_by = $request->session()->get('EmployeeId');
					$empAttendanceObj->save();
				}
			}
			
			/**
			*@description - inserted Attendance Details as per employee
			*start code
			*/
			
			$request->session()->flash('message','You have successfully marked Attendance.');
				//$request->session()->flash('alert-class', 'alert-danger'); 
				return redirect('employeeAttendance');
		}
		
		public function attendancedetails(Request $req)
		{
			$empid = $req->empid;
			$monthNo = $req->monthNo;
			/* if(!empty($req->session()->get('applied_month')))
			{
			
				$month = $req->session()->get('applied_month');
			}
			else
			{
				$month = date('m');
			} */
			$month = (int)$monthNo;
			$year = 2022;
			$monthDetails = array();
			$monthDetails['name'] = date("F", mktime(0, 0, 0, $month, 10));
			$monthDetails['value'] = $month;
			$monthDetails['emp_id'] = $empid;
			$monthDetails['year'] = $year;
			
			
			$first_day_of_month = date('w', mktime(0,0,0,$month,1,$year));
			$monthDetails['firstday'] = $first_day_of_month;
			
			$empAttendanceDetails = EmployeeAttendanceModel::whereMonth("attendance_date",$month)->where("emp_id",$empid)->get();
			
			$daysInMonth = $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
			
			$months = array();
			$months[1] = 'January';
			$months[2] = 'February';
			$months[3] = 'March';
			$months[4] = 'April';
			$months[5] = 'May';
			$months[6] = 'June';
			$months[7] = 'July';
			$months[8] = 'August';
			$months[9] = 'September';
			$months[10] = 'October';
			$months[11] = 'November';
			$months[12] = 'December';
			$monthDetails['months'] = $months;
			return view("Employee/attendancedetails",compact('empAttendanceDetails','daysInMonth','monthDetails'));
		}
		
		public function markAsHolidaySet(Request $req)
		{
			$selecteddates = $req->selecteddates;
		
			
				$empAttendanceObj = new EmployeeAttendanceModel();
				
				$empAttendanceObj->attendance_date = date('Y-m-d',strtotime($selecteddates));
				  $empAttendanceObj->mark_attendance = 'H';
				  $empAttendanceObj->over_ride_sandwich = 0;
				  $empAttendanceObj->created_by = $req->session()->get('EmployeeId');
				$empAttendanceObj->save();
				
			
				
			$req->session()->flash('message','You have successfully marked Attendance.');
				//$request->session()->flash('alert-class', 'alert-danger'); 
				return redirect('employeeAttendance');
		}
		
		public function appliedFilterOnAttendance(Request $request)
		{
			$selectedFilter = $request->input();
			
			if(!empty($selectedFilter['selectFrom']))
			{
				$request->session()->put('selectFrom',$selectedFilter['selectFrom']);
			}
		
			if(!empty($selectedFilter['selectTo']))
			{
				$request->session()->put('selectTo',$selectedFilter['selectTo']);
			}
			
			
			if(!empty($selectedFilter['dept_id']))
			{
				$request->session()->put('dept_id',$selectedFilter['dept_id']);
			}
			if(!empty($selectedFilter['emp_id']))
			{
				$request->session()->put('emp_id',$selectedFilter['emp_id']);
			}
			else
			{
				$request->session()->put('emp_id','');
			}
			
			return redirect('employeeAttendance');
		}
		public function resetFAttendance(Request $request)
		{
			$request->session()->put('selectFrom','');
			$request->session()->put('selectTo','');
			$request->session()->put('dept_id','');
			return redirect('employeeAttendance');
		}
		
		public function exportAttendance(Request $request)
		{
			
			$filename = 'AttendanceReport_' . date("d-m-Y h:i:s") . '.csv';
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'";'); 
			$requestInput = $request->input();
			
		   /*  echo '<pre>';
			print_r($requestInput);
			exit;  */
			$_empArray = $requestInput['empids'];
			$selectfromexport = $requestInput['selectfromexport'];
			$selecttoexport = $requestInput['selecttoexport'];
			$dept_idexport = $requestInput['dept_idexport'];
			$DateRange = array();
			if(!empty($selectfromexport) && !empty($selecttoexport))
			{
				$DateRange = $this->getDatesFromRangeLists($selectfromexport, $selecttoexport);
			}
			$header = array();
			$header[] = 'Employee Id';
			$header[] = 'Department Name';
			$header[] = 'Employee Name';
			$header[] = 'Employee Number';
			foreach($DateRange as $_date)
			{
				$header[] = date("d M",strtotime($_date));
			}
			
			$f = fopen('php://output', 'w');
			fputcsv($f, $header, ',');
       
			
			
			/*
			*get List of holidays
			*/
			$detailsHoliday = EmployeeAttendanceModel::where("mark_attendance",'H')->get();
			$holidayList = array();
			foreach($detailsHoliday as $_holiday)
			{
				$attendanceD = $_holiday->attendance_date;
				$holidayList[] = date("d-m-Y",strtotime($attendanceD));
			}
			
			/*
			*get List of holidays
			*/
			/*
			*
			*/
			$_empArray_ids = explode(",",$_empArray);
			
						
			foreach ($_empArray_ids as $empid) {
				$values = array();
				$values[] = $this->EmpId($empid);
				$values[] = $this->EmpDepartment($empid);
				$values[] = $this->EmpName($empid);
				$values[] = $this->EmpMobile($empid);
				foreach($DateRange as $_date)
				{
				$_dateSet = date('Y-m-d',strtotime($_date));
				
				
				$dayName =  date('D', strtotime($_date));
				
					/*
					*check for holiday
					*start coding
					*/
						$goprocess = 1;
					
						$detailsHoliday = EmployeeAttendanceModel::where("attendance_date",$_dateSet)->where("mark_attendance","Holiday")->first();
						if(!empty($detailsHoliday) )
						{
							
							$values[] = 'H';
							
							$goprocess = 2;
						}
						
						
					
					
					/*
					*check for holiday
					*End coding
					*/
					$details = EmployeeAttendanceModel::where("dept_id",$dept_idexport)->where("emp_id",$empid)->where("attendance_date",$_dateSet)->where("over_ride_sandwich",0)->first();
					if($goprocess == 1 || (!empty($details) && $details->mark_attendance == 'sandwich'))
					{
					if(!empty($details))
					{
						
						if($details->mark_attendance == 'present')
						{
							$values[] = 'P';
							
						}
						else if($details->mark_attendance == 'sandwich')
						{
							$values[] = 'S';
						}
						else if($details->mark_attendance == 'absent')
						{
							$values[] = 'A';
						}
						else if($details->mark_attendance == 'late')
						{
							$values[] = 'L';
						}
						else
						{
							
							if($details->leave_type == 'casual_leave')
						{
							$values[] = 'CL';
						}
						else if($details->leave_type == 'annual_leave')
						{
							$values[] = 'AL';
						}
						else if($details->leave_type == 'sick_leave')
						{
							$values[] = 'SL';
						}
						else if($details->leave_type == 'public_holiday')
						{
							$values[] = 'PH';
						}
						else if($details->leave_type == 'emergency_leave')
						{
							$values[] = 'EL';
						}
						else if($details->leave_type == 'half_day')
						{
							$values[] = 'HD';
						}
						else
						{
							
						}
						}
						
						
						
						
 					}
					else
					{
						if($dayName == 'Sun')
						{
							$values[] = 'H';
						}
						else
						{
							if(!in_array($_date,$holidayList))
							{
								$values[] = 'Not Marked';
							}
							else
							{
								$values[] = 'H';
							}
								
						}
					}
					}
					
				
				}
				fputcsv($f, $values, ',');
				/* echo '<pre>';
				print_r($values);
				exit; */
			}
			
			exit();
		}
		public function EmpId($eId)
		{
			$eMod = Employee_details::where('id',$eId)->first();
			return $eMod->emp_id;
		}
		public function EmpDepartment($eId)
		{
			$emp = Employee_details::where("id",$eId)->first();
			$dept_id = $emp->dept_id;
			$dMod = Department::where('id',$dept_id)->first();
			return $dMod->department_name;
		}
		
		public function EmpName($eId)
		{
			$eMod = Employee_details::where('id',$eId)->first();
			return $eMod->first_name.' '.$eMod->last_name;
		}
		public function EmpMobile($eId)
		{
				$emp = Employee_details::where("id",$eId)->first();
				$empCode = $emp->emp_id;
				$eMod = Employee_attribute::where('emp_id',$empCode)->where("attribute_code","LC_Number")->first();
				
				if(empty($eMod))
				{
					return '';
				}
				else
				{
					return $eMod->attribute_values;
				}
		}
		
		public function PresentAttendance($eId,$request)
		{
			if(!empty($request->session()->get('applied_month')))
			{
			
				$month = $request->session()->get('applied_month');
			}
			else
			{
				$month = date('m');
			}
			$empdetails = EmployeeAttendanceModel::whereMonth("attendance_date",$month)->where("emp_id",$eId)->where("attendance_value","P")->selectraw("count(id) as totalAttendance,emp_id")->groupBy('emp_id')->first();
			if(!empty($empdetails))
			{
				$totalAttendance = $empdetails->totalAttendance;
			}
			else
			{
				 $totalAttendance = 0;
			}
			return $totalAttendance;
		}
		
		public function AbsentAttendance($eId,$request)
		{
			if(!empty($request->session()->get('applied_month')))
			{
			
				$month = $request->session()->get('applied_month');
			}
			else
			{
				$month = date('m');
			}
			$empdetails = EmployeeAttendanceModel::whereMonth("attendance_date",$month)->where("emp_id",$eId)->where("attendance_value","A")->selectraw("count(id) as totalAttendance,emp_id")->groupBy('emp_id')->first();
			 if(!empty($empdetails))
			{
				$totalAttendance = $empdetails->totalAttendance;
			}
			else
			{
				 $totalAttendance = 0;
			}
			return $totalAttendance;
		}
		
		public function LeaveDays($eId,$request)
		{
			if(!empty($request->session()->get('applied_month')))
			{
			
				$month = $request->session()->get('applied_month');
			}
			else
			{
				$month = date('m');
			}
			$empdetails = EmployeeAttendanceModel::whereMonth("attendance_date",$month)->where("emp_id",$eId)->where("attendance_value","L")->selectraw("count(id) as totalAttendance,emp_id")->groupBy('emp_id')->first();
			if(!empty($empdetails))
			{
				$totalAttendance = $empdetails->totalAttendance;
			}
			else
			{
				 $totalAttendance = 0;
			}
			return $totalAttendance;
		}
		
		public function HoliDays($request)
		{
			if(!empty($request->session()->get('applied_month')))
			{
			
				$month = $request->session()->get('applied_month');
			}
			else
			{
				$month = date('m');
			}
			
			$year = 2022;	
			$emp_obj = new EmployeeAttendanceModel();
			$empdetails = EmployeeAttendanceModel::whereMonth("attendance_date",$month)->where("attendance_value",'H')->selectraw("count(id) as totalHolidays")->first();
			 return $empdetails->totalHolidays;
		}
		
		public function ValidDays($request)
		{
			if(!empty($request->session()->get('applied_month')))
			{
			
				$month = $request->session()->get('applied_month');
			}
			else
			{
				$month = date('m');
			}
			$year = 2022;	
			$daysInMonth = $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
			/*
			*getting sunday
			*/
			 $sundays=0;
			$total_days=$daysInMonth;
			for($i=1;$i<=$total_days;$i++)
			{
				if(date('N',strtotime($year.'-'.$month.'-'.$i))==7)
				{	
					$sundays++;
				}
			}
			/*
			*getting sunday
			*/
			$validd = $daysInMonth - $sundays;
			
			/*
			*get Holiday in months
			*/
			
			$year = 2022;	
			$emp_obj = new EmployeeAttendanceModel();
			$empdetails = EmployeeAttendanceModel::whereMonth("attendance_date",$month)->where("attendance_value",'H')->selectraw("count(id) as totalHolidays")->first();
			/*
			*get Holiday in months
			*/
			$holidaysCount = $empdetails->totalHolidays;
			$newValidDays = $validd-$holidaysCount;
			return  $newValidDays;
		}
		
		public function sandwichProgress(Request $req)
		{
			
			$eid = $req->eid;
			$deptId = $req->deptId;
			$start = $req->selectFrom;
			$end = $req->selectTo;
			$dateRangeArray = $this->getDatesFromRangeSandwich($start, $end);
			
		/* 	 echo '<pre>';
			print_r($dateRangeArray);
			exit; */ 
			/*
			*check existance of attendance
			*start code
			*/
		/* 	echo $eid.'@'.$deptId;
			exit;
			 */
			$updateArray = array();
			foreach($dateRangeArray as $_dateRange)
			{
				$dateRangedateT = date('Y-m-d',strtotime($_dateRange));
				$attendanceExistCheck = EmployeeAttendanceModel::where("attendance_date",$dateRangedateT)
				->where("dept_id",$deptId)
				->where("emp_id",$eid)
				->first();
				
				if(!empty($attendanceExistCheck))
				{
					$emp_obj = EmployeeAttendanceModel::find($attendanceExistCheck->id);
					
					$emp_obj->over_ride_sandwich =1;
					$emp_obj->save();
					
				}
			}
			
			/*
			*check existance of attendance
			*start code
			*/
			
			/*
			*Apply sandwitch rules
			*start coding
			*/
			foreach($dateRangeArray as $_dateRange)
			{
				$emp_obj = new EmployeeAttendanceModel();
				$emp_obj->dept_id = $deptId;
				$emp_obj->emp_id = $eid;
				$emp_obj->attendance_date = date('Y-m-d',strtotime($_dateRange));
				$emp_obj->mark_attendance = 'sandwich';
				$emp_obj->over_ride_sandwich = 0;
				$emp_obj->created_by = $req->session()->get('EmployeeId');
				$emp_obj->save();
			}
			$req->session()->flash('message','Sandwich rules applied.');
			echo "Done";
			exit;
			/*
			*Apply sandwitch rules
			*start coding
			*/
		}
		public function leaveApprovalPanel(Request $req)
		{
			$eid = $req->empId;
			$selectFrom = $req->selectFrom;
			$selectTo = $req->selectTo;
			$selectFromSet = date('Y-m-d',strtotime($selectFrom));
			$selectToSet = date('Y-m-d',strtotime($selectTo));
			
			$empdetails = new Employee_details();
			$empdetailsListing = $empdetails->where("id",$eid)->first();
			$_departmentEmp = $this->EmpDepartment($eid);
			$employeeDetails['name'] =  $empdetailsListing->first_name.' '.$empdetailsListing->last_name;
			$employeeDetails['department'] =  $_departmentEmp;
			$employeeDetails['selectFrom'] =  $selectFrom;
			$employeeDetails['selectTo'] =  $selectTo;
			
			$employeeDetailsAsPerSelectedDates = EmployeeAttendanceModel::whereBetween('attendance_date',[$selectFromSet, $selectToSet])->where("emp_id",$eid)->where("mark_attendance","leave")->orderBy("id",'DESC')->get();
			
			
			$totalLeaveTaken  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_approved",2)->count();
			$leaveTypeCount = array();
			$leaveTypeCount['casual_leave']  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_type","casual_leave")->where("leave_approved",2)->count();
			$leaveTypeCount['annual_leave']  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_type","annual_leave")->where("leave_approved",2)->count();
			$leaveTypeCount['sick_leave']  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_type","sick_leave")->where("leave_approved",2)->count();
			$leaveTypeCount['public_holiday']  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_type","public_holiday")->where("leave_approved",2)->count();
			$leaveTypeCount['emergency_leave']  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_type","emergency_leave")->where("leave_approved",2)->count();
			$leaveTypeCount['half_day']  = EmployeeAttendanceModel::where("emp_id",$eid)->where("mark_attendance","leave")->where("leave_type","half_day")->where("leave_approved",2)->count();
			
			
			return view("Employee/leaveApprovalPanel",compact('employeeDetails','employeeDetailsAsPerSelectedDates','totalLeaveTaken','leaveTypeCount'));
		}
		
		public function leaveApproved(Request $req)
		{
		
			$attendanceId = $req->attendanceId;
			$attendanceObj = EmployeeAttendanceModel::find($attendanceId);
			$attendanceData = EmployeeAttendanceModel::where("id",$attendanceId)->first();
			
			$currentYear = date("Y",strtotime($attendanceData->attendance_date));
			
			if($attendanceData->leave_type == 'annual_leave')
			{
				
			$annualLeaveMainData =	AnnualLeave::where("emp_id",$attendanceObj->emp_id)->where("year",$currentYear)->where("settlement_status",1)->first();
			
			if($annualLeaveMainData != '')
			{
				if($annualLeaveMainData->remaining_leave > 0)
				{
					$attendanceObj->leave_approved = 2;
					$attendanceObj->save();
					/*
					*Approval Of leave
					*Employee Leave Approval
					*/
					$annualMod = new AnnualLeaveDetails();
					$annualMod->emp_id = $attendanceObj->emp_id;
					$annualMod->leave_date = $attendanceObj->attendance_date;
					$annualMod->approved_by =$req->session()->get('EmployeeId');
					$annualMod->save();
					$annualLeaveMain = AnnualLeave::find($annualLeaveMainData->id);
					$annualLeaveMain->leave_taken = $annualLeaveMainData->leave_taken+1;
					$leaveTaken = $annualLeaveMainData->leave_taken+1;
					$annualLeaveMain->remaining_leave = $annualLeaveMainData->remaining_leave-1;
					
					$annualLeaveMain->save();
					/*
					*Approval Of leave
					*Employee Leave Approval
					*/
					
						$req->session()->flash('message','Leave Approved Successfully.');
						return  redirect()->back();
				}
				else
				{
					$req->session()->flash('message','No annual leave are remaining of this employee. So you can not approve.');
						return  redirect()->back();
				}
			}
			else
			{
				$req->session()->flash('message','Leave Data is not generated for this employee. This is an technical issue. Please contact to technical team.');
				return  redirect()->back();
			}
			}
			else
			{
				$attendanceObj->leave_approved = 2;
					$attendanceObj->save();
					$req->session()->flash('message','Leave Approved Successfully.');
						return  redirect()->back();
			}
		}
		
		public function leaveDisApproved(Request $req)
		{
			$attendanceId = $req->attendanceId;
			$attendanceObj = EmployeeAttendanceModel::find($attendanceId);
			$attendanceObj->leave_approved = 3;
			$attendanceObj->save();
			$req->session()->flash('message','Leave disapproved Successfully.');
			return  redirect()->back();
		}
		
		public function filledEmps(Request $req)
		{
			$deptId = $req->deptId;
			/* echo $deptId;
			exit; */
			$empLists = Employee_details::where("dept_id",$deptId)->get();
			$listofEmpId = array();
			foreach($empLists as $_emp)
			{
				$listofEmpId[$_emp->emp_id] = $_emp->emp_id;
			}
			return view("Employee/filledEmps",compact('listofEmpId'));
		}
		
		public function appliedFilterOnEMPList(Request $request)
		{
			$selectedFilter = $request->input();
			
			if(!empty($selectedFilter['deptID']))
			{
				$request->session()->put('deptID',$selectedFilter['deptID']);
			}
			return redirect('listEmp');
		}
		
		public function resetEmpdepartmentFilter(Request $request)
		{
			$request->session()->put('deptID','');
			return redirect('listEmp');
		}
		
		
		
		public function empUpdateNew_bak(Request $request)
		{
			$filename = 'Employee-update_15Jan2023.csv';
			$uploadPath = '/srv/www/htdocs/hrm/public/uploads/empImport/';
			$fullpathFileName = $uploadPath . $filename;
			$file = fopen($fullpathFileName, "r");
			$i = 1;
			$dataFromCsv = array();
			while (!feof($file)) {

				$dataFromCsv[$i] = fgetcsv($file);

				$i++;
			}

			fclose($file);
			/* echo '<pre>';
			print_r($dataFromCsv);
			exit;  */
			$empdetails = new Employee_details();
			$empAttrMod = new Employee_attribute();
			$iCsv = 0;
			$iCsvIndex = 0;
			$arrayDat = array();
			$arrayDatAttribute = array();
			 /* echo '<pre>';
			print_r($dataFromCsv);
			exit;   */
			$valuesCheck = array();
			foreach ($dataFromCsv as $fromCsv) {
				if ($iCsv != 0 && $fromCsv[1] != '') {
					/* echo '<pre>';
					print_r($fromCsv);
					exit; */
					/* $arrayDat[$iCsv]['emp_id'] = $fromCsv[0];
					$arrayDat[$iCsv]['dept_id'] = $fromCsv[1];
					$arrayDat[$iCsv]['onboarding_status'] = $fromCsv[2];
					$arrayDat[$iCsv]['first_name'] = trim($fromCsv[3]);
					$arrayDat[$iCsv]['middle_name'] = trim($fromCsv[4]);
					$arrayDat[$iCsv]['last_name'] = trim($fromCsv[5]);
					$arrayDat[$iCsv]['source_code'] = trim($fromCsv[32]);
					$arrayDat[$iCsv]['basic_salary'] = round(trim($fromCsv[23]),2);
					$arrayDat[$iCsv]['others_mol'] = round(trim($fromCsv[24]),2);
					$arrayDat[$iCsv]['gross_mol'] = round(trim($fromCsv[25]),2);
					$arrayDat[$iCsv]['actual_salary'] = round(trim($fromCsv[33]),2);
					$arrayDat[$iCsv]['status'] = 1; */
					$empIDValue = $fromCsv[0];
					$dept_id = Employee_details::where("emp_id",$empIDValue)->first()->dept_id;
					/*
					*LOC_ADD
					*/
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $dept_id;
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'company_name_issue_issued';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[1]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					
					$iCsvIndex++;
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $dept_id;
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'company_code_payroll';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[2]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					
					
					$iCsvIndex++;
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $dept_id;
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'category_payroll';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[3]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					
					
					$iCsvIndex++;
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $dept_id;
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'personname_as_per_mol_payroll';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[5]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $dept_id;
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'status_payroll';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[10]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $dept_id;
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'date_payroll';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[11]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					$employeeAttrDeleteMod = Employee_attribute::where('emp_id',$empIDValue)->where("attribute_code",'person_code')->first();
					
					if($employeeAttrDeleteMod != '')
					{
						 $rowId = $employeeAttrDeleteMod->id;
						// $employeeAttrDeleteMod->delete();
					}
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $dept_id;
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'person_code';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[4]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					$employeeAttrDeleteMod = Employee_attribute::where('emp_id',$empIDValue)->where("attribute_code",'PERMOL')->first();
					
					if($employeeAttrDeleteMod != '')
					{
						 $rowId = $employeeAttrDeleteMod->id;
						 //$employeeAttrDeleteMod->delete();
					}
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $dept_id;
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'PERMOL';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[6]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					$employeeAttrDeleteMod = Employee_attribute::where('emp_id',$empIDValue)->where("attribute_code",'PP_NO')->first();
					
					if($employeeAttrDeleteMod != '')
					{
						 $rowId = $employeeAttrDeleteMod->id;
						 //$employeeAttrDeleteMod->delete();
					}
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $dept_id;
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'PP_NO';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[7]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					$employeeAttrDeleteMod = Employee_attribute::where('emp_id',$empIDValue)->where("attribute_code",'NAT')->first();
					
					if($employeeAttrDeleteMod != '')
					{
						 $rowId = $employeeAttrDeleteMod->id;
						 //$employeeAttrDeleteMod->delete();
					}
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $dept_id;
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'NAT';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[8]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					$employeeAttrDeleteMod = Employee_attribute::where('emp_id',$empIDValue)->where("attribute_code",'LC_Number')->first();
					
					if($employeeAttrDeleteMod != '')
					{
						 $rowId = $employeeAttrDeleteMod->id;
						// $employeeAttrDeleteMod->delete();
					}
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $dept_id;
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'LC_Number';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[9]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					$iCsvIndex++;
					
					
					
					
									
					
				}
				$iCsv++;
			}
			
			//$empdetails->insert($arrayDat);
			//$empAttrMod->insert($arrayDatAttribute); 
			echo '<pre>';
			print_r($arrayDatAttribute);
			exit;  
			echo "yes - DONE- Rahul";
			exit;
			
		}
		
		
		
		public function empUpdateNew_bak1Feb(Request $request)
		{
			$filename = 'Employee-update_1Feb2023.csv';
			$uploadPath = '/srv/www/htdocs/hrm/public/uploads/empImport/';
			$fullpathFileName = $uploadPath . $filename;
			$file = fopen($fullpathFileName, "r");
			$i = 1;
			$dataFromCsv = array();
			while (!feof($file)) {

				$dataFromCsv[$i] = fgetcsv($file);

				$i++;
			}

			fclose($file);
			/*  echo '<pre>';
			print_r($dataFromCsv);
			exit;   */
			$empdetails = new Employee_details();
			$empAttrMod = new Employee_attribute();
			$iCsv = 0;
			$iCsvIndex = 0;
			$arrayDat = array();
			$arrayDatAttribute = array();
			 /* echo '<pre>';
			print_r($dataFromCsv);
			exit;   */
			$valuesCheck = array();
			foreach ($dataFromCsv as $fromCsv) {
				if ($iCsv != 0 && $fromCsv[1] != '') {
					/* echo '<pre>';
					print_r($fromCsv);
					exit; */
					/* $arrayDat[$iCsv]['emp_id'] = $fromCsv[0];
					$arrayDat[$iCsv]['dept_id'] = $fromCsv[1];
					$arrayDat[$iCsv]['onboarding_status'] = $fromCsv[2];
					$arrayDat[$iCsv]['first_name'] = trim($fromCsv[3]);
					$arrayDat[$iCsv]['middle_name'] = trim($fromCsv[4]);
					$arrayDat[$iCsv]['last_name'] = trim($fromCsv[5]);
					$arrayDat[$iCsv]['source_code'] = trim($fromCsv[32]);
					$arrayDat[$iCsv]['basic_salary'] = round(trim($fromCsv[23]),2);
					$arrayDat[$iCsv]['others_mol'] = round(trim($fromCsv[24]),2);
					$arrayDat[$iCsv]['gross_mol'] = round(trim($fromCsv[25]),2);
					$arrayDat[$iCsv]['actual_salary'] = round(trim($fromCsv[33]),2);
					$arrayDat[$iCsv]['status'] = 1; */
					$empIDValue = $fromCsv[0];
					$dept_id = Employee_details::where("emp_id",$empIDValue)->first()->dept_id;
					/*
					*LOC_ADD
					*/
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $dept_id;
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'EBN';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[1]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					
					$iCsvIndex++;
					
					$arrayDatAttribute[$iCsvIndex]['dept_id'] = $dept_id;
					$arrayDatAttribute[$iCsvIndex]['emp_id'] = $fromCsv[0];
					$arrayDatAttribute[$iCsvIndex]['attribute_code'] = 'EMP_IBAN';
					$arrayDatAttribute[$iCsvIndex]['attribute_values'] = trim($fromCsv[2]);
					$arrayDatAttribute[$iCsvIndex]['status'] = 1;
					
					
					
					$iCsvIndex++;
					
					
					
					
									
					
				}
				$iCsv++;
			}
			
			//$empdetails->insert($arrayDat);
			//$empAttrMod->insert($arrayDatAttribute); 
			/* echo '<pre>';
			print_r($arrayDatAttribute);
			exit;   */
			echo "yes - DONE- Rahul";
			exit;
			
		}
		public function updateVintageEMP(Request $req){
		 $dateC = date("Y-m-d");
		 
		 $Collection  = Employee_details::whereDate("vintage_updated_date","<",$dateC)->get();
		 if(count($Collection)>0)
			{
			foreach($Collection as $_coll)
			{
				$empId=$_coll->emp_id;
				$dept_id=$_coll->dept_id;
				//$details = Employee_details::where("id",$_coll->id)->first();
				
				/*update Obj*/
				$updateOBJ = Employee_details::find($_coll->id);
				/*update Obj*/	
			$empattributesMod = Employee_attribute::where('emp_id',$empId)->where('attribute_code','DOJ')->where('dept_id',$dept_id)->first();
				 //print_r($empattributesMod);exit;
				if(!empty($empattributesMod)){				 
				$createdAT = $empattributesMod->attribute_values;
				}else{
				$createdAT=0;
				}
				/*				
				$days INterbakl
				
				*/
				$doj = str_replace("/","-",$createdAT);//exit;
				//$date1 = date("Y-m-d",strtotime($doj));
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
	public function workLocationEMP(Request $req){
		 $Collection  = Employee_details::get();
		 if(count($Collection)>0)
			{
			foreach($Collection as $_coll)
			{
				$empId=$_coll->emp_id;
				$dept_id=$_coll->dept_id;
				//$details = Employee_details::where("id",$_coll->id)->first();
				
				/*update Obj*/
				$updateOBJ = Employee_details::find($_coll->id);
				/*update Obj*/	
			$empattributesMod = Employee_attribute::where('emp_id',$empId)->where('attribute_code','work_location')->where('dept_id',$dept_id)->first();
				 //print_r($empattributesMod);exit;
				if(!empty($empattributesMod)){				 
				$location = strtolower($empattributesMod->attribute_values);
				}else{
				$location='';
				}
				
				$updateOBJ->work_location = $location;
				$updateOBJ->save();
				
			}
			}
			else
			{
				echo "All DONe";
				exit;
			}
	
	}
			public function AjaxEmpListenbd(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('offset_emp_filter')))
				{
					$paginationValue = $request->session()->get('offset_emp_filter');
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
					$empdetails = Employee_details::where("dept_id",9)->paginate($paginationValue);	
					$reportsCountenbd = Employee_details::where("dept_id",9)->get()->count();
					$activeCountenbd = Employee_details::where("dept_id",9)->where('status',1)->get()->count();
					$inactiveCountenbd = Employee_details::where("dept_id",9)->where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('empid_emp_filter_inner_list')) && $request->session()->get('empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('fname_emp_filter_inner_list')) && $request->session()->get('fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('lname_emp_filter_inner_list')) && $request->session()->get('lname_emp_filter_inner_list') != 'All')
				{
					$lname = $request->session()->get('lname_emp_filter_inner_list');
					 $selectedFilter['l_name'] = $lname;
					 if($whereraw == '')
					{
						$whereraw = 'last_name like "%'.$lname.'%"';
					}
					else
					{
						$whereraw .= ' And last_name like "%'.$lname.'%"';
					}
				}
				if(!empty($request->session()->get('design_emp_filter_inner_list')) && $request->session()->get('design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('jobfunction_emp_filter_inner_list')) && $request->session()->get('jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('RecruiterName_emp_filter_inner_list')) && $request->session()->get('RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
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
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					
					 $locationArray = explode(",",$location);
					 $finallocationArray=array();
					 foreach($locationArray as $_locationArray){
						 $finallocationArray[]="'".$_locationArray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $finallocationArray);
					 if($whereraw == '')
					{
						$whereraw = 'work_location IN('.$finalloc.')';
					}
					else
					{
						$whereraw .= ' And work_location IN('.$finalloc.')';
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				
				
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderByRaw("-vintage_days DESC")->whereRaw($whereraw)->where("dept_id",9)->where("offline_status",1)->paginate($paginationValue);
				$reportsCountebbd = Employee_details::whereRaw($whereraw)->where("dept_id",9)->where("offline_status",1)->get()->count();
					$reportsCountenbd = Employee_details::whereRaw($whereraw)->where("dept_id",9)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountenbd = Employee_details::whereRaw($whereraw)->where("dept_id",9)->where("offline_status",1)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::where("dept_id",9)->where("offline_status",1)->orderByRaw("-vintage_days DESC")->paginate($paginationValue);
					$reportsCountenbd = Employee_details::where("dept_id",9)->where("offline_status",1)->get()->count();	
					$activeCountenbd = Employee_details::where("dept_id",9)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountenbd = Employee_details::where("dept_id",9)->where("offline_status",1)->where('status',2)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/AjaxEmpListenbd'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));

			$exportemployeestatus=ExportEmployeeStatus::where('id',1)->first();
			
			return view("EmpProcess/AjaxEmpListenbd",compact('empdetails','paginationValue','departmentLists','deptID','reportsCountenbd','activeCountenbd','inactiveCountenbd','exportemployeestatus'));
		}
		public function AjaxEmpListdeem(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('offset_emp_filter')))
				{
					$paginationValue = $request->session()->get('offset_emp_filter');
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
					$empdetails = Employee_details::paginate($paginationValue);	
					$reportsCount = Employee_details::where("dept_id",8)->where("offline_status",1)->get()->count();
					$activeCount = Employee_details::where("dept_id",8)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCount = Employee_details::where("dept_id",8)->where("offline_status",1)->where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('empid_emp_filter_inner_list')) && $request->session()->get('empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('fname_emp_filter_inner_list')) && $request->session()->get('fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('lname_emp_filter_inner_list')) && $request->session()->get('lname_emp_filter_inner_list') != 'All')
				{
					$lname = $request->session()->get('lname_emp_filter_inner_list');
					 $selectedFilter['l_name'] = $lname;
					 if($whereraw == '')
					{
						$whereraw = 'last_name like "%'.$lname.'%"';
					}
					else
					{
						$whereraw .= ' And last_name like "%'.$lname.'%"';
					}
				}
				if(!empty($request->session()->get('design_emp_filter_inner_list')) && $request->session()->get('design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('jobfunction_emp_filter_inner_list')) && $request->session()->get('jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('RecruiterName_emp_filter_inner_list')) && $request->session()->get('RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
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
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					
					 $locationArray = explode(",",$location);
					 $finallocationArray=array();
					 foreach($locationArray as $_locationArray){
						 $finallocationArray[]="'".$_locationArray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $finallocationArray);
					 if($whereraw == '')
					{
						$whereraw = 'work_location IN('.$finalloc.')';
					}
					else
					{
						$whereraw .= ' And work_location IN('.$finalloc.')';
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				$empIdArray = array();
				if($whereraw == '')
				{
				$appidGet = Employee_details::where("dept_id",8)->get();
				}
				else
				{
					
					$appidGet = Employee_details::whereRaw($whereraw)->where("dept_id",8)->get();
					
				}
				
				foreach($appidGet as $_d)
				{
					if($_d->emp_id != NULL && $_d->emp_id != '')
					{
						$empIdArray[$_d->emp_id] = $_d->emp_id;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				$f_nameArray = array();
				if($whereraw == '')
				{
				$f_namedata = Employee_details::where("dept_id",8)->get();
				}
				else
				{
					
					$f_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",8)->get();
					
				}
				
				foreach($f_namedata as $_f)
				{
					//echo $_f->first_name;exit;
					$f_nameArray[$_f->first_name] = $_f->first_name;
				}
				//print_r();exit;
				$l_nameArray = array();
				if($whereraw == '')
				{
				$l_namedata = Employee_details::where("dept_id",8)->get();
				}
				else
				{
					
					$l_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",8)->get();
					
				}
				
				foreach($l_namedata as $_lname)
				{
					//echo $_lname->last_name;exit;
					$l_nameArray[$_lname->last_name] = $_lname->last_name;
				}
				
				$departmentArray = array();
				if($whereraw == '')
				{
						$department = Department::where("status",1)->orderBy('id','DESC')->get();
				}
				else
				{
					$department =Department::where("status",1)->orderBy('id','DESC')->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$departmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$sourcecodeArray = array();
				if($whereraw == '')
				{
				$soursecode= Employee_details::where("dept_id",8)->get();
				}
				else
				{
					
					$soursecode = Employee_details::whereRaw($whereraw)->where("dept_id",8)->get();
					
				}
				
				foreach($soursecode as $_scode)
				{
					//echo $_lname->last_name;exit;
					$sourcecodeArray[$_scode->source_code] = $_scode->source_code;
				}
				
				$designationArray = array();
				if($whereraw == '')
				{
				$designation= Employee_details::where("dept_id",8)->get();
				}
				else
				{
					
					$designation = Employee_details::whereRaw($whereraw)->where("dept_id",8)->get();
					
				}
				
				foreach($designation as $_designation)
				{
					//echo $_lname->last_name;exit;
					if(!empty($_designation->job_role)){
					$designationArray[$_designation->job_role] = $_designation->job_role;
					}
				}
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = Employee_details::where("dept_id",8)->orderBy("id", "DESC")->get();
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
					//$Vintage = DocumentCollectionDetails::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = Employee_details::whereRaw($whereraw)->where("dept_id",8)->orderBy("id", "DESC")->get();
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
				
				
				$LocationArray = array();
				if($whereraw == '')
				{
				$loc= Employee_details::where("dept_id",8)->get();
				}
				else
				{
					
					$loc = Employee_details::whereRaw($whereraw)->where("dept_id",8)->get();
					
				}
				
				foreach($loc as $_location)
				{
					//echo $_lname->last_name;exit;
					if($_location->work_location !=''){
					$LocationArray[$_location->work_location] = $_location->work_location;
					}
				}
				
				$VisaUnderCompany = array();
				if($whereraw == '')
				{
				//$empdata= Employee_details::get();
				$Collection  = Employee_details::where("dept_id",8)->get();
				if(!empty($Collection)){
				$empid=array();
				foreach($Collection as $_coll)
				{
					$empid[]=$_coll->emp_id;										
				}
	
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
				}
				}
				else
				{
					$Collection = Employee_details::whereRaw($whereraw)->where("dept_id",8)->get();
					$empid=array();
					foreach($Collection as $_coll)
					{
					$empid[]=$_coll->emp_id;					
					
					}
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
					 //print_r($empattributesMod);exit;
					
				}		
				
				//print_r($finaldata);
				if(!empty($empattributesMod)){
				foreach($empattributesMod as $_companyvisa)
				{
				
					if($_companyvisa->attribute_values=='-' || $_companyvisa->attribute_values=='' || $_companyvisa->attribute_values=='NULL'){
						
					}else{
					$VisaUnderCompany[$_companyvisa->attribute_values] = $_companyvisa->attribute_values;
					}
					
				}
				}
				
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderByRaw("-vintage_days DESC")->whereRaw($whereraw)->where("dept_id",8)->where("offline_status",1)->paginate($paginationValue);
				$reportsCountdeem = Employee_details::whereRaw($whereraw)->where("dept_id",8)->where("offline_status",1)->get()->count();
					$activeCountdeem = Employee_details::whereRaw($whereraw)->where("dept_id",8)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountdeem = Employee_details::whereRaw($whereraw)->where("dept_id",8)->where("offline_status",1)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::where("dept_id",8)->where("offline_status",1)->orderByRaw("-vintage_days DESC")->paginate($paginationValue);
					$reportsCountdeem = Employee_details::where("dept_id",8)->where("offline_status",1)->get()->count();	
					$activeCountdeem = Employee_details::where("dept_id",8)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountdeem = Employee_details::where("dept_id",8)->where("offline_status",1)->where('status',2)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/AjaxEmpListdeem'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));
			$exportemployeestatus=ExportEmployeeStatus::where('id',1)->first();
			
			return view("EmpProcess/AjaxEmpListdeem",compact('VisaUnderCompany','LocationArray','VintageArray','empdetails','paginationValue','departmentLists','deptID','reportsCountdeem','empIdArray','selectedFilter','f_nameArray','l_nameArray','departmentArray','sourcecodeArray','designationArray','activeCountdeem','inactiveCountdeem','exportemployeestatus'));
		}
		public function AjaxEmpListmashreq(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('offset_emp_filter')))
				{
					$paginationValue = $request->session()->get('offset_emp_filter');
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
					$empdetails = Employee_details::paginate($paginationValue);	
					$reportsCountmashreq = Employee_details::where("dept_id",36)->get()->count();
					$activeCountmashreq = Employee_details::where("dept_id",36)->where('status',1)->get()->count();
					$inactiveCountmashreq = Employee_details::where("dept_id",36)->where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('empid_emp_filter_inner_list')) && $request->session()->get('empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('fname_emp_filter_inner_list')) && $request->session()->get('fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('lname_emp_filter_inner_list')) && $request->session()->get('lname_emp_filter_inner_list') != 'All')
				{
					$lname = $request->session()->get('lname_emp_filter_inner_list');
					 $selectedFilter['l_name'] = $lname;
					 if($whereraw == '')
					{
						$whereraw = 'last_name like "%'.$lname.'%"';
					}
					else
					{
						$whereraw .= ' And last_name like "%'.$lname.'%"';
					}
				}
				if(!empty($request->session()->get('design_emp_filter_inner_list')) && $request->session()->get('design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('jobfunction_emp_filter_inner_list')) && $request->session()->get('jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('RecruiterName_emp_filter_inner_list')) && $request->session()->get('RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
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
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					
					 $locationArray = explode(",",$location);
					 $finallocationArray=array();
					 foreach($locationArray as $_locationArray){
						 $finallocationArray[]="'".$_locationArray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $finallocationArray);
					 if($whereraw == '')
					{
						$whereraw = 'work_location IN('.$finalloc.')';
					}
					else
					{
						$whereraw .= ' And work_location IN('.$finalloc.')';
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				$empIdArray = array();
				if($whereraw == '')
				{
				$appidGet = Employee_details::where("dept_id",36)->get();
				}
				else
				{
					
					$appidGet = Employee_details::whereRaw($whereraw)->where("dept_id",36)->get();
					
				}
				
				foreach($appidGet as $_d)
				{
					if($_d->emp_id != NULL && $_d->emp_id != '')
					{
						$empIdArray[$_d->emp_id] = $_d->emp_id;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				$f_nameArray = array();
				if($whereraw == '')
				{
				$f_namedata = Employee_details::where("dept_id",36)->get();
				}
				else
				{
					
					$f_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",36)->get();
					
				}
				
				foreach($f_namedata as $_f)
				{
					//echo $_f->first_name;exit;
					$f_nameArray[$_f->first_name] = $_f->first_name;
				}
				//print_r();exit;
				$l_nameArray = array();
				if($whereraw == '')
				{
				$l_namedata = Employee_details::where("dept_id",36)->get();
				}
				else
				{
					
					$l_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",36)->get();
					
				}
				
				foreach($l_namedata as $_lname)
				{
					//echo $_lname->last_name;exit;
					$l_nameArray[$_lname->last_name] = $_lname->last_name;
				}
				
				$departmentArray = array();
				if($whereraw == '')
				{
						$department = Department::where("status",1)->orderBy('id','DESC')->get();
				}
				else
				{
					$department =Department::where("status",1)->orderBy('id','DESC')->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$departmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$sourcecodeArray = array();
				if($whereraw == '')
				{
				$soursecode= Employee_details::where("dept_id",36)->get();
				}
				else
				{
					
					$soursecode = Employee_details::whereRaw($whereraw)->where("dept_id",36)->get();
					
				}
				
				foreach($soursecode as $_scode)
				{
					//echo $_lname->last_name;exit;
					$sourcecodeArray[$_scode->source_code] = $_scode->source_code;
				}
				
				$designationArray = array();
				if($whereraw == '')
				{
				$designation= Employee_details::where("dept_id",36)->get();
				}
				else
				{
					
					$designation = Employee_details::whereRaw($whereraw)->where("dept_id",36)->get();
					
				}
				
				foreach($designation as $_designation)
				{
					//echo $_lname->last_name;exit;
					if(!empty($_designation->job_role)){
					$designationArray[$_designation->job_role] = $_designation->job_role;
					}
				}
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = Employee_details::where("dept_id",36)->orderBy("id", "DESC")->get();
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
					//$Vintage = DocumentCollectionDetails::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = Employee_details::whereRaw($whereraw)->where("dept_id",36)->orderBy("id", "DESC")->get();
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
				
				
				$LocationArray = array();
				if($whereraw == '')
				{
				$loc= Employee_details::where("dept_id",36)->get();
				}
				else
				{
					
					$loc = Employee_details::whereRaw($whereraw)->where("dept_id",36)->get();
					
				}
				
				foreach($loc as $_location)
				{
					//echo $_lname->last_name;exit;
					if($_location->work_location !=''){
					$LocationArray[$_location->work_location] = $_location->work_location;
					}
				}
				
				$VisaUnderCompany = array();
				if($whereraw == '')
				{
				//$empdata= Employee_details::get();
				$Collection  = Employee_details::where("dept_id",36)->get();
				if(!empty($Collection)){
				$empid=array();
				foreach($Collection as $_coll)
				{
					$empid[]=$_coll->emp_id;										
				}
	
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
				}
				}
				else
				{
					$Collection = Employee_details::whereRaw($whereraw)->where("dept_id",36)->get();
					$empid=array();
					foreach($Collection as $_coll)
					{
					$empid[]=$_coll->emp_id;					
					
					}
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
					 //print_r($empattributesMod);exit;
					
				}		
				
				//print_r($finaldata);
				if(!empty($empattributesMod)){
				foreach($empattributesMod as $_companyvisa)
				{
				
					if($_companyvisa->attribute_values=='-' || $_companyvisa->attribute_values=='' || $_companyvisa->attribute_values=='NULL'){
						
					}else{
					$VisaUnderCompany[$_companyvisa->attribute_values] = $_companyvisa->attribute_values;
					}
					
				}
				}
				
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderByRaw("-vintage_days DESC")->whereRaw($whereraw)->where("dept_id",36)->where("offline_status",1)->paginate($paginationValue);
				$reportsCountmashreq = Employee_details::whereRaw($whereraw)->where("dept_id",36)->where("offline_status",1)->get()->count();
					$activeCountmashreq = Employee_details::whereRaw($whereraw)->where("dept_id",36)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountmashreq = Employee_details::whereRaw($whereraw)->where("dept_id",36)->where("offline_status",1)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::where("dept_id",36)->where("offline_status",1)->orderByRaw("-vintage_days DESC")->paginate($paginationValue);
					$reportsCountmashreq = Employee_details::where("dept_id",36)->where("offline_status",1)->get()->count();	
					$activeCountmashreq = Employee_details::where("dept_id",36)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountmashreq = Employee_details::where("dept_id",36)->where("offline_status",1)->where('status',2)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/AjaxEmpListmashreq'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));
			$exportemployeestatus=ExportEmployeeStatus::where('id',1)->first();
			
			return view("EmpProcess/AjaxEmpListmashreq",compact('VisaUnderCompany','LocationArray','VintageArray','empdetails','paginationValue','departmentLists','deptID','reportsCountmashreq','empIdArray','selectedFilter','f_nameArray','l_nameArray','departmentArray','sourcecodeArray','designationArray','activeCountmashreq','inactiveCountmashreq','exportemployeestatus'));
		}
		public function AjaxEmpListaafaq(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('offset_emp_filter')))
				{
					$paginationValue = $request->session()->get('offset_emp_filter');
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
					$empdetails = Employee_details::where("dept_id",43)->where("offline_status",1)->paginate($paginationValue);	
					$reportsCountaafaq = Employee_details::where("dept_id",43)->where("offline_status",1)->get()->count();
					$activeCountaafaq = Employee_details::where("dept_id",43)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountaafaq = Employee_details::where("dept_id",43)->where("offline_status",1)->where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('empid_emp_filter_inner_list')) && $request->session()->get('empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('fname_emp_filter_inner_list')) && $request->session()->get('fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('lname_emp_filter_inner_list')) && $request->session()->get('lname_emp_filter_inner_list') != 'All')
				{
					$lname = $request->session()->get('lname_emp_filter_inner_list');
					 $selectedFilter['l_name'] = $lname;
					 if($whereraw == '')
					{
						$whereraw = 'last_name like "%'.$lname.'%"';
					}
					else
					{
						$whereraw .= ' And last_name like "%'.$lname.'%"';
					}
				}
				if(!empty($request->session()->get('design_emp_filter_inner_list')) && $request->session()->get('design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('jobfunction_emp_filter_inner_list')) && $request->session()->get('jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('RecruiterName_emp_filter_inner_list')) && $request->session()->get('RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
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
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					
					 $locationArray = explode(",",$location);
					 $finallocationArray=array();
					 foreach($locationArray as $_locationArray){
						 $finallocationArray[]="'".$_locationArray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $finallocationArray);
					 if($whereraw == '')
					{
						$whereraw = 'work_location IN('.$finalloc.')';
					}
					else
					{
						$whereraw .= ' And work_location IN('.$finalloc.')';
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				$empIdArray = array();
				if($whereraw == '')
				{
				$appidGet = Employee_details::where("dept_id",43)->get();
				}
				else
				{
					
					$appidGet = Employee_details::whereRaw($whereraw)->where("dept_id",43)->get();
					
				}
				
				foreach($appidGet as $_d)
				{
					if($_d->emp_id != NULL && $_d->emp_id != '')
					{
						$empIdArray[$_d->emp_id] = $_d->emp_id;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				$f_nameArray = array();
				if($whereraw == '')
				{
				$f_namedata = Employee_details::where("dept_id",43)->get();
				}
				else
				{
					
					$f_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",43)->get();
					
				}
				
				foreach($f_namedata as $_f)
				{
					//echo $_f->first_name;exit;
					$f_nameArray[$_f->first_name] = $_f->first_name;
				}
				//print_r();exit;
				$l_nameArray = array();
				if($whereraw == '')
				{
				$l_namedata = Employee_details::where("dept_id",43)->get();
				}
				else
				{
					
					$l_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",43)->get();
					
				}
				
				foreach($l_namedata as $_lname)
				{
					//echo $_lname->last_name;exit;
					$l_nameArray[$_lname->last_name] = $_lname->last_name;
				}
				
				$departmentArray = array();
				if($whereraw == '')
				{
						$department = Department::where("status",1)->orderBy('id','DESC')->get();
				}
				else
				{
					$department =Department::where("status",1)->orderBy('id','DESC')->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$departmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$sourcecodeArray = array();
				if($whereraw == '')
				{
				$soursecode= Employee_details::where("dept_id",43)->get();
				}
				else
				{
					
					$soursecode = Employee_details::whereRaw($whereraw)->where("dept_id",43)->get();
					
				}
				
				foreach($soursecode as $_scode)
				{
					//echo $_lname->last_name;exit;
					$sourcecodeArray[$_scode->source_code] = $_scode->source_code;
				}
				
				$designationArray = array();
				if($whereraw == '')
				{
				$designation= Employee_details::where("dept_id",43)->get();
				}
				else
				{
					
					$designation = Employee_details::whereRaw($whereraw)->where("dept_id",43)->get();
					
				}
				
				foreach($designation as $_designation)
				{
					//echo $_lname->last_name;exit;
					if(!empty($_designation->job_role)){
					$designationArray[$_designation->job_role] = $_designation->job_role;
					}
				}
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = Employee_details::where("dept_id",43)->orderBy("id", "DESC")->get();
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
					//$Vintage = DocumentCollectionDetails::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = Employee_details::whereRaw($whereraw)->where("dept_id",43)->orderBy("id", "DESC")->get();
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
				
				
				$LocationArray = array();
				if($whereraw == '')
				{
				$loc= Employee_details::where("dept_id",43)->get();
				}
				else
				{
					
					$loc = Employee_details::whereRaw($whereraw)->where("dept_id",43)->get();
					
				}
				
				foreach($loc as $_location)
				{
					//echo $_lname->last_name;exit;
					if($_location->work_location !=''){
					$LocationArray[$_location->work_location] = $_location->work_location;
					}
				}
				
				$VisaUnderCompany = array();
				if($whereraw == '')
				{
				//$empdata= Employee_details::get();
				$Collection  = Employee_details::where("dept_id",43)->get();
				if(!empty($Collection)){
				$empid=array();
				foreach($Collection as $_coll)
				{
					$empid[]=$_coll->emp_id;										
				}
	
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
				}
				}
				else
				{
					$Collection = Employee_details::whereRaw($whereraw)->where("dept_id",43)->get();
					$empid=array();
					foreach($Collection as $_coll)
					{
					$empid[]=$_coll->emp_id;					
					
					}
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
					 //print_r($empattributesMod);exit;
					
				}		
				
				//print_r($finaldata);
				if(!empty($empattributesMod)){
				foreach($empattributesMod as $_companyvisa)
				{
				
					if($_companyvisa->attribute_values=='-' || $_companyvisa->attribute_values=='' || $_companyvisa->attribute_values=='NULL'){
						
					}else{
					$VisaUnderCompany[$_companyvisa->attribute_values] = $_companyvisa->attribute_values;
					}
					
				}
				}
				
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderByRaw("-vintage_days DESC")->whereRaw($whereraw)->where("dept_id",43)->where("offline_status",1)->paginate($paginationValue);
				$reportsCountaafaq = Employee_details::whereRaw($whereraw)->where("dept_id",43)->where("offline_status",1)->get()->count();
					$activeCountaafaq = Employee_details::whereRaw($whereraw)->where("dept_id",43)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountaafaq = Employee_details::whereRaw($whereraw)->where("dept_id",43)->where("offline_status",1)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::where("dept_id",43)->where("offline_status",1)->orderByRaw("-vintage_days DESC")->paginate($paginationValue);
					$reportsCountaafaq = Employee_details::where("dept_id",43)->where("offline_status",1)->get()->count();	
					$activeCountaafaq = Employee_details::where("dept_id",43)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountaafaq = Employee_details::where("dept_id",43)->where("offline_status",1)->where('status',2)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/AjaxEmpListaafaq'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));
			$exportemployeestatus=ExportEmployeeStatus::where('id',1)->first();
			
			return view("EmpProcess/AjaxEmpListaafaqList",compact('VisaUnderCompany','LocationArray','VintageArray','empdetails','paginationValue','departmentLists','deptID','reportsCountaafaq','empIdArray','selectedFilter','f_nameArray','l_nameArray','departmentArray','sourcecodeArray','designationArray','activeCountaafaq','inactiveCountaafaq','exportemployeestatus'));
		}		
		
		
		public function searchbyempName(Request $request)
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
			$request->session()->put('empid_emp_filter_inner_list',$empid);
			$request->session()->put('fname_emp_filter_inner_list',$fname);
			$request->session()->put('lname_emp_filter_inner_list',$lname);
			$request->session()->put('scode_emp_filter_inner_list',$source);
			$request->session()->put('location_emp_filter_inner_list',$location);
			$request->session()->put('design_emp_filter_inner_list',$designation);
			$request->session()->put('jobfunction_emp_filter_inner_list',$jobfunction);
			$request->session()->put('RecruiterName_emp_filter_inner_list',$RecruiterName);
			
			
			
			
			  redirect('AjaxEmpList');	
		}
		public function empFilterreset(Request $request)
		{
			
			$request->session()->put('fname_emp_filter_inner_list','');
			$request->session()->put('lname_emp_filter_inner_list','');
			$request->session()->put('scode_emp_filter_inner_list','');
			$request->session()->put('location_emp_filter_inner_list','');
			$request->session()->put('design_emp_filter_inner_list','');
			$request->session()->put('empid_emp_filter_inner_list','');
			$request->session()->put('jobfunction_emp_filter_inner_list','');
			$request->session()->put('RecruiterName_emp_filter_inner_list','');
			  redirect('AjaxEmpList');	
		}
		public function getdesigndata($deptId,$empId){
			$empdetails = Employee_details::where("emp_id",$empId)->first();
			$designationMod = Designation::where("department_id",$deptId)->where("status",1)->get();
			$design=Designation::where("tlsm",2)->where("department_id",$deptId)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
			$tL_details = Employee_details::whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->where("dept_id",$deptId)->orderBy("id","ASC")->get();
			$atl_details = Employee_details::whereIn("designation_by_doc_collection",$designarray)->where("job_function",3)->where("offline_status",1)->whereNotNull('tl_id')->where("dept_id",$deptId)->orderBy("id","ASC")->get();
			return view("EmpProcess/designationdropdown",compact('designationMod','empdetails','tL_details','atl_details'));
	
		}
		public static function getAttributeValuedept($dept_id){
			$departmentLists = Department::where("id",$dept_id)->first();
			if($departmentLists != '')
			  {
			  $devid=$departmentLists->divison_id;
			  $DivisonList = Divison::where("id",$devid)->first();
			  if($DivisonList!=''){
			  
			  return $DivisonList->divison_name;
			  }
			  else{
				 return ''; 
			  }
			  
		}
		}
		public function updateEmpwarningLetterDetails(Request $req)
		{
			//print_r($req->input());exit;
			//print_r($_FILES);exit;
			$inputData = $req->input();
			$num = $req->input('empid');
			$empData =Employee_details::where("emp_id",$num)->first();
			$empId = $empData->emp_id;
			$dept_id=$empData->dept_id;

			$empIdPadding = $empId;
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				
				if(!empty($req->file($key)))
				{
				$filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
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
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
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


			
			$attributesValues = $req->input();
			//echo "<pre>";
            //print_r($attributesValues);exit;			
			unset($attributesValues['_token']);
			unset($attributesValues['dept_id']);
			unset($attributesValues['onboarding_status']);
			unset($attributesValues['first_name']);
			unset($attributesValues['middle_name']);
			unset($attributesValues['last_name']);
			unset($attributesValues['_url']);
			unset($attributesValues['id']);
			unset($attributesValues['empid']);
			
			
			
			foreach($attributesValues as $key=>$value)
			{
				
				/*echo "<pre>";
				print_r($attributesValues);
				print_r($value);
			print_r($key);exit;*/
				if(in_array($key,$listOfAttribute))
				{
				
					if($filesAttributeInfo[$key] != '')
					{
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
					}
					
				}
				else{
				 if(!empty($value))
					{
					if($value != 'undefined')
				{
							$dpid = $dept_id;
							$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$key)
												->where('dept_id',$dept_id)
												->first();
												
							
							if(!empty($empattributesMod))
							{
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $key;
							$empattributes->attribute_values = $value;
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
							}
							else
							{
								$empattributes = new Employee_attribute();
								$empattributes->attribute_code = $key;
								$empattributes->attribute_values = $attributesValues[$key];
								$empattributes->status = 1;
								$empattributes->emp_id = $empIdPadding;
								$empattributes->dept_id = $dept_id;
								$empattributes->save();
							}
				} 
				}
				}
				
			}
			foreach($keys as $key)
			{
			if(in_array($key,$listOfAttribute))
				{
				if($filesAttributeInfo[$key] != '')
					{
						$dpid = $dept_id;
						$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
						$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
												->where('attribute_code',$attributes_code)
												->where('dept_id',$dept_id)
												->first();
						if(!empty($empattributesMod)){
							$attributes = Attributes::where("attribute_id",$key)->first();
							$attributes_code=$attributes->attribute_code;
							$empattributes = Employee_attribute::find($empattributesMod->id);
							$empattributes->attribute_code = $attributes_code;
							$empattributes->attribute_values = $filesAttributeInfo[$key];
							$empattributes->status = 1;
							$empattributes->emp_id = $empIdPadding;
							$empattributes->dept_id = $dept_id;
							$empattributes->save();
						}
						else{						
						$attributes = Attributes::where("attribute_id",$key)->first();
						$attributes_code=$attributes->attribute_code;
						
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $attributes_code;
						$empattributes->attribute_values = $filesAttributeInfo[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empId;
						$empattributes->dept_id = $dept_id;
						$empattributes->save();
						}
					}
				}
			}


			
			
			
						$response['code'] = '200';
					   $response['message'] = "Data Saved Successfully.";
					   $response['empid'] = $empIdPadding;
					   
			echo json_encode($response);
					   exit;
		}
		public static function getAttributeValuedesign($design){
			$designationMod = Designation::where("id",$design)->first();
			if($designationMod != '')
			  {
			  
			  return $designationMod->name;
			  }
			  else{
				 return ''; 
			  }
			  
		}
		public function getupdatedepartmentData($rowId){
			
			$empdetails =Employee_details::where("emp_id",$rowId)->first();
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			return view("EmpProcess/PopupFormDept",compact('rowId','empdetails','departmentLists'));
		}
		public function updateEmpDepartmentData(Request $request){
			 
			 $emp_id=$request->input('rowId');
			 $dept_id=$request->input('dept_id');
			 $bankGeneratedCode=$request->input('source_code');
			 $location=$request->input('work_location');
			 $total_gross_salary=$request->input('total_gross_salary');
			 $others_mol=$request->input('others_mol');
			 $team_lead=$request->input('team_lead');
			 $dpdata =Employee_details::where("emp_id",$emp_id)->first();
			 if($dpdata!=''){
				 $departmentMod = Department::where("id",$dpdata->dept_id)->first();
				 $deptname=$departmentMod->department_name;
				 //$deptdata=$dpdata->dept_id;
					$logObj = new EmpChangeLog();
					$logObj->emp_id =$emp_id;
					$logObj->change_attribute_value =$deptname;
					$logObj->change_attribute_name ="Department";					
					$logObj->createdBY=$request->session()->get('EmployeeId');
					$logObj->save();
			 }
			 $work_location = Employee_attribute::where('emp_id',$emp_id)->where('attribute_code','work_location')->first();
			 if($work_location!=''){
				 //$deptdata=$dpdata->dept_id;
					$workLocation = new EmpChangeLog();
					$workLocation->emp_id =$emp_id;
					$workLocation->change_attribute_value =$work_location->attribute_values;
					$workLocation->change_attribute_name ="Location";					
					$workLocation->createdBY=$request->session()->get('EmployeeId');
					$logObj->save();
			 }
			 $bankcode = Employee_attribute::where('emp_id',$emp_id)->where('attribute_code','source_code')->first();
			 if($bankcode!=''){
				 //$deptdata=$dpdata->dept_id;
					$logObjbank = new EmpChangeLog();
					$logObjbank->emp_id =$emp_id;
					$logObjbank->change_attribute_value =$bankcode->attribute_values;
					$logObjbank->change_attribute_name ="Bank Code";					
					$logObjbank->createdBY=$request->session()->get('EmployeeId');
					$logObjbank->save();
			 }
			 $teamlead =Employee_details::where("emp_id",$emp_id)->first();
					
					 if($teamlead!=''){
						 $tlid = $teamlead->tl_id;
						 $teamleaddpdata =Employee_details::where("id",$tlid)->first();
						 if($teamleaddpdata!='' || $teamleaddpdata!=NULL){
							 $TLname=$teamleaddpdata->first_name.' '.$teamleaddpdata->middle_name.' '.$teamleaddpdata->last_name;
						 }
						 else{
							 $TLname='NA';
						 }
						 
							$logObj = new EmpChangeLog();
							$logObj->emp_id =$emp_id;
							$logObj->change_attribute_value =$TLname;
							$logObj->change_attribute_name ="Previous Team lead";					
							$logObj->createdBY=$request->session()->get('EmployeeId');
							$logObj->save();
					 }
					 /*$total_gross_salary = Employee_attribute::where('emp_id',$emp_id)->where('attribute_code','total_gross_salary')->first();
					 if($total_gross_salary!=''){
						 //$deptdata=$dpdata->dept_id;
							$totalgrosssalary = new EmpChangeLog();
							$totalgrosssalary->emp_id =$emp_id;
							$totalgrosssalary->change_attribute_value =$total_gross_salary->attribute_values;
							$totalgrosssalary->change_attribute_name ="total gross salary";					
							$totalgrosssalary->createdBY=$request->session()->get('EmployeeId');
							$totalgrosssalary->save();
					 }*/
			 
			 //print_r($request->input());exit;
			 $empdetails =Employee_details::where("emp_id",$emp_id)->first();
			 $empdetails->dept_id=$dept_id;
			 $empdetails->tl_id=$team_lead;
			 $empdetails->save();
			$empattributesMod = Employee_attribute::where('emp_id',$emp_id)->get();							
			if(!empty($empattributesMod))
			{
			foreach($empattributesMod as $updatedept){
			$empattributes = Employee_attribute::find($updatedept->id);
			$empattributes->dept_id = $dept_id;
			$empattributes->save();
			}
			}
			// update bank code--------->
			$empAttrExist = Employee_attribute::where("emp_id",$emp_id)->where("attribute_code","source_code")->first();
					if($empAttrExist != '')
					{
						$updatesourcecode = Employee_attribute::find($empAttrExist->id);
						
					}
					else
					{
						$updatesourcecode = new Employee_attribute();
					}
					$updatesourcecode->dept_id = $dept_id;
					$updatesourcecode->emp_id = $emp_id;
					$updatesourcecode->attribute_code = 'source_code';
					$updatesourcecode->attribute_values = $bankGeneratedCode;
					$updatesourcecode->save();
					
			// update location--------->
			$empAttrExistlocation = Employee_attribute::where("emp_id",$emp_id)->where("attribute_code","work_location")->first();
					if($empAttrExistlocation != '')
					{
						$updateslocation = Employee_attribute::find($empAttrExistlocation->id);						
					}
					else
					{
						$updateslocation = new Employee_attribute();
					}
					$updateslocation->dept_id = $dept_id;
					$updateslocation->emp_id = $emp_id;
					$updateslocation->attribute_code = 'work_location';
					$updateslocation->attribute_values = $location;
					$updateslocation->save();		
			// update total_gross_salary --------->
			$empAttrExistgrosssalary = Employee_attribute::where("emp_id",$emp_id)->where("attribute_code","total_gross_salary")->first();
					if($empAttrExistgrosssalary != '')
					{
						$updatesgrosssalary = Employee_attribute::find($empAttrExistgrosssalary->id);						
					}
					else
					{
						$updatesgrosssalary = new Employee_attribute();
					}
					$updatesgrosssalary->dept_id = $dept_id;
					$updatesgrosssalary->emp_id = $emp_id;
					$updatesgrosssalary->attribute_code = 'total_gross_salary';
					$updatesgrosssalary->attribute_values = $total_gross_salary;
					$updatesgrosssalary->save();
					
			// update others_mol --------->
			$empAttrExistothers_mol = Employee_attribute::where("emp_id",$emp_id)->where("attribute_code","others_mol")->first();
					if($empAttrExistothers_mol != '')
					{
						$updatesothers_mol = Employee_attribute::find($empAttrExistothers_mol->id);						
					}
					else
					{
						$updatesothers_mol = new Employee_attribute();
					}
					$updatesothers_mol->dept_id = $dept_id;
					$updatesothers_mol->emp_id = $emp_id;
					$updatesothers_mol->attribute_code = 'others_mol';
					$updatesothers_mol->attribute_values = $others_mol;
					$updatesothers_mol->save();		
					
				$response['code'] = '200';
			   $response['message'] = "Data Saved Successfully.";
			   //$response['empid'] = $empIdPadding;
			   
				echo json_encode($response);
			   exit;
			
			
		 }
		 public function OfflineEMPData(Request $request){
			 
			// print_r($request->input());exit;wetwet
			 $docId=$request->input('rowId');
			 $onboarding_date=$request->input('onboarding_date');
			 $empdetails =Employee_details::where("emp_id",$docId)->first();
			 if($empdetails!=''){
			 $offlineObj=new EmpOffline();
			 $offlineObj->emp_id=$empdetails->emp_id;			 
			 $offlineObj->emp_name=$empdetails->first_name.' '.$empdetails->middle_name. ' '.$empdetails->last_name;
			 $offlineObj->tl_se=$empdetails->tl_id;			 
			 $offlineObj->designation=$empdetails->designation_by_doc_collection;
			 $offlineObj->department=$empdetails->dept_id;
			 $offlineObj->leaving_type=$request->input('leaving_type');
			 if($request->input('date_of_resign')!=''){
			 $offlineObj->last_working_day_resign=$request->input('date_of_resign');
			 }
			 $offlineObj->reasons_of_terminate=$request->input('reasons');
			 $empattributesMod = Employee_attribute::where('emp_id',$docId)->where('attribute_code','CONTACT_NUMBER')->first();
			 if($empattributesMod!=''){
				$offlineObj->mobile_no=$empattributesMod->attribute_values;
			 }else{
				 $offlineObj->mobile_no='';
			 }
			 $work_location = Employee_attribute::where('emp_id',$docId)->where('attribute_code','work_location')->first();
			 if($work_location!=''){
				 $offlineObj->location=$work_location->attribute_values;
			 }
			 else{
				 $offlineObj->location='';
			 }
			 $DOJ= Employee_attribute::where('emp_id',$docId)->where('attribute_code','DOJ')->first();
			 if($DOJ!=''){
				 $offlineObj->doj=$DOJ->attribute_values;
			 }
			 $documentAttributesDetails =DocumentCollectionDetails::where("id",$empdetails->document_collection_id)->first();
			 //print_r($documentAttributesDetails);exit;
			 
			 if($documentAttributesDetails!=''){
				$offlineObj->email=$documentAttributesDetails->email;
				$offlineObj->recruiter_name=$documentAttributesDetails->recruiter_name;
				$offlineObj->job_opening=$documentAttributesDetails->job_opening;
				$offlineObj->interview_id=$documentAttributesDetails->interview_id;
				$offlineObj->document_collection_id=$documentAttributesDetails->id;
			 }
			 $offlineObj->onboarding_date=$onboarding_date;
			 $offlineObj->condition_leaving=1;
			 $offlineObj->retain=2;
			 $offlineObj->exit_interview_approved=2;
			 $offlineObj->fnf_approved=2;
			 $offlineObj->offboard_email_status=1;
			 $offlineObj->created_by=$request->session()->get('EmployeeId');
			 if($offlineObj->save()){
			 
			 $updatedata = Employee_details::where("emp_id",$docId)->first();
			 $tldata=Employee_details::where("tl_id",$updatedata->id)->get();
			 if(count($tldata)>0){
				foreach($tldata as $_tldata){
				$empdeleteobj = Employee_details::find($_tldata->id);
				$empdeleteobj->tl_id=NULL;
				$empdeleteobj->save();
				} 
			 }
			 
			 $updateOBJ = Employee_details::where("emp_id",$docId)->first();
			 $updateOBJ->offboard_status=3;
			 //$updateOBJ->pre_offline_status=2;
			 
			 $updateOBJ->save();








			 // New code start for pdf

			 
				//$rowid = $request->rowid;
				$offEmpDetails = EmpOffline::where("emp_id",$empdetails->emp_id)->orderBy("id","DESC")->first();

				if($offEmpDetails->department)
				{
					$deptDetails = Department::where("id",$offEmpDetails->department)->orderBy("id","DESC")->first();

					if($deptDetails)
					{
						$deptName = $deptDetails->department_name;
					}
					else
					{
						$deptName = "--";
					}
				}


				if($offEmpDetails->designation)
				{
					$desigDetails = Designation::where("id",$offEmpDetails->designation)->orderBy("id","DESC")->first();

					if($desigDetails)
					{
						$desigName = $desigDetails->name;
					}
					else
					{
						$desigName = "--";
					}
				}

				$emailDetails = Employee_attribute::where("emp_id",$offEmpDetails->emp_id)->where("attribute_code","email")->orderBy("id","DESC")->first();

				if($emailDetails)
				{
					$email = $emailDetails->attribute_values;
				}
				else
				{
					$email = "--";
				}


				if($offEmpDetails->leaving_type==1)
				{
					$leaveType = "Resign";
				}
				elseif($offEmpDetails->leaving_type==2)
				{
					$leaveType = "Terminate";
				}
				elseif($offEmpDetails->leaving_type==6)
				{
					$leaveType = "Abscond";
				}
				else
				{
					$leaveType = "--";
				}


				

				
				
				
				
				
				$filepath = public_path('exitform/EXITFORM.pdf');
				$filepath2 = public_path('exitform/');
				
				$this->fpdf = new Fpdf;
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->AddPage();
				//$this->fpdf->Text(10, 10, "Hello World!"); 

				$imgpath1 = public_path('exitform/image-1.jpg');
				$imgpath2 = public_path('exitform/image-22.jpg');
				$imgpath3 = public_path('exitform/image-11.jpg');
				$imgpath4 = public_path('exitform/image-3.jpg');
				$imgpath5 = public_path('exitform/image-4.jpg');
				$imgpath7 = public_path('exitform/image_7.png');
				$imgpath8 = public_path('exitform/image_8.png');

				

				
				$x = 10;
				//$this->pageHeader($x,$sifDataOne);
				$y=30; 
				$this->fpdf->Image($imgpath1, 5, $y-30, 203);

				$this->fpdf->rect($x,$y+40,$x+90,10); //whole structure
				$this->fpdf->Text($x+2,$y+45,'Mr./ Mrs./ Ms.:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->emp_name)
				{
					$this->fpdf->Text($x+26,$y+45,$offEmpDetails->emp_name);
				}
				else
				{
					$this->fpdf->Text($x+26,$y+45,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line



				$this->fpdf->rect($x,$y+40,$x+185,10); //whole structure
				$this->fpdf->Text($x+102,$y+45,'Emp ID:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->emp_id)
				{
					$this->fpdf->Text($x+116,$y+45,$offEmpDetails->emp_id);
				}
				else
				{
					$this->fpdf->Text($x+116,$y+45,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line


				$this->fpdf->rect($x,$y+50,$x+90,10); //whole structure
				$this->fpdf->Text($x+2,$y+55,'Date of Resignation:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->date_of_resign)
				{
					$emp_resign = date("d M, Y", strtotime($offEmpDetails->date_of_resign));
					$this->fpdf->Text($x+35,$y+55,$emp_resign);
				}
				else
				{
					$this->fpdf->Text($x+35,$y+55,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line


				$this->fpdf->rect($x,$y+50,$x+185,10); //whole structure
				$this->fpdf->Text($x+102,$y+55,'Last Working Day:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->date_of_resign)
				{
					$this->fpdf->Text($x+132,$y+55,$offEmpDetails->date_of_resign);
				}
				else
				{
					$this->fpdf->Text($x+132,$y+55,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line







				$this->fpdf->rect($x,$y+60,$x+90,10); //whole structure
				$this->fpdf->Text($x+2,$y+65,'Department:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->department)
				{
					$this->fpdf->Text($x+23,$y+65,$deptName);
				}
				else
				{
					$this->fpdf->Text($x+45,$y+65,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line




				$this->fpdf->rect($x,$y+60,$x+185,10); //whole structure
				$this->fpdf->Text($x+102,$y+65,'Date of Joining:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->date_of_joining)
				{
					$emp_doj = date("d M, Y", strtotime($offEmpDetails->date_of_joining));
					$this->fpdf->Text($x+128,$y+65,$emp_doj);
				}
				else
				{
					$this->fpdf->Text($x+128,$y+65,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line







				$this->fpdf->rect($x,$y+70,$x+90,10); //whole structure
				$this->fpdf->Text($x+2,$y+75,'Designation:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->designation)
				{
					$this->fpdf->Text($x+23,$y+75,$desigName);
				}
				else
				{
					$this->fpdf->Text($x+23,$y+75,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line











				$this->fpdf->rect($x,$y+70,$x+185,10); //whole structure
				$this->fpdf->Text($x+102,$y+75,'Location:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->location)
				{
					$this->fpdf->Text($x+117,$y+75,$offEmpDetails->location);
				}
				else
				{
					$this->fpdf->Text($x+117,$y+75,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line











				$this->fpdf->rect($x,$y+80,$x+90,10); //whole structure
				$this->fpdf->Text($x+2,$y+85,'Personal Email id:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				
				$this->fpdf->Text($x+32,$y+85,$email);
				
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line







				$this->fpdf->rect($x,$y+80,$x+185,10); //whole structure
				$this->fpdf->Text($x+102,$y+85,'Official Email ID:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->date_of_resign)
				{
					$this->fpdf->Text($x+132,$y+85,$offEmpDetails->date_of_resign);
				}
				else
				{
					$this->fpdf->Text($x+132,$y+85,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line






				$this->fpdf->Image($imgpath7, 5, $y+92, 209);







				$this->fpdf->rect($x-1,$y+114,$x+71.7,8); //whole structure
				$this->fpdf->SetFont('Arial', '', 9);
				$this->fpdf->Text($x+8,$y+119,'1');
				$this->fpdf->line(21,144,21,166);//SN line
				$this->fpdf->SetFont('Arial', '', 9);
				$this->fpdf->Text($x+13,$y+119,'Reason for leaving.');



				$this->fpdf->rect($x-1,$y+114,$x+186,8); //whole structure	
				$this->fpdf->Text($x+82,$y+119,$leaveType);		
				$this->fpdf->SetFont('Arial', 'B', 9);	
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line



				$this->fpdf->rect($x-1,$y+122,$x+71.7,7); //whole structure
				$this->fpdf->SetFont('Arial', '', 9);
				$this->fpdf->Text($x+8,$y+127,'2');
				$this->fpdf->SetFont('Arial', '', 9);
				$this->fpdf->Text($x+13,$y+127,'No. of working days (current month)');

				$this->fpdf->rect($x-1,$y+122,$x+186,7); //whole structure	
				$this->fpdf->Text($x+82,$y+127,"Test Reason");			
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line







				$this->fpdf->rect($x-1,$y+129,$x+71.7,7); //whole structure
				$this->fpdf->SetFont('Arial', '', 9);
				$this->fpdf->Text($x+8,$y+134,'3');
				$this->fpdf->SetFont('Arial', '', 9);
				$this->fpdf->Text($x+13,$y+134,'All official documents/ Warning letters');

				$this->fpdf->rect($x-1,$y+129,$x+186,7); //whole structure
				$this->fpdf->Text($x+82,$y+134,"Test Reason");				
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line
				
				
				$this->fpdf->Image($imgpath8, 5, $y+135, 209);

				
				$filename=public_path('exitform/exitform-'.$offEmpDetails->id.'.pdf');
				$this->fpdf->Output($filename,'F');
				
				$newName = 'exitform-'.$offEmpDetails->id.'.pdf';
			  
				


				//return $newName;

				


			 // new code for pdf end


















			 }
			// 	$response['code'] = '200';
			//    $response['message'] = "Data Saved Successfully.";
			//    //$response['empid'] = $empIdPadding;
			//    $response['file'] = $newName;
			   
			// 	echo json_encode($response);
			//    exit;
			return $newName;
			 }
			
		 }
		public function deleteEmpdata(Request $req)
		{
			$emp_id =$req->id;
			$empdetails =Employee_details::where("emp_id",$emp_id)->first();
			$empdelete = Employee_details::find($empdetails->id);
			 if($empdelete->delete()){
			$empattributesMod = Employee_attribute::where('emp_id',$emp_id)->get();							
			if(!empty($empattributesMod))
			{
			foreach($empattributesMod as $updatedept){
			$empattributes = Employee_attribute::find($updatedept->id);
			
			$empattributes->delete();
			}
			}
			 }
        $req->session()->flash('message','Employee deleted Successfully.');
        return redirect('empProcess');
		//redirect('AjaxEmpList');
		}
				public function getLocationbasedDesign($location=NULL){
				if($location!=''){
					$location=explode(',',$location);
					}
					else{
						$location='';
					}
				$locationarra=array();
				foreach($location as $_location){
					if($_location=="ABU DHABI"){
						$locationarra[]="AUH";
					}
					else if($_location=="DUBAI"){
						$locationarra[]="DXB";
					}
					else if($_location=="Karachi"){
						$locationarra[]="Karachi";
					}
					else{
						$locationarra[]="NA";
					}
					
				}
			$jobOpning=JobOpening::whereIn('location',$locationarra)->where("status",1)->get();
			if($jobOpning!=''){
				$design=array();
				foreach($jobOpning as $_data){
				if($_data->designation!='' || $_data->designation!='NULL'){
				$design[]=$_data->designation;
				}
				
				}
			}
			//print_r($design);exit;
			$Designation=Designation::whereIn("id",$design)->get();
		
		return view("EmpProcess/Dropdownlocationbaseddesign",compact('Designation'));
	}
		public function getEMPViewDetails($opentab=NULL){
		$opentab=$opentab;
		if($opentab=='all'){
			$totelCount = Employee_details::where("offline_status",1)->get()->count();	
			$offboardCount = Employee_details::where("offline_status",2)->get()->count();
			$work_location = Employee_attribute::where('attribute_code','work_location')->get();
				if(!empty($work_location)){
					 $location=array();
					 foreach($work_location as $_location){
						 if(array_key_exists($_location->attribute_values, $location)) {
                           $location[$_location->attribute_values] +=1;
                        } else {
                           $location[$_location->attribute_values] =1;
                        }
					 }
				 
				}
				
				$design = Employee_details::get();
				if($design!=''){
					$Designation=array();
					foreach($design as $_design){
						if(array_key_exists($_design->designation_by_doc_collection, $Designation)) {
                           $Designation[$_design->designation_by_doc_collection] +=1;
                        } else {
                           $Designation[$_design->designation_by_doc_collection] =1;
                        }
					}
					
				}
			
				
				 //print_r($Designation);
		}
		elseif($opentab=='aafaq'){
			$totelCount = Employee_details::where("dept_id",43)->where("offline_status",1)->get()->count();	
			$offboardCount = Employee_details::where("dept_id",43)->where("offline_status",2)->get()->count();
			$work_location = Employee_attribute::where("dept_id",43)->where('attribute_code','work_location')->get();
				if(!empty($work_location)){
					 $location=array();
					 foreach($work_location as $_location){
						 if(array_key_exists($_location->attribute_values, $location)) {
                           $location[$_location->attribute_values] +=1;
                        } else {
                           $location[$_location->attribute_values] =1;
                        }
					 }
				 
				}
				
				
				$design = Employee_details::where("dept_id",43)->get();
				if($design!=''){
					$Designation=array();
					foreach($design as $_design){
						if(array_key_exists($_design->designation_by_doc_collection, $Designation)) {
                           $Designation[$_design->designation_by_doc_collection] +=1;
                        } else {
                           $Designation[$_design->designation_by_doc_collection] =1;
                        }
					}
					
				}
			
				
				 //print_r($Designation);
		}elseif($opentab=='mashreq'){
			$totelCount = Employee_details::where("dept_id",36)->where("offline_status",1)->get()->count();	
			$offboardCount = Employee_details::where("dept_id",36)->where("offline_status",2)->get()->count();
			$work_location = Employee_attribute::where("dept_id",36)->where('attribute_code','work_location')->get();
				if(!empty($work_location)){
					 $location=array();
					 foreach($work_location as $_location){
						 if(array_key_exists($_location->attribute_values, $location)) {
                           $location[$_location->attribute_values] +=1;
                        } else {
                           $location[$_location->attribute_values] =1;
                        }
					 }
				 
				}
				
				$design = Employee_details::where("dept_id",36)->get();
				if($design!=''){
					$Designation=array();
					foreach($design as $_design){
						if(array_key_exists($_design->designation_by_doc_collection, $Designation)) {
                           $Designation[$_design->designation_by_doc_collection] +=1;
                        } else {
                           $Designation[$_design->designation_by_doc_collection] =1;
                        }
					}
					
				}
			
				
				 //print_r($Designation);
		}
		elseif($opentab=='deem'){
			$totelCount = Employee_details::where("dept_id",8)->where("offline_status",1)->get()->count();	
			$offboardCount = Employee_details::where("dept_id",8)->where("offline_status",2)->get()->count();
			$work_location = Employee_attribute::where("dept_id",8)->where('attribute_code','work_location')->get();
				if(!empty($work_location)){
					 $location=array();
					 foreach($work_location as $_location){
						 if(array_key_exists($_location->attribute_values, $location)) {
                           $location[$_location->attribute_values] +=1;
                        } else {
                           $location[$_location->attribute_values] =1;
                        }
					 }
				 
				}
				
				$design = Employee_details::where("dept_id",8)->get();
				if($design!=''){
					$Designation=array();
					foreach($design as $_design){
						if(array_key_exists($_design->designation_by_doc_collection, $Designation)) {
                           $Designation[$_design->designation_by_doc_collection] +=1;
                        } else {
                           $Designation[$_design->designation_by_doc_collection] =1;
                        }
					}
					
				}
			
				
				 //print_r($Designation);
		}
		elseif($opentab=='enbd'){
			$totelCount = Employee_details::where("dept_id",9)->where("offline_status",1)->get()->count();	
			$offboardCount = Employee_details::where("dept_id",9)->where("offline_status",2)->get()->count();
			$work_location = Employee_attribute::where("dept_id",9)->where('attribute_code','work_location')->get();
				if(!empty($work_location)){
					 $location=array();
					 foreach($work_location as $_location){
						 if(array_key_exists($_location->attribute_values, $location)) {
                           $location[$_location->attribute_values] +=1;
                        } else {
                           $location[$_location->attribute_values] =1;
                        }
					 }
				 
				}
				
				$design = Employee_details::where("dept_id",9)->get();
				if($design!=''){
					$Designation=array();
					foreach($design as $_design){
						if(array_key_exists($_design->designation_by_doc_collection, $Designation)) {
                           $Designation[$_design->designation_by_doc_collection] +=1;
                        } else {
                           $Designation[$_design->designation_by_doc_collection] =1;
                        }
					}
					
				}
			
				
				 //print_r($Designation);
		}
		elseif($opentab=='dib'){
			$totelCount = Employee_details::where("dept_id",46)->where("offline_status",1)->get()->count();	
			$offboardCount = Employee_details::where("dept_id",46)->where("offline_status",2)->get()->count();
			$work_location = Employee_attribute::where("dept_id",46)->where('attribute_code','work_location')->get();
				if(!empty($work_location)){
					 $location=array();
					 foreach($work_location as $_location){
						 if(array_key_exists($_location->attribute_values, $location)) {
                           $location[$_location->attribute_values] +=1;
                        } else {
                           $location[$_location->attribute_values] =1;
                        }
					 }
				 
				}
				
				$design = Employee_details::where("dept_id",46)->get();
				if($design!=''){
					$Designation=array();
					foreach($design as $_design){
						if(array_key_exists($_design->designation_by_doc_collection, $Designation)) {
                           $Designation[$_design->designation_by_doc_collection] +=1;
                        } else {
                           $Designation[$_design->designation_by_doc_collection] =1;
                        }
					}
					
				}
			
				
				 //print_r($Designation);
		}
		elseif($opentab=='scb'){
			$totelCount = Employee_details::where("dept_id",47)->where("offline_status",1)->get()->count();	
			$offboardCount = Employee_details::where("dept_id",47)->where("offline_status",2)->get()->count();
			$work_location = Employee_attribute::where("dept_id",47)->where('attribute_code','work_location')->get();
				if(!empty($work_location)){
					 $location=array();
					 foreach($work_location as $_location){
						 if(array_key_exists($_location->attribute_values, $location)) {
                           $location[$_location->attribute_values] +=1;
                        } else {
                           $location[$_location->attribute_values] =1;
                        }
					 }
				 
				}
				
				$design = Employee_details::where("dept_id",47)->get();
				if($design!=''){
					$Designation=array();
					foreach($design as $_design){
						if(array_key_exists($_design->designation_by_doc_collection, $Designation)) {
                           $Designation[$_design->designation_by_doc_collection] +=1;
                        } else {
                           $Designation[$_design->designation_by_doc_collection] =1;
                        }
					}
					
				}
			
				
				 //print_r($Designation);
		}else{
			$totelCount='';
			$offboardCount='';
			$location='';
			$Designation='';
		}
		
		
		return view("EmpProcess/PopupViewDetails",compact('totelCount','offboardCount','location','Designation'));
	}
	public function AjaxEmpempLogData(Request $request){
			$id=$request->empId;
			$empdetails = EmpChangeLog::where("emp_id",$id)->get();
			return view("EmpProcess/AjaxEmpListLog",compact('empdetails'));
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
		public function getupdatedepartmentTL($rowId,$empId){
			
			
			$tL_details = Employee_details::where("job_role","Team Leader")->where("dept_id",$rowId)->orderBy("id","ASC")->get();
			
			$source_code = Employee_attribute::where('emp_id',$empId)->where('attribute_code','source_code')->first();
			//print_r($source_code);exit;
			$total_gross_salary = Employee_attribute::where('emp_id',$empId)->where('attribute_code','total_gross_salary')->first();
			$others_mol = Employee_attribute::where('emp_id',$empId)->where('attribute_code','others_mol')->first();
			return view("EmpProcess/DropdownTL",compact('tL_details','source_code','total_gross_salary','others_mol'));
		}
		
		public function getupdatedepartmentsalaryVal($rowId){
			$source_code = Employee_attribute::where('emp_id',$rowId)->where('attribute_code','source_code')->first();
			$total_gross_salary = Employee_attribute::where('emp_id',$rowId)->where('attribute_code','total_gross_salary')->first();
			$others_mol = Employee_attribute::where('emp_id',$rowId)->where('attribute_code','others_mol')->first();
			
			return view("EmpProcess/textboxfromdata",compact('source_code','total_gross_salary','others_mol'));
		}
// DIB
	public function AjaxEmpListDIBData(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('offset_emp_filter')))
				{
					$paginationValue = $request->session()->get('offset_emp_filter');
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
					$empdetails = Employee_details::where("dept_id",46)->paginate($paginationValue);	
					$reportsCountenbd = Employee_details::where("dept_id",46)->get()->count();
					$activeCountenbd = Employee_details::where("dept_id",46)->where('status',1)->get()->count();
					$inactiveCountenbd = Employee_details::where("dept_id",46)->where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('fname_emp_filter_inner_list')) && $request->session()->get('fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('fname_emp_filter_inner_list')) && $request->session()->get('fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('lname_emp_filter_inner_list')) && $request->session()->get('lname_emp_filter_inner_list') != 'All')
				{
					$lname = $request->session()->get('lname_emp_filter_inner_list');
					 $selectedFilter['l_name'] = $lname;
					 if($whereraw == '')
					{
						$whereraw = 'last_name like "%'.$lname.'%"';
					}
					else
					{
						$whereraw .= ' And last_name like "%'.$lname.'%"';
					}
				}
				if(!empty($request->session()->get('design_emp_filter_inner_list')) && $request->session()->get('design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('jobfunction_emp_filter_inner_list')) && $request->session()->get('jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('RecruiterName_emp_filter_inner_list')) && $request->session()->get('RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
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
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					
					 $locationArray = explode(",",$location);
					 $finallocationArray=array();
					 foreach($locationArray as $_locationArray){
						 $finallocationArray[]="'".$_locationArray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $finallocationArray);
					 if($whereraw == '')
					{
						$whereraw = 'work_location IN('.$finalloc.')';
					}
					else
					{
						$whereraw .= ' And work_location IN('.$finalloc.')';
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				$empIdArray = array();
				if($whereraw == '')
				{
				$appidGet = Employee_details::where("dept_id",9)->get();
				}
				else
				{
					
					$appidGet = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					
				}
				
				foreach($appidGet as $_d)
				{
					if($_d->emp_id != NULL && $_d->emp_id != '')
					{
						$empIdArray[$_d->emp_id] = $_d->emp_id;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				$f_nameArray = array();
				if($whereraw == '')
				{
				$f_namedata = Employee_details::where("dept_id",9)->get();
				}
				else
				{
					
					$f_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					
				}
				
				foreach($f_namedata as $_f)
				{
					//echo $_f->first_name;exit;
					$f_nameArray[$_f->first_name] = $_f->first_name;
				}
				//print_r();exit;
				$l_nameArray = array();
				if($whereraw == '')
				{
				$l_namedata = Employee_details::where("dept_id",9)->get();
				}
				else
				{
					
					$l_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					
				}
				
				foreach($l_namedata as $_lname)
				{
					//echo $_lname->last_name;exit;
					$l_nameArray[$_lname->last_name] = $_lname->last_name;
				}
				
				$departmentArray = array();
				if($whereraw == '')
				{
						$department = Department::where("status",1)->orderBy('id','DESC')->get();
				}
				else
				{
					$department =Department::where("status",1)->orderBy('id','DESC')->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$departmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$sourcecodeArray = array();
				if($whereraw == '')
				{
				$soursecode= Employee_details::where("dept_id",9)->get();
				}
				else
				{
					
					$soursecode = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					
				}
				
				foreach($soursecode as $_scode)
				{
					//echo $_lname->last_name;exit;
					$sourcecodeArray[$_scode->source_code] = $_scode->source_code;
				}
				
				$designationArray = array();
				if($whereraw == '')
				{
				$designation= Employee_details::where("dept_id",9)->get();
				}
				else
				{
					
					$designation = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					
				}
				
				foreach($designation as $_designation)
				{
					//echo $_lname->last_name;exit;
					if(!empty($_designation->job_role)){
					$designationArray[$_designation->job_role] = $_designation->job_role;
					}
				}
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = Employee_details::where("dept_id",9)->orderBy("id", "DESC")->get();
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
					//$Vintage = DocumentCollectionDetails::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = Employee_details::whereRaw($whereraw)->where("dept_id",9)->orderBy("id", "DESC")->get();
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
				
				
				$LocationArray = array();
				if($whereraw == '')
				{
				$loc= Employee_details::where("dept_id",9)->get();
				}
				else
				{
					
					$loc = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					
				}
				
				foreach($loc as $_location)
				{
					//echo $_lname->last_name;exit;
					if($_location->work_location !=''){
					$LocationArray[$_location->work_location] = $_location->work_location;
					}
				}
				
				$VisaUnderCompany = array();
				if($whereraw == '')
				{
				//$empdata= Employee_details::get();
				$Collection  = Employee_details::where("dept_id",9)->get();
				if(!empty($Collection)){
				$empid=array();
				foreach($Collection as $_coll)
				{
					$empid[]=$_coll->emp_id;										
				}
	
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
				}
				}
				else
				{
					$Collection = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					$empid=array();
					foreach($Collection as $_coll)
					{
					$empid[]=$_coll->emp_id;					
					
					}
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
					 //print_r($empattributesMod);exit;
					
				}		
				
				//print_r($finaldata);
				if(!empty($empattributesMod)){
				foreach($empattributesMod as $_companyvisa)
				{
				
					if($_companyvisa->attribute_values=='-' || $_companyvisa->attribute_values=='' || $_companyvisa->attribute_values=='NULL'){
						
					}else{
					$VisaUnderCompany[$_companyvisa->attribute_values] = $_companyvisa->attribute_values;
					}
					
				}
				}
				
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderByRaw("-vintage_days DESC")->whereRaw($whereraw)->where("dept_id",46)->where("offline_status",1)->paginate($paginationValue);
				$reportsCountdib = Employee_details::whereRaw($whereraw)->where("dept_id",46)->where("offline_status",1)->get()->count();
					$activeCountdib = Employee_details::whereRaw($whereraw)->where("dept_id",46)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountdib = Employee_details::whereRaw($whereraw)->where("dept_id",46)->where("offline_status",1)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::where("dept_id",46)->where("offline_status",1)->orderByRaw("-vintage_days DESC")->paginate($paginationValue);
					$reportsCountdib = Employee_details::where("dept_id",46)->where("offline_status",1)->get()->count();	
					$activeCountdib = Employee_details::where("dept_id",46)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountdib = Employee_details::where("dept_id",46)->where("offline_status",1)->where('status',2)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/AjaxEmpListDIBData'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));
			$exportemployeestatus=ExportEmployeeStatus::where('id',1)->first();
			
			return view("EmpProcess/AjaxEmpListDIB",compact('VisaUnderCompany','LocationArray','VintageArray','empdetails','paginationValue','departmentLists','deptID','reportsCountdib','empIdArray','selectedFilter','f_nameArray','l_nameArray','departmentArray','sourcecodeArray','designationArray','activeCountdib','inactiveCountdib','exportemployeestatus'));
		}
//SCB
			public function AjaxEmpListSCBData(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('offset_emp_filter')))
				{
					$paginationValue = $request->session()->get('offset_emp_filter');
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
					$empdetails = Employee_details::where("dept_id",47)->paginate($paginationValue);	
					$reportsCountenbd = Employee_details::where("dept_id",47)->get()->count();
					$activeCountenbd = Employee_details::where("dept_id",47)->where('status',1)->get()->count();
					$inactiveCountenbd = Employee_details::where("dept_id",47)->where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('empid_emp_filter_inner_list')) && $request->session()->get('empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('fname_emp_filter_inner_list')) && $request->session()->get('fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('lname_emp_filter_inner_list')) && $request->session()->get('lname_emp_filter_inner_list') != 'All')
				{
					$lname = $request->session()->get('lname_emp_filter_inner_list');
					 $selectedFilter['l_name'] = $lname;
					 if($whereraw == '')
					{
						$whereraw = 'last_name like "%'.$lname.'%"';
					}
					else
					{
						$whereraw .= ' And last_name like "%'.$lname.'%"';
					}
				}
				if(!empty($request->session()->get('design_emp_filter_inner_list')) && $request->session()->get('design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('jobfunction_emp_filter_inner_list')) && $request->session()->get('jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('RecruiterName_emp_filter_inner_list')) && $request->session()->get('RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
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
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					
					 $locationArray = explode(",",$location);
					 $finallocationArray=array();
					 foreach($locationArray as $_locationArray){
						 $finallocationArray[]="'".$_locationArray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $finallocationArray);
					 if($whereraw == '')
					{
						$whereraw = 'work_location IN('.$finalloc.')';
					}
					else
					{
						$whereraw .= ' And work_location IN('.$finalloc.')';
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				$empIdArray = array();
				if($whereraw == '')
				{
				$appidGet = Employee_details::where("dept_id",9)->get();
				}
				else
				{
					
					$appidGet = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					
				}
				
				foreach($appidGet as $_d)
				{
					if($_d->emp_id != NULL && $_d->emp_id != '')
					{
						$empIdArray[$_d->emp_id] = $_d->emp_id;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				$f_nameArray = array();
				if($whereraw == '')
				{
				$f_namedata = Employee_details::where("dept_id",9)->get();
				}
				else
				{
					
					$f_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					
				}
				
				foreach($f_namedata as $_f)
				{
					//echo $_f->first_name;exit;
					$f_nameArray[$_f->first_name] = $_f->first_name;
				}
				//print_r();exit;
				$l_nameArray = array();
				if($whereraw == '')
				{
				$l_namedata = Employee_details::where("dept_id",9)->get();
				}
				else
				{
					
					$l_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					
				}
				
				foreach($l_namedata as $_lname)
				{
					//echo $_lname->last_name;exit;
					$l_nameArray[$_lname->last_name] = $_lname->last_name;
				}
				
				$departmentArray = array();
				if($whereraw == '')
				{
						$department = Department::where("status",1)->orderBy('id','DESC')->get();
				}
				else
				{
					$department =Department::where("status",1)->orderBy('id','DESC')->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$departmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$sourcecodeArray = array();
				if($whereraw == '')
				{
				$soursecode= Employee_details::where("dept_id",9)->get();
				}
				else
				{
					
					$soursecode = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					
				}
				
				foreach($soursecode as $_scode)
				{
					//echo $_lname->last_name;exit;
					$sourcecodeArray[$_scode->source_code] = $_scode->source_code;
				}
				
				$designationArray = array();
				if($whereraw == '')
				{
				$designation= Employee_details::where("dept_id",9)->get();
				}
				else
				{
					
					$designation = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					
				}
				
				foreach($designation as $_designation)
				{
					//echo $_lname->last_name;exit;
					if(!empty($_designation->job_role)){
					$designationArray[$_designation->job_role] = $_designation->job_role;
					}
				}
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = Employee_details::where("dept_id",9)->orderBy("id", "DESC")->get();
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
					//$Vintage = DocumentCollectionDetails::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = Employee_details::whereRaw($whereraw)->where("dept_id",9)->orderBy("id", "DESC")->get();
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
				
				
				$LocationArray = array();
				if($whereraw == '')
				{
				$loc= Employee_details::where("dept_id",9)->get();
				}
				else
				{
					
					$loc = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					
				}
				
				foreach($loc as $_location)
				{
					//echo $_lname->last_name;exit;
					if($_location->work_location !=''){
					$LocationArray[$_location->work_location] = $_location->work_location;
					}
				}
				
				$VisaUnderCompany = array();
				if($whereraw == '')
				{
				//$empdata= Employee_details::get();
				$Collection  = Employee_details::where("dept_id",9)->get();
				if(!empty($Collection)){
				$empid=array();
				foreach($Collection as $_coll)
				{
					$empid[]=$_coll->emp_id;										
				}
	
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
				}
				}
				else
				{
					$Collection = Employee_details::whereRaw($whereraw)->where("dept_id",9)->get();
					$empid=array();
					foreach($Collection as $_coll)
					{
					$empid[]=$_coll->emp_id;					
					
					}
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
					 //print_r($empattributesMod);exit;
					
				}		
				
				//print_r($finaldata);
				if(!empty($empattributesMod)){
				foreach($empattributesMod as $_companyvisa)
				{
				
					if($_companyvisa->attribute_values=='-' || $_companyvisa->attribute_values=='' || $_companyvisa->attribute_values=='NULL'){
						
					}else{
					$VisaUnderCompany[$_companyvisa->attribute_values] = $_companyvisa->attribute_values;
					}
					
				}
				}
				
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderByRaw("-vintage_days DESC")->whereRaw($whereraw)->where("dept_id",47)->where("offline_status",1)->paginate($paginationValue);
				$reportsCountscb = Employee_details::whereRaw($whereraw)->where("dept_id",47)->where("offline_status",1)->get()->count();
					$activeCountscb = Employee_details::whereRaw($whereraw)->where("dept_id",47)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountscb = Employee_details::whereRaw($whereraw)->where("dept_id",47)->where("offline_status",1)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::where("dept_id",47)->where("offline_status",1)->orderByRaw("-vintage_days DESC")->paginate($paginationValue);
					$reportsCountscb = Employee_details::where("dept_id",47)->where("offline_status",1)->get()->count();	
					$activeCountscb = Employee_details::where("dept_id",47)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountscb = Employee_details::where("dept_id",47)->where("offline_status",1)->where('status',2)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/AjaxEmpListSCBData'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));
			$exportemployeestatus=ExportEmployeeStatus::where('id',1)->first();
			
			return view("EmpProcess/AjaxEmpListSCB",compact('VisaUnderCompany','LocationArray','VintageArray','empdetails','paginationValue','departmentLists','deptID','reportsCountscb','empIdArray','selectedFilter','f_nameArray','l_nameArray','departmentArray','sourcecodeArray','designationArray','activeCountscb','inactiveCountscb','exportemployeestatus'));
		}
	public static function getLocationData($job_function){
				
			$jobOpning=JobOpening::where('id',$job_function)->first();
			if($jobOpning!=''){
				return $jobOpning->location;
				
			}
			else{
				return 'test';
			}
			
	}

public function viewEmployeeProfilePersonalDetails($empid=NULL)
	{
		$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','p_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();

		$empDetailsSection2 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','v_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	

		$empDetailsSection3 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','deploy_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empDetailsSection4 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','b_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			$empDetailswarningletter = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','warning_letter')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
					   /* echo "<pre>";
					   print_r($empRequiredDetails);
					  exit;  */
					 $document_collection_id = $empRequiredDetails->document_collection_id;
					  if($document_collection_id != '' && $document_collection_id != NULL)
					  {
			$kycSection5 = DocumentCollectionAttributes::join('kyc_documents', 'kyc_documents.attribute_code', '=', 'document_collection_attributes.id')
              		->where('kyc_documents.document_collection_id',$document_collection_id)
					->where('document_collection_attributes.attribute_area','kyc')
					
					  ->orderBy('document_collection_attributes.sort_order', 'ASC')
					  ->get();
					  }
					  else
					  {
						 $kycSection5 = array(); 
					  } 
					 $emp_detailsPhoto = Employee_details::where("emp_id",$empid)->first();  
			return view("EmpProcessProfile/viewEmployeeProfilePersonalDetails",compact('empDetails','emp_detailsPhoto'),compact('empDetailswarningletter','empRequiredDetails','empDetailsSection2','empDetailsSection3','empDetailsSection4','kycSection5'));
	
	}	
	public function viewEmployeeVisaInsuranceDetails($empid=NULL)
	{
		$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','p_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();

		$empDetailsSection2 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','v_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	

		$empDetailsSection3 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','deploy_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empDetailsSection4 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','b_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			$empDetailswarningletter = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','warning_letter')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
					   /* echo "<pre>";
					   print_r($empRequiredDetails);
					  exit;  */
					 $document_collection_id = $empRequiredDetails->document_collection_id;
					  if($document_collection_id != '' && $document_collection_id != NULL)
					  {
			$kycSection5 = DocumentCollectionAttributes::join('kyc_documents', 'kyc_documents.attribute_code', '=', 'document_collection_attributes.id')
              		->where('kyc_documents.document_collection_id',$document_collection_id)
					->where('document_collection_attributes.attribute_area','kyc')
					
					  ->orderBy('document_collection_attributes.sort_order', 'ASC')
					  ->get();
					  }
					  else
					  {
						 $kycSection5 = array(); 
					  } 
					 $emp_detailsPhoto = Employee_details::where("emp_id",$empid)->first();  
			return view("EmpProcessProfile/viewEmployeeVisaInsuranceDetails",compact('empDetails','emp_detailsPhoto'),compact('empDetailswarningletter','empRequiredDetails','empDetailsSection2','empDetailsSection3','empDetailsSection4','kycSection5'));
	
	}
	public function viewEmployeehiringDetails($empid=NULL)
	{
		$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','p_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();

		$empDetailsSection2 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','v_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	

		$empDetailsSection3 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','deploy_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empDetailsSection4 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','b_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			$empDetailswarningletter = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','warning_letter')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
					  
				
			$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
					   /* echo "<pre>";
					   print_r($empRequiredDetails);
					  exit;  */
					 $document_collection_id = $empRequiredDetails->document_collection_id;
					  if($document_collection_id != '' && $document_collection_id != NULL)
					  {
			$kycSection5 = DocumentCollectionAttributes::join('kyc_documents', 'kyc_documents.attribute_code', '=', 'document_collection_attributes.id')
              		->where('kyc_documents.document_collection_id',$document_collection_id)
					->where('document_collection_attributes.attribute_area','kyc')
					
					  ->orderBy('document_collection_attributes.sort_order', 'ASC')
					  ->get();
					  }
					  else
					  {
						 $kycSection5 = array(); 
					  } 
					 $emp_detailsPhoto = Employee_details::where("emp_id",$empid)->first();  
			return view("EmpProcessProfile/viewEmployeeHiringDetails",compact('empDetails','emp_detailsPhoto'),compact('empDetailswarningletter','empRequiredDetails','empDetailsSection2','empDetailsSection3','empDetailsSection4','kycSection5'));
	
	}
	
	public function viewEmployeeDeploymentDetails($empid=NULL)
	{
		$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','p_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();

		$empDetailsSection2 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','v_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	

		$empDetailsSection3 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','deploy_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empDetailsSection4 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','b_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			$empDetailswarningletter = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','warning_letter')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
					  
				
			$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
					   /* echo "<pre>";
					   print_r($empRequiredDetails);
					  exit;  */
					 $document_collection_id = $empRequiredDetails->document_collection_id;
					  if($document_collection_id != '' && $document_collection_id != NULL)
					  {
			$kycSection5 = DocumentCollectionAttributes::join('kyc_documents', 'kyc_documents.attribute_code', '=', 'document_collection_attributes.id')
              		->where('kyc_documents.document_collection_id',$document_collection_id)
					->where('document_collection_attributes.attribute_area','kyc')
					
					  ->orderBy('document_collection_attributes.sort_order', 'ASC')
					  ->get();
					  }
					  else
					  {
						 $kycSection5 = array(); 
					  } 
					 $emp_detailsPhoto = Employee_details::where("emp_id",$empid)->first();  
			return view("EmpProcessProfile/viewEmployeeDeploymentDetails",compact('empDetails','emp_detailsPhoto'),compact('empDetailswarningletter','empRequiredDetails','empDetailsSection2','empDetailsSection3','empDetailsSection4','kycSection5'));
	
	}
public function viewEmployeeCompensationDetails($empid=NULL)
	{
		$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','p_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();

		$empDetailsSection2 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','v_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	

		$empDetailsSection3 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','deploy_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empDetailsSection4 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','b_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			$empDetailswarningletter = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','warning_letter')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
					  
				
			$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
					   /* echo "<pre>";
					   print_r($empRequiredDetails);
					  exit;  */
					 $document_collection_id = $empRequiredDetails->document_collection_id;
					  if($document_collection_id != '' && $document_collection_id != NULL)
					  {
			$kycSection5 = DocumentCollectionAttributes::join('kyc_documents', 'kyc_documents.attribute_code', '=', 'document_collection_attributes.id')
              		->where('kyc_documents.document_collection_id',$document_collection_id)
					->where('document_collection_attributes.attribute_area','kyc')
					
					  ->orderBy('document_collection_attributes.sort_order', 'ASC')
					  ->get();
					  }
					  else
					  {
						 $kycSection5 = array(); 
					  } 
					 $emp_detailsPhoto = Employee_details::where("emp_id",$empid)->first();  
			return view("EmpProcessProfile/viewEmployeeCompensationDetails",compact('empDetails','emp_detailsPhoto'),compact('empDetailswarningletter','empRequiredDetails','empDetailsSection2','empDetailsSection3','empDetailsSection4','kycSection5'));
	
	}
	
	public function viewEmployeeWarningDetails($empid=NULL)
	{
		$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','p_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();

		$empDetailsSection2 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','v_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	

		$empDetailsSection3 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','deploy_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empDetailsSection4 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','b_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			$empDetailswarningletter = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','warning_letter')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
					  
				
			$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
					   /* echo "<pre>";
					   print_r($empRequiredDetails);
					  exit;  */
					 $document_collection_id = $empRequiredDetails->document_collection_id;
					  if($document_collection_id != '' && $document_collection_id != NULL)
					  {
			$kycSection5 = DocumentCollectionAttributes::join('kyc_documents', 'kyc_documents.attribute_code', '=', 'document_collection_attributes.id')
              		->where('kyc_documents.document_collection_id',$document_collection_id)
					->where('document_collection_attributes.attribute_area','kyc')
					
					  ->orderBy('document_collection_attributes.sort_order', 'ASC')
					  ->get();
					  }
					  else
					  {
						 $kycSection5 = array(); 
					  } 
					 $emp_detailsPhoto = Employee_details::where("emp_id",$empid)->first();  
			return view("EmpProcessProfile/viewEmployeeWarningDetails",compact('empDetails','emp_detailsPhoto'),compact('empDetailswarningletter','empRequiredDetails','empDetailsSection2','empDetailsSection3','empDetailsSection4','kycSection5'));
	
	}
	public static function getInterviewValue($interview_id){
		
		if($interview_id !=''){
			$name=InterviewProcess::where('id',$interview_id)->first();
			if($name!=''){
				return $name->name;
			}
			else{
				return "";
			}
		}
	}
	public static function getInterviewValueMobile($interview_id){
		
		if($interview_id !=''){
			$name=InterviewProcess::where('id',$interview_id)->first();
			if($name!=''){
				return $name->mobile;
			}
			else{
				return "";
			}
		}
	}
	
	
	
	public static function getInterviewValuejoboping($interview_id){
		
		if($interview_id !=''){
			$joboping=InterviewProcess::where('id',$interview_id)->first();
			if($joboping!=''){
				$job= $joboping->job_opening;
				$jobOpning=JobOpening::where('id',$job)->first();
				if($jobOpning!=''){
					return $jobOpning->name;
					
				}
				else{
					return ' ';
				}
			}
			else{
				return "";
			}
		}
	}
	
	public static function getAttributeValueJobLocationData($interview_id){
		if($interview_id !=''){
			$jobopinglocation=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview1")->first();		
			if($jobopinglocation!=''){
				return $jobopinglocation->location;
				
			}
			else{
				return "-";
			}
		}
	}	
	
	public static function getAttributeValueRecruiter($interview_id){
		if($interview_id !=''){
			$Recruiter=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview1")->first();
			if($Recruiter!=''){
				$RecruiterId= $Recruiter->recruiter;
				$RecruiterName= RecruiterDetails::where("id",$RecruiterId)->first();
				if($RecruiterName!=''){
					return $RecruiterName->name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	public static function getAttributeValueDesignation($interview_id){
		if($interview_id !=''){
			$Designation=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview1")->first();
			if($Designation!=''){
				$DesignationId= $Designation->recruiter;
				$DesignationName= Designation::where("id",$DesignationId)->first();
				if($DesignationName!=''){
					return $DesignationName->name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	
	public static function getAttributeValueDepartment($interview_id){
		if($interview_id !=''){
			$Department=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview1")->first();
			if($Department!=''){
				$DepartmentId= $Department->department;
				$DepartmentName= Department::where("id",$DepartmentId)->first();
				if($DepartmentName!=''){
					return $DepartmentName->department_name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	
	public static function getAttributeValueInterviewDate($interview_id){
		if($interview_id !=''){
			$startdate=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview1")->first();
			if($startdate!=''){
				
					return $startdate->created_at;
					
				}
		
			else{
				return "-";
			}
		}
	}
	public static function getInterviewerName($interview_id){
		if($interview_id !=''){
			$Recruiter=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"final discussion")->first();
			if($Recruiter!=''){
				$RecruiterId= $Recruiter->recruiter;
				$RecruiterName= RecruiterDetails::where("id",$RecruiterId)->first();
				if($RecruiterName!=''){
					return $RecruiterName->name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	public static function getCondidateRating($interview_id){
		if($interview_id !=''){
			$CondidateRating=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"final discussion")->first();
			if($CondidateRating!=''){
				
				
					return $CondidateRating->rating;
				
			}
			else{
				return "-";
			}
		}
	}
	public static function getCondidatesalary($interview_id){
	if($interview_id !=''){
			$Condidatesalary=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"final discussion")->first();
			if($Condidatesalary!=''){
				
				
					return $Condidatesalary->salary;
				
			}
			else{
				return "-";
			}
		}	
	}
	public static function getAttributeValueFinalInterviewDate($interview_id){
		if($interview_id !=''){
			$finaldate=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"final discussion")->first();
			if($finaldate!=''){
				
					return $finaldate->created_at;
					
				}
		
			else{
				return "-";
			}
		}
	}
	// final interview data
	
	
	public static function getAttributeValueJobLocationDatafinal($interview_id){
		if($interview_id !=''){
			$jobopinglocation=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"final discussion")->first();		
			if($jobopinglocation!=''){
				return $jobopinglocation->location;
				
			}
			else{
				return "-";
			}
		}
	}	
	
	public static function getAttributeValueDesignationfinal($interview_id){
		if($interview_id !=''){
			$Designation=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"final discussion")->first();
			if($Designation!=''){
				$DesignationId= $Designation->recruiter;
				$DesignationName= Designation::where("id",$DesignationId)->first();
				if($DesignationName!=''){
					return $DesignationName->name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	
	public static function getAttributeValueDepartmentfinal($interview_id){
		if($interview_id !=''){
			$Department=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"final discussion")->first();
			if($Department!=''){
				$DepartmentId= $Department->department;
				$DepartmentName= Department::where("id",$DepartmentId)->first();
				if($DepartmentName!=''){
					return $DepartmentName->department_name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	
	public static function getAttributeValueResume($interview_id){
		if($interview_id !=''){
			$CondidateResume=InterviewProcess::where('id',$interview_id)->first();
			if($CondidateResume!=''){
				
				
					return $CondidateResume->attached_cv;
				
			}
			else{
				return "-";
			}
		}
	}
	
	public static function getinterview1Status($interview_id)
	{
		$interview1Status = InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview1")->first();
		if($interview1Status != '')
		{
			return 2;
		}
		else
		{
			return 1;
		}
	}
	
	
	
	public static function getInterviewerNameInterview1($interview_id){
		if($interview_id !=''){
			$Recruiter=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview1")->first();
			if($Recruiter!=''){
				$RecruiterId= $Recruiter->recruiter;
				$RecruiterName= RecruiterDetails::where("id",$RecruiterId)->first();
				if($RecruiterName!=''){
					return $RecruiterName->name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	public static function getCondidateRatingInterview1($interview_id){
		if($interview_id !=''){
			$CondidateRating=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview1")->first();
			if($CondidateRating!=''){
				
				
					return $CondidateRating->rating;
				
			}
			else{
				return "-";
			}
		}
	}
	public static function getCondidatesalaryInterview1($interview_id){
	if($interview_id !=''){
			$Condidatesalary=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview1")->first();
			if($Condidatesalary!=''){
				
				
					return $Condidatesalary->salary;
				
			}
			else{
				return "-";
			}
		}	
	}
	public static function getAttributeValueFinalInterviewDateInterview1($interview_id){
		if($interview_id !=''){
			$finaldate=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview1")->first();
			if($finaldate!=''){
				
					return $finaldate->created_at;
					
				}
		
			else{
				return "-";
			}
		}
	}
	// final interview data
	
	
	public static function getAttributeValueJobLocationDatafinalInterview1($interview_id){
		if($interview_id !=''){
			$jobopinglocation=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview1")->first();		
			if($jobopinglocation!=''){
				return $jobopinglocation->location;
				
			}
			else{
				return "-";
			}
		}
	}	
	
	public static function getAttributeValueDesignationfinalInterview1($interview_id){
		if($interview_id !=''){
			$Designation=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview1")->first();
			if($Designation!=''){
				$DesignationId= $Designation->recruiter;
				$DesignationName= Designation::where("id",$DesignationId)->first();
				if($DesignationName!=''){
					return $DesignationName->name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	
	public static function getAttributeValueDepartmentfinalInterview1($interview_id){
		if($interview_id !=''){
			$Department=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview1")->first();
			if($Department!=''){
				$DepartmentId= $Department->department;
				$DepartmentName= Department::where("id",$DepartmentId)->first();
				if($DepartmentName!=''){
					return $DepartmentName->department_name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	
	
	
	
	public static function getinterview2Status($interview_id)
	{
		$interview1Status = InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview2")->first();
		if($interview1Status != '')
		{
			return 2;
		}
		else
		{
			return 1;
		}
	}
	
	
	
	public static function getInterviewerNameInterview2($interview_id){
		if($interview_id !=''){
			$Recruiter=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview2")->first();
			if($Recruiter!=''){
				$RecruiterId= $Recruiter->recruiter;
				$RecruiterName= RecruiterDetails::where("id",$RecruiterId)->first();
				if($RecruiterName!=''){
					return $RecruiterName->name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	public static function getCondidateRatingInterview2($interview_id){
		if($interview_id !=''){
			$CondidateRating=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview2")->first();
			if($CondidateRating!=''){
				
				
					return $CondidateRating->rating;
				
			}
			else{
				return "-";
			}
		}
	}
	public static function getCondidatesalaryInterview2($interview_id){
	if($interview_id !=''){
			$Condidatesalary=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview2")->first();
			if($Condidatesalary!=''){
				
				
					return $Condidatesalary->salary;
				
			}
			else{
				return "-";
			}
		}	
	}
	public static function getAttributeValueFinalInterviewDateInterview2($interview_id){
		if($interview_id !=''){
			$finaldate=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview2")->first();
			if($finaldate!=''){
				
					return $finaldate->created_at;
					
				}
		
			else{
				return "-";
			}
		}
	}
	// final interview data
	
	
	public static function getAttributeValueJobLocationDatafinalInterview2($interview_id){
		if($interview_id !=''){
			$jobopinglocation=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview2")->first();		
			if($jobopinglocation!=''){
				return $jobopinglocation->location;
				
			}
			else{
				return "-";
			}
		}
	}	
	
	public static function getAttributeValueDesignationfinalInterview2($interview_id){
		if($interview_id !=''){
			$Designation=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview2")->first();
			if($Designation!=''){
				$DesignationId= $Designation->recruiter;
				$DesignationName= Designation::where("id",$DesignationId)->first();
				if($DesignationName!=''){
					return $DesignationName->name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	
	public static function getAttributeValueDepartmentfinalInterview2($interview_id){
		if($interview_id !=''){
			$Department=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview2")->first();
			if($Department!=''){
				$DepartmentId= $Department->department;
				$DepartmentName= Department::where("id",$DepartmentId)->first();
				if($DepartmentName!=''){
					return $DepartmentName->department_name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	
	
	
	public static function getinterview3Status($interview_id)
	{
		$interview1Status = InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview3")->first();
		if($interview1Status != '')
		{
			return 2;
		}
		else
		{
			return 1;
		}
	}
	
	
	
	public static function getInterviewerNameInterview3($interview_id){
		if($interview_id !=''){
			$Recruiter=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview3")->first();
			if($Recruiter!=''){
				$RecruiterId= $Recruiter->recruiter;
				$RecruiterName= RecruiterDetails::where("id",$RecruiterId)->first();
				if($RecruiterName!=''){
					return $RecruiterName->name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	public static function getCondidateRatingInterview3($interview_id){
		if($interview_id !=''){
			$CondidateRating=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview3")->first();
			if($CondidateRating!=''){
				
				
					return $CondidateRating->rating;
				
			}
			else{
				return "-";
			}
		}
	}
	public static function getCondidatesalaryInterview3($interview_id){
	if($interview_id !=''){
			$Condidatesalary=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview3")->first();
			if($Condidatesalary!=''){
				
				
					return $Condidatesalary->salary;
				
			}
			else{
				return "-";
			}
		}	
	}
	public static function getAttributeValueFinalInterviewDateInterview3($interview_id){
		if($interview_id !=''){
			$finaldate=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview3")->first();
			if($finaldate!=''){
				
					return $finaldate->created_at;
					
				}
		
			else{
				return "-";
			}
		}
	}
	// final interview data
	
	
	public static function getAttributeValueJobLocationDatafinalInterview3($interview_id){
		if($interview_id !=''){
			$jobopinglocation=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview3")->first();		
			if($jobopinglocation!=''){
				return $jobopinglocation->location;
				
			}
			else{
				return "-";
			}
		}
	}	
	
	public static function getAttributeValueDesignationfinalInterview3($interview_id){
		if($interview_id !=''){
			$Designation=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview3")->first();
			if($Designation!=''){
				$DesignationId= $Designation->recruiter;
				$DesignationName= Designation::where("id",$DesignationId)->first();
				if($DesignationName!=''){
					return $DesignationName->name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	
	public static function getAttributeValueDepartmentfinalInterview3($interview_id){
		if($interview_id !=''){
			$Department=InterviewDetailsProcess::where('interview_id',$interview_id)->where('interview_type',"Interview3")->first();
			if($Department!=''){
				$DepartmentId= $Department->department;
				$DepartmentName= Department::where("id",$DepartmentId)->first();
				if($DepartmentName!=''){
					return $DepartmentName->department_name;
					
				}
				else{
					return '-';
				}
			}
			else{
				return "-";
			}
		}
	}
	public function AjaxEmpListCBDData(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('offset_emp_filter')))
				{
					$paginationValue = $request->session()->get('offset_emp_filter');
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
					$empdetails = Employee_details::where("dept_id",49)->paginate($paginationValue);	
					$reportsCountenbd = Employee_details::where("dept_id",49)->get()->count();
					$activeCountenbd = Employee_details::where("dept_id",49)->where('status',1)->get()->count();
					$inactiveCountenbd = Employee_details::where("dept_id",49)->where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('empid_emp_filter_inner_list')) && $request->session()->get('empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				//echo $whereraw;
				if(!empty($request->session()->get('fname_emp_filter_inner_list')) && $request->session()->get('fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('lname_emp_filter_inner_list')) && $request->session()->get('lname_emp_filter_inner_list') != 'All')
				{
					$lname = $request->session()->get('lname_emp_filter_inner_list');
					 $selectedFilter['l_name'] = $lname;
					 if($whereraw == '')
					{
						$whereraw = 'last_name like "%'.$lname.'%"';
					}
					else
					{
						$whereraw .= ' And last_name like "%'.$lname.'%"';
					}
				}
				if(!empty($request->session()->get('design_emp_filter_inner_list')) && $request->session()->get('design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('jobfunction_emp_filter_inner_list')) && $request->session()->get('jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('RecruiterName_emp_filter_inner_list')) && $request->session()->get('RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
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
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					
					 $locationArray = explode(",",$location);
					 $finallocationArray=array();
					 foreach($locationArray as $_locationArray){
						 $finallocationArray[]="'".$_locationArray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $finallocationArray);
					 if($whereraw == '')
					{
						$whereraw = 'work_location IN('.$finalloc.')';
					}
					else
					{
						$whereraw .= ' And work_location IN('.$finalloc.')';
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				$empIdArray = array();
				if($whereraw == '')
				{
				$appidGet = Employee_details::where("dept_id",49)->get();
				}
				else
				{
					
					$appidGet = Employee_details::whereRaw($whereraw)->where("dept_id",49)->get();
					
				}
				
				foreach($appidGet as $_d)
				{
					if($_d->emp_id != NULL && $_d->emp_id != '')
					{
						$empIdArray[$_d->emp_id] = $_d->emp_id;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				$f_nameArray = array();
				if($whereraw == '')
				{
				$f_namedata = Employee_details::where("dept_id",49)->get();
				}
				else
				{
					
					$f_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",49)->get();
					
				}
				
				foreach($f_namedata as $_f)
				{
					//echo $_f->first_name;exit;
					$f_nameArray[$_f->first_name] = $_f->first_name;
				}
				//print_r();exit;
				$l_nameArray = array();
				if($whereraw == '')
				{
				$l_namedata = Employee_details::where("dept_id",49)->get();
				}
				else
				{
					
					$l_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",49)->get();
					
				}
				
				foreach($l_namedata as $_lname)
				{
					//echo $_lname->last_name;exit;
					$l_nameArray[$_lname->last_name] = $_lname->last_name;
				}
				
				$departmentArray = array();
				if($whereraw == '')
				{
						$department = Department::where("status",1)->orderBy('id','DESC')->get();
				}
				else
				{
					$department =Department::where("status",1)->orderBy('id','DESC')->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$departmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$sourcecodeArray = array();
				if($whereraw == '')
				{
				$soursecode= Employee_details::where("dept_id",49)->get();
				}
				else
				{
					
					$soursecode = Employee_details::whereRaw($whereraw)->where("dept_id",49)->get();
					
				}
				
				foreach($soursecode as $_scode)
				{
					//echo $_lname->last_name;exit;
					$sourcecodeArray[$_scode->source_code] = $_scode->source_code;
				}
				
				$designationArray = array();
				if($whereraw == '')
				{
				$designation= Employee_details::where("dept_id",49)->get();
				}
				else
				{
					
					$designation = Employee_details::whereRaw($whereraw)->where("dept_id",49)->get();
					
				}
				
				foreach($designation as $_designation)
				{
					//echo $_lname->last_name;exit;
					if(!empty($_designation->job_role)){
					$designationArray[$_designation->job_role] = $_designation->job_role;
					}
				}
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = Employee_details::where("dept_id",49)->orderBy("id", "DESC")->get();
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
					//$Vintage = DocumentCollectionDetails::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = Employee_details::whereRaw($whereraw)->where("dept_id",49)->orderBy("id", "DESC")->get();
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
				
				
				$LocationArray = array();
				if($whereraw == '')
				{
				$loc= Employee_details::where("dept_id",49)->get();
				}
				else
				{
					
					$loc = Employee_details::whereRaw($whereraw)->where("dept_id",49)->get();
					
				}
				
				foreach($loc as $_location)
				{
					//echo $_lname->last_name;exit;
					if($_location->work_location !=''){
					$LocationArray[$_location->work_location] = $_location->work_location;
					}
				}
				
				$VisaUnderCompany = array();
				if($whereraw == '')
				{
				//$empdata= Employee_details::get();
				$Collection  = Employee_details::where("dept_id",49)->get();
				if(!empty($Collection)){
				$empid=array();
				foreach($Collection as $_coll)
				{
					$empid[]=$_coll->emp_id;										
				}
	
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
				}
				}
				else
				{
					$Collection = Employee_details::whereRaw($whereraw)->where("dept_id",49)->get();
					$empid=array();
					foreach($Collection as $_coll)
					{
					$empid[]=$_coll->emp_id;					
					
					}
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
					 //print_r($empattributesMod);exit;
					
				}		
				
				//print_r($finaldata);
				if(!empty($empattributesMod)){
				foreach($empattributesMod as $_companyvisa)
				{
				
					if($_companyvisa->attribute_values=='-' || $_companyvisa->attribute_values=='' || $_companyvisa->attribute_values=='NULL'){
						
					}else{
					$VisaUnderCompany[$_companyvisa->attribute_values] = $_companyvisa->attribute_values;
					}
					
				}
				}
				
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderByRaw("-vintage_days DESC")->whereRaw($whereraw)->where("dept_id",49)->where("offline_status",1)->paginate($paginationValue);
				$reportsCountcbd = Employee_details::whereRaw($whereraw)->where("dept_id",49)->where("offline_status",1)->get()->count();
					$activeCountcbd = Employee_details::whereRaw($whereraw)->where("dept_id",49)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountcbd = Employee_details::whereRaw($whereraw)->where("dept_id",49)->where("offline_status",1)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::where("dept_id",49)->where("offline_status",1)->orderByRaw("-vintage_days DESC")->paginate($paginationValue);
					$reportsCountcbd = Employee_details::where("dept_id",49)->where("offline_status",1)->get()->count();	
					$activeCountcbd = Employee_details::where("dept_id",49)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountcbd = Employee_details::where("dept_id",49)->where("offline_status",1)->where('status',2)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/AjaxEmpListCBDData'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));
			$exportemployeestatus=ExportEmployeeStatus::where('id',1)->first();
			
			return view("EmpProcess/AjaxEmpListCBD",compact('VisaUnderCompany','LocationArray','VintageArray','empdetails','paginationValue','departmentLists','deptID','reportsCountcbd','empIdArray','selectedFilter','f_nameArray','l_nameArray','departmentArray','sourcecodeArray','designationArray','activeCountcbd','inactiveCountcbd','exportemployeestatus'));
		}
		public function viewEmployeeSalesProfileDetails($empid=NULL)
	{
		$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','p_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();

		$empDetailsSection2 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','v_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	

		$empDetailsSection3 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','deploy_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empDetailsSection4 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','b_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			$empDetailswarningletter = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','warning_letter')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
					  
				
			$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
					   /* echo "<pre>";
					   print_r($empRequiredDetails);
					  exit;  */
					 $document_collection_id = $empRequiredDetails->document_collection_id;
					  if($document_collection_id != '' && $document_collection_id != NULL)
					  {
			$kycSection5 = DocumentCollectionAttributes::join('kyc_documents', 'kyc_documents.attribute_code', '=', 'document_collection_attributes.id')
              		->where('kyc_documents.document_collection_id',$document_collection_id)
					->where('document_collection_attributes.attribute_area','kyc')
					
					  ->orderBy('document_collection_attributes.sort_order', 'ASC')
					  ->get();
					  }
					  else
					  {
						 $kycSection5 = array(); 
					  } 
					 $emp_detailsPhoto = Employee_details::where("emp_id",$empid)->first();  
			return view("EmpProcessProfile/viewEmployeeSalesProfileDetails",compact('empDetails','emp_detailsPhoto'),compact('empDetailswarningletter','empRequiredDetails','empDetailsSection2','empDetailsSection3','empDetailsSection4','kycSection5'));
	
	}
	public function AjaxEmpListAttritionData(Request $request){
		$deptID = '';
			if(!empty($request->session()->get('offset_emp_filter')))
				{
					$paginationValue = $request->session()->get('offset_emp_filter');
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
				if(!empty($request->session()->get('empid_emp_filter_inner_list')) && $request->session()->get('empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('fname_emp_filter_inner_list')) && $request->session()->get('fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				
				if(!empty($request->session()->get('lname_emp_filter_inner_list')) && $request->session()->get('lname_emp_filter_inner_list') != 'All')
				{
					$lname = $request->session()->get('lname_emp_filter_inner_list');
					 $selectedFilter['l_name'] = $lname;
					 if($whereraw == '')
					{
						$whereraw = 'last_name like "%'.$lname.'%"';
					}
					else
					{
						$whereraw .= ' And last_name like "%'.$lname.'%"';
					}
				}
				if(!empty($request->session()->get('design_emp_filter_inner_list')) && $request->session()->get('design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('jobfunction_emp_filter_inner_list')) && $request->session()->get('jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('RecruiterName_emp_filter_inner_list')) && $request->session()->get('RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
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
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					
					 $locationArray = explode(",",$location);
					 $finallocationArray=array();
					 foreach($locationArray as $_locationArray){
						 $finallocationArray[]="'".$_locationArray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $finallocationArray);
					 if($whereraw == '')
					{
						$whereraw = 'work_location IN('.$finalloc.')';
					}
					else
					{
						$whereraw .= ' And work_location IN('.$finalloc.')';
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				$empIdArray = array();
				if($whereraw == '')
				{
				$appidGet = Employee_details::get();
				}
				else
				{
					
					$appidGet = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($appidGet as $_d)
				{
					if($_d->emp_id != NULL && $_d->emp_id != '')
					{
						$empIdArray[$_d->emp_id] = $_d->emp_id;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				$f_nameArray = array();
				if($whereraw == '')
				{
				$f_namedata = Employee_details::get();
				}
				else
				{
					
					$f_namedata = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($f_namedata as $_f)
				{
					//echo $_f->first_name;exit;
					$f_nameArray[$_f->first_name] = $_f->first_name;
				}
				//print_r();exit;
				$l_nameArray = array();
				if($whereraw == '')
				{
				$l_namedata = Employee_details::get();
				}
				else
				{
					
					$l_namedata = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($l_namedata as $_lname)
				{
					//echo $_lname->last_name;exit;
					$l_nameArray[$_lname->last_name] = $_lname->last_name;
				}
				
				$departmentArray = array();
				if($whereraw == '')
				{
						$department = Department::where("status",1)->orderBy('id','DESC')->get();
				}
				else
				{
					$department =Department::where("status",1)->orderBy('id','DESC')->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$departmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$sourcecodeArray = array();
				if($whereraw == '')
				{
				$soursecode= Employee_details::get();
				}
				else
				{
					
					$soursecode = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($soursecode as $_scode)
				{
					//echo $_lname->last_name;exit;
					$sourcecodeArray[$_scode->source_code] = $_scode->source_code;
				}
				
				$designationArray = array();
				if($whereraw == '')
				{
				$designation= Employee_details::get();
				}
				else
				{
					
					$designation = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($designation as $_designation)
				{
					//echo $_lname->last_name;exit;
					if(!empty($_designation->job_role)){
					$designationArray[$_designation->job_role] = $_designation->job_role;
					}
				}
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = Employee_details::orderBy("id", "DESC")->get();
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
					//$Vintage = DocumentCollectionDetails::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = Employee_details::whereRaw($whereraw)->orderBy("id", "DESC")->get();
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
				
				
				$LocationArray = array();
				if($whereraw == '')
				{
				$loc= Employee_details::get();
				}
				else
				{
					
					$loc = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($loc as $_location)
				{
					//echo $_lname->last_name;exit;
					if($_location->work_location !=''){
					$LocationArray[$_location->work_location] = $_location->work_location;
					}
				}
				
				$VisaUnderCompany = array();
				if($whereraw == '')
				{
				//$empdata= Employee_details::get();
				$Collection  = Employee_details::get();
				if(!empty($Collection)){
				$empid=array();
				foreach($Collection as $_coll)
				{
					$empid[]=$_coll->emp_id;										
				}
	
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
				}
				}
				else
				{
					$Collection = Employee_details::whereRaw($whereraw)->get();
					$empid=array();
					foreach($Collection as $_coll)
					{
					$empid[]=$_coll->emp_id;					
					
					}
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
					 //print_r($empattributesMod);exit;
					
				}		
				
				//print_r($finaldata);
				if(!empty($empattributesMod)){
				foreach($empattributesMod as $_companyvisa)
				{
				
					if($_companyvisa->attribute_values=='-' || $_companyvisa->attribute_values=='' || $_companyvisa->attribute_values=='NULL'){
						
					}else{
					$VisaUnderCompany[$_companyvisa->attribute_values] = $_companyvisa->attribute_values;
					}
					
				}
				}
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 97 || $empsessionId== 123 ){
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
				}
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;exit;
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderByRaw("-vintage_days DESC")->whereRaw($whereraw)->where("offline_status",2)->paginate($paginationValue);
				$reportsCountAttrition = Employee_details::whereRaw($whereraw)->where("offline_status",2)->get()->count();
					$activeCountAttrition = Employee_details::whereRaw($whereraw)->where("offline_status",2)->where('status',1)->get()->count();
					$inactiveCountAttrition = Employee_details::whereRaw($whereraw)->where("offline_status",2)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::orderByRaw("-vintage_days DESC")->where("offline_status",2)->paginate($paginationValue);
					$reportsCountAttrition = Employee_details::where("offline_status",2)->get()->count();	
					$activeCountAttrition = Employee_details::where('status',1)->where("offline_status",2)->get()->count();
					$inactiveCountAttrition = Employee_details::where('status',2)->where("offline_status",2)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/AjaxEmpList'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));
			$exportemployeestatus=ExportEmployeeStatus::where('id',1)->first();
			
			return view("EmpProcess/AjaxEmpListAttrition",compact('VisaUnderCompany','LocationArray','VintageArray','empdetails','paginationValue','departmentLists','deptID','reportsCountAttrition','empIdArray','selectedFilter','f_nameArray','l_nameArray','departmentArray','sourcecodeArray','designationArray','activeCountAttrition','inactiveCountAttrition','exportemployeestatus'));
		}
		public static function getRecruiter($id)
			{	
			$data =RecruiterDetails::where("id",$id)->first();
			  
			  if($data != '')
			  {
				
			  return $data->name;
			  }
			  else
			  {
			  return '';
			  }
			}
	public static function getCheckLoginUserData($userid){
		 $departmentDetails = JobFunctionPermission::where("user_id",$userid)->first();
		   if($departmentDetails != '')
		   {
			   return $departmentDetails->hide_cols;
		   }
		   else
		   {
			   return 'All';
		   }
		  
	}
	public static function getCheckLoginUserJobFunction($uid){
		$departmentDetails = JobFunctionPermission::where("user_id",$uid)->first();
		   if($departmentDetails != '')
		   {
			   return $departmentDetails->job_function_id;
		   }
		   else
		   {
			   return 'All';
		   }
	}
	public static function getCheckLoginUserDptid($uid){
		$departmentDetails = JobFunctionPermission::where("user_id",$uid)->first();
		   if($departmentDetails != '')
		   {
			   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
			   if($empdata!=''){
				   return $empdata->dept_id;
			   }
			   else
		   {
			   return '';
		   }
		   }
		   else
		   {
			   return '';
		   }
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
	   public function AjaxEmpListDUCData(Request $request){
			
			$deptID = '';
			if(!empty($request->session()->get('offset_emp_filter')))
				{
					$paginationValue = $request->session()->get('offset_emp_filter');
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
				
				if(!empty($request->session()->get('empid_emp_filter_inner_list')) && $request->session()->get('empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				//echo $whereraw;
				if(!empty($request->session()->get('fname_emp_filter_inner_list')) && $request->session()->get('fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('lname_emp_filter_inner_list')) && $request->session()->get('lname_emp_filter_inner_list') != 'All')
				{
					$lname = $request->session()->get('lname_emp_filter_inner_list');
					 $selectedFilter['l_name'] = $lname;
					 if($whereraw == '')
					{
						$whereraw = 'last_name like "%'.$lname.'%"';
					}
					else
					{
						$whereraw .= ' And last_name like "%'.$lname.'%"';
					}
				}
				if(!empty($request->session()->get('design_emp_filter_inner_list')) && $request->session()->get('design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('jobfunction_emp_filter_inner_list')) && $request->session()->get('jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('RecruiterName_emp_filter_inner_list')) && $request->session()->get('RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
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
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					
					 $locationArray = explode(",",$location);
					 $finallocationArray=array();
					 foreach($locationArray as $_locationArray){
						 $finallocationArray[]="'".$_locationArray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $finallocationArray);
					 if($whereraw == '')
					{
						$whereraw = 'work_location IN('.$finalloc.')';
					}
					else
					{
						$whereraw .= ' And work_location IN('.$finalloc.')';
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderByRaw("-vintage_days DESC")->whereRaw($whereraw)->where("dept_id",23)->where("offline_status",1)->paginate($paginationValue);
				$reportsCountduc = Employee_details::whereRaw($whereraw)->where("dept_id",23)->where("offline_status",1)->get()->count();
					$activeCountduc = Employee_details::whereRaw($whereraw)->where("dept_id",23)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountduc = Employee_details::whereRaw($whereraw)->where("dept_id",23)->where("offline_status",1)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::where("dept_id",23)->where("offline_status",1)->orderByRaw("-vintage_days DESC")->paginate($paginationValue);
					$reportsCountduc = Employee_details::where("dept_id",23)->where("offline_status",1)->get()->count();	
					$activeCountduc = Employee_details::where("dept_id",23)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCountduc = Employee_details::where("dept_id",23)->where("offline_status",1)->where('status',2)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/AjaxEmpListDUCData'));
			$exportemployeestatus=ExportEmployeeStatus::where('id',1)->first();
			
			
			return view("EmpProcess/AjaxEmpListDUC",compact('empdetails','paginationValue','reportsCountduc','activeCountduc','inactiveCountduc','exportemployeestatus'));
		}
			public function AjaxEmpListEIBData(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('offset_emp_filter')))
				{
					$paginationValue = $request->session()->get('offset_emp_filter');
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
					$empdetails = Employee_details::where("dept_id",49)->paginate($paginationValue);	
					$reportsCountenbd = Employee_details::where("dept_id",49)->get()->count();
					$activeCountenbd = Employee_details::where("dept_id",49)->where('status',1)->get()->count();
					$inactiveCountenbd = Employee_details::where("dept_id",49)->where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('empid_emp_filter_inner_list')) && $request->session()->get('empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				//echo $whereraw;
				if(!empty($request->session()->get('fname_emp_filter_inner_list')) && $request->session()->get('fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('lname_emp_filter_inner_list')) && $request->session()->get('lname_emp_filter_inner_list') != 'All')
				{
					$lname = $request->session()->get('lname_emp_filter_inner_list');
					 $selectedFilter['l_name'] = $lname;
					 if($whereraw == '')
					{
						$whereraw = 'last_name like "%'.$lname.'%"';
					}
					else
					{
						$whereraw .= ' And last_name like "%'.$lname.'%"';
					}
				}
				if(!empty($request->session()->get('design_emp_filter_inner_list')) && $request->session()->get('design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('jobfunction_emp_filter_inner_list')) && $request->session()->get('jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('RecruiterName_emp_filter_inner_list')) && $request->session()->get('RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
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
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					
					 $locationArray = explode(",",$location);
					 $finallocationArray=array();
					 foreach($locationArray as $_locationArray){
						 $finallocationArray[]="'".$_locationArray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $finallocationArray);
					 if($whereraw == '')
					{
						$whereraw = 'work_location IN('.$finalloc.')';
					}
					else
					{
						$whereraw .= ' And work_location IN('.$finalloc.')';
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				$empIdArray = array();
				if($whereraw == '')
				{
				$appidGet = Employee_details::where("dept_id",52)->get();
				}
				else
				{
					
					$appidGet = Employee_details::whereRaw($whereraw)->where("dept_id",52)->get();
					
				}
				
				foreach($appidGet as $_d)
				{
					if($_d->emp_id != NULL && $_d->emp_id != '')
					{
						$empIdArray[$_d->emp_id] = $_d->emp_id;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				$f_nameArray = array();
				if($whereraw == '')
				{
				$f_namedata = Employee_details::where("dept_id",52)->get();
				}
				else
				{
					
					$f_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",52)->get();
					
				}
				
				foreach($f_namedata as $_f)
				{
					//echo $_f->first_name;exit;
					$f_nameArray[$_f->first_name] = $_f->first_name;
				}
				//print_r();exit;
				$l_nameArray = array();
				if($whereraw == '')
				{
				$l_namedata = Employee_details::where("dept_id",52)->get();
				}
				else
				{
					
					$l_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",52)->get();
					
				}
				
				foreach($l_namedata as $_lname)
				{
					//echo $_lname->last_name;exit;
					$l_nameArray[$_lname->last_name] = $_lname->last_name;
				}
				
				$departmentArray = array();
				if($whereraw == '')
				{
						$department = Department::where("status",1)->orderBy('id','DESC')->get();
				}
				else
				{
					$department =Department::where("status",1)->orderBy('id','DESC')->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$departmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$sourcecodeArray = array();
				if($whereraw == '')
				{
				$soursecode= Employee_details::where("dept_id",52)->get();
				}
				else
				{
					
					$soursecode = Employee_details::whereRaw($whereraw)->where("dept_id",52)->get();
					
				}
				
				foreach($soursecode as $_scode)
				{
					//echo $_lname->last_name;exit;
					$sourcecodeArray[$_scode->source_code] = $_scode->source_code;
				}
				
				$designationArray = array();
				if($whereraw == '')
				{
				$designation= Employee_details::where("dept_id",52)->get();
				}
				else
				{
					
					$designation = Employee_details::whereRaw($whereraw)->where("dept_id",52)->get();
					
				}
				
				foreach($designation as $_designation)
				{
					//echo $_lname->last_name;exit;
					if(!empty($_designation->job_role)){
					$designationArray[$_designation->job_role] = $_designation->job_role;
					}
				}
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = Employee_details::where("dept_id",52)->orderBy("id", "DESC")->get();
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
					//$Vintage = DocumentCollectionDetails::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = Employee_details::whereRaw($whereraw)->where("dept_id",52)->orderBy("id", "DESC")->get();
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
				
				
				$LocationArray = array();
				if($whereraw == '')
				{
				$loc= Employee_details::where("dept_id",52)->get();
				}
				else
				{
					
					$loc = Employee_details::whereRaw($whereraw)->where("dept_id",52)->get();
					
				}
				
				foreach($loc as $_location)
				{
					//echo $_lname->last_name;exit;
					if($_location->work_location !=''){
					$LocationArray[$_location->work_location] = $_location->work_location;
					}
				}
				
				$VisaUnderCompany = array();
				if($whereraw == '')
				{
				//$empdata= Employee_details::get();
				$Collection  = Employee_details::where("dept_id",52)->get();
				if(!empty($Collection)){
				$empid=array();
				foreach($Collection as $_coll)
				{
					$empid[]=$_coll->emp_id;										
				}
	
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
				}
				}
				else
				{
					$Collection = Employee_details::whereRaw($whereraw)->where("dept_id",52)->get();
					$empid=array();
					foreach($Collection as $_coll)
					{
					$empid[]=$_coll->emp_id;					
					
					}
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
					 //print_r($empattributesMod);exit;
					
				}		
				
				//print_r($finaldata);
				if(!empty($empattributesMod)){
				foreach($empattributesMod as $_companyvisa)
				{
				
					if($_companyvisa->attribute_values=='-' || $_companyvisa->attribute_values=='' || $_companyvisa->attribute_values=='NULL'){
						
					}else{
					$VisaUnderCompany[$_companyvisa->attribute_values] = $_companyvisa->attribute_values;
					}
					
				}
				}
				
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->where("dept_id",52)->where("offline_status",1)->paginate($paginationValue);
				$reportsCounteib = Employee_details::whereRaw($whereraw)->where("dept_id",52)->where("offline_status",1)->get()->count();
					$activeCounteib = Employee_details::whereRaw($whereraw)->where("dept_id",52)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCounteib = Employee_details::whereRaw($whereraw)->where("dept_id",52)->where("offline_status",1)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::where("dept_id",52)->where("offline_status",1)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCounteib = Employee_details::where("dept_id",52)->where("offline_status",1)->get()->count();	
					$activeCounteib = Employee_details::where("dept_id",52)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCounteib = Employee_details::where("dept_id",52)->where("offline_status",1)->where('status',2)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/AjaxEmpListEIBData'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));
			$exportemployeestatus=ExportEmployeeStatus::where('id',1)->first();
			
			
			return view("EmpProcess/AjaxEmpListEIB",compact('VisaUnderCompany','LocationArray','VintageArray','empdetails','paginationValue','departmentLists','deptID','reportsCounteib','empIdArray','selectedFilter','f_nameArray','l_nameArray','departmentArray','sourcecodeArray','designationArray','activeCounteib','inactiveCounteib','exportemployeestatus'));
		}
	public function AjaxEmpListHSBCData(Request $request){
			//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('offset_emp_filter')))
				{
					$paginationValue = $request->session()->get('offset_emp_filter');
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
					$empdetails = Employee_details::where("dept_id",54)->paginate($paginationValue);	
					$reportsCountenbd = Employee_details::where("dept_id",54)->get()->count();
					$activeCountenbd = Employee_details::where("dept_id",54)->where('status',1)->get()->count();
					$inactiveCountenbd = Employee_details::where("dept_id",54)->where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('empid_emp_filter_inner_list')) && $request->session()->get('empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				//echo $whereraw;
				if(!empty($request->session()->get('fname_emp_filter_inner_list')) && $request->session()->get('fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				if(!empty($request->session()->get('lname_emp_filter_inner_list')) && $request->session()->get('lname_emp_filter_inner_list') != 'All')
				{
					$lname = $request->session()->get('lname_emp_filter_inner_list');
					 $selectedFilter['l_name'] = $lname;
					 if($whereraw == '')
					{
						$whereraw = 'last_name like "%'.$lname.'%"';
					}
					else
					{
						$whereraw .= ' And last_name like "%'.$lname.'%"';
					}
				}
				if(!empty($request->session()->get('design_emp_filter_inner_list')) && $request->session()->get('design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('jobfunction_emp_filter_inner_list')) && $request->session()->get('jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('RecruiterName_emp_filter_inner_list')) && $request->session()->get('RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
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
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					
					 $locationArray = explode(",",$location);
					 $finallocationArray=array();
					 foreach($locationArray as $_locationArray){
						 $finallocationArray[]="'".$_locationArray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalloc=implode(",", $finallocationArray);
					 if($whereraw == '')
					{
						$whereraw = 'work_location IN('.$finalloc.')';
					}
					else
					{
						$whereraw .= ' And work_location IN('.$finalloc.')';
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				$empIdArray = array();
				if($whereraw == '')
				{
				$appidGet = Employee_details::where("dept_id",54)->get();
				}
				else
				{
					
					$appidGet = Employee_details::whereRaw($whereraw)->where("dept_id",54)->get();
					
				}
				
				foreach($appidGet as $_d)
				{
					if($_d->emp_id != NULL && $_d->emp_id != '')
					{
						$empIdArray[$_d->emp_id] = $_d->emp_id;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				$f_nameArray = array();
				if($whereraw == '')
				{
				$f_namedata = Employee_details::where("dept_id",54)->get();
				}
				else
				{
					
					$f_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",54)->get();
					
				}
				
				foreach($f_namedata as $_f)
				{
					//echo $_f->first_name;exit;
					$f_nameArray[$_f->first_name] = $_f->first_name;
				}
				//print_r();exit;
				$l_nameArray = array();
				if($whereraw == '')
				{
				$l_namedata = Employee_details::where("dept_id",54)->get();
				}
				else
				{
					
					$l_namedata = Employee_details::whereRaw($whereraw)->where("dept_id",54)->get();
					
				}
				
				foreach($l_namedata as $_lname)
				{
					//echo $_lname->last_name;exit;
					$l_nameArray[$_lname->last_name] = $_lname->last_name;
				}
				
				$departmentArray = array();
				if($whereraw == '')
				{
						$department = Department::where("status",1)->orderBy('id','DESC')->get();
				}
				else
				{
					$department =Department::where("status",1)->orderBy('id','DESC')->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$departmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$sourcecodeArray = array();
				if($whereraw == '')
				{
				$soursecode= Employee_details::where("dept_id",54)->get();
				}
				else
				{
					
					$soursecode = Employee_details::whereRaw($whereraw)->where("dept_id",54)->get();
					
				}
				
				foreach($soursecode as $_scode)
				{
					//echo $_lname->last_name;exit;
					$sourcecodeArray[$_scode->source_code] = $_scode->source_code;
				}
				
				$designationArray = array();
				if($whereraw == '')
				{
				$designation= Employee_details::where("dept_id",54)->get();
				}
				else
				{
					
					$designation = Employee_details::whereRaw($whereraw)->where("dept_id",54)->get();
					
				}
				
				foreach($designation as $_designation)
				{
					//echo $_lname->last_name;exit;
					if(!empty($_designation->job_role)){
					$designationArray[$_designation->job_role] = $_designation->job_role;
					}
				}
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = Employee_details::where("dept_id",54)->orderBy("id", "DESC")->get();
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
					//$Vintage = DocumentCollectionDetails::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = Employee_details::whereRaw($whereraw)->where("dept_id",54)->orderBy("id", "DESC")->get();
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
				
				
				$LocationArray = array();
				if($whereraw == '')
				{
				$loc= Employee_details::where("dept_id",54)->get();
				}
				else
				{
					
					$loc = Employee_details::whereRaw($whereraw)->where("dept_id",54)->get();
					
				}
				
				foreach($loc as $_location)
				{
					//echo $_lname->last_name;exit;
					if($_location->work_location !=''){
					$LocationArray[$_location->work_location] = $_location->work_location;
					}
				}
				
				$VisaUnderCompany = array();
				if($whereraw == '')
				{
				//$empdata= Employee_details::get();
				$Collection  = Employee_details::where("dept_id",54)->get();
				if(!empty($Collection)){
				$empid=array();
				foreach($Collection as $_coll)
				{
					$empid[]=$_coll->emp_id;										
				}
	
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
				}
				}
				else
				{
					$Collection = Employee_details::whereRaw($whereraw)->where("dept_id",49)->get();
					$empid=array();
					foreach($Collection as $_coll)
					{
					$empid[]=$_coll->emp_id;					
					
					}
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
					 //print_r($empattributesMod);exit;
					
				}		
				
				//print_r($finaldata);
				if(!empty($empattributesMod)){
				foreach($empattributesMod as $_companyvisa)
				{
				
					if($_companyvisa->attribute_values=='-' || $_companyvisa->attribute_values=='' || $_companyvisa->attribute_values=='NULL'){
						
					}else{
					$VisaUnderCompany[$_companyvisa->attribute_values] = $_companyvisa->attribute_values;
					}
					
				}
				}
				
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->where("dept_id",54)->where("offline_status",1)->paginate($paginationValue);
				$reportsCounthsbc = Employee_details::whereRaw($whereraw)->where("dept_id",54)->where("offline_status",1)->get()->count();
					$activeCounthsbc = Employee_details::whereRaw($whereraw)->where("dept_id",54)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCounthsbc = Employee_details::whereRaw($whereraw)->where("dept_id",54)->where("offline_status",1)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::where("dept_id",54)->where("offline_status",1)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCounthsbc = Employee_details::where("dept_id",54)->where("offline_status",1)->get()->count();	
					$activeCounthsbc = Employee_details::where("dept_id",54)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCounthsbc = Employee_details::where("dept_id",54)->where("offline_status",1)->where('status',2)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/AjaxEmpListHSBCData'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));
			$exportemployeestatus=ExportEmployeeStatus::where('id',1)->first();
			
			return view("EmpProcess/AjaxEmpListHSBC",compact('VisaUnderCompany','LocationArray','VintageArray','empdetails','paginationValue','departmentLists','deptID','reportsCounthsbc','empIdArray','selectedFilter','f_nameArray','l_nameArray','departmentArray','sourcecodeArray','designationArray','activeCounthsbc','inactiveCounthsbc','exportemployeestatus'));
		}





		// New Code Start 16-05-2024


		public function getRequestedEmployeeData(Request $request)
		{
			
			//return $empDataFirst;	
			$userid=$request->session()->get('EmployeeId');
			$userData = User::where("id",$userid)->orderBy('id', 'desc')->first();

			$empsessionId=$request->session()->get('EmployeeId');
			$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
			if($departmentDetails != '')
			{
				$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
				if($empDetails!='')
				{
					$empData = Employee_details::join('department_details', 'employee_details.dept_id', '=', 'department_details.id')
					->select('employee_details.emp_id', 'department_details.department_name', 'employee_details.emp_name')
					->where('offline_status',1)
					->where('offboard_status',2)
					//->where('offboard_status','!=',3)
					->where('employee_details.dept_id',$empDetails->dept_id)
					->get();
				}
			}
			else{
				$empData = Employee_details::join('department_details', 'employee_details.dept_id', '=', 'department_details.id')
				->select('employee_details.emp_id', 'department_details.department_name', 'employee_details.emp_name')
				->where('offline_status',1)
				->where('offboard_status',2)
				
				// ->toSql();
				// dd($empData);
				->get();
			}

			return view("EmpProcess/getRequestedEmployeePop",compact('empData','userData'));
		}

		// New Code End 16-05-2024
		public static function getATL($id = NULL)
	   {
		  
			    $emp_details = Employee_details::where("id",$id)->first(); 
				if($emp_details!=''){
					
					
						 return $emp_details->emp_name;
					}
					else{
						return "--";
					}
		   
	   }
}
