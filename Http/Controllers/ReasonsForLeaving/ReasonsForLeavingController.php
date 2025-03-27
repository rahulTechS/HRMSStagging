<?php

namespace App\Http\Controllers\ReasonsForLeaving;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\Job\JobOpening;
use App\Models\Job\JobOpeningTarget;
use App\Models\JobFunction\JobFunction;
use App\Models\Company\Department;
use App\Models\Recruiter\Designation;
use App\Models\ReasonsForLeaving\ReasonsForLeaving;
use App\Models\TrainingCategory\EmpTraining;
use App\Models\DataCut\ENBDDataCutCards;
use App\Models\Employee\Employee_details;

class ReasonsForLeavingController extends Controller
{
    
	 public function ReasonsForLeaving(Request $req)
	   {
		  $filterList = array();
		  $filterList['name'] = '';
		  $filterList['status'] = '';
		  
		  $ReasonsForLeavingDetails = ReasonsForLeaving::orderBy("id","DESC")->whereIN("status",array(1,2));
		  
		  if(!empty($req->session()->get('name')))
			{
			
				$name = $req->session()->get('name');
				$filterList['name'] = $name;
				$ReasonsForLeavingDetails = $ReasonsForLeavingDetails->where("name","like","%".$name."%");
			}
		 
		 if(!empty($req->session()->get('status')))
			{
			
				$status = $req->session()->get('status');
				$filterList['status'] = $status;
				$ReasonsForLeavingDetails = $ReasonsForLeavingDetails->where("status",$status);
			}
			
		  $ReasonsForLeavingDetails = $ReasonsForLeavingDetails->get();
		  return view("ReasonsForLeaving/ReasonsForLeaving",compact('ReasonsForLeavingDetails','filterList'));
	   }
	   
	   public function addReasonsForLeaving()
	   {
		   return view("ReasonsForLeaving/addReasonsForLeaving");
	   }
	   
	   public function addReasonsForLeavingPost(Request $request)
	   {
		   //print_r($request->input());exit;
		   $parameterInput = $request->input();
		  //print_r($parameterInput);exit;
		   $ReasonsForLeavingMod = new ReasonsForLeaving();
			$ReasonsForLeavingMod->name = $parameterInput['ReasonsForLeaving']['name'];			
			$ReasonsForLeavingMod->status = $parameterInput['ReasonsForLeaving']['status'];
			$ReasonsForLeavingMod->save();
			$request->session()->flash('message','ReasonsForLeaving Saved.');
			return redirect('ReasonsForLeaving');
	   }
	   
	 
	   	   
	   public function updateReasonsForLeaving(Request $request)
	   {
		    $ReasonsForLeavingId = $request->id;
		    $ReasonsForLeavingDetails = ReasonsForLeaving::where("id",$ReasonsForLeavingId)->first();
			
			return view("ReasonsForLeaving/updateReasonsForLeaving",compact('ReasonsForLeavingDetails'));
	   }
	   
	   public function updateReasonsForLeavingPost(Request $request)
	   {
		   $parameterMeters = $request->input();
		  
		    $datas = $parameterMeters['ReasonsForLeaving'];
		    $ReasonsForLeavingUpdateMod = ReasonsForLeaving::find($datas['id']);
		    $ReasonsForLeavingUpdateMod->name = $datas['name'];
		    $ReasonsForLeavingUpdateMod->status = $datas['status'];
			$ReasonsForLeavingUpdateMod->save();
			
			$request->session()->flash('message','ReasonsForLeaving Updated.');
			return redirect('ReasonsForLeaving');
	   }
	   
	   public function deleteReasonsForLeaving(Request $request)
	   {
		     $ReasonsForLeavingId = $request->id;
			 $ReasonsForLeavingUpdateMod = ReasonsForLeaving::find($ReasonsForLeavingId);
			 $ReasonsForLeavingUpdateMod->status = 3;
			 $ReasonsForLeavingUpdateMod->save();
			 $request->session()->flash('message','ReasonsForLeaving Deleted.');
			 return redirect('ReasonsForLeaving');
	   }
	   
	   public function appliedFilterOnReasonsForLeaving(Request $request)
	   {
		   $selectedFilter = $request->input();		
		   $request->session()->put('name',$selectedFilter['name']);
		   $request->session()->put('status',$selectedFilter['status']);
		   return redirect('ReasonsForLeaving');
	   }
	   
	   public function resetReasonsForLeavingFilter(Request $request)
	   {
		  	
		   $request->session()->put('name',"");
		   
		   $request->session()->put('status',"");
		   return redirect('ReasonsForLeaving');
	   }
	   
}
