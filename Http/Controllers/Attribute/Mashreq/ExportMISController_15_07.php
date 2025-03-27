<?php

namespace App\Http\Controllers\Attribute\Mashreq;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\DepartmentForm;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Attribute\DepartmentFormChildEntry;
use App\Models\Attribute\FormProduct;
use App\Models\Attribute\MasterAttribute;
use App\Models\Attribute\AttributeType;
use App\Models\Attribute\FormSection;
use App\Models\Attribute\CdaDeviationDetails;
use App\Models\Attribute\SalaryStruture;
use App\Models\EmpOffline\EmpOffline;
use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
use App\Models\Common\MashreqLoginMIS;
use App\Models\Common\MashreqBookingMIS;
use App\Models\ENBDLoanMIS\ENBDLoanMIS;
use App\Models\Common\MashreqBankMIS;
use App\Models\Common\MashreqMTDMIS;
use App\Models\Dashboard\MashreqFinalMTD;
use App\Models\Common\MashreqMasterMIS;
use App\Models\Recruiter\RecruiterCategory;
use App\Models\Recruiter\Recruiter;
use App\Models\Recruiter\Designation;
use App\Models\Dashboard\MasterPayout;
use App\Models\SEPayout\RangeDetailsVintage;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Http\Controllers\Attribute\DepartmentFormController;
use App\Http\Controllers\Attribute\MasterAttributeController;
use App\Models\Employee\ExportDataLog;
use Session;

class ExportMISController extends Controller
{
   
 public function exportReProcess(Request $request)
 {
	
			 $parameters = $request->input(); 
			/*  echo "<pre>";
			 print_r($parameters);
			 exit; */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Re_Process_Data_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:P1');
			$sheet->setCellValue('A1', 'Re-Process Data - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Eligibility Date'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Flag Date'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Application Date'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Team'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Employee ID'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Employee Name'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Customer Name'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Salary'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');			
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Ref No'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('CIF Number'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Booked'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Submit Count'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Scheme Group'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Scheme Name'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Notes'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Min Start Date'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');



			$SchemeName = 'BSC: NH --> T2- 5/6.99K, T2';
			$eligibility_month_add = "6";
			if(Session::get('scheme_group_reProcess') != '')
			{
				$select_scheme_group_reProcess=Session::get('scheme_group_reProcess');
				if($select_scheme_group_reProcess=='BV Waiver Approved')
				{
					$SchemeName = 'BVA: NH/T2 --> T1, 7K+,T1,NL';
					$eligibility_month_add = "12";
				}
				if($select_scheme_group_reProcess=='Salary Credit Alert')
				{
					$SchemeName = 'SCA: 1/6 salaries reached- 7k,NH';
					$eligibility_month_add = "12";
				}
				
			}

			$select_scheme_reProcess = '';
			if(Session::get('scheme_reProcess') != '')
			{
				$select_scheme_reProcess=Session::get('scheme_reProcess');
			}

			$select_statistics_reProcess = '';
			if(Session::get('statistics_reProcess') != '')
			{
				$select_statistics_reProcess=Session::get('statistics_reProcess');
			}
			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  MashreqLoginMIS::where("id",$sid)->first();



				$today = date('Y-m-d');	
				$flag_date = date("Y-m-d", strtotime("+".$eligibility_month_add." months", strtotime($mis->min_startdate)));
				$eligibility_date = date("Y-m-d", strtotime("+".$eligibility_month_add." months", strtotime($mis->min_startdate)));

				if(@$mis->cif !='')
				{
					$Login_info = MasterAttributeController::getLoginInfoByCIF(@$mis->cif);				
				}
				
				$application_date = '';
				$ref_no = '';
				foreach($Login_info as $Login_data)
				{
					$application_date .= $Login_data->application_date.',';		
					$ref_no .= $Login_data->ref_no.',';	
				}
				$application_date = substr($application_date,0,-1);
				$ref_no = substr($ref_no,0,-1);

				$booking_status = 'No';
				if($mis->booking_status=='1')
				{
					$booking_status = 'Yes';					
				}

				
				 
				 
				 $indexCounter++; 	
				
				$sheet->setCellValue('A'.$indexCounter, $eligibility_date)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('B'.$indexCounter, $flag_date)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('C'.$indexCounter, $application_date)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('D'.$indexCounter, $mis->team)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('E'.$indexCounter, $mis->emp_id)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('F'.$indexCounter, $mis->emp_name)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('G'.$indexCounter, $mis->customer_name)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('H'.$indexCounter, $mis->cdafinalsalary)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('I'.$indexCounter, $ref_no)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('J'.$indexCounter, $mis->cif)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('K'.$indexCounter, $booking_status)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('L'.$indexCounter, ($mis->submit_count?$mis->submit_count:'1'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('M'.$indexCounter, $select_scheme_group_reProcess)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('N'.$indexCounter, $select_scheme_reProcess)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('O'.$indexCounter, $mis->last_comment)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('P'.$indexCounter, $mis->min_startdate)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'P'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','P') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
					$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="Mashreq-reProcess";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 } 
 

 public function exportDocReportBookingMisMashreqCards(Request $request)
 {
	
			 $parameters = $request->input(); 
			/*  echo "<pre>";
			 print_r($parameters);
			 exit; */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Booking_MIS_Mashreq_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:O1');
			$sheet->setCellValue('A1', 'Booking MIS Mashreq Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 4;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('instanceid'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('cif_cis_number'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('customername'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('plastictype'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('sellerid'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('sellername'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('dateofdisbursal'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('cdafinalsalary'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('I'.$indexCounter, strtoupper('agent_name'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('J'.$indexCounter, strtoupper('Employee ID'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Employee Name'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('L'.$indexCounter, strtoupper('Recruiter'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Recruiter Category'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Vintage Days'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Range ID'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  MashreqBookingMIS::where("id",$sid)->first();

				$Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	

			$emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');
			$getrecruiterInfo = DepartmentFormController::getrecruiterInfo(@$Employee_details_data->recruiter);
			
			$getrecruiterCategoryInfo = DepartmentFormController::getrecruiterCategoryInfo(@$getrecruiterInfo->recruit_cat);
				 
				 
				 $indexCounter++; 	
				
				$sheet->setCellValue('A'.$indexCounter, $mis->instanceid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->cif_cis_number)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mis->customername)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $mis->plastictype)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mis->sellerid)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mis->sellername)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, date("d-m-Y",strtotime($mis->dateofdisbursal)))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->cdafinalsalary)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('I'.$indexCounter, $mis->agent_name)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('J'.$indexCounter, $mis->emp_id)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $emp_name)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, @$getrecruiterInfo->name)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, @$getrecruiterCategoryInfo->name)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, @$Employee_details_data->vintage_days)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, @$Employee_details_data->range_id)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'O'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:O1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','O') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
					$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="Mashreq-Booking";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 } 

 public function exportDocReportBookingMisMashreqCardsLink(Request $request)
 {
	
			 $parameters = $request->input(); 
			/*  echo "<pre>";
			 print_r($parameters);
			 exit; */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Export_for_Linking_Missing_Mashreq_Cards_'.date("d-m-Y").'_'.time().'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
					
			$indexCounter = 1;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('instanceid'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('cif_cis_number'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('customername'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('plastictype'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('sellerid'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('sellername'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('dateofdisbursal'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('cdafinalsalary'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Ref No'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('J'.$indexCounter, strtoupper('Emp ID'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('K'.$indexCounter, strtoupper('Team'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('L'.$indexCounter, strtoupper('Submission Date'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  MashreqBookingMIS::where("id",$sid)->first();

				$Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	

			$emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');
			$getrecruiterInfo = DepartmentFormController::getrecruiterInfo(@$Employee_details_data->recruiter);
			
			$getrecruiterCategoryInfo = DepartmentFormController::getrecruiterCategoryInfo(@$getrecruiterInfo->recruit_cat);
				 
				 
				 $indexCounter++; 	
				
				$sheet->setCellValue('A'.$indexCounter, $mis->instanceid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->cif_cis_number)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mis->customername)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $mis->plastictype)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mis->sellerid)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mis->sellername)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, date("d-m-Y",strtotime($mis->dateofdisbursal)))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->cdafinalsalary)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('I'.$indexCounter, $mis->ref_no)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('J'.$indexCounter, '')->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('K'.$indexCounter, '')->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('L'.$indexCounter, '')->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'L'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','L') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
					$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="Mashreq-Bookinglink";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 }
 
 
 
 public function exportDocReportloginMisMashreqCards(Request $request)
 {
			$parameters = $request->input(); 
			 /* echo "<pre>";
			 print_r($parameters);
			 exit;  */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Login_MIS_Mashreq_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AB1');
			$sheet->setCellValue('A1', 'Login MIS Mashreq Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('internal_mis_id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('agent_full_name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('all_cda_deviation'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('app_decision'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('app_decisiondetails'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('application_date'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('application_status'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('applicationid'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('ref_no'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('booked_flag'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('bureau_score'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('bureau_segmentation'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('card_type'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('cda_descision'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('cdafinalsalary'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('cif'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('customer_name'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('disbursed_date'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('employee_category_desc'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('employer_name'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('last_comment'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('mis_date'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('mrs_score'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('seller_id'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('stl_format'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('nationality'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('seller_channel_name'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('min_startdate'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  MashreqLoginMIS::where("id",$sid)->first();
				 
				 
				 $indexCounter++; 	
				
				$sheet->setCellValue('A'.$indexCounter, $mis->internal_mis_id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->agent_full_name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mis->all_cda_deviation)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $mis->app_decision)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mis->app_decisiondetails)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mis->application_date)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->application_status)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->applicationid)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $mis->ref_no)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->booked_flag)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $mis->bureau_score)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $mis->bureau_segmentation)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $mis->card_type)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $mis->cda_descision)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $mis->cdafinalsalary)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $mis->cif)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $mis->customer_name)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $mis->disbursed_date)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $mis->employee_category_desc)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $mis->employer_name)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $mis->last_comment)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $mis->mis_date)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $mis->mrs_score)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $mis->seller_id)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Y'.$indexCounter, $mis->stl_format)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $mis->nationality)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter, $mis->seller_channel_name)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter, $mis->min_startdate)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'AB'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:AB1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AB') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
					$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="Mashreq-login";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 }
 
 
 
 public function exportDocReportbankMisMashreqCards(Request $request)
 {
			$parameters = $request->input(); 
			 /* echo "<pre>";
			 print_r($parameters);
			 exit; */  
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Bank_MIS_Mashreq_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:X1');
			$sheet->setCellValue('A1', 'Bank MIS Mashreq Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('rcms_id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('agent_full_name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('all_cda_deviation'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('application_date'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('application_ref_no'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('booked_flag'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('bureau_score'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('bureau_segmentation'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('card_type'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('cda_descision'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('cdafinalsalary'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('customer_name'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('disbursed_date'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('employee_category_desc'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('employer_name'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('final_dsr'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('last_comment'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('mrs_score'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('seller_id'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('status'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('remarks'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('app_decision'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('app_decisiondetails'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('stl_yn'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  MashreqBankMIS::where("id",$sid)->first();
				 
				 
				 $indexCounter++; 	
				
				$sheet->setCellValue('A'.$indexCounter, $mis->rcms_id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->agent_full_name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mis->all_cda_deviation)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $mis->application_date)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mis->application_ref_no)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mis->booked_flag)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->bureau_score)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->bureau_segmentation)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $mis->card_type)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->cda_descision)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $mis->cdafinalsalary)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $mis->customer_name)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $mis->disbursed_date)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $mis->employee_category_desc)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $mis->employer_name)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $mis->final_dsr)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $mis->last_comment)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $mis->mrs_score)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $mis->seller_id)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $mis->status)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $mis->remarks)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $mis->app_decision)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $mis->app_decisiondetails)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $mis->stl_yn)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
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
					$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="Mashreq-Bank";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 }
 
 
 public function exportDocReportmtdMisMashreqCards(Request $request)
 {
			 $parameters = $request->input(); 
			/*  echo "<pre>";
			 print_r($parameters);
			 exit;   */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'MTD_MIS_Mashreq_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:N1');
			$sheet->setCellValue('A1', 'MTD MIS Mashreq Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('instanceid'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('cif_cis_number'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('customername'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('plastictype'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('sellerid'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('sellername'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('dateofdisbursal'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('cdafinalsalary'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('card_status'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('agents_name'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('points'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('product'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('team_manager'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('vertical'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  MashreqMTDMIS::where("id",$sid)->first();
				 
				 
				 $indexCounter++; 	
				
				$sheet->setCellValue('A'.$indexCounter, $mis->instanceid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->cif_cis_number)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mis->customername)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $mis->plastictype)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mis->sellerid)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mis->sellername)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->dateofdisbursal)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->cdafinalsalary)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $mis->card_status)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->agents_name)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $mis->points)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $mis->product)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $mis->team_manager)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $mis->vertical)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'N'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:O1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','N') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 }

 public function exportDocReportFinalmtdMisMashreqCards(Request $request)
 {
			 $parameters = $request->input(); 
			/*  echo "<pre>";
			 print_r($parameters);
			 exit;   */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Final_MTD_MIS_Mashreq_Cards_'.date("d-m-Y-His").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:N1');
			$sheet->setCellValue('A1', 'Final MTD MIS Mashreq Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('instanceid'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('cif_cis_number'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('customername'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('plastictype'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('sellerid'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('sellername'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('dateofdisbursal'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('cdafinalsalary'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('card_status'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('agents_name'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('points'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('product'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('team_manager'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('vertical'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  MashreqFinalMTD::where("id",$sid)->first();
				 
				 
				 $indexCounter++; 	
				
				$sheet->setCellValue('A'.$indexCounter, $mis->instanceid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->cif_cis_number)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mis->customername)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $mis->plastictype)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mis->sellerid)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mis->sellername)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->dateofdisbursal)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->cdafinalsalary)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $mis->card_status)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->agents_name)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $mis->points)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $mis->product)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $mis->team_manager)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $mis->vertical)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'N'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:O1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','N') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
					$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="Mashreq-MTD";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 }
 
 
 public function exportDocReportinternalMisMashreqCards(Request $request)
 {
			 ini_set('max_execution_time',900);
			 ini_set('memory_limit','1600M');
			 $parameters = $request->input(); 
			 /*echo "<pre>";
			print_r($parameters);
			exit; */
	         $selectedId = $parameters['selectedIds'];

			 
	        $filename = 'Internal_MIS_Mashreq_Cards_'.date("d-m-Y").'_'.time().'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:Y1');
			$sheet->setCellValue('A1', 'Internal MIS Mashreq Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->mergeCells('A2:J2');
			$sheet->setCellValue('A2', strtoupper('Internal'))->getStyle('A2')->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			$sheet->mergeCells('K2:L2');
			$sheet->setCellValue('K2', strtoupper('Booking'))->getStyle('K2')->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			$sheet->mergeCells('M2:Y2');
			$sheet->setCellValue('M2', strtoupper('LOGIN'))->getStyle('M2')->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$indexCounter = 3;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('SN. No.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Team'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Submission Date'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Employee ID'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Employee Name'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('F'.$indexCounter, strtoupper('Ref No.'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Customer Name'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');


			$sheet->setCellValue('H'.$indexCounter, strtoupper('Mobile'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Status'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Remarks'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Booking Status'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('L'.$indexCounter, strtoupper('Missing Login'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('M'.$indexCounter, strtoupper('Customer Name Login'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Application Id'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('App Decision'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('CDA Descision'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Status-Login'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Last Comment'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('All CDA Deviation'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('CDA Salary'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('Employer Name'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('Employer Category'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('Bureau Score'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Mrs Score'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('Bureau Segmentation'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			//echo sizeof($selectedId);exit;

			if(@$_REQUEST['all']==1)
			 {
					$whereRaw = " form_id='1' AND (status='1' OR status='2')";
				if(@$request->session()->get('ref_no_string') != '' && @$request->session()->get('form_id') != '')
					{
						$ref_no_string = $request->session()->get('ref_no_string');
						if(strlen($ref_no_string)>4)
						{
							$whereRaw .= " AND ref_no IN (".$ref_no_string.")";	
						}
						else
						{
							$whereRaw .= " AND ref_no IN ('0')";	
						}	
					}

					if(@$request->session()->get('team_internal') != '')
					{
						$team = $request->session()->get('team_internal');
						$team_str = '';
						foreach($team as $team_value)
						{
							if($team_str == '')
							{
								$team_str = "'".$team_value."'";
							}
							else
							{
								$team_str = $team_str.","."'".$team_value."'";
							}
						}
						$whereRaw .= " AND team IN (".$team_str.")";	
						$searchValues['team_internal'] = $team;
						
					}
					


					if(@$request->session()->get('sales_processor_internal') != '')
					{
						$team = array();
						$team_Mahwish_130 = array('Ajay','Mujahid','Akshada','Shahnawaz');
						$team_Umar_168 = array('Arsalan','Zubair');
						$team_Arsalan_129 = array('Mohsin','Sahir');

						$sales_processor_internal = $request->session()->get('sales_processor_internal');
						
						foreach($sales_processor_internal as $sales_processor_internal_value)
						{				
							if($sales_processor_internal_value=='Mahwish')
							{
								$team = array_merge($team,$team_Mahwish_130);
							}
							if($sales_processor_internal_value=='Arsalan')
							{
								$team = array_merge($team,$team_Arsalan_129);
							}
							if($sales_processor_internal_value=='Umer')
							{
								$team = array_merge($team,$team_Umar_168);
							}
						}
						
						
						$team_str = '';
						foreach($team as $team_value)
						{
							if($team_str == '')
							{
								$team_str = "'".$team_value."'";
							}
							else
							{
								$team_str = $team_str.","."'".$team_value."'";
							}
						}
						$whereRaw .= " AND team IN (".$team_str.")";			
						$searchValues['sales_processor_internal'] = $sales_processor_internal;
						
					}

					if(@$request->session()->get('emp_id_internal') != '')
					{
						$emp_id = $request->session()->get('emp_id_internal');
						$emp_id_str = '';
						foreach($emp_id as $emp_id_value)
						{
							if($emp_id_str == '')
							{
								$emp_id_str = "'".$emp_id_value."'";
							}
							else
							{
								$emp_id_str = $emp_id_str.","."'".$emp_id_value."'";
							}
						}
						$whereRaw .= " AND emp_id IN (".$emp_id_str.")";	
						$searchValues['emp_id_internal'] = $emp_id;
						
					}

					if(@$request->session()->get('form_status') != '')
					{
						$form_status = $request->session()->get('form_status');
						$form_status_str = '';
						foreach($form_status as $form_status_value)
						{
							if($form_status_str == '')
							{
								$form_status_str = "'".$form_status_value."'";
							}
							else
							{
								$form_status_str = $form_status_str.","."'".$form_status_value."'";
							}
						}
						$whereRaw .= " AND form_status IN (".$form_status_str.")";			
					}

					if(@$request->session()->get('application_id_internal') != '')
					{
						$application_id_internal = $request->session()->get('application_id_internal');						
						$whereRaw .= " AND application_id = '".$application_id_internal."'";			
					}

					if(@$request->session()->get('missing_login_internal') != '')
					{
						$missing_login_internal = $request->session()->get('missing_login_internal');
						if($missing_login_internal=='Missing in Login (Current Month)')
						{
							$whereRaw .= " AND (application_id = '' OR application_id IS NULL)";
							$whereRaw .= " AND submission_date >='".date('Y-m-01')."'";
							$whereRaw .= " AND submission_date <='".date('Y-m-d')."'";
						}
						else if($missing_login_internal=='Linked From Booking')
						{
							$whereRaw .= " AND missing_booking_link_status = '1'";
							
						}
						else if($missing_login_internal=='Missing in Login')
						{
							$whereRaw .= " AND (application_id = '' OR application_id IS NULL)";
						}
						else if($missing_login_internal=='Missing Mobile Number')
						{
							$whereRaw .= " AND (customer_mobile = '' OR customer_mobile IS NULL)";
						}
						else
						{
							$whereRaw .= " AND (customer_mobile != '' AND customer_mobile IS NOT NULL)";
						}
					}

					if(@$request->session()->get('ref_no_internal') != '')
					{
						$ref_no_internal = $request->session()->get('ref_no_internal');						
						$whereRaw .= " AND ref_no = '".$ref_no_internal."'";			
					}

					if(@$request->session()->get('remarks') != '')
					{
						$remarks = $request->session()->get('remarks');						
						$whereRaw .= " AND remarks LIKE '%".$remarks."%'";			
					}

					if($request->session()->get('start_date_internal') != '')
					{
						$start_date_internal = $request->session()->get('start_date_internal');			
						$whereRaw .= " AND submission_date >='".date('Y-m-d',strtotime($start_date_internal))."'";
						$searchValues['start_date_internal'] = $start_date_internal;			
					}

					if($request->session()->get('end_date_internal') != '')
					{
						$end_date_internal = $request->session()->get('end_date_internal');			
						$whereRaw .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_internal))."'";
						$searchValues['end_date_internal'] = $end_date_internal;			
					}				

					$tbldata = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->orderby('submission_date','DESC')->paginate(10000);
			 }
			 else
			 {

					$tbldata = DepartmentFormEntry::wherein('id',$selectedId)->get();
			 }
			
			$sn = 1;
			foreach ($tbldata as $mis) {
				
				//$mis =  DepartmentFormEntry::where("id",$sid)->first();
				
				 //$Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	

				//$emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');

			$emp_name = $mis->emp_name;

			$submission_date = @$mis->submission_date;
			if($submission_date!='0000-00-00')
			{
				$submission_date = date('d-m-Y',strtotime($mis->submission_date));
			}
			else
			{
				$submission_date='';
			}
			

			//$getrecruiterInfo = DepartmentFormController::getrecruiterInfo(@$Employee_details_data->recruiter);
			
			//$getrecruiterCategoryInfo = DepartmentFormController::getrecruiterCategoryInfo(@$getrecruiterInfo->recruit_cat);
			
				if($mis->status == 1)
                  $status='Activated';
                else
					$status='Deactivated';
               	
				 $htmlContect = html_entity_decode( $mis->form_status );

                   $htmlContect1 = html_entity_decode( $mis->remarks );
				if(strtolower($mis->form_status)=='booked')
				{
                   $bookingStatus = 'Booked';
				}
                else
				{
				   $bookingStatus = 'Not Booked';
				}
                if($mis->MissingLogin == 2)
				{
						$missingLogin = 'Yes'; 
				}
				else
				{
						$missingLogin = 'No';
				}
	  $htmlContect2 = html_entity_decode( $mis->last_comment );

                 
					
                 
				 $indexCounter++; 	
				
				$sheet->setCellValue('A'.$indexCounter, $mis->id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, strtoupper($mis->team))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $submission_date)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $mis->emp_id)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper($emp_name))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');


				$sheet->setCellValue('F'.$indexCounter, $mis->ref_no)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, strtoupper($mis->customer_name))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');


				$sheet->setCellValue('H'.$indexCounter, $mis->customer_mobile)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter,  strip_tags( $htmlContect ))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, strip_tags( $htmlContect1 ))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $bookingStatus)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('L'.$indexCounter, $missingLogin)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('M'.$indexCounter, strtoupper($mis->customername_login))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, $mis->application_id)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper($mis->app_decision))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper($mis->cda_descision))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper($mis->status_login))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper(strip_tags( $htmlContect2 )))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper($mis->all_cda_deviation))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper($mis->cdafinalsalary))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper($mis->employer_name))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper($mis->employee_category_desc))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper($mis->bureau_score))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper($mis->mrs_score))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper($mis->bureau_segmentation))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
				
				$sn++;
				
			}


			
			
			  for($col = 'A'; $col !== 'Y'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="Mashreq-Internal";					
				$logObj->save();
				$spreadsheet->getActiveSheet()->getStyle('A1:Y1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				
				$spreadsheet->getActiveSheet()->getStyle('A2:J2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ff7e7e');
				
				
				$spreadsheet->getActiveSheet()->getStyle('K2:L2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffa210');
				
				
				$spreadsheet->getActiveSheet()->getStyle('M2:Y2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('7eff9f');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','Y') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 }

 public function exportDocReportinternalMisMashreqCardsEmp(Request $request)
 {
			 $parameters = $request->input(); 
			/* echo "<pre>";
			print_r($parameters);
			exit; */
			
			$team_Mahwish_130 = array('Ajay','AJAY','Mujahid','Akshada','Shahnawaz','Anas');
			$team_Umar_168 = array('Arsalan','Zubair','Umer');
			$team_Arsalan_129 = array('Mohsin','Sahir','Sahir Arsalan');

				

			$whereRawMain = "emp_id!='' and form_id='1'";
			$whereRawBooking = "instanceid!=''";
			$whereRawLogin = "ref_no!=''";
			$title = 'Agent Performance Mashreq Cards - '.date("d/m/Y");
			if($request->session()->get('start_date_internal') != '')
			{
				$start_date_internal = $request->session()->get('start_date_internal');			
				$whereRawMain .= " AND submission_date >='".date('Y-m-d',strtotime($start_date_internal))."'";
				$whereRawBooking .= " AND dateofdisbursal >='".date('Y-m-d',strtotime($start_date_internal))."'";
				$whereRawLogin .= " AND application_date >='".date('Y-m-d',strtotime($start_date_internal))."'";
				$title = 'Agent Performance Mashreq Cards: - '.date('d/m/Y',strtotime($start_date_internal)).' - '.date("d/m/Y");

				$previousdate =  date('Y-m-d', strtotime($start_date_internal." -1 month"));
				$pYear = date("Y",strtotime($previousdate));
				$pMonth = date("m",strtotime($previousdate));
				$first_day_of_last_month = $pYear."-".$pMonth."-01";
				$last_day_of_last_month = $pYear."-".$pMonth."-31";				

			}
			else
			{
				$start_date_internal = date('Y-m-01');			
				$whereRawMain .= " AND submission_date >='".date('Y-m-d',strtotime($start_date_internal))."'";
				$whereRawBooking .= " AND dateofdisbursal >='".date('Y-m-d',strtotime($start_date_internal))."'";
				$whereRawLogin .= " AND application_date >='".date('Y-m-d',strtotime($start_date_internal))."'";
				$title = 'Agent Performance Mashreq Cards: - '.date('d/m/Y',strtotime($start_date_internal)).' - '.date("d/m/Y");

				$first_day_of_last_month =  date('Y-m-d', strtotime('first day of last month'));
				$last_day_of_last_month =  date('Y-m-d', strtotime('last day of last month'));
			}

			if($request->session()->get('end_date_internal') != '')
			{
				$end_date_internal = $request->session()->get('end_date_internal');			
				$whereRawMain .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_internal))."'";
				$whereRawBooking .= " AND dateofdisbursal <='".date('Y-m-d',strtotime($end_date_internal))."'";
				$whereRawLogin .= " AND application_date <='".date('Y-m-d',strtotime($end_date_internal))."'";
				$title = 'Agent Performance Mashreq Cards: - '.date('d/m/Y',strtotime($start_date_internal)).' - '.date('d/m/Y',strtotime($end_date_internal));
						
			}
			else
			{
				$end_date_internal = date('Y-m-d');				
				$whereRawMain .= " AND submission_date <='".date('Y-m-d',strtotime($end_date_internal))."'";
				$whereRawBooking .= " AND dateofdisbursal <='".date('Y-m-d',strtotime($end_date_internal))."'";
				$whereRawLogin .= " AND application_date <='".date('Y-m-d',strtotime($end_date_internal))."'";
				$title = 'Agent Performance Mashreq Cards: - '.date('d/m/Y',strtotime($start_date_internal)).' - '.date('d/m/Y',strtotime($end_date_internal));
			}
			
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Agent_Performance_Mashreq_Cards_'.date("d-m-Y").time().'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AB1');
			$sheet->setCellValue('A1', $title)->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 4;			
			
			$sheet->setCellValue('A'.$indexCounter, strtoupper('TL'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee ID'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Designation'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Processor'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Submission'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Booking'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Last Month Bookings'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Last Month Bookings (Final MTD)'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Range ID'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('VINTAGE'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('T-1 Submission'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('T-2 Submission'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Recruiter'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Recruiter Category'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');			
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Agent Salary'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('Q'.$indexCounter, strtoupper('5-7k'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('7-10k'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('10-15k'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('15K+'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('STP %'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('V'.$indexCounter, strtoupper('SUBMISSION TO BOOKING'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('W'.$indexCounter, strtoupper('JOURNEY TO BOOKING'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('X'.$indexCounter, strtoupper('JOURNEY TO SUBMISSION'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('Y'.$indexCounter, strtoupper('No Hit'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('Poor'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('Thin 2'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('Thin 1'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter, strtoupper('Rich'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$whereRawGroup = " GROUP BY emp_id";

			
			$last_day = date('Y-m-d', strtotime('last day'));
			$last_2day = date('Y-m-d', strtotime('-2 day'));

			
			$selectedId = DB::table('department_form_parent_entry')->whereRaw($whereRawMain.$whereRawGroup)->orderby('submission_date','DESC')->get(['team','emp_id']);
			$sn = 1;
			$emp_id_submitted = "'0',";
			$SubmitCountTotal = 0;
			$BookingCountTotal = 0;
			foreach ($selectedId as $mis) {
				$Processor ='';
				if(in_array($mis->team,$team_Mahwish_130))
				{
					$Processor = 'Mahwish';
				}
				if(in_array($mis->team,$team_Umar_168))
				{
					$Processor = 'Umar';
				}
				if(in_array($mis->team,$team_Arsalan_129))
				{
					$Processor = 'Arsalan';
				}				
				
				$whereRaw = $whereRawMain." AND emp_id='".$mis->emp_id."'";
				$emp_id_submitted .= "'".$mis->emp_id."',";
				
		
				$BookingCountQ = DB::table('mashreq_booking_mis')->whereRaw($whereRawBooking." AND emp_id='".$mis->emp_id."'")->get(['id'])->count();
				$BookingCount = $BookingCountQ;

				$BookingCountTotal = $BookingCountTotal+$BookingCount;
				
				$SubmissionCountQuery = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->get(['ref_no']);
				$ref_nos = "'0',";
				foreach($SubmissionCountQuery as $SubmissionData)
				{
					$ref_nos .= "'".$SubmissionData->ref_no."',";
				}
				$ref_nos = substr($ref_nos,0,-1);

				$SubmissionCount = count($SubmissionCountQuery);

				$SubmitCountTotal = $SubmitCountTotal+$SubmissionCount;
				
				 $Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	

			$emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');
			$offline_status= @$Employee_details_data->offline_status;
			

			$getrecruiterInfo = DepartmentFormController::getrecruiterInfo(@$Employee_details_data->recruiter);
			
			$getrecruiterCategoryInfo = DepartmentFormController::getrecruiterCategoryInfo(@$getrecruiterInfo->recruit_cat);
			
				
				 $indexCounter++; 					
				
				$sheet->setCellValue('A'.$indexCounter, $mis->team)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				
				$sheet->setCellValue('B'.$indexCounter, $mis->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('C'.$indexCounter, $emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');



				$Designation = $this->getDesignation($mis->emp_id);

				$sheet->setCellValue('D'.$indexCounter, $Designation)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('E'.$indexCounter, $Processor)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('F'.$indexCounter, $SubmissionCount)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('G'.$indexCounter, $BookingCount)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				

				$sheet->setCellValue('H'.$indexCounter, $this->lastMonthBooking($mis->emp_id,$start_date_internal,$offline_status))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('I'.$indexCounter, $this->lastMonthBookingFinalMTD($mis->emp_id,$start_date_internal))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$vintageDays = '-';
					$doj = '';
					$empAttr = Employee_attribute::where("emp_id",$mis->emp_id)->where("attribute_code","DOJ")->first();
					if($empAttr != '')
						{
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									$doj = date("Y-m-d",strtotime($doj));
									$vintageDays = abs(strtotime($end_date_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}

				$sheet->setCellValue('J'.$indexCounter, $this->getRangeID($vintageDays))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('K'.$indexCounter, $vintageDays)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				

				$T1Submission = DB::table('department_form_parent_entry')->whereRaw("submission_date='".$last_day."' AND emp_id='".$mis->emp_id."' and form_id='1'")->get(['id'])->count();

				$sheet->setCellValue('L'.$indexCounter, @$T1Submission)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				
				$T2Submission = DB::table('department_form_parent_entry')->whereRaw("submission_date>='".$last_2day."' AND submission_date<='".$last_day."' AND emp_id='".$mis->emp_id."' and form_id='1'")->get(['id'])->count();

				$sheet->setCellValue('M'.$indexCounter, @$T2Submission)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('N'.$indexCounter, @$getrecruiterCategoryInfo->name)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('O'.$indexCounter, @$getrecruiterInfo->name)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$emp_salary= $this->getAgentSalary($mis->emp_id);
				$sheet->setCellValue('P'.$indexCounter, $emp_salary)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$login5to7k = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id='".$mis->emp_id."' and cdafinalsalary>=5000 AND cdafinalsalary<=7000 AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$login5to7k = round(($login5to7k/$SubmissionCount),2);
				
				$sheet->setCellValue('Q'.$indexCounter, $login5to7k)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$login7to10k = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id='".$mis->emp_id."' and cdafinalsalary>7000 AND cdafinalsalary<=10000 AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$login7to10k = round(($login7to10k/$SubmissionCount),2);

				$sheet->setCellValue('R'.$indexCounter, $login7to10k)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$login10to15k = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id='".$mis->emp_id."' and cdafinalsalary>10000 AND cdafinalsalary<=15000 AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$login10to15k = round(($login10to15k/$SubmissionCount),2);

				$sheet->setCellValue('S'.$indexCounter, $login10to15k)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$login15kplus = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id='".$mis->emp_id."' and cdafinalsalary>15000 AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$login15kplus = round(($login15kplus/$SubmissionCount),2);

				$sheet->setCellValue('T'.$indexCounter, $login15kplus)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$STPCount = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id='".$mis->emp_id."' and application_status='STP Disbursed' AND booking_status='1' AND ref_no IN (".$ref_nos.")")->get(['id'])->count();

				$JourneyCount = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id='".$mis->emp_id."' AND ref_no IN (".$ref_nos.")")->get(['id'])->count();

				$STP_Percent = 0;

				if($BookingCount>0)
				{
					$STP_Percent = ($STPCount/$BookingCount);
				}
				
				$J_2_B_Percent = 0;
				$J_2_S_Percent = 0;
				if($JourneyCount>0)
				{
					$J_2_B_Percent = ($BookingCount/$JourneyCount);
					$J_2_S_Percent = ($SubmissionCount/$JourneyCount);
				}
				

				$sheet->setCellValue('U'.$indexCounter, number_format($STP_Percent,2))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				if($SubmissionCount>0)
				{
					$approvalRate = ($BookingCount/$SubmissionCount);
				}
				else
				{
					$approvalRate = 0;
				}

				$sheet->setCellValue('V'.$indexCounter, number_format($approvalRate,2))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('W'.$indexCounter, number_format($J_2_B_Percent,2))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('X'.$indexCounter, number_format($J_2_S_Percent,2))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$loginHoHit = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id='".$mis->emp_id."' and bureau_segmentation='NO HIT' AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$totalNOHITValue = round(($loginHoHit/$SubmissionCount),2);
				$sheet->setCellValue('Y'.$indexCounter, $totalNOHITValue)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				

				$loginPoor = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id='".$mis->emp_id."' and bureau_segmentation='POOR' AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$totalPOORValue = round(($loginPoor/$SubmissionCount),2);
				$sheet->setCellValue('Z'.$indexCounter, $totalPOORValue)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$loginThin2 = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id='".$mis->emp_id."' and bureau_segmentation='THIN2' AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$totalTHIN2Value = round(($loginThin2/$SubmissionCount),2);
				$sheet->setCellValue('AA'.$indexCounter, $totalTHIN2Value)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$loginThin1 = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id='".$mis->emp_id."' and bureau_segmentation='THIN1' AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$totalTHIN1Value = round(($loginThin1/$SubmissionCount),2);
				$sheet->setCellValue('AB'.$indexCounter, $totalTHIN1Value)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$loginRich = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id='".$mis->emp_id."' and bureau_segmentation='RICH' AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$totalRICHValue = round(($loginRich/$SubmissionCount),2);
				$sheet->setCellValue('AC'.$indexCounter, $totalRICHValue)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				
				
				
				$sn++;
				
			}

			/*
			*adding Sales Agent with zero Submission
			*Start Coding
			*/

			$emp_id_submittedVal = substr($emp_id_submitted,0,-1);
			//$whereRawEmp = " dept_id='36' AND offline_status='1' AND emp_id NOT IN (".$emp_id_submitted.") AND job_function='2' AND (job_role='Relationship Officer- Cards' OR job_role='Team Leader- Cards')";

			$whereRawEmp = " dept_id='36' AND designation_by_doc_collection NOT IN ('4','12','44','46') AND emp_id NOT IN (".$emp_id_submittedVal.") AND job_function='2' AND emp_id!='#N/A' order by emp_id";

			$selectedId = DB::table('employee_details')->whereRaw($whereRawEmp)->get();
			
			foreach ($selectedId as $mis) {
				
				if($mis->offline_status!='1')
				{
					$offlineEmp = DB::table('offline_empolyee_details')->whereRaw("emp_id='".$mis->emp_id."' AND last_working_day_resign>='".date('Y-m-d',strtotime($start_date_internal))."' AND last_working_day_resign IS NOT NULL")->get();
					if(count($offlineEmp)>0)
					{
						//Data will be added in report
					}
					else
					{
						continue;
					}
				}


				$tlNameDB = $mis->team = ucwords(strtolower($mis->tl_name));
							
				
				$whereRaw = $whereRawMain." AND emp_id='".$mis->emp_id."'";				

				$BookingCountQ = DB::table('mashreq_booking_mis')->whereRaw($whereRawBooking." AND emp_id='".$mis->emp_id."'")->get(['id'])->count();
				$BookingCount = $BookingCountQ;			
				

				$SubmissionCount = 0;			
				
			$emp_id_submitted .= "'".@$mis->emp_id."',";
			$emp_name= @$mis->first_name.(@$mis->middle_name ? " ".@$mis->middle_name:'').(@$mis->last_name?" ".@$mis->last_name:'');
			$offline_status= @$mis->offline_status;

			$getrecruiterInfo = DepartmentFormController::getrecruiterInfo(@$mis->recruiter);
			
			$getrecruiterCategoryInfo = DepartmentFormController::getrecruiterCategoryInfo(@$getrecruiterInfo->recruit_cat);



					$vintageDays = '-';
					$doj = '';
					$empAttr = Employee_attribute::where("emp_id",$mis->emp_id)->where("attribute_code","DOJ")->first();
					if($empAttr != '')
						{
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									$doj = date("Y-m-d",strtotime($doj));
									$vintageDays = abs(strtotime($end_date_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
			
				
				 $indexCounter++; 	
				 
				 $tlName = ucwords(strtolower($this->getTLName($mis->emp_id)));
				 $tlName = str_replace('Sahir Arsalan','Arsalan',$tlName);
				 //$tlName = str_replace('Mohammed','Sahir',$tlName);
				 //$tlName = str_replace('Muhammad','Sahir',$tlName);
				
				$tlName = $tlNameDB?$tlNameDB:$tlName;
				 $Processor ='';
				if(in_array($tlName,$team_Mahwish_130))
				{
					$Processor = 'Mahwish';
				}
				if(in_array($tlName,$team_Umar_168))
				{
					$Processor = 'Umar';
				}
				if(in_array($tlName,$team_Arsalan_129))
				{
					$Processor = 'Arsalan';
				}	
				
				$sheet->setCellValue('A'.$indexCounter, $tlName)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				
				$sheet->setCellValue('B'.$indexCounter, $mis->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('C'.$indexCounter, $emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$Designation = $this->getDesignation($mis->emp_id);

				$sheet->setCellValue('D'.$indexCounter, $Designation)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('E'.$indexCounter, $Processor)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('F'.$indexCounter, $SubmissionCount)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('G'.$indexCounter, $BookingCount)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('G'.$indexCounter, 0)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				

				$sheet->setCellValue('H'.$indexCounter, $this->lastMonthBooking($mis->emp_id,$start_date_internal,$offline_status))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				
				$sheet->setCellValue('I'.$indexCounter, $this->lastMonthBookingFinalMTD($mis->emp_id,$start_date_internal))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('J'.$indexCounter, $this->getRangeID($vintageDays))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');			
								

				$sheet->setCellValue('K'.$indexCounter, $vintageDays)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$T1Submission = '0';

				$sheet->setCellValue('L'.$indexCounter, $T1Submission)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$T2Submission = '0';

				$sheet->setCellValue('M'.$indexCounter, $T2Submission)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('N'.$indexCounter, @$getrecruiterCategoryInfo->name)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('O'.$indexCounter, @$getrecruiterInfo->name)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$emp_salary= $this->getAgentSalary($mis->emp_id);

				$sheet->setCellValue('P'.$indexCounter, $emp_salary)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				
				$sheet->setCellValue('Q'.$indexCounter, 0)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				

				$sheet->setCellValue('R'.$indexCounter, 0)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				
				$sheet->setCellValue('S'.$indexCounter, 0)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				

				$sheet->setCellValue('T'.$indexCounter, 0)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('U'.$indexCounter, 0)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('V'.$indexCounter, 0)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('W'.$indexCounter, 0)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('X'.$indexCounter, 0)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('Y'.$indexCounter, 0)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('Z'.$indexCounter, 0)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('AA'.$indexCounter, 0)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('AB'.$indexCounter, 0)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('AC'.$indexCounter, 0)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				
			}

			$emp_id_submittedValue = substr($emp_id_submitted,0,-1);


			$previousdateMissingEmp =  date('Y-m-d', strtotime($start_date_internal." -1 month"));
				$pYearMissing = date("Y",strtotime($previousdateMissingEmp));
				$pMonthMissing = date("m",strtotime($previousdateMissingEmp));
				$startDateMissing = $pYearMissing."-".$pMonthMissing."-01";
				
				$whereRawLast = "emp_id!='' and form_id='1' and submission_date >= '".$startDateMissing."' and emp_id NOT IN (".$emp_id_submittedValue.") AND emp_id!='#N/A'";

				$selectedId = DB::table('department_form_parent_entry')->whereRaw($whereRawLast.$whereRawGroup)->orderby('submission_date','DESC')->get(['team','emp_id']);


				 /*$collectionModelMissing = DepartmentFormEntry::selectRaw('emp_id,team')
												  ->groupBy('emp_id')
												  ->where('form_id','1')
												  ->whereDate('submission_date', '>=', $startDateMissing)
												  ->whereNotIn('emp_id',$emp_id_submittedValue)
												  ->get();*/

			
			//$whereRawEmp = " dept_id='36' AND offline_status='1' AND emp_id NOT IN (".$emp_id_submitted.") AND job_function='2' AND (job_role='Relationship Officer- Cards' OR job_role='Team Leader- Cards')";

			//$whereRawEmp = " dept_id='36' AND emp_id NOT IN (".$emp_id_submittedVal.") AND job_function='2' order by emp_id";

			//$selectedId = DB::table('employee_details')->whereRaw($whereRawEmp)->get();
			
			foreach ($selectedId as $mis) {				

				$tlNameDB = $mis->team = ucwords(strtolower($mis->team));
							
				
				$whereRaw = $whereRawMain." AND emp_id='".$mis->emp_id."'";				

				$BookingCountQ = DB::table('mashreq_booking_mis')->whereRaw($whereRawBooking." AND emp_id='".$mis->emp_id."'")->get(['id'])->count();
				$BookingCount = $BookingCountQ;			
				

				$SubmissionCount = 0;			
				

			$Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	

			$emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');
			$offline_status= @$Employee_details_data->offline_status;
			

			$getrecruiterInfo = DepartmentFormController::getrecruiterInfo(@$Employee_details_data->recruiter);
			
			$getrecruiterCategoryInfo = DepartmentFormController::getrecruiterCategoryInfo(@$getrecruiterInfo->recruit_cat);



					$vintageDays = '-';
					$doj = '';
					$empAttr = Employee_attribute::where("emp_id",$mis->emp_id)->where("attribute_code","DOJ")->first();
					if($empAttr != '')
						{
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									$doj = date("Y-m-d",strtotime($doj));
									$vintageDays = abs(strtotime($end_date_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}
			
				
				 $indexCounter++; 	
				 
				 $tlName = ucwords(strtolower($this->getTLName($mis->emp_id)));
				 $tlName = str_replace('Sahir Arsalan','Arsalan',$tlName);
				 //$tlName = str_replace('Mohammed','Sahir',$tlName);
				 //$tlName = str_replace('Muhammad','Sahir',$tlName);
				 $tlName = $tlNameDB?$tlNameDB:$tlName;
				 


				 $Processor ='';
				if(in_array($tlName,$team_Mahwish_130))
				{
					$Processor = 'Mahwish';
				}
				if(in_array($tlName,$team_Umar_168))
				{
					$Processor = 'Umar';
				}
				if(in_array($tlName,$team_Arsalan_129))
				{
					$Processor = 'Arsalan';
				}	
				
				$sheet->setCellValue('A'.$indexCounter, $tlName)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				
				$sheet->setCellValue('B'.$indexCounter, $mis->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('C'.$indexCounter, $emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$Designation = $this->getDesignation($mis->emp_id);

				$sheet->setCellValue('D'.$indexCounter, $Designation)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('E'.$indexCounter, $Processor)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('F'.$indexCounter, $SubmissionCount)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('G'.$indexCounter, $BookingCount)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('G'.$indexCounter, 0)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				

				$sheet->setCellValue('H'.$indexCounter, $this->lastMonthBooking($mis->emp_id,$start_date_internal,$offline_status))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				
				$sheet->setCellValue('I'.$indexCounter, $this->lastMonthBookingFinalMTD($mis->emp_id,$start_date_internal))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('J'.$indexCounter, $this->getRangeID($vintageDays))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');			
								

				$sheet->setCellValue('K'.$indexCounter, $vintageDays)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$T1Submission = '0';

				$sheet->setCellValue('L'.$indexCounter, $T1Submission)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$T2Submission = '0';

				$sheet->setCellValue('M'.$indexCounter, $T2Submission)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('N'.$indexCounter, @$getrecruiterCategoryInfo->name)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('O'.$indexCounter, @$getrecruiterInfo->name)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$emp_salary= $this->getAgentSalary($mis->emp_id);

				$sheet->setCellValue('P'.$indexCounter, $emp_salary)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				
				$sheet->setCellValue('Q'.$indexCounter, 0)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				

				$sheet->setCellValue('R'.$indexCounter, 0)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				
				$sheet->setCellValue('S'.$indexCounter, 0)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				

				$sheet->setCellValue('T'.$indexCounter, 0)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('U'.$indexCounter, 0)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('V'.$indexCounter, 0)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('W'.$indexCounter, 0)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('X'.$indexCounter, 0)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('Y'.$indexCounter, 0)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('Z'.$indexCounter, 0)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('AA'.$indexCounter, 0)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('AB'.$indexCounter, 0)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('AC'.$indexCounter, 0)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				
			}

			///////////// Total ////////

				$indexCounter++; 				
				$sheet->setCellValue('A'.$indexCounter, 'Total')->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');
	
				$sheet->setCellValue('F'.$indexCounter, $SubmitCountTotal)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('right')->setVertical('top');				
				$sheet->setCellValue('G'.$indexCounter, $BookingCountTotal)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('right')->setVertical('top');

				/////////////Total ////////

				///////////// Bookings not claimed in Internal MIS ////////

				$indexCounter++; 
				$indexCounter++;
				$sheet->setCellValue('A'.$indexCounter, 'Bookings not claimed in Internal MIS')->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');

				$MSCountTotal = DB::table('mashreq_booking_mis')->whereRaw($whereRawBooking." AND emp_id IS NULL")->get(['id'])->count();
				
				$sheet->setCellValue('G'.$indexCounter, $MSCountTotal)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('right')->setVertical('top');

				$indexCounter++;
				$sheet->setCellValue('A'.$indexCounter, 'Bookings not captured in Login MIS')->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');

				$NCLoginCountTotal = DB::table('mashreq_booking_mis')->whereRaw($whereRawBooking." AND (ref_no IS NULL OR ref_no='')")->get(['id'])->count();
				
				$sheet->setCellValue('G'.$indexCounter, $NCLoginCountTotal)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('right')->setVertical('top');

				$indexCounter++;
				$sheet->setCellValue('A'.$indexCounter, 'Bookings not claimed in MTD MIS')->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');

				$NCMTDCountTotal = DB::table('mashreq_booking_mis')->whereRaw($whereRawBooking." AND data_from_mtd='1'")->get(['id'])->count();
				
				$sheet->setCellValue('G'.$indexCounter, $NCMTDCountTotal)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('right')->setVertical('top');

				

				///////////// Bookings not claimed in Internal MIS ////////
				
			
			
			  for($col = 'A'; $col !== 'AC'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:AC1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter-3;$index++)
				{
					  foreach (range('A','AC') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				
	
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="Mashreq-Final Report";					
				$logObj->save();
				$spreadsheet->getActiveSheet()->setTitle('Master Reports');

				$spreadsheet->createSheet(1); 
				$spreadsheet->setActiveSheetIndex(1); 
				$spreadsheet->getActiveSheet()->setTitle('CM Perf. Analysis');
				/*
				*Sheet2
				*/
				$this->sheet2Performance($spreadsheet,$title,$whereRawMain,$whereRawLogin,$whereRawBooking,$start_date_internal,$end_date_internal);

				$spreadsheet->createSheet(2); 
				$spreadsheet->setActiveSheetIndex(2); 
				$spreadsheet->getActiveSheet()->setTitle('TL Reports');
				/*
				*Sheet2
				*/
				$this->sheet3Performance($spreadsheet,$title,$whereRawMain,$whereRawLogin,$whereRawBooking,$start_date_internal,$end_date_internal);

				
				$spreadsheet->createSheet(3); 
				$spreadsheet->setActiveSheetIndex(3); 
				$spreadsheet->getActiveSheet()->setTitle('Flag Details');
				/*
				*Sheet3
				*/
				$this->sheet4FlagDetails($spreadsheet,$title,$whereRawMain,$whereRawLogin,$whereRawBooking,$start_date_internal,$end_date_internal);

				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 }

	protected function sheet2Performance($spreadsheet,$title,$whereRawMain,$whereRawLogin,$whereRawBooking,$start_date_internal,$end_date_internal)
	{
			$start_date_internal = date('Y-m-01');
			$end_date_internal = date('Y-m-d');
			
			$title = 'CM Performance Analysis';
			$team_Mahwish_130 = array('Ajay','AJAY','Mujahid','Akshada','Shahnawaz','Anas');
			$team_Umar_168 = array('Arsalan','Zubair','Umer');
			$team_Arsalan_129 = array('Mohsin','Sahir','Sahir Arsalan');

			$currentMonth =  date('M');
			$lastMonth =  date('M', strtotime('-1 month'));
			$lastToLastMonth =  date('M', strtotime('-2 month'));

			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:M1');
			$sheet->setCellValue('A1', $title)->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 4;			
			
			$sheet->setCellValue('A'.$indexCounter, strtoupper('TL'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee ID'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('D'.$indexCounter, strtoupper($currentMonth.' Submission'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper($currentMonth.' Booking'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('F'.$indexCounter, strtoupper($lastMonth. ' Submission'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper($lastMonth. ' Booking'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('H'.$indexCounter, strtoupper($lastToLastMonth. ' Submission'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper($lastToLastMonth. ' Booking'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');


			$sheet->setCellValue('J'.$indexCounter, strtoupper('DOJ'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('K'.$indexCounter, strtoupper('Range ID'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('VINTAGE'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('M'.$indexCounter, strtoupper('LWD'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			

			$whereRawGroup = " GROUP BY emp_id";

			
			$last_day = date('Y-m-d', strtotime('last day'));
			$last_2day = date('Y-m-d', strtotime('-2 day'));

			
			$selectedId = DB::table('department_form_parent_entry')->whereRaw($whereRawMain.$whereRawGroup)->orderby('submission_date','DESC')->get(['team','emp_id']);
			$sn = 1;
			$emp_id_submitted = "'0',";
			$SubmitCountTotal = 0;
			$BookingCountTotal = 0;
			foreach ($selectedId as $mis) {
				$Processor ='';
				if(in_array($mis->team,$team_Mahwish_130))
				{
					$Processor = 'Mahwish';
				}
				if(in_array($mis->team,$team_Umar_168))
				{
					$Processor = 'Umar';
				}
				if(in_array($mis->team,$team_Arsalan_129))
				{
					$Processor = 'Arsalan';
				}				
				
				$whereRaw = $whereRawMain." AND emp_id='".$mis->emp_id."'";
				$emp_id_submitted .= "'".$mis->emp_id."',";	
				
				
				$BookingCountQ = DB::table('mashreq_booking_mis')->whereRaw($whereRawBooking." AND emp_id='".$mis->emp_id."'")->get(['id'])->count();
				$BookingCount = $BookingCountQ;

				$BookingCountTotal = $BookingCountTotal+$BookingCount;
				
				$SubmissionCountQuery = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->get(['ref_no']);
				$ref_nos = "'0',";
				foreach($SubmissionCountQuery as $SubmissionData)
				{
					$ref_nos .= "'".$SubmissionData->ref_no."',";
				}
				$ref_nos = substr($ref_nos,0,-1);

				$SubmissionCount = count($SubmissionCountQuery);

				$SubmitCountTotal = $SubmitCountTotal+$SubmissionCount;
				
				$Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	

			  $emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');

			  $offline_status= @$Employee_details_data->offline_status;

				$LWD = '';
				$diff = '';
				if($offline_status != '1')
				{
					$LWD = @$this->getEmpLWD($mis->emp_id);
					if($LWD!='')
					{
						$LWD = date('d-m-Y',strtotime($LWD));

						$diff = strtotime(date('Y-m-01')) - strtotime($LWD);
						if($diff>0 && $SubmissionCount==0)
						{
							$SubmissionCount = 'NA';							
						}
						if($diff>0 && $BookingCount==0)
						{							
							$BookingCount = 'NA';
						}
					}
				}
			

			$getrecruiterInfo = DepartmentFormController::getrecruiterInfo(@$Employee_details_data->recruiter);
			
			$getrecruiterCategoryInfo = DepartmentFormController::getrecruiterCategoryInfo(@$getrecruiterInfo->recruit_cat);

			$vintageDays = '-';
			$doj = '';
			$empAttr = Employee_attribute::where("emp_id",$mis->emp_id)->where("attribute_code","DOJ")->first();
			if($empAttr != '')
				{
						$dojEmp = $empAttr->attribute_values;
						if($dojEmp != '' && $dojEmp != NULL)
						{
							$doj = str_replace("/","-",$dojEmp);
							$doj = date("Y-m-d",strtotime($doj));
							$vintageDays = abs(strtotime($end_date_internal)-strtotime($doj))/ (60 * 60 * 24);
						}
				}

				$lastMonthSubmission = $this->lastMonthSubmission($mis->emp_id,$start_date_internal,$offline_status);
				$lastMonthBooking = $this->lastMonthBooking($mis->emp_id,$start_date_internal,$offline_status);

				$lastToLastMonthSubmission =  $this->lastToLastMonthSubmission($mis->emp_id,$start_date_internal,$offline_status);
				$lastToLastMonthBooking = $this->lastToLastMonthBooking($mis->emp_id,$start_date_internal,$offline_status);

				

				$diffDOJCurrentMonth = '';
				$diffDOJLastMonth = '';
				$diffDOJLastToLastMonth = '';
				if($doj!='')
				{				
					$currM = date('Y-m-d');					
					$LastM = date('Y-m-31', strtotime($currM." -1 month"));
					$LasToLastM = date('Y-m-31', strtotime($currM." -2 month"));					

					$diffDOJLastMonth = strtotime($doj) - strtotime($LastM);
					
					if($diffDOJLastMonth>0 && $lastMonthSubmission==0)
					{
						$lastMonthSubmission = 'DOJ NA';							
					}
					if($diffDOJLastMonth>0 && $lastMonthBooking==0)
					{							
						$lastMonthBooking = 'DOJ NA';
					}

					$diffDOJLastToLastMonth = strtotime($doj) - strtotime($LasToLastM);
					if($diffDOJLastToLastMonth>0 && $lastToLastMonthSubmission==0)
					{
						$lastToLastMonthSubmission = 'DOJ NA';							
					}
					if($diffDOJLastToLastMonth>0 && $lastToLastMonthBooking==0)
					{							
						$lastToLastMonthBooking = 'DOJ NA';
					}
				}
			
			
				
				 $indexCounter++; 					
				
				$sheet->setCellValue('A'.$indexCounter, $mis->team)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				
				$sheet->setCellValue('B'.$indexCounter, $mis->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('C'.$indexCounter, $emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('D'.$indexCounter, $SubmissionCount)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $BookingCount)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('F'.$indexCounter,$lastMonthSubmission)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter,$lastMonthBooking)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('H'.$indexCounter,$lastToLastMonthSubmission)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $lastToLastMonthBooking)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				

					

				$sheet->setCellValue('J'.$indexCounter, ($doj?date("d-m-Y",strtotime($doj)):'NA'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('K'.$indexCounter, $this->getRangeID($vintageDays))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('L'.$indexCounter, $vintageDays)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				

				$sheet->setCellValue('M'.$indexCounter, $LWD)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				

				
				
				
				
				$sn++;
				
			}

			/*
			*adding Sales Agent with zero Submission
			*Start Coding
			*/

			$emp_id_submittedVal = substr($emp_id_submitted,0,-1);
			//$whereRawEmp = " dept_id='36' AND offline_status='1' AND emp_id NOT IN (".$emp_id_submitted.") AND job_function='2' AND (job_role='Relationship Officer- Cards' OR job_role='Team Leader- Cards')";

			$whereRawEmp = " dept_id='36' AND designation_by_doc_collection NOT IN ('4','12','44','46') AND emp_id NOT IN (".$emp_id_submittedVal.") AND job_function='2' AND emp_id!='#N/A' order by emp_id";

			$selectedId = DB::table('employee_details')->whereRaw($whereRawEmp)->get();
			
			foreach ($selectedId as $mis) {

				$LWD = '';
				$diff = '';
				if($mis->offline_status!='1')
				{
					$offlineEmp = DB::table('offline_empolyee_details')->whereRaw("emp_id='".$mis->emp_id."' AND last_working_day_resign>='".date('Y-m-d',strtotime($start_date_internal))."' AND last_working_day_resign IS NOT NULL")->get();
					if(count($offlineEmp)>0)
					{
						//Data will be added in report
						$LWD = @$this->getEmpLWD($mis->emp_id);
						if($LWD!='')
						{
							$LWD = date('d-m-Y',strtotime($LWD));
							$diff = strtotime(date('Y-m-01')) - strtotime($LWD);
							
						}
					}
					else
					{
						continue;
					}
				}


				$tlNameDB = $mis->team = ucwords(strtolower($mis->tl_name));
							
				
				$whereRaw = $whereRawMain." AND emp_id='".$mis->emp_id."'";				

				$BookingCountQ = DB::table('mashreq_booking_mis')->whereRaw($whereRawBooking." AND emp_id='".$mis->emp_id."'")->get(['id'])->count();
				$BookingCount = $BookingCountQ;

				$SubmissionCount = 0;			
				
			$emp_id_submitted .= "'".@$mis->emp_id."',";
			$emp_name= @$mis->first_name.(@$mis->middle_name ? " ".@$mis->middle_name:'').(@$mis->last_name?" ".@$mis->last_name:'');
			$offline_status = @$mis->offline_status;

			$getrecruiterInfo = DepartmentFormController::getrecruiterInfo(@$mis->recruiter);
			
			$getrecruiterCategoryInfo = DepartmentFormController::getrecruiterCategoryInfo(@$getrecruiterInfo->recruit_cat);



					$vintageDays = '-';
					$doj = '';
					$empAttr = Employee_attribute::where("emp_id",$mis->emp_id)->where("attribute_code","DOJ")->first();
					if($empAttr != '')
						{
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									$doj = date("Y-m-d",strtotime($doj));
									$vintageDays = abs(strtotime($end_date_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}

						$lastMonthSubmission = $this->lastMonthSubmission($mis->emp_id,$start_date_internal,$offline_status);
						$lastMonthBooking = $this->lastMonthBooking($mis->emp_id,$start_date_internal,$offline_status);

						$lastToLastMonthSubmission =  $this->lastToLastMonthSubmission($mis->emp_id,$start_date_internal,$offline_status);
						$lastToLastMonthBooking = $this->lastToLastMonthBooking($mis->emp_id,$start_date_internal,$offline_status);

						$diffDOJCurrentMonth = '';
						$diffDOJLastMonth = '';
						$diffDOJLastToLastMonth = '';
						if($doj!='')
						{				
							$currM = date('Y-m-d');					
							$LastM = date('Y-m-31', strtotime($currM." -1 month"));
							$LasToLastM = date('Y-m-31', strtotime($currM." -2 month"));					

							$diffDOJLastMonth = strtotime($doj) - strtotime($LastM);
							
							if($diffDOJLastMonth>0 && $lastMonthSubmission==0)
							{
								$lastMonthSubmission = 'DOJ NA';							
							}
							if($diffDOJLastMonth>0 && $lastMonthBooking==0)
							{							
								$lastMonthBooking = 'DOJ NA';
							}

							$diffDOJLastToLastMonth = strtotime($doj) - strtotime($LasToLastM);
							if($diffDOJLastToLastMonth>0 && $lastToLastMonthSubmission==0)
							{
								$lastToLastMonthSubmission = 'DOJ NA';							
							}
							if($diffDOJLastToLastMonth>0 && $lastToLastMonthBooking==0)
							{							
								$lastToLastMonthBooking = 'DOJ NA';
							}
						}
			
				
				 $indexCounter++; 	
				 
				 $tlName = ucwords(strtolower($this->getTLName($mis->emp_id)));
				 $tlName = str_replace('Sahir Arsalan','Arsalan',$tlName);
				 
				
				$tlName = $tlNameDB?$tlNameDB:$tlName;
				 $Processor ='';
				if(in_array($tlName,$team_Mahwish_130))
				{
					$Processor = 'Mahwish';
				}
				if(in_array($tlName,$team_Umar_168))
				{
					$Processor = 'Umar';
				}
				if(in_array($tlName,$team_Arsalan_129))
				{
					$Processor = 'Arsalan';
				}
				

				
				if($diff>0 && $SubmissionCount==0 && $LWD!='')
				{
					$SubmissionCount = 'NA';							
				}
				if($diff>0 && $BookingCount==0 && $LWD!='')
				{							
					$BookingCount = 'NA';
				}
				
				$sheet->setCellValue('A'.$indexCounter, $tlName)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				
				$sheet->setCellValue('B'.$indexCounter, $mis->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('C'.$indexCounter, $emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('D'.$indexCounter, $SubmissionCount)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $BookingCount)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('F'.$indexCounter, $lastMonthSubmission)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $lastMonthBooking)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	

				$sheet->setCellValue('H'.$indexCounter, $lastToLastMonthSubmission)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $lastToLastMonthBooking)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				

				$sheet->setCellValue('J'.$indexCounter, ($doj?date("d-m-Y",strtotime($doj)):'NA'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('K'.$indexCounter, $this->getRangeID($vintageDays))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');			
								

				$sheet->setCellValue('L'.$indexCounter, $vintageDays)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				

				$sheet->setCellValue('M'.$indexCounter, $LWD)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				
				
				
				$sn++;
				
			}

			$emp_id_submittedValue = substr($emp_id_submitted,0,-1);


			$previousdateMissingEmp =  date('Y-m-d', strtotime($start_date_internal." -1 month"));
				$pYearMissing = date("Y",strtotime($previousdateMissingEmp));
				$pMonthMissing = date("m",strtotime($previousdateMissingEmp));
				$startDateMissing = $pYearMissing."-".$pMonthMissing."-01";
				
				$whereRawLast = "emp_id!='' and form_id='1' and submission_date >= '".$startDateMissing."' and emp_id NOT IN (".$emp_id_submittedValue.") AND emp_id!='#N/A'";

				$selectedId = DB::table('department_form_parent_entry')->whereRaw($whereRawLast.$whereRawGroup)->orderby('submission_date','DESC')->get(['team','emp_id']);				 
			
			foreach ($selectedId as $mis) {				

				$tlNameDB = $mis->team = ucwords(strtolower($mis->team));
							
				
				$whereRaw = $whereRawMain." AND emp_id='".$mis->emp_id."'";				

				$BookingCountQ = DB::table('mashreq_booking_mis')->whereRaw($whereRawBooking." AND emp_id='".$mis->emp_id."'")->get(['id'])->count();
				$BookingCount = $BookingCountQ;			
				

				$SubmissionCount = 0;			
				

			$Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	

			$emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');

			$offline_status= @$Employee_details_data->offline_status;
			$LWD = '';
			if($offline_status != '1')
			{
				$LWD = @$this->getEmpLWD($mis->emp_id);
				if($LWD!='')
				{
					$LWD = date('d-m-Y',strtotime($LWD));
					$diff = strtotime(date('Y-m-01')) - strtotime($LWD);
				}
			}
			

			$getrecruiterInfo = DepartmentFormController::getrecruiterInfo(@$Employee_details_data->recruiter);
			
			$getrecruiterCategoryInfo = DepartmentFormController::getrecruiterCategoryInfo(@$getrecruiterInfo->recruit_cat);



					$vintageDays = '-';
					$doj = '';
					$empAttr = Employee_attribute::where("emp_id",$mis->emp_id)->where("attribute_code","DOJ")->first();
					if($empAttr != '')
						{
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									$doj = date("Y-m-d",strtotime($doj));
									$vintageDays = abs(strtotime($end_date_internal)-strtotime($doj))/ (60 * 60 * 24);
								}
						}

				$lastMonthSubmission = $this->lastMonthSubmission($mis->emp_id,$start_date_internal,$offline_status);
				$lastMonthBooking = $this->lastMonthBooking($mis->emp_id,$start_date_internal,$offline_status);

				$lastToLastMonthSubmission =  $this->lastToLastMonthSubmission($mis->emp_id,$start_date_internal,$offline_status);
				$lastToLastMonthBooking = $this->lastToLastMonthBooking($mis->emp_id,$start_date_internal,$offline_status);

						$diffDOJCurrentMonth = '';
						$diffDOJLastMonth = '';
						$diffDOJLastToLastMonth = '';
						if($doj!='')
						{				
							$currM = date('Y-m-d');					
							$LastM = date('Y-m-31', strtotime($currM." -1 month"));
							$LasToLastM = date('Y-m-31', strtotime($currM." -2 month"));					

							$diffDOJLastMonth = strtotime($doj) - strtotime($LastM);
							
							if($diffDOJLastMonth>0 && $lastMonthSubmission==0)
							{
								$lastMonthSubmission = 'DOJ NA';							
							}
							if($diffDOJLastMonth>0 && $lastMonthBooking==0)
							{							
								$lastMonthBooking = 'DOJ NA';
							}

							$diffDOJLastToLastMonth = strtotime($doj) - strtotime($LasToLastM);
							if($diffDOJLastToLastMonth>0 && $lastToLastMonthSubmission==0)
							{
								$lastToLastMonthSubmission = 'DOJ NA';							
							}
							if($diffDOJLastToLastMonth>0 && $lastToLastMonthBooking==0)
							{							
								$lastToLastMonthBooking = 'DOJ NA';
							}
						}
			
				
				 $indexCounter++; 	
				 
				 $tlName = ucwords(strtolower($this->getTLName($mis->emp_id)));
				 $tlName = str_replace('Sahir Arsalan','Arsalan',$tlName);
				 //$tlName = str_replace('Mohammed','Sahir',$tlName);
				 //$tlName = str_replace('Muhammad','Sahir',$tlName);
				 $tlName = $tlNameDB?$tlNameDB:$tlName;
				 


				 $Processor ='';
				if(in_array($tlName,$team_Mahwish_130))
				{
					$Processor = 'Mahwish';
				}
				if(in_array($tlName,$team_Umar_168))
				{
					$Processor = 'Umar';
				}
				if(in_array($tlName,$team_Arsalan_129))
				{
					$Processor = 'Arsalan';
				}
				
				if($diff>0 && $SubmissionCount==0 && $LWD!='')
				{
					$SubmissionCount = 'NA';							
				}
				if($diff>0 && $BookingCount==0 && $LWD!='')
				{							
					$BookingCount = 'NA';
				}
				
				$sheet->setCellValue('A'.$indexCounter, $tlName)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				
				$sheet->setCellValue('B'.$indexCounter, $mis->emp_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('C'.$indexCounter, $emp_name)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				

				$sheet->setCellValue('D'.$indexCounter, $SubmissionCount)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $BookingCount)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('F'.$indexCounter, $lastMonthSubmission)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $lastMonthBooking)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	

				$sheet->setCellValue('H'.$indexCounter, $lastToLastMonthSubmission)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $lastToLastMonthBooking)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				

				$sheet->setCellValue('J'.$indexCounter, ($doj?date("d-m-Y",strtotime($doj)):'NA'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('K'.$indexCounter, $this->getRangeID($vintageDays))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');			
								

				$sheet->setCellValue('L'.$indexCounter, $vintageDays)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				

				$sheet->setCellValue('M'.$indexCounter, $LWD)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				
				
				$sn++;
				
			}

			

			///////////// Total ////////

				$indexCounter++; 				
				$sheet->setCellValue('A'.$indexCounter, 'Total')->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');
	
				$sheet->setCellValue('D'.$indexCounter, $SubmitCountTotal)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('right')->setVertical('top');				
				$sheet->setCellValue('E'.$indexCounter, $BookingCountTotal)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('right')->setVertical('top');

				/////////////Total ////////

				
				
			
			
			  for($col = 'A'; $col !== 'M'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:M1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter-3;$index++)
				{
					  foreach (range('A','M') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}



	}

	protected function sheet3Performance($spreadsheet,$title,$whereRawMain,$whereRawLogin,$whereRawBooking,$start_date_internal,$end_date_internal)
	{
			
			$title = str_replace('Agent','TL',$title);
			$team_Mahwish_130 = array('Ajay','AJAY','Mujahid','Akshada','Shahnawaz','Anas');
			$team_Umar_168 = array('Arsalan','Zubair','Umer');
			$team_Arsalan_129 = array('Mohsin','Sahir','Sahir Arsalan');

			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:T1');
			$sheet->setCellValue('A1', $title)->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;			
			
			$sheet->setCellValue('A'.$indexCounter, strtoupper('TL'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Processor'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('C'.$indexCounter, strtoupper('Submission'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('D'.$indexCounter, strtoupper('Booking'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('E'.$indexCounter, strtoupper('Last Month Bookings'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('F'.$indexCounter, strtoupper('Last Month Bookings (Final MTD)'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('G'.$indexCounter, strtoupper('T-1 Submission'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('H'.$indexCounter, strtoupper('T-2 Submission'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');		

			$sheet->setCellValue('I'.$indexCounter, strtoupper('5-7k'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('J'.$indexCounter, strtoupper('7-10k'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('K'.$indexCounter, strtoupper('10-15k'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('L'.$indexCounter, strtoupper('15K+'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('M'.$indexCounter, strtoupper('STP %'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('N'.$indexCounter, strtoupper('SUBMISSION TO BOOKING'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('O'.$indexCounter, strtoupper('JOURNEY TO BOOKING'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('P'.$indexCounter, strtoupper('JOURNEY TO SUBMISSION'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('Q'.$indexCounter, strtoupper('No Hit'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('R'.$indexCounter, strtoupper('Poor'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('S'.$indexCounter, strtoupper('Thin 2'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('T'.$indexCounter, strtoupper('Thin 1'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('U'.$indexCounter, strtoupper('Rich'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$whereRawGroup = " AND team!='#N/A' AND team!='0' GROUP BY team";

			
			$last_day = date('Y-m-d', strtotime('last day'));
			$last_2day = date('Y-m-d', strtotime('-2 day'));

			
			$selectedId = DB::table('department_form_parent_entry')->whereRaw($whereRawMain.$whereRawGroup)->orderby('submission_date','DESC')->get();
			$sn = 1;
			
			$SubmitCountTotal = 0;
			$BookingCountTotal = 0;
			foreach ($selectedId as $mis) {
				$Processor ='';
				if(in_array($mis->team,$team_Mahwish_130))
				{
					$Processor = 'Mahwish';
				}
				if(in_array($mis->team,$team_Umar_168))
				{
					$Processor = 'Umar';
				}
				if(in_array($mis->team,$team_Arsalan_129))
				{
					$Processor = 'Arsalan';
				}	
				

				$getTeamEmpID = DB::table('department_form_parent_entry')->whereRaw($whereRawMain." AND team='".$mis->team."' group by emp_id order by emp_id")->get(['emp_id']);			
				$emp_id_submitted = "'0',";
			
				foreach ($getTeamEmpID as $data) 
				{
					$emp_id_submitted .= "'".$data->emp_id."',";
				}
				$emp_id_submitted = substr($emp_id_submitted,0,-1);
				
				$whereRaw = $whereRawMain." AND team='".$mis->team."' AND emp_id IN (".$emp_id_submitted.")";
				

				$BookingCountQ = DB::table('mashreq_booking_mis')->whereRaw($whereRawBooking." AND team='".$mis->team."'")->get(['id'])->count();
				$BookingCountTeam = $BookingCountQ;

				$BookingCountTotal = $BookingCountTotal+$BookingCountTeam;
				
				$SubmissionCountQuery = DB::table('department_form_parent_entry')->whereRaw($whereRaw)->get(['ref_no']);
				$ref_nos = "'0',";
				foreach($SubmissionCountQuery as $SubmissionData)
				{
					$ref_nos .= "'".$SubmissionData->ref_no."',";
				}
				$ref_nos = substr($ref_nos,0,-1);

				$SubmissionCount = count($SubmissionCountQuery);

				$SubmitCountTotal = $SubmitCountTotal+$SubmissionCount;			
				 
			
				
				 $indexCounter++; 					
				
				$sheet->setCellValue('A'.$indexCounter, $mis->team)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				

				$sheet->setCellValue('B'.$indexCounter, $Processor)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('C'.$indexCounter, $SubmissionCount)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('D'.$indexCounter, $BookingCountTeam)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');			


				$sheet->setCellValue('E'.$indexCounter, $this->lastMonthBookingTeam($mis->team,serialize($emp_id_submitted),$start_date_internal))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				
				$sheet->setCellValue('F'.$indexCounter, $this->lastMonthBookingFinalMTDTeam($mis->team,serialize($emp_id_submitted),$start_date_internal))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				

				$T1Submission = DB::table('department_form_parent_entry')->whereRaw("submission_date='".$last_day."' AND emp_id IN (".$emp_id_submitted.") and form_id='1'")->get(['id'])->count();

				$sheet->setCellValue('G'.$indexCounter, @$T1Submission)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				
				$T2Submission = DB::table('department_form_parent_entry')->whereRaw("submission_date>='".$last_2day."' AND submission_date<='".$last_day."' AND emp_id IN (".$emp_id_submitted.") and form_id='1'")->get(['id'])->count();

				$sheet->setCellValue('H'.$indexCounter, @$T2Submission)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				

				$login5to7k = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id IN (".$emp_id_submitted.") and cdafinalsalary>=5000 AND cdafinalsalary<=7000 AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$login5to7k = round(($login5to7k/$SubmissionCount),2);
				
				$sheet->setCellValue('I'.$indexCounter, $login5to7k)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$login7to10k = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id IN (".$emp_id_submitted.") and cdafinalsalary>7000 AND cdafinalsalary<=10000 AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$login7to10k = round(($login7to10k/$SubmissionCount),2);

				$sheet->setCellValue('J'.$indexCounter, $login7to10k)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$login10to15k = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id IN (".$emp_id_submitted.") and cdafinalsalary>10000 AND cdafinalsalary<=15000 AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$login10to15k = round(($login10to15k/$SubmissionCount),2);

				$sheet->setCellValue('K'.$indexCounter, $login10to15k)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$login15kplus = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id IN (".$emp_id_submitted.") and cdafinalsalary>15000 AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$login15kplus = round(($login15kplus/$SubmissionCount),2);

				$sheet->setCellValue('L'.$indexCounter, $login15kplus)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$STPCount = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id IN (".$emp_id_submitted.") and application_status='STP Disbursed' AND booking_status='1' AND ref_no IN (".$ref_nos.")")->get(['id'])->count();

				$JourneyCount = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id IN (".$emp_id_submitted.") AND ref_no IN (".$ref_nos.")")->get(['id'])->count();

				$STP_Percent = 0;

				if($BookingCountTeam>0)
				{
					$STP_Percent = ($STPCount/$BookingCountTeam);
				}
				
				$J_2_B_Percent = 0;
				$J_2_S_Percent = 0;
				if($JourneyCount>0)
				{
					$J_2_B_Percent = ($BookingCountTeam/$JourneyCount);
					$J_2_S_Percent = ($SubmissionCount/$JourneyCount);
				}

				if($BookingCountTeam>0)
				{
					$STP_Percent = ($STPCount/$BookingCountTeam);
				}
				else
				{
					$STP_Percent = 0;
				}

				$sheet->setCellValue('M'.$indexCounter, number_format($STP_Percent,2))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				if($SubmissionCount>0)
				{
					$approvalRate = ($BookingCountTeam/$SubmissionCount);
				}
				else
				{
					$approvalRate = 0;
				}

				$sheet->setCellValue('N'.$indexCounter, number_format($approvalRate,2))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('O'.$indexCounter, number_format($J_2_B_Percent,2))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('P'.$indexCounter, number_format($J_2_S_Percent,2))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$loginHoHit = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id IN (".$emp_id_submitted.") and bureau_segmentation='NO HIT' AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$totalNOHITValue = round(($loginHoHit/$SubmissionCount),2);
				$sheet->setCellValue('Q'.$indexCounter, $totalNOHITValue)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				

				$loginPoor = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id IN (".$emp_id_submitted.") and bureau_segmentation='POOR' AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$totalPOORValue = round(($loginPoor/$SubmissionCount),2);
				$sheet->setCellValue('R'.$indexCounter, $totalPOORValue)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$loginThin2 = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id IN (".$emp_id_submitted.") and bureau_segmentation='THIN2' AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$totalTHIN2Value = round(($loginThin2/$SubmissionCount),2);
				$sheet->setCellValue('S'.$indexCounter, $totalTHIN2Value)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$loginThin1 = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id IN (".$emp_id_submitted.") and bureau_segmentation='THIN1' AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$totalTHIN1Value = round(($loginThin1/$SubmissionCount),2);
				$sheet->setCellValue('T'.$indexCounter, $totalTHIN1Value)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$loginRich = DB::table('mashreq_login_data')->whereRaw($whereRawLogin." AND emp_id IN (".$emp_id_submitted.") and bureau_segmentation='RICH' AND ref_no IN (".$ref_nos.")")->get(['id'])->count();
				$totalRICHValue = round(($loginRich/$SubmissionCount),2);
				$sheet->setCellValue('U'.$indexCounter, $totalRICHValue)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}

			///////////// Total ////////

				/*$indexCounter++; 				
				$sheet->setCellValue('A'.$indexCounter, 'Total')->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');
	
				$sheet->setCellValue('C'.$indexCounter, $SubmitCountTotal)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('right')->setVertical('top');				
				$sheet->setCellValue('D'.$indexCounter, $BookingCountTotal)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('right')->setVertical('top');

				/////////////Total ////////

				///////////// Bookings not claimed in Internal MIS ////////

				$indexCounter++; 
				$indexCounter++;
				$sheet->setCellValue('A'.$indexCounter, 'Bookings not claimed in Internal MIS')->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('left')->setVertical('top');

				$MSCountTotal = DB::table('mashreq_booking_mis')->whereRaw($whereRawBooking." AND emp_id IS NULL")->get(['id'])->count();
				
				$sheet->setCellValue('D'.$indexCounter, $MSCountTotal)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('right')->setVertical('top');*/

				

				///////////// Bookings not claimed in Internal MIS ////////
				
			
			
			  for($col = 'A'; $col !== 'U'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:U1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','U') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}



	}

	protected function sheet4FlagDetails($spreadsheet,$title,$whereRawMain,$whereRawLogin,$whereRawBooking,$start_date_internal,$end_date_internal)
	{
			
			$title = 'Flag Details of Last 3 Months';
			$team_Mahwish_130 = array('Ajay','AJAY','Mujahid','Akshada','Shahnawaz','Anas');
			$team_Umar_168 = array('Arsalan','Zubair','Umer');
			$team_Arsalan_129 = array('Mohsin','Sahir','Sahir Arsalan');

			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:J1');
			$sheet->setCellValue('A1', $title)->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;			
			
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Employee ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('B'.$indexCounter, strtoupper('Employee Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');


			$selectedIdPre = DB::table('master_payout_pre')->whereRaw("dept_id = '36' AND agent_product='Card'")->limit(1)->orderby('sort_order','DESC')->groupBy('sales_time')->get(['sort_order','sales_time']);
			
			$max_sort_order = $selectedIdPre[0]->sort_order;
			$max_sales_time = $selectedIdPre[0]->sales_time;

			$check_data = MasterPayout::select("id")->where("dept_id",'36')->whereRaw("sort_order='".$max_sort_order."'")->get()->count();

			if($check_data>0)
			{

				$selectedId = DB::table('master_payout')->whereRaw("dept_id = '36' AND agent_product_id='1'")->limit(3)->orderby('sort_order','DESC')->groupBy('sales_time')->get(['sales_time','sort_order','range_id']);
				
				$k=1;
				$sort_orders = '';
				foreach ($selectedId as $mis) 
				{
					if($k==1)
					{
						$col='C';
						$colRange='F';
					}
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

				//$sheet->setCellValue('F'.$indexCounter, strtoupper('Range ID'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('I'.$indexCounter, strtoupper('DOJ'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('J'.$indexCounter, strtoupper('Salary'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sort_orders = substr($sort_orders,0,-1);
			
				
				$selectedEmp = DB::table('master_payout')->whereRaw("dept_id = '36' AND agent_product_id='1' AND (employee_id!='' OR employee_id IS NOT NULL) AND employee_id NOT LIKE '%,%' AND employee_id NOT LIKE '%.%' AND sort_order IN (".$sort_orders.")")->groupBy('employee_id')->get(['employee_id','agent_name','range_id','doj']);
				$sn = 1;

				$exp_sort_orders = explode(",",$sort_orders);
				
				foreach ($selectedEmp as $selectedEmpData) 
				{
				
					
					 $indexCounter++; 					
					
					$sheet->setCellValue('A'.$indexCounter, $selectedEmpData->employee_id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
					

					$sheet->setCellValue('B'.$indexCounter, $selectedEmpData->agent_name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$FirstData = DB::table('master_payout')->whereRaw("dept_id = '36' AND sort_order ='".$exp_sort_orders[0]."' AND employee_id='".$selectedEmpData->employee_id."' AND agent_product_id='1'")->get(['cards_mashreq','flag_rule_name','agent_target','range_id']);	
					

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

						$agent_target = $FirstDataVal->agent_target;

						$sheet->setCellValue('C'.$indexCounter, $FirstDataVal->cards_mashreq)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('F'.$indexCounter, $FirstDataVal->range_id)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

						$spreadsheet->getActiveSheet()->getStyle('C'.$indexCounter.':'.'C'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
					}

					$SecondData = DB::table('master_payout')->whereRaw("dept_id = '36' AND sort_order ='".$exp_sort_orders[1]."' AND employee_id='".$selectedEmpData->employee_id."' AND agent_product_id='1'")->get(['cards_mashreq','flag_rule_name','range_id']);				

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

						$sheet->setCellValue('D'.$indexCounter, $SecondDataVal->cards_mashreq)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('G'.$indexCounter, $SecondDataVal->range_id)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

						$spreadsheet->getActiveSheet()->getStyle('D'.$indexCounter.':'.'D'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
					}

					$ThirdData = DB::table('master_payout')->whereRaw("dept_id = '36' AND sort_order ='".$exp_sort_orders[2]."' AND employee_id='".$selectedEmpData->employee_id."' AND agent_product_id='1'")->get(['cards_mashreq','flag_rule_name','range_id']);				

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

						$sheet->setCellValue('E'.$indexCounter, $ThirdDataVal->cards_mashreq)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('H'.$indexCounter, $ThirdDataVal->range_id)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

						$spreadsheet->getActiveSheet()->getStyle('E'.$indexCounter.':'.'E'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
					}

					//$sheet->setCellValue('F'.$indexCounter, $selectedEmpData->range_id)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$sheet->setCellValue('I'.$indexCounter, $selectedEmpData->doj)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$EmpSalary = $this->getEmpSalary(36,$agent_target);

					$sheet->setCellValue('J'.$indexCounter, $EmpSalary)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					

					


					
					$sn++;
					
				}
			}
			else
			{
				$sheet->setCellValue('C'.$indexCounter, $max_sales_time)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, strtoupper('Range ID -'.$max_sales_time))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');


				$selectedId = DB::table('master_payout')->whereRaw("dept_id = '36' AND agent_product_id='1'")->limit(2)->orderby('sort_order','DESC')->groupBy('sales_time')->get(['sales_time','sort_order','range_id']);
				
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

				//$sheet->setCellValue('F'.$indexCounter, strtoupper('Range ID'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('I'.$indexCounter, strtoupper('DOJ'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('J'.$indexCounter, strtoupper('Salary'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sort_orders = substr($sort_orders,0,-1);
			
				
				$selectedEmp = DB::table('master_payout')->whereRaw("dept_id = '36' AND agent_product_id='1' AND (employee_id!='' OR employee_id IS NOT NULL) AND employee_id NOT LIKE '%,%' AND employee_id NOT LIKE '%.%' AND sort_order IN (".$sort_orders.")")->groupBy('employee_id')->get(['employee_id','agent_name','range_id','doj']);
				$sn = 1;

				$exp_sort_orders = explode(",",$sort_orders);
				
				foreach ($selectedEmp as $selectedEmpData) 
				{
				
					
					 $indexCounter++; 					
					
					$sheet->setCellValue('A'.$indexCounter, $selectedEmpData->employee_id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
					

					$sheet->setCellValue('B'.$indexCounter, $selectedEmpData->agent_name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$FirstData = DB::table('master_payout_pre')->whereRaw("dept_id = '36' AND sort_order ='".$max_sort_order."' AND agent_id='".$selectedEmpData->employee_id."' AND agent_product='Card'")->get(['tc','flag_rule_name','range_id']);	
					

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

					$SecondData = DB::table('master_payout')->whereRaw("dept_id = '36' AND agent_product_id='1' AND sort_order ='".$exp_sort_orders[0]."' AND employee_id='".$selectedEmpData->employee_id."'")->get(['cards_mashreq','flag_rule_name','agent_target','range_id']);				

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

						$agent_target = $SecondDataVal->agent_target;

						$sheet->setCellValue('D'.$indexCounter, $SecondDataVal->cards_mashreq)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('G'.$indexCounter, $SecondDataVal->range_id)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

						$spreadsheet->getActiveSheet()->getStyle('D'.$indexCounter.':'.'D'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
					}

					$ThirdData = DB::table('master_payout')->whereRaw("dept_id = '36' AND agent_product_id='1' AND sort_order ='".$exp_sort_orders[1]."' AND employee_id='".$selectedEmpData->employee_id."'")->get(['cards_mashreq','flag_rule_name','range_id']);				

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

						$sheet->setCellValue('E'.$indexCounter, $ThirdDataVal->cards_mashreq)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
						$sheet->setCellValue('H'.$indexCounter, $ThirdDataVal->range_id)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

						$spreadsheet->getActiveSheet()->getStyle('E'.$indexCounter.':'.'E'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgcolor);
					}

					//$sheet->setCellValue('F'.$indexCounter, $selectedEmpData->range_id)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$sheet->setCellValue('I'.$indexCounter, $selectedEmpData->doj)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					$EmpSalary = $this->getEmpSalary(36,$agent_target);

					$sheet->setCellValue('J'.$indexCounter, $EmpSalary)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

					

					


					
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

 public function exportENBDLoanInternalMIS(Request $request)
 {
	
			 $parameters = $request->input(); 
			/*  echo "<pre>";
			 print_r($parameters);
			 exit; */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Export_ENBD_Loan_MIS_Data_'.date("d-m-Y").'_'.time().'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
					
			$indexCounter = 1;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('aecb_id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('emp'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('date of submission'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('app_id_generation_date'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('approval_date'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('disbursal_date'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('team'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('cm_full_name'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('I'.$indexCounter, strtoupper('gender'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('J'.$indexCounter, strtoupper('date_of_birth'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('K'.$indexCounter, strtoupper('nationality'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('L'.$indexCounter, strtoupper('marital_status'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('M'.$indexCounter, strtoupper('salary'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('N'.$indexCounter, strtoupper('company_name'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('O'.$indexCounter, strtoupper('sourcing'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('P'.$indexCounter, strtoupper('pre_calling'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('Q'.$indexCounter, strtoupper('application_status'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('R'.$indexCounter, strtoupper('app_id'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('S'.$indexCounter, strtoupper('fpd'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('T'.$indexCounter, strtoupper('roi'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('U'.$indexCounter, strtoupper('loan_amount'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('V'.$indexCounter, strtoupper('tenure'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('W'.$indexCounter, strtoupper('mobile'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('X'.$indexCounter, strtoupper('Co Category'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('Y'.$indexCounter, strtoupper('aecb_score'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('Z'.$indexCounter, strtoupper('scheme_name'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('AA'.$indexCounter, strtoupper('bank'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('AB'.$indexCounter, strtoupper('account_no'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('AC'.$indexCounter, strtoupper('chq#'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sheet->setCellValue('AD'.$indexCounter, strtoupper('comment'))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  ENBDLoanMIS::where("id",$sid)->first();

				$Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	

			$emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');
			
				 
				 
				 $indexCounter++; 	
				
				$sheet->setCellValue('A'.$indexCounter, $mis->aecb_id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $emp_name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, date("d-m-Y",strtotime($mis->date_of_submission)))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, date("d-m-Y",strtotime($mis->app_id_generation_date)))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, date("d-m-Y",strtotime($mis->approval_date)))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, date("d-m-Y",strtotime($mis->disbursal_date)))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('G'.$indexCounter, $mis->team)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->cm_full_name)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('I'.$indexCounter, $mis->gender)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('J'.$indexCounter, date("d-m-Y",strtotime($mis->date_of_birth)))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $mis->nationality)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $mis->marital_status)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

				$sheet->setCellValue('M'.$indexCounter, $mis->salary)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $mis->company_name)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $mis->sourcing)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $mis->pre_calling)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $mis->application_status)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $mis->app_id)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, date("d-m-Y",strtotime($mis->fpd)))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $mis->roi)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $mis->loan_amount)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $mis->tenure)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $mis->mobile)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $mis->ale)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Y'.$indexCounter, $mis->aecb_score)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $mis->scheme_name)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter, $mis->bank)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter, $mis->account_no)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AC'.$indexCounter, $mis->chq)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AD'.$indexCounter, $mis->comment)->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
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
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 }
 
 
 public function cdaDeviationUpdate(Request $request)
	{
		$loginData = MashreqLoginMIS::select("all_cda_deviation")->get();
		
		foreach($loginData as $_login)
		{
			$all_cda_deviation = $_login->all_cda_deviation;
			$cda_deviation_array = explode("|",$all_cda_deviation);
			echo "<pre>";
			print_r($cda_deviation_array);
			exit;
		}
	}

	public function getDesignation($empId)
		{
			$empDetailsModel = Employee_details::where("emp_id",$empId)->first();
			if($empDetailsModel != '')
			{
				$empdesignationId = $empDetailsModel->designation_by_doc_collection;
				if($empdesignationId != '' && $empdesignationId != NULL)
				{
					$designationMod = Designation::where("id",$empdesignationId)->first();
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

	protected function getAgentSalary($empId)
	{
		$empDetailsModel = Employee_attribute::where("emp_id",$empId)->where("attribute_code","total_gross_salary")->first();
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

	protected function getTLName($empId)
	{
		$empDetailsModel = Employee_details::where("emp_id",$empId)->first();
			if($empDetailsModel != '')
			{
				$tlID = $empDetailsModel->tl_id;
				return $tl_name = $empDetailsModel->tl_name;
				/*if($tlID != '' && $tlID != NULL)
				{
					return Employee_details::where("id",$tlID)->first()->sales_name;
				}
				else
				{
					return "-";
				}*/
			}
			else
			{
				return "-";
			}
	}

	protected function lastMonthSubmission($empId,$start_date_internal,$offline_status)
	{
		
		$previousdate =  date('Y-m-d', strtotime($start_date_internal." -1 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		
		$saleEnd = $pMonth."-".$pYear;		
		
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;

		
		
		$lastMonthSubmission = DB::table('department_form_parent_entry')->whereRaw("form_id='1' AND submission_date>='".$startDate."' AND submission_date<='".$endDate."' AND emp_id='".$empId."'")->get(['id'])->count();
				
		
		if($offline_status != '1')
		{
			$LWD = @$this->getEmpLWD($empId);
			if($LWD!='')
			{
				$LWD = date('d-m-Y',strtotime($LWD));

				$diff = strtotime($startDate) - strtotime($LWD);
				if($diff>0 && $lastMonthSubmission==0)
				{
					$lastMonthSubmission = 'NA';							
				}				
			}
		}
		return $lastMonthSubmission;
		
		
	}

	protected function lastToLastMonthSubmission($empId,$start_date_internal,$offline_status)
	{
		
		$previousdate =  date('Y-m-d', strtotime($start_date_internal." -2 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		
		$saleEnd = $pMonth."-".$pYear;		
		
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		
		$lastMonthSubmission = DB::table('department_form_parent_entry')->whereRaw("form_id='1' AND submission_date>='".$startDate."' AND submission_date<='".$endDate."' AND emp_id='".$empId."'")->get(['id'])->count();

		if($offline_status != '1')
		{
			$LWD = @$this->getEmpLWD($empId);
			if($LWD!='')
			{
				$LWD = date('d-m-Y',strtotime($LWD));

				$diff = strtotime($startDate) - strtotime($LWD);
				if($diff>0 && $lastMonthSubmission==0)
				{
					$lastMonthSubmission = 'NA';							
				}				
			}
		}
		return $lastMonthSubmission;		
		
		
	}

	protected function lastMonthBooking($empId,$start_date_internal,$offline_status)
	{
		
		$previousdate =  date('Y-m-d', strtotime($start_date_internal." -1 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		
		$saleEnd = $pMonth."-".$pYear;		
		
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		
		$LastMonthBooking = DB::table('mashreq_booking_mis')->whereRaw("dateofdisbursal>='".$startDate."' AND dateofdisbursal<='".$endDate."' AND emp_id='".$empId."'")->get(['id'])->count();

		if($offline_status != '1')
		{
			$LWD = @$this->getEmpLWD($empId);
			if($LWD!='')
			{
				$LWD = date('d-m-Y',strtotime($LWD));

				$diff = strtotime($startDate) - strtotime($LWD);
				if($diff>0 && $LastMonthBooking==0)
				{
					$LastMonthBooking = 'NA';							
				}				
			}
		}
		return $LastMonthBooking;	
		
		
	}

	protected function lastToLastMonthBooking($empId,$start_date_internal,$offline_status)
	{
		
		$previousdate =  date('Y-m-d', strtotime($start_date_internal." -2 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		
		$saleEnd = $pMonth."-".$pYear;		
		
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		
		$lastToLastMonthBooking = DB::table('mashreq_booking_mis')->whereRaw("dateofdisbursal>='".$startDate."' AND dateofdisbursal<='".$endDate."' AND emp_id='".$empId."'")->get(['id'])->count();

		if($offline_status != '1')
		{
			$LWD = @$this->getEmpLWD($empId);
			if($LWD!='')
			{
				$LWD = date('d-m-Y',strtotime($LWD));

				$diff = strtotime($startDate) - strtotime($LWD);
				if($diff>0 && $lastToLastMonthBooking==0)
				{
					$lastToLastMonthBooking = 'NA';							
				}				
			}
		}
		return $lastToLastMonthBooking;	
		
		
	}



	protected function lastMonthBooking_OLD($empId,$start_date_internal)
	{
		
		$previousdate =  date('Y-m-d', strtotime($start_date_internal." -1 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		
		$saleEnd = $pMonth."-".$pYear;
		
		/*
		*check master payout first
		*/
		/*$employeePayoutData = MasterPayout::where("dept_id",36)->where("sales_time",$saleEnd)->where("employee_id",$empId)->first();
		if($employeePayoutData != '')
		{
		
			return $employeePayoutData->cards_mashreq;
			
		}
		else
		{	*/	
			$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
			$endDate = $pYear."-".$pMonth."-".$d;
			
			$LastMonthBookingFinalMTD = DB::table('mashreq_final_mtd_mis')->whereRaw("dateofdisbursal>='".$startDate."' AND dateofdisbursal<='".$endDate."' AND emp_id='".$empId."'")->get(['id'])->count();

			if($LastMonthBookingFinalMTD>0)
			{
				return $LastMonthBookingFinalMTD;
			}
			else
			{
				/*$LastMonthBookingMTD = DB::table('mashreq_mtd_mis')->whereRaw("dateofdisbursal>='".$startDate."' AND dateofdisbursal<='".$endDate."' AND emp_id='".$empId."'")->get(['id'])->count();
				if($LastMonthBookingMTD>0)
				{
					return $LastMonthBookingMTD;
				}
				else
				{*/
					$LastMonthBooking = DB::table('mashreq_booking_mis')->whereRaw("dateofdisbursal>='".$startDate."' AND dateofdisbursal<='".$endDate."' AND emp_id='".$empId."'")->get(['id'])->count();
					return $LastMonthBooking;
				//}
			}

			
		//}
		
	}

	protected function lastMonthBookingFinalMTD($empId,$start_date_internal)
	{
		
		$previousdate =  date('Y-m-d', strtotime($start_date_internal." -1 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		
		$saleEnd = $pMonth.'-'.$pYear;		
				
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		
		$LastMonthBookingFinalMTD = DB::table('mashreq_final_mtd_mis')->whereRaw("dateofdisbursal>='".$startDate."' AND dateofdisbursal<='".$endDate."' AND emp_id='".$empId."'")->get(['id'])->count();
		return $LastMonthBookingFinalMTD;

			
		
	}

	protected function lastMonthBookingFinalMTDTeam($team,$empId,$start_date_internal)
	{
		$empId = unserialize($empId);
		$previousdate =  date('Y-m-d', strtotime($start_date_internal." -1 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		
		$saleEnd = $pMonth.'-'.$pYear;		
				
		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
		$endDate = $pYear."-".$pMonth."-".$d;
		
		//$LastMonthBookingFinalMTD = DB::table('mashreq_final_mtd_mis')->whereRaw("dateofdisbursal>='".$startDate."' AND dateofdisbursal<='".$endDate."' AND emp_id IN (".$empId.")")->get(['id'])->count();
		$LastMonthBookingFinalMTD = DB::table('mashreq_final_mtd_mis')->whereRaw("dateofdisbursal>='".$startDate."' AND dateofdisbursal<='".$endDate."' AND team='".$team."'")->get(['id'])->count();
		return $LastMonthBookingFinalMTD;

			
		
	}

	protected function lastMonthBookingTeam($team,$empId,$start_date_internal)
	{
		$empId = unserialize($empId);
		
		$previousdate =  date('Y-m-d', strtotime($start_date_internal." -1 month"));
		$pYear = date("Y",strtotime($previousdate));
		$pMonth = date("m",strtotime($previousdate));
		$startDate = $pYear."-".$pMonth."-01";
		
		
		$saleEnd = $pMonth."-".$pYear;
		
		
		/*$employeePayoutData = MasterPayout::where("dept_id",36)->where("sales_time1",$saleEnd)->whereRaw("employee_id IN (".$empId.")")->selectRaw('SUM(cards_mashreq) as cards_mashreq')->first();
		
		if($employeePayoutData != '')
		{
			
			return $employeePayoutData->cards_mashreq;
			
		}
		else
		{
		
		

			$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
			$endDate = $pYear."-".$pMonth."-".$d;

			$LastMonthBooking = DB::table('mashreq_booking_mis')->whereRaw("dateofdisbursal>='".$startDate."' AND dateofdisbursal<='".$endDate."' AND team ='".$team."'")->get(['id'])->count();
			return $LastMonthBooking;
		}*/

		$d= date('t', mktime(0, 0, 0, $pMonth, 1, $pYear)); 
			$endDate = $pYear."-".$pMonth."-".$d;

			$LastMonthBooking = DB::table('mashreq_booking_mis')->whereRaw("dateofdisbursal>='".$startDate."' AND dateofdisbursal<='".$endDate."' AND team ='".$team."'")->get(['id'])->count();
			return $LastMonthBooking;
			
			
		
	}

	protected function getRangeID($vintageDays)
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

	protected function getEmpSalary($bank_id,$target)
	{		
		
		return @SalaryStruture::where("bank_id",$bank_id)->where("target",$target)->first()->salary;
		//print_r($data);exit;
		
	}

	protected function getEmpLWD($emp_id)
	{
		return @EmpOffline::where("emp_id",$emp_id)->first()->last_working_day_resign;
	}


	
}
