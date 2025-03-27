<?php
namespace App\Http\Controllers\SalaryCertificate;

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
use App\Models\AdvancedPay\AdvancedPayRequest;
use App\Models\AdvancedPay\RecoveryAmt;

use App\Models\ChangeDepartment\ChangeDepartmentRequestLog;

use App\User;
use App\Models\Entry\Employee;
use App\Models\AdvancedPay\AdvancedPayLogs;

use App\Models\SalaryCertificate\SalaryCertificate;

class SalaryCertificateController extends Controller
{
	public  function Index(Request $request)
	{
        //$empDetails = Employee_details::orderBy('id', 'desc')->get(); 
        //$empDetailsIndex = Employee_details::orderBy('id', 'desc')->get();





        $tdate = date('Y-m-d');
				

        $empData_details = Employee_details::orderBy('id','desc')->get();
        $lastworkingdate='';
        $offmarkEmp=array();
        foreach($empData_details as $emp)
        {
            if($emp->offline_status==2)
            {
                $offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
                if($offlineEmpData)
                {
                    if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
                    {
                        $lastworkingdate = $offlineEmpData->last_working_day_resign;
                    }
                    elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
                    {
                        $lastworkingdate = $offlineEmpData->last_working_day_terminate;
                    }
                    else
                    {
                        $new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
                        $lastworkingdate = $new_date;
                    }							
                }

                if($lastworkingdate)
                {
                    
                        if($lastworkingdate < $tdate)
                        {
                            $offmarkEmp[]=$emp->emp_id;
                        }
                        
                    

                }
            }
            else
            {
                
                    if($emp->doj > $tdate)
                    {
                        $offmarkEmp[]=$emp->emp_id;									
                    }
                    
                
            }
        }

        $empDetailsIndex = Employee_details::whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
        ->get();









        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $designationLists=Designation::where("status",1)->get();

        $empsessionId=$request->session()->get('EmployeeId');
		$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
		if($departmentDetails != '')
		{
			//return "Hello".$empDetails->dept_id;
			$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
			if($empDetails!='')
			{
				//return "Hello".$empDetails->dept_id;47
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
        return view("SalaryCertificate/index",compact('empDetailsIndex','departmentLists','designationLists','tL_details'));
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


    public function allSalarySlipsListing(Request $request)
    {
        $whereraw = '';
        $whererawMonthDate = '';
        $filterMonth='';
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
        
        
        if(!empty($request->session()->get('paySlipsRequest_page_limit')))
        {
            $paginationValue = $request->session()->get('paySlipsRequest_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	


        if(!empty($request->session()->get('paySlip_requests_emp_name')) && $request->session()->get('paySlip_requests_emp_name') != 'All')
        {
            $fname = $request->session()->get('paySlip_requests_emp_name');
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


        if(!empty($request->session()->get('paySlip_requests_emp_id')) && $request->session()->get('paySlip_requests_emp_id') != 'All')
        {
            $empId = $request->session()->get('paySlip_requests_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }

        if(!empty($request->session()->get('paySlip_month_filter')) && $request->session()->get('paySlip_month_filter') != 'All')
        {
            $datefrom = $request->session()->get('paySlip_month_filter');
            $filterMonth=$datefrom;

            if($whereraw == '')
            {
                $whereraw = 'pay_slip_month IN ("'.$datefrom.'")';
            }
            else
            {
                $whereraw .= ' And pay_slip_month IN ("'.$datefrom.'")';
            }
        }



        if(!empty($request->session()->get('paySlip_requests_dept')) && $request->session()->get('paySlip_requests_dept') != 'All')
        {
            $deptid = $request->session()->get('paySlip_requests_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('advancedpay_requests_designation')) && $request->session()->get('advancedpay_requests_designation') != 'All')
        {
            $desigid = $request->session()->get('advancedpay_requests_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('paySlip_requests_tl')) && $request->session()->get('paySlip_requests_tl') != 'All')
        {
            $tlid = $request->session()->get('paySlip_requests_tl');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }


        //$whereraw='';
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
            // exit;
            if($empData==1) // all
            {
                
                if($filterMonth)
                {
                    $requestDetails = SalaryCertificate::whereRaw($whereraw)->orderBy('id', 'desc')					
                    ->paginate($paginationValue);
    
                    $reportsCount = SalaryCertificate::whereRaw($whereraw)->orderBy('id','desc')
                    ->get()->count();
                }
                else
                {
                    
                    // $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
                    // ->get();
                    
                    // $newResult=array();
                    // foreach($empDetails as $value)
                    // {
                    //     $newResult[]=$value->emp_id;
                    // }
                    
                    $requestDetails = SalaryCertificate::whereRaw($whereraw)->orderBy('id', 'desc')					
                    ->paginate($paginationValue);
    
                    $reportsCount = SalaryCertificate::whereRaw($whereraw)->orderBy('id','desc')
                    ->get()->count();
                }
                
                
                    
                    
                    
                    
                
                
                
                
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();


                $empDetails = Employee_details::whereRaw($whereraw)->where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                
                // $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id', 'desc')					
                // ->paginate($paginationValue);
                // $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id','desc')
                // ->get()->count();






                if($filterMonth)
                {
                    $requestDetails = SalaryCertificate::whereRaw($whereraw)->whereIn('emp_id',$newResult)->orderBy('id', 'desc')					
                    ->paginate($paginationValue);
    
                    $reportsCount = SalaryCertificate::whereRaw($whereraw)->whereIn('emp_id',$newResult)->orderBy('id','desc')
                    ->get()->count();
                }
                else
                {
                    
                    $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
                    ->get();
                    
                    $newResult=array();
                    foreach($empDetails as $value)
                    {
                        $newResult[]=$value->emp_id;
                    }
                    
                    $requestDetails = SalaryCertificate::whereIn('emp_id',$newResult)->orderBy('id', 'desc')					
                    ->paginate($paginationValue);
    
                    $reportsCount = SalaryCertificate::whereIn('emp_id',$newResult)->orderBy('id','desc')
                    ->get()->count();
                }




            }
        }
        else
        {
            if($empData==1) // all
            {
                // $requestDetails = Employee_details::select('emp_id','dept_id','tl_id','emp_name')->where('offline_status',1)->orderBy('id', 'desc')
                // //->toSql();	 
                // //dd($documentCollectiondetails);						
                // ->paginate($paginationValue);	
                
                // $reportsCount = Employee_details::select('emp_id')->where('offline_status',1)
                // ->get()->count();





                $tdate = date('Y-m-d');
				

				$empData_details = Employee_details::orderBy('id','desc')->get();
				$lastworkingdate='';
				$offmarkEmp=array();
				foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							
								if($lastworkingdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;
								}
								
							

						}
					}
					else
					{
						
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;									
							}
							
						
					}
				}

				$requestDetails = Employee_details::select('emp_id','dept_id','tl_id','emp_name')->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				->paginate($paginationValue);

				$reportsCount = Employee_details::select('emp_id','dept_id','tl_id','emp_name')->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
                ->get()->count();











                
            }
            else // specific dept
            {
                
                // $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                // $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                // $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
                
                
                // $requestDetails = Employee_details::select('emp_id','dept_id','tl_id','emp_name')->where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                // ->paginate($paginationValue);
                
                // // $newResult=array();
                // // foreach($empDetails as $value)
                // // {
                // //     $newResult[]=$value->emp_id;
                // // }


                // // $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id', 'desc')
                // // //->toSql();	 
                // // //dd($documentCollectiondetails);						
                // // ->paginate($paginationValue);


                

                
                // $reportsCount = Employee_details::select('emp_id','dept_id','tl_id','emp_name')->where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                //->get()->count();





                
				$tdate = date('Y-m-d');
				

				$departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
				$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
				$empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
				$lastworkingdate='';
				$offmarkEmp=array();


                foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							
								if($lastworkingdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;
								}
								
							

						}
					}
					else
					{
						
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;									
							}
							
						
					}
				}

				

				$requestDetails = Employee_details::select('emp_id','dept_id','tl_id','emp_name')->where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				->paginate($paginationValue);

				$reportsCount = Employee_details::select('emp_id','dept_id','tl_id','emp_name')->where('employee_details.dept_id',$empDetails->dept_id)->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
                ->get()->count();
            }
        }

        //return $requestDetails;
        
        $requestDetails->setPath(config('app.url/listingAll'));
        return view("SalaryCertificate/listingAll",compact('requestDetails','paginationValue','reportsCount','filterMonth'));
    }

    
    public static function uploadPaySlipFormData(Request $request)
    {
        $empid=$request->empid;
        $paymonth=$request->paymonth;
        
        
        
        $loggedinUserid=$request->session()->get('EmployeeId');
        

        $empDetails = Employee_details::where('emp_id',$empid)->where('offline_status',1)->orderBy('id', 'desc')->first();
        
        //$empDetails = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();

        return view("SalaryCertificate/uploadPaySlipForm",compact('empDetails','paymonth'));
    }

    public static function getEmpContentData(Request $request)
    {
        $empid=$request->empid;
        $empDetails='';
        
        return view("SalaryCertificate/addRequestContent");

    }

    public function uploadPaySlipRequestPostSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
			//'file_upload' => 'required|mimes:csv,txt,pdf,jpeg,png,jpg',
            'file_paySlip' => 'required|mimes:pdf',
            //'editdepartment' => 'required',
            //'editdesignation' => 'required',
           // 'teamleaders' => 'required', 
        ],
		[
			//'customFile' => '',
            //'file_upload.required'=> 'Please Select file to upload',
		 	//'editdesignation.required'=> 'Please Select New Designation from List',
			//'teamleaders.required'=> 'Please Select Team Leader from List',
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            //return $request->all();

            
            $file = $request->file('file_paySlip');
            $filename = $request->empid.'_SalaryCertificate_'.date("Y-m-d_h-i-s").'.pdf';  
            //$filename = time().'_'.$file->getClientOriginalName();

            if(file_exists(public_path('paySlips/'.$filename)))
            {
                unlink(public_path('paySlips/'.$filename));
            }
            
            // File extension
            $extension = $file->getClientOriginalExtension();

            // File upload location
            $location = 'paySlips';

            // Upload file
            $file->move(public_path('paySlips/'), $filename);

            // File path
            $filepath = url('paySlips/'.$filename);


            $usersessionId=$request->session()->get('EmployeeId');
            $empDetails = Employee_details::where('emp_id',$request->empid)->where('offline_status',1)->orderBy('id', 'desc')->first();
            $paySlipRequestData = SalaryCertificate::where('emp_id',$request->empid)->where('pay_slip_month',$request->paymonth)->orderBy('id', 'desc')->first();


            if($paySlipRequestData)
            {
                // $paySlipRequestData->emp_id = $request->empid;
                // $paySlipRequestData->dept_id = $empDetails->dept_id;
                // $paySlipRequestData->tl_id = $empDetails->tl_id;
                // $paySlipRequestData->emp_name = $empDetails->emp_name;
                // if($filename!='')
                // {
                //     $paySlipRequestData->pay_slip_file = $filename;
                // }	
    
                // $paySlipRequestData->pay_slip_month = $request->paymonth;
                // $paySlipRequestData->created_at = date('Y-m-d H:i:s'); 
                // $paySlipRequestData->created_by = $usersessionId;    
                // $paySlipRequestData->save();
                return response()->json(['exist'=>'Salary Certificate Already Uploaded.']);
            }
            else
            {
                $paySlipRequestData = new SalaryCertificate();
                $paySlipRequestData->emp_id = $request->empid;
                $paySlipRequestData->dept_id = $empDetails->dept_id;
                $paySlipRequestData->tl_id = $empDetails->tl_id;
                $paySlipRequestData->emp_name = $empDetails->emp_name;
                if($filename!='')
                {
                    $paySlipRequestData->pay_slip_file = $filename;
                }	
    
                $paySlipRequestData->pay_slip_month = $request->paymonth;
                $paySlipRequestData->created_at = date('Y-m-d H:i:s'); 
                $paySlipRequestData->created_by = $usersessionId;  
                $paySlipRequestData->comments = $request->requestComments;
                  
                $paySlipRequestData->save();
                return response()->json(['success'=>'Salary Certificate Uploaded Successfully.']);
            }

            


            
        }
    }


    public function advancedPayRequestsListing(Request $request)
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
        
        if(!empty($request->session()->get('advancedPayRequest_page_limit')))
        {
            $paginationValue = $request->session()->get('advancedPayRequest_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	



        if(!empty($request->session()->get('advancedpay_requests_emp_name')) && $request->session()->get('advancedpay_requests_emp_name') != 'All')
        {
            $fname = $request->session()->get('advancedpay_requests_emp_name');
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


        if(!empty($request->session()->get('advancedpay_requests_emp_id')) && $request->session()->get('advancedpay_requests_emp_id') != 'All')
        {
            $empId = $request->session()->get('advancedpay_requests_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }

        if(!empty($request->session()->get('advancedpay_requests_dept')) && $request->session()->get('advancedpay_requests_dept') != 'All')
        {
            $deptid = $request->session()->get('advancedpay_requests_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('advancedpay_requests_designation')) && $request->session()->get('advancedpay_requests_designation') != 'All')
        {
            $desigid = $request->session()->get('advancedpay_requests_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('advancedpay_requests_tl')) && $request->session()->get('advancedpay_requests_tl') != 'All')
        {
            $tlid = $request->session()->get('advancedpay_requests_tl');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }

        
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        

        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
            // exit;

            if($empData==1) // all
            {
                $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')
                ->get()->count();
                
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();

                $empDetails = Employee_details::whereRaw($whereraw)->where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')
                ->get()->count();
            }
        }
        else
        {
            if($empData==1) // all
            {
                $requestDetails = AdvancedPayRequest::where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')						
                ->paginate($paginationValue);	
                
                $reportsCount = AdvancedPayRequest::where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')
                ->get()->count();
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
                
                $empDetails = Employee_details::where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }

                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')						
                ->paginate($paginationValue);
                
                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id', 'desc')
                ->get()->count();
            }
        }
        
        $requestDetails->setPath(config('app.url/listingAdvancedPayRequests'));
        return view("AdvancedPay/listingAdvancedPayRequests",compact('requestDetails','paginationValue','reportsCount'));
    }


    public function getAdvancedPayRequestContent(Request $request)
    {
        $empid=$request->empid;
        $rowid=$request->rowid;

        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();

        $requestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();

        return view("AdvancedPay/advancedPayRequestContent",compact('requestData','departmentLists','tL_details'));
    }

    public function advancedPayRequestSubmitApproved(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
			'approvedamt' => 'required|numeric|lte:requestedamount',
            'amtReleasedate' => 'required|date',
           // 'teamleaders' => 'required', 
        ],
		[
			'approvedamt.required'=> 'Please Fill Amount',
            'approvedamt.numeric'=> 'Amount must be a number.',
		 	'amtReleasedate.required'=> 'Please Select Amount Release Date',
			'approvedamt.lte'=> 'The Approved Amount must be less than or equal to Requested Amount',
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            //return $request->all();
            $usersessionId=$request->session()->get('EmployeeId');
            $advancedPayRequest = AdvancedPayRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

            
            //$advancedPayRequest->emp_id = $request->addRequestEmp;
            $advancedPayRequest->approved_advanced_amt = $request->approvedamt;
            $advancedPayRequest->approved_reject_at = date('Y-m-d H:i:s');
            $advancedPayRequest->amt_release_date = $request->amtReleasedate;
            $advancedPayRequest->approved_reject_by = $usersessionId; 
            $advancedPayRequest->approved_reject_status = 1;
            $advancedPayRequest->approved_reject_comments = $request->requestUpdatedComments;
            $advancedPayRequest->status = 2;
            $advancedPayRequest->save();

            $advancedPayLogs = new AdvancedPayLogs();
			$advancedPayLogs->emp_id = $request->empid;
            $advancedPayLogs->event_at = date('Y-m-d H:i:s');;
            $advancedPayLogs->event_by = $usersessionId;
            $advancedPayLogs->event = 2;
            $advancedPayLogs->approved_amt = $request->approvedamt;            
            $advancedPayLogs->save();


            return response()->json(['success'=>'Advanced Pay Request Approved.']);
        }
    }


    public function advancedPayRequestSubmitRejected(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
			'requestUpdatedComments' => 'required',
            //'addRequestEmp' => 'required',
           // 'teamleaders' => 'required', 
        ],
		[
			'requestUpdatedComments.required'=> 'Please Write Some Comments',
		 	//'addRequestEmp.required'=> 'Please Select Employee from List',
			//'teamleaders.required'=> 'Please Select Team Leader from List',
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            //return $request->all();
            $usersessionId=$request->session()->get('EmployeeId');
            $advancedPayRequest = AdvancedPayRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

            
            //$advancedPayRequest->emp_id = $request->addRequestEmp;
            $advancedPayRequest->approved_advanced_amt = $request->approvedamt;
            $advancedPayRequest->approved_reject_at = date('Y-m-d H:i:s');
            $advancedPayRequest->approved_reject_by = $usersessionId; 
            $advancedPayRequest->approved_reject_status = 2;
            $advancedPayRequest->approved_reject_comments = $request->requestUpdatedComments;
            $advancedPayRequest->status = 2;

            
            $advancedPayRequest->save();


            

            $advancedPayLogs = new AdvancedPayLogs();
			$advancedPayLogs->emp_id = $request->empid;
            $advancedPayLogs->event_at = date('Y-m-d H:i:s');;
            $advancedPayLogs->event_by = $usersessionId;
            $advancedPayLogs->event = 3;
            $advancedPayLogs->approved_amt = $request->approvedamt;            
            $advancedPayLogs->save();


            return response()->json(['success'=>'Advanced Pay Request Rejected.']);
        }
    }


    public function advancedPayFinalListing(Request $request)
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
        
        if(!empty($request->session()->get('advancedPayRequest_page_limit')))
        {
            $paginationValue = $request->session()->get('advancedPayRequest_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }	



        if(!empty($request->session()->get('advancedpay_requests_emp_name')) && $request->session()->get('advancedpay_requests_emp_name') != 'All')
        {
            $fname = $request->session()->get('advancedpay_requests_emp_name');
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


        if(!empty($request->session()->get('advancedpay_requests_emp_id')) && $request->session()->get('advancedpay_requests_emp_id') != 'All')
        {
            $empId = $request->session()->get('advancedpay_requests_emp_id');
                if($whereraw == '')
            {
                $whereraw = 'emp_id IN ('.$empId.')';
            }
            else
            {
                $whereraw .= ' And emp_id IN ('.$empId.')';
            }
        }

        if(!empty($request->session()->get('advancedpay_requests_dept')) && $request->session()->get('advancedpay_requests_dept') != 'All')
        {
            $deptid = $request->session()->get('advancedpay_requests_dept');
                if($whereraw == '')
            {
                $whereraw = 'dept_id IN ('.$deptid.')';
            }
            else
            {
                $whereraw .= ' And dept_id IN ('.$deptid.')';
            }
        }


        if(!empty($request->session()->get('advancedpay_requests_designation')) && $request->session()->get('advancedpay_requests_designation') != 'All')
        {
            $desigid = $request->session()->get('advancedpay_requests_designation');
                if($whereraw == '')
            {
                $whereraw = 'designation_by_doc_collection  IN ('.$desigid.')';
            }
            else
            {
                $whereraw .= ' And designation_by_doc_collection  IN ('.$desigid.')';
            }
        }

        if(!empty($request->session()->get('advancedpay_requests_tl')) && $request->session()->get('advancedpay_requests_tl') != 'All')
        {
            $tlid = $request->session()->get('advancedpay_requests_tl');
                if($whereraw == '')
            {
                $whereraw = 'tl_id  IN ('.$tlid.')';
            }
            else
            {
                $whereraw .= ' And tl_id  IN ('.$tlid.')';
            }
        }

        
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empData = $this->getLoggedinUser($loggedinUserid);

        if($whereraw != '')
		{
            // echo "<pre>";
            // print_r($whereraw);
            // exit;

            if($empData==1) // all
            {
                $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }

                // new code
                $requestPayDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->groupBy('emp_id')
                ->get();
                
                $newResultLast=array();
                foreach($requestPayDetails as $value)
                {
                    $newResultLast[]=$value->id;
                }
                
                $requestDetails = AdvancedPayRequest::whereIn('id',$newResultLast)->orderBy('id', 'desc')						
                ->paginate($paginationValue);	
                
                $reportsCount = AdvancedPayRequest::whereIn('id',$newResultLast)->orderBy('id', 'desc')
                ->get()->count();
                // new code
                
                // $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')					
                // ->paginate($paginationValue);

                // $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')
                // ->get()->count();
                
            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();

                $empDetails = Employee_details::whereRaw($whereraw)->where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }


                 // new code
                 $requestPayDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->groupBy('emp_id')
                 ->get();
                 
                 $newResultLast=array();
                 foreach($requestPayDetails as $value)
                 {
                     $newResultLast[]=$value->id;
                 }
                 
                 $requestDetails = AdvancedPayRequest::whereIn('id',$newResultLast)->orderBy('id', 'desc')						
                 ->paginate($paginationValue);	
                 
                 $reportsCount = AdvancedPayRequest::whereIn('id',$newResultLast)->orderBy('id', 'desc')
                 ->get()->count();
                 // new code
                
                // $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')					
                // ->paginate($paginationValue);
                // $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->orderBy('id', 'desc')
                // ->get()->count();
            }
        }
        else
        {
            if($empData==1) // all
            {
                $requestPayDetails = AdvancedPayRequest::where('request_status',1)->where('approved_reject_status',1)->groupBy('emp_id')
                ->get();
                
                $newResult=array();
                foreach($requestPayDetails as $value)
                {
                    $newResult[]=$value->id;
                }
                
                $requestDetails = AdvancedPayRequest::whereIn('id',$newResult)->orderBy('id', 'desc')						
                ->paginate($paginationValue);	
                
                $reportsCount = AdvancedPayRequest::whereIn('id',$newResult)->orderBy('id', 'desc')
                ->get()->count();

            }
            else // specific dept
            {
                $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();
                
                $empDetails = Employee_details::where('dept_id',$empDetails->dept_id)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }

                //new code start
                
                $requestPayDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->where('request_status',1)->where('approved_reject_status',1)->groupBy('emp_id')
                ->get();
                
                $newResultFinal=array();
                foreach($requestPayDetails as $value)
                {
                    $newResultFinal[]=$value->id;
                }
                
                
                $requestDetails = AdvancedPayRequest::whereIn('id',$newResultFinal)->orderBy('id', 'desc')						
                ->paginate($paginationValue);

                $reportsCount = AdvancedPayRequest::whereIn('id',$newResultFinal)->orderBy('id', 'desc')
                ->get()->count();
                // new code end

            }
        }
        
        $requestDetails->setPath(config('app.url/listingAdvancedPayFinalRequests'));
        return view("AdvancedPay/listingAdvancedPayFinalRequests",compact('requestDetails','paginationValue','reportsCount'));
    }

    public function searchPaySlipRequestFilter(Request $request)
	{
			$department='';
			if($request->input('paySlipdepartment')!=''){
			 
			 $department=implode(",", $request->input('paySlipdepartment'));
			}

            $newDepartment='';
			if($request->input('newDepartment')!=''){
			 
			 $newDepartment=implode(",", $request->input('newDepartment'));
			}


			$teamlaed='';
			if($request->input('paySlipteamlaed')!=''){
			 
			 $teamlaed=implode(",", $request->input('paySlipteamlaed'));
			}
			$dateto = $request->input('dateto');
			$datefrom = $request->input('datefrom');
			$name='';
			if($request->input('paySlipemp_name')!=''){
			 
			 $name=implode(",", $request->input('paySlipemp_name'));
			}
			//$name = $request->input('emp_name');
			$empId='';
			if($request->input('paySlipempId')!=''){
			 
			 $empId=implode(",", $request->input('paySlipempId'));
			}
			$payMonth='';
			if($request->input('datefrompayMonth')!=''){
			 
			 $payMonth=$request->input('datefrompayMonth');

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

			$request->session()->put('paySlip_requests_emp_name',$name);
            $request->session()->put('paySlip_requests_emp_id',$empId);
            $request->session()->put('paySlip_requests_dept',$department);
            $request->session()->put('transfer_requests_new_dept',$newDepartment);
            $request->session()->put('paySlip_month_filter',$payMonth);
            $request->session()->put('paySlip_requests_tl',$teamlaed);




            $request->session()->put('emp_leaves_fromdate',$datefrom);
            $request->session()->put('emp_leaves_todate',$dateto);


			$request->session()->put('range_filter_inner_list',$rangeid);
			$request->session()->put('empid_emp_offboard_filter_inner_list',$empId);
			
			//$request->session()->put('departmentId_filter_inner_list',$department);
			
			
			
			$request->session()->put('dateto_offboard_lastworkingday_list',$datetolastworkingday);
			$request->session()->put('datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			
			$request->session()->put('ReasonofAttrition_empoffboard_filter_list',$ReasonofAttrition);
			$request->session()->put('empoffboard_status_filter_list',$offboardstatus);
			$request->session()->put('datefrom_offboard_dort_list',$datefromdort);
			$request->session()->put('dateto_offboard_dort_list',$datetodort);
			$request->session()->put('empoffboard_ffstatus_filter_list',$offboardffstatus);
			 //return  redirect('listingPageonboarding');	
	}

    public function resetPaySlipRequestFilter(Request $request)
    {
        $request->session()->put('paySlip_requests_emp_name','');
        $request->session()->put('paySlip_requests_emp_id','');
        $request->session()->put('paySlip_requests_dept','');
        $request->session()->put('transfer_requests_new_dept','');
        $request->session()->put('paySlip_month_filter','');
        $request->session()->put('paySlip_requests_tl','');



        


        $request->session()->put('emp_leaves_fromdate','');
		$request->session()->put('emp_leaves_todate','');
        
        
    }


    public static function getDepartment($dept_id)
    {
            
        $deptInfo = Department::where('id',$dept_id)->orderBy('id', 'desc')->first();
        if($deptInfo)
        {
            return $deptInfo->department_name;
        }
        else
        {
            return '--';
        }
            
    }


    public static function getRequestStatus($empid,$rowid)
    {
        $requestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();

        if($requestData)
        {
            if($requestData->approved_reject_status==1)
            {
                return "Request Approved";
            }
            if($requestData->approved_reject_status==2)
            {
                return "Request Rejected";
            }
            if($requestData->request_status==1 && $requestData->approved_reject_status==NULL)
            {
                return "Pending for Approval/Reject";
            }
        }
        else
        {
            return '--';
        }
    }

    public function summaryTabWithFullViewAjax(Request $request)
	{
		    $empid = $request->empid;
			$rowid = $request->rowid;

            $advancedPayRequestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();

			$completedStep = 0;
			$OnboardingProgress = '';
			$stepsAll = array();
			/*Step1*/
		    



            $stepsAll[0]['name'] = 'Approved/Reject'; 
            if($advancedPayRequestData->request_status == 1  && $advancedPayRequestData->approved_reject_status == 0)
            {
                $stepsAll[0]['stage'] = 'inprogress';
                $stepsAll[0]['Tab'] = 'active'; 
            }
            else
            {
                $completedStep++;
                $stepsAll[0]['stage'] = 'active'; 
                $OnboardingProgress = 'Approved/Reject';
                $stepsAll[0]['Tab'] = 'active'; 
            }
            $stepsAll[0]['slagURL'] = 'tab2'; 
            //$stepsAll[0]['tab'] = 'active'; 
            $stepsAll[0]['onclick'] = 'tab2Panel();';            
            $OnboardingProgress = 'Approved/Reject';
            /*Step1*/	



            $stepsAll[1]['name'] = 'Final Summary'; 
            
            if($advancedPayRequestData->request_status == 1  && $advancedPayRequestData->approved_reject_status == 1 && $advancedPayRequestData->status==2)
            {
                $completedStep++;
                $stepsAll[1]['stage'] = 'active'; 
                $OnboardingProgress = 'Final Summary';
                $stepsAll[1]['Tab'] = 'active'; 
            }
            else 
            {
                //$completedStep++;
                $OnboardingProgress = 'Final Summary';
                $stepsAll[1]['stage'] = 'pending'; 
                $stepsAll[1]['Tab'] = 'disabled-tab';  

            }
            $stepsAll[1]['slagURL'] = 'tab2'; 
            $stepsAll[1]['onclick'] = 'tab2Panel();'; 
            $OnboardingProgress = 'Final Summary';


		    
			
			$totalStep = 2;
			$p = $completedStep/$totalStep;
			$percentange = round($p*100);

			//return $percentange;
			
			
			return view("AdvancedPay/summaryTabWithFullViewAjax",compact('advancedPayRequestData','stepsAll','percentange','OnboardingProgress'));
	}

    public function approvedSummaryTabData(Request $request)
    {
        $empid = $request->empid;
        $rowid = $request->rowid;

        $advancedPayRequestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();
        return view("AdvancedPay/secondTabRequestInfo",compact('advancedPayRequestData'));             
    }

    public function finalSummaryTabData(Request $request)
    {
        $empid = $request->empid;
        $rowid = $request->rowid;

        $advancedPayRequestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();
        return view("AdvancedPay/thirdTabRequestinfo",compact('advancedPayRequestData'));             
    }

    public function recoveryRequestData(Request $request)
    {
         $empid = $request->empid;
         $rowid = $request->rowid;

         $advancedPayRequestData = AdvancedPayRequest::where('emp_id',$empid)->where('id',$rowid)->orderBy('id', 'desc')->first();
         return view("AdvancedPay/recoveryRequestContent",compact('advancedPayRequestData')); 
    }



    public function recoveryRequestUpdatePostSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
            'recoveryamt' => 'required|numeric|lte:totalRemaining',
            'amtRecoverydate' => 'required|date',
             //'teamleaders' => 'required', 
        ],
        [
            'recoveryamt.required'=> 'Please Fill Amount',
            'recoveryamt.numeric'=> 'Amount must be in Number',
            'amtRecoverydate.required'=> 'Please Select recovery date',
            'recoveryamt.lte'=> 'The Recovery Amount must be less than or equal to Total Remaining Amount',
                
        ]);

        if(($validator->fails()))
        {
            return response()->json(['error'=>$validator->errors()]);
        }
        else
        {
            //return $request->all();
            $usersessionId=$request->session()->get('EmployeeId');
            //$advancedPayRequest = AdvancedPayRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

            
            $recoveryAmtRequest = new RecoveryAmt();
            $recoveryAmtRequest->emp_id = $request->empid;
            $recoveryAmtRequest->recovery_amt = $request->recoveryamt;
            $recoveryAmtRequest->recovery_at = $request->amtRecoverydate;
            $recoveryAmtRequest->recovery_by = $usersessionId; 
            $recoveryAmtRequest->recovery_comment = $request->recoveryComments;               
            $recoveryAmtRequest->save();


         $advancedPayLogs = new AdvancedPayLogs();
         $advancedPayLogs->emp_id = $request->empid;
         $advancedPayLogs->event_at = date('Y-m-d H:i:s');;
         $advancedPayLogs->event_by = $usersessionId;
         $advancedPayLogs->event = 4;
         $advancedPayLogs->recovered_amt = $request->recoveryamt;            
         $advancedPayLogs->save();


            return response()->json(['success'=>'Recovered Successfully.']);
        }
    }

    
    public static function getSumRequestedAmt($empid)
    {
         $requestedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('requested_advanced_amt');

         if($requestedAmt)
         {
             return $requestedAmt;
         }
         else
         {
             return 0;
         }
    }

    public static function getSumApprovedAmt($empid)
    {
         $approvedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('approved_advanced_amt');

         if($approvedAmt)
         {
             return $approvedAmt;
         }
         else
         {
             return 0;
         }
    }

    public static function getSumRecoveredAmt($empid)
    {
         $recoveredAmt = RecoveryAmt::where('emp_id',$empid)->sum('recovery_amt');

         if($recoveredAmt)
         {
             return $recoveredAmt;
         }
         else
         {
             return 0;
         }
    }


    public static function getBalancedAmt($empid)
    {
        $requestedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('requested_advanced_amt');
        $approvedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('approved_advanced_amt');
        $recoveredAmt = RecoveryAmt::where('emp_id',$empid)->sum('recovery_amt');
        $balancedAmt = $approvedAmt - $recoveredAmt;
        return $balancedAmt;
    }

       
    public function advancedPayLogsTabData(Request $request)
    {
        $empid = $request->empid;
        $rowid = $request->rowid;
        //return 'Emp_id: '.$empid.' Row_id: '.$rowid;
        $advancedPayRequestData = AdvancedPayLogs::where('emp_id',$empid)->orderBy('id','desc')->get();
        return view("AdvancedPay/advancedPayLogsDetails",compact('advancedPayRequestData')); 
        
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

    public static function getTL($tl_id)
    {
        $tL_details = Employee_details::where("id",$tl_id)->orderBy("id","ASC")->first();

        if($tL_details)
        {
            return $tL_details->emp_name;
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
		$request->session()->put('paySlipsRequest_page_limit',$offset);
	}
    public static function getlocalMobileNo($empid,$attributecode)
    {
        $attrval = Employee_attribute::where('emp_id',$empid)->where("attribute_code",$attributecode)->first();
        if($attrval!=''){
            $data=substr ($attrval->attribute_values, -9);
            $finaldata="+971 ". $data;
            return $finaldata;
        }
        else{
            return "";
        }
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


    public function downloadFile(Request $request)
    {
            $file =  $request->filename;
            $empid =  $request->empid;

            $extension = pathinfo($file, PATHINFO_EXTENSION);			   

            
            $fileName = public_path("/paySlips");
            $newf = $fileName."/".$file;


            if($extension=='pdf')
            {
             $headers = ['Content-Type: application/pdf'];
             //$newName = 'transferLetter-'.time().'.pdf';
             $newName = $empid.'_SalaryCertificate_'.date("Y-m-d_h-i-s").'.pdf'; 
            }
            if($extension=='doc')
            {
             $headers = ['Content-Type: application/pdf'];
             $newName = 'transferLetter-'.time().'.doc';
            }
            if($extension=='txt')
            {
             $headers = ['Content-Type: text/plain'];
             $newName = 'transferLetter-'.time().'.txt';
            }			   

             if($extension=='docx')
             {

             $headers = ['Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
             $newName = 'transferLetter-'.time().'.docx';
            }

         if($extension=='csv')
         {
             $headers = ['Content-Type: text/csv'];
             $newName = 'transferLetter-'.time().'.csv';
         }

         if($extension=='jpeg')
         {
             $headers = ['Content-Type: image/jpeg'];
             $newName = 'transferLetter-'.time().'.jpeg';
         }

         if($extension=='jpg')
         {
             $headers = ['Content-Type: image/jpg'];
             $newName = 'transferLetter-'.time().'.jpg';
         }

         if($extension=='png')
         {
             $headers = ['Content-Type: image/png'];
             $newName = 'transferLetter-'.time().'.png';
         }
    
            //return $newf;

          
            return response()->download($newf, $newName, $headers);
    }

    public static function getPaySlip($empid,$payMonth)
    {
       // return $payMonth;
        $data = SalaryCertificate::where('emp_id',$empid)->where('pay_slip_month',$payMonth)->orderBy("id","DESC")->first();
        //print_r($data);
        if($data != '')
        {
            return $data->pay_slip_file;
        }
        else
        {
            return '';
        }
    }

    public function addrequestPopData(Request $request)
    {
        $loggedinUserid=$request->session()->get('EmployeeId');
        $empRequested = AdvancedPayRequest::select('emp_id')->where('request_status',1)->whereNull('approved_reject_status')->orderBy('id','desc')->get();

        $departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
        if($departmentDetails != '')
        {
            $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
            if($empDetails!='')
            {
				$departmentDetails = JobFunctionPermission::where("user_id",$loggedinUserid)->first();
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                $empData_details = Employee_details::where('employee_details.dept_id',$empDetails->dept_id)->orderBy('id','desc')->get();

                $empDetails = Employee_details::where('dept_id',$empDetails->dept_id)->where('offline_status',1)->whereNotIn('emp_id', $empRequested)->orderBy('id', 'desc')
                ->get();
            }
        }
        else
        {
			//$empDetails = Employee_details::where('offline_status',1)->whereNotIn('emp_id', $empRequested)->orderBy('id', 'desc')->get();	
            
            

            
				$tdate = date('Y-m-d');
				

				$empData_details = Employee_details::orderBy('id','desc')->get();
				$lastworkingdate='';
				$offmarkEmp=array();
				foreach($empData_details as $emp)
				{
					if($emp->offline_status==2)
					{
						$offlineEmpData = EmpOffline::where('emp_id',$emp->emp_id)->orderBy('id','desc')->first();						
						if($offlineEmpData)
						{
							if($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_resign;
							}
							elseif($offlineEmpData->last_working_day_resign!=''|| $offlineEmpData->last_working_day_resign!=NULL)
							{
								$lastworkingdate = $offlineEmpData->last_working_day_terminate;
							}
							else
							{
								$new_date = date("Y-m-d",strtotime($offlineEmpData->created_at));
								$lastworkingdate = $new_date;
							}							
						}

						if($lastworkingdate)
						{
							
								if($lastworkingdate < $tdate)
								{
									$offmarkEmp[]=$emp->emp_id;
								}
								
							

						}
					}
					else
					{
						
							if($emp->doj > $tdate)
							{
								$offmarkEmp[]=$emp->emp_id;									
							}
							
						
					}
				}

				$empDetails = Employee_details::whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
				->get();

				//$reportsCount = Employee_details::select('emp_id','dept_id','tl_id','emp_name')->whereNotIn('emp_id', $offmarkEmp)->orderBy('id','desc')
               // ->get()->count();









        }
        
        //$empDetails = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();

        return view("SalaryCertificate/addRequest",compact('empDetails'));
    }


    public function addPaySlipRequestPostSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
			//'file_upload' => 'required|mimes:csv,txt,pdf,jpeg,png,jpg',
            'file_paySlipAdd' => 'required|mimes:pdf',
            'datefrompayMonth' => 'required',
            //'editdesignation' => 'required',
           // 'teamleaders' => 'required', 
        ],
		[
			//'customFile' => '',
            'file_paySlipAdd.required'=> 'Please Select file to upload',
		 	'datefrompayMonth.required'=> 'Please Select Month',
			//'teamleaders.required'=> 'Please Select Team Leader from List',
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            //return $request->all();

            
            $file = $request->file('file_paySlipAdd');
            $filename = $request->empid.'_SalaryCertificate_'.date("Y-m-d_h-i-s").'.pdf';  
            //$filename = time().'_'.$file->getClientOriginalName();

            if(file_exists(public_path('paySlips/'.$filename)))
            {
                unlink(public_path('paySlips/'.$filename));
            }
            
            // File extension
            $extension = $file->getClientOriginalExtension();

            // File upload location
            $location = 'paySlips';

            // Upload file
            $file->move(public_path('paySlips/'), $filename);

            // File path
            $filepath = url('paySlips/'.$filename);


            $usersessionId=$request->session()->get('EmployeeId');
            $requestPayMonth = $request->datefrompayMonth;
            //exit;
            $empDetails = Employee_details::where('emp_id',$request->empid)->where('offline_status',1)->orderBy('id', 'desc')->first();
           
            $paySlipRequestData = SalaryCertificate::where('emp_id',$request->empid)->where('pay_slip_month',$requestPayMonth)->orderBy('id', 'desc')->first();

            //dd($paySlipRequestData);

            if($paySlipRequestData)
            {
                // echo "Hello";
                // exit;
                // $paySlipRequestData->emp_id = $request->empid;
                // $paySlipRequestData->dept_id = $empDetails->dept_id;
                // $paySlipRequestData->tl_id = $empDetails->tl_id;
                // $paySlipRequestData->emp_name = $empDetails->emp_name;
                // if($filename!='')
                // {
                //     $paySlipRequestData->pay_slip_file = $filename;
                // }	
    
                // $paySlipRequestData->pay_slip_month = $request->datefrompayMonth;
                // //$paySlipRequestData->created_at = date('Y-m-d H:i:s'); 
                // $paySlipRequestData->created_by = $usersessionId;    
                // $paySlipRequestData->save();

                

                return response()->json(['exist'=>1]);
            }
            else
            {
                // echo "Update";
                // exit;
                $paySlipRequestData = new SalaryCertificate();
                $paySlipRequestData->emp_id = $request->empid;
                $paySlipRequestData->dept_id = $empDetails->dept_id;
                $paySlipRequestData->tl_id = $empDetails->tl_id;
                $paySlipRequestData->emp_name = $empDetails->emp_name;
                if($filename!='')
                {
                    $paySlipRequestData->pay_slip_file = $filename;
                }	
    
                $paySlipRequestData->pay_slip_month = $request->datefrompayMonth;
                $paySlipRequestData->created_at = date('Y-m-d H:i:s'); 
                $paySlipRequestData->created_by = $usersessionId;    
                $paySlipRequestData->comments = $request->requestComments;

                $paySlipRequestData->save();

                return response()->json(['success'=>'Salary Certificate Uploaded Successfully.']);
            }

            


            
        }
    }

}