<?php

namespace App\Http\Controllers\EmployeePerformanceReview;

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
use App\Models\WarningLetter\WarningLetterRequest;
use App\Models\WarningLetter\WarningLetterReasons;
use Illuminate\Support\Facades\DB;





class WarningLetterController extends Controller
{
    
       
	public function index(Request $request)
	{
		  $ReasonsForLeavingDetails = ReasonsForLeaving::where("status",1)->get();
		  $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		  $tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();
		  $empId = Employee_details::get();
		  $Designation=Designation::where("status",1)->get();

		  $warningData = WarningLetterRequest::groupBy('emp_id')->get();

		  $warningNameData = WarningLetterRequest::
						join('employee_details', 'warning_letter_requests.emp_id', '=', 'employee_details.emp_id')
						->groupBy('employee_details.emp_name')
						->orderBy('warning_letter_requests.id', 'desc')->get();

		$userid=$request->session()->get('EmployeeId');
		$userData = User::where("id",$userid)->orderBy('id', 'desc')->first();
		$empDetails = Employee_details::where("emp_id",$userData->employee_id)->where("job_function",3)->orderBy('id', 'desc')->first();
		$btnvisible=0;
		$tabvisible=0;
		if($empDetails)
		{
			$btnvisible=1;
		}
		if($userData->group_id==13)
		{
			$tabvisible=1;
		}

		//return $warningNameData;


		  return view("EmployeePerformanceReview/WarningIndex",compact('ReasonsForLeavingDetails','departmentLists','tL_details','empId','Designation','warningData','warningNameData','btnvisible','tabvisible'));
	}

	public function listingAllWarningEmployeeData(Request $request)
	  {
		  
		
		//return $request->all();
		
		
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
			//$filterList['emp_name'] = '';
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

			if(!empty($request->session()->get('warning_page_limit')))
			{
				$paginationValue = $request->session()->get('warning_page_limit');
			}
			else
			{
				$paginationValue = 100;
			}			   
			   
			//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();


			// if(empty($empty_array)) 			
			// {
			// 	$request->session()->put('warning_letter_emp_name','');

			// }



			

			// if(!empty($request->session()->get('warning_letter_emp_name')) && $request->session()->get('warning_letter_emp_name') != 'All')
			// {
			// 	$fname = $request->session()->get('warning_letter_emp_name');
			// 	 $cnameArray = explode(",",$fname);
				 
			// 	 $namefinalarray=array();
			// 	 foreach($cnameArray as $namearray){
			// 		 $namefinalarray[]="'".$namearray."'";					 
			// 	 }
			// 	 //print_r($namefinalarray);exit;
			// 	 $finalcname=implode(",", $namefinalarray);
			// 	 if($whereraw == '')
			// 	{
			// 		//$whereraw = 'emp_name like "%'.$fname.'%"';
			// 		$whereraw = 'employee_details.emp_name IN('.$finalcname.')';
			// 	}
			// 	else
			// 	{
			// 		$whereraw .= ' And employee_details.emp_name IN('.$finalcname.')';
			// 	}
			// }
			

			

			if(!empty($request->session()->get('warning_letter_emp_name')) && $request->session()->get('warning_letter_emp_name') != 'All')
			{
				$fname = $request->session()->get('warning_letter_emp_name');
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
					$whereraw = 'employee_details.emp_name IN('.$finalcname.')';
				}
				else
				{
					$whereraw .= ' And employee_details.emp_name IN('.$finalcname.')';
				}
			}

			//echo 'xxxxxxxxxx   sssss  '.$request->session()->get('warning_letter_emp_name');
			//exit;


			if(!empty($request->session()->get('warning_letter_fromdate')) && $request->session()->get('warning_letter_fromdate') != 'All')
			{
				$datefrom = $request->session()->get('warning_letter_fromdate');
				if($whereraw == '')
				{
					$whereraw = 'warning_letter_requests.created_at>= "'.$datefrom.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And warning_letter_requests.created_at>= "'.$datefrom.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('warning_letter_todate')) && $request->session()->get('warning_letter_todate') != 'All')
			{
				$dateto = $request->session()->get('warning_letter_todate');
				if($whereraw == '')
				{
					$whereraw = 'warning_letter_requests.created_at<= "'.$dateto.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And warning_letter_requests.created_at<= "'.$dateto.' 00:00:00"';
				}		
			}
			if(!empty($request->session()->get('warning_letter_department')) && $request->session()->get('warning_letter_department') != 'All')
			{
				$dept = $request->session()->get('warning_letter_department');
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
			if(!empty($request->session()->get('warning_letter_teamleader')) && $request->session()->get('warning_letter_teamleader') != 'All')
			{
				$teamlead = $request->session()->get('warning_letter_teamleader');
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
			if(!empty($request->session()->get('warning_letter_emp_id')) && $request->session()->get('warning_letter_emp_id') != 'All')
			{
				$empId = $request->session()->get('warning_letter_emp_id');
				if($whereraw == '')
				{
					$whereraw = 'warning_letter_requests.emp_id IN ('.$empId.')';
				}
				else
				{
					$whereraw .= ' And warning_letter_requests.emp_id IN ('.$empId.')';
				}
			}
			


			   
			   


			   if($whereraw != '')
			   {
				   //echo "hello";exit;

				   



				   $empsessionId=$request->session()->get('EmployeeId');
				   $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empDetails!='')
					   {
						$documentCollectiondetails = WarningLetterRequest::join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->whereRaw($whereraw)
						->select('employee_details.*', 'warning_letter_requests.*')
						->orderBy('warning_letter_requests.id', 'desc')
						//->toSql();	 
						//dd($documentCollectiondetails);						
						->paginate($paginationValue);		
						
						$reportsCount = WarningLetterRequest::join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->whereRaw($whereraw)
						->select('employee_details.*', 'warning_letter_requests.*')
						->orderBy('warning_letter_requests.id', 'desc')
						->get()->count();
					   }
				   }
				   else{
					$documentCollectiondetails = WarningLetterRequest::join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
					->whereRaw($whereraw)
					->select('employee_details.*', 'warning_letter_requests.*')
					->orderBy('warning_letter_requests.id', 'desc')
					//->toSql();	 
					//dd($documentCollectiondetails);						
					->paginate($paginationValue);		
					
					$reportsCount = WarningLetterRequest::join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
					->whereRaw($whereraw)
					->select('employee_details.*', 'warning_letter_requests.*')
					->orderBy('warning_letter_requests.id', 'desc')
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
						$documentCollectiondetails = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);

						
						$reportsCount = WarningLetterRequest::join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->orderBy('warning_letter_requests.id', 'desc')
						->get()->count();
					}
				}
				else{
					$documentCollectiondetails = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);

						
						$reportsCount = WarningLetterRequest::join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->orderBy('warning_letter_requests.id', 'desc')
						->get()->count();
				}

				
				
				
				
				
				
				
				
				
				// echo "hello1";exit;

				   
					





			   }
					$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
			   

					$userid=$request->session()->get('EmployeeId');
					$userData = User::where("id",$userid)->orderBy('id', 'desc')->first();
					$empDetails = Employee_details::where("emp_id",$userData->employee_id)->where("job_function",3)->orderBy('id', 'desc')->first();
					$btnvisible=0;
					$tabvisible=0;

					if($empDetails)
					{
						$btnvisible=1;
					}
					if($userData->group_id==13)
					{
						$tabvisible=1;
					}
					




			   $documentCollectiondetails->setPath(config('app.url/listingAllEmployee'));
			   
	   //print_r($documentCollectiondetails);exit;
	   
		$salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
	   return view("EmployeePerformanceReview/listingAllEmployee",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue','btnvisible','tabvisible'));
	  }


	  public function addEmployeeWarningPostData(Request $request)
		{
			//return $request->all();
			$empData = Employee_details::where("emp_id",$request->empid)->orderBy('id','DESC')->first();
			$warningData = WarningLetterRequest::where("emp_id",$request->empid)->orderBy('id','DESC')->first();

			//return $warningData;
	
			if($warningData)
			{	
				$empsessionId=$request->session()->get('EmployeeId');
				
				//$userData = User::where("id",$empsessionId)->orderBy('id','DESC')->first();
				//$usersids = array(101456,101058,101042,100762,101466);	
				//return $userData->employee_id;s
	
				$warningletterRequest = new WarningLetterRequest();
				$warningletterRequest->emp_id = $request->empid;
				$warningletterRequest->dept_id =$empData->dept_id;
				$warningletterRequest->tl_id =$empData->tl_id;
				$warningletterRequest->status =1;

				if($warningData->reject_status==1)
				{		
					$warningletterRequest->counter =$warningData->counter;
				}
				else{
					$warningletterRequest->counter =$warningData->counter+1;
				}

				$warningletterRequest->createdby =$empsessionId;
				$warningletterRequest->warning_letter_reason =$request->reason;
				$warningletterRequest->comments =$request->comment;

	
				$warningletterRequest->save();
	
				$response['code'] = '200';
				$response['message'] = "Data Saved Successfully.";
				echo json_encode($response);			
			}
			else
			{
				$empsessionId=$request->session()->get('EmployeeId');
				
				//$userData = User::where("id",$empsessionId)->orderBy('id','DESC')->first();
				//$usersids = array(101456,101058,101042,100762,101466);	
				//return $userData->employee_id;s
	
				$warningletterRequest = new WarningLetterRequest();
				$warningletterRequest->emp_id = $request->empid;
				$warningletterRequest->dept_id =$empData->dept_id;
				$warningletterRequest->tl_id =$empData->tl_id;
				$warningletterRequest->status =1;
				$warningletterRequest->createdby =$empsessionId;
				$warningletterRequest->warning_letter_reason =$request->reason;
				$warningletterRequest->comments =$request->comment;

	
				$warningletterRequest->save();
	
				$response['code'] = '200';
				$response['message'] = "Data Saved Successfully.";
				echo json_encode($response);
			}
		}


		
		public function getleavingTypePopupData2(Request $request)
		{			
			$empDataFirst = WarningLetterRequest::select('emp_id')->get()->toArray();
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
					->where('employee_details.dept_id',$empDetails->dept_id)
					->get();
				}
			}
			else{
				$empData = Employee_details::join('department_details', 'employee_details.dept_id', '=', 'department_details.id')
				->select('employee_details.emp_id', 'department_details.department_name', 'employee_details.emp_name')
				//->whereNotIn('emp_id', $empDataFirst)
				->get();
			}


			

	

			
			return view("EmployeePerformanceReview/LeavingType2",compact('empData','userData'));
		}


		public function getwarnletterparentformData(Request $request)
		{
			$empid =  $request->empid;

			$empData = WarningLetterRequest::where("warning_letter_requests.status",1)
				   ->join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
				   ->select('employee_details.*', 'warning_letter_requests.*')
				   ->where('warning_letter_requests.emp_id', $empid)
				   //->orWhere('change_salary_request.request_type', 3)
				   //->where('change_salary_request.request_type', 3)
				   //->whereRaw($whereraw)
				   ->orderBy('warning_letter_requests.id', 'desc')->first();
	
	
			
			return view("EmployeePerformanceReview/LeavingType",compact('empData'));
		}


		public function firstWarningLetterPost(Request $rq)
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
						if(file_exists(public_path('warningLetter/'.$newFileName))){

							  unlink(public_path('warningLetter/'.$newFileName));

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
						
						
						$rq->file($key)->move(public_path('warningLetter/'), $newFileName);
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
					
					
					$userid=$rq->session()->get('EmployeeId');
					$empdata = WarningLetterRequest::where("emp_id",$rq->input('emp_id'))->where("id",$rq->input('row_id'))->orderBy('id','DESC')->first();
					//return $empdata;


					if($empdata->warning_letter_count==0)
					{
						$empdata->warning_letter_comment =$rq->input('warnlettercomment');
						$empdata->warning_letter_status =1;
						$empdata->status =3;
						$empdata->warning_letter_count =$empdata->warning_letter_count+1;
						$empdata->warning_letter_issued_on =date('Y-m-d H:i:s');
						$empdata->warning_letter_issued_by =$userid;
						$empdata->final_status =1;
	
						if($newFileName!='')
						{
							$empdata->warning_letter =$newFileName;
						}
						$empdata->save();
					}


			
			$response['code'] = '200';
			$response['message'] = " Saved  Successfully.";				
			echo json_encode($response);
		    exit;
		}	


		public static function getStatus($empid = NULL,$id = NULL)
		{
			$empdata = WarningLetterRequest::where("emp_id",$empid)->where("id",$id)->orderBy('id','DESC')->first();



			$ends = array('th','st','nd','rd','th','th','th','th','th','th');
			if ((($empdata->counter % 100) >= 11) && (($empdata->counter%100) <= 13))
				$wcounter = $empdata->counter. 'th';
			else
			$wcounter = $empdata->counter. $ends[$empdata->counter % 10];


			
			
			

			if($empdata->approved_status== 0 && $empdata->reject_status==0 && $empdata->status==1 && $empdata->final_status==0)
			{
				return $wcounter.' Warning Letter Request Pending for Approval/Reject';
			}
			else if($empdata->approved_status== 1 && $empdata->reject_status==0 && $empdata->status==2 && $empdata->final_status==0)
			{
				return $wcounter.' Warning Letter Request Approved';
			}
			else if($empdata->approved_status== 0 && $empdata->reject_status==1 && $empdata->status==1 && $empdata->final_status==0)
			{
				return $wcounter.' Warning Letter Request Rejected';
			}
			else if($empdata->warning_letter_status== 1 && $empdata->final_status==1)
			{
				return $wcounter.' Warning Letter Issued';
			}
			// else if($empdata->first_warning_letter_status== 1 && $empdata->second_warning_letter_status==1 && $empdata->third_warning_letter_status==0 && $empdata->final_status==0)
			// {
			// 	return 'Second Warning Letter Issued';
			// }
			// else if($empdata->first_warning_letter_status== 1 && $empdata->second_warning_letter_status==1 && $empdata->third_warning_letter_status==1 && $empdata->final_status==0)
			// {
			// 	return 'Third Warning Letter Issued';
			// }
			// else if($empdata->first_warning_letter_status== 1 && $empdata->second_warning_letter_status==1 && $empdata->third_warning_letter_status==1 && $empdata->final_status==1)
			// {
			// 	return 'Tirmination Done';
			// }



			// else if($empdata->first_warning_letter_status== 0 && $empdata->second_warning_letter_status==0 && $empdata->third_warning_letter_status==0 && $empdata->final_status==0)
			// {
			// 	return 'First Warning Letter Pending';
			// }
			// else if($empdata->first_warning_letter_status== 1 && $empdata->second_warning_letter_status==0 && $empdata->third_warning_letter_status==0 && $empdata->final_status==0)
			// {
			// 	return 'First Warning Letter Issued';
			// }
			// else if($empdata->first_warning_letter_status== 1 && $empdata->second_warning_letter_status==1 && $empdata->third_warning_letter_status==0 && $empdata->final_status==0)
			// {
			// 	return 'Second Warning Letter Issued';
			// }
			// else if($empdata->first_warning_letter_status== 1 && $empdata->second_warning_letter_status==1 && $empdata->third_warning_letter_status==1 && $empdata->final_status==0)
			// {
			// 	return 'Third Warning Letter Issued';
			// }

			// else if($empdata->first_warning_letter_status== 1 && $empdata->second_warning_letter_status==1 && $empdata->third_warning_letter_status==1 && $empdata->final_status==1)
			// {
			// 	return 'Tirmination Done';
			// }
			else{
			return "---";
			}
		 
			
		}

		public static function checkWarningStatus($emp_id)
	   {
			$empdata = WarningLetterRequest::where("emp_id",$emp_id)->orderBy('id','DESC')->first();
			if($empdata->first_warning_letter_status==1 && $empdata->status==2)
			{
				return 1;
			}
			else if($empdata->status==3 && $empdata->second_warning_letter_status==1)
			{
				return 2;
			}
			else if($empdata->status==5 && $empdata->third_warning_letter_status==1)
			{
				return 3;
			}
			else{
				return 0;
			}
			
	   }




	   public function secondWarningLetterPost(Request $rq)
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
					   if(file_exists(public_path('warningLetter/'.$newFileName))){

							 unlink(public_path('warningLetter/'.$newFileName));

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
					   
					   
					   $rq->file($key)->move(public_path('warningLetter/'), $newFileName);
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
				   
				   

				   $empdata = WarningLetterRequest::where("emp_id",$rq->input('emp_id'))->orderBy('id','DESC')->first();
				   //return $empdata;
				   if($empdata->first_warning_letter_status==1 && $empdata->second_warning_letter_status==0 && $empdata->third_warning_letter_status==0)
				   {
					   $empdata->second_warning_letter_comment =$rq->input('warnlettercomment');
					   $empdata->second_warning_letter_status =1;
					   $empdata->status =4;
   
					   if($newFileName!='')
					   {
						   $empdata->second_warning_letter =$newFileName;
					   }

				   }					
				   $empdata->save();

		   
		   $response['code'] = '200';
		   $response['message'] = "Saved  Successfully.";				
		   echo json_encode($response);
		   exit;
	   }	




	   public function thirdWarningLetterPost(Request $rq)
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
					   if(file_exists(public_path('warningLetter/'.$newFileName))){

							 unlink(public_path('warningLetter/'.$newFileName));

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
					   
					   
					   $rq->file($key)->move(public_path('warningLetter/'), $newFileName);
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
				   
				   

				   $empdata = WarningLetterRequest::where("emp_id",$rq->input('emp_id'))->orderBy('id','DESC')->first();
				   //return $empdata;
				   if($empdata->first_warning_letter_status==1 && $empdata->second_warning_letter_status==1 && $empdata->third_warning_letter_status==0)
				   {
					   $empdata->third_warning_letter_comment =$rq->input('warnlettercomment');
					   $empdata->third_warning_letter_status =1;
					   $empdata->status =5;
   
					   if($newFileName!='')
					   {
						   $empdata->third_warning_letter =$newFileName;
					   }
					   

				   }					
				   $empdata->save();

		   
		   $response['code'] = '200';
		   $response['message'] = " Saved  Successfully.";				
		   echo json_encode($response);
		   exit;
	   }	



	   public function listingStepOneData(Request $request)
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
			
			if(!empty($request->session()->get('warning_page_limit')))
				{
					$paginationValue = $request->session()->get('warning_page_limit');
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
				
				
				if(!empty($request->session()->get('warning_letter_fromdate')) && $request->session()->get('warning_letter_fromdate') != 'All')
				{
					$datefrom = $request->session()->get('warning_letter_fromdate');
					 if($whereraw == '')
					{
						$whereraw = 'warning_letter_requests.created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And warning_letter_requests.created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('warning_letter_todate')) && $request->session()->get('warning_letter_todate') != 'All')
				{
					$dateto = $request->session()->get('warning_letter_todate');
					 if($whereraw == '')
					{
						$whereraw = 'warning_letter_requests.created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And warning_letter_requests.created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('warning_letter_department')) && $request->session()->get('warning_letter_department') != 'All')
				{
					$dept = $request->session()->get('warning_letter_department');
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
				if(!empty($request->session()->get('warning_letter_teamleader')) && $request->session()->get('warning_letter_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('warning_letter_teamleader');
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
				if(!empty($request->session()->get('warning_letter_emp_id')) && $request->session()->get('warning_letter_emp_id') != 'All')
				{
					$empId = $request->session()->get('warning_letter_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'warning_letter_requests.emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And warning_letter_requests.emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('warning_letter_emp_name')) && $request->session()->get('warning_letter_emp_name') != 'All')
				{
					$fname = $request->session()->get('warning_letter_emp_name');
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
						$whereraw = 'employee_details.emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And employee_details.emp_name IN('.$finalcname.')';
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
				if(!empty($request->session()->get('warning_letter_designation')) && $request->session()->get('warning_letter_designation') != 'All')
				{
					$designd = $request->session()->get('warning_letter_designation');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
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
							$documentCollectiondetails = WarningLetterRequest::
							join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
							->select('employee_details.*', 'warning_letter_requests.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->whereRaw($whereraw)
							->where('warning_letter_requests.approved_status', 0)
							->where('warning_letter_requests.reject_status', 0)
							//->where('warning_letter_requests.second_warning_letter_status', 0)
							->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);
		
		
							$reportsCount = WarningLetterRequest::
							join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
							->select('employee_details.*', 'warning_letter_requests.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->whereRaw($whereraw)
							->where('warning_letter_requests.approved_status', 0)
							->where('warning_letter_requests.reject_status', 0)
							//->where('warning_letter_requests.second_warning_letter_status', 0)
							->orderBy('warning_letter_requests.id', 'desc')
							->get()->count();
						}
					}
					else{
						$documentCollectiondetails = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->whereRaw($whereraw)
						->where('warning_letter_requests.approved_status', 0)
						->where('warning_letter_requests.reject_status', 0)
						//->where('warning_letter_requests.second_warning_letter_status', 0)
						->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);


						$reportsCount = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->whereRaw($whereraw)
						->where('warning_letter_requests.approved_status', 0)
						->where('warning_letter_requests.reject_status', 0)
						//->where('warning_letter_requests.second_warning_letter_status', 0)
						->orderBy('warning_letter_requests.id', 'desc')
						->get()->count();
					}

					
					
					
					
					
					
					
					//echo $whereraw;
					//echo "hello";exit;
					//$documentCollectiondetails = EmpOffline::whereRaw($whereraw)->orderBy("created_at","DESC")->paginate($paginationValue);
					
					
					

					
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
							$documentCollectiondetails = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->where('warning_letter_requests.approved_status', 0)
						->where('warning_letter_requests.reject_status', 0)
						//->where('warning_letter_requests.second_warning_letter_status', 0)
						->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);
	
						//$documentCollectiondetails = EmpOffline::whereRaw($whereraw1)->orderBy("created_at","DESC")->paginate($paginationValue);
						//$reportsCount = ChangeSalary::get()->count();
	
						$reportsCount = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->where('warning_letter_requests.approved_status', 0)
						->where('warning_letter_requests.reject_status', 0)
						->orderBy('warning_letter_requests.id', 'desc')->get()->count();
						}
					}
					else{
						$documentCollectiondetails = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('warning_letter_requests.approved_status', 0)
						->where('warning_letter_requests.reject_status', 0)
						//->where('warning_letter_requests.second_warning_letter_status', 0)
						->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);
	
						//$documentCollectiondetails = EmpOffline::whereRaw($whereraw1)->orderBy("created_at","DESC")->paginate($paginationValue);
						//$reportsCount = ChangeSalary::get()->count();
	
						$reportsCount = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('warning_letter_requests.approved_status', 0)
						->where('warning_letter_requests.reject_status', 0)
						->orderBy('warning_letter_requests.id', 'desc')->get()->count();
					}

					
					
					
					
					
					
					
					
					
					//echo "hello1";
					//$whereraw1 = 'condition_leaving = 1 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL';			
					
					

					



					

					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$documentCollectiondetails->setPath(config('app.url/listingEmpStepOne'));



				$userid=$request->session()->get('EmployeeId');
				$userData = User::where("id",$userid)->orderBy('id', 'desc')->first();
				$empDetails = Employee_details::where("emp_id",$userData->employee_id)->where("job_function",3)->orderBy('id', 'desc')->first();
				$btnvisible=0;

				if($empDetails)
				{
					$btnvisible=1;
				}
				

				// echo "hhhhhh".$empsessionId;


				// $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();


				// return $departmentDetails;


				
		
		
		 
		return view("EmployeePerformanceReview/listingEmpStepOne",compact('departmentLists','productDetails','paginationValue','designationDetails','documentCollectiondetails','reportsCount','btnvisible'));
	   }












	   
	   public function listingStepTwoData(Request $request)
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
			
			if(!empty($request->session()->get('warning_page_limit')))
				{
					$paginationValue = $request->session()->get('warning_page_limit');
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
				
				
				if(!empty($request->session()->get('warning_letter_fromdate')) && $request->session()->get('warning_letter_fromdate') != 'All')
				{
					$datefrom = $request->session()->get('warning_letter_fromdate');
					 if($whereraw == '')
					{
						$whereraw = 'warning_letter_requests.created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And warning_letter_requests.created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('warning_letter_todate')) && $request->session()->get('warning_letter_todate') != 'All')
				{
					$dateto = $request->session()->get('warning_letter_todate');
					 if($whereraw == '')
					{
						$whereraw = 'warning_letter_requests.created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And warning_letter_requests.created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('warning_letter_department')) && $request->session()->get('warning_letter_department') != 'All')
				{
					$dept = $request->session()->get('warning_letter_department');
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
				if(!empty($request->session()->get('warning_letter_teamleader')) && $request->session()->get('warning_letter_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('warning_letter_teamleader');
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
				if(!empty($request->session()->get('warning_letter_emp_id')) && $request->session()->get('warning_letter_emp_id') != 'All')
				{
					$empId = $request->session()->get('warning_letter_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'warning_letter_requests.emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And warning_letter_requests.emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('warning_letter_emp_name')) && $request->session()->get('warning_letter_emp_name') != 'All')
				{
					$fname = $request->session()->get('warning_letter_emp_name');
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
						$whereraw = 'employee_details.emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And employee_details.emp_name IN('.$finalcname.')';
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
				if(!empty($request->session()->get('warning_letter_designation')) && $request->session()->get('warning_letter_designation') != 'All')
				{
					$designd = $request->session()->get('warning_letter_designation');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
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
							$documentCollectiondetails = WarningLetterRequest::
							join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
							->select('employee_details.*', 'warning_letter_requests.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->whereRaw($whereraw)
							->where('warning_letter_requests.final_status', 0)
							->where('warning_letter_requests.approved_status', 1)
							//->orWhere('warning_letter_requests.reject_status', 1)
							->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);
		
							//$reportsCount = Employee_details::whereRaw($whereraw)->where("offline_status",1)->get()->count();
		
							$reportsCount = WarningLetterRequest::
							join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
							->select('employee_details.*', 'warning_letter_requests.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->whereRaw($whereraw)
							->where('warning_letter_requests.final_status', 0)
							->where('warning_letter_requests.approved_status', 1)
							//->orWhere('warning_letter_requests.reject_status', 1)
							->orderBy('warning_letter_requests.id', 'desc')
							->get()->count();
						}
					}
					else{
						$documentCollectiondetails = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->whereRaw($whereraw)
						->where('warning_letter_requests.final_status', 0)
						->where('warning_letter_requests.approved_status', 1)
						//->orWhere('warning_letter_requests.reject_status', 1)
						->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);
	
						//$reportsCount = Employee_details::whereRaw($whereraw)->where("offline_status",1)->get()->count();
	
						$reportsCount = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->whereRaw($whereraw)
						->where('warning_letter_requests.final_status', 0)
						->where('warning_letter_requests.approved_status', 1)
						//->orWhere('warning_letter_requests.reject_status', 1)
						->orderBy('warning_letter_requests.id', 'desc')
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
							$documentCollectiondetails = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->where('warning_letter_requests.final_status', 0)
						->where('warning_letter_requests.approved_status', 1)
						//->orWhere('warning_letter_requests.reject_status', 1)
						->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);
	
						//$documentCollectiondetails = EmpOffline::whereRaw($whereraw1)->orderBy("created_at","DESC")->paginate($paginationValue);
						//$reportsCount = ChangeSalary::get()->count();
	
						$reportsCount = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->where('warning_letter_requests.final_status', 0)
						->where('warning_letter_requests.approved_status', 1)
						//->orWhere('warning_letter_requests.reject_status', 1)
						->orderBy('warning_letter_requests.id', 'desc')->get()->count();
						}
					}
					else{
						$documentCollectiondetails = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('warning_letter_requests.final_status', 0)
						->where('warning_letter_requests.approved_status', 1)
						//->orWhere('warning_letter_requests.reject_status', 1)
						->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);
	
						//$documentCollectiondetails = EmpOffline::whereRaw($whereraw1)->orderBy("created_at","DESC")->paginate($paginationValue);
						//$reportsCount = ChangeSalary::get()->count();
	
						$reportsCount = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('warning_letter_requests.final_status', 0)
						->where('warning_letter_requests.approved_status', 1)
						//->orWhere('warning_letter_requests.reject_status', 1)
						->orderBy('warning_letter_requests.id', 'desc')->get()->count();
					}

					
					
					
					//echo "hello1";
					//$whereraw1 = 'condition_leaving = 1 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL';

					

					

					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$documentCollectiondetails->setPath(config('app.url/listingEmpStepTwo'));


				$empsessionId=$request->session()->get('EmployeeId');

				
		 
		return view("EmployeePerformanceReview/listingEmpStepTwo",compact('departmentLists','productDetails','paginationValue','designationDetails','documentCollectiondetails','reportsCount'));
	   }









	   
	   public function listingStepThreeData(Request $request)
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
				
				
				if(!empty($request->session()->get('warning_letter_fromdate')) && $request->session()->get('warning_letter_fromdate') != 'All')
				{
					$datefrom = $request->session()->get('warning_letter_fromdate');
					 if($whereraw == '')
					{
						$whereraw = 'warning_letter_requests.created_at>= "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And warning_letter_requests.created_at>= "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('warning_letter_todate')) && $request->session()->get('warning_letter_todate') != 'All')
				{
					$dateto = $request->session()->get('warning_letter_todate');
					 if($whereraw == '')
					{
						$whereraw = 'warning_letter_requests.created_at<= "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And warning_letter_requests.created_at<= "'.$dateto.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('warning_letter_department')) && $request->session()->get('warning_letter_department') != 'All')
				{
					$dept = $request->session()->get('warning_letter_department');
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
				if(!empty($request->session()->get('warning_letter_teamleader')) && $request->session()->get('warning_letter_teamleader') != 'All')
				{
					$teamlead = $request->session()->get('warning_letter_teamleader');
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
				if(!empty($request->session()->get('warning_letter_emp_id')) && $request->session()->get('warning_letter_emp_id') != 'All')
				{
					$empId = $request->session()->get('warning_letter_emp_id');
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('warning_letter_emp_name')) && $request->session()->get('warning_letter_emp_name') != 'All')
				{
					$fname = $request->session()->get('warning_letter_emp_name');
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
				if(!empty($request->session()->get('warning_letter_designation')) && $request->session()->get('warning_letter_designation') != 'All')
				{
					$designd = $request->session()->get('warning_letter_designation');
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
				
				
				
								
				if($whereraw != '')
				{
					
					
					
					$empsessionId=$request->session()->get('EmployeeId');
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
					if($departmentDetails != '')
					{
						$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
						if($empDetails!='')
						{
							$documentCollectiondetails = WarningLetterRequest::
							join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
							->select('employee_details.*', 'warning_letter_requests.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->whereRaw($whereraw)
							->where('warning_letter_requests.second_warning_letter_status', 1)
							->where('warning_letter_requests.third_warning_letter_status', 0)
							->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);
			
			
			
			
			
							//$reportsCount = Employee_details::whereRaw($whereraw)->where("offline_status",1)->get()->count();
			
							$reportsCount = WarningLetterRequest::
							join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
							->select('employee_details.*', 'warning_letter_requests.*')
							->where('employee_details.dept_id',$empDetails->dept_id)
							->whereRaw($whereraw)
							->where('warning_letter_requests.second_warning_letter_status', 1)
							->where('warning_letter_requests.third_warning_letter_status', 0)
							->orderBy('warning_letter_requests.id', 'desc')
							->get()->count();
						}
					}
					else{
						$documentCollectiondetails = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->whereRaw($whereraw)
						->where('warning_letter_requests.second_warning_letter_status', 1)
						->where('warning_letter_requests.third_warning_letter_status', 0)
						->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);
		
		
		
		
		
						//$reportsCount = Employee_details::whereRaw($whereraw)->where("offline_status",1)->get()->count();
		
						$reportsCount = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->whereRaw($whereraw)
						->where('warning_letter_requests.second_warning_letter_status', 1)
						->where('warning_letter_requests.third_warning_letter_status', 0)
						->orderBy('warning_letter_requests.id', 'desc')
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
							$documentCollectiondetails = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->where('warning_letter_requests.second_warning_letter_status', 1)
						->where('warning_letter_requests.third_warning_letter_status', 0)
						->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);
	
						//$documentCollectiondetails = EmpOffline::whereRaw($whereraw1)->orderBy("created_at","DESC")->paginate($paginationValue);
						//$reportsCount = ChangeSalary::get()->count();
	
						$reportsCount = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->where('warning_letter_requests.second_warning_letter_status', 1)
						->where('warning_letter_requests.third_warning_letter_status', 0)
						->orderBy('warning_letter_requests.id', 'desc')->get()->count();
						}
					}
					else{
						$documentCollectiondetails = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('warning_letter_requests.second_warning_letter_status', 1)
						->where('warning_letter_requests.third_warning_letter_status', 0)
						->orderBy('warning_letter_requests.id', 'desc')->paginate($paginationValue);
	
						//$documentCollectiondetails = EmpOffline::whereRaw($whereraw1)->orderBy("created_at","DESC")->paginate($paginationValue);
						//$reportsCount = ChangeSalary::get()->count();
	
						$reportsCount = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('warning_letter_requests.second_warning_letter_status', 1)
						->where('warning_letter_requests.third_warning_letter_status', 0)
						->orderBy('warning_letter_requests.id', 'desc')->get()->count();
					}

					
					
					
					//echo "hello1";
					//$whereraw1 = 'condition_leaving = 1 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL';

					

					
					
					//$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					//$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					//$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
					$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
					$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
				
				$documentCollectiondetails->setPath(config('app.url/listingEmpStepThree'));


				$empsessionId=$request->session()->get('EmployeeId');

				// echo "hhhhhh".$empsessionId;


				// $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();


				// return $departmentDetails;


				
		
		
		 
		return view("EmployeePerformanceReview/listingEmpStepThree",compact('departmentLists','productDetails','paginationValue','designationDetails','documentCollectiondetails','reportsCount'));
	   }












	   
	public function listingConfirmtabData(Request $request)
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
		
		if(!empty($request->session()->get('warning_page_limit')))
			{
				$paginationValue = $request->session()->get('warning_page_limit');
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
			
			
			if(!empty($request->session()->get('warning_letter_fromdate')) && $request->session()->get('warning_letter_fromdate') != 'All')
			{
				$datefrom = $request->session()->get('warning_letter_fromdate');
				 if($whereraw == '')
				{
					$whereraw = 'warning_letter_requests.created_at>= "'.$datefrom.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And warning_letter_requests.created_at>= "'.$datefrom.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('warning_letter_todate')) && $request->session()->get('warning_letter_todate') != 'All')
			{
				$dateto = $request->session()->get('warning_letter_todate');
				 if($whereraw == '')
				{
					$whereraw = 'warning_letter_requests.created_at<= "'.$dateto.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And warning_letter_requests.created_at<= "'.$dateto.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('warning_letter_department')) && $request->session()->get('warning_letter_department') != 'All')
			{
				$dept = $request->session()->get('warning_letter_department');
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
			if(!empty($request->session()->get('warning_letter_teamleader')) && $request->session()->get('warning_letter_teamleader') != 'All')
			{
				$teamlead = $request->session()->get('warning_letter_teamleader');
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
			if(!empty($request->session()->get('warning_letter_emp_id')) && $request->session()->get('warning_letter_emp_id') != 'All')
			{
				$empId = $request->session()->get('warning_letter_emp_id');
				 if($whereraw == '')
				{
					$whereraw = 'warning_letter_requests.emp_id IN ('.$empId.')';
				}
				else
				{
					$whereraw .= ' And warning_letter_requests.emp_id IN ('.$empId.')';
				}
			}
			if(!empty($request->session()->get('warning_letter_emp_name')) && $request->session()->get('warning_letter_emp_name') != 'All')
			{
				$fname = $request->session()->get('warning_letter_emp_name');
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
					$whereraw = 'employee_details.emp_name IN('.$finalcname.')';
				}
				else
				{
					$whereraw .= ' And employee_details.emp_name IN('.$finalcname.')';
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
			if(!empty($request->session()->get('warning_letter_designation')) && $request->session()->get('warning_letter_designation') != 'All')
			{
				$designd = $request->session()->get('warning_letter_designation');
				 //$departmentArray = explode(",",$designd);
				if($whereraw == '')
				{
					$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
				}
				else
				{
					$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
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
						$documentCollectiondetails = DB::select( DB::raw("select * from employee_details
						join warning_letter_requests ON warning_letter_requests.emp_id = employee_details.emp_id
						where ".$whereraw."
						AND warning_letter_requests.warning_letter_status=1 AND warning_letter_requests.final_status=1
						AND warning_letter_requests.id in (select max(id) from warning_letter_requests group by emp_id);") );
	
					
	
						//return $documentCollectiondetails;
	
	
	
						
						$newResult=array();
						foreach($documentCollectiondetails as $value)
						{
							$newResult[]=$value->id;
						}
						$documentCollectiondetails = WarningLetterRequest::join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->whereIn('warning_letter_requests.id',$newResult)
						->orderBy('warning_letter_requests.id', 'desc')
						->paginate($paginationValue);
		
						$reportsCount = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->whereRaw($whereraw)
						->where('warning_letter_requests.warning_letter_status', 1)
						->where('warning_letter_requests.final_status', 1)
						->orderBy('warning_letter_requests.id', 'desc')
						->groupBy('warning_letter_requests.emp_id')
						->get()->count();
					}
				}
				else{
					$documentCollectiondetails = DB::select( DB::raw("select * from employee_details
						join warning_letter_requests ON warning_letter_requests.emp_id = employee_details.emp_id
						where ".$whereraw."
						AND warning_letter_requests.warning_letter_status=1 AND warning_letter_requests.final_status=1
						AND warning_letter_requests.id in (select max(id) from warning_letter_requests group by emp_id);") );
	
					
	
						//return $documentCollectiondetails;
	
	
	
						
						$newResult=array();
						foreach($documentCollectiondetails as $value)
						{
							$newResult[]=$value->id;
						}
						$documentCollectiondetails = WarningLetterRequest::join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->whereIn('warning_letter_requests.id',$newResult)
						->orderBy('warning_letter_requests.id', 'desc')
						->paginate($paginationValue);
		
						$reportsCount = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->whereRaw($whereraw)
						->where('warning_letter_requests.warning_letter_status', 1)
						->where('warning_letter_requests.final_status', 1)
						->orderBy('warning_letter_requests.id', 'desc')
						->groupBy('warning_letter_requests.emp_id')
						->get()->count();
				}

				
				
				
				
				
				//echo $whereraw;
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
						$documentCollectiondetails = DB::select( DB::raw("select * from employee_details
						join warning_letter_requests ON warning_letter_requests.emp_id = employee_details.emp_id
						where warning_letter_requests.final_status=1
						AND warning_letter_requests.warning_letter_status=1 AND
						warning_letter_requests.id in (select max(id) from warning_letter_requests group by emp_id);") );
		
						$newResult=array();
						foreach($documentCollectiondetails as $value)
						{
							$newResult[]=$value->id;
						}
						$documentCollectiondetails = WarningLetterRequest::join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->whereIn('warning_letter_requests.id',$newResult)
						->orderBy('warning_letter_requests.id', 'desc')
						//->get();
	
						//return $documentCollectiondetails;
	
						->paginate($paginationValue);
		
						$reportsCount = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->where('warning_letter_requests.warning_letter_status', 1)
						->where('warning_letter_requests.final_status', 1)
						->orderBy('warning_letter_requests.id', 'desc')
						->groupBy('warning_letter_requests.emp_id')
						->get()->count();
					}
				}
				else{
					$documentCollectiondetails = DB::select( DB::raw("select * from employee_details
						join warning_letter_requests ON warning_letter_requests.emp_id = employee_details.emp_id
						where warning_letter_requests.final_status=1
						AND warning_letter_requests.warning_letter_status=1 AND
						warning_letter_requests.id in (select max(id) from warning_letter_requests group by emp_id);") );
		
						$newResult=array();
						foreach($documentCollectiondetails as $value)
						{
							$newResult[]=$value->id;
						}
						$documentCollectiondetails = WarningLetterRequest::join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->whereIn('warning_letter_requests.id',$newResult)
						->orderBy('warning_letter_requests.id', 'desc')
						//->get();
	
						//return $documentCollectiondetails;
	
						->paginate($paginationValue);
		
						$reportsCount = WarningLetterRequest::
						join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
						->select('employee_details.*', 'warning_letter_requests.*')
						->where('warning_letter_requests.warning_letter_status', 1)
						->where('warning_letter_requests.final_status', 1)
						->orderBy('warning_letter_requests.id', 'desc')
						->groupBy('warning_letter_requests.emp_id')
						->get()->count();
				}

				
				
				
				//echo "hello1";


			
					



				
				
			}
			 $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
				 $productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
				 $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
			
			 $documentCollectiondetails->setPath(config('app.url/listingConfirmtab'));
			 
			 
	 return view("EmployeePerformanceReview/listingConfirmtab",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','reportsCount','filterList','paginationValue','selectedFilter'));
	}

	public function exportConfirmTabReport(Request $request)
	{
		
			$parameters = $request->input(); 
			$selectedId = $parameters['selectedIds'];

			//return $selectedId;
			 
			$filename = 'warning_letters_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet(); 
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:M1');
			$sheet->setCellValue('A1', 'Warnig Letters List - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Team Leader'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Designation'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Department'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Work Location'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Source Code'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Tenure'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('J'.$indexCounter, strtoupper('Mobile Number'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('K'.$indexCounter, strtoupper('Reason'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('L'.$indexCounter, strtoupper('Comment'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('M'.$indexCounter, strtoupper('Status'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				//echo $sid;
				$misData = WarningLetterRequest::join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
				->where("warning_letter_requests.id",$sid)->first();

				//$empName = $this->getEmployeeName($misData->emp_id);
				$teamLeader = $this->getTeamLeader($misData->tl_id);
				$designation = $this->getAttributeValuedesign($misData->designation_by_doc_collection);
				$dept = $this->getDepartment($misData->emp_id);
				$location = $this->getAttributeListValue($misData->emp_id,'work_location');
				$tenure = $this->getTimeFromJoining($misData->emp_id);
				$mobile = $this->getlocalMobileNo($misData->emp_id,'CONTACT_NUMBER');
				$reason = $this->getWarningReason($misData->warning_letter_reason);
				$status = $this->getStatus($misData->emp_id,$sid);

				$indexCounter++; 
				
				$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $misData->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $teamLeader)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $designation)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $dept)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $misData->source_code)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('I'.$indexCounter, $tenure)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('J'.$indexCounter, $mobile)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('K'.$indexCounter, $reason)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	


				$sheet->setCellValue('L'.$indexCounter, $misData->comments)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	


				$sheet->setCellValue('M'.$indexCounter, $status)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	

				
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
				$writer->save(public_path('uploads/exportWarningLetter/'.$filename));	
				echo $filename;
				exit;
		}


		public static function getDepartment($empid)
		{
			$empDetails = Employee_details::where("emp_id",$empid)->orderBy('id','desc')->first();
			
			if($empDetails)
			{
				$departmentDetails = Department::where("id",$empDetails->dept_id)->first();
				if($departmentDetails != '')
				{
					return $departmentDetails->department_name;
				}
				else{
					 return '--'; 
				}
	
			}
			else{
				return '--';
			}
		  
		}


	public function finalTerminationProcess(Request $request)
	{
		$empid =  $request->empid;

		//return "Hello";


			$warningData = WarningLetterRequest::where("emp_id",$empid)->orderBy('id','DESC')->first();
			$warningData->final_status=1;
			$warningData->status=5;
			$warningData->save();

			 $empdetails =Employee_details::where("emp_id",$empid)->first();
			 if($empdetails!=''){
			 $offlineObj=new EmpOffline();
			 $offlineObj->emp_id=$empdetails->emp_id;			 
			 $offlineObj->emp_name=$empdetails->first_name.' '.$empdetails->middle_name. ' '.$empdetails->last_name;
			 $offlineObj->tl_se=$empdetails->tl_id;			 
			 $offlineObj->designation=$empdetails->designation_by_doc_collection;
			 $offlineObj->department=$empdetails->dept_id;
			 $empattributesMod = Employee_attribute::where('emp_id',$empid)->where('attribute_code','CONTACT_NUMBER')->first();
			 if($empattributesMod!=''){
				$offlineObj->mobile_no=$empattributesMod->attribute_values;
			 }else{
				 $offlineObj->mobile_no='';
			 }
			 $work_location = Employee_attribute::where('emp_id',$empid)->where('attribute_code','work_location')->first();
			 if($work_location!=''){
				 $offlineObj->location=$work_location->attribute_values;
			 }
			 else{
				 $offlineObj->location='';
			 }
			 $DOJ= Employee_attribute::where('emp_id',$empid)->where('attribute_code','DOJ')->first();
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
			 //$offlineObj->onboarding_date=$onboarding_date;
			 $offlineObj->leaving_type=2;
			 //$offlineObj->retain=2;
			 $offlineObj->created_by=$request->session()->get('EmployeeId');
			 $offlineObj->save();
			 $updateOBJ = Employee_details::where("emp_id",$empid)->first();
			 $updateOBJ->pre_offline_status=2;
			 
			 $updateOBJ->save();
				$response['code'] = '200';
			   $response['message'] = "Data Saved Successfully.";
			   //$response['empid'] = $empIdPadding;
			   
				echo json_encode($response);
			   exit;
			 }
	}


	public function setPageLimitProcess(Request $request)
	{
		$offset = $request->offset;
		   $request->session()->put('warning_page_limit',$offset);
	}
	
	
	public static function getDepartmentName($dept_id)
	{
		//return $dept_id;
		$departmentData =  Department::where("id",$dept_id)->first();
		return $departmentData->ID;
		return $departmentData->department_name;
	}


	public static function getAttributeValuedesign($design)
	{
		$designationMod = Designation::where("id",$design)->first();
		if($designationMod != '')
		{
			return $designationMod->name;
		}
		else{
			return ''; 
		}
		  
	}

	public static function getTeamLeader($id = NULL)
	{
			 $emp_details = Employee_details::where("id",$id)->first(); 
			 if($emp_details!='')
			 {
				return $emp_details->emp_name;
			}
			else
			{
				return "--";
			}
		
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

	public static function getlocalMobileNo($empid,$attributecode)
	{
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


	public function employeeStatusInfoData(Request $request)
	{
		$empid =  $request->empid;
		$warningData = WarningLetterRequest::where("emp_id",$empid)->orderBy('id','DESC')->first();
		$warningReasonsData = WarningLetterReasons::orderBy('id','DESC')->get();
		
		$warningletterEmpDetails = WarningLetterRequest::
		join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
		->select('employee_details.*', 'warning_letter_requests.*')
		->where('warning_letter_requests.emp_id', $empid)
		->where('warning_letter_requests.warning_letter_status', 1)
		->orderBy('warning_letter_requests.id', 'desc')->first();
		
		$warningletterCount = WarningLetterRequest::
		join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
		->select('employee_details.*', 'warning_letter_requests.*')
		->where('warning_letter_requests.emp_id', $empid)
		->where('warning_letter_requests.warning_letter_status', 1)
		->orderBy('warning_letter_requests.id', 'desc')->get()->count();


		if($warningletterEmpDetails)
		{
			$warningletterEmpDetailsData = $warningletterEmpDetails;
		}
		else
		{
			$warningletterEmpDetailsData = '';
		}

		if($warningData)
		{
			$warningEmpData=$warningData;
		}else{
			$warningEmpData='';		}

	 return view("EmployeePerformanceReview/empstatusDetails",compact('warningEmpData','warningReasonsData','warningletterEmpDetailsData','warningletterCount'));
	}

	public function warningLetterApprovedRequest(Request $request)
	{
		$empid =  $request->empid;
		$rowid =  $request->rowid;
		$userid=$request->session()->get('EmployeeId');
		$warningEmpData = WarningLetterRequest::where("emp_id",$empid)->where("id",$rowid)->orderBy('id','DESC')->first();
		if(!$warningEmpData)
		{
			return "Employee Not found.";
		}
		$warningEmpData->approved_status=1;
		$warningEmpData->status=2;
		$warningEmpData->approved_reject_on=date('Y-m-d H:i:s');
		$warningEmpData->approved_reject_by=$userid;
		$warningEmpData->save();

		$response['code'] = '200';
		$response['message'] = "Request Approved Successfully.";				
		echo json_encode($response);
	}

	public function warningLetterRejectRequest(Request $request)
	{
		$empid =  $request->empid;
		$rowid =  $request->rowid;

		$userid=$request->session()->get('EmployeeId');

		$warningEmpData = WarningLetterRequest::where("emp_id",$empid)->where("id",$rowid)->orderBy('id','DESC')->first();
		if(!$warningEmpData)
		{
			return "Employee Not found.";
		}
		$warningEmpData->reject_status=1;
		$warningEmpData->approved_reject_on=date('Y-m-d H:i:s');
		$warningEmpData->approved_reject_by=$userid;
		$warningEmpData->save();

		
		$response['code'] = '200';
		$response['message'] = "Request Rejected Successfully.";				
		echo json_encode($response);
	}

	public static function getWarningReason($reasonid)
	{
		
		$warningReasonsData = WarningLetterReasons::where('id',$reasonid)->orderBy('id','DESC')->first();

		if($warningReasonsData)
		{
			return $warningReasonsData->name;
		}
		else{
			return '--';
		}
		

	}

	public static function getCreatedInfo($userid)
	{
		$userData = User::where('id',$userid)->orderBy('id','DESC')->first();
		if($userData)
		{
			return $userData->fullname;
		}
		else{
			return '--';
		}
		

	}

	public static function getCounter($empid) {


		$warningEmpCounter = WarningLetterRequest::where("emp_id",$empid)->where("final_status",1)->orderBy('id','DESC')->get()->count();

		//return $warningEmpData;





		$ends = array('th','st','nd','rd','th','th','th','th','th','th');
		if ((($warningEmpCounter % 100) >= 11) && (($warningEmpCounter%100) <= 13))
			return $warningEmpCounter. 'th';
		else
			return $warningEmpCounter. $ends[$warningEmpCounter % 10];
	}



	public function downloadFile(Request $request)
{
		$file =  $request->filename;

		$fileName = public_path("/warningLetter");
		$newf = $fileName."/".$file;
		//return $newf;
        $headers = ['Content-Type: application/pdf'];
        $newName = 'warningLetter-'.time().'.pdf';
      
        return response()->download($newf, $newName, $headers);
}




public function searchWarningLetterData(Request $request)
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
			
			$request->session()->put('warning_letter_emp_name',$name);
			$request->session()->put('warning_letter_emp_id',$empId);
			$request->session()->put('warning_letter_fromdate',$datefrom);
			$request->session()->put('warning_letter_todate',$dateto);
			$request->session()->put('warning_letter_department',$department);
			$request->session()->put('warning_letter_teamleader',$teamlaed);
			
			$request->session()->put('warning_letter_designation',$design);
			// $request->session()->put('dateto_offboard_lastworkingday_list',$datetolastworkingday);
			// $request->session()->put('datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			
			// $request->session()->put('ReasonofAttrition_empoffboard_filter_list',$ReasonofAttrition);
			// $request->session()->put('empoffboard_status_filter_list',$offboardstatus);
			// $request->session()->put('datefrom_offboard_dort_list',$datefromdort);
			// $request->session()->put('dateto_offboard_dort_list',$datetodort);
			// $request->session()->put('empoffboard_ffstatus_filter_list',$offboardffstatus);
			 //return  redirect('listingPageonboarding');	
		}
		public function resetWarningLetterData(Request $request){
			$request->session()->put('warning_letter_fromdate','');
			$request->session()->put('warning_letter_todate','');
			$request->session()->put('warning_letter_department','');
			$request->session()->put('warning_letter_teamleader','');
			$request->session()->put('warning_letter_emp_name','');
			$request->session()->put('warning_letter_emp_id','');
			$request->session()->put('warning_letter_designation','');
			// $request->session()->put('dateto_offboard_lastworkingday_list','');
			// $request->session()->put('datefrom_offboard_lastworkingday_list','');
			// $request->session()->put('ReasonofAttrition_empoffboard_filter_list','');
			// $request->session()->put('empoffboard_status_filter_list','');
			// $request->session()->put('datefrom_offboard_dort_list','');
			// $request->session()->put('dateto_offboard_dort_list','');
			// $request->session()->put('empoffboard_ffstatus_filter_list','');
		}


	


	

// warning letter module end


}
