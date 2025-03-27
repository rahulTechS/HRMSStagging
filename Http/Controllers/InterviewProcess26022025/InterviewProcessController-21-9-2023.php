<?php

namespace App\Http\Controllers\InterviewProcess;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\InterviewProcess\InterviewProcessFailed;
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
use App\Models\Entry\Employee;
use App\Models\Logs\InterviewProcessLog;
use App\Models\InterviewProcess\DesignationParmission;

use File;
class InterviewProcessController extends Controller
{
    public function InterviewProcess()
	{
		//echo "hello";exit;
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$jobOpning=JobOpening::where("status",1)->get();
		$EmpName = InterviewProcess::groupBy('name')->selectRaw('count(*) as name, name')->get();
		//print_r($EmpName);
		return view("InterviewProcess/InterviewList",compact('EmpName','jobOpning','jobRecruiterDetails'));
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
		$formdata=$rq->input();
		$keys = array_keys($_FILES);
		$finaljsondata = json_encode(array('PostData' =>$formdata,'File'=>$_FILES), JSON_PRETTY_PRINT);
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
			$designation=$jobOpning->designation;
				}
				else{
				$departmentname='';
				$designation='';
				}
			//$departmentname=$jobOpning->department;
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
			$obj->createdBy = $rq->session()->get('EmployeeId');
			if($rq->input('status')==1){
				$obj->status = $rq->input('status');
			}
			$obj->serial_number = $srlnum;
			if($obj->save())
			{
			
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
			$interview->createdBy = $rq->session()->get('EmployeeId');
			$interview->location=$rq->input('location');
			$interview->recruiter=$rq->input('recruiter');
			$interview->designation=$designation;
			$interview->department=$departmentname;
			if($interview->save()){
				$logObj = new InterviewProcessLog();
				$logObj->process_id =$LastId;
				$logObj->created_by=$rq->session()->get('EmployeeId');
				$logObj->title ="Interview1";
				$logObj->response =$finaljsondata;
				$logObj->save();
				
				
			}
			$response['code'] = '200';
			$response['id']=$LastId;
			$response['message'] = "Interview Process Save  Successfully.";		
			}
	else
	{
		$response['code'] = '300';
				
				$response['message'] = "Interview Process Issue to Save.";		
	}	
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
				 
				 $empId  = $request->session()->get('EmployeeId');
				 $empmode = Employee::where("id",$empId)->first();
				 if($empmode != '')
				 {
					 $empGroupId = $empmode->group_id;
					 if($empGroupId == 22)
					 {
						 $whereraw = 'createdBy = '.$empId; 
					$request->session()->put('interview_recruiter_filter_inner_list','');
					 }
				 }
				
				 
				 
				 if($empId == 104)
				 {
					$selectedFilter['recruiter']= 9; 
					$request->session()->put('interview_recruiter_filter_inner_list',9);
				 }
				 else if($empId == 103)
				 {
					 $selectedFilter['recruiter']= 11;
					 $request->session()->put('interview_recruiter_filter_inner_list',11);
				 }
				/*  else if($empId == 102)
				 {
					 $selectedFilter['recruiter']= 12;
					 $request->session()->put('interview_recruiter_filter_inner_list',12);
				 } */
				 else if($empId == 101)
				 {
					 $selectedFilter['recruiter']= 8;
					 $request->session()->put('interview_recruiter_filter_inner_list',8);
				 }
				 else if($empId == 100)
				 {
					 $selectedFilter['recruiter']= 7;
					 $request->session()->put('interview_recruiter_filter_inner_list',7);
				 }
				 else if($empId == 99)
				 {
					 $selectedFilter['recruiter']= 10;
					 $request->session()->put('interview_recruiter_filter_inner_list',10);
				 }
				 
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
					$cnameArray = explode(",",$name);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						//$whereraw = 'name like "%'.$name.'%"';
						$whereraw = 'name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And name IN('.$finalcname.')';
					}
				}
				//echo $whereraw;
				if(!empty($request->session()->get('datefrom_filter_inner_list')) && $request->session()->get('datefrom_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at< "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at< "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_filter_inner_list')) && $request->session()->get('dateto_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at> "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at> "'.$dateto.' 00:00:00"';
					}
				}
				//echo $whereraw;//exit;
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
					$currentinterviewarray = $request->session()->get('interview_currentinterview_filter_inner_list');
					if($currentinterviewarray!=''){
						$currentinterview=explode(',',$currentinterviewarray);
					}
					else{
						$currentinterview='';
					}
					
					 $selectedFilter['CurrentInterview'] = $currentinterview;
					 //print_r($currentinterview);
					 if($whereraw == '')
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
						if($interviewdetails != '')
						{
						$whereraw = 'id IN('.$interviewdetails.')';
						}
						
					}
					else
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
					   if($interviewdetails != '')
						{
						$whereraw .= ' And id IN('.$interviewdetails.')';
						}
					}
				}
				if(!empty($request->session()->get('interview_currentstatus_filter_inner_list')) && $request->session()->get('interview_currentstatus_filter_inner_list') != 'All')
				{
					$currentstatus = $request->session()->get('interview_currentstatus_filter_inner_list');
					 $selectedFilter['CurrentStatus'] = $currentstatus;
					 if($whereraw == '')
					{
						$whereraw = 'current_status IN('.$currentstatus.')';
					}
					else
					{
						$whereraw .= ' And current_status IN('.$currentstatus.')';
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
						$whereraw = 'job_opening IN('.$jobopning.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$jobopning.')';
					}
				}
				if(!empty($request->session()->get('interview_recruiter_filter_inner_list')) && $request->session()->get('interview_recruiter_filter_inner_list') != 'All')
				{
					$recruiter = $request->session()->get('interview_recruiter_filter_inner_list');
					 $selectedFilter['recruiter'] = $recruiter;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN('.$recruiter.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$recruiter.')';
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
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
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
				if($data->status==1){$st="Reject";} else if($data->status==3){$st="In-Progress";} else{$st="Accept";}
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
			if($jobOpning!=''){
			$departmentname=$jobOpning->department;
			}else{
				$departmentname='';
			}
			$processdata=InterviewProcess::where("id",$rq->input('id'))->first();
			$finaljsondata = json_encode(array('PostData' =>$processdata), JSON_PRETTY_PRINT);
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
		$obj->createdBy = $rq->session()->get('EmployeeId');
		if($obj->save()){
			$logObj = new InterviewProcessLog();
			$logObj->process_id =$rq->input('id');
			$logObj->created_by=$rq->session()->get('EmployeeId');
			$logObj->title ="Updated candidate Details";
			$logObj->response =$finaljsondata;
			$logObj->save();
		}
		
		$rq->session()->flash('message','Update Saved Successfully.');
		$response['code'] = '200';
		  $response['message'] = "Update Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
		
	}
	public function updateInterviewProcessPost(Request $rq)
	{
		$processdata=InterviewProcess::where("id",$rq->input('id'))->first();
		$jobid=$processdata->job_opening;
		$recruiter=$processdata->recruiter;
		$location=$processdata->location;
		$department=$processdata->department;		
		$jobOpning=JobOpening::where("id",$jobid)->first();
		if($jobOpning!=''){
		$designation=$jobOpning->designation;
			}
			else{
			$designation='';
			}
		$interviewdetailsdata=InterviewDetailsProcess::where("interview_id",$rq->input('id'))->where("interview_type",'Interview1')->first();
		$finaljsondata = json_encode(array('PostData' =>$processdata,"InterviewDetails"=>$interviewdetailsdata), JSON_PRETTY_PRINT);
		if($rq->input('status')==1){
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 1;
		if($obj->save()){
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
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$location;
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$department;
			//$detailsObj->save();
		}
		else{
			$detailsObj = new InterviewDetailsProcess();
			$detailsObj->interview_id = $rq->input('id');
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
			$detailsObj->interview_type = 'Interview1';
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$location;
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$department;
			
		}
		if($detailsObj->save()){
			$logObj = new InterviewProcessLog();
			$logObj->process_id =$rq->input('id');
			$logObj->created_by=$rq->session()->get('EmployeeId');
			$logObj->title ="Interview1";
			$logObj->response =$finaljsondata;
			$logObj->save();
		}
		}
		}
		if($rq->input('status')==2){
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 1;
		if($obj->save()){
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
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$location;
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$department;
			//$detailsObj->save();
		}
		else{
			$detailsObj = new InterviewDetailsProcess();
			$detailsObj->interview_id = $rq->input('id');
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
			$detailsObj->interview_type = 'Interview1';
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$location;
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$department;
			
		}
		if($detailsObj->save()){
			$logObj = new InterviewProcessLog();
			$logObj->process_id =$rq->input('id');
			$logObj->created_by=$rq->session()->get('EmployeeId');
			$logObj->title ="Interview1";
			$logObj->response =$finaljsondata;
			$logObj->save();
			}
		}
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
		$processdata=InterviewProcess::where("id",$rq->input('id'))->first();
		$jobid=$processdata->job_opening;
		$recruiter=$processdata->recruiter;
		$location=$processdata->location;
		$department=$processdata->department;		
		$jobOpning=JobOpening::where("id",$jobid)->first();
		if($jobOpning!=''){
		$designation=$jobOpning->designation;
			}
			else{
			$designation='';
			}
		$interviewdetailsdata=InterviewDetailsProcess::where("interview_id",$rq->input('id'))->where("interview_type",'Interview2')->first();
		$finaljsondata = json_encode(array('PostData' =>$processdata,"InterviewDetails"=>$interviewdetailsdata), JSON_PRETTY_PRINT);
		
		if($rq->input('status')==1){
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 1;
		if($obj->save()){
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
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$location;
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$department;
			
		}
		else{
			$detailsObj = new InterviewDetailsProcess();
			$detailsObj->interview_id = $rq->input('id');
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
			$detailsObj->interview_type = 'Interview2';
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$location;
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$department;
			//$detailsObj->save();
		}
		if($detailsObj->save())
		{	
			$logObj = new InterviewProcessLog();
			$logObj->process_id =$rq->input('id');
			$logObj->created_by=$rq->session()->get('EmployeeId');
			$logObj->title ="Interview2";
			$logObj->response =$finaljsondata;
			$logObj->save();
		}
		}
		}
		if($rq->input('status')==2){
		$obj = InterviewProcess::find($rq->input('id'));
		//$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 1;
		if($obj->save()){
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
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$location;
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$department;
			
			//$detailsObj->save();
		}
		else{
			$detailsObj = new InterviewDetailsProcess();
			$detailsObj->interview_id = $rq->input('id');
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
			$detailsObj->interview_type = 'Interview2';
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$location;
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$department;
			//$detailsObj->save();
		}
		if($detailsObj->save())
		{	
			$logObj = new InterviewProcessLog();
			$logObj->process_id =$rq->input('id');
			$logObj->created_by=$rq->session()->get('EmployeeId');
			$logObj->title ="Interview2";
			$logObj->response =$finaljsondata;
			$logObj->save();
		}

		}
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
		$processdata=InterviewProcess::where("id",$rq->input('id'))->first();
		$jobid=$processdata->job_opening;
		$recruiter=$processdata->recruiter;
		$location=$processdata->location;
		$department=$processdata->department;		
		$jobOpning=JobOpening::where("id",$jobid)->first();
		if($jobOpning!=''){
		$designation=$jobOpning->designation;
			}
			else{
			$designation='';
			}
		$interviewdetailsdata=InterviewDetailsProcess::where("interview_id",$rq->input('id'))->where("interview_type",'Interview3')->first();
		$finaljsondata = json_encode(array('PostData' =>$processdata,"InterviewDetails"=>$interviewdetailsdata), JSON_PRETTY_PRINT);
		
		if($rq->input('status')==1){
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 1;
		if($obj->save()){
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
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$location;
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$department;
			//$detailsObj->save();
		}
		else{
			$detailsObj = new InterviewDetailsProcess();
			$detailsObj->interview_id = $rq->input('id');
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
			$detailsObj->interview_type = 'Interview3';
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$location;
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$department;
			//$detailsObj->save();
		}
		if($detailsObj->save())
		{	
			$logObj = new InterviewProcessLog();
			$logObj->process_id =$rq->input('id');
			$logObj->created_by=$rq->session()->get('EmployeeId');
			$logObj->title ="Interview3";
			$logObj->response =$finaljsondata;
			$logObj->save();
		}
		}
		}
		if($rq->input('status')==2){
		$obj = InterviewProcess::find($rq->input('id'));
		//$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 1;
		if($obj->save()){
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
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$location;
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$department;
			//$detailsObj->save();
		}
		else{
			$detailsObj = new InterviewDetailsProcess();
			$detailsObj->interview_id = $rq->input('id');
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
			$detailsObj->interview_type = 'Interview3';
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$location;
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$department;
			//$interview->save();
		}
		if($detailsObj->save())
		{	
			$logObj = new InterviewProcessLog();
			$logObj->process_id =$rq->input('id');
			$logObj->created_by=$rq->session()->get('EmployeeId');
			$logObj->title ="Interview3";
			$logObj->response =$finaljsondata;
			$logObj->save();
		}
		}
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
		$processdata=InterviewProcess::where("id",$rq->input('id'))->first();
		$recruiter=$processdata->recruiter;
		$interviewdetailsdata=InterviewDetailsProcess::where("interview_id",$rq->input('id'))->where("interview_type",'final discussion')->first();
		$finaljsondata = json_encode(array('PostData' =>$processdata,"InterviewDetails"=>$interviewdetailsdata), JSON_PRETTY_PRINT);
		
		if($rq->input('status')==1 || $rq->input('status')==3){
			$jobOpning=JobOpening::where("id",$rq->input('job_opening'))->first();
			if($jobOpning!=''){
			$departmentname=$jobOpning->department;
			$designation=$jobOpning->designation;
			}
			else{
			$departmentname='';
			$designation='';
			}
			
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->job_opening = $rq->input('job_opening');
		$obj->department = $departmentname;
		$obj->location = $rq->input('location');
		$obj->designation = $rq->input('designation');
		$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 'final discussion';
			if($obj->save())
			{
				/* save Details data*/
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
			if($rq->input('interviewer_status')==2){
				$detailsObj->interviewer_name = $rq->input('interviewer_name_all');
				$detailsObj->interviewer_status = $rq->input('interviewer_status');
			}
			else{
				$detailsObj->interviewer_name = $rq->input('interviewer_name');
				$detailsObj->interviewer_status = $rq->input('interviewer_status');
			}
			
			
			$detailsObj->interviewer_tl = $rq->input('interviewer_tl');
			$detailsObj->rating = $rq->input('rating');
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$rq->input('location');
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$departmentname;
			//$detailsObj->save();
		}
		else{
			$detailsObj = new InterviewDetailsProcess();
			$detailsObj->interview_id = $rq->input('id');
			$detailsObj->experience = $rq->input('experience');
			$detailsObj->job_knowledge = $rq->input('job_knowledge');
			$detailsObj->Communication_skills = $rq->input('Communication_skills');
			$detailsObj->team_work_abilities = $rq->input('team_work_abilities');
			$detailsObj->presentation = $rq->input('presentation');
			$detailsObj->job_stability = $rq->input('job_stability');
			$detailsObj->other_notes = $rq->input('other_notes');
			$detailsObj->salary = $rq->input('salary');
			$detailsObj->status = $rq->input('status');
			if($rq->input('interviewer_status')==2){
				$detailsObj->interviewer_name = $rq->input('interviewer_name_all');
				$detailsObj->interviewer_status = $rq->input('interviewer_status');
			}
			else{
				$detailsObj->interviewer_name = $rq->input('interviewer_name');
				$detailsObj->interviewer_status = $rq->input('interviewer_status');
			}
			$detailsObj->interviewer_tl = $rq->input('interviewer_tl');
			$detailsObj->rating = $rq->input('rating');
			$detailsObj->interview_type = 'final discussion';
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$rq->input('location');
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$departmentname;
			
		}
		if($detailsObj->save())
		{	
			$logObj = new InterviewProcessLog();
			$logObj->process_id =$rq->input('id');
			$logObj->created_by=$rq->session()->get('EmployeeId');
			$logObj->title ="final discussion";
			$logObj->response =$finaljsondata;
			$logObj->save();
		}
		else{
			$finaljsonpost = json_encode(array('PostData' =>$rq->input()), JSON_PRETTY_PRINT);
			$FailedObj = new InterviewProcessFailed();
			$logObj->interview_id =$rq->input('id');
			$logObj->created_by=$rq->session()->get('EmployeeId');
			$logObj->response =$finaljsonpost;
			$logObj->save();
		}
				/* save Details data*/
			}
		}
		if($rq->input('status')==2){
		$jobOpning=JobOpening::where("id",$rq->input('job_opening'))->first();
		if($jobOpning!=''){
		$departmentname=$jobOpning->department;
		$designation=$jobOpning->designation;
			}
			else{
			$departmentname='';
			$designation='';
			}
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->job_opening = $rq->input('job_opening');
		$obj->department = $departmentname;
		$obj->location = $rq->input('location');
		$obj->designation = $rq->input('designation');
		//$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 2;
		if($obj->save())
			{
				/* save Details data*/
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
			if($rq->input('interviewer_status')==2){
				$detailsObj->interviewer_name = $rq->input('interviewer_name_all');
				$detailsObj->interviewer_status = $rq->input('interviewer_status');
			}
			else{
				$detailsObj->interviewer_name = $rq->input('interviewer_name');
				$detailsObj->interviewer_status = $rq->input('interviewer_status');
			}
			$detailsObj->interviewer_tl = $rq->input('interviewer_tl');
			$detailsObj->rating = $rq->input('rating');
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$rq->input('location');
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$departmentname;
			//$detailsObj->save();
		}
		else{
			$detailsObj = new InterviewDetailsProcess();
			$detailsObj->interview_id = $rq->input('id');
			$detailsObj->experience = $rq->input('experience');
			$detailsObj->job_knowledge = $rq->input('job_knowledge');
			$detailsObj->Communication_skills = $rq->input('Communication_skills');
			$detailsObj->team_work_abilities = $rq->input('team_work_abilities');
			$detailsObj->presentation = $rq->input('presentation');
			$detailsObj->job_stability = $rq->input('job_stability');
			$detailsObj->other_notes = $rq->input('other_notes');
			$detailsObj->salary = $rq->input('salary');
			$detailsObj->status = $rq->input('status');
			if($rq->input('interviewer_status')==2){
				$detailsObj->interviewer_name = $rq->input('interviewer_name_all');
				$detailsObj->interviewer_status = $rq->input('interviewer_status');
			}
			else{
				$detailsObj->interviewer_name = $rq->input('interviewer_name');
				$detailsObj->interviewer_status = $rq->input('interviewer_status');
			}
			$detailsObj->interviewer_tl = $rq->input('interviewer_tl');
			$detailsObj->rating = $rq->input('rating');
			$detailsObj->interview_type = 'final discussion';
			$detailsObj->createdBy = $rq->session()->get('EmployeeId');
			$detailsObj->location=$rq->input('location');
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$departmentname;
			
			//$interview->save();
		}
		
		if($detailsObj->save())
		{
		$data = InterviewProcess::where("id",$rq->input('id'))->first();
		$name=$data->name;
		$mobile=$data->mobile;
		$job=$data->job_opening;
		$recuter=RecruiterDetails::where("id",$data->recruiter)->first();	
		if($recuter!=''){
		$recruiter_cat=$recuter->recruit_cat;	
		}
		else{
			$recruiter_cat='';
		}
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
		$DocumentCollectionDetails->recruiter_cat=$recruiter_cat;
		$DocumentCollectionDetails->recruiter_name=$data->recruiter;
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
		$DocumentCollectionDetails->onboarding_status_final=1;
		$DocumentCollectionDetails->offer_letter_onboarding_status=1;
		$DocumentCollectionDetails->visa_process_status=0;
		$DocumentCollectionDetails->training_process_status=0;
		$DocumentCollectionDetails->offer_letter_relased_status=1;
		$DocumentCollectionDetails->offer_letter_document_status=1;
		$DocumentCollectionDetails->visa_documents_status=1;
		$DocumentCollectionDetails->bgverification_status=5;
		$DocumentCollectionDetails->onboard_question_kyc_status=1;
		$DocumentCollectionDetails->interview_approved_by=$rq->input('interviewer_name');
		$DocumentCollectionDetails->interview_id=$data->id;
		$DocumentCollectionDetails->save();
		$LastInsertId = $DocumentCollectionDetails->id;
		$objDocument = new DocumentCollectionDetailsValues();
		$objDocument->document_collection_id = $LastInsertId;
		$objDocument->attribute_code = 63;
		$objDocument->attribute_value = $data->bg_description;
		if($objDocument->save()){
			
			$logObj = new InterviewProcessLog();
			$logObj->process_id =$rq->input('id');
			$logObj->created_by=$rq->session()->get('EmployeeId');
			$logObj->title ="final discussion";
			$logObj->response =$finaljsondata;
			$logObj->save();
		
		
		if($data->bg_upload!=''){
		$objDocument1 = new DocumentCollectionDetailsValues();
		$objDocument1->document_collection_id = $LastInsertId;
		$objDocument1->attribute_code = 64;
		$objDocument1->attribute_value = $data->bg_upload;
		
		$imagePath = public_path('interviewcv/'.$data->bg_upload);
		$newPath = public_path('documentCollectionFiles/'.$data->bg_upload);
		File::copy($imagePath, $newPath);
		$objDocument1->save();	
		}
		}
						
				/* save Details data*/
			
			}
			else{
				$finaljsonpost = json_encode(array('PostData' =>$rq->input()), JSON_PRETTY_PRINT);
				$FailedObj = new InterviewProcessFailed();
				$logObj->interview_id =$rq->input('id');
				$logObj->created_by=$rq->session()->get('EmployeeId');
				$logObj->response =$finaljsonpost;
				if($logObj->save()){
					$obj = InterviewProcess::find($rq->input('id'));
					$obj->current_status = 1;
					$obj->interview_type = 1;
					$obj->save();
				}
			}

		
		}

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
		$interview6 = InterviewDetailsProcess::where("interview_id",$rowId)->orderBy("id","DESC")->get();
		if($interview6!=''){
			$interviewdetail6=$interview6;
		}
		else{
			$interviewdetail6='';
		}
		//print_r($interviewdetail1);exit;
		return view("InterviewProcess/viewInterview",compact('interviewdetail6','interviewdetail1','interviewdetail2','interviewdetail3','interviewdetail4','interviewList'));
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
	
	public function getDepartmentid($dept){
		$jobOpning=JobOpening::where('id',$dept)->where("status",1)->first();
		$design=$jobOpning->designation;
		$designation= Designation::where("id",$design)->first();
		$response['code'] = '200';
		  $response['dept'] =$jobOpning->department;
		  $response['design'] =$designation->name;
		  $response['designId'] =$design;
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
	public static function getRecruiterName($uid)
	{
		return "";
				$admin =Employee::where("id",$uid)->first();
				if($admin != '')
				{
				return " - ".$admin->fullname;
				}
				else
				{
					return "";
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
				 
				  $empId  = $request->session()->get('EmployeeId');
				 $empmode = Employee::where("id",$empId)->first();
				 if($empmode != '')
				 {
					 $empGroupId = $empmode->group_id;
					 if($empGroupId == 22)
					 {
						 $whereraw = 'createdBy = '.$empId; 
					$request->session()->put('interview_recruiter_filter_inner_list','');
					 }
				 }
				 if($empId == 104)
				 {
					$selectedFilter['recruiter']= 9; 
					$request->session()->put('interview_recruiter_filter_inner_list',9);
				 }
				 else if($empId == 103)
				 {
					 $selectedFilter['recruiter']= 11;
					 $request->session()->put('interview_recruiter_filter_inner_list',11);
				 }
				/*  else if($empId == 102)
				 {
					 $selectedFilter['recruiter']= 12;
					 $request->session()->put('interview_recruiter_filter_inner_list',12);
				 } */
				 else if($empId == 101)
				 {
					 $selectedFilter['recruiter']= 8;
					 $request->session()->put('interview_recruiter_filter_inner_list',8);
				 }
				 else if($empId == 100)
				 {
					 $selectedFilter['recruiter']= 7;
					 $request->session()->put('interview_recruiter_filter_inner_list',7);
				 }
				 else if($empId == 99)
				 {
					 $selectedFilter['recruiter']= 10;
					 $request->session()->put('interview_recruiter_filter_inner_list',10);
				 }
				 
				 
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
					$cnameArray = explode(",",$name);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						//$whereraw = 'name like "%'.$name.'%"';
						$whereraw = 'name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And name IN('.$finalcname.')';
					}
				}
								if(!empty($request->session()->get('datefrom_filter_inner_list')) && $request->session()->get('datefrom_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at< "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at< "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_filter_inner_list')) && $request->session()->get('dateto_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at> "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at> "'.$dateto.' 00:00:00"';
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
					$currentinterviewarray = $request->session()->get('interview_currentinterview_filter_inner_list');
					if($currentinterviewarray!=''){
						$currentinterview=explode(',',$currentinterviewarray);
					}
					else{
						$currentinterview='';
					}
					
					 $selectedFilter['CurrentInterview'] = $currentinterview;
					 //print_r($currentinterview);
					 if($whereraw == '')
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
						if($interviewdetails != '')
						{
						$whereraw = 'id IN('.$interviewdetails.')';
						}
						
					}
					else
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
					   if($interviewdetails != '')
						{
						$whereraw .= ' And id IN('.$interviewdetails.')';
						}
					}
				}
				if(!empty($request->session()->get('interview_currentstatus_filter_inner_list')) && $request->session()->get('interview_currentstatus_filter_inner_list') != 'All')
				{
					$currentstatus = $request->session()->get('interview_currentstatus_filter_inner_list');
					 $selectedFilter['CurrentStatus'] = $currentstatus;
					 if($whereraw == '')
					{
						$whereraw = 'current_status IN('.$currentstatus.')';
					}
					else
					{
						$whereraw .= ' And current_status IN('.$currentstatus.')';
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
						$whereraw = 'job_opening IN('.$jobopning.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$jobopning.')';
					}
				}
				if(!empty($request->session()->get('interview_recruiter_filter_inner_list')) && $request->session()->get('interview_recruiter_filter_inner_list') != 'All')
				{
					$recruiter = $request->session()->get('interview_recruiter_filter_inner_list');
					 $selectedFilter['recruiter'] = $recruiter;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN('.$recruiter.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$recruiter.')';
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
				  $empId  = $request->session()->get('EmployeeId');
				 $empmode = Employee::where("id",$empId)->first();
				 if($empmode != '')
				 {
					 $empGroupId = $empmode->group_id;
					 if($empGroupId == 22)
					 {
						 $whereraw = 'createdBy = '.$empId; 
					$request->session()->put('interview_recruiter_filter_inner_list','');
					 }
				 }
				 if($empId == 104)
				 {
					$selectedFilter['recruiter']= 9; 
					$request->session()->put('interview_recruiter_filter_inner_list',9);
				 }
				 else if($empId == 103)
				 {
					 $selectedFilter['recruiter']= 11;
					 $request->session()->put('interview_recruiter_filter_inner_list',11);
				 }
				/*  else if($empId == 102)
				 {
					 $selectedFilter['recruiter']= 12;
					 $request->session()->put('interview_recruiter_filter_inner_list',12);
				 } */
				 else if($empId == 101)
				 {
					 $selectedFilter['recruiter']= 8;
					 $request->session()->put('interview_recruiter_filter_inner_list',8);
				 }
				 else if($empId == 100)
				 {
					 $selectedFilter['recruiter']= 7;
					 $request->session()->put('interview_recruiter_filter_inner_list',7);
				 }
				 else if($empId == 99)
				 {
					 $selectedFilter['recruiter']= 10;
					 $request->session()->put('interview_recruiter_filter_inner_list',10);
				 }
				 
				 
				 
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
					$cnameArray = explode(",",$name);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						//$whereraw = 'name like "%'.$name.'%"';
						$whereraw = 'name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And name IN('.$finalcname.')';
					}
				}
								if(!empty($request->session()->get('datefrom_filter_inner_list')) && $request->session()->get('datefrom_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at< "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at< "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_filter_inner_list')) && $request->session()->get('dateto_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at> "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at> "'.$dateto.' 00:00:00"';
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
					$currentinterviewarray = $request->session()->get('interview_currentinterview_filter_inner_list');
					if($currentinterviewarray!=''){
						$currentinterview=explode(',',$currentinterviewarray);
					}
					else{
						$currentinterview='';
					}
					
					 $selectedFilter['CurrentInterview'] = $currentinterview;
					 //print_r($currentinterview);
					 if($whereraw == '')
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
						if($interviewdetails != '')
						{
						$whereraw = 'id IN('.$interviewdetails.')';
						}
						
					}
					else
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
					   if($interviewdetails != '')
						{
						$whereraw .= ' And id IN('.$interviewdetails.')';
						}
					}
				}
				if(!empty($request->session()->get('interview_currentstatus_filter_inner_list')) && $request->session()->get('interview_currentstatus_filter_inner_list') != 'All')
				{
					$currentstatus = $request->session()->get('interview_currentstatus_filter_inner_list');
					 $selectedFilter['CurrentStatus'] = $currentstatus;
					 if($whereraw == '')
					{
						$whereraw = 'current_status IN('.$currentstatus.')';
					}
					else
					{
						$whereraw .= ' And current_status IN('.$currentstatus.')';
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
						$whereraw = 'job_opening IN('.$jobopning.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$jobopning.')';
					}
				}
				if(!empty($request->session()->get('interview_recruiter_filter_inner_list')) && $request->session()->get('interview_recruiter_filter_inner_list') != 'All')
				{
					$recruiter = $request->session()->get('interview_recruiter_filter_inner_list');
					 $selectedFilter['recruiter'] = $recruiter;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN('.$recruiter.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$recruiter.')';
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
				  $empId  = $request->session()->get('EmployeeId');
				 $empmode = Employee::where("id",$empId)->first();
				 if($empmode != '')
				 {
					 $empGroupId = $empmode->group_id;
					 if($empGroupId == 22)
					 {
						 $whereraw = 'createdBy = '.$empId; 
					$request->session()->put('interview_recruiter_filter_inner_list','');
					 }
				 }
				 if($empId == 104)
				 {
					$selectedFilter['recruiter']= 9; 
					$request->session()->put('interview_recruiter_filter_inner_list',9);
				 }
				 else if($empId == 103)
				 {
					 $selectedFilter['recruiter']= 11;
					 $request->session()->put('interview_recruiter_filter_inner_list',11);
				 }
				/*  else if($empId == 102)
				 {
					 $selectedFilter['recruiter']= 12;
					 $request->session()->put('interview_recruiter_filter_inner_list',12);
				 } */
				 else if($empId == 101)
				 {
					 $selectedFilter['recruiter']= 8;
					 $request->session()->put('interview_recruiter_filter_inner_list',8);
				 }
				 else if($empId == 100)
				 {
					 $selectedFilter['recruiter']= 7;
					 $request->session()->put('interview_recruiter_filter_inner_list',7);
				 }
				 else if($empId == 99)
				 {
					 $selectedFilter['recruiter']= 10;
					 $request->session()->put('interview_recruiter_filter_inner_list',10);
				 }
				 
				 
				 
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
					$cnameArray = explode(",",$name);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						//$whereraw = 'name like "%'.$name.'%"';
						$whereraw = 'name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And name IN('.$finalcname.')';
					}
				}
								if(!empty($request->session()->get('datefrom_filter_inner_list')) && $request->session()->get('datefrom_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at< "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at< "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_filter_inner_list')) && $request->session()->get('dateto_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at> "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at> "'.$dateto.' 00:00:00"';
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
					$currentinterviewarray = $request->session()->get('interview_currentinterview_filter_inner_list');
					if($currentinterviewarray!=''){
						$currentinterview=explode(',',$currentinterviewarray);
					}
					else{
						$currentinterview='';
					}
					
					 $selectedFilter['CurrentInterview'] = $currentinterview;
					 //print_r($currentinterview);
					 if($whereraw == '')
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
						if($interviewdetails != '')
						{
						$whereraw = 'id IN('.$interviewdetails.')';
						}
						
					}
					else
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
					   if($interviewdetails != '')
						{
						$whereraw .= ' And id IN('.$interviewdetails.')';
						}
					}
				}
				if(!empty($request->session()->get('interview_currentstatus_filter_inner_list')) && $request->session()->get('interview_currentstatus_filter_inner_list') != 'All')
				{
					$currentstatus = $request->session()->get('interview_currentstatus_filter_inner_list');
					 $selectedFilter['CurrentStatus'] = $currentstatus;
					 if($whereraw == '')
					{
						$whereraw = 'current_status IN('.$currentstatus.')';
					}
					else
					{
						$whereraw .= ' And current_status IN('.$currentstatus.')';
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
						$whereraw = 'job_opening IN('.$jobopning.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$jobopning.')';
					}
				}
				if(!empty($request->session()->get('interview_recruiter_filter_inner_list')) && $request->session()->get('interview_recruiter_filter_inner_list') != 'All')
				{
					$recruiter = $request->session()->get('interview_recruiter_filter_inner_list');
					 $selectedFilter['recruiter'] = $recruiter;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN('.$recruiter.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$recruiter.')';
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
				 $empId  = $request->session()->get('EmployeeId');
				 $empmode = Employee::where("id",$empId)->first();
				 if($empmode != '')
				 {
					 $empGroupId = $empmode->group_id;
					 if($empGroupId == 22)
					 {
						 $whereraw = 'createdBy = '.$empId; 
					$request->session()->put('interview_recruiter_filter_inner_list','');
					 }
				 }
				 if($empId == 104)
				 {
					$selectedFilter['recruiter']= 9; 
					$request->session()->put('interview_recruiter_filter_inner_list',9);
				 }
				 else if($empId == 103)
				 {
					 $selectedFilter['recruiter']= 11;
					 $request->session()->put('interview_recruiter_filter_inner_list',11);
				 }
				 /* else if($empId == 102)
				 {
					 $selectedFilter['recruiter']= 12;
					 $request->session()->put('interview_recruiter_filter_inner_list',12);
				 } */
				 else if($empId == 101)
				 {
					 $selectedFilter['recruiter']= 8;
					 $request->session()->put('interview_recruiter_filter_inner_list',8);
				 }
				 else if($empId == 100)
				 {
					 $selectedFilter['recruiter']= 7;
					 $request->session()->put('interview_recruiter_filter_inner_list',7);
				 }
				 else if($empId == 99)
				 {
					 $selectedFilter['recruiter']= 10;
					 $request->session()->put('interview_recruiter_filter_inner_list',10);
				 }
				 
				 
				 
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
					$cnameArray = explode(",",$name);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						//$whereraw = 'name like "%'.$name.'%"';
						$whereraw = 'name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And name IN('.$finalcname.')';
					}
				}
								if(!empty($request->session()->get('datefrom_filter_inner_list')) && $request->session()->get('datefrom_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at< "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at< "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_filter_inner_list')) && $request->session()->get('dateto_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at> "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at> "'.$dateto.' 00:00:00"';
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
					
					$currentinterviewarray = $request->session()->get('interview_currentinterview_filter_inner_list');
					if($currentinterviewarray!=''){
						$currentinterview=explode(',',$currentinterviewarray);
					}
					else{
						$currentinterview='';
					}
					
					 $selectedFilter['CurrentInterview'] = $currentinterview;
					 //print_r($currentinterview);
					 if($whereraw == '')
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
						if($interviewdetails != '')
						{
						$whereraw = 'id IN('.$interviewdetails.')';
						}
						
					}
					else
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
					   if($interviewdetails != '')
						{
						$whereraw .= ' And id IN('.$interviewdetails.')';
						}
					}
				}
				if(!empty($request->session()->get('interview_currentstatus_filter_inner_list')) && $request->session()->get('interview_currentstatus_filter_inner_list') != 'All')
				{
					$currentstatus = $request->session()->get('interview_currentstatus_filter_inner_list');
					 $selectedFilter['CurrentStatus'] = $currentstatus;
					 if($whereraw == '')
					{
						$whereraw = 'current_status IN('.$currentstatus.')';
					}
					else
					{
						$whereraw .= ' And current_status IN('.$currentstatus.')';
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
						$whereraw = 'job_opening IN('.$jobopning.')';
					}
					else
					{
						
						$whereraw .= ' And job_opening IN('.$jobopning.')';
					}
				}
				if(!empty($request->session()->get('interview_recruiter_filter_inner_list')) && $request->session()->get('interview_recruiter_filter_inner_list') != 'All')
				{
					$recruiter = $request->session()->get('interview_recruiter_filter_inner_list');
					 $selectedFilter['recruiter'] = $recruiter;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN('.$recruiter.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$recruiter.')';
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
			$name='';	
			if($request->interviewname!=''){
			$namearray = array_filter($request->interviewname);
			$name=implode(",", $namearray);
			}
			//$name = $request->interviewname;
			$mobile = $request->interviewmobile;
			
			$recruiter='';	
			if($request->recruiter!=''){
			$recruiteryarr = array_filter($request->recruiter);
			$recruiter=implode(",", $recruiteryarr);
			}
			
			$job_opening='';
			if($request->job_opening!=''){
			 $job_openingarray = array_filter($request->job_opening);
			 $job_opening=implode(",", $job_openingarray);
			}
		$status='';
			if($request->status!=''){
			$statusarray = array_filter($request->status);
			$status=implode(",", $statusarray);
			}
			
			$interviewstage='';
			if($request->interviewstage!=''){
			$interviewstagearray = array_filter($request->interviewstage);
			$interviewstage=implode(",", $interviewstagearray);
			}
			$dateto = $request->dateto;
			$datefrom = $request->datefrom;			
			
			$request->session()->put('interview_name_filter_inner_list',$name);
			$request->session()->put('interview_mobile_filter_inner_list',$mobile);
			$request->session()->put('interview_jobopning_filter_inner_list',$job_opening);
			$request->session()->put('interview_currentinterview_filter_inner_list',$interviewstage);
			
			$request->session()->put('interview_recruiter_filter_inner_list',$recruiter);
			$request->session()->put('interview_currentstatus_filter_inner_list',$status);
			$request->session()->put('datefrom_filter_inner_list',$datefrom);
			$request->session()->put('dateto_filter_inner_list',$dateto);
			
			
		}
		public function interviewFilterreset(Request $request)
		{
			
			$request->session()->put('interview_name_filter_inner_list','');
			$request->session()->put('interview_mobile_filter_inner_list','');
			$request->session()->put('interview_jobopning_filter_inner_list','');
			$request->session()->put('interview_currentstatus_filter_inner_list','');
			$request->session()->put('interview_recruiter_filter_inner_list','');
			$request->session()->put('interview_currentinterview_filter_inner_list','');
			$request->session()->put('datefrom_filter_inner_list','');
			$request->session()->put('dateto_filter_inner_list','');
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
			$processdata=InterviewProcess::where("id",$rq->input('id'))->first();
		$finaljsondata = json_encode(array('PostData' =>$processdata), JSON_PRETTY_PRINT);	
		$obj = InterviewProcess::find($rq->input('id'));
		
		$obj->bg_description = $rq->input('bg_description');
		$obj->bg_upload = $img;
		$obj->bgverification_status = $rq->input('bgverification_status');
		if($obj->save()){
			$logObj = new InterviewProcessLog();
			$logObj->process_id =$rq->input('id');
			$logObj->created_by=$rq->session()->get('EmployeeId');
			$logObj->title ="Updated BG verifivation Details";
			$logObj->response =$finaljsondata;
			$logObj->save();
		}
		
		$rq->session()->flash('message','Update Saved Successfully.');
		$response['code'] = '200';
		  $response['message'] = "Update Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
		
	}
	public function updatejobOpningData($location=NULL){
		$jobOpning=JobOpening::where('location',$location)->where("status",1)->get();
		return view("InterviewProcess/UpdateDropdownForm",compact('jobOpning'));
	}
	public static function getUserName($id)
	{	

	  $data = Employee::where('id',$id)->orderBy("id","DESC")->first();
	  //print_r($data);
	  if($data != '')
	  {
	  return $data->fullname;
	  }
	  else
	  {
	  return '';
	  }
	}
	public function ViewFinalLogs($rowId=NULL,$stage=NULL){
		$interviewdetail=InterviewProcessLog::where("process_id",$rowId)->where("title",$stage)->get();
		if($interviewdetail!=''){
			$interviewlogs=$interviewdetail;
		}
		else{
			$interviewlogs='';
		}
		return view("InterviewProcess/InterviewLogsDetails",compact('interviewlogs'));
	}
	public function getupdateInterviewData(){
	$data=InterviewProcess::get();
	if($data!=''){
		foreach($data as $_data){
			//print_r($_data);exit;
			$jobid=$_data->job_opening;
			$recruiter=$_data->recruiter;
			$location=$_data->location;
			$department=$_data->department;		
			$jobOpning=JobOpening::where("id",$jobid)->first();
			if($jobOpning!=''){
			$designation=$jobOpning->designation;
				}
				else{
				$designation='';
				}
			$detailsdata = InterviewDetailsProcess::where("interview_id",$_data->id)->get();
			if($detailsdata!=''){
			foreach($detailsdata as $_detailsdata){	
			$detailsObj = InterviewDetailsProcess::find($_detailsdata->id);
			$detailsObj->location=$location;
			$detailsObj->recruiter=$recruiter;
			$detailsObj->designation=$designation;
			$detailsObj->department=$department;
			if($detailsObj->save()){
				echo "Update data";
			}
			else{
				echo "not Update data";
			}
			}
			}
				
		}
		echo "Update data";
	}
}
public static function getinterView1Date($id)
			{	

		  $data = InterviewDetailsProcess::where('interview_id',$id)->where("interview_type",'Interview1')->first();
		  if($data != '')
		  {
		  return date("d M Y",strtotime($data->created_at));
		  }
		  else
		  {
		  return '';
		  }
		}	
	public static function getinterviewerName1($id)
			{
			 $interview1=InterviewDetailsProcess::where('interview_id',$id)->where("interview_type",'Interview1')->first();
			 if($interview1!=''){
				 $eecruiter =Employee::where('id',$interview1->createdBy)->orderBy("id","DESC")->first();
			//$eecruiter =RecruiterDetails::where("id",$interview1->recruiter)->first();
			  
			  if($eecruiter != '')
			  {
				
			  return $eecruiter->fullname;
			  }
			  else
			  {
			  return '';
			  }
			 }
			}
public static function getFinalDiscussionDate($id)
	{	

	  $datafinal = InterviewDetailsProcess::where('interview_id',$id)->where("interview_type",'final discussion')->first();
	  if($datafinal != '')
	  {
	  return date("d M Y",strtotime($datafinal->created_at));
	  }
	  else
	  {
	  return '';
	  }
	}
	
	public static function getinterviewerNameFD($id)
			{	
			$interview4=InterviewDetailsProcess::where('interview_id',$id)->where("interview_type",'final discussion')->first();
			 if($interview4!=''){
				  $eecruiter =Employee::where('id',$interview4->createdBy)->orderBy("id","DESC")->first();
			//RecruiterDetails::where("id",$interview4->createdBy)->first();
			  
			  if($eecruiter != '')
			  {
				
			  return $eecruiter->fullname;
			  }
			  else
			  {
			  return '';
			  }
			}
			}
//DIB

public function interviewListDIB(Request $request){
		
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
				 
				  $empId  = $request->session()->get('EmployeeId');
				 $empmode = Employee::where("id",$empId)->first();
				 if($empmode != '')
				 {
					 $empGroupId = $empmode->group_id;
					 if($empGroupId == 22)
					 {
						 $whereraw = 'createdBy = '.$empId; 
					$request->session()->put('interview_recruiter_filter_inner_list','');
					 }
				 }
				 if($empId == 104)
				 {
					$selectedFilter['recruiter']= 9; 
					$request->session()->put('interview_recruiter_filter_inner_list',9);
				 }
				 else if($empId == 103)
				 {
					 $selectedFilter['recruiter']= 11;
					 $request->session()->put('interview_recruiter_filter_inner_list',11);
				 }
				/*  else if($empId == 102)
				 {
					 $selectedFilter['recruiter']= 12;
					 $request->session()->put('interview_recruiter_filter_inner_list',12);
				 } */
				 else if($empId == 101)
				 {
					 $selectedFilter['recruiter']= 8;
					 $request->session()->put('interview_recruiter_filter_inner_list',8);
				 }
				 else if($empId == 100)
				 {
					 $selectedFilter['recruiter']= 7;
					 $request->session()->put('interview_recruiter_filter_inner_list',7);
				 }
				 else if($empId == 99)
				 {
					 $selectedFilter['recruiter']= 10;
					 $request->session()->put('interview_recruiter_filter_inner_list',10);
				 }
				 
				 
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
					$cnameArray = explode(",",$name);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						//$whereraw = 'name like "%'.$name.'%"';
						$whereraw = 'name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And name IN('.$finalcname.')';
					}
				}
								if(!empty($request->session()->get('datefrom_filter_inner_list')) && $request->session()->get('datefrom_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at< "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at< "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_filter_inner_list')) && $request->session()->get('dateto_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at> "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at> "'.$dateto.' 00:00:00"';
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
					$currentinterviewarray = $request->session()->get('interview_currentinterview_filter_inner_list');
					if($currentinterviewarray!=''){
						$currentinterview=explode(',',$currentinterviewarray);
					}
					else{
						$currentinterview='';
					}
					
					 $selectedFilter['CurrentInterview'] = $currentinterview;
					 //print_r($currentinterview);
					 if($whereraw == '')
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
						if($interviewdetails != '')
						{
						$whereraw = 'id IN('.$interviewdetails.')';
						}
						
					}
					else
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
					   if($interviewdetails != '')
						{
						$whereraw .= ' And id IN('.$interviewdetails.')';
						}
					}
				}
				if(!empty($request->session()->get('interview_currentstatus_filter_inner_list')) && $request->session()->get('interview_currentstatus_filter_inner_list') != 'All')
				{
					$currentstatus = $request->session()->get('interview_currentstatus_filter_inner_list');
					 $selectedFilter['CurrentStatus'] = $currentstatus;
					 if($whereraw == '')
					{
						$whereraw = 'current_status IN('.$currentstatus.')';
					}
					else
					{
						$whereraw .= ' And current_status IN('.$currentstatus.')';
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
						$whereraw = 'job_opening IN('.$jobopning.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$jobopning.')';
					}
				}
				if(!empty($request->session()->get('interview_recruiter_filter_inner_list')) && $request->session()->get('interview_recruiter_filter_inner_list') != 'All')
				{
					$recruiter = $request->session()->get('interview_recruiter_filter_inner_list');
					 $selectedFilter['recruiter'] = $recruiter;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN('.$recruiter.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$recruiter.')';
					}
				}
				
				
				//echo $whereraw;//exit;
				$nameArray = array();
				if($whereraw == '')
				{
				$name = InterviewProcess::where('department',46)->get();
				}
				else
				{
					
					$name = InterviewProcess::whereRaw($whereraw)->where('department',46)->get();
					
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
				$mobile = InterviewProcess::where('department',46)->get();
				}
				else
				{
					
					$mobile = InterviewProcess::whereRaw($whereraw)->where('department',46)->get();
					
				}
				
				foreach($mobile as $_mobile)
				{
					//echo $_lname->last_name;exit;
					$mobileArray[$_mobile->mobile] = $_mobile->mobile;
				}
				
				$CurrentInterviewArray = array();
				
				if($whereraw == '')
				{
				$interview1 = InterviewProcess::where('department',46)->get();
				$CurrentInterview =array();
				
				foreach($interview1 as $_data){
				$CurrentInterview[]=$_data->id;
				}
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',46)->get();
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
				$interview1 = InterviewProcess::where('department',46)->get();
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',46)->get();
					
				}
				
				
				foreach($interview1 as $_interviewstatus)
				{
					//echo $_lname->last_name;exit;
					$CurrentStatusArray[$_interviewstatus->current_status] = $_interviewstatus->current_status;
				}
				
				$InterviewDateArray = array();
				if($whereraw == '')
				{
					
				$internaldate = InterviewProcess::where('department',46)->get();
				}
				else
				{
					
					$internaldate = InterviewProcess::whereRaw($whereraw)->where('department',46)->get();
					
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
					
				$serl = InterviewProcess::where('department',46)->get();
				}
				else
				{
					
					$serl = InterviewProcess::whereRaw($whereraw)->where('department',46)->get();
					
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
					
				$job = InterviewProcess::where('department',46)->get();
				$jobdata=array();
				foreach($job as $jobval){
				if($jobval->job_opening !=''){	
				$jobdata[]=$jobval->job_opening;
				}
				}
				}
				else
				{
					$job = InterviewProcess::whereRaw($whereraw)->where('department',46)->get();
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
					
				$recruiter = InterviewProcess::where('department',46)->get();
				$recruiterdata=array();
				foreach($recruiter as $recruiterval){
				if($recruiterval->recruiter !=''){	
				$recruiterdata[]=$recruiterval->recruiter;
				}
				}
				}
				else
				{
					$recruiter = InterviewProcess::whereRaw($whereraw)->where('department',46)->get();
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
					$InterviewList = InterviewProcess::whereRaw($whereraw)->where('department',46)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountDIB = InterviewProcess::whereRaw($whereraw)->where('department',46)->get()->count();	
				}
				else{
					$InterviewList = InterviewProcess::where('department',46)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountDIB = InterviewProcess::where('department',46)->get()->count();	
				}					
				
				
				$InterviewList->setPath(config('app.url/interviewListDIB'));
				
				
				
				
			
			return view("InterviewProcess/InterviewListingDIB",compact('recruiterArray','JobOpningArray','SerialNumberArray','InterviewDateArray','CurrentStatusArray','CurrentInterviewArray','InterviewList','paginationValue','reportsCountDIB','selectedFilter','mobileArray','nameArray'));
		}
//SCB

public function interviewListSCB(Request $request){
		
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
				 
				  $empId  = $request->session()->get('EmployeeId');
				 $empmode = Employee::where("id",$empId)->first();
				 if($empmode != '')
				 {
					 $empGroupId = $empmode->group_id;
					 if($empGroupId == 22)
					 {
						 $whereraw = 'createdBy = '.$empId; 
					$request->session()->put('interview_recruiter_filter_inner_list','');
					 }
				 }
				 if($empId == 104)
				 {
					$selectedFilter['recruiter']= 9; 
					$request->session()->put('interview_recruiter_filter_inner_list',9);
				 }
				 else if($empId == 103)
				 {
					 $selectedFilter['recruiter']= 11;
					 $request->session()->put('interview_recruiter_filter_inner_list',11);
				 }
				/*  else if($empId == 102)
				 {
					 $selectedFilter['recruiter']= 12;
					 $request->session()->put('interview_recruiter_filter_inner_list',12);
				 } */
				 else if($empId == 101)
				 {
					 $selectedFilter['recruiter']= 8;
					 $request->session()->put('interview_recruiter_filter_inner_list',8);
				 }
				 else if($empId == 100)
				 {
					 $selectedFilter['recruiter']= 7;
					 $request->session()->put('interview_recruiter_filter_inner_list',7);
				 }
				 else if($empId == 99)
				 {
					 $selectedFilter['recruiter']= 10;
					 $request->session()->put('interview_recruiter_filter_inner_list',10);
				 }
				 
				 
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
					$cnameArray = explode(",",$name);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						//$whereraw = 'name like "%'.$name.'%"';
						$whereraw = 'name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And name IN('.$finalcname.')';
					}
				}
								if(!empty($request->session()->get('datefrom_filter_inner_list')) && $request->session()->get('datefrom_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at< "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at< "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_filter_inner_list')) && $request->session()->get('dateto_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at> "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at> "'.$dateto.' 00:00:00"';
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
					$currentinterviewarray = $request->session()->get('interview_currentinterview_filter_inner_list');
					if($currentinterviewarray!=''){
						$currentinterview=explode(',',$currentinterviewarray);
					}
					else{
						$currentinterview='';
					}
					
					 $selectedFilter['CurrentInterview'] = $currentinterview;
					 //print_r($currentinterview);
					 if($whereraw == '')
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
						if($interviewdetails != '')
						{
						$whereraw = 'id IN('.$interviewdetails.')';
						}
						
					}
					else
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
					   if($interviewdetails != '')
						{
						$whereraw .= ' And id IN('.$interviewdetails.')';
						}
					}
				}
				if(!empty($request->session()->get('interview_currentstatus_filter_inner_list')) && $request->session()->get('interview_currentstatus_filter_inner_list') != 'All')
				{
					$currentstatus = $request->session()->get('interview_currentstatus_filter_inner_list');
					 $selectedFilter['CurrentStatus'] = $currentstatus;
					 if($whereraw == '')
					{
						$whereraw = 'current_status IN('.$currentstatus.')';
					}
					else
					{
						$whereraw .= ' And current_status IN('.$currentstatus.')';
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
						$whereraw = 'job_opening IN('.$jobopning.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$jobopning.')';
					}
				}
				if(!empty($request->session()->get('interview_recruiter_filter_inner_list')) && $request->session()->get('interview_recruiter_filter_inner_list') != 'All')
				{
					$recruiter = $request->session()->get('interview_recruiter_filter_inner_list');
					 $selectedFilter['recruiter'] = $recruiter;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN('.$recruiter.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$recruiter.')';
					}
				}
				
				
				//echo $whereraw;//exit;
				$nameArray = array();
				if($whereraw == '')
				{
				$name = InterviewProcess::where('department',47)->get();
				}
				else
				{
					
					$name = InterviewProcess::whereRaw($whereraw)->where('department',47)->get();
					
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
				$mobile = InterviewProcess::where('department',47)->get();
				}
				else
				{
					
					$mobile = InterviewProcess::whereRaw($whereraw)->where('department',47)->get();
					
				}
				
				foreach($mobile as $_mobile)
				{
					//echo $_lname->last_name;exit;
					$mobileArray[$_mobile->mobile] = $_mobile->mobile;
				}
				
				$CurrentInterviewArray = array();
				
				if($whereraw == '')
				{
				$interview1 = InterviewProcess::where('department',47)->get();
				$CurrentInterview =array();
				
				foreach($interview1 as $_data){
				$CurrentInterview[]=$_data->id;
				}
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',47)->get();
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
				$interview1 = InterviewProcess::where('department',47)->get();
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',47)->get();
					
				}
				
				
				foreach($interview1 as $_interviewstatus)
				{
					//echo $_lname->last_name;exit;
					$CurrentStatusArray[$_interviewstatus->current_status] = $_interviewstatus->current_status;
				}
				
				$InterviewDateArray = array();
				if($whereraw == '')
				{
					
				$internaldate = InterviewProcess::where('department',47)->get();
				}
				else
				{
					
					$internaldate = InterviewProcess::whereRaw($whereraw)->where('department',47)->get();
					
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
					
				$serl = InterviewProcess::where('department',47)->get();
				}
				else
				{
					
					$serl = InterviewProcess::whereRaw($whereraw)->where('department',47)->get();
					
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
					
				$job = InterviewProcess::where('department',47)->get();
				$jobdata=array();
				foreach($job as $jobval){
				if($jobval->job_opening !=''){	
				$jobdata[]=$jobval->job_opening;
				}
				}
				}
				else
				{
					$job = InterviewProcess::whereRaw($whereraw)->where('department',47)->get();
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
					
				$recruiter = InterviewProcess::where('department',47)->get();
				$recruiterdata=array();
				foreach($recruiter as $recruiterval){
				if($recruiterval->recruiter !=''){	
				$recruiterdata[]=$recruiterval->recruiter;
				}
				}
				}
				else
				{
					$recruiter = InterviewProcess::whereRaw($whereraw)->where('department',47)->get();
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
					$InterviewList = InterviewProcess::whereRaw($whereraw)->where('department',47)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountSCB = InterviewProcess::whereRaw($whereraw)->where('department',47)->get()->count();	
				}
				else{
					$InterviewList = InterviewProcess::where('department',47)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountSCB = InterviewProcess::where('department',47)->get()->count();	
				}					
				
				
				$InterviewList->setPath(config('app.url/interviewListSCB'));
				
				
				
				
			
			return view("InterviewProcess/InterviewListingSCB",compact('recruiterArray','JobOpningArray','SerialNumberArray','InterviewDateArray','CurrentStatusArray','CurrentInterviewArray','InterviewList','paginationValue','reportsCountSCB','selectedFilter','mobileArray','nameArray'));
		}	

public function interviewListCBD(Request $request){
		
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
				 
				  $empId  = $request->session()->get('EmployeeId');
				 $empmode = Employee::where("id",$empId)->first();
				 if($empmode != '')
				 {
					 $empGroupId = $empmode->group_id;
					 if($empGroupId == 22)
					 {
						 $whereraw = 'createdBy = '.$empId; 
					$request->session()->put('interview_recruiter_filter_inner_list','');
					 }
				 }
				 if($empId == 104)
				 {
					$selectedFilter['recruiter']= 9; 
					$request->session()->put('interview_recruiter_filter_inner_list',9);
				 }
				 else if($empId == 103)
				 {
					 $selectedFilter['recruiter']= 11;
					 $request->session()->put('interview_recruiter_filter_inner_list',11);
				 }
				/*  else if($empId == 102)
				 {
					 $selectedFilter['recruiter']= 12;
					 $request->session()->put('interview_recruiter_filter_inner_list',12);
				 } */
				 else if($empId == 101)
				 {
					 $selectedFilter['recruiter']= 8;
					 $request->session()->put('interview_recruiter_filter_inner_list',8);
				 }
				 else if($empId == 100)
				 {
					 $selectedFilter['recruiter']= 7;
					 $request->session()->put('interview_recruiter_filter_inner_list',7);
				 }
				 else if($empId == 99)
				 {
					 $selectedFilter['recruiter']= 10;
					 $request->session()->put('interview_recruiter_filter_inner_list',10);
				 }
				 
				 
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
					$cnameArray = explode(",",$name);
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						//$whereraw = 'name like "%'.$name.'%"';
						$whereraw = 'name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And name IN('.$finalcname.')';
					}
				}
								if(!empty($request->session()->get('datefrom_filter_inner_list')) && $request->session()->get('datefrom_filter_inner_list') != 'All')
				{
					$datefrom = $request->session()->get('datefrom_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at< "'.$datefrom.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at< "'.$datefrom.' 00:00:00"';
					}
				}
				if(!empty($request->session()->get('dateto_filter_inner_list')) && $request->session()->get('dateto_filter_inner_list') != 'All')
				{
					$dateto = $request->session()->get('dateto_filter_inner_list');
					 if($whereraw == '')
					{
						$whereraw = 'created_at> "'.$dateto.' 00:00:00"';
					}
					else
					{
						$whereraw .= ' And created_at> "'.$dateto.' 00:00:00"';
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
					$currentinterviewarray = $request->session()->get('interview_currentinterview_filter_inner_list');
					if($currentinterviewarray!=''){
						$currentinterview=explode(',',$currentinterviewarray);
					}
					else{
						$currentinterview='';
					}
					
					 $selectedFilter['CurrentInterview'] = $currentinterview;
					 //print_r($currentinterview);
					 if($whereraw == '')
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
						if($interviewdetails != '')
						{
						$whereraw = 'id IN('.$interviewdetails.')';
						}
						
					}
					else
					{
						$interview= InterviewDetailsProcess::whereIn('interview_type',$currentinterview)->orderBy("id","DESC")->get();
						$interviewarr=array();
						foreach($interview as $_inter){
						$interviewdata=InterviewDetailsProcess::where('interview_id',$_inter->interview_id)->orderBy("id","DESC")->first();
						if($interviewdata!=''){
						if(in_array($interviewdata->interview_type,$currentinterview)){
						$interviewarr[]=$_inter->interview_id;
						}
						}
						}
						$interviewdetails=implode(",",$interviewarr);
					   if($interviewdetails != '')
						{
						$whereraw .= ' And id IN('.$interviewdetails.')';
						}
					}
				}
				if(!empty($request->session()->get('interview_currentstatus_filter_inner_list')) && $request->session()->get('interview_currentstatus_filter_inner_list') != 'All')
				{
					$currentstatus = $request->session()->get('interview_currentstatus_filter_inner_list');
					 $selectedFilter['CurrentStatus'] = $currentstatus;
					 if($whereraw == '')
					{
						$whereraw = 'current_status IN('.$currentstatus.')';
					}
					else
					{
						$whereraw .= ' And current_status IN('.$currentstatus.')';
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
						$whereraw = 'job_opening IN('.$jobopning.')';
					}
					else
					{
						$whereraw .= ' And job_opening IN('.$jobopning.')';
					}
				}
				if(!empty($request->session()->get('interview_recruiter_filter_inner_list')) && $request->session()->get('interview_recruiter_filter_inner_list') != 'All')
				{
					$recruiter = $request->session()->get('interview_recruiter_filter_inner_list');
					 $selectedFilter['recruiter'] = $recruiter;
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN('.$recruiter.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$recruiter.')';
					}
				}
				
				
				//echo $whereraw;//exit;
				$nameArray = array();
				if($whereraw == '')
				{
				$name = InterviewProcess::where('department',49)->get();
				}
				else
				{
					
					$name = InterviewProcess::whereRaw($whereraw)->where('department',49)->get();
					
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
				$mobile = InterviewProcess::where('department',49)->get();
				}
				else
				{
					
					$mobile = InterviewProcess::whereRaw($whereraw)->where('department',49)->get();
					
				}
				
				foreach($mobile as $_mobile)
				{
					//echo $_lname->last_name;exit;
					$mobileArray[$_mobile->mobile] = $_mobile->mobile;
				}
				
				$CurrentInterviewArray = array();
				
				if($whereraw == '')
				{
				$interview1 = InterviewProcess::where('department',49)->get();
				$CurrentInterview =array();
				
				foreach($interview1 as $_data){
				$CurrentInterview[]=$_data->id;
				}
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',49)->get();
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
				$interview1 = InterviewProcess::where('department',49)->get();
				}
				else
				{
					
					$interview1 = InterviewProcess::whereRaw($whereraw)->where('department',49)->get();
					
				}
				
				
				foreach($interview1 as $_interviewstatus)
				{
					//echo $_lname->last_name;exit;
					$CurrentStatusArray[$_interviewstatus->current_status] = $_interviewstatus->current_status;
				}
				
				$InterviewDateArray = array();
				if($whereraw == '')
				{
					
				$internaldate = InterviewProcess::where('department',49)->get();
				}
				else
				{
					
					$internaldate = InterviewProcess::whereRaw($whereraw)->where('department',49)->get();
					
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
					
				$serl = InterviewProcess::where('department',49)->get();
				}
				else
				{
					
					$serl = InterviewProcess::whereRaw($whereraw)->where('department',49)->get();
					
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
					
				$job = InterviewProcess::where('department',49)->get();
				$jobdata=array();
				foreach($job as $jobval){
				if($jobval->job_opening !=''){	
				$jobdata[]=$jobval->job_opening;
				}
				}
				}
				else
				{
					$job = InterviewProcess::whereRaw($whereraw)->where('department',49)->get();
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
					
				$recruiter = InterviewProcess::where('department',49)->get();
				$recruiterdata=array();
				foreach($recruiter as $recruiterval){
				if($recruiterval->recruiter !=''){	
				$recruiterdata[]=$recruiterval->recruiter;
				}
				}
				}
				else
				{
					$recruiter = InterviewProcess::whereRaw($whereraw)->where('department',49)->get();
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
					$InterviewList = InterviewProcess::whereRaw($whereraw)->where('department',49)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountCBD = InterviewProcess::whereRaw($whereraw)->where('department',49)->get()->count();	
				}
				else{
					$InterviewList = InterviewProcess::where('department',49)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCountCBD = InterviewProcess::where('department',49)->get()->count();	
				}					
				
				
				$InterviewList->setPath(config('app.url/interviewListCBD'));
				
				
				
				
			
			return view("InterviewProcess/InterviewListingCBD",compact('recruiterArray','JobOpningArray','SerialNumberArray','InterviewDateArray','CurrentStatusArray','CurrentInterviewArray','InterviewList','paginationValue','reportsCountCBD','selectedFilter','mobileArray','nameArray'));
		}
	public function checkDesignationParmission($rowId,$job){
		
		$jobOpning=JobOpening::where('id',$job)->where("status",1)->first();
		if($jobOpning !=''){
		$design=$jobOpning->designation;
		$Designation=DesignationParmission::where("designation_id",$design)->first();
		$designstatus =1;
		if($Designation!=''){
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$interviewdetail4 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'final discussion')->first();
		$designstatus  = 2;
		return view("InterviewProcess/Dropdownrec",compact('jobRecruiterDetails','interviewdetail4','designstatus'));
		}
		else
		{
			$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
			$interviewdetail4 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'final discussion')->first();
			return view("InterviewProcess/Dropdownrec",compact('jobRecruiterDetails','interviewdetail4','designstatus'));
		}
		
		}
		
		
	}
public static function getdesignNameId($job){
		$jobOpning=JobOpening::where('id',$job)->where("status",1)->first();
		if($jobOpning !=''){
		$design=$jobOpning->designation;
		return $design;
			  }
			  else
			  {
			  return '';
			  }
		
	}	
		
}
