<?php

namespace App\View\Components\ComparePreference;
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
use App\User;
use App\Models\Common\MashreqLoginMIS;
use App\Models\Common\MashreqBankMIS;
use App\Models\Common\MashreqBookingMIS;
use App\Models\Common\MashreqMTDMIS;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Dashboard\MasterPayout;
use App\Models\Bank\CBD\CBDBankMis;

class LastMonthCbdCumulativeSubmissions extends Component
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
	public $WeekList;
	public $lagendData;
	public $salestime;
	
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
				//$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-1 month'));
			//$fromDate = $m.'-'.'01';
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-3 month'));
			$fromDate = $m.'-'.'01';
			}
			else{
				if(Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]')!=''){
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				}
				else{
				$dates =  Request::session()->get('widgetFilterHiring['.$widgetId.'][date_salesTime]');
			
				$dd="01-".$dates;
				$date=date("Y-m-d",strtotime($dd));

				 $fromDate = date("Y-m-d",strtotime($date)); //2023-01-01
				$toDate = date("Y-m-t",strtotime($date)); //2023-01-31	
				}
				
			}
			if($whereraw == '')
			{
				$whereraw = "approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			//$fromDate= date('Y-m-d', strtotime('first day of last month'));


			//$toDate= date('Y-m-d', strtotime('last day of last month'));	
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			if($whereraw == '')
			{
				$whereraw = "approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
			}
		}
			
		if(Request::session()->get('widgetFiltermolTeam['.$widgetId.']') != '' && Request::session()->get('widgetFiltermolTeam['.$widgetId.']') != NULL )
		{
			$deptIds =  Request::session()->get('widgetFiltermolTeam['.$widgetId.']');
			
			$cnameArray = explode(",",$deptIds);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
			
			if($whereraw == '')
			{
			$whereraw = 'team IN('.$finalcname.')';
			}
			else
			{
				$whereraw .= ' AND team IN('.$finalcname.')';
			}
		}
		
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Akshada','Shahnawaz');
			$team_Umar_168 = array('Arsalan','Zubair');
			$team_Arsalan_129 = array('Mohsin','Sahir');
			$sales_processor_internalarray =  Request::session()->get('widgetFilterprocessor['.$widgetId.']');
			
			$sales_processor_internal=explode(",",$sales_processor_internalarray);
			
			//print_r($sales_processor_internal);
			foreach($sales_processor_internal as $sales_processor_internal_value)
			{				
				if($sales_processor_internal_value=='Mahwish')
				{
					//echo "h1";
					$team = array_merge($team,$team_Mahwish_130);
				}
				if($sales_processor_internal_value=='Arsalan')
				{
					//echo "h2";
					$team = array_merge($team,$team_Arsalan_129);
				}
				if($sales_processor_internal_value=='Umar')
				{
					//echo "h3";
					$team = array_merge($team,$team_Umar_168);
				}
			}
			//print_r($team);exit;
			$teamfinalarray=array();
			 foreach($team as $teamarray){
				 $teamfinalarray[]="'".$teamarray."'";
				 
				 
			 }
			$teamfinal=implode(",",$teamfinalarray);
			if($whereraw == '')
			{
			$whereraw = 'team IN('.$teamfinal.')';
			}
			else
			{
				$whereraw .= ' AND team IN('.$teamfinal.')';
			}
					
		}
		$this->loggedinuser = Request::session()->get('EmployeeId');
		$userData= User::where('id',$this->loggedinuser)->first();
		$empData= Employee_details::where('emp_id',$userData->employee_id)->where('job_function',3)->first();
		if($empData!=''){
			$totalempdata= Employee_details::where('tl_id',$empData->id)->where('dept_id',49)->get();
		}
		else{
			$totalempdata= Employee_details::where('dept_id',49)->get();
		}
		
		$finalemp=array();
			foreach($totalempdata as $emp)
			{
				$finalemp[]=$emp->emp_id;
			}
		//echo $whereraw;
		//print_r($finalemp);exit;
		
		if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::whereIn("emp_id",$finalemp)->whereRaw($whereraw)->groupBy('emp_id')->get();
		
		}
			
		else
		{
			
		$totaldata= DepartmentFormEntry::whereIn("emp_id",$finalemp)->whereRaw($whereraw)->groupBy('emp_id')->get();

		}
			
		//print_r($totaldata);exit;
		
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
		$TeamLists = DepartmentFormEntry::groupBy('team')->selectRaw('count(*) as total, team')->whereNotNull('team')->get();
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
		$this->salestime=MasterPayout::select('sales_time')->where("dept_id",49)->orderBy("sort_order","DESC")->get()->unique('sales_time');
		
		//$this->processorSelecteddata = $processorSelected;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.ComparePreference.cbdcumulativesubmissionslastmonth');
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
	
public static function currentMonthData($emp_id,$widgetId){
	$whereraw = '';
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
			}
		
		
		
		
		if(Request::session()->get('widgetFiltermolTeam['.$widgetId.']') != '' && Request::session()->get('widgetFiltermolTeam['.$widgetId.']') != NULL )
		{
			$deptIds =  Request::session()->get('widgetFiltermolTeam['.$widgetId.']');
			
			$cnameArray = explode(",",$deptIds);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
			
			if($whereraw == '')
			{
			$whereraw = 'team IN('.$finalcname.')';
			}
			else
			{
				$whereraw .= ' AND team IN('.$finalcname.')';
			}
		}
		
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Akshada','Shahnawaz');
			$team_Umar_168 = array('Arsalan','Zubair');
			$team_Arsalan_129 = array('Mohsin','Sahir');
			$sales_processor_internalarray =  Request::session()->get('widgetFilterprocessor['.$widgetId.']');
			
			$sales_processor_internal=explode(",",$sales_processor_internalarray);
			
			//print_r($sales_processor_internal);
			foreach($sales_processor_internal as $sales_processor_internal_value)
			{				
				if($sales_processor_internal_value=='Mahwish')
				{
					//echo "h1";
					$team = array_merge($team,$team_Mahwish_130);
				}
				if($sales_processor_internal_value=='Arsalan')
				{
					//echo "h2";
					$team = array_merge($team,$team_Arsalan_129);
				}
				if($sales_processor_internal_value=='Umar')
				{
					//echo "h3";
					$team = array_merge($team,$team_Umar_168);
				}
			}
			//print_r($team);exit;
			$teamfinalarray=array();
			 foreach($team as $teamarray){
				 $teamfinalarray[]="'".$teamarray."'";
				 
				 
			 }
			$teamfinal=implode(",",$teamfinalarray);
			if($whereraw == '')
			{
			$whereraw = 'team IN('.$teamfinal.')';
			}
			else
			{
				$whereraw .= ' AND team IN('.$teamfinal.')';
			}
					
		}
		//echo $whereraw;exit;
		if(Request::session()->get('widgetFilterBYSubmissions['.$widgetId.']') != '' && Request::session()->get('widgetFilterBYSubmissions['.$widgetId.']')=="Submissions" )
			{
				if($whereraw != '')
				{
				return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->whereRaw($whereraw)->get()->count();
				
				}
					
				else
				{
					
				return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->whereRaw($whereraw)->get()->count();

				}
			}
			else{
				if($whereraw != '')
				{
				//return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->whereRaw($whereraw)->whereIn("form_status",array("Booked","booked"))->get()->count();
				
			//$totaldata= CBDBankMis::whereIn("employee_id",$emp_id)->whereRaw($whereraw)->groupBy('employee_id')->get();
			
		return $totaldata=CBDBankMis::whereIn("Status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw)->where('employee_id', $emp_id)->orderBy("id","DESC")->get()->count();
	
				}
					
				else
				{
					
				//return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->whereRaw($whereraw)->whereIn("form_status",array("Booked","booked"))->get()->count();

                return $totaldata=CBDBankMis::whereIn("Status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw)->where('employee_id', $emp_id)->orderBy("id","DESC")->get()->count();

				}
			}
	
}
public static function LastMonthcountData($emp_id,$widgetId){
		$whereraw = '';
		//echo "".Request::session()->get('widgetFilterHiring['.$widgetId.'][date_salesTime]');exit;
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][date_salesTime]')!=''){
			$dates =  Request::session()->get('widgetFilterHiring['.$widgetId.'][date_salesTime]');
			
				$dd="01-".$dates;
				$date=date("Y-m-d",strtotime($dd));

				 $fromDate = date("Y-m-d",strtotime($date)); //2023-01-01
				$toDate = date("Y-m-t",strtotime($date)); //2023-01-31	
			if($whereraw == '')
			{
				$whereraw = "approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
			}
		}else{
			$toDate = date("Y-m-d",strtotime("-1 month ".date("Y-m-d")));
			$fromDate =$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And approval_date >= '".$fromDate."' and approval_date <= '".$toDate."'";
			}
		}
		
		//echo $whereraw;
		
		
		if(Request::session()->get('widgetFiltermolTeam['.$widgetId.']') != '' && Request::session()->get('widgetFiltermolTeam['.$widgetId.']') != NULL )
		{
			$deptIds =  Request::session()->get('widgetFiltermolTeam['.$widgetId.']');
			
			$cnameArray = explode(",",$deptIds);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
			
			if($whereraw == '')
			{
			$whereraw = 'team IN('.$finalcname.')';
			}
			else
			{
				$whereraw .= ' AND team IN('.$finalcname.')';
			}
		}
		
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Akshada','Shahnawaz');
			$team_Umar_168 = array('Arsalan','Zubair');
			$team_Arsalan_129 = array('Mohsin','Sahir');
			$sales_processor_internalarray =  Request::session()->get('widgetFilterprocessor['.$widgetId.']');
			
			$sales_processor_internal=explode(",",$sales_processor_internalarray);
			
			//print_r($sales_processor_internal);
			foreach($sales_processor_internal as $sales_processor_internal_value)
			{				
				if($sales_processor_internal_value=='Mahwish')
				{
					//echo "h1";
					$team = array_merge($team,$team_Mahwish_130);
				}
				if($sales_processor_internal_value=='Arsalan')
				{
					//echo "h2";
					$team = array_merge($team,$team_Arsalan_129);
				}
				if($sales_processor_internal_value=='Umar')
				{
					//echo "h3";
					$team = array_merge($team,$team_Umar_168);
				}
			}
			//print_r($team);exit;
			$teamfinalarray=array();
			 foreach($team as $teamarray){
				 $teamfinalarray[]="'".$teamarray."'";
				 
				 
			 }
			$teamfinal=implode(",",$teamfinalarray);
			if($whereraw == '')
			{
			$whereraw = 'team IN('.$teamfinal.')';
			}
			else
			{
				$whereraw .= ' AND team IN('.$teamfinal.')';
			}
					
		}
		
		if(Request::session()->get('widgetFilterBYSubmissions['.$widgetId.']') != '' && Request::session()->get('widgetFilterBYSubmissions['.$widgetId.']')=="Submissions" )
			{
				if($whereraw != '')
				{
				return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->whereRaw($whereraw)->get()->count();
				
				}
					
				else
				{
					
				return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->whereRaw($whereraw)->get()->count();

				}
			}
			else{
				if($whereraw != '')
				{
			//	return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->whereRaw($whereraw)->whereIn("form_status",array("Booked","booked"))->get()->count();
				
				return $totaldata=CBDBankMis::whereIn("Status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw)->where('employee_id', $emp_id)->orderBy("id","DESC")->get()->count();

				
				
				}
					
				else
				{
					
				//return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->whereRaw($whereraw)->whereIn("form_status",array("Booked","booked"))->get()->count();

				return $totaldata=CBDBankMis::whereIn("Status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw)->where('employee_id', $emp_id)->orderBy("id","DESC")->get()->count();


				}
			}
	
}

	
}
