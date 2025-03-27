<?php
namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\Controller;
use App\Models\API\APIAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Employee\EmpAppAccess;
use App\Models\Employee\Employee_details;
use App\Models\Company\Department;
use App\Models\JobFunction\JobFunction;
use App\Models\Recruiter\Designation;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Common\MashreqLoginMIS;
use App\Models\Bank\CBD\CBDBankMis;
use App\Models\Employee\Employee_attribute;
use App\Models\KYCProcess\KYCProcess;
use  App\Models\Attribute\Attributes;
use DateTime;
use App\Models\Attribute\MasterAttribute;
use App\Models\Attribute\AttributeType;
use App\Models\Attribute\DepartmentFormChildEntry;
use App\Http\Controllers\Push\NotificatonController;
use Crypt;

class V3EMPAPIController extends Controller
{
	
	
	protected function getDepartmentName($deptId)
	{
		$deptMod = Department::where("id",$deptId)->first();
		if($deptMod != '')
		{
			return $deptMod->department_name;
		}
		else
		{
			return "-";
		}
	}
	
	protected function getFuncName($funcId)
	{
		$jobFuncModel = JobFunction::where("id",$funcId)->first();
		if($jobFuncModel != '')
		{
			return $jobFuncModel->name;
		}
		else
		{
			return "-";
		}
	}
	
	protected function getDesignationName($designId)
	{
		$designationMod = Designation::where("id",$designId)->first();
		if($designationMod != '')
		{
			return $designationMod->name;	
		}
		else
		{
			return "-";
		}
	}
	
	
	public function EmployeeProfileAPI(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' )
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
	
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				$result['responseCode'] = 200;
						$result['message'] = "Successfull";
				
				
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
	}
	
	
	public function salesSearchAgent(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' && isset($requestParameters['AppId']) && $requestParameters['AppId'] != '' && isset($requestParameters['deptId']) && $requestParameters['deptId'] != '')
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		$AppId = $requestParameters['AppId'];
		$deptId = $requestParameters['deptId'];
	
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				
				if($deptId == 36)
				{
					
					$ApplicationStatusList = DB::table('department_form_parent_entry')
							->join('mashreq_login_data', 'mashreq_login_data.ref_no', '=', 'department_form_parent_entry.ref_no')
							
							->where("department_form_parent_entry.form_id",1)
							->where("department_form_parent_entry.ref_no",$AppId)
							->where("department_form_parent_entry.emp_id",$empId)
							->first();
							if($ApplicationStatusList != '')
							{
							$data = array();
							$dataTitle = array();
							//values
							$data['CustomerName'] = $ApplicationStatusList->customer_name;
							$data['CustomerMobile'] = $ApplicationStatusList->customer_mobile;
							$data['sellerId'] = $ApplicationStatusList->seller_id;
							$data['Status'] = $ApplicationStatusList->form_status;
							$data['remarks'] = $ApplicationStatusList->remarks;
							$data['cardType'] = $ApplicationStatusList->card_type;
							$data['lastComment'] = $ApplicationStatusList->last_comment;
							$data['submissionDate'] = $ApplicationStatusList->submission_date;
							$data['applicationId'] = $ApplicationStatusList->applicationid;
							$data['refNo'] = $ApplicationStatusList->ref_no;
							$data['bureau_score'] = $ApplicationStatusList->bureau_score;
							$data['mrs_score'] = $ApplicationStatusList->mrs_score;
							$data['bureau_segmentation'] = $ApplicationStatusList->bureau_segmentation;
							$data['Team'] = $ApplicationStatusList->team;
							$data['employer_name'] = $ApplicationStatusList->employer_name;
							
							
							//values
							$result['responseCode'] = 200;
							$result['Data'] = $data;
							//valuesTitle
							$dataTitle[0]['Title'] = 'Customer Name';
							$dataTitle[0]['Key'] = 'CustomerName';
							
							$dataTitle[1]['Title'] = 'Customer Mobile';
							$dataTitle[1]['Key'] = 'CustomerMobile';
							
							$dataTitle[2]['Title'] = 'Status';
							$dataTitle[2]['Key'] = 'Status';
							
							$dataTitle[3]['Title'] = 'Remark';
							$dataTitle[3]['Key'] = 'remarks';
							
							$dataTitle[4]['Title'] = 'Card Type';
							$dataTitle[4]['Key'] = 'cardType';
							
							$dataTitle[5]['Title'] = 'Last Comment';
							$dataTitle[5]['Key'] = 'lastComment';
							
							$dataTitle[6]['Title'] = 'Submission Date';
							$dataTitle[6]['Key'] = 'submissionDate';
							
							$dataTitle[7]['Title'] = 'Application Date';
							$dataTitle[7]['Key'] = 'applicationId';
							
							$dataTitle[8]['Title'] = 'Ref No';
							$dataTitle[8]['Key'] = 'refNo';
							
							$dataTitle[9]['Title'] = 'Team';
							$dataTitle[9]['Key'] = 'Team';
							
							$dataTitle[10]['Title'] = 'seller Id';
							$dataTitle[10]['Key'] = 'sellerId';
							
							$dataTitle[11]['Title'] = 'Bureau Score';
							$dataTitle[11]['Key'] = 'bureau_score';
							
							$dataTitle[12]['Title'] = 'Mrs Score';
							$dataTitle[12]['Key'] = 'mrs_score';
							
							$dataTitle[13]['Title'] = 'Bureau Segmentation';
							$dataTitle[13]['Key'] = 'bureau_segmentation';
							
							$dataTitle[14]['Title'] = 'Employer Name';
							$dataTitle[14]['Key'] = 'employer_name';
							
							//valuesTitle
							$result['DataTitle'] = $dataTitle;
							//colorcode
							$result['colorCode'] = $this->checkcolorCodeMashreq($ApplicationStatusList->ref_no,$ApplicationStatusList->form_status);
							
							$result['message'] = "Successfull";
							}
							else
							{
									$result['responseCode'] = 202;
									$result['message'] = "Reference No. not found.";
							}
				}
				else if($deptId == 49)
				{
					
					$totalRecordsBooked = DB::table('department_form_parent_entry')
							->join('CBD_bank_mis', 'department_form_parent_entry.ref_no', '=', 'CBD_bank_mis.ref_no')
							
							->where("department_form_parent_entry.form_id",2)
							->where("department_form_parent_entry.emp_id",$empId)
							->where("department_form_parent_entry.ref_no",$AppId)
							->first();
							if($totalRecordsBooked != '')
							{
								$data = array();
								$data['SubmissionDate'] = date("Y-m-d",strtotime($totalRecordsBooked->application_date));
								$data['CustomerName'] = $totalRecordsBooked->customer_name;
								$data['CustomerMobile'] = $totalRecordsBooked->customer_mobile;
								$data['refNo'] = $totalRecordsBooked->ref_no;
								$data['Team'] = $totalRecordsBooked->team;
								$data['CardType'] = $this->getCardType($totalRecordsBooked->ref_no);
								$data['Remarks'] = $totalRecordsBooked->remarks;
								$data['Status'] = $this->checkCBDStatus($totalRecordsBooked->ref_no);
								$data['approvalDate'] = $totalRecordsBooked->approval_date;
								$data['colorCode'] = $this->checkcolorCodeCBD($totalRecordsBooked->ref_no);
								$result['responseCode'] = 200;
								$result['colorCode'] = $this->checkcolorCodeCBD($totalRecordsBooked->ref_no);
							
								$result['Data'] = $data;
								//valuesTitle
								$dataTitle[0]['Title'] = 'Submission Date';
								$dataTitle[0]['Key'] = 'SubmissionDate';
								
								$dataTitle[1]['Title'] = 'Customer Name';
								$dataTitle[1]['Key'] = 'CustomerName';
								
								$dataTitle[2]['Title'] = 'Customer Mobile';
								$dataTitle[2]['Key'] = 'CustomerMobile';
								
								$dataTitle[3]['Title'] = 'Team';
								$dataTitle[3]['Key'] = 'Team';
								
								$dataTitle[4]['Title'] = 'Card Type';
								$dataTitle[4]['Key'] = 'CardType';
								
								$dataTitle[5]['Title'] = 'Remarks';
								$dataTitle[5]['Key'] = 'Remarks';
								
								$dataTitle[6]['Title'] = 'Status';
								$dataTitle[6]['Key'] = 'Status';
								
								$dataTitle[7]['Title'] = 'Approval Date';
								$dataTitle[7]['Key'] = 'approvalDate';
								
								$dataTitle[8]['Title'] = 'Ref No';
								$dataTitle[8]['Key'] = 'refNo';
								
								//valuesTitle
								$result['DataTitle'] = $dataTitle;
								$result['message'] = "Successfull";
							}
							else
							{
								$result['responseCode'] = 202;
								$result['message'] = "Reference No. not found.";
							}
				}
				else
				{
						$result['responseCode'] = 202;
						$result['message'] = "Reference No. not found.";
				}
				
					
				
				
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
	}
	
public function EmployeeSalesAgent(Request $request)
{
	$requestParameters = $request->input();
		
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' && isset($requestParameters['pageLimit']) && $requestParameters['pageLimit'] != '' && isset($requestParameters['pageNo']) && $requestParameters['pageNo'] != '' && isset($requestParameters['submisionDateFrom']) && $requestParameters['submisionDateFrom'] != '' && isset($requestParameters['submissionDateTo']) && $requestParameters['submissionDateTo'] != '')
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		$pageLimit = $requestParameters['pageLimit'];
		$pageNo = $requestParameters['pageNo'];
		$submisionDateFrom = $requestParameters['submisionDateFrom'];
		$submissionDateTo = $requestParameters['submissionDateTo'];
		$cardType = $requestParameters['cardType'];
		$isNumeric = $requestParameters['isNumeric'];
		$searchText = $requestParameters['searchText'];
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				
				$empDetails = Employee_details::where("emp_id",$empId)->first();
				
				if($empDetails->job_function != 2)
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
						
				}
				else
				{
				/*
				*get Employee Sales Details
				*/
				
				if($empDetails->dept_id == 36)
				{
					$whereRaw = 'department_form_parent_entry.form_id in (1,5)';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.submission_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and department_form_parent_entry.submission_date <= '".$submissionDateTo."'";
					}
					
					if($cardType != 'All' && $cardType != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.card_type ='".trim($cardType)."'";
					}
					if($searchText != 'All')
					{
						if($isNumeric == 'true')
						{
							$whereRaw .= " and department_form_parent_entry.customer_mobile like '%".trim($searchText)."%'";
						}
						else
						{
							$whereRaw .= " and department_form_parent_entry.customer_name like '%".trim($searchText)."%'";
						}
					}
					// echo $whereRaw;exit; 
					$deptMods = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->orderBy("submission_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					
					$totalRecordsSub = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count();
					$totalRecordsBooked = DB::table('department_form_parent_entry')
							->join('mashreq_login_data', 'mashreq_login_data.ref_no', '=', 'department_form_parent_entry.ref_no')
							
							->where('mashreq_login_data.booking_status',1) 
							->whereIn("department_form_parent_entry.form_id",array(1,5))
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->get()->count();
					$totalRecordsRejected = DepartmentFormEntry::where("emp_id",$empId)->whereIn("department_form_parent_entry.form_id",array(1,5))->whereRaw($whereRaw)->whereIn("form_status",array('Declined','Terminated','Decline','decline','Reject To Seller','Rejected'))->get()->count(); 
					
				
					/* echo "<pre>";
					print_r($deptMod);
					exit; */
					$response['Submissions'] = array();
					$i =0;
					foreach($deptMods as $mod)
					{
						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->submission_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['Team'] = $mod->team;
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $mod->bureau_score;
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $mod->card_type;
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
						$response['Submissions'][$i]['Remarks'] = $mod->remarks;
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
					
						$response['Submissions'][$i]['BookedFlag'] = $this->checkBooked($mod->ref_no);
						$response['Submissions'][$i]['DeparmentId'] = 36;
						$response['Submissions'][$i]['DeparmentName'] = 'Mashreq';
						$response['Submissions'][$i]['channelCbd'] = $mod->channel_cbd;
						$response['Submissions'][$i]['statusAECBCbd'] = $mod->status_AECB_cbd;
						$response['Submissions'][$i]['approvalDate'] = $mod->approval_date;
						
						
						if( $mod->form_id == 5)
						{
							$response['Submissions'][$i]['Status'] = 'Pre Screen Rejected';
						$response['Submissions'][$i]['colorCode'] = '#fd1a16';
						}
						else
						{
							
						$response['Submissions'][$i]['Status'] = $mod->form_status;
						$response['Submissions'][$i]['colorCode'] = $this->checkcolorCodeMashreq($mod->ref_no,$mod->form_status);
						}
						/*
						*login Data
						*/
						$response['Submissions'][$i]['Nationality']  = NULL;
						$response['Submissions'][$i]['Salary']  = NULL;
					
					
						$response['Submissions'][$i]['EmployeeCategoryDesc']  = NULL;
						$response['Submissions'][$i]['bureauScore']  = NULL;
					
						$response['Submissions'][$i]['bureauSegmentation']   = NULL;
				
						$response['Submissions'][$i]['minStartdate']  = NULL;
						$response['Submissions'][$i]['applicationStatus']  = NULL;
						$response['Submissions'][$i]['cdaDescision']  = NULL;
						$response['Submissions'][$i]['cif']  = NULL;
						$response['Submissions'][$i]['sellerChannelName']  = NULL;
						$response['Submissions'][$i]['dateofdisbursal']  = NULL;
						$loginData = MashreqLoginMIS::where("ref_no",$mod->ref_no)->first();
			if($loginData != '')
			{
					$response['Submissions'][$i]['Nationality']  = $loginData->nationality;
					$response['Submissions'][$i]['Salary']  = $loginData->cdafinalsalary;
					
					
					$response['Submissions'][$i]['EmployeeCategoryDesc']  = $loginData->employee_category_desc;
					$response['Submissions'][$i]['bureauScore']  = $loginData->bureau_score;
					
					$response['Submissions'][$i]['bureauSegmentation']   = $loginData->bureau_segmentation;
				
					$response['Submissions'][$i]['minStartdate']  = $loginData->min_startdate;
					$response['Submissions'][$i]['applicationStatus']  =$loginData->application_status;
					$response['Submissions'][$i]['cdaDescision']  =$loginData->cda_descision;
					$response['Submissions'][$i]['cif']  =$loginData->cif;
					$response['Submissions'][$i]['sellerChannelName']  =$loginData->seller_channel_name;
					$response['Submissions'][$i]['dateofdisbursal']  =$loginData->dateofdisbursal;
					
			}
						/*
						*login Data
						*/
						$i++;
					}
					/*
					*get list of cardType
					*/
					$cardsType = DepartmentFormEntry::select("card_type as CardType")->whereNotNull('card_type')->groupBy("card_type")->get();
					/* echo "<pre>";
					print_r($cardsType);
					exit; */
					/*
					*get list of cardType
					*/
					$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['totalRecordsSubmissions'] = $totalRecordsSub;
						$result['totalRecordsBooked'] = $totalRecordsBooked;
						$result['totalRecordsRejected'] = $totalRecordsRejected;
							$result['ListOfCardType'] = $cardsType;
						$result['Values'] = $response;
				}
				else if($empDetails->dept_id == 49)
				{
					$whereRaw = 'department_form_parent_entry.form_id = 2';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.application_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and department_form_parent_entry.application_date <= '".$submissionDateTo."'";
					}
					
					if($cardType != 'All' && $cardType != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.card_type ='".trim($cardType)."'";
					}
					if($searchText != 'All')
					{
						if($isNumeric == 'true')
						{
							$whereRaw .= " and department_form_parent_entry.customer_mobile like '%".trim($searchText)."%'";
						}
						else
						{
							$whereRaw .= " and department_form_parent_entry.customer_name like '%".trim($searchText)."%'";
						}
					}
					// echo $whereRaw;exit; 
					$deptMods = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->orderBy("application_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					$totalRecordsSub = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count();
					
					$totalRecordsBooked = DB::table('department_form_parent_entry')
							->join('CBD_bank_mis', 'department_form_parent_entry.ref_no', '=', 'CBD_bank_mis.ref_no')
							->whereIn("CBD_bank_mis.Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))
							->where("department_form_parent_entry.form_id",2)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->get()->count();
							
					$totalRecordsRejected = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",2)->whereIn("form_status",array("Missing(Terminated)","Application submission failed","Archive on Reject","Declined","Terminated","Terminated by user"))->whereRaw($whereRaw)->get()->count(); 
					
					/* echo "<pre>";
					print_r($deptMod);
					exit; */
					$response['Submissions'] = array();
					$i =0;
					foreach($deptMods as $mod)
					{
						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->application_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['Team'] = $mod->team;
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $mod->bureau_score;
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $this->getCardType($mod->ref_no);
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
						$response['Submissions'][$i]['Remarks'] = $mod->remarks;
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
						$response['Submissions'][$i]['Status'] = $this->checkCBDStatus($mod->ref_no);
						$response['Submissions'][$i]['BookedFlag'] = $this->checkBookedCBD($mod->ref_no);
						$response['Submissions'][$i]['DeparmentId'] = 49;
						$response['Submissions'][$i]['DeparmentName'] = 'CBD';
							$response['Submissions'][$i]['channelCbd'] = $mod->channel_cbd;
						$response['Submissions'][$i]['statusAECBCbd'] = $mod->status_AECB_cbd;
						$response['Submissions'][$i]['approvalDate'] = $mod->approval_date;
							$response['Submissions'][$i]['colorCode'] = $this->checkcolorCodeCBD($mod->ref_no);
							$response['Submissions'][$i]['Nationality']  = NULL;
						$response['Submissions'][$i]['Salary']  = NULL;
					
					
						$response['Submissions'][$i]['EmployeeCategoryDesc']  = NULL;
						$response['Submissions'][$i]['bureauScore']  = NULL;
					
						$response['Submissions'][$i]['bureauSegmentation']   = NULL;
				
							$response['Submissions'][$i]['minStartdate']  = NULL;
						$response['Submissions'][$i]['applicationStatus']  = NULL;
						$response['Submissions'][$i]['cdaDescision']  = NULL;
						$response['Submissions'][$i]['cif']  = NULL;
						$response['Submissions'][$i]['sellerChannelName']  = NULL;
						$response['Submissions'][$i]['dateofdisbursal']  = NULL;
						$i++;
					}
					/*
					*get list of cardType
					*/
					$cardsType = CBDBankMis::select("card_type as CardType")->whereNotNull('card_type')->where('card_type',"!=",'0')->groupBy("card_type")->get();
					/* echo "<pre>";
					print_r($cardsType);
					exit; */
					/*
					*get list of cardType
					*/
					$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['totalRecordsSubmissions'] = $totalRecordsSub;
						$result['totalRecordsBooked'] = $totalRecordsBooked;
						$result['totalRecordsRejected'] = $totalRecordsRejected;
							$result['ListOfCardType'] = $cardsType;
						$result['Values'] = $response;
				}
				else
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
				}
				
				
						/*
				*get Employee Sales Details
				*/
				}
				
				
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
}




public function EmployeeSalesAgentBooked(Request $request)
{
	$requestParameters = $request->input();
		
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' && isset($requestParameters['pageLimit']) && $requestParameters['pageLimit'] != '' && isset($requestParameters['pageNo']) && $requestParameters['pageNo'] != '' && isset($requestParameters['submisionDateFrom']) && $requestParameters['submisionDateFrom'] != '' && isset($requestParameters['submissionDateTo']) && $requestParameters['submissionDateTo'] != '')
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		$pageLimit = $requestParameters['pageLimit'];
		$pageNo = $requestParameters['pageNo'];
		$submisionDateFrom = $requestParameters['submisionDateFrom'];
		$submissionDateTo = $requestParameters['submissionDateTo'];
		$cardType = $requestParameters['cardType'];
		$isNumeric = $requestParameters['isNumeric'];
		$searchText = $requestParameters['searchText'];
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				
				$empDetails = Employee_details::where("emp_id",$empId)->first();
				
				if($empDetails->job_function != 2)
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
						
				}
				else
				{
				/*
				*get Employee Sales Details
				*/
				
				if($empDetails->dept_id == 36)
				{
					$whereRaw = 'department_form_parent_entry.form_id in (1,5)';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.submission_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and department_form_parent_entry.submission_date <= '".$submissionDateTo."'";
					}
					
					if($cardType != 'All' && $cardType != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.card_type ='".trim($cardType)."'";
					}
					if($searchText != 'All')
					{
						if($isNumeric == 'true')
						{
							$whereRaw .= " and department_form_parent_entry.customer_mobile like '%".trim($searchText)."%'";
						}
						else
						{
							$whereRaw .= " and department_form_parent_entry.customer_name like '%".trim($searchText)."%'";
						}
					}
					/* echo $whereRaw;exit; */
					//$deptMods = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->orderBy("submission_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					
					/* $totalRecords = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count(); */
					$deptMods = DB::table('department_form_parent_entry')
							->join('mashreq_login_data', 'mashreq_login_data.ref_no', '=', 'department_form_parent_entry.ref_no')
							
							->where('mashreq_login_data.booking_status',1) 
							->where("department_form_parent_entry.form_id",1)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->orderBy("submission_date","DESC")->skip($pageNo)->take($pageLimit)->get();
				
				
					
					$totalRecordsSub = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count();
					$totalRecordsBooked = DB::table('department_form_parent_entry')
							->join('mashreq_login_data', 'mashreq_login_data.ref_no', '=', 'department_form_parent_entry.ref_no')
							
							->where('mashreq_login_data.booking_status',1) 
							->where("department_form_parent_entry.form_id",1)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->get()->count();
					$totalRecordsRejected = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",1)->whereRaw($whereRaw)->whereIn("form_status",array('Declined','Terminated','Decline','decline','Reject To Seller','Rejected'))->get()->count(); 
					
				
				
					 /* echo "<pre>";
					print_r($deptMods->count());
					exit;  */
					/* echo $deptMods->count();exit; */
					$response['Submissions'] = array();
					$i =0;
					foreach($deptMods as $mod)
					{
						
						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->submission_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['Team'] = $mod->team;
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $mod->bureau_score;
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $mod->card_type;
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
						$response['Submissions'][$i]['Remarks'] = $mod->remarks;
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
						$response['Submissions'][$i]['Status'] = $mod->form_status;
						$response['Submissions'][$i]['BookedFlag'] = $this->checkBooked($mod->ref_no);
						$response['Submissions'][$i]['DeparmentId'] = 36;
						$response['Submissions'][$i]['DeparmentName'] = 'Mashreq';
							$response['Submissions'][$i]['channelCbd'] = $mod->channel_cbd;
						$response['Submissions'][$i]['statusAECBCbd'] = $mod->status_AECB_cbd;
						$response['Submissions'][$i]['approvalDate'] = $mod->approval_date;
						$response['Submissions'][$i]['colorCode'] = $this->checkcolorCodeMashreq($mod->ref_no,$mod->form_status);
						/*
						*login Data
						*/
						$response['Submissions'][$i]['Nationality']  = NULL;
						$response['Submissions'][$i]['Salary']  = NULL;
					
					
						$response['Submissions'][$i]['EmployeeCategoryDesc']  = NULL;
						$response['Submissions'][$i]['bureauScore']  = NULL;
					
						$response['Submissions'][$i]['bureauSegmentation']   = NULL;
				
						$response['Submissions'][$i]['minStartdate']  = NULL;
						$response['Submissions'][$i]['applicationStatus']  = NULL;
						$response['Submissions'][$i]['cdaDescision']  = NULL;
						$response['Submissions'][$i]['cif']  = NULL;
						$response['Submissions'][$i]['sellerChannelName']  = NULL;
						$response['Submissions'][$i]['dateofdisbursal']  = NULL;
						$loginData = MashreqLoginMIS::where("ref_no",$mod->ref_no)->first();
			if($loginData != '')
			{
					$response['Submissions'][$i]['Nationality']  = $loginData->nationality;
					$response['Submissions'][$i]['Salary']  = $loginData->cdafinalsalary;
					
					
					$response['Submissions'][$i]['EmployeeCategoryDesc']  = $loginData->employee_category_desc;
					$response['Submissions'][$i]['bureauScore']  = $loginData->bureau_score;
					
					$response['Submissions'][$i]['bureauSegmentation']   = $loginData->bureau_segmentation;
				
					$response['Submissions'][$i]['minStartdate']  = $loginData->min_startdate;
					$response['Submissions'][$i]['applicationStatus']  =$loginData->application_status;
					$response['Submissions'][$i]['cdaDescision']  =$loginData->cda_descision;
					$response['Submissions'][$i]['cif']  =$loginData->cif;
					$response['Submissions'][$i]['sellerChannelName']  =$loginData->seller_channel_name;
					$response['Submissions'][$i]['dateofdisbursal']  = $loginData->dateofdisbursal;
					
			}
						/*
						*login Data
						*/
						$i++;
					
					}
					/*
					*get list of cardType
					*/
					$cardsType = DepartmentFormEntry::select("card_type as CardType")->whereNotNull('card_type')->groupBy("card_type")->get();
					/* echo "<pre>";
					print_r($response['Submissions']);
					exit; */ 
					/*
					*get list of cardType
					*/
					$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['totalRecordsSubmissions'] = $totalRecordsSub;
						$result['totalRecordsBooked'] = $totalRecordsBooked;
						$result['totalRecordsRejected'] = $totalRecordsRejected;
							$result['ListOfCardType'] = $cardsType;
						$result['Values'] = $response;
				}
				else if($empDetails->dept_id == 49)
				{
					$whereRaw = 'department_form_parent_entry.form_id = 2';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.application_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and department_form_parent_entry.application_date <= '".$submissionDateTo."'";
					}
					
					if($cardType != 'All' && $cardType != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.card_type ='".trim($cardType)."'";
					}
					if($searchText != 'All')
					{
						if($isNumeric == 'true')
						{
							$whereRaw .= " and department_form_parent_entry.customer_mobile like '%".trim($searchText)."%'";
						}
						else
						{
							$whereRaw .= " and department_form_parent_entry.customer_name like '%".trim($searchText)."%'";
						}
					}
					// echo $whereRaw;exit; 
					//$deptMods = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->orderBy("application_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					/* $totalRecords = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count(); */
					$deptMods = DB::table('department_form_parent_entry')
							->join('CBD_bank_mis', 'department_form_parent_entry.ref_no', '=', 'CBD_bank_mis.ref_no')
							->whereIn("CBD_bank_mis.Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))
							->where("department_form_parent_entry.form_id",2)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->orderBy("application_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					
						$totalRecordsSub = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count();
					
					$totalRecordsBooked = DB::table('department_form_parent_entry')
							->join('CBD_bank_mis', 'department_form_parent_entry.ref_no', '=', 'CBD_bank_mis.ref_no')
							->whereIn("CBD_bank_mis.Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))
							->where("department_form_parent_entry.form_id",2)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->get()->count();
							
					$totalRecordsRejected = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",2)->whereIn("form_status",array("Missing(Terminated)","Application submission failed","Archive on Reject","Declined","Terminated","Terminated by user"))->whereRaw($whereRaw)->get()->count(); 
					
				/* echo $totalRecords;exit; */
					/* echo "<pre>";
					print_r($deptMod);
					exit; */
					$response['Submissions'] = array();
					$i =0;
					foreach($deptMods as $mod)
					{
						
						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->application_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['Team'] = $mod->team;
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $mod->bureau_score;
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $this->getCardType($mod->ref_no);
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
						$response['Submissions'][$i]['Remarks'] = $mod->remarks;
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
						$response['Submissions'][$i]['Status'] = $this->checkCBDStatus($mod->ref_no);
						$response['Submissions'][$i]['BookedFlag'] = $this->checkBookedCBD($mod->ref_no);
						$response['Submissions'][$i]['DeparmentId'] = 49;
						$response['Submissions'][$i]['DeparmentName'] = 'CBD';
							$response['Submissions'][$i]['channelCbd'] = $mod->channel_cbd;
						$response['Submissions'][$i]['statusAECBCbd'] = $mod->status_AECB_cbd;
						$response['Submissions'][$i]['approvalDate'] = $mod->approval_date;
							$response['Submissions'][$i]['colorCode'] = $this->checkcolorCodeCBD($mod->ref_no);
							$response['Submissions'][$i]['Nationality']  = NULL;
						$response['Submissions'][$i]['Salary']  = NULL;
					
					
						$response['Submissions'][$i]['EmployeeCategoryDesc']  = NULL;
						$response['Submissions'][$i]['bureauScore']  = NULL;
					
						$response['Submissions'][$i]['bureauSegmentation']   = NULL;
				
							$response['Submissions'][$i]['minStartdate']  = NULL;
						$response['Submissions'][$i]['applicationStatus']  = NULL;
						$response['Submissions'][$i]['cdaDescision']  = NULL;
						$response['Submissions'][$i]['cif']  = NULL;
						$response['Submissions'][$i]['sellerChannelName']  = NULL;
						$response['Submissions'][$i]['dateofdisbursal']  = NULL;
						$i++;
						
					}
					/*
					*get list of cardType
					*/
					$cardsType = CBDBankMis::select("card_type as CardType")->whereNotNull('card_type')->where('card_type',"!=",'0')->groupBy("card_type")->get();
					/* echo "<pre>";
					print_r($cardsType);
					exit; */
					/*
					*get list of cardType
					*/
					$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['totalRecordsSubmissions'] = $totalRecordsSub;
						$result['totalRecordsBooked'] = $totalRecordsBooked;
						$result['totalRecordsRejected'] = $totalRecordsRejected;
							$result['ListOfCardType'] = $cardsType;
						$result['Values'] = $response;
				}
				else
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
				}
				
				
						/*
				*get Employee Sales Details
				*/
				}
				
				
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
}




public function EmployeeSalesAgentRejected(Request $request)
{
	$requestParameters = $request->input();
		
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' && isset($requestParameters['pageLimit']) && $requestParameters['pageLimit'] != '' && isset($requestParameters['pageNo']) && $requestParameters['pageNo'] != '' && isset($requestParameters['submisionDateFrom']) && $requestParameters['submisionDateFrom'] != '' && isset($requestParameters['submissionDateTo']) && $requestParameters['submissionDateTo'] != '')
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		$pageLimit = $requestParameters['pageLimit'];
		$pageNo = $requestParameters['pageNo'];
		$submisionDateFrom = $requestParameters['submisionDateFrom'];
		$submissionDateTo = $requestParameters['submissionDateTo'];
		$cardType = $requestParameters['cardType'];
		$isNumeric = $requestParameters['isNumeric'];
		$searchText = $requestParameters['searchText'];
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				
				$empDetails = Employee_details::where("emp_id",$empId)->first();
				
				if($empDetails->job_function != 2)
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
						
				}
				else
				{
					
				/*
				*get Employee Sales Details
				*/
				
				if($empDetails->dept_id == 36)
				{
					$whereRaw = 'department_form_parent_entry.form_id in (1,5)';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.submission_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and department_form_parent_entry.submission_date <= '".$submissionDateTo."'";
					}
					
					if($cardType != 'All' && $cardType != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.card_type ='".trim($cardType)."'";
					}
					if($searchText != 'All')
					{
						if($isNumeric == 'true')
						{
							$whereRaw .= " and department_form_parent_entry.customer_mobile like '%".trim($searchText)."%'";
						}
						else
						{
							$whereRaw .= " and department_form_parent_entry.customer_name like '%".trim($searchText)."%'";
						}
					}
					/* echo $whereRaw;exit; */
					$deptMods = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",1)->whereRaw($whereRaw)->whereIn("form_status",array('Declined','Terminated','Decline','decline','Reject To Seller','Rejected'))->orderBy("submission_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					
					$totalRecordsSub = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count();
					$totalRecordsBooked = DB::table('department_form_parent_entry')
							->join('mashreq_login_data', 'mashreq_login_data.ref_no', '=', 'department_form_parent_entry.ref_no')
							
							->where('mashreq_login_data.booking_status',1) 
							->where("department_form_parent_entry.form_id",1)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->get()->count();
					$totalRecordsRejected = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",1)->whereRaw($whereRaw)->whereIn("form_status",array('Declined','Terminated','Decline','decline','Reject To Seller','Rejected'))->get()->count(); 
					
				
				
				
					/* echo "<pre>";
					print_r($deptMod);
					exit; */
					$response['Submissions'] = array();
					$i =0;
					foreach($deptMods as $mod)
					{
						
						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->submission_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['Team'] = $mod->team;
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $mod->bureau_score;
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $mod->card_type;
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
						$response['Submissions'][$i]['Remarks'] = $mod->remarks;
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
						$response['Submissions'][$i]['Status'] = $mod->form_status;
						$response['Submissions'][$i]['BookedFlag'] = '';
						$response['Submissions'][$i]['DeparmentId'] = 36;
						$response['Submissions'][$i]['DeparmentName'] = 'Mashreq';
							$response['Submissions'][$i]['channelCbd'] = $mod->channel_cbd;
						$response['Submissions'][$i]['statusAECBCbd'] = $mod->status_AECB_cbd;
						$response['Submissions'][$i]['approvalDate'] = $mod->approval_date;
						$response['Submissions'][$i]['colorCode'] = $this->checkcolorCodeMashreq($mod->ref_no,$mod->form_status);
						/*
						*login Data
						*/
						$response['Submissions'][$i]['Nationality']  = NULL;
						$response['Submissions'][$i]['Salary']  = NULL;
					
					
						$response['Submissions'][$i]['EmployeeCategoryDesc']  = NULL;
						$response['Submissions'][$i]['bureauScore']  = NULL;
					
						$response['Submissions'][$i]['bureauSegmentation']   = NULL;
				$response['Submissions'][$i]['minStartdate']  = NULL;
						$response['Submissions'][$i]['applicationStatus']  = NULL;
						$response['Submissions'][$i]['cdaDescision']  = NULL;
						$response['Submissions'][$i]['cif']  = NULL;
						$response['Submissions'][$i]['sellerChannelName']  = NULL;
						$response['Submissions'][$i]['dateofdisbursal']  = NULL;
						$loginData = MashreqLoginMIS::where("ref_no",$mod->ref_no)->first();
			if($loginData != '')
			{
					$response['Submissions'][$i]['Nationality']  = $loginData->nationality;
					$response['Submissions'][$i]['Salary']  = $loginData->cdafinalsalary;
					
					
					$response['Submissions'][$i]['EmployeeCategoryDesc']  = $loginData->employee_category_desc;
					$response['Submissions'][$i]['bureauScore']  = $loginData->bureau_score;
					
					$response['Submissions'][$i]['bureauSegmentation']   = $loginData->bureau_segmentation;
				
					$response['Submissions'][$i]['minStartdate']  = $loginData->min_startdate;
					$response['Submissions'][$i]['applicationStatus']  =$loginData->application_status;
					$response['Submissions'][$i]['cdaDescision']  =$loginData->cda_descision;
					$response['Submissions'][$i]['cif']  =$loginData->cif;
					$response['Submissions'][$i]['sellerChannelName']  =$loginData->seller_channel_name;
					$response['Submissions'][$i]['dateofdisbursal']  = $loginData->dateofdisbursal;
					
			}
						/*
						*login Data
						*/
						$i++;
					
					}
					/*
					*get list of cardType
					*/
					$cardsType = DepartmentFormEntry::select("card_type as CardType")->whereNotNull('card_type')->groupBy("card_type")->get();
					/* echo "<pre>";
					print_r($cardsType);
					exit; */
					/*
					*get list of cardType
					*/
					$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['totalRecordsSubmissions'] = $totalRecordsSub;
						$result['totalRecordsBooked'] = $totalRecordsBooked;
						$result['totalRecordsRejected'] = $totalRecordsRejected;
							$result['ListOfCardType'] = $cardsType;
						$result['Values'] = $response;
				}
				else if($empDetails->dept_id == 49)
				{
					$whereRaw = 'department_form_parent_entry.form_id = 2';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.application_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and department_form_parent_entry.application_date <= '".$submissionDateTo."'";
					}
					
					if($cardType != 'All' && $cardType != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.card_type ='".trim($cardType)."'";
					}
					if($searchText != 'All')
					{
						if($isNumeric == 'true')
						{
							$whereRaw .= " and department_form_parent_entry.customer_mobile like '%".trim($searchText)."%'";
						}
						else
						{
							$whereRaw .= " and department_form_parent_entry.customer_name like '%".trim($searchText)."%'";
						}
					}
					/* echo $whereRaw;exit; */
					$deptMods = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",2)->whereIn("form_status",array("Missing(Terminated)","Application submission failed","Archive on Reject","Declined","Terminated","Terminated by user"))->whereRaw($whereRaw)->orderBy("application_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					 
					
						$totalRecordsSub = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count();
					
					$totalRecordsBooked = DB::table('department_form_parent_entry')
							->join('CBD_bank_mis', 'department_form_parent_entry.ref_no', '=', 'CBD_bank_mis.ref_no')
							->whereIn("CBD_bank_mis.Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))
							->where("department_form_parent_entry.form_id",2)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->get()->count();
							
					$totalRecordsRejected = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",2)->whereIn("form_status",array("Missing(Terminated)","Application submission failed","Archive on Reject","Declined","Terminated","Terminated by user"))->whereRaw($whereRaw)->get()->count(); 
					
						
				/* echo $totalRecords;exit; */
					/*  echo "<pre>";
					print_r($deptMods);
					exit; */ 
					$response['Submissions'] = array();
					$i =0;
					foreach($deptMods as $mod)
					{

						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->application_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['Team'] = $mod->team;
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $mod->bureau_score;
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $this->getCardType($mod->ref_no);
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
						$response['Submissions'][$i]['Remarks'] = $mod->remarks;
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
						$response['Submissions'][$i]['Status'] = $this->checkCBDStatus($mod->ref_no);
						$response['Submissions'][$i]['BookedFlag'] = '';
						$response['Submissions'][$i]['DeparmentId'] = 49;
						$response['Submissions'][$i]['DeparmentName'] = 'CBD';
							$response['Submissions'][$i]['channelCbd'] = $mod->channel_cbd;
						$response['Submissions'][$i]['statusAECBCbd'] = $mod->status_AECB_cbd;
						$response['Submissions'][$i]['approvalDate'] = $mod->approval_date;
							$response['Submissions'][$i]['colorCode'] = $this->checkcolorCodeCBD($mod->ref_no);
							$response['Submissions'][$i]['Nationality']  = NULL;
						$response['Submissions'][$i]['Salary']  = NULL;
					
					
						$response['Submissions'][$i]['EmployeeCategoryDesc']  = NULL;
						$response['Submissions'][$i]['bureauScore']  = NULL;
					
						$response['Submissions'][$i]['bureauSegmentation']   = NULL;
				
						$response['Submissions'][$i]['minStartdate']  = NULL;
						$response['Submissions'][$i]['applicationStatus']  = NULL;
						$response['Submissions'][$i]['cdaDescision']  = NULL;
						$response['Submissions'][$i]['cif']  = NULL;
						$response['Submissions'][$i]['sellerChannelName']  = NULL;
						$i++;
						
					}
					/*
					*get list of cardType
					*/
					$cardsType = CBDBankMis::select("card_type as CardType")->whereNotNull('card_type')->where('card_type',"!=",'0')->groupBy("card_type")->get();
					/* echo "<pre>";
					print_r($cardsType);
					exit; */
					/*
					*get list of cardType
					*/
					$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['totalRecordsSubmissions'] = $totalRecordsSub;
						$result['totalRecordsBooked'] = $totalRecordsBooked;
						$result['totalRecordsRejected'] = $totalRecordsRejected;
							$result['ListOfCardType'] = $cardsType;
						$result['Values'] = $response;
				}
				else
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
				}
				
				
						/*
				*get Employee Sales Details
				*/
				}
				
				
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
}

protected function checkcolorCodeMashreq($refNO,$status)
{
	$dataLogin = MashreqLoginMIS::where("ref_no",trim($refNO))->first();
	if($dataLogin != '')
	{
		if($dataLogin->booking_status == 1)
		{
			return "#2f29a6";
		}
		else
		{
			if($status == 'Declined' || $status == 'Terminated' || $status == 'Decline' || $status == 'decline'  || $status == 'Reject To Seller' || $status == 'Rejected')
			{
				return "#fd1a16";
			}
			else
			{
				return "#fea621";
			}
		}
		
	}
	else
	{
		if($status == 'Declined' || $status == 'Terminated' || $status == 'Decline' || $status == 'decline'  || $status == 'Reject To Seller' || $status == 'Rejected')
		{
			return "#fd1a16";
		}
		else
		{
			return "#fea621";
		}
	}
}

protected function checkBooked($refNO)
{
	$dataLogin = MashreqLoginMIS::where("ref_no",trim($refNO))->first();
	if($dataLogin != '')
	{
		if($dataLogin->booking_status == 1)
		{
			return "Booked";
		}
		else
		{
			return "";
		}
		
	}
	else
	{
		return "";
	}
}


public function EmployeeSalesManager(Request $request)
{
	
	$requestParameters = $request->input();
		
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' && isset($requestParameters['pageLimit']) && $requestParameters['pageLimit'] != '' && isset($requestParameters['pageNo']) && $requestParameters['pageNo'] != '' && isset($requestParameters['submisionDateFrom']) && $requestParameters['submisionDateFrom'] != '' && isset($requestParameters['submissionDateTo']) && $requestParameters['submissionDateTo'] != '')
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		$pageLimit = $requestParameters['pageLimit'];
		$pageNo = $requestParameters['pageNo'];
		$agents = $requestParameters['agents'];
		$submisionDateFrom = $requestParameters['submisionDateFrom'];
		$submissionDateTo = $requestParameters['submissionDateTo'];
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				
				$empDetails = Employee_details::where("emp_id",$empId)->first();
				
				if($empDetails->job_function != 3)
				{
					$result['responseCode'] = 202;
						$result['message'] = "This Employee not Permitted to access.";
						
				}
				else
				{
				/*
				*get Employee Sales Details
				*/
				$response['Submissions'] = array();
				if($empDetails->dept_id == 36)
				{
					$whereRaw = 'form_id = 1';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and submission_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and submission_date <= '".$submissionDateTo."'";
					}
					
					$salesName = $empDetails->sales_name;
					if($agents == 'All')
					{
						$deptMods = DepartmentFormEntry::where("team",$salesName)->whereRaw($whereRaw)->orderBy("submission_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					}
					else
					{
						$agentList = explode(",",$agents);
						$deptMods = DepartmentFormEntry::where("team",$salesName)->whereIn("emp_id",$agentList)->whereRaw($whereRaw)->orderBy("submission_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					}
					$i =0;
					foreach($deptMods as $mod)
					{
						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->submission_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['EmpId'] = $mod->emp_id;
						$response['Submissions'][$i]['AgentName'] = $this->agentName($mod->emp_id);
						$response['Submissions'][$i]['AgentCode'] = $this->agentCode($mod->emp_id);
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $mod->bureau_score;
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $mod->card_type;
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
						$response['Submissions'][$i]['Remarks'] = $mod->remarks;
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
						$response['Submissions'][$i]['BookedFlag'] = $this->checkBooked($mod->ref_no);
						$response['Submissions'][$i]['DeparmentId'] = 36;
						$response['Submissions'][$i]['DeparmentName'] = 'Mashreq';
						$i++;
					}
					
					
				$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['Values'] = $response;
						/*
				*get Employee Sales Details
				*/
				}
				else if($empDetails->dept_id == 49)
				{
					$whereRaw = 'form_id = 2';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and application_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and application_date <= '".$submissionDateTo."'";
					}
					
					$salesName = $empDetails->sales_name;
					if($agents == 'All')
					{
						$deptMods = DepartmentFormEntry::where("team",$salesName)->whereRaw($whereRaw)->orderBy("application_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					}
					else
					{
						$agentList = explode(",",$agents);
						$deptMods = DepartmentFormEntry::where("team",$salesName)->whereIn("emp_id",$agentList)->whereRaw($whereRaw)->orderBy("submission_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					}
					$i =0;
					foreach($deptMods as $mod)
					{
						
						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->application_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['EmpId'] = $mod->emp_id;
						$response['Submissions'][$i]['AgentName'] = $this->agentName($mod->emp_id);
						$response['Submissions'][$i]['AgentCode'] = $this->agentCode($mod->emp_id);
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $mod->bureau_score;
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $mod->card_type;
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
						$response['Submissions'][$i]['Remarks'] = $mod->remarks;
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
						$response['Submissions'][$i]['BookedFlag'] = $this->checkBookedCBD($mod->ref_no);
						$response['Submissions'][$i]['DeparmentId'] = 49;
						$response['Submissions'][$i]['DeparmentName'] = 'CBD';
						$response['Submissions'][$i]['colorCode'] = $this->checkcolorCodeCBD($mod->ref_no);
						$i++;
					}
				$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['Values'] = $response;
						/*
				*get Employee Sales Details
				*/
				}
				}
				
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
}

protected function agentName($empId)
{
	$empMod = Employee_details::where("emp_id",$empId)->first();
	if($empMod != '')
	{
		return $empMod->emp_name;
	}
	else
	{
		return '';
	}
}

protected function agentCode($empId)
{
	$empMod = Employee_details::where("emp_id",$empId)->first();
	if($empMod != '')
	{
		return $empMod->source_code;
	}
	else
	{
		return '';
	}
}

protected function checkBookedCBD($refNo)
{
	$thisSubmisisonBooked = CBDBankMis::where("ref_no",$refNo)->whereIn("Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))->first();
	if($thisSubmisisonBooked != '')
	{
		return "Booked";
		
	}
	else
	{
		return "";
	}
}

protected function checkcolorCodeCBD($refNo)
{
	$thisSubmisisonBooked = CBDBankMis::where("ref_no",$refNo)->whereIn("Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))->first();
	if($thisSubmisisonBooked != '')
	{
		return "#2f29a6";
		
	}
	else
	{
		$thisSubmisisonReject = CBDBankMis::where("ref_no",$refNo)->whereIn("Status",array("Missing(Terminated)","Application submission failed","Archive on Reject","Declined","Terminated","Terminated by user"))->first();
		if($thisSubmisisonReject != '')
		{
			return "#fd1a16";
		}
		else
		{
			return "#fea621";
		}
	
		
	}
}
protected function checkCBDStatus($refNo)
{
	$thisSubmisisonBooked = CBDBankMis::where("ref_no",$refNo)->first();
	if($thisSubmisisonBooked != '')
	{
		return $thisSubmisisonBooked->Status;
		
	}
	else
	{
		return "Pending";
	}
}

protected function getCardType($refNo)
{
	$thisSubmisisonBooked = CBDBankMis::where("ref_no",$refNo)->first();
	if($thisSubmisisonBooked != '')
	{
		return $thisSubmisisonBooked->card_type;
		
	}
	else
	{
		return "";
	}
}
public function appKYCPage(Request $request)
	{
		$requestParameters = $request->input();
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' )
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				
			   $result = $this->getKYCDetails($empData,$empId);
			   
					$result['responseCode'] = 200;
				$result['message'] = "Successfull";
				$result['errorMessage'] = "This field is required. You can not left blank.";
				
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
	}
	
	
protected function getKYCDetails($empData,$empId)
{
	  $kycFieldsCount = KYCProcess::where("status",1)->get()->count();
					/*
					*check for data
					*/
					$kycFields = KYCProcess::where("status",1)->get();
					$fieldsFilled = 0;
					$kycDetails = array();
				
					
					$i=0;
					foreach($kycFields as $fields)
					{
						if($fields->position  == 'Attribute')
						{
							 $code = $fields->attribute_code;
							
							$valueFound = Employee_attribute::where('emp_id',$empId)->where('attribute_code',$code)->first();
							/* echo "<pre>";
							print_r($valueFound);
							exit; */
							if($valueFound != '')
							{
								if($valueFound->attribute_values != NULL && $valueFound->attribute_values != '')
								{
								
								  
									$kycDetails['Values'][$i]['Name'] = $fields->name;
									$kycDetails['Values'][$i]['Tab'] = $this->getTabName($fields->tabName);
									$kycDetails['Values'][$i]['Type'] = $fields->type;
									$kycDetails['Values'][$i]['AttributeCode'] = $fields->attribute_code;
									$kycDetails['Values'][$i]['AttributeValue'] = $valueFound->attribute_values;
									$kycDetails['Values'][$i]['existStatus'] = 1;
									$kycDetails['Values'][$i]['required'] = $this->kycRequired($fields->attribute_code);
									$kycDetails['Values'][$i]['options'] = '';
									$kycAttributeData = Attributes::where("attribute_code",$fields->attribute_code)->first();
									if($kycAttributeData != '')
									{
										$kycDetails['Values'][$i]['options'] = json_decode($kycAttributeData->opt_option);
									}
									
									$fieldsFilled++;
								}
								else
								{
									
									$kycDetails['Values'][$i]['Name'] = $fields->name;
									$kycDetails['Values'][$i]['Tab'] = $this->getTabName($fields->tabName);
									$kycDetails['Values'][$i]['Type'] = $fields->type;
									$kycDetails['Values'][$i]['AttributeCode'] = $fields->attribute_code;
									$kycDetails['Values'][$i]['AttributeValue'] ='';
									$kycDetails['Values'][$i]['existStatus'] = 3;
										$kycDetails['Values'][$i]['required'] = $this->kycRequired($fields->attribute_code);
									$kycDetails['Values'][$i]['options'] = '';
									$kycAttributeData = Attributes::where("attribute_code",$fields->attribute_code)->first();
									if($kycAttributeData != '')
									{
										$kycDetails['Values'][$i]['options'] = json_decode($kycAttributeData->opt_option);
									}
								}
							}
							else
							{
								
									$kycDetails['Values'][$i]['Name'] = $fields->name;
									$kycDetails['Values'][$i]['Tab'] = $this->getTabName($fields->tabName);
									$kycDetails['Values'][$i]['Type'] = $fields->type;
									$kycDetails['Values'][$i]['AttributeCode'] = $fields->attribute_code;
									$kycDetails['Values'][$i]['AttributeValue'] ='';
									$kycDetails['Values'][$i]['existStatus'] = 3;
										$kycDetails['Values'][$i]['required'] = $this->kycRequired($fields->attribute_code);
									$kycDetails['Values'][$i]['options'] = '';
									$kycAttributeData = Attributes::where("attribute_code",$fields->attribute_code)->first();
									if($kycAttributeData != '')
									{
										$kycDetails['Values'][$i]['options'] = json_decode($kycAttributeData->opt_option);
									}
							}
							
							
							$i++;
						}
						
					}
					if($fieldsFilled == 0)
					{
						$CountKycP = 0;
					}
					else
					{
						$CountKycP = ($fieldsFilled/$kycFieldsCount)*100;
					}
					$CountKycPF = round($CountKycP,2);
					$kycDetails['parentValues']['Percentage'] = $CountKycPF;
					
					return $kycDetails;
}	
protected function kycRequired($code)
{
	$data = Attributes::where("attribute_code",$code)->first();
	if($data != '')
	{
		if($data->kyc_require_status == 1)
		{
			return 1;
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
protected function getTabName($_tab)
{
						if($_tab == 'p_d')
						{
							return 'Personal Details';
						}
						elseif($_tab == 'v_d')
						{						
						return 'Visa and Insurance Information';
						}
						elseif($_tab == 'c_d')
						{
						return 'Company Details';
						}
						elseif($_tab == 'b_d')
						{						
						return 'Compensation & Payroll Information';
						}
						elseif($_tab == 'deploy_d')
						{	
							return 'Deployment Information';
						}
						elseif($_tab == 'hiring_d')
						{	
							return 'Hiring Information';
						}
							
						else
						{
							return 'none';
						}
}


public function KYCTabs(Request $request)
{
	$requestParameters = $request->input();
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' )
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$result['responseCode'] = 200;
				$result['message'] = "Successfull";
			
			$result['Tabs'][] = 'Personal Details';
			$result['Tabs'][] = 'Visa and Insurance Information';
			$result['Tabs'][] = 'Company Details';
			$result['Tabs'][] = 'Compensation & Payroll Information';
			$result['Tabs'][] = 'Deployment Information';
			$result['Tabs'][] = 'Hiring Information';
			
		}
			
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
}

public function saveKyc(Request $request)
	{
		
	$requestParameters = $request->input();
	$filesParameters1 = $request->file();
	if(isset($requestParameters['values']))
	{
		$datas = $requestParameters['values'];
	}
	else
	{
		$datas = array();
	}
	if(isset($filesParameters1['attach']))
	{
		$filesParameters  = $filesParameters1['attach'];
	}
	else
	{
		$filesParameters  = array();
	}
	
	
	
	
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' )
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
				/*
				*managing Images and doc
				*/
				foreach($filesParameters as $key=>$file)
					{
						
						$filenameWithExt =  $file->getClientOriginalName();
				
						$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
						$fileExtension =$file->getClientOriginalExtension();
						
						$newFileName = $key."_".$empId.'.'.$fileExtension;
						if(file_exists(public_path('documentCollectionFiles/'.$newFileName))){

							  unlink(public_path('documentCollectionFiles/'.$newFileName));
							}  
						if($file->move(public_path('documentCollectionFiles/'), $newFileName))
						{
							
							$empAttrMod = Employee_attribute::where("emp_id",$empId)->where("attribute_code",$key)->first();
							if($empAttrMod != '')
							{
								$updateAttr = Employee_attribute::find($empAttrMod->id);
								$updateAttr->attribute_values = $newFileName;
								$updateAttr->save();
							}
							else
							{
								$deptId = Employee_attribute::where("emp_id",$empId)->first()->dept_id;
								$createAttr = new Employee_attribute();
								$createAttr->emp_id = $empId;
								$createAttr->attribute_code = $key;
								$createAttr->dept_id = $deptId;
								$createAttr->attribute_values = $newFileName;
								$createAttr->status = 1;
								$createAttr->save();
							}
							
							
							
						}
						
						
						
					
					}
					
					
				/*
				*managing Images and doc
				*/
				/*
				*saving data
				*/
				foreach($datas as $key=>$data)
					{
					if($key != 'EMP_EID_Back' && $key != 'EMP_EID_F')
							{
								$empAttrMod = Employee_attribute::where("emp_id",$empId)->where("attribute_code",$key)->first();
								if($empAttrMod != '')
								{
									$updateAttr = Employee_attribute::find($empAttrMod->id);
									$updateAttr->attribute_values = $data;
									$updateAttr->save();
								}
								else
								{
									$deptId = Employee_details::where("emp_id",$empId)->first()->dept_id;
									$createAttr = new Employee_attribute();
									$createAttr->emp_id = $empId;
									$createAttr->attribute_code = $key;
									$createAttr->dept_id = $deptId;
									$createAttr->attribute_values = $data;
									$createAttr->status = 1;
									$createAttr->save();
								}
							}
					}
				
					$result['responseCode'] = 200;
					$result['message'] = "Successfull";
					
		}
			
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
	}
	
public function AddSubmission(Request $request)
{
	$requestParameters = $request->input();
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' )
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				
			  $empDetails = Employee_details::where("emp_id",$empId)->first();
				/* echo "<pre>";
					print_r($empDetails);
					exit; */
				if($empDetails->job_function != 2)
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
						
				}
				else
				{
					if($empDetails->dept_id == 36 || $empDetails->dept_id == 49)
					{
					 if($empDetails->dept_id ==36)
					 {
					 $departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', 1)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();
					 $departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', 1)->groupby('form_section')->orderby('sort_order','ASC')->get(['form_section']);

					 }
					 else
					 {
						 $departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', 2)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get(); 
						$departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', 2)->groupby('form_section')->orderby('sort_order','ASC')->get(['form_section']);

					}
					$attributeDetails = array();
					$indexAttr =0;
					foreach($departmentFormAttributeDetails as $Attr)
					{
					 	/* echo "<pre>";
						print_r($departmentFormAttributeDetails);
						exit; */ 
						if($Attr->id != 751 && $Attr->id != 750 && $Attr->id != 190 && $Attr->id != 195 && $Attr->id != 196 )
						{
						$attributeDetails[$indexAttr]['SID'] = $Attr->id;
						$attributeDetails[$indexAttr]['sortOrder'] = $Attr->sort_order;
						$attributeId = $Attr->attribute_id;
						$attributeData = MasterAttribute::where("id",$attributeId)->first();
						
						
						$attributeDetails[$indexAttr]['AttributeId'] = $attributeData->id;
						$attributeDetails[$indexAttr]['AttributeName'] = $attributeData->attribute_name;
						$attributeDetails[$indexAttr]['AttributeCode'] = $attributeData->attribute_code;
						if($attributeData->attribute_code == 'agent_name_cbd')
						{
							$attributeDetails[$indexAttr]['AttributeTypeId'] = 3;
						
							$attributeDetails[$indexAttr]['AttributeTypeName'] = AttributeType::where("attribute_type_id",3)->first()->attribute_type_name;
						}
						else
						{
							$attributeDetails[$indexAttr]['AttributeTypeId'] = $attributeData->attribute_type;
						
							$attributeDetails[$indexAttr]['AttributeTypeName'] = AttributeType::where("attribute_type_id",$attributeData->attribute_type)->first()->attribute_type_name;
						}
						
						if($attributeData->attribute_code == 'customer_mobile')
						{
							$attributeDetails[$indexAttr]['Required'] = 1;
						}
						else
						{
$attributeDetails[$indexAttr]['Required'] = $Attr->required;
						}
						
						$attributeDetails[$indexAttr]['FormSection'] = $Attr->form_section;
						$attributeDetails[$indexAttr]['Status'] = $attributeData->status;
						if($attributeData->attribute_code == 'emp_id' || $attributeData->attribute_code == 'agent_name_cbd')
						{
							$empDetailsList = Employee_details::where("dept_id",$empDetails->dept_id)->where("job_function",2)->get();
							$empList = array();
							foreach($empDetailsList as $list)
							{
								if($empDetails->dept_id == 36)
								{
									
										$empList[$list->emp_id] = $list->emp_name.'('.$list->emp_id.')';
									
								}
								else
								{
									
										$empList[$list->emp_id] = $list->emp_name.'('.$list->source_code.')';
									
								}
							}
							$attributeDetails[$indexAttr]['OptionValues'] = json_encode($empList);
							
						}
						
						else if($empDetails->dept_id == 36 && $attributeData->attribute_code == 'team')
						{
							
							$teamA = array();
							$teamA[] = 'Ajay';
							$teamA[] = 'Anas';
							$teamA[] = 'Arsalan';
							$teamA[] = 'Mohsin';
							$teamA[] = 'Mujahid';
							$teamA[] = 'Sahir';
							$teamA[] = 'Shahnawaz';
							$teamA[] = 'Zubair';
							$attributeDetails[$indexAttr]['OptionValues'] = json_encode($teamA);
						}
						else
						{
							if($attributeData->attribute_type == 3)
							{
							$optionValueList = explode(",",$attributeData->option_values);
							$attributeDetails[$indexAttr]['OptionValues'] = json_encode($optionValueList);
							}
							else
							{
								$attributeDetails[$indexAttr]['OptionValues'] = '';
							}
						}
						$indexAttr++;
						}
					}
					
					/*
					*setcookie
					*/
					$attributeDetails[$indexAttr]['SID'] = 1500;
					$attributeDetails[$indexAttr]['sortOrder'] = 10;
					$attributeDetails[$indexAttr]['AttributeId'] = '';
					$attributeDetails[$indexAttr]['AttributeName'] = 'Pre Screen Approval/Rejected';
					$attributeDetails[$indexAttr]['AttributeCode'] = 'Pre_Screen_Approval_Rejected';
					$attributeDetails[$indexAttr]['AttributeTypeId'] =3;
					$attributeDetails[$indexAttr]['AttributeTypeName'] ="dropdown";
					$attributeDetails[$indexAttr]['Required'] =1;
					$attributeDetails[$indexAttr]['FormSection'] ='Sourcing Details';
					$attributeDetails[$indexAttr]['Status'] =1;
					$attributeDetails[$indexAttr]['OptionValues'] =json_encode(array("Select Any","Approved","Rejected"));
					/*
					*setcookie
					*/
					$sectionGroup = array();
					foreach($departmentFormAttributeGroup as $group)
					{
						$sectionGroup [] = $group->form_section;
						
					}
					$result['values'] = $attributeDetails;
					$result['valuesGroup'] = $sectionGroup;
					$result['responseCode'] = 200;
					$result['message'] = "Successfull";
					$result['errorMessage'] = "This field is required. You can not left blank.";
					}
					else
					{
						$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
					}
				}
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
}

public function PostSubmission(Request $request)
{
	$requestParameters = $request->input();
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' )
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				
			  $empDetails = Employee_details::where("emp_id",$empId)->first();
				
			if($empDetails->job_function != 2)
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
						
				}
				else
				{
					if($empDetails->dept_id == 36 || $empDetails->dept_id == 49)
					{
							 if($empDetails->dept_id ==49)
							 {
									$postDataInput = $requestParameters['attribute_value'];
									
									$entry_obj = new DepartmentFormEntry();			
							
									/*
									*parent entry 
									*start code
									*/
									$entry_obj->ref_no = $postDataInput['ref_no'];
									$entry_obj->form_id = 2;
									$entry_obj->source_from = 1;
									$entry_obj->form_title = 'CBD Internal MIS APP';
									$entry_obj->form_status = $postDataInput['status_cbd'];
									$entry_obj->team = $postDataInput['sm_name_cbd'];
									$entry_obj->customer_name = $postDataInput['customer_name'];
									$entry_obj->customer_mobile = $postDataInput['customer_mobile'];
									$entry_obj->remarks = $postDataInput['CBD_remark'];
									$entry_obj->emp_id = $empId;
									
									
										
										$sourceCodeMod = Employee_details::select("source_code")->where("emp_id",$empId)->first();
										if($sourceCodeMod != '')
										{
											$entry_obj->agent_code = $sourceCodeMod->source_code;
										}
										
									
									
									$entry_obj->channel_cbd = $postDataInput['channel_cbd'];
									$entry_obj->status_AECB_cbd = $postDataInput['aecb_status'];
									$entry_obj->card_type_cbd = $postDataInput['card_type_cbd'];
									$entry_obj->application_date = date("Y-m-d",strtotime($postDataInput['app_date']));
									$entry_obj->approval_date = date("Y-m-d",strtotime($postDataInput['app_date']));
									$entry_obj->status = 1;
									$entry_obj->cbd_marging_status = 1;	
									$entry_obj->missing_internal = 1;	
									$entry_obj->approval_update_status = 2;	
									$entry_obj->save();
									$insertID = $entry_obj->id;
									/*
									*parent entry 
									*end code
									*/
									
									
									/*
									*child entry 
									*start code
									*/
									$child_obj = new DepartmentFormChildEntry();
									foreach($postDataInput as $key=>$value)
									{
										$child_obj = new DepartmentFormChildEntry();
										$child_obj->parent_id = $insertID;
										$child_obj->form_id = 2;
										$child_obj->attribute_code = $key;
										if($key == 'approval_date_cbd')
										{
											$child_obj->attribute_value = date("Y-m-d",strtotime($postDataInput['app_date']));
										}
										else
										{
											$child_obj->attribute_value = $value;
										}
										$child_obj->status = 1;
										$child_obj->save();
									}
									/*
									*child entry 
									*end code
									*/
								$result['responseCode'] = 200;
								$result['message'] = "Successfull.";
							 }
							else
							{
								
								//mashreq
								$entry_obj = new DepartmentFormEntry();	
			
								$user_id = $empId;

								$entry_obj->user_id = $user_id;
								$postData = $request->input();
								$attribute_value = $postData['attribute_value'];
								
								if($attribute_value['Pre_Screen_Approval_Rejected'] == 'Approved')
									{
										$formId = 1;
									}
									else
									{
										$formId = 5;
									}
									$entry_obj->form_id = $formId;
								$entry_obj->source_from = 1;
								$entry_obj->form_title = 'Credit Card Submission Form APP';
								
								$entry_obj->save();
								$parent_id = $entry_obj->id;

								
								

								
							   
								$application_id = '';		
								$submission_date = '';
								$ref_no = '';
								$team = '';
								$emp_id = '';
								$emp_name = '';
								foreach($attribute_value as $k=>$v)
								{
									if($k=='application_id')
									{
										$application_id = $v;
									}
									if($k=='ref_no')
									{
										$ref_no = $v;					
										$check = DB::table('department_form_parent_entry')->whereRaw("ref_no ='".$ref_no."'")->get();
										if(count($check)>0)
										{						
											$delete1 = DB::table('department_form_parent_entry')->where('ref_no', $ref_no)->delete();
											$delete2 = DB::table('department_form_child_entry')->where('parent_id', $parent_id)->delete();				
											
										}
									}
									if($k=='submission_date')
									{
										$submission_date = date('Y-m-d',strtotime($v));
									}
									if($k=='team')
									{
										$team = $v;
									}
									if($k=='form_status')
									{
										$form_status = 'WIP';
									}
									if($k=='remarks')
									{
										$remarks = $v;
									}
									if($k=='emp_id')
									{					
										$emp_id = $v;
										$emp_name ='';
										$emp_nameMod = Employee_details::select("emp_name")->where("emp_id",$v)->first();
										if($emp_nameMod != '')
										{
											$emp_name = $emp_nameMod->emp_name;
										}
										$values_emp = array('parent_id' => $parent_id,'form_id' => $formId,'attribute_code' => 'emp_name','attribute_value' => $emp_name);
										DB::table('department_form_child_entry')->insert($values_emp);
									}
									$values = array('parent_id' => $parent_id,'form_id' => $formId,'attribute_code' => $k,'attribute_value' => $v);
									DB::table('department_form_child_entry')->insert($values);
									
								}
								if(count($_FILES)>0)
							   {
								   $target_path =public_path('uploads/formFiles/');
								   $file_attribute_value = $_FILES['attribute_value'];  
								  
								   foreach($file_attribute_value as $k=>$v)
									{
									   if($k=='name')
										{
										   foreach($v as $file_key=>$file_val)
											{
											   //echo $file_key.'=='.$file_val;						   
											   //echo '<br>';
											   $tmp_name = $file_attribute_value['tmp_name'][$file_key];
											   
											   $ext = explode('.', basename( $file_val));
											   $filename = "F_".md5(uniqid()) . "." . $ext[count($ext)-1];
											   $target_path = $target_path.$filename; 						

												if(move_uploaded_file($tmp_name, $target_path)) 
												{
													$file_values = array('attribute_value' => $filename);
													DB::table('department_form_child_entry')->where('form_id', $formId)->where('attribute_code', $file_key)->where('parent_id', $parent_id)->update($file_values);
													
												}						

											}					   
										}				  
										
									}
								}
								DepartmentFormEntry::where('id', $parent_id)
									->update(['customer_name' => $attribute_value['customer_name'],'customer_mobile' => str_replace("'","",$attribute_value['customer_mobile']),'application_id' => $application_id, 'ref_no' => $ref_no, 'submission_date' => $submission_date,'team' => $team,'form_status' => 'WIP','remarks' => addslashes(str_replace("'","`",$remarks)),'emp_id' => $emp_id,'emp_name' => $emp_name
									]);
									$result['responseCode'] = 200;
								$result['message'] = "Successfull.";
							}
					}
					else
					{
							$result['responseCode'] = 202;
						    $result['message'] = "You are not Permitted to access.";
					}
				}
			
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
}


public function NotExistEID(Request $request)
{
	$requestParameters = $request->input();
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' )
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				
			  $empDetails = Employee_details::where("emp_id",$empId)->first();
			 
			  EmpAppAccess::where("employee_id",$empId)->update(array("is_allow_eid"=>1));
			  $result['responseCode'] = 200;
				$result['message'] = "Successfull";
			
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
}

public function EmployeeSalesAgentV2(Request $request)
{
	$requestParameters = $request->input();
		
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' && isset($requestParameters['pageLimit']) && $requestParameters['pageLimit'] != '' && isset($requestParameters['pageNo']) && $requestParameters['pageNo'] != '' && isset($requestParameters['submisionDateFrom']) && $requestParameters['submisionDateFrom'] != '' && isset($requestParameters['submissionDateTo']) && $requestParameters['submissionDateTo'] != '')
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		$pageLimit = $requestParameters['pageLimit'];
		$pageNo = $requestParameters['pageNo'];
		$submisionDateFrom = $requestParameters['submisionDateFrom'];
		$submissionDateTo = $requestParameters['submissionDateTo'];
		$cardType = $requestParameters['cardType'];
		$isNumeric = $requestParameters['isNumeric'];
		$searchText = $requestParameters['searchText'];
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				
				$empDetails = Employee_details::where("emp_id",$empId)->first();
				
				if($empDetails->job_function != 2)
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
						
				}
				else
				{
				/*
				*get Employee Sales Details
				*/
				
				if($empDetails->dept_id == 36)
				{
					$whereRaw = 'department_form_parent_entry.form_id in (1,5)';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.submission_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and department_form_parent_entry.submission_date <= '".$submissionDateTo."'";
					}
					
					if($cardType != 'All' && $cardType != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.card_type ='".trim($cardType)."'";
					}
					if($searchText != 'All')
					{
						if($isNumeric == 'true')
						{
							$whereRaw .= " and department_form_parent_entry.customer_mobile like '%".trim($searchText)."%'";
						}
						else
						{
							$whereRaw .= " and department_form_parent_entry.customer_name like '%".trim($searchText)."%'";
						}
					}
					// echo $whereRaw;exit; 
					$deptMods = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->orderBy("submission_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					
					$totalRecordsSub = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count();
					$totalRecordsBooked = DB::table('department_form_parent_entry')
							->join('mashreq_login_data', 'mashreq_login_data.ref_no', '=', 'department_form_parent_entry.ref_no')
							
							->where('mashreq_login_data.booking_status',1) 
							->whereIn("department_form_parent_entry.form_id",array(1,5))
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->get()->count();
					$totalRecordsRejected = DepartmentFormEntry::where("emp_id",$empId)->whereIn("form_id",array(1,5))->whereRaw($whereRaw)->whereIn("form_status",array('Declined','Terminated','Decline','decline','Reject To Seller','Rejected'))->get()->count(); 
					
				
					/* echo "<pre>";
					print_r($deptMod);
					exit; */
					$response['Submissions'] = array();
					$i =0;
					foreach($deptMods as $mod)
					{
						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->submission_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['Team'] = $mod->team;
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $mod->bureau_score;
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $mod->card_type;
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
						
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
						if( $mod->form_id == 5)
						{
							$response['Submissions'][$i]['Status'] = 'Pre Screen Rejected';
						$response['Submissions'][$i]['colorCode'] = '#fd1a16';
						}
						else
						{
							
						$response['Submissions'][$i]['Status'] = $mod->form_status;
						$response['Submissions'][$i]['colorCode'] = $this->checkcolorCodeMashreq($mod->ref_no,$mod->form_status);
						}
											$childSalaryData =	DepartmentFormChildEntry::where("parent_id",$mod->id)->where("attribute_code","salary")->first();
						if($childSalaryData != '')
						{
							$response['Submissions'][$i]['Salary']  = $childSalaryData->attribute_value;
						}
						else
						{
							$response['Submissions'][$i]['Salary']  = NULL;
						}
						
						$response['Submissions'][$i]['BookedFlag'] = $this->checkBooked($mod->ref_no);
						$response['Submissions'][$i]['DeparmentId'] = 36;
						$response['Submissions'][$i]['DeparmentName'] = 'Mashreq';
						$response['Submissions'][$i]['channelCbd'] = $mod->channel_cbd;
						$response['Submissions'][$i]['statusAECBCbd'] = $mod->status_AECB_cbd;
						$response['Submissions'][$i]['approvalDate'] = $mod->approval_date;
						
						$text= htmlspecialchars_decode(strip_tags($mod->remarks));
						$response['Submissions'][$i]['Remarks'] = str_replace('\r',"",$text);
						/*
						*login Data
						*/
						$response['Submissions'][$i]['Nationality']  = NULL;
						
					
					
						$response['Submissions'][$i]['EmployeeCategoryDesc']  = NULL;
						$response['Submissions'][$i]['bureauScore']  = NULL;
					
						$response['Submissions'][$i]['bureauSegmentation']   = NULL;
				
						$response['Submissions'][$i]['minStartdate']  = NULL;
						$response['Submissions'][$i]['applicationStatus']  = NULL;
						$response['Submissions'][$i]['cdaDescision']  = NULL;
						$response['Submissions'][$i]['cif']  = NULL;
						$response['Submissions'][$i]['sellerChannelName']  = NULL;
						$response['Submissions'][$i]['dateofdisbursal']  = NULL;
						$loginData = MashreqLoginMIS::where("ref_no",$mod->ref_no)->first();
			if($loginData != '')
			{
					$response['Submissions'][$i]['Nationality']  = $loginData->nationality;
					$response['Submissions'][$i]['Salary']  = $loginData->cdafinalsalary;
					
					
					$response['Submissions'][$i]['EmployeeCategoryDesc']  = $loginData->employee_category_desc;
					$response['Submissions'][$i]['bureauScore']  = $loginData->bureau_score;
					
					
						$response['Submissions'][$i]['APPSCORE']  = NULL;
						$response['Submissions'][$i]['BureauMOB']  = NULL;
					
					$response['Submissions'][$i]['bureauSegmentation']   = $loginData->bureau_segmentation;
				
					$response['Submissions'][$i]['minStartdate']  = $loginData->min_startdate;
					$response['Submissions'][$i]['applicationStatus']  =$loginData->application_status;
					$response['Submissions'][$i]['cdaDescision']  =$loginData->cda_descision;
					$response['Submissions'][$i]['cif']  =$loginData->cif;
					$response['Submissions'][$i]['sellerChannelName']  =$loginData->seller_channel_name;
					$response['Submissions'][$i]['dateofdisbursal']  =$loginData->dateofdisbursal;
					
			}
						/*
						*login Data
						*/
						$i++;
					}
					/*
					*get list of cardType
					*/
					$cardsType = DepartmentFormEntry::select("card_type as CardType")->whereNotNull('card_type')->groupBy("card_type")->get();
					/* echo "<pre>";
					print_r($cardsType);
					exit; */
					/*
					*get list of cardType
					*/
					$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['totalRecordsSubmissions'] = $totalRecordsSub;
						$result['totalRecordsBooked'] = $totalRecordsBooked;
						$result['totalRecordsRejected'] = $totalRecordsRejected;
							$result['ListOfCardType'] = $cardsType;
						$result['Values'] = $response;
				}
				else if($empDetails->dept_id == 49)
				{
					$whereRaw = 'department_form_parent_entry.form_id = 2';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.application_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and department_form_parent_entry.application_date <= '".$submissionDateTo."'";
					}
					
					if($cardType != 'All' && $cardType != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.card_type ='".trim($cardType)."'";
					}
					if($searchText != 'All')
					{
						if($isNumeric == 'true')
						{
							$whereRaw .= " and department_form_parent_entry.customer_mobile like '%".trim($searchText)."%'";
						}
						else
						{
							$whereRaw .= " and department_form_parent_entry.customer_name like '%".trim($searchText)."%'";
						}
					}
					//echo $whereRaw;exit; 
					$deptMods = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->orderBy("application_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					$totalRecordsSub = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count();
					
					$totalRecordsBooked = DB::table('department_form_parent_entry')
							->join('CBD_bank_mis', 'department_form_parent_entry.ref_no', '=', 'CBD_bank_mis.ref_no')
							->whereIn("CBD_bank_mis.Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))
							->where("department_form_parent_entry.form_id",2)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->get()->count();
							
					$totalRecordsRejected = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",2)->whereIn("form_status",array("Missing(Terminated)","Application submission failed","Archive on Reject","Declined","Terminated","Terminated by user"))->whereRaw($whereRaw)->get()->count(); 
					
					/* echo "<pre>";
					print_r($deptMod);
					exit; */
					$response['Submissions'] = array();
					$i =0;
					foreach($deptMods as $mod)
					{
						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->application_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['Team'] = $mod->team;
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $this->CBDData($mod->ref_no,'Bureau_Score');
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $this->getCardType($mod->ref_no);
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
						
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
						$response['Submissions'][$i]['Status'] = $this->checkCBDStatus($mod->ref_no);
						$response['Submissions'][$i]['BookedFlag'] = $this->checkBookedCBD($mod->ref_no);
						$response['Submissions'][$i]['DeparmentId'] = 49;
						$response['Submissions'][$i]['DeparmentName'] = 'CBD';
							$response['Submissions'][$i]['channelCbd'] = $mod->channel_cbd;
						$response['Submissions'][$i]['statusAECBCbd'] = $mod->status_AECB_cbd;
						$response['Submissions'][$i]['approvalDate'] = $mod->approval_date;
							$response['Submissions'][$i]['colorCode'] = $this->checkcolorCodeCBD($mod->ref_no);
						$text= htmlspecialchars_decode(strip_tags($mod->remarks));
						$response['Submissions'][$i]['Remarks'] = str_replace('\r',"",$text);
							$response['Submissions'][$i]['Nationality']  = NULL;
						$response['Submissions'][$i]['Salary']  = NULL;
					
					
						$response['Submissions'][$i]['EmployeeCategoryDesc']  = NULL;
						$response['Submissions'][$i]['bureauScore']  = $this->CBDData($mod->ref_no,'Bureau_Score');
						$response['Submissions'][$i]['APPSCORE']  = $this->CBDData($mod->ref_no,'APP_SCORE');
						$response['Submissions'][$i]['BureauMOB']  = $this->CBDData($mod->ref_no,'Bureau_MOB');
					
						$response['Submissions'][$i]['bureauSegmentation']   = NULL;
				
							$response['Submissions'][$i]['minStartdate']  = NULL;
						$response['Submissions'][$i]['applicationStatus']  = NULL;
						$response['Submissions'][$i]['cdaDescision']  = NULL;
						$response['Submissions'][$i]['cif']  = NULL;
						$response['Submissions'][$i]['sellerChannelName']  = NULL;
						$response['Submissions'][$i]['dateofdisbursal']  = NULL;
						$i++;
					}
					/*
					*get list of cardType
					*/
					$cardsType = CBDBankMis::select("card_type as CardType")->whereNotNull('card_type')->where('card_type',"!=",'0')->groupBy("card_type")->get();
					/* echo "<pre>";
					print_r($cardsType);
					exit; */
					/*
					*get list of cardType
					*/
					$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['totalRecordsSubmissions'] = $totalRecordsSub;
						$result['totalRecordsBooked'] = $totalRecordsBooked;
						$result['totalRecordsRejected'] = $totalRecordsRejected;
							$result['ListOfCardType'] = $cardsType;
						$result['Values'] = $response;
				}
				else
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
				}
				
				
						/*
				*get Employee Sales Details
				*/
				}
				
				
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
}



protected function CBDData($rNo,$coloum)
{
	$thisSubmisisonBooked = CBDBankMis::where("ref_no",$rNo)->first();
	if($thisSubmisisonBooked != '')
	{
		return $thisSubmisisonBooked->$coloum;
		
	}
	else
	{
		return "";
	}
}



public function EmployeeSalesAgentBookedV2(Request $request)
{
	$requestParameters = $request->input();
		
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' && isset($requestParameters['pageLimit']) && $requestParameters['pageLimit'] != '' && isset($requestParameters['pageNo']) && $requestParameters['pageNo'] != '' && isset($requestParameters['submisionDateFrom']) && $requestParameters['submisionDateFrom'] != '' && isset($requestParameters['submissionDateTo']) && $requestParameters['submissionDateTo'] != '')
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		$pageLimit = $requestParameters['pageLimit'];
		$pageNo = $requestParameters['pageNo'];
		$submisionDateFrom = $requestParameters['submisionDateFrom'];
		$submissionDateTo = $requestParameters['submissionDateTo'];
		$cardType = $requestParameters['cardType'];
		$isNumeric = $requestParameters['isNumeric'];
		$searchText = $requestParameters['searchText'];
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				
				$empDetails = Employee_details::where("emp_id",$empId)->first();
				
				if($empDetails->job_function != 2)
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
						
				}
				else
				{
				/*
				*get Employee Sales Details
				*/
				
				if($empDetails->dept_id == 36)
				{
					$whereRaw = 'department_form_parent_entry.form_id = 1';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.submission_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and department_form_parent_entry.submission_date <= '".$submissionDateTo."'";
					}
					
					if($cardType != 'All' && $cardType != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.card_type ='".trim($cardType)."'";
					}
					if($searchText != 'All')
					{
						if($isNumeric == 'true')
						{
							$whereRaw .= " and department_form_parent_entry.customer_mobile like '%".trim($searchText)."%'";
						}
						else
						{
							$whereRaw .= " and department_form_parent_entry.customer_name like '%".trim($searchText)."%'";
						}
					}
					/* echo $whereRaw;exit; */
					//$deptMods = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->orderBy("submission_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					
					/* $totalRecords = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count(); */
					$deptMods = DB::table('department_form_parent_entry')
							->join('mashreq_login_data', 'mashreq_login_data.ref_no', '=', 'department_form_parent_entry.ref_no')
							
							->where('mashreq_login_data.booking_status',1) 
							->where("department_form_parent_entry.form_id",1)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->orderBy("submission_date","DESC")->skip($pageNo)->take($pageLimit)->get();
				
				
					
					$totalRecordsSub = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count();
					$totalRecordsBooked = DB::table('department_form_parent_entry')
							->join('mashreq_login_data', 'mashreq_login_data.ref_no', '=', 'department_form_parent_entry.ref_no')
							
							->where('mashreq_login_data.booking_status',1) 
							->where("department_form_parent_entry.form_id",1)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->get()->count();
					$totalRecordsRejected = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",1)->whereRaw($whereRaw)->whereIn("form_status",array('Declined','Terminated','Decline','decline','Reject To Seller','Rejected'))->get()->count(); 
					
				
				
					 /* echo "<pre>";
					print_r($deptMods->count());
					exit;  */
					/* echo $deptMods->count();exit; */
					$response['Submissions'] = array();
					$i =0;
					foreach($deptMods as $mod)
					{
						
						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->submission_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['Team'] = $mod->team;
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $mod->bureau_score;
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $mod->card_type;
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
						
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
						$response['Submissions'][$i]['Status'] = $mod->form_status;
						$response['Submissions'][$i]['BookedFlag'] = $this->checkBooked($mod->ref_no);
						$response['Submissions'][$i]['DeparmentId'] = 36;
						$response['Submissions'][$i]['DeparmentName'] = 'Mashreq';
							$response['Submissions'][$i]['channelCbd'] = $mod->channel_cbd;
						$response['Submissions'][$i]['statusAECBCbd'] = $mod->status_AECB_cbd;
						$response['Submissions'][$i]['approvalDate'] = $mod->approval_date;
						$response['Submissions'][$i]['colorCode'] = $this->checkcolorCodeMashreq($mod->ref_no,$mod->form_status);
					$text= htmlspecialchars_decode(strip_tags($mod->remarks));
						$response['Submissions'][$i]['Remarks'] = str_replace('\r',"",$text);
						/*
						*login Data
						*/
						$response['Submissions'][$i]['Nationality']  = NULL;
						$response['Submissions'][$i]['Salary']  = NULL;
					
					
						$response['Submissions'][$i]['EmployeeCategoryDesc']  = NULL;
						$response['Submissions'][$i]['bureauScore']  = NULL;
					
						$response['Submissions'][$i]['bureauSegmentation']   = NULL;
				
						$response['Submissions'][$i]['minStartdate']  = NULL;
						$response['Submissions'][$i]['applicationStatus']  = NULL;
						$response['Submissions'][$i]['cdaDescision']  = NULL;
						$response['Submissions'][$i]['cif']  = NULL;
						$response['Submissions'][$i]['sellerChannelName']  = NULL;
						$response['Submissions'][$i]['dateofdisbursal']  = NULL;
						$loginData = MashreqLoginMIS::where("ref_no",$mod->ref_no)->first();
			if($loginData != '')
			{
					$response['Submissions'][$i]['Nationality']  = $loginData->nationality;
					$response['Submissions'][$i]['Salary']  = $loginData->cdafinalsalary;
					
					
					$response['Submissions'][$i]['EmployeeCategoryDesc']  = $loginData->employee_category_desc;
					$response['Submissions'][$i]['bureauScore']  = $loginData->bureau_score;
						$response['Submissions'][$i]['APPSCORE']  = NULL;
						$response['Submissions'][$i]['BureauMOB']  = NULL;
					$response['Submissions'][$i]['bureauSegmentation']   = $loginData->bureau_segmentation;
				
					$response['Submissions'][$i]['minStartdate']  = $loginData->min_startdate;
					$response['Submissions'][$i]['applicationStatus']  =$loginData->application_status;
					$response['Submissions'][$i]['cdaDescision']  =$loginData->cda_descision;
					$response['Submissions'][$i]['cif']  =$loginData->cif;
					$response['Submissions'][$i]['sellerChannelName']  =$loginData->seller_channel_name;
					$response['Submissions'][$i]['dateofdisbursal']  = $loginData->dateofdisbursal;
					
			}
						/*
						*login Data
						*/
						$i++;
					
					}
					/*
					*get list of cardType
					*/
					$cardsType = DepartmentFormEntry::select("card_type as CardType")->whereNotNull('card_type')->groupBy("card_type")->get();
					/* echo "<pre>";
					print_r($response['Submissions']);
					exit; */ 
					/*
					*get list of cardType
					*/
					$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['totalRecordsSubmissions'] = $totalRecordsSub;
						$result['totalRecordsBooked'] = $totalRecordsBooked;
						$result['totalRecordsRejected'] = $totalRecordsRejected;
							$result['ListOfCardType'] = $cardsType;
						$result['Values'] = $response;
				}
				else if($empDetails->dept_id == 49)
				{
					$whereRaw = 'department_form_parent_entry.form_id = 2';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.application_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and department_form_parent_entry.application_date <= '".$submissionDateTo."'";
					}
					
					if($cardType != 'All' && $cardType != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.card_type ='".trim($cardType)."'";
					}
					if($searchText != 'All')
					{
						if($isNumeric == 'true')
						{
							$whereRaw .= " and department_form_parent_entry.customer_mobile like '%".trim($searchText)."%'";
						}
						else
						{
							$whereRaw .= " and department_form_parent_entry.customer_name like '%".trim($searchText)."%'";
						}
					}
					// echo $whereRaw;exit; 
					//$deptMods = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->orderBy("application_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					/* $totalRecords = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count(); */
					$deptMods = DB::table('department_form_parent_entry')
							->join('CBD_bank_mis', 'department_form_parent_entry.ref_no', '=', 'CBD_bank_mis.ref_no')
							->whereIn("CBD_bank_mis.Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))
							->where("department_form_parent_entry.form_id",2)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->orderBy("application_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					
						$totalRecordsSub = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count();
					
					$totalRecordsBooked = DB::table('department_form_parent_entry')
							->join('CBD_bank_mis', 'department_form_parent_entry.ref_no', '=', 'CBD_bank_mis.ref_no')
							->whereIn("CBD_bank_mis.Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))
							->where("department_form_parent_entry.form_id",2)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->get()->count();
							
					$totalRecordsRejected = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",2)->whereIn("form_status",array("Missing(Terminated)","Application submission failed","Archive on Reject","Declined","Terminated","Terminated by user"))->whereRaw($whereRaw)->get()->count(); 
					
				/* echo $totalRecords;exit; */
					/* echo "<pre>";
					print_r($deptMod);
					exit; */
					$response['Submissions'] = array();
					$i =0;
					foreach($deptMods as $mod)
					{
						
						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->application_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['Team'] = $mod->team;
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $this->CBDData($mod->ref_no,'Bureau_Score');
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $this->getCardType($mod->ref_no);
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
					
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
						$response['Submissions'][$i]['Status'] = $this->checkCBDStatus($mod->ref_no);
						$response['Submissions'][$i]['BookedFlag'] = $this->checkBookedCBD($mod->ref_no);
						$response['Submissions'][$i]['DeparmentId'] = 49;
						$response['Submissions'][$i]['DeparmentName'] = 'CBD';
							$response['Submissions'][$i]['channelCbd'] = $mod->channel_cbd;
						$response['Submissions'][$i]['statusAECBCbd'] = $mod->status_AECB_cbd;
						$response['Submissions'][$i]['approvalDate'] = $mod->approval_date;
							$response['Submissions'][$i]['colorCode'] = $this->checkcolorCodeCBD($mod->ref_no);
							$text= htmlspecialchars_decode(strip_tags($mod->remarks));
						$response['Submissions'][$i]['Remarks'] = str_replace('\r',"",$text);
							$response['Submissions'][$i]['Nationality']  = NULL;
						$response['Submissions'][$i]['Salary']  = NULL;
					
					
						$response['Submissions'][$i]['EmployeeCategoryDesc']  = NULL;
							$response['Submissions'][$i]['bureauScore']  = $this->CBDData($mod->ref_no,'Bureau_Score');
						$response['Submissions'][$i]['APPSCORE']  = $this->CBDData($mod->ref_no,'APP_SCORE');
						$response['Submissions'][$i]['BureauMOB']  = $this->CBDData($mod->ref_no,'Bureau_MOB');
					
						$response['Submissions'][$i]['bureauSegmentation']   = NULL;
				
							$response['Submissions'][$i]['minStartdate']  = NULL;
						$response['Submissions'][$i]['applicationStatus']  = NULL;
						$response['Submissions'][$i]['cdaDescision']  = NULL;
						$response['Submissions'][$i]['cif']  = NULL;
						$response['Submissions'][$i]['sellerChannelName']  = NULL;
						$response['Submissions'][$i]['dateofdisbursal']  = NULL;
						$i++;
						
					}
					/*
					*get list of cardType
					*/
					$cardsType = CBDBankMis::select("card_type as CardType")->whereNotNull('card_type')->where('card_type',"!=",'0')->groupBy("card_type")->get();
					/* echo "<pre>";
					print_r($cardsType);
					exit; */
					/*
					*get list of cardType
					*/
					$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['totalRecordsSubmissions'] = $totalRecordsSub;
						$result['totalRecordsBooked'] = $totalRecordsBooked;
						$result['totalRecordsRejected'] = $totalRecordsRejected;
							$result['ListOfCardType'] = $cardsType;
						$result['Values'] = $response;
				}
				else
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
				}
				
				
						/*
				*get Employee Sales Details
				*/
				}
				
				
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
}




public function EmployeeSalesAgentRejectedV2(Request $request)
{
	$requestParameters = $request->input();
		
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' && isset($requestParameters['pageLimit']) && $requestParameters['pageLimit'] != '' && isset($requestParameters['pageNo']) && $requestParameters['pageNo'] != '' && isset($requestParameters['submisionDateFrom']) && $requestParameters['submisionDateFrom'] != '' && isset($requestParameters['submissionDateTo']) && $requestParameters['submissionDateTo'] != '')
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		$pageLimit = $requestParameters['pageLimit'];
		$pageNo = $requestParameters['pageNo'];
		$submisionDateFrom = $requestParameters['submisionDateFrom'];
		$submissionDateTo = $requestParameters['submissionDateTo'];
		$cardType = $requestParameters['cardType'];
		$isNumeric = $requestParameters['isNumeric'];
		$searchText = $requestParameters['searchText'];
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				
				$empDetails = Employee_details::where("emp_id",$empId)->first();
				
				if($empDetails->job_function != 2)
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
						
				}
				else
				{
					
				/*
				*get Employee Sales Details
				*/
				
				if($empDetails->dept_id == 36)
				{
					$whereRaw = 'department_form_parent_entry.form_id = 1';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.submission_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and department_form_parent_entry.submission_date <= '".$submissionDateTo."'";
					}
					
					if($cardType != 'All' && $cardType != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.card_type ='".trim($cardType)."'";
					}
					if($searchText != 'All')
					{
						if($isNumeric == 'true')
						{
							$whereRaw .= " and department_form_parent_entry.customer_mobile like '%".trim($searchText)."%'";
						}
						else
						{
							$whereRaw .= " and department_form_parent_entry.customer_name like '%".trim($searchText)."%'";
						}
					}
					/* echo $whereRaw;exit; */
					$deptMods = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",1)->whereRaw($whereRaw)->whereIn("form_status",array('Declined','Terminated','Decline','decline','Reject To Seller','Rejected'))->orderBy("submission_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					
					$totalRecordsSub = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count();
					$totalRecordsBooked = DB::table('department_form_parent_entry')
							->join('mashreq_login_data', 'mashreq_login_data.ref_no', '=', 'department_form_parent_entry.ref_no')
							
							->where('mashreq_login_data.booking_status',1) 
							->where("department_form_parent_entry.form_id",1)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->get()->count();
					$totalRecordsRejected = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",1)->whereRaw($whereRaw)->whereIn("form_status",array('Declined','Terminated','Decline','decline','Reject To Seller','Rejected'))->get()->count(); 
					
				
				
				
					/* echo "<pre>";
					print_r($deptMod);
					exit; */
					$response['Submissions'] = array();
					$i =0;
					foreach($deptMods as $mod)
					{
						
						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->submission_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['Team'] = $mod->team;
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $mod->bureau_score;
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $mod->card_type;
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
						
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
						$response['Submissions'][$i]['Status'] = $mod->form_status;
						$response['Submissions'][$i]['BookedFlag'] = '';
						$response['Submissions'][$i]['DeparmentId'] = 36;
						$response['Submissions'][$i]['DeparmentName'] = 'Mashreq';
							$response['Submissions'][$i]['channelCbd'] = $mod->channel_cbd;
						$response['Submissions'][$i]['statusAECBCbd'] = $mod->status_AECB_cbd;
						$response['Submissions'][$i]['approvalDate'] = $mod->approval_date;
						$response['Submissions'][$i]['colorCode'] = $this->checkcolorCodeMashreq($mod->ref_no,$mod->form_status);
						$text= htmlspecialchars_decode(strip_tags($mod->remarks));
						$response['Submissions'][$i]['Remarks'] = str_replace('\r',"",$text);
						/*
						*login Data
						*/
						$response['Submissions'][$i]['Nationality']  = NULL;
						$response['Submissions'][$i]['Salary']  = NULL;
					
					
						$response['Submissions'][$i]['EmployeeCategoryDesc']  = NULL;
						$response['Submissions'][$i]['bureauScore']  = NULL;
					
						$response['Submissions'][$i]['bureauSegmentation']   = NULL;
				$response['Submissions'][$i]['minStartdate']  = NULL;
						$response['Submissions'][$i]['applicationStatus']  = NULL;
						$response['Submissions'][$i]['cdaDescision']  = NULL;
						$response['Submissions'][$i]['cif']  = NULL;
						$response['Submissions'][$i]['sellerChannelName']  = NULL;
						$response['Submissions'][$i]['dateofdisbursal']  = NULL;
						$loginData = MashreqLoginMIS::where("ref_no",$mod->ref_no)->first();
			if($loginData != '')
			{
					$response['Submissions'][$i]['Nationality']  = $loginData->nationality;
					$response['Submissions'][$i]['Salary']  = $loginData->cdafinalsalary;
					
					
					$response['Submissions'][$i]['EmployeeCategoryDesc']  = $loginData->employee_category_desc;
					$response['Submissions'][$i]['bureauScore']  = $loginData->bureau_score;
						$response['Submissions'][$i]['APPSCORE']  = NULL;
						$response['Submissions'][$i]['BureauMOB']  = NULL;
					$response['Submissions'][$i]['bureauSegmentation']   = $loginData->bureau_segmentation;
				
					$response['Submissions'][$i]['minStartdate']  = $loginData->min_startdate;
					$response['Submissions'][$i]['applicationStatus']  =$loginData->application_status;
					$response['Submissions'][$i]['cdaDescision']  =$loginData->cda_descision;
					$response['Submissions'][$i]['cif']  =$loginData->cif;
					$response['Submissions'][$i]['sellerChannelName']  =$loginData->seller_channel_name;
					$response['Submissions'][$i]['dateofdisbursal']  = $loginData->dateofdisbursal;
					
			}
						/*
						*login Data
						*/
						$i++;
					
					}
					/*
					*get list of cardType
					*/
					$cardsType = DepartmentFormEntry::select("card_type as CardType")->whereNotNull('card_type')->groupBy("card_type")->get();
					/* echo "<pre>";
					print_r($cardsType);
					exit; */
					/*
					*get list of cardType
					*/
					$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['totalRecordsSubmissions'] = $totalRecordsSub;
						$result['totalRecordsBooked'] = $totalRecordsBooked;
						$result['totalRecordsRejected'] = $totalRecordsRejected;
							$result['ListOfCardType'] = $cardsType;
						$result['Values'] = $response;
				}
				else if($empDetails->dept_id == 49)
				{
					$whereRaw = 'department_form_parent_entry.form_id = 2';
					if($submisionDateFrom != 'All' && $submissionDateTo != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.application_date >='".date("Y-m-d",strtotime($submisionDateFrom))."' and department_form_parent_entry.application_date <= '".$submissionDateTo."'";
					}
					
					if($cardType != 'All' && $cardType != 'All')
					{
						$whereRaw .= " and department_form_parent_entry.card_type ='".trim($cardType)."'";
					}
					if($searchText != 'All')
					{
						if($isNumeric == 'true')
						{
							$whereRaw .= " and department_form_parent_entry.customer_mobile like '%".trim($searchText)."%'";
						}
						else
						{
							$whereRaw .= " and department_form_parent_entry.customer_name like '%".trim($searchText)."%'";
						}
					}
					/* echo $whereRaw;exit; */
					$deptMods = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",2)->whereIn("form_status",array("Missing(Terminated)","Application submission failed","Archive on Reject","Declined","Terminated","Terminated by user"))->whereRaw($whereRaw)->orderBy("application_date","DESC")->skip($pageNo)->take($pageLimit)->get();
					 
					
						$totalRecordsSub = DepartmentFormEntry::where("emp_id",$empId)->whereRaw($whereRaw)->get()->count();
					
					$totalRecordsBooked = DB::table('department_form_parent_entry')
							->join('CBD_bank_mis', 'department_form_parent_entry.ref_no', '=', 'CBD_bank_mis.ref_no')
							->whereIn("CBD_bank_mis.Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))
							->where("department_form_parent_entry.form_id",2)
							->whereRaw($whereRaw)
							->where("department_form_parent_entry.emp_id",$empId)
							->get()->count();
							
					$totalRecordsRejected = DepartmentFormEntry::where("emp_id",$empId)->where("form_id",2)->whereIn("form_status",array("Missing(Terminated)","Application submission failed","Archive on Reject","Declined","Terminated","Terminated by user"))->whereRaw($whereRaw)->get()->count(); 
					
						
				/* echo $totalRecords;exit; */
					/*  echo "<pre>";
					print_r($deptMods);
					exit; */ 
					$response['Submissions'] = array();
					$i =0;
					foreach($deptMods as $mod)
					{

						$response['Submissions'][$i]['SubmissionDate'] = date("Y-m-d",strtotime($mod->application_date));
						$response['Submissions'][$i]['CustomerName'] = $mod->customer_name;
						$response['Submissions'][$i]['CustomerMobile'] = $mod->customer_mobile;
						$response['Submissions'][$i]['Team'] = $mod->team;
						$response['Submissions'][$i]['StatusLogin'] = $mod->status_login;
						$response['Submissions'][$i]['BureauScore'] = $this->CBDData($mod->ref_no,'Bureau_Score');
						$response['Submissions'][$i]['MrsScore'] = $mod->mrs_score;
						$response['Submissions'][$i]['CardType'] = $this->getCardType($mod->ref_no);
						$response['Submissions'][$i]['AllCdaDeviation'] = $mod->all_cda_deviation;
						$response['Submissions'][$i]['BureauSegmentation'] = $mod->bureau_segmentation;
						
						$response['Submissions'][$i]['CdaDescision'] = $mod->cda_descision;
						$response['Submissions'][$i]['EmployerName'] = $mod->employer_name;
						$response['Submissions'][$i]['LastComment'] = $mod->last_comment;
						$response['Submissions'][$i]['EmployeeCategoryDesc'] = $mod->employee_category_desc;
						$response['Submissions'][$i]['CustomernameLogin'] = $mod->customername_login;
						$response['Submissions'][$i]['RefNo'] = $mod->ref_no;
						$response['Submissions'][$i]['ApplicationId'] = $mod->application_id;
						$response['Submissions'][$i]['Sid'] = $mod->id;
						$response['Submissions'][$i]['Status'] = $this->checkCBDStatus($mod->ref_no);
						$response['Submissions'][$i]['BookedFlag'] = '';
						$response['Submissions'][$i]['DeparmentId'] = 49;
						$response['Submissions'][$i]['DeparmentName'] = 'CBD';
							$response['Submissions'][$i]['channelCbd'] = $mod->channel_cbd;
						$response['Submissions'][$i]['statusAECBCbd'] = $mod->status_AECB_cbd;
						$response['Submissions'][$i]['approvalDate'] = $mod->approval_date;
							$response['Submissions'][$i]['colorCode'] = $this->checkcolorCodeCBD($mod->ref_no);
						$text= htmlspecialchars_decode(strip_tags($mod->remarks));
						$response['Submissions'][$i]['Remarks'] = str_replace('\r',"",$text);
							$response['Submissions'][$i]['Nationality']  = NULL;
						$response['Submissions'][$i]['Salary']  = NULL;
					
					
						$response['Submissions'][$i]['EmployeeCategoryDesc']  = NULL;
							$response['Submissions'][$i]['bureauScore']  = $this->CBDData($mod->ref_no,'Bureau_Score');
						$response['Submissions'][$i]['APPSCORE']  = $this->CBDData($mod->ref_no,'APP_SCORE');
						$response['Submissions'][$i]['BureauMOB']  = $this->CBDData($mod->ref_no,'Bureau_MOB');
					
						$response['Submissions'][$i]['bureauSegmentation']   = NULL;
				
						$response['Submissions'][$i]['minStartdate']  = NULL;
						$response['Submissions'][$i]['applicationStatus']  = NULL;
						$response['Submissions'][$i]['cdaDescision']  = NULL;
						$response['Submissions'][$i]['cif']  = NULL;
						$response['Submissions'][$i]['sellerChannelName']  = NULL;
						$i++;
						
					}
					/*
					*get list of cardType
					*/
					$cardsType = CBDBankMis::select("card_type as CardType")->whereNotNull('card_type')->where('card_type',"!=",'0')->groupBy("card_type")->get();
					/* echo "<pre>";
					print_r($cardsType);
					exit; */
					/*
					*get list of cardType
					*/
					$result['responseCode'] = 200;
						$result['message'] = "Successfull";
						$result['totalRecordsSubmissions'] = $totalRecordsSub;
						$result['totalRecordsBooked'] = $totalRecordsBooked;
						$result['totalRecordsRejected'] = $totalRecordsRejected;
							$result['ListOfCardType'] = $cardsType;
						$result['Values'] = $response;
				}
				else
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
				}
				
				
						/*
				*get Employee Sales Details
				*/
				}
				
				
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
}


public function PostSubmissionV2(Request $request)
{
	$requestParameters = $request->input();
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' )
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				
			  $empDetails = Employee_details::where("emp_id",$empId)->first();
				
			if($empDetails->job_function != 2)
				{
					$result['responseCode'] = 202;
						$result['message'] = "You are not Permitted to access.";
						
				}
				else
				{
					if($empDetails->dept_id == 36 || $empDetails->dept_id == 49)
					{
							 if($empDetails->dept_id ==49)
							 {
									$postDataInput = $requestParameters['attribute_value'];
									
									$entry_obj = new DepartmentFormEntry();			
							
									/*
									*parent entry 
									*start code
									*/
									$entry_obj->ref_no = $postDataInput['ref_no'];
									$entry_obj->form_id = 2;
									$entry_obj->source_from = 1;
									$entry_obj->form_title = 'CBD Internal MIS APP';
									$entry_obj->form_status = $postDataInput['status_cbd'];
									$entry_obj->team = $postDataInput['sm_name_cbd'];
									$entry_obj->customer_name = $postDataInput['customer_name'];
									$entry_obj->customer_mobile = $postDataInput['customer_mobile'];
									$entry_obj->remarks = $postDataInput['CBD_remark'];
									$entry_obj->emp_id = $empId;
									
									
										
										$sourceCodeMod = Employee_details::select("source_code")->where("emp_id",$empId)->first();
										if($sourceCodeMod != '')
										{
											$entry_obj->agent_code = $sourceCodeMod->source_code;
										}
										
									
									
									$entry_obj->channel_cbd = $postDataInput['channel_cbd'];
									$entry_obj->status_AECB_cbd = $postDataInput['aecb_status'];
									$entry_obj->card_type_cbd = $postDataInput['card_type_cbd'];
									$entry_obj->application_date = date("Y-m-d",strtotime($postDataInput['app_date']));
									$entry_obj->approval_date = date("Y-m-d",strtotime($postDataInput['app_date']));
									$entry_obj->status = 1;
									$entry_obj->cbd_marging_status = 1;	
									$entry_obj->missing_internal = 1;	
									$entry_obj->approval_update_status = 2;	
									$entry_obj->save();
									$insertID = $entry_obj->id;
									/*
									*parent entry 
									*end code
									*/
									
									
									/*
									*child entry 
									*start code
									*/
									$child_obj = new DepartmentFormChildEntry();
									foreach($postDataInput as $key=>$value)
									{
										$child_obj = new DepartmentFormChildEntry();
										$child_obj->parent_id = $insertID;
										$child_obj->form_id = 2;
										$child_obj->attribute_code = $key;
										if($key == 'approval_date_cbd')
										{
											$child_obj->attribute_value = date("Y-m-d",strtotime($postDataInput['app_date']));
										}
										else
										{
											$child_obj->attribute_value = $value;
										}
										$child_obj->status = 1;
										$cusName = $postDataInput['customer_name'];
										
									}
									/*
									*child entry 
									*end code
									*/
									NotificatonController::sendMeNotification($empId,'New Sale Submitted','Great work! Your sale for '.$cusName.' has been successfully submitted. Keep the momentum going!','SubmissionList');
										$child_obj->save();
								$result['responseCode'] = 200;
								$result['message'] = "Successfull.";
							 }
							else
							{
								
								//mashreq
								$entry_obj = new DepartmentFormEntry();	
			
								$user_id = $empId;

								$entry_obj->user_id = $user_id;
								$postData = $request->input();
								$attribute_value = $postData['attribute_value'];
								
								if($attribute_value['Pre_Screen_Approval_Rejected'] == 'Approved')
									{
										$formId = 1;
									}
									else
									{
										$formId = 5;
									}
									$entry_obj->form_id = $formId;
								$entry_obj->source_from = 1;
								$entry_obj->form_title = 'Credit Card Submission Form APP';
								
								$entry_obj->save();
								$parent_id = $entry_obj->id;

								
								

								
							   
								$application_id = '';		
								$submission_date = '';
								$ref_no = '';
								$team = '';
								$emp_id = '';
								$emp_name = '';
								foreach($attribute_value as $k=>$v)
								{
									if($k=='application_id')
									{
										$application_id = $v;
									}
									if($k=='ref_no')
									{
										$ref_no = $v;					
										$check = DB::table('department_form_parent_entry')->whereRaw("ref_no ='".$ref_no."'")->get();
										if(count($check)>0)
										{						
											$delete1 = DB::table('department_form_parent_entry')->where('ref_no', $ref_no)->delete();
											$delete2 = DB::table('department_form_child_entry')->where('parent_id', $parent_id)->delete();				
											
										}
									}
									if($k=='submission_date')
									{
										$submission_date = date('Y-m-d',strtotime($v));
									}
									if($k=='team')
									{
										$team = $v;
									}
									if($k=='form_status')
									{
										$form_status = 'WIP';
									}
									if($k=='remarks')
									{
										$remarks = $v;
									}
									if($k=='emp_id')
									{					
										$emp_id = $v;
										$emp_name ='';
										$emp_nameMod = Employee_details::select("emp_name")->where("emp_id",$v)->first();
										if($emp_nameMod != '')
										{
											$emp_name = $emp_nameMod->emp_name;
										}
										$values_emp = array('parent_id' => $parent_id,'form_id' => $formId,'attribute_code' => 'emp_name','attribute_value' => $emp_name);
										DB::table('department_form_child_entry')->insert($values_emp);
									}
									$values = array('parent_id' => $parent_id,'form_id' => $formId,'attribute_code' => $k,'attribute_value' => $v);
									DB::table('department_form_child_entry')->insert($values);
									
								}
								if(count($_FILES)>0)
							   {
								   $target_path =public_path('uploads/formFiles/');
								   $file_attribute_value = $_FILES['attribute_value'];  
								  
								   foreach($file_attribute_value as $k=>$v)
									{
									   if($k=='name')
										{
										   foreach($v as $file_key=>$file_val)
											{
											   //echo $file_key.'=='.$file_val;						   
											   //echo '<br>';
											   $tmp_name = $file_attribute_value['tmp_name'][$file_key];
											   
											   $ext = explode('.', basename( $file_val));
											   $filename = "F_".md5(uniqid()) . "." . $ext[count($ext)-1];
											   $target_path = $target_path.$filename; 						

												if(move_uploaded_file($tmp_name, $target_path)) 
												{
													$file_values = array('attribute_value' => $filename);
													DB::table('department_form_child_entry')->where('form_id', $formId)->where('attribute_code', $file_key)->where('parent_id', $parent_id)->update($file_values);
													
												}						

											}					   
										}				  
										
									}
								}
								$cusName = $attribute_value['customer_name'];
								NotificatonController::sendMeNotification($empId,'New Sale Submitted','Great work! Your sale for '.$cusName.' has been successfully submitted. Keep the momentum going!','SubmissionList');
								DepartmentFormEntry::where('id', $parent_id)
									->update(['customer_name' => $attribute_value['customer_name'],'customer_mobile' => str_replace("'","",$attribute_value['customer_mobile']),'application_id' => $application_id, 'ref_no' => $ref_no, 'submission_date' => $submission_date,'team' => $team,'form_status' => 'WIP','remarks' => addslashes(str_replace("'","`",$remarks)),'emp_id' => $emp_id,'emp_name' => $emp_name
									]);
									$result['responseCode'] = 200;
								$result['message'] = "Successfull.";
							}
					}
					else
					{
							$result['responseCode'] = 202;
						    $result['message'] = "You are not Permitted to access.";
					}
				}
			
			}
			else
			{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			}
		}
		else
		{
				$result['responseCode'] = 401;
				$result['message'] = "Issue in token or employee Id.";
			
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		return response()->json($result);
}
}