<?php

namespace App\View\Components\SalesManagement;
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
use App\Models\Employee\Employee_attribute;
use Session;
//use App\Models\Common\MashreqLoginMIS;
//use App\Models\Common\MashreqBankMIS;
//use App\Models\Common\MashreqBookingMIS;
//use App\Models\Common\MashreqMTDMIS;
use App\Models\Attribute\DepartmentFormEntry;

use App\Models\Bank\EIB\EibBankMis;
use App\Models\Attribute\EIBDepartmentFormEntry;
use App\Models\Attribute\EIBDepartmentFormChildEntry;
use App\User;
use App\Models\Dashboard\MasterPayout;
use App\Models\EmpOffline\EmpOffline;









class EibRecommendationsSales extends Component
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
	public $TeamListsSelectedRecommend;
	public $processorSelecteddata;
	
	
    public function __construct($widgetId)
    {
		
        $widget_name = WidgetCreation::where("id",$widgetId)->first()->widget_name;
	   //$widgetData = WidgetBarMol::where("widget_id",$widgetId)->first();


	//    $this->loggedinuser = Request::session()->get('EmployeeId');
	// 	$userData= User::where('id',$this->loggedinuser)->first();
	// 	$empData= Employee_details::where('emp_id',$userData->employee_id)->where('job_function',3)->first();
	  
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
		if(Request::session()->get('widgetFiltermolTeamSalesRecommend['.$widgetId.']') != '' && Request::session()->get('widgetFiltermolTeamSalesRecommend['.$widgetId.']') != NULL )
		{
			$deptIds =  Request::session()->get('widgetFiltermolTeamSalesRecommend['.$widgetId.']');
			
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
		
		if(Request::session()->get('widgetFilterprocessorSalesRecommend['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessorSalesRecommend['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Akshada','Shahnawaz');
			$team_Umar_168 = array('Arsalan','Zubair');
			$team_Arsalan_129 = array('Mohsin','Sahir');
			$sales_processor_internalarray =  Request::session()->get('widgetFilterprocessorSalesRecommend['.$widgetId.']');
			
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
			$totalempdata= Employee_details::where('tl_id',$empData->id)->where('dept_id',52)->get();
		}
		else{
			$totalempdata= Employee_details::where('dept_id',52)->get();
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
			$totaldata= EibBankMis::whereIn("emp_id",$finalemp)->whereRaw($whereraw)->groupBy('emp_id')->get();

			
		
		}
			
		else
		{
			
			$totaldata= EibBankMis::whereIn("emp_id",$finalemp)->whereRaw($whereraw)->groupBy('emp_id')->get();

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
		if(Request::session()->get('widgetFiltermolTeamSalesRecommend['.$widgetId.']') != '' && Request::session()->get('widgetFiltermolTeamSalesRecommend['.$widgetId.']') != NULL)	
		{
			$this->TeamListsSelectedRecommend = explode(",",Request::session()->get('widgetFiltermolTeamSalesRecommend['.$widgetId.']'));
		}
		else
		{
			$this->TeamListsSelectedRecommend ='';
		}
		if(Request::session()->get('widgetFilterprocessorSalesRecommend['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessorSalesRecommend['.$widgetId.']') != NULL)	
		{
			$this->processorSelecteddata = Request::session()->get('widgetFilterprocessorSalesRecommend['.$widgetId.']');
		}
		else
		{
			$this->processorSelecteddata ='';
		}
		$TeamLists = EibBankMis::groupBy('tl_name')->selectRaw('count(*) as total, tl_name')->whereNotNull('tl_name')->get();
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
        
		return view('components.SalesManagement.eibrecommendationsales');
		
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
				$name = EibBankMis::where("tl_name",$r)->first()->tl_name;
			}
			else
			{
				$name = $name.','.EibBankMis::where("tl_name",$r)->first()->tl_name;
			}
		}
		return $name;
	}
	
Private function getTeamdata($team,$widgetId)
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
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			if($whereraw == '')
			{
				$whereraw = "dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			if($whereraw == '')
			{
				$whereraw = "dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
			}
		}
	
		//echo $whereraw;exit;
		if($whereraw != '')
		{
		$totaldata= MashreqBookingMIS::groupBy('dateofdisbursal')->selectRaw('count(*) as total, dateofdisbursal')->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();
		
		}
			
		else
		{
			
		$totaldata= MashreqBookingMIS::groupBy('dateofdisbursal')->selectRaw('count(*) as total, dateofdisbursal')->where("form_id",1)->whereRaw($whereraw)->where("team",$team)->get();

		}
		
		$graphArray = array();
		$range=array();
		$weekarray = array();
		$noofweek=array();
		$countvalue=0;
		$bookingAsDateCompime=array();
		///echo date('t');
		foreach($totaldata as $_totaldata)
		{
			
			//print_r($_totaldata);exit;
			$date=explode("-",$_totaldata->dateofdisbursal);
			$datezero=date('m-j', strtotime($_totaldata->dateofdisbursal));
			$monthlevel=date('M', strtotime($_totaldata->dateofdisbursal));//exit;
			$month=date('Y-m', strtotime($_totaldata->dateofdisbursal));
			//$bookingAsDateCompime[$month]=0;
			if(isset($bookingAsDateCompime[$month]))
				{
				$bookingAsDateCompime[$month]  = $bookingAsDateCompime[$month]+$_totaldata->total;
				}
				else

				{
				$bookingAsDateCompime[$month] = $_totaldata->total;;
				}
			$weekarray[$month]['data'][$datezero]=$bookingAsDateCompime[$month];
			$weekarray[$month]['type']='line';
			$weekarray[$month]['name']=$monthlevel;
			
			
			
			
		}
		//echo "<pre>";
		//print_r($weekarray);//exit;
		$frommonth=date('Y-m', strtotime($fromDate));
		$tomonth=date('Y-m', strtotime($toDate));
		if($frommonth==$tomonth){
		$countvalue=0;
		$graph=array();
		$nenwarray=array();
		$lagend=array();
		foreach($weekarray as $key=>$_datavalue){
			//echo $key;exit;
			//print_r($_datavalue['data']);//exit;
			$yearexp=explode("-",$key);
			//echo date("Y-m");
			//echo $yearexp[0]."-".$yearexp[1];exit;
			if(date("Y-m")==$yearexp[0]."-".$yearexp[1]){
				$start_date = $yearexp[0]."-".$yearexp[1]."-01";
				$end_date   = date("Y-m-d");
				$dateDiff   = strtotime($end_date) - strtotime($start_date);
				$numOfDays  = $dateDiff / 86400;
				$numberday = $numOfDays;//exit;
			}else{
			$numberday = date('t', mktime(0, 0, 0, $yearexp[1], 1, $yearexp[0]));
			}
			//echo $numberday;
			$daysarray=range(1,$numberday);
			//print_r($daysarray);//e$daysarrayxit;
			$myvalue = 0;
			$finalweekarray=array();
			foreach($daysarray as $value){
				//print_r($_datavalue['data']);exit;
				//echo $_datavalue['data'][$yearexp[1].'-'.$value];exit;'echo 
				
				if(isset($_datavalue['data'][$yearexp[1].'-'.$value])==''){
				$valuedata = $myvalue;
				}
				else{
					$valuedata=$_datavalue['data'][$yearexp[1].'-'.$value];
					$myvalue=$valuedata;
				}
				$finalweekarray['data'][]=$valuedata;
			//echo $yearexp[1].'-'.$value."<br>";
			}
			
			$graph=$finalweekarray;
			
		}
		return $graph;
		}
		else{
			$graph=array();
		$nenwarray=array();
		$lagend=array();
		$finalweekarray=array();
			$countvalue=0;
			//print_r($weekarray);
		foreach($weekarray as $key=>$_datavalue){
			 
			
			
				 $countvalue= end($_datavalue['data'])+$countvalue;
					$finalweekarray['data'][] =$countvalue;
					//$finalweekarray['type'][] =$_datavalue['type'];
					$finalweekarray['name'][] =$_datavalue['name'];
			}
			
			
			
		return $finalweekarray;
			
		}
	}

Private function getProcessordata($processor,$widgetId)
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
				//$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-1 month'));
			//$fromDate = $m.'-'.'01';
			}
			elseif($datatype == 'month_3')
			{
				//echo "hello";exit;
				$toDate = date("Y-m-d");
				$m= date("Y-m", strtotime('-3 month'));
				$fromDate = $m.'-'.'01';
			}
			else{
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				
			}
			//echo $fromDate;exit;
			if($whereraw == '')
			{
				$whereraw = "dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			if($whereraw == '')
			{
				$whereraw = "dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And dateofdisbursal >= '".$fromDate."' and dateofdisbursal <= '".$toDate."'";
			}
		}
		if($processor!='')
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Akshada','Shahnawaz');
			$team_Umar_168 = array('Arsalan','Zubair');
			$team_Arsalan_129 = array('Mohsin','Sahir');
			$sales_processor_internalarray =  $processor;
			
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
		if($whereraw != '')
		{
		$totaldata= MashreqBookingMIS::groupBy('dateofdisbursal')->selectRaw('count(*) as total, dateofdisbursal')->where("form_id",1)->whereRaw($whereraw)->get();
		
		}
			
		else
		{
			
		$totaldata= MashreqBookingMIS::groupBy('dateofdisbursal')->selectRaw('count(*) as total, dateofdisbursal')->where("form_id",1)->whereRaw($whereraw)->get();

		}
		//print_r($totaldata);
		$graphArray = array();
		$range=array();
		$weekarray = array();
		$noofweek=array();
		$countvalue=0;
		$bookingAsDateCompime=array();
		///echo date('t');
		foreach($totaldata as $_totaldata)
		{
			
			//print_r($_totaldata);exit;
			$date=explode("-",$_totaldata->dateofdisbursal);
			$datezero=date('m-j', strtotime($_totaldata->dateofdisbursal));
			$monthlevel=date('M', strtotime($_totaldata->dateofdisbursal));//exit;
			$month=date('Y-m', strtotime($_totaldata->dateofdisbursal));
			//$bookingAsDateCompime[$month]=0;
			if(isset($bookingAsDateCompime[$month]))
				{
				$bookingAsDateCompime[$month]  = $bookingAsDateCompime[$month]+$_totaldata->total;
				}
				else

				{
				$bookingAsDateCompime[$month] = $_totaldata->total;;
				}
			$weekarray[$month]['data'][$datezero]=$bookingAsDateCompime[$month];
			$weekarray[$month]['type']='line';
			$weekarray[$month]['name']=$monthlevel;
			
			
			
			
		}
		
		$frommonth=date('Y-m', strtotime($fromDate));
		$tomonth=date('Y-m', strtotime($toDate));
		if($frommonth==$tomonth){
		//print_r($weekarray);exit;
		$countvalue=0;
		$graph=array();
		$nenwarray=array();
		$lagend=array();
		foreach($weekarray as $key=>$_datavalue){
			//echo $key;exit;
			//print_r($_datavalue['data']);//exit;
			$yearexp=explode("-",$key);
			//echo date("Y-m");
			//echo $yearexp[0]."-".$yearexp[1];exit;
			if(date("Y-m")==$yearexp[0]."-".$yearexp[1]){
				$start_date = $yearexp[0]."-".$yearexp[1]."-01";
				$end_date   = date("Y-m-d");
				$dateDiff   = strtotime($end_date) - strtotime($start_date);
				$numOfDays  = $dateDiff / 86400;
				$numberday = $numOfDays;//exit;
			}else{
			$numberday = date('t', mktime(0, 0, 0, $yearexp[1], 1, $yearexp[0]));
			}
			//echo $numberday;
			$daysarray=range(1,$numberday);
			//print_r($daysarray);//e$daysarrayxit;
			$myvalue = 0;
			$finalweekarray=array();
			foreach($daysarray as $value){
				//print_r($_datavalue['data']);exit;
				//echo $_datavalue['data'][$yearexp[1].'-'.$value];exit;'echo 
				
				if(isset($_datavalue['data'][$yearexp[1].'-'.$value])==''){
				$valuedata = $myvalue;
				}
				else{
					$valuedata=$_datavalue['data'][$yearexp[1].'-'.$value];
					$myvalue=$valuedata;
				}
				$finalweekarray['data'][]=$valuedata;
				//$finalweekarray['name'][]='';
			//echo $yearexp[1].'-'.$value."<br>";
			}
			
			$graph=$finalweekarray;
			
		}
		return $graph;
		}else{
		
		$graph=array();
		$nenwarray=array();
		$lagend=array();
		$finalweekarray=array();
			$countvalue=0;
			//print_r($weekarray);
		foreach($weekarray as $key=>$_datavalue){
			 
			
			
				 $countvalue= end($_datavalue['data'])+$countvalue;
					$finalweekarray['data'][] =$countvalue;
					//$finalweekarray['type'][] =$_datavalue['type'];
					$finalweekarray['name'][] =$_datavalue['name'];
			}
			
			
			
		return $finalweekarray;
		//return $noofweek;
		}
		
		
	}	
// public static function getTarget($emp_id,$widgetId){
	
		
// 		$empTargetdata= MasterPayout::where('employee_id', $emp_id)->orderBy("id","DESC")->first();
		
		
// 		if($empTargetdata!='')
// 		{
// 			return $empTargetdata->agent_target;
// 		}
// 		else
// 		{
// 			return 'NA';
// 		}
	
// }
public static function getVisaStatus($emp_id){
	$empDetails = Employee_details::where("emp_id",$emp_id)->orderBy('id','desc')->first();

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















public static function getSourceCode($empid)
{
	$empSourcedata= Employee_details::where('emp_id',$empid)->first();

	if($empSourcedata)
	{
		return $empSourcedata->source_code;
	}
	else
	{
		return 'NA';
	}
}









	public static function getTarget($empid,$fromfilterdate,$month,$tofilterdate)
	{
		//return $empid;
		if($month == 'current_month')
		{
			$toDate = date("n-Y");
			$fromDate = date("n").'-'.date("Y");

			$year = explode("-",$toDate);
		}
		elseif($month == 'last_month')
		{
			$fromDate= date('n-Y', strtotime('first day of last month'));
			$toDate= date('n-Y', strtotime('last day of last month'));

			$year = explode("-",$toDate);
		}
		elseif($month == 'month_3')
		{
			$toDate = date("n-Y");
			$fromDate = date("n-Y",strtotime("-90 days"));

			$year = explode("-",$toDate);

		}
		elseif($month == 'custom')
		{
			$to = strtotime($tofilterdate);
			$toDate = date("n-Y", $to);

			$from = strtotime($fromfilterdate);
			$fromDate = date("n-Y", $from);

			$year = explode("-",$toDate);
		}
		else
		{
			$toDate = date("n-Y");
			$fromDate = date("n").'-'.date("Y");
			$year = explode("-",$toDate);
		}
		
		$whererawdefault = "sales_time >= '".$fromDate."' and sales_time <= '".$toDate."'";
		
		
		
		$empTargetdata= MasterPayout::whereRaw($whererawdefault)->where('year',$year[1])->where('employee_id', $empid)->orderBy("id","DESC")->get();
		$target = $empTargetdata->sum('agent_target');

		//return $target;

		//return ($empTargetdata);


		if($empTargetdata)
		{
			return $target;
		}
		else
		{
			return 'NA';
		}
	}

	public static function getDoJ($empid)
	{
		$empdojdata= Employee_details::where('emp_id', $empid)->orderBy("id","DESC")->first();

		if($empdojdata)
		{
			if($empdojdata->doj==NULL)
			{
				return "NA";
			}
			else
			{
				$dateofjoin = date("d M, Y", strtotime($empdojdata->doj));
				return $dateofjoin;
			}
			
			
		}
		else
		{
			return 'NA';
		}
	}


	public static function getBookingCount($empid,$fromfilterdate,$month,$tofilterdate)
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
		
		$whererawdefault = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
		
		$empbookingdata= EibBankMis::whereRaw($whererawdefault)->where('emp_id', $empid)->where('final_decision','Approve')->orderBy("id","DESC")->get()->count();		
		return $empbookingdata;
		
	}


	public static function getPreviousBookingCount($empid,$fromfilterdate,$month,$tofilterdate)
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
			//$fromDate= date('Y-m-d', strtotime('first day of last month'));
			//$toDate= date('Y-m-d', strtotime('last day of last month'));
			
			$fromDate= date('Y-m-d', strtotime('first day of last month'));
			//$toDate= date('Y-m-d', strtotime('last day of last month'));
			$toDate = date("Y-m-d");
			$toDate= date("Y-m-d", strtotime('-1 month'));
			//$fromDate = $m.'-'.'01';
			
		}
		
		$whererawdefault = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
		
		$empbookingdata= EibBankMis::whereRaw($whererawdefault)->where('emp_id', $empid)->where('final_decision','Approve')->orderBy("id","DESC")->get()->count();		
		return $empbookingdata;
		
	}



	public static function getSecondPreviousBookingCount($empid,$fromfilterdate,$month,$tofilterdate)
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
			//$toDate = date("Y-m-d");
			//$fromDate = date("Y-m-d",strtotime("-60 days"));
			
			//$toDate = date("Y-m-d");
			$from = date("Y-m",strtotime("-60 days"));
			$fromDate=$from."-01";
			$toDate = date("Y-m-d",strtotime("-60 days"));
			
		}
		
		$whererawdefault = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";
		
		$empbookingdata= EibBankMis::whereRaw($whererawdefault)->where('emp_id', $empid)->where('final_decision','Approve')->orderBy("id","DESC")->get()->count();		
		return $empbookingdata;
		
	}



	public static function getVintageData($empid)
	   {
		  
			//echo $empid;exit;
			$offboarddata=EmpOffline::where("emp_id",$empid)->first();
			if($offboarddata!=''){
			$lasworkingday='';
			if($offboarddata->last_working_day_resign!='' && $offboarddata->last_working_day_resign!=NULL){
				$lasworkingday=date("Y-m-d",strtotime($offboarddata->last_working_day_resign));
			}
			else if($offboarddata->last_working_day_terminate!='' && $offboarddata->last_working_day_terminate!=NULL){
				$lasworkingday=date("Y-m-d",strtotime($offboarddata->last_working_day_terminate));
			}
			else{
				$lasworkingday=date("Y-m-d",strtotime($offboarddata->created_at));
			}
			
	
				$empdat=Employee_details::where("emp_id",$empid)->first();		 
				if($empdat!='' && $empdat->doj!=''){
				 $doj=date("Y-m-d",strtotime(str_replace("/","-",$empdat->doj)));
				  }else{
					$doj='';  
				  }		
				
				if($doj !='' && $lasworkingday!=''){
					$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  $lasworkingday;

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
				else{
					return "--";
				}
   
	   }








}
