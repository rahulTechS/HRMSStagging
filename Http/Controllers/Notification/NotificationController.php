<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\DepartmentForm;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Attribute\FormProduct;
use App\Models\Attribute\MasterAttribute;
use App\Models\Attribute\AttributeType;
use App\Models\Attribute\FormSection;
use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use App\Models\Common\MashreqLoginMIS;
use App\Models\Common\MashreqBankMIS;
use App\Models\Common\MashreqBookingMIS;
use App\Models\Common\MashreqMTDMIS;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\JobFunction\JobFunction;
use App\Models\Recruiter\Designation;

use Illuminate\Support\Facades\DB;

use Session;
ini_set("max_execution_time", 0);
class NotificationController extends Controller
{
   
    public function logProcess($fileType=NULL,Request $request)
    {		
		$fileType = $fileType;
		$searchValues = array();
		$dateRangeStr = 'of Last 7 days';
		$start_date = '';
		$end_date = '';

		$currentDate = date("Y-m-d");
		$date7DaysBack = date("Y-m-d",strtotime("-7 days"));

		$whereRawCandidateFinalDiscussion = " interview_info.id!=''";
		$whereRawMOLTyped = " id!=''";
		$whereRawEVisa = " id!=''";
		$whereRawOnboarded = " id!=''";

		if(@$request->session()->get('jobfunction') != '')
		{
			$jobfunction = $request->session()->get('jobfunction');
			$jobfunction_str = '';
			foreach($jobfunction as $jobfunction_value)
			{
				if($jobfunction_str == '')
				{
					$jobfunction_str = "'".$jobfunction_value."'";
				}
				else
				{
					$jobfunction_str = $jobfunction_str.","."'".$jobfunction_value."'";
				}
			}
			$whereRawCandidateFinalDiscussion .= " AND interview_info.job_opening IN (".$jobfunction_str.")";	
			$whereRawMOLTyped .= " AND job_opening IN (".$jobfunction_str.")";
			$whereRawOnboarded .= " AND job_opening_id IN (".$jobfunction_str.")";
			$searchValues['jobfunction'] = $jobfunction;
			
		}

		if(@$request->session()->get('RecruiterName') != '')
		{
			$RecruiterName = $request->session()->get('RecruiterName');
			$RecruiterName_str = '';
			foreach($RecruiterName as $RecruiterName_value)
			{
				if($RecruiterName_str == '')
				{
					$RecruiterName_str = "'".$RecruiterName_value."'";
				}
				else
				{
					$RecruiterName_str = $RecruiterName_str.","."'".$RecruiterName_value."'";
				}
			}
			$whereRawCandidateFinalDiscussion .= " AND interview_info.recruiter IN (".$RecruiterName_str.")";	
			$whereRawMOLTyped .= " AND recruiter_name IN (".$RecruiterName_str.")";	
			$whereRawOnboarded .= " AND recruiter IN (".$RecruiterName_str.")";	
			$searchValues['RecruiterName'] = $RecruiterName;
			
		}

		if($request->session()->get('start_date') != '')
		{
			$start_date = $request->session()->get('start_date');			
			$whereRawCandidateFinalDiscussion .= " AND interview_info.created_at >='".date('Y-m-d',strtotime($start_date))."'";
			$whereRawMOLTyped .= " AND updated_at >='".date('Y-m-d',strtotime($start_date))."'";
			$whereRawOnboarded .= " AND created_at >='".date('Y-m-d',strtotime($start_date))."'";

			$dateRangeStr = 'from '.$start_date;
			$searchValues['start_date'] = $start_date;
			
		}

		if($request->session()->get('end_date') != '')
		{
			$end_date = $request->session()->get('end_date');			
			$whereRawCandidateFinalDiscussion .= " AND interview_info.created_at <='".date('Y-m-d',strtotime($end_date))."'";
			$whereRawMOLTyped .= " AND updated_at <='".date('Y-m-d',strtotime($end_date))."'";
			$whereRawOnboarded .= " AND created_at <='".date('Y-m-d',strtotime($end_date))."'";

			$dateRangeStr .= ' to '.$end_date;

			$searchValues['end_date'] = $end_date;
			
		}

		if($start_date == '')
		{

			$getCandidateFinalDiscussion = InterviewProcess::select("interview_info.id","interview_details.created_at","interview_details.status","interview_details.interview_type")
					->join("interview_details","interview_details.interview_id","=","interview_info.id")
					->whereBetween("interview_details.created_at", [$date7DaysBack, $currentDate])
					->where("interview_details.interview_type","final discussion")
					->where("interview_details.status",2)->whereRaw($whereRawCandidateFinalDiscussion)->get()->count();


			$getMOLTyped = DocumentCollectionDetails::whereNotNull("mol_date")->whereBetween("created_at", [$date7DaysBack, $currentDate])->where("backout_status",1)->whereRaw($whereRawMOLTyped)->get()->count();

			$getEVisa = DocumentCollectionDetails::where("evisa_status",1)->whereBetween("updated_at", [$date7DaysBack, $currentDate])->where("backout_status",1)->where("visa_process_status","!=",4)->whereRaw($whereRawMOLTyped)->get()->count();

			$getOnboarded = Employee_details::whereBetween("doj", [$date7DaysBack, $currentDate])->whereRaw($whereRawOnboarded)->get()->count();
		}
		else
		{
			$getCandidateFinalDiscussion = InterviewProcess::select("interview_info.id","interview_details.created_at","interview_details.status","interview_details.interview_type")
					->join("interview_details","interview_details.interview_id","=","interview_info.id")					
					->where("interview_details.interview_type","final discussion")
					->where("interview_details.status",2)->whereRaw($whereRawCandidateFinalDiscussion)->get()->count();


			$getMOLTyped = DocumentCollectionDetails::whereNotNull("mol_date")->where("backout_status",1)->whereRaw($whereRawMOLTyped)->get()->count();

			$getEVisa = DocumentCollectionDetails::where("evisa_status",1)->where("backout_status",1)->where("visa_process_status","!=",4)->whereRaw($whereRawMOLTyped)->get()->count();

			$getOnboarded = Employee_details::whereRaw($whereRawOnboarded)->get()->count();
		}


		$jobfun=JobFunction::where("status",1)->get();
		$recdata=RecruiterDetails::where("status",1)->get();
		$Designation=Designation::where("status",1)->get();

        return view("Notification/logProcess",compact('fileType','jobfun','recdata','Designation','getCandidateFinalDiscussion','getMOLTyped','getEVisa','getOnboarded','searchValues','dateRangeStr'));
    }

	public function logProcessRedirect($fileType=NULL,Request $request)
    {		
		$fileType = $fileType;
		$currentDate = date("Y-m-d");
		$date7DaysBack = date("Y-m-d",strtotime("-7 days"));
		$start_date = '';
		$end_date = '';
		
		if($fileType=='Onboarded')
		{
			if(@$request->session()->get('jobfunction') != '')
			{
				$jobfunction = $request->session()->get('jobfunction');	
				$jobfunction_str = '';
				foreach($jobfunction as $jobfunction_value)
				{
					if($jobfunction_str == '')
					{
						$jobfunction_str = "'".$jobfunction_value."'";
					}
					else
					{
						$jobfunction_str = $jobfunction_str.","."'".$jobfunction_value."'";
					}
				}
				$request->session()->put('opening_cand_filter_inner_list',$jobfunction_str);
			}

			if(@$request->session()->get('RecruiterName') != '')
			{
				$RecruiterName = $request->session()->get('RecruiterName');	
				$RecruiterName_str = '';
				foreach($RecruiterName as $RecruiterName_value)
				{
					if($RecruiterName_str == '')
					{
						$RecruiterName_str = "'".$RecruiterName_value."'";
					}
					else
					{
						$RecruiterName_str = $RecruiterName_str.","."'".$RecruiterName_value."'";
					}
				}
				$request->session()->put('company_RecruiterName_filter_inner_list',$RecruiterName_str);
			}

			if(@$request->session()->get('start_date') != '')
			{
				$start_date = date("Y-m-d",strtotime($request->session()->get('start_date')));	
			}

			if(@$request->session()->get('end_date') != '')
			{
				$end_date = date("Y-m-d",strtotime($request->session()->get('end_date')));	
			}

			if($start_date=='' && $end_date=='')
			{
				$end_date = date("Y-m-d");
				$start_date = date("Y-m-d",strtotime("-7 days"));				
			}

			if($start_date!='' && $end_date=='')
			{
				$end_date = date("Y-m-d");				
			}
			//print_r($request->session());exit;
			$request->session()->put('datefrom_candonboard_filter_inner_list',$start_date);
			$request->session()->put('dateto_candonboard_filter_inner_list',$end_date);		
			$request->session()->put('tabOpenByWidget',"onboard");
		}

		return redirect("documentcollectionAjax");
		
	}

	public function logProcessSearch(Request $request)
    {			
			$start_date = @$_REQUEST['start_date'];
			$end_date = @$_REQUEST['end_date'];
			$jobfunction = @$_REQUEST['jobfunction'];
			$RecruiterName = @$_REQUEST['RecruiterName'];
			

			if($jobfunction!='')
			{
				$request->session()->put('jobfunction',$jobfunction);				
			}
			else
			{
				$request->session()->put('jobfunction','');		
			}

			if($RecruiterName!='')
			{
				$request->session()->put('RecruiterName',$RecruiterName);				
			}
			else
			{
				$request->session()->put('RecruiterName','');		
			}			
			
			if($start_date!='')
			{
				$request->session()->put('start_date',$start_date);				
			}
			else
			{
				$request->session()->put('start_date','');		
			}

			if($end_date!='')
			{
				$request->session()->put('end_date',$end_date);				
			}
			else
			{
				$request->session()->put('end_date','');		
			}
			
			return redirect("logProcess/Hiring");
				
	}

	public function logProcessSearchReset($form_id=NULL, Request $request)
    {			
		$request->session()->put('jobfunction','');		
		$request->session()->put('RecruiterName','');		
		$request->session()->put('start_date','');
		$request->session()->put('end_date','');
		
		return redirect("logProcess/Hiring");
				
	}

	public static function getCandidateFinalDiscussion()
	{
		$currentDate = date("Y-m-d");
		$date7DaysBack = date("Y-m-d",strtotime("-7 days"));
		
			return  InterviewProcess::select("interview_info.id","interview_details.created_at","interview_details.status","interview_details.interview_type")
				->join("interview_details","interview_details.interview_id","=","interview_info.id")
				->whereBetween("interview_details.created_at", [$date7DaysBack, $currentDate])
				->where("interview_details.interview_type","final discussion")
				->where("interview_details.status",2)->get()->count();
		
		
	}

	public static function getMOLTyped()
	{
		$currentDate = date("Y-m-d");
		$date7DaysBack = date("Y-m-d",strtotime("-7 days"));
			return DocumentCollectionDetails::where("mol_date","!=",NULL)->whereBetween("created_at", [$date7DaysBack, $currentDate])->where("onboard_status",1)->where("backout_status",1)->where("visa_process_status","!=",4)->get()->count();
		
	}

	public static function getEVisa()
	{		
		$currentDate = date("Y-m-d");
		$date7DaysBack = date("Y-m-d",strtotime("-7 days"));
			return DocumentCollectionDetails::where("evisa_status",1)->whereBetween("created_at", [$date7DaysBack, $currentDate])->where("onboard_status",1)->where("backout_status",1)->where("visa_process_status","!=",4)->get()->count();
		
	}

	public static function getOnboarded()
	{
		$currentDate = date("Y-m-d");
		$date7DaysBack = date("Y-m-d",strtotime("-7 days"));
		
			return Employee_details::whereBetween("doj", [$date7DaysBack, $currentDate])->get()->count();
		
	}

	

    
}
