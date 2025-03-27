<?php

namespace App\Http\Controllers\EmpOfflineProcess;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee\Employee_details;
use App\Models\Visa\visaType;
use App\Models\Visa\VisaStage;
use App\Models\Visa\Visaprocess;
use App\Models\Visa\VisaProcessHistroy;
use App\Models\Visa\DocumentUploadVisaStage;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Logs\DocumentCollectionDetailsLog;
use App\Models\EmpOffline\CancelationVisaProcess;
use App\Models\EmpOffline\EmpOffline;
use App\Models\EmpOffline\DocumentUploadCancelationvisaStage;
use App\Models\EmpOffline\VisaProcessCancelationHistroy;

class CancelationVisaController extends Controller
{
	
	public static function getStageName($id)
	{
		return VisaStage::where("id",$id)->first()->stage_name;
	}
    public function selectEmployee()
	{
		$empDetails = Employee_details::where("status",1)->orderBy("id","DESC")->get();
		return view("VisaProcess/selectEmployee",compact('empDetails'));
	}
	public function preVisaProcess (Request $req)
	{
		$documentCollectionId = $req->documentCollectionId;
		
		$documentCollectionData = EmpOffline::where("id",$documentCollectionId)->first();
		
		
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
		$visaProcessLists = CancelationVisaProcess::where("document_id",$documentCollectionId)->orderBy('id','DESC')->get();
		
		/*
		*checking Visa Process Status for employee
		*End Code
		*/
		return view("EmpOfflineProcess/CancelationVisaProcess/visaProcess_step1",compact('result'),compact('visaProcessLists'));
	}
	public function preCancelVisaProcessAjax (Request $req)
	{
		$documentCollectionId = $req->documentCollectionId;
		
		$documentCollectionData = EmpOffline::where("id",$documentCollectionId)->first();
		$docId=$documentCollectionData->document_collection_id;
		$visatYpeId = '';
		
		
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
		$visaProcessLists = CancelationVisaProcess::where("document_id",$documentCollectionId)->orderBy('id','DESC')->get();
		if(count($visaProcessLists)>0){
			$visaProcessLists =$visaProcessLists;
		}else{
			if($docId!=''){
			
			$Documentdata=DocumentCollectionDetails::where("id",$docId)->first();
			
			$visaprocessstatus=$Documentdata->visa_process_status;
			if($visaprocessstatus==4){
				$visaProcess = Visaprocess::where("document_id",$docId)->orderBy('id','DESC')->first();
				if($visaProcess!=''){
					$visatYpeId = $visaProcess->visa_type;
				}
				else{
					$visatYpeId = '';
				}
			}
			
		}
		$visaProcessLists = CancelationVisaProcess::where("document_id",$documentCollectionId)->orderBy('id','DESC')->get();
		
		}
		/*
		*checking Visa Process Status for employee
		*End Code
		*/
		//echo "Wait...";exit;
		return view("EmpOfflineProcess/CancelationVisaProcess/visaProcess_step1_ajax",compact('result'),compact('visaProcessLists','visatYpeId'));
	}
	
	public function setPreCancelVisaStage($visaTypeId = NULL,$documentCollectionId = NULL)
	{
		$visaStageLists = VisaStage::where("visa_type",$visaTypeId)->where("status",1)->where("category",2)->orderBy("stage_order","ASC")->get();
		return view("EmpOfflineProcess/CancelationVisaProcess/setPreVisaStage",compact('visaStageLists','documentCollectionId','visaTypeId'));
	}
	public function setPreCancelVisaStageAjax($visaTypeId = NULL,$documentCollectionId = NULL)
	{
		$visaStageLists = VisaStage::where("visa_type",$visaTypeId)->where("status",1)->where("category",2)->orderBy("stage_order","ASC")->get();
		return view("EmpOfflineProcess/CancelationVisaProcess/setPreVisaStageAjax",compact('visaStageLists','documentCollectionId','visaTypeId'));
	}
	public function empPreVisaPost(Request $req)
	{
		$requestData = $req->input();
		
		$visaprocessObj = new CancelationVisaProcess();
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
			$_documentCollectionMod = EmpOffline::find($docId);
			$_documentCollectionMod->visa_process_status = 2;
			$_documentCollectionMod->save();
		/*
		*update Document Collection visa status
		*end code
		*/
		$req->session()->flash('message','Visa Process setup for Employee.');
        //return back();
				$response['code'] = '200';
			   $response['message'] = "Data Saved Successfully.";
			   $response['docId'] = $docId;
			   
				echo json_encode($response);
			   exit;
	}
	
	
	public function empPreVisaPostAjax(Request $req)
	{
		$requestData = $req->input();
		
		$visaprocessObj = new CancelationVisaProcess();
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
			$_documentCollectionMod = EmpOffline::find($docId);
			$_documentCollectionMod->visa_process_status = 2;
			$_documentCollectionMod->save();
		/*
		*update Document Collection visa status
		*end code
		*/
		echo 'Visa Process setup for Employee.';
        exit;
	}
	public function visaPreProcessPost(Request $req)
	{
		$requestData = $req->input();
		//print_r($_FILES);exit;
		$visaprocessObj = CancelationVisaProcess::find($requestData['visa_process_id']);
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
			$documentforVisaStageMod = new DocumentUploadCancelationvisaStage();
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
			   $response['docId'] = $requestData['document_id'];
			   
				echo json_encode($response);
			   exit;
	}
	public static function getStatusOfStage($documentId,$stageId,$typeId)
	   {
		  $visaProcessDetail =  CancelationVisaProcess::where("document_id",$documentId)->where("visa_stage",$stageId)->where("visa_type",$typeId)->first();
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
		  $visaProcessDetail =  CancelationVisaProcess::where("document_id",$documentId)->where("visa_type",$typeId)->orderBy("id","DESC")->first();
		 
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
	   
	    public static function getVisaProcessId($documentId,$stageId,$typeId)
		   {
			   $visaProcessDetail =  CancelationVisaProcess::where("document_id",$documentId)->where("visa_stage",$stageId)->where("visa_type",$typeId)->first();
			 
			  return  $visaProcessDetail->id;
			 
		   }
	   public static function detailedVisaStage($documentId,$stageId,$typeId)
		   {
			   $visaProcessDetail =  CancelationVisaProcess::where("document_id",$documentId)->where("visa_stage",$stageId)->where("visa_type",$typeId)->first();
			 
			  return  $visaProcessDetail;
			 
		   }
		   
      public static function progressDetails($documentId,$typeId)
		   {
			   $visaProcessDetail =  CancelationVisaProcess::where("document_id",$documentId)->where("visa_type",$typeId)->orderBy("id","ASC")->first();
			    $progressArray = array();
			   if($visaProcessDetail != '')
			   {
			  
			   
			   $initiatedDate = $visaProcessDetail->created_at;
			   $now = date("Y-m-d");
			 
			   $datediff = strtotime($now) - strtotime($initiatedDate);

				$days =   round($datediff / (60 * 60 * 24));
				
				$progressArray['initiatedDate'] = date("d M Y",strtotime($initiatedDate));
				$progressArray['days'] = $days;

				$visaProcessDetailCurrentStage =  CancelationVisaProcess::where("document_id",$documentId)->where("visa_type",$typeId)->orderBy("id","DESC")->first();
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
				$visaProcessDetailAll =  CancelationVisaProcess::where("document_id",$documentId)->where("visa_type",$typeId)->get();
				
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
				$lastStageCompleted =  CancelationVisaProcess::where("document_id",$documentId)->where("visa_type",$typeId)->where("stage_staus",2)->orderBy("id","DESC")->first();
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
		   
	public function CancelempPreVisaCompletePost(Request $req)
	{
		$requestData = $req->input();
		//print_r($requestData);exit;
		$documentId = $requestData['document_id'];
		$visaType = $requestData['visa_type'];
		$visaStage = $requestData['visa_stage'];
		$visaprocessExist = CancelationVisaProcess::where("document_id",$documentId)->where("visa_type",$visaType)->where("visa_stage",$visaStage)->first();
		if($visaprocessExist != '')
		{
			$visaprocessObj = CancelationVisaProcess::find($visaprocessExist->id);
		}
		else
		{
		$visaprocessObj = new CancelationVisaProcess();
		}
		$visaprocessObj->document_id = $requestData['document_id'];
		$visaprocessObj->visa_type = $requestData['visa_type'];
		$visaprocessObj->visa_stage = $requestData['visa_stage'];
		$visaprocessObj->comment = $requestData['comment'];
		$visaprocessObj->final_comment = $requestData['final_comment'];
		$visaprocessObj->cost = $requestData['cost'];
		$visaprocessObj->cost_fine = $requestData['cost_fine'];
		$visaprocessObj->stage_staus = $requestData['stage_staus'];
		$visaprocessObj->closing_date_by_system = date("Y-m-d");
		$visaprocessObj->closing_date = $requestData['completiondate'];
		$visaprocessObj->save();
			
		/*
		*update Document Collection visa status
		*start code
		*/
			$docId = $requestData['document_id'];
			$_documentCollectionMod = EmpOffline::find($docId);
			$_documentCollectionMod->visa_process_status = $requestData['stage_staus'];
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
			$documentforVisaStageMod = new DocumentUploadCancelationvisaStage();
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
			   $response['docId'] = $docId;
			   
				echo json_encode($response);
			   exit;
	}
	
	
	function CancelupdateDocPost(Request $req)
	{
			$requestData = $req->input();
			/* echo '<pre>';
			print_r($requestData);
			exit; */
			/*$removeDocs = $requestData['removeDoc'];
			if($removeDocs!=''){
			foreach($removeDocs as $rowId=>$value)
			{
				if($value == 2)
				{
					$documentDatas = DocumentUploadCancelationvisaStage::where("id",$rowId)->first();
					 unlink(public_path('documentCollectionFiles/'.$documentDatas->file_name));
					DocumentUploadCancelationvisaStage::find($rowId)->delete();
				}
			}
			}
			*/
			$filesAttributeInfo = array();
			$num = $requestData['visa_p'];
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
			$documentforVisaStageMod = new DocumentUploadCancelationvisaStage();
			$documentforVisaStageMod->visaprocess_id = $requestData['visa_p'];
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
	   $response['docId'] = $requestData['document_id'];
	   
		echo json_encode($response);
	   exit;
	}
	
	public static function getVisaTypeName($typeId)
	{
		$visaTypeDetails = visaType::where("id",$typeId)->first();
		return $visaTypeDetails->title;
	}
	
	public static function CancelrollBackVisaType(Request $request)
	{
		$documentCollectionId = $request->documentCollectionId;
		$typeId = $request->typeId;
		$visaProcessLists = CancelationVisaProcess::where("document_id",$documentCollectionId)->where("visa_type",$typeId)->get();
		
		foreach($visaProcessLists as $_visaP)
		{
			$modHistroy = new VisaProcessCancelationHistroy();
			$modHistroy->document_id = $_visaP->document_id;
			$modHistroy->visa_type = $_visaP->visa_type;
			$modHistroy->visa_stage = $_visaP->visa_stage;
			$modHistroy->comment = $_visaP->comment;
			$modHistroy->stage_staus = $_visaP->stage_staus;
			$modHistroy->cost = $_visaP->cost;
			$modHistroy->cost_fine = $_visaP->cost_fine;
			$modHistroy->cancel_status = $_visaP->cancel_status;
			$modHistroy->final_comment = $_visaP->final_comment;
			$modHistroy->OldCreated_at = $_visaP->created_at;
			$modHistroy->OldUpdated_at = $_visaP->updated_at;
			$modHistroy->closing_date = $_visaP->closing_date;
			$modHistroy->save();
				
			$visaProcessDeleteMod = CancelationVisaProcess::find($_visaP->id);
			$visaProcessDeleteMod->delete();
		}
		//$request->session()->flash('success','Successfully rollback.');
        //return back();
		$response['code'] = '200';
			   $response['message'] = "Successfully rollback";
			   $response['docId'] = $documentCollectionId;
			   
				echo json_encode($response);
			   exit;
	}
	public function getExtraUpload(Request $request)
	{
		$index = $request->index;
		return view("preVisaProcess/getExtraUpload",compact('index'));
	}
	public function getExtraUploadUpdate(Request $request)
	{
		$index = $request->index;
		$visaProcessID = $request->visaProcessID;
		$documents = DocumentUploadCancelationvisaStage::where("visaprocess_id",$visaProcessID)->get();
		return view("preVisaProcess/getExtraUploadUpdate",compact('index','documents'));
	}
	public function getExtraUploadNew(Request $request)
	{
		$index = $request->index;
		return view("preVisaProcess/getExtraUploadNew",compact('index'));
	}
	
	public static function documentCounting($id)
	{
		$documentCount = DocumentUploadCancelationvisaStage::where("visaprocess_id",$id)->count();
		return $documentCount;
	}
	
	public function CancelgetFilesForStages(Request $request)
	{
		$processId = $request->processId;
		$documentlist = DocumentUploadCancelationvisaStage::where("visaprocess_id",$processId)->get();
		$stagestatus=CancelationVisaProcess::where("id",$processId)->first();
		
		if($stagestatus!=''){
		$status=$stagestatus->stage_staus;
		}
		return view("EmpOfflineProcess/CancelationVisaProcess/getFilesForStages",compact('documentlist','status'));
	}
	public function CancelcheckForVisaPComplete(Request $request)
	{
		$documentCollectionId = $request->documentCollectionId;
		$typeId = $request->typeId;
		$visaProcessLists = CancelationVisaProcess::where("document_id",$documentCollectionId)->where("visa_type",$typeId)->orderBy("id","DESC")->first();
		$visaProcessListsAll = CancelationVisaProcess::where("document_id",$documentCollectionId)->where("visa_type",$typeId)->orderBy("id","DESC")->get();
		return view("EmpOfflineProcess/CancelationVisaProcess/checkForVisaPComplete",compact('visaProcessLists','visaProcessListsAll'));
	}
	public function CancelcompleteVisaProcess(Request $request)
	{
		$parameters = $request->input();
		
		$documentCollectionId = $parameters['docID'];
		$visaTypeId = $parameters['visaTypeId'];
		 $costArray = $parameters['cost'];
		 $cost_fineArray = $parameters['cost_fine'];
		 /*
		 *update cost
		 */
		 foreach($costArray as $visaSId=>$cost)
		 {
				$visaStageData = CancelationVisaProcess::where("document_id",$documentCollectionId)->where("visa_type",$visaTypeId)->where("visa_stage",$visaSId)->first();
			
		      $updateMod = CancelationVisaProcess::find($visaStageData->id);
			  $updateMod->cost = $cost;
			   //$updateMod->cost_fine = $cost_fineArray;
			  $updateMod->save();
				
		 }
		 foreach($cost_fineArray as $visaSId=>$cost_fine)
		 {
				$visaStageData = CancelationVisaProcess::where("document_id",$documentCollectionId)->where("visa_type",$visaTypeId)->where("visa_stage",$visaSId)->first();
			
		      $updateMod = CancelationVisaProcess::find($visaStageData->id);
			  //$updateMod->cost = $cost;
			   $updateMod->cost_fine = $cost_fine;
			  $updateMod->save();
				  
		 }
		
		 /*
		 *update cost
		 */
		$docMod = EmpOffline::find($documentCollectionId);
		$docMod->condition_leaving = 5;
		$docMod->visa_process_status= 5;
		$docMod->save(); 
		//$request->session()->flash('message','Visa Process Complete for Request.');
		//return redirect('documentcollectionAjax?id='.$documentCollectionId.'&type=VisaP');
		 $response['code'] = '200';
			   $response['message'] = "Successfully rollback";
			   $response['docId'] = $documentCollectionId;
			   
				echo json_encode($response);
			   exit; 
	}
	
	public static function getFinalRequestVisaStatus($id)
	{
		$data=EmpOffline::where("id",$id)->first();
		if($data!=''){
			return $data->visa_process_status;
		}
		else{
			return '';
		}
	}
	
	public function updateVisaProcess(Request $request)
	{
		$pid = $request->processId;
		$visaProcessDetails = CancelationVisaProcess::where("id",$pid)->first();
		
		return view("EmpOfflineProcess/CancelationVisaProcess/updateVisaProcess",compact('visaProcessDetails'));
	}
	public function getcompleteProcessStages($docId=NULL,$visaId=NULL,$stageId=NULL){
		//echo $visaId;
		$visaProcessDetails = CancelationVisaProcess::where("document_id",$docId)->where("visa_type",$visaId)->where("visa_stage",$stageId)->first();
		//print_r($visaProcessDetails);exit;
		$response = array();
		if($visaProcessDetails!=''){
			$stageId=$visaProcessDetails->id;
			$data=DocumentUploadCancelationvisaStage::where("visaprocess_id",$stageId)->get();
			$i=1;
			foreach($data as $_img){
			$response['img'][$i]=$_img->file_name;
			$i++;
			}
			
			$response['code'] = '200';
		    
		    $response['cost'] = $visaProcessDetails->cost;
			 $response['cost_fine'] = $visaProcessDetails->cost_fine;
			$response['comment'] = $visaProcessDetails->comment;
			$response['final_comment'] = $visaProcessDetails->final_comment;
			//$response['closing_date'] = date("dd/MM/YYYY",strtotime($visaProcessDetails->closing_date));
			$response['closing_date'] =$visaProcessDetails->closing_date;
			$response['stage_staus'] = $visaProcessDetails->stage_staus;	
//print_r($response);exit;			
			echo json_encode($response);
		   exit;
		}
		else
		{
			$response['code'] = '300';
			echo json_encode($response);
		   exit;
		}
		
	}
	public function getFilesForStagesDelete($stageId=NULL){
		
		$uploadfile=DocumentUploadCancelationvisaStage::where("id",$stageId)->first();
		$data=CancelationVisaProcess::where("id",$uploadfile->visaprocess_id)->first();
		$image_name = $uploadfile->file_name;
		  if($image_name != '')
		  {
			if(file_exists(public_path('documentCollectionFiles/'.$image_name))){

			  unlink(public_path('documentCollectionFiles/'.$image_name));

			}
		  }
			$docMod = DocumentUploadCancelationvisaStage::find($stageId);
			
			$docMod->delete();

			$response['code'] = '200';
			$response['visaId'] = $data->document_id;
			
			echo json_encode($response);
		   exit;
		  
	 	
	}
}