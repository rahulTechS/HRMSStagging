<?php

namespace App\View\Components\Eibtest;
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


class EibTest extends Component
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
		//print_r( $widget_name);exit;
		
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
			   $fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
			
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
		
		
		
		//echo $whereraw;
		if($whereraw != '')
		{
			//echo "h1";
		$totaldata= EIBDepartmentFormEntry::select("application_date")->whereRaw($whereraw)->groupBy('application_date')->get();		
		}
			
		else
		{
			//echo "h2";
		$totaldata= EIBDepartmentFormEntry::select("application_date")->groupBy('application_date')->get();	

		}
	  
		$graphdata= $totaldata;
		//echo count($totaldata);
	 // print_r($graphdata);exit;
	   
	   
	   
	   
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
        return view('components.EibTest.eibtest');
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
	public static function getTotalsubmission($application_date,$widgetId)
	{
		//echo $application_date;exit;
		//return EIBDepartmentFormEntry::where("application_date",$application_date)->get()->count();
		
		$assignarray=array();
		$datacount=EIBDepartmentFormEntry::where("application_date",$application_date)->get();
		
		foreach($datacount as $dataget){
			
			$assignarray[]=$dataget->id;
			
		}
		//print_r($assignarray);exit;
		return count($assignarray);
		
	}
      public static function getTeamListsSelectedName($dept){
		//$departmentName = Department::where("id",$data->department)->first()->department_name;
		$name = '';
		foreach($dept as $r)
		{
			if($name == '')
			{
				$name = EIBDepartmentFormEntry::where("tl_name",$r)->first()->tl_name;
			}
			else
			{
				$name = $name.','.EIBDepartmentFormEntry::where("tl_name",$r)->first()->tl_name;
			}
		}
		return $name;
	}	
	

public static function getTotalsubmissionbooking($team,$widgetId)
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
		return $totaldata= EibBankMis::select("id")->where("matched_status",1)->where("final_decision","Approve")->whereRaw($whereraw)->where("tl_name",$team)->get()->count();
		}
			
		else
		{
			//echo "h2";
		return $totaldata=EibBankMis::select("id")->where("matched_status",1)->where("final_decision","Approve")->whereRaw($whereraw)->get()->count();	

		}
		//print_r($totaldata);exit;
		
		
	}
}