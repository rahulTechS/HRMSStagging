<?php
namespace App\Http\Controllers\API\V1;

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
use Crypt;

class LandingController extends Controller
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

public function landingPageProfile(Request $request)
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
				
			  
			   
					
				 //$result = $this->getAttributeDetails($empData,$empId);
				
			
				 
				 $empDetails = Employee_details::where("emp_id",$empId)->first();
				 
					
					$result['Values']['Basic'][0]['Tab'] = 'Basic';
		
					$result['Values']['Basic'][0]['empName'] = $empDetails->emp_name;
					
				
					$result['Values']['Basic'][0]['EmpId'] = $empId;
					
					
					$result['Values']['Basic'][0]['Department'] = $this->getDepartmentName($empDetails->dept_id);
					$result['Values']['Basic'][0]['DepartmentId'] = $empDetails->dept_id;
				
					

					$result['Values']['Basic'][0]['Designation'] = $this->getDesignationName($empDetails->designation_by_doc_collection);
			     	$result['Values']['Basic'][0]['DesignationId'] = $empDetails->designation_by_doc_collection;
					
			
					$result['Values']['Basic'][0]['Function Name'] = $this->getFuncName($empDetails->job_function);
					$result['Values']['Basic'][0]['FunctionId'] = $empDetails->job_function;
			
					
					
				 /* $result['bottomMenu'][0]['Title'] = 'Home'; 
				 $result['bottomMenu'][0]['Icon'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/home.png'; 
				 $result['bottomMenu'][0]['IconSelected'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/home-active.png'; 
				 $result['bottomMenu'][0]['identifier'] = 150;  */
				   $result['bottomMenu'][0]['Title'] = 'Profile'; 
				 $result['bottomMenu'][0]['Icon'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/profile.png'; 
				 $result['bottomMenu'][0]['IconSelected'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/profile-active.png'; 
				  $result['bottomMenu'][0]['identifier'] = 154; 
				  $result['bottomMenu'][0]['DefaultSelected'] = true;

				  
				   $result['bottomMenu'][1]['Title'] = 'Sales'; 
				 $result['bottomMenu'][1]['Icon'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/sales-management.png'; 
				 $result['bottomMenu'][1]['IconSelected'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/sales-management-active.png'; 
				  $result['bottomMenu'][1]['identifier'] = 152; 
				  $result['bottomMenu'][1]['DefaultSelected'] = false;
				  
				     $result['bottomMenu'][2]['Title'] = 'Add Sales'; 
				 $result['bottomMenu'][2]['Icon'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/add-submission.png'; 
				 $result['bottomMenu'][2]['IconSelected'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/add-submission-active.png'; 
				  $result['bottomMenu'][2]['identifier'] = 153; 
				   $result['bottomMenu'][2]['DefaultSelected'] = false;

				  
				    $result['bottomMenu'][3]['Title'] = 'Cross Sales'; 
				 $result['bottomMenu'][3]['Icon'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/add-submission.png'; 
				 $result['bottomMenu'][3]['IconSelected'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/add-submission-active.png'; 
				  $result['bottomMenu'][3]['identifier'] = 160; 
				   $result['bottomMenu'][3]['DefaultSelected'] = false; 
				  
				/*  $result['bottomMenu'][3]['Title'] = 'Call'; 
				 $result['bottomMenu'][3]['Icon'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/call-management.png'; 
				 $result['bottomMenu'][3]['IconSelected'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/call-management-active.png'; 
				  $result['bottomMenu'][3]['identifier'] = 151; 
				 $result['bottomMenu'][3]['DefaultSelected'] = false; */
				
				/*  $result['bottomMenu'][3]['Title'] = 'Historical'; 
				 $result['bottomMenu'][3]['Icon'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/historical-performance.png';
				 $result['bottomMenu'][3]['IconSelected'] = 'https://www.hr-suranigroup.com/hrm/img/mobile-icon/historical-performance-active.png';
				 $result['bottomMenu'][3]['identifier'] = 153;  */
				
				 $empData = EmpAppAccess::where("employee_id",$empId)->first();
				 if($empData != '')
				 {
					 if($empData->pics != '' && $empData->pics != NULL)
					 {
					  $result['TopMenu']['CandidatePic'] =  'https://www.hr-suranigroup.com/uploads/ApiDocs/'.$empData->pics;
					 }
					 else
					 {
						  $result['TopMenu']['CandidatePic'] =  '';
					 }
					  $result['TopMenu']['CallIcon'] =  'https://www.hr-suranigroup.com/hrm/img/mobile-icon/call-icon.png';
					 $result['TopMenu']['FirstName'] = $empDetails->first_name;
					 $result['TopMenu']['CompleteName'] = $empDetails->emp_name;
				}
				 else
				 {
					 $result['TopMenu']['CandidatePic'] =  '';
					  $result['TopMenu']['CallIcon'] =  '';
					   $result['TopMenu']['FirstName'] ='';
					 $result['TopMenu']['CompleteName'] = '';
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



protected function getAttributeDetails($empData,$empId)
{
	 
					/*
					*check for data
					*/
					$kycFields = Employee_attribute::where('emp_id',$empId)->where("status",1)->get();
					$fieldsFilled = 0;
					$kycDetails = array();
				$kycDetails['Tabs'] = array();
					
					$i=0;
					$i1 = 0;
					foreach($kycFields as $fields)
					{
						
							 $code = $fields->attribute_code;
							/* print_r($fields);
							exit; */
							$valueFound = Attributes::where('attribute_code',$code)->first();
							/*  echo "<pre>";
							print_r($valueFound);
							exit; */ 
							if($valueFound != '')
							{
								if($fields->attribute_values != NULL && $fields->attribute_values != '')
								{
								
								   if(!isset($kycDetails['Values'][$this->getTabName($valueFound->tab_name)]))
								   {
									   $i = 0;
								   }
								   else
								   {
									    $i = count($kycDetails['Values'][$this->getTabName($valueFound->tab_name)]);
										
								   }
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['Name'] = $valueFound->attribute_name;
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['Tab'] = $this->getTabName($valueFound->tab_name);
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['Type'] = $valueFound->attrbute_type_id;
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['AttributeCode'] = $fields->attribute_code;
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['AttributeValue'] = $fields->attribute_values;
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['existStatus'] = 1;
									
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['options'] = '';
									$kycAttributeData = Attributes::where("attribute_code",$fields->attribute_code)->first();
									if($kycAttributeData != '')
									{
										$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['options'] = json_decode($kycAttributeData->opt_option);
									}
									
									$fieldsFilled++;
								}
								else
								{
									 if(!isset($kycDetails['Values'][$this->getTabName($valueFound->tab_name)]))
								   {
									   $i = 0;
								   }
								   else
								   {
									    $i = count($kycDetails['Values'][$this->getTabName($valueFound->tab_name)]);
										 $i = $i-1;
								   }
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['Name'] = $valueFound->attribute_name;
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['Tab'] = $this->getTabName($valueFound->tab_name);
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['Type'] = $valueFound->attrbute_type_id;
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['AttributeCode'] = $fields->attribute_code;
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['AttributeValue'] ='';
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['existStatus'] = 3;
										
									$kycDetails['Values'][$this->getTabName($valueFound->tab_name)][$i]['options'] = '';
									$kycAttributeData = Attributes::where("attribute_code",$valueFound->attribute_code)->first();
									if($kycAttributeData != '')
									{
										$kycDetails['Values']['options'] = json_decode($kycAttributeData->opt_option);
									}
								}
								$t = $this->getTabName($valueFound->tab_name);
							$tabA = $kycDetails['Tabs'];
							
							if(!in_array($t,$tabA))
							{
								$kycDetails['Tabs'][] = $this->getTabName($valueFound->tab_name);
								
							}
							}
							
							
							
							$i++;
						
						
					}
					
					
					foreach($kycDetails['Tabs'] as $tab)
					{
							$kycDetails['TabsSort'][$tab] = $this->getOrder($tab);
					}
					
					return $kycDetails;
}	

protected function getOrder($_tab)
{
	if($_tab == 'Personal')
						{
							return 1;
						}
						elseif($_tab == 'Visa & Insurance')
						{						
						return 2;
						}
						elseif($_tab == 'Company')
						{
						return 3;
						}
						elseif($_tab == 'Compensation & Payroll')
						{						
						return 4;
						}
						elseif($_tab == 'Deployment')
						{	
							return 5;
						}
						elseif($_tab == 'Hiring')
						{	
							return 6;
						}
							
						else
						{
							return 'none';
						}
}
protected function getTabName($_tab)
{
						if($_tab == 'p_d')
						{
							return 'Personal';
						}
						elseif($_tab == 'v_d')
						{						
						return 'Visa & Insurance';
						}
						elseif($_tab == 'c_d')
						{
						return 'Company';
						}
						elseif($_tab == 'b_d')
						{						
						return 'Compensation & Payroll';
						}
						elseif($_tab == 'deploy_d')
						{	
							return 'Deployment';
						}
						elseif($_tab == 'hiring_d')
						{	
							return 'Hiring';
						}
							
						else
						{
							return 'none';
						}
}
}