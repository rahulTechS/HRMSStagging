<?php
namespace App\Http\Controllers\AgentTarget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\DepartmentForm;
use App\Models\Attribute\FormProduct;
use App\Models\Attribute\MasterAttribute;
use App\Models\Attribute\AttributeType;
use App\Models\Attribute\FormSection;
use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Http\Controllers\Attribute\DepartmentFormController;
use App\Models\Bank\SCB\SCBDepartmentFormChildEntry;
use App\Models\Bank\SCB\SCBDepartmentFormParentEntry;
use App\Models\Bank\SCB\SCBImportFile;
use App\Models\Bank\SCB\SCBBankMis;
use App\Models\Dashboard\MasterPayout;
use App\Models\SEPayout\RangeDetailsVintage;
use App\Models\Recruiter\Designation;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\EmpProcess\JobFunctionPermission;
use App\Models\Employee_Attendance\EmpAttendance;
use App\Models\Employee_Attendance\Attendance;
use Illuminate\Support\Facades\Validator;
use App\Models\EmpOffline\EmpOffline;
use App\Models\Employee_Attendance\EmpAttendanceCron;
use App\Models\Employee_Attendance\EmpAttendance2;
use DateTime;
use Session;
use App\Models\ChangeDepartment\ChangeDepartmentRequest;
use App\Models\ChangeDepartment\ChangeDepartmentRequestLog;
use App\Models\AgentTargets\AgentTarget;
use App\User;
use App\Models\Entry\Employee;
class AgentTargetController extends Controller
{
	public  function Index(Request $request)
	{
        //$empDetails = Employee_details::orderBy('id', 'desc')->get(); 
        
        $empDetailsIndex = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();

        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();

        $designationLists=Designation::where("status",1)->get();

        $empsessionId=$request->session()->get('EmployeeId');



		$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
		if($departmentDetails != '')
		{
			
			$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
			if($empDetails!='')
			{
				
				$design=Designation::where("tlsm",2)->where("department_id",$empDetails->dept_id)->where("status",1)->get();
				$designarray=array();
				foreach($design as $_design){
					$designarray[]=$_design->id;
				}
				$finalarray=implode(",",$designarray);				
				$tL_details = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("dept_id",$empDetails->dept_id)->where("offline_status",1)->get();
			}
		}
		else
		{
			$design=Designation::where("tlsm",2)->where("status",1)->get();
			$designarray=array();
			foreach($design as $_design){
				$designarray[]=$_design->id;
			}
			$finalarray=implode(",",$designarray);			
			$tL_details = Employee_details::orderBy("id","DESC")->whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->get();
		}




        $loggedinUserid=$request->session()->get('EmployeeId');   
        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        if($departmentDetails)
        {
            $empDetailsDept=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
            $departmentid = $empDetailsDept->dept_id;
        }
        else{
            $departmentid = '';
        }
        
        
        



        return view("AgentTarget/index",compact('empDetailsIndex','departmentLists','designationLists','tL_details','departmentid'));
    }

    public static function getLoggedinUser($loggedinUserid)
	{
        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        if($departmentDetails != '')
        {
            $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
            if($empDetails!='')
            {
				$employeeData=2;
            }
        }
        else
        {
			$employeeData=1;		
        }
		return $employeeData;
	}

    public function listingAllEmployeeData(Request $request)
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
        
        
        if(!empty($request->session()->get('agentTarget_page_limit')))
        {
            $paginationValue = $request->session()->get('agentTarget_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('agentTarget_emp_name')) && $request->session()->get('agentTarget_emp_name') != 'All')
        {
            $fname = $request->session()->get('agentTarget_emp_name');
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
    
                $finalcname=implode(",", $namefinalarray);
                
                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'emp_name IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And emp_name IN('.$finalcname.')';
                }
            }


           
        }


        if(!empty($request->session()->get('agentTarget_emp_id')) && $request->session()->get('agentTarget_emp_id') != 'All')
        {
            $empId = $request->session()->get('agentTarget_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('agentTarget_dept')) && $request->session()->get('agentTarget_dept') != 'All')
        {
            $deptid = $request->session()->get('agentTarget_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('agentTarget_designation')) && $request->session()->get('agentTarget_designation') != 'All')
        {
            $desigid = $request->session()->get('agentTarget_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_teamLeader')) && $request->session()->get('agentTarget_teamLeader') != 'All')
        {
            $tlid = $request->session()->get('agentTarget_teamLeader');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }


        if(!empty($request->session()->get('agentTarget_month_filter')) && $request->session()->get('agentTarget_month_filter') != 'All')
        {
            $monthYear = $request->session()->get('agentTarget_month_filter');

        }

        //$whereraw='';
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();


        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
            // exit;

            if($empData==1) // all
            {
                if(!empty($request->session()->get('agentTarget_month_filter')))
				{
					$targetView = explode("-",$monthYear);
					$month=$targetView[0];
					$year=$targetView[1];
					$tdate = $year.'-'.$month;
				}
				else
				{
					$tdate = date('Y-m');
				}
                
                
                $requestDetails = Employee_details::whereRaw($whereraw)->where('offline_status',1)->where('job_function',2)->orderBy('id', 'desc')
                //->toSql();	 
                //dd($requestDetails);						
                ->paginate($paginationValue);

                $reportsCount = Employee_details::whereRaw($whereraw)->where('offline_status',1)->where('job_function',2)->orderBy('id', 'desc')
                ->get()->count();
                
            }
            else // specific dept
            {
                if(!empty($request->session()->get('agentTarget_month_filter')))
				{
					$targetView = explode("-",$monthYear);
					$month=$targetView[0];
					$year=$targetView[1];
					$tdate = $year.'-'.$month;
				}
				else
				{
					$tdate = date('Y-m');
				}
                
                
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();


                $requestDetails = Employee_details::whereRaw($whereraw)->where('dept_id',$empDetails->dept_id)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->orderBy('id', 'desc')
                ->paginate($paginationValue);

                $reportsCount = Employee_details::whereRaw($whereraw)->where('dept_id',$empDetails->dept_id)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->orderBy('id', 'desc')
                ->get()->count();
            }
        }
        else
        {
            if($empData==1) // all
            {
                
                if(!empty($request->session()->get('agentTarget_month_filter')))
				{
					$targetView = explode("-",$monthYear);
					$month=$targetView[0];
					$year=$targetView[1];
					$tdate = $year.'-'.$month;
				}
				else
				{
					$tdate = date('Y-m');
				}
                
                $requestDetails = Employee_details::where('offline_status',1)->where('job_function',2)->orderBy('id', 'desc')						
                ->paginate($paginationValue);	
                
                $reportsCount = Employee_details::where('offline_status',1)->where('job_function',2)->orderBy('id','desc')
                ->get()->count();
            }
            else // specific dept
            {
                if(!empty($request->session()->get('agentTarget_month_filter')))
				{
					$targetView = explode("-",$monthYear);
					$month=$targetView[0];
					$year=$targetView[1];
					$tdate = $year.'-'.$month;
				}
				else
				{
					$tdate = date('Y-m');
				}
                
                
                

                
                // $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();


                
                $requestDetails = Employee_details::where('dept_id',$empDetails->dept_id)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->orderBy('id', 'desc')
                ->paginate($paginationValue);

                $reportsCount = Employee_details::where('dept_id',$empDetails->dept_id)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->orderBy('id', 'desc')
                ->get()->count();            
            }
        }




     
        $userDetails=User::where("id",$loggedinUserid)->first();
        $logempDetails = Employee_details::where('emp_id',$userDetails->employee_id)->orderBy('id', 'desc')->first();
        $editRole = '';
        if($logempDetails)
        {
            if($logempDetails->job_function==4)
            {
                $editRole = 1;
            }
            else
            {
                $editRole = '';
            }
        }
        
        $requestDetails->setPath(config('app.url/listingAll'));
        return view("AgentTarget/listingAll",compact('requestDetails','paginationValue','reportsCount','tdate','editRole'));
    }

    public function setAgentTargetPost(Request $request)
    {
        
        
            
            $emprowid = $request->emprowid;
            $target = $request->target;
    
            $rawData = explode("-",$emprowid);
    
            $empid = $rawData[0];
            $rowid = $rawData[1];
            $year = $rawData[2];
            $month = $rawData[3];
            $targetDate = $year.'-'.$month;
            $usersessionId=$request->session()->get('EmployeeId');
    
            $empDetails = AgentTarget::where('emp_id',$empid)->where('rowid',$rowid)->where('month_year',$targetDate)->orderBy('id', 'desc')->first();
    
            if($empDetails)
            {
                $empDetails->current_month_target = $target;
                $empDetails->month_target = $target;
                $empDetails->month_year = $year.'-'.$month;
                $empDetails->set_target_by = $usersessionId;
                $empDetails->set_target_at = date('Y-m-d');
                $empDetails->save();
    
                return response()->json(['success'=>'Target Updated Successfully.']);
    
            }
            else
            {
                
                $targetsData = new AgentTarget();
                $targetsData->emp_id = $empid;
                $targetsData->rowid = $rowid;           
                $targetsData->current_month_target = $target;
                $targetsData->month_target = $target;
                $targetsData->created_at = date('Y-m-d H:i:s');
                $targetsData->month_year = $year.'-'.$month;
                $targetsData->set_target_by = $usersessionId;
                $targetsData->set_target_at = date('Y-m-d');
                $targetsData->status = 1;					
                $targetsData->save(); 
                return response()->json(['success'=>'Target Saved Successfully.']);
            }

        
        
        
        
        
        
        
        



    }


    public function setAgentTargetviaCheckPost(Request $request)
    {
        
        //return $request->all();
        $parameters = $request->input(); 
		$selectedId = $parameters['selectedIds'];
        $targetval = $parameters['targetval'];
        $targetDate = $parameters['targetDate'];
        $usersessionId=$request->session()->get('EmployeeId');

        $rawData = explode("-",$targetDate);
        $year = $rawData[0];
        $month = $rawData[1];

        // echo "<pre>";
        // print_r($selectedId);
        // print_r($targetval); 
        // print_r($rawData);        
        // exit;


        if($parameters['targetval']!='')
        {
            foreach ($selectedId as $sid) 
            {
                $empDetails = AgentTarget::where('rowid',$sid)->where('month_year',$targetDate)->orderBy('id', 'desc')->first();

                $empinfoDetails = Employee_details::where('id',$sid)->orderBy('id', 'desc')->first();


    
                if($empDetails)
                {    
                    $empDetails->current_month_target = $targetval;
                    $empDetails->month_target = $targetval;                    
                    $empDetails->month_year = $year.'-'.$month;
                    $empDetails->set_target_by = $usersessionId;
                    $empDetails->set_target_at = date('Y-m-d');				
                    $empDetails->save(); 
                    
                }
                else
                {
                    $targetsData = new AgentTarget();
                    $targetsData->emp_id = $empinfoDetails->emp_id;
                    $targetsData->rowid = $sid;           
                    $targetsData->current_month_target = $targetval;
                    $targetsData->month_target = $targetval;
                    $targetsData->created_at = date('Y-m-d H:i:s');
                    $targetsData->month_year = $year.'-'.$month;
                    $targetsData->set_target_by = $usersessionId;
                    $targetsData->set_target_at = date('Y-m-d');
                    $targetsData->status = 1;					
                    $targetsData->save(); 
                }
                
            }
            return response()->json(['success'=>'Mass Target Saved Successfully.']);
        }
        else
        {
            return response()->json(['success'=>'Nothing to Save.']);
        }

        
        
    }



    public static function getAgentTarget($empid,$rowid,$tdate)
    {
        $empDetails = AgentTarget::where('emp_id',$empid)->where('rowid',$rowid)->where('month_year',$tdate)->orderBy('id', 'desc')->first();

        if($empDetails)
        {
            return $empDetails->current_month_target;
        }
        else
        {
            return "";
        }
    }

    public static function getPrevoiusTarget($empid,$rowid,$tdate)
    {
        $empDetails = AgentTarget::where('emp_id',$empid)->where('rowid',$rowid)->where('month_year',$tdate)->orderBy('id', 'desc')->first();

        if($empDetails)
        {
            return $empDetails->current_month_target;
        }
        else
        {
            return "NA";
        }
    }
    public function listingCBDAgentsData(Request $request)
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
        
        if(!empty($request->session()->get('agentTarget_page_limit')))
        {
            $paginationValue = $request->session()->get('agentTarget_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	



        if(!empty($request->session()->get('agentTarget_emp_name')) && $request->session()->get('agentTarget_emp_name') != 'All')
        {
            $fname = $request->session()->get('agentTarget_emp_name');
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
    
                $finalcname=implode(",", $namefinalarray);
                
                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'emp_name IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And emp_name IN('.$finalcname.')';
                }
            }


           
        }


        if(!empty($request->session()->get('agentTarget_emp_id')) && $request->session()->get('agentTarget_emp_id') != 'All')
        {
            $empId = $request->session()->get('agentTarget_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('agentTarget_dept')) && $request->session()->get('agentTarget_dept') != 'All')
        {
            $deptid = $request->session()->get('agentTarget_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('agentTarget_designation')) && $request->session()->get('agentTarget_designation') != 'All')
        {
            $desigid = $request->session()->get('agentTarget_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_teamLeader')) && $request->session()->get('agentTarget_teamLeader') != 'All')
        {
            $tlid = $request->session()->get('agentTarget_teamLeader');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }


        if(!empty($request->session()->get('agentTarget_month_filter')) && $request->session()->get('agentTarget_month_filter') != 'All')
        {
            $monthYear = $request->session()->get('agentTarget_month_filter');

        }


        
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        if($whereraw != '')
		{
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }

            $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
            $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
            
            $requestDetails = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',49)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',49)->orderBy('id', 'desc')
            ->get()->count();
        }
        else
        {
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }

            $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
            $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
            
            $requestDetails = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',49)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',49)->orderBy('id', 'desc')
            ->get()->count();
            
        }

        $userDetails=User::where("id",$loggedinUserid)->first();
        $logempDetails = Employee_details::where('emp_id',$userDetails->employee_id)->orderBy('id', 'desc')->first();
        $editRole = '';
        if($logempDetails)
        {
            if($logempDetails->job_function==4)
            {
                $editRole = 1;
            }
            else
            {
                $editRole = '';
            }
        }
        
        $requestDetails->setPath(config('app.url/listingCBDAgents'));
        return view("AgentTarget/listingCBDAgents",compact('requestDetails','paginationValue','reportsCount','tdate','editRole'));
    }


    public function listingMashreqAgentsData(Request $request)
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
        
        if(!empty($request->session()->get('agentTarget_page_limit')))
        {
            $paginationValue = $request->session()->get('agentTarget_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('agentTarget_emp_name')) && $request->session()->get('agentTarget_emp_name') != 'All')
        {
            $fname = $request->session()->get('agentTarget_emp_name');
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
    
                $finalcname=implode(",", $namefinalarray);
                
                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'emp_name IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And emp_name IN('.$finalcname.')';
                }
            }


           
        }


        if(!empty($request->session()->get('agentTarget_emp_id')) && $request->session()->get('agentTarget_emp_id') != 'All')
        {
            $empId = $request->session()->get('agentTarget_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('agentTarget_dept')) && $request->session()->get('agentTarget_dept') != 'All')
        {
            $deptid = $request->session()->get('agentTarget_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('agentTarget_designation')) && $request->session()->get('agentTarget_designation') != 'All')
        {
            $desigid = $request->session()->get('agentTarget_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_teamLeader')) && $request->session()->get('agentTarget_teamLeader') != 'All')
        {
            $tlid = $request->session()->get('agentTarget_teamLeader');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_month_filter')) && $request->session()->get('agentTarget_month_filter') != 'All')
        {
            $monthYear = $request->session()->get('agentTarget_month_filter');

        }


        
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();

        if($whereraw != '')
		{
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }
            
            
            $requestDetails = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',36)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',36)->orderBy('id', 'desc')
            ->get()->count();
            
        }
        else
        {
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }
            
            

            $requestDetails = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',36)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',36)->orderBy('id', 'desc')
            ->get()->count();
        }

        //return $requestDetails;

        $userDetails=User::where("id",$loggedinUserid)->first();
        $logempDetails = Employee_details::where('emp_id',$userDetails->employee_id)->orderBy('id', 'desc')->first();
        $editRole = '';
        if($logempDetails)
        {
            if($logempDetails->job_function==4)
            {
                $editRole = 1;
            }
            else
            {
                $editRole = '';
            }
        }
        
        $requestDetails->setPath(config('app.url/listingMashreqAgents'));
        return view("AgentTarget/listingMashreqAgents",compact('requestDetails','paginationValue','reportsCount','tdate','editRole'));
    }


    public function listingENBDAgentsData(Request $request)
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
        
        
        if(!empty($request->session()->get('agentTarget_page_limit')))
        {
            $paginationValue = $request->session()->get('agentTarget_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('agentTarget_emp_name')) && $request->session()->get('agentTarget_emp_name') != 'All')
        {
            $fname = $request->session()->get('agentTarget_emp_name');
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
    
                $finalcname=implode(",", $namefinalarray);
                
                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'emp_name IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And emp_name IN('.$finalcname.')';
                }
            }


           
        }


        if(!empty($request->session()->get('agentTarget_emp_id')) && $request->session()->get('agentTarget_emp_id') != 'All')
        {
            $empId = $request->session()->get('agentTarget_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('agentTarget_dept')) && $request->session()->get('agentTarget_dept') != 'All')
        {
            $deptid = $request->session()->get('agentTarget_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('agentTarget_designation')) && $request->session()->get('agentTarget_designation') != 'All')
        {
            $desigid = $request->session()->get('agentTarget_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_teamLeader')) && $request->session()->get('agentTarget_teamLeader') != 'All')
        {
            $tlid = $request->session()->get('agentTarget_teamLeader');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_month_filter')) && $request->session()->get('agentTarget_month_filter') != 'All')
        {
            $monthYear = $request->session()->get('agentTarget_month_filter');

        }

        //$whereraw='';
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();

        if($whereraw != '')
		{
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }
            
            $requestDetails = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',9)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',9)->orderBy('id', 'desc')
            ->get()->count();
        }
        else
        {
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }
            
            $requestDetails = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',9)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',9)->orderBy('id', 'desc')
            ->get()->count();
        }

        $userDetails=User::where("id",$loggedinUserid)->first();
        $logempDetails = Employee_details::where('emp_id',$userDetails->employee_id)->orderBy('id', 'desc')->first();
        $editRole = '';
        if($logempDetails)
        {
            if($logempDetails->job_function==4)
            {
                $editRole = 1;
            }
            else
            {
                $editRole = '';
            }
        }
        
        $requestDetails->setPath(config('app.url/listingENBDAgents'));
        return view("AgentTarget/listingENBDAgents",compact('requestDetails','paginationValue','reportsCount','tdate','editRole'));
    }

    public function listingDeemAgentsData(Request $request)
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
        
        
        if(!empty($request->session()->get('agentTarget_page_limit')))
        {
            $paginationValue = $request->session()->get('agentTarget_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('agentTarget_emp_name')) && $request->session()->get('agentTarget_emp_name') != 'All')
        {
            $fname = $request->session()->get('agentTarget_emp_name');
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
    
                $finalcname=implode(",", $namefinalarray);
                
                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'emp_name IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And emp_name IN('.$finalcname.')';
                }
            }


           
        }


        if(!empty($request->session()->get('agentTarget_emp_id')) && $request->session()->get('agentTarget_emp_id') != 'All')
        {
            $empId = $request->session()->get('agentTarget_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('agentTarget_dept')) && $request->session()->get('agentTarget_dept') != 'All')
        {
            $deptid = $request->session()->get('agentTarget_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('agentTarget_designation')) && $request->session()->get('agentTarget_designation') != 'All')
        {
            $desigid = $request->session()->get('agentTarget_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_teamLeader')) && $request->session()->get('agentTarget_teamLeader') != 'All')
        {
            $tlid = $request->session()->get('agentTarget_teamLeader');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_month_filter')) && $request->session()->get('agentTarget_month_filter') != 'All')
        {
            $monthYear = $request->session()->get('agentTarget_month_filter');

        }

        //$whereraw='';
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();

        if($whereraw != '')
		{
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }

            $requestDetails = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',8)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',8)->orderBy('id', 'desc')
            ->get()->count();
        }
        else
        {
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }
            
            $requestDetails = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',8)->orderBy('id', 'desc')
            // ->toSql();
            // dd($requestDetails);						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',8)->orderBy('id', 'desc')
            ->get()->count();
        }

        $userDetails=User::where("id",$loggedinUserid)->first();
        $logempDetails = Employee_details::where('emp_id',$userDetails->employee_id)->orderBy('id', 'desc')->first();
        $editRole = '';
        if($logempDetails)
        {
            if($logempDetails->job_function==4)
            {
                $editRole = 1;
            }
            else
            {
                $editRole = '';
            }
        }
        
        $requestDetails->setPath(config('app.url/listingDeemAgents'));
        return view("AgentTarget/listingDeemAgents",compact('requestDetails','paginationValue','reportsCount','tdate','editRole'));
    }


    public function listingAafaqAgentsData(Request $request)
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
        
        
        if(!empty($request->session()->get('agentTarget_page_limit')))
        {
            $paginationValue = $request->session()->get('agentTarget_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('agentTarget_emp_name')) && $request->session()->get('agentTarget_emp_name') != 'All')
        {
            $fname = $request->session()->get('agentTarget_emp_name');
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
    
                $finalcname=implode(",", $namefinalarray);
                
                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'emp_name IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And emp_name IN('.$finalcname.')';
                }
            }


           
        }


        if(!empty($request->session()->get('agentTarget_emp_id')) && $request->session()->get('agentTarget_emp_id') != 'All')
        {
            $empId = $request->session()->get('agentTarget_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('agentTarget_dept')) && $request->session()->get('agentTarget_dept') != 'All')
        {
            $deptid = $request->session()->get('agentTarget_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('agentTarget_designation')) && $request->session()->get('agentTarget_designation') != 'All')
        {
            $desigid = $request->session()->get('agentTarget_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_teamLeader')) && $request->session()->get('agentTarget_teamLeader') != 'All')
        {
            $tlid = $request->session()->get('agentTarget_teamLeader');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_month_filter')) && $request->session()->get('agentTarget_month_filter') != 'All')
        {
            $monthYear = $request->session()->get('agentTarget_month_filter');

        }

        //$whereraw='';
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();

        if($whereraw != '')
		{
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }
            
            
            $requestDetails = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',43)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',43)->orderBy('id', 'desc')
            ->get()->count();
        }
        else
        {
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }
            
            $requestDetails = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',43)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',43)->orderBy('id', 'desc')
            ->get()->count();
        }

        $userDetails=User::where("id",$loggedinUserid)->first();
        $logempDetails = Employee_details::where('emp_id',$userDetails->employee_id)->orderBy('id', 'desc')->first();
        $editRole = '';
        if($logempDetails)
        {
            if($logempDetails->job_function==4)
            {
                $editRole = 1;
            }
            else
            {
                $editRole = '';
            }
        }
        
        $requestDetails->setPath(config('app.url/listingAafaqAgents'));
        return view("AgentTarget/listingAafaqAgents",compact('requestDetails','paginationValue','reportsCount','tdate','editRole'));
    }

    public function listingDIBAgentsData(Request $request)
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
        
        
        if(!empty($request->session()->get('agentTarget_page_limit')))
        {
            $paginationValue = $request->session()->get('agentTarget_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('agentTarget_emp_name')) && $request->session()->get('agentTarget_emp_name') != 'All')
        {
            $fname = $request->session()->get('agentTarget_emp_name');
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
    
                $finalcname=implode(",", $namefinalarray);
                
                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'emp_name IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And emp_name IN('.$finalcname.')';
                }
            }


           
        }


        if(!empty($request->session()->get('agentTarget_emp_id')) && $request->session()->get('agentTarget_emp_id') != 'All')
        {
            $empId = $request->session()->get('agentTarget_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('agentTarget_dept')) && $request->session()->get('agentTarget_dept') != 'All')
        {
            $deptid = $request->session()->get('agentTarget_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('agentTarget_designation')) && $request->session()->get('agentTarget_designation') != 'All')
        {
            $desigid = $request->session()->get('agentTarget_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_teamLeader')) && $request->session()->get('agentTarget_teamLeader') != 'All')
        {
            $tlid = $request->session()->get('agentTarget_teamLeader');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_month_filter')) && $request->session()->get('agentTarget_month_filter') != 'All')
        {
            $monthYear = $request->session()->get('agentTarget_month_filter');

        }

        //$whereraw='';
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();

        if($whereraw != '')
		{
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }
            
            $requestDetails = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',46)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',46)->orderBy('id', 'desc')
            ->get()->count();
        }
        else
        {
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }
            
            $requestDetails = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',46)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',46)->orderBy('id', 'desc')
            ->get()->count();
        }

        $userDetails=User::where("id",$loggedinUserid)->first();
        $logempDetails = Employee_details::where('emp_id',$userDetails->employee_id)->orderBy('id', 'desc')->first();
        $editRole = '';
        if($logempDetails)
        {
            if($logempDetails->job_function==4)
            {
                $editRole = 1;
            }
            else
            {
                $editRole = '';
            }
        }
        
        $requestDetails->setPath(config('app.url/listingDIBAgents'));
        return view("AgentTarget/listingDIBAgents",compact('requestDetails','paginationValue','reportsCount','tdate','editRole'));
    }

    public function listingSCBAgentsData(Request $request)
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
        
        
        if(!empty($request->session()->get('agentTarget_page_limit')))
        {
            $paginationValue = $request->session()->get('agentTarget_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('agentTarget_emp_name')) && $request->session()->get('agentTarget_emp_name') != 'All')
        {
            $fname = $request->session()->get('agentTarget_emp_name');
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
    
                $finalcname=implode(",", $namefinalarray);
                
                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'emp_name IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And emp_name IN('.$finalcname.')';
                }
            }


           
        }


        if(!empty($request->session()->get('agentTarget_emp_id')) && $request->session()->get('agentTarget_emp_id') != 'All')
        {
            $empId = $request->session()->get('agentTarget_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('agentTarget_dept')) && $request->session()->get('agentTarget_dept') != 'All')
        {
            $deptid = $request->session()->get('agentTarget_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('agentTarget_designation')) && $request->session()->get('agentTarget_designation') != 'All')
        {
            $desigid = $request->session()->get('agentTarget_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_teamLeader')) && $request->session()->get('agentTarget_teamLeader') != 'All')
        {
            $tlid = $request->session()->get('agentTarget_teamLeader');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }


        if(!empty($request->session()->get('agentTarget_month_filter')) && $request->session()->get('agentTarget_month_filter') != 'All')
        {
            $monthYear = $request->session()->get('agentTarget_month_filter');

        }

        //$whereraw='';
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();

        if($whereraw != '')
		{
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }
            
            
            $requestDetails = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',47)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',47)->orderBy('id', 'desc')
            ->get()->count();
        }
        else
        {
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }
            
            
            $requestDetails = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',47)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',47)->orderBy('id', 'desc')
            ->get()->count();
        }

        $userDetails=User::where("id",$loggedinUserid)->first();
        $logempDetails = Employee_details::where('emp_id',$userDetails->employee_id)->orderBy('id', 'desc')->first();
        $editRole = '';
        if($logempDetails)
        {
            if($logempDetails->job_function==4)
            {
                $editRole = 1;
            }
            else
            {
                $editRole = '';
            }
        }
        
        $requestDetails->setPath(config('app.url/listingSCBAgents'));
        return view("AgentTarget/listingSCBAgents",compact('requestDetails','paginationValue','reportsCount','tdate','editRole'));
    }


    public function listingEIBAgentsData(Request $request)
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
        
        
        if(!empty($request->session()->get('agentTarget_page_limit')))
        {
            $paginationValue = $request->session()->get('agentTarget_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('agentTarget_emp_name')) && $request->session()->get('agentTarget_emp_name') != 'All')
        {
            $fname = $request->session()->get('agentTarget_emp_name');
            if($fname==',')
            {               
            }
            else
            {
                $cnameArray = explode(",",$fname);
                
                $namefinalarray=array();
                foreach($cnameArray as $namearray){
                    $namefinalarray[]="'".$namearray."'";                
                }
    
                $finalcname=implode(",", $namefinalarray);
                
                if($whereraw == '')
                {
                    //$whereraw = 'emp_name like "%'.$fname.'%"';
                    $whereraw = 'emp_name IN('.$finalcname.')';
                }
                else
                {
                    $whereraw .= ' And emp_name IN('.$finalcname.')';
                }
            }


           
        }


        if(!empty($request->session()->get('agentTarget_emp_id')) && $request->session()->get('agentTarget_emp_id') != 'All')
        {
            $empId = $request->session()->get('agentTarget_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }



        if(!empty($request->session()->get('agentTarget_dept')) && $request->session()->get('agentTarget_dept') != 'All')
        {
            $deptid = $request->session()->get('agentTarget_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('agentTarget_designation')) && $request->session()->get('agentTarget_designation') != 'All')
        {
            $desigid = $request->session()->get('agentTarget_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('agentTarget_teamLeader')) && $request->session()->get('agentTarget_teamLeader') != 'All')
        {
            $tlid = $request->session()->get('agentTarget_teamLeader');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }


        if(!empty($request->session()->get('agentTarget_month_filter')) && $request->session()->get('agentTarget_month_filter') != 'All')
        {
            $monthYear = $request->session()->get('agentTarget_month_filter');

        }

        //$whereraw='';
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();

        if($whereraw != '')
		{
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }
            
            
            $requestDetails = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',52)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::whereRaw($whereraw)->where('tl_id',$empDetails->id)->where('offline_status',1)->where('job_function',2)->where('dept_id',52)->orderBy('id', 'desc')
            ->get()->count();
        }
        else
        {
            
            if(!empty($request->session()->get('agentTarget_month_filter')))
            {
                $targetView = explode("-",$monthYear);
                $month=$targetView[0];
                $year=$targetView[1];
                $tdate = $year.'-'.$month;
            }
            else
            {
                $tdate = date('Y-m');
            }
            
            $requestDetails = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',52)->orderBy('id', 'desc')						
            ->paginate($paginationValue);	
            
            $reportsCount = Employee_details::where('offline_status',1)->where('tl_id',$empDetails->id)->where('job_function',2)->where('dept_id',52)->orderBy('id', 'desc')
            ->get()->count();
        }


        $userDetails=User::where("id",$loggedinUserid)->first();
        $logempDetails = Employee_details::where('emp_id',$userDetails->employee_id)->orderBy('id', 'desc')->first();
        $editRole = '';
        if($logempDetails)
        {
            if($logempDetails->job_function==4)
            {
                $editRole = 1;
            }
            else
            {
                $editRole = '';
            }
        }
        
        $requestDetails->setPath(config('app.url/listingEIBAgents'));
        return view("AgentTarget/listingEIBAgents",compact('requestDetails','paginationValue','reportsCount','tdate','editRole'));
    }

    public static function getEmpName($empid)
    {
        $empData = Employee_details::where('emp_id', $empid)->orderBy('id','desc')->first();

        if($empData)
        {
            return $empData->emp_name;
        }
        else
        {
            return "--";
        }
    }

    public static function getDesignation($empid)
    {
        $empInfo = Employee_attribute::where('emp_id',$empid)->where('attribute_code','DESIGN')->orderBy('id', 'desc')->first();

        if($empInfo)
        {
            return $empInfo->attribute_values;
        }
        else
        {
            return '--';
        }
    }

    public static function getDepartment($deptid)
    {
        
            $deptInfo = Department::where('id',$deptid)->orderBy('id', 'desc')->first();
            if($deptInfo)
            {
                return $deptInfo->department_name;
            }
            else
            {
                return '--';
            }
      
    }
    public static function getTL($empid)
    {
        $empInfo = Employee_details::where('emp_id',$empid)->orderBy('id', 'desc')->first();

        if($empInfo)
        {
            //$tL_details = Employee_details::where("job_role","Team Leader")->where("id",$empInfo->tl_id)->orderBy("id","ASC")->first();
            $tL_details = Employee_details::where("id",$empInfo->tl_id)->orderBy("id","ASC")->first();


            if($tL_details)
            {
                return $tL_details->emp_name;
            }
            else
            {
                return '--';
            }
        }
        else
        {
            return '--';
        }
    }

    

    public static function getWorkLocation($empid)
    {
        $empInfo = Employee_attribute::where('emp_id',$empid)->where('attribute_code','work_location')->orderBy('id', 'desc')->first();

        if($empInfo)
        {
            return $empInfo->attribute_values;
        }
        else
        {
            return '--';
        }
    }

    public function setPageLimitProcess(Request $request)
	{
		$offset = $request->offset;
		$request->session()->put('agentTarget_page_limit',$offset);
	}

    public function searchAgentTargettFilter(Request $request)
	{
            $department='';
            if($request->input('department')!=''){
            
            $department=implode(",", $request->input('department'));
            }

            $newDepartment='';
			if($request->input('newDepartment')!=''){
			 
			 $newDepartment=implode(",", $request->input('newDepartment'));
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

			$request->session()->put('agentTarget_emp_name',$name);
            $request->session()->put('agentTarget_emp_id',$empId);
            $request->session()->put('agentTarget_dept',$department);
            $request->session()->put('agentTarget_designation',$design);
            $request->session()->put('agentTarget_teamLeader',$teamlaed);
            $request->session()->put('agentTarget_month_filter',$datefrom);



            $request->session()->put('emp_leaves_fromdate',$datefrom);
            $request->session()->put('emp_leaves_todate',$dateto);


			
	}

    public function resetAgentTargetFilter(Request $request)
    {
        $request->session()->put('agentTarget_emp_name','');
        $request->session()->put('agentTarget_emp_id','');
        $request->session()->put('agentTarget_dept','');
        $request->session()->put('agentTarget_designation','');
        $request->session()->put('agentTarget_teamLeader','');
        $request->session()->put('agentTarget_month_filter','');


        


        $request->session()->put('emp_leaves_fromdate','');
		$request->session()->put('emp_leaves_todate','');
        
        
    }
    public static function getUserName($id)
    {
            $data = Employee::where('id',$id)->orderBy("id","DESC")->first();
            //print_r($data);
            if($data != '')
            {
                return $data->fullname;
            }
            else
            {
                return '';
            }
    }
    public function getAgentTargetData(Request $request)
    {
        $monthYear = $request->session()->get('agentTarget_month_filter');
        if(!empty($request->session()->get('agentTarget_month_filter')))
        {
            $targetView = explode("-",$monthYear);
            $month=$targetView[0];
            $year=$targetView[1];
            $tdate = $year.'-'.$month;
        }
        else
        {
            $tdate = date('Y-m');
        }

        return view("AgentTarget/addRequestContent",compact('tdate'));

    }

    public function exportTargetReport(Request $request)
	{
        //return $request->all();
        $parameters = $request->input(); 
        $selectedId = $parameters['selectedIds'];
        $month = $parameters['month'];
        $year = $parameters['year'];
        $targetDate = $year.'-'.$month;
            
        $filename = 'agent_target_report_'.date("d-m-Y").'.xlsx';
        $spreadsheet = new Spreadsheet(); 
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Agent Target List - '.$month.'/'.$year)->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
        $indexCounter = 2;
        $sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('D'.$indexCounter, strtoupper('Team Leader'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('E'.$indexCounter, strtoupper('Designation'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('F'.$indexCounter, strtoupper('Department'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('G'.$indexCounter, strtoupper('Previous Month Target'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
        $sheet->setCellValue('H'.$indexCounter, strtoupper('Current Month Target'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            

        $sn = 1;
        foreach ($selectedId as $sid) 
        {
            //echo $sid;
            $misData = Employee_details::where("id",$sid)->first();

            //$empName = $this->getEmployeeName($misData->emp_id);
            $teamLeader = $this->getTL($misData->emp_id);
            $designation = $this->getDesignation($misData->emp_id);
            $dept = $this->getDepartment($misData->dept_id);
            $empname = $this->getEmpName($misData->emp_id);
            // $vintage = $this->getVintage($misData->emp_id);

            
            $empDetails = AgentTarget::where('emp_id',$misData->emp_id)->where('rowid',$sid)->where('month_year',$targetDate)->orderBy('id', 'desc')->first();

            if($empDetails)
            {
                $target = $empDetails->current_month_target;
            }
            else
            {
                $target = "--";
            }


            $previousmonthYear = date('Y-m', strtotime($targetDate." -1 month"));
            $previousTarget = $this->getPrevoiusTarget($misData->emp_id,$sid,$previousmonthYear);



            $indexCounter++; 
            
            
            
            $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('C'.$indexCounter, $empname)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('D'.$indexCounter, $teamLeader)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('E'.$indexCounter, $designation)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('F'.$indexCounter, $dept)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('G'.$indexCounter, $previousTarget)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
            $sheet->setCellValue('H'.$indexCounter, $target)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');


            
            
            
            $sn++;
            
        }
            
            
        for($col = 'A'; $col !== 'H'; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
            
        $spreadsheet->getActiveSheet()->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
                
        for($index=1;$index<=$indexCounter;$index++)
        {
                foreach (range('A','H') as $col) {
                    $spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
                }
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save(public_path('uploads/exportAgentTargets/'.$filename));	
        echo $filename;
        exit;
	}

}