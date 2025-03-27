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
use App\Models\MIS\HandsOnMisReport;
use App\Models\MIS\HandsOnFinal;
use App\Models\MIS\HandsOnFinalTab;
use App\Models\Logs\ExportReportLogs;

class ImportController extends Controller
{
  
			public function impMisManualCards(Request $request)
			{
				$filename = 'menual-2-26May2023-1.csv';
				$uploadPath = '/srv/www/htdocs/hrm/public/uploads/exportMIS/';
				$fullpathFileName = $uploadPath . $filename;
				$file = fopen($fullpathFileName, "r");
				$i = 1;
				$dataFromCsv = array();
				while (!feof($file)) {

					$dataFromCsv[$i] = fgetcsv($file);

					$i++;
				}
			 /*    echo '<pre>';
				print_r($dataFromCsv);
				exit;           */
				$iCsv = 0;
			$iCsvIndex = 0;
			$arrayDat = array();
			$arrayDatAttribute = array();
			
				$valuesCheck = array();
				$tlList = array();
				$tlList['rana'] = 705;
				$tlList['tauseef'] = 1045;
				$tlList['arsalan'] = 766;
				$tlList['meenal'] = 988;
				$tlList['rajiv'] =725;
				$tlList['laraib'] =1048;
				$tlList['MANOJKUMAR'] =1029;
				foreach ($dataFromCsv as $fromCsv) {
					if ($iCsv != 0 && isset($fromCsv[1])) {
						
						/*
						*LOC_ADD
						*/
						$fromCsv[2] = str_replace("/","-",$fromCsv[2]);
						//$fromCsv[10] = str_replace("/","-",$fromCsv[10]);
						$fromCsv[26] = str_replace("/","-",$fromCsv[26]);
						$appId = $fromCsv[4];
						
						/*
						*check for App id
						*/
						$existMisCheck = 1;
						$dataCutFlag = 1;
						if($appId  != '' && $appId != 'NOT SOURCED')
						{
							$checkApp = MainMisReport::where("application_id",$appId)->first();
							if($checkApp != '')
							{
								$misObj = MainMisReport::find($checkApp->id);
								if($checkApp->datacut_match_status == 2)
								{
										$dataCutFlag = 2;
								}
								$existMisCheck = 2;
							}
							else
							{
								$misObj = new MainMisReport();
							}
						}
						else
						{
							
							$misObj = new MainMisReport();
						}
						/*
						*check for App id
						*/
						
						
						$misObj->date_of_submission = date("d-m-Y",strtotime($fromCsv[2]));
						$misObj->application_type = 'Fresh App w/o Supp.';
						$misObj->lead_source = $fromCsv[14];
						if($fromCsv[15] == 'PREMIUM')
						{
							$misObj->PRODUCT = 'Premium';
						}
						else if($fromCsv[15] == 'SUPER PREMIUM')
						{
							
							$misObj->PRODUCT = 'Super Premium';
						}
						else
						{
							$misObj->PRODUCT = 'Mass';
						}
					
					
						
						$misObj->application_id = $fromCsv[4];
						if(trim($fromCsv[5]) == 'NOT SOURCED')
						{
							$misObj->current_activity = 5;
						}
						else if(trim($fromCsv[5]) == 'CANCEL')
						{
							$misObj->current_activity = 1;
						}
						else if(trim($fromCsv[5]) == 'DCT')
						{
							$misObj->current_activity = 16;
						}
						else if(trim($fromCsv[5]) == 'End')
						{
							$misObj->current_activity = 2;
						}
						else if(trim($fromCsv[5]) == 'HOLD RCC')
						{
							$misObj->current_activity = 3;
						}
						else if(trim($fromCsv[5]) == 'HOLD SOURCING')
						{
							$misObj->current_activity = 4;
						}
						else if(trim($fromCsv[5]) == 'Rejected')
						{
							$misObj->current_activity = 6;
						}
						else if(trim($fromCsv[5]) == 'UPFRONT REJECT')
						{
							$misObj->current_activity = 6;
						}
						else if(trim($fromCsv[5]) == 'REJECT')
						{
							$misObj->current_activity = 6;
						}
						else if(trim($fromCsv[5]) == 'Single Data Entry')
						{
							$misObj->current_activity = 7;
						}
						else if(trim($fromCsv[5]) == 'CURRENT ACTIVITY')
						{
							$misObj->current_activity = 7;
						}
						else if(trim($fromCsv[5]) == 'Underwritting')
						{
							$misObj->current_activity = 8;
						}
						else if(trim($fromCsv[5]) == 'SUPPLEMENTARY APP')
						{
							$misObj->current_activity = 15;
						}
						else if(trim($fromCsv[5]) == 'UPFRONT HOLD')
						{
							$misObj->current_activity = 17;
						}
						else if(trim($fromCsv[5]) == 'Approved')
						{
							$misObj->current_activity = 18;
						}
						else if(trim($fromCsv[5]) == 'DECLINED')
						{
							$misObj->current_activity = 19;
						}
						else if(trim($fromCsv[5]) == 'DOV')
						{
							$misObj->current_activity = 20;
						}
						else
						{
							$misObj->current_activity = $fromCsv[5];
						}
						
						
						if(trim($fromCsv[16]) == 'Bank Side Pending - Approved')
						{
							$misObj->approved_notapproved = 1;
						}
						else if(trim($fromCsv[16]) == 'CANCEL')
						{
							$misObj->approved_notapproved = 2;
						}
						else if(trim($fromCsv[16]) == 'End')
						{
							$misObj->approved_notapproved = 3;
						}
						else if(trim($fromCsv[16]) == 'NOT SOURCED')
						{
							$misObj->approved_notapproved = 4;
						}
						else if(trim($fromCsv[16]) == 'Rejected')
						{
							$misObj->approved_notapproved = 5;
						}
						else if(trim($fromCsv[16]) == 'Sales Side Pending - Approved')
						{
							$misObj->approved_notapproved = 6;
						}
						
						else if(trim($fromCsv[16]) == 'Wip')
						{
							$misObj->approved_notapproved = 7;
						}
						else if(trim($fromCsv[16]) == 'SUPPLEMENTARY APP')
						{
							$misObj->approved_notapproved = 12;
						}
						else if(trim($fromCsv[16]) == 'HOLD RCC')
						{
							$misObj->approved_notapproved = 13;
						}
						else 
						{
							$misObj->approved_notapproved = $fromCsv[16];
						}
						
						$monthEndArray = explode("-",$fromCsv[6]);
						$monthEndVal = '';
						if(count($monthEndArray) >1)
						{
							$monthEndVal = trim($monthEndArray[0]);
						}
						else
						{
							$monthEndVal =  trim($fromCsv[6]);
						}
						
						if(trim($monthEndVal) == 'AWATING REPORT')
						{
							$misObj->monthly_ends = 1;
						}
						else if(trim($monthEndVal) == 'CANCEL')
						{
							$misObj->monthly_ends = 2;
						}
						else if(trim($monthEndVal) == 'NOT SOURCED')
						{
							$misObj->monthly_ends = 4;
						}
						else if(trim($monthEndVal) == 'Rejected')
						{
							$misObj->monthly_ends = 5;
						}
						else if(trim($monthEndVal) == 'End')
						{
							$misObj->monthly_ends = 3;
						}
						else
						{
							$misObj->monthly_ends = $fromCsv[6];
						}
						$misObj->last_remarks_added = $fromCsv[17];
						$misObj->cm_name = $fromCsv[18];
						$misObj->fv_company_name = $fromCsv[7];
						$misObj->company_name_as_per_visa = $fromCsv[8];
						if($fromCsv[9] == 'ALE')
						{
							$misObj->ALE_NALE = 'Ale';
						}
						else
						{
							$misObj->ALE_NALE = 'Nale';
						}
						
						$misObj->CV_MOBILE_NUMBER = $fromCsv[19];
						$misObj->EV_DIRECT_OFFICE_NO = $fromCsv[20];
						$misObj->E_MAILADDRESS = $fromCsv[21];
						$misObj->SALARY = $fromCsv[23];
						$misObj->LOS = date("d-m-Y",strtotime($fromCsv[10]));
						$misObj->ACCOUNT_STATUS = $fromCsv[11];
						if($misObj->ACCOUNT_NO != 'No')
						{
						$misObj->ACCOUNT_NO = $fromCsv[12];
						}
						$misObj->SALARIED = $fromCsv[22];
						$tlName = strtolower(trim($fromCsv[0]));
						if(isset($tlList[$tlName]))
						{
							$misObj->TL = $tlList[$tlName];
						}
						else
						{
							$misObj->TL = strtolower(trim($fromCsv[0]));
						}
						
						$misObj->SE_CODE_NAME = $fromCsv[1];
						$misObj->REFERENCE_NAME = $fromCsv[31];
						$misObj->REFERENCE_MOBILE_NO = $fromCsv[32];
						$misObj->NATIONALITY = $fromCsv[24];
						$misObj->PASSPORT_NO = $fromCsv[25];
						$misObj->DOB = date("d-m-Y",strtotime($fromCsv[26]));
						$misObj->VISA_Expiry_DATE = $fromCsv[27];
						$misObj->DESIGNATION = $fromCsv[28];
						$misObj->PRE_CALLING = $fromCsv[33];
						$misObj->MMN = $fromCsv[29];
						$misObj->EIDA = $fromCsv[30];
						$misObj->IBAN = $fromCsv[13];
						$misObj->EV = $fromCsv[34];
						$misObj->Type_of_Income_Proof = $fromCsv[35];
						$misObj->submission_format = date("Y-m-d",strtotime($fromCsv[2]));
						
						$scCode = $fromCsv[1];
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
						if($existMisCheck == 1)
						{
						 $misObj->match_status = 1;
						}
			   $misObj->hand_on_status = 1;
			   $misObj->hand_on_status_final = 1;
			   
			   
			   if(trim($fromCsv[16]) == 'Bank Side Pending - Approved')
						{
							$misObj->approved_notapproved_internal = 1;
						}
						else if(trim($fromCsv[16]) == 'CANCEL')
						{
							$misObj->approved_notapproved_internal = 2;
						}
						else if(trim($fromCsv[16]) == 'End')
						{
							$misObj->approved_notapproved_internal = 3;
						}
						else if(trim($fromCsv[16]) == 'NOT SOURCED')
						{
							$misObj->approved_notapproved_internal = 4;
						}
						else if(trim($fromCsv[16]) == 'Rejected')
						{
							$misObj->approved_notapproved_internal = 5;
						}
						else if(trim($fromCsv[16]) == 'Sales Side Pending - Approved')
						{
							$misObj->approved_notapproved_internal = 6;
						}
						
						else if(trim($fromCsv[16]) == 'Wip')
						{
							$misObj->approved_notapproved_internal = 7;
						}
						else if(trim($fromCsv[16]) == 'SUPPLEMENTARY APP')
						{
							$misObj->approved_notapproved_internal = 12;
						}
						else 
						{
							$misObj->approved_notapproved_internal = $fromCsv[16];
						}
						
						
						
						if(trim($fromCsv[5]) == 'NOT SOURCED')
						{
							$misObj->current_activity_internal = 5;
						}
						else if(trim($fromCsv[5]) == 'CANCEL')
						{
							$misObj->current_activity_internal = 1;
						}
						else if(trim($fromCsv[5]) == 'DCT')
						{
							$misObj->current_activity_internal = 16;
						}
						else if(trim($fromCsv[5]) == 'End')
						{
							$misObj->current_activity_internal = 2;
						}
						else if(trim($fromCsv[5]) == 'HOLD RCC')
						{
							$misObj->current_activity_internal = 3;
						}
						else if(trim($fromCsv[5]) == 'HOLD SOURCING')
						{
							$misObj->current_activity_internal = 4;
						}
						else if(trim($fromCsv[5]) == 'Rejected')
						{
							$misObj->current_activity_internal = 6;
						}
						else if(trim($fromCsv[5]) == 'Single Data Entry')
						{
							$misObj->current_activity_internal = 7;
						}
						else if(trim($fromCsv[5]) == 'Underwritting')
						{
							$misObj->current_activity_internal = 8;
						}
						else if(trim($fromCsv[5]) == 'SUPPLEMENTARY APP')
						{
							$misObj->current_activity_internal = 15;
						}
						else
						{
							$misObj->current_activity_internal = $fromCsv[5];
						}
						
			   
			  
			   $misObj->mothly_end_internal = $fromCsv[6];
			   $misObj->Card_Name = $fromCsv[40];
			   $misObj->doj = $fromCsv[36];
			   $misObj->submission_location = 'Dubai';
			  
			 
			  
			  $misObj->complete_status = 2;
			  $misObj->over_ride_status = 0;
			  $misObj->datacut_match_status = 1;
			  $misObj->file_source = 'manual';
						$misObj->type_data = 'Import';
					if($dataCutFlag == 1)
					{
						$misObj->save();
					}
						
						$iCsvIndex++;
						
						/* echo "check";
						exit; */
						
										
						
					}
					$iCsv++;
				}
				echo "manual 2";
						exit;
			}
			
			
			public function impMisManualCardsUpdate(Request $request)
			{
				$filename = '23March2023-mis-internal.csv';
				$uploadPath = '/srv/www/htdocs/hrm/public/uploads/exportMIS/';
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
			
				$valuesCheck = array();
				foreach ($dataFromCsv as $fromCsv) {
					if ($iCsv != 0 && isset($fromCsv[1])) {
						
						/*
						*LOC_ADD
						*/
						$fromCsv[2] = str_replace("/","-",$fromCsv[2]);
						//$fromCsv[10] = str_replace("/","-",$fromCsv[10]);
						$fromCsv[26] = str_replace("/","-",$fromCsv[26]);
						$appId = $fromCsv[4];
						
						/*
						*check for App id
						*/
						if($appId  != '' && $appId != 'NOT SOURCED')
						{
							$checkApp = MainMisReport::where("application_id",$appId)->first();
							if($checkApp != '')
							{
								$misObj = MainMisReport::find($checkApp->id);
								
							}
							else
							{
								$misObj = new MainMisReport();
							}
						}
						else
						{
							
							$misObj = new MainMisReport();
						}
						/*
						*check for App id
						*/
						
						
						$misObj->date_of_submission = date("d-m-Y",strtotime($fromCsv[2]));
						$misObj->application_type = 'Fresh App w/o Supp.';
						$misObj->lead_source = $fromCsv[14];
						if($fromCsv[15] == 'PREMIUM')
						{
							$misObj->PRODUCT = 'Premium';
						}
						else if($fromCsv[15] == 'SUPER PREMIUM')
						{
							
							$misObj->PRODUCT = 'Super Premium';
						}
						else
						{
							$misObj->PRODUCT = 'Mass';
						}
					
					
						
						$misObj->application_id = $fromCsv[4];
						/* $misObj->current_activity = $fromCsv[5];
						$misObj->approved_notapproved = $fromCsv[16];
						$misObj->monthly_ends = $fromCsv[6]; */
						$misObj->last_remarks_added = $fromCsv[17];
						$misObj->cm_name = $fromCsv[18];
						$misObj->fv_company_name = $fromCsv[7];
						$misObj->company_name_as_per_visa = $fromCsv[8];
						if($fromCsv[9] == 'ALE')
						{
							$misObj->ALE_NALE = 'Ale';
						}
						else
						{
							$misObj->ALE_NALE = 'Nale';
						}
						
						$misObj->CV_MOBILE_NUMBER = $fromCsv[19];
						$misObj->EV_DIRECT_OFFICE_NO = $fromCsv[20];
						$misObj->E_MAILADDRESS = $fromCsv[21];
						$misObj->SALARY = $fromCsv[23];
						$misObj->LOS = date("d-m-Y",strtotime($fromCsv[10]));
						$misObj->ACCOUNT_STATUS = $fromCsv[11];
						if($misObj->ACCOUNT_NO != 'No')
						{
						$misObj->ACCOUNT_NO = $fromCsv[12];
						}
						$misObj->SALARIED = $fromCsv[22];
						$misObj->TL = $fromCsv[0];
						$misObj->SE_CODE_NAME = $fromCsv[1];
						$misObj->REFERENCE_NAME = $fromCsv[31];
						$misObj->REFERENCE_MOBILE_NO = $fromCsv[32];
						$misObj->NATIONALITY = $fromCsv[24];
						$misObj->PASSPORT_NO = $fromCsv[25];
						$misObj->DOB = date("d-m-Y",strtotime($fromCsv[26]));
						$misObj->VISA_Expiry_DATE = $fromCsv[27];
						$misObj->DESIGNATION = $fromCsv[28];
						$misObj->PRE_CALLING = $fromCsv[33];
						$misObj->MMN = $fromCsv[29];
						$misObj->EIDA = $fromCsv[30];
						$misObj->IBAN = $fromCsv[13];
						$misObj->EV = $fromCsv[34];
						$misObj->Type_of_Income_Proof = $fromCsv[35];
						$misObj->submission_format = date("Y-m-d",strtotime($fromCsv[2]));
						
						$scCode = $fromCsv[1];
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
						
						/* $misObj->created_by = $request->session()->get('EmployeeId'); */
						/*  $misObj->match_status = 1;
			   $misObj->hand_on_status = 1;
			   $misObj->hand_on_status_final = 1; */
			   $misObj->current_activity_internal = $fromCsv[5];
			   $misObj->approved_notapproved_internal = $fromCsv[16];
			   $misObj->mothly_end_internal = $fromCsv[6];
			  
			  $misObj->file_source = 'manual';
			  $misObj->type_data = 'Import';
			  
			 /*  $misObj->complete_status = 2;
			  $misObj->over_ride_status = 0;
			  
						$misObj->type_data = 'Import'; */
					
						$misObj->save();
						
						$iCsvIndex++;
						
						
						
						
										
						
					}
					$iCsv++;
				}
				echo "Physical File Updated";
						exit;
			}
			
			
			
			public function impMisTabCards(Request $request)
			{
				$filename = '26-Tab_internal_mis-3-updated-Abu1.csv';
				$uploadPath = '/srv/www/htdocs/hrm/public/uploads/exportMIS/';
				$fullpathFileName = $uploadPath . $filename;
				$file = fopen($fullpathFileName, "r");
				$i = 1;
				$dataFromCsv = array();
				while (!feof($file)) {
					/* if(!empty(fgetcsv($file)))
					{ */
					$dataFromCsv[$i] = fgetcsv($file);

					$i++;
					/* } */
				}
				$dataFromCsv = array_filter($dataFromCsv);
				/* echo '<pre>';
				print_r($dataFromCsv);
				exit;   */ 
				$iCsv = 0;
			$iCsvIndex = 0;
			$arrayDat = array();
			$arrayDatAttribute = array();
			 
				$valuesCheck = array();
				$tlList = array();
				$tlList['rana'] = 705;
				$tlList['tauseef'] = 1045;
				$tlList['arsalan'] = 766;
				$tlList['meenal'] = 988;
				$tlList['rajiv'] =725;
				$tlList['laraib'] =1048;
				$tlList['MANOJKUMAR'] =1029;
				foreach ($dataFromCsv as $fromCsv) {
					if ($iCsv != 0 && isset($fromCsv[1])) {
						
						/*
						*LOC_ADD
						*/
						//$fromCsv[2] = str_replace("/","-",$fromCsv[2]);
						//$fromCsv[10] = str_replace("/","-",$fromCsv[10]);
						//$fromCsv[26] = str_replace("/","-",$fromCsv[26]);
						$appId = $fromCsv[4];
						
						/*
						*check for App id
						*/
						$dataCutFlag = 1;
						if($appId  != '' && $appId != 'NOT SOURCED')
						{
							$checkApp = MainMisReport::where("application_id",$appId)->first();
							if($checkApp != '')
							{
								$misObj = MainMisReport::find($checkApp->id);
									if($checkApp->datacut_match_status == 2)
									{
											$dataCutFlag = 2;
									}
								
							}
							else
							{
								$misObj = new MainMisReport();
							}
						}
						else
						{
							
							$misObj = new MainMisReport();
						}
						/*
						*check for App id
						*/
						$misObj->application_id = $fromCsv[4];
						
						
						$tlName = strtolower(trim($fromCsv[0]));
						if(isset($tlList[$tlName]))
						{
							$misObj->TL = $tlList[$tlName];
						}
						else
						{
							$misObj->TL = strtolower(trim($fromCsv[0]));
						}
						
						
						$misObj->SE_CODE_NAME = $fromCsv[1];
						$misObj->date_of_submission = date("d-m-Y",strtotime($fromCsv[2]));
						$misObj->application_type = 'Fresh App w/o Supp.';
						
						if($fromCsv[8] == 'PREMIUM')
						{
							$misObj->PRODUCT = 'Premium';
						}
						else if($fromCsv[8] == 'SUPER PREMIUM')
						{
							
							$misObj->PRODUCT = 'Super Premium';
						}
						else
						{
							$misObj->PRODUCT = 'Mass';
						}
						$misObj->Card_Name = $fromCsv[9];
						$misObj->fv_company_name = $this->clean($fromCsv[10]);;
						$misObj->company_name_as_per_visa = $this->clean($fromCsv[11]);;
						
						
						
						$monthEndArray = explode("-",$fromCsv[38]);
						$currentStatus = '';
						if(count($monthEndArray) >1)
						{
							$currentStatus = trim($monthEndArray[0]);
						}
						else
						{
							$currentStatus =  trim($fromCsv[38]);
						}
						
						
						if($currentStatus == 'CANCEL')
						{
							$misObj->approved_notapproved = 2;
						}
						else if($currentStatus == 'CANCELLED')
						{
							$misObj->approved_notapproved = 2;
						}
						else if($currentStatus == 'REJECTED')
						{
							$misObj->approved_notapproved = 5;
						}
						else if($currentStatus == 'WIP')
						{
							$misObj->approved_notapproved = 7;
						}
						else if($currentStatus == 'TERMINATED')
						{
							$misObj->approved_notapproved = 2;
						}
						else if($currentStatus == 'ERROR')
						{
							$misObj->approved_notapproved = 2;
						}
						else if($currentStatus == 'End')
						{
							$misObj->approved_notapproved = 3;
						}
						else
						{
						
						$misObj->approved_notapproved = $fromCsv[38];
						}
						
						$misObj->last_remarks_added = $this->clean($fromCsv[18]);
						$misObj->cm_name = $this->clean($fromCsv[20]);
						$misObj->CV_MOBILE_NUMBER = $fromCsv[21];
						$misObj->DMS_Status_Description = $this->clean($fromCsv[19]);
						$misObj->SALARY = $fromCsv[25];
						
						$misObj->SALARIED = $fromCsv[24];
						$misObj->NATIONALITY = $fromCsv[26];
						//$misObj->DESIGNATION = $fromCsv[30];
						
						
						$misObj->submission_format = date("Y-m-d",strtotime($fromCsv[2]));
						
						$scCode = $fromCsv[1];
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
						 $misObj->match_status = 1;
			   $misObj->hand_on_status = 1;
			   $misObj->hand_on_status_final = 1;
			  
			  
			  $misObj->submission_location = 'Abu Dhabi';
			  $misObj->complete_status = 2;
			  $misObj->over_ride_status = 0;
			  $misObj->file_source = 'Tab';
			  $misObj->current_activity_tab = $fromCsv[38];
			  $misObj->datacut_match_status = 1;
			  $misObj->tab_process_status = 1;
			  $misObj->ALE_NALE = 'NONE';
						$misObj->type_data = 'Import';
						if($dataCutFlag ==1)
						{
						$misObj->save();
						}
						$iCsvIndex++;
						
						
						
						/* echo "check";
						exit */;
									
						
					}
					$iCsv++;
				}
				echo "Tab 1 abu done1";
						exit;
			}
			function clean($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}
}
