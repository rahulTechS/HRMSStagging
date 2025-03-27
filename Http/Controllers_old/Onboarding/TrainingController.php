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
use UserPermissionAuth;



class TrainingController extends Controller
{
    
       
	   public function trainingType()
		{
			
			return view("Onboarding/Training/TrainingType");
		}
		public function setOffSetInnerTrainingType(Request $request)
		{
			$offset = $request->offset;
			$request->session()->put('offset_training_filter',$offset);
		}
	   public function trainingTypeList(Request $request)
	   {
		   if(!empty($request->session()->get('offset_training_filter')))
				{
					$paginationValue = $request->session()->get('offset_training_filter');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				$selectedFilter['Attribute_name'] = '';
				$selectedFilter['Attribute_dept'] = '';
				if(!empty($request->session()->get('name_training_attribute_filter_inner_list')) && $request->session()->get('name_training_attribute_filter_inner_list') != 'All')
				{
					$name = $request->session()->get('name_training_attribute_filter_inner_list');
					 $selectedFilter['Attribute_name'] = $name;
					 if($whereraw == '')
					{
						$whereraw = 'name = "'.$name.'"';
					}
					else
					{
						$whereraw .= ' And name = "'.$name.'"';
					}
				}
				if(!empty($request->session()->get('dept_training_attribute_filter_inner_list')) && $request->session()->get('dept_training_attribute_filter_inner_list') != 'All')
				{
					$dept = $request->session()->get('dept_training_attribute_filter_inner_list');
					 $selectedFilter['Attribute_dept'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department_id = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department_id = "'.$dept.'"';
					}
				}

				$attributeNameArray = array();
				if($whereraw == '')
				{
				$name = TrainingType::whereIn("status",array(1,2))->get();
				}
				else
				{					
				$name = TrainingType::whereRaw($whereraw)->whereIn("status",array(1,2))->get();					
				}				
				foreach($name as $_name)
				{
					$attributeNameArray[$_name->name] = $_name->name;
				}
				$docattributeDptNameArray = array();
				if($whereraw == '')
				{
				$dept = TrainingType::whereIn("status",array(1,2))->get();
				}
				else
				{					
				$dept = TrainingType::whereRaw($whereraw)->whereIn("status",array(1,2))->get();					
				}				
				foreach($dept as $_dept)
				{
					$docattributeDptNameArray[$_dept->department_id] = $_dept->department_id;
				}
				if($whereraw != '')
				{
				
				$trainingTypeListing = TrainingType::whereRaw($whereraw)->whereIn("status",array(1,2))->orderBy("id","DESC")->paginate($paginationValue);
				$reportsCount = TrainingType::whereRaw($whereraw)->whereIn("status",array(1,2))->orderBy("id","DESC")->get()->count();
				}
				else{
			$trainingTypeListing =TrainingType::whereIn("status",array(1,2))->orderBy("id","DESC")->paginate($paginationValue);
			$reportsCount =TrainingType::whereIn("status",array(1,2))->orderBy("id","DESC")->get()->count();
				}
			return view("Onboarding/Training/TrainingTypeList",compact('trainingTypeListing','reportsCount','paginationValue','selectedFilter','attributeNameArray','docattributeDptNameArray'));
	   }
	   
	   public function addTrainingType()
	   {
		   $departmentList =  Department::where("status",1)->get();
		   return view("Onboarding/Training/AddTrainingType",compact('departmentList'));
	   }
	   public function addTrainingTypePost(Request $request)
	   {
		   	$obj = new TrainingType();
			$obj->department_id = $request->input('department_id');
			$obj->name = $request->input('name');
			$obj->status = $request->input('status');
			$obj->save();
			//$request->session()->flash('message','Traning Saved Successfully.');
			//return redirect('trainingType');
			$response['code'] = '200';
			$response['message'] = "Traning Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
			echo json_encode($response);
		   exit;
	   }
	   
	   public function manageTrainingStages(Request $req)
	   {
		    $trainingID = $req->id;
			$trainingData = TrainingType::where("id",$trainingID)->first();
			if(!empty($req->session()->get('offset_trainingtagetype_filter')))
				{
					$paginationValue = $req->session()->get('offset_trainingtagetype_filter');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				 
				 $selectedFilter['stage_name'] = '';
				 
				 $selectedFilter['stage_group'] = '';
				 
				 
				$stagenameArray = array();
				if($whereraw == '')
				{
				$stagetitle = TrainingStages::where('training_id',$trainingID)->whereIn("status",array(1,2))->get();
				
				}
				else
				{
					$stagetitle = TrainingStages::whereRaw($whereraw)->where('training_id',$trainingID)->whereIn("status",array(1,2))->get();
					
				}
				
				foreach($stagetitle as $_stagetitle)
				{
					//echo $_lname->last_name;exit;
					$stagenameArray[$_stagetitle->stage_name] = $_stagetitle->stage_name;
				}
				
				
				if(!empty($req->session()->get('stage_title_trainingtagetype_filter')) && $req->session()->get('stage_title_trainingtagetype_filter') != 'All')
				{
					$stage_name = $req->session()->get('stage_title_trainingtagetype_filter');
					 $selectedFilter['stage_name'] = $stage_name;
					 if($whereraw == '')
					{
						$whereraw = 'stage_name = "'.$stage_name.'"';
					}
					else
					{
						$whereraw .= ' And stage_name = "'.$stage_name.'"';
					}
				}
				
				if($whereraw != '')
				{
					$trainingStageList = TrainingStages::whereRaw($whereraw)->where('training_id',$trainingID)->whereIn("status",array(1,2))->paginate($paginationValue);
					//print_r($visaStageList);exit;
					$reportsCount = TrainingStages::whereRaw($whereraw)->where('training_id',$trainingID)->whereIn("status",array(1,2))->get()->count();
				}else{
					$trainingStageList = TrainingStages::where('training_id',$trainingID)->whereIn("status",array(1,2))->orderBy("stage_order","ASC")->paginate($paginationValue);
					$reportsCount=TrainingStages::where('training_id',$trainingID)->whereIn("status",array(1,2))->get()->count();		   	
				}
				return view("Onboarding/Training/ManageTrainingStages",compact('trainingData','trainingStageList','reportsCount','paginationValue','stagenameArray','selectedFilter'));
	   }
	   public function setFilterbyTrainingStagename(Request $request)
			{
				
				$stage_title = $request->stageName;
				$request->session()->put('stage_title_trainingtagetype_filter',$stage_title);
				 
			}
	   public function AddTrainingstagepopup($TrainingtypeId=NULL)
			{	
			$stage_group=TrainingStages::where('training_id',$TrainingtypeId)->whereIn("status",array(1,2))->get();
			return view("Onboarding/Training/PopupForm",compact('TrainingtypeId','stage_group'));
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
		 //return redirect('manageTrainingStages/'.$trainingId);
				$response['code'] = '200';
			   $response['message'] = "Data Saved Successfully.";
			   $response['trainingTypeId'] = $trainingId;
			   
				echo json_encode($response);
			   exit;		 
	   }
	   
	   public function trainingStagesEditStart(Request $req)
	   {
		   $trainingId = $req->trainingId;
			$trainingStageData = TrainingStages::where("id",$trainingId)->first();
		
		return view('Onboarding/Training/UpdatePopupForm',compact('trainingStageData'));
	   }
	   
	   public function updateTrainingStagePostProcess(Request $req)
	   {
		      $trainingStageData = $req->input();
		 
		
		
		 $tStageModel = TrainingStages::find($trainingStageData['id']);
		$trainingTypeId=$tStageModel->training_id;
		 $tStageModel->stage_name = $trainingStageData['stage_name'];
		 $tStageModel->stage_description = $trainingStageData['stage_description'];
	
		
		 $tStageModel->status = $trainingStageData['status'];
		
		 $tStageModel->save();
		 $response['code'] = '200';
		   $response['message'] = "Data update Successfully.";
		   $response['trainingTypeId'] = $trainingTypeId;
		   
			echo json_encode($response);
		   exit;
		 //return back();
	   }
	   
	   public function deleteStartTraining(Request $req)
	   {
		   $tId = $req->trainingStageId;
	
		
		$trainingStageData = TrainingStages::where("id",$tId)->first();
		$trainingTypeId=$trainingStageData->training_id;
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
			 //return back();
			$response['code'] = '200';
		   $response['message'] = "Data update Successfully.";
		   $response['trainingTypeId'] = $trainingTypeId;
		   
			echo json_encode($response);
		   exit;
	   }
	   public function setOffSetForTrainingStagedata(Request $request)
			{
				
				$offset = $request->offset;
				$TrainingtypeId=$request->TrainingtypeId;
				$request->session()->put('offset_trainingtagetype_filter',$offset);
				$request->session()->put('TrainingtypeId',$TrainingtypeId);
				 
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
			return view('Onboarding/Training/UpdateTrainingType',compact('traningModel','departmentList'));
		}
		
		public function editTrainingTypePost(Request $request)
		{
			  $trainingData = $request->input();
			  $tId = $trainingData['id'];
			  $trainingMod =  TrainingType::find($tId);
			  $trainingMod->department_id = $trainingData['department_id'];
			  $trainingMod->name = $trainingData['name'];
			  $trainingMod->status = $trainingData['status'];
			  $trainingMod->save();
			  //$request->session()->flash('message','Traning Updated Successfully.');
			  //return redirect('trainingType');
			  $response['code'] = '200';
			$response['message'] = "Traning Updated Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
			echo json_encode($response);
		   exit;
		}
		
		public function deleteTraining(Request $request)
		{
			$trainingId =  $request->trainingId;
			 $trainingMod =  TrainingType::find($trainingId);
			 $trainingMod->status = 3;
			  $trainingMod->save();
			  //$request->session()->flash('message','Traning Deleted Successfully.');
			  //return redirect('trainingType');
			  $response['code'] = '200';
			$response['message'] = "Traning Deleted Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
			echo json_encode($response);
		   exit;
		}
		public function filterBytrainingAttributeName(Request $request)
			{
				
				$name = $request->name;
				$request->session()->put('name_training_attribute_filter_inner_list',$name);	
			}
		
		public function filterBytrainingAttributeDptName(Request $request)
			{
				$dept = $request->dept;
				$request->session()->put('dept_training_attribute_filter_inner_list',$dept);	
			}
	  
	   
}
