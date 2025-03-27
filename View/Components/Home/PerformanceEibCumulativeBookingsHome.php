<?php

namespace App\View\Components\Home;
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

class PerformanceEibCumulativeBookingsHome extends Component
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
		
		if($whereraw != '')
		{
		$totaldata= EibBankMis::groupBy('tl_name')->selectRaw('count(*) as total, tl_name')->where("matched_status",1)->where("final_decision","Approve")->whereRaw($whereraw)->get();
		
		}
			
		else
		{
			
		$totaldata= EibBankMis::groupBy('tl_name')->selectRaw('count(*) as total, tl_name')->where("matched_status",1)->where("final_decision","Approve")->whereRaw($whereraw)->get();

		}

		
		
		
		
		
		
		$graphArray = array();
		$range=array();
		$weekarray = array();
		$noofweek=array();
		$noofmonth=array();
		$bookingAsDateCompime=array();
		
		foreach($totaldata as $_totaldata)
		{
			$dataval=$this->getTeamdata($_totaldata->tl_name,$widgetId);
			//print_r($dataval);
			//exit;
			if(isset($dataval['data'])){
			$weekarray[$_totaldata->tl_name]['data']=$dataval['data'];
			}
			$weekarray[$_totaldata->tl_name]['type']='line';
			$weekarray[$_totaldata->tl_name]['name']=$_totaldata->tl_name;
			$lagend[]=$_totaldata->tl_name;
			
			if(isset($dataval['name'])){
			$noofweek=$dataval['name'];
			}else{
				$noofweek=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
			}
			
		}
		
		
		
		
		$finalweekarray=array();
		$lagend=array();
		foreach($weekarray as $key=>$_datavalue){
		$finalweekarray[]=$_datavalue;
		$lagend[]=$key;
		//$finalweekarray[$key]['type']='line';
		}
		
	$finaldata= json_encode($finalweekarray);//exit;



		
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
			
			$weekarray[$_totaldata]['type']='line';
			$weekarray[$_totaldata]['name']=$_totaldata;
			$lagend[]=$_totaldata;
			if(isset($dataval['name'])){
			$noofweek=$dataval['name'];
			}else{
				$noofweek=array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
			}
			
			
			
		}
		
		
		
		$finalweekarray=array();
		$lagend=array();
		foreach($weekarray as $key=>$_datavalue){
		$finalweekarray[]=$_datavalue;
		$lagend[]=$key;
		//$finalweekarray[$key]['type']='line';
		}
		
	$finaldata= json_encode($finalweekarray);//exit;



		
		$graphdata= $finaldata;	
		}
		else{
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				$fromDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				
				$fromdate1= date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				$todate1= date("Y",strtotime("+1 month ".date("Y-m-d"))).'-'.date("m",strtotime("+1 month ".date("Y-m-d"))).'-'.'05';
				
				$fromdate2='';
				$todate2='';
				
				
				$fromdate3='';
				$todate3='';
				
			}
			elseif($datatype == 'last_month')
			{
				
			
			
				$toDate = date("Y").'-'.date("m").'-'.'05';
				$fromDate = date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				
				$fromdate1= date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$todate1= date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'05';
				
				$fromdate2='';
				$todate2='';
				
				
				$fromdate3='';
				$todate3='';
				
			}
			elseif($datatype == 'month_3')
			{
				$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$toDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$toDate = date("Y-m-d");
				}
				
				$fromdate1= date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';;
				$todate1= date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'05';
				
				$fromdate2=date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$todate2=date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'05';
				
				
				$fromdate3=date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				$todate3=date("Y",strtotime("+1 month ".date("Y-m-d"))).'-'.date("m",strtotime("+1 month ".date("Y-m-d"))).'-'.'05';
				
				
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
			$fromDate = date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';
			$currentDate = date("d",strtotime(date("Y-m-d")));
			
			if($currentDate<=05){
				$toDate = date("Y").'-'.date("m").'-'.'05';
			}
			else{
				$toDate = date("Y-m-d");
			}
			
			$fromdate1= date("Y",strtotime("-2 month ".date("Y-m-d"))).'-'.date("m",strtotime("-2 month ".date("Y-m-d"))).'-'.'06';;
				$todate1= date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'05';
				
				$fromdate2=date("Y",strtotime("-1 month ".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
				$todate2=date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'05';
				
				
				$fromdate3=date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime(date("Y-m-d"))).'-'.'06';
				$todate3=date("Y",strtotime("+1 month ".date("Y-m-d"))).'-'.date("m",strtotime("+1 month ".date("Y-m-d"))).'-'.'05';
			
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
		
		if($whereraw != '')
		{
		$totaldata= EibBankMis::groupBy('application_date')->selectRaw('count(*) as total, application_date')->where("matched_status",1)->where("final_decision","Approve")->whereRaw($whereraw)->get();
		
		}
			
		else
		{
			
		$totaldata= EibBankMis::groupBy('application_date')->selectRaw('count(*) as total, application_date')->where("matched_status",1)->where("final_decision","Approve")->whereRaw($whereraw)->get();

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
			//echo $_totaldata->submission_date;exit;
				
				
			if(strtotime($_totaldata->application_date) >= strtotime($fromdate1) && strtotime($_totaldata->application_date) <= strtotime($todate1))
			{
				$month=date('Y-m', strtotime($fromdate1));
				
				$monthlevel=date('M', strtotime($fromdate1));
				$datezero=date('j', strtotime($_totaldata->application_date));
				$monthArr = explode("-",$month);
				$monthArrV = $monthArr[1];
				
				if(isset($bookingAsDateCompime[$month]))
				{
				$bookingAsDateCompime[$month]  = $bookingAsDateCompime[$month]+$_totaldata->total;
				}
				else

				{
				$bookingAsDateCompime[$month] = $_totaldata->total;;
				}
				
				
			}
			else if(strtotime($_totaldata->application_date) >= strtotime($fromdate2) && strtotime($_totaldata->application_date) <= strtotime($todate2))
			{
				$month=date('Y-m', strtotime($fromdate2));
				$monthlevel=date('M', strtotime($fromdate2));
				$datezero=date('j', strtotime($_totaldata->application_date));
				$monthArr = explode("-",$month);
				$monthArrV = $monthArr[1];
				if(isset($bookingAsDateCompime[$month]))
				{
				$bookingAsDateCompime[$month]  = $bookingAsDateCompime[$month]+$_totaldata->total;
				}
				else

				{
				$bookingAsDateCompime[$month] = $_totaldata->total;;
				}
				
			}
			else if(strtotime($_totaldata->application_date) >= strtotime($fromdate3) && strtotime($_totaldata->application_date) <= strtotime($todate3))
			{
				$month=date('Y-m', strtotime($fromdate3));
				$monthlevel=date('M', strtotime($fromdate3));
				$datezero=date('j', strtotime($_totaldata->application_date));
				$monthArr = explode("-",$month);
				$monthArrV = $monthArr[1];
				if(isset($bookingAsDateCompime[$month]))
				{
				$bookingAsDateCompime[$month]  = $bookingAsDateCompime[$month]+$_totaldata->total;
				}
				else

				{
				$bookingAsDateCompime[$month] = $_totaldata->total;;
				}
				
			}
			
			
			
			//print_r($_totaldata);exit;
			
			
			//$bookingAsDateCompime[$month]=0;
			
				
			$weekarray[$month]['data'][$datezero]=$bookingAsDateCompime[$month];
			$weekarray[$month]['type']='line';
			$weekarray[$month]['name']=date('F', mktime(0, 0, 0, $monthArrV, 10));
			
			
			
			
		}
		/*  echo "<pre>";
		
		 print_r($weekarray);
		exit; */  
		
		for ($j =6; $j <= 31; $j++){
		$noofweek[]=$j;
		}
		
		for ($j =1; $j <= 5; $j++){
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
			$oldCOunt = 0;
			$current_month=date('m', strtotime(date("Y-m-d")));
			$current_month_day=date('d', strtotime(date("Y-m-d")));
			//echo $yearexp[1];exit;
			if($yearexp[1]==$current_month && $current_month_day>=6){
				$irange=$current_month_day;
			}
			else{
				$irange=31;
			}
			//echo $irange;exit;
			$finalweekarray=array();
			for($i=6; $i<=$irange; $i++){
				
			if(isset($_datavalue['data'][$i])){
				$finalweekarray[]=$_datavalue['data'][$i];
				$myvalue = 	$i+1;				
				$oldCOunt = $_datavalue['data'][$i];			
				}
				else{
					$finalweekarray[]=$oldCOunt;
					$myvalue = 	$i+1;				
					$oldCOunt = $oldCOunt;
				}
				
					
				}
				//print_r($finalweekarray);exit;
			   $nextmonth=date("m",strtotime("+1 month ".date("Y-m-d")));
			   $nextmonth_day=date("d",strtotime("+1 month ".date("Y-m-d")));
			   $dd=$key."-01";
			  
			   $nexyearexp=date("m",strtotime("+1 month ",strtotime($dd)));
			   //echo $nexyearexp;exit;
				for($i=1; $i<=5; $i++){
				if($nexyearexp==$nextmonth){
					if($i>=$nextmonth_day){
						if(isset($_datavalue['data'][$i])){
						$finalweekarray[]=$_datavalue['data'][$i];
						$myvalue = 	$i+1;				
						$oldCOunt = $_datavalue['data'][$i];			
						}
						else{
							$finalweekarray[]=$oldCOunt;
							$myvalue = 	$i+1;				
							$oldCOunt = $oldCOunt;
						}
					}
				}
				else{
					if(isset($_datavalue['data'][$i])){
					$finalweekarray[]=$_datavalue['data'][$i];
					$myvalue = 	$i+1;				
					$oldCOunt = $_datavalue['data'][$i];			
					}
					else{
						$finalweekarray[]=$oldCOunt;
						$myvalue = 	$i+1;				
						$oldCOunt = $oldCOunt;
					}
					}
					
					
				}
			
			/* echo "<pre>";
			print_r($finalweekarray);
			exit; */
			
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
		  /* echo "<pre>";
	print_r($finalarray);	
	exit;    */
 $finaldata= json_encode($finalarray);




		
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
        return view('components.Home.performanceeibcumulativebookingshome');
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
				$nameval = EIBDepartmentFormEntry::where("tl_name",$r)->first();
				if($nameval!=''){
					$name=$nameval->tl_name;
				}
				
			}
			else
			{
				$name = $name.','.EIBDepartmentFormEntry::where("tl_name",$r)->first()->tl_name;
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
		
		
		if($whereraw != '')
		{
		$totaldata= EibBankMis::groupBy('application_date')->selectRaw('count(*) as total, application_date')->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->where("matched_status",1)->where("final_decision","Approve")->get();
		
		}
			
		else
		{
			
		$totaldata= EibBankMis::groupBy('application_date')->selectRaw('count(*) as total, application_date')->where("application_no","!=",NULL)->whereRaw($whereraw)->where("tl_name",$team)->where("matched_status",1)->where("final_decision","Approve")->get();

		}
		$graphArray = array();
		$range=array();
		$weekarray = array();
		$noofweek=array();
		$noofmonth=array();
		$bookingAsDateCompime=array();
		
		foreach($totaldata as $_totaldata)
		{
			$date=explode("-",$_totaldata->application_date);
			$month=date('M', strtotime($_totaldata->application_date));//exit;
			//$bookingAsDateCompime[$month]=0;
			if(isset($bookingAsDateCompime[$month]))
				{
				$bookingAsDateCompime[$month]  = $bookingAsDateCompime[$month]+$_totaldata->total;
				}
				else

				{
				$bookingAsDateCompime[$month] = $_totaldata->total;;
				}
			$weekarray[$month]['data'][]=$bookingAsDateCompime[$month];
			
		}
		//echo "<pre>";
		//print_r($weekarray);exit;
		$frommonth=date('Y-m', strtotime($fromDate));
		$tomonth=date('Y-m', strtotime($toDate));
		if($frommonth==$tomonth){
		
		$finalweekarray=array();
		$lagend=array();
		foreach($weekarray as $key=>$_datavalue){
		$finalweekarray=$_datavalue;
		$lagend[]=$key;	
		}
		//print_r($finalweekarray);exit;
		return $finalweekarray;
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
					$finalweekarray['name'][] =$key;
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
		$totaldata= MashreqBookingMIS::groupBy('dateofdisbursal')->selectRaw('count(*) as total, dateofdisbursal')->where("instanceid","!=",NULL)->whereRaw($whereraw)->get();
		
		}
			
		else
		{
			
		$totaldata= MashreqBookingMIS::groupBy('dateofdisbursal')->selectRaw('count(*) as total, dateofdisbursal')->where("instanceid","!=",NULL)->whereRaw($whereraw)->get();

		}
		$graphArray = array();
		$range=array();
		$weekarray = array();
		$noofweek=array();
		$noofmonth=array();
		$bookingAsDateCompime=array();
		
		foreach($totaldata as $_totaldata)
		{
			$date=explode("-",$_totaldata->dateofdisbursal);
			$month=date('M', strtotime($_totaldata->dateofdisbursal));//exit;
			//$bookingAsDateCompime[$month]=0;
			if(isset($bookingAsDateCompime[$month]))
				{
				$bookingAsDateCompime[$month]  = $bookingAsDateCompime[$month]+$_totaldata->total;
				}
				else

				{
				$bookingAsDateCompime[$month] = $_totaldata->total;;
				}
			$weekarray[$month]['data'][]=$bookingAsDateCompime[$month];
			
		}
		//echo "<pre>";
		//print_r($weekarray);exit;
		$frommonth=date('Y-m', strtotime($fromDate));
		$tomonth=date('Y-m', strtotime($toDate));
		if($frommonth==$tomonth){
		
		$finalweekarray=array();
		$lagend=array();
		foreach($weekarray as $key=>$_datavalue){
		$finalweekarray=$_datavalue;
		$lagend[]=$key;	
		}
		//print_r($finalweekarray);exit;
		return $finalweekarray;
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
					$finalweekarray['name'][] =$key;
			}
			
			
			
		return $finalweekarray;
			
		}
		
		
	}	
}
