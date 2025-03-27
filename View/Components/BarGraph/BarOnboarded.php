<?php

namespace App\View\Components\BarGraph\Onboarded;
require_once "/srv/www/htdocs/core/autoload.php";
use Illuminate\View\Component;
use App\Models\Entry\Employee;
use Request;

use App\Models\Dashboard\WidgetCreation;

use App\Models\Dashboard\Widgetlayouts\WidgetBarOnboarded;
use App\Models\Job\JobOpening;
use App\Models\Company\Department;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\Employee\Employee_details;
use Session;
class BarOnboarded extends Component
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
	public $filterTypeOnboard;
	public $from_salesTime_onboard;
	public $to_salesTime_onboard;
	
    public function __construct($widgetId)
    {
       $widget_name = WidgetCreation::where("id",$widgetId)->first()->widget_name;
	   $widgetData = WidgetBarOnboarded::where("widget_id",$widgetId)->first();
	   $jobOpeningArray = explode(",",$widgetData->job_opening);
		$graphArray = array();
		$index = 0;
		$colorCode = array("#e87454","#e8be54","#bce854","#77f7d7","#5c8696","#3e545c","#2d308c","#80238c","#d9b6de","#876f7d","#9e9e9e","#de3c3c","#420f0f","#b6dbad","#7ad164");
		foreach($jobOpeningArray as $_opening)
		{
			$graphArray[$index]['Job Opening'] = $this->getJobOpeningName($_opening);
			$graphArray[$index]['Total'] = $this->getOnboarded($_opening,$widgetId);
			$graphArray[$index]['color'] = $colorCode[$index];
			$index++;
		}
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
			$this->recruitersSelected = '';
		}
		
		
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]') != NULL)	
		{
			$this->filterTypeOnboard = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		}
		else
		{
			$this->filterTypeOnboard = '';
		}
		
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]') != NULL)	
		{
			$this->from_salesTime_onboard = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
		}
		else
		{
			$this->from_salesTime_onboard = '';
		}
		
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]') != NULL)	
		{
			$this->to_salesTime_onboard = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
		}
		else
		{
			$this->to_salesTime_onboard = '';
		}
		
		
	   $recruiters = RecruiterDetails::where("status",1)->get();
	   $recruiterCategory = RecruiterCategory::where("status",1)->get();
	   $this->widgetName = $widget_name;
	   $this->widgetId = $widgetId;
	   $this->widgetgraphData = $graphArray;
	   $this->recruiters = $recruiters;
	   $this->recruiterCategory = $recruiterCategory;
	  
	   
	  
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.bargraph.bar_onboarded');
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
	
	
	Private static function getOnboarded($jobId,$widgetId)
	{
		
		$whereraw = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
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
				$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
		}
		
		if($whereraw  != '')
		{
			if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
			{
				$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
				
				$recruiterIdsArray = explode(",",$recruiterIds);
				return DocumentCollectionDetails::where("job_opening",$jobId)->whereIn("recruiter_name",$recruiterIdsArray)->where("onboard_status",2)->whereRaw($whereraw)->get()->count();
			}
			else
			{
				return DocumentCollectionDetails::where("job_opening",$jobId)->whereRaw($whereraw)->where("onboard_status",2)->get()->count();
			}
		}
		else
		{
			if(Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]') != NULL)
			{
				$recruiterIds =  Request::session()->get('widgetFilterHiring['.$widgetId.'][recruiterId]');
				
				$recruiterIdsArray = explode(",",$recruiterIds);
				return DocumentCollectionDetails::where("job_opening",$jobId)->whereIn("recruiter_name",$recruiterIdsArray)->where("onboard_status",2)->get()->count();
			}
			else
			{
				return DocumentCollectionDetails::where("job_opening",$jobId)->where("onboard_status",2)->get()->count();
			}
		}
	}
	
	
}
