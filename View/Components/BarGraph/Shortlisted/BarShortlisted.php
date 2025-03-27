<?php

namespace App\View\Components\BarGraph\Shortlisted;
require_once "/srv/www/htdocs/core/autoload.php";
use Illuminate\View\Component;
use App\Models\Entry\Employee;
use Request;

use App\Models\Dashboard\WidgetCreation;

use App\Models\Dashboard\Widgetlayouts\WidgetBarShortlisted;
use App\Models\Job\JobOpening;
use App\Models\Company\Department;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\Employee\Employee_details;
use Session;
class BarShortlisted extends Component
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
	public $filterTypeShortlist;
	public $from_salesTime_shortlist;
	public $to_salesTime_shortlist;
	public $approvedBy;
	public $approvedBySelected;
	public $jobOpeningselectedList;
	public $jobOpeningLists;
	public $departmentLists;
	public $DepartmentSelected;
	
    public function __construct($widgetId)
    {
        $widget_name = WidgetCreation::where("id",$widgetId)->first()->widget_name;
		
		$widgetData = WidgetBarShortlisted::where("widget_id",$widgetId)->first();
		
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][job_opening]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][job_opening]') != NULL)	
		{
			$jobOpeningArray = Request::session()->get('widgetFilterHiring['.$widgetId.'][job_opening]');
		}
		else
		{
			$jobOpeningArray = explode(",",$widgetData->job_opening);
		}
		
		$this->jobOpeningselectedList = $jobOpeningArray;
		//print_r($jobOpeningArray);exit;
		$graphArray = array();
		$index = 0;
		$colorCode = array("#e87454","#e8be54","#bce854","#77f7d7","#5c8696","#3e545c","#2d308c","#80238c","#d9b6de","#876f7d","#9e9e9e","#de3c3c","#420f0f","#b6dbad","#7ad164");
		foreach($jobOpeningArray as $_opening)
		{
			$graphArray[$index]['Job Opening'] = $this->getJobOpeningName($_opening);
			$graphArray[$index]['Total'] = $this->getShortlisted($_opening,$widgetId);
			$graphArray[$index]['job_id'] = $_opening;
			//echo $index;exit;
			//$graphArray[$index]['color'] = $colorCode[$index];
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
			$this->recruitersSelected = array();
		}
		
		
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]') != NULL)	
		{
			$this->filterTypeShortlist = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		}
		else
		{
			$this->filterTypeShortlist = '';
		}
		
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]') != NULL)	
		{
			$this->from_salesTime_shortlist = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
		}
		else
		{
			$this->from_salesTime_shortlist = '';
		}
		
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]') != NULL)	
		{
			$this->to_salesTime_shortlist = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
		}
		else
		{
			$this->to_salesTime_shortlist = '';
		}
		if(Request::session()->get('widgetFiltershortlistedDept['.$widgetId.']') != '' && Request::session()->get('widgetFiltershortlistedDept['.$widgetId.']') != NULL)	
		{
			$this->DepartmentSelected = explode(",",Request::session()->get('widgetFiltershortlistedDept['.$widgetId.']'));
		}
		else
		{
			$this->DepartmentSelected ='';
		}
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		
		/* echo "<pre>";
		print_r($graphArray);
		exit;  */
			$recruiters = RecruiterDetails::where("status",1)->get();
			$recruiterCategory = RecruiterCategory::where("status",1)->get();
		/* 	$this->recruiterCategorySelected = '';
			$this->recruitersSelected = ''; */
			$this->widgetName = $widget_name;
			$this->widgetId = $widgetId;
			$this->widgetgraphData = $graphArray;
			$this->recruiters = $recruiters;
			$this->recruiterCategory = $recruiterCategory;
			$this->departmentLists = $departmentLists;
			
			$this->jobOpeningLists = JobOpening::where("status",1)->get();
		
			$approvedLists = DocumentCollectionDetails::groupBy('interview_approved_by')
					->selectRaw('count(*) as total, interview_approved_by')
					->get();
			$this->approvedBy = $approvedLists;
			
			
			if(Request::session()->get('widgetFilterHiring['.$widgetId.'][shortlist_by]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][shortlist_by]') != NULL)	
				{
					$this->approvedBySelected = Request::session()->get('widgetFilterHiring['.$widgetId.'][shortlist_by]');
				}
				else
				{
					$this->approvedBySelected = '';
				}
			
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.bargraph.bar_shortlisted');
    }
	
	Private function getShortlisted($jobId,$widgetId)
	{
		/* $currentDate = date("Y-m-d");
		$date30DaysBack = date("Y-m-d",strtotime("-30 days")); */
		
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
				$whereraw .= " AND created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " AND created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
		}
		
		
		if(Request::session()->get('widgetFilterHiring['.$widgetId.'][shortlist_by]') != '' && Request::session()->get('widgetFilterHiring['.$widgetId.'][shortlist_by]') != NULL)	
				{
					$shortlist_by = Request::session()->get('widgetFilterHiring['.$widgetId.'][shortlist_by]');
					if($whereraw == '')
						{
							$whereraw = "interview_approved_by IN (".$shortlist_by.")";
						}
						else
						{
							$whereraw .= " AND interview_approved_by IN (".$shortlist_by.")";
						}
				}
		if(Request::session()->get('widgetFiltershortlistedDept['.$widgetId.']') != '' && Request::session()->get('widgetFiltershortlistedDept['.$widgetId.']') != NULL  && Request::session()->get('widgetFiltershortlistedDept['.$widgetId.']') !=1)
		{
			$deptIds =  Request::session()->get('widgetFiltershortlistedDept['.$widgetId.']');
			
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
		if($whereraw  != '')
		{
			
		return DocumentCollectionDetails::where("job_opening",$jobId)->whereRaw($whereraw)->where("backout_status",1)->get()->count();
		
		}
		else
		{
			
			return DocumentCollectionDetails::where("job_opening",$jobId)->where("backout_status",1)->get()->count();
		}		
					
					
			
		
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
	
	
	public static function getRecruiterDetails($rid)
	{
		return RecruiterDetails::where("id",$rid)->first()->name;
	}

}
