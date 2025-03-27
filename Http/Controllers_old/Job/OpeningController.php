<?php

namespace App\Http\Controllers\Job;

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
use App\Models\Company\Department;

class OpeningController extends Controller
{
    
       public function jobOpening(Request $req)
	   {
		  $filterList = array();
		  $filterList['department'] = '';
		  $filterList['name'] = '';
		  $filterList['location'] = '';
		  $filterList['status'] = '';
		  $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->where("status",1)->get();
		  $jobOpeningDetails = JobOpening::orderBy("id","DESC")->whereIn("status",array(1,2));
		  
		  if(!empty($req->session()->get('name')))
			{
			
				$name = $req->session()->get('name');
				$filterList['name'] = $name;
				$jobOpeningDetails = $jobOpeningDetails->where("name","like","%".$name."%");
			}
		 if(!empty($req->session()->get('department')))
			{
			
				$department = $req->session()->get('department');
				$filterList['department'] = $department;
				$jobOpeningDetails = $jobOpeningDetails->where("department",$department);
			}
		 if(!empty($req->session()->get('location')))
			{
			
				$location = $req->session()->get('location');
				$filterList['location'] = $location;
				$jobOpeningDetails = $jobOpeningDetails->where("location",$location);
			}
		 if(!empty($req->session()->get('status')))
			{
			
				$status = $req->session()->get('status');
				$filterList['status'] = $status;
				$jobOpeningDetails = $jobOpeningDetails->where("status",$status);
			}
			
		  $jobOpeningDetails = $jobOpeningDetails->get();
		  return view("Opening/jobOpening",compact('jobOpeningDetails','filterList','departmentLists'));
	   }
	   
	   public function addJobOpening()
	   {
		   $departmentDetails =  Department::where("status",1)->orderBy("id","DESC")->get();
		   return view("Opening/addJobOpening",compact('departmentDetails'));
	   }
	   
	   public function addJobOpeningPost(Request $request)
	   {
		   $parameterInput = $request->input();
		  
		   $jobOpeningMod = new JobOpening();
			$jobOpeningMod->name = $parameterInput['jobOpening']['name'];
			$jobOpeningMod->job_description = $parameterInput['jobOpening']['job_description'];
			$jobOpeningMod->job_specification = $parameterInput['jobOpening']['job_specification'];
			$jobOpeningMod->department = $parameterInput['jobOpening']['department'];
			$jobOpeningMod->location = $parameterInput['jobOpening']['location'];
			$jobOpeningMod->status = $parameterInput['jobOpening']['status'];
			$jobOpeningMod->save();
			$jobOpeningId= $jobOpeningMod->id;
			
			/*
			*Define Target 
			*/
			$targetMonth = $parameterInput['target_month'];
			for($i =0;$i<count($targetMonth);$i++)
			{
				if($parameterInput['target_month'][$i] != '' && $parameterInput['no_target'][$i] != '')
				{
					$jobOpeningTargetMod = new JobOpeningTarget();
					$jobOpeningTargetMod->job_opening_id = $jobOpeningId;
					$jobOpeningTargetMod->target_month = $parameterInput['target_month'][$i];
					$jobOpeningTargetMod->targets = $parameterInput['no_target'][$i];
					$jobOpeningTargetMod->save();
				}
			}
			
			/*
			*Define Target
			*/
			$request->session()->flash('message','Job Opening Saved.');
			return redirect('jobOpening');
	   }
	   
	   public function viewJobOpening(Request $request)
	   {
		    $jobOpeningId = $request->id;
		    $jobOpeningDetails = JobOpening::where("id",$jobOpeningId)->first();
			$jobOpeningTargetValues = JobOpeningTarget::where("job_opening_id",$jobOpeningId)->get();
			return view("Opening/viewJobOpening",compact('jobOpeningDetails','jobOpeningTargetValues'));
	   }
	   
	   public function updateJobOpening(Request $request)
	   {
		    $departmentDetails =  Department::where("status",1)->orderBy("id","DESC")->get();
		    $jobOpeningId = $request->id;
		    $jobOpeningDetails = JobOpening::where("id",$jobOpeningId)->first();
			$jobOpeningTargetValues = JobOpeningTarget::where("job_opening_id",$jobOpeningId)->get();
		
			return view("Opening/updateJobOpening",compact('jobOpeningDetails','departmentDetails','jobOpeningTargetValues'));
	   }
	   
	   public function updateJobOpeningPost(Request $request)
	   {
		   $parameterMeters = $request->input();
		  
		    $datas = $parameterMeters['jobOpening'];
		    $jobOpeningUpdateMod = JobOpening::find($datas['id']);
		    $jobOpeningUpdateMod->name = $datas['name'];
		    $jobOpeningUpdateMod->job_description = $datas['job_description'];
		    $jobOpeningUpdateMod->job_specification = $datas['job_specification'];
		    $jobOpeningUpdateMod->department = $datas['department'];
		    $jobOpeningUpdateMod->location = $datas['location'];
		    $jobOpeningUpdateMod->status = $datas['status'];
			$jobOpeningUpdateMod->save();
			/*
			*Define Target 
			*/
			  /* delete Monthly Target */
				 $jobOpeningId = $datas['id'];
				$updateJobOpeningTarget =  JobOpeningTarget::where("job_opening_id",$jobOpeningId)->get();
				foreach($updateJobOpeningTarget as $_jobOpeningTarget)
				{
					$jobOpeningTargetModDelete = JobOpeningTarget::find($_jobOpeningTarget->id);
					$jobOpeningTargetModDelete->delete();
				}
			  /* delete Monthly Target */
			$targetMonth = $parameterMeters['target_month'];
			for($i =0;$i<count($targetMonth);$i++)
			{
				if($parameterMeters['target_month'][$i] != '' && $parameterMeters['no_target'][$i] != '')
				{
					$jobOpeningTargetMod = new JobOpeningTarget();
					$jobOpeningTargetMod->job_opening_id = $jobOpeningId;
					$jobOpeningTargetMod->target_month = $parameterMeters['target_month'][$i];
					$jobOpeningTargetMod->targets = $parameterMeters['no_target'][$i];
					$jobOpeningTargetMod->save();
				}
			}
			
			/*
			*Define Target
			*/
			$request->session()->flash('message','Job Opening Updated.');
			return redirect('jobOpening');
	   }
	   
	   public function deleteJobOpening(Request $request)
	   {
		     $jobOpeningId = $request->id;
			 $jobOpeningUpdateMod = JobOpening::find($jobOpeningId);
			 $jobOpeningUpdateMod->status = 3;
			 $jobOpeningUpdateMod->save();
			 $request->session()->flash('message','Job Opening Deleted.');
			 return redirect('jobOpening');
	   }
	   
	   public function appliedFilterOnJobOpening(Request $request)
	   {
		   $selectedFilter = $request->input();
		   $request->session()->put('department',$selectedFilter['department']);		
		   $request->session()->put('name',$selectedFilter['name']);
		   $request->session()->put('location',$selectedFilter['location']);
		   $request->session()->put('status',$selectedFilter['status']);
		   return redirect('jobOpening');
	   }
	   
	   public function resetjobOpeningFilter(Request $request)
	   {
		   $request->session()->put('department',"");		
		   $request->session()->put('name',"");
		   $request->session()->put('location',"");
		   $request->session()->put('status',"");
		   return redirect('jobOpening');
	   }
	   
	   public function addMoreTarget(Request $request)
	   {
		   $counterValue = $request->counter;
		   	return view("Opening/addMoreTarget",compact('counterValue'));
	   }

}
