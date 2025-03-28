<?php
namespace App\Http\Controllers\API\V4;

use App\Http\Controllers\Controller;
use App\Models\API\APIAuth;
use App\Models\API\V2\DeviceTokenFrontend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Employee\EmpAppAccess;
use App\Models\Employee\Employee_details;
use App\Models\Company\Department;
use App\Models\JobFunction\JobFunction;
use App\Models\Recruiter\Designation;
use App\Models\KYCProcess\KYCProcess;
use App\Models\Employee\Employee_attribute;
use  App\Models\Attribute\Attributes;

use App\Models\Employee\ChangeSalary;
use App\Models\Entry\Employee;
use App\Models\Changesalary\Change_Salary_logs;
use App\Models\WarningLetter\WarningLetterRequest;
use App\Models\WarningLetter\WarningLetterReasons;
use App\Http\Controllers\EmployeePerformanceReview\WarningLetterController;
use DateTime;
use Crypt;

class V4WarningLetterController extends Controller
{
	
	public function warningLetterTabs(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					$result['responseCode'] = 200;
							$result['message'] = "Sucessfull.";
				$changeSalaryList = array();
				$changeSalaryList[0]['Values'] = 'All';
				$changeSalaryList[1]['Values'] = 'Requested';
				$changeSalaryList[2]['Values'] = 'In-Process';
				$changeSalaryList[3]['Values'] = 'Issued';
				
				$changeSalaryList[0]['Keys'] = 'All';
				$changeSalaryList[1]['Keys'] = 'Requested';
				$changeSalaryList[2]['Keys'] = 'inProcess';
				$changeSalaryList[3]['Keys'] = 'issued';
				
				$result['result'] = $changeSalaryList;
					
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
	
	
	protected function getUserName($uid)
	{
		$_empModDetails = Employee::where("id",$uid)->first();
		if($_empModDetails != '')
		{
			return $_empModDetails->fullname;
		}
		else
		{
			return '-';
		}
	}
	
	protected function getAgentName($eid)
	{
		$_empModDetails = Employee_details::where("emp_id",$eid)->first();
		if($_empModDetails != '')
		{
			return $_empModDetails->emp_name;
		}
		else
		{
			return '-';
		}
	}

	protected function getTimeFromJoiningAPI($empid)
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
	
	protected function getReasonTxt($reasonId)
	{
		$warningDetails = WarningLetterReasons::where("id",$reasonId)->first();
		if($warningDetails != '')
		{
			return $warningDetails->name;
		}
		else
		{
			return "-";
		}
	}
	
	public function warningLetterList(Request $request)
	{
		
		$requestParameters = $request->input();
		
		if(isset($requestParameters['type']) && $requestParameters['type'] != ''&&  isset($requestParameters['managerId']) && $requestParameters['managerId'] != ''&& isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$managerId = $requestParameters['managerId'];
			$type = $requestParameters['type'];
			$pageLimit = $requestParameters['pageLimit'];
			$pageNo = $requestParameters['pageNo'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					if($type == 'All')
					{
						
						$warningLettersData = WarningLetterRequest::where('tl_id', $managerId)->orderBy('id', 'desc')->skip($pageNo)->take($pageLimit)
							->get();
							$totalCountWarningLetter = WarningLetterRequest::where('tl_id', $managerId)->get()->count();
					}
					else if($type == 'Requested')
					{
							$warningLettersData = WarningLetterRequest::where('approved_status', 0)
													->where('reject_status', 0)
													->where('tl_id', $managerId)
													->orderBy('id', 'desc')
													->skip($pageNo)
													->take($pageLimit)
													->get();
													
							$totalCountWarningLetter = WarningLetterRequest::where('approved_status', 0)
													->where('reject_status', 0)
													->where('tl_id', $managerId)
													
													->get()->count();
					}
					
					else if($type == 'inProcess')
					{
							$warningLettersData = WarningLetterRequest::where('final_status', 0)
													->where('approved_status', 1)
													->where('tl_id', $managerId)
													->orderBy('id', 'desc')
													->skip($pageNo)
													->take($pageLimit)
													->get();
													
							$totalCountWarningLetter = WarningLetterRequest::where('final_status', 0)
													->where('approved_status', 1)
													->where('tl_id', $managerId)
													
													->get()->count();
					}
					else if($type == 'issued')
					{
						$documentCollectiondetails  = DB::select( DB::raw("select * from warning_letter_requests
						where final_status=1
						AND tl_id=".$managerId." AND warning_letter_status=1 AND
						id in (select max(id) from warning_letter_requests group by emp_id);") );
						$newResult=array();
						foreach($documentCollectiondetails as $value)
						{
							$newResult[]=$value->id;
						}
						$warningLettersData = WarningLetterRequest::
						whereIn('id',$newResult)
						->orderBy('id', 'desc')	
						->skip($pageNo)
						->take($pageLimit)
						->get();
						
						$totalCountWarningLetter = WarningLetterRequest::
						whereIn('id',$newResult)
						
						->get()->count();
					}
					
					$warningLetterArray = array();
					$index=0;
					foreach($warningLettersData as $list)
					{
						$empdata = WarningLetterRequest::where("emp_id",$list->emp_id)->where("id",$list->id)->orderBy('id','DESC')->first();



					$ends = array('th','st','nd','rd','th','th','th','th','th','th');
					if ((($empdata->counter % 100) >= 11) && (($empdata->counter%100) <= 13))
						$wcounter = $empdata->counter. 'th';
					else
					$wcounter = $empdata->counter. $ends[$empdata->counter % 10];
						$warningLetterArray[$index]['employeeId'] = $list->emp_id;
						$warningLetterArray[$index]['employeeName'] = $this->getAgentName($list->emp_id);
						$warningLetterArray[$index]['tenure'] = $this->getTimeFromJoiningAPI($list->emp_id);
						$warningLetterArray[$index]['reason'] = $this->getReasonTxt($list->warning_letter_reason);
						$warningLetterArray[$index]['comments'] = $list->comments;
						if($list->approved_status== 0 && $list->reject_status==0 && $list->status==1 && $list->final_status==0)
						{
							$warningLetterArray[$index]['status'] = $wcounter.' Warning Letter Request Pending for Approval/Reject';
						}
						else if($list->approved_status== 1 && $list->reject_status==0 && $list->status==2 && $list->final_status==0)
						{
							$warningLetterArray[$index]['status'] =  $wcounter.' Warning Letter Request Approved';
						}
						else if($list->approved_status== 0 && $list->reject_status==1 && $list->status==1 && $list->final_status==0)
						{
							$warningLetterArray[$index]['status'] =  $wcounter.' Warning Letter Request Rejected';
						}
						else if($list->warning_letter_status== 1 && $list->final_status==1)
						{
							$warningLetterArray[$index]['status'] =  $wcounter.' Warning Letter Issued';
						}
$warningLetterArray[$index]['rowId'] =  $list->id;
						
						$index++;
					}
					$result['responseCode'] = 200;
							$result['message'] = "Sucessfull.";
				
				$result['result'] = $warningLetterArray;
				$warningLetterTitle = array();
				
				$warningLetterTitle[0]['Title'] = 'Employee Id';
				$warningLetterTitle[0]['Key'] = 'employeeId';
				
				
				$warningLetterTitle[1]['Title'] = 'Employee Name';
				$warningLetterTitle[1]['Key'] = 'employeeName';
				
				$warningLetterTitle[2]['Title'] = 'Tenure';
				$warningLetterTitle[2]['Key'] = 'tenure';
				
				$warningLetterTitle[3]['Title'] = 'Reason';
				$warningLetterTitle[3]['Key'] = 'reason';
				
				$warningLetterTitle[4]['Title'] = 'Status';
				$warningLetterTitle[4]['Key'] = 'status';
				
				$warningLetterTitle[5]['Title'] = 'RowId';
				$warningLetterTitle[5]['Key'] = 'rowId';
				
				$result['resultTitleDetails'] = $warningLetterTitle;	
				$result['totalCount'] = $totalCountWarningLetter;
				
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
	
	public function addChangeSalaryFromStep1(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['managerId']) && $requestParameters['managerId'] != ''&& isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$managerId = $requestParameters['managerId'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					$empDetails = Employee_details::where("tl_id",$managerId)->get();
					$empArray = array();
					$index =0;
					foreach($empDetails as $emp1)
					{
						$empArray[$index]['key'] = $emp1->emp_id;
						$empArray[$index]['value'] = $emp1->emp_name;	
						$index++;
					}
					
					$formAdd = array();
					$formAdd[0]['Title'] = 'Employee Name';
					$formAdd[0]['key'] = 'employee_name';
					$formAdd[0]['type'] = 'dropdown';
					$formAdd[0]['options'] = $empArray;
					$formAdd[0]['value'] = '';
					$formAdd[0]['readOnly'] = 'No';
					
					
					
					
					$result['responseCode'] = 200;
					$result['message'] = "Sucessfull.";
					$result['result'] = $formAdd;
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
	
public function addChangeSalaryFromStep2(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['selectEmpId']) && $requestParameters['selectEmpId'] != ''&&isset($requestParameters['managerId']) && $requestParameters['managerId'] != ''&& isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$managerId = $requestParameters['managerId'];
			$selectEmpId = $requestParameters['selectEmpId'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					$empDetails = Employee_details::where("emp_id",$selectEmpId)->first();
					/* echo "<pre>";
					print_r($empDetails);
					exit; */
					
					$formAdd = array();
					$formAdd[0]['Title'] = 'Employee Id';
					$formAdd[0]['key'] = 'emp_id';
					$formAdd[0]['type'] = 'text';
					$formAdd[0]['options'] = '';
					$formAdd[0]['value'] = $empDetails->emp_id;
					$formAdd[0]['readOnly'] = 'Yes';
					
					
					$formAdd[1]['Title'] = 'Employee Name';
					$formAdd[1]['key'] = 'employee_name';
					$formAdd[1]['type'] = 'text';
					$formAdd[1]['options'] = '';
					$formAdd[1]['value'] = $empDetails->emp_name;
					$formAdd[1]['readOnly'] = 'Yes';
					
					
					$formAdd[2]['Title'] = 'Old Salary';
					$formAdd[2]['key'] = 'old_salary';
					$formAdd[2]['type'] = 'text';
					$formAdd[2]['options'] = '';
					$formAdd[2]['value'] = $empDetails->actual_salary;
					$formAdd[2]['readOnly'] = 'Yes';
					$requestType = array();
					$requestType[0]['key']= 1; 
					$requestType[0]['value']= 'Increment Letter'; 
					
					$requestType[1]['key']= 2; 
					$requestType[1]['value']= 'Mol'; 
					
					$requestType[2]['key']= 3; 
					$requestType[2]['value']= 'Both'; 
					
					$requestType[3]['key']= 4; 
					$requestType[3]['value']= 'Decrement Letter'; 
					
					$formAdd[3]['Title'] = 'Request Type';
					$formAdd[3]['key'] = 'request_type';
					$formAdd[3]['type'] = 'dropdown';
					$formAdd[3]['options'] = $requestType;
					$formAdd[3]['value'] = '';
					$formAdd[3]['readOnly'] = 'No';
					
					$formAdd[4]['Title'] = 'New Salary';
					$formAdd[4]['key'] = 'new_salary';
					$formAdd[4]['type'] = 'text';
					$formAdd[4]['options'] = '';
					$formAdd[4]['value'] = '';
					$formAdd[4]['readOnly'] = 'No';
					
					
					$formAdd[5]['Title'] = 'New Salary Effective From';
					$formAdd[5]['key'] = 'effective_from';
					$formAdd[5]['type'] = 'calender';
					$formAdd[5]['options'] = '';
					$formAdd[5]['value'] = '';
					$formAdd[5]['readOnly'] = 'No';
					
					
					
					$result['responseCode'] = 200;
					$result['message'] = "Sucessfull.";
					$result['result'] = $formAdd;
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
	
public function postChangeSalary(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['selectEmpId']) && $requestParameters['selectEmpId'] != ''&&isset($requestParameters['managerId']) && $requestParameters['managerId'] != ''&& isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$managerId = $requestParameters['managerId'];
			$selectEmpId = $requestParameters['selectEmpId'];
			$old_salary = $requestParameters['old_salary'];
			$new_salary = $requestParameters['new_salary'];
			$request_type = $requestParameters['request_type'];
			$effective_from = $requestParameters['effective_from'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					/* echo "<pre>";
					print_r($requestParameters);
					exit; */
						$createObj = new ChangeSalary();
						$createObj->emp_id = $selectEmpId;
						$createObj->tl_id = $managerId;
						$createObj->oldsalary = $old_salary;
						$createObj->newsalary = $new_salary;
						$createObj->request_type = $request_type;
						$createObj->new_salary_effective_from = $effective_from;
						$createObj->status = 1;
						$createObj->request_status = 1;
						$createObj->created_at = date('Y-m-d H:i:s');
						$createObj->createdby = $managerId;
						
						$createObj->dept_id = Employee_details::where("emp_id",$selectEmpId)->first()->dept_id;
						if($createObj->save())
						{
							$saveObj = new Change_Salary_logs();
							$saveObj->emp_id =$selectEmpId;
							$saveObj->request =$request_type;
							$saveObj->request_event =1;
							$saveObj->user_id =$managerId;
							$saveObj->event_at  =date('Y-m-d');
							$saveObj->save();
							$result['responseCode'] = 200;
							$result['message'] = "Sucessfull.";
						}
						else
						{
							$result['responseCode'] = 500;
							$result['message'] = "Issue to save.";
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
	
	
		
public function addWarningLettersStep1(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['managerId']) && $requestParameters['managerId'] != ''&& isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$managerId = $requestParameters['managerId'];
			
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					$empDetails = Employee_details::where("tl_id",$managerId)->get();
					$empArray = array();
					$index =0;
					foreach($empDetails as $emp1)
					{
						$empArray[$index]['key'] = $emp1->emp_id;
						$empArray[$index]['value'] = $emp1->emp_name;	
						$index++;
					}
					
					$formAdd = array();
					$formAdd[0]['Title'] = 'Employee Name';
					$formAdd[0]['key'] = 'employee_name';
					$formAdd[0]['type'] = 'dropdown';
					$formAdd[0]['options'] = $empArray;
					$formAdd[0]['value'] = '';
					$formAdd[0]['readOnly'] = 'No';
					
					
					
					
					$result['responseCode'] = 200;
					$result['message'] = "Sucessfull.";
					$result['result'] = $formAdd;
					
				
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
	
	protected function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}
	
	public function addWarningLettersStep2(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['selectEmpId']) && $requestParameters['selectEmpId'] != ''&& isset($requestParameters['managerId']) && $requestParameters['managerId'] != ''&& isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$managerId = $requestParameters['managerId'];
			$selectEmpId = $requestParameters['selectEmpId'];
			
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					$empDetails = Employee_details::where("emp_id",$selectEmpId)->first();
					/* echo "<pre>";
					print_r($empDetails);
					exit; */
					
					
				$warningData = WarningLetterRequest::where("emp_id",$selectEmpId)->orderBy('id','DESC')->first();
				$warningReasonsData = WarningLetterReasons::orderBy('id','DESC')->get();
				
				$warningletterEmpDetails = WarningLetterRequest::
				join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
				->select('employee_details.*', 'warning_letter_requests.*')
				->where('warning_letter_requests.emp_id', $selectEmpId)
				->where('warning_letter_requests.warning_letter_status', 1)
				->orderBy('warning_letter_requests.id', 'desc')->first();
				
				$warningletterCount = WarningLetterRequest::
				join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
				->select('employee_details.*', 'warning_letter_requests.*')
				->where('warning_letter_requests.emp_id', $selectEmpId)
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
					$warningEmpData='';	
				}
					
					$formAdd = array();
					$formAdd[0]['Title'] = 'Employee Id';
					$formAdd[0]['key'] = 'emp_id';
					$formAdd[0]['type'] = 'caption';
					$formAdd[0]['options'] = '';
					$formAdd[0]['value'] = $empDetails->emp_id;
					$formAdd[0]['readOnly'] = 'Yes';
					
					
					$formAdd[1]['Title'] = 'Employee Name';
					$formAdd[1]['key'] = 'employee_name';
					$formAdd[1]['type'] = 'caption';
					$formAdd[1]['options'] = '';
					$formAdd[1]['value'] = $empDetails->emp_name;
					$formAdd[1]['readOnly'] = 'Yes';
					
					
					
$count = 2;

if($warningEmpData)
{
if($warningEmpData->warning_letter_status >0)
{
					
					$formAdd[$count]['Title'] = 'Warning Letter Issued By';
					$formAdd[$count]['key'] = 'Warning_Letter_Issued_By';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] = WarningLetterController::getCreatedInfo($warningletterEmpDetailsData->warning_letter_issued_by);
					$formAdd[$count]['readOnly'] = 'Yes';
					$createDate = new DateTime($warningletterEmpDetailsData->warning_letter_issued_on);
					$newdate = $createDate->format('Y-m-d');
					$date = DateTime::createFromFormat('Y-m-d', $newdate);

					$count++;
					$formAdd[$count]['Title'] = 'Warning Letter Issued On';
					$formAdd[$count]['key'] = 'Warning_Letter_Issued_On';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =$date->format('d M, Y');
					$formAdd[$count]['readOnly'] = 'Yes';
					
					
					$count++;
					$formAdd[$count]['Title'] = 'Last Warning Letter Status';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Status';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =$this->ordinal($warningletterCount). " Warning Letter Issued";
					$formAdd[$count]['readOnly'] = 'Yes';
    
					$count++;
					$formAdd[$count]['Title'] = 'Last Warning Letter Comments';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Comments';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =$warningletterEmpDetailsData->warning_letter_comment;
					$formAdd[$count]['readOnly'] = 'Yes';
     
	
}

else
{



if($warningEmpData->approved_status ==0 && $warningEmpData->reject_status ==0)
{
					$formAdd[$count]['Title'] = 'Last Warning Letter Request Status';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_Status';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =WarningLetterController::getStatus($warningEmpData->emp_id,$warningEmpData->id);
					$formAdd[$count]['readOnly'] = 'Yes';
					
						$count++;
						$createDate = new DateTime($warningEmpData->created_at);
						$newdate = $createDate->format('Y-m-d');
						$date = DateTime::createFromFormat('Y-m-d', $newdate);

					$formAdd[$count]['Title'] = 'Last Warning Letter Request On';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_On';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =$date->format('d M, Y');
					$formAdd[$count]['readOnly'] = 'Yes';
     

      
}




if($warningEmpData->approved_status ==1 && $warningEmpData->reject_status ==0)
{

					$formAdd[$count]['Title'] = 'Last Warning Letter Request Status';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_Status';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =WarningLetterController::getStatus($warningEmpData->emp_id,$warningEmpData->id);
					$formAdd[$count]['readOnly'] = 'Yes';
     $count++;
	 $createDate = new DateTime($warningEmpData->created_at);
$newdate = $createDate->format('Y-m-d');
$date = DateTime::createFromFormat('Y-m-d', $newdate);

	 $formAdd[$count]['Title'] = 'Last Warning Letter Request On';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_On';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =$date->format('d M, Y');
					$formAdd[$count]['readOnly'] = 'Yes';
	 $count++;
	  $formAdd[$count]['Title'] = 'Last Warning Letter Request Approved By';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_On';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =WarningLetterController::getCreatedInfo($warningEmpData->approved_reject_by);
					$formAdd[$count]['readOnly'] = 'Yes';
					$createDate = new DateTime($warningEmpData->approved_reject_on);
$newdate = $createDate->format('Y-m-d');
$date = DateTime::createFromFormat('Y-m-d', $newdate);

					 $count++;
	  $formAdd[$count]['Title'] = 'Last Warning Letter Request Approved On';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_Approved_On';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =$date->format('d M, Y');
					$formAdd[$count]['readOnly'] = 'Yes';

}




if($warningEmpData->approved_status ==0 && $warningEmpData->reject_status ==1)
{
					$formAdd[$count]['Title'] = 'Last Warning Letter Request Status';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_Status';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =WarningLetterController::getStatus($warningEmpData->emp_id,$warningEmpData->id);
					$formAdd[$count]['readOnly'] = 'Yes';
   
	 $createDate = new DateTime($warningEmpData->created_at);
$newdate = $createDate->format('Y-m-d');
$date = DateTime::createFromFormat('Y-m-d', $newdate);
					$count++;
					$formAdd[$count]['Title'] = 'Last Warning Letter Request On';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_Status';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =$date->format('d M, Y');
					$formAdd[$count]['readOnly'] = 'Yes';
	 
     
	 
	 $count++;
					$formAdd[$count]['Title'] = 'Last Warning Letter Request Rejected By';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_Rejected_By';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =WarningLetterController::getCreatedInfo($warningEmpData->approved_reject_by);
					$formAdd[$count]['readOnly'] = 'Yes';
					$createDate = new DateTime($warningEmpData->approved_reject_on);
$newdate = $createDate->format('Y-m-d');
$date = DateTime::createFromFormat('Y-m-d', $newdate);

					
		 $count++;
					$formAdd[$count]['Title'] = 'Last Warning Letter Request Rejected On';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_Rejected_On';
					$formAdd[$count]['type'] = 'caption';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =$date->format('d M, Y');
					$formAdd[$count]['readOnly'] = 'Yes';	
      
}






}



 $count++;

					$wLetterReason = array();
					$wLetterReason[0]['key'] = 22 ;
					$wLetterReason[0]['value'] = 'COMPLIANCE BREACH'; 
					
					$wLetterReason[1]['key'] = 2 ;
					$wLetterReason[1]['value'] = 'DISCIPLINE/PUNCTUALITY'; 
					
					$wLetterReason[2]['key'] = 1 ;
					$wLetterReason[2]['value'] = 'Performence'; 
					
					
					$formAdd[$count]['Title'] = 'Warning Letter Reason';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_Rejected_On';
					$formAdd[$count]['type'] = 'dropdown';
					$formAdd[$count]['options'] = $wLetterReason;
					$formAdd[$count]['value'] =$request->id;
					$formAdd[$count]['readOnly'] = 'No';	

  
 $count++;
$formAdd[$count]['Title'] = 'Comments';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_Rejected_On';
					$formAdd[$count]['type'] = 'textarea';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] =$warningEmpData->comments;
					$formAdd[$count]['readOnly'] = 'No';	







	
	
	}
else
{


$count++;
$wLetterReason = array();
					$wLetterReason[0]['key'] = 22 ;
					$wLetterReason[0]['value'] = 'COMPLIANCE BREACH'; 
					
					$wLetterReason[1]['key'] = 2 ;
					$wLetterReason[1]['value'] = 'DISCIPLINE/PUNCTUALITY'; 
					
					$wLetterReason[2]['key'] = 1 ;
					$wLetterReason[2]['value'] = 'Performence'; 
					
					$formAdd[$count]['Title'] = 'Warning Letter Reason';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_Rejected_On';
					$formAdd[$count]['type'] = 'dropdown';
					$formAdd[$count]['options'] = $wLetterReason;
					$formAdd[$count]['value'] =$request->id;
					$formAdd[$count]['readOnly'] = 'No';	
					
					
					$count++;
$formAdd[$count]['Title'] = 'Comments';
					$formAdd[$count]['key'] = 'Last_Warning_Letter_Request_Rejected_On';
					$formAdd[$count]['type'] = 'textarea';
					$formAdd[$count]['options'] = '';
					$formAdd[$count]['value'] ='';
					$formAdd[$count]['readOnly'] = 'No';	


}
					
					sort($formAdd);
					
					$result['responseCode'] = 200;
					$result['message'] = "Sucessfull.";
					$result['result'] = $formAdd;
				
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
	
	public function postWarningLettersRequest(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['managerId']) && $requestParameters['managerId'] != ''&& isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['selectEmpId']) && $requestParameters['selectEmpId'] != '' && isset($requestParameters['warning_letter_reason']) && $requestParameters['warning_letter_reason'] != '' && isset($requestParameters['comments']) && $requestParameters['comments'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$managerId = $requestParameters['managerId'];
			$selectEmpId = $requestParameters['selectEmpId'];
			$warning_letter_reason = $requestParameters['warning_letter_reason'];
			$comments = $requestParameters['comments'];
			
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					$warningData = WarningLetterRequest::where("emp_id",$selectEmpId)->orderBy('id','DESC')->first();

			
	
			if($warningData)
			{	
				
				
	
				$warningletterRequest = new WarningLetterRequest();
				$warningletterRequest->emp_id = $selectEmpId;
				$warningletterRequest->dept_id =Employee_details::where("emp_id",$selectEmpId)->first()->dept_id;
				$warningletterRequest->tl_id =$managerId;
				$warningletterRequest->status =1;

				if($warningData->reject_status==1)
				{		
					$warningletterRequest->counter =$warningData->counter;
				}
				else{
					$warningletterRequest->counter =$warningData->counter+1;
				}

				$warningletterRequest->createdby =$managerId;
				$warningletterRequest->warning_letter_reason =$warning_letter_reason;
				$warningletterRequest->comments =$comments;

	
				$warningletterRequest->save();
	
				

						$result['responseCode'] = 200;
						$result['message'] = "Data Saved Successfully.";				
			}
			else
			{
				
				
				
	
				$warningletterRequest = new WarningLetterRequest();
				$warningletterRequest->emp_id = $selectEmpId;
				$warningletterRequest->dept_id =Employee_details::where("emp_id",$selectEmpId)->first()->dept_id;
				$warningletterRequest->tl_id =$managerId;
				$warningletterRequest->status =1;
				$warningletterRequest->createdby =$managerId;
				$warningletterRequest->warning_letter_reason =$warning_letter_reason;
				$warningletterRequest->comments =$comments;

	
				$warningletterRequest->save();
	
				$result['responseCode'] = 200;
						$result['message'] = "Data Saved Successfully.";			
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
	
	
	public function wariningLetterProgressStatus(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != '' && isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['selected_id']) && $requestParameters['selected_id'] != ''&& isset($requestParameters['row_id']) && $requestParameters['row_id'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$selected_id = $requestParameters['selected_id'];
			$row_id = $requestParameters['row_id'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
										$changeSalaryProcess = $this->warningLettersummaryTabWithFullViewAjaxAPI($selected_id,$row_id);
					
				$result = $changeSalaryProcess;
					
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
	
	
	 public function warningLettersummaryTabWithFullViewAjaxAPI($empId,$id)
	   {
		    
		    //$documentCollectionDetails = EmpOffline::where("id",$documentCollectId)->first();
			//$documentCollectionDetails = Employee_details::orderBy("id","DESC")->where('emp_id',$empId)->where("offline_status",1)->first();
			//$changesalarydetails = ChangeSalary::where("emp_id",$empId)->orderBy('id','DESC')->first();

			$warningLetterdetails = WarningLetterRequest::where("emp_id",$empId)->where("id",$id)->orderBy('id','DESC')->first();


			$documentCollectionDetails = WarningLetterRequest::
			join('employee_details', 'employee_details.emp_id', '=', 'warning_letter_requests.emp_id')
			->select('employee_details.*', 'warning_letter_requests.*')
			->where('warning_letter_requests.emp_id', $empId)
			->where('warning_letter_requests.id', $id)
			->orderBy('warning_letter_requests.id', 'desc')->first();


			


			//return $changesalarydetails->approvedrejectstatus;

			if(!$warningLetterdetails)
			{
				$reqstatus=0;
			}
			else{
				$reqstatus=$warningLetterdetails->status;
			}

			
			/*
			*upload document values with label
			*start code
			*/
			/*
			*upload document values with label
			*end code
			*/
			
			/*
			*Define All steps
			*start code
			*/

			$completedStep = 0;
			$OnboardingProgress = '';
			$stepsAll = array();
			/*Step1*/
		    




			if($warningLetterdetails)
			{
					
				
				
				$stepsAll[0]['name'] = 'Approved or Reject Request'; 
				if($reqstatus == 1 && $warningLetterdetails->approved_status==0 && $warningLetterdetails->reject_status==0)
				{
					$stepsAll[0]['stage'] = 'inprogress';
					$stepsAll[0]['Tab'] = 'active'; 
				}
				else
				{
					$completedStep++;
					$stepsAll[0]['stage'] = 'active'; 
					$OnboardingProgress = 'Approved or Reject Request';
					$stepsAll[0]['Tab'] = 'active'; 
				}
				$stepsAll[0]['slagURL'] = 'tab2'; 
				//$stepsAll[0]['tab'] = 'active'; 
				$stepsAll[0]['onclick'] = 'tab2Panel();'; 
				
				$OnboardingProgress = 'Approved or Reject Request';
				/*Step1*/	
				
				
				
				
				
				
				
				
				
				/*Step2*/
					$stepsAll[1]['name'] = 'Upload Warning Letter'; 
					if($reqstatus == 2 && $warningLetterdetails->approved_status==1)
					{
					$stepsAll[1]['stage'] = 'inprogress'; 
					$stepsAll[1]['Tab'] = 'active';
					}
					else if($reqstatus == 3 && $warningLetterdetails->warning_letter_status==1)
					{
					$completedStep++;
					$stepsAll[1]['stage'] = 'active'; 
					$OnboardingProgress = 'Upload Warning Letter';
					$stepsAll[1]['Tab'] = 'active';
					}
					else 
					{
					$stepsAll[1]['stage'] = 'pending'; 
					$stepsAll[1]['Tab'] = 'disabled-tab';  

					}
					$stepsAll[1]['slagURL'] = 'tab3'; 
					$stepsAll[1]['onclick'] = 'tab3Panel();'; 

					/*Step2*/



					/*Step3*/
					$stepsAll[2]['name'] = 'Final'; 
					if($reqstatus==5 && $warningLetterdetails->first_warning_letter_status==1 && $warningLetterdetails->second_warning_letter_status==1 && $warningLetterdetails->third_warning_letter_status==1)
					{
						$stepsAll[2]['stage'] = 'inprogress'; 
						$stepsAll[2]['Tab'] = 'active'; 
					}
					else if($reqstatus==3 && $warningLetterdetails->final_status==1)
					{
						$completedStep++;
						$stepsAll[2]['stage'] = 'active'; 
						$OnboardingProgress = 'Final';
						$stepsAll[2]['Tab'] = 'active';
					}
					else 
					{
						$stepsAll[2]['stage'] = 'pending'; 
						$stepsAll[2]['Tab'] = 'disabled-tab';  

					}
					$stepsAll[2]['slagURL'] = 'tab4'; 
					$stepsAll[2]['onclick'] = 'tab4Panel();'; 
					/*Step3*/

					// if($changesalarydetails->request_type==1)
					// {
					// 	$completedStep++;
					// }



					

					// /*Step4*/
					// $stepsAll[3]['name'] = 'Final'; 
					// if($reqstatus==41)
					// {
					// $stepsAll[3]['stage'] = 'inprogress'; 
					// $stepsAll[3]['Tab'] = 'active'; 
					// }
					// else if($reqstatus>4)
					// {
					// $completedStep++;
					// $stepsAll[3]['stage'] = 'active'; 
					// $OnboardingProgress = 'Final';
					// $stepsAll[3]['Tab'] = 'active';
					// }
					// else
					// {
					// $stepsAll[3]['stage'] = 'pending'; 
					// $stepsAll[3]['Tab'] = 'disabled-tab'; 
					// }
					// $stepsAll[3]['slagURL'] = 'tab5'; 
					// $stepsAll[3]['onclick'] = 'tab5Panel();'; 			
					/*Step4*/

					// if($changesalarydetails->request_type==2)
					// {
					// 	$completedStep++;
					// }



					/*Step5*/
					// $stepsAll[4]['name'] = 'Change Salary Request Confirmed'; 
					// //echo $documentCollectionDetails->condition_leaving;
					// if($reqstatus==5)
					// {

					// $stepsAll[4]['stage'] = 'inprogress'; 
					// $stepsAll[4]['Tab'] = 'active'; 
					// }
					// else if($reqstatus>5)
					// {
					// $completedStep++;
					// $stepsAll[4]['stage'] = 'active'; 
					// $OnboardingProgress = 'Change Salary Request Confirmed';
					// $stepsAll[4]['Tab'] = 'active'; 
					// }
					// else
					// {
					// $stepsAll[4]['stage'] = 'pending'; 
					// $stepsAll[4]['Tab'] = 'disabled-tab'; 
					// }
					// $stepsAll[4]['slagURL'] = 'tab6';  
					// $stepsAll[4]['onclick'] = 'tab6Panel();'; 

					




			}
		    
			
			$totalStep = 3;
			$p = $completedStep/$totalStep;
			$percentange = round($p*100);

			//return $percentange;
			
						
			$result['result']['steps'] = $stepsAll;
			$result['result']['percentange'] = $percentange;
			return $result;
	   }
}