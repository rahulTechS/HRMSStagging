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

class PerformanceEibPlasticDistrbution extends Component
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
				//toDate=date("Y").'-'.date("m").'-'.'05';
				//$fromDate=date("Y",strtotime("-1 month".date("Y-m-d"))).'-'.date("m",strtotime("-1 month ".date("Y-m-d"))).'-'.'06';
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
		if(Request::session()->get('widgetFilterBYSubmissions['.$widgetId.']') != '' && Request::session()->get('widgetFilterBYSubmissions['.$widgetId.']')=="Submissions" )
		{
			//echo $whereraw;
			if($whereraw != '')
			{
				//echo "h1";
			$totaldata= EIBDepartmentFormEntry::select("application_no","card_type")->whereRaw($whereraw)->get();
			}
				
			else
			{
				//echo "h2";
			$totaldata= EIBDepartmentFormEntry::select("application_no","card_type")->whereRaw($whereraw)->get();	

			}
		}
		else{
			
				
			if($whereraw != '')
			{
				//echo "h1";
			$totaldataval= EIBDepartmentFormEntry::select("application_no","card_type")->whereRaw($whereraw)->get();
			}
				
			else
			{
				//echo "h2";
			$totaldataval= EIBDepartmentFormEntry::select("application_no","card_type")->whereRaw($whereraw)->get();	

			}
			$finalarray=array();
			foreach($totaldataval as $valuedat){
				$val=EibBankMis::where('application_no',$valuedat->application_no)->where("matched_status",1)->where("final_decision","Approve")->first();
				if($val!=''){
				$finalarray[]=$val->application_no;
				}
				
			}
			$totaldata= EIBDepartmentFormEntry::select("application_no","card_type")->whereIn("application_no",$finalarray)->get();
			
			
		}		
		//print_r($totaldata);exit;
	   //echo count($totaldata);//exit;
	   $graphArray = array();
	   $range=array();
		$index = 0;
		$RTA=0;
		$SWITCH=0;
		$FLEX=0;
		$CASHBACK =0;
		$FLEXELITE=0;
		$CASHBACKPLUS=0;
		$SKYWARDSBLACK=0;
		$SKYWARDSINFINITE=0;
		$ETIHADGUEST=0;
		$EMARATI=0;
		$SKYWARDSSIGNATURE=0;
		$SKYWARDSSIGNATURE=0;
		$CASHBACKPLATINUM=0;
		$SYWARDSBLACK=0;
		$SKYWARSBLACK=0;
		$FELX=0;
		foreach($totaldata as $_totaldata)
		{
			
				$cardtype=$_totaldata->card_type;
				if($cardtype=='RTA'){
					//echo $sal."r1";
				$RTA=$RTA+1;	
				}
				else if($cardtype=='SWITCH'){
					//echo $sal."r2";
				$SWITCH=$SWITCH+1;	
				}
				else if($cardtype=='FLEX'){
					//echo $sal."r3";
				$FLEX=$FLEX+1;	
				}
				else if($cardtype=='CASHBACK '){
					//echo $sal."r4";
				$CASHBACK=$CASHBACK+1;	
				}
				else if($cardtype=='FLEX ELITE'){
					//echo $sal."r4";
				$FLEXELITE=$FLEXELITE+1;	
				}
				else if($cardtype=='CASHBACK PLUS'){
					//echo $sal."r4";
				$CASHBACKPLUS=$CASHBACKPLUS+1;	
				}
				else if($cardtype=='SKYWARDS BLACK'){
					//echo $sal."r4";
				$SKYWARDSBLACK=$SKYWARDSBLACK+1;	
				}
				else if($cardtype=='SKYWARDS INFINITE'){
					//echo $sal."r4";
				$SKYWARDSINFINITE=$SKYWARDSINFINITE+1;	
				}
				
				else if($cardtype=='ETIHAD GUEST'){
					//echo $sal."r4";
				$ETIHADGUEST=$ETIHADGUEST+1;	
				}
				else if($cardtype=='EMARATI'){
					//echo $sal."r4";
				$EMARATI=$EMARATI+1;	
				}
				else if($cardtype=='SKYWARDS SIGNATURE'){
					//echo $sal."r4";
				$SKYWARDSSIGNATURE=$SKYWARDSSIGNATURE+1;	
				}
				else if($cardtype=='CASHBACK PLATINUM'){
					//echo $sal."r4";
				$CASHBACKPLATINUM=$CASHBACKPLATINUM+1;	
				}
				else if($cardtype=='SYWARDS BLACK'){
					//echo $sal."r4";
				$SYWARDSBLACK=$SYWARDSBLACK+1;	
				}
				else if($cardtype=='SKYWARS BLACK'){
					//echo $sal."r4";
				$SKYWARSBLACK=$SKYWARSBLACK+1;	
				}
				else if($cardtype=='FELX'){
					//echo $sal."r4";
				$FELX=$FELX+1;	
				}
				
				else{
					
				}
			
			
			
		}
		$countd=count($totaldata);
		if($countd>=1){
			$totaldata=$countd;
		}else{
			$totaldata=1;
		}
			$RTAfinal=round(($RTA/$totaldata)*100);
			$SWITCHfinal=round(($SWITCH/$totaldata)*100);
			$FLEXfinal=round(($FLEX/$totaldata)*100);
			$CASHBACKfinal=round(($CASHBACK/$totaldata)*100);
			$FLEXELITEElitefinal=round(($FLEXELITE/$totaldata)*100);
			$CASHBACKPLUSfinal=round(($CASHBACKPLUS/$totaldata)*100);
			$SKYWARDSBLACKfinal=round(($SKYWARDSBLACK/$totaldata)*100);
			$SKYWARDSINFINITEfinal=round(($SKYWARDSINFINITE/$totaldata)*100);			
			$ETIHADGUESTfinal=round(($ETIHADGUEST/$totaldata)*100);
			$EMARATIfinal=round(($EMARATI/$totaldata)*100);
			$SKYWARDSSIGNATUREfinal=round(($SKYWARDSSIGNATURE/$totaldata)*100);
			$CASHBACKPLATINUMfinal=round(($CASHBACKPLATINUM/$totaldata)*100);
			$SYWARDSBLACKfinal=round(($SYWARDSBLACK/$totaldata)*100);
			$SKYWARSBLACKfinal=round(($SKYWARSBLACK/$totaldata)*100);
			$FELXfinal=round(($FELX/$totaldata)*100);
			
			//$graphArray=array();
			$graphArray = array(
						array(
							"range" => "RTA (".$RTAfinal."%)",
							"salery" => $RTAfinal
						),
						array(
							"range" => "SWITCH (".$SWITCHfinal."%)",
							"salery" => $SWITCHfinal
						),
						array(
							"range" => "FLEX (".$FLEXfinal."%)",
							"salery" => $FLEXfinal
						)
						,
						array(
							"range" => "CASHBACK (".$CASHBACKfinal."%)",
							"salery" => $CASHBACKfinal
						)
						,
						array(
							"range" => "FLEX ELITE (".$FLEXELITEElitefinal."%)",
							"salery" => $FLEXELITEElitefinal
						)
						,
						array(
							"range" => "CASHBACK PLUS (".$CASHBACKPLUSfinal."%)",
							"salery" => $CASHBACKPLUSfinal
						)
						,
						array(
							"range" => "SKYWARDS BLACK (".$SKYWARDSBLACKfinal."%)",
							"salery" => $SKYWARDSBLACKfinal
						)
						,
						array(
							"range" => "SKYWARDS INFINITE (".$SKYWARDSINFINITEfinal."%)",
							"salery" => $SKYWARDSINFINITEfinal
						)
						,
						array(
							"range" => "ETIHAD GUEST (".$ETIHADGUESTfinal."%)",
							"salery" => $ETIHADGUESTfinal
						)
						,
						array(
							"range" => "EMARATI (".$EMARATIfinal."%)",
							"salery" => $EMARATIfinal
						)
						,
						array(
							"range" => "SKYWARDS SIGNATURE (".$SKYWARDSSIGNATUREfinal."%)",
							"salery" => $SKYWARDSSIGNATUREfinal
						)
						,
						array(
							"range" => "CASHBACK PLATINUM (".$CASHBACKPLATINUMfinal."%)",
							"salery" => $CASHBACKPLATINUMfinal
						)
						,
						array(
							"range" => "SYWARDS BLACK (".$SYWARDSBLACKfinal."%)",
							"salery" => $SYWARDSBLACKfinal
						)
						,
						array(
							"range" => "SKYWARS BLACK (".$SKYWARSBLACKfinal."%)",
							"salery" => $SKYWARSBLACKfinal
						)
						,
						array(
							"range" => "FELX (".$FELXfinal."%)",
							"salery" => $FELXfinal
						)
					);
		$graphdata= json_encode($graphArray);
	  //print_r($graphdata);//exit;
	   
	   
	   
	   
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
		//$this->processorSelecteddata = $processorSelected;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.EIB.performanceeibplasticdistrbution');
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
				$name = DepartmentFormEntry::where("team",$r)->first()->team;
			}
			else
			{
				$name = $name.','.DepartmentFormEntry::where("team",$r)->first()->team;
			}
		}
		return $name;
	}	
	
}
