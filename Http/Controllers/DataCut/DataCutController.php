<?php

namespace App\Http\Controllers\DataCut;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\DataCut\ENBDCardsDatacutImportFiles;
use App\Models\DataCut\ENBDDataCutImportFiles;
use App\Models\DataCut\ENBDDataCutCards;
use App\Models\DataCut\ENBDDataCut;
use App\Models\MIS\JonusReportLog;
use App\Models\DataCut\EnbdMisCardsPhysicalDatacut;
use App\Models\DataCut\EnbdMisCardsTabDatacut;
use App\Models\DataCut\enbdFinalCompleteMISDatacutTab;
use App\Models\DataCut\DatacutInformation;
use App\Models\DataCut\EnbdFinalMISCompletePhysical;
use App\Models\DataCut\EnbdRMCompletePerformance;
use App\Models\DataCut\EnbdFinalMisCompletebothCreditCards;
use App\Models\DataCut\EnbdRMCompletePerformanceLoan;
use App\Models\DataCut\AbudhabiProductInfo;
use App\Models\MIS\MainMisReport;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use App\Models\SEPayout\AgentPayout;
use App\Models\Industry\CompanyListComplete;
class DataCutController extends Controller
{
    public function manageDataCut()
	{
		return view("DataCut/manageDataCut");
	}
       
	   public function jonusUploadAjaxDataCutCards(Request $request)
	   {
		   
		    $currentDate = date("Y-m-d");
				
				/*
				*checking for jonus loan
				*/
				$uploadStatusJonusDatacutCardsCount = 1;
				$uploadStatusJonusLoans = JonusReportLog::whereDate("uploaded_date",$currentDate)->where("type","datacut")->orderBy("id","DESC")->first();
				if($uploadStatusJonusLoans == '')
				{
					$uploadStatusJonusDatacutCardsCount = 2;
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
				return view("DataCut/jonusUploadAjaxDataCutCards",compact('uploadStatusJonusDatacutCardsCount','jonusReportLogDetails','jonusReportLoglist'));
	   }
	   
	   
	   
	   
	    public function jonusUploadAjaxDataCutLoans(Request $request)
		   {
			   
				$currentDate = date("Y-m-d");
					
					/*
					*checking for jonus loan
					*/
					$uploadStatusJonusDatacutCardsCount = 1;
					$uploadStatusJonusLoans = JonusReportLog::whereDate("uploaded_date",$currentDate)->where("type","datacut-loans")->orderBy("id","DESC")->first();
					if($uploadStatusJonusLoans == '')
					{
						$uploadStatusJonusDatacutCardsCount = 2;
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
					return view("DataCut/jonusUploadAjaxDataCutLoans",compact('uploadStatusJonusDatacutCardsCount','jonusReportLogDetails','jonusReportLoglist'));
		   }
	    
		   public function reloadCalRenderDataCutCards(Request $request)
		   {
			   $monthSelected = $request->m;
			   $yearSelected = $request->y;
			   return view("DataCut/reloadCalRenderDataCutCards",compact('monthSelected','yearSelected'));
		   }
		   
		   public function reloadCalRenderDataCutLoans(Request $request)
		   {
			   $monthSelected = $request->m;
			   $yearSelected = $request->y;
			   return view("DataCut/reloadCalRenderDataCutLoans",compact('monthSelected','yearSelected'));
		   }
		   public function ENBDDataCutCardsFileUpload(Request $request)
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

					$fileName = 'ENBD-DataCutCards-MIS_'.date("Y-m-d_h-i-s").'.csv';  

		   

					$request->file->move(public_path('uploads/misImport'), $fileName);

					$misObjImport = new ENBDCardsDatacutImportFiles();
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
				
				
				
				 public function ENBDDataCutLoansFileUpload(Request $request)
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

					$fileName = 'ENBD-DataCutLoans-MIS_'.date("Y-m-d_h-i-s").'.csv';  

		   

					$request->file->move(public_path('uploads/misImport'), $fileName);

					$misObjImport = new ENBDDataCutImportFiles();
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
				
			public function ENBDDataCutCardsFileImport(Request $request)
						{
							
							$result = array();
							$attr_f_import = $request->attr_f_import;
							$inserteddate = $request->inserteddate;
							
							$empDetailsDat = ENBDCardsDatacutImportFiles::find($attr_f_import);
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
							 
							 
							if(count($dataFromCsv[1]) == 12 && count($dataFromCsv) >1)
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
									$appId = trim($fromCsv[0]);
									$appIdExist = ENBDDataCutCards::where("APP_ID_C",$appId)->first();
									
									if($appIdExist != '')
									{
										$rowId = $appIdExist->id;
										$enbdCardsObj = ENBDDataCutCards::find($rowId);
									}
									else
									{
									$enbdCardsObj = new ENBDDataCutCards();
									$enbdCardsObj->APP_ID_C = trim($fromCsv[0]);
									}
									$enbdCardsObj->status_value = $fromCsv[1];
									
									$enbdCardsObj->CARD_TYPE = $fromCsv[2];
									$enbdCardsObj->P1CODE = $fromCsv[3];
									$enbdCardsObj->STRUSERFIELD20 = $fromCsv[4];
									$enbdCardsObj->CUST_FIRST_NAME = $fromCsv[5];
									$enbdCardsObj->CUST_WORK_CITY1 = $fromCsv[6];
									$enbdCardsObj->CARD_DESC = $fromCsv[7];
									$enbdCardsObj->Agency = $fromCsv[8];
									$enbdCardsObj->CC_UP = $fromCsv[9];
									$enbdCardsObj->DINER_BUNDLE_IND = $fromCsv[10];
									$enbdCardsObj->CARD_CLASS = $fromCsv[11];
									
								
									$enbdCardsObj->created_by = $request->session()->get('EmployeeId');
									
								
									$enbdCardsObj->save();
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
							$jonusLogObj->type = 'datacut';
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
	   
	   
	   public function listingENBDDataCutCardsJonus(Request $request)
			{
				
			  $whereraw = '';
			  $selectedFilter['filterId'] = '';
			  $selectedFilter['filterValue'] = '';
			  $selectedFilter['report'] = '';
			/*  
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
					
			  }  */
			
				if(!empty($request->session()->get('offset_enbd_datacutcards_jonus')))
				{
					
					$paginationValue = $request->session()->get('offset_enbd_datacutcards_jonus');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if($whereraw != '')
				{
					$reports = ENBDDataCutCards::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue)->onEachSide(0);
				}
				else
				{
				
					$reports = ENBDDataCutCards::orderBy("id","DESC")->paginate($paginationValue)->onEachSide(0);
				}
				$reports->setPath(config('app.url/listingENBDDataCutCardsJonus'));
				
				
				
				
				
				if($whereraw != '')
				{
					
					$reportsCount = ENBDDataCutCards::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = ENBDDataCutCards::get()->count();
				}
				
				
				
				return view("DataCut/listingENBDDataCutCardsJonus",compact('reports','reportsCount','paginationValue','selectedFilter'));
			}
			
	public function setOffSetJonusDataCutCards(Request $request)
	{
		$offset = $request->offset;
				$request->session()->put('offset_enbd_datacutcards_jonus',$offset);
				return  redirect('manageDataCut');
	}
	public function setOffSetJonusDataCutLoans(Request $request)
	{
		$offset = $request->offset;
				$request->session()->put('offset_enbd_datacutloans_jonus',$offset);
				return  redirect('manageDataCut');
	}
	
	
	public function ENBDDataCutLoansFileImport(Request $request)
						{
							
							$result = array();
							$attr_f_import = $request->attr_f_import;
							$inserteddate = $request->inserteddate;
							
							$empDetailsDat = ENBDDataCutImportFiles::find($attr_f_import);
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
							
							if(count($dataFromCsv[1]) == 9 && count($dataFromCsv) >1)
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
									$appId = trim($fromCsv[0]);
									$appIdExist = ENBDDataCut::where("APPLID",$appId)->first();
									
									if($appIdExist != '')
									{
										$rowId = $appIdExist->id;
										$enbdCardsObj = ENBDDataCut::find($rowId);
									}
									else
									{
									$enbdCardsObj = new ENBDDataCut();
									$enbdCardsObj->APPLID = trim($fromCsv[0]);
									}
									$enbdCardsObj->INSPECTORNAME = $fromCsv[1];
									
									$enbdCardsObj->DISBURSEDAMOUNT = $fromCsv[2];
									$enbdCardsObj->EFFRATE = $fromCsv[3];
									$enbdCardsObj->SUPPLIERDESC = $fromCsv[4];
									$enbdCardsObj->CIF_RC_CODE = $fromCsv[5];
									$enbdCardsObj->AMTFIN = $fromCsv[6];
									$enbdCardsObj->Agency = $fromCsv[7];
									$enbdCardsObj->FINAL_AMOUNT = $fromCsv[8];
									
								
									$enbdCardsObj->created_by = $request->session()->get('EmployeeId');
									
								
									$enbdCardsObj->save();
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
							$jonusLogObj->type = 'datacut-loans';
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
		   public function listingENBDDataCutLoansJonus(Request $request)
			{
				
			  $whereraw = '';
			  $selectedFilter['filterId'] = '';
			  $selectedFilter['filterValue'] = '';
			  $selectedFilter['report'] = '';
			/*  
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
					
			  }  */
			
				if(!empty($request->session()->get('offset_enbd_datacutloans_jonus')))
				{
					
					$paginationValue = $request->session()->get('offset_enbd_datacutloans_jonus');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if($whereraw != '')
				{
					$reports = ENBDDataCut::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue)->onEachSide(0);
				}
				else
				{
				
					$reports = ENBDDataCut::orderBy("id","DESC")->paginate($paginationValue)->onEachSide(0);
				}
				$reports->setPath(config('app.url/listingENBDDataCutLoansJonus'));
				
				
				
				
				
				if($whereraw != '')
				{
					
					$reportsCount = ENBDDataCut::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = ENBDDataCut::get()->count();
				}
				
				
				
				return view("DataCut/listingENBDDataCutLoansJonus",compact('reports','reportsCount','paginationValue','selectedFilter'));
			}
			
			public function generateFinalDataCut(Request $request)
			{
				$datacutInfo = DatacutInformation::where("datacut_match_status",1)->orderBy("id","DESC")->first();
				if($datacutInfo != '')
				{
					$appId = trim($datacutInfo->APP_ID_C);
					$mainMisData = MainMisReport::where("application_id",$appId)->where("file_source","manual")->orderBy("id","DESC")->first();
					if($mainMisData != '')
					{
						$checkExist = EnbdMisCardsPhysicalDatacut::where("application_id",$appId)->first();
						if($checkExist != '')
						{
							$addRecords = EnbdMisCardsPhysicalDatacut::find($checkExist->id);
						}
						else
						{
							$addRecords = new EnbdMisCardsPhysicalDatacut();
						}
						
						$addRecords->date_of_submission = $mainMisData->date_of_submission;
						$addRecords->application_type = $mainMisData->application_type;
						$addRecords->lead_source = $mainMisData->lead_source;
						$addRecords->PRODUCT = $mainMisData->PRODUCT;
						$addRecords->application_id = $mainMisData->application_id;
						$addRecords->current_activity = $mainMisData->current_activity;
						$addRecords->approved_notapproved = $mainMisData->approved_notapproved;
						$addRecords->monthly_ends = $mainMisData->monthly_ends;
						$addRecords->last_remarks_added = $mainMisData->last_remarks_added;
						$addRecords->cm_name = $mainMisData->cm_name;
						$addRecords->fv_company_name = $mainMisData->fv_company_name;
						$addRecords->company_name_as_per_visa = $mainMisData->company_name_as_per_visa;
						$addRecords->ALE_NALE = $mainMisData->ALE_NALE;
						$addRecords->CV_MOBILE_NUMBER = $mainMisData->CV_MOBILE_NUMBER;
						$addRecords->EV_DIRECT_OFFICE_NO = $mainMisData->EV_DIRECT_OFFICE_NO;
						$addRecords->E_MAILADDRESS = $mainMisData->E_MAILADDRESS;
						$addRecords->SALARY = $mainMisData->SALARY;
						$addRecords->LOS = $mainMisData->LOS;
						$addRecords->ACCOUNT_STATUS = $mainMisData->ACCOUNT_STATUS;
						$addRecords->ACCOUNT_NO = $mainMisData->ACCOUNT_NO;
						$addRecords->SALARIED = $mainMisData->SALARIED;
						$addRecords->TL = $mainMisData->TL;
						$addRecords->SE_CODE_NAME = $mainMisData->SE_CODE_NAME;
						$addRecords->REFERENCE_NAME = $mainMisData->REFERENCE_NAME;
						$addRecords->REFERENCE_MOBILE_NO = $mainMisData->REFERENCE_MOBILE_NO;
						$addRecords->NATIONALITY = $mainMisData->NATIONALITY;
						$addRecords->PASSPORT_NO = $mainMisData->PASSPORT_NO;
						$addRecords->DOB = $mainMisData->DOB;
						$addRecords->VISA_Expiry_DATE = $mainMisData->VISA_Expiry_DATE;
						$addRecords->DESIGNATION = $mainMisData->DESIGNATION;
						$addRecords->MMN = $mainMisData->MMN;
						$addRecords->EIDA = $mainMisData->EIDA;
						$addRecords->IBAN = $mainMisData->IBAN;
						$addRecords->EV = $mainMisData->EV;
						$addRecords->Type_of_Income_Proof = $mainMisData->Type_of_Income_Proof;
						$addRecords->file_source = $mainMisData->file_source;
						$addRecords->other_bank = $mainMisData->other_bank;
						$addRecords->employee_id = $mainMisData->employee_id;
						$addRecords->Employee_status = $mainMisData->Employee_status;
						$addRecords->created_by = $mainMisData->created_by;
						$addRecords->submission_format = $mainMisData->submission_format;
						$addRecords->Offer = $mainMisData->Offer;
						$addRecords->Scheme = $mainMisData->Scheme;
						$addRecords->ev_status = $mainMisData->ev_status;
						$addRecords->last_updated = $mainMisData->last_updated;
						$addRecords->last_updated_date = $mainMisData->last_updated_date;
						$addRecords->approve_limit = $mainMisData->approve_limit;
						$addRecords->match_status = $mainMisData->match_status;
						$addRecords->Card_Name = $mainMisData->Card_Name;
						$addRecords->current_activity_internal = $mainMisData->current_activity_internal;
						$addRecords->approved_notapproved_internal = $mainMisData->approved_notapproved_internal;
						$addRecords->mothly_end_internal = $mainMisData->mothly_end_internal;
						$addRecords->monthly_end_number = $mainMisData->monthly_end_number;
						$addRecords->internal_updated_jonus = $mainMisData->internal_updated_jonus;
						$addRecords->datacut_id = $datacutInfo->id;
						$addRecords->end_sales_time = $datacutInfo->sales_time;
						$addRecords->mis_id = $mainMisData->id;
						$addRecords->submission_location = $mainMisData->submission_location;
						$addRecords->CARD_DESC = $datacutInfo->CARD_DESC;
						$addRecords->new_data = 1;
						
						if($addRecords->save())
							{
							$updateDataCut = DatacutInformation::find($datacutInfo->id);
							$updateDataCut->datacut_match_status = 2;
							$updateDataCut->save();
							
							
							$updateDataCutMainMIS = MainMisReport::find($mainMisData->id);
							$updateDataCutMainMIS->datacut_match_status = 2;
							$updateDataCutMainMIS->save();
								echo "updated";
								exit;
							}
						else
							{
								$updateDataCut = DatacutInformation::find($datacutInfo->id);
								$updateDataCut->datacut_match_status = 5;
								$updateDataCut->save();
								
								$updateDataCutMainMIS = MainMisReport::find($mainMisData->id);
								$updateDataCutMainMIS->datacut_match_status = 5;
								$updateDataCutMainMIS->save();
								echo "Issue in Updated";
								exit;
							}
					}
					else
					{
						$updateDataCut = DatacutInformation::find($datacutInfo->id);
						$updateDataCut->datacut_match_status = 3;
						$updateDataCut->save();
						echo "Not matched In internal mis";
								exit;
					}
				}
				else
				{
					echo "data Done";
					exit;
				}
			}
			
			
			public function dataCutVintange(Request $request)
			{
				$vintangeDataCut = EnbdMisCardsPhysicalDatacut::where("vintange_status",1)->where("Employee_status","Verified")->first();
				if($vintangeDataCut != '')
				{
					
				
				$employeeData = Employee_details::where("id",$vintangeDataCut->employee_id)->first();
						if($employeeData != '')
						{
							$empId = $employeeData->emp_id;
							$deptId = $employeeData->dept_id;
							$empAttr = Employee_attribute::where("emp_id",$empId)->where("dept_id",$deptId)->where("attribute_code","DOJ")->first();
							if($empAttr != '')
							{
								$salesTime = $vintangeDataCut->end_sales_time;
								$salesTimeArray = explode("-",$salesTime);
								if($salesTimeArray[0] == 2)
								{
									$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-28';
								}
								else
								{
								$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-30';
								}
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);//exit;
									
									//$date1 = date("Y-m-d",strtotime($doj));
									$daysInterval = abs(strtotime($salesTimeValue)-strtotime($doj))/ (60 * 60 * 24);
									$agentPUpdate = EnbdMisCardsPhysicalDatacut::find($vintangeDataCut->id);
									$agentPUpdate->vintage = $daysInterval;
									$agentPUpdate->doj = $doj;
									$agentPUpdate->vintange_status = 2;
									$agentPUpdate->save();
									echo "yes";
									exit;
									
								}
								else
								{
									$agentPUpdate = EnbdMisCardsPhysicalDatacut::find($vintangeDataCut->id);
									
									$agentPUpdate->vintange_status = 3;
									$agentPUpdate->save();
									echo "not";
									exit;
								}
							}
								else
								{
									$agentPUpdate = EnbdMisCardsPhysicalDatacut::find($vintangeDataCut->id);
									
									$agentPUpdate->vintange_status = 3;
									$agentPUpdate->save();
									echo "not";
									exit;
								}	
							
						}
						else
						{
							$agentPUpdate = EnbdMisCardsPhysicalDatacut::find($vintangeDataCut->id);
									
									$agentPUpdate->vintange_status = 3;
									$agentPUpdate->save();
									echo "not";
									exit;
						}
						
				}
				else
				{
					echo "All Done";
					exit;
				}
			}
			
			
			
			
			public function generateFinalMIS(Request $request)
			{
			
				$interMIS = MainMisReport::where("datacut_match_status",1)->where("file_source","manual")->orderBy("id","DESC")->first();
				if($interMIS != '')
				{
					$appId = trim($interMIS->application_id);
					$existingCheck = EnbdFinalMISCompletePhysical::where("application_id",$appId)->where("match_datacut",1)->orderBy("id","DESC")->first();
					
						
						if($existingCheck != '')
						{
							$addRecords = EnbdFinalMISCompletePhysical::find($existingCheck->id);
						}
						else
						{
							$addRecords = new EnbdFinalMISCompletePhysical();
						}
						
						$addRecords->date_of_submission = $interMIS->date_of_submission;
						$addRecords->application_type = $interMIS->application_type;
						$addRecords->lead_source = $interMIS->lead_source;
						$addRecords->PRODUCT = $interMIS->PRODUCT;
						$addRecords->application_id = $interMIS->application_id;
						$addRecords->current_activity = $interMIS->current_activity;
						$addRecords->approved_notapproved = $interMIS->approved_notapproved;
						$addRecords->monthly_ends = $interMIS->monthly_ends;
						$addRecords->last_remarks_added = $interMIS->last_remarks_added;
						$addRecords->cm_name = $interMIS->cm_name;
						$addRecords->fv_company_name = $interMIS->fv_company_name;
						$addRecords->company_name_as_per_visa = $interMIS->company_name_as_per_visa;
						$addRecords->ALE_NALE = $interMIS->ALE_NALE;
						$addRecords->CV_MOBILE_NUMBER = $interMIS->CV_MOBILE_NUMBER;
						$addRecords->EV_DIRECT_OFFICE_NO = $interMIS->EV_DIRECT_OFFICE_NO;
						$addRecords->E_MAILADDRESS = $interMIS->E_MAILADDRESS;
						$addRecords->SALARY = $interMIS->SALARY;
						$addRecords->LOS = $interMIS->LOS;
						$addRecords->ACCOUNT_STATUS = $interMIS->ACCOUNT_STATUS;
						$addRecords->ACCOUNT_NO = $interMIS->ACCOUNT_NO;
						$addRecords->SALARIED = $interMIS->SALARIED;
						$addRecords->TL = $interMIS->TL;
						$addRecords->SE_CODE_NAME = $interMIS->SE_CODE_NAME;
						$addRecords->REFERENCE_NAME = $interMIS->REFERENCE_NAME;
						$addRecords->REFERENCE_MOBILE_NO = $interMIS->REFERENCE_MOBILE_NO;
						$addRecords->NATIONALITY = $interMIS->NATIONALITY;
						$addRecords->PASSPORT_NO = $interMIS->PASSPORT_NO;
						$addRecords->DOB = $interMIS->DOB;
						$addRecords->VISA_Expiry_DATE = $interMIS->VISA_Expiry_DATE;
						$addRecords->DESIGNATION = $interMIS->DESIGNATION;
						$addRecords->MMN = $interMIS->MMN;
						$addRecords->EIDA = $interMIS->EIDA;
						$addRecords->IBAN = $interMIS->IBAN;
						$addRecords->EV = $interMIS->EV;
						$addRecords->Type_of_Income_Proof = $interMIS->Type_of_Income_Proof;
						$addRecords->file_source = $interMIS->file_source;
						$addRecords->other_bank = $interMIS->other_bank;
						$addRecords->employee_id = $interMIS->employee_id;
						$addRecords->Employee_status = $interMIS->Employee_status;
						$addRecords->created_by = $interMIS->created_by;
						$addRecords->submission_format = $interMIS->submission_format;
						$addRecords->Offer = $interMIS->Offer;
						$addRecords->Scheme = $interMIS->Scheme;
						$addRecords->ev_status = $interMIS->ev_status;
						$addRecords->last_updated = $interMIS->last_updated;
						$addRecords->last_updated_date = $interMIS->last_updated_date;
						$addRecords->approve_limit = $interMIS->approve_limit;
						$addRecords->match_status = $interMIS->match_status;
						$addRecords->Card_Name = $interMIS->Card_Name;
						$addRecords->current_activity_internal = $interMIS->current_activity_internal;
						$addRecords->approved_notapproved_internal = $interMIS->approved_notapproved_internal;
						$addRecords->mothly_end_internal = $interMIS->mothly_end_internal;
						$addRecords->monthly_end_number = $interMIS->monthly_end_number;
						$addRecords->internal_updated_jonus = $interMIS->internal_updated_jonus;
						$addRecords->submission_location = $interMIS->submission_location;
						$addRecords->match_datacut = 1;
						
						
						$addRecords->mis_id = $interMIS->id;
						
						
						if($addRecords->save())
							{
							
							
							$updateDataCutMainMIS = MainMisReport::find($interMIS->id);
							$updateDataCutMainMIS->datacut_match_status = 4;
							$updateDataCutMainMIS->save();
								echo "updated";
								exit;
							}
						else
							{
								
								
								$updateDataCutMainMIS = MainMisReport::find($interMIS->id);
								$updateDataCutMainMIS->datacut_match_status = 5;
								$updateDataCutMainMIS->save();
								echo "Issue in Updated";
								exit;
							}
					
				}
				else
				{
					echo "data Done";
					exit;
				}
			}
			
			
			public function dataCutVintangeFinal(Request $request)
			{
				$vintangeDataCut = EnbdFinalMISCompletePhysical::where("vintange_status",1)->where('match_datacut',1)->where("Employee_status","Verified")->first();
				if($vintangeDataCut != '')
				{
					
				
				$employeeData = Employee_details::where("id",$vintangeDataCut->employee_id)->first();
						if($employeeData != '')
						{
							$empId = $employeeData->emp_id;
							$deptId = $employeeData->dept_id;
							$empAttr = Employee_attribute::where("emp_id",$empId)->where("dept_id",$deptId)->where("attribute_code","DOJ")->first();
							if($empAttr != '')
							{
								$salesTimeValue = $vintangeDataCut->submission_format;
								
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);//exit;
									
									//$date1 = date("Y-m-d",strtotime($doj));
									$daysInterval = abs(strtotime($salesTimeValue)-strtotime($doj))/ (60 * 60 * 24);
									$agentPUpdate = EnbdFinalMISCompletePhysical::find($vintangeDataCut->id);
									$agentPUpdate->vintage = $daysInterval;
									$agentPUpdate->doj = $doj;
									$agentPUpdate->vintange_status = 2;
									$agentPUpdate->save();
									echo "yes";
									exit;
									
								}
								else
								{
									$agentPUpdate = EnbdFinalMISCompletePhysical::find($vintangeDataCut->id);
									
									$agentPUpdate->vintange_status = 3;
									$agentPUpdate->save();
									echo "not";
									exit;
								}
							}
								else
								{
									$agentPUpdate = EnbdFinalMISCompletePhysical::find($vintangeDataCut->id);
									
									$agentPUpdate->vintange_status = 3;
									$agentPUpdate->save();
									echo "not";
									exit;
								}	
							
						}
						else
						{
							$agentPUpdate = EnbdFinalMISCompletePhysical::find($vintangeDataCut->id);
									
									$agentPUpdate->vintange_status = 3;
									$agentPUpdate->save();
									echo "not";
									exit;
						}
						
				}
				else
				{
					echo "All Done";
					exit;
				}
			}
			
			
			
			
			
			public function generateFinalDataCutTab(Request $request)
			{
				$datacutInfo = DatacutInformation::where("datacut_match_status",1)->orderBy("id","DESC")->first();
				
				if($datacutInfo != '')
				{
					$appId = trim($datacutInfo->APP_ID_C);
					$mainMisData = MainMisReport::where("application_id",$appId)->where("file_source","Tab")->orderBy("id","DESC")->first();
				
					if($mainMisData != '')
					{
						$checkExist = EnbdMisCardsTabDatacut::where("application_id",$appId)->first();
						if($checkExist != '')
						{
							$addRecords = EnbdMisCardsTabDatacut::find($checkExist->id);
						}
						else
						{
							$addRecords = new EnbdMisCardsTabDatacut();
						}
						
						$addRecords->date_of_submission = $mainMisData->date_of_submission;
						$addRecords->application_type = $mainMisData->application_type;
						$addRecords->lead_source = $mainMisData->lead_source;
						$addRecords->PRODUCT = $mainMisData->PRODUCT;
						$addRecords->application_id = $mainMisData->application_id;
						$addRecords->current_activity = $mainMisData->current_activity;
						$addRecords->approved_notapproved = $mainMisData->approved_notapproved;
						$addRecords->monthly_ends = $mainMisData->monthly_ends;
						$addRecords->last_remarks_added = $mainMisData->last_remarks_added;
						$addRecords->cm_name = $mainMisData->cm_name;
						$addRecords->fv_company_name = $mainMisData->fv_company_name;
						$addRecords->company_name_as_per_visa = $mainMisData->company_name_as_per_visa;
						$addRecords->ALE_NALE = $mainMisData->ALE_NALE;
						$addRecords->CV_MOBILE_NUMBER = $mainMisData->CV_MOBILE_NUMBER;
						$addRecords->EV_DIRECT_OFFICE_NO = $mainMisData->EV_DIRECT_OFFICE_NO;
						$addRecords->E_MAILADDRESS = $mainMisData->E_MAILADDRESS;
						$addRecords->SALARY = $mainMisData->SALARY;
						$addRecords->LOS = $mainMisData->LOS;
						$addRecords->ACCOUNT_STATUS = $mainMisData->ACCOUNT_STATUS;
						$addRecords->ACCOUNT_NO = $mainMisData->ACCOUNT_NO;
						$addRecords->SALARIED = $mainMisData->SALARIED;
						$addRecords->TL = $mainMisData->TL;
						$addRecords->SE_CODE_NAME = $mainMisData->SE_CODE_NAME;
						$addRecords->REFERENCE_NAME = $mainMisData->REFERENCE_NAME;
						$addRecords->REFERENCE_MOBILE_NO = $mainMisData->REFERENCE_MOBILE_NO;
						$addRecords->NATIONALITY = $mainMisData->NATIONALITY;
						$addRecords->PASSPORT_NO = $mainMisData->PASSPORT_NO;
						$addRecords->DOB = $mainMisData->DOB;
						$addRecords->VISA_Expiry_DATE = $mainMisData->VISA_Expiry_DATE;
						$addRecords->DESIGNATION = $mainMisData->DESIGNATION;
						$addRecords->MMN = $mainMisData->MMN;
						$addRecords->EIDA = $mainMisData->EIDA;
						$addRecords->IBAN = $mainMisData->IBAN;
						$addRecords->EV = $mainMisData->EV;
						$addRecords->Type_of_Income_Proof = $mainMisData->Type_of_Income_Proof;
						$addRecords->file_source = $mainMisData->file_source;
						$addRecords->other_bank = $mainMisData->other_bank;
						$addRecords->employee_id = $mainMisData->employee_id;
						$addRecords->Employee_status = $mainMisData->Employee_status;
						$addRecords->created_by = $mainMisData->created_by;
						$addRecords->submission_format = $mainMisData->submission_format;
						$addRecords->Offer = $mainMisData->Offer;
						$addRecords->Scheme = $mainMisData->Scheme;
						$addRecords->ev_status = $mainMisData->ev_status;
						$addRecords->last_updated = $mainMisData->last_updated;
						$addRecords->last_updated_date = $mainMisData->last_updated_date;
						$addRecords->approve_limit = $mainMisData->approve_limit;
						$addRecords->match_status = $mainMisData->match_status;
						$addRecords->Card_Name = $mainMisData->Card_Name;
						$addRecords->application_mode = $mainMisData->application_mode;
						$addRecords->STP_NSTP_flag = $mainMisData->STP_NSTP_flag;
						$addRecords->customer_type = $mainMisData->customer_type;
						$addRecords->DMS_Outcome = $mainMisData->DMS_Outcome;
						$addRecords->DMS_Status_Description = $mainMisData->DMS_Status_Description;
						$addRecords->current_activity_tab = $mainMisData->current_activity_tab;
						$addRecords->internal_updated_jonus = $mainMisData->internal_updated_jonus;
						$addRecords->datacut_id = $datacutInfo->id;
						$addRecords->end_sales_time = $datacutInfo->sales_time;
						$addRecords->mis_id = $mainMisData->id;
						$addRecords->CARD_DESC = $datacutInfo->CARD_DESC;
						$addRecords->new_data = 1;
						if($addRecords->save())
							{
							$updateDataCut = DatacutInformation::find($datacutInfo->id);
							$updateDataCut->datacut_match_status = 2;
							$updateDataCut->save();
							
							
							$updateDataCutMainMIS = MainMisReport::find($mainMisData->id);
							$updateDataCutMainMIS->datacut_match_status = 2;
							$updateDataCutMainMIS->save();
								echo "updated";
								exit;
							}
						else
							{
								$updateDataCut = DatacutInformation::find($datacutInfo->id);
								$updateDataCut->datacut_match_status = 5;
								$updateDataCut->save();
								
								$updateDataCutMainMIS = MainMisReport::find($mainMisData->id);
								$updateDataCutMainMIS->datacut_match_status = 5;
								$updateDataCutMainMIS->save();
								echo "Issue in Updated";
								exit;
							}
					}
					else
					{
						$updateDataCut = DatacutInformation::find($datacutInfo->id);
						$updateDataCut->datacut_match_status = 3;
						$updateDataCut->save();
						echo "Not matched In internal mis";
								exit;
					}
				}
				else
				{
					echo "data Done";
					exit;
				}
			}
			
			
			
			
			public function dataCutVintangeTab(Request $request)
			{
				$vintangeDataCut = EnbdMisCardsTabDatacut::where("vintange_status",1)->where("Employee_status","Verified")->first();
				if($vintangeDataCut != '')
				{
					
				
				$employeeData = Employee_details::where("id",$vintangeDataCut->employee_id)->first();
						if($employeeData != '')
						{
							$empId = $employeeData->emp_id;
							$deptId = $employeeData->dept_id;
							$empAttr = Employee_attribute::where("emp_id",$empId)->where("dept_id",$deptId)->where("attribute_code","DOJ")->first();
							if($empAttr != '')
							{
								$salesTime = $vintangeDataCut->end_sales_time;
								$salesTimeArray = explode("-",$salesTime);
								if($salesTimeArray[0] == 2)
								{
									$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-28';
								}
								else
								{
								$salesTimeValue = $salesTimeArray[1].'-'.$salesTimeArray[0].'-30';
								}
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);//exit;
									
									//$date1 = date("Y-m-d",strtotime($doj));
									$daysInterval = abs(strtotime($salesTimeValue)-strtotime($doj))/ (60 * 60 * 24);
									$agentPUpdate = EnbdMisCardsTabDatacut::find($vintangeDataCut->id);
									$agentPUpdate->vintage = $daysInterval;
									$agentPUpdate->doj = $doj;
									$agentPUpdate->vintange_status = 2;
									$agentPUpdate->save();
									echo "yes";
									exit;
									
								}
								else
								{
									$agentPUpdate = EnbdMisCardsTabDatacut::find($vintangeDataCut->id);
									
									$agentPUpdate->vintange_status = 3;
									$agentPUpdate->save();
									echo "not";
									exit;
								}
							}
								else
								{
									$agentPUpdate = EnbdMisCardsTabDatacut::find($vintangeDataCut->id);
									
									$agentPUpdate->vintange_status = 3;
									$agentPUpdate->save();
									echo "not";
									exit;
								}	
							
						}
						else
						{
							$agentPUpdate = EnbdMisCardsTabDatacut::find($vintangeDataCut->id);
									
									$agentPUpdate->vintange_status = 3;
									$agentPUpdate->save();
									echo "not";
									exit;
						}
						
				}
				else
				{
					echo "All Done";
					exit;
				}
			}
			
			
			
			public function generateFinalMISTab(Request $request)
			{
			
				$interMIS = MainMisReport::where("datacut_match_status",1)->where("file_source","Tab")->orderBy("id","DESC")->first();
				if($interMIS != '')
				{
					$appId = trim($interMIS->application_id);
					$existingCheck = enbdFinalCompleteMISDatacutTab::where("application_id",$appId)->where("match_datacut",1)->orderBy("id","DESC")->first();
					
						
						if($existingCheck != '')
						{
							$addRecords = enbdFinalCompleteMISDatacutTab::find($existingCheck->id);
						}
						else
						{
							$addRecords = new enbdFinalCompleteMISDatacutTab();
						}
						
						$addRecords->date_of_submission = $interMIS->date_of_submission;
						$addRecords->application_type = $interMIS->application_type;
						$addRecords->lead_source = $interMIS->lead_source;
						$addRecords->PRODUCT = $interMIS->PRODUCT;
						$addRecords->application_id = $interMIS->application_id;
						$addRecords->current_activity = $interMIS->current_activity;
						$addRecords->approved_notapproved = $interMIS->approved_notapproved;
						$addRecords->monthly_ends = $interMIS->monthly_ends;
						$addRecords->last_remarks_added = $interMIS->last_remarks_added;
						$addRecords->cm_name = $interMIS->cm_name;
						$addRecords->fv_company_name = $interMIS->fv_company_name;
						$addRecords->company_name_as_per_visa = $interMIS->company_name_as_per_visa;
						$addRecords->ALE_NALE = $interMIS->ALE_NALE;
						$addRecords->CV_MOBILE_NUMBER = $interMIS->CV_MOBILE_NUMBER;
						$addRecords->EV_DIRECT_OFFICE_NO = $interMIS->EV_DIRECT_OFFICE_NO;
						$addRecords->E_MAILADDRESS = $interMIS->E_MAILADDRESS;
						$addRecords->SALARY = $interMIS->SALARY;
						$addRecords->LOS = $interMIS->LOS;
						$addRecords->ACCOUNT_STATUS = $interMIS->ACCOUNT_STATUS;
						$addRecords->ACCOUNT_NO = $interMIS->ACCOUNT_NO;
						$addRecords->SALARIED = $interMIS->SALARIED;
						$addRecords->TL = $interMIS->TL;
						$addRecords->SE_CODE_NAME = $interMIS->SE_CODE_NAME;
						$addRecords->REFERENCE_NAME = $interMIS->REFERENCE_NAME;
						$addRecords->REFERENCE_MOBILE_NO = $interMIS->REFERENCE_MOBILE_NO;
						$addRecords->NATIONALITY = $interMIS->NATIONALITY;
						$addRecords->PASSPORT_NO = $interMIS->PASSPORT_NO;
						$addRecords->DOB = $interMIS->DOB;
						$addRecords->VISA_Expiry_DATE = $interMIS->VISA_Expiry_DATE;
						$addRecords->DESIGNATION = $interMIS->DESIGNATION;
						$addRecords->MMN = $interMIS->MMN;
						$addRecords->EIDA = $interMIS->EIDA;
						$addRecords->IBAN = $interMIS->IBAN;
						$addRecords->EV = $interMIS->EV;
						$addRecords->Type_of_Income_Proof = $interMIS->Type_of_Income_Proof;
						$addRecords->file_source = $interMIS->file_source;
						$addRecords->other_bank = $interMIS->other_bank;
						$addRecords->employee_id = $interMIS->employee_id;
						$addRecords->Employee_status = $interMIS->Employee_status;
						$addRecords->created_by = $interMIS->created_by;
						$addRecords->submission_format = $interMIS->submission_format;
						$addRecords->Offer = $interMIS->Offer;
						$addRecords->Scheme = $interMIS->Scheme;
						$addRecords->ev_status = $interMIS->ev_status;
						$addRecords->last_updated = $interMIS->last_updated;
						$addRecords->last_updated_date = $interMIS->last_updated_date;
						$addRecords->approve_limit = $interMIS->approve_limit;
						$addRecords->match_status = $interMIS->match_status;
						$addRecords->Card_Name = $interMIS->Card_Name;
						$addRecords->application_mode = $interMIS->application_mode;
						$addRecords->STP_NSTP_flag = $interMIS->STP_NSTP_flag;
						$addRecords->customer_type = $interMIS->customer_type;
						$addRecords->DMS_Outcome = $interMIS->DMS_Outcome;
						$addRecords->DMS_Status_Description = $interMIS->DMS_Status_Description;
						$addRecords->current_activity_tab = $interMIS->current_activity_tab;
						$addRecords->internal_updated_jonus = $interMIS->internal_updated_jonus;
						$addRecords->submission_location = $interMIS->submission_location;
						$addRecords->match_datacut = 1;
						
						
						$addRecords->mis_id = $interMIS->id;
						
						
						if($addRecords->save())
							{
							
							
							$updateDataCutMainMIS = MainMisReport::find($interMIS->id);
							$updateDataCutMainMIS->datacut_match_status = 4;
							$updateDataCutMainMIS->save();
								echo "updated";
								exit;
							}
						else
							{
								
								
								$updateDataCutMainMIS = MainMisReport::find($interMIS->id);
								$updateDataCutMainMIS->datacut_match_status = 5;
								$updateDataCutMainMIS->save();
								echo "Issue in Updated";
								exit;
							}
					
				}
				else
				{
					echo "data Done";
					exit;
				}
			}
			
			
			public function dataCutVintangeFinalTab(Request $request)
			{
				$vintangeDataCut = enbdFinalCompleteMISDatacutTab::where("vintange_status",1)->where('match_datacut',1)->where("Employee_status","Verified")->first();
				if($vintangeDataCut != '')
				{
					
				
				$employeeData = Employee_details::where("id",$vintangeDataCut->employee_id)->first();
						if($employeeData != '')
						{
							$empId = $employeeData->emp_id;
							$deptId = $employeeData->dept_id;
							$empAttr = Employee_attribute::where("emp_id",$empId)->where("dept_id",$deptId)->where("attribute_code","DOJ")->first();
							if($empAttr != '')
							{
								$salesTimeValue = $vintangeDataCut->submission_format;
								
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);//exit;
									
									//$date1 = date("Y-m-d",strtotime($doj));
									$daysInterval = abs(strtotime($salesTimeValue)-strtotime($doj))/ (60 * 60 * 24);
									$agentPUpdate = enbdFinalCompleteMISDatacutTab::find($vintangeDataCut->id);
									$agentPUpdate->vintage = $daysInterval;
									$agentPUpdate->doj = $doj;
									$agentPUpdate->vintange_status = 2;
									$agentPUpdate->save();
									echo "yes";
									exit;
									
								}
								else
								{
									$agentPUpdate = enbdFinalCompleteMISDatacutTab::find($vintangeDataCut->id);
									
									$agentPUpdate->vintange_status = 3;
									$agentPUpdate->save();
									echo "not";
									exit;
								}
							}
								else
								{
									$agentPUpdate = enbdFinalCompleteMISDatacutTab::find($vintangeDataCut->id);
									
									$agentPUpdate->vintange_status = 3;
									$agentPUpdate->save();
									echo "not";
									exit;
								}	
							
						}
						else
						{
							$agentPUpdate = enbdFinalCompleteMISDatacutTab::find($vintangeDataCut->id);
									
									$agentPUpdate->vintange_status = 3;
									$agentPUpdate->save();
									echo "not";
									exit;
						}
						
				}
				else
				{
					echo "All Done";
					exit;
				}
			}
			
	public function completePerformance(Request $request)
	{
		
		$enbdAgentList = AgentPayout::where("performance_status",1)->where("agent_product","CARD")->first();
		//$enbdAgentList = AgentPayout::where("performance_status",1)->where("agent_product","CARD")->where("agent_bank_code","A7V8")->first();
		if($enbdAgentList != '')
		{
			$bankCode = $enbdAgentList->agent_bank_code;
			$getDetails = AgentPayout::where("agent_bank_code",$bankCode)->where("performance_status",1)->orderBy("vintage","ASC")->first();
			/* 
			*get Current Positions
			*/
			$existDetails = EnbdRMCompletePerformance::where("Agent_Code",$bankCode)->first();
			$Sales_Time_Count = 1;
			if($existDetails != '')
			{
				$existDetailsList = EnbdRMCompletePerformance::where("Agent_Code",$bankCode)->get();
				$existSalesCount = $existDetailsList->count();
				$Sales_Time_Count = $existSalesCount+1;
			}
			
			/* 
			*get Current Positions
			*/
			/*
			*inserting Data
			*/
			$rmProfile = new EnbdRMCompletePerformance();
			$rmProfile->Agent_Name = $getDetails->agent_name;
			$rmProfile->Agent_Code = $bankCode;
			$rmProfile->Agent_Product = $getDetails->agent_product;
			$rmProfile->Sales_Time = $getDetails->sales_time;
			$rmProfile->range_id = $getDetails->range_id;
			$rmProfile->Sales_Time_Count = $Sales_Time_Count;
			$rmProfile->Mass =$getDetails->mass;
			$rmProfile->Premium =$getDetails->premium;
			$rmProfile->SP =$getDetails->super_premium;
			$rmProfile->cards_count = $getDetails->tc_card;
			$rmProfile->Loan_Amt = $getDetails->final_loan_amount;
			
			$rmProfile->Converted_performance = $getDetails->tc_final;
			
			if($Sales_Time_Count == 1)
			{
				$rmProfile->Runining_Converted_performance = $getDetails->tc_final;
				$runingCPerformance = $getDetails->tc_final;
			}
            else
			{
				$running_cP = 0;
				foreach($existDetailsList as $exist)
				{
					$running_cP =$running_cP+$exist->Converted_performance;
				}
				$rmProfile->Runining_Converted_performance = $running_cP+$getDetails->tc_final;
				$runingCPerformance = $running_cP+$getDetails->tc_final;
			}
			
			/*
			*Cumulative Card coding
			*Start Code
			*/
			if($Sales_Time_Count == 1)
			{
				$rmProfile->Cumulative_Card = $getDetails->tc_card;
				$Cumulative_Card = $getDetails->tc_card;
			}
            else
			{
				$running_cc = 0;
				foreach($existDetailsList as $exist)
				{
					$running_cc =$running_cc+$exist->cards_count;
				}
				$rmProfile->Cumulative_Card = $running_cc+$getDetails->tc_card;
				$Cumulative_Card = $running_cc+$getDetails->tc_card;
			}
			
			/*
			*Cumulative Card coding
			*End Code
			*/
			/**
			*target COding
			*/
			$rmProfile->target = $getDetails->agent_target;
			$rmProfile->target_achieved = $getDetails->tc_card;
			if(($getDetails->agent_target != 0 && $getDetails->agent_target != NULL) && ($getDetails->tc_card != 0 && $getDetails->tc_card != NULL))
			{
				$rmProfile->target_achieved_percentage = round(($getDetails->tc_card/$getDetails->agent_target),2);
			}
			else
			{
				$rmProfile->target_achieved_percentage = 0;
			}
			
			
		
			if($Sales_Time_Count == 1)
			{
				$rmProfile->target_r = $getDetails->agent_target;
				$rmProfile->target_achieved_r = $getDetails->tc_card;
				if(($getDetails->agent_target != 0 && $getDetails->agent_target != NULL) && ($getDetails->tc_card != 0 && $getDetails->tc_card != NULL))
				{
					$rmProfile->target_achieved_percentage_r = round(($getDetails->tc_card/$getDetails->agent_target),2);
				}
				else
				{
					$rmProfile->target_achieved_percentage_r = 0;
				}
				
			}
            else
			{
				$runingtarget = 0;
				$runingtargetA = 0;
				foreach($existDetailsList as $exist)
				{
					$runingtarget =$runingtarget+$exist->target;
					$runingtargetA =$runingtargetA+$exist->target_achieved;
				}
				$rmProfile->target_r = $runingtarget+$getDetails->agent_target;
				$targetRF = $runingtarget+$getDetails->agent_target;
				$rmProfile->target_achieved_r = $runingtargetA+$getDetails->tc_card;
				$targetRFA = $runingtargetA+$getDetails->tc_card;
				if($targetRF != 0 && $targetRFA != 0)
				{
					$rmProfile->target_achieved_percentage_r = round(($targetRF/$targetRFA),2);
				}
				else
				{
					$rmProfile->target_achieved_percentage_r = 0;
				}
			}
			/**
			*target Coding
			*/
			/*
			*productivity and runining
			*start code
			*/
			$rmProfile->Productivity = $getDetails->tc_card;
			$rmProfile->Running_productivity = round($Cumulative_Card/$Sales_Time_Count,2);
			
			
			/*
			*productivity and runining
			*start code
			*/
			$rmProfile->Revenue = $getDetails->total_revenue;
			$runingRevenue = 0;
			if($Sales_Time_Count == 1)
			{
				$rmProfile->Running_Revenue = $getDetails->total_revenue;
				$runingRevenue = $getDetails->total_revenue;
			}
            else
			{
				$running_rev = 0;
				foreach($existDetailsList as $exist)
				{
					$running_rev =$running_rev+$exist->Revenue;
				}
				$rmProfile->Running_Revenue = $running_rev+$getDetails->total_revenue;
				$runingRevenue = $running_rev+$getDetails->total_revenue;
			}
			
			
			$rmProfile->Salary = $getDetails->total_salary;	
			
			$runingSalary = 0;
			if($Sales_Time_Count == 1)
			{			
			$rmProfile->running_salary = $getDetails->total_salary;		
			$runingSalary = $getDetails->total_salary;		
			}
			else
			{
				$salary = 0;
				foreach($existDetailsList as $exist)
				{
					$salary =$salary+$exist->Salary;
				}
				$rmProfile->running_salary = $salary+$getDetails->total_salary;
				$runingSalary = $salary+$getDetails->total_salary;	
			}
			if($getDetails->tc_card != 0)
			{
			$rmProfile->Revenue_Card = round($getDetails->total_revenue/$getDetails->tc_card,2);
			}
			else
			{
				$rmProfile->Revenue_Card = 0;
			}
			if($runingRevenue != 0 && $Cumulative_Card!=0)
			{
			$rmProfile->Running_Rev_Card = round($runingRevenue/$Cumulative_Card,2);
			}
			else
			{
				$existDetails1 = EnbdRMCompletePerformance::where("Agent_Code",$bankCode)->orderBy("id","DESC")->first();
				if($existDetails1 != '')
				{
					$rmProfile->Running_Rev_Card =$existDetails1->Running_Rev_Card;
				}
				else
					{
						$rmProfile->Running_Rev_Card = 0;
					}
			}
			
			/*
			*Distribution_Cost
			*/
			if($getDetails->tc_final != 0)
			{
			$Distribution_Cost = $getDetails->total_salary/$getDetails->tc_final;
			$rmProfile->Distribution_Cost = round($Distribution_Cost,2);
			}
			else
			{
				$rmProfile->Distribution_Cost = 0;
			}
			
			if($runingCPerformance != 0)
			{
			$Running_Distribution_Cost = $runingSalary/$runingCPerformance;
			$rmProfile->Running_Distribution_Cost = round($Running_Distribution_Cost,2);
			}
			else
			{
				$rmProfile->Running_Distribution_Cost = 0;
			}
			/*
			*Distribution_Cost
			*/
			
			/*
			*Excess
			*/
			$rmProfile->excess = $getDetails->excess;
			
			
			if($Sales_Time_Count == 1)
					{
						$rmProfile->Running_Excess = $getDetails->excess;
					}
					else
					{
						$excess = 0;
						foreach($existDetailsList as $exist)
						{
							$excess =$excess+$exist->excess;
						}
						$rmProfile->Running_Excess = $excess+$getDetails->excess;
					}	
			/*
			*Excess
			*/
			
			/*
			*Mass,P and SP logic
			*/
			if($getDetails->tc_card != 0 && $getDetails->mass != 0)
			{
				$rmProfile->Mass_percentage = round($getDetails->mass/$getDetails->tc_card,2);
			}
			else
			{
				$rmProfile->Mass_percentage = 0;
			}
			
			if($getDetails->tc_card != 0 && $getDetails->premium != 0)
			{
				$rmProfile->Premium_percentage = round($getDetails->premium/$getDetails->tc_card,2);
			}
			else
			{
				$rmProfile->Premium_percentage = 0;
			}
			
			if($getDetails->tc_card != 0 && $getDetails->super_premium != 0)
			{
				$rmProfile->SP_percentage = round($getDetails->super_premium/$getDetails->tc_card,2);
			}
			else
			{
				$rmProfile->SP_percentage = 0;
			}
			
			
			
			
					if($Sales_Time_Count == 1)
					{
						if($getDetails->mass != 0 && $getDetails->tc_card !=0)
						{
						$rmProfile->Mass_percentage_r = round($getDetails->mass/$getDetails->tc_card,2);
						}
						else
						{
							$rmProfile->Mass_percentage_r = 0;
						}
					}
					else
					{
						$massPR = 0;
						foreach($existDetailsList as $exist)
						{
							$massPR =$massPR+$exist->Mass;
						}
					  if($Cumulative_Card != 0 && $getDetails->mass != 0)
						{
							$completeMass = $massPR+$getDetails->mass;
						$rmProfile->Mass_percentage_r = round($completeMass/$Cumulative_Card,2);
						}
						else
						{
							if($Cumulative_Card != 0)
							{
							$rmProfile->Mass_percentage_r = round($massPR/$Cumulative_Card,2);
							}
							else
							{
									$rmProfile->Mass_percentage_r = 0;
							}
						}
					}
					
					
					if($Sales_Time_Count == 1)
					{
						if($getDetails->premium != 0 && $getDetails->tc_card !=0)
						{
						$rmProfile->Premium_percentange_r = round($getDetails->premium/$getDetails->tc_card,2);
						}
						else
						{
							$rmProfile->Premium_percentange_r =0;
						}
					}
					else
					{
						$premiumPR = 0;
						foreach($existDetailsList as $exist)
						{
							$premiumPR =$premiumPR+$exist->Premium;
						}
						if($getDetails->premium != 0 && $Cumulative_Card != 0)
						{
							$completePremium = $premiumPR+$getDetails->premium;
							$rmProfile->Premium_percentange_r = round($completePremium/$Cumulative_Card,2);
						}
						else
						{
							if($Cumulative_Card != 0)
							{
							$rmProfile->Premium_percentange_r = round($premiumPR/$Cumulative_Card,2);
							}
							else
							{
								$rmProfile->Premium_percentange_r = 0;	
							}
						}
					}
					
					
					if($Sales_Time_Count == 1)
					{
						if($getDetails->super_premium != 0 && $getDetails->tc_card !=0)
						{
						$rmProfile->SP_percentage_r = round($getDetails->super_premium/$getDetails->tc_card,2);
						}
						else
						{
							$rmProfile->SP_percentage_r = 0;
						}
					}
					else
					{
						$massPR1 = 0;
						foreach($existDetailsList as $exist)
						{
							$massPR1 =$massPR1+$exist->SP;
						}
						if($getDetails->super_premium !=0 && $Cumulative_Card != 0)
						{
							$completeSP = $massPR1+$getDetails->super_premium;
						$rmProfile->SP_percentage_r = round($completeSP/$Cumulative_Card,2);
						}
						else
						{
							if($Cumulative_Card != 0)
							{
							$rmProfile->SP_percentage_r = round($massPR1/$Cumulative_Card,2);
							}
							else
							{
								$rmProfile->SP_percentage_r = 0;
							}
						}
					}
			/*
			*Mass,P and SP logic
			*/
			$rmProfile->doj = $getDetails->doj;
			$rmProfile->vintage = $getDetails->vintage;
			$rmProfile->match_employee = $getDetails->match_employee;
			
			
			
			/* $rmProfile->doj = $getDetails->doj;
			$rmProfile->vintage = $getDetails->vintage;
			$runingPerformance = 0;
			if($Sales_Time_Count == 1)
			{
				$rmProfile->running_p = $getDetails->tc_final;
				$runingPerformance = $getDetails->tc_final;
			}
            else
			{
				$running_p = 0;
				foreach($existDetailsList as $exist)
				{
					$running_p =$running_p+$exist->Performance;
				}
				$rmProfile->running_p = $running_p+$getDetails->tc_final;
				$runingPerformance = $running_p+$getDetails->tc_final;
			}
			 */
			
			
			
			
			
			/*
			*Month_Productivity Without Conversion
			*/
			/* 	$rmProfile->Month_Productivity = $getDetails->tc_card;
				
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Running_Productivity = $getDetails->tc_card;
					}
					else
					{
						$Productivity = 0;
						foreach($existDetailsList as $exist)
						{
							$Productivity =$Productivity+$exist->Month_Productivity;
						}
						$rmProfile->Running_Productivity = $Productivity+$getDetails->tc_card;
					}	
			 */	
				
				
			/*
			*Month_Productivity Without Conversion
			*/
			
			
			/*
			*Month_Productivity With Conversion
			*/
			/* 	$rmProfile->Month_Productivity_C = $getDetails->tc_final;
				
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Running_Productivity_C = $getDetails->tc_final;
					}
					else
					{
						$Productivity = 0;
						foreach($existDetailsList as $exist)
						{
							$Productivity =$Productivity+$exist->Month_Productivity_C;
						}
						$rmProfile->Running_Productivity_C = $Productivity+$getDetails->tc_final;
					}	
			 */	
				
				
			/*
			*Month_Productivity With Conversion
			*/
			
				
			
			
			/* submission_count count*/
			if($getDetails->match_employee == 2)
			{
			 	$employeeData = Employee_details::where("source_code",$bankCode)->first();
				if($employeeData!= '')
				{
				$employeeDataID = $employeeData->id;
				$salesTimeArray = explode("-",$getDetails->sales_time);
				$monthP = sprintf("%02d", $salesTimeArray[0]);
				$salesTimeNew  = $monthP.'-'.$salesTimeArray[1];
				$submissionDetails = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->get();
				$submissionDetailsENDCards = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("match_datacut",2)->get();
				$rmProfile->submission_count = $submissionDetails->count();
				/*
				*get End Submission
				*/
				$submissionDetailsEnd = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("match_datacut",2)->get()->count();
				/*
				*get End Submission
				*/
				if($submissionDetails->count() != 0)
				{
				$Approval_Rate = $submissionDetailsEnd/$submissionDetails->count();
				$rmProfile->Approval_Rate = round($Approval_Rate,2);
				}
				else
				{
					
				$rmProfile->Approval_Rate = 0;
				}
				$col5 = 0;
				$col5to10 = 0;
				$col10to15 = 0;
				$col15to25 = 0;
				$col25 = 0;
				foreach($submissionDetails as $_detailsS)
				{
					if( $_detailsS->SALARY < 5000)
					{
						$col5++;
					}
					elseif($_detailsS->SALARY >= 5000 && $_detailsS->SALARY < 10000)
					{
						$col5to10++;
					}
					elseif($_detailsS->SALARY >= 10000 && $_detailsS->SALARY < 15000)
					{
						$col10to15++;
					}
					elseif($_detailsS->SALARY >= 15000 && $_detailsS->SALARY < 25000)
					{
						$col15to25++;
					}
					elseif($_detailsS->SALARY >= 25000)
					{
						$col25++;
					}
					
				}
				$rmProfile->Sal_less_5k = $col5;
				$rmProfile->Sal_5_10k = $col5to10;
				$rmProfile->Sal_10k_15k = $col10to15;
				$rmProfile->Sal_15k_25k = $col15to25;
				$rmProfile->Sal_greater_25k = $col25;
				
				
				
				$col5end = 0;
				$col5to10end = 0;
				$col10to15end = 0;
				$col15to25end = 0;
				$col25end = 0;
				foreach($submissionDetailsENDCards as $_detailsS)
				{
					if( $_detailsS->SALARY < 5000)
					{
						$col5end++;
					}
					elseif($_detailsS->SALARY >= 5000 && $_detailsS->SALARY < 10000)
					{
						$col5to10end++;
					}
					elseif($_detailsS->SALARY >= 10000 && $_detailsS->SALARY < 15000)
					{
						$col10to15end++;
					}
					elseif($_detailsS->SALARY >= 15000 && $_detailsS->SALARY < 25000)
					{
						$col15to25end++;
					}
					elseif($_detailsS->SALARY >= 25000)
					{
						$col25end++;
					}
					
				}
				$rmProfile->Sal_less_5k_end = $col5end;
				$rmProfile->Sal_5_10k_end = $col5to10end;
				$rmProfile->Sal_10k_15k_end = $col10to15end;
				$rmProfile->Sal_15k_25k_end = $col15to25end;
				$rmProfile->Sal_greater_25k_end = $col25end;
				
				if($col5end !=0 && $submissionDetailsEnd != 0)
				{
				$rmProfile->Sal_less_5k_approval = round($col5end/$col5,2);
				}
				else
				{
					$rmProfile->Sal_less_5k_approval = 0;
				}
				
				if($col5to10end !=0 && $submissionDetailsEnd != 0)
				{
				$rmProfile->Sal_5_10k_approval = round($col5to10end/$col5to10,2);
				}
				else
				{
					$rmProfile->Sal_5_10k_approval  =0;
				}
				
				if($col10to15end !=0 && $submissionDetailsEnd != 0)
				{
					$rmProfile->Sal_10k_15k_approval = round($col10to15end/$col10to15,2);
				}
				else
				{
					$rmProfile->Sal_10k_15k_approval =0;
				}
				
				if($col15to25end !=0 && $submissionDetailsEnd != 0)
				{
					$rmProfile->Sal_15k_25k_approval = round($col15to25end/$col15to25,2);
				}
				else 
				{
					$rmProfile->Sal_15k_25k_approval =0;
				}
				
				if($col25end !=0 && $submissionDetailsEnd != 0)
				{
					$rmProfile->Sal_greater_25k_approval = round($col25end/$col25,2);
				}
				else
				{
					$rmProfile->Sal_greater_25k_approval =0;
				}
				
				/*
				*Card Per Appoval Rate
				*
				*/
				
				$massSubmissionCountEND = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("match_datacut",2)->where("PRODUCT","Mass")->get()->count();
				$pSubmissionCountEND = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("match_datacut",2)->where("PRODUCT","Premium")->get()->count();
				$SpSubmissionCountEND = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("match_datacut",2)->where("PRODUCT","Super Premium")->get()->count();
				
				
				$massSubmissionCount = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("PRODUCT","Mass")->get()->count();
				$pSubmissionCount = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("PRODUCT","Premium")->get()->count();
				$SpSubmissionCount = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("PRODUCT","Super Premium")->get()->count();
				
				if($massSubmissionCountEND != 0 && $massSubmissionCount != 0)
				{
					$massSubmissionApproval = round($massSubmissionCountEND/$massSubmissionCount,2);
				}
				else
				{
					$massSubmissionApproval = 0;
				}
				
				if($pSubmissionCountEND != 0 && $pSubmissionCount != 0)
				{
				
				$pSubmissionApproval = round($pSubmissionCountEND/$pSubmissionCount,2);
				
				}
				else
				{
					$pSubmissionApproval = 0;
				}
				
				if($SpSubmissionCountEND != 0 && $SpSubmissionCount != 0)
				{
				$SpSubmissionApproval = round($SpSubmissionCountEND/$SpSubmissionCount,2);
				}
				else
				{
					$SpSubmissionApproval =0;
				}
				
				
				$rmProfile->Mass_submission = $massSubmissionCount;
				$rmProfile->Mass_submission_end = $massSubmissionCountEND;
				$rmProfile->Mass_approval_rate = $massSubmissionApproval;
				
				
				
				$rmProfile->Premium_submission = $pSubmissionCount;
				$rmProfile->Premium_submission_end = $pSubmissionCountEND;
				$rmProfile->Premium_approval_rate = $pSubmissionApproval;
				
				
				
				$rmProfile->SP_submission = $SpSubmissionCount;
				$rmProfile->SP_submission_end = $SpSubmissionCountEND;
				$rmProfile->SP_approval_rate = $SpSubmissionApproval;
				$rmProfile->submission_end_count = $submissionDetailsEnd;
				/*
				*Card Per Appoval Rate
				*
				*/
				$aleSubmissionCount = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("ALE_NALE","Ale")->get()->count();
				
				$naleSubmissionCount = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("ALE_NALE","Nale")->get()->count();
				
				
				$aleSubmissionCountEnd = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("ALE_NALE","Ale")->where("match_datacut",2)->get()->count();
				
				$naleSubmissionCountEnd = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$salesTimeNew)->where("ALE_NALE","Nale")->where("match_datacut",2)->get()->count();
				
				
				$aleApprovalRate = 0;
				if($aleSubmissionCount != 0 && $aleSubmissionCountEnd != 0)
				{
					$aleApprovalRate =  round($aleSubmissionCountEnd/$aleSubmissionCount,2);
				}
				
				
				$naleApprovalRate = 0;
				if($naleSubmissionCount != 0 && $naleSubmissionCountEnd != 0)
				{
					$naleApprovalRate =  round($naleSubmissionCountEnd/$naleSubmissionCount,2);
				}
				
				$rmProfile->ale = $aleSubmissionCount;
				$rmProfile->nale = $naleSubmissionCount;
				$rmProfile->ale_end = $aleSubmissionCountEnd;
				$rmProfile->nale_end = $naleSubmissionCountEnd;
				$rmProfile->ale_approval_rate = $aleApprovalRate;
				$rmProfile->nale_approval_rate = $naleApprovalRate;
				/*
				*Ale And Nale Coding
				*/
				
				/*
				*Ale And Nale Coding
				*/
				//$salary1 = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$getDetails->sales_time)->orderBy('SALARY','ASC')->first();
				//$salary2 = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$getDetails->sales_time)->orderBy('SALARY','DESC')->first();
				//if($salary1 != '' && $salary2 != '')
				//{
				//$rmProfile->salary_cus = $salary1->SALARY.'-'.$salary2->SALARY;
				//}
				
				
				/*
				*runing Ale
				*/
				$aleRuning =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->ale_r =  $aleSubmissionCount;
						$aleRuning = $aleSubmissionCount;
						
					}
					else
					{
						$aler = 0;
						foreach($existDetailsList as $exist)
						{
							$aler =$aler+$exist->ale;
						}
						$rmProfile->ale_r =$aler+$aleSubmissionCount;
						$aleRuning = $aler+$aleSubmissionCount;
					}
				
				/*
				*runing Ale
				*/
				/*
				*runing Nale
				*/
				$naleRuning =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->nale_r =  $naleSubmissionCount;
						$naleRuning = $naleSubmissionCount;
						
					}
					else
					{
						$naler = 0;
						foreach($existDetailsList as $exist)
						{
							$naler =$naler+$exist->nale;
						}
						$rmProfile->nale_r =$naler+$naleSubmissionCount;
						$naleRuning = $naler+$naleSubmissionCount;
					}
				
				/*
				*runing Nale
				*/
				
				/*
				*runing Ale End
				*/
				$aleEndRuning =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->ale_end_r =  $aleSubmissionCountEnd;
						$aleEndRuning = $aleSubmissionCountEnd;
						
					}
					else
					{
						$aleEndr = 0;
						foreach($existDetailsList as $exist)
						{
							$aleEndr =$aleEndr+$exist->ale_end;
						}
						$rmProfile->ale_end_r =$aleEndr+$aleSubmissionCountEnd;
						$aleEndRuning = $aleEndr+$aleSubmissionCountEnd;
					}
				
				/*
				*runing Ale End
				*/
				
				
				
				/*
				*runing Nale End
				*/
				$naleEndRuning =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->nale_end_r =  $naleSubmissionCountEnd;
						$naleEndRuning = $naleSubmissionCountEnd;
						
					}
					else
					{
						$naleEndr = 0;
						foreach($existDetailsList as $exist)
						{
							$naleEndr =$naleEndr+$exist->nale_end;
						}
						$rmProfile->nale_end_r =$naleEndr+$naleSubmissionCountEnd;
						$naleEndRuning = $naleEndr+$naleSubmissionCountEnd;
					}
				
				/*
				*runing Nale End
				*/
				
				/*
				*runing Ale approval rate
				*/
				if($aleRuning != 0 && $aleEndRuning != 0)
				{
					$rmProfile->ale_approval_rate_r = round($aleEndRuning/$aleRuning,2);
				}
				else
				{
					$rmProfile->ale_approval_rate_r = 0;
				}
				
				/*
				*runing Ale approval rate
				*/
				
				
				/*
				*runing Nale approval rate
				*/
				if($naleRuning != 0 && $naleEndRuning != 0)
				{
					$rmProfile->nale_approval_rate_r = round($naleEndRuning/$naleRuning,2);
				}
				else
				{
					$rmProfile->nale_approval_rate_r = 0;
				}
				
				/*
				*runing Nale approval rate
				*/
				
				
				/*
				*runing salary 
				*submission
				*/
				
				$col5R =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_less_5k_r =  $col5;
						$col5R = $col5;
						
					}
					else
					{
						$col5V = 0;
						foreach($existDetailsList as $exist)
						{
							$col5V =$col5V+$exist->Sal_less_5k;
						}
						$rmProfile->Sal_less_5k_r =$col5V+$col5;
						$col5R = $col5V+$col5;
					}
				
				/*
				*
				*/
				
				$col5to10R =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_5_10k_r =  $col5to10;
						$col5to10R = $col5to10;
						
					}
					else
					{
						$col5to10V = 0;
						foreach($existDetailsList as $exist)
						{
							$col5to10V =$col5to10V+$exist->Sal_5_10k;
						}
						$rmProfile->Sal_5_10k_r =$col5to10V+$col5to10;
						$col5to10R = $col5to10V+$col5to10;
					}
					
				/*
				*
				*/
				$col10to15R =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_10k_15k_r =  $col10to15;
						$col10to15R = $col10to15;
						
					}
					else
					{
						$col10to15V = 0;
						foreach($existDetailsList as $exist)
						{
							$col10to15V =$col10to15V+$exist->Sal_10k_15k;
						}
						$rmProfile->Sal_10k_15k_r =$col10to15V+$col10to15;
						$col10to15R = $col10to15V+$col10to15;
					}
					
				/*
				*
				*/	
					
				$col15to25R =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_15k_25k_r =  $col15to25;
						$col15to25R = $col15to25;
						
					}
					else
					{
						$col15to25V = 0;
						foreach($existDetailsList as $exist)
						{
							$col15to25V =$col15to25V+$exist->Sal_15k_25k;
						}
						$rmProfile->Sal_15k_25k_r =$col15to25V+$col15to25;
						$col15to25R = $col15to25V+$col15to25;
					}	
				/*
				*
				*/	
					
				$col25R =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_greater_25k_r =  $col25;
						$col25R = $col25;
						
					}
					else
					{
						$col25V = 0;
						foreach($existDetailsList as $exist)
						{
							$col25V =$col25V+$exist->Sal_greater_25k;
						}
						$rmProfile->Sal_greater_25k_r =$col25V+$col25;
						$col25R = $col25V+$col25;
					}	
				/*
				*runing salary Submission
				*/
				
				
				
				
				/*
				*runing salary 
				*End
				*/
				
				$col5Rend =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_less_5k_end_r =  $col5end;
						$col5Rend = $col5end;
						
					}
					else
					{
						$col5Vend = 0;
						foreach($existDetailsList as $exist)
						{
							$col5Vend =$col5Vend+$exist->Sal_less_5k_end;
						}
						$rmProfile->Sal_less_5k_end_r =$col5Vend+$col5end;
						$col5Rend = $col5Vend+$col5end;
					}
				
				/*
				*
				*/
				
				$col5to10Rend =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_5_10k_end_r =  $col5to10end;
						$col5to10Rend = $col5to10end;
						
					}
					else
					{
						$col5to10Vend = 0;
						foreach($existDetailsList as $exist)
						{
							$col5to10Vend =$col5to10Vend+$exist->Sal_5_10k_end;
						}
						$rmProfile->Sal_5_10k_end_r =$col5to10Vend+$col5to10end;
						$col5to10Rend = $col5to10Vend+$col5to10end;
					}
					
				/*
				*
				*/
				$col10to15Rend =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_10k_15k_end_r =  $col10to15end;
						$col10to15Rend = $col10to15end;
						
					}
					else
					{
						$col10to15Vend = 0;
						foreach($existDetailsList as $exist)
						{
							$col10to15Vend =$col10to15Vend+$exist->Sal_10k_15k_end;
						}
						$rmProfile->Sal_10k_15k_end_r =$col10to15Vend+$col10to15end;
						$col10to15Rend = $col10to15Vend+$col10to15end;
					}
					
				/*
				*
				*/	
					
				$col15to25Rend =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_15k_25k_end_r =  $col15to25end;
						$col15to25Rend = $col15to25end;
						
					}
					else
					{
						$col15to25Vend = 0;
						foreach($existDetailsList as $exist)
						{
							$col15to25Vend =$col15to25Vend+$exist->Sal_15k_25k_end;
						}
						$rmProfile->Sal_15k_25k_end_r =$col15to25Vend+$col15to25end;
						$col15to25Rend = $col15to25Vend+$col15to25end;
					}	
				/*
				*
				*/	
					
				$col25Rend =0;
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Sal_greater_25k_end_r =  $col25end;
						$col25Rend = $col25end;
						
					}
					else
					{
						$col25Vend = 0;
						foreach($existDetailsList as $exist)
						{
							$col25Vend =$col25Vend+$exist->Sal_greater_25k_end;
						}
						$rmProfile->Sal_greater_25k_end_r =$col25Vend+$col25end;
						$col25Rend = $col25Vend+$col25end;
					}	
				/*
				*runing salary less than 5K
				*/
				
				/*
				*runing salary approval 
				*/
				if($col5Rend !=0 )
				{
				$rmProfile->Sal_less_5k_approval_r = round($col5Rend/$col5R,2);
				}
				else
				{
					$rmProfile->Sal_less_5k_approval_r = 0;
				}
				
				if($col5to10Rend !=0 )
				{
				$rmProfile->Sal_5_10k_approval_r = round($col5to10Rend/$col5to10R,2);
				}
				else
				{
					$rmProfile->Sal_5_10k_approval_r  =0;
				}
				
				if($col10to15Rend !=0)
				{
					$rmProfile->Sal_10k_15k_approval_r = round($col10to15Rend/$col10to15R,2);
				}
				else
				{
					$rmProfile->Sal_10k_15k_approval_r =0;
				}
				
				if($col15to25Rend !=0)
				{
					$rmProfile->Sal_15k_25k_approval_r = round($col15to25Rend/$col15to25R,2);
				}
				else 
				{
					$rmProfile->Sal_15k_25k_approval_r =0;
				}
				
				if($col25Rend !=0 )
				{
					$rmProfile->Sal_greater_25k_approval_r = round($col25Rend/$col25R,2);
				}
				else
				{
					$rmProfile->Sal_greater_25k_approval_r =0;
				}
				/*
				*runing salary approval
				*/
				
				} 
			}
			
			/* submission_count count*/
			
			/*customer Salary*/
		
			
			
			/*customer Salary*/
			if($rmProfile->save())
			{
				
				$update = AgentPayout::find($getDetails->id);
				$update->performance_status = 2;
				$update->save();
				echo "updated";
			exit;
			}
			else
			{
				$update = AgentPayout::find($getDetails->id);
				$update->performance_status = 3;
				$update->save();
				echo "not updated";
			exit;
			}
			
			
			/*
			*inserting Data
			*/
		}
		else
		{
			echo "all DONE";
			exit;
		}
		
	}
	
	
	public function completePerformanceLoan(Request $request)
	{
		
		$enbdAgentList = AgentPayout::where("performance_status",1)->where("agent_product","!=","CARD")->first();
		if($enbdAgentList != '')
		{
			$bankCode = $enbdAgentList->agent_bank_code;
			$getDetails = AgentPayout::where("agent_bank_code",$bankCode)->where("performance_status",1)->orderBy("vintage","ASC")->first();
			/* 
			*get Current Positions
			*/
			$existDetails = EnbdRMCompletePerformanceLoan::where("Agent_Code",$bankCode)->first();
			$Sales_Time_Count = 1;
			if($existDetails != '')
			{
				$existDetailsList = EnbdRMCompletePerformanceLoan::where("Agent_Code",$bankCode)->get();
				$existSalesCount = $existDetailsList->count();
				$Sales_Time_Count = $existSalesCount+1;
			}
			
			/* 
			*get Current Positions
			*/
			/*
			*inserting Data
			*/
			$rmProfile = new EnbdRMCompletePerformanceLoan();
			$rmProfile->Agent_Name = $getDetails->agent_name;
			$rmProfile->Agent_Code = $bankCode;
			$rmProfile->Agent_Product = $getDetails->agent_product;
			$rmProfile->Sales_Time = $getDetails->sales_time;
			$rmProfile->Sales_Time_Count = $Sales_Time_Count;
			$rmProfile->range_id = $getDetails->range_id;
			$rmProfile->doj = $getDetails->doj;
			$rmProfile->vintage = $getDetails->vintage;
			$rmProfile->Loan_Amt = $getDetails->personal_loan+$getDetails->auto_loan;
			$loanAmt = $getDetails->personal_loan+$getDetails->auto_loan;
			
			$rmProfile->mass = $getDetails->mass;
			$massAmt = $getDetails->mass*50000;
			$rmProfile->premium = $getDetails->premium;
			$premiumAmt = $getDetails->premium*100000;
			$rmProfile->super_premium = $getDetails->super_premium;
			$super_premiumAmt = $getDetails->super_premium*150000;
			$rmProfile->Loan_Amt_Converted = $loanAmt+$massAmt+$premiumAmt+$super_premiumAmt;
			
			$loanAmtConverted = $loanAmt+$massAmt+$premiumAmt+$super_premiumAmt;
			
			/*
			*runing Loan Converted amt
			*/
			
			
			
			$runingloanAmtC = 0;
			if($Sales_Time_Count == 1)
			{			
			$rmProfile->Loan_Amt_Converted_runing = $loanAmtConverted;		
			$runingloanAmtC = $loanAmtConverted;		
			}
			else
			{
				$loanAmtC = 0;
				foreach($existDetailsList as $exist)
				{
					$loanAmtC =$loanAmtC+$exist->Loan_Amt_Converted;
				}
				$rmProfile->Loan_Amt_Converted_runing = $loanAmtC+$loanAmtConverted;
				$runingloanAmtC = $loanAmtC+$loanAmtConverted;
			}
			
			/*
			*runing Loan Converted amt
			*/
			
			$rmProfile->match_employee = $getDetails->match_employee;
			
			
			$rmProfile->Salary = $getDetails->total_salary;	
			
			$runingSalary = 0;
			if($Sales_Time_Count == 1)
			{			
			$rmProfile->running_salary = $getDetails->total_salary;		
			$runingSalary = $getDetails->total_salary;		
			}
			else
			{
				$salary = 0;
				foreach($existDetailsList as $exist)
				{
					$salary =$salary+$exist->Salary;
				}
				$rmProfile->running_salary = $salary+$getDetails->total_salary;
				$runingSalary = $salary+$getDetails->total_salary;	
			}
			
			
			/*
			*Distribution_Cost
			*/
			if($loanAmtConverted != 0)
			{
			$Distribution_Cost = $getDetails->total_salary/$loanAmtConverted;
			$rmProfile->Distribution_Cost = round($Distribution_Cost,2);
			}
			else
			{
				$rmProfile->Distribution_Cost = 0;
			}
			
			if($runingloanAmtC != 0)
			{
			$Running_Distribution_Cost = $runingSalary/$runingloanAmtC;
			$rmProfile->Running_Distribution_Cost = round($Running_Distribution_Cost,2);
			}
			else
			{
				$rmProfile->Running_Distribution_Cost = 0;
			}
			/*
			*Distribution_Cost
			*/
			
			/*
			*Month_Productivity Without Conversion
			*/
				$rmProfile->Month_Productivity = $loanAmt;
				
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Running_Productivity = $loanAmt;
					}
					else
					{
						$Productivity = 0;
						foreach($existDetailsList as $exist)
						{
							$Productivity =$Productivity+$exist->Month_Productivity;
						}
						$rmProfile->Running_Productivity = $Productivity+$loanAmt;
					}	
				
				
				
			/*
			*Month_Productivity Without Conversion
			*/
			
			
			/*
			*Month_Productivity With Conversion
			*/
				$rmProfile->Month_Productivity_C = $loanAmtConverted;
				
				if($Sales_Time_Count == 1)
					{
						$rmProfile->Running_Productivity_C = $loanAmtConverted;
					}
					else
					{
						$Productivity = 0;
						foreach($existDetailsList as $exist)
						{
							$Productivity =$Productivity+$exist->Month_Productivity_C;
						}
						$rmProfile->Running_Productivity_C = $Productivity+$loanAmtConverted;
					}	
				
				
				
			/*
			*Month_Productivity With Conversion
			*/
			$rmProfile->excess = $getDetails->excess;
			
			
			if($Sales_Time_Count == 1)
					{
						$rmProfile->Running_Excess = $getDetails->excess;
					}
					else
					{
						$excess = 0;
						foreach($existDetailsList as $exist)
						{
							$excess =$excess+$exist->excess;
						}
						$rmProfile->Running_Excess = $excess+$getDetails->excess;
					}	
				
			/*
			*Excess
			*/

			/*
			*Excess
			*/
			
			/* submission_count count*/
			if($getDetails->match_employee == 2)
			{
			/* 	$employeeDataID = Employee_details::where("source_code",$bankCode)->first()->id;
				$submissionDetails = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$getDetails->sales_time)->get();
				$rmProfile->submission_count = $submissionDetails->count();
				if($submissionDetails->count() != 0)
				{
				$Approval_Rate = $getDetails->tc_card/$submissionDetails->count();
				$rmProfile->Approval_Rate = round($Approval_Rate/100,2);
				}
				else
				{
					
				$rmProfile->Approval_Rate = 0;
				}
				$salary1 = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$getDetails->sales_time)->orderBy('SALARY','ASC')->first();
				$salary2 = EnbdFinalMisCompletebothCreditCards::where('employee_id',$employeeDataID)->where("submission_sales_time",$getDetails->sales_time)->orderBy('SALARY','DESC')->first();
				if($salary1 != '' && $salary2 != '')
				{
				$rmProfile->salary_cus = $salary1->SALARY.'-'.$salary2->SALARY;
				} */
			}
			
			/* submission_count count*/
			
			/*customer Salary*/
		
			
			
			/*customer Salary*/
			if($rmProfile->save())
			{
				$update = AgentPayout::find($getDetails->id);
				$update->performance_status = 2;
				$update->save();
				echo "updated";
			exit;
			}
			else
			{
				$update = AgentPayout::find($getDetails->id);
				$update->performance_status = 3;
				$update->save();
				echo "not updated";
			exit;
			}
			
			
			/*
			*inserting Data
			*/
		}
		else
		{
			echo "all DONE";
			exit;
		}
		
	}
	
	public function updateAleNaleBothCreditCards()
	{
		$enbdBothCC = EnbdFinalMisCompletebothCreditCards::where("ale_nale_update_status",1)->first();
		if($enbdBothCC != '')
		{
			$companyName = $enbdBothCC->fv_company_name;
			
			if($companyName != '')
			{
			$nameofcompanyArray = explode(" ",$companyName);
			if(count($nameofcompanyArray) >0)
			{
			$n = count($nameofcompanyArray);
			$lastElement = $n-1;
			/*
			*creating keywords for industry
			*/
			$searchCompanyName = '';
			for($i=0;$i<$lastElement;$i++)
			{
				if($i == 0)
				{
					$searchCompanyName = $nameofcompanyArray[$i];
				}
				else
				{
					$searchCompanyName = $searchCompanyName.' '.$nameofcompanyArray[$i];
				}
			}
			
			/*
			*creating keywords for industry
			*/
				//echo $searchCompanyName;exit;
				$companyExist  = CompanyListComplete::where('name_of_company', 'like', '%' .$searchCompanyName. '%')->where("Bank","ENBD")->first();
				if($companyExist != '')
				{
					$update = EnbdFinalMisCompletebothCreditCards::find($enbdBothCC->id);
					$update->ALE_NALE = 'Ale';
					$update->ale_nale_update_status = 3;
					$update->save();
						echo "updated";
				exit;
				}
				else
				{
					$update = EnbdFinalMisCompletebothCreditCards::find($enbdBothCC->id);
					$update->ALE_NALE = 'Nale';
					$update->ale_nale_update_status = 3;
					$update->save();
						echo "updated";
				exit;
				}
			}
			else
			{
				$update = EnbdFinalMisCompletebothCreditCards::find($enbdBothCC->id);
				$update->ale_nale_update_status = 5;
				$update->save();
				echo "not updated";
				exit;
			}
			
			}
			else
			{
				$update = EnbdFinalMisCompletebothCreditCards::find($enbdBothCC->id);
				$update->ale_nale_update_status = 5;
				$update->save();
				echo "not updated";
				exit;
			}
		}
		else
		{
				echo "All updated";
				exit;
		}
	}
	
	public function updateProductAbu()
	{
		$productUpdate = EnbdFinalMisCompletebothCreditCards::where("submission_location","Abu Dhabi")->where("product_update_status",1)->first();
		if($productUpdate != '')
		{
			$appId = $productUpdate->application_id;
			if($appId != '' && $appId != NULL)
			{
				$pInfo = AbudhabiProductInfo::where("AppId",$appId)->first();
				if($pInfo != '')
				{
					$update = EnbdFinalMisCompletebothCreditCards::find($productUpdate->id);
					$update->product_update_status = 3;
					if($pInfo->Product == 'SUPER PREMIUM')
					{
						$update->PRODUCT = 'Super Premium';
					}
					else if($pInfo->Product == 'PREMIUM')
					{
						$update->PRODUCT = 'Premium';
					}
					else
					{
						$update->PRODUCT = 'Mass';
					}
					
					$update->save();
					echo "updated";
					exit;
				}
				else
				{
					$update = EnbdFinalMisCompletebothCreditCards::find($productUpdate->id);
					$update->product_update_status = 5;
					$update->save();
					echo "not updated";
					exit;
				}
			}
			else
			{
				    $update = EnbdFinalMisCompletebothCreditCards::find($productUpdate->id);
					$update->product_update_status = 5;
					$update->save();
					echo "not updated";
					exit;
			}
		}
		else
		{
			echo "All updated";
				exit;
		}
		
	}
	
	
	
	public function updateProductAbuInternal()
	{
		$productUpdate = MainMisReport::where("submission_location","Abu Dhabi")->where("product_update_status",1)->first();
		if($productUpdate != '')
		{
			$appId = $productUpdate->application_id;
			if($appId != '' && $appId != NULL)
			{
				$pInfo = AbudhabiProductInfo::where("AppId",$appId)->first();
				if($pInfo != '')
				{
					$update = MainMisReport::find($productUpdate->id);
					$update->product_update_status = 3;
					if($pInfo->Product == 'SUPER PREMIUM')
					{
						$update->PRODUCT = 'Super Premium';
					}
					else if($pInfo->Product == 'PREMIUM')
					{
						$update->PRODUCT = 'Premium';
					}
					else
					{
						$update->PRODUCT = 'Mass';
					}
					
					$update->save();
					echo "updated";
					exit;
				}
				else
				{
					$update = MainMisReport::find($productUpdate->id);
					$update->product_update_status = 5;
					$update->save();
					echo "not updated";
					exit;
				}
			}
			else
			{
				    $update = MainMisReport::find($productUpdate->id);
					$update->product_update_status = 5;
					$update->save();
					echo "not updated";
					exit;
			}
		}
		else
		{
			echo "All updated";
				exit;
		}
		
	}
	
	
	public function updateAleNaleinternalMis()
	{
		$enbdBothCC = MainMisReport::where("ale_nale_update_status",1)->first();
		if($enbdBothCC != '')
		{
			$companyName = $enbdBothCC->fv_company_name;
			
			if($companyName != '')
			{
			$nameofcompanyArray = explode(" ",$companyName);
			if(count($nameofcompanyArray) >0)
			{
			$n = count($nameofcompanyArray);
			$lastElement = $n-1;
			/*
			*creating keywords for industry
			*/
			$searchCompanyName = '';
			for($i=0;$i<$lastElement;$i++)
			{
				if($i == 0)
				{
					$searchCompanyName = $nameofcompanyArray[$i];
				}
				else
				{
					$searchCompanyName = $searchCompanyName.' '.$nameofcompanyArray[$i];
				}
			}
			
			/*
			*creating keywords for industry
			*/
				//echo $searchCompanyName;exit;
				$companyExist  = CompanyListComplete::where('name_of_company', 'like', '%' .$searchCompanyName. '%')->where("Bank","ENBD")->first();
				if($companyExist != '')
				{
					$update = MainMisReport::find($enbdBothCC->id);
					$update->ALE_NALE = 'Ale';
					$update->ale_nale_update_status = 3;
					$update->save();
						echo "updated";
				exit;
				}
				else
				{
					$update = MainMisReport::find($enbdBothCC->id);
					$update->ALE_NALE = 'Nale';
					$update->ale_nale_update_status = 3;
					$update->save();
						echo "updated";
				exit;
				}
			}
			else
			{
				$update = MainMisReport::find($enbdBothCC->id);
				$update->ale_nale_update_status = 5;
				$update->save();
				echo "not updated";
				exit;
			}
			
			}
			else
			{
				$update = MainMisReport::find($enbdBothCC->id);
				$update->ale_nale_update_status = 5;
				$update->save();
				echo "not updated";
				exit;
			}
		}
		else
		{
				echo "All updated";
				exit;
		}
	}
	
	public function updateDataCutAgain()
	{
		$dataCutInformation = DatacutInformation::where("datacut_match_status",3)->where("cus_name","!=","")->whereNotNull("cus_name")->first();
		/* echo '<pre>';
		print_r($dataCutInformation);
		exit; */
		if($dataCutInformation != '')
		{
			if($dataCutInformation->cus_name != '')
			{
			/*
			*checking in Internal MIS
			*start coding
			*/
			$sales_time = $dataCutInformation->sales_time;
			$cusName = $dataCutInformation->cus_name;
			$cusNameArray = explode(" ",$cusName);
			
			$searchName = '';
			if(count($cusNameArray) >1)
			{
				$searchName =$cusNameArray[0];
			}
			else
			{
				$searchName =$cusName;
			}
			
			/*
			*making Sales Time
			*start code
			*/
			$saleTimeArray = explode("-",$sales_time);
			$monthNumber = $saleTimeArray[0];
			$yearNumber = $saleTimeArray[1];
			$saleTimeDateFormat = $yearNumber.'-'.$monthNumber.'-01';
			$saleTimeDateFormatEnd = date("Y-m-t", strtotime($saleTimeDateFormat));
		
			 $saleTimeDateFormatFirst = date("Y-m-d", mktime(0, 0, 0, $monthNumber-2, 1));
			
		
			/*
			*making Sales Time
			*end code
			*/
			$mainMisDetails = MainMisReport::where("datacut_match_status",4)->where("cm_name","like",'%' . $searchName . '%')->where("approved_notapproved",3)->whereBetween("submission_format",[$saleTimeDateFormatFirst,$saleTimeDateFormatEnd])->first();
			
			
			/*
			*working with both credit card table
			*/
			if($mainMisDetails != '')
			{
				$bothModel  = EnbdFinalMisCompletebothCreditCards::where("application_id",$mainMisDetails->application_id)->where("match_datacut",1)->first();
			/* 	echo '<pre>';
			print_r($bothModel);
			exit; */
			
			
				/*updating table*/
				if($bothModel != '')
				{
					$bothUpdateModel = EnbdFinalMisCompletebothCreditCards::find($bothModel->id);
					$bothUpdateModel->datacut_id = $dataCutInformation->id;
					$bothUpdateModel->end_sales_time = $dataCutInformation->sales_time;
					$bothUpdateModel->match_datacut = 2;
					$bothUpdateModel->again_datacut_process = 4;
					$bothUpdateModel->save();
					$updateDataCut = DatacutInformation::find($dataCutInformation->id);
					$updateDataCut->datacut_match_status = 4;
					$updateDataCut->save();
					
					echo "updated";
					exit;
				}
				else
				{
					$updateDataCut = DatacutInformation::find($dataCutInformation->id);
					$updateDataCut->datacut_match_status = 6;
					$updateDataCut->save();
					echo "not match";
					exit;
				}
				
				/*updating table*/
				
				
			}
			else
			{
				$updateDataCut = DatacutInformation::find($dataCutInformation->id);
				$updateDataCut->datacut_match_status = 6;
				$updateDataCut->save();
				echo "not match";
				exit;
			}
			
			/*
			*working with both credit card table
			*/
			/*
			*checking in Internal MIS
			*end coding
			*/
			}
			else
			{
				
					$updateDataCut = DatacutInformation::find($dataCutInformation->id);
					$updateDataCut->datacut_match_status = 6;
					$updateDataCut->save();
				
				echo "Customer Name Blank";
				exit;
			}
		}
		else
		{
			echo "All Updated";
			exit;
		}
		
	}
	
	
	public function sectorUpdate()
	{
		$enbdBothCC = EnbdFinalMisCompletebothCreditCards::where("sector_status",1)->first();
		if($enbdBothCC != '')
		{
			$companyName = $enbdBothCC->fv_company_name;
			
			if($companyName != '')
			{
			$nameofcompanyArray = explode(" ",$companyName);
			if(count($nameofcompanyArray) >0)
			{
			$n = count($nameofcompanyArray);
			$lastElement = $n-1;
			/*
			*creating keywords for industry
			*/
			$searchCompanyName = '';
			for($i=0;$i<$lastElement;$i++)
			{
				if($i == 0)
				{
					$searchCompanyName = $nameofcompanyArray[$i];
				}
				else
				{
					$searchCompanyName = $searchCompanyName.' '.$nameofcompanyArray[$i];
				}
			}
			
			/*
			*creating keywords for industry
			*/
				//echo $searchCompanyName;exit;
				$companyExist  = CompanyListComplete::where('name_of_company', 'like', '%' .$searchCompanyName. '%')->first();
				if($companyExist != '')
				{
					$update = EnbdFinalMisCompletebothCreditCards::find($enbdBothCC->id);
					$update->sector = $companyExist->Industry;
					$update->sector_status = 2;
					$update->save();
						echo "updated";
				exit;
				}
				else
				{
					$update = EnbdFinalMisCompletebothCreditCards::find($enbdBothCC->id);
					$update->sector_status = 3;
					$update->save();
					
						echo "not updated";
				exit;
				}
			}
			else
			{
				$update = EnbdFinalMisCompletebothCreditCards::find($enbdBothCC->id);
				$update->sector_status = 5;
				$update->save();
				echo "not updated";
				exit;
			}
			
			}
			else
			{
				$update = EnbdFinalMisCompletebothCreditCards::find($enbdBothCC->id);
				$update->sector_status = 5;
				$update->save();
				echo "not updated";
				exit;
			}
		}
		else
		{
				echo "All updated";
				exit;
		}
	}
		
		
	public function resetOnBoardingAllFilters(Request $request)
	{
		$request->session()->put('onboarding_department_filter','');
		$request->session()->put('cname_empAll_filter_inner_list','');
		$request->session()->put('company_candAll_filter_inner_list','');
		$request->session()->put('email_candAll_filter_inner_list','');
		$request->session()->put('desc_candAll_filter_inner_list','');
		$request->session()->put('company_RecruiterNameAll_filter_inner_list','');
		$request->session()->put('dept_candAll_filter_inner_list','');
		$request->session()->put('opening_candAll_filter_inner_list','');
		$request->session()->put('status_candAll_filter_inner_list','');
		$request->session()->put('vintage_candAll_filter_inner_list','');
		$request->session()->put('cname_empDeem_filter_inner_list','');
		$request->session()->put('company_RecruiterNamedeem_filter_inner_list','');
		$request->session()->put('company_candDeem_filter_inner_list','');
		$request->session()->put('email_candDeem_filter_inner_list','');
		$request->session()->put('desc_candDeem_filter_inner_list','');
		$request->session()->put('dept_candDeem_filter_inner_list','');
		$request->session()->put('opening_candDeem_filter_inner_list','');
		$request->session()->put('status_candDeem_filter_inner_list','');
		$request->session()->put('vintage_candDeem_filter_inner_list','');
		$request->session()->put('company_RecruiterNameaafaq_filter_inner_list','');
		$request->session()->put('cname_empAafaq_filter_inner_list','');
		$request->session()->put('company_candAafaq_filter_inner_list','');
		$request->session()->put('email_candAafaq_filter_inner_list','');
		$request->session()->put('desc_candAafaq_filter_inner_list','');
		$request->session()->put('dept_candAafaq_filter_inner_list','');
		$request->session()->put('opening_candAafaq_filter_inner_list','');
		$request->session()->put('status_candAafaq_filter_inner_list','');
		$request->session()->put('vintage_candAafaq_filter_inner_list','');
		$request->session()->put('cname_empmashreq_filter_inner_list','');
		$request->session()->put('company_candmashreq_filter_inner_list','');
		$request->session()->put('company_RecruiterNamemashreq_filter_inner_list','');
		$request->session()->put('email_candmashreq_filter_inner_list','');
		$request->session()->put('desc_candmashreq_filter_inner_list','');
		$request->session()->put('dept_candmashreq_filter_inner_list','');
		$request->session()->put('opening_candmashreq_filter_inner_list','');
		$request->session()->put('status_candmashreq_filter_inner_list','');
		$request->session()->put('vintage_candmashreq_filter_inner_list','');
		$request->session()->put('company_RecruiterNameenbd_filter_inner_list','');
		$request->session()->put('cname_emp_filter_inner_list','');
		$request->session()->put('company_cand_filter_inner_list','');
		$request->session()->put('email_cand_filter_inner_list','');
		$request->session()->put('desc_cand_filter_inner_list','');
		$request->session()->put('dept_cand_filter_inner_list','');
		$request->session()->put('opening_cand_filter_inner_list','');
		$request->session()->put('status_cand_filter_inner_list','');
		$request->session()->put('vintage_cand_filter_inner_list','');
		$request->session()->put('company_RecruiterNamevisapipeline_filter_inner_list','');
		$request->session()->put('cname_empvisapipeline_filter_inner_list','');
		$request->session()->put('company_candvisapipeline_filter_inner_list','');
		$request->session()->put('email_candvisapipeline_filter_inner_list','');
		$request->session()->put('desc_candvisapipeline_filter_inner_list','');
		$request->session()->put('dept_candvisapipeline_filter_inner_list','');
		$request->session()->put('opening_candvisapipeline_filter_inner_list','');
		$request->session()->put('status_candvisapipeline_filter_inner_list','');
		$request->session()->put('vintage_candvisapipeline_filter_inner_list','');
		return redirect('documentcollectionAjax');
	}
	
	
	public function updateSalary()
	{
		$updateMod = EnbdFinalMisCompletebothCreditCards::where("update_salary",1)->select('application_id','id')->first();
		
		if($updateMod != '')
		{
			
				$model = MainMisReport::where("application_id",$updateMod->application_id)->first();
				if($model != '')
				{
					$salary = $model->SALARY;
				$update = EnbdFinalMisCompletebothCreditCards::find($updateMod->id);
				$update->SALARY = $salary;
				$update->update_salary = 2;
				$update->save();
				}
				else
				{
					$update = EnbdFinalMisCompletebothCreditCards::find($updateMod->id);
				
				$update->update_salary = 3;
				$update->save();
				}
		
				echo "done";
		exit;
		}
		else
		{
			echo "all done";
			exit;
		}
	
	}
	
}
