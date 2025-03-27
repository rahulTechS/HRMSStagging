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
use App\Models\KYCProcess\KYCProcess;
use App\Models\Employee\Employee_attribute;
use  App\Models\Attribute\Attributes;
use DateTime;
use Crypt;

class V3AuthTokenController extends Controller
{
	public function getAuthToken()
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@$%^&*';
		$token = '';
		for ($i = 0; $i < 30; $i++) {
			$token .= $characters[rand(0, strlen($characters) - 1)];
		}
		$tokenStr = $token.'#'.strtotime(date("Y-m-d H:i:s"));
		$result['Token'] = $tokenStr;
		$result['UserName'] = 'SuraniGroupAPI';
		$result['Password'] = 'Sura@123Grou1###2';
		$result['Page_id'] = '173';
		$result['DateUnixValue'] = strtotime(date("Y-m-d H:i:s"));
		$createToken = new APIAuth();
		$createToken->Token = $tokenStr;
		$createToken->save();
		return response()->json($result);
	}
	
	public function appLoginStep1(Request $request)
	{
		
		$requestParameters = $request->input();
		if(isset($requestParameters['Device_UDID']) && $requestParameters['Device_UDID'] != '' && isset($requestParameters['Imei_number']) && $requestParameters['Imei_number'] != '' && isset($requestParameters['Manufacturer']) && $requestParameters['Manufacturer'] != '' && isset($requestParameters['ModelNo']) && $requestParameters['ModelNo'] != '' && isset($requestParameters['DeviceOS']) && $requestParameters['DeviceOS'] != '' && isset($requestParameters['OSVersion']) && $requestParameters['OSVersion'] != '' && isset($requestParameters['AppVersion']) && $requestParameters['AppVersion'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '')
		{
		$result = array();
		$empId = $requestParameters['empId'];
		$empData = EmpAppAccess::where("employee_id",$empId)->first();
		if($empData != '')
		{
			
				
				if($empData->passwordStatus == 1)
				{
				$result['message'] = 'Create Password.';
				}
				else
				{
					$result['message'] = 'Login';
				}
				$result['responseCode'] = 200;
		$result['passwordStatus'] = $empData->passwordStatus;
		$result['jobFunction'] = $empData->job_function;
		
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@$%^&*';
		$token = '';
		for ($i = 0; $i < 30; $i++) {
			$token .= $characters[rand(0, strlen($characters) - 1)];
		}
		$tokenStr = $token.'#'.strtotime(date("Y-m-d H:i:s"));
		
		
		$result['Values']['Token'] = $tokenStr;
		$result['Values']['PageId'] = '173';
		$result['Values']['EmpId'] = $requestParameters['empId'];
		$empMainData = Employee_details::select("emp_name")->where("emp_id",$empId)->first();
		if($empMainData != '')
		{
			$result['Values']['EmployeeName'] = $empMainData->emp_name;
		}
		else
		{
			$result['Values']['EmployeeName'] = '';
		}
		$result['Values']['DateUnixValue'] = strtotime(date("Y-m-d H:i:s"));
		
		
		
		
		$createToken = new APIAuth();
		$createToken->Token = $tokenStr;
		$createToken->Device_UDID = $requestParameters['Device_UDID'];
		$createToken->Imei_number = $requestParameters['Imei_number'];
		$createToken->Manufacturer = $requestParameters['Manufacturer'];
		$createToken->ModelNo = $requestParameters['ModelNo'];
		$createToken->DeviceOS = $requestParameters['DeviceOS'];
		$createToken->OSVersion = $requestParameters['OSVersion'];
		$createToken->AppVersion = $requestParameters['AppVersion'];
		$createToken->emp_id = $requestParameters['empId'];
		$createToken->save();
		return response()->json($result);
		}
		else
		{
			$result['responseCode'] = 401;
			$result['message'] = "Employee not found";
			return response()->json($result);
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
				return response()->json($result);
		}
	}
	
	
	public function appLoginStep1V2(Request $request)
	{
		
		$requestParameters = $request->input();
		if(isset($requestParameters['Device_UDID']) && $requestParameters['Device_UDID'] != '' && isset($requestParameters['Imei_number']) && $requestParameters['Imei_number'] != '' && isset($requestParameters['Manufacturer']) && $requestParameters['Manufacturer'] != '' && isset($requestParameters['ModelNo']) && $requestParameters['ModelNo'] != '' && isset($requestParameters['DeviceOS']) && $requestParameters['DeviceOS'] != '' && isset($requestParameters['OSVersion']) && $requestParameters['OSVersion'] != '' && isset($requestParameters['AppVersion']) && $requestParameters['AppVersion'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '')
		{
		$result = array();
		$empId = $requestParameters['empId'];
		$empData = EmpAppAccess::where("employee_id",$empId)->first();
		if($empData != '')
		{
			
				
				if($empData->passwordStatus == 1)
				{
				$result['message'] = 'Create Password.';
				}
				else
				{
					$result['message'] = 'Login';
				}
				$result['responseCode'] = 200;
		$result['passwordStatus'] = $empData->passwordStatus;
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@$%^&*';
		$token = '';
		for ($i = 0; $i < 30; $i++) {
			$token .= $characters[rand(0, strlen($characters) - 1)];
		}
		$tokenStr = $token.'#'.strtotime(date("Y-m-d H:i:s"));
		
		
		$result['Values']['Token'] = $tokenStr;
		$result['Values']['PageId'] = '173';
		$result['Values']['EmpId'] = $requestParameters['empId'];
		$empMainData = Employee_details::select("emp_name")->where("emp_id",$empId)->first();
		if($empMainData != '')
		{
			$result['Values']['EmployeeName'] = $empMainData->emp_name;
		}
		else
		{
			$result['Values']['EmployeeName'] = '';
		}
		$result['Values']['DateUnixValue'] = strtotime(date("Y-m-d H:i:s"));
		
		/* $result['responseCode'] = 300;
			$result['message'] = "Kindly Update your APP to use functionality.";
			return response()->json($result); */
		
		
	 	$createToken = new APIAuth();
		$createToken->Token = $tokenStr;
		$createToken->Device_UDID = $requestParameters['Device_UDID'];
		$createToken->Imei_number = $requestParameters['Imei_number'];
		$createToken->Manufacturer = $requestParameters['Manufacturer'];
		$createToken->ModelNo = $requestParameters['ModelNo'];
		$createToken->DeviceOS = $requestParameters['DeviceOS'];
		$createToken->OSVersion = $requestParameters['OSVersion'];
		$createToken->AppVersion = $requestParameters['AppVersion'];
		$createToken->emp_id = $requestParameters['empId'];
		$createToken->save(); 
		
		return response()->json($result);
		}
		else
		{
			$result['responseCode'] = 401;
			$result['message'] = "Employee not found";
			return response()->json($result);
		}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
				return response()->json($result);
		}
	}
	
	public function appLoginProcess(Request $request)
	{
		$requestParameters = $request->input();
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' && isset($requestParameters['Password']) && $requestParameters['Password'] != '')
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		$Password = $requestParameters['Password'];
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				$passwordFromUsername = Crypt::decrypt($empData->password);
				if($passwordFromUsername == $Password)
				{
					$empMainData = Employee_details::where("emp_id",$empId)->first();
						$result = $this->getEmpDetails($empData,$empId);
				}
				else
				{
					$result['responseCode'] = 300;
					$result['message'] = "Check Employee id Or Password.";
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
	
	
	
	
	
	public function appHomeProfile(Request $request)
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
				
				$result = $this->getEmpDetails($empData,$empId);
					
				
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
	
	
	public function appSignUpProcess(Request $request)
	{
		$requestParameters = $request->input();
		if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' && isset($requestParameters['Password']) && $requestParameters['Password'] != '')
		{
		$result = array();
		$Token = $requestParameters['Token'];
		$empId = $requestParameters['empId'];
		$Password = $requestParameters['Password'];
		$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
		if($checkToken != '' && trim($checkToken->Token) == trim($Token))
		{
			$empData = EmpAppAccess::where("employee_id",$empId)->first();
			if($empData != '')
			{
				$passwordCRY = Crypt::encrypt($Password);
				
					
					
					
					$update = EmpAppAccess::find($empData->id);
					$update->password = $passwordCRY;
					$update->passwordtxt = $Password;
					$update->passwordStatus = 2;
					if($update->save())
					{
						$result['responseCode'] = 200;
						$result['message'] = "SignUp Successfull";
					}
					else
					{
						$result['responseCode'] = 200;
						$result['message'] = "Issue in SignUp";
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
	
	
	
	protected function getEmpDetails($empData,$empId)
	{
		
				
				    $kycFieldsCount = KYCProcess::where("status",1)->get()->count();
					/*
					*check for data
					*/
					$kycFields = KYCProcess::where("status",1)->get();
					$fieldsFilled = 0;
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
									$fieldsFilled++;
								}
							}
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
					
					/*
					*check Skip KyC
					*/
					$SkipKYCPage = false;
					$checkForDocuments = EmpAppAccess::where("employee_id",$empId)->first();
					if($checkForDocuments->emirate_id_path_front != '' && $checkForDocuments->emirate_id_path_front != NULL && $checkForDocuments->emirate_id_path_bank != '' && $checkForDocuments->emirate_id_path_bank != NULL && $checkForDocuments->pics != '' && $checkForDocuments->pics != NULL)
					{
						$SkipKYCPage = true;
					}
					
					
					if($checkForDocuments->is_allow_eid == 1 && $checkForDocuments->pics != '' && $checkForDocuments->pics != NULL)
					{
						$SkipKYCPage = true;
					}	
					$attrValue = Attributes::where("kyc_require_status",1)->where("kyc_status",1)->where("status",1)->get();
					foreach($attrValue as $attr)
					{
						$code = $attr->attribute_code;
						$valueFound = Employee_attribute::where('emp_id',$empId)->where('attribute_code',$code)->first();
						if($valueFound != '')
							{
								if($valueFound->attribute_values == NULL || $valueFound->attribute_values == '')
								{
									$SkipKYCPage = false;
								}
							}
							else
							{
								$SkipKYCPage = false; 
							}
					}
					/*
					*check Skip KyC
					*/
					/*
					*check for data
					*/
					$empMainData = Employee_details::where("emp_id",$empId)->first();
					$result['responseCode'] = 200;
					$result['message'] = "Login Successfull";
					$homePageDefine = array();
					$homePageDefine[0]['Name'] = "Personal Details";
					$homePageDefine[0]['IconUrl'] = "https://www.hr-suranigroup.com/hrm/img/mobile-icon/home-icon-1.png";
					$homePageDefine[0]['PageID'] = 130;
					$homePageDefine[1]['Name'] = "Hiring Information";
					$homePageDefine[1]['IconUrl'] = "https://www.hr-suranigroup.com/hrm/img/mobile-icon/home-icon-2.png";
					$homePageDefine[1]['PageID'] = 131;
					$homePageDefine[2]['Name'] = "Visa Information";
					$homePageDefine[2]['IconUrl'] = "https://www.hr-suranigroup.com/hrm/img/mobile-icon/home-icon-3.png";
					$homePageDefine[2]['PageID'] = 132;
					$homePageDefine[3]['Name'] = "Insurance Information";
					$homePageDefine[3]['IconUrl'] = "https://www.hr-suranigroup.com/hrm/img/mobile-icon/home-icon-4.png";
					$homePageDefine[3]['PageID'] = 133;
					$homePageDefine[4]['Name'] = "Deployment Information";
					$homePageDefine[4]['IconUrl'] = "https://www.hr-suranigroup.com/hrm/img/mobile-icon/home-icon-5.png";
					$homePageDefine[4]['PageID'] = 134;
					$homePageDefine[5]['Name'] = "Warning Letters";
					$homePageDefine[5]['IconUrl'] = "https://www.hr-suranigroup.com/hrm/img/mobile-icon/home-icon-6.png";
					$homePageDefine[5]['PageID'] = 135;
					$homePageDefine[6]['Name'] = "Leaves";
					$homePageDefine[6]['IconUrl'] = "https://www.hr-suranigroup.com/hrm/img/mobile-icon/home-icon-7.png";
					$homePageDefine[6]['PageID'] = 136;
					$homePageDefine[7]['Name'] = "Attendance";
					$homePageDefine[7]['IconUrl'] = "https://www.hr-suranigroup.com/hrm/img/mobile-icon/home-icon-8.png";
					$homePageDefine[7]['PageID'] = 137;
					
					
					$homePageDefine[7]['Name'] = "More";
					$homePageDefine[7]['IconUrl'] = "https://www.hr-suranigroup.com/hrm/img/mobile-icon/home-icon-more.png";
					$homePageDefine[7]['PageID'] = 138;
					
					$salesDefine = array();
					$salesDefine[0]['Name'] = 'View Submissions';
					$salesDefine[0]['IconUrl'] = "https://www.hr-suranigroup.com/hrm/img/mobile-icon/home-icon-8.png";
					$salesDefine[0]['PageID'] = 140;
					$salesDefine[1]['Name'] = 'Add Submissions';
					$salesDefine[1]['IconUrl'] = "https://www.hr-suranigroup.com/hrm/img/mobile-icon/home-icon-8.png";
					$salesDefine[1]['PageID'] = 141;
					$valuesArray = array();
					$valuesArray['EmployeeName'] = $empMainData->emp_name;
					$valuesArray['FirstName'] = $empMainData->first_name;
					$valuesArray['deptId'] = $empMainData->dept_id;
					$valuesArray['deptName'] = $this->getDepartmentName($empMainData->dept_id);
					$valuesArray['Type'] = $this->getDepartmentName($empMainData->dept_id);
					$valuesArray['empId'] = $empMainData->emp_id;
					$valuesArray['JobFunctionId'] = $empMainData->job_function;
					$valuesArray['JobFunctionName'] = $this->getFuncName($empMainData->job_function);
					$valuesArray['DesignationId'] = $empMainData->designation_by_doc_collection;
					$valuesArray['DesignationName'] = $this->getDesignationName($empMainData->designation_by_doc_collection);
					$valuesArray['ActualSalary'] = $empMainData->actual_salary;
					$valuesArray['WorkLocation'] = $empMainData->work_location;
					$valuesArray['DateOfJoining'] = $empMainData->doj;
					$valuesArray['SourceCode'] = $empMainData->source_code;
					$result['EmployeeDetails'] = $valuesArray;
					
					if($CountKycPF != 100)
					{
						$result['Kyc']= array("Status"=>"Pending","Completed"=>$CountKycPF,"SkipKYCPage"=>$SkipKYCPage);
					}
					else
					{
						$result['Kyc']= array("Status"=>"Done","Completed"=>"100","SkipKYCPage"=>$SkipKYCPage);
					}
					
					$result['HomeContest'] = "Book 5 Cards in 2 daysto earn a commision of 200AED";
					$result['Tabs'][0]['Title'] = 'Home';
					$result['Tabs'][0]['Values'] = $homePageDefine;
					
					$result['Tabs'][1]['Title'] = 'Sales';
					$result['Tabs'][1]['Values'] = $salesDefine;
					
					
					$result['Tabs'][2]['Title'] = 'More';
					$result['Tabs'][2]['Values'] = array();
					
					return $result;
				
			
	}
}