<?php

namespace App\View\Components\MyTeam;
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
use App\Models\EmpOffline\EmpOffline;
use App\Models\Dashboard\MasterPayout;


class OffboardFnfHome extends Component
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
		$empsessionId=Request::session()->get('EmployeeId');
		$user=Employee::where("id",$empsessionId)->first();
		if($user!=''){
			$empdata=Employee_details::where("emp_id",$user->employee_id)->where("job_function",3)->first();
					   //print_r($empdata);exit;
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'department IN('.$empdata->dept_id.') And tl_se='.$empdata->id;
							}
							else
							{
								$whereraw .= ' AND department IN('.$empdata->dept_id.') And tl_se='.$empdata->id;
							}
						   //$dept=$empdata->dept_id;
					   }
			
		}
		
		if(Request::session()->get('widgetFilterBYHomeRetain['.$widgetId.']') != '' && Request::session()->get('widgetFilterBYHomeRetain['.$widgetId.']') =="Retain")
		{
			if($whereraw != '')
			{
				//echo "h1";
			$totaldata= EmpOffline::whereRaw($whereraw)->where("retain",1)->orderBy("condition_leaving_date", "DESC")->get();
			}
				
			else
			{
				//echo "h2";
			$totaldata= EmpOffline::where("retain",1)->orderBy("condition_leaving_date", "DESC")->get();	

			}
		}
		else if(Request::session()->get('widgetFilterBYHomeFNF['.$widgetId.']') != '' && Request::session()->get('widgetFilterBYHomeFNF['.$widgetId.']') =="FNF")
		{
			if($whereraw != '')
			{
				//echo "h1";
			$totaldata= EmpOffline::whereRaw($whereraw)->whereIn("condition_leaving",array(3,4,5,6))->orderBy("created_at","DESC")->get();
			}
				
			else
			{
				//echo "h2";
			$totaldata= EmpOffline::whereIn("condition_leaving",array(3,4,5,6))->orderBy("created_at","DESC")->get();	

			}	
		}
		else if(Request::session()->get('widgetFilterBYHomeExitInterview['.$widgetId.']') != '' && Request::session()->get('widgetFilterBYHomeExitInterview['.$widgetId.']') =="Exit Interview"){
			if($whereraw != '')
			{
				//echo "h1";
				$whereraw .= ' And condition_leaving = 2 AND last_working_day_resign IS NULL';
			$totaldata= EmpOffline::whereRaw($whereraw)->orderBy("created_at","DESC")->get();
			}
				
			else
			{
				//echo "h2";
			$whereraw1 = 'condition_leaving = 2 AND last_working_day_resign IS NULL';
			$totaldata= EmpOffline::whereRaw($whereraw1)->orderBy("created_at","DESC")->get();	

			}
		}
		else{
			if($whereraw != '')
			{
				//echo "h1";
				//$whereraw .= ' And condition_leaving = 2 AND last_working_day_resign IS NULL';
			$totaldata= EmpOffline::whereRaw($whereraw)->orderBy("created_at","DESC")->get();
			}
				
			else
			{
				//echo "h2";
			//$whereraw1 = 'condition_leaving = 2 AND last_working_day_resign IS NULL';
			$totaldata= EmpOffline::orderBy("created_at","DESC")->get();	

			}
		}
		
		$graphdata= $totaldata;
		
	   
	   
		$this->jobOpeningselectedList = 0;
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]') != NULL)	
		{
			$this->recruiterCategorySelected = Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterCat]');
		}
		else
		{
			$this->recruiterCategorySelected = '';
		}
		
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)	
		{
			$this->recruitersSelected = explode(",",Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]'));
		}
		else
		{
			$this->recruitersSelected = array();
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
	   $recruiters = RecruiterDetails::where("status",1)->get();
	   $recruiterCategory = RecruiterCategory::where("status",1)->get();
	   $this->widgetName = $widget_name;
	   $this->widgetgraphData = $graphdata;
	   $this->widgetId = $widgetId;
	   $this->recruiters = $recruiters;
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
        return view('components.MyTeam.offboardfnfhome');
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
		public static function getTotalDataResign($dept,$widgetId)
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
		return EmpOffline::select("id")->whereRaw($whereraw)->where("department",$dept)->where("leaving_type",1)->get()->count();
		}
			
		else
		{
			//echo "h2";
			return EmpOffline::select("id")->where("department",$dept)->where("leaving_type",1)->get()->count();
		//return DepartmentFormEntry::where("form_id",2)->whereRaw($whereraw)->get()->count();	

		}
	}

	public static function getTotalDataTerminate($dept,$widgetId)
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
		return EmpOffline::select("id")->whereRaw($whereraw)->where("department",$dept)->where("leaving_type",2)->get()->count();
		}
			
		else
		{
			//echo "h2";
			return EmpOffline::select("id")->where("department",$dept)->where("leaving_type",2)->get()->count();
		//return DepartmentFormEntry::where("form_id",2)->whereRaw($whereraw)->get()->count();	

		}
	}
	public static function getTotalDataForceResign($dept,$widgetId)
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
		return EmpOffline::select("id")->whereRaw($whereraw)->where("department",$dept)->where("leaving_type",4)->get()->count();
		}
			
		else
		{
			//echo "h2";
			return EmpOffline::select("id")->where("department",$dept)->where("leaving_type",4)->get()->count();
		//return DepartmentFormEntry::where("form_id",2)->whereRaw($whereraw)->get()->count();	

		}
	}
	public static function getTotalDataRetained($dept,$widgetId)
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
		return EmpOffline::select("id")->whereRaw($whereraw)->where("department",$dept)->where("retain",1)->get()->count();
		}
			
		else
		{
			//echo "h2";
			return EmpOffline::select("id")->where("department",$dept)->where("retain",1)->get()->count();
		//return DepartmentFormEntry::where("form_id",2)->whereRaw($whereraw)->get()->count();	

		}
	}
	public static function getTotalDataLWDComplete($dept,$widgetId)
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
		if($whereraw == '')
			{
			$whereraw = 'last_working_day_resign is not null OR last_working_day_terminate is not null ';
			}
			else
			{
				$whereraw .= ' AND last_working_day_resign is not null OR last_working_day_terminate is not null';
			}
		
		//echo $whereraw;exit;
		if($whereraw != '')
		{
			//echo "h1";
		return EmpOffline::select("id")->whereRaw($whereraw)->where("department",$dept)->get()->count();
		}
			
		else
		{
			//echo "h2";
			$whereraw1 = 'last_working_day_resign is not null OR last_working_day_terminate is not null ';
			return EmpOffline::select("id")->whereRaw($whereraw1)->where("department",$dept)->get()->count();
		//return DepartmentFormEntry::where("form_id",2)->whereRaw($whereraw)->get()->count();	

		}
	}
	public static function getTotalDataQuestionnairePending($dept,$widgetId)
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
		if($whereraw == '')
				{
					$whereraw = 'condition_leaving = 2 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL AND exit_interview_question_status IS NULL';
				}
				else
				{
					$whereraw .= ' And condition_leaving = 2 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL AND exit_interview_question_status IS NULL';
				}
		
		//echo $whereraw;exit;
		if($whereraw != '')
		{
			//echo "h1";
		return EmpOffline::select("id")->whereRaw($whereraw)->where("department",$dept)->get()->count();
		}
			
		else
		{
			//echo "h2";
			$whereraw1 = 'condition_leaving = 2 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL AND exit_interview_question_status IS NULL';
			return EmpOffline::select("id")->whereRaw($whereraw1)->where("department",$dept)->get()->count();
		//return DepartmentFormEntry::where("form_id",2)->whereRaw($whereraw)->get()->count();	

		}
	}
	public static function getTotalDataAwaitingInterview($dept,$widgetId)
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
		if($whereraw == '')
					{
						$whereraw = 'condition_leaving = 2 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL AND exit_interview_question_status=1';
					}
					else
					{
						$whereraw .= ' And condition_leaving = 2 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL AND exit_interview_question_status=1';
					}
		
		//echo $whereraw;exit;
		if($whereraw != '')
		{
			//echo "h1";
		return EmpOffline::select("id")->whereRaw($whereraw)->where("department",$dept)->get()->count();
		}
			
		else
		{
			//echo "h2";
			$whereraw1 = 'condition_leaving = 2 AND last_working_day_resign IS NULL AND last_working_day_terminate IS NULL AND exit_interview_question_status =1';
			return EmpOffline::select("id")->whereRaw($whereraw1)->where("department",$dept)->get()->count();
		//return DepartmentFormEntry::where("form_id",2)->whereRaw($whereraw)->get()->count();	

		}
	}
	public static function getTotalDataProceedFNF($dept,$widgetId)
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
		return EmpOffline::select("id")->whereRaw($whereraw)->where("department",$dept)->where("condition_leaving",3)->where("retain",2)->get()->count();
		}
			
		else
		{
			//echo "h2";
			return EmpOffline::select("id")->where("condition_leaving",3)->where("retain",2)->where("department",$dept)->get()->count();
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
	public static function getThreeMonthPerformance($empid,$widgetId)
	{
		
		
		//echo $empid."<br>";
		
		$getPerformanceData= MasterPayout::where('employee_id', $empid)->orderBy('id', 'desc')->limit(3)->get();
		
	if($getPerformanceData!=''){
	//echo $empid;exit;
	//print_r($getPerformanceData);exit;
	$cardtotal=0;
	$targettotal=0;
	foreach($getPerformanceData as $_getPerformanceData){
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
	if($targettotal>0){
	$targettotalf=	$targettotal;
	}else{
	$targettotalf=	1;	
	}
		$percentPerform = ($cardtotal / $targettotalf) * 100;
		return $percentPerform = round($percentPerform, 2);	
	}
else{
	return 0;
}	
	}
	public static function getCurrentMonthPerformance($empid,$widgetId)
	{
		//$empid='11100000';
		
		
		
		
		$getPerformanceData= MasterPayout::where('employee_id', $empid)->orderBy("id","DESC")->first();
	if($getPerformanceData!=''){
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
	}else{
		return 0;
	}
		
	}
}