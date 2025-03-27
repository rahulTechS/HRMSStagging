<?php

namespace App\View\Components\TestRavi;
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

use App\Models\ENBDLoanMIS\ENBDLoanMIS;
use App\Models\ENBDLoanMIS\ENBDLoanMISAECB;

class EnbdInternalData extends Component
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
	
    public function __construct($widgetId)
    {
		
        $widget_name = WidgetCreation::where("id",$widgetId)->first()->widget_name;
	   //$widgetData = WidgetBarMol::where("widget_id",$widgetId)->first();
	  
	   $whereraw = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');

		if(Request::session()->get('widgetFilterBYTeam['.$widgetId.']')!='' && Request::session()->get('widgetFilterBYTeam['.$widgetId.']')=="BYTeam"){
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
				$whereraw = "date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			if($whereraw == '')
			{
				$whereraw = "date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
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
		$totaldata= ENBDLoanMIS::groupBy('team')->selectRaw('count(*) as total, team')->whereRaw($whereraw)->get();
		
		}
			
		else
		{
			
		$totaldata= ENBDLoanMIS::groupBy('team')->selectRaw('count(*) as total, team')->whereRaw($whereraw)->get();

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
			
			
			$dataval=$this->getTeamdata($_totaldata->team,$widgetId);
			if(isset($dataval['data'])){
			$weekarray[$_totaldata->team]['data']=$dataval['data'];
			}
			$weekarray[$_totaldata->team]['type']='line';
			$weekarray[$_totaldata->team]['name']=$_totaldata->team;
			$lagend[]=$_totaldata->team;
			
			if(isset($dataval['name'])){
			$noofweek=$dataval['name'];
			}else{
				$noofweek=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
			}
			
			
			
			
		}
		//echo "<pre>";
		//print_r($weekarray);exit;
		
		
		
		
		$finalarray=array();
		foreach($weekarray as $dataval){
		$finalarray[]=$dataval;
		}
		//echo "<pre>";
	//print_r($finalarray);	
   $finaldata= json_encode($finalarray);



		$noofweek=array_unique($noofweek);
		//print_r($noofweek);exit;
		$graphdata= $finaldata;
			
		}
		else if(Request::session()->get('widgetFilterBYProcessor['.$widgetId.']')!='' && Request::session()->get('widgetFilterBYProcessor['.$widgetId.']')=="ByProcessor"){
		
		
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team_Mahwish_130 = array('Ajay','Mujahid','Akshada','Shahnawaz');
			$team_Umar_168 = array('Arsalan','Zubair');
			$team_Arsalan_129 = array('Mohsin','Sahir');
			$sales_processor_internalarray =  Request::session()->get('widgetFilterprocessor['.$widgetId.']');
			
			//$team=$sales_processor_internalarray;
			$team=explode(",",$sales_processor_internalarray);
			
			//print_r($sales_processor_internal);
			
			//print_r($team);exit;
			$teamfinalarray=array();
			 foreach($team as $teamarray){
				 $teamfinalarray[]="'".$teamarray."'";
				 
				 
			 }
			$teamfinal=implode(",",$teamfinalarray);
			
		}
		else{
			$team=array('Mahwish','Umar','Arsalan');
			
		}
		
		$graphArray = array();
		$range=array();
		$weekarray = array();
		$noofweek=array();
		$countvalue=0;
		$bookingAsDateCompime=array();
		///echo date('t');
		//print_r($team);exit;
		foreach($team as $_totaldata)
		{
			$dataval=$this->getProcessordata($_totaldata,$widgetId);
			if(isset($dataval['data'])){
			$weekarray[$_totaldata]['data']=$dataval['data'];
			}
			else{
				$weekarray[$_totaldata]['data']=0;
			}
			$weekarray[$_totaldata]['type']='line';
			$weekarray[$_totaldata]['name']=$_totaldata;
			$lagend[]=$_totaldata;
			if(isset($dataval['name'])){
			$noofweek=$dataval['name'];
			}else{
				$noofweek=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
			}
			
			
			
		}
		
		
		$finalarray=array();
		foreach($weekarray as $dataval){
		$finalarray[]=$dataval;
		}
		//echo "<pre>";
	//print_r($finalarray);	
  $finaldata= json_encode($finalarray);



		$noofweek=array_unique($noofweek);
		//print_r(json_encode($noofweek));exit;
		$graphdata= $finaldata;	
		}
		else{
		
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
				$whereraw = "date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			if($whereraw == '')
			{
				$whereraw = "date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
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
		$totaldata= ENBDLoanMIS::groupBy('date_of_submission')->selectRaw('count(*) as total, date_of_submission')->whereRaw($whereraw)->get();
		
		}
			
		else
		{
			
		$totaldata= ENBDLoanMIS::groupBy('date_of_submission')->selectRaw('count(*) as total, date_of_submission')->whereRaw($whereraw)->get();

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
			$date=explode("-",$_totaldata->date_of_submission);
			$datezero=date('m-j', strtotime($_totaldata->date_of_submission));
			$monthlevel=date('M', strtotime($_totaldata->date_of_submission));//exit;
			$month=date('Y-m', strtotime($_totaldata->date_of_submission));
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
		//print_r($weekarray);
		for ($j =1; $j <= 31; $j++){
		$noofweek[]=$j;
		}
		
		
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
				$finalweekarray[]=$valuedata;
			//echo $yearexp[1].'-'.$value."<br>";
			}
			
			$graph[$yearexp[1]]['data']=$finalweekarray;
			$graph[$yearexp[1]]['type']=$_datavalue['type'];
			$graph[$yearexp[1]]['name']=$_datavalue['name'];
			$lagend[]=$_datavalue['name'];
		}
		//print_r($lagend);
		//echo "<pre>";
		//print_r($graph);exit;
		$finalarray=array();
		foreach($graph as $dataval){
		$finalarray[]=$dataval;
		}
		//echo "<pre>";
	//print_r($finalarray);	
  $finaldata= json_encode($finalarray);



		$noofweek=array_unique($noofweek);
		//print_r($noofweek);exit;
		$graphdata= $finaldata;
		
		}
	   
	   
	   
	   
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
		$TeamLists = ENBDLoanMIS::groupBy('team')->selectRaw('count(*) as total, team')->get();
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
		$this->WeekList=$noofweek;
		$this->lagendData=$lagend;
		//$this->processorSelecteddata = $processorSelected;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.TestRavi.enbd-internal-data');
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
				$name = ENBDLoanMIS::where("team",$r)->first()->team;
			}
			else
			{
				$name = $name.','.ENBDLoanMIS::where("team",$r)->first()->team;
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
				$whereraw = "date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			if($whereraw == '')
			{
				$whereraw = "date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
			}
		}
	
		//echo $whereraw;exit;
		if($whereraw != '')
		{
		$totaldata= ENBDLoanMIS::groupBy('date_of_submission')->selectRaw('count(*) as total, date_of_submission')->whereRaw($whereraw)->where("team",$team)->get();
		
		}
			
		else
		{
			
		$totaldata= ENBDLoanMIS::groupBy('date_of_submission')->selectRaw('count(*) as total, date_of_submission')->whereRaw($whereraw)->where("team",$team)->get();

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
			$date=explode("-",$_totaldata->date_of_submission);
			$datezero=date('m-j', strtotime($_totaldata->date_of_submission));
			$monthlevel=date('M', strtotime($_totaldata->date_of_submission));//exit;
			$month=date('Y-m', strtotime($_totaldata->date_of_submission));
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
				$whereraw = "date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			//$fromDate = date("Y-m-d",strtotime("-90 days"));
			if($whereraw == '')
			{
				$whereraw = "date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And date_of_submission >= '".$fromDate."' and date_of_submission <= '".$toDate."'";
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
		$totaldata= ENBDLoanMIS::groupBy('date_of_submission')->selectRaw('count(*) as total, date_of_submission')->whereRaw($whereraw)->get();
		
		}
			
		else
		{
			
		$totaldata= ENBDLoanMIS::groupBy('date_of_submission')->selectRaw('count(*) as total, date_of_submission')->whereRaw($whereraw)->get();

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
			$date=explode("-",$_totaldata->date_of_submission);
			$datezero=date('m-j', strtotime($_totaldata->date_of_submission));
			$monthlevel=date('M', strtotime($_totaldata->date_of_submission));//exit;
			$month=date('Y-m', strtotime($_totaldata->date_of_submission));
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
	
}
