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

class PerformanceCbdIncomeSegmentation extends Component
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
		$totaldata= DepartmentFormEntry::select("ref_no")->where("ref_no","!=",NULL)->where("form_id",2)->whereRaw($whereraw)->get();
		}
			
		else
		{
			//echo "h2";
		$totaldata= DepartmentFormEntry::select("ref_no")->where("ref_no","!=",NULL)->whereRaw($whereraw)->where("form_id",2)->get();	

		}
	   //echo count($totaldata);//exit;
	   $graphArray = array();
	   $range=array();
		$index = 0;
		$salary5to7total=0;
		$salary7to10total=0;
		$salary10to15total=0;
		$salary15to25total=0;
		$salary25to50total=0;
		$salary50plustotal=0;
		foreach($totaldata as $_totaldata)
		{
			if(Request::session()->get('widgetFilterBYBookings['.$widgetId.']') != '' && Request::session()->get('widgetFilterBYBookings['.$widgetId.']')=="Bookings" )
			{
				$status=array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC");
			$rangesal=CBDBankMis::select("declared_salary")->where("ref_no",$_totaldata->ref_no)->whereIn('Status',$status)->first();
			//print_r($rangesal);exit;
			if($rangesal!=''){
				$sal=$rangesal->declared_salary;
				if($sal<=7000){
					//echo $sal."r1";
				$salary5to7total=$salary5to7total+1;	
				}
				else if($sal>=7001 && $sal<=10000){
					//echo $sal."r2";
				$salary7to10total=$salary7to10total+1;	
				}
				else if($sal>=10001 && $sal<=15000){
					//echo $sal."r3";
				$salary10to15total=$salary10to15total+1;	
				}
				else if($sal>15001 && $sal<=25000){
					//echo $sal."r4";
				$salary15to25total=$salary15to25total+1;	
				}
				else if($sal>25001 && $sal<=50000){
					//echo $sal."r4";
				$salary25to50total=$salary25to50total+1;	
				}
				else if($sal>50000){
					//echo $sal."r4";
				$salary50plustotal=$salary50plustotal+1;	
				}
				else{
					
				}
			}	
			}
			else{
			//echo $_totaldata->application_id;//exit;
			$rangesal=CBDBankMis::select("declared_salary")->where("ref_no",$_totaldata->ref_no)->first();
			//print_r($rangesal);exit;
			if($rangesal!=''){
				$sal=$rangesal->declared_salary;
				if($sal<=7000){
					//echo $sal."r1";
				$salary5to7total=$salary5to7total+1;	
				}
				else if($sal>=7001 && $sal<=10000){
					//echo $sal."r2";
				$salary7to10total=$salary7to10total+1;	
				}
				else if($sal>=10001 && $sal<=15000){
					//echo $sal."r3";
				$salary10to15total=$salary10to15total+1;	
				}
				else if($sal>15001 && $sal<=25000){
					//echo $sal."r4";
				$salary15to25total=$salary15to25total+1;	
				}
				else if($sal>25001 && $sal<=50000){
					//echo $sal."r4";
				$salary25to50total=$salary25to50total+1;	
				}
				else if($sal>50000){
					//echo $sal."r4";
				$salary50plustotal=$salary50plustotal+1;	
				}
				else{
					
				}
			}
			}
			
			
		}
		$countd=count($totaldata);
		if($countd>=1){
			$totaldata=$countd;
		}else{
			$totaldata=1;
		}
			$salary5to7totalfinal=round(($salary5to7total/$totaldata)*100);
			$salary7to10totalfinal=round(($salary7to10total/$totaldata)*100);
			$salary10to15totalfinal=round(($salary10to15total/$totaldata)*100);
			$salary15to25totalfinal=round(($salary15to25total/$totaldata)*100);
			$salary25to50totalfinal=round(($salary25to50total/$totaldata)*100);
			$salary50plustotalfinal=round(($salary50plustotal/$totaldata)*100);
			//$graphArray=array();
			$graphArray = array(
						array(
							"range" => "5-7k (".$salary5to7totalfinal."%)",
							"salery" => $salary5to7totalfinal
						),
						array(
							"range" => "7-10k (".$salary7to10totalfinal."%)",
							"salery" => $salary7to10totalfinal
						),
						array(
							"range" => "10-15k (".$salary10to15totalfinal."%)",
							"salery" => $salary10to15totalfinal
						)
						,
						
						array(
							"range" => "15k-25k (".$salary15to25totalfinal."%)",
							"salery" => $salary15to25totalfinal
						)
						,
						array(
							"range" => "25-50k (".$salary25to50totalfinal."%)",
							"salery" => $salary25to50totalfinal
						)
						,
						array(
							"range" => "50k+ (".$salary50plustotalfinal."%)",
							"salery" => $salary50plustotalfinal
						)
					);
		$graphdata= json_encode($graphArray);
	  
	   
	   
	   
	   
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
		//$this->processorSelecteddata = $processorSelected;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Performance.performancecbdincomesegmentation');
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
