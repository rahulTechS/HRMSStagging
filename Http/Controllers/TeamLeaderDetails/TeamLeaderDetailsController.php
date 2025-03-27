<?php
namespace App\Http\Controllers\TeamLeaderDetails;

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



class TeamLeaderDetailsController extends Controller
{
    
     public function TeamLeaderDetails(Request $request)
		{
			$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
			
					  
			return view("TeamLeaderDetails/TeamLeadrDetailsList",compact('departmentLists'));
	
	}
	public function TeamLeaderGetData(Request $request)
	   {
		   $whereraw='';
		   $selectedFilter['Location']='';
		   $selectedFilter['department']='';
		   if(!empty($request->session()->get('location_emp_filter_inner_list')) && $request->session()->get('location_emp_filter_inner_list') != 'All')
				{
					$locationarray = $request->session()->get('location_emp_filter_inner_list');
					if($locationarray!=''){
						$location=explode(',',$locationarray);
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
						$attributedata= Employee_attribute::where('attribute_code','work_location')->whereIn('attribute_values',$location)->get();
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
				if(!empty($request->session()->get('departmentId_candAll_filter_inner_list')) && $request->session()->get('departmentId_candAll_filter_inner_list') != 'All' && $request->session()->get('location_emp_filter_inner_list') != 'null')
				{
					$department = $request->session()->get('departmentId_candAll_filter_inner_list');
					 $selectedFilter['department'] = $department;
					 if($whereraw == '')
					{
						
						$whereraw = 'dept_id IN('.$department.')';
					}
					else
					{
						
						$whereraw .= ' And dept_id IN('.$department.')';
					}
				}
		   
		   if($whereraw != '')
				{
		  $tL_details = Employee_details::whereRaw($whereraw)->where("job_role","Team Leader")->get();
				}
				else{
					$tL_details = Employee_details::where("job_role","Team Leader")->get();
				}
			if($tL_details!=''){
				$tlarray=array();
				foreach($tL_details as $_Tldata){
					$name=$_Tldata->first_name.' '.$_Tldata->middle_name.' '.$_Tldata->last_name;
					$tL_salesData = Employee_details::where("tl_id",$_Tldata->id)->get();
					$tlarray[$_Tldata->id]=$tL_salesData;
				}
				
				
			}
			return view("TeamLeaderDetails/TeamLedaerList",compact('tlarray'));
		 
	   }
	   public function filtergetTLBYlocation(Request $request)
		{
			
			
			 
			 $location=$request->location;
			 
			
			
			$request->session()->put('location_emp_filter_inner_list',$location);	
		}
		public function filtergetTLBYdepartmentList(Request $request)
		{
			
			 $department=$request->deptId;
			
			$request->session()->put('departmentId_candAll_filter_inner_list',$department);
		}
	public static function getempName($empid)
			{	
			
			//echo $attributecode;//exit;
			  $attr = Employee_details::where('id',$empid)->first();
			  if($attr != '')
			  {
			  return $attr->first_name.' '.$attr->middle_name.' '.$attr->last_name;
			  }
			  else
			  {
			  return '';
			  }
			}
			
	public function getTLwitagentupdatedepartmentData($empId,$agentId){
			
			$empdetails =Employee_details::where("id",$empId)->first();
			$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
			return view("TeamLeaderDetails/PopupFormDept",compact('empId','empdetails','departmentLists','agentId'));
		}
	function getAgentupdatedepartmentData($empId,$agentId){
		$empdetails =Employee_details::where("id",$empId)->first();
		$departmentLists = Department::where("status",1)->orderBy('id','DESC')->get();
		return view("TeamLeaderDetails/PopupFormDeptAgent",compact('empId','empdetails','departmentLists','agentId'));
	}
	public function getChangedepartmentagentTL($deptId){
			
			
			$tL_details = Employee_details::where("job_role","Team Leader")->where("dept_id",$deptId)->orderBy("id","ASC")->get();
			
			
			return view("TeamLeaderDetails/DropdownTL",compact('tL_details'));
		}	
		
	public function updateTLEmpDepartmentAgentData(Request $request){

			 $emp_id=$request->input('rowId');
			 $dept_id=$request->input('dept_id');
			 $tlId=$request->input('team_lead');
			 $agentId=explode(',',$request->input('agentId'));
			 //print_r($agentId);exit;
			 foreach($agentId as $_agent){
				$dpdata =Employee_details::where("id",$_agent)->first();
				//print_r($dpdata);exit;
				if($dpdata!=''){
					//echo $dpdata->$dept_id."ramesj";exit;
				 $departmentMod = Department::where("id",$dpdata->dept_id)->first();
				 $deptname=$departmentMod->department_name;
				 //$deptdata=$dpdata->dept_id;
					
				}
				
				$empdetails =Employee_details::find($_agent);
				 $empdetails->dept_id=$dept_id;
				 $empdetails->tl_id=$tlId;
				 if($empdetails->save()){
					$empattributesMod = Employee_attribute::where('emp_id',$dpdata->emp_id)->get();							
					if(!empty($empattributesMod))
					{
					foreach($empattributesMod as $updatedept){
					$empattributes = Employee_attribute::find($updatedept->id);
					$empattributes->dept_id = $dept_id;
					$empattributes->save();
					}
					}
					 
					$logObj = new EmpChangeLog();
					$logObj->emp_id =$dpdata->emp_id;
					$logObj->change_attribute_value =$deptname;
					$logObj->change_attribute_name ="Department";					
					$logObj->createdBY=$request->session()->get('EmployeeId');
					$logObj->save();
				 }
			 }
			 
				$response['code'] = '200';
			   $response['message'] = "Data Saved Successfully.";
			   //$response['empid'] = $empIdPadding;
			   
				echo json_encode($response);
			   exit;
			
			
		 }
		 public function getChangedepartmentagentTLwithsomeagent($empId){
			
			
			$tL_salesData = Employee_details::where("tl_id",$empId)->get();
			
			
			return view("TeamLeaderDetails/TLWithagentData",compact('tL_salesData'));
		}
	public function updateTLwithagentEmpDepartmentAgentData(Request $request){

			 $TL_Id=$request->input('TL_Id');
			 $dept_id=$request->input('dept_id');
			 
			 $changetlwithagent=$request->input('changetlwithagent');
			 
				$agentId=explode(',',$request->input('agentId'));
			 array_push($agentId,$TL_Id); 
			 
			 
			 foreach($agentId as $_agent){
				$dpdata =Employee_details::where("id",$_agent)->first();
				//print_r($dpdata);
				if($dpdata!=''){
					//echo $dpdata->$dept_id."ramesj";exit;
				 $departmentMod = Department::where("id",$dpdata->dept_id)->first();
				 $deptname=$departmentMod->department_name;
				 //$deptdata=$dpdata->dept_id;
					
				}
				$empdetails =Employee_details::find($_agent);
				 $empdetails->dept_id=$dept_id;
				 if($empdetails->save()){
					$empattributesMod = Employee_attribute::where('emp_id',$dpdata->emp_id)->get();							
					if(!empty($empattributesMod))
					{
					foreach($empattributesMod as $updatedept){
					$empattributes = Employee_attribute::find($updatedept->id);
					$empattributes->dept_id = $dept_id;
					$empattributes->save();
					}
					}
					 
					$logObj = new EmpChangeLog();
					$logObj->emp_id =$dpdata->emp_id;
					$logObj->change_attribute_value =$deptname;
					$logObj->change_attribute_name ="Department";					
					$logObj->createdBY=$request->session()->get('EmployeeId');
					$logObj->save();
				 }
			 }
			 
				$response['code'] = '200';
			   $response['message'] = "Data Saved Successfully.";
			   //$response['empid'] = $empIdPadding;
			   
				echo json_encode($response);
			   exit;
			
			
		 }	 
}
