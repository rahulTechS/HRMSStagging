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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Http\Controllers\MIS\ReportingController;
use App\Models\Logs\ExportReportLogs;
class ExcelRenderingController extends Controller
{
  
			
			
			public function excelGenerate(Request $request)
			{
				error_reporting(E_ALL);
				ini_set("display_errors", 1);
				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet();
				$sheet->mergeCells('A1:D5');
				$sheet->setCellValue('A1', 'HandsonReport Credit Cards11')->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$spreadsheet->getActiveSheet()->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('00FF7F');
				$writer = new Xlsx($spreadsheet);
				$writer->save('/srv/www/htdocs/hrm/public/uploads/Gaurav.xlsx');
			}
			
			
			
			
public function exportHandsonReportFinal(Request $request)
{
	$parameters = $request->input(); 

	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'hands_on_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
				$sheet->mergeCells('A1:V1');
				$sheet->setCellValue('A1', 'FOR CREDIT CARD - Hands On - Smart Union - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Login Date'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Application Type'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Tracker No'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Customer Name'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Mobile No'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Nationality'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Product Type'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('SE Code'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Checklist'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Precalling Template'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Application Form'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('V-Passport & V-Visa'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('V-EIDA'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Income Proof'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Bank Statements'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Bank Name'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Security Check'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Cheque Number'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('Amount'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('Type Of Proof'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('Remarks'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			
			//$f = fopen(public_path('uploads/exportMIS/'.$filename), 'w');
			$freshApp = 0;
			$resubmission = 0;
			$secChq = 0;
			$sn = 1;
			foreach ($selectedId as $sid) {
				 $misData = HandsOnFinal::where("id",$sid)->first();
				 $indexCounter++;
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $misData->login_date)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($misData->application_type))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($misData->tracker_no))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper($misData->customer_name))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, strtoupper($misData->mobile_no))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, strtoupper($misData->nationality))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, strtoupper($misData->product_type))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, strtoupper($misData->se_code))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, strtoupper($misData->Checklist))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, strtoupper($misData->precalling_template))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, strtoupper($misData->application_form))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, strtoupper($misData->v_passport_v_visa))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, strtoupper($misData->V_EIDA))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, strtoupper($misData->income_proof))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, strtoupper($misData->bank_statements))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, strtoupper($misData->bank_name))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, strtoupper($misData->security_check))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, strtoupper($misData->cheque_number))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, strtoupper($misData->amount))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, strtoupper($misData->type_of_proof))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, strtoupper($misData->remarks))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				if($misData->application_type == 'Fresh App w/o Supp.' || 'Fresh App with Supp.' || 'Fresh App')
				{
					$freshApp++;
				}
				else if($misData->application_type == 'Resubmission')
				{
					$resubmission++;
				}
				else if($misData->application_type == 'Sec Chq')
				{
					$secChq++;
				}
				$sn++;
				
			}
			$indexCounter++;
			$firstMerge = $indexCounter;
			$sheet->mergeCells('A'.$indexCounter.':B'.$indexCounter);
		
			$sheet->setCellValue('A'.$indexCounter, strtoupper("FRESH FILES"))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, $freshApp)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$indexCounter++;
			$sheet->mergeCells('A'.$indexCounter.':B'.$indexCounter);
			
			$sheet->setCellValue('A'.$indexCounter, strtoupper("Resubmission"))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, $resubmission)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			$indexCounter++;
			$sheet->mergeCells('A'.$indexCounter.':B'.$indexCounter);
			$sheet->mergeCells('D'.$firstMerge.':V'.$indexCounter);
			$sheet->setCellValue('D'.$indexCounter, 'Received By:')->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('middle');
			$sheet->setCellValue('A'.$indexCounter, strtoupper("Security Cheques"))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, $secChq)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			  foreach (range('A','V') as $col) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			$spreadsheet->getActiveSheet()->getStyle('A2:I2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffff00');
			$spreadsheet->getActiveSheet()->getStyle('J2:V2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('c9f2d3');
			$spreadsheet->getActiveSheet()->getStyle('A1:V1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','V') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportMIS/'.$filename));	
				/*
			*Export Logs
			*/
			$exportLogsObj = new ExportReportLogs();
			$exportLogsObj->download_area = 'MIS Cards HandsOn Manual Report';
			$exportLogsObj->download_filename = $filename;
			$exportLogsObj->downloaded_by = $request->session()->get('EmployeeId');
			$exportLogsObj->save();
			/*
			*export Logs
			*/
				echo $filename;
				exit;
}
		

public function exportHandsonReportFinalTab(Request $request)
{
	$parameters = $request->input(); 

	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Tab_hands_on_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
				$sheet->mergeCells('A1:G1');
				$sheet->setCellValue('A1', 'FOR TAB SUBMISSION - Hands On - Smart Union - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Date'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('APP ID'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('CM Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('PRODUCT'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('SE'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('STATUS'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				 $misData = HandsOnFinalTab::where("id",$sid)->first();
				 $indexCounter++;
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $misData->login_date)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($misData->appid))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($misData->customer_name))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper($misData->PRODUCT))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, strtoupper($misData->se_code))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, strtoupper($misData->Status))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
			  foreach (range('A','G') as $col) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','G') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportMIS/'.$filename));	
					/*
			*Export Logs
			*/
			$exportLogsObj = new ExportReportLogs();
			$exportLogsObj->download_area = 'MIS Cards HandsOn Tab Report';
			$exportLogsObj->download_filename = $filename;
			$exportLogsObj->downloaded_by = $request->session()->get('EmployeeId');
			$exportLogsObj->save();
			/*
			*export Logs
			*/
				echo $filename;
				exit;
}		


public function excelDailyCCD(Request $request)
{
	$filename = 'CCD REPORT DAILY BASIS_'.date("d-m-Y").'.xlsx';
		/*
			*Export Logs
			*/
			$exportLogsObj = new ExportReportLogs();
			$exportLogsObj->download_area = 'CCD REPORT DAILY BASIS Report';
			$exportLogsObj->download_filename = $filename;
			$exportLogsObj->downloaded_by = $request->session()->get('EmployeeId');
			$exportLogsObj->save();
			/*
			*export Logs
			*/
				$tL_detailsMod = Employee_attribute::where("attribute_code","DESIGN")->whereIn("attribute_values",array("SALES MANAGER","TEAM LEADER"))->where("dept_id",9)->get();
				$tL_id = array();
				foreach($tL_detailsMod as $tl)
				{
					$tL_id[] = $tl->emp_id;
				}
				$tL_details = Employee_details::whereIn("emp_id",$tL_id)->get();
				$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet();
				$sheet->mergeCells('A1:J1');
				$sheet->setCellValue('A1', 'CCD END REPORT TL WISE - '.strtoupper(date("M")).' ENDS')->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('TL WISE'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('PHYS APPROVED'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('PHYS LOGINS'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('TAB LOGINS'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('PHYS WIP'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('TAB WIP'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('PHYSICAL ENDS'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('TAB ENDS'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('PHYS AWAITING'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter++;
			$sn = 1;
			 $newMonth = date("m");
					 $currentYear = date("Y");
					 $total_phyApproved = 0;
					 $total_phyLogin = 0;
					 $total_tabLogin = 0;
					 $total_phyWip = 0;
					 $total_tabWip = 0;
					 $total_phyEnd = 0;
					 $total_tabEnd = 0;
					 $total_awaiting = 0;
			foreach($tL_details as $TLData)
			{
				$leader = $TLData->id;
				$phyApproved = ReportingController::getReportMisCards($newMonth,$currentYear,1,$leader,'manual');
				$total_phyApproved = $total_phyApproved+$phyApproved;
				$phyLogin = ReportingController::getReportMisCards($newMonth,$currentYear,'login',$leader,'manual');
				$total_phyLogin = $total_phyLogin+$phyLogin;
				$tabLogin = ReportingController::getReportMisCards($newMonth,$currentYear,'login',$leader,'Tab');
				$total_tabLogin = $total_tabLogin+$tabLogin;
				$phyWip = ReportingController::getReportMisCards($newMonth,$currentYear,7,$leader,'manual');
				$total_phyWip = $total_phyWip+$phyWip;
				$tabWip = ReportingController::getReportMisCards($newMonth,$currentYear,7,$leader,'Tab');
				$total_tabWip = $total_tabWip+$tabWip;
				$phyEnd = ReportingController::getReportMisCards($newMonth,$currentYear,3,$leader,'manual');
				$total_phyEnd = $total_phyEnd+$phyEnd;
				$tabEnd = ReportingController::getReportMisCards($newMonth,$currentYear,3,$leader,'Tab');
				$total_tabEnd = $total_tabEnd+$tabEnd;
				$awaiting = ReportingController::getWaiting($newMonth,$currentYear,$leader,'manual');
				$total_awaiting = $total_awaiting+$awaiting;
				$sheet->setCellValue('A'.$indexCounter, strtoupper($sn))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, strtoupper($TLData->first_name.' '.$TLData->middle_name.' '.$TLData->last_name))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($phyApproved))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($phyLogin))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper($tabLogin))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, strtoupper($phyWip))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, strtoupper($tabWip))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, strtoupper($phyEnd))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, strtoupper($tabEnd))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, strtoupper($awaiting))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$indexCounter++;
				$sn++;
			}	
			$index11 = $indexCounter-1;
			$spreadsheet->getActiveSheet()->getStyle('B3:B'.$index11)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffb380');
			$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter.':J'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('c6e0b4');
				$sheet->setCellValue('B'.$indexCounter, strtoupper('Total'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($total_phyApproved))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($total_phyLogin))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper($total_tabLogin))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, strtoupper($total_phyWip))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, strtoupper($total_tabWip))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, strtoupper($total_phyEnd))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, strtoupper($total_tabEnd))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, strtoupper($total_awaiting))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$indexCounter++;
				$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter.':J'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('9bc2e6');
				$sheet->setCellValue('B'.$indexCounter, strtoupper('Grand Total'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->mergeCells('D'.$indexCounter.':E'.$indexCounter);
				$sheet->setCellValue('D'.$indexCounter, strtoupper($total_phyLogin+$total_tabLogin))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->mergeCells('F'.$indexCounter.':G'.$indexCounter);
				$sheet->setCellValue('F'.$indexCounter, strtoupper($total_phyWip+$total_tabWip))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->mergeCells('H'.$indexCounter.':I'.$indexCounter);
				$sheet->setCellValue('H'.$indexCounter, strtoupper($total_phyEnd+$total_tabEnd))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, strtoupper($total_awaiting))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','J') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				
			$indexCounter++;
			$indexCounter++;
			$indexCounter++;
			$indexCounter++;
			
			$indexG1 = $indexCounter;
			/*
			*Grand Total
			*/
			$sheet->setCellValue('A'.$indexCounter, strtoupper('TL WISE'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('PHYS + TAB LOGINS'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('PHYS + TAB WIP'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('PHYS + TAB END'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$grandLogin = 0;
					$grandWip = 0;
					$grandEnd = 0;
					foreach($tL_details as $tl1)
					{
						$indexCounter++;
						$leader = $tl1->id;
						 $phyl = ReportingController::getReportMisCards($newMonth,$currentYear,'login',$leader,'manual');
						$tabl = ReportingController::getReportMisCards($newMonth,$currentYear,'login',$leader,'Tab');
						$grandLogin = $grandLogin+($phyl+$tabl);
						$phywip = ReportingController::getReportMisCards($newMonth,$currentYear,7,$leader,'manual');
					  $tabwip = ReportingController::getReportMisCards($newMonth,$currentYear,7,$leader,'Tab');
					  $grandWip = $grandWip+($phywip+$tabwip);
					   $phyend =ReportingController::getReportMisCards($newMonth,$currentYear,3,$leader,'manual');
					  $tabend = ReportingController::getReportMisCards($newMonth,$currentYear,3,$leader,'Tab');
					  $grandEnd = $grandEnd+($phyend+$tabend);
					  $sheet->setCellValue('A'.$indexCounter, strtoupper($tl1->first_name))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper($phyl+$tabl))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper($phywip+$tabwip))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper($phyend+$tabend))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					}
					
					$indexCounter++;
					
						  $sheet->setCellValue('A'.$indexCounter, strtoupper('Grand Total'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper($grandLogin))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper($grandWip))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper($grandEnd))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter.':D'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('9bc2e6');
			
			/*
			*Grand Total
			*/
			for($index=$indexG1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','D') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
			foreach (range('A','J') as $col) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			$spreadsheet->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			$spreadsheet->getActiveSheet()->getStyle('H2:J2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffff00');
			$spreadsheet->getActiveSheet()->getStyle('A2:G2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('a9d08e');
			$spreadsheet->getActiveSheet()->getStyle('A13:D13')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportMIS/'.$filename));	
				echo $filename;
				exit;
				
}


public function printToFile(Request $request)
{
	$leaders = $request->teamidlist;
	$leadersArray = explode(",",$leaders);
	 $currentMonth = date("m");
	 $currentYear = date("Y");
	$filename = 'DAILY BASIS REPORT_'.date("d-m-Y").'.xlsx';
	$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet();
				
				$sheet->mergeCells('A2:B6');
				$sheet->setCellValue('A2', strtoupper('date'))->getStyle('A2')->getAlignment()->setHorizontal('center')->setVertical('top');
				$spreadsheet->getActiveSheet()->getStyle('A2:B6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffd966');
				
		$newMonth = 0;
					$indexCounter = 7;
					for($i=0;$i<3;$i++)
					{
						$newMonth = date('m', strtotime('-'.$i.' month'));
						$currentYear = date('Y', strtotime('-'.$i.' month'));
						
						$sheet->mergeCells('A'.$indexCounter.':B'.$indexCounter);
						$sheet->setCellValue('A'.$indexCounter, strtoupper(date("F", mktime(0, 0, 0, $newMonth, 10))))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter.':B'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffd966');
						$indexCounter++;
						
					}
					$indexCounter = 2;
					$cellIndex = 7;
					$startCellName ='C'; 
					$startCellIndex = '2';
					foreach($leadersArray as $leader)
					{
						
						$cellName = strtoupper($this->toAlpha($cellIndex));
						 $sheet->mergeCells($startCellName.$indexCounter.':'.$cellName.$indexCounter);
						$sheet->setCellValue($startCellName.$indexCounter, strtoupper(ReportingController::getLeaderName($leader)))->getStyle($startCellName.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top'); 
						
						$spreadsheet->getActiveSheet()->getStyle($startCellName.$indexCounter.':'.$cellName.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('333333');
						$spreadsheet->getActiveSheet()->getStyle($startCellName.$indexCounter)->getFont()->getColor()->setRGB ('ffffff'); 
						$indexCounter++;
						 $sheet->mergeCells($startCellName.$indexCounter.':'.$cellName.$indexCounter);
						$sheet->setCellValue($startCellName.$indexCounter, strtoupper('Captain: Arif - CC Targert : 350 - PL Target: 1M'))->getStyle($startCellName.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top'); 
						$indexCounter++;
						$indexCounter++;
						
						 $sheet->mergeCells($startCellName.$indexCounter.':'.$cellName.$indexCounter);
						$sheet->setCellValue($startCellName.$indexCounter, strtoupper('Credit Cards'))->getStyle($startCellName.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top'); 
						$spreadsheet->getActiveSheet()->getStyle($startCellName.$indexCounter.':'.$cellName.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('033479');
						$spreadsheet->getActiveSheet()->getStyle($startCellName.$indexCounter)->getFont()->getColor()->setRGB ('ffffff'); 
						$indexCounter++;
						/*
						*login
						*/
						$sheet->setCellValue($startCellName.$indexCounter, strtoupper('Login'))->getStyle($startCellName.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top'); 
						$spreadsheet->getActiveSheet()->getStyle($startCellName.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bdd7ee');						
						$startCellNameNew =strtoupper($this->toAlpha($startCellIndex+1));
						 $newIndex = $indexCounter;
						 for($i=0;$i<3;$i++)
							{
								$newMonth = date('m', strtotime('-'.$i.' month'));
								$currentYear = date('Y', strtotime('-'.$i.' month'));
								$newIndex++;
								$loginCount = ReportingController::getReportMisCards($newMonth,$currentYear,'login',$leader,'all');
								
								$sheet->setCellValue($startCellName.$newIndex, $loginCount)->getStyle($startCellName.$newIndex)->getAlignment()->setHorizontal('center')->setVertical('top'); 
								$spreadsheet->getActiveSheet()->getStyle($startCellName.$newIndex)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bdd7ee');
							}
						/*
						*login
						*/
						/*
						*approved
						*/
						$sheet->setCellValue($startCellNameNew.$indexCounter, strtoupper('Approved'))->getStyle($startCellNameNew.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top'); 
						$spreadsheet->getActiveSheet()->getStyle($startCellNameNew.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bdd7ee');						
						$newIndex = $indexCounter;
						 for($i=0;$i<3;$i++)
							{
								$newMonth = date('m', strtotime('-'.$i.' month'));
								$currentYear = date('Y', strtotime('-'.$i.' month'));
								$newIndex++;
								$approved = ReportingController::getReportMisCards($newMonth,$currentYear,1,$leader,'all');
								$sheet->setCellValue($startCellNameNew.$newIndex, $approved)->getStyle($startCellNameNew.$newIndex)->getAlignment()->setHorizontal('center')->setVertical('top'); 
								$spreadsheet->getActiveSheet()->getStyle($startCellNameNew.$newIndex)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bdd7ee');
							}
						$startCellIndex++;
						/*
						*approved
						*/
						/*
						*phy ends
						*/
						$startCellNameNew =strtoupper($this->toAlpha($startCellIndex+1));
						$sheet->setCellValue($startCellNameNew.$indexCounter, strtoupper('Phyiscal ENDS'))->getStyle($startCellNameNew.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top'); 
						$spreadsheet->getActiveSheet()->getStyle($startCellNameNew.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bdd7ee');						
						$newIndex = $indexCounter;
						 for($i=0;$i<3;$i++)
							{
								$newMonth = date('m', strtotime('-'.$i.' month'));
								$currentYear = date('Y', strtotime('-'.$i.' month'));
								$newIndex++;
								$phyend = ReportingController::getReportMisCards($newMonth,$currentYear,3,$leader,'manual');
								$sheet->setCellValue($startCellNameNew.$newIndex, $phyend)->getStyle($startCellNameNew.$newIndex)->getAlignment()->setHorizontal('center')->setVertical('top'); 
								$spreadsheet->getActiveSheet()->getStyle($startCellNameNew.$newIndex)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bdd7ee');
							}
						$startCellIndex++;
						/*
						*phy ends
						*/
						/*
						*tab ends
						*/
						$startCellNameNew =strtoupper($this->toAlpha($startCellIndex+1));
						$sheet->setCellValue($startCellNameNew.$indexCounter, strtoupper('Tab Ends'))->getStyle($startCellNameNew.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top'); 
						$spreadsheet->getActiveSheet()->getStyle($startCellNameNew.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bdd7ee');						
						$newIndex = $indexCounter;
						 for($i=0;$i<3;$i++)
							{
								$newMonth = date('m', strtotime('-'.$i.' month'));
								$currentYear = date('Y', strtotime('-'.$i.' month'));
								$newIndex++;
								$tabend = ReportingController::getReportMisCards($newMonth,$currentYear,3,$leader,'Tab');
								$sheet->setCellValue($startCellNameNew.$newIndex, $tabend)->getStyle($startCellNameNew.$newIndex)->getAlignment()->setHorizontal('center')->setVertical('top'); 
								$spreadsheet->getActiveSheet()->getStyle($startCellNameNew.$newIndex)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bdd7ee');
							}
						$startCellIndex++;
						/*
						*tab ends
						*/
						/*
						*wip
						*/
						$startCellNameNew =strtoupper($this->toAlpha($startCellIndex+1));
						$sheet->setCellValue($startCellNameNew.$indexCounter, strtoupper('WIP'))->getStyle($startCellNameNew.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top'); 
						$spreadsheet->getActiveSheet()->getStyle($startCellNameNew.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bdd7ee');						
						$newIndex = $indexCounter;
						 for($i=0;$i<3;$i++)
							{
								$newMonth = date('m', strtotime('-'.$i.' month'));
								$currentYear = date('Y', strtotime('-'.$i.' month'));
								$newIndex++;
								$wip = ReportingController::getReportMisCards($newMonth,$currentYear,7,$leader,'all');
								$sheet->setCellValue($startCellNameNew.$newIndex, $wip)->getStyle($startCellNameNew.$newIndex)->getAlignment()->setHorizontal('center')->setVertical('top'); 
								$spreadsheet->getActiveSheet()->getStyle($startCellNameNew.$newIndex)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bdd7ee');
							}
						$startCellIndex++;
						/*
						*wip
						*/
						/*
						*Awaiting
						*/
						$startCellNameNew =strtoupper($this->toAlpha($startCellIndex+1));
						$sheet->setCellValue($startCellNameNew.$indexCounter, strtoupper('Awaiting Sourcing'))->getStyle($startCellNameNew.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top'); 
						$spreadsheet->getActiveSheet()->getStyle($startCellNameNew.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bdd7ee');						
						$newIndex = $indexCounter;
						 for($i=0;$i<3;$i++)
							{
								$newMonth = date('m', strtotime('-'.$i.' month'));
								$currentYear = date('Y', strtotime('-'.$i.' month'));
								$newIndex++;
								$sourcing = ReportingController::getReportMisCards($newMonth,$currentYear,4,$leader,'all');
								$sheet->setCellValue($startCellNameNew.$newIndex, $sourcing)->getStyle($startCellNameNew.$newIndex)->getAlignment()->setHorizontal('center')->setVertical('top'); 
								$spreadsheet->getActiveSheet()->getStyle($startCellNameNew.$newIndex)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bdd7ee');
							}
						/*
						*Awaiting
						*/
						$startCellName =strtoupper($this->toAlpha($cellIndex+1));
						$startCellIndex = $cellIndex+1;
						$cellIndex = $cellIndex+6;
						$indexCounter = 2;
					}
					$sheet->mergeCells('A1:'.$cellName.'1');
				$sheet->setCellValue('A1', 'DAILY REPORT TL WISE - '.strtoupper(date("M")).' ENDS')->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
				$cellNameLastCondition = strtoupper($this->toAlpha($cellIndex-5));
				
					for($index=1;$index<=9;$index++)
						{
							for ($i2 = 'A'; $i2 !== $cellNameLastCondition; $i2++){
							  
									$spreadsheet->getActiveSheet()->getStyle($i2.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
							  }
						}
					for ($i2 = 'A'; $i2 !== $cellNameLastCondition; $i2++){
					   $sheet->getColumnDimension($i2)->setAutoSize(true);
					}
					
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportMIS/'.$filename));	
				echo $filename;
				exit;
}




public function printToFileLoan(Request $request)
{
	
	$leaders = $request->teamidlist;
	$leadersArray = explode(",",$leaders);
	 $currentMonth = date("m");
	 $currentYear = date("Y");
	$filename = 'LOAN DAILY BASIS REPORT_'.date("d-m-Y").'.xlsx';
	$spreadsheet = new Spreadsheet();
				$sheet = $spreadsheet->getActiveSheet();
				$sheet->mergeCells('A1:Q1');
				$sheet->setCellValue('A1', 'Loan Daily Reports')->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$indexCounter = 2;
				foreach($leadersArray as $leader)
				{
					$sheet->mergeCells('A'.$indexCounter.':Q'.$indexCounter);
					$sheet->setCellValue('A'.$indexCounter, strtoupper('TEAM'.ReportingController::getLeaderName($leader)))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter.':Q'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('333333');
					$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter)->getFont()->getColor()->setRGB ('ffffff');  
					$indexCounter=$indexCounter+1;
					$indexAn = $indexCounter+5;
					$sheet->mergeCells('A'.$indexCounter.':B'.$indexAn);
					$sheet->setCellValue('A'.$indexCounter, strtoupper('DATE'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter.':B'.$indexAn)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffd966');
					$indexAn1 = $indexCounter+2;
					$sheet->mergeCells('C'.$indexCounter.':Q'.$indexAn1);
					$sheet->setCellValue('C'.$indexCounter, strtoupper('Captain: Arif - CC Targert : 350 - PL Target: 1M'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('middle');
					$indexCounter=$indexCounter+3;
					$sheet->mergeCells('C'.$indexCounter.':G'.$indexCounter);
					$sheet->setCellValue('C'.$indexCounter, strtoupper('Personal Loan'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$spreadsheet->getActiveSheet()->getStyle('C'.$indexCounter.':G'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('033479');
					$spreadsheet->getActiveSheet()->getStyle('C'.$indexCounter)->getFont()->getColor()->setRGB ('ffffff');  
					$sheet->mergeCells('H'.$indexCounter.':L'.$indexCounter);
					$sheet->setCellValue('H'.$indexCounter, strtoupper('Merchant Loan'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$spreadsheet->getActiveSheet()->getStyle('H'.$indexCounter.':L'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('033479');
					$spreadsheet->getActiveSheet()->getStyle('H'.$indexCounter)->getFont()->getColor()->setRGB ('ffffff'); 
					$sheet->mergeCells('M'.$indexCounter.':Q'.$indexCounter);
					$sheet->setCellValue('M'.$indexCounter, strtoupper('Auto Loan'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
					$spreadsheet->getActiveSheet()->getStyle('M'.$indexCounter.':Q'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('033479');
					$spreadsheet->getActiveSheet()->getStyle('M'.$indexCounter)->getFont()->getColor()->setRGB ('ffffff'); 
					$indexCounter =$indexCounter+1;
					/*Personal Loan*/
					$sheet->setCellValue('C'.$indexCounter, strtoupper('Login'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('D'.$indexCounter, strtoupper('Approved'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('E'.$indexCounter, strtoupper('Disbursed'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('F'.$indexCounter, strtoupper('WIP'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('G'.$indexCounter, strtoupper('Awaiting Sourcing'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$spreadsheet->getActiveSheet()->getStyle('C'.$indexCounter.':G'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffcccc');
					/*Personal Loan*/
					
					
					/*Merchant Loan*/
					
					$sheet->setCellValue('H'.$indexCounter, strtoupper('Login'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('I'.$indexCounter, strtoupper('Approved'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('J'.$indexCounter, strtoupper('End'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('K'.$indexCounter, strtoupper('WIP'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('L'.$indexCounter, strtoupper('Awaiting Sourcing'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$spreadsheet->getActiveSheet()->getStyle('H'.$indexCounter.':L'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffff00');
					/*Merchant Loan*/
					
					
					
					/*Auto Loan*/
					
					$sheet->setCellValue('M'.$indexCounter, strtoupper('Login'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('N'.$indexCounter, strtoupper('Approved'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('O'.$indexCounter, strtoupper('End'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					
					$sheet->setCellValue('P'.$indexCounter, strtoupper('Awaiting Sourcing'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('Q'.$indexCounter, strtoupper('WIP'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$spreadsheet->getActiveSheet()->getStyle('M'.$indexCounter.':Q'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('9999ff');
					/*Auto Loan*/
					
					$indexCounter =$indexCounter+1;	
					$spreadsheet->getActiveSheet()->getStyle('C'.$indexCounter.':G'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffcccc');
					$spreadsheet->getActiveSheet()->getStyle('H'.$indexCounter.':L'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffff00');
					$spreadsheet->getActiveSheet()->getStyle('M'.$indexCounter.':Q'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('9999ff');
					$indexCounter =$indexCounter+1;	
					$newMonth = 0;
					for($i=0;$i<3;$i++)
					{
						$newMonth = date('m', strtotime('-'.$i.' month'));
						$currentYear = date('Y', strtotime('-'.$i.' month'));
						$sheet->mergeCells('A'.$indexCounter.':B'.$indexCounter);
						$sheet->setCellValue('A'.$indexCounter, strtoupper(date("F", mktime(0, 0, 0, $newMonth, 10))))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$spreadsheet->getActiveSheet()->getStyle('A'.$indexCounter.':B'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffd966');
						/*Personal Loan*/
					$sheet->setCellValue('C'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,'login',$leader,'PERSONAL LOAN'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('D'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,1,$leader,'PERSONAL LOAN'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('E'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,1,$leader,'PERSONAL LOAN'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('F'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,6,$leader,'PERSONAL LOAN'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('G'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,6,$leader,'PERSONAL LOAN'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$spreadsheet->getActiveSheet()->getStyle('C'.$indexCounter.':G'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffcccc');
					/*Personal Loan*/
					
					
					/*Merchant Loan*/
					
					$sheet->setCellValue('H'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,'login',$leader,'RETAIL SME-MERCHANT LOANS'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('I'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,1,$leader,'RETAIL SME-MERCHANT LOANS'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('J'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,2,$leader,'RETAIL SME-MERCHANT LOANS'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('K'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,6,$leader,'RETAIL SME-MERCHANT LOANS'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('L'.$indexCounter,ReportingController::getReportMisLoan($newMonth,$currentYear,6,$leader,'RETAIL SME-MERCHANT LOANS'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$spreadsheet->getActiveSheet()->getStyle('H'.$indexCounter.':L'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffff00');
					/*Merchant Loan*/
					
					
					
					/*Auto Loan*/
					
					$sheet->setCellValue('M'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,'login',$leader,'AUTO LOAN'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('N'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,1,$leader,'AUTO LOAN'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('O'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,2,$leader,'AUTO LOAN'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					
					$sheet->setCellValue('P'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,6,$leader,'AUTO LOAN'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$sheet->setCellValue('Q'.$indexCounter, ReportingController::getReportMisLoan($newMonth,$currentYear,6,$leader,'AUTO LOAN'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
					$spreadsheet->getActiveSheet()->getStyle('M'.$indexCounter.':Q'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('9999ff');
					/*Auto Loan*/
					 //$spreadsheet->getActiveSheet()->getStyle('A3:B'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffd966');
						$indexCounter++;
				}
				
				}	
				$spreadsheet->getActiveSheet()->getStyle('A1:Q1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffff00');
				
			
			/*$spreadsheet->getActiveSheet()->getStyle('A2:G2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('a9d08e');
			$spreadsheet->getActiveSheet()->getStyle('A13:D13')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			 */
			 	for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','Q') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
			foreach (range('A','Q') as $col) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportMIS/'.$filename));	
				echo $filename;
				exit;
	

}
	protected function toAlpha($data){
    $alphabet =   array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
    $alpha_flip = array_flip($alphabet);
    if($data <= 25){
      return $alphabet[$data];
    }
    elseif($data > 25){
      $dividend = ($data + 1);
      $alpha = '';
      $modulo;
      while ($dividend > 0){
        $modulo = ($dividend - 1) % 26;
        $alpha = $alphabet[$modulo] . $alpha;
        $dividend = floor((($dividend - $modulo) / 26));
      } 
      return $alpha;
    }
}		
}
