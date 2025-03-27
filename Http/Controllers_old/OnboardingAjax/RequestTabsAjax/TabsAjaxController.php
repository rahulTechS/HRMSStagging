<?php

namespace App\Http\Controllers\OnboardingAjax\RequestTabsAjax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use App\Models\Company\Department;
use App\Models\Company\Product;
use App\Models\Recruiter\Designation;
use App\Models\Offerletter\SalaryBreakup;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionAttributes;
use  App\Models\Attribute\Attributes;
use App\Models\Employee\Employee_attribute;
use App\Models\Employee\Employee_details;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use App\Models\Onboarding\HiringSourceDetails;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Onboarding\IncentiveLetterDetails;
use App\Models\Onboarding\TrainingProcess;
use App\Models\Onboarding\TrainingType;
use App\Models\Onboarding\TrainingStages;
use Illuminate\Support\Facades\Validator;
use  App\Models\Attribute\AttributeType;
use App\Models\Offerletter\OfferletterDetails;
use App\Models\Visa\visaType;
use App\Models\Visa\VisaStage;
use App\Models\Visa\Visaprocess;
use UserPermissionAuth;



class TabsAjaxController extends Controller
{
    
      
	   public function summaryTabWithFullViewAjax(Request $request)
	   {
		    $documentCollectId = $request->documentCollectionId;
		    $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
			
			/*
			*upload document values with label
			*start code
			*/
				$documentCollectionValues = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectId)->get();
				/* echo '<pre>';
				print_r($documentCollectionValues);
				exit; */
				$docCollectionDetails = array();
				foreach($documentCollectionValues as $_docCollectionValue)
				{
					
					$attrId = $_docCollectionValue->attribute_code;
					$docAttributes = DocumentCollectionAttributes::where("id",$attrId)->first();
					if($docAttributes != '')
					{
					$attributeName = $docAttributes->attribute_name.'^'.$docAttributes->attrbute_type_id;
					
					
					$attributeValue = $_docCollectionValue->attribute_value;
					$docCollectionDetails[$attributeName] = $attributeValue;
					}
				}
				
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
		    $stepsAll[0]['name'] = 'Document Collection Request'; 
			$stepsAll[0]['stage'] = 'active'; 
			$stepsAll[0]['slagURL'] = 'tab2'; 
			$stepsAll[0]['tab'] = 'active'; 
			 $stepsAll[0]['onclick'] = 'tab2Panel();'; 
			
			$OnboardingProgress = 'Document Collection Request';
			/*Step1*/
		    
			/*Step2*/
			$stepsAll[1]['name'] = 'Document Collection (Offerletter)'; 
		    if($documentCollectionDetails->status == 1 || $documentCollectionDetails->status == 3)
			{
				$stepsAll[1]['stage'] = 'inprogress'; 
			}
			else
			{
				$completedStep++;
				$stepsAll[1]['stage'] = 'active'; 
				$OnboardingProgress = 'Document Collection (Offerletter)';
			}
			$stepsAll[1]['slagURL'] = 'tab3'; 
			$stepsAll[1]['onclick'] = 'tab3Panel();'; 
			$stepsAll[1]['Tab'] = 'active'; 
			/*Step2*/
			
			/*Step3*/
			$stepsAll[2]['name'] = 'Offer Letter Generated'; 
			if($documentCollectionDetails->status == 2)
			{
				$stepsAll[2]['stage'] = 'inprogress'; 
				$stepsAll[2]['Tab'] = 'active'; 
			}
			else if($documentCollectionDetails->status > 2 && $documentCollectionDetails->status != 3)
			{
				$completedStep++;
				$stepsAll[2]['stage'] = 'active'; 
				$OnboardingProgress = 'Offer Letter Generated';
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
			
			/*Step4*/
			$stepsAll[3]['name'] = 'Upload Signed Offer Letter'; 
			if($documentCollectionDetails->status == 4)
			{
				$stepsAll[3]['stage'] = 'inprogress'; 
				$stepsAll[3]['Tab'] = 'active'; 
			}
			else if($documentCollectionDetails->status > 4)
			{
				$completedStep++;
				$stepsAll[3]['stage'] = 'active'; 
				$OnboardingProgress = 'Upload Signed Offer Letter';
				$stepsAll[3]['Tab'] = 'active'; 
			}
			else
			{
				$stepsAll[3]['stage'] = 'pending'; 
				$stepsAll[3]['Tab'] = 'disabled-tab'; 
			}
			$stepsAll[3]['slagURL'] = 'tab41'; 
			$stepsAll[3]['onclick'] = 'tab4Panel();'; 			
			/*Step4*/
			
			
			/*Step5*/
			$stepsAll[4]['name'] = 'Document Collection (Visa)'; 
		    if($documentCollectionDetails->status == 5)
			{
				$stepsAll[4]['stage'] = 'inprogress'; 
				$stepsAll[4]['Tab'] = 'active'; 
			}
			else if($documentCollectionDetails->status > 5)
			{
				$completedStep++;
				$stepsAll[4]['stage'] = 'active'; 
				$OnboardingProgress = 'Document Collection (Visa)';
				$stepsAll[4]['Tab'] = 'active'; 
			}
			else
			{
				$stepsAll[4]['stage'] = 'pending'; 
				$stepsAll[4]['Tab'] = 'disabled-tab'; 
			}
			$stepsAll[4]['slagURL'] = 'tab5';  
			$stepsAll[4]['onclick'] = 'tab5Panel();'; 			
			/*Step5*/
			
			$stepsAll[5]['name'] = 'Visa Process'; 
			if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->visa_process_status != 4)
			{
				 $stepsAll[5]['stage'] = 'inprogress'; 
				 $stepsAll[5]['Tab'] = 'active'; 
			}
			else if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->visa_process_status == 4)
			{
				$completedStep++;
				 $stepsAll[5]['stage'] = 'active'; 
				 $OnboardingProgress = 'Visa Process';
				  $stepsAll[5]['Tab'] = 'active'; 
			}
			else
			{
				 $stepsAll[5]['stage'] = 'pending'; 
				  $stepsAll[5]['Tab'] = 'disabled-tab'; 
			}
		   
			$stepsAll[5]['slagURL'] = 'visaProcess'; 
			$stepsAll[5]['onclick'] = 'tabvisaProcessPanel();'; 		
			
			$stepsAll[6]['name'] = 'Training and On-Boarding Process'; 
			if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->training_process_status != 4)
			{
				$stepsAll[6]['stage'] = 'inprogress'; 
				$stepsAll[6]['Tab'] = 'active';
			}
			else if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->training_process_status == 4)
			{
				$completedStep++;
				$stepsAll[6]['stage'] = 'active';
				$stepsAll[6]['Tab'] = 'active';				
			}
			else
			{
				$stepsAll[6]['stage'] = 'pending'; 
				 $stepsAll[6]['Tab'] = 'disabled-tab';
			}
			
		    
			$stepsAll[6]['slagURL'] = 'tab6';
			 $stepsAll[6]['onclick'] = 'tabtrainingProcessPanel();'; 	
			
			$stepsAll[7]['name'] = 'Bank Code Generation Process'; 
			if($documentCollectionDetails->status == 6)
			{
				$stepsAll[7]['stage'] = 'inprogress'; 
				$stepsAll[7]['Tab'] = 'active';
			}
			else if($documentCollectionDetails->status > 6)
			{
				$completedStep++;
				$stepsAll[7]['stage'] = 'active';
				$stepsAll[7]['Tab'] = 'active';				
			}
			else
			{
				$stepsAll[7]['stage'] = 'pending'; 
				 $stepsAll[7]['Tab'] = 'disabled-tab';
			}
			$stepsAll[7]['slagURL'] = 'tab7';
		    $stepsAll[7]['onclick'] = 'codeGeneration();'; 	
			
			
			$stepsAll[8]['name'] = 'On-Boarding Completed'; 
			
			if(($documentCollectionDetails->status == 7) || ($documentCollectionDetails->department == 8 && $documentCollectionDetails->status == 6))
			{
				$stepsAll[8]['stage'] = 'inprogress'; 
				$stepsAll[8]['Tab'] = 'active';
			}
			else if($documentCollectionDetails->status > 7)
			{
				$completedStep++;
				$stepsAll[8]['stage'] = 'active';
				$stepsAll[8]['Tab'] = 'active';				
			}
			else
			{
				$stepsAll[8]['stage'] = 'pending'; 
				 $stepsAll[8]['Tab'] = 'disabled-tab';
			}
			
		    $stepsAll[8]['slagURL'] = 'tab8';
			 $stepsAll[8]['onclick'] = 'finalization();'; 	
			$totalStep = 9;
			$p = $completedStep/$totalStep;
			$percentange = round($p*100);
			/*
			*Define All steps
			*end code
			*/
			$visaProcessLists = Visaprocess::where("document_id",$documentCollectId)->orderBy('id','DESC')->get();
			return view("OnboardingAjax/RequestTabsAjax/summaryTabWithFullViewAjax",compact('documentCollectionDetails','docCollectionDetails','visaProcessLists','stepsAll','percentange','OnboardingProgress'));
	   }
	   
	   
	   
	   public function signedOfferLetter(Request $request)
	   {
		     $documentCollectId = $request->documentCollectionId;
			  $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
			 return view("Onboarding/RequestTabs/signedOfferLetter",compact('documentCollectId','documentCollectionDetails')); 
	   }
	   public function signedOfferLetterAjax(Request $request)
	   {
		     $documentCollectId = $request->documentCollectionId;
			  $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
			 return view("OnboardingAjax/RequestTabsAjax/signedOfferLetterAjax",compact('documentCollectId','documentCollectionDetails')); 
	   }
	   public function uploadSignOfferLetterPostAjax(Request $request)
	   {
		    $selectedFilter = $request->input();
			
			$documentCollectId = $selectedFilter['documentCollectionID'];
			  $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
			    $key = 'signed_offerletter';
			   if($request->file($key))
				{
			    $filenameWithExt = $request->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$request->file($key)->getClientOriginalExtension();
				
				$newFileName = $documentCollectionDetails->emp_name.'-'.$documentCollectId.'-SignedOfferLetter.'.$fileExtension;
				
				    if(file_exists(public_path('uploads/SignedOfferLetters/'.$newFileName))){

					  unlink(public_path('uploads/SignedOfferLetters/'.$newFileName));

					}
				
				/*
				*Updating File Name
				*/
				
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $request->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$request->file($key)->move(public_path('uploads/SignedOfferLetters/'), $newFileName);
				
				
				/*
				*update request collection Model
				*start code
				*/
				$updateDocumentCollectionObj = DocumentCollectionDetails::find($documentCollectId);
				$updateDocumentCollectionObj->signed_offerletter_name = $newFileName;
				$getExistingStatus = DocumentCollectionDetails::where("id",$documentCollectId)->first()->status;
				if($getExistingStatus < 5)
				{
				$updateDocumentCollectionObj->status = 5;
				
				$updateDocumentCollectionObj->serialized_id = 'signedOfferletter-000'.$documentCollectId;
				}
				$updateDocumentCollectionObj->signed_offerletter_date = date("Y-m-d");
				$updateDocumentCollectionObj->save();
				
				/*
				*update request collection Model
				*end code
				*/
					echo 'Signed offer letter uploaded successfully.';
					exit;
				}
				else
				{
					echo 'error','issue to upload Signed offer letter.';
					exit;
				}
	   }
	   
	   public function uploadVisaDocumentAjax(Request $request)
	   {
		   $uploadDetails = array();
		   $id = $request->id; 
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   $documentAttributes = DocumentCollectionAttributes::where("status",1)->where("attribute_area","both")->orWhere("attribute_area","visaprocess")->get();
		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
		   }
			
			/* echo '<pre>';
			print_r($uploadDetails);
			exit; */
		   return view("OnboardingAjax/RequestTabsAjax/uploadVisaDocumentAjax",compact('documentDetails','documentAttributes','uploadDetails'));
	   }
	   
	   public function uploadVisaDocumentStartAjax(Request $request)
	   {
		    $selectedFilter = $request->input();
		  
		   $saveData = array();
		  
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		   $status = $selectedFilter['status'];
		   $num = $documentCollectionId;
		    unset($selectedFilter['_token']);
		    unset($selectedFilter['status']);
		    unset($selectedFilter['documentCollectionID']);
		    unset($selectedFilter['_url']);
			
		   
		
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			
			foreach($keys as $key)
			{
				if($request->file($key))
				{
				 $filenameWithExt = $request->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$request->file($key)->getClientOriginalExtension();
				$vKey = $key;
				$newFileName = $key.'-'.$num.'.'.$fileExtension;
				
				    if(file_exists(public_path('documentCollectionFiles/'.$newFileName))){

					  unlink(public_path('documentCollectionFiles/'.$newFileName));

					}
				
				/*
				*Updating File Name
				*/
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $request->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$request->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
				$fileIndex++;
				}
			}
			
			
			
			foreach($selectedFilter as $key=>$value)
			{
				if($value != '' && $value != 'undefined')
				{
				$existDocument = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectionId)->where("attribute_code",$key)->first();
				if($existDocument != '')
				{
					$objDocument= DocumentCollectionDetailsValues::find($existDocument->id);
				}
				else
				{
				$objDocument = new DocumentCollectionDetailsValues();	
				}	
				
				$objDocument->document_collection_id = $documentCollectionId;
				$objDocument->attribute_code = $key;
				$objDocument->attribute_value = $value;
				$objDocument->save();
				}
				
			}
			
			foreach($keys as $key)
			{
				if(in_array($key,$listOfAttribute))
				{
					
					$existDocument = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectionId)->where("attribute_code",$key)->first();
					if($existDocument != '')
					{
						$objDocument= DocumentCollectionDetailsValues::find($existDocument->id);
					}
					else
					{
						$objDocument = new DocumentCollectionDetailsValues();
					}
					$objDocument->document_collection_id = $documentCollectionId;
					$objDocument->attribute_code = $key;
					$objDocument->attribute_value = $filesAttributeInfo[$key];
					$objDocument->save();
					
				}
			}
			
		
			/*
			*update Status on main Document Collection table
			*/
			$documentCollectionMod = DocumentCollectionDetails::find($documentCollectionId);
			
			$getExistingStatus = DocumentCollectionDetails::where("id",$documentCollectionId)->first()->status;
			if($getExistingStatus < 6)
			{
				$documentCollectionMod->status = $status;
			if($status == 6)
			{
				$documentCollectionMod->serialized_id = 'VisaDocCollection-Completed-000'.$documentCollectionId;
				$documentCollectionMod->upload_visa_document_date = date("Y-m-d");
			}
			else
			{
				$documentCollectionMod->serialized_id = 'VisaDocCollection-Inprogress-000'.$documentCollectionId;
			}
			}
			$documentCollectionMod->save();
			echo 'Visa Document Upload Successfully.';
			exit;
		  
	   }
	   
	   public function tab2EmpDetailsAjax(Request $request)
	   {
		      $documentCollectId = $request->documentCollectionId;
			 $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
			
			 return view("OnboardingAjax/RequestTabsAjax/employeeDetailsAjax",compact('documentCollectionDetails')); 
	   }
	   
	   
	    public function tab3EmpDetailsAjax(Request $request)
		   {
				  $documentCollectId = $request->documentCollectionId;
				 $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
				$documentCollectionValues = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectId)->get();
				/* echo '<pre>';
				print_r($documentCollectionValues);
				exit; */
				$docCollectionDetails = array();
				foreach($documentCollectionValues as $_docCollectionValue)
				{
					
					$attrId = $_docCollectionValue->attribute_code;
					$docAttributes = DocumentCollectionAttributes::where("id",$attrId)->first();
					if($docAttributes != '')
					{
						if($docAttributes->attribute_area== 'offerletter' || $docAttributes->attribute_area== 'both')
						{
							$attributeName = $docAttributes->attribute_name.'^'.$docAttributes->attrbute_type_id;
							$attributeValue = $_docCollectionValue->attribute_value;
							if($attributeValue != 'undefined')
							{
							$docCollectionDetails[$attributeName] = $attributeValue;
							}
						}
					}
				}
				/* echo '<pre>';
				print_r($docCollectionDetails);
				exit;  */
				 return view("OnboardingAjax/RequestTabsAjax/offerLetterDocumentAjax",compact('documentCollectionDetails','docCollectionDetails')); 
		   }
		   
		  public function tab4EmpDetailsAjax(Request $request)
		   {
				  $documentCollectId = $request->documentCollectionId;
				 $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
				
				 return view("OnboardingAjax/RequestTabsAjax/offerIncentiveletterAjax",compact('documentCollectionDetails')); 
		   }
		  public function tab5EmpDetailsAjax(Request $request)
		  {
			  $documentCollectId = $request->documentCollectionId;
				 $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
				$documentCollectionValues = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectId)->get();
				/* echo '<pre>';
				print_r($documentCollectionValues);
				exit; */
				$docCollectionDetails = array();
				foreach($documentCollectionValues as $_docCollectionValue)
				{
					
					$attrId = $_docCollectionValue->attribute_code;
					$docAttributes = DocumentCollectionAttributes::where("id",$attrId)->first();
					if($docAttributes != '')
					{
						if($docAttributes->attribute_area== 'visaprocess' || $docAttributes->attribute_area== 'both')
						{
						$attributeName = $docAttributes->attribute_name.'^'.$docAttributes->attrbute_type_id;
						$attributeValue = $_docCollectionValue->attribute_value;
						$docCollectionDetails[$attributeName] = $attributeValue;
						}
					}
				}
				/* echo '<pre>';
				print_r($docCollectionDetails);
				exit;  */
				 return view("OnboardingAjax/RequestTabsAjax/visaDocumentAjax",compact('documentCollectionDetails','docCollectionDetails'));
		  }	

	public function tabvisaProcessPanelAjax(Request $request)
	{
		$documentCollectId = $request->documentCollectionId;
		$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
		/*
				*total Cost and percentage work
				*/
				/* echo $documentId;
				echo '<br />';
				echo $typeId;
				exit; */
				$visaProcessDetailAll =  Visaprocess::where("document_id",$documentCollectId)->get();
				
				$checkCost = array();
				$totalCost = 0;
				foreach($visaProcessDetailAll as $all)
				{
					if($all->stage_staus == 2 || $all->stage_staus == 3)
					{
					$costEach = $all->cost;
					$totalCost = $totalCost+$costEach;
					$checkCost[] = $all->cost;
					}
				}
				
				
				/*
				*total Cost and percentage work
				*/
				/*
				*percentage work
				*/
				$visaStageD = '';
				$lastStageCompleted =  Visaprocess::where("document_id",$documentCollectId)->where("stage_staus",2)->orderBy("id","DESC")->first();
				if($lastStageCompleted != '')
				{
				 $visaStageId = $lastStageCompleted->visa_stage;
				$visaStageD = VisaStage::where("id",$visaStageId)->first();
				
				$completedStageOrder = $visaStageD->stage_order;
				$typeId = $lastStageCompleted->visa_type;
				$totalStagesCount = VisaStage::where("visa_type",$typeId)->where("status",1)->count();
				$p1 = $completedStageOrder/$totalStagesCount;
				$p2 = round($p1*100);
				$percentage = $p2;
				$firstStageCompleted =  Visaprocess::where("document_id",$documentCollectId)->orderBy("id","ASC")->first();
				 $initiatedDate = $firstStageCompleted->created_at;
			   $now = date("Y-m-d");
			 
			   $datediff = strtotime($now) - strtotime($initiatedDate);

				$totalDays =   round($datediff / (60 * 60 * 24));
					if($totalDays < 0)
					{
						$totalDays = 0;
					}
				}
				else
				{
					$percentage = 0;
					$totalDays=0;
				}
				
				if($documentCollectionDetails->visa_process_status == 4)
				{
					$percentage = 100;
				}
				/*
				*percentage work
				*/
				/*
				*get All  visa stages
				*working
				*/
				$visaStageLists = array();
				$lastStageCompletedList =  Visaprocess::where("document_id",$documentCollectId)->whereIn("stage_staus",array(1,2))->orderBy("id","DESC")->get();
				
				$iStage=0;
				foreach($lastStageCompletedList as $list)
				{
					
					$visaStageInfo = VisaStage::where("id",$list->visa_stage)->first();
					if($visaStageInfo != '')
					{
					$visaStageLists[$iStage]['stageName'] = $visaStageInfo->stage_name;
					$visaStageLists[$iStage]['cost'] = $visaStageInfo->cost;
					$visaStageLists[$iStage]['stage_description'] = $visaStageInfo->stage_description;
					$visaStageLists[$iStage]['status'] = $list->stage_staus;
					$visaStageLists[$iStage]['finalcost'] = $list->cost;
					$visaStageLists[$iStage]['created_at'] = $list->created_at;
					$visaStageLists[$iStage]['closing_date'] = $list->closing_date;
					$visaStageLists[$iStage]['comment'] = $list->comment;
					$visaStageLists[$iStage]['final_comment'] = $list->final_comment;
					$visaStageLists[$iStage]['processId'] = $list->id;
					$iStage++;
					}
				}
				
				/*
				*get All  visa stages
				*working
				*/
				$visaTypeModChecked =  Visaprocess::where("document_id",$documentCollectId)->orderBy("id","DESC")->first();
				if($visaTypeModChecked != '')
				{
				$visaTypeId = $visaTypeModChecked->visa_type;
				}
				else
				{
					$visaTypeId = 0;
				}
		return view("OnboardingAjax/RequestTabsAjax/tabvisaProcessPanelAjax",compact('documentCollectionDetails','totalCost','percentage','lastStageCompleted','visaStageD','totalDays','visaStageLists','visaTypeId'));
	}		  
	   
	   public static function getPopupContents($processName,$processStatus,$documentCollectId)
	   {
		   /* echo $processName;
		   echo '<br />';
		   echo $processStatus;
		   echo '<br />';
		   echo $documentCollectId;
		   exit; */
		  $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
		  $arrayOfDetails = array();
		  switch($processName)
		  {
			  case 'Document Collection Request':
			            $currentDate = date("Y-m-d");
						$startDate = date("Y-m-d",strtotime($documentCollectionDetails->created_at));
						
						$datediff = strtotime($currentDate) - strtotime($startDate);

						$days =   round($datediff / (60 * 60 * 24));
						$arrayOfDetails['StartDate'] = date("d M Y",strtotime($documentCollectionDetails->created_at));
						$arrayOfDetails['EndDate'] = '--';
						$arrayOfDetails['days'] = $days;
						break;
			  
			  
			  case 'Offer Letter Details Completed':
					if($processStatus == 'inprogress')
					{
						$currentDate = date("Y-m-d");
						$startDate = date("Y-m-d",strtotime($documentCollectionDetails->created_at));
						
						$datediff = strtotime($currentDate) - strtotime($startDate);

						$days =   round($datediff / (60 * 60 * 24));
						$arrayOfDetails['StartDate'] = date("d M Y",strtotime($documentCollectionDetails->created_at));
						$arrayOfDetails['EndDate'] = '--';
						$arrayOfDetails['days'] = $days;
					}
					
					if($processStatus == 'active')
					{
						$currentDate = date("Y-m-d",strtotime($documentCollectionDetails->offer_letter_details_date));
						$startDate = date("Y-m-d",strtotime($documentCollectionDetails->created_at));
						
						$datediff = strtotime($currentDate) - strtotime($startDate);

						$days =   round($datediff / (60 * 60 * 24));
						$arrayOfDetails['StartDate'] = date("d M Y",strtotime($documentCollectionDetails->created_at));
						$arrayOfDetails['EndDate'] = date("d M Y",strtotime($documentCollectionDetails->offer_letter_details_date));
						$arrayOfDetails['days'] = $days;
					}
					break;
					
					
					
				case 'Offer Letter Generated':
					if($processStatus == 'inprogress')
					{
						$currentDate = date("Y-m-d");
						$startDate = date("Y-m-d",strtotime($documentCollectionDetails->offer_letter_details_date));
						
						$datediff = strtotime($currentDate) - strtotime($startDate);

						$days =   round($datediff / (60 * 60 * 24));
						$arrayOfDetails['StartDate'] = date("d M Y",strtotime($documentCollectionDetails->offer_letter_details_date));
						$arrayOfDetails['EndDate'] = '--';
						$arrayOfDetails['days'] = $days;
					}
					
					if($processStatus == 'active')
					{
						$currentDate = date("Y-m-d",strtotime($documentCollectionDetails->offer_letter_generated_date));
						$startDate = date("Y-m-d",strtotime($documentCollectionDetails->offer_letter_details_date));
						
						$datediff = strtotime($currentDate) - strtotime($startDate);

						$days =   round($datediff / (60 * 60 * 24));
						$arrayOfDetails['StartDate'] = date("d M Y",strtotime($documentCollectionDetails->offer_letter_details_date));
						$arrayOfDetails['EndDate'] = date("d M Y",strtotime($documentCollectionDetails->offer_letter_generated_date));
						$arrayOfDetails['days'] = $days;
					}
					break;
					
					
					case 'Upload Signed Offer Letter':
						if($processStatus == 'inprogress')
						{
							$currentDate = date("Y-m-d");
							$startDate = date("Y-m-d",strtotime($documentCollectionDetails->offer_letter_generated_date));
							
							$datediff = strtotime($currentDate) - strtotime($startDate);

							$days =   round($datediff / (60 * 60 * 24));
							$arrayOfDetails['StartDate'] = date("d M Y",strtotime($documentCollectionDetails->offer_letter_generated_date));
							$arrayOfDetails['EndDate'] = '--';
							$arrayOfDetails['days'] = $days;
						}
						
						if($processStatus == 'active')
						{
							$currentDate = date("Y-m-d",strtotime($documentCollectionDetails->signed_offerletter_date));
							$startDate = date("Y-m-d",strtotime($documentCollectionDetails->offer_letter_generated_date));
							
							$datediff = strtotime($currentDate) - strtotime($startDate);

							$days =   round($datediff / (60 * 60 * 24));
							$arrayOfDetails['StartDate'] = date("d M Y",strtotime($documentCollectionDetails->offer_letter_generated_date));
							$arrayOfDetails['EndDate'] = date("d M Y",strtotime($documentCollectionDetails->signed_offerletter_date));
							$arrayOfDetails['days'] = $days;
						}
					break;
					
					
					case 'Upload Visa Documents':
						if($processStatus == 'inprogress')
						{
							$currentDate = date("Y-m-d");
							$startDate = date("Y-m-d",strtotime($documentCollectionDetails->signed_offerletter_date));
							
							$datediff = strtotime($currentDate) - strtotime($startDate);

							$days =   round($datediff / (60 * 60 * 24));
							$arrayOfDetails['StartDate'] = date("d M Y",strtotime($documentCollectionDetails->signed_offerletter_date));
							$arrayOfDetails['EndDate'] = '--';
							$arrayOfDetails['days'] = $days;
						}
						
						if($processStatus == 'active')
						{
							$currentDate = date("Y-m-d",strtotime($documentCollectionDetails->upload_visa_document_date));
							$startDate = date("Y-m-d",strtotime($documentCollectionDetails->signed_offerletter_date));
							
							$datediff = strtotime($currentDate) - strtotime($startDate);

							$days =   round($datediff / (60 * 60 * 24));
							$arrayOfDetails['StartDate'] = date("d M Y",strtotime($documentCollectionDetails->signed_offerletter_date));
							$arrayOfDetails['EndDate'] = date("d M Y",strtotime($documentCollectionDetails->upload_visa_document_date));
							$arrayOfDetails['days'] = $days;
						}
					break;
					
					
					case 'Visa Process':
						if($processStatus == 'inprogress')
						{
							$currentDate = date("Y-m-d");
							$startDate = date("Y-m-d",strtotime($documentCollectionDetails->upload_visa_document_date));
							
							$datediff = strtotime($currentDate) - strtotime($startDate);

							$days =   round($datediff / (60 * 60 * 24));
							$arrayOfDetails['StartDate'] = date("d M Y",strtotime($documentCollectionDetails->upload_visa_document_date));
							$arrayOfDetails['EndDate'] = '--';
							$arrayOfDetails['days'] = $days;
						}
						
						if($processStatus == 'active')
						{
							$currentDate = date("Y-m-d",strtotime($documentCollectionDetails->upload_visa_document_date));
							$startDate = date("Y-m-d",strtotime($documentCollectionDetails->signed_offerletter_date));
							
							$datediff = strtotime($currentDate) - strtotime($startDate);

							$days =   round($datediff / (60 * 60 * 24));
							$arrayOfDetails['StartDate'] = date("d M Y",strtotime($documentCollectionDetails->signed_offerletter_date));
							$arrayOfDetails['EndDate'] = date("d M Y",strtotime($documentCollectionDetails->upload_visa_document_date));
							$arrayOfDetails['days'] = $days;
						}
					break;
					
					default:
						$currentDate = date("Y-m-d");
						$startDate = date("Y-m-d",strtotime($documentCollectionDetails->created_at));
						
						$datediff = strtotime($currentDate) - strtotime($startDate);

						$days =   round($datediff / (60 * 60 * 24));
						$arrayOfDetails['StartDate'] = date("d M Y",strtotime($documentCollectionDetails->created_at));
						$arrayOfDetails['EndDate'] = '--';
						$arrayOfDetails['days'] = $days;
						break;
					
		  }
		 
		  return $arrayOfDetails;
		  
	   }

public static function getVisaTypeName($typeID)
{
	
	$visaTypeMod = visaType::where("id",$typeID)->first();
	return $visaTypeMod->title;
}

public function tabtrainingProcessPanelAjax(Request $request)
{
		$documentCollectId = $request->documentCollectionId;
		$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
		
		/*
		*check for KYC as per department
		*/
		$departmentId = $documentCollectionDetails->department;
		$kycAttributeList = DocumentCollectionAttributes::where("department_id",$departmentId)->where("attribute_area","kyc")->where("status",1)->orderBy("sort_order","ASC")->get();
		/*
		*check for KYC as per department
		/*
				*total Cost and percentage work
				*/
				/* echo $documentId;
				echo '<br />';
				echo $typeId;
				exit; */
				/*$trainingProcessDetailAll =  TrainingProcess::where("document_id",$documentCollectId)->get();
				
				 $checkCost = array();
				$totalCost = 0;
				foreach($trainingProcessDetailAll as $all)
				{
					if($all->stage_staus == 2 || $all->stage_staus == 3)
					{
					$costEach = $all->cost;
					$totalCost = $totalCost+$costEach;
					$checkCost[] = $all->cost;
					}
				} */
				
				
				/*
				*total Cost and percentage work
				*/
				/*
				*percentage work
				*/
				$trainingStageD = '';
				$lastStageCompleted =  TrainingProcess::where("document_id",$documentCollectId)->where("stage_staus",2)->orderBy("id","DESC")->first();
				if($lastStageCompleted != '')
				{
				 $trainingStageId = $lastStageCompleted->training_stageId;
				$trainingStageD = TrainingStages::where("id",$trainingStageId)->first();
				
				$completedStageOrder = $trainingStageD->stage_order;
				$typeId = $lastStageCompleted->training_id;
				$totalStagesCount = TrainingStages::where("training_id",$typeId)->where("status",1)->count();
				$p1 = $completedStageOrder/$totalStagesCount;
				$p2 = round($p1*100);
				$percentage = $p2;
				$firstStageCompleted =  TrainingProcess::where("document_id",$documentCollectId)->orderBy("id","ASC")->first();
				 $initiatedDate = $firstStageCompleted->created_at;
			   $now = date("Y-m-d");
			 
			   $datediff = strtotime($now) - strtotime($initiatedDate);

				$totalDays =   round($datediff / (60 * 60 * 24));
					if($totalDays < 0)
					{
						$totalDays = 0;
					}
				}
				else
				{
					$percentage = 0;
					$totalDays=0;
				}
				if($documentCollectionDetails->training_process_status == 4)
				{
					$percentage = 100;
				}
				/*
				*percentage work
				*/
				/*
				*get All  visa stages
				*working
				*/
				$trainingStageLists = array();
				$lastStageCompletedList =  TrainingProcess::where("document_id",$documentCollectId)->whereIn("stage_staus",array(1,2))->orderBy("id","DESC")->get();
				
				$iStage=0;
				foreach($lastStageCompletedList as $list)
				{
					
					$trainingStageInfo = TrainingStages::where("id",$list->training_stageId)->first();
					if($trainingStageInfo != '')
					{
					$trainingStageLists[$iStage]['stageName'] = $trainingStageInfo->stage_name;
					
					$trainingStageLists[$iStage]['stage_description'] = $trainingStageInfo->stage_description;
					$trainingStageLists[$iStage]['status'] = $list->stage_staus;
					$trainingStageLists[$iStage]['processId'] = $list->id;
				
					$trainingStageLists[$iStage]['created_at'] = $list->created_at;
					$trainingStageLists[$iStage]['closing_date'] = $list->closing_date;
					$trainingStageLists[$iStage]['comment'] = $list->comment;
					$trainingStageLists[$iStage]['final_comment'] = $list->final_comment;
					$trainingStageLists[$iStage]['trainingId'] = $list->id;
					$iStage++;
					}
				}
				
				/*
				*get All  visa stages
				*working
				*/
				$trainingTypeModChecked =  TrainingProcess::where("document_id",$documentCollectId)->orderBy("id","DESC")->first();
				if($trainingTypeModChecked != '')
				{
				$trainingTypeId = $trainingTypeModChecked->training_id;
				}
				else
				{
					$trainingTypeId = 0;
				}
		return view("OnboardingAjax/RequestTabsAjax/tabtrainingProcessPanelAjax",compact('documentCollectionDetails','percentage','lastStageCompleted','trainingStageD','totalDays','trainingStageLists','trainingTypeId','kycAttributeList'));
}

public static function getTrainingName($typeid)
{
	return TrainingType::where("id",$typeid)->first()->name;
}

public static function checkForVisaStage($docId,$typeid)
{
	return Visaprocess::where("document_id",$docId)->where("visa_type",$typeid)->count();
}

public static function startDateVisaProcess($docId,$typeid)
{
	$visaP =  Visaprocess::where("document_id",$docId)->where("visa_type",$typeid)->orderBy("id","ASC")->first();
	return date("d M Y",strtotime($visaP->created_at));
}

public static function endDateVisaProcess($docId,$typeid)
{
	$visaP =  Visaprocess::where("document_id",$docId)->where("visa_type",$typeid)->orderBy("id","DESC")->first();
	return date("d M Y",strtotime($visaP->closing_date));
}

public function codeGenerationAjax(Request $request)
{
	$documentCollectionId = $request->documentCollectionId;
	$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
	return view("OnboardingAjax/RequestTabsAjax/codeGenerationAjax",compact('documentCollectionDetails'));
}

public static function checkForTStage($docId,$typeid)
{
	return TrainingProcess::where("document_id",$docId)->where("training_id",$typeid)->count();
}

public static function startDateTProcess($docId,$typeid)
{
	$visaP =  TrainingProcess::where("document_id",$docId)->where("training_id",$typeid)->orderBy("id","ASC")->first();
	return date("d M Y",strtotime($visaP->created_at));
}

public static function endDateTProcess($docId,$typeid)
{
	$visaP =  TrainingProcess::where("document_id",$docId)->where("training_id",$typeid)->orderBy("id","DESC")->first();
	return date("d M Y",strtotime($visaP->closing_date));
}
 public function finalizationOnboardingTabAjax(Request $request)
	   {
			 $documentCollectionId = $request->documentCollectionId;
			$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		     return view("OnboardingAjax/RequestTabsAjax/finalizationOnboardingTabAjax",compact('documentCollectionId','documentCollectionDetails'));
	   }
	   
	   
public static function stepsStatus($documentCollectId =NULL)
{
	
     $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
	 /*
			*Define All steps
			*start code
			*/
			$completedStep = 1;
			$OnboardingProgress = '';
			$stepsAll = array();
			/*Step1*/
		    $stepsAll[0]['name'] = 'Document Collection Request'; 
			$stepsAll[0]['stage'] = 'active'; 
			$stepsAll[0]['slagURL'] = 'step1'; 
			$stepsAll[0]['Tab'] = 'active'; 
			
			$OnboardingProgress = 'Document Collection Request';
			/*Step1*/
		    
			/*Step2*/
			$stepsAll[1]['name'] = 'Document Collection (Offerletter)'; 
		    if($documentCollectionDetails->status == 1 || $documentCollectionDetails->status == 3)
			{
				$stepsAll[1]['stage'] = 'inprogress'; 
			}
			else
			{
				$completedStep++;
				$stepsAll[1]['stage'] = 'active'; 
				$OnboardingProgress = 'Document Collection (Offerletter)';
			}
			$stepsAll[1]['slagURL'] = 'uploadDocument/'.$documentCollectionDetails->id; 
			$stepsAll[1]['Tab'] = 'active'; 
			/*Step2*/
			
			/*Step3*/
			$stepsAll[2]['name'] = 'Offer Letter Generated'; 
			if($documentCollectionDetails->status == 2)
			{
				$stepsAll[2]['stage'] = 'inprogress'; 
				$stepsAll[2]['Tab'] = 'active'; 
			}
			else if($documentCollectionDetails->status > 2 && $documentCollectionDetails->status != 3)
			{
				$completedStep++;
				$stepsAll[2]['stage'] = 'active'; 
				$OnboardingProgress = 'Offer Letter Generated';
				$stepsAll[2]['Tab'] = 'active'; 
			}
			else 
			{
			    $stepsAll[2]['stage'] = 'pending'; 
				$stepsAll[2]['Tab'] = 'disabled-tab'; 
			}
			$stepsAll[2]['slagURL'] = 'createOfferLetter/'.$documentCollectionDetails->id; 
			/*Step3*/
			
			/*Step4*/
			$stepsAll[3]['name'] = 'Upload Signed Offer Letter'; 
			if($documentCollectionDetails->status == 4)
			{
				$stepsAll[3]['stage'] = 'inprogress'; 
				$stepsAll[3]['Tab'] = 'active'; 
			}
			else if($documentCollectionDetails->status > 4)
			{
				$completedStep++;
				$stepsAll[3]['stage'] = 'active'; 
				$OnboardingProgress = 'Upload Signed Offer Letter';
				$stepsAll[3]['Tab'] = 'active'; 
			}
			else
			{
				$stepsAll[3]['stage'] = 'pending'; 
				$stepsAll[3]['Tab'] = 'disabled-tab'; 
			}
			$stepsAll[3]['slagURL'] = 'signedOfferLetter/'.$documentCollectionDetails->id;  
			/*Step4*/
			
			
			/*Step5*/
			$stepsAll[4]['name'] = 'Document Collection (Visa)'; 
		    if($documentCollectionDetails->status == 5)
			{
				$stepsAll[4]['stage'] = 'inprogress'; 
				$stepsAll[4]['Tab'] = 'active'; 
			}
			else if($documentCollectionDetails->status > 5)
			{
				$completedStep++;
				$stepsAll[4]['stage'] = 'active'; 
				$OnboardingProgress = 'Document Collection (Visa)';
				$stepsAll[4]['Tab'] = 'active'; 
			}
			else
			{
				$stepsAll[4]['stage'] = 'pending'; 
				$stepsAll[4]['Tab'] = 'disabled-tab'; 
			}
			$stepsAll[4]['slagURL'] = 'uploadVisaDocument/'.$documentCollectionDetails->id;  
			/*Step5*/
			
			$stepsAll[5]['name'] = 'Visa Process'; 
			if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->visa_process_status != 4)
			{
				 $stepsAll[5]['stage'] = 'inprogress'; 
				 $stepsAll[5]['Tab'] = 'active'; 
			}
			else if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->visa_process_status == 4)
			{
				$completedStep++;
				 $stepsAll[5]['stage'] = 'active'; 
				 $OnboardingProgress = 'Visa Process';
				  $stepsAll[5]['Tab'] = 'active'; 
			}
			else
			{
				 $stepsAll[5]['stage'] = 'pending'; 
				  $stepsAll[5]['Tab'] = 'disabled-tab'; 
			}
		   
			$stepsAll[5]['slagURL'] = 'preVisaProcess/'.$documentCollectionDetails->id; 
			
			$stepsAll[6]['name'] = 'Training and On-Boarding Process'; 
			if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->training_process_status != 4)
			{
				$stepsAll[6]['stage'] = 'inprogress'; 
				$stepsAll[6]['Tab'] = 'active';
			}
			else if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->training_process_status == 4)
			{
				$completedStep++;
				$stepsAll[6]['stage'] = 'active';
				$stepsAll[6]['Tab'] = 'active';				
			}
			else
			{
				$stepsAll[6]['stage'] = 'pending'; 
				 $stepsAll[6]['Tab'] = 'disabled-tab';
			}
			
		    
			$stepsAll[6]['slagURL'] = 'preTrainingProcess/'.$documentCollectionDetails->id;
			 
			
			$stepsAll[7]['name'] = 'Bank Code Generation Process'; 
			if($documentCollectionDetails->status == 6)
			{
				$stepsAll[7]['stage'] = 'inprogress'; 
				$stepsAll[7]['Tab'] = 'active';
			}
			else if($documentCollectionDetails->status > 6)
			{
				$completedStep++;
				$stepsAll[7]['stage'] = 'active';
				$stepsAll[7]['Tab'] = 'active';				
			}
			else
			{
				$stepsAll[7]['stage'] = 'pending'; 
				 $stepsAll[7]['Tab'] = 'disabled-tab';
			}
			$stepsAll[7]['slagURL'] = 'bankCodeGeneration/'.$documentCollectionDetails->id;
		   
			
			
			$stepsAll[8]['name'] = 'On-Boarding Completed'; 
			
			if(($documentCollectionDetails->status == 7) || ($documentCollectionDetails->department == 8 && $documentCollectionDetails->status == 6))
			{
				$stepsAll[8]['stage'] = 'inprogress'; 
				$stepsAll[8]['Tab'] = 'active';
			}
			else if($documentCollectionDetails->status > 7)
			{
				$completedStep++;
				$stepsAll[8]['stage'] = 'active';
				$stepsAll[8]['Tab'] = 'active';				
			}
			else
			{
				$stepsAll[8]['stage'] = 'pending'; 
				 $stepsAll[8]['Tab'] = 'disabled-tab';
			}
			
		    $stepsAll[8]['slagURL'] = 'welcomeOnboard/'.$documentCollectionDetails->id;
			return $stepsAll;
			
}

public function requestOnBoardingAjax(Request $request)
{
			$documentCollectionId = $request->documentCollectionId;
			
		     return view("OnboardingAjax/RequestTabsAjax/requestOnBoardingAjax",compact('documentCollectionId'));
}

public function onboardingDoing(Request $request)
{
		$documentCollectionId = $request->documentCollectionId;
		$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
	/* 	echo '<pre>';
		print_r($documentCollectionDetails);
		exit; */
		
			
			
		/*
		*Creating Employee First by Document Id
		*start Coding
		*/
		$empMod = new Employee_details();
		
		$maxempid = Employee_details::max('emp_id');
			if($maxempid=='')
			{
				$num = $emplid;
			}
			else{
				$num = $maxempid+1;
			}
			$empMod->emp_id=$num;
			$empMod->dept_id=$documentCollectionDetails->department;
			$empMod->onboarding_status=1;
			$empMod->first_name=$documentCollectionDetails->emp_name;
			
			$empMod->status=1;
			$empMod->document_collection_id=$documentCollectionId;
			if($empMod->save())
			{
				$LastInsertEmpId = $empMod->emp_id;
				$lastId = $empMod->id;
				
				/*
				*company id development
				*/
				$deptId = $documentCollectionDetails->department;
				$departmentName = Department::where("id",$deptId)->first()->department_name;
				$companyName = $departmentName.'000'.$lastId;
				$updateEmp = Employee_details::find($lastId);
				$updateEmp->company_id = $companyName;
				$updateEmp->save();
				/*
				*company id development
				*/
				
				
				
				
				/*
				*Creating Employee Attribute 
				*/
				$empAttrMod = new Employee_attribute();
				$empAttrMod->emp_id = $LastInsertEmpId;
				$empAttrMod->dept_id = $documentCollectionDetails->department;
				$empAttrMod->attribute_code = 'CONTACT_NUMBER';
				$empAttrMod->attribute_values =  $documentCollectionDetails->mobile_no;
				$empAttrMod->status =  1;
				$empAttrMod->save();
				
				$empAttrMod = new Employee_attribute();
				$empAttrMod->emp_id = $LastInsertEmpId;
				$empAttrMod->dept_id = $documentCollectionDetails->department;
				$empAttrMod->attribute_code = 'email';
				$empAttrMod->attribute_values =  $documentCollectionDetails->email;
				$empAttrMod->status =  1;
				$empAttrMod->save();
				
				
				$empAttrMod = new Employee_attribute();
				$empAttrMod->emp_id = $LastInsertEmpId;
				$empAttrMod->dept_id = $documentCollectionDetails->department;
				$empAttrMod->attribute_code = 'hiring_source';
				$hiringId = $documentCollectionDetails->hiring_source;
				$empAttrMod->attribute_values = HiringSourceDetails::where("id",$hiringId)->first()->name;
				$empAttrMod->status =  1;
				$empAttrMod->save();
				
				$empAttrMod = new Employee_attribute();
				$empAttrMod->emp_id = $LastInsertEmpId;
				$empAttrMod->dept_id = $documentCollectionDetails->department;
				$empAttrMod->attribute_code = 'recruiter_name';
				$recruiterNameId = $documentCollectionDetails->recruiter_name;
				$empAttrMod->attribute_values = RecruiterDetails::where("id",$recruiterNameId)->first()->name;
				$empAttrMod->status =  1;
				$empAttrMod->save();
				
				
				$empAttrMod = new Employee_attribute();
				$empAttrMod->emp_id = $LastInsertEmpId;
				$empAttrMod->dept_id = $documentCollectionDetails->department;
				$empAttrMod->attribute_code = 'designation_onboarding';
				$designationID = $documentCollectionDetails->designation;
				$empAttrMod->attribute_values = Designation::where("id",$designationID)->first()->name;
				$empAttrMod->status =  1;
				$empAttrMod->save();
				
				
				$empAttrMod = new Employee_attribute();
				$empAttrMod->emp_id = $LastInsertEmpId;
				$empAttrMod->dept_id = $documentCollectionDetails->department;
				$empAttrMod->attribute_code = 'caption';
				$empAttrMod->attribute_values = $documentCollectionDetails->caption;
				$empAttrMod->status =  1;
				$empAttrMod->save();
				
				$empAttrMod = new Employee_attribute();
				$empAttrMod->emp_id = $LastInsertEmpId;
				$empAttrMod->dept_id = $documentCollectionDetails->department;
				$empAttrMod->attribute_code = 'work_location';
				if($documentCollectionDetails->location == 'DXB')
				{
					$location = 'Dubai';
				}
				else
				{
					$location = 'Abu Dabhi';
				}
				$empAttrMod->attribute_values = $location;
				$empAttrMod->status =  1;
				$empAttrMod->save();
				
				
				$empAttrMod = new Employee_attribute();
				$empAttrMod->emp_id = $LastInsertEmpId;
				$empAttrMod->dept_id = $documentCollectionDetails->department;
				$empAttrMod->attribute_code = 'bank_generated_code';
				$empAttrMod->attribute_values = $documentCollectionDetails->bank_generated_code;
				$empAttrMod->status =  1;
				$empAttrMod->save();
				/*
				*Creating Employee Attribute
				*/
				/*
				*Attribute values Of Document Collection
				*/
				
				$docValues = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectionId)->get();
				/* echo '<pre>';
				print_r($docValues);
				exit; */
				
				foreach($docValues as $_docV)
				{
					$attrId = $_docV->attribute_code;
					$docAttribute = DocumentCollectionAttributes::where("id",$attrId)->first();
					
					$empAttrMod = new Employee_attribute();
					$empAttrMod->emp_id = $LastInsertEmpId;
					$empAttrMod->dept_id = $documentCollectionDetails->department;
					$empAttrMod->attribute_code = $docAttribute->attribute_code;
					$empAttrMod->attribute_values =  $_docV->attribute_value;
					$empAttrMod->status =  1;
					$empAttrMod->save();
				}
				/*
				*Attribute values Of Document Collection
				*/
				/*
				*Update Status on Document collection model
				*Start Coding
				*/
				$documentCollectionDetails = DocumentCollectionDetails::find($documentCollectionId);
				$documentCollectionDetails->status = 8;
				$documentCollectionDetails->serialized_id = 'On-Boarded-000'.$documentCollectionId;
				$documentCollectionDetails->save();
				$request->session()->flash('message','Request completed. Employee Generated.');
				/*
				*Update Status on Document collection model
				*End Coding
				*/
				echo "DONE";
				exit;
		}
		else
		{
			echo "Not";
			exit;
		}
		echo "not";
		exit;
		/*
		*Creating Employee First by Document Id
		*end Coding
		*/
			
	
}

public function manageEmpAttrFromDocumentAttr(Request $request)
	{
		$documentAttrLists = DocumentCollectionAttributes::where("status",1)->whereIn("attribute_area",array("offerletter","both","visaprocess"))->get();
		
		/*
		* Getting not matched document attribute in to employee Attribute
		* Start Coding
		*/
		$notMatchedAttribute = array();
		$needtoCreateAttribute = array();
		$index = 0;
		$index1 = 0;
		foreach($documentAttrLists as $_list)
		{
			$attrCodeFromDocument = $_list->attribute_code;
			$attributeModel = Attributes::where("attribute_code",$attrCodeFromDocument)->first();
			if($attributeModel == '')
			{
				$notMatchedAttribute[$index]['Attribute Code'] = $attrCodeFromDocument;
				$notMatchedAttribute[$index]['Status'] = 'Not Exist';
				$needtoCreateAttribute[$index1] = $_list;
				$index1++;
				
			}
			else
			{
				$notMatchedAttribute[$index]['Attribute Code'] = $attrCodeFromDocument;
				$notMatchedAttribute[$index]['Status'] = 'Exist';
			}
			$index++;
		}
		/*
		* Getting not matched document attribute in to employee Attribute
		* End Coding
		*/
		/*
		*creating needed Attribute
		*/
		foreach($needtoCreateAttribute as $need)
		{
			$attrEmpMod = new Attributes();
			$attrEmpMod->attribute_name =  $need->attribute_name;
			$attrEmpMod->attribute_code =  $need->attribute_code;
			$attrEmpMod->attrbute_type_id =  $need->attrbute_type_id;
			$attrEmpMod->opt_option =  $need->opt;
			$attrEmpMod->conditional_attribute =  2;
			$attrEmpMod->parent_attribute =  0;
			$attrEmpMod->attribute_requirement =  2;
			$attrEmpMod->department_id =  'All';
			$attrEmpMod->attribute_set =  'Employee';
			$attrEmpMod->onboarding_status =  1;
			$attrEmpMod->status =  1;
			if($need->attribute_area == 'visaprocess')
			{
				$attrEmpMod->tab_name =  'v_d';
			}
			else
			{
				$attrEmpMod->tab_name =  'c_d';
			}
			$sort_order = Attributes::orderBy("attribute_id","DESC")->first()->sort_order;
			$sort_order_new = $sort_order+1;
			$attrEmpMod->sort_order =  $sort_order_new;
			$attrEmpMod->save();
		}
		/*
		*creating needed Attribute
		*/
		
		echo "Done";
		exit;
	}   
	
	public static function getEmployeePaddingId($docID)
	{
		return Employee_details::where("document_collection_id",$docID)->first()->emp_id;
	}
}