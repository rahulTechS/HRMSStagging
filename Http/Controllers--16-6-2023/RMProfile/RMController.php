<?php

namespace App\Http\Controllers\RMProfile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use App\Models\RMProfile\RMPerformanceStatus;
use App\Models\RMProfile\RMDetails;
use App\Models\RMProfile\ENBDRMProfile;

use App\Models\MIS\ENBDCardsMisReport;
use App\Http\Controllers\MIS\MisController;
use App\Models\MIS\MainMisReport;
use App\Models\SEPayout\AgentPayout;



class RMController extends Controller
{
  
			
	public function rmProfile()
	{
		/*
		*runing physical Details
		*/
		$cardSubmissionPhyMod = MainMisReport::where("rm_profile_status",1)->where("Employee_status","Verified")->where("file_source","manual")->where("application_id","!=","NOT SOURCED")->orderBy("id","DESC")->first();
		/*
		*runing physical Details
		*/
		if($cardSubmissionPhyMod != '')
		{
			/*
			*check submission is exist or not
			*/
			$checkExistance = ENBDRMProfile::where("app_id",$cardSubmissionPhyMod->application_id)->first();
			if($checkExistance != '')
			{
				$rmPObj = ENBDRMProfile::find($checkExistance->id);
			}
			else
			{
				$rmPObj = new ENBDRMProfile();
			}
			/*
			*check submission is exist or not
			*/
			$makingData = array();
			$makingData['internal_exist_status'] = 1;
				$rmPObj->internal_exist_status = 1;
				
				$makingData['PRODUCT'] = $cardSubmissionPhyMod->PRODUCT;
				$rmPObj->PRODUCT = $cardSubmissionPhyMod->PRODUCT;
				
			$makingData['bank_code'] = $this->getSourceCode($cardSubmissionPhyMod->employee_id);
			$rmPObj->bank_code = $this->getSourceCode($cardSubmissionPhyMod->employee_id);
			
			
			$makingData['agent_id'] = $cardSubmissionPhyMod->employee_id;
			$rmPObj->agent_id = $cardSubmissionPhyMod->employee_id;
			$makingData['app_id'] = $cardSubmissionPhyMod->application_id;
			$rmPObj->app_id = $cardSubmissionPhyMod->application_id;
			
			$makingData['agent_name'] = MisController::getEmployeeName($cardSubmissionPhyMod->employee_id);
			$rmPObj->agent_name = MisController::getEmployeeName($cardSubmissionPhyMod->employee_id);
			
			$makingData['location'] = MisController::getLocation($cardSubmissionPhyMod->employee_id);
			$rmPObj->location = MisController::getLocation($cardSubmissionPhyMod->employee_id);
			
			$makingData['tl_name'] = $this->getTLName($cardSubmissionPhyMod->employee_id);
			$rmPObj->tl_name = $this->getTLName($cardSubmissionPhyMod->employee_id);
			
			$makingData['tl_id'] = $this->getTLID($cardSubmissionPhyMod->employee_id);
			$rmPObj->tl_id = $this->getTLID($cardSubmissionPhyMod->employee_id);
			
			if($cardSubmissionPhyMod->match_status == 2)
			{
				$detailJonus = ENBDCardsMisReport::where("application_id",$cardSubmissionPhyMod->application_id)->first();
				$makingData['sourcing_date'] = $detailJonus->DATEOFSOURCING;
				$rmPObj->sourcing_date = $detailJonus->DATEOFSOURCING;
				$makingData['formatted_sourcing_date'] = date("Y-m-d",strtotime($detailJonus->DATEOFSOURCING));
				$rmPObj->formatted_sourcing_date = date("Y-m-d",strtotime($detailJonus->DATEOFSOURCING));
				$makingData['action_date'] = $detailJonus->LASTUPDATED;
				$rmPObj->action_date = $detailJonus->LASTUPDATED;
				$makingData['formatted_action_date'] = date("Y-m-d",strtotime($detailJonus->LASTUPDATED));
				$rmPObj->formatted_action_date = date("Y-m-d",strtotime($detailJonus->LASTUPDATED));
				$makingData['last_remark'] = $detailJonus->LASTREMARKSADDED;
				$rmPObj->last_remark = $detailJonus->LASTREMARKSADDED;
				$makingData['offer'] = $detailJonus->OFFER;
				$rmPObj->offer = $detailJonus->OFFER;
				$makingData['SCHEME'] = $detailJonus->SCHEME;
				$rmPObj->SCHEME = $detailJonus->SCHEME;
				$makingData['internal_exist_status'] = 2;
				$rmPObj->internal_exist_status = 2;
			}
			else
			{
					$makingData['sourcing_date'] = $cardSubmissionPhyMod->date_of_submission;
				$rmPObj->sourcing_date = $cardSubmissionPhyMod->date_of_submission;
				$makingData['formatted_sourcing_date'] = $cardSubmissionPhyMod->submission_format;
				$rmPObj->formatted_sourcing_date = $cardSubmissionPhyMod->submission_format;
			}
			
			
			
			
			$makingData['status'] = $cardSubmissionPhyMod->approved_notapproved_internal;
			$rmPObj->status = $cardSubmissionPhyMod->approved_notapproved_internal;
			$makingData['status_id'] = $cardSubmissionPhyMod->approved_notapproved_internal;
			$rmPObj->status_id = $cardSubmissionPhyMod->approved_notapproved_internal;
			$makingData['Card'] = $cardSubmissionPhyMod->Card_Name;
				$rmPObj->Card = $cardSubmissionPhyMod->Card_Name;
			
			
			
			$makingData['customer_name'] = $cardSubmissionPhyMod->cm_name;
			$rmPObj->customer_name = $cardSubmissionPhyMod->cm_name;
			$makingData['customer_mobile'] = $cardSubmissionPhyMod->CV_MOBILE_NUMBER;
				$rmPObj->customer_mobile = $cardSubmissionPhyMod->CV_MOBILE_NUMBER;
				
				
				$makingData['customer_salary'] = $cardSubmissionPhyMod->SALARY;
				$rmPObj->customer_salary = $cardSubmissionPhyMod->SALARY;
				
				
				$makingData['customer_company_name'] = $cardSubmissionPhyMod->fv_company_name;
				$rmPObj->customer_company_name = $cardSubmissionPhyMod->fv_company_name;
				
				$makingData['company_type'] = $cardSubmissionPhyMod->ALE_NALE;
				$rmPObj->company_type = $cardSubmissionPhyMod->ALE_NALE;
				if($cardSubmissionPhyMod->approved_notapproved_internal == 3)
				{
					$cardSubmissionPhyMod->mothly_end_internal;
					$rmPObj->monthly_end = $cardSubmissionPhyMod->mothly_end_internal;
					$saleTArray = explode("-",$cardSubmissionPhyMod->mothly_end_internal);
					$saleTArray1 = $saleTArray[1];
					$saleTArray2 = explode(" ",$saleTArray1);
					
					 $saleT  = date("m",strtotime($saleTArray2[1])).'-'.$saleTArray2[2];
					 $makingData['sale_time'] = $saleT;
				     $rmPObj->sale_time = $saleT;
				}
				else
				{
					 $saleT   = date("m",strtotime($cardSubmissionPhyMod->submission_format)).'-'.date("Y",strtotime($cardSubmissionPhyMod->submission_format));
					 $makingData['sale_time'] = $saleT;
				     $rmPObj->sale_time = $saleT;
					
				}
				
				
			/*
			*checking for agent Infomation
			*/
			$p1code = $this->getSourceCode($cardSubmissionPhyMod->employee_id);
			$rmDetails = AgentPayout::where("sales_time",$saleT)->where("agent_bank_code",$p1code)->first();
			if($rmDetails != '')
			{
					$makingData['agent_profile_id'] = $rmDetails->id;
					$rmPObj->agent_profile_id = $rmDetails->id;
					
					$makingData['agent_salary'] = $rmDetails->total_salary;
					$rmPObj->agent_salary = $rmDetails->total_salary;
					
					
					$makingData['agent_vintage'] = $rmDetails->vintage;
					$rmPObj->agent_vintage = $rmDetails->vintage;
					$rmPObj->doj = $rmDetails->doj;
					
					$makingData['find_agent_status'] = 'Yes';
					$rmPObj->find_agent_status = 'Yes';
			}
			else
			{
				$rmDetails = AgentPayout::where("agent_bank_code",$p1code)->orderBy("id","DESC")->first();
				if($rmDetails != '')
				{
						$salesTimeArray = explode("-",$saleT);
								if($salesTimeArray[0] == 2)
								{
									$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-28';
								}
								else
								{
								$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-30';
								}
					   $doj= $rmDetails->doj; 
						$daysInterval = abs(strtotime($salesTimeValue)-strtotime($doj))/ (60 * 60 * 24);
						$makingData['agent_salary'] = $rmDetails->total_salary;
						$rmPObj->agent_salary = $rmDetails->total_salary;
						
						
						$makingData['agent_vintage'] = $daysInterval;
						$rmPObj->agent_vintage = $daysInterval;
						$rmPObj->doj = $rmDetails->doj;
						$makingData['find_agent_status'] = 'No';
						$rmPObj->find_agent_status = 'No';
				}
				else
				{
				$rmPObj->find_agent_status = 'No';
				$makingData['find_agent_status'] = 'No';
				}
			}
			/*
			*checking for agent Infomation
			*/
			
			if($rmPObj->save())
			{
				
				$updateMe = MainMisReport::find($cardSubmissionPhyMod->id);
				$updateMe->rm_profile_status = 2;
				$updateMe->save();
			}
			else
			{
				$updateMe = MainMisReport::find($cardSubmissionPhyMod->id);
				$updateMe->rm_profile_status = 3;
				$updateMe->save();
			}
		}
		
		
		/* echo '<pre>';
		print_r($makingData); */
		echo "yes";
		exit;
	}
	
	
	protected function getTLID($eid)
	{
				$empData =Employee_details::where("id",$eid)->first();
				if($empData != '')
				{
				return $empData->tl_id;
				}
				else
				{
				return '--';
				}
	}
	
	protected function getTLName($eid)
	{
				$empData =Employee_details::where("id",$eid)->first();
				if($empData != '')
				{
				return MisController::getEmployeeName($empData->tl_id);
				}
				else
				{
				return '--';
				}
	}	
	
	protected function getSourceCode($eid)
	{
				$empData =Employee_details::where("id",$eid)->first();
				if($empData != '')
				{
				return $empData->source_code;
				}
				else
				{
				return '--';
				}
	}	
	
	public function RMProfileMaking()
	{
		$salestimeUniq = AgentPayout::groupBy('sales_time')->selectRaw('count(*) as total, sales_time')->get();
		foreach($salestimeUniq as $rmList)
		{
			$rm = AgentPayout::where("sales_time",$rmList->sales_time)->first();
			$rmDetails = new RMDetails();
			$rmDetails->agent_product = $rm->agent_product;
			$rmDetails->bank_code = $rm->agent_bank_code;
			$rmDetails->agent_salary = $rm->total_salary;
			$rmDetails->total_revenue = $rm->total_revenue;
			$rmDetails->excess = $rm->excess;
			$rmDetails->sales_time = $rm->sales_time;
			$rmDetails->vitange_salestime = $rm->vintage;
			$rmDetails->range_id = $rm->range_id;
			$rmDetails->total_cards = $rm->tc_card;
			$rmDetails->total_cards_including_loan = $rm->tc_final;
			$rmDetails->Mass = $rm->mass;
			$rmDetails->Premium = $rm->premium;
			$rmDetails->Super_Premium = $rm->super_premium;
			$rmDetails->doj = date("Y-m-d",strtotime($rm->doj));
			$rmDetails->dojtxt = $rm->doj;
			$rmDetails->agent_name = $rm->agent_name;
			$rmDetails->save();
		
		}
		
		echo "yes";
		exit;
		
	}
			
}
