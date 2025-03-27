<?php

namespace App\Http\Controllers\TrainingtAttribute;

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
use App\Models\TrainingtAttribute\TrainingtAttribute;
use App\Models\TrainingCategory\EmpTraining;
use App\Models\DataCut\ENBDDataCutCards;
use App\Models\Employee\Employee_details;

class TrainingtAttributeController extends Controller
{
    
	 public function TrainingtAttribute(Request $req)
	   {
		  $filterList = array();
		  $filterList['name'] = '';
		  $filterList['status'] = '';
		  
		  $TrainingtAttributeDetails = TrainingtAttribute::orderBy("id","DESC")->where("status",1);
		  
		  if(!empty($req->session()->get('name')))
			{
			
				$name = $req->session()->get('name');
				$filterList['name'] = $name;
				$TrainingtAttributeDetails = $TrainingtAttributeDetails->where("name","like","%".$name."%");
			}
		 
		 if(!empty($req->session()->get('status')))
			{
			
				$status = $req->session()->get('status');
				$filterList['status'] = $status;
				$TrainingtAttributeDetails = $TrainingtAttributeDetails->where("status",$status);
			}
			
		  $TrainingtAttributeDetails = $TrainingtAttributeDetails->get();
		  return view("TrainingtAttribute/TrainingtAttribute",compact('TrainingtAttributeDetails','filterList'));
	   }
	   
	   public function addTrainingtAttribute()
	   {
		   return view("TrainingtAttribute/addTrainingtAttribute");
	   }
	   
	   public function addTrainingtAttributePost(Request $request)
	   {
		   //print_r($request->input());exit;
		   $parameterInput = $request->input();
		  //print_r($parameterInput);exit;
		   $jobOpeningMod = new TrainingtAttribute();
			$jobOpeningMod->name = $parameterInput['TrainingtAttribute']['name'];	
			$jobOpeningMod->code = $parameterInput['TrainingtAttribute']['code'];	
			$jobOpeningMod->attribute_type = $parameterInput['TrainingtAttribute']['attribute_type'];				
			$jobOpeningMod->status = $parameterInput['TrainingtAttribute']['status'];
			$jobOpeningMod->save();
			$request->session()->flash('message','Attribute Saved.');
			return redirect('TrainingtAttribute');
	   }
	   
	 
	   	   
	   public function updateTrainingtAttribute(Request $request)
	   {
		    $TrainingtAttributeId = $request->id;
		    $TrainingtAttributeDetails = TrainingtAttribute::where("id",$TrainingtAttributeId)->first();
			
			return view("TrainingtAttribute/updateTrainingtAttribute",compact('TrainingtAttributeDetails'));
	   }
	   
	   public function updateTrainingtAttributePost(Request $request)
	   {
		   $parameterMeters = $request->input();
		  
		    $datas = $parameterMeters['TrainingtAttribute'];
		    $TrainingtAttributeUpdateMod = TrainingtAttribute::find($datas['id']);
		    $TrainingtAttributeUpdateMod->name = $datas['name'];
			$TrainingtAttributeUpdateMod->code = $datas['code'];
			$TrainingtAttributeUpdateMod->attribute_type = $datas['attribute_type'];
		    $TrainingtAttributeUpdateMod->status = $datas['status'];
			$TrainingtAttributeUpdateMod->save();
			
			$request->session()->flash('message','Attribute Updated.');
			return redirect('TrainingtAttribute');
	   }
	   
	   public function deleteTrainingtAttribute(Request $request)
	   {
		     $TrainingtAttributeId = $request->id;
			 $TrainingtAttributeUpdateMod = TrainingtAttribute::find($TrainingtAttributeId);
			 $TrainingtAttributeUpdateMod->status = 3;
			 $TrainingtAttributeUpdateMod->save();
			 $request->session()->flash('message','Attribute Deleted.');
			 return redirect('TrainingtAttribute');
	   }
	   
	   public function appliedFilterOnTrainingtAttribute(Request $request)
	   {
		   $selectedFilter = $request->input();		
		   $request->session()->put('name',$selectedFilter['name']);
		   $request->session()->put('status',$selectedFilter['status']);
		   return redirect('TrainingtAttribute');
	   }
	   
	   public function resetTrainingtAttributeFilter(Request $request)
	   {
		  	
		   $request->session()->put('name',"");
		   
		   $request->session()->put('status',"");
		   return redirect('TrainingtAttribute');
	   }
	   
}
