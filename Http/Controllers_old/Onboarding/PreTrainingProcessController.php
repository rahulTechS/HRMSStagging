<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee\Employee_details;
use App\Models\Onboarding\TrainingType;
use App\Models\Onboarding\TrainingStages;
use App\Models\Onboarding\TrainingProcess;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentUploadTrainingStage;
class PreTrainingProcessController extends Controller
{
 
	public function preTrainingProcess (Request $req)
	{
		
		$documentCollectionId = $req->documentCollectionId;
		
		$documentCollectionData = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		
		
		$result = array();
		$result['empDetail'] = $documentCollectionData;
		/*
		*getting Visa Type List
		*/
		$departmentId = $documentCollectionData->department;
		$trainingTypeList = TrainingType::where("status",1)->where("department_id",$departmentId)->orderBy("id","DESC")->get();
		$result['trainingTypeList'] = $trainingTypeList;
		/*
		*getting Visa Type List
		*/
		/*
		*checking Visa Process Status for employee
		*Start Code
		*/
		$trainingProcessLists = TrainingProcess::where("document_id",$documentCollectionId)->orderBy('id','DESC')->get();
		
		/*
		*checking Visa Process Status for employee
		*End Code
		*/
		return view("Onboarding/Training/preTrainingProcess",compact('result'),compact('trainingProcessLists'));
	}
	public function setPreTrainingStage($trainingId = NULL,$documentCollectionId = NULL)
	{
		
		$trainingStageLists = TrainingStages::where("training_id",$trainingId)->where("status",1)->orderBy("stage_order","ASC")->get();
		return view("Onboarding/Training/setPreTrainingStage",compact('trainingStageLists','documentCollectionId','trainingId'));
	}
	
	public function PreTrainingPost(Request $req)
	{
		$requestData = $req->input();
		
		$visaprocessObj = new TrainingProcess();
		$visaprocessObj->document_id = $requestData['document_id'];
		$visaprocessObj->training_id = $requestData['trainingId'];
		$visaprocessObj->training_stageId = $requestData['trainingStage'];
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
		
		$req->session()->flash('message','Training Process setup for request.');
        return back();
	}
	public function trainingPreProcessPost(Request $req)
	{
		$requestData = $req->input();
		/* echo '<pre>';
		print_r($requestData);
		exit; */
		$tprocessObj = TrainingProcess::find($requestData['training_process_id']);
		
		$tprocessObj->final_comment = $requestData['final_comment'];
		$tprocessObj->stage_staus = $requestData['stage_staus'];
		if($requestData['stage_staus'] == 2)
		{
			$tprocessObj->cancel_status = 1;
		}
		else
		{
			$tprocessObj->cancel_status = 2;
		}
		$tprocessObj->closing_date = date("Y-m-d");
		$tprocessObj->save();
		
		/*
		*upload file if exist
		*/
		//DocumentUploadVisaStage
		$num = $tprocessObj->id;
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
			    $newFileName = 'TrainingDocument-'.$key.'-'.$num.'.'.$fileExtension;
			   
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
			$documentfortrainingStageMod = new DocumentUploadTrainingStage();
			$documentfortrainingStageMod->trainingprocess_id = $tprocessObj->id;
			$documentfortrainingStageMod->file_name = $value;
			$documentfortrainingStageMod->save();
			}
			
		/*
		*upload file if exist
		*/
		
		$req->session()->flash('message','Training Process setup for Request.');
        return back();
	}
	public static function getStatusOfStage($documentId,$stageId,$typeId)
	   {
		  $tProcessDetail =  TrainingProcess::where("document_id",$documentId)->where("training_stageId",$stageId)->where("training_id",$typeId)->first();
		  if($tProcessDetail != '')
		  {
		  if( $tProcessDetail->stage_staus == 1)
		  {
			  return "class-inprogress";
		  }
		  else if($tProcessDetail->stage_staus == 2)
		  {
			  return "class-completed";
		  }
		  else if($tProcessDetail->stage_staus == 3)
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
		  $tProcessDetail =  TrainingProcess::where("document_id",$documentId)->where("training_id",$typeId)->orderBy("id","DESC")->first();
		 
		  if($tProcessDetail != '')
		  {
			   if($tProcessDetail->stage_staus == 1)
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
	   
	    public static function getTrainingProcessId($documentId,$stageId,$typeId)
		   {
			   $tProcessDetail =  TrainingProcess::where("document_id",$documentId)->where("training_stageId",$stageId)->where("training_id",$typeId)->first();
			 
			  return  $tProcessDetail->id;
			 
		   }
	   public static function detailedTrainingStage($documentId,$stageId,$typeId)
		   {
			   $tProcessDetail =  TrainingProcess::where("document_id",$documentId)->where("training_stageId",$stageId)->where("training_id",$typeId)->first();
			 
			  return  $tProcessDetail;
			 
		   }
		   
      public static function progressDetails($documentId,$typeId)
		   {
			   $trainingProcessDetail =  TrainingProcess::where("document_id",$documentId)->where("training_id",$typeId)->orderBy("id","ASC")->first();
			    $progressArray = array();
			   if($trainingProcessDetail != '')
			   {
			  
			   
			   $initiatedDate = $trainingProcessDetail->created_at;
			   $now = date("Y-m-d");
			 
			   $datediff = strtotime($now) - strtotime($initiatedDate);

				$days =   round($datediff / (60 * 60 * 24));
				
				$progressArray['initiatedDate'] = date("d M Y",strtotime($initiatedDate));
				$progressArray['days'] = $days;

				$tProcessDetailCurrentStage =  TrainingProcess::where("document_id",$documentId)->where("training_id",$typeId)->orderBy("id","DESC")->first();
				$tStageIdF = $tProcessDetailCurrentStage->training_stageId;
				$tStageD = TrainingStages::where("id",$tStageIdF)->first();
				$progressArray['currentStage'] = $tStageD->stage_name;
				
				/*
				*total Cost and percentage work
				*/
				/* echo $documentId;
				echo '<br />';
				echo $typeId;
				exit; */
				
				
				/*
				*total Cost and percentage work
				*/
				/*
				*percentage work
				*/
				$lastStageCompleted =  TrainingProcess::where("document_id",$documentId)->where("training_id",$typeId)->where("stage_staus",2)->orderBy("id","DESC")->first();
				if($lastStageCompleted != '')
				{
				 $tStageId = $lastStageCompleted->training_stageId;
				$tStageD = TrainingStages::where("id",$tStageId)->first();
				$completedStageOrder = $tStageD->stage_order;
				
				$totalStagesCount = TrainingStages::where("training_id",$typeId)->where("status",1)->count();
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
		   
	public function preTrainingCompletePost(Request $req)
	{
		$requestData = $req->input();
		
		
		$tprocessObj = new TrainingProcess();
		$tprocessObj->document_id = $requestData['document_id'];
		$tprocessObj->training_id = $requestData['training_id'];
		$tprocessObj->training_stageId = $requestData['training_stage'];
		$tprocessObj->comment = $requestData['comment'];
		$tprocessObj->final_comment = $requestData['final_comment'];

		$tprocessObj->stage_staus = 2;
		$tprocessObj->closing_date = date("Y-m-d");
		$tprocessObj->save();
		/*
		*update Document Collection visa status
		*start code
		*/
		/* 	$docId = $requestData['document_id'];
			$_documentCollectionMod = DocumentUploadTrainingStage::find($docId);
			$_documentCollectionMod->visa_process_status = 2;
			$_documentCollectionMod->save(); */
		/*
		*update Document Collection visa status
		*end code
		*/
		
		/*
		*upload file if exist
		*/
		//DocumentUploadVisaStage
		$num = $tprocessObj->id;
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
			    $newFileName = 'TrainingDocument-'.$key.'-'.$num.'.'.$fileExtension;
			   
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
				$documentfortrainingStageMod = new DocumentUploadTrainingStage();
				$documentfortrainingStageMod->trainingprocess_id = $tprocessObj->id;
				$documentfortrainingStageMod->file_name = $value;
				$documentfortrainingStageMod->save();
			}
			
		/*
		*upload file if exist
		*/
		$req->session()->flash('message','Training Process setup for Request.');
        return back();
	}
	
	public function updateDocPostTraining(Request $req)
	{
		$requestData = $req->input();
		$removeDocs = $requestData['removeDoc'];
			foreach($removeDocs as $rowId=>$value)
			{
				if($value == 2)
				{
					$documentDatas = DocumentUploadTrainingStage::where("id",$rowId)->first();
					 unlink(public_path('documentCollectionFiles/'.$documentDatas->file_name));
					DocumentUploadTrainingStage::find($rowId)->delete();
				}
			}
			
			//DocumentUploadVisaStage
		$num = $requestData['trainingIdName'];
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
			    $newFileName = 'TrainingDocument-'.$key.'-'.$num.'-'.time().'.'.$fileExtension;
			   
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
				$documentfortrainingStageMod = new DocumentUploadTrainingStage();
				$documentfortrainingStageMod->trainingprocess_id = $requestData['trainingIdName'];
				$documentfortrainingStageMod->file_name = $value;
				$documentfortrainingStageMod->save();
			}
			
		/*
		*upload file if exist
		*/
		$req->session()->flash('message','Training document updated.');
        return back();
	}
	
	public static function getTrainingName($typeId)
	{
		$tTypeDetails = TrainingType::where("id",$typeId)->first();
		return $tTypeDetails->name;
	}
	
	public static function rollBackTraining(Request $request)
	{
		$documentCollectionId = $request->docId;
		$typeId = $request->trainingId;
		$trainingProcessLists = TrainingProcess::where("document_id",$documentCollectionId)->where("training_id",$typeId)->get();
		/* echo '<pre>';
		print_r($trainingProcessLists);
		exit; */
		foreach($trainingProcessLists as $_tP)
		{
			$tProcessDeleteMod = TrainingProcess::find($_tP->id);
			$tProcessDeleteMod->delete();
		}
		$request->session()->flash('success','Successfully rollback.');
        return back();
	}
	public function getExtraUpload(Request $request)
	{
		$index = $request->index;
		return view("preVisaProcess/getExtraUpload",compact('index'));
	}
	public function getExtraUploadNew(Request $request)
	{
		$index = $request->index;
		return view("preVisaProcess/getExtraUploadNew",compact('index'));
	}
	
	public static function documentCounting($id)
	{
		$documentCount = DocumentUploadTrainingStage::where("trainingprocess_id",$id)->count();
		return $documentCount;
	}
	
	public function getFilesForTraining(Request $request)
	{
		$processId = $request->processId;
		$documentlist = DocumentUploadTrainingStage::where("trainingprocess_id",$processId)->get();
		return view("Onboarding/Training/getFilesForTraining",compact('documentlist'));
	}
	
	public function empDemo()
	{
		return view("Onboarding/Training/empDemo");
	}
	
	public function checkForTrainingComplete(Request $request)
	{
		$docId = $request->docId;
		$trainingId = $request->trainingId;
		$trainingProcessLists = TrainingProcess::where("document_id",$docId)->where("training_id",$trainingId)->orderBy("id","DESC")->first();
		return view("Onboarding/Training/checkForTrainingComplete",compact('trainingProcessLists'));
	}
	
	public function completeTrainingProcess(Request $request)
	{
		$docId = $request->docId;
		$docMod = DocumentCollectionDetails::find($docId);
		$docMod->training_process_status = 4;
		$docMod->save();
		
		return redirect('documentcollectionAjax?id='.$docId.'&type=training');
	}
	
	public static function getFinalRequesttStatus($id)
	{
		return DocumentCollectionDetails::where("id",$id)->first()->training_process_status;
	}
	
	public function getExtraUploadUpdateTraining(Request $request)
	{
		$index = $request->index;
		$processId = $request->processId;
		$documents = DocumentUploadTrainingStage::where("trainingprocess_id",$processId)->get();
		return view("Onboarding/Training/getExtraUploadUpdateTraining",compact('index','documents'));
	}
	
	
	
	
}