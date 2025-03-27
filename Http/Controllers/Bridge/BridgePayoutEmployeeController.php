<?php

namespace App\Http\Controllers\Bridge;

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
use App\Models\SEPayout\AgentPayout;
use App\Models\SEPayout\AgentPayoutMidPoint;
use App\Models\SEPayout\AgentPayoutMashreq;
use App\Models\SEPayout\AgentPayoutDeem;
use App\Models\SEPayout\PayoutTlMapping;
use App\Models\SEPayout\AgentPayoutDIB;
use App\Models\Dashboard\DeemAttrition;
use App\Models\Dashboard\RecruiterInfoImport;
use App\Models\EmpOffline\EmpOffline;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionAttributes;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Dashboard\MasterPayout;
use App\Models\Dashboard\UpdatedRecruiterData;



class BridgePayoutEmployeeController extends Controller
{
  
	public function analysisEmpBasicInfo()
	{
		/*
		*ENBD Block
		*Start Code
		*/
		
		$collectionENBD = AgentPayout::orderBy("id","DESC")->first();
		
		if($collectionENBD != '')
		{
			$salesTime = $collectionENBD->sales_time;
			$agentPayoutENBD = AgentPayout::where("sales_time",$salesTime)->get();
			/* echo "<pre>";
			print_r($agentPayoutENBD);
			exit; */
			foreach($agentPayoutENBD as $_agentPEnbd)
			{
				$empDetailsAsPerPayout = Employee_details::where("emp_id",trim($_agentPEnbd->employee_id))->first();
				if($empDetailsAsPerPayout != '')
				{
					$EmpTLID = $empDetailsAsPerPayout->tl_id;
					$PayoutTLID = $this->TLPayoutId($_agentPEnbd->tl_name);
					/*
					*department change log
					*/
					if($empDetailsAsPerPayout->dept_id != 9)
						{
							$empUpdateMod =	Employee_details::find($empDetailsAsPerPayout->id);
							$empUpdateMod->emp_check_payout_status = 4;
							$empUpdateMod->mismatch_key = 'Department';
							$empUpdateMod->save();
						}
					elseif($EmpTLID != $PayoutTLID)
						{
							$empUpdateMod =	Employee_details::find($empDetailsAsPerPayout->id);
							$empUpdateMod->emp_check_payout_status = 4;
							$empUpdateMod->mismatch_key = 'TL';
							$empUpdateMod->save();
						}
					else
						{
							$empUpdateMod =	Employee_details::find($empDetailsAsPerPayout->id);
							$empUpdateMod->emp_check_payout_status = 2;
							$empUpdateMod->mismatch_key = 'Matched';
							$empUpdateMod->save();
						}
					
					$updateModENBD = AgentPayout::find($_agentPEnbd->id);
					$updateModENBD->emp_payout_status = 2;
					$updateModENBD->save();
					/*
					*department change log
					*/
				}
				else
				{
					$updateModENBD = AgentPayout::find($_agentPEnbd->id);
					$updateModENBD->emp_payout_status = 3;
					$updateModENBD->save();
				}
			}
			
		}
		else
		{
			echo "<br/>";
			echo "ENBD Payout Exist";
		}
		echo "done";
		exit;
					
		/*
		*ENBD Block
		*End Code
		*/
	}
	
	protected function TLPayoutId($tlName)
	{
		$tlPayoutTL = PayoutTlMapping::where("tl_name",$tlName)->first();
		if($tlPayoutTL != "")
		{
			return $tlPayoutTL->tl_id;
		}
		else
		{
			return "no";
		}
	}
	
	public function analysisEmpBasicInfoMashreq()
	{
		 echo "yes DONE";
		exit;
		/*
		*Mashreq Block
		*Start Code
		*/
		
		$collectionENBD = AgentPayoutMashreq::orderBy("id","DESC")->first();
		
		if($collectionENBD != '')
		{
			/* echo "<pre>";
			print_r($collectionENBD);
			exit; */
			$salesTime = $collectionENBD->end_sales_time;
			$agentPayoutENBD = AgentPayoutMashreq::where("end_sales_time",$salesTime)->get();
			/* echo "<pre>";
			print_r($agentPayoutENBD);
			exit; */
			foreach($agentPayoutENBD as $_agentPEnbd)
			{
				$empDetailsAsPerPayout = Employee_details::where("emp_id",trim($_agentPEnbd->employee_id))->first();
				if($empDetailsAsPerPayout != '')
				{
					$EmpTLID = $empDetailsAsPerPayout->tl_id;
					$PayoutTLID = $this->TLPayoutId($_agentPEnbd->tl_name);
					/*
					*department change log
					*/
					if($empDetailsAsPerPayout->dept_id != 36)
						{
							$empUpdateMod =	Employee_details::find($empDetailsAsPerPayout->id);
							$empUpdateMod->emp_check_payout_status = 4;
							$empUpdateMod->mismatch_key = 'Department';
							$empUpdateMod->save();
						}
					elseif($EmpTLID != $PayoutTLID)
						{
							$empUpdateMod =	Employee_details::find($empDetailsAsPerPayout->id);
							$empUpdateMod->emp_check_payout_status = 4;
							$empUpdateMod->mismatch_key = 'TL';
							$empUpdateMod->save();
						}
					else
						{
							$empUpdateMod =	Employee_details::find($empDetailsAsPerPayout->id);
							$empUpdateMod->emp_check_payout_status = 2;
							$empUpdateMod->mismatch_key = 'Matched';
							$empUpdateMod->save();
						}
					
					$updateModENBD = AgentPayoutMashreq::find($_agentPEnbd->id);
					$updateModENBD->emp_payout_status = 2;
					$updateModENBD->save();
					/*
					*department change log
					*/
				}
				else
				{
					$updateModENBD = AgentPayoutMashreq::find($_agentPEnbd->id);
					$updateModENBD->emp_payout_status = 3;
					$updateModENBD->save();
				}
			}
			
		}
		else
		{
			echo "<br/>";
			echo "Mashreq Payout Exist";
		}
		echo "done";
		exit;
					
		/*
		*Mashreq Block
		*End Code
		*/
	}
	
	
	
	
	public function analysisEmpBasicInfoDeem()
	{
		 echo "yes DONE-Deem";
		exit;
		/*
		*Mashreq Block
		*Start Code
		*/
		
		$collectionENBD = AgentPayoutDeem::orderBy("id","DESC")->first();
		
		if($collectionENBD != '')
		{
			/* echo "<pre>";
			print_r($collectionENBD);
			exit; */
			$salesTime = $collectionENBD->end_sales_time;
			$agentPayoutENBD = AgentPayoutDeem::where("end_sales_time",$salesTime)->get();
			/* echo "<pre>";
			print_r($agentPayoutENBD);
			exit; */
			foreach($agentPayoutENBD as $_agentPEnbd)
			{
				$empDetailsAsPerPayout = Employee_details::where("emp_id",trim($_agentPEnbd->employee_id))->first();
				if($empDetailsAsPerPayout != '')
				{
					$EmpTLID = $empDetailsAsPerPayout->tl_id;
					$PayoutTLID = $this->TLPayoutId($_agentPEnbd->tl_name);
					/*
					*department change log
					*/
					if($empDetailsAsPerPayout->dept_id != 36)
						{
							$empUpdateMod =	Employee_details::find($empDetailsAsPerPayout->id);
							$empUpdateMod->emp_check_payout_status = 4;
							$empUpdateMod->mismatch_key = 'Department';
							$empUpdateMod->save();
						}
					elseif($EmpTLID != $PayoutTLID)
						{
							$empUpdateMod =	Employee_details::find($empDetailsAsPerPayout->id);
							$empUpdateMod->emp_check_payout_status = 4;
							$empUpdateMod->mismatch_key = 'TL';
							$empUpdateMod->save();
						}
					else
						{
							$empUpdateMod =	Employee_details::find($empDetailsAsPerPayout->id);
							$empUpdateMod->emp_check_payout_status = 2;
							$empUpdateMod->mismatch_key = 'Matched';
							$empUpdateMod->save();
						}
					
					$updateModENBD = AgentPayoutMashreq::find($_agentPEnbd->id);
					$updateModENBD->emp_payout_status = 2;
					$updateModENBD->save();
					/*
					*department change log
					*/
				}
				else
				{
					$updateModENBD = AgentPayoutMashreq::find($_agentPEnbd->id);
					$updateModENBD->emp_payout_status = 3;
					$updateModENBD->save();
				}
			}
			
		}
		else
		{
			echo "<br/>";
			echo "Mashreq Payout Exist";
		}
		echo "done";
		exit;
					
		/*
		*Mashreq Block
		*End Code
		*/
	}
	
	
	public function analysisUpdateTL()
	{
		/*
		*for ENBD TL
		*/
		$empModel = Employee_details::where("emp_check_payout_status",4)->where("mismatch_key","TL")->where("dept_id",9)->get();
		
		foreach($empModel as $_emp)
		{
			$emp_id = $_emp->emp_id;
			$agentModel = AgentPayout::where("employee_id",$emp_id)->orderBy("id","DESC")->first();
			
			$agentTLName = $agentModel->tl_name;
			$tlId = PayoutTlMapping::where("tl_name",$agentTLName)->first()->tl_id;
		
			/*
			*TL Update
			*/
			$empModUpdate = Employee_details::find($_emp->id);
			$empModUpdate->tl_id = $tlId;
			$empModUpdate->emp_check_payout_status = 5;
			$empModUpdate->save();
			/*
			*TL Update
			*/
			
		}
		/*
		*for ENBD TL
		*/
		
		
		/*
		*for Mashreq TL
		*/
		$empModelM = Employee_details::where("emp_check_payout_status",4)->where("mismatch_key","TL")->where("dept_id",36)->get();
		
		foreach($empModelM as $_empM)
		{
			$emp_id = $_empM->emp_id;
			$agentModel = AgentPayoutMashreq::where("employee_id",$emp_id)->orderBy("id","DESC")->first();
			
			$agentTLName = $agentModel->tl_name;
			$tlId = PayoutTlMapping::where("tl_name",$agentTLName)->first()->tl_id;
		
			/*
			*TL Update
			*/
			$empModUpdate = Employee_details::find($_empM->id);
			$empModUpdate->tl_id = $tlId;
			$empModUpdate->emp_check_payout_status = 5;
			$empModUpdate->save();
			/*
			*TL Update
			*/
			
		}
		/*
		*for Mashreq TL
		*/
		
		echo "all done";
		exit;
	}
	
	public function UpdateOffBoardEMPData(Request $request){
		
		$deemdata=DeemAttrition::where("status_attrition",1)->get();
		if(count($deemdata)>0){
			foreach($deemdata as $_deemdata){
				$empId=$_deemdata->emp_id;
				$empData = Employee_details::where("emp_id",$empId)->first();
				if($empData!=''){
						if($empData->offline_status==2){
							$deemModUpdate = DeemAttrition::find($_deemdata->id);
							$deemModUpdate->status_attrition =3;
							$deemModUpdate->save();
						}
						elseif($empData->offline_status==1){
							 $docId=$empId;
							 $onboarding_date=date("Y-m-d");
							 
							 $offlineObj=new EmpOffline();
							 $offlineObj->emp_id=$empData->emp_id;			 
							 $offlineObj->emp_name=$empData->first_name.' '.$empData->middle_name. ' '.$empData->last_name;
										 
							 $offlineObj->designation=$empData->designation_by_doc_collection;
							 $offlineObj->department=$empData->dept_id;
							 $empattributesMod = Employee_attribute::where('emp_id',$docId)->where('attribute_code','CONTACT_NUMBER')->first();
							 if($empattributesMod!=''){
								$offlineObj->mobile_no=$empattributesMod->attribute_values;
							 }else{
								 $offlineObj->mobile_no='';
							 }
							 $work_location = Employee_attribute::where('emp_id',$docId)->where('attribute_code','work_location')->first();
							 if($work_location!=''){
								 $offlineObj->location=$work_location->attribute_values;
							 }
							 else{
								 $offlineObj->location='';
							 }
							 $DOJ= Employee_attribute::where('emp_id',$docId)->where('attribute_code','DOJ')->first();
							 if($DOJ!=''){
								 $offlineObj->doj=$DOJ->attribute_values;
							 }
							 $documentAttributesDetails =DocumentCollectionDetails::where("id",$empData->document_collection_id)->first();
							 //print_r($documentAttributesDetails);exit;
							 
							 if($documentAttributesDetails!=''){
								$offlineObj->email=$documentAttributesDetails->email;
								$offlineObj->recruiter_name=$documentAttributesDetails->recruiter_name;
								$offlineObj->job_opening=$documentAttributesDetails->job_opening;
								$offlineObj->interview_id=$documentAttributesDetails->interview_id;
								$offlineObj->document_collection_id=$documentAttributesDetails->id;
							 }
							 $offlineObj->onboarding_date=$onboarding_date;
							 $offlineObj->condition_leaving=1;
							 $offlineObj->created_by=$request->session()->get('EmployeeId');
							 $offlineObj->save();
							 $updateOBJ = Employee_details::where("emp_id",$docId)->first();
							 $updateOBJ->offline_status=2;
							 
								$updateOBJ->save();
								
								$deemModUpdate = DeemAttrition::find($_deemdata->id);
								$deemModUpdate->status_attrition =2;
								$deemModUpdate->save();
								
							 
						}
					
				}
			}
			
		}
		echo "all done";
		exit;
	}
			
		public function analysisAttritionInfoDeem()
		{
				$deemAttritionData = DeemAttrition::get();
				/* echo "<pre>";
				print_r($deemAttritionData);
				exit; */
				foreach($deemAttritionData as $deem)
				{
					$checkEmpDeem = AgentPayoutDeem::where("emp_id",trim($deem->emp_id))->get();
					if(count($checkEmpDeem) > 0)
					{
						foreach($checkEmpDeem as $emp)
						{
							$update = AgentPayoutDeem::find($emp->id);
							$update->attrition_id = 2;
							$update->last_working_day = $deem->last_working_date;
							$update->reason = $deem->reason;
							$update->save();
						}
						$updateAttrition = DeemAttrition::find($deem->id);
						$updateAttrition->update_status = 2;
						$updateAttrition->save();
					}
					else
					{
						$updateAttrition = DeemAttrition::find($deem->id);
						$updateAttrition->update_status = 3;
						$updateAttrition->save();
					}
				}
				echo "done";
				exit;
		}	
public function getEmpIDWithoutDOJ()
	{
			$employeeDatas = Employee_details::get();
			/* echo "<pre>";
			print_r($employeeDatas);
			exit; */
			$missingDOJ = '';
			foreach($employeeDatas as $emp)
			{
				$empAttr = Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","DOJ")->first();
				if($empAttr == '')
				{
					if($missingDOJ  == '')
					{
						$missingDOJ = $emp->emp_id;
					}
					else
					{
						$missingDOJ = $missingDOJ.",".$emp->emp_id;
					}
					
				}
				else if($empAttr->attribute_values == '' || $empAttr->attribute_values == NULL)
				{
					
					if($missingDOJ  == '')
					{
						$missingDOJ = $emp->emp_id;
					}
					else
					{
						$missingDOJ = $missingDOJ.",".$emp->emp_id;
					}
				}
				else{
					
				}
			}
			echo $missingDOJ;
			
			exit;
			
			
	}

public function updateENBDTL()
	{
			$collection = AgentPayout::groupBy('employee_id')
			->selectRaw('count(*) as total, employee_id')
			->get();
			/* echo "<pre>";
			print_r($collection);
			exit; */
			foreach($collection as $col)
			{
				if($col->employee_id != NULL && $col->employee_id != '')
				{
					
					$agentData = AgentPayout::where("employee_id",$col->employee_id)->orderBy("sort_order","DESC")->first();
					
					$empDetails = Employee_details::where("emp_id",$col->employee_id)->first();
					if($empDetails != '')
					{
						if($empDetails->dept_id == 9)
						{
							 /* echo "<pre>";
							print_r($empDetails);
							exit; */
							$updateObj = Employee_details::find($empDetails->id);
							$updateObj->tl_id = $this->TLPayoutId($agentData->tl_name);
							$updateObj->tl_name = $agentData->tl_name;
							$updateObj->save();
							
						}
					}
				}
			}
			echo "done";
			exit;
			
	}	


public function updateMashreqTL()
	{
			$collection = AgentPayoutMashreq::groupBy('employee_id')
			->selectRaw('count(*) as total, employee_id')
			->get();
			 /* echo "<pre>";
			print_r($collection);
			exit;  */
			foreach($collection as $col)
			{
				if($col->employee_id != NULL && $col->employee_id != '')
				{
					
					$agentData = AgentPayoutMashreq::where("employee_id",$col->employee_id)->orderBy("sort_order","DESC")->first();
					
					$empDetails = Employee_details::where("emp_id",$col->employee_id)->first();
					if($empDetails != '')
					{
						if($empDetails->dept_id == 36)
						{
							 /*  echo "<pre>";
							print_r($empDetails);
							exit;  */
							$updateObj = Employee_details::find($empDetails->id);
							$updateObj->tl_id = $this->TLPayoutId(trim($agentData->tl_name));
							$updateObj->tl_name = $agentData->tl_name;
							$updateObj->save();
							
						}
					}
				}
			}
			echo "done";
			exit;
			
	}	
	
	
	public function updateDeemTL()
	{
			$collection = AgentPayoutDeem::groupBy('emp_id')
			->selectRaw('count(*) as total, emp_id')
			->get();
			  /* echo "<pre>";
			print_r($collection);
			exit;  */ 
			foreach($collection as $col)
			{
				if($col->emp_id != NULL && $col->emp_id != '')
				{
					
					$agentData = AgentPayoutDeem::where("emp_id",$col->emp_id)->orderBy("sort_order","DESC")->first();
					/* echo "<pre>";
			print_r($agentData);
			exit;  */
					$empDetails = Employee_details::where("emp_id",$col->emp_id)->first();
				
					if($empDetails != '')
					{
						if($empDetails->dept_id == 8)
						{
								/* echo "<pre>";
			print_r($empDetails);
			exit;  */
							 /*  echo "<pre>";
							print_r($empDetails);
							exit;  */
							$updateObj = Employee_details::find($empDetails->id);
							$updateObj->tl_id = $this->TLPayoutId(trim($agentData->tl_name));
							$updateObj->tl_name = $agentData->tl_name;
							$updateObj->save();
							
						}
					}
				}
			}
			echo "done";
			exit;
			
	}	
public function checkExistingDepartment()
	{
		$empPayoutBankStatus = Employee_details::where("payout_bank_status",1)->get();
		/* echo "<pre>";
		print_r($empPayoutBankStatus);
		exit; */
		foreach($empPayoutBankStatus as $emp)
		{
			/*
			*ENBD
			*/
			$enbdExistStatus = 1;
			$enbdExist = AgentPayout::where("employee_id",$emp->emp_id)->orderBy("sort_order","DESC")->first();
			if($enbdExist != '')
			{
				$enbdExistStatus = 2;
				$updateENBD = Employee_details::find($emp->id);
				$updateENBD->payout_bank_status = 2;
				$updateENBD->payout_bank = 'ENBD';
				$updateENBD->save();
			}
			/*
			*ENBD
			*/
			
			
			/*
			*mashreq
			*/
			$mExistStatus = 1;
			$mExist = AgentPayoutMashreq::where("employee_id",$emp->emp_id)->orderBy("sort_order","DESC")->first();
			if($mExist != '')
			{
				$mExistStatus = 2;
				$updateENBD = Employee_details::find($emp->id);
				$updateENBD->payout_bank_status = 2;
				if($enbdExistStatus == 2)
				{
					$updateENBD->payout_bank = 'ENBD,Mashreq';
				}
				else
				{
				$updateENBD->payout_bank = 'Mashreq';
				}
				$updateENBD->save();
			}
			/*
			*mashreq
			*/
			
			
			/*
			*Deem
			*/
			$dExistStatus = 1;
			$dExist = AgentPayoutDeem::where("emp_id",$emp->emp_id)->orderBy("sort_order","DESC")->first();
			if($dExist != '')
			{
				$dExistStatus = 2;
				$updateENBD = Employee_details::find($emp->id);
				$updateENBD->payout_bank_status = 2;
				if($enbdExistStatus == 2)
				{
					if($mExistStatus == 2)
					{
					$updateENBD->payout_bank = 'ENBD,Mashreq,DEEM';
					}
					else
					{
						$updateENBD->payout_bank = 'ENBD,DEEM';
					}
				}
				else
				{
					if($mExistStatus == 2)
					{
				$updateENBD->payout_bank = 'Mashreq,DEEM';
					}
					else
					{
						$updateENBD->payout_bank = 'DEEM';
					}
				}
				$updateENBD->save();
			}
			/*
			*Deem
			*/
		}
		echo "OK DONE";
		exit;
	}
	
	public function recruiterUpdate()
	{
		$empdetails = Employee_details::where("document_id_status",1)->get();
		/* echo "<pre>";
		print_r($empdetails);
		exit; */
		foreach($empdetails as $emp)
		{
			if($emp->document_collection_id != NULL && $emp->document_collection_id != '')
			{
				$docId =  $emp->document_collection_id;
				
				$onboardData = DocumentCollectionDetails::where("id",$docId)->first();
				if($onboardData != '')
				{
				
					$recruiter_name = $onboardData->recruiter_name;
					/*
					*update recruiter in main emp table
					*/
						$empUpdateMod = Employee_details::find($emp->id);
						$empUpdateMod->recruiter = $recruiter_name;
						$empUpdateMod->document_id_status = 2;
						$empUpdateMod->save();
					/*
					*update recruiter in main emp table
					*/
				
				}
				else
				{
						$empUpdateMod = Employee_details::find($emp->id);
					
						$empUpdateMod->document_id_status = 3;
						$empUpdateMod->save();
				}
			}
		}
		echo "yes";
		exit;
		
	}
public function recruiterUpdateExcel()
	{
		$recruiterInfoImportModel =  RecruiterInfoImport::get();
	
		/*
		*@update recuriter 
		*/
		$empdetails = Employee_details::where("document_id_status","!=",2)->get();
		/*  echo "<pre>";
		 echo count($empdetails);
		 exit;
		print_r($empdetails);
		exit;  */
		foreach($empdetails as $emp)
		{
			$recruiterInfoImportModel =  RecruiterInfoImport::where("emp_id",$emp->emp_id)->first();
			if($recruiterInfoImportModel != '')
			{
				$employeeUpdateModel = Employee_details::find($emp->id);
				$employeeUpdateModel->recruiter = $recruiterInfoImportModel->recruiter;
				$employeeUpdateModel->document_id_status = 4;
				$employeeUpdateModel->save();
			}
			else
			{
				$employeeUpdateModel = Employee_details::find($emp->id);
				
				$employeeUpdateModel->document_id_status = 5;
				$employeeUpdateModel->save();
			}
		}
		echo "yes";
		exit;
		/*
		*@update recuriter 
		*/

	}
	
	public function recruiterUpdatePayout()
	{
		/*
		*ENBD Recruiter Update
		*/
		
		/* $agentENBDData = AgentPayout::get();
		foreach($agentENBDData as $agent)
		{
			if($agent->employee_id != NULL && $agent->employee_id != '')
			{
				$empId = $agent->employee_id;
				$empData = Employee_details::where("emp_id",$empId)->first();
				if($empData != '')
					{
						$updateENBDPayout = AgentPayout::find($agent->id);
						$updateENBDPayout->recruiter_id = $empData->recruiter;
						if($empData->recruiter != '' && $empData->recruiter != NULL)
						{
						$updateENBDPayout->recruiter_name = $this->recruiterName($empData->recruiter);
						}
						$updateENBDPayout->save();
						
					}
			}
		} */
		/*
		*ENBD Recruiter Update
		*/
		
		
		/*
		*Mashreq Recruiter Update
		*/
		
		/*  $agentMData = AgentPayoutMashreq::get();
		foreach($agentMData as $agent)
		{
			if($agent->employee_id != NULL && $agent->employee_id != '')
			{
				$empId = $agent->employee_id;
				$empData = Employee_details::where("emp_id",$empId)->first();
				if($empData != '')
					{
						$updateMPayout = AgentPayoutMashreq::find($agent->id);
						$updateMPayout->recruiter_id = $empData->recruiter;
						if($empData->recruiter != '' && $empData->recruiter != NULL)
						{
						$updateMPayout->recruiter_name = $this->recruiterName($empData->recruiter);
						}
						$updateMPayout->save();
						
					}
			}
		}  */
		/*
		*Mashreq Recruiter Update
		*/
		
		
		/*
		*Deem Recruiter Update
		*/
		
		  $agentDData = AgentPayoutDeem::get();
		foreach($agentDData as $agent)
		{
			if($agent->emp_id != NULL && $agent->emp_id != '')
			{
				$empId = $agent->emp_id;
				$empData = Employee_details::where("emp_id",$empId)->first();
				if($empData != '')
					{
						$updateDPayout = AgentPayoutDeem::find($agent->id);
						$updateDPayout->recruiter_id = $empData->recruiter;
						if($empData->recruiter != '' && $empData->recruiter != NULL)
						{
						$updateDPayout->recruiter_name = $this->recruiterName($empData->recruiter);
						}
						$updateDPayout->save();
						
					}
			}
		}  
		/*
		*Deem Recruiter Update
		*/
		echo "done";
		exit;
	}
	
	protected function recruiterName($rid)
	{
		$rdetails = RecruiterDetails::where("id",$rid)->first();
		if($rdetails != '')
		{
			return $rdetails->name;
		}
		else
		{
			return "not found";
		}
	}
	
	public function enbdtomaster()
	{
		$enbdData = AgentPayout::get();
		/* echo "<pre>";
		print_r($enbdData);
		exit; */
		foreach($enbdData as $enbd)
		{
			$masterObj = new MasterPayout();
			$masterObj->dept_id = $enbd->dept_id;
			$masterObj->bank_name = "ENBD";
			$masterObj->agent_product = $enbd->agent_product;
			$masterObj->agent_target = $enbd->agent_target;
			$masterObj->agent_name = $enbd->agent_name;
			$masterObj->agent_bank_code = $enbd->agent_bank_code;
			$masterObj->tl_name = $enbd->tl_name;
			$masterObj->tl_id = $this->TLPayoutId($enbd->tl_name);
			$masterObj->location = $enbd->location;
			$masterObj->mass = $enbd->mass;
			$masterObj->premium = $enbd->premium;
			$masterObj->super_premium = $enbd->super_premium;
			$masterObj->personal_loan = $enbd->personal_loan;
			$masterObj->auto_loan = $enbd->auto_loan;
			$masterObj->sup = $enbd->sup;
			$masterObj->tc = $enbd->tc;
			$masterObj->tc_card = $enbd->tc_card;
			$masterObj->total_salary = $enbd->total_salary;
			$masterObj->claw_back = $enbd->claw_back;
			$masterObj->total_revenue = $enbd->total_revenue;
			$masterObj->excess_enbd = $enbd->excess;
			$masterObj->card_rev = $enbd->card_rev;
			$masterObj->loan_rev = $enbd->loan_rev;
			$masterObj->month = $enbd->month;
			$masterObj->year = $enbd->year;
			$masterObj->sales_time = $enbd->sales_time;
			$masterObj->doj = $enbd->doj;
			$masterObj->vintage = $enbd->vintage;
			$masterObj->range_id = $enbd->range_id;
			$masterObj->employee_id_status = $enbd->employee_id_status;
			$masterObj->employee_id = $enbd->employee_id;
			$masterObj->attrition_id = $enbd->attrition_id;
			$masterObj->last_working_day = $enbd->last_working_day;
			$masterObj->reason = $enbd->reason;
			$masterObj->recruiter_name = $enbd->recruiter_name;
			$masterObj->recruiter_id = $enbd->recruiter_id;
			$masterObj->save();
		}
		echo "done";
			exit;
	}
	
	
	public function mashreqtomaster()
	{
		
		$mashreqData = AgentPayoutMashreq::get();
		 /* echo "<pre>";
		print_r($mashreqData);
		exit; */ 
		foreach($mashreqData as $mashreq)
		{
			$masterObj = new MasterPayout();
			$masterObj->dept_id = $mashreq->dept_id;
			$masterObj->bank_name = "Mashreq";
			$masterObj->agent_product = $mashreq->agent_product;
			$masterObj->agent_target = $mashreq->agent_target;
			$masterObj->agent_name = $mashreq->agent_name;
			$masterObj->agent_bank_code = $mashreq->agent_bank_code;
			$masterObj->tl_name = $mashreq->tl_name;
			$masterObj->tl_id = $this->TLPayoutId($mashreq->tl_name);
			$masterObj->location = $mashreq->location;
			$masterObj->cards_mashreq = $mashreq->cards_mashreq;
			$masterObj->cards_point_m = $mashreq->cards_point_m;
			$masterObj->cards_bonus_m = $mashreq->cards_bonus_m;
			$masterObj->extra_point_m = $mashreq->extra_point_m;
			
			$masterObj->personal_loan = $mashreq->personal_loan;
			$masterObj->auto_loan = $mashreq->auto_loan;
			
			
			$masterObj->total_salary = $mashreq->total_salary;
			$masterObj->claw_back = $mashreq->claw_back;
			$masterObj->total_revenue = $mashreq->total_revenue;
			$masterObj->excess_mashreq = $mashreq->excess;
			$masterObj->card_rev = $mashreq->card_rev;
			$masterObj->loan_rev = $mashreq->loan_rev;
			$masterObj->month = $mashreq->month;
			$masterObj->year = $mashreq->year;
			$masterObj->sales_time = $mashreq->end_sales_time;
			$masterObj->doj = $mashreq->doj;
			$masterObj->vintage = $mashreq->vintage;
			$masterObj->range_id = $mashreq->range_id;
			$masterObj->employee_id_status = $mashreq->employee_id_status;
			$masterObj->employee_id = $mashreq->employee_id;
			$masterObj->attrition_id = $mashreq->attrition_id;
			$masterObj->last_working_day = $mashreq->last_working_day;
			$masterObj->reason = $mashreq->reason;
			$masterObj->recruiter_name = $mashreq->recruiter_name;
			$masterObj->recruiter_id = $mashreq->recruiter_id;
			$masterObj->save();
			
		}
		echo "done";
			exit;
	}
	
	public function deemtomaster()
	{
		/* echo "DEEM";
		exit; */
		$deemData = AgentPayoutDeem::get();
		/* echo "<pre>";
		print_r($deemData);
		exit;  */
		foreach($deemData as $deem)
		{
			$masterObj = new MasterPayout();
			$masterObj->dept_id = $deem->dept_id;
			$masterObj->bank_name = "Deem";
			$masterObj->agent_product = $deem->agent_product;
			$masterObj->agent_target = $deem->target;
			$masterObj->agent_name = $deem->agent_name;
			$masterObj->agent_bank_code = $deem->agent_bank_code;
			$masterObj->tl_name = $deem->tl_name;
			$masterObj->tl_id = $this->TLPayoutId($deem->tl_name);
			$masterObj->location = $deem->location;
			$masterObj->no_cards_deem = $deem->no_cards;
			$masterObj->total_card_deem = $deem->total_card;
			
			
			$masterObj->personal_loan = $deem->personal_loan;
			$masterObj->auto_loan = $deem->auto_loan;
			
			
			$masterObj->total_salary = $deem->total_salary;
			
			$masterObj->total_revenue = $deem->cards_revenue+$deem->loan_rev;
			$masterObj->excess_deem = $deem->excess;
			$masterObj->card_rev = $deem->cards_revenue;
			$masterObj->loan_rev = $deem->loan_rev;
			$masterObj->month = $deem->month;
			$masterObj->year = $deem->year;
			$masterObj->sales_time = $deem->sales_time;
			$masterObj->doj = $deem->doj;
			$masterObj->vintage = $deem->vintage;
			$masterObj->range_id = $deem->range_id;
			$masterObj->employee_id_status = $deem->employee_id_status;
			$masterObj->employee_id = $deem->emp_id;
			$masterObj->attrition_id = $deem->attrition_id;
			$masterObj->last_working_day = $deem->last_working_day;
			$masterObj->reason = $deem->reason;
			$masterObj->recruiter_name = $deem->recruiter_name;
			$masterObj->recruiter_id = $deem->recruiter_id;
			$masterObj->save();
			/* echo "done";
			exit; */
			
		}
		echo "done";
			exit;
	}
	
	public function updateRecruiterToEmp()
	{
		$datas = UpdatedRecruiterData::get();
		foreach($datas as $data)
		{
			$employeeExist = Employee_details::where("emp_id",$data->employee_id)->first();
			if($employeeExist != '')
			{
				$empUpdate = Employee_details::find($employeeExist->id);
				$empUpdate->recruiter = $data->source_id;
				$empUpdate->recruiter_name = $data->source;
				$empUpdate->save();
				
				$updateR = UpdatedRecruiterData::find($data->id);
				$updateR->status = 2;
				$updateR->save();
			}
			else
			{
				$updateR = UpdatedRecruiterData::find($data->id);
				$updateR->status = 3;
				$updateR->save();
			}
		}
		echo "done";
		exit;
	}
	
	public function DIBtomaster()
	{
		/* echo "DEEM";
		exit; */
		$dibData = AgentPayoutDIB::get();
	    /* echo "<pre>";
		print_r($dibData);
		exit;  */ 
		foreach($dibData as $dib)
		{
			$masterObj = new MasterPayout();
			$masterObj->dept_id = $dib->dept_id;
			$masterObj->bank_name = "DIB";
			$masterObj->agent_product = $dib->agent_product;
			$masterObj->agent_target = $dib->agent_target;
			$masterObj->agent_name = $dib->agent_name;
			$masterObj->agent_bank_code = $dib->agent_bank_code;
			$masterObj->tl_name = $dib->tl_name;
			//$masterObj->tl_id = $this->TLPayoutId($dib->tl_name);
			$masterObj->location = $dib->location;
			$masterObj->no_card_dib = $dib->no_card;
			$masterObj->tc = $dib->tc;
		
			$masterObj->personal_loan = $dib->personal_loan;
			$masterObj->auto_loan = $dib->auto_loan;
			
			
			$masterObj->total_salary = $dib->total_salary;
			
			$masterObj->total_revenue = $dib->total_revenue;
			$masterObj->excess_dib = $dib->excess;
			
			$masterObj->month = $dib->month;
			$masterObj->year = $dib->year;
			$masterObj->sales_time = $dib->sales_time;
			$masterObj->doj = $dib->doj;
			$masterObj->vintage = $dib->vintage;
			$masterObj->range_id = $dib->range_id;
			$masterObj->employee_id_status = $dib->employee_id_status;
			$masterObj->employee_id = $dib->employee_id;
			$masterObj->attrition_id = $dib->attrition_id;
			$masterObj->last_working_day = $dib->last_working_day;
			$masterObj->reason = $dib->reason;
			$masterObj->recruiter_name = $dib->recruiter_name;
			$masterObj->recruiter_id = $dib->recruiter_id;
			$masterObj->save();
			/* echo "done";
			exit; */ 
			
		}
		echo "done11";
			exit;
	}
	
	
	
public function DNCRAPIToken()
{
	$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://apihub.etisalat.ae:9443/etisalat/resourceapi/confidential/oauth2/token ',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => 'grant_type=client_credentials&scope=apioauth&client_id=72657fc8a51bf4876d30455f210fdca8&client_secret=fd33302085674c34e52f334759afe403',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/x-www-form-urlencoded'
  ),
));

$response = curl_exec($curl);
/* exit; */
curl_close($curl);
$tokenResponse =  json_decode($response);
// echo $response;exit; 
$accessToken = $tokenResponse->access_token;

/*
*getting number DND
*/
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://apihub.etisalat.ae:9443/etisalat/resourceapi/customer/v1.0.0/getDNCRStatus',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{    "accountList": {

        "accountNumber": [

                "0502622680",

            "6478437438",

            "0501549263",

            "0543934762"

        ]    }    }',
  CURLOPT_HTTPHEADER => array(
    'clientId: 72657fc8a51bf4876d30455f210fdca8',
    'X-TIB-TransactionID: TEST12345',
    'X-TIB-RequestedSystem: Smartunion',
    'Content-Type: application/json',
	'authorization: Bearer '.$accessToken,
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;


/* echo "<pre>";
print_r( $info); */
exit;
/*
*getting number DND
*/
}

public function updateLatLong()
	{
		$logData = LoginLog::where("status",1)->get();
		foreach($logData as $log)
		{
			$ip = $log->ip;
			$data = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$ip));
			$updateLog = LoginLog::find($log->id);
			$updateLog->latitude = $data['geoplugin_latitude'];
			$updateLog->longitude = $data['geoplugin_longitude'];
			$updateLog->geoplugin_city = $data['geoplugin_city'];
			$updateLog->geoplugin_region = $data['geoplugin_region'];
			$updateLog->geoplugin_regionCode = $data['geoplugin_regionCode'];
			$updateLog->geoplugin_regionName = $data['geoplugin_regionName'];
			$updateLog->geoplugin_countryCode = $data['geoplugin_countryCode'];
			$updateLog->geoplugin_countryName = $data['geoplugin_countryName'];
			$updateLog->status = 2;
			$updateLog->save();
			
		}
		
	
		echo "done";
		exit;
	}	
}