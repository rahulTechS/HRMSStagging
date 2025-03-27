<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Consultancy\ConsultancyModel;
use App\Models\Consultancy\Resumedetails;

use App\Models\Recruiter\Designation;
use App\Models\Recruiter\Recruiter;
use App\Models\Recruiter\Stages;
use App\Models\Recruiter\CandidateStatus;
use App\Models\Entry\Employee;
use App\Models\Employee\Employee_details;
use App\Models\Recruiter\ResumeHistroy;
use App\Models\Company\Department;
use App\Models\JobFunction\JobFunction;
use Crypt;
use Session;
class RecruiterController extends Controller
{
		public function registeredRecruiter()
		{
			return view("Recruiter/Frontend/DashboardRecruiter");
		}
		
		public function manageRecruiter()
		{
			$recruiterLists = array();
			$recruiterLists = Recruiter::where("status",1)->orWhere('status',2)->get();
			return view("Recruiter/manageRecruiter",compact('recruiterLists'));
		}
		
		public function manageDesignation()
		{
			$designationLists = array();
			$designationLists = Designation::where("status",1)->orWhere("status",2)->get();
			return view("Recruiter/manageDesignation",compact('designationLists'));
		}
		
		public function addDesignation()
		{
			$departmentDetails = Department::where("status",1)->get();
			$jobFunctionDetails = JobFunction::orderBy("id","DESC")->where("status",1)->get();
			return view("Recruiter/addDesignation",compact('departmentDetails','jobFunctionDetails'));
		}
		
		public function addDesignationPost(Request $request)
		{
			$requestInput = $request->input();
			
			$designation_mod = new Designation();
			$designation_mod->name = $requestInput['name'];
			$designation_mod->department_id = $requestInput['department_id'];
			$designation_mod->job_function = $requestInput['jobFunction_id'];
			$designation_mod->tlsm = $requestInput['is_tl'];
			$designation_mod->status = $requestInput['status'];
			$designation_mod->save();
			$request->session()->flash('message','Successfully Saved Designation.');
			return redirect('manageDesignation');
			
		}
		
		public function updateDesignation(Request $request)
		{
			$departmentDetails = Department::where("status",1)->get();
			$jobFunctionDetails = JobFunction::where("status",1)->get();
			$designationdata = Designation::where('id',$request->id)->get()->first();
			

			return view("Recruiter/updateDesignation",compact('designationdata','departmentDetails','jobFunctionDetails'));
		}
		public function updateDesignationPost(Request $request)
		{
			$requestInput = $request->input();
			//$jobFunctionDetails = JobFunction::where("status",1)->get();
			
			$designation_mod = Designation::find($requestInput['id']);
			$designation_mod->name = $requestInput['name'];
			$designation_mod->department_id = $requestInput['department_id'];
			$designation_mod->job_function = $requestInput['jobFunction_id'];
			$designation_mod->tlsm = $requestInput['is_tl'];
			$designation_mod->status = $requestInput['status'];
			if($designation_mod->save()){
			if($requestInput['is_tl']==2){
				$designationdatanew = Employee_details::where('designation_by_doc_collection',$designation_mod->id)->get();
         foreach($designationdatanew as $detailsdataemp){
	             $emplyooid=$detailsdataemp->id;
				$employeedetilstable=Employee_details::find($emplyooid);
				//print_r($employeedetilstable);exit;
				$employeedetilstable->job_function=$requestInput['jobFunction_id'];
				//$employeedetilstable->job_opening_id=3;
				//$employeedetilstable->update_comment="TL";
				$employeedetilstable->save();
			   }
			}
			}
			$request->session()->flash('message','Successfully Updated Designation.');
			return redirect('manageDesignation');
		}
		public function deleteDesignation(Request $request)
		{
			$designation_mod = Designation::find($request->id);
			$designation_mod->status =3;
			$designation_mod->save();
			$request->session()->flash('message','Designation Deleted Successfully.');
			return redirect('manageDesignation');
		}
		public function addRecruiter()
		{
			$designationLists = Designation::where("status",1)->get();
			return view("Recruiter/addRecruiter",compact('designationLists'));
		}
		
		public function addRecruiterPost(Request $request)
		{
			$requestInput = $request->input();
			/*
			*checking username existance in employee model
			*start code
			*/
			$emplists = Employee::where('username',$request->input('username'))->get();
			$emplistsCount = $emplists->count();
			if($emplistsCount >0)
			{
				$request->session()->flash('message','username already exists.');
				$request->session()->flash('alert-class', 'alert-danger'); 
				return redirect()->back()->withInput();
			}
			/*
			*checking username existance in employee model
			*end code
			*/
			$recruiter_mod = new Recruiter();
			$recruiter_mod->emp_id = $requestInput['emp_id'];
			$recruiter_mod->name = $requestInput['name'];
			$recruiter_mod->recruit_designation = $requestInput['recruit_designation'];
			$recruiter_mod->username = $requestInput['username'];
			$recruiter_mod->password = Crypt::encrypt($requestInput['password']);
			$recruiter_mod->passwordtxt = $requestInput['password'];
			$recruiter_mod->status = $requestInput['status'];
			$recruiter_mod->save();
			
			/*
			*
			*@update employee account as recruiter
			*@start code
			*/
			$e_obj = new Employee();
			$e_obj->username = $request->input('username');
			$e_obj->password = Crypt::encrypt($request->input('password'));
			$e_obj->fullname = $request->input('name');
			$e_obj->passwordtxt = $request->input('password');
			$e_obj->designation = 'Recruiter';
			$e_obj->pics = 'user-profile.png';
			$e_obj->status = $request->input('status');;
			$e_obj->save();
			/*
			*
			*@update employee account as recruiter
			*@end code
			*/
			
			$request->session()->flash('message','Successfully Saved Recruiter.');
			return redirect('manageRecruiter');
			
		}
		
		public function updateRecruiter(Request $request)
		{
			$recruiterData = Recruiter::where('id',$request->id)->get()->first();
			
			$designationList = Designation::where('status',1)->get();
			return view("Recruiter/updateRecruiter",compact('designationList','recruiterData'));
			
		}
		
		public function updateRecruiterPost(Request $request)
		{
			$requestInput = $request->input();
			$recruiter_mod = Recruiter::find($requestInput['id']);
			$recruiter_mod->emp_id = $requestInput['emp_id'];
			$recruiter_mod->name = $requestInput['name'];
			$recruiter_mod->recruit_designation = $requestInput['recruit_designation'];
			$recruiter_mod->status = $requestInput['status'];
			$recruiter_mod->save();
			$request->session()->flash('message','Successfully Updated Recruiter.');
			return redirect('manageRecruiter');
		}
		
		public function changeRecruiterAccess(Request $request)
		{
			$recruiterData = Recruiter::where('id',$request->id)->get()->first();
			return view("Recruiter/changeRecruiterAccess",compact('recruiterData'));
		}
		
		public function changepassRecruiterPost(Request $req)
		{
			$parameterDetails = $req->input();
			
			$c_obj =  Recruiter::find($req->input('id'));
			
            $c_obj->password = Crypt::encrypt($req->input('password'));
			$c_obj->passwordtxt = $req->input('password');
			$c_obj->save();
			/*
			*
			*@employee model updation for password
			*
			*/
			$_empObj = Employee::where("username",$req->input('username'))->get()->first();
			
			$e_obj =  Employee::find($_empObj->id);
			$e_obj->password = Crypt::encrypt($req->input('password'));
			$e_obj->passwordtxt = $req->input('password');
			$e_obj->save();
			/*
			*
			*@employee model updation for password
			*
			*/
            $req->session()->flash('message','Recruiter Password Updated Successfully.');
            return redirect('manageRecruiter');
		}
		
		public function deleteRecruiter(Request $req)
		{
			$c_obj =  Recruiter::find($req->id);
            $c_obj->status = 3;
			$c_obj->save(); 
			/*
			*
			*@employee model updation for password
			*
			*/
			$recuriterModel = Recruiter::where('id',$req->id)->get()->first();
			
			$uName = $recuriterModel->username;
			
			$_empObj = Employee::where("username",$uName)->get()->first();
			
			$e_obj =  Employee::find($_empObj->id);
			$e_obj->status = 3;
			
			$e_obj->save();
			/*
			*
			*@employee model updation for password
			*
			*/
            $req->session()->flash('message','Recruiter Deleted Successfully.');
            return redirect('manageRecruiter');
		}
		
		public function manageCandidateStatus()
		{
			$candidateStatus = array();
			$candidateStatus = CandidateStatus::where('Status',1)->orWhere('status',2)->get();
			return view("Recruiter/CandidateStatus/manageCandidateStatus",compact('candidateStatus'));
		}
		
		public function addCandidateStatus()
		{
			$designationLists = array();
			$stages = array();
			
			$designationLists = Designation::where("status",1)->get();
			$stages = Stages::get();
			return view("Recruiter/CandidateStatus/addCandidateStatus",compact('designationLists','stages'));
		}
		public function manageStage()
		{
			$stages = array();
			$stages = Stages::get();
			return view("Recruiter/Stage/manageStage",compact('stages'));
		}
		public function addStage()
		{
			return view("Recruiter/Stage/addStage");
		}
		public function addStagePost(Request $req)
		{
			$reqInput = $req->input();
			$stageMod = new Stages();
			$stageMod->name = $req->input('name');
			$stageMod->save();
			$req->session()->flash('message','Stage Add Successfully.');
            return redirect('manageStage');
		}
		public function addCandidateStatusPost(Request $req)
		{
			$reqInput = $req->input();
			$statusMod = new CandidateStatus();
			$statusMod->status_name = $req->input('status_name');
			$statusMod->stage_id = $req->input('stage_id');
			$statusMod->designation_id = $req->input('designation_id');
			$statusMod->status = $req->input('status');
			$statusMod->save();
			$req->session()->flash('message','Status Add Successfully.');
            return redirect('manageCandidateStatus');
		}
		public function updateCandidateStatus(Request $req)
		{
			$candidateMod = CandidateStatus::where('id',$req->id)->get()->first();
			$designationLists = array();
			$stages = array();
			
			$designationLists = Designation::where("status",1)->get();
			$stages = Stages::get();
			return view("Recruiter/CandidateStatus/updateCandidateStatus",compact('designationLists','stages','candidateMod'));
		}
		public function updateCandidateStatusPost(Request $req)
		{
			$reqModInput = $req->input();
			$statusMod = CandidateStatus::find($req->input('id'));
			$statusMod->status_name = $req->input('status_name');
			$statusMod->stage_id = $req->input('stage_id');
			$statusMod->designation_id = $req->input('designation_id');
			$statusMod->status = $req->input('status');
			$statusMod->save();
			$req->session()->flash('message','Status Updated Successfully.');
            return redirect('manageCandidateStatus');
		}
		
		public function deleteCandidateStatus(Request $req)
		{
			$statusMod = CandidateStatus::find($req->id);
			$statusMod->status = 3;
			$statusMod->save();
			$req->session()->flash('message','Status deleted Successfully.');
            return redirect('manageCandidateStatus');
		}
		
		public function shortlistedResume(Request $request)
		{
			$employeeDesignation = $request->session()->get('EmployeeDesignation');
		$layoutName = '';
			if($employeeDesignation == 'Recruiter')
			{	
				$layoutName = 'layouts.recruiterLayout';
			}
			else if($employeeDesignation == 'Consultancy')
			{
				$layoutName = 'layouts.consultancyLayout';
			}
			else
			{	
				$layoutName = 'layouts.hrmLayout';
			}
			/*
			*Session values
			*/
			
			$sessionValues['selectedrecordperpage_shortlist'] = '8';
			$sessionValues['selectedresumeStatus_shortlist'] = '';
			$sessionValues['selectedConsultancy_shortlist'] = '';
			$sessionValues['selecteddateFrom_shortlist'] = '';
			$sessionValues['selecteddateTo_shortlist'] = '';
			if($request->session()->get('selectedConsultancy_shortlist')  != '' )
			{
				$sessionValues['selectedConsultancy_shortlist'] =$request->session()->get('selectedConsultancy_shortlist');
			}
			if($request->session()->get('selectedresumeStatus_shortlist') != '' )
			{
				
				$sessionValues['selectedresumeStatus_shortlist'] =$request->session()->get('selectedresumeStatus_shortlist');
			}
			if($request->session()->get('selectedrecordperpage_shortlist') != '' )
			{
				$sessionValues['selectedrecordperpage_shortlist'] =$request->session()->get('selectedrecordperpage_shortlist');
				
			}
			if($request->session()->get('selecteddateFrom_shortlist') != '' )
			{
				$sessionValues['selecteddateFrom_shortlist'] =$request->session()->get('selecteddateFrom_shortlist');
				
			}
			if($request->session()->get('selecteddateTo_shortlist') != '' )
			{
				$sessionValues['selecteddateTo_shortlist'] =$request->session()->get('selecteddateTo_shortlist');
				
			}
			
			
			/*
			*Session Values
			*/
			/**
			*@getting all Datats needed for resume listing
			*/
			$datas['status'] = array('1'=>'Shortlisted');
			$cStatusList = CandidateStatus::where('status',1)->get();
			foreach($cStatusList as $_cstatus)
			{
				$datas['status'][$_cstatus->id] = $_cstatus->status_name;
			}
			$consultancyDetails = ConsultancyModel::where('status',1)->get();
			$datas['consultancyLists'] = $consultancyDetails;
			
			$whereCause = array();
			$whereCauseBetween = array();
			if($sessionValues['selectedresumeStatus_shortlist'] != '')
			{
				$whereCause['status'] = $sessionValues['selectedresumeStatus_shortlist'];
			}
			
			if($sessionValues['selectedConsultancy_shortlist'] != '')
			{
				$whereCause['consultancy_id'] = $sessionValues['selectedConsultancy_shortlist'];
			}
			
			
			$whereCause['resume_status'] = 2;
			
			if(count($whereCause) >0 && ($sessionValues['selecteddateFrom_shortlist'] != '' && $sessionValues['selecteddateTo_shortlist'] != ''))
			{
				$fromdate = $sessionValues['selecteddateFrom_shortlist'];
				$todate = $sessionValues['selecteddateTo_shortlist'];
				$datas['resumeCount'] = Resumedetails::where($whereCause)->whereBetween('created_at', [$fromdate, $todate])->get();
			}
			elseif(count($whereCause) >0 && ($sessionValues['selecteddateFrom_shortlist'] == '' && $sessionValues['selecteddateTo_shortlist'] == ''))
			{
				$datas['resumeCount'] = Resumedetails::where($whereCause)->get();
			}
			elseif(count($whereCause) == 0 && ($sessionValues['selecteddateFrom_shortlist'] != '' && $sessionValues['selecteddateTo_shortlist'] != ''))
			{
				$fromdate = $sessionValues['selecteddateFrom_shortlist'];
				$todate = $sessionValues['selecteddateTo_shortlist'];
				$datas['resumeCount'] = Resumedetails::whereBetween('created_at', [$fromdate, $todate])->get();
			}
			else
			{
				$datas['resumeCount'] = Resumedetails::get();
			}
			/**
			*@getting all Datats needed for resume listing
			*/
			
			$candidateStatusModel = CandidateStatus::where('status',1)->get();
		return view("Recruiter/shortlistedResume",compact('layoutName','datas','sessionValues','candidateStatusModel'));
			
		
		}
		
		public function getResumeshortlisted(Request $request)
			{
				$consultancyId = $request->id;
				$fromdate = $request->fromdate;
				$todate = $request->todate;
				$status = $request->status;
				/*
				* checking for recruiter account
				* adding degination conditions
				* start code
				*/
				
				$employeeDesignation = $request->session()->get('EmployeeDesignation');
				$employeeId = $request->session()->get('EmployeeId');
				if($employeeDesignation == 'Recruiter')
				{
					$empObj = Employee::where("id",$employeeId)->get()->first();			
					$username = $empObj->username;
					$recruiterObj = Recruiter::where("username",$username)->get()->first();
					
					$recruit_designation = $recruiterObj->recruit_designation;
					$resumeLists = Resumedetails::where("consultancy_id",$consultancyId)->where("resume_status",2)->where("status",$status)->where("resume_designation",$recruit_designation)->whereBetween('created_at', [$fromdate, $todate])->get();
					$candidateStatusModel  = CandidateStatus::where('status',1)->where('designation_id',$recruit_designation)->get();
				}
				else
				{
					$resumeLists = Resumedetails::where("consultancy_id",$consultancyId)->where("resume_status",2)->where("status",$status)->whereBetween('created_at', [$fromdate, $todate])->get();
					$candidateStatusModel  = CandidateStatus::where('status',1)->get();
				}
				/*
				* checking for recruiter account
				* adding degination conditions
				* end code
				*/
				
				$resumeCount = $resumeLists->count();
				$employeeDesignation = $request->session()->get('EmployeeDesignation');
					$layoutName = '';
					if($employeeDesignation == 'Recruiter')
					{	
					$layoutName = 'layouts.recruiterLayout';
					}
					else
					{	
					$layoutName = 'layouts.hrmLayout';
					}
				return view("Recruiter/Resume/getResumeshortlisted",compact('resumeLists','resumeCount','layoutName','candidateStatusModel'));
				
			}
			public function setCandidateStatus(Request $request)
			{
				$reqInput = $request->input();
				
				$comment = $request->input('comment');
				$status = $request->input('status');
				$resumeIDArray = $request->input('idtext');
				$resumeIDArrayV = explode("_",$resumeIDArray);
				$resumeID = $resumeIDArrayV[1];
				$_resumeObj = Resumedetails::find($resumeID);
				$_resumeObj->feedback = $comment;
				$_resumeObj->status = $status;
				$_resumeObj->save();
				/*
				*
				*/
				
					$resumeHistroyObj = new ResumeHistroy();
					$resumeHistroyObj->resume_id = $resumeID;
					if($status == 1)
					{
						$resumeHistroyObj->status_name	 = 'Shortlisted';
						$resumeHistroyObj->status_id	 = 1;
					}
					else
					{
						$candidateStatusOBJ = CandidateStatus::where('id',$status)->get()->first();
						$resumeHistroyObj->status_name	 = $candidateStatusOBJ->status_name;
						$resumeHistroyObj->status_id	 = $status;
					}
					$resumeHistroyObj->comment	 = $comment;
					$resumeHistroyObj->save();
				/*
				*
				*/
				echo "DONE";
				exit;
			}
			
			public function shortlistedResumeFilter(Request $request)
				{
					/*
					*getting filter features
					*start coding
					*/
					$selectedFilter = $request->input();
					
					
					if(!empty($selectedFilter['consultancy']))
					{
						$request->session()->put('selectedConsultancy_shortlist',$selectedFilter['consultancy']);
					}
					else
					{
						$request->session()->put('selectedConsultancy_shortlist','');
					}
					
					if(!empty($selectedFilter['resumeStatus']))
					{
						$request->session()->put('selectedresumeStatus_shortlist',$selectedFilter['resumeStatus']);
					}
					else
					{
						$request->session()->put('selectedresumeStatus_shortlist','');
					}
					
					
					if(!empty($selectedFilter['recordperpage']))
					{
						$request->session()->put('selectedrecordperpage_shortlist',$selectedFilter['recordperpage']);
					}
					else
					{
						$request->session()->put('selectedrecordperpage_shortlist','');
					}
					
					
					if(!empty($selectedFilter['date_from']))
					{
						$request->session()->put('selecteddateFrom_shortlist',$selectedFilter['date_from']);
					}
					else
					{
						$request->session()->put('selecteddateFrom_shortlist','');
					}
					
					if(!empty($selectedFilter['date_to']))
					{
						$request->session()->put('selecteddateTo_shortlist',$selectedFilter['date_to']);
					}
					else
					{
						$request->session()->put('selecteddateTo_shortlist','');
					}
					
					return redirect('shortlistedResume');
					/*
					*getting filter features
					*end coding
					*/
					
					
				}
				
				
				public function showResumeShortListed(Request $request)
					{
						
							/**
							*@getting all Datats needed for resume listing
							*/
							$pagelimit= $request->pageLimit;
							$skip= $request->skip;
							$sessionValues['selectedresumeStatus_shortlist'] = '';
							$sessionValues['selectedConsultancy_shortlist'] = '';
							$sessionValues['selecteddateFrom_shortlist'] = '';
							$sessionValues['selecteddateTo_shortlist'] = '';
							if($request->session()->get('selectedConsultancy_shortlist')  != '' )
							{
								$sessionValues['selectedConsultancy_shortlist'] =$request->session()->get('selectedConsultancy_shortlist');
							}
							if($request->session()->get('selectedresumeStatus_shortlist') != '' )
							{
								
								$sessionValues['selectedresumeStatus_shortlist'] =$request->session()->get('selectedresumeStatus_shortlist');
							}
							if($request->session()->get('selecteddateFrom_shortlist') != '' )
							{
								$sessionValues['selecteddateFrom_shortlist'] =$request->session()->get('selecteddateFrom_shortlist');
								
							}
							if($request->session()->get('selecteddateTo_shortlist') != '' )
							{
								$sessionValues['selecteddateTo_shortlist'] =$request->session()->get('selecteddateTo_shortlist');
								
							}
							
							$whereCause = array();
							if($sessionValues['selectedresumeStatus_shortlist'] != '')
							{
								$whereCause['status'] = $sessionValues['selectedresumeStatus_shortlist'];
							}
							
							if($sessionValues['selectedConsultancy_shortlist'] != '')
							{
								$whereCause['consultancy_id'] = $sessionValues['selectedConsultancy_shortlist'];
							}
							$whereCause['resume_status'] = 2;
							if(count($whereCause) >0 && ($sessionValues['selecteddateFrom_shortlist'] != '' && $sessionValues['selecteddateTo_shortlist'] != ''))
							{
								$fromdate = $sessionValues['selecteddateFrom_shortlist'];
								$todate = $sessionValues['selecteddateTo_shortlist'];
							$resumeLists = Resumedetails::where($whereCause)->whereBetween('created_at', [$fromdate, $todate])->skip($skip)->take($pagelimit)->orderBy('id', 'DESC')->get();
							}
							else if(count($whereCause) >0 && ($sessionValues['selecteddateFrom_shortlist'] == '' && $sessionValues['selecteddateTo_shortlist'] == ''))
							{
							$resumeLists = Resumedetails::where($whereCause)->skip($skip)->take($pagelimit)->orderBy('id', 'DESC')->get();
							}
							elseif(count($whereCause) == 0 && ($sessionValues['selecteddateFrom_shortlist'] != '' && $sessionValues['selecteddateTo_shortlist'] != ''))
							{
								$fromdate = $sessionValues['selecteddateFrom_shortlist'];
								$todate = $sessionValues['selecteddateTo_shortlist'];
								$resumeLists = Resumedetails::whereBetween('created_at', [$fromdate, $todate])->skip($skip)->take($pagelimit)->orderBy('id', 'DESC')->get();
							}
							else
							{
								$resumeLists = Resumedetails::skip($skip)->take($pagelimit)->orderBy('id', 'DESC')->get();
							}
							/**
							*@getting all Datats needed for resume listing
							*/
						return view("Recruiter/showResumeShortListed",compact('resumeLists'));
					}
					public function resetSearchShortlisted(Request $request)
						{
							$request->session()->put('selectedConsultancy_shortlist','');
							$request->session()->put('selectedresumeStatus_shortlist','');
							$request->session()->put('selectedrecordperpage_shortlist','');
							$request->session()->put('selecteddateFrom_shortlist','');
							$request->session()->put('selecteddateTo_shortlist','');
							
							$request->session()->flash('message','Filter reset successfully.');
							return redirect('shortlistedResume');
						}
		public static function getJobFunctionName($id)
			{	
			
			  $data = JobFunction::where("id",$id)->first();
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
