<?php

namespace App\Http\Controllers\Passport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use App\Models\Company\Department;
use App\Models\Company\Product;
use App\Models\Recruiter\Designation;
use App\Models\Offerletter\SalaryBreakup;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionAttributes;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use App\Models\Onboarding\KycDocuments;
use App\Models\Onboarding\HiringSourceDetails;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Onboarding\VisaDetails;
use App\Models\Onboarding\IncentiveLetterDetails;
use Illuminate\Support\Facades\Validator;
use  App\Models\Attribute\AttributeType;
use App\Models\Offerletter\OfferletterDetails;
use App\Models\Visa\visaType;
use App\Models\Visa\VisaStage;
use App\Models\Visa\Visaprocess;
use App\Models\Onboarding\TrainingProcess;
use UserPermissionAuth;
use App\Models\Entry\Employee;
use App\Models\Employee\Employee_details;
use App\Models\Job\JobOpening;
use App\Models\Employee\Employee_attribute;
use  App\Models\Attribute\Attributes;
use App\Models\EmpOffline\EmpOffline;
use App\Models\EmpOffline\QuestionForLeaving;
use App\Models\Question\Question;
use App\Models\SettelementAttribute\SettelementAttribute;
use App\Models\CompanyAssets\CompanyAssets;
use App\Models\SettelementCheckList\SettelementCheckList;
use App\Models\EmpOffline\SettelementAttributes;
use App\Models\ReasonsForLeaving\ReasonsForLeaving;
use App\Models\EmpOffline\OffboardEMPData;
use App\Models\EmpOffline\SettelementLogs;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\EmpOffline\CancelationVisaProcess;
use App\Models\Employee\SalaryRequest;
use App\Models\Employee\ChangeSalary;
use App\Models\EmpProcess\JobFunctionPermission;
use App\Models\Changesalary\Employee_details_change_salary;
use App\Models\WarningLetter\WarningLetterRequest;
use App\Models\WarningLetter\WarningLetterReasons;
use Illuminate\Support\Facades\DB;
use App\Services\LoggerFactory;
use App\Models\Passport\Passport;
use App\Models\Passport\PassportHistory;

use App\Models\Employee\ExportDataLog;

class PassportController extends Controller
{
    public function __construct(LoggerFactory $logFactory)
    {
        $this->log = $logFactory->setPath('logs/passport')->createLogger('passport'); 
    }

    public static function getEmployeeName($empid)
	{
		$empDetails = Employee_details::where('emp_id',$empid)->orderBy('id','desc')->first();
		if(!$empDetails)
		{
			return '--';
		}
		return $empDetails->emp_name;		
	}
	public static function getTeamLeader($empid)
	{
		$empDetails = Employee_details::where("emp_id",$empid)->orderBy('id','desc')->first(); 
		if($empDetails)
		{
			$emp_details = Employee_details::where("id",$empDetails->tl_id)->first(); 
			if($emp_details!='')
			{
				return $emp_details->emp_name;
			}
			else
			{
				return "--";
			}
		}
		else
		{
			return '--';
		}	
	}
    public static function getDesignation($empid)
	{
		$empDetails = Employee_details::where("emp_id",$empid)->orderBy('id','desc')->first(); 
		//return $empDetails;

		if($empDetails)
		{
			$designationDetails = Designation::where("id",$empDetails->designation_by_doc_collection)->first();
			if($designationDetails != '')
			{
				return $designationDetails->name;
			}
			else{
				 return '--'; 
			}
		}
		else{
			return '--';
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

    public static function getVintage($empid)
    {
        $empDetails = Employee_details::where('emp_id',$empid)->orderBy('id','desc')->first();
        if(!$empDetails)
        {
            return '--';
        }
        return $empDetails->vintage_days;
    }

    public static function getVisaStatus($empid)
	{	
		$empDetails = Employee_details::where("emp_id",$empid)->orderBy('id','desc')->first();

		if($empDetails)
		{
			if($empDetails->document_collection_id != NULL)
			{
				$visaDetails = DocumentCollectionDetails::where("id",$empDetails->document_collection_id)->orderBy('id','desc')->first();

				if($visaDetails)
				{
					if($visaDetails->visa_process_status==4)
					{
						return "Visa Complete";
					}
					elseif($visaDetails->visa_process_status==2)
					{
						return "Visa In-Progress -  ".$visaDetails->visa_stage_steps;
					}
					else
					{
						return "Visa in-Complete";
					}

				}
				else
				{
					return "N/A";
				}

			}
			else
			{
				//return "N/A";
				return "Visa Complete";
			}

		}
		else
		{
			return "N/A";
		}
	}

	public static function getVisaStages($empid)
	{	
		$empDetails = Employee_details::where("emp_id",$empid)->orderBy('id','desc')->first();

		if($empDetails)
		{
			if($empDetails->document_collection_id != NULL)
			{
				$visaDetails = DocumentCollectionDetails::where("id",$empDetails->document_collection_id)->orderBy('id','desc')->first();

				if($visaDetails)
				{
					if($visaDetails->visa_process_status==4)
					{
						return "N/A";
					}
					elseif($visaDetails->visa_process_status==2)
					{
						//return "Visa in-Progress";

						$visaprocessDetails = Visaprocess::where("document_id",$empDetails->document_collection_id)->orderBy('id','desc')->first();

						if($visaprocessDetails)
						{
							$visastageDetails = VisaStage::where("id",$visaprocessDetails->visa_stage)->orderBy('id','desc')->first();

							if($visastageDetails)
							{
								
								
								$visaTypeDetails = visaType::where("id",$visastageDetails->visa_type)->orderBy('id','desc')->first();

								return $visastageDetails->stage_name. ' - ' .$visaTypeDetails->title;
							}
							else{
								return "N/A";
							}

						}
						else
						{
							return "N/A";
						}





					}
					else
					{
						return "N/A";
					}

				}
				else
				{
					return "N/A";
				}

			}
			else
			{
				//return "N/A";
				return "Stamp";
			}

		}
		else
		{
			return "N/A";
		}
	}

	

	public static function getUserName($userid)
	{	
		$attributecode = 'work_location';
		$userDetails = User::where('id',$userid)->first();
		if($userDetails != '')
		{
			return $userDetails->fullname;
		}
		else
		{
			return '--';
		}
	}


	public static function getVisaTypeName($visaTypeId)
	{
		$visaTypeDetails = visaType::where("id",$visaTypeId)->orderBy('id','desc')->first();

		if($visaTypeDetails)
		{
			return $visaTypeDetails->title;
		}
		else
		{
			return "--";
		}
		

	}

    public function index(Request $request)
	{		
		$empsessionId=$request->session()->get('EmployeeId');
		if($empsessionId==72)
		{
			$releaseTabName = "Approval Released Queue";
			$releaseTabShow=1;
		}
		else
		{
			$releaseTabName = "Released Queue";
			$releaseTabShow=2;
		}  
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();
        $empId = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->get();
        $Designation=Designation::where("status",1)->get();

		return view("Passport/PassportIndex",compact('departmentLists','tL_details','empId','Designation','releaseTabName','releaseTabShow'));
	}

    public function listingAllPassportsData(Request $request) // All Passports List
	{
		//return "Hello";
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
		
        //$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';
        
        
    
        if(!empty($request->session()->get('passport_page_limit')))
        {
            $paginationValue = $request->session()->get('passport_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }
			
        if($whereraw != '')
        {
            // echo $whereraw;
            // exit;
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.updated_at','desc')
                //->toSql();
                //dd($passportDetails);
                
                ->paginate($paginationValue);

                $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.id','desc')
                ->get()->count();	
                }
            }
            else
            {
                $passportDetails = Passport::whereRaw($whereraw)->orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }

        }
        else
        {
            
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('passport.updated_at','desc')
                    ->paginate($paginationValue);

                    $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->where('employee_details.dept_id',$empDetails->dept_id)->orderBy('passport.id','desc')
                    ->get()->count();
                }
            }
            else
            {
                $passportDetails = Passport::orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::orderBy('id','desc')->get()->count();
            }


        }
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
        $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
        $passportDetails->setPath(config('app.url/listingAllPassports'));
        //print_r($documentCollectiondetails);exit;
        $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();

		return view("Passport/listingAllPassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	}

    public function listingRequestedPassportsTabData(Request $request) // Not Available Passports List
	{
		//return "Hello";
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
		
        //$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';
        
        
    
        if(!empty($request->session()->get('passport_page_limit')))
        {
            $paginationValue = $request->session()->get('passport_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }
			
        if($whereraw != '')
        {
            // echo $whereraw;
            // exit;
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.updated_at','desc')
                //->toSql();
                //dd($passportDetails);
                
                ->paginate($paginationValue);

                $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.id','desc')
                ->get()->count();	
                }
            }
            else
            {
                $passportDetails = Passport::whereRaw($whereraw)->orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }

        }
        else
        {
            
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('passport.updated_at','desc')
                    ->paginate($paginationValue);

                    $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->where('employee_details.dept_id',$empDetails->dept_id)->orderBy('passport.id','desc')
                    ->get()->count();
                }
            }
            else
            {
                $passportDetails = Passport::where('passport_status',0)->whereNull('collection_queue_status')->orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::where('passport_status',0)->whereNull('collection_queue_status')->orderBy('id','desc')->get()->count();
            }


        }
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
        $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
        $passportDetails->setPath(config('app.url/listingRequestedPassports'));
        //print_r($documentCollectiondetails);exit;
        $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();

		return view("Passport/listingRequestedPassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	}

    public function listingCollectionQueueData(Request $request) // Collection Queue List
	{
		//return "Hello";
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
		
        //$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';
        
        
    
        if(!empty($request->session()->get('passport_page_limit')))
        {
            $paginationValue = $request->session()->get('passport_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }
			
        if($whereraw != '')
        {
            // echo $whereraw;
            // exit;
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.updated_at','desc')
                //->toSql();
                //dd($passportDetails);
                
                ->paginate($paginationValue);

                $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.id','desc')
                ->get()->count();	
                }
            }
            else
            {
                $passportDetails = Passport::whereRaw($whereraw)->orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }

        }
        else
        {
            
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('passport.updated_at','desc')
                    ->paginate($paginationValue);

                    $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->where('employee_details.dept_id',$empDetails->dept_id)->orderBy('passport.id','desc')
                    ->get()->count();
                }
            }
            else
            {
                $passportDetails = Passport::where('passport_status',0)->where('collection_queue_status',1)->orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::where('passport_status',0)->where('collection_queue_status',1)->orderBy('id','desc')->get()->count();
            }


        }
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
        $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
        $passportDetails->setPath(config('app.url/listingCollectionQueue'));
        //print_r($documentCollectiondetails);exit;
        $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();

		return view("Passport/listingCollectionQueue",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	}

    public function listingAvailablePassportsData(Request $request) // Available Passports List
	{
		//return "Hello";
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
		
        //$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';
        
        
    
        if(!empty($request->session()->get('passport_page_limit')))
        {
            $paginationValue = $request->session()->get('passport_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }
			
        if($whereraw != '')
        {
            // echo $whereraw;
            // exit;
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.updated_at','desc')
                //->toSql();
                //dd($passportDetails);
                
                ->paginate($paginationValue);

                $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.id','desc')
                ->get()->count();	
                }
            }
            else
            {
                $passportDetails = Passport::whereRaw($whereraw)->orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }

        }
        else
        {
            
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('passport.updated_at','desc')
                    ->paginate($paginationValue);

                    $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->where('employee_details.dept_id',$empDetails->dept_id)->orderBy('passport.id','desc')
                    ->get()->count();
                }
            }
            else
            {
                $passportDetails = Passport::where('passport_status',1)->whereNull('release_request_generate')->orWhere('request_status',2)->orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::where('passport_status',1)->whereNull('release_request_generate')->orWhere('request_status',2)->orderBy('id','desc')->get()->count();
            }


        }
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
        $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();

        $passportDetails->setPath(config('app.url/listingAvailablePassports'));
        //print_r($documentCollectiondetails);exit;
        $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();

		return view("Passport/listingAvailablePassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	}

    public function listingApprovalReleasedQueueData(Request $request) // Requested Passports for Approval or Rejected List
	{
		//return "Hello";
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
		
        //$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';
        
        
    
        if(!empty($request->session()->get('passport_page_limit')))
        {
            $paginationValue = $request->session()->get('passport_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }
			
        if($whereraw != '')
        {
            // echo $whereraw;
            // exit;
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.updated_at','desc')
                //->toSql();
                //dd($passportDetails);
                
                ->paginate($paginationValue);

                $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.id','desc')
                ->get()->count();	
                }
            }
            else
            {
                $passportDetails = Passport::whereRaw($whereraw)->orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }

        }
        else
        {
            
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('passport.updated_at','desc')
                    ->paginate($paginationValue);

                    $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->where('employee_details.dept_id',$empDetails->dept_id)->orderBy('passport.id','desc')
                    ->get()->count();
                }
            }
            else
            {
                $passportDetails = Passport::where('passport_status',1)->where('release_request_generate',1)->whereNull('request_status')->orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::where('passport_status',1)->where('release_request_generate',1)->whereNull('request_status')->orderBy('id','desc')->get()->count();
            }


        }
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
        $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
        
        $passportDetails->setPath(config('app.url/listingApprovalPassports'));
        //print_r($documentCollectiondetails);exit;
        $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();

		return view("Passport/listingApprovalPassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	}




    public function requestedPassportForApprovalTabData(Request $request) // Requested Passports for Approval or Rejected List woth all for sam
	{
		//return "Hello";
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
		
        //$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';
        
        
    
        if(!empty($request->session()->get('passport_page_limit')))
        {
            $paginationValue = $request->session()->get('passport_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }
			
        if($whereraw != '')
        {
            // echo $whereraw;
            // exit;
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.updated_at','desc')
                //->toSql();
                //dd($passportDetails);
                
                ->paginate($paginationValue);

                $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.id','desc')
                ->get()->count();	
                }
            }
            else
            {
                $passportDetails = Passport::whereRaw($whereraw)->orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }

        }
        else
        {
            
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('passport.updated_at','desc')
                    ->paginate($paginationValue);

                    $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->where('employee_details.dept_id',$empDetails->dept_id)->orderBy('passport.id','desc')
                    ->get()->count();
                }
            }
            else
            {
                // $passportDetails = Passport::join('passport_release_request_history', 'passport_release_request_history.emp_id', '=', 'passport.emp_id')->where('passport_release_request_history.request_type',7)->orWhere('passport_release_request_history.request_type',8)->orderBy('passport_release_request_history.created_at','desc')
                // ->paginate($paginationValue);
                // // ->toSql();
                // dd($passportDetails);



                $val1=7;
                $val2=3;
                $val3=1;

                $newd = Passport::join('passport_release_request_history', 'passport_release_request_history.emp_id', '=', 'passport.emp_id')
                ->where(function ($q) use($val1, $val2,$val3) {
                    $q->where('passport_release_request_history.request_type', $val1)->orWhere('passport_release_request_history.request_type', 8)
                    ->orWhere(function($q2) use($val2,$val3){
                      $q2->where('passport_release_request_history.release_request_generate', $val3)->where('passport_release_request_history.request_type', $val2);
                    });
                })->orderBy('passport_release_request_history.created_at','desc');


                $passportDetails = $newd->paginate($paginationValue);



                // $passportDetails = Passport::where('passport.release_request_generate',1)
                // ->paginate($paginationValue);
                // ->toSql();




                $reportsCount = $newd->get()->count();
            }


        }
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
        $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
        
        $passportDetails->setPath(config('app.url/listingRequestforApprovalPassports'));
        //print_r($documentCollectiondetails);exit;
        $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();

		return view("Passport/listingRequestforApprovalPassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	}


    // public function listingAfterApprovalData(Request $request) // Requested Passports After Approved or Rejected
	// {
	// 	//return "Hello";
	// 	$whereraw = '';
	// 	$whereraw1 = '';
	// 	$selectedFilter['CNAME'] = '';
	// 	$selectedFilter['CEMAIL'] = '';
	// 	$selectedFilter['DESC'] = '';
	// 	$selectedFilter['DEPT'] = '';
	// 	$selectedFilter['OPENING'] = '';
	// 	$selectedFilter['STATUS'] = '';
	// 	$selectedFilter['vintage'] = '';
	// 	$selectedFilter['Company'] = '';
	// 	$selectedFilter['Recruiter'] = '';
		
    //     //$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
    //     $filterList = array();
    //     $filterList['deptID'] = '';
    //     $filterList['productID'] = '';
    //     $filterList['designationID'] = '';
    //     $filterList['emp_name'] = '';
    //     $filterList['caption'] = '';
    //     $filterList['status'] = '';
    //     $filterList['serialized_id'] = '';
    //     $filterList['visa_process_status'] = '';
        
        
    
    //     if(!empty($request->session()->get('passport_page_limit')))
    //     {
    //         $paginationValue = $request->session()->get('passport_page_limit');
    //     }
    //     else
    //     {
    //         $paginationValue = 100;
    //     }
			
    //     if($whereraw != '')
    //     {
    //         // echo $whereraw;
    //         // exit;
    //         $empsessionId=$request->session()->get('EmployeeId');
    //         $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
    //         if($departmentDetails != '')
    //         {
    //             $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
    //             if($empDetails!='')
    //             {
    //                 $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
    //             ->where('employee_details.dept_id',$empDetails->dept_id)
    //             ->whereRaw($whereraw)
    //             ->orderBy('passport.updated_at','desc')
    //             //->toSql();
    //             //dd($passportDetails);
                
    //             ->paginate($paginationValue);

    //             $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
    //             ->where('employee_details.dept_id',$empDetails->dept_id)
    //             ->whereRaw($whereraw)
    //             ->orderBy('passport.id','desc')
    //             ->get()->count();	
    //             }
    //         }
    //         else
    //         {
    //             $passportDetails = Passport::whereRaw($whereraw)->orderBy('updated_at','desc')->paginate($paginationValue);
    //             $reportsCount = Passport::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
    //         }

    //     }
    //     else
    //     {
            
    //         $empsessionId=$request->session()->get('EmployeeId');
    //         $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
    //         if($departmentDetails != '')
    //         {
    //             $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
    //             if($empDetails!='')
    //             {
    //                 $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
    //                 ->where('employee_details.dept_id',$empDetails->dept_id)
    //                 ->orderBy('passport.updated_at','desc')
    //                 ->paginate($paginationValue);

    //                 $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->where('employee_details.dept_id',$empDetails->dept_id)->orderBy('passport.id','desc')
    //                 ->get()->count();
    //             }
    //         }
    //         else
    //         {
    //             $passportDetails = Passport::where('passport_status',1)->where('release_request_generate',1)->orderBy('updated_at','desc')->paginate($paginationValue);
    //             $reportsCount = Passport::where('passport_status',1)->where('release_request_generate',1)->orderBy('id','desc')->get()->count();
    //         }


    //     }
    //     $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
    //     $productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
    //     $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
        
    //     $passportDetails->setPath(config('app.url/listingAfterApprovalPassports'));
    //     //print_r($documentCollectiondetails);exit;
    //     $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();

	// 	return view("Passport/listingAfterApprovalPassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	// }


    public function listingReleasedPassportsData(Request $request) // Released Passport Queue List
	{
		//return "Hello";
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
		
        //$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';
        
        
    
        if(!empty($request->session()->get('passport_page_limit')))
        {
            $paginationValue = $request->session()->get('passport_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }
			
        if($whereraw != '')
        {
            // echo $whereraw;
            // exit;
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.updated_at','desc')
                //->toSql();
                //dd($passportDetails);
                
                ->paginate($paginationValue);

                $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.id','desc')
                ->get()->count();	
                }
            }
            else
            {
                $passportDetails = Passport::whereRaw($whereraw)->orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }

        }
        else
        {
            
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('passport.updated_at','desc')
                    ->paginate($paginationValue);

                    $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->where('employee_details.dept_id',$empDetails->dept_id)->orderBy('passport.id','desc')
                    ->get()->count();
                }
            }
            else
            {
                $passportDetails = Passport::where('passport_status',1)->where('release_request_generate',1)->where('request_status',1)->orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::where('passport_status',1)->where('release_request_generate',1)->where('request_status',1)->orderBy('id','desc')->get()->count();
            }


        }
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
        $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
        
        $passportDetails->setPath(config('app.url/listingRealeasedPassports'));
        //print_r($documentCollectiondetails);exit;
        $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();

		return view("Passport/listingRealeasedPassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	}



    public function listingPassportsLogsTabData(Request $request) // Passport Logs Details for sam
	{
		//return "Hello";
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
		
        //$documentCollectiondetails = EmpOffline::orderBy("id","DESC");
        $filterList = array();
        $filterList['deptID'] = '';
        $filterList['productID'] = '';
        $filterList['designationID'] = '';
        $filterList['emp_name'] = '';
        $filterList['caption'] = '';
        $filterList['status'] = '';
        $filterList['serialized_id'] = '';
        $filterList['visa_process_status'] = '';
        
        
    
        if(!empty($request->session()->get('passport_page_limit')))
        {
            $paginationValue = $request->session()->get('passport_page_limit');
        }
        else
        {
            $paginationValue = 100;
        }
			
        if($whereraw != '')
        {
            // echo $whereraw;
            // exit;
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.updated_at','desc')
                //->toSql();
                //dd($passportDetails);
                
                ->paginate($paginationValue);

                $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                ->where('employee_details.dept_id',$empDetails->dept_id)
                ->whereRaw($whereraw)
                ->orderBy('passport.id','desc')
                ->get()->count();	
                }
            }
            else
            {
                $passportDetails = Passport::whereRaw($whereraw)->orderBy('updated_at','desc')->paginate($paginationValue);
                $reportsCount = Passport::whereRaw($whereraw)->orderBy('id','desc')->get()->count();
            }

        }
        else
        {
            
            $empsessionId=$request->session()->get('EmployeeId');
            $departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
            if($departmentDetails != '')
            {
                $empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
                if($empDetails!='')
                {
                    $passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
                    ->where('employee_details.dept_id',$empDetails->dept_id)
                    ->orderBy('passport.updated_at','desc')
                    ->paginate($paginationValue);

                    $reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->where('employee_details.dept_id',$empDetails->dept_id)->orderBy('passport.id','desc')
                    ->get()->count();
                }
            }
            else
            {
                $passportDetails = Passport::select('passport.*', 'passport_release_request_history.*','passport_release_request_history.created_at as requestceated')->join('passport_release_request_history', 'passport_release_request_history.emp_id', '=', 'passport.emp_id')->where('request_type',1)->orWhere('request_type',2)->orderBy('passport_release_request_history.created_at','desc')
                ->paginate($paginationValue);
                // ->toSql();
                // dd($passportDetails);




                $reportsCount = Passport::join('passport_release_request_history', 'passport_release_request_history.emp_id', '=', 'passport.emp_id')->where('request_type',1)->orWhere('request_type',2)->orderBy('passport_release_request_history.created_at','desc')->get()->count();
            }


        }
        $departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
        $productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
        $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
        
        $passportDetails->setPath(config('app.url/listingPassportsLogs'));
        //print_r($documentCollectiondetails);exit;
        $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();

		return view("Passport/listingPassportsLogs",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	}



    public function requestReleasePassportFormDatainRow(Request $request)
	{
		$empid = $request->empid;
		$passportDetails = Passport::where('emp_id',$empid)->orderBy('id','desc')->first();
		return view("Passport/RequestReleaseFormContentinRow",compact('passportDetails'));
	}

    public function saveReleaseRequestinRowData(Request $request)
	{
		//return $request->all();
		$validator = Validator::make($request->all(), [
			//'passportnumber' => 'required',
			//'passportreleaseddate' => 'required',
            'releasecommentsinrow' => 'required',  
            'release_start_date' => 'required|date',
            'release_end_date' => 'required|date|after_or_equal:release_start_date',     
        ],
		[
			//'passportnumber.required'=> 'passport number is Required',
			//'passportreleaseddate.required'=> 'Passport Released Date is Required',
		 	'releasecommentsinrow.required'=> 'Comments field is Required',
            'release_start_date.required'=> 'Please Select From Date',
		 	'release_end_date.required'=> 'Please Select To Date',
            'release_end_date.after_or_equal'=> 'Date must be Equal or Greater than, Start Date',
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			//return $request->all();
            $userid=$request->session()->get('EmployeeId');
			$request->request->add(['user_id' => $userid]); //add request
			$this->log->info("Released Passport Request: " . json_encode($request->all()));

            $startDate = date("Y-m-d", strtotime($request->release_start_date));
            $endDate = date("Y-m-d", strtotime($request->release_end_date));

			$passportData = Passport::where('emp_id',$request->releasedempidinrow)->orderBy('id','DESC')->first();
			if($passportData)
			{
				$passportData->release_request_comments = $request->releasecommentsinrow;			
				$passportData->release_request_by = $userid;			
				$passportData->request_id = random_int(1000,9999).$request->releasedempidinrow.random_int(1000,9999);
				//$passportData->passport_number = $request->passportnumber;
				$passportData->release_request_generate = 1;	
				$passportData->release_request_at = date('Y-m-d H:i:s');	
				$passportData->release_start_date = $startDate;			
				$passportData->release_end_date = $endDate;

				$passportData->save();
			}
			else
			{
				$passportData = new Passport();
				$passportData->release_request_comments = $request->releasecommentsinrow;
				$passportData->emp_id = $request->releasedempidinrow;			
				$passportData->release_request_by = $userid;
				$passportData->request_id = random_int(1000,9999).$request->releasedempidinrow.random_int(1000,9999);
				//$passportData->passport_release_date = $request->passportreleaseddate;
				//$passportData->passport_number = $request->passportnumber;
				$passportData->release_request_generate = 1;	
				//$passportData->passport_status = 0;	
				$passportData->release_request_at = date('Y-m-d H:i:s');
                $passportData->release_start_date = $startDate;		
				$passportData->release_end_date = $endDate;
				$passportData->save();
			}

			$passportData = Passport::where('emp_id',$request->releasedempidinrow)->orderBy('id','DESC')->first();

			
			$passportHistory = new PassportHistory();
			$passportHistory->emp_id = $request->releasedempidinrow;
			$passportHistory->requestcreatedat = date('Y-m-d');
			$passportHistory->requestcreatedby = $userid;
			$passportHistory->requestcreatedcomment = $request->releasecommentsinrow;
			//$passportHistory->passport_release_date = $request->passportreleaseddate;
			//$passportHistory->release_status = 1;
			$passportHistory->request_type = 3;
			$passportHistory->status = 1;
			$passportHistory->request_id = $passportData->request_id;
            $passportHistory->release_start_date = $startDate;			
			$passportHistory->release_end_date = $endDate;
            $passportHistory->release_request_generate = 1;
			$passportHistory->save();


			return response()->json(['success'=>'Release Passport Request Generated Successfully.']);
		}

		
	}



    
	public function saveReleaseRequestfromTopData(Request $request)
	{
		//return $request->all();



		$validator = Validator::make($request->all(), [
			//'passportnumber' => 'required',
			//'passportreleaseddate' => 'required',
            'releasedformempcomments' => 'required', 
            'release_start_date_top' => 'required|date',
            'release_end_date_top' => 'required|date|after_or_equal:release_start_date_top',        
        ],
		[
			//'passportnumber.required'=> 'passport number is Required',
			//'passportreleaseddate.required'=> 'Passport Released Date is Required',
		 	'releasedformempcomments.required'=> 'Comments field is Required',
             'release_start_date_top.required'=> 'Please Select From Date',
		 	'release_end_date_top.required'=> 'Please Select To Date',
            'release_end_date_top.after_or_equal'=> 'Date must be Equal or Greater than, Start Date',
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			$userid=$request->session()->get('EmployeeId');
			$request->request->add(['user_id' => $userid]); //add request
			$this->log->info("Released Passport Request: " . json_encode($request->all()));

			$passportData = Passport::where('emp_id',$request->releasedformempid)->orderBy('id','DESC')->first();

            $startDate = date("Y-m-d", strtotime($request->release_start_date_top));
            $endDate = date("Y-m-d", strtotime($request->release_end_date_top));



			if($passportData)
			{
				$passportData->release_request_comments = $request->releasedformempcomments;			
				$passportData->release_request_by = $userid;			
				$passportData->request_id = random_int(1000,9999).$request->releasedformempid.random_int(1000,9999);
				//$passportData->passport_number = $request->passportnumber;
				$passportData->release_request_generate = 1;	
				$passportData->release_request_at = date('Y-m-d H:i:s');	
                $passportData->release_start_date = $startDate;			
				$passportData->release_end_date = $endDate;
				$passportData->save();
			}
			else
			{

				$passportData = new Passport();
				$passportData->release_request_comments = $request->releasedformempcomments;
				$passportData->emp_id = $request->releasedformempid;			
				$passportData->release_request_by = $userid;
				$passportData->request_id = random_int(1000,9999).$request->releasedformempid.random_int(1000,9999);
				//$passportData->passport_release_date = $request->passportreleaseddate;
				//$passportData->passport_number = $request->passportnumber;
				$passportData->release_request_generate = 1;	
				//$passportData->passport_status = 0;	
				$passportData->release_request_at = date('Y-m-d H:i:s');
                $passportData->release_start_date = $startDate;			
				$passportData->release_end_date = $endDate;
				$passportData->save();
			}


			


			$passportData = Passport::where('emp_id',$request->releasedformempid)->orderBy('id','DESC')->first();

			
			$passportHistory = new PassportHistory();
			$passportHistory->emp_id = $request->releasedformempid;
			$passportHistory->requestcreatedat = date('Y-m-d');
			$passportHistory->requestcreatedby = $userid;
			$passportHistory->requestcreatedcomment = $request->releasedformempcomments;
			//$passportHistory->passport_release_date = $request->passportreleaseddate;
			//$passportHistory->release_status = 1;
			$passportHistory->request_type = 3;
			$passportHistory->status = 1;
			$passportHistory->request_id = $passportData->request_id;
            $passportHistory->release_start_date = $startDate;			
			$passportHistory->release_end_date = $endDate;
			$passportHistory->save();


			return response()->json(['success'=>'Release Passport Request Saved Successfully.']);


		}

		
	}







	public function saveReleaseRequest(Request $request)
	{
		//return $request->all();
		$validator = Validator::make($request->all(), [
			//'passportnumber' => 'required',
			'passportreleaseddate' => 'required',
            'releasecomments' => 'required',       
        ],
		[
			//'passportnumber.required'=> 'passport number is Required',
			'passportreleaseddate.required'=> 'Passport Released Date is Required',
		 	'releasecomments.required'=> 'Comments field is Required',
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			$userid=$request->session()->get('EmployeeId');
			$request->request->add(['user_id' => $userid]); //add request
			$this->log->info("Released Passport Request: " . json_encode($request->all()));

			$passportData = Passport::where('emp_id',$request->empid)->orderBy('id','DESC')->first();

            $passportData->passport_released_comments = $request->releasecomments;			
            $passportData->released_by = $userid;			
            $passportData->passport_release_date = $request->passportreleaseddate;
            $passportData->collection_queue_status = 1;
            $passportData->passport_status = 0;	
            $passportData->released_at = date('Y-m-d H:i:s');

            if($passportData->request_id == '')
            {
                $passportData->request_id = random_int(1000,9999).$request->empid.random_int(1000,9999);

            }

            $passportData->save();
			
			$passportData = Passport::where('emp_id',$request->empid)->orderBy('id','DESC')->first();

			$passportHistory = new PassportHistory();
			$passportHistory->emp_id = $request->empid;
			$passportHistory->release_at = date('Y-m-d');
			$passportHistory->release_by = $userid;
			$passportHistory->release_comments = $request->releasecomments;
			$passportHistory->passport_release_date = $request->passportreleaseddate;
			$passportHistory->release_status = 1;
			$passportHistory->request_type = 1;
			$passportHistory->status = 1;
			$passportHistory->request_id = $passportData->request_id;
			$passportHistory->save();

			return response()->json(['success'=>'Release Passport Request Saved Successfully.']);
		}

		
	}










    public function requestReleasePassportApprovedActionProcess(Request $request)
	{
		$empid = $request->empid;
		$userid=$request->session()->get('EmployeeId');
		$passportDetails = Passport::where('emp_id',$empid)->orderBy('id','desc')->first();

		$passportDetails->request_status = 1;
		$passportDetails->request_approved_at = date('Y-m-d');	
		$passportDetails->request_approved_by = $userid;
        //$passportDetails->release_request_generate = NULL;					
		$passportDetails->save();

		$passportData = Passport::where('emp_id',$request->empid)->orderBy('id','DESC')->first();

		$passportHistory = new PassportHistory();
		$passportHistory->emp_id = $request->empid;
		$passportHistory->requestcreatedat = date('Y-m-d H:i:s');
		$passportHistory->requestcreatedby = $userid;
		//$passportHistory->requestcreatedcomment = $request->releasecomments;
		$passportHistory->request_type = 7;
		$passportHistory->status = 1;
		$passportHistory->request_id = $passportData->request_id;
		$passportHistory->save();



        $passportHistoryData = PassportHistory::where('emp_id',$request->empid)->where('request_type',3)->orderBy('id','DESC')->first();

        if($passportHistoryData)
        {
            $passportHistoryData->release_request_generate = NULL;					
		    $passportHistoryData->save();
        }




		return response()->json(['success'=>'Release Passport Request Approved Successfully.']);

	}
	public function requestReleasePassportRejectedActionProcess(Request $request)
	{
		$empid = $request->empid;
		$userid=$request->session()->get('EmployeeId');
		$passportDetails = Passport::where('emp_id',$empid)->orderBy('id','desc')->first();

		$passportDetails->request_status = 2;
		$passportDetails->request_reject_at = date('Y-m-d H:i:s');	
		$passportDetails->request_reject_by = $userid;	
        
        $passportDetails->release_request_generate = NULL;
        $passportDetails->request_status = NULL;
        
		$passportDetails->save();

		$passportData = Passport::where('emp_id',$request->empid)->orderBy('id','DESC')->first();

		$passportHistory = new PassportHistory();
		$passportHistory->emp_id = $request->empid;
		$passportHistory->requestcreatedat = date('Y-m-d');
		$passportHistory->requestcreatedby = $userid;
		$passportHistory->requestcreatedcomment = $request->releasecomments;
		$passportHistory->request_type = 8;
		$passportHistory->status = 1;
		$passportHistory->request_id = $passportData->request_id;
		$passportHistory->save();



        $passportHistoryData = PassportHistory::where('emp_id',$request->empid)->where('request_type',3)->orderBy('id','DESC')->first();

        if($passportHistoryData)
        {
            $passportHistoryData->release_request_generate = NULL;					
		    $passportHistoryData->save();
        }




		return response()->json(['success'=>'Release Passport Request Rejected Successfully.']);

	}








    public function requestReleasePassportFormData(Request $request)
	{
		$empid = $request->empid;
		$passportDetails = Passport::where('emp_id',$empid)->orderBy('id','desc')->first();
		return view("Passport/RequestReleaseFormContent",compact('passportDetails'));
	}















    public function requestforPassportFormData(Request $request)
	{
		$empid = $request->empid;
		$passportDetails = Passport::where('emp_id',$empid)->orderBy('id','desc')->first();

		//return $passportDetails;


		return view("Passport/RequestforPassport",compact('passportDetails'));
	}

    public function requestPassportformPostData(Request $request)
	{
		//return $request->all();

		$validator = Validator::make($request->all(), [
            'passportsubmitdate' => 'required',
			//'passportnumber' => 'required',
			'requestcomments' => 'required',      
        ],
		[
		 'passportsubmitdate.required'=> 'Passport Submition date is required',
		// 'passportnumber.required'=> 'Passport Number is Required',
		 'requestcomments.required'=> 'Comments is Required',
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			//return $request->all();
			$this->log->info("Request for Passport Request: " . json_encode($request->all()));


			$passportData = Passport::where('emp_id',$request->empid)->orderBy('id','DESC')->first();

			$userid=$request->session()->get('EmployeeId');
			$passportData->collect_passport_comment = $request->requestcomments;
			$passportData->collect_by = $userid;			
			$passportData->collect_at = date('Y-m-d H:i:s');
			$passportData->passport_status = 1;	
            $passportData->passport_collect_date = $request->passportsubmitdate;

			$passportData->release_request_generate = NULL;
            $passportData->request_status = NULL;
            $passportData->request_approved_at = NULL;	
            $passportData->request_approved_by = NULL;
			$passportData->save();





			$passportData = Passport::where('emp_id',$request->empid)->orderBy('id','DESC')->first();

			
			$passportHistory = new PassportHistory();
			$passportHistory->emp_id = $request->empid;
			$passportHistory->request_at = date('Y-m-d');
			$passportHistory->request_by = $userid;
			$passportHistory->passport_submit_date = $request->passportsubmitdate;
			$passportHistory->request_comments = $request->requestcomments;
			$passportHistory->request_status = 1;
			$passportHistory->request_type = 2;
			$passportHistory->status = 1;
			$passportHistory->request_id = $passportData->request_id;

			$passportHistory->save();

			return response()->json(['success'=>'Request Saved Successfully.']);


		}

	}












    public function releaseRequestModelData(Request $request)
	{
			$empsessionId=$request->session()->get('EmployeeId');
			$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
			if($departmentDetails != '')
			{
				$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
				if($empDetails!='')
				{
					$empDetails = Passport::where('passport_status',1)
					->join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
					->where('employee_details.dept_id',$empDetails->dept_id)
					// ->toSql();
					// dd($empDetails);
					->get();
				}
			}
			else{
				$empDetails = Passport::where('passport_status',1)->get();
			}


			


		
			//$empDetails = Passport::where('passport_status',1)->get();


		return view("Passport/RequestReleasedPop",compact('empDetails'));
	}


    public function getEmployeePassportInfoData($empid)
	{
		//$empDataFirst = Employee_details_change_salary::select('emp_id')->get()->toArray();

		$empDetails = Employee_details::	
		where('emp_id',$empid)
		->orderBy('id','desc')->first();



		$passportDetails = Passport::where('emp_id',$empid)
		->first();


		return view("Passport/passportDetailsFilled",compact('passportDetails','empDetails'));

	}


    public function requestPassportHistoryDetails(Request $request)
	{
		$empid = $request->empid;

		//return $empid;



		$passportDetails = PassportHistory::where("emp_id",$empid)->orderBy('id','DESC')->get();
		/* echo "<pre>";
		print_r($passportDetails);
		exit; */
		//return $passportDetails;

		return view("Passport/RequestHistoryDetails",compact('passportDetails','empid'));


	}


    

}