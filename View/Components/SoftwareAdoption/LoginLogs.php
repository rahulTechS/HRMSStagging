<?php

namespace App\View\Components\SoftwareAdoption;
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
use App\Models\Onboarding\DocumentCollectionDetailsValues;
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
use App\Models\Onboarding\EmployeeIncrement;
use App\Models\Onboarding\EmployeeOnboardData;
use App\Models\Onboarding\EmployeeOnboardLogdata;
use App\Models\Entry\LoginLog;
use App\Models\JobFunction\JobFunction;

class LoginLogs extends Component
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
		//echo "wait...";exit;
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
				if($whereraw == '')
				{
					$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
				else
				{
					$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				if($whereraw == '')
				{
					$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
				else
				{
					$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-90 days"));
				if($whereraw == '')
				{
					$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
				else
				{
					$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
			}
			elseif($datatype == 'MAX')
			{
				//$toDate = date("Y-m-d");
				//$fromDate = date("Y-m-d",strtotime("-90 days"));
			}
			elseif($datatype == '5days')
			{
				$toDate = date("Y-m-d");
				$fromDate = date('Y-m-d', strtotime('-5 days', strtotime($toDate)));
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
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
					if($whereraw == '')
					{
						$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
					}
					else
					{
						$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
					}				
			}
			
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date('Y-m-d', strtotime('-5 days', strtotime($toDate)));
			if($whereraw == '')
			{
				$whereraw = "created_at >= '".$fromDate." 00:00:00' and created_at <= '".$toDate." 23:59:59'";
			}
			else
			{
				$whereraw .= " And created_at >= '".$fromDate." 00:00:00' and created_at <= '".$toDate." 23:59:59'";
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
		
		if(Request::session()->get('widgetFiltermolfunction['.$widgetId.']') != '' && Request::session()->get('widgetFiltermolfunction['.$widgetId.']') != NULL)
		{
			$jobfunction =  Request::session()->get('widgetFiltermolfunction['.$widgetId.']');
			
			
			if($whereraw == '')
			{
			$whereraw = 'job_function = "'.$jobfunction.'"';
			}
			else
			{
				$whereraw .= ' AND job_function = "'.$jobfunction.'"';
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
			$whereraw = 'emp_id IN('.$finalcname.')';
			}
			else
			{
				$whereraw .= ' AND emp_id IN('.$finalcname.')';
			}
		}
		
		if($whereraw != '')
				{
					//echo "hello";exit;
					if(Request::session()->get('widgetFiltermolfunction['.$widgetId.']') != '' && Request::session()->get('widgetFiltermolfunction['.$widgetId.']') != NULL)
					{
						$jobfunction =  Request::session()->get('widgetFiltermolfunction['.$widgetId.']');
						
						$documentCollectiondetaildata = LoginLog::whereRaw($whereraw)->where("job_function",$jobfunction)->groupBy('user_id')->get();
						
								
					}else{
					
					$documentCollectiondetaildata = LoginLog::whereRaw($whereraw)->whereIn("job_function",array(3,4))->groupBy('user_id')->get();
					//$reportsCountdeadline = DocumentCollectionDetails::whereRaw($whereraw)->whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->get()->count();
					}
				}
				else
				{
					//echo "hello1";
					$datatype1 = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
					if($datatype1 == 'MAX')
					{
					$documentCollectiondetaildata = LoginLog::whereIn("job_function",array(3,4))->groupBy('user_id')->get();	
					}
					else{
					$documentCollectiondetaildata = LoginLog::whereRaw($whereraw)->whereIn("job_function",array(3,4))->groupBy('user_id')->get();					
					}
					}
				//print_r($documentCollectiondetaildata);exit;
		$graphdata= $documentCollectiondetaildata;
		
	   
	   
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
		//$this->EmpName = DocumentCollectionDetails::groupBy('emp_name')->selectRaw('count(*) as emp_name, emp_name')->get();
		$TeamLists = LoginLog::select("emp_name","emp_id")->whereIn("job_function",array(3,4))->groupBy('emp_name')->get();
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
        return view('components.SoftwareAdoption.loginlog');
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
	




public static function getOnBoardEMPId($docId){
	$departmentDetails = EmployeeOnboardLogdata::where("document_id",$docId)->first();
		   if($departmentDetails != '' && $departmentDetails->emp_id!='')
		   {
			   return $departmentDetails->emp_id;
		   }
		   else
		   {
			    $empd=Employee_details::where("document_collection_id",$docId)->first();
				if($empd!=""){
					return $empd->emp_id;
				}else{
			   return '--';
				}
		   }
}
 public static function getRecruiterName($recruiterId)
	   {
		   $rec=RecruiterDetails::where("id",$recruiterId)->first();
		   if($rec!=''){
			 return $rec->name;  
		   }
		   else{
			 return "";  
		   }
	   }
	   public static function getChangeStatus($docId){
		 $Documentdata = DocumentCollectionDetailsValues::where("document_collection_id",$docId)->where("attribute_code",66)->first(); 
		if($Documentdata != '')
		   {
			    return date("d M Y",strtotime($Documentdata->attribute_value)) ;
		   }
		   else
		   {
			    return "--";
		   }
		   
		}
	public static function getjobfunctionname($jobid){
		$departmentDetails = JobFunction::where("id",$jobid)->first();
		   if($departmentDetails != '')
		   {
				return $departmentDetails->name;
		   }
		   else
		   {
			   return '';
		   }
	}
public static function getTotalloginsdata($empid,$widgetId)
	{
		$whereraw = '';
		$datatype = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
		if($datatype != NULL && $datatype != '')
		{
			
			if($datatype == 'current_month')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y").'-'.date("m").'-'.'01';
				if($whereraw == '')
				{
					$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
				else
				{
					$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
				
			}
			elseif($datatype == 'last_month')
			{
				$fromDate= date('Y-m-d', strtotime('first day of last month'));


				$toDate= date('Y-m-d', strtotime('last day of last month'));
				if($whereraw == '')
				{
					$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
				else
				{
					$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
			}
			elseif($datatype == 'month_3')
			{
				$toDate = date("Y-m-d");
				$fromDate = date("Y-m-d",strtotime("-90 days"));
				if($whereraw == '')
				{
					$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
				else
				{
					$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
			}
			elseif($datatype == 'MAX')
			{
				//$toDate = date("Y-m-d");
				//$fromDate = date("Y-m-d",strtotime("-90 days"));
			}
			elseif($datatype == '5days')
			{
				$toDate = date("Y-m-d");
				$fromDate = date('Y-m-d', strtotime('-5 days', strtotime($toDate)));
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
				$fromdateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][from_salesTime]');
				$fromDate = date("Y-m-d",strtotime($fromdateValue));
				$todateValue = Request::session()->get('widgetFilterHiring['.$widgetId.'][to_salesTime]');
				$toDate = date("Y-m-d",strtotime($todateValue));
				if($whereraw == '')
				{
					$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
				else
				{
					$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
				}
				
			}
			
		}
		else{
			$toDate = date("Y-m-d");
			$fromDate = date('Y-m-d', strtotime('-5 days', strtotime($toDate)));
			if($whereraw == '')
			{
				$whereraw = "created_at >= '".$fromDate." 00:00:00' and created_at <= '".$toDate." 23:59:59'";
			}
			else
			{
				$whereraw .= " And created_at >= '".$fromDate." 00:00:00' and created_at <= '".$toDate." 23:59:59'";
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
			$whereraw = 'emp_id IN('.$finalcname.')';
			}
			else
			{
				$whereraw .= ' AND emp_id IN('.$finalcname.')';
			}
		}
		
		
		//echo $whereraw;exit;
		if($whereraw != '')
		{
			//echo "h1";
			
			$datatype2 = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
					if($datatype2 == 'MAX')
					{
					return $documentCollectiondetaildata = LoginLog::where("emp_id",$empid)->get()->count();	
					}
					else{
					return $documentCollectiondetaildata = LoginLog::whereRaw($whereraw)->where("emp_id",$empid)->get()->count();					
					}
		
			//return $documentCollectiondetaildata = LoginLog::whereRaw($whereraw)->where("emp_id",$empid)->get()->count();
		
		}
			
		else
		{
			//echo "h2";
		$datatype2 = Request::session()->get('widgetFilterHiring['.$widgetId.'][data_type]');
					if($datatype2 == 'MAX')
					{
					return $documentCollectiondetaildata = LoginLog::where("emp_id",$empid)->get()->count();	
					}
					else{
					return $documentCollectiondetaildata = LoginLog::whereRaw($whereraw)->where("emp_id",$empid)->get()->count();					
					}	

		}
		
		
	}	
	public static function getTeamListsSelectedNamelog($TeamListsSelected){
		//$departmentName = Department::where("id",$data->department)->first()->department_name;
		//print_r($TeamListsSelected);exit;
		$name = '';
		foreach($TeamListsSelected as $r)
		{
			
			if($name == '')
			{
				$fname = LoginLog::where("emp_id",$r)->first();
				if($fname!=''){
				$name=	$fname->emp_name;
				}
			}
			else
			{
				$name = $name.','.LoginLog::where("emp_id",$r)->first()->emp_name;
				
			}
		}
		return $name;
	}
}