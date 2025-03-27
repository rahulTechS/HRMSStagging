<?php

namespace App\Http\Controllers\Banks\CBD;

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
class CBDBankMISController extends Controller
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
 public function importBankCBD()
 {
	
	return view("Banks/CBD/BankMIS/importBankCBD");
 }
 
 public function loginCalRenderTabCBD(Request $request)
 {
	 $monthSelected = $request->m;
	   $yearSelected = $request->y;
	   return view("Banks/CBD/BankMIS/loginCalRenderTabCBD",compact('monthSelected','yearSelected'));
	 
 }
 
	public static function getCBDFileLog($calendar_date=NULL)
		{
		  return $getCBDFileLog = CbdImportFile::where('calendar_date', $calendar_date)->where("type",1)->orderBy('updated_at','DESC')->first();
		}
	
	public static function getCBDFileLogMTF($calendar_date=NULL)
		{
		  return $getCBDFileLog = CbdImportFile::where('calendar_date', $calendar_date)->where("type",2)->orderBy('updated_at','DESC')->first();
		}
 
		public function FileUploadExcelCBD(Request $request)
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
					$fileName = 'CBD_Bank_MIS_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

						$request->file->move(public_path('uploads/CBDMIS/'), $fileName);
						$spreadsheet = new Spreadsheet();

						$inputFileType = 'Xlsx';
						$inputFileName = '/srv/www/htdocs/hrm/public/uploads/CBDMIS/'.$fileName;

						/*  Create a new Reader of the type defined in $inputFileType  */
						$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
						/*  Advise the Reader that we only want to load cell data  */
						$reader->setReadDataOnly(true);
						$spreadsheet = $reader->load($inputFileName);
						$worksheet = $spreadsheet->getActiveSheet();
						// Get the highest row number and column letter referenced in the worksheet
						$highestRow = $worksheet->getHighestRow()-1; // e.g. 10							

						
						
						$tableName = 'CBD_import_file';

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
				
				
				public function FileUploadExcelMTDCBD(Request $request)
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
					$fileName = 'CBD_Bank_MIS_'.date("Y-m-d_h-i-s").'.xlsx';  

		   

						$request->file->move(public_path('uploads/CBDMIS/'), $fileName);
						$spreadsheet = new Spreadsheet();

						$inputFileType = 'Xlsx';
						$inputFileName = '/srv/www/htdocs/hrm/public/uploads/CBDMIS/'.$fileName;

						/*  Create a new Reader of the type defined in $inputFileType  */
						$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
						/*  Advise the Reader that we only want to load cell data  */
						$reader->setReadDataOnly(true);
						$spreadsheet = $reader->load($inputFileName);
						$worksheet = $spreadsheet->getActiveSheet();
						// Get the highest row number and column letter referenced in the worksheet
						$highestRow = $worksheet->getHighestRow()-1; // e.g. 10							

						
						
						$tableName = 'CBD_import_file';

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
				
				public function CBDFileImport(Request $request)
				{	
			
					$user_id = $request->session()->get('EmployeeId');
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('CBD_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/CBDMIS/';
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
							
							if(count($sheetData[$k])!= 23)
							{
								$fileInfo = DB::table('CBD_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}							
							
							$sheetData[$k] = str_replace("'","`",$sheetData[$k]);
							$allowBureau_Score = 1;
							if(trim($sheetData[$k][16]) == '#N/A' || trim($sheetData[$k][16]) == 'N/A' || trim($sheetData[$k][16]) == 'NA' || trim($sheetData[$k][16]) == '#NA' || trim($sheetData[$k][16]) == '')
							{
								$allowBureau_Score = 2;
							}
							
							$allowAPP_SCORE = 1;
							
							if(trim($sheetData[$k][17]) == '#N/A' || trim($sheetData[$k][17]) == 'N/A' || trim($sheetData[$k][17]) == 'NA' || trim($sheetData[$k][17]) == '#NA' || trim($sheetData[$k][17]) == '')
							{
								$allowAPP_SCORE = 2;
							}
							
							
							$allowBureau_MOB = 1;
							if(trim($sheetData[$k][18]) == '#N/A' || trim($sheetData[$k][18]) == 'N/A' || trim($sheetData[$k][18]) == 'NA' || trim($sheetData[$k][18]) == '#NA' || trim($sheetData[$k][18]) == '')
							{
								$allowBureau_MOB = 2;
							}
							
							$allowTotal_Liabilities = 1; 
							if(trim($sheetData[$k][19]) == '#N/A' || trim($sheetData[$k][19]) == 'N/A' || trim($sheetData[$k][19]) == 'NA' || trim($sheetData[$k][19]) == '#NA' || trim($sheetData[$k][19]) == '')
							{
								$allowTotal_Liabilities = 2;
							}
							
							
							$allowTotal_DSR = 1;
							if(trim($sheetData[$k][20]) == '#N/A' || trim($sheetData[$k][20]) == 'N/A' || trim($sheetData[$k][20]) == 'NA' || trim($sheetData[$k][20]) == '#NA' || trim($sheetData[$k][20]) == '')
							{
								$allowTotal_DSR = 2;
							}
							
							
							$allowAECB_Status = 1;
							if(trim($sheetData[$k][21]) == '#N/A' || trim($sheetData[$k][21]) == 'N/A' || trim($sheetData[$k][21]) == 'NA' || trim($sheetData[$k][21]) == '#NA' || trim($sheetData[$k][21]) == '')
							{
								$allowAECB_Status = 2;
							}
							
							$sheetData[$k][18] = str_replace("#N/A","",$sheetData[$k][18]);
							$sheetData[$k][19] = str_replace("#N/A","",$sheetData[$k][19]);
							$sheetData[$k][20] = str_replace("#N/A","",$sheetData[$k][20]);
							$sheetData[$k][8] = str_replace("#N/A","",$sheetData[$k][8]);
							$sheetData[$k][9] = str_replace("#N/A","",$sheetData[$k][9]);
							$sheetData[$k][14] = str_replace("#N/A","",$sheetData[$k][14]);
							$sheetData[$k][16] = str_replace("#N/A","",$sheetData[$k][16]);
							$sheetData[$k][17] = str_replace("#N/A","",$sheetData[$k][17]);
							$file_values = array(
												'customer_name' => trim($sheetData[$k][1]),
												'nationality' => trim($sheetData[$k][2]),
												'no_primary_cards' => trim($sheetData[$k][3]),
												'creation_date' => ($sheetData[$k][4]?date('Y-m-d',strtotime($sheetData[$k][4])):'0000-00-00'),												
												'created_month' => trim($sheetData[$k][5]),
												'card_type' => trim($sheetData[$k][6]),
												'employer_name' => trim($sheetData[$k][7]),
												'declared_salary' => ($sheetData[$k][8]?trim(str_replace(",","",$sheetData[$k][8])):'0'),
												'ELIGIBLE_INCOME' => ($sheetData[$k][9]?trim(str_replace(",","",$sheetData[$k][9])):'0'),
												'Created_User' => trim($sheetData[$k][10]),
												'AGENCY' => trim($sheetData[$k][11]),
												'Status' => trim($sheetData[$k][12]),
												'Channel' => trim($sheetData[$k][13]),
												'TOTAL_LIMIT_POST_APPROVAL' => ($sheetData[$k][14]?trim(str_replace(",","",$sheetData[$k][14])):'0'),
												'DROP_OFF_STAGE' => trim($sheetData[$k][15]),	
												
												'Decline_Reason' => trim($sheetData[$k][22]),	
												
												);
								
/* echo "<pre>";
print_r($file_values);
exit; */								
							/*
							*check for #NA
							*/							
							if($allowBureau_MOB ==1)
							{
							
							$file_values['Bureau_MOB'] = ($sheetData[$k][18]?trim(str_replace(",","",$sheetData[$k][18])):'0');
							}
							
							
							if($allowTotal_Liabilities ==1)
							{
							
							$file_values['Total_Liabilities'] = ($sheetData[$k][19]?trim(str_replace(",","",$sheetData[$k][19])):'0');
							}
							
							if($allowTotal_DSR ==1)
							{
							
							$file_values['Total_DSR'] = ($sheetData[$k][20]?trim(str_replace(",","",$sheetData[$k][20])):'0');
							}
							if($allowAECB_Status ==1)
							{
							
							$file_values['AECB_Status'] = trim($sheetData[$k][21]);
							}
							
							if($allowBureau_Score ==1)
							{
							
							$file_values['Bureau_Score'] = ($sheetData[$k][16]?trim(str_replace(",","",$sheetData[$k][16])):'0');
							}
							
							if($allowAPP_SCORE ==1)
							{
							
							$file_values['APP_SCORE'] = ($sheetData[$k][17]?trim(str_replace(",","",$sheetData[$k][17])):'0');
							}
							/*
							*check for #NA
							*/	
							if($k == 287)
							{
								/*  echo trim($sheetData[$k][0]);
								echo "====";
							echo "<pre>";
							print_r($file_values);
							
							exit;   */
							}
											
							/* 	echo "<pre>";
							print_r($file_values);
							
							exit;		 */		
							 $ref_no = trim($sheetData[$k][0]);
							$whereRaw = " ref_no ='".$ref_no."'";
							$check = DB::table('CBD_bank_mis')->whereRaw($whereRaw)->get();

							if(count($check)>0)
							{			
								$getdatacbd_marging_status = DB::table('CBD_bank_mis')->whereRaw($whereRaw)->first()->cbd_marging_status;
								if($getdatacbd_marging_status == 2)
								{
									$file_values['update_status'] = 1;
								}
								DB::table('CBD_bank_mis')->where('ref_no', $ref_no)->update($file_values);
								
							}
							else
							{
								
								$all_values = $file_values;
								$all_values['ref_no'] = $ref_no;
								$all_values['cbd_marging_status'] = 1;
							
								
								DB::table('CBD_bank_mis')->insert($all_values);
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
			
			
			public function CBDMTDFileImport(Request $request)
			{
					$user_id = $request->session()->get('EmployeeId');
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('CBD_import_file')->where('id', $attr_f_import)->first();
					
					$filename = $fileInfo->file_name;
					
					$uploadPath = '/srv/www/htdocs/hrm/public/uploads/CBDMIS/';
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
							
							if(count($sheetData[$k])!= 11)
							{
								$fileInfo = DB::table('CBD_import_file')->where('id', $attr_f_import)->delete();
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
												'CD_OPN_DT' => ($sheetData[$k][0]?date('Y-m-d',strtotime($sheetData[$k][0])):'0000-00-00'),
												'PROD_DESC_TX' => trim($sheetData[$k][1]),
												'CD_Actv_Dt' => trim($sheetData[$k][2]),
												'Type' => trim($sheetData[$k][3]),
												'Activation_Status' => trim($sheetData[$k][4]),
												'Agency' => trim($sheetData[$k][5]),
												'User_Name' => trim($sheetData[$k][6]),
												
												'Salary' => trim($sheetData[$k][8]),
												'MonthofApproval' => trim($sheetData[$k][9]),
												'Comments' => trim($sheetData[$k][10]),
												
												
												
												);
												
								
							 $Appl_Nb = trim($sheetData[$k][7]);
							$whereRaw = " Appl_Nb ='".$Appl_Nb."'";
							$check = DB::table('Bank_CBD_MTD')->whereRaw($whereRaw)->get();

							if(count($check)>0)
							{			
							
								
									/* $file_values['update_status'] = 1;
									$file_values['match_bank_status'] = 1; */
								
								DB::table('Bank_CBD_MTD')->where('Appl_Nb', $Appl_Nb)->update($file_values);
								
							}
							else
							{
								
								$all_values = $file_values;
								$all_values['Appl_Nb'] = $Appl_Nb;
								$all_values['update_status'] = 1;
								$all_values['match_bank_status'] = 1;
							
								
								DB::table('Bank_CBD_MTD')->insert($all_values);
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
			
			
			public function exportDocReportBankMisCBDCards(Request $request)
			{
				 $parameters = $request->input(); 
				/*  echo "<pre>";
				 print_r($parameters);
				 exit;  */
				 $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Bank_MIS_CBD_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AD1');
			$sheet->setCellValue('A1', 'Bank MIS CBD Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('App Ref No'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Agent Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Customer Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Nationality'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('No of Primary Cards'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Creation date'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Creation Month'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Card Type'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Employer Name'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('DECLARED SALARY'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('ELIGIBLE INCOME'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Created User'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('AGENCY'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Status'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Channel'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('TOTAL LIMIT POST APPROVAL'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('DROP OFF STAGE'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Bureau Score'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('APP SCORE'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('Bureau MOB'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('Total Liabilities'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('Total DSR'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('AECB Status'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('Decline Reason (S1) '))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('Recruiter Name'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('Recruiter Category'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('Vintage'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter, strtoupper('Range Id'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AD'.$indexCounter, strtoupper('Employee Id'))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  CBDBankMis::where("id",$sid)->first();
				$indexCounter++;
				
				$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->ref_no)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $this->getEmployeeName($mis->employee_id))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $mis->customer_name)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mis->nationality)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mis->no_primary_cards)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->creation_date)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->created_month)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $mis->card_type)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->employer_name)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $mis->declared_salary)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $mis->ELIGIBLE_INCOME)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $mis->Created_User)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $mis->AGENCY)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $mis->Status)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $mis->Channel)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $mis->TOTAL_LIMIT_POST_APPROVAL)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $mis->DROP_OFF_STAGE)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $mis->Bureau_Score)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $mis->APP_SCORE)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $mis->Bureau_MOB)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $mis->Total_Liabilities)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $mis->Total_DSR)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $mis->AECB_Status)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Y'.$indexCounter, $mis->Decline_Reason)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $this->getrecruiterNameCBD($mis->employee_id))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter,$this->getrecruiterCatCBD($mis->employee_id))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter,$mis->vintage)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AC'.$indexCounter,$mis->range_id)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AD'.$indexCounter,$mis->employee_id)->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
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
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="CBD-Bank";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
			}
			
			
			public static function getAllMonthFileLogCBD(Request $request)
			{	  
			  $m = $request->m;
			  $y = $request->y;

			  $calendar_start_date = $y.'-'.$m.'-01';
			  $calendar_end_date = $y.'-'.$m.'-31';

			  $whereRaw = " calendar_date >='".$calendar_start_date."' and calendar_date <='".$calendar_end_date."'";

			  $getFileLog = DB::table('CBD_import_file')->whereRaw($whereRaw)->orderBy('updated_at','DESC')->get();
			 

			  return view("Banks/CBD/BankMIS/allMonthFileLogCBD",compact('getFileLog'));

			}
			
			public function searchCBDBankInner(Request $request)
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

				if(@isset($requestParameters['ref_no']))
				{
					$ref_no = @$requestParameters['ref_no'];
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

				if(isset($requestParameters['start_date_creation']))
				{
					$start_date_creation = @$requestParameters['start_date_creation'];
				}
				if(isset($requestParameters['end_date_creation']))
				{
					$end_date_creation = @$requestParameters['end_date_creation'];
				}
				
				if(isset($requestParameters['start_date_approval_bank']))
				{
					$start_date_approval_bank = @$requestParameters['start_date_approval_bank'];
				}
				if(isset($requestParameters['end_date_approval_bank']))
				{
					$end_date_approval_bank = @$requestParameters['end_date_approval_bank'];
				}
				
				if(isset($requestParameters['submission_type']))
				{
					$submission_type = @$requestParameters['submission_type'];
				}
				
				$request->session()->put('ref_no_CBD_bank',$ref_no);
				$request->session()->put('AECB_Status_CBD_bank',$AECB_Status);
				$request->session()->put('status_CBD_bank',$status);
				$request->session()->put('employee_id_CBD_bank',$employee_id);
				$request->session()->put('smManager_CBD_bank',$sm_manager);
				$request->session()->put('start_date_creation_CBD_bank',$start_date_creation);
				$request->session()->put('end_date_creation_CBD_bank',$end_date_creation);
				
				$request->session()->put('start_date_approval_CBD_bank',$start_date_approval_bank);
				$request->session()->put('end_date_approval_CBD_bank',$end_date_approval_bank);
				$request->session()->put('master_cbd_search_bank','');
				$request->session()->put('submission_type_CBD_Bank',$submission_type);
				return redirect("loadBankContentsCBDCardBankSide");
			}
			
			public function resetLoginInnerCBD(Request $request)
			{
				$request->session()->put('ref_no_CBD_bank','');
				$request->session()->put('AECB_Status_CBD_bank','');
				$request->session()->put('status_CBD_bank','');
				$request->session()->put('start_date_creation_CBD_bank','');
				$request->session()->put('end_date_creation_CBD_bank','');
				$request->session()->put('start_date_approval_CBD_bank','');
				$request->session()->put('end_date_approval_CBD_bank','');
				$request->session()->put('employee_id_CBD_bank','');
				$request->session()->put('smManager_CBD_bank','');
				$request->session()->put('submission_type_CBD_Bank','');
				$request->session()->put('master_cbd_search_bank',2);
				return redirect("loadBankContentsCBDCardBankSide");
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

}
