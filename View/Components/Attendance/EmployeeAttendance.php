<?php

namespace App\View\Components\Attendance;
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
use App\Models\Employee_Attendance\EmpAttendance;


class EmployeeAttendance extends Component
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

	public $filterfromdate;

	public $finalfromdate;

	public $deptSelected;


	
	
    public function __construct($widgetId)
    {
		
        $widget_name = WidgetCreation::where("id",$widgetId)->first()->widget_name;

		
	   //$widgetData = WidgetBarMol::where("widget_id",$widgetId)->first();
	  
	   $whereraw = '';
	   $finalfromdate = '';
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
				$finalfromdate = $fromDate;
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

		
		
		
		
		//echo $whereraw;exit;
		if($whereraw != '')
		{
			// echo "h1";
			// print_r($whereraw);
			// exit;
			//$totaldata= DocumentCollectionDetails::select("department")->groupBy('department')->get();
			$totaldata= EmpAttendance::whereRaw($whereraw)->groupBy('emp_id')->get();
			
		}
			
		else
		{
			//echo "h2";
			//$totaldata= DocumentCollectionDetails::select("department")->groupBy('department')->get();
			$totaldata= EmpAttendance::groupBy('emp_id')->get();	
			
			

		}

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
		
		
		// if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)	
		// {
		// 	$this->recruitersSelected = explode(",",Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]'));
		// }
		// else
		// {
		// 	$this->recruitersSelected = array();
		// }
		
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
		

		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][department]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][department]') != NULL)	
		{
			$this->deptSelected = Request::session()->get('widgetFilterHiring['.$widgetId.'][department]');
		}
		else
		{
			$this->deptSelected = array();
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
	   $recruiters = RecruiterDetails::where("status",1)->get();
	   $recruiterCategory = RecruiterCategory::where("status",1)->get();
	   $departmentLists = Department::where("status",1)->orderBy("id","DESC")->get();
	   $this->widgetName = $widget_name;
	   $this->widgetgraphData = $graphdata;
	   $this->widgetId = $widgetId;
	   $this->recruiters = $recruiters;
	   $this->departmentLists = $departmentLists;
	   $this->recruiterCategory = $recruiterCategory;
	   $this->jobOpeningLists = JobOpening::where("status",1)->get();
	   $this->recruiterCategory = $recruiterCategory;
		$this->TeamLists = $TeamLists;

		$this->filterfromdate = $finalfromdate;
		
		//$this->processorSelecteddata = $processorSelected;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Attendance.empattendance');
		//return view('dashboard.newattendance');
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
	public static function getTotalDataDXB($dept,$widgetId)
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
				$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
		}
		else{
			/*$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			*/
		}
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			if($whereraw == '')
			{
			$whereraw = 'recruiter_name IN('.$recruiterIds.')';
			}
			else
			{
				$whereraw .= ' AND recruiter_name IN('.$recruiterIds.')';
			}
					
		}
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]');
			
			$rCat = Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]');
					$recruiterCatMod = RecruiterDetails::where("recruit_cat",$rCat)->where("status",1)->get();
					$recruiterIdsArray1 = array();
					foreach($recruiterCatMod as $rMod)
					{
						$recruiterIdsArray1[] =  $rMod->id;
					}
					//print_r($recruiterIdsArray1);exit;
					 $recruiterIdsArray = implode(",",$recruiterIdsArray1);//exit;
			if($whereraw == '')
			{
			$whereraw = 'recruiter_name IN('.$recruiterIdsArray.')';
			}
			else
			{
				$whereraw .= ' AND recruiter_name IN('.$recruiterIdsArray.')';
			}
					
		}
		
		
		//echo $whereraw;exit;
		if($whereraw != '')
		{
			
		return DocumentCollectionDetails::select("id")->whereRaw($whereraw)->where("department",$dept)->where("location","DXB")->where("offer_letter_relased_status",1)->where("offer_letter_onboarding_status",1)->where("backout_status",1)->where("onboard_status",1)->get()->count();
		}
			
		else
		{
			//echo "h2";
			return DocumentCollectionDetails::select("id")->where("department",$dept)->where("location","DXB")->where("offer_letter_relased_status",1)->where("offer_letter_onboarding_status",1)->where("backout_status",1)->where("onboard_status",1)->get()->count();
		//return DepartmentFormEntry::where("form_id",2)->whereRaw($whereraw)->get()->count();	

		}
	}
	public static function getTotalDataAUH($dept,$widgetId)
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
				$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
		}
		else{
			/*$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			*/
		}
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
			
			$recruiterIdsArray = explode(",",$recruiterIds);
			if($whereraw == '')
			{
			$whereraw = 'recruiter_name IN('.$recruiterIds.')';
			}
			else
			{
				$whereraw .= ' AND recruiter_name IN('.$recruiterIds.')';
			}
					
		}
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]') != NULL)
		{
			$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]');
			
			$rCat = Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]');
					$recruiterCatMod = RecruiterDetails::where("recruit_cat",$rCat)->where("status",1)->get();
					$recruiterIdsArray1 = array();
					foreach($recruiterCatMod as $rMod)
					{
						$recruiterIdsArray1[] =  $rMod->id;
					}
					//print_r($recruiterIdsArray1);exit;
					 $recruiterIdsArray = implode(",",$recruiterIdsArray1);//exit;
			if($whereraw == '')
			{
			$whereraw = 'recruiter_name IN('.$recruiterIdsArray.')';
			}
			else
			{
				$whereraw .= ' AND recruiter_name IN('.$recruiterIdsArray.')';
			}
					
		}
		
		
		//echo $whereraw;exit;
		if($whereraw != '')
		{
			//echo "h1";
		return DocumentCollectionDetails::select("id")->whereRaw($whereraw)->where("department",$dept)->where("location","AUH")->where("offer_letter_relased_status",1)->where("offer_letter_onboarding_status",1)->where("backout_status",1)->where("onboard_status",1)->get()->count();
		}
			
		else
		{
			//echo "h2";
			return DocumentCollectionDetails::select("id")->where("department",$dept)->where("location","AUH")->where("offer_letter_relased_status",1)->where("offer_letter_onboarding_status",1)->where("backout_status",1)->where("onboard_status",1)->get()->count();
		//return DepartmentFormEntry::where("form_id",2)->whereRaw($whereraw)->get()->count();	

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




    public static function getTotalPresentAttendance($dept,$widgetId)
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
			$whererawdefault = "attendance_date >= '".$fromDate."' and attendance_date <= '".$toDate."'";
			
			
			$emp = Employee_details::where('dept_id',$dept)->orderBy('id','desc')->get();
			$empid=array();
			foreach($emp as $empvalue)
			{
				$empid[]=$empvalue->emp_id;
			}

			$attendanceData = EmpAttendance::select('attribute_value')->whereRaw($whererawdefault)->whereIn('emp_id',$empid)->where('attribute_code','attendance')->where('attribute_value','P')->orderBy('id','desc')->get()->count();

			return $attendanceData;

		}

		

    }

	public static function getTotalAbsentAttendance($dept,$widgetId)
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

			$attendanceData = EmpAttendance::select('attribute_value')->whereRaw($whereraw)->whereIn('emp_id',$empid)->where('attribute_code','attendance')->where('attribute_value','A')->orderBy('id','desc')->get()->count();

			return $attendanceData;


		}
			
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$whererawdefault = "attendance_date >= '".$fromDate."' and attendance_date <= '".$toDate."'";
			
			$emp = Employee_details::where('dept_id',$dept)->orderBy('id','desc')->get();
			$empid=array();
			foreach($emp as $empvalue)
			{
				$empid[]=$empvalue->emp_id;
			}

			$attendanceData = EmpAttendance::select('attribute_value')->whereRaw($whererawdefault)->whereIn('emp_id',$empid)->where('attribute_code','attendance')->where('attribute_value','A')->orderBy('id','desc')->get()->count();

			return $attendanceData;

		}
		
		
		
		
		
		
		
		
		//return $dept;

		// $search = '2024-03';
		// $emp = Employee_details::where('dept_id',$dept)->orderBy('id','desc')->get();
		// $empid=array();
		// foreach($emp as $empvalue)
		// {
		// 	$empid[]=$empvalue->emp_id;
		// }

		// $attendanceData = EmpAttendance::select('attribute_value')->whereIn('emp_id',$empid)->where('attribute_code','attendance')->where('attribute_value','A')->where('attendance_date','LIKE',"%{$search}%")->orderBy('id','desc')->get()->count();

		// return $attendanceData;

    }



	public static function getPresentAttendanceTotal($empid,$widgetId,$fromfilterdate,$month,$tofilterdate)
	{	
		if($month == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
		}
		elseif($month == 'last_month')
		{
			$fromDate= date('Y-m-d', strtotime('first day of last month'));
			$toDate= date('Y-m-d', strtotime('last day of last month'));
		}
		elseif($month == 'month_3')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y-m-d",strtotime("-90 days"));
		}
		elseif($month == 'custom')
		{
			$to = strtotime($tofilterdate);
			$toDate = date("Y-m-d", $to);

			$from = strtotime($fromfilterdate);
			$fromDate = date("Y-m-d", $from);
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
		}
		//return $fromDate.$toDate;
		$whererawdefault = "attendance_date >= '".$fromDate."' and attendance_date <= '".$toDate."'";
		
		$attendanceData = EmpAttendance::select('attribute_value')->whereRaw($whererawdefault)->where('emp_id',$empid)->where('attribute_code','attendance')->where('attribute_value','P')->orderBy('id','desc')->get()->count();

		return $attendanceData;
	}

	public static function getAbsentAttendanceTotal($empid,$widgetId,$fromfilterdate,$month,$tofilterdate)
	{	
		if($month == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
		}
		elseif($month == 'last_month')
		{
			$fromDate= date('Y-m-d', strtotime('first day of last month'));
			$toDate= date('Y-m-d', strtotime('last day of last month'));
		}
		elseif($month == 'month_3')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y-m-d",strtotime("-90 days"));
		}
		elseif($month == 'custom')
		{
			$to = strtotime($tofilterdate);
			$toDate = date("Y-m-d", $to);

			$from = strtotime($fromfilterdate);
			$fromDate = date("Y-m-d", $from);
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
		}
		
		$whererawdefault = "attendance_date >= '".$fromDate."' and attendance_date <= '".$toDate."'";
		
		$attendanceData = EmpAttendance::select('attribute_value')->whereRaw($whererawdefault)->where('emp_id',$empid)->where('attribute_code','attendance')->where('attribute_value','A')->orderBy('id','desc')->get()->count();

		return $attendanceData;
	}

	public static function getTodayAttendance($empid,$widgetId,$fromfilterdate,$month,$tofilterdate)
	{	
		if($month == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
		}
		elseif($month == 'last_month')
		{
			$fromDate= date('Y-m-d', strtotime('first day of last month'));
			$toDate= date('Y-m-d', strtotime('last day of last month'));
		}
		elseif($month == 'month_3')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y-m-d",strtotime("-90 days"));
		}
		elseif($month == 'custom')
		{
			$to = strtotime($tofilterdate);
			$toDate = date("Y-m-d", $to);

			$from = strtotime($fromfilterdate);
			$fromDate = date("Y-m-d", $from);
		}
		else
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
		}
		
		$whererawdefault = "attendance_date >= '".$fromDate."' and attendance_date <= '".$toDate."'";

		$todayDate = date("Y-m-d");

		$whererToday = "attendance_date = '".$todayDate."'";
		
		$attendanceData = EmpAttendance::select('attribute_value')->whereRaw($whererToday)->where('emp_id',$empid)->where('attribute_code','attendance')->orderBy('id','desc')->first();

		if($attendanceData)
		{
			return $attendanceData->attribute_value;
		}
		else
		{
			return "NA";
		}

		
	}

	
}