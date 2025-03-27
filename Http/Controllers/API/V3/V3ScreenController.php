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
use Illuminate\Support\Str;
use DateTime;
use Crypt;
use App\Models\ManageApp\AppScreens;

class V3ScreenController extends Controller
{
	
	
	protected function StaticScreen(Request $request)
	{
		
					$result['responseCode'] = 200;
					$result['message'] = "Successfull";
					$result['Screen'][0]['ImageUrl'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/manage-sales.png';
					$result['Screen'][0]['Title'] = 'Manage your';
					$result['Screen'][0]['SubTitle'] = 'Sales';
					$result['Screen'][0]['bulletPoints'][0] = 'View customer details.';
					$result['Screen'][0]['bulletPoints'][1] = 'Monitor the status of ongoing submissions.';
					$result['Screen'][0]['bulletPoints'][2] = 'Review submissions history.';
					
					
					$result['Screen'][1]['ImageUrl'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/manage-leaves.png';
					$result['Screen'][1]['Title'] = 'Manage your';
					$result['Screen'][1]['SubTitle'] = 'Leave & Attendance';
					$result['Screen'][1]['bulletPoints'][0] = 'Online Portals & Transparency.';
					$result['Screen'][1]['bulletPoints'][1] = 'Mark attendance per day.';
					$result['Screen'][1]['bulletPoints'][2] = 'Manage your leave.';
					
					
					$result['Screen'][2]['ImageUrl'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/track-commission.png';
					$result['Screen'][2]['Title'] = 'Manage your';
					$result['Screen'][2]['SubTitle'] = 'Sales Commissions';
					$result['Screen'][2]['bulletPoints'][0] = 'Promotions and scheduling.';
					$result['Screen'][2]['bulletPoints'][1] = 'Merchandising for actions at point of sale to be effective.';
					$result['Screen'][2]['bulletPoints'][2] = 'Manage your leave.';
					
				
			return response()->json($result);
		}
		
		
			
		
public function UploadDocument(Request $request)
{
	$requestParameters = $request->input();
	$filesParameters  = $request->file();
	/* print_r($requestParameters);
	exit; */

		$result = array();
	
			if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' )
			{
			$result = array();
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			
		
		
			$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					/* echo "ddd";
					exit; */
					$updateValueArray = array();
					if(isset($requestParameters['HolderName']) && $requestParameters['HolderName'] != '')
					{
						$updateValueArray['HolderName'] = $requestParameters['HolderName'];
					}
					if(isset($requestParameters['ENumber']) && $requestParameters['ENumber'] != '')
					{
						$updateValueArray['ENumber'] = $requestParameters['ENumber'];
					}
					if(isset($requestParameters['ExpiryDate']) && $requestParameters['ExpiryDate'] != '')
					{
						$updateValueArray['ExpiryDate'] = date("Y-m-d",strtotime($requestParameters['ExpiryDate']));
					}
					if(isset($requestParameters['IssueDate']) && $requestParameters['IssueDate'] != '')
					{
						$updateValueArray['IssueDate'] =  date("Y-m-d",strtotime($requestParameters['IssueDate']));
					}
					if(isset($requestParameters['CnicHolderDoB']) && $requestParameters['CnicHolderDoB'] != '')
					{
						$updateValueArray['CnicHolderDoB'] = date("Y-m-d",strtotime($requestParameters['CnicHolderDoB']));
					}
					if(isset($requestParameters['Nationality']) && $requestParameters['Nationality'] != '')
					{
						$updateValueArray['Nationality'] = $requestParameters['Nationality'];
					}
					if(isset($requestParameters['Occupation']) && $requestParameters['Occupation'] != '')
					{
						$updateValueArray['Occupation'] = $requestParameters['Occupation'];
					}
					if(isset($requestParameters['Employer']) && $requestParameters['Employer'] != '')
					{
						$updateValueArray['Employer'] = $requestParameters['Employer'];
					}
					if(isset($requestParameters['IssuingPlace']) && $requestParameters['IssuingPlace'] != '')
					{
						$updateValueArray['IssuingPlace'] = $requestParameters['IssuingPlace'];
					}
					/* print_r($updateValueArray);
					exit; */
					foreach($filesParameters as $key=>$file)
					{
						
						$filenameWithExt =  $file->getClientOriginalName();
				
						$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
						$fileExtension =$file->getClientOriginalExtension();
						
						$newFileName = $key."_".$empId.'.'.$fileExtension;
						if(file_exists(public_path('uploads/ApiDocs/'.$newFileName))){

							  unlink(public_path('uploads/ApiDocs/'.$newFileName));
							}  
						if($file->move(public_path('uploads/ApiDocs/'), $newFileName))
						{
							$result['responseCode'] = 200;
							
							if($key == 'EmirateId_front')
							{
								EmpAppAccess::where("employee_id",$empId)->update(array("emirate_id_path_front"=>$newFileName));
								$result['EmirateId_front'] = "https://www.hr-suranigroup.com/uploads/ApiDocs/".$newFileName;
								$result['message']['EmirateId_front'] = "Successfull";	
							}
							else if($key == 'EmirateId_back')
							{
								EmpAppAccess::where("employee_id",$empId)->update(array("emirate_id_path_bank"=>$newFileName));
								$result['EmirateId_back'] = "https://www.hr-suranigroup.com/uploads/ApiDocs/".$newFileName;
								$result['message']['EmirateId_back'] = "Successfull";	
							}
							else
							{
								EmpAppAccess::where("employee_id",$empId)->update(array("pics"=>$newFileName));
								$result['CandidatePic'] = "https://www.hr-suranigroup.com/uploads/ApiDocs/".$newFileName;
								$result['message']['CandidatePic'] = "Successfull";	
							}
							
							
							
						}
						else
						{
							$result['responseCode'] = 205;
							if($key == 'EmirateId_front')
							{
								
								$result['message']['EmirateId_front'] = "issue to save";	
							}
							else if($key == 'EmirateId_back')
							{
								
								$result['message']['EmirateId_back'] = "issue to save";	
							}
							else
							{
								
								$result['message']['CandidatePic'] = "issue to save";	
							}
								
						}
					
					}
					if(count($updateValueArray) >0)
					{
						EmpAppAccess::where("employee_id",$empId)->update($updateValueArray);
						if(!isset($result['responseCode']))
						{
							$result['responseCode'] = 200;
							$result['message']['values'] = "Save Successfull";	
							$data = EmpAppAccess::where("employee_id",$empId)->first();
							$result['Information']['HolderName'] = $data->HolderName;	
							$result['Information']['ENumber'] = $data->ENumber;	
							$result['Information']['ExpiryDate'] = $data->ExpiryDate;	
							$result['Information']['IssueDate'] = $data->IssueDate;	
							$result['Information']['CnicHolderDoB'] = $data->CnicHolderDoB;	
							$result['Information']['Nationality'] = $data->Nationality;	
							$result['Information']['Occupation'] = $data->Occupation;	
							$result['Information']['Employer'] = $data->Employer;	
							$result['Information']['IssuingPlace'] = $data->IssuingPlace;	
						}
						else
						{
							
							$result['message']['values'] = "Save Successfull";	
							$data = EmpAppAccess::where("employee_id",$empId)->first();
							
								$result['Information']['HolderName'] = $data->HolderName;	
							$result['Information']['ENumber'] = $data->ENumber;	
							$result['Information']['ExpiryDate'] = $data->ExpiryDate;	
							$result['Information']['IssueDate'] = $data->IssueDate;	
							$result['Information']['CnicHolderDoB'] = $data->CnicHolderDoB;	
							$result['Information']['Nationality'] = $data->Nationality;	
							$result['Information']['Occupation'] = $data->Occupation;	
							$result['Information']['Employer'] = $data->Employer;	
							$result['Information']['IssuingPlace'] = $data->IssuingPlace;
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
					$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
			}
			return response()->json($result);
}


public function getDocumentStatus(Request $request)
{
	$requestParameters = $request->input();
	
	

		$result = array();
	
			if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' )
			{
			$result = array();
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
		
			$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					$checkForDocuments = EmpAppAccess::where("employee_id",$empId)->first();
					
					$result['responseCode'] = 200;
					$result['message'] = "Successfull";	
					$result['EmirateId_front'] = '';
					$result['EmirateId_back'] = '';
					$result['CandidatePic'] = '';
						$result['skipKYC'] = 'Yes';
						$result['KYCSelected'] = 'Yes';
						$eidStatus = 1;
					if($checkForDocuments != '')
					{
						if($checkForDocuments->emirate_id_path_front != '' && $checkForDocuments->emirate_id_path_front != NULL)
						{
							$result['EmirateId_front'] = "https://www.hr-suranigroup.com/uploads/ApiDocs/".$checkForDocuments->emirate_id_path_front;
						}
						else
						{
							$eidStatus = 2;
						}
						if($checkForDocuments->emirate_id_path_bank != '' && $checkForDocuments->emirate_id_path_bank != NULL)
						{
							$result['EmirateId_back'] = "https://www.hr-suranigroup.com/uploads/ApiDocs/".$checkForDocuments->emirate_id_path_bank;
						}
						else
						{
							$eidStatus = 2;
						}
						if($checkForDocuments->pics != '' && $checkForDocuments->pics != NULL)
						{
							$result['CandidatePic'] = "https://www.hr-suranigroup.com/uploads/ApiDocs/".$checkForDocuments->pics;
						}
						else
						{
								$result['skipKYC'] = 'No';
						}
						if($checkForDocuments->is_allow_eid != NULL && $checkForDocuments->is_allow_eid != '' )
						{
							$result['IsAllowEid'] = 'No';
						}
						else
						{
						
							$result['IsAllowEid'] = 'Yes'; 
						}
					}
					
				
				  if($eidStatus == 2 && ($checkForDocuments->is_allow_eid == NULL || $checkForDocuments->is_allow_eid == ''))
				  {
					  	$result['skipKYC'] = 'No';
				  }
					/* echo $result['skipKYC'];exit; */
					$attrValue = Attributes::where("kyc_require_status",1)->where("kyc_status",1)->where("status",1)->get();
					foreach($attrValue as $attr)
					{
						$code = $attr->attribute_code;
						$valueFound = Employee_attribute::where('emp_id',$empId)->where('attribute_code',$code)->first();
						if($valueFound != '')
							{
								if($valueFound->attribute_values == NULL || $valueFound->attribute_values == '')
								{
									$result['skipKYC'] = 'No'; 
									$result['KYCSelected'] ='No';
								}
							}
							else
							{
								$result['skipKYC'] = 'No'; 
								$result['KYCSelected'] ='No';
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
					$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
			}
			return response()->json($result);
}



	protected function StaticScreenTest(Request $request)
	{
		$requestParameters = $request->input();
		$result = array();
	
			if(isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['empId']) && $requestParameters['empId'] != '' )
			{
			$result = array();
			$result1 = array();
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
		
			$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					$result['responseCode'] = 200;
					$result['message'] = "Successfull";


					$screensData = AppScreens::where('status',1)->orderBy('id', 'desc')->get();

					foreach($screensData as $screen)
					{
						$result['Screen'][$screen->id]['ImageUrl'] = $screen->imageurl;
						$result['Screen'][$screen->id]['Title'] = $screen->title;
						$result['Screen'][$screen->id]['SubTitle'] = $screen->subtitle;
						//$result['Screen'][$screen->id]['Bullet'] = $screen->bullet_points;

						$result['Screen'][$screen->id]['bulletPoints'] = explode(",",$screen->bullet_points);
						


					}


					// foreach($result['Screen'][$screen->id] as $key=>$value)
					// {
					// 	//print_r($value);
					// 	$result1[]=$value;
					// }

					// //exit;
					// //$result['Screen'] = $result1;
					// print_r($result1);



					// $result['Screen'][0]['ImageUrl'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/manage-sales.png';
					// $result['Screen'][0]['Title'] = 'Manage your Test';
					// $result['Screen'][0]['SubTitle'] = 'Sales';
					// $result['Screen'][0]['bulletPoints'][0] = 'View customer details.';
					// $result['Screen'][0]['bulletPoints'][1] = 'Monitor the status of ongoing submissions.';
					// $result['Screen'][0]['bulletPoints'][2] = 'Review submissions history.';
					
					
					// $result['Screen'][1]['ImageUrl'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/manage-leaves.png';
					// $result['Screen'][1]['Title'] = 'Manage your';
					// $result['Screen'][1]['SubTitle'] = 'Leave & Attendance';
					// $result['Screen'][1]['bulletPoints'][0] = 'Online Portals & Transparency.';
					// $result['Screen'][1]['bulletPoints'][1] = 'Mark attendance per day.';
					// $result['Screen'][1]['bulletPoints'][2] = 'Manage your leave.';
					
					
					// $result['Screen'][2]['ImageUrl'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/track-commission.png';
					// $result['Screen'][2]['Title'] = 'Manage your';
					// $result['Screen'][2]['SubTitle'] = 'Sales Commissions';
					// $result['Screen'][2]['bulletPoints'][0] = 'Promotions and scheduling.';
					// $result['Screen'][2]['bulletPoints'][1] = 'Merchandising for actions at point of sale to be effective.';
					// $result['Screen'][2]['bulletPoints'][2] = 'Manage your leave.';
					
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