<?php

namespace App\Http\Controllers\Presentation;
require_once "/srv/www/htdocs/core/autoload.php";
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
use App\Models\Visa\Visaprocess;
use File;
class PresentationController extends Controller
{
    public function funnelDisplay(Request $request)
	{
		//echo "hello";exit;
			$departmentArray = Department::where("status",1)->get();
		$recruiterDetails = RecruiterDetails::where("status",1)->get();
		$whereRawInterview = '';
		$whereRawDocumentCollection = '';
		$deptFilterArray = array();
		$fromDateValue = '';
		$toDateValue = '';
		
	/*
	*interview process
	*/
			if( $request->session()->get('presentation_dept_f') != '')
			{
				$presentation_dept_f = $request->session()->get('presentation_dept_f');
				$presentation_dept_fArray = explode(",",$presentation_dept_f);
				$deptFilterArray = explode(",",$presentation_dept_f);
			
					if($whereRawInterview == '')
					{
						
						$whereRawInterview = ' department IN ('.$presentation_dept_f.')';
					}
					else
					{
						$whereRawInterview .= 'AND department IN ('.$presentation_dept_f.')';
					}
				
			}
			
			if( $request->session()->get('presentation_recruiter_f') != '')
			{
				$presentation_recruiter_f = $request->session()->get('presentation_recruiter_f');
				
			
					if($whereRawInterview == '')
					{
						
						$whereRawInterview = ' recruiter IN ('.$presentation_recruiter_f.')';
					}
					else
					{
						$whereRawInterview .= 'AND recruiter IN ('.$presentation_recruiter_f.')';
					}
				
			}
			
			if( $request->session()->get('presentation_location_f') != '')
			{
				$presentation_location_f = $request->session()->get('presentation_location_f');
				$presentation_location_fArray = explode(",",$presentation_location_f);
				
				$presentation_location_fsttr1 = '';
				foreach($presentation_location_fArray as $_parray)
				{
					if($presentation_location_fsttr1 == '')
					{
						$presentation_location_fsttr1 = '"'.$_parray.'"';
					}
					else
					{
						$presentation_location_fsttr1 = $presentation_location_fsttr1.',"'.$_parray.'"';
					}
				}
			
					if($whereRawInterview == '')
					{
						
						$whereRawInterview = ' location IN ('.$presentation_location_fsttr1.')';
					}
					else
					{
						$whereRawInterview .= 'AND location IN ('.$presentation_location_fsttr1.')';
					}
				
			}
			
			
			if($request->session()->get('presentation_from_f') != '')
			{
				$fromDateValue = $request->session()->get('presentation_from_f');
				$fromDate = date("Y-m-d 00:00:00",strtotime($request->session()->get('presentation_from_f')));
				if($whereRawInterview == '')
					{
						$whereRawInterview = ' created_at >= "'.$fromDate.'"';
					}
					else
					{
						$whereRawInterview .=' AND created_at >= "'.$fromDate.'"';
					}
			}
			
			if($request->session()->get('presentation_to_f') != '')
			{
				$toDateValue = $request->session()->get('presentation_to_f');
				$fromDate = date("Y-m-d 12:00:00",strtotime($request->session()->get('presentation_to_f')));
				if($whereRawInterview == '')
					{
						$whereRawInterview = ' created_at <= "'.$fromDate.'"';
					}
					else
					{
						$whereRawInterview .=' AND created_at <= "'.$fromDate.'"';
					}
			}
			//echo $whereRawInterview;exit;
	/*
	*interview process
	*/		
			
			
			if( $request->session()->get('presentation_dept_f') != '')
			{
				$presentation_dept_f = $request->session()->get('presentation_dept_f');
					if($whereRawDocumentCollection == '')
					{
						$whereRawDocumentCollection = ' department IN ('.$presentation_dept_f.')';
					}
					else
					{
						$whereRawDocumentCollection .=' AND department IN ('.$presentation_dept_f.')';;
					}
			}
			
			
			if( $request->session()->get('presentation_recruiter_f') != '')
			{
				$presentation_recruiter_f = $request->session()->get('presentation_recruiter_f');
					if($whereRawDocumentCollection == '')
					{
						$whereRawDocumentCollection = ' recruiter_name IN ('.$presentation_recruiter_f.')';
					}
					else
					{
						$whereRawDocumentCollection .=' AND recruiter_name IN ('.$presentation_recruiter_f.')';;
					}
			}
			if( $request->session()->get('presentation_location_f') != '')
			{
				$presentation_location_f = $request->session()->get('presentation_location_f');
				$presentation_location_fArray = explode(",",$presentation_location_f);
				$presentation_location_fsttr = '';
				foreach($presentation_location_fArray as $_parray)
				{
					if($presentation_location_fsttr == '')
					{
						$presentation_location_fsttr = '"'.$_parray.'"';
					}
					else
					{
						$presentation_location_fsttr = $presentation_location_fsttr.',"'.$_parray.'"';
					}
				}
					if($whereRawDocumentCollection == '')
					{
						$whereRawDocumentCollection = ' location IN ('.$presentation_location_fsttr.')';
					}
					else
					{
						$whereRawDocumentCollection .=' AND location IN ('.$presentation_location_fsttr.')';;
					}
			}
			
			if($request->session()->get('presentation_from_f') != '')
			{
				$fromDateValue = $request->session()->get('presentation_from_f');
				$fromDate = date("Y-m-d 00:00:00",strtotime($request->session()->get('presentation_from_f')));
				if($whereRawDocumentCollection == '')
					{
						$whereRawDocumentCollection = ' created_at >= "'.$fromDate.'"';
					}
					else
					{
						$whereRawDocumentCollection .=' AND created_at >= "'.$fromDate.'"';
					}
			}
			
			if($request->session()->get('presentation_to_f') != '')
			{
				$toDateValue = $request->session()->get('presentation_to_f');
				$fromDate = date("Y-m-d 12:00:00",strtotime($request->session()->get('presentation_to_f')));
				if($whereRawDocumentCollection == '')
					{
						$whereRawDocumentCollection = ' created_at <= "'.$fromDate.'"';
					}
					else
					{
						$whereRawDocumentCollection .=' AND created_at <= "'.$fromDate.'"';
					}
			}
			//echo $whereRawDocumentCollection;exit;
			/* echo $whereRawInterview;
			echo '<br />';
			echo $whereRawDocumentCollection;
			
			exit; */
		if($whereRawInterview == '')
		{
			$interview1Count = InterviewDetailsProcess::where("interview_type","Interview1")->get()->count();
		}
		else
		{
			$interview1Count = InterviewDetailsProcess::where("interview_type","Interview1")->whereRaw($whereRawInterview)->get()->count();
		}
		if($whereRawInterview == '')
		{
			$finalDiscussionCount = InterviewDetailsProcess::where("interview_type","Final Discussion")->where("status",2)->get()->count();
		}
		else
		{
			$finalDiscussionCount = InterviewDetailsProcess::where("interview_type","Final Discussion")->where("status",2)->whereRaw($whereRawInterview)->get()->count();
		}
		if($whereRawDocumentCollection != '')
		{
			$offerletterCompleted = DocumentCollectionDetails::where("offer_letter_onboarding_status",2)->whereRaw($whereRawDocumentCollection)->get()->count();
		}
		else
		{
			$offerletterCompleted = DocumentCollectionDetails::where("offer_letter_onboarding_status",2)->get()->count();	
		}
		if($whereRawDocumentCollection != '')
		{
			$visaProcessInprogress = DocumentCollectionDetails::where("visa_process_status",2)->whereRaw($whereRawDocumentCollection)->get()->count();
		}
		else
		{
			$visaProcessInprogress = DocumentCollectionDetails::where("visa_process_status",2)->get()->count();
		}
		
		if($whereRawDocumentCollection != '')
		{
			$visaProcessCompleted = DocumentCollectionDetails::where("visa_process_status",4)->whereRaw($whereRawDocumentCollection)->get()->count();
		}
		else
		{
			$visaProcessCompleted = DocumentCollectionDetails::where("visa_process_status",4)->get()->count();	
		}
		if($whereRawDocumentCollection != '')
		{
			$OnboardingCompleted = DocumentCollectionDetails::where("onboard_status",2)->whereRaw($whereRawDocumentCollection)->get()->count();
		}
		else
		{
			$OnboardingCompleted = DocumentCollectionDetails::where("onboard_status",2)->get()->count();	
		}
		
		
		$recNameArray = array();
		$locationFilterArray = array();
		return view("presentation/funnelDisplay",compact('fromDateValue','toDateValue','deptFilterArray','recNameArray','locationFilterArray','departmentArray','recruiterDetails','interview1Count','finalDiscussionCount','offerletterCompleted','visaProcessInprogress','visaProcessCompleted','OnboardingCompleted'));
	}
	public function DashboardPresentation(Request $request)
	{
	$departmentArray = Department::where("status",1)->get();
		$recruiterDetails = RecruiterDetails::where("status",1)->get();
			$deptFilterArray_f = array();
			if( $request->session()->get('presentation_dept_f') != '')
			{
				$presentation_dept_f = $request->session()->get('presentation_dept_f');
				$presentation_dept_fArray = explode(",",$presentation_dept_f);
				$deptFilterArray_f = explode(",",$presentation_dept_f);
			}
		if( $request->session()->get('presentation_jobopening_dept') != '')
			{
			$deptFilter = $request->session()->get('presentation_jobopening_dept');
						$deptFilterArray = explode(",",$deptFilter);
			}
			else
			{
			$deptFilterArray = array(9);
			}

if( $request->session()->get('presentation_jobopening_location') != '')
		{
				$locationFilter = $request->session()->get('presentation_jobopening_location');
			$locationFilterArray = explode(",",$locationFilter);
		}
		else
		{
			$locationFilterArray = array('DXB');
		}


if( $request->session()->get('presentation_jobopening_recruiter') != '')
		{
$recName = $request->session()->get('presentation_jobopening_recruiter');
$recNameArray = explode(",",$recName);
}
else
{

$recNameArray = array();
}


	$recNameArray_f = array();
	if( $request->session()->get('presentation_recruiter_f') != '')
			{
				$presentation_recruiter_f = $request->session()->get('presentation_recruiter_f');
				$recNameArray_f = explode(",",$presentation_recruiter_f);
			}
	$locationFilterArray_f = array();
	if( $request->session()->get('presentation_location_f') != '')
		{
			$locationFilterArray_f = explode(",",$request->session()->get('presentation_location_f'));
		}
	$fromDateValue = '';
	$toDateValue = '';
			if($request->session()->get('presentation_from_f') != '')
			{
				$fromDateValue = $request->session()->get('presentation_from_f');
			}
			
			if($request->session()->get('presentation_to_f') != '')
			{
				$toDateValue = $request->session()->get('presentation_to_f');
			}
			$fromjobopeningDate1 = '';
			$tojobopeningDate1 = '';
		if( $request->session()->get('presentation_jobopening_from') != '')
			{
					$fromjobopeningDate1 = date("Y-m-d",strtotime($request->session()->get('presentation_jobopening_from')));
					
			}
			
		if( $request->session()->get('presentation_jobopening_to') != '')
			{
					$tojobopeningDate1 = date("Y-m-d",strtotime($request->session()->get('presentation_jobopening_to')));
					
			}	
		return view("presentation/dashboardpresentation",compact('fromjobopeningDate1','tojobopeningDate1','fromDateValue','toDateValue','deptFilterArray_f','recNameArray_f','locationFilterArray_f','departmentArray','deptFilterArray','recruiterDetails','locationFilterArray','recNameArray'));
	}
	
	public function onboardingPresentation(Request $request)
	{
		$whererawDocCollection = '';
		if( $request->session()->get('presentation_jobopening_from') != '')
			{
					$fromjobopeningDate = date("Y-m-d 00:00:00",strtotime($request->session()->get('presentation_jobopening_from')));
					if($whererawDocCollection == '')
					{
						$whererawDocCollection = 'created_at >= "'.$fromjobopeningDate.'"';
					}
					else
					{
						$whererawDocCollection .= ' AND created_at >= "'.$fromjobopeningDate.'"';
					}
			}
			
		if( $request->session()->get('presentation_jobopening_to') != '')
			{
					$tojobopeningDate = date("Y-m-d 12:00:00",strtotime($request->session()->get('presentation_jobopening_to')));
					if($whererawDocCollection == '')
					{
						$whererawDocCollection = 'created_at <= "'.$tojobopeningDate.'"';
					}
					else
					{
						$whererawDocCollection .= ' AND created_at <= "'.$tojobopeningDate.'"';
					}
			}	
			
		//echo $whererawDocCollection;exit;
		if( $request->session()->get('presentation_jobopening_recruiter') != '')
			{
				$recName = $request->session()->get('presentation_jobopening_recruiter');
				$recNameArray = explode(",",$recName);
				if($whererawDocCollection == '')
				{
					$collection = DocumentCollectionDetails::whereIn("recruiter_name",$recNameArray)->groupBy('job_opening')
					->selectRaw('count(*) as total, job_opening')
					->get();
				}
				else
				{
					$collection = DocumentCollectionDetails::whereIn("recruiter_name",$recNameArray)->whereRaw($whererawDocCollection)->groupBy('job_opening')
					->selectRaw('count(*) as total, job_opening')
					->get();
				}
			}
		else
			{
				if($whererawDocCollection == '')
				{
					$collection = DocumentCollectionDetails::groupBy('job_opening')
					->selectRaw('count(*) as total, job_opening')
					->get();
				}
				else
				{
					$collection = DocumentCollectionDetails::groupBy('job_opening')->whereRaw($whererawDocCollection)
					->selectRaw('count(*) as total, job_opening')
					->get();
				}
				$recNameArray = '';
			}
	
	
	$departmentArray = Department::where("status",1)->get();
	$recruiterDetails = RecruiterDetails::where("status",1)->get();

	$colorCode = array("#4F5060","#67819D","#ADBD37","#588133","#003B45");
	$data = array();
	$index = 0;
	$indexColor = 0;
	$whereRaw = '';
	foreach($collection as $_collection)
	{
		
		if( $request->session()->get('presentation_jobopening_dept') != '')
		{
			$deptFilter = $request->session()->get('presentation_jobopening_dept');
			$deptFilterArray = explode(",",$deptFilter);
			if($whereRaw == '')
			{
				$whereRaw = 'department IN ('.$deptFilter.')';
			}
			else
			{
				$whereRaw .= ' AND department IN ('.$deptFilter.')';
			}
		}
		else
		{
			$deptFilterArray = array(9);
			if($whereRaw == '')
			{
				$whereRaw = 'department IN (9)';
			}
			else
			{
				$whereRaw .= ' AND department IN (9)';
			}
			
		}

if( $request->session()->get('presentation_jobopening_location') != '')
		{
			$locationFilter = $request->session()->get('presentation_jobopening_location');
			$locationFilterArray = explode(",",$locationFilter);
$locationFilter1 = '';
			foreach($locationFilterArray as $loc)
				{
					if($locationFilter1 == '')
					{
						$locationFilter1  = "'".$loc."'";
					}
					else
					{
						$locationFilter1 = $locationFilter1.","."'".$loc."'";
					}
		
				}
			if($whereRaw == '')
			{
				
				$whereRaw = "location IN (".$locationFilter1.")";
			}
			else
			{
				$whereRaw .= " AND location IN (".$locationFilter1.")";
			}
		}
		else
		{
			$locationFilterArray = array('DXB');
			if($whereRaw == '')
			{
				$whereRaw = 'location IN ("DXB")';
			}
			else
			{
				$whereRaw .= ' AND location IN ("DXB")';
			}
			
		}
		
		
		$jobOpeningData = JobOpening::where("id",$_collection->job_opening)->whereRaw($whereRaw)->first();
		
		if($jobOpeningData != '')
		{
		$data[$index]['jobopening']= $jobOpeningData->name.'-'.$jobOpeningData->location.'-'.$this->getDepartmentName($jobOpeningData->department);
		$data[$index]['total']= $_collection->total;
		$data[$index]['color']= $colorCode[$indexColor];
		if($indexColor == 4)
		{
				$indexColor = 0;
		}
		else
		{
		$indexColor++;
		}
		$index++;
		}
	}
	
	
	
/* 	echo '<pre>';
	print_r($data);
	exit; */
		return view("presentation/onboardingpresentation",compact('data','departmentArray','deptFilterArray','recruiterDetails','locationFilterArray'));
	}

protected function getDepartmentName($deptId)
	{
		return Department::where("id",$deptId)->first()->department_name;
	}

public function searchjobopeningwidget(Request $request)
{

	$department = $request->department;
   
	$recruiter = $request->recruiter;
	$locationdata = $request->locationdata;
	$from_date = $request->from_date;
	$to_date = $request->to_date;
		if($department!=''){
			$deptarr = array_filter($department);
			$deptstr=implode(",", $deptarr);
			}
		else
			{
			$deptstr='';
			}

			if($locationdata!=''){
			$locationarr = array_filter($locationdata);
			$locationstr=implode(",", $locationarr);
			}
		else
			{
			$locationstr='';
			}


if($recruiter!=''){
			$recarr = array_filter($recruiter);
			$recstr=implode(",", $recarr);
			}
		else
			{
			$recstr='';
			}

	$request->session()->put('presentation_jobopening_dept',$deptstr);
	$request->session()->put('presentation_jobopening_location',$locationstr);
	$request->session()->put('presentation_jobopening_recruiter',$recstr);
	$request->session()->put('presentation_jobopening_from',$from_date);
	$request->session()->put('presentation_jobopening_to',$to_date);
	
}



public function searchFunnelwidget(Request $request)
{
	

	$departmentf = $request->department_f;
	$recruiterf = $request->recruiter_f;
	$locationdataf = $request->locationdata_f;
	$from_datef = $request->from_date_f;
	$to_datef = $request->to_date_f;
		if($departmentf!=''){
			$deptarr = array_filter($departmentf);
			$deptstr=implode(",", $deptarr);
			}
		else
			{
			$deptstr='';
			}

			if($locationdataf!=''){
			$locationarr = array_filter($locationdataf);
			$locationstr=implode(",", $locationarr);
			}
		else
			{
			$locationstr='';
			}


		if($recruiterf!=''){
			$recarr = array_filter($recruiterf);
			$recstr=implode(",", $recarr);
			}
		else
			{
			$recstr='';
			}

	$request->session()->put('presentation_dept_f',$deptstr);
	$request->session()->put('presentation_location_f',$locationstr);
	$request->session()->put('presentation_recruiter_f',$recstr);
	$request->session()->put('presentation_from_f',$from_datef);
	$request->session()->put('presentation_to_f',$to_datef);
	
}

public function resetFunnelwidget(Request $request)
{
	$request->session()->put('presentation_dept_f','');
	$request->session()->put('presentation_location_f','');
	$request->session()->put('presentation_recruiter_f','');
	$request->session()->put('presentation_from_f','');
	$request->session()->put('presentation_to_f','');
}

public function resetJobOpeningWidget(Request $request)
{
$request->session()->put('presentation_jobopening_dept','');
$request->session()->put('presentation_jobopening_location','');
$request->session()->put('presentation_jobopening_recruiter','');
	$request->session()->put('presentation_jobopening_from','');
	$request->session()->put('presentation_jobopening_to','');
}
	
public function visaApproval()
{
  $approvalData = DocumentCollectionDetails::where("ok_visa",3)->get();
  return view("presentation/visaApproval",compact('approvalData'));
}

public function loadVisaProcess()
{
	$visaData = DocumentCollectionDetails::where("visa_process_status",2)->get();
    return view("presentation/loadVisaProcess",compact('visaData'));
}
	
public static function getvisaTypeName($id)
{
	$visatypeid = Visaprocess::where("document_id",$id)->first()->visa_type;
	return visaType::where("id",$visatypeid)->first()->title;
}
}
