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
use DateTime;
use Crypt;

class V4ChangeSalaryController extends Controller
{
	
	public function changeSalaryTabs(Request $request)
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
				$changeSalaryList[2]['Values'] = 'Increment/Decrement';
				$changeSalaryList[3]['Values'] = 'Mol';
				$changeSalaryList[4]['Values'] = 'Change Salary Complete';
				
				$changeSalaryList[0]['Keys'] = 'All';
				$changeSalaryList[1]['Keys'] = 'Requested';
				$changeSalaryList[2]['Keys'] = 'IncrementDe';
				$changeSalaryList[3]['Keys'] = 'Mol';
				$changeSalaryList[4]['Keys'] = 'final';
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

	
	public function changeSalaryList(Request $request)
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
						$changeSalaryList = ChangeSalary::where("tl_id",$managerId)->skip($pageNo)->take($pageLimit)->get();
						$totalChangeSalaryList = ChangeSalary::where("tl_id",$managerId)->get()->count();
					}
					else if($type == 'Requested')
					{
							$changeSalaryList = ChangeSalary::where("tl_id",$managerId)
							->where('incrementstatus', 0)
							->where('finalstatus', 0)
							->where('approvedrejectstatus', 0)
							->skip($pageNo)->take($pageLimit)
							->get();
							
							
							$totalChangeSalaryList = ChangeSalary::where("tl_id",$managerId)
							->where('incrementstatus', 0)
							->where('finalstatus', 0)
							->where('approvedrejectstatus', 0)
							
							->get()->count();
					}
					
					else if($type == 'IncrementDe')
					{
							$changeSalaryList = ChangeSalary::where("tl_id",$managerId)
							->where('request_type', 1)
							->where('incrementstatus', 0)
							->where('approvedrejectstatus', 1)
													
							->orWhere('request_type', 3)
							->where('incrementstatus', 0)
							->where('approvedrejectstatus', 1)
													
							->orWhere('request_type', 4)
							->where('incrementstatus', 0)
							->where('approvedrejectstatus', 1)
							->skip($pageNo)->take($pageLimit)
							->get();
							
							
							$totalChangeSalaryList = ChangeSalary::where("tl_id",$managerId)
							->where('request_type', 1)
							->where('incrementstatus', 0)
							->where('approvedrejectstatus', 1)
													
							->orWhere('request_type', 3)
							->where('incrementstatus', 0)
							->where('approvedrejectstatus', 1)
													
							->orWhere('request_type', 4)
							->where('incrementstatus', 0)
							->where('approvedrejectstatus', 1)
							
							->get()->count();
					}
					else if($type == 'Mol')
					{
						$changeSalaryList = ChangeSalary::where("tl_id",$managerId)
							->where('request_type', 2)
							->where('molstatus', 0)
							->where('approvedrejectstatus', 1)

							->orWhere('request_type', 3)
							->where('molstatus', 0)
							->where('approvedrejectstatus', 1)
							->skip($pageNo)->take($pageLimit)
							->get();
							
							
							$totalChangeSalaryList = ChangeSalary::where("tl_id",$managerId)
							->where('request_type', 2)
							->where('molstatus', 0)
							->where('approvedrejectstatus', 1)

							->orWhere('request_type', 3)
							->where('molstatus', 0)
							->where('approvedrejectstatus', 1)
							
							->get()->count();
					}
					else if($type == 'final')
					{
						$changeSalaryList = ChangeSalary::where("tl_id",$managerId)
							->where('request_type', 1)
							->where('incrementstatus', 1)
							->where('approvedrejectstatus', 1)

							->orWhere('request_type', 2)
							->where('approvedrejectstatus', 1)

							->orWhere('request_type', 3)
							->where('approvedrejectstatus', 1)

							->orWhere('request_type', 4)
							->where('incrementstatus', 1)
							->where('approvedrejectstatus', 1)
							->skip($pageNo)->take($pageLimit)
							->get();
							
							
							$totalChangeSalaryList = ChangeSalary::where("tl_id",$managerId)
							->where('request_type', 1)
							->where('incrementstatus', 1)
							->where('approvedrejectstatus', 1)

							->orWhere('request_type', 2)
							->where('approvedrejectstatus', 1)

							->orWhere('request_type', 3)
							->where('approvedrejectstatus', 1)

							->orWhere('request_type', 4)
							->where('incrementstatus', 1)
							->where('approvedrejectstatus', 1)
							
							->get()->count();
					}
					$ChangeSalaryArray = array();
					$index=0;
					foreach($changeSalaryList as $list)
					{
						
						$ChangeSalaryArray[$index]['empId']=$list->emp_id;
						$ChangeSalaryArray[$index]['agentName']=$this->getAgentName($list->emp_id);
						$requestType = $list->request_type;
						if($requestType ==1)
						{	
							$ChangeSalaryArray[$index]['requestType']='Increment Request';
						}
						else if($requestType ==2)
						{
							$ChangeSalaryArray[$index]['requestType']='MOL Request';
						}
						else if($requestType ==3)
						{
							$ChangeSalaryArray[$index]['requestType']='Increment & MOL (Both) Request';
						}
						else if($requestType ==4)
						{
							$ChangeSalaryArray[$index]['requestType']='Decrement Request';
						}
						else{
							$ChangeSalaryArray[$index]['requestType']='Not Define';
						}

						
						$ChangeSalaryArray[$index]['status']=$list->status;
						$ChangeSalaryArray[$index]['createdAt']=date("dMY",strtotime($list->created_at));
						$ChangeSalaryArray[$index]['tlId']=$list->tl_id;
						$ChangeSalaryArray[$index]['tlName']=$list->tl_id;
						$ChangeSalaryArray[$index]['requestStatus']=$list->request_status;
						$ChangeSalaryArray[$index]['newSalary']=$list->newsalary;
						$ChangeSalaryArray[$index]['oldSalary']=$list->oldsalary;
						$ChangeSalaryArray[$index]['comments']=$list->comments;
						$ChangeSalaryArray[$index]['incrementComment']=$list->increment_comment;
						$approvedrejectstatus = $list->approvedrejectstatus;
						$incrementstatus = $list->incrementstatus;
						$molstatus = $list->molstatus;
						if($approvedrejectstatus==0)
						{
							
							$ChangeSalaryArray[$index]['approvedRejectStatus']='Request Initeated';
						}
						else if($approvedrejectstatus==1 && $incrementstatus==0 && $molstatus==0)
						{
						
							$ChangeSalaryArray[$index]['approvedRejectStatus']='Request Approved';
						}
						else if($approvedrejectstatus==2 && $incrementstatus==0 && $molstatus==0)
						{
						
							$ChangeSalaryArray[$index]['approvedRejectStatus']='Request Rejected';
						}
						else
						{
							$ChangeSalaryArray[$index]['approvedRejectStatus']='Request Initeated';
						}



						if($requestType==1 && $incrementstatus==2 && $molstatus==0)
						{
							$ChangeSalaryArray[$index]['incrementStatus']='Increment Rejected';
						}
						else if($requestType==4 && $incrementstatus==2 && $molstatus==0)
						{
							
							$ChangeSalaryArray[$index]['incrementStatus']='Decrement Rejected';
						}
						else if($requestType==1 && $incrementstatus==1)
						{
							$ChangeSalaryArray[$index]['incrementStatus']='Increment Done (All Process done)';
						}
						else if($requestType==4 && $incrementstatus==1)
						{
							$ChangeSalaryArray[$index]['incrementStatus']='Decrement Done (All Process done)';
						}
						else if($requestType==1 && $approvedrejectstatus==1 && $incrementstatus==0)
							{
								$ChangeSalaryArray[$index]['incrementStatus']='Increment Pending';
							}
						else if($requestType==4 && $approvedrejectstatus==1 && $incrementstatus==0)
							{
								$ChangeSalaryArray[$index]['incrementStatus']='Decrement Pending';
							}
						else
						{
								$ChangeSalaryArray[$index]['incrementStatus']='Pending';
						}
						
						if($requestType==2 && $molstatus==1)
						{
							$ChangeSalaryArray[$index]['molStatus']= 'MOL Done (All Process done)';
						}
						else if($requestType==3 && $molstatus==1 && $incrementstatus==1)
						{
							$ChangeSalaryArray[$index]['molStatus']= 'Increment & MOL Both Done (All Process done)';
							
						}
						else if(($requestType==2 || $requestType==3) && $molstatus==0)
						{
							$ChangeSalaryArray[$index]['molStatus']=	'MOL Pending';
							
						}
						else
						{
							$ChangeSalaryArray[$index]['molStatus']=	'MOL Pending';
						}
						
						
						$ChangeSalaryArray[$index]['finalComment']=$list->finalcomment;
						//$ChangeSalaryArray[$index]['finalStatus']=$list->finalstatus;
						$ChangeSalaryArray[$index]['createdBy']=$this->getUserName($list->createdby);
						$ChangeSalaryArray[$index]['incrementBy']=$this->getUserName($list->incrementby);
						$ChangeSalaryArray[$index]['incrementon']=$list->incrementon;
						$ChangeSalaryArray[$index]['approvedRejectBy']=$this->getUserName($list->approvedrejectby);
						$ChangeSalaryArray[$index]['approvedRejecton']=$list->approvedrejecton;
						$ChangeSalaryArray[$index]['behalfOffUser']=$this->getUserName($list->behalfoff_user);
						//$ChangeSalaryArray[$index]['finalSalaryStatus']=$list->final_salary_status;
						$ChangeSalaryArray[$index]['newSalaryEffectiveFrom']=$list->new_salary_effective_from;
						$ChangeSalaryArray[$index]['rowId']=$list->id;
						$index++;
					}
					$result['responseCode'] = 200;
							$result['message'] = "Sucessfull.";
				
				$result['result'] = $ChangeSalaryArray;
				$salaryChangeTitle = array();
				$salaryChangeDetails = array();
				$salaryChangeTitle[0]['Title'] = 'Agent Code';
				$salaryChangeTitle[0]['Key'] = 'empId';
				
				
				$salaryChangeTitle[1]['Title'] = 'Agent Name';
				$salaryChangeTitle[1]['Key'] = 'agentName';
				
				$salaryChangeTitle[2]['Title'] = 'Request Type';
				$salaryChangeTitle[2]['Key'] = 'requestType';
				
				$salaryChangeTitle[3]['Title'] = 'New Salary';
				$salaryChangeTitle[3]['Key'] = 'newSalary';
				
				$salaryChangeTitle[4]['Title'] = 'Old Salary';
				$salaryChangeTitle[4]['Key'] = 'oldSalary';
				
				
				$salaryChangeTitle[5]['Title'] = 'Approved Reject Status';
				$salaryChangeTitle[5]['Key'] = 'approvedRejectStatus';
				
				
				$salaryChangeTitle[6]['Title'] = 'Increment Status';
				$salaryChangeTitle[6]['Key'] = 'incrementStatus';
				
				
				
				$salaryChangeTitle[7]['Title'] = 'Mol Status';
				$salaryChangeTitle[7]['Key'] = 'molStatus';
				
				
				$result['resultTitleList'] = $salaryChangeTitle;	
				
								$salaryChangeDetails[0]['Title'] = 'Agent Code';
				$salaryChangeDetails[0]['Key'] = 'empId';
				
				
				$salaryChangeDetails[1]['Title'] = 'Agent Name';
				$salaryChangeDetails[1]['Key'] = 'agentName';
				
				$salaryChangeDetails[2]['Title'] = 'Request Type';
				$salaryChangeDetails[2]['Key'] = 'requestType';
				
				$salaryChangeDetails[3]['Title'] = 'New Salary';
				$salaryChangeDetails[3]['Key'] = 'newSalary';
				
				$salaryChangeDetails[4]['Title'] = 'Old Salary';
				$salaryChangeDetails[4]['Key'] = 'oldSalary';
				
				
				$salaryChangeDetails[5]['Title'] = 'Approved Reject Status';
				$salaryChangeDetails[5]['Key'] = 'approvedRejectStatus';
				
				
				$salaryChangeDetails[6]['Title'] = 'Increment Status';
				$salaryChangeDetails[6]['Key'] = 'incrementStatus';
				
				
				
				$salaryChangeDetails[7]['Title'] = 'Mol Status';
				$salaryChangeDetails[7]['Key'] = 'molStatus';
				
				
				$salaryChangeDetails[8]['Title'] = 'Created By';
				$salaryChangeDetails[8]['Key'] = 'createdBy';
				
				
				$salaryChangeDetails[9]['Title'] = 'Increment By';
				$salaryChangeDetails[9]['Key'] = 'incrementBy';
				
				$salaryChangeDetails[10]['Title'] = 'Approved Reject By';
				$salaryChangeDetails[10]['Key'] = 'approvedRejectBy';
				
				$salaryChangeDetails[11]['Title'] = 'Approved Rejecton';
				$salaryChangeDetails[11]['Key'] = 'approvedRejecton';
				
				$salaryChangeDetails[12]['Title'] = 'Behalf Off User';
				$salaryChangeDetails[12]['Key'] = 'behalfOffUser';
				$salaryChangeDetails[13]['Title'] = 'New Salary Effective From';
				$salaryChangeDetails[13]['Key'] = 'newSalaryEffectiveFrom';
				$salaryChangeDetails[14]['Title'] = 'New Salary Effective From';
				$salaryChangeDetails[14]['Key'] = 'newSalaryEffectiveFrom';
				$salaryChangeDetails[15]['Title'] = 'Comments';
				$salaryChangeDetails[15]['Key'] = 'comments';
				
				$salaryChangeDetails[16]['Title'] = 'Final Comment';
				$salaryChangeDetails[16]['Key'] = 'finalComment';
				
					$salaryChangeDetails[17]['Title'] = 'RowId';
				$salaryChangeDetails[17]['Key'] = 'rowId';
				$result['resultTitleDetails'] = $salaryChangeDetails;	
				$result['totalCount'] = $totalChangeSalaryList;
				
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
	
public function addWarningLetters(Request $request)
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
					echo "<pre>";
					print_r($requestParameters);
					exit; 
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
	
	
	public function changeSalaryProgressStatus(Request $request)
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
										$changeSalaryProcess = $this->offBoardsummaryTabWithFullViewAjaxAPI($selected_id,$row_id);
					
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
	
	
	public function offBoardsummaryTabWithFullViewAjaxAPI($empId,$rowid)
	   {
		    
		    //$documentCollectionDetails = EmpOffline::where("id",$documentCollectId)->first();
			$documentCollectionDetails = Employee_details::orderBy("id","DESC")->where('emp_id',$empId)->first();
			
			$changesalarydetails = ChangeSalary::where("emp_id",$empId)->where("id",$rowid)->orderBy('id','DESC')->first();

			

			if(!$changesalarydetails)
			{
				$reqstatus=0;
			}
			else{
				$reqstatus=$changesalarydetails->request_status;
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

			$completedStep = 1;
			$OnboardingProgress = '';
			$stepsAll = array();
			/*Step1*/
		    $stepsAll[0]['name'] = 'Initiate Request'; 
			if($reqstatus == 0)
			{
				$stepsAll[0]['stage'] = 'inprogress';
				$stepsAll[0]['Tab'] = 'active'; 	
				
				



				
				$stepsAll[1]['name'] = 'Approved/Reject Request';				
				$stepsAll[1]['stage'] = 'pending'; 
				$stepsAll[1]['Tab'] = 'disabled-tab'; 				
				$stepsAll[1]['slagURL'] = 'tab3'; 
				$stepsAll[1]['onclick'] = 'tab3Panel();'; 


				$stepsAll[2]['name'] = 'Increment Letter'; 
					
					$stepsAll[2]['stage'] = 'pending'; 
					$stepsAll[2]['Tab'] = 'disabled-tab';  

					
					$stepsAll[2]['slagURL'] = 'tab4'; 
					$stepsAll[2]['onclick'] = 'tab4Panel();'; 




					$stepsAll[3]['name'] = 'MOL Modification'; 
					
					$stepsAll[3]['stage'] = 'pending'; 
					$stepsAll[3]['Tab'] = 'disabled-tab'; 
					
					$stepsAll[3]['slagURL'] = 'tab5'; 
					$stepsAll[3]['onclick'] = 'tab5Panel();'; 	
					
					


					$stepsAll[4]['name'] = 'Change Salary Request Confirmed'; 
					
					$stepsAll[4]['stage'] = 'pending'; 
					$stepsAll[4]['Tab'] = 'disabled-tab'; 
					
					$stepsAll[4]['slagURL'] = 'tab6';  
					$stepsAll[4]['onclick'] = 'tab6Panel();'; 

					










			}
			else
			{
				$completedStep++;
				$stepsAll[0]['stage'] = 'active'; 
				$OnboardingProgress = 'Initiate Request';
				$stepsAll[0]['Tab'] = 'active'; 
			}
			$stepsAll[0]['slagURL'] = 'tab2'; 
			//$stepsAll[0]['tab'] = 'active'; 
			$stepsAll[0]['onclick'] = 'tab2Panel();'; 
			
			$OnboardingProgress = 'Initiate Request';
			/*Step1*/





			if($changesalarydetails)
			{
					/*Step2*/
					$stepsAll[1]['name'] = 'Approved/Reject Request'; 
					if($reqstatus == 1)
					{
					$stepsAll[1]['stage'] = 'inprogress'; 
					$stepsAll[1]['Tab'] = 'active';
					}
					else if($reqstatus > 1)
					{
					$completedStep++;
					$stepsAll[1]['stage'] = 'active'; 
					$OnboardingProgress = 'Approved/Reject Request';
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
					$stepsAll[2]['name'] = 'Increment/Decrement'; 
					if($changesalarydetails->approvedrejectstatus==1 && $changesalarydetails->incrementstatus==0 && ($changesalarydetails->request_type==1 || $changesalarydetails->request_type==3 || $changesalarydetails->request_type==4))
					{
						$stepsAll[2]['stage'] = 'inprogress'; 
						$stepsAll[2]['Tab'] = 'active'; 
					}
					else if($changesalarydetails->incrementstatus==1 || $changesalarydetails->incrementstatus==2)
					{
						$completedStep++;
						$stepsAll[2]['stage'] = 'active'; 
						$OnboardingProgress = 'Increment/Decrement';
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

					if($changesalarydetails->request_type==1 || $changesalarydetails->request_type==4)
					{
						$completedStep++;
					}
					




					/*Step4*/
					$stepsAll[3]['name'] = 'MOL Modification'; 

					if($changesalarydetails->request_type==2)
					{
						if($changesalarydetails->approvedrejectstatus==1 && $changesalarydetails->molstatus==0)
						{
							$stepsAll[3]['stage'] = 'inprogress'; 
							$stepsAll[3]['Tab'] = 'active';
						}
						else if($changesalarydetails->molstatus==1)
						{
							$completedStep++;
							$stepsAll[3]['stage'] = 'active'; 
							$OnboardingProgress = 'MOL Modification';
							$stepsAll[3]['Tab'] = 'active';
						}
						else
						{
							$stepsAll[3]['stage'] = 'pending'; 
							$stepsAll[3]['Tab'] = 'disabled-tab'; 
						}
					}
					elseif($changesalarydetails->request_type==3)
					{
						if($changesalarydetails->approvedrejectstatus==1 && $changesalarydetails->molstatus==0 && ($changesalarydetails->incrementstatus==1 || $changesalarydetails->incrementstatus==2))
						{
							$stepsAll[3]['stage'] = 'inprogress'; 
							$stepsAll[3]['Tab'] = 'active';
						}
						else if($changesalarydetails->molstatus==1)
						{
							$completedStep++;
							$stepsAll[3]['stage'] = 'active'; 
							$OnboardingProgress = 'MOL Modification';
							$stepsAll[3]['Tab'] = 'active';
						}
						else
						{
							$stepsAll[3]['stage'] = 'pending'; 
							$stepsAll[3]['Tab'] = 'disabled-tab'; 
						}
					}
					else
					{
						$stepsAll[3]['stage'] = 'pending'; 
						$stepsAll[3]['Tab'] = 'disabled-tab'; 
					}



					// if($changesalarydetails->approvedrejectstatus==1 && $changesalarydetails->molstatus==0 && ($changesalarydetails->request_type==2 || $changesalarydetails->request_type==3))
					// {
					// 	$stepsAll[3]['stage'] = 'inprogress'; 
					// 	$stepsAll[3]['Tab'] = 'active'; 
					// }
					// else if($changesalarydetails->molstatus==1)
					// {
					// 	$completedStep++;
					// 	$stepsAll[3]['stage'] = 'active'; 
					// 	$OnboardingProgress = 'MOL Modification';
					// 	$stepsAll[3]['Tab'] = 'active';
					// }
					// else
					// {
					// 	$stepsAll[3]['stage'] = 'pending'; 
					// 	$stepsAll[3]['Tab'] = 'disabled-tab'; 
					// }

					$stepsAll[3]['slagURL'] = 'tab5'; 
					$stepsAll[3]['onclick'] = 'tab5Panel();'; 			
					/*Step4*/

					if($changesalarydetails->request_type==2)
					{
						$completedStep++;
					}



					/*Step5*/
					$stepsAll[4]['name'] = 'Request Confirmation'; 
					//echo $documentCollectionDetails->condition_leaving;
					// if($changesalarydetails->approvedrejectstatus==1 && ($changesalarydetails->molstatus==1 || $changesalarydetails->incrementstatus==1) && $changesalarydetails->finalstatus==0)
					// {

					// $stepsAll[4]['stage'] = 'inprogress'; 
					// $stepsAll[4]['Tab'] = 'active'; 
					// }
					// else if($changesalarydetails->approvedrejectstatus==1 && ($changesalarydetails->molstatus==1 || $changesalarydetails->incrementstatus==1)  && ($changesalarydetails->finalstatus==1 || $changesalarydetails->finalstatus==2))
					// {
					// $completedStep++;
					// $stepsAll[4]['stage'] = 'active'; 
					// $OnboardingProgress = 'Request Confirmation';
					// $stepsAll[4]['Tab'] = 'active'; 
					// }
					// else
					// {
					// $stepsAll[4]['stage'] = 'pending'; 
					// $stepsAll[4]['Tab'] = 'disabled-tab'; 
					// }




					if($changesalarydetails->request_type==1)
					{
						if($changesalarydetails->approvedrejectstatus==1 && $changesalarydetails->incrementstatus==1)
						{
						$completedStep++;
						$stepsAll[4]['stage'] = 'active'; 
						$OnboardingProgress = 'Request Confirmation';
						$stepsAll[4]['Tab'] = 'active'; 
						}
						else
						{
						$stepsAll[4]['stage'] = 'pending'; 
						$stepsAll[4]['Tab'] = 'disabled-tab'; 
						}
					}
					if($changesalarydetails->request_type==4)
					{
						if($changesalarydetails->approvedrejectstatus==1 && $changesalarydetails->incrementstatus==1)
						{
						$completedStep++;
						$stepsAll[4]['stage'] = 'active'; 
						$OnboardingProgress = 'Request Confirmation';
						$stepsAll[4]['Tab'] = 'active'; 
						}
						else
						{
						$stepsAll[4]['stage'] = 'pending'; 
						$stepsAll[4]['Tab'] = 'disabled-tab'; 
						}
					}

					if($changesalarydetails->request_type==2)
					{
						if($changesalarydetails->approvedrejectstatus==1 && $changesalarydetails->molstatus==1)
						{
						$completedStep++;
						$stepsAll[4]['stage'] = 'active'; 
						$OnboardingProgress = 'Request Confirmation';
						$stepsAll[4]['Tab'] = 'active'; 
						}
						else
						{
						$stepsAll[4]['stage'] = 'pending'; 
						$stepsAll[4]['Tab'] = 'disabled-tab'; 
						}
					}


					if($changesalarydetails->request_type==3)
					{
						if($changesalarydetails->approvedrejectstatus==1 && $changesalarydetails->incrementstatus==1 && $changesalarydetails->molstatus==1)
						{
						$completedStep++;
						$stepsAll[4]['stage'] = 'active'; 
						$OnboardingProgress = 'Request Confirmation';
						$stepsAll[4]['Tab'] = 'active'; 
						}
						elseif($changesalarydetails->approvedrejectstatus==1 && $changesalarydetails->incrementstatus==2 && $changesalarydetails->molstatus==1)
						{
						$completedStep++;
						$stepsAll[4]['stage'] = 'active'; 
						$OnboardingProgress = 'Request Confirmation';
						$stepsAll[4]['Tab'] = 'active'; 
						}
						elseif($changesalarydetails->approvedrejectstatus==1 && $changesalarydetails->incrementstatus==1 && $changesalarydetails->molstatus==2)
						{
						$completedStep++;
						$stepsAll[4]['stage'] = 'active'; 
						$OnboardingProgress = 'Request Confirmation';
						$stepsAll[4]['Tab'] = 'active'; 
						}
						else
						{
						$stepsAll[4]['stage'] = 'pending'; 
						$stepsAll[4]['Tab'] = 'disabled-tab'; 
						}
					}
					
					$stepsAll[4]['slagURL'] = 'tab6';  
					$stepsAll[4]['onclick'] = 'tab6Panel();'; 


			}












		    
			
			$totalStep = 6;
			$p = $completedStep/$totalStep;
			$percentange = round($p*100);
						
			$result['result']['steps'] = $stepsAll;
			$result['result']['percentange'] = $percentange;
			return $result;
}
}