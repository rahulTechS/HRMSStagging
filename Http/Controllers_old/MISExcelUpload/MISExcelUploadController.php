<?php

namespace App\Http\Controllers\MISExcelUpload;

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
use App\Models\MISExcelUpload\ENBDCardsMisReportExcel;
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
use App\Models\MISExcelUpload\MainMisReportTabExcel;
use App\Models\MIS\PrecallingFile;
use App\Models\LoanMis\ENDBLoanMis;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Logs\ENBDCardsLogs;


class MISExcelUploadController extends Controller
{
  
			
			
			public function enbdCardsJonusReportExcel(Request $request)
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
					$reports = enbdCardsMISReport::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue);
				}
				else
				{
					$reports = enbdCardsMISReport::orderBy("id","DESC")->paginate($paginationValue);
				}
				$reports->setPath(config('app.url/enbdCardsJonusReportExcel'));
				
				
				
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
				return view("MISExcelUpload/Jonus/enbdCardsJonusReport",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
			}
			
			
			public function listingENBDCardJonusManual(Request $request)
			{
				
			  $whereraw = '';
			  $selectedFilter['filterId'] = '';
			  $selectedFilter['filterValue'] = '';
			  $selectedFilter['report'] = '';
			  
			  
			/* 	echo $whereraw;exit; */
			if(!empty($request->session()->get('jonus_filter_selected_id')))
			  {
					$filterSelectedId = $request->session()->get('jonus_filter_selected_id');
					$filterSelectedValue = $request->session()->get('jonus_filter_selected_value');
					 $selectedFilter['filterId'] = $filterSelectedId;
					 $selectedFilter['filterValue'] = $filterSelectedValue;
					if($filterSelectedId == 1)
					{
						if($whereraw == '')
						{
							$whereraw = 'application_id = "'.$filterSelectedValue.'"';
						}
						else
						{
							$whereraw .= ' And application_id = "'.$filterSelectedValue.'"';
						}
					}
					else if($filterSelectedId == 2)
					{
						if($whereraw == '')
						{
							$whereraw = 'customer_name = "'.$filterSelectedValue.'"';
						}
						else
						{
							$whereraw .= ' And customer_name = "'.$filterSelectedValue.'"';
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
					$reports = enbdCardsMISReport::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue)->onEachSide(0);
				}
				else
				{
				
					$reports = enbdCardsMISReport::orderBy("id","DESC")->paginate($paginationValue)->onEachSide(0);
				}
				$reports->setPath(config('app.url/listingENBDCardJonusManual'));
				
				
				
				
				
				if($whereraw != '')
				{
					
					$reportsCount = enbdCardsMISReport::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = enbdCardsMISReport::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				
				return view("MIS/Jonus/listingENBDCardJonusManual",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
			}
			
			
			
			public function listingENBDCardJonusTab(Request $request)
			{
				
			  $whereraw = '';
			  $selectedFilter['filterId'] = '';
			  $selectedFilter['filterValue'] = '';
			  $selectedFilter['report'] = '';
			  
			
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
					$reports = MainMisReportTab::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue)->onEachSide(0);
				}
				else
				{
				
					$reports = MainMisReportTab::orderBy("id","DESC")->paginate($paginationValue)->onEachSide(0);
				}
				$reports->setPath(config('app.url/listingENBDCardJonusTab'));
				
				
				
				
				
				if($whereraw != '')
				{
					
					$reportsCount = MainMisReportTab::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = MainMisReportTab::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				
				return view("MIS/Jonus/listingENBDCardJonusTab",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
			}
			
			
			
			public function setOffSetForENDBCardsJonus(Request $request)
			{
				$offset = $request->offset;
				$request->session()->put('offset_enbd_cards_jonus',$offset);
				return  redirect('enbdCardsJonusReport');
			}
			 public function jonusUploadAjaxExcel(Request $request)
		   {
			   $currentDate = date("Y-m-d");
				/*
				*checking for jonus cards
				*/
				
				$uploadStatusJonusCardsCount = 1;
				$uploadStatusJonusCards = JonusReportLog::whereDate("uploaded_date",$currentDate)->where("type","cards-m")->orderBy("id","DESC")->first();
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
				return view("MISExcelUpload/Jonus/jonusUploadAjax",compact('uploadStatusJonusCardsCount','uploadStatusJonusLoansCount','jonusReportLogDetails','jonusReportLoglist'));
		   }
		   
		   public function reloadCalRenderExcel(Request $request)
		   {
			   $monthSelected = $request->m;
			   $yearSelected = $request->y;
			   return view("MISExcelUpload/Jonus/reloadCalRender",compact('monthSelected','yearSelected'));
		   }
		   
		   public function reloadCalRenderTabExcel(Request $request)
		   {
			   $monthSelected = $request->m;
			   $yearSelected = $request->y;
			   return view("MISExcelUpload/Jonus/reloadCalRenderTab",compact('monthSelected','yearSelected'));
		   }
		   
		     public function ENBDCardsFileUploadExcel(Request $request)
				{
					
							
					$response = array();
				  /* $request->validate([

					'file' => 'required|mimes:csv,txt|max:10000',

				]); */
				 /* $validator = Validator::make($request->only('file'), [
					'file' => 'required|mimes:csv,xlsx|max:100000000000',
				]);

				   if ($validator->fails()) {
					   $response['code'] = '300';
					   $response['message'] = $validator;
					  
					}
					else
					{ */

					$fileName = 'ENBD-Cards-MIS_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

					$request->file->move(public_path('uploads/misImport/'), $fileName);
						$spreadsheet = new Spreadsheet();

						$inputFileType = 'Xlsx';
						$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/'.$fileName;

						/*  Create a new Reader of the type defined in $inputFileType  */
						$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
						/*  Advise the Reader that we only want to load cell data  */
						$reader->setReadDataOnly(true);
						$spreadsheet = $reader->load($inputFileName);
						$worksheet = $spreadsheet->getActiveSheet();
						// Get the highest row number and column letter referenced in the worksheet
						$highestRow = $worksheet->getHighestRow()-1; // e.g. 10											
					
					$misObjImport = new ENBDCardsImportFiles();
					$misObjImport->file_name = $fileName;
					$misObjImport->save();
						$response['code'] = '200';
					   $response['message'] = "You have successfully upload file.";
					   $response['filename'] = $fileName;
					   $response['filenameID'] = $misObjImport->id;
					   $response['totalcount'] = $highestRow;
					/* } */
					   echo json_encode($response);
					   exit;
					
				}
				
				public function ENBDCardsFileImportExcel(Request $request)
						{
							
							$result = array();
							$attr_f_import = $request->attr_f_import;
							$inserteddate = $request->inserteddate;
							$conter = $request->counter;
							
							$empDetailsDat = ENBDCardsImportFiles::find($attr_f_import);
							$filename = $empDetailsDat->file_name;
							$filenameSelectedForImport = $empDetailsDat->file_name;
							
							$uploadPath = '/srv/www/htdocs/hrm/public/uploads/misImport/';
							$fullpathFileName = $uploadPath . $filename;
							
								 $spreadsheet = new Spreadsheet();

								$inputFileType = 'Xlsx';
								//$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/excel/'.$fileName;

								/*  Create a new Reader of the type defined in $inputFileType  */
								$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
								/*  Advise the Reader that we only want to load cell data  */
								$reader->setReadDataOnly(true);
								$spreadsheet = $reader->load($fullpathFileName);
								$sheetData = $spreadsheet->getActiveSheet()->toArray();
								
								//$data=$sheetData->getIndex();
								//$data=$sheetData->getRowIterator($conter);
								//echo "<pre>";
									
								if(!empty($sheetData)){
									
									$appId = trim($sheetData[$conter][1]);
									$appIdExist = ENBDCardsMisReportExcel::where("application_id",$appId)->first();
									
									if($appIdExist != '')
									{
										$rowId = $appIdExist->id;
										$enbdCardsObj = ENBDCardsMisReportExcel::find($rowId);
									}
									else
									{
									$enbdCardsObj = new ENBDCardsMisReportExcel();
									$enbdCardsObj->application_id = trim($sheetData[$conter][1]);
									}
									//$enbdCardsObj = new ENBDCardsMisReport();
									//$sheetData[$conter][1];exit;
									//echo trim($this->checkSharePointCode($sheetData[$conter][1]));exit;
									//$this->checkSharePointCode($sheetData[$conter][1]);
									$enbdCardsObj->CARDID = trim($this->checkSharePointCode($sheetData[$conter][0]));
									//exit;
									$enbdCardsObj->application_id = trim($this->checkSharePointCode($sheetData[$conter][1]));
									$enbdCardsObj->customer_name = trim($this->checkSharePointCode($sheetData[$conter][2]));
									$enbdCardsObj->FILERECEIPTDTTIME = $sheetData[$conter][3];
									$enbdCardsObj->OFFER = trim($this->checkSharePointCode($sheetData[$conter][4]));
									$enbdCardsObj->CURRENTACTIVITY = trim($this->checkSharePointCode($sheetData[$conter][5]));
									$enbdCardsObj->STATUS = trim($this->checkSharePointCode($sheetData[$conter][6]));
									$enbdCardsObj->APPLICATIONTYPE = trim($this->checkSharePointCode($sheetData[$conter][7]));
									$enbdCardsObj->DATEOFSOURCING = trim($this->checkSharePointCode($sheetData[$conter][8]));
									$enbdCardsObj->SIGNEDDATE = trim($this->checkSharePointCode($sheetData[$conter][9]));
									$enbdCardsObj->PRODUCT = trim($this->checkSharePointCode($sheetData[$conter][10]));
									$enbdCardsObj->SCHEMEGROUP = trim($this->checkSharePointCode($sheetData[$conter][11]));
									$enbdCardsObj->SCHEME = trim($this->checkSharePointCode($sheetData[$conter][12]));
									$enbdCardsObj->CHANNELCODE = trim($this->checkSharePointCode($sheetData[$conter][13]));
									$enbdCardsObj->DSA_BRANCH = trim($this->checkSharePointCode($sheetData[$conter][14]));
									$enbdCardsObj->DME_RBE = trim($this->checkSharePointCode($sheetData[$conter][15]));
									$enbdCardsObj->APP_REJ_CANDATE_TIME = trim($this->checkSharePointCode($sheetData[$conter][16]));
									$enbdCardsObj->LASTREMARKSADDED = trim($this->checkSharePointCode($sheetData[$conter][17]));
									$enbdCardsObj->CHANNELCODEPERV =trim($this->checkSharePointCode( $sheetData[$conter][18]));
									$enbdCardsObj->EVSTATUS = trim($this->checkSharePointCode($sheetData[$conter][19]));
									$enbdCardsObj->EVACTIONDATE = trim($this->checkSharePointCode($sheetData[$conter][20]));
									$enbdCardsObj->EVUSER = trim($this->checkSharePointCode($sheetData[$conter][21]));
									$enbdCardsObj->CVSTATUS = trim($this->checkSharePointCode($sheetData[$conter][22]));
									$enbdCardsObj->CVACTIONDATE = $sheetData[$conter][23];
									$enbdCardsObj->WCSTATUS = trim($this->checkSharePointCode($sheetData[$conter][24]));
									$enbdCardsObj->WCACTIONDATE = $sheetData[$conter][25];
									$enbdCardsObj->WCREMARKS = trim($this->checkSharePointCode($sheetData[$conter][26]));
									$enbdCardsObj->APPLICATIONCREDITSTATUS = trim($this->checkSharePointCode($sheetData[$conter][27]));
									$enbdCardsObj->CARDAPPROVALSTATUS = trim($this->checkSharePointCode($sheetData[$conter][28]));
									$enbdCardsObj->LASTUPDATED = trim($this->checkSharePointCode($sheetData[$conter][29]));
									$enbdCardsObj->PRI_SUPP_STANDALONE = trim($this->checkSharePointCode($sheetData[$conter][30]));
									$enbdCardsObj->PRIMARYCARD_STAND_ALONE = trim($this->checkSharePointCode($sheetData[$conter][31]));
									$enbdCardsObj->PRIMARY_ACC_NO_STANDALONE = trim($this->checkSharePointCode($sheetData[$conter][32]));
									$enbdCardsObj->CARDTYPE = trim($this->checkSharePointCode($sheetData[$conter][33]));
									$enbdCardsObj->BILLINGCYCLE = trim($this->checkSharePointCode($sheetData[$conter][34]));
									$enbdCardsObj->REQUESTEDLIMIT = trim($this->checkSharePointCode($sheetData[$conter][35]));
									$enbdCardsObj->APPROVEDLIMIT = trim($this->checkSharePointCode($sheetData[$conter][36]));
									$enbdCardsObj->SOURCED_ON = trim($this->checkSharePointCode($sheetData[$conter][37]));
									$enbdCardsObj->REPORTGENDATE = trim($this->checkSharePointCode($sheetData[$conter][38]));
									$enbdCardsObj->REFERRAL_GROUP = trim($this->checkSharePointCode($sheetData[$conter][39]));
									$enbdCardsObj->REFERRAL_CODE = trim($this->checkSharePointCode($sheetData[$conter][40]));
									$enbdCardsObj->REFERRALNAME = trim($this->checkSharePointCode($sheetData[$conter][41]));
									$enbdCardsObj->P1CODE = trim($this->checkSharePointCode($sheetData[$conter][42]));
									$enbdCardsObj->CASSTATUS = trim($this->checkSharePointCode($sheetData[$conter][43]));
									$enbdCardsObj->created_by = $request->session()->get('EmployeeId');
									$enbdCardsObj->date_sourcing = date("Y-m-d",strtotime($sheetData[$conter][8]));
									$enbdCardsObj->match_status = 1;
								/* 	$arrayDatAttribute[$iCsvIndex]['updated_at'] = date("Y-m-d");
									$arrayDatAttribute[$iCsvIndex]['created_at'] =  date("Y-m-d"); */
									
									/*
									*get Employee ID
									*/
									$bank_code = trim($this->checkSharePointCode($sheetData[$conter][42]));
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
									//print_r($enbdCardsObj);exit;
									$enbdCardsObj->save();
								
							
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
							$jonusLogObj->type = 'cards-m';
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
							'_x0031_','_x0032_','_x0035_','_x0039_','_x0036_','_x0009_','_x0034_','_x0030_','_x0034_');
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
			   
			   
			   public function jonusUploadAjaxTabExcel(Request $request)
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
					return view("MISExcelUpload/Jonus/jonusUploadAjaxTab",compact('uploadStatusJonusCardsCount','uploadStatusJonusLoansCount','jonusReportLogDetails','jonusReportLoglist'));
			   }
			   
			   
public function ENBDTabCardsFileUploadExcel(Request $request)
				{
					$response = array();
				  /* $request->validate([

					'file' => 'required|mimes:csv,txt|max:10000',

				]); */
				/*  $validator = Validator::make($request->only('file'), [
					'file' => 'required|mimes:csv,xlsx|max:1000000',
				]);

				   if ($validator->fails()) {
					   $response['code'] = '300';
					   $response['message'] = $validator;
					  
					}
					else
					{ */

					$fileName = 'ENBD-Tab-Cards-MIS_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

					$request->file->move(public_path('uploads/misImport/'), $fileName);
						$spreadsheet = new Spreadsheet();
						$inputFileType = 'Xlsx';
						$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/'.$fileName;
						/*  Create a new Reader of the type defined in $inputFileType  */
						$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
						/*  Advise the Reader that we only want to load cell data  */
						$reader->setReadDataOnly(true);
						$spreadsheet = $reader->load($inputFileName);
						$worksheet = $spreadsheet->getActiveSheet();
						// Get the highest row number and column letter referenced in the worksheet
						$highestRow = $worksheet->getHighestRow()-1; // e.g. 10
						$misObjImport = new ENBDCardsImportFiles();
						$misObjImport->file_name = $fileName;
						$misObjImport->save();
						$response['code'] = '200';
					   $response['message'] = "You have successfully upload file.";
					   $response['filename'] = $fileName;
					   $response['filenameID'] = $misObjImport->id;
					   $response['totalcount'] = $highestRow;
					/*}*/
					   echo json_encode($response);
					   exit;
					
				}
				
				public function ENBDTabCardsFileImportExcel(Request $request)
						{ //echo "Hello";
							//print_r($request);exit;
							//echo "hello";exit;
							$result = array();
							$attr_f_import = $request->attr_f_import;
							$inserteddate = $request->inserteddate;
							$conter = $request->counter;
							
							$empDetailsDat = ENBDCardsImportFiles::find($attr_f_import);
							$filename = $empDetailsDat->file_name;
							$filenameSelectedForImport = $empDetailsDat->file_name;
							//$file = fopen($fullpathFileName, "r");
							$uploadPath = '/srv/www/htdocs/hrm/public/uploads/misImport/';
							$fullpathFileName = $uploadPath . $filename;
							
								 $spreadsheet = new Spreadsheet();

								$inputFileType = 'Xlsx';
								//$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/excel/'.$fileName;

								/*  Create a new Reader of the type defined in $inputFileType  */
								$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
								/*  Advise the Reader that we only want to load cell data  */
								$reader->setReadDataOnly(true);
								$spreadsheet = $reader->load($fullpathFileName);
								$sheetData = $spreadsheet->getActiveSheet()->toArray();
								//print_r($sheetData);exit;
							    if(!empty($sheetData)){
							
							
						$AppID = trim($this->checkSharePointCode($sheetData[$conter][2]));
						$checkExistTracker = MainMisReportTabExcel::where("application_number",$AppID)->first();
						if($checkExistTracker != '')
						{
							$misObj = MainMisReportTabExcel::find($checkExistTracker->id);
						}
						else
						{
							$misObj = new MainMisReportTabExcel();
						}
						$misObj->customer_type = trim($this->checkSharePointCode($sheetData[$conter][0]));
						$misObj->application_mode = trim($this->checkSharePointCode($sheetData[$conter][1]));
						$misObj->application_number = trim($this->checkSharePointCode($sheetData[$conter][2]));
						$misObj->application_status = trim($this->checkSharePointCode($sheetData[$conter][3]));
						$application_createddate = ($sheetData[$conter][4] - 25569) * 86400;
						$date_application_created = gmdate("d-m-Y H:i:s A", $application_createddate);
						$misObj->application_created = trim($this->checkSharePointCode($date_application_created));
						$misObj->application_createdBy = trim($this->checkSharePointCode($sheetData[$conter][5]));
						$misObj->created_group = trim($this->checkSharePointCode($sheetData[$conter][6]));
						$misObj->created_month = trim($this->checkSharePointCode($sheetData[$conter][7]));
						$misObj->STP_NSTP_flag = trim($this->checkSharePointCode($sheetData[$conter][8]));
						$misObj->customer_name = trim($this->checkSharePointCode($sheetData[$conter][9]));
						$misObj->RBE_Code = trim($this->checkSharePointCode($sheetData[$conter][10]));
						$misObj->DMS_Outcome = trim($this->checkSharePointCode($sheetData[$conter][11]));
						$misObj->DMS_Status_Description = trim($this->checkSharePointCode($sheetData[$conter][12]));
						$misObj->Card_Name = trim($this->checkSharePointCode($sheetData[$conter][13]));
						$misObj->Scheme = trim($this->checkSharePointCode($sheetData[$conter][14]));
						$misObj->employee_id = trim($this->checkSharePointCode($sheetData[$conter][15]));
						$misObj->Employee_status = trim($this->checkSharePointCode($sheetData[$conter][16]));
						$misObj->created_by = trim($this->checkSharePointCode($sheetData[$conter][17]));
						$misObj->updated_at = date("Y-m-d",strtotime($inserteddate));
						$misObj->created_at =date("Y-m-d");
						$misObj->handsOnReport = 1;	
						$submitted_datedat = ($sheetData[$conter][19] - 25569) * 86400;
						$date_column_submit = gmdate("d-m-Y H:i:s A", $submitted_datedat);
						//echo strtotime($sheetData[$conter][19]);exit;
						$misObj->submitted_date = trim($this->checkSharePointCode($date_column_submit));
						//echo $sheetData[$conter][20];exit;
						$closedate = ($sheetData[$conter][20] - 25569) * 86400;
						$date_column_closedate = gmdate("d-m-Y H:i:s A", $closedate);
						$misObj->close_date= trim($this->checkSharePointCode($date_column_closedate));
						$misObj->sourcing_duration = trim($this->checkSharePointCode($sheetData[$conter][21]));
						$misObj->creation_location = trim($this->checkSharePointCode($sheetData[$conter][22]));
						$misObj->submission_location = trim($this->checkSharePointCode($sheetData[$conter][23]));						
						$scCode = trim($this->checkSharePointCode($sheetData[$conter][10]));
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
						$misTabModel = MainMisReportTabExcel::where("id",$misTabId)->first();
							$checkAppIDExistInInternalMIS = MainMisReport::where("application_id",$AppID)->where("over_ride_status","!=",1)->first();
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
							$MISInternalObj->over_ride_status = 1;
							
							
							$MISInternalObj->save();
							
						/*
						*Update Data In main MIS
						*/
						
									
									
									
									
													
									
							
								
							
							/* echo '<pre>';
							print_r($arrayDatAttribute);
							exit; */
							 
			  /**
			  *Making Logs
			  *start code
			  */
			  $approved_notapprovedValue = MainMisReport::where("id",$MISInternalObj->id)->first()->approved_notapproved;
			  $logsENBDCards = new ENBDCardsLogs();
			  $logsENBDCards->type = 'Tab';
			  $logsENBDCards->action = $approved_notapprovedValue;
			  $logsENBDCards->action_date = date("Y-m-d");
			  $logsENBDCards->action_by = $request->session()->get('EmployeeId');
			  $logsENBDCards->action_area = "Jonus Update Status";
			  $logsENBDCards->mis_id = $MISInternalObj->id;
			  $logsENBDCards->source = 'Entry';
			  $logsENBDCards->save();
			  /**
			  *Making Logs
			  *End code
			  */
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
					
}
