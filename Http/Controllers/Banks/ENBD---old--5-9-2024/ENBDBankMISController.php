<?php

namespace App\Http\Controllers\Banks\ENBD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\DepartmentForm;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Attribute\DepartmentFormChildEntry;
use App\Models\Attribute\FormProduct;
use App\Models\Attribute\MasterAttribute;
use App\Models\Attribute\AttributeType;
use App\Models\Attribute\FormSection;

use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use App\Models\Bank\CBD\CBDBankMis;
use App\Models\Bank\CBD\BankCBDMTD;
use App\Models\Bank\CBD\CbdImportFile;
use App\Models\Bank\EIB\EibImportFile;
use App\Models\Bank\EIB\EibBankMis;
use App\Models\Bank\ENBD\EnbdImportFile;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Http\Controllers\Attribute\DepartmentFormController;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\PerformanceFlagRules\MasterPayoutPre;
use App\Models\SEPayout\WorkTimeRange;
use App\Models\Dashboard\MasterPayout;
use Session;
use App\Models\Employee\ExportDataLog;
use App\Models\MIS\MainMisReportTab;
use App\Models\MIS\ENBDCardsMisReport;

use App\Models\Attribute\ENBDDepartmentFormEntry;
use App\Models\Attribute\ENBDDepartmentFormChildEntry;

use App\Models\Recruiter\Designation;
use App\Models\Bank\SCB\SCBDepartmentFormChildEntry;
use App\Models\Bank\SCB\SCBDepartmentFormParentEntry;
use App\Models\Bank\SCB\SCBImportFile;
use App\Models\Bank\SCB\SCBBankMis;
use App\Models\SEPayout\RangeDetailsVintage;



class ENBDBankMISController extends Controller
{
   protected function getEmployeeName($empid)
	 {
		 if($empid != '' && $empid != NULL)
		 {
			$empName = Employee_details::select("emp_name")->where("emp_id",$empid)->first();
			if($empName != '')
			{
				return $empName->emp_name;
			}
			else
			{
				return '';
			}
		 }
		 else
		 {
			 return '';
		 }
	 }
 public function importBankENBD()
 {
	return view("Banks/ENBD/BankMIS/importBankENBD");
 }
 
 public function loginCalRenderTabCBD(Request $request)
 {
	$monthSelected = $request->m;
	$yearSelected = $request->y;
	return view("Banks/ENBD/BankMIS/loginCalRenderTabEIB",compact('monthSelected','yearSelected'));
	 
 }
 
	public static function getEIBFileLog($calendar_date=NULL)
	{
		return $getCBDFileLog = EnbdImportFile::where('calendar_date', $calendar_date)->where("type",1)->orderBy('updated_at','DESC')->first();
	}
	
	public static function getEIBFileLogMTF($calendar_date=NULL)
	{
		return $getCBDFileLog = EnbdImportFile::where('calendar_date', $calendar_date)->where("type",2)->orderBy('updated_at','DESC')->first();
	}

	public static function getENBDDatCutFileLog($calendar_date=NULL)
	{
		return $getCBDFileLog = EnbdImportFile::where('calendar_date', $calendar_date)->where("type",3)->orderBy('updated_at','DESC')->first();
	}
 
	public function FileUploadExcelENBDimport(Request $request)
	{
					$user_id = $request->session()->get('EmployeeId');
					
							//echo '<pre>';
							//print_r($request->uploadedDate);exit;
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
					$fileType = $request->fileType;
					$fileName = 'ENBD_Bank_MIS_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

						$request->file->move(public_path('uploads/ENBDJonus/'), $fileName);
						$spreadsheet = new Spreadsheet();

						$inputFileType = 'Xlsx';
						$inputFileName = '/srv/www/htdocs/hrm/public/uploads/ENBDJonus/'.$fileName;

						/*  Create a new Reader of the type defined in $inputFileType  */
						$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
						/*  Advise the Reader that we only want to load cell data  */
						$reader->setReadDataOnly(true);
						$spreadsheet = $reader->load($inputFileName);
						$worksheet = $spreadsheet->getActiveSheet();
						// Get the highest row number and column letter referenced in the worksheet
						$highestRow = $worksheet->getHighestRow()-1; // e.g. 10							

						
						
						$tableName = 'ENBD_import_file';

					$values = array('user_id'=>$user_id,'file_name' => $fileName,'totalcount' => $highestRow,'calendar_date' => $request->uploadedDate,'type'=>1);
					$filenameID = DB::table($tableName)->insertGetId($values);

					   $response['code'] = '200';
					   $response['message'] = "You have successfully upload file.";
					   $response['filename'] = $fileName;
					   $response['filenameID'] = $filenameID;
					   $response['totalcount'] = $highestRow;
					/* } */
					   echo json_encode($response);
					   exit;
					
	}
				
				
				public function FileUploadExcelJonusTabPost(Request $request)
				{
						$user_id = $request->session()->get('EmployeeId');
					
							//echo '<pre>';
							//print_r($request->uploadedDate);exit;
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
					$fileType = $request->fileType;
					$fileName = 'ENBD_Jonus_Tab_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

						$request->file->move(public_path('uploads/ENBDJonus/'), $fileName);
						$spreadsheet = new Spreadsheet();

						$inputFileType = 'Xlsx';
						$inputFileName = '/srv/www/htdocs/hrm/public/uploads/ENBDJonus/'.$fileName;

						/*  Create a new Reader of the type defined in $inputFileType  */
						$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
						/*  Advise the Reader that we only want to load cell data  */
						$reader->setReadDataOnly(true);
						$spreadsheet = $reader->load($inputFileName);
						$worksheet = $spreadsheet->getActiveSheet();
						// Get the highest row number and column letter referenced in the worksheet
						$highestRow = $worksheet->getHighestRow()-1; // e.g. 10							

						
						
						$tableName = 'ENBD_import_file';

					$values = array('user_id'=>$user_id,'file_name' => $fileName,'totalcount' => $highestRow,'calendar_date' => $request->uploadedDate,'type'=>2);
					$filenameID = DB::table($tableName)->insertGetId($values);

					   $response['code'] = '200';
					   $response['message'] = "You have successfully upload file.";
					   $response['filename'] = $fileName;
					   $response['filenameID'] = $filenameID;
					   $response['totalcount'] = $highestRow;
					/* } */
					   echo json_encode($response);
					   exit;
				}
				
				public function ENBDFileImport(Request $request)
				{	
			
					$user_id = $request->session()->get('EmployeeId');
					$result = array();
					$attr_f_import = $request->attr_f_import; 
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('ENBD_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/ENBDJonus/';
					$fullpathFileName = $uploadPath . $filename;
				
					 $spreadsheet = new Spreadsheet();

					$inputFileType = 'Xlsx';
					//$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/excel/'.$fileName;

					/*  Create a new Reader of the type defined in $inputFileType  */
					$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
					/*  Advise the Reader that we only want to load cell data  */
					$reader->setReadDataOnly(false); /// For date format issue
					$spreadsheet = $reader->load($fullpathFileName);
					$sheetData = $spreadsheet->getActiveSheet()->toArray();
					
					//$data=$sheetData->getIndex();
					//$data=$sheetData->getRowIterator($conter);
					// echo "<pre>";
					// print_r($sheetData);//exit;

					// if(!empty($sheetData))
					// {
					// 	echo "Sample";
					// }
					// else
					// {
					// 	echo "Hello";
					// }
					//exit;


						
					if(!empty($sheetData))
					{
						for($k=0;$k<count($sheetData);$k++)
						{
							
							if(count($sheetData[$k])!= 62)
							{
								$fileInfo = DB::table('ENBD_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}	
												
							
							// $sheetData[$k] = str_replace("'","`",$sheetData[$k]);
							// $allowBureau_Score = 1;
							// if(trim($sheetData[$k][16]) == '#N/A' || trim($sheetData[$k][16]) == 'N/A' || trim($sheetData[$k][16]) == 'NA' || trim($sheetData[$k][16]) == '#NA' || trim($sheetData[$k][16]) == '')
							// {
							// 	$allowBureau_Score = 2;
							// }
							
							// $allowAPP_SCORE = 1;
							
							// if(trim($sheetData[$k][17]) == '#N/A' || trim($sheetData[$k][17]) == 'N/A' || trim($sheetData[$k][17]) == 'NA' || trim($sheetData[$k][17]) == '#NA' || trim($sheetData[$k][17]) == '')
							// {
							// 	$allowAPP_SCORE = 2;
							// }
							
							
							// $allowBureau_MOB = 1;
							// if(trim($sheetData[$k][18]) == '#N/A' || trim($sheetData[$k][18]) == 'N/A' || trim($sheetData[$k][18]) == 'NA' || trim($sheetData[$k][18]) == '#NA' || trim($sheetData[$k][18]) == '')
							// {
							// 	$allowBureau_MOB = 2;
							// }
							
							// $allowTotal_Liabilities = 1; 
							// if(trim($sheetData[$k][19]) == '#N/A' || trim($sheetData[$k][19]) == 'N/A' || trim($sheetData[$k][19]) == 'NA' || trim($sheetData[$k][19]) == '#NA' || trim($sheetData[$k][19]) == '')
							// {
							// 	$allowTotal_Liabilities = 2;
							// }
							
							
							// $allowTotal_DSR = 1;
							// if(trim($sheetData[$k][20]) == '#N/A' || trim($sheetData[$k][20]) == 'N/A' || trim($sheetData[$k][20]) == 'NA' || trim($sheetData[$k][20]) == '#NA' || trim($sheetData[$k][20]) == '')
							// {
							// 	$allowTotal_DSR = 2;
							// }
							
							
							// $allowAECB_Status = 1;
							// if(trim($sheetData[$k][21]) == '#N/A' || trim($sheetData[$k][21]) == 'N/A' || trim($sheetData[$k][21]) == 'NA' || trim($sheetData[$k][21]) == '#NA' || trim($sheetData[$k][21]) == '')
							// {
							// 	$allowAECB_Status = 2;
							// }
							
							// $sheetData[$k][18] = str_replace("#N/A","",$sheetData[$k][18]);
							// $sheetData[$k][19] = str_replace("#N/A","",$sheetData[$k][19]);
							// $sheetData[$k][20] = str_replace("#N/A","",$sheetData[$k][20]);
							// $sheetData[$k][8] = str_replace("#N/A","",$sheetData[$k][8]);
							// $sheetData[$k][9] = str_replace("#N/A","",$sheetData[$k][9]);
							// $sheetData[$k][14] = str_replace("#N/A","",$sheetData[$k][14]);
							// $sheetData[$k][16] = str_replace("#N/A","",$sheetData[$k][16]);
							// $sheetData[$k][17] = str_replace("#N/A","",$sheetData[$k][17]);

								$dateofs = trim($sheetData[$k][8]);
								$unixTime1 = strtotime($dateofs);
								$sourcingDate = date("Y-m-d", $unixTime1);

								$filterdate = trim($sheetData[$k][3]);
								$unixTime2 = strtotime($filterdate);
								$pidDate = date("Y-m-d", $unixTime2);

								$signeddate = trim($sheetData[$k][9]);
								$unixTime3 = strtotime($signeddate);
								$signDate = date("Y-m-d", $unixTime3);

								$wcdate = trim($sheetData[$k][25]);
								$unixTime4 = strtotime($wcdate);
								$wcActionDate = date("Y-m-d", $unixTime4);

								$latupdate = trim($sheetData[$k][29]);
								$unixTime5 = strtotime($latupdate);
								$lastupdateDate = date("Y-m-d", $unixTime5);

								$sourceddate = trim($sheetData[$k][37]);
								$unixTime6 = strtotime($sourceddate);
								$sourcedonDate = date("Y-m-d", $unixTime6);


								$reportdate = trim($sheetData[$k][38]);
								$unixTime7 = strtotime($reportdate);
								$reportgenDate = date("Y-m-d", $unixTime7);

								$useridsess=$request->session()->get('EmployeeId');





							$file_values = array(
												'CARDID' => trim($sheetData[$k][0]),
												'customer_name' => trim($sheetData[$k][2]),

												'FILERECEIPTDTTIME1' => trim($sheetData[$k][3]),
												'FILERECEIPTDTTIME' => $pidDate,

												'OFFER' => trim($sheetData[$k][4]),
												'CURRENTACTIVITY' => trim($sheetData[$k][5]),
												'STATUS' => trim($sheetData[$k][6]),
												'APPLICATIONTYPE' => trim($sheetData[$k][7]),

												'DATEOFSOURCING' => $sourcingDate,
												'DATEOFSOURCING1' => trim($sheetData[$k][8]),
												
												'SIGNEDDATE1' => trim($sheetData[$k][9]),
												'SIGNEDDATE' => $signDate,


												'PRODUCT' => trim($sheetData[$k][10]),
												'SCHEMEGROUP' => trim($sheetData[$k][11]),
												'SCHEME' => trim($sheetData[$k][12]),
												'CHANNELCODE' => trim($sheetData[$k][13]),
												'DSA_BRANCH' => trim($sheetData[$k][14]),
												'DME_RBE' => trim($sheetData[$k][15]),
												'APP_REJ_CANDATE_TIME' => trim($sheetData[$k][16]),
												'LASTREMARKSADDED' => trim($sheetData[$k][17]),
												'CHANNELCODEPERV' => trim($sheetData[$k][18]),
												'EVSTATUS' => trim($sheetData[$k][19]),
												'EVACTIONDATE' => trim($sheetData[$k][20]),
												'EVUSER' => trim($sheetData[$k][21]),
												'CVSTATUS' => trim($sheetData[$k][22]),
												'CVACTIONDATE' => trim($sheetData[$k][23]),
												'WCSTATUS' => trim($sheetData[$k][24]),

												'WCACTIONDATE1' => trim($sheetData[$k][25]),
												'WCACTIONDATE' => $wcActionDate,


												'WCREMARKS' => trim($sheetData[$k][26]),
												'APPLICATIONCREDITSTATUS' => trim($sheetData[$k][27]),
												'CARDAPPROVALSTATUS' => trim($sheetData[$k][28]),

												'LASTUPDATED1' => trim($sheetData[$k][29]),
												'LASTUPDATED' => $lastupdateDate,


												'PRI_SUPP_STANDALONE' => trim($sheetData[$k][30]),
												//'application_created_at' => ($sheetData[$k][4]?date('Y-m-d',strtotime($sheetData[$k][4])):'0000-00-00'),												
												'PRIMARYCARD_STAND_ALONE' => trim($sheetData[$k][31]),
												'PRIMARY_ACC_NO_STANDALONE' => trim($sheetData[$k][32]),
												'CARDTYPE' => trim($sheetData[$k][33]),
												'BILLINGCYCLE' => ($sheetData[$k][34]),
												'REQUESTEDLIMIT' => ($sheetData[$k][35]),
												'APPROVEDLIMIT' => trim($sheetData[$k][36]),

												'SOURCED_ON1' => trim($sheetData[$k][37]),
												'SOURCED_ON' => $sourcedonDate,


												'REPORTGENDATE1' => trim($sheetData[$k][38]),
												'REPORTGENDATE' => $reportgenDate,


												'REFERRAL_GROUP' => trim($sheetData[$k][39]),
												'REFERRAL_CODE' => ($sheetData[$k][40]),
												'REFERRALNAME' => trim($sheetData[$k][41]),												
												'P1CODE' => trim($sheetData[$k][42]),
												'CASSTATUS' => trim($sheetData[$k][43]),
												// 'final_decision' => trim($sheetData[$k][18]),
												'created_at' => date('Y-m-d H:i:s'),
												'created_by' => $useridsess,
												//'application_created_at' => date('Y-m-d H:i:s'),	
												
												);



												$application_number = trim($sheetData[$k][1]);


												if($application_number!='')
												{
													$row_values = array(
													
														'current_activity' => trim($sheetData[$k][5]),
														'app_id' => trim($sheetData[$k][1]),
														'created_at' => date('Y-m-d H:i:s'),
														'updated_at' => date('Y-m-d H:i:s'),
														'uploaded_by' => $user_id,
														'type' => 'physical',
															
														
													);
												}


												










								
								//  echo "<pre>";
								// print_r($file_values);
								// exit; 							
							/*
							*check for #NA
							*/							
							// if($allowBureau_MOB ==1)
							// {
							
							// $file_values['Bureau_MOB'] = ($sheetData[$k][18]?trim(str_replace(",","",$sheetData[$k][18])):'0');
							// }
							
							
							// if($allowTotal_Liabilities ==1)
							// {
							
							// $file_values['Total_Liabilities'] = ($sheetData[$k][19]?trim(str_replace(",","",$sheetData[$k][19])):'0');
							// }
							
							// if($allowTotal_DSR ==1)
							// {
							
							// $file_values['Total_DSR'] = ($sheetData[$k][20]?trim(str_replace(",","",$sheetData[$k][20])):'0');
							// }
							// if($allowAECB_Status ==1)
							// {
							
							// $file_values['AECB_Status'] = trim($sheetData[$k][21]);
							// }
							
							// if($allowBureau_Score ==1)
							// {
							
							// $file_values['Bureau_Score'] = ($sheetData[$k][16]?trim(str_replace(",","",$sheetData[$k][16])):'0');
							// }
							
							// if($allowAPP_SCORE ==1)
							// {
							
							// $file_values['APP_SCORE'] = ($sheetData[$k][17]?trim(str_replace(",","",$sheetData[$k][17])):'0');
							// }
							// /*
							// *check for #NA
							// */	
							// if($k == 287)
							// {
							// 	/*  echo trim($sheetData[$k][0]);
							// 	echo "====";
							// echo "<pre>";
							// print_r($file_values);
							
							// exit;   */
							// }
											
							/* 	echo "<pre>";
							print_r($file_values);
							
							exit;		 */		
							 
							$whereRaw = "application_no ='".$application_number."'";
							$check = DB::table('ENBD_Jonus_Cards_physical')->whereRaw($whereRaw)->get();

							// echo "<pre>";
							// print_r($check);
							// exit;

							if(count($check)>0)
							{			
								$getdatacbd_marging_status = DB::table('ENBD_Jonus_Cards_physical')->whereRaw($whereRaw)->first();
								// if($getdatacbd_marging_status == 2)
								// {
								// 	$file_values['update_status'] = 1;
								// }
								DB::table('ENBD_Jonus_Cards_physical')->where('application_no', $application_number)->update($file_values);
								
							}
							else
							{
								
								$all_values = $file_values;
								$all_values['application_no'] = $application_number;
								//$all_values['cbd_marging_status'] = 1;
							
								
								DB::table('ENBD_Jonus_Cards_physical')->insert($all_values);
								
								// $singlepost = EibBankMis::where('application_number','Application Number')->first();
								// $singlepost->delete();

								
							} 

							
							DB::table('enbd_jonus_app_activity_history')->insert($row_values);

							$eibBankMod = ENBDCardsMisReport::whereNull("update_status")->whereNull("enbd_marging_status")->whereNull("matched_status")->get();
							foreach($eibBankMod as $eib)
							{
								$empdetails = Employee_details::where("source_code",$eib->P1CODE)->first();

								if($empdetails)
								{
									$file_values2 = array();
									$file_values2['employee_id'] = $empdetails->emp_id;	
									DB::table('ENBD_Jonus_Cards_physical')->where('P1CODE', $eib->P1CODE)->update($file_values2);
								}
							}
							




						}
						
						$result['code'] = 200;
					
					}
					else
					{
						$result['code'] = 300;
					}
				
				$request->session()->flash('success','Import Completed.');
				echo json_encode($result);
				exit;
				
			}
			
			
			public function ENBDJonusTabFileImport(Request $request)
			{
					$user_id = $request->session()->get('EmployeeId');
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('ENBD_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/ENBDJonus/';
					$fullpathFileName = $uploadPath . $filename;
				
					 $spreadsheet = new Spreadsheet();

					$inputFileType = 'Xlsx';
					//$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/excel/'.$fileName;

					/*  Create a new Reader of the type defined in $inputFileType  */
					$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
					/*  Advise the Reader that we only want to load cell data  */
					$reader->setReadDataOnly(false); /// For date format issue
					$spreadsheet = $reader->load($fullpathFileName);
					$sheetData = $spreadsheet->getActiveSheet()->toArray();
					
					//$data=$sheetData->getIndex();
					//$data=$sheetData->getRowIterator($conter);
					//echo "<pre>";
					//print_r($sheetData);exit;
						
					if(!empty($sheetData))
					{
						for($k=0;$k<count($sheetData);$k++)
						{
							
							if(count($sheetData[$k])!= 30)
							{
								$fileInfo = DB::table('ENBD_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}							
							
							$sheetData[$k] = str_replace("'","`",$sheetData[$k]);
							
							$file_values = array(
												'customer_type' => trim($sheetData[$k][1]),
												'application_mode' => trim($sheetData[$k][2]),
												'application_no' => trim($sheetData[$k][3]),
												'PID' => trim($sheetData[$k][4]),
												'application_status' => trim($sheetData[$k][5]),
												'application_created' => ($sheetData[$k][6]?date('Y-m-d',strtotime($sheetData[$k][6])):'0000-00-00'),
												'application_createdBy' => trim($sheetData[$k][7]),
												
												'created_group' => trim($sheetData[$k][8]),
												'created_month' => trim($sheetData[$k][9]),
												'STP_NSTP_flag' => trim($sheetData[$k][10]),
												'customer_name' => trim($sheetData[$k][11]),
												'customer_mobile' => trim($sheetData[$k][12]),
												'RBE_Code' => trim($sheetData[$k][13]),
												'DMS_Outcome' => trim($sheetData[$k][14]),
												'DMS_Status_Description' => trim($sheetData[$k][15]),
												'Card_Name' => trim($sheetData[$k][16]),
												'Scheme' => trim($sheetData[$k][17]),
												'employee_id' => trim($sheetData[$k][18]),
												'Employee_status' => trim($sheetData[$k][19]),
												'handsOnReport' => trim($sheetData[$k][20]),
												'submitted_date' => ($sheetData[$k][21]?date('Y-m-d',strtotime($sheetData[$k][21])):'0000-00-00'),
												'close_date' => ($sheetData[$k][22]?date('Y-m-d',strtotime($sheetData[$k][22])):'0000-00-00'),
												'sourcing_duration' => trim($sheetData[$k][23]),
												'creation_location' => trim($sheetData[$k][24]),
												'submission_location' => trim($sheetData[$k][25]),
												'process_status' => trim($sheetData[$k][26]),
												'ale_nonale' => trim($sheetData[$k][27]),
												'application_workflow_status' => trim($sheetData[$k][28]),
												'application_reason_for_assignment' => trim($sheetData[$k][29]), 	
												
												
												
												);


												$application_no = trim($sheetData[$k][3]);
												if($application_no!='')
												{
													$row_values = array(
													
														'application_status' => trim($sheetData[$k][5]),
														'app_id' => trim($sheetData[$k][3]),
														'created_at' => date('Y-m-d H:i:s'),
														'updated_at' => date('Y-m-d H:i:s'),
														'uploaded_by' => $user_id,
														'type' => 'tab',
															
														
													);
												}

												


												
												
								
							
							$whereRaw = " application_no ='".$application_no."'";
							$check = DB::table('Jonus_Enbd_Mis_Cards_Tab')->whereRaw($whereRaw)->get();

							if(count($check)>0)
							{			
							
								
									/* $file_values['update_status'] = 1;
									$file_values['match_bank_status'] = 1; */
								
								DB::table('Jonus_Enbd_Mis_Cards_Tab')->where('application_no', $application_no)->update($file_values);
								
							}
							else
							{
								
								$all_values = $file_values;
								$all_values['application_no'] = $application_no;
								// $all_values['update_status'] = 1;
								// $all_values['match_bank_status'] = 1;
								$all_values['created_at'] = date("Y-m-d H:i:s");
								$all_values['updated_at'] = date("Y-m-d H:i:s");
								$all_values['created_by'] = $user_id;
							
								
								DB::table('Jonus_Enbd_Mis_Cards_Tab')->insert($all_values);
							} 

							DB::table('enbd_jonus_app_activity_history')->insert($row_values);
						}
						
						$result['code'] = 200;
					
					}
					else
					{
						$result['code'] = 300;
					}
				
				$request->session()->flash('success','Import Completed.');
				echo json_encode($result);
				exit;
			}
			
			
			public function exportENBDJonusTabData(Request $request)
			{
				 $parameters = $request->input(); 
				/*  echo "<pre>";
				 print_r($parameters);
				 exit;  */
				 $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'ENBD_Jonus_Tab_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AD1');
			$sheet->setCellValue('A1', 'ENBD Jonus Tab - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Application No'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Application Mode'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Customer Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('RBE Code'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Mobile'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('DMS Outcome'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Card Name'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Scheme Name'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Application Status'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Application Created'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('PID'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('STP/NSTP Flag'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('N'.$indexCounter, strtoupper('AGENCY'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('O'.$indexCounter, strtoupper('Status'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('P'.$indexCounter, strtoupper('Channel'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('Q'.$indexCounter, strtoupper('TOTAL LIMIT POST APPROVAL'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('R'.$indexCounter, strtoupper('DROP OFF STAGE'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('S'.$indexCounter, strtoupper('Bureau Score'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('T'.$indexCounter, strtoupper('APP SCORE'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('U'.$indexCounter, strtoupper('Bureau MOB'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('V'.$indexCounter, strtoupper('Total Liabilities'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('W'.$indexCounter, strtoupper('Total DSR'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('X'.$indexCounter, strtoupper('AECB Status'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('Y'.$indexCounter, strtoupper('Decline Reason (S1) '))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('Z'.$indexCounter, strtoupper('Recruiter Name'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('AA'.$indexCounter, strtoupper('Recruiter Category'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('AB'.$indexCounter, strtoupper('Vintage'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('AC'.$indexCounter, strtoupper('Range Id'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			// $sheet->setCellValue('AD'.$indexCounter, strtoupper('Employee Id'))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  MainMisReportTab::where("id",$sid)->first();

				$createdDate = date("d M, Y", strtotime($mis->application_created));
				$indexCounter++;
				
				$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->application_no)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mis->application_mode)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $mis->customer_name)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mis->RBE_Code)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mis->customer_mobile)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->DMS_Outcome)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->Card_Name)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $mis->Scheme)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->application_status)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $createdDate)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $mis->PID)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $mis->STP_NSTP_flag)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('N'.$indexCounter, $mis->AGENCY)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('O'.$indexCounter, $mis->Status)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('P'.$indexCounter, $mis->Channel)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('Q'.$indexCounter, $mis->TOTAL_LIMIT_POST_APPROVAL)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('R'.$indexCounter, $mis->DROP_OFF_STAGE)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('S'.$indexCounter, $mis->Bureau_Score)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('T'.$indexCounter, $mis->APP_SCORE)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('U'.$indexCounter, $mis->Bureau_MOB)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('V'.$indexCounter, $mis->Total_Liabilities)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('W'.$indexCounter, $mis->Total_DSR)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('X'.$indexCounter, $mis->AECB_Status)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('Y'.$indexCounter, $mis->Decline_Reason)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('Z'.$indexCounter, $this->getrecruiterNameCBD($mis->employee_id))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('AA'.$indexCounter,$this->getrecruiterCatCBD($mis->employee_id))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('AB'.$indexCounter,$mis->vintage)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('AC'.$indexCounter,$mis->range_id)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				// $sheet->setCellValue('AD'.$indexCounter,$mis->employee_id)->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'AD'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:AD1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AD') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				// $logObj = new ExportDataLog();
				// $logObj->user_id =$request->session()->get('EmployeeId');
				// $logObj->download_date =date("Y-m-d");
				// $logObj->tilte ="CBD-Bank";					
				// $logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
			}
			
			
			public static function getAllMonthFileLogEIB(Request $request)
			{	  
			  $m = $request->m;
			  $y = $request->y;

			  $calendar_start_date = $y.'-'.$m.'-01';
			  $calendar_end_date = $y.'-'.$m.'-31';

			  $whereRaw = " calendar_date >='".$calendar_start_date."' and calendar_date <='".$calendar_end_date."'";

			  $getFileLog = DB::table('ENBD_import_file')->whereRaw($whereRaw)->orderBy('updated_at','DESC')->get();
			 

			  return view("Banks/ENBD/BankMIS/allMonthFileLogENBD",compact('getFileLog'));

			}
			
			public function searchJonusPhysiaclInner(Request $request)
			{
				$requestParameters = $request->input();

				$start_date_creation = '';
				$end_date_creation = '';
				$start_date_approval_bank = '';
				$end_date_approval_bank = '';
				$AECB_Status = '';
				$status = '';
				$ref_no = '';
				$employee_id = '';
				$sm_manager = '';
				$submission_type = '';

				if(@isset($requestParameters['app_no']))
				{
					$ref_no = @$requestParameters['app_no'];
				}

				if(isset($requestParameters['AECB_Status']))
				{
					$AECB_Status = @$requestParameters['AECB_Status'];
				}

				if(isset($requestParameters['status']))
				{
					$status = @$requestParameters['status'];
				}
				
				if(isset($requestParameters['employee_id']))
				{
					$employee_id = @$requestParameters['employee_id'];
				}
				if(isset($requestParameters['sm_manager']))
				{
					$sm_manager = @$requestParameters['sm_manager'];
				}

				if(isset($requestParameters['start_date_applicationJphysiacl']))
				{
					$start_date_creation = @$requestParameters['start_date_applicationJphysiacl'];
				}
				if(isset($requestParameters['end_date_applicationJonusPhysiacal']))
				{
					$end_date_creation = @$requestParameters['end_date_applicationJonusPhysiacal'];
				}
				
				if(isset($requestParameters['start_date_approval_bank']))
				{
					$start_date_approval_bank = @$requestParameters['start_date_approval_bank'];
				}
				if(isset($requestParameters['end_date_approval_bank']))
				{
					$end_date_approval_bank = @$requestParameters['end_date_approval_bank'];
				}
				
				if(isset($requestParameters['submission_type_inner_jonusPhysical']))
				{
					$submission_type = @$requestParameters['submission_type_inner_jonusPhysical'];
				}
				
				$request->session()->put('app_no_ENBD_jonusPhysical',$ref_no);
				$request->session()->put('AECB_Status_CBD_bank',$AECB_Status);
				$request->session()->put('status_CBD_bank',$status);
				$request->session()->put('employee_id_CBD_bank',$employee_id);
				$request->session()->put('smManager_CBD_bank',$sm_manager);
				$request->session()->put('start_date_application_JonusPhysical',$start_date_creation);
				$request->session()->put('end_date_application_JonusPhysiacal',$end_date_creation);
				
				$request->session()->put('start_date_approval_CBD_bank',$start_date_approval_bank);
				$request->session()->put('end_date_approval_CBD_bank',$end_date_approval_bank);
				$request->session()->put('master_cbd_search_bank','');
				$request->session()->put('submission_type_internal_jonusPhysical',$submission_type);
				return redirect("loadJonusENBDCardsContent");
			}
			
			public function resetJonusPhysiaclData(Request $request)
			{
				$request->session()->put('app_no_ENBD_jonusPhysical','');
				$request->session()->put('AECB_Status_CBD_bank','');
				$request->session()->put('status_CBD_bank','');
				$request->session()->put('start_date_application_JonusPhysical','');
				$request->session()->put('end_date_application_JonusPhysiacal','');
				$request->session()->put('start_date_approval_CBD_bank','');
				$request->session()->put('end_date_approval_CBD_bank','');
				$request->session()->put('employee_id_CBD_bank','');
				$request->session()->put('smManager_CBD_bank','');
				$request->session()->put('submission_type_internal_jonusPhysical','');
				$request->session()->put('master_cbd_search_bank',2);
				return redirect("loadJonusENBDCardsContent");
			}








































			public function searchJonusTabInner(Request $request)
			{
				$requestParameters = $request->input();

				$start_date_creation = '';
				$end_date_creation = '';
				$start_date_approval_bank = '';
				$end_date_approval_bank = '';
				$AECB_Status = '';
				$status = '';
				$ref_no = '';
				$employee_id = '';
				$sm_manager = '';
				$submission_type = '';

				if(@isset($requestParameters['app_no']))
				{
					$ref_no = @$requestParameters['app_no'];
				}

				if(isset($requestParameters['AECB_Status']))
				{
					$AECB_Status = @$requestParameters['AECB_Status'];
				}

				if(isset($requestParameters['status']))
				{
					$status = @$requestParameters['status'];
				}
				
				if(isset($requestParameters['employee_id']))
				{
					$employee_id = @$requestParameters['employee_id'];
				}
				if(isset($requestParameters['sm_manager']))
				{
					$sm_manager = @$requestParameters['sm_manager'];
				}

				if(isset($requestParameters['start_date_applicationjonusTab']))
				{
					$start_date_creation = @$requestParameters['start_date_applicationjonusTab'];
				}
				if(isset($requestParameters['end_date_applicationjonusTab']))
				{
					$end_date_creation = @$requestParameters['end_date_applicationjonusTab'];
				}
				
				if(isset($requestParameters['start_date_approval_bank']))
				{
					$start_date_approval_bank = @$requestParameters['start_date_approval_bank'];
				}
				if(isset($requestParameters['end_date_approval_bank']))
				{
					$end_date_approval_bank = @$requestParameters['end_date_approval_bank'];
				}
				
				if(isset($requestParameters['submission_type_inner_jonusTab']))
				{
					$submission_type = @$requestParameters['submission_type_inner_jonusTab'];
				}
				
				$request->session()->put('app_no_ENBD_jonusTab',$ref_no);
				$request->session()->put('AECB_Status_CBD_bank',$AECB_Status);
				$request->session()->put('status_CBD_bank',$status);
				$request->session()->put('employee_id_CBD_bank',$employee_id);
				$request->session()->put('smManager_CBD_bank',$sm_manager);
				$request->session()->put('start_date_application_jonusTab',$start_date_creation);
				$request->session()->put('end_date_application_jonusTab',$end_date_creation);
				
				$request->session()->put('start_date_approval_CBD_bank',$start_date_approval_bank);
				$request->session()->put('end_date_approval_CBD_bank',$end_date_approval_bank);
				$request->session()->put('master_cbd_search_bank','');
				$request->session()->put('submission_type_internal_jonusTab',$submission_type);
				return redirect("loadJonusTabENBDCards");
			}
			
			public function resetJonusTabData(Request $request)
			{
				$request->session()->put('app_no_ENBD_jonusTab','');
				$request->session()->put('AECB_Status_CBD_bank','');
				$request->session()->put('status_CBD_bank','');
				$request->session()->put('start_date_application_jonusTab','');
				$request->session()->put('end_date_application_jonusTab','');
				$request->session()->put('start_date_approval_CBD_bank','');
				$request->session()->put('end_date_approval_CBD_bank','');
				$request->session()->put('employee_id_CBD_bank','');
				$request->session()->put('smManager_CBD_bank','');
				$request->session()->put('submission_type_internal_jonusTab','');
				$request->session()->put('master_cbd_search_bank',2);
				return redirect("loadJonusTabENBDCards");
			}
			
			public function updateEmptoBankCBD(Request $request)
			{
				$cbdBankMod = CBDBankMis::whereNull("update_emp_status")->get();
				echo count($cbdBankMod);
				exit;
				foreach($cbdBankMod as $_cbd)
				{
					$ref_noBank = $_cbd->ref_no;
					
					$CBDInternalMis = DepartmentFormEntry::where("ref_no",$ref_noBank)->first();
					if($CBDInternalMis != '')
					{
						$cbdUpdate = CBDBankMis::find($_cbd->id);
						$cbdUpdate->employee_id =$CBDInternalMis->emp_id;
						$cbdUpdate->sm_manager =$CBDInternalMis->team;
						$cbdUpdate->update_emp_status =2;
						$cbdUpdate->save();
					}					
				}
				echo "done";
				exit;
			}
			
			
			public function updateBankCBDToInternal(Request $request)
			{
				$cbdInternalMod = DepartmentFormEntry::whereNull("cbd_update_status")->where("form_id",2)->get();
				/*  echo count($cbdInternalMod);
				 exit; */
				 foreach($cbdInternalMod as $_cbd)
					{
						$ref_noBank = $_cbd->ref_no;
						
						$CBDbankMIS = CBDBankMis::where("ref_no",$ref_noBank)->first();
						if($CBDbankMIS != '')
						{
							
							$cbdUpdate = DepartmentFormEntry::find($_cbd->id);
							$cbdUpdate->channel_cbd =$CBDbankMIS->Channel;
							if($_cbd->status_AECB_cbd == '' || $_cbd->status_AECB_cbd == NULL || $_cbd->status_AECB_cbd == '0' || $_cbd->status_AECB_cbd == '#N/A')
							{
								$cbdUpdate->status_AECB_cbd =$CBDbankMIS->AECB_Status;
							}
							$cbdUpdate->card_type_cbd =$CBDbankMIS->card_type;
							$cbdUpdate->cbd_update_status =2;
							$cbdUpdate->save();
						}					
					} 
				echo "done";
				exit;
			}
			
			public function updateInternalToChild(Request $request)
			{
				$cbdInternalMod = DepartmentFormEntry::where("cbd_update_status",2)->where("form_id",2)->get();
				 
				 foreach($cbdInternalMod as $_cbd)
					{
						$parentID = $_cbd->id;
						
						$updateAECD = DepartmentFormChildEntry::where("parent_id",$parentID)->where("attribute_code","aecb_status")->first();
						if($updateAECD != '')
						{
							$updateMe = DepartmentFormChildEntry::find($updateAECD->id);
							$updateMe->attribute_value = $_cbd->status_AECB_cbd;
							$updateMe->save();
						}	

						$update1 = DepartmentFormChildEntry::where("parent_id",$parentID)->where("attribute_code","channel_cbd")->first();
						if($update1 != '')
						{
							$updateMe1 = DepartmentFormChildEntry::find($update1->id);
							$updateMe1->attribute_value = $_cbd->channel_cbd;
							$updateMe1->save();
						}		
						$update2 = DepartmentFormChildEntry::where("parent_id",$parentID)->where("attribute_code","card_type_cbd")->first();
						if($update2 != '')
						{
							$updateMe2 = DepartmentFormChildEntry::find($update2->id);
							$updateMe2->attribute_value = $_cbd->card_type_cbd;
							$updateMe2->save();
						}								
					} 
				echo "done";
				exit;
			}
			
			
			
			protected function getrecruiterNameCBD($empid = NULL)
			{
				$recruiterMod = Employee_details::where("emp_id",$empid)->first();
				if($recruiterMod != '')
				{
					$recruiter = $recruiterMod->recruiter;
					$rdata = RecruiterDetails::where("id",$recruiter)->first();
				if($rdata != '')
				{
				 return $rdata->name;
					
				}
				else
				{
					return ''; 
				}
				}
				else
				{
					return ''; 
				}
			}
	
			protected function getrecruiterCatCBD($empid = NULL)
			{
				$recruiterMod = Employee_details::where("emp_id",$empid)->first();
				if($recruiterMod != '')
				{
					$recruiter = $recruiterMod->recruiter;
				$rdata = RecruiterDetails::where("id",$recruiter)->first();
				if($rdata != '')
				{
					$r = $rdata->recruit_cat;
					if($r != '' && $r != NULL)
					{
						return RecruiterCategory::where("id",$r)->first()->name;
					}
					else
					{
						return '';
					}
				}
				else
				{
					return ''; 
				}
				}
				else
				{
					return ''; 
				}
			}
			
			protected function getVintageCBD($empid = NULL)
			{
				$empmod =  Employee_details::where("emp_id",$empid)->first();
				if($empmod != '')
				{
					return $empmod->vintage_days; 
				}
				else
				{
					return ''; 
				}
			}
			protected function getRangeIdCBD($empid = NULL)
			{
				$empmod =  Employee_details::where("emp_id",$empid)->first();
				if($empmod != '')
				{
					return $empmod->range_id; 
				}
				else
				{
					return ''; 
				}
				
			}
			
public function updateRefNOFinal()
{
	
	$datas =  DepartmentFormEntry::whereNotNull("ref_no")->where("form_id",2)->get();
	
	foreach($datas as $data)
	{
		
		$childData = DepartmentFormChildEntry::where("parent_id",$data->id)->where("attribute_code","ref_no")->first();
		if($childData != '')
		{
			if(trim($data->ref_no) != trim($childData->attribute_value))
			{
			$updateChild = DepartmentFormChildEntry::find($childData->id);
			$updateChild->attribute_value = $data->ref_no;
			$updateChild->save();
			}
			
		}
		else
		{
			echo "exist";
			exit;
		}
	}
	echo "all done";
	exit;
}

public function updateApprovalRateInternal()
{
	$departmentMod = DepartmentFormEntry::where("form_id",2)->where("approval_update_status",1)->get();
	foreach($departmentMod as $mod)
	{
		
		$departUpdate = DepartmentFormEntry::find($mod->id);
		$departUpdate->approval_date = $mod->application_date;
		$departUpdate->approval_update_status = 2;
		$departUpdate->save();
		/*
		*update In child
		*/
		$childData = DepartmentFormChildEntry::where("parent_id",$mod->id)->where("attribute_code","approval_date_cbd")->first();
		if($childData != '')
		{
			if(trim($mod->application_date) != trim($childData->attribute_value))
			{
			$updateChild = DepartmentFormChildEntry::find($childData->id);
			$updateChild->attribute_value = $mod->application_date;
			$updateChild->save();
			}
			
		}
		else
		{
			$addChild = new DepartmentFormChildEntry();
			$addChild->attribute_value = $mod->application_date;
			$addChild->attribute_code = 'approval_date_cbd';
			$addChild->parent_id = $mod->id;
			$addChild->status = 1;
			$addChild->form_id = 2;
			$addChild->save();
		}
		/*
		*update In child
		*/
	}
	
	echo "done";
	exit;
}

public function updateApprovalRateBank()
{
	
	$bankMod = CBDBankMis::whereNull("approval_status")->get();
	/* echo count($bankMod);
	exit; */
	foreach($bankMod as $mod)
	{
		$updateBank = CBDBankMis::find($mod->id);
		$updateBank->approval_date = $mod->creation_date;
		$updateBank->approval_status = 2;
		$updateBank->save();
	}
	echo "done";
	exit;
	
}

public function currentMonthCount()
{
	echo "not execute now";
	exit;
	//$masterPayoutDetails = MasterPayoutPre::get();
	$start_date = date("Y-m-d");
	$previousdateMissingEmpDateFormat =  date('Y-m-d', strtotime($start_date." -1 month"));
	$previousdateMissingEmpDateFormatMonth = date("m",strtotime($previousdateMissingEmpDateFormat));
	$previousdateMissingEmpDateFormatYear = date("Y",strtotime($previousdateMissingEmpDateFormat));
	$d=date("t",strtotime($previousdateMissingEmpDateFormat));
	$start_date_application_CBD_internal = '01-'.$previousdateMissingEmpDateFormatMonth.'-'.$previousdateMissingEmpDateFormatYear;
	$end_date_application_CBD_internal = $d.'-'.$previousdateMissingEmpDateFormatMonth.'-'.$previousdateMissingEmpDateFormatYear;
	/* echo $start_date_application_CBD_internal;
	echo "<br />";
	echo $end_date_application_CBD_internal;
	exit; */
	/*
	*submission added Agent
	*start code
	*/
/* 	echo (int)$previousdateMissingEmpDateFormatMonth;exit; */
	$whereRawBankCarryForward = "approval_date >='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'";
	$whereRawBankCarryForward .= " AND approval_date <='".date('Y-m-d',strtotime($end_date_application_CBD_internal))."'";
	
	 $collectionModelMissing = DepartmentFormEntry::selectRaw('emp_id,team')
												  ->groupBy('emp_id')
												  ->whereRaw($whereRawBankCarryForward)
												 
												  ->where("form_id",2)
												  ->get();
				
				
				foreach($collectionModelMissing as $missing)
				{
					
					$totalBankBooking = CBDBankMis::select("id")->where("employee_id",$missing->emp_id)->whereIn("Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))->whereRaw($whereRawBankCarryForward)->get()->count();
					
					$objCreate = new MasterPayoutPre();
					$objCreate->agent_product = 'Card';
					$objCreate->agent_id = $missing->emp_id;
					$objCreate->TL = $missing->team;
					$objCreate->agent_name = $this->getEmployeeName($missing->emp_id);
					$objCreate->sales_time = (int)$previousdateMissingEmpDateFormatMonth.'-'.$previousdateMissingEmpDateFormatYear;
					$objCreate->dept_id = 49;
					$objCreate->bank_name = 'CBD';
					$objCreate->tc = $totalBankBooking;
					$objCreate->save();
				}
	/*
	*submission added Agent
	*end code
	*/
	echo "done";
	exit;
}


public function calculateVintagePrePayout()
			{
				$masterPayoutMod = MasterPayoutPre::where("vintage_status",1)->get();
				/*  echo '<pre>';
				print_r($agentPayoutMod);
				exit;  */   
				$vintageArray = array();
				foreach($masterPayoutMod as $payout)
				{
					$agent_id = trim($payout->agent_id);
					if($agent_id != '' && $agent_id != NULL)
					{
						$employeeData = Employee_details::where("emp_id",$agent_id)->first();
						if($employeeData != '')
						{
							$empId = $employeeData->emp_id;
							$deptId = $employeeData->dept_id;
							$empAttr = Employee_attribute::where("emp_id",$empId)->where("attribute_code","DOJ")->first();
							if($empAttr != '')
							{
								$salesTime = $payout->sales_time;
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
									$agentPUpdate = MasterPayoutPre::find($payout->id);
									$agentPUpdate->vintage = $daysInterval;
									$agentPUpdate->doj = date("Y-m-d",strtotime($doj));
									$agentPUpdate->vintage_status = 2;
									$agentPUpdate->save();
									
								}
							}								
							
						}
					}
					
				}
				echo "done";
				exit;
			}
			
			
			public function UpdateTimeRangePrePayout(){
			$data=WorkTimeRange::get();
			foreach($data as $_time){
					$range=$_time->range;
					$rangedata=explode('-',$range);
					//print_r($rangedata);

					$whereraw='vintage >='.$rangedata[0].' and vintage <='.$rangedata[1].'';
					$PayoutData =MasterPayoutPre::whereRaw($whereraw)->get();
					foreach($PayoutData as $_newdata){
						$updateMod = MasterPayoutPre::find($_newdata->id);
						$updateMod->range_id=$_time->id;
						$updateMod->save();
					}
					
				
			}
			
			}
			
public function UpdateTargetPrePayout()
{
	$data = MasterPayoutPre::where("target_status",1)->get();
	foreach($data as $agent)
	{
		$checkForTarget = MasterPayout::where("employee_id",$agent->agent_id)->orderBy("sort_order","DESC")->first();
		if($checkForTarget != '')
		{
			$updateObj = MasterPayoutPre::find($agent->id);
			$updateObj->agent_target = $checkForTarget->agent_target;
			$updateObj->target_status = 2;
			$updateObj->save();
		}
		else
		{
			$updateObj = MasterPayoutPre::find($agent->id);
		
			$updateObj->target_status = 3;
			$updateObj->save();
		}
	}
	echo "done";
	exit;
}






















	public function FileUploadExcelENBDDataCutimport(Request $request)
	{
					$user_id = $request->session()->get('EmployeeId');
					
							//echo '<pre>';
							//print_r($request->uploadedDate);exit;
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
					$fileType = $request->fileType;
					$fileName = 'ENBD_DataCut_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

						$request->file->move(public_path('uploads/ENBDDataCut/'), $fileName);
						$spreadsheet = new Spreadsheet();

						$inputFileType = 'Xlsx';
						$inputFileName = '/srv/www/htdocs/hrm/public/uploads/ENBDDataCut/'.$fileName;

						/*  Create a new Reader of the type defined in $inputFileType  */
						$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
						/*  Advise the Reader that we only want to load cell data  */
						$reader->setReadDataOnly(true);
						$spreadsheet = $reader->load($inputFileName);
						$worksheet = $spreadsheet->getActiveSheet();
						// Get the highest row number and column letter referenced in the worksheet
						$highestRow = $worksheet->getHighestRow()-1; // e.g. 10							

						
						
						$tableName = 'ENBD_import_file';

					$values = array('user_id'=>$user_id,'file_name' => $fileName,'totalcount' => $highestRow,'calendar_date' => $request->uploadedDate,'type'=>1);
					$filenameID = DB::table($tableName)->insertGetId($values);

					   $response['code'] = '200';
					   $response['message'] = "You have successfully upload file.";
					   $response['filename'] = $fileName;
					   $response['filenameID'] = $filenameID;
					   $response['totalcount'] = $highestRow;
					/* } */
					   echo json_encode($response);
					   exit;
					
	}






				public function ENBDDataCutFileImport(Request $request)
				{	
			
					$user_id = $request->session()->get('EmployeeId');
					$result = array();
					$attr_f_import = $request->attr_f_import; 
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('ENBD_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/ENBDDataCut/';
					$fullpathFileName = $uploadPath . $filename;
				
					 $spreadsheet = new Spreadsheet();

					$inputFileType = 'Xlsx';
					//$inputFileName = '/srv/www/htdocs/hrm/public/uploads/misImport/excel/'.$fileName;

					/*  Create a new Reader of the type defined in $inputFileType  */
					$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
					/*  Advise the Reader that we only want to load cell data  */
					$reader->setReadDataOnly(false); /// For date format issue
					$spreadsheet = $reader->load($fullpathFileName);
					$sheetData = $spreadsheet->getActiveSheet()->toArray();
					
					//$data=$sheetData->getIndex();
					//$data=$sheetData->getRowIterator($conter);
					// echo "<pre>";
					// print_r($sheetData);//exit;

					// if(!empty($sheetData))
					// {
					// 	echo "Sample";
					// }
					// else
					// {
					// 	echo "Hello";
					// }
					//exit;


						
					if(!empty($sheetData))
					{
						for($k=0;$k<count($sheetData);$k++)
						{
							
							if(count($sheetData[$k])!= 13)
							{
								$fileInfo = DB::table('ENBD_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}	
												
							
							$file_values = array(
												'agency' => trim($sheetData[$k][1]),
												'card_type' => trim($sheetData[$k][3]),
												'product' => trim($sheetData[$k][4]),
												'ale_nale' => trim($sheetData[$k][5]),
												'company_name' => trim($sheetData[$k][6]),
												'customer_name' => trim($sheetData[$k][7]),
												'mobile' => trim($sheetData[$k][8]),
												'p1_code' => trim($sheetData[$k][9]),
												'rbe_name' => trim($sheetData[$k][10]),
												'issued_date' => ($sheetData[$k][11]?date('Y-m-d',strtotime($sheetData[$k][11])):'0000-00-00'),
												'region' => trim($sheetData[$k][12]),
												'created_at' => date('Y-m-d H:i:s'),
												
												//'application_created_at' => ($sheetData[$k][4]?date('Y-m-d',strtotime($sheetData[$k][4])):'0000-00-00'),												
												
												//'created_at' => date('Y-m-d H:i:s'),
												//'application_created_at' => date('Y-m-d H:i:s'),	
												
												);
								
								//  echo "<pre>";
								// print_r($file_values);
								// exit; 							
							
							 $application_number = trim($sheetData[$k][2]);
							$whereRaw = "application_no ='".$application_number."'";
							$check = DB::table('ENBD_DataCut')->whereRaw($whereRaw)->get();

							// echo "<pre>";
							// print_r($check);
							// exit;

							if(count($check)>0)
							{			
								$getdatacbd_marging_status = DB::table('ENBD_DataCut')->whereRaw($whereRaw)->first();
								// if($getdatacbd_marging_status == 2)
								// {
								// 	$file_values['update_status'] = 1;
								// }
								DB::table('ENBD_DataCut')->where('application_no', $application_number)->update($file_values);
								
							}
							else
							{
								
								$all_values = $file_values;
								$all_values['application_no'] = $application_number;
								//$all_values['cbd_marging_status'] = 1;
							
								
								DB::table('ENBD_DataCut')->insert($all_values);
								
								// $singlepost = EibBankMis::where('application_number','Application Number')->first();
								// $singlepost->delete();

								
							} 
						}
						
						$result['code'] = 200;
					
					}
					else
					{
						$result['code'] = 300;
					}
				
				$request->session()->flash('success','Import Completed.');
				echo json_encode($result);
				exit;
				
			}




			public function searchInnerDataCutFilter(Request $request)
			{
				$requestParameters = $request->input();

				$start_date_creation = '';
				$end_date_creation = '';
				$start_date_approval_bank = '';
				$end_date_approval_bank = '';
				$AECB_Status = '';
				$status = '';
				$ref_no = '';
				$employee_id = '';
				$sm_manager = '';
				$submission_type = '';

				if(@isset($requestParameters['app_noDataCut']))
				{
					$ref_no = @$requestParameters['app_noDataCut'];
				}

				if(isset($requestParameters['AECB_Status']))
				{
					$AECB_Status = @$requestParameters['AECB_Status'];
				}

				if(isset($requestParameters['status']))
				{
					$status = @$requestParameters['status'];
				}
				
				if(isset($requestParameters['employee_id']))
				{
					$employee_id = @$requestParameters['employee_id'];
				}
				if(isset($requestParameters['sm_manager']))
				{
					$sm_manager = @$requestParameters['sm_manager'];
				}

				if(isset($requestParameters['start_date_DataCut']))
				{
					$start_date_creation = @$requestParameters['start_date_DataCut'];
				}
				if(isset($requestParameters['end_date_DataCut']))
				{
					$end_date_creation = @$requestParameters['end_date_DataCut'];
				}
				
				if(isset($requestParameters['start_date_approval_bank']))
				{
					$start_date_approval_bank = @$requestParameters['start_date_approval_bank'];
				}
				if(isset($requestParameters['end_date_approval_bank']))
				{
					$end_date_approval_bank = @$requestParameters['end_date_approval_bank'];
				}
				
				if(isset($requestParameters['submission_type_inner_data']))
				{
					$submission_type = @$requestParameters['submission_type_inner_data'];
				}
				
				$request->session()->put('app_no_ENBD_DataCut',$ref_no);
				$request->session()->put('AECB_Status_CBD_bank',$AECB_Status);
				$request->session()->put('status_CBD_bank',$status);
				$request->session()->put('employee_id_CBD_bank',$employee_id);
				$request->session()->put('smManager_CBD_bank',$sm_manager);
				$request->session()->put('start_date_application_DataCut',$start_date_creation);
				$request->session()->put('end_date_application_DataCut',$end_date_creation);
				
				$request->session()->put('start_date_approval_CBD_bank',$start_date_approval_bank);
				$request->session()->put('end_date_approval_CBD_bank',$end_date_approval_bank);
				$request->session()->put('master_cbd_search_bank','');
				$request->session()->put('submission_type_internal_datacut',$submission_type);
				return redirect("loadENBDDataCutContent");
			}
			
			public function resetInnerDataCutFilter(Request $request)
			{
				$request->session()->put('app_no_ENBD_DataCut','');
				$request->session()->put('AECB_Status_CBD_bank','');
				$request->session()->put('status_CBD_bank','');
				$request->session()->put('start_date_application_DataCut','');
				$request->session()->put('end_date_application_DataCut','');
				$request->session()->put('start_date_approval_CBD_bank','');
				$request->session()->put('end_date_approval_CBD_bank','');
				$request->session()->put('employee_id_CBD_bank','');
				$request->session()->put('smManager_CBD_bank','');
				$request->session()->put('submission_type_internal_datacut','');
				$request->session()->put('master_cbd_search_bank',2);
				return redirect("loadENBDDataCutContent");
			}

















// Export Agent Performance Module Start

public function exportAgentPerformanceDataENBDBank(Request $request)
{
		
	
	
		$start_date_application_SCB_internal = '';
		$end_date_application_SCB_internal = '';
		$whereRaw = 'form_id = 7';
		$whereRawBank = "application_no != ''";
		//echo $request->session()->get('start_date_application_ENBD_master');
		//exit;
	
		if($request->session()->get('start_date_application_ENBD_master') != '')
		{
			
			$start_date_application_SCB_internal = $request->session()->get('start_date_application_ENBD_master');	
			//exit;		
			$whereRaw .= " AND submission_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			$whereRawBank .= " AND submission_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			
		}
	    else
		{
			
			$start_date_application_SCB_internal = date("Y")."-".date("m")."-01";			
			$whereRaw .= " AND submission_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			$whereRawBank .= " AND submission_date >='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."'";
			
		}
		if($request->session()->get('end_date_application_ENBD_master') != '')
		{
			
			$end_date_application_SCB_internal = $request->session()->get('end_date_application_ENBD_master');			
			$whereRaw .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			$whereRawBank .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			
		}	
		else
		{
			
			$end_date_application_SCB_internal = date("Y-m-d");	
			$whereRaw .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			$whereRawBank .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_application_SCB_internal))."'";
			
		}
		// echo $start_date_application_SCB_internal;
		// echo "<pre>";
		// echo $end_date_application_SCB_internal;
		//exit; 
			/*
			*-1,-2 month Name
			*start code
			*/

			// $endDate = '2024-08-30';
			// $startDate = '2024-06-01';
			
			// $whereRawBank = "application_no != '' AND submission_date >='$startDate' AND submission_date <='$endDate'";


			//$whereRawBank='';
			// echo $whereRawBank;
			// echo $whereRaw;
			// exit;
			$previousMonthName =  date('M-Y', strtotime(date($start_date_application_SCB_internal)." -1 month"));
			$previousMonthName1 =  date('M-Y', strtotime(date($start_date_application_SCB_internal)." -2 month"));
			/*
			*-1,-2 month Name
			*end code
			*/
			// $collectionModel = ENBDDepartmentFormEntry::selectRaw('count(*) as total, emp_id,tl_name,vintage,range_id,doj,agent_code')
			// 									  ->groupBy('emp_id')
			// 									  ->whereRaw($whereRaw)
			// 									  ->get();



			$collectionModel = ENBDDepartmentFormEntry::selectRaw('count(*) as total, emp_id,tl_name')
												  ->groupBy('emp_id')
												  ->whereRaw($whereRaw)
												  ->get();


			// print_r($collectionModel);
			// die;
		
		    $filename = 'Agent_performance_ENBD_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:R2');
			$sheet->setCellValue('Q1', 'Agents Performance ENBD Cards - from -'.date("d M Y",strtotime($start_date_application_SCB_internal)).'to -'.date("d M Y",strtotime($end_date_application_SCB_internal)))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$indexCounter = 5;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Agent Emp Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Agent name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('SM Manager'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Total Submissions'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Total Booking As Per Bank MIS'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName.')'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName1.')'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Recruiter Name'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Recruiter Category'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Vintage'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Range Id'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Designation'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('T-1 Submissions'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('T-2 Submissions'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Agent Salary'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('SUBMISSION TO BOOKING'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('DOJ'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			$empMoreThanZeroSubmission = array();
			$totalSubmission = 0;
			$totalBookingBank = 0;
			$totalBookingMTD = 0;
			$totalLastBooking = 0;
			$totalLastBookingP = 0;
			$totalBooking = 0;
			$t1Total = 0;
			$t2Total = 0;
			$usedEmp = array();
			$totalNotCaptured = 0;
			foreach ($collectionModel as $model) {
				if($model->emp_id != '')
				{
					$usedEmp[] = $model->emp_id; 
					$vintageDays = '-';
					$empAttr = Employee_attribute::where("emp_id",$model->emp_id)->where("attribute_code","DOJ")->first();
					if($empAttr != '')
						{
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									$doj = date("Y-m-d",strtotime($doj));
									$vintageDays = abs(strtotime($end_date_application_SCB_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
					$empMoreThanZeroSubmission[] = $model->emp_id;
					$totalBankBooking = ENBDDepartmentFormEntry::select("id")->where("emp_id",$model->emp_id)->whereIn("status",array("COMPLETED"))->whereRaw($whereRawBank)->get()->count();
					$indexCounter++;
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $model->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $this->getEmployeeName($model->emp_id))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $model->tl_name)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $model->total)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $totalBankBooking)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBooking($model->emp_id,$start_date_application_SCB_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->lastMonthBookingP($model->emp_id,$start_date_application_SCB_internal))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $this->getrecruiterNameSCB($model->emp_id))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $this->getrecruiterCatSCB($model->emp_id))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $vintageDays)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->getDesignation($model->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $this->t1Submissions($model->emp_id))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->t2Submissions($model->emp_id))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter, $this->getAgentSalary($model->emp_id))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+$model->total;
					$totalBookingBank = $totalBookingBank+$totalBankBooking;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($model->emp_id,$start_date_application_SCB_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingP($model->emp_id,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1Submissions($model->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($model->emp_id);
					
						$totalBooking = $totalBooking+$totalBankBooking;
					
					
					$sheet->setCellValue('Q'.$indexCounter,$this->getApprovalRate($model->total,$totalBankBooking))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					$sheet->setCellValue('R'.$indexCounter,$model->doj)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
				}
			}
			/*
			*adding Sales Agent with zero Submission
			*Start Coding
			*/
				$empwithZeroSubmission = Employee_details::where("dept_id",52)
								->whereNotIn("emp_id",$empMoreThanZeroSubmission)
								->where("job_function",2)
								->get();
				
				foreach($empwithZeroSubmission as $zeroSubmission)
				{
					if($zeroSubmission->offline_status != 1)
					{
						
					$offlineEmp = DB::table('offline_empolyee_details')->whereRaw("emp_id='".$zeroSubmission->emp_id."' AND last_working_day_resign>='".date('Y-m-d',strtotime($start_date_application_SCB_internal))."' AND last_working_day_resign IS NOT NULL")->get();
					/*
					*check Emp exist in last submission
					*/
					$previousdate =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -1 month"));
					$pYear = date("Y",strtotime($previousdate));
					$pMonth = date("m",strtotime($previousdate));
					$startDate = $pYear."-".$pMonth."-01";
					$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
					$endDate = $pYear."-".$pMonth."-".$d;
					$totalBankBooking = ENBDDepartmentFormEntry::select("id")->where("emp_id",$zeroSubmission->emp_id)->whereIn("status",array("COMPLETED"))->whereBetween("submission_date",[$startDate,$endDate])->get()->count();
					if($totalBankBooking >0)
					{
						$offlineEmp = DB::table('offline_empolyee_details')->where("emp_id",$zeroSubmission->emp_id)->get();
					}
					/*
					*check Emp exist in last submission
					*/
					if(count($offlineEmp)>0)
					{
						$usedEmp[] = $zeroSubmission->emp_id;
						$vintageDays = '-';
					$doj = '';
					$empAttr = Employee_attribute::where("emp_id",$zeroSubmission->emp_id)->where("attribute_code","DOJ")->first();
					if($empAttr != '')
						{
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									$doj = date("Y-m-d",strtotime($doj));
									$vintageDays = abs(strtotime($end_date_application_SCB_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
						if(strtotime($doj) <= strtotime($end_date_application_SCB_internal) && $doj != '')
						{
					$indexCounter++;
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $zeroSubmission->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $zeroSubmission->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $this->getTLName($zeroSubmission->emp_id))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, 0)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, 0)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_SCB_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_SCB_internal))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $this->getrecruiterNameSCB($zeroSubmission->emp_id))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $this->getrecruiterCatSCB($zeroSubmission->emp_id))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $vintageDays)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->getDesignation($zeroSubmission->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $this->t1Submissions($zeroSubmission->emp_id))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->t2Submissions($zeroSubmission->emp_id))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter, $this->getAgentSalary($zeroSubmission->emp_id))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+0;
					$totalBookingBank = $totalBookingBank+0;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_SCB_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1Submissions($zeroSubmission->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($zeroSubmission->emp_id);	
					$sheet->setCellValue('Q'.$indexCounter,"0")->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
				
					$sheet->setCellValue('R'.$indexCounter,$zeroSubmission->doj)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						
						
					$totalBooking = $totalBooking+0;
					
					}
					else
					{
						continue;
					}
				
					}
					}
					else
					{
						$vintageDays = '-';
					$doj = '';
					$empAttr = Employee_attribute::where("emp_id",$zeroSubmission->emp_id)->where("attribute_code","DOJ")->first();
					if($empAttr != '')
						{
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									$doj = date("Y-m-d",strtotime($doj));
									$vintageDays = abs(strtotime($end_date_application_SCB_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
						if(strtotime($doj) <= strtotime($end_date_application_SCB_internal) && $doj != '')
						{
							$usedEmp[] = $zeroSubmission->emp_id;
					$indexCounter++;
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $zeroSubmission->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $zeroSubmission->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $this->getTLName($zeroSubmission->emp_id))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, 0)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, 0)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_SCB_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_SCB_internal))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $this->getrecruiterNameSCB($zeroSubmission->emp_id))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $this->getrecruiterCatSCB($zeroSubmission->emp_id))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $vintageDays)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->getDesignation($zeroSubmission->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $this->t1Submissions($zeroSubmission->emp_id))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->t2Submissions($zeroSubmission->emp_id))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter, $this->getAgentSalary($zeroSubmission->emp_id))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+0;
					$totalBookingBank = $totalBookingBank+0;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_SCB_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1Submissions($zeroSubmission->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($zeroSubmission->emp_id);	
					$sheet->setCellValue('Q'.$indexCounter,"0")->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					$sheet->setCellValue('R'.$indexCounter,$zeroSubmission->doj)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$totalBooking = $totalBooking+0;
						}
						
						
						
					
						
					}
					
			
				}
				
				
				$previousdateMissingEmp =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -1 month"));
				$pYearMissing = date("Y",strtotime($previousdateMissingEmp));
				$pMonthMissing = date("m",strtotime($previousdateMissingEmp));
				$startDateMissing = $pYearMissing."-".$pMonthMissing."-01";
				
				
				 $collectionModelMissing = ENBDDepartmentFormEntry::selectRaw('emp_id,tl_name')
												  ->groupBy('emp_id')
												  ->whereDate('submission_date', '>=', $startDateMissing)
												  ->whereNotIn('emp_id',$usedEmp)
												 
												  ->get();
												  
				foreach($collectionModelMissing as $missing)
				{
				$vintageDays = '-';
					$doj = '';
					$empAttr = Employee_attribute::where("emp_id",$missing->emp_id)->where("attribute_code","DOJ")->first();
					if($empAttr != '')
						{
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									$doj = date("Y-m-d",strtotime($doj));
									$vintageDays = abs(strtotime($end_date_application_SCB_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
				$indexCounter++;
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $missing->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $missing->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $this->getTLName($missing->emp_id))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, 0)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, 0)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBooking($missing->emp_id,$start_date_application_SCB_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->lastMonthBookingP($missing->emp_id,$start_date_application_SCB_internal))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $this->getrecruiterNameSCB($missing->emp_id))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $this->getrecruiterCatSCB($missing->emp_id))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $vintageDays)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->getDesignation($missing->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $this->t1Submissions($missing->emp_id))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->t2Submissions($missing->emp_id))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter, $this->getAgentSalary($missing->emp_id))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+0;
					$totalBookingBank = $totalBookingBank+0;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($missing->emp_id,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1Submissions($missing->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($missing->emp_id);	
					$sheet->setCellValue('Q'.$indexCounter,"0")->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					$sheet->setCellValue('R'.$indexCounter,$missing->doj)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$totalBooking = $totalBooking+0;

				}					
			/*
			*adding Sales Agent with zero Submission
			*Start Coding
			*/
			$indexCounter = $indexCounter+2;
			$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter.':R'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			$sheet->setCellValue('C'.$indexCounter, "Total")->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, $totalSubmission)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, $totalBookingBank)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, $totalLastBooking)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, $totalLastBookingP)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, $t1Total)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, $t2Total)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			 
			$approvalRateALL =  @round(($totalBooking/$totalSubmission),2);
		
			$sheet->setCellValue('Q'.$indexCounter,$approvalRateALL)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			
			for($col = 'A'; $col !== 'R'; $col++) {
					$sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
					$spreadsheet->getActiveSheet()->getStyle('A1:R2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
					
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','R') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$spreadsheet->getActiveSheet()->setTitle('Agent Reports');
				$spreadsheet->createSheet(1); 
				$spreadsheet->setActiveSheetIndex(1); 
				$spreadsheet->getActiveSheet()->setTitle('TL Reports'); 
				/*
				*Sheet2
				*/
				$this->sheet2Performance($spreadsheet,$whereRaw,$whereRawBank,$start_date_application_SCB_internal,$end_date_application_SCB_internal);
				$spreadsheet->createSheet(2); 
				$spreadsheet->setActiveSheetIndex(2); 
				$spreadsheet->getActiveSheet()->setTitle('Flag Details');
				/*
				*Sheet3
				*/
				$this->sheet3FlagDetails($spreadsheet,$start_date_application_SCB_internal,$end_date_application_SCB_internal);
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="SCB-Final-Report";					
				$logObj->save();
					$writer = new Xlsx($spreadsheet);
					$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
}


public function getTLName($empId)
{
		$empDetailsModel = Employee_details::select("tl_id")->where("emp_id",$empId)->first();
		if($empDetailsModel != '')
		{
			$tlID = $empDetailsModel->tl_id;
			if($tlID != '' && $tlID != NULL)
			{
				$empTlName = Employee_details::select("export_name")->where("id",$tlID)->first()->export_name;

				if($empTlName!='')
				{
					return $empTlName;
				}
				else
				{
					$empDetailsData = Employee_details::where("id",$tlID)->where("job_function",3)->first();

					if($empDetailsData)
					{
						return $empDetailsData->emp_name;
					}
					else
					{
						return "--";
					}
				}
				
			}
			else
			{
				return "-";
			}
		}
		else
		{
			return "-";
		}

		
}


protected function lastMonthBooking($empId,$start_date_application_SCB_internal)
{
		
		$previousdate =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -1 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		$saleEnd = $pMonth.'-'.$pYear;
		
		/*
		*check master payout first
		*/
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",52)->where("sales_time",$saleEnd)->where("employee_id",$empId)->first();
		if($employeePayoutData != '')
		{
		
			return $employeePayoutData->tc;
		/*
		*check master payout first
		*/		
		}
		else
		{
		/* $previousMonthPayout = date("m-Y",strtotime($start_date_application_SCB_internal." -1 month"));
		
		$employeePayoutDataCount = MasterPayout::select("id")->where("dept_id",47)->where("sales_time",$previousMonthPayout)->get()->count();
		if($employeePayoutDataCount > 0)
		{
			return 0;
		} */
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		/* echo $startDate;
		echo "<br />";
		echo $endDate;
		exit;	 */	
		$totalBankBooking = ENBDDepartmentFormEntry::select("id")->where("emp_id",$empId)->whereIn("status",array("COMPLETED"))->whereBetween("submission_date",[$startDate,$endDate])->get()->count();
		return 	$totalBankBooking;	
		}
		
}
	
	
	protected function lastMonthBookingP($empId,$start_date_application_SCB_internal)
	{
		
		$previousdate =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -2 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		$saleEnd = $pMonth.'-'.$pYear;
		
		/*
		*check master payout first
		*/
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",52)->where("sales_time",$saleEnd)->where("employee_id",$empId)->first();
		if($employeePayoutData != '')
		{
		
			return $employeePayoutData->tc;
		/*
		*check master payout first
		*/		
		}
		else
		{
		/* $previousMonthPayout = date("m-Y",strtotime($start_date_application_SCB_internal." -1 month"));
		
		$employeePayoutDataCount = MasterPayout::select("id")->where("dept_id",47)->where("sales_time",$previousMonthPayout)->get()->count();
		if($employeePayoutDataCount > 0)
		{
			return 0;
		} */
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		/* echo $startDate;
		echo "<br />";
		echo $endDate;
		exit;	 */	
		$totalBankBooking = ENBDDepartmentFormEntry::select("id")->where("emp_id",$empId)->whereIn("status",array("COMPLETED"))->whereBetween("submission_date",[$startDate,$endDate])->get()->count();
		return 	$totalBankBooking;	
		}
		
	}


	protected function getrecruiterNameSCB($empid = NULL)
	{
		$recruiterMod = Employee_details::where("emp_id",$empid)->first();
		if($recruiterMod != '')
		{
			$recruiter = $recruiterMod->recruiter;
			$rdata = RecruiterDetails::where("id",$recruiter)->first();
		if($rdata != '')
		{
			return $rdata->name;
			
		}
		else
		{
			return ''; 
		}
		}
		else
		{
			return ''; 
		}
	}
	
	protected function getrecruiterCatSCB($empid = NULL)
	{
		$recruiterMod = Employee_details::where("emp_id",$empid)->first();
		if($recruiterMod != '')
		{
			$recruiter = $recruiterMod->recruiter;
		$rdata = RecruiterDetails::where("id",$recruiter)->first();
		if($rdata != '')
		{
			$r = $rdata->recruit_cat;
			if($r != '' && $r != NULL)
			{
				return RecruiterCategory::where("id",$r)->first()->name;
			}
			else
			{
				return '';
			}
		}
		else
		{
			return ''; 
		}
		}
		else
		{
			return ''; 
		}
	}



	protected function getRangeIdData($vintageDays)
	{
		if($vintageDays < 711 )
		{
			if($vintageDays != '' && $vintageDays != NULL)
			{
				return RangeDetailsVintage::where("vintage",$vintageDays)->first()->range_id;
			}
			else
			{
				return "-";
			}
		}
		else
		{
			return '25';
		}
	}
			
			public function getDesignation($empId)
			{
				$empDetailsModel = Employee_details::select("designation_by_doc_collection")->where("emp_id",$empId)->first();
				if($empDetailsModel != '')
				{
					$empdesignationId = $empDetailsModel->designation_by_doc_collection;
					if($empdesignationId != '' && $empdesignationId != NULL)
					{
						$designationMod = Designation::select("name")->where("id",$empdesignationId)->first();
						if($designationMod != '')
						{
							return $designationMod->name;
						}
						else
						{
							return "-";
						}
					}
					else
					{
						return "-";
					}
				}
				else
				{
					return "-";
				}
			}


	protected function t1Submissions($empId)
	{
		$previousDate =  date('Y-m-d', strtotime(' -1 day'));
		return ENBDDepartmentFormEntry::select("id")->whereDate("submission_date","=",$previousDate)->where("emp_id",$empId)->get()->count();
		
	}
	protected function t2Submissions($empId)
	{
		$endDate =  date('Y-m-d', strtotime(' -1 day'));
		$StartDate =  date('Y-m-d', strtotime(' -2 day'));
		return ENBDDepartmentFormEntry::select("id")->whereBetween("submission_date",[$StartDate,$endDate])->where("emp_id",$empId)->get()->count();
		
	}

	protected function getAgentSalary($empId)
	{
		$empDetailsModel = Employee_attribute::select("attribute_values")->where("emp_id",$empId)->where("attribute_code","total_gross_salary")->first();
			if($empDetailsModel != '')
			{
				$basic_salary = $empDetailsModel->attribute_values;
				if($basic_salary != '' && $basic_salary != NULL)
				{
					return $basic_salary;
				}
				else
				{
					return 0;
				}
			}
			else
			{
				return 0;
			}
	}

	protected function getApprovalRate($totalSubmission,$booking)
	{
		
			if($totalSubmission <= 0)
			{
				return 0;
			}
			else
			{
			return round(($booking/$totalSubmission),2);
			}
		
	}




	protected function sheet2Performance($spreadsheet,$whereRaw,$whereRawBank,$start_date_application_SCB_internal,$end_date_application_SCB_internal)
	{
		$previousMonthName =  date('M-Y', strtotime(date($start_date_application_SCB_internal)." -1 month"));
		$previousMonthName1 =  date('M-Y', strtotime(date($start_date_application_SCB_internal)." -2 month"));
			
		$collectionModel = ENBDDepartmentFormEntry::selectRaw('count(*) as total,tl_name')
												->groupBy('tl_name')
												->whereRaw($whereRaw)
												->get();


		// echo "<pre>";
		// print_r($collectionModel);
		// exit;




		$sheet = $spreadsheet->getActiveSheet();
		$sheet->mergeCells('A1:H2');
		$sheet->setCellValue('A1', 'TL Performance EIB Cards - from -'.date("d M Y",strtotime($start_date_application_SCB_internal)).' to -'.date("d M Y",strtotime($end_date_application_SCB_internal)))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			
		$indexCounter = 5;
		$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('B'.$indexCounter, strtoupper('SM Manager'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('C'.$indexCounter, strtoupper('Total Submissions'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('D'.$indexCounter, strtoupper('Total Booking As Per Bank MIS'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('E'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName.')'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('F'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName1.')'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('G'.$indexCounter, strtoupper('T-1 Submissions'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		$sheet->setCellValue('H'.$indexCounter, strtoupper('T-2 Submissions'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		
		$sheet->setCellValue('I'.$indexCounter, strtoupper('Submission to Booking'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');


		/*
				*Sheet2
				*/
				$sn = 1;
			$empMoreThanZeroSubmission = array();
			$totalSubmission = 0;
			$totalBookingBank = 0;
			$totalBookingMTD = 0;
			$totalLastBooking = 0;
			$totalLastBookingP = 0;
			$totalBooking = 0;
			$t1Total = 0;
			$t2Total = 0;
			$teamValue = array();
				foreach ($collectionModel as $model) 
				{
					if($model->tl_name != '')
					{
					
						
						$teamValue[] = $model->tl_name;
						$totalBankBooking = ENBDDepartmentFormEntry::select("id")->where("tl_name",$model->tl_name)->whereIn("status",array("COMPLETED"))->whereRaw($whereRawBank)->get()->count();
						$indexCounter++;
						
						$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						
						$sheet->setCellValue('B'.$indexCounter, $model->tl_name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('C'.$indexCounter, $model->total)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('D'.$indexCounter, $totalBankBooking)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('E'.$indexCounter, $this->lastMonthBookingTeam($model->tl_name,$start_date_application_SCB_internal))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('F'.$indexCounter, $this->lastMonthBookingTeamP($model->tl_name,$start_date_application_SCB_internal))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('G'.$indexCounter, $this->t1SubmissionsTeam($model->tl_name))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('H'.$indexCounter, $this->t2SubmissionsTeam($model->tl_name))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						
						$totalSubmission = $totalSubmission+$model->total;
						$totalBookingBank = $totalBookingBank+$totalBankBooking;
						
						$totalLastBooking = $totalLastBooking+$this->lastMonthBookingTeam($model->tl_name,$start_date_application_SCB_internal);
						$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingTeamP($model->tl_name,$start_date_application_SCB_internal);
						$t1Total = $t1Total+$this->t1SubmissionsTeam($model->tl_name);
						$t2Total = $t2Total+$this->t2SubmissionsTeam($model->tl_name);
						$totalBooking = $totalBooking+$totalBankBooking;
						
						
						
						$journey_to_submission = @round(($model->total/$totalJourneyValueSingle),2);
						$sheet->setCellValue('I'.$indexCounter,$this->getApprovalRate($model->total,$totalBankBooking))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						
						
						
						$sn++;
					}
				}




				/*
			*adding missing team
			*/
				$previousdatePP =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -2 month"));
				$pYearPP = date("Y",strtotime($previousdatePP));
				$pMonthPP = date("m",strtotime($previousdatePP));
				$startDatePP = $pYearPP."-".$pMonthPP."-01";
				$collectionModelP = ENBDDepartmentFormEntry::selectRaw('tl_name')
														->groupBy('tl_name')
														->whereDate('submission_date','>=',$startDatePP)
														->whereNotIn("tl_name",$teamValue)
														->get();
														
				foreach ($collectionModelP as $model) 
				{
						if($model->tl_name != '')
						{
				
					
					
					$totalBankBooking = ENBDDepartmentFormEntry::select("id")->where("tl_name",$model->tl_name)->whereIn("status",array("COMPLETED"))->whereRaw($whereRawBank)->get()->count();
					$indexCounter++;
					
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sheet->setCellValue('B'.$indexCounter, $model->team)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $model->total)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $totalBankBooking)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $this->lastMonthBookingTeam($model->team,$start_date_application_SCB_internal))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $this->lastMonthBookingTeamP($model->team,$start_date_application_SCB_internal))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->t1SubmissionsTeam($model->team))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->t2SubmissionsTeam($model->team))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$totalSubmission = $totalSubmission+$model->total;
					$totalBookingBank = $totalBookingBank+$totalBankBooking;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBookingTeam($model->team,$start_date_application_SCB_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingTeamP($model->team,$start_date_application_SCB_internal);
					$t1Total = $t1Total+$this->t1SubmissionsTeam($model->team);
					$t2Total = $t2Total+$this->t2SubmissionsTeam($model->team);
					$totalBooking = $totalBooking+$totalBankBooking;
					
					
					
					$journey_to_submission = @round(($model->total/$totalJourneyValueSingle),2);
					$sheet->setCellValue('I'.$indexCounter,$this->getApprovalRate($model->total,$totalBankBooking))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					
					$sn++;
				}
	
		}			
		
		


				/**
			*Total Rows
			*/
			$indexCounter = $indexCounter+2;
			$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter.':T'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			$sheet->setCellValue('B'.$indexCounter, "Total")->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, $totalSubmission)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, $totalBookingBank)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, $totalLastBooking)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, $totalLastBookingP)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, $t1Total)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, $t2Total)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			if($totalSubmission != 0)
			{
				
				$approvalRateALL =  round(($totalBooking/$totalSubmission),2);

			}
			else
			{
				$approvalRateALL = 0;
			}
			
			$sheet->setCellValue('I'.$indexCounter,$approvalRateALL)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
		/*	
		*Total Rows
		*/
		$indexCounter++;

			for($col = 'A'; $col !== 'I'; $col++) {
					$sheet->getColumnDimension($col)->setAutoSize(true);
			}
	
			$spreadsheet->getActiveSheet()->getStyle('A1:I2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			
		for($index=1;$index<=$indexCounter;$index++)
		{
			  foreach (range('A','I') as $col) {
					$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
			  }
		}

















	}





	protected function sheet3FlagDetails($spreadsheet,$start_date_application_SCB_internal,$end_date_application_SCB_internal)
	{
		$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:G1');
			$sheet->setCellValue('A1','Flag Details')->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;			
			
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Employee ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$selectedId = DB::table('master_payout')->whereRaw("dept_id = '52'")->limit(3)->orderby('sort_order','DESC')->groupBy('sales_time')->get(['sales_time','sort_order']);
			
			$k=1;
			$sort_orders = '';
			foreach ($selectedId as $mis) 
			{
				if($k==1)
				{
					$col='C';
				}
				if($k==2)
				{
					$col='D';
				}
				if($k==3)
				{
					$col='E';
				}

				$sort_orders .= $mis->sort_order.',';

				$sheet->setCellValue($col.$indexCounter, $mis->sales_time)->getStyle($col.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$k++;
			}

			$sheet->setCellValue('F'.$indexCounter, strtoupper('Range ID'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('G'.$indexCounter, strtoupper('DOJ'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sort_orders = substr($sort_orders,0,-1);
		
			
			$selectedEmp = DB::table('master_payout')->whereRaw("dept_id = '52' AND (employee_id!='' OR employee_id IS NOT NULL) AND employee_id NOT LIKE '%,%' AND employee_id NOT LIKE '%.%' AND sort_order IN (".$sort_orders.")")->groupBy('employee_id')->get(['employee_id','agent_name','range_id','doj']);
			$sn = 1;

			$exp_sort_orders = explode(",",$sort_orders);
			
			$sn = 1;

			$exp_sort_orders = explode(",",$sort_orders);

			$no_of_ele = count($exp_sort_orders);
			
			if($no_of_ele == 2)
			{
				array_push($exp_sort_orders,0);
			}
			if($no_of_ele == 1)
			{
				array_push($exp_sort_orders,0,0);
			}

			// print_r($exp_sort_orders);
			// exit;
			
			foreach ($selectedEmp as $selectedEmpData) 
			{
			
				
				 $indexCounter++; 					
				
				$sheet->setCellValue('A'.$indexCounter, $selectedEmpData->employee_id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				

				$sheet->setCellValue('B'.$indexCounter, $selectedEmpData->agent_name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$FirstData = DB::table('master_payout')->whereRaw("dept_id = '52' AND sort_order ='".$exp_sort_orders[0]."' AND employee_id='".$selectedEmpData->employee_id."'")->get(['tc','flag_rule_name']);	
				

				foreach($FirstData as $FirstDataVal)
				{
					$bgcolor = 'FFFFFF';
					if($FirstDataVal->flag_rule_name == 'Red')
					{
						$bgcolor = 'FF0000';
					}
					if($FirstDataVal->flag_rule_name == 'Green')
					{
						$bgcolor = '66cc66';
					}
					if($FirstDataVal->flag_rule_name == 'Yellow')
					{
						$bgcolor = 'ffff66';
					}
					$sheet->setCellValue('C'.$indexCounter, $FirstDataVal->tc)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$spreadsheet->getActiveSheet()->getStyle('C'.$indexCounter.':'.'C'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
				}

				$SecondData = DB::table('master_payout')->whereRaw("dept_id = '52' AND sort_order ='".$exp_sort_orders[1]."' AND employee_id='".$selectedEmpData->employee_id."'")->get(['tc','flag_rule_name']);				

				foreach($SecondData as $SecondDataVal)
				{
					$bgcolor = 'FFFFFF';
					if($SecondDataVal->flag_rule_name == 'Red')
					{
						$bgcolor = 'FF0000';
					}
					if($SecondDataVal->flag_rule_name == 'Green')
					{
						$bgcolor = '66cc66';
					}
					if($SecondDataVal->flag_rule_name == 'Yellow')
					{
						$bgcolor = 'ffff66';
					}

					$sheet->setCellValue('D'.$indexCounter, $SecondDataVal->tc)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$spreadsheet->getActiveSheet()->getStyle('D'.$indexCounter.':'.'D'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
				}


				if($exp_sort_orders[2]==0)
				{

				}
				else
				{
					$ThirdData = DB::table('master_payout')->whereRaw("dept_id = '52' AND sort_order ='".$exp_sort_orders[2]."' AND employee_id='".$selectedEmpData->employee_id."'")->get(['tc','flag_rule_name']);				

					foreach($ThirdData as $ThirdDataVal)
					{
						$bgcolor = 'FFFFFF';
						if($ThirdDataVal->flag_rule_name == 'Red')
						{
							$bgcolor = 'FF0000';
						}
						if($ThirdDataVal->flag_rule_name == 'Green')
						{
							$bgcolor = '66cc66';
						}
						if($ThirdDataVal->flag_rule_name == 'Yellow')
						{
							$bgcolor = 'ffff66';
						}
	
						$sheet->setCellValue('E'.$indexCounter, $ThirdDataVal->tc)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
	
						$spreadsheet->getActiveSheet()->getStyle('E'.$indexCounter.':'.'E'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
					}
				}

				

				$sheet->setCellValue('F'.$indexCounter, $selectedEmpData->range_id)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('G'.$indexCounter, $selectedEmpData->doj)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				

				


				
				$sn++;
				
			}
			
			
			
			for($col = 'A'; $col !== 'G'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','G') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
	}





	protected function lastMonthBookingTeam($team,$start_date_application_SCB_internal)
	{
	
		$previousdate =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -1 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		$saleEnd = $pMonth.'-'.$pYear;
		/* echo $saleEnd;
		exit; */
		/*
		*check master payout first
		*/
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",52)->where("sales_time",$saleEnd)->where("tl_name",$team)->get();
		$totalCard = 0;
		if(count($employeePayoutData) > 0)
		{
		 foreach($employeePayoutData as $empPayout)
		 {
			 $totalCard = $totalCard+$empPayout->tc;
		 }
			return $totalCard;
		/*
		*check master payout first
		*/		
		}
		else
		{
		
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		
		
		$totalBankBooking = ENBDDepartmentFormEntry::select("id")->where("tl_name",$team)->whereIn("status",array("COMPLETED"))->whereBetween("submission_date",[$startDate,$endDate])->get()->count();
		return 	$totalBankBooking;	
		}
		
		
	}
	
	
	protected function lastMonthBookingTeamP($team,$start_date_application_SCB_internal)
	{
	
		$previousdate =  date('Y-m-d', strtotime($start_date_application_SCB_internal." -2 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		$saleEnd = $pMonth.'-'.$pYear;
		/* echo $saleEnd;
		exit; */
		/*
		*check master payout first
		*/
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",52)->where("sales_time",$saleEnd)->where("tl_name",$team)->get();
		$totalCard = 0;
		if(count($employeePayoutData) > 0)
		{
		 foreach($employeePayoutData as $empPayout)
		 {
			 $totalCard = $totalCard+$empPayout->tc;
		 }
			return $totalCard;
		/*
		*check master payout first
		*/		
		}
		else
		{
		
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		
		$totalBankBooking = ENBDDepartmentFormEntry::select("id")->where("tl_name",$team)->whereIn("status",array("COMPLETED"))->whereBetween("submission_date",[$startDate,$endDate])->get()->count();
		return 	$totalBankBooking;	
		}
		
		
	}


	protected function t1SubmissionsTeam($team)
	{
		$previousDate =  date('Y-m-d', strtotime(' -1 day'));
		return ENBDDepartmentFormEntry::select("id")->whereDate("submission_date","=",$previousDate)->where("tl_name",$team)->get()->count();
		
	}
	protected function t2SubmissionsTeam($team)
	{
		$endDate =  date('Y-m-d', strtotime(' -1 day'));
		$StartDate =  date('Y-m-d', strtotime(' -2 day'));
		return ENBDDepartmentFormEntry::select("id")->whereBetween("submission_date",[$StartDate,$endDate])->where("tl_name",$team)->get()->count();
		
	}




	public function getTLNamefromBank($empid)
	{
		 if($empid != '' && $empid != NULL)
		 {
			$empName = ENBDDepartmentFormEntry::select("tl_name")->where("emp_id",$empid)->first();
			if($empName != '')
			{
				return $empName->tl_name;
			}
			else
			{
				return '--';
			}
		 }
		 else
		 {
			 return '--';
		 }
	}



// Export Agent Performance Module End









}
