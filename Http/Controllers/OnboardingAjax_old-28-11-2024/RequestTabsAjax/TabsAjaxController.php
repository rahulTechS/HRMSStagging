<?php

namespace App\Http\Controllers\OnboardingAjax\RequestTabsAjax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use App\Models\Entry\Employee;
use App\Models\Company\Department;
use App\Models\Company\Product;
use App\Models\Recruiter\Designation;
use App\Models\Offerletter\SalaryBreakup;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionAttributes;
use  App\Models\Attribute\Attributes;
use App\Models\Onboarding\VisaDetails;
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
use App\Models\Visa\VisaProcessHistroy;
use UserPermissionAuth;
use App\Models\VisaManagement\VisaManagementProcess;
use App\Models\Onboarding\DocumentCollectionBackout;
use App\Models\Logs\DocumentCollectionDetailsLog;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Onboarding\SpecialCommentLog;
use App\Models\Onboarding\OnboardCandidateKyc;

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
			else if($documentCollectionDetails->status > 2 && $documentCollectionDetails->status != 3 )
			{
				$completedStep++;
				$stepsAll[2]['stage'] = 'active'; 
				$OnboardingProgress = 'Offer Letter Generated';
				$stepsAll[2]['Tab'] = 'active'; 
			}
			else 
			{
			  
				$stepsAll[2]['stage'] = 'inprogress'; 
				$stepsAll[2]['Tab'] = 'active'; 
			}
			$stepsAll[2]['slagURL'] = 'tab4'; 
			$stepsAll[2]['onclick'] = 'tab4Panel();'; 
			/*Step3*/
			
			/*Step4*/
			$stepsAll[3]['name'] = 'Upload Signed Offer Letter'; 
			if($documentCollectionDetails->status == 4 || $documentCollectionDetails->city_status==2)
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
		    if($documentCollectionDetails->status == 5 || $documentCollectionDetails->city_status==2)
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
			$stepsAll[5]['name'] = 'BG verification'; 
		    if($documentCollectionDetails->status == 5)
			{
				$stepsAll[5]['stage'] = 'inprogress'; 
				$stepsAll[5]['Tab'] = 'active'; 
			}
			else if($documentCollectionDetails->status > 5)
			{
				$completedStep++;
				$stepsAll[5]['stage'] = 'active'; 
				$OnboardingProgress = 'BG verification';
				$stepsAll[5]['Tab'] = 'active'; 
			}
			else
			{
				$stepsAll[5]['stage'] = 'pending'; 
				$stepsAll[5]['Tab'] = 'disabled-tab'; 
			}
			$stepsAll[5]['slagURL'] = 'tab9';  
			$stepsAll[5]['onclick'] = 'tab9Panel();'; 
			
			$stepsAll[6]['name'] = 'Visa Process';
			
			if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->visa_process_status != 4 || $documentCollectionDetails->city_status==2)
			{
				 $stepsAll[6]['stage'] = 'inprogress'; 
				 $stepsAll[6]['Tab'] = 'active'; 
			}
			else if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->visa_process_status == 4)
			{
				$completedStep++;
				 $stepsAll[6]['stage'] = 'active'; 
				 $OnboardingProgress = 'Visa Process';
				  $stepsAll[6]['Tab'] = 'active'; 
			}
			else
			{
				 $stepsAll[6]['stage'] = 'pending'; 
				  $stepsAll[6]['Tab'] = 'disabled-tab'; 
			}
		   
			$stepsAll[6]['slagURL'] = 'visaProcess'; 
			$stepsAll[6]['onclick'] = 'tabvisaProcessPanel();'; 		
			
			//tab 8 start
			$stepsAll[7]['name'] = 'Training'; 
			if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->training_process_status != 4 || $documentCollectionDetails->city_status==2)
			{
				$stepsAll[7]['stage'] = 'inprogress'; 
				$stepsAll[7]['Tab'] = 'active';
			}
			else if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->training_process_status == 4)
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
			
		    
			$stepsAll[7]['slagURL'] = 'tab6';
			 $stepsAll[7]['onclick'] = 'tabtrainingProcessPanel();';
			 $stepsAll[8]['name'] = 'On-Boarding'; 
		    if($documentCollectionDetails->bgverification_status == 1 && $documentCollectionDetails->onboard_status == 1 || $documentCollectionDetails->approval_for_skip_status==1 || $documentCollectionDetails->city_status==2)
			{
				$stepsAll[8]['stage'] = 'inprogress'; 
				$stepsAll[8]['Tab'] = 'active'; 
			}
			else if($documentCollectionDetails->bgverification_status == 1 && $documentCollectionDetails->onboard_status == 2 || $documentCollectionDetails->approval_for_skip_status==1)
			{
				$completedStep++;
				$stepsAll[8]['stage'] = 'active'; 
				$OnboardingProgress = 'On-Boarding';
				$stepsAll[8]['Tab'] = 'active'; 
			}
			else
			{
				$stepsAll[8]['stage'] = 'pending'; 
				$stepsAll[8]['Tab'] = 'disabled-tab'; 
			}
			$stepsAll[8]['slagURL'] = 'onboard';  
			$stepsAll[8]['onclick'] = 'onboardPanel();'; 
			
			$stepsAll[9]['name'] = 'Bank Code Generation Process'; 
			if($documentCollectionDetails->status == 6 || $documentCollectionDetails->city_status==2)
			{
				$stepsAll[9]['stage'] = 'inprogress'; 
				$stepsAll[9]['Tab'] = 'active';
			}
			else if($documentCollectionDetails->status > 6)
			{
				$completedStep++;
				$stepsAll[9]['stage'] = 'active';
				$stepsAll[9]['Tab'] = 'active';				
			}
			else
			{
				$stepsAll[9]['stage'] = 'pending'; 
				 $stepsAll[9]['Tab'] = 'disabled-tab';
			}
			$stepsAll[9]['slagURL'] = 'tab7';
		    $stepsAll[9]['onclick'] = 'codeGeneration();'; 	
			
			
			$stepsAll[10]['name'] = 'On-Boarding Completed'; 
			
			if(($documentCollectionDetails->status >= 7 || $documentCollectionDetails->onboard_status == 1) || ($documentCollectionDetails->department == 8 && $documentCollectionDetails->status == 6) || $documentCollectionDetails->city_status==2)
			{
				$stepsAll[10]['stage'] = 'inprogress'; 
				$stepsAll[10]['Tab'] = 'active';
			}
			else if($documentCollectionDetails->onboard_status == 2)
			{
				$completedStep++;
				$stepsAll[10]['stage'] = 'active';
				$stepsAll[10]['Tab'] = 'active';				
			}
			else
			{
				$stepsAll[10]['stage'] = 'pending'; 
				 $stepsAll[10]['Tab'] = 'disabled-tab';
			}
			
		    $stepsAll[10]['slagURL'] = 'tab8';
			 $stepsAll[10]['onclick'] = 'finalization();'; 	
			$totalStep = 10;
			$p = $completedStep/$totalStep;
			$percentange = round($p*100);
			/*
			*Define All steps
			*end code
			*/
		/* 	echo '<pre>';
			print_r($stepsAll);
			exit; */
			$iS =0;
			foreach($stepsAll as $_stepDefine)
			{
				if($documentCollectionDetails->bgverification_status == 1  || $documentCollectionDetails->approval_for_skip_status==1 || $documentCollectionDetails->city_status==2)
				{
					 if($_stepDefine['stage'] == 'pending')
					 {
						 $stepsAll[$iS]['stage'] = 'inprogress';
						 $stepsAll[$iS]['Tab'] = 'active';
					 }
					
				}
				else
				{
					if($iS >2)
					{
						 $stepsAll[$iS]['stage'] = 'pending';
						 $stepsAll[$iS]['Tab'] = 'disabled-tab';
					}
				}
				$iS++;
			}
			/* echo '<pre>';
			print_r($stepsAll);
			exit; */
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
	   
	   
	    public function uploadVisaDocumentAjaxCom(Request $request)
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
		   return view("OnboardingAjax/RequestTabsAjax/uploadVisaDocumentAjaxCom",compact('documentDetails','documentAttributes','uploadDetails'));
	   }
	   public function getVisaDocumentTouristVisa($documentCollectionID=NULL)
	   {
		   $uploadDetails = array();
		   $id = $documentCollectionID; 
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   $array=array();
		   $array[]="touristvisa";
		   $array[]="alltypevisa";
		   $attribute=array();
		   $attribute[]="both";
		   $attribute[]="visaprocess";
		   $documentAttributes = DocumentCollectionAttributes::where("attribute_area","both")->orWhere("attribute_area","visaprocess")->whereIn("attribute_category",$array)->where("status",1)->get();

		   $medicalAttributes = DocumentCollectionAttributes::where("attribute_area","medical")->where("attribute_category","alltypevisa")->where("status",1)->get();
		  
		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
		   }
			
			/* echo '<pre>';
			print_r($uploadDetails);
			exit; */
		   return view("OnboardingAjax/RequestTabsAjax/uploadVisaDocumentByOnchange",compact('documentDetails','documentAttributes','uploadDetails','medicalAttributes'));
	   }
	   public function getVisaDocumentResidenceVisa($documentCollectionID=NULL)
	   {
		   $uploadDetails = array();
		   $id = $documentCollectionID; 
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   $array=array();
		   $array[]="residencevisa";
		   $array[]="alltypevisa";
		   $attribute=array();
		   $attribute[]="both";
		   $attribute[]="visaprocess";
		   
		   $documentAttributes = DocumentCollectionAttributes::where("attribute_area","both")->orWhere("attribute_area","visaprocess")->whereIn("attribute_category",$array)->where("status",1)->get();
           $medicalAttributes = DocumentCollectionAttributes::where("attribute_area","medical")->where("attribute_category","alltypevisa")->where("status",1)->get();

		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
		   }
			
			/* echo '<pre>';
			print_r($uploadDetails);
			exit; */
		   return view("OnboardingAjax/RequestTabsAjax/uploadVisaDocumentByOnchange",compact('documentDetails','documentAttributes','uploadDetails' ,'medicalAttributes'));
	   }
	   
	   public function getVisaDocumentResidenceVisaCom($documentCollectionID=NULL)
	   {
		   $uploadDetails = array();
		   $id = $documentCollectionID; 
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   $array=array();
		   $array[]="residencevisa";
		   $array[]="alltypevisa";
		   $attribute=array();
		   $attribute[]="both";
		   $attribute[]="visaprocess";
		   
		   $documentAttributes = DocumentCollectionAttributes::where("attribute_area","both")->orWhere("attribute_area","visaprocess")->whereIn("attribute_category",$array)->where("status",1)->get();
		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
		   }
			
			/* echo '<pre>';
			print_r($uploadDetails);
			exit; */
		   return view("OnboardingAjax/RequestTabsAjax/uploadVisaDocumentByOnchangeCom",compact('documentDetails','documentAttributes','uploadDetails'));
	   }
	   public function getVisaDocumentIndividualSponsor($documentCollectionID=NULL)
	   {
		   $uploadDetails = array();
		   $id = $documentCollectionID; 
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   $array=array();
		   $array[]="individualsponsor";
		   $array[]="alltypevisa";
		   $attribute=array();
		   $attribute[]="both";
		   $attribute[]="visaprocess";
		   
		   
		   
		   $documentAttributes = DocumentCollectionAttributes::where("attribute_area","both")->orWhere("attribute_area","visaprocess")->whereIn("attribute_category",$array)->where("status",1)->get();
		   $medicalAttributes = DocumentCollectionAttributes::where("attribute_area","medical")->where("attribute_category","alltypevisa")->where("status",1)->get();

		   
		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
		   }
			
			/* echo '<pre>';
			print_r($uploadDetails);
			exit; */
		   return view("OnboardingAjax/RequestTabsAjax/uploadVisaDocumentByOnchange",compact('documentDetails','documentAttributes','uploadDetails','medicalAttributes'));
	   }
	   public function getVisaDocumentCompanySponsor($documentCollectionID=NULL)
	   {
		   $uploadDetails = array();
		   $id = $documentCollectionID; 
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   $array=array();
		   $array[]="companysponsor";
		   $array[]="alltypevisa";
		   $attribute=array();
		   $attribute[]="both";
		   $attribute[]="visaprocess";
		   
		   $documentAttributes = DocumentCollectionAttributes::where("attribute_area","both")->orWhere("attribute_area","visaprocess")->whereIn("attribute_category",$array)->where("status",1)->get();
		   $medicalAttributes = DocumentCollectionAttributes::where("attribute_area","medical")->where("attribute_category","alltypevisa")->where("status",1)->get();

		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
		   }
			
			/* echo '<pre>';
			print_r($uploadDetails);
			exit; */
		   return view("OnboardingAjax/RequestTabsAjax/uploadVisaDocumentByOnchange",compact('documentDetails','documentAttributes','uploadDetails','medicalAttributes'));
	   }
	   public function getVisaDocumentOutsideCountry($documentCollectionID=NULL)
	   {
		   $uploadDetails = array();
		   $id = $documentCollectionID; 
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   $array=array();
		   $array[]="alltypevisa";
		   $attribute=array();
		   $attribute[]="both";
		   $attribute[]="visaprocess";
		   
		   $documentAttributes = DocumentCollectionAttributes::where("attribute_area","both")->orWhere("attribute_area","visaprocess")->whereIn("attribute_category",$array)->where("status",1)->get();
		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
		   }
			
			/* echo '<pre>';
			print_r($uploadDetails);
			exit; */
			$medicalAttributes = DocumentCollectionAttributes::where("attribute_area","medical")->where("attribute_category","alltypevisa")->where("status",1)->get();

			$outside="Outside Country";
		   return view("OnboardingAjax/RequestTabsAjax/uploadVisaDocumentByOnchange",compact('medicalAttributes','outside','documentDetails','documentAttributes','uploadDetails'));
	   }
	   
	   
	   public function uploadVisaDocumentStartAjax(Request $request)
	   {
		    $selectedFilter = $request->input();
		  /*
			*update visa expiry date
			*/
			/* update after change inside to out side*/
			$changecountry=DocumentCollectionDetails::where("id",$selectedFilter['documentCollectionID'])->first();
			if($changecountry!=''){
				$current_visa_status=$selectedFilter['current_visa_status'];
				if($changecountry->current_visa_status!=$current_visa_status){
					if($current_visa_status=="Outside Country"){
						//echo $current_visa_status;
						$getdata=DocumentCollectionDetailsValues::where("document_collection_id",$selectedFilter['documentCollectionID'])->where("attribute_code",66)->first();
						if($getdata!=''){
							$deleteobj=DocumentCollectionDetailsValues::find($getdata->id);
							$deleteobj->delete();
						}
						$documentMod = DocumentCollectionDetails::find($selectedFilter['documentCollectionID']);
						$documentMod->visa_expiry_date = NULL;
						$documentMod->current_visa_details = NULL;
						$documentMod->sort_dateBY = NULL;
						$documentMod->sort_date = NULL;
						$documentMod->stamping_deadline =NULL;
						$documentMod->save();
						
						
					}
					else if($current_visa_status=="Inside Country"){
						$documentMod = DocumentCollectionDetails::find($selectedFilter['documentCollectionID']);
						$documentMod->sort_dateBY = NULL;
						$documentMod->sort_date = NULL;
						$documentMod->entry_date =NULL;
						$documentMod->save();
					}
				}
				
			}
			
			/* end */
			if(isset($selectedFilter[66]) && $selectedFilter[66] != 'undefined')
			{
				
				$docId = $selectedFilter['documentCollectionID'];
				$visaExpiryDate = date("Y-m-d",strtotime($selectedFilter[66]));
				$docMod = DocumentCollectionDetails::find($docId);
				$docMod->visa_expiry_date = $visaExpiryDate;
				$documentCollect = DocumentCollectionDetails::where("id",$docId)->first();
				if($documentCollect->current_visa_status=="Inside Country" && $documentCollect->sort_dateBY=="Change Status" || $documentCollect->sort_dateBY==NULL){
					$docMod->sort_date =$visaExpiryDate;
					$docMod->sort_dateBY ="Change Status";	
				}
				if($docMod->save()){
					$finaljsondata = json_encode(array('visa_expiry_date' =>$visaExpiryDate), JSON_PRETTY_PRINT);
					$logObj = new DocumentCollectionDetailsLog();
					$logObj->document_id =$docId;
					$logObj->created_by=$request->session()->get('EmployeeId');
					$logObj->title ="update visa expiry date Document Collection Tab";
					$logObj->response =$finaljsondata;
					$logObj->category ="Offer Letter";
					$logObj->save();
				}
			}
			/*
			*update visa expiry date
			*/
		   $saveData = array();
		  
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		   $status = $selectedFilter['status'];
		   $current_visa_status = $selectedFilter['current_visa_status'];
		   $current_visa_details = $selectedFilter['current_visa_details'];
		   
		   
		   
		   
		   $num = $documentCollectionId;
		    unset($selectedFilter['_token']);
		    unset($selectedFilter['status']);
			unset($selectedFilter['current_visa_status']);
			unset($selectedFilter['current_visa_details']);
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
				if($objDocument->save()){
					$finaljsondata = json_encode(array($key =>$value), JSON_PRETTY_PRINT);
					$logObj = new DocumentCollectionDetailsLog();
					$logObj->document_id =$documentCollectionId;
					$logObj->created_by=$request->session()->get('EmployeeId');
					$logObj->title ="update Visa Document Collection tab";
					$logObj->response =$finaljsondata;
					$logObj->category ="Offer Letter";
					$logObj->save();
				}
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
					if($objDocument->save()){
						$finaljsondata = json_encode(array($key =>$filesAttributeInfo[$key]), JSON_PRETTY_PRINT);
						$logObj = new DocumentCollectionDetailsLog();
						$logObj->document_id =$documentCollectionId;
						$logObj->created_by=$request->session()->get('EmployeeId');
						$logObj->title ="update Visa Document Collection tab";
						$logObj->response =$finaljsondata;
						$logObj->category ="Offer Letter";
						$logObj->save();
					}
					
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
				//$documentCollectionMod->upload_visa_document_date = date("Y-m-d");
			}
			else
			{
				$documentCollectionMod->serialized_id = 'VisaDocCollection-Inprogress-000'.$documentCollectionId;
			}
			}
			$documentCollectionMod->current_visa_status = $current_visa_status;
			$documentCollectionMod->current_visa_details = $current_visa_details;
			$visadetails=$current_visa_details;
			$id=$documentCollectionId;
			
			
			if($visadetails!=''&& $visadetails=="Inside Country"){
			if($visadetails=="Tourist Visa"){
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$documentValuesExistingVisa = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",71)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL) && ($documentValuesExistingVisa!='' && $documentValuesExistingVisa!=NULL)){
				$documentCollectionMod->visa_documents_status = 2;
				$documentCollectionMod->upload_visa_document_date = date("Y-m-d");
			}
			else{
				//return "Document Not Received";
			}
		}
		else if($visadetails=="Residence Visa"){
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL)){
				$documentCollectionMod->visa_documents_status = 2;
				$documentCollectionMod->upload_visa_document_date = date("Y-m-d");
			}
			else{
				//return "Document Not Received";
			}
		}
		else if($visadetails=="Individual Sponsor"){
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$SponsorDocPassport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",72)->first();
			$SponsorDocVisa = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",73)->first();
			$SponsorDocEmirates = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",74)->first();
			$SponsorNOC = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",75)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL) && ($SponsorDocPassport!='' && $SponsorDocPassport!=NULL) && ($SponsorDocVisa!='' && $SponsorDocVisa!=NULL) && ($SponsorDocEmirates!='' && $SponsorDocEmirates!=NULL) && ($SponsorNOC!='' && $SponsorNOC!=NULL)){
				$documentCollectionMod->visa_documents_status = 2;
				$documentCollectionMod->upload_visa_document_date = date("Y-m-d");
			}
			else{
				//return "Document Not Received";
			}
		}
		else if($visadetails=="Residence Visa"){
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			$CompanyNOC = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",76)->first();
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL) && ($CompanyNOC!='' && $CompanyNOC!=NULL)){
				$documentCollectionMod->visa_documents_status = 2;
				$documentCollectionMod->upload_visa_document_date = date("Y-m-d");
			}
			else{
				//return "Document Not Received";
			}
		}
		}
		else{
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();			
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL)){
				$documentCollectionMod->visa_documents_status = 2;
				$documentCollectionMod->upload_visa_document_date = date("Y-m-d");
			}
		}
			
			
		   
			if($documentCollectionMod->save()){
				$finaljsondata = json_encode(array('current_visa_status' =>$current_visa_status,'current_visa_details'=>$current_visa_details,"status"=>$status), JSON_PRETTY_PRINT);
						$logObj = new DocumentCollectionDetailsLog();
						$logObj->document_id =$documentCollectionId;
						$logObj->created_by=$request->session()->get('EmployeeId');
						$logObj->title ="update Visa Document Collection tab";
						$logObj->response =$finaljsondata;
						$logObj->category ="Offer Letter";
						$logObj->save();
			}
			
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
				$cat1 = array();
				$cat2 = array();
				$cat3 = array();
				$cat4 = array();
				foreach($documentCollectionValues as $_docCollectionValue)
				{
					
					$attrId = $_docCollectionValue->attribute_code;
					$docAttributes = DocumentCollectionAttributes::where("id",$attrId)->first();
					if($docAttributes != '')
					{
						if($docAttributes->attribute_area== 'offerletter' || $docAttributes->attribute_area== 'both' )
						{
							$attributeName = $docAttributes->attribute_name.'^'.$docAttributes->attrbute_type_id;
							$attributeValue = $_docCollectionValue->attribute_value;
							$attributeid = $_docCollectionValue->attribute_code;
							if($attributeValue != 'undefined')
							{
							if($attributeid==14){
							$cat1[$attributeName] = $attributeValue;
							}
						else if($attributeid==15 || $attributeid==17){
							$cat2[$attributeName] = $attributeValue;
						}
						else
							$docCollectionDetails[$attributeName] = $attributeValue;
							}
						}
						else if($docAttributes->attribute_area== 'bdminterview')
						{
							$attributeName = $docAttributes->attribute_name.'^'.$docAttributes->attrbute_type_id;
							$attributeValue = $_docCollectionValue->attribute_value;
							$attributeid = $_docCollectionValue->attribute_code;
							if($attributeValue != 'undefined')
							{
							
							$cat3[$attributeName] = $attributeValue;
							}
						}
						else if($docAttributes->attribute_area== 'bgverification')
						{
							$attributeName = $docAttributes->attribute_name.'^'.$docAttributes->attrbute_type_id;
							$attributeValue = $_docCollectionValue->attribute_value;
							$attributeid = $_docCollectionValue->attribute_code;
							if($attributeValue != 'undefined')
							{
							
							$cat4[$attributeName] = $attributeValue;
							}
						}
						else
						{
							
						}
						
						
				}
				}
				/* echo '<pre>';
				print_r($cat1);
				print_r($cat2);
				exit; */ 
				 return view("OnboardingAjax/RequestTabsAjax/offerLetterDocumentAjax",compact('cat3','cat2','cat1','cat4','documentCollectionDetails','docCollectionDetails')); 
		   }
		   public function tab9EmpDetailsAjax(Request $request)
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
						if($docAttributes->attribute_area== 'bgverification')
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
				 return view("OnboardingAjax/RequestTabsAjax/bgverificationDocumentAjax",compact('documentCollectionDetails','docCollectionDetails')); 
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
				
				$docCollectionDetailsMedical = array();
				foreach($documentCollectionValues as $_docCollectionValueMedical)
				{
					
					$attrIdmedical = $_docCollectionValueMedical->attribute_code;
					$docAttributesmedical = DocumentCollectionAttributes::where("id",$attrIdmedical)->first();
					if($docAttributesmedical != '')
					{
						if($docAttributesmedical->attribute_area== 'medical')
						{
						$attributeName = $docAttributesmedical->attribute_name.'^'.$docAttributesmedical->attrbute_type_id;
						$attributeValue = $_docCollectionValueMedical->attribute_value;
						$docCollectionDetailsMedical[$attributeName] = $attributeValue;
						}
					}
				}
				
				
				
				/* echo '<pre>';
				print_r($docCollectionDetails);
				exit;  */
				 return view("OnboardingAjax/RequestTabsAjax/visaDocumentAjax",compact('documentCollectionDetails','docCollectionDetails' ,'docCollectionDetailsMedical'));
		  }	
		  
		  
		    public function tab5EmpDetailsAjaxComponent(Request $request)
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
					 return view("OnboardingAjax/RequestTabsAjax/visaDocumentAjaxComponent",compact('documentCollectionDetails','docCollectionDetails'));
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
				$totalfine = 0;
				$totalCostwithfine = 0;
				foreach($visaProcessDetailAll as $all)
				{
					if($all->stage_staus == 2 || $all->stage_staus == 3)
					{
					$costEach = $all->cost;
					$fineEach = $all->cost_fine;
					$totalCost = $totalCost+$costEach;
					$totalfine = $totalfine+$fineEach;
					//$totalCostwithfine = $totalfine+$fineEach;
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
					$visaStageLists[$iStage]['fine'] = $list->cost_fine;
					$iStage++;
					}
				}
				$visaStageListspr = array();
				
				$lastStageCompletedListpr =  VisaProcessHistroy::where("document_id",$documentCollectId)->orderBy("id","DESC")->get();
				//print_r($lastStageCompletedListpr);
				if($lastStageCompletedListpr!=''){
				$iStagepr=0;
				foreach($lastStageCompletedListpr as $listpr)
				{
					
					$visaStageInfopr = VisaStage::where("id",$listpr->visa_stage)->first();
					if($visaStageInfopr != '')
					{
					$visaStageListspr[$iStagepr]['stageName'] = $visaStageInfopr->stage_name;
					$visaStageListspr[$iStagepr]['cost'] = $visaStageInfopr->cost;
					$visaStageListspr[$iStagepr]['stage_description'] = $visaStageInfopr->stage_description;
					$visaStageListspr[$iStagepr]['status'] = $listpr->stage_staus;
					$visaStageListspr[$iStagepr]['finalcost'] = $listpr->cost;
					$visaStageListspr[$iStagepr]['created_at'] = $listpr->created_at;
					$visaStageListspr[$iStagepr]['closing_date'] = $listpr->closing_date;
					$visaStageListspr[$iStagepr]['comment'] = $listpr->comment;
					$visaStageListspr[$iStagepr]['final_comment'] = $listpr->final_comment;
					$visaStageListspr[$iStagepr]['processId'] = $listpr->id;
					$visaStageListspr[$iStagepr]['fine'] = $listpr->cost_fine;
					$iStagepr++;
					}
				}
				}
				else{
					$visaStageListspr=0;
				}
				/*
				*get All  visa stages
				*working
				*/
				$visaTypeModChecked =  Visaprocess::where("document_id",$documentCollectId)->orderBy("id","DESC")->first();
				if($visaTypeModChecked != '')
				{
				$visaTypeId = $visaTypeModChecked->visa_type;
				$visaType = visaType::where("id",$visaTypeModChecked->visa_type)->first();
				if($visaType!=''){
				$type= $visaType->title;
				}
				}
				else
				{
					$visaTypeId = 0;
					$type='';
				}
				
				$visaTypeModCheckedpr =  VisaProcessHistroy::where("document_id",$documentCollectId)->orderBy("id","DESC")->first();
				if($visaTypeModCheckedpr != '')
				{
				$visaTypeIdpr = $visaTypeModCheckedpr->visa_type;
				$visaTypepr = visaType::where("id",$visaTypeModCheckedpr->visa_type)->first();
				if($visaTypepr!=''){
				$typepr= $visaTypepr->title;
				}
				}
				else
				{
					$typepr='';
				}
				
				
		return view("OnboardingAjax/RequestTabsAjax/tabvisaProcessPanelAjax",compact('typepr','visaStageListspr','totalfine','type','documentCollectionDetails','totalCost','percentage','lastStageCompleted','visaStageD','totalDays','visaStageLists','visaTypeId'));
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
			else if($documentCollectionDetails->status > 2 && $documentCollectionDetails->status != 3 )
			{
				$completedStep++;
				$stepsAll[2]['stage'] = 'active'; 
				$OnboardingProgress = 'Offer Letter Generated';
				$stepsAll[2]['Tab'] = 'active'; 
			}
			else 
			{
			    /* $stepsAll[2]['stage'] = 'pending'; 
				$stepsAll[2]['Tab'] = 'disabled-tab';  */
				$stepsAll[2]['stage'] = 'inprogress'; 
				$stepsAll[2]['Tab'] = 'active'; 
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
			$stepsAll[5]['name'] = 'BG verification'; 
		    if($documentCollectionDetails->status == 5)
			{
				$stepsAll[5]['stage'] = 'inprogress'; 
				$stepsAll[5]['Tab'] = 'active'; 
			}
			else if($documentCollectionDetails->status > 5)
			{
				$completedStep++;
				$stepsAll[5]['stage'] = 'active'; 
				$OnboardingProgress = 'BG verification';
				$stepsAll[5]['Tab'] = 'active'; 
			}
			else
			{
				$stepsAll[5]['stage'] = 'pending'; 
				$stepsAll[5]['Tab'] = 'disabled-tab'; 
			}
			$stepsAll[5]['slagURL'] = 'tab9';  
			$stepsAll[5]['onclick'] = 'tab9Panel();'; 
			
			$stepsAll[6]['name'] = 'Visa Process';
			
			if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->visa_process_status != 4)
			{
				 $stepsAll[6]['stage'] = 'inprogress'; 
				 $stepsAll[6]['Tab'] = 'active'; 
			}
			else if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->visa_process_status == 4)
			{
				$completedStep++;
				 $stepsAll[6]['stage'] = 'active'; 
				 $OnboardingProgress = 'Visa Process';
				  $stepsAll[6]['Tab'] = 'active'; 
			}
			else
			{
				 $stepsAll[6]['stage'] = 'pending'; 
				  $stepsAll[6]['Tab'] = 'disabled-tab'; 
			}
		   
			$stepsAll[6]['slagURL'] = 'visaProcess'; 
			$stepsAll[6]['onclick'] = 'tabvisaProcessPanel();'; 		
			
			$stepsAll[7]['name'] = 'Training and On-Boarding Process'; 
			if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->training_process_status != 4)
			{
				$stepsAll[7]['stage'] = 'inprogress'; 
				$stepsAll[7]['Tab'] = 'active';
			}
			else if($documentCollectionDetails->status >= 6 && $documentCollectionDetails->training_process_status == 4)
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
			
		    
			$stepsAll[7]['slagURL'] = 'tab6';
			 $stepsAll[7]['onclick'] = 'tabtrainingProcessPanel();'; 	
			
			$stepsAll[8]['name'] = 'Bank Code Generation Process'; 
			if($documentCollectionDetails->status == 6)
			{
				$stepsAll[8]['stage'] = 'inprogress'; 
				$stepsAll[8]['Tab'] = 'active';
			}
			else if($documentCollectionDetails->status > 6)
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
			$stepsAll[8]['slagURL'] = 'tab7';
		    $stepsAll[8]['onclick'] = 'codeGeneration();'; 	
			
			
			$stepsAll[9]['name'] = 'On-Boarding Completed'; 
			
			if(($documentCollectionDetails->status >= 7 || $documentCollectionDetails->onboard_status == 1) || ($documentCollectionDetails->department == 8 && $documentCollectionDetails->status == 6))
			{
				$stepsAll[9]['stage'] = 'inprogress'; 
				$stepsAll[9]['Tab'] = 'active';
			}
			else if($documentCollectionDetails->onboard_status == 2)
			{
				$completedStep++;
				$stepsAll[9]['stage'] = 'active';
				$stepsAll[9]['Tab'] = 'active';				
			}
			else
			{
				$stepsAll[9]['stage'] = 'pending'; 
				 $stepsAll[9]['Tab'] = 'disabled-tab';
			}
			
		    $stepsAll[9]['slagURL'] = 'tab8';
			 $stepsAll[9]['onclick'] = 'finalization();'; 	
			$totalStep = 10;
			$p = $completedStep/$totalStep;
			$percentange = round($p*100);
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
	public function offerLetterIncentiveForm($id=NULL)
	   {
		   $uploadDetails = array();
		   $id = $id; 
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		  
		   
		   $documentAttributes = DocumentCollectionAttributes::where("attribute_area","offerincentiveletter")->where("status",1)->get();
		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
		   }
			
			/* echo '<pre>';
			print_r($uploadDetails);
			exit; */
		   return view("OnboardingAjax/RequestTabsAjax/offerLetterIncentiveForm",compact('documentDetails','documentAttributes','uploadDetails','id'));
	   }
	   public function tabonboardPanelAjax(Request $request)
		   {
				  $documentCollectId = $request->documentCollectionId;
				  $onboardkyc=OnboardCandidateKyc::where("docId",$documentCollectId)->first();
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
						if($docAttributes->attribute_area== 'onboard')
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
				 return view("OnboardingAjax/RequestTabsAjax/onboardDocumentAjax",compact('documentCollectionDetails','docCollectionDetails','onboardkyc')); 
		   }
		  public function visadetailedform(Request $request){
			  $documentCollectId = $request->documentCollectionId;
			  $visatype = $request->visatype;
			  $attributesDetailsvd = Attributes::where("tab_name","v_d")->where("status",1)->orderBy("sort_order","ASC")->get();
				$documentCollectionValues = VisaDetails::where("document_collection_id",$documentCollectId)->where("visa_type_id",$visatype)->get();
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
						if($docAttributes->attribute_area== 'onboard')
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
				return view("OnboardingAjax/RequestTabsAjax/visadetailsAjax",compact('attributesDetailsvd','documentCollectId','visatype','documentCollectionValues')); 
		  }
		   public static function getAttributeValue($docId,$visatype,$attributeid)
			{	
			//echo $empid;
			//echo $attributecode;//exit;
			
			  $attr = VisaDetails::where("document_collection_id",$docId)->where("visa_type_id",$visatype)->where("Attribute_code",$attributeid)->first();
			  //print_r($attr);//exit;
			  if($attr != '')
			  {
			  return $attr->attribute_value;
			  }
			  else
			  {
			  return '';
			  }
			}
			public function ManageBackout($docId)
		   {
				  $documentCollectId = $docId;
				 $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
				 
				 return view("OnboardingAjax/backoutDocumentAjax",compact('documentCollectionDetails','documentCollectId')); 
		   }
		   public function tab3backoutEmpDetailsAjax(Request $request)
		   {
				  $documentCollectId = $request->documentCollectionID;
				 $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
				 $backoutdata=DocumentCollectionBackout::where("document_id",$documentCollectId)->where("status",1)->first();
				 return view("OnboardingAjax/RequestTabsAjax/backoutEmpDetailsAjax",compact('documentCollectionDetails','backoutdata')); 
		   }
		   		   public function AllDocmentcollectionData(Request $request){
			   $id = $request->documentCollectionId;
			  $uploadDetails = array();
		   $id = $id; 
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		  
		   
		   $documentAttributes = DocumentCollectionAttributes::where("attrbute_type_id",2)->where("status",1)->get();
		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
		   }
			
			/* echo '<pre>';
			print_r($uploadDetails);
			exit; */
		   //return view("OnboardingAjax/RequestTabsAjax/offerLetterIncentiveForm",compact('documentDetails','documentAttributes','uploadDetails','id'));
	   
				return view("OnboardingAjax/RequestTabsAjax/requestdocumentattachmentAjax",compact('documentDetails','documentAttributes','uploadDetails','id')); 
		  
		   }
		public function OnboardLogsTab(Request $request){
			  $id = $request->documentCollectionId;
			  $visadata = Visaprocess::where("document_id",$id)->orderBy('id','ASC')->get();
			  $Documentdata=DocumentCollectionDetails::where("id",$id)->first();
			  
			  if($Documentdata!=''){
				  $interviewData = InterviewDetailsProcess::where("interview_id",$Documentdata->interview_id)->orderBy("id","DESC")->get();
			  }
			  else{
				  $interviewData = "";
			  }
			  
			  $special_comment=SpecialCommentLog::where("document_id",$id)->orderBy("created_at","DESC")->get();
				$DocumentCollectionDetails=DocumentCollectionDetailsLog::where("document_id",$id)->orderBy("id","DESC")->get();
				if($DocumentCollectionDetails!=''){
					$DocumentCollectionDetails=$DocumentCollectionDetails;
				}
				else{
					$DocumentCollectionDetails='';
				}
				return view("OnboardingAjax/RequestTabsAjax/OnboardLogsDetails",compact('visadata','special_comment','DocumentCollectionDetails','interviewData','Documentdata')); 
		  
		   }
	public static function getUserName($id)
	{	

	  $data = Employee::where('id',$id)->orderBy("id","DESC")->first();
	  //print_r($data);
	  if($data != '')
	  {
	  return $data->fullname;
	  }
	  else
	  {
	  return '';
	  }
	}
	public static function getDocAttributeVal($key)
	{	

	  $data = DocumentCollectionAttributes::where("id",$key)->first();
	  //print_r($data);
	  if($data != '')
	  {
	  return $data->attribute_name;
	  }
	  else
	  {
	  return $key;
	  }
	}
		   
		   
		   
	public static function getVisaProcessVal($key)
	{	

	  $data = VisaStage::where("id",$key)->first();
	  //print_r($data);
	  if($data != '')
	  {
	  return $data->stage_name;
	  }
	  else
	  {
	  return $key;
	  }
	}
	public static function getVisaProcessstage($key)
	{	

	  $data = Visaprocess::where("id",$key)->first();
	  //print_r($data);
	  if($data != '')
	  {
		  $stage = VisaStage::where("id",$data->visa_stage)->first();
	  return $stage->stage_name;
	  }
	  else
	  {
	  return $key;
	  }
	}
	
	
public function tabvisaProcessPanelAjaxCom(Request $request)
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
					$fineEach = $all->cost_fine;
					$totalCost = $totalCost+$costEach+$fineEach;
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
		return view("OnboardingAjax/RequestTabsAjax/tabvisaProcessPanelAjaxCom",compact('documentCollectionDetails','totalCost','percentage','lastStageCompleted','visaStageD','totalDays','visaStageLists','visaTypeId'));
	}

public static function getBDMInterview($id)
	{	

	  $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",78)->first();
	  //print_r($data);
	  if($documentAttributesDetails != '')
	  {
	  return $documentAttributesDetails->attribute_value;
	  }
	  else
	  {
	  return "";
	  }
	}
public static function getBDMdate($id)
	{	

	  $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",78)->first();
	  //print_r($data);
	  if($documentAttributesDetails != '')
	  {
		if($documentAttributesDetails->created_at!=''){
	  return date("d M Y",strtotime($documentAttributesDetails->created_at));
	  }
	  else{
		  return "";
	  }
	  }
	  else
	  {
	  return "";
	  }
	}
	public static function getVisaStagename($id)
	{	

	  $data = VisaStage::where("id",$id)->first();
	  //print_r($data);
	  if($data != '')
	  {
	  return $data->stage_name;
	  }
	  else
	  {
	  return '';
	  }
	}
	public static function getVisaStageDate($id)
	{	

	  $data = Visaprocess::where("document_id",$id)->orderBy('id','DESC')->first();
	  //print_r($data);
	  if($data != '')
	  {
		if($data->created_at!=''){
	  return date("d M Y",strtotime($data->created_at));
	  }
	  else{
		  return "";
	  }
	  }
	  else
	  {
	  return '';
	  }
	}
	public static function getStageUserName($id)
	{	

	  $data = Visaprocess::where("document_id",$id)->orderBy('id','DESC')->first();
	  //print_r($data);
	  if($data != '')
	  {
		$username = Employee::where('id',$data->createdBy)->orderBy("id","DESC")->first();
	  //print_r($data);
	  if($username != '')
	  {
	  return $username->fullname;
	  }
	  else
	  {
	  return '';
	  }
	  }
	  else
	  {
	  return '';
	  }
	}
	public function ViewFinalLogsData(Request $request){
			  $id = $request->documentCollectionId;
			  $Documentdata=DocumentCollectionDetails::where("id",$id)->first();
			  
			  if($Documentdata!=''){
				  $interviewData = InterviewDetailsProcess::where("interview_id",$Documentdata->interview_id)->orderBy("id","DESC")->get();
			  }
			  else{
				  $interviewData = "";
			  }
			  
			  
				$DocumentCollectionDetails=DocumentCollectionDetailsLog::where("document_id",$id)->orderBy("id","DESC")->get();
				if($DocumentCollectionDetails!=''){
					$DocumentCollectionDetails=$DocumentCollectionDetails;
				}
				else{
					$DocumentCollectionDetails='';
				}
				return view("OnboardingAjax/RequestTabsAjax/ViewFinalLogsData",compact('DocumentCollectionDetails','interviewData','Documentdata')); 
		  
		   }
	public function addSpecialCommentLog(Request $request)
	   {
		    $selectedFilter = $request->input();
		  	
				$docId = $selectedFilter['docId'];
				$catname = $selectedFilter['catname'];
				$titleData = $selectedFilter['titleData'];
				$special_comment = $selectedFilter['special_comment'];
				$logObj = new SpecialCommentLog();
				$logObj->createdBy=$request->session()->get('EmployeeId');
				$logObj->document_id =$docId;				
				$logObj->comment =$special_comment;
				$logObj->category =$catname;
				$logObj->title =$titleData;
				if($logObj->save()){
					echo "Data save.";
				}
				else{
					echo "Data not saved";
				}
			    $response['code'] = '200';
			  $response['message'] = " Saved Successfully.";
			   //$response['empid'] = $empIdPadding;
			   
			echo "yes";
			exit;
		  
	   }
	public static function getDocumentUpladDate($id)
	{	

	  $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",14)->first();
	  //print_r($data);
	  if($documentAttributesDetails != '')
	  {
		if($documentAttributesDetails->created_at!=''){
	  return date("d M Y",strtotime($documentAttributesDetails->created_at));
	  }
	  else{
		  return "";
	  }
	  }
	  else
	  {
	  return "";
	  }
	} 
public static function getDocumentUpladUserName($id)
	{	

	  $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",14)->first();
	  //print_r($data);
	  if($documentAttributesDetails != '')
	  {
	  $uid=$documentAttributesDetails->created_by;
		  $data = Employee::where('id',$uid)->orderBy("id","DESC")->first();
		  //print_r($data);
		  if($data != '')
		  {
		  return $data->fullname;
		  }
		  else
		  {
		  return '';
		  }
	  }
	  else
	  {
	  return "";
	  }
	}
//pasport data
	public static function getDocumentpasportUpladDate($id)
	{	

	  $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
	  //print_r($data);
	  if($documentAttributesDetails != '')
	  {
		if($documentAttributesDetails->created_at!=''){
	  return date("d M Y",strtotime($documentAttributesDetails->created_at));
	  }
	  else{
		  return "";
	  }
	  }
	  else
	  {
	  return "";
	  }
	} 
public static function getDocumentpasportUpladUserName($id)
	{	

	  $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
	  //print_r($data);
	  if($documentAttributesDetails != '')
	  {
	  $uid=$documentAttributesDetails->created_by;
		  $data = Employee::where('id',$uid)->orderBy("id","DESC")->first();
		  //print_r($data);
		  if($data != '')
		  {
		  return $data->fullname;
		  }
		  else
		  {
		  return '';
		  }
	  }
	  else
	  {
	  return "";
	  }
	}
	public function Viewcommentdata($docId=NULL, $catname=NULL){
		
		$special_comment=SpecialCommentLog::where("document_id",$docId)->where("category",$catname)->orderBy("created_at","DESC")->get();
		
		return view("OnboardingAjax/RequestTabsAjax/SpecialCommentLogdata",compact('special_comment')); 
		  
	}
// start visa type log
public static function getVisaTypenameData($id)
	{	

	  $data = Visaprocess::where("document_id",$id)->orderBy('id','ASC')->first();
	  //print_r($data);
	  if($data != '')
	  {
		  $stage = visaType::where("id",$data->visa_type)->first();
	  return $stage->title;
	  }
	  else
	  {
	  return '';
	  }
	}
	public static function getVisaTypeDate($id)
	{	

	  $data = Visaprocess::where("document_id",$id)->orderBy('id','ASC')->first();
	  //print_r($data);
	  if($data != '')
	  {
		if($data->created_at!=''){
	  return date("d M Y",strtotime($data->created_at));
	  }
	  else{
		  return "";
	  }
	  }
	  else
	  {
	  return '';
	  }
	}
	public static function getTypeUserNameData($id)
	{	

	  $data = Visaprocess::where("document_id",$id)->orderBy('id','ASC')->first();
	  //print_r($data);
	  if($data != '')
	  {
		$username = Employee::where('id',$data->createdBy)->orderBy("id","DESC")->first();
	  //print_r($data);
	  if($username != '')
	  {
	  return $username->fullname;
	  }
	  else
	  {
	  return '';
	  }
	  }
	  else
	  {
	  return '';
	  }
	}
	public static function specialcommentDataCount($title,$id){
	$special_comment_count=SpecialCommentLog::where("document_id",$id)->where("title",$title)->get()->count();
	return $special_comment_count;
	//echo (COUNT($special_comment));exit;
		
	}
	public static function specialcommentData($title,$id){
	$special_comment=SpecialCommentLog::where("document_id",$id)->where("title",$title)->get();
	//echo (COUNT($special_comment));exit;
		if(count($special_comment)>0){
			
			return $special_comment;
		}
		else{
			return 0;
		}
	}
	public static function getDocumentUpladSalaryDate($id)
	{	

	  $documentAttributesDetails =DocumentCollectionDetailsLog::where("document_id",$id)->where("title",'Updated Candidate Details')->orderBy('id','DESC')->first();
	  //print_r($data);
	  if($documentAttributesDetails != '')
	  {
		if($documentAttributesDetails->created_at!=''){
	  return date("d M Y",strtotime($documentAttributesDetails->created_at));
	  }
	  else{
		  return "";
	  }
	  }
	  else
	  {
	  return "";
	  }
	} 
	public static function getDocumentsalaryUserName($id)
	{	

	  $documentAttributesDetails =DocumentCollectionDetailsLog::where("document_id",$id)->where("title",'Updated Candidate Details')->orderBy('id','DESC')->first();
	  //print_r($data);
	  if($documentAttributesDetails != '')
	  {
	  $uid=$documentAttributesDetails->created_by;
		  $data = Employee::where('id',$uid)->orderBy("id","DESC")->orderBy('id','DESC')->first();
		  //print_r($data);
		  if($data != '')
		  {
		  return $data->fullname;
		  }
		  else
		  {
		  return '';
		  }
	  }
	  else
	  {
	  return "";
	  }
	}
	public static function getDocumentoldsalaryData($id)
	{	

	  $documentAttributesDetails =DocumentCollectionDetailsLog::where("document_id",$id)->where("title",'Updated Candidate Details')->first();
	  //print_r($data);
	  if($documentAttributesDetails != '')
	  {
		  $salary=json_decode($documentAttributesDetails->response, true);
		  
		  
		 return  $salary ['DocData']['proposed_salary'];
		  
		  
	  }
	  else
	  {
	  return "";
	  }
	}
	public static function getUserNameRequested($title,$id){
	 $documentAttributesDetails =DocumentCollectionDetailsLog::where("document_id",$id)->where("title","$title")->orderBy('id','DESC')->first();
	  //print_r($data);
	  if($documentAttributesDetails != '')
	  {
	  $uid=$documentAttributesDetails->created_by;
		  $data = Employee::where('id',$uid)->orderBy("id","DESC")->orderBy('id','DESC')->first();
		  //print_r($data);
		  if($data != '')
		  {
		  return $data->fullname;
		  }
		  else
		  {
		  return '';
		  }
	  }
	  else
	  {
	  return "";
	  }
	}
	public static function getdisapprovedVisa($title,$id)
	{	

	  $documentAttributesDetails =DocumentCollectionDetailsLog::where("document_id",$id)->where("title","$title")->orderBy('id','DESC')->first();
	  //print_r($data);
	  if($documentAttributesDetails != '')
	  {
		if($documentAttributesDetails->created_at!=''){
	  return date("d M Y",strtotime($documentAttributesDetails->created_at));
	  }
	  else{
		  return "";
	  }
	  }
	  else
	  {
	  return "";
	  }
	} 
	
	public static function getDocumentsLogsOnboard($docId)
	{
		$documentCollMod = DocumentCollectionDetailsValues::where("document_collection_id",$docId)
		->where("attribute_type",2)->get();
		
		$documentDetailsData = array();
		$index= 1;
		foreach($documentCollMod as $_mod)
		{
				$attriMod = DocumentCollectionAttributes::where("id",$_mod->attribute_code)->first();
				if($attriMod != '')
				{
					$documentDetailsData[$index]['AttributeName'] = $attriMod->attribute_name;
					$documentDetailsData[$index]['LastUpdatedDate'] = date("d M Y",strtotime($_mod->updated_at));
					$documentDetailsData[$index]['updated_by'] = $_mod->created_by;
					$index++;
				}
		}
		return $documentDetailsData;
	}
}