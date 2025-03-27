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
use App\Models\Entry\Employee;
use App\Models\Employee_Leaves\LeaveTypes;
use App\Models\Employee_Leaves\RequestedLeaves;
use App\Models\Employee_Leaves\RequestedLeavesLog;
use DateTime;
use Crypt;
use App\Models\Passport\Passport;
use App\Models\Passport\PassportHistory;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionAttributes;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use App\Models\Visa\visaType;
use App\Models\Visa\VisaStage;
use App\Models\Visa\Visaprocess;
use App\Http\Controllers\Passport\PassportController;

class V4PassportController extends Controller
{
	
	public function passportTabs(Request $request)
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
				
				$changeSalaryList[1]['Values'] = 'Available Passports';
				$changeSalaryList[2]['Values'] = 'Passports Not Available';
				$changeSalaryList[3]['Values'] = "Released Queue";
				$changeSalaryList[4]['Values'] = "Collection Queue";
				
				$changeSalaryList[0]['Keys'] = 'All';
			
				$changeSalaryList[1]['Keys'] = 'Available_Passports';
				$changeSalaryList[2]['Keys'] = 'Passports_Not_Available';
				$changeSalaryList[3]['Keys'] = 'Released_Queue';
				$changeSalaryList[4]['Keys'] = 'Collection_Queue';
				
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
	
public static function getVisaStatus($empid)
	{	
		$empDetails = Employee_details::where("emp_id",$empid)->orderBy('id','desc')->first();

		if($empDetails)
		{
			if($empDetails->document_collection_id != NULL)
			{
				$visaDetails = DocumentCollectionDetails::where("id",$empDetails->document_collection_id)->orderBy('id','desc')->first();

				if($visaDetails)
				{
					if($visaDetails->visa_process_status==4)
					{
						return "Visa Complete";
					}
					elseif($visaDetails->visa_process_status==2)
					{
						return "Visa In-Progress -  ".$visaDetails->visa_stage_steps;
					}
					else
					{
						return "Visa in-Complete";
					}

				}
				else
				{
					return "N/A";
				}

			}
			else
			{
				return "N/A";
			}

		}
		else
		{
			return "N/A";
		}
	}
	
	public static function getVisaTypeName($visaTypeId)
	{
		$visaTypeDetails = visaType::where("id",$visaTypeId)->orderBy('id','desc')->first();

		if($visaTypeDetails)
		{
			return $visaTypeDetails->title;
		}
		else
		{
			return "--";
		}
		

	}
	
	
	public static function getVisaStages($empid)
	{	
		$empDetails = Employee_details::where("emp_id",$empid)->orderBy('id','desc')->first();

		if($empDetails)
		{
			if($empDetails->document_collection_id != NULL)
			{
				$visaDetails = DocumentCollectionDetails::where("id",$empDetails->document_collection_id)->orderBy('id','desc')->first();

				if($visaDetails)
				{
					if($visaDetails->visa_process_status==4)
					{
						return "N/A";
					}
					elseif($visaDetails->visa_process_status==2)
					{
						//return "Visa in-Progress";

						$visaprocessDetails = Visaprocess::where("document_id",$empDetails->document_collection_id)->orderBy('id','desc')->first();

						if($visaprocessDetails)
						{
							$visastageDetails = VisaStage::where("id",$visaprocessDetails->visa_stage)->orderBy('id','desc')->first();

							if($visastageDetails)
							{
								
								
								$visaTypeDetails = visaType::where("id",$visastageDetails->visa_type)->orderBy('id','desc')->first();

								return $visastageDetails->stage_name. ' - ' .$visaTypeDetails->title;
							}
							else{
								return "N/A";
							}

						}
						else
						{
							return "N/A";
						}





					}
					else
					{
						return "N/A";
					}

				}
				else
				{
					return "N/A";
				}

			}
			else
			{
				return "N/A";
			}

		}
		else
		{
			return "N/A";
		}
	}

	
	public static function getVintage($empid)
		{
			$empDetails = Employee_details::where('emp_id',$empid)->orderBy('id','desc')->first();
			if(!$empDetails)
			{
				return '--';
			}
			return $empDetails->vintage_days;
		}

	
public function passportList(Request $request)
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
			$managerId = 0;
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					if($type == 'All')
					{
						$passportData = Passport::where("tl_id",$managerId)->orderBy('updated_at','desc')->skip($pageNo)
														->take($pageLimit)
														->get();
						$passportDataCount = Passport::where("tl_id",$managerId)->orderBy('updated_at','desc')->skip($pageNo)
														->take($pageLimit)
														->get()->count();
					}
				
					
					else if($type == 'Available_Passports')
					{
					
						$passportData = Passport::where("tl_id",$managerId)->where("passport_status",1)->orderBy('updated_at','desc')->skip($pageNo)
														->take($pageLimit)
														->get();
						$passportDataCount = Passport::where("tl_id",$managerId)->where("passport_status",1)->orderBy('updated_at','desc')->skip($pageNo)
														->take($pageLimit)
														->get()->count();
					}
					else if($type == 'Passports_Not_Available')
					{
						
						
					$passportData = Passport::where("tl_id",$managerId)->where('pre_release_request_approved_reject_status',1)
->where('collection_status',1)->where("passport_status",0)->orderBy('updated_at','desc')->skip($pageNo)
														->take($pageLimit)
														->get();
						$passportDataCount = Passport::where("tl_id",$managerId)->where('pre_release_request_approved_reject_status',1)
->where('collection_status',1)->where("passport_status",0)->orderBy('updated_at','desc')->skip($pageNo)
														->take($pageLimit)
														->get()->count();					
						
					}
					else if($type == 'Released_Queue')
					{
						
						
					$passportData = Passport::where("tl_id",$managerId)->where('release_list_status',1)
					->where('release_request_status',0)->orderBy('updated_at','desc')->skip($pageNo)
														->take($pageLimit)
														->get();
						$passportDataCount = Passport::where("tl_id",$managerId)->where('release_list_status',1)
					->where('release_request_status',0)->orderBy('updated_at','desc')->skip($pageNo)
														->take($pageLimit)
														->get()->count();					
						
					}
					else if($type == 'Collection_Queue')
					{
						
						
					$passportData = Passport::where("tl_id",$managerId)->where('pre_release_request_approved_reject_status',1)
->where('collection_status',1)->where("passport_status",0)->orderBy('updated_at','desc')->skip($pageNo)
														->take($pageLimit)
														->get();
						$passportDataCount = Passport::where("tl_id",$managerId)->where('pre_release_request_approved_reject_status',1)
->where('collection_status',1)->where("passport_status",0)->orderBy('updated_at','desc')->skip($pageNo)
														->take($pageLimit)
														->get()->count();					
						
					}
					
					$passportArray = array();
					$index=0;
					foreach($passportData as $passport)
					{
						$passportArray[$index]['employeeId'] = $passport->emp_id;
						$passportArray[$index]['employeeName'] = $this->getAgentName($passport->emp_id);
						$passportArray[$index]['passport_number'] = $passport->passport_number;
						$passportArray[$index]['VisaStatus'] = $this->getVisaStatus($passport->emp_id);
						$passportArray[$index]['VisaType'] = $this->getVisaTypeName($passport->emp_id);
						$passportArray[$index]['VisaStage'] = $this->getVisaStages($passport->emp_id);
						$passportArray[$index]['Vintage'] = $this->getVintage($passport->emp_id);
						if($passport->passport_status==1)
						{
							$pstatus='Available';
						}
						else
						{
							$pstatus='Not Available';
						}
						$passportArray[$index]['PassportStatus'] = $pstatus;
						
					
				
						$passportArray[$index]['CreatedAt'] = date("Y-m-d",strtotime($passport->created_at));
							
							$index++;
					}
					$result['responseCode'] = 200;
							$result['message'] = "Sucessfull.";
				
				$result['result'] = $passportArray;
				$warningLetterTitle = array();
				
				$warningLetterTitle[0]['Title'] = 'Employee Id';
				$warningLetterTitle[0]['Key'] = 'employeeId';
				
				
				$warningLetterTitle[1]['Title'] = 'Employee Name';
				$warningLetterTitle[1]['Key'] = 'employeeName';
				
				$warningLetterTitle[2]['Title'] = 'Password Number';
				$warningLetterTitle[2]['Key'] = 'passport_number';
				
				$warningLetterTitle[3]['Title'] = 'Visa Status';
				$warningLetterTitle[3]['Key'] = 'VisaStatus';
				
				$warningLetterTitle[4]['Title'] = 'Visa Type';
				$warningLetterTitle[4]['Key'] = 'VisaType';
				
				
				$warningLetterTitle[5]['Title'] = 'Visa Stage';
				$warningLetterTitle[5]['Key'] = 'VisaStage';
				
				
				$warningLetterTitle[6]['Title'] = 'Vintage';
				$warningLetterTitle[6]['Key'] = 'Vintage';
				
				$warningLetterTitle[7]['Title'] = 'Passport Status';
				$warningLetterTitle[7]['Key'] = 'PassportStatus';
				
				$warningLetterTitle[8]['Title'] = 'Created At';
				$warningLetterTitle[8]['Key'] = 'CreatedAt';
				
				$result['resultTitleDetails'] = $warningLetterTitle;	
				$result['totalCount'] = $passportDataCount;
				
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
	
public function passportCollection(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['selectEmpId']) && $requestParameters['selectEmpId'] != '' && isset($requestParameters['managerId']) && $requestParameters['managerId'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$selectEmpId = $requestParameters['selectEmpId'];
			$managerId = $requestParameters['managerId'];
			
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
					
					
					
					$formAdd[2]['Title'] = 'Passport Submit Date';
					$formAdd[2]['key'] = 'Passport_Submit_Date';
					$formAdd[2]['type'] = 'calender';
					$formAdd[2]['options'] = '';
					$formAdd[2]['value'] = '';
					$formAdd[2]['readOnly'] = 'No';
					
					$formAdd[3]['Title'] = 'Comments';
					$formAdd[3]['key'] = 'Comments';
					$formAdd[3]['type'] = 'textarea';
					$formAdd[3]['options'] = '';
					$formAdd[3]['value'] = '';
					$formAdd[3]['readOnly'] = 'No';
					
					
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
	
	
	public function passportInfo(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['selectEmpId']) && $requestParameters['selectEmpId'] != '' && isset($requestParameters['managerId']) && $requestParameters['managerId'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$selectEmpId = $requestParameters['selectEmpId'];
			$managerId = $requestParameters['managerId'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					$passportHistroy = PassportHistory::where("emp_id",$selectEmpId)->orderBy('id','asc')->get();
					$passArray = array();
					$index = 0;
					foreach($passportHistroy as $_data)
					{
						
						  if($_data->request_type==1)
						  {    
							$requestT = 'Release Passport';
						  }
                          elseif($_data->request_type==2)
						  {
							$requestT = 'Collect Passport';
                          }
						  elseif($_data->request_type==6)
                          {
							$requestT =  'Release Passport Request Rollback';
                          }
						  else
						  {    
							$requestT = 'Release Passport Request Generated';
						  }   
						$passArray[$index]['Request Type'] = $requestT;
						$passArray[$index]['Request Id'] = $_data->request_id;
						if($_data->request_type==1)
						{
							$passArray[$index]['Request Created At'] = date("d M Y",strtotime($_data->release_at));
						}
						else
						{
							$passArray[$index]['Request Created At'] = date("d M Y",strtotime($_data->request_at));
						}
						
						  if($_data->request_type==1)
                          {
							$passArray[$index]['Request By'] = PassportController::getUserName($_data->release_by);
                          }
						  elseif($_data->request_type==2)
                          {
							$passArray[$index]['Request By'] = PassportController::getUserName($_data->request_by); 
                          }
						  else
						  {
							$passArray[$index]['Request By'] = PassportController::getUserName($_data->requestcreatedby); 
						  }
						  
						  
						  
						    if($_data->request_type==1)
							{
							$passArray[$index]['Released/Submit Date'] =date("d M Y",strtotime($_data->passport_release_date));
                        
							}

                          elseif($_data->request_type==2)
                          {
                          $passArray[$index]['Released/Submit Date'] =date("d M Y",strtotime($_data->passport_submit_date));
                          
                          }
						  
						  
						   if($_data->request_type==1)
						   {
                          $passArray[$index]['Request Comments'] = $_data->release_comments;
						   }
						  elseif($_data->request_type==2)
                          {
						  $passArray[$index]['Request Comments'] = $_data->request_comments;
                          }
						  else
                          {
							$passArray[$index]['Request Comments'] = $_data->requestcreatedcomment;

						  }          
						$index++;
					}
					$result['responseCode'] = 200;
					$result['message'] = "Sucessfull.";
				
					$result['result'] = $passArray;
					
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

public function passportCollectionPost(Request $request)
{
	$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['selectEmpId']) && $requestParameters['selectEmpId'] != '' && isset($requestParameters['managerId']) && $requestParameters['managerId'] != '' && isset($requestParameters['Passport_Submit_Date']) && $requestParameters['Passport_Submit_Date'] != '' && isset($requestParameters['Comments']) && $requestParameters['Comments'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$selectEmpId = $requestParameters['selectEmpId'];
			$managerId = $requestParameters['managerId'];
			$Passport_Submit_Date = $requestParameters['Passport_Submit_Date'];
			$Comments = $requestParameters['Comments'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					
					$passportData = Passport::where('emp_id',$selectEmpId)->orderBy('id','DESC')->first();

					$userid=$managerId;
					$passportData->requestpassport_comments = $Comments;
					$passportData->requestpassport_by = $userid;			
					$passportData->passport_submit_date = $Passport_Submit_Date;
					$passportData->requestpassport_status = 1;
					$passportData->passport_status = 1;	
					
					$passportData->release_request_status = 0;
					$passportData->releaserequestat = NULL;
					$passportData->releaseby = NULL;
					$passportData->release_comments = NULL;
					$passportData->release_list_status = 0;
					$passportData->pre_release_request_approved_reject_status = 2;
					$passportData->collection_status = 2;
				
					if($passportData->save())
					{





							$passportData = Passport::where('emp_id',$selectEmpId)->orderBy('id','DESC')->first();

							
							$passportHistory = new PassportHistory();
							$passportHistory->emp_id = $selectEmpId;
							$passportHistory->request_at = date('Y-m-d');
							$passportHistory->request_by = $userid;
							$passportHistory->passport_submit_date = $Passport_Submit_Date;
							$passportHistory->request_comments = $Comments;
							$passportHistory->request_status = 1;
							$passportHistory->request_type = 2;
							$passportHistory->status = 1;
							$passportHistory->request_id = $passportData->request_id;

							$passportHistory->save();
									
									$result['responseCode'] = 200;
									$result['message'] = "Sucessfull.";
					}
					else
					{
						$result['responseCode'] = 401;
									$result['message'] = "Issue To Sucessfull.";
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
	public function passportRelease(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['selectEmpId']) && $requestParameters['selectEmpId'] != '' && isset($requestParameters['managerId']) && $requestParameters['managerId'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$selectEmpId = $requestParameters['selectEmpId'];
			$managerId = $requestParameters['managerId'];
			
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
					
					
					
					$formAdd[2]['Title'] = 'Comments';
					$formAdd[2]['key'] = 'Comments';
					$formAdd[2]['type'] = 'textarea';
					$formAdd[2]['options'] = '';
					$formAdd[2]['value'] = '';
					$formAdd[2]['readOnly'] = 'No';
					
					
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
	
public function passportReleasePost(Request $request)
{
	$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['selectEmpId']) && $requestParameters['selectEmpId'] != '' && isset($requestParameters['managerId']) && $requestParameters['managerId'] != ''  && isset($requestParameters['Comments']) && $requestParameters['Comments'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$selectEmpId = $requestParameters['selectEmpId'];
			$managerId = $requestParameters['managerId'];
			
			$Comments = $requestParameters['Comments'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					$userid=$managerId;
					$passportData = Passport::where('emp_id',$selectEmpId)->orderBy('id','DESC')->first();

			



			if($passportData)
			{
				$passportData->release_comments = $Comments;			
				$passportData->request_generate_by = $managerId;			
				$passportData->request_id = random_int(1000,9999).$selectEmpId.random_int(1000,9999);
				
				$passportData->request_generate_status = 1;
				$passportData->release_list_status = 1;	
				$passportData->passport_status = 0;	

				$passportData->request_generate_at = date('Y-m-d');
			
					$passportData->pre_release_request_status  = 1;				

				
				$passportData->save();
			}
			else
			{

				$passportData = new Passport();
				$passportData->release_comments = $Comments;
				$passportData->emp_id = $selectEmpId;			
				$passportData->request_generate_by = $managerId;
				$passportData->request_id = random_int(1000,9999).$selectEmpId.random_int(1000,9999);
			
				//$passportData->passport_release_date = $request->passportreleaseddate;
				//$passportData->passport_number = $request->passportnumber;
				$passportData->request_generate_status = 1;	
				$passportData->release_list_status = 1;
				$passportData->passport_status = 0;	
				
					$passportData->pre_release_request_status  = 1;		
	
				$passportData->request_generate_at = date('Y-m-d');	
			
				$passportData->save();
			}


			


			$passportData = Passport::where('emp_id',$selectEmpId)->orderBy('id','DESC')->first();

			
			$passportHistory = new PassportHistory();
			$passportHistory->emp_id = $selectEmpId;
			$passportHistory->requestcreatedat = date('Y-m-d');
			$passportHistory->requestcreatedby = $managerId;
			$passportHistory->requestcreatedcomment = $Comments;
			//$passportHistory->passport_release_date = $request->passportreleaseddate;
			//$passportHistory->release_status = 1;
			$passportHistory->request_type = 3;
			$passportHistory->status = 1;

			$passportHistory->request_id = $passportData->request_id;

			if($passportHistory->save())
			{
									
									$result['responseCode'] = 200;
									$result['message'] = "Sucessfull.";
			
					}
					else
					{
						$result['responseCode'] = 401;
									$result['message'] = "Issue To Sucessfull.";
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


public function passportReleaseFinal(Request $request)
{
	$requestParameters = $request->input();
		
		if(  isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['selectEmpId']) && $requestParameters['selectEmpId'] != '' && isset($requestParameters['managerId']) && $requestParameters['managerId'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$selectEmpId = $requestParameters['selectEmpId'];
			$managerId = $requestParameters['managerId'];
			
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
					
					
					$formAdd[2]['Title'] = 'Passport Release Date';
					$formAdd[2]['key'] = 'Passport_Submit_Date';
					$formAdd[2]['type'] = 'calender';
					$formAdd[2]['options'] = '';
					$formAdd[2]['value'] = '';
					$formAdd[2]['readOnly'] = 'No';
					
					$formAdd[3]['Title'] = 'Comments';
					$formAdd[3]['key'] = 'Comments';
					$formAdd[3]['type'] = 'textarea';
					$formAdd[3]['options'] = '';
					$formAdd[3]['value'] = '';
					$formAdd[3]['readOnly'] = 'No';
					
					
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

public function passportReleaseFinalPost(Request $request)
{
	$requestParameters = $request->input();
		
		if(isset($requestParameters['Passport_Submit_Date']) && $requestParameters['Passport_Submit_Date'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['selectEmpId']) && $requestParameters['selectEmpId'] != '' && isset($requestParameters['managerId']) && $requestParameters['managerId'] != ''  && isset($requestParameters['Comments']) && $requestParameters['Comments'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$selectEmpId = $requestParameters['selectEmpId'];
			$managerId = $requestParameters['managerId'];
			$Passport_Submit_Date = $requestParameters['Passport_Submit_Date'];
			
			$Comments = $requestParameters['Comments'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					$userid=$managerId;
					$passportData = Passport::where('emp_id',$selectEmpId)->orderBy('id','DESC')->first();


				$passportData->release_comments = $Comments;			
				$passportData->releaseby = $managerId;			
				$passportData->passport_release_date = $Passport_Submit_Date;
				//$passportData->passport_number = $request->passportnumber;
				$passportData->release_request_status = 1;	
				$passportData->passport_status = 0;	
				$passportData->releaserequestat = date('Y-m-d H:i:s');

				$passportData->requestpassport_status = 0;
				$passportData->requestpassport_at = NULL;
				$passportData->requestpassport_by = NULL;
				$passportData->requestpassport_comments = NULL;
				$passportData->release_list_status = 0;
				$passportData->request_generate_status = 0;
				if($passportData->request_generated_by_leave == 1)
				{
					$passportData->request_generated_by_leave = 0;
				}
				

				if($passportData->request_id == '')
				{
					$passportData->request_id = random_int(1000,9999).$selectEmpId.random_int(1000,9999);

				}
$passportData->pre_release_request_approved_reject_status = 1;
					$passportData->collection_status = 1;	
				$passportData->save();
			


			$passportData = Passport::where('emp_id',$selectEmpId)->orderBy('id','DESC')->first();

			
			$passportHistory = new PassportHistory();
			$passportHistory->emp_id = $selectEmpId;
			$passportHistory->release_at = date('Y-m-d');
			$passportHistory->release_by = $userid;
			$passportHistory->release_comments = $Comments;
			$passportHistory->passport_release_date = $Passport_Submit_Date;
			$passportHistory->release_status = 1;
			$passportHistory->request_type = 1;
			$passportHistory->status = 1;
			
			$passportHistory->request_id = $passportData->request_id;


			

			if($passportHistory->save())
			{
									
									$result['responseCode'] = 200;
									$result['message'] = "Sucessfull.";
			
					}
					else
					{
						$result['responseCode'] = 401;
									$result['message'] = "Issue To Sucessfull.";
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