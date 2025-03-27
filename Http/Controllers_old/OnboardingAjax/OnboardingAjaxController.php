<?php

namespace App\Http\Controllers\OnboardingAjax;

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



class OnboardingAjaxController extends Controller
{
    
       public function documentcollection(Request $req)
	   {
		  
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
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
		return view("OnboardingAjax/documentcollectionajax",compact('departmentLists','productDetails','designationDetails','filterList','salaryBreakUpdetails','departmentIdArray'));
	   }
	   
	   public function listingPageonboarding(Request $request)
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
		//$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
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
				
				
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					 $selectedFilter['CNAME'] = $cname;
					 if($whereraw == '')
					{
						$whereraw = 'emp_name = "'.$cname.'"';
					}
					else
					{
						$whereraw .= ' And emp_name = "'.$cname.'"';
					}
				}
				if(!empty($request->session()->get('company_cand_filter_inner_list')) && $request->session()->get('company_cand_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_cand_filter_inner_list');
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
				if(!empty($request->session()->get('desc_cand_filter_inner_list')) && $request->session()->get('desc_cand_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_cand_filter_inner_list');
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
				if(!empty($request->session()->get('dept_cand_filter_inner_list')) && $request->session()->get('dept_cand_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_cand_filter_inner_list');
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
						$whereraw = 'job_opening = "'.$opening.'"';
					}
					else
					{
						$whereraw .= ' And job_opening = "'.$opening.'"';
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
				
				
				
				
				
				$CandidateNameArray = array();
				if($whereraw == '')
				{
					/* echo "sddf";
					exit; */
				$c_namedata = DocumentCollectionDetails::get();
				}
				else
				{
					
					$c_namedata = DocumentCollectionDetails::whereRaw($whereraw)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = DocumentCollectionDetails::get();
				}
				else
				{
					
					$email = DocumentCollectionDetails::whereRaw($whereraw)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = DocumentCollectionDetails::get();
				}
				else
				{
					
					$visa = DocumentCollectionDetails::whereRaw($whereraw)->get();
					
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
					$ventArray = DocumentCollectionDetails::orderBy("id", "DESC")->get();
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
					$ventArray = DocumentCollectionDetails::whereRaw($whereraw)->orderBy("id", "DESC")->get();
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
					$depidArray = DocumentCollectionDetails::get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = DocumentCollectionDetails::whereRaw($whereraw)->get();
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
				$jobArray = DocumentCollectionDetails::get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = DocumentCollectionDetails::whereRaw($whereraw)->get();
					
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
					$OpeningArray[$_opening->id] = $_opening->name;
				}
				$StatusArray = array();
				if($whereraw == '')
				{
				$status =  DocumentCollectionDetails::get();
				}
				else
				{
					$status =  DocumentCollectionDetails::whereRaw($whereraw)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = DocumentCollectionDetails::get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = DocumentCollectionDetails::whereRaw($whereraw)->get();
					
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
					$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC")->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboarding'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboarding",compact('companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
	   public function filterByCandidateName(Request $request)
		{
			$cname = $request->cname;
			$request->session()->put('cname_emp_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmail(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_cand_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignation(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_cand_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartment(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_cand_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpening(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_cand_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatuss(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_cand_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintage(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_cand_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompany(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_cand_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
	   
	   
	   public function documentcollection1(Request $req)
	   {
		  
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		if(!empty($req->session()->get('serialized_id')))
			{
			
				$serialized_id = $req->session()->get('serialized_id');
				$filterList['serialized_id'] = $serialized_id;
				$documentCollectiondetails = $documentCollectiondetails->where("serialized_id","like",$serialized_id."%");
			}
			
		if(!empty($req->session()->get('emp_name')))
			{
			
				$emp_name = $req->session()->get('emp_name');
				$filterList['emp_name'] = $emp_name;
				$documentCollectiondetails = $documentCollectiondetails->where("emp_name","like",$emp_name."%");
			}
		
			if(!empty($req->session()->get('department')))
			{
			
				$department = $req->session()->get('department');
				$filterList['deptID'] = $department;
				$documentCollectiondetails = $documentCollectiondetails->where("department",$department);
			}	
		if(!empty($req->session()->get('caption')))
			{
			
				$caption = $req->session()->get('caption');
				$filterList['caption'] = $caption;
				$documentCollectiondetails = $documentCollectiondetails->where("caption",$caption);
			}	
		if(!empty($req->session()->get('status')))
			{
			
				$status = $req->session()->get('status');
				$filterList['status'] = $status;
				$documentCollectiondetails = $documentCollectiondetails->where("status",$status);
			}		
		if(!empty($req->session()->get('designation')))
			{
			
				$designation = $req->session()->get('designation');
				$filterList['designationID'] = $designation;
				$documentCollectiondetails = $documentCollectiondetails->where("designation",$designation);
			}		
			if(!empty($req->session()->get('visa_process_status')))
			{
			
				$visa_process_status = $req->session()->get('visa_process_status');
				$filterList['visa_process_status'] = $visa_process_status;
				$documentCollectiondetails = $documentCollectiondetails->where("visa_process_status",$visa_process_status);
			}				
			$documentCollectiondetails = $documentCollectiondetails->get();
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("Onboarding/documentcollection",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','filterList','salaryBreakUpdetails'));
	   }
	   
	   
	    public function addDocumentCollectionAjax(Request $request)
	   {
		  
		   $departmentDetails =  Department::where("status",1)->orderBy("id","DESC")->get();
		   $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		   $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		   $hiringSourceList = HiringSourceDetails::where("status",1)->orderBy("id","DESC")->get();
		   $recruiterList = RecruiterDetails::where("status",1)->orderBy("id","DESC")->get();
		    $jobOpeningList = JobOpening::where("status",1)->orderBy("id","DESC")->get();
			return view("OnboardingAjax/adddocumentcollectionajax",compact('departmentDetails','designationDetails','salaryBreakUpdetails','hiringSourceList','recruiterList','jobOpeningList'));
	   }
	  public function getCaptionOfSalaryBreakupforDocument(Request $request)
	   {
		   $deptId = $request->deptId;
		   $designId = $request->designId;
		   $salaryDetails =  SalaryBreakup::where("dept_id",$deptId)->where("designation",$designId)->where("status",1)->get();
		   return view("Onboarding/getCaptionOfSalaryBreakup",compact('salaryDetails'));
	   }
	 function getDesignationOfDocumentation(Request $request)
	   {
		     $deptId = $request->deptId;
			  $designationDetails =  Designation::where("status",1)->where("department_id",$deptId)->orderBy('id','DESC')->get();
			  return view("Onboarding/getDesignationOfDocumentation",compact('designationDetails'));
	   }
	   function getDesignationOfDocumentationList(Request $request)
	   {
		     $deptId = $request->deptId;
			  $designationDetails =  Designation::where("status",1)->where("department_id",$deptId)->orderBy('id','DESC')->get();
			  return view("Onboarding/getDesignationOfDocumentationList",compact('designationDetails'));
	   }
	   
	  public function getSalaryBreakupDocumentAjax(Request $request)
	   {
		     $deptId = $request->deptId;
	   $designId = $request->designId;
	   $caption = $request->cap;
	   $salaryDetails =  SalaryBreakup::where("dept_id",$deptId)->where("designation",$designId)->where("caption",$caption)->first();
	  
	   return view("OnboardingAjax/getSalaryBreakupAjax",compact('salaryDetails'));
	   }
	   
	  
	   
	   public function generatedocumentCollectionPostAjax(Request $request)
	   {
		  
		   $selectedFilter = $request->input();
		   $documentCollectionModel = new DocumentCollectionDetails();
		   $documentCollectionModel->emp_name =  $selectedFilter['documentCollection']['emp_name'];
		   $documentCollectionModel->mobile_no =  $selectedFilter['documentCollection']['mobile_no'];
		   $documentCollectionModel->email =  $selectedFilter['documentCollection']['email'];
		   $documentCollectionModel->hiring_source =  $selectedFilter['documentCollection']['hiring_source'];
		   $documentCollectionModel->recruiter_name =  $selectedFilter['documentCollection']['recruiter_name'];
		   $documentCollectionModel->job_opening =  $selectedFilter['documentCollection']['job_opening'];
		   $documentCollectionModel->designation =  $selectedFilter['documentCollection']['designation'];
		   $documentCollectionModel->department =  $selectedFilter['documentCollection']['department'];
		   $documentCollectionModel->caption =  $selectedFilter['documentCollection']['caption'];
		   $documentCollectionModel->monthly_package =  $selectedFilter['documentCollection']['monthly_package'];
		   $documentCollectionModel->package_id =  $selectedFilter['documentCollection']['package_id'];
		   $documentCollectionModel->location =  $selectedFilter['documentCollection']['location'];
		   $documentCollectionModel->company_visa =  $selectedFilter['documentCollection']['company_visa'];
		   
		   $documentCollectionModel->status =  1;
		   $documentCollectionModel->visa_process_status =  1;
		   $documentCollectionModel->training_process_status =  1;
		   $documentCollectionModel->kyc_status =  1;
		   $documentCollectionModel->update_offer_letter_allow =  1;
		   $documentCollectionModel->created_by =  $request->session()->get('EmployeeId');
		   $documentCollectionModel->save();
		   
		   $documentCollectionUpdate = DocumentCollectionDetails::find($documentCollectionModel->id);
		   $documentCollectionUpdate->serialized_id =  'DocCollection-Inprogress-000'.$documentCollectionModel->id;
		   $documentCollectionUpdate->save();
		  echo "Request Save Successfully";
		  exit;
	   }
	   
	   public function editDocumentCollectionAjax(Request $request)
	   {
		   $dCollectionId = $request->dCollectionId;
		   $documentCollectionData = DocumentCollectionDetails::where("id",$dCollectionId)->first();
		  
		   $departmentDetails =  Department::where("status",1)->orderBy("id","DESC")->get();
		   $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		   $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		   $hiringSourceList = HiringSourceDetails::where("status",1)->orderBy("id","DESC")->get();
		   $recruiterList = RecruiterDetails::where("status",1)->orderBy("id","DESC")->get();
		    $jobOpeningList = JobOpening::where("status",1)->orderBy("id","DESC")->get();
			return view("OnboardingAjax/editDocumentCollectionAjax",compact('departmentDetails','designationDetails','salaryBreakUpdetails','documentCollectionData','hiringSourceList','recruiterList','jobOpeningList'));
	   }
	   
	   public function editdocumentCollectionPostAjax(Request $request)
	   {
		    $selectedFilter = $request->input();
			
			
			 $selectedFilter = $request->input();
		   $documentCollectionModel = DocumentCollectionDetails::find($selectedFilter['documentCollectionEdit']['id']);
		   $documentCollectionModel->emp_name =  $selectedFilter['documentCollectionEdit']['emp_name'];
		   $documentCollectionModel->mobile_no =  $selectedFilter['documentCollectionEdit']['mobile_no'];
		   $documentCollectionModel->email =  $selectedFilter['documentCollectionEdit']['email'];
		   $documentCollectionModel->hiring_source =  $selectedFilter['documentCollectionEdit']['hiring_source'];
		   $documentCollectionModel->recruiter_name =  $selectedFilter['documentCollectionEdit']['recruiter_name'];
		   $documentCollectionModel->job_opening =  $selectedFilter['documentCollectionEdit']['job_opening'];
		   $documentCollectionModel->department =  $selectedFilter['documentCollectionEdit']['department'];
		    $documentCollectionModel->designation =  $selectedFilter['documentCollection']['designation'];
		   $documentCollectionModel->caption =  $selectedFilter['documentCollection']['caption'];
		   $documentCollectionModel->monthly_package =  $selectedFilter['documentCollection']['monthly_package'];
		   $documentCollectionModel->package_id =  $selectedFilter['documentCollection']['package_id'];
		   $documentCollectionModel->location =  $selectedFilter['documentCollection']['location'];
		   $documentCollectionModel->company_visa =  $selectedFilter['documentCollection']['company_visa'];
		   $documentCollectionModel->update_offer_letter_allow =  2;
		 
		   if($documentCollectionModel->save())
		   {
				echo "Request Updated Successfully.";
		   }
		   else
		   {
			   echo "Issue to Updated Successfully.";
		   }
		   
		   exit;		   
		  /*  $request->session()->flash('message','Document Collection updated.');
			return redirect('documentcollection'); */
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
			$documentCollectionModel = DocumentCollectionDetails::find($documentCollectionId);
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
	   
	   public function uploadDocumentAjax(Request $request)
	   {
		   $uploadDetails = array();
		   $id = $request->id; 
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   $documentAttributes = DocumentCollectionAttributes::where("status",1)->where("attribute_area","both")->orWhere("attribute_area","offerletter")->orderBy("sort_order","ASC")->get();
		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   if($_documentCUpload->attribute_value != 'undefined')
			   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
			   }
		   }
			
			
		   return view("OnboardingAjax/uploadDocumentAjax",compact('documentDetails','documentAttributes','uploadDetails'));
	   }
	   
	   public function uploadKYCAjax(Request $request)
	   {
		   $uploadDetails = array();
		   $id = $request->id; 
		   $mode = $request->mode; 
		   $redirectMod = $request->redirectMod;
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   
		   $documentAttributes = DocumentCollectionAttributes::where("status",1)->Where("attribute_area","kyc")->where("department_id",$documentDetails->department)->get();
		   $documentAttributesDetails =KycDocuments::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
		   }
			
				$empRequiredDetails =  Employee_details::where('document_collection_id',$id)->first();
		   return view("OnboardingAjax/uploadKYCAjax",compact('documentDetails','documentAttributes','uploadDetails','mode','empRequiredDetails','redirectMod'));
	   }
	   
	   public function updateDocumentCollectionAttrPost(Request $request)
	   {
		   $selectedFilterInput = $request->input();
		 	
		   $id =  $selectedFilterInput['collectionAttrId'];
		   $documentAttributeModelUpdate = DocumentCollectionAttributes::find($id);
			$documentAttributeModelUpdate->attribute_name = $selectedFilterInput['attribute_name'];
			$documentAttributeModelUpdate->attribute_code = $selectedFilterInput['attribute_code'];
			$documentAttributeModelUpdate->attrbute_type_id = $selectedFilterInput['attrbute_type_id'];
			if($selectedFilterInput['attrbute_type_id'] == 3)
			{
				$documentAttributeModelUpdate->opt = implode(",",$selectedFilterInput['opt']);
			}
			$documentAttributeModelUpdate->attribute_requirement = $selectedFilterInput['attribute_requirement'];
			$documentAttributeModelUpdate->sort_order = $selectedFilterInput['sort_order'];
			$documentAttributeModelUpdate->status = $selectedFilterInput['status'];
			$documentAttributeModelUpdate->attribute_area = $selectedFilterInput['attribute_area'];
			if($selectedFilterInput['attribute_area'] == 'kyc')
			{
				$documentAttributeModelUpdate->department_id = $selectedFilterInput['department_id'];
			}
			else
			{
				$documentAttributeModelUpdate->department_id = NULL;
			}
			$documentAttributeModelUpdate->save();
			$request->session()->flash('message','Attribute Updated Successfully.');
            return redirect('dCollectionAttributes');
	   }
	   
	   public function deleteDocumentCollectionAttr(Request $request)
	   {
		    $attributeId = $request->attrId;
			 $documentAttributeModelUpdate = DocumentCollectionAttributes::find($attributeId);
			 $documentAttributeModelUpdate->delete();
			 $request->session()->flash('message','Attribute Deleted Successfully.');
             return redirect('dCollectionAttributes');
	   }
	   
	   public function appliedFilterOnDocumentCollectionAttribute(Request $request)
	   {
		   $selectedFilter = $request->input();
		   $request->session()->put('attribute_name',$selectedFilter['attribute_name']);		
		   $request->session()->put('attrbute_type_id',$selectedFilter['attrbute_type_id']);
		   $request->session()->put('attribute_area',$selectedFilter['attribute_area']);
		   if($selectedFilter['attribute_area'] == 'kyc')
		   {
				$request->session()->put('department_id',$selectedFilter['department_id']);
		   }
		   else
		   {
			   $request->session()->put('department_id','');
		   }
		   return redirect('dCollectionAttributes');
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
	   
	   public function uploadDocumentStartAjax(Request $request)
	   {
		   $selectedFilter = $request->input();
		/*   echo '<pre>';
		  print_r($selectedFilter);
		  exit; */
		   $saveData = array();
		  
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		   $status = $selectedFilter['status'];
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
			
		
			/*
			*update Status on main Document Collection table
			*/
			$getExistingStatus = DocumentCollectionDetails::where("id",$documentCollectionId)->first()->status;
			$documentCollectionMod = DocumentCollectionDetails::find($documentCollectionId);
			if($getExistingStatus <=3)
			{
				$documentCollectionMod->status = $status;
				if($status == 3)
				{
					$documentCollectionMod->serialized_id = 'Offerletter-DocCollection-Rejected-000'.$documentCollectionId;
				}
				elseif($status == 2)
				{
					$documentCollectionMod->serialized_id = 'Offerletter-DocCollection-Completed-000'.$documentCollectionId;
					$documentCollectionMod->offer_letter_details_date = date("Y-m-d");
				}
				else
				{
					$documentCollectionMod->serialized_id = 'Offerletter-DocCollection-Inprogress-000'.$documentCollectionId;
				}
				
				$documentCollectionMod->save();
			}
			echo "Document Upload Successfully.";
			exit;
	   }
	   
	   
	   
	   public function KYCStartAjax(Request $request)
	   {
		   $selectedFilter = $request->input();
		   
		   $saveData = array();
		  
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		   $kyc_status = $selectedFilter['kyc_status'];
		   $num = $documentCollectionId;
		   $mode = $selectedFilter['mode'];
		   $redirectMod = $selectedFilter['redirectMod'];
		    unset($selectedFilter['_token']);
		    unset($selectedFilter['kyc_status']);
		    unset($selectedFilter['documentCollectionID']);
		    unset($selectedFilter['_url']);
		    unset($selectedFilter['mode']);
		    unset($selectedFilter['redirectMod']);
			
		   
			
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
				 $newFileName = 'KYC-'.$key.'-'.$num.'.'.$fileExtension;
			   
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
				$existDocument = KycDocuments::where("document_collection_id",$documentCollectionId)->where("attribute_code",$key)->first();
				if($existDocument != '')
				{
					$objDocument= KycDocuments::find($existDocument->id);
				}
				else
				{
					$objDocument = new KycDocuments();	
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
					
					$existDocument = KycDocuments::where("document_collection_id",$documentCollectionId)->where("attribute_code",$key)->first();
					if($existDocument != '')
					{
						$objDocument= KycDocuments::find($existDocument->id);
					}
					else
					{
						$objDocument = new KycDocuments();
					}
					$objDocument->document_collection_id = $documentCollectionId;
					$objDocument->attribute_code = $key;
					$objDocument->attribute_value = $filesAttributeInfo[$key];
					$objDocument->save();
					
				}
			}
			
		
			/*
			*update Status on main Document Collection table
			*/
			$getExistingStatus = DocumentCollectionDetails::where("id",$documentCollectionId)->first()->kyc_status;
			$documentCollectionMod = DocumentCollectionDetails::find($documentCollectionId);
			if($getExistingStatus <=3)
			{
				$documentCollectionMod->kyc_status = $kyc_status;
				if($kyc_status == 2)
				{
					
					//$documentCollectionMod->offer_letter_details_date = date("Y-m-d");
				}
				else
				{
					//$documentCollectionMod->serialized_id = 'Offerletter-DocCollection-Inprogress-000'.$documentCollectionId;
				}
				
				$documentCollectionMod->save();
			}
			echo 'KYC Document Upload Successfully.';
			if($mode == 'M')
			{
				
			}
			else
			{
				if($redirectMod == 'B')
				{
					$empRequiredDetails =  Employee_details::where('document_collection_id',$documentCollectionId)->first();
					if($empRequiredDetails == '')
					{
						
					}
					else
					{
					
					}
				}
				else
				{
					
					
						
					
				}
			}
			exit;
	   }
	   
	   public static function getHiringSourceName($hiringSourceId)
	   {
		  return HiringSourceDetails::where("id",$hiringSourceId)->first()->name;
	   }
	   public static function getRecruiterName($recruiterId)
	   {
		   return RecruiterDetails::where("id",$recruiterId)->first()->name;
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
	   public function incentiveLetter(Request $request)
	   {
		   $documentCollectId = $request->documentCollectionId;
		   //$documentCollectionDetails = IncentiveLetterDetails::where("id",$documentCollectId)->first();
		   $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
		   $departmentId = $documentCollectionDetails->department;
		   $designationId = $documentCollectionDetails->designation;
		   $location = $documentCollectionDetails->location;
		  $incentiveLetterDetails = IncentiveLetterDetails::where("department_id",$departmentId)->where("designation_id",$designationId)->where("location",$location)->first();
		  if($incentiveLetterDetails == '')
		  {
			  $request->session()->flash('message','No incentive letter attached with selected department and designation.');
		
		      return redirect('documentcollection');
		  }
		  else
		  {
			  
			   $pathToIncentiveLetter = $incentiveLetterDetails->path.'/'.$incentiveLetterDetails->location.'/'.$documentCollectId;
			   //return redirect()->to($pathToIncentiveLetter);
			   echo "<script>window.open('".url($pathToIncentiveLetter)."', '_blank')</script>";
			   exit;
		  }
		   
	   }
	   
	    public function checkforIncentiveLetter(Request $request)
		   {
			   $documentCollectId = $request->documentCollectionId;
			   
			   $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
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
		    $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
			
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
		   $documentDetails = DocumentCollectionDetails::where("id",$documentCollectionID)->first();
		   return view("OnboardingAjax/bankCodeGenerationAjax",compact('documentDetails'));
	   }
	   public function saveBankCode(Request $request)
	   {
		   $parameterInput = $request->input();
		   $documentCollectionID = $parameterInput['documentCollectionID'];
		   $bankGeneratedCode = $parameterInput['bank_generated_code'];
		   $docCollectionMod = DocumentCollectionDetails::find($documentCollectionID);
		   $docCollectionMod->bank_generated_code=$bankGeneratedCode;
		   $docCollectionMod->status=7;
		   $docCollectionMod->serialized_id = 'ReadyForOnboarding-000'.$documentCollectionID;
		   $docCollectionMod->save();
		   $request->session()->flash('message','Bank Generated Code Saved Successfully.');
		
		   return redirect('documentcollection');
	   }
	   
	    public function saveBankCodeAjax(Request $request)
	   {
		   $parameterInput = $request->input();
		   $documentCollectionID = $parameterInput['documentCollectionID'];
		   $bankGeneratedCode = $parameterInput['bank_generated_code'];
		   $docCollectionMod = DocumentCollectionDetails::find($documentCollectionID);
		   $docCollectionMod->bank_generated_code=$bankGeneratedCode;
		   $docCollectionMod->status=7;
		   $docCollectionMod->serialized_id = 'ReadyForOnboarding-000'.$documentCollectionID;
		   $docCollectionMod->save();
		   echo 'Bank Generated Code Saved Successfully.';
		   exit;
	   }
	   
	   public function finalizationOnboarding(Request $request)
	   {
		    $documentCollectionId = $request->documentCollectionId;
			$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		     return view("Onboarding/finalizationOnboarding",compact('documentCollectionId','documentCollectionDetails'));
	   }
	   public static function getJobOpening($jobOpeningId = NULL)
	   {
		   if($jobOpeningId != NULL)
		   {
			    return JobOpening::where("id",$jobOpeningId)->first()->name;
		   }
		   else
		   {
			    return "--";
		   }
	   }
	   
	   public function setOffSetForOnboarding(Request $request)
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
		 
		 $Collection  = DocumentCollectionDetails::whereDate("vintage_updated_date","<",$dateC)->get();
		 if(count($Collection)>0)
			{
			foreach($Collection as $_coll)
			{
				$details = DocumentCollectionDetails::where("id",$_coll->id)->first();
				
				/*update Obj*/
				$updateOBJ = DocumentCollectionDetails::find($_coll->id);
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
}
