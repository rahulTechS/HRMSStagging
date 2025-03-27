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
use App\Models\Dashboard\MasterPayout;
use App\Models\Dashboard\MasterPayoutPre;


class TlPerformanceMashreqSpread extends Component
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
		
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Anas','Shahnawaz');
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
				if($sales_processor_internal_value=='Tapash Dahal')
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
		$totaldata= DepartmentFormEntry::select("team")->where("form_id",1)->whereRaw($whereraw)->groupBy('team')->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata= DepartmentFormEntry::select("team")->where("form_id",1)->groupBy('team')->get();	

		}
		$finalarray=array();
		$totalarray=array();
		foreach($totaldata as $_totaldata){
			
			$dataval0to3=$this->getTeamdataGraph0to3($_totaldata->team,$widgetId);
			$dataval4to6=$this->getTeamdataGraph4to6($_totaldata->team,$widgetId);
			$dataval7to10=$this->getTeamdataGraph7to10($_totaldata->team,$widgetId);
			$dataval10plus=$this->getTeamdataGraph10plus($_totaldata->team,$widgetId);
			//$dataval0to3=$this->getTeamdataGraph0to3($_totaldata->team,$widgetId);
			if(isset($dataval0to3["0-3"])){
			$finalarray["0-3"][]=$dataval0to3["0-3"][$_totaldata->team];
			}
			else{
				$finalarray["0-3"][]=0;
			}
			$finalarray['type']["0-3"]='column';
     		$finalarray['name']["0-3"] ="0-3";
			if(isset($dataval4to6["4-6"])){
			$finalarray["4-6"][]=$dataval4to6["4-6"][$_totaldata->team];
			}
			else{
				$finalarray["4-6"][]=0;
			}
			$finalarray['type']["4-6"]='column';
     		$finalarray['name']["0-3"] ="4-6";
			if(isset($dataval7to10["7-10"])){
			$finalarray["7-10"][]=$dataval7to10["7-10"][$_totaldata->team];
			}
			else{
				$finalarray["7-10"][]=0;
			}
			$finalarray['type']["7-10"]='column';
     		$finalarray['name']["7-10"] ="7-10";
			if(isset($dataval10plus["10+"])){
			$finalarray["10+"][]=$dataval10plus["10+"][$_totaldata->team];
			}
			else{
				$finalarray["10+"][]=0;
			}
			$finalarray['type']["10+"]='column';
     		$finalarray['name']["10+"] ="10+";
			$finalarray['team'][] =$_totaldata->team;
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
        return view('components.Performance.TLperformancemashreqspread');
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
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
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
	
	
	
	
	
	public static function getNumberHeadcount($team,$widgetId){
		
		 $whereraw = '';
		$whererawsales = '';
		$whererawrange = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
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
				$salestime=date("n-Y", strtotime($fromDate));				
				}
				
				
				 
				
			}
			
			
			
			
		}
		
if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Anas','Shahnawaz');
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
				if($sales_processor_internal_value=='Tapash Dahal')
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
			
       if($whererawsales == '')
			{
				$whererawsales = 'TL IN('.$teamfinal.')';
			}
			else
			{
				$whererawsales .= ' And TL IN('.$teamfinal.')';
			}


			
		}
		
		
		
		
		else{
			
		}
		if(Request::session()->get('widgetFiltermolRange['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolRange['.$widgetId.']') != '')
		{
			$rangeid = Request::session()->get('widgetFiltermolRange['.$widgetId.']');
			if($rangeid!=1){
				 if($rangeid==2)
				{
					$arry5plus=array(1,2,3); 
					$rangeids=implode(",", $arry5plus);
				}
				else if($rangeid==3)
				{
					$arry5plus=array(4,5,6);
				$rangeids=implode(",", $arry5plus);					
				}				
				else if($rangeid==4)
				{
					$arry5plus=array(7,8,9,10); 
					$rangeids=implode(",", $arry5plus);
				}				
				else if($rangeid==5)
				{
					$arry5plus=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
					$rangeids=implode(",", $arry5plus);
				}				
				
				if($whererawsales == '')
				{
					$whererawsales = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whererawsales .= 'And range_id IN ('.$rangeids.')';

				}
				
				
			}
		}

		
			  
			  
			  

				//$empsalesname=$empIddata->sales_name;
			if($datatype == 'current_month' || $datatype == ''){				
				$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',36)->where('job_function',3)->first();
				
				if($empdata!=''){
				if($whererawsales != '')
				{
				return	$empcountdata  = Employee_details::where('tl_id',$empdata->id)->where('offline_status',1)->whereRaw($whererawsales)->where('dept_id',36)->get()->count();
				}
				else{
					return 	$empcountdata  = Employee_details::where('tl_id',$empdata->id)->where('offline_status',1)->where('dept_id',36)->get()->count();
				
				}
				  }
				  
				  
				  else{
					  return 0;
					  
					}
			}
			else{
				if($whererawsales != '')
				{
				
				return $totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->get()->count();
				}else{
				
				return $totalmastercard=MasterPayoutPre::where("TL",$team)->get()->count();
				}
			}
	
		
		
	}
	
	
	
	
		
	public static function getTotalsubmissionzerocard($team,$widgetId)
	{
		$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
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
				$salestime=date("n-Y", strtotime($fromDate));				
				}
				
				
				 
				
			}
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
			
			
			
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		
		
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Anas','Shahnawaz');
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
				if($sales_processor_internal_value=='Tapash Dahal')
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
			
       if($whererawsales == '')
			{
				$whererawsales = 'TL IN('.$teamfinal.')';
			}
			else
			{
				$whererawsales .= ' And TL IN('.$teamfinal.')';
			}


			
		}
		
		
		
		if(Request::session()->get('widgetFiltermolRange['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolRange['.$widgetId.']') != '')
		{
			$rangeid = Request::session()->get('widgetFiltermolRange['.$widgetId.']');
			
				 if($rangeid==2)
				{
					$arry5plus=array(1,2,3); 
					$rangeids=implode(",", $arry5plus);
				}
				else if($rangeid==3)
				{
					$arry5plus=array(4,5,6);
				$rangeids=implode(",", $arry5plus);					
				}				
				else if($rangeid==4)
				{
					$arry5plus=array(7,8,9,10); 
					$rangeids=implode(",", $arry5plus);
				}				
				else if($rangeid==5)
				{
					$arry5plus=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
					$rangeids=implode(",", $arry5plus);
				}				
				if($whereraw == '')
				{
					$whereraw = 'range_id IN ('.$rangeids.')';
					$whererawrange = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whereraw .= ' And range_id IN ('.$rangeids.')';
					$whererawrange = 'range_id IN ('.$rangeids.')';
				}
				if($whererawsales == '')
				{
					$whererawsales = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whererawsales .= 'And range_id IN ('.$rangeids.')';

				}
				
				
			}
//echo $salestime;
		//echo $whereraw;exit;
		if($datatype == 'current_month' || $datatype == ''){

		
		//echo $whereraw;
		if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	
//print_r($totaldata);exit;
		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			$finalarrayempid[]=$_countdata->emp_id;

		}
		//print_r($finalarrayempid);
		//$count;
		
		if($whererawrange != '')
			{
				
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',36)->where('job_function',3)->first();
				if($empdata!=''){
					
					
					$totalcard  = Employee_details::where('tl_id',$empdata->id)->whereNotIn('emp_id',$finalarrayempid)->whereRaw($whererawrange)->where('offline_status',1)->where('dept_id',36)->get()->count();
					$count  = Employee_details::where('tl_id',$empdata->id)->whereRaw($whererawrange)->where('offline_status',1)->where('dept_id',36)->get()->count();
					
					}else{
						$totalcard=1;
					}
			} else{
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',36)->where('job_function',3)->first();
			if($empdata!=''){
					
					
					$totalcard  = Employee_details::where('tl_id',$empdata->id)->whereNotIn('emp_id',$finalarrayempid)->where('offline_status',1)->where('dept_id',36)->get()->count();
					$count  = Employee_details::where('tl_id',$empdata->id)->where('offline_status',1)->where('dept_id',36)->get()->count();
					
					}else{
						$totalcard=1;
					}
			
			}
			if($count>0){
				$count=$count;
			}else{
				$count=1;
			}
		return round(($totalcard/$count)*100,2);
		
		}
		else{
			return 0;
		}
	}
	else{
	
			
			//echo $whereraw;
			if($whererawsales != '')
			{
			$totalmasterTL=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->where("tc","=",0)->get()->count();
			$totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->get()->count();
			}else{
			$totalmasterTL=MasterPayoutPre::where("TL",$team)->where("tc","=",0)->get()->count();
			$totalmastercard=MasterPayoutPre::where("TL",$team)->get()->count();
			}
				
			//$fname." ".$lname;  
			
			
			if($totalmastercard>0){
				$totalmastercard=$totalmastercard;
			}else{
				$totalmastercard=1;
			}
			return round(($totalmasterTL/$totalmastercard)*100,2);
			//return $totalmasterTL;
			//return view(compact('$totalmasterTL'));
			// return view('components/DashboardLeadership/Leadershipmashreqspread',compact('totalmasterTL'));
			
			//return $a;
			//return $fres.round(ceil($countdata)/$totalemp,2)." (Card: ".$countdata."))";
		}
			

		
	}
	
	
	
	
	public static function getTotalsubmissionzerototal($team,$widgetId)
	{
		$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
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
				$salestime=date("n-Y", strtotime($fromDate));				
				}
				
			}
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		
		
	if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Anas','Shahnawaz');
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
				if($sales_processor_internal_value=='Tapash Dahal')
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
			
       if($whererawsales == '')
			{
				$whererawsales = 'TL IN('.$teamfinal.')';
			}
			else
			{
				$whererawsales .= ' And TL IN('.$teamfinal.')';
			}


			
		}	
		
		
		
		
		
		if(Request::session()->get('widgetFiltermolRange['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolRange['.$widgetId.']') != '')
		{
			$rangeid = Request::session()->get('widgetFiltermolRange['.$widgetId.']');
			
				 if($rangeid==2)
				{
					$arry5plus=array(1,2,3); 
					$rangeids=implode(",", $arry5plus);
				}
				else if($rangeid==3)
				{
					$arry5plus=array(4,5,6);
				$rangeids=implode(",", $arry5plus);					
				}				
				else if($rangeid==4)
				{
					$arry5plus=array(7,8,9,10); 
					$rangeids=implode(",", $arry5plus);
				}				
				else if($rangeid==5)
				{
					$arry5plus=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
					$rangeids=implode(",", $arry5plus);
				}				
				if($whereraw == '')
				{
					$whereraw = 'range_id IN ('.$rangeids.')';
					$whererawrange = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whereraw .= ' And range_id IN ('.$rangeids.')';
					$whererawrange = 'range_id IN ('.$rangeids.')';
				}
				if($whererawsales == '')
				{
					$whererawsales = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whererawsales .= 'And range_id IN ('.$rangeids.')';
					
				}
				
			}

		
		if($datatype == 'current_month' || $datatype == ''){

		
		//echo $whereraw;
		if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	
//print_r($totaldata);exit;
		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			$finalarrayempid[]=$_countdata->emp_id;

		}
		//print_r($finalarrayempid);
		//$count;
		if($whererawrange != '')
			{
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',36)->where('job_function',3)->first();
				if($empdata!=''){
					
					
					$totalcard  = Employee_details::where('tl_id',$empdata->id)->whereNotIn('emp_id',$finalarrayempid)->whereRaw($whererawrange)->where('offline_status',1)->where('dept_id',36)->get()->count();
					
					}else{
						$totalcard=1;
					}
			} else{
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',36)->where('job_function',3)->first();
			if($empdata!=''){
					
					
					$totalcard  = Employee_details::where('tl_id',$empdata->id)->whereNotIn('emp_id',$finalarrayempid)->where('offline_status',1)->where('dept_id',36)->get()->count();
					
					}else{
						$totalcard=1;
					}
			
			}
//echo $totalcard;exit;			
			//return $count;
		return $totalcard;
		
		}
		else{
			return 0;
		}
	}
	else{
	
			
			//echo $whereraw;
			if($whererawsales != '')
			{
			$totalmasterTL=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->where("tc","=",0)->get()->count();
			$totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->get()->count();
			}else{
			$totalmasterTL=MasterPayoutPre::where("TL",$team)->where("tc","=",0)->get()->count();
			$totalmastercard=MasterPayoutPre::where("TL",$team)->get()->count();
			}
				
			//$fname." ".$lname;  
			
			
			
			return $totalmasterTL;
			//return $totalmasterTL;
			//return view(compact('$totalmasterTL'));
			// return view('components/DashboardLeadership/Leadershipmashreqspread',compact('totalmasterTL'));
			
			//return $a;
			//return $fres.round(ceil($countdata)/$totalemp,2)." (Card: ".$countdata."))";
		}
		
	}
	
	
	public static function getTotalsubmissionlessthen3($team,$widgetId)
	{
		$whereraw = '';
		$whererawsales = '';
		$whererawrange = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
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
				$salestime=date("n-Y", strtotime($fromDate));				
				}
				
			}
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			
			
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		
		
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Anas','Shahnawaz');
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
				if($sales_processor_internal_value=='Tapash Dahal')
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
			
       if($whererawsales == '')
			{
				$whererawsales = 'TL IN('.$teamfinal.')';
			}
			else
			{
				$whererawsales .= ' And TL IN('.$teamfinal.')';
			}


			
		}
		
		
		
		
		if(Request::session()->get('widgetFiltermolRange['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolRange['.$widgetId.']') != '')
		{
			$rangeid = Request::session()->get('widgetFiltermolRange['.$widgetId.']');
			
				 if($rangeid==2)
				{
					$arry5plus=array(1,2,3); 
					$rangeids=implode(",", $arry5plus);
				}
				else if($rangeid==3)
				{
					$arry5plus=array(4,5,6);
				$rangeids=implode(",", $arry5plus);					
				}				
				else if($rangeid==4)
				{
					$arry5plus=array(7,8,9,10); 
					$rangeids=implode(",", $arry5plus);
				}				
				else if($rangeid==5)
				{
					$arry5plus=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
					$rangeids=implode(",", $arry5plus);
				}				
				if($whereraw == '')
				{
					$whereraw = 'range_id IN ('.$rangeids.')';
					$whererawrange = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whereraw .= ' And range_id IN ('.$rangeids.')';
					$whererawrange = 'range_id IN ('.$rangeids.')';
				}
				if($whererawsales == '')
				{
					$whererawsales = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whererawsales .= 'And range_id IN ('.$rangeids.')';
					
				}
				
			}

		
		if($datatype == 'current_month' || $datatype == ''){

		
		//echo $whereraw;
		if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	
//print_r($totaldata);exit;
		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			if($_countdata->total<=3){
				$count=$count+1;
//$totalreturndata=$count;
			}

		}
		//$count;
		if($whererawrange != '')
			{
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',36)->where('job_function',3)->first();
				if($empdata!=''){
					
					
					$totalcard  = Employee_details::where('tl_id',$empdata->id)->whereRaw($whererawrange)->where('offline_status',1)->where('dept_id',36)->get()->count();
					
					}else{
						$totalcard=1;
					}
			} else{
			$empdata  = Employee_details::where('sales_name',$team)->where('dept_id',36)->where('job_function',3)->first();
			if($empdata!=''){
					
					
					$totalcard  = Employee_details::where('tl_id',$empdata->id)->where('offline_status',1)->where('dept_id',36)->get()->count();
					
					}else{
						$totalcard=1;
					}
			
			}	
			if($totalcard>0){
				$totalcard=$totalcard;
			}else{
				$totalcard=1;
			}
			//return $count;
		return round(($count/$totalcard)*100,2);
		
		}
		else{
			return 0;
		}
	}
	else{
	
			
			//echo $whererawsales;
			
			if($whererawsales != '')
			{
			$totalmasterTL=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->whereBetween('tc', [1,3])->get()->count();
			$totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->get()->count();
			}else{
			$totalmasterTL=MasterPayoutPre::where("TL",$team)->whereBetween('tc', [1,3])->get()->count();
			$totalmastercard=MasterPayoutPre::where("TL",$team)->get()->count();
			}
			if($totalmastercard>0){
				$totalmastercard=$totalmastercard;
			}
			else{
				$totalmastercard=1;
			}
			//$fname." ".$lname;  
			
			
			
			return round(($totalmasterTL/$totalmastercard)*100,2);
			//return $totalmasterTL;
			//return view(compact('$totalmasterTL'));
			// return view('components/DashboardLeadership/Leadershipmashreqspread',compact('totalmasterTL'));
			
			//return $a;
			//return $fres.round(ceil($countdata)/$totalemp,2)." (Card: ".$countdata."))";
		}
			

		
	}
	
	
	public static function getTotalsubmissionlessthen3total($team,$widgetId)
	{
		$whereraw = '';
		$whererawsales = '';
		$whererawrange='';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
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
				$salestime=date("n-Y", strtotime($fromDate));				
				}
				
			}
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			
			
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		
		
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Anas','Shahnawaz');
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
				if($sales_processor_internal_value=='Tapash Dahal')
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
			
       if($whererawsales == '')
			{
				$whererawsales = 'TL IN('.$teamfinal.')';
			}
			else
			{
				$whererawsales .= ' And TL IN('.$teamfinal.')';
			}


			
		}
		
		
		
		
		if(Request::session()->get('widgetFiltermolRange['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolRange['.$widgetId.']') != '')
		{
			$rangeid = Request::session()->get('widgetFiltermolRange['.$widgetId.']');
			
				 if($rangeid==2)
				{
					$arry5plus=array(1,2,3); 
					$rangeids=implode(",", $arry5plus);
				}
				else if($rangeid==3)
				{
					$arry5plus=array(4,5,6);
				$rangeids=implode(",", $arry5plus);					
				}				
				else if($rangeid==4)
				{
					$arry5plus=array(7,8,9,10); 
					$rangeids=implode(",", $arry5plus);
				}				
				else if($rangeid==5)
				{
					$arry5plus=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
					$rangeids=implode(",", $arry5plus);
				}				
				if($whereraw == '')
				{
					$whereraw = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whereraw .= ' And range_id IN ('.$rangeids.')';
					
				}
				if($whererawsales == '')
				{
					$whererawsales = 'range_id IN ('.$rangeids.')';
				}
				else
				{
					$whererawsales .= 'And range_id IN ('.$rangeids.')';
				}
				
			}

		
		if($datatype == 'current_month' || $datatype == ''){

		
		//echo $whereraw;
		if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	

		}


		//dd($totaldata);

		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			if($_countdata->total<=3){
				$count=$count+1;
//$totalreturndata=$count;
			}

		}
		//$count;
		 
		return $count;
		
		}
		else{
			return 0;
		}
	}
	else{
	
			
			//echo $whereraw;
			
			if($whererawsales != '')
			{
			$totalmasterTL=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->whereBetween('tc', [1,3])->get()->count();
			$totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->get()->count();
			}else{
			$totalmasterTL=MasterPayoutPre::where("TL",$team)->whereBetween('tc', [1,3])->get()->count();
			$totalmastercard=MasterPayoutPre::where("TL",$team)->get()->count();
			}
			if($totalmastercard>0){
				$totalmastercard=$totalmastercard;
			}
			else{
				$totalmastercard=1;
			}
			//$fname." ".$lname;  
			
			
			
			return $totalmasterTL;
			//return $totalmasterTL;
			//return view(compact('$totalmasterTL'));
			// return view('components/DashboardLeadership/Leadershipmashreqspread',compact('totalmasterTL'));
			
			//return $a;
			//return $fres.round(ceil($countdata)/$totalemp,2)." (Card: ".$countdata."))";

	}	

		
	}
	
	
	
	public static function getTotalsubmission4to6($team,$widgetId)
	{
		$whereraw = '';
		$whererawsales = '';
		$whererawrange='';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
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
				$salestime=date("n-Y", strtotime($fromDate));				
				}
				
			}
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			
			
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		
		
		
	if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Anas','Shahnawaz');
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
				if($sales_processor_internal_value=='Tapash Dahal')
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
			
       if($whererawsales == '')
			{
				$whererawsales = 'TL IN('.$teamfinal.')';
			}
			else
			{
				$whererawsales .= ' And TL IN('.$teamfinal.')';
			}


			
		}	
		
		
		
		
		
		
		
		
		
		
		
		
		
		if(Request::session()->get('widgetFiltermolRange['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolRange['.$widgetId.']') != '')
		{
			$rangeid = Request::session()->get('widgetFiltermolRange['.$widgetId.']');
			
				 if($rangeid==2)
				{
					$arry5plus=array(1,2,3); 
					$rangeids=implode(",", $arry5plus);
				}
				else if($rangeid==3)
				{
					$arry5plus=array(4,5,6);
				$rangeids=implode(",", $arry5plus);					
				}				
				else if($rangeid==4)
				{
					$arry5plus=array(7,8,9,10); 
					$rangeids=implode(",", $arry5plus);
				}				
				else if($rangeid==5)
				{
					$arry5plus=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
					$rangeids=implode(",", $arry5plus);
				}				
				if($whereraw == '')
				{
					$whereraw = 'range_id IN ('.$rangeids.')';
					$whererawrange = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whereraw .= ' And range_id IN ('.$rangeids.')';
					$whererawrange = 'range_id IN ('.$rangeids.')';
				}
				if($whererawsales == '')
				{
					$whererawsales = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whererawsales .= 'And range_id IN ('.$rangeids.')';
					
				}
				
			}
		
		
		if($datatype == 'current_month' || $datatype == ''){

		
		//echo $whereraw;
		if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	

		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			if($_countdata->total>=4 && $_countdata->total<=6){
				$count=$count+1;
				$totalreturndata=$count;
			}

		}
		//echo $whererawrange;
		//$count;$whererawrange
		 $empdata  = Employee_details::where('sales_name',$team)->where('dept_id',36)->where('job_function',3)->first();
				
				if($empdata!=''){
				
				if($whererawrange != '')
					{
		    	$totalcard  = Employee_details::where('tl_id',$empdata->id)->whereRaw($whererawrange)->where('offline_status',1)->where('dept_id',36)->get()->count();
					}
					else{
					$totalcard  = Employee_details::where('tl_id',$empdata->id)->where('offline_status',1)->where('dept_id',36)->get()->count();
					
					}
				
				}else{
					$totalcard=1;
				}

		//$df = $totalcard;	
		if($totalcard>0){
			$totalcard=$totalcard;
		}else{
			$totalcard=1;
		}
		return round(($count/$totalcard)*100,2);
		
		}
		else{
			return 0;
		}
	}
	else{
		
			//echo $whereraw;//exit;
			if($whererawsales!='')
			{
			$totalmasterTL=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->whereBetween('tc', [4, 6])->get()->count();
			$totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->get()->count();
			}else{
			$totalmasterTL=MasterPayoutPre::where("TL",$team)->whereBetween('tc', [4, 6])->get()->count();
			$totalmastercard=MasterPayoutPre::where("TL",$team)->get()->count();
			}
			if($totalmastercard>0){
				$totalmastercard=$totalmastercard;
			}
			else{
				$totalmastercard=1;
			}
			return round(($totalmasterTL/$totalmastercard)*100,2);
	
	}
	}
	
	
	
	public static function getTotalsubmission4to6total($team,$widgetId)
	{
		$whereraw = '';
		$whererawsales = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
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
				$salestime=date("n-Y", strtotime($fromDate));				
				}
				
			}
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
			
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		
		
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Anas','Shahnawaz');
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
				if($sales_processor_internal_value=='Tapash Dahal')
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
			
       if($whererawsales == '')
			{
				$whererawsales = 'TL IN('.$teamfinal.')';
			}
			else
			{
				$whererawsales .= ' And TL IN('.$teamfinal.')';
			}


			
		}
		
		
		
		
		
		
		
		
		
		
		if(Request::session()->get('widgetFiltermolRange['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolRange['.$widgetId.']') != '')
		{
			$rangeid = Request::session()->get('widgetFiltermolRange['.$widgetId.']');
			
				 if($rangeid==2)
				{
					$arry5plus=array(1,2,3); 
					$rangeids=implode(",", $arry5plus);
				}
				else if($rangeid==3)
				{
					$arry5plus=array(4,5,6);
				$rangeids=implode(",", $arry5plus);					
				}				
				else if($rangeid==4)
				{
					$arry5plus=array(7,8,9,10); 
					$rangeids=implode(",", $arry5plus);
				}				
				else if($rangeid==5)
				{
					$arry5plus=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
					$rangeids=implode(",", $arry5plus);
				}				
				if($whereraw == '')
				{
					$whereraw = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whereraw .= ' And range_id IN ('.$rangeids.')';
					
				}
				if($whererawsales == '')
				{
					
					$whererawsales = 'range_id IN ('.$rangeids.')';
				}
				else
				{
					
					$whererawsales .= 'And range_id IN ('.$rangeids.')';
				}
				
			}
		
		
		if($datatype == 'current_month' || $datatype == ''){

		
		//echo $whereraw;
		if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	

		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
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
		
		
			//echo $whereraw;
			if($whererawsales!='')
			{
			$totalmasterTL=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->whereBetween('tc', [4, 6])->get()->count();
			$totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->get()->count();
			}else{
			$totalmasterTL=MasterPayoutPre::where("TL",$team)->whereBetween('tc', [4, 6])->get()->count();
			$totalmastercard=MasterPayoutPre::where("TL",$team)->get()->count();
			}
			if($totalmastercard>0){
				$totalmastercard=$totalmastercard;
			}
			else{
				$totalmastercard=1;
			}
			
			
			//return $totalmasterTL;
	return $totalmasterTL;
	}
		
	}
	
	
	
	
		public static function getTotalsubmission7to10($team,$widgetId)
	{
		$whereraw = '';
		$whererawsales = '';
		$whererawrange='';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
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
				$salestime=date("n-Y", strtotime($fromDate));				
				}
				
			}
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			
			
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		
		
		
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Anas','Shahnawaz');
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
				if($sales_processor_internal_value=='Tapash Dahal')
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
			
       if($whererawsales == '')
			{
				$whererawsales = 'TL IN('.$teamfinal.')';
			}
			else
			{
				$whererawsales .= ' And TL IN('.$teamfinal.')';
			}


			
		}
		
		
		
		
		
		
		
		
		
		
		
		if(Request::session()->get('widgetFiltermolRange['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolRange['.$widgetId.']') != '')
		{
			$rangeid = Request::session()->get('widgetFiltermolRange['.$widgetId.']');
			
				 if($rangeid==2)
				{
					$arry5plus=array(1,2,3); 
					$rangeids=implode(",", $arry5plus);
				}
				else if($rangeid==3)
				{
					$arry5plus=array(4,5,6);
				$rangeids=implode(",", $arry5plus);					
				}				
				else if($rangeid==4)
				{
					$arry5plus=array(7,8,9,10); 
					$rangeids=implode(",", $arry5plus);
				}				
				else if($rangeid==5)
				{
					$arry5plus=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
					$rangeids=implode(",", $arry5plus);
				}				
				if($whereraw == '')
				{
					$whereraw = 'range_id IN ('.$rangeids.')';
					$whererawrange = 'range_id IN ('.$rangeids.')';
				}
				else
				{
					$whereraw .= ' And range_id IN ('.$rangeids.')';
					$whererawrange = 'range_id IN ('.$rangeids.')';
										

				}
				if($whererawsales == '')
				{
					$whererawsales = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whererawsales .= 'And range_id IN ('.$rangeids.')';
										

				}
				
			}
			if($datatype == 'current_month' || $datatype == ''){

		
		//echo $whereraw;
		if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	

		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			if($_countdata->total>=7 && $_countdata->total<=10){
				$count=$count+1;
				$totalreturndata=$count;
			}

		}
		//$count;
		 $empdata  = Employee_details::where('sales_name',$team)->where('dept_id',36)->where('job_function',3)->first();
				
				if($empdata!=''){
				
				if($whererawrange != '')
					{
		    	$totalcard  = Employee_details::where('tl_id',$empdata->id)->whereRaw($whererawrange)->where('offline_status',1)->where('dept_id',36)->get()->count();
					}
					else{
					$totalcard  = Employee_details::where('tl_id',$empdata->id)->where('offline_status',1)->where('dept_id',36)->get()->count();
					
					}
				
				}else{
					$totalcard=1;
				}
				if($totalcard>0){
					$totalcard=$totalcard;
				}else{
					$totalcard=1;
				}
		return round(($count/$totalcard)*100,2);
		
		}
		else{
			return 0;
		}
	}
	else{
		//echo $whereraw;
			if($whererawsales != '')
			{
			$totalmasterTL=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->whereBetween('tc', [7, 10])->get()->count();
			$totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->get()->count();
			}else{
			$totalmasterTL=MasterPayoutPre::where("TL",$team)->whereBetween('tc', [7, 10])->get()->count();
			$totalmastercard=MasterPayoutPre::where("TL",$team)->get()->count();
			}
			if($totalmastercard>0){
				$totalmastercard=$totalmastercard;
			}
			else{
				$totalmastercard=1;
			}
			return round(($totalmasterTL/$totalmastercard)*100,2);
	
	}
	}
	
	
	
	
	public static function getTotalsubmission7to10total($team,$widgetId)
	{
				$whereraw = '';
		$whererawsales = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
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
				$salestime=date("n-Y", strtotime($fromDate));				
				}
				
			}
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
			
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		
		
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Anas','Shahnawaz');
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
				if($sales_processor_internal_value=='Tapash Dahal')
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
			
       if($whererawsales == '')
			{
				$whererawsales = 'TL IN('.$teamfinal.')';
			}
			else
			{
				$whererawsales .= ' And TL IN('.$teamfinal.')';
			}


			
		}
		
		
		
		
		
		
		
		
		
		
		
		if(Request::session()->get('widgetFiltermolRange['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolRange['.$widgetId.']') != '')
		{
			$rangeid = Request::session()->get('widgetFiltermolRange['.$widgetId.']');
			
				 if($rangeid==2)
				{
					$arry5plus=array(1,2,3); 
					$rangeids=implode(",", $arry5plus);
				}
				else if($rangeid==3)
				{
					$arry5plus=array(4,5,6);
				$rangeids=implode(",", $arry5plus);					
				}				
				else if($rangeid==4)
				{
					$arry5plus=array(7,8,9,10); 
					$rangeids=implode(",", $arry5plus);
				}				
				else if($rangeid==5)
				{
					$arry5plus=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
					$rangeids=implode(",", $arry5plus);
				}				
				if($whereraw == '')
				{
					$whereraw = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whereraw .= ' And range_id IN ('.$rangeids.')';
					
				}
				if($whererawsales == '')
				{
					$whererawsales = 'range_id IN ('.$rangeids.')';
				}
				else
				{
					$whererawsales .= 'And range_id IN ('.$rangeids.')';
				}
				
			}
			if($datatype == 'current_month' || $datatype == ''){

		
		//echo $whereraw;
		if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	

		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			if($_countdata->total>=7 && $_countdata->total<=10){
				$count=$count+1;
				$totalreturndata=$count;
			}

		}
		//$count;
		 $totalcard= DepartmentFormEntry::where("range_id","!=",NULL)->where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->whereBetween('submission_date', [$fromDate, $toDate])->where("team",$team)->get()->count();
		if($totalcard>0){
			$totalcard=$totalcard;
		}
		else{
			$totalcard=1;
		}
		return $count;
		
		}
		else{
			return 0;
		}
	}
	else{

		//echo $whereraw;
			if($whererawsales != '')
			{
			$totalmasterTL=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->whereBetween('tc', [7, 10])->get()->count();
			$totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->get()->count();
			}else{
			$totalmasterTL=MasterPayoutPre::where("TL",$team)->whereBetween('tc', [7, 10])->get()->count();
			$totalmastercard=MasterPayoutPre::where("TL",$team)->get()->count();
			}
			if($totalmastercard>0){
				$totalmastercard=$totalmastercard;
			}
			else{
				$totalmastercard=1;
			}
			return $totalmasterTL;
	}
		
	}
	
	
	
	
	
	
	public static function getTotalsubmission10plus($team,$widgetId)
	{
				$whereraw = '';
		$whererawsales = '';
		$whererawrange='';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
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
				$salestime=date("n-Y", strtotime($fromDate));				
				}
				
			}
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		
		
		
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Anas','Shahnawaz');
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
				if($sales_processor_internal_value=='Tapash Dahal')
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
			
       if($whererawsales == '')
			{
				$whererawsales = 'TL IN('.$teamfinal.')';
			}
			else
			{
				$whererawsales .= ' And TL IN('.$teamfinal.')';
			}


			
		}
		
		
		
		
		
		
		
		
		
		
		if(Request::session()->get('widgetFiltermolRange['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolRange['.$widgetId.']') != '')
		{
			$rangeid = Request::session()->get('widgetFiltermolRange['.$widgetId.']');
			
				 if($rangeid==2)
				{
					$arry5plus=array(1,2,3); 
					$rangeids=implode(",", $arry5plus);
				}
				else if($rangeid==3)
				{
					$arry5plus=array(4,5,6);
				$rangeids=implode(",", $arry5plus);					
				}				
				else if($rangeid==4)
				{
					$arry5plus=array(7,8,9,10); 
					$rangeids=implode(",", $arry5plus);
				}				
				else if($rangeid==5)
				{
					$arry5plus=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
					$rangeids=implode(",", $arry5plus);
				}				
				if($whereraw == '')
				{
					$whereraw = 'range_id IN ('.$rangeids.')';
					
					$whererawrange = 'range_id IN ('.$rangeids.')';
				}
				else
				{
					$whereraw .= ' And range_id IN ('.$rangeids.')';
					
					$whererawrange = 'range_id IN ('.$rangeids.')';
				}
				if($whererawsales == '')
				{
					$whererawsales = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whererawsales .= 'And range_id IN ('.$rangeids.')';
					
				}
				
			}
			if($datatype == 'current_month' || $datatype == ''){

		
		//echo $whereraw;
		if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	

		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			if($_countdata->total>10){
				$count=$count+1;
				$totalreturndata=$count;
			}

		}
		//$count;
		 $empdata  = Employee_details::where('sales_name',$team)->where('dept_id',36)->where('job_function',3)->first();
				
				if($empdata!=''){
				
				if($whererawrange != '')
					{
		    	$totalcard  = Employee_details::where('tl_id',$empdata->id)->whereRaw($whererawrange)->where('offline_status',1)->where('dept_id',36)->get()->count();
					}
					else{
					$totalcard  = Employee_details::where('tl_id',$empdata->id)->where('offline_status',1)->where('dept_id',36)->get()->count();
					
					}
				
				}else{
					$totalcard=1;
				}
			if($totalcard>0){
				$totalcard=$totalcard;
			}else{
				$totalcard=1;
			}	
		return round(($count/$totalcard)*100,2);
		
		}
		else{
			return 0;
		}
	}
	else{
     		//echo $salestime;exit;
			if($whererawsales != '')
			{
			$totalmasterTL=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->where("tc",">",10)->get()->count();
			$totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->get()->count();
			}else{
			$totalmasterTL=MasterPayoutPre::where("TL",$team)->where("tc",">",10)->get()->count();
			$totalmastercard=MasterPayoutPre::where("TL",$team)->get()->count();
			}
			if($totalmastercard>0){
				$totalmastercard=$totalmastercard;
			}
			else{
				$totalmastercard=1;
			}
			return round(($totalmasterTL/$totalmastercard)*100,2);
	}
	
		
	}
	
	
	
	
	
	
	public static function getTotalsubmission10plustotal($team,$widgetId)
	{
				$whereraw = '';
		$whererawsales = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				$salestime=date("n-Y", strtotime($fromDate));
				
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
			$m= date("Y-m", strtotime('-2 month'));
			$fromDate = $m.'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
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
				$salestime=date("n-Y", strtotime($fromDate));				
				}
				
			}
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			
			
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			$salestime=date("n-Y", strtotime($fromDate));
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			if($whererawsales == '')
			{
				$whererawsales = "sales_time= '".$salestime."'";
			}
			else
			{
				$whererawsales .= " And sales_time= '".$salestime."'";
			}
		}
		
		
		
		if(Request::session()->get('widgetFilterprocessor['.$widgetId.']') != '' && Request::session()->get('widgetFilterprocessor['.$widgetId.']') != NULL)
		{
			
			$team = array();
			$team_Mahwish_130 = array('Ajay','Mujahid','Anas','Shahnawaz');
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
				if($sales_processor_internal_value=='Tapash Dahal')
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
			
       if($whererawsales == '')
			{
				$whererawsales = 'TL IN('.$teamfinal.')';
			}
			else
			{
				$whererawsales .= ' And TL IN('.$teamfinal.')';
			}


			
		}
		
		
		
		
		
		
		
		
		
		
		if(Request::session()->get('widgetFiltermolRange['.$widgetId.']') != 'All' && Request::session()->get('widgetFiltermolRange['.$widgetId.']') != '')
		{
			$rangeid = Request::session()->get('widgetFiltermolRange['.$widgetId.']');
			
				 if($rangeid==2)
				{
					$arry5plus=array(1,2,3); 
					$rangeids=implode(",", $arry5plus);
				}
				else if($rangeid==3)
				{
					$arry5plus=array(4,5,6);
				$rangeids=implode(",", $arry5plus);					
				}				
				else if($rangeid==4)
				{
					$arry5plus=array(7,8,9,10); 
					$rangeids=implode(",", $arry5plus);
				}				
				else if($rangeid==5)
				{
					$arry5plus=array(11,12,13,14,15,16,17,18,19,20,21,22,23,24,25); 
					$rangeids=implode(",", $arry5plus);
				}				
				if($whereraw == '')
				{
					$whereraw = 'range_id IN ('.$rangeids.')';
					
				}
				else
				{
					$whereraw .= ' And range_id IN ('.$rangeids.')';
					
				}
				if($whererawsales == '')
				{
					$whererawsales = 'range_id IN ('.$rangeids.')';
				}
				else
				{
					$whererawsales .= 'And range_id IN ('.$rangeids.')';
				}
				
			}
			if($datatype == 'current_month' || $datatype == ''){

		
		//echo $whereraw;
		if($whereraw != '')
		{
		$totaldata= DepartmentFormEntry::where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->selectRaw('count(*) as total, emp_id')->whereRaw($whereraw)->where("team",$team)->groupBy('emp_id')->get();
		}
			
		else
		{
		$totaldata=DepartmentFormEntry::where("application_id","!=",NULL)->selectRaw('count(*) as total, emp_id')->where("form_id",1)->where("form_status","Booked")->whereRaw($whereraw)->groupBy('emp_id')->get();	

		}
		if($totaldata!=''){
			$finalarray=array();
			$finalarrayempid=array();
		
		$totalbooking=0;
		
			$count=0;
			
		foreach($totaldata as $_countdata){
			
			if($_countdata->total>10){
				$count=$count+1;
				$totalreturndata=$count;
			}

		}
		//$count;
		 $totalcard= DepartmentFormEntry::where("range_id","!=",NULL)->where("application_id","!=",NULL)->where("form_id",1)->where("form_status","Booked")->whereBetween('submission_date', [$fromDate, $toDate])->where("team",$team)->get()->count();
		if($totalcard>0){
			$totalcard=$totalcard;
		}
		else{
			$totalcard=1;
		}
		return $count;
		
		}
		else{
			return 0;
		}
	}
	else{
     		//echo $salestime;exit;
			//echo $whererawsales;
			if($whererawsales != '')
			{
			$totalmasterTL=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->where("tc",">",10)->get()->count();
			$totalmastercard=MasterPayoutPre::whereRaw($whererawsales)->where("TL",$team)->get()->count();
			}else{
			$totalmasterTL=MasterPayoutPre::where("TL",$team)->where("tc",">",10)->get()->count();
			$totalmastercard=MasterPayoutPre::where("TL",$team)->get()->count();
			}
			if($totalmastercard>0){
				$totalmastercard=$totalmastercard;
			}
			else{
				$totalmastercard=1;
			}
			return $totalmasterTL;
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
}