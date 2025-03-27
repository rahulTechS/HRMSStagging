<?php
namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\Controller;
use App\Models\API\APIAuth;
use App\Models\API\V2\DeviceTokenFrontend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Employee\EmpAppAccess;
use App\Models\Employee\Employee_details;
use App\Models\Company\Department;
use App\Models\JobFunction\JobFunction;
use App\Models\Recruiter\Designation;
use App\Models\KYCProcess\KYCProcess;
use App\Models\Employee\Employee_attribute;
use  App\Models\Attribute\Attributes;
use App\Models\CrossSell\CrossSellScenariosAllocation;
use App\Models\CrossSell\CustomersMasterChild;
use App\Models\Employee_Attendance\EmpAttendance;
use DateTime;
use Crypt;

class V3AttendanceAPIController extends Controller
{
	
	
	public function attendanceDetails(Request $request)
	{
		
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['attendanceMonth']) && $requestParameters['attendanceMonth'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$attendanceMonth = $requestParameters['attendanceMonth'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					$monthArr = explode("-",$attendanceMonth);
					$monthNo = $monthArr[1];
				
					 $fromDate = $monthArr[0]."-".$monthArr[1]."-01";
					
					$noofDayInMOnth = date('t', strtotime($fromDate));
					 $toDate = $monthArr[0]."-".$monthArr[1]."-".$noofDayInMOnth;
					
					$empAttendanceData = EmpAttendance::where("emp_id",$empId)->whereBetween("attendance_date",[$fromDate,$toDate])->orderBy("attendance_date","DESC")->get();
					 /* echo "<pre>";
					print_r($empAttendanceData);
					exit;  */
					$attendanceData = array();
					$index = 0;
					foreach($empAttendanceData as $empAttendance)
					{
						$attendanceData[$index]['empId'] = $empAttendance->emp_id;
						$attendanceData[$index]['attendanceDate'] = $empAttendance->attendance_date;
						$attendanceData[$index]['attendanceValue'] = $empAttendance->attribute_value;
						$attendanceData[$index]['ColorValue'] = '#CCC526';
						$index++;
					}
					$result['responseCode'] = 200;
					$result['result'] = $attendanceData;
				}
				else
				{
						$result['responseCode'] = 401;
						$result['message'] = "Issue in token or employee Id.";
					
				}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		
		
		
		
		return response()->json($result);
	}
	
	
	
	
	public function updateLeadStatus(Request $request)
	{
		
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['leadId']) && $requestParameters['leadId'] != '' && isset($requestParameters['leadId']) && $requestParameters['leadId'] != '' && isset($requestParameters['Status']) && $requestParameters['Status'] != '' && isset($requestParameters['remark']) && $requestParameters['remark'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$leadId = $requestParameters['leadId'];
			$Status = $requestParameters['Status'];
			$remark = $requestParameters['remark'];
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					$update = CustomersMasterChild::find($leadId);
					if($Status == 'inprogress')
					{
						$update->lead_status = 1;
					}
					else if($Status == 'interested')
					{
						$update->lead_status = 2;
					}
					else if($Status == 'not_interested')
					{
						$update->lead_status = 3;
					}
					
					$update->lead_remark = $remark;
					if($update->save())
					{
						$result['responseCode'] = 200;
							$result['message'] = "Sucessfull.";
					}
					else
					{
						$result['responseCode'] = 300;
							$result['message'] = "Issue to save data .";
					}
				
					
				}
				else
				{
						$result['responseCode'] = 401;
						$result['message'] = "Issue in token or employee Id.";
					
				}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		
		
		
		
		return response()->json($result);
	}
	
	
	public function markAttendance(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['value']) && $requestParameters['value'] != '' && isset($requestParameters['dateAttendance']) && $requestParameters['dateAttendance'] != '' && isset($requestParameters['lang']) && $requestParameters['lang'] != '' && isset($requestParameters['lat']) && $requestParameters['lat'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$value = $requestParameters['value'];
			$lang = $requestParameters['lang'];
			$lat = $requestParameters['lat'];
			$dateAttendance = $requestParameters['dateAttendance'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					$newFileName = '';
					$checkExist = EmpAttendance::where("attendance_date",$dateAttendance)->where("emp_id",$empId)->first();
					/*
					*adding sellpics
					*/
					$filesParameters  = $request->file();
					foreach($filesParameters as $key=>$file)
					{
						
						$filenameWithExt =  $file->getClientOriginalName();
				
						$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
						$fileExtension =$file->getClientOriginalExtension();
						
						$newFileName = $key."_".date("Ymdhsi")."_".$empId.'.'.$fileExtension;
						if(file_exists(public_path('uploads/AttendanceAPI/'.$newFileName))){

							  unlink(public_path('uploads/AttendanceAPI/'.$newFileName));
							}  
						if($file->move(public_path('uploads/AttendanceAPI/'), $newFileName))
						{
							
						}
					}
					
					/*
					*adding sellpics
					*/
					if($checkExist != '')
					{
						$result['responseCode'] = 200;
						$result['message'] = "Attendance Already Marked.";
						/* $update = EmpAttendance::find($checkExist->id);
						$update->attribute_value = 'P';
						$update->status =1;
						$update->attendance_mark_using ='APP';
						 $update->pics =$newFileName; 
						$update->save(); */
						
					}
					else
					{
						$create = new EmpAttendance();
						$create->attribute_value = 'P';
						$create->emp_id = $empId;
						$create->attribute_code = 'attendance';
						$create->attendance_date = $dateAttendance;
						$create->attendance_mark_on = $dateAttendance;
						$create->status =1;
						$create->approve_status =1;
						$create->lang =$lang;
						$create->lat =$lat;
						$create->in_time =date("Y-m-d h:m:s");
						$create->out_time =date("Y-m-d 06:00:00");
						$create->attendance_mark_using ='APP';
						/* $update->pics =$newFileName; */
						$create->save();
						$result['responseCode'] = 200;
						$result['message'] = "SuccessFull.";
						
					}
					
					
				}
				else
				{
						$result['responseCode'] = 401;
						$result['message'] = "Issue in token or employee Id.";
					
				}
		}
		else
		{
			$result['responseCode'] = 600;
				$result['message'] = "Issue with request parameters.";
		}
		
		
		
		
		return response()->json($result);
	}
}