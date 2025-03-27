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


class PassportController extends Controller
{
    public function __construct(LoggerFactory $logFactory)
    {
        $this->log = $logFactory->setPath('logs/passport')->createLogger('passport'); 
    }
       
	public function index(Request $request)
	{
		// $this->log->info("Store Basic Details Request: " . json_encode($input));
		
		//$this->log->info("Store Basic Details Request: ");	

  
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		  $tL_details = Employee_details::where("job_role","Team Leader")->orderBy("id","ASC")->get();
		  //$empId = Employee_details::where("offline_status",1)->get();
		  $empId = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->get();

		  $Designation=Designation::where("status",1)->get();

		  return view("Passport/PassportIndex",compact('departmentLists','tL_details','empId','Designation'));
	}

	public function listingAllPassportsData(Request $request)
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
			
			//$request->session()->put('cname_empAll_filter_inner_list','');
			if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
		  	{
			  $departmentID = $request->session()->get('onboarding_department_filter');
			  //$whereraw .= 'department = "'.$departmentID.'"';
		  	}
		
			if(!empty($request->session()->get('passport_page_limit')))
			{
				$paginationValue = $request->session()->get('passport_page_limit');
			}
			else
			{
				$paginationValue = 100;
			}
			
			
			if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
			{
				$retained = $request->session()->get('offboardall_retained_filter_inner_list');
				 if($whereraw == '')
				{
					$whereraw = 'retain = "'.$retained.'"';
				}
				else
				{
					$whereraw .= ' And retain = "'.$retained.'"';
				}
			}
			
			if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
			{
				$exittype = $request->session()->get('offboardall_filter_inner_list');
				 if($whereraw == '')
				{
					$whereraw = 'leaving_type = "'.$exittype.'"';
				}
				else
				{
					$whereraw .= ' And leaving_type = "'.$exittype.'"';
				}
			}
			
			
			
			//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
			
			if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
			{
				$datefrom = $request->session()->get('passport_fromdate');
				 if($whereraw == '')
				{
					$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
			{
				$dateto = $request->session()->get('passport_todate');
				 if($whereraw == '')
				{
					$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
			{
				$dept = $request->session()->get('passport_department');
				 //$departmentArray = explode(",",$dept);
				if($whereraw == '')
				{
					$whereraw = 'employee_details.dept_id IN('.$dept.')';
				}
				else
				{
					$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
				}
			}
			if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
			{
				$teamlead = $request->session()->get('passport_teamleader');
				 //$departmentArray = explode(",",$dept);
				if($whereraw == '')
				{
					$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
					
				}
				else
				{
					$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
				}
			}
			if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
			{
				$empId = $request->session()->get('passport_emp_id');
				 if($whereraw == '')
				{
					$whereraw = 'passport.emp_id IN ('.$empId.')';
				}
				else
				{
					$whereraw .= ' And passport.emp_id IN ('.$empId.')';
				}
			}







			if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
				{
					$designd = $request->session()->get('passport_designation');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
					}
				}



			// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
			// {
			// 	$rangeid = $request->session()->get('range_filter_inner_list');
			// 	 if($whereraw == '')
			// 	{
			// 		$whereraw = 'range_id IN ('.$rangeid.')';
			// 	}
			// 	else
			// 	{
			// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
			// 	}
			// }










			if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
			{
				$fname = $request->session()->get('passport_emp_name');
				 $cnameArray = explode(",",$fname);
				 
				 $namefinalarray=array();
				 foreach($cnameArray as $namearray){
					 $namefinalarray[]="'".$namearray."'";					 
				 }
				 //print_r($namefinalarray);exit;
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
			
			if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
			{
				$company = $request->session()->get('company_candAll_filter_inner_list');
				 $selectedFilter['Company'] = $company;
				 if($whereraw == '')
				{
					$whereraw = 'company_visa = "'.$company.'"';
				}
				else
				{
					$whereraw .= ' And company_visa = "'.$company.'"';
				}
			}
			//echo $cname;exit;
			if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
			{
				$email = $request->session()->get('email_candAll_filter_inner_list');
				 $selectedFilter['CEMAIL'] = $email;
				 if($whereraw == '')
				{
					$whereraw = 'email = "'.$email.'"';
				}
				else
				{
					$whereraw .= ' And email = "'.$email.'"';
				}
			}
			if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
			{
				$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
				 if($whereraw == '')
				{
					$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
				}
			}
			if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
			{
				$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
				 if($whereraw == '')
				{
					$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
				}
			}
			


			
			
			if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
			{
				$dept = $request->session()->get('dept_candAll_filter_inner_list');
				 $selectedFilter['DEPT'] = $dept;
				 if($whereraw == '')
				{
					$whereraw = 'department = "'.$dept.'"';
				}
				else
				{
					$whereraw .= ' And department = "'.$dept.'"';
				}
			}
			if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
			{
				$opening = $request->session()->get('opening_cand_filter_inner_list');
				 $selectedFilter['OPENING'] = $opening;
				 if($whereraw == '')
				{
					$whereraw = 'job_opening IN('.$opening.')';
				}
				else
				{
					$whereraw .= ' And job_opening IN('.$opening.')';
				}
			}
			if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
			{
				$status = $request->session()->get('status_candAll_filter_inner_list');
				 $selectedFilter['STATUS'] = $status;
				 if($whereraw == '')
				{
					$whereraw = 'status = "'.$status.'"';
				}
				else
				{
					$whereraw .= ' And status = "'.$status.'"';
				}
			}
			//echo $whereraw;exit;
			if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
			{
				$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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
			
			
			
			
			
			
			if($whereraw != '')
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
				else{
					$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
					->whereRaw($whereraw)
					->orderBy('passport.updated_at','desc')
					//->toSql();
	
					//dd($passportDetails);
					
					->paginate($paginationValue);
	
					$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
					->whereRaw($whereraw)
					->orderBy('passport.id','desc')
					->get()->count();	
				}

				
				
				
				
				//echo "hello";exit;
				

						

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
				else{
					$passportDetails = Passport::orderBy('updated_at','desc')->paginate($paginationValue);

					$reportsCount = Passport::orderBy('id','desc')
					->get()->count();
				}

				
				
				
				
				
				
				
				
				
				
				
				
				
				//echo "hello1";
				

				

			}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
				$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
				$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
			
			$passportDetails->setPath(config('app.url/listingAllPassports'));
			
			//print_r($documentCollectiondetails);exit;
	
	 		$salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();

		return view("Passport/listingAllPassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','salaryBreakUpdetails','paginationValue'));
	}

	public function exportAllPassportsReport(Request $request)
		{
			
			$parameters = $request->input(); 
				 $selectedId = $parameters['selectedIds'];
				 
				$filename = 'passport_report_'.date("d-m-Y").'.xlsx';
				$spreadsheet = new Spreadsheet(); 
				$sheet = $spreadsheet->getActiveSheet();
				$sheet->mergeCells('A1:J1');
				$sheet->setCellValue('A1', 'Passports List - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
				$indexCounter = 2;
				$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper('Team Leader'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper('Designation'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, strtoupper('Department'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, strtoupper('Work Location'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, strtoupper('Vintage Days'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, strtoupper('Passport Number'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, strtoupper('Passport Status'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn = 1;
				foreach ($selectedId as $sid) {
					//echo $sid;
					$misData = Passport::where("id",$sid)->first();

					$empName = $this->getEmployeeName($misData->emp_id);
					$teamLeader = $this->getTeamLeader($misData->emp_id);
					$designation = $this->getDesignation($misData->emp_id);
					$dept = $this->getDepartment($misData->emp_id);
					$location = $this->getWorkLocation($misData->emp_id);
					$vintage = $this->getVintage($misData->emp_id);
					$indexCounter++; 
					
					if($misData->passport_status==1)
					{
						$pstatus='Available';
					}
					else
					{
						$pstatus='Not Available';
					}
					
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $empName)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $teamLeader)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $designation)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $dept)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $vintage)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('I'.$indexCounter, $misData->passport_number)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('J'.$indexCounter, $pstatus)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					
					$sn++;
					
				}
				
				
				  for($col = 'A'; $col !== 'J'; $col++) {
				   $sheet->getColumnDimension($col)->setAutoSize(true);
				}
				
				$spreadsheet->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
					
					for($index=1;$index<=$indexCounter;$index++)
					{
						  foreach (range('A','J') as $col) {
								$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
						  }
					}
					$writer = new Xlsx($spreadsheet);
					$writer->save(public_path('uploads/exportPassport/'.$filename));	
					echo $filename;
					exit;
			}



	public function listingAvailablePassportsData(Request $request)
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
			
			//$request->session()->put('cname_empAll_filter_inner_list','');
			if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
		  	{
			  $departmentID = $request->session()->get('onboarding_department_filter');
			  //$whereraw .= 'department = "'.$departmentID.'"';
		  	}
		
			if(!empty($request->session()->get('passport_page_limit'))) 
			{
				$paginationValue = $request->session()->get('passport_page_limit');
			}
			else
			{
				$paginationValue = 100;
			}
			
			
			if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
			{
				$retained = $request->session()->get('offboardall_retained_filter_inner_list');
				 if($whereraw == '')
				{
					$whereraw = 'retain = "'.$retained.'"';
				}
				else
				{
					$whereraw .= ' And retain = "'.$retained.'"';
				}
			}
			
			if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
			{
				$exittype = $request->session()->get('offboardall_filter_inner_list');
				 if($whereraw == '')
				{
					$whereraw = 'leaving_type = "'.$exittype.'"';
				}
				else
				{
					$whereraw .= ' And leaving_type = "'.$exittype.'"';
				}
			}
			
			
			
			//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
			
			if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
			{
				$datefrom = $request->session()->get('passport_fromdate');
				 if($whereraw == '')
				{
					$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
			{
				$dateto = $request->session()->get('passport_todate');
				 if($whereraw == '')
				{
					$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
			{
				$dept = $request->session()->get('passport_department');
				 //$departmentArray = explode(",",$dept);
				if($whereraw == '')
				{
					$whereraw = 'employee_details.dept_id IN('.$dept.')';
				}
				else
				{
					$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
				}
			}
			if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
			{
				$teamlead = $request->session()->get('passport_teamleader');
				 //$departmentArray = explode(",",$dept);
				if($whereraw == '')
				{
					$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
					
				}
				else
				{
					$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
				}
			}
			if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
			{
				$empId = $request->session()->get('passport_emp_id');
				 if($whereraw == '')
				{
					$whereraw = 'passport.emp_id IN ('.$empId.')';
				}
				else
				{
					$whereraw .= ' And passport.emp_id IN ('.$empId.')';
				}
			}







			if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
				{
					$designd = $request->session()->get('passport_designation');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
					}
				}



			// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
			// {
			// 	$rangeid = $request->session()->get('range_filter_inner_list');
			// 	 if($whereraw == '')
			// 	{
			// 		$whereraw = 'range_id IN ('.$rangeid.')';
			// 	}
			// 	else
			// 	{
			// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
			// 	}
			// }










			if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
			{
				$fname = $request->session()->get('passport_emp_name');
				 $cnameArray = explode(",",$fname);
				 
				 $namefinalarray=array();
				 foreach($cnameArray as $namearray){
					 $namefinalarray[]="'".$namearray."'";					 
				 }
				 //print_r($namefinalarray);exit;
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
			
			if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
			{
				$company = $request->session()->get('company_candAll_filter_inner_list');
				 $selectedFilter['Company'] = $company;
				 if($whereraw == '')
				{
					$whereraw = 'company_visa = "'.$company.'"';
				}
				else
				{
					$whereraw .= ' And company_visa = "'.$company.'"';
				}
			}
			//echo $cname;exit;
			if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
			{
				$email = $request->session()->get('email_candAll_filter_inner_list');
				 $selectedFilter['CEMAIL'] = $email;
				 if($whereraw == '')
				{
					$whereraw = 'email = "'.$email.'"';
				}
				else
				{
					$whereraw .= ' And email = "'.$email.'"';
				}
			}
			if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
			{
				$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
				 if($whereraw == '')
				{
					$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
				}
			}
			if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
			{
				$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
				 if($whereraw == '')
				{
					$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
				}
			}
			


			
			
			if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
			{
				$dept = $request->session()->get('dept_candAll_filter_inner_list');
				 $selectedFilter['DEPT'] = $dept;
				 if($whereraw == '')
				{
					$whereraw = 'department = "'.$dept.'"';
				}
				else
				{
					$whereraw .= ' And department = "'.$dept.'"';
				}
			}
			if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
			{
				$opening = $request->session()->get('opening_cand_filter_inner_list');
				 $selectedFilter['OPENING'] = $opening;
				 if($whereraw == '')
				{
					$whereraw = 'job_opening IN('.$opening.')';
				}
				else
				{
					$whereraw .= ' And job_opening IN('.$opening.')';
				}
			}
			if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
			{
				$status = $request->session()->get('status_candAll_filter_inner_list');
				 $selectedFilter['STATUS'] = $status;
				 if($whereraw == '')
				{
					$whereraw = 'status = "'.$status.'"';
				}
				else
				{
					$whereraw .= ' And status = "'.$status.'"';
				}
			}
			//echo $whereraw;exit;
			if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
			{
				$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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
		
			
			
		
			
			
			
			if($whereraw != '')
			{
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				if($departmentDetails != '')
				{
					$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					if($empDetails!='')
					{
						$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
						->select('passport.id as rowid','passport.*','employee_details.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->whereRaw($whereraw)
						->where('passport.passport_status',1)
						// ->where('finalstatus',0)
						// ->orWhere('finalstatus', '>', 0)
						->orderBy('passport.updated_at','desc')->paginate($paginationValue);
		
						$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->whereRaw($whereraw)
						->where('passport.passport_status',1)
						// ->where('finalstatus',0)
						// ->orWhere('finalstatus', '>', 0)
						->orderBy('passport.id','desc')
						->get()->count();
					}
				}
				else{
					$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
					->select('passport.id as rowid','passport.*','employee_details.*')
	
					->whereRaw($whereraw)
					->where('passport.passport_status',1)
					// ->where('finalstatus',0)
					// ->orWhere('finalstatus', '>', 0)
					->orderBy('passport.updated_at','desc')->paginate($paginationValue);
	
					$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
					->whereRaw($whereraw)
					->where('passport.passport_status',1)
					// ->where('finalstatus',0)
					// ->orWhere('finalstatus', '>', 0)
					->orderBy('passport.id','desc')
					->get()->count();
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
						->select('passport.id as rowid','passport.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->where('passport.passport_status',1)
						// ->where('finalstatus',0)
						// ->orWhere('finalstatus', '>', 0)
						->orderBy('passport.updated_at','desc')->paginate($paginationValue);
		
						$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->where('employee_details.dept_id',$empDetails->dept_id)->where('passport.passport_status',1)
						// ->where('finalstatus',0)
						// ->orWhere('finalstatus', '>', 0)
						->orderBy('passport.id','desc')->get()->count();
					}
				}
				else{
					$passportDetails = Passport::
					select('passport.id as rowid','passport.*')
	
					->where('passport.passport_status',1)
					// ->where('finalstatus',0)
					// ->orWhere('finalstatus', '>', 0)
					->orderBy('passport.updated_at','desc')->paginate($paginationValue);
	
					$reportsCount = Passport::where('passport.passport_status',1)
					// ->where('finalstatus',0)
					// ->orWhere('finalstatus', '>', 0)
					->orderBy('id','desc')->get()->count();
				}

				
				
				
				
				
				
				
				



				


			}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
				$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
				$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
			
			$passportDetails->setPath(config('app.url/listingAvailablePassports'));
			
			//print_r($documentCollectiondetails);exit;
	

		return view("Passport/listingAvailablePassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','paginationValue'));
	}


	public function listingPassportsNotAvailableData(Request $request)
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
			
			//$request->session()->put('cname_empAll_filter_inner_list','');
			if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
		  	{
			  $departmentID = $request->session()->get('onboarding_department_filter');
			  //$whereraw .= 'department = "'.$departmentID.'"';
		  	}
		
			if(!empty($request->session()->get('passport_page_limit')))
			{
				$paginationValue = $request->session()->get('passport_page_limit');
			}
			else
			{
				$paginationValue = 100;
			}
			
			
			if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
			{
				$retained = $request->session()->get('offboardall_retained_filter_inner_list');
				 if($whereraw == '')
				{
					$whereraw = 'retain = "'.$retained.'"';
				}
				else
				{
					$whereraw .= ' And retain = "'.$retained.'"';
				}
			}
			
			if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
			{
				$exittype = $request->session()->get('offboardall_filter_inner_list');
				 if($whereraw == '')
				{
					$whereraw = 'leaving_type = "'.$exittype.'"';
				}
				else
				{
					$whereraw .= ' And leaving_type = "'.$exittype.'"';
				}
			}
			
			
			
			//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
			
			if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
			{
				$datefrom = $request->session()->get('passport_fromdate');
				 if($whereraw == '')
				{
					$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
			{
				$dateto = $request->session()->get('passport_todate');
				 if($whereraw == '')
				{
					$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
			{
				$dept = $request->session()->get('passport_department');
				 //$departmentArray = explode(",",$dept);
				if($whereraw == '')
				{
					$whereraw = 'employee_details.dept_id IN('.$dept.')';
				}
				else
				{
					$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
				}
			}
			if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
			{
				$teamlead = $request->session()->get('passport_teamleader');
				 //$departmentArray = explode(",",$dept);
				if($whereraw == '')
				{
					$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
					
				}
				else
				{
					$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
				}
			}
			if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
			{
				$empId = $request->session()->get('passport_emp_id');
				 if($whereraw == '')
				{
					$whereraw = 'passport.emp_id IN ('.$empId.')';
				}
				else
				{
					$whereraw .= ' And passport.emp_id IN ('.$empId.')';
				}
			}







			if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
				{
					$designd = $request->session()->get('passport_designation');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
					}
				}



			// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
			// {
			// 	$rangeid = $request->session()->get('range_filter_inner_list');
			// 	 if($whereraw == '')
			// 	{
			// 		$whereraw = 'range_id IN ('.$rangeid.')';
			// 	}
			// 	else
			// 	{
			// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
			// 	}
			// }










			if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
			{
				$fname = $request->session()->get('passport_emp_name');
				 $cnameArray = explode(",",$fname);
				 
				 $namefinalarray=array();
				 foreach($cnameArray as $namearray){
					 $namefinalarray[]="'".$namearray."'";					 
				 }
				 //print_r($namefinalarray);exit;
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
			
			if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
			{
				$company = $request->session()->get('company_candAll_filter_inner_list');
				 $selectedFilter['Company'] = $company;
				 if($whereraw == '')
				{
					$whereraw = 'company_visa = "'.$company.'"';
				}
				else
				{
					$whereraw .= ' And company_visa = "'.$company.'"';
				}
			}
			//echo $cname;exit;
			if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
			{
				$email = $request->session()->get('email_candAll_filter_inner_list');
				 $selectedFilter['CEMAIL'] = $email;
				 if($whereraw == '')
				{
					$whereraw = 'email = "'.$email.'"';
				}
				else
				{
					$whereraw .= ' And email = "'.$email.'"';
				}
			}
			if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
			{
				$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
				 if($whereraw == '')
				{
					$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
				}
			}
			if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
			{
				$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
				 if($whereraw == '')
				{
					$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
				}
			}
			


			
			
			if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
			{
				$dept = $request->session()->get('dept_candAll_filter_inner_list');
				 $selectedFilter['DEPT'] = $dept;
				 if($whereraw == '')
				{
					$whereraw = 'department = "'.$dept.'"';
				}
				else
				{
					$whereraw .= ' And department = "'.$dept.'"';
				}
			}
			if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
			{
				$opening = $request->session()->get('opening_cand_filter_inner_list');
				 $selectedFilter['OPENING'] = $opening;
				 if($whereraw == '')
				{
					$whereraw = 'job_opening IN('.$opening.')';
				}
				else
				{
					$whereraw .= ' And job_opening IN('.$opening.')';
				}
			}
			if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
			{
				$status = $request->session()->get('status_candAll_filter_inner_list');
				 $selectedFilter['STATUS'] = $status;
				 if($whereraw == '')
				{
					$whereraw = 'status = "'.$status.'"';
				}
				else
				{
					$whereraw .= ' And status = "'.$status.'"';
				}
			}
			//echo $whereraw;exit;
			if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
			{
				$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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
		
			
			
		
			
			
			if($whereraw != '')
			{
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				if($departmentDetails != '')
				{
					$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					if($empDetails!='')
					{
						$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
						->select('passport.id as rowid','passport.*','employee_details.*')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->whereRaw($whereraw)
						->where('passport.passport_status',0)
						// ->where('finalstatus',0)
						// ->orWhere('finalstatus', '>', 0)
						->orderBy('passport.updated_at','desc')->paginate($paginationValue);
		
						$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
						->where('employee_details.dept_id',$empDetails->dept_id)
						->whereRaw($whereraw)
						->where('passport.passport_status',0)
						// ->where('finalstatus',0)
						// ->orWhere('finalstatus', '>', 0)
						->orderBy('passport.id','desc')
						->get()->count();
					}
				}
				else{
					$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
					->select('passport.id as rowid','passport.*','employee_details.*')
	
					->whereRaw($whereraw)
					->where('passport.passport_status',0)
					// ->where('finalstatus',0)
					// ->orWhere('finalstatus', '>', 0)
					->orderBy('passport.updated_at','desc')->paginate($paginationValue);
	
					$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
					->whereRaw($whereraw)
					->where('passport.passport_status',0)
					// ->where('finalstatus',0)
					// ->orWhere('finalstatus', '>', 0)
					->orderBy('passport.id','desc')
					->get()->count();
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
					->select('passport.id as rowid','passport.*')
					->where('employee_details.dept_id',$empDetails->dept_id)
					->where('passport.passport_status',0)
					//->where('finalstatus',0)->orWhere('finalstatus', '>', 0)
					->orderBy('passport.updated_at','desc')->paginate($paginationValue);
	
					$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->where('passport.passport_status',0)
					->where('employee_details.dept_id',$empDetails->dept_id)
					->orderBy('passport.id','desc')->get()->count();
					}
				}
				else{
					$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
					->select('passport.id as rowid','passport.*')
	
					->where('passport.passport_status',0)
					//->where('finalstatus',0)->orWhere('finalstatus', '>', 0)
					->orderBy('passport.updated_at','desc')->paginate($paginationValue);
	
					$reportsCount = Passport::where('passport.passport_status',0)
					//->where('finalstatus',0)->orWhere('finalstatus', '>', 0)
					->orderBy('id','desc')->get()->count();
				}

				
				//echo "hello1";
				



				
			}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
				$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
				$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
			
			$passportDetails->setPath(config('app.url/listingPassportsNotAvailable'));
			
			//print_r($documentCollectiondetails);exit;
	

		return view("Passport/listingPassportsNotAvailable",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','paginationValue'));
	}

	public function listingReleasedPassportsData(Request $request)
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
			
			//$request->session()->put('cname_empAll_filter_inner_list','');
			if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
		  	{
			  $departmentID = $request->session()->get('onboarding_department_filter');
			  //$whereraw .= 'department = "'.$departmentID.'"';
		  	}
		
			if(!empty($request->session()->get('passport_page_limit')))
			{
				$paginationValue = $request->session()->get('passport_page_limit');
			}
			else
			{
				$paginationValue = 100;
			}
			
			
			if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
			{
				$retained = $request->session()->get('offboardall_retained_filter_inner_list');
				 if($whereraw == '')
				{
					$whereraw = 'retain = "'.$retained.'"';
				}
				else
				{
					$whereraw .= ' And retain = "'.$retained.'"';
				}
			}
			
			if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
			{
				$exittype = $request->session()->get('offboardall_filter_inner_list');
				 if($whereraw == '')
				{
					$whereraw = 'leaving_type = "'.$exittype.'"';
				}
				else
				{
					$whereraw .= ' And leaving_type = "'.$exittype.'"';
				}
			}
			
			
			
			//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
			
			if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
			{
				$datefrom = $request->session()->get('passport_fromdate');
				 if($whereraw == '')
				{
					$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
			{
				$dateto = $request->session()->get('passport_todate');
				 if($whereraw == '')
				{
					$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
			{
				$dept = $request->session()->get('passport_department');
				 //$departmentArray = explode(",",$dept);
				if($whereraw == '')
				{
					$whereraw = 'employee_details.dept_id IN('.$dept.')';
				}
				else
				{
					$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
				}
			}
			if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
			{
				$teamlead = $request->session()->get('passport_teamleader');
				 //$departmentArray = explode(",",$dept);
				if($whereraw == '')
				{
					$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
					
				}
				else
				{
					$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
				}
			}
			if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
			{
				$empId = $request->session()->get('passport_emp_id');
				 if($whereraw == '')
				{
					$whereraw = 'passport.emp_id IN ('.$empId.')';
				}
				else
				{
					$whereraw .= ' And passport.emp_id IN ('.$empId.')';
				}
			}







			if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
				{
					$designd = $request->session()->get('passport_designation');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
					}
				}



			// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
			// {
			// 	$rangeid = $request->session()->get('range_filter_inner_list');
			// 	 if($whereraw == '')
			// 	{
			// 		$whereraw = 'range_id IN ('.$rangeid.')';
			// 	}
			// 	else
			// 	{
			// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
			// 	}
			// }










			if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
			{
				$fname = $request->session()->get('passport_emp_name');
				 $cnameArray = explode(",",$fname);
				 
				 $namefinalarray=array();
				 foreach($cnameArray as $namearray){
					 $namefinalarray[]="'".$namearray."'";					 
				 }
				 //print_r($namefinalarray);exit;
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
			
			if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
			{
				$company = $request->session()->get('company_candAll_filter_inner_list');
				 $selectedFilter['Company'] = $company;
				 if($whereraw == '')
				{
					$whereraw = 'company_visa = "'.$company.'"';
				}
				else
				{
					$whereraw .= ' And company_visa = "'.$company.'"';
				}
			}
			//echo $cname;exit;
			if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
			{
				$email = $request->session()->get('email_candAll_filter_inner_list');
				 $selectedFilter['CEMAIL'] = $email;
				 if($whereraw == '')
				{
					$whereraw = 'email = "'.$email.'"';
				}
				else
				{
					$whereraw .= ' And email = "'.$email.'"';
				}
			}
			if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
			{
				$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
				 if($whereraw == '')
				{
					$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
				}
			}
			if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
			{
				$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
				 if($whereraw == '')
				{
					$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
				}
			}
			


			
			
			if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
			{
				$dept = $request->session()->get('dept_candAll_filter_inner_list');
				 $selectedFilter['DEPT'] = $dept;
				 if($whereraw == '')
				{
					$whereraw = 'department = "'.$dept.'"';
				}
				else
				{
					$whereraw .= ' And department = "'.$dept.'"';
				}
			}
			if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
			{
				$opening = $request->session()->get('opening_cand_filter_inner_list');
				 $selectedFilter['OPENING'] = $opening;
				 if($whereraw == '')
				{
					$whereraw = 'job_opening IN('.$opening.')';
				}
				else
				{
					$whereraw .= ' And job_opening IN('.$opening.')';
				}
			}
			if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
			{
				$status = $request->session()->get('status_candAll_filter_inner_list');
				 $selectedFilter['STATUS'] = $status;
				 if($whereraw == '')
				{
					$whereraw = 'status = "'.$status.'"';
				}
				else
				{
					$whereraw .= ' And status = "'.$status.'"';
				}
			}
			//echo $whereraw;exit;
			if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
			{
				$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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
			
			
			
			
			
			
			// if($whereraw != '')
			// {
			// 	//echo "hello";
			// 	$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
			// 	->whereRaw($whereraw)
			// 	->where('release_request_status',1)
			// 	->where('finalstatus',0)
			// 	->orderBy('passport.id','desc')->paginate($paginationValue);

			// 	$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
			// 	->whereRaw($whereraw)
			// 	->where('release_request_status',1)
			// 	->where('finalstatus',0)
			// 	->orderBy('passport.id','desc')
			// 	->get()->count();

			// }
			// else
			// {
			// 	//echo "hello1";
			// 	$passportDetails = Passport::where('release_request_status',1)->where('finalstatus',0)->orderBy('id','desc')->paginate($paginationValue);

			// 	$reportsCount = Passport::where('release_request_status',1)->where('finalstatus',0)->orderBy('id','desc')->get()->count();
			// }



		





			if($whereraw != '')
			{
				
			
				


					$empsessionId=$request->session()->get('EmployeeId');
					$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
					if($departmentDetails != '')
					{
						$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
						if($empDetails!='')
						{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->select('passport.id as rowid','passport.*','employee_details.*')
			
							->whereRaw($whereraw)
							->where('passport.release_list_status',1)
							->where('release_request_status',0)
							->where('employee_details.dept_id',$empDetails->dept_id)
							->orderBy('passport.updated_at','desc')->paginate($paginationValue);
			
							$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->whereRaw($whereraw)
							->where('passport.release_list_status',1)
							->where('release_request_status',0)
							->where('employee_details.dept_id',$empDetails->dept_id)
							->orderBy('passport.updated_at','desc')
							->get()->count();
						}
					}
					else
					{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->select('passport.id as rowid','passport.*','employee_details.*')
			
							->whereRaw($whereraw)
							->where('passport.release_list_status',1)
							->where('release_request_status',0)
							->orderBy('passport.updated_at','desc')->paginate($paginationValue);
			
							$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->whereRaw($whereraw)
							->where('passport.release_list_status',1)
							->where('release_request_status',0)
							->orderBy('passport.updated_at','desc')
							->get()->count();
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
						->select('passport.id as rowid','passport.*')
		
						->where('release_list_status',1)
						->where('release_request_status',0)
						->where('employee_details.dept_id',$empDetails->dept_id)
						->orderBy('passport.updated_at','desc')->paginate($paginationValue);
		
						// $reportsCount = Passport::where('passport.passport_status',1)
						// //->where('release_request_status',0)
						// ->orderBy('passport.id','desc')->get()->count();
		
		
						$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')->where('release_list_status',1)
						->where('release_request_status',0)
						->where('employee_details.dept_id',$empDetails->dept_id)
						->orderBy('passport.id','desc')->get()->count();
					}
				}
				else{
					$passportDetails = Passport::				
					select('passport.id as rowid','passport.*')
	
					->where('release_list_status',1)
					->where('release_request_status',0)
					->orderBy('passport.updated_at','desc')->paginate($paginationValue);
	
					// $reportsCount = Passport::where('passport.passport_status',1)
					// //->where('release_request_status',0)
					// ->orderBy('passport.id','desc')->get()->count();
	
	
					$reportsCount = Passport::where('release_list_status',1)
					->where('release_request_status',0)
					->orderBy('passport.id','desc')->get()->count();
				}

				
				
				
				
				
				
				
				
				
				
				//echo "hello1";
				// $passportDetails = Passport::where('passport.passport_status',1)
				// //->where('release_request_status',0)
				// ->orderBy('passport.updated_at','desc')->paginate($paginationValue);
			
				



			}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
				$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
				$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
			
			$passportDetails->setPath(config('app.url/listingRealeasedPassports'));
			
			//print_r($documentCollectiondetails);exit;
	

		return view("Passport/listingRealeasedPassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','paginationValue'));

	}


	public function listingRequestedPassportsTabData(Request $request)
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
			
			//$request->session()->put('cname_empAll_filter_inner_list','');
			if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
		  	{
			  $departmentID = $request->session()->get('onboarding_department_filter');
			  //$whereraw .= 'department = "'.$departmentID.'"';
		  	}
		
			if(!empty($request->session()->get('passport_page_limit')))
			{
				$paginationValue = $request->session()->get('passport_page_limit');
			}
			else
			{
				$paginationValue = 100;
			}
			
			
			if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
			{
				$retained = $request->session()->get('offboardall_retained_filter_inner_list');
				 if($whereraw == '')
				{
					$whereraw = 'retain = "'.$retained.'"';
				}
				else
				{
					$whereraw .= ' And retain = "'.$retained.'"';
				}
			}
			
			if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
			{
				$exittype = $request->session()->get('offboardall_filter_inner_list');
				 if($whereraw == '')
				{
					$whereraw = 'leaving_type = "'.$exittype.'"';
				}
				else
				{
					$whereraw .= ' And leaving_type = "'.$exittype.'"';
				}
			}
			
			
			
			//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
			
			if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
			{
				$datefrom = $request->session()->get('passport_fromdate');
				 if($whereraw == '')
				{
					$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
			{
				$dateto = $request->session()->get('passport_todate');
				 if($whereraw == '')
				{
					$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
				}
				else
				{
					$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
				}
			}
			if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
			{
				$dept = $request->session()->get('passport_department');
				 //$departmentArray = explode(",",$dept);
				if($whereraw == '')
				{
					$whereraw = 'employee_details.dept_id IN('.$dept.')';
				}
				else
				{
					$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
				}
			}
			if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
			{
				$teamlead = $request->session()->get('passport_teamleader');
				 //$departmentArray = explode(",",$dept);
				if($whereraw == '')
				{
					$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
					
				}
				else
				{
					$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
				}
			}
			if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
			{
				$empId = $request->session()->get('passport_emp_id');
				 if($whereraw == '')
				{
					$whereraw = 'passport.emp_id IN ('.$empId.')';
				}
				else
				{
					$whereraw .= ' And passport.emp_id IN ('.$empId.')';
				}
			}







			if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
				{
					$designd = $request->session()->get('passport_designation');
					 //$departmentArray = explode(",",$designd);
					if($whereraw == '')
					{
						$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
					}
					else
					{
						$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
					}
				}



			// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
			// {
			// 	$rangeid = $request->session()->get('range_filter_inner_list');
			// 	 if($whereraw == '')
			// 	{
			// 		$whereraw = 'range_id IN ('.$rangeid.')';
			// 	}
			// 	else
			// 	{
			// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
			// 	}
			// }










			if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
			{
				$fname = $request->session()->get('passport_emp_name');
				 $cnameArray = explode(",",$fname);
				 
				 $namefinalarray=array();
				 foreach($cnameArray as $namearray){
					 $namefinalarray[]="'".$namearray."'";					 
				 }
				 //print_r($namefinalarray);exit;
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
			
			if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
			{
				$company = $request->session()->get('company_candAll_filter_inner_list');
				 $selectedFilter['Company'] = $company;
				 if($whereraw == '')
				{
					$whereraw = 'company_visa = "'.$company.'"';
				}
				else
				{
					$whereraw .= ' And company_visa = "'.$company.'"';
				}
			}
			//echo $cname;exit;
			if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
			{
				$email = $request->session()->get('email_candAll_filter_inner_list');
				 $selectedFilter['CEMAIL'] = $email;
				 if($whereraw == '')
				{
					$whereraw = 'email = "'.$email.'"';
				}
				else
				{
					$whereraw .= ' And email = "'.$email.'"';
				}
			}
			if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
			{
				$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
				 if($whereraw == '')
				{
					$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
				}
			}
			if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
			{
				$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
				 if($whereraw == '')
				{
					$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
				}
				else
				{
					$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
				}
			}
			


			
			
			if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
			{
				$dept = $request->session()->get('dept_candAll_filter_inner_list');
				 $selectedFilter['DEPT'] = $dept;
				 if($whereraw == '')
				{
					$whereraw = 'department = "'.$dept.'"';
				}
				else
				{
					$whereraw .= ' And department = "'.$dept.'"';
				}
			}
			if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
			{
				$opening = $request->session()->get('opening_cand_filter_inner_list');
				 $selectedFilter['OPENING'] = $opening;
				 if($whereraw == '')
				{
					$whereraw = 'job_opening IN('.$opening.')';
				}
				else
				{
					$whereraw .= ' And job_opening IN('.$opening.')';
				}
			}
			if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
			{
				$status = $request->session()->get('status_candAll_filter_inner_list');
				 $selectedFilter['STATUS'] = $status;
				 if($whereraw == '')
				{
					$whereraw = 'status = "'.$status.'"';
				}
				else
				{
					$whereraw .= ' And status = "'.$status.'"';
				}
			}
			//echo $whereraw;exit;
			if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
			{
				$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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
			
			
			
			
			
			if($whereraw != '')
			{
				
				
				
				$empsessionId=$request->session()->get('EmployeeId');
				$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
				if($departmentDetails != '')
				{
					$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
					if($empDetails!='')
					{
						$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
					->select('passport.id as rowid','passport.*','employee_details.*')
					->where('employee_details.dept_id',$empDetails->dept_id)
					->whereRaw($whereraw)
					//->where('requestpassport.passport_status',0)
					->where('passport.passport_status',0)
					->where('release_list_status',0)
	
					->orderBy('passport.updated_at','desc')->paginate($paginationValue);
	
					$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
					->where('employee_details.dept_id',$empDetails->dept_id)
					->whereRaw($whereraw)
					//->where('requestpassport_status',0)
					->where('passport.passport_status',0)
					->where('release_list_status',0)
	
					->orderBy('passport.id','desc')
					->get()->count();
					}
				}
				else{
					$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
					->select('passport.id as rowid','passport.*','employee_details.*')
	
					->whereRaw($whereraw)
					//->where('requestpassport.passport_status',0)
					->where('passport.passport_status',0)
					->where('release_list_status',0)
	
					->orderBy('passport.updated_at','desc')->paginate($paginationValue);
	
					$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
					->whereRaw($whereraw)
					//->where('requestpassport.passport_status',0)
					->where('passport.passport_status',0)
					->where('release_list_status',0)
	
					->orderBy('passport.id','desc')
					->get()->count();
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
					//where('requestpassport.passport_status',0)
					->select('passport.id as rowid','passport.*')
					->where('employee_details.dept_id',$empDetails->dept_id)
	
					->where('passport.passport_status',0)
					->where('release_list_status',0)
					->orderBy('passport.updated_at','desc')->paginate($paginationValue);
	
					$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
					//where('requestpassport.passport_status',0)
					->where('employee_details.dept_id',$empDetails->dept_id)
					->where('passport.passport_status',0)
					->where('release_list_status',0)
	
					->orderBy('passport.id','desc')->get()->count();
					}
				}
				else{
					$passportDetails = Passport::
					//where('requestpassport.passport_status',0)
					select('passport.id as rowid','passport.*')
	
					->where('passport.passport_status',0)
					->where('release_list_status',0)
					->orderBy('passport.updated_at','desc')->paginate($paginationValue);
	
					$reportsCount = Passport::
					//where('requestpassport.passport_status',0)
					where('passport.passport_status',0)
					->where('release_list_status',0)
	
					->orderBy('id','desc')->get()->count();
				}

				
				
				
				
				
				
				
				
				
				
			}
				$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
				$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
				$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
			
			$passportDetails->setPath(config('app.url/listingRequestedPassports'));
			
			//print_r($documentCollectiondetails);exit;
	

		return view("Passport/listingRequestedPassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','paginationValue'));

	}


	public function listingFinalPassportsTabData(Request $request)
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
					
					//$request->session()->put('cname_empAll_filter_inner_list','');
					if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
					  {
					  $departmentID = $request->session()->get('onboarding_department_filter');
					  //$whereraw .= 'department = "'.$departmentID.'"';
					  }
				
					if(!empty($request->session()->get('passport_page_limit')))
					{
						$paginationValue = $request->session()->get('passport_page_limit');
					}
					else
					{
						$paginationValue = 100;
					}
					
					
					if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
					{
						$retained = $request->session()->get('offboardall_retained_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'retain = "'.$retained.'"';
						}
						else
						{
							$whereraw .= ' And retain = "'.$retained.'"';
						}
					}
					
					if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
					{
						$exittype = $request->session()->get('offboardall_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'leaving_type = "'.$exittype.'"';
						}
						else
						{
							$whereraw .= ' And leaving_type = "'.$exittype.'"';
						}
					}
					
					
					
					//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
					
					if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
					{
						$datefrom = $request->session()->get('passport_fromdate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
					{
						$dateto = $request->session()->get('passport_todate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
					{
						$dept = $request->session()->get('passport_department');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.dept_id IN('.$dept.')';
						}
						else
						{
							$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
						}
					}
					if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
					{
						$teamlead = $request->session()->get('passport_teamleader');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
							
						}
						else
						{
							$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
						}
					}
					if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
					{
						$empId = $request->session()->get('passport_emp_id');
						 if($whereraw == '')
						{
							$whereraw = 'passport.emp_id IN ('.$empId.')';
						}
						else
						{
							$whereraw .= ' And passport.emp_id IN ('.$empId.')';
						}
					}
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
						{
							$designd = $request->session()->get('passport_designation');
							 //$departmentArray = explode(",",$designd);
							if($whereraw == '')
							{
								$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
							}
							else
							{
								$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
							}
						}
		
		
		
					// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
					// {
					// 	$rangeid = $request->session()->get('range_filter_inner_list');
					// 	 if($whereraw == '')
					// 	{
					// 		$whereraw = 'range_id IN ('.$rangeid.')';
					// 	}
					// 	else
					// 	{
					// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
					// 	}
					// }
		
		
		
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
					{
						$fname = $request->session()->get('passport_emp_name');
						 $cnameArray = explode(",",$fname);
						 
						 $namefinalarray=array();
						 foreach($cnameArray as $namearray){
							 $namefinalarray[]="'".$namearray."'";					 
						 }
						 //print_r($namefinalarray);exit;
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
					
					if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
					{
						$company = $request->session()->get('company_candAll_filter_inner_list');
						 $selectedFilter['Company'] = $company;
						 if($whereraw == '')
						{
							$whereraw = 'company_visa = "'.$company.'"';
						}
						else
						{
							$whereraw .= ' And company_visa = "'.$company.'"';
						}
					}
					//echo $cname;exit;
					if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
					{
						$email = $request->session()->get('email_candAll_filter_inner_list');
						 $selectedFilter['CEMAIL'] = $email;
						 if($whereraw == '')
						{
							$whereraw = 'email = "'.$email.'"';
						}
						else
						{
							$whereraw .= ' And email = "'.$email.'"';
						}
					}
					if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
					{
						$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
						}
					}
					if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
					{
						$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
					}
					
		
		
					
					
					if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
					{
						$dept = $request->session()->get('dept_candAll_filter_inner_list');
						 $selectedFilter['DEPT'] = $dept;
						 if($whereraw == '')
						{
							$whereraw = 'department = "'.$dept.'"';
						}
						else
						{
							$whereraw .= ' And department = "'.$dept.'"';
						}
					}
					if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
					{
						$opening = $request->session()->get('opening_cand_filter_inner_list');
						 $selectedFilter['OPENING'] = $opening;
						 if($whereraw == '')
						{
							$whereraw = 'job_opening IN('.$opening.')';
						}
						else
						{
							$whereraw .= ' And job_opening IN('.$opening.')';
						}
					}
					if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
					{
						$status = $request->session()->get('status_candAll_filter_inner_list');
						 $selectedFilter['STATUS'] = $status;
						 if($whereraw == '')
						{
							$whereraw = 'status = "'.$status.'"';
						}
						else
						{
							$whereraw .= ' And status = "'.$status.'"';
						}
					}
					//echo $whereraw;exit;
					if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
					{
						$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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
					
					
					
					
					
					if($whereraw != '')
					{
						//echo "hello";
						$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
						->whereRaw($whereraw)
						//->where('requestpassport_status',1)
						->where('finalstatus','>',0)
						->orderBy('passport.updated_at','desc')->paginate($paginationValue);
		
						$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
						->whereRaw($whereraw)
						//->where('requestpassport.passport_status',1)
						->where('finalstatus','>',0)
						->orderBy('passport.id','desc')
						->get()->count();
					}
					else
					{
						//echo "hello1";
						$passportDetails = Passport::where('finalstatus','>',0)->orderBy('id','desc')->paginate($paginationValue);		
						$reportsCount = Passport::where('finalstatus','>',0)->orderBy('id','desc')->get()->count();
					}
						$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
						$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
						$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
					
					$passportDetails->setPath(config('app.url/listingFinalPassports'));
					
					//print_r($documentCollectiondetails);exit;
			
		
				return view("Passport/listingFinalPassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','paginationValue'));
	}




	public function availableVisaCompletedData(Request $request)
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
					
					//$request->session()->put('cname_empAll_filter_inner_list','');
					if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
					  {
					  $departmentID = $request->session()->get('onboarding_department_filter');
					  //$whereraw .= 'department = "'.$departmentID.'"';
					  }
				
					if(!empty($request->session()->get('passport_page_limit')))
					{
						$paginationValue = $request->session()->get('passport_page_limit');
					}
					else
					{
						$paginationValue = 100;
					}
					
					
					if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
					{
						$retained = $request->session()->get('offboardall_retained_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'retain = "'.$retained.'"';
						}
						else
						{
							$whereraw .= ' And retain = "'.$retained.'"';
						}
					}
					
					if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
					{
						$exittype = $request->session()->get('offboardall_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'leaving_type = "'.$exittype.'"';
						}
						else
						{
							$whereraw .= ' And leaving_type = "'.$exittype.'"';
						}
					}
					
					
					
					//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
					
					if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
					{
						$datefrom = $request->session()->get('passport_fromdate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
					{
						$dateto = $request->session()->get('passport_todate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
					{
						$dept = $request->session()->get('passport_department');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.dept_id IN('.$dept.')';
						}
						else
						{
							$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
						}
					}
					if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
					{
						$teamlead = $request->session()->get('passport_teamleader');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
							
						}
						else
						{
							$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
						}
					}
					if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
					{
						$empId = $request->session()->get('passport_emp_id');
						 if($whereraw == '')
						{
							$whereraw = 'passport.emp_id IN ('.$empId.')';
						}
						else
						{
							$whereraw .= ' And passport.emp_id IN ('.$empId.')';
						}
					}
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
						{
							$designd = $request->session()->get('passport_designation');
							 //$departmentArray = explode(",",$designd);
							if($whereraw == '')
							{
								$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
							}
							else
							{
								$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
							}
						}
		
		
		
					// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
					// {
					// 	$rangeid = $request->session()->get('range_filter_inner_list');
					// 	 if($whereraw == '')
					// 	{
					// 		$whereraw = 'range_id IN ('.$rangeid.')';
					// 	}
					// 	else
					// 	{
					// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
					// 	}
					// }
		
		
		
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
					{
						$fname = $request->session()->get('passport_emp_name');
						 $cnameArray = explode(",",$fname);
						 
						 $namefinalarray=array();
						 foreach($cnameArray as $namearray){
							 $namefinalarray[]="'".$namearray."'";					 
						 }
						 //print_r($namefinalarray);exit;
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
					
					if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
					{
						$company = $request->session()->get('company_candAll_filter_inner_list');
						 $selectedFilter['Company'] = $company;
						 if($whereraw == '')
						{
							$whereraw = 'company_visa = "'.$company.'"';
						}
						else
						{
							$whereraw .= ' And company_visa = "'.$company.'"';
						}
					}
					//echo $cname;exit;
					if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
					{
						$email = $request->session()->get('email_candAll_filter_inner_list');
						 $selectedFilter['CEMAIL'] = $email;
						 if($whereraw == '')
						{
							$whereraw = 'email = "'.$email.'"';
						}
						else
						{
							$whereraw .= ' And email = "'.$email.'"';
						}
					}
					if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
					{
						$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
						}
					}
					if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
					{
						$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
					}
					
		
		
					
					
					if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
					{
						$dept = $request->session()->get('dept_candAll_filter_inner_list');
						 $selectedFilter['DEPT'] = $dept;
						 if($whereraw == '')
						{
							$whereraw = 'department = "'.$dept.'"';
						}
						else
						{
							$whereraw .= ' And department = "'.$dept.'"';
						}
					}
					if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
					{
						$opening = $request->session()->get('opening_cand_filter_inner_list');
						 $selectedFilter['OPENING'] = $opening;
						 if($whereraw == '')
						{
							$whereraw = 'job_opening IN('.$opening.')';
						}
						else
						{
							$whereraw .= ' And job_opening IN('.$opening.')';
						}
					}
					if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
					{
						$status = $request->session()->get('status_candAll_filter_inner_list');
						 $selectedFilter['STATUS'] = $status;
						 if($whereraw == '')
						{
							$whereraw = 'status = "'.$status.'"';
						}
						else
						{
							$whereraw .= ' And status = "'.$status.'"';
						}
					}
					//echo $whereraw;exit;
					if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
					{
						$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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
					
					
					
					
					
					if($whereraw != '')
					{
						
						
						$empsessionId=$request->session()->get('EmployeeId');
						$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
						if($departmentDetails != '')
						{
							$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
							if($empDetails!='')
							{
								$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

								->whereRaw($whereraw)

								->where('passport.passport_status',1)
								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)	
								->orWhereNotNull('employee_details.document_collection_id')
								->whereRaw($whereraw)

								->where('passport.passport_status',1)

								->where('document_collection_details.visa_process_status',4)
								->where('employee_details.dept_id',$empDetails->dept_id)				
								->orderBy('passport.updated_at','desc')
								->paginate($paginationValue);
				
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->whereRaw($whereraw)

								->where('passport.passport_status',1)
								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)	
								->orWhereNotNull('employee_details.document_collection_id')
								->whereRaw($whereraw)

								->where('passport.passport_status',1)

								->where('document_collection_details.visa_process_status',4)
								->where('employee_details.dept_id',$empDetails->dept_id)				
								->orderBy('passport.id','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

								->whereRaw($whereraw)

								->where('passport.passport_status',1)
								->whereNull('employee_details.document_collection_id')
								
								->orWhereNotNull('employee_details.document_collection_id')
								->whereRaw($whereraw)

								->where('passport.passport_status',1)

								->where('document_collection_details.visa_process_status',4)
											
								->orderBy('passport.updated_at','desc')
								->paginate($paginationValue);
				
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->whereRaw($whereraw)

								->where('passport.passport_status',1)
								->whereNull('employee_details.document_collection_id')
								
								->orWhereNotNull('employee_details.document_collection_id')
								->whereRaw($whereraw)

								->where('passport.passport_status',1)

								->where('document_collection_details.visa_process_status',4)
												
								->orderBy('passport.id','desc')
								->get()->count();
						}
						
						
						
						
						
						
						
						
						//echo "hello"; exit;
						
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
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

								->where('passport.passport_status',1)
								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)
								//->where('employee_details.document_collection_id','!=','')	
								->orWhereNotNull('employee_details.document_collection_id')
								->where('passport.passport_status',1)
								->where('document_collection_details.visa_process_status',4)
								->where('employee_details.dept_id',$empDetails->dept_id)				
								->orderBy('passport.updated_at','desc')
								->paginate($paginationValue);
								//->get();


								//return $passportDetails;
								
								//->paginate($paginationValue);
								//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->where('passport.passport_status',1)
								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)
								//->where('employee_details.document_collection_id','!=','')	
								->orWhereNotNull('employee_details.document_collection_id')
								->where('passport.passport_status',1)

								->where('document_collection_details.visa_process_status',4)	
								->where('employee_details.dept_id',$empDetails->dept_id)			
								->orderBy('passport.id','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

							->where('passport.passport_status',1)
							->whereNull('employee_details.document_collection_id')

							//->where('employee_details.document_collection_id','!=','')	
							->orWhereNotNull('employee_details.document_collection_id')
							->where('passport.passport_status',1)
							->where('document_collection_details.visa_process_status',4)				
							->orderBy('passport.updated_at','desc')
							->paginate($paginationValue);
							//->get();


							//return $passportDetails;
							
							//->paginate($paginationValue);
							//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
							$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->where('passport.passport_status',1)
							->whereNull('employee_details.document_collection_id')

							//->where('employee_details.document_collection_id','!=','')	
							->orWhereNotNull('employee_details.document_collection_id')
							->where('passport.passport_status',1)

							->where('document_collection_details.visa_process_status',4)				
							->orderBy('passport.id','desc')
							->get()->count();
						}
						
						
						//echo "hello1";

						
					}
						$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
						$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
						$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
					
					$passportDetails->setPath(config('app.url/listingAvailableCompletePassports'));
					
					//print_r($documentCollectiondetails);exit;
			
		
				return view("Passport/listingAvailableCompletePassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','paginationValue'));
	}


	public function availableinProgressVisaData(Request $request)
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
					
					//$request->session()->put('cname_empAll_filter_inner_list','');
					if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
					  {
					  $departmentID = $request->session()->get('onboarding_department_filter');
					  //$whereraw .= 'department = "'.$departmentID.'"';
					  }
				
					if(!empty($request->session()->get('passport_page_limit')))
					{
						$paginationValue = $request->session()->get('passport_page_limit');
					}
					else
					{
						$paginationValue = 100;
					}
					
					
					if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
					{
						$retained = $request->session()->get('offboardall_retained_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'retain = "'.$retained.'"';
						}
						else
						{
							$whereraw .= ' And retain = "'.$retained.'"';
						}
					}
					
					if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
					{
						$exittype = $request->session()->get('offboardall_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'leaving_type = "'.$exittype.'"';
						}
						else
						{
							$whereraw .= ' And leaving_type = "'.$exittype.'"';
						}
					}
					
					
					
					//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
					
					if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
					{
						$datefrom = $request->session()->get('passport_fromdate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
					{
						$dateto = $request->session()->get('passport_todate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
					{
						$dept = $request->session()->get('passport_department');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.dept_id IN('.$dept.')';
						}
						else
						{
							$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
						}
					}
					if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
					{
						$teamlead = $request->session()->get('passport_teamleader');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
							
						}
						else
						{
							$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
						}
					}
					if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
					{
						$empId = $request->session()->get('passport_emp_id');
						 if($whereraw == '')
						{
							$whereraw = 'passport.emp_id IN ('.$empId.')';
						}
						else
						{
							$whereraw .= ' And passport.emp_id IN ('.$empId.')';
						}
					}
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
						{
							$designd = $request->session()->get('passport_designation');
							 //$departmentArray = explode(",",$designd);
							if($whereraw == '')
							{
								$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
							}
							else
							{
								$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
							}
						}
		
		
		
					// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
					// {
					// 	$rangeid = $request->session()->get('range_filter_inner_list');
					// 	 if($whereraw == '')
					// 	{
					// 		$whereraw = 'range_id IN ('.$rangeid.')';
					// 	}
					// 	else
					// 	{
					// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
					// 	}
					// }
		
		
		
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
					{
						$fname = $request->session()->get('passport_emp_name');
						 $cnameArray = explode(",",$fname);
						 
						 $namefinalarray=array();
						 foreach($cnameArray as $namearray){
							 $namefinalarray[]="'".$namearray."'";					 
						 }
						 //print_r($namefinalarray);exit;
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
					
					if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
					{
						$company = $request->session()->get('company_candAll_filter_inner_list');
						 $selectedFilter['Company'] = $company;
						 if($whereraw == '')
						{
							$whereraw = 'company_visa = "'.$company.'"';
						}
						else
						{
							$whereraw .= ' And company_visa = "'.$company.'"';
						}
					}
					//echo $cname;exit;
					if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
					{
						$email = $request->session()->get('email_candAll_filter_inner_list');
						 $selectedFilter['CEMAIL'] = $email;
						 if($whereraw == '')
						{
							$whereraw = 'email = "'.$email.'"';
						}
						else
						{
							$whereraw .= ' And email = "'.$email.'"';
						}
					}
					if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
					{
						$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
						}
					}
					if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
					{
						$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
					}
					
		
		
					
					
					if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
					{
						$dept = $request->session()->get('dept_candAll_filter_inner_list');
						 $selectedFilter['DEPT'] = $dept;
						 if($whereraw == '')
						{
							$whereraw = 'department = "'.$dept.'"';
						}
						else
						{
							$whereraw .= ' And department = "'.$dept.'"';
						}
					}
					if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
					{
						$opening = $request->session()->get('opening_cand_filter_inner_list');
						 $selectedFilter['OPENING'] = $opening;
						 if($whereraw == '')
						{
							$whereraw = 'job_opening IN('.$opening.')';
						}
						else
						{
							$whereraw .= ' And job_opening IN('.$opening.')';
						}
					}
					if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
					{
						$status = $request->session()->get('status_candAll_filter_inner_list');
						 $selectedFilter['STATUS'] = $status;
						 if($whereraw == '')
						{
							$whereraw = 'status = "'.$status.'"';
						}
						else
						{
							$whereraw .= ' And status = "'.$status.'"';
						}
					}
					//echo $whereraw;exit;
					if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
					{
						$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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










					// inner filter start

					
					if(!empty($request->session()->get('visastatus_filter_inner_list_innerTbl')) && $request->session()->get('visastatus_filter_inner_list_innerTbl') != 'All')
					{
						$visastatus = $request->session()->get('visastatus_filter_inner_list_innerTbl');
						$visastatusArray = explode(",",$visastatus);
						//echo "<pre>";
						//print_r($departmentArray);

						$visaarr = array();
						foreach($visastatusArray as $value)
						{
							if($value==1)
							{
								$value='Stage1';
							}
							if($value==2)
							{
								$value='Stage2';
							}
							$visaarr[]=$value;
						}
						
						$visaDetails = DocumentCollectionDetails::whereIn("visa_stage_steps",$visaarr)->orderBy('id','desc')->get();
						$documentidarr = array();

						foreach($visaDetails as $value)
						{
							$documentidarr[] = $value->id;
						}

						$documentidList = implode(', ', $documentidarr); 

						if($whereraw == '')
						{
							$whereraw = 'employee_details.document_collection_id IN('.$documentidList.')';
						}
						else
						{
							$whereraw .= ' And employee_details.document_collection_id IN('.$documentidList.')';
						}

					}





					if(!empty($request->session()->get('visastages_filter_inner_list_innerTbl')) && $request->session()->get('visastages_filter_inner_list_innerTbl') != 'All')
					{
						$visaStages = $request->session()->get('visastages_filter_inner_list_innerTbl');
						$visaStagesArray = explode(",",$visaStages);
						//echo "<pre>";
						//print_r($visaStagesArray);

						$docidarr =array();
						$vtypearr = array();
						foreach($visaStagesArray as $visastageid)
						{
							//echo $visastageid;

							$vsids = explode("-",$visastageid);



							$visaprocessDetails = Visaprocess::where("visa_stage",$vsids[0])->where('visa_type',$vsids[1])->orderBy('id','desc')->first();
							//return $visaprocessDetails;

							if($visaprocessDetails)
							{
								$docidarr[]=$visaprocessDetails->visa_stage;
								$vtypearr[]=$visaprocessDetails->visa_type;

							}
							else{
								$docidarr[]=0;
								$vtypearr[]=0;


							}


						}
						//print_r($docidarr);
						//print_r($vtypearr);

						//exit;













						//exit;

						// $visaarr = array();
						// foreach($visaStagesArray as $value)
						// {
						// 	if($value==1)
						// 	{
						// 		$value='Stage1';
						// 	}
						// 	if($value==2)
						// 	{
						// 		$value='Stage2';
						// 	}
						// 	$visaarr[]=$value;
						// }
						
						$visaDetails = Visaprocess::whereIn("visa_stage",$docidarr)->whereIn("visa_type",$vtypearr)->orderBy('id','desc')->get();
						//return $visaDetails;
						$documentidarr = array();

						foreach($visaDetails as $value)
						{
							$documentidarr[] = $value->document_id;
						}

						$documentidList = implode(', ', $documentidarr); 

						if($whereraw == '')
						{
							$whereraw = 'employee_details.document_collection_id IN('.$documentidList.')';
						}
						else
						{
							$whereraw .= ' And employee_details.document_collection_id IN('.$documentidList.')';
						}

					}


					// inner filter end
					
					
					
					
					
					if($whereraw != '')
					{
						
						$empsessionId=$request->session()->get('EmployeeId');
						$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
						if($departmentDetails != '')
						{
							$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
							if($empDetails!='')
							{
								$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

								->whereRaw($whereraw)
								->where('employee_details.dept_id',$empDetails->dept_id)
								->where('passport.passport_status',1)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)				
								->orderBy('passport.updated_at','desc')->paginate($paginationValue);
				
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->whereRaw($whereraw)
								->where('employee_details.dept_id',$empDetails->dept_id)
								->where('passport.passport_status',1)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)			
								->orderBy('passport.id','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

							->whereRaw($whereraw)
							->where('passport.passport_status',1)
							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)				
							->orderBy('passport.updated_at','desc')->paginate($paginationValue);
			
							$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->whereRaw($whereraw)
							->where('passport.passport_status',1)
							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)			
							->orderBy('passport.id','desc')
							->get()->count();
						}
						
						
						
						
						
						
						
						//echo "hello";
						
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
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
								->where('employee_details.dept_id',$empDetails->dept_id)
								->where('passport.passport_status',1)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)				
								->orderBy('passport.updated_at','desc')
								->paginate($paginationValue);
								//->get();


								//return $passportDetails;
								
								//->paginate($paginationValue);
								//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->where('employee_details.dept_id',$empDetails->dept_id)
								->where('passport.passport_status',1)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)				
								->orderBy('passport.id','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

							->where('passport.passport_status',1)
							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)				
							->orderBy('passport.updated_at','desc')
							->paginate($paginationValue);
							//->get();


							//return $passportDetails;
							
							//->paginate($paginationValue);
							//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
							$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->where('passport.passport_status',1)
							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)				
							->orderBy('passport.id','desc')
							->get()->count();
						}
						
						
						
						//echo "hello1";

						
					}
						$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
						$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
						$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();

						$visaStages = VisaStage::orderBy('id','asc')->get();

						//return $visaStages;

					
					$passportDetails->setPath(config('app.url/listingAvailableinProgressPassports'));
					
					//print_r($documentCollectiondetails);exit;
			
		
				return view("Passport/listingAvailableinProgressPassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','paginationValue','visaStages'));
	}





	public function NotavailableVisaCompletedData(Request $request)
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
					
					//$request->session()->put('cname_empAll_filter_inner_list','');
					if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
					  {
					  $departmentID = $request->session()->get('onboarding_department_filter');
					  //$whereraw .= 'department = "'.$departmentID.'"';
					  }
				
					if(!empty($request->session()->get('passport_page_limit')))
					{
						$paginationValue = $request->session()->get('passport_page_limit');
					}
					else
					{
						$paginationValue = 100;
					}
					
					
					if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
					{
						$retained = $request->session()->get('offboardall_retained_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'retain = "'.$retained.'"';
						}
						else
						{
							$whereraw .= ' And retain = "'.$retained.'"';
						}
					}
					
					if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
					{
						$exittype = $request->session()->get('offboardall_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'leaving_type = "'.$exittype.'"';
						}
						else
						{
							$whereraw .= ' And leaving_type = "'.$exittype.'"';
						}
					}
					
					
					
					//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
					
					if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
					{
						$datefrom = $request->session()->get('passport_fromdate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
					{
						$dateto = $request->session()->get('passport_todate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
					{
						$dept = $request->session()->get('passport_department');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.dept_id IN('.$dept.')';
						}
						else
						{
							$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
						}
					}
					if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
					{
						$teamlead = $request->session()->get('passport_teamleader');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
							
						}
						else
						{
							$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
						}
					}
					if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
					{
						$empId = $request->session()->get('passport_emp_id');
						 if($whereraw == '')
						{
							$whereraw = 'passport.emp_id IN ('.$empId.')';
						}
						else
						{
							$whereraw .= ' And passport.emp_id IN ('.$empId.')';
						}
					}
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
						{
							$designd = $request->session()->get('passport_designation');
							 //$departmentArray = explode(",",$designd);
							if($whereraw == '')
							{
								$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
							}
							else
							{
								$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
							}
						}
		
		
		
					// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
					// {
					// 	$rangeid = $request->session()->get('range_filter_inner_list');
					// 	 if($whereraw == '')
					// 	{
					// 		$whereraw = 'range_id IN ('.$rangeid.')';
					// 	}
					// 	else
					// 	{
					// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
					// 	}
					// }
		
		
		
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
					{
						$fname = $request->session()->get('passport_emp_name');
						 $cnameArray = explode(",",$fname);
						 
						 $namefinalarray=array();
						 foreach($cnameArray as $namearray){
							 $namefinalarray[]="'".$namearray."'";					 
						 }
						 //print_r($namefinalarray);exit;
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
					
					if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
					{
						$company = $request->session()->get('company_candAll_filter_inner_list');
						 $selectedFilter['Company'] = $company;
						 if($whereraw == '')
						{
							$whereraw = 'company_visa = "'.$company.'"';
						}
						else
						{
							$whereraw .= ' And company_visa = "'.$company.'"';
						}
					}
					//echo $cname;exit;
					if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
					{
						$email = $request->session()->get('email_candAll_filter_inner_list');
						 $selectedFilter['CEMAIL'] = $email;
						 if($whereraw == '')
						{
							$whereraw = 'email = "'.$email.'"';
						}
						else
						{
							$whereraw .= ' And email = "'.$email.'"';
						}
					}
					if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
					{
						$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
						}
					}
					if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
					{
						$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
					}
					
		
		
					
					
					if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
					{
						$dept = $request->session()->get('dept_candAll_filter_inner_list');
						 $selectedFilter['DEPT'] = $dept;
						 if($whereraw == '')
						{
							$whereraw = 'department = "'.$dept.'"';
						}
						else
						{
							$whereraw .= ' And department = "'.$dept.'"';
						}
					}
					if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
					{
						$opening = $request->session()->get('opening_cand_filter_inner_list');
						 $selectedFilter['OPENING'] = $opening;
						 if($whereraw == '')
						{
							$whereraw = 'job_opening IN('.$opening.')';
						}
						else
						{
							$whereraw .= ' And job_opening IN('.$opening.')';
						}
					}
					if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
					{
						$status = $request->session()->get('status_candAll_filter_inner_list');
						 $selectedFilter['STATUS'] = $status;
						 if($whereraw == '')
						{
							$whereraw = 'status = "'.$status.'"';
						}
						else
						{
							$whereraw .= ' And status = "'.$status.'"';
						}
					}
					//echo $whereraw;exit;
					if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
					{
						$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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
					
					
					
					
					
					if($whereraw != '')
					{
						
						
						$empsessionId=$request->session()->get('EmployeeId');
						$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
						if($departmentDetails != '')
						{
							$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
							if($empDetails!='')
							{
								$passportDetails = Passport::join('employee_details', 'passport.emp_id', '=', 'employee_details.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
								->whereRaw($whereraw)
								->where('passport.passport_status',0)
								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)	
								->orWhereNotNull('employee_details.document_collection_id')
								->whereRaw($whereraw)
								->where('passport.passport_status',0)
								->where('document_collection_details.visa_process_status',4)
								->where('employee_details.dept_id',$empDetails->dept_id)				
								->orderBy('passport.updated_at','desc')
								->paginate($paginationValue);
				
								$reportsCount = Passport::join('employee_details', 'passport.emp_id', '=', 'employee_details.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->whereRaw($whereraw)
								->where('passport.passport_status',0)
								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)
								//->where('employee_details.document_collection_id','!=','')	
								->orWhereNotNull('employee_details.document_collection_id')
								->whereRaw($whereraw)

								->where('passport.passport_status',0)

								->where('document_collection_details.visa_process_status',4)
								->where('employee_details.dept_id',$empDetails->dept_id)				
								->orderBy('passport.id','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'passport.emp_id', '=', 'employee_details.emp_id')
							->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
							->whereRaw($whereraw)
							->where('passport.passport_status',0)
							->whereNull('employee_details.document_collection_id')
							//->where('employee_details.document_collection_id','!=','')	
							->orWhereNotNull('employee_details.document_collection_id')
							->whereRaw($whereraw)
							->where('passport.passport_status',0)
							->where('document_collection_details.visa_process_status',4)				
							->orderBy('passport.updated_at','desc')
							->paginate($paginationValue);
			
							$reportsCount = Passport::join('employee_details', 'passport.emp_id', '=', 'employee_details.emp_id')
							->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->whereRaw($whereraw)
							->where('passport.passport_status',0)
							->whereNull('employee_details.document_collection_id')

							//->where('employee_details.document_collection_id','!=','')	
							->orWhereNotNull('employee_details.document_collection_id')
							->whereRaw($whereraw)

							->where('passport.passport_status',0)

							->where('document_collection_details.visa_process_status',4)				
							->orderBy('passport.id','desc')
							->get()->count();
						}
						
						
						
						
						
						
						//echo "hello";
						
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
								$passportDetails = Passport::join('employee_details', 'passport.emp_id', '=', 'employee_details.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
								->where('passport.passport_status',0)
								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)
								//->where('employee_details.document_collection_id','!=','')	
								->orWhereNotNull('employee_details.document_collection_id')
								->where('passport.passport_status',0)

								->where('document_collection_details.visa_process_status',4)	
								->where('employee_details.dept_id',$empDetails->dept_id)			
								->orderBy('passport.updated_at','desc')
								//->get();

								//return $passportDetails;

								//->toSql();

								//dd($passportDetails);


								->paginate($paginationValue);
								//->get();


								//return $passportDetails;
								
								//->paginate($paginationValue);
								//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
								$reportsCount = Passport::join('employee_details', 'passport.emp_id', '=', 'employee_details.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->where('passport.passport_status',0)
								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)
								//->where('employee_details.document_collection_id','!=','')	
								->orWhereNotNull('employee_details.document_collection_id')
								->where('passport.passport_status',0)

								->where('document_collection_details.visa_process_status',4)
								->where('employee_details.dept_id',$empDetails->dept_id)				
								->orderBy('passport.id','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'passport.emp_id', '=', 'employee_details.emp_id')
							->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
							->where('passport.passport_status',0)
							->whereNull('employee_details.document_collection_id')

							//->where('employee_details.document_collection_id','!=','')	
							->orWhereNotNull('employee_details.document_collection_id')
							->where('passport.passport_status',0)

							->where('document_collection_details.visa_process_status',4)				
							->orderBy('passport.updated_at','desc')
							//->get();

							//return $passportDetails;

							//->toSql();

							//dd($passportDetails);


							->paginate($paginationValue);
							//->get();


							//return $passportDetails;
							
							//->paginate($paginationValue);
							//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
							$reportsCount = Passport::join('employee_details', 'passport.emp_id', '=', 'employee_details.emp_id')
							->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->where('passport.passport_status',0)
							->whereNull('employee_details.document_collection_id')

							//->where('employee_details.document_collection_id','!=','')	
							->orWhereNotNull('employee_details.document_collection_id')
							->where('passport.passport_status',0)

							->where('document_collection_details.visa_process_status',4)				
							->orderBy('passport.id','desc')
							->get()->count();
						}
						
						//echo "hello1";

						
					}
						$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
						$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
						$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
					
					$passportDetails->setPath(config('app.url/listingNotAvailableCompletePassports'));
					
					//print_r($documentCollectiondetails);exit;
			
		
				return view("Passport/listingNotAvailableCompletePassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','paginationValue'));
	}

	public function exportNotAvailableCompletePassportsReport(Request $request)
	{
		
		$parameters = $request->input(); 
			 $selectedId = $parameters['selectedIds'];
			 
			$filename = 'passport_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet(); 
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:J1');
			$sheet->setCellValue('A1', 'Passports List - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Team Leader'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Designation'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Department'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Work Location'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Vintage Days'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Passport Number'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Passport Status'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				//echo $sid;
				$misData = Passport::where("id",$sid)->first();

				

				$empName = $this->getEmployeeName($misData->emp_id);
				$teamLeader = $this->getTeamLeader($misData->emp_id);
				$designation = $this->getDesignation($misData->emp_id);
				$dept = $this->getDepartment($misData->emp_id);
				$location = $this->getWorkLocation($misData->emp_id);
				$vintage = $this->getVintage($misData->emp_id);
				$indexCounter++; 
				
				if($misData->passport_status==1)
				{
					$pstatus='Available';
				}
				else
				{
					$pstatus='Not Available';
				}
				
				
				$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $misData->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $empName)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $teamLeader)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $designation)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $dept)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $vintage)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('I'.$indexCounter, $misData->passport_number)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('J'.$indexCounter, $pstatus)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'J'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','J') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportPassport/'.$filename));	
				echo $filename;
				exit;
	}



	


	public function NotavailableinProgressVisaData(Request $request)
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
					
					//$request->session()->put('cname_empAll_filter_inner_list','');
					if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
					  {
					  $departmentID = $request->session()->get('onboarding_department_filter');
					  //$whereraw .= 'department = "'.$departmentID.'"';
					  }
				
					if(!empty($request->session()->get('passport_page_limit')))
					{
						$paginationValue = $request->session()->get('passport_page_limit');
					}
					else
					{
						$paginationValue = 100;
					}
					
					
					if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
					{
						$retained = $request->session()->get('offboardall_retained_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'retain = "'.$retained.'"';
						}
						else
						{
							$whereraw .= ' And retain = "'.$retained.'"';
						}
					}
					
					if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
					{
						$exittype = $request->session()->get('offboardall_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'leaving_type = "'.$exittype.'"';
						}
						else
						{
							$whereraw .= ' And leaving_type = "'.$exittype.'"';
						}
					}
					
					
					
					//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
					
					if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
					{
						$datefrom = $request->session()->get('passport_fromdate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
					{
						$dateto = $request->session()->get('passport_todate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
					{
						$dept = $request->session()->get('passport_department');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.dept_id IN('.$dept.')';
						}
						else
						{
							$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
						}
					}
					if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
					{
						$teamlead = $request->session()->get('passport_teamleader');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
							
						}
						else
						{
							$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
						}
					}
					if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
					{
						$empId = $request->session()->get('passport_emp_id');
						 if($whereraw == '')
						{
							$whereraw = 'passport.emp_id IN ('.$empId.')';
						}
						else
						{
							$whereraw .= ' And passport.emp_id IN ('.$empId.')';
						}
					}
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
						{
							$designd = $request->session()->get('passport_designation');
							 //$departmentArray = explode(",",$designd);
							if($whereraw == '')
							{
								$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
							}
							else
							{
								$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
							}
						}
		
		
		
					// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
					// {
					// 	$rangeid = $request->session()->get('range_filter_inner_list');
					// 	 if($whereraw == '')
					// 	{
					// 		$whereraw = 'range_id IN ('.$rangeid.')';
					// 	}
					// 	else
					// 	{
					// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
					// 	}
					// }
		
		
		
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
					{
						$fname = $request->session()->get('passport_emp_name');
						 $cnameArray = explode(",",$fname);
						 
						 $namefinalarray=array();
						 foreach($cnameArray as $namearray){
							 $namefinalarray[]="'".$namearray."'";					 
						 }
						 //print_r($namefinalarray);exit;
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
					
					if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
					{
						$company = $request->session()->get('company_candAll_filter_inner_list');
						 $selectedFilter['Company'] = $company;
						 if($whereraw == '')
						{
							$whereraw = 'company_visa = "'.$company.'"';
						}
						else
						{
							$whereraw .= ' And company_visa = "'.$company.'"';
						}
					}
					//echo $cname;exit;
					if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
					{
						$email = $request->session()->get('email_candAll_filter_inner_list');
						 $selectedFilter['CEMAIL'] = $email;
						 if($whereraw == '')
						{
							$whereraw = 'email = "'.$email.'"';
						}
						else
						{
							$whereraw .= ' And email = "'.$email.'"';
						}
					}
					if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
					{
						$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
						}
					}
					if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
					{
						$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
					}
					
		
		
					
					
					if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
					{
						$dept = $request->session()->get('dept_candAll_filter_inner_list');
						 $selectedFilter['DEPT'] = $dept;
						 if($whereraw == '')
						{
							$whereraw = 'department = "'.$dept.'"';
						}
						else
						{
							$whereraw .= ' And department = "'.$dept.'"';
						}
					}
					if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
					{
						$opening = $request->session()->get('opening_cand_filter_inner_list');
						 $selectedFilter['OPENING'] = $opening;
						 if($whereraw == '')
						{
							$whereraw = 'job_opening IN('.$opening.')';
						}
						else
						{
							$whereraw .= ' And job_opening IN('.$opening.')';
						}
					}
					if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
					{
						$status = $request->session()->get('status_candAll_filter_inner_list');
						 $selectedFilter['STATUS'] = $status;
						 if($whereraw == '')
						{
							$whereraw = 'status = "'.$status.'"';
						}
						else
						{
							$whereraw .= ' And status = "'.$status.'"';
						}
					}
					//echo $whereraw;exit;
					if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
					{
						$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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
					
					
					
					
					
					if($whereraw != '')
					{
						
						
						$empsessionId=$request->session()->get('EmployeeId');
						$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
						if($departmentDetails != '')
						{
							$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
							if($empDetails!='')
							{
								$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

								->whereRaw($whereraw)
								->where('passport.passport_status',0)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)	
								->where('employee_details.dept_id',$empDetails->dept_id)			
								->orderBy('passport.updated_at','desc')->paginate($paginationValue);
				
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->whereRaw($whereraw)
								->where('passport.passport_status',0)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)
								->where('employee_details.dept_id',$empDetails->dept_id)			
								->orderBy('passport.id','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

							->whereRaw($whereraw)
							->where('passport.passport_status',0)
							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)				
							->orderBy('passport.updated_at','desc')->paginate($paginationValue);
			
							$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->whereRaw($whereraw)
							->where('passport.passport_status',0)
							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)			
							->orderBy('passport.id','desc')
							->get()->count();
						}
						
						
						//echo "hello";
						
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
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

								->where('passport.passport_status',0)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)	
								->where('employee_details.dept_id',$empDetails->dept_id)			
								->orderBy('passport.updated_at','desc')
								->paginate($paginationValue);
								//->get();


								//return $passportDetails;
								
								//->paginate($paginationValue);
								//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->where('passport.passport_status',0)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)	
								->where('employee_details.dept_id',$empDetails->dept_id)			
								->orderBy('passport.id','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

							->where('passport.passport_status',0)
							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)				
							->orderBy('passport.updated_at','desc')
							->paginate($paginationValue);
							//->get();


							//return $passportDetails;
							
							//->paginate($paginationValue);
							//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
							$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->where('passport.passport_status',0)
							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)				
							->orderBy('passport.id','desc')
							->get()->count();
						}
						
						
						//echo "hello1";

						
					}
						$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
						$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
						$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
					
					$passportDetails->setPath(config('app.url/listingNotAvailableinProgressPassports'));
					
					//print_r($documentCollectiondetails);exit;
			
		
				return view("Passport/listingNotAvailableinProgressPassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','paginationValue'));
	}












	public function availableVisaCompletedReleasedData(Request $request)
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
					
					//$request->session()->put('cname_empAll_filter_inner_list','');
					if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
					  {
					  $departmentID = $request->session()->get('onboarding_department_filter');
					  //$whereraw .= 'department = "'.$departmentID.'"';
					  }
				
					if(!empty($request->session()->get('passport_page_limit')))
					{
						$paginationValue = $request->session()->get('passport_page_limit');
					}
					else
					{
						$paginationValue = 100;
					}
					
					
					if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
					{
						$retained = $request->session()->get('offboardall_retained_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'retain = "'.$retained.'"';
						}
						else
						{
							$whereraw .= ' And retain = "'.$retained.'"';
						}
					}
					
					if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
					{
						$exittype = $request->session()->get('offboardall_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'leaving_type = "'.$exittype.'"';
						}
						else
						{
							$whereraw .= ' And leaving_type = "'.$exittype.'"';
						}
					}
					
					
					
					//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
					
					if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
					{
						$datefrom = $request->session()->get('passport_fromdate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
					{
						$dateto = $request->session()->get('passport_todate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
					{
						$dept = $request->session()->get('passport_department');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.dept_id IN('.$dept.')';
						}
						else
						{
							$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
						}
					}
					if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
					{
						$teamlead = $request->session()->get('passport_teamleader');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
							
						}
						else
						{
							$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
						}
					}
					if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
					{
						$empId = $request->session()->get('passport_emp_id');
						 if($whereraw == '')
						{
							$whereraw = 'passport.emp_id IN ('.$empId.')';
						}
						else
						{
							$whereraw .= ' And passport.emp_id IN ('.$empId.')';
						}
					}
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
						{
							$designd = $request->session()->get('passport_designation');
							 //$departmentArray = explode(",",$designd);
							if($whereraw == '')
							{
								$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
							}
							else
							{
								$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
							}
						}
		
		
		
					// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
					// {
					// 	$rangeid = $request->session()->get('range_filter_inner_list');
					// 	 if($whereraw == '')
					// 	{
					// 		$whereraw = 'range_id IN ('.$rangeid.')';
					// 	}
					// 	else
					// 	{
					// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
					// 	}
					// }
		
		
		
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
					{
						$fname = $request->session()->get('passport_emp_name');
						 $cnameArray = explode(",",$fname);
						 
						 $namefinalarray=array();
						 foreach($cnameArray as $namearray){
							 $namefinalarray[]="'".$namearray."'";					 
						 }
						 //print_r($namefinalarray);exit;
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
					
					if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
					{
						$company = $request->session()->get('company_candAll_filter_inner_list');
						 $selectedFilter['Company'] = $company;
						 if($whereraw == '')
						{
							$whereraw = 'company_visa = "'.$company.'"';
						}
						else
						{
							$whereraw .= ' And company_visa = "'.$company.'"';
						}
					}
					//echo $cname;exit;
					if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
					{
						$email = $request->session()->get('email_candAll_filter_inner_list');
						 $selectedFilter['CEMAIL'] = $email;
						 if($whereraw == '')
						{
							$whereraw = 'email = "'.$email.'"';
						}
						else
						{
							$whereraw .= ' And email = "'.$email.'"';
						}
					}
					if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
					{
						$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
						}
					}
					if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
					{
						$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
					}
					
		
		
					
					
					if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
					{
						$dept = $request->session()->get('dept_candAll_filter_inner_list');
						 $selectedFilter['DEPT'] = $dept;
						 if($whereraw == '')
						{
							$whereraw = 'department = "'.$dept.'"';
						}
						else
						{
							$whereraw .= ' And department = "'.$dept.'"';
						}
					}
					if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
					{
						$opening = $request->session()->get('opening_cand_filter_inner_list');
						 $selectedFilter['OPENING'] = $opening;
						 if($whereraw == '')
						{
							$whereraw = 'job_opening IN('.$opening.')';
						}
						else
						{
							$whereraw .= ' And job_opening IN('.$opening.')';
						}
					}
					if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
					{
						$status = $request->session()->get('status_candAll_filter_inner_list');
						 $selectedFilter['STATUS'] = $status;
						 if($whereraw == '')
						{
							$whereraw = 'status = "'.$status.'"';
						}
						else
						{
							$whereraw .= ' And status = "'.$status.'"';
						}
					}
					//echo $whereraw;exit;
					if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
					{
						$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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
					
					
					
					
					
					if($whereraw != '')
					{
						
						$empsessionId=$request->session()->get('EmployeeId');
						$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
						if($departmentDetails != '')
						{
							$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
							if($empDetails!='')
							{
								$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
								->whereRaw($whereraw)

								->where('passport.release_list_status',1)
								->where('release_request_status',0)

								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)
								//->where('employee_details.document_collection_id','!=','')	
								->orWhereNotNull('employee_details.document_collection_id')
								->where('passport.release_list_status',1)
								->where('release_request_status',0)

								->where('document_collection_details.visa_process_status',4)	
								->where('employee_details.dept_id',$empDetails->dept_id)			
								->orderBy('passport.updated_at','desc')
								->paginate($paginationValue);
		
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
								->whereRaw($whereraw)
								->where('passport.release_list_status',1)
								->where('release_request_status',0)

								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)
								//->where('employee_details.document_collection_id','!=','')	
								->orWhereNotNull('employee_details.document_collection_id')
								->where('passport.release_list_status',1)
								->where('release_request_status',0)

								->where('document_collection_details.visa_process_status',4)
								->where('employee_details.dept_id',$empDetails->dept_id)				
								->orderBy('passport.updated_at','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
							->whereRaw($whereraw)

							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->whereNull('employee_details.document_collection_id')

							//->where('employee_details.document_collection_id','!=','')	
							->orWhereNotNull('employee_details.document_collection_id')
							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->where('document_collection_details.visa_process_status',4)				
							->orderBy('passport.updated_at','desc')
							->paginate($paginationValue);
		
							$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
							->whereRaw($whereraw)
							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->whereNull('employee_details.document_collection_id')

							//->where('employee_details.document_collection_id','!=','')	
							->orWhereNotNull('employee_details.document_collection_id')
							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->where('document_collection_details.visa_process_status',4)				
							->orderBy('passport.updated_at','desc')
							->get()->count();
						}
						
						
						//echo "hello"; exit;
						
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
							->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->whereNull('employee_details.document_collection_id')
							->where('employee_details.dept_id',$empDetails->dept_id)
							//->where('employee_details.document_collection_id','!=','')	
							->orWhereNotNull('employee_details.document_collection_id')
							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->where('document_collection_details.visa_process_status',4)	
							->where('employee_details.dept_id',$empDetails->dept_id)			
							->orderBy('passport.updated_at','desc')
							->paginate($paginationValue);
							//->get();


							//return $passportDetails;
							
							//->paginate($paginationValue);
							//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
							$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->whereNull('employee_details.document_collection_id')
							->where('employee_details.dept_id',$empDetails->dept_id)
							//->where('employee_details.document_collection_id','!=','')	
							->orWhereNotNull('employee_details.document_collection_id')
							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->where('document_collection_details.visa_process_status',4)
							->where('employee_details.dept_id',$empDetails->dept_id)				
							->orderBy('passport.updated_at','desc')
							->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->whereNull('employee_details.document_collection_id')

							//->where('employee_details.document_collection_id','!=','')	
							->orWhereNotNull('employee_details.document_collection_id')
							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->where('document_collection_details.visa_process_status',4)				
							->orderBy('passport.updated_at','desc')
							->paginate($paginationValue);
							//->get();


							//return $passportDetails;
							
							//->paginate($paginationValue);
							//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
							$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->whereNull('employee_details.document_collection_id')

							//->where('employee_details.document_collection_id','!=','')	
							->orWhereNotNull('employee_details.document_collection_id')
							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->where('document_collection_details.visa_process_status',4)				
							->orderBy('passport.updated_at','desc')
							->get()->count();
						}
						
						
						//echo "hello1";

						
					}
						$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
						$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
						$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
					
					$passportDetails->setPath(config('app.url/listingRealeasedPassportsVisaComplete'));
					
					//print_r($documentCollectiondetails);exit;
			
		
				return view("Passport/listingRealeasedPassportsVisaComplete",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','paginationValue'));
	}


	public function availableinProgressVisaReleasedData(Request $request)
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
					
					//$request->session()->put('cname_empAll_filter_inner_list','');
					if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
					  {
					  $departmentID = $request->session()->get('onboarding_department_filter');
					  //$whereraw .= 'department = "'.$departmentID.'"';
					  }
				
					if(!empty($request->session()->get('passport_page_limit')))
					{
						$paginationValue = $request->session()->get('passport_page_limit');
					}
					else
					{
						$paginationValue = 100;
					}
					
					
					if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
					{
						$retained = $request->session()->get('offboardall_retained_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'retain = "'.$retained.'"';
						}
						else
						{
							$whereraw .= ' And retain = "'.$retained.'"';
						}
					}
					
					if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
					{
						$exittype = $request->session()->get('offboardall_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'leaving_type = "'.$exittype.'"';
						}
						else
						{
							$whereraw .= ' And leaving_type = "'.$exittype.'"';
						}
					}
					
					
					
					//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
					
					if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
					{
						$datefrom = $request->session()->get('passport_fromdate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
					{
						$dateto = $request->session()->get('passport_todate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
					{
						$dept = $request->session()->get('passport_department');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.dept_id IN('.$dept.')';
						}
						else
						{
							$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
						}
					}
					if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
					{
						$teamlead = $request->session()->get('passport_teamleader');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
							
						}
						else
						{
							$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
						}
					}
					if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
					{
						$empId = $request->session()->get('passport_emp_id');
						 if($whereraw == '')
						{
							$whereraw = 'passport.emp_id IN ('.$empId.')';
						}
						else
						{
							$whereraw .= ' And passport.emp_id IN ('.$empId.')';
						}
					}
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
						{
							$designd = $request->session()->get('passport_designation');
							 //$departmentArray = explode(",",$designd);
							if($whereraw == '')
							{
								$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
							}
							else
							{
								$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
							}
						}
		
		
		
					// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
					// {
					// 	$rangeid = $request->session()->get('range_filter_inner_list');
					// 	 if($whereraw == '')
					// 	{
					// 		$whereraw = 'range_id IN ('.$rangeid.')';
					// 	}
					// 	else
					// 	{
					// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
					// 	}
					// }
		
		
		
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
					{
						$fname = $request->session()->get('passport_emp_name');
						 $cnameArray = explode(",",$fname);
						 
						 $namefinalarray=array();
						 foreach($cnameArray as $namearray){
							 $namefinalarray[]="'".$namearray."'";					 
						 }
						 //print_r($namefinalarray);exit;
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
					
					if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
					{
						$company = $request->session()->get('company_candAll_filter_inner_list');
						 $selectedFilter['Company'] = $company;
						 if($whereraw == '')
						{
							$whereraw = 'company_visa = "'.$company.'"';
						}
						else
						{
							$whereraw .= ' And company_visa = "'.$company.'"';
						}
					}
					//echo $cname;exit;
					if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
					{
						$email = $request->session()->get('email_candAll_filter_inner_list');
						 $selectedFilter['CEMAIL'] = $email;
						 if($whereraw == '')
						{
							$whereraw = 'email = "'.$email.'"';
						}
						else
						{
							$whereraw .= ' And email = "'.$email.'"';
						}
					}
					if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
					{
						$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
						}
					}
					if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
					{
						$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
					}
					
		
		
					
					
					if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
					{
						$dept = $request->session()->get('dept_candAll_filter_inner_list');
						 $selectedFilter['DEPT'] = $dept;
						 if($whereraw == '')
						{
							$whereraw = 'department = "'.$dept.'"';
						}
						else
						{
							$whereraw .= ' And department = "'.$dept.'"';
						}
					}
					if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
					{
						$opening = $request->session()->get('opening_cand_filter_inner_list');
						 $selectedFilter['OPENING'] = $opening;
						 if($whereraw == '')
						{
							$whereraw = 'job_opening IN('.$opening.')';
						}
						else
						{
							$whereraw .= ' And job_opening IN('.$opening.')';
						}
					}
					if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
					{
						$status = $request->session()->get('status_candAll_filter_inner_list');
						 $selectedFilter['STATUS'] = $status;
						 if($whereraw == '')
						{
							$whereraw = 'status = "'.$status.'"';
						}
						else
						{
							$whereraw .= ' And status = "'.$status.'"';
						}
					}
					//echo $whereraw;exit;
					if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
					{
						$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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
					
					
					
					
					
					if($whereraw != '')
					{
						
						$empsessionId=$request->session()->get('EmployeeId');
						$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
						if($departmentDetails != '')
						{
							$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
							if($empDetails!='')
							{
								$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
								->whereRaw($whereraw)

								->where('passport.release_list_status',1)
								->where('release_request_status',0)

								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)
								->where('employee_details.dept_id',$empDetails->dept_id)				
								->orderBy('passport.updated_at','desc')
								->paginate($paginationValue);
				
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
								->whereRaw($whereraw)

								->where('passport.release_list_status',1)
								->where('release_request_status',0)

								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)		
								->where('employee_details.dept_id',$empDetails->dept_id)		
								->orderBy('passport.updated_at','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
							->whereRaw($whereraw)

							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)				
							->orderBy('passport.updated_at','desc')
							->paginate($paginationValue);
			
							$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
							->whereRaw($whereraw)

							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)				
							->orderBy('passport.updated_at','desc')
							->get()->count();
						}
						
						
						//echo "hello";
						
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
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

								->where('passport.release_list_status',1)
								->where('release_request_status',0)

								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)	
								->where('employee_details.dept_id',$empDetails->dept_id)			
								->orderBy('passport.updated_at','desc')
								->paginate($paginationValue);
								//->get();


								//return $passportDetails;
						
								//->paginate($paginationValue);
								//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->where('passport.release_list_status',1)
								->where('release_request_status',0)

								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)	
								->where('employee_details.dept_id',$empDetails->dept_id)			
								->orderBy('passport.id','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)				
							->orderBy('passport.updated_at','desc')
							->paginate($paginationValue);
							//->get();


							//return $passportDetails;
						
							//->paginate($paginationValue);
							//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
							$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->where('passport.release_list_status',1)
							->where('release_request_status',0)

							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)				
							->orderBy('passport.id','desc')
							->get()->count();
						}
						
						
						
						
						//echo "hello1";

						
					}
						$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
						$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
						$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
					
					$passportDetails->setPath(config('app.url/listingRealeasedPassportsVisaInProgress'));
					
					//print_r($documentCollectiondetails);exit;
			
		
				return view("Passport/listingRealeasedPassportsVisaInProgress",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','paginationValue'));
	}


















	
	public function NotavailableVisaCompletedRequestedData(Request $request)
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
					
					//$request->session()->put('cname_empAll_filter_inner_list','');
					if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
					  {
					  $departmentID = $request->session()->get('onboarding_department_filter');
					  //$whereraw .= 'department = "'.$departmentID.'"';
					  }
				
					if(!empty($request->session()->get('passport_page_limit')))
					{
						$paginationValue = $request->session()->get('passport_page_limit');
					}
					else
					{
						$paginationValue = 100;
					}
					
					
					if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
					{
						$retained = $request->session()->get('offboardall_retained_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'retain = "'.$retained.'"';
						}
						else
						{
							$whereraw .= ' And retain = "'.$retained.'"';
						}
					}
					
					if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
					{
						$exittype = $request->session()->get('offboardall_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'leaving_type = "'.$exittype.'"';
						}
						else
						{
							$whereraw .= ' And leaving_type = "'.$exittype.'"';
						}
					}
					
					
					
					//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
					
					if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
					{
						$datefrom = $request->session()->get('passport_fromdate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
					{
						$dateto = $request->session()->get('passport_todate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
					{
						$dept = $request->session()->get('passport_department');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.dept_id IN('.$dept.')';
						}
						else
						{
							$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
						}
					}
					if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
					{
						$teamlead = $request->session()->get('passport_teamleader');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
							
						}
						else
						{
							$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
						}
					}
					if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
					{
						$empId = $request->session()->get('passport_emp_id');
						 if($whereraw == '')
						{
							$whereraw = 'passport.emp_id IN ('.$empId.')';
						}
						else
						{
							$whereraw .= ' And passport.emp_id IN ('.$empId.')';
						}
					}
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
						{
							$designd = $request->session()->get('passport_designation');
							 //$departmentArray = explode(",",$designd);
							if($whereraw == '')
							{
								$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
							}
							else
							{
								$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
							}
						}
		
		
		
					// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
					// {
					// 	$rangeid = $request->session()->get('range_filter_inner_list');
					// 	 if($whereraw == '')
					// 	{
					// 		$whereraw = 'range_id IN ('.$rangeid.')';
					// 	}
					// 	else
					// 	{
					// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
					// 	}
					// }
		
		
		
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
					{
						$fname = $request->session()->get('passport_emp_name');
						 $cnameArray = explode(",",$fname);
						 
						 $namefinalarray=array();
						 foreach($cnameArray as $namearray){
							 $namefinalarray[]="'".$namearray."'";					 
						 }
						 //print_r($namefinalarray);exit;
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
					
					if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
					{
						$company = $request->session()->get('company_candAll_filter_inner_list');
						 $selectedFilter['Company'] = $company;
						 if($whereraw == '')
						{
							$whereraw = 'company_visa = "'.$company.'"';
						}
						else
						{
							$whereraw .= ' And company_visa = "'.$company.'"';
						}
					}
					//echo $cname;exit;
					if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
					{
						$email = $request->session()->get('email_candAll_filter_inner_list');
						 $selectedFilter['CEMAIL'] = $email;
						 if($whereraw == '')
						{
							$whereraw = 'email = "'.$email.'"';
						}
						else
						{
							$whereraw .= ' And email = "'.$email.'"';
						}
					}
					if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
					{
						$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
						}
					}
					if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
					{
						$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
					}
					
		
		
					
					
					if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
					{
						$dept = $request->session()->get('dept_candAll_filter_inner_list');
						 $selectedFilter['DEPT'] = $dept;
						 if($whereraw == '')
						{
							$whereraw = 'department = "'.$dept.'"';
						}
						else
						{
							$whereraw .= ' And department = "'.$dept.'"';
						}
					}
					if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
					{
						$opening = $request->session()->get('opening_cand_filter_inner_list');
						 $selectedFilter['OPENING'] = $opening;
						 if($whereraw == '')
						{
							$whereraw = 'job_opening IN('.$opening.')';
						}
						else
						{
							$whereraw .= ' And job_opening IN('.$opening.')';
						}
					}
					if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
					{
						$status = $request->session()->get('status_candAll_filter_inner_list');
						 $selectedFilter['STATUS'] = $status;
						 if($whereraw == '')
						{
							$whereraw = 'status = "'.$status.'"';
						}
						else
						{
							$whereraw .= ' And status = "'.$status.'"';
						}
					}
					//echo $whereraw;exit;
					if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
					{
						$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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
					
					
					
					
					
					if($whereraw != '')
					{
						
						$empsessionId=$request->session()->get('EmployeeId');
						$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
						if($departmentDetails != '')
						{
							$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
							if($empDetails!='')
							{
								$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

								->whereRaw($whereraw)
								->where('passport.passport_status',0)
								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)
								//->where('employee_details.document_collection_id','!=','')	
								->orWhereNotNull('employee_details.document_collection_id')
								->whereRaw($whereraw)

								->where('passport.passport_status',0)

								->where('document_collection_details.visa_process_status',4)	
								->where('employee_details.dept_id',$empDetails->dept_id)			
								->orderBy('passport.updated_at','desc')
								->paginate($paginationValue);
				
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->whereRaw($whereraw)
								->where('passport.passport_status',0)
								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)
								//->where('employee_details.document_collection_id','!=','')	
								->orWhereNotNull('employee_details.document_collection_id')
								->whereRaw($whereraw)

								->where('passport.passport_status',0)

								->where('document_collection_details.visa_process_status',4)
								->where('employee_details.dept_id',$empDetails->dept_id)				
								->orderBy('passport.id','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
						->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
						->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

						->whereRaw($whereraw)
						->where('passport.passport_status',0)
						->whereNull('employee_details.document_collection_id')

						//->where('employee_details.document_collection_id','!=','')	
						->orWhereNotNull('employee_details.document_collection_id')
						->whereRaw($whereraw)

						->where('passport.passport_status',0)

						->where('document_collection_details.visa_process_status',4)				
						->orderBy('passport.updated_at','desc')
						->paginate($paginationValue);
		
						$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
						->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
						->whereRaw($whereraw)
						->where('passport.passport_status',0)
						->whereNull('employee_details.document_collection_id')

						//->where('employee_details.document_collection_id','!=','')	
						->orWhereNotNull('employee_details.document_collection_id')
						->whereRaw($whereraw)

						->where('passport.passport_status',0)

						->where('document_collection_details.visa_process_status',4)				
						->orderBy('passport.id','desc')
						->get()->count();
						}
						
						
						//echo "hello";
						
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
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

								->where('passport.passport_status',0)
								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)
								//->where('employee_details.document_collection_id','!=','')	
								->orWhereNotNull('employee_details.document_collection_id')
								->where('passport.passport_status',0)

								->where('document_collection_details.visa_process_status',4)
								->where('employee_details.dept_id',$empDetails->dept_id)				
								->orderBy('passport.updated_at','desc')
								->paginate($paginationValue);
								//->get();


								//return $passportDetails;
								
								//->paginate($paginationValue);
								//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->where('passport.passport_status',0)
								->whereNull('employee_details.document_collection_id')
								->where('employee_details.dept_id',$empDetails->dept_id)
								//->where('employee_details.document_collection_id','!=','')	
								->orWhereNotNull('employee_details.document_collection_id')
								->where('passport.passport_status',0)

								->where('document_collection_details.visa_process_status',4)		
								->where('employee_details.dept_id',$empDetails->dept_id)		
								->orderBy('passport.id','desc')
								->get()->count();
							}
						}
						else{
								$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')

								->where('passport.passport_status',0)
								->whereNull('employee_details.document_collection_id')

								//->where('employee_details.document_collection_id','!=','')	
								->orWhereNotNull('employee_details.document_collection_id')
								->where('passport.passport_status',0)

								->where('document_collection_details.visa_process_status',4)				
								->orderBy('passport.updated_at','desc')
								->paginate($paginationValue);
								//->get();


								//return $passportDetails;
								
								//->paginate($paginationValue);
								//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->leftjoin('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->where('passport.passport_status',0)
								->whereNull('employee_details.document_collection_id')

								//->where('employee_details.document_collection_id','!=','')	
								->orWhereNotNull('employee_details.document_collection_id')
								->where('passport.passport_status',0)

								->where('document_collection_details.visa_process_status',4)				
								->orderBy('passport.id','desc')
								->get()->count();
						}
						
						
						//echo "hello1";

						
					}
						$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
						$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
						$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
					
					$passportDetails->setPath(config('app.url/listingRequestedQueuePassports'));
					
					//print_r($documentCollectiondetails);exit;
			
		
				return view("Passport/listingRequestedQueuePassports",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','paginationValue'));
	}


	public function NotavailableinProgressVisaRequestedData(Request $request)
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
					
					//$request->session()->put('cname_empAll_filter_inner_list','');
					if(!empty($request->session()->get('onboarding_department_filter')) && $request->session()->get('onboarding_department_filter') != '')
					  {
					  $departmentID = $request->session()->get('onboarding_department_filter');
					  //$whereraw .= 'department = "'.$departmentID.'"';
					  }
				
					if(!empty($request->session()->get('passport_page_limit')))
					{
						$paginationValue = $request->session()->get('passport_page_limit');
					}
					else
					{
						$paginationValue = 100;
					}
					
					
					if(!empty($request->session()->get('offboardall_retained_filter_inner_list')) && $request->session()->get('offboardall_retained_filter_inner_list') != 'All')
					{
						$retained = $request->session()->get('offboardall_retained_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'retain = "'.$retained.'"';
						}
						else
						{
							$whereraw .= ' And retain = "'.$retained.'"';
						}
					}
					
					if(!empty($request->session()->get('offboardall_filter_inner_list')) && $request->session()->get('offboardall_filter_inner_list') != 'All')
					{
						$exittype = $request->session()->get('offboardall_filter_inner_list');
						 if($whereraw == '')
						{
							$whereraw = 'leaving_type = "'.$exittype.'"';
						}
						else
						{
							$whereraw .= ' And leaving_type = "'.$exittype.'"';
						}
					}
					
					
					
					//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
					
					if(!empty($request->session()->get('passport_fromdate')) && $request->session()->get('passport_fromdate') != 'All')
					{
						$datefrom = $request->session()->get('passport_fromdate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at>= "'.$datefrom.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_todate')) && $request->session()->get('passport_todate') != 'All')
					{
						$dateto = $request->session()->get('passport_todate');
						 if($whereraw == '')
						{
							$whereraw = 'passport.created_at<= "'.$dateto.' 00:00:00"';
						}
						else
						{
							$whereraw .= ' And passport.created_at<= "'.$dateto.' 00:00:00"';
						}
					}
					if(!empty($request->session()->get('passport_department')) && $request->session()->get('passport_department') != 'All')
					{
						$dept = $request->session()->get('passport_department');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.dept_id IN('.$dept.')';
						}
						else
						{
							$whereraw .= ' And employee_details.dept_id IN('.$dept.')';
						}
					}
					if(!empty($request->session()->get('passport_teamleader')) && $request->session()->get('passport_teamleader') != 'All')
					{
						$teamlead = $request->session()->get('passport_teamleader');
						 //$departmentArray = explode(",",$dept);
						if($whereraw == '')
						{
							$whereraw = 'employee_details.tl_id IN('.$teamlead.')';
							
						}
						else
						{
							$whereraw .= ' And employee_details.tl_id IN('.$teamlead.')';
						}
					}
					if(!empty($request->session()->get('passport_emp_id')) && $request->session()->get('passport_emp_id') != 'All')
					{
						$empId = $request->session()->get('passport_emp_id');
						 if($whereraw == '')
						{
							$whereraw = 'passport.emp_id IN ('.$empId.')';
						}
						else
						{
							$whereraw .= ' And passport.emp_id IN ('.$empId.')';
						}
					}
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_designation')) && $request->session()->get('passport_designation') != 'All')
						{
							$designd = $request->session()->get('passport_designation');
							 //$departmentArray = explode(",",$designd);
							if($whereraw == '')
							{
								$whereraw = 'employee_details.designation_by_doc_collection IN('.$designd.')';
							}
							else
							{
								$whereraw .= ' And employee_details.designation_by_doc_collection IN('.$designd.')';
							}
						}
		
		
		
					// if(!empty($request->session()->get('range_filter_inner_list')) && $request->session()->get('range_filter_inner_list') != 'All')
					// {
					// 	$rangeid = $request->session()->get('range_filter_inner_list');
					// 	 if($whereraw == '')
					// 	{
					// 		$whereraw = 'range_id IN ('.$rangeid.')';
					// 	}
					// 	else
					// 	{
					// 		$whereraw .= ' And range_id IN ('.$rangeid.')';
					// 	}
					// }
		
		
		
		
		
		
		
		
		
		
					if(!empty($request->session()->get('passport_emp_name')) && $request->session()->get('passport_emp_name') != 'All')
					{
						$fname = $request->session()->get('passport_emp_name');
						 $cnameArray = explode(",",$fname);
						 
						 $namefinalarray=array();
						 foreach($cnameArray as $namearray){
							 $namefinalarray[]="'".$namearray."'";					 
						 }
						 //print_r($namefinalarray);exit;
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
					
					if(!empty($request->session()->get('company_candAll_filter_inner_list')) && $request->session()->get('company_candAll_filter_inner_list') != 'All')
					{
						$company = $request->session()->get('company_candAll_filter_inner_list');
						 $selectedFilter['Company'] = $company;
						 if($whereraw == '')
						{
							$whereraw = 'company_visa = "'.$company.'"';
						}
						else
						{
							$whereraw .= ' And company_visa = "'.$company.'"';
						}
					}
					//echo $cname;exit;
					if(!empty($request->session()->get('email_candAll_filter_inner_list')) && $request->session()->get('email_candAll_filter_inner_list') != 'All')
					{
						$email = $request->session()->get('email_candAll_filter_inner_list');
						 $selectedFilter['CEMAIL'] = $email;
						 if($whereraw == '')
						{
							$whereraw = 'email = "'.$email.'"';
						}
						else
						{
							$whereraw .= ' And email = "'.$email.'"';
						}
					}
					if(!empty($request->session()->get('datefrom_offboard_lastworkingday_list')) && $request->session()->get('datefrom_offboard_lastworkingday_list') != 'All')
					{
						$lastworkingday = $request->session()->get('datefrom_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign< "'.$lastworkingday.'" OR  last_working_day_terminate< "'.$lastworkingday.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign< "'.$lastworkingday.'" OR last_working_day_terminate< "'.$lastworkingday.'"';
						}
					}
					if(!empty($request->session()->get('dateto_offboard_lastworkingday_list')) && $request->session()->get('dateto_offboard_lastworkingday_list') != 'All')
					{
						$dateto = $request->session()->get('dateto_offboard_lastworkingday_list');
						 if($whereraw == '')
						{
							$whereraw = 'last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
						else
						{
							$whereraw .= ' And last_working_day_resign> "'.$dateto.'"  OR  last_working_day_terminate> "'.$dateto.'"';
						}
					}
					
		
		
					
					
					if(!empty($request->session()->get('dept_candAll_filter_inner_list')) && $request->session()->get('dept_candAll_filter_inner_list') != 'All')
					{
						$dept = $request->session()->get('dept_candAll_filter_inner_list');
						 $selectedFilter['DEPT'] = $dept;
						 if($whereraw == '')
						{
							$whereraw = 'department = "'.$dept.'"';
						}
						else
						{
							$whereraw .= ' And department = "'.$dept.'"';
						}
					}
					if(!empty($request->session()->get('opening_cand_filter_inner_list')) && $request->session()->get('opening_cand_filter_inner_list') != 'All')
					{
						$opening = $request->session()->get('opening_cand_filter_inner_list');
						 $selectedFilter['OPENING'] = $opening;
						 if($whereraw == '')
						{
							$whereraw = 'job_opening IN('.$opening.')';
						}
						else
						{
							$whereraw .= ' And job_opening IN('.$opening.')';
						}
					}
					if(!empty($request->session()->get('status_candAll_filter_inner_list')) && $request->session()->get('status_candAll_filter_inner_list') != 'All')
					{
						$status = $request->session()->get('status_candAll_filter_inner_list');
						 $selectedFilter['STATUS'] = $status;
						 if($whereraw == '')
						{
							$whereraw = 'status = "'.$status.'"';
						}
						else
						{
							$whereraw .= ' And status = "'.$status.'"';
						}
					}
					//echo $whereraw;exit;
					if(!empty($request->session()->get('vintage_candAll_filter_inner_list')) && $request->session()->get('vintage_candAll_filter_inner_list') != 'All')
					{
						$vintage = $request->session()->get('vintage_candAll_filter_inner_list');
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
					
					
					
					
					
					if($whereraw != '')
					{
						
						$empsessionId=$request->session()->get('EmployeeId');
						$departmentDetails = JobFunctionPermission::where("user_id",$empsessionId)->first();
						if($departmentDetails != '')
						{
							$empDetails=Employee_details::where("emp_id",$departmentDetails->emp_id)->first();
							if($empDetails!='')
							{
								$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
								->whereRaw($whereraw)
								->where('passport.passport_status',0)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)	
								->where('employee_details.dept_id',$empDetails->dept_id)			
								->orderBy('passport.updated_at','desc')->paginate($paginationValue);
				
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->whereRaw($whereraw)
								->where('passport.passport_status',0)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)
								->where('employee_details.dept_id',$empDetails->dept_id)			
								->orderBy('passport.id','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
								->whereRaw($whereraw)
								->where('passport.passport_status',0)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)				
								->orderBy('passport.updated_at','desc')->paginate($paginationValue);
				
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->whereRaw($whereraw)
								->where('passport.passport_status',0)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)			
								->orderBy('passport.id','desc')
								->get()->count();
						}
						
						//echo "hello";
						
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
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
								->where('passport.passport_status',0)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)
								->where('employee_details.dept_id',$empDetails->dept_id)				
								->orderBy('passport.updated_at','desc')
								->paginate($paginationValue);
								//->get();


								//return $passportDetails;
								
								//->paginate($paginationValue);
								//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
								$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
								->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
								->where('passport.passport_status',0)
								->where('employee_details.document_collection_id','!=','')	
								->whereNotNull('employee_details.document_collection_id')
								->where('document_collection_details.visa_process_status','!=',4)	
								->where('employee_details.dept_id',$empDetails->dept_id)			
								->orderBy('passport.id','desc')
								->get()->count();
							}
						}
						else{
							$passportDetails = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->select('passport.id as rowid','passport.*','employee_details.*','document_collection_details.*')
							->where('passport.passport_status',0)
							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)				
							->orderBy('passport.updated_at','desc')
							->paginate($paginationValue);
							//->get();


							//return $passportDetails;
							
							//->paginate($paginationValue);
							//$passportDetails = Passport::where('passport_status',1)->orderBy('id','desc')->paginate($paginationValue);		
							$reportsCount = Passport::join('employee_details', 'employee_details.emp_id', '=', 'passport.emp_id')
							->join('document_collection_details', 'employee_details.document_collection_id', '=', 'document_collection_details.id')
							->where('passport.passport_status',0)
							->where('employee_details.document_collection_id','!=','')	
							->whereNotNull('employee_details.document_collection_id')
							->where('document_collection_details.visa_process_status','!=',4)				
							->orderBy('passport.id','desc')
							->get()->count();
						}
						
						
						
						//echo "hello1";

						
					}
						$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
						$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
						$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
					
					$passportDetails->setPath(config('app.url/listingRequestedQueuePassportsINProgress'));
					
					//print_r($documentCollectiondetails);exit;
			
		
				return view("Passport/listingRequestedQueuePassportsINProgress",compact('passportDetails','departmentLists','productDetails','designationDetails','reportsCount','filterList','paginationValue'));
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
		
		//return $empid;
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

	public function requestReleasePassportFormData(Request $request)
	{
		$empid = $request->empid;
		$passportDetails = Passport::where('emp_id',$empid)->orderBy('id','desc')->first();
		return view("Passport/RequestReleaseFormContent",compact('passportDetails'));
	}

	public function requestReleasePassportFormDatainRow(Request $request)
	{
		$empid = $request->empid;
		$passportDetails = Passport::where('emp_id',$empid)->orderBy('id','desc')->first();
		return view("Passport/RequestReleaseFormContentinRow",compact('passportDetails'));
	}

	public function requestforPassportFormData(Request $request)
	{
		$empid = $request->empid;
		$passportDetails = Passport::where('emp_id',$empid)->orderBy('id','desc')->first();

		//return $passportDetails;


		return view("Passport/RequestforPassport",compact('passportDetails'));
	}

	public function requestPassportUpdateDetails(Request $request)
	{
		$id = $request->id;
		$passportDetails = Passport::where('id',$id)->orderBy('id','desc')->first();
		return view("Passport/RequestUpdateDetails",compact('passportDetails'));
	}




	public function requestPassportHistoryDetails(Request $request)
	{
		$empid = $request->empid;

		//return $empid;



		$passportDetails = PassportHistory::where("emp_id",$empid)->orderBy('id','asc')->get();
		
		//return $passportDetails;

		return view("Passport/RequestHistoryDetails",compact('passportDetails','empid'));


	}




	
	public function saveReleaseRequestfromTopData(Request $request)
	{
		//return $request->all();



		$validator = Validator::make($request->all(), [
			//'passportnumber' => 'required',
			//'passportreleaseddate' => 'required',
            'releasecomments' => 'required',       
        ],
		[
			//'passportnumber.required'=> 'passport number is Required',
			//'passportreleaseddate.required'=> 'Passport Released Date is Required',
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

			



			if($passportData)
			{
				$passportData->release_comments = $request->releasecomments;			
				$passportData->request_generate_by = $userid;			
				$passportData->request_id = random_int(1000,9999).$request->empid.random_int(1000,9999);
				//$passportData->passport_number = $request->passportnumber;
				$passportData->request_generate_status = 1;
				$passportData->release_list_status = 1;	
				$passportData->passport_status = 0;	

				$passportData->request_generate_at = date('Y-m-d');	

				
				$passportData->save();
			}
			else
			{

				$passportData = new Passport();
				$passportData->release_comments = $request->releasecomments;
				$passportData->emp_id = $request->empid;			
				$passportData->request_generate_by = $userid;
				$passportData->request_id = random_int(1000,9999).$request->empid.random_int(1000,9999);
			
				//$passportData->passport_release_date = $request->passportreleaseddate;
				//$passportData->passport_number = $request->passportnumber;
				$passportData->request_generate_status = 1;	
				$passportData->release_list_status = 1;
				$passportData->passport_status = 0;	
	
				$passportData->request_generate_at = date('Y-m-d');	
			
				$passportData->save();
			}


			


			$passportData = Passport::where('emp_id',$request->empid)->orderBy('id','DESC')->first();

			
			$passportHistory = new PassportHistory();
			$passportHistory->emp_id = $request->empid;
			$passportHistory->requestcreatedat = date('Y-m-d');
			$passportHistory->requestcreatedby = $userid;
			$passportHistory->requestcreatedcomment = $request->releasecomments;
			//$passportHistory->passport_release_date = $request->passportreleaseddate;
			//$passportHistory->release_status = 1;
			$passportHistory->request_type = 3;
			$passportHistory->status = 1;

			$passportHistory->request_id = $passportData->request_id;

			$passportHistory->save();


			return response()->json(['success'=>'Release Passport Request Saved Successfully.']);


		}

		
	}


	public function saveReleaseRequestinRowData(Request $request)
	{
		//return $request->all();



		$validator = Validator::make($request->all(), [
			//'passportnumber' => 'required',
			//'passportreleaseddate' => 'required',
            'releasecomments' => 'required',       
        ],
		[
			//'passportnumber.required'=> 'passport number is Required',
			//'passportreleaseddate.required'=> 'Passport Released Date is Required',
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

			



			if($passportData)
			{
				$passportData->release_comments = $request->releasecomments;			
				$passportData->request_generate_by = $userid;			
				$passportData->request_id = random_int(1000,9999).$request->empid.random_int(1000,9999);
				//$passportData->passport_number = $request->passportnumber;
				$passportData->request_generate_status = 1;
				$passportData->release_list_status = 1;	
				$passportData->passport_status = 0;	

				$passportData->request_generate_at = date('Y-m-d');	

				
				$passportData->save();
			}
			else
			{

				$passportData = new Passport();
				$passportData->release_comments = $request->releasecomments;
				$passportData->emp_id = $request->empid;			
				$passportData->request_generate_by = $userid;
				$passportData->request_id = random_int(1000,9999).$request->empid.random_int(1000,9999);
			
				//$passportData->passport_release_date = $request->passportreleaseddate;
				//$passportData->passport_number = $request->passportnumber;
				$passportData->request_generate_status = 1;	
				$passportData->release_list_status = 1;
				$passportData->passport_status = 0;	
	
				$passportData->request_generate_at = date('Y-m-d');	
			
				$passportData->save();
			}


			


			$passportData = Passport::where('emp_id',$request->empid)->orderBy('id','DESC')->first();

			
			$passportHistory = new PassportHistory();
			$passportHistory->emp_id = $request->empid;
			$passportHistory->requestcreatedat = date('Y-m-d');
			$passportHistory->requestcreatedby = $userid;
			$passportHistory->requestcreatedcomment = $request->releasecomments;
			//$passportHistory->passport_release_date = $request->passportreleaseddate;
			//$passportHistory->release_status = 1;
			$passportHistory->request_type = 3;
			$passportHistory->status = 1;

			$passportHistory->request_id = $passportData->request_id;

			$passportHistory->save();


			return response()->json(['success'=>'Release Passport Request Generated Successfully.']);


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


				$passportData->release_comments = $request->releasecomments;			
				$passportData->releaseby = $userid;			
				$passportData->passport_release_date = $request->passportreleaseddate;
				//$passportData->passport_number = $request->passportnumber;
				$passportData->release_request_status = 1;	
				$passportData->passport_status = 0;	
				$passportData->releaserequestat = date('Y-m-d H:i:s');

				$passportData->requestpassport_status = 0;
				$passportData->requestpassport_at = NULL;
				$passportData->requestpassport_by = NULL;
				$passportData->requestpassport_comments = NULL;
				$passportData->release_list_status = 0;

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
			$passportData->requestpassport_comments = $request->requestcomments;
			//$passportData->passport_number = $request->passportnumber;			
			$passportData->requestpassport_by = $userid;			
			$passportData->passport_submit_date = $request->passportsubmitdate;
			$passportData->requestpassport_status = 1;
			$passportData->passport_status = 1;	
			
			$passportData->release_request_status = 0;
			$passportData->releaserequestat = NULL;
			$passportData->releaseby = NULL;
			$passportData->release_comments = NULL;
			$passportData->release_list_status = 0;
		
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

	public function finalRequestUpdatePostData(Request $request)
	{
		//return $request->all();

		$validator = Validator::make($request->all(), [
            'finalstatus' => 'required',     
        ],
		[
		 'finalstatus.required'=> 'Status is required',
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
			//return $request->all();

			$passportData = Passport::where('id',$request->rowid)->where('emp_id',$request->empid)->orderBy('id','DESC')->first();
			$userid=$request->session()->get('EmployeeId');

			if($passportData->passport_status==1)
			{
				$passportData->passport_number = $request->passportnumber;
				$passportData->releaseat = $request->passportsubmitdate;		
				$passportData->release_request_status=1;
				$passportData->releaseby = $userid;
				
				$passportData->passport_status = 0;
			}
			else
			{
				$passportData->passport_number = $request->passportnumber;
				$passportData->requestpassport_at = $request->passportsubmitdate;		
				$passportData->requestpassport_status=1;
				$passportData->requestpassport_by = $userid;

				$passportData->passport_status = 1;

			}

			$userid=$request->session()->get('EmployeeId');
			$passportData->final_by = $userid;			
			$passportData->final_at = date('Y-m-d H:i:s');
			$passportData->finalstatus = $request->finalstatus;		
			$passportData->save();

			return response()->json(['success'=>'Request Saved Successfully.']);


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
				return "N/A";
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
				return "N/A";
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


	public function searchPassportDetailsData(Request $request)
		{
			//print_r($request->input());
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
			
			$request->session()->put('passport_emp_name',$name);
			$request->session()->put('passport_emp_id',$empId);
			$request->session()->put('passport_fromdate',$datefrom);
			$request->session()->put('passport_todate',$dateto);
			$request->session()->put('passport_department',$department);
			$request->session()->put('passport_teamleader',$teamlaed);
			
			$request->session()->put('passport_designation',$design);
			// $request->session()->put('dateto_offboard_lastworkingday_list',$datetolastworkingday);
			// $request->session()->put('datefrom_offboard_lastworkingday_list',$datefromlastworkingday);
			
			// $request->session()->put('ReasonofAttrition_empoffboard_filter_list',$ReasonofAttrition);
			// $request->session()->put('empoffboard_status_filter_list',$offboardstatus);
			// $request->session()->put('datefrom_offboard_dort_list',$datefromdort);
			// $request->session()->put('dateto_offboard_dort_list',$datetodort);
			// $request->session()->put('empoffboard_ffstatus_filter_list',$offboardffstatus);
			 //return  redirect('listingPageonboarding');	
		}
		public function resetPassportDetailsData(Request $request){
			$request->session()->put('passport_fromdate','');
			$request->session()->put('passport_todate','');
			$request->session()->put('passport_department','');
			$request->session()->put('passport_teamleader','');
			$request->session()->put('passport_emp_name','');
			$request->session()->put('passport_emp_id','');
			$request->session()->put('passport_designation','');
			// $request->session()->put('dateto_offboard_lastworkingday_list','');
			// $request->session()->put('datefrom_offboard_lastworkingday_list','');
			// $request->session()->put('ReasonofAttrition_empoffboard_filter_list','');
			// $request->session()->put('empoffboard_status_filter_list','');
			// $request->session()->put('datefrom_offboard_dort_list','');
			// $request->session()->put('dateto_offboard_dort_list','');
			// $request->session()->put('empoffboard_ffstatus_filter_list','');

			// $request->session()->put('visastatus_filter_inner_list_innerTbl','');
			// $request->session()->put('visastages_filter_inner_list_innerTbl','');
		}





		public function searchInnerFilterPassportDetailsData(Request $request)
		{
			$visaStatus='';
			if($request->input('visaStatus')!='')
			{
				$visaStatus=implode(",", $request->input('visaStatus'));
			}

			$visastagesvisastages='';
			if($request->input('visastages')!='')
			{
				$visastages=implode(",", $request->input('visastages'));
			}

			$request->session()->put('visastatus_filter_inner_list_innerTbl',$visaStatus);
			$request->session()->put('visastages_filter_inner_list_innerTbl',$visastages);


		}

		public function resetInnerFilter(Request $request)
		{
			$request->session()->put('visastatus_filter_inner_list_innerTbl','');
			$request->session()->put('visastages_filter_inner_list_innerTbl','');

			
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


		public function setPageLimitProcess(Request $request)
		{
			$offset = $request->offset;
		   	$request->session()->put('passport_page_limit',$offset);
		}






		
		public function UpdateUsersinPassport(Request $request)
		{
			$emp_details = Employee_details::whereNull('passport_update_status')->orderBy("id","desc")->get();

			//return $emp_details;
	
			foreach($emp_details as $emp)
			{
				$passport_details = Passport::where('emp_id',$emp->emp_id)->orderBy("id","ASC")->first();

				if($passport_details)
				{
				}
				else
				{
					$passportDetails = new Passport();
					$passportDetails->emp_id = $emp->emp_id;
					$passportDetails->passport_status = 0;
					$passportDetails->save();

					$empDetails = Employee_details::where('emp_id',$emp->emp_id)->orderBy("id","desc")->first();
					$empDetails->passport_update_status = 1;
					$empDetails->save();


				}
				
				
			}	
			return response()->json(['success'=>'Passport Module Updated Successfully.']);		  
		}





	// above new one


	// below old ones

	
	public function updateRecordsfromChildtoParent(Request $request)
	{
		



		$eibParentData = Passport::get();

		foreach($eibParentData as $eibParent)
		{
			$eibBankData = Employee_details::where('emp_id',$eibParent->emp_id)->orderBy("id","DESC")->first();

			if($eibBankData)
			{
				$eibParentDataRequest = Passport::where('emp_id',$eibBankData->emp_id)
				->update(['tl_id' => $eibBankData->tl_id]);
			}

			
		}
		return response()->json(['success'=>'Data Updated Successfully for ENBD Parent Table for Attribute customer_email_enbd.']);









		
		// $eibParentData = ENBDDepartmentFormEntry::whereBetween('id', [1, 20000])->get();

		// foreach($eibParentData as $eibParent)
		// {
		// 	$eibBankData = ENBDDepartmentFormChildEntry::where('parent_id',$eibParent->id)->where('attribute_code','customer_email_enbd')->orderBy("id","DESC")->first();

		// 	if($eibBankData)
		// 	{
		// 		$eibParentDataRequest = ENBDDepartmentFormEntry::where('id',$eibBankData->parent_id)
		// 		->update(['email' => $eibBankData->attribute_value]);
		// 	}

			
		// }
		// return response()->json(['success'=>'Data Updated Successfully for ENBD Parent Table for Attribute customer_email_enbd.']);

	}


}
