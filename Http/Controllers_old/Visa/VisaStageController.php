<?php

namespace App\Http\Controllers\Visa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visa\VisaStage;
use App\Models\Visa\VisaStageGroup;
use App\Models\Visa\visaType;
use App\Models\Visa\VisaTimeContraint;
use Session;

class VisaStageController extends Controller
{
    public function listing()
	{
		$visaStagingMods = VisaStage::orderBy('id','DESC')->where("visa_stages.status",1)->orWhere("visa_stages.status",2)->select('visa_stages.*','visa_type.title')->join("visa_type","visa_type.id","=","visa_stages.visa_type")->get();
		return view('Visa/listVisaStage',compact('visaStagingMods'));
	}
	public static function getStageGroupName($stage_group)
		{
			$visastagename = VisaStageGroup::where("id",$stage_group)->first();

			return  $visastagename->group_name;
		}
	public function addVisaStage()
	{
		$visaTypeList = visaType::where("status",1)->orderBy('id','DESC')->get();
		return view('Visa/addVisaStage',compact('visaTypeList'));
	}
	
	public function addVisaStagePost(Request $req)
	{
		$obj = new VisaStage();
		$obj->visa_type = $req->input('visa_type');
		$obj->stage_name = $req->input('stage_name');
		$obj->stage_description = $req->input('stage_description');
		$obj->cost = $req->input('cost');
		$obj->stage_order = $req->input('stage_order');
		$obj->status = $req->input('status');
		$obj->save();
		$req->session()->flash('message','Visa Stage Saved Successfully.');
        return redirect('visaStages');
	}
	public function editVisaStage($id)
	{
		$objVisaStages = VisaStage::where('id',$id)->first();
		$visaTypeList = visaType::where("status",1)->orderBy('id','DESC')->get();
		return view('Visa/editVisaStage',compact('objVisaStages'),compact('visaTypeList'));
		
	}
	public function updateVisaStagePost(Request $req)
	{
		
		$obj = VisaStage::find($req->input('id'));
		$obj->visa_type = $req->input('visa_type');
		$obj->stage_name = $req->input('stage_name');
		$obj->stage_description = $req->input('stage_description');
		$obj->cost = $req->input('cost');
		$obj->stage_order = $req->input('stage_order');
		$obj->status = $req->input('status');
		$obj->save();
		$req->session()->flash('message','Visa Stage Updated Successfully.');
        return redirect('visaStages');
	}
	public function visaTimeContraint()
	{
		$VisaTimeContraintDetails = VisaTimeContraint::where("status",1)->orWhere("status",2)->orderBy('id','DESC')->get();
		return view('Visa/listVisaTimeContraint',compact('VisaTimeContraintDetails'));
	}
	public function addVisaTimeContraint()
	{
		$visaTypeList = visaType::where("status",1)->orderBy('id','DESC')->get();
		$visaStaginggetting = VisaStage::where("status",1)->orderBy('id','DESC')->get();
		return view('Visa/addVisaTimeContraint',compact('visaStaginggetting'),compact('visaTypeList'));
	}
	
	public function addStageTimeContraintPost(Request $req)
	{
		$visaTimeContraintObj = new VisaTimeContraint();
		$visaTimeContraintObj->from_stageId = $req->input("from_stageId");
		$visaTimeContraintObj->visa_type = $req->input("visa_type");
		$visaTimeContraintObj->to_stageId = $req->input("to_stageId");
		$visaTimeContraintObj->days_to_finish = $req->input("days_to_finish");
		$visaTimeContraintObj->status = $req->input("status");
		$visaTimeContraintObj->save();
		$req->session()->flash('message','Visa Stage Time Contraint Added Successfully.');
        return redirect('visaStagesTimeContraint');
	}
	public function editVisaTimeContraint($id)
	{
		$visaTypeList = visaType::where("status",1)->orderBy('id','DESC')->get();
		$visaStaginggetting = VisaStage::where("status",1)->orderBy('id','DESC')->get();
		$result = array();
		$result['visaStaginggetting'] = $visaStaginggetting;
		$result['visaTypeList'] = $visaTypeList;
		$visaTimeContraintList = VisaTimeContraint::where('id',$id)->first();
		return view('Visa/editVisaTimeContraint',compact('visaTimeContraintList'),compact('result'));
	}
	public function updateStageTimeContraintPost(Request $req)
	{
		$visaTimeContraintObj = VisaTimeContraint::find($req->input('id'));
		$visaTimeContraintObj->from_stageId = $req->input("from_stageId");
		$visaTimeContraintObj->visa_type = $req->input("visa_type");
		$visaTimeContraintObj->to_stageId = $req->input("to_stageId");
		$visaTimeContraintObj->days_to_finish = $req->input("days_to_finish");
		$visaTimeContraintObj->status = $req->input("status");
		$visaTimeContraintObj->save();
		$req->session()->flash('message','Visa Stage Time Contraint Updated Successfully.');
        return redirect('visaStagesTimeContraint');
	}
	public function getStageAsPerType($id)
	{
		$visaStageList = VisaStage::where('visa_type',$id)->get();
		return view('Visa/getStageAsPerType',compact('visaStageList'));
	}
	public function editStageAsPerType($id,$timeId,$behave)
	{
		$visaTimeContraintList = VisaTimeContraint::where('id',$timeId)->first();
		$visaStageList = VisaStage::where('visa_type',$id)->get();
		if($behave == 'from')
		{
			$selectedValue = $visaTimeContraintList->from_stageId;
		}
		else
		{
			$selectedValue = $visaTimeContraintList->to_stageId;
		}
		return view('Visa/editStageAsPerType',compact('visaStageList'),compact('selectedValue'));
	}
	public function deleteVisaStage(Request $req)
	{
		$visaStatus_obj = VisaStage::find($req->id);
       
        $visaStatus_obj->status = 3;
       
        $visaStatus_obj->save();
        $req->session()->flash('message','Visa Stage deleted Successfully.');
        return redirect('visaStages');
	}
	
	public function deleteVisaTimeContraint(Request $req)
	{
		$visaTimeContraint_obj = VisaTimeContraint::find($req->id);
       
        $visaTimeContraint_obj->status = 3;
       
        $visaTimeContraint_obj->save();
        $req->session()->flash('message','Visa Time Contraint deleted Successfully.');
        return redirect('visaStagesTimeContraint');
	}
	
	public function setOffSetForVisaStagedata(Request $request)
			{
				
				$offset = $request->offset;
				$VisatypeId=$request->VisatypeId;
				$request->session()->put('offset_visastagetype_filter',$offset);
				$request->session()->put('VisatypeId_visastage_filter',$VisatypeId);
				 return  redirect('manageVisaStages/'.$VisatypeId);
				 
			}
			public function setFilterbyStagename(Request $request)
			{
				
				$stage_title = $request->stageName;
				$VisatypeId=$request->VisatypeId;
				$request->session()->put('stage_title_visastagetype_filter',$stage_title);
				 return  redirect('manageVisaStages/'.$VisatypeId);
				 
			}
			public function setFilterbyGroup(Request $request)
			{
				
				$group = $request->group;
				$VisatypeId=$request->VisatypeId;
				$request->session()->put('stage_group_visastagetype_filter',$group);
				 return  redirect('manageVisaStages/'.$VisatypeId);
				 
			}
	public function manageVisaStages(Request $req)
	{
		if(!empty($req->session()->get('offset_visastagetype_filter')))
				{
					$paginationValue = $req->session()->get('offset_visastagetype_filter');
				}
				else
				{
					$paginationValue = 10;
				}
				//echo $paginationValue;//exit;
				if(!empty($req->session()->get('VisatypeId_visastage_filter')))
				{
					$visaTypeId = $req->session()->get('VisatypeId_visastage_filter');
				}
				else
				{
					$visaTypeId = $req->visaTypeId;
				}
				$whereraw='';
				$whereraw1 = '';
				 
				 $selectedFilter['stage_name'] = '';
				 
				 $selectedFilter['stage_group'] = '';
				 
				 
				$stagenameArray = array();
				if($whereraw == '')
				{
				$stagetitle = VisaStage::where('visa_type',$visaTypeId)->whereIn("status",array(1,2))->get();
				
				}
				else
				{
					$stagetitle = VisaStage::whereRaw($whereraw)->where('visa_type',$visaTypeId)->whereIn("status",array(1,2))->get();
					
				}
				
				foreach($stagetitle as $_stagetitle)
				{
					//echo $_lname->last_name;exit;
					$stagenameArray[$_stagetitle->stage_name] = $_stagetitle->stage_name;
				}
				
				
				if(!empty($req->session()->get('stage_title_visastagetype_filter')) && $req->session()->get('stage_title_visastagetype_filter') != 'All')
				{
					$stage_name = $req->session()->get('stage_title_visastagetype_filter');
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
				
				//echo $group;exit;
		//echo $visaTypeId;exit;
		$visaTypeData = visaType::where("id",$visaTypeId)->first();
		if($whereraw != '')
				{
					$visaStageList = VisaStage::whereRaw($whereraw)->where('visa_type',$visaTypeId)->whereIn("status",array(1,2))->paginate($paginationValue);
					//print_r($visaStageList);exit;
					$reportsCount = VisaStage::whereRaw($whereraw)->where('visa_type',$visaTypeId)->whereIn("status",array(1,2))->get()->count();
				}else{
					$visaStageList = VisaStage::where('visa_type',$visaTypeId)->whereIn("status",array(1,2))->paginate($paginationValue);
					//print_r($visaStageList);exit;
					$reportsCount = VisaStage::where('visa_type',$visaTypeId)->whereIn("status",array(1,2))->get()->count();
				}	
//print_r($visaStageList);exit;				
		return view('Visa/manageVisaStages',compact('visaTypeData','visaStageList','paginationValue','reportsCount','visaTypeId','selectedFilter','stagenameArray'));
	}
	public function Addstagepopup($VisatypeId=NULL)
			{	
			$stage_group=VisaStageGroup::where("status",1)->get();
			return view("Visa/PopupForm",compact('VisatypeId','stage_group'));
			}
	public function addVisaStagePostProcess(Request $req)
	{
		 $visaStageData = $req->input();
		
		 $visaTypeId = $visaStageData['visaTypeId'];
		 $visaStageListCount =  VisaStage::where("visa_type",$visaTypeId)->where("status",'!=',3)->count();
		
		 $visaStageModel = new VisaStage();
		 $visaStageModel->visa_type = $visaStageData['visaTypeId'];
		 $visaStageModel->stage_name = $visaStageData['stage_name'];
		 $visaStageModel->stage_description = $visaStageData['stage_description'];
		 $visaStageModel->stage_order = $visaStageListCount+1;
		 $visaStageModel->cost = $visaStageData['cost'];
		 $visaStageModel->status = $visaStageData['status'];
		 if(isset($visaStageData['allow_upload']))
		 {
			 $visaStageModel->allow_upload = 1; 
		 }
		 else
		 {
			  $visaStageModel->allow_upload = 0; 
		 }
		 $visaStageModel->save();
		 //return redirect('manageVisaStages/'.$visaTypeId);
				$response['code'] = '200';
			   $response['message'] = "Data Saved Successfully.";
			   $response['visaTypeId'] = $visaTypeId;
			   
				echo json_encode($response);
			   exit;
	}
	public function Updatestagepopup($VisatypeId=NULL)
			{	
			//echo $VisatypeId
			$stage_group=VisaStageGroup::where("status",1)->get();
			$visaStageData = VisaStage::where("id",$VisatypeId)->first();
			return view("Visa/UpdatePopupForm",compact('visaStageData','VisatypeId','stage_group'));
			}
	
	/*public function visaStagesLists(Request $req)
	{
		if(!empty(req->visaTypeId)){
		echo $req->visaTypeId;
		exit;
		}
		else{echo "";}
	}*/
	
	public function visaStagesArrowUp(Request $req)
	{
		$beforeId =0;
		$stageId = $req->visaStageId;
		$visaTypeId = $req->visaTypeId;
		$visaStageData = VisaStage::where("id",$stageId)->first();
		if($visaStageData->stage_order != 1 && $visaStageData->stage_order != '')
		{
			$currentSortOrder = $visaStageData->stage_order;
			$sortOrderbefore = $currentSortOrder-1;
			/*
			*update Before Value
			*/
			$visaStageDatabefore = VisaStage::where("stage_order",$sortOrderbefore)->where("visa_type",$visaTypeId)->first();
			$beforeId = $visaStageDatabefore->id;
			$beforeUpdateMod = VisaStage::find($beforeId);
			$beforeUpdateMod->stage_order = $currentSortOrder;
			$beforeUpdateMod->save();
			/*
			*update Before Value
			*/
			$currentUpdateMod = VisaStage::find($stageId);
			$currentUpdateMod->stage_order = $sortOrderbefore;
			$currentUpdateMod->save();
			
		}
		$visaStageList = VisaStage::where('visa_type',$visaTypeId)->where("status",array(1,2))->orderBy("stage_order","ASC")->get();
		echo $beforeId;exit;
	}
	
	
	public function visaStagesArrowDown(Request $req)
	{
		$afterId =0;
		$stageId = $req->visaStageId;
		$visaTypeId = $req->visaTypeId;
		$visaStageDataCount = VisaStage::where("visa_type",$visaTypeId)->where("status",array(1,2))->count();
		$visaStageData = VisaStage::where("id",$stageId)->first();
		if($visaStageData->stage_order != $visaStageDataCount && $visaStageData->stage_order != '')
		{
			$currentSortOrder = $visaStageData->stage_order;
			$sortOrderafter = $currentSortOrder+1;
			/*
			*update Before Value
			*/
			$visaStageDataAfter = VisaStage::where("stage_order",$sortOrderafter)->where("visa_type",$visaTypeId)->first();
			$afterId = $visaStageDataAfter->id;
			$afterUpdateMod = VisaStage::find($afterId);
			$afterUpdateMod->stage_order = $currentSortOrder;
			$afterUpdateMod->save();
			/*
			*update Before Value
			*/
			$currentUpdateMod = VisaStage::find($stageId);
			$currentUpdateMod->stage_order = $sortOrderafter;
			$currentUpdateMod->save();
			
		}
		$visaStageList = VisaStage::where('visa_type',$visaTypeId)->where("status",array(1,2))->orderBy("stage_order","ASC")->get();
		echo $afterId;exit;
	}
	
	public function visaStagesEditStart(Request $req)
	{
		$stageId = $req->visaStageId;
		$visaStageData = VisaStage::where("id",$stageId)->first();
		return view('Visa/visaStagesEditStart',compact('visaStageData'));
	}
	
	public function updateVisaStagePostProcess(Request $req)
	{
		 $visaStageData = $req->input();
		
		 $id = $visaStageData['id'];
		 $visaStageUpdate =  VisaStage::find($id);
		 $visaTypeId=$visaStageUpdate->visa_type;
		 $visaStageUpdate->stage_name = $visaStageData['stage_name'];
		 $visaStageUpdate->stage_description = $visaStageData['stage_description'];
		 $visaStageUpdate->cost = $visaStageData['cost'];
		 $visaStageUpdate->status = $visaStageData['status'];
		 if(isset($visaStageData['allow_upload']))
		 {
			 $visaStageUpdate->allow_upload = 1; 
		 }
		 else
		 {
			  $visaStageUpdate->allow_upload = 0; 
		 }
		 $visaStageUpdate->save();
		$response['code'] = '200';
		   $response['message'] = "Data Saved Successfully.";
		   $response['visaTypeId'] = $visaTypeId;
		   
			echo json_encode($response);
		   exit;
		 //return back();
	}
	
	public function deleteStart(Request $req)
	{
		//echo "hello";exit;
		$stageId = $req->visaStageId;
	
		
		$visaStageData = VisaStage::where("id",$stageId)->first();
		$visaTypeId=$visaStageData->visa_type;
		$stage_order = $visaStageData->stage_order;
		$visa_typeId = $visaStageData->visa_type;
		$arrangementModel = VisaStage::where("visa_type",$visa_typeId)->where("stage_order",">",$stage_order)->where("status",'!=',3)->orderBy("stage_order","ASC")->get();
		
		foreach($arrangementModel as $_arrage)
		{
			
			$updateArragement = VisaStage::find($_arrage->id);
			$updateArragement->stage_order = $stage_order;
			$updateArragement->save();
			$stage_order++;
		}
		$deleteOne = VisaStage::find($stageId);
		$deleteOne->status = 3;
		$deleteOne->save();
		 $response['code'] = '200';
		   $response['message'] = "Data Saved Successfully.";
		   $response['visaTypeId'] = $visaTypeId;
		   
			echo json_encode($response);
		   exit;
	}
}