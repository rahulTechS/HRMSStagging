<?php

namespace App\Http\Controllers\SettelementCheckList;

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
use App\Models\SettelementCheckList\SettelementCheckList;
use App\Models\TrainingCategory\EmpTraining;
use App\Models\DataCut\ENBDDataCutCards;
use App\Models\Employee\Employee_details;

class SettelementCheckListController extends Controller
{
    
	 public function SettelementCheckList(Request $req)
	   {
		  $filterList = array();
		  $filterList['name'] = '';
		  $filterList['status'] = '';
		  
		  $SettelementCheckListDetails = SettelementCheckList::orderBy("id","DESC")->where("status",1);
		  
		  if(!empty($req->session()->get('name')))
			{
			
				$name = $req->session()->get('name');
				$filterList['name'] = $name;
				$SettelementCheckListDetails = $SettelementCheckListDetails->where("name","like","%".$name."%");
			}
		 
		 if(!empty($req->session()->get('status')))
			{
			
				$status = $req->session()->get('status');
				$filterList['status'] = $status;
				$SettelementCheckListDetails = $SettelementCheckListDetails->where("status",$status);
			}
			
		  $SettelementCheckListDetails = $SettelementCheckListDetails->get();
		  return view("SettelementCheckList/SettelementCheckList",compact('SettelementCheckListDetails','filterList'));
	   }
	   
	   public function addSettelementCheckList()
	   {
		   return view("SettelementCheckList/addSettelementCheckList");
	   }
	   
	   public function addSettelementCheckListPost(Request $request)
	   {
		   //print_r($request->input());exit;
		   $parameterInput = $request->input();
		  //print_r($parameterInput);exit;
		   $jobOpeningMod = new SettelementCheckList();
			$jobOpeningMod->name = $parameterInput['SettelementCheckList']['name'];			
			$jobOpeningMod->status = $parameterInput['SettelementCheckList']['status'];
			$jobOpeningMod->save();
			$request->session()->flash('message','checklist Saved.');
			return redirect('SettelementCheckList');
	   }
	   
	 
	   	   
	   public function updateSettelementCheckList(Request $request)
	   {
		    $SettelementCheckListId = $request->id;
		    $SettelementCheckListDetails = SettelementCheckList::where("id",$SettelementCheckListId)->first();
			
			return view("SettelementCheckList/updateSettelementCheckList",compact('SettelementCheckListDetails'));
	   }
	   
	   public function updateSettelementCheckListPost(Request $request)
	   {
		   $parameterMeters = $request->input();
		  
		    $datas = $parameterMeters['SettelementCheckList'];
		    $SettelementCheckListUpdateMod = SettelementCheckList::find($datas['id']);
		    $SettelementCheckListUpdateMod->name = $datas['name'];
		    $SettelementCheckListUpdateMod->status = $datas['status'];
			$SettelementCheckListUpdateMod->save();
			
			$request->session()->flash('message','checklist Updated.');
			return redirect('SettelementCheckList');
	   }
	   
	   public function deleteSettelementCheckList(Request $request)
	   {
		     $SettelementCheckListId = $request->id;
			 $SettelementCheckListUpdateMod = SettelementCheckList::find($SettelementCheckListId);
			 $SettelementCheckListUpdateMod->status = 3;
			 $SettelementCheckListUpdateMod->save();
			 $request->session()->flash('message','checklist Deleted.');
			 return redirect('SettelementCheckList');
	   }
	   
	   public function appliedFilterOnSettelementCheckList(Request $request)
	   {
		   $selectedFilter = $request->input();		
		   $request->session()->put('name',$selectedFilter['name']);
		   $request->session()->put('status',$selectedFilter['status']);
		   return redirect('SettelementCheckList');
	   }
	   
	   public function resetSettelementCheckListFilter(Request $request)
	   {
		  	
		   $request->session()->put('name',"");
		   
		   $request->session()->put('status',"");
		   return redirect('SettelementCheckList');
	   }
	   
}
