<?php

namespace App\Http\Controllers\CompanyAssets;

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
use App\Models\CompanyAssets\CompanyAssets;
use App\Models\TrainingCategory\EmpTraining;
use App\Models\DataCut\ENBDDataCutCards;
use App\Models\Employee\Employee_details;

class CompanyAssetsController extends Controller
{
    
	 public function CompanyAssets(Request $req)
	   {
		  $filterList = array();
		  $filterList['name'] = '';
		  $filterList['status'] = '';
		  
		  $CompanyAssetsDetails = CompanyAssets::orderBy("id","DESC")->whereIN("status",array(1,2));;
		  
		  if(!empty($req->session()->get('name')))
			{
			
				$name = $req->session()->get('name');
				$filterList['name'] = $name;
				$CompanyAssetsDetails = $CompanyAssetsDetails->where("name","like","%".$name."%");
			}
		 
		 if(!empty($req->session()->get('status')))
			{
			
				$status = $req->session()->get('status');
				$filterList['status'] = $status;
				$CompanyAssetsDetails = $CompanyAssetsDetails->where("status",$status);
			}
			
		  $CompanyAssetsDetails = $CompanyAssetsDetails->get();
		  return view("CompanyAssets/CompanyAssets",compact('CompanyAssetsDetails','filterList'));
	   }
	   
	   public function addCompanyAssets()
	   {
		   return view("CompanyAssets/addCompanyAssets");
	   }
	   
	   public function addCompanyAssetsPost(Request $request)
	   {
		   //print_r($request->input());exit;
		   $parameterInput = $request->input();
		  //print_r($parameterInput);exit;
		   $jobOpeningMod = new CompanyAssets();
			$jobOpeningMod->name = $parameterInput['CompanyAssets']['name'];			
			$jobOpeningMod->status = $parameterInput['CompanyAssets']['status'];
			$jobOpeningMod->save();
			$request->session()->flash('message','CompanyAssets Saved.');
			return redirect('CompanyAssets');
	   }
	   
	 
	   	   
	   public function updateCompanyAssets(Request $request)
	   {
		    $CompanyAssetsId = $request->id;
		    $CompanyAssetsDetails = CompanyAssets::where("id",$CompanyAssetsId)->first();
			
			return view("CompanyAssets/updateCompanyAssets",compact('CompanyAssetsDetails'));
	   }
	   
	   public function updateCompanyAssetsPost(Request $request)
	   {
		   $parameterMeters = $request->input();
		  
		    $datas = $parameterMeters['CompanyAssets'];
		    $CompanyAssetsUpdateMod = CompanyAssets::find($datas['id']);
		    $CompanyAssetsUpdateMod->name = $datas['name'];
		    $CompanyAssetsUpdateMod->status = $datas['status'];
			$CompanyAssetsUpdateMod->save();
			
			$request->session()->flash('message','CompanyAssets Updated.');
			return redirect('CompanyAssets');
	   }
	   
	   public function deleteCompanyAssets(Request $request)
	   {
		     $CompanyAssetsId = $request->id;
			 $CompanyAssetsUpdateMod = CompanyAssets::find($CompanyAssetsId);
			 $CompanyAssetsUpdateMod->status = 3;
			 $CompanyAssetsUpdateMod->save();
			 $request->session()->flash('message','CompanyAssets Deleted.');
			 return redirect('CompanyAssets');
	   }
	   
	   public function appliedFilterOnCompanyAssets(Request $request)
	   {
		   $selectedFilter = $request->input();		
		   $request->session()->put('name',$selectedFilter['name']);
		   $request->session()->put('status',$selectedFilter['status']);
		   return redirect('CompanyAssets');
	   }
	   
	   public function resetCompanyAssetsFilter(Request $request)
	   {
		  	
		   $request->session()->put('name',"");
		   
		   $request->session()->put('status',"");
		   return redirect('CompanyAssets');
	   }
	   
}
