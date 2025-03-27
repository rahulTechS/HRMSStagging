<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use App\Models\Company\Department;
use App\Models\Onboarding\TrainingType;
use App\Models\Onboarding\TrainingStages;
use Illuminate\Support\Facades\Validator;
use App\Models\Onboarding\TrainingUploadDocument;

use UserPermissionAuth;



class TrainingController extends Controller
{
    
       public function trainingType(Request $req)
	   {
			$trainingTypeListing =TrainingType::whereIn("status",array(1,2))->orderBy("id","DESC")->get();
			return view("Onboarding/Training/trainingType",compact('trainingTypeListing'));
	   }
	   
	   public function addTrainingType()
	   {
		   $departmentList =  Department::where("status",1)->get();
		   return view("Onboarding/Training/addTrainingType",compact('departmentList'));
	   }
	   public function addTrainingTypePost(Request $request)
	   {
		   //print_r($_FILES);
		   $imgArray=array();  
		   if(count($_FILES)>0){
		   $keys = array_keys($_FILES['document']['name']);
		 
		for($i=0; $i<count($_FILES['document']['name']); $i++){
		//$target_path = "TrainingDocument/";
		$target_path =public_path('TrainingDocument/');
		$ext = explode('.', basename( $_FILES['document']['name'][$i]));
		$target_path = $target_path ."T_".md5(uniqid()) . "." . $ext[count($ext)-1]; 
		//echo $target_path;exit;

		if(move_uploaded_file($_FILES['document']['tmp_name'][$i], $target_path)) {
			$imgArray[]="T_".md5(uniqid()) . "." . $ext[count($ext)-1];
		} else{
			
		}
		}
		   }
		
		   	$obj = new TrainingType();
			
			$obj->name = $request->input('name');
			$obj->status = $request->input('status');
			if($obj->save()){
			$tId = $obj->id;
			if(count($imgArray)>0){
			foreach($imgArray as $_img){
				$ImgObj= new TrainingUploadDocument();
				$ImgObj->training_id=$tId;
				$ImgObj->doc_name=$_img;
				$ImgObj->save();
			}
			}
			}
			$request->session()->flash('message','Traning Saved Successfully.');
			return redirect('trainingType');
	   }
	   
	   public function manageTrainingStages(Request $req)
	   {
		    $trainingID = $req->id;
			$trainingData = TrainingType::where("id",$trainingID)->first();
			$trainingStageList = TrainingStages::where('training_id',$trainingID)->where("status",1)->orWhere("status",2)->orderBy("stage_order","ASC")->get();
		   	return view("Onboarding/Training/manageTrainingStages",compact('trainingData','trainingStageList'));
	   }
	   public function addTrainingStagePostProcess(Request $req)
	   {
		   $trainingStageData = $req->input();
		 
		 $trainingId = $trainingStageData['trainingId'];
		 $trainingStageListCount =  TrainingStages::where("training_id",$trainingId)->where("status",'!=',3)->count();
		
		 $tStageModel = new TrainingStages();
		 $tStageModel->training_id = $trainingStageData['trainingId'];
		 $tStageModel->stage_name = $trainingStageData['stage_name'];
		 $tStageModel->stage_description = $trainingStageData['stage_description'];
		 $tStageModel->stage_order = $trainingStageListCount+1;
		
		 $tStageModel->status = $trainingStageData['status'];
		
		 $tStageModel->save();
		 return redirect('manageTrainingStages/'.$trainingId); 
	   }
	   
	   public function trainingStagesEditStart(Request $req)
	   {
		   $trainingId = $req->trainingId;
			$trainingStageData = TrainingStages::where("id",$trainingId)->first();
		
		return view('Onboarding/Training/TrainingStagesEditStart',compact('trainingStageData'));
	   }
	   
	   public function updateTrainingStagePostProcess(Request $req)
	   {
		      $trainingStageData = $req->input();
		 
		
		
		
		 $tStageModel = TrainingStages::find($trainingStageData['id']);
		
		 $tStageModel->stage_name = $trainingStageData['stage_name'];
		 $tStageModel->stage_description = $trainingStageData['stage_description'];
	
		
		 $tStageModel->status = $trainingStageData['status'];
		
		 $tStageModel->save();
			 
		 return back();
	   }
	   
	   public function deleteStartTraining(Request $req)
	   {
		   $tId = $req->trainingStageId;
	
		
		$trainingStageData = TrainingStages::where("id",$tId)->first();
		
		$stage_order = $trainingStageData->stage_order;
		$t_typeId = $trainingStageData->training_id;
		$arrangementModel = TrainingStages::where("training_id",$t_typeId)->where("stage_order",">",$stage_order)->where("status",'!=',3)->orderBy("stage_order","ASC")->get();
		
		foreach($arrangementModel as $_arrage)
		{
			
			$updateArragement = TrainingStages::find($_arrage->id);
			$updateArragement->stage_order = $stage_order;
			$updateArragement->save();
			$stage_order++;
		}
			$deleteOne = TrainingStages::find($tId);
			$deleteOne->status = 3;
			$deleteOne->save();
			 return back();
	   }
	   
	   
	   public function trainingStagesArrowUp(Request $req)
	   {
		    $beforeId =0;
		    $stageId = $req->trainingStageId;
			$typeId = $req->trainingId;
			$tStageData = TrainingStages::where("id",$stageId)->first();
			if($tStageData->stage_order != 1 && $tStageData->stage_order != '')
			{
				$currentSortOrder = $tStageData->stage_order;
				$sortOrderbefore = $currentSortOrder-1;
				/*
				*update Before Value
				*/
				$visaStageDatabefore = TrainingStages::where("stage_order",$sortOrderbefore)->where("training_id",$typeId)->first();
				$beforeId = $visaStageDatabefore->id;
				$beforeUpdateMod = TrainingStages::find($beforeId);
				$beforeUpdateMod->stage_order = $currentSortOrder;
				$beforeUpdateMod->save();
				/*
				*update Before Value
				*/
				$currentUpdateMod = TrainingStages::find($stageId);
				$currentUpdateMod->stage_order = $sortOrderbefore;
				$currentUpdateMod->save();
				
			}
			
			echo $beforeId;exit;
	   }
	   public function trainingStagesArrowDown(Request $req)
		{
			$afterId = 0;
			$stageId = $req->trainingStageId;
			$typeId = $req->trainingId;
			$visaStageDataCount = TrainingStages::where("training_id",$typeId)->where("status",array(1,2))->count();
			
			$visaStageData = TrainingStages::where("id",$stageId)->first();
			if($visaStageData->stage_order != $visaStageDataCount && $visaStageData->stage_order != '')
			{
				$currentSortOrder = $visaStageData->stage_order;
				$sortOrderafter = $currentSortOrder+1;
				/*
				*update Before Value
				*/
				$visaStageDataAfter = TrainingStages::where("stage_order",$sortOrderafter)->where("training_id",$typeId)->first();
				$afterId = $visaStageDataAfter->id;
				$afterUpdateMod = TrainingStages::find($afterId);
				$afterUpdateMod->stage_order = $currentSortOrder;
				$afterUpdateMod->save();
				/*
				*update Before Value
				*/
				$currentUpdateMod = TrainingStages::find($stageId);
				$currentUpdateMod->stage_order = $sortOrderafter;
				$currentUpdateMod->save();
				
			}
			
			echo $afterId;exit;
		}
		
		public function editTraining(Request $req)
		{
			$trainingId =  $req->trainingId;
			$traningModel = TrainingType::where("id",$trainingId)->first();
			 $departmentList =  Department::where("status",1)->get();
			 $uploadDoc=TrainingUploadDocument::where("training_id",$trainingId)->get();
			return view('Onboarding/Training/editTraining',compact('traningModel','departmentList','uploadDoc'));
		}
		
		public function editTrainingTypePost(Request $request)
		{
			$imgArray=array();
			if(count($_FILES)>0){
			$keys = array_keys($_FILES['document']['name']);
		   
		for($i=0; $i<count($_FILES['document']['name']); $i++){
		//$target_path = "TrainingDocument/";
		$target_path =public_path('TrainingDocument/');
		$ext = explode('.', basename( $_FILES['document']['name'][$i]));
		$target_path = $target_path ."T_".md5(uniqid()) . "." . $ext[count($ext)-1]; 
		//echo $target_path;exit;

		if(move_uploaded_file($_FILES['document']['tmp_name'][$i], $target_path)) {
			$imgArray[]="T_".md5(uniqid()) . "." . $ext[count($ext)-1];
		} else{
			
		}
		}
			}
			  $trainingData = $request->input();
			  $tId = $trainingData['id'];
			  $trainingMod =  TrainingType::find($tId);
			  
			  $trainingMod->name = $trainingData['name'];
			  $trainingMod->status = $trainingData['status'];
			  if($trainingMod->save()){
				  if(count($imgArray)>0){
				 foreach($imgArray as $_img){
				$ImgObj= new TrainingUploadDocument();
				$ImgObj->training_id=$tId;
				$ImgObj->doc_name=$_img;
				$ImgObj->save();
			}
			  }
			  }
			  
			  $request->session()->flash('message','Traning Updated Successfully.');
			  return redirect('trainingType');
		}
		
		public function deleteTraining(Request $request)
		{
			$trainingId =  $request->trainingId;
			 $trainingMod =  TrainingType::find($trainingId);
			 $trainingMod->status = 3;
			  $trainingMod->save();
			  $request->session()->flash('message','Traning Deleted Successfully.');
			  return redirect('trainingType');
		}
		public function TrainingExtraUploadFile(Request $request)
		{
		$index = $request->index;
		return view("Onboarding/Training/getExtraUpload",compact('index'));
		}
		public function DeleteImgTraining(Request $request){
			$TrainingId=$request->TrainingId;
			$tData =TrainingUploadDocument::where("id",$TrainingId)->first();
			$trainingData=$tData->training_id;
			
			$empattributes = TrainingUploadDocument::find($TrainingId);
			
			$empattributes->delete();
			$request->session()->flash('message','Document Deleted Successfully.');
			  return redirect("editTraining/$trainingData");
				
		}
	  
	   
}
