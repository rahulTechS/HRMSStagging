<?php

namespace App\View\Components\EIB;
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
use App\Models\Bank\EIB\EibBankMis;
use App\Models\Attribute\EIBDepartmentFormEntry;
use App\Models\Attribute\EIBDepartmentFormChildEntry;


class TlPerformanceEibSpread extends Component
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
				//$toDate = date("Y-m-d");
				//$fromDate = date("Y").'-'.date("m").'-'.'01';
				
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				
			}
			elseif($datatype == 'last_month')
			{
				//$toDate = date("Y-m-d");
				//$fromDate = date("Y-m-d",strtotime("-30 days"));
				
				$toDate = date("Y").'-'.date("m").'-'.'05';
				$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
			}
			elseif($datatype == 'month_3')
			{
				//$toDate = date("Y-m-d");
				//$fromDate = date("Y-m-d",strtotime("-90 days"));
				$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				
				
				
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
			//$toDate = date("Y-m-d");
			//$fromDate = date("Y").'-'.date("m").'-'.'01';
			
			$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
			
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
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
			$whereraw = 'tl_name IN('.$finalcname.')';
			}
			else
			{
				$whereraw .= ' AND tl_name IN('.$finalcname.')';
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
			$whereraw = 'tl_name IN('.$teamfinal.')';
			}
			else
			{
				$whereraw .= ' AND tl_name IN('.$teamfinal.')';
			}
					
		}
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= EIBDepartmentFormEntry::select('tl_name')->whereRaw($whereraw)->groupBy('tl_name')->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata= EIBDepartmentFormEntry::select("tl_name")->whereRaw($whereraw)->groupBy('tl_name')->get();	

		}
		$finalarray=array();
		$totalarray=array();
		foreach($totaldata as $_totaldata){
			
			$dataval0to3=$this->getTeamdataGraph0to3($_totaldata->tl_name,$widgetId);
			$dataval4to6=$this->getTeamdataGraph4to6($_totaldata->tl_name,$widgetId);
			$dataval7to10=$this->getTeamdataGraph7to10($_totaldata->tl_name,$widgetId);
			$dataval10plus=$this->getTeamdataGraph10plus($_totaldata->tl_name,$widgetId);
			//$dataval0to3=$this->getTeamdataGraph0to3($_totaldata->team,$widgetId);
			if(isset($dataval0to3["0-3"])){
			$finalarray["0-3"][]=$dataval0to3["0-3"][$_totaldata->tl_name];
			}
			else{
				$finalarray["0-3"][]=0;
			}
			$finalarray['type']["0-3"]='column';
     		$finalarray['name']["0-3"] ="0-3";
			if(isset($dataval4to6["4-6"])){
			$finalarray["4-6"][]=$dataval4to6["4-6"][$_totaldata->tl_name];
			}
			else{
				$finalarray["4-6"][]=0;
			}
			$finalarray['type']["4-6"]='column';
     		$finalarray['name']["0-3"] ="4-6";
			if(isset($dataval7to10["7-10"])){
			$finalarray["7-10"][]=$dataval7to10["7-10"][$_totaldata->tl_name];
			}
			else{
				$finalarray["7-10"][]=0;
			}
			$finalarray['type']["7-10"]='column';
     		$finalarray['name']["7-10"] ="7-10";
			if(isset($dataval10plus["10+"])){
			$finalarray["10+"][]=$dataval10plus["10+"][$_totaldata->tl_name];
			}
			else{
				$finalarray["10+"][]=0;
			}
			$finalarray['type']["10+"]='column';
     		$finalarray['name']["10+"] ="10+";
			$finalarray['team'][] =$_totaldata->tl_name;
			//exit;
			
		}
	     $finalvalue= $finalarray;
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
		$TeamLists = EIBDepartmentFormEntry::groupBy('tl_name')->selectRaw('count(*) as total, tl_name')->get();
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
		$this->Graphdata=$finalvalue;
		//$this->processorSelecteddata = $processorSelected;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.EIB.TLperformanceeibspread');
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
				//$toDate = date("Y-m-d");
				//$fromDate = date("Y").'-'.date("m").'-'.'01';
				
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				
				
			}
			elseif($datatype == 'last_month')
			{
				//$toDate = date("Y-m-d");
				//$fromDate = date("Y-m-d",strtotime("-30 days"));
				$toDate = date("Y").'-'.date("m").'-'.'05';
				$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
			}
			elseif($datatype == 'month_3')
			{
				//$toDate = date("Y-m-d");
				//$fromDate = date("Y-m-d",strtotime("-90 days"));
				$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				
				
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
			//$toDate = date("Y-m-d");
			//$fromDate = date("Y").'-'.date("m").'-'.'01';
			
			$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
			
			
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
	

public static function getTotalsubmissionlessthen3($team,$widgetId)
	{
		//echo $team."rr";exit;
		$whereraw = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
			}
			elseif($datatype == 'last_month')
			{
				
			//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
			    $toDate = date("Y").'-'.date("m").'-'.'05';
				$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';	
			}
			elseif($datatype == 'month_3')
			{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-3 month ".date("Y-m-d"))).'-'.date("m",strtotime("-3 month ".date("Y-m-d"))).'-'.'06';
				
				$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				
				
				
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
		}
		
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();	

		}
		//echo $whereraw;
		
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_no;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 //$countdata=MashreqBookingMIS::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("instanceid",$finalarray)->get();
		$countdata=EibBankMis::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("application_no",$finalarray)->where("matched_status",1)->where("final_decision","Approve")->get();
		
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
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
			}
			elseif($datatype == 'last_month')
			{
				
			
			
			//$toDate = date("Y").'-'.date("m").'-'.'05';
			//	$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
			
			 $toDate = date("Y").'-'.date("m").'-'.'05';
				$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';	
			
				
			}
			elseif($datatype == 'month_3')
			{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-3 month ".date("Y-m-d"))).'-'.date("m",strtotime("-3 month ".date("Y-m-d"))).'-'.'06';
				$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				
				
				
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				
				
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_no;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 //$countdata=MashreqBookingMIS::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("instanceid",$finalarray)->get();
		$countdata=EibBankMis::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("application_no",$finalarray)->where("matched_status",1)->where("final_decision","Approve")->get();
		
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
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
			}
			elseif($datatype == 'last_month')
			{
				
			
			
			//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				
				 $toDate = date("Y").'-'.date("m").'-'.'05';
				$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';	
				
			}
			elseif($datatype == 'month_3')
			{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-3 month ".date("Y-m-d"))).'-'.date("m",strtotime("-3 month ".date("Y-m-d"))).'-'.'06';
				
				$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				
				
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_no;
		}	
		//print_r($finalarray);exit;	
		$totalbooking=0;
		
		 $countdata=EibBankMis::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("application_no",$finalarray)->where("matched_status",1)->where("final_decision","Approve")->get();
		
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
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
			}
			elseif($datatype == 'last_month')
			{
				
			 $toDate = date("Y").'-'.date("m").'-'.'05';
				$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';	
			
			//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				
			}
			elseif($datatype == 'month_3')
			{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-3 month ".date("Y-m-d"))).'-'.date("m",strtotime("-3 month ".date("Y-m-d"))).'-'.'06';
				
				$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				
				
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_no;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		// $countdata=MashreqBookingMIS::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("instanceid",$finalarray)->get();
		$countdata=EibBankMis::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("application_no",$finalarray)->where("matched_status",1)->where("final_decision","Approve")->get();
		
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
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
			}
			elseif($datatype == 'last_month')
			{
				
			
			
			//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				 $toDate = date("Y").'-'.date("m").'-'.'05';
				$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';	
			
			}
			elseif($datatype == 'month_3')
			{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-3 month ".date("Y-m-d"))).'-'.date("m",strtotime("-3 month ".date("Y-m-d"))).'-'.'06';
				
				$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_no;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=EibBankMis::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("application_no",$finalarray)->where("matched_status",1)->where("final_decision","Approve")->get();
		
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
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				
				
			}
			elseif($datatype == 'last_month')
			{
				
			//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
			
			$toDate = date("Y").'-'.date("m").'-'.'05';
				$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				
			}
			elseif($datatype == 'month_3')
			{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-3 month ".date("Y-m-d"))).'-'.date("m",strtotime("-3 month ".date("Y-m-d"))).'-'.'06';
				
				$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_no;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		  $countdata=EibBankMis::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("application_no",$finalarray)->where("matched_status",1)->where("final_decision","Approve")->get();
		
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
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
			}
			elseif($datatype == 'last_month')
			{
				
			
			
			//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				$toDate = date("Y").'-'.date("m").'-'.'05';
				$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
			}
			elseif($datatype == 'month_3')
			{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-3 month ".date("Y-m-d"))).'-'.date("m",strtotime("-3 month ".date("Y-m-d"))).'-'.'06';
				
				$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_no;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=EibBankMis::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("application_no",$finalarray)->where("matched_status",1)->where("final_decision","Approve")->get();
		
		
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
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
			}
			elseif($datatype == 'last_month')
			{
				
			
			
			    // $toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				
				
				$toDate = date("Y").'-'.date("m").'-'.'05';
				$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				
			}
			elseif($datatype == 'month_3')
			{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-3 month ".date("Y-m-d"))).'-'.date("m",strtotime("-3 month ".date("Y-m-d"))).'-'.'06';
			$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}	
				
				
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
				//$toDate = date("Y").'-'.date("m").'-'.'05';
				//$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				
			if($whereraw == '')
			{
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."' And tl_name='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=EIBDepartmentFormEntry::select("application_no")->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->application_no;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=EibBankMis::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("application_no",$finalarray)->where("matched_status",1)->where("final_decision","Approve")->get();
		
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
}