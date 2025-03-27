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
}
