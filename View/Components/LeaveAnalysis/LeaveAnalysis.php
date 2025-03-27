<?php

namespace App\View\Components\LeaveAnalysis;
require_once "/srv/www/htdocs/core/autoload.php";
use Illuminate\View\Component;
use App\Models\Entry\Employee;
use Request;
use App\User;
use App\Models\Dashboard\WidgetCreation;

use App\Models\Dashboard\Widgetlayouts\WidgetBarMol;
use App\Models\Job\JobOpening;
use App\Models\Company\Department;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\Employee\Employee_details;
use Session;
use App\Models\Common\MashreqLoginMIS;
use App\Models\Common\MashreqBankMIS;
use App\Models\Common\MashreqBookingMIS;
use App\Models\Common\MashreqMTDMIS;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Bank\CBD\CBDBankMis;
use App\Models\WarningLetter\WarningLetterRequest;
use App\Models\WarningLetter\WarningLetterReasons;
use App\Models\Recruiter\Designation;
use App\Models\Dashboard\MasterPayout;
use App\Models\Visa\Visaprocess;
use App\Models\Visa\VisaStage;
use App\Models\CrossSell\CrossSellScenariosAllocation;
use App\Models\Employee_Leaves\RequestedLeaves;

class LeaveAnalysis extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */


	public $widgetName;
	public $widgetId;
	public $widgetgraphData;
	public $recruiters;
	public $recruiterCategory;
	public $recruitersSelected;
	public $recruiterCategorySelected;
	public $filterTypeMOL;
	public $from_salesTime_MOL;
	public $to_salesTime_MOL;
	public $jobOpeningselectedList;
	public $jobOpeningLists;
	public $TeamLists;
	public $TeamListsSelected;
	public $processorSelecteddata;
	public $departmentLists;

	public $teamLeaderLists;

	public $deptSelected;

	public $tlSelected;

	
	
    public function __construct($widgetId)
    {
		
        $widget_name = WidgetCreation::where("id",$widgetId)->first()->widget_name;

		
	   //$widgetData = WidgetBarMol::where("widget_id",$widgetId)->first();
	  
	   $whereraw = '';
		/*$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-90 days"));
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
		}*/
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][department]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][department]') != NULL)
		{
			$deptIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][department]');
			$ids=implode(',',$deptIds);
			 //print_r($deptIds);
			//exit;
			
				if($whereraw == '')
				{
				$whereraw = "dept_id IN('".$ids."')";
				}
				else
				{
					$whereraw .= " AND dept_id IN('".$ids."')";
				}			
			}
			else
			{
				
				if($whereraw == '')
				{
				$whereraw = 'dept_id IN(36)';
				}
				else
				{
					$whereraw .= ' AND dept_id IN(36)';
				}			
			}




		
		
		//echo $whereraw;exit;
		if($whereraw != '')
		{
			// echo "h1";
			// print_r($whereraw);
			// exit;
		 	$totaldata= Employee_details::whereRaw($whereraw)->where('job_function',3)->where('offline_status',1)->get();
			
			//print_r($totaldata);exit;
		}
			
		else
		{
			

			$totaldata= Employee_details::where('job_function',3)->where('dept_id',36)->where('offline_status',1)->get();

		}


		
		
		$graphdata= $totaldata;
		
		//print_r($graphdata);exit;
	   
	   
		$this->jobOpeningselectedList = 0;
		
		// if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]') != NULL)	
		// {
		// 	$this->recruiterCategorySelected = Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]');
		// }
		// else
		// {
		// 	$this->recruiterCategorySelected = '';
		// }
		
		
		
		
		
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterWarnLetter]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterWarnLetter]') != NULL)	
		{
			$this->recruitersSelected = Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterWarnLetter]');
		}
		else
		{
			$this->recruitersSelected = array();
		}


		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][department]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][department]') != NULL)	
		{
			$this->deptSelected = Request::session()->get('widgetFilterHiring['.$widgetId.'][department]');
		}
		else
		{
			$this->deptSelected = array();
		}

		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][teamLeaders]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][teamLeaders]') != NULL)	
		{
			$this->tlSelected = Request::session()->get('widgetFilterHiring['.$widgetId.'][teamLeaders]');
		}
		else
		{
			$this->tlSelected = array();
		}
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]') != NULL)	
		{
			$this->filterTypeMOL = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		}
		else
		{
			$this->filterTypeMOL = '';
		}
		
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]') != NULL)	
		{
			$this->from_salesTime_MOL = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
		}
		else
		{
			$this->from_salesTime_MOL = '';
		}
		
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]') != NULL)	
		{
			$this->to_salesTime_MOL = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
		}
		else
		{
			$this->to_salesTime_MOL = '';
		}
		if(Request::session()->get('widgetFiltermolTeam['.$widgetId.']') != '' && Request::session()->get('widgetFiltermolTeam['.$widgetId.']') != NULL)	
		{
			$this->TeamListsSelected = explode(",",Request::session()->get('widgetFiltermolTeam['.$widgetId.']'));
		}
		else
		{
			$this->TeamListsSelected ='';
		}
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)	
		{
			$this->processorSelecteddata = Request::session()->get('widgetFilterprocessor['.$widgetId.']');
		}
		else
		{
			$this->processorSelecteddata ='';
		}
		$TeamLists = DepartmentFormEntry::groupBy('team')->selectRaw('count(*) as total, team')->get();



	   

		$design=Designation::where("tlsm",2)->where("status",1)->get();
		$designarray=array();
		foreach($design as $_design){
			$designarray[]=$_design->id;
		}
		$finalarray=implode(",",$designarray);			
		$teamLeaderLists = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->get();





	   $recruiters = RecruiterDetails::where("status",1)->get();
	   $departmentLists = Department::where("status",1)->orderBy("id","DESC")->get();
	   $recruiterCategory = RecruiterCategory::where("status",1)->get();
	   $this->widgetName = $widget_name;
	   $this->widgetgraphData = $graphdata;
	   $this->widgetId = $widgetId;
	   $this->recruiters = $recruiters;
	   $this->departmentLists = $departmentLists;
	   $this->teamLeaderLists = $teamLeaderLists;
	   $this->recruiterCategory = $recruiterCategory;
	   $this->jobOpeningLists = JobOpening::where("status",1)->get();
	   $this->recruiterCategory = $recruiterCategory;
		$this->TeamLists = $TeamLists;
		
		//$this->processorSelecteddata = $processorSelected;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.LeaveAnalysis.leaveanalysis');
    }
	Private function getJobOpeningName($jobId)
	{
		$data  =  JobOpening::where("id",$jobId)->first();
		if($data != '')
		{
			$departmentName = Department::where("id",$data->department)->first()->department_name;
			return $data->name.'-'.$departmentName.'-'.$data->location;
		}
		else
		{
			return "No Name";
		}
		
	}
	
	
public static function getTeamListsSelectedName($dept){
		//$departmentName = Department::where("id",$data->department)->first()->department_name;
		$name = '';
		foreach($dept as $r)
		{
			if($name == '')
			{
				$name = DepartmentFormEntry::where("team",$r)->first()->team;
			}
			else
			{
				$name = $name.','.DepartmentFormEntry::where("team",$r)->first()->team;
			}
		}
		return $name;
	}	
	




	public static function getDeptname($dept){
		
		$name = Department::where("id",$dept)->first();
		if($name!=""){
			
			return $name->department_name;
		}
		else{
			return '';
		}
		
		
			
		
	}




    public static function getTotalWarningCount($dept,$widgetId)
	{
		$whereraw = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-90 days"));
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "attendance_date >= '".$fromDate."' and attendance_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And attendance_date >= '".$fromDate."' and attendance_date <= '".$toDate."'";
			}
		}






		if($whereraw != '')
		{
			//echo "h1";

			// echo "Hello";
			// print_r($whereraw);
			// exit;


			$emp = Employee_details::where('dept_id',$dept)->orderBy('id','desc')->get();
			$empid=array();
			foreach($emp as $empvalue)
			{
				$empid[]=$empvalue->emp_id;
			}

			$attendanceData = EmpAttendance::select('attribute_value')->whereRaw($whereraw)->whereIn('emp_id',$empid)->where('attribute_code','attendance')->where('attribute_value','P')->orderBy('id','desc')->get()->count();

			return $attendanceData;


		}
			
		else
		{
			
			
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$whererawdefault = "warning_letter_issued_on >= '".$fromDate."' and warning_letter_issued_on <= '".$toDate."'";
			
			
			$emp = Employee_details::where('dept_id',$dept)->orderBy('id','desc')->get();
			$empid=array();
			foreach($emp as $empvalue)
			{
				$empid[]=$empvalue->emp_id;
			}

			$attendanceData = WarningLetterRequest::whereRaw($whererawdefault)->whereIn('emp_id',$empid)->where('warning_letter_status', 1)
			->where('final_status', 1)->orderBy('id','desc')->get()->count();

			return $attendanceData;

		}

		

    }

	



	public static function getTotalWarningLetterCount($empid,$widgetId)
	{
		$totalCounter= WarningLetterRequest::where('emp_id', $empid)->orderBy("id","DESC")->first();

		if($totalCounter)
		{
			
			$ends = array('th','st','nd','rd','th','th','th','th','th','th');
			if ((($totalCounter->counter % 100) >= 11) && (($totalCounter->counter%100) <= 13))
				return $totalCounter->counter. 'th';
			else
			return $totalCounter->counter. $ends[$totalCounter->counter % 10];
			
			//return $totalCounter->counter;
		}
		else
		{
			return "--";
		}
	}


	public static function getWarningLetterIssuedOn($empid,$widgetId)
	{
		$warningData= WarningLetterRequest::where('emp_id', $empid)->orderBy("id","DESC")->first();

		if($warningData)
		{
			
			
			return $newDate = date("d M, Y", strtotime($warningData->warning_letter_issued_on));
			
			
			
		}
		else
		{
			return "--";
		}
	}



	public static function getCurrentMonthPerformance($empid,$widgetId)
	{
		
		
		
		$getPerformanceData= MasterPayout::where('employee_id', $empid)->orderBy("id","DESC")->first();

		//return $getPerformanceData;

		if($getPerformanceData)
		{
			if($getPerformanceData->dept_id==36) // Mashreq
			{
				$card = $getPerformanceData->cards_mashreq;
			}
			elseif($getPerformanceData->dept_id==8) // Mashreq
			{
				$card = $getPerformanceData->no_cards_deem;
			}
			elseif($getPerformanceData->dept_id==46) // Mashreq
			{
				$card = $getPerformanceData->no_card_dib;
			}
			else
			{
				$card = $getPerformanceData->tc;
			}

			if($getPerformanceData->agent_target)
			{
				$percentPerform = ($card / $getPerformanceData->agent_target) * 100;
				return $percentPerform = round($percentPerform, 2);
			}
			else
			{
				return $percentPerform = 0;
			}
		}
		else
		{
			return 0;
		}

		
		
	}


	public static function getThreeMonthPerformanceold($empid,$widgetId)
	{
		
		
		
		
		$getPerformanceData= MasterPayout::where('employee_id', $empid)->orderBy('id', 'desc')->take(3)->get();

		//return $getPerformanceData;

		if($getPerformanceData->dept_id==36) // Mashreq
		{
			$card = $getPerformanceData->sum('cards_mashreq');
		}
		elseif($getPerformanceData->dept_id==8) // Mashreq
		{
			$card = $getPerformanceData->sum('no_cards_deem');
		}
		elseif($getPerformanceData->dept_id==46) // Mashreq
		{
			$card = $getPerformanceData->sum('no_card_dib');
		}
		else
		{
			$card = $getPerformanceData->sum('tc');
		}
		$target = $getPerformanceData->sum('agent_target');
		
		$percentPerform = ($card / $target) * 100;
		return $percentPerform = round($percentPerform, 2);
		
		
	}




	public static function getThreeMonthPerformance($empid,$widgetId)
	{
		
		
		//echo $empid."<br>";
		
		$getPerformanceData= MasterPayout::where('employee_id', $empid)->orderBy('id', 'desc')->limit(3)->get();
		
		if($getPerformanceData!='')
		{
			//echo $empid;exit;
			//print_r($getPerformanceData);exit;
			$cardtotal=0;
			$targettotal=0;
			foreach($getPerformanceData as $_getPerformanceData)
			{
				if($_getPerformanceData->dept_id==36) // Mashreq
				{
					$cardtotal = $cardtotal+$_getPerformanceData->cards_mashreq;				
				}
				elseif($_getPerformanceData->dept_id==8) // Mashreq
				{
					$cardtotal = $cardtotal+$_getPerformanceData->no_cards_deem;
				}
				elseif($_getPerformanceData->dept_id==46) // Mashreq
				{
					$cardtotal = $cardtotal+$_getPerformanceData->no_card_dib;
				}
				else
				{
					$cardtotal = $cardtotal+$_getPerformanceData->tc;
				}
				$targettotal = $targettotal+$_getPerformanceData->agent_target;
					
			}
			//echo $cardtotal;
			//echo $targettotal;exit;
			if($targettotal>0)
			{
				$targettotalf=	$targettotal;
			}
			else
			{
				$targettotalf=	1;	
			}
			$percentPerform = ($cardtotal / $targettotalf) * 100;
			return $percentPerform = round($percentPerform, 2);	
		}
		else{
			return 0;
		}	
	}




	


	
	public static function getVisaStageName($id)
	{	
		$data = Visaprocess::where("document_id",$id)->orderBy('id','DESC')->first();
		
		//print_r($data);
		if($data != '')
		{
			$visa_stage = VisaStage::where("id",$data->visa_stage)->first();
			if($visa_stage!=''){
				return "Current Visa Stage: ".$visa_stage->stage_name;
			}else{
				return '';
			}
		}
		else
		{
			return '';
		}
		
	}





	public static function getVisaStatus($empid)
	{	
		$empDetails = Employee_details::where("emp_id",$empid)->orderBy('id','desc')->first();

		if($empDetails)
		{
			if($empDetails->document_collection_id != NULL)
			{
				$visaDetails = DocumentCollectionDetails::where("id",$empDetails->document_collection_id)->orderBy('id','desc')->first();

				if($visaDetails)
				{
					if($visaDetails->visa_process_status==4)
					{
						return "Visa Complete";
					}
					elseif($visaDetails->visa_process_status==2)
					{
						return "Visa In-Progress -  ".$visaDetails->visa_stage_steps;
					}
					else
					{
						return "Visa in-Complete";
					}

				}
				else
				{
					return "N/A";
				}

			}
			else
			{
				//return "N/A";
				return "Visa Complete";
			}

		}
		else
		{
			return "N/A";
		}
	}




	

	public static function getTeamLeader($id = NULL)
	{
			 $emp_details = Employee_details::where("id",$id)->first(); 
			 if($emp_details!='')
			 {
				return $emp_details->emp_name;
			}
			else
			{
				return "--";
			}
		
	}


public static function getEmpName($emp_id)
	{
			 $emp_details = Employee_details::where("emp_id",$emp_id)->first(); 
			 if($emp_details!='')
			 {
				return $emp_details->emp_name;
			}
			else
			{
				return "--";
			}
		
	}



	public static function getEmailStatus($empid)
	{
		$warningData= WarningLetterRequest::where('emp_id', $empid)->orderBy("id","DESC")->first();

		if($warningData!='')
		{
			if($warningData->email_sent==0)
			{
				return 1;
			}
			if($warningData->email_sent==1)
			{
				return 2;
			}
		}
		else
		{
			return 3;
		}
		
	}


public static function getHeadCount($id,$widgetId){
	if($id!=""){
		return Employee_details::where('tl_id',$id)->where('offline_status',1)->get()->count();
}else{
	return "";
}
	
}
public static function getRequestLeave($id,$widgetId){
	
	$whereraw = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-90 days"));
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "request_at >= '".$fromDate."' and request_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And request_at >= '".$fromDate."' and request_at <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "request_at >= '".$fromDate."' and request_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And request_at >= '".$fromDate."' and request_at <= '".$toDate."'";
			}
		}
	
	$totalempdata= Employee_details::where('tl_id',$id)->where('offline_status',1)->get();
	$finalemp1=array();
			foreach($totalempdata as $emp)
			{
				$finalemp1[]=$emp->emp_id;
			}
			
			if($whereraw != '')
		{
			return RequestedLeaves::whereRaw($whereraw)->whereIn("emp_id",$finalemp1)->get()->count();
                  
		}else{
			return RequestedLeaves::whereRaw($whereraw)->whereIn("emp_id",$finalemp1)->get()->count();
		}
}
public static function getApprovedLeave($id,$widgetId){
	
	$whereraw = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-90 days"));
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "approved_reject_at >= '".$fromDate."' and approved_reject_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And approved_reject_at >= '".$fromDate."' and approved_reject_at <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "approved_reject_at >= '".$fromDate."' and approved_reject_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And approved_reject_at >= '".$fromDate."' and approved_reject_at <= '".$toDate."'";
			}
		}
	
	$totalempdata= Employee_details::where('tl_id',$id)->where('offline_status',1)->get();
	$finalemp1=array();
			foreach($totalempdata as $emp)
			{
				$finalemp1[]=$emp->emp_id;
			}
			
			if($whereraw != '')
		{
			return RequestedLeaves::whereRaw($whereraw)->whereIn("emp_id",$finalemp1)->where("approved_reject_status",1)->where("final_status",1)->get()->count();
                  
		}else{
			return RequestedLeaves::whereRaw($whereraw)->whereIn("emp_id",$finalemp1)->where("approved_reject_status",1)->where("final_status",1)->get()->count();
		}
	
}
public static function getCurrentlyLeave($id,$widgetId){
	$totalempdata= Employee_details::where('tl_id',$id)->where('offline_status',1)->get();
	$finalemp1=array();
			foreach($totalempdata as $emp)
			{
				$finalemp1[]=$emp->emp_id;
			}
				$tDate = date('Y-m-d');
                    $requestedLeaves = RequestedLeaves::whereIn("emp_id",$finalemp1)->where('final_status',1)->get();
                    $newResult=array();
                    foreach($requestedLeaves as $value)
                    {
                        if($value->updated_from_date==NULL && $value->updated_to_date==NULL)
                        {
                            if($value->from_date <= $tDate && $value->to_date >= $tDate)
                            {
                                $newResult[]=$value->id;
                            }                       
                        }
                        else
                        {
                            if($value->updated_from_date <= $tDate && $value->updated_to_date >= $tDate)
                            {
                                $newResult[]=$value->id;
                            }
                        }
                    }	
					return RequestedLeaves::whereIn("id",$newResult)->get()->count();
}
}