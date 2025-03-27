<?php

namespace App\View\Components\MyTeam;
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


class ChangeStatus extends Component
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
	public $EmpName;
	public $departmentLists;
	public $empIdList;
	
	
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
				$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
		}
		else{
			/*$toDate = date("Y-m-d");
			$fromDate = date("Y").'-'.date("m").'-'.'01';
			if($whereraw == '')
			{
				$whereraw = "created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}
			else
			{
				$whereraw .= " And created_at >= '".$fromDate."' and created_at <= '".$toDate."'";
			}*/
			
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
		if(!empty(Request::session()->get('cname_emp_filter_inner_list['.$widgetId.']')) && Request::session()->get('cname_emp_filter_inner_list['.$widgetId.']')!= 'All')
				{
					$cname = Request::session()->get('cname_emp_filter_inner_list['.$widgetId.']');
					$cnameArray = explode(",",$cname);
					 $selectedFilter['CNAME'] = $cname;
					 $namefinalarray=array();
					 foreach($cnameArray as $namearray){
						 $namefinalarray[]="'".$namearray."'";
						 
						 
					 }
					 //print_r($namefinalarray);exit;
					 $finalcname=implode(",", $namefinalarray);
					 if($whereraw == '')
					{
						$whereraw = 'emp_name IN('.$finalcname.')';
					}
					else
					{
						$whereraw .= ' And emp_name IN('.$finalcname.')';
					}
				}
				if(!empty(Request::session()->get('widgetFilteronboardDept['.$widgetId.']')) && Request::session()->get('widgetFilteronboardDept['.$widgetId.']') != 'All')
				{
					$dept_id = Request::session()->get('widgetFilteronboardDept['.$widgetId.']');
					 
					 if($whereraw == '')
					{
						$whereraw = 'department IN ('.$dept_id.')';
					}
					else
					{
						$whereraw .= ' And department IN ('.$dept_id.')';
					}
				}
				if(!empty(Request::session()->get('empid_emp_filter_inner_list['.$widgetId.']')) && Request::session()->get('empid_emp_filter_inner_list['.$widgetId.']')!= 'All')
				{
					
					Request::session()->get('empid_emp_filter_inner_list['.$widgetId.']');
					//exit;
					
					$empid = Request::session()->get('empid_emp_filter_inner_list['.$widgetId.']');
					$empArray = explode(",",$empid);
					 $empfinalarray=array();
					 foreach($empArray as $_emparray){
						 $onboardempdata=EmployeeOnboardData::where("emp_id",$_emparray)->first();

						 $onboardemplogdata=EmployeeOnboardLogdata::where("emp_id",$_emparray)->first();



						 if($onboardempdata!='' || $onboardemplogdata!='')
						 {
							  $empfinalarray[]=$onboardempdata->document_id;
							  
						 }
						
						 else{
							
							 $empdata=Employee_details::where("emp_id",$_emparray)->first();


							 if($empdata!=''){

								if($empdata->document_collection_id!='')
								{
									$empfinalarray[]=$empdata->document_collection_id; 
								}
								
								
							 }
							 
						 }
						
						 
						 
					 }
					 //print_r($empfinalarray);
					
					 
					 if (count($empfinalarray) === 0) 
					 {
						// list is empty.
						//echo "empty";
				   	 }
					 else
					 {
						$finalempid=implode(",", $empfinalarray);
						if($whereraw == '')
						{
							$whereraw = 'id IN('.$finalempid.')';
						}
						else
						{
							$whereraw .= ' And id IN('.$finalempid.')';
						}
					 }
					//  echo "Hello".$whereraw;
					//  exit;
					// exit;
					 
				}
		
		$todayDate = date('Y-m-d');
		if($whereraw == '')
					{
						$whereraw = 'sort_date>= "'.$todayDate.'"';
					}
					else
					{
						$whereraw .= ' And sort_date>= "'.$todayDate.'"';
					}
					
					$empsessionId=Request::session()->get('EmployeeId');
		$user=Employee::where("id",$empsessionId)->first();
		if($user!=''){
			$empdata=Employee_details::where("emp_id",$user->employee_id)->where("job_function",3)->first();
					   //print_r($empdata);exit;
					   if($empdata!=''){
						$tldata= Employee_details::where("tl_id",$empdata->id)->get();
						if($tldata!=''){
							$finaldocidarray=array();
							foreach($tldata as $_tldata){
								if($_tldata->document_collection_id!=''){
									$finaldocidarray[]=$_tldata->document_collection_id;
								}
								}
								$finalids=implode(",",$finaldocidarray);
								if($whereraw == '')
								{
								$whereraw = 'id IN('.$finalids.')';
								}
								else
								{
									$whereraw .= ' AND id IN('.$finalids.')';
								}

						}
						
						
					   }
		}
					   //echo $whereraw;exit;
					   if(Request::session()->get('widgetFilterBYHomeChangeStatus['.$widgetId.']') != '' && Request::session()->get('widgetFilterBYHomeChangeStatus['.$widgetId.']') =="Change Status")
						{
						  if($whereraw != '')
							{
								//echo "hello";exit;
								$documentCollectiondetaildata = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->where("sort_dateBY","Change Status")->whereRaw($whereraw)->orderByRaw("-sort_date DESC")->get();
								//$reportsCountdeadline = DocumentCollectionDetails::whereRaw($whereraw)->whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->get()->count();
								
							}
							else
							{
								//echo "hello1";
								$documentCollectiondetaildata = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->whereRaw($whereraw)->where("sort_dateBY","Change Status")->orderByRaw("-sort_date DESC")->get();
								//$reportsCountdeadline = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->get()->count();
							}
						}
						else if(Request::session()->get('widgetFilterBYHomeStamping['.$widgetId.']') != '' && Request::session()->get('widgetFilterBYHomeStamping['.$widgetId.']') =="Stamping")
						{
							if($whereraw != '')
							{
								//echo "hello";exit;
								$documentCollectiondetaildata = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->where("sort_dateBY","Stamping Deadline")->whereRaw($whereraw)->orderByRaw("-sort_date DESC")->get();
								//$reportsCountdeadline = DocumentCollectionDetails::whereRaw($whereraw)->whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->get()->count();
								
							}
							else
							{
								//echo "hello1";
								$documentCollectiondetaildata = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->whereRaw($whereraw)->where("sort_dateBY","Stamping Deadline")->orderByRaw("-sort_date DESC")->get();
								//$reportsCountdeadline = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->get()->count();
							}
							
						}
						else if(Request::session()->get('widgetFilterBYHomeDateEntry['.$widgetId.']') != '' && Request::session()->get('widgetFilterBYHomeDateEntry['.$widgetId.']') =="Date Entry")
						{
							if($whereraw != '')
							{
								//echo "hello";exit;
								$documentCollectiondetaildata = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->where("sort_dateBY","Date of Entry")->whereRaw($whereraw)->orderByRaw("-sort_date DESC")->get();
								//$reportsCountdeadline = DocumentCollectionDetails::whereRaw($whereraw)->whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->get()->count();
								
							}
							else
							{
								//echo "hello1";
								$documentCollectiondetaildata = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->whereRaw($whereraw)->where("sort_dateBY","Date of Entry")->orderByRaw("-sort_date DESC")->get();
								//$reportsCountdeadline = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->get()->count();
							}
							
						}
						else{
							if($whereraw != '')
							{
								//echo "hello";exit;
								$documentCollectiondetaildata = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->whereIn("sort_dateBY",array("Stamping Deadline","Date of Entry","Change Status"))->whereRaw($whereraw)->orderByRaw("-sort_date DESC")->get();
								//$reportsCountdeadline = DocumentCollectionDetails::whereRaw($whereraw)->whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->get()->count();
								
							}
							else
							{
								//echo "hello1";
								$documentCollectiondetaildata = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->whereRaw($whereraw)->whereIn("sort_dateBY",array("Stamping Deadline","Date of Entry","Change Status"))->orderByRaw("-sort_date DESC")->get();
								//$reportsCountdeadline = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->get()->count();
							}
						}
							
							
							$finaldocidarray=array();
							foreach($documentCollectiondetaildata as $_documentCollectiondetails){
								$EMPdetailsdata =  Employee_details::where("document_collection_id",$_documentCollectiondetails->id)->first();
								if($EMPdetailsdata!='' && $EMPdetailsdata->offline_status==2){
									
								}
								else{
									$finaldocidarray[]=$_documentCollectiondetails->id;
								}

							}
							$documentCollectiondetails= DocumentCollectionDetails::whereIn("id",$finaldocidarray)->orderByRaw("-sort_date DESC")->get();
				
					   
			
			
					
					
					
					
					
					
					
					
					
					
		
		$graphdata= $documentCollectiondetails;
		
	   
	   
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
		$this->EmpName = DocumentCollectionDetails::groupBy('emp_name')->selectRaw('count(*) as emp_name, emp_name')->get();
		$this->departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$a=Employee_details::get();		
		$b=EmployeeOnboardData::get();
		$this->empIdList=$a->merge($b);
		//$this->processorSelecteddata = $processorSelected;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.MyTeam.changestatus');
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


		public static function getDeptName($deptid)
		{
			
			
			$documentdata = Department::where("id",$deptid)->first(); 

			if($documentdata)
			{
				return $documentdata->department_name;
			}
			else
			{
				return "--";
			}
		}
}