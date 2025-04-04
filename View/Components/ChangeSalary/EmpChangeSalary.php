<?php

namespace App\View\Components\ChangeSalary;
require_once "/srv/www/htdocs/core/autoload.php";
use Illuminate\View\Component;
use App\Models\Entry\Employee;
use Request;

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
use App\Models\Employee\ChangeSalary;
use App\Models\WarningLetter\WarningLetterReasons;
use App\Models\Recruiter\Designation;

class EmpChangeSalary extends Component
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
				$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
		}
		



		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterWarnLetter]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterWarnLetter]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterWarnLetter]');
			$totaldata= Employee_details::whereIn('recruiter',$recruiterIds)->get();
			$empArr = array();
			foreach($totaldata as $recid)
			{
				$empArr[]=$recid->emp_id;
			}
			$recruiterIdsFinal=implode(",", $empArr);
			if($whereraw == '')
			{
			$whereraw = 'emp_id IN('.$recruiterIdsFinal.')';
			}
			else
			{
				$whereraw .= ' AND emp_id IN('.$recruiterIdsFinal.')';
			}					
		}



		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][department]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][department]') != NULL)
		{
			$deptIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][department]');
			// print_r($deptIds);
			// exit;
			$totaldata= Employee_details::whereIn('dept_id',$deptIds)->get();
			$empArr = array();
			foreach($totaldata as $departid)
			{
				$empArr[]=$departid->emp_id;
			}
			$departIdsFinal=implode(",", $empArr);
			if($whereraw == '')
			{
			$whereraw = 'emp_id IN('.$departIdsFinal.')';
			}
			else
			{
				$whereraw .= ' AND emp_id IN('.$departIdsFinal.')';
			}					
		}

		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][teamLeaders]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][teamLeaders]') != NULL)
		{
			$tlIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][teamLeaders]');
			// print_r($deptIds);
			// exit;
			$totaldata= Employee_details::whereIn('tl_id',$tlIds)->get();
			$empArr = array();
			foreach($totaldata as $team)
			{
				$empArr[]=$team->emp_id;
			}
			$teamIdsFinal=implode(",", $empArr);
			if($whereraw == '')
			{
			$whereraw = 'emp_id IN('.$teamIdsFinal.')';
			}
			else
			{
				$whereraw .= ' AND emp_id IN('.$teamIdsFinal.')';
			}					
		}









		
		
		//echo $whereraw;exit;
		if($whereraw != '')
		{
			// // echo "h1";
			// print_r($whereraw);
			// exit;
			$totaldata= ChangeSalary::whereRaw($whereraw)
			->orderBy("id","DESC")->get();
			
		}
			
		else
		{
			//echo "h2"; 
			//$totaldata= DocumentCollectionDetails::select("department")->groupBy('department')->get();	

			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$whererawdefault = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";

			// echo $whererawdefault;
			// exit;

			$totaldata= ChangeSalary::whereRaw($whererawdefault)->groupBy('emp_id')->orderBy("id","DESC")->get();	

		}

		// echo "<pre>";
		// print_r($totaldata);
		//exit;
		$finalemp=array();
		foreach($totaldata as $emp)
		{
			$finalemp[]=$emp->emp_id;
		}

		// echo "<pre>";
		// print_r($finalemp);
		// exit;


		$empDetails= Employee_details::whereIn("emp_id",$finalemp)->get();
		
		$graphdata= $empDetails;
		
	   
	   
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
        return view('components.ChangeSalary.empchangesalary');
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
			return $totalCounter->counter;
		}
		else
		{
			return "--";
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

	public static function getNewSalary($empid = NULL)
	{
			 $emp_details = ChangeSalary::where("emp_id",$empid)->orderBy("id","DESC")->first(); 
			 if($emp_details!='')
			 {
				return $emp_details->newsalary;
			}
			else
			{
				return "--";
			}
		
	}
	public static function getOldSalary($empid = NULL)
	{
			 $emp_details = ChangeSalary::where("emp_id",$empid)->orderBy("id","DESC")->first(); 
			 if($emp_details!='')
			 {
				return $emp_details->oldsalary;
			}
			else
			{
				return "--";
			}
		
	}

	
}