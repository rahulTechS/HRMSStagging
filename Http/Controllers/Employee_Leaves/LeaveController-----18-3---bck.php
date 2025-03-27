<?php

namespace App\Http\Controllers\Employee_Leaves;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LoggerFactory;
use Illuminate\Support\Facades\Validator;

use App\Models\Employee_Leaves\LeaveTypes;
use App\Models\Employee_Leaves\RequestedLeaves;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use App\Models\Company\Department;
use App\User;
use App\Models\Employee_Leaves\RequestedLeavesLog;
use App\Models\EmpProcess\JobFunctionPermission;

class LeaveController extends Controller
{
    public function __construct(LoggerFactory $logFactory)
    {
        //$this->log = $logFactory->setPath('logs/leaves')->createLogger('leaves'); 
    }
	public function Index(Request $request)
	{
        $empData = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                ->orderBy('leaves_request.id', 'desc')->get();

        
       // return view("Employee_Leaves/Index",compact('ReasonsForLeavingDetails','departmentLists','tL_details','empId','Designation'));
        return view("Employee_Leaves/Index",compact('empData'));
    }


    public function getLeaveRequestFormContent()
	{
		$leaveTypesdata = LeaveTypes::where('status',1)->orderBy('id','ASC')->get();

        if($leaveTypesdata)
        {
            $leaveTypesdata=$leaveTypesdata;
            $msg=0;
        }
        else{
            $msg=1;
            $leaveTypesdata="";
        }
		return view("Employee_Leaves/leaveRequestForm",compact('leaveTypesdata','msg'));		
	}

    public function getLeavesContent(Request $request,$leaveid)
	{
		
        $empLeaveData = RequestedLeaves::select('emp_id')->where('approved_reject_status',0)->where('final_status',0)->orderBy('leaves_request.id', 'desc')->get();
        //return $empLeaveData;
        
        
        
        
        $leaveTypesdata = LeaveTypes::where('id',$leaveid)->where('status',1)->orderBy('id','ASC')->first();
        $leavesCount = $leaveTypesdata->total;
        //$empData = Employee_details::orderBy('id','desc')->get();


        $loggedinUserid=$request->session()->get('EmployeeId');
        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        if($departmentDetails != '')
        {
            $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
            if($empDetails!='')
            {
                $empData = Employee_details::
                where('employee_details.dept_id',$empDetails->dept_id)
                ->whereNotIn('emp_id', $empLeaveData)
                ->orderBy('id','desc')->get();
            }
        }
        else
        {
            $empData = Employee_details::whereNotIn('emp_id', $empLeaveData)->orderBy('id','desc')->get();
        }







		return view("Employee_Leaves/leaveRequestFormContent",compact('leaveTypesdata','empData','leavesCount'));		
	}

    public function getRemainsLeaves(Request $request)
	{
		$empid = $request->empid;
        $leaveid = $request->leaveid;

        $leaveTypesdata = LeaveTypes::where('status',1)->orderBy('id','ASC')->get();
        $totalLeavesData = LeaveTypes::where('id',$leaveid)->where('status',1)->orderBy('id','ASC')->first();
        $SumLeavesdata = RequestedLeaves::where('final_status',1)->where('approved_reject_status',1)->where('leave_id',$leaveid)->where('emp_id',$empid)->orderBy('id','ASC')->get();
        
        //return $SumLeavesdata;
        


        if($SumLeavesdata)
        {
            $totalLeaves = $SumLeavesdata->sum('num_days');
            $leavesCount = $totalLeavesData->total - $totalLeaves;
            if($leavesCount > 0)
            {
                return "Available Leaves: ".$leavesCount;
            }
            else{
                return "Applied Leaves Marked As Unpaid.";
            }
        }
        else{
            return "Available Leaves: ".$totalLeavesData->total;
        }


        
        
        //$empData = Employee_details::orderBy('id','desc')->get();
		//return view("Employee_Leaves/leaveRequestForm",compact('leaveTypesdata','leavesCount'));		
	}

    public static function getLeaveType($leaveid)
    {
        $leaveTypesdata = LeaveTypes::where('id',$leaveid)->where('status',1)->orderBy('id','ASC')->first();
        if($leaveTypesdata)
        {
            return $leaveTypesdata->leaves_title;
        }
        else
        {
            return "--";
        }
        
    }

    public static function getEmployeeName($empid)
    {
        $employeeInfo = Employee_details::where('emp_id',$empid)->orderBy('id','desc')->first();
        
        if($employeeInfo)
        {
            return $employeeInfo->emp_name;
        }
        else
        {
            return "--";
        }
    }

    public static function getDepartment($empid)
	{
		$empDetails = Employee_details::where("emp_id",$empid)->orderBy('id','desc')->first();
		
		if($empDetails)
		{
			$departmentDetails = Department::where("id",$empDetails->dept_id)->first();
			if($departmentDetails != '')
			{
				return $departmentDetails->department_name;
			}
			else{
				 return '--'; 
			}

		}
		else{
			return '--';
		}
	  
	}


	public static function getWorkLocation($empid)
	{	
		$attributecode = 'work_location';
		$attr = Employee_attribute::where('emp_id',$empid)->where("attribute_code",$attributecode)->first();
		if($attr != '')
		{
			return $attr->attribute_values;
		}
		else
		{
			return '--';
		}
	}

    

    public static function getStatus($empid,$rowid)
	{	
		$requestedLeaves = RequestedLeaves::where('emp_id',$empid)->where("id",$rowid)->first();

        if($requestedLeaves)
        {
            if($requestedLeaves->status==1 && $requestedLeaves->approved_reject_status==0)
            {
                return "Pending";
            }
            if($requestedLeaves->status==1 && $requestedLeaves->approved_reject_status==1)
            {
                return "Approved";
            }
            if($requestedLeaves->status==1 && $requestedLeaves->approved_reject_status==2)
            {
                return "Rejected";
            }
        }
        else
        {
            return "--";
        }

	}

    public static function getUserName($userid)
	{	
		$userData = User::where("id",$userid)->first();

        if($userData)
        {
            return $userData->fullname;            
        }
        else
        {
            return "--";
        }

	}

    public static function getEmpName($empId)
	{
		$empDetails = Employee_details::where("emp_id",$empId)->first();

		if($empDetails)
		{
			return $empDetails->emp_name;
		}
		else
		{
			return "--";
		}
	}

    

    
    public function allLeavesListingData(Request $request)
	{
		$whereraw = '';
		$whereraw1 = '';
		$selectedFilter['CNAME'] = '';
		$selectedFilter['CEMAIL'] = '';
		$selectedFilter['DESC'] = '';
		$selectedFilter['DEPT'] = '';
		$selectedFilter['OPENING'] = '';
		$selectedFilter['STATUS'] = '';
		$selectedFilter['vintage'] = '';
		$selectedFilter['Company'] = '';
		$selectedFilter['Recruiter'] = '';
		
        
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';

        if(!empty($request->session()->get('EmpLeaves_page_limit')))
        {
            $paginationValue = $request->session()->get('EmpLeaves_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }


        if(!empty($request->session()->get('leaves_emp_name')) && $request->session()->get('leaves_emp_name') != 'All')
        {
            $fname = $request->session()->get('leaves_emp_name');
            $cnameArray = explode(",",$fname);
                
            $namefinalarray=array();
            foreach($cnameArray as $namearray){
                $namefinalarray[]="'".$namearray."'";                
            }
                
            $finalcname=implode(",", $namefinalarray);

            if($whereraw == '')
            {
                //$whereraw = 'emp_name like "%'.$fname.'%"';
                $whereraw = 'employee_details.emp_name IN('.$finalcname.')';
            }
            else
            {
                $whereraw .= ' And employee_details.emp_name IN('.$finalcname.')';
            }
        }

        if(!empty($request->session()->get('leaves_emp_id')) && $request->session()->get('leaves_emp_id') != 'All')
        {
            $empId = $request->session()->get('leaves_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'leaves_request.emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And leaves_request.emp_id IN ('.$empId.')';
            }
        }

        if(!empty($request->session()->get('emp_leaves_fromdate')) && $request->session()->get('emp_leaves_fromdate') != 'All')
        {
            $datefrom = $request->session()->get('emp_leaves_fromdate');
            if($whereraw == '')
            {
                $whereraw = 'leaves_request.created_at>= "'.$datefrom.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And leaves_request.created_at>= "'.$datefrom.' 00:00:00"';
            }
        }
        if(!empty($request->session()->get('emp_leaves_todate')) && $request->session()->get('emp_leaves_todate') != 'All')
        {
            $dateto = $request->session()->get('emp_leaves_todate');
            if($whereraw == '')
            {
                $whereraw = 'leaves_request.created_at<= "'.$dateto.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And leaves_request.created_at<= "'.$dateto.' 00:00:00"';
            }
        }



        if($whereraw != '')
		{
            $loggedinUserid=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $requestedLeaves = RequestedLeaves::whereRaw($whereraw)
                    ->join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('leaves_request.id', 'desc')
                    //->toSql();
                    //dd($requestedLeaves);
                    ->paginate($paginationValue);

                    $reportsCount = RequestedLeaves::whereRaw($whereraw)
                    ->join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->orderBy('leaves_request.id', 'desc')
                    ->get()->count();
                }
            }
            else
            {
                $requestedLeaves = RequestedLeaves::whereRaw($whereraw)
                ->join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                ->orderBy('leaves_request.id', 'desc')
                //->toSql();
                //dd($requestedLeaves);
                ->paginate($paginationValue);

                $reportsCount = RequestedLeaves::whereRaw($whereraw)
                ->join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                ->orderBy('leaves_request.id', 'desc')
                ->get()->count();
            }
            
            
            
            

        }
        else
        {
            $loggedinUserid=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $requestedLeaves = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('leaves_request.id', 'desc')
                    ->paginate($paginationValue);
                    //return $requestedLeaves;
                        
                    $reportsCount = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('leaves_request.id', 'desc')
                    ->get()->count();
                }
            }
            else
            {
                $requestedLeaves = RequestedLeaves::orderBy('leaves_request.id', 'desc')
                ->paginate($paginationValue);
                    
                $reportsCount = RequestedLeaves::orderBy('leaves_request.id', 'desc')
                ->get()->count();
            }
           
        }
        
        
			
		$requestedLeaves->setPath(config('app.url/listingAllLeaves'));		
	    return view("Employee_Leaves/listingAllLeaves",compact('requestedLeaves','paginationValue','reportsCount'));
	}

    public function requestedLeavesListingData(Request $request)
	{
		$whereraw = '';
		$whereraw1 = '';
		$selectedFilter['CNAME'] = '';
		$selectedFilter['CEMAIL'] = '';
		$selectedFilter['DESC'] = '';
		$selectedFilter['DEPT'] = '';
		$selectedFilter['OPENING'] = '';
		$selectedFilter['STATUS'] = '';
		$selectedFilter['vintage'] = '';
		$selectedFilter['Company'] = '';
		$selectedFilter['Recruiter'] = '';
		
        
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';

        if(!empty($request->session()->get('EmpLeaves_page_limit')))
        {
            $paginationValue = $request->session()->get('EmpLeaves_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }


        if(!empty($request->session()->get('leaves_emp_name')) && $request->session()->get('leaves_emp_name') != 'All')
        {
            $fname = $request->session()->get('leaves_emp_name');
            $cnameArray = explode(",",$fname);
                
            $namefinalarray=array();
            foreach($cnameArray as $namearray){
                $namefinalarray[]="'".$namearray."'";                
            }
                
            $finalcname=implode(",", $namefinalarray);

            if($whereraw == '')
            {
                //$whereraw = 'emp_name like "%'.$fname.'%"';
                $whereraw = 'employee_details.emp_name IN('.$finalcname.')';
            }
            else
            {
                $whereraw .= ' And employee_details.emp_name IN('.$finalcname.')';
            }
        }

        if(!empty($request->session()->get('leaves_emp_id')) && $request->session()->get('leaves_emp_id') != 'All')
        {
            $empId = $request->session()->get('leaves_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'leaves_request.emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And leaves_request.emp_id IN ('.$empId.')';
            }
        }

        if(!empty($request->session()->get('emp_leaves_fromdate')) && $request->session()->get('emp_leaves_fromdate') != 'All')
        {
            $datefrom = $request->session()->get('emp_leaves_fromdate');
                if($whereraw == '')
            {
                $whereraw = 'leaves_request.created_at>= "'.$datefrom.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And leaves_request.created_at>= "'.$datefrom.' 00:00:00"';
            }
        }
        if(!empty($request->session()->get('emp_leaves_todate')) && $request->session()->get('emp_leaves_todate') != 'All')
        {
            $dateto = $request->session()->get('emp_leaves_todate');
                if($whereraw == '')
            {
                $whereraw = 'leaves_request.created_at<= "'.$dateto.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And leaves_request.created_at<= "'.$dateto.' 00:00:00"';
            }
        }






			
        if($whereraw != '')
		{
            $loggedinUserid=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    
                    $requestedLeaves = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('leaves_request.status',1)
                    ->where('leaves_request.approved_reject_status',0)
                    ->whereRaw($whereraw)
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('leaves_request.id', 'desc')
                    ->paginate($paginationValue);

                    $reportsCount = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('leaves_request.status',1)
                    ->where('leaves_request.approved_reject_status',0)
                    ->whereRaw($whereraw)
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('leaves_request.id', 'desc')
                    ->get()->count();
                }
            }
            else
            {
                $requestedLeaves = RequestedLeaves::where('leaves_request.status',1)
                ->where('leaves_request.approved_reject_status',0)
                ->whereRaw($whereraw)
                ->orderBy('leaves_request.id', 'desc')
                ->paginate($paginationValue);

                $reportsCount = RequestedLeaves::where('leaves_request.status',1)
                ->where('leaves_request.approved_reject_status',0)
                ->whereRaw($whereraw)
                ->orderBy('leaves_request.id', 'desc')
                ->get()->count();
            }

            

        }
        else
        {
            $loggedinUserid=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    
                    $requestedLeaves = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('leaves_request.status',1)
                    ->where('leaves_request.approved_reject_status',0)
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('leaves_request.id', 'desc')
                    ->paginate($paginationValue);
                        
                    $reportsCount = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('leaves_request.status',1)
                    ->where('leaves_request.approved_reject_status',0)
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('leaves_request.id', 'desc')
                    ->get()->count();
                }
            }
            else
            {
                $requestedLeaves = RequestedLeaves::where('leaves_request.status',1)
                ->where('leaves_request.approved_reject_status',0)
                ->orderBy('leaves_request.id', 'desc')
                ->paginate($paginationValue);
                    
                $reportsCount = RequestedLeaves::where('leaves_request.status',1)
                ->where('leaves_request.approved_reject_status',0)
                ->orderBy('leaves_request.id', 'desc')
                ->get()->count();
            }
            
        }
        
        $empsessionId=$request->session()->get('EmployeeId');
		$empDetails = User::where("id",$empsessionId)->first();

		

			if($empDetails)
			{
				
				$usersids = array(101456,101058,101042,100762,101466,101549,101558);

				if (in_array($empDetails->employee_id, $usersids))
				{
					$visiblebtn = 1;
				}
				else
				{
					$visiblebtn = 0;
				}
			}
				

			
		$requestedLeaves->setPath(config('app.url/listingLeavesRequest'));		
	    return view("Employee_Leaves/listingLeavesRequest",compact('requestedLeaves','paginationValue','reportsCount','visiblebtn'));
	}

    public function finalRequestedLeavesListingData(Request $request)
	{
		$whereraw = '';
		$whereraw1 = '';
		$selectedFilter['CNAME'] = '';
		$selectedFilter['CEMAIL'] = '';
		$selectedFilter['DESC'] = '';
		$selectedFilter['DEPT'] = '';
		$selectedFilter['OPENING'] = '';
		$selectedFilter['STATUS'] = '';
		$selectedFilter['vintage'] = '';
		$selectedFilter['Company'] = '';
		$selectedFilter['Recruiter'] = '';
		
        
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';

        if(!empty($request->session()->get('EmpLeaves_page_limit')))
        {
            $paginationValue = $request->session()->get('EmpLeaves_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }


        if(!empty($request->session()->get('leaves_emp_name')) && $request->session()->get('leaves_emp_name') != 'All')
        {
            $fname = $request->session()->get('leaves_emp_name');
            $cnameArray = explode(",",$fname);
                
            $namefinalarray=array();
            foreach($cnameArray as $namearray){
                $namefinalarray[]="'".$namearray."'";                
            }
                
            $finalcname=implode(",", $namefinalarray);

            if($whereraw == '')
            {
                //$whereraw = 'emp_name like "%'.$fname.'%"';
                $whereraw = 'employee_details.emp_name IN('.$finalcname.')';
            }
            else
            {
                $whereraw .= ' And employee_details.emp_name IN('.$finalcname.')';
            }
        }

        if(!empty($request->session()->get('leaves_emp_id')) && $request->session()->get('leaves_emp_id') != 'All')
        {
            $empId = $request->session()->get('leaves_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'leaves_request.emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And leaves_request.emp_id IN ('.$empId.')';
            }
        }

        if(!empty($request->session()->get('emp_leaves_fromdate')) && $request->session()->get('emp_leaves_fromdate') != 'All')
        {
            $datefrom = $request->session()->get('emp_leaves_fromdate');
                if($whereraw == '')
            {
                $whereraw = 'leaves_request.created_at>= "'.$datefrom.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And leaves_request.created_at>= "'.$datefrom.' 00:00:00"';
            }
        }
        if(!empty($request->session()->get('emp_leaves_todate')) && $request->session()->get('emp_leaves_todate') != 'All')
        {
            $dateto = $request->session()->get('emp_leaves_todate');
                if($whereraw == '')
            {
                $whereraw = 'leaves_request.created_at<= "'.$dateto.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And leaves_request.created_at<= "'.$dateto.' 00:00:00"';
            }
        }






			
        if($whereraw != '')
		{
            $loggedinUserid=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    
                    $requestedLeaves = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('leaves_request.status',1)
                    ->where('leaves_request.approved_reject_status',1)
                    ->where('leaves_request.final_status',1)
                    ->whereRaw($whereraw)
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('leaves_request.id', 'desc')
                    ->paginate($paginationValue);

                    $reportsCount = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('leaves_request.status',1)
                    ->where('leaves_request.approved_reject_status',1)
                    ->where('leaves_request.final_status',1)
                    ->whereRaw($whereraw)
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('leaves_request.id', 'desc')
                    ->get()->count();
                }
            }
            else
            {
                $requestedLeaves = RequestedLeaves::where('leaves_request.status',1)
                ->where('leaves_request.approved_reject_status',1)
                ->where('leaves_request.final_status',1)
                ->whereRaw($whereraw)
                ->orderBy('leaves_request.id', 'desc')
                ->paginate($paginationValue);

                $reportsCount = RequestedLeaves::where('leaves_request.status',1)
                ->where('leaves_request.approved_reject_status',1)
                ->where('leaves_request.final_status',1)
                ->whereRaw($whereraw)
                ->orderBy('leaves_request.id', 'desc')
                ->get()->count();
            }
            
            
            

        }
        else
        {
            $loggedinUserid=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    // $empData = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
                    $requestedLeaves = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('leaves_request.status',1)
                    ->where('leaves_request.approved_reject_status',1)
                    ->where('leaves_request.final_status',1)
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('leaves_request.id', 'desc')
                    ->paginate($paginationValue);
                        
                    $reportsCount = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('leaves_request.status',1)
                    ->where('leaves_request.approved_reject_status',1)
                    ->where('leaves_request.final_status',1)
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('leaves_request.id', 'desc')
                    ->get()->count();
                }
            }
            else
            {
                $requestedLeaves = RequestedLeaves::where('leaves_request.status',1)
                ->where('leaves_request.approved_reject_status',1)
                ->where('leaves_request.final_status',1)
                ->orderBy('leaves_request.id', 'desc')
                ->paginate($paginationValue);
                    
                $reportsCount = RequestedLeaves::where('leaves_request.status',1)
                ->where('leaves_request.approved_reject_status',1)
                ->where('leaves_request.final_status',1)
                ->orderBy('leaves_request.id', 'desc')
                ->get()->count();
            }
            
        }
        
        
				

			
		$requestedLeaves->setPath(config('app.url/listingFinalLeavesRequest'));		
	    return view("Employee_Leaves/listingFinalLeavesRequest",compact('requestedLeaves','paginationValue','reportsCount'));
	}

    public function todaysLeavesListingData(Request $request)
	{
		$whereraw = '';
		$whereraw1 = '';
		$selectedFilter['CNAME'] = '';
		$selectedFilter['CEMAIL'] = '';
		$selectedFilter['DESC'] = '';
		$selectedFilter['DEPT'] = '';
		$selectedFilter['OPENING'] = '';
		$selectedFilter['STATUS'] = '';
		$selectedFilter['vintage'] = '';
		$selectedFilter['Company'] = '';
		$selectedFilter['Recruiter'] = '';
		
        
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';

        if(!empty($request->session()->get('EmpLeaves_page_limit')))
        {
            $paginationValue = $request->session()->get('EmpLeaves_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }


        if(!empty($request->session()->get('leaves_emp_name')) && $request->session()->get('leaves_emp_name') != 'All')
        {
            $fname = $request->session()->get('leaves_emp_name');
            $cnameArray = explode(",",$fname);
                
            $namefinalarray=array();
            foreach($cnameArray as $namearray){
                $namefinalarray[]="'".$namearray."'";                
            }
                
            $finalcname=implode(",", $namefinalarray);

            if($whereraw == '')
            {
                //$whereraw = 'emp_name like "%'.$fname.'%"';
                $whereraw = 'employee_details.emp_name IN('.$finalcname.')';
            }
            else
            {
                $whereraw .= ' And employee_details.emp_name IN('.$finalcname.')';
            }
        }

        if(!empty($request->session()->get('leaves_emp_id')) && $request->session()->get('leaves_emp_id') != 'All')
        {
            $empId = $request->session()->get('leaves_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'leaves_request.emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And leaves_request.emp_id IN ('.$empId.')';
            }
        }

        if(!empty($request->session()->get('emp_leaves_fromdate')) && $request->session()->get('emp_leaves_fromdate') != 'All')
        {
            $datefrom = $request->session()->get('emp_leaves_fromdate');
                if($whereraw == '')
            {
                $whereraw = 'leaves_request.created_at>= "'.$datefrom.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And leaves_request.created_at>= "'.$datefrom.' 00:00:00"';
            }
        }
        if(!empty($request->session()->get('emp_leaves_todate')) && $request->session()->get('emp_leaves_todate') != 'All')
        {
            $dateto = $request->session()->get('emp_leaves_todate');
                if($whereraw == '')
            {
                $whereraw = 'leaves_request.created_at<= "'.$dateto.' 00:00:00"';
            }
            else
            {
                $whereraw .= ' And leaves_request.created_at<= "'.$dateto.' 00:00:00"';
            }
        }






			
        if($whereraw != '')
		{
            $loggedinUserid=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $tDate = date('Y-m-d');
                    $requestedLeaves = RequestedLeaves::where('final_status',1)->get();
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

                    $requestedLeaves = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->whereIn('leaves_request.id',$newResult)
                    ->whereRaw($whereraw)
                    ->orderBy('leaves_request.id', 'desc')
                    ->paginate($paginationValue);

                    $reportsCount = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->whereIn('leaves_request.id',$newResult)
                    ->whereRaw($whereraw)
                    ->get()->count();
                }
            }
            else
            {
                $tDate = date('Y-m-d');
                $requestedLeaves = RequestedLeaves::where('final_status',1)->get();
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

                $requestedLeaves = RequestedLeaves::
                whereIn('leaves_request.id',$newResult)
                ->whereRaw($whereraw)
                ->orderBy('id', 'desc')
                ->paginate($paginationValue);

                $reportsCount = RequestedLeaves::whereIn('id',$newResult)
                ->whereRaw($whereraw)
                ->orderBy('leaves_request.id', 'desc')
                ->get()->count();
            }
            
            
            

        }
        else
        {
            $loggedinUserid=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $tDate = date('Y-m-d');
                    $requestedLeaves = RequestedLeaves::where('final_status',1)->get();
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

                    $requestedLeaves = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->whereIn('leaves_request.id',$newResult)
                    ->orderBy('leaves_request.id', 'desc')
                    ->paginate($paginationValue);

                    $reportsCount = RequestedLeaves::join('employee_details', 'employee_details.emp_id', '=', 'leaves_request.emp_id')
                    ->select('employee_details.*', 'leaves_request.*','employee_details.id as rowid')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->whereIn('leaves_request.id',$newResult)
                    ->get()->count();

                }
            }
            else
            {
                $tDate = date('Y-m-d');
                $requestedLeaves = RequestedLeaves::where('final_status',1)->get();
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

                $requestedLeaves = RequestedLeaves::
                whereIn('leaves_request.id',$newResult)
                ->orderBy('leaves_request.id', 'desc')
                ->paginate($paginationValue);

                $reportsCount = RequestedLeaves::whereIn('id',$newResult)
                ->orderBy('leaves_request.id', 'desc')
                ->get()->count();
                //return $reportsCount;
            }
            
        }
        
        
				

			
		$requestedLeaves->setPath(config('app.url/listingTodaysLeaves'));		
	    return view("Employee_Leaves/listingTodaysLeaves",compact('requestedLeaves','paginationValue','reportsCount'));
	}

    public function createRequestedLeave(Request $request)
    {
		//return $request->all();

    	$validator = Validator::make($request->all(), 
        [			
			'leaveTypes' => 'required',
            'emp_drop' => 'required',
			'leave_fromdate' => 'required|date',
            'leave_todate' => 'required|date|after_or_equal:leave_fromdate',
            'reason' => 'required', 
        ],
		[
			'leaveTypes' => 'Please Select Requested Leave Type',
            'emp_drop.required'=> 'Please Select Employee',
			'leave_fromdate.required'=> 'Please Select From Date',
		 	'leave_todate.required'=> 'Please Select To Date',
            'leave_todate.after_or_equal'=> 'Date must be Equal or Greater than, Leave from Date',
			'reason.required'=> 'Please Select Reason for Leave',
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			$usersessionId=$request->session()->get('EmployeeId');
            $requestedLeaves = new RequestedLeaves();
			$requestedLeaves->emp_id = $request->emp_drop;
            $requestedLeaves->leave_id = $request->leaveTypes;
            $requestedLeaves->from_date = $request->leave_fromdate;
            $requestedLeaves->to_date = $request->leave_todate;
            $requestedLeaves->reason = $request->reason;
            $requestedLeaves->comment = $request->comments;

            $diff = strtotime($request->leave_todate) - strtotime($request->leave_fromdate);   
            // 1 day = 24 hours 
            // 24 * 60 * 60 = 86400 seconds 
            $num_days = abs(round($diff / 86400)); 
            $num_days = $num_days+1;

            $requestedLeaves->num_days = $num_days;
            $requestedLeaves->status = 1;
            $requestedLeaves->request_at = date('Y-m-d H:i:s');
            $requestedLeaves->request_by = $usersessionId;
            $requestedLeaves->save(); 
            
            

            $leavesLogs = new RequestedLeavesLog();
			$leavesLogs->emp_id = $request->emp_drop;
            $leavesLogs->leave_id = $request->leaveTypes;
            $leavesLogs->request_event = 1;
            $leavesLogs->event_at = date('Y-m-d');
            $leavesLogs->event_by = $usersessionId;
            $leavesLogs->row_id = $requestedLeaves->id;
            $leavesLogs->save();   

            return response()->json(['success'=>'Leave Submitted Successfully.']);
			
		} 
	}



    public function updateEditLeaveRequestPostData(Request $request)
    {
		//return $request->all();

    	$validator = Validator::make($request->all(), 
        [			
			
			'edit_leave_fromdate' => 'required|date',
            'edit_leave_todate' => 'required|date|after_or_equal:edit_leave_fromdate',
            'leave_status' => 'required', 
        ],
		[
			
			'edit_leave_fromdate.required'=> 'Please Select From Date',
		 	'edit_leave_todate.required'=> 'Please Select To Date',
            'edit_leave_todate.after_or_equal'=> 'Date must be Equal or Greater than, Leave from Date',
			'leave_status.required'=> 'Please Select Status',
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			
            
            
            $requestedLeaves = RequestedLeaves::where('emp_id',$request->emp_id)->where('id',$request->rowid)->orderBy('id','ASC')->first();
            
            $usersessionId=$request->session()->get('EmployeeId');
            
			

            $diff = strtotime($request->edit_leave_todate) - strtotime($request->edit_leave_fromdate);   
            // 1 day = 24 hours 
            // 24 * 60 * 60 = 86400 seconds 
            $num_days = abs(round($diff / 86400)); 
            $num_days = $num_days+1;

            $requestedLeaves->final_status = $request->leave_status; 
            $requestedLeaves->updated_from_date = $request->edit_leave_fromdate;
            $requestedLeaves->updated_to_date = $request->edit_leave_todate;
            $requestedLeaves->num_days = $num_days;   
            $requestedLeaves->save();   

            $leavesLogs = new RequestedLeavesLog();
			$leavesLogs->emp_id = $requestedLeaves->emp_id;
            $leavesLogs->leave_id = $requestedLeaves->leave_id;
            $leavesLogs->request_event = 4;
            $leavesLogs->event_at = date('Y-m-d');
            $leavesLogs->event_by = $usersessionId;
            $leavesLogs->row_id = $requestedLeaves->id;
            $leavesLogs->save();   

            return response()->json(['success'=>'Leave Updated Successfully.']);
			
		} 
	}


    public function updateRequestedLeaveApproved(Request $request)
    {
		//return $request->all();

    	$validator = Validator::make($request->all(), 
        [			
			'updated_leave_fromdate' => 'required_if:updatedleavedates,1',
            'updated_leave_todate' => 'required_if:updatedleavedates,1|date|after_or_equal:updated_leave_fromdate',
            'updatedcomments' => 'required',
        ],
		[
			'updated_leave_fromdate.required_if'=> 'Please Select From Date',
            'updated_leave_todate.required_if'=> 'Please Select To Date',
            'updated_leave_todate.after_or_equal'=> 'Date must be Equal or Greater than, Leave from Date',
            'updatedcomments.required' => 'Please write some comments',				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{

            if($request->finalsaved==1)
            {
                $usersessionId=$request->session()->get('EmployeeId');
           
                $requestedLeaves = RequestedLeaves::where('emp_id',$request->emp_id)->where('id',$request->rowid)->orderBy('id','ASC')->first();

                $diff = strtotime($request->updated_leave_todate) - strtotime($request->updated_leave_fromdate);   
                // 1 day = 24 hours 
                // 24 * 60 * 60 = 86400 seconds 
                $num_days = abs(round($diff / 86400)); 
                $num_days = $num_days+1;
                
                $requestedLeaves->approved_reject_status = 1;
                $requestedLeaves->approved_reject_at = date('Y-m-d H:i:s');
                $requestedLeaves->approved_reject_by = $usersessionId;
                $requestedLeaves->approved_reject_comment = $request->updatedcomments; 
                $requestedLeaves->final_status = 1; 
                $requestedLeaves->updated_from_date = $request->updated_leave_fromdate;
                $requestedLeaves->updated_to_date = $request->updated_leave_todate;
                $requestedLeaves->num_days = $num_days;             
                $requestedLeaves->save();   

                $leavesLogs = new RequestedLeavesLog();
                $leavesLogs->emp_id = $request->emp_id;
                $leavesLogs->leave_id = $requestedLeaves->leave_id;
                $leavesLogs->request_event = 2;
                $leavesLogs->event_at = date('Y-m-d');
                $leavesLogs->event_by = $usersessionId;
                $leavesLogs->row_id = $requestedLeaves->id;
                $leavesLogs->save();   

                return response()->json(['success'=>'Leave Request Updated Successfully.']);
            }
            else
            {
                return response()->json(['show'=>1]);
            }
            
           
			
		} 
	}


    public function updateRequestedLeaveRejected(Request $request)
    {
		//return $request->all();

    	$validator = Validator::make($request->all(), 
        [			
			'updatedcomments' => 'required',
        ],
		[
			'updatedcomments.required' => 'Please write some comments',				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			$usersessionId=$request->session()->get('EmployeeId');
           
            $requestedLeaves = RequestedLeaves::where('emp_id',$request->emp_id)->where('id',$request->rowid)->orderBy('id','ASC')->first();
			
            $requestedLeaves->approved_reject_status = 2;
            $requestedLeaves->approved_reject_at = date('Y-m-d H:i:s');
            $requestedLeaves->approved_reject_by = $usersessionId;
            $requestedLeaves->approved_reject_comment = $request->updatedcomments; 
            $requestedLeaves->final_status = 1;            
            $requestedLeaves->save(); 
            
            $leavesLogs = new RequestedLeavesLog();
			$leavesLogs->emp_id = $request->emp_id;
            $leavesLogs->leave_id = $requestedLeaves->leave_id;
            $leavesLogs->request_event = 3;
            $leavesLogs->event_at = date('Y-m-d');
            $leavesLogs->event_by = $usersessionId;
            $leavesLogs->row_id = $requestedLeaves->id;
            $leavesLogs->save();  

            return response()->json(['success'=>'Leave Request Updated Successfully.']);
			
		} 
	}


    public function getLeaveApprovedFormData(Request $request)
    {
        $empid = $request->empid;
        $rowid = $request->rowid;

        $requestedLeaves = RequestedLeaves::where('emp_id',$empid)->where('id',$rowid)
        ->orderBy('id', 'desc')
        ->first();

        return view("Employee_Leaves/leaveApprovedFormContent",compact('requestedLeaves'));	

    }

    public function editLeaveRequestBeforeStart(Request $request)
    {
        $empid = $request->empid;
        $rowid = $request->rowid;

        $requestedLeaves = RequestedLeaves::where('emp_id',$empid)->where('id',$rowid)
        ->orderBy('id', 'desc')
        ->first();

        return view("Employee_Leaves/editLeaveRequestBeforeStart",compact('requestedLeaves'));	

    }

    public function setPageLimitProcess(Request $request)
	{
		$offset = $request->offset;
		$request->session()->put('EmpLeaves_page_limit',$offset);
	}
    


    public function searchLeavesData(Request $request)
	{
			$department='';
			if($request->input('department')!=''){
			 
			 $department=implode(",", $request->input('department'));
			}
			$teamlaed='';
			if($request->input('teamlaed')!=''){
			 
			 $teamlaed=implode(",", $request->input('teamlaed'));
			}
			$dateto = $request->input('dateto');
			$datefrom = $request->input('datefrom');
			$name='';
			if($request->input('emp_name')!=''){
			 
			 $name=implode(",", $request->input('emp_name'));
			}
			//$name = $request->input('emp_name');
			$empId='';
			if($request->input('empId')!=''){
			 
			 $empId=implode(",", $request->input('empId'));
			}
			$design='';
			if($request->input('designationdata')!=''){
			 
			 $design=implode(",", $request->input('designationdata'));
			}
			$datetolastworkingday = $request->input('datetolastworkingday');
			$datefromlastworkingday = $request->input('datefromlastworkingday');
			//02-9-2023
			$ReasonofAttrition='';
			if($request->input('ReasonofAttrition')!=''){
			 
			 $ReasonofAttrition=implode(",", $request->input('ReasonofAttrition'));
			}
			$offboardstatus='';
			if($request->input('offboardstatus')!=''){
			 
			 $offboardstatus=implode(",", $request->input('offboardstatus'));
			}
			$datetodort = $request->input('datetodort');
			$datefromdort = $request->input('datefromdort');
			
			$offboardffstatus='';
			if($request->input('offboardffstatus')!=''){
			 
			 $offboardffstatus=implode(",", $request->input('offboardffstatus'));
			}

			$rangeid='';
			if($request->input('rangeid')!=''){
			 
			 $rangeid=implode(",", $request->input('rangeid'));
			}

			$request->session()->put('leaves_emp_name',$name);
            $request->session()->put('leaves_emp_id',$empId);
            $request->session()->put('emp_leaves_fromdate',$datefrom);
            $request->session()->put('emp_leaves_todate',$dateto);


			$request->session()->put('range_filter_inner_list',$rangeid);
			$request->session()->put('empid_emp_offboard_filter_inner_list',$empId);
			
			$request->session()->put('departmentId_filter_inner_list',$department);
			$request->session()->put('teamleader_filter_inner_list',$teamlaed);
			
			$request->session()->put('design_empoffboard_filter_inner_list',$design);
			$request->session()->put('dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			
			$request->session()->put('ReasonofAttrition_empoffboard_filter_list',$ReasonofAttrition);
			$request->session()->put('empoffboard_status_filter_list',$offboardstatus);
			$request->session()->put('datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('dateto_offboard_dort_list',$datetodort);
			$request->session()->put('empoffboard_ffstatus_filter_list',$offboardffstatus);
			 //return  redirect('listingPageonboarding');	
	}

    public function resetLeavesFilterData(Request $request)
    {
        $request->session()->put('leaves_emp_name','');
        $request->session()->put('leaves_emp_id','');
        $request->session()->put('emp_leaves_fromdate','');
		$request->session()->put('emp_leaves_todate','');
        
        
        
        $request->session()->put('departmentId_filter_inner_list','');
        $request->session()->put('teamleader_filter_inner_list','');
        $request->session()->put('name_emp_offboard_filter_inner_list','');
        $request->session()->put('empid_emp_offboard_filter_inner_list','');
        $request->session()->put('design_empoffboard_filter_inner_list','');
        $request->session()->put('dateto_offboard_lastworkingday_list','');
        $request->session()->put('datefrom_offboard_lastworkingday_list','');
        $request->session()->put('ReasonofAttrition_empoffboard_filter_list','');
        $request->session()->put('empoffboard_status_filter_list','');
        $request->session()->put('datefrom_offboard_dort_list','');
        $request->session()->put('dateto_offboard_dort_list','');
        $request->session()->put('empoffboard_ffstatus_filter_list','');
    }





    public function summaryTabsfullViewData(Request $request)
    {
         $empId = $request->empid;
         $rowid = $request->rowid;
         
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
         
         $visaProcessLists = '';
         return view("Employee_Leaves/summaryTabWithFullViewAjax",compact('leaveRequestDetails','stepsAll','percentange','OnboardingProgress'));
    }



    public function leaveRequestInfoTabsTwoData(Request $request)
    {
        $empId = $request->empid;
        $rowid = $request->rowid;
        
        $leaveRequestDetails = RequestedLeaves::where('emp_id',$empId)->where("id",$rowid)->orderBy("id","DESC")->first();


        $empsessionId=$request->session()->get('EmployeeId');
		$empDetails = User::where("id",$empsessionId)->first();

		

			if($empDetails)
			{
				
				$usersids = array(101456,101058,101042,100762,101466,101549,101558);

				if (in_array($empDetails->employee_id, $usersids))
				{
					$visiblebtn = 1;
				}
				else
				{
					$visiblebtn = 0;
				}
			}

    
        return view("Employee_Leaves/leaveRequestInfoinTabs",compact('leaveRequestDetails','visiblebtn')); 
    }

    public function finalLeaveRequestInfoData(Request $request)
    {
        $empId = $request->empid;
        $rowid = $request->rowid;
        
        $leaveRequestDetails = RequestedLeaves::where('emp_id',$empId)->where("id",$rowid)->orderBy("id","DESC")->first();

    
        return view("Employee_Leaves/finalLeaveRequestInfoTabs",compact('leaveRequestDetails')); 
    }


    public function requestedLeavesLogsData(Request $request)
    {
        $empid = $request->empid;
        $rowid = $request->rowid;

        //return 'Emp_id: '.$empid.' Row_id: '.$rowid;

        $leavesRequestLogsData = RequestedLeavesLog::where('emp_id',$empid)->orderBy('id','desc')->get();


        

        return view("Employee_Leaves/RequestedLeavesLogsDetails",compact('leavesRequestLogsData')); 
      
    }





}
