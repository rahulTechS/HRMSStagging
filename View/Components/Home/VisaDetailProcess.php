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
use App\Models\Bank\CBD\CBDBankMis;
use App\Models\Bank\SCB\SCBBankMis;
use App\Models\Bank\SCB\SCBDepartmentFormParentEntry;
use App\Models\Dashboard\MasterPayout;
use App\Models\DocumentCollectionVisaDetailsProcess\DocumentCollectionVisaDetailsProcess;



class VisaDetailProcess extends Component
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
				$fromDate= date('Y-m-d', strtotime('first day of last month'));
				$toDate= date('Y-m-d', strtotime('last day of last month'));
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
				$m= date("Y-m", strtotime('-2 month'));
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
				$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
		}
		else{
			/*$toDate = date("Y-m-d");
			$fromDate = date("Y-m-d",strtotime("-90 days"));
			if($whereraw == '')
			{
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}*/
		}
		if(Request::session()->get('widgetFiltermolTeam['.$widgetId.']') != '' && Request::session()->get('widgetFiltermolTeam['.$widgetId.']') != NULL )
		{
			$deptIds =  Request::session()->get('widgetFiltermolTeam['.$widgetId.']');
			
			$cnameArray = explode(",",$deptIds);
					 
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]=$namearray;
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
			
			if($whereraw == '')
			{
			$whereraw = 'dept_id IN('.$finalcname.')';
			}
			else
			{
				$whereraw .= ' AND dept_id IN('.$finalcname.')';
			}
		}
		
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= DocumentCollectionVisaDetailsProcess::whereRaw($whereraw)->whereNotNull('dept_id')->groupBy('dept_id')->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata= DocumentCollectionVisaDetailsProcess::groupBy('dept_id')->whereNotNull('dept_id')->get();	

		}
		$graphdata= $totaldata;
		//echo count($totaldata);
		//print_r($graphdata);exit;
		
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
		$TeamLists = DocumentCollectionVisaDetailsProcess::select("dept_id")->whereNotNull('dept_id')->groupBy('dept_id')->get();
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
        return view('components.Home.visadetailprocess');
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
	public static function getvisadetailprocessdata($bank,$widgetId){
	$total= Department::where("id",$bank)->first();
	if($total!=''){
		return $total->department_name;

	}
	else{
		return "No Name";

	}	
	}

	public static function getmedicalprocessdata($medical,$widgetId){
		$medicaltotal= DocumentCollectionVisaDetailsProcess::where("dept_id",$medical)->where("medical",1)->get()->count();
		if($medicaltotal!=''){
			return $medicaltotal;
	
		}
		else{
			return "--";
	
		}	
		}


		public static function getemirtidprocessdata($emritid,$widgetId){
			$emirtidtotal= DocumentCollectionVisaDetailsProcess::where("dept_id",$emritid)->where("emirates",2)->get()->count();
			if($emirtidtotal!=''){
				return $emirtidtotal;
		
			}
			else{
				return "--";
		
			}	
			}



	public static function getsalesTimeAgent($bank,$widgetId){
	$total= MasterPayout::where("bank_name",$bank)->orderBy("sort_order","DESC")->first();
	if($total!=''){
	if($bank=="ENBD" || $bank=="Mashreq"){
	return $totalcount= MasterPayout::where("bank_name",$bank)->where("agent_product_id",1)->where("sales_time",$total->sales_time)->get()->count();	
	}
	else{
	return $totalcount= MasterPayout::where("bank_name",$bank)->where("sales_time",$total->sales_time)->get()->count();
	}
	}
	
		
		
	}
	public static function getsalesTimeCards($bank,$widgetId){
	$total= MasterPayout::where("bank_name",$bank)->orderBy("sort_order","DESC")->first();
	if($total!=''){
	if($bank=="ENBD" || $bank=="Mashreq"){
	 $totalcount= MasterPayout::where("bank_name",$bank)->where("agent_product_id",1)->where("sales_time",$total->sales_time)->get();
	}else{
	 $totalcount= MasterPayout::where("bank_name",$bank)->where("sales_time",$total->sales_time)->get();	
	}
	 $countval=0;
	 $countvalf='';
	 foreach($totalcount as $_data){
		if($_data->bank_name=="ENBD" || $_data->bank_name=="CBD" || $_data->bank_name=="SCB" || $_data->bank_name=="EIB"){
		$countval=$countval+$_data->tc;
		$countvalf=$countval." Cards";
		}
		elseif($_data->bank_name=="DIB"){
			$countval=$countval+$_data->no_card_dib;
			$countvalf=$countval." Cards";
		}	
		elseif($_data->bank_name=="Deem"){
			$countval=$countval+$_data->no_cards_deem;
			$countvalf=$countval." Cards";
		}
		
		
		elseif($_data->bank_name=="Mashreq"){
			$countval=$countval+$_data->cards_point_m;
			$countvalf=$countval." Points";
		}
	 }
	 return $countvalf;
	}	
	}
	public static function getTotalmissingmobilenoSP($team,$widgetId){
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
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y-m-d",strtotime("-90 days"));
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
		if($team=="Mahwish"){
			$teamarray=array('Ajay','Mujahid','Akshada','Shahnawaz');
			
		}
		elseif($team=="Umar"){
			$teamarray=array('Arsalan','Zubair');
		}
		elseif($team=="Arsalan"){
			$teamarray=array('Mohsin','Sahir');
		}
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		return $totaldata= DepartmentFormEntry::whereIn("team",$teamarray)->where("form_id",1)->whereRaw($whereraw)->whereNull("customer_mobile")->get()->count();
		}
			
		else
		{
			//echo "h2";
		return $totaldata= DepartmentFormEntry::whereIn("team",$teamarray)->whereRaw($whereraw)->where("form_id",1)->whereNull("customer_mobile")->get()->count();	

		}		
	}
	public static function getTotalmissingmobileno($team,$widgetId){
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
				$whereraw = "submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And submission_date >= '".$fromDate."' and submission_date <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y-m-d",strtotime("-90 days"));
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
		return $totaldata= DepartmentFormEntry::where("team",$team)->where("form_id",1)->whereRaw($whereraw)->whereNull("customer_mobile")->get()->count();
		}
			
		else
		{
			//echo "h2";
		return $totaldata= DepartmentFormEntry::where("team",$team)->whereRaw($whereraw)->where("form_id",1)->whereNull("customer_mobile")->get()->count();	

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
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."' And team='".$team."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."' And team='".$team."'";
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
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= SCBDepartmentFormParentEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=SCBDepartmentFormParentEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whereraw)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->ref_no;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=SCBDepartmentFormParentEntry::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("ref_no",$finalarray)->get();
		
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
		$totaldata= SCBDepartmentFormParentEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=SCBDepartmentFormParentEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whereraw)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->ref_no;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=SCBDepartmentFormParentEntry::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("ref_no",$finalarray)->get();
		
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
		$totaldata= SCBDepartmentFormParentEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=SCBDepartmentFormParentEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whereraw)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->ref_no;
		}	
		//print_r($finalarray);exit;	
		$totalbooking=0;
		
		 $countdata=SCBDepartmentFormParentEntry::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("ref_no",$finalarray)->get();
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
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."' And team='".$team."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."' And team='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= SCBDepartmentFormParentEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=SCBDepartmentFormParentEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whereraw)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->ref_no;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=SCBDepartmentFormParentEntry::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("ref_no",$finalarray)->get();
		
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
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."' And team='".$team."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."' And team='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= SCBDepartmentFormParentEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=SCBDepartmentFormParentEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whereraw)->where("team",$team)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->ref_no;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=SCBDepartmentFormParentEntry::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("ref_no",$finalarray)->get();
		
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
				$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."' And team='".$team."'";
			}
			else
			{
				$whereraw .= " And application_date >= '".$fromDate."' and application_date <= '".$toDate."' And team='".$team."'";
			}
		}
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= SCBDepartmentFormParentEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whereraw)->where("team",$team)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata=SCBDepartmentFormParentEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whereraw)->where("team",$team)->get();	

		}
		//print_r($totaldata);exit;
		if($totaldata!=''){
			$finalarray=array();
		foreach($totaldata as $_totaldata){
			//print_r($_totaldata);exit;
			$finalarray[]=$_totaldata->ref_no;
		}	
		//print_r($finalarray);	
		$totalbooking=0;
		
		 $countdata=SCBDepartmentFormParentEntry::groupBy('emp_id')->selectRaw('count(*) as total, emp_id')->whereIn("ref_no",$finalarray)->get();
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
	
		
	public static function getTeamListsSelectedNameTime($TeamListsSelected){
		//$departmentName = Department::where("id",$data->department)->first()->department_name;
		//print_r($TeamListsSelected);exit;
		$name = '';
		foreach($TeamListsSelected as $r)
		{
			
			if($name == '')
			{
				$fname =  Department::where("id",$r)->first();
				if($fname!=''){
				$name=	$fname->department_name;
				}
			}
			else
			{
				$name = $name.','.Department::where("id",$r)->first()->department_name;
				
			}
		}
		return $name;
	}
}