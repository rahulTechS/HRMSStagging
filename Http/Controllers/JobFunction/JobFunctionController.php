<?php

namespace App\Http\Controllers\JobFunction;

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

class JobFunctionController extends Controller
{
    
       public function jobFunction(Request $req)
	   {
		  $filterList = array();
		  $filterList['name'] = '';
		  $filterList['status'] = '';
		  
		  $jobFunctionDetails = JobFunction::orderBy("id","DESC")->where("status",1);
		  
		  if(!empty($req->session()->get('name')))
			{
			
				$name = $req->session()->get('name');
				$filterList['name'] = $name;
				$jobFunctionDetails = $jobFunctionDetails->where("name","like","%".$name."%");
			}
		 
		 if(!empty($req->session()->get('status')))
			{
			
				$status = $req->session()->get('status');
				$filterList['status'] = $status;
				$jobFunctionDetails = $jobFunctionDetails->where("status",$status);
			}
			
		  $jobFunctionDetails = $jobFunctionDetails->get();
		  return view("JobFunction/jobFunction",compact('jobFunctionDetails','filterList'));
	   }
	   
	   public function addJobFunction()
	   {
		   return view("JobFunction/addJobFunction");
	   }
	   
	   public function addJobFunctionPost(Request $request)
	   {
		   $parameterInput = $request->input();
		  //print_r($parameterInput);exit;
		   $jobOpeningMod = new JobFunction();
			$jobOpeningMod->name = $parameterInput['jobFunction']['name'];			
			$jobOpeningMod->status = $parameterInput['jobFunction']['status'];
			$jobOpeningMod->save();
			$request->session()->flash('message','Job Function Saved.');
			return redirect('jobFunction');
	   }
	   	   
	   public function updateJobFunction(Request $request)
	   {
		    $jobFunctionId = $request->id;
		    $jobFunctionDetails = JobFunction::where("id",$jobFunctionId)->first();
			
			return view("JobFunction/updateJobFunction",compact('jobFunctionDetails'));
	   }
	   
	   public function updateJobFunctionPost(Request $request)
	   {
		   $parameterMeters = $request->input();
		  
		    $datas = $parameterMeters['jobFunction'];
		    $jobOpeningUpdateMod = JobFunction::find($datas['id']);
		    $jobOpeningUpdateMod->name = $datas['name'];
		    $jobOpeningUpdateMod->status = $datas['status'];
			$jobOpeningUpdateMod->save();
			
			$request->session()->flash('message','Job Function Updated.');
			return redirect('jobFunction');
	   }
	   
	   public function deleteJobFunction(Request $request)
	   {
		     $jobFunctionId = $request->id;
			 $jobFunctionUpdateMod = JobFunction::find($jobFunctionId);
			 $jobFunctionUpdateMod->status = 3;
			 $jobFunctionUpdateMod->save();
			 $request->session()->flash('message','Job Function Deleted.');
			 return redirect('jobFunction');
	   }
	   
	   public function appliedFilterOnJobFunction(Request $request)
	   {
		   $selectedFilter = $request->input();		
		   $request->session()->put('name',$selectedFilter['name']);
		   $request->session()->put('status',$selectedFilter['status']);
		   return redirect('jobFunction');
	   }
	   
	   public function resetjobFunctionFilter(Request $request)
	   {
		   $request->session()->put('department',"");		
		   $request->session()->put('name',"");
		   $request->session()->put('location',"");
		   $request->session()->put('status',"");
		   return redirect('jobFunction');
	   }
	   

}
