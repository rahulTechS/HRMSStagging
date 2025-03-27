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
use App\Models\Onboarding\DocumentCollectionDetails;
use Carbon\Carbon;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Employee\Employee_details;

class InterviewProcessController extends Controller
{
    public function InterviewProcess()
	{
		//echo "hello";exit;
		return view("InterviewProcess/InterviewList");
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
			$LastInsertId="updated";
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
			if(!empty($newFileName)){
			$img=$newFileName;
			}
			else{
			$img='';
			}
			$date = Carbon::now();
			$formatedDate = $date->format('Y-m-d');
			
			$obj = new InterviewProcess();
			$obj->name = $rq->input('name');
			$obj->mobile = $rq->input('mobile');
			$obj->visa_requirement = $rq->input('visa_requirement');
			$obj->attached_cv = $img;
			$obj->job_opening = $rq->input('job_opening');
			$obj->current_status = $rq->input('status');
			$obj->internal_date = $formatedDate;
			$obj->location = $rq->input('location');
			$obj->recruiter = $rq->input('recruiter');
			$obj->designation = $rq->input('designation');
			if($rq->input('status')==1){
				$obj->status = $rq->input('status');
			}
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
			$interview->interview_type = 'Interview4';
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
				 
				 if(!empty($request->session()->get('interview_name_filter_inner_list')) && $request->session()->get('interview_name_filter_inner_list') != 'All')
				{
					$name = $request->session()->get('interview_name_filter_inner_list');
					 $selectedFilter['name'] = $name;
					 if($whereraw == '')
					{
						$whereraw = 'name = "'.$name.'"';
					}
					else
					{
						$whereraw .= ' And name = "'.$name.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('interview_mobile_filter_inner_list')) && $request->session()->get('interview_mobile_filter_inner_list') != 'All')
				{
					$mobile = $request->session()->get('interview_mobile_filter_inner_list');
					 $selectedFilter['mobile'] = $mobile;
					 if($whereraw == '')
					{
						$whereraw = 'mobile = "'.$mobile.'"';
					}
					else
					{
						$whereraw .= ' And mobile = "'.$mobile.'"';
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
						$whereraw = ' And id IN('.$interviewdetails.')';
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
				
				
				
				
			
			return view("InterviewProcess/InterviewListing",compact('InterviewDateArray','CurrentStatusArray','CurrentInterviewArray','InterviewList','paginationValue','reportsCount','selectedFilter','mobileArray','nameArray'));
		}
		public function interviewListcomplete(Request $request){
		
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
				 if(!empty($request->session()->get('interview_namecomplete_filter_inner_list')) && $request->session()->get('interview_namecomplete_filter_inner_list') != 'All')
				{
					$namecomplete = $request->session()->get('interview_namecomplete_filter_inner_list');
					 $selectedFilter['name'] = $namecomplete;
					 if($whereraw == '')
					{
						$whereraw = 'name = "'.$namecomplete.'"';
					}
					else
					{
						$whereraw .= ' And name = "'.$namecomplete.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('interview_mobilecomplete_filter_inner_list')) && $request->session()->get('interview_mobilecomplete_filter_inner_list') != 'All')
				{
					$mobilecomplete = $request->session()->get('interview_mobilecomplete_filter_inner_list');
					 $selectedFilter['mobile'] = $mobilecomplete;
					 if($whereraw == '')
					{
						$whereraw = 'mobile = "'.$mobilecomplete.'"';
					}
					else
					{
						$whereraw .= ' And mobile = "'.$mobilecomplete.'"';
					}
				}
				$nameArray = array();
				if($whereraw == '')
				{
				$name = InterviewProcess::where('interview_type',2)->get();
				}
				else
				{
					
					$name = InterviewProcess::where('interview_type',2)->whereRaw($whereraw)->get();
					
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
				$mobile = InterviewProcess::where('interview_type',2)->get();
				}
				else
				{
					
					$mobile = InterviewProcess::where('interview_type',2)->whereRaw($whereraw)->get();
					
				}
				
				foreach($mobile as $_mobile)
				{
					//echo $_lname->last_name;exit;
					$mobileArray[$_mobile->mobile] = $_mobile->mobile;
				}
				if($whereraw != '')
				{
					$InterviewList = InterviewProcess::where('interview_type',2)->whereRaw($whereraw)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCount = InterviewProcess::where('interview_type',2)->whereRaw($whereraw)->get()->count();	
				}
				else{
					$InterviewList = InterviewProcess::where('interview_type',2)->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCount = InterviewProcess::where('interview_type',2)->get()->count();	
				}					
				
				
				$InterviewList->setPath(config('app.url/interviewList'));
				
				
				
				
			
			return view("InterviewProcess/InterviewListingComplete",compact('InterviewList','paginationValue','reportsCount','selectedFilter','mobileArray','nameArray'));
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
		
		public function setFilterbyInterviewNameCoplete(Request $request)
		{
			$namecomplete = $request->namecomplete;
			$request->session()->put('interview_namecomplete_filter_inner_list',$namecomplete);	
		}
		public function setfilterByInterviewMobileComplete(Request $request)
		{
			$mobilecomplete = $request->mobilecomplete;
			$request->session()->put('interview_mobilecomplete_filter_inner_list',$mobilecomplete);	
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
		$interviewdetail1 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview1')->first();
			
		$interviewdetail2 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview2')->first();
		$interviewdetail3 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview3')->first();
		$interviewdetail4 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview4')->first();
		$jobRecruiterDetails=RecruiterDetails::where("status",1)->get();
		$interviewList= InterviewProcess::where("id",$rowId)->first();
		$jobid=$interviewList->job_opening;
		if($jobid!=''){
		$jobOpningdata=JobOpening::where('id',$jobid)->where("status",1)->first();
		$deptid=$jobOpningdata->department;
		$array=array();
		$array[]='Sales Executive';
		$array[]='Team Leader';
		$tL_details = Employee_details::where("dept_id",$deptid)->whereIn("job_role",$array)->get();
		}
		$jobOpning=JobOpening::where("status",1)->get();
		$visaTypeList = visaType::whereIn('id',array(7,8,3,4,2))->where("status",1)->orderBy("id","DESC")->get();
		//$tL_details = Employee_details::where("dept_id",$dept)->where("job_role","Team Leader")->orderBy("id","ASC")->get();
		
		//print_r($interviewdetail1);exit;
		return view("InterviewProcess/UpdateInterviewForm",compact('tL_details','jobRecruiterDetails','jobOpning','visaTypeList','interviewdetail1','interviewdetail2','interviewdetail3','interviewdetail4','interviewList'));
	}
	public function updateInterviewProcessPost0(Request $rq)
	{
		$keys = array_keys($_FILES);
					
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				
				if(!empty($rq->file($key)))
				{
				$filenameWithExt = $rq->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$rq->file($key)->getClientOriginalExtension();
				$vKey = $key;
				$newFileName = $key.'-interview-updated'.'.'.$fileExtension;
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
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->name = $rq->input('name');
		$obj->mobile = $rq->input('mobile');
		$obj->visa_requirement = $rq->input('visa_requirement');
		$obj->attached_cv = $img;
		$obj->job_opening = $rq->input('job_opening');
		$obj->location = $rq->input('location');
		$obj->recruiter = $rq->input('recruiter');
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
		
		if($rq->input('status')==1){
		$obj = InterviewProcess::find($rq->input('id'));
		$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
		$obj->interview_type = 'Interview4';
		$obj->save();
		}
		if($rq->input('status')==2){
		$obj = InterviewProcess::find($rq->input('id'));
		//$obj->status = $rq->input('status');
		$obj->current_status = $rq->input('status');
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
		}
		else{
			$departmentname=9;
		}
		
		$DocumentCollectionDetails = new DocumentCollectionDetails();
		$DocumentCollectionDetails->emp_name=$name;
		$DocumentCollectionDetails->mobile_no=$mobile;
		$DocumentCollectionDetails->department=$departmentname;
		$DocumentCollectionDetails->designation=10;
		$DocumentCollectionDetails->status=1;
		$DocumentCollectionDetails->save();
		}
		
			
		$interviewdetails =InterviewDetailsProcess::where("interview_id",$rq->input('id'))->where("interview_type",'Interview4')->first();
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
			$interview->interview_type = 'Interview4';
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
		$interviewdetail4 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview4')->first();
		$interviewList= InterviewProcess::where("id",$rowId)->first();
		//print_r($interviewdetail1);exit;
		return view("InterviewProcess/viewInterview",compact('interviewdetail1','interviewdetail2','interviewdetail3','interviewdetail4','interviewList'));
	}
	public function viewInterviewDataComplete($rowId=NULL)
	{
		$interviewdetail1 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview1')->first();
			
		$interviewdetail2 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview2')->first();
		$interviewdetail3 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview3')->first();
		$interviewdetail4 = InterviewDetailsProcess::where("interview_id",$rowId)->where("interview_type",'Interview4')->first();
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
		$response['code'] = '200';
		  $response['dept'] =$jobOpning->department;
			echo json_encode($response);
		   exit;
	}
	public function getempdetalbyinterviewSE($dept=NULL){
		$array=array();
		$array[]='Sales Executive';
		$array[]='Team Leader';
		$tL_details = Employee_details::where("dept_id",$dept)->whereIn("job_role",$array)->get();
		//echo "<pre>";
				//print_r($tL_details);exit;	
		return view("InterviewProcess/DropdownFormDept",compact('tL_details'));
	}
	public function getempdetalbyinterviewTL($dept=NULL){
		$tL_details = Employee_details::where("dept_id",$dept)->where("job_role","Team Leader")->orderBy("id","ASC")->get();
					
		return view("InterviewProcess/DropdownFormDept",compact('tL_details'));
	}
		 
	
}
