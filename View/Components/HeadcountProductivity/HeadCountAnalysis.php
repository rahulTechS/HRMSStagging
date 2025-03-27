<?php

namespace App\View\Components\HeadcountProductivity;
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
use App\Models\Dashboard\MasterPayout;
use App\Models\Dashboard\MashreqFinalMTD;
use App\Models\Dashboard\MasterPayoutPre;
use App\Models\Dashboard\MasterProcessorList;




class HeadCountAnalysis extends Component
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
	public $TeamListsSelected2;
	public $processorSelecteddata;
	public $SalesTimeSelecteddata;
	public $processorList;
	
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
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
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














		if(Request::session()->get('widgetFiltermolRange['.$widgetId.']') != '' && Request::session()->get('widgetFiltermolRange['.$widgetId.']') != NULL )
		{
			$deptIds =  Request::session()->get('widgetFiltermolRange['.$widgetId.']');

			// echo $deptIds."Helllllllllllllllllllllo";
			// exit;
			
			// $cnameArray = explode(",",$deptIds);
					 
			// 		 $namefinalarray=array();
			// 		 foreach($cnameArray as $namearray){
			// 			 $namefinalarray[]="'".$namearray."'";
						 
						 
			// 		 }
			// 		 //print_r($namefinalarray);exit;
			// 		 $finalcname=implode(",", $namefinalarray);
			
			// if($whereraw == '')
			// {
			// $whereraw = 'team IN('.$finalcname.')';
			// }
			// else
			// {
			// 	$whereraw .= ' AND team IN('.$finalcname.')';
			// }
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
		
		echo $whereraw;
		//exit;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= DepartmentFormEntry::select("team")->where("form_id",1)->whereRaw($whereraw)->groupBy('team')->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata= DepartmentFormEntry::select("team")->whereRaw($whereraw)->where("form_id",1)->groupBy('team')->get();	

		}
	  
		$graphdata= $totaldata;
		//echo count($totaldata);
	// 	echo "<pre>";
	//   print_r($graphdata);exit;
	   
	   
	   
	   
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







		if(Request::session()->get('widgetFiltermolRange['.$widgetId.']') != '' && Request::session()->get('widgetFiltermolRange['.$widgetId.']') != NULL)	
		{
			$this->TeamListsSelected2 = explode(",",Request::session()->get('widgetFiltermolRange['.$widgetId.']'));
		}
		else
		{
			$this->TeamListsSelected2 ='';
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
		$TeamLists = DepartmentFormEntry::groupBy('team')->selectRaw('count(*) as total, team')->get();


		$fprocessorList = MasterProcessorList::select('processor')->where("dept_id",36)->get()->unique('processor');	  

		


	   $recruiters = RecruiterDetails::where("status",1)->get();
	   $recruiterCategory = RecruiterCategory::where("status",1)->get();
	   $this->widgetName = $widget_name;
	   $this->widgetgraphData = $graphdata;
	   $this->processorList = $fprocessorList;
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
        //return view('components.Performance.TLperformancemashreqproductivity');
		return view('components.HeadcountProductivity.HeadCountAnalysisProductivity');
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
	
	
	// public static function getTotalProductivity($team,$widgetId,$fromDate,$toDate,$range_val)
	// {
		
	// 	//return $range_val;
		
	// 	$whereraw='';
	// 	if(!empty(Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']')) && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != '')
	// 	{
	// 		$SalesTime = Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']');
	// 		$SalesTimeArray = explode(",",$SalesTime);
	// 		$SalesTimefinalarray=array();
	// 		$arry5plus=array();
	// 		foreach($SalesTimeArray as $_SalesTimearray)
	// 		{
	// 			if($_SalesTimearray=='All')
	// 			{
	// 				$arry5plus=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
	// 			}
	// 			else if($_SalesTimearray==5)
	// 			{
	// 				$arry5plus=array(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
	// 			}
	// 			else
	// 			{
	// 				$SalesTimefinalarray[]=$_SalesTimearray;
	// 			}
	// 		}
	// 		$sales = array_merge($SalesTimefinalarray,$arry5plus);
	// 		//print_r($sales);//exit;
	// 		//echo $whereraw ;exit;
	// 		$finalSales=implode(",", $sales);				
	// 	}
	// 	else
	// 	{
	// 		$arry5plus=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
	// 		$finalSales=implode(",", $arry5plus);
	// 	}


	// 	if($range_val == 2)
	// 	{
	// 		$arryRange=array(1,2,3);
	// 		$finalSales=implode(",", $arryRange);
	// 	}
	// 	elseif($range_val == 3)
	// 	{
	// 		$arryRange=array(4,5,6);
	// 		$finalSales=implode(",", $arryRange);
	// 	}
	// 	elseif($range_val == 4)
	// 	{
	// 		$arryRange=array(7,8,9,10,11,12);
	// 		$finalSales=implode(",", $arryRange);
	// 	}
	// 	elseif($range_val == 5)
	// 	{
	// 		$arryRange=array(13,14,15,16,17,18,19,20,21,22,23,24,25);
	// 		$finalSales=implode(",", $arryRange);
	// 	}
	// 	else
	// 	{
	// 		$finalSales=$finalSales;
	// 	}

	// 	// print_r($finalSales);
	// 	// return "Hello";




	// 	$whereraw = 'range_id IN ('.$finalSales.')';
	// 	//echo $whereraw;exit;
	// 	$salestime=date("m-Y", strtotime($fromDate));
	// 	//echo $salestime;exit;
	// 	$totalmasterdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->get();




	// 	if(count($totalmasterdata)>0)
	// 	{
	// 		$totalemp=count($totalmasterdata);


	// 		// New code
	// 		$tl_details = Employee_details::where("sales_name",$team)->get();

	// 		//return $tl_details;
	// 		$tl_id = "";
	// 		foreach($tl_details as $tldata)
	// 		{
				
	// 			if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
	// 			{
	// 				$tl_id = $tldata->tl_id;
	// 				break;
					
	// 			}
	// 			else
	// 			{
	// 				continue;
	// 			}
	// 		}

	// 		if($tl_id!='')
	// 		{
	// 			$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
	// 			//break;
	// 		}
	// 		else
	// 		{
	// 			//continue;
	// 		}
	// 		// New code



	// 		$countdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');
	// 		if($countdata>0)
	// 		{
	// 			return "Hello".round($countdata/$totalemp);
	// 		}
	// 		else
	// 		{
	// 			return 0;
	// 		}
	// 	}
	// 	else
	// 	{
	// 		$whererawmis='';
	// 		if(!empty(Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']')) && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != '')
	// 		{
	// 			$SalesTime = Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']');
	// 			$SalesTimeArray = explode(",",$SalesTime);
	// 			$SalesTimefinalarray=array();
	// 			$arry5plus=array();
	// 			foreach($SalesTimeArray as $_SalesTimearray)
	// 			{
	// 				if($_SalesTimearray=='All')
	// 				{
						
	// 				}
	// 				else if($_SalesTimearray==5)
	// 				{
	// 					$arry5plus=array(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
	// 				}
	// 				else{
	// 					$SalesTimefinalarray[]=$_SalesTimearray;
	// 				}					
	// 			}
	// 			$sales = array_merge($SalesTimefinalarray,$arry5plus);
	// 			//print_r($sales);//exit;
	// 			$finalSales=implode(",", $sales);
	// 			if($whererawmis == '')
	// 			{
	// 				$whererawmis = 'range_id IN ('.$finalSales.')';
	// 			}
	// 			else
	// 			{
	// 				$whererawmis .= ' And range_id IN('.$finalSales.')';
	// 			}
	// 		}

	// 		if($whererawmis == '')
	// 		{
	// 			$whererawmis = "dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
	// 		}
	// 		else
	// 		{
	// 			$whererawmis .= " And dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
	// 		}
	// 		//echo $whererawmis;
	// 		//$whererawmis = "dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";	
	// 		$totalmtd=MashreqFinalMTD::whereRaw($whererawmis)->where("team",$team)->get();


	// 		if(count($totalmtd)>0)
	// 		{
	// 			//$totalemp=0;
	// 			foreach($totalmtd as $_mtd)
	// 			{
	// 				$employeeExist = Employee_details::where("emp_id",$_mtd->emp_id)->first();
	// 				//echo $_mtd->emp_id;exit;
	// 				if($employeeExist->tl_id!=''|| $employeeExist->tl_id!=NULL)
	// 				{
	// 					$totalemp = Employee_details::where("tl_id",$employeeExist->tl_id)->where("dept_id",36)->count();
	// 					break;
	// 				}
	// 				else{
	// 					continue;
	// 				}
								
	// 			}


	// 			// New code
	// 			$tl_details = Employee_details::where("sales_name",$team)->get();

	// 			//return $tl_details;
	// 			$tl_id = "";
	// 			foreach($tl_details as $tldata)
	// 			{
					
	// 				if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
	// 				{
	// 					$tl_id = $tldata->tl_id;
	// 					break;
						
	// 				}
	// 				else
	// 				{
	// 					continue;
	// 				}
	// 			}

	// 			if($tl_id!='')
	// 			{
	// 				$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
	// 				//break;
	// 			}
	// 			else
	// 			{
	// 				$totalemp = 0;
	// 			}
	// 			// New code







	// 			//echo $totalemp;//exit;
	// 			$countdata=MashreqFinalMTD::whereRaw($whererawmis)->where("team",$team)->count();
			
	// 			if($countdata>0 && $totalemp > 0)
	// 			{
	// 				//return "Hello333".round($countdata/$totalemp);
	// 				return round(ceil($countdata)/$totalemp,2)." (Total: ".$totalemp.")";
	// 			}
	// 			else
	// 			{
	// 				$res=0;
	// 				return $res." (Total: ".$totalemp.")";
	// 			}	
	// 		}
	// 		else
	// 		{
	// 			$whereraw1='';
	// 			if(!empty(Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']')) && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != '')
	// 			{
	// 				$SalesTime = Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']');
	// 			  	$SalesTimeArray = explode(",",$SalesTime);
	// 				$SalesTimefinalarray=array();
	// 				$arry5plus=array();
	// 				foreach($SalesTimeArray as $_SalesTimearray)
	// 				{
	// 					if($_SalesTimearray=='All')
	// 					{
							
	// 					}
	// 					else if($_SalesTimearray==5)
	// 					{
	// 						$arry5plus=array(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
	// 					}
	// 					else
	// 					{
	// 						$SalesTimefinalarray[]=$_SalesTimearray;
	// 					}						
	// 				}
	// 				$sales = array_merge($SalesTimefinalarray,$arry5plus);
	// 				//print_r($sales);//exit;
	// 				$finalSales=implode(",", $sales);
	// 				if($whereraw1 == '')
	// 				{
	// 					$whereraw1 = 'range_id IN ('.$finalSales.')';
	// 				}
	// 				else
	// 				{
	// 					$whereraw1 .= ' And range_id IN('.$finalSales.')';
	// 				}
	// 			}

				
	// 			if($whereraw1 == '')
	// 			{
	// 				$whereraw1 = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
	// 			}
	// 			else
	// 			{
	// 				$whereraw1 .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
	// 			}


	// 			$tl_details = Employee_details::where("sales_name",$team)->get();

	// 			//return $tl_details;
	// 			$tl_id = "";
	// 			foreach($tl_details as $tldata)
	// 			{
					
	// 				if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
	// 				{
	// 					$tl_id = $tldata->tl_id;
	// 					break;
						
	// 				}
	// 				else
	// 				{
	// 					continue;
	// 				}
	// 			}

	// 			if($tl_id!='')
	// 			{
	// 				$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
	// 				//break;
	// 			}
	// 			else
	// 			{
	// 				$totalemp = 0;
	// 			}



	// 			//$whereraw1 = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
	// 			// $totalempdata= DepartmentFormEntry::where("emp_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw1)->get();
	// 			// foreach($totalempdata as $_data)
	// 			// {
	// 			// 	$employeeExist = Employee_details::where("emp_id",$_data->emp_id)->first();
	// 			// 	//echo $employeeExist->tl_id;exit;
	// 			// 	if($employeeExist->tl_id!=''|| $employeeExist->tl_id!=NULL)
	// 			// 	{
	// 			// 		$totalemp = Employee_details::where("tl_id",$employeeExist->tl_id)->where("dept_id",36)->count();
	// 			// 		break;
	// 			// 	}
	// 			// 	else
	// 			// 	{
	// 			// 		continue;
	// 			// 	}
									
	// 			// }
	// 			$totaldata= DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw1)->get();

	// 			if($totaldata!='')
	// 			{
	// 				$finalarray=array();
	// 				foreach($totaldata as $_totaldata)
	// 				{
	// 					//print_r($_totaldata);exit;
	// 					$finalarray[]=$_totaldata->application_id;
	// 				}	
	// 				//print_r($finalarray);	
	// 				$totalbooking=0;
				
	// 				$countdata=MashreqBookingMIS::whereIn("instanceid",$finalarray)->count();
				
	// 				//print_r($countdata);
	// 				if($countdata!='' && $totalemp > 0)
	// 				{
	// 					//return ceil($countdata/$totalemp)." Total Get: ".$countdata." Total Emp: ".$totalemp;
	// 					return round(ceil($countdata)/$totalemp,2)." (Total: ".$totalemp.")";
	// 					//echo ceil($number*100)/100;
	// 				}
	// 				else
	// 				{
						
	// 					$res =0;
	// 					return $res." (Total: ".$totalemp.")";

	// 				}
				
	// 			}
	// 			else
	// 			{
	// 				return 0;
	// 			}





	// 		}
			






 	// 	}
		
		








	// }



	// public static function getTeamProductivity($team,$widgetId,$fromDate,$toDate)
	// {
	// 	$whereraw='';
	// 	if(!empty(Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']')) && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != '')
	// 	{
	// 		$SalesTime = Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']');
	// 		$SalesTimeArray = explode(",",$SalesTime);
	// 		$SalesTimefinalarray=array();
	// 		$arry5plus=array();
	// 		foreach($SalesTimeArray as $_SalesTimearray)
	// 		{
	// 			if($_SalesTimearray=='All')
	// 			{
	// 				$arry5plus=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
	// 			}
	// 			else if($_SalesTimearray==5)
	// 			{
	// 				$arry5plus=array(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
	// 			}
	// 			else
	// 			{
	// 				$SalesTimefinalarray[]=$_SalesTimearray;
	// 			}
	// 		}
	// 		$sales = array_merge($SalesTimefinalarray,$arry5plus);
	// 		//print_r($sales);//exit;
	// 		//echo $whereraw ;exit;
	// 		$finalSales=implode(",", $sales);				
	// 	}
	// 	else
	// 	{
	// 		$arry5plus=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
	// 		$finalSales=implode(",", $arry5plus);
	// 	}




	// 	$whereraw = 'range_id IN ('.$finalSales.')';
	// 	//echo $whereraw."r";exit;
	// 	$salestime=date("m-Y", strtotime($fromDate));
	// 	//echo $salestime;exit;
	// 	$totalmasterdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->get();
	// 	if(count($totalmasterdata)>0)
	// 	{
	// 		$totalemp=count($totalmasterdata);


	// 		// New code
	// 		$tl_details = Employee_details::where("sales_name",$team)->get();

	// 		//return $tl_details;
	// 		$tl_id = "";
	// 		foreach($tl_details as $tldata)
	// 		{
				
	// 			if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
	// 			{
	// 				$tl_id = $tldata->tl_id;
	// 				break;
					
	// 			}
	// 			else
	// 			{
	// 				continue;
	// 			}
	// 		}

	// 		if($tl_id!='')
	// 		{
	// 			$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
	// 			//break;
	// 		}
	// 		else
	// 		{
	// 			//continue;
	// 		}
	// 		// New code



	// 		$countdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');
	// 		if($countdata>0)
	// 		{
	// 			return round($countdata/$totalemp);
	// 		}
	// 		else
	// 		{
	// 			return 0;
	// 		}
	// 	}
	// 	else
	// 	{
	// 		$whererawmis='';
	// 		if(!empty(Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']')) && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != '')
	// 		{
	// 			$SalesTime = Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']');
	// 			$SalesTimeArray = explode(",",$SalesTime);
	// 			$SalesTimefinalarray=array();
	// 			$arry5plus=array();
	// 			foreach($SalesTimeArray as $_SalesTimearray)
	// 			{
	// 				if($_SalesTimearray=='All')
	// 				{
						
	// 				}
	// 				else if($_SalesTimearray==5)
	// 				{
	// 					$arry5plus=array(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
	// 				}
	// 				else{
	// 					$SalesTimefinalarray[]=$_SalesTimearray;
	// 				}					
	// 			}
	// 			$sales = array_merge($SalesTimefinalarray,$arry5plus);
	// 			//print_r($sales);//exit;
	// 			$finalSales=implode(",", $sales);
	// 			if($whererawmis == '')
	// 			{
	// 				$whererawmis = 'range_id IN ('.$finalSales.')';
	// 			}
	// 			else
	// 			{
	// 				$whererawmis .= ' And range_id IN('.$finalSales.')';
	// 			}
	// 		}

	// 		if($whererawmis == '')
	// 		{
	// 			$whererawmis = "dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
	// 		}
	// 		else
	// 		{
	// 			$whererawmis .= " And dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
	// 		}
	// 		//echo $whererawmis;
	// 		//$whererawmis = "dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";	
	// 		$totalmtd=MashreqFinalMTD::whereRaw($whererawmis)->where("team",$team)->get();


	// 		if(count($totalmtd)>0)
	// 		{
	// 			//$totalemp=0;
	// 			foreach($totalmtd as $_mtd)
	// 			{
	// 				$employeeExist = Employee_details::where("emp_id",$_mtd->emp_id)->first();
	// 				//echo $_mtd->emp_id;exit;
	// 				if($employeeExist->tl_id!=''|| $employeeExist->tl_id!=NULL)
	// 				{
	// 					$totalemp = Employee_details::where("tl_id",$employeeExist->tl_id)->where("dept_id",36)->count();
	// 					break;
	// 				}
	// 				else{
	// 					continue;
	// 				}
								
	// 			}


	// 			// New code
	// 			$tl_details = Employee_details::where("sales_name",$team)->get();

	// 			//return $tl_details;
	// 			$tl_id = "";
	// 			foreach($tl_details as $tldata)
	// 			{
					
	// 				if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
	// 				{
	// 					$tl_id = $tldata->tl_id;
	// 					break;
						
	// 				}
	// 				else
	// 				{
	// 					continue;
	// 				}
	// 			}

	// 			if($tl_id!='')
	// 			{
	// 				$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
	// 				//break;
	// 			}
	// 			else
	// 			{
	// 				//continue;
	// 			}
	// 			// New code







	// 			//echo $totalemp;//exit;
	// 			$countdata=MashreqFinalMTD::whereRaw($whererawmis)->where("team",$team)->count();
			
	// 			if($countdata>0)
	// 			{
	// 				//return "Hello333".round($countdata/$totalemp);
	// 				return round(ceil($countdata)/$totalemp,2);
	// 			}
	// 			else
	// 			{
	// 				return 0;
	// 			}	
	// 		}
	// 		else
	// 		{
	// 			$whereraw1='';
	// 			if(!empty(Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']')) && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != '')
	// 			{
	// 				$SalesTime = Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']');
	// 			  	$SalesTimeArray = explode(",",$SalesTime);
	// 				$SalesTimefinalarray=array();
	// 				$arry5plus=array();
	// 				foreach($SalesTimeArray as $_SalesTimearray)
	// 				{
	// 					if($_SalesTimearray=='All')
	// 					{
							
	// 					}
	// 					else if($_SalesTimearray==5)
	// 					{
	// 						$arry5plus=array(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
	// 					}
	// 					else
	// 					{
	// 						$SalesTimefinalarray[]=$_SalesTimearray;
	// 					}						
	// 				}
	// 				$sales = array_merge($SalesTimefinalarray,$arry5plus);
	// 				//print_r($sales);//exit;
	// 				$finalSales=implode(",", $sales);
	// 				if($whereraw1 == '')
	// 				{
	// 					$whereraw1 = 'range_id IN ('.$finalSales.')';
	// 				}
	// 				else
	// 				{
	// 					$whereraw1 .= ' And range_id IN('.$finalSales.')';
	// 				}
	// 			}

				
	// 			if($whereraw1 == '')
	// 			{
	// 				$whereraw1 = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
	// 			}
	// 			else
	// 			{
	// 				$whereraw1 .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
	// 			}


	// 			$tl_details = Employee_details::where("sales_name",$team)->get();

	// 			//return $tl_details;
	// 			$tl_id = "";
	// 			foreach($tl_details as $tldata)
	// 			{
					
	// 				if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
	// 				{
	// 					$tl_id = $tldata->tl_id;
	// 					break;
						
	// 				}
	// 				else
	// 				{
	// 					continue;
	// 				}
	// 			}

	// 			if($tl_id!='')
	// 			{
	// 				$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
	// 				//break;
	// 			}
	// 			else
	// 			{
	// 				$totalemp = 0;
	// 			}



	// 			//$whereraw1 = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
	// 			// $totalempdata= DepartmentFormEntry::where("emp_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw1)->get();
	// 			// foreach($totalempdata as $_data)
	// 			// {
	// 			// 	$employeeExist = Employee_details::where("emp_id",$_data->emp_id)->first();
	// 			// 	//echo $employeeExist->tl_id;exit;
	// 			// 	if($employeeExist->tl_id!=''|| $employeeExist->tl_id!=NULL)
	// 			// 	{
	// 			// 		$totalemp = Employee_details::where("tl_id",$employeeExist->tl_id)->where("dept_id",36)->count();
	// 			// 		break;
	// 			// 	}
	// 			// 	else
	// 			// 	{
	// 			// 		continue;
	// 			// 	}
									
	// 			// }
	// 			$totaldata= DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw1)->get();

	// 			if($totaldata!='')
	// 			{
	// 				$finalarray=array();
	// 				foreach($totaldata as $_totaldata)
	// 				{
	// 					//print_r($_totaldata);exit;
	// 					$finalarray[]=$_totaldata->application_id;
	// 				}	
	// 				//print_r($finalarray);	
	// 				$totalbooking=0;
				
	// 				$countdata=MashreqBookingMIS::whereIn("instanceid",$finalarray)->count();
				
	// 				//print_r($countdata);
	// 				if($countdata!='' && $totalemp > 0)
	// 				{
	// 					//return ceil($countdata/$totalemp)." Total Get: ".$countdata." Total: ".$totalemp;
	// 					return round(ceil($countdata)/$totalemp,2);
	// 					//echo ceil($number*100)/100;
	// 				}
	// 				else
	// 				{
	// 					return 0;
	// 				}
				
	// 			}
	// 			else
	// 			{
	// 				return 0;
	// 			}





	// 		}
			






 	// 	}
		
		








	// }


	


	// public static function getTotalProductivitybyProcessor($processor,$widgetId,$fromDate,$toDate)
	// {
	// 	// return "Hello".$toDate;

	// 	$team1 = "Mujahid";
	// 	$a1= self::getTeamProductivity($team1,$widgetId,$fromDate,$toDate);

	// 	$team2 = "Ajay";
	// 	$a2= self::getTeamProductivity($team2,$widgetId,$fromDate,$toDate);

	// 	$team3 = "Akshada";
	// 	$a3= self::getTeamProductivity($team3,$widgetId,$fromDate,$toDate);

	// 	$team4 = "Shahnawaz";
	// 	$a4= self::getTeamProductivity($team4,$widgetId,$fromDate,$toDate);


		

	// 	return $a1+$a2+$a3+$a4;
	// }


	// public static function getTotalProductivitybyProcessor2($processor,$widgetId,$fromDate,$toDate)
	// {
	// 	// return "Hello".$toDate;

	// 	$team1 = "Arsalan";
	// 	$a1= self::getTeamProductivity($team1,$widgetId,$fromDate,$toDate);

	// 	$team2 = "Zubair";
	// 	$a2= self::getTeamProductivity($team2,$widgetId,$fromDate,$toDate);

	// 	return $a1+$a2;
	// }


	// public static function getTotalProductivitybyProcessor3($processor,$widgetId,$fromDate,$toDate)
	// {
	// 	// return "Hello".$toDate;

	// 	$team1 = "Mohsin";
	// 	$a1= self::getTeamProductivity($team1,$widgetId,$fromDate,$toDate);

	// 	$team2 = "Sahir";
	// 	$a2= self::getTeamProductivity($team2,$widgetId,$fromDate,$toDate);

		
	// 	return $a1+$a2;
	// }




	// new modified code start



	public static function getTotalProductivity($team,$widgetId,$fromDate,$toDate,$range_val)
	{
		$currentdate = date("Y-m");
		//return $fromDate.$toDate;	
		$whereraw='';
		// print_r($finalSales);
		// return "Hello";

		$ftime=strtotime($fromDate);
		$fmonth=date("m",$ftime);
		$fyear=date("Y",$ftime);

		$startDate = $fyear."-".$fmonth;




		$totime=strtotime($fromDate);
		$tomonth=date("m",$totime);
		$toyear=date("Y",$totime);

		$endDate = $fyear."-".$fmonth;


		$arry5plus=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
		$finalSales=implode(",", $arry5plus);

		if($range_val == 2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		elseif($range_val == 3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		elseif($range_val == 4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		elseif($range_val == 5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		else
		{
			$finalSales=$finalSales;
		}


		//print_r($finalSales);
		// exit;
		// return "Hello";


		
		if($startDate == $currentdate && $endDate == $currentdate)
		{
			//return "Current Month";

			$whereraw = 'range_id IN ('.$finalSales.')';
			//echo $whereraw;exit;
			$salestime=date("Y-m", strtotime($fromDate));
			//echo $salestime;
			$term = "%".$salestime."%";
			$totalmasterdata=DepartmentFormEntry::whereRaw($whereraw)->where("submission_date",'like',$term)->where("team",$team)->get();

			if(count($totalmasterdata)>0)
			{
				// New code
				$tl_details = Employee_details::where("sales_name",$team)->where("dept_id",36)->where('offline_status',1)->get();
				//return $tl_details;
				$tl_id = "";
				foreach($tl_details as $tldata)
				{
					if($tldata->id!=''|| $tldata->id!=NULL)
					{
						$tl_id = $tldata->id;
						break;
					}
					else
					{
						continue;
					}
				}

				if($tl_id!='')
				{
					if($range_val!='')
					{
						$totalemp = Employee_details::whereRaw($whereraw)->where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
					}
					else
					{
						$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
					}
					
				}
				else
				{
					$totalemp = 0;
				}
				// New code


				
				if($range_val!='')
				{
					$countdata=DepartmentFormEntry::whereRaw($whereraw)->where("submission_date",'like',$term)->where("team",$team)->where('form_status',"Booked")->get()->count();
				}
				else
				{
					$countdata=DepartmentFormEntry::where("submission_date",'like',$term)->where("team",$team)->where('form_status',"Booked")->get()->count();
				}
				

				//$countdata=DepartmentFormEntry::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');

				//$countd = count();

				if($countdata > 0 && $totalemp > 0)
				{
					//return "Hello".round($countdata/$totalemp);
					return round(ceil($countdata)/$totalemp,2)." (Total: ".$totalemp.")";
				}
				else
				{
					$res=0;
					return $res;
				}
			}
			else
			{
				return 0;
			}
		}
		else
		{
			$whereraw = 'range_id IN ('.$finalSales.')';
			//echo $whereraw;exit;
			$salestime=date("n-Y", strtotime($fromDate));
			//echo $salestime;
			$totalmasterdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->get();

			//return $totalmasterdata;

			

			
			if(count($totalmasterdata)>0)
			{
				// New code
				// $tl_details = Employee_details::where("sales_name",$team)->get();
				// //return $tl_details;
				// $tl_id = "";
				// foreach($tl_details as $tldata)
				// {
				// 	if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
				// 	{
				// 		$tl_id = $tldata->tl_id;
				// 		break;
				// 	}
				// 	else
				// 	{
				// 		continue;
				// 	}
				// }

				// if($tl_id!='')
				// {
					if($range_val!='')
					{
						//$totalemp = Employee_details::whereRaw($whereraw)->where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
						//$totalemp = MasterPayout::whereRaw($whereraw)->where("TL",$team)->where("sales_time",$salestime)->count();
						$totalemp = MasterPayout::whereRaw($whereraw)->where("tl_name",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();


					}
					else
					{
						//$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
						//$totalemp = MasterPayout::where("TL",$team)->where("sales_time",$salestime)->count();
						$totalemp = MasterPayout::where("tl_name",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();


					}

					if($totalemp > 0)
					{
						$totalemp = $totalemp;
					}
					else
					{
						$totalemp = 0;
					}
					
				// }
				// else
				// {
				// 	$totalemp = 0;
				// }
				// New code

				

				if($range_val!='')
				{
					
					$countdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');
				}
				else
				{
					$countdata=MasterPayout::where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');
				}

				if($countdata > 0 && $totalemp > 0)
				{
					//return "Hello".round($countdata/$totalemp);
					return round(ceil($countdata)/$totalemp,2)." (Total: ".$totalemp.")";
				}
				else
				{
					$res=0;
					return $res;
				}
			}
			else
			{
				
				$whereraw = 'range_id IN ('.$finalSales.')';
				//echo $whereraw;exit;
				$salestime=date("n-Y", strtotime($fromDate));
				// New code
				// $tl_details = Employee_details::where("sales_name",$team)->get();
				// //return $tl_details;
				// $tl_id = "";
				// foreach($tl_details as $tldata)
				// {
				// 	if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
				// 	{
				// 		$tl_id = $tldata->tl_id;
				// 		break;
				// 	}
				// 	else
				// 	{
				// 		continue;
				// 	}
				// }

				
					if($range_val!='')
					{
						//$totalemp = Employee_details::whereRaw($whereraw)->where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();

						$totalemp = MasterPayoutPre::whereRaw($whereraw)->where("TL",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();

					}
					else
					{
						//$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();

						$totalemp = MasterPayoutPre::where("TL",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();
					}

					if($totalemp > 0)
					{
						$totalemp = $totalemp;
					}
					else
					{
						$totalemp = 0;
					}
					
				
				// New code

				// echo $whereraw;

				// echo $salestime;
				// exit;
				if($range_val!='')
				{
					$countdata=MasterPayoutPre::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("TL",$team)->sum('tc');
				}
				else
				{
					$countdata=MasterPayoutPre::where("sales_time",$salestime)->where("dept_id",36)->where("TL",$team)->sum('tc');
				}

				
				//return $totalemp;

				if($countdata > 0 && $totalemp > 0)
				{
					//return "Hello".round($countdata/$totalemp);
					$prepercentValue = round(ceil($countdata)/$totalemp,2);
					$finalpercentValue = $prepercentValue *100;
					return round(ceil($countdata)/$totalemp,2)." (Total: ".$totalemp.")";
					//return $finalpercentValue." (Total: ".$totalemp.":::".$countdata.")";



					//return round(($countdata/$totalemp)*100)." (Totalxxx: ".$totalemp.":::".$countdata.")";
				}
				else
				{
					$res=0;
					return $res;
				}
				
				
				
			}

		}
		
	}

	public static function getTeamProductivity($team,$widgetId,$fromDate,$toDate,$range_val)
	{
		$currentdate = date("Y-m");
		//return $fromDate.$toDate;	
		$whereraw='';
		// print_r($finalSales);
		// return "Hello";

		$ftime=strtotime($fromDate);
		$fmonth=date("m",$ftime);
		$fyear=date("Y",$ftime);

		$startDate = $fyear."-".$fmonth;




		$totime=strtotime($fromDate);
		$tomonth=date("m",$totime);
		$toyear=date("Y",$totime);

		$endDate = $fyear."-".$fmonth;


		$arry5plus=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
		$finalSales=implode(",", $arry5plus);

		if($range_val == 2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		elseif($range_val == 3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		elseif($range_val == 4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		elseif($range_val == 5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		else
		{
			$finalSales=$finalSales;
		}


		
		if($startDate == $currentdate && $endDate == $currentdate)
		{
			//return "Current Month";

			$whereraw = 'range_id IN ('.$finalSales.')';
			//echo $whereraw;exit;
			$salestime=date("Y-m", strtotime($fromDate));
			//echo $salestime;
			$term = "%".$salestime."%";
			$totalmasterdata=DepartmentFormEntry::whereRaw($whereraw)->where("submission_date",'like',$term)->where("team",$team)->get();

			if(count($totalmasterdata)>0)
			{
				// New code
				//$tl_details = Employee_details::where("sales_name",$team)->get();
				$tl_details = Employee_details::where("sales_name",$team)->where("dept_id",36)->where('offline_status',1)->get();
				//return $tl_details;
				$tl_id = "";
				foreach($tl_details as $tldata)
				{
					if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
					{
						$tl_id = $tldata->tl_id;
						break;
					}
					else
					{
						continue;
					}
				}

				if($tl_id!='')
				{
					if($range_val!='')
					{
						$totalemp = Employee_details::whereRaw($whereraw)->where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
					}
					else
					{
						$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
					}
					
				}
				else
				{
					$totalemp = 0;
				}
				// New code

				//$countdata=DepartmentFormEntry::whereRaw($whereraw)->where("submission_date",'like',$term)->where("team",$team)->where('form_status',"Booked")->get()->count();


				if($range_val!='')
				{
					$countdata=DepartmentFormEntry::whereRaw($whereraw)->where("submission_date",'like',$term)->where("team",$team)->where('form_status',"Booked")->get()->count();
				}
				else
				{
					$countdata=DepartmentFormEntry::where("submission_date",'like',$term)->where("team",$team)->where('form_status',"Booked")->get()->count();
				}

				//$countdata=DepartmentFormEntry::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');

				//$countd = count();

				if($countdata > 0 && $totalemp > 0)
				{
					//return "Hello".round($countdata/$totalemp);
					return round(ceil($countdata)/$totalemp,2);
				}
				else
				{
					$res=0;
					return $res;
				}
			}
			else
			{
				return 0;
			}
		}
		else
		{
			$whereraw = 'range_id IN ('.$finalSales.')';
			//echo $whereraw;exit;
			$salestime=date("n-Y", strtotime($fromDate));
			//echo $salestime;
			$totalmasterdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->get();

			//return $totalmasterdata;

			

			
			if(count($totalmasterdata)>0)
			{
				// New code
				// $tl_details = Employee_details::where("sales_name",$team)->get();
				// //return $tl_details;
				// $tl_id = "";
				// foreach($tl_details as $tldata)
				// {
				// 	if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
				// 	{
				// 		$tl_id = $tldata->tl_id;
				// 		break;
				// 	}
				// 	else
				// 	{
				// 		continue;
				// 	}
				// }

				// if($tl_id!='')
				// {
					if($range_val!='')
					{
						//$totalemp = Employee_details::whereRaw($whereraw)->where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
						//$totalemp = MasterPayout::whereRaw($whereraw)->where("TL",$team)->where("sales_time",$salestime)->count();
						$totalemp = MasterPayout::whereRaw($whereraw)->where("tl_name",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();


					}
					else
					{
						//$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
						//$totalemp = MasterPayout::where("TL",$team)->where("sales_time",$salestime)->count();
						$totalemp = MasterPayout::where("tl_name",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();


					}

					if($totalemp > 0)
					{
						$totalemp = $totalemp;
					}
					else
					{
						$totalemp = 0;
					}
					
				// }
				// else
				// {
				// 	$totalemp = 0;
				// }
				// New code

				//$countdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');

				if($range_val!='')
				{
					
					$countdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');
				}
				else
				{
					$countdata=MasterPayout::where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');
				}

				if($countdata > 0 && $totalemp > 0)
				{
					//return "Hello".round($countdata/$totalemp);
					return round(ceil($countdata)/$totalemp,2);
				}
				else
				{
					$res=0;
					return $res;
				}
			}
			else
			{
				
				$whereraw = 'range_id IN ('.$finalSales.')';
				//echo $whereraw;exit;
				$salestime=date("n-Y", strtotime($fromDate));
				// New code
				// $tl_details = Employee_details::where("sales_name",$team)->get();
				// //return $tl_details;
				// $tl_id = "";
				// foreach($tl_details as $tldata)
				// {
				// 	if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
				// 	{
				// 		$tl_id = $tldata->tl_id;
				// 		break;
				// 	}
				// 	else
				// 	{
				// 		continue;
				// 	}
				// }

				
					if($range_val!='')
					{
						//$totalemp = Employee_details::whereRaw($whereraw)->where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();

						$totalemp = MasterPayoutPre::whereRaw($whereraw)->where("TL",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();

					}
					else
					{
						//$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();

						$totalemp = MasterPayoutPre::where("TL",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();
					}

					if($totalemp > 0)
					{
						$totalemp = $totalemp;
					}
					else
					{
						$totalemp = 0;
					}
					
				
				// New code

				// echo $whereraw;

				// echo $salestime;
				// exit;


				//$countdata=MasterPayoutPre::where("sales_time",$salestime)->where("dept_id",36)->where("TL",$team)->sum('tc');
				if($range_val!='')
				{
					$countdata=MasterPayoutPre::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("TL",$team)->sum('tc');
				}
				else
				{
					$countdata=MasterPayoutPre::where("sales_time",$salestime)->where("dept_id",36)->where("TL",$team)->sum('tc');
				}
				//return $totalemp;

				if($countdata > 0 && $totalemp > 0)
				{
					//return "Hello".round($countdata/$totalemp);
					return round(ceil($countdata)/$totalemp,2);
				}
				else
				{
					$res=0;
					return $res;
				}
				
				
				
			}

		}
		
	}
	public static function getTeamProductivityOLD2222($team,$widgetId,$fromDate,$toDate,$range_val)
	{
		
		
	}

	public static function getTeamProductivityOLDOLD($team,$widgetId,$fromDate,$toDate)
	{
		$whereraw='';
		if(!empty(Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']')) && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != '')
		{
			$SalesTime = Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']');
			$SalesTimeArray = explode(",",$SalesTime);
			$SalesTimefinalarray=array();
			$arry5plus=array();
			foreach($SalesTimeArray as $_SalesTimearray)
			{
				if($_SalesTimearray=='All')
				{
					$arry5plus=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
				}
				else if($_SalesTimearray==5)
				{
					$arry5plus=array(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
				}
				else
				{
					$SalesTimefinalarray[]=$_SalesTimearray;
				}
			}
			$sales = array_merge($SalesTimefinalarray,$arry5plus);
			//print_r($sales);//exit;
			//echo $whereraw ;exit;
			$finalSales=implode(",", $sales);				
		}
		else
		{
			$arry5plus=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
			$finalSales=implode(",", $arry5plus);
		}




		$whereraw = 'range_id IN ('.$finalSales.')';
		//echo $whereraw."r";exit;
		$salestime=date("m-Y", strtotime($fromDate));
		//echo $salestime;exit;
		$totalmasterdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->get();
		if(count($totalmasterdata)>0)
		{
			$totalemp=count($totalmasterdata);


			// New code
			$tl_details = Employee_details::where("sales_name",$team)->get();

			//return $tl_details;
			$tl_id = "";
			foreach($tl_details as $tldata)
			{
				
				if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
				{
					$tl_id = $tldata->tl_id;
					break;
					
				}
				else
				{
					continue;
				}
			}

			if($tl_id!='')
			{
				$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
				//break;
			}
			else
			{
				//continue;
			}
			// New code



			$countdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');
			if($countdata>0)
			{
				return round($countdata/$totalemp);
			}
			else
			{
				return 0;
			}
		}
		else
		{
			$whererawmis='';
			if(!empty(Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']')) && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != '')
			{
				$SalesTime = Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']');
				$SalesTimeArray = explode(",",$SalesTime);
				$SalesTimefinalarray=array();
				$arry5plus=array();
				foreach($SalesTimeArray as $_SalesTimearray)
				{
					if($_SalesTimearray=='All')
					{
						
					}
					else if($_SalesTimearray==5)
					{
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
				$whererawmis = "dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
			}
			else
			{
				$whererawmis .= " And dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
			}
			//echo $whererawmis;
			//$whererawmis = "dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";	
			$totalmtd=MashreqFinalMTD::whereRaw($whererawmis)->where("team",$team)->get();


			if(count($totalmtd)>0)
			{
				//$totalemp=0;
				foreach($totalmtd as $_mtd)
				{
					$employeeExist = Employee_details::where("emp_id",$_mtd->emp_id)->first();
					//echo $_mtd->emp_id;exit;
					if($employeeExist->tl_id!=''|| $employeeExist->tl_id!=NULL)
					{
						$totalemp = Employee_details::where("tl_id",$employeeExist->tl_id)->where("dept_id",36)->count();
						break;
					}
					else{
						continue;
					}
								
				}


				// New code
				$tl_details = Employee_details::where("sales_name",$team)->get();

				//return $tl_details;
				$tl_id = "";
				foreach($tl_details as $tldata)
				{
					
					if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
					{
						$tl_id = $tldata->tl_id;
						break;
						
					}
					else
					{
						continue;
					}
				}

				if($tl_id!='')
				{
					$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
					//break;
				}
				else
				{
					//continue;
				}
				// New code







				//echo $totalemp;//exit;
				$countdata=MashreqFinalMTD::whereRaw($whererawmis)->where("team",$team)->count();
			
				if($countdata>0)
				{
					//return "Hello333".round($countdata/$totalemp);
					return round(ceil($countdata)/$totalemp,2);
				}
				else
				{
					return 0;
				}	
			}
			else
			{
				$whereraw1='';
				if(!empty(Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']')) && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']') != '')
				{
					$SalesTime = Request::session()->get('widgetFiltermolSalesTime['.$widgetId.']');
				  	$SalesTimeArray = explode(",",$SalesTime);
					$SalesTimefinalarray=array();
					$arry5plus=array();
					foreach($SalesTimeArray as $_SalesTimearray)
					{
						if($_SalesTimearray=='All')
						{
							
						}
						else if($_SalesTimearray==5)
						{
							$arry5plus=array(5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
						}
						else
						{
							$SalesTimefinalarray[]=$_SalesTimearray;
						}						
					}
					$sales = array_merge($SalesTimefinalarray,$arry5plus);
					//print_r($sales);//exit;
					$finalSales=implode(",", $sales);
					if($whereraw1 == '')
					{
						$whereraw1 = 'range_id IN ('.$finalSales.')';
					}
					else
					{
						$whereraw1 .= ' And range_id IN('.$finalSales.')';
					}
				}

				
				if($whereraw1 == '')
				{
					$whereraw1 = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
				}
				else
				{
					$whereraw1 .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
				}


				$tl_details = Employee_details::where("sales_name",$team)->get();

				//return $tl_details;
				$tl_id = "";
				foreach($tl_details as $tldata)
				{
					
					if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
					{
						$tl_id = $tldata->tl_id;
						break;
						
					}
					else
					{
						continue;
					}
				}

				if($tl_id!='')
				{
					$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
					//break;
				}
				else
				{
					$totalemp = 0;
				}



				//$whereraw1 = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."' And team='".$team."'";
				// $totalempdata= DepartmentFormEntry::where("emp_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw1)->get();
				// foreach($totalempdata as $_data)
				// {
				// 	$employeeExist = Employee_details::where("emp_id",$_data->emp_id)->first();
				// 	//echo $employeeExist->tl_id;exit;
				// 	if($employeeExist->tl_id!=''|| $employeeExist->tl_id!=NULL)
				// 	{
				// 		$totalemp = Employee_details::where("tl_id",$employeeExist->tl_id)->where("dept_id",36)->count();
				// 		break;
				// 	}
				// 	else
				// 	{
				// 		continue;
				// 	}
									
				// }
				$totaldata= DepartmentFormEntry::select("application_id")->where("application_id","!=",NULL)->where("form_id",1)->whereRaw($whereraw1)->get();

				if($totaldata!='')
				{
					$finalarray=array();
					foreach($totaldata as $_totaldata)
					{
						//print_r($_totaldata);exit;
						$finalarray[]=$_totaldata->application_id;
					}	
					//print_r($finalarray);	
					$totalbooking=0;
				
					$countdata=MashreqBookingMIS::whereIn("instanceid",$finalarray)->count();
				
					//print_r($countdata);
					if($countdata!='' && $totalemp > 0)
					{
						//return ceil($countdata/$totalemp)." Total Get: ".$countdata." Total Emp: ".$totalemp;
						return round(ceil($countdata)/$totalemp,2);
						//echo ceil($number*100)/100;
					}
					else
					{
						return 0;
					}
				
				}
				else
				{
					return 0;
				}





			}
			






 		}
		
		








	}


	


	public static function getTotalProductivitybyProcessorData($processor,$widgetId,$fromDate,$toDate,$range_val)
	{
		$currentdate = date("Y-m");
		//return $fromDate.$toDate;	
		$whereraw='';
		// print_r($finalSales);
		// return "Hello";

		$ftime=strtotime($fromDate);
		$fmonth=date("m",$ftime);
		$fyear=date("Y",$ftime);

		$startDate = $fyear."-".$fmonth;




		$totime=strtotime($fromDate);
		$tomonth=date("m",$totime);
		$toyear=date("Y",$totime);

		$endDate = $fyear."-".$fmonth;


		$arry5plus=array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
		$finalSales=implode(",", $arry5plus);

		if($range_val == 2)
		{
			$arryRange=array(0,1,2,3);
			$finalSales=implode(",", $arryRange);
		}
		elseif($range_val == 3)
		{
			$arryRange=array(4,5,6);
			$finalSales=implode(",", $arryRange);
		}
		elseif($range_val == 4)
		{
			$arryRange=array(7,8,9,10);
			$finalSales=implode(",", $arryRange);
		}
		elseif($range_val == 5)
		{
			$arryRange=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);
			$finalSales=implode(",", $arryRange);
		}
		else
		{
			$finalSales=$finalSales;
		}



		$tl_TeamLists = MasterProcessorList::where('processor',$processor)->get();

		//$sumdata = array();
		$sumdata = 0;
		$processorTeam = array();
		foreach($tl_TeamLists as $_tlteam)
		{
			$processorTeam[] = $_tlteam->emp_name;
			//echo $_tlteam->emp_name; echo "<br/>";echo "<br/>";
			// $a1= self::getTeamProductivity($_tlteam->emp_name,$widgetId,$fromDate,$toDate,$range_val);
			// //echo $a1;echo "<br/>";echo "<br/>";//echo $fromDate;echo "<br/>";
			// // $sumdata[] = $a1;
			// $sumdata+= $a1;
		}


		
		if($startDate == $currentdate && $endDate == $currentdate)
		{
			// return "Current Month";
			// $team="Ram";

			$whereraw = 'range_id IN ('.$finalSales.')';
			//echo $whereraw;exit;
			$salestime=date("Y-m", strtotime($fromDate));
			//echo $salestime;
			$term = "%".$salestime."%";
			$totalmasterdata=DepartmentFormEntry::whereRaw($whereraw)->where("submission_date",'like',$term)->whereIn('team', $processorTeam)->get();

			if(count($totalmasterdata)>0)
			{
				// New code
				//$tl_details = Employee_details::where("sales_name",$team)->get();
				$tl_details = Employee_details::whereIn('sales_name', $processorTeam)->where("dept_id",36)->where('offline_status',1)->get();
				//return $tl_details;
				$tlidsarr = array();
				$tl_id = "";
				foreach($tl_details as $tldata)
				{
					if($tldata->id!=''|| $tldata->id!=NULL)
					{
						// $tl_id = $tldata->id;
						// break;
						$tlidsarr[]=$tldata->id;
					}
					else
					{
						$tlidsarr[]="";
					}
				}




				// print_r($tlidsarr);
				// return "Hello";



				if(count($tlidsarr) === 0) 
				{
					// list is empty.
					$totalemp = 0;
			   	}
				else
				{
					if($range_val!='')
					{
						$totalemp = Employee_details::whereRaw($whereraw)->whereIn('tl_id', $tlidsarr)->where("dept_id",36)->where("offline_status",1)->count();
					}
					else
					{
						$totalemp = Employee_details::whereIn('tl_id', $tlidsarr)->where("dept_id",36)->where("offline_status",1)->count();
					}
				}

				//return $totalemp;

				// if($tl_id!='')
				// {
				// 	if($range_val!='')
				// 	{
				// 		$totalemp = Employee_details::whereRaw($whereraw)->where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
				// 	}
				// 	else
				// 	{
				// 		$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
				// 	}
					
				// }
				// else
				// {
				// 	$totalemp = 0;
				// }
				// New code

				//$countdata=DepartmentFormEntry::whereRaw($whereraw)->where("submission_date",'like',$term)->where("team",$team)->where('form_status',"Booked")->get()->count();


				if($range_val!='')
				{
					$countdata=DepartmentFormEntry::whereRaw($whereraw)->where("submission_date",'like',$term)->whereIn('team', $processorTeam)->where('form_status',"Booked")->get()->count();
				}
				else
				{
					$countdata=DepartmentFormEntry::where("submission_date",'like',$term)->whereIn('team', $processorTeam)->where('form_status',"Booked")->get()->count();
				}

				//$countdata=DepartmentFormEntry::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');

				//$countd = count();

				if($countdata > 0 && $totalemp > 0)
				{
					//return "Hello".round($countdata/$totalemp);
					return round(ceil($countdata)/$totalemp,2)." (Total: ".$totalemp.")";
				}
				else
				{
					$res=0;
					return $res;
				}
			}
			else
			{
				return 0;
			}
		}
		else
		{
			$whereraw = 'range_id IN ('.$finalSales.')';
			//echo $whereraw;exit;
			$salestime=date("n-Y", strtotime($fromDate));
			//echo $salestime;
			$totalmasterdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->whereIn('tl_name', $processorTeam)->get();

			//return $totalmasterdata;

			

			
			if(count($totalmasterdata)>0)
			{
				// New code
				// $tl_details = Employee_details::where("sales_name",$team)->get();
				// //return $tl_details;
				// $tl_id = "";
				// foreach($tl_details as $tldata)
				// {
				// 	if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
				// 	{
				// 		$tl_id = $tldata->tl_id;
				// 		break;
				// 	}
				// 	else
				// 	{
				// 		continue;
				// 	}
				// }

				// if($tl_id!='')
				// {
					if($range_val!='')
					{
						//$totalemp = Employee_details::whereRaw($whereraw)->where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
						//$totalemp = MasterPayout::whereRaw($whereraw)->where("TL",$team)->where("sales_time",$salestime)->count();
						$totalemp = MasterPayout::whereRaw($whereraw)->where("tl_name",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();


					}
					else
					{
						//$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
						//$totalemp = MasterPayout::where("TL",$team)->where("sales_time",$salestime)->count();
						$totalemp = MasterPayout::where("tl_name",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();


					}

					if($totalemp > 0)
					{
						$totalemp = $totalemp;
					}
					else
					{
						$totalemp = 0;
					}
					
				// }
				// else
				// {
				// 	$totalemp = 0;
				// }
				// New code

				//$countdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');

				if($range_val!='')
				{
					
					$countdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');
				}
				else
				{
					$countdata=MasterPayout::where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');
				}

				if($countdata > 0 && $totalemp > 0)
				{
					//return "Hello".round($countdata/$totalemp);
					return round(ceil($countdata)/$totalemp,2);
				}
				else
				{
					$res=0;
					return $res;
				}
			}
			else
			{
				
				$whereraw = 'range_id IN ('.$finalSales.')';
				//echo $whereraw;exit;
				$salestime=date("n-Y", strtotime($fromDate));
				// New code
				// $tl_details = Employee_details::where("sales_name",$team)->get();
				// //return $tl_details;
				// $tl_id = "";
				// foreach($tl_details as $tldata)
				// {
				// 	if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
				// 	{
				// 		$tl_id = $tldata->tl_id;
				// 		break;
				// 	}
				// 	else
				// 	{
				// 		continue;
				// 	}
				// }

				
					if($range_val!='')
					{
						//$totalemp = Employee_details::whereRaw($whereraw)->where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();

						$totalemp = MasterPayoutPre::whereRaw($whereraw)->whereIn('TL', $processorTeam)->where("dept_id",36)->where("sales_time",$salestime)->count();

					}
					else
					{
						//$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();

						$totalemp = MasterPayoutPre::whereIn('TL', $processorTeam)->where("dept_id",36)->where("sales_time",$salestime)->count();
					}

					if($totalemp > 0)
					{
						$totalemp = $totalemp;
					}
					else
					{
						$totalemp = 0;
					}
					
				
				// New code

				// echo $whereraw;

				// echo $salestime;
				// exit;


				//$countdata=MasterPayoutPre::where("sales_time",$salestime)->where("dept_id",36)->where("TL",$team)->sum('tc');
				if($range_val!='')
				{
					$countdata=MasterPayoutPre::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->whereIn('TL', $processorTeam)->sum('tc');
				}
				else
				{
					$countdata=MasterPayoutPre::where("sales_time",$salestime)->where("dept_id",36)->whereIn('TL', $processorTeam)->sum('tc');
				}
				//return $totalemp;

				if($countdata > 0 && $totalemp > 0)
				{
					//return "Hello".round($countdata/$totalemp);
					return round(ceil($countdata)/$totalemp,2)." (Total: ".$totalemp.")";
				}
				else
				{
					$res=0;
					return $res;
				}
				
				
				
			}

		}
		
	}


	public static function getTotalProductivitybyProcessorDataOLD333($processor,$widgetId,$fromDate,$toDate,$range_val)
	{
		//return "Hello".$toDate;

		//echo 

		

		$tl_TeamLists = MasterProcessorList::where('processor',$processor)->get();

		//$sumdata = array();
		$sumdata = 0;
		foreach($tl_TeamLists as $_tlteam)
		{
			//echo $_tlteam->emp_name; echo "<br/>";echo "<br/>";
			$a1= self::getTeamProductivity($_tlteam->emp_name,$widgetId,$fromDate,$toDate,$range_val);
			//echo $a1;echo "<br/>";echo "<br/>";//echo $fromDate;echo "<br/>";
			// $sumdata[] = $a1;
			$sumdata+= $a1;
		}
		//return $a1;

		//$tsum = array_sum($sumdata);

		return $sumdata;

		
	}







	public static function getTotalProductivitybyProcessor($processor,$widgetId,$fromDate,$toDate,$range_val)
	{
		// return "Hello".$toDate;

		$team1 = "Mujahid";
		$a1= self::getTeamProductivity($team1,$widgetId,$fromDate,$toDate,$range_val);

		$team2 = "Ajay";
		$a2= self::getTeamProductivity($team2,$widgetId,$fromDate,$toDate,$range_val);

		$team3 = "Akshada";
		$a3= self::getTeamProductivity($team3,$widgetId,$fromDate,$toDate,$range_val);

		$team4 = "Shahnawaz";
		$a4= self::getTeamProductivity($team4,$widgetId,$fromDate,$toDate,$range_val);


		

		return $a1+$a2+$a3+$a4;
	}


	public static function getTotalProductivitybyProcessor2($processor,$widgetId,$fromDate,$toDate,$range_val)
	{
		// return "Hello".$toDate;

		$team1 = "Arsalan";
		$a1= self::getTeamProductivity($team1,$widgetId,$fromDate,$toDate,$range_val);

		$team2 = "Zubair";
		$a2= self::getTeamProductivity($team2,$widgetId,$fromDate,$toDate,$range_val);

		return $a1+$a2;
	}


	public static function getTotalProductivitybyProcessor3($processor,$widgetId,$fromDate,$toDate,$range_val)
	{
		// return "Hello".$toDate;

		$team1 = "Mohsin";
		$a1= self::getTeamProductivity($team1,$widgetId,$fromDate,$toDate,$range_val);

		$team2 = "Sahir";
		$a2= self::getTeamProductivity($team2,$widgetId,$fromDate,$toDate,$range_val);

		
		return $a1+$a2;
	}


	public static function getRangeValues($range)
	{
		//print_r($range);
		$frange='';
		foreach($range as $value)
		{
			if($value==1)
			{
				$frange = "All";
			}
			if($value==2)
			{
				$frange = "0,1,2,3";
			}
			if($value==3)
			{
				$frange = "4,5,6";
			}
			if($value==4)
			{
				$frange = "7,8,9,10";
			}
			if($value==5)
			{
				$frange = "11,12,13...+++";
			}
		}

		return $frange;

	}






	// New Code Start (9-10-2024)

	public static function getHeadCountProductivity($team,$widget,$range,$datatype)
	{
		echo $datatype = Request::session()->get('widgetFilterHiring['.$widget.'][data_type]');

		return "Hello".$datatype;



	}


	public static function getHeadCountTotal($team,$widget,$range_val,$datatype)
	{
		$datatype = Request::session()->get('widgetFilterHiring['.$widget.'][data_type]');

		// $whereraw = 'range_id IN ('.$finalSales.')';
		// 	//echo $whereraw;exit;
		// $salestime=date("n-Y", strtotime($fromDate));


		if($datatype == 'current_month')
		{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			
		}
		elseif($datatype == 'last_month')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-30 days"));
			$fromDate = date("Y-m-01",strtotime("-1 Months")); 
		}
		elseif($datatype == 'month_3')
		{
			$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			$fromDate = date("Y-m-01",strtotime("-2 Months")); 
		}




		if($datatype=='' || $datatype == 'current_month')
		{
			// New code
			$tl_details = Employee_details::where("sales_name",$team)->where("dept_id",36)->where('offline_status',1)->get();
			//return $tl_details;
			$tl_id = "";
			foreach($tl_details as $tldata)
			{
				if($tldata->id!=''|| $tldata->id!=NULL)
				{
					$tl_id = $tldata->id;
					break;
				}
				else
				{
					continue;
				}
			}

			if($tl_id!='')
			{
				if($range_val!='')
				{
					$totalemp = Employee_details::whereRaw($whereraw)->where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
				}
				else
				{
					$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
				}
				
			}
			else
			{
				$totalemp = 0;
			}

			return $totalemp;
		}
		else
		{
			//$whereraw = 'range_id IN ('.$finalSales.')';
			//echo $whereraw;exit;
			$salestime=date("n-Y", strtotime($fromDate));
			//echo $salestime;
			$totalmasterdata=MasterPayout::where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->get();

			//return $totalmasterdata;

			

			
			if(count($totalmasterdata)>0)
			{
				// New code
				// $tl_details = Employee_details::where("sales_name",$team)->get();
				// //return $tl_details;
				// $tl_id = "";
				// foreach($tl_details as $tldata)
				// {
				// 	if($tldata->tl_id!=''|| $tldata->tl_id!=NULL)
				// 	{
				// 		$tl_id = $tldata->tl_id;
				// 		break;
				// 	}
				// 	else
				// 	{
				// 		continue;
				// 	}
				// }

				// if($tl_id!='')
				// {
					if($range_val!='')
					{
						//$totalemp = Employee_details::whereRaw($whereraw)->where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
						//$totalemp = MasterPayout::whereRaw($whereraw)->where("TL",$team)->where("sales_time",$salestime)->count();
						$totalemp = MasterPayout::whereRaw($whereraw)->where("tl_name",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();


					}
					else
					{
						//$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();
						//$totalemp = MasterPayout::where("TL",$team)->where("sales_time",$salestime)->count();
						$totalemp = MasterPayout::where("tl_name",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();


					}

					if($totalemp > 0)
					{
						$totalemp = $totalemp;
					}
					else
					{
						$totalemp = 0;
					}
					
				// }
				// else
				// {
				// 	$totalemp = 0;
				// }
				// New code

				

				if($range_val!='')
				{
					
					$countdata=MasterPayout::whereRaw($whereraw)->where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');
				}
				else
				{
					$countdata=MasterPayout::where("sales_time",$salestime)->where("dept_id",36)->where("tl_name",$team)->sum('cards_mashreq');
				}

				if($countdata > 0 && $totalemp > 0)
				{
					//return "Hello".round($countdata/$totalemp);
					return round(ceil($countdata)/$totalemp,2)." (Total: ".$totalemp.")";
				}
				else
				{
					$res=0;
					return $res;
				}
			}
			else
			{
				
				//$whereraw = 'range_id IN ('.$finalSales.')';
				//echo $whereraw;exit;
				$salestime=date("n-Y", strtotime($fromDate));
				

				
					if($range_val!='')
					{
						//$totalemp = Employee_details::whereRaw($whereraw)->where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();

						$totalemp = MasterPayoutPre::where("TL",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();

					}
					else
					{
						//$totalemp = Employee_details::where("tl_id",$tl_id)->where("dept_id",36)->where("offline_status",1)->count();

						$totalemp = MasterPayoutPre::where("TL",$team)->where("dept_id",36)->where("sales_time",$salestime)->count();
					}

					if($totalemp > 0)
					{
						$totalemp = $totalemp;
					}
					else
					{
						$totalemp = 0;
					}

					return $totalemp."hghjghjgg";
					
				
				// New code

				
				
				
				
			}

		}

		


		



	}


	

	// New Code End (9-10-2024)


}