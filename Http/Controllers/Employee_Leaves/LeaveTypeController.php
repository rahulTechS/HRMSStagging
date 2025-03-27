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

class LeaveTypeController extends Controller
{
    public function __construct(LoggerFactory $logFactory)
    {
        //$this->log = $logFactory->setPath('logs/leaves')->createLogger('leaves'); 
    }
	public function Index(Request $request)
	{
        $empData = LeaveTypes::orderBy('id', 'desc')->get();

        $leaveTypesData = LeaveTypes::orderBy('id', 'desc')->get();


        
       // return view("Employee_Leaves/Index",compact('ReasonsForLeavingDetails','departmentLists','tL_details','empId','Designation'));
        return view("Employee_Leaves/Leave_Types/Index",compact('empData','leaveTypesData'));
    }


    public function newLeaveTypeFormData()
	{
		$leaveTypesdata = LeaveTypes::orderBy('id','ASC')->get();
		return view("Employee_Leaves/Leave_Types/addleaveTypeForm",compact('leaveTypesdata'));		
	}



   



  



    

    
    public function allLeaveTypesListingData(Request $request)
	{
		if(!empty($request->session()->get('EmpLeaves_page_limit')))
        {
            $paginationValue = $request->session()->get('EmpLeaves_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }
            
        $requestedLeaves = LeaveTypes::orderBy('id', 'desc')
        ->paginate($paginationValue);
            
        $reportsCount = LeaveTypes::orderBy('id', 'desc')
        ->get()->count();           
        
		$requestedLeaves->setPath(config('app.url/listingAllLeave_Types'));		
	    return view("Employee_Leaves/Leave_Types/listingAllLeave_Types",compact('requestedLeaves','paginationValue','reportsCount'));
	}



    public function createRequestedLeave(Request $request)
    {
		//return $request->all();

    	$validator = Validator::make($request->all(), 
        [			
			'leave_title' => 'required',
            'leave_count' => 'required|numeric',
			
        ],
		[
			
            'leave_title.required'=> 'Please Fill Leave Title',
			'leave_count.required'=> 'Please Fill Leave Counts',
		 	
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			$usersessionId=$request->session()->get('EmployeeId');
            $requestedLeaves = new LeaveTypes();
			$requestedLeaves->leaves_title = $request->leave_title;
            $requestedLeaves->total = $request->leave_count;
            $requestedLeaves->status = 1;
           
            $requestedLeaves->save(); 
            
          

            return response()->json(['success'=>'Leave type Added Successfully.']);
			
		} 
	}



    public function delete($id)
	{
		$leaveTypes = LeaveTypes::find($id)->delete();
        $empData = LeaveTypes::orderBy('id', 'desc')->get();
        $leaveTypesData = LeaveTypes::orderBy('id', 'desc')->get();
        
        return view("Employee_Leaves/Leave_Types/Index",compact('empData','leaveTypesData'));
	}




    public function editLeaveTypesData(Request $request)
    {
        
        $rowid = $request->rowid;

        $requestedLeaves = LeaveTypes::where('id',$rowid)
        ->orderBy('id', 'desc')
        ->first();

        return view("Employee_Leaves/Leave_Types/editLeaveTypes",compact('requestedLeaves'));	

    }





    public function updateLeaveTypePostData(Request $request)
    {
		//return $request->all();

    	$validator = Validator::make($request->all(), 
        [			
			
			'edit_leave_title' => 'required',
            'edit_leave_count' => 'required|numeric',
            'leave_status' => 'required', 
        ],
		[
			
			'edit_leave_title.required'=> 'Please Fill Leave Title',
		 	'edit_leave_count.required'=> 'Please Fill Leave Counts',
            
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			
            
            
            $requestedLeaves = LeaveTypes::where('id',$request->rowid)->orderBy('id','ASC')->first();
            
            $usersessionId=$request->session()->get('EmployeeId');
            
			

           

            $requestedLeaves->leaves_title = $request->edit_leave_title; 
            $requestedLeaves->total = $request->edit_leave_count;
            $requestedLeaves->status = $request->leave_status;
            $requestedLeaves->save();   

           

            return response()->json(['success'=>'Leave Updated Successfully.']);
			
		} 
	}




    public function setPageLimitProcess(Request $request)
	{
		$offset = $request->offset;
		$request->session()->put('EmpLeaves_page_limit',$offset);
	}
    














}
