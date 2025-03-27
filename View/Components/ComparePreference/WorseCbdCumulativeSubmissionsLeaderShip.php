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
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Dashboard\MasterPayout;
use App\Models\Recruiter\Designation;

class WorseCbdCumulativeSubmissionsLeaderShip extends Component
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
	public $totaldatacount;
	
    public function __construct($widgetId)
    {
		
        $widget_name = WidgetCreation::where("id",$widgetId)->first()->widget_name;
	   //$widgetData = WidgetBarMol::where("widget_id",$widgetId)->first();
	  
	   $whereraw = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		
		
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
			$whereraw = 'sales_name IN('.$finalcname.')';
			}
			else
			{
				$whereraw .= ' AND sales_name IN('.$finalcname.')';
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
		//echo $whereraw;exit;
		//print_r($finalemp);exit;
		
		$design=Designation::where("tlsm",2)->where("department_id",49)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);
			    //echo $finalarray;
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetailsdata = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",49)->where("offline_status",1)->whereNotIn('emp_id', [102482])->get();
				$reportsCountcbd = Employee_details::whereRaw($whereraw)->whereNotNull('tl_id')->where("dept_id",49)->where("offline_status",1)->where("job_function",2)->get()->count();
				}
				else
				{
					$empdetailsdata = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",49)->where("offline_status",1)->whereNotIn('emp_id', [102482])->get();
					$reportsCountcbd = Employee_details::where("offline_status",1)->whereNotNull('tl_id')->where("dept_id",49)->where("job_function",2)->get()->count();	
					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails=array();
			//echo count($empdetailsdata);exit;
			if(count($empdetailsdata)>0){
				
				foreach($empdetailsdata as $_Tldata){
					$tL_salesData = Employee_details::where("tl_id",$_Tldata->id)->where("offline_status",1)->where("job_function",2)->get();
					if($tL_salesData!=''){
					$empdetails[$_Tldata->id]=$tL_salesData;
					}
				}
				
				
			}
			
		//echo count($empdetailsdata);exit;
		
		$graphdata= $empdetailsdata;
		
	   
	   //total count data
		
		$whereraw1 = '';
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
			if($whereraw1 == '')
			{
				$whereraw1 = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw1 .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			//$fromDate= date('Y-m-d', strtotime('first day of last month'));
			//$toDate= date('Y-m-d', strtotime('last day of last month'));	
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			if($whereraw1 == '')
			{
				$whereraw1 = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw1 .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
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
			
			if($whereraw1 == '')
			{
			$whereraw1 = 'team IN('.$finalcname.')';
			}
			else
			{
				$whereraw1 .= ' AND team IN('.$finalcname.')';
			}
		}
		
		
		$this->loggedinuser = Request::session()->get('EmployeeId');
		$userData= User::where('id',$this->loggedinuser)->first();
		$empData= Employee_details::where('emp_id',$userData->employee_id)->where('job_function',3)->first();
		if($empData!=''){
			$totalempdata= Employee_details::where('tl_id',$empData->id)->where('dept_id',49)->where("offline_status",1)->get();
		}
		else{
			$totalempdata= Employee_details::where('dept_id',49)->where("offline_status",1)->get();
		}
		
		$finalemp=array();
			foreach($totalempdata as $emp)
			{
				$finalemp[]=$emp->emp_id;
			}
		//echo $whereraw;
		//print_r($finalemp);exit;

		if(Request::session()->get('widgetFilterBYSubmissions['.$widgetId.']') != '' && Request::session()->get('widgetFilterBYSubmissions['.$widgetId.']')=="Submissions" )
			{
				if($whereraw1 != '')
				{
				$totaldatacount= DepartmentFormEntry::whereIn("emp_id",$finalemp)->where("form_id",2)->whereRaw($whereraw1)->groupBy('emp_id')->get();
				
				}
					
				else
				{
					
				$totaldatacount= DepartmentFormEntry::whereIn("emp_id",$finalemp)->where("form_id",2)->whereRaw($whereraw1)->groupBy('emp_id')->get();

				}
			}
			else{
				//echo "wait";//exit;
				if($whereraw1 != '')
				{
				$totaldatacount= DepartmentFormEntry::whereIn("emp_id",$finalemp)->where("form_id",2)->whereRaw($whereraw1)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->groupBy('emp_id')->get();
				
				}
					
				else
				{
					
				$totaldatacount= DepartmentFormEntry::whereIn("emp_id",$finalemp)->where("form_id",2)->whereRaw($whereraw1)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->groupBy('emp_id')->get();

				}
			}
		
		
			
		
		
		$totaldatacount= $totaldatacount;
	   
	   
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
		
		
		$TeamLists = DepartmentFormEntry::groupBy('team')->selectRaw('count(*) as total, team')->whereNotNull('team')->where("form_id",2)->get();
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
		$this->totaldatacount=$graphdata;
		
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.ComparePreference.cbdcumulativesubmissionsworseleadership');
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
	Private function getMOLTyped($jobId,$widgetId)
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
				$whereraw = "mol_date >= '".$fromDate."' and mol_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And mol_date >= '".$fromDate."' and mol_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "mol_date >= '".$fromDate."' and mol_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And mol_date >= '".$fromDate."' and mol_date <= '".$toDate."'";
			}
		}
		if(Request::session()->get('widgetFiltermolDept['.$widgetId.']') != '' && Request::session()->get('widgetFiltermolDept['.$widgetId.']') != NULL && Request::session()->get('widgetFiltermolDept['.$widgetId.']') !=1)
		{
			$deptIds =  Request::session()->get('widgetFiltermolDept['.$widgetId.']');
			
			$deptIdsArray = explode(",",$deptIds);
			if($whereraw == '')
			{
			$whereraw = 'department IN('.$deptIds.')';
			}
			else
			{
				$whereraw .= ' AND department IN('.$deptIds.')';
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
		//echo $whereraw;
		if($whereraw != '')
		{
		return DocumentCollectionDetails::where("mol_date","!=",NULL)->where("job_opening",$jobId)->where("backout_status",1)->whereRaw($whereraw)->get()->count();
		}
			
		else
		{
		return DocumentCollectionDetails::where("mol_date","!=",NULL)->whereRaw($whereraw)->where("job_opening",$jobId)->where("backout_status",1)->get()->count();	

		}
	}
public static function getTeamListsSelectedName($dept){
		//$departmentName = Department::where("id",$data->department)->first()->department_name;
		$name = '';
		foreach($dept as $r)
		{
			if($name == '')
			{
				$name = DepartmentFormEntry::where("team",$r)->where("form_id",2)->first()->team;
			}
			else
			{
				$name = $name.','.DepartmentFormEntry::where("team",$r)->where("form_id",2)->first()->team;
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
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
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
		
		
		//echo $whereraw;exit;
		if(Request::session()->get('widgetFilterBYSubmissions['.$widgetId.']') != '' && Request::session()->get('widgetFilterBYSubmissions['.$widgetId.']')=="Submissions" )
			{
				if($whereraw != '')
				{
				return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->where("form_id",2)->whereRaw($whereraw)->get()->count();
				
				}
					
				else
				{
					
				return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->where("form_id",2)->whereRaw($whereraw)->get()->count();

				}
			}
			else{
				if($whereraw != '')
				{
				return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->where("form_id",2)->whereRaw($whereraw)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->get()->count();
				
				}
					
				else
				{
					
				return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->where("form_id",2)->whereRaw($whereraw)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->get()->count();

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
				$dateto=date("d")."-".$dates;
				$toDate = date("Y-m-d",strtotime($dateto)); //2023-01-31
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}else{
			$toDate = date("Y-m-d",strtotime("-1 month ".date("Y-m-d")));
			$fromDate =$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
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
		
		
		if(Request::session()->get('widgetFilterBYSubmissions['.$widgetId.']') != '' && Request::session()->get('widgetFilterBYSubmissions['.$widgetId.']')=="Submissions" )
			{
				if($whereraw != '')
				{
				return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->where("form_id",2)->whereRaw($whereraw)->get()->count();
				
				}
					
				else
				{
					
				return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->where("form_id",2)->whereRaw($whereraw)->get()->count();

				}
			}
			else{
				if($whereraw != '')
				{
				return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->where("form_id",2)->whereRaw($whereraw)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->get()->count();
				
				}
					
				else
				{
					
				return $totaldata= DepartmentFormEntry::where("emp_id",$emp_id)->where("form_id",2)->whereRaw($whereraw)->whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->get()->count();

				}
			}
	
}
public static function TLDataCount($ids,$widgetId){
	
	$whereraw1 = '';
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
			if($whereraw1 == '')
			{
				$whereraw1 = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw1 .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
			//$toDate = date("Y-m-d");
			//$fromDate = date("Y").'-'.date("m").'-'.'01';
			$fromDate= date('Y-m-d', strtotime('first day of last month'));


			$toDate= date('Y-m-d', strtotime('last day of last month'));	
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			if($whereraw1 == '')
			{
				$whereraw1 = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw1 .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
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
			
			if($whereraw1 == '')
			{
			$whereraw1 = 'team IN('.$finalcname.')';
			}
			else
			{
				$whereraw1 .= ' AND team IN('.$finalcname.')';
			}
		}
		
		
		
			$totalempdata= Employee_details::where('tl_id',$ids)->where('dept_id',49)->where("offline_status",1)->get();
		
		$finalemp1=array();
			foreach($totalempdata as $emp)
			{
				$finalemp1[]=$emp->emp_id;
			}
		//echo $whereraw;
		//print_r($finalemp);exit;
		
		if($whereraw1 != '')
		{
		return $totaltldata= DepartmentFormEntry::whereIn("emp_id",$finalemp1)->where("form_id",2)->whereRaw($whereraw1)->groupBy('emp_id')->get();
		
		}
			
		else
		{
			
		return $totaltldata= DepartmentFormEntry::whereIn("emp_id",$finalemp1)->where("form_id",2)->whereRaw($whereraw1)->groupBy('emp_id')->get();

		}
		//return "wait..";		 
	
}
	
}
