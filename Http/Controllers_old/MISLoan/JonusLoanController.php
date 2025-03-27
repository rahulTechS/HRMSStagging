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
use App\Models\LoanMis\JonusLoan;

use App\Models\Entry\Employee;

use App\Models\Attribute\Attributes;
use App\Models\MIS\JonusReportLog;
use App\Models\LoanMis\ENBDLoanImportFiles;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class JonusLoanController extends Controller
{
  
			
			
			public function enbdLoanJonusReport(Request $request)
			{
			/* 	error_reporting(E_ALL);
ini_set("display_errors", 1); */
				
			  $whereraw = '';
			   $selectedFilter['customer_name'] = '';
			  $selectedFilter['employee_id'] = '';
			  /* if(!empty($request->session()->get('enbd_cards_customer_name')))
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
				}  */
			 
				
				if(!empty($request->session()->get('offset_enbd_cards_jonus')))
				{
					$paginationValue = $request->session()->get('offset_enbd_cards_jonus');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if($whereraw != '')
				{
					$reports = JonusLoan::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue);
				}
				else
				{
					$reports = JonusLoan::orderBy("id","DESC")->paginate($paginationValue);
				}
				$reports->setPath(config('app.url/enbdCardsJonusReport'));
				
				
				
				
				
					if($whereraw != '')
				{
					
					$reportsCount = JonusLoan::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = JonusLoan::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				return view("MISLoan/JonusLoan/enbdLoanJonusReport",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
			}
			
			
			public function listingENBDLoanJonus(Request $request)
			{
				
			  $whereraw = '';
			  $selectedFilter['filterId'] = '';
			  $selectedFilter['filterValue'] = '';
			  $selectedFilter['report'] = '';
			 
			if(!empty($request->session()->get('jonus_loan_filter_selected_id')))
			  {
					$filterSelectedId = $request->session()->get('jonus_loan_filter_selected_id');
					$filterSelectedValue = $request->session()->get('jonus_loan_filter_selected_value');
					 $selectedFilter['filterId'] = $filterSelectedId;
					 $selectedFilter['filterValue'] = $filterSelectedValue;
					if($filterSelectedId == 1)
					{
						if($whereraw == '')
						{
							$whereraw = 'APPLICATIONSID = "'.$filterSelectedValue.'"';
						}
						else
						{
							$whereraw .= ' And APPLICATIONSID = "'.$filterSelectedValue.'"';
						}
					}
					else if($filterSelectedId == 2)
					{
						if($whereraw == '')
						{
							$whereraw = 'NAME = "'.$filterSelectedValue.'"';
						}
						else
						{
							$whereraw .= ' And NAME = "'.$filterSelectedValue.'"';
						}
					}
					else if($filterSelectedId == 5)
					{
						if($whereraw == '')
						{
							$whereraw = 'PRODUCT = "'.$filterSelectedValue.'"';
						}
						else
						{
							$whereraw .= ' And PRODUCT = "'.$filterSelectedValue.'"';
						}
					}
					else if($filterSelectedId == 3)
					{
							$agentList = array();
							$agent_details = Employee_details::where("status",1)->where("source_code","!=","-")->where("dept_id",9)->get();
							foreach($agent_details as $agentId)
							{
								$locName = $this->getLocation($agentId->id);
								if($locName == $filterSelectedValue)
								{
									$agentList[] = $agentId->id;
								}
							}
							if(count($agentList) >0)
							{
							$agentListstr = implode(",",$agentList);
							
						if($whereraw == '')
						{
							$whereraw = 'employee_id IN ('.$agentListstr.')';
						}
						else
						{
							$whereraw .= ' And employee_id IN ('.$agentListstr.')';
						}
							}
						
					}
					else if($filterSelectedId == 4)
					{
						
						if($whereraw == '')
						{
							$whereraw = 'employee_id = "'.$filterSelectedValue.'"';
						}
						else
						{
							$whereraw .= ' And employee_id = "'.$filterSelectedValue.'"';
						}
					}
					
			  } 
			
				if(!empty($request->session()->get('offset_enbd_loan_jonus')))
				{
					
					$paginationValue = $request->session()->get('offset_enbd_loan_jonus');
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
				$reports->setPath(config('app.url/listingENBDLoanJonus'));
				
				
				
				
				
				if($whereraw != '')
				{
					
					$reportsCount = JonusLoan::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = JonusLoan::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				
				return view("MISLoan/JonusLoan/listingENBDLoanJonus",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
			}
			
			
			
			
			
			
			public function getLocation($id)
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
			public function setOffSetJonusLoan(Request $request)
			{
				$offset = $request->offset;
				$request->session()->put('offset_enbd_loan_jonus',$offset);
				return  redirect('enbdLoanJonusReport');
			}
			 public function jonusUploadAjaxLoan(Request $request)
		   {
			   $currentDate = date("Y-m-d");
				
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
				return view("MISLoan/JonusLoan/jonusUploadAjaxLoan",compact('uploadStatusJonusLoansCount','jonusReportLogDetails','jonusReportLoglist'));
		   }
		   
		   public function reloadCalRenderLoan(Request $request)
		   {
			   $monthSelected = $request->m;
			   $yearSelected = $request->y;
			   return view("MISLoan/JonusLoan/reloadCalRenderLoan",compact('monthSelected','yearSelected'));
		   }
		   
		  
		     public function ENBDLoanFileUpload(Request $request)
				{
					$response = array();
				  /* $request->validate([

					'file' => 'required|mimes:csv,txt|max:10000',

				]); 
				 $validator = Validator::make($request->only('file'), [
					'file' => 'required|mimes:csv,xlsx|max:100000000',
				]);

				   if ($validator->fails()) {
					   $response['code'] = '300';
					   $response['message'] = $validator;
					  
					}
					else
					{*/

					$fileName = 'ENBD-Loan-MIS_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

					$request->file->move(public_path('uploads/misImport'), $fileName);
					$spreadsheet = new Spreadsheet();
					$inputFileType = 'Xlsx';
					$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/'.$fileName;
					$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
					$reader->setReadDataOnly(true);
					$spreadsheet = $reader->load($inputFileName);
					$worksheet = $spreadsheet->getActiveSheet();
					$highestRow = $worksheet->getHighestRow()-1; // e.g. 10											
					$misObjImport = new ENBDLoanImportFiles();
					$misObjImport->file_name = $fileName;
					$misObjImport->save();
						$response['code'] = '200';
					   $response['message'] = "You have successfully upload file.";
					   $response['filename'] = $fileName;
					   $response['filenameID'] = $misObjImport->id;
					   $response['totalcount'] = $highestRow;
					//}
					   echo json_encode($response);
					   exit;
					
				}
				
				public function ENBDLoanFileImport(Request $request)
						{
							$result = array();
							$attr_f_import = $request->attr_f_import;
							$inserteddate = $request->inserteddate;
							$conter = $request->counter;
							
							$empDetailsDat = ENBDLoanImportFiles::find($attr_f_import);
							$filename = $empDetailsDat->file_name;
							$filenameSelectedForImport = $empDetailsDat->file_name;
							$uploadPath = '/srv/www/htdocs/hrm/public/uploads/misImport/';
							$fullpathFileName = $uploadPath . $filename;
							 
							$spreadsheet = new Spreadsheet();
							$inputFileType = 'Xlsx';
							$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
							$reader->setReadDataOnly(true);
							$spreadsheet = $reader->load($fullpathFileName);
							$sheetData = $spreadsheet->getActiveSheet()->toArray();   
							if(!empty($sheetData)){
									$appId = trim($this->checkSharePointCode($sheetData[$conter][0]));
									$appIdExist = JonusLoan::where("APPLICATIONSID",$appId)->first();
									
									if($appIdExist != '')
									{
										$rowId = $appIdExist->id;
										$enbdCardsObj = JonusLoan::find($rowId);
									}
									else
									{
									$enbdCardsObj = new JonusLoan();
									$enbdCardsObj->APPLICATIONSID = trim($this->checkSharePointCode($sheetData[$conter][0]));
									}
									$enbdCardsObj->NAME = trim($this->checkSharePointCode($sheetData[$conter][1]));
									
									$enbdCardsObj->ASSET_DETAILS = trim($this->checkSharePointCode($sheetData[$conter][2]));
									$enbdCardsObj->CONSTITUTION = trim($this->checkSharePointCode($sheetData[$conter][3]));
									$enbdCardsObj->CUST_CATEGORY = trim($this->checkSharePointCode($sheetData[$conter][4]));
									$enbdCardsObj->PROF_QUALIFICATION = trim($this->checkSharePointCode($sheetData[$conter][5]));
									$enbdCardsObj->SIGNEDDATETIME = $sheetData[$conter][6];
									if($this->checkSharePointCode($sheetData[$conter][7]=='.00')){
										$PointCode=number_format('0',2);
									}
									else{
										$PointCode=number_format(trim(str_replace(' ', '',$this->checkSharePointCode($sheetData[$conter][7])),2));
									}
									$enbdCardsObj->ASSET_COST = $PointCode;
									$enbdCardsObj->LOAN_AMOUNT = number_format(trim(str_replace(' ', '',$this->checkSharePointCode($sheetData[$conter][8]))),2);
									$enbdCardsObj->TENURE = trim($this->checkSharePointCode($sheetData[$conter][9]));
									$enbdCardsObj->CHANNEL = trim($this->checkSharePointCode($sheetData[$conter][10]));
									$enbdCardsObj->PRODUCT = trim($this->checkSharePointCode($sheetData[$conter][11]));
									$enbdCardsObj->SCHEME_GROUP = trim($this->checkSharePointCode($sheetData[$conter][12]));
									$enbdCardsObj->SCHEME_NAME = trim($this->checkSharePointCode($sheetData[$conter][13]));
									$enbdCardsObj->PROMOTION_SCHEME = trim($this->checkSharePointCode($sheetData[$conter][14]));
									$enbdCardsObj->LOAN_TYPE = trim($this->checkSharePointCode($sheetData[$conter][15]));
									$enbdCardsObj->BRANCH_NAME = trim($this->checkSharePointCode($sheetData[$conter][16]));
									$enbdCardsObj->RBE_NAME = trim($this->checkSharePointCode($sheetData[$conter][17]));
									$enbdCardsObj->CPV_FIRED = trim($this->checkSharePointCode($sheetData[$conter][18]));
									$enbdCardsObj->CPV_STATUS = trim($this->checkSharePointCode($sheetData[$conter][19]));
									$enbdCardsObj->DSA = trim($this->checkSharePointCode($sheetData[$conter][20]));
									$enbdCardsObj->CREDIT_STATUS = trim($this->checkSharePointCode($sheetData[$conter][21]));
									$enbdCardsObj->EMPLOYER_NAME = trim($this->checkSharePointCode($sheetData[$conter][22]));
									$enbdCardsObj->EMPLOYER_CATG = trim($this->checkSharePointCode($sheetData[$conter][23]));
									$enbdCardsObj->PREVIOUS_LIABILITY = trim($this->checkSharePointCode($sheetData[$conter][24]));
									$enbdCardsObj->TOPUP_EXIST_LOAN_NO = trim($this->checkSharePointCode($sheetData[$conter][25]));
									$enbdCardsObj->TOPUP_AMOUNT_REQ = number_format((float)trim(str_replace(' ', '',$this->checkSharePointCode($sheetData[$conter][26]))),2, '.', ',');
									$enbdCardsObj->TOPUP_OS_PRINCIPAL = number_format((float)trim(str_replace(' ', '',$this->checkSharePointCode($sheetData[$conter][27]))),2, '.', ',');
									$enbdCardsObj->TOPUP_TOTAL_OS = number_format((float)trim(str_replace(' ', '',$this->checkSharePointCode($sheetData[$conter][28]))),2, '.', ',');
									$enbdCardsObj->TO_BANK = trim($this->checkSharePointCode($sheetData[$conter][29]));
									$enbdCardsObj->TO_BRANCH = trim($this->checkSharePointCode($sheetData[$conter][30]));
									$enbdCardsObj->TO_ACC_NO = trim($this->checkSharePointCode($sheetData[$conter][31]));
									$enbdCardsObj->TO_OS_AMT = number_format((float)trim(str_replace(' ', '',$this->checkSharePointCode($sheetData[$conter][32]))),2, '.', ',');
									$enbdCardsObj->TO_ADD_AMT = number_format((float)trim(str_replace(' ', '',$this->checkSharePointCode($sheetData[$conter][33]))),2, '.', ',');
									$enbdCardsObj->AUTH_STATUS = trim($this->checkSharePointCode($sheetData[$conter][34]));
									$enbdCardsObj->DISBURSAL_STATUS = trim($this->checkSharePointCode($sheetData[$conter][35]));
									$enbdCardsObj->DISBURSAL_DATETIME = $sheetData[$conter][36];
									$enbdCardsObj->CAS_STATUS = trim($this->checkSharePointCode($sheetData[$conter][37]));
									$enbdCardsObj->MANUFACTURER_NAME = trim($this->checkSharePointCode($sheetData[$conter][38]));
									$enbdCardsObj->SHOWROOM_NAME = trim($this->checkSharePointCode($sheetData[$conter][39]));
									$enbdCardsObj->DEALER_SALES_REP_NAME = trim($this->checkSharePointCode($sheetData[$conter][40]));
									$enbdCardsObj->RATE = trim($this->checkSharePointCode($sheetData[$conter][41]));
									$enbdCardsObj->RATE_TYPE = trim($this->checkSharePointCode($sheetData[$conter][42]));
									$enbdCardsObj->MARGIN_MONEY = trim($this->checkSharePointCode($sheetData[$conter][43]));
									$enbdCardsObj->CUSTOMER_TYPE = trim($this->checkSharePointCode($sheetData[$conter][44]));
									$enbdCardsObj->DOB_DOI = trim($this->checkSharePointCode($sheetData[$conter][45]));
									$enbdCardsObj->LOAN_NATURE = trim($this->checkSharePointCode($sheetData[$conter][46]));
									$enbdCardsObj->NATIONALITY = trim($this->checkSharePointCode($sheetData[$conter][47]));
									$enbdCardsObj->UAE_NATIONAL = trim($this->checkSharePointCode($sheetData[$conter][48]));
									$enbdCardsObj->INTEREST_START_DATETIME = $sheetData[$conter][49];
									$enbdCardsObj->PAYMENT_MODE = trim($this->checkSharePointCode($sheetData[$conter][50]));
									$enbdCardsObj->LOAN_PURPOSE = trim($this->checkSharePointCode($sheetData[$conter][51]));
									$enbdCardsObj->LAST_UPDATETIMED = trim($this->checkSharePointCode($sheetData[$conter][52]));
									$enbdCardsObj->DISCREPANCY_FLAG = trim($this->checkSharePointCode($sheetData[$conter][53]));
									$enbdCardsObj->FILERECEIPTDTTIME = trim($this->checkSharePointCode($sheetData[$conter][54]));
									$enbdCardsObj->SOURCED_ON = trim($this->checkSharePointCode($sheetData[$conter][55]));
									$enbdCardsObj->REFERRAL_GROUP = trim($this->checkSharePointCode($sheetData[$conter][56]));
									$enbdCardsObj->REFERRAL_CODE = trim($this->checkSharePointCode($sheetData[$conter][57]));
									$enbdCardsObj->REFERRAL_NAME = trim($this->checkSharePointCode($sheetData[$conter][58]));
									$enbdCardsObj->P1CODE = trim($this->checkSharePointCode($sheetData[$conter][59]));
									$enbdCardsObj->LAA_PRODUCT_ID_C = trim($this->checkSharePointCode($sheetData[$conter][60]));
									$enbdCardsObj->LAST_REMARKS_ADDED = trim($this->checkSharePointCode($sheetData[$conter][61]));
								
									$enbdCardsObj->created_by = $request->session()->get('EmployeeId');
									$enbdCardsObj->date_sourcing = date("Y-m-d",strtotime($sheetData[$conter][6]));
									$enbdCardsObj->match_status = 1;
								/* 	$arrayDatAttribute[$iCsvIndex]['updated_at'] = date("Y-m-d");
									$arrayDatAttribute[$iCsvIndex]['created_at'] =  date("Y-m-d"); */
									
									/*
									*get Employee ID
									*/
									$bank_code = trim($this->checkSharePointCode($sheetData[$conter][59]));
									$employeeDetails = Employee_details::where("source_code",$bank_code)->first();
									if($employeeDetails != '')
									{
											$enbdCardsObj->employee_id = $employeeDetails->id;
											$enbdCardsObj->Employee_status = "Verified";
									}
									else
									{
										$enbdCardsObj->Employee_status = "Not-verified";
									}
									/*
									*get Employee ID
									*/
									$enbdCardsObj->save();
									
								
							$jonusLogObj = new JonusReportLog();
							$jonusLogObj->uploaded_date = date("Y-m-d",strtotime($inserteddate));
							$jonusLogObj->created_date = date("Y-m-d");
							$jonusLogObj->time_values = date("h:i A");
							$jonusLogObj->type = 'loan';
							$jonusLogObj->file_name = $filenameSelectedForImport;
							$jonusLogObj->created_by = $request->session()->get('EmployeeId');
							$jonusLogObj->save();
							$result['code'] = 200;
							}
							else
							{
								$result['code'] = 300;
							}
							/*
							*making logs
							*/
							/* $enbdCardsObj = new ENBDCardsMisReport();
							$enbdCardsObj->insert($arrayDatAttribute); */
						// $request->session()->flash('success','Import Completed.');
						  echo json_encode($result);
						  exit;
							/*  return back()

						->with('success','Import Completed.'); */
						}
						public function checkSharePointCode($Data=NULL){
						//echo $Data;
							$arraydata=array('_x0020_','_x0033_','_x007e_','_x0021_','_x0040_','_x0023_','_x0024_','_x0025_','_x005E_','_x0026_','_x002A_','_x0028_','_x0029_','_x002B_','_x002D_','_x003D_','_x007B_','_x007D_','_x003A_',
							'_x0022_','_x007C_','_x003A_','_x0027_','_x005C_','_x003C_','_x003E_','_x003F_','_x002C_','_x002E_','_x002F_','_x0060_','_x005B_','_x005D_',
							'_x0031_','_x0032_','_x0035_','_x0039_','_x0036_','_x0009_','_x0034_','_x0030_','_x0034_','_x0037_','_x0038_');
							//$_val="DINERS_x0020_BUNDLE_x0020_PRODUCT";
							$finaldata='';
							foreach($arraydata as $_val){
							$place = strpos($Data, $_val);
							if (!empty($place)) {
							$Data = str_replace($_val, ' ',$Data);
		
							$finaldata=$Data;
							
							}
							else{
								$Data = str_replace($_val, ' ',$Data);
								$finaldata=$Data;
							}
							
							}
							
							return $finaldata;
							//echo "hello";						
							
						}
			
			public static function getdaysDifference($loopDate)
			{
				$loopD = date("Y-m-d",strtotime($loopDate));
				$now = time(); // or your date as well
				$your_date = strtotime($loopD);
				$datediff = $now - $your_date;

				return  round($datediff / (60 * 60 * 24));
			}
			public function showMainFiltersLoan(Request $request)
			{
				$filterId =  $request->filterId;
				$filterValue =  $request->filterValue;
				$appIdArray = array();
				$customername = array();
				$location = array();
				$byAgent = array();
				$cardsMisData = JonusLoan::get();
				if($filterId == 1)
				{
					foreach($cardsMisData as $cardsMis)
					{
						if(!empty($cardsMis->APPLICATIONSID))
						{
						$appIdArray[$cardsMis->APPLICATIONSID] = $cardsMis->APPLICATIONSID;
						}
					}
				}
				else if($filterId == 2)
				{
					foreach($cardsMisData as $cardsMis)
					{
						if(!empty($cardsMis->NAME))
						{
						$customername[$cardsMis->NAME] = $cardsMis->NAME;
						}
					}
				}
				else if($filterId == 3)
				{
					$location['DUBAI'] = 'DUBAI';
					$location['ABU DHABI'] = 'ABU DHABI';
				}
				else if($filterId == 4)
				{
					foreach($cardsMisData as $cardsMis)
					{
						if(!empty($cardsMis->employee_id))
						{
						$byAgent[$cardsMis->employee_id] = $this->getEmployeeName($cardsMis->employee_id);
						}
					}
				}
				return view("MISLoan/JonusLoan/showMainFiltersLoan",compact('appIdArray','customername','location','byAgent','filterId','filterValue'));
			}
		   public function getEmployeeName($id)
			{
				$empData =Employee_details::where("id",$id)->first();
				if($empData != '')
				{
				return $empData->first_name.' '.$empData->middle_name.' '.$empData->last_name;
				}
				else
				{
				return '--';
				}
			}
			
			
			public static function getAgentName($id)
			{
				
				$empData =Employee_details::where("id",$id)->first();
				if($empData != '')
				{
				return $empData->first_name.' '.$empData->middle_name.' '.$empData->last_name;
				}
				else
				{
				return '--';
				}
			}
			
			public function applyfilterJonusLoan(Request $request)
			{
				$selectedValue =  $request->selectedValue;
				$filterid = $request->filterid;
				
				$selectedFilter = $request->input();
			
				$request->session()->put('jonus_loan_filter_selected_id',$filterid);
				$request->session()->put('jonus_loan_filter_selected_value',$selectedValue);
				 return  redirect('enbdLoanJonusReport');
			}
			
			public function updateFilterJonusLoan(Request $request)
			{
				$filterList = array();
				
				
			    if(!empty($request->session()->get('jonus_loan_filter_selected_id')))
				  {
					   $filterList['report']['id'] = $request->session()->get('jonus_loan_filter_selected_id');
					   $filterList['report']['value'] = $request->session()->get('jonus_loan_filter_selected_value');
				  }
				
				
				return view("MISLoan/JonusLoan/updateFilterJonusLoan",compact('filterList'));
			}
			  public function cancelFiltersJonusLoan(Request $request)
			   {
				   $type = $request->type;
				   if($type == 'report')
				   {
						$request->session()->put('jonus_loan_filter_selected_id','');  
						$request->session()->put('jonus_loan_filter_selected_value',''); 
								
				   }
				   
			   }
			   
			   public function updateDataOfsource()
			   {
				   $enbdMisMods = ENBDCardsMisReport::get();
				   foreach($enbdMisMods as $mod)
				   {
					   $enbdMisModUpdate = ENBDCardsMisReport::find($mod->id);
					   $dateofsourcing = $mod->DATEOFSOURCING;
					   $enbdMisModUpdate->date_sourcing = date("Y-m-d",strtotime($dateofsourcing));
					   $enbdMisModUpdate->save();
				   }
				   echo "done";
				   exit;
			   }
			   
			   
			   public function jonusUploadAjaxTab(Request $request)
			   {
				   $currentDate = date("Y-m-d");
					/*
					*checking for jonus cards
					*/
					
					$uploadStatusJonusCardsCount = 1;
					$uploadStatusJonusCards = JonusReportLog::whereDate("uploaded_date",$currentDate)->where("type","cards-t")->orderBy("id","DESC")->first();
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
					return view("MIS/Jonus/jonusUploadAjaxTab",compact('uploadStatusJonusCardsCount','uploadStatusJonusLoansCount','jonusReportLogDetails','jonusReportLoglist'));
			   }
			   
			   
public function ENBDTabCardsFileUpload(Request $request)
				{
					$response = array();
				  /* $request->validate([

					'file' => 'required|mimes:csv,txt|max:10000',

				]); */
				 $validator = Validator::make($request->only('file'), [
					'file' => 'required|mimes:csv,txt|max:10000',
				]);

				   if ($validator->fails()) {
					   $response['code'] = '300';
					   $response['message'] = $validator;
					  
					}
					else
					{

					$fileName = 'ENBD-Tab-Cards-MIS_'.date("Y-m-d_h-i-s").'.csv';  

		   

					$request->file->move(public_path('uploads/misImport'), $fileName);

					$misObjImport = new ENBDCardsImportFiles();
					$misObjImport->file_name = $fileName;
					$misObjImport->save();
						$response['code'] = '200';
					   $response['message'] = "You have successfully upload file.";
					   $response['filename'] = $fileName;
					   $response['filenameID'] = $misObjImport->id;
					}
					   echo json_encode($response);
					   exit;
					
				}
				
				public function ENBDTabCardsFileImport(Request $request)
						{
							$result = array();
							$attr_f_import = $request->attr_f_import;
							$inserteddate = $request->inserteddate;
							
							$empDetailsDat = ENBDCardsImportFiles::find($attr_f_import);
							$filename = $empDetailsDat->file_name;
							$filenameSelectedForImport = $empDetailsDat->file_name;
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
							
							 
							if(count($dataFromCsv[1]) == 15 && count($dataFromCsv) >1)
							{
								
							$iCsv = 0;
							$iCsvIndex = 0;
							$arrayDat = array();
							$arrayDatAttribute = array();
							   
							$valuesCheck = array();
							foreach ($dataFromCsv as $fromCsv) {
								if ($iCsv != 0 && $fromCsv[1] != '') {
									
									/*
						*LOC_ADD
						*/
						
						$AppID = $fromCsv[2];
						$checkExistTracker = MainMisReportTab::where("application_number",$AppID)->first();
						if($checkExistTracker != '')
						{
							$misObj = MainMisReportTab::find($checkExistTracker->id);
						}
						else
						{
							$misObj = new MainMisReportTab();
						}
						$misObj->customer_type = $fromCsv[0];
						$misObj->application_mode = $fromCsv[1];
						$misObj->application_number = $fromCsv[2];
						$misObj->application_status = $fromCsv[3];
						$misObj->application_created = $fromCsv[4];
						$misObj->application_createdBy = $fromCsv[5];
						$misObj->created_group = $fromCsv[6];
						$misObj->created_month = $fromCsv[7];
						$misObj->STP_NSTP_flag = $fromCsv[8];
						$misObj->customer_name = $fromCsv[9];
						$misObj->RBE_Code = $fromCsv[10];
						$misObj->DMS_Outcome = $fromCsv[11];
						$misObj->DMS_Status_Description = $fromCsv[12];
						$misObj->Card_Name = $fromCsv[13];
						$misObj->Scheme = $fromCsv[14];
						
						
						
						
						
						$scCode = $fromCsv[10];
						if(!empty($scCode))
						{
							
								$bank_code = $scCode;
								$employeeDetails = Employee_details::where("source_code",$scCode)->first();
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
						
						$misObj->created_by = $request->session()->get('EmployeeId');
						
					
						$misObj->save();
						
						/*
						*update Data In main MIS
						*/
						$misTabId = $misObj->id;
						$misTabModel = MainMisReportTab::where("id",$misTabId)->first();
							$checkAppIDExistInInternalMIS = MainMisReport::where("application_id",$AppID)->first();
							if($checkAppIDExistInInternalMIS  != '')
							{
								$MISInternalObj =  MainMisReport::find($checkAppIDExistInInternalMIS->id);
							}
							else
							{
								$MISInternalObj = new MainMisReport();
							}
							$MISInternalObj->application_id = $misTabModel->application_number;
							$MISInternalObj->date_of_submission = $misTabModel->application_created;
							$MISInternalObj->customer_type = $misTabModel->customer_type;
							$MISInternalObj->STP_NSTP_flag = $misTabModel->STP_NSTP_flag;
							$MISInternalObj->DMS_Outcome = $misTabModel->DMS_Outcome;
							$MISInternalObj->DMS_Status_Description = $misTabModel->DMS_Status_Description;
							$MISInternalObj->Card_Name = $misTabModel->Card_Name;
							$MISInternalObj->Scheme = $misTabModel->Scheme;
							$MISInternalObj->employee_id = $misTabModel->employee_id;
							$MISInternalObj->Employee_status = $misTabModel->Employee_status;
							$MISInternalObj->application_mode = $misTabModel->application_mode;
							$MISInternalObj->file_source = 'Tab';
							$MISInternalObj->created_by = $misTabModel->created_by;
							if($misTabModel->application_status == 'COMPLETED')
							{
								$MISInternalObj->approved_notapproved = 3;
							}
							elseif($misTabModel->application_status == 'REJECTED')
							{
								$MISInternalObj->approved_notapproved = 5;
							}
							elseif($misTabModel->application_status == 'CANCELLED')
							{
								$MISInternalObj->approved_notapproved = 2;
							}
							elseif($misTabModel->application_status == 'SENT_TO_CHECKER')
							{
								$MISInternalObj->approved_notapproved = 7;
							}
							elseif($misTabModel->application_status == 'AO_COMPLETED_REJECT_REVIEW')
							{
								$MISInternalObj->approved_notapproved = 7;
							}
							elseif($misTabModel->application_status == 'SENT_TO_COMPLIANCE')
							{
								$MISInternalObj->approved_notapproved = 7;
							}
							elseif($misTabModel->application_status == 'SUBMITTED')
							{
								$MISInternalObj->approved_notapproved = 7;
							}
							elseif($misTabModel->application_status == 'REJECT_REVIEW')
							{
								$MISInternalObj->approved_notapproved = 7;
							}
							elseif($misTabModel->application_status == 'REJECTED_BY_CHECKER')
							{
								$MISInternalObj->approved_notapproved = 7;
							}
							elseif($misTabModel->application_status == 'SENT_TO_RCC')
							{
								$MISInternalObj->approved_notapproved = 7;
							}
							elseif($misTabModel->application_status == 'AO_COMPLETED_REFERRED_BY_RCC')
							{
								$MISInternalObj->approved_notapproved = 7;
							}
							elseif($misTabModel->application_status == 'REFERRED_BY_RCC')
							{
								$MISInternalObj->approved_notapproved = 7;
							}
							elseif($misTabModel->application_status == 'ERROR_IN_PROCESSING')
							{
								$MISInternalObj->approved_notapproved = 7;
							}
							elseif($misTabModel->application_status == 'AO_COMPLETED_SENT_TO_RCC')
							{
								$MISInternalObj->approved_notapproved = 7;
							}
							elseif($misTabModel->application_status == 'REFERRED_BY_COMPLIANCE')
							{
								$MISInternalObj->approved_notapproved = 7;
							}
							elseif($misTabModel->application_status == 'CANCELLED_FSK_HIT')
							{
								$MISInternalObj->approved_notapproved = 2;
							}
							if($misTabModel->Employee_status == 'Verified')
							{
							$MISInternalObj->SE_CODE_NAME = $employeeDetails->first_name.' '.$employeeDetails->middle_name.' '.$employeeDetails->last_name.'_'.$scCode;
							}
							$MISInternalObj->cm_name = $misTabModel->customer_name;
							$MISInternalObj->complete_status = 2;
							$MISInternalObj->submission_format = date("Y-m-d",strtotime($misTabModel->application_created));
							$MISInternalObj->match_status = 2;
							$MISInternalObj->hand_on_status = 2;
							
							
							$MISInternalObj->save();
							
						/*
						*Update Data In main MIS
						*/
						$iCsvIndex++;
									
									
									
									
													
									
								}
								$iCsv++;
							}
							/* echo '<pre>';
							print_r($arrayDatAttribute);
							exit; */
							/*
							*making logs
							*/
							$jonusLogObj = new JonusReportLog();
							$jonusLogObj->uploaded_date = date("Y-m-d",strtotime($inserteddate));
							$jonusLogObj->created_date = date("Y-m-d");
							$jonusLogObj->time_values = date("h:i A");
							$jonusLogObj->type = 'cards-t';
							$jonusLogObj->file_name = $filenameSelectedForImport;
							$jonusLogObj->created_by = $request->session()->get('EmployeeId');
							$jonusLogObj->save();
							$result['code'] = 200;
							}
							else
							{
								$result['code'] = 300;
							}
							/*
							*making logs
							*/
							/* $enbdCardsObj = new ENBDCardsMisReport();
							$enbdCardsObj->insert($arrayDatAttribute); */
						// $request->session()->flash('success','Import Completed.');
						  echo json_encode($result);
						  exit;
							/*  return back()

						->with('success','Import Completed.'); */
						}	
	public function runAjaxToUpdateStatusPreload(Request $request)
	{
		$precall = PrecallingFile::where("status",1)->orderBy("id","DESC")->first();
		if($precall != '')
		{
		$precallModel = PrecallingFile::find($precall->id);
		$fileName = $precallModel->filename;
		/*
		*check for MIS
		*
		*/
		$fileNameArray = explode("_",$fileName);
		
		$phoneNo = $fileNameArray[5];
		$phoneNoFinal = substr($phoneNo, 3);
		
	
		 $mainReportDetails = MainMisReport::where("CV_MOBILE_NUMBER",$phoneNoFinal)->first();
		 if($mainReportDetails != '')
		 {
			 $precallModel->status=3;
			 $precallModel->mis_id=$mainReportDetails->id;
			 $precallModel->created_By=$request->session()->get('EmployeeId');
		 }
		 else
		 {
			 $precallModel->status=2;
		 }
		/*
		*check for MIS
		*
		*/
		
		$precallModel->save();
		echo 'updated';
		exit;
		}
		else
		{
			echo "Nothing to update";
			exit;
		}
	}						
}
