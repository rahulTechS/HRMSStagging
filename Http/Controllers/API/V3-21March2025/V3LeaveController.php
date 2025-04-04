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
use App\Models\Entry\Employee;
use App\Models\Employee_Leaves\LeaveTypes;
use App\Models\Employee_Leaves\RequestedLeaves;
use App\Models\Employee_Leaves\RequestedLeavesLog;
use DateTime;
use Crypt;

class V3LeaveController extends Controller
{
	
	public function leaveTabs(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					$result['responseCode'] = 200;
							$result['message'] = "Sucessfull.";
				$changeSalaryList = array();
				$changeSalaryList[0]['Values'] = 'All';
				$changeSalaryList[1]['Values'] = 'Requested';
				$changeSalaryList[2]['Values'] = 'Leave Status';
				$changeSalaryList[3]['Values'] = "Today's Leave";
				
				$changeSalaryList[0]['Keys'] = 'All';
				$changeSalaryList[1]['Keys'] = 'Requested';
				$changeSalaryList[2]['Keys'] = 'leave_status';
				$changeSalaryList[3]['Keys'] = 'today_leave';
				
				$result['result'] = $changeSalaryList;
					
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
	
protected function getAgentName($eid)
	{
		$_empModDetails = Employee_details::where("emp_id",$eid)->first();
		if($_empModDetails != '')
		{
			return $_empModDetails->emp_name;
		}
		else
		{
			return '-';
		}
	}	
	
	protected function getUserName($uid)
	{
		$_empModDetails = Employee::where("id",$uid)->first();
		if($_empModDetails != '')
		{
			return $_empModDetails->fullname;
		}
		else
		{
			return '-';
		}
	}
	
	
	protected function getLeaveType($leave_id)
	{
		$leaveTypeData = LeaveTypes::where("id",$leave_id)->first();
		if($leaveTypeData != '')
		{
			return $leaveTypeData->leaves_title;
		}
		else
		{
			return "-";
		}
	}
	
public function leaveList(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['type']) && $requestParameters['type'] != ''&&  isset($requestParameters['managerId']) && $requestParameters['managerId'] != ''&& isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$managerId = $requestParameters['managerId'];
			$type = $requestParameters['type'];
			$pageLimit = $requestParameters['pageLimit'];
			$pageNo = $requestParameters['pageNo'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					if($type == 'All')
					{
						
						$leavesData = RequestedLeaves::where("tl_id",$managerId)
														->where("job_function",2)
														->orderBy('id', 'desc')
														->skip($pageNo)
														->take($pageLimit)
														->get();
						$leavesDataCount = RequestedLeaves::where("tl_id",$managerId)
											->where("job_function",2)
											->orderBy('id', 'desc')
											->get()->count();
						
						
						
					}
					else if($type == 'Requested')
					{
							$leavesData = RequestedLeaves::where('status',1)
														->where('approved_reject_status',0)
														->where("tl_id",$managerId)
														->where("job_function",2)
														->orderBy('id', 'desc')
														->skip($pageNo)
														->take($pageLimit)
														->get();
														
												
						$leavesDataCount = RequestedLeaves::where('status',1)
														->where('approved_reject_status',0)
														->where("tl_id",$managerId)
														->where("job_function",2)
														->orderBy('id', 'desc')
														->get()->count();
					}
					
					else if($type == 'leave_status')
					{
					
							$leavesData = RequestedLeaves::where('status',1)
														->where('approved_reject_status',1)
														 ->where('final_status',1)
														->where("tl_id",$managerId)
														->where("job_function",2)
														->orderBy('id', 'desc')
														->skip($pageNo)
														->take($pageLimit)
														->get();
														
												
							$leavesDataCount = RequestedLeaves::where('status',1)
														->where('approved_reject_status',1)
														 ->where('final_status',1)
														->where("tl_id",$managerId)
														->where("job_function",2)
														->orderBy('id', 'desc')
														
														->get()->count();
					}
					else if($type == 'today_leave')
					{
						
						
						$tDate = date('Y-m-d');
                $requestedLeaves = RequestedLeaves::where('final_status',1)->where("tl_id",$managerId)
														->where("job_function",2)->get();
                $newResult=array();
                foreach($requestedLeaves as $value)
                {
                    if($value->updated_from_date==NULL && $value->updated_to_date==NULL)
                    {
                        if($value->from_date <= $tDate && $value->to_date >= $tDate)
                        {
                            $newResult[]=$value->id;
                        }                       
                    }
                    else
                    {
                        if($value->updated_from_date <= $tDate && $value->updated_to_date >= $tDate)
                        {
                            $newResult[]=$value->id;
                        }
                    }
                }

                $leavesData = RequestedLeaves::
                whereIn('id',$newResult)
                ->orderBy('id', 'desc')
                ->skip($pageNo)
				->take($pageLimit)
				->get();
					$leavesDataCount = RequestedLeaves::
                whereIn('id',$newResult)
                ->orderBy('id', 'desc')
              
				->get()->count();
														
												
						
					}
					
					$leaveArray = array();
					$index=0;
					foreach($leavesData as $leave)
					{
						
						$leaveArray[$index]['employeeId'] = $leave->emp_id;
						$leaveArray[$index]['employeeName'] = $this->getAgentName($leave->emp_id);
						$leaveArray[$index]['from_date'] = date("Y-m-d",strtotime($leave->from_date));
						$leaveArray[$index]['to_date'] = date("Y-m-d",strtotime($leave->to_date));
						$leaveArray[$index]['reason'] = $leave->reason;
						$leaveArray[$index]['comment'] = $leave->comment;
						$leaveArray[$index]['num_days'] = $leave->num_days;
						
						$leaveArray[$index]['request_at'] = date("Y-m-d",strtotime($leave->request_at));
						$leaveArray[$index]['request_by'] = $this->getUserName($leave->request_by);
						$leaveArray[$index]['leaveType'] = $this->getLeaveType($leave->leave_id);
						
						
						  if($leave->status==1 && $leave->approved_reject_status==0)
							{
								$leaveArray[$index]['status'] = "Pending";
							}
							if($leave->status==1 && $leave->approved_reject_status==1  && $leave->final_status==1)
							{
								$leaveArray[$index]['status'] =  "Approved";
							}
							if($leave->status==1 && $leave->approved_reject_status==2)
							{
								$leaveArray[$index]['status'] =  "Rejected";
							}
							if($leave->status==1 && $leave->approved_reject_status==1 && $leave->final_status==2)
							{
								$leaveArray[$index]['status'] =  "Request Rejected";
							}
							$leaveArray[$index]['rowId'] = $leave->id;
							$index++;
					}
					$result['responseCode'] = 200;
							$result['message'] = "Sucessfull.";
				
				$result['result'] = $leaveArray;
				$warningLetterTitle = array();
				
				$warningLetterTitle[0]['Title'] = 'Employee Id';
				$warningLetterTitle[0]['Key'] = 'employeeId';
				
				
				$warningLetterTitle[1]['Title'] = 'Employee Name';
				$warningLetterTitle[1]['Key'] = 'employeeName';
				
				$warningLetterTitle[2]['Title'] = 'From Date';
				$warningLetterTitle[2]['Key'] = 'from_date';
				
				$warningLetterTitle[3]['Title'] = 'To Date';
				$warningLetterTitle[3]['Key'] = 'to_date';
				
				$warningLetterTitle[4]['Title'] = 'Reason';
				$warningLetterTitle[4]['Key'] = 'reason';
				
				
							$warningLetterTitle[5]['Title'] = 'Comment';
				$warningLetterTitle[5]['Key'] = 'comment';
				
				
							$warningLetterTitle[6]['Title'] = 'Num Of Days';
				$warningLetterTitle[6]['Key'] = 'num_days';
				
				$warningLetterTitle[7]['Title'] = 'Created At';
				$warningLetterTitle[7]['Key'] = 'created_at';
				
				
				$warningLetterTitle[8]['Title'] = 'Request At';
				$warningLetterTitle[8]['Key'] = 'request_at';
				
				$warningLetterTitle[9]['Title'] = 'Request By';
				$warningLetterTitle[9]['Key'] = 'request_by';
				
				$warningLetterTitle[10]['Title'] = 'Leave Type';
				$warningLetterTitle[10]['Key'] = 'leaveType';
				
				
				$warningLetterTitle[11]['Title'] = 'Leave Status';
				$warningLetterTitle[11]['Key'] = 'status';
				
				
					$warningLetterTitle[12]['Title'] = 'RowId';
				$warningLetterTitle[12]['Key'] = 'rowId';
				
				$result['resultTitleDetails'] = $warningLetterTitle;	
				$result['totalCount'] = $leavesDataCount;
				
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
	
public function addLeaveRequestStep1(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['managerId']) && $requestParameters['managerId'] != ''&& isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$managerId = $requestParameters['managerId'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					$leaveTypeData = LeaveTypes::where("status",1)->get();
					$leavetypeArray = array();
					$index=0;
					foreach($leaveTypeData as $data)
					{
							$leavetypeArray[$index]['key'] = $data->id;
							$leavetypeArray[$index]['value'] = $data->leaves_title;
							$index++;
					}
					$formAdd = array();
					$formAdd[0]['Title'] = 'Leave Type';
					$formAdd[0]['key'] = 'leave_type';
					$formAdd[0]['type'] = 'dropdown';
					$formAdd[0]['options'] = $leavetypeArray;
					$formAdd[0]['value'] = '';
					$formAdd[0]['readOnly'] = 'No';
					
					
					
					
					$result['responseCode'] = 200;
					$result['message'] = "Sucessfull.";
					$result['result'] = $formAdd;
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
	
	
	
		
public function addLeaveRequestStep2(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['managerId']) && $requestParameters['managerId'] != ''&& isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$managerId = $requestParameters['managerId'];
			$leave_type = $requestParameters['leave_type'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					
					$empDetails = Employee_details::where("tl_id",$managerId)->get();
					$empArray = array();
					$index =0;
					foreach($empDetails as $emp1)
					{
						$empArray[$index]['key'] = $emp1->emp_id;
						$empArray[$index]['value'] = $emp1->emp_name;	
						$index++;
					}
					
					$formAdd = array();
					$formAdd[0]['Title'] = 'Leave Type';
					$formAdd[0]['key'] = 'leave_type';
					$formAdd[0]['type'] = 'text';
					$formAdd[0]['options'] = '';
					$formAdd[0]['value'] = $this->getLeaveType($leave_type);
					$formAdd[0]['readOnly'] = 'Yes';
					
					$formAdd[1]['Title'] = 'Employee Id';
					$formAdd[1]['key'] = 'selectedEmpId';
					$formAdd[1]['type'] = 'dropdown';
					$formAdd[1]['options'] = $empArray;
					$formAdd[1]['value'] = '';
					$formAdd[1]['readOnly'] = 'No';
					
					
					
					$formAdd[2]['Title'] = 'Leave From Date';
					$formAdd[2]['key'] = 'leave_from_date';
					$formAdd[2]['type'] = 'calender';
					$formAdd[2]['options'] = '';
					$formAdd[2]['value'] = '';
					$formAdd[2]['readOnly'] = 'No';
					
					
					$formAdd[3]['Title'] = 'Leave To Date';
					$formAdd[3]['key'] = 'leave_to_date';
					$formAdd[3]['type'] = 'calender';
					$formAdd[3]['options'] = '';
					$formAdd[3]['value'] = '';
					$formAdd[3]['readOnly'] = 'No';
					
					
					$formAdd[4]['Title'] = 'Reason';
					$formAdd[4]['key'] = 'reason';
					$formAdd[4]['type'] = 'text';
					$formAdd[4]['options'] = '';
					$formAdd[4]['value'] = '';
					$formAdd[4]['readOnly'] = 'No';
					
					$formAdd[5]['Title'] = 'Comments/Notes';
					$formAdd[5]['key'] = 'comments_notes';
					$formAdd[5]['type'] = 'textarea';
					$formAdd[5]['options'] = '';
					$formAdd[5]['value'] = '';
					$formAdd[5]['readOnly'] = 'No';
					
					
					$formAdd[6]['Title'] = 'During Leave Outside Country&During Leave Inside Country';
					$formAdd[6]['key'] = 'duringLeave_Station';
					$formAdd[6]['type'] = 'radio';
					$formAdd[6]['options'] = '';
					$formAdd[6]['value'] = '';
					$formAdd[6]['readOnly'] = 'No';
					
					
					
					
					
					$result['responseCode'] = 200;
					$result['message'] = "Sucessfull.";
					$result['result'] = $formAdd;
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
	
	
		
public function postLeaveRequest(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['managerId']) && $requestParameters['managerId'] != ''&& isset($requestParameters['empId']) && $requestParameters['empId'] != ''&& isset($requestParameters['Token']) && $requestParameters['Token'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$managerId = $requestParameters['managerId'];
			$leave_type = $requestParameters['leave_type'];
			$selectedEmpId = $requestParameters['selectedEmpId'];
			$leave_from_date = $requestParameters['leave_from_date'];
			$leave_to_date = $requestParameters['leave_to_date'];
			$reason = $requestParameters['reason'];
			$duringLeave_Station = $requestParameters['duringLeave_Station'];
			$comments_notes = $requestParameters['comments_notes'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
					/* echo "<pre>";
					print_r($requestParameters);
					exit; */
					$empDetails = Employee_details::where("emp_id",$selectedEmpId)->orderBy('id','desc')->first();
            
            $usersessionId=$managerId;
            $requestedLeaves = new RequestedLeaves();
			$requestedLeaves->emp_id = $selectedEmpId;
            $requestedLeaves->leave_id = $leave_type;
            $requestedLeaves->from_date = $leave_from_date;
            $requestedLeaves->to_date = $leave_to_date;
            $requestedLeaves->reason = $reason;
            $requestedLeaves->comment = $comments_notes;

            $requestedLeaves->tl_id = $empDetails->tl_id;
            $requestedLeaves->job_function = $empDetails->job_function;

            $diff = strtotime($leave_to_date) - strtotime($leave_from_date);   
            // 1 day = 24 hours 
            // 24 * 60 * 60 = 86400 seconds 
            $num_days = abs(round($diff / 86400)); 
            $num_days = $num_days+1;

            $requestedLeaves->num_days = $num_days;
            $requestedLeaves->status = 1;
            $requestedLeaves->request_at = date('Y-m-d H:i:s');
            $requestedLeaves->request_by = $managerId;

            if($duringLeave_Station==1)
            {
                $requestedLeaves->during_leave_outsideCountry = 1;
            }
            if($duringLeave_Station==2)
            {
                $requestedLeaves->during_leave_insideCountry = 1;
            }
            

            if($requestedLeaves->save())
			{
					$leavesLogs = new RequestedLeavesLog();
					$leavesLogs->emp_id = $selectedEmpId;
					$leavesLogs->leave_id = $leave_type;
					$leavesLogs->request_event = 1;
					$leavesLogs->event_at = date('Y-m-d');
					$leavesLogs->event_by = $managerId;
					$leavesLogs->row_id = $requestedLeaves->id;
					$leavesLogs->save();							
					$result['responseCode'] = 200;
					$result['message'] = "Sucessfull.";
			}
			else
			{
				$result['responseCode'] = 202;
					$result['message'] = "issue to save.";
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
public function leaveProgressStatus(Request $request)
	{
		$requestParameters = $request->input();
		
		if(isset($requestParameters['empId']) && $requestParameters['empId'] != '' && isset($requestParameters['Token']) && $requestParameters['Token'] != '' && isset($requestParameters['selected_id']) && $requestParameters['selected_id'] != ''&& isset($requestParameters['row_id']) && $requestParameters['row_id'] != '')
		{
			$Token = $requestParameters['Token'];
			$empId = $requestParameters['empId'];
			$selected_id = $requestParameters['selected_id'];
			$row_id = $requestParameters['row_id'];
			
				$checkToken = APIAuth::where("emp_id",$empId)->orderBy("id","DESC")->first();
				if($checkToken != '' && trim($checkToken->Token) == trim($Token))
				{
										$changeSalaryProcess = $this->summaryTabsfullViewDataAPI($selected_id,$row_id);
					
				$result = $changeSalaryProcess;
					
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
	
	public function summaryTabsfullViewDataAPI($empId,$rowid)
    {
        
         
         $leaveRequestDetails = RequestedLeaves::where('emp_id',$empId)->where("id",$rowid)->orderBy("id","DESC")->first();
         //return $leaveRequestDetails;
         


         $completedStep = 0;
         $OnboardingProgress = '';
         $stepsAll = array();
         /*Step1*/
         $stepsAll[0]['name'] = 'Leave Request Created'; 
         if($leaveRequestDetails->status == 1 && $leaveRequestDetails->approved_reject_status == 0)
         {
             $completedStep++;
             $stepsAll[0]['stage'] = 'active'; 
             $OnboardingProgress = 'Leave Request Created';
             $stepsAll[0]['Tab'] = 'active'; 

         }
         elseif($leaveRequestDetails->status == 1 && ($leaveRequestDetails->approved_reject_status == 1 || $leaveRequestDetails->approved_reject_status == 2))
         {
            $stepsAll[0]['stage'] = 'active'; 
            $OnboardingProgress = 'Leave Request Created';
            $stepsAll[0]['Tab'] = 'active'; 
         }
         else
         {
            $stepsAll[0]['stage'] = 'pending'; 
            $stepsAll[0]['Tab'] = 'disabled-tab';  
         }
         $stepsAll[0]['slagURL'] = 'tab2'; 
         //$stepsAll[0]['tab'] = 'active'; 
         $stepsAll[0]['onclick'] = 'tab2Panel();'; 
         
         $OnboardingProgress = 'Leave Request Created';
         /*Step1*/




         /*Step2*/
        $stepsAll[1]['name'] = 'Approved/Reject Request'; 
        if($leaveRequestDetails->status == 1 && ($leaveRequestDetails->approved_reject_status == 1 || $leaveRequestDetails->approved_reject_status == 2))
        {
            $completedStep++;
            $stepsAll[1]['stage'] = 'active'; 
            $OnboardingProgress = 'Approved/Reject Request';
            $stepsAll[1]['Tab'] = 'active';
        }
        elseif($leaveRequestDetails->status == 1 && $leaveRequestDetails->approved_reject_status == 0)
        {
            $stepsAll[1]['stage'] = 'inprogress'; 
            $stepsAll[1]['Tab'] = 'active'; 
        }
        else 
        {
            $stepsAll[1]['stage'] = 'pending'; 
            $stepsAll[1]['Tab'] = 'disabled-tab';  

        }
        $stepsAll[1]['slagURL'] = 'tab2'; 
        $stepsAll[1]['onclick'] = 'tab2Panel();'; 

        /*Step2*/





        /*Step3*/
        $stepsAll[2]['name'] = 'Request Confirmed'; 
        if($leaveRequestDetails->status == 1 && ($leaveRequestDetails->approved_reject_status == 1 || $leaveRequestDetails->approved_reject_status == 2) && $leaveRequestDetails->final_status == 1)
        {
            $completedStep++;
            $stepsAll[2]['stage'] = 'active'; 
            $OnboardingProgress = 'Request Confirmed';
            $stepsAll[2]['Tab'] = 'active';
        }
        else 
        {
            $stepsAll[2]['stage'] = 'pending'; 
            $stepsAll[2]['Tab'] = 'disabled-tab';  

        }
        $stepsAll[2]['slagURL'] = 'tab3'; 
        $stepsAll[2]['onclick'] = 'tab3Panel();'; 
        /*Step3*/




         
         $totalStep = 2;
         $p = $completedStep/$totalStep;
         $percentange = round($p*100);
         
        			
			$result['result']['steps'] = $stepsAll;
			$result['result']['percentange'] = $percentange;
			return $result;
    }
}