<?php

namespace App\Http\Controllers\MIS;

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
use App\Models\MIS\ENBDCardsImportFiles;
use App\Models\MIS\ENBDCardsMisReport;
use App\Models\MIS\MainMisImportFiles;
use App\Models\MIS\JonusReportLog;
use App\Models\Entry\Employee;
use App\Models\MIS\MainMisReport;
use App\Models\MIS\CurrentActivity;
use App\Models\MIS\ENDBCARDStatus;
use App\Models\MIS\MonthlyEnds;
use App\Models\Attribute\Attributes;
use App\Models\MIS\BankDetailsUAE;
use App\Models\MIS\MainMisImportENBDCardsTabFiles;
use App\Models\MIS\MainMisReportTab;
use App\Models\MIS\PrecallingFile;
use App\Models\MIS\EVcallFile;
use App\Models\LoanMis\MisDocuments;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\PDFMarge\FPDF_Merge;
use App\Models\MIS\WpCountries;
use App\Models\Logs\ENBDCardsLogs;

class MisController extends Controller
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
			
			public function manageMISENBDCards(Request $request)
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
					$reports = MainMisReport::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue);
				}
				else
				{
					$reports = MainMisReport::orderBy("id","DESC")->paginate($paginationValue);
				}
				$reports->setPath(config('app.url/manageMISENBDCards'));
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if($whereraw != '')
				{
					
					$reportsCount = MainMisReport::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = MainMisReport::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				
				return view("MIS/manageMISENBDCards",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
				
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
			public function setOffSetForENDBCardsInnerMIS(Request $request)
			{
				$offset = $request->offset;
				$request->session()->put('offset_enbd_cards_inner_mis',$offset);
				 return  redirect('manageMISENBDCards');
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
			public function addMisReportENBDCard(Request $request)
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
				$currentActMod = CurrentActivity::where("status",1)->get();
				$enbdCardsStatus = ENDBCARDStatus::where("status",1)->get();
				$MonthlyEndsValues = MonthlyEnds::where("status",1)->get();
				$NATs = Attributes::where("attribute_id",38)->first();
				$natValues = json_decode($NATs->opt_option);
				$banks = BankDetailsUAE::where("status",1)->get();
				return view("MIS/addMisReportENBDCard",compact('currentActMod','enbdCardsStatus','MonthlyEndsValues','tL_details','agent_details','natValues','banks'));
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
		   
		   public function savePostENBDCARDSMIS(Request $request)
		   {
			   
				
			   $requestParameters = $request->input();
			   
			
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
			 
			  
			  
			 
			 
			  
			   $misReportObj->REFERENCE_NAME = $requestParameters['REFERENCE_NAME'];
			   $misReportObj->REFERENCE_MOBILE_NO = $requestParameters['REFERENCE_MOBILE_NO'];
			 
			   $misReportObj->cm_name = $requestParameters['cm_name'];
			   $misReportObj->CV_MOBILE_NUMBER = $requestParameters['CV_MOBILE_NUMBER'];
			   $misReportObj->EV_DIRECT_OFFICE_NO = $requestParameters['EV_DIRECT_OFFICE_NO'];
			   $misReportObj->E_MAILADDRESS = $requestParameters['E_MAILADDRESS'];
			   $misReportObj->SALARY = $requestParameters['SALARY'];
			   $misReportObj->SALARIED = $requestParameters['SALARIED'];
			   $misReportObj->NATIONALITY = $requestParameters['NATIONALITY'];
			   $misReportObj->PASSPORT_NO = $requestParameters['PASSPORT_NO'];
			   $misReportObj->DOB = $requestParameters['DOB'];
			   $misReportObj->VISA_Expiry_DATE = $requestParameters['VISA_Expiry_DATE'];
			   $misReportObj->DESIGNATION = $requestParameters['DESIGNATION'];
			   $misReportObj->MMN = $requestParameters['MMN'];
			   $misReportObj->EIDA = $requestParameters['EIDA'];
			  
			   $misReportObj->EV = $requestParameters['EV'];
			    $misReportObj->PRE_CALLING = $requestParameters['PRE_CALLING'];
			   $misReportObj->Type_of_Income_Proof = $requestParameters['Type_of_Income_Proof'];
			   $misReportObj->match_status = 1;
			   $misReportObj->hand_on_status = 1;
			   $misReportObj->hand_on_status_final = 1;
			  
			  
			  
			  $misReportObj->complete_status = 2;
			  $misReportObj->over_ride_status = 0;
			  $misReportObj->save();
			  
			  /**
			  *Making Logs
			  *start code
			  */
			  
			  $logsENBDCards = new ENBDCardsLogs();
			  $logsENBDCards->type = $requestParameters['file_source'];
			  $logsENBDCards->action = "Submission Created";
			  $logsENBDCards->action_date = date("Y-m-d");
			  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
			  $logsENBDCards->action_area = "Submission Created";
			  $logsENBDCards->mis_id = $misReportObj->id;
			  $logsENBDCards->source = 'Entry';
			  $logsENBDCards->save();
			  /**
			  *Making Logs
			  *End code
			  */
			   /*
			  upload
			  */
			  $newFileName = '';
			   $key = 'PRE_CALLING';
			   if($request->file($key))
				{
					
			    $filenameWithExt = $request->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$request->file($key)->getClientOriginalExtension();
				
				$newFileName = 'Pre-calling_'.$requestParameters['application_id'].'_'.$misReportObj->id.'.'.$fileExtension;
				
				    if(file_exists(public_path('uploads/precalling/'.$newFileName))){

					  unlink(public_path('uploads/precalling/'.$newFileName));

					}
				
				/*
				*Updating File Name
				*/
				
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $request->file($key)->getClientOriginalExtension();
				// Filename To store
				
				
				
				$request->file($key)->move(public_path('uploads/precalling/'), $newFileName);
				
				
				
				}
				/* if( $newFileName != '')
				{
				 $misReportObjUpdate = MainMisReport::find($misReportObj->id);
				 $misReportObjUpdate->PRE_CALLING = $newFileName;
				 $misReportObjUpdate->save();
				} */
			   
				echo "<h3>Data Saved.</h3>";
		   }
		   
		   public static function getCurrentActivity($id)
		   {
			   if(!empty($id))
			   {
			   return CurrentActivity::where("id",$id)->first()->name;
			   }
			   else
			   {
				   return "-";
			   }
		   }
		   
		   public static function getapproved_notapproved($id)
		   {
			    if(!empty($id))
			   {
			   return ENDBCARDStatus::where("id",$id)->first()->status_name;
			   }
			   else
			   {
				    return "-";
			   }
		   }
		   public static function getmonthly_ends($id)
		   {
			    if(!empty($id))
			   {
			   return MonthlyEnds::where("id",$id)->first()->name;
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
			   return BankDetailsUAE::where("id",$id)->first()->bank_name;
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
			
			public function setOffSetForENDBCardsInnerMISTab(Request $request)
			{
				$offset = $request->offset;
				$request->session()->put('offset_enbd_cards_tab_inner_mis',$offset);
				 return  redirect('manageMISENBDCardsTab');
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
			
			public function getManualENBDCards(Request $request)
			{
				$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
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
				$cList = WpCountries::get();
				return view("MIS/getManualENBDCards",compact('currentActMod','enbdCardsStatus','MonthlyEndsValues','tL_details','agent_details','natValues','banks','cList'));
			}
			public function getManualENBDCardsTab(Request $request)
			{
				$rowId = $request->rowid;
			   $misRecords = MainMisReport::where("id",$rowId)->first();
				$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
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
				$cList = WpCountries::get();
				return view("MIS/getManualENBDCardsTab",compact('currentActMod','enbdCardsStatus','MonthlyEndsValues','tL_details','agent_details','natValues','banks','misRecords','rowId','cList'));
				
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
			
			public function listingENBDCardManual(Request $request)
			{
				
			  $whereraw = '';
			  $selectedFilter['customer_name'] = '';
			  $selectedFilter['employee_id'] = '';
			  $selectedFilter['report'] = '';
			  $selectedFilter['file_source'] = '';
			  
			  
			  if(!empty($request->session()->get('file_source_type_filter')))
				{
					$filesource = $request->session()->get('file_source_type_filter');
					$selectedFilter['file_source'] = $filesource;
					if($filesource  != 'All')
					{
						if($whereraw == '')
						{
							$whereraw = "file_source = '".$filesource."'";
						}
						else
						{
							$whereraw .= " And file_source = '".$filesource."'";
						}
					}
				}
				else
				{
					if($whereraw == '')
					{
						$whereraw = "file_source = 'manual'";
					}
					else
					{
						$whereraw .= " And file_source = 'manual'";
					}
					$selectedFilter['file_source'] = 'manual';
				}
				
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
			  }
			  
			/* 	echo $whereraw;exit; */
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
					$reports = MainMisReport::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue);
				}
				else
				{
					$reports = MainMisReport::orderBy("id","DESC")->paginate($paginationValue);
				}
				$reports->setPath(config('app.url/listingENBDCardManual'));
				
				
				
				//$reports = enbdCardsMISReport::orderBy("id","DESC")->limit($limit)->offset(($page - 1) * $limit)->get();
				
				if($whereraw != '')
				{
					
					$reportsCount = MainMisReport::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = MainMisReport::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				$autoSelectRowId = '';
				if(!empty($request->session()->get('mis_return_id')))
				{
					$autoSelectRowId = $request->session()->get('mis_return_id');
				}
				$request->session()->put('mis_return_id','');
				return view("MIS/listingENBDCardManual",compact('reports','reportsCount','paginationValue','employees','selectedFilter','autoSelectRowId'));
			}
		   public function findSE(Request $request)
		   {
			    $tLID = $request->tLID;
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->where("tl_id",$tLID)->get();
				
				return view("MIS/findSE",compact('agent_details'));
		   }
		   
		   public function updateMisReportENBDCard(Request $request)
		   {
			   $rowId = $request->rowid;
			   $misRecords = MainMisReport::where("id",$rowId)->first();
			   $tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				//$tL_details = Employee_details::whereIn("emp_id",$tL_id)->get();
				$tL_details = Employee_details::where("job_role",'Team Leader')->where("dept_id",9)->get();
				$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->where("tl_id",$misRecords->TL)->get();
				$currentActMod = CurrentActivity::where("status",1)->get();
				$enbdCardsStatus = ENDBCARDStatus::where("status",1)->get();
				$MonthlyEndsValues = MonthlyEnds::where("status",1)->get();
				$NATs = Attributes::where("attribute_id",38)->first();
				$natValues = json_decode($NATs->opt_option);
				$banks = BankDetailsUAE::where("status",1)->get();
				return view("MIS/updateMisReportENBDCard",compact('currentActMod','enbdCardsStatus','MonthlyEndsValues','tL_details','agent_details','natValues','banks','misRecords'));
		   }
		   
		   
		   public function getManualENBDCardsUpdate(Request $request)
			{
				 $rowId = $request->rowid;
			   $misRecords = MainMisReport::where("id",$rowId)->first();
				$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
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
				$cList = WpCountries::get();
				return view("MIS/getManualENBDCardsUpdate",compact('currentActMod','enbdCardsStatus','MonthlyEndsValues','tL_details','agent_details','natValues','banks','misRecords','rowId','cList'));
			}
			
			
			public function getManualENBDCardsUpdateTab(Request $request)
			{
				 $rowId = $request->rowid;
			   $misRecords = MainMisReport::where("id",$rowId)->first();
				$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
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
				$cList = WpCountries::get();
				return view("MIS/getManualENBDCardsUpdateTab",compact('currentActMod','enbdCardsStatus','MonthlyEndsValues','tL_details','agent_details','natValues','banks','misRecords','rowId','cList'));
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
				
				
				$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
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
			
			
			public function savePostENBDCARDSMISUpdate(Request $request)
		   {
			   
				
			   $requestParameters = $request->input();
			   
			   $appId = $requestParameters['application_id'];
			   $misID = $requestParameters['misID'];
			   /*
			   *creating Values for log
			   */
			   $misReportLogs = MainMisReport::where("id",$misID)->first();
			   $statusLog = $misReportLogs->approved_notapproved;
			   $cactivityLog = $misReportLogs->current_activity;
			   $monthlyEndLog = $misReportLogs->monthly_ends;
			   /*
			   *creating Values for log
			   */
			   $misReportObjUpdate = MainMisReport::find($misID);
			  $misReportObjUpdate->date_of_submission = $requestParameters['date_of_submission'];
			      $misReportObjUpdate->submission_format = date("Y-m-d",strtotime($requestParameters['date_of_submission']));
			   $misReportObjUpdate->application_type = $requestParameters['application_type'];
			   $misReportObjUpdate->application_id = $requestParameters['application_id'];
			  
			   $misReportObjUpdate->current_activity = $requestParameters['current_activity'];
			   $misReportObjUpdate->monthly_ends = $requestParameters['monthly_ends'];
			  
			   
			   $misReportObjUpdate->fv_company_name = $requestParameters['fv_company_name'];
			   $misReportObjUpdate->company_name_as_per_visa = $requestParameters['company_name_as_per_visa'];
			   $misReportObjUpdate->ALE_NALE = $requestParameters['ALE_NALE'];
			   $misReportObjUpdate->LOS = $requestParameters['LOS'];
			   $misReportObjUpdate->ACCOUNT_STATUS = $requestParameters['ACCOUNT_STATUS'];
			   if($requestParameters['ACCOUNT_STATUS'] == 1)
			   {
				    $misReportObjUpdate->other_bank = $requestParameters['other_bank'];
			   }
			   $misReportObjUpdate->ACCOUNT_NO = $requestParameters['ACCOUNT_NO'];
			   $misReportObjUpdate->TL = $requestParameters['TL'];
			  
			   $misReportObjUpdate->lead_source = $requestParameters['lead_source'];
			   $misReportObjUpdate->PRODUCT = $requestParameters['PRODUCT'];
			   $misReportObjUpdate->file_source = $requestParameters['file_source'];
			   $misReportObjUpdate->SE_CODE_NAME = $requestParameters['SE_CODE_NAME'];
			  
			   $misReportObjUpdate->iban = $requestParameters['iban'];
			   $misReportObjUpdate->approved_notapproved = $requestParameters['approved_notapproved'];
			   $misReportObjUpdate->last_remarks_added = $requestParameters['last_remarks_added'];
			  
			   $misReportObjUpdate->created_by = $request->session()->get('EmployeeId');
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
								$misReportObjUpdate->employee_id =  $employeeDetails->id;
								$misReportObjUpdate->Employee_status = "Verified";
								}
								else
								{
									$misReportObjUpdate->Employee_status = "Not-Verified";
								}
							}
							else
							{
								$misReportObjUpdate->Employee_status = "Not-Verified";
							}
						}
					else
					{
						$misReportObjUpdate->Employee_status = "Not-Verified";
					}
			  $misReportObjUpdate->type_data = 'generated';
			   $misReportObjUpdate->REFERENCE_NAME = $requestParameters['REFERENCE_NAME'];
			   $misReportObjUpdate->REFERENCE_MOBILE_NO = $requestParameters['REFERENCE_MOBILE_NO'];
			 
			   $misReportObjUpdate->cm_name = $requestParameters['cm_name'];
			   $misReportObjUpdate->CV_MOBILE_NUMBER = $requestParameters['CV_MOBILE_NUMBER'];
			   $misReportObjUpdate->EV_DIRECT_OFFICE_NO = $requestParameters['EV_DIRECT_OFFICE_NO'];
			   $misReportObjUpdate->E_MAILADDRESS = $requestParameters['E_MAILADDRESS'];
			   $misReportObjUpdate->SALARY = $requestParameters['SALARY'];
			   $misReportObjUpdate->SALARIED = $requestParameters['SALARIED'];
			   $misReportObjUpdate->NATIONALITY = $requestParameters['NATIONALITY'];
			   $misReportObjUpdate->PASSPORT_NO = $requestParameters['PASSPORT_NO'];
			   $misReportObjUpdate->DOB = $requestParameters['DOB'];
			   $misReportObjUpdate->VISA_Expiry_DATE = $requestParameters['VISA_Expiry_DATE'];
			   $misReportObjUpdate->DESIGNATION = $requestParameters['DESIGNATION'];
			   $misReportObjUpdate->MMN = $requestParameters['MMN'];
			   $misReportObjUpdate->EIDA = $requestParameters['EIDA'];
			  
			   $misReportObjUpdate->EV = $requestParameters['EV'];
			    $misReportObjUpdate->PRE_CALLING = $requestParameters['PRE_CALLING'];
			   $misReportObjUpdate->Type_of_Income_Proof = $requestParameters['Type_of_Income_Proof'];
			  
			  
			  
			  $misReportObjUpdate->complete_status = 2;
			  /*
			  *checking for marging allow
			  *
			  */
			  $jonusUpdateStatusFlag = 0;
			  $jonusUpdateCurrentActivityFlag = 0;
				$enbdCardMod =  ENBDCardsMisReport::where("application_id",$appId)->first();
				if($enbdCardMod != '')
				{
					if($enbdCardMod->match_status == 1 || $enbdCardMod->match_status == 3)
					{
								$misReportObjUpdate->Offer = $enbdCardMod->OFFER;
								$misReportObjUpdate->Scheme = $enbdCardMod->SCHEME;
								$misReportObjUpdate->ev_status = $enbdCardMod->EVSTATUS;
								if($enbdCardMod->STATUS == 'COMPLETED')
								{
									$misReportObjUpdate->approved_notapproved = 3;
								}
								else if($enbdCardMod->STATUS == 'STARTED')
								{
									$misReportObjUpdate->approved_notapproved = 7;
								}
								else
								{
									$misReportObjUpdate->approved_notapproved = 7;
								}
								
								/*current Activity*/
								if(trim($enbdCardMod->CURRENTACTIVITY) == 'End')
								{
								$misReportObjUpdate->current_activity = 2;
								}
								else if(trim($enbdCardMod->CURRENTACTIVITY) == 'Reject Review')
								{
								$misReportObjUpdate->current_activity = 6;
								}
								else if(trim($enbdCardMod->CURRENTACTIVITY) == 'Single Data Entry')
								{
								$misReportObjUpdate->current_activity = 7;
								}
								else if(trim($enbdCardMod->CURRENTACTIVITY) == 'Document Verification')
								{
								$misReportObjUpdate->current_activity = 12;
								}
								else if(trim($enbdCardMod->CURRENTACTIVITY) == 'Underwriting')
								{
								$misReportObjUpdate->current_activity = 8;
								}
								else if(trim($enbdCardMod->CURRENTACTIVITY) == 'Detail Data Entry')
								{
								$misReportObjUpdate->current_activity = 11;
								}
								else if(trim($enbdCardMod->CURRENTACTIVITY) == 'CANCEL')
								{
								$misReportObjUpdate->current_activity = 1;
								}
								else if(trim($enbdCardMod->CURRENTACTIVITY) == 'VERIFICATION DETAIL')
								{
								$misReportObjUpdate->current_activity = 13;
								}
								else if(trim($enbdCardMod->CURRENTACTIVITY) == 'HOLD RCC')
								{
								$misReportObjUpdate->current_activity = 3;
								}
								else if(trim($enbdCardMod->CURRENTACTIVITY) == 'HOLD SOURCING')
								{
								$misReportObjUpdate->current_activity = 4;
								}
								/*current Activity*/
								$misReportObjUpdate->last_updated = $enbdCardMod->LASTUPDATED;
								$misReportObjUpdate->last_updated_date = date("Y-m-d",strtotime($enbdCardMod->LASTUPDATED));
								$misReportObjUpdate->approve_limit = $enbdCardMod->APPROVEDLIMIT;
								$misReportObjUpdate->last_remarks_added = strtoupper($enbdCardMod->LASTREMARKSADDED);
								$misReportObjUpdate->match_status = 2;
								$misReportObjUpdate->hand_on_status = 2;
								
								$misReportObjUpdate->save();
								$jonusUpdate = enbdCardsMISReport::find($enbdCardMod->id);
								$jonusUpdate->match_status =2;
								$jonusUpdate->save();
								 /**
								  *Making Logs
								  *start code
								  */
								  if($enbdCardMod->STATUS != '')
								  {
								  $logsENBDCards = new ENBDCardsLogs();
								  $logsENBDCards->type = $requestParameters['file_source'];
								  $logsENBDCards->action = $enbdCardMod->STATUS;
								  $logsENBDCards->action_date = date("Y-m-d");
								  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
								  $logsENBDCards->action_area = "Jonus Update Status";
								  $logsENBDCards->mis_id = $misReportObjUpdate->id;
								  $logsENBDCards->source = 'Entry';
								  $logsENBDCards->save();
								  $jonusUpdateStatusFlag = 1;
								  }
								  if($enbdCardMod->current_activity != '')
								  {
								  $logsENBDCards = new ENBDCardsLogs();
								  $logsENBDCards->type = $requestParameters['file_source'];
								  $logsENBDCards->action = $enbdCardMod->current_activity;
								  $logsENBDCards->action_date = date("Y-m-d");
								  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
								  $logsENBDCards->action_area = "Jonus Update Current Activity";
								  $logsENBDCards->mis_id = $misReportObjUpdate->id;
								  $logsENBDCards->source = 'Entry';
								  $logsENBDCards->save();
								   $jonusUpdateCurrentActivityFlag =1;
								  }
								  
								   /**
								  *Making Logs
								  *end code
								  */
								
								
					}
				}
			   /*
			  *checking for marging allow
			  *
			  */
			  $misReportObjUpdate->save();
			  
			   /**
			  *Making Logs
			  *start code
			  */
			/*  echo $statusLog;
			 echo '<br />';
			 echo $requestParameters['approved_notapproved'];exit; */
			  if($statusLog != $requestParameters['approved_notapproved'])
			  {
				  if($jonusUpdateStatusFlag != 1)
				  {
						  $logsENBDCards = new ENBDCardsLogs();
						  $logsENBDCards->type = $requestParameters['file_source'];
						  $logsENBDCards->action = $requestParameters['approved_notapproved'];
						  $logsENBDCards->action_date = date("Y-m-d");
						  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
						  $logsENBDCards->action_area = "Status Update";
						  $logsENBDCards->mis_id = $misReportObjUpdate->id;
						  $logsENBDCards->source = 'Entry';
						  $logsENBDCards->save();
				  }
			  }
			  
			  
			  if($cactivityLog != $requestParameters['current_activity'])
			  {
				  if( $jonusUpdateCurrentActivityFlag != 1)
				  {
						  $logsENBDCards = new ENBDCardsLogs();
						  $logsENBDCards->type = $requestParameters['file_source'];
						  $logsENBDCards->action = $requestParameters['current_activity'];
						  $logsENBDCards->action_date = date("Y-m-d");
						  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
						  $logsENBDCards->action_area = "Current Activity Update";
						  $logsENBDCards->mis_id = $misReportObjUpdate->id;
						  $logsENBDCards->source = 'Entry';
						  $logsENBDCards->save();
				  }
			  }
			  
			  if($monthlyEndLog != $requestParameters['monthly_ends'])
			  {
			  $logsENBDCards = new ENBDCardsLogs();
			  $logsENBDCards->type = $requestParameters['file_source'];
			  $logsENBDCards->action = $requestParameters['monthly_ends'];;
			  $logsENBDCards->action_date = date("Y-m-d");
			  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
			  $logsENBDCards->action_area = "Monthly Ends Update";
			  $logsENBDCards->mis_id = $misReportObjUpdate->id;
			  $logsENBDCards->source = 'Entry';
			  $logsENBDCards->save();
			  }
			   /**
			  *Making Logs
			  *end code
			  */
			   /*
			  upload
			  */
			  $newFileName = '';
			   $key = 'PRE_CALLING';
			   if($request->file($key))
				{
					
			    $filenameWithExt = $request->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$request->file($key)->getClientOriginalExtension();
				
				$newFileName = 'Pre-calling_'.$requestParameters['application_id'].'_'.$misReportObj->id.'.'.$fileExtension;
				
				    if(file_exists(public_path('uploads/precalling/'.$newFileName))){

					  unlink(public_path('uploads/precalling/'.$newFileName));

					}
				
				/*
				*Updating File Name
				*/
				
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $request->file($key)->getClientOriginalExtension();
				// Filename To store
				
				
				
				$request->file($key)->move(public_path('uploads/precalling/'), $newFileName);
				
				
				
				}
				/* if( $newFileName != '')
				{
				 $misReportObjUpdate = MainMisReport::find($misReportObj->id);
				 $misReportObjUpdate->PRE_CALLING = $newFileName;
				 $misReportObjUpdate->save();
				} */
			   
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
			   $filename = 'Mashreq_AllEmployees_18Feb2023-1-final.csv';
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'";');  
			$allEmployee = Employee_details::where("dept_id",36)->get();
			$header = array();
			$header[] = 'Employee Id';
			$header[] = 'First Name';
			$header[] = 'Middle Name';
			$header[] = 'Last Name';
			$header[] = 'Bank Code';
			$header[] = 'Passport Number';
			$header[] = 'Email';
			
			$header[] = 'Person Code';
			$header[] = 'Date of Birth';
			$header[] = 'Labour Card Number';
			$header[] = 'Nationality';
			$header[] = 'Local Contact Number';
			$header[] = 'Home Address';
			$header[] = 'Gender';
			$header[] = 'Local Address';
			$header[] = 'Emirates ID NO';
			$header[] = 'Home Country Contact Number';
			$header[] = 'EMERGENCY CONTACT NUMBER';
			$header[] = 'Email Address';
			$header[] = 'Permanent Visa Number';
			$header[] = 'VISA UID NO';
			$header[] = 'Labour Expiry Date';
			$header[] = 'Date of Joining';
			$header[] = 'Residence Stamp Expiry Date';
			$header[] = 'Designation';
			$header[] = 'Designation As Per Mol';
			$header[] = 'COMPANY NAME UNDER WHICH VISA IS ISSUED';
			$header[] = 'COMPANY CODE';
			$header[] = 'CATEGORY';
			$header[] = 'PERSON NAME AS PER MOL';
			$header[] = 'Residence Stamp Start Date';
			$header[] = 'Basic Salary MOL';
			$header[] = 'Others MOL';
			$header[] = 'Insurance Number';
			$header[] = 'Effects';
			$header[] = 'Actual Salary';
			$header[] = 'Residence visa no';
			$header[] = 'STATUS PAYROLL';
			$header[] = 'Basic Salary MOL';
			$header[] = 'Total Gross Salary (MOL)';
			$header[] = 'Work Location';
			$header[] = 'Source Code';
			$header[] = 'Entity';
			$header[] = 'Date Payroll';
			$header[] = 'Employee IBAN Number';
			$header[] = 'Employee Bank Name';
			$header[] = 'Employee Bank Account Number';
		
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
				$values[] =  $emp->source_code;
				
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","PP_NO")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","email")->first()->attribute_values;
			
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","person_code")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","EMPDOB")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","LC_Number")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","NAT")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","CONTACT_NUMBER")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","HOM_ADD")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","GNDR")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","LOC_ADD")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","emirates_id_no")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","HC_CONTACT_NUMBER")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","emergency_contact_number")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","email")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","PVISA_NUMBER")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","visa_uid_no")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","labour_expiry_date")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","DOJ")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","residence_stamp_expiry_date")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","DESIGN")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","PERMOL")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","company_name_issue_issued")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","company_code_payroll")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","category_payroll")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","personname_as_per_mol_payroll")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","residence_stamp_start_date")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","basic_salary_mol")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","others_mol")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","insurance")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","effects")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","actual_salary")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","residence_visa_no")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","status_payroll")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","basic_salary_mol")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","total_gross_salary")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","work_location")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","source_code")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","entity")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","date_payroll")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","EMP_IBAN")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","EBN")->first()->attribute_values;
				$values[] =	@Employee_attribute::where("emp_id",$emp->emp_id)->where("attribute_code","EBAM")->first()->attribute_values;
				
				
				fputcsv($f, $values, ',');
				/* echo '<pre>';
				print_r($values);
				exit;  */
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
		   
		   
		   
		   public function savePostENBDCARDSMISUpdateTab(Request $request)
		   {
			   
				
			   $requestParameters = $request->input();
			   
			   $appId = $requestParameters['application_id'];
			   $misID = $requestParameters['misID'];
			   /*
			   *get Existing Status
			   */
			   $existingStatus = MainMisReport::where("id",$misID)->first()->approved_notapproved;
			   /*
			   *get Existing Status
			   */
			   
			   $misReportObjUpdate = MainMisReport::find($misID);
			  $misReportObjUpdate->date_of_submission = $requestParameters['date_of_submission'];
			      $misReportObjUpdate->submission_format = date("Y-m-d",strtotime($requestParameters['date_of_submission']));
			   $misReportObjUpdate->application_type = $requestParameters['application_type'];
			   $misReportObjUpdate->customer_type = $requestParameters['customer_type'];
			   $misReportObjUpdate->application_id = $requestParameters['application_id'];
			  
			   $misReportObjUpdate->application_mode = $requestParameters['application_mode'];
			   $misReportObjUpdate->DMS_Outcome = $requestParameters['DMS_Outcome'];
			   $misReportObjUpdate->Card_Name = $requestParameters['Card_Name'];
			   $misReportObjUpdate->STP_NSTP_flag = $requestParameters['STP_NSTP_flag'];
			   $misReportObjUpdate->DMS_Status_Description = $requestParameters['DMS_Status_Description'];
			  
			   
			   $misReportObjUpdate->fv_company_name = $requestParameters['fv_company_name'];
			   $misReportObjUpdate->company_name_as_per_visa = $requestParameters['company_name_as_per_visa'];
			  
			   $misReportObjUpdate->LOS = $requestParameters['LOS'];
			   $misReportObjUpdate->ACCOUNT_STATUS = $requestParameters['ACCOUNT_STATUS'];
			   if($requestParameters['ACCOUNT_STATUS'] == 1)
			   {
				    $misReportObjUpdate->other_bank = $requestParameters['other_bank'];
			   }
			   $misReportObjUpdate->ACCOUNT_NO = $requestParameters['ACCOUNT_NO'];
			   $misReportObjUpdate->TL = $requestParameters['TL'];
			  
			   
			  
			   $misReportObjUpdate->file_source = $requestParameters['file_source'];
			   $misReportObjUpdate->SE_CODE_NAME = $requestParameters['SE_CODE_NAME'];
			  
			   $misReportObjUpdate->iban = $requestParameters['iban'];
			   $misReportObjUpdate->approved_notapproved = $requestParameters['approved_notapproved'];
			   $misReportObjUpdate->last_remarks_added = $requestParameters['last_remarks_added'];
			  
			   $misReportObjUpdate->created_by = $request->session()->get('EmployeeId');
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
								$misReportObjUpdate->employee_id =  $employeeDetails->id;
								$misReportObjUpdate->Employee_status = "Verified";
								}
								else
								{
									$misReportObjUpdate->Employee_status = "Not-Verified";
								}
							}
							else
							{
								$misReportObjUpdate->Employee_status = "Not-Verified";
							}
						}
					else
					{
						$misReportObjUpdate->Employee_status = "Not-Verified";
					}
			  $misReportObjUpdate->type_data = 'generated';
			   $misReportObjUpdate->REFERENCE_NAME = $requestParameters['REFERENCE_NAME'];
			   $misReportObjUpdate->REFERENCE_MOBILE_NO = $requestParameters['REFERENCE_MOBILE_NO'];
			 
			   $misReportObjUpdate->cm_name = $requestParameters['cm_name'];
			   $misReportObjUpdate->CV_MOBILE_NUMBER = $requestParameters['CV_MOBILE_NUMBER'];
			   $misReportObjUpdate->EV_DIRECT_OFFICE_NO = $requestParameters['EV_DIRECT_OFFICE_NO'];
			   $misReportObjUpdate->E_MAILADDRESS = $requestParameters['E_MAILADDRESS'];
			   $misReportObjUpdate->SALARY = $requestParameters['SALARY'];
			   $misReportObjUpdate->SALARIED = $requestParameters['SALARIED'];
			   $misReportObjUpdate->NATIONALITY = $requestParameters['NATIONALITY'];
			   $misReportObjUpdate->PASSPORT_NO = $requestParameters['PASSPORT_NO'];
			   $misReportObjUpdate->DOB = $requestParameters['DOB'];
			   $misReportObjUpdate->VISA_Expiry_DATE = $requestParameters['VISA_Expiry_DATE'];
			   $misReportObjUpdate->DESIGNATION = $requestParameters['DESIGNATION'];
			   $misReportObjUpdate->MMN = $requestParameters['MMN'];
			   $misReportObjUpdate->EIDA = $requestParameters['EIDA'];
			  
			   $misReportObjUpdate->EV = $requestParameters['EV'];
			    $misReportObjUpdate->PRE_CALLING = $requestParameters['PRE_CALLING'];
			   $misReportObjUpdate->Type_of_Income_Proof = $requestParameters['Type_of_Income_Proof'];
			  
			    $misReportObjUpdate->PRODUCT = $requestParameters['PRODUCT'];
			    $misReportObjUpdate->NATIONALITY = $requestParameters['NATIONALITY'];
			  
			  $misReportObjUpdate->complete_status = 2;
			  
			  $misReportObjUpdate->save();
			  if( $existingStatus != $requestParameters['approved_notapproved'])
			  {
								 /**
								  *Making Logs
								  *start code
								  */
								  
								  $logsENBDCards = new ENBDCardsLogs();
								  $logsENBDCards->type = $requestParameters['file_source'];
								  $logsENBDCards->action = $requestParameters['approved_notapproved'];
								  $logsENBDCards->action_date = date("Y-m-d");
								  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
								  $logsENBDCards->action_area = "Status Update";
								  $logsENBDCards->mis_id = $misReportObjUpdate->id;
								  $logsENBDCards->source = 'Entry';
								  $logsENBDCards->save();
								  
								   /**
								  *Making Logs
								  *end code
								  */
			  }
			   /*
			  upload
			  */
			  $newFileName = '';
			   $key = 'PRE_CALLING';
			   if($request->file($key))
				{
					
			    $filenameWithExt = $request->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$request->file($key)->getClientOriginalExtension();
				
				$newFileName = 'Pre-calling_'.$requestParameters['application_id'].'_'.$misReportObj->id.'.'.$fileExtension;
				
				    if(file_exists(public_path('uploads/precalling/'.$newFileName))){

					  unlink(public_path('uploads/precalling/'.$newFileName));

					}
				
				/*
				*Updating File Name
				*/
				
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $request->file($key)->getClientOriginalExtension();
				// Filename To store
				
				
				
				$request->file($key)->move(public_path('uploads/precalling/'), $newFileName);
				
				
				
				}
				/* if( $newFileName != '')
				{
				 $misReportObjUpdate = MainMisReport::find($misReportObj->id);
				 $misReportObjUpdate->PRE_CALLING = $newFileName;
				 $misReportObjUpdate->save();
				} */
			   
				echo "<h3>Data Updated.</h3>";
		   }
		   
		   
		   
		    public function savePostENBDCARDSMISTab(Request $request)
		   {
			   
				
			   $requestParameters = $request->input();
			  
			  
			   $misReportObjUpdate = new MainMisReport();
			  $misReportObjUpdate->date_of_submission = $requestParameters['date_of_submission'];
			      $misReportObjUpdate->submission_format = date("Y-m-d",strtotime($requestParameters['date_of_submission']));
			   $misReportObjUpdate->application_type = $requestParameters['application_type'];
			   $misReportObjUpdate->customer_type = $requestParameters['customer_type'];
			   $misReportObjUpdate->application_id = $requestParameters['application_id'];
			  
			   $misReportObjUpdate->application_mode = $requestParameters['application_mode'];
			   $misReportObjUpdate->DMS_Outcome = $requestParameters['DMS_Outcome'];
			   $misReportObjUpdate->Card_Name = $requestParameters['Card_Name'];
			   $misReportObjUpdate->STP_NSTP_flag = $requestParameters['STP_NSTP_flag'];
			   $misReportObjUpdate->DMS_Status_Description = $requestParameters['DMS_Status_Description'];
			  
			   
			   $misReportObjUpdate->fv_company_name = $requestParameters['fv_company_name'];
			   $misReportObjUpdate->company_name_as_per_visa = $requestParameters['company_name_as_per_visa'];
			  
			   $misReportObjUpdate->LOS = $requestParameters['LOS'];
			   $misReportObjUpdate->ACCOUNT_STATUS = $requestParameters['ACCOUNT_STATUS'];
			   if($requestParameters['ACCOUNT_STATUS'] == 1)
			   {
				    $misReportObjUpdate->other_bank = $requestParameters['other_bank'];
			   }
			   $misReportObjUpdate->ACCOUNT_NO = $requestParameters['ACCOUNT_NO'];
			   $misReportObjUpdate->TL = $requestParameters['TL'];
			  
			   
			  
			   $misReportObjUpdate->file_source = $requestParameters['file_source'];
			   $misReportObjUpdate->SE_CODE_NAME = $requestParameters['SE_CODE_NAME'];
			  
			   $misReportObjUpdate->iban = $requestParameters['iban'];
			   $misReportObjUpdate->approved_notapproved = $requestParameters['approved_notapproved'];
			   $misReportObjUpdate->last_remarks_added = $requestParameters['last_remarks_added'];
			  
			   $misReportObjUpdate->created_by = $request->session()->get('EmployeeId');
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
								$misReportObjUpdate->employee_id =  $employeeDetails->id;
								$misReportObjUpdate->Employee_status = "Verified";
								}
								else
								{
									$misReportObjUpdate->Employee_status = "Not-Verified";
								}
							}
							else
							{
								$misReportObjUpdate->Employee_status = "Not-Verified";
							}
						}
					else
					{
						$misReportObjUpdate->Employee_status = "Not-Verified";
					}
			  $misReportObjUpdate->type_data = 'generated';
			   $misReportObjUpdate->REFERENCE_NAME = $requestParameters['REFERENCE_NAME'];
			   $misReportObjUpdate->REFERENCE_MOBILE_NO = $requestParameters['REFERENCE_MOBILE_NO'];
			 
			   $misReportObjUpdate->cm_name = $requestParameters['cm_name'];
			   $misReportObjUpdate->CV_MOBILE_NUMBER = $requestParameters['CV_MOBILE_NUMBER'];
			   $misReportObjUpdate->EV_DIRECT_OFFICE_NO = $requestParameters['EV_DIRECT_OFFICE_NO'];
			   $misReportObjUpdate->E_MAILADDRESS = $requestParameters['E_MAILADDRESS'];
			   $misReportObjUpdate->SALARY = $requestParameters['SALARY'];
			   $misReportObjUpdate->SALARIED = $requestParameters['SALARIED'];
			   $misReportObjUpdate->NATIONALITY = $requestParameters['NATIONALITY'];
			   $misReportObjUpdate->PASSPORT_NO = $requestParameters['PASSPORT_NO'];
			   $misReportObjUpdate->DOB = $requestParameters['DOB'];
			   $misReportObjUpdate->VISA_Expiry_DATE = $requestParameters['VISA_Expiry_DATE'];
			   $misReportObjUpdate->DESIGNATION = $requestParameters['DESIGNATION'];
			   $misReportObjUpdate->MMN = $requestParameters['MMN'];
			   $misReportObjUpdate->EIDA = $requestParameters['EIDA'];
			  
			   $misReportObjUpdate->EV = $requestParameters['EV'];
			    $misReportObjUpdate->PRE_CALLING = $requestParameters['PRE_CALLING'];
			   $misReportObjUpdate->Type_of_Income_Proof = $requestParameters['Type_of_Income_Proof'];
			  
			    $misReportObjUpdate->PRODUCT = $requestParameters['PRODUCT'];
			  
			  $misReportObjUpdate->complete_status = 2;
			  
			  $misReportObjUpdate->save();
			  
			  /**
			  *Making Logs
			  *start code
			  */
			  
			  $logsENBDCards = new ENBDCardsLogs();
			  $logsENBDCards->type = $requestParameters['file_source'];
			  $logsENBDCards->action = "Submission Created";
			  $logsENBDCards->action_date = date("Y-m-d");
			  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
			  $logsENBDCards->action_area = "Submission Created";
			  $logsENBDCards->mis_id = $misReportObjUpdate->id;
			  $logsENBDCards->source = 'Entry';
			  $logsENBDCards->save();
			  
			  
			  $logsENBDCards = new ENBDCardsLogs();
			  $logsENBDCards->type = $requestParameters['file_source'];
			  $logsENBDCards->action = $requestParameters['approved_notapproved'];
			  $logsENBDCards->action_date = date("Y-m-d");
			  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
			  $logsENBDCards->action_area = "Status Update";
			  $logsENBDCards->mis_id = $misReportObjUpdate->id;
			  $logsENBDCards->source = 'Entry';
			  $logsENBDCards->save();
			  /**
			  *Making Logs
			  *End code
			  */
			   /*
			  upload
			  */
			  $newFileName = '';
			   $key = 'PRE_CALLING';
			   if($request->file($key))
				{
					
			    $filenameWithExt = $request->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$request->file($key)->getClientOriginalExtension();
				
				$newFileName = 'Pre-calling_'.$requestParameters['application_id'].'_'.$misReportObj->id.'.'.$fileExtension;
				
				    if(file_exists(public_path('uploads/precalling/'.$newFileName))){

					  unlink(public_path('uploads/precalling/'.$newFileName));

					}
				
				/*
				*Updating File Name
				*/
				
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $request->file($key)->getClientOriginalExtension();
				// Filename To store
				
				
				
				$request->file($key)->move(public_path('uploads/precalling/'), $newFileName);
				
				
				
				}
				/* if( $newFileName != '')
				{
				 $misReportObjUpdate = MainMisReport::find($misReportObj->id);
				 $misReportObjUpdate->PRE_CALLING = $newFileName;
				 $misReportObjUpdate->save();
				} */
			   
				echo "<h3>Data Saved.</h3>";
		   }
		   
		   public function setFileSourceForMIS(Request $request)
		   {
			   $request->session()->put('file_source_type_filter',$request->fileSource);
			   return  redirect('manageMISENBDCards');
		   }
		   
		   public static function getPreCallFileStatus($MISid)
		   {
			   $precallStatus = PrecallingFile::where("mis_id",$MISid)->orderBy("id","DESC")->first();
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
			   $precallStatus = PrecallingFile::where("mis_id",$MISid)->orderBy("id","DESC")->first();
			if($precallStatus != '')
			{
				return $precallStatus->filename;
			}
			else
			{
				return 'not';
			}
		   }
		   
		   
		   
		    public static function getEvCallStatus($MISid)
		   {
			   $precallStatus = EVcallFile::where("mis_id",$MISid)->orderBy("id","DESC")->first();
			if($precallStatus != '')
			{
				return 'exist';
			}
			else
			{
				return 'not';
			}
		   }
		   
		    public static function getEvCallFileName($MISid)
		   {
			   $precallStatus = EVcallFile::where("mis_id",$MISid)->orderBy("id","DESC")->first();
			if($precallStatus != '')
			{
				return $precallStatus->filename;
			}
			else
			{
				return 'not';
			}
		   }
		   
		   public function uploadDocCardsManual(Request $request)
				{
					$misid = $request->misid;
					
					return view("MIS/uploadDocCardsManual",compact('misid'));
				}
			 public function uploadDocTab(Request $request)
				{
					$misid = $request->misid;
					
					return view("MIS/uploadDocTab",compact('misid'));
				}
			 public function precallingUpload(Request $request)
				{
					$misid = $request->misid;
					
					return view("MIS/precallingUpload",compact('misid'));
				}	
				
			 public function evupload(Request $request)
				{
					$misid = $request->misid;
					
					return view("MIS/evupload",compact('misid'));
				}		
			public function dailyReportTeamsCards(Request $request)
				{
				
					$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				$tL_details = Employee_details::whereIn("emp_id",$tL_id)->get();
				$selectedTL = array();
				
					return view("MIS/dailyReportTeamsCards",compact('tL_details','selectedTL'));
				}	
			public function dailyReportTeamsCardsCCD(Request $request)
				{
				
					$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				$tL_details = Employee_details::whereIn("emp_id",$tL_id)->get();
				$selectedTL = array();
				
					return view("MIS/dailyReportTeamsCardsccd",compact('tL_details','selectedTL'));
				}		
		public function reportingTeamDisplay(Request $request)
				{
				
					$leaders = $request->leaders;
					$leadersArray = explode(",",$leaders);
					return view("MIS/reportingTeamDisplay",compact('leadersArray'));
				}	
				
				
		  public function downloadPdfCards(Request $request)
		  {
			  $misid = $request->misid;
			  $type = $request->type;
	$misDocuments = MisDocuments::where("mis_id",$misid)->where("type",$type)->get();
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
				$customerName = MainMisReport::where("id",$misid)->first()->cm_name;
				if($type == 'Card-t')
				{
				 $pdfName = 'Cards-Tab-Documents_'.$customerName.'.pdf';
				}
				else
				{
					$pdfName = 'Cards-Manual-Documents_'.$customerName.'.pdf';
				}
				$this->fpdf->Output('D',$pdfName); 
		  }
		  
		public function checkResubmissionProcess(Request $request)
		{
			$AppIdValue = $request->AppIdValue;
			
			$cv_mobile_number = $request->cv_mobile_number;
			$misid = $request->misid;
			/*
			*check for APP ID resubmission
			*/
			$appId = MainMisReport::where("application_id",$AppIdValue)->where("id","!=",$misid)->where("over_ride_status","!=",1)->first();
			
			if($appId != '')
			{
				echo "APP ID already exists, Please confirm if this is case of resubmission.";
				exit;
			}
			/*
			*check for APP ID resubmission
			*/
			/*
			*check for APP ID resubmission
			*/
			$mobile = MainMisReport::where("CV_MOBILE_NUMBER",$cv_mobile_number)->where("id","!=",$misid)->where("over_ride_status","!=",1)->first();
			if($mobile != '')
			{
				echo "Customer Mobile Number already exists, Please confirm if this is case of resubmission.";
				exit;
			}
			/*
			*check for APP ID resubmission
			*/
			echo "done";
			exit;
		}
		
		public function confirmedReSubmision(Request $request)
		{
			$AppIdValue = $request->AppIdValue;
			
			$cv_mobile_number = $request->cv_mobile_number;
			$misid = $request->misid;
			/*
			*check for APP ID resubmission
			*/
			$appId = MainMisReport::where("application_id",$AppIdValue)->where("id","!=",$misid)->where("over_ride_status","!=",1)->first();
			
			if($appId != '')
			{
				$objUpdate = MainMisReport::find($appId->id);
				$objUpdate->over_ride_status = 1;
				$objUpdate->save();
			}
			/*
			*check for APP ID resubmission
			*/
			/*
			*check for APP ID resubmission
			*/
			$mobile = MainMisReport::where("CV_MOBILE_NUMBER",$cv_mobile_number)->where("id","!=",$misid)->where("over_ride_status","!=",1)->first();
			if($mobile != '')
			{
				$objUpdate = MainMisReport::find($mobile->id);
				$objUpdate->over_ride_status = 1;
				$objUpdate->save();
			}
			/*
			*check for APP ID resubmission
			*/
			
			echo "DONE";
			exit;
		}
		
		
		
		
		
		public function checkResubmissionProcessAdd(Request $request)
		{
			
			
			$cv_mobile_number = $request->cv_mobile_number;
		
			
			/*
			*check for APP ID resubmission
			*/
			$mobile = MainMisReport::where("CV_MOBILE_NUMBER",$cv_mobile_number)->where("over_ride_status","!=",1)->first();
			if($mobile != '')
			{
				echo "Customer Mobile Number already exists, Please confirm if this is case of resubmission.";
				exit;
			}
			/*
			*check for APP ID resubmission
			*/
			echo "done";
			exit;
		}
		
		public function confirmedReSubmisionAdd(Request $request)
		{
		
			
			$cv_mobile_number = $request->cv_mobile_number;
			
			
			/*
			*check for APP ID resubmission
			*/
			$mobile = MainMisReport::where("CV_MOBILE_NUMBER",$cv_mobile_number)->where("over_ride_status","!=",1)->first();
			if($mobile != '')
			{
				$objUpdate = MainMisReport::find($mobile->id);
				$objUpdate->over_ride_status = 1;
				$objUpdate->save();
			}
			/*
			*check for APP ID resubmission
			*/
			
			echo "DONE";
			exit;
		}
		
		
		public function checkResubmissionProcessTab(Request $request)
		{
			$AppIdValue = $request->AppIdValue;
			
			$cv_mobile_number = $request->cv_mobile_number;
			
			/*
			*check for APP ID resubmission
			*/
			$appId = MainMisReport::where("application_id",$AppIdValue)->where("over_ride_status","!=",1)->first();
			
			if($appId != '')
			{
				echo "APP ID already exists, Please confirm if this is case of resubmission.";
				exit;
			}
			/*
			*check for APP ID resubmission
			*/
			/*
			*check for APP ID resubmission
			*/
			$mobile = MainMisReport::where("CV_MOBILE_NUMBER",$cv_mobile_number)->where("over_ride_status","!=",1)->first();
			if($mobile != '')
			{
				echo "Customer Mobile Number already exists, Please confirm if this is case of resubmission.";
				exit;
			}
			/*
			*check for APP ID resubmission
			*/
			echo "done";
			exit;
		}
		
		public function confirmedReSubmisionTab(Request $request)
		{
			$AppIdValue = $request->AppIdValue;
			
			$cv_mobile_number = $request->cv_mobile_number;
		
			/*
			*check for APP ID resubmission
			*/
			$appId = MainMisReport::where("application_id",$AppIdValue)->where("over_ride_status","!=",1)->first();
			
			if($appId != '')
			{
				$objUpdate = MainMisReport::find($appId->id);
				$objUpdate->over_ride_status = 1;
				$objUpdate->save();
			}
			/*
			*check for APP ID resubmission
			*/
			/*
			*check for APP ID resubmission
			*/
			$mobile = MainMisReport::where("CV_MOBILE_NUMBER",$cv_mobile_number)->where("over_ride_status","!=",1)->first();
			if($mobile != '')
			{
				$objUpdate = MainMisReport::find($mobile->id);
				$objUpdate->over_ride_status = 1;
				$objUpdate->save();
			}
			/*
			*check for APP ID resubmission
			*/
			
			echo "DONE";
			exit;
		}
		
		
		public function checkResubmissionProcessTabUpdate(Request $request)
		{
			$AppIdValue = $request->AppIdValue;
			
			$cv_mobile_number = $request->cv_mobile_number;
			$misid = $request->misid;
			/*
			*check for APP ID resubmission
			*/
			$appId = MainMisReport::where("application_id",$AppIdValue)->where("id","!=",$misid)->where("over_ride_status","!=",1)->first();
			
			if($appId != '')
			{
				echo "APP ID already exists, Please confirm if this is case of resubmission.";
				exit;
			}
			/*
			*check for APP ID resubmission
			*/
			/*
			*check for APP ID resubmission
			*/
			$mobile = MainMisReport::where("CV_MOBILE_NUMBER",$cv_mobile_number)->where("id","!=",$misid)->where("over_ride_status","!=",1)->first();
			if($mobile != '')
			{
				echo "Customer Mobile Number already exists, Please confirm if this is case of resubmission.";
				exit;
			}
			/*
			*check for APP ID resubmission
			*/
			echo "done";
			exit;
		}
		
		public function confirmedReSubmisionTabUpdate(Request $request)
		{
			$AppIdValue = $request->AppIdValue;
			
			$cv_mobile_number = $request->cv_mobile_number;
			$misid = $request->misid;
			/*
			*check for APP ID resubmission
			*/
			$appId = MainMisReport::where("application_id",$AppIdValue)->where("id","!=",$misid)->where("over_ride_status","!=",1)->first();
			
			if($appId != '')
			{
				$objUpdate = MainMisReport::find($appId->id);
				$objUpdate->over_ride_status = 1;
				$objUpdate->save();
			}
			/*
			*check for APP ID resubmission
			*/
			/*
			*check for APP ID resubmission
			*/
			$mobile = MainMisReport::where("CV_MOBILE_NUMBER",$cv_mobile_number)->where("id","!=",$misid)->where("over_ride_status","!=",1)->first();
			if($mobile != '')
			{
				$objUpdate = MainMisReport::find($mobile->id);
				$objUpdate->over_ride_status = 1;
				$objUpdate->save();
			}
			/*
			*check for APP ID resubmission
			*/
			
			echo "DONE";
			exit;
		}
		  
}
