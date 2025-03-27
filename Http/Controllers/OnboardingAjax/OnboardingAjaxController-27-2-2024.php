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
use App\Models\Onboarding\EmployeeIncrement;
use App\Models\Onboarding\EmployeeOnboardData;
use App\Models\Onboarding\EmployeeOnboardLogdata;

class OnboardingAjaxController extends Controller
{
    
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
	   
	   public function listingPageonboardingENBD(Request $request)
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
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
					}
				}
				if(!empty($request->session()->get('company_RecruiterName_filter_inner_list')) && $request->session()->get('company_RecruiterName_filter_inner_list') != 'All')
				{
					$rec_id = $request->session()->get('company_RecruiterName_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
					 $selectedFilter['Recruiter'] = $rec_id;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_name IN('.$rec_id.')';
					}
					else
					{
						$whereraw .= ' And recruiter_name IN ('.$rec_id.')';
					}
				}
				
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				
				if(!empty($request->session()->get('company_visastage_status_filter_inner_list')) && $request->session()->get('company_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('company_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($onBoardingStatusArray);
					 exit; */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_i",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
					}
					
				
					 
				}
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
					}
				}
				
				//echo $whereraw;exit;
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				//$request->session()->put('cname_emp_filter_inner_list','');
				
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				
				
				
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = DocumentCollectionDetails::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = DocumentCollectionDetails::whereRaw($whereraw)->get();
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
				$c_namedata = DocumentCollectionDetails::where("department",9)->get();
				}
				else
				{
					
					$c_namedata = DocumentCollectionDetails::whereRaw($whereraw)->where("department",9)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = DocumentCollectionDetails::where("department",9)->get();
				}
				else
				{
					
					$email = DocumentCollectionDetails::whereRaw($whereraw)->where("department",9)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = DocumentCollectionDetails::where("department",9)->get();
				}
				else
				{
					
					$visa = DocumentCollectionDetails::whereRaw($whereraw)->where("department",9)->get();
					
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
					$ventArray = DocumentCollectionDetails::where("department",9)->orderBy("id", "DESC")->get();
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
					$ventArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",9)->orderBy("id", "DESC")->get();
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
					$depidArray = DocumentCollectionDetails::where("department",9)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",9)->get();
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
				$jobArray = DocumentCollectionDetails::where("department",9)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",9)->get();
					
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
				$status =  DocumentCollectionDetails::where("department",9)->get();
				}
				else
				{
					$status =  DocumentCollectionDetails::whereRaw($whereraw)->where("department",9)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = DocumentCollectionDetails::where("department",9)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",9)->get();
					
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
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("-visa_expiry_date DESC")->whereRaw($whereraw)->where("department",9)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::where("department",9)->orderByRaw("-visa_expiry_date DESC")->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("department",9)->get()->count();
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::where("department",9)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingENBD'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingENBD",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
	   
	   
	   
	    public function listingPageonboardingAll(Request $request)
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
				
				
				
				
				
				
				//echo $whereraw;
				
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
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
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('departmentId_candAll_filter_inner_list')) && $request->session()->get('departmentId_candAll_filter_inner_list') != 'All' && $request->session()->get('departmentId_candAll_filter_inner_list') !=  'null')
				{
					$departmentids = $request->session()->get('departmentId_candAll_filter_inner_list');
					
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
				
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				//echo $whereraw;
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
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
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
				if(!empty($request->session()->get('desc_candAll_filter_inner_list')) && $request->session()->get('desc_candAll_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candAll_filter_inner_list');
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
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				/*
				*consultancy Code
				*/
				$r_id = 0;
				$empsessionIdGet=$request->session()->get('EmployeeId');
				$empDataGetting = Employee::where("id",$empsessionIdGet)->first();
				if($empDataGetting != '')
				{
				
					if($empDataGetting->group_id == 22)
					{
						if($empDataGetting->r_id != '' && $empDataGetting->r_id != NULL)
						{
						$r_id = $empDataGetting->r_id;
						$request->session()->put('company_RecruiterName_filter_inner_list',$r_id);
						}
						else
						{
							$request->session()->put('company_RecruiterName_filter_inner_list',"");
						}
					}
				}
				/*
				*consultancy Code
				*/
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
					}
				}
				//echo $whereraw;
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('current_visa_status_filter_inner_list')) && $request->session()->get('current_visa_status_filter_inner_list') != 'All')
				{
					$current_visa_statusarray = $request->session()->get('current_visa_status_filter_inner_list');
					  $current_visa_status = explode(",",$current_visa_statusarray);
					 if($whereraw == '')
					{
						$whereraw = 'current_visa_status IN("'.$current_visa_statusarray.'")';
					}
					else
					{
						$whereraw .= ' And current_visa_status IN("'.$current_visa_statusarray.'")';
					}
				}
				if(!empty($request->session()->get('company_visastage_status_filter_inner_list')) && $request->session()->get('company_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('company_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($visastage_status);
					 exit;  */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
					}
				
					 //echo $whereraw;exit;
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
				
				
				
				
				
				
				//echo $whereraw;//exit;
				
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("-visa_expiry_date DESC")->whereRaw($whereraw)->paginate($paginationValue);
				
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("-visa_expiry_date DESC")->paginate($paginationValue);
					
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
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingAll'));
				
				
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingAll",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	   }
	   
	   public function listingPageonboardingdeem(Request $request)
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
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('company_visastage_status_filter_inner_list')) && $request->session()->get('company_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('company_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($onBoardingStatusArray);
					 exit; */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_i",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
					}
					
				
					 
				}
			if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
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
				if(!empty($request->session()->get('desc_candDeem_filter_inner_list')) && $request->session()->get('desc_candDeem_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candDeem_filter_inner_list');
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
				$c_namedata = DocumentCollectionDetails::where("department",8)->get();
				}
				else
				{
					
					$c_namedata = DocumentCollectionDetails::whereRaw($whereraw)->where("department",8)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = DocumentCollectionDetails::where("department",8)->get();
				}
				else
				{
					
					$email = DocumentCollectionDetails::whereRaw($whereraw)->where("department",8)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = DocumentCollectionDetails::where("department",8)->get();
				}
				else
				{
					
					$visa = DocumentCollectionDetails::whereRaw($whereraw)->where("department",8)->get();
					
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
					$recruterArray = DocumentCollectionDetails::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = DocumentCollectionDetails::whereRaw($whereraw)->get();
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
					$ventArray = DocumentCollectionDetails::where("department",8)->orderBy("id", "DESC")->get();
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
					$ventArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",8)->orderBy("id", "DESC")->get();
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
					$depidArray = DocumentCollectionDetails::where("department",8)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",8)->get();
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
				$jobArray = DocumentCollectionDetails::where("department",8)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",8)->get();
					
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
				$status =  DocumentCollectionDetails::where("department",8)->get();
				}
				else
				{
					$status =  DocumentCollectionDetails::whereRaw($whereraw)->where("department",8)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = DocumentCollectionDetails::where("department",8)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",8)->get();
					
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
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("-visa_expiry_date DESC")->whereRaw($whereraw)->where("department",8)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::where("department",8)->orderByRaw("-visa_expiry_date DESC")->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("department",8)->get()->count();
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::where("department",8)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingdeem'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingdeem",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
	   
	   
	   
	    public function listingPageonboardingaafaq(Request $request)
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
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('company_visastage_status_filter_inner_list')) && $request->session()->get('company_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('company_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($onBoardingStatusArray);
					 exit; */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_i",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
					}
					
				
					 
				}
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
					}
				}
				if(!empty($request->session()->get('company_candAafaq_filter_inner_list')) && $request->session()->get('company_candAafaq_filter_inner_list') != 'All')
				{
					$company = $request->session()->get('company_candAafaq_filter_inner_list');
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
				if(!empty($request->session()->get('email_candAafaq_filter_inner_list')) && $request->session()->get('email_candAafaq_filter_inner_list') != 'All')
				{
					$email = $request->session()->get('email_candAafaq_filter_inner_list');
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
				if(!empty($request->session()->get('desc_candAafaq_filter_inner_list')) && $request->session()->get('desc_candAafaq_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candAafaq_filter_inner_list');
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
				if(!empty($request->session()->get('dept_candAafaq_filter_inner_list')) && $request->session()->get('dept_candAafaq_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_candAafaq_filter_inner_list');
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
				if(!empty($request->session()->get('status_candAafaq_filter_inner_list')) && $request->session()->get('status_candAafaq_filter_inner_list') != 'All')
				{
					$status = $request->session()->get('status_candAafaq_filter_inner_list');
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
				if(!empty($request->session()->get('vintage_candAafaq_filter_inner_list')) && $request->session()->get('vintage_candAafaq_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_candAafaq_filter_inner_list');
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
					$recruterArray = DocumentCollectionDetails::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = DocumentCollectionDetails::whereRaw($whereraw)->get();
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
				$c_namedata = DocumentCollectionDetails::where("department",43)->get();
				}
				else
				{
					
					$c_namedata = DocumentCollectionDetails::whereRaw($whereraw)->where("department",43)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = DocumentCollectionDetails::where("department",43)->get();
				}
				else
				{
					
					$email = DocumentCollectionDetails::whereRaw($whereraw)->where("department",43)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = DocumentCollectionDetails::where("department",43)->get();
				}
				else
				{
					
					$visa = DocumentCollectionDetails::whereRaw($whereraw)->where("department",43)->get();
					
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
					$ventArray = DocumentCollectionDetails::where("department",43)->orderBy("id", "DESC")->get();
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
					$ventArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",43)->orderBy("id", "DESC")->get();
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
					$depidArray = DocumentCollectionDetails::where("department",43)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",43)->get();
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
				$jobArray = DocumentCollectionDetails::where("department",43)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",43)->get();
					
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
				$status =  DocumentCollectionDetails::where("department",43)->get();
				}
				else
				{
					$status =  DocumentCollectionDetails::whereRaw($whereraw)->where("department",43)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = DocumentCollectionDetails::where("department",43)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",43)->get();
					
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
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("-visa_expiry_date DESC")->whereRaw($whereraw)->where("department",43)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::where("department",43)->orderByRaw("-visa_expiry_date DESC")->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("department",43)->get()->count();
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::where("department",43)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingaafaq'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingaafaq",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
	   public function listingPageonboardingmashreq(Request $request)
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
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
					}
				}
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
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
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('company_visastage_status_filter_inner_list')) && $request->session()->get('company_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('company_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($onBoardingStatusArray);
					 exit; */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_i",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
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
					$recruterArray = DocumentCollectionDetails::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = DocumentCollectionDetails::whereRaw($whereraw)->get();
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
				$c_namedata = DocumentCollectionDetails::where("department",36)->get();
				}
				else
				{
					
					$c_namedata = DocumentCollectionDetails::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = DocumentCollectionDetails::where("department",36)->get();
				}
				else
				{
					
					$email = DocumentCollectionDetails::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = DocumentCollectionDetails::where("department",36)->get();
				}
				else
				{
					
					$visa = DocumentCollectionDetails::whereRaw($whereraw)->where("department",36)->get();
					
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
					$ventArray = DocumentCollectionDetails::where("department",36)->orderBy("id", "DESC")->get();
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
					$ventArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",36)->orderBy("id", "DESC")->get();
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
					$depidArray = DocumentCollectionDetails::where("department",36)->get();
					
					$deptList = array(); 
					foreach($depidArray as $_dept)
					  {
					  $deptList[]  = $_dept->designation;
					  }
					  
				$desc =  Designation::whereIn('id',array_unique($deptList))->get();
				}
				else
				{
					
					$depidArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",36)->get();
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
				$jobArray = DocumentCollectionDetails::where("department",36)->get();
					
					$jobList = array(); 
					foreach($jobArray as $_job)
					  {
					  $jobList[]  = $_job->job_opening;
					  }
				$opening =  JobOpening::whereIn('id',array_unique($jobList))->get();
				}
				else
				{
					$jobArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",36)->get();
					
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
				$status =  DocumentCollectionDetails::where("department",36)->get();
				}
				else
				{
					$status =  DocumentCollectionDetails::whereRaw($whereraw)->where("department",36)->get();
					
				}
				foreach($status as $_status)
				{
					//echo $_f->first_name;exit;
					$StatusArray[$_status->status] = $_status->status;
				}
				$DepartmentArray = array();
				if($whereraw == '')
				{
					$dpetArray = DocumentCollectionDetails::where("department",36)->get();
					
					$dpetList = array(); 
					foreach($dpetArray as $_dpet)
					  {
					  $dpetList[]  = $_dpet->department;
					  }
						$department = Department::whereIn('id',array_unique($dpetList))->get();
				}
				else
				{
					$dpetArray = DocumentCollectionDetails::whereRaw($whereraw)->where("department",36)->get();
					
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
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("-visa_expiry_date DESC")->whereRaw($whereraw)->where("department",36)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::where("department",36)->orderByRaw("-visa_expiry_date DESC")->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("department",36)->get()->count();
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::where("department",36)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingmashreq'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingmashreq",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
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
		
		//Start deem mashreq
		public function filterByCandidateNameDeem(Request $request)
		{
			$cname = $request->cname;
			$request->session()->put('cname_empDeem_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailDeem(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candDeem_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationDeem(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candDeem_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentDeem(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candDeem_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningDeem(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candDeem_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatussDeem(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candDeem_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintageDeem(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candDeem_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanyDeem(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candDeem_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
		
		//Start All
		public function filterByCandidateNameAll(Request $request)
		{
			$cname = $request->cname;
			$request->session()->put('cname_empAll_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailAll(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candAll_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationAll(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candAll_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentAll(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candAll_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningAll(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candAll_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatussAll(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candAll_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintageAll(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candAll_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanyAll(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candAll_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
		
		
		//Start All
		public function filterByCandidateNameAafaq(Request $request)
		{
			$cname = $request->cname;
			$request->session()->put('cname_empAafaq_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailAafaq(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candAafaq_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationAafaq(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candAafaq_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentAafaq(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candAafaq_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningAafaq(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candAafaq_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatussAafaq(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candAafaq_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintageAafaq(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candAafaq_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanyAafaq(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candAafaq_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
	   //masr
	   public function filterByCandidateNamemashreq(Request $request)
		{
			$cname = $request->cname;
			//echo $cname;exit;
			$request->session()->put('cname_empmashreq_filter_inner_list',$cname);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCandidateEmailmashreq(Request $request)
		{
			$email = $request->email;
			$request->session()->put('email_candmashreq_filter_inner_list',$email);
			 //return  redirect('listingPageonboarding');	
		}
		
		public function filterByDesignationmashreq(Request $request)
		{
			$desc = $request->desc;
			$request->session()->put('desc_candmashreq_filter_inner_list',$desc);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByDepartmentmashreq(Request $request)
		{
			$dept = $request->dept;
			$request->session()->put('dept_candmashreq_filter_inner_list',$dept);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByOpeningmashreq(Request $request)
		{
			$opening = $request->opening;
			$request->session()->put('opening_candmashreq_filter_inner_list',$opening);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByStatusmashreq(Request $request)
		{
			$status = $request->status;
			$request->session()->put('status_candmashreq_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByVintagemashreq(Request $request)
		{
			$vintage = $request->vintage;
			$request->session()->put('vintage_candmashreq_filter_inner_list',$vintage);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByCompanymashreq(Request $request)
		{
			$company = $request->company;
			$request->session()->put('company_candmashreq_filter_inner_list',$company);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByRecruiterNameAll(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNameAll_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByRecruiterNamemashreq(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNamemashreq_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByRecruiterNameenbd(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNameenbd_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
	   public function filterByRecruiterNameaafaq(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNameaafaq_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
		public function filterByRecruiterNamedeem(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNamedeem_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
		}
	   public function filterByRecruiterNamevisapipeline(Request $request)
		{
			$rec_id = $request->rec_id;
			$request->session()->put('company_RecruiterNamevisapipeline_filter_inner_list',$rec_id);
			 //return  redirect('listingPageonboarding');	
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
			$newdata=InterviewDetailsProcess::where("interview_id",$documentCollectionData->interview_id)->where("interview_type","final discussion")->first();
			if($newdata!=''){
			$interviewer_name=$newdata->interviewer_name;
			}else{
			$interviewer_name='';	
			}
			
			return view("OnboardingAjax/editDocumentCollectionAjax",compact('interviewer_name','departmentDetails','designationDetails','salaryBreakUpdetails','documentCollectionData','hiringSourceList','recruiterList','jobOpeningList'));
	   }
	   
	   public function editdocumentCollectionPostAjax(Request $request)
	   {
		   
		    $selectedFilter = $request->input();
			$processdata=DocumentCollectionDetails::where("id",$selectedFilter['documentCollectionEdit']['id'])->first();
			$finaljsondata = json_encode(array('DocData' =>$processdata), JSON_PRETTY_PRINT);
			$job=$selectedFilter['documentCollectionEdit']['job_opening'];
			$jobOpning=JobOpening::where("id",$job)->first();
			//print_r($jobOpning);exit;
			$departmentname=$jobOpning->department;
			if($jobOpning->designation!=''){
			$designation=$jobOpning->designation;
			}
			else{
				$designation=10;
			}
			 //$selectedFilter = $request->input();
		   $documentCollectionModel = DocumentCollectionDetails::find($selectedFilter['documentCollectionEdit']['id']);
		   $documentCollectionModel->emp_name =  $selectedFilter['documentCollectionEdit']['emp_name'];
		   $documentCollectionModel->mobile_no =  $selectedFilter['documentCollectionEdit']['mobile_no'];
		   $documentCollectionModel->email =  $selectedFilter['documentCollectionEdit']['email'];
		   //$documentCollectionModel->hiring_source =  $selectedFilter['documentCollectionEdit']['hiring_source'];
		   $documentCollectionModel->recruiter_name =  $selectedFilter['documentCollectionEdit']['recruiter_name'];
		   $documentCollectionModel->job_opening =  $selectedFilter['documentCollectionEdit']['job_opening'];
		   $documentCollectionModel->department =  $departmentname;
		    $documentCollectionModel->designation =  $designation;
		   //$documentCollectionModel->caption =  $selectedFilter['documentCollection']['caption'];
		   //$documentCollectionModel->monthly_package =  $selectedFilter['documentCollection']['monthly_package'];
		   $documentCollectionModel->proposed_salary =  $selectedFilter['documentCollectionEdit']['proposed_salary'];
		   $documentCollectionModel->location =  $selectedFilter['documentCollection']['location'];
		   $documentCollectionModel->current_visa_status =  $selectedFilter['documentCollection']['current_visa_status'];
		   $documentCollectionModel->update_offer_letter_allow =  2;
		 
		   if($documentCollectionModel->save())
		   {
			   if(isset($selectedFilter['documentCollectionEdit']['interviewer_name'])){
				$docdata=DocumentCollectionDetails::where("id",$selectedFilter['documentCollectionEdit']['id'])->first(); 
				 if($docdata!=''){
					 $interviewid=$docdata->interview_id;
					 $newdata=InterviewDetailsProcess::where("interview_id",$interviewid)->where("interview_type","final discussion")->first();
					 if($newdata!=''){
						 $interviewObJ=InterviewDetailsProcess::find($newdata->id);
						 $interviewObJ->interviewer_name=$selectedFilter['documentCollectionEdit']['interviewer_name'];
						 if($interviewObJ->save()){
							$logObj = new DocumentCollectionDetailsLog();
							$logObj->document_id =$selectedFilter['documentCollectionEdit']['id'];
							$logObj->created_by=$request->session()->get('EmployeeId');
							$logObj->title ="update interviewer name";
							$logObj->response ="update interviewer name";
							$logObj->category ="Interview process";
							$logObj->save(); 
						 }
					 }
				 }
			   }
			   
			   $logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$selectedFilter['documentCollectionEdit']['id'];
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="Updated Candidate Details";
				$logObj->response =$finaljsondata;
				$logObj->category ="Offer letter";
				$logObj->save();
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
		   $documentAttributes = DocumentCollectionAttributes::where("attribute_area","both")->orWhere("attribute_area","offerletter")->where("status",1)->orderBy("sort_order","ASC")->get();
		   $bdminterview = DocumentCollectionAttributes::where("attribute_area","bdminterview")->where("status",1)->orderBy("sort_order","ASC")->get();
		   $bgverification = DocumentCollectionAttributes::where("attribute_area","bgverification")->where("status",1)->orderBy("sort_order","ASC")->get();
		   
		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   if($_documentCUpload->attribute_value != 'undefined')
			   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
			   }
		   }
			
			
		   return view("OnboardingAjax/uploadDocumentAjax",compact('bdminterview','documentDetails','documentAttributes','uploadDetails','bgverification'));
	   }
	   public function uploadBGverificationDocumentAjax(Request $request)
	   {
		   $uploadDetails = array();
		   $id = $request->id; 
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   $documentAttributes = DocumentCollectionAttributes::where("attribute_area","bgverification")->where("status",1)->orderBy("sort_order","ASC")->get();
		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   if($_documentCUpload->attribute_value != 'undefined')
			   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
			   }
		   }
			
			
		   return view("OnboardingAjax/uploadBGverificationDocumentAjax",compact('documentDetails','documentAttributes','uploadDetails'));
	   }
	   
	   public function uploadonboardDocumentAjax(Request $request)
	   {
		   $cList = WpCountries::get();
		   $uploadDetails = array();
		   $id = $request->documentCollectionId; 
		   $onboardkyc=OnboardCandidateKyc::where("docId",$id)->first();
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   $documentAttributes = DocumentCollectionAttributes::where("attribute_area","onboard")->where("status",1)->orderBy("sort_order","ASC")->get();
		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   if($_documentCUpload->attribute_value != 'undefined')
			   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
			   }
		   }
			
			
		   return view("OnboardingAjax/uploadonboardDocumentAjax",compact('documentDetails','documentAttributes','uploadDetails','cList','onboardkyc'));
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
			/*
			*update visa expiry date
			*/
			if(isset($selectedFilter[66]) && $selectedFilter[66] != 'undefined')
			{
				
				$docId = $selectedFilter['documentCollectionID'];
				$visaExpiryDate = date("Y-m-d",strtotime($selectedFilter[66]));
				$docMod = DocumentCollectionDetails::find($docId);
				$docMod->visa_expiry_date = $visaExpiryDate;
				if($docMod->save()){
					$finaljsondata = json_encode(array('visa_expiry_date' =>$visaExpiryDate), JSON_PRETTY_PRINT);
					$logObj = new DocumentCollectionDetailsLog();
					$logObj->document_id =$docId;
					$logObj->created_by=$request->session()->get('EmployeeId');
					$logObj->title ="update visa expiry date";
					$logObj->response =$finaljsondata;
					$logObj->category ="Offer letter";
					$logObj->save();
				}
			}
			/*
			*update visa expiry date
			*/
			
		   $saveData = array();
		  
		   $current_employment_status = $selectedFilter['current_employment_status'];
		   $resign_status = $selectedFilter['resign_status'];
		   $dateresign = $selectedFilter['dateresign'];
		   $lastworkingday = $selectedFilter['lastworkingday'];
		   $expecteddateresign = $selectedFilter['expecteddateresign'];
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		   $status = $selectedFilter['status'];
		   $bgverification_status = $selectedFilter['bgverification_status'];
		    $current_visa_status = $selectedFilter['current_visa_status'];
		   $num = $documentCollectionId;
		    unset($selectedFilter['_token']);
		    unset($selectedFilter['status']);
		    unset($selectedFilter['documentCollectionID']);
		    unset($selectedFilter['_url']);
			unset($selectedFilter['current_employment_status']);
			unset($selectedFilter['resign_status']);
			unset($selectedFilter['dateresign']);
			unset($selectedFilter['lastworkingday']);
			unset($selectedFilter['expecteddateresign']);
			//unset($selectedFilter['current_visa_status']);
			
		   
			
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
				$objDocument->created_by=$request->session()->get('EmployeeId');
				if($objDocument->save()){
					$finaljsondata = json_encode(array($key =>$value), JSON_PRETTY_PRINT);
					$logObj = new DocumentCollectionDetailsLog();
					$logObj->document_id =$documentCollectionId;
					$logObj->created_by=$request->session()->get('EmployeeId');
					$logObj->title ="update Document Collection Data";
					$logObj->response =$finaljsondata;
					$logObj->category ="Offer letter";
					$logObj->save();
				}
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
					$objDocument->created_by=$request->session()->get('EmployeeId');
					if($objDocument->save()){
						$finaljsondata = json_encode(array($key =>$filesAttributeInfo[$key]), JSON_PRETTY_PRINT);
						$logObj = new DocumentCollectionDetailsLog();
						$logObj->document_id =$documentCollectionId;
						$logObj->created_by=$request->session()->get('EmployeeId');
						$logObj->title ="update Document Collection Data";
						$logObj->response =$finaljsondata;
						$logObj->category ="Offer letter";
						$logObj->save();
					}
					
				}
			}
			
		
			/*
			*update Status on main Document Collection table
			*/
			
			$getExistingStatus = DocumentCollectionDetails::where("id",$documentCollectionId)->first()->status;
			$documentCollectionMod = DocumentCollectionDetails::find($documentCollectionId);
			//print_r($documentCollectionMod);exit;
			if($getExistingStatus <=3)
			{
				$documentCollectionMod->status = $status;
				$documentCollectionMod->offer_letter_document_createBy = $request->session()->get('EmployeeId');
				$documentCollectionMod->offer_letter_document_date = date("Y-m-d");
				$documentValuescv = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectionId)->where("attribute_code",14)->first();
		
					$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectionId)->where("attribute_code",15)->first();
					if(($documentValuescv!='' && $documentValuescv!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL)){
						$documentCollectionMod->offer_letter_document_status = 2;
						$documentCollectionMod->offer_letter_relased_status = 3;
					}
				
				if($status == 3)
				{
					$documentCollectionMod->serialized_id = 'Offerletter-DocCollection-Rejected-000'.$documentCollectionId;
					$documentCollectionMod->current_visa_status=$current_visa_status;
				}
				elseif($status == 2)
				{
					$documentCollectionMod->serialized_id = 'Offerletter-DocCollection-Completed-000'.$documentCollectionId;
					$documentCollectionMod->offer_letter_details_date = date("Y-m-d");
					$documentCollectionMod->current_visa_status=$current_visa_status;
				}
				else
				{
					$documentCollectionMod->serialized_id = 'Offerletter-DocCollection-Inprogress-000'.$documentCollectionId;
					$documentCollectionMod->current_visa_status=$current_visa_status;
				}
				$documentCollectionMod->offer_letter_details_date = date("Y-m-d");
				$documentCollectionMod->current_visa_status=$current_visa_status;
				
				$documentCollectionMod->save();
			}
			$documentCollectionMod->current_visa_status=$current_visa_status;
			$documentCollectionMod->bgverification_status=$bgverification_status;
			
			if($dateresign!=''){
			$date=$dateresign;
			}
			else{
			$date= $expecteddateresign;
			}
		   if($current_employment_status=="Employed" && ($resign_status=="Yes" ||$resign_status=="No" )){
		   $documentCollectionMod->current_employment_status=$current_employment_status;
		   $documentCollectionMod->resign_status = $resign_status; 
			$documentCollectionMod->resign_date = $date; 
			$documentCollectionMod->resign_created_date =date("Y-m-d"); 
			$documentCollectionMod->resign_created_by = $request->session()->get('EmployeeId');
			$documentCollectionMod->resign_lastworkingday = $lastworkingday;
		   }
		   else{
			  $documentCollectionMod->current_employment_status=$current_employment_status;
		   $documentCollectionMod->resign_status =''; 
			$documentCollectionMod->resign_date = ''; 
			$documentCollectionMod->resign_created_date =date("Y-m-d"); 
			$documentCollectionMod->resign_created_by = $request->session()->get('EmployeeId');
			$documentCollectionMod->resign_lastworkingday = ''; 
		   }
			
			if($bgverification_status==1){
				
			$documentCollectionMod->bgverification_response_date=date("Y-m-d");
			}
			
				
				if($documentCollectionMod->save()){
					$finaljsondata = json_encode(array('current_visa_status' =>$current_visa_status), JSON_PRETTY_PRINT);
						$logObj = new DocumentCollectionDetailsLog();
						$logObj->document_id =$documentCollectionId;
						$logObj->created_by=$request->session()->get('EmployeeId');
						$logObj->title ="update Document Collection Data";
						$logObj->response =$finaljsondata;
						$logObj->category ="Offer letter";
						$logObj->save();
				}
			echo "Document Upload Successfully.";
			exit;
	   }
	   public function uploadBGverificationDocumentStartAjax(Request $request)
	   {
		   $selectedFilter = $request->input();
		   /*echo '<pre>';
		  print_r($selectedFilter);
		  exit; */
		   $saveData = array();
		  
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		   $status = $selectedFilter['bgverification_status'];
		   
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
				if($objDocument->save()){
					$finaljsondata = json_encode(array($key =>$value), JSON_PRETTY_PRINT);
						$logObj = new DocumentCollectionDetailsLog();
						$logObj->document_id =$documentCollectionId;
						$logObj->created_by=$request->session()->get('EmployeeId');
						$logObj->title ="update Document Collection Data";
						$logObj->response =$finaljsondata;
						$logObj->category ="Offer letter";
						$logObj->save();
				}
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
					if($objDocument->save()){
						$finaljsondata = json_encode(array($key =>$filesAttributeInfo[$key]), JSON_PRETTY_PRINT);
						$logObj = new DocumentCollectionDetailsLog();
						$logObj->document_id =$documentCollectionId;
						$logObj->created_by=$request->session()->get('EmployeeId');
						$logObj->title ="update Document Collection Data";
						$logObj->response =$finaljsondata;
						$logObj->category ="Offer letter";
						$logObj->save();
					}
					
				}
			}
			
		
			/*
			*update Status on main Document Collection table
			*/
			$getExistingStatus = DocumentCollectionDetails::where("id",$documentCollectionId)->first()->bgverification_status;
			$documentCollectionMod = DocumentCollectionDetails::find($documentCollectionId);
			
				$documentCollectionMod->bgverification_status = $status;
				
				if($documentCollectionMod->save()){
					$finaljsondata = json_encode(array('bgverification_status' =>$status), JSON_PRETTY_PRINT);
						$logObj = new DocumentCollectionDetailsLog();
						$logObj->document_id =$documentCollectionId;
						$logObj->created_by=$request->session()->get('EmployeeId');
						$logObj->title ="update Document Collection Data";
						$logObj->response =$finaljsondata;
						$logObj->category ="Offer letter";
						$logObj->save();
				}
			
			//echo "Document Upload Successfully.";
			$response['code'] = '200';
		   $response['message'] = "Data Saved Successfully.";
		   $response['docId'] = $documentCollectionId;
		   
			echo json_encode($response);
			//exit;
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
				if($objDocument->save()){
					$finaljsondata = json_encode(array($key =>$value), JSON_PRETTY_PRINT);
						$logObj = new DocumentCollectionDetailsLog();
						$logObj->document_id =$documentCollectionId;
						$logObj->created_by=$request->session()->get('EmployeeId');
						$logObj->title ="update kyc Data";
						$logObj->response =$finaljsondata;
						$logObj->category ="Offer letter";
						$logObj->save();
				}
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
					if($objDocument->save()){
						$finaljsondata = json_encode(array($key =>$filesAttributeInfo[$key]), JSON_PRETTY_PRINT);
						$logObj = new DocumentCollectionDetailsLog();
						$logObj->document_id =$documentCollectionId;
						$logObj->created_by=$request->session()->get('EmployeeId');
						$logObj->title ="update kyc Data";
						$logObj->response =$finaljsondata;
						$logObj->category ="Offer letter";
						$logObj->save();
					}
					
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
		   /*
		   *updating in main employee table
		   */
		    $documentDetails = DocumentCollectionDetails::where("id",$documentCollectionID)->first();
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
		   $docCollectionMod = DocumentCollectionDetails::find($documentCollectionID);
		   $docCollectionMod->bank_generated_code=$bankGeneratedCode;
		 
		   $docCollectionMod->serialized_id = 'ReadyForOnboarding-000'.$documentCollectionID;
		   if($docCollectionMod->save()){
			   $finaljsondata = json_encode(array('DocData' =>$parameterInput), JSON_PRETTY_PRINT);

				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$documentCollectionID;
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="Update Bank Code";
				$logObj->response =$finaljsondata;
				$logObj->category ='Training Process';
				$logObj->save();
		   }
		    /*
		   *updating in main employee table
		   */
		    $documentDetails = DocumentCollectionDetails::where("id",$documentCollectionID)->first();
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
			$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		     return view("Onboarding/finalizationOnboarding",compact('documentCollectionId','documentCollectionDetails'));
	   }
	   public static function getJobOpening($jobOpeningId = NULL)
	   {
		   if($jobOpeningId != NULL)
		   {
			    return JobOpening::where("id",$jobOpeningId)->first();;
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
				if($objDocument->save()){
					$finaljsondata = json_encode(array($key =>$value), JSON_PRETTY_PRINT);
					$logObj = new DocumentCollectionDetailsLog();
					$logObj->document_id =$documentCollectionId;
					$logObj->created_by=$request->session()->get('EmployeeId');
					$logObj->title ="Update Offer Letter and Incentive Letter";
					$logObj->response =$finaljsondata;
					$logObj->category ='Offer letter';
					$logObj->save();
				}
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
					if($objDocument->save())
					{
						$finaljsondata = json_encode(array($key =>$filesAttributeInfo[$key]), JSON_PRETTY_PRINT);
						$logObj = new DocumentCollectionDetailsLog();
						$logObj->document_id =$documentCollectionId;
						$logObj->created_by=$request->session()->get('EmployeeId');
						$logObj->title ="Update Offer Letter and Incentive Letter";
						$logObj->response =$finaljsondata;
						$logObj->category ='Offer letter';
						$logObj->save();
						if($key == 81)
						{
							$documentCollectionMod = DocumentCollectionDetails::find($documentCollectionId);
							$documentCollectionMod->offer_letter_onboarding_status=2;
							$documentCollectionMod->offer_letter_generated_date=date("Y-m-d");
							$documentCollectionMod->offer_letter_createBy=$request->session()->get('EmployeeId');
								$documentCollectionMod->save();
						}
					}
					
				}
			}
			
		
			
			echo "Document Upload Successfully.";
			exit;
	   }
	   public function uploadonboardDocumentStartAjax(Request $request)
	   {
		   
		   $selectedFilter = $request->input();
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		    
		$empattributesMod = OnboardCandidateKyc::where('docId',$documentCollectionId)->first();
												
			if(!empty($empattributesMod))
			{					
			$onboardKYCOBJ = OnboardCandidateKyc::find($empattributesMod->id);
			}
			else
			{
				$onboardKYCOBJ = new OnboardCandidateKyc();
			}
			
			$onboardKYCOBJ->docId =$documentCollectionId;
			$onboardKYCOBJ->onboard_local_address =$request->input('onboard_local_address');
			$onboardKYCOBJ->onboard_dob =$request->input('onboard_dob');
			$onboardKYCOBJ->onboard_contactno =$request->input('onboard_contactno');
			$onboardKYCOBJ->onboard_emergency_contactno=$request->input('onboard_emergency_contactno');
			$onboardKYCOBJ->country =$request->input('country');
			$onboardKYCOBJ->home_country_address =$request->input('onboard_home_country_address');
			$onboardKYCOBJ->home_country_contactno =$request->input('onboard_home_country_contactno');
			$onboardKYCOBJ->gender =$request->input('GNDR');
			$onboardKYCOBJ->email =$request->input('onboard_email');
			$onboardKYCOBJ->createdBY=$request->session()->get('EmployeeId');
			
			$onboardKYCOBJ->save();
					
		  
		  
		  
		   $saveData = array();
		  
		   
		   
		  
		   
		   $num = $documentCollectionId;
		    unset($selectedFilter['_token']);
		    unset($selectedFilter['documentCollectionID']);
		    unset($selectedFilter['_url']);
			unset($selectedFilter['onboard_local_address']);
			unset($selectedFilter['onboard_dob']);
			unset($selectedFilter['onboard_contactno']);
			unset($selectedFilter['onboard_emergency_contactno']);
			unset($selectedFilter['country']);
			unset($selectedFilter['onboard_home_country_address']);
			unset($selectedFilter['onboard_home_country_contactno']);
			unset($selectedFilter['GNDR']);
			unset($selectedFilter['onboard_email']);
			
			
			
		   
			
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
				if($objDocument->save()){
					
					$finaljsondata = json_encode(array($key =>$value), JSON_PRETTY_PRINT);
					$logObj = new DocumentCollectionDetailsLog();
					$logObj->document_id =$documentCollectionId;
					$logObj->created_by=$request->session()->get('EmployeeId');
					$logObj->title ="Update On Board";
					$logObj->response =$finaljsondata;
					$logObj->category ='On Board';
					$logObj->save();
				}
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
					if($objDocument->save()){
						$finaljsondata = json_encode(array($key =>$filesAttributeInfo[$key]), JSON_PRETTY_PRINT);
						$logObj = new DocumentCollectionDetailsLog();
						$logObj->document_id =$documentCollectionId;
						$logObj->created_by=$request->session()->get('EmployeeId');
						$logObj->title ="Update On Board";
						$logObj->response =$finaljsondata;
						$logObj->category ='On Board';
						$logObj->save();
					}
					
				}
			}
			
			/*
			*onboarding Process
			*/
			if($selectedFilter[84] == "YES")
			{
				
				$documentDetailsForOnboarding = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
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
					$onboardempdata=EmployeeOnboardData::where("document_id",$documentCollectionId)->first();
					$onboardstatusdata=DocumentCollectionDetails::where("onboard_status",1)->first();
					$empincid=EmployeeIncrement::where("id",1)->first();
					if($onboardempdata!=''){
						$EMPID = $onboardempdata->emp_id;
						$newEMPID = $onboardempdata->emp_id;
					}
					else{
						$EMPID = $empincid->increment_id;
						$newEMPID = $empincid->increment_id;
					}
					
					
					
					$newEmpModel->emp_id = $EMPID;
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
					$newEmpModel->emp_name =$empName;
					$newEmpModel->onboarding_status = 1;
					$newEmpModel->document_collection_id = $documentCollectionId;
					$newEmpModel->interview_id =$documentDetailsForOnboarding->interview_id;
					if($documentDetailsForOnboarding->location == 'AUH')
									{
										$worklocation = 'ABU DHABI';
									}
									elseif($documentDetailsForOnboarding->location == 'DXB')
									{
									$worklocation = 'DUBAI';
									}
									elseif($documentDetailsForOnboarding->location == 'Karachi')
									{
									$worklocation = 'Karachi';
									}
									else
									{
										$worklocation = 'NA';
									}
					$newEmpModel->work_location = $worklocation;
					$newEmpModel->recruiter = $documentDetailsForOnboarding->recruiter_name;
					$newEmpModel->job_opening_id = $documentDetailsForOnboarding->job_opening;
					$newEmpModel->status = 1;
					$newEmpModel->source_code = $documentDetailsForOnboarding->bank_generated_code;
					$newEmpModel->designation_by_doc_collection = $documentDetailsForOnboarding->designation;
					$newEmpModel->doj = date("Y-m-d",strtotime($selectedFilter[83]));
					$newEmpModel->offline_status = 1;
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
							$newEmpModel->job_function = $designationMod->job_function;
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
									elseif($documentDetailsForOnboarding->location == 'Karachi')
									{
									$employeeAttribute->attribute_values = 'Karachi';
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
									$documentkycdata = OnboardCandidateKyc::where('docId',$documentCollectionId)->first();
									if($documentkycdata!=''){
										
									if($documentkycdata->onboard_local_address != '')
									{
										
									$employeeAttribute = new Employee_attribute();
									$employeeAttribute->emp_id = $newEMPID;
									$employeeAttribute->dept_id = $deptId;
									$employeeAttribute->attribute_code = 'LOC_ADD';
									$employeeAttribute->attribute_values = $documentkycdata->onboard_local_address;									
									$employeeAttribute->status = 1;
									$employeeAttribute->save();
									}
									if($documentkycdata->onboard_dob != '')
									{	
									$employeeAttribute = new Employee_attribute();
									$employeeAttribute->emp_id = $newEMPID;
									$employeeAttribute->dept_id = $deptId;
									$employeeAttribute->attribute_code = 'EMPDOB';
									$employeeAttribute->attribute_values = $documentkycdata->onboard_dob;									
									$employeeAttribute->status = 1;
									$employeeAttribute->save();
									}
									if($documentkycdata->onboard_contactno != '')
									{	
									$employeeAttribute = new Employee_attribute();
									$employeeAttribute->emp_id = $newEMPID;
									$employeeAttribute->dept_id = $deptId;
									$employeeAttribute->attribute_code = 'CONTACT_NUMBER';
									$employeeAttribute->attribute_values = $documentkycdata->onboard_contactno;									
									$employeeAttribute->status = 1;
									$employeeAttribute->save();
									}
									if($documentkycdata->onboard_emergency_contactno != '')
									{	
									$employeeAttribute = new Employee_attribute();
									$employeeAttribute->emp_id = $newEMPID;
									$employeeAttribute->dept_id = $deptId;
									$employeeAttribute->attribute_code = 'emergency_contact_number';
									$employeeAttribute->attribute_values = $documentkycdata->onboard_emergency_contactno;									
									$employeeAttribute->status = 1;
									$employeeAttribute->save();
									}
									if($documentkycdata->country != '')
									{	
									$employeeAttribute = new Employee_attribute();
									$employeeAttribute->emp_id = $newEMPID;
									$employeeAttribute->dept_id = $deptId;
									$employeeAttribute->attribute_code = 'NAT';
									$employeeAttribute->attribute_values = $documentkycdata->country;									
									$employeeAttribute->status = 1;
									$employeeAttribute->save();
									}
									if($documentkycdata->home_country_address != '')
									{	
									$employeeAttribute = new Employee_attribute();
									$employeeAttribute->emp_id = $newEMPID;
									$employeeAttribute->dept_id = $deptId;
									$employeeAttribute->attribute_code = 'HOME_COUN_ADD';
									$employeeAttribute->attribute_values = $documentkycdata->home_country_address;									
									$employeeAttribute->status = 1;
									$employeeAttribute->save();
									}
									if($documentkycdata->home_country_contactno != '')
									{	
									$employeeAttribute = new Employee_attribute();
									$employeeAttribute->emp_id = $newEMPID;
									$employeeAttribute->dept_id = $deptId;
									$employeeAttribute->attribute_code = 'HC_CONTACT_NUMBER';
									$employeeAttribute->attribute_values = $documentkycdata->home_country_contactno;									
									$employeeAttribute->status = 1;
									$employeeAttribute->save();
									}
									if($documentkycdata->gender != '')
									{	
									$employeeAttribute = new Employee_attribute();
									$employeeAttribute->emp_id = $newEMPID;
									$employeeAttribute->dept_id = $deptId;
									$employeeAttribute->attribute_code = 'GNDR';
									$employeeAttribute->attribute_values = $documentkycdata->gender;									
									$employeeAttribute->status = 1;
									$employeeAttribute->save();
									}
									if($documentkycdata->email != '')
									{	
									$employeeAttribute = new Employee_attribute();
									$employeeAttribute->emp_id = $newEMPID;
									$employeeAttribute->dept_id = $deptId;
									$employeeAttribute->attribute_code = 'email';
									$employeeAttribute->attribute_values = $documentkycdata->email;									
									$employeeAttribute->status = 1;
									$employeeAttribute->save();
									}
									
									}
									$detailsObj = DocumentCollectionDetails::find($documentCollectionId);
									$detailsObj->status = 8;
									$detailsObj->onboard_status=2; 
									$detailsObj->onboard_date=date("Y-m-d");
									$detailsObj->onboard_createBy=$request->session()->get('EmployeeId');
									if($detailsObj->save()){
										$finaljsondata = json_encode(array("status"=>8,"onboard_status"=>2), JSON_PRETTY_PRINT);
										$logObj = new DocumentCollectionDetailsLog();
										$logObj->document_id =$documentCollectionId;
										$logObj->created_by=$request->session()->get('EmployeeId');
										$logObj->title ="Update On Board";
										$logObj->response =$finaljsondata;
										$logObj->category ='On Board';
										$logObj->save();
									}
									
						/*
						*employee Attribute
						*/
					}
					
					$incOBJ=EmployeeIncrement::find(1);
					$incOBJ->increment_id=$newEMPID+1;
					$incOBJ->save();
					$onboardstatusdata=DocumentCollectionDetails::where("id",$documentCollectionId)->whereIn("visa_process_status",array(0,1))->first();
					if($onboardstatusdata!=''){					
					$logonboard= new EmployeeOnboardLogdata();
					$logonboard->document_id=$documentCollectionId;
					$logonboard->emp_id=$newEMPID;
					$logonboard->status=2;
					$logonboard->save();
					
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
				if($objDocument->save()){
					$finaljsondata = json_encode(array($key =>$value), JSON_PRETTY_PRINT);
					$logObj = new DocumentCollectionDetailsLog();
					$logObj->document_id =$documentCollectionId;
					$logObj->created_by=$request->session()->get('EmployeeId');
					$logObj->title ="Updated Visa Information";
					$logObj->response =$finaljsondata;
					$logObj->category ='Visa Process';
					$logObj->save();
				}
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
					if($objDocument->save()){
						$finaljsondata = json_encode(array($key =>$filesAttributeInfo[$key]), JSON_PRETTY_PRINT);
						$logObj = new DocumentCollectionDetailsLog();
						$logObj->document_id =$documentCollectionId;
						$logObj->created_by=$request->session()->get('EmployeeId');
						$logObj->title ="Updated Visa Information";
						$logObj->response =$finaljsondata;
						$logObj->category ='Visa Process';
						$logObj->save();
					}
					
				}
			}
			$doccollection =DocumentCollectionDetails::where("id",$documentCollectionId)->first();
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
							if($employeeAttribute->save()){
								$finaljsondata = json_encode(array($attribute_code =>$_attribute->attribute_value,"status"=>1), JSON_PRETTY_PRINT);
								$logObj = new DocumentCollectionDetailsLog();
								$logObj->document_id =$documentCollectionId;
								$logObj->created_by=$request->session()->get('EmployeeId');
								$logObj->title ="Updated Visa Information";
								$logObj->response =$finaljsondata;
								$logObj->category ='Visa Process';
								$logObj->save();
							}
							
							}
						}
						
						
					}
				
			}
		
			
			$response['code'] = '200';
			$response['visaId'] = $documentCollectionId;
			
			echo json_encode($response);
		   exit;
	   }
	   public function listingPageonboardingVisapipeline(Request $request)
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
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				
				if(!empty($request->session()->get('company_visastage_status_filter_inner_list')) && $request->session()->get('company_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('company_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($onBoardingStatusArray);
					 exit; */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_i",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
					}
				
					 
				}
				
				
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
					}
				}
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('departmentId_visaapprove_filter_inner_list')) && $request->session()->get('departmentId_visaapprove_filter_inner_list') != 'All' && $request->session()->get('departmentId_visaapprove_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_visaapprove_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('visaapprove_bg_filter_inner_list')) && $request->session()->get('visaapprove_bg_filter_inner_list') != 'All')
				{
					$bgstatus = $request->session()->get('visaapprove_bg_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'bgverification_status IN('.$bgstatus.')';
					}
					else
					{
						$whereraw .= ' And bgverification_status IN('.$bgstatus.')';
					}
				}
				if(!empty($request->session()->get('visaapprove_resign_filter_inner_list')) && $request->session()->get('visaapprove_resign_filter_inner_list') != 'All')
				{
					$resign = $request->session()->get('visaapprove_resign_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'resign_status IN("'.$resign.'")';
					}
					else
					{
						$whereraw .= ' And resign_status IN("'.$resign.'")';
					}
				}
				if(!empty($request->session()->get('datefrom_visaapprove_approval_filter_inner_list')) && $request->session()->get('datefrom_visaapprove_approval_filter_inner_list') != 'All')
				{
					$datefromapproval = $request->session()->get('datefrom_visaapprove_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefromapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefromapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visaapprove_approval_filter_inner_list')) && $request->session()->get('dateto_visaapprove_approval_filter_inner_list') != 'All')
				{
					$datetoapproval = $request->session()->get('dateto_visaapprove_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$datetoapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$datetoapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('datefrom_visaapprove_expected_filter_inner_list')) && $request->session()->get('datefrom_visaapprove_expected_filter_inner_list') != 'All')
				{
					$datefromexpected = $request->session()->get('datefrom_visaapprove_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visaapprove_expected_filter_inner_list')) && $request->session()->get('dateto_visaapprove_expected_filter_inner_list') != 'All')
				{
					$datetoexpected = $request->session()->get('dateto_visaapprove_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
				}
				
				
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = DocumentCollectionDetails::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = DocumentCollectionDetails::whereRaw($whereraw)->get();
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
				$c_namedata = DocumentCollectionDetails::where("ok_visa",2)->get();
				}
				else
				{
					
					$c_namedata = DocumentCollectionDetails::whereRaw($whereraw)->where("ok_visa",2)->get();
					
				}
				foreach($c_namedata as $_cname)
				{
					//echo $_f->first_name;exit;
					$CandidateNameArray[$_cname->emp_name] = $_cname->emp_name;
				}
				$CandidateEmailArray = array();
				if($whereraw == '')
				{
				$email = DocumentCollectionDetails::where("ok_visa",2)->get();
				}
				else
				{
					
					$email = DocumentCollectionDetails::whereRaw($whereraw)->where("ok_visa",2)->get();
					
				}
				foreach($email as $_email)
				{
					//echo $_f->first_name;exit;
					$CandidateEmailArray[$_email->email] = $_email->email;
				}
				$companyvisaArray = array();
				if($whereraw == '')
				{
				$visa = DocumentCollectionDetails::where("ok_visa",2)->get();
				}
				else
				{
					
					$visa = DocumentCollectionDetails::whereRaw($whereraw)->where("ok_visa",2)->get();
					
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
					$ventArray = DocumentCollectionDetails::where("ok_visa",2)->orderBy("id", "DESC")->get();
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
					$ventArray = DocumentCollectionDetails::whereRaw($whereraw)->where("ok_visa",2)->orderBy("id", "DESC")->get();
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
				
				
				
				
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_approved_date DESC")->whereRaw($whereraw)->where("ok_visa",2)->where("backout_status",1)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::where("ok_visa",2)->where("backout_status",1)->orderByRaw("visa_approved_date DESC")->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("ok_visa",2)->where("backout_status",1)->get()->count();
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::where("ok_visa",2)->where("backout_status",1)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingVisapipeline'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingvisapipeline",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter'));
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
			$detailsObj = DocumentCollectionDetails::find($docid);
			$detailsObj->ok_visa = 2; 
			$detailsObj->save();
		}
		public function documentcollectionbyfilter(Request $request)
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
			
			//print_r($request->input());exit;
			$visastagestatus=$request->input('visastagestatus');
			
			
			//$name = $request->input('candidatename');
			$backout = $request->input('backout');
			$job_openingarray = $request->input('job_opening');
			if($request->input('job_opening') != '')
			{
				$job_openingarray  = array_filter($job_openingarray);
			}
			if($job_openingarray!=''){
			$job_opening=implode(",", $job_openingarray);
			}
			else{
				$job_opening='';
			}
			$RecruiterNamecatarray=$request->input('recruiterNamecat');
			if($RecruiterNamecatarray != '')
			{
				$RecruiterNamecatarray  = array_filter($RecruiterNamecatarray);
			}
			if($RecruiterNamecatarray!=''){
			$RecruiterNamecat=implode(",", $RecruiterNamecatarray);
			}
			else{
				$RecruiterNamecat='';
			}
			
			$RecruiterNamearray=$request->input('recruiterName');
			if($RecruiterNamearray != '')
			{
				$RecruiterNamearray  = array_filter($RecruiterNamearray);
			}
			if($RecruiterNamearray!=''){
			$RecruiterName=implode(",", $RecruiterNamearray);
			}
			else{
				$RecruiterName='';
			}
			$visastage_statusarray=$request->input('visastage_status');
			if($request->input('visastage_status') != '')
			{
				$visastage_statusarray  = array_filter($visastage_statusarray);
			}
			if($visastage_statusarray!=''){
			$visastage_status=implode(",", $visastage_statusarray);
			}
			else{
				$visastage_status='';
			}
			
			$interview_approved_by=$request->input('interview_approved_by');
			if($request->input('interview_approved_by') != '')
			{
				$interview_approved_by  = array_filter($interview_approved_by);
			}
			if($interview_approved_by!=''){
			$interview_approved_by=implode(",", $interview_approved_by);
			}
			else{
				$interview_approved_by='';
			}
			
			$dateto = $request->dateto;
			$datefrom = $request->datefrom;
			
			//echo $RecruiterName;exit;
			$request->session()->put('cname_emp_filter_inner_list',$name);
			$request->session()->put('opening_cand_filter_inner_list',$job_opening);
			$request->session()->put('company_RecruiterNamecat_filter_inner_list',$RecruiterNamecat);
			$request->session()->put('company_RecruiterName_filter_inner_list',$RecruiterName);
			$request->session()->put('company_backout_filter_inner_list',$backout);
			$request->session()->put('company_visastage_status_filter_inner_list',$visastage_status);
			$request->session()->put('interview_approved_by_filter_inner_list',$interview_approved_by);
			//$request->session()->put('onboard_special_status_filter_inner_list',$special_status);
			$request->session()->put('dateto_candAll_filter_inner_list',$dateto);
			$request->session()->put('datefrom_candAll_filter_inner_list',$datefrom);
			
			
			$response['code'] = '200';
		   $response['message'] = "Data Saved Successfully.";
		   $response['visa'] = $visastagestatus;
		   
			echo json_encode($response);
			
		}
		public function documentresetfilter(Request $request)
		{
			
			$request->session()->put('cname_emp_filter_inner_list','');
			$request->session()->put('opening_cand_filter_inner_list','');
			$request->session()->put('company_RecruiterNamecat_filter_inner_list','');
			$request->session()->put('company_backout_filter_inner_list','');
			$request->session()->put('company_visastage_status_filter_inner_list','');
			$request->session()->put('departmentId_candAll_filter_inner_list','');
			$request->session()->put('departmentId_canddepartmentListofferletter_filter_inner_list','');
			$request->session()->put('departmentId_candofferlettercomplete_filter_inner_list','');
			$request->session()->put('departmentId_candonboard_filter_inner_list','');
			$request->session()->put('departmentId_candbackout_filter_inner_list','');
			$request->session()->put('filterpendingofferletter_filter_inner_list','');
			$request->session()->put('departmentId_candvisadocumentsstatus_filter_inner_list','');
			$request->session()->put('interview_approved_by_filter_inner_list','');
			//$request->session()->put('onboard_special_status_filter_inner_list','');
			$request->session()->put('dateto_candAll_filter_inner_list','');
			$request->session()->put('datefrom_candAll_filter_inner_list','');
			
			
			
			
			/*
				*consultancy Code
				*/
				$r_id = 0;
				$empsessionIdGet=$request->session()->get('EmployeeId');
				$empDataGetting = Employee::where("id",$empsessionIdGet)->first();
				if($empDataGetting != '')
				{
				
					if($empDataGetting->group_id != 22)
					{
						$request->session()->put('company_RecruiterName_filter_inner_list','');
					}
				}
				else
				{
					$request->session()->put('company_RecruiterName_filter_inner_list','');
				}
				/*
				*consultancy Code
				*/
			
			
			
			
		}
			public function listingPageonboardingRequestedVisapipeline(Request $request)
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
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				
				if(!empty($request->session()->get('company_visastage_status_filter_inner_list')) && $request->session()->get('company_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('company_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($onBoardingStatusArray);
					 exit; */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_i",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
					}
					
				
					 
				}
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
					}
				}
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('departmentId_visarequest_filter_inner_list')) && $request->session()->get('departmentId_visarequest_filter_inner_list') != 'All' && $request->session()->get('departmentId_visarequest_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_visarequest_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('visarequest_bg_filter_inner_list')) && $request->session()->get('visarequest_bg_filter_inner_list') != 'All')
				{
					$bgstatus = $request->session()->get('visarequest_bg_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'bgverification_status IN('.$bgstatus.')';
					}
					else
					{
						$whereraw .= ' And bgverification_status IN('.$bgstatus.')';
					}
				}
				if(!empty($request->session()->get('visarequest_documents_status_filter_inner_list')) && $request->session()->get('visarequest_documents_status_filter_inner_list') != 'All')
				{
					$visa_documents_status = $request->session()->get('visarequest_documents_status_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'visa_documents_status IN('.$visa_documents_status.')';
					}
					else
					{
						$whereraw .= ' And visa_documents_status IN('.$visa_documents_status.')';
					}
				}
				if(!empty($request->session()->get('visarequest_resign_filter_inner_list')) && $request->session()->get('visarequest_resign_filter_inner_list') != 'All')
				{
					$resign = $request->session()->get('visarequest_resign_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'resign_status IN("'.$resign.'")';
					}
					else
					{
						$whereraw .= ' And resign_status IN("'.$resign.'")';
					}
				}
				if(!empty($request->session()->get('datefrom_visarequest_approval_filter_inner_list')) && $request->session()->get('datefrom_visarequest_approval_filter_inner_list') != 'All')
				{
					$datefromapproval = $request->session()->get('datefrom_visarequest_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefromapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefromapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visarequest_approval_filter_inner_list')) && $request->session()->get('dateto_visarequest_approval_filter_inner_list') != 'All')
				{
					$datetoapproval = $request->session()->get('dateto_visarequest_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$datetoapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$datetoapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('datefrom_visarequest_expected_filter_inner_list')) && $request->session()->get('datefrom_visarequest_expected_filter_inner_list') != 'All')
				{
					$datefromexpected = $request->session()->get('datefrom_visarequest_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visarequest_expected_filter_inner_list')) && $request->session()->get('dateto_visarequest_expected_filter_inner_list') != 'All')
				{
					$datetoexpected = $request->session()->get('dateto_visarequest_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
				}
				
				//echo $whereraw;
				
				
				
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_requested_date DESC")->whereRaw($whereraw)->where("ok_visa",3)->where("backout_status",1)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::where("ok_visa",3)->where("backout_status",1)->orderByRaw("visa_requested_date DESC")->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("ok_visa",3)->where("backout_status",1)->get()->count();
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::where("ok_visa",3)->where("backout_status",1)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingRequestedVisapipeline'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingrequestedvisapipeline",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter'));
	   }
		
	   public function ApproveRequestedVisaPost(Request $request){
			$docid=$request->docId;
			$detailsObj = DocumentCollectionDetails::find($docid);
			$detailsObj->ok_visa = 2; 
			$detailsObj->visa_approved_date=date("Y-m-d");
			if($detailsObj->save()){
				$finaljsondata = json_encode(array('ok_visa' =>2), JSON_PRETTY_PRINT);
				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$docid;
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="Approved visa Status";
				$logObj->response =$finaljsondata;
				$logObj->category ="Offer Letter";
				$logObj->save();
			}
		}
		public function DisApprovedVisaRequestedVisaPost(Request $request){
			$docid=$request->docId;
			$detailsObj = DocumentCollectionDetails::find($docid);
			$detailsObj->ok_visa = 4; 
			if($detailsObj->save()){
				$finaljsondata = json_encode(array('ok_visa' =>4), JSON_PRETTY_PRINT);
				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$docid;
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="DisApproved visa Status";
				$logObj->response =$finaljsondata;
				$logObj->category ="Offer Letter";
				$logObj->save();
			}
		}
		public function RequestVisaPost(Request $request){
			$docid=$request->docId;
			$detailsObj = DocumentCollectionDetails::find($docid);
			$detailsObj->ok_visa = 3; 
			$detailsObj->visa_requested_date=date("Y-m-d");
			if($detailsObj->save()){
				$finaljsondata = json_encode(array('ok_visa' =>3), JSON_PRETTY_PRINT);
				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$docid;
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="Request visa Status";
				$logObj->response =$finaljsondata;
				$logObj->category ="Offer Letter";
				$logObj->save();
			}
		}
		public function BackoutDocumentStartAjax(Request $request){
			//print_r($request->input());exit;
			$docid=$request->input('documentCollectionID');
			$bckout_description=$request->input('bckout_description');
			$backoutobj=new DocumentCollectionBackout();
			$backoutobj->document_id=$docid;
			$backoutobj->backout_description=$bckout_description;
			$backoutobj->backout_reason=$request->input('backout_reason');
			$backoutobj->otherdetails=$request->input('otherdetails');
			$backoutobj->status=1;
			$backoutobj->save();
			$detailsObj = DocumentCollectionDetails::find($docid);
			$detailsObj->backout_status = 2; 
			$detailsObj->backout_createBy =$request->session()->get('EmployeeId');
			$detailsObj->backout_create_date = date("Y-m-d");
			if($detailsObj->save()){
				$finaljsondata = json_encode(array('DocData' =>$request->input()), JSON_PRETTY_PRINT);

				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$docid;
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="Update Backout Details";
				$logObj->response =$finaljsondata;
				$logObj->category ="Offer Letter";
				$logObj->save();
			}
			echo "Backout update Successfully.";
			exit;
		}
		public function checkVisaStage($docid=NULL){
			$docCollection = DocumentCollectionDetails::where("id",$docid)->first();
			if($docCollection!=''){
				$status=$docCollection->offer_letter_status;
				$visa_process_status=$docCollection->visa_process_status;
				$training_process_status=$docCollection->training_process_status;
				$visaProcess = Visaprocess::where("document_id",$docid)->get()->count();
				$trainingprocess = TrainingProcess::where("document_id",$docid)->get()->count();
				if($status==1 && $visaProcess==0 && $trainingprocess==0){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 1; 
					$docmentObj->save();
					
				}
				if($status==1 && $visaProcess>0 && $trainingprocess==0){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 2; 
					$docmentObj->save();
					
				}
				if($status==1 && $visaProcess>0 && $trainingprocess>0){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 3; 
					$docmentObj->save();
					
				}
				if($status==1 && $visa_process_status==4 && $trainingprocess>0){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 4; 
					$docmentObj->save();
					
				}
				if($status==1 && $visa_process_status==4 && $training_process_status==4){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 5; 
					$docmentObj->save();
					
				}
				if($status==1 && $visa_process_status==4 && $trainingprocess==0){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 6; 
					$docmentObj->save();
					
				}
				if($status==1 && $visaProcess==0 && $training_process_status==4){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 7; 
					$docmentObj->save();
					
				}
				if($status==1 && $visaProcess>0 && $training_process_status==4){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 8; 
					$docmentObj->save();
					
				}
				
				if($status==2 && $visaProcess==0 && $trainingprocess==0){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 9; 
					$docmentObj->save();
					
				}
				if($status==2 && $visaProcess>0 && $trainingprocess==0){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 10; 
					$docmentObj->save();
					
				}
				if($status==2 && $visaProcess>0 && $trainingprocess>0){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 11; 
					$docmentObj->save();
					
				}
				if($status==2 && $visa_process_status==4 && $trainingprocess>0){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 12; 
					$docmentObj->save();
					
				}
				if($status==2 && $visa_process_status==4 && $training_process_status==4){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 13; 
					$docmentObj->save();
					
				}
				
				if($status==2 && $visa_process_status==4 && $trainingprocess==0){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 14; 
					$docmentObj->save();
					
				}
				if($status==2 && $visaProcess==0 && $training_process_status==4){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 15; 
					$docmentObj->save();
					
				}
				if($status==2 && $visaProcess>0 && $training_process_status==4){
					$docmentObj = DocumentCollectionDetails::find($docid);
					$docmentObj->onboarding_status_final = 16; 
					$docmentObj->save();
					
				}
				
				
				
				
			}
			
		}
		public function requestdocmentdataPost(Request $request)
	   {
		   $selectedFilter = $request->input();
		   /*echo '<pre>';
		  print_r($selectedFilter);
		  exit; */
		  //print_r($_FILES);exit;
		   $saveData = array();
		  
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		   
		   
		   $num = $documentCollectionId;
		    unset($selectedFilter['_token']);
		    //unset($selectedFilter['status']);
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
				if($objDocument->save()){
					$finaljsondata = json_encode(array($key=>$value), JSON_PRETTY_PRINT);
					$logObj = new DocumentCollectionDetailsLog();
					$logObj->document_id =$documentCollectionId;
					$logObj->created_by=$request->session()->get('EmployeeId');
					$logObj->title ="Upload Document Details";
					$logObj->response =$finaljsondata;
					$logObj->category ="Offer Letter";
					$logObj->save();
				}
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
					if($objDocument->save()){
						$finaljsondata = json_encode(array($key=>$filesAttributeInfo[$key]), JSON_PRETTY_PRINT);
						$logObj = new DocumentCollectionDetailsLog();
						$logObj->document_id =$documentCollectionId;
						$logObj->created_by=$request->session()->get('EmployeeId');
						$logObj->title ="Upload Document Details";
						$logObj->response =$finaljsondata;
						$logObj->category ="Offer Letter";
						$logObj->save();
					}
					
				}
			}
		
			$response['code'] = '200';
		   $response['message'] = "Data Saved Successfully.";
		   $response['docId'] = $documentCollectionId;
		   
			echo json_encode($response);
			//exit;
	   }
	   	   public function exportDocReport(Request $request){
		$parameters = $request->input(); 
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Doc_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:X1');
			$sheet->setCellValue('A1', 'Doc List - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('EMP ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Final Discussion Approval Date'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Date of joining'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Recruiter Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Candidate Name'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Candidate Mobile Number'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Job Opening'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Location'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Department'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Current Visa Status'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Current Visa Details'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Expiry date of current visa'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Candidate Vintage'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Visa Pipeline Status'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Backout Status'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('OfferLetter Status'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Visa Status'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Training Status'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Onboard status'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('proposed salary'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('Backout Reason'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('Evisa Date'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('Total Gross Salary'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Expected DOJ'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				//echo $sid;
				 $misData = DocumentCollectionDetails::where("id",$sid)->first();
				 $empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 if($empiddata!=''){
					$empid=$empiddata->emp_id; 
				 }else{
					$empid="NA";  
				 }
				 $backout=DocumentCollectionBackout::where("document_id",$sid)->where("status",1)->first();
				 if($backout!=''){
					 $backoutData=$backout->backout_reason;
				 }
				 else{
					$backoutData=''; 
				 }
				 if($misData->expected_date_joining!=''){
				 $expected_date_joining=date("d-M-Y",strtotime(str_replace("/","-",$misData->expected_date_joining)));
				 }
				 else{
					 $expected_date_joining='';
				 }
				 if($misData->evisa_start_date!=''){
				 $evisa=date("d-M-Y",strtotime(str_replace("/","-",$misData->evisa_start_date)));
				 }
				 else{
					 $evisa='';
				 }
				 $documentValuespdate = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",83)->first();
				 if($documentValuespdate!=''){
					 $dateofjoining=date("d-M-Y",strtotime(str_replace("/","-",$documentValuespdate->attribute_value)));
				 }
				 else{
					$dateofjoining=''; 
				 }
				 $proposedsalary=$misData->proposed_salary;
				 $cname=$misData->emp_name;
				 if(!empty($misData->created_at)){
				 $date=date("d-M-Y",strtotime(str_replace("/","-",$misData->created_at)));
				 }
				 else{
					 $date='';
				 }
				 $mobile=$misData->mobile_no;
				 $recruiter_name=$misData->recruiter_name;
				 $rec=RecruiterDetails::where("id",$recruiter_name)->first();
				 if($rec!=''){
				 $recruiter_name=$rec->name;
				 }else{
					 $recruiter_name='';
				 }
				 
				 $job=$misData->job_opening;
				 $jobOpning=JobOpening::where("id",$job)->first();
				 if($jobOpning!=''){
				 $jobname=$jobOpning->name;
				 $location=$jobOpning->location;
				 }else{
					$jobname=''; 
					$location='';
				 }
				 $department=$misData->department;
				 $current_visa_status=$misData->current_visa_status;
				 $current_visa_details=$misData->current_visa_details;
				 if($misData->visa_expiry_date!=''){
				 $Expirydate=date("d-M-Y",strtotime(str_replace("/","-",$misData->visa_expiry_date)));
				 }
				 else{
					 $Expirydate=''; 
				 }
				 if($misData->created_at != '')
				{
					$doj = $misData->created_at;
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
						 $ventage= $returnData;
				
				}
				else{
					$ventage="";
				}
				if($misData->ok_visa == 1){
						$pipline="NOT Generate";
				}else if($misData->ok_visa == 2){
						$pipline="Approved";
				}else if($misData->ok_visa == 3){
						$pipline="Requested";
				}else if($misData->ok_visa == 4){
						$pipline="DisApproved";
				}
				else{
					$pipline="";
				}
				if($misData->backout_status == 1){
						$backout="No";
					
					}else{
						$backout="Yes";
					}	
					
					if($misData->offer_letter_onboarding_status == 1){
					 $offerletter="incomplete";
					} else{
					$offerletter="complete";
				    }
					
					if($misData->visa_process_status == 4){
					 $visaprocess="complete";
					}
					else if($misData->visa_process_status == 2){
						$visaprocess="inprogress";
					}else{	
					 $visaprocess="incomplete";
					}
				 
				if($misData->training_process_status == 4){
					$training="complete";
				}else if($misData->training_process_status == 2){
						$training="inprogress";
				}else{
					$training="incomplete";
				}
				 if($misData->onboard_status == 2){
					$onboard="complete";
				 }else{
					$onboard="incomplete";
				 }
				 $molsalary = VisaDetails::where("document_collection_id",$sid)->where("attribute_code",132)->first();
				   if($molsalary !='')
				   { 
					$molsalary= "AED ".$molsalary->attribute_value;
				   }
				   else
				   {
						$molsalary='';
				   }
				 
				 
				 $indexCounter++; 	
				 $departmentMod = Department::where("id",$department)->first();
				 $deptname=$departmentMod->department_name;
				 $sheet->setCellValue('A'.$indexCounter, $empid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $date)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $dateofjoining)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($recruiter_name))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper($cname))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mobile)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $jobname)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $location)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $deptname)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $current_visa_status)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $current_visa_details)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $Expirydate)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $ventage)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $pipline)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $backout)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $offerletter)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $visaprocess)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $training)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $onboard)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, "AED ".$proposedsalary)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $backoutData)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $evisa)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $molsalary)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $expected_date_joining)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'X'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:X1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','X') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
		}
	 public function listingPageonboardingonboard(Request $request)
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
				
				
				if((!empty($request->session()->get('datefrom_candonboard_filter_inner_list')) && $request->session()->get('datefrom_candonboard_filter_inner_list') != 'All') && (!empty($request->session()->get('dateto_candonboard_filter_inner_list')) && $request->session()->get('dateto_candonboard_filter_inner_list') != 'All'))
				{
					$datefrom = $request->session()->get('datefrom_candonboard_filter_inner_list');
					$dateto = $request->session()->get('dateto_candonboard_filter_inner_list');
					$var='doj>= "'.$datefrom.'" And doj<= "'.$dateto.'"';
					//echo $var;//exit;
					$documentValues = Employee_details::whereRaw($var)->where("status",1)->get();
					if($documentValues!=''){
						$idsarray=array();
						foreach($documentValues as $_filterdate){
							$idsarray[]=$_filterdate->document_collection_id;
						}
					}
					
					$finaldata=implode(",", $idsarray);
					 if($whereraw == '')
					{
						$whereraw = 'id IN('.$finaldata.')';
					}
					else
					{
						$whereraw .= ' And id IN('.$finaldata.')';
					}
				}
				
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
				if(!empty($request->session()->get('visastage_candonboard_filter_inner_list')) && $request->session()->get('visastage_candonboard_filter_inner_list') != 'All')
				{
					$visastage = $request->session()->get('visastage_candonboard_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'current_visa_stage IN('.$visastage.')';
					}
					else
					{
						$whereraw .= ' And current_visa_stage IN('.$visastage.')';
					}
				}
				
				//echo $whereraw;
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('departmentId_candonboard_filter_inner_list')) && $request->session()->get('departmentId_candonboard_filter_inner_list') != 'All' && $request->session()->get('departmentId_candonboard_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_candonboard_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
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
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
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
				if(!empty($request->session()->get('desc_candAll_filter_inner_list')) && $request->session()->get('desc_candAll_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candAll_filter_inner_list');
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
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
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
					//echo "hello";exit;
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("onboard_date DESC")->where("onboard_status",2)->where("backout_status",1)->whereRaw($whereraw)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("onboard_date DESC")->where("onboard_status",2)->where("backout_status",1)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("onboard_status",2)->where("backout_status",1)->get()->count();
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::where("onboard_status",2)->where("backout_status",1)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingonboard'));
				
		$documentColectionId = DocumentCollectionDetails::whereIn("visa_process_status",array(2,4))->where("onboard_status",2)->where("backout_status",1)->get();
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
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		//return view("OnboardingAjax/listingPageonboardingOnboard",compact('visastagearray','CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   return view("OnboardingAjax/listingPageonboardingOnboard",compact('visastagearray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	   }
	   public function departmentListData(Request $request)
		{
			
			$department = $request->departmentId;
			$request->session()->put('departmentId_candAll_filter_inner_list',$department);
			 //return  redirect('listingPageonboarding');	
		}
		public function departmentListDataofferletter(Request $request)
		{
			
			$department = $request->departmentId;
			$request->session()->put('departmentId_canddepartmentListofferletter_filter_inner_list',$department);
			 //return  redirect('listingPageonboarding');	
		}
	public function listingofferletterpending(Request $request)
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
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
				
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('datefrom_candofferletter_filter_inner_list')) && $request->session()->get('datefrom_candofferletter_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candofferletter_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_candofferletter_filter_inner_list')) && $request->session()->get('dateto_candofferletter_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candofferletter_filter_inner_list');
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
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('filterpendingofferletter_filter_inner_list')) && $request->session()->get('filterpendingofferletter_filter_inner_list') != 'All' && $request->session()->get('filterpendingofferletter_filter_inner_list') != 'null')
				{
					$pendingId = $request->session()->get('filterpendingofferletter_filter_inner_list');
					 $pendingArray = explode(",",$pendingId);
			
					 if($whereraw == '')
					{
						
						$whereraw = 'offer_letter_relased_status IN('.$pendingId.')';	
						
					}
					else
					{
						
						$whereraw .= 'And offer_letter_relased_status IN('.$pendingId.')';	
						
						
					}
				}
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				if(!empty($request->session()->get('departmentId_canddepartmentListofferletter_filter_inner_list')) && $request->session()->get('departmentId_canddepartmentListofferletter_filter_inner_list') != 'All' && $request->session()->get('departmentId_canddepartmentListofferletter_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_canddepartmentListofferletter_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
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
				if(!empty($request->session()->get('desc_candAll_filter_inner_list')) && $request->session()->get('desc_candAll_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candAll_filter_inner_list');
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
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('filtercompleteofferletterbg_filter_inner_list')) && $request->session()->get('filtercompleteofferletterbg_filter_inner_list') != 'All')
				{
					$bgstatus = $request->session()->get('filtercompleteofferletterbg_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'bgverification_status IN('.$bgstatus.')';
					}
					else
					{
						$whereraw .= ' And bgverification_status IN('.$bgstatus.')';
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
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
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
					//echo "hello";exit;
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("created_at DESC")->whereRaw($whereraw)->where("offer_letter_onboarding_status",1)->where("backout_status",1)->where("onboard_status",1)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("created_at DESC")->where("offer_letter_onboarding_status",1)->where("backout_status",1)->where("onboard_status",1)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("offer_letter_onboarding_status",1)->where("backout_status",1)->where("onboard_status",1)->get()->count();
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::where("offer_letter_onboarding_status",1)->where("backout_status",1)->where("onboard_status",1)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingofferletterpending'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingofferletterpending",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter'));
	   }
// offer letter complete

public function departmentListDataofferlettercomplete(Request $request)
		{
			
			$department = $request->departmentId;
			$request->session()->put('departmentId_candofferlettercomplete_filter_inner_list',$department);
			 //return  redirect('listingPageonboarding');	
		}
	public function listingofferlettercomplete(Request $request)
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
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('filtervisa_documents_status_filter_inner_list')) && $request->session()->get('filtervisa_documents_status_filter_inner_list') != 'All')
				{
					$visa_documents_status = $request->session()->get('filtervisa_documents_status_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'visa_documents_status IN('.$visa_documents_status.')';
					}
					else
					{
						$whereraw .= ' And visa_documents_status IN('.$visa_documents_status.')';
					}
				}
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				if(!empty($request->session()->get('filtercompleteofferletterbg_filter_inner_list')) && $request->session()->get('filtercompleteofferletterbg_filter_inner_list') != 'All')
				{
					$bgstatus = $request->session()->get('filtercompleteofferletterbg_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'bgverification_status IN('.$bgstatus.')';
					}
					else
					{
						$whereraw .= ' And bgverification_status IN('.$bgstatus.')';
					}
				}
				if(!empty($request->session()->get('offerletter_cimplete_resign_filter_inner_list')) && $request->session()->get('offerletter_cimplete_resign_filter_inner_list') != 'All')
				{
					$resign = $request->session()->get('offerletter_cimplete_resign_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'resign_status IN("'.$resign.'")';
					}
					else
					{
						$whereraw .= ' And resign_status IN("'.$resign.'")';
					}
				}
				
				
				if(!empty($request->session()->get('datefrom_offerlettercomplete_filter_inner_list')) && $request->session()->get('datefrom_offerlettercomplete_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_offerlettercomplete_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_offerlettercomplete_filter_inner_list')) && $request->session()->get('dateto_offerlettercomplete_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_offerlettercomplete_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('datefrom_offerlettercomplete_expecteddoj_filter_inner_list')) && $request->session()->get('datefrom_offerlettercomplete_expecteddoj_filter_inner_list') != 'All')
				{
					$datefromexpecteddoj = $request->session()->get('datefrom_offerlettercomplete_expecteddoj_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining>= "'.$datefromexpecteddoj.'"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining>= "'.$datefromexpecteddoj.'"';
					}
				}
				if(!empty($request->session()->get('dateto_offerlettercomplete_expecteddoj_filter_inner_list')) && $request->session()->get('dateto_offerlettercomplete_expecteddoj_filter_inner_list') != 'All')
				{
					$datetoexpecteddoj = $request->session()->get('dateto_offerlettercomplete_expecteddoj_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining<= "'.$datetoexpecteddoj.'"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining<= "'.$datetoexpecteddoj.'"';
					}
				}
				
				
				
				

				//echo $whereraw;
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('departmentId_candofferlettercomplete_filter_inner_list')) && $request->session()->get('departmentId_candofferlettercomplete_filter_inner_list') != 'All' && $request->session()->get('departmentId_candofferlettercomplete_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_candofferlettercomplete_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
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
				if(!empty($request->session()->get('desc_candAll_filter_inner_list')) && $request->session()->get('desc_candAll_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candAll_filter_inner_list');
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
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('current_visa_status_filter_inner_list_offerlettercomlete')) && $request->session()->get('current_visa_status_filter_inner_list_offerlettercomlete') != 'All')
				{
					$current_visa_statusarray = $request->session()->get('current_visa_status_filter_inner_list_offerlettercomlete');
					  $current_visa_status = explode(",",$current_visa_statusarray);
					 if($whereraw == '')
					{
						$whereraw = 'current_visa_status IN("'.$current_visa_statusarray.'")';
					}
					else
					{
						$whereraw .= ' And current_visa_status IN("'.$current_visa_statusarray.'")';
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
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
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
				//echo $whereraw;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("offer_letter_document_date DESC")->whereRaw($whereraw)->where("offer_letter_onboarding_status",2)->whereIn("visa_process_status",array(0,1))->whereIn("ok_visa",array(1,4))->where("backout_status",1)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("offer_letter_document_date DESC")->where("offer_letter_onboarding_status",2)->where("backout_status",1)->whereIn("visa_process_status",array(0,1))->whereIn("ok_visa",array(1,4))->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->whereIn("visa_process_status",array(0,1))->whereIn("ok_visa",array(1,4))->get()->count();
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::where("offer_letter_onboarding_status",2)->where("backout_status",1)->whereIn("visa_process_status",array(0,1))->whereIn("ok_visa",array(1,4))->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingofferlettercomplete'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingofferlettercomplete",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter'));
	   }
	   public function departmentListDataonboard(Request $request)
		{
			
			$department = $request->departmentId;
			$request->session()->put('departmentId_candonboard_filter_inner_list',$department);
			 //return  redirect('listingPageonboarding');	
		}
// backout data
	public function listingPageonboardingbackout(Request $request)
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
				
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('departmentId_candbackout_filter_inner_list')) && $request->session()->get('departmentId_candbackout_filter_inner_list') != 'All' && $request->session()->get('departmentId_candbackout_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_candbackout_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				//echo $whereraw; 
				if(!empty($request->session()->get('filter_backout_empid_inner_list')) && $request->session()->get('filter_backout_empid_inner_list') != 'All' && $request->session()->get('filter_backout_empid_inner_list') != '')
				{
					$gempid = $request->session()->get('filter_backout_empid_inner_list');
					
					 if($whereraw == '')
					{
						//echo "h1";exit;
						if($gempid==1){
						$whereraw = ' employee_id IS NOT NULL';
						}
						else{
						$whereraw = ' employee_id IS NULL';
						}
					}
					else
					{
						//echo "h2";exit;
						if($gempid==1){
						$whereraw .= ' And employee_id IS NOT NULL';
						}
						else{
						$whereraw .= ' And employee_id IS NULL';
						}
					}
				}
//echo $whereraw;
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
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
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
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
				if(!empty($request->session()->get('desc_candAll_filter_inner_list')) && $request->session()->get('desc_candAll_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candAll_filter_inner_list');
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
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
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
				//echo $whereraw;exit;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("backout_create_date DESC")->whereRaw($whereraw)->where("backout_status",2)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("backout_create_date DESC")->where("backout_status",2)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("backout_status",2)->get()->count();
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::where("backout_status",2)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingbackout'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingbackout",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter'));
	   }
	   public function departmentListDatabackout(Request $request)
		{
			
			$department = $request->departmentId;
			$request->session()->put('departmentId_candbackout_filter_inner_list',$department);
			 //return  redirect('listingPageonboarding');	
		}
	public function listingPageonboardingVisainprocess(Request $request)
	   {
		  
		    $whereraw = '';
/* 					$empId=$request->session()->get('EmployeeId');
					$department = $this->department_permissionInhouse($empId);
					if($department != 'All')
					{
						if($whereraw == '')
						{
							$whereraw = 'department IN("'.$department.'")';
						}
						else
						{
							$whereraw .= ' And department IN("'.$department.'")';
						}
					}
 */			$whereraw1 = '';
			$selectedFilter['CNAME'] = '';
			$selectedFilter['CEMAIL'] = '';
			$selectedFilter['DESC'] = '';
			$selectedFilter['DEPT'] = '';
			$selectedFilter['OPENING'] = '';
			$selectedFilter['STATUS'] = '';
			$selectedFilter['vintage'] = '';
			$selectedFilter['Company'] = '';
			$selectedFilter['Recruiter'] = '';
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
		
				


			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('visainprocessall_filter_inner_list')) && $request->session()->get('visainprocessall_filter_inner_list') != 'All')
				{
					$visastage = $request->session()->get('visainprocessall_filter_inner_list');
					//echo $visastage;
					
					 if($whereraw == '')
					{
						$whereraw = 'current_visa_stage IN('.$visastage.')';
					}
					else
					{
						$whereraw .= ' And current_visa_stage IN('.$visastage.')';
					}
				}
				//echo $whereraw;
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				
				if(!empty($request->session()->get('inprocessall_visastage_status_filter_inner_list')) && $request->session()->get('inprocessall_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('inprocessall_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($onBoardingStatusArray);
					 exit; */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
					}
				
					 
				}
				
				
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
					}
				}
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('departmentId_visainprocessall_filter_inner_list')) && $request->session()->get('departmentId_visainprocessall_filter_inner_list') != 'All' && $request->session()->get('departmentId_visainprocessall_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_visainprocessall_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('visainprocessall_bg_filter_inner_list')) && $request->session()->get('visainprocessall_bg_filter_inner_list') != 'All')
				{
					$bgstatus = $request->session()->get('visainprocessall_bg_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'bgverification_status IN('.$bgstatus.')';
					}
					else
					{
						$whereraw .= ' And bgverification_status IN('.$bgstatus.')';
					}
				}
				if(!empty($request->session()->get('visainprocessall_resign_filter_inner_list')) && $request->session()->get('visainprocessall_resign_filter_inner_list') != 'All')
				{
					$resign = $request->session()->get('visainprocessall_resign_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'resign_status IN("'.$resign.'")';
					}
					else
					{
						$whereraw .= ' And resign_status IN("'.$resign.'")';
					}
				}
				
				
				if(!empty($request->session()->get('datefrom_visainprocessall_moldate_filter_inner_list')) && $request->session()->get('datefrom_visainprocessall_moldate_filter_inner_list') != 'All')
				{
					$datefrommol = $request->session()->get('datefrom_visainprocessall_moldate_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'mol_date>= "'.$datefrommol.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And mol_date>= "'.$datefrommol.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessall_moldate_filter_inner_list')) && $request->session()->get('dateto_visainprocessall_moldate_filter_inner_list') != 'All')
				{
					$datetomol = $request->session()->get('dateto_visainprocessall_moldate_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'mol_date<= "'.$datetomol.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And mol_date<= "'.$datetomol.' 00:00:00"';
					}
				}
				
				if(!empty($request->session()->get('datefrom_visainprocessall_evisa_filter_inner_list')) && $request->session()->get('datefrom_visainprocessall_evisa_filter_inner_list') != 'All')
				{
					$datefromevisa = $request->session()->get('datefrom_visainprocessall_evisa_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'evisa_start_date>= "'.$datefromevisa.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And evisa_start_date>= "'.$datefromevisa.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessall_evisa_filter_inner_list')) && $request->session()->get('dateto_visainprocessall_evisa_filter_inner_list') != 'All')
				{
					$datetoevisa = $request->session()->get('dateto_visainprocessall_evisa_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'evisa_start_date<= "'.$datetoevisa.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And evisa_start_date<= "'.$datetoevisa.' 00:00:00"';
					}
				}
				
				
				if(!empty($request->session()->get('datefrom_visainprocessall_approval_filter_inner_list')) && $request->session()->get('datefrom_visainprocessall_approval_filter_inner_list') != 'All')
				{
					$datefromapproval = $request->session()->get('datefrom_visainprocessall_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefromapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefromapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessall_approval_filter_inner_list')) && $request->session()->get('dateto_visainprocessall_approval_filter_inner_list') != 'All')
				{
					$datetoapproval = $request->session()->get('dateto_visainprocessall_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$datetoapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$datetoapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('datefrom_visainprocessall_expected_filter_inner_list')) && $request->session()->get('datefrom_visainprocessall_expected_filter_inner_list') != 'All')
				{
					$datefromexpected = $request->session()->get('datefrom_visainprocessall_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessall_expected_filter_inner_list')) && $request->session()->get('dateto_visainprocessall_expected_filter_inner_list') != 'All')
				{
					$datetoexpected = $request->session()->get('dateto_visainprocessall_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
				}
				
				
				
				//echo $whereraw;
				if($whereraw != '')
				{
				
					if(!empty($request->session()->get('inprocessall_visastage_status_filter_inner_list')) && $request->session()->get('inprocessall_visastage_status_filter_inner_list') != 'All')
					{
					$onBoardingStatusArray1 = $request->session()->get('inprocessall_visastage_status_filter_inner_list');
					
					 $visastage_status1 = explode(",",$onBoardingStatusArray1);
					 
					if(in_array("visa_c",$visastage_status1))
					{
						
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_inprogress_date DESC")->whereRaw($whereraw)->whereiN("visa_process_status",array(2,4))->where("backout_status",1)->paginate($paginationValue);
					 }
					 else{
						
						$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_inprogress_date DESC")->whereRaw($whereraw)->where("visa_process_status",2)->where("backout_status",1)->paginate($paginationValue);
					 
					 }
					 
					}
					else{
						
						$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_inprogress_date DESC")->whereRaw($whereraw)->where("visa_process_status",2)->where("backout_status",1)->paginate($paginationValue);
					 
					 }
					
				}
				else
				{
					//echo "hello1";
					
					$documentCollectiondetails = DocumentCollectionDetails::where("visa_process_status",2)->where("backout_status",1)->orderByRaw("visa_inprogress_date DESC")->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					if(!empty($request->session()->get('inprocessall_visastage_status_filter_inner_list')) && $request->session()->get('inprocessall_visastage_status_filter_inner_list') != 'All')
					{
					$onBoardingStatusArray2 = $request->session()->get('inprocessall_visastage_status_filter_inner_list');
					
					 $visastage_status2 = explode(",",$onBoardingStatusArray2);
					 
					if(in_array("visa_c",$visastage_status2))
					{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->whereIn("visa_process_status",array(2,4))->where("backout_status",1)->get()->count();
					}
					else{
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("visa_process_status",2)->where("backout_status",1)->get()->count();
					}
					}
					else{
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("visa_process_status",2)->where("backout_status",1)->get()->count();
					}
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::where("visa_process_status",2)->where("backout_status",1)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingFirstTimevisainprocess'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingvisainprocess-All",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter'));
	   }
	public function listingPageonboardingVisacomplete(Request $request)
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
			if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				
				if(!empty($request->session()->get('datefrom_visainprocessallcomplete_moldate_filter_inner_list')) && $request->session()->get('datefrom_visainprocessallcomplete_moldate_filter_inner_list') != 'All')
				{
					$datefrommol = $request->session()->get('datefrom_visainprocessallcomplete_moldate_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'mol_date>= "'.$datefrommol.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And mol_date>= "'.$datefrommol.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessallcomplete_moldate_filter_inner_list')) && $request->session()->get('dateto_visainprocessallcomplete_moldate_filter_inner_list') != 'All')
				{
					$datetomol = $request->session()->get('dateto_visainprocessallcomplete_moldate_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'mol_date<= "'.$datetomol.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And mol_date<= "'.$datetomol.' 00:00:00"';
					}
				}
				
				if(!empty($request->session()->get('datefrom_visainprocessallcomplete_evisa_filter_inner_list')) && $request->session()->get('datefrom_visainprocessallcomplete_evisa_filter_inner_list') != 'All')
				{
					$datefromevisa = $request->session()->get('datefrom_visainprocessallcomplete_evisa_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'evisa_start_date>= "'.$datefromevisa.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And evisa_start_date>= "'.$datefromevisa.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessallcomplete_evisa_filter_inner_list')) && $request->session()->get('dateto_visainprocessallcomplete_evisa_filter_inner_list') != 'All')
				{
					$datetoevisa = $request->session()->get('dateto_visainprocessallcomplete_evisa_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'evisa_start_date<= "'.$datetoevisa.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And evisa_start_date<= "'.$datetoevisa.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				
				if(!empty($request->session()->get('departmentId_visainprocessallComplete_filter_inner_list')) && $request->session()->get('departmentId_visainprocessallComplete_filter_inner_list') != 'All' && $request->session()->get('departmentId_visainprocessallstage2_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_visainprocessallComplete_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				
				if(!empty($request->session()->get('visainprocessallComplete_bg_filter_inner_list')) && $request->session()->get('visainprocessallComplete_bg_filter_inner_list') != 'All')
				{
					$bgstatus = $request->session()->get('visainprocessallComplete_bg_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'bgverification_status IN('.$bgstatus.')';
					}
					else
					{
						$whereraw .= ' And bgverification_status IN('.$bgstatus.')';
					}
				}
				if(!empty($request->session()->get('visainprocessallComplete_resign_filter_inner_list')) && $request->session()->get('visainprocessallComplete_resign_filter_inner_list') != 'All')
				{
					$resign = $request->session()->get('visainprocessallComplete_resign_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'resign_status IN("'.$resign.'")';
					}
					else
					{
						$whereraw .= ' And resign_status IN("'.$resign.'")';
					}
				}
				if(!empty($request->session()->get('inprocessallComplete_visastage_status_filter_inner_list')) && $request->session()->get('inprocessallComplete_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('inprocessallComplete_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($onBoardingStatusArray);
					 exit; */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
					}
				
					 
				}
				if(!empty($request->session()->get('datefrom_visainprocessallComplete_approval_filter_inner_list')) && $request->session()->get('datefrom_visainprocessallComplete_approval_filter_inner_list') != 'All')
				{
					$datefromapproval = $request->session()->get('datefrom_visainprocessallComplete_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefromapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefromapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessallComplete_approval_filter_inner_list')) && $request->session()->get('dateto_visainprocessallComplete_approval_filter_inner_list') != 'All')
				{
					$datetoapproval = $request->session()->get('dateto_visainprocessallComplete_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$datetoapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$datetoapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('datefrom_visainprocessallComplete_expected_filter_inner_list')) && $request->session()->get('datefrom_visainprocessallComplete_expected_filter_inner_list') != 'All')
				{
					$datefromexpected = $request->session()->get('datefrom_visainprocessallComplete_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessallComplete_expected_filter_inner_list')) && $request->session()->get('dateto_visainprocessallComplete_expected_filter_inner_list') != 'All')
				{
					$datetoexpected = $request->session()->get('dateto_visainprocessallComplete_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('visainprocessallComplete_filter_inner_list')) && $request->session()->get('visainprocessallComplete_filter_inner_list') != 'All')
				{
					$visastage = $request->session()->get('visainprocessallComplete_filter_inner_list');
					
					 if($whereraw == '')
					{
						$whereraw = 'current_visa_stage IN('.$visastage.')';
					}
					else
					{
						$whereraw .= ' And current_visa_stage IN('.$visastage.')';
					}
				}
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
					}
				}
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				
				
				
				
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_complete_date DESC")->whereRaw($whereraw)->where("visa_process_status",4)->where("backout_status",1)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::where("visa_process_status",4)->where("backout_status",1)->orderByRaw("visa_complete_date DESC")->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("visa_process_status",4)->where("backout_status",1)->get()->count();
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::where("visa_process_status",4)->where("backout_status",1)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingVisacomplete'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingvisacomplete",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter'));
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
	public function listingPageonboardingVisaAll(Request $request)
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
		
		if(!empty($request->session()->get('visaprocessall_visastage_status_filter_inner_list')) && $request->session()->get('visaprocessall_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('visaprocessall_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($onBoardingStatusArray);
					 exit; */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
					}
				
					 
				}
		
		if(!empty($request->session()->get('datefrom_visainprocessvisaall_moldate_filter_inner_list')) && $request->session()->get('datefrom_visainprocessvisaall_moldate_filter_inner_list') != 'All')
				{
					$datefrommol = $request->session()->get('datefrom_visainprocessvisaall_moldate_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'mol_date>= "'.$datefrommol.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And mol_date>= "'.$datefrommol.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessvisaall_moldate_filter_inner_list')) && $request->session()->get('dateto_visainprocessvisaall_moldate_filter_inner_list') != 'All')
				{
					$datetomol = $request->session()->get('dateto_visainprocessvisaall_moldate_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'mol_date<= "'.$datetomol.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And mol_date<= "'.$datetomol.' 00:00:00"';
					}
				}
				
				if(!empty($request->session()->get('datefrom_visainprocessvisaall_evisa_filter_inner_list')) && $request->session()->get('datefrom_visainprocessvisaall_evisa_filter_inner_list') != 'All')
				{
					$datefromevisa = $request->session()->get('datefrom_visainprocessvisaall_evisa_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'evisa_start_date>= "'.$datefromevisa.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And evisa_start_date>= "'.$datefromevisa.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessvisaall_evisa_filter_inner_list')) && $request->session()->get('dateto_visainprocessvisaall_evisa_filter_inner_list') != 'All')
				{
					$datetoevisa = $request->session()->get('dateto_visainprocessvisaall_evisa_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'evisa_start_date<= "'.$datetoevisa.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And evisa_start_date<= "'.$datetoevisa.' 00:00:00"';
					}
				}
		
		//$request->session()->put('cname_empAll_filter_inner_list','');
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  //$whereraw .= 'department = "'.$departmentID.'"';
			  }
			if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 100;
				}
				if(!empty($request->session()->get('visaall_visaprocessstatus_filter_inner_list')) && $request->session()->get('visaall_visaprocessstatus_filter_inner_list') != 'All')
				{
					$visaprocessstatusArray = $request->session()->get('visaall_visaprocessstatus_filter_inner_list');
					 
					 $visaprocessstatus = explode(",",$visaprocessstatusArray);
					 if(in_array(1,$visaprocessstatus) && in_array(2,$visaprocessstatus))
						 {
						  if($whereraw == '')
							{
								$whereraw = '(visa_process_status IN (1,0,2,4) And ok_visa=2)';
							}
							else
							{
								$whereraw .= ' And (visa_process_status IN (1,0,2,4) And ok_visa=2)';
							}
						 }
						 if(in_array(1,$visaprocessstatus) && !in_array(2,$visaprocessstatus))
						 {
						  if($whereraw == '')
							{
								$whereraw = '(visa_process_status IN (1,0) And ok_visa=2)';
							}
							else
							{
								$whereraw .= ' And (visa_process_status IN (1,0) And ok_visa=2)';
							}
						 }
						 if(!in_array(1,$visaprocessstatus) && in_array(2,$visaprocessstatus))
						 {
						  if($whereraw == '')
							{
								$whereraw = '(visa_process_status IN (2,4) And ok_visa=2)';
							}
							else
							{
								$whereraw .= ' And (visa_process_status IN (2,4) And ok_visa=2)';
							}
						 }
				}	 
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('departmentId_visaall_filter_inner_list')) && $request->session()->get('departmentId_visaall_filter_inner_list') != 'All' && $request->session()->get('departmentId_visaall_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_visaall_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('visaall_documents_status_filter_inner_list')) && $request->session()->get('visaall_documents_status_filter_inner_list') != 'All')
				{
					$visa_documents_status = $request->session()->get('visaall_documents_status_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'visa_documents_status IN('.$visa_documents_status.')';
					}
					else
					{
						$whereraw .= ' And visa_documents_status IN('.$visa_documents_status.')';
					}
				}
				if(!empty($request->session()->get('visa_bg_filter_inner_list')) && $request->session()->get('visa_bg_filter_inner_list') != 'All')
				{
					$bgstatus = $request->session()->get('visa_bg_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'bgverification_status IN('.$bgstatus.')';
					}
					else
					{
						$whereraw .= ' And bgverification_status IN('.$bgstatus.')';
					}
				}
				if(!empty($request->session()->get('visaall_resign_filter_inner_list')) && $request->session()->get('visaall_resign_filter_inner_list') != 'All')
				{
					$resign = $request->session()->get('visaall_resign_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'resign_status IN("'.$resign.'")';
					}
					else
					{
						$whereraw .= ' And resign_status IN("'.$resign.'")';
					}
				}
				if(!empty($request->session()->get('visaall_visaapproved_filter_inner_list')) && $request->session()->get('visaall_visaapproved_filter_inner_list') != 'All')
				{
					$visaapproved = $request->session()->get('visaall_visaapproved_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'ok_visa IN("'.$visaapproved.'")';
					}
					else
					{
						$whereraw .= ' And ok_visa IN("'.$visaapproved.'")';
					}
				}
				if(!empty($request->session()->get('datefrom_visaall_approval_filter_inner_list')) && $request->session()->get('datefrom_visaall_approval_filter_inner_list') != 'All')
				{
					$datefromapproval = $request->session()->get('datefrom_visaall_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefromapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefromapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visaall_approval_filter_inner_list')) && $request->session()->get('dateto_visaall_approval_filter_inner_list') != 'All')
				{
					$datetoapproval = $request->session()->get('dateto_visaall_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$datetoapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$datetoapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('datefrom_visaall_expected_filter_inner_list')) && $request->session()->get('datefrom_visaall_expected_filter_inner_list') != 'All')
				{
					$datefromexpected = $request->session()->get('datefrom_visaall_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visaall_expected_filter_inner_list')) && $request->session()->get('dateto_visaall_expected_filter_inner_list') != 'All')
				{
					$datetoexpected = $request->session()->get('dateto_visaall_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
				}
				
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('current_visa_status_filter_inner_list_visaprocessvisaall')) && $request->session()->get('current_visa_status_filter_inner_list_visaprocessvisaall') != 'All')
				{
					$current_visa_status = $request->session()->get('current_visa_status_filter_inner_list_visaprocessvisaall');
					$current_visa_statusArray = explode(",",$current_visa_status);
					 
					 $current_visa_statusfinalarray=array();
					 foreach($current_visa_statusArray as $statusarray){
						 $current_visa_statusfinalarray[]="'".$statusarray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalvisastatus=implode(",", $current_visa_statusfinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'current_visa_status IN('.$finalvisastatus.')';
					}
					else
					{
						$whereraw .= ' And current_visa_status IN('.$finalvisastatus.')';
					}
				}
				if(!empty($request->session()->get('current_visa_type_value_filter_inner_list_visaprocessvisaall')) && $request->session()->get('current_visa_type_value_filter_inner_list_visaprocessvisaall') != 'All')
				{
					$visa_type_value = $request->session()->get('current_visa_type_value_filter_inner_list_visaprocessvisaall');
					$visa_type_valueArray = explode(",",$visa_type_value);
					 $totalvisavalue = array();
					 $rvalue = array(7,8,14,19,20,21);
					$lvalue = array(11,15,22,23,24,25);
					 
					 foreach($visa_type_valueArray as $detailsarray){
						 if($detailsarray==1){
						 $totalvisavalue = array_merge($totalvisavalue,$lvalue);
						 }
						 else{
							$totalvisavalue = array_merge($totalvisavalue,$rvalue); 
						 }
						 
						 
					 }
					 
					 //print_r($totalvisavalue);exit;
					 //$finalvisatypeval=implode(",", $totalvisavalue);
					 $visaProcessLists = Visaprocess::whereIn("visa_type",$totalvisavalue)->groupBy('visa_type')->get();
					// print_r($visaProcessLists);exit;
					 $docidByVisatype=array();
					 if($visaProcessLists!=''){
						 foreach($visaProcessLists as $_visatype){
							$docidByVisatype[]= $_visatype->document_id;
						 }
					 }
					 $finalvisatypedocid=implode(",", $docidByVisatype);
					 if($whereraw == '')
					{
						$whereraw = 'id IN('.$finalvisatypedocid.')';
					}
					else
					{
						$whereraw .= ' And id IN('.$finalvisatypedocid.')';
					}
				}
				
				if(!empty($request->session()->get('current_visa_details_filter_inner_list_visaprocessvisaall')) && $request->session()->get('current_visa_details_filter_inner_list_visaprocessvisaall') != 'All')
				{
					$current_visa_detail = $request->session()->get('current_visa_details_filter_inner_list_visaprocessvisaall');
					$current_visa_detailArray = explode(",",$current_visa_detail);
					 
					 $current_visa_detailarray=array();
					 foreach($current_visa_detailArray as $detailsarray){
						 $current_visa_detailarray[]="'".$detailsarray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalvisadetails=implode(",", $current_visa_detailarray);
					 if($whereraw == '')
					{
						$whereraw = 'current_visa_details IN('.$finalvisadetails.')';
					}
					else
					{
						$whereraw .= ' And current_visa_details IN('.$finalvisadetails.')';
					}
				}
				//echo $whereraw;
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
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
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
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
				if(!empty($request->session()->get('desc_candAll_filter_inner_list')) && $request->session()->get('desc_candAll_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candAll_filter_inner_list');
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
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
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
				//echo $whereraw;
				if($whereraw != '')
				{
					$whereraw .= 'AND (visa_process_status = 2 OR visa_process_status = 4 OR ok_visa =3 OR ok_visa =2)';
					//echo "hello";exit;
					$documentCollectiondetails = DocumentCollectionDetails::whereRaw($whereraw)->where("backout_status",1)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$whereraw = '(visa_process_status = 2 OR visa_process_status = 4 OR ok_visa =3 OR ok_visa =2)';
					$documentCollectiondetails = DocumentCollectionDetails::whereRaw($whereraw)->where("backout_status",1)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					$whereraw .= 'AND (visa_process_status = 2 OR visa_process_status = 4 OR ok_visa =3 OR ok_visa =2)';
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("backout_status",1)->get()->count();
				}
				else
				{
					$whereraw = '(visa_process_status = 2 OR visa_process_status = 4 OR ok_visa =3 OR ok_visa =2)';
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("backout_status",1)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingVisaAll'));
				
		//echo $reportsCount;exit;
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingvisaAll",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter'));
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
public function OfferLetterReleasedStatus(Request $request){
			$docid=$request->docId;
	
			$detailsObj = DocumentCollectionDetails::find($docid);
			$detailsObj->offer_letter_relased_status = 2; 
			$detailsObj->offer_letter_replease_date=date("Y-m-d");
			$detailsObj->offer_letter_relased_by=$request->session()->get('EmployeeId');
			if($detailsObj->save()){
				$finaljsondata = json_encode(array('offer_letter_relased_status' =>2), JSON_PRETTY_PRINT);
				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$docid;
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="offer letter relased status";
				$logObj->response =$finaljsondata;
				$logObj->category ="Offer Letter Released";
				$logObj->save();
			}
			echo "Data save";
		}
		public function ListDataofferletterPending(Request $request)
		{
			
			$pendingId = $request->pendingId;
			$request->session()->put('filterpendingofferletter_filter_inner_list',$pendingId);
			 //return  redirect('listingPageonboarding');	
		}
		public function updateBGVerification(Request $request){
			$docid=$request->docId;
			$detailsObj = DocumentCollectionDetails::find($docid);
			$detailsObj->bgverification_status = 4; 
			$detailsObj->bgverification_initiated_date=date("Y-m-d");
			
			if($detailsObj->save()){
				$finaljsondata = json_encode(array('bgverification_status' =>4), JSON_PRETTY_PRINT);
				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$docid;
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="bgverification status";
				$logObj->response =$finaljsondata;
				$logObj->category ="Offer Letter";
				$logObj->save();
			}
		}
	public function departmentListDatavisadocumentsstatus(Request $request)
		{
			
			$department = $request->departmentId;
			$request->session()->put('departmentId_candvisadocumentsstatus_filter_inner_list',$department);
			 //return  redirect('listingPageonboarding');	
		}
	public function listingFirstTimevisadocumentsstatus(Request $request)
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
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('datefrom_candvisadocumentsstatus_filter_inner_list')) && $request->session()->get('datefrom_candvisadocumentsstatus_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candvisadocumentsstatus_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_candvisadocumentsstatus_filter_inner_list')) && $request->session()->get('dateto_candvisadocumentsstatus_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candvisadocumentsstatus_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('departmentId_candvisadocumentsstatus_filter_inner_list')) && $request->session()->get('departmentId_candvisadocumentsstatus_filter_inner_list') != 'All' && $request->session()->get('departmentId_candvisadocumentsstatus_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_candvisadocumentsstatus_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
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
				if(!empty($request->session()->get('desc_candAll_filter_inner_list')) && $request->session()->get('desc_candAll_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candAll_filter_inner_list');
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
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				
				
				
				
				$CandidateRecruiterArray = array();
				if($whereraw == '')
				{
					$recruterArray = DocumentCollectionDetails::get();
					
					$recList = array(); 
					foreach($recruterArray as $_recruter)
					  {
					  $recList[]  = $_recruter->recruiter_name;
					  }
					  
				$recruter_details =  RecruiterDetails::whereIn('id',array_unique($recList))->get();
				}
				else
				{
					
					$recruterArray = DocumentCollectionDetails::whereRaw($whereraw)->get();
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
					$dept=Department::where("id",$_opening->department)->first();
					//echo $_f->first_name;exit;
					$OpeningArray[$_opening->id] = $_opening->name ." (".$dept->department_name." - ".$_opening->location.")";
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
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
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
				//echo $whereraw;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("upload_visa_document_date DESC")->whereRaw($whereraw)->where("status",6)->whereIn("visa_process_status",array(0,1))->whereIn("ok_visa",array(1,4))->where("backout_status",1)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("upload_visa_document_date DESC")->where("status",6)->where("backout_status",1)->whereIn("visa_process_status",array(0,1))->whereIn("ok_visa",array(1,4))->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("status",6)->where("backout_status",1)->whereIn("visa_process_status",array(0,1))->whereIn("ok_visa",array(1,4))->get()->count();
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::where("status",6)->where("backout_status",1)->whereIn("visa_process_status",array(0,1))->whereIn("ok_visa",array(1,4))->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingFirstTimevisadocumentsstatus'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingFirstTimevisadocumentsstatus",compact('CandidateRecruiterArray','companyvisaArray','VintageArray','departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter','CandidateNameArray','CandidateEmailArray','DesignationArray','OpeningArray','StatusArray','DepartmentArray'));
	   }
		public function documentcollectionbyfilterBYDateALL(Request $request)
		{
			
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$currentvisastatus='';
			if($request->input('currentVisa_status')!=''){
			 
			 $currentvisastatus=implode(",", $request->input('currentVisa_status'));
			}
			$dateto = $request->dateto;
			$datefrom = $request->datefrom;
			$request->session()->put('departmentId_candAll_filter_inner_list',$department);
			$request->session()->put('dateto_candAll_filter_inner_list',$dateto);
			$request->session()->put('datefrom_candAll_filter_inner_list',$datefrom);
			$request->session()->put('current_visa_status_filter_inner_list',$currentvisastatus);
			 //return  redirect('listingPageonboarding');	
		}
		public function resetListDataFilterDateALL(Request $request){
			$request->session()->put('departmentId_candAll_filter_inner_list','');
			$request->session()->put('dateto_candAll_filter_inner_list','');
			$request->session()->put('datefrom_candAll_filter_inner_list','');
			$request->session()->put('current_visa_status_filter_inner_list','');
		}
		public function documentcollectionbyfilterBYDateofferletter(Request $request)
		{
			//print_r($request->input());
			$pendingId='';
			if($request->input('pendingofferletter')!=''){
			 
			 $pendingId=implode(",", $request->input('pendingofferletter'));
			}
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$bgstatus='';
			if($request->input('bgcompleteofferletter')!=''){
			 
			 $bgstatus=implode(",", $request->input('bgcompleteofferletter'));
			}
			$dateto = $request->dateto;
			$datefrom = $request->datefrom;
			$request->session()->put('filterpendingofferletter_filter_inner_list',$pendingId);
			$request->session()->put('departmentId_canddepartmentListofferletter_filter_inner_list',$department);
			$request->session()->put('dateto_candofferletter_filter_inner_list',$dateto);
			$request->session()->put('datefrom_candofferletter_filter_inner_list',$datefrom);
			$request->session()->put('filtercompleteofferletterbg_filter_inner_list',$bgstatus);
			 //return  redirect('listingPageonboarding');	
		}
		public function resetListDataFilterDateofferletter(Request $request){
			$request->session()->put('filterpendingofferletter_filter_inner_list','');
			$request->session()->put('departmentId_canddepartmentListofferletter_filter_inner_list','');
			$request->session()->put('dateto_candofferletter_filter_inner_list','');
			$request->session()->put('datefrom_candofferletter_filter_inner_list','');
			$request->session()->put('filtercompleteofferletterbg_filter_inner_list','');
		}
		public function documentcollectionbyfilterBYDateofferletterComplete(Request $request)
		{
			
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$bgstatus='';
			if($request->input('bgcompleteofferletter')!=''){
			 
			 $bgstatus=implode(",", $request->input('bgcompleteofferletter'));
			}
			$visadocumentstatus='';
			if($request->input('visa_documents_status')!=''){
			 
			 $visadocumentstatus=implode(",", $request->input('visa_documents_status'));
			}
			$resignstatus='';
			if($request->input('offerlettercompleteresign')!=''){
			 
			 $resignstatus=implode(",", $request->input('offerlettercompleteresign'));
			}
			$currentvisa_status='';
			if($request->input('currentVisa_status')!=''){
			 
			 $currentvisa_status=implode(",", $request->input('currentVisa_status'));
			}
			//print_r($bgstatus);exit;
			$dateto = $request->dateto;
			$datefrom = $request->datefrom;
			
			$datetoexpecteddoj = $request->datetoexpecteddoj;
			$datefromexpecteddoj = $request->datefromexpecteddoj;
			
			$request->session()->put('filtervisa_documents_status_filter_inner_list',$visadocumentstatus);
			$request->session()->put('filtercompleteofferletterbg_filter_inner_list',$bgstatus);
			$request->session()->put('departmentId_candofferlettercomplete_filter_inner_list',$department);
			$request->session()->put('dateto_offerlettercomplete_filter_inner_list',$dateto);
			$request->session()->put('datefrom_offerlettercomplete_filter_inner_list',$datefrom);
			$request->session()->put('offerletter_cimplete_resign_filter_inner_list',$resignstatus);
			$request->session()->put('datefrom_offerlettercomplete_expecteddoj_filter_inner_list',$datefromexpecteddoj);
			$request->session()->put('dateto_offerlettercomplete_expecteddoj_filter_inner_list',$datetoexpecteddoj);
			$request->session()->put('current_visa_status_filter_inner_list_offerlettercomlete',$currentvisa_status);
			 //return  redirect('listingPageonboarding');	
		}
		public function resetListDataFilterDateofferletterComplete(Request $request){
			$request->session()->put('departmentId_candofferlettercomplete_filter_inner_list','');
			$request->session()->put('dateto_offerlettercomplete_filter_inner_list','');
			$request->session()->put('datefrom_offerlettercomplete_filter_inner_list','');
			$request->session()->put('filtervisa_documents_status_filter_inner_list','');
			$request->session()->put('filtercompleteofferletterbg_filter_inner_list','');
			$request->session()->put('offerletter_cimplete_resign_filter_inner_list','');
			$request->session()->put('datefrom_offerlettercomplete_expecteddoj_filter_inner_list','');
			$request->session()->put('dateto_offerlettercomplete_expecteddoj_filter_inner_list','');
			$request->session()->put('current_visa_status_filter_inner_list_offerlettercomlete','');
		}
	public function documentcollectionbyfilterBYDatevisadocumentsstatus(Request $request)
		{
			
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			
			$dateto = $request->dateto;
			$datefrom = $request->datefrom;
			
			$request->session()->put('departmentId_candvisadocumentsstatus_filter_inner_list',$department);
			$request->session()->put('dateto_candvisadocumentsstatus_filter_inner_list',$dateto);
			$request->session()->put('datefrom_candvisadocumentsstatus_filter_inner_list',$datefrom);
			 //return  redirect('listingPageonboarding');	
		}
		public function resetListDataFilterDatevisadocumentsstatus(Request $request){
			$request->session()->put('departmentId_candvisadocumentsstatus_filter_inner_list','');
			$request->session()->put('dateto_candvisadocumentsstatus_filter_inner_list','');
			$request->session()->put('datefrom_candvisadocumentsstatus_filter_inner_list','');
		}
	public static function VisaDocumentsStatus($current_visa_details,$id){
		$visadetails=$current_visa_details;
		if($visadetails!='' && $visadetails!=NULL && $visadetails=='Inside Country'){
		if($visadetails=="Tourist Visa"){
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$documentValuesExistingVisa = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",71)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL) && ($documentValuesExistingVisa!='' && $documentValuesExistingVisa!=NULL)){
				return "Document Received";
			}
			else{
				return "Document Not Received";
			}
		}
		else if($visadetails=="Residence Visa"){
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL)){
				return "Document Received";
			}
			else{
				return "Document Not Received";
			}
		}
		else if($visadetails=="Individual Sponsor"){
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$SponsorDocPassport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",72)->first();
			$SponsorDocVisa = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",73)->first();
			$SponsorDocEmirates = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",74)->first();
			$SponsorNOC = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",75)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL) && ($SponsorDocPassport!='' && $SponsorDocPassport!=NULL) && ($SponsorDocVisa!='' && $SponsorDocVisa!=NULL) && ($SponsorDocEmirates!='' && $SponsorDocEmirates!=NULL) && ($SponsorNOC!='' && $SponsorNOC!=NULL)){
				return "Document Received";
			}
			else{
				return "Document Not Received";
			}
		}
		else if($visadetails=="Residence Visa"){
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			$CompanyNOC = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",76)->first();
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL) && ($CompanyNOC!='' && $CompanyNOC!=NULL)){
				return "Document Received";
			}
			else{
				return "Document Not Received";
			}
		}
		else{
			return "Document Not Received";
		}
		}
		else{
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL)){
				return "Document Received";
			}
			else{
				return "Document Not Received";
			}
		}
	}
		
	public static function getExpectedDateJoiningData($id = NULL)
	   {
		   $documentValues = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",88)->first();
		   if($documentValues !='' && $documentValues !=NULL)
		   { 
			return $documentValues->attribute_value;
		   }
		   else
		   {
			    return "--";
		   }
	   }
	public function getEmpBackoutPopupData(Request $req)
	   {
		  
		$docId =  $req->docId;
		
		return view("OnboardingAjax/backoutpopup",compact('docId'));
	   }
	   public function getEMPexpecteddateJoiningData(Request $req)
	   {
		  
		$docId =  $req->docId;
		$Documentdata=DocumentCollectionDetails::where("id",$docId)->first();
		
		return view("OnboardingAjax/expecteddateofjoining",compact('docId','Documentdata'));
	   }
	   public function expecteddateUpdateData(Request $request){
			$docid=$request->input('docId');
			$expecteddatejoining=$request->input('expecteddatejoining');
			//$expecteddateobj=new DocumentCollectionBackout();		
			$expecteddateobj = DocumentCollectionDetails::find($docid);
			$expecteddateobj->expected_date_joining = $expecteddatejoining; 
			$expecteddateobj->save();
	   }
	public function getShowSpecialcommentdatahome(Request $request){
		
			$id = $request->docId;
			  $visadata = Visaprocess::where("document_id",$id)->orderBy('id','ASC')->get();
			  $Documentdata=DocumentCollectionDetails::where("id",$id)->first();
			  
			  if($Documentdata!=''){
				  $interviewData = InterviewDetailsProcess::where("interview_id",$Documentdata->interview_id)->orderBy("id","DESC")->get();
			  }
			  else{
				  $interviewData = "";
			  }
			  
			  $special_comment=SpecialCommentLog::where("document_id",$id)->orderBy("created_at","DESC")->get();
				$DocumentCollectionDetails=DocumentCollectionDetailsLog::where("document_id",$id)->orderBy("id","DESC")->get();
				if($DocumentCollectionDetails!=''){
					$DocumentCollectionDetails=$DocumentCollectionDetails;
				}
				else{
					$DocumentCollectionDetails='';
				}
				return view("OnboardingAjax/ShowSpecialcommentdata",compact('visadata','special_comment','DocumentCollectionDetails','interviewData','Documentdata')); 
		  
	}
	public function getremovebackoutStatus(Request $request){
			
			$docid=$request->docId;
			$backoutdata=DocumentCollectionBackout::where("document_id",$docid)->where("status",1)->first();
						
			if($backoutdata!=''){
				$id=$backoutdata->id;
			
			$backoutobj=DocumentCollectionBackout::find($id);
			$backoutobj->status=2;
			$backoutobj->save();
			$detailsObj = DocumentCollectionDetails::find($docid);
			$detailsObj->backout_status = 1; 
			$detailsObj->backout_createBy =$request->session()->get('EmployeeId');
			$detailsObj->backout_create_date = date("Y-m-d");
			if($detailsObj->save()){
				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$docid;
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="Update Backout Details";
				$logObj->response ="Remove Backout";
				$logObj->category ="Offer Letter";
				$logObj->save();
			}
			}
			echo "Backout update Successfully.";
			exit;
		}
		public function getEMPResignStatusDetails(Request $req)
	   {
		  
		$docId =  $req->docId;
		
		return view("OnboardingAjax/ResignStatuspopup",compact('docId'));
	   }
	   public function ResignStatusDocumentStartAjax(Request $request){
			
			
			$docid=$request->input('documentCollectionID');
			
			$resign_status=$request->input('resign_status');
			$date='';
			if($request->input('dateresign')!=''){
			$date=$request->input('dateresign');
			}
			else{
			$date=$request->input('expecteddateresign');
			}
				
			$expecteddateobj = DocumentCollectionDetails::find($docid);
			
			if($resign_status=="Yes" || $resign_status=="No" ){
				
			$expecteddateobj->resign_status = $resign_status; 
			$expecteddateobj->resign_date = $date; 
			$expecteddateobj->resign_created_date =date("Y-m-d"); 
			$expecteddateobj->resign_created_by = $request->session()->get('EmployeeId');
			$expecteddateobj->resign_lastworkingday = $request->input('lastworkingday');
			}
			else{
				
			$expecteddateobj->resign_status = $resign_status; 
			$expecteddateobj->resign_date = ''; 
			$expecteddateobj->resign_created_date =date("Y-m-d"); 
			$expecteddateobj->resign_created_by = $request->session()->get('EmployeeId');
			$expecteddateobj->resign_lastworkingday ='';
			}
			if($expecteddateobj->save()){
				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$docid;
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="Update Resign Details";
				$logObj->response ="Resign";
				$logObj->category ="Offer Letter";
				$logObj->save();
			}
			echo "Updated";
		}
		public function OnBoardCandidateKyc(Request $req)
	   {
		  
		$docId =  $req->docId;
		 $cList = WpCountries::get();
		 $Documentdata=DocumentCollectionDetails::where("id",$docId)->first();
		return view("OnboardingAjax/onboardDocumentCandidateKyc",compact('docId','cList','Documentdata'));
	   }
	   
	  public function OnBoardCandidateKycPost(Request $request)
	   {
		$selectedFilter = $request->input();
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		    
		$empattributesMod = OnboardCandidateKyc::where('docId',$documentCollectionId)->first();
												
			if(!empty($empattributesMod))
			{					
			$onboardKYCOBJ = OnboardCandidateKyc::find($empattributesMod->id);
			}
			else
			{
				$onboardKYCOBJ = new OnboardCandidateKyc();
			}
			
			$onboardKYCOBJ->docId =$documentCollectionId;
			$onboardKYCOBJ->onboard_local_address =$request->input('onboard_local_address');
			$onboardKYCOBJ->onboard_dob =$request->input('onboard_dob');
			$onboardKYCOBJ->onboard_contactno =$request->input('onboard_contactno');
			$onboardKYCOBJ->onboard_emergency_contactno=$request->input('onboard_emergency_contactno');
			$onboardKYCOBJ->country =$request->input('country');
			$onboardKYCOBJ->home_country_address =$request->input('onboard_home_country_address');
			$onboardKYCOBJ->home_country_contactno =$request->input('onboard_home_country_contactno');
			$onboardKYCOBJ->gender =$request->input('GNDR');
			$onboardKYCOBJ->email =$request->input('onboard_email');
			$onboardKYCOBJ->createdBY=$request->session()->get('EmployeeId');
			
			
			if($onboardKYCOBJ->save()){
				$DocOBJ=DocumentCollectionDetails::find($documentCollectionId);
				$DocOBJ->onboard_question_kyc_status=2;
				$DocOBJ->save();
				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$documentCollectionId;
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="Update employee Document kyc";
				$logObj->response ="employee Document kyc";
				$logObj->category ="Onboard Process";
				$logObj->save();
			}
			
			session()->flash('message', 'Thank you! We wish you all the best!'); 
			return redirect("/OnBoardFeedBack/$documentCollectionId");
				   
	   }
	   public function OnBoardFeedBack(Request $request){
		 $docId =  $request->docId;
		 
		 $attributeDetail = Question::where("attrbute_type",2)->where('status',1)->get();
		return view("OnboardingAjax/onboardFeedBack",compact('docId','attributeDetail'));  
	   }
	  public function OnBoardCandidateFeedBackPost(Request $rq)
		{
			
		   $documentCollectionId = $rq->input('documentCollectionID');
		
			$question_id=$rq->input('question_id');
			
			foreach($question_id as $_question){
				$ans=$rq->input($_question);
				
				if(!is_null($ans)){					
				if(count($ans)>0){
					
				foreach($ans as $_ans){
					$questinObj = new OnboardFeedBack();
					$questinObj->question_answer = $_ans;
					$questinObj->question_id = $_question;
					$questinObj->doc_id = $documentCollectionId;
					$questinObj->status = 1;
					$questinObj->save();
				}	
				}
				else{
					
				}
			}
			else{
				//session()->flash('message', 'All questions are compulsory'); 
				//return redirect("/OnBoardFeedBack/$documentCollectionId");
			}
			}
				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$documentCollectionId;
				$logObj->created_by=$rq->session()->get('EmployeeId');
				$logObj->title ="Feedback Question Submit";
				$logObj->response ="Feedback Question";
				$logObj->category ="Onboard Process";
				$logObj->save();
			session()->flash('message', 'Data Save Successfully '); 
			return redirect("/OnBoardCandidateKyc/$documentCollectionId");
			

		}
		public function getEMPResignStatusDetailsUpdate(Request $req)
	   {
		  
		$docId =  $req->docId;
		$Documentdata=DocumentCollectionDetails::where("id",$docId)->first();
		return view("OnboardingAjax/ResignStatuspopupUpdate",compact('docId','Documentdata'));
	   }
		
	public function ResignStatusDocumentUpdateStartAjax(Request $request){
		
		$docid=$request->input('documentCollectionID');
		
		$resign_status=$request->input('resign_status');
		$date='';
		if($request->input('dateresign')!=''){
		$date=$request->input('dateresign');
		}
		else{
		$date=$request->input('expecteddateresign');
		}
			
		$expecteddateobj = DocumentCollectionDetails::find($docid);
		if($resign_status=="Yes" || $resign_status=="No" ){
				
			$expecteddateobj->resign_status = $resign_status; 
			$expecteddateobj->resign_date = $date; 
			$expecteddateobj->resign_created_date =date("Y-m-d"); 
			$expecteddateobj->resign_created_by = $request->session()->get('EmployeeId');
			$expecteddateobj->resign_lastworkingday = $request->input('lastworkingday');
			}
			else{
				
			$expecteddateobj->resign_status = $resign_status; 
			$expecteddateobj->resign_date = ''; 
			$expecteddateobj->resign_created_date =date("Y-m-d"); 
			$expecteddateobj->resign_created_by = $request->session()->get('EmployeeId');
			$expecteddateobj->resign_lastworkingday ='';
			}
		if($expecteddateobj->save()){
			$logObj = new DocumentCollectionDetailsLog();
			$logObj->document_id =$docid;
			$logObj->created_by=$request->session()->get('EmployeeId');
			$logObj->title ="Update Resign Details";
			$logObj->response ="Update Resign";
			$logObj->category ="Offer Letter";
			$logObj->save();
		}
		echo "Updated";
	}
	
	  public function listingPageonboardinglistingNotification(Request $request)
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
				$r_id = 0;
				$empsessionIdGet=$request->session()->get('EmployeeId');
				$empDataGetting = Employee::where("id",$empsessionIdGet)->first();
				if($empDataGetting != '')
				{
				
					if($empDataGetting->group_id == 25)
					{
						$whereraw .= ' department = 9';
					}
				}
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
		//$request->session()->put('cname_empAll_filter_inner_list','');
 if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
			  {
				  $departmentID = $request->session()->get('onboarding_department_filter');
				  //$whereraw .= 'department = "'.$departmentID.'"';
			  }
			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
					if($paginationValue=="undefined"){
						//$paginationValue = 10;
					}
				}
				else
				{
					$paginationValue = 10;
				}
				//$request->session()->put('onboard_special_status_filter_inner_list','');
				if(!empty($request->session()->get('onboard_special_status_filter_inner_list')) && $request->session()->get('onboard_special_status_filter_inner_list') != 'All')
				{
					$specialStatusArray = $request->session()->get('onboard_special_status_filter_inner_list');
				/* echo "<pre>";
				print_r($specialStatusArray); */
				$visa_filter_status_30days = 0;
				$visa_filter_status_60days = 0;
				$evisa_status=0;
				//$evisa_status_date='';
				
					 $specialStatusAA = explode(",",$specialStatusArray);
					 if(in_array("2",$specialStatusAA))
					{
						$visa_filter_status_30days = 1;
					}
					if(in_array("6",$specialStatusAA))
					{
						$visa_filter_status_60days = 1;
					}
					if(in_array("8",$specialStatusAA))
					{
						$evisa_status = 1;
					}
					
					$specialStatusArray1 = array();
					foreach($specialStatusAA as $ss)
					{
						if($ss != 2 && $ss !=6 && $ss !=8)
						{
						$specialStatusArray1[] = $ss;
						}
					} 
					$specialStatusArray12 = implode(",",$specialStatusArray1); 
					if($specialStatusArray12 == '')
					{
						$specialStatusArray12 = 12;
					}
					if($whereraw == '')
					{
						
						$whereraw = 'special_filter_status IN('.$specialStatusArray12.')';
						if($visa_filter_status_30days == 1)
						{
							$whereraw .= ' OR visa_filter_status_30days =2';
						}
						if($visa_filter_status_60days == 1)
						{
							$whereraw .= ' OR visa_filter_status_60days =2';
						}
						if($evisa_status == 1)
						{
							$whereraw .= " OR evisa_status =1 And onboard_status = 1 And evisa_start_date<'".date('Y-m-d', strtotime("-7 days"))."'" ;
						}
					}
					else
					{
						$whereraw .= ' And special_filter_status IN('.$specialStatusArray12.')';
						if($visa_filter_status_30days == 1)
						{
							$whereraw .= ' OR visa_filter_status_30days =2';
						}
						if($visa_filter_status_60days == 1)
						{
							$whereraw .= ' OR visa_filter_status_60days =2';
						}
						if($evisa_status == 1)
						{
							$whereraw .= " OR evisa_status =1 And onboard_status = 1 And evisa_start_date<'".date('Y-m-d', strtotime("-7 days"))."'" ;
						}
					}
				}
						
				 //echo $whereraw; 
				
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
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
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if(!empty($request->session()->get('departmentId_candAll_filter_inner_list')) && $request->session()->get('departmentId_candAll_filter_inner_list') != 'All' && $request->session()->get('departmentId_candAll_filter_inner_list') !=  'null')
				{
					$departmentids = $request->session()->get('departmentId_candAll_filter_inner_list');
					
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					/*$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}*/
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
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
				if(!empty($request->session()->get('desc_candAll_filter_inner_list')) && $request->session()->get('desc_candAll_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candAll_filter_inner_list');
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
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				/*
				*consultancy Code
				*/
				$r_id = 0;
				$empsessionIdGet=$request->session()->get('EmployeeId');
				$empDataGetting = Employee::where("id",$empsessionIdGet)->first();
				if($empDataGetting != '')
				{
				
					if($empDataGetting->group_id == 22)
					{
						if($empDataGetting->r_id != '' && $empDataGetting->r_id != NULL)
						{
						$r_id = $empDataGetting->r_id;
						$request->session()->put('company_RecruiterName_filter_inner_list',$r_id);
						}
						else
						{
							$request->session()->put('company_RecruiterName_filter_inner_list',"");
						}
					}
				}
				/*
				*consultancy Code
				*/
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('company_visastage_status_filter_inner_list')) && $request->session()->get('company_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('company_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($visastage_status);
					 exit;  */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
					}
				
					 //echo $whereraw;exit;
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
				
				
				
				
				
				
				//echo $whereraw;//exit;
				//echo $paginationValue;
				if($whereraw != '')
				{
					//echo "hello";exit;
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("-visa_expiry_date DESC")->whereRaw($whereraw)->where("backout_status",1)->paginate($paginationValue);
					//$departmentLists =  Department::where("status",1)->whereRaw($whereraw)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->whereRaw($whereraw)->orderBy('id','DESC')->get();
				}
				else
				{
					$whereraw = "special_filter_status IN (1,3,4,5,7) OR visa_filter_status_30days = 2 OR visa_filter_status_60days = 2 OR evisa_status = 1";
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::whereRaw($whereraw)->where("backout_status",1)->orderByRaw("-visa_expiry_date DESC")->paginate($paginationValue);
					
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("backout_status",1)->get()->count();
				}
				else
				{
					$whereraw = "special_filter_status IN (1,3,4,5,7) OR visa_filter_status_30days = 2 OR visa_filter_status_60days = 2";
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("backout_status",1)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardinglistingNotification'));
				
				
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingNotification",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter'));
	   }
	   public function documentcollectionbyfilterBYNotification(Request $request)
		{
			
			$status='';
			if($request->input('special_status')!=''){
			 
			 $status=implode(",", $request->input('special_status'));
			}
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$request->session()->put('departmentId_candAll_filter_inner_list',$department);
			$request->session()->put('onboard_special_status_filter_inner_list',$status);
			 //return  redirect('listingPageonboarding');	
		}
		public function resetListDataFilterNotification(Request $request){
			$request->session()->put('onboard_special_status_filter_inner_list','');
			$request->session()->put('departmentId_candAll_filter_inner_list','');
			 $request->session()->put('onboading_page_limit','');
		}
		public function ApprovalforSkipYes(Request $request){
			$docid=$request->docId;
			$detailsObj = DocumentCollectionDetails::find($docid);
			$detailsObj->approval_for_skip_status = 1; 
			$detailsObj->approval_for_skipBY= $request->session()->get('EmployeeId'); 
			$detailsObj->approval_for_skip_date = date("Y-m-d");
			if($detailsObj->save()){
			$logObj = new DocumentCollectionDetailsLog();
			$logObj->document_id =$docid;
			$logObj->created_by=$request->session()->get('EmployeeId');
			$logObj->title ="Update Approval for Skip Yes";
			$logObj->response ="Approval for Skip";
			$logObj->category ="Offer Letter";
			$logObj->save();
			}
		}
		public function ApprovalforSkipNo(Request $request){
			$docid=$request->docId;
			$detailsObj = DocumentCollectionDetails::find($docid);
			$detailsObj->approval_for_skip_status = 2; 
			$detailsObj->approval_for_skipBY= $request->session()->get('EmployeeId'); 
			$detailsObj->approval_for_skip_date = date("Y-m-d");
			if($detailsObj->save()){
				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$docid;
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="Update Approval for Skip NO";
				$logObj->response ="Approval for Skip";
				$logObj->category ="Offer Letter";
				$logObj->save();
			}
		}
		public function documentcollectionbyfilterBYDateOnboard(Request $request)
		{
			
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$visastage='';
			if($request->input('visastage')!=''){
			 
			 $visastage=implode(",", $request->input('visastage'));
			}
			$dateto = $request->dateto;
			$datefrom = $request->datefrom;
			$request->session()->put('departmentId_candonboard_filter_inner_list',$department);
			$request->session()->put('dateto_candonboard_filter_inner_list',$dateto);
			$request->session()->put('datefrom_candonboard_filter_inner_list',$datefrom);
			$request->session()->put('visastage_candonboard_filter_inner_list',$visastage);
			 //return  redirect('listingPageonboarding');	
		}
		public function resetListDataFilterDateOnboard(Request $request){
			$request->session()->put('departmentId_candonboard_filter_inner_list','');
			$request->session()->put('dateto_candonboard_filter_inner_list','');
			$request->session()->put('datefrom_candonboard_filter_inner_list','');
			$request->session()->put('visastage_candonboard_filter_inner_list','');
			
		}
		public function listingPageonboardingVisainprocessStage1(Request $request)
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
		
				


			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('visainprocessallstage1_filter_inner_list')) && $request->session()->get('visainprocessallstage1_filter_inner_list') != 'All')
				{
					$visastage = $request->session()->get('visainprocessallstage1_filter_inner_list');
					
					 if($whereraw == '')
					{
						$whereraw = 'current_visa_stage IN('.$visastage.')';
					}
					else
					{
						$whereraw .= ' And current_visa_stage IN('.$visastage.')';
					}
				}
				if(!empty($request->session()->get('datefrom_visainprocessallstage1_moldate_filter_inner_list')) && $request->session()->get('datefrom_visainprocessallstage1_moldate_filter_inner_list') != 'All')
				{
					$datefrommol = $request->session()->get('datefrom_visainprocessallstage1_moldate_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'mol_date>= "'.$datefrommol.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And mol_date>= "'.$datefrommol.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessallstage1_moldate_filter_inner_list')) && $request->session()->get('dateto_visainprocessallstage1_moldate_filter_inner_list') != 'All')
				{
					$datetomol = $request->session()->get('dateto_visainprocessallstage1_moldate_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'mol_date<= "'.$datetomol.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And mol_date<= "'.$datetomol.' 00:00:00"';
					}
				}
				
				if(!empty($request->session()->get('datefrom_visainprocessallstage1_evisa_filter_inner_list')) && $request->session()->get('datefrom_visainprocessallstage1_evisa_filter_inner_list') != 'All')
				{
					$datefromevisa = $request->session()->get('datefrom_visainprocessallstage1_evisa_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'evisa_start_date>= "'.$datefromevisa.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And evisa_start_date>= "'.$datefromevisa.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessallstage1_evisa_filter_inner_list')) && $request->session()->get('dateto_visainprocessallstage1_evisa_filter_inner_list') != 'All')
				{
					$datetoevisa = $request->session()->get('dateto_visainprocessallstage1_evisa_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'evisa_start_date<= "'.$datetoevisa.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And evisa_start_date<= "'.$datetoevisa.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				
				if(!empty($request->session()->get('inprocessallstage1_visastage_status_filter_inner_list')) && $request->session()->get('inprocessallstage1_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('inprocessallstage1_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($onBoardingStatusArray);
					 exit; */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
					}
				
					 
				}
				
				
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
					}
				}
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('departmentId_visainprocessallstage1_filter_inner_list')) && $request->session()->get('departmentId_visainprocessallstage1_filter_inner_list') != 'All' && $request->session()->get('departmentId_visainprocessallstage1_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_visainprocessallstage1_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('visainprocessallstage1_bg_filter_inner_list')) && $request->session()->get('visainprocessallstage1_bg_filter_inner_list') != 'All')
				{
					$bgstatus = $request->session()->get('visainprocessallstage1_bg_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'bgverification_status IN('.$bgstatus.')';
					}
					else
					{
						$whereraw .= ' And bgverification_status IN('.$bgstatus.')';
					}
				}
				
				if(!empty($request->session()->get('visainprocessallstage1_resign_filter_inner_list')) && $request->session()->get('visainprocessallstage1_resign_filter_inner_list') != 'All')
				{
					$resign = $request->session()->get('visainprocessallstage1_resign_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'resign_status IN("'.$resign.'")';
					}
					else
					{
						$whereraw .= ' And resign_status IN("'.$resign.'")';
					}
				}
				if(!empty($request->session()->get('datefrom_visainprocessallstage1_approval_filter_inner_list')) && $request->session()->get('datefrom_visainprocessallstage1_approval_filter_inner_list') != 'All')
				{
					$datefromapproval = $request->session()->get('datefrom_visainprocessallstage1_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefromapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefromapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessallstage1_approval_filter_inner_list')) && $request->session()->get('dateto_visainprocessallstage1_approval_filter_inner_list') != 'All')
				{
					$datetoapproval = $request->session()->get('dateto_visainprocessallstage1_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$datetoapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$datetoapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('datefrom_visainprocessallstage1_expected_filter_inner_list')) && $request->session()->get('datefrom_visainprocessallstage1_expected_filter_inner_list') != 'All')
				{
					$datefromexpected = $request->session()->get('datefrom_visainprocessallstage1_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessallstage1_expected_filter_inner_list')) && $request->session()->get('dateto_visainprocessallstage1_expected_filter_inner_list') != 'All')
				{
					$datetoexpected = $request->session()->get('dateto_visainprocessallstage1_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
				}
				
				
				//echo $whereraw ;
				if($whereraw != '')
				{
					if(!empty($request->session()->get('inprocessallstage1_visastage_status_filter_inner_list')) && $request->session()->get('inprocessallstage1_visastage_status_filter_inner_list') != 'All')
					{
					$onBoardingStatusArraystage1 = $request->session()->get('inprocessallstage1_visastage_status_filter_inner_list');
					 
					 $visastage_statusstage1 = explode(",",$onBoardingStatusArraystage1);
					//print_r($visastage_statusstage1);
					 if(in_array("visa_c",$visastage_statusstage1))
					{
					
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_inprogress_date DESC")->whereRaw($whereraw)->whereIn("visa_process_status",array(2,4))->where("visa_stage_steps","Stage1")->where("backout_status",1)->paginate($paginationValue);
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("visa_stage_steps","Stage1")->whereIn("visa_process_status",array(2,4))->where("backout_status",1)->get()->count();
					}
					else{
						$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_inprogress_date DESC")->whereRaw($whereraw)->where("visa_process_status",2)->where("visa_stage_steps","Stage1")->where("backout_status",1)->paginate($paginationValue);
						$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("visa_stage_steps","Stage1")->where("visa_process_status",2)->where("backout_status",1)->get()->count();
					}
					}
					else{
						$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_inprogress_date DESC")->whereRaw($whereraw)->where("visa_process_status",2)->where("visa_stage_steps","Stage1")->where("backout_status",1)->paginate($paginationValue);
						$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("visa_stage_steps","Stage1")->where("visa_process_status",2)->where("backout_status",1)->get()->count();
					}
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetails = DocumentCollectionDetails::where("visa_process_status",2)->where("visa_stage_steps","Stage1")->where("backout_status",1)->orderByRaw("visa_inprogress_date DESC")->paginate($paginationValue);
					$reportsCount = DocumentCollectionDetails::where("visa_process_status",2)->where("visa_stage_steps","Stage1")->where("backout_status",1)->get()->count();
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingVisainprocessStage1'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingvisainprocess-Stage1",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter'));
	   }
	public function listingPageonboardingVisainprocessStage2(Request $request)
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
		
				


			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				
				if(!empty($request->session()->get('datefrom_visainprocessallstage2_moldate_filter_inner_list')) && $request->session()->get('datefrom_visainprocessallstage2_moldate_filter_inner_list') != 'All')
				{
					$datefrommol = $request->session()->get('datefrom_visainprocessallstage2_moldate_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'mol_date>= "'.$datefrommol.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And mol_date>= "'.$datefrommol.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessallstage2_moldate_filter_inner_list')) && $request->session()->get('dateto_visainprocessallstage2_moldate_filter_inner_list') != 'All')
				{
					$datetomol = $request->session()->get('dateto_visainprocessallstage2_moldate_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'mol_date<= "'.$datetomol.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And mol_date<= "'.$datetomol.' 00:00:00"';
					}
				}
				
				if(!empty($request->session()->get('datefrom_visainprocessallstage2_evisa_filter_inner_list')) && $request->session()->get('datefrom_visainprocessallstage2_evisa_filter_inner_list') != 'All')
				{
					$datefromevisa = $request->session()->get('datefrom_visainprocessallstage2_evisa_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'evisa_start_date>= "'.$datefromevisa.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And evisa_start_date>= "'.$datefromevisa.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessallstage2_evisa_filter_inner_list')) && $request->session()->get('dateto_visainprocessallstage2_evisa_filter_inner_list') != 'All')
				{
					$datetoevisa = $request->session()->get('dateto_visainprocessallstage2_evisa_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'evisa_start_date<= "'.$datetoevisa.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And evisa_start_date<= "'.$datetoevisa.' 00:00:00"';
					}
				}
				
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				
				if(!empty($request->session()->get('inprocessallstage2_visastage_status_filter_inner_list')) && $request->session()->get('inprocessallstage2_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('inprocessallstage2_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($onBoardingStatusArray);
					 exit; */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
					}
				
					 
				}
				
				if(!empty($request->session()->get('visainprocessallstage2_filter_inner_list')) && $request->session()->get('visainprocessallstage2_filter_inner_list') != 'All')
				{
					$visastage = $request->session()->get('visainprocessallstage2_filter_inner_list');
					
					 if($whereraw == '')
					{
						$whereraw = 'current_visa_stage IN('.$visastage.')';
					}
					else
					{
						$whereraw .= ' And current_visa_stage IN('.$visastage.')';
					}
				}
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
					}
				}
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('departmentId_visainprocessallstage2_filter_inner_list')) && $request->session()->get('departmentId_visainprocessallstage2_filter_inner_list') != 'All' && $request->session()->get('departmentId_visainprocessallstage2_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_visainprocessallstage2_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('visainprocessallstage2_bg_filter_inner_list')) && $request->session()->get('visainprocessallstage2_bg_filter_inner_list') != 'All')
				{
					$bgstatus = $request->session()->get('visainprocessallstage2_bg_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'bgverification_status IN('.$bgstatus.')';
					}
					else
					{
						$whereraw .= ' And bgverification_status IN('.$bgstatus.')';
					}
				}
				
				if(!empty($request->session()->get('visainprocessallstage2_resign_filter_inner_list')) && $request->session()->get('visainprocessallstage2_resign_filter_inner_list') != 'All')
				{
					$resign = $request->session()->get('visainprocessallstage2_resign_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'resign_status IN("'.$resign.'")';
					}
					else
					{
						$whereraw .= ' And resign_status IN("'.$resign.'")';
					}
				}
				if(!empty($request->session()->get('datefrom_visainprocessallstage2_approval_filter_inner_list')) && $request->session()->get('datefrom_visainprocessallstage2_approval_filter_inner_list') != 'All')
				{
					$datefromapproval = $request->session()->get('datefrom_visainprocessallstage2_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefromapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefromapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessallstage2_approval_filter_inner_list')) && $request->session()->get('dateto_visainprocessallstage2_approval_filter_inner_list') != 'All')
				{
					$datetoapproval = $request->session()->get('dateto_visainprocessallstage2_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$datetoapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$datetoapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('datefrom_visainprocessallstage2_expected_filter_inner_list')) && $request->session()->get('datefrom_visainprocessallstage2_expected_filter_inner_list') != 'All')
				{
					$datefromexpected = $request->session()->get('datefrom_visainprocessallstage2_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessallstage2_expected_filter_inner_list')) && $request->session()->get('dateto_visainprocessallstage2_expected_filter_inner_list') != 'All')
				{
					$datetoexpected = $request->session()->get('dateto_visainprocessallstage2_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
				}
				
				
				
				//echo $whereraw;
				if($whereraw != '')
				{
				if(!empty($request->session()->get('inprocessallstage2_visastage_status_filter_inner_list')) && $request->session()->get('inprocessallstage2_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArraystage2 = $request->session()->get('inprocessallstage2_visastage_status_filter_inner_list');
					 
					 $visastage_statusstage2 = explode(",",$onBoardingStatusArraystage2);
					
					 if(in_array("visa_c",$visastage_status))
						 {
					 $documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_inprogress_date DESC")->whereRaw($whereraw)->where("visa_stage_steps","Stage2")->whereIn("visa_process_status",array(2,4))->where("backout_status",1)->paginate($paginationValue);
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->whereIn("visa_process_status",array(2,4))->where("visa_stage_steps","Stage2")->where("backout_status",1)->get()->count();						
				}
				else{
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_inprogress_date DESC")->whereRaw($whereraw)->where("visa_stage_steps","Stage2")->where("visa_process_status",2)->where("backout_status",1)->paginate($paginationValue);
					 $reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("visa_process_status",2)->where("visa_stage_steps","Stage2")->where("backout_status",1)->get()->count();
					 }
				}
				else{
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_inprogress_date DESC")->whereRaw($whereraw)->where("visa_stage_steps","Stage2")->where("visa_process_status",2)->where("backout_status",1)->paginate($paginationValue);
					 $reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->where("visa_process_status",2)->where("visa_stage_steps","Stage2")->where("backout_status",1)->get()->count();
						 }
				}
				else
				{
				
					$documentCollectiondetails = DocumentCollectionDetails::where("visa_process_status",2)->where("visa_stage_steps","Stage2")->where("backout_status",1)->orderByRaw("visa_inprogress_date DESC")->paginate($paginationValue);
					$reportsCount = DocumentCollectionDetails::where("visa_process_status",2)->where("visa_stage_steps","Stage2")->where("backout_status",1)->get()->count();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingVisainprocessStage2'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingvisainprocess-Stage2",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter'));
	   }
	  
	  public static function getStageType($id){

		   $documentdata = Visaprocess::where("document_id",$id)->orderBy('id','DESC')->first();
		   
		   if($documentdata !='' && $documentdata !=NULL)
		   { 
			 $datastage = VisaStage::where("id",$documentdata->visa_stage)->where("visa_type",$documentdata->visa_type)->orderBy('id','DESC')->first();
			 
			 if($datastage!=''){
				 if($datastage->stage_type=='Stage2'){
					return "Yes"; 
				 }
				 else{
					return "NO"; 
				 }
				 
				 
			 }
			 else{
				 return "NO";
			 }
		   }
		   else
		   {
			    return "NO";
		   }
	   }
	   public static function getStageType1($id){
		   $documentdata = Visaprocess::where("document_id",$id)->orderBy('id','DESC')->first();
		   
		   if($documentdata !='' && $documentdata !=NULL)
		   { 
			 $datastage = VisaStage::where("id",$documentdata->visa_stage)->where("visa_type",$documentdata->visa_type)->orderBy('id','DESC')->first();
			 
			 if($datastage!=''){
				 if($datastage->stage_type=='Stage1'){
					return "Yes"; 
				 }
				 else{
					return "NO"; 
				 }
				 
				 
			 }
			 else{
				 return "NO";
			 }
		   }
		   else
		   {
			    return "NO";
		   }
	   }
	public static function getVisaStageName($id)
	{	
	$data = Visaprocess::where("document_id",$id)->orderBy('id','DESC')->first();
	  
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
	}
	
	public static function getDateofJoining($id = NULL)
	   {
		   $documentValues = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",83)->first();
		   if($documentValues !='' && $documentValues !=NULL)
		   { 
			return $documentValues->attribute_value;
		   }
		   else
		   {
			    return "--";
		   }
	   }
	public function getVisaAllFilterData(Request $request){
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
		$visastagestatuslist=DocumentVisaStageStatus::get();
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$jobOpning=JobOpening::where("status",1)->get();
		
		return view("OnboardingAjax/VisaAllFilterpopup",compact('departmentLists','visastagestatuslist'));
	  
	}
public function documentcollectionfilterBYVisaALL(Request $request)
		{
			
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$bgstatus='';
			if($request->input('bgvisaall')!=''){
			 
			 $bgstatus=implode(",", $request->input('bgvisaall'));
			}
			$visadocumentstatus='';
			if($request->input('visa_documents_status')!=''){
			 
			 $visadocumentstatus=implode(",", $request->input('visa_documents_status'));
			}
			$resignstatus='';
			if($request->input('visallresign')!=''){
			 
			 $resignstatus=implode(",", $request->input('visallresign'));
			}
			$visaapproved='';
			if($request->input('visaapproved')!=''){
			 
			 $visaapproved=implode(",", $request->input('visaapproved'));
			}
			$visaprocessstatus='';
			if($request->input('visaprocessstatus')!=''){
			 
			 $visaprocessstatus=implode(",", $request->input('visaprocessstatus'));
			}
			$visaprocessall_status='';
			if($request->input('visaprocessall_status')!=''){
			 
			 $visaprocessall_status=implode(",", $request->input('visaprocessall_status'));
			}
			
			$currentVisa_status='';
			if($request->input('currentVisa_status')!=''){
			 
			 $currentVisa_status=implode(",", $request->input('currentVisa_status'));
			}
			$current_visa_details='';
			if($request->input('current_visa_details')!=''){
			 
			 $current_visa_details=implode(",", $request->input('current_visa_details'));
			}
			$visa_type_value='';
			if($request->input('currentVisa_value')!=''){
			 
			 $visa_type_value=implode(",", $request->input('currentVisa_value'));
			}
			
			//print_r($bgstatus);exit;
			$dateToVisaAllapproval = $request->datetoapproval;
			$dateFromVisaAllapproval = $request->datefromapproval;
			
			$datetoexpected = $request->datetoexpected;
			$datefromexpected = $request->datefromexpected;
			
			$datefrommolvisaall = $request->datefrommolvisaall;
			$datetomolvisaall = $request->datetomolvisaall;
			$datefromevisavisaall = $request->datefromevisavisaall;
			$datetoevisavisaall = $request->datetoevisavisaall;
			
			$request->session()->put('visaall_documents_status_filter_inner_list',$visadocumentstatus);
			$request->session()->put('visa_bg_filter_inner_list',$bgstatus);
			$request->session()->put('departmentId_visaall_filter_inner_list',$department);
			
			$request->session()->put('visaall_resign_filter_inner_list',$resignstatus);
			$request->session()->put('visaall_visaapproved_filter_inner_list',$visaapproved);
			$request->session()->put('dateto_visaall_approval_filter_inner_list',$dateToVisaAllapproval);
			$request->session()->put('datefrom_visaall_approval_filter_inner_list',$dateFromVisaAllapproval);
			$request->session()->put('datefrom_visaall_expected_filter_inner_list',$datefromexpected);
			$request->session()->put('dateto_visaall_expected_filter_inner_list',$datetoexpected);
			$request->session()->put('visaall_visaprocessstatus_filter_inner_list',$visaprocessstatus);
		    $request->session()->put('visaprocessall_visastage_status_filter_inner_list',$visaprocessall_status);
			
			
			$request->session()->put('datefrom_visainprocessvisaall_moldate_filter_inner_list',$datefrommolvisaall);
			$request->session()->put('dateto_visainprocessvisaall_moldate_filter_inner_list',$datetomolvisaall);
			$request->session()->put('datefrom_visainprocessvisaall_evisa_filter_inner_list',$datefromevisavisaall);
			$request->session()->put('dateto_visainprocessvisaall_evisa_filter_inner_list',$datetoevisavisaall);
			
			$request->session()->put('current_visa_status_filter_inner_list_visaprocessvisaall',$currentVisa_status);
			$request->session()->put('current_visa_details_filter_inner_list_visaprocessvisaall',$current_visa_details);
			$request->session()->put('current_visa_type_value_filter_inner_list_visaprocessvisaall',$visa_type_value);
			
			
			
			 //return  redirect('listingPageonboarding');	
		}
		public function resetdocumentcollectionfilterBYVisaALL(Request $request){
			$request->session()->put('visaall_documents_status_filter_inner_list','');
			$request->session()->put('visa_bg_filter_inner_list','');
			$request->session()->put('departmentId_visaall_filter_inner_list','');		
			$request->session()->put('visaall_resign_filter_inner_list','');
			$request->session()->put('visaall_visaapproved_filter_inner_list','');
			$request->session()->put('dateto_visaall_approval_filter_inner_list','');
			$request->session()->put('datefrom_visaall_approval_filter_inner_list','');
			$request->session()->put('datefrom_visaall_expected_filter_inner_list','');
			$request->session()->put('dateto_visaall_expected_filter_inner_list','');
			$request->session()->put('visaall_visaprocessstatus_filter_inner_list','');
			$request->session()->put('visaprocessall_visastage_status_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessvisaall_moldate_filter_inner_list','');
			$request->session()->put('dateto_visainprocessvisaall_moldate_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessvisaall_evisa_filter_inner_list','');
			$request->session()->put('dateto_visainprocessvisaall_evisa_filter_inner_list','');
			$request->session()->put('current_visa_status_filter_inner_list_visaprocessvisaall','');
			$request->session()->put('current_visa_details_filter_inner_list_visaprocessvisaall','');
			$request->session()->put('current_visa_type_value_filter_inner_list_visaprocessvisaall','');
			
			
			
		}
public function getVisaRequestFilterData(Request $request){
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
		$visastagestatuslist=DocumentVisaStageStatus::get();
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$jobOpning=JobOpening::where("status",1)->get();
		
		return view("OnboardingAjax/VisaRequestFilterpopup",compact('departmentLists','visastagestatuslist'));
	  
	}

public function documentcollectionfilterBYVisarequest(Request $request)
		{
			
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$bgstatus='';
			if($request->input('bgvisarequest')!=''){
			 
			 $bgstatus=implode(",", $request->input('bgvisarequest'));
			}
			$visadocumentstatus='';
			if($request->input('visarequest_documents_status')!=''){
			 
			 $visadocumentstatus=implode(",", $request->input('visarequest_documents_status'));
			}
			$resignstatus='';
			if($request->input('visarequestresign')!=''){
			 
			 $resignstatus=implode(",", $request->input('visarequestresign'));
			}
			$visaapproved='';
			if($request->input('visaapproved')!=''){
			 
			 $visaapproved=implode(",", $request->input('visaapproved'));
			}
			//print_r($bgstatus);exit;
			$dateToVisaAllapproval = $request->datetoapproval;
			$dateFromVisaAllapproval = $request->datefromapproval;
			
			$datetoexpected = $request->datetoexpected;
			$datefromexpected = $request->datefromexpected;
			
			$request->session()->put('departmentId_visarequest_filter_inner_list',$department);
			$request->session()->put('visarequest_bg_filter_inner_list',$bgstatus);		
			$request->session()->put('visarequest_documents_status_filter_inner_list',$visadocumentstatus);
			$request->session()->put('visarequest_resign_filter_inner_list',$resignstatus);
			$request->session()->put('dateto_visarequest_approval_filter_inner_list',$dateToVisaAllapproval);
			$request->session()->put('datefrom_visarequest_approval_filter_inner_list',$dateFromVisaAllapproval);
			$request->session()->put('datefrom_visarequest_expected_filter_inner_list',$datefromexpected);
			$request->session()->put('dateto_visarequest_expected_filter_inner_list',$datetoexpected);
			
			
			
			 //return  redirect('listingPageonboarding');	
		}
		public function resetdocumentcollectionfilterBYVisarequest(Request $request){
			$request->session()->put('departmentId_visarequest_filter_inner_list','');
			$request->session()->put('visarequest_bg_filter_inner_list','');		
			$request->session()->put('visarequest_documents_status_filter_inner_list','');
			$request->session()->put('visarequest_resign_filter_inner_list','');
			$request->session()->put('dateto_visarequest_approval_filter_inner_list','');
			$request->session()->put('datefrom_visarequest_approval_filter_inner_list','');
			$request->session()->put('datefrom_visarequest_expected_filter_inner_list','');
			$request->session()->put('dateto_visarequest_expected_filter_inner_list','');
		}
public function getVisaApproveFilterData(Request $request){
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
		$visastagestatuslist=DocumentVisaStageStatus::get();
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$jobOpning=JobOpening::where("status",1)->get();
		
		return view("OnboardingAjax/VisaApprovedFilterpopup",compact('departmentLists','visastagestatuslist'));
	  
	}

public function documentcollectionfilterBYVisaapprove(Request $request)
		{
			
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$bgstatus='';
			if($request->input('bgvisaapprove')!=''){
			 
			 $bgstatus=implode(",", $request->input('bgvisaapprove'));
			}
			$visadocumentstatus='';
			if($request->input('visarequest_documents_status')!=''){
			 
			 $visadocumentstatus=implode(",", $request->input('visarequest_documents_status'));
			}
			$resignstatus='';
			if($request->input('visaapproveresign')!=''){
			 
			 $resignstatus=implode(",", $request->input('visaapproveresign'));
			}
			$visaapproved='';
			if($request->input('visaapproved')!=''){
			 
			 $visaapproved=implode(",", $request->input('visaapproved'));
			}
			//print_r($bgstatus);exit;
			$dateToVisaAllapproval = $request->datetoapproval;
			$dateFromVisaAllapproval = $request->datefromapproval;
			
			$datetoexpected = $request->datetoexpected;
			$datefromexpected = $request->datefromexpected;
			
			$request->session()->put('departmentId_visaapprove_filter_inner_list',$department);
			$request->session()->put('visaapprove_bg_filter_inner_list',$bgstatus);		
			$request->session()->put('visaapprove_resign_filter_inner_list',$resignstatus);
			$request->session()->put('dateto_visaapprove_approval_filter_inner_list',$dateToVisaAllapproval);
			$request->session()->put('datefrom_visaapprove_approval_filter_inner_list',$dateFromVisaAllapproval);
			$request->session()->put('datefrom_visaapprove_expected_filter_inner_list',$datefromexpected);
			$request->session()->put('dateto_visaapprove_expected_filter_inner_list',$datetoexpected);
			
			
			
			 //return  redirect('listingPageonboarding');	
		}
		public function resetdocumentcollectionfilterBYVisaapprove(Request $request){
			$request->session()->put('departmentId_visaapprove_filter_inner_list','');
			$request->session()->put('visaapprove_bg_filter_inner_list','');		
			$request->session()->put('visaapprove_resign_filter_inner_list','');
			$request->session()->put('dateto_visaapprove_approval_filter_inner_list','');
			$request->session()->put('datefrom_visaapprove_approval_filter_inner_list','');
			$request->session()->put('datefrom_visaapprove_expected_filter_inner_list','');
			$request->session()->put('dateto_visaapprove_expected_filter_inner_list','');
		}
public function getVisaInProcessFilterData(Request $request){
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
		$visastagestatuslist=DocumentVisaStageStatus::get();
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$jobOpning=JobOpening::where("status",1)->get();
		$documentColectionId = DocumentCollectionDetails::whereIn("visa_process_status",array(2,4))->get();
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
		return view("OnboardingAjax/VisaInProcessFilterpopup",compact('visastagearray','departmentLists','visastagestatuslist'));
	  
	}
public function documentcollectionfilterBYvisainprocessall(Request $request)
		{
			
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$bgstatus='';
			if($request->input('bgvisainprocessall')!=''){
			 
			 $bgstatus=implode(",", $request->input('bgvisainprocessall'));
			}
			$visastage_status='';
			if($request->input('visastage_status')!=''){
			 
			 $visastage_status=implode(",", $request->input('visastage_status'));
			}
			$resignstatus='';
			if($request->input('visainprocessallresign')!=''){
			 
			 $resignstatus=implode(",", $request->input('visainprocessallresign'));
			}
			$visaapproved='';
			if($request->input('visaapproved')!=''){
			 
			 $visaapproved=implode(",", $request->input('visaapproved'));
			}
			$visastage='';
			if($request->input('visastage')!=''){
			 
			 $visastage=implode(",", $request->input('visastage'));
			}
			//print_r($bgstatus);exit;
			$dateToVisaAllapproval = $request->datetoapproval;
			$dateFromVisaAllapproval = $request->datefromapproval;
			
			$datetoexpected = $request->datetoexpected;
			$datefromexpected = $request->datefromexpected;
			
			$datetomol = $request->datetomol;
			$datefrommol = $request->datefrommol;
			$datetoevisa = $request->datetoevisa;
			$datefromevisa = $request->datefromevisa;
			
			
			$request->session()->put('departmentId_visainprocessall_filter_inner_list',$department);
			$request->session()->put('visainprocessall_bg_filter_inner_list',$bgstatus);		
			$request->session()->put('visainprocessall_resign_filter_inner_list',$resignstatus);
			$request->session()->put('inprocessall_visastage_status_filter_inner_list',$visastage_status);
			$request->session()->put('datefrom_visainprocessall_approval_filter_inner_list',$dateFromVisaAllapproval);
			$request->session()->put('dateto_visainprocessall_approval_filter_inner_list',$dateToVisaAllapproval);
			
			$request->session()->put('datefrom_visainprocessall_expected_filter_inner_list',$datefromexpected);
			$request->session()->put('dateto_visainprocessall_expected_filter_inner_list',$datetoexpected);
			$request->session()->put('visainprocessall_filter_inner_list',$visastage);
			
			$request->session()->put('dateto_visainprocessall_moldate_filter_inner_list',$datetomol);
			$request->session()->put('datefrom_visainprocessall_moldate_filter_inner_list',$datefrommol);
			$request->session()->put('dateto_visainprocessall_evisa_filter_inner_list',$datetoevisa);
			$request->session()->put('datefrom_visainprocessall_evisa_filter_inner_list',$datefromevisa);
			
			
			
			 //return  redirect('listingPageonboarding');	
		}
		public function resetdocumentcollectionfilterBYvisainprocess(Request $request){
			$request->session()->put('departmentId_visainprocessall_filter_inner_list','');
			$request->session()->put('visainprocessall_bg_filter_inner_list','');		
			$request->session()->put('visainprocessall_resign_filter_inner_list','');
			$request->session()->put('inprocessall_visastage_status_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessall_approval_filter_inner_list','');
			$request->session()->put('dateto_visainprocessall_approval_filter_inner_list','');
			
			$request->session()->put('datefrom_visainprocessall_expected_filter_inner_list','');
			$request->session()->put('dateto_visainprocessall_expected_filter_inner_list','');
			$request->session()->put('visainprocessall_filter_inner_list','');
			$request->session()->put('dateto_visainprocessall_moldate_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessall_moldate_filter_inner_list','');
			$request->session()->put('dateto_visainprocessall_evisa_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessall_evisa_filter_inner_list','');
		}	
	public function getVisaInProcessStage1FilterData(Request $request){
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
		$visastagestatuslist=DocumentVisaStageStatus::get();
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$jobOpning=JobOpening::where("status",1)->get();
		$documentColectionId = DocumentCollectionDetails::whereIn("visa_process_status",array(2,4))->where("visa_stage_steps","Stage1")->get();
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
		return view("OnboardingAjax/VisaInProcessStage1Filterpopup",compact('visastagearray','departmentLists','visastagestatuslist'));
	  
	}
	public function documentcollectionfilterBYvisainprocessallstage1(Request $request)
		{
			
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$bgstatus='';
			if($request->input('bgvisainprocessallstage1')!=''){
			 
			 $bgstatus=implode(",", $request->input('bgvisainprocessallstage1'));
			}
			$visastage_status='';
			if($request->input('visastage_status')!=''){
			 
			 $visastage_status=implode(",", $request->input('visastage_status'));
			}
			$resignstatus='';
			if($request->input('visainprocessallstage1resign')!=''){
			 
			 $resignstatus=implode(",", $request->input('visainprocessallstage1resign'));
			}
			$visaapproved='';
			if($request->input('visaapproved')!=''){
			 
			 $visaapproved=implode(",", $request->input('visaapproved'));
			}
			$visastage='';
			if($request->input('visastage')!=''){
			 
			 $visastage=implode(",", $request->input('visastage'));
			}
			//print_r($bgstatus);exit;
			$dateToVisaAllapproval = $request->datetoapproval;
			$dateFromVisaAllapproval = $request->datefromapproval;
			
			$datetoexpected = $request->datetoexpected;
			$datefromexpected = $request->datefromexpected;
			
			$datefrommolstage1 = $request->datefrommolstage1;
			$datetomolstage1 = $request->datetomolstage1;
			$datefromevisastage1 = $request->datefromevisastage1;
			$datetoevisastage1 = $request->datetoevisastage1;
			
			$request->session()->put('departmentId_visainprocessallstage1_filter_inner_list',$department);
			$request->session()->put('visainprocessallstage1_bg_filter_inner_list',$bgstatus);		
			$request->session()->put('visainprocessallstage1_resign_filter_inner_list',$resignstatus);
			$request->session()->put('inprocessallstage1_visastage_status_filter_inner_list',$visastage_status);
			$request->session()->put('datefrom_visainprocessallstage1_approval_filter_inner_list',$dateFromVisaAllapproval);
			$request->session()->put('dateto_visainprocessallstage1_approval_filter_inner_list',$dateToVisaAllapproval);
			
			$request->session()->put('datefrom_visainprocessallstage1_expected_filter_inner_list',$datefromexpected);
			$request->session()->put('dateto_visainprocessallstage1_expected_filter_inner_list',$datetoexpected);
			$request->session()->put('visainprocessallstage1_filter_inner_list',$visastage);
			
			$request->session()->put('datefrom_visainprocessallstage1_moldate_filter_inner_list',$datefrommolstage1);
			$request->session()->put('dateto_visainprocessallstage1_moldate_filter_inner_list',$datetomolstage1);
			$request->session()->put('datefrom_visainprocessallstage1_evisa_filter_inner_list',$datefromevisastage1);
			$request->session()->put('dateto_visainprocessallstage1_evisa_filter_inner_list',$datetoevisastage1);
			
			
			
			 //return  redirect('listingPageonboarding');	
		}
		public function resetdocumentcollectionfilterBYvisainprocessstage1(Request $request){
			$request->session()->put('departmentId_visainprocessallstage1_filter_inner_list','');
			$request->session()->put('visainprocessallstage1_bg_filter_inner_list','');		
			$request->session()->put('visainprocessallstage1_resign_filter_inner_list','');
			$request->session()->put('inprocessallstage1_visastage_status_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessallstage1_approval_filter_inner_list','');
			$request->session()->put('dateto_visainprocessallstage1_approval_filter_inner_list','');
			
			$request->session()->put('datefrom_visainprocessallstage1_expected_filter_inner_list','');
			$request->session()->put('dateto_visainprocessallstage1_expected_filter_inner_list','');
			$request->session()->put('visainprocessallstage1_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessallstage1_moldate_filter_inner_list','');
			$request->session()->put('dateto_visainprocessallstage1_moldate_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessallstage1_evisa_filter_inner_list','');
			$request->session()->put('dateto_visainprocessallstage1_evisa_filter_inner_list','');
		}
	public function getVisaInProcessStage2FilterData(Request $request){
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
		$visastagestatuslist=DocumentVisaStageStatus::get();
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$jobOpning=JobOpening::where("status",1)->get();
		
				
		$documentColectionId = DocumentCollectionDetails::whereIn("visa_process_status",array(2,4))->where("visa_stage_steps","Stage2")->get();
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
		return view("OnboardingAjax/VisaInProcessStage2Filterpopup",compact('visastagearray','departmentLists','visastagestatuslist'));
	  
	}
	
	public function documentcollectionfilterBYvisainprocessallstage2(Request $request)
		{
			
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$bgstatus='';
			if($request->input('bgvisainprocessallstage2')!=''){
			 
			 $bgstatus=implode(",", $request->input('bgvisainprocessallstage2'));
			}
			$visastage_status='';
			if($request->input('visastage_status')!=''){
			 
			 $visastage_status=implode(",", $request->input('visastage_status'));
			}
			$resignstatus='';
			if($request->input('visainprocessallstage2resign')!=''){
			 
			 $resignstatus=implode(",", $request->input('visainprocessallstage2resign'));
			}
			$visaapproved='';
			if($request->input('visaapproved')!=''){
			 
			 $visaapproved=implode(",", $request->input('visaapproved'));
			}
			$visastage='';
			if($request->input('visastage')!=''){
			 
			 $visastage=implode(",", $request->input('visastage'));
			}
			//print_r($bgstatus);exit;
			$dateToVisaAllapproval = $request->datetoapproval;
			$dateFromVisaAllapproval = $request->datefromapproval;
			
			$datetoexpected = $request->datetoexpected;
			$datefromexpected = $request->datefromexpected;
			
			$datefrommolstage2 = $request->datefrommolstage2;
			$datetomolstage2 = $request->datetomolstage2;
			$datefromevisastage2 = $request->datefromevisastage2;
			$datetoevisastage2 = $request->datetoevisastage2;
			$request->session()->put('departmentId_visainprocessallstage2_filter_inner_list',$department);
			$request->session()->put('visainprocessallstage2_bg_filter_inner_list',$bgstatus);		
			$request->session()->put('visainprocessallstage2_resign_filter_inner_list',$resignstatus);
			$request->session()->put('inprocessallstage2_visastage_status_filter_inner_list',$visastage_status);
			$request->session()->put('datefrom_visainprocessallstage2_approval_filter_inner_list',$dateFromVisaAllapproval);
			$request->session()->put('dateto_visainprocessallstage2_approval_filter_inner_list',$dateToVisaAllapproval);
			
			$request->session()->put('datefrom_visainprocessallstage2_expected_filter_inner_list',$datefromexpected);
			$request->session()->put('dateto_visainprocessallstage2_expected_filter_inner_list',$datetoexpected);
			$request->session()->put('visainprocessallstage2_filter_inner_list',$visastage);
			
			$request->session()->put('datefrom_visainprocessallstage2_moldate_filter_inner_list',$datefrommolstage2);
			$request->session()->put('dateto_visainprocessallstage2_moldate_filter_inner_list',$datetomolstage2);
			$request->session()->put('datefrom_visainprocessallstage2_evisa_filter_inner_list',$datefromevisastage2);
			$request->session()->put('dateto_visainprocessallstage2_evisa_filter_inner_list',$datetoevisastage2);
			
			
			
			
			 //return  redirect('listingPageonboarding');	
		}
		public function resetdocumentcollectionfilterBYvisainprocessstage2(Request $request){
			$request->session()->put('departmentId_visainprocessallstage2_filter_inner_list','');
			$request->session()->put('visainprocessallstage2_bg_filter_inner_list','');		
			$request->session()->put('visainprocessallstage2_resign_filter_inner_list','');
			$request->session()->put('inprocessallstage2_visastage_status_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessallstage2_approval_filter_inner_list','');
			$request->session()->put('dateto_visainprocessallstage2_approval_filter_inner_list','');
			
			$request->session()->put('datefrom_visainprocessallstage2_expected_filter_inner_list','');
			$request->session()->put('dateto_visainprocessallstage2_expected_filter_inner_list','');
			$request->session()->put('visainprocessallstage2_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessallstage1_moldate_filter_inner_list','');
			$request->session()->put('dateto_visainprocessallstage1_moldate_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessallstage1_evisa_filter_inner_list','');
			$request->session()->put('dateto_visainprocessallstage1_evisa_filter_inner_list','');
			
			
		}
		public static function getVisaStageNameDropdown($id){
			$visa_stage = VisaStage::where("id",$id)->first();
			  if($visa_stage!=''){
				$typeId=$visa_stage->visa_type;
				return $visatype=visaType::where("id",$typeId)->first()->title;			 
			  }
			  else{
				  return "";
			  }
		}
		
	public function	documentcollectionSavevisaexpdate(Request $request){
		$docId=$request->input('docId');
		$date=$request->input('visaexpdate');
		$detailsObj = DocumentCollectionDetails::find($docId);
		$detailsObj->visa_expiry_date = $date; 
		$detailsObj->save();
		echo "done";
	}
	
	public function documentcollectionSaveEmployeementStatus(Request $request){
		$selectedFilter = $request->input();
		   $current_employment_status = $selectedFilter['current_employment_status'];
		   $docId=$selectedFilter['docId'];
		   $documentCollectionMod = DocumentCollectionDetails::find($docId);
		   
		   $documentCollectionMod->current_employment_status=$current_employment_status;
		   
		   $documentCollectionMod->save();
		   
		   echo "done";
	}
	public function listingPageonboardingVisaincomplete(Request $request)
	   {
		  
		    $whereraw = '';
/* 					$empId=$request->session()->get('EmployeeId');
					$department = $this->department_permissionInhouse($empId);
					if($department != 'All')
					{
						if($whereraw == '')
						{
							$whereraw = 'department IN("'.$department.'")';
						}
						else
						{
							$whereraw .= ' And department IN("'.$department.'")';
						}
					}
 */			$whereraw1 = '';
			$selectedFilter['CNAME'] = '';
			$selectedFilter['CEMAIL'] = '';
			$selectedFilter['DESC'] = '';
			$selectedFilter['DEPT'] = '';
			$selectedFilter['OPENING'] = '';
			$selectedFilter['STATUS'] = '';
			$selectedFilter['vintage'] = '';
			$selectedFilter['Company'] = '';
			$selectedFilter['Recruiter'] = '';
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
		
				


			
			if(!empty($request->session()->get('onboading_page_limit')))
				{
					$paginationValue = $request->session()->get('onboading_page_limit');
				}
				else
				{
					$paginationValue = 10;
				}
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('visainprocessall_filter_inner_list')) && $request->session()->get('visainprocessall_filter_inner_list') != 'All')
				{
					$visastage = $request->session()->get('visainprocessall_filter_inner_list');
					//echo $visastage;
					
					 if($whereraw == '')
					{
						$whereraw = 'current_visa_stage IN('.$visastage.')';
					}
					else
					{
						$whereraw .= ' And current_visa_stage IN('.$visastage.')';
					}
				}
				//echo $whereraw;
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
					}
				}
				
				if(!empty($request->session()->get('inprocessall_visastage_status_filter_inner_list')) && $request->session()->get('inprocessall_visastage_status_filter_inner_list') != 'All')
				{
					$onBoardingStatusArray = $request->session()->get('inprocessall_visastage_status_filter_inner_list');
					 //$selectedFilter['Recruiter'] = $rec_id;
					 //echo $visastage_status;exit;
					 $visastage_status = explode(",",$onBoardingStatusArray);
					/*  echo '<pre>';
					 print_r($onBoardingStatusArray);
					 exit; */
							 if(in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								 {
								  if($whereraw == '')
									{
										$whereraw = '(offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
									else
									{
										$whereraw .= ' And (offer_letter_onboarding_status = 1 OR offer_letter_onboarding_status = 2)';
									}
								 }
								else if(in_array("offer_i",$visastage_status) && !in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 1';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 1';
											}
								}
								else if(!in_array("offer_i",$visastage_status) && in_array("offer_c",$visastage_status))
								{
									 if($whereraw == '')
											{
												$whereraw = 'offer_letter_onboarding_status = 2';
											}
											else
											{
												$whereraw .= ' And offer_letter_onboarding_status = 2';
											}
								}
									if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							 {
								  if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR visa_process_status = 2 OR visa_process_status = 4)';
									}
							 }
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
									if($whereraw == '')
											{
												$whereraw = '(visa_process_status = 2 OR visa_process_status = 4)';
											}
											else
											{
												$whereraw .= ' And (visa_process_status = 2 OR visa_process_status = 4)';
											}
									}

							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0) OR  visa_process_status = 4)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 2)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 2)';
									}
							}
							else if(!in_array("visa_i",$visastage_status) && in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status = 4)';
									}
									else
									{
										$whereraw .= ' And (visa_process_status = 4)';
									}
							}
							else if(in_array("visa_i",$visastage_status) && !in_array("visa_c",$visastage_status) && !in_array("visa_p",$visastage_status))
							{
							if($whereraw == '')
									{
										$whereraw = '(visa_process_status IN (1,0))';
									}
									else
									{
										$whereraw .= ' And (visa_process_status IN (1,0))';
									}
							}
						if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
						{
				  if($whereraw == '')
					{
						$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					else
					{
						$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4 OR training_process_status = 2)';
					}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status IN (1,0)';
								}
								else
								{
									$whereraw .= ' And training_process_status IN (1,0)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 4';
								}
								else
								{
									$whereraw .= ' And training_process_status = 4';
								}
					}
					else if(!in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'training_process_status = 2';
								}
								else
								{
									$whereraw .= ' And training_process_status = 2';
								}
					}
					else if(in_array("training_i",$visastage_status) && !in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 2)';
								}
					}
					else if(in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && !in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status IN (1,0) OR training_process_status = 4)';
								}
								else
								{
									$whereraw .= ' And (training_process_status IN (1,0) OR training_process_status = 4)';
								}
					}
					else if(!in_array("training_i",$visastage_status) && in_array("training_c",$visastage_status) && in_array("training_p",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = '(training_process_status = 4 OR training_process_status = 2)';
								}
								else
								{
									$whereraw .= ' And (training_process_status = 4 OR training_process_status = 2)';
								}
					}
					//onboard
					if(in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
						 {
							  if($whereraw == '')
								{
									$whereraw = '(onboard_status =1 OR onboard_status = 2)';
								}
								else
								{
									$whereraw .= ' And (onboard_status =1 OR onboard_status = 2)';
								}
						 }
					else if(in_array("onboard_i",$visastage_status) && !in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status =1';
								}
								else
								{
									$whereraw .= ' And onboard_status =1';
								}
					}
					else if(!in_array("onboard_i",$visastage_status) && in_array("onboard_c",$visastage_status))
					{
						 if($whereraw == '')
								{
									$whereraw = 'onboard_status = 2';
								}
								else
								{
									$whereraw .= ' And onboard_status = 2';
								}
					}
				
					 
				}
				
				
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
					}
				}
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('departmentId_visainprocessall_filter_inner_list')) && $request->session()->get('departmentId_visainprocessall_filter_inner_list') != 'All' && $request->session()->get('departmentId_visainprocessall_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_visainprocessall_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('visainprocessall_bg_filter_inner_list')) && $request->session()->get('visainprocessall_bg_filter_inner_list') != 'All')
				{
					$bgstatus = $request->session()->get('visainprocessall_bg_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'bgverification_status IN('.$bgstatus.')';
					}
					else
					{
						$whereraw .= ' And bgverification_status IN('.$bgstatus.')';
					}
				}
				if(!empty($request->session()->get('visainprocessall_resign_filter_inner_list')) && $request->session()->get('visainprocessall_resign_filter_inner_list') != 'All')
				{
					$resign = $request->session()->get('visainprocessall_resign_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'resign_status IN("'.$resign.'")';
					}
					else
					{
						$whereraw .= ' And resign_status IN("'.$resign.'")';
					}
				}
				if(!empty($request->session()->get('datefrom_visainprocessall_approval_filter_inner_list')) && $request->session()->get('datefrom_visainprocessall_approval_filter_inner_list') != 'All')
				{
					$datefromapproval = $request->session()->get('datefrom_visainprocessall_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefromapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefromapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessall_approval_filter_inner_list')) && $request->session()->get('dateto_visainprocessall_approval_filter_inner_list') != 'All')
				{
					$datetoapproval = $request->session()->get('dateto_visainprocessall_approval_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$datetoapproval.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$datetoapproval.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('datefrom_visainprocessall_expected_filter_inner_list')) && $request->session()->get('datefrom_visainprocessall_expected_filter_inner_list') != 'All')
				{
					$datefromexpected = $request->session()->get('datefrom_visainprocessall_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining>= "'.$datefromexpected.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_visainprocessall_expected_filter_inner_list')) && $request->session()->get('dateto_visainprocessall_expected_filter_inner_list') != 'All')
				{
					$datetoexpected = $request->session()->get('dateto_visainprocessall_expected_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And expected_date_joining<= "'.$datetoexpected.' 00:00:00"';
					}
				}
				
				
				
				//echo $whereraw;
				if($whereraw != '')
				{
				
					if(!empty($request->session()->get('inprocessall_visastage_status_filter_inner_list')) && $request->session()->get('inprocessall_visastage_status_filter_inner_list') != 'All')
					{
					$onBoardingStatusArray1 = $request->session()->get('inprocessall_visastage_status_filter_inner_list');
					
					 $visastage_status1 = explode(",",$onBoardingStatusArray1);
					 
					if(in_array("visa_c",$visastage_status1))
					{
						
					$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_inprogress_date DESC")->whereRaw($whereraw)->whereIn("visa_process_status",array(0,1))->where("ok_visa",2)->where("backout_status",1)->paginate($paginationValue);
					 }
					 else{
						
						$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_inprogress_date DESC")->whereRaw($whereraw)->whereIn("visa_process_status",array(0,1))->where("ok_visa",2)->where("backout_status",1)->paginate($paginationValue);
					 
					 }
					 
					}
					else{
						
						$documentCollectiondetails = DocumentCollectionDetails::orderByRaw("visa_inprogress_date DESC")->whereRaw($whereraw)->whereIn("visa_process_status",array(0,1))->where("ok_visa",2)->where("backout_status",1)->paginate($paginationValue);
					 
					 }
					
				}
				else
				{
					//echo "hello1";
					
					$documentCollectiondetails = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1))->where("ok_visa",2)->where("backout_status",1)->orderByRaw("visa_inprogress_date DESC")->paginate($paginationValue);
					
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				if($whereraw != '')
				{
					if(!empty($request->session()->get('inprocessall_visastage_status_filter_inner_list')) && $request->session()->get('inprocessall_visastage_status_filter_inner_list') != 'All')
					{
					$onBoardingStatusArray2 = $request->session()->get('inprocessall_visastage_status_filter_inner_list');
					
					 $visastage_status2 = explode(",",$onBoardingStatusArray2);
					 
					if(in_array("visa_c",$visastage_status2))
					{
					
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->whereIn("visa_process_status",array(0,1))->where("ok_visa",2)->where("backout_status",1)->get()->count();
					}
					else{
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->whereIn("visa_process_status",array(0,1))->where("ok_visa",2)->where("backout_status",1)->get()->count();
					}
					}
					else{
					$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->whereIn("visa_process_status",array(0,1))->where("ok_visa",2)->where("backout_status",1)->get()->count();
					}
				}
				else
				{
					$reportsCount = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1))->where("ok_visa",2)->where("backout_status",1)->get()->count();
				}
				$documentCollectiondetails->setPath(config('app.url/listingFirstTimevisainprocess'));
				
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("OnboardingAjax/listingPageonboardingvisapending",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','selectedFilter'));
	   }
	   public function getVisaincompleteFilterData(Request $request){
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
		$visastagestatuslist=DocumentVisaStageStatus::get();
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$jobOpning=JobOpening::where("status",1)->get();
		$documentColectionId = DocumentCollectionDetails::whereIn("visa_process_status",array(2,4))->get();
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
		return view("OnboardingAjax/VisaIncompleteFilterpopup",compact('visastagearray','departmentLists','visastagestatuslist'));
	  
	}
	public function getOfferletterFollowUpDate(Request $req)
	   {
		  
		$docId =  $req->docId;
		
		return view("OnboardingAjax/OfferletterFollowUppopup",compact('docId'));
	   }
	   public function docContentFrmOfferletterFollowUpPost(Request $request){
			
			
			$docid=$request->input('documentCollectionID');
			
			$date=$request->input('dateOfferLetterFollow');
			
			$comment=$request->input('comment');
			
				
			$expecteddateobj = DocumentCollectionDetails::find($docid);
			
			$expecteddateobj->offer_letter_followup_status =1; 
			$expecteddateobj->offer_letter_followup_date = $date; 
			$expecteddateobj->followup_comment = $comment; 
			$expecteddateobj->followup_createdBY = $request->session()->get('EmployeeId');
			if($expecteddateobj->save()){
				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$docid;
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="create offer letter follow up";
				$logObj->response ="offer letter Follow up";
				$logObj->category ="Offer Letter";
				$logObj->save();
			}
			echo "Updated";
		}
		public function listingPageonboardingDeadline(Request $request)
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
				
				if(!empty($request->session()->get('salesdept_emp_filter_inner_list')) && $request->session()->get('salesdept_emp_filter_inner_list') != 'All')
				{
					$salesdept = $request->session()->get('salesdept_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department = "'.$salesdept.'"';
					}
					else
					{
						$whereraw .= ' And department = "'.$salesdept.'"';
					}
				}
				if(!empty($request->session()->get('datefrom_candonboard_filter_inner_list')) && $request->session()->get('datefrom_candonboard_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candonboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'onboard_date>= "'.$datefrom.'"';
					}
					else
					{
						$whereraw .= ' And onboard_date>= "'.$datefrom.'"';
					}
				}
				if(!empty($request->session()->get('dateto_candonboard_filter_inner_list')) && $request->session()->get('dateto_candonboard_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candonboard_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'onboard_date<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And onboard_date<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('visastage_candonboard_filter_inner_list')) && $request->session()->get('visastage_candonboard_filter_inner_list') != 'All')
				{
					$visastage = $request->session()->get('visastage_candonboard_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'current_visa_stage IN('.$visastage.')';
					}
					else
					{
						$whereraw .= ' And current_visa_stage IN('.$visastage.')';
					}
				}
				//echo $whereraw;
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				if(!empty($request->session()->get('datefrom_candAll_filter_inner_list')) && $request->session()->get('datefrom_candAll_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_candAll_filter_inner_list')) && $request->session()->get('dateto_candAll_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_candAll_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('departmentId_candonboard_filter_inner_list')) && $request->session()->get('departmentId_candonboard_filter_inner_list') != 'All' && $request->session()->get('departmentId_candonboard_filter_inner_list') != 'null')
				{
					$departmentids = $request->session()->get('departmentId_candonboard_filter_inner_list');
					 $selectedFilter['department'] = $departmentids;
					 $departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
					
					 if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
				}
				else
				{
					$empId=$request->session()->get('EmployeeId');
					$departmentids = $this->department_permissionInhouse($empId);
					
					if($departmentids != 'All')
					{
						$departmentArray = explode(",",$departmentids);
							$department = '';
							foreach($departmentArray as $_department)
							{
								if($department == '')
								{
									$department = "'".trim($_department)."'";
								}
								else
								{
									$department = $department.",'".trim($_department)."'";
								}
							}
						if($whereraw == '')
						{
							$whereraw = 'department IN('.$department.')';
						}
						else
						{
							$whereraw .= ' And department IN('.$department.')';
						}
					}
				}
				if(!empty($request->session()->get('cname_emp_filter_inner_list')) && $request->session()->get('cname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('cname_emp_filter_inner_list');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
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
				if(!empty($request->session()->get('interview_approved_by_filter_inner_list')) && $request->session()->get('interview_approved_by_filter_inner_list') != 'All')
				{
					$interview_approved_by = $request->session()->get('interview_approved_by_filter_inner_list');
					//echo $rec_idarray;exit;
					//$rec_id=explode(',',$rec_idarray);
					//print_r($rec_id);exit;
			
					 if($whereraw == '')
					{
						$whereraw = 'interview_approved_by IN('.$interview_approved_by.')';
					}
					else
					{
						$whereraw .= ' And interview_approved_by IN ('.$interview_approved_by.')';
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
				if(!empty($request->session()->get('company_backout_filter_inner_list')) && $request->session()->get('company_backout_filter_inner_list') != 'All')
				{
					$backout = $request->session()->get('company_backout_filter_inner_list');
				
					 $selectedFilter['backout'] = $backout;
					 if($whereraw == '')
					{
						$whereraw = 'backout_status= "'.$backout.'"';
					}
					else
					{
						$whereraw .= ' And backout_status= "'.$backout.'"';
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
				if(!empty($request->session()->get('desc_candAll_filter_inner_list')) && $request->session()->get('desc_candAll_filter_inner_list') != 'All')
				{
					$desc = $request->session()->get('desc_candAll_filter_inner_list');
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
				if(!empty($request->session()->get('company_RecruiterNamecat_filter_inner_list')) && $request->session()->get('company_RecruiterNamecat_filter_inner_list') != 'All')
				{
					$rec_idcat = $request->session()->get('company_RecruiterNamecat_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter_cat IN('.$rec_idcat.')';
					}
					else
					{
						$whereraw .= ' And recruiter_cat IN('.$rec_idcat.')';
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
				if(!empty($request->session()->get('datefrom_deadline_filter_inner_list')) && $request->session()->get('datefrom_deadline_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_deadline_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'caption>= "'.$datefrom.'"';
					}
					else
					{
						$whereraw .= ' And caption>= "'.$datefrom.'"';
					}
				}
				if(!empty($request->session()->get('dateto_deadline_filter_inner_list')) && $request->session()->get('dateto_deadline_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_deadline_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'caption<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And caption<= "'.$dateto.'"';
					}
				}
				if(!empty($request->session()->get('datefrom_reminder_filter_inner_list')) && $request->session()->get('datefrom_reminder_filter_inner_list') != 'All')
				{
					$datefromdata = $request->session()->get('datefrom_reminder_filter_inner_list');
					$newdate = strtotime ( '-10 days' , strtotime ( $datefromdata ) ) ;
					$datefrom = date ( 'Y-m-d' , $newdate );
					 if($whereraw == '')
					{
						$whereraw = 'caption>= "'.$datefrom.'"';
					}
					else
					{
						$whereraw .= ' And caption>= "'.$datefrom.'"';
					}
				}
				if(!empty($request->session()->get('dateto_reminder_filter_inner_list')) && $request->session()->get('dateto_reminder_filter_inner_list') != 'All')
				{
					$datetodata = $request->session()->get('dateto_reminder_filter_inner_list');
					$newdate = strtotime ( '-10 days' , strtotime ( $datetodata ) ) ;
					$dateto = date ( 'Y-m-d' , $newdate );
					 if($whereraw == '')
					{
						$whereraw = 'caption<= "'.$dateto.'"';
					}
					else
					{
						$whereraw .= ' And caption<= "'.$dateto.'"';
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
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
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
					//echo "hello";exit;
					$documentCollectiondetaildata = DocumentCollectionDetails::orderBy("caption","DESC")->whereIn("visa_process_status",array(0,1,2))->where("backout_status",1)->whereRaw($whereraw)->get();
					//$reportsCount = DocumentCollectionDetails::whereRaw($whereraw)->whereIn("visa_process_status",array(0,1,2))->where("backout_status",1)->get()->count();
					
				}
				else
				{
					//echo "hello1";
					$documentCollectiondetaildata = DocumentCollectionDetails::orderBy("caption","DESC")->whereIn("visa_process_status",array(0,1,2))->where("backout_status",1)->get();
					//$reportsCount = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("backout_status",1)->get()->count();
				}
				
				$visastagearray=array(29,53,74,86,90,102,106,118,122,134,168);
				$finaldocidarray=array();
				if($documentCollectiondetaildata!=''){
				foreach($documentCollectiondetaildata as $_documentCollectiondetails){
					$visaProcess = Visaprocess::where("document_id",$_documentCollectiondetails->id)->whereIn("visa_stage",$visastagearray)->where("stage_staus",2)->first();
					if($visaProcess==''){
						$finaldocidarray[]=$_documentCollectiondetails->id;
					}

				}
				}
				//print_r(count($finaldocidarray));exit;
				$documentCollectiondetails= DocumentCollectionDetails::orderBy("caption","DESC")->whereIn("id",$finaldocidarray)->paginate($paginationValue);
				$reportsCount = DocumentCollectionDetails::whereIn("id",$finaldocidarray)->get()->count();
				$documentCollectiondetails->setPath(config('app.url/listingPageonboardingDeadline'));
		 
		return view("OnboardingAjax/listingPageonboardingDeadline",compact('documentCollectiondetails','reportsCount','filterList','paginationValue','selectedFilter'));
	   }
		
		public function getOfferletterFollowUpDateUpdate(Request $req)
	   {
		  
		$docId =  $req->docId;
		$docdata=DocumentCollectionDetails::where("id",$docId)->first();
		return view("OnboardingAjax/OfferletterFollowUppopupUpdate",compact('docId','docdata'));
	   }
	   public function docContentFrmOfferletterFollowUpUpdatePost(Request $request){
			
			
			$docid=$request->input('documentCollectionID');
			
			$date=$request->input('dateOfferLetterFollowupdate');
			
			$comment=$request->input('commentupdate');
			$status=$request->input('status_update');
				
			$expecteddateobj = DocumentCollectionDetails::find($docid);
			
			$expecteddateobj->offer_letter_followup_status =$status; 
			$expecteddateobj->offer_letter_followup_update_date = $date; 
			$expecteddateobj->followup_update_comment = $comment;
			if($expecteddateobj->save()){
				$logObj = new DocumentCollectionDetailsLog();
				$logObj->document_id =$docid;
				$logObj->created_by=$request->session()->get('EmployeeId');
				$logObj->title ="Update offer letter follow up";
				$logObj->response ="offer letter Follow up";
				$logObj->category ="Offer Letter";
				$logObj->save();
			}
			echo "Updated";
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
	public static function getrecdata($uid){
		$departmentDetails = JobFunctionPermission::where("user_id",$uid)->first();
		   if($departmentDetails != '')
		   {
			   $recuterdata=RecruiterDetails::where("employee_id",$departmentDetails->emp_id)->first();
					 if($recuterdata!=''){
					return $recuterdata->id;
						
					 }
					 else{
						return ''; 
					 }
			   
		   }
		   else
		   {
			   return 'All';
		   }
	}
	public static function getInterviwerName($interviewId){
		$data =InterviewDetailsProcess::where("interview_id",$interviewId)->where("interview_type","final discussion")->first();
			  
			  if($data != '')
			  {
				$recdata =RecruiterDetails::where("id",$data->interviewer_name)->first();
				if($recdata!=''){
				  return $recdata->name;
				  }
				  else
				  {
				  return '';
				  }
				}
	        
	}
public static function getGrossSalaryMOL($id){
		$documentValues = VisaDetails::where("document_collection_id",$id)->where("attribute_code",132)->first();
		   if($documentValues !='' && $documentValues !=NULL)
		   { 
			return "AED ".$documentValues->attribute_value;
		   }
		   else
		   {
			    return "--";
		   }
	        
	}

public function getVisaInProcessCompleteFilterData(Request $request){
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
		$visastagestatuslist=DocumentVisaStageStatus::get();
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$jobOpning=JobOpening::where("status",1)->get();
		
				
		$documentColectionId = DocumentCollectionDetails::whereIn("visa_process_status",array(2,4))->where("visa_stage_steps","Stage2")->get();
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
		return view("OnboardingAjax/VisaInProcessCompletFilterpopup",compact('visastagearray','departmentLists','visastagestatuslist'));
	  
	}
	
	public function documentcollectionfilterBYvisaComplete(Request $request)
		{
			
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$bgstatus='';
			if($request->input('bgvisainprocessallComplete')!=''){
			 
			 $bgstatus=implode(",", $request->input('bgvisainprocessallComplete'));
			}
			$visastage_status='';
			if($request->input('visastage_status')!=''){
			 
			 $visastage_status=implode(",", $request->input('visastage_status'));
			}
			$resignstatus='';
			if($request->input('visainprocessallCompleteresign')!=''){
			 
			 $resignstatus=implode(",", $request->input('visainprocessallCompleteresign'));
			}
			$visaapproved='';
			if($request->input('visaapproved')!=''){
			 
			 $visaapproved=implode(",", $request->input('visaapproved'));
			}
			$visastage='';
			if($request->input('visastage')!=''){
			 
			 $visastage=implode(",", $request->input('visastage'));
			}
			//print_r($bgstatus);exit;
			$dateToVisaAllapproval = $request->datetoapproval;
			$dateFromVisaAllapproval = $request->datefromapproval;
			
			$datetoexpected = $request->datetoexpected;
			$datefromexpected = $request->datefromexpected;
			
			$datefrommolcomplete = $request->datefrommolcomplete;
			$datetomolcomplete = $request->datetomolcomplete;
			$datefromevisacomplete = $request->datefromevisacomplete;
			$datetoevisacomplete = $request->datetoevisacomplete;
			
			$request->session()->put('departmentId_visainprocessallComplete_filter_inner_list',$department);
			$request->session()->put('visainprocessallComplete_bg_filter_inner_list',$bgstatus);		
			$request->session()->put('visainprocessallComplete_resign_filter_inner_list',$resignstatus);
			$request->session()->put('inprocessallComplete_visastage_status_filter_inner_list',$visastage_status);
			$request->session()->put('datefrom_visainprocessallComplete_approval_filter_inner_list',$dateFromVisaAllapproval);
			$request->session()->put('dateto_visainprocessallComplete_approval_filter_inner_list',$dateToVisaAllapproval);
			
			$request->session()->put('datefrom_visainprocessallComplete_expected_filter_inner_list',$datefromexpected);
			$request->session()->put('dateto_visainprocessallComplete_expected_filter_inner_list',$datetoexpected);
			$request->session()->put('visainprocessallComplete_filter_inner_list',$visastage);
			
			$request->session()->put('datefrom_visainprocessallcomplete_moldate_filter_inner_list',$datefrommolcomplete);
			$request->session()->put('dateto_visainprocessallcomplete_moldate_filter_inner_list',$datetomolcomplete);
			$request->session()->put('datefrom_visainprocessallcomplete_evisa_filter_inner_list',$datefromevisacomplete);
			$request->session()->put('dateto_visainprocessallcomplete_evisa_filter_inner_list',$datetoevisacomplete);
			
			
			 //return  redirect('listingPageonboarding');	
		}
		public function resetdocumentcollectionfilterBYvisaComplete(Request $request){
			$request->session()->put('departmentId_visainprocessallComplete_filter_inner_list','');
			$request->session()->put('visainprocessallComplete_bg_filter_inner_list','');		
			$request->session()->put('visainprocessallComplete_resign_filter_inner_list','');
			$request->session()->put('inprocessallComplete_visastage_status_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessallComplete_approval_filter_inner_list','');
			$request->session()->put('dateto_visainprocessallComplete_approval_filter_inner_list','');
			
			$request->session()->put('datefrom_visainprocessallComplete_expected_filter_inner_list','');
			$request->session()->put('dateto_visainprocessallComplete_expected_filter_inner_list','');
			$request->session()->put('visainprocessallComplete_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessallcomplete_moldate_filter_inner_list','');
			$request->session()->put('dateto_visainprocessallcomplete_moldate_filter_inner_list','');
			$request->session()->put('datefrom_visainprocessallcomplete_evisa_filter_inner_list','');
			$request->session()->put('dateto_visainprocessallcomplete_evisa_filter_inner_list','');
		}
		public static function getChangeStatus($docId){
		 $Documentdata = DocumentCollectionDetailsValues::where("document_collection_id",$docId)->where("attribute_code",91)->first(); 
		if($Documentdata != '')
		   {
			    return date("d M Y",strtotime($Documentdata->attribute_value)) ;
		   }
		   else
		   {
			    return "--";
		   }
		   
		}
		public static function getstampingStatus($docId){
		 $Documentdata = DocumentCollectionDetailsValues::where("document_collection_id",$docId)->where("attribute_code",92)->first(); 
		if($Documentdata != '')
		   {
			    return date("d M Y",strtotime($Documentdata->attribute_value)) ;
		   }
		   else
		   {
			    return "--";
		   }
		   
		}
	public static function getstampingStatusData($docId){
		 $Documentdata = DocumentCollectionDetailsValues::where("document_collection_id",$docId)->where("attribute_code",92)->first(); 
		if($Documentdata != '')
		   {
			    return date("d M Y",strtotime($Documentdata->attribute_value)) ;
		   }
		   else
		   {
			    return "--";
		   }
		   
		}
	public static function getDateofEntryData($docId,$outcountry){
		if($outcountry=="Outside Country"){
		 $Documentdata = DocumentCollectionDetailsValues::where("document_collection_id",$docId)->where("attribute_code",83)->first(); 
		if($Documentdata != '')
		   {
			   $date = $Documentdata->attribute_value;
				$newdate = strtotime ( '-3 days' , strtotime ( $date ) ) ;
				$newdate = date ( 'd M Y' , $newdate );
			    return $newdate ;
		   }
		   else
		   {
			    return "--";
		   }
		   
		}
		else{
			return "--";
		}
	}	
public static function getReminderData($docId){
		
		 $Documentdata = DocumentCollectionDetailsValues::where("document_collection_id",$docId)->where("attribute_code",92)->first(); 
		if($Documentdata != '')
		   {
			   $date = $Documentdata->attribute_value;
				$newdate = strtotime ( '-10 days' , strtotime ( $date ) ) ;
				$newdate = date ( 'd M Y' , $newdate );
			    return $newdate ;
		   }
		   else
		   {
			    return "--";
		   }
		   
		
	}		
	public function documentcollectionbyfilterBYDateOnboardDeadLine(Request $request)
		{
			
			$dateFromdeadline = $request->datefromdeadline;
			$dateTodeadline = $request->datetodeadline;
			
			$datefromreminder = $request->datefromreminder;
			$datetoreminder = $request->datetoreminder;
			
			
			$request->session()->put('datefrom_deadline_filter_inner_list',$dateFromdeadline);
			$request->session()->put('dateto_deadline_filter_inner_list',$dateTodeadline);		
			$request->session()->put('datefrom_reminder_filter_inner_list',$datefromreminder);
			$request->session()->put('dateto_reminder_filter_inner_list',$datetoreminder);
			
			
			 //return  redirect('listingPageonboarding');	
		}
		public function resetListDataFilterDateOnboardDeadLine(Request $request){
			$request->session()->put('datefrom_deadline_filter_inner_list','');
			$request->session()->put('dateto_deadline_filter_inner_list','');		
			$request->session()->put('datefrom_reminder_filter_inner_list','');
			$request->session()->put('dateto_reminder_filter_inner_list','');
		}
 public function exportDocReportDeadLine(Request $request){
			$parameters = $request->input(); 
	        $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Doc_report_deadline_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:X1');
			$sheet->setCellValue('A1', 'Doc List - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('EMP ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Final Discussion Approval Date'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Date of joining'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Recruiter Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Candidate Name'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Candidate Mobile Number'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Job Opening'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Location'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Department'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Current Visa Status'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Current Visa Details'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Expiry date of current visa'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Candidate Vintage'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Visa Pipeline Status'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Visa Status'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Training Status'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Onboard status'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('proposed salary'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Evisa Date'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('Total Gross Salary'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('Change Status'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('Stamping Deadline'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('Date of Entry'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Reminder'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				//echo $sid;
				 $misData = DocumentCollectionDetails::where("id",$sid)->first();
				 $empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 if($empiddata!=''){
					$empid=$empiddata->emp_id; 
				 }else{
					$empid="NA";  
				 }
				 $backout=DocumentCollectionBackout::where("document_id",$sid)->where("status",1)->first();
				 if($backout!=''){
					 $backoutData=$backout->backout_reason;
				 }
				 else{
					$backoutData=''; 
				 }
				 if($misData->evisa_start_date!=''){
				 $evisa=date("d-M-Y",strtotime(str_replace("/","-",$misData->evisa_start_date)));
				 }
				 else{
					 $evisa='';
				 }
				 $documentValuespdate = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",83)->first();
				 if($documentValuespdate!=''){
					 $dateofjoining=date("d-M-Y",strtotime(str_replace("/","-",$documentValuespdate->attribute_value)));
				 }
				 else{
					$dateofjoining=''; 
				 }
				 $proposedsalary=$misData->proposed_salary;
				 $cname=$misData->emp_name;
				 if(!empty($misData->created_at)){
				 $date=date("d-M-Y",strtotime(str_replace("/","-",$misData->created_at)));
				 }
				 else{
					 $date='';
				 }
				 $mobile=$misData->mobile_no;
				 $recruiter_name=$misData->recruiter_name;
				 $rec=RecruiterDetails::where("id",$recruiter_name)->first();
				 if($rec!=''){
				 $recruiter_name=$rec->name;
				 }else{
					 $recruiter_name='';
				 }
				 
				 $job=$misData->job_opening;
				 $jobOpning=JobOpening::where("id",$job)->first();
				 if($jobOpning!=''){
				 $jobname=$jobOpning->name;
				 $location=$jobOpning->location;
				 }else{
					$jobname=''; 
					$location='';
				 }
				 $department=$misData->department;
				 $current_visa_status=$misData->current_visa_status;
				 $current_visa_details=$misData->current_visa_details;
				 if($misData->visa_expiry_date!=''){
				 $Expirydate=date("d-M-Y",strtotime(str_replace("/","-",$misData->visa_expiry_date)));
				 }
				 else{
					 $Expirydate=''; 
				 }
				 if($misData->created_at != '')
				{
					$doj = $misData->created_at;
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
						 $ventage= $returnData;
				
				}
				else{
					$ventage="";
				}
				if($misData->ok_visa == 1){
						$pipline="NOT Generate";
				}else if($misData->ok_visa == 2){
						$pipline="Approved";
				}else if($misData->ok_visa == 3){
						$pipline="Requested";
				}else if($misData->ok_visa == 4){
						$pipline="DisApproved";
				}
				else{
					$pipline="";
				}
				if($misData->backout_status == 1){
						$backout="No";
					
					}else{
						$backout="Yes";
					}	
					
					if($misData->offer_letter_onboarding_status == 1){
					 $offerletter="incomplete";
					} else{
					$offerletter="complete";
				    }
					
					if($misData->visa_process_status == 4){
					 $visaprocess="complete";
					}
					else if($misData->visa_process_status == 2){
						$visaprocess="inprogress";
					}else{	
					 $visaprocess="incomplete";
					}
				 
				if($misData->training_process_status == 4){
					$training="complete";
				}else if($misData->training_process_status == 2){
						$training="inprogress";
				}else{
					$training="incomplete";
				}
				 if($misData->onboard_status == 2){
					$onboard="complete";
				 }else{
					$onboard="incomplete";
				 }
				 $molsalary = VisaDetails::where("document_collection_id",$sid)->where("attribute_code",132)->first();
				   if($molsalary !='')
				   { 
					$molsalary= "AED ".$molsalary->attribute_value;
				   }
				   else
				   {
						$molsalary='';
				   }
				 $changestatusData = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",91)->first(); 
				if($changestatusData != '')
				   {
						$changestatus= date("d M Y",strtotime($changestatusData->attribute_value)) ;
				   }
				   else
				   {
						$changestatus= "--";
				   }
				 $Stampingdeadlinedata = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",92)->first(); 
				if($Stampingdeadlinedata != '')
				   {
						$Stampingdeadline= date("d M Y",strtotime($Stampingdeadlinedata->attribute_value)) ;
				   }
				   else
				   {
						$Stampingdeadline= "--";
				   }
				   
				   if($misData->current_visa_status=="Outside Country"){
					$dateofentrydata = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",83)->first(); 
					if($dateofentrydata != '')
					   {
						   $date = $dateofentrydata->attribute_value;
							$newdate = strtotime ( '-3 days' , strtotime ( $date ) ) ;
							$newdate = date ( 'd M Y' , $newdate );
							$dateofentry=$newdate ;
					   }
					   else
					   {
							$dateofentry= "--";
					   }
					   
					}
					else{
						$dateofentry= "--";
					}
				 $ReminderDocumentdata = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",92)->first(); 
					if($ReminderDocumentdata != '')
					   {
						   $date = $ReminderDocumentdata->attribute_value;
							$newdate = strtotime ( '-10 days' , strtotime ( $date ) ) ;
							$newdate = date ( 'd M Y' , $newdate );
							$Reminder= $newdate ;
					   }
					   else
					   {
							$Reminder=  "--";
					   }
				 
				 
				 $indexCounter++; 	
				 $departmentMod = Department::where("id",$department)->first();
				 $deptname=$departmentMod->department_name;
				 $sheet->setCellValue('A'.$indexCounter, $empid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $date)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $dateofjoining)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($recruiter_name))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper($cname))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mobile)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $jobname)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $location)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $deptname)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $current_visa_status)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $current_visa_details)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $Expirydate)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $ventage)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $pipline)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $visaprocess)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $training)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $onboard)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, "AED ".$proposedsalary)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $evisa)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $molsalary)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $changestatus)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $Stampingdeadline)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $dateofentry)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $Reminder)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'W'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:W1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','W') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
		}	
public static function getOnBoardEMPId($docId){
	$departmentDetails = EmployeeOnboardLogdata::where("document_id",$docId)->first();
		   if($departmentDetails != '')
		   {
			   return $departmentDetails->emp_id;
		   }
		   else
		   {
			   return '--';
		   }
}
public function ListDataFilterBackout(Request $request)
		{
			
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$empid='';
			if($request->input('empid')!=''){
			 
			 $empid=$request->input('empid');
			}
			
			$request->session()->put('departmentId_candbackout_filter_inner_list',$department);
			$request->session()->put('filter_backout_empid_inner_list',$empid);		
			
			
			 //return  redirect('listingPageonboarding');	
		}
		public function resetListDataFilterBackout(Request $request){
			$request->session()->put('departmentId_candbackout_filter_inner_list','');
			$request->session()->put('filter_backout_empid_inner_list','');		
		}		
}
