<?php

namespace App\Http\Controllers\VisaManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee\Employee_details;
use App\Models\Visa\visaType;
use App\Models\Visa\VisaStage;
use App\Models\Visa\VisaStageGroup;
use App\Models\Visa\Visaprocess;
use App\Models\VisaManagement\VisaManagementProcess;
use App\Models\Visa\VisaProcessHistroy;
use App\Models\VisaManagement\VisaManagementProcessHistroy;
use App\Models\Visa\DocumentUploadVisaStage;
use App\Models\VisaManagement\DocumentUploadVisaManagementStage;
use App\Models\Onboarding\DocumentCollectionDetails;
class VisaManagementController extends Controller
{
   	
	public function visaManagement(Request $req)
	{
		$documentCollectionId = $req->documentCollectionId;
		
		$documentCollectionData = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		
		
		$result = array();
		$result['empDetail'] = $documentCollectionData;
		/*
		*getting Visa Type List
		*/
		$visaTypeList = visaType::where("status",1)->orderBy("id","DESC")->get();
		
		$visaProcessLists = VisaManagementProcess::where("document_id",$documentCollectionId)->orderBy('id','DESC')->get();
		
		
		return view("VisaManagement/VisaManagement",compact('result','visaProcessLists','documentCollectionId','visaTypeList'));
	}
	
	public function VisaManagementType(Request $req)
	{
		$documentCollectionId = $req->documentCollectionId;
		$documentCollectionData = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		
		
		$result = array();
		$result['empDetail'] = $documentCollectionData;
		//print_r($visaTypeList);exit;
		return view("VisaManagement/CondidateDetails",compact('result'));
	}
	public function visaManagmentProcessStage(Request $req)
	{
		$stageId = $req->stageId;
		//echo $stageId;exit;
		$documentCollectionId = $req->documentCollectionId;
		$group = VisaStageGroup::where("status",1)->get();
		$visaStageLists = VisaStage::where("visa_type",$stageId)->where("status",1)->orderBy("stage_order","ASC")->get();
		$i=0;
		$groups=array();
		foreach($group as $_group){
			$visaStageval = VisaStage::where("visa_type",$stageId)->where("stage_group",$_group->id)->where("status",1)->get();
			//$group = VisaStageGroup::where("id",$_stagelist->stage_group)->where("status",1)->first();
			if(!empty($visaStageval)){	
			foreach($visaStageval as $fdata){
			$groups[$_group->id][$fdata->id] =$fdata->stage_name;
			
			}
			
			}
			else{
				$groups='';
			}
		}
		//print_r($groups);exit;
		return view("VisaManagement/VisaManagementProcess",compact('visaStageLists','groups','documentCollectionId'));
	}
	public static function getStageGroupName($stage_group)
		{
			$visastagename = VisaStageGroup::where("id",$stage_group)->first();

			return  $visastagename->group_name;
		}
	
	public function visaProcessStart(Request $req)
	{
		$documentCollectionId = $req->documentCollectionId;
		$stageId = $req->stageId;
		$visastagedata = VisaStage::where("id",$stageId)->where("status",1)->first();
		//print_r($visastage);exit;
		return view("VisaManagement/GroupStageDetails",compact('visastagedata','documentCollectionId'));
	}
	public function visaStageInitiateProcess($visaTypeId = NULL,$stageId=NULL,$documentCollectionId = NULL)
	{
		$visastage = VisaStage::where("id",$stageId)->where("status",1)->first();
		return view("VisaManagement/VisaManagementInitiateForm",compact('visastage', 'visaTypeId','stageId','documentCollectionId'));
	}
	public function visaStageChangeProcess($processId = NULL)
	{
		$processdata = VisaManagementProcess::where("id",$processId)->first();
		$visastage = VisaStage::where("id",$processdata->visa_stage)->where("status",1)->first();
		return view("VisaManagement/VisaManagementChangeStatusForm",compact('processdata','visastage'));
	}
	public function visaStageUpdateDocument($processId = NULL)
	{
		$processdata = VisaManagementProcess::where("id",$processId)->first();
		$visastage = VisaStage::where("id",$processdata->visa_stage)->where("status",1)->first();
		return view("VisaManagement/VisaManagementUpdateDocumentForm",compact('processdata','visastage'));
	}
	public function completeProcess($visaTypeId = NULL,$stageId=NULL,$documentCollectionId = NULL)
	{
		$visastage = VisaStage::where("id",$stageId)->where("status",1)->first();
		return view("VisaManagement/VisaManagementCompleteForm",compact('visastage', 'visaTypeId','stageId','documentCollectionId'));
	}
	

	
	
	
	public function setPreVisaStage($visaTypeId = NULL,$documentCollectionId = NULL)
	{
		$visaStageLists = VisaStage::where("visa_type",$visaTypeId)->where("status",1)->orderBy("stage_order","ASC")->get();
		return view("preVisaProcess/setPreVisaStage",compact('visaStageLists','documentCollectionId','visaTypeId'));
	}
	
	
	public function setPreVisaStageAjax($visaTypeId = NULL,$documentCollectionId = NULL)
	{
		$visaStageLists = VisaStage::where("visa_type",$visaTypeId)->where("status",1)->orderBy("stage_order","ASC")->get();
		return view("preVisaProcess/setPreVisaStageAjax",compact('visaStageLists','documentCollectionId','visaTypeId'));
	}
	public function preVisaProcess (Request $req)
	{
		$documentCollectionId = $req->documentCollectionId;
		
		$documentCollectionData = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		
		
		$result = array();
		$result['empDetail'] = $documentCollectionData;
		/*
		*getting Visa Type List
		*/
		$visaTypeList = visaType::where("status",1)->orderBy("id","DESC")->get();
		$result['visaTypeList'] = $visaTypeList;
		/*
		*getting Visa Type List
		*/
		/*
		*checking Visa Process Status for employee
		*Start Code
		*/
		$visaProcessLists = VisaManagementProcess::where("document_id",$documentCollectionId)->orderBy('id','DESC')->get();
		
		/*
		*checking Visa Process Status for employee
		*End Code
		*/
		return view("preVisaProcess/visaProcess_step1",compact('result'),compact('visaProcessLists'));
	}
	
	
	
	public function empPreVisaPostData(Request $req)
	{
		$requestData = $req->input();
			//print_r($requestData);exit;
		$visaprocessObj = new VisaManagementProcess();
		$visaprocessObj->document_id = $requestData['document_id'];
		$visaprocessObj->visa_type = $requestData['visa_type'];
		$visaprocessObj->visa_stage = $requestData['visa_stage'];
		$visaprocessObj->comment = $requestData['comment'];
		$visaprocessObj->stage_staus = $requestData['stage_staus'];
		if($requestData['stage_staus'] == 2 || $requestData['stage_staus'] == 1)
		{
			$visaprocessObj->cancel_status = 1;
		}
		else
		{
			$visaprocessObj->cancel_status = 2;
		}
		$visaprocessObj->save();
		/*
		*update Document Collection visa status
		*start code
		*/
			$docId = $requestData['document_id'];
			$_documentCollectionMod = DocumentCollectionDetails::find($docId);
			$_documentCollectionMod->visa_process_status = 2;
			$_documentCollectionMod->save();
		/*
		*update Document Collection visa status
		*end code
		*/
		//$req->session()->flash('message','Visa Process setup for Employee.');
        //return back();
		$response['code'] = '200';
	    $response['message'] = "Data Saved Successfully.";
	    $response['visa_type'] = $requestData['visa_type'];
		$response['visa_stage'] = $requestData['visa_stage'];
		$response['document_id'] =$requestData['document_id'];
	   //echo "hello";exit;
		echo json_encode($response);
		 
	    exit;
	}
	
	
	public function empPreVisaPostAjax(Request $req)
	{
		$requestData = $req->input();
		
		$visaprocessObj = new VisaManagementProcess();
		$visaprocessObj->document_id = $requestData['document_id'];
		$visaprocessObj->visa_type = $requestData['visa_type'];
		$visaprocessObj->visa_stage = $requestData['visa_stage'];
		$visaprocessObj->comment = $requestData['comment'];
		$visaprocessObj->stage_staus = $requestData['stage_staus'];
		if($requestData['stage_staus'] == 2 || $requestData['stage_staus'] == 1)
		{
			$visaprocessObj->cancel_status = 1;
		}
		else
		{
			$visaprocessObj->cancel_status = 2;
		}
		$visaprocessObj->save();
		/*
		*update Document Collection visa status
		*start code
		*/
			$docId = $requestData['document_id'];
			$_documentCollectionMod = DocumentCollectionDetails::find($docId);
			$_documentCollectionMod->visa_process_status = 2;
			$_documentCollectionMod->save();
		/*
		*update Document Collection visa status
		*end code
		*/
		echo 'Visa Process setup for Employee.';
        exit;
	}
	public function visaPreProcessPostChangestatus(Request $req)
	{
		$requestData = $req->input();
		//print_r($requestData);
		//print_r($_FILES);exit;
		$visaprocessObj = VisaManagementProcess::find($requestData['visa_process_id']);
		$visaprocessObj->cost = $requestData['cost'];
		$visaprocessObj->final_comment = $requestData['final_comment'];
		$visaprocessObj->stage_staus = $requestData['stage_staus'];
		if($requestData['stage_staus'] == 2)
		{
			$visaprocessObj->cancel_status = 1;
		}
		else
		{
			$visaprocessObj->cancel_status = 2;
		}
		$visaprocessObj->closing_date = date("Y-m-d");
		$visaprocessObj->save();
		
		/*
		*upload file if exist
		*/
		//DocumentUploadVisaStage
		$num = $visaprocessObj->id;
		$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				if($req->file($key))
				{
					
				 $filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
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
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
				$fileIndex++;
				}
			}
			/* echo '<pre>';
			print_r($filesAttributeInfo);
			echo "=======================";
			print_r($listOfAttribute);
			
			exit;
		 */
			foreach($filesAttributeInfo as $value)
			{
			$documentforVisaStageMod = new DocumentUploadVisaManagementStage();
			$documentforVisaStageMod->visaprocess_id = $visaprocessObj->id;
			$documentforVisaStageMod->file_name = $value;
			$documentforVisaStageMod->save();
			}
			$response['code'] = '200';
			$response['message'] = "Data Saved Successfully.";
			$response['visa_type'] = $requestData['visa_type'];
			$response['visa_stage'] = $requestData['visa_stage'];
			$response['document_id'] =$requestData['document_id'];
		   //echo "hello";exit;
			echo json_encode($response);
			 
			exit;
			
		/*
		*upload file if exist
		*/
		
		//$req->session()->flash('message','Visa Process setup for Request.');
        //return back();
	}
	public static function getStatusOfStage($documentId,$stageId,$typeId)
	   {
		  $visaProcessDetail =  VisaManagementProcess::where("document_id",$documentId)->where("visa_stage",$stageId)->where("visa_type",$typeId)->first();
		  if($visaProcessDetail != '')
		  {
		  if( $visaProcessDetail->stage_staus == 1)
		  {
			  return "class-inprogress";
		  }
		  else if($visaProcessDetail->stage_staus == 2)
		  {
			  return "class-completed";
		  }
		  else if($visaProcessDetail->stage_staus == 3)
		  {
			  return "class-cancelled";
		  }
		  else
		  {
			  return "class-pending";
		  }
		  }
		  else
		  {
			  return "class-pending";
		  }
		 
	   }
	   
	 public static function allowAction($documentId,$stageId,$typeId)
	   {
		  $visaProcessDetail =  VisaManagementProcess::where("document_id",$documentId)->where("visa_type",$typeId)->orderBy("id","DESC")->first();
		 
		  if($visaProcessDetail != '')
		  {
			   if($visaProcessDetail->stage_staus == 1)
			  {
				  return "No";
			  }
			  else
			  {
			  return 'Yes';
			  }
		  }
		  else
		  {
			  return "Yes";
		  }
		 
	   }
	   public static function getStageStatus($stageId,$documentId)
		   {
			   
			   $visaProcessDetail =  VisaManagementProcess::where("document_id",$documentId)->where("visa_stage",$stageId)->first();
			 if(!empty($visaProcessDetail)){
			  return  $visaProcessDetail->stage_staus;
			 }
			 
		   }
	   
	    public static function getVisaProcessId($documentId,$stageId,$typeId)
		   {
			   
			   $visaProcessDetail =  VisaManagementProcess::where("document_id",$documentId)->where("visa_stage",$stageId)->where("visa_type",$typeId)->first();
			 if(!empty($visaProcessDetail)){
			  return  $visaProcessDetail->id;
			 }
			 
		   }
	   public static function detailedVisaStage($documentId,$stageId,$typeId)
		   {
			   $visaProcessDetail =  VisaManagementProcess::where("document_id",$documentId)->where("visa_stage",$stageId)->where("visa_type",$typeId)->first();
			 
			  return  $visaProcessDetail;
			 
		   }
		   public static function getgroupcount($groupdata){
			 $finaldata=array();
			 $count=array();
			$totalStages = VisaStage::where("stage_group",$groupdata)->where("status",1)->get();
			if(!empty($totalStages)){
			foreach($totalStages as $data){
				//echo $data->id
			$visaProcess =  VisaManagementProcess::where("visa_stage",$data->id)->where("stage_staus",2)->first();
			if(!empty($visaProcess)){
			 $count[]=$visaProcess->id;
			}
			}
			}
			$finaldata=count($count);
		   $totalStagesCount = VisaStage::where("stage_group",$groupdata)->where("status",1)->count();
		   
			$p1 = $finaldata/$totalStagesCount;
				$p2 = round($p1*100);
		   return $p2;
		   }
		   
      public static function progressDetails($documentId,$typeId,$stageId)
		   {
			   
			   $visaProcessDetail =  VisaManagementProcess::where("document_id",$documentId)->where("visa_type",$typeId)->where("visa_stage",$stageId)->orderBy("id","ASC")->first();
			    $progressArray = array();
			   if($visaProcessDetail != '')
			   {
			  
			   
			   $initiatedDate = $visaProcessDetail->created_at;
			   $now = date("Y-m-d");
			 
			   $datediff = strtotime($now) - strtotime($initiatedDate);

				$days =   round($datediff / (60 * 60 * 24));
				
				$progressArray['initiatedDate'] = date("d M Y",strtotime($initiatedDate));
				//$progressArray['currentStage'] = $visaProcessDetail->stage_name
				$progressArray['days'] = $days;

				$visaProcessDetailCurrentStage =  VisaManagementProcess::where("document_id",$documentId)->where("visa_type",$typeId)->orderBy("id","DESC")->first();
				$visaStageIdF = $visaProcessDetailCurrentStage->visa_stage;
				$visaStageD = VisaStage::where("id",$visaStageIdF)->first();
				$progressArray['currentStage'] = $visaStageD->stage_name;
				
				/*
				*total Cost and percentage work
				*/
				/* echo $documentId;
				echo '<br />';
				echo $typeId;
				exit; */
				$visaProcessDetailAll =  VisaManagementProcess::where("document_id",$documentId)->where("visa_type",$typeId)->get();
				
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
				
				$progressArray['totalCost'] = $totalCost;
				/*
				*total Cost and percentage work
				*/
				/*
				*percentage work
				*/
				$lastStageCompleted =  VisaManagementProcess::where("document_id",$documentId)->where("visa_type",$typeId)->where("stage_staus",2)->orderBy("id","DESC")->first();
				if($lastStageCompleted != '')
				{
				 $visaStageId = $lastStageCompleted->visa_stage;
				$visaStageD = VisaStage::where("id",$visaStageId)->first();
				$completedStageOrder = $visaStageD->stage_order;
				
				$totalStagesCount = VisaStage::where("visa_type",$typeId)->where("status",1)->count();
				$p1 = $completedStageOrder/$totalStagesCount;
				$p2 = round($p1*100);
				$progressArray['percentage'] = $p2;
				}
				else
				{
					$progressArray['percentage'] = 0;
				}
				/*
				*percentage work
				*/
			   }
			  return  $progressArray;
			 
		   }
		   
		   
		   
	public function forceVisaCompletePost(Request $req)
	{
		$requestData = $req->input();
		

		
		$visaprocessObj = new VisaManagementProcess();
		$visaprocessObj->document_id = $requestData['document_id'];
		$visaprocessObj->visa_type = $requestData['visa_type'];
		$visaprocessObj->visa_stage = $requestData['visa_stage'];
		$visaprocessObj->comment = $requestData['comment'];
		$visaprocessObj->final_comment = $requestData['final_comment'];
		$visaprocessObj->cost = $requestData['cost'];
		$visaprocessObj->stage_staus = 2;
		$visaprocessObj->closing_date = date("Y-m-d");
		$visaprocessObj->save();
		/*
		*update Document Collection visa status
		*start code
		*/
			$docId = $requestData['document_id'];
			$_documentCollectionMod = DocumentCollectionDetails::find($docId);
			$_documentCollectionMod->visa_process_status = 2;
			$_documentCollectionMod->save();
		/*
		*update Document Collection visa status
		*end code
		*/
		
		/*
		*upload file if exist
		*/
		//DocumentUploadVisaStage
		$num = $visaprocessObj->id;
		$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				if($req->file($key))
				{
					
				 $filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
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
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
				$fileIndex++;
				}
			}
			/* echo '<pre>';
			print_r($filesAttributeInfo);
			echo "=======================";
			print_r($listOfAttribute);
			
			exit;
		 */
			foreach($filesAttributeInfo as $value)
			{
			$documentforVisaStageMod = new DocumentUploadVisaManagementStage();
			$documentforVisaStageMod->visaprocess_id = $visaprocessObj->id;
			$documentforVisaStageMod->file_name = $value;
			$documentforVisaStageMod->save();
			}
			
		/*
		*upload file if exist
		*/
		//$req->session()->flash('message','Visa Process setup for Request.');
        //return back();
		$response['code'] = '200';
	    $response['message'] = "Data Saved Successfully.";
	    $response['visa_type'] = $requestData['visa_type'];
		$response['visa_stage'] = $requestData['visa_stage'];
		$response['document_id'] =$requestData['document_id'];
	   //echo "hello";exit;
		echo json_encode($response);
		 
	    exit;
		
	}
	
	function visaPreProcessPostUpdateDoc(Request $req)
	{
			$requestData = $req->input();
			 //echo '<pre>';
			//print_r($requestData);
			//print_r($_FILES);
			//exit; 
			if(!empty($requestData['removeDoc'])){
			$removeDocs = $requestData['removeDoc'];
			
			foreach($removeDocs as $rowId=>$value)
			{
				if($value == 2)
				{
					$documentDatas = DocumentUploadVisaManagementStage::where("id",$rowId)->first();
					 unlink(public_path('documentCollectionFiles/'.$documentDatas->file_name));
					DocumentUploadVisaManagementStage::find($rowId)->delete();
				}
			}
			}
			
			$filesAttributeInfo = array();
			$num = $requestData['visa_process_id'];
		$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				if($req->file($key))
				{
					
				 $filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
				$vKey = $key;
			    $newFileName = $key.'-'.$num.'-'.time().'.'.$fileExtension;
			   
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
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
			
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
				$fileIndex++;
				}
			}
			/* echo '<pre>';
			print_r($filesAttributeInfo);
			echo "=======================";
			print_r($listOfAttribute);
			
			exit;
		 */
			foreach($filesAttributeInfo as $value)
			{
			$documentforVisaStageMod = new DocumentUploadVisaManagementStage();
			$documentforVisaStageMod->visaprocess_id = $requestData['visa_process_id'];
			$documentforVisaStageMod->file_name = $value;
			$documentforVisaStageMod->save();
			}
			
			
		/*
		*upload file if exist
		*/
		//$req->session()->flash('message','Visa stage updated.');
        //return back();
		$response['code'] = '200';
	    $response['message'] = "Data Saved Successfully.";
	    $response['visa_type'] = $requestData['visa_type'];
		$response['visa_stage'] = $requestData['visa_stage'];
		$response['document_id'] =$requestData['document_id'];
	   //echo "hello";exit;
		echo json_encode($response);
		 
	    exit;
	}
	
	public static function getVisaTypeName($typeId)
	{
		$visaTypeDetails = visaType::where("id",$typeId)->first();
		return $visaTypeDetails->title;
	}
	public static function getStageId($documentCollectionId){
	
	$stage_id = VisaManagementProcess::where("document_id",$documentCollectionId)->first();
	if(!empty($stage_id)){
		return $stage_id->visa_type;
	}
	else{
		return "";
	}
	}
	
	public static function rollBackVisaTypeData(Request $request)
	{
		$documentCollectionId = $request->documentCollectionId;
		$visaProcessLists = VisaManagementProcess::where("document_id",$documentCollectionId)->get();
		
		foreach($visaProcessLists as $_visaP)
		{
			$modHistroy = new VisaManagementProcessHistroy();
			$modHistroy->document_id = $_visaP->document_id;
			$modHistroy->visa_type = $_visaP->visa_type;
			$modHistroy->visa_stage = $_visaP->visa_stage;
			$modHistroy->comment = $_visaP->comment;
			$modHistroy->stage_staus = $_visaP->stage_staus;
			$modHistroy->cancel_status = $_visaP->cancel_status;
			$modHistroy->final_comment = $_visaP->final_comment;
			$modHistroy->OldCreated_at = $_visaP->created_at;
			$modHistroy->OldUpdated_at = $_visaP->updated_at;
			$modHistroy->closing_date = $_visaP->closing_date;
			$modHistroy->save();
			$visaProcessDeleteMod = VisaManagementProcess::find($_visaP->id);
			$visaProcessDeleteMod->delete();
		}
		//$request->session()->flash('success','Successfully rollback.');
        //return back();
		$response['code'] = '200';
	    $response['message'] = "Data Saved Successfully.";
		$response['document_id'] =$documentCollectionId;
	   //echo "hello";exit;
		echo json_encode($response);
		 
	    exit;
	}
	public function getExtraUploadcomplete(Request $request)
	{
		$index = $request->index;
		return view("VisaManagement/getExtraUpload",compact('index'));
	}
	public function getExtraUploadUpdatedoc(Request $request)
	{
		$index = $request->index;
		$visaProcessID = $request->visaProcessID;
		$documents = DocumentUploadVisaManagementStage::where("visaprocess_id",$visaProcessID)->get();
		return view("VisaManagement/getExtraUploadUpdate",compact('index','documents'));
	}
	public function getExtraUploadNew(Request $request)
	{
		$index = $request->index;
		return view("VisaManagement/getExtraUploadNew",compact('index'));
	}
	
	public static function documentCounting($id)
	{
		$documentCount = DocumentUploadVisaManagementStage::where("visaprocess_id",$id)->count();
		return $documentCount;
	}
	
	public function getFilesForStagesFile(Request $request)
	{
		$processId = $request->processId;
		$documentlist = DocumentUploadVisaManagementStage::where("visaprocess_id",$processId)->get();
		return view("VisaManagement/getFilesForStages",compact('documentlist'));
	}
	public function checkForVisaPCompleteProcess(Request $request)
	{
		$documentCollectionId = $request->documentCollectionId;
		
		$visaProcessLists = Visaprocess::where("document_id",$documentCollectionId)->orderBy("id","DESC")->first();
		return view("VisaManagement/checkForVisaPComplete",compact('visaProcessLists'));
		
	}
	public function completeVisaProcessFinal(Request $request)
	{
		$documentCollectionId = $request->documentCollectionId;
		$docMod = DocumentCollectionDetails::find($documentCollectionId);
		$docMod->visa_process_status = 4;
		$docMod->save();
		//$request->session()->flash('message','Visa Process Complete for Request.');
		//return redirect('documentcollectionAjax?id='.$documentCollectionId.'&type=VisaP');
		$response['code'] = '200';
	    $response['message'] = "Data Saved Successfully.";
		$response['document_id'] =$documentCollectionId;
	   //echo "hello";exit;
		echo json_encode($response);	 
	    exit;
	}
	
	public static function getFinalRequestVisaStatus($id)
	{
		return DocumentCollectionDetails::where("id",$id)->first()->visa_process_status;
	}
	
	public function updateVisaProcess(Request $request)
	{
		$pid = $request->processId;
		$visaProcessDetails = VisaManagementProcess::where("id",$pid)->first();
		
		return view("preVisaProcess/updateVisaProcess",compact('visaProcessDetails'));
	}
}