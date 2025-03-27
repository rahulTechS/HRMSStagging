<?php

namespace App\View\Components\EmployeeManagement;
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
//use App\Models\Common\MashreqLoginMIS;
//use App\Models\Common\MashreqBankMIS;
//use App\Models\Common\MashreqBookingMIS;
//use App\Models\Common\MashreqMTDMIS;

use App\Models\Attribute\DepartmentFormEntry;


class EmployeeManagementDepartment extends Component
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
	public $department;
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
	public $Graphdata;
	
	
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
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-30 days"));
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
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= Employee_details::where("offline_status",1)->whereRaw($whereraw)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata= Employee_details::where("offline_status",1)->get();	

		}
		
		$graphdata= $totaldata;
		//echo count($totaldata);
		
	  //print_r(json_encode($finalvalue));exit;
	   
	   
	   
	   
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
		$TeamLists = Employee_details::where('offline_status',1)->get();
		$department =Department::where("status",1)->get();
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
		$this->department = $department;
		//$this->Graphdata=$finalvalue;
		//$this->processorSelecteddata = $processorSelected;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.EmployeeManagement.employeemanagementdepartment');
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
	public static function getTotalsubmission($team,$widgetId)
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
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-30 days"));
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
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
		}
		
		
		//echo $whereraw;exit;
		if($whereraw != '')
		{
			//echo "h1";
		return DepartmentFormEntry::where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get()->count();
		}
			
		else
		{
			//echo "h2";
		return DepartmentFormEntry::where("form_id",1)->whereRaw($whereraw)->get()->count();	

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
	
	 public static function getDeptName($deptname){
		$departmentName = Department::where("id",$deptname)->first();
		
		
		
		if($departmentName != '')
			{
				return $departmentName->department_name;
				
		}
		
		else{
		return '';
			
		}
		
	}	
	
	
	
	

public static function getTotalsubmissionlessthen3($team,$widgetId)
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
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-30 days"));
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
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_id;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=MashreqBookingMIS::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("instanceid",$finalarray)->get();
		
		//print_r($countdata);
		if($countdata!=''){
			$count=0;
			
		foreach($countdata as $_countdata){
			
			if($_countdata->total<=3){
				$count=$count+1;
//$totalreturndata=$count;
			}
		}
		return $count;
		}
		else{
			return 0;
		}
		
		}
		else{
			return 0;
		}
		
	}
	public static function getTotalsubmission4to6($team,$widgetId)
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
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-30 days"));
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
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_id;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=MashreqBookingMIS::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("instanceid",$finalarray)->get();
		
		//print_r($countdata);
		if($countdata!=''){
			$count=0;
		foreach($countdata as $_countdata){
			
			if($_countdata->total>=4 && $_countdata->total<=6){
				$count=$count+1;
				$totalreturndata=$count;
			}
		}
		return $count;
		}
		else{
			return 0;
		}
		
		}
		else{
			return 0;
		}
		
	}
		public static function getTotalsubmission7to10($team,$widgetId)
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
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-30 days"));
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
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_id;
		}	
		//print_r($finalarray);exit;	
		$totalbooking=0;
		
		 $countdata=MashreqBookingMIS::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("instanceid",$finalarray)->get();
		
		//print_r($countdata);exit;
		if($countdata!=''){
			$count=0;
		foreach($countdata as $_countdata){
			
			if($_countdata->total>=7 && $_countdata->total<=10){
				$count=$count+1;
				$totalreturndata=$count;
			}
			//echo $totaldata;exit;
		}
		return $count;
		}
		else{
			return 0;
		}
		
		}
		else{
			return 0;
		}
		
	}
	public static function getTotalsubmission10plus($team,$widgetId)
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
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-30 days"));
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
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_id;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=MashreqBookingMIS::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("instanceid",$finalarray)->get();
		
		//print_r($countdata);
		if($countdata!=''){
			$count=0;
		foreach($countdata as $_countdata){
			
			if($_countdata->total>10){
				$count=$count+1;
				$totalreturndata=$count;
			}
		}
		return $count;
		}
		else{
			return 0;
		}
		
		}
		else{
			return 0;
		}
		
	}
	Private function getTeamdataGraph0to3($team,$widgetId)
	{
		//$team="Shahnawaz";
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
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-30 days"));
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
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_id;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=MashreqBookingMIS::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("instanceid",$finalarray)->get();
		
		//print_r($countdata);
		if($countdata!=''){
			$count1=0;
			$count2=0;
			$count3=0;
			$count4=0;
		$countarray1=array();
			$teamarray=array();
		foreach($countdata as $_countdata){
			if($_countdata->total<=3){
				$count1=$count1+1;
				$teamarray["0-3"][$team]=$count1;
				$teamarray['type'] ='column';
				$teamarray['name'] ="0-3";
				
			}
			else{
				$teamarray["0-3"][$team]=$count1;
			}
							
		}
		
		return $teamarray;
		}
		else{
			return 0;
		}
		
		}
		else{
			return 0;
		}
	}
Private function getTeamdataGraph4to6($team,$widgetId)
	{
		//$team="Shahnawaz";
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
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-30 days"));
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
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_id;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=MashreqBookingMIS::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("instanceid",$finalarray)->get();
		
		//print_r($countdata);
		if($countdata!=''){
			$count1=0;
			$count2=0;
			$count3=0;
			$count4=0;
		$countarray1=array();
			$teamarray=array();
		foreach($countdata as $_countdata){
			if($_countdata->total>=4 && $_countdata->total<=6){
				$count2=$count2+1;
				$teamarray["4-6"][$team]=$count2;
				$teamarray['type'] ='column';
				$teamarray['name'] ="4-6";
			}
			else{
			$teamarray["4-6"][$team]=$count2;
			}
							
		}
		
		return $teamarray;
		}
		else{
			return 0;
		}
		
		}
		else{
			return 0;
		}
	}
	Private function getTeamdataGraph7to10($team,$widgetId)
	{
		//$team="Shahnawaz";
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
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-30 days"));
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
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_id;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=MashreqBookingMIS::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("instanceid",$finalarray)->get();
		
		
		if($countdata!=''){
			$count1=0;
			$count2=0;
			$count3=0;
			$count4=0;
			$countarray1=array();
			$teamarray=array();
		foreach($countdata as $_countdata){
			//print_r($_countdata->total);
			if($_countdata->total>=7 && $_countdata->total<=10){
				$count3=$count3+1;
				$teamarray["7-10"][$team]=$count3;
				$teamarray['type'] ='column';
				$teamarray['name'] ="7-10";
			}
			else{
				$teamarray["7-10"][$team]=$count3;
			}
							
		}
		//print_r($teamarray);
		return $teamarray;
		}
		else{
			return 0;
		}
		
		}
		else{
			return 0;
		}
	}
		Private function getTeamdataGraph10plus($team,$widgetId)
	{
		//$team="Shahnawaz";
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
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-30 days"));
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
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_id;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=MashreqBookingMIS::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("instanceid",$finalarray)->get();
		
		//print_r($countdata);
		if($countdata!=''){
			$count1=0;
			$count2=0;
			$count3=0;
			$count4=0;
		$countarray1=array();
			$teamarray=array();
		foreach($countdata as $_countdata){
			if($_countdata->total>10){
				$count4=$count4+1;
				$teamarray["10+"][$team]=$count4;
				$teamarray['type'] ='column';
				$teamarray['name'] ="10+";
			}
			else{
			$teamarray["10+"][$team]=$count4;
			}
							
		}
		
		return $teamarray;
		}
		else{
			return 0;
		}
		
		}
		else{
			return 0;
		}
	}
	
	
	public static function getVintageData($empid)
	   {
		  
			
	
				$empdat=Employee_details::where("emp_id",$empid)->first();		 
				if($empdat!='' && $empdat->doj!=''){
				 $doj=date("Y-m-d",strtotime(str_replace("/","-",$empdat->doj)));
				  }else{
					$doj='';  
				  }		
				
				if($doj !=''){
					$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =   date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 //echo   $returnData;


					 return $returnData;
				}
				else{
					return "--";
				}
			}
				

	
	
	
}