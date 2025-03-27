<?php

namespace App\Http\Controllers\CompareFeatures;

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
use App\Models\AdvancedPay\AdvancedPayRequest;
use App\Models\AdvancedPay\RecoveryAmt;





use App\Models\CompareFeatures\Features;
use App\Models\CompareCategories\CompareCategories;


use App\Models\Employee\ExportDataLog;

class FeaturesController extends Controller
{
    public function __construct(LoggerFactory $logFactory)
    {
        $this->log = $logFactory->setPath('logs/Comparefeatures')->createLogger('features'); 
    }

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
        return view("CompareFeatures/index",compact('empDetailsIndex','departmentLists','designationLists','tL_details'));
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

    public static function getCateName($cateid)
	{
        $departmentDetails = CompareCategories::where("id",$cateid)->first();
        if($departmentDetails != '')
        {
            
				return $departmentDetails->name;
           
        }
        else
        {
			return "--";		
        }
		//return $employeeData;
	}


    

    public static function addFeaturesData(Request $request)
    {
        $loggedinUserid=$request->session()->get('EmployeeId');
        
        $cateDetails = CompareCategories::orderBy('id', 'desc')->get();
        
        
        //$empDetails = Employee_details::where('offline_status',1)->orderBy('id', 'desc')->get();

        return view("CompareFeatures/addRequest",compact('cateDetails'));
    }

    public static function getEmpContentData(Request $request)
    {
        $rowid=$request->rowid;
        // $empDetails='';
        // $empAdvancedDetails = AdvancedPayRequest::where('emp_id',$empid)->where('approved_reject_status',1)->orderBy('id', 'desc')->get();

        // $empRecoveryDetails = RecoveryAmt::where('emp_id',$empid)->orderBy('id', 'desc')->get();

        // if($empAdvancedDetails)
        // {
        //     $empDetails = $empAdvancedDetails;
        // }


        // $approvedAmt = AdvancedPayRequest::where('emp_id',$empid)->sum('approved_advanced_amt');
        // $recoveredAmt = RecoveryAmt::where('emp_id',$empid)->sum('recovery_amt');
        // $balancedAmt = $approvedAmt - $recoveredAmt;

        return view("CompareFeatures/addRequestContent");

    }


    public function getTableRows(Request $request)
	{

	$i = $request->counter;
	





	return view("CompareFeatures/tableRowContent",compact('i'));

	}



    public function addNewFeaturesRequestPostSubmit(Request $request)
    {
        
        //return $request->all();
        
        $validator = Validator::make($request->all(), 
        [			
			'features1' => 'required',
            'features2' => 'required_if:counter,2',
            'features3' => 'required_if:counter,3',
            'features4' => 'required_if:counter,4',
            'features5' => 'required_if:counter,5',
            'features6' => 'required_if:counter,6',
           // 'teamleaders' => 'required', 
        ],
		[
			'features1.required'=> 'Please Add Feature',
            //'features2.required'=> 'Please Fill Feature ',
            'features2.required_if'=> 'Please Add Feature',
            'features3.required_if'=> 'Please Add Feature',
            'features4.required_if'=> 'Please Add Feature',
            'features5.required_if'=> 'Please Add Feature',
            'features6.required_if'=> 'Please Add Feature',
            // 'features3.required'=> 'Please Fill Feature ',
            // 'features4.required'=> 'Please Fill Feature ',
            // 'features5.required'=> 'Please Fill Feature ',
            // 'advancedamt.numeric'=> 'Amount must be a number.',
		 	// 'addRequestEmp.required'=> 'Please Select Employee from List',
			//'teamleaders.required'=> 'Please Select Team Leader from List',
				
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            
            $advancedPayRequest = new Features();
            $advancedPayRequest->category_id = $request->addRequestEmp;
            $advancedPayRequest->features = $request->features1;
            $advancedPayRequest->created_at = date('Y-m-d H:i:s');
            $advancedPayRequest->status = 1;
            $advancedPayRequest->save();


            foreach ($request->features as $user)
            {
                $user;

                $advancedPayRequest = new Features();
                $advancedPayRequest->category_id = $request->addRequestEmp;
                $advancedPayRequest->features = $user;
                $advancedPayRequest->created_at = date('Y-m-d H:i:s');
                $advancedPayRequest->status = 1;
                $advancedPayRequest->save();
            }
            return $request->all();
            


            $usersessionId=$request->session()->get('EmployeeId');
            //$transferRequestData = AdvancedPayRequest::where('emp_id',$request->empid)->where('id',$request->rowid)->orderBy('id', 'desc')->first();

            


            

           

            return response()->json(['success'=>'Advanced Pay Request Added Successfully.']);
        }
    }


    public function allFeaturesListingData(Request $request)
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
                
                $empDetails = Employee_details::whereRaw($whereraw)->orderBy('id', 'desc')
                ->get();
                
                $newResult=array();
                foreach($empDetails as $value)
                {
                    $newResult[]=$value->emp_id;
                }
                
                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id', 'desc')					
                ->paginate($paginationValue);

                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id','desc')
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
                
                
                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id', 'desc')					
                ->paginate($paginationValue);
                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id','desc')
                ->get()->count();
            }
        }
        else
        {
            if($empData==1) // all
            {
                $requestDetails = Features::groupBy('category_id')->orderBy('id', 'desc')
                //->toSql();	 
                //dd($documentCollectiondetails);						
                ->paginate($paginationValue);	
                
                $reportsCount = Features::groupBy('category_id')->orderBy('id','desc')
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


                $requestDetails = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id', 'desc')
                //->toSql();	 
                //dd($documentCollectiondetails);						
                ->paginate($paginationValue);


                

                
                $reportsCount = AdvancedPayRequest::whereIn('emp_id',$newResult)->orderBy('id','desc')
                ->get()->count();
            }
        }

        //return $requestDetails;
        
        $requestDetails->setPath(config('app.url/listingAll'));
        return view("CompareFeatures/listingAll",compact('requestDetails','paginationValue','reportsCount'));
    }

    


    

}