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
use App\Models\Bank\CBD\CbdImportFile;
use App\Models\Bank\CBD\CbdLinkImportFile;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Http\Controllers\Attribute\DepartmentFormController;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\Bank\CBD\BankCBDMTD;
use App\Models\Recruiter\Designation;
use App\Models\Dashboard\MasterPayout;
use App\Models\SEPayout\RangeDetailsVintage;
use App\Models\Attribute\SalaryStruture;
use Session;
use App\Models\Employee\ExportDataLog;
use App\Http\Controllers\Push\NotificatonController;

class CBDLinkMISController extends Controller
{
   
 public function linkBankCBD()
 {
	
	return view("Banks/CBD/matchingMIS/linkBankCBD");
 }
 
 public function loginCalRenderTabCBDLinks(Request $request)
 {
	 
	 $monthSelected = $request->m;
	   $yearSelected = $request->y;
	   return view("Banks/CBD/matchingMIS/loginCalRenderTabCBDLinks",compact('monthSelected','yearSelected'));
	 
 }
 
 public static function getCBDFileLogLinks($calendar_date=NULL)
    {
      return $getCBDFileLog = CbdLinkImportFile::where('calendar_date', $calendar_date)->orderBy('updated_at','DESC')->first();
    }
 
		public function FileUploadLinkExcelCBD(Request $request)
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

						
						
						$tableName = 'CBD_link_import_file';

					$values = array('user_id'=>$user_id,'file_name' => $fileName,'totalcount' => $highestRow,'calendar_date' => $request->uploadedDate);
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
				
				
				
				public function CBDLinkFileImport(Request $request)
				{					
					$user_id = $request->session()->get('EmployeeId');
					$result = array();
					$attr_f_import = $request->attr_f_import;
					$inserteddate = $request->inserteddate;
					$conter = $request->counter;

					$fileInfo = DB::table('CBD_link_import_file')->where('id', $attr_f_import)->first();
					
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
							
							if(count($sheetData[$k])!= 10)
							{
								$fileInfo = DB::table('CBD_link_import_file')->where('id', $attr_f_import)->delete();
								$result['code'] = 300;
								echo json_encode($result);
								exit;
							}
							if($k==0)
							{
								continue;
							}							
							
							$sheetData[$k] = str_replace("'","`",$sheetData[$k]);
							
							$interMISId = $sheetData[$k][0];
							
							$refNo = $sheetData[$k][9];
							if(trim($refNo) != 'missing' && trim($refNo) != 'tab case' && trim($refNo) != 'duplicate' && trim($refNo) != 'test')
							{
								/* echo "<pre>";
								print_r($sheetData);
								exit;
								echo $refNo;exit; */
							$jonusId = CBDBankMis::where("ref_no",trim($refNo))->first()->id;
							
							/*
							*marging from bank to mis
							*/
							$bankData = CBDBankMis::where("id",$jonusId)->first();
							$updateInternalMis = DepartmentFormEntry::find($interMISId);
							$updateInternalMis->ref_no = $bankData->ref_no;
							$updateInternalMis->customer_name = $bankData->customer_name;
							$updateInternalMis->channel_cbd = $bankData->Channel;
							$updateInternalMis->status_AECB_cbd = $bankData->AECB_Status;
							$updateInternalMis->form_status = $bankData->Status;
							$updateInternalMis->card_type_cbd = $bankData->card_type;
							
							$updateInternalMis->cbd_marging_status = 2;
							$updateInternalMis->cbd_update_status = 2;
							$updateInternalMis->missing_internal = 2;
							$updateInternalMis->save();
							
								/*
								*update in child
								*/
								$getData = DepartmentFormChildEntry::where("parent_id",$interMISId)->where("attribute_code","customer_name")->first();
								if($getData != '')
								{
									$updateChild = DepartmentFormChildEntry::find($getData->id);
									$updateChild->attribute_value = $bankData->customer_name;
									$updateChild->save();
								}
								/*
								*update in child
								*/
								
								/*
								*update in child
								*/
								
								$getData = DepartmentFormChildEntry::where("parent_id",$interMISId)->where("attribute_code","status_cbd")->first();
								if($getData != '')
								{
									$updateChild = DepartmentFormChildEntry::find($getData->id);
									$updateChild->attribute_value = $bankData->Status;
									$updateChild->save();
								}
								$getData = DepartmentFormChildEntry::where("parent_id",$interMISId)->where("attribute_code","channel_cbd")->first();
								if($getData != '')
								{
									$updateChild = DepartmentFormChildEntry::find($getData->id);
									$updateChild->attribute_value = $bankData->Channel;
									$updateChild->save();
								}
								$getData = DepartmentFormChildEntry::where("parent_id",$interMISId)->where("attribute_code","card_type_cbd")->first();
								if($getData != '')
								{
									$updateChild = DepartmentFormChildEntry::find($getData->id);
									$updateChild->attribute_value = $bankData->card_type;
									$updateChild->save();
								}
								$getData = DepartmentFormChildEntry::where("parent_id",$interMISId)->where("attribute_code","aecb_status")->first();
								if($getData != '')
								{
									$updateChild = DepartmentFormChildEntry::find($getData->id);
									$updateChild->attribute_value = $bankData->AECB_Status;
									$updateChild->save();
								}
								/*
								*update in child
								*/
							
							/*
							*marging from bank to mis
							*/
							
							/*
							*marging from internal to bank
							*/
							$misInternalData = DepartmentFormEntry::where("id",$interMISId)->first();
							
								$updateBankMis = CBDBankMis::find($jonusId);
								$updateBankMis->sm_manager = $misInternalData->team;
								$updateBankMis->employee_id = $misInternalData->emp_id;
								$updateBankMis->cbd_marging_status = 2;
								$updateBankMis->update_emp_status = 2;
								$updateBankMis->save();
							/*
							*marging from internal to bank
							*/
							}
							else
							{
								$updateInternalMis = DepartmentFormEntry::find($interMISId);
								
								if(trim($refNo) == 'tab case')
								{
									$updateInternalMis->ref_no = 'tab case';
									$updateInternalMis->tabcase_status = 1;
								}
								if(trim($refNo) == 'duplicate')
								{
									$updateInternalMis->ref_no = 'duplicate';
									$updateInternalMis->duplicate_status = 1;
								}
								else
								{
									$updateInternalMis->ref_no = 'missing';
								}
								$updateInternalMis->missing_internal = 1;
								$updateInternalMis->cbd_marging_status = 2;
								$updateInternalMis->cbd_update_status = 2;
								$updateInternalMis->save();
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
			$sheet->mergeCells('A1:X1');
			$sheet->setCellValue('A1', 'Bank MIS CBD Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('App Ref No'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Customer Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Nationality'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('No of Primary Cards'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Creation date'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Creation Month'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Card Type'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Employer Name'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('DECLARED SALARY'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('ELIGIBLE INCOME'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Created User'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('AGENCY'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Status'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Channel'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('TOTAL LIMIT POST APPROVAL'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('DROP OFF STAGE'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Bureau Score'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('APP SCORE'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('Bureau MOB'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('Total Liabilities'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('Total DSR'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('AECB Status'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Decline Reason (S1) '))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  CBDBankMis::where("id",$sid)->first();
				$indexCounter++;
				
				$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->ref_no)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mis->customer_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $mis->nationality)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mis->no_primary_cards)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mis->creation_date)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->created_month)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->card_type)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $mis->employer_name)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->declared_salary)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $mis->ELIGIBLE_INCOME)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $mis->Created_User)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $mis->AGENCY)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $mis->Status)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $mis->Channel)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $mis->TOTAL_LIMIT_POST_APPROVAL)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $mis->DROP_OFF_STAGE)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $mis->Bureau_Score)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $mis->APP_SCORE)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $mis->Bureau_MOB)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $mis->Total_Liabilities)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $mis->Total_DSR)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $mis->AECB_Status)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $mis->Decline_Reason)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'X'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:X1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','X') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
			}
			
			
			public function exportDocReportCBDCardsFinalReport(Request $request)
			{
				 $parameters = $request->input(); 
				/*   echo "<pre>";
				 print_r($parameters);
				 exit;   */
				 $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Final_MIS_CBD_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AG1');
			$sheet->setCellValue('A1', 'Final MIS CBD Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('App Ref No'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Customer Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Customer Mobile'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('SM Name'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Employee Name'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Employee Code'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Nationality'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('No of Primary Cards'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Creation date'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Creation Month'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Card Type'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Employer Name'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('DECLARED SALARY'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('ELIGIBLE INCOME'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Created User'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('AGENCY'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Status'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Channel'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('TOTAL LIMIT POST APPROVAL'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('DROP OFF STAGE'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('Bureau Score'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('APP SCORE'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Bureau MOB'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('Total Liabilities'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('Total DSR'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('AECB Status'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('Decline Reason (S1) '))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter, strtoupper('MIS Link Status'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AD'.$indexCounter, strtoupper('Recuriter Name'))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AE'.$indexCounter, strtoupper('Recuriter Category'))->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AF'.$indexCounter, strtoupper('Vintage'))->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AG'.$indexCounter, strtoupper('Range_id'))->getStyle('AG'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$internalMis = DepartmentFormEntry::where("id",$sid)->first();
				$internalMisRefNo = $internalMis->ref_no;
				$indexCounter++;
				if($internalMisRefNo != '' && $internalMisRefNo != NULL && $internalMisRefNo != 'missing')
				{
				$mis =  CBDBankMis::where("ref_no",$internalMisRefNo)->first();
				if($mis != '')
				{
				
				
				$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->ref_no)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mis->customer_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $internalMis->customer_mobile)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $internalMis->team)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $this->getEmployeeName($internalMis->emp_id))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $internalMis->agent_code)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->nationality)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $mis->no_primary_cards)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->creation_date)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $mis->created_month)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $mis->card_type)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $mis->employer_name)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $mis->declared_salary)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $mis->ELIGIBLE_INCOME)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $mis->Created_User)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $mis->AGENCY)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $mis->Status)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $mis->Channel)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $mis->TOTAL_LIMIT_POST_APPROVAL)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $mis->DROP_OFF_STAGE)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $mis->Bureau_Score)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $mis->APP_SCORE)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $mis->Bureau_MOB)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Y'.$indexCounter, $mis->Total_Liabilities)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $mis->Total_DSR)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter, $mis->AECB_Status)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter, $mis->Decline_Reason)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AC'.$indexCounter, 'DONE')->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AD'.$indexCounter, $this->getrecruiterNameCBD($internalMis->emp_id))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AE'.$indexCounter, $this->getrecruiterCatCBD($internalMis->emp_id))->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AF'.$indexCounter, $internalMis->vintage)->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AG'.$indexCounter, $internalMis->range_id)->getStyle('AG'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				
				}
				else
				{
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $internalMis->ref_no)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $internalMis->customer_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $internalMis->customer_mobile)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $internalMis->team)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $this->getEmployeeName($internalMis->emp_id))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $internalMis->agent_code)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('H'.$indexCounter, '')->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('I'.$indexCounter, '')->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $internalMis->application_id)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('K'.$indexCounter, $mis->created_month)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $internalMis->card_type_cbd)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('M'.$indexCounter, $mis->employer_name)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('N'.$indexCounter, $mis->declared_salary)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('O'.$indexCounter, $mis->ELIGIBLE_INCOME)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('P'.$indexCounter, $mis->Created_User)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('Q'.$indexCounter, $mis->AGENCY)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('R'.$indexCounter, $internalMis->form_status)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('S'.$indexCounter, $internalMis->channel_cbd)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('T'.$indexCounter, $mis->TOTAL_LIMIT_POST_APPROVAL)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('U'.$indexCounter, $mis->DROP_OFF_STAGE)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('V'.$indexCounter, $mis->Bureau_Score)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('W'.$indexCounter, $mis->APP_SCORE)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('X'.$indexCounter, $mis->Bureau_MOB)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('Y'.$indexCounter, $mis->Total_Liabilities)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('Z'.$indexCounter, $mis->Total_DSR)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AA'.$indexCounter, $internalMis->status_AECB_cbd)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('AB'.$indexCounter, $mis->Decline_Reason)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AC'.$indexCounter, 'PENDING')->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AD'.$indexCounter, $this->getrecruiterNameCBD($internalMis->emp_id))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AE'.$indexCounter, $this->getrecruiterCatCBD($internalMis->emp_id))->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AF'.$indexCounter, $internalMis->vintage)->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AG'.$indexCounter, $internalMis->range_id)->getStyle('AG'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
				}
				}
				else
				{
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $internalMis->ref_no)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $internalMis->customer_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $internalMis->customer_mobile)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $internalMis->team)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $this->getEmployeeName($internalMis->emp_id))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $internalMis->agent_code)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('H'.$indexCounter, '')->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('I'.$indexCounter, '')->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $internalMis->application_id)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('K'.$indexCounter, $mis->created_month)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $internalMis->card_type_cbd)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('M'.$indexCounter, $mis->employer_name)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('N'.$indexCounter, $mis->declared_salary)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('O'.$indexCounter, $mis->ELIGIBLE_INCOME)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('P'.$indexCounter, $mis->Created_User)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('Q'.$indexCounter, $mis->AGENCY)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('R'.$indexCounter, $internalMis->form_status)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('S'.$indexCounter, $internalMis->channel_cbd)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('T'.$indexCounter, $mis->TOTAL_LIMIT_POST_APPROVAL)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('U'.$indexCounter, $mis->DROP_OFF_STAGE)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('V'.$indexCounter, $mis->Bureau_Score)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('W'.$indexCounter, $mis->APP_SCORE)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('X'.$indexCounter, $mis->Bureau_MOB)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('Y'.$indexCounter, $mis->Total_Liabilities)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('Z'.$indexCounter, $mis->Total_DSR)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AA'.$indexCounter, $internalMis->status_AECB_cbd)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					//$sheet->setCellValue('AB'.$indexCounter, $mis->Decline_Reason)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AC'.$indexCounter, 'PENDING')->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AD'.$indexCounter, $this->getrecruiterNameCBD($internalMis->emp_id))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AE'.$indexCounter, $this->getrecruiterCatCBD($internalMis->emp_id))->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AF'.$indexCounter, $internalMis->vintage)->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AG'.$indexCounter, $internalMis->range_id)->getStyle('AG'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
				}
				$sn++;
			}
			
			
			  for($col = 'A'; $col !== 'AG'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:AG1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AG') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="CBD-Internal";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
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
				$AECB_Status = '';
				$status = '';
				$ref_no = '';
				$employee_id = '';
				$sm_manager = '';

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
				
				$request->session()->put('ref_no_CBD_bank',$ref_no);
				$request->session()->put('AECB_Status_CBD_bank',$AECB_Status);
				$request->session()->put('status_CBD_bank',$status);
				$request->session()->put('employee_id_CBD_bank',$employee_id);
				$request->session()->put('smManager_CBD_bank',$sm_manager);
				$request->session()->put('start_date_creation_CBD_bank',$start_date_creation);
				$request->session()->put('end_date_creation_CBD_bank',$end_date_creation);
				$request->session()->put('master_cbd_search_bank','');
				return redirect("loadBankContentsCBDCardBankSide");
			}
			
			public function resetLoginInnerCBD(Request $request)
			{
				$request->session()->put('ref_no_CBD_bank','');
				$request->session()->put('AECB_Status_CBD_bank','');
				$request->session()->put('status_CBD_bank','');
				$request->session()->put('start_date_creation_CBD_bank','');
				$request->session()->put('end_date_creation_CBD_bank','');
				$request->session()->put('employee_id_CBD_bank','');
				$request->session()->put('smManager_CBD_bank','');
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
			
			
public function updateMISStatus()
			{
				$cbdBankMod = CBDBankMis::where("update_status",1)->where("cbd_marging_status",2)->get();
				/* echo count($cbdBankMod);
				exit;  */
				
				foreach($cbdBankMod as $cbd)
				{
					$refNo = $cbd->ref_no;
					$detailsInternal = DepartmentFormEntry::where("ref_no",$refNo)->where("form_id",2)->first();
					if($detailsInternal != '')
					{
						$file_values = array();
						$file_values['form_status'] = $cbd->Status;
						DB::table('department_form_parent_entry')->where('ref_no', $refNo)->update($file_values);
						$file_values1 = array();
						$file_values1['update_status'] = 2;
						DB::table('CBD_bank_mis')->where('ref_no', $refNo)->update($file_values1);
					}
					else
					{
						$file_values1 = array();
						$file_values1['update_status'] = 3;
						DB::table('CBD_bank_mis')->where('ref_no', $refNo)->update($file_values1);
					}
				}
				echo "done";
				exit;
				
				
			}
			
			
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
				 
	public function updateMTDDataInternal()
	{
		$bankCBDMTDMod = BankCBDMTD::where("update_status",1)->get();
		foreach($bankCBDMTDMod as $mtd)
		{
			$Appl_Nb = $mtd->Appl_Nb;
			$departP = DepartmentFormEntry::where("ref_no",$Appl_Nb)->first();
			if($departP != '')
			{
				$updateMTD = BankCBDMTD::find($mtd->id);
				$updateMTD->employee_id = $departP->emp_id;
				$updateMTD->sm_manager = $departP->team;
				$updateMTD->application_date = $departP->application_date;
				$updateMTD->update_status = 2;
				$updateMTD->save();
			}
		}
		echo "done";
		exit;
	}
	
	
	public function updateMTDDataBank()
	{
		$bankCBDMTDMod = BankCBDMTD::where("match_bank_status",1)->get();
		foreach($bankCBDMTDMod as $mtd)
		{
			$Appl_Nb = $mtd->Appl_Nb;
			$departP = CBDBankMis::where("ref_no",$Appl_Nb)->first();
			if($departP != '')
			{
				$updateMTD = BankCBDMTD::find($mtd->id);
				
				$updateMTD->match_bank_status = 2;
				$updateMTD->save();
			}
		}
		echo "done";
		exit;
	}
	
	
	
	public function exportDocReportMTDMisCBDCards(Request $request)
	{
			$parameters = $request->input(); 
				 /* echo "<pre>";
				 print_r($parameters);
				 exit; */
				 $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'MTD_MIS_CBD_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:S1');
			$sheet->setCellValue('A1', 'MTD MIS CBD Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Agent name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('SM Manager'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Application Date'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('CD OPN DT'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('PROD_DESC_TX'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('CD_Actv_Dt'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Type'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Activation Status'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Agency'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Agent Code'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Reference No'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Salary'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Month of Approval'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Comments'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Recruiter Name'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Recruiter Category'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Vintage'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Range Id'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				$indexCounter++;
				$misMTD = BankCBDMTD::where("id",$sid)->first();
				$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $this->getEmployeeName($misMTD->employee_id))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $misMTD->sm_manager)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $misMTD->application_date)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $misMTD->CD_OPN_DT)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $misMTD->PROD_DESC_TX)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $misMTD->CD_Actv_Dt)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $misMTD->Type)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $misMTD->Activation_Status)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $misMTD->Agency)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $misMTD->User_Name)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $misMTD->Appl_Nb)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $misMTD->Salary)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $misMTD->MonthofApproval)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $misMTD->Comments)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $this->getrecruiterNameCBD($misMTD->employee_id))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $this->getrecruiterCatCBD($misMTD->employee_id))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $misMTD->vintage)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $misMTD->range_id)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sn++;
			}
			
			
			  for($col = 'A'; $col !== 'S'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:S1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','S') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="CBD-MTD";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
	}
	
	public function action_handler_export_internal_cards_CBD_Agents_Performance(Request $request)
	{
		$start_date_application_CBD_internal = '';
		$end_date_application_CBD_internal = '';
		$whereRaw = 'form_id = 2';
		$whereRawBank = "ref_no != ''";
		$whereRawBankCarryForward = "ref_no != ''";
		$whereRawMTD = "Appl_Nb != ''";
		if($request->session()->get('start_date_application_CBD_internal') != '')
				{
					$start_date_application_CBD_internal = $request->session()->get('start_date_application_CBD_internal');			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'";
					$whereRawBank .= " AND creation_date >='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'";
					$whereRawBankCarryForward .= " AND approval_date >='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'";
					$whereRawMTD .= " AND CD_OPN_DT >='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'";
				}
	    else
				{
					$start_date_application_CBD_internal = date("Y")."-".date("m")."-01";			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'";
					$whereRawBank .= " AND creation_date >='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'";
					$whereRawBankCarryForward .= " AND approval_date >='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'";
					$whereRawMTD .= " AND CD_OPN_DT >='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'";
				}
		if($request->session()->get('end_date_application_CBD_internal') != '')
				{
					$end_date_application_CBD_internal = $request->session()->get('end_date_application_CBD_internal');			
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_CBD_internal))."'";
					$whereRawBank .= " AND creation_date <='".date('Y-m-d',strtotime($end_date_application_CBD_internal))."'";
					$whereRawBankCarryForward .= " AND approval_date <='".date('Y-m-d',strtotime($end_date_application_CBD_internal))."'";
					$whereRawMTD .= " AND CD_OPN_DT <='".date('Y-m-d',strtotime($end_date_application_CBD_internal))."'";
				}	
		else
				{
					$end_date_application_CBD_internal = date("Y-m-d");	
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_CBD_internal))."'";
					$whereRawBank .= " AND creation_date <='".date('Y-m-d',strtotime($end_date_application_CBD_internal))."'";
					$whereRawBankCarryForward .= " AND approval_date <='".date('Y-m-d',strtotime($end_date_application_CBD_internal))."'";
					$whereRawMTD .= " AND CD_OPN_DT <='".date('Y-m-d',strtotime($end_date_application_CBD_internal))."'";
				}
		
			/*
			*-1,-2 month Name
			*start code
			*/
			$previousMonthName =  date('M-Y', strtotime(date($start_date_application_CBD_internal)." -1 month"));
			$previousMonthName1 =  date('M-Y', strtotime(date($start_date_application_CBD_internal)." -2 month"));
			/*
			*-1,-2 month Name
			*end code
			*/
			$collectionModel = DepartmentFormEntry::selectRaw('count(*) as total, emp_id,team,vintage,range_id,doj,agent_code')
												  ->groupBy('emp_id')
												  ->whereRaw($whereRaw)
												  ->get();
		
		    $filename = 'Agent_performance_CBD_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:R2');
			$sheet->setCellValue('A1', 'Agents Performance CBD Cards - from -'.date("d M Y",strtotime($start_date_application_CBD_internal)).'to -'.date("d M Y",strtotime($end_date_application_CBD_internal)))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->mergeCells('S1:V2');
			$sheet->setCellValue('S1', 'Income Segmentation')->getStyle('S1')->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->mergeCells('W1:Y2');
			$sheet->setCellValue('W1', 'Approval Rate')->getStyle('W1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->mergeCells('Z1:AD2');
			$sheet->setCellValue('Z1', 'AECB Status')->getStyle('Z1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->mergeCells('AF1:AF2');
			$sheet->setCellValue('AF1', 'DOJ')->getStyle('AF1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 5;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Agent Emp Id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Agent name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Agent Code'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('SM Manager'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Total Submissions'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Journey'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Total Booking As Per Bank MIS'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Total Booking As Per MTD MIS'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName.')'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName1.')'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Recruiter Name'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Recruiter Category'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Vintage'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Range Id'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Designation'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('T-1 Submissions'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('T-2 Submissions'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Agent Salary'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('5-7k'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('7-10k'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('10-15k'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('15k+'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('X'.$indexCounter, strtoupper('SUBMISSION TO BOOKING'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('JOURNEY TO BOOKING'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('JOURNEY TO SUBMISSION'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('NO HIT'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('THICK'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter, strtoupper('THIN A'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AD'.$indexCounter, strtoupper('THIN B'))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AE'.$indexCounter, strtoupper('Not Captured'))->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AF'.$indexCounter, strtoupper('DOJ'))->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			$empMoreThanZeroSubmission = array();
			$usedEmp = array();
			$totalSubmission = 0;
			$totalBookingBank = 0;
			$totalBookingMTD = 0;
			$totalLastBooking = 0;
			$totalLastBookingP = 0;
			$totalBooking = 0;
			$t1Total = 0;
			$t2Total = 0;
			$submission5_7total = 0;
			$submission7_10total = 0;
			$submission10_15total = 0;
			$submission15total = 0;
			$totalJourneyValue = 0;
			$totalNOHIT = 0;
			$totalTHICK = 0;
			$totalTHINA = 0;
			$totalTHINB = 0;
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
									$vintageDays = abs(strtotime($end_date_application_CBD_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
					$empMoreThanZeroSubmission[] = $model->emp_id;
					$totalBankBooking = CBDBankMis::select("id")->where("employee_id",$model->emp_id)->whereIn("Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))->whereRaw($whereRawBankCarryForward)->get()->count();
					$totalMTDBooking = BankCBDMTD::select("id")->where("employee_id",$model->emp_id)->whereRaw($whereRawMTD)->get()->count();
					$indexCounter++;
					$totalJourneyValue = $totalJourneyValue+$this->getJourneyCount($model->emp_id,$whereRawBank);
					$totalJourneyValueSingle = $this->getJourneyCount($model->emp_id,$whereRawBank);
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $model->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $this->getEmployeeName($model->emp_id))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $model->agent_code)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $model->team)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $model->total)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->getJourneyCount($model->emp_id,$whereRawBank))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $totalBankBooking)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $totalMTDBooking)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $this->lastMonthBooking($model->emp_id,$start_date_application_CBD_internal))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $this->lastMonthBookingP($model->emp_id,$start_date_application_CBD_internal))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getrecruiterNameCBD($model->emp_id))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->getrecruiterCatCBD($model->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $vintageDays)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter, $this->getDesignation($model->emp_id))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('Q'.$indexCounter, $this->t1Submissions($model->emp_id))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('R'.$indexCounter, $this->t2Submissions($model->emp_id))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('S'.$indexCounter, $this->getAgentSalary($model->emp_id))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+$model->total;
					$totalBookingBank = $totalBookingBank+$totalBankBooking;
					$totalBookingMTD = $totalBookingMTD+$totalMTDBooking;
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($model->emp_id,$start_date_application_CBD_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingP($model->emp_id,$start_date_application_CBD_internal);
					$t1Total = $t1Total+$this->t1Submissions($model->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($model->emp_id);
					if($totalMTDBooking >0)
					{
						$totalBooking = $totalBooking+$totalMTDBooking;
					}
					else
					{
						$totalBooking = $totalBooking+$totalBankBooking;
					}
					if($totalJourneyValueSingle <= 0)
					{
						$journeyToSubmission = 0;
					}
					else
					{
						$journeyToSubmission = round(($model->total/$totalJourneyValueSingle),2);
					}
					$sheet->setCellValue('X'.$indexCounter,$this->getApprovalRate($model->total,$totalBankBooking,$totalMTDBooking))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sheet->setCellValue('Y'.$indexCounter,$this->getApprovalRate($totalJourneyValueSingle,$totalBankBooking,$totalMTDBooking))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sheet->setCellValue('Z'.$indexCounter,$journeyToSubmission)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					/*
					*handle salary distribution
					*start code
					*/
						$salaryDistributionList = $this->getSubmissionDistributionAsperSalary($model->emp_id,$whereRawBank);
						
						$submission5_7 = @round(($salaryDistributionList['5-7']/$totalJourneyValueSingle),2);
						$submission7_10 = @round(($salaryDistributionList['7-10']/$totalJourneyValueSingle),2);
						$submission10_15 = @round(($salaryDistributionList['10-15']/$totalJourneyValueSingle),2);
						$submission15 = @round(($salaryDistributionList['15']/$totalJourneyValueSingle),2);
						$sheet->setCellValue('T'.$indexCounter, $submission5_7)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('U'.$indexCounter, $submission7_10)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('V'.$indexCounter, $submission10_15)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('W'.$indexCounter, $submission15)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$submission5_7total = $submission5_7total+$salaryDistributionList['5-7'];
						$submission7_10total = $submission7_10total+$salaryDistributionList['7-10'];
						$submission10_15total = $submission10_15total+$salaryDistributionList['10-15'];
						$submission15total = $submission15total+$salaryDistributionList['15'];
					
					/*
					*handle salary distribution
					*end code
					*/
					$aecbStatusArray = $this->getAECBStatusOfEmp($model->emp_id,$whereRawBank);
					if($totalJourneyValueSingle <= 0)
					{
						$noHit = 0;
						$THICK = 0;
						$THINA = 0;
						$THINB = 0;
						$NotCaptured = 0;
					}
					else
					{
						$noHit = round(($aecbStatusArray['NO HIT']/$totalJourneyValueSingle),2);
						$THICK = round(($aecbStatusArray['THICK']/$totalJourneyValueSingle),2);
						$THINA = round(($aecbStatusArray['THIN A']/$totalJourneyValueSingle),2);
						$THINB = round(($aecbStatusArray['THIN B']/$totalJourneyValueSingle),2);
						$NotCaptured = round(($aecbStatusArray['Not Captured']/$totalJourneyValueSingle),2);
					}
					$sheet->setCellValue('AA'.$indexCounter,$noHit)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AB'.$indexCounter,$THICK)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AC'.$indexCounter,$THINA)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AD'.$indexCounter,$THINB)->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AE'.$indexCounter,$NotCaptured)->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$totalNOHIT = $totalNOHIT+$aecbStatusArray['NO HIT'];
					$totalTHICK = $totalTHICK+$aecbStatusArray['THICK'];
					$totalTHINA = $totalTHINA+$aecbStatusArray['THIN A'];
					$totalTHINB = $totalTHINB+$aecbStatusArray['THIN B'];
					$totalNotCaptured = $totalNotCaptured+$aecbStatusArray['Not Captured'];
					$sheet->setCellValue('AF'.$indexCounter,$model->doj)->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				}
			}
			/*
			*adding Sales Agent with zero Submission
			*Start Coding
			*/
				$empwithZeroSubmission = Employee_details::where("dept_id",49)
								->whereNotIn("emp_id",$empMoreThanZeroSubmission)
								->where("job_function",2)
								->get();
				/* echo "<pre>";
				print_r($empwithZeroSubmission);
				exit; */
				foreach($empwithZeroSubmission as $zeroSubmission)
				{
					if($zeroSubmission->offline_status != 1)
					{
						
					$offlineEmp = DB::table('offline_empolyee_details')->whereRaw("emp_id='".$zeroSubmission->emp_id."' AND last_working_day_resign>='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'")->get();
					
					/*
					*check Emp exist in last submission
					*/
					$previousdate =  date('Y-m-d', strtotime($start_date_application_CBD_internal." -2 month"));
					$pYear = date("Y",strtotime($previousdate));
					$pMonth = date("m",strtotime($previousdate));
					$startDate = $pYear."-".$pMonth."-01";
					$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
					$endDate = $pYear."-".$pMonth."-".$d;
					$totalBankBooking = CBDBankMis::select("id")->where("employee_id",$zeroSubmission->emp_id)->whereIn("Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))->whereBetween("approval_date",[$startDate,$endDate])->get()->count();
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
									$vintageDays = abs(strtotime($end_date_application_CBD_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
						if(strtotime($doj) <= strtotime($end_date_application_CBD_internal) && $doj != '')
						{
					$indexCounter++;
					$totalJourneyValue = $totalJourneyValue+$this->getJourneyCount($zeroSubmission->emp_id,$whereRawBank);
					$totalJourneyValueSingle = $this->getJourneyCount($zeroSubmission->emp_id,$whereRawBank);
					$totalBankBooking = CBDBankMis::select("id")->where("employee_id",$zeroSubmission->emp_id)->whereIn("Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))->whereRaw($whereRawBankCarryForward)->get()->count();
					$totalMTDBooking = BankCBDMTD::select("id")->where("employee_id",$zeroSubmission->emp_id)->whereRaw($whereRawMTD)->get()->count();
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $zeroSubmission->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $zeroSubmission->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $zeroSubmission->source_code)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $this->getTLName($zeroSubmission->emp_id))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, 0)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->getJourneyCount($zeroSubmission->emp_id,$whereRawBank))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $totalBankBooking)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $totalMTDBooking)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_CBD_internal))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_CBD_internal))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getrecruiterNameCBD($zeroSubmission->emp_id))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->getrecruiterCatCBD($zeroSubmission->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $vintageDays)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter, $this->getDesignation($zeroSubmission->emp_id))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('Q'.$indexCounter, $this->t1Submissions($zeroSubmission->emp_id))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('R'.$indexCounter, $this->t2Submissions($zeroSubmission->emp_id))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('S'.$indexCounter, $this->getAgentSalary($zeroSubmission->emp_id))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+0;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_CBD_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_CBD_internal);
					$t1Total = $t1Total+$this->t1Submissions($zeroSubmission->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($zeroSubmission->emp_id);	
					$sheet->setCellValue('X'.$indexCounter,"0")->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sheet->setCellValue('Y'.$indexCounter,"0")->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sheet->setCellValue('Z'.$indexCounter,"0")->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
						
							$totalBookingBank = $totalBookingBank+$totalBankBooking;
							$totalBookingMTD = $totalBookingMTD+$totalMTDBooking;
							if($totalMTDBooking >0)
							{
								$totalBooking = $totalBooking+$totalMTDBooking;
							}
							else
							{
								$totalBooking = $totalBooking+$totalBankBooking;
							}
						}
						
						$salaryDistributionList = $this->getSubmissionDistributionAsperSalary($zeroSubmission->emp_id,$whereRawBank);
						if($totalJourneyValueSingle <= 0)
						{
							$submission5_7 = 0;
						$submission7_10 = 0;
						$submission10_15 = 0;
						$submission15 = 0;
						}
						else
						{
						$submission5_7 = @round(($salaryDistributionList['5-7']/$totalJourneyValueSingle),2);
						$submission7_10 = @round(($salaryDistributionList['7-10']/$totalJourneyValueSingle),2);
						$submission10_15 = @round(($salaryDistributionList['10-15']/$totalJourneyValueSingle),2);
						$submission15 = @round(($salaryDistributionList['15']/$totalJourneyValueSingle),2);
						}
						$sheet->setCellValue('T'.$indexCounter, $submission5_7)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('U'.$indexCounter, $submission7_10)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('V'.$indexCounter, $submission10_15)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('W'.$indexCounter, $submission15)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$submission5_7total = $submission5_7total+$salaryDistributionList['5-7'];
						$submission7_10total = $submission7_10total+$salaryDistributionList['7-10'];
						$submission10_15total = $submission10_15total+$salaryDistributionList['10-15'];
						$submission15total = $submission15total+$salaryDistributionList['15'];
						$aecbStatusArray = $this->getAECBStatusOfEmp($zeroSubmission->emp_id,$whereRawBank);
					if($totalJourneyValueSingle <= 0)
					{
						$noHit = 0;
						$THICK = 0;
						$THINA = 0;
						$THINB = 0;
						$NotCaptured = 0;
					}
					else
					{
						$noHit = round(($aecbStatusArray['NO HIT']/$totalJourneyValueSingle),2);
						$THICK = round(($aecbStatusArray['THICK']/$totalJourneyValueSingle),2);
						$THINA = round(($aecbStatusArray['THIN A']/$totalJourneyValueSingle),2);
						$THINB = round(($aecbStatusArray['THIN B']/$totalJourneyValueSingle),2);
						$NotCaptured = round(($aecbStatusArray['Not Captured']/$totalJourneyValueSingle),2);
						
					}
					$sheet->setCellValue('AA'.$indexCounter,$noHit)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AB'.$indexCounter,$THICK)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AC'.$indexCounter,$THINA)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AD'.$indexCounter,$THINB)->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AE'.$indexCounter,$NotCaptured)->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$totalNOHIT = $totalNOHIT+$aecbStatusArray['NO HIT'];
					$totalTHICK = $totalTHICK+$aecbStatusArray['THICK'];
					$totalTHINA = $totalTHINA+$aecbStatusArray['THIN A'];
					$totalTHINB = $totalTHINB+$aecbStatusArray['THIN B'];
					$totalNotCaptured = $totalNotCaptured+$aecbStatusArray['Not Captured'];
						$sheet->setCellValue('AF'.$indexCounter,$zeroSubmission->doj)->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					}
					else
					{
						continue;
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
									$vintageDays = abs(strtotime($end_date_application_CBD_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
						if(strtotime($doj) <= strtotime($end_date_application_CBD_internal) && $doj != '')
						{
							$usedEmp[] = $zeroSubmission->emp_id;
					$indexCounter++;
					$totalJourneyValue = $totalJourneyValue+$this->getJourneyCount($zeroSubmission->emp_id,$whereRawBank);
					$totalJourneyValueSingle = $this->getJourneyCount($zeroSubmission->emp_id,$whereRawBank);
					$totalBankBooking = CBDBankMis::select("id")->where("employee_id",$zeroSubmission->emp_id)->whereIn("Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))->whereRaw($whereRawBankCarryForward)->get()->count();
					$totalMTDBooking = BankCBDMTD::select("id")->where("employee_id",$zeroSubmission->emp_id)->whereRaw($whereRawMTD)->get()->count();
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $zeroSubmission->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $zeroSubmission->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $zeroSubmission->source_code)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $this->getTLName($zeroSubmission->emp_id))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, 0)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->getJourneyCount($zeroSubmission->emp_id,$whereRawBank))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $totalBankBooking)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $totalMTDBooking)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_CBD_internal))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_CBD_internal))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getrecruiterNameCBD($zeroSubmission->emp_id))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->getrecruiterCatCBD($zeroSubmission->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $vintageDays)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter, $this->getDesignation($zeroSubmission->emp_id))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('Q'.$indexCounter, $this->t1Submissions($zeroSubmission->emp_id))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('R'.$indexCounter, $this->t2Submissions($zeroSubmission->emp_id))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('S'.$indexCounter, $this->getAgentSalary($zeroSubmission->emp_id))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+0;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($zeroSubmission->emp_id,$start_date_application_CBD_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingP($zeroSubmission->emp_id,$start_date_application_CBD_internal);
					$t1Total = $t1Total+$this->t1Submissions($zeroSubmission->emp_id);
					$t2Total = $t2Total+$this->t2Submissions($zeroSubmission->emp_id);	
					$sheet->setCellValue('X'.$indexCounter,"0")->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sheet->setCellValue('Y'.$indexCounter,"0")->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sheet->setCellValue('Z'.$indexCounter,"0")->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
													$totalBookingBank = $totalBookingBank+$totalBankBooking;
							$totalBookingMTD = $totalBookingMTD+$totalMTDBooking;
							if($totalMTDBooking >0)
							{
								$totalBooking = $totalBooking+$totalMTDBooking;
							}
							else
							{
								$totalBooking = $totalBooking+$totalBankBooking;
							}

						}
						
						$salaryDistributionList = $this->getSubmissionDistributionAsperSalary($zeroSubmission->emp_id,$whereRawBank);
						if($totalJourneyValueSingle <= 0)
						{
							$submission5_7 = 0;
						$submission7_10 = 0;
						$submission10_15 = 0;
						$submission15 = 0;
						}
						else
						{
						$submission5_7 = @round(($salaryDistributionList['5-7']/$totalJourneyValueSingle),2);
						$submission7_10 = @round(($salaryDistributionList['7-10']/$totalJourneyValueSingle),2);
						$submission10_15 = @round(($salaryDistributionList['10-15']/$totalJourneyValueSingle),2);
						$submission15 = @round(($salaryDistributionList['15']/$totalJourneyValueSingle),2);
						}
						$sheet->setCellValue('T'.$indexCounter, $submission5_7)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('U'.$indexCounter, $submission7_10)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('V'.$indexCounter, $submission10_15)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('W'.$indexCounter, $submission15)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$submission5_7total = $submission5_7total+$salaryDistributionList['5-7'];
						$submission7_10total = $submission7_10total+$salaryDistributionList['7-10'];
						$submission10_15total = $submission10_15total+$salaryDistributionList['10-15'];
						$submission15total = $submission15total+$salaryDistributionList['15'];
						$aecbStatusArray = $this->getAECBStatusOfEmp($zeroSubmission->emp_id,$whereRawBank);
					if($totalJourneyValueSingle <= 0)
					{
						$noHit = 0;
						$THICK = 0;
						$THINA = 0;
						$THINB = 0;
						$NotCaptured = 0;
					}
					else
					{
						$noHit = round(($aecbStatusArray['NO HIT']/$totalJourneyValueSingle),2);
						$THICK = round(($aecbStatusArray['THICK']/$totalJourneyValueSingle),2);
						$THINA = round(($aecbStatusArray['THIN A']/$totalJourneyValueSingle),2);
						$THINB = round(($aecbStatusArray['THIN B']/$totalJourneyValueSingle),2);
						$NotCaptured = round(($aecbStatusArray['Not Captured']/$totalJourneyValueSingle),2);
						
					}
					$sheet->setCellValue('AA'.$indexCounter,$noHit)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AB'.$indexCounter,$THICK)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AC'.$indexCounter,$THINA)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AD'.$indexCounter,$THINB)->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AE'.$indexCounter,$NotCaptured)->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$totalNOHIT = $totalNOHIT+$aecbStatusArray['NO HIT'];
					$totalTHICK = $totalTHICK+$aecbStatusArray['THICK'];
					$totalTHINA = $totalTHINA+$aecbStatusArray['THIN A'];
					$totalTHINB = $totalTHINB+$aecbStatusArray['THIN B'];
					$totalNotCaptured = $totalNotCaptured+$aecbStatusArray['Not Captured'];
						$sheet->setCellValue('AF'.$indexCounter,$zeroSubmission->doj)->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					}
					
				}
				
				/* echo "<pre>";
				print_r($usedEmp);
				exit; */
				/*
				*add missing Emp
				*/
				$previousdateMissingEmp =  date('Y-m-d', strtotime($start_date_application_CBD_internal." -2 month"));
				$pYearMissing = date("Y",strtotime($previousdateMissingEmp));
				$pMonthMissing = date("m",strtotime($previousdateMissingEmp));
				$startDateMissing = $pYearMissing."-".$pMonthMissing."-01";
				
				
				 $collectionModelMissing = DepartmentFormEntry::selectRaw('emp_id,team')
												  ->groupBy('emp_id')
												  ->whereDate('approval_date', '>=', $startDateMissing)
												  ->whereNotIn('emp_id',$usedEmp)
												  ->where("form_id",2)
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
									$vintageDays = abs(strtotime($end_date_application_CBD_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
					$indexCounter++;
					$totalJourneyValue = $totalJourneyValue+0;
					$totalJourneyValueSingle = 0;
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('B'.$indexCounter, $missing->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $missing->emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $missing->source_code)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $this->getTLName($missing->emp_id))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, 0)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, 0)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter,0)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, 0)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $this->lastMonthBooking($missing->emp_id,$start_date_application_CBD_internal))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('K'.$indexCounter, $this->lastMonthBookingP($missing->emp_id,$start_date_application_CBD_internal))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('L'.$indexCounter, $this->getrecruiterNameCBD($missing->emp_id))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('M'.$indexCounter, $this->getrecruiterCatCBD($missing->emp_id))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('N'.$indexCounter, $vintageDays)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('O'.$indexCounter, $this->getRangeIdData($vintageDays))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter, $this->getDesignation($missing->emp_id))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('Q'.$indexCounter, $this->t1Submissions($missing->emp_id))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('R'.$indexCounter, $this->t2Submissions($missing->emp_id))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('S'.$indexCounter, $this->getAgentSalary($missing->emp_id))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sn++;
					$totalSubmission = $totalSubmission+0;
					
					$totalLastBooking = $totalLastBooking+$this->lastMonthBooking($missing->emp_id,$start_date_application_CBD_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingP($missing->emp_id,$start_date_application_CBD_internal);
					$t1Total = $t1Total+0;
					$t2Total = $t2Total+0;	
					$sheet->setCellValue('X'.$indexCounter,"0")->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sheet->setCellValue('Y'.$indexCounter,"0")->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sheet->setCellValue('Z'.$indexCounter,"0")->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
						
							$totalBookingBank = $totalBookingBank+0;
							$totalBookingMTD = $totalBookingMTD+0;
							if($totalMTDBooking >0)
							{
								$totalBooking = $totalBooking+0;
							}
							else
							{
								$totalBooking = $totalBooking+0;
							}
						
						
						
						$sheet->setCellValue('T'.$indexCounter, 0)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('U'.$indexCounter, 0)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('V'.$indexCounter, 0)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('W'.$indexCounter, 0)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$submission5_7total = $submission5_7total+0;
						$submission7_10total = $submission7_10total+0;
						$submission10_15total = $submission10_15total+0;
						$submission15total = $submission15total+0;
						
					$sheet->setCellValue('AA'.$indexCounter,0)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AB'.$indexCounter,0)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AC'.$indexCounter,0)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AD'.$indexCounter,0)->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('AE'.$indexCounter,0)->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$totalNOHIT = $totalNOHIT+0;
					$totalTHICK = $totalTHICK+0;
					$totalTHINA = $totalTHINA+0;
					$totalTHINB = $totalTHINB+0;
					$totalNotCaptured = $totalNotCaptured+0;
						$sheet->setCellValue('AF'.$indexCounter,$missing->doj)->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				}
				/*
				*add missing Emp
				*/
			/*
			*adding Sales Agent with zero Submission
			*Start Coding
			*/
			$indexCounter = $indexCounter+2;
			$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter.':AE'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			$sheet->setCellValue('C'.$indexCounter, "Total")->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, $totalSubmission)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, $totalJourneyValue)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, $totalBookingBank)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, $totalBookingMTD)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, $totalLastBooking)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, $totalLastBookingP)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, $t1Total)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, $t2Total)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			 
			$approvalRateALL =  @round(($totalBooking/$totalSubmission),2);
			$approvalRateALLJourney =  @round(($totalBooking/$totalJourneyValue),2);
			$total_submission_journey = @round(($totalSubmission/$totalJourneyValue),2);
			$sheet->setCellValue('X'.$indexCounter,$approvalRateALL)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter,$approvalRateALLJourney)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter,$total_submission_journey)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			if($totalJourneyValue <=0)
			{
				$total5_7 = 0;
			$total7_10 = 0;
			$total10_15 = 0;
			$total15 = 0;
			}
			else
			{
			$total5_7 = @round(($submission5_7total/$totalJourneyValue),2);
			$total7_10 = @round(($submission7_10total/$totalJourneyValue),2);
			$total10_15 = @round(($submission10_15total/$totalJourneyValue),2);
			$total15 = @round(($submission15total/$totalJourneyValue),2);
			}
			$sheet->setCellValue('T'.$indexCounter,$total5_7)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter,$total7_10)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter,$total10_15)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter,$total15)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					if($totalJourneyValue <= 0)
					{
						$totalNOHITValue = 0;
						$totalTHICKValue = 0;
						$totalTHINAValue = 0;
						$totalTHINBValue = 0;
						$totalNotCapturedValue = 0;
					}
					else
					{
						$totalNOHITValue = round(($totalNOHIT/$totalJourneyValue),2);
						$totalTHICKValue = round(($totalTHICK/$totalJourneyValue),2);
						$totalTHINAValue = round(($totalTHINA/$totalJourneyValue),2);
						$totalTHINBValue = round(($totalTHINB/$totalJourneyValue),2);
						$totalNotCapturedValue = round(($totalNotCaptured/$totalJourneyValue),2);
					}
			
			$sheet->setCellValue('AA'.$indexCounter,$totalNOHITValue)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter,$totalTHICKValue)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter,$totalTHINAValue)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AD'.$indexCounter,$totalTHINBValue)->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
			$sheet->setCellValue('AE'.$indexCounter,$totalNotCapturedValue)->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
			
			$indexCounter++;
		$missingMTD = $this->totalMissingEmployeeMTD($start_date_application_CBD_internal);
	$sheet->setCellValue('I'.$indexCounter,"Missing Emp MTD")->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
			$sheet->setCellValue('J'.$indexCounter,$missingMTD)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
			
			
			for($col = 'A'; $col !== 'AF'; $col++) {
					$sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
				/*
				*color all coloum
				*/
				$indexList = $indexCounter-3;
				$spreadsheet->getActiveSheet()->getStyle('H5:H'.$indexList)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('fce5ae');
				$spreadsheet->getActiveSheet()->getStyle('J5:J'.$indexList)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('de9f28');
				$spreadsheet->getActiveSheet()->getStyle('K5:K'.$indexList)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('e8ef0d');
				/*
				*color all coloum
				*/
			
					$spreadsheet->getActiveSheet()->getStyle('A1:R2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
					$spreadsheet->getActiveSheet()->getStyle('S1:V2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('c0b698');
					
					$spreadsheet->getActiveSheet()->getStyle('W1:Y2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('acdbf7');
					$spreadsheet->getActiveSheet()->getStyle('Z1:AD2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('f1edb0');
				$spreadsheet->getActiveSheet()->getStyle('AE1:AF2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('acdbf7');
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AF') as $col) {
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
				$this->sheet2Performance($spreadsheet,$whereRaw,$whereRawBank,$whereRawBankCarryForward,$whereRawMTD,$start_date_application_CBD_internal,$end_date_application_CBD_internal);
				$spreadsheet->createSheet(2); 
				$spreadsheet->setActiveSheetIndex(2); 
				$spreadsheet->getActiveSheet()->setTitle('Flag Details');
				/*
				*Sheet3
				*/
				$this->sheet3FlagDetails($spreadsheet,$start_date_application_CBD_internal,$end_date_application_CBD_internal);
	$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="CBD-Final_Report";					
				$logObj->save();
					$writer = new Xlsx($spreadsheet);
					$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
			
			
			
	}
	
	protected function sheet3FlagDetails($spreadsheet,$start_date_application_CBD_internal,$end_date_application_CBD_internal)
	{
		$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:H1');
			$sheet->setCellValue('A1', "Flag Details")->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;			
			
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Employee ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');


			$selectedIdPre = DB::table('master_payout_pre')->whereRaw("dept_id = '49' AND agent_product='Card'")->limit(1)->orderby('sort_order','DESC')->groupBy('sales_time')->get(['sort_order','sales_time','range_id']);
			
			$max_sort_order = $selectedIdPre[0]->sort_order;
			$max_sales_time = $selectedIdPre[0]->sales_time;

			$check_data = MasterPayout::select("id")->where("dept_id",'49')->whereRaw("sort_order='".$max_sort_order."'")->get()->count();

			if($check_data>0)
			{

				$selectedId = DB::table('master_payout')->whereRaw("dept_id = '49' AND agent_product_id='1'")->limit(3)->orderby('sort_order','DESC')->groupBy('sales_time')->get(['sales_time','sort_order','range_id']);
				
				$k=1;
				$rangeIndex = 1;
				$sort_orders = '';
				foreach ($selectedId as $mis) 
				{
					if($k==1)
					{
						$col='C';
						$colrange='F';
					}
					if($k==2)
					{
						$col='D';
						$colrange='G';
					}
					if($k==3)
					{
						$col='E';
						$colrange='H';
					}

					$sort_orders .= $mis->sort_order.',';

					$sheet->setCellValue($col.$indexCounter, $mis->sales_time)->getStyle($col.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue($colrange.$indexCounter, strtoupper('Range ID -'.$mis->sales_time))->getStyle($colrange.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
 
					$k++;
				}

				/* $sheet->setCellValue('F'.$indexCounter, strtoupper('Range ID'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
 */
				$sheet->setCellValue('I'.$indexCounter, strtoupper('DOJ'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, strtoupper('Agent Salary'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sort_orders = substr($sort_orders,0,-1);
			
				
				$selectedEmp = DB::table('master_payout')->whereRaw("dept_id = '49' AND agent_product_id='1' AND (employee_id!='' OR employee_id IS NOT NULL) AND employee_id NOT LIKE '%,%' AND employee_id NOT LIKE '%.%' AND sort_order IN (".$sort_orders.")")->groupBy('employee_id')->get(['employee_id','agent_name','range_id','doj']);
				$sn = 1;

				$exp_sort_orders = explode(",",$sort_orders);
				
				foreach ($selectedEmp as $selectedEmpData) 
				{
				
					
					 $indexCounter++; 					
					
					$sheet->setCellValue('A'.$indexCounter, $selectedEmpData->employee_id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
					

					$sheet->setCellValue('B'.$indexCounter, $selectedEmpData->agent_name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$FirstData = DB::table('master_payout')->whereRaw("dept_id = '49' AND sort_order ='".$exp_sort_orders[0]."' AND employee_id='".$selectedEmpData->employee_id."' AND agent_product_id='1'")->get(['tc','flag_rule_name','range_id']);	
					

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
						$sheet->setCellValue('F'.$indexCounter, $FirstDataVal->range_id)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					}

					$SecondData = DB::table('master_payout')->whereRaw("dept_id = '49' AND sort_order ='".$exp_sort_orders[1]."' AND employee_id='".$selectedEmpData->employee_id."' AND agent_product_id='1'")->get(['tc','flag_rule_name','range_id']);				

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
						$sheet->setCellValue('G'.$indexCounter, $SecondDataVal->range_id)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					}

					$ThirdData = DB::table('master_payout')->whereRaw("dept_id = '49' AND sort_order ='".$exp_sort_orders[2]."' AND employee_id='".$selectedEmpData->employee_id."' AND agent_product_id='1'")->get(['tc','flag_rule_name','range_id']);				

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
						$sheet->setCellValue('H'.$indexCounter, $ThirdDataVal->range_id)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

						$spreadsheet->getActiveSheet()->getStyle('E'.$indexCounter.':'.'E'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
					}

					
					$sheet->setCellValue('I'.$indexCounter, $selectedEmpData->doj)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$agentT = DB::table('master_payout')->where("dept_id",49)->where("employee_id",$selectedEmpData->employee_id)->orderby("sort_order","DESC")->first()->agent_target;
					if($agentT != NULL && $agentT!= '')
					{
					$salaryMod = SalaryStruture::where("bank_id",49)->where("target",trim($agentT))->first();
					if($salaryMod != '')
					{
					$sheet->setCellValue('J'.$indexCounter, $salaryMod->salary)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					}
					}
					


					
					$sn++;
					
				}
			}
			else
			{
				$sheet->setCellValue('C'.$indexCounter, $max_sales_time)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, strtoupper('Range ID -'.$max_sales_time))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');


				$selectedId = DB::table('master_payout')->whereRaw("dept_id = '49' AND agent_product_id='1'")->limit(2)->orderby('sort_order','DESC')->groupBy('sales_time')->get(['sales_time','sort_order','range_id']);
				
				$k=2;
				$sort_orders = '';
				foreach ($selectedId as $mis) 
				{					
					if($k==2)
					{
						$col='D';
						$colRange='G';
					}
					if($k==3)
					{
						$col='E';
						$colRange='H';
					}

					$sort_orders .= $mis->sort_order.',';

					$sheet->setCellValue($col.$indexCounter, $mis->sales_time)->getStyle($col.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue($colRange.$indexCounter, strtoupper('Range ID -'.$mis->sales_time))->getStyle($colRange.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$k++;
				}

				
				$sheet->setCellValue('I'.$indexCounter, strtoupper('DOJ'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, strtoupper('Agent Target'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sort_orders = substr($sort_orders,0,-1);
			
				
				$selectedEmp = DB::table('master_payout')->whereRaw("dept_id = '49' AND agent_product_id='1' AND (employee_id!='' OR employee_id IS NOT NULL) AND employee_id NOT LIKE '%,%' AND employee_id NOT LIKE '%.%' AND sort_order IN (".$sort_orders.")")->groupBy('employee_id')->get(['employee_id','agent_name','range_id','doj']);
				$sn = 1;

				$exp_sort_orders = explode(",",$sort_orders);
				
				foreach ($selectedEmp as $selectedEmpData) 
				{
				
					
					 $indexCounter++; 					
					
					$sheet->setCellValue('A'.$indexCounter, $selectedEmpData->employee_id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
					

					$sheet->setCellValue('B'.$indexCounter, $selectedEmpData->agent_name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$FirstData = DB::table('master_payout_pre')->whereRaw("dept_id = '49' AND sort_order ='".$max_sort_order."' AND agent_id='".$selectedEmpData->employee_id."' AND agent_product='Card'")->get(['tc','flag_rule_name','range_id']);	
					

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
						$sheet->setCellValue('F'.$indexCounter, $FirstDataVal->range_id)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

						$spreadsheet->getActiveSheet()->getStyle('C'.$indexCounter.':'.'C'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
					}

					$SecondData = DB::table('master_payout')->whereRaw("dept_id = '49' AND agent_product_id='1' AND sort_order ='".$exp_sort_orders[0]."' AND employee_id='".$selectedEmpData->employee_id."'")->get(['tc','flag_rule_name','range_id']);				

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
						$sheet->setCellValue('G'.$indexCounter, $SecondDataVal->range_id)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

						$spreadsheet->getActiveSheet()->getStyle('D'.$indexCounter.':'.'D'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
					}

					$ThirdData = DB::table('master_payout')->whereRaw("dept_id = '49' AND agent_product_id='1' AND sort_order ='".$exp_sort_orders[1]."' AND employee_id='".$selectedEmpData->employee_id."'")->get(['tc','flag_rule_name','range_id']);				

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
$sheet->setCellValue('H'.$indexCounter, $ThirdDataVal->range_id)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$spreadsheet->getActiveSheet()->getStyle('E'.$indexCounter.':'.'E'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
					}

					/* $sheet->setCellValue('F'.$indexCounter, $selectedEmpData->range_id)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
 */
					$sheet->setCellValue('I'.$indexCounter, $selectedEmpData->doj)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$agentT = DB::table('master_payout')->where("dept_id",49)->where("employee_id",$selectedEmpData->employee_id)->orderby("sort_order","DESC")->first()->agent_target;
					if($agentT != NULL && $agentT!= '')
					{
					$salaryMod = SalaryStruture::where("bank_id",49)->where("target",trim($agentT))->first();
					if($salaryMod != '')
					{
					$sheet->setCellValue('J'.$indexCounter, $salaryMod->salary)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					}
					}
					

					


					
					$sn++;
					
				}

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
	
	
	protected function sheet2Performance($spreadsheet,$whereRaw,$whereRawBank,$whereRawBankCarryForward,$whereRawMTD,$start_date_application_CBD_internal,$end_date_application_CBD_internal)
	{
			/*
			*-1,-2 month Name
			*start code
			*/
			$previousMonthName =  date('M-Y', strtotime(date($start_date_application_CBD_internal)." -1 month"));
			$previousMonthName1 =  date('M-Y', strtotime(date($start_date_application_CBD_internal)." -2 month"));
			/*
			*-1,-2 month Name
			*end code
			*/
			$collectionModel = DepartmentFormEntry::selectRaw('count(*) as total,team')
												  ->groupBy('team')
												  ->whereRaw($whereRaw)
												  ->get();
												  
												  
			
				$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:I2');
			$sheet->setCellValue('A1', 'TL Performance CBD Cards - from -'.date("d M Y",strtotime($start_date_application_CBD_internal)).'to -'.date("d M Y",strtotime($end_date_application_CBD_internal)))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->mergeCells('J1:M2');
			$sheet->setCellValue('J1', 'Income Segmentation')->getStyle('J1')->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->mergeCells('N1:Q2');
			$sheet->setCellValue('N1', 'Approval Rate')->getStyle('N1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->mergeCells('R1:U2');
			$sheet->setCellValue('R1', 'AECB Status')->getStyle('R1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 5;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('SM Manager'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Total Submissions'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Journey'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Total Booking As Per Bank MIS'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Total Booking As Per MTD MIS'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName.')'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Last Month Booking('.$previousMonthName1.')'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('T-1 Submissions'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('T-2 Submissions'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('5-7k'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('7-10k'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('10-15k'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('15k+'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Submission to Booking'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Journey to Booking'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Journey to Submission'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('NO HIT'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('THICK'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('THIN A'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('THIN B'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('Not Captured'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
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
			$submission5_7total = 0;
			$submission7_10total = 0;
			$submission10_15total = 0;
			$submission15total = 0;
			$totalJourneyValue = 0;
			$totalNOHIT = 0;
			$totalTHICK = 0;
			$totalTHINA = 0;
			$totalTHINB = 0;
			$totalNotCaptured = 0;
			$journey_to_submission_total = 0;
			$teamName = array();
			$teamValue = array();
				foreach ($collectionModel as $model) {
				if($model->team != '')
				{
				
					
					$teamValue[] = $model->team;
					$totalBankBooking = CBDBankMis::select("id")->where("sm_manager",$model->team)->whereIn("Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))->whereRaw($whereRawBankCarryForward)->get()->count();
					$totalMTDBooking = BankCBDMTD::select("id")->where("sm_manager",$model->team)->whereRaw($whereRawMTD)->get()->count();
					$indexCounter++;
					$totalJourneyValue = $totalJourneyValue+$this->getJourneyCountTeam($model->team,$whereRawBank);
					$totalJourneyValueSingle = $this->getJourneyCountTeam($model->team,$whereRawBank);
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sheet->setCellValue('B'.$indexCounter, $model->team)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, $model->total)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, $this->getJourneyCountTeam($model->team,$whereRawBank))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, $totalBankBooking)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, $totalMTDBooking)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBookingTeam($model->team,$start_date_application_CBD_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->lastMonthBookingTeamP($model->team,$start_date_application_CBD_internal))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, $this->t1SubmissionsTeam($model->team))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, $this->t2SubmissionsTeam($model->team))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$totalSubmission = $totalSubmission+$model->total;
					$totalBookingBank = $totalBookingBank+$totalBankBooking;
					$totalBookingMTD = $totalBookingMTD+$totalMTDBooking;
					$totalLastBooking = $totalLastBooking+$this->lastMonthBookingTeam($model->team,$start_date_application_CBD_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingTeamP($model->team,$start_date_application_CBD_internal);
					
					$t1Total = $t1Total+$this->t1SubmissionsTeam($model->team);
					$t2Total = $t2Total+$this->t2SubmissionsTeam($model->team);
					if($totalMTDBooking >0)
					{
						$totalBooking = $totalBooking+$totalMTDBooking;
					}
					else
					{
						$totalBooking = $totalBooking+$totalBankBooking;
					}
					
					/*
					*handle salary distribution
					*start code
					*/
						$salaryDistributionList = $this->getSubmissionDistributionAsperSalaryTeam($model->team,$whereRawBank);
						if($totalJourneyValueSingle <= 0)
						{
							$submission5_7 = 0;
							$submission7_10 = 0;
							$submission10_15 = 0;
							$submission15 = 0;
						}
						else
						{
							$submission5_7 = round(($salaryDistributionList['5-7']/$totalJourneyValueSingle),2);
							$submission7_10 = round(($salaryDistributionList['7-10']/$totalJourneyValueSingle),2);
							$submission10_15 = round(($salaryDistributionList['10-15']/$totalJourneyValueSingle),2);
							$submission15 = round(($salaryDistributionList['15']/$totalJourneyValueSingle),2);
						}
						
						$sheet->setCellValue('K'.$indexCounter, $submission5_7)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('L'.$indexCounter, $submission7_10)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('M'.$indexCounter, $submission10_15)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('N'.$indexCounter, $submission15)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$submission5_7total = $submission5_7total+$salaryDistributionList['5-7'];
						$submission7_10total = $submission7_10total+$salaryDistributionList['7-10'];
						$submission10_15total = $submission10_15total+$salaryDistributionList['10-15'];
						$submission15total = $submission15total+$salaryDistributionList['15'];
					
					/*
					*handle salary distribution
					*end code
					*/
					
					$journey_to_submission = @round(($model->total/$totalJourneyValueSingle),2);
					$sheet->setCellValue('O'.$indexCounter,$this->getApprovalRate($model->total,$totalBankBooking,$totalMTDBooking))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter,$this->getApprovalRate($totalJourneyValueSingle,$totalBankBooking,$totalMTDBooking))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('Q'.$indexCounter,$journey_to_submission)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					$aecbStatusArray = $this->getAECBStatusOfEmpTeam($model->team,$whereRawBank);
					if($totalJourneyValueSingle <=0)
					{
						$noHit = 0;
						$THICK = 0;
						$THINA = 0;
						$THINB = 0;
						$NotCaptured = 0;
					}
					else
					{
						$noHit = round(($aecbStatusArray['NO HIT']/$totalJourneyValueSingle),2);
						$THICK = round(($aecbStatusArray['THICK']/$totalJourneyValueSingle),2);
						$THINA = round(($aecbStatusArray['THIN A']/$totalJourneyValueSingle),2);
						$THINB = round(($aecbStatusArray['THIN B']/$totalJourneyValueSingle),2);
						$NotCaptured = round(($aecbStatusArray['Not Captured']/$totalJourneyValueSingle),2);
					}
					
					$sheet->setCellValue('R'.$indexCounter,$noHit)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('S'.$indexCounter,$THICK)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('T'.$indexCounter,$THINA)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('U'.$indexCounter,$THINB)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('V'.$indexCounter,$NotCaptured)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$totalNOHIT = $totalNOHIT+$aecbStatusArray['NO HIT'];
					$totalTHICK = $totalTHICK+$aecbStatusArray['THICK'];
					$totalTHINA = $totalTHINA+$aecbStatusArray['THIN A'];
					$totalTHINB = $totalTHINB+$aecbStatusArray['THIN B'];
					$totalNotCaptured = $totalNotCaptured+$aecbStatusArray['Not Captured'];
					$sn++;
				}
	}
	/*
	*adding missing team
	*/
		$previousdatePP =  date('Y-m-d', strtotime($start_date_application_CBD_internal." -2 month"));
		$pYearPP = date("Y",strtotime($previousdatePP));
		$pMonthPP = date("m",strtotime($previousdatePP));
	    $startDatePP = $pYearPP."-".$pMonthPP."-01";
		$collectionModelP = DepartmentFormEntry::selectRaw('team')
												  ->groupBy('team')
												  ->whereDate('approval_date','>=',$startDatePP)
												  ->whereNotIn("team",$teamValue)
												  ->get();
	
			foreach ($collectionModelP as $model) {
				if($model->team != '')
				{
				
					
					$teamValue[] = $model->team;
					$indexCounter++;
					$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sheet->setCellValue('B'.$indexCounter, $model->team)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('C'.$indexCounter, 0)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('D'.$indexCounter, 0)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('E'.$indexCounter, 0)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('F'.$indexCounter, 0)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('G'.$indexCounter, $this->lastMonthBookingTeam($model->team,$start_date_application_CBD_internal))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('H'.$indexCounter, $this->lastMonthBookingTeamP($model->team,$start_date_application_CBD_internal))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('I'.$indexCounter, 0)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('J'.$indexCounter, 0)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
				
					$totalLastBooking = $totalLastBooking+$this->lastMonthBookingTeam($model->team,$start_date_application_CBD_internal);
					$totalLastBookingP = $totalLastBookingP+$this->lastMonthBookingTeamP($model->team,$start_date_application_CBD_internal);
					
				
					
					/*
					*handle salary distribution
					*start code
					*/
						
						$sheet->setCellValue('K'.$indexCounter, 0)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('L'.$indexCounter, 0)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('M'.$indexCounter, 0)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('N'.$indexCounter, 0)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						
					/*
					*handle salary distribution
					*end code
					*/
					
					$journey_to_submission = @round(($model->total/$totalJourneyValueSingle),2);
					$sheet->setCellValue('O'.$indexCounter,0)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('P'.$indexCounter,0)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('Q'.$indexCounter,0)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					
					
					
					$sheet->setCellValue('R'.$indexCounter,0)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('S'.$indexCounter,0)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('T'.$indexCounter,0)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('U'.$indexCounter,0)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$sheet->setCellValue('V'.$indexCounter,0)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
					$sn++;
				}
	}									  
	/*
	*adding missing team
	*/
	/**
	*Total Rows
    */
			$indexCounter = $indexCounter+2;
			$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter.':T'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			$sheet->setCellValue('B'.$indexCounter, "Total")->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, $totalSubmission)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, $totalJourneyValue)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, $totalBookingBank)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, $totalBookingMTD)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, $totalLastBooking)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, $totalLastBookingP)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('I'.$indexCounter, $t1Total)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, $t2Total)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			 
			$approvalRateALL =  @round(($totalBooking/$totalSubmission),2);
			$approvalRateALLJourney =  @round(($totalBooking/$totalJourneyValue),2);
			$journey_to_submission = @round(($totalSubmission/$totalJourneyValue),2);
			$sheet->setCellValue('O'.$indexCounter,$approvalRateALL)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter,$approvalRateALLJourney)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter,$journey_to_submission)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					
			if($totalJourneyValue <= 0)
			{
				$total5_7 = 0;
				$total7_10 = 0;
				$total10_15 = 0;
				$total15 = 0;
				
			}
			else
			{
				$total5_7 = round(($submission5_7total/$totalJourneyValue),2);
				$total7_10 = round(($submission7_10total/$totalJourneyValue),2);
				$total10_15 = round(($submission10_15total/$totalJourneyValue),2);
				$total15 = round(($submission15total/$totalJourneyValue),2);
			}
			$sheet->setCellValue('K'.$indexCounter,$total5_7)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter,$total7_10)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter,$total10_15)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter,$total15)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					if($totalJourneyValue <= 0)
					{
						$totalNOHITValue = 0;
						$totalTHICKValue = 0;
						$totalTHINAValue = 0;
						$totalTHINBValue = 0;
						$totalNotCapturedValue = 0;
					}
					else
					{
					$totalNOHITValue = round(($totalNOHIT/$totalJourneyValue),2);
					$totalTHICKValue = round(($totalTHICK/$totalJourneyValue),2);
					$totalTHINAValue = round(($totalTHINA/$totalJourneyValue),2);
					$totalTHINBValue = round(($totalTHINB/$totalJourneyValue),2);
					$totalNotCapturedValue = round(($totalNotCaptured/$totalJourneyValue),2);
					}
			
			$sheet->setCellValue('R'.$indexCounter,$totalNOHITValue)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter,$totalTHICKValue)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter,$totalTHINAValue)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter,$totalTHINBValue)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
			$sheet->setCellValue('V'.$indexCounter,$totalNotCapturedValue)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
	/**
	*Total Rows
	*/
	$indexCounter++;
		$missingMTD = $this->totalMissingEmployeeMTD($start_date_application_CBD_internal);
	$sheet->setCellValue('F'.$indexCounter,"Missing Emp MTD")->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
			$sheet->setCellValue('G'.$indexCounter,$missingMTD)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
			
			for($col = 'A'; $col !== 'V'; $col++) {
					$sheet->getColumnDimension($col)->setAutoSize(true);
			}
			/*
				*color all coloum
				*/
				$indexList = $indexCounter-3;
				$spreadsheet->getActiveSheet()->getStyle('E5:E'.$indexList)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('fce5ae');
				$spreadsheet->getActiveSheet()->getStyle('G5:G'.$indexList)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('de9f28');
				$spreadsheet->getActiveSheet()->getStyle('H5:H'.$indexList)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('e8ef0d');
				/*
				*color all coloum
				*/
					$spreadsheet->getActiveSheet()->getStyle('A1:I2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
					$spreadsheet->getActiveSheet()->getStyle('J1:M2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('c0b698');
					
					$spreadsheet->getActiveSheet()->getStyle('N1:Q2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('acdbf7');
					$spreadsheet->getActiveSheet()->getStyle('R1:V2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('f1edb0');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','V') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
	}
	protected function getAECBStatusOfEmp($empId,$whereRawBank)
	{
		$totalBankdata = CBDBankMis::select("AECB_Status")->where("employee_id",$empId)->whereRaw($whereRawBank)->get();
		$AECB_StatusArray = array(); 
		$AECB_StatusArray['NO HIT'] = 0;
		$AECB_StatusArray['THICK'] = 0;
		$AECB_StatusArray['THIN A'] = 0;
		$AECB_StatusArray['THIN B'] = 0;
		$AECB_StatusArray['Not Captured'] = 0;
		foreach($totalBankdata as $bank)
		{
			
			$AECB_Status = $bank->AECB_Status;
			if($AECB_Status == 'NO HIT')
			{
				$AECB_StatusArray['NO HIT'] = $AECB_StatusArray['NO HIT']+1;
			}
			else if($AECB_Status == 'THICK')
			{
				$AECB_StatusArray['THICK'] = $AECB_StatusArray['THICK']+1;
			}
			else if($AECB_Status == 'THIN A')
			{
				$AECB_StatusArray['THIN A'] = $AECB_StatusArray['THIN A']+1;
			}
			else if($AECB_Status == 'THIN B')
			{
				$AECB_StatusArray['THIN B'] = $AECB_StatusArray['THIN B']+1;
			}
			else
			{
				$AECB_StatusArray['Not Captured'] = $AECB_StatusArray['Not Captured']+1;
			}
		}
		return $AECB_StatusArray;
		
	}
	protected function getJourneyCount($empId,$whereRawBank)
	{
		return $totalJourney = CBDBankMis::select("id")->where("employee_id",$empId)->whereRaw($whereRawBank)->get()->count();
	}
	protected function getSubmissionDistributionAsperSalary($empId,$whereRawBank)
	{
		$totalBankdata = CBDBankMis::select("declared_salary")->where("employee_id",$empId)->whereRaw($whereRawBank)->get();
		$declareSalaryArray = array(); 
		$declareSalaryArray['5-7'] = 0;
		$declareSalaryArray['7-10'] = 0;
		$declareSalaryArray['10-15'] = 0;
		$declareSalaryArray['15'] = 0;
		foreach($totalBankdata as $bank)
		{
			
			$declared_salary = $bank->declared_salary;
			if($declared_salary >= 5000 && $declared_salary < 7000)
			{
				$declareSalaryArray['5-7'] = $declareSalaryArray['5-7']+1;
			}
			else if($declared_salary >= 7000 && $declared_salary < 10000)
			{
				$declareSalaryArray['7-10'] = $declareSalaryArray['7-10']+1;
			}
			else if($declared_salary >= 10000 && $declared_salary < 15000)
			{
				$declareSalaryArray['10-15'] = $declareSalaryArray['10-15']+1;
			}
			else if($declared_salary >= 15000)
			{
				$declareSalaryArray['15'] = $declareSalaryArray['15']+1;
			}
		}
		return $declareSalaryArray;
	}
	
	protected function getApprovalRate($totalSubmission,$booking,$mtd)
	{
		if($mtd >0)
		{
			if($totalSubmission <= 0)
			{
				return 0;
			}
			else
			{
			return round(($mtd/$totalSubmission),2);
			}
		}
		else
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
	protected function t1Submissions($empId)
	{
		$previousDate =  date('Y-m-d', strtotime(' -1 day'));
		return DepartmentFormEntry::select("id")->whereDate("application_date","=",$previousDate)->where("emp_id",$empId)->get()->count();
		
	}
	protected function t2Submissions($empId)
	{
		$endDate =  date('Y-m-d', strtotime(' -1 day'));
		$StartDate =  date('Y-m-d', strtotime(' -2 day'));
		return DepartmentFormEntry::select("id")->whereBetween("application_date",[$StartDate,$endDate])->where("emp_id",$empId)->get()->count();
		
	}
	protected function totalMissingEmployeeMTD($start_date_application_CBD_internal)
	{
			$previousMonth = date("Y-m",strtotime($start_date_application_CBD_internal." -1 month"));
			$monthYearSet = explode("-",$previousMonth);
			$previousMonthData = BankCBDMTD::select("id")->whereMonth("application_date",$monthYearSet[1])->whereYear("application_date",$monthYearSet[0])->get()->count();
			if($previousMonthData > 0)
			{
				return BankCBDMTD::select("id")->whereNULL("employee_id")->whereMonth("CD_OPN_DT",$monthYearSet[1])->whereYear("CD_OPN_DT",$monthYearSet[0])->get()->count();
			}
			else
			{
				return "Not Captured";
			}
	}
	protected function lastMonthBooking($empId,$start_date_application_CBD_internal)
	{
		
		$previousdate =  date('Y-m-d', strtotime($start_date_application_CBD_internal." -1 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		$saleEnd = $pMonth.'-'.$pYear;
		
		/*
		*check master payout first
		*/
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",49)->where("sales_time",$saleEnd)->where("employee_id",$empId)->first();
		if($employeePayoutData != '')
		{
		
			return $employeePayoutData->tc;
		/*
		*check master payout first
		*/		
		}
		else
		{
			
		$previousMonthPayout = date("m-Y",strtotime($start_date_application_CBD_internal." -1 month"));
		
		$employeePayoutDataCount = MasterPayout::select("id")->where("dept_id",49)->where("sales_time",$previousMonthPayout)->get()->count();
		if($employeePayoutDataCount > 0)
		{
			
			return 0;
		}
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		
		$totalMTDBooking = BankCBDMTD::select("id")->where("employee_id",$empId)->whereBetween("CD_OPN_DT",[$startDate,$endDate])->get()->count();
		if($totalMTDBooking == 0)
		{
			$previousMonth = date("Y-m",strtotime($start_date_application_CBD_internal." -1 month"));
			$monthYearSet = explode("-",$previousMonth);
			
			$previousMonthData = BankCBDMTD::select("id")->whereMonth("CD_OPN_DT",$monthYearSet[1])->whereYear("CD_OPN_DT",$monthYearSet[0])->get()->count();
			/* echo $previousMonthData;
			exit; */
			if($previousMonthData > 0)
			{
				
				return 0;
			}
			else
			{
				$totalBankBooking = CBDBankMis::select("id")->where("employee_id",$empId)->whereIn("Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))->whereBetween("approval_date",[$startDate,$endDate])->get()->count();
				return 	$totalBankBooking;
			}
		}
		else
		{
			return $totalMTDBooking;
		}	
		}
		
	}
	
	
	protected function lastMonthBookingP($empId,$start_date_application_CBD_internal)
	{
		
		$previousdate =  date('Y-m-d', strtotime($start_date_application_CBD_internal." -2 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		$saleEnd = $pMonth.'-'.$pYear;
		
		/*
		*check master payout first
		*/
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",49)->where("sales_time",$saleEnd)->where("employee_id",$empId)->first();
		if($employeePayoutData != '')
		{
		
			return $employeePayoutData->tc;
		/*
		*check master payout first
		*/		
		}
		else
		{
			
		$previousMonthPayout = date("m-Y",strtotime($start_date_application_CBD_internal." -2 month"));
		
		$employeePayoutDataCount = MasterPayout::select("id")->where("dept_id",49)->where("sales_time",$previousMonthPayout)->get()->count();
		if($employeePayoutDataCount > 0)
		{
			
			return 0;
		}
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		
		$totalMTDBooking = BankCBDMTD::select("id")->where("employee_id",$empId)->whereBetween("CD_OPN_DT",[$startDate,$endDate])->get()->count();
		if($totalMTDBooking == 0)
		{
			$previousMonth = date("Y-m",strtotime($start_date_application_CBD_internal." -2 month"));
			$monthYearSet = explode("-",$previousMonth);
			
			$previousMonthData = BankCBDMTD::select("id")->whereMonth("CD_OPN_DT",$monthYearSet[1])->whereYear("CD_OPN_DT",$monthYearSet[0])->get()->count();
			/* echo $previousMonthData;
			exit; */
			if($previousMonthData > 0)
			{
				
				return 0;
			}
			else
			{
				$totalBankBooking = CBDBankMis::select("id")->where("employee_id",$empId)->whereIn("Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))->whereBetween("approval_date",[$startDate,$endDate])->get()->count();
				return 	$totalBankBooking;
			}
		}
		else
		{
			return $totalMTDBooking;
		}	
		}
		
	}
	
	protected function lastMonthBookingTeam($team,$start_date_application_CBD_internal)
	{
		$previousdate =  date('Y-m-d', strtotime($start_date_application_CBD_internal." -1 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		$saleEnd = $pMonth.'-'.$pYear;
		
		/*
		*check master payout first
		*/
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",49)->where("sales_time",$saleEnd)->where("tl_name",$team)->get();
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
		
		$totalMTDBooking = BankCBDMTD::select("id")->where("sm_manager",$team)->whereBetween("CD_OPN_DT",[$startDate,$endDate])->get()->count();
		if($totalMTDBooking == 0)
		{
			
			$totalBankBooking = CBDBankMis::select("id")->where("sm_manager",$team)->whereIn("Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))->whereBetween("approval_date",[$startDate,$endDate])->get()->count();
			return 	$totalBankBooking;
		}
		else
		{
			return $totalMTDBooking;
		}	
		}
		
		
	}
	protected function lastMonthBookingTeamP($team,$start_date_application_CBD_internal)
	{
		$previousdate =  date('Y-m-d', strtotime($start_date_application_CBD_internal." -2 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		$saleEnd = $pMonth.'-'.$pYear;
		
		/*
		*check master payout first
		*/
		$employeePayoutData = MasterPayout::select("tc")->where("dept_id",49)->where("sales_time",$saleEnd)->where("tl_name",$team)->get();
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
		
		$totalMTDBooking = BankCBDMTD::select("id")->where("sm_manager",$team)->whereBetween("CD_OPN_DT",[$startDate,$endDate])->get()->count();
		if($totalMTDBooking == 0)
		{
			
			$totalBankBooking = CBDBankMis::select("id")->where("sm_manager",$team)->whereIn("Status",array("Approved","Pending with Onboarder","Pending with COC","Welcome Calling,Archive on Approval","Missing(Approved)"))->whereBetween("approval_date",[$startDate,$endDate])->get()->count();
			return 	$totalBankBooking;
		}
		else
		{
			return $totalMTDBooking;
		}	
		}
		
		
	}
	
	protected function t1SubmissionsTeam($team)
	{
		$previousDate =  date('Y-m-d', strtotime(' -1 day'));
		return DepartmentFormEntry::select("id")->whereDate("application_date","=",$previousDate)->where("team",$team)->get()->count();
		
	}
	protected function t2SubmissionsTeam($team)
	{
		$endDate =  date('Y-m-d', strtotime(' -1 day'));
		$StartDate =  date('Y-m-d', strtotime(' -2 day'));
		return DepartmentFormEntry::select("id")->whereBetween("application_date",[$StartDate,$endDate])->where("team",$team)->get()->count();
		
	}
	protected function getSubmissionDistributionAsperSalaryTeam($team,$whereRawBank)
	{
		$totalBankdata = CBDBankMis::select("declared_salary")->where("sm_manager",$team)->whereRaw($whereRawBank)->get();
		$declareSalaryArray = array(); 
		$declareSalaryArray['5-7'] = 0;
		$declareSalaryArray['7-10'] = 0;
		$declareSalaryArray['10-15'] = 0;
		$declareSalaryArray['15'] = 0;
		foreach($totalBankdata as $bank)
		{
			
			$declared_salary = $bank->declared_salary;
			if($declared_salary >= 5000 && $declared_salary < 7000)
			{
				$declareSalaryArray['5-7'] = $declareSalaryArray['5-7']+1;
			}
			else if($declared_salary >= 7000 && $declared_salary < 10000)
			{
				$declareSalaryArray['7-10'] = $declareSalaryArray['7-10']+1;
			}
			else if($declared_salary >= 10000 && $declared_salary < 15000)
			{
				$declareSalaryArray['10-15'] = $declareSalaryArray['10-15']+1;
			}
			else if($declared_salary >= 15000)
			{
				$declareSalaryArray['15'] = $declareSalaryArray['15']+1;
			}
		}
		return $declareSalaryArray;
	}
	protected function getJourneyCountTeam($team,$whereRawBank)
	{
		return $totalJourney = CBDBankMis::select("id")->where("sm_manager",$team)->whereRaw($whereRawBank)->get()->count();
	}
	protected function getAECBStatusOfEmpTeam($team,$whereRawBank)
	{
		$totalBankdata = CBDBankMis::select("AECB_Status")->where("sm_manager",$team)->whereRaw($whereRawBank)->get();
		$AECB_StatusArray = array(); 
		$AECB_StatusArray['NO HIT'] = 0;
		$AECB_StatusArray['THICK'] = 0;
		$AECB_StatusArray['THIN A'] = 0;
		$AECB_StatusArray['THIN B'] = 0;
		$AECB_StatusArray['Not Captured'] = 0;
		foreach($totalBankdata as $bank)
		{
			
			$AECB_Status = $bank->AECB_Status;
			if($AECB_Status == 'NO HIT')
			{
				$AECB_StatusArray['NO HIT'] = $AECB_StatusArray['NO HIT']+1;
			}
			else if($AECB_Status == 'THICK')
			{
				$AECB_StatusArray['THICK'] = $AECB_StatusArray['THICK']+1;
			}
			else if($AECB_Status == 'THIN A')
			{
				$AECB_StatusArray['THIN A'] = $AECB_StatusArray['THIN A']+1;
			}
			else if($AECB_Status == 'THIN B')
			{
				$AECB_StatusArray['THIN B'] = $AECB_StatusArray['THIN B']+1;
			}
			else
			{
				$AECB_StatusArray['Not Captured'] = $AECB_StatusArray['Not Captured']+1;
			}
		}
		return $AECB_StatusArray;
		
	}
	protected function getTLName($empId)
	{
		$empDetailsModel = Employee_details::select("tl_id")->where("emp_id",$empId)->first();
			if($empDetailsModel != '')
			{
				$tlID = $empDetailsModel->tl_id;
				if($tlID != '' && $tlID != NULL)
				{
					return Employee_details::select("export_name")->where("id",$tlID)->first()->export_name;
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
 public function update_missing_bank_employee_details()
	{
			$cbdBankModel = CBDBankMis::select("id","Created_User")->whereNull("update_emp_status")->whereNull("update_emp_status_missing")->get();
			/* echo "<pre>";
			//print_r($cbdBankModel);
			echo count($cbdBankModel);
			exit; */
			
			foreach($cbdBankModel as $bank)
			{
					/* echo "<pre>";
					print_r($bank);
					exit; */
					$source_code = $bank->Created_User;
					$empMod = Employee_details::select("emp_id","emp_name")->where("source_code",trim($source_code))->first();
					/* echo "<pre>";
					print_r($empMod);
					exit; */
					if($empMod != '')
					{
						$smName = CBDBankMis::select("sm_manager")->where("update_emp_status",2)->where("employee_id",$empMod->emp_id)->first();
						/* echo $smName;exit; */
						if($smName != '')
						{
							$updateBank = CBDBankMis::find($bank->id);
							$updateBank->employee_id = $empMod->emp_id;
							$updateBank->sm_manager = $smName->sm_manager;
							$updateBank->update_emp_status_missing = 2;
							$updateBank->save();
						}
						else
						{
							$smNameInternal = DepartmentFormEntry::select("team")->where("form_id",2)->where("emp_id",$empMod->emp_id)->first();
							if($smNameInternal != '')
							{
								$updateBank = CBDBankMis::find($bank->id);
								$updateBank->employee_id = $empMod->emp_id;
								$updateBank->sm_manager = $smNameInternal->team;
								$updateBank->update_emp_status_missing = 2;
								$updateBank->save();
							}
						}
					}
			}
			
			echo "done";
			exit;
	}
	
	
	
	 public function update_missing_mtd_employee_details()
	{
			$cbdBankModel = BankCBDMTD::select("id","User_Name")->where("update_status",1)->whereNull("update_emp_status_missing")->get();
			/* echo "<pre>";
			//print_r($cbdBankModel);
			echo count($cbdBankModel);
			exit; */
			
			foreach($cbdBankModel as $bank)
			{
					/* echo "<pre>";
					print_r($bank);
					exit; */
					$source_code = $bank->User_Name;
					$empMod = Employee_details::select("emp_id","emp_name")->where("source_code",trim($source_code))->first();
					/* echo "<pre>";
					print_r($empMod);
					exit; */
					if($empMod != '')
					{
						$smName = CBDBankMis::select("sm_manager")->where("update_emp_status",2)->where("employee_id",$empMod->emp_id)->first();
						/* echo $smName;exit; */
						if($smName != '')
						{
							$updateBank = BankCBDMTD::find($bank->id);
							$updateBank->employee_id = $empMod->emp_id;
							$updateBank->sm_manager = $smName->sm_manager;
							$updateBank->update_emp_status_missing = 2;
							$updateBank->save();
						}
						else
						{
							$smNameInternal = DepartmentFormEntry::select("team")->where("form_id",2)->where("emp_id",$empMod->emp_id)->first();
							if($smNameInternal != '')
							{
								$updateBank = BankCBDMTD::find($bank->id);
								$updateBank->employee_id = $empMod->emp_id;
								$updateBank->sm_manager = $smNameInternal->team;
								$updateBank->update_emp_status_missing = 2;
								$updateBank->save();
							}
						}
					}
			}
			
			echo "done";
			exit;
	}
	
	public function realTimeUpdateCBD()
	{
		$departmentFromCount = 	DepartmentFormEntry::where("form_id",2)
			->whereNotNull("ref_no")
			->where("cbd_marging_status",1)
			->get();
		foreach($departmentFromCount as $dept)
		{
						$refNo = $dept->ref_no;
						$interMISId = $dept->id;
						$jonusModel = CBDBankMis::where("ref_no",trim($refNo))->first();
				if($jonusModel != '')
				{
					
							$jonusId = $jonusModel->id;
							/*
							*marging from bank to mis
							*/
							$bankData = CBDBankMis::where("id",$jonusId)->first();
							$updateInternalMis = DepartmentFormEntry::find($interMISId);
							$updateInternalMis->ref_no = $bankData->ref_no;
							$updateInternalMis->customer_name = $bankData->customer_name;
							$updateInternalMis->channel_cbd = $bankData->Channel;
							$updateInternalMis->status_AECB_cbd = $bankData->AECB_Status;
							$updateInternalMis->form_status = $bankData->Status;
							$updateInternalMis->card_type_cbd = $bankData->card_type;
							
							$updateInternalMis->cbd_marging_status = 2;
							$updateInternalMis->cbd_update_status = 2;
							$updateInternalMis->missing_internal = 2;
							$updateInternalMis->save();
							
								/*
								*update in child
								*/
								$getData = DepartmentFormChildEntry::where("parent_id",$interMISId)->where("attribute_code","customer_name")->first();
								if($getData != '')
								{
									$updateChild = DepartmentFormChildEntry::find($getData->id);
									$updateChild->attribute_value = $bankData->customer_name;
									$updateChild->save();
								}
								/*
								*update in child
								*/
								
								/*
								*update in child
								*/
								
								$getData = DepartmentFormChildEntry::where("parent_id",$interMISId)->where("attribute_code","status_cbd")->first();
								if($getData != '')
								{
									$updateChild = DepartmentFormChildEntry::find($getData->id);
									$updateChild->attribute_value = $bankData->Status;
									$updateChild->save();
								}
								$getData = DepartmentFormChildEntry::where("parent_id",$interMISId)->where("attribute_code","channel_cbd")->first();
								if($getData != '')
								{
									$updateChild = DepartmentFormChildEntry::find($getData->id);
									$updateChild->attribute_value = $bankData->Channel;
									$updateChild->save();
								}
								$getData = DepartmentFormChildEntry::where("parent_id",$interMISId)->where("attribute_code","card_type_cbd")->first();
								if($getData != '')
								{
									$updateChild = DepartmentFormChildEntry::find($getData->id);
									$updateChild->attribute_value = $bankData->card_type;
									$updateChild->save();
								}
								$getData = DepartmentFormChildEntry::where("parent_id",$interMISId)->where("attribute_code","aecb_status")->first();
								if($getData != '')
								{
									$updateChild = DepartmentFormChildEntry::find($getData->id);
									$updateChild->attribute_value = $bankData->AECB_Status;
									$updateChild->save();
								}
								/*
								*update in child
								*/
							
							/*
							*marging from bank to mis
							*/
							
							/*
							*marging from internal to bank
							*/
							$misInternalData = DepartmentFormEntry::where("id",$interMISId)->first();
							
								$updateBankMis = CBDBankMis::find($jonusId);
								$updateBankMis->sm_manager = $misInternalData->team;
								$updateBankMis->employee_id = $misInternalData->emp_id;
								$updateBankMis->cbd_marging_status = 2;
								$updateBankMis->update_emp_status = 2;
								$updateBankMis->save();
							/*
							*marging from internal to bank
							*/
							//echo $interMISId = $dept->id;exit;
				}
				
		}
		echo "done";
		exit;
	}
	
	
	public function updateMissingCBDEntryPost(Request $request)
	{
		$parametersInput = $request->input();
		
		$valueId = $parametersInput['value_id'];
		$ref_no = $parametersInput['ref_no'];
		$form_status = $parametersInput['form_status'];
		$modUpdate = DepartmentFormEntry::find($valueId);
		$modUpdate->ref_no = $ref_no;
		$modUpdate->form_status = $form_status;
		$modUpdate->missing_internal = 4;
		$modUpdate->save();
		/*
		*ref NO
		*/
		$childData = DepartmentFormChildEntry::where("parent_id",$valueId)->where("attribute_code","ref_no")->first();
		if($childData != '')
		{
			
			$updateChild = DepartmentFormChildEntry::find($childData->id);
			$updateChild->attribute_value = $ref_no;
			$updateChild->save();
			
			
		}
		/*
		*ref NO
		*/
		/*
		*Status
		*/
		$childData = DepartmentFormChildEntry::where("parent_id",$valueId)->where("attribute_code","status_cbd")->first();
		if($childData != '')
		{
			
			$updateChild = DepartmentFormChildEntry::find($childData->id);
			$updateChild->attribute_value = $form_status;
			$updateChild->save();
			
			
		}
		/*
		*Status
		*/
		
		return redirect('cbdCardsManagement');
	}
	
	public function updateMissingCBDEntryPostLater()
	{
		$countData = DepartmentFormEntry::where("form_id",2)->where("missing_internal",4)->where("cbd_marging_status",2)->get();
	/* 	echo count($countData);
		exit; */
		foreach($countData as $_data)
		{
			$update = DepartmentFormEntry::find($_data->id);
			$update->missing_internal = 2;
			$update->save();
		}
		echo "yes";
		exit;
	}
	
	
 public function updateRemarkExcelStyle(Request $request)
 {
	 $parametersInput = $request->input();
	
	 $remarks= $parametersInput['remarks'];
	 $id= $parametersInput['id'];
	 $updateMod = DepartmentFormEntry::find($id);
	 $updateMod->remarks = $remarks;
	  $updateMod->save();
	  
	  
	  $mainData = DepartmentFormEntry::where('id',$id)->first();
		$emp_name = $mainData->emp_name;
		$emp_id = $mainData->emp_id;
	  /*
	  *Update Value in child
	  */
	  $checkRemarkExist = DepartmentFormChildEntry::where("parent_id",$id)->where("attribute_code","remarks")->first();
		if($checkRemarkExist != '')
		{
			$departmentUpdate = DepartmentFormChildEntry::find($checkRemarkExist->id);
			$departmentUpdate->attribute_value = $remarks;
			$departmentUpdate->save();
		}
		else
		{
			$departmentAdd = new DepartmentFormChildEntry();
			$departmentAdd->parent_id = $id;
			$departmentAdd->form_id = 1;
			$departmentAdd->attribute_code = "remarks";
			$departmentAdd->attribute_value = $remarks;
			$departmentAdd->status = 1;
			$departmentAdd->save();
			
		}
		
		NotificatonController::sendMeNotification($emp_id,'Update on '.$emp_name,'Update on '.$emp_name.' -'.trim($remarks),'SubmissionList');
		echo "done";
		exit;
	  /*
	  *Update Value in child
	  */
 }
 
 
 
  public function updateLastCommentExcelStyle(Request $request)
 {
	 $parametersInput = $request->input();
	
	 $lastComment= strip_tags($parametersInput['lastComment']);
	 $id= $parametersInput['id'];
	 $updateMod = DepartmentFormEntry::find($id);
	 $updateMod->last_comment = $lastComment;
	  $updateMod->save();
	echo "done";
	exit;
 }
 public function updateFormStatusExcelStyle(Request $request)
 {
	 $parametersInput = $request->input();
	
	 $formStatus= strip_tags($parametersInput['formStatus']);
	 $id= $parametersInput['id'];
	 $updateMod = DepartmentFormEntry::find($id);
	 $updateMod->form_status = $formStatus;
	  $updateMod->save();
	   /*
	  *Update Value in child
	  */
	  $checkRemarkExist = DepartmentFormChildEntry::where("parent_id",$id)->where("attribute_code","form_status")->first();
		if($checkRemarkExist != '')
		{
			$departmentUpdate = DepartmentFormChildEntry::find($checkRemarkExist->id);
			$departmentUpdate->attribute_value = $formStatus;
			$departmentUpdate->save();
		}
		else
		{
			$departmentAdd = new DepartmentFormChildEntry();
			$departmentAdd->parent_id = $id;
			$departmentAdd->form_id = 1;
			$departmentAdd->attribute_code = "form_status";
			$departmentAdd->attribute_value = $formStatus;
			$departmentAdd->status = 1;
			$departmentAdd->save();
			
		}
		echo "done";
		exit;
	  /*
	  *Update Value in child
	  */
	echo "done";
	exit;
 }
}
