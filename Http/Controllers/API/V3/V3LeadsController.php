<?php
namespace App\Http\Controllers\API\V3;

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
use App\Models\CrossSell\CrossSellScenariosAllocation;
use App\Models\CrossSell\CustomersMasterChild;
use DateTime;
use Crypt;

class V3LeadsController extends Controller
{
	
	
	public function manageLeads(Request $request)
	{
		
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['pageLimit']) && $requestParameters['pageLimit'] != '' && isset($requestParameters['pageNo']) && $requestParameters['pageNo'] != '' && isset($requestParameters['pageLimit']) && $requestParameters['pageLimit'] != '' && isset($requestParameters['statusType']) && $requestParameters['statusType'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$pageLimit = $requestParameters['pageLimit'];
			$pageNo = $requestParameters['pageNo'];
			$statusType = $requestParameters['statusType'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					$leadDetails = array();
					$leadDetailsStatus = array();
					$detailsLead = CrossSellScenariosAllocation::where("emp_id",$empId)->get();
					$index = 0;
					foreach($detailsLead as $lead)
					{
						/* echo "<pre>";
						print_r($lead);
						exit; */
						$customer_ids = $lead->customer_ids;
						$cusLists = explode(",",$customer_ids);
						
						
							if (($key = array_search(0, $cusLists)) !== false) {
								unset($cusLists[$key]);
							}
								if($statusType != 'All')
								{
									$lead_status = 1;
									if($statusType == 'inprogress')
									{
										$lead_status = 1;
									}
									elseif($statusType == 'interested')
									{
										$lead_status = 2;
									}
									elseif($statusType == 'not_interested')
									{
										$lead_status = 3;
									}
									else
									{
										$lead_status = 5;
									}
								$customerDataList = CustomersMasterChild::whereIn("id",$cusLists)->where("lead_status",$lead_status)->skip($pageNo)->take($pageLimit)->get();
								}
								else
								{
									$customerDataList = CustomersMasterChild::whereIn("id",$cusLists)->skip($pageNo)->take($pageLimit)->get();
								}
								
								/*
								*total data as per Status
								*/
								
								$leadDetailsStatus['All'] = CustomersMasterChild::whereIn("id",$cusLists)->get()->count();
								$leadDetailsStatus['Pending'] = CustomersMasterChild::whereIn("id",$cusLists)->where("lead_status",5)->get()->count();
								$leadDetailsStatus['In-progress'] = CustomersMasterChild::whereIn("id",$cusLists)->where("lead_status",1)->get()->count();
								$leadDetailsStatus['Interested'] = CustomersMasterChild::whereIn("id",$cusLists)->where("lead_status",2)->get()->count();
								$leadDetailsStatus['Not-interested'] = CustomersMasterChild::whereIn("id",$cusLists)->where("lead_status",3)->get()->count();
								
								/*
								*total data as per Status
								*/
								foreach($customerDataList as $customerData)
								{
									$leadDetails[$index]['leadId'] = $customerData->id;
									$leadDetails[$index]['sucb_cif'] = $customerData->sucb_cif;
									$leadDetails[$index]['customerName'] = $customerData->cm_name;
									$leadDetails[$index]['cmMobile'] = $customerData->cm_mobile;
									$leadDetails[$index]['cmSalary'] = $customerData->cm_salary;
									$leadDetails[$index]['companyName'] = $customerData->cm_employer;
									$leadDetails[$index]['Score'] = $customerData->cm_aecb_score;
									$leadDetails[$index]['Remark'] = $customerData->lead_remark;
									
									if($customerData->lead_status == 1)
									{
										$leadDetails[$index]['Status'] = 'In-progress';
										$leadDetails[$index]['StatusColor'] = '#fea621';
									}
									else if($customerData->lead_status == 2)
									{
										$leadDetails[$index]['Status'] = 'Interested';
										$leadDetails[$index]['StatusColor'] = '#2f29a6';
									}
									else if($customerData->lead_status == 3)
									{
										$leadDetails[$index]['Status'] = 'Not Interested';
										$leadDetails[$index]['StatusColor'] = '#fd1a16';
									}
									else
									{
										$leadDetails[$index]['Status'] = 'Pending';
										$leadDetails[$index]['StatusColor'] = '#a7a7ae';
									}
									
									$index++;
								}
							
						
					}
					if(count($leadDetailsStatus) == 0)
					{
						$leadDetailsStatus['All'] = 0;
								$leadDetailsStatus['Pending'] = 0;
								$leadDetailsStatus['In-progress'] = 0;
								$leadDetailsStatus['Interested'] = 0;
								$leadDetailsStatus['Not-interested'] = 0;
								
					}
					$leadDetails1=array();
					$leadDetails1[0]['displayName'] = 'In-progress'; 
					$leadDetails1[0]['displayValue'] = 'inprogress'; 
					$leadDetails1[1]['displayName'] = 'Interested'; 
					$leadDetails1[1]['displayValue'] = 'interested'; 
					$leadDetails1[2]['displayName'] = 'Not Interested'; 
					$leadDetails1[2]['displayValue'] = 'not_interested'; 
					
					$result['responseCode'] = 200;
						$result['message'] = "SuccessFull.";
						$result['result'] = $leadDetails;
						$result['statusCount'] = $leadDetailsStatus;
						$result['statusLists'] = $leadDetails1;
					
					
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
	
	
	
	
	public function updateLeadStatus(Request $request)
	{
		
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['leadId']) && $requestParameters['leadId'] != '' && isset($requestParameters['leadId']) && $requestParameters['leadId'] != '' && isset($requestParameters['Status']) && $requestParameters['Status'] != '' && isset($requestParameters['remark']) && $requestParameters['remark'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$leadId = $requestParameters['leadId'];
			$Status = $requestParameters['Status'];
			$remark = $requestParameters['remark'];
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					$update = CustomersMasterChild::find($leadId);
					if($Status == 'inprogress')
					{
						$update->lead_status = 1;
					}
					else if($Status == 'interested')
					{
						$update->lead_status = 2;
					}
					else if($Status == 'not_interested')
					{
						$update->lead_status = 3;
					}
					
					$update->lead_remark = $remark;
					if($update->save())
					{
						$result['responseCode'] = 200;
							$result['message'] = "Sucessfull.";
					}
					else
					{
						$result['responseCode'] = 300;
							$result['message'] = "Issue to save data .";
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