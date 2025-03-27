<?php

namespace App\Http\Controllers\SettelementAttribute;

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
use App\Models\SettelementAttribute\SettelementAttribute;
use App\Models\TrainingCategory\EmpTraining;
use App\Models\DataCut\ENBDDataCutCards;
use App\Models\Employee\Employee_details;

class SettelementAttributeController extends Controller
{
    
	 public function SettelementAttribute(Request $req)
	   {
		  $filterList = array();
		  $filterList['name'] = '';
		  $filterList['status'] = '';
		  
		  $SettelementAttributeDetails = SettelementAttribute::orderBy("id","DESC")->where("status",1);
		  
		  if(!empty($req->session()->get('name')))
			{
			
				$name = $req->session()->get('name');
				$filterList['name'] = $name;
				$SettelementAttributeDetails = $SettelementAttributeDetails->where("name","like","%".$name."%");
			}
		 
		 if(!empty($req->session()->get('status')))
			{
			
				$status = $req->session()->get('status');
				$filterList['status'] = $status;
				$SettelementAttributeDetails = $SettelementAttributeDetails->where("status",$status);
			}
			
		  $SettelementAttributeDetails = $SettelementAttributeDetails->get();
		  return view("SettelementAttribute/SettelementAttribute",compact('SettelementAttributeDetails','filterList'));
	   }
	   
	   public function addSettelementAttribute()
	   {
		   return view("SettelementAttribute/addSettelementAttribute");
	   }
	   
	   public function addSettelementAttributePost(Request $request)
	   {
		   //print_r($request->input());exit;
		   $parameterInput = $request->input();
		  //print_r($parameterInput);exit;
		   $jobOpeningMod = new SettelementAttribute();
			$jobOpeningMod->name = $parameterInput['SettelementAttribute']['name'];	
			$jobOpeningMod->code = $parameterInput['SettelementAttribute']['code'];	
			$jobOpeningMod->attribute_type = $parameterInput['SettelementAttribute']['attribute_type'];				
			$jobOpeningMod->status = $parameterInput['SettelementAttribute']['status'];
			$jobOpeningMod->save();
			$request->session()->flash('message','Attribute Saved.');
			return redirect('SettelementAttribute');
	   }
	   
	 
	   	   
	   public function updateSettelementAttribute(Request $request)
	   {
		    $SettelementAttributeId = $request->id;
		    $SettelementAttributeDetails = SettelementAttribute::where("id",$SettelementAttributeId)->first();
			
			return view("SettelementAttribute/updateSettelementAttribute",compact('SettelementAttributeDetails'));
	   }
	   
	   public function updateSettelementAttributePost(Request $request)
	   {
		   $parameterMeters = $request->input();
		  
		    $datas = $parameterMeters['SettelementAttribute'];
		    $SettelementAttributeUpdateMod = SettelementAttribute::find($datas['id']);
		    $SettelementAttributeUpdateMod->name = $datas['name'];
			$SettelementAttributeUpdateMod->code = $datas['code'];
			$SettelementAttributeUpdateMod->attribute_type = $datas['attribute_type'];
		    $SettelementAttributeUpdateMod->status = $datas['status'];
			$SettelementAttributeUpdateMod->save();
			
			$request->session()->flash('message','Attribute Updated.');
			return redirect('SettelementAttribute');
	   }
	   
	   public function deleteSettelementAttribute(Request $request)
	   {
		     $SettelementAttributeId = $request->id;
			 $SettelementAttributeUpdateMod = SettelementAttribute::find($SettelementAttributeId);
			 $SettelementAttributeUpdateMod->status = 3;
			 $SettelementAttributeUpdateMod->save();
			 $request->session()->flash('message','Attribute Deleted.');
			 return redirect('SettelementAttribute');
	   }
	   
	   public function appliedFilterOnSettelementAttribute(Request $request)
	   {
		   $selectedFilter = $request->input();		
		   $request->session()->put('name',$selectedFilter['name']);
		   $request->session()->put('status',$selectedFilter['status']);
		   return redirect('SettelementAttribute');
	   }
	   
	   public function resetSettelementAttributeFilter(Request $request)
	   {
		  	
		   $request->session()->put('name',"");
		   
		   $request->session()->put('status',"");
		   return redirect('SettelementAttribute');
	   }
	   
}
