<?php

namespace App\Http\Controllers\InterviewProcess;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use Session;
use App\Models\Job\JobOpening;
use App\Models\Visa\visaType;
use App\Models\Company\Department;
use App\Models\Recruiter\Designation;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use Carbon\Carbon;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Employee\Employee_details;
use App\Models\Consultancy\ConsultancyModel;
use File;
class InterviewProcessController extends Controller
{
    public function InterviewProcess()
	{
		//echo "hello";exit;
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$jobOpning=JobOpening::where("status",1)->get();
		return view("InterviewProcess/InterviewList",compact('jobOpning','jobRecruiterDetails'));
	}
	public function addInterviewProcess()
	{
		//$managerList=HiringManager::get();
		$jobOpning=JobOpening::where("status",1)->get();
		$visaTypeList = visaType::whereIn('id',array(7,8,3,4,2))->where("status",1)->orderBy("id","DESC")->get();
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		return view("InterviewProcess/InterviewForm",compact('jobOpning','visaTypeList','jobRecruiterDetails'));
	}
	public function addInterviewProcessPost0(Request $rq)
	{
		//print_r($rq->input());exit;
		
		$keys = array_keys($_FILES);
		
		$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			$LastInsertId=date("Y-m-d_h-i-s");
			foreach($keys as $key)
			{
				//print_r($req->file($key));
				if(!empty($rq->file($key)))
				{
					//echo $key;exit;
				$filenameWithExt = $rq->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$rq->file($key)->getClientOriginalExtension();
				$vKey = $key;
				$newFileName = $key.'-interview-'.$LastInsertId.'.'.$fileExtension;
				
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				
				$extension = $rq->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$rq->file($key)->move(public_path('interviewcv/'), $newFileName);
				$fileIndex++;
				}
				else
				{
					
					$vKey = $keys[$fileIndex];
					$filesAttributeInfo[$vKey] = '';
					$listOfAttribute[] = $vKey;
					$fileIndex++;
					
				}
			}
			
			
			if(!empty($filesAttributeInfo['CV'])){
			$img=$filesAttributeInfo['CV'];
			}
			else{
			$img='';
			}
			if(!empty($filesAttributeInfo['bg_upload'])){
			$bg_upload=$filesAttributeInfo['bg_upload'];
			}
			else{
			$bg_upload='';
			}
			$date = Carbon::now();
			$formatedDate = $date->format('Y-m-d');
			$namearray = explode(' ', $rq->input('name'));
			
			$srlno ='INTERVIEW-'.$namearray[0].'-40001';
			
			$maxsrlno = InterviewProcess::max('id');
			if($maxsrlno=='')
			{
				$srlnum = $srlno;
			}
			else{
				$fdata=$maxsrlno+1;
				$srlnum = 'INTERVIEW-'.$namearray[0].'-4000'.$fdata;
			}
			$jobOpning=JobOpening::where("id",$rq->input('job_opening'))->first();
			if($jobOpning!=''){
				$departmentname=$jobOpning->department;
			}
			else{
				$departmentname='';
			}
			
			//$designation=$jobOpning->designation;
			$obj = new InterviewProcess();
			$obj->name = $rq->input('name');
			$obj->mobile = $rq->input('mobile');
			$obj->visa_requirement = $rq->input('visa_requirement');
			$obj->attached_cv = $img;
			$obj->job_opening = $rq->input('job_opening');
			$obj->current_status = $rq->input('status');
			$obj->internal_date = $formatedDate;
			$obj->department = $departmentname;
			$obj->location = $rq->input('location');
			$obj->recruiter = $rq->input('recruiter');
			$obj->designation = $rq->input('designation');
			$obj->bg_description = $rq->input('bg_description');
			$obj->bg_upload = $bg_upload;
			$obj->bgverification_status = $rq->input('bgverification_status');
			if($rq->input('status')==1){
				$obj->status = $rq->input('status');
			}
			$obj->serial_number = $srlnum;
			$obj->save();
			
			$LastId = $obj->id;
			$interview = new InterviewDetailsProcess();
			$interview->interview_id = $LastId;
			$interview->experience = $rq->input('experience');
			$interview->job_knowledge = $rq->input('job_knowledge');
			$interview->Communication_skills = $rq->input('Communication_skills');
			$interview->team_work_abilities = $rq->input('team_work_abilities');
			$interview->presentation = $rq->input('presentation');
			$interview->job_stability = $rq->input('job_stability');
			$interview->other_notes = $rq->input('other_notes');
			$interview->salary = $rq->input('salary');
			$interview->status = $rq->input('status');
			$interview->interviewer_name = $rq->input('interviewer_name');
			$interview->interviewer_tl = $rq->input('interviewer_tl');
			$interview->rating = $rq->input('rating');
			$interview->interview_type = 'Interview1';
			$interview->save();
			$response['code'] = '200';
			$response['id']=$LastId;
			$response['message'] = "Interview Process Save  Successfully.";		   
			echo json_encode($response);
		    exit;
			
		
	}
	public function addInterviewProcessPost(Request $rq)
	{
		if($rq->input('status')==1){
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->status = $rq->input('status');
		$obj->save();
		}
	
			$interview = new InterviewDetailsProcess();
			$interview->interview_id = $rq->input('id');
			$interview->experience = $rq->input('experience');
			$interview->job_knowledge = $rq->input('job_knowledge');
			$interview->Communication_skills = $rq->input('Communication_skills');
			$interview->team_work_abilities = $rq->input('team_work_abilities');
			$interview->presentation = $rq->input('presentation');
			$interview->job_stability = $rq->input('job_stability');
			$interview->other_notes = $rq->input('other_notes');
			$interview->salary = $rq->input('salary');
			$interview->status = $rq->input('status');
			$interview->interview_type = 'Interview1';
			$interview->save();
			
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		   
			echo json_encode($response);
		    exit;
			
		
	}
	public function addInterviewProcessPostStep2(Request $rq)
	{
		
		
		$LastInsertId="updated";
		$keys = array_keys($_FILES);
		
		$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				//print_r($req->file($key));
				if(!empty($rq->file($key)))
				{
					//echo $key;exit;
				$filenameWithExt = $rq->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$rq->file($key)->getClientOriginalExtension();
				$vKey = $key;
				$newFileName = $key.'-interview2-'.$LastInsertId.'.'.$fileExtension;
				
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				
				$extension = $rq->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$rq->file($key)->move(public_path('interviewcv/'), $newFileName);
				$fileIndex++;
				}
				else
				{
					
					$vKey = $keys[$fileIndex];
					$filesAttributeInfo[$vKey] = '';
					$listOfAttribute[] = $vKey;
					$fileIndex++;
					
				}
			}
			if(!empty($newFileName)){
			$img=$newFileName;
			}
			else{
			$img='';
			}
			$interview = new InterviewDetailsProcess();
			$interview->interview_id = $LastInsertId;
			$interview->job_knowledge = $rq->input('job_knowledge');
			$interview->Communication_skills = $rq->input('Communication_skills');
			$interview->team_work_abilities = $rq->input('team_work_abilities');
			$interview->presentation = $rq->input('presentation');
			$interview->job_stability = $rq->input('job_stability');
			$interview->other_notes = $rq->input('other_notes');
			$interview->visa_requirement = $rq->input('visa_requirement');
			$interview->attached_cv = $img;
			$interview->job_opening = $rq->input('job_opening');
			$interview->salary = $rq->input('salary');
			$interview->status = $rq->input('status');
			$interview->interview_type = 'Interview2';
			$interview->save();
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		   
			echo json_encode($response);
		    exit;
			
		
	}
	public function addInterviewProcessPostStep3(Request $rq)
	{
		
		$obj = new InterviewProcess();
		$obj->name = $rq->input('name');
		$obj->mobile = $rq->input('mobile');
		$obj->experience = $rq->input('experience');
		$obj->save();
		$LastInsertId = $obj->id;
		$keys = array_keys($_FILES);
		
		$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				//print_r($req->file($key));
				if(!empty($rq->file($key)))
				{
					//echo $key;exit;
				$filenameWithExt = $rq->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$rq->file($key)->getClientOriginalExtension();
				$vKey = $key;
				$newFileName = $key.'-interview3-'.$LastInsertId.'.'.$fileExtension;
				
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				
				$extension = $rq->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$rq->file($key)->move(public_path('interviewcv/'), $newFileName);
				$fileIndex++;
				}
				else
				{
					
					$vKey = $keys[$fileIndex];
					$filesAttributeInfo[$vKey] = '';
					$listOfAttribute[] = $vKey;
					$fileIndex++;
					
				}
			}
			if(!empty($newFileName)){
			$img=$newFileName;
			}
			else{
			$img='';
			}
			$interview = new InterviewDetailsProcess();
			$interview->interview_id = $LastInsertId;
			$interview->job_knowledge = $rq->input('job_knowledge');
			$interview->Communication_skills = $rq->input('Communication_skills');
			$interview->team_work_abilities = $rq->input('team_work_abilities');
			$interview->presentation = $rq->input('presentation');
			$interview->job_stability = $rq->input('job_stability');
			$interview->other_notes = $rq->input('other_notes');
			$interview->visa_requirement = $rq->input('visa_requirement');
			$interview->attached_cv = $img;
			$interview->job_opening = $rq->input('job_opening');
			$interview->salary = $rq->input('salary');
			$interview->status = $rq->input('status');
			$interview->interview_type = 'Interview3';
			$interview->save();
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		   
			echo json_encode($response);
		    exit;
			
		
	}
	public function addInterviewProcessPostStep4(Request $rq)
	{
		
		$obj = new InterviewProcess();
		$obj->name = $rq->input('name');
		$obj->mobile = $rq->input('mobile');
		$obj->experience = $rq->input('experience');
		$obj->save();
		$LastInsertId = $obj->id;
		$keys = array_keys($_FILES);
		
		$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				//print_r($req->file($key));
				if(!empty($rq->file($key)))
				{
					//echo $key;exit;
				$filenameWithExt = $rq->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$rq->file($key)->getClientOriginalExtension();
				$vKey = $key;
				$newFileName = $key.'-interview4-'.$LastInsertId.'.'.$fileExtension;
				
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				
				$extension = $rq->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$rq->file($key)->move(public_path('interviewcv/'), $newFileName);
				$fileIndex++;
				}
				else
				{
					
					$vKey = $keys[$fileIndex];
					$filesAttributeInfo[$vKey] = '';
					$listOfAttribute[] = $vKey;
					$fileIndex++;
					
				}
			}
			if(!empty($newFileName)){
			$img=$newFileName;
			}
			else{
			$img='';
			}
			$interview = new InterviewDetailsProcess();
			$interview->interview_id = $LastInsertId;
			$interview->job_knowledge = $rq->input('job_knowledge');
			$interview->Communication_skills = $rq->input('Communication_skills');
			$interview->team_work_abilities = $rq->input('team_work_abilities');
			$interview->presentation = $rq->input('presentation');
			$interview->job_stability = $rq->input('job_stability');
			$interview->other_notes = $rq->input('other_notes');
			$interview->visa_requirement = $rq->input('visa_requirement');
			$interview->attached_cv = $img;
			$interview->job_opening = $rq->input('job_opening');
			$interview->salary = $rq->input('salary');
			$interview->status = $rq->input('status');
			$interview->interview_type = 'final discussion';
			$interview->save();
			$response['code'] = '200';
			$response['message'] = "Interview Process Save  Successfully.";		   
			echo json_encode($response);
		    exit;
			
		
	}
	public function setOffSetForInterviewProcess(Request $request)
			{
				echo $offset = $request->offset;
				$request->session()->put('offset_hiring_filter',$offset);
				 //return  redirect('visaTypeList');
			}
	public function interviewList(Request $request){
		
			if(!empty($request->session()->get('offset_hiring_filter')))
				{
					$paginationValue = $request->session()->get('offset_hiring_filter');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				
				 
				 $selectedFilter['name'] = '';
				 $selectedFilter['mobile'] = '';
				 $selectedFilter['CurrentInterview'] = '';
				 $selectedFilter['CurrentStatus'] = '';
				 $selectedFilter['InterviewDate'] = '';
				 $selectedFilter['SerialNumber'] = '';
				 $selectedFilter['job']= '';
				 $selectedFilter['recruiter']= '';
				 
				 
				 
				 
				 if(!empty($request->session()->get('interview_SerialNumber_filter_inner_list')) && $request->session()->get('interview_SerialNumber_filter_inner_list') != 'All')
				{
					$SerialNumber = $request->session()->get('interview_SerialNumber_filter_inner_list');
					 $selectedFilter['SerialNumber'] = $SerialNumber;
					 if($whereraw == '')
					{
						$whereraw = 'serial_number = "'.$SerialNumber.'"';
					}
					else
					{
						$whereraw .= ' And serial_number = "'.$SerialNumber.'"';
					}
				}
				 if(!empty($request->session()->get('interview_name_filter_inner_list')) && $request->session()->get('interview_name_filter_inner_list') != 'All')
				{
					$name = $request->session()->get('interview_name_filter_inner_list');
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						$whereraw = 'name like "%'.$name.'%"';
					}
					else
					{
						$whereraw .= ' And name like "%'.$name.'%"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('interview_mobile_filter_inner_list')) && $request->session()->get('interview_mobile_filter_inner_list') != 'All')
				{
					$mobile = $request->session()->get('interview_mobile_filter_inner_list');
					 $selectedFilter['mobile'] = $mobile;
					 if($whereraw == '')
					{
						$whereraw = 'mobile like "%'.$mobile.'%"';
					}
					else
					{
						$whereraw .= ' And mobile like "%'.$mobile.'%"';
					}
				}
				if(!empty($request->session()->get('interview_currentinterview_filter_inner_list')) && $request->session()->get('interview_currentinterview_filter_inner_list') != 'All')
				{
					$currentinterview = $request->session()->get('interview_currentinterview_filter_inner_list');
					 $selectedFilter['CurrentInterview'] = $currentinterview;
					 if($whereraw == '')
					{
						$interview= InterviewDetailsProcess::where('interview_type',$currentinterview)->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewarr[]=$_inter->interview_id;
						}
						$interviewdetails=implode(",",$interviewarr);
						$whereraw = 'id IN('.$interviewdetails.')';
						
					}
					else
					{
						$interview= InterviewDetailsProcess::where('interview_type',$currentinterview)->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewarr[]=$_inter->interview_id;
						}
						$interviewdetails=implode(",",$interviewarr);
						$whereraw .= ' And id IN('.$interviewdetails.')';
					}
				}
				if(!empty($request->session()->get('interview_currentstatus_filter_inner_list')) && $request->session()->get('interview_currentstatus_filter_inner_list') != 'All')
				{
					$currentstatus = $request->session()->get('interview_currentstatus_filter_inner_list');
					 $selectedFilter['CurrentStatus'] = $currentstatus;
					 if($whereraw == '')
					{
						$whereraw = 'current_status = "'.$currentstatus.'"';
					}
					else
					{
						$whereraw .= ' And current_status = "'.$currentstatus.'"';
					}
				}
				if(!empty($request->session()->get('interview_currentdate_filter_inner_list')) && $request->session()->get('interview_currentdate_filter_inner_list') != 'All')
				{
					$currentdate = $request->session()->get('interview_currentdate_filter_inner_list');
					 $selectedFilter['InterviewDate'] = $currentdate;
					 if($whereraw == '')
					{
						$whereraw = 'internal_date = "'.$currentdate.'"';
					}
					else
					{
						$whereraw .= ' And internal_date = "'.$currentdate.'"';
					}
				}
				if(!empty($request->session()->get('interview_jobopning_filter_inner_list')) && $request->session()->get('interview_jobopning_filter_inner_list') != 'All')
				{
					$jobopning = $request->session()->get('interview_jobopning_filter_inner_list');
					 $selectedFilter['job'] = $jobopning;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening = "'.$jobopning.'"';
					}
					else
					{
						$whereraw .= ' And job_opening = "'.$jobopning.'"';
					}
				}
				if(!empty($request->session()->get('interview_recruiter_filter_inner_list')) && $request->session()->get('interview_recruiter_filter_inner_list') != 'All')
				{
					$recruiter = $request->session()->get('interview_recruiter_filter_inner_list');
					 $selectedFilter['recruiter'] = $recruiter;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter = "'.$recruiter.'"';
					}
					else
					{
						$whereraw .= ' And recruiter = "'.$recruiter.'"';
					}
				}
				
				
				//echo $whereraw;//exit;
				$nameArray = array();
				if($whereraw == '')
				{
				$name = InterviewProcess::get();
				}
				else
				{
					
					$name = InterviewProcess::whereRaw($whereraw)->get();
					
				}
				//echo $whereraw;exit;
				foreach($name as $_name)
				{
					//echo $_f->first_name;exit;
					$nameArray[$_name->name] = $_name->name;
				}
				
				//print_r();exit;
				$mobileArray = array();
				if($whereraw == '')
				{
				$mobile = InterviewProcess::get();
				}
				else
				{
					
					$mobile = InterviewProcess::whereRaw($whereraw)->get();
					
				}
				
				foreach($mobile as $_mobile)
				{
					//echo $_lname->last_name;exit;
					$mobileArray[$_mobile->mobile] = $_mobile->mobile;
				}
				
				$CurrentInterviewArray = array();
				
				if($whereraw == '')
				{
				$interview1 = InterviewProcess::get();
				$CurrentInterview =array();
				
				foreach($interview1 as $_data){
				$CurrentInterview[]=$_data->id;
				}
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->get();
					$CurrentInterview =array();
					foreach($interview1 as $_data){
					$CurrentInterview[]=$_data->id;
				}
				}
				$finaldata=array();
				foreach($CurrentInterview as $val){
				$data=InterviewDetailsProcess::where("interview_id",$val)->orderBy("id","DESC")->first();
				if($data!=''){
				$finaldata[$val]=$data->interview_type;
				}
				//InterviewDetailsProcess::where('interview_id',$id)->orderBy("id","DESC")->first();
				}
				$finaldata=array_unique($finaldata);
				foreach($finaldata as $key=>$_interview1)
				{
					//echo $_lname->last_name;exit;
					$CurrentInterviewArray[$_interview1] = $_interview1;
				}
					
				$CurrentStatusArray = array();
				if($whereraw == '')
				{
				$interview1 = InterviewProcess::get();
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->get();
					
				}
				
				
				foreach($interview1 as $_interviewstatus)
				{
					//echo $_lname->last_name;exit;
					$CurrentStatusArray[$_interviewstatus->current_status] = $_interviewstatus->current_status;
				}
				
				$InterviewDateArray = array();
				if($whereraw == '')
				{
					
				$internaldate = InterviewProcess::get();
				}
				else
				{
					
					$internaldate = InterviewProcess::whereRaw($whereraw)->get();
					
				}
				//print_r($date);exit;
				
				foreach($internaldate as $_date)
				{
					
					//echo $_lname->last_name;exit;
					$InterviewDateArray[$_date->internal_date] = $_date->internal_date;
				}
				$SerialNumberArray = array();
				if($whereraw == '')
				{
					
				$serl = InterviewProcess::get();
				}
				else
				{
					
					$serl = InterviewProcess::whereRaw($whereraw)->get();
					
				}
				//print_r($date);exit;
				
				foreach($serl as $_serl)
				{
					
					//echo $_lname->last_name;exit;
					$SerialNumberArray[$_serl->serial_number] = $_serl->serial_number;
				}
				
				$JobOpningArray = array();
				if($whereraw == '')
				{
					
				$job = InterviewProcess::get();
				$jobdata=array();
				foreach($job as $jobval){
				if($jobval->job_opening !=''){	
				$jobdata[]=$jobval->job_opening;
				}
				}
				}
				else
				{
					$job = InterviewProcess::whereRaw($whereraw)->get();
					$jobdata=array();
					foreach($job as $jobval){
					if($jobval->job_opening !=''){	
					$jobdata[]=$jobval->job_opening;
					}
					}
				}
				$finaldata=array_unique($jobdata);
				
				foreach($finaldata as $_job)
				{
					
					$data = JobOpening::where('id',$_job)->first();
					$JobOpningArray[$data->id] = $data->name;
				}
				
				$recruiterArray = array();
				if($whereraw == '')
				{
					
				$recruiter = InterviewProcess::get();
				$recruiterdata=array();
				foreach($recruiter as $recruiterval){
				if($recruiterval->recruiter !=''){	
				$recruiterdata[]=$recruiterval->recruiter;
				}
				}
				}
				else
				{
					$recruiter = InterviewProcess::whereRaw($whereraw)->get();
					$recruiterdata=array();
					foreach($recruiter as $recruiterval){
					if($recruiterval->recruiter !=''){	
					$recruiterdata[]=$recruiterval->recruiter;
					}
					}
				}
				$finaldatar=array_unique($recruiterdata);
				
				foreach($finaldatar as $_recruiter)
				{
					
					$datar = RecruiterDetails::where('id',$_recruiter)->first();
					$recruiterArray[$datar->id] = $datar->name;
				}
				
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 96 || $empsessionId== 97){
					$interviewarr=array(9);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'department IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND department IN('.$interviewdetails.')';
					}
				}
				else if($empsessionId== 94 || $empsessionId== 95){
					$interviewarr=array(8,36,43);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'department IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND department IN('.$interviewdetails.')';
					}
				}
				else{
					/*nothings to do*/
				}
				
				if($whereraw != '')
				{
					$InterviewList = InterviewProcess::whereRaw($whereraw)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCount = InterviewProcess::whereRaw($whereraw)->get()->count();	
				}
				else{
					$InterviewList = InterviewProcess::orderBy("id","DESC")->paginate($paginationValue);
					$reportsCount = InterviewProcess::get()->count();	
				}					
				
				
				$InterviewList->setPath(config('app.url/interviewList'));
				
				
				
				
			
			return view("InterviewProcess/InterviewListingAll",compact('recruiterArray','JobOpningArray','SerialNumberArray','InterviewDateArray','CurrentStatusArray','CurrentInterviewArray','InterviewList','paginationValue','reportsCount','selectedFilter','mobileArray','nameArray'));
		}
		
		public function filterByInterviewSerialNumber(Request $request)
		{
			$SerialNumber = $request->SerialNumber;
			$request->session()->put('interview_SerialNumber_filter_inner_list',$SerialNumber);	
		}
		public function setFilterbyInterviewName(Request $request)
		{
			$name = $request->name;
			$request->session()->put('interview_name_filter_inner_list',$name);	
		}
		public function setfilterByInterviewMobile(Request $request)
		{
			$mobile = $request->mobile;
			$request->session()->put('interview_mobile_filter_inner_list',$mobile);	
		}
		public function filterByCurrentInterview(Request $request)
		{
			$currentinterview = $request->currentinterview;
			$request->session()->put('interview_currentinterview_filter_inner_list',$currentinterview);	
		}
		public function filterByCurrentInterviewStatus(Request $request)
		{
			$currentstatus = $request->currentstatus;
			$request->session()->put('interview_currentstatus_filter_inner_list',$currentstatus);	
		}
		public function filterByInterviewDate(Request $request)
		{
			$currentdate = $request->currentdate;
			$request->session()->put('interview_currentdate_filter_inner_list',$currentdate);	
		}
		
		public function filterByJobOpningInterview(Request $request)
		{
			$jobopning = $request->jobopning;
			$request->session()->put('interview_jobopning_filter_inner_list',$jobopning);	
		}
		public function filterByRecruiterInterview(Request $request)
		{
			$recruiter = $request->recruiter;
			$request->session()->put('interview_recruiter_filter_inner_list',$recruiter);	
		}
		
		public static function getInterviewExp($id)
			{	
			
			  $data = InterviewDetailsProcess::where('interview_id',$id)->orderBy("id","DESC")->first();
			  if($data != '')
			  {
			  return $data->experience;
			  }
			  else
			  {
			  return '';
			  }
			}
		public static function getcurrentInterview($id)
			{	

			  $data = InterviewDetailsProcess::where('interview_id',$id)->orderBy("id","DESC")->first();
			  if($data != '')
			  {
			  return $data->interview_type;
			  }
			  else
			  {
			  return '';
			  }
			}
			public static function getcurrentstatus($id)
			{	
			
			  $data = InterviewDetailsProcess::where('interview_id',$id)->orderBy("id","DESC")->first();
			  if($data != '')
			  {
				if($data->status==1){$st="Reject";} else{$st="Accept";}
			  return $st;
			  }
			  else
			  {
			  return '';
			  }
			}
			public static function getVisarequriment($id)
			{	
			
			  $data = visaType::where('id',$id)->first();
			  if($data != '')
			  {
				
			  return $data->title;
			  }
			  else
			  {
			  return '';
			  }
			}
			public static function getJobOpning($id)
			{	
			
			  $data = JobOpening::where('id',$id)->first();
			  if($data != '')
			  {
				
			  return $data->name;
			  }
			  else
			  {
			  return '';
			  }
			}
			public static function getDepartmentName($id)
			{	
			
			  $data = Department::where('id',$id)->first();
			  if($data != '')
			  {
				
			  return $data->department_name;
			  }
			  else
			  {
			  return '';
			  }
			}
			public static function getDepartmentNameByJOb($id)
			{	
				$job = JobOpening::where('id',$id)->first();
				if($job!=''){
				$deptid=$job->department;
			  $data = Department::where('id',$deptid)->first();
			  if($data != '')
			  {
				
			  return $data->department_name;
			  }
			  else
			  {
			  return '';
			  }
				}
			}
			public static function getDesignationName($nam)
			{	
				$data = Employee_details::where("id",$nam)->first();
			  //$data = Department::where('id',$id)->first();
			  if($data != '')
			  {
				
			  return $data->first_name.' '.$data->middle_name.' '.$data->last_name;
			  }
			  else
			  {
			  return '';
			  }
			}
			public static function getconsultancyName($nam)
			{	
				$data = ConsultancyModel::where("id",$nam)->first();
			  //$data = Department::where('id',$id)->first();
			  if($data != '')
			  {
				
			  return $data->consultancy_name;
			  }
			  else
			  {
			  return '';
			  }
			}
		
		
		
	
	public function deleteVisaType(Request $req)
	{
		$visaType_obj = visaType::find($req->id);
       
        $visaType_obj->status = 3;
       
        $visaType_obj->save();
        $req->session()->flash('message','Visa Type deleted Successfully.');
        //return redirect('VisaTypeList');
		$response['code'] = '200';
		  $response['message'] = "Visa Type deleted Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
	}
	
	public function UpdateInterviewForm($rowId=NULL)
	{
		$tL_details=array();
		$jobOpning=array();
		$deptid='';
		$interviewdetail1 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview1')->first();
			
		$interviewdetail2 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview2')->first();
		$interviewdetail3 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview3')->first();
		$interviewdetail4 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'final discussion')->first();
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$interviewList= InterviewProcess::where("id",$rowId)->first();
		$jobid=$interviewList->job_opening;
		$locationId=$interviewList->location;
		if($jobid!=''){
		$jobOpningdata=JobOpening::where('id',$jobid)->where("status",1)->first();
		$deptid=$jobOpningdata->department;
		
		}
		
		$tL_details = ConsultancyModel::where('status',1)->get();
		//$tL_details = Employee_details::where("dept_id",$deptid)->whereIn("job_role",$array)->get();
		if(!empty($locationId)){
		$jobOpning=JobOpening::where('location',$locationId)->where("status",1)->get();
		}
		$visaTypeList = visaType::whereIn('id',array(7,8,3,4,2))->where("status",1)->orderBy("id","DESC")->get();
		//$tL_details = Employee_details::where("dept_id",$dept)->where("job_role","Team Leader")->orderBy("id","ASC")->get();
		$array=array();
		$array[]='Sales Executive';
		$array[]='Team Leader';
		$SalesExecutive = Employee_details::whereIn("job_role",$array)->get();
		
		//print_r($interviewdetail1);exit;
		return view("InterviewProcess/UpdateInterviewForm",compact('SalesExecutive','deptid','tL_details','jobRecruiterDetails','jobOpning','visaTypeList','interviewdetail1','interviewdetail2','interviewdetail3','interviewdetail4','interviewList'));
	}
	public function updateInterviewProcessPost0(Request $rq)
	{
		$keys = array_keys($_FILES);
					
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			$LastInsertId=date("Y-m-d_h-i-s");
			foreach($keys as $key)
			{
				
				if(!empty($rq->file($key)))
				{
				$filenameWithExt = $rq->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$rq->file($key)->getClientOriginalExtension();
				$vKey = $key;
				$newFileName = $key.'-interview-'.$LastInsertId.'.'.$fileExtension;
				if(file_exists(public_path('interviewcv/'.$newFileName))){

					  unlink(public_path('interviewcv/'.$newFileName));

					}
				/*
				*Updating File Name
				*/
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $rq->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$rq->file($key)->move(public_path('interviewcv/'), $newFileName);
				$fileIndex++;
				}
				else
				{
					
					$vKey = $keys[$fileIndex];
					$filesAttributeInfo[$vKey] = '';
					$listOfAttribute[] = $vKey;
					$fileIndex++;
					
				}
			}
		if(!empty($newFileName)){
			$img=$newFileName;
			}
			else{
			$img=$rq->input('img');
			}
			$jobOpning=JobOpening::where("id",$rq->input('job_opening'))->first();
			$departmentname=$jobOpning->department;
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->name = $rq->input('name');
		$obj->mobile = $rq->input('mobile');
		$obj->visa_requirement = $rq->input('visa_requirement');
		$obj->attached_cv = $img;
		$obj->job_opening = $rq->input('job_opening');
		$obj->location = $rq->input('location');
		$obj->recruiter = $rq->input('recruiter');
		$obj->department = $departmentname;
		$obj->designation = $rq->input('designation');
		$obj->save();
		
		$rq->session()->flash('message','Update Saved Successfully.');
		$response['code'] = '200';
		  $response['message'] = "Update Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
		
	}
	public function updateInterviewProcessPost(Request $rq)
	{
		
		if($rq->input('status')==1){
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 1;
		$obj->save();
		}
		if($rq->input('status')==2){
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 1;
		$obj->save();
		}
		$interviewdetails =InterviewDetailsProcess::where("interview_id",$rq->input('id'))->where("interview_type",'Interview1')->first();
		if($interviewdetails!=''){
			$detId=$interviewdetails->id;
			$detailsObj = InterviewDetailsProcess::find($detId);
			$detailsObj->experience = $rq->input('experience');
			$detailsObj->job_knowledge = $rq->input('job_knowledge');
			$detailsObj->Communication_skills = $rq->input('Communication_skills');
			$detailsObj->team_work_abilities = $rq->input('team_work_abilities');
			$detailsObj->presentation = $rq->input('presentation');
			$detailsObj->job_stability = $rq->input('job_stability');
			$detailsObj->other_notes = $rq->input('other_notes');
			
			$detailsObj->salary = $rq->input('salary');
			$detailsObj->status = $rq->input('status');
			$detailsObj->interviewer_name = $rq->input('interviewer_name');
			$detailsObj->interviewer_tl = $rq->input('interviewer_tl');
			$detailsObj->rating = $rq->input('rating');
			$detailsObj->save();
		}
		else{
			$interview = new InterviewDetailsProcess();
			$interview->interview_id = $rq->input('id');
			$interview->experience = $rq->input('experience');
			$interview->job_knowledge = $rq->input('job_knowledge');
			$interview->Communication_skills = $rq->input('Communication_skills');
			$interview->team_work_abilities = $rq->input('team_work_abilities');
			$interview->presentation = $rq->input('presentation');
			$interview->job_stability = $rq->input('job_stability');
			$interview->other_notes = $rq->input('other_notes');
			$interview->salary = $rq->input('salary');
			$interview->status = $rq->input('status');
			$interview->interviewer_name = $rq->input('interviewer_name');
			$interview->interviewer_tl = $rq->input('interviewer_tl');
			$interview->rating = $rq->input('rating');
			$interview->interview_type = 'Interview1';
			$interview->save();
		}
		
		
		
		
		
		
		$rq->session()->flash('message','Update Saved Successfully.');
		$response['code'] = '200';
		  $response['message'] = "Update Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
		
	}
	public function updateInterviewProcessPostStep2(Request $rq)
	{
		
		if($rq->input('status')==1){
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 1;
		$obj->save();
		}
		if($rq->input('status')==2){
		$obj = InterviewProcess::find($rq->input('id'));
		//$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 1;
		$obj->save();
		}
		
			
		$interviewdetails =InterviewDetailsProcess::where("interview_id",$rq->input('id'))->where("interview_type",'Interview2')->first();
		if($interviewdetails!=''){
			$detId=$interviewdetails->id;
			$detailsObj = InterviewDetailsProcess::find($detId);
			$detailsObj->experience = $rq->input('experience');
			$detailsObj->job_knowledge = $rq->input('job_knowledge');
			$detailsObj->Communication_skills = $rq->input('Communication_skills');
			$detailsObj->team_work_abilities = $rq->input('team_work_abilities');
			$detailsObj->presentation = $rq->input('presentation');
			$detailsObj->job_stability = $rq->input('job_stability');
			$detailsObj->other_notes = $rq->input('other_notes');
			$detailsObj->salary = $rq->input('salary');
			$detailsObj->status = $rq->input('status');
			$detailsObj->interviewer_name = $rq->input('interviewer_name');
			$detailsObj->interviewer_tl = $rq->input('interviewer_tl');
			$detailsObj->rating = $rq->input('rating');
			
			$detailsObj->save();
		}
		else{
			$interview = new InterviewDetailsProcess();
			$interview->interview_id = $rq->input('id');
			$interview->experience = $rq->input('experience');
			$interview->job_knowledge = $rq->input('job_knowledge');
			$interview->Communication_skills = $rq->input('Communication_skills');
			$interview->team_work_abilities = $rq->input('team_work_abilities');
			$interview->presentation = $rq->input('presentation');
			$interview->job_stability = $rq->input('job_stability');
			$interview->other_notes = $rq->input('other_notes');
			$interview->salary = $rq->input('salary');
			$interview->status = $rq->input('status');
			$interview->interviewer_name = $rq->input('interviewer_name');
			$interview->interviewer_tl = $rq->input('interviewer_tl');
			$interview->rating = $rq->input('rating');
			$interview->interview_type = 'Interview2';
			$interview->save();
		}
		
		
		
		
		
		
		$rq->session()->flash('message','Update Saved Successfully.');
		$response['code'] = '200';
		  $response['message'] = "Update Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
		
	}
public function updateInterviewProcessPostStep3(Request $rq)
	{
		
		if($rq->input('status')==1){
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 1;
		$obj->save();
		}
		if($rq->input('status')==2){
		$obj = InterviewProcess::find($rq->input('id'));
		//$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 1;
		$obj->save();
		}
		
		$interviewdetails =InterviewDetailsProcess::where("interview_id",$rq->input('id'))->where("interview_type",'Interview3')->first();
		if($interviewdetails!=''){
			$detId=$interviewdetails->id;
			$detailsObj = InterviewDetailsProcess::find($detId);
			$detailsObj->experience = $rq->input('experience');
			$detailsObj->job_knowledge = $rq->input('job_knowledge');
			$detailsObj->Communication_skills = $rq->input('Communication_skills');
			$detailsObj->team_work_abilities = $rq->input('team_work_abilities');
			$detailsObj->presentation = $rq->input('presentation');
			$detailsObj->job_stability = $rq->input('job_stability');
			$detailsObj->other_notes = $rq->input('other_notes');
			$detailsObj->salary = $rq->input('salary');
			$detailsObj->status = $rq->input('status');
			$detailsObj->interviewer_name = $rq->input('interviewer_name');
			$detailsObj->interviewer_tl = $rq->input('interviewer_tl');
			$detailsObj->rating = $rq->input('rating');
			$detailsObj->save();
		}
		else{
			$interview = new InterviewDetailsProcess();
			$interview->interview_id = $rq->input('id');
			$interview->experience = $rq->input('experience');
			$interview->job_knowledge = $rq->input('job_knowledge');
			$interview->Communication_skills = $rq->input('Communication_skills');
			$interview->team_work_abilities = $rq->input('team_work_abilities');
			$interview->presentation = $rq->input('presentation');
			$interview->job_stability = $rq->input('job_stability');
			$interview->other_notes = $rq->input('other_notes');
			$interview->salary = $rq->input('salary');
			$interview->status = $rq->input('status');
			$interview->interviewer_name = $rq->input('interviewer_name');
			$interview->interviewer_tl = $rq->input('interviewer_tl');
			$interview->rating = $rq->input('rating');
			$interview->interview_type = 'Interview3';
			$interview->save();
		}
		
		
		
		
		
		
		$rq->session()->flash('message','Update Saved Successfully.');
		$response['code'] = '200';
		  $response['message'] = "Update Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
		
	}
	public function updateInterviewProcessPostStep4(Request $rq)
	{
		//print_r($rq->input());exit;
		$jobOpning=JobOpening::where("id",$rq->input('job_opening'))->first();
		if($jobOpning!=''){
		$departmentname=$jobOpning->department;
		
		}
		else{
			$departmentname=9;
		}
		if($rq->input('status')==1){
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->job_opening =$rq->input('job_opening');
		$obj->department = $departmentname;
		$obj->location = $rq->input('location');
		$obj->interview_type = 'final discussion';
		$obj->save();
		}
		if($rq->input('status')==2){
		$obj = InterviewProcess::find($rq->input('id'));
		//$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->department = $departmentname;
		$obj->location = $rq->input('location');
		$obj->job_opening =$rq->input('job_opening');
		$obj->interview_type = 2;
		$obj->save();
		$data = InterviewProcess::where("id",$rq->input('id'))->first();
		$name=$data->name;
		$mobile=$data->mobile;
		$job=$data->job_opening;
		
		if($job !='')
		{
		$jobOpning=JobOpening::where("id",$job)->first();
		$departmentname=$jobOpning->department;
		$designation=$jobOpning->designation;
		}
		else{
			$departmentname=9;
			$designation=10;
		}
		
		$DocumentCollectionDetails = new DocumentCollectionDetails();
		$DocumentCollectionDetails->emp_name=$name;
		$DocumentCollectionDetails->mobile_no=$mobile;
		$DocumentCollectionDetails->department=$departmentname;
		$DocumentCollectionDetails->designation=$designation;
		$DocumentCollectionDetails->status=1;
		if($data->recruiter!="Team Leader"){
		$DocumentCollectionDetails->recruiter_name=$data->recruiter;
		}
		$DocumentCollectionDetails->job_opening=$job;
		$DocumentCollectionDetails->location=$data->location;
		$DocumentCollectionDetails->tl_se=$data->designation;
		$DocumentCollectionDetails->current_visa_status=$data->visa_requirement;
		$DocumentCollectionDetails->proposed_salary=$rq->input('salary');
		$DocumentCollectionDetails->attachedcv =$data->attached_cv;
		$DocumentCollectionDetails->bgverification_status =$data->bgverification_status;
		$DocumentCollectionDetails->offer_letter_status=1;
		$DocumentCollectionDetails->onboard_status=1;
		$DocumentCollectionDetails->ok_visa=1;
		$DocumentCollectionDetails->backout_status=1;
		$DocumentCollectionDetails->interview_id=$data->id;
		$DocumentCollectionDetails->save();
		$LastInsertId = $DocumentCollectionDetails->id;
		$objDocument = new DocumentCollectionDetailsValues();
		$objDocument->document_collection_id = $LastInsertId;
		$objDocument->attribute_code = 63;
		$objDocument->attribute_value = $data->bg_description;
		$objDocument->save();
		if($data->bg_upload!=''){
		$objDocument1 = new DocumentCollectionDetailsValues();
		$objDocument1->document_collection_id = $LastInsertId;
		$objDocument1->attribute_code = 64;
		$objDocument1->attribute_value = $data->bg_upload;
		$objDocument1->save();
		$imagePath = public_path('interviewcv/'.$data->bg_upload);
		$newPath = public_path('documentCollectionFiles/'.$data->bg_upload);
		File::copy($imagePath, $newPath);
		}

		}
		
			
		$interviewdetails =InterviewDetailsProcess::where("interview_id",$rq->input('id'))->where("interview_type",'final discussion')->first();
		if($interviewdetails!=''){
			$detId=$interviewdetails->id;
			$detailsObj = InterviewDetailsProcess::find($detId);
			$detailsObj->experience = $rq->input('experience');
			$detailsObj->job_knowledge = $rq->input('job_knowledge');
			$detailsObj->Communication_skills = $rq->input('Communication_skills');
			$detailsObj->team_work_abilities = $rq->input('team_work_abilities');
			$detailsObj->presentation = $rq->input('presentation');
			$detailsObj->job_stability = $rq->input('job_stability');
			$detailsObj->other_notes = $rq->input('other_notes');
			
			$detailsObj->salary = $rq->input('salary');
			$detailsObj->status = $rq->input('status');
			$detailsObj->interviewer_name = $rq->input('interviewer_name');
			$detailsObj->interviewer_tl = $rq->input('interviewer_tl');
			$detailsObj->rating = $rq->input('rating');
			$detailsObj->save();
		}
		else{
			$interview = new InterviewDetailsProcess();
			$interview->interview_id = $rq->input('id');
			$interview->experience = $rq->input('experience');
			$interview->job_knowledge = $rq->input('job_knowledge');
			$interview->Communication_skills = $rq->input('Communication_skills');
			$interview->team_work_abilities = $rq->input('team_work_abilities');
			$interview->presentation = $rq->input('presentation');
			$interview->job_stability = $rq->input('job_stability');
			$interview->other_notes = $rq->input('other_notes');
			$interview->salary = $rq->input('salary');
			$interview->status = $rq->input('status');
			$interview->interviewer_name = $rq->input('interviewer_name');
			$interview->interviewer_tl = $rq->input('interviewer_tl');
			$interview->rating = $rq->input('rating');
			$interview->interview_type = 'final discussion';
			
			$interview->save();
		}
		
		
		
		
		
		
		$rq->session()->flash('message','Update Saved Successfully.');
		$response['code'] = '200';
		  $response['message'] = "Update Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
		
	}
	public function viewInterviewData($rowId=NULL)
	{
		$interviewdetail1 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview1')->first();
			
		$interviewdetail2 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview2')->first();
		$interviewdetail3 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview3')->first();
		$interviewdetail4 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'final discussion')->first();
		$interviewList= InterviewProcess::where("id",$rowId)->first();
		//print_r($interviewdetail1);exit;
		return view("InterviewProcess/viewInterview",compact('interviewdetail1','interviewdetail2','interviewdetail3','interviewdetail4','interviewList'));
	}
	public function viewInterviewDataComplete($rowId=NULL)
	{
		$interviewdetail1 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview1')->first();
			
		$interviewdetail2 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview2')->first();
		$interviewdetail3 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview3')->first();
		$interviewdetail4 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'final discussion')->first();
		$interviewList= InterviewProcess::where("id",$rowId)->first();
		//print_r($interviewdetail1);exit;
		return view("InterviewProcess/viewInterviewcomplete",compact('interviewdetail1','interviewdetail2','interviewdetail3','interviewdetail4','interviewList'));
	}
	public function checkMobileExit($mobile){
		$mobile= InterviewProcess::where("mobile",$mobile)->first();
		if($mobile != '')
			  {
				
			  $response['code'] = '201';
			  $response['message'] = "Mobile no Already exit.";
				echo json_encode($response);
			   exit;
			  }
			  else
			  {
			  $response['code'] = '200';
			  $response['message'] = "Mobile no not  exit.";
				echo json_encode($response);
			   exit;
			  }
	}
	public function getjobOpningData($location=NULL){
		$jobOpning=JobOpening::where('location',$location)->where("status",1)->get();
		return view("InterviewProcess/DropdownForm",compact('jobOpning'));
	}
	public function updatejobOpningData($location=NULL){
		$jobOpning=JobOpening::where('location',$location)->where("status",1)->get();
		return view("InterviewProcess/UpdateDropdownForm",compact('jobOpning'));
	}
	public function getDepartmentid($dept){
		$jobOpning=JobOpening::where('id',$dept)->where("status",1)->first();
		$design=$jobOpning->designation;
		$designation= Designation::where("id",$design)->first();
		$response['code'] = '200';
		  $response['dept'] =$jobOpning->department;
		  $response['design'] =$designation->name;
			echo json_encode($response);
		   exit;
	}
	public static function getdesignName($job){
		$jobOpning=JobOpening::where('id',$job)->where("status",1)->first();
		if($jobOpning !=''){
		$design=$jobOpning->designation;
		$designation= Designation::where("id",$design)->first();
		if($designation!=''){
			return $designation->name;
			  }
			  else
			  {
			  return '';
			  }
			
		}
		
	}
	public function getempdetalbyinterviewSE($dept=NULL){
		$array=array();
		$array[]='SALES MANAGER';
		$array[]='Team Leader';
		$tL_details = ConsultancyModel::where('status',1)->get();
		//==echo "<pre>";
				//print_r($tL_details);exit;	
		return view("InterviewProcess/DropdownFormDept",compact('tL_details'));
	}
	public function getempdetalbyinterviewTL($dept=NULL){
		$array=array();
		$array[]='SALES MANAGER';
		$array[]='Team Leader';
		$tL_details = ConsultancyModel::where('status',1)->get();
					
		return view("InterviewProcess/DropdownInterviewerFormDept",compact('tL_details'));
	}
	public function getempdetalbyinterviewSalesExecutive($SalesExecutive=NULL){
		$array=array();
		$array[]='Sales Executive';
		$array[]='Team Leader';
		$SalesExc = Employee_details::whereIn("job_role",$array)->get();
					
		return view("InterviewProcess/DropdownFormSalesExecutive",compact('SalesExc'));
	}
	public function getempdetalbyinterviewerSalesExecutive($SalesExecutive=NULL){
		$array=array();
		$array[]='Sales Executive';
		$array[]='Team Leader';
		$SalesExc = Employee_details::whereIn("job_role",$array)->get();
					
		return view("InterviewProcess/DropdownForminterviewerSalesExecutive",compact('SalesExc'));
	}
	
	
	public static function getInterviewRecruiter($id)
			{	
			$data =RecruiterDetails::where("id",$id)->first();
			  
			  if($data != '')
			  {
				
			  return $data->name;
			  }
			  else
			  {
			  return '';
			  }
			}
	public function interviewListenbd(Request $request){
		
			if(!empty($request->session()->get('offset_hiring_filter')))
				{
					$paginationValue = $request->session()->get('offset_hiring_filter');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				
				 
				 $selectedFilter['name'] = '';
				 $selectedFilter['mobile'] = '';
				 $selectedFilter['CurrentInterview'] = '';
				 $selectedFilter['CurrentStatus'] = '';
				 $selectedFilter['InterviewDate'] = '';
				 $selectedFilter['SerialNumber'] = '';
				 $selectedFilter['job']= '';
				 $selectedFilter['recruiter']= '';
				 
				 
				 
				 
				 if(!empty($request->session()->get('interview_SerialNumber_filter_inner_list')) && $request->session()->get('interview_SerialNumber_filter_inner_list') != 'All')
				{
					$SerialNumber = $request->session()->get('interview_SerialNumber_filter_inner_list');
					 $selectedFilter['SerialNumber'] = $SerialNumber;
					 if($whereraw == '')
					{
						$whereraw = 'serial_number = "'.$SerialNumber.'"';
					}
					else
					{
						$whereraw .= ' And serial_number = "'.$SerialNumber.'"';
					}
				}
				 if(!empty($request->session()->get('interview_name_filter_inner_list')) && $request->session()->get('interview_name_filter_inner_list') != 'All')
				{
					$name = $request->session()->get('interview_name_filter_inner_list');
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						$whereraw = 'name like "%'.$name.'%"';
					}
					else
					{
						$whereraw .= ' And name like "%'.$name.'%"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('interview_mobile_filter_inner_list')) && $request->session()->get('interview_mobile_filter_inner_list') != 'All')
				{
					$mobile = $request->session()->get('interview_mobile_filter_inner_list');
					 $selectedFilter['mobile'] = $mobile;
					 if($whereraw == '')
					{
						$whereraw = 'mobile like "%'.$mobile.'%"';
					}
					else
					{
						$whereraw .= ' And mobile like "%'.$mobile.'%"';
					}
				}
				if(!empty($request->session()->get('interview_currentinterview_filter_inner_list')) && $request->session()->get('interview_currentinterview_filter_inner_list') != 'All')
				{
					$currentinterview = $request->session()->get('interview_currentinterview_filter_inner_list');
					 $selectedFilter['CurrentInterview'] = $currentinterview;
					 if($whereraw == '')
					{
						$interview= InterviewDetailsProcess::where('interview_type',$currentinterview)->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewarr[]=$_inter->interview_id;
						}
						$interviewdetails=implode(",",$interviewarr);
						$whereraw = 'id IN('.$interviewdetails.')';
						
					}
					else
					{
						$interview= InterviewDetailsProcess::where('interview_type',$currentinterview)->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewarr[]=$_inter->interview_id;
						}
						$interviewdetails=implode(",",$interviewarr);
						$whereraw .= ' And id IN('.$interviewdetails.')';
					}
				}
				if(!empty($request->session()->get('interview_currentstatus_filter_inner_list')) && $request->session()->get('interview_currentstatus_filter_inner_list') != 'All')
				{
					$currentstatus = $request->session()->get('interview_currentstatus_filter_inner_list');
					 $selectedFilter['CurrentStatus'] = $currentstatus;
					 if($whereraw == '')
					{
						$whereraw = 'current_status = "'.$currentstatus.'"';
					}
					else
					{
						$whereraw .= ' And current_status = "'.$currentstatus.'"';
					}
				}
				if(!empty($request->session()->get('interview_currentdate_filter_inner_list')) && $request->session()->get('interview_currentdate_filter_inner_list') != 'All')
				{
					$currentdate = $request->session()->get('interview_currentdate_filter_inner_list');
					 $selectedFilter['InterviewDate'] = $currentdate;
					 if($whereraw == '')
					{
						$whereraw = 'internal_date = "'.$currentdate.'"';
					}
					else
					{
						$whereraw .= ' And internal_date = "'.$currentdate.'"';
					}
				}
				if(!empty($request->session()->get('interview_jobopning_filter_inner_list')) && $request->session()->get('interview_jobopning_filter_inner_list') != 'All')
				{
					$jobopning = $request->session()->get('interview_jobopning_filter_inner_list');
					 $selectedFilter['job'] = $jobopning;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening = "'.$jobopning.'"';
					}
					else
					{
						$whereraw .= ' And job_opening = "'.$jobopning.'"';
					}
				}
				if(!empty($request->session()->get('interview_recruiter_filter_inner_list')) && $request->session()->get('interview_recruiter_filter_inner_list') != 'All')
				{
					$recruiter = $request->session()->get('interview_recruiter_filter_inner_list');
					 $selectedFilter['recruiter'] = $recruiter;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter = "'.$recruiter.'"';
					}
					else
					{
						$whereraw .= ' And recruiter = "'.$recruiter.'"';
					}
				}
				
				
				//echo $whereraw;//exit;
				$nameArray = array();
				if($whereraw == '')
				{
				$name = InterviewProcess::where('department',9)->get();
				}
				else
				{
					
					$name = InterviewProcess::whereRaw($whereraw)->where('department',9)->get();
					
				}
				//echo $whereraw;exit;
				foreach($name as $_name)
				{
					//echo $_f->first_name;exit;
					$nameArray[$_name->name] = $_name->name;
				}
				
				//print_r();exit;
				$mobileArray = array();
				if($whereraw == '')
				{
				$mobile = InterviewProcess::where('department',9)->get();
				}
				else
				{
					
					$mobile = InterviewProcess::whereRaw($whereraw)->where('department',9)->get();
					
				}
				
				foreach($mobile as $_mobile)
				{
					//echo $_lname->last_name;exit;
					$mobileArray[$_mobile->mobile] = $_mobile->mobile;
				}
				
				$CurrentInterviewArray = array();
				
				if($whereraw == '')
				{
				$interview1 = InterviewProcess::where('department',9)->get();
				$CurrentInterview =array();
				
				foreach($interview1 as $_data){
				$CurrentInterview[]=$_data->id;
				}
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',9)->get();
					$CurrentInterview =array();
					foreach($interview1 as $_data){
					$CurrentInterview[]=$_data->id;
				}
				}
				$finaldata=array();
				foreach($CurrentInterview as $val){
				$data=InterviewDetailsProcess::where("interview_id",$val)->orderBy("id","DESC")->first();
				if($data!=''){
				$finaldata[$val]=$data->interview_type;
				}
				//InterviewDetailsProcess::where('interview_id',$id)->orderBy("id","DESC")->first();
				}
				$finaldata=array_unique($finaldata);
				foreach($finaldata as $key=>$_interview1)
				{
					//echo $_lname->last_name;exit;
					$CurrentInterviewArray[$_interview1] = $_interview1;
				}
					
				$CurrentStatusArray = array();
				if($whereraw == '')
				{
				$interview1 = InterviewProcess::where('department',9)->get();
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',9)->get();
					
				}
				
				
				foreach($interview1 as $_interviewstatus)
				{
					//echo $_lname->last_name;exit;
					$CurrentStatusArray[$_interviewstatus->current_status] = $_interviewstatus->current_status;
				}
				
				$InterviewDateArray = array();
				if($whereraw == '')
				{
					
				$internaldate = InterviewProcess::where('department',9)->get();
				}
				else
				{
					
					$internaldate = InterviewProcess::whereRaw($whereraw)->where('department',9)->get();
					
				}
				//print_r($date);exit;
				
				foreach($internaldate as $_date)
				{
					
					//echo $_lname->last_name;exit;
					$InterviewDateArray[$_date->internal_date] = $_date->internal_date;
				}
				$SerialNumberArray = array();
				if($whereraw == '')
				{
					
				$serl = InterviewProcess::where('department',9)->get();
				}
				else
				{
					
					$serl = InterviewProcess::whereRaw($whereraw)->where('department',9)->get();
					
				}
				//print_r($date);exit;
				
				foreach($serl as $_serl)
				{
					
					//echo $_lname->last_name;exit;
					$SerialNumberArray[$_serl->serial_number] = $_serl->serial_number;
				}
				
				$JobOpningArray = array();
				if($whereraw == '')
				{
					
				$job = InterviewProcess::where('department',9)->get();
				$jobdata=array();
				foreach($job as $jobval){
				if($jobval->job_opening !=''){	
				$jobdata[]=$jobval->job_opening;
				}
				}
				}
				else
				{
					$job = InterviewProcess::whereRaw($whereraw)->where('department',9)->get();
					$jobdata=array();
					foreach($job as $jobval){
					if($jobval->job_opening !=''){	
					$jobdata[]=$jobval->job_opening;
					}
					}
				}
				$finaldata=array_unique($jobdata);
				
				foreach($finaldata as $_job)
				{
					
					$data = JobOpening::where('id',$_job)->first();
					$JobOpningArray[$data->id] = $data->name;
				}
				
				$recruiterArray = array();
				if($whereraw == '')
				{
					
				$recruiter = InterviewProcess::where('department',9)->get();
				$recruiterdata=array();
				foreach($recruiter as $recruiterval){
				if($recruiterval->recruiter !=''){	
				$recruiterdata[]=$recruiterval->recruiter;
				}
				}
				}
				else
				{
					$recruiter = InterviewProcess::whereRaw($whereraw)->where('department',9)->get();
					$recruiterdata=array();
					foreach($recruiter as $recruiterval){
					if($recruiterval->recruiter !=''){	
					$recruiterdata[]=$recruiterval->recruiter;
					}
					}
				}
				$finaldatar=array_unique($recruiterdata);
				
				foreach($finaldatar as $_recruiter)
				{
					
					$datar = RecruiterDetails::where('id',$_recruiter)->first();
					$recruiterArray[$datar->id] = $datar->name;
				}
				
				if($whereraw != '')
				{
					$InterviewList = InterviewProcess::whereRaw($whereraw)->where('department',9)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountenbd = InterviewProcess::whereRaw($whereraw)->where('department',9)->get()->count();	
				}
				else{
					$InterviewList = InterviewProcess::where('department',9)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountenbd = InterviewProcess::where('department',9)->get()->count();	
				}					
				
				
				$InterviewList->setPath(config('app.url/interviewListenbd'));
				
				
				
				
			
			return view("InterviewProcess/InterviewListingenbd",compact('recruiterArray','JobOpningArray','SerialNumberArray','InterviewDateArray','CurrentStatusArray','CurrentInterviewArray','InterviewList','paginationValue','reportsCountenbd','selectedFilter','mobileArray','nameArray'));
		}
	public function interviewListdeem(Request $request){
		
			if(!empty($request->session()->get('offset_hiring_filter')))
				{
					$paginationValue = $request->session()->get('offset_hiring_filter');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				
				 
				 $selectedFilter['name'] = '';
				 $selectedFilter['mobile'] = '';
				 $selectedFilter['CurrentInterview'] = '';
				 $selectedFilter['CurrentStatus'] = '';
				 $selectedFilter['InterviewDate'] = '';
				 $selectedFilter['SerialNumber'] = '';
				 $selectedFilter['job']= '';
				 $selectedFilter['recruiter']= '';
				 
				 
				 
				 
				 if(!empty($request->session()->get('interview_SerialNumber_filter_inner_list')) && $request->session()->get('interview_SerialNumber_filter_inner_list') != 'All')
				{
					$SerialNumber = $request->session()->get('interview_SerialNumber_filter_inner_list');
					 $selectedFilter['SerialNumber'] = $SerialNumber;
					 if($whereraw == '')
					{
						$whereraw = 'serial_number = "'.$SerialNumber.'"';
					}
					else
					{
						$whereraw .= ' And serial_number = "'.$SerialNumber.'"';
					}
				}
				 if(!empty($request->session()->get('interview_name_filter_inner_list')) && $request->session()->get('interview_name_filter_inner_list') != 'All')
				{
					$name = $request->session()->get('interview_name_filter_inner_list');
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						$whereraw = 'name like "%'.$name.'%"';
					}
					else
					{
						
						$whereraw .= ' And name like "%'.$name.'%"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('interview_mobile_filter_inner_list')) && $request->session()->get('interview_mobile_filter_inner_list') != 'All')
				{
					$mobile = $request->session()->get('interview_mobile_filter_inner_list');
					 $selectedFilter['mobile'] = $mobile;
					 if($whereraw == '')
					{
						$whereraw = 'mobile like "%'.$mobile.'%"';
					}
					else
					{
						$whereraw .= ' And mobile like "%'.$mobile.'%"';
					}
				}
				if(!empty($request->session()->get('interview_currentinterview_filter_inner_list')) && $request->session()->get('interview_currentinterview_filter_inner_list') != 'All')
				{
					$currentinterview = $request->session()->get('interview_currentinterview_filter_inner_list');
					 $selectedFilter['CurrentInterview'] = $currentinterview;
					 if($whereraw == '')
					{
						$interview= InterviewDetailsProcess::where('interview_type',$currentinterview)->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewarr[]=$_inter->interview_id;
						}
						$interviewdetails=implode(",",$interviewarr);
						$whereraw = 'id IN('.$interviewdetails.')';
						
					}
					else
					{
						$interview= InterviewDetailsProcess::where('interview_type',$currentinterview)->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewarr[]=$_inter->interview_id;
						}
						$interviewdetails=implode(",",$interviewarr);
						$whereraw .= ' And id IN('.$interviewdetails.')';
					}
				}
				if(!empty($request->session()->get('interview_currentstatus_filter_inner_list')) && $request->session()->get('interview_currentstatus_filter_inner_list') != 'All')
				{
					$currentstatus = $request->session()->get('interview_currentstatus_filter_inner_list');
					 $selectedFilter['CurrentStatus'] = $currentstatus;
					 if($whereraw == '')
					{
						$whereraw = 'current_status = "'.$currentstatus.'"';
					}
					else
					{
						$whereraw .= ' And current_status = "'.$currentstatus.'"';
					}
				}
				if(!empty($request->session()->get('interview_currentdate_filter_inner_list')) && $request->session()->get('interview_currentdate_filter_inner_list') != 'All')
				{
					$currentdate = $request->session()->get('interview_currentdate_filter_inner_list');
					 $selectedFilter['InterviewDate'] = $currentdate;
					 if($whereraw == '')
					{
						$whereraw = 'internal_date = "'.$currentdate.'"';
					}
					else
					{
						$whereraw .= ' And internal_date = "'.$currentdate.'"';
					}
				}
				if(!empty($request->session()->get('interview_jobopning_filter_inner_list')) && $request->session()->get('interview_jobopning_filter_inner_list') != 'All')
				{
					$jobopning = $request->session()->get('interview_jobopning_filter_inner_list');
					 $selectedFilter['job'] = $jobopning;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening = "'.$jobopning.'"';
					}
					else
					{
						$whereraw .= ' And job_opening = "'.$jobopning.'"';
					}
				}
				if(!empty($request->session()->get('interview_recruiter_filter_inner_list')) && $request->session()->get('interview_recruiter_filter_inner_list') != 'All')
				{
					$recruiter = $request->session()->get('interview_recruiter_filter_inner_list');
					 $selectedFilter['recruiter'] = $recruiter;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter = "'.$recruiter.'"';
					}
					else
					{
						$whereraw .= ' And recruiter = "'.$recruiter.'"';
					}
				}
				
				
				//echo $whereraw;//exit;
				$nameArray = array();
				if($whereraw == '')
				{
				$name = InterviewProcess::where('department',8)->get();
				}
				else
				{
					
					$name = InterviewProcess::whereRaw($whereraw)->where('department',8)->get();
					
				}
				//echo $whereraw;exit;
				foreach($name as $_name)
				{
					//echo $_f->first_name;exit;
					$nameArray[$_name->name] = $_name->name;
				}
				
				//print_r();exit;
				$mobileArray = array();
				if($whereraw == '')
				{
				$mobile = InterviewProcess::where('department',8)->get();
				}
				else
				{
					
					$mobile = InterviewProcess::whereRaw($whereraw)->where('department',8)->get();
					
				}
				
				foreach($mobile as $_mobile)
				{
					//echo $_lname->last_name;exit;
					$mobileArray[$_mobile->mobile] = $_mobile->mobile;
				}
				
				$CurrentInterviewArray = array();
				
				if($whereraw == '')
				{
				$interview1 = InterviewProcess::where('department',8)->get();
				$CurrentInterview =array();
				
				foreach($interview1 as $_data){
				$CurrentInterview[]=$_data->id;
				}
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',8)->get();
					$CurrentInterview =array();
					foreach($interview1 as $_data){
					$CurrentInterview[]=$_data->id;
				}
				}
				$finaldata=array();
				foreach($CurrentInterview as $val){
				$data=InterviewDetailsProcess::where("interview_id",$val)->orderBy("id","DESC")->first();
				$finaldata[$val]=$data->interview_type;
				//InterviewDetailsProcess::where('interview_id',$id)->orderBy("id","DESC")->first();
				}
				$finaldata=array_unique($finaldata);
				foreach($finaldata as $key=>$_interview1)
				{
					//echo $_lname->last_name;exit;
					$CurrentInterviewArray[$_interview1] = $_interview1;
				}
					
				$CurrentStatusArray = array();
				if($whereraw == '')
				{
				$interview1 = InterviewProcess::where('department',8)->get();
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',8)->get();
					
				}
				
				
				foreach($interview1 as $_interviewstatus)
				{
					//echo $_lname->last_name;exit;
					$CurrentStatusArray[$_interviewstatus->current_status] = $_interviewstatus->current_status;
				}
				
				$InterviewDateArray = array();
				if($whereraw == '')
				{
					
				$internaldate = InterviewProcess::where('department',8)->get();
				}
				else
				{
					
					$internaldate = InterviewProcess::whereRaw($whereraw)->where('department',8)->get();
					
				}
				//print_r($date);exit;
				
				foreach($internaldate as $_date)
				{
					
					//echo $_lname->last_name;exit;
					$InterviewDateArray[$_date->internal_date] = $_date->internal_date;
				}
				$SerialNumberArray = array();
				if($whereraw == '')
				{
					
				$serl = InterviewProcess::where('department',8)->get();
				}
				else
				{
					
					$serl = InterviewProcess::whereRaw($whereraw)->where('department',8)->get();
					
				}
				//print_r($date);exit;
				
				foreach($serl as $_serl)
				{
					
					//echo $_lname->last_name;exit;
					$SerialNumberArray[$_serl->serial_number] = $_serl->serial_number;
				}
				
				$JobOpningArray = array();
				if($whereraw == '')
				{
					
				$job = InterviewProcess::where('department',8)->get();
				$jobdata=array();
				foreach($job as $jobval){
				if($jobval->job_opening !=''){	
				$jobdata[]=$jobval->job_opening;
				}
				}
				}
				else
				{
					$job = InterviewProcess::whereRaw($whereraw)->where('department',8)->get();
					$jobdata=array();
					foreach($job as $jobval){
					if($jobval->job_opening !=''){	
					$jobdata[]=$jobval->job_opening;
					}
					}
				}
				$finaldata=array_unique($jobdata);
				
				foreach($finaldata as $_job)
				{
					
					$data = JobOpening::where('id',$_job)->first();
					$JobOpningArray[$data->id] = $data->name;
				}
				
				$recruiterArray = array();
				if($whereraw == '')
				{
					
				$recruiter = InterviewProcess::where('department',8)->get();
				$recruiterdata=array();
				foreach($recruiter as $recruiterval){
				if($recruiterval->recruiter !=''){	
				$recruiterdata[]=$recruiterval->recruiter;
				}
				}
				}
				else
				{
					$recruiter = InterviewProcess::whereRaw($whereraw)->where('department',8)->get();
					$recruiterdata=array();
					foreach($recruiter as $recruiterval){
					if($recruiterval->recruiter !=''){	
					$recruiterdata[]=$recruiterval->recruiter;
					}
					}
				}
				$finaldatar=array_unique($recruiterdata);
				
				foreach($finaldatar as $_recruiter)
				{
					
					$datar = RecruiterDetails::where('id',$_recruiter)->first();
					$recruiterArray[$datar->id] = $datar->name;
				}
				
				if($whereraw != '')
				{
					$InterviewList = InterviewProcess::whereRaw($whereraw)->where('department',8)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountdeem = InterviewProcess::whereRaw($whereraw)->where('department',8)->get()->count();	
				}
				else{
					$InterviewList = InterviewProcess::where('department',8)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountdeem = InterviewProcess::where('department',8)->get()->count();	
				}					
				
				
				$InterviewList->setPath(config('app.url/interviewListdeem'));
				
				
				
				
			
			return view("InterviewProcess/InterviewListingdeem",compact('recruiterArray','JobOpningArray','SerialNumberArray','InterviewDateArray','CurrentStatusArray','CurrentInterviewArray','InterviewList','paginationValue','reportsCountdeem','selectedFilter','mobileArray','nameArray'));
		}
			public function interviewListmashreq(Request $request){
		
			if(!empty($request->session()->get('offset_hiring_filter')))
				{
					$paginationValue = $request->session()->get('offset_hiring_filter');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				
				 
				 $selectedFilter['name'] = '';
				 $selectedFilter['mobile'] = '';
				 $selectedFilter['CurrentInterview'] = '';
				 $selectedFilter['CurrentStatus'] = '';
				 $selectedFilter['InterviewDate'] = '';
				 $selectedFilter['SerialNumber'] = '';
				 $selectedFilter['job']= '';
				 $selectedFilter['recruiter']= '';
				 
				 
				 
				 
				 if(!empty($request->session()->get('interview_SerialNumber_filter_inner_list')) && $request->session()->get('interview_SerialNumber_filter_inner_list') != 'All')
				{
					$SerialNumber = $request->session()->get('interview_SerialNumber_filter_inner_list');
					 $selectedFilter['SerialNumber'] = $SerialNumber;
					 if($whereraw == '')
					{
						$whereraw = 'serial_number = "'.$SerialNumber.'"';
					}
					else
					{
						$whereraw .= ' And serial_number = "'.$SerialNumber.'"';
					}
				}
				 if(!empty($request->session()->get('interview_name_filter_inner_list')) && $request->session()->get('interview_name_filter_inner_list') != 'All')
				{
					$name = $request->session()->get('interview_name_filter_inner_list');
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						$whereraw = 'name like "%'.$name.'%"';
					}
					else
					{
						$whereraw .= ' And name like "%'.$name.'%"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('interview_mobile_filter_inner_list')) && $request->session()->get('interview_mobile_filter_inner_list') != 'All')
				{
					$mobile = $request->session()->get('interview_mobile_filter_inner_list');
					 $selectedFilter['mobile'] = $mobile;
					 if($whereraw == '')
					{
						$whereraw = 'mobile like "%'.$mobile.'%"';
					}
					else
					{
						$whereraw .= ' And mobile like "%'.$mobile.'%"';
					}
				}
				if(!empty($request->session()->get('interview_currentinterview_filter_inner_list')) && $request->session()->get('interview_currentinterview_filter_inner_list') != 'All')
				{
					$currentinterview = $request->session()->get('interview_currentinterview_filter_inner_list');
					 $selectedFilter['CurrentInterview'] = $currentinterview;
					 if($whereraw == '')
					{
						$interview= InterviewDetailsProcess::where('interview_type',$currentinterview)->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewarr[]=$_inter->interview_id;
						}
						$interviewdetails=implode(",",$interviewarr);
						$whereraw = 'id IN('.$interviewdetails.')';
						
					}
					else
					{
						$interview= InterviewDetailsProcess::where('interview_type',$currentinterview)->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewarr[]=$_inter->interview_id;
						}
						$interviewdetails=implode(",",$interviewarr);
						$whereraw .= ' And id IN('.$interviewdetails.')';
					}
				}
				if(!empty($request->session()->get('interview_currentstatus_filter_inner_list')) && $request->session()->get('interview_currentstatus_filter_inner_list') != 'All')
				{
					$currentstatus = $request->session()->get('interview_currentstatus_filter_inner_list');
					 $selectedFilter['CurrentStatus'] = $currentstatus;
					 if($whereraw == '')
					{
						$whereraw = 'current_status = "'.$currentstatus.'"';
					}
					else
					{
						$whereraw .= ' And current_status = "'.$currentstatus.'"';
					}
				}
				if(!empty($request->session()->get('interview_currentdate_filter_inner_list')) && $request->session()->get('interview_currentdate_filter_inner_list') != 'All')
				{
					$currentdate = $request->session()->get('interview_currentdate_filter_inner_list');
					 $selectedFilter['InterviewDate'] = $currentdate;
					 if($whereraw == '')
					{
						$whereraw = 'internal_date = "'.$currentdate.'"';
					}
					else
					{
						$whereraw .= ' And internal_date = "'.$currentdate.'"';
					}
				}
				if(!empty($request->session()->get('interview_jobopning_filter_inner_list')) && $request->session()->get('interview_jobopning_filter_inner_list') != 'All')
				{
					$jobopning = $request->session()->get('interview_jobopning_filter_inner_list');
					 $selectedFilter['job'] = $jobopning;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening = "'.$jobopning.'"';
					}
					else
					{
						$whereraw .= ' And job_opening = "'.$jobopning.'"';
					}
				}
				if(!empty($request->session()->get('interview_recruiter_filter_inner_list')) && $request->session()->get('interview_recruiter_filter_inner_list') != 'All')
				{
					$recruiter = $request->session()->get('interview_recruiter_filter_inner_list');
					 $selectedFilter['recruiter'] = $recruiter;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter = "'.$recruiter.'"';
					}
					else
					{
						$whereraw .= ' And recruiter = "'.$recruiter.'"';
					}
				}
				
				
				//echo $whereraw;//exit;
				$nameArray = array();
				if($whereraw == '')
				{
				$name = InterviewProcess::where('department',36)->get();
				}
				else
				{
					
					$name = InterviewProcess::whereRaw($whereraw)->where('department',36)->get();
					
				}
				//echo $whereraw;exit;
				foreach($name as $_name)
				{
					//echo $_f->first_name;exit;
					$nameArray[$_name->name] = $_name->name;
				}
				
				//print_r();exit;
				$mobileArray = array();
				if($whereraw == '')
				{
				$mobile = InterviewProcess::where('department',36)->get();
				}
				else
				{
					
					$mobile = InterviewProcess::whereRaw($whereraw)->where('department',36)->get();
					
				}
				
				foreach($mobile as $_mobile)
				{
					//echo $_lname->last_name;exit;
					$mobileArray[$_mobile->mobile] = $_mobile->mobile;
				}
				
				$CurrentInterviewArray = array();
				
				if($whereraw == '')
				{
				$interview1 = InterviewProcess::where('department',36)->get();
				$CurrentInterview =array();
				
				foreach($interview1 as $_data){
				$CurrentInterview[]=$_data->id;
				}
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',36)->get();
					$CurrentInterview =array();
					foreach($interview1 as $_data){
					$CurrentInterview[]=$_data->id;
				}
				}
				$finaldata=array();
				foreach($CurrentInterview as $val){
				$data=InterviewDetailsProcess::where("interview_id",$val)->orderBy("id","DESC")->first();
				$finaldata[$val]=$data->interview_type;
				//InterviewDetailsProcess::where('interview_id',$id)->orderBy("id","DESC")->first();
				}
				$finaldata=array_unique($finaldata);
				foreach($finaldata as $key=>$_interview1)
				{
					//echo $_lname->last_name;exit;
					$CurrentInterviewArray[$_interview1] = $_interview1;
				}
					
				$CurrentStatusArray = array();
				if($whereraw == '')
				{
				$interview1 = InterviewProcess::where('department',36)->get();
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',36)->get();
					
				}
				
				
				foreach($interview1 as $_interviewstatus)
				{
					//echo $_lname->last_name;exit;
					$CurrentStatusArray[$_interviewstatus->current_status] = $_interviewstatus->current_status;
				}
				
				$InterviewDateArray = array();
				if($whereraw == '')
				{
					
				$internaldate = InterviewProcess::where('department',36)->get();
				}
				else
				{
					
					$internaldate = InterviewProcess::whereRaw($whereraw)->where('department',36)->get();
					
				}
				//print_r($date);exit;
				
				foreach($internaldate as $_date)
				{
					
					//echo $_lname->last_name;exit;
					$InterviewDateArray[$_date->internal_date] = $_date->internal_date;
				}
				$SerialNumberArray = array();
				if($whereraw == '')
				{
					
				$serl = InterviewProcess::where('department',36)->get();
				}
				else
				{
					
					$serl = InterviewProcess::whereRaw($whereraw)->where('department',36)->get();
					
				}
				//print_r($date);exit;
				
				foreach($serl as $_serl)
				{
					
					//echo $_lname->last_name;exit;
					$SerialNumberArray[$_serl->serial_number] = $_serl->serial_number;
				}
				
				$JobOpningArray = array();
				if($whereraw == '')
				{
					
				$job = InterviewProcess::where('department',36)->get();
				$jobdata=array();
				foreach($job as $jobval){
				if($jobval->job_opening !=''){	
				$jobdata[]=$jobval->job_opening;
				}
				}
				}
				else
				{
					$job = InterviewProcess::whereRaw($whereraw)->where('department',36)->get();
					$jobdata=array();
					foreach($job as $jobval){
					if($jobval->job_opening !=''){	
					$jobdata[]=$jobval->job_opening;
					}
					}
				}
				$finaldata=array_unique($jobdata);
				
				foreach($finaldata as $_job)
				{
					
					$data = JobOpening::where('id',$_job)->first();
					$JobOpningArray[$data->id] = $data->name;
				}
				
				$recruiterArray = array();
				if($whereraw == '')
				{
					
				$recruiter = InterviewProcess::where('department',36)->get();
				$recruiterdata=array();
				foreach($recruiter as $recruiterval){
				if($recruiterval->recruiter !=''){	
				$recruiterdata[]=$recruiterval->recruiter;
				}
				}
				}
				else
				{
					$recruiter = InterviewProcess::whereRaw($whereraw)->where('department',36)->get();
					$recruiterdata=array();
					foreach($recruiter as $recruiterval){
					if($recruiterval->recruiter !=''){	
					$recruiterdata[]=$recruiterval->recruiter;
					}
					}
				}
				$finaldatar=array_unique($recruiterdata);
				
				foreach($finaldatar as $_recruiter)
				{
					
					$datar = RecruiterDetails::where('id',$_recruiter)->first();
					$recruiterArray[$datar->id] = $datar->name;
				}
				
				if($whereraw != '')
				{
					$InterviewList = InterviewProcess::whereRaw($whereraw)->where('department',36)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountmashreq = InterviewProcess::whereRaw($whereraw)->where('department',36)->get()->count();	
				}
				else{
					$InterviewList = InterviewProcess::where('department',36)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountmashreq = InterviewProcess::where('department',36)->get()->count();	
				}					
				
				
				$InterviewList->setPath(config('app.url/interviewListmashreq'));
				
				
				
				
			
			return view("InterviewProcess/InterviewListingmashreq",compact('recruiterArray','JobOpningArray','SerialNumberArray','InterviewDateArray','CurrentStatusArray','CurrentInterviewArray','InterviewList','paginationValue','reportsCountmashreq','selectedFilter','mobileArray','nameArray'));
		}
		public function interviewListaafaq(Request $request){
		
			if(!empty($request->session()->get('offset_hiring_filter')))
				{
					$paginationValue = $request->session()->get('offset_hiring_filter');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				
				 
				 $selectedFilter['name'] = '';
				 $selectedFilter['mobile'] = '';
				 $selectedFilter['CurrentInterview'] = '';
				 $selectedFilter['CurrentStatus'] = '';
				 $selectedFilter['InterviewDate'] = '';
				 $selectedFilter['SerialNumber'] = '';
				 $selectedFilter['job']= '';
				 $selectedFilter['recruiter']= '';
				 
				 
				 
				 
				 if(!empty($request->session()->get('interview_SerialNumber_filter_inner_list')) && $request->session()->get('interview_SerialNumber_filter_inner_list') != 'All')
				{
					$SerialNumber = $request->session()->get('interview_SerialNumber_filter_inner_list');
					 $selectedFilter['SerialNumber'] = $SerialNumber;
					 if($whereraw == '')
					{
						$whereraw = 'serial_number = "'.$SerialNumber.'"';
					}
					else
					{
						$whereraw .= ' And serial_number = "'.$SerialNumber.'"';
					}
				}
				 if(!empty($request->session()->get('interview_name_filter_inner_list')) && $request->session()->get('interview_name_filter_inner_list') != 'All')
				{
					$name = $request->session()->get('interview_name_filter_inner_list');
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						$whereraw = 'name like "%'.$name.'%"';
					}
					else
					{
						$whereraw .= ' And name like "%'.$name.'%"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('interview_mobile_filter_inner_list')) && $request->session()->get('interview_mobile_filter_inner_list') != 'All')
				{
					$mobile = $request->session()->get('interview_mobile_filter_inner_list');
					 $selectedFilter['mobile'] = $mobile;
					 if($whereraw == '')
					{
						$whereraw = 'mobile like "%'.$mobile.'%"';
					}
					else
					{
						$whereraw .= ' And mobile like "%'.$mobile.'%"';
					}
				}
				if(!empty($request->session()->get('interview_currentinterview_filter_inner_list')) && $request->session()->get('interview_currentinterview_filter_inner_list') != 'All')
				{
					$currentinterview = $request->session()->get('interview_currentinterview_filter_inner_list');
					 $selectedFilter['CurrentInterview'] = $currentinterview;
					 if($whereraw == '')
					{
						$interview= InterviewDetailsProcess::where('interview_type',$currentinterview)->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewarr[]=$_inter->interview_id;
						}
						$interviewdetails=implode(",",$interviewarr);
						$whereraw = 'id IN('.$interviewdetails.')';
						
					}
					else
					{
						$interview= InterviewDetailsProcess::where('interview_type',$currentinterview)->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewarr[]=$_inter->interview_id;
						}
						$interviewdetails=implode(",",$interviewarr);
						$whereraw .= ' And id IN('.$interviewdetails.')';
					}
				}
				if(!empty($request->session()->get('interview_currentstatus_filter_inner_list')) && $request->session()->get('interview_currentstatus_filter_inner_list') != 'All')
				{
					$currentstatus = $request->session()->get('interview_currentstatus_filter_inner_list');
					 $selectedFilter['CurrentStatus'] = $currentstatus;
					 if($whereraw == '')
					{
						$whereraw = 'current_status = "'.$currentstatus.'"';
					}
					else
					{
						$whereraw .= ' And current_status = "'.$currentstatus.'"';
					}
				}
				if(!empty($request->session()->get('interview_currentdate_filter_inner_list')) && $request->session()->get('interview_currentdate_filter_inner_list') != 'All')
				{
					$currentdate = $request->session()->get('interview_currentdate_filter_inner_list');
					 $selectedFilter['InterviewDate'] = $currentdate;
					 if($whereraw == '')
					{
						$whereraw = 'internal_date = "'.$currentdate.'"';
					}
					else
					{
						$whereraw .= ' And internal_date = "'.$currentdate.'"';
					}
				}
				if(!empty($request->session()->get('interview_jobopning_filter_inner_list')) && $request->session()->get('interview_jobopning_filter_inner_list') != 'All')
				{
					$jobopning = $request->session()->get('interview_jobopning_filter_inner_list');
					 $selectedFilter['job'] = $jobopning;
					 if($whereraw == '')
					{
						$whereraw = 'job_opening = "'.$jobopning.'"';
					}
					else
					{
						
						$whereraw .= ' And job_opening = "'.$jobopning.'"';
					}
				}
				if(!empty($request->session()->get('interview_recruiter_filter_inner_list')) && $request->session()->get('interview_recruiter_filter_inner_list') != 'All')
				{
					$recruiter = $request->session()->get('interview_recruiter_filter_inner_list');
					 $selectedFilter['recruiter'] = $recruiter;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter = "'.$recruiter.'"';
					}
					else
					{
						$whereraw .= ' And recruiter = "'.$recruiter.'"';
					}
				}
				
				
				//echo $whereraw;//exit;
				$nameArray = array();
				if($whereraw == '')
				{
				$name = InterviewProcess::where('department',43)->get();
				}
				else
				{
					
					$name = InterviewProcess::whereRaw($whereraw)->where('department',43)->get();
					
				}
				//echo $whereraw;exit;
				foreach($name as $_name)
				{
					//echo $_f->first_name;exit;
					$nameArray[$_name->name] = $_name->name;
				}
				
				//print_r();exit;
				$mobileArray = array();
				if($whereraw == '')
				{
				$mobile = InterviewProcess::where('department',43)->get();
				}
				else
				{
					
					$mobile = InterviewProcess::whereRaw($whereraw)->where('department',43)->get();
					
				}
				
				foreach($mobile as $_mobile)
				{
					//echo $_lname->last_name;exit;
					$mobileArray[$_mobile->mobile] = $_mobile->mobile;
				}
				
				$CurrentInterviewArray = array();
				
				if($whereraw == '')
				{
				$interview1 = InterviewProcess::where('department',43)->get();
				$CurrentInterview =array();
				
				foreach($interview1 as $_data){
				$CurrentInterview[]=$_data->id;
				}
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',43)->get();
					$CurrentInterview =array();
					foreach($interview1 as $_data){
					$CurrentInterview[]=$_data->id;
				}
				}
				$finaldata=array();
				foreach($CurrentInterview as $val){
				$data=InterviewDetailsProcess::where("interview_id",$val)->orderBy("id","DESC")->first();
				$finaldata[$val]=$data->interview_type;
				//InterviewDetailsProcess::where('interview_id',$id)->orderBy("id","DESC")->first();
				}
				$finaldata=array_unique($finaldata);
				foreach($finaldata as $key=>$_interview1)
				{
					//echo $_lname->last_name;exit;
					$CurrentInterviewArray[$_interview1] = $_interview1;
				}
					
				$CurrentStatusArray = array();
				if($whereraw == '')
				{
				$interview1 = InterviewProcess::where('department',43)->get();
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',43)->get();
					
				}
				
				
				foreach($interview1 as $_interviewstatus)
				{
					//echo $_lname->last_name;exit;
					$CurrentStatusArray[$_interviewstatus->current_status] = $_interviewstatus->current_status;
				}
				
				$InterviewDateArray = array();
				if($whereraw == '')
				{
					
				$internaldate = InterviewProcess::where('department',43)->get();
				}
				else
				{
					
					$internaldate = InterviewProcess::whereRaw($whereraw)->where('department',43)->get();
					
				}
				//print_r($date);exit;
				
				foreach($internaldate as $_date)
				{
					
					//echo $_lname->last_name;exit;
					$InterviewDateArray[$_date->internal_date] = $_date->internal_date;
				}
				$SerialNumberArray = array();
				if($whereraw == '')
				{
					
				$serl = InterviewProcess::where('department',43)->get();
				}
				else
				{
					
					$serl = InterviewProcess::whereRaw($whereraw)->where('department',43)->get();
					
				}
				//print_r($date);exit;
				
				foreach($serl as $_serl)
				{
					
					//echo $_lname->last_name;exit;
					$SerialNumberArray[$_serl->serial_number] = $_serl->serial_number;
				}
				
				$JobOpningArray = array();
				if($whereraw == '')
				{
					
				$job = InterviewProcess::where('department',43)->get();
				$jobdata=array();
				foreach($job as $jobval){
				if($jobval->job_opening !=''){	
				$jobdata[]=$jobval->job_opening;
				}
				}
				}
				else
				{
					$job = InterviewProcess::whereRaw($whereraw)->where('department',43)->get();
					$jobdata=array();
					foreach($job as $jobval){
					if($jobval->job_opening !=''){	
					$jobdata[]=$jobval->job_opening;
					}
					}
				}
				$finaldata=array_unique($jobdata);
				
				foreach($finaldata as $_job)
				{
					
					$data = JobOpening::where('id',$_job)->first();
					$JobOpningArray[$data->id] = $data->name;
				}
				
				$recruiterArray = array();
				if($whereraw == '')
				{
					
				$recruiter = InterviewProcess::where('department',43)->get();
				$recruiterdata=array();
				foreach($recruiter as $recruiterval){
				if($recruiterval->recruiter !=''){	
				$recruiterdata[]=$recruiterval->recruiter;
				}
				}
				}
				else
				{
					$recruiter = InterviewProcess::whereRaw($whereraw)->where('department',43)->get();
					$recruiterdata=array();
					foreach($recruiter as $recruiterval){
					if($recruiterval->recruiter !=''){	
					$recruiterdata[]=$recruiterval->recruiter;
					}
					}
				}
				$finaldatar=array_unique($recruiterdata);
				
				foreach($finaldatar as $_recruiter)
				{
					
					$datar = RecruiterDetails::where('id',$_recruiter)->first();
					$recruiterArray[$datar->id] = $datar->name;
				}
				
				if($whereraw != '')
				{
					$InterviewList = InterviewProcess::whereRaw($whereraw)->where('department',43)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountaafaq = InterviewProcess::whereRaw($whereraw)->where('department',43)->get()->count();	
				}
				else{
					$InterviewList = InterviewProcess::where('department',43)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountaafaq = InterviewProcess::where('department',43)->get()->count();	
				}					
				
				
				$InterviewList->setPath(config('app.url/interviewListaafaq'));
				
				
				
				
			
			return view("InterviewProcess/InterviewListingaafaq",compact('recruiterArray','JobOpningArray','SerialNumberArray','InterviewDateArray','CurrentStatusArray','CurrentInterviewArray','InterviewList','paginationValue','reportsCountaafaq','selectedFilter','mobileArray','nameArray'));
		}
		public function searchbyInterview(Request $request)
		{
			
			$name = $request->interviewname;
			$mobile = $request->interviewmobile;
			$recruiter = $request->recruiter;
			$job_opening = $request->job_opening;
			$status = $request->status;
			$interviewstage = $request->interviewstage;
			
			$request->session()->put('interview_name_filter_inner_list',$name);
			$request->session()->put('interview_mobile_filter_inner_list',$mobile);
			$request->session()->put('interview_jobopning_filter_inner_list',$job_opening);
			$request->session()->put('interview_currentinterview_filter_inner_list',$interviewstage);
			
			$request->session()->put('interview_recruiter_filter_inner_list',$recruiter);
			$request->session()->put('interview_currentstatus_filter_inner_list',$status);
			
			
		}
		public function interviewFilterreset(Request $request)
		{
			
			$request->session()->put('interview_name_filter_inner_list','');
			$request->session()->put('interview_mobile_filter_inner_list','');
			$request->session()->put('interview_jobopning_filter_inner_list','');
			$request->session()->put('interview_currentstatus_filter_inner_list','');
			$request->session()->put('interview_recruiter_filter_inner_list','');
			$request->session()->put('interview_currentinterview_filter_inner_list','');
		}
		public function updatebgverificationpost(Request $rq)
	{
		$keys = array_keys($_FILES);
					
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			$LastInsertId=date("Y-m-d_h-i-s");
			foreach($keys as $key)
			{
				
				if(!empty($rq->file($key)))
				{
				$filenameWithExt = $rq->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$rq->file($key)->getClientOriginalExtension();
				$vKey = $key;
				$newFileName = $key.'-interview-'.$LastInsertId.'.'.$fileExtension;
				if(file_exists(public_path('interviewcv/'.$newFileName))){

					  unlink(public_path('interviewcv/'.$newFileName));

					}
				/*
				*Updating File Name
				*/
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $rq->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$rq->file($key)->move(public_path('interviewcv/'), $newFileName);
				$fileIndex++;
				}
				else
				{
					
					$vKey = $keys[$fileIndex];
					$filesAttributeInfo[$vKey] = '';
					$listOfAttribute[] = $vKey;
					$fileIndex++;
					
				}
			}
		if(!empty($newFileName)){
			$img=$newFileName;
			}
			else{
			$img=$rq->input('bg_uploadfile');
			}
			
		$obj = InterviewProcess::find($rq->input('id'));
		
		$obj->bg_description = $rq->input('bg_description');
		$obj->bg_upload = $img;
		$obj->bgverification_status = $rq->input('bgverification_status');
		$obj->save();
		
		$rq->session()->flash('message','Update Saved Successfully.');
		$response['code'] = '200';
		  $response['message'] = "Update Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
		
	}
		 
	
}
