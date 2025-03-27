<?php

namespace App\Http\Controllers\Consultancy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Consultancy\ConsultancyModel;
use App\Models\Consultancy\Resumedetails;

use App\Models\Entry\Employee;
use App\Models\Recruiter\Designation;
use App\Models\Recruiter\Recruiter;
use App\Models\Recruiter\ResumeHistroy;
use Crypt;
use Session;
use Illuminate\Support\Facades\File; 

class ResumeController extends Controller
{
	
		public function addResume()
		{
			$designationMOD = Designation::where("status",1)->get();
			return view("Consultancy/Resume/addResume",compact('designationMOD'));
		}
		
		public function resumePost(Request $request)
		{			
			$requestInput = $request->input();
			$employeeId = $request->session()->get('EmployeeId');
			/*
			*check mobile no can not exist
			*/
			
			$consultancyModCount = Resumedetails::where('condidate_no',$request->input('contact_no'))->get()->count();
			if($consultancyModCount >0)
			{
				$request->session()->flash('message','Mobile No is already exist. Please change mobile number.');
				$request->session()->flash('alert-class', 'alert-danger'); 
				return redirect()->back()->withInput();;
			}
			/*
			*check mobile no can not exist
			*/
			
			/* $request->validate([

            'file' => 'required|mimes:doc,txt,pdf,docx|max:2048',

        ]); */
			if($request->file->getClientOriginalExtension() != 'doc' && $request->file->getClientOriginalExtension() != 'docx' && $request->file->getClientOriginalExtension() != 'pdf')
			{
				$request->session()->flash('message','Resume Format should be Doc or PDF.');
				$request->session()->flash('alert-class', 'alert-danger'); 
				return redirect()->back()->withInput();;
			}
			if($request->input('resume_content') == '')
			{
				$request->session()->flash('message','Please copy & paste resume Content in "Add Resume Content" Editor, Then proceed.');
				$request->session()->flash('alert-class', 'alert-danger'); 
				return redirect()->back()->withInput();;
			}
			/*
			*adding resume
			*/
			$consultancyMod = ConsultancyModel::where('employee_id',$employeeId)->get()->first();
			$resumeObjImport = new Resumedetails();
            $resumeObjImport->condidate_no = $request->input('contact_no');
            $resumeObjImport->candidate_name = $request->input('candidate_name');
          
            $resumeObjImport->consultancy_id = $consultancyMod->id;
            $resumeObjImport->emp_id = $employeeId;
            $resumeObjImport->status = 1;
            $resumeObjImport->resume_status = 1;
            $resumeObjImport->resume_designation = $request->input('resume_designation');
            $resumeData = $request->input('resume_content');
			$resumeData = trim($resumeData);
			$resumeData = stripslashes($resumeData);
			$resumeData = htmlspecialchars($resumeData);
			$resumeObjImport->resume_content = $resumeData;
            
			/*
			*adding resume
			*/
			$extension = $request->file->getClientOriginalExtension();
			
			$fileName = $request->input('candidate_name').'_'.$request->input('contact_no').'_'.$employeeId.'_resume.'.$extension;  
			$request->file->move(public_path('uploads/consultancyResume'), $fileName);
			 
			
			 $resumeObjImport->resume_name = $fileName;
			 
			 $resumeObjImport->save();
        
			$request->session()->flash('message','You have successfully upload resume.');
				//$request->session()->flash('alert-class', 'alert-danger'); 
				return redirect('manageResume');
		}
    
	public function manageResume(Request $request)
	{
		$employeeId = $request->session()->get('EmployeeId');
		$consultancyMod = ConsultancyModel::where('employee_id',$employeeId)->get()->first();
		$resumeLists = Resumedetails::where("consultancy_id",$consultancyMod->id)->get();
		
		return view("Consultancy/Resume/manageResume",compact('resumeLists'));
	}
	
	public function deleteResume(Request $request)
	{
			$resumeDetails = Resumedetails::where("id",$request->id)->get()->first();
			
			if($resumeDetails->resume_status != 1)
			{
				$request->session()->flash('message','Resume has been processed by our team so you can not delete.');
					$request->session()->flash('alert-class', 'alert-danger'); 
				return redirect('manageResume');
			}
			else
			{
				$filename =public_path('uploads/consultancyResume').'/'.$resumeDetails->resume_name; 
				
				File::delete($filename);
				Resumedetails::where("id",$request->id)->delete();
				$request->session()->flash('message','Resume Deleted Successfully.');
				return redirect('manageResume');
			}
			
	}
	
	public function getResume(Request $request)
	{
		$consultancyId = $request->id;
		$fromdate = $request->fromdate;
		$todate = $request->todate;
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
			$resumeLists = Resumedetails::where("consultancy_id",$consultancyId)->where("resume_status",1)->where("resume_designation",$recruit_designation)->whereBetween('created_at', [$fromdate, $todate])->get();
		}
		else
		{
			$resumeLists = Resumedetails::where("consultancy_id",$consultancyId)->where("resume_status",1)->whereBetween('created_at', [$fromdate, $todate])->get();
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
		return view("Consultancy/Resume/getResume",compact('resumeLists','resumeCount','layoutName'));
		
	}
	
	public function downloadResume(Request $request)
	{
	
		$filepath = '/srv/www/htdocs/hrm/public/uploads/consultancyResume/'.$request->filename;
	 $file = $filepath;

        if (file_exists($file)) {
			$filename = $request->filename;
           $contenttype = "application/force-download";
			header("Content-Type: " . $contenttype);
			header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";");
			readfile($file);
			exit();
            exit;
        }
	}
	
	public function setResumeStatus(Request $request)
	{
		
		$status =  $request->status;
		
		$resumeIdtxt = explode("_",$request->id);
		$resumeId = $resumeIdtxt[1];
		
		$_resumeObj = Resumedetails::find($resumeId);
		
		if($status == 'shortlisted')
		{
			$_resumeObj->resume_status = 2;
			/*
			*@description load Resume Histroy
			*/
			$resume_histroyObj = new ResumeHistroy();
			$resume_histroyObj->resume_id = $resumeId;
			$resume_histroyObj->status_name	 = "Shortlisted";
			$resume_histroyObj->status_id	 = 1;
			$resume_histroyObj->save();
			/*
			*@description load Resume Histroy
			*/
		}
		if($status == 'rejected')
		{
		$_resumeObj->resume_status = 3;
		}
		$_resumeObj->feedback = "";
		$_resumeObj->save();
	}
	
	public function setResumeFeedback(Request $request)
	{
		$details = $request->input();
		
		
		
		$status =  $request->input("statusResume");
		
		$resumeIdtxt = explode("_",$request->input("idtext"));
		$resumeId = $resumeIdtxt[1];
		
		$_resumeObj = Resumedetails::find($resumeId);
		
		if($status == 'shortlisted')
		{
			$_resumeObj->resume_status = 2;
			/*
			*@description load Resume Histroy
			*/
			$resume_histroyObj = new ResumeHistroy();
			$resume_histroyObj->resume_id = $resumeId;
			$resume_histroyObj->status_name	 = "Shortlisted";
			$resume_histroyObj->status_id	 = 1;
			$resume_histroyObj->save();
			/*
			*@description load Resume Histroy
			*/
		}
		if($status == 'rejected')
		{
			$_resumeObj->resume_status = 3;
		}
			$_resumeObj->feedback = $request->input("feedback");
			$_resumeObj->save();
	}
	
	public function historyResume(Request $request)
	{
		$resumehistroyAfterShortlist = array();
		$resumeId = $request->id;
		$resumedetails = Resumedetails::where("id",$resumeId)->get()->first();
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
			*if resume shortlisted then load resume histroy
			*/
			if($resumedetails->resume_status == 2)
			{
				$resumehistroyAfterShortlist = ResumeHistroy::where('resume_id',$resumeId)->orderBy('id', 'desc')->get();
			}
			/*
			*if resume shortlisted then load resume histroy
			*/
		return view("Consultancy/Resume/historyResume",compact('resumedetails','layoutName','resumehistroyAfterShortlist'));
		
	}
	
	public function resumeShortlisting(Request $request)
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
			
			$sessionValues['selectedrecordperpage'] = '8';
			$sessionValues['selectedresumeStatus'] = '';
			$sessionValues['selectedConsultancy'] = '';
			$sessionValues['selecteddateFrom'] = '';
			$sessionValues['selecteddateTo'] = '';
			if($request->session()->get('selectedConsultancy')  != '' )
			{
				$sessionValues['selectedConsultancy'] =$request->session()->get('selectedConsultancy');
			}
			if($request->session()->get('selectedresumeStatus') != '' )
			{
				
				$sessionValues['selectedresumeStatus'] =$request->session()->get('selectedresumeStatus');
			}
			if($request->session()->get('selectedrecordperpage') != '' )
			{
				$sessionValues['selectedrecordperpage'] =$request->session()->get('selectedrecordperpage');
				
			}
			if($request->session()->get('selecteddateFrom') != '' )
			{
				$sessionValues['selecteddateFrom'] =$request->session()->get('selecteddateFrom');
				
			}
			if($request->session()->get('selecteddateTo') != '' )
			{
				$sessionValues['selecteddateTo'] =$request->session()->get('selecteddateTo');
				
			}
			
			
			/*
			*Session Values
			*/
			/**
			*@getting all Datats needed for resume listing
			*/
			$datas['status'] = array('1'=>'Review Pending','2'=>'Shortlisted','3'=>'Rejected');
			$consultancyDetails = ConsultancyModel::where('status',1)->get();
			$datas['consultancyLists'] = $consultancyDetails;
			
			$whereCause = array();
			$whereCauseBetween = array();
			if($sessionValues['selectedresumeStatus'] != '')
			{
				$whereCause['resume_status'] = $sessionValues['selectedresumeStatus'];
			}
			
			if($sessionValues['selectedConsultancy'] != '')
			{
				$whereCause['consultancy_id'] = $sessionValues['selectedConsultancy'];
			}
			
			
			
			
			if(count($whereCause) >0 && ($sessionValues['selecteddateFrom'] != '' && $sessionValues['selecteddateTo'] != ''))
			{
				$fromdate = $sessionValues['selecteddateFrom'];
				$todate = $sessionValues['selecteddateTo'];
				$datas['resumeCount'] = Resumedetails::where($whereCause)->whereBetween('created_at', [$fromdate, $todate])->get();
			}
			elseif(count($whereCause) >0 && ($sessionValues['selecteddateFrom'] == '' && $sessionValues['selecteddateTo'] == ''))
			{
				$datas['resumeCount'] = Resumedetails::where($whereCause)->get();
			}
			elseif(count($whereCause) == 0 && ($sessionValues['selecteddateFrom'] != '' && $sessionValues['selecteddateTo'] != ''))
			{
				$fromdate = $sessionValues['selecteddateFrom'];
				$todate = $sessionValues['selecteddateTo'];
				$datas['resumeCount'] = Resumedetails::whereBetween('created_at', [$fromdate, $todate])->get();
			}
			else
			{
				$datas['resumeCount'] = Resumedetails::get();
			}
			/**
			*@getting all Datats needed for resume listing
			*/
		return view("Consultancy/Resume/resumeShortlisting",compact('layoutName','datas','sessionValues'));
	}
	
	public function resumeShortlistingFilter(Request $request)
	{
		/*
		*getting filter features
		*start coding
		*/
		$selectedFilter = $request->input();
		
		
		if(!empty($selectedFilter['consultancy']))
		{
			$request->session()->put('selectedConsultancy',$selectedFilter['consultancy']);
		}
		else
		{
			$request->session()->put('selectedConsultancy','');
		}
		
		if(!empty($selectedFilter['resumeStatus']))
		{
			$request->session()->put('selectedresumeStatus',$selectedFilter['resumeStatus']);
		}
		else
		{
			$request->session()->put('selectedresumeStatus','');
		}
		
		
		if(!empty($selectedFilter['recordperpage']))
		{
			$request->session()->put('selectedrecordperpage',$selectedFilter['recordperpage']);
		}
		else
		{
			$request->session()->put('selectedrecordperpage','');
		}
		
		
		if(!empty($selectedFilter['date_from']))
		{
			$request->session()->put('selecteddateFrom',$selectedFilter['date_from']);
		}
		else
		{
			$request->session()->put('selecteddateFrom','');
		}
		
		if(!empty($selectedFilter['date_to']))
		{
			$request->session()->put('selecteddateTo',$selectedFilter['date_to']);
		}
		else
		{
			$request->session()->put('selecteddateTo','');
		}
		
		return redirect('resumeShortlisting');
		/*
		*getting filter features
		*end coding
		*/
		
		
	}
	
	public function showResume(Request $request)
	{
		
			/**
			*@getting all Datats needed for resume listing
			*/
			$pagelimit= $request->pageLimit;
			$skip= $request->skip;
			$sessionValues['selectedresumeStatus'] = '';
			$sessionValues['selectedConsultancy'] = '';
			$sessionValues['selecteddateFrom'] = '';
			$sessionValues['selecteddateTo'] = '';
			if($request->session()->get('selectedConsultancy')  != '' )
			{
				$sessionValues['selectedConsultancy'] =$request->session()->get('selectedConsultancy');
			}
			if($request->session()->get('selectedresumeStatus') != '' )
			{
				
				$sessionValues['selectedresumeStatus'] =$request->session()->get('selectedresumeStatus');
			}
			if($request->session()->get('selecteddateFrom') != '' )
			{
				$sessionValues['selecteddateFrom'] =$request->session()->get('selecteddateFrom');
				
			}
			if($request->session()->get('selecteddateTo') != '' )
			{
				$sessionValues['selecteddateTo'] =$request->session()->get('selecteddateTo');
				
			}
			
			$whereCause = array();
			if($sessionValues['selectedresumeStatus'] != '')
			{
				$whereCause['resume_status'] = $sessionValues['selectedresumeStatus'];
			}
			
			if($sessionValues['selectedConsultancy'] != '')
			{
				$whereCause['consultancy_id'] = $sessionValues['selectedConsultancy'];
			}
			if(count($whereCause) >0 && ($sessionValues['selecteddateFrom'] != '' && $sessionValues['selecteddateTo'] != ''))
			{
				$fromdate = $sessionValues['selecteddateFrom'];
				$todate = $sessionValues['selecteddateTo'];
			$resumeLists = Resumedetails::where($whereCause)->whereBetween('created_at', [$fromdate, $todate])->skip($skip)->take($pagelimit)->orderBy('id', 'DESC')->get();
			}
			else if(count($whereCause) >0 && ($sessionValues['selecteddateFrom'] == '' && $sessionValues['selecteddateTo'] == ''))
			{
			$resumeLists = Resumedetails::where($whereCause)->skip($skip)->take($pagelimit)->orderBy('id', 'DESC')->get();
			}
			elseif(count($whereCause) == 0 && ($sessionValues['selecteddateFrom'] != '' && $sessionValues['selecteddateTo'] != ''))
			{
				$fromdate = $sessionValues['selecteddateFrom'];
				$todate = $sessionValues['selecteddateTo'];
				$resumeLists = Resumedetails::whereBetween('created_at', [$fromdate, $todate])->skip($skip)->take($pagelimit)->orderBy('id', 'DESC')->get();
			}
			else
			{
				$resumeLists = Resumedetails::skip($skip)->take($pagelimit)->orderBy('id', 'DESC')->get();
			}
			/**
			*@getting all Datats needed for resume listing
			*/
		return view("Consultancy/Resume/showResume",compact('resumeLists'));
	}
	
	public function resetSearch(Request $request)
	{
		$request->session()->put('selectedConsultancy','');
		$request->session()->put('selectedresumeStatus','');
		$request->session()->put('selectedrecordperpage','');
		$request->session()->put('selecteddateFrom','');
		$request->session()->put('selecteddateTo','');
		
		$request->session()->flash('message','Filter reset successfully.');
		return redirect('resumeShortlisting');
	}
	
}
