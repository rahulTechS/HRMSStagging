<?php

namespace App\Http\Controllers\MISLoan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use App\Models\Company\Divison;
use App\Models\Company\Department;
use App\Models\Company\Product;
use App\Models\MIS\ProductMis;
use App\Models\MIS\WpCountries;
use App\Models\MIS\ENBDCardsImportFiles;
use App\Models\MIS\ENBDCardsMisReport;
use App\Models\MIS\MainMisImportFiles;
use App\Models\MIS\JonusReportLog;
use App\Models\Entry\Employee;
use App\Models\MIS\MainMisReport;
use App\Models\Attribute\Attributes;
use App\Models\MIS\BankDetailsUAE;
use App\Models\MIS\MainMisImportENBDCardsTabFiles;
use App\Models\MIS\MainMisReportTab;
use App\Models\MIS\PrecallingFile;
use App\Models\LoanMis\ENDBLoanMis;
use App\Models\LoanMis\LoanStatus;
use App\Models\LoanMis\LoanScheme;
use App\Models\LoanMis\LoanBanks;
use App\Models\LoanMis\JonusLoan;
use App\Models\LoanMis\MisDocuments;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\PDFMarge\FPDF_Merge;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Logs\ExportReportLogs;
use App\Models\Logs\ENBDCardsLogs;

class MisLoanController extends Controller
{
    public function ImportMISReport()
	{
		$divisonDetails = Divison::where("status",1)->orderBy("id","DESC")->get();
		return view("MIS/importMISReport",compact('divisonDetails'));
	}
       
	   public function bankNameDetails(Request $request)
	   {
		    $divisonID = $request->divisionId;
		   	$departmentDetails =  Department::where("divison_id",$divisonID)->where("status",1)->orderBy("id","DESC")->get();
			return view("MIS/bankNameDetails",compact('departmentDetails'));
	   }
	   
	   public function locationDetails(Request $request)
	   {
		    $bankId = $request->bankId;
		   	
			return view("MIS/locationDetails",compact('bankId'));
	   }
	   
	   public function productDetails(Request $request)
	   {
		    $location = $request->location;
		    $bankId = $request->bankId;
		   	$productMIDetails = ProductMis::where("department_id",$bankId)->orderBy("id","DESC")->get();
			return view("MIS/productDetails",compact('productMIDetails'));
	   }
	   
	   public function ReadyForImport(Request $request)
	   {
		   $parameterRequests = $request->input();
		  
		   $division = $parameterRequests['division'];
		   $bank_name = $parameterRequests['bank_name'];
		   $product = $parameterRequests['product'];
		   if($division == 6 && $bank_name ==9 && $product== 1)
		   {
			     return redirect('ENBDCardsMisImport');
		   }
		   else
		   {
				 return redirect('notCreated');
		   }
	   }
	   
	   public function ENBDCardsMisImport()
	   {
		   $enbdCardsFImport = ENBDCardsImportFiles::orderBy("id","DESC")->get();
			
		   return view("MIS/ENBDCardsMisImport",compact('enbdCardsFImport'));
	   }
	   
	    public function notCreated()
	   {
		   return view("MIS/notCreated");
	   }
	   
	 
		
		
			
			
			
			public function enbdCardsJonusReport(Request $request)
			{
				error_reporting(E_ALL);
				ini_set("display_errors", 1);
				/* $page = $request->has('page') ? $request->get('page') : 1; */
				/* $limit = $request->has('limit') ? $request->get('limit') : 10; */
			  $whereraw = '';
			  $selectedFilter['customer_name'] = '';
			  $selectedFilter['employee_id'] = '';
			   if(!empty($request->session()->get('enbd_cards_customer_name')))
				{
					$customerName = $request->session()->get('enbd_cards_customer_name');
					$selectedFilter['customer_name'] = $customerName;
					if($whereraw == '')
					{
						$whereraw = "customer_name like '".$customerName."%'";
					}
					else
					{
						$whereraw .= " And customer_name like '".$customerName."%'";
					}
				}
				
				if(!empty($request->session()->get('enbd_cards_employee_id')))
				{
					
					$employeeId = $request->session()->get('enbd_cards_employee_id');
					$selectedFilter['employee_id'] = $employeeId;
					if($whereraw == '')
					{
						$whereraw = 'employee_id = '.$employeeId;
					}
					else
					{
						$whereraw .= ' And employee_id = '.$employeeId;
					}
				} 
			 
				
				if(!empty($request->session()->get('offset_enbd_cards')))
				{
					$paginationValue = $request->session()->get('offset_enbd_cards');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if($whereraw != '')
				{
					$reports = enbdCardsMISReport::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue);
				}
				else
				{
					$reports = enbdCardsMISReport::orderBy("id","DESC")->paginate($paginationValue);
				}
				$reports->setPath(config('app.url/enbdCardsJonusReport'));
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
					if($whereraw != '')
				{
					
					$reportsCount = enbdCardsMISReport::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = enbdCardsMISReport::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				return view("MIS/enbdCardsJonusReport",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
			}
			
			public static function getEmployeeName($id)
			{
				$empData =Employee_details::where("id",$id)->first();
				if($empData != '')
				{
				return $empData->first_name.'&nbsp;'.$empData->middle_name.'&nbsp;'.$empData->last_name;
				}
				else
				{
				return '--';
				}
			}
			
			public static function getLocation($id)
			{
				$empData =Employee_details::where("id",$id)->first();
				
				if($empData != '')
				{
				$empId = $empData->emp_id;
				return Employee_attribute::where("emp_id",$empId)->where("attribute_code","work_location")->first()->attribute_values;
				}
				else
				{
				return '--';
				}
				
			}
			public static function getAdminName($id)
			{
				$admin =Employee::where("id",$id)->first();
				return $admin->fullname;
			}
			
			public function detailsCustomerReport(Request $request)
			{
				$misid =  $request->misId;
				$report = enbdCardsMISReport::where("id",$misid)->first()->toArray();
				
				return view("MIS/detailsCustomerReport",compact('report'));
			}
			
			public function ENBDCardsFileImportStart(Request $request)
			{
				
				$detailsV = $request->input();
				$attr_f_import = $detailsV['attr_f_import'];
				$empDetailsDat = ENBDCardsImportFiles::find($attr_f_import);
				$filename = $empDetailsDat->file_name;
				$uploadPath = '/srv/www/htdocs/hrm/public/uploads/misImport/';
				$fullpathFileName = $uploadPath . $filename;
				$file = fopen($fullpathFileName, "r");
				$i = 1;
				$dataFromCsv = array();
				while (!feof($file)) {

					$dataFromCsv[$i] = fgetcsv($file);

					$i++;
				}

				fclose($file);
				
				echo 'Number of Records :- '.count($dataFromCsv);exit;
				
			}
			
			public function jonusUpload(Request $request)
			{
				$currentDate = date("Y-m-d");
				/*
				*checking for jonus cards
				*/
				
				$uploadStatusJonusCardsCount = 1;
				$uploadStatusJonusCards = JonusReportLog::whereDate("uploaded_date",$currentDate)->where("type","cards")->orderBy("id","DESC")->first();
				if($uploadStatusJonusCards == '')
				{
					$uploadStatusJonusCardsCount = 2;
				}
				/*
				*checking for jonus cards
				*/
				/*
				*checking for jonus loan
				*/
				$uploadStatusJonusLoansCount = 1;
				$uploadStatusJonusLoans = JonusReportLog::whereDate("uploaded_date",$currentDate)->where("type","loans")->orderBy("id","DESC")->first();
				if($uploadStatusJonusLoans == '')
				{
					$uploadStatusJonusLoansCount = 2;
				}
				/*
				*checking for jonus loan
				*/
				
				$jonusReportLogDetails = JonusReportLog::orderBy("id","DESC")->first();
				
				/*
				*jonus report logs datas
				*start coding
				*/
				$jonusReportLoglist = JonusReportLog::orderBy("id","DESC")->get()->count();
				/*
				*jonus report logs datas
				*start coding
				*/
				$redirecttoMIS = 1;
				if(isset($_REQUEST['misid']))
				{
					$request->session()->put('mis_return_id',$_REQUEST['misid']);  
					$redirecttoMIS = 2;
				}
				
				return view("MIS/jonusUpload",compact('uploadStatusJonusCardsCount','uploadStatusJonusLoansCount','jonusReportLogDetails','jonusReportLoglist','redirecttoMIS'));
			}
			public function addMISReport(Request $request)
			{
				return view("MIS/addMISReport");
			}
			
			public function manageMisLoan(Request $request)
			{
				
				$whereraw = '';
			  $selectedFilter['customer_name'] = '';
			  $selectedFilter['employee_id'] = '';
			  $selectedFilter['report'] = '';
			   if(!empty($request->session()->get('mis_enbd_cards_customer_name')))
				{
					$customerName = $request->session()->get('mis_enbd_cards_customer_name');
					$selectedFilter['customer_name'] = $customerName;
					if($whereraw == '')
					{
						$whereraw = "cm_name like '".$customerName."%'";
					}
					else
					{
						$whereraw .= " And cm_name like '".$customerName."%'";
					}
				}
				
				if(!empty($request->session()->get('mis_enbd_cards_emp_name')))
				{
					
					$employeeId = $request->session()->get('mis_enbd_cards_emp_name');
					$selectedFilter['employee_id'] = $employeeId;
					if($whereraw == '')
					{
						$whereraw = 'employee_id = '.$employeeId;
					}
					else
					{
						$whereraw .= ' And employee_id = '.$employeeId;
					}
				} 
			     if(!empty($request->session()->get('mis_enbd_cards_report_manual')))
				  {
						$dateReport = $request->session()->get('mis_enbd_cards_report_manual');
						$selectedFilter['report'] = 'DS';
						if($whereraw == '')
						{
							$whereraw = 'submission_format = '.$dateReport;
						}
						else
						{
							$whereraw .= ' And submission_format = '.$dateReport;
						}
				  }
				
				if(!empty($request->session()->get('offset_enbd_cards_inner_mis')))
				{
					$paginationValue = $request->session()->get('offset_enbd_cards_inner_mis');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if($whereraw != '')
				{
					$reports = ENDBLoanMis::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue);
				}
				else
				{
					$reports = ENDBLoanMis::orderBy("id","DESC")->paginate($paginationValue);
				}
				$reports->setPath(config('app.url/manageMisLoan'));
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if($whereraw != '')
				{
					
					$reportsCount = ENDBLoanMis::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = ENDBLoanMis::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				
				return view("MISLoan/Loan/manageMisLoan",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
				
			}
			
			public function mainMisImport(Request $request)
			{
				$mainMISFiles = MainMisImportFiles::orderBy("id","DESC")->get();
			
		   
				return view("MIS/mainMisImport",compact('mainMISFiles'));
			}
			
			
			
			
			public function mainMISFileUpload(Request $request)
			{
				$request->validate([

            'file' => 'required|mimes:csv,txt|max:10000',

        ]);

  

        $fileName = 'MIS-Report-Cards_'.date("Y-m-d_h-i-s").'.csv';  

   

        $request->file->move(public_path('uploads/misMainReport'), $fileName);

			$misObjImport = new MainMisImportFiles();
            $misObjImport->file_name = $fileName;
            $misObjImport->save();

        return back()

            ->with('success','You have successfully upload file.')

            ->with('file',$fileName);
			}
			
			
			public function importENBDMISCardsTab(Request $request)
			{
				$mainMISFiles = MainMisImportENBDCardsTabFiles::orderBy("id","DESC")->get();
			
		   
				return view("MIS/importENBDMISCardsTab",compact('mainMISFiles'));
			}
			
			public function mainMISENBDCardsTabFileUpload(Request $request)
			{
				$request->validate([

            'file' => 'required|mimes:csv,txt|max:10000',

        ]);

  

			$fileName = 'MIS-Report-Cards-tab_'.date("Y-m-d_h-i-s").'.csv';  

	   

			$request->file->move(public_path('uploads/misMainReportTab'), $fileName);

				$misObjImport = new MainMisImportENBDCardsTabFiles();
				$misObjImport->file_name = $fileName;
				$misObjImport->save();

			return back()

				->with('success','You have successfully upload file.')

				->with('file',$fileName);
			}
			
			public function setOffSetForENDBCards(Request $request)
			{
				$offset = $request->offset;
				$request->session()->put('offset_enbd_cards',$offset);
				 return  redirect('enbdCardsJonusReport');
			}
			public function setOffSetForENDBLoanInnerMIS(Request $request)
			{
				$offset = $request->offset;
				$request->session()->put('offset_enbd_loan_inner_mis',$offset);
				 return  redirect('manageMisLoan');
			}
			
			public function enbdCardsFilters(Request $request)
			{
				$selectedFilter = $request->input();
			
				$request->session()->put('enbd_cards_customer_name',$selectedFilter['filters']['customer_name']);
				$request->session()->put('enbd_cards_employee_id',$selectedFilter['filters']['employee_id']);
				 return  redirect('enbdCardsJonusReport');
			}
			
			public function resetEnbdCardsFilter(Request $request)
			{
				$request->session()->put('enbd_cards_customer_name','');
				$request->session()->put('enbd_cards_employee_id','');
				 return  redirect('enbdCardsJonusReport');
			}
			
			public function mainMISFileImport(Request $request)
			{
				$detailsV = $request->input();
				$attr_f_import = $detailsV['attr_f_import'];
				$empDetailsDat = MainMisImportFiles::find($attr_f_import);
				$filename = $empDetailsDat->file_name;
				$uploadPath = '/srv/www/htdocs/hrm/public/uploads/misMainReport/';
				$fullpathFileName = $uploadPath . $filename;
				$file = fopen($fullpathFileName, "r");
				$i = 1;
				$dataFromCsv = array();
				while (!feof($file)) {

					$dataFromCsv[$i] = fgetcsv($file);

					$i++;
				}

				fclose($file);
				
				$iCsv = 0;
			$iCsvIndex = 0;
			$arrayDat = array();
			$arrayDatAttribute = array();
			/*  echo '<pre>';
			print_r($dataFromCsv);
			exit;  */  
				$valuesCheck = array();
				foreach ($dataFromCsv as $fromCsv) {
					if ($iCsv != 0 && $fromCsv[1] != '') {
						
						/*
						*LOC_ADD
						*/
						$misObj = new MainMisReport();
						$misObj->date_of_submission = $fromCsv[1];
						$misObj->application_type = $fromCsv[2];
						$misObj->lead_source = $fromCsv[3];
						$misObj->PRODUCT = $fromCsv[4];
						$misObj->application_id = $fromCsv[5];
						$misObj->current_activity = $fromCsv[6];
						$misObj->approved_notapproved = $fromCsv[7];
						$misObj->monthly_ends = $fromCsv[8];
						$misObj->last_remarks_added = $fromCsv[9];
						$misObj->cm_name = $fromCsv[10];
						$misObj->fv_company_name = $fromCsv[11];
						$misObj->company_name_as_per_visa = $fromCsv[12];
						$misObj->ALE_NALE = $fromCsv[13];
						$misObj->CV_MOBILE_NUMBER = $fromCsv[14];
						$misObj->EV_DIRECT_OFFICE_NO = $fromCsv[15];
						$misObj->E_MAILADDRESS = $fromCsv[16];
						$misObj->SALARY = $fromCsv[17];
						$misObj->LOS = $fromCsv[18];
						$misObj->ACCOUNT_STATUS = $fromCsv[19];
						$misObj->ACCOUNT_NO = $fromCsv[20];
						$misObj->SALARIED = $fromCsv[21];
						$misObj->TL = $fromCsv[22];
						$misObj->SE_CODE_NAME = $fromCsv[23];
						$misObj->REFERENCE_NAME = $fromCsv[24];
						$misObj->REFERENCE_MOBILE_NO = $fromCsv[25];
						$misObj->NATIONALITY = $fromCsv[26];
						$misObj->PASSPORT_NO = $fromCsv[27];
						$misObj->DOB = $fromCsv[28];
						$misObj->VISA_Expiry_DATE = $fromCsv[29];
						$misObj->DESIGNATION = $fromCsv[30];
						$misObj->PRE_CALLING = $fromCsv[31];
						$misObj->MMN = $fromCsv[32];
						$misObj->EIDA = $fromCsv[33];
						$misObj->IBAN = $fromCsv[34];
						$misObj->EV = $fromCsv[35];
						$misObj->Type_of_Income_Proof = $fromCsv[36];
						$misObj->submission_format = date("Y-m-d",strtotime($fromCsv[1]));
						
						$scCode = $fromCsv[23];
						if(!empty($scCode))
						{
							$scCodeArray = explode("_",$scCode);
							if(isset($scCodeArray[1]))
							{
								$bank_code = $scCodeArray[1];
								$employeeDetails = Employee_details::where("source_code",$bank_code)->first();
								if($employeeDetails != '')
								{
								$misObj->employee_id =  $employeeDetails->id;
								$misObj->Employee_status = "Verified";
								}
								else
								{
									$misObj->Employee_status = "Not-Verified";
								}
							}
							else
							{
								$misObj->Employee_status = "Not-Verified";
							}
						}
						
						$misObj->created_by = $request->session()->get('EmployeeId');
						$misObj->type_data = 'Import';
					
						$misObj->save();
						$iCsvIndex++;
						
						
						
						
										
						
					}
					$iCsv++;
				}
				
				
				/* $enbdCardsObj = new ENBDCardsMisReport();
				$enbdCardsObj->insert($arrayDatAttribute); */
				$request->session()->flash('success','Import Completed.');
				return  redirect('manageMISENBDCards');
			}
			
			
			public function mainMISENBDCardsTabFileImport(Request $request)
			{
				$detailsV = $request->input();
				$attr_f_import = $detailsV['attr_f_import'];
				$empDetailsDat = MainMisImportENBDCardsTabFiles::find($attr_f_import);
				$filename = $empDetailsDat->file_name;
				$uploadPath = '/srv/www/htdocs/hrm/public/uploads/misMainReportTab/';
				$fullpathFileName = $uploadPath . $filename;
				$file = fopen($fullpathFileName, "r");
				$i = 1;
				$dataFromCsv = array();
				while (!feof($file)) {

					$dataFromCsv[$i] = fgetcsv($file);

					$i++;
				}

				$iCsv = 0;
			$iCsvIndex = 0;
			$arrayDat = array();
			$arrayDatAttribute = array();
			/*  echo '<pre>';
			print_r($dataFromCsv);
			exit;  */  
				$valuesCheck = array();
				foreach ($dataFromCsv as $fromCsv) {
					if ($iCsv != 0 && $fromCsv[1] != '') {
						
						/*
						*LOC_ADD
						*/
						$misObj = new MainMisReportTab();
						$misObj->DSA_Submission = $fromCsv[0];
						$misObj->Application_Type = $fromCsv[1];
						$misObj->Product_CREDIT_CARD_BUNDLE = $fromCsv[2];
						$misObj->TRACKER = $fromCsv[3];
						$misObj->CURRENT_ACTIVITY = $fromCsv[4];
						$misObj->DATA_CUT = $fromCsv[5];
						$misObj->CUSTOMER_NAME = $fromCsv[6];
						$misObj->TL = $fromCsv[7];
						$misObj->SE = $fromCsv[8];
						$misObj->NATIONALITY = $fromCsv[9];
						$misObj->SALARY = $fromCsv[10];
						$misObj->PRODUCT = $fromCsv[11];
						$misObj->Original_PRODUCT = $fromCsv[12];
						$misObj->Supplementary = $fromCsv[13];
						$misObj->MOBILE = $fromCsv[14];
						$misObj->STATUS = $fromCsv[15];
						$misObj->COMPANY_NAME = $fromCsv[16];
						$misObj->DESIGNATION = $fromCsv[17];
						$misObj->ENBD_STATUS = $fromCsv[18];
						$misObj->SEM = $fromCsv[19];
						
						
						$scCode = $fromCsv[8];
						if(!empty($scCode))
						{
							$scCodeArray = explode("_",$scCode);
							if(isset($scCodeArray[1]))
							{
								$bank_code = $scCodeArray[1];
								$employeeDetails = Employee_details::where("source_code",$bank_code)->first();
								if($employeeDetails != '')
								{
								$misObj->employee_id =  $employeeDetails->id;
								$misObj->Employee_status = "Verified";
								}
								else
								{
									$misObj->Employee_status = "Not-Verified";
								}
							}
							else
							{
								$misObj->Employee_status = "Not-Verified";
							}
						}
						
						$misObj->created_by = $request->session()->get('EmployeeId');
						$misObj->type_data = 'Import';
					
						$misObj->save();
						$iCsvIndex++;
						
						
						
						
										
						
					}
					$iCsv++;
				}
				
				
				/* $enbdCardsObj = new ENBDCardsMisReport();
				$enbdCardsObj->insert($arrayDatAttribute); */
				$request->session()->flash('success','Import Completed.');
				return  redirect('manageMISENBDCards');
				
			}
			
			
			public function addMisReportENBDLoan(Request $request)
			{
				//$tL_details = Employee_details::where("status",1)->where("source_code","-")->where("dept_id",9)->get();
				$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				$tL_details = Employee_details::where("job_role",'Team Leader')->where("dept_id",9)->get();
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->get();
				
				$NATs = Attributes::where("attribute_id",38)->first();
				$natValues = json_decode($NATs->opt_option);
				$banks = BankDetailsUAE::where("status",1)->get();
				return view("MISLoan/Loan/addMisReportENBDLoan",compact('tL_details','agent_details','natValues','banks'));
			}
			public function detailsMISReport(Request $request)
			{
				$misid =  $request->misId;
				$report = MainMisReport::where("id",$misid)->first()->toArray();
				
				return view("MIS/detailsMISReport",compact('report'));
			}
			
			public function enbdCardsMISFilters(Request $request)
			{
				$selectedFilter = $request->input();
			
				$request->session()->put('mis_enbd_cards_customer_name',$selectedFilter['filters']['customer_name']);
				$request->session()->put('mis_enbd_cards_emp_name',$selectedFilter['filters']['employee_id']);
				 return  redirect('manageMISENBDCards');
			}
			
			public function resetEnbdCardsMISFilter(Request $request)
			{
				$request->session()->put('mis_enbd_cards_customer_name','');
				$request->session()->put('mis_enbd_cards_emp_name','');
				 return  redirect('manageMISENBDCards');
			}
		   
		   public function savePostENBDLoanMIS(Request $request)
		   {
			   
				
			   $requestParameters = $request->input();
			  $product = $requestParameters['PRODUCT'];
			
				$misReportObj = new ENDBLoanMis();
			   $misReportObj->CM_NAME = $requestParameters['CM_NAME'];
			   $misReportObj->MOBILE = $requestParameters['MOBILE'];
			   $misReportObj->SALARY = $requestParameters['SALARY'];
			  
			   $misReportObj->LOAN = $requestParameters['LOAN'];
			  
			   
			   $misReportObj->TENURE = $requestParameters['TENURE'];
			   $misReportObj->NATIONALITY = $requestParameters['NATIONALITY'];
			   $misReportObj->CHQ = $requestParameters['CHQ'];
			   $misReportObj->chq_req = $requestParameters['chq_req'];
			 
			   $misReportObj->PRE_CALLING = $requestParameters['PRE_CALLING'];
			   $misReportObj->TL_NAME = $requestParameters['TL_NAME'];
			   $misReportObj->SE_NAME = $requestParameters['SE_NAME'];
			   $misReportObj->PRODUCT = $requestParameters['PRODUCT'];
			   $misReportObj->date_of_submission = date("Y-m-d",strtotime($requestParameters['date_of_submission']));
			   $misReportObj->SOURCING = $requestParameters['SOURCING'];
			   $misReportObj->STATUS = $requestParameters['STATUS'];
			   $misReportObj->ROI = $requestParameters['ROI'];
			   $misReportObj->COMPANY_NAME = $requestParameters['COMPANY_NAME'];
			   $misReportObj->CAT = $requestParameters['CAT'];
			   $misReportObj->FPD = $requestParameters['FPD'];
			     $misReportObj->COMMENTS = $requestParameters['COMMENTS'];
			    if($requestParameters['internal_DISBURSAL'] != '')
				   {
				   $misReportObj->internal_DISBURSAL = date("Y-m-d",strtotime($requestParameters['internal_DISBURSAL']));
				   }
			   if($product != 'AUTO LOAN')
			   {
			   $misReportObj->AECB = $requestParameters['AECB'];
			   }
			   else
			   {
				   if($requestParameters['mortgage_date'] != '')
				   {
				   $misReportObj->mortgage_date = date("Y-m-d",strtotime($requestParameters['mortgage_date']));
				   }
				    if($requestParameters['purchase_order'] != '')
				   {
				   $misReportObj->purchase_order = date("Y-m-d",strtotime($requestParameters['purchase_order']));
				   }
				  
				   
				   $misReportObj->vehicle_value = $requestParameters['vehicle_value'];
				   $misReportObj->Downpayment = $requestParameters['Downpayment'];
				   $misReportObj->carmake = $requestParameters['carmake'];
				   $misReportObj->carmodel = $requestParameters['carmodel'];
				   $misReportObj->modelyear = $requestParameters['modelyear'];
				   
			   }
			   $misReportObj->SCHEME = $requestParameters['SCHEME'];
			   $misReportObj->BANK = $requestParameters['BANK'];
			   if($requestParameters['BANK'] == 'other_bank')
			   {
				    $misReportObj->other_bank = $requestParameters['other_bank'];
			   }
			   $misReportObj->account_no = $requestParameters['account_no'];
			   
			  
			   $misReportObj->created_by = $request->session()->get('EmployeeId');
			  
			   $scCode = $requestParameters['SE_NAME'];
			   if(!empty($scCode))
						{
							$scCodeArray = explode("_",$scCode);
							if(isset($scCodeArray[1]))
							{
								$bank_code = $scCodeArray[1];
								$employeeDetails = Employee_details::where("source_code",$bank_code)->first();
								if($employeeDetails != '')
								{
								$misReportObj->employee_id =  $employeeDetails->id;
								$misReportObj->Employee_status = "Verified";
								}
								else
								{
									$misReportObj->Employee_status = "Not-Verified";
								}
							}
							else
							{
								$misReportObj->Employee_status = "Not-Verified";
							}
						}
					else
					{
						$misReportObj->Employee_status = "Not-Verified";
					}
			  $misReportObj->type_data = 'generated';
			 
			  
			  
			 
			 
			  
			
			   $misReportObj->match_status = 1;
			 
			  $misReportObj->save();
			  
				echo "<h3>Data Saved.</h3>";
		   }
		   
		   public static function getloanStatus($id)
		   {
			   if(!empty($id))
			   {
			   return strtoupper(LoanStatus::where("id",$id)->first()->name);
			   }
			   else
			   {
				   return "-";
			   }
		   }
		    public function getloanStatusCSV($id)
		   {
			   if(!empty($id))
			   {
			   return strtoupper(LoanStatus::where("id",$id)->first()->name);
			   }
			   else
			   {
				   return "-";
			   }
		   }
		   public static function getBankName($id)
		   {
			    if(!empty($id))
			   {

			   return strtoupper(LoanBanks::where("id",$id)->first()->name);
			   }
			   else
			   {
				    return "-";
			   }
		   }
		   
		    public function getBankNameCSV($id)
		   {
			    if(!empty($id))
			   {

			   return strtoupper(LoanBanks::where("id",$id)->first()->name);
			   }
			   else
			   {
				    return "-";
			   }
		   }
		   public static function getScheme($id)
		   {
			    if(!empty($id))
			   {
			   return strtoupper(LoanScheme::where("id",$id)->first()->name);
			   }
			   else
			   {
				   return "-";
			   }
		   }
		   public function getSchemeCSV($id)
		   {
			    if(!empty($id))
			   {
			   return strtoupper(LoanScheme::where("id",$id)->first()->name);
			   }
			   else
			   {
				   return "-";
			   }
		   }
		  
		   public static function getjonusCardsLogsStatus($date,$type)
		   {
			   $array1 = array();
			   $uploadDate = date("Y-m-d",strtotime($date));
			   $reports = JonusReportLog::whereDate("uploaded_date",$uploadDate)->where("type",$type)->first();
			   $array1['status'] = 'NO';
			   if($reports != '')
			   {
				   $adminId = $reports->created_by;
				   $admin =Employee::where("id",$adminId)->first();
			
				   $array1['status'] = 'YES'; 
				   $array1['time_values'] = $reports->time_values; 
				   $array1['created_by'] = $admin->fullname; 
				   $array1['file_name'] = $reports->file_name; 
			   }
			   return $array1;
		   }
		   
		   
		   public function manageMISENBDCardsTab(Request $request)
			{
				
				$whereraw = '';
			  $selectedFilter['customer_name'] = '';
			  $selectedFilter['employee_id'] = '';
			   if(!empty($request->session()->get('mis_enbd_cards_tab_customer_name')))
				{
					$customerName = $request->session()->get('mis_enbd_cards_tab_customer_name');
					$selectedFilter['customer_name'] = $customerName;
					if($whereraw == '')
					{
						$whereraw = "CUSTOMER_NAME like '".$customerName."%'";
					}
					else
					{
						$whereraw .= " And CUSTOMER_NAME like '".$customerName."%'";
					}
				}
				
				if(!empty($request->session()->get('mis_enbd_cards_tab_emp_name')))
				{
					
					$employeeId = $request->session()->get('mis_enbd_cards_tab_emp_name');
					$selectedFilter['employee_id'] = $employeeId;
					if($whereraw == '')
					{
						$whereraw = 'employee_id = '.$employeeId;
					}
					else
					{
						$whereraw .= ' And employee_id = '.$employeeId;
					}
				} 
			 
				
				if(!empty($request->session()->get('offset_enbd_cards_tab_inner_mis')))
				{
					$paginationValue = $request->session()->get('offset_enbd_cards_tab_inner_mis');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if($whereraw != '')
				{
					$reports = MainMisReportTab::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue);
				}
				else
				{
					$reports = MainMisReportTab::orderBy("id","DESC")->paginate($paginationValue);
				}
				$reports->setPath(config('app.url/manageMISENBDCardsTab'));
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if($whereraw != '')
				{
					
					$reportsCount = MainMisReportTab::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = MainMisReportTab::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				
				return view("MIS/manageMISENBDCardsTab",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
				
			}
			
			public function detailsMISReportTab(Request $request)
			{
				$misid =  $request->misId;
				$report = MainMisReportTab::where("id",$misid)->first()->toArray();
				
				return view("MIS/detailsMISReportTab",compact('report'));
			}
			
			
			public function enbdCardsTabMISFilters(Request $request)
			{
				$selectedFilter = $request->input();
			
				$request->session()->put('mis_enbd_cards_tab_customer_name',$selectedFilter['filters']['customer_name']);
				$request->session()->put('mis_enbd_cards_tab_emp_name',$selectedFilter['filters']['employee_id']);
				 return  redirect('manageMISENBDCardsTab');
			}
			
			public function resetEnbdCardsMISFilterTab(Request $request)
			{
				$request->session()->put('mis_enbd_cards_tab_customer_name','');
				$request->session()->put('mis_enbd_cards_tab_customer_name','');
				 return  redirect('manageMISENBDCardsTab');
			}
			
			public function getManualENBDLoan(Request $request)
			{
				$loanType = $request->loanType;
				$cList = WpCountries::get();
				$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->where("attribute_values","SALES MANAGER")->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				$tL_details = Employee_details::whereIn("emp_id",$tL_id)->get();
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->get();
				$LoanStatusList = LoanStatus::where("status",1)->get();
				
				$LoanSchemeList = LoanScheme::where("status",1)->get();
				$NATs = Attributes::where("attribute_id",38)->first();
				$natValues = json_decode($NATs->opt_option);
				$banks = LoanBanks::where("status",1)->get();
				return view("MISLoan/Loan/getManualENBDLoan",compact('LoanStatusList','LoanSchemeList','tL_details','agent_details','natValues','banks','loanType','cList'));
			}
			public function getManualENBDCardsFinal(Request $request)
			{
				$requestParameters = $request->input();
				
				/*save First Step Data*/
				
				 $misReportObj = new MainMisReport();
			   $misReportObj->date_of_submission = $requestParameters['date_of_submission'];
			   $misReportObj->submission_format = date("Y-m-d",strtotime($requestParameters['date_of_submission']));
			   $misReportObj->application_type = $requestParameters['application_type'];
			  
			   $misReportObj->current_activity = $requestParameters['current_activity'];
			  
			   
			   $misReportObj->fv_company_name = $requestParameters['fv_company_name'];
			   $misReportObj->company_name_as_per_visa = $requestParameters['company_name_as_per_visa'];
			   $misReportObj->ALE_NALE = $requestParameters['ALE_NALE'];
			   $misReportObj->LOS = $requestParameters['LOS'];
			   $misReportObj->ACCOUNT_STATUS = $requestParameters['ACCOUNT_STATUS'];
			   if($requestParameters['ACCOUNT_STATUS'] == 1)
			   {
				    $misReportObj->other_bank = $requestParameters['other_bank'];
			   }
			   $misReportObj->ACCOUNT_NO = $requestParameters['ACCOUNT_NO'];
			   $misReportObj->TL = $requestParameters['TL'];
			  
			   $misReportObj->lead_source = $requestParameters['lead_source'];
			   $misReportObj->PRODUCT = $requestParameters['PRODUCT'];
			   $misReportObj->file_source = $requestParameters['file_source'];
			   $misReportObj->SE_CODE_NAME = $requestParameters['SE_CODE_NAME'];
			  
			   $misReportObj->iban = $requestParameters['iban'];
			  
			   $misReportObj->created_by = $request->session()->get('EmployeeId');
			   $scCode = $requestParameters['SE_CODE_NAME'];
			   
			   
			   if(!empty($scCode))
						{
							$scCodeArray = explode("_",$scCode);
							if(isset($scCodeArray[1]))
							{
								$bank_code = $scCodeArray[1];
								$employeeDetails = Employee_details::where("source_code",$bank_code)->first();
								if($employeeDetails != '')
								{
								$misReportObj->employee_id =  $employeeDetails->id;
								$misReportObj->Employee_status = "Verified";
								}
								else
								{
									$misReportObj->Employee_status = "Not-Verified";
								}
							}
							else
							{
								$misReportObj->Employee_status = "Not-Verified";
							}
						}
					else
					{
						$misReportObj->Employee_status = "Not-Verified";
					}
			  $misReportObj->type_data = 'generated';
			  $misReportObj->complete_status = 1;
			  
			  
			 
			  $misReportObj->save();
			  $misMainReportID = $misReportObj->id;
				/*save First Step Data*/
				
				
				$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->where("attribute_values","SALES MANAGER")->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				$tL_details = Employee_details::whereIn("emp_id",$tL_id)->get();
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->get();
				$currentActMod = CurrentActivity::where("status",1)->get();
				$enbdCardsStatus = ENDBCARDStatus::where("status",1)->get();
				$MonthlyEndsValues = MonthlyEnds::where("status",1)->get();
				$NATs = Attributes::where("attribute_id",38)->first();
				$natValues = json_decode($NATs->opt_option);
				$banks = BankDetailsUAE::where("status",1)->get();
				return view("MIS/getManualENBDCardsfinal",compact('currentActMod','enbdCardsStatus','MonthlyEndsValues','tL_details','agent_details','natValues','banks','misMainReportID'));
			}
			
			public function listingENBDLoan(Request $request)
			{
				
			  $whereraw = '';
			  $whereraw1 = '';
			  $selectedFilter['customer_name'] = '';
			  $selectedFilter['employee_id'] = '';
			  $selectedFilter['report'] = '';
			  $selectedFilter['PRODUCT'] = '';
			  $selectedFilter['submission_from'] = '';
			  $selectedFilter['submission_to'] = '';
			  $selectedFilter['SOURCING'] = '';
			  $selectedFilter['customer_name'] = '';
			  $selectedFilter['submission_date_one'] = '';
			  $selectedFilter['APPID'] = '';
			  $selectedFilter['loan_status'] = '';
			  $selectedFilter['SCHEME'] = '';
			  $selectedFilter['Bank'] = '';
			  
			  
			  if(!empty($request->session()->get('product_type_filter')) && $request->session()->get('product_type_filter') != 'ALL')
				{
					
					$product = $request->session()->get('product_type_filter');
					$selectedFilter['PRODUCT'] = $product;
					if($product  != 'ALL')
					{
						if($whereraw == '')
						{
							$whereraw = "PRODUCT = '".$product."'";
							$whereraw1 = "PRODUCT = '".$product."'";
						}
						else
						{
							$whereraw .= " And PRODUCT = '".$product."'";
							$whereraw1 .= " And PRODUCT = '".$product."'";
						}
					}
				}
				
				
			   /* if(!empty($request->session()->get('mis_enbd_cards_customer_name')))
				{
					$customerName = $request->session()->get('mis_enbd_cards_customer_name');
					$selectedFilter['customer_name'] = $customerName;
					if($whereraw == '')
					{
						$whereraw = "cm_name like '".$customerName."%'";
					}
					else
					{
						$whereraw .= " And cm_name like '".$customerName."%'";
					}
				}
				
				if(!empty($request->session()->get('mis_enbd_cards_emp_name')))
				{
					
					$employeeId = $request->session()->get('mis_enbd_cards_emp_name');
					$selectedFilter['employee_id'] = $employeeId;
					if($whereraw == '')
					{
						$whereraw = 'employee_id = '.$employeeId;
					}
					else
					{
						$whereraw .= ' And employee_id = '.$employeeId;
					}
				} 
			  if(!empty($request->session()->get('mis_enbd_cards_report_manual')))
			  {
					$dateReport = $request->session()->get('mis_enbd_cards_report_manual');
					$selectedFilter['report'] = 'DS';
					if($whereraw == '')
					{
						$whereraw = 'submission_format = "'.$dateReport.'"';
					}
					else
					{
						$whereraw .= ' And submission_format = "'.$dateReport.'"';
					}
			  }
			  
			  if(!empty($request->session()->get('mis_enbd_cards_report_manual_type')) && $request->session()->get('mis_enbd_cards_report_manual_type') == 'ME')
			  {
					$dateReportFrom = $request->session()->get('mis_enbd_cards_report_manual_from');
					$dateReportTo = $request->session()->get('mis_enbd_cards_report_manual_to');
					$selectedFilter['report'] = 'ME';
					if($whereraw == '')
					{
						$whereraw = 'submission_format >= "'.$dateReportFrom.'" and submission_format <= "'.$dateReportTo.'"';
					}
					else
					{
						$whereraw .= ' And submission_format >= "'.$dateReportFrom.'" and submission_format <= "'.$dateReportTo.'"';
					}
			  }
			   if(!empty($request->session()->get('mis_enbd_cards_report_manual_type')) && $request->session()->get('mis_enbd_cards_report_manual_type') == 'Q')
			  {
					$dateReportFrom = $request->session()->get('mis_enbd_cards_report_manual_from');
					$dateReportTo = $request->session()->get('mis_enbd_cards_report_manual_to');
					$selectedFilter['report'] = 'ME';
					if($whereraw == '')
					{
						$whereraw = 'submission_format >= "'.$dateReportFrom.'" and submission_format <= "'.$dateReportTo.'"';
					}
					else
					{
						$whereraw .= ' And submission_format >= "'.$dateReportFrom.'" and submission_format <= "'.$dateReportTo.'"';
					}
			  }
			   if(!empty($request->session()->get('mis_enbd_cards_sales_se')))
			  {
					$se = $request->session()->get('mis_enbd_cards_sales_se');
					
					if($whereraw == '')
					{
						$whereraw = 'employee_id = "'.$se.'"';
					}
					else
					{
						$whereraw .= ' And employee_id = "'.$se.'"';
					}
			  } */
			  
			/* 	echo $whereraw;exit; */
				if(!empty($request->session()->get('offset_enbd_loan_inner_mis')))
				{
					$paginationValue = $request->session()->get('offset_enbd_loan_inner_mis');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if(!empty($request->session()->get('submission_from_enbd_loan_inner_mis')) && !empty($request->session()->get('submission_to_enbd_loan_inner_mis')))
				{
					$submission_from = $request->session()->get('submission_from_enbd_loan_inner_mis');
					$submission_to = $request->session()->get('submission_to_enbd_loan_inner_mis');
					 $selectedFilter['submission_from'] = $submission_from;
			  $selectedFilter['submission_to'] = $submission_to;
					if($whereraw == '')
					{
						$whereraw = 'date_of_submission >= "'.date("Y-m-d",strtotime($submission_from)).'" and date_of_submission <= "'.date("Y-m-d",strtotime($submission_to)).'"';
						$whereraw1 = 'date_of_submission >= "'.date("Y-m-d",strtotime($submission_from)).'" and date_of_submission <= "'.date("Y-m-d",strtotime($submission_to)).'"';
					}
					else
					{
					$whereraw .= ' And date_of_submission >= "'.date("Y-m-d",strtotime($submission_from)).'" and date_of_submission <= "'.date("Y-m-d",strtotime($submission_to)).'"';
					$whereraw1 .= ' And date_of_submission >= "'.date("Y-m-d",strtotime($submission_from)).'" and date_of_submission <= "'.date("Y-m-d",strtotime($submission_to)).'"';
					}
				}
				if(!empty($request->session()->get('emp_enbd_loan_inner_mis')) && $request->session()->get('emp_enbd_loan_inner_mis') != 'All')
				{
					$empID = $request->session()->get('emp_enbd_loan_inner_mis');
					 $selectedFilter['employee_id'] = $empID;
					 if($whereraw == '')
					{
						$whereraw = 'employee_id = '.$empID;
					}
					else
					{
						$whereraw .= ' And employee_id = '.$empID;
					}
				}
				if(!empty($request->session()->get('sourcing_enbd_loan_inner_mis')) && $request->session()->get('sourcing_enbd_loan_inner_mis') != 'All')
				{
					$sourcing = $request->session()->get('sourcing_enbd_loan_inner_mis');
					 $selectedFilter['SOURCING'] = $sourcing;
					 if($whereraw == '')
					{
						$whereraw = 'SOURCING = "'.$sourcing.'"';
					}
					else
					{
						$whereraw .= ' And SOURCING = "'.$sourcing.'"';
					}
				}
				
				if(!empty($request->session()->get('appid_enbd_loan_inner_mis')) && $request->session()->get('appid_enbd_loan_inner_mis') != 'All')
				{
					$appId = $request->session()->get('appid_enbd_loan_inner_mis');
					 $selectedFilter['APPID'] = $appId;
					 if($whereraw == '')
					{
						$whereraw = 'app_id = "'.$appId.'"';
					}
					else
					{
						$whereraw .= ' And app_id = "'.$appId.'"';
					}
				}
				
				if(!empty($request->session()->get('cus_name_enbd_loan_inner_mis')) && $request->session()->get('cus_name_enbd_loan_inner_mis') != 'All')
				{
					$cusName = $request->session()->get('cus_name_enbd_loan_inner_mis');
					 $selectedFilter['customer_name'] = $cusName;
					 if($whereraw == '')
					{
						$whereraw = 'CM_NAME = "'.$cusName.'"';
					}
					else
					{
						$whereraw .= ' And CM_NAME = "'.$cusName.'"';
					}
				}
				
				if(!empty($request->session()->get('submission_one_enbd_loan_inner_mis')) && $request->session()->get('submission_one_enbd_loan_inner_mis') != 'All')
				{
					$submissionOne = $request->session()->get('submission_one_enbd_loan_inner_mis');
					 $selectedFilter['submission_date_one'] = $submissionOne;
					 if($whereraw == '')
					{
						$whereraw = 'date_of_submission like "%'.date("Y-m-d",strtotime($submissionOne)).'%"';
					}
					else
					{
						$whereraw .= ' And date_of_submission like "%'.date("Y-m-d",strtotime($submissionOne)).'%"';
					}
				}
				if(!empty($request->session()->get('status_enbd_loan_inner_mis')) && $request->session()->get('status_enbd_loan_inner_mis') != 'All')
				{
					$statusLoan = $request->session()->get('status_enbd_loan_inner_mis');
					 $selectedFilter['loan_status'] = $statusLoan;
					 if($whereraw == '')
					{
						$whereraw = 'STATUS = "'.$statusLoan.'"';
					}
					else
					{
						$whereraw .= ' And STATUS = "'.$statusLoan.'"';
					}
				}
				if(!empty($request->session()->get('scheme_enbd_loan_inner_mis')) && $request->session()->get('scheme_enbd_loan_inner_mis') != 'All')
				{
					$scheme = $request->session()->get('scheme_enbd_loan_inner_mis');
					 $selectedFilter['SCHEME'] = $scheme;
					 if($whereraw == '')
					{
						$whereraw = 'SCHEME = "'.$scheme.'"';
					}
					else
					{
						$whereraw .= ' And SCHEME = "'.$scheme.'"';
					}
				}
				
				if(!empty($request->session()->get('bank_enbd_loan_inner_mis')) && $request->session()->get('bank_enbd_loan_inner_mis') != 'All')
				{
					$bank = $request->session()->get('bank_enbd_loan_inner_mis');
					 $selectedFilter['Bank'] = $bank;
					 if($whereraw == '')
					{
						$whereraw = 'BANK = "'.$bank.'"';
					}
					else
					{
						$whereraw .= ' And BANK = "'.$bank.'"';
					}
				}
				
				
				if($whereraw != '')
				{
					$reports = ENDBLoanMis::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue);
				}
				else
				{
					$reports = ENDBLoanMis::orderBy("id","DESC")->paginate($paginationValue);
				}
				$reports->setPath(config('app.url/listingENBDLoan'));
				
				
				
				if($whereraw != '')
				{
					
					$reportsCount = ENDBLoanMis::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = ENDBLoanMis::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				$autoSelectRowId = '';
				if(!empty($request->session()->get('mis_return_id')))
				{
					$autoSelectRowId = $request->session()->get('mis_return_id');
				}
				/*
				*get all employee list from loan mis
				*start code
				*/
				$employeeListArray = array();
				if($whereraw == '')
				{
				$employeeList = ENDBLoanMis::groupBy("employee_id")->selectRaw('count(*) as total, employee_id')->get();
				}
				else
				{
					$employeeList = ENDBLoanMis::groupBy("employee_id")->selectRaw('count(*) as total, employee_id')->whereRaw($whereraw)->get();
					
				}
				foreach($employeeList as $_emp)
				{
					if($_emp->employee_id != '' && $_emp->employee_id != NULL)
					{
						$employeeMainList = Employee_details::where("id",$_emp->employee_id)->first();
						$employeeListArray[$_emp->employee_id] = $employeeMainList->first_name.' '.$employeeMainList->middle_name.' '.$employeeMainList->last_name.'_'.$employeeMainList->source_code;
					}
				}
				/* echo '<pre>';
				print_r($employeeListArray);
				exit; */
				/*
				*get all employee list from loan mis
				*end code
				*/
				
				
				/*
				*get all employee list from loan mis
				*start code
				*/
				$sourcingArray = array();
				if($whereraw == '')
				{
				$sourceList = ENDBLoanMis::groupBy("SOURCING")->selectRaw('count(*) as total, SOURCING')->get();
				}
				else
				{
					
					$sourceList = ENDBLoanMis::groupBy("SOURCING")->selectRaw('count(*) as total, SOURCING')->whereRaw($whereraw)->get();
					
				}
				
				foreach($sourceList as $_s)
				{
					$sourcingArray[$_s->SOURCING] = $_s->SOURCING;
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				
				
				
				/*
				*get all employee list from loan mis
				*start code
				*/
				$customerNameArray = array();
				if($whereraw == '')
				{
				$customerList = ENDBLoanMis::groupBy("CM_NAME")->selectRaw('count(*) as total, CM_NAME')->get();
				}
				else
				{
					
					$customerList = ENDBLoanMis::groupBy("CM_NAME")->selectRaw('count(*) as total, CM_NAME')->whereRaw($whereraw)->get();
					
				}
				
				foreach($customerList as $_c)
				{
					$customerNameArray[$_c->CM_NAME] = $_c->CM_NAME;
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				
				
				/*
				*get all employee list from loan mis
				*start code
				*/
				$submissionArray = array();
				if($whereraw == '')
				{
				$submissionGet = ENDBLoanMis::groupBy("date_of_submission")->selectRaw('count(*) as total, date_of_submission')->get();
				}
				else
				{
					
					$submissionGet = ENDBLoanMis::groupBy("date_of_submission")->selectRaw('count(*) as total, date_of_submission')->whereRaw($whereraw)->get();
					
				}
				
				foreach($submissionGet as $_d)
				{
					$submissionArray[$_d->date_of_submission] = date("d M Y",strtotime($_d->date_of_submission));
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				
				
				/*
				*get all employee list from loan mis
				*start code
				*/
				$appIdArray = array();
				if($whereraw == '')
				{
				$appidGet = ENDBLoanMis::groupBy("app_id")->selectRaw('count(*) as total, app_id')->get();
				}
				else
				{
					
					$appidGet = ENDBLoanMis::groupBy("app_id")->selectRaw('count(*) as total, app_id')->whereRaw($whereraw)->get();
					
				}
				
				foreach($appidGet as $_d)
				{
					if($_d->app_id != NULL && $_d->app_id != '')
					{
						$appIdArray[$_d->app_id] = $_d->app_id;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				
				
				
				/*
				*get all employee list from loan mis
				*start code
				*/
				$statusArray = array();
				if($whereraw == '')
				{
				$statusGet = ENDBLoanMis::groupBy("STATUS")->selectRaw('count(*) as total, STATUS')->get();
				}
				else
				{
					
					$statusGet = ENDBLoanMis::groupBy("STATUS")->selectRaw('count(*) as total, STATUS')->whereRaw($whereraw)->get();
					
				}
				
				foreach($statusGet as $_stat)
				{
					if($_stat->STATUS != NULL && $_stat->STATUS != '')
					{
						$statusArray[$_stat->STATUS] = $_stat->STATUS;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				
				/*
				*get all employee list from loan mis
				*start code
				*/
				$schemeArray = array();
				if($whereraw == '')
				{
				$schemeGet = ENDBLoanMis::groupBy("SCHEME")->selectRaw('count(*) as total, SCHEME')->get();
				}
				else
				{
					
					$schemeGet = ENDBLoanMis::groupBy("SCHEME")->selectRaw('count(*) as total, SCHEME')->whereRaw($whereraw)->get();
					
				}
				
				foreach($schemeGet as $_scheme)
				{
					if($_scheme->SCHEME != NULL && $_scheme->SCHEME != '')
					{
						$schemeArray[$_scheme->SCHEME] = $_scheme->SCHEME;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				
				
				/*
				*get all employee list from loan mis
				*start code
				*/
				$bankNameArray = array();
				if($whereraw == '')
				{
				$bankGet = ENDBLoanMis::groupBy("BANK")->selectRaw('count(*) as total, BANK')->get();
				}
				else
				{
					
					$bankGet = ENDBLoanMis::groupBy("BANK")->selectRaw('count(*) as total, BANK')->whereRaw($whereraw)->get();
					
				}
				
				foreach($bankGet as $_bank)
				{
					if($_bank->BANK != NULL && $_bank->BANK != '')
					{
						$bankNameArray[$_bank->BANK] = $_bank->BANK;
					}
				}
				
				/*
				*get all employee list from loan mis
				*end code
				*/
				$request->session()->put('mis_return_id','');
				return view("MISLoan/Loan/listingENBDLoan",compact('reports','reportsCount','paginationValue','employees','selectedFilter','autoSelectRowId','employeeListArray','sourcingArray','customerNameArray','submissionArray','appIdArray','statusArray','schemeArray','bankNameArray'));
			}
		   
		   
		   public function updateMisReportENBDLoan(Request $request)
		   {
			   $rowId = $request->rowid;
			   $misRecords = ENDBLoanMis::where("id",$rowId)->first();
			   $tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				$tL_details = Employee_details::where("job_role",'Team Leader')->where("dept_id",9)->get();
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->where("tl_id",$misRecords->TL_NAME)->get();
				
				$NATs = Attributes::where("attribute_id",38)->first();
				$natValues = json_decode($NATs->opt_option);
				$banks = BankDetailsUAE::where("status",1)->get();
				return view("MISLoan/Loan/updateMisReportENBDLoan",compact('tL_details','agent_details','natValues','banks','misRecords'));
		   }
		   
		   
		   public function getManualENBDLoanUpdate(Request $request)
			{
				 $rowId = $request->rowid;
				 $loanType = $request->loanType;
				 $cList = WpCountries::get();
			   $misRecords = ENDBLoanMis::where("id",$rowId)->first();
				$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				$tL_details = Employee_details::whereIn("emp_id",$tL_id)->get();
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->get();
				$LoanStatusList = LoanStatus::where("status",1)->get();
				
				$LoanSchemeList = LoanScheme::where("status",1)->get();
				$NATs = Attributes::where("attribute_id",38)->first();
				$natValues = json_decode($NATs->opt_option);
				$banks = LoanBanks::where("status",1)->get();
				return view("MISLoan/Loan/getManualENBDLoanUpdate",compact('LoanStatusList','LoanSchemeList','tL_details','agent_details','natValues','banks','misRecords','rowId','loanType','cList'));
			}
			
			
			public function getManualENBDCardsUpdateTab(Request $request)
			{
				 $rowId = $request->rowid;
			   $misRecords = MainMisReport::where("id",$rowId)->first();
				$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->where("attribute_values","SALES MANAGER")->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				$tL_details = Employee_details::whereIn("emp_id",$tL_id)->get();
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->get();
				$currentActMod = CurrentActivity::where("status",1)->get();
				$enbdCardsStatus = ENDBCARDStatus::where("status",1)->get();
				$MonthlyEndsValues = MonthlyEnds::where("status",1)->get();
				$NATs = Attributes::where("attribute_id",38)->first();
				$natValues = json_decode($NATs->opt_option);
				$banks = BankDetailsUAE::where("status",1)->get();
				return view("MIS/getManualENBDCardsUpdateTab",compact('currentActMod','enbdCardsStatus','MonthlyEndsValues','tL_details','agent_details','natValues','banks','misRecords','rowId'));
			}
			
			public function getManualENBDCardsFinalUpdate(Request $request)
			{
				
				$requestParameters = $request->input();
				
				/*save First Step Data*/
				$rowId = $requestParameters['row_id'];
				 $misReportObj = MainMisReport::find($rowId);
			   $misReportObj->date_of_submission = $requestParameters['date_of_submission'];
			      $misReportObj->submission_format = date("Y-m-d",strtotime($requestParameters['date_of_submission']));
			   $misReportObj->application_type = $requestParameters['application_type'];
			   $misReportObj->application_id = $requestParameters['application_id'];
			  
			   $misReportObj->current_activity = $requestParameters['current_activity'];
			   $misReportObj->monthly_ends = $requestParameters['monthly_ends'];
			  
			   
			   $misReportObj->fv_company_name = $requestParameters['fv_company_name'];
			   $misReportObj->company_name_as_per_visa = $requestParameters['company_name_as_per_visa'];
			   $misReportObj->ALE_NALE = $requestParameters['ALE_NALE'];
			   $misReportObj->LOS = $requestParameters['LOS'];
			   $misReportObj->ACCOUNT_STATUS = $requestParameters['ACCOUNT_STATUS'];
			   if($requestParameters['ACCOUNT_STATUS'] == 1)
			   {
				    $misReportObj->other_bank = $requestParameters['other_bank'];
			   }
			   $misReportObj->ACCOUNT_NO = $requestParameters['ACCOUNT_NO'];
			   $misReportObj->TL = $requestParameters['TL'];
			  
			   $misReportObj->lead_source = $requestParameters['lead_source'];
			   $misReportObj->PRODUCT = $requestParameters['PRODUCT'];
			   $misReportObj->file_source = $requestParameters['file_source'];
			   $misReportObj->SE_CODE_NAME = $requestParameters['SE_CODE_NAME'];
			  
			   $misReportObj->iban = $requestParameters['iban'];
			   $misReportObj->approved_notapproved = $requestParameters['approved_notapproved'];
			   $misReportObj->last_remarks_added = $requestParameters['last_remarks_added'];
			  
			   $misReportObj->created_by = $request->session()->get('EmployeeId');
			   $scCode = $requestParameters['SE_CODE_NAME'];
			   
			   
			   if(!empty($scCode))
						{
							$scCodeArray = explode("_",$scCode);
							if(isset($scCodeArray[1]))
							{
								$bank_code = $scCodeArray[1];
								$employeeDetails = Employee_details::where("source_code",$bank_code)->first();
								if($employeeDetails != '')
								{
								$misReportObj->employee_id =  $employeeDetails->id;
								$misReportObj->Employee_status = "Verified";
								}
								else
								{
									$misReportObj->Employee_status = "Not-Verified";
								}
							}
							else
							{
								$misReportObj->Employee_status = "Not-Verified";
							}
						}
					else
					{
						$misReportObj->Employee_status = "Not-Verified";
					}
			  $misReportObj->type_data = 'generated';
			 
			  
			  
			 
			  $misReportObj->save();
			  $misMainReportID = $misReportObj->id;
				/*save First Step Data*/
				
				
				$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->where("attribute_values","SALES MANAGER")->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				$tL_details = Employee_details::whereIn("emp_id",$tL_id)->get();
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->get();
				$currentActMod = CurrentActivity::where("status",1)->get();
				$enbdCardsStatus = ENDBCARDStatus::where("status",1)->get();
				$MonthlyEndsValues = MonthlyEnds::where("status",1)->get();
				$NATs = Attributes::where("attribute_id",38)->first();
				$natValues = json_decode($NATs->opt_option);
				$banks = BankDetailsUAE::where("status",1)->get();
				$misRecords = MainMisReport::where("id",$rowId)->first();
				return view("MIS/getManualENBDCardsFinalUpdate",compact('currentActMod','enbdCardsStatus','MonthlyEndsValues','tL_details','agent_details','natValues','banks','misMainReportID','misRecords'));
			}
			
			
			public function savePostENBDLoanMISUpdate(Request $request)
		   {
			    
				
			   $requestParameters = $request->input();
			  $product = $requestParameters['PRODUCT'];
				$mis_id = $requestParameters['mis_id'];
				$misReportObj = ENDBLoanMis::find($mis_id);
			   $misReportObj->CM_NAME = $requestParameters['CM_NAME'];
			   $misReportObj->MOBILE = $requestParameters['MOBILE'];
			   $misReportObj->SALARY = $requestParameters['SALARY'];
			   $misReportObj->app_id = $requestParameters['app_id'];
			   $misReportObj->PRE_CALLING_date = $requestParameters['PRE_CALLING_date'];
			   $misReportObj->COMMENTS = $requestParameters['COMMENTS'];
			  
			   $misReportObj->LOAN = $requestParameters['LOAN'];
			  
			   
			   $misReportObj->TENURE = $requestParameters['TENURE'];
			   $misReportObj->NATIONALITY = $requestParameters['NATIONALITY'];
			   $misReportObj->CHQ = $requestParameters['CHQ'];
			   $misReportObj->chq_req = $requestParameters['chq_req'];
			 
			   $misReportObj->PRE_CALLING = $requestParameters['PRE_CALLING'];
			   $misReportObj->TL_NAME = $requestParameters['TL_NAME'];
			   $misReportObj->SE_NAME = $requestParameters['SE_NAME'];
			   $misReportObj->PRODUCT = $requestParameters['PRODUCT'];
			   $misReportObj->date_of_submission = date("Y-m-d",strtotime($requestParameters['date_of_submission']));
			   $misReportObj->SOURCING = $requestParameters['SOURCING'];
			   $misReportObj->STATUS = $requestParameters['STATUS'];
			   $misReportObj->ROI = $requestParameters['ROI'];
			   $misReportObj->COMPANY_NAME = $requestParameters['COMPANY_NAME'];
			   $misReportObj->CAT = $requestParameters['CAT'];
			   if($requestParameters['internal_DISBURSAL'] != '')
				   {
				   $misReportObj->internal_DISBURSAL = date("Y-m-d",strtotime($requestParameters['internal_DISBURSAL']));
				   }
			    if($product != 'AUTO LOAN')
			   {
				$misReportObj->AECB = $requestParameters['AECB'];
			    $misReportObj->FPD_days = $requestParameters['FPD_days'];
			   }
			   else
			   {
				   if($requestParameters['mortgage_date'] != '')
				   {
				   $misReportObj->mortgage_date = date("Y-m-d",strtotime($requestParameters['mortgage_date']));
				   }
				   if($requestParameters['purchase_order'] != '')
				   {
				   $misReportObj->purchase_order = date("Y-m-d",strtotime($requestParameters['purchase_order']));
				   }
				   
				   
				   $misReportObj->vehicle_value = $requestParameters['vehicle_value'];
				   $misReportObj->Downpayment = $requestParameters['Downpayment'];
				    $misReportObj->carmake = $requestParameters['carmake'];
				   $misReportObj->carmodel = $requestParameters['carmodel'];
				   $misReportObj->modelyear = $requestParameters['modelyear'];
				   
			   }
			 
			   $misReportObj->SCHEME = $requestParameters['SCHEME'];
			   $misReportObj->BANK = $requestParameters['BANK'];
			   $misReportObj->FPD = $requestParameters['FPD'];
			  
			   if($requestParameters['BANK'] == 'other_bank')
			   {
				    $misReportObj->other_bank = $requestParameters['other_bank'];
			   }
			   $misReportObj->account_no = $requestParameters['account_no'];
			   
			  
			   $misReportObj->created_by = $request->session()->get('EmployeeId');
			  
			   $scCode = $requestParameters['SE_NAME'];
			   if(!empty($scCode))
						{
							$scCodeArray = explode("_",$scCode);
							if(isset($scCodeArray[1]))
							{
								$bank_code = $scCodeArray[1];
								$employeeDetails = Employee_details::where("source_code",$bank_code)->first();
								if($employeeDetails != '')
								{
								$misReportObj->employee_id =  $employeeDetails->id;
								$misReportObj->Employee_status = "Verified";
								}
								else
								{
									$misReportObj->Employee_status = "Not-Verified";
								}
							}
							else
							{
								$misReportObj->Employee_status = "Not-Verified";
							}
						}
					else
					{
						$misReportObj->Employee_status = "Not-Verified";
					}
			  $misReportObj->type_data = 'generated';
			 
			  
			  
			 
			 
			  
			
			  
			  $misReportObj->save();
			  
			  
			  /*
			  *check for Match Status from jonus loan
			  *start Coding
			  */
			  $appId = $requestParameters['app_id'];
			  $checkExistAppID = JonusLoan::where("APPLICATIONSID",$appId)->first();
			  if($checkExistAppID != '')
			  {
				 
				  $misReportObjMatch = ENDBLoanMis::find($mis_id);
				  $misReportObjMatch->match_status = 2;
				  $misReportObjMatch->DISBURSAL_STATUS = $checkExistAppID->DISBURSAL_STATUS;
				  $misReportObjMatch->DISBURSAL_DATETIME = $checkExistAppID->DISBURSAL_DATETIME;
				  $misReportObjMatch->DISBURSAL_DATETIME_format = date("Y-m-d",strtotime($checkExistAppID->DISBURSAL_DATETIME));
				  $misReportObjMatch->UAE_NATIONAL = $checkExistAppID->UAE_NATIONAL;
				  $misReportObjMatch->save();
				  $jonusMatch =  JonusLoan::find($checkExistAppID->id);
				   $jonusMatch->match_status = 2;
				   $jonusMatch->save();
			  }
			  /*
			  *check for Match Status from jonus loan
			  *start Coding
			  */
			  
				echo "<h3>Data Updated.</h3>";
		   }
		   
		   public function filterReport(Request $request)
		   {
			   $type = $request->type;
			   $request->session()->put('mis_enbd_cards_report_manual','');
			    $request->session()->put('mis_enbd_cards_report_manual_type','');
			    $request->session()->put('mis_enbd_cards_report_manual_to','');
			    $request->session()->put('mis_enbd_cards_report_manual_from','');
			   if($type == 'DS')
			   {
				 $request->session()->put('mis_enbd_cards_report_manual',date("Y-m-d"));  
				 $request->session()->put('mis_enbd_cards_report_manual_type',$type);  
			   }
			   else  if($type == 'ME')
			   {
				  $lastMonth = date('Y-m', strtotime("-1 month"));
				  $fromdate = $lastMonth.'-21';
				  $currentDate = date("d");
				  if($currentDate < 20)
				  {
					  $todate = date("Y-m-d");
				  }
				  else
				  {
					  $todate = date("Y-m").'-20';
				  }
				 $request->session()->put('mis_enbd_cards_report_manual_from',$fromdate);  
				 $request->session()->put('mis_enbd_cards_report_manual_to',$todate);  
				 $request->session()->put('mis_enbd_cards_report_manual_type',$type);  
			   }
			     else  if($type == 'Q')
			   {
				  $lastMonth = date('Y-m', strtotime("-3 month"));
				  $fromdate = $lastMonth.'-1';
				  $todate = date("Y-m-d");
				  
				 $request->session()->put('mis_enbd_cards_report_manual_from',$fromdate);  
				 $request->session()->put('mis_enbd_cards_report_manual_to',$todate);  
				 $request->session()->put('mis_enbd_cards_report_manual_type',$type);  
			   }
			   
		   }
		   
		   public function updateFilter(Request $request)
		   {
			    $filterList = array();
				$filterList['report'] = '';
				$filterList['sales'] = '';
			    if(!empty($request->session()->get('mis_enbd_cards_report_manual')))
				  {
					   $filterList['report'] = $request->session()->get('mis_enbd_cards_report_manual_type');
				  }
				else if(!empty($request->session()->get('mis_enbd_cards_report_manual_type')) && $request->session()->get('mis_enbd_cards_report_manual_type') == 'ME')
				{
					$filterList['report'] = $request->session()->get('mis_enbd_cards_report_manual_type');
				}
				else if(!empty($request->session()->get('mis_enbd_cards_report_manual_type')) && $request->session()->get('mis_enbd_cards_report_manual_type') == 'Q')
				{
					$filterList['report'] = $request->session()->get('mis_enbd_cards_report_manual_type');
				}
				else
				{
					//nothings
				}
				
				
			   if(!empty($request->session()->get('mis_enbd_cards_sales_se')))
				{
					$filterList['sales'] = $request->session()->get('mis_enbd_cards_sales_se');
				}
				
				return view("MIS/updateFilter",compact('filterList'));
		   }
		   
		   public function cancelFilters(Request $request)
		   {
			   $type = $request->type;
			   if($type == 'report')
			   {
				    $request->session()->put('mis_enbd_cards_report_manual','');  
					$request->session()->put('mis_enbd_cards_report_manual_type',''); 
					$request->session()->put('mis_enbd_cards_report_manual_from','');  
					$request->session()->put('mis_enbd_cards_report_manual_to','');  					
			   }
			   if($type == 'sales')
			   {
				    $request->session()->put('mis_enbd_cards_sales_se','');  
			   }
		   }
		   
		   public function getDetailsAsperLoc(Request $request)
		   {
			  $loc =  $request->loc;
			  
			  $tlList=array();
			  $TLMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
			  foreach($TLMod as $_tl)
			  {
				 
				  $tlList[] = $_tl->emp_id;
			  }
			 $tL_details = Employee_details::whereIn("emp_id",$tlList)->get();
			 
			 
			 
			 	$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->get();
				return view("MIS/getDetailsAsperLoc",compact('tL_details','agent_details','loc'));
		   }
		   
		   public function filterSales(Request $request)
		   {
			   $request->session()->put('mis_enbd_cards_sales_se','');  
			    $seid =  $request->seid;
				$request->session()->put('mis_enbd_cards_sales_se',$seid);  
		   }
		  
		   
		    public static function getDaysMonth($month,$year)
		   {
			  
			   return date('t', mktime(0, 0, 0, $month, 1, $year));
		   }
		    public static function getDaysMonthLastMonth($month,$year)
		   {
			   $month = $month-1;
			  if($month == 0)
			  {
				  $month = 12;
				  $year = $year-1;
			  }
			
			   return date('t', mktime(0, 0, 0, $month, 1, $year));
		   }
		   
		    public static function getfirstDayName($month,$year)
		   {
			  $dateS = $year.'-'.$month.'-01';
			   return date('D', strtotime($dateS));
		   }
		   
		   
		   
		   public static function uploadedCount($month,$year,$type)
		   {
			   return JonusReportLog::whereMonth("uploaded_date",$month)->whereYear("uploaded_date",$year)->where("type",$type)->get()->count();
		   }
		   
		   public function exportEmp(Request $request)
		   {
			   $filename = 'AllEmployees.csv';
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'";'); 
			$allEmployee = Employee_details::get();
			$header = array();
			$header[] = 'Employee Id';
			$header[] = 'First Name';
			$header[] = 'Middle Name';
			$header[] = 'Last Name';
			$header[] = 'Passport Number';
			$header[] = 'Email';
			$header[] = 'Contact Number';
			$header[] = 'Person Code';
		
			$f = fopen('php://output', 'w');
			fputcsv($f, $header, ',');
       
			
			
			/*
			*get List of holidays
			*/
			
			
						
			foreach ($allEmployee as $emp) {
				$values = array();
				$values[] = $emp->emp_id;
				$values[] =  $emp->first_name;
				$values[] =  $emp->middle_name;
				$values[] =  $emp->last_name;
				
				$values[] =	Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","PP_NO")->first()->attribute_values;
				$values[] =	Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","email")->first()->attribute_values;
				$values[] =	Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","CONTACT_NUMBER")->first()->attribute_values;
				$values[] =	Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","person_code")->first()->attribute_values;
				
				
				fputcsv($f, $values, ',');
				/* echo '<pre>';
				print_r($values);
				exit; */
			}
			
			exit();
		   }
		   
		   public function graphDS()
		   {
			   return view("MIS/graphDS");
		   }
		   public function checkMonthlyEndByAppId(Request $request)
		   {
			   $appId =  $request->AppIdValue;
			  $arrayResponse = array();
			  $arrayResponse['code'] = '300';
			   $currentAct = ENBDCardsMisReport::where("application_id",$appId)->first();
			   if($currentAct != '')
			   {
				   if($currentAct->match_status == 2)
				   {
					      $arrayResponse['code'] = '302';
						  $arrayResponse['message'] = "Given APPID already connected to another MIS.";
				   }
				   else
				   {
					   $cAct = '';
					   $status = '';
							if(trim($currentAct->CURRENTACTIVITY) == 'End')
								{
								$cAct = 2;
								}
								else if(trim($currentAct->CURRENTACTIVITY) == 'Reject Review')
								{
								$cAct  = 6;
								}
								else if(trim($currentAct->CURRENTACTIVITY) == 'Single Data Entry')
								{
								$cAct  = 7;
								}
								else if(trim($currentAct->CURRENTACTIVITY) == 'Document Verification')
								{
								$cAct = 12;
								}
								else if(trim($currentAct->CURRENTACTIVITY) == 'Underwriting')
								{
								$cAct = 8;
								}
								else if(trim($currentAct->CURRENTACTIVITY) == 'Detail Data Entry')
								{
								$cAct = 11;
								}
								else if(trim($currentAct->CURRENTACTIVITY) == 'CANCEL')
								{
								$cAct = 1;
								}
								else if(trim($currentAct->CURRENTACTIVITY) == 'VERIFICATION DETAIL')
								{
								$cAct = 13;
								}
								else if(trim($currentAct->CURRENTACTIVITY) == 'HOLD RCC')
								{
								$cAct = 3;
								}
								else if(trim($currentAct->CURRENTACTIVITY) == 'HOLD SOURCING')
								{
								$cAct = 4;
								}
								
								if(trim($currentAct->STATUS) == 'STARTED')
								{
								$status = 7;
								}
								elseif(trim($currentAct->STATUS) == 'COMPLETED')
								{
								$status = 1;
								}
								
									  $arrayResponse['code'] = '200';
									  $arrayResponse['current_act'] = $cAct;
									  $arrayResponse['approved_not_approved'] = $status;
									
								
				   }
			   }
			   
			   echo json_encode($arrayResponse);
			   exit;
		   }
		   
		   
		   
		 
		   
		   public function setProductForMIS(Request $request)
		   {
			   $request->session()->put('product_type_filter',$request->product);
			   $request->session()->put('emp_enbd_loan_inner_mis','All');
				$request->session()->put('sourcing_enbd_loan_inner_mis','All');
				$request->session()->put('appid_enbd_loan_inner_mis','All');
				$request->session()->put('cus_name_enbd_loan_inner_mis','All');
				$request->session()->put('submission_one_enbd_loan_inner_mis','All');
				$request->session()->put('status_enbd_loan_inner_mis','All');
				$request->session()->put('scheme_enbd_loan_inner_mis','All');
				$request->session()->put('bank_enbd_loan_inner_mis','All');
				$request->session()->put('submission_from_enbd_loan_inner_mis','');
				$request->session()->put('submission_to_enbd_loan_inner_mis','');
			   return  redirect('manageMisLoan');
		   }
		   
		 
		   
		   function checkAppIdLoanENBD(Request $request)
		   {
			   $result['code'] = 200;
			   $appID =  $request->appID;
			   $mis_id =  $request->mis_id;
			   $apiCheck = ENDBLoanMis::where("app_id",$appID)->first();
			   if($apiCheck != '')
			   {
				   if($apiCheck->id != $mis_id)
				   {
				   $result['code'] = 302;
				   $result['message'] = 'Application Id Already exist with other mis';
				   }
			   }
			   echo json_encode($result);
			   exit;
			   
		   }
		   
		   public function findAppIdLoan(Request $request)
			{
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->get();
				if(!empty($request->session()->get('margingoffersetLoan')))
				{
					$paginationValue = $request->session()->get('margingoffersetLoan');
				}
				else
				{
					$paginationValue = 10;
				}
				return view("MISLoan/Loan/findAppIdLoan",compact('paginationValue','agent_details'));
			}
			
			public function listingInternalMisMargeLoan(Request $request)
			{
				$se = $request->se;
				$product = $request->product;
				 $whereraw = '';
			  $selectedFilter['customer_name'] = '';
			  $selectedFilter['employee_id'] = '';
			  $selectedFilter['report'] = '';
			  $whereraw = "(app_id = '' OR app_id IS NULL)";
			  if($se != 'All')
			  {
				  $agentArray = explode("_",$se);
				  $agentBackCode = $agentArray[1];
				 $employeeDetail =  Employee_details::where("source_code",$agentBackCode)->first();
				 if($employeeDetail != '')
				 {
				  $whereraw .= ' AND (employee_id='.$employeeDetail->id.')';
				 }
			  }
			   if($product != 'All')
			  {
				 
				  $whereraw .= ' AND (PRODUCT="'.$product.'")';
				 
			  }
			  
			/* 	echo $whereraw;exit; */
				if(!empty($request->session()->get('margingoffersetLoan')))
				{
					$paginationValue = $request->session()->get('margingoffersetLoan');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if($whereraw != '')
				{
					$reports = ENDBLoanMis::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue)->onEachSide(0);
				}
				else
				{
					$reports = ENDBLoanMis::orderBy("id","DESC")->paginate($paginationValue)->onEachSide(0);
				}
				$reports->setPath(config('app.url/listingInternalMisMargeLoan'));
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if($whereraw != '')
				{
					
					$reportsCount = ENDBLoanMis::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = ENDBLoanMis::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				return view("MISLoan/Loan/listingInternalMisMargeLoan",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
			}
			
			public function listingENBDMisMargeLoan(Request $request)
			{
				$se = $request->se;
				$product = $request->product;
				$whereraw = '';
			  $selectedFilter['submission_from'] = '';
			  $selectedFilter['submission_to'] = '';
			  $selectedFilter['report'] = '';
			  $whereraw = 'match_status = 1';
			  if($se != 'All')
			  {
				  $agentArray = explode("_",$se);
				  $agentBackCode = $agentArray[1];
				
				
				  $whereraw .= ' AND P1CODE="'.$agentBackCode.'"';
				
			  }
			  
			  if($product != 'All')
			  {
				 
				
				
				  $whereraw .= ' AND PRODUCT="'.$product.'"';
				
			  }
			if(!empty($request->session()->get('marge_enbd_submission_from_loan')))
				{
					$submissionForm = $request->session()->get('marge_enbd_submission_from_loan');
					$selectedFilter['submission_from'] = $submissionForm;
					if($whereraw == '')
					{
						$whereraw = "date_sourcing >= '".$submissionForm."'";
					}
					else
					{
						$whereraw .= " And date_sourcing  >= '".$submissionForm."'";
					}
				}
				else
				{
					$submissionForm = date("Y-m-d",strtotime("-60 days"));
					$selectedFilter['submission_from'] = $submissionForm;
					if($whereraw == '')
					{
						$whereraw = "date_sourcing >= '".$submissionForm."'";
					}
					else
					{
						$whereraw .= " And date_sourcing  >= '".$submissionForm."'";
					}
				}
				
				
				if(!empty($request->session()->get('marge_enbd_submission_to_loan')))
				{
					$submissionTo = $request->session()->get('marge_enbd_submission_to_loan');
					$selectedFilter['submission_to'] = $submissionTo;
					if($whereraw == '')
					{
						$whereraw = "date_sourcing <= '".$submissionTo."'";
					}
					else
					{
						$whereraw .= " And date_sourcing <= '".$submissionTo."'";
					}
				}
				else
				{
					$submissionTo = date("Y-m-d");
					$selectedFilter['submission_to'] = $submissionTo;
					if($whereraw == '')
					{
						$whereraw = "date_sourcing <= '".$submissionTo."'";
					}
					else
					{
						$whereraw .= " And date_sourcing <= '".$submissionTo."'";
					}
				}
				
			//echo $whereraw;exit;
				if(!empty($request->session()->get('margingoffersetLoan')))
				{
					
					$paginationValue = $request->session()->get('margingoffersetLoan');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if($whereraw != '')
				{
					$reports = JonusLoan::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue)->onEachSide(0);
				}
				else
				{
				
					$reports = JonusLoan::orderBy("id","DESC")->paginate($paginationValue)->onEachSide(0);
				}
				$reports->setPath(config('app.url/listingENBDMisMargeLoan'));
				
				
				
				
				
				if($whereraw != '')
				{
					
					$reportsCount = JonusLoan::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = JonusLoan::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				
				return view("MISLoan/Loan/listingENBDMisMargeLoan",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
			}
			
			public function margeOffSetLoan(Request $request)
			{
				 $marge_offset = $request->marge_offset;
				 $request->session()->put('margingoffersetLoan',$marge_offset); 
				 return  redirect('manageMisLoan');				 
			}
			
			public function submissionRangeFilterMargingLoan(Request $request)
			{
				$subFrom = $request->subFrom;
				$subTo = $request->subTo;
				$bank_code = $request->bank_code;
				$request->session()->put('marge_enbd_submission_from_loan', date("Y-m-d",strtotime($subFrom)));
				$request->session()->put('marge_enbd_submission_to_loan',date("Y-m-d",strtotime($subTo)));
				return  redirect('listingENBDMisMargeLoan/'.$bank_code);
			}
			
			public function margeConfirmationLoan(Request $request)
			{
				$result = array();
				$internalMis = $request->internalMis;
				$enbdMis = $request->enbdMis;
				$enbdMisA = explode("_",$enbdMis);
				$internalMisA = explode("_",$internalMis);
				$result['appId'] = JonusLoan::where("id",$enbdMisA[2])->first()->APPLICATIONSID;
				$result['cm_name'] = ENDBLoanMis::where("id",$internalMisA[2])->first()->CM_NAME;
				$result['employee_id'] = $this->getEmployeeName(ENDBLoanMis::where("id",$internalMisA[2])->first()->employee_id);
				echo json_encode($result);
				exit;
				
				
			}
			
			
			public function mergeAppIdWithMISLoan(Request $request)
				{
					$misId = explode("_",$request->misId);
					$jonusId = explode("_",$request->jonusId);
					
					$misIdValue = $misId[2];
					$jonusIdValue = $jonusId[2];
					
											
					/*
					*Marging with JonusLoan
					*/
					$checkExistAppID = JonusLoan::where("id",$jonusIdValue)->first();
					if($checkExistAppID != '')
					{
					  $misReportObjMatch = ENDBLoanMis::find($misIdValue);
					  $misReportObjMatch->match_status = 2;
					  $misReportObjMatch->DISBURSAL_STATUS = $checkExistAppID->DISBURSAL_STATUS;
					  $misReportObjMatch->DISBURSAL_DATETIME = $checkExistAppID->DISBURSAL_DATETIME;
					  $misReportObjMatch->DISBURSAL_DATETIME_format = date("Y-m-d",strtotime($checkExistAppID->DISBURSAL_DATETIME));
					  $misReportObjMatch->UAE_NATIONAL = $checkExistAppID->UAE_NATIONAL;
					  $misReportObjMatch->app_id = $checkExistAppID->APPLICATIONSID;
					  $misReportObjMatch->save();
					  $jonusMatch =  JonusLoan::find($jonusIdValue);
				      $jonusMatch->match_status = 2;
				      $jonusMatch->save();
					  echo "Updated";
					  exit;
					}
					else
					{
						echo "done";
						exit;
					}
				   /*
					*Marging with JonusLoan
					*/
					echo "done1";
						exit;
				}
				
				
				public function exportMisReportLoan(Request $request)
{
	$parameters = $request->input(); 
	
	         $selectedId = $parameters['selectedIds'];
	        $filename = 'mis_loan_report_'.date("d-m-Y-h-i-s").'.csv';
				/*
				*Export Logs
				*/
				$exportLogsObj = new ExportReportLogs();
				$exportLogsObj->download_area = 'MIS Loan Report';
				$exportLogsObj->download_filename = $filename;
				$exportLogsObj->downloaded_by = $request->session()->get('EmployeeId');
				$exportLogsObj->save();
				/*
				*export Logs
				*/
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'";'); 
			$header = array();
			$header[] = strtoupper('Data of Submission');
			$header[] = strtoupper('Application ID');
			$header[] = strtoupper('PRODUCT');
			$header[] = strtoupper('SOURCING');
			$header[] = strtoupper('STATUS');
			$header[] = strtoupper('SCHEME');
			$header[] = strtoupper('FPD');
			$header[] = strtoupper('ROI');
			
			$header[] = strtoupper('Customer Name');
			$header[] = strtoupper('Customer Company Name');
			
			$header[] = strtoupper('Customer Mobile');
		
			$header[] = strtoupper('Salary');
			$header[] = strtoupper('Loan');
			$header[] = strtoupper('TENURE');
			$header[] = strtoupper('Bank');
			$header[] = strtoupper('ACCOUNT NO');
			$header[] = strtoupper('NATIONALITY');
			$header[] = strtoupper('Agent Name');
			$header[] = strtoupper('Agent Location');
			
			$header[] = strtoupper('DISBURSAL STATUS');
			$header[] = strtoupper('DISBURSAL DATETIME');
			$header[] = strtoupper('UAE_NATIONAL');
			
			$f = fopen(public_path('uploads/exportMIS/'.$filename), 'w');
			fputcsv($f, $header, ',');
			foreach ($selectedId as $sid) {
				 $misData = ENDBLoanMis::where("id",$sid)->first();
				$values = array();
				$values[] = date("d M Y",strtotime($misData->date_of_submission));
				$values[] = $misData->app_id;
				$values[] = strtoupper($misData->PRODUCT);
				$values[] = strtoupper($misData->SOURCING);
				$values[] = strtoupper($this->getloanStatusCSV($misData->STATUS));
				$values[] = strtoupper($this->getSchemeCSV($misData->SCHEME));
				$values[] = strtoupper($misData->FPD);
				$values[] = strtoupper($misData->ROI);
				$values[] = strtoupper($misData->CM_NAME);
				$values[] = strtoupper($misData->COMPANY_NAME);
				$values[] = strtoupper($misData->MOBILE);
				$values[] = strtoupper($misData->SALARY);
				$values[] = strtoupper($misData->LOAN);
				$values[] = strtoupper($misData->TENURE);
				if($misData->BANK != 'other_bank')
				{
				$values[] = strtoupper($this->getBankNameCSV($misData->BANK));
				}
				else
				{
					$values[] = strtoupper($misData->other_bank);
				}
				$values[] = strtoupper($misData->account_no);
				$values[] = strtoupper($misData->NATIONALITY);
				
				$values[] = strtoupper($this->getAgent($misData->employee_id));
				$values[] = strtoupper($this->getLocation($misData->employee_id));
				$values[] = strtoupper($misData->DISBURSAL_STATUS);
				$values[] = strtoupper($misData->DISBURSAL_DATETIME);
				$values[] = strtoupper($misData->UAE_NATIONAL);
				
				fputcsv($f, $values, ',');
			}
			
	echo $filename;
	exit;
}

public function getAgent($id)
{
	$employeeMod = Employee_details::where("id",$id)->first();
	if($employeeMod != '')
	{
		return $employeeMod->first_name." ".$employeeMod->middle_name.' '.$employeeMod->last_name;
	}
	else
	{
		return '--';
	}
}

public function uploadDocLoan(Request $request)
{
	$misid = $request->misid;
	
	return view("MISLoan/Loan/uploadDocLoan",compact('misid'));
}


public function downloadLoanPdf(Request $request)
{
	$misid = $request->misid;
	$misDocuments = MisDocuments::where("mis_id",$misid)->where("type","loan")->get();
	$imageArray  = array();
	foreach($misDocuments as $_mis)
	{
		
		$imageArray[]= $_mis->document_name;
		
	}
				$this->fpdf = new Fpdf;
				$this->FPDF_Merge = new FPDF_Merge;
				$this->fpdf->AddPage();
				$this->fpdf->SetFont('helvetica','',10);
				$x = 10;
				$y = 10;
				$index=1;
				foreach($imageArray as $imageName)
				{
					$imageNameArr = explode(".",$imageName);
					if(strtolower($imageNameArr[1]) != 'pdf')
					{
					if($index == 3)
					{
						$this->fpdf->AddPage();
						$this->fpdf->SetFont('helvetica','',10);
						$y = 10;
						$index = 1;
					}
					$this->fpdf->SetXY($x, $y);
					
					$this->fpdf->Cell(0, 0, $this->fpdf->Image('https://www.hr-suranigroup.com/uploads/loadDocuments/'.$imageName,10,$y,150,130), 0, 'L');
					$y = $y+150;
					$index++;
					}
					else
					{/* 
						 stream_wrapper_register('var', 'VariableStream');
						$data =  file_get_contents('https://www.hr-suranigroup.com/uploads/loadDocuments/'.$imageName);
						$v = 'img'.md5($data);
        $GLOBALS[$v] = $data;
        $a = getimagesize('var://'.$v);
        if(!$a)
            $this->Error('Invalid image data');
        $type = substr(strstr($a['mime'],'/'),1);
        $this->fpdf->Image('var://'.$v, $x, $y, $w, $h, $type, $link); */
						//$this->fpdf->Image($values);
						//$this->FPDF_Merge->add('/srv/www/htdocs/hrm/public/uploads/loadDocuments/'.$imageName);
					}
				}
				
				//$this->FPDF_Merge->add($this->fpdf->Output());
//$merge->add('doc2.pdf');
//$merge->output();
				$customerName = ENDBLoanMis::where("id",$misid)->first()->CM_NAME;
				 $pdfName = 'LoanDocuments_'.$customerName.'.pdf';
				
				$this->fpdf->Output('D',$pdfName); 
				
				
}

public function downloadOpt(Request $request)
{
	$misid = $request->misid;
	$misDocuments = MisDocuments::where("mis_id",$misid)->where("type","loan")->get();
	$imageArray  = array();
	foreach($misDocuments as $_mis)
	{
		
		$imageArray[]= $_mis->document_name;
		
	}
	$imageFound = 0;
	$pdfFound = array();
	
	foreach($imageArray as $imageName)
				{
					$imageNameArr = explode(".",$imageName);
					if(strtolower($imageNameArr[1]) != 'pdf')
					{
						$imageFound++;
					}
					else
					{
						$pdfFound[] = $imageName;
					}
					
				}
	return view("MISLoan/Loan/downloadOpt",compact('imageFound','pdfFound','misid'));
}
		 
		   public static function getPreCallFileStatus($MISid)
		   {
			   $precallStatus = PrecallingFile::where("mis_id",$MISid)->where("type",'loan')->orderBy("id","DESC")->first();
				if($precallStatus != '')
				{
					return 'exist';
				}
				else
				{
					return 'not';
				}
		   }
		   
		    public static function getPreCallFileName($MISid)
		   {
			   $precallStatus = PrecallingFile::where("mis_id",$MISid)->where("type",'loan')->orderBy("id","DESC")->first();
			if($precallStatus != '')
			{
				return $precallStatus->filename;
			}
			else
			{
				return 'not';
			}
		   }

	public function uploadPrecallingLoan(Request $request)
				{
					$misid = $request->misid;
					
					return view("MISLoan/Loan/uploadPrecallingLoan",compact('misid'));
				}	   
	public function dailyReportTeamsCardsLoan(Request $request)
				{
				
					$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				$tL_details = Employee_details::whereIn("emp_id",$tL_id)->get();
				$selectedTL = array();
				
					return view("MISLoan/Loan/dailyReportTeamsCardsLoan",compact('tL_details','selectedTL'));
				}	
		
	public function yieldReport(Request $request)
				{
				
					$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				$tL_details = Employee_details::whereIn("emp_id",$tL_id)->get();
				$selectedTL = array();
				
					return view("MISLoan/Loan/yieldReport",compact('tL_details','selectedTL'));
				}			
		public function reportingTeamDisplayLoan(Request $request)
				{
				
					$leaders = $request->leaders;
					$leadersArray = explode(",",$leaders);
					return view("MISLoan/Loan/reportingTeamDisplayLoan",compact('leadersArray'));
				}	
				
		public static function getsignedDate($appID)
		{
			$loan = JonusLoan::where("APPLICATIONSID",$appID)->first();
			if($loan != '')
			{
				return ' (SIGNED DATE - '.$loan->SIGNEDDATETIME.')';
			}
			else
			{
			return void(0);
			}
		}
		
		public function loadClientLoan(Request $request)
		{
			$client_Month = $request->client_Month;
			$client_product = $request->client_product;
			$client_Year = $request->client_Year;
			
			$newMonth = $client_Month-1;
			
				$currentYear = $client_Year;
						if($newMonth == 0)
						{
							$newMonth = 12;
							 $currentYear =  $currentYear-1;
						}
						
						
				$dateFrom = $currentYear.'-'.$newMonth.'-21';
				
				$dateTo = $client_Year.'-'.$client_Month.'-20';
				
				$loanMISData = ENDBLoanMis::where("match_status",2)->whereDate("date_of_submission",">=",$dateFrom)->whereDate("date_of_submission","<=",$dateTo)->whereIn("DISBURSAL_STATUS",array('Fully Disbursed','Partially disbursed'))->where("PRODUCT",$client_product)->get();
				$loanMISData = ENDBLoanMis::where("match_status",2)->whereDate("date_of_submission",">=",$dateFrom)->whereDate("date_of_submission","<=",$dateTo)->where("PRODUCT",$client_product)->get();
			   return view("MISLoan/Loan/loadClientLoan",compact('loanMISData','client_Month','client_product','client_Year'));
		}
		
		function excelYieldReport(Request $request)
		{
			$client_Month = $request->client_Month;
			$client_product = $request->client_product;
			$client_Year = $request->client_Year;
			
			$newMonth = $client_Month-1;
			
				$currentYear = $client_Year;
						if($newMonth == 0)
						{
							$newMonth = 12;
							 $currentYear =  $currentYear-1;
						}
						
						
				$dateFrom = $currentYear.'-'.$newMonth.'-21';
				
				$dateTo = $client_Year.'-'.$client_Month.'-20';
				
				$loanMISData = ENDBLoanMis::where("match_status",2)->whereDate("date_of_submission",">=",$dateFrom)->whereDate("date_of_submission","<=",$dateTo)->whereIn("DISBURSAL_STATUS",array('Fully Disbursed','Partially disbursed'))->where("PRODUCT",$client_product)->get();
				$loanMISData = ENDBLoanMis::where("match_status",2)->whereDate("date_of_submission",">=",$dateFrom)->whereDate("date_of_submission","<=",$dateTo)->where("PRODUCT",$client_product)->get();
				
				 $filename = 'Yield_Report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
				
			$indexCounter = 1;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('CM NAME'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('LOAN AMOUNT'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('EFFECTIVE ROI'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('W ROI'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexSN = 1;
			  $totalLoan = 0;
				  $totalwroi = 0;
			 foreach($loanMISData as $loan)
			 {
				 $indexCounter++;
				 $loanA = $loan->LOAN;
				  $totalLoan = $totalLoan+$loanA;
				  $roi = $loan->ROI;
				  $wrot = round(($loanA*$roi),2);
				  $totalwroi = $totalwroi+$wrot;
				 $sheet->setCellValue('A'.$indexCounter, strtoupper($indexSN))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper($loan->CM_NAME))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, number_format($loan->LOAN,2))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, $loan->ROI)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, number_format($wrot,2))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexSN++;
			 }
			 $indexCounter++;
			 $sheet->mergeCells('A'.$indexCounter.':B'.$indexCounter);
			 $sheet->setCellValue('A'.$indexCounter, 'TOTAL')->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('middle');
			 $sheet->setCellValue('C'.$indexCounter, $totalLoan)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('middle');
			 $sheet->setCellValue('D'.$indexCounter,'TOTAL')->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('middle');
			 $sheet->setCellValue('E'.$indexCounter,$totalwroi)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('middle');
			  $indexCounter++;
			  $sheet->mergeCells('A'.$indexCounter.':C'.$indexCounter);
			 $sheet->setCellValue('A'.$indexCounter, 'MONTHLY YIELD')->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('middle');
			 $sheet->mergeCells('D'.$indexCounter.':E'.$indexCounter);
			 $avgYield = round(($totalwroi/$totalLoan),2);
			 $sheet->setCellValue('D'.$indexCounter, $avgYield)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('middle');
			  foreach (range('A','E') as $col) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','E') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportMIS/'.$filename));	
				/*
				*Export Logs
				*/
				$exportLogsObj = new ExportReportLogs();
				$exportLogsObj->download_area = 'MIS Loan Yield Report';
				$exportLogsObj->download_filename = $filename;
				$exportLogsObj->downloaded_by = $request->session()->get('EmployeeId');
				$exportLogsObj->save();
				/*
				*export Logs
				*/
				echo $filename;
				exit;
		}
		
		public function searchLoanDateRange(Request $request)
		{
			$dateFrom = $request->dateFrom;
			$dateTo = $request->dateTo;
			$request->session()->put('submission_from_enbd_loan_inner_mis',$dateFrom);
			$request->session()->put('submission_to_enbd_loan_inner_mis',$dateTo);
			 $request->session()->put('emp_enbd_loan_inner_mis','All');
				$request->session()->put('sourcing_enbd_loan_inner_mis','All');
				$request->session()->put('appid_enbd_loan_inner_mis','All');
				$request->session()->put('cus_name_enbd_loan_inner_mis','All');
				$request->session()->put('submission_one_enbd_loan_inner_mis','All');
				$request->session()->put('status_enbd_loan_inner_mis','All');
				$request->session()->put('scheme_enbd_loan_inner_mis','All');
				$request->session()->put('bank_enbd_loan_inner_mis','All');
			 return  redirect('manageMisLoan');		
		}
		
		public function resetLoanDateRange(Request $request)
		{
			$request->session()->put('submission_from_enbd_loan_inner_mis','');
			$request->session()->put('submission_to_enbd_loan_inner_mis','');
			 return  redirect('manageMisLoan');	
		}
		
		public function resetAllLoan(Request $request)
		{
			$request->session()->put('sourcing_enbd_loan_inner_mis','');
			   $request->session()->put('emp_enbd_loan_inner_mis','');
			   $request->session()->put('submission_one_enbd_loan_inner_mis','');
			   $request->session()->put('submission_from_enbd_loan_inner_mis','');
			$request->session()->put('submission_to_enbd_loan_inner_mis','');
			$request->session()->put('appid_enbd_loan_inner_mis','');
			$request->session()->put('cus_name_enbd_loan_inner_mis','');
			$request->session()->put('scheme_enbd_loan_inner_mis','');
			$request->session()->put('bank_enbd_loan_inner_mis','');
			 return  redirect('manageMisLoan');	
		}
		public function filterByEmpLoan(Request $request)
		{
			$emp = $request->emp;
			$request->session()->put('emp_enbd_loan_inner_mis',$emp);
			 return  redirect('manageMisLoan');	
		}
		public function filterBySourcingLoan(Request $request)
		{
			$sourcing = $request->sourcing;
			$request->session()->put('sourcing_enbd_loan_inner_mis',$sourcing);
			 return  redirect('manageMisLoan');	
		}
		public function filterByAppid(Request $request)
		{
			$appid = $request->appid;
			$request->session()->put('appid_enbd_loan_inner_mis',$appid);
			 return  redirect('manageMisLoan');	
		}
		public function filterByCustomerName(Request $request)
		{
			$cusName = $request->cusName;
			$request->session()->put('cus_name_enbd_loan_inner_mis',$cusName);
			 return  redirect('manageMisLoan');	
		}
		
		public function filterBySubmission(Request $request)
		{
			$subDate = $request->subDate;
			$request->session()->put('submission_one_enbd_loan_inner_mis',$subDate);
			 return  redirect('manageMisLoan');	
		}
		
		public function filterByStatus(Request $request)
		{
			$loan_status = $request->loan_status;
			$request->session()->put('status_enbd_loan_inner_mis',$loan_status);
			 return  redirect('manageMisLoan');	
		}
		public function filterByScheme(Request $request)
		{
			$schemeId = $request->schemeId;
			$request->session()->put('scheme_enbd_loan_inner_mis',$schemeId);
			 return  redirect('manageMisLoan');	
		}
		public function filterByBank(Request $request)
		{
			$bankId = $request->bankId;
			$request->session()->put('bank_enbd_loan_inner_mis',$bankId);
			 return  redirect('manageMisLoan');	
		}
		
		public function mergeENBDToMISLoan(Request $request)
		{
			/*
					*Marging with JonusLoan
					*/
					$checkExistAppID = JonusLoan::where("match_status",1)->first();
					if($checkExistAppID != '')
					{
						$appId = $checkExistAppID->APPLICATIONSID;
					  $misReportObj = ENDBLoanMis::where('app_id',$appId)->first();
					  if($misReportObj != '')
					  {
						  $misReportObjMatch = ENDBLoanMis::find($misReportObj->id);
					  $misReportObjMatch->match_status = 2;
					  $misReportObjMatch->DISBURSAL_STATUS = $checkExistAppID->DISBURSAL_STATUS;
					  $misReportObjMatch->DISBURSAL_DATETIME = $checkExistAppID->DISBURSAL_DATETIME;
					  if($checkExistAppID->DISBURSAL_DATETIME != '')
					  {
					  $misReportObjMatch->DISBURSAL_DATETIME_format = date("Y-m-d",strtotime($checkExistAppID->DISBURSAL_DATETIME));
					  }
					  $misReportObjMatch->UAE_NATIONAL = $checkExistAppID->UAE_NATIONAL;
					  $misReportObjMatch->app_id = $checkExistAppID->APPLICATIONSID;
					  
					  if($checkExistAppID->application_status == 'COMPLETED')
							{
								$misReportObjMatch->approved_notapproved = 3;
							}
							elseif($checkExistAppID->application_status == 'REJECTED')
							{
								$misReportObjMatch->approved_notapproved = 5;
							}
							elseif($checkExistAppID->application_status == 'CANCELLED')
							{
								$misReportObjMatch->approved_notapproved = 2;
							}
							elseif($checkExistAppID->application_status == 'SENT_TO_CHECKER')
							{
								$misReportObjMatch->approved_notapproved = 7;
							}
							elseif($checkExistAppID->application_status == 'AO_COMPLETED_REJECT_REVIEW')
							{
								$misReportObjMatch->approved_notapproved = 7;
							}
							elseif($checkExistAppID->application_status == 'SENT_TO_COMPLIANCE')
							{
								$misReportObjMatch->approved_notapproved = 7;
							}
							elseif($checkExistAppID->application_status == 'SUBMITTED')
							{
								$misReportObjMatch->approved_notapproved = 7;
							}
							elseif($checkExistAppID->application_status == 'REJECT_REVIEW')
							{
								$misReportObjMatch->approved_notapproved = 7;
							}
							elseif($checkExistAppID->application_status == 'REJECTED_BY_CHECKER')
							{
								$misReportObjMatch->approved_notapproved = 7;
							}
							elseif($checkExistAppID->application_status == 'SENT_TO_RCC')
							{
								$misReportObjMatch->approved_notapproved = 7;
							}
							elseif($checkExistAppID->application_status == 'AO_COMPLETED_REFERRED_BY_RCC')
							{
								$misReportObjMatch->approved_notapproved = 7;
							}
							elseif($checkExistAppID->application_status == 'REFERRED_BY_RCC')
							{
								$misReportObjMatch->approved_notapproved = 7;
							}
							elseif($checkExistAppID->application_status == 'ERROR_IN_PROCESSING')
							{
								$misReportObjMatch->approved_notapproved = 7;
							}
							elseif($checkExistAppID->application_status == 'AO_COMPLETED_SENT_TO_RCC')
							{
								$misReportObjMatch->approved_notapproved = 7;
							}
							elseif($checkExistAppID->application_status == 'REFERRED_BY_COMPLIANCE')
							{
								$misReportObjMatch->approved_notapproved = 7;
							}
							elseif($checkExistAppID->application_status == 'CANCELLED_FSK_HIT')
							{
								$misReportObjMatch->approved_notapproved = 2;
							}
					  $misReportObjMatch->save();
					  $jonusMatch =  JonusLoan::find($checkExistAppID->id);
				      $jonusMatch->match_status = 2;
				      $jonusMatch->save();
					 
					  echo "Updated Loan";
					  exit;
					  }
					  else
					  {
						  $jonusMatch =  JonusLoan::find($checkExistAppID->id);
							$jonusMatch->match_status = 3;
				      $jonusMatch->save();
						  echo "APP ID NOT found Loan";
						  exit;
					  }
					}
					else
					{
						echo "done Loan";
						exit;
					}
				   /*
					*Marging with JonusLoan
					*/
					echo "done Loan";
						exit;
		}
		 
		
}
