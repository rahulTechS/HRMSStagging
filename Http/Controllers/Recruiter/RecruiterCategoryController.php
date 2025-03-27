<?php

namespace App\Http\Controllers\Recruiter;

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
use App\Models\TrainingCategory\TrainingCategory;
use App\Models\TrainingCategory\EmpTraining;
use App\Models\DataCut\ENBDDataCutCards;
use App\Models\Employee\Employee_details;
use App\Models\Onboarding\TrainingType;
use App\Models\Recruiter\RecruiterCategory;

class RecruiterCategoryController extends Controller
{
    
	 
	
       public function RecruiterCategory(Request $req)
	   {
		  $filterList = array();
		  $filterList['name'] = '';
		  $filterList['status'] = '';
		  
		  $RecruiterCategoryDetails = RecruiterCategory::orderBy("id","DESC")->where("status",1);
		  
		  if(!empty($req->session()->get('name')))
			{
			
				$name = $req->session()->get('name');
				$filterList['name'] = $name;
				$RecruiterCategoryDetails = $RecruiterCategoryDetails->where("name","like","%".$name."%");
			}
		 
		 if(!empty($req->session()->get('status')))
			{
			
				$status = $req->session()->get('status');
				$filterList['status'] = $status;
				$RecruiterCategoryDetails = $RecruiterCategoryDetails->where("status",$status);
			}
			
		  $RecruiterCategoryDetails = $RecruiterCategoryDetails->get();
		  return view("Recruiter/RecruiterCategory/RecruiterCategory",compact('RecruiterCategoryDetails','filterList'));
	   }
	   
	   public function addRecruiterCategory()
	   {
		   return view("Recruiter/RecruiterCategory/addRecruiterCategory");
	   }
	   
	   public function addRecruiterCategoryPost(Request $request)
	   {
		   $parameterInput = $request->input();
		  //print_r($parameterInput);exit;
		   $jobOpeningMod = new RecruiterCategory();
			$jobOpeningMod->name = $parameterInput['RecruiterCategory']['name'];			
			$jobOpeningMod->status = $parameterInput['RecruiterCategory']['status'];
			$jobOpeningMod->save();
			$request->session()->flash('message','Recruiter Category Saved.');
			return redirect('RecruiterCategory');
	   }
	   
	 
	   	   
	   public function updateRecruiterCategory(Request $request)
	   {
		    $RecruiterCategoryId = $request->id;
		    $RecruiterCategoryDetails = RecruiterCategory::where("id",$RecruiterCategoryId)->first();
			
			return view("Recruiter/RecruiterCategory/updateRecruiterCategory",compact('RecruiterCategoryDetails'));
	   }
	   
	   public function updateRecruiterCategoryPost(Request $request)
	   {
		   $parameterMeters = $request->input();
		  
		    $datas = $parameterMeters['RecruiterCategory'];
		    $RecruiterCategoryUpdateMod = RecruiterCategory::find($datas['id']);
		    $RecruiterCategoryUpdateMod->name = $datas['name'];
		    $RecruiterCategoryUpdateMod->status = $datas['status'];
			$RecruiterCategoryUpdateMod->save();
			
			$request->session()->flash('message','Recruiter Category Updated.');
			return redirect('RecruiterCategory');
	   }
	   
	   public function deleteRecruiterCategory(Request $request)
	   {
		     $RecruiterCategoryId = $request->id;
			 $RecruiterCategoryUpdateMod = RecruiterCategory::find($RecruiterCategoryId);
			 $RecruiterCategoryUpdateMod->status = 3;
			 $RecruiterCategoryUpdateMod->save();
			 $request->session()->flash('message','Recruiter Category Deleted.');
			 return redirect('RecruiterCategory');
	   }
	   
	   public function appliedFilterOnRecruiterCategory(Request $request)
	   {
		   $selectedFilter = $request->input();		
		   $request->session()->put('name',$selectedFilter['name']);
		   $request->session()->put('status',$selectedFilter['status']);
		   return redirect('RecruiterCategory');
	   }
	   
	   public function resetRecruiterCategoryFilter(Request $request)
	   {
		  	
		   $request->session()->put('name',"");
		   
		   $request->session()->put('status',"");
		   return redirect('RecruiterCategory');
	   }
	   
}
