<?php

namespace App\Http\Controllers\EMPTraining;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Company\Subsidiary;
use App\Models\Company\Divison;
use App\Models\Company\Department;
use  App\Models\Attribute\Attributes;
use App\Models\Employee\Employee_attribute;
use App\Models\EmpProcess\Emp_joining_data;
use App\Models\EmpOffline\EmpOffline;
use App\Models\Employee\Employee_details;
use App\Models\Employee\EmployeeImportFiles;
use App\Models\Employee\EmployeeAttendanceModel;
use App\Models\Payroll\AnnualLeaveDetails;
use App\Models\Payroll\AnnualLeave;
use App\Models\MIS\WpCountries;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionAttributes;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use App\Models\Recruiter\Designation;
use App\Models\Job\JobOpening;
use Session;
use App\Models\EmpProcess\EmpChangeLog;
use App\Models\Entry\Employee;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\EmpProcess\JobFunctionPermission;
use App\Models\JobFunction\JobFunction;
use App\Models\TrainingQuestion\TrainingAnswer;
use App\Models\TrainingQuestion\TrainingRating;
use App\Models\TrainingCategory\EmpTraining;
use App\Models\Onboarding\TrainingType;


class EMPTrainingController extends Controller
{
    
	 public function EmpTraining(Request $request)
		{
			$Designation=Designation::where("status",1)->get();
			$empId=Employee_details::get();
			$EmpName=Employee_details::get();
			$empsessionIdGet=$request->session()->get('EmployeeId');
			$empDataGetting = Employee::where("id",$empsessionIdGet)->first();
			$design='';
			if($empDataGetting!='' && $empDataGetting->	employee_id!=''){
				$empid=Employee_details::where("emp_id",$empDataGetting->	employee_id)->first();
				if($empid!=''){
					$design=$empid->dept_id;
				}
			}
			$jobfun=JobFunction::where("status",1)->get();
			$recdata=RecruiterDetails::where("status",1)->get();
			return view("EMPTraining/manageEmpTraining",compact('Designation','empId','EmpName','design','jobfun','recdata'));
		}
	public function listingEmpcurrentTraining(Request $request)
		{
			$currentemp=EmpTraining::get();
			$empIds=array();
			foreach($currentemp as $empdata){
			$empIds[]=	$empdata->emp_present_ids;
				
			}
			
			$fdata=implode(",",$empIds);
			$convert=array_unique(explode(",",$fdata));
			$submitanswer=TrainingAnswer::groupBy('u_id')->selectRaw('count(*) as total, u_id')->get();
			if($submitanswer!=''){
			$ansarray=array();
			foreach($submitanswer as $_submitanswer){
			$ansarray[]=	$_submitanswer->u_id;
				
			}	
			}
			$finaldataarray=array_diff($convert,$ansarray);
			//print_r($finaldataarray);exit;
			$deptID = '';
			if(!empty($request->session()->get('offset_training')))
				{
					$paginationValue = $request->session()->get('offset_training');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				 $selectedFilter['EMPID'] = '';
				 $selectedFilter['f_name'] = '';
				 $selectedFilter['l_name'] = '';
				  $selectedFilter['designation'] = '';
				  $selectedFilter['sourcecode'] = '';
				  $selectedFilter['department'] = '';
				  $selectedFilter['vintage'] = '';
				  $selectedFilter['Location'] = '';
				  $selectedFilter['VisaUnderCompany'] = '';
				if(!empty($request->session()->get('dept_filter_for_emp')) && $request->session()->get('dept_filter_for_emp') != 'All'){
				
				//$filesource='';
					$deptID = $request->session()->get('dept_filter_for_emp');
					$selectedFilter['department'] = $deptID;
				if($deptID !=''){
				
					$whereraw = 'dept_id = "'.$deptID.'"';
				//$whereraw = 'type,Team Leader';
				}
				}
				else{
					//$empdetails = Employee_details::paginate($paginationValue);	
					//$reportsCount = Employee_details::get()->count();
					//$activeCount = Employee_details::where('status',1)->get()->count();
					//$inactiveCount = Employee_details::where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('training_empid_emp_filter_inner_list')) && $request->session()->get('training_empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('training_empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('training_fname_emp_filter_inner_list')) && $request->session()->get('training_fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('training_fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
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
				
				
				if(!empty($request->session()->get('training_design_emp_filter_inner_list')) && $request->session()->get('training_design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('training_design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('training_jobfunction_emp_filter_inner_list')) && $request->session()->get('training_jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('training_jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('training_RecruiterName_emp_filter_inner_list')) && $request->session()->get('training_RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('training_RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				
				
				
				if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					if($location!=''){
						$location=explode(',',$location);
					}
					else{
						$location='';
					}
					 $selectedFilter['Location'] = $location;
					 if($whereraw == '')
					{
						$attributedata= Employee_attribute::where('attribute_code','work_location')->whereIn('attribute_values',$location)->get();
						if($attributedata!=''){
						$locationarray=array();
						foreach($attributedata as $_location){
						$locationarray[]=$_location->emp_id;
						}
						$empiddetails=implode(",",$locationarray);
						$whereraw = 'emp_id IN('.$empiddetails.')';
						}
						
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','work_location')->whereIn('attribute_values',array($location))->get();
						if($attributedata!=''){
						$locationarray=array();
						foreach($attributedata as $_location){
						$locationarray[]=$_location->emp_id;
						}
						$empiddetails=implode(",",$locationarray);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And work_location = "'.$location.'"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
						}
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				$empIdArray = array();
				if($whereraw == '')
				{
				$appidGet = Employee_details::get();
				}
				else
				{
					
					$appidGet = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($appidGet as $_d)
				{
					if($_d->emp_id != NULL && $_d->emp_id != '')
					{
						$empIdArray[$_d->emp_id] = $_d->emp_id;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				$f_nameArray = array();
				if($whereraw == '')
				{
				$f_namedata = Employee_details::get();
				}
				else
				{
					
					$f_namedata = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($f_namedata as $_f)
				{
					//echo $_f->first_name;exit;
					$f_nameArray[$_f->first_name] = $_f->first_name;
				}
				//print_r();exit;
				$l_nameArray = array();
				if($whereraw == '')
				{
				$l_namedata = Employee_details::get();
				}
				else
				{
					
					$l_namedata = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($l_namedata as $_lname)
				{
					//echo $_lname->last_name;exit;
					$l_nameArray[$_lname->last_name] = $_lname->last_name;
				}
				
				$departmentArray = array();
				if($whereraw == '')
				{
						$department = Department::where("status",1)->orderBy('id','DESC')->get();
				}
				else
				{
					$department =Department::where("status",1)->orderBy('id','DESC')->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$departmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$sourcecodeArray = array();
				if($whereraw == '')
				{
				$soursecode= Employee_details::get();
				}
				else
				{
					
					$soursecode = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($soursecode as $_scode)
				{
					//echo $_lname->last_name;exit;
					$sourcecodeArray[$_scode->source_code] = $_scode->source_code;
				}
				
				$designationArray = array();
				if($whereraw == '')
				{
				$designation= Employee_details::get();
				}
				else
				{
					
					$designation = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($designation as $_designation)
				{
					//echo $_lname->last_name;exit;
					if(!empty($_designation->job_role)){
					$designationArray[$_designation->job_role] = $_designation->job_role;
					}
				}
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = Employee_details::orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = DocumentCollectionDetails::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = Employee_details::whereRaw($whereraw)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				$LocationArray = array();
				if($whereraw == '')
				{
				$loc= Employee_details::get();
				}
				else
				{
					
					$loc = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($loc as $_location)
				{
					//echo $_lname->last_name;exit;
					if($_location->work_location !=''){
					$LocationArray[$_location->work_location] = $_location->work_location;
					}
				}
				
				$VisaUnderCompany = array();
				if($whereraw == '')
				{
				//$empdata= Employee_details::get();
				$Collection  = Employee_details::get();
				if(!empty($Collection)){
				$empid=array();
				foreach($Collection as $_coll)
				{
					$empid[]=$_coll->emp_id;										
				}
	
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
				}
				}
				else
				{
					$Collection = Employee_details::whereRaw($whereraw)->get();
					$empid=array();
					foreach($Collection as $_coll)
					{
					$empid[]=$_coll->emp_id;					
					
					}
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
					 //print_r($empattributesMod);exit;
					
				}		
				
				//print_r($finaldata);
				if(!empty($empattributesMod)){
				foreach($empattributesMod as $_companyvisa)
				{
				
					if($_companyvisa->attribute_values=='-' || $_companyvisa->attribute_values=='' || $_companyvisa->attribute_values=='NULL'){
						
					}else{
					$VisaUnderCompany[$_companyvisa->attribute_values] = $_companyvisa->attribute_values;
					}
					
				}
				}
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 96 || $empsessionId== 97 || $empsessionId== 123){
					$interviewarr=array(9);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
					$interviewarr=array(8,36,43);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else{
					/*nothings to do*/
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'dept_id IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND dept_id IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}				   
				}
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->whereIn("id",$finaldataarray)->where("offline_status",1)->paginate($paginationValue);
				$reportsCount = Employee_details::whereRaw($whereraw)->whereIn("id",$finaldataarray)->where("offline_status",1)->get()->count();
					$activeCount = Employee_details::whereRaw($whereraw)->whereIn("id",$finaldataarray)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCount = Employee_details::whereRaw($whereraw)->whereIn("id",$finaldataarray)->where("offline_status",1)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::orderBy("id","DESC")->whereIn("id",$finaldataarray)->where("offline_status",1)->paginate($paginationValue);
					$reportsCount = Employee_details::where("offline_status",1)->whereIn("id",$finaldataarray)->get()->count();	
					$activeCount = Employee_details::where('status',1)->whereIn("id",$finaldataarray)->where("offline_status",1)->get()->count();
					$inactiveCount = Employee_details::where('status',2)->whereIn("id",$finaldataarray)->where("offline_status",1)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/listingEmpcurrentTraining'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));
			
			return view("EMPTraining/listingEmpTraining",compact('VisaUnderCompany','LocationArray','VintageArray','empdetails','paginationValue','departmentLists','deptID','reportsCount','empIdArray','selectedFilter','f_nameArray','l_nameArray','departmentArray','sourcecodeArray','designationArray','activeCount','inactiveCount'));
		
				
		}
	public function addTrainingPanel()
		{
			$departmentArray = Department::where("status",1)->get();
			$trainingCategoryArray = TrainingType::where("status",1)->get();
			$empArray = Employee_details::where("status",1)->get();
			return view("TrainingCategory/addTrainingPanel",compact('departmentArray','trainingCategoryArray','empArray'));
		}
		
    public function editEmpTraining($trainingId)
		{
			$departmentArray = Department::where("status",1)->get();
			$trainingCategoryArray = TrainingType::where("status",1)->get();
			$empArray = Employee_details::where("status",1)->get();
			$empTraining = EmpTraining::where("id",$trainingId)->first();
			return view("TrainingCategory/editEmpTraining",compact('departmentArray','trainingCategoryArray','empArray','empTraining'));
		}
       public function TrainingCategory(Request $req)
	   {
		  $filterList = array();
		  $filterList['name'] = '';
		  $filterList['status'] = '';
		  
		  $TrainingCategoryDetails = TrainingCategory::orderBy("id","DESC")->where("status",1);
		  
		  if(!empty($req->session()->get('name')))
			{
			
				$name = $req->session()->get('name');
				$filterList['name'] = $name;
				$TrainingCategoryDetails = $TrainingCategoryDetails->where("name","like","%".$name."%");
			}
		 
		 if(!empty($req->session()->get('status')))
			{
			
				$status = $req->session()->get('status');
				$filterList['status'] = $status;
				$TrainingCategoryDetails = $TrainingCategoryDetails->where("status",$status);
			}
			
		  $TrainingCategoryDetails = $TrainingCategoryDetails->get();
		  return view("TrainingCategory/TrainingCategory",compact('TrainingCategoryDetails','filterList'));
	   }
	   
	   public function addTrainingCategory()
	   {
		   return view("TrainingCategory/addTrainingCategory");
	   }
	   
	   public function addTrainingCategoryPost(Request $request)
	   {
		   $parameterInput = $request->input();
		  //print_r($parameterInput);exit;
		   $jobOpeningMod = new TrainingCategory();
			$jobOpeningMod->name = $parameterInput['TrainingCategory']['name'];			
			$jobOpeningMod->status = $parameterInput['TrainingCategory']['status'];
			$jobOpeningMod->save();
			$request->session()->flash('message','Training Category Saved.');
			return redirect('TrainingCategory');
	   }
	   
	 
	   	   
	   public function updateTrainingCategory(Request $request)
	   {
		    $TrainingCategoryId = $request->id;
		    $TrainingCategoryDetails = TrainingCategory::where("id",$TrainingCategoryId)->first();
			
			return view("TrainingCategory/updateTrainingCategory",compact('TrainingCategoryDetails'));
	   }
	   
	   public function updateTrainingCategoryPost(Request $request)
	   {
		   $parameterMeters = $request->input();
		  
		    $datas = $parameterMeters['TrainingCategory'];
		    $TrainingCategoryUpdateMod = TrainingCategory::find($datas['id']);
		    $TrainingCategoryUpdateMod->name = $datas['name'];
		    $TrainingCategoryUpdateMod->status = $datas['status'];
			$TrainingCategoryUpdateMod->save();
			
			$request->session()->flash('message','Training Category Updated.');
			return redirect('TrainingCategory');
	   }
	   
	   public function deleteTrainingCategory(Request $request)
	   {
		     $TrainingCategoryId = $request->id;
			 $TrainingCategoryUpdateMod = TrainingCategory::find($TrainingCategoryId);
			 $TrainingCategoryUpdateMod->status = 3;
			 $TrainingCategoryUpdateMod->save();
			 $request->session()->flash('message','Training Category Deleted.');
			 return redirect('TrainingCategory');
	   }
	   
	   public function appliedFilterOnTrainingCategory(Request $request)
	   {
		   $selectedFilter = $request->input();		
		   $request->session()->put('name',$selectedFilter['name']);
		   $request->session()->put('status',$selectedFilter['status']);
		   return redirect('TrainingCategory');
	   }
	   
	   public function resetTrainingCategoryFilter(Request $request)
	   {
		  	
		   $request->session()->put('name',"");
		   
		   $request->session()->put('status',"");
		   return redirect('TrainingCategory');
	   }
	   public function saveEmployeeTraining(Request $request)
	   {
		   $parameterInput = $request->input();
		  /*  echo '<pre>';
		   print_r($parameterInput);
		   exit; */
		   $name = $parameterInput['name'];
		   $training_id = $parameterInput['training_id'];
		  
		   $emp_present_ids = implode(",",$parameterInput['emp_present_ids']);
		   $emp_present = count($parameterInput['emp_present_ids']);
		   $training_date = $parameterInput['training_date'];
		   $content_of_training = $parameterInput['content_of_training'];
		   $EmpTrainingModel = new EmpTraining();
		   $EmpTrainingModel->name = $name;
		   $EmpTrainingModel->training_id = $training_id;
		   
		   $EmpTrainingModel->emp_present_ids = $emp_present_ids;
		   $EmpTrainingModel->emp_present = $emp_present;
		   $EmpTrainingModel->training_date = date("Y-m-d",strtotime($training_date));
		   $EmpTrainingModel->content_of_training = $content_of_training;
		   $EmpTrainingModel->save();
		   echo "<p class='messT'>Training Save Successfully.</p>";
		   exit;
	   }
	   
	   public function updateEmployeeTraining(Request $request)
	   {
		   $parameterInput = $request->input();
		   $name = $parameterInput['name'];
		   $training_id = $parameterInput['training_id'];
		  
		   $emp_present_ids = implode(",",$parameterInput['emp_present_ids']);
		   $emp_present = count($parameterInput['emp_present_ids']);
		   $training_date = $parameterInput['training_date'];
		   $content_of_training = $parameterInput['content_of_training'];
		   $id = $parameterInput['id'];
		   $EmpTrainingModelUpdate = EmpTraining::find($id);
		   $EmpTrainingModelUpdate->name = $name;
		   $EmpTrainingModelUpdate->training_id = $training_id;
		  
		   $EmpTrainingModelUpdate->emp_present_ids = $emp_present_ids;
		   $EmpTrainingModelUpdate->emp_present = $emp_present;
		   $EmpTrainingModelUpdate->training_date = date("Y-m-d",strtotime($training_date));
		   $EmpTrainingModelUpdate->content_of_training = $content_of_training;
		   $EmpTrainingModelUpdate->save();
		   echo "<p class='messT'>Training Updated Successfully.</p>";
		   exit;
	   }
	   
	   public static function getEmpNameList($eids)
	   {
		   $eidArray = explode(",",$eids);
		   $nameEmp = '';
		   foreach($eidArray as $eid)
		   {
			   if($nameEmp != '')
			   {
				$nameEmp = $nameEmp.','.Employee_details::where("id",$eid)->first()->first_name;
			   }
			   else
			   {
				   $nameEmp = Employee_details::where("id",$eid)->first()->first_name;
			   }
		   }
		   return $nameEmp;
	   }
	   
	   public static function getTrainingCategory($tId)
	   {
		   $trainingModel =  TrainingType::where("id",$tId)->first();
		   if($trainingModel != '')
		   {
			   return $trainingModel->name;
		   }
		   else
		   {
			   return "-";
		   }
	   }
	   
	   public static function getDepartment($did)
	   {
		
		    $dModel =  Department::where("id",$did)->first();
		   if($dModel != '')
		   {
			   return $dModel->department_name;
		   }
		   else
		   {
			   return "-";
		   }
	   }
		public function setOffSetEMPTraining(Request $request,$setc)
		{
			$request->session()->put('offset_training',$setc);
			 //return redirect('listingEmpTraining');
		}
		public function trainingbyfilter(Request $request)
		{
			$parametersInput = $request->input();
			/* echo '<pre>';
			print_r($parametersInput);
			exit; */
			if(isset($parametersInput['tname']))
			{
			$tName = $parametersInput['tname'];
			}
			else
			{
				$tName ='';
			}
			if(isset($parametersInput['departmentT']))
			{
			$departmentT = $parametersInput['departmentT'];
			}
			else
			{
				$department ='';
				$departmentT ='';
			}
			if(isset($parametersInput['trainingC']))
			{
			$trainingC = $parametersInput['trainingC'];
			}
			else
			{
				$training ='';
				$trainingC ='';
			}
			
			if($departmentT != '')
			{
				$departmentTArray  = array_filter($departmentT);
				$department = implode(",",$departmentTArray);
			}
			if($trainingC != '')
			{
				$trainingCArray  = array_filter($trainingC);
				$training = implode(",",$trainingCArray);
			}
			
			/*  echo $tName;
			echo "<br />";
			echo $department;
			echo "<br />";
			echo $training;exit; */
			$request->session()->put('cname_training_filter_inner_list',$tName);
			$request->session()->put('department_training_filter_inner_list',$department);
			$request->session()->put('trainingC_training_filter_inner_list',$training);
			
			 return redirect('listingEmpTraining');
		}
		
		public function trainingbyfilterReset(Request $request)
		{
			$request->session()->put('cname_training_filter_inner_list','');
			$request->session()->put('department_training_filter_inner_list','');
			$request->session()->put('trainingC_training_filter_inner_list','');
			
			 return redirect('listingEmpTraining');
		}
		public function listingEmppendingTraining(Request $request)
		{
				//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('offset_training')))
				{
					$paginationValue = $request->session()->get('offset_training');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				 $selectedFilter['EMPID'] = '';
				 $selectedFilter['f_name'] = '';
				 $selectedFilter['l_name'] = '';
				  $selectedFilter['designation'] = '';
				  $selectedFilter['sourcecode'] = '';
				  $selectedFilter['department'] = '';
				  $selectedFilter['vintage'] = '';
				  $selectedFilter['Location'] = '';
				  $selectedFilter['VisaUnderCompany'] = '';
				if(!empty($request->session()->get('dept_filter_for_emp')) && $request->session()->get('dept_filter_for_emp') != 'All'){
				
				//$filesource='';
					$deptID = $request->session()->get('dept_filter_for_emp');
					$selectedFilter['department'] = $deptID;
				if($deptID !=''){
				
					$whereraw = 'dept_id = "'.$deptID.'"';
				//$whereraw = 'type,Team Leader';
				}
				}
				else{
					//$empdetails = Employee_details::paginate($paginationValue);	
					//$reportsCount = Employee_details::get()->count();
					//$activeCount = Employee_details::where('status',1)->get()->count();
					//$inactiveCount = Employee_details::where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('training_empid_emp_filter_inner_list')) && $request->session()->get('training_empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('training_empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('training_fname_emp_filter_inner_list')) && $request->session()->get('training_fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('training_fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
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
				
				//echo $whereraw;exit;
				if(!empty($request->session()->get('training_design_emp_filter_inner_list')) && $request->session()->get('training_design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('training_design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('training_jobfunction_emp_filter_inner_list')) && $request->session()->get('training_jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('training_jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('training_RecruiterName_emp_filter_inner_list')) && $request->session()->get('training_RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('training_RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				
				
				
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				if(!empty($request->session()->get('training_cat_emp_filter_inner_list')) && $request->session()->get('training_cat_emp_filter_inner_list') != 'All')
				{
					$catid = $request->session()->get('training_cat_emp_filter_inner_list');
					 if($catid==1){
						 $catdata = "created_at>='".date('Y-m-d', strtotime("-30 days"))."'" ;
					 }
					 else if($catid==2){
						$catdata = "created_at<='".date('Y-m-d', strtotime("-31 days"))."' and created_at>='".date('Y-m-d', strtotime("-60 days"))."'" ; 
					 }
					  else if($catid==3){
						$catdata = "created_at<='".date('Y-m-d', strtotime("-61 days"))."' and created_at>='".date('Y-m-d', strtotime("-90 days"))."'" ; 
					 }
					 else if($catid==4){
						$catdata = "created_at<='".date('Y-m-d', strtotime("-91 days"))."' and created_at>='".date('Y-m-d', strtotime("-120 days"))."'" ; 
					 }
					 else if($catid==5){
						$catdata = "created_at<='".date('Y-m-d', strtotime("-121 days"))."' and created_at>='".date('Y-m-d', strtotime("-150 days"))."'" ; 
					 }
					 else{
						 $catdata='';
					 }
					 
					 
					 
					 if($whereraw == '')
					{
						$whereraw = $catdata;
					}
					else
					{
						$whereraw .= ' And '.$catdata;
					}
				}
				
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					if($location!=''){
						$location=explode(',',$location);
					}
					else{
						$location='';
					}
					 $selectedFilter['Location'] = $location;
					 if($whereraw == '')
					{
						$attributedata= Employee_attribute::where('attribute_code','work_location')->whereIn('attribute_values',$location)->get();
						if($attributedata!=''){
						$locationarray=array();
						foreach($attributedata as $_location){
						$locationarray[]=$_location->emp_id;
						}
						$empiddetails=implode(",",$locationarray);
						$whereraw = 'emp_id IN('.$empiddetails.')';
						}
						
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','work_location')->whereIn('attribute_values',array($location))->get();
						if($attributedata!=''){
						$locationarray=array();
						foreach($attributedata as $_location){
						$locationarray[]=$_location->emp_id;
						}
						$empiddetails=implode(",",$locationarray);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And work_location = "'.$location.'"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
						}
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				$empIdArray = array();
				if($whereraw == '')
				{
				$appidGet = Employee_details::get();
				}
				else
				{
					
					$appidGet = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($appidGet as $_d)
				{
					if($_d->emp_id != NULL && $_d->emp_id != '')
					{
						$empIdArray[$_d->emp_id] = $_d->emp_id;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				$f_nameArray = array();
				if($whereraw == '')
				{
				$f_namedata = Employee_details::get();
				}
				else
				{
					
					$f_namedata = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($f_namedata as $_f)
				{
					//echo $_f->first_name;exit;
					$f_nameArray[$_f->first_name] = $_f->first_name;
				}
				//print_r();exit;
				$l_nameArray = array();
				if($whereraw == '')
				{
				$l_namedata = Employee_details::get();
				}
				else
				{
					
					$l_namedata = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($l_namedata as $_lname)
				{
					//echo $_lname->last_name;exit;
					$l_nameArray[$_lname->last_name] = $_lname->last_name;
				}
				
				$departmentArray = array();
				if($whereraw == '')
				{
						$department = Department::where("status",1)->orderBy('id','DESC')->get();
				}
				else
				{
					$department =Department::where("status",1)->orderBy('id','DESC')->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$departmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$sourcecodeArray = array();
				if($whereraw == '')
				{
				$soursecode= Employee_details::get();
				}
				else
				{
					
					$soursecode = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($soursecode as $_scode)
				{
					//echo $_lname->last_name;exit;
					$sourcecodeArray[$_scode->source_code] = $_scode->source_code;
				}
				
				$designationArray = array();
				if($whereraw == '')
				{
				$designation= Employee_details::get();
				}
				else
				{
					
					$designation = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($designation as $_designation)
				{
					//echo $_lname->last_name;exit;
					if(!empty($_designation->job_role)){
					$designationArray[$_designation->job_role] = $_designation->job_role;
					}
				}
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = Employee_details::orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = DocumentCollectionDetails::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = Employee_details::whereRaw($whereraw)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				$LocationArray = array();
				if($whereraw == '')
				{
				$loc= Employee_details::get();
				}
				else
				{
					
					$loc = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($loc as $_location)
				{
					//echo $_lname->last_name;exit;
					if($_location->work_location !=''){
					$LocationArray[$_location->work_location] = $_location->work_location;
					}
				}
				
				$VisaUnderCompany = array();
				if($whereraw == '')
				{
				//$empdata= Employee_details::get();
				$Collection  = Employee_details::get();
				if(!empty($Collection)){
				$empid=array();
				foreach($Collection as $_coll)
				{
					$empid[]=$_coll->emp_id;										
				}
	
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
				}
				}
				else
				{
					$Collection = Employee_details::whereRaw($whereraw)->get();
					$empid=array();
					foreach($Collection as $_coll)
					{
					$empid[]=$_coll->emp_id;					
					
					}
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
					 //print_r($empattributesMod);exit;
					
				}		
				
				//print_r($finaldata);
				if(!empty($empattributesMod)){
				foreach($empattributesMod as $_companyvisa)
				{
				
					if($_companyvisa->attribute_values=='-' || $_companyvisa->attribute_values=='' || $_companyvisa->attribute_values=='NULL'){
						
					}else{
					$VisaUnderCompany[$_companyvisa->attribute_values] = $_companyvisa->attribute_values;
					}
					
				}
				}
				
				$currentemp=EmpTraining::get();
				$empIds=array();
				foreach($currentemp as $empdata){
				$empIds[]=	$empdata->emp_present_ids;
					
				}
				
				$fdata=implode(",",$empIds);
				$convert=array_unique(explode(",",$fdata));
				
				$submitanswer=TrainingAnswer::groupBy('u_id')->selectRaw('count(*) as total, u_id')->get();
				if($submitanswer!=''){
				$ansarray=array();
				foreach($submitanswer as $_submitanswer){
				$ansarray[]=	$_submitanswer->u_id;
					
				}	
				}
				$finaldataarray=array_unique(array_merge($convert,$ansarray));
				if($whereraw ==''){
				$whereraw = "created_at>='".date('Y-m-d', strtotime("-150 days"))."'" ;
				}
				else{
					$whereraw .= " And created_at>='".date('Y-m-d', strtotime("-150 days"))."'" ;
				}
				
				//echo $whereraw;
				if($whereraw != '')
				{
				$empdetails = Employee_details::whereNotIn('id',$finaldataarray)->whereRaw($whereraw)->where("offline_status",1)->paginate($paginationValue);
				$reportsCountpending = Employee_details::whereNotIn('id',$finaldataarray)->whereRaw($whereraw)->where("offline_status",1)->get()->count();
					//echo $reportsCount ;exit;
					$activeCount = Employee_details::whereNotIn('id',$finaldataarray)->whereRaw($whereraw)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCount = Employee_details::whereNotIn('id',$finaldataarray)->whereRaw($whereraw)->where("offline_status",1)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::orderBy("id","DESC")->whereNotIn('id',$finaldataarray)->where("offline_status",1)->paginate($paginationValue);
					$reportsCountpending = Employee_details::where("offline_status",1)->whereNotIn('id',$finaldataarray)->get()->count();	
					$activeCount = Employee_details::where('status',1)->whereNotIn('id',$finaldataarray)->where("offline_status",1)->get()->count();
					$inactiveCount = Employee_details::where('status',2)->whereNotIn('id',$finaldataarray)->where("offline_status",1)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/listingEmppendingTraining'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));
			
			return view("EMPTraining/listingEmppendingTraining",compact('VisaUnderCompany','LocationArray','VintageArray','empdetails','paginationValue','departmentLists','deptID','reportsCountpending','empIdArray','selectedFilter','f_nameArray','l_nameArray','departmentArray','sourcecodeArray','designationArray','activeCount','inactiveCount'));
		
				
				
		}
		public function listingEmpcompleteTraining(Request $request)
		{
				//$request->session()->put('design_emp_filter_inner_list','');
			$deptID = '';
			if(!empty($request->session()->get('offset_training')))
				{
					$paginationValue = $request->session()->get('offset_training');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				 $selectedFilter['EMPID'] = '';
				 $selectedFilter['f_name'] = '';
				 $selectedFilter['l_name'] = '';
				  $selectedFilter['designation'] = '';
				  $selectedFilter['sourcecode'] = '';
				  $selectedFilter['department'] = '';
				  $selectedFilter['vintage'] = '';
				  $selectedFilter['Location'] = '';
				  $selectedFilter['VisaUnderCompany'] = '';
				if(!empty($request->session()->get('dept_filter_for_emp')) && $request->session()->get('dept_filter_for_emp') != 'All'){
				
				//$filesource='';
					$deptID = $request->session()->get('dept_filter_for_emp');
					$selectedFilter['department'] = $deptID;
				if($deptID !=''){
				
					$whereraw = 'dept_id = "'.$deptID.'"';
				//$whereraw = 'type,Team Leader';
				}
				}
				else{
					//$empdetails = Employee_details::paginate($paginationValue);	
					//$reportsCount = Employee_details::get()->count();
					//$activeCount = Employee_details::where('status',1)->get()->count();
					//$inactiveCount = Employee_details::where('status',2)->get()->count();
				}
				if(!empty($request->session()->get('training_empid_emp_filter_inner_list')) && $request->session()->get('training_empid_emp_filter_inner_list') != 'All')
				{
					$empId = $request->session()->get('training_empid_emp_filter_inner_list');
					 $selectedFilter['EMPID'] = $empId;
					 if($whereraw == '')
					{
						$whereraw = 'emp_id IN ('.$empId.')';
					}
					else
					{
						$whereraw .= ' And emp_id IN ('.$empId.')';
					}
				}
				if(!empty($request->session()->get('training_fname_emp_filter_inner_list')) && $request->session()->get('training_fname_emp_filter_inner_list') != 'All')
				{
					$cname = $request->session()->get('training_fname_emp_filter_inner_list');
					 $cnameArray = explode(",",$cname);
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
				
				
				if(!empty($request->session()->get('training_design_emp_filter_inner_list')) && $request->session()->get('training_design_emp_filter_inner_list') != 'All')
				{
					$design = $request->session()->get('training_design_emp_filter_inner_list');
					 $selectedFilter['designation'] = $design;
					 if($whereraw == '')
					{
						$whereraw = 'designation_by_doc_collection IN ('.$design.')';
					}
					else
					{
						$whereraw .= ' And designation_by_doc_collection IN('.$design.')';
					}
				}
				if(!empty($request->session()->get('training_jobfunction_emp_filter_inner_list')) && $request->session()->get('training_jobfunction_emp_filter_inner_list') != 'All')
				{
					$jobfunction = $request->session()->get('training_jobfunction_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'job_function IN ('.$jobfunction.')';
					}
					else
					{
						$whereraw .= ' And job_function IN('.$jobfunction.')';
					}
				}
				if(!empty($request->session()->get('training_RecruiterName_emp_filter_inner_list')) && $request->session()->get('training_RecruiterName_emp_filter_inner_list') != 'All')
				{
					$RecruiterName = $request->session()->get('training_RecruiterName_emp_filter_inner_list');
					 
					 if($whereraw == '')
					{
						$whereraw = 'recruiter IN ('.$RecruiterName.')';
					}
					else
					{
						$whereraw .= ' And recruiter IN('.$RecruiterName.')';
					}
				}
				/*if(!empty($request->session()->get('training_cat_emp_filter_inner_list')) && $request->session()->get('training_cat_emp_filter_inner_list') != 'All')
				{
					$catid = $request->session()->get('training_cat_emp_filter_inner_list');
					$ansarray=array();
					 if($catid==1){
						$tdata=TrainingAnswer::where("t_id",17)->get();
						if($tdata!=''){
						
						foreach($tdata as $_tdata){
							
								$ansarray[]=	$_tdata->u_id;
							
							
						}	
						}
						
					 }
					 else if($catid==2){
						$tdata=TrainingAnswer::where("t_id",19)->get();
						if($tdata!=''){
						
						foreach($tdata as $_tdata){
							
								$ansarray[]=	$_tdata->u_id;
							
							
						}	
						}
					 }
					  else if($catid==3){
						$tdata=TrainingAnswer::where("t_id",20)->get();
						if($tdata!=''){
						
						foreach($tdata as $_tdata){
							
								$ansarray[]=	$_tdata->u_id;
							
						}	
						} 
					 }
					 else if($catid==4){
						$tdata=TrainingAnswer::where("t_id",32)->get();
						if($tdata!=''){
						
						foreach($tdata as $_tdata){
							
								$ansarray[]=	$_tdata->u_id;
							
							
						}	
						} 
					 }
					 else{
						 $ansarray[]=0;
					 }
					 
					 //$ansarra=array_unique($ansarray);
					 $ansarray=implode(',',$ansarray);
					 
					 if($whereraw == '')
					{
						$whereraw = 'id IN ('.$ansarray.')';
					}
					else
					{
						$whereraw .= ' And id IN ('.$ansarray.')';
					}
				}
				
				echo $whereraw;*/
				if(!empty($request->session()->get('scode_emp_filter_inner_list')) && $request->session()->get('scode_emp_filter_inner_list') != 'All')
				{
					$scode = $request->session()->get('scode_emp_filter_inner_list');
					 $selectedFilter['sourcecode'] = $scode;
					 if($whereraw == '')
					{
						$whereraw = 'source_code like "%'.$scode.'%"';
					}
					else
					{
						$whereraw .= ' And source_code like "%'.$scode.'%"';
					}
				}if(!empty($request->session()->get('vintage_emp_filter_inner_list')) && $request->session()->get('vintage_emp_filter_inner_list') != 'All')
				{
					$vintage = $request->session()->get('vintage_emp_filter_inner_list');
					 $selectedFilter['vintage'] = $vintage;
					 if($whereraw == '')
					{
						if($vintage == '<10'){
						$whereraw = 'vintage_days >= 1 and vintage_days <9';
						}
						elseif($vintage == '10-20'){
						$whereraw = 'vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw = 'vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw = 'vintage_days >31';
						}
					}
					else
					{
						if($vintage == '<10'){
							$whereraw .= 'And vintage_days >= 1 and vintage_days <=9';							
						}
						elseif($vintage == '10-20'){
						$whereraw .= 'And vintage_days >= 10 and vintage_days <=20';
						}
						elseif($vintage == '20-30'){
						$whereraw .= 'And vintage_days >= 20 and vintage_days <=30';
						}
						else{
							$whereraw .= ' And vintage_days >31';
						}
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
				
				if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$location = $request->session()->get('location_emp_filter_inner_list');
					if($location!=''){
						$location=explode(',',$location);
					}
					else{
						$location='';
					}
					 $selectedFilter['Location'] = $location;
					 if($whereraw == '')
					{
						$attributedata= Employee_attribute::where('attribute_code','work_location')->whereIn('attribute_values',$location)->get();
						if($attributedata!=''){
						$locationarray=array();
						foreach($attributedata as $_location){
						$locationarray[]=$_location->emp_id;
						}
						$empiddetails=implode(",",$locationarray);
						$whereraw = 'emp_id IN('.$empiddetails.')';
						}
						
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','work_location')->whereIn('attribute_values',array($location))->get();
						if($attributedata!=''){
						$locationarray=array();
						foreach($attributedata as $_location){
						$locationarray[]=$_location->emp_id;
						}
						$empiddetails=implode(",",$locationarray);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And work_location = "'.$location.'"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
						}
					}
				}
				//echo $whereraw;//exit;
				if(!empty($request->session()->get('visacompany_emp_filter_inner_list')) && $request->session()->get('visacompany_emp_filter_inner_list') != 'All')
				{
					$companyvisa = $request->session()->get('visacompany_emp_filter_inner_list');
					 $selectedFilter['VisaUnderCompany'] = $companyvisa;
					 if($whereraw == '')
					{
					$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
					$vidacompany=array();
					foreach($attributedata as $_comp){
					$vidacompany[]=$_comp->emp_id;
					}
					$empiddetails=implode(",",$vidacompany);
					$whereraw = 'emp_id IN('.$empiddetails.')';
					//$whereraw = 'emp_id In("'.$vidacompany.'")';
					}
					else
					{
						$attributedata= Employee_attribute::where('attribute_code','company_name_issue_issued')->where('attribute_values',$companyvisa)->get();
						$vidacompany=array();
						foreach($attributedata as $_comp){
						$vidacompany[]=$_comp->emp_id;
						}
						$empiddetails=implode(",",$vidacompany);
						$whereraw .= ' And emp_id IN('.$empiddetails.')';
						//$whereraw .= ' And emp_id In"('.$vidacompany.')"';
						//$whereraw .= ' And vintage_days = "'.$vintage.'"';
					}
				}
						//echo $whereraw;//exit;		
				
				$empIdArray = array();
				if($whereraw == '')
				{
				$appidGet = Employee_details::get();
				}
				else
				{
					
					$appidGet = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($appidGet as $_d)
				{
					if($_d->emp_id != NULL && $_d->emp_id != '')
					{
						$empIdArray[$_d->emp_id] = $_d->emp_id;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				$f_nameArray = array();
				if($whereraw == '')
				{
				$f_namedata = Employee_details::get();
				}
				else
				{
					
					$f_namedata = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($f_namedata as $_f)
				{
					//echo $_f->first_name;exit;
					$f_nameArray[$_f->first_name] = $_f->first_name;
				}
				//print_r();exit;
				$l_nameArray = array();
				if($whereraw == '')
				{
				$l_namedata = Employee_details::get();
				}
				else
				{
					
					$l_namedata = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($l_namedata as $_lname)
				{
					//echo $_lname->last_name;exit;
					$l_nameArray[$_lname->last_name] = $_lname->last_name;
				}
				
				$departmentArray = array();
				if($whereraw == '')
				{
						$department = Department::where("status",1)->orderBy('id','DESC')->get();
				}
				else
				{
					$department =Department::where("status",1)->orderBy('id','DESC')->get();
					//$department = Employee_details::whereRaw($whereraw1)->get();
					
				}
				
				foreach($department as $_dptname)
				{
					//echo $_lname->last_name;exit;
					$departmentArray[$_dptname->id] = $_dptname->department_name;
				}
				$sourcecodeArray = array();
				if($whereraw == '')
				{
				$soursecode= Employee_details::get();
				}
				else
				{
					
					$soursecode = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($soursecode as $_scode)
				{
					//echo $_lname->last_name;exit;
					$sourcecodeArray[$_scode->source_code] = $_scode->source_code;
				}
				
				$designationArray = array();
				if($whereraw == '')
				{
				$designation= Employee_details::get();
				}
				else
				{
					
					$designation = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($designation as $_designation)
				{
					//echo $_lname->last_name;exit;
					if(!empty($_designation->job_role)){
					$designationArray[$_designation->job_role] = $_designation->job_role;
					}
				}
				$VintageArray = array();
				if($whereraw == '')
				{
					$ventArray = Employee_details::orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  
					  //$value=asort($value1);
					  //$min=min($value);
					  //$max=max($value);
					   $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31 ) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					  //print_r($finaldata);
					//$Vintage = DocumentCollectionDetails::whereIn('vintage_days',array_unique($ventList))->get();
				}
				else
				{
					//echo $whereraw;//exit;
					$ventArray = Employee_details::whereRaw($whereraw)->orderBy("id", "DESC")->get();
					$ventList = array(); 
					foreach($ventArray as $_vent)
					  {
					  $ventList[]  = $_vent->vintage_days;
					  }
					  $value=(array_unique($ventList));
					  //$min=min($value);
					  //$max=max($value);
					  $Vintage=array();
					  foreach($value as $data){
					  if ($data<=10) {
						  $Vintage[]="<10";
					  }
					  elseif($data>=11 && $data<=20) {
						  $Vintage[]="10-20";
					  }
					  elseif($data>=21 && $data<=30) {
						  $Vintage[]="21-30";
					  }
					  elseif($data>=31) {
						  $Vintage[]=">30";
					  }
					  }
					  $finaldata=array_unique($Vintage);
					
				}
				foreach($finaldata as $_vintage)
				{
					//echo $_f->first_name;exit;
					$VintageArray[$_vintage] = $_vintage;
				}
				
				
				$LocationArray = array();
				if($whereraw == '')
				{
				$loc= Employee_details::get();
				}
				else
				{
					
					$loc = Employee_details::whereRaw($whereraw)->get();
					
				}
				
				foreach($loc as $_location)
				{
					//echo $_lname->last_name;exit;
					if($_location->work_location !=''){
					$LocationArray[$_location->work_location] = $_location->work_location;
					}
				}
				
				$VisaUnderCompany = array();
				if($whereraw == '')
				{
				//$empdata= Employee_details::get();
				$Collection  = Employee_details::get();
				if(!empty($Collection)){
				$empid=array();
				foreach($Collection as $_coll)
				{
					$empid[]=$_coll->emp_id;										
				}
	
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
				}
				}
				else
				{
					$Collection = Employee_details::whereRaw($whereraw)->get();
					$empid=array();
					foreach($Collection as $_coll)
					{
					$empid[]=$_coll->emp_id;					
					
					}
					$empattributesMod = Employee_attribute::whereIn('emp_id',$empid)->where('attribute_code','company_name_issue_issued')->get();
					 //print_r($empattributesMod);exit;
					
				}		
				
				//print_r($finaldata);
				if(!empty($empattributesMod)){
				foreach($empattributesMod as $_companyvisa)
				{
				
					if($_companyvisa->attribute_values=='-' || $_companyvisa->attribute_values=='' || $_companyvisa->attribute_values=='NULL'){
						
					}else{
					$VisaUnderCompany[$_companyvisa->attribute_values] = $_companyvisa->attribute_values;
					}
					
				}
				}
				$empsessionId=$request->session()->get('EmployeeId');
				if($empsessionId== 96 || $empsessionId== 97 || $empsessionId== 123){
					$interviewarr=array(9);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else if($empsessionId== 94 || $empsessionId== 95 || $empsessionId== 111){
					$interviewarr=array(8,36,43);
					$interviewdetails=implode(",",$interviewarr);
					if($whereraw == '')
					{
					$whereraw = 'dept_id IN('.$interviewdetails.')';
					}
					else
					{
						$whereraw .= ' AND dept_id IN('.$interviewdetails.')';
					}
				}
				else{
					/*nothings to do*/
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				   if($departmentDetails != '')
				   {
					   $empdata=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					   if($empdata!=''){
						   if($whereraw == '')
							{
							$whereraw = 'dept_id IN('.$empdata->dept_id.')';
							}
							else
							{
								$whereraw .= ' AND dept_id IN('.$empdata->dept_id.')';
							}
						   //$dept=$empdata->dept_id;
					   }
				   }
					else{
						
					}				   
				}
				
				//print_r($sourcecodeArray);exit;
				//echo $whereraw;//exit;
				$submitanswer=TrainingAnswer::groupBy('u_id')->selectRaw('count(*) as total, u_id')->get();
				if($submitanswer!=''){
				$ansarray=array();
				foreach($submitanswer as $_submitanswer){
				$ansarray[]=	$_submitanswer->u_id;
					
				}	
				}
				if($whereraw != '')
				{
				$empdetails = Employee_details::orderBy("id","DESC")->whereRaw($whereraw)->whereIn('id', $ansarray)->where("offline_status",1)->paginate($paginationValue);
				$reportsCountcomplete = Employee_details::whereRaw($whereraw)->whereIn('id', $ansarray)->where("offline_status",1)->get()->count();
					$activeCount = Employee_details::whereRaw($whereraw)->whereIn('id', $ansarray)->where("offline_status",1)->where('status',1)->get()->count();
					$inactiveCount = Employee_details::whereRaw($whereraw)->whereIn('id', $ansarray)->where("offline_status",1)->where('status',2)->get()->count();				
				}
				else
				{
					$empdetails = Employee_details::orderBy("id","DESC")->whereIn('id', $ansarray)->where("offline_status",1)->paginate($paginationValue);
					$reportsCountcomplete = Employee_details::where("offline_status",1)->whereIn('id', $ansarray)->get()->count();	
					$activeCount = Employee_details::where('status',1)->whereIn('id', $ansarray)->where("offline_status",1)->get()->count();
					$inactiveCount = Employee_details::where('status',2)->whereIn('id', $ansarray)->where("offline_status",1)->get()->count();					
				}
			
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			$empdetails->setPath(config('app.url/listingEmpcompleteTraining'));
			Cache::put('empdetails', $empdetails, now()->addMinutes(30));
			
			return view("EMPTraining/listingEmpcompleteTraining",compact('VisaUnderCompany','LocationArray','VintageArray','empdetails','paginationValue','departmentLists','deptID','reportsCountcomplete','empIdArray','selectedFilter','f_nameArray','l_nameArray','departmentArray','sourcecodeArray','designationArray','activeCount','inactiveCount'));
		
				
		}
		public static function getnoofDays($empid){
			$empId = Employee_details::where("id",$empid)->first();
			if($empId!=''  && $empId->created_at!=''){
				
				$doj = $empId->created_at;
				$doj = str_replace("/","-",$doj);
				$date1 = date("Y-m-d",strtotime($doj));
				$date2 =  date("Y-m-d");
				$diff = abs(strtotime($date2)-strtotime($date1));
				return   round($diff / (60 * 60 * 24));
				//return $offset;
			}
		}
		public function addTrainingEMPdataFromList(Request $request){
			
			//print_r($request->input());exit;
			$visaapproved='';
			if($request->input('selectedIds')!=''){
			 
			 $visaapproved=implode(",", $request->input('selectedIds'));
			}
			$request->session()->put('departmentId_emptraining_filter_inner_list',$visaapproved);
			
		}
		public function searchbyempNameTraining(Request $request)
		{
			$selectedFilter = $request->input();
			//print_r($selectedFilter);exit;
			//$fname = $request->emp_filtername;
			$fname='';
			if($request->emp_filtername!=''){
			$fnamearray=array_filter($request->emp_filtername);		
			$fname=implode(",", $fnamearray);
			}
			
			$designation='';
			if($request->designationdata!=''){
			$designationarray=array_filter($request->designationdata);			
			$designation=implode(",", $designationarray);				
			}
			$empid='';
			if($request->empId!=''){
			$empIdarray=array_filter($request->empId);		
			$empid=implode(",", $empIdarray);
			}
			$jobfunction='';
			if($request->jobfunction!=''){
			$jobfunctionarray=array_filter($request->jobfunction);		
			$jobfunction=implode(",", $jobfunctionarray);
			}
			$RecruiterName='';
			if($request->RecruiterName!=''){
			$RecruiterNamearray=array_filter($request->RecruiterName);		
			$RecruiterName=implode(",", $RecruiterNamearray);
			}
			$tcatName='';
			if($request->tcat!=''){
					
			$tcatName=$request->tcat;
			}
			$request->session()->put('training_empid_emp_filter_inner_list',$empid);
			$request->session()->put('training_fname_emp_filter_inner_list',$fname);
			$request->session()->put('training_design_emp_filter_inner_list',$designation);
			$request->session()->put('training_jobfunction_emp_filter_inner_list',$jobfunction);
			$request->session()->put('training_RecruiterName_emp_filter_inner_list',$RecruiterName);			
			$request->session()->put('training_cat_emp_filter_inner_list',$tcatName);
			
			
			
				
		}
		public function empFilterresetTraining(Request $request)
		{
			
			$request->session()->put('training_fname_emp_filter_inner_list','');
			$request->session()->put('training_design_emp_filter_inner_list','');
			$request->session()->put('training_empid_emp_filter_inner_list','');
			$request->session()->put('training_jobfunction_emp_filter_inner_list','');
			$request->session()->put('training_RecruiterName_emp_filter_inner_list','');
			$request->session()->put('training_cat_emp_filter_inner_list','');
			  	
		}
		public static function getcompleteTrainingData($empid){
			$ans=TrainingAnswer::where("u_id",$empid)->first();
			if($ans!=''){
				$t_id=$ans->t_id;
				$catname=TrainingType::where("id",$t_id)->first();
				if($catname!=''){
					return $catname->name;
				}
				else{
					return '';
				}
				
			}
			else{
					return '';
				}
		}
		public static function getPendingTrainingData($empid){
			
			$data=EmpTraining::where("emp_present_ids",'LIKE',"%{$empid}%")->first();
			if($data!=''){
				return $data->name;
			}
			else{
				return '';
			}
			
		}
		public static function getPendingTrainingDate($empid){
			
			$data=EmpTraining::where("emp_present_ids",'LIKE',"%{$empid}%")->first();
			if($data!=''){
				return date("d M Y",strtotime($data->training_date));
			}
			else{
				return '';
			}
			
		}
		public static function getcompleteTrainingDate($empid){
			
			$data=TrainingAnswer::where("u_id",$empid)->first();
			if($data!=''){
				return date("d M Y",strtotime($data->created_at));
			}
			else{
				return '';
			}
			
		}
		public function exportEmpReportTraining(Request $request){
			$parameters = $request->input(); 
			
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'emp_training_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:O1');
			$sheet->setCellValue('A1', 'EMP List - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('First Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Middle Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Last Name'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Bank Code'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Local Contact Number'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Date of Joining'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Designation'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Work Location'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Department'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('TL Name'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Salary'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Recruiter'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Job Function'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				//echo $sid;
				 $misData = Employee_details::where("id",$sid)->first();
				 $tldata=$misData->tl_id;
				 $tlname='';
				 if($tldata!=''){
				 $tld=Employee_details::where("id",$tldata)->first();
				 if($tld!=''){
				 $tlname=$tld->first_name.' '.$tld->last_name;
				 }else{
					$tlname=''; 
				 }
				 }
				 $empattributesMod = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','DOJ')->where('dept_id',$misData->dept_id)->first();
				 if(!empty($empattributesMod)){
				 $doj=date("d-M-Y",strtotime(str_replace("/","-",$empattributesMod->attribute_values)));
				 }
				 else{
					 $doj='';
				 }
				 $empsessionId=$request->session()->get('EmployeeId');
				$jobfunctiondetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				 //echo $jobfunctiondetails->job_function_id;exit;
				 if($jobfunctiondetails!='' && ($jobfunctiondetails->job_function_id==3 || $jobfunctiondetails->job_function_id==4)){
					$LocalContactNumber='';
					$basicSalary='';
				 }
				 else{
					 $CONTACT_NUMBER = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','CONTACT_NUMBER')->where('dept_id',$misData->dept_id)->first();
					 if(!empty($CONTACT_NUMBER)){
					 $LocalContactNumber=$CONTACT_NUMBER->attribute_values;
					 }
					 else{
						 $LocalContactNumber='';
					 } 
					 $total_gross_salary = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','total_gross_salary')->where('dept_id',$misData->dept_id)->first();
					 if(!empty($total_gross_salary)){
					 $basicSalary=$total_gross_salary->attribute_values;
					 }
					 else{
						 $basicSalary='';
					 }
					 
				 }
				 
				 
				 
				 $work_location = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','work_location')->where('dept_id',$misData->dept_id)->first();
				 if(!empty($work_location)){
				 $worklocation=$work_location->attribute_values;
				 }
				 else{
					 $worklocation='';
				 }
				 
				 $source_code = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','source_code')->where('dept_id',$misData->dept_id)->first();
				 if(!empty($source_code)){
				 $source_val=$source_code->attribute_values;
				 }
				 else{
					 $source_val='';
				 }
				 
				 $designationMod = Designation::where("id",$misData->designation_by_doc_collection)->first();
					if($designationMod != '')
					  {
					  $designation_by_doc_collection= $designationMod->name;
					  
					  }
					  else{
						 $designation_by_doc_collection=''; 
					  }
				 $salary=DocumentCollectionDetails::where("id",$misData->document_collection_id)->first();
				 if($salary!=''){
					$fsalary =$salary->proposed_salary;
				 }
				 else{
					 $fsalary ='';
				 }
				 $Recruiter =RecruiterDetails::where("id",$misData->recruiter)->first();
			  
					  if($Recruiter != '')
					  {
						
					  $RecruiterDetails= $Recruiter->name;
					  }
					  else
					  {
					  $RecruiterDetails= '';
					  }
					  $jobfunDetails = JobFunction::where("id",$misData->job_function)->first();
					   if($jobfunDetails != '')
					   {
							$jobfunction= $jobfunDetails->name;
					   }
					   else
					   {
						  $jobfunction= '';
					   }
				 
				 $indexCounter++; 	
				 $departmentMod = Department::where("id",$misData->dept_id)->first();
				 $deptname=$departmentMod->department_name;
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($misData->first_name))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($misData->middle_name))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper($misData->last_name))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $source_val)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $LocalContactNumber)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $doj)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $designation_by_doc_collection)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $worklocation)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $deptname)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('L'.$indexCounter, $tlname)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('M'.$indexCounter, "AED".$fsalary)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $RecruiterDetails)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $jobfunction)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
								
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'O'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:O1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','O') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/EMPTraining/'.$filename));	
				echo $filename;
				exit;
		}
		public function exportEmpReportTrainingCurrent(Request $request){
			$parameters = $request->input(); 
			
	         $selectedId = $parameters['selectedIds'];
			 //print_r($selectedId);exit;
			 
	        $filename = 'emp_training_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:O1');
			$sheet->setCellValue('A1', 'EMP List - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('First Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Middle Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Last Name'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Bank Code'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Local Contact Number'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Date of Joining'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Designation'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Work Location'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Department'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('TL Name'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Salary'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Recruiter'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Job Function'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $_selectedId) {
				//echo $sid;
				$expdata=explode('-',$_selectedId);
				$sid=$expdata[0];
				 $misData = Employee_details::where("id",$sid)->first();
				 $tldata=$misData->tl_id;
				 $tlname='';
				 if($tldata!=''){
				 $tld=Employee_details::where("id",$tldata)->first();
				 if($tld!=''){
				 $tlname=$tld->first_name.' '.$tld->last_name;
				 }else{
					$tlname=''; 
				 }
				 }
				 $empattributesMod = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','DOJ')->where('dept_id',$misData->dept_id)->first();
				 if(!empty($empattributesMod)){
				 $doj=date("d-M-Y",strtotime(str_replace("/","-",$empattributesMod->attribute_values)));
				 }
				 else{
					 $doj='';
				 }
				 $empsessionId=$request->session()->get('EmployeeId');
				$jobfunctiondetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				 //echo $jobfunctiondetails->job_function_id;exit;
				 if($jobfunctiondetails!='' && ($jobfunctiondetails->job_function_id==3 || $jobfunctiondetails->job_function_id==4)){
					$LocalContactNumber='';
					$basicSalary='';
				 }
				 else{
					 $CONTACT_NUMBER = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','CONTACT_NUMBER')->where('dept_id',$misData->dept_id)->first();
					 if(!empty($CONTACT_NUMBER)){
					 $LocalContactNumber=$CONTACT_NUMBER->attribute_values;
					 }
					 else{
						 $LocalContactNumber='';
					 } 
					 $total_gross_salary = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','total_gross_salary')->where('dept_id',$misData->dept_id)->first();
					 if(!empty($total_gross_salary)){
					 $basicSalary=$total_gross_salary->attribute_values;
					 }
					 else{
						 $basicSalary='';
					 }
					 
				 }
				 
				 
				 
				 $work_location = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','work_location')->where('dept_id',$misData->dept_id)->first();
				 if(!empty($work_location)){
				 $worklocation=$work_location->attribute_values;
				 }
				 else{
					 $worklocation='';
				 }
				 
				 $source_code = Employee_attribute::where('emp_id',$misData->emp_id)->where('attribute_code','source_code')->where('dept_id',$misData->dept_id)->first();
				 if(!empty($source_code)){
				 $source_val=$source_code->attribute_values;
				 }
				 else{
					 $source_val='';
				 }
				 
				 $designationMod = Designation::where("id",$misData->designation_by_doc_collection)->first();
					if($designationMod != '')
					  {
					  $designation_by_doc_collection= $designationMod->name;
					  
					  }
					  else{
						 $designation_by_doc_collection=''; 
					  }
				 $salary=DocumentCollectionDetails::where("id",$misData->document_collection_id)->first();
				 if($salary!=''){
					$fsalary =$salary->proposed_salary;
				 }
				 else{
					 $fsalary ='';
				 }
				 $Recruiter =RecruiterDetails::where("id",$misData->recruiter)->first();
			  
					  if($Recruiter != '')
					  {
						
					  $RecruiterDetails= $Recruiter->name;
					  }
					  else
					  {
					  $RecruiterDetails= '';
					  }
					  $jobfunDetails = JobFunction::where("id",$misData->job_function)->first();
					   if($jobfunDetails != '')
					   {
							$jobfunction= $jobfunDetails->name;
					   }
					   else
					   {
						  $jobfunction= '';
					   }
				 
				 $indexCounter++; 	
				 $departmentMod = Department::where("id",$misData->dept_id)->first();
				 $deptname=$departmentMod->department_name;
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($misData->first_name))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($misData->middle_name))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper($misData->last_name))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $source_val)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $LocalContactNumber)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $doj)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $designation_by_doc_collection)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $worklocation)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $deptname)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('L'.$indexCounter, $tlname)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('M'.$indexCounter, "AED".$fsalary)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $RecruiterDetails)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $jobfunction)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
								
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'O'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:O1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','O') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/EMPTraining/'.$filename));	
				echo $filename;
				exit;
		}
}
