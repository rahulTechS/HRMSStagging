<?php

namespace App\Http\Controllers\Contents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\Onboarding\RecruiterDetails;
use App\Models\Onboarding\HiringSourceDetails;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\Employee\Employee_details;
use Crypt;
use Session;
class ContentsController extends Controller
{
		
		public function recruiterContents()
		{
			$recruiterLists = array();
			$recruiterLists = RecruiterDetails::where("status",1)->orWhere("status",2)->orderBy("id","DESC")->get();
			return view("Contents/recruiterContents",compact('recruiterLists'));
		}
		
		 public function addRecruiter()
		{
			$empdata=Employee_details::where("status",1)->where("offline_status",1)->get();
			$RecruiterLists=RecruiterCategory::where("status",1)->get();
			return view("Contents/addRecruiter",compact('RecruiterLists','empdata'));
		}
		
		public function addRecruiterPost(Request $request)
		{
			$requestInput = $request->input();
			
			$recruiter_mod = new RecruiterDetails();
			$recruiter_mod->name = $requestInput['name'];
			$recruiter_mod->recruit_cat = $requestInput['recruit_cat'];			
			$recruiter_mod->status = $requestInput['status'];
			$recruiter_mod->employee_id = $requestInput['emp_id'];
			$recruiter_mod->save();
			$request->session()->flash('message','Successfully Saved Recruiter.');
			return redirect('recruiterContents');
			
		}
		
		public function updateRecruiter(Request $request)
		{
			$empdata=Employee_details::where("status",1)->where("offline_status",1)->get();
			$RecruiterLists=RecruiterCategory::where("status",1)->get();
			$recruiterdata = RecruiterDetails::where('id',$request->id)->get()->first();
			return view("Contents/updateRecruiter",compact('recruiterdata','RecruiterLists','empdata'));
		}
		
		public function updateRecruiterPost(Request $request)
		{
			$requestInput = $request->input();
			
			$recruiterDetailsMod = RecruiterDetails::find($requestInput['id']);
			$recruiterDetailsMod->name = $requestInput['name'];
			$recruiterDetailsMod->recruit_cat = $requestInput['recruit_cat'];
			$recruiterDetailsMod->status = $requestInput['status'];
			$recruiterDetailsMod->employee_id = $requestInput['emp_id'];
			$recruiterDetailsMod->save();
			$request->session()->flash('message','Successfully Updated Recruiter.');
			return redirect('recruiterContents');
		}
		
		public function deleteRecruiter(Request $request)
		{
			$recruiterDetailsMod = RecruiterDetails::find($request->id);
			$recruiterDetailsMod->status =3;
			$recruiterDetailsMod->save();
			$request->session()->flash('message','Recruiter Deleted Successfully.');
			return redirect('recruiterContents');
		} 
		
		public function manageHiringSource()
		{
			$hiringSourceLists = array();
			$hiringSourceLists = HiringSourceDetails::where("status",1)->orWhere("status",2)->orderBy("id","DESC")->get();
			return view("Contents/manageHiringSource",compact('hiringSourceLists'));
		}
		public function addHiringSource()
		{
			return view("Contents/addHiringSource");
		}
		
		public function addHiringSourcePost(Request $request)
		{
			$requestInput = $request->input();
			$hiringSourceModel = new HiringSourceDetails();
			$hiringSourceModel->name = $requestInput['name'];
			$hiringSourceModel->status = $requestInput['status'];
			$hiringSourceModel->save();
			$request->session()->flash('message','Successfully Saved Hiring Source.');
			return redirect('manageHiringSource');
		}
		public function updateHiring(Request $request)
		{
			$hiringdata = HiringSourceDetails::where('id',$request->id)->get()->first();
			return view("Contents/updateHiring",compact('hiringdata'));
			
		}
		
		public function updateHiringPost(Request $request)
		{
			$requestInput = $request->input();
			$hiringSourceModel = HiringSourceDetails::find($requestInput['id']);
			$hiringSourceModel->name = $requestInput['name'];
			$hiringSourceModel->status = $requestInput['status'];
			$hiringSourceModel->save();
			$request->session()->flash('message','Successfully Updated Hiring Source.');
			return redirect('manageHiringSource');
		}
        
		public function deleteHiring(Request $request)
		{
			$hiringSourceModel = HiringSourceDetails::find($request->id);
			$hiringSourceModel->status =3;
			$hiringSourceModel->save();
			$request->session()->flash('message','Hiring Source Deleted Successfully.');
			return redirect('manageHiringSource');
		}
		public static function getcatName($id){
			$data = RecruiterCategory::where("id",$id)->first();
			  if($data != '')
			  {
				
			  return $data->name;
			  }
			  else
			  {
			  return '';
			  }
		}
}
