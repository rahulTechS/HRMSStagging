<?php

namespace App\View\Components\Performance;
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
use App\Models\Dashboard\MasterPayout;
use App\Models\Bank\SCB\SCBBankMis;
use App\Models\Bank\SCB\SCBDepartmentFormParentEntry;


class TlPerformanceScbProductivity extends Component
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
	public $SalesTimeSelecteddata;
	
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
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
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
		$totaldata= SCBDepartmentFormParentEntry::select("team")->whereRaw($whereraw)->groupBy('team')->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata= SCBDepartmentFormParentEntry::select("team")->whereRaw($whereraw)->groupBy('team')->get();	

		}
	  
		$graphdata= $totaldata;
		//echo count($totaldata);
	  //print_r($graphdata);exit;
	   
	   
	   
	   
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
		if(Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != '' && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != NULL)	
		{
			$this->SalesTimeSelecteddata = Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']');
		}
		else
		{
			$this->SalesTimeSelecteddata ='';
		}
		$TeamLists = SCBDepartmentFormParentEntry::groupBy('team')->selectRaw('count(*) as total, team')->get();
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
        return view('components.Performance.TLperformancescbproductivity');
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
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
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
		}
		
		
		//echo $whereraw;exit;
		if($whereraw != '')
		{
			//echo "h1";
		return SCBDepartmentFormParentEntry::whereRaw($whereraw)->where("team",$team)->get()->count();
		}
			
		else
		{
			//echo "h2";
		return SCBDepartmentFormParentEntry::whereRaw($whereraw)->get()->count();	

		}
	}
public static function getTeamListsSelectedName($dept){
		//$departmentName = Department::where("id",$data->department)->first()->department_name;
		$name = '';
		foreach($dept as $r)
		{
			if($name == '')
			{
				$name = SCBDepartmentFormParentEntry::where("team",$r)->first()->team;
			}
			else
			{
				$name = $name.','.SCBDepartmentFormParentEntry::where("team",$r)->first()->team;
			}
		}
		return $name;
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
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
			}
		}
		else{
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
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= SCBDepartmentFormParentEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=SCBDepartmentFormParentEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw)->get();	

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
	public static function getTotalProductivity($team,$widgetId,$fromDate,$toDate)
	{
		
			$whereraw='';
		//echo $SalesTime = Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']');exit;
		if(!empty(Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']')) && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != '')
			{
				$SalesTime = Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']');
				  $SalesTimeArray = explode(",",$SalesTime);
					 $SalesTimefinalarray=array();
					 $arry5plus=array();
					 foreach($SalesTimeArray as $_SalesTimearray){
						 if($_SalesTimearray=='All'){
							$arry5plus=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
						 }
						 else if($_SalesTimearray==5){
							$arry5plus=array(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
						 }
						 else{
						 $SalesTimefinalarray[]=$_SalesTimearray;
						 }
						 
						 
					 }
					 $sales = array_merge($SalesTimefinalarray,$arry5plus);
				 //print_r($sales);//exit;
				 //echo $whereraw ;exit;
				 $finalSales=implode(",", $sales);
				 
			}
			else{
				$arry5plus=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
				//$sales = array_merge($SalesTimefinalarray,$arry5plus);
				 //print_r($sales);//exit;
				 //echo $whereraw ;exit;
				 $finalSales=implode(",", $arry5plus);
			}
			$whereraw = 'range_id IN ('.$finalSales.')';
		//echo $whereraw."r";exit;
			$salestime=date("m-Y", strtotime($fromDate));
			//echo $salestime;exit;
			$totalmasterdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",47)->where("tl_name",$team)->get();
			if(count($totalmasterdata)>0){
			$totalemp=count($totalmasterdata);
			$countdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",47)->where("tl_name",$team)->sum('tc');
				if($countdata>0){
				return round($countdata/$totalemp);
				}
				else{
					return 0;
				}
			}	
			else{
			
			$whererawmis='';
			if(!empty(Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']')) && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != '')
				{
					$SalesTime = Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']');
					  $SalesTimeArray = explode(",",$SalesTime);
						 $SalesTimefinalarray=array();
						 $arry5plus=array();
						 foreach($SalesTimeArray as $_SalesTimearray){
							 if($_SalesTimearray=='All'){
								 
							 }
							 else if($_SalesTimearray==5){
								$arry5plus=array(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
							 }
							 else{
							 $SalesTimefinalarray[]=$_SalesTimearray;
							 }
							 
							 
						 }
						 $sales = array_merge($SalesTimefinalarray,$arry5plus);
					 //print_r($sales);//exit;
					 $finalSales=implode(",", $sales);
					 if($whererawmis == '')
					{
						$whererawmis = 'range_id IN ('.$finalSales.')';
					}
					else
					{
						$whererawmis .= ' And range_id IN('.$finalSales.')';
					}
				}
				if($whererawmis == '')
				{
					$whererawmis = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
				}
				else
				{
					$whererawmis .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
				}
			//$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."' And team='".$team."'";
			$totalempdata= SCBDepartmentFormParentEntry::where("ref_no","!=",NULL)->whereRaw($whererawmis)->where("team",$team)->get();
			$totalemp =0;
			if(count($totalempdata)>0){
			foreach($totalempdata as $_totalempdata){
				$employeeExist = Employee_details::where("emp_id",$_totalempdata->emp_id)->first();
				//print_r($employeeExist);
				//echo $_totalempdata->emp_id;exit;
				if($employeeExist!=''){
				if($employeeExist->tl_id!=''|| $employeeExist->tl_id!=NULL){
				$totalemp = Employee_details::where("tl_id",$employeeExist->tl_id)->where("dept_id",47)->count();
				break;
				}
				else{
					continue;
				}
			}				
				}
			}
			$totaldata= SCBDepartmentFormParentEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whererawmis)->where("team",$team)->get();
		
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->ref_no;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=SCBDepartmentFormParentEntry::whereIn("ref_no",$finalarray)->count();
		
		//print_r($countdata);
		if($countdata!=''){
		if($totalemp>0){
			$totalemp=$totalemp;
		}	
		else{
			$totalemp=1;
		}
		return round($countdata/$totalemp);
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
}