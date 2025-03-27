<?php

namespace App\Http\Controllers\OnboardingAjax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use App\Models\Company\Department;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Illuminate\Support\Facades\Validator;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionAttributes;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Offerletter\OfferletterDetails;
use App\Models\Visa\visaType;
use App\Models\Visa\VisaStage;
use App\Models\Visa\Visaprocess;
use App\Models\Entry\Employee;
use App\Models\Employee\Employee_details;
use App\Models\Job\JobOpening;
use App\Models\Employee\Employee_attribute;
use UserPermissionAuth;
use App\Models\Onboarding\DocumentCollectionBackout;
use App\Models\Onboarding\VisaDetails;
use App\Models\Onboarding\EmployeeOnboardData;
use App\Models\MIS\WpCountries;
use App\Models\Employee\ExportDataLog;


class ReportingonboardController extends Controller
{
    
       public function action_handler_export_onboarding_reportingFuc(Request $request)
	   {
		  
			 
	        $filename = 'Onboarding_Final_Report'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AB1');
			$sheet->setCellValue('A1', 'Onboarding Final Report - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('EMP ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('FINAL DISCUSSION APPROVAL DATE'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('C'.$indexCounter, strtoupper('RECRUITER NAME'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('CANDIDATE NAME'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('CANDIDATE MOBILE NUMBER'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('JOB OPENING'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('LOCATION'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('DEPARTMENT'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('CURRENT VISA STATUS'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('CURRENT VISA DETAILS'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('EXPIRY DATE OF CURRENT VISA'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Country'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Skilled/ Unskilled'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Attestation'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		
			
			$sheet->setCellValue('O'.$indexCounter, strtoupper('CANDIDATE VINTAGE'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('P'.$indexCounter, strtoupper('OFFERLETTER STATUS'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('VISA STATUS'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('TRAINING STATUS'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('ONBOARD STATUS'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('PROPOSED SALARY'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('EVISA DATE'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('TOTAL GROSS SALARY'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('EXPECTED DOJ'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Expected joining Type'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('RESIGN STATUS'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('VISA TYPE'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('Notes'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('Degree Custody'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			$selectedId=DocumentCollectionDetails::select("id")->where("offer_letter_relased_status",1)->where("offer_letter_onboarding_status",1)->where("backout_status",1)->where("onboard_status",1)->get();
			foreach ($selectedId as $sid) {
				 $sid=$sid->id;
				//echo $sid;//exit;
				 $misData = DocumentCollectionDetails::where("id",$sid)->first();
				 //print_r($misData);exit;
				 $empiddata=EmployeeOnboardData::where("document_id",$sid)->first();
				 //$empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 if($empiddata!=''){
					$empid=$empiddata->emp_id; 
				 }else{
					$empid="NA";  
				 }
				 if(!empty($misData->created_at)){
				 $finalapproveldate=date("d-M-Y",strtotime(str_replace("/","-",$misData->created_at)));
				 }
				 else{
					$finalapproveldate='';
				 }
				 
				 $documentValuespdate = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",83)->first();
				 if($documentValuespdate!=''){
					 $dateofjoining=date("d-M-Y",strtotime(str_replace("/","-",$documentValuespdate->attribute_value)));
				 }
				 else{
					$dateofjoining=''; 
				 }
				 $recruiter_name=$misData->recruiter_name;
				 $rec=RecruiterDetails::where("id",$recruiter_name)->first();
				 if($rec!=''){
				 $recruiter_name=$rec->name;
				 }else{
					 $recruiter_name='';
				 }
				 $cname=$misData->emp_name;
				 $mobile=$misData->mobile_no;
				 $job=$misData->job_opening;
				 $jobOpning=JobOpening::where("id",$job)->first();
				 if($jobOpning!=''){
				 $jobname=$jobOpning->name;
				 $location=$jobOpning->location;
				 }else{
					$jobname=''; 
					$location='';
				 }
				 $department=$misData->department;
				 $departmentMod = Department::where("id",$department)->first();
				 $deptname=$departmentMod->department_name;
				 $current_visa_status=$misData->current_visa_status;
				 $current_visa_details=$misData->current_visa_details;
				 if($misData->visa_expiry_date!=''){
				 $Expirydate=date("d-M-Y",strtotime(str_replace("/","-",$misData->visa_expiry_date)));
				 }
				 else{
					 $Expirydate=''; 
				 }
				 if($misData->created_at != '')
				{
					$doj = $misData->created_at;
					$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 $ventage= $returnData;
				
				}
				else{
					$ventage="";
				}
				if($misData->ok_visa == 1){
						$pipline="NOT Generate";
				}else if($misData->ok_visa == 2){
						$pipline="Approved";
				}else if($misData->ok_visa == 3){
						$pipline="Requested";
				}else if($misData->ok_visa == 4){
						$pipline="DisApproved";
				}
				else{
					$pipline="";
				}
				if($misData->backout_status == 1){
						$backoutd="No";
					
					}else{
						$backoutd="Yes";
					}
					if($misData->offer_letter_onboarding_status == 1){
					 $offerletter="incomplete";
					} else{
					$offerletter="complete";
				    }
					if($misData->visa_process_status == 4){
					 $visaprocess="complete";
					}
					else if($misData->visa_process_status == 2){
						$visaprocess="inprogress";
					}else{	
					 $visaprocess="incomplete";
					}
					if($misData->training_process_status == 4){
					$training="complete";
					}else if($misData->training_process_status == 2){
							$training="inprogress";
					}else{
						$training="incomplete";
					}
					if($misData->onboard_status == 2){
					$onboard="complete";
					 }else{
						$onboard="incomplete";
					 }
					 $proposedsalary=$misData->proposed_salary;
					 $backout=DocumentCollectionBackout::where("document_id",$sid)->where("status",1)->first();
					 if($backout!=''){
						 $backoutData=$backout->backout_reason;
					 }
					 else{
						$backoutData=''; 
					 }
					 if($misData->evisa_start_date!=''){
					 $evisa=date("d-M-Y",strtotime(str_replace("/","-",$misData->evisa_start_date)));
					 }
					 else{
						 $evisa='';
					 }
					 
					 
					 
					 
					 
					   $molsalary = VisaDetails::where("document_collection_id",$sid)->where("attribute_code",132)->first();
					   if($molsalary !='')
					   { 
						$molsalary= "AED ".$molsalary->attribute_value;
					   }
					   else
					   {
							$molsalary='';
					   }

				 if($misData->expected_date_joining!=''){
				 $expected_date_joining=date("d-M-Y",strtotime(str_replace("/","-",$misData->expected_date_joining)));
				 }
				 else{
					 $expected_date_joining='';
				 }
				 if($misData->resign_status!=''){
					   $resignstatsu=$misData->resign_status;
					   }
					   else{
						  $resignstatsu=''; 
					   }
  
				   $visaProcess = Visaprocess::where("document_id",$misData->id)->orderBy('id','DESC')->first();
									
					if($visaProcess!=''){
						$visatypeId=$visaProcess->visa_type;
						$visaTypeData = visaType::where("id",$visatypeId)->first();
						$visatypename=$visaTypeData->title;
					}
					else{
						$visatypename='';
					}
					$cList = WpCountries::where("id",$misData->country)->first();
					if($cList!=''){
						$country=$cList->name;
					}
					else{
						$country='';
					}
				 $skill=$misData->skill;
				 $attestation=$misData->attestation;
				 $indexCounter++; 	
				 
				 $sheet->setCellValue('A'.$indexCounter, $empid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $finalapproveldate)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('C'.$indexCounter, strtoupper($recruiter_name))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($cname))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mobile)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $jobname)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $deptname)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $current_visa_status)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $current_visa_details)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $Expirydate)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $country)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $skill)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $attestation)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sheet->setCellValue('O'.$indexCounter, $ventage)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('P'.$indexCounter, $offerletter)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $visaprocess)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $training)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $onboard)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, "AED ".$proposedsalary)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $evisa)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $molsalary)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $expected_date_joining)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $misData->expected_joining_type)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('Y'.$indexCounter, $resignstatsu)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $visatypename)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter, $misData->notes)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter, $misData->degree_custody)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
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
				$spreadsheet->setActiveSheetIndex(0);
				$spreadsheet->getActiveSheet()->setTitle('OL Docs Pending');
				

				
				$spreadsheet->createSheet(); 
				$spreadsheet->setActiveSheetIndex(1);
				$spreadsheet->getActiveSheet()->setTitle('OL Released');
				$this->OLReleased($spreadsheet);
				
				
				$spreadsheet->createSheet(); 
				$spreadsheet->setActiveSheetIndex(2);
				$spreadsheet->getActiveSheet()->setTitle('VISA DOCS PEND');
				$this->visaDocsPend($spreadsheet);
				
				
				$spreadsheet->createSheet(); 
				$spreadsheet->setActiveSheetIndex(3);
				$spreadsheet->getActiveSheet()->setTitle('VISA DOCS REC');
				$this->visaDocsREC($spreadsheet);
				
				$spreadsheet->createSheet(); 
				$spreadsheet->setActiveSheetIndex(4);
				$spreadsheet->getActiveSheet()->setTitle('S1');
				$this->dataS1($spreadsheet);
				
				
				$spreadsheet->createSheet(); 
				$spreadsheet->setActiveSheetIndex(5);
				$spreadsheet->getActiveSheet()->setTitle('S2');
				$this->dataS2($spreadsheet);
				
				
				$spreadsheet->createSheet(); 
				$spreadsheet->setActiveSheetIndex(6);
				$spreadsheet->getActiveSheet()->setTitle('ONB Completed');
				$this->ONBCompleted($spreadsheet);
				
				
				$spreadsheet->createSheet(); 
				$spreadsheet->setActiveSheetIndex(7);
				$spreadsheet->getActiveSheet()->setTitle('DeadLine');
				$this->deadlineGet($spreadsheet);
				$logObj = new ExportDataLog();
				$logObj->user_id =$request->session()->get('EmployeeId');
				$logObj->download_date =date("Y-m-d");
				$logObj->tilte ="Onboard-Final Report";					
				$logObj->save();
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
	   }
	public function OLReleased($spreadsheet)
	{
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AB1');
			$sheet->setCellValue('A1', 'OL Released - from -'.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('EMP ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('FINAL DISCUSSION APPROVAL DATE'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('RECRUITER NAME'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('CANDIDATE NAME'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('CANDIDATE MOBILE NUMBER'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('JOB OPENING'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('LOCATION'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('DEPARTMENT'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('CURRENT VISA STATUS'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('CURRENT VISA DETAILS'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('EXPIRY DATE OF CURRENT VISA'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Country'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Skilled/ Unskilled'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Attestation'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		
			$sheet->setCellValue('O'.$indexCounter, strtoupper('CANDIDATE VINTAGE'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('OFFERLETTER STATUS'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('VISA STATUS'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('TRAINING STATUS'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('ONBOARD STATUS'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('PROPOSED SALARY'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('EVISA DATE'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('TOTAL GROSS SALARY'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('EXPECTED DOJ'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Expected joining Type'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('RESIGN STATUS'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('VISA TYPE'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('Notes'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('Degree Custody'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			$selectedId=DocumentCollectionDetails::select("id")->where("offer_letter_relased_status",2)->where("offer_letter_onboarding_status",1)->where("backout_status",1)->where("onboard_status",1)->get();
			foreach ($selectedId as $sid) {
				 $sid=$sid->id;
				//echo $sid;//exit;
				 $misData = DocumentCollectionDetails::where("id",$sid)->first();
				 //print_r($misData);exit;
				 $empiddata=EmployeeOnboardData::where("document_id",$sid)->first();
				 //$empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 if($empiddata!=''){
					$empid=$empiddata->emp_id; 
				 }else{
					$empid="NA";  
				 }
				 if(!empty($misData->created_at)){
				 $finalapproveldate=date("d-M-Y",strtotime(str_replace("/","-",$misData->created_at)));
				 }
				 else{
					$finalapproveldate='';
				 }
				 
				 $documentValuespdate = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",83)->first();
				 if($documentValuespdate!=''){
					 $dateofjoining=date("d-M-Y",strtotime(str_replace("/","-",$documentValuespdate->attribute_value)));
				 }
				 else{
					$dateofjoining=''; 
				 }
				 $recruiter_name=$misData->recruiter_name;
				 $rec=RecruiterDetails::where("id",$recruiter_name)->first();
				 if($rec!=''){
				 $recruiter_name=$rec->name;
				 }else{
					 $recruiter_name='';
				 }
				 $cname=$misData->emp_name;
				 $mobile=$misData->mobile_no;
				 $job=$misData->job_opening;
				 $jobOpning=JobOpening::where("id",$job)->first();
				 if($jobOpning!=''){
				 $jobname=$jobOpning->name;
				 $location=$jobOpning->location;
				 }else{
					$jobname=''; 
					$location='';
				 }
				 $department=$misData->department;
				 $departmentMod = Department::where("id",$department)->first();
				 $deptname=$departmentMod->department_name;
				 $current_visa_status=$misData->current_visa_status;
				 $current_visa_details=$misData->current_visa_details;
				 if($misData->visa_expiry_date!=''){
				 $Expirydate=date("d-M-Y",strtotime(str_replace("/","-",$misData->visa_expiry_date)));
				 }
				 else{
					 $Expirydate=''; 
				 }
				 if($misData->created_at != '')
				{
					$doj = $misData->created_at;
					$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 $ventage= $returnData;
				
				}
				else{
					$ventage="";
				}
				if($misData->ok_visa == 1){
						$pipline="NOT Generate";
				}else if($misData->ok_visa == 2){
						$pipline="Approved";
				}else if($misData->ok_visa == 3){
						$pipline="Requested";
				}else if($misData->ok_visa == 4){
						$pipline="DisApproved";
				}
				else{
					$pipline="";
				}
				if($misData->backout_status == 1){
						$backoutd="No";
					
					}else{
						$backoutd="Yes";
					}
					if($misData->offer_letter_onboarding_status == 1){
					 $offerletter="incomplete";
					} else{
					$offerletter="complete";
				    }
					if($misData->visa_process_status == 4){
					 $visaprocess="complete";
					}
					else if($misData->visa_process_status == 2){
						$visaprocess="inprogress";
					}else{	
					 $visaprocess="incomplete";
					}
					if($misData->training_process_status == 4){
					$training="complete";
					}else if($misData->training_process_status == 2){
							$training="inprogress";
					}else{
						$training="incomplete";
					}
					if($misData->onboard_status == 2){
					$onboard="complete";
					 }else{
						$onboard="incomplete";
					 }
					 $proposedsalary=$misData->proposed_salary;
					 $backout=DocumentCollectionBackout::where("document_id",$sid)->where("status",1)->first();
					 if($backout!=''){
						 $backoutData=$backout->backout_reason;
					 }
					 else{
						$backoutData=''; 
					 }
					 if($misData->evisa_start_date!=''){
					 $evisa=date("d-M-Y",strtotime(str_replace("/","-",$misData->evisa_start_date)));
					 }
					 else{
						 $evisa='';
					 }
					 
					 
					 
					 
					 
					   $molsalary = VisaDetails::where("document_collection_id",$sid)->where("attribute_code",132)->first();
					   if($molsalary !='')
					   { 
						$molsalary= "AED ".$molsalary->attribute_value;
					   }
					   else
					   {
							$molsalary='';
					   }

				 if($misData->expected_date_joining!=''){
				 $expected_date_joining=date("d-M-Y",strtotime(str_replace("/","-",$misData->expected_date_joining)));
				 }
				 else{
					 $expected_date_joining='';
				 }
				 if($misData->resign_status!=''){
					   $resignstatsu=$misData->resign_status;
					   }
					   else{
						  $resignstatsu=''; 
					   }
  
				   $visaProcess = Visaprocess::where("document_id",$misData->id)->orderBy('id','DESC')->first();
									
					if($visaProcess!=''){
						$visatypeId=$visaProcess->visa_type;
						$visaTypeData = visaType::where("id",$visatypeId)->first();
						$visatypename=$visaTypeData->title;
					}
					else{
						$visatypename='';
					}
					$cList = WpCountries::where("id",$misData->country)->first();
					if($cList!=''){
						$country=$cList->name;
					}
					else{
						$country='';
					}
				 $skill=$misData->skill;
				 $attestation=$misData->attestation;
				 
				 $indexCounter++; 	
				 
				 $sheet->setCellValue('A'.$indexCounter, $empid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $finalapproveldate)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($recruiter_name))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($cname))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mobile)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $jobname)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $deptname)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $current_visa_status)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $current_visa_details)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $Expirydate)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('L'.$indexCounter, $country)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $skill)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $attestation)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('O'.$indexCounter, $ventage)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $offerletter)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $visaprocess)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $training)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $onboard)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, "AED ".$proposedsalary)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $evisa)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $molsalary)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $expected_date_joining)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $misData->expected_joining_type)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Y'.$indexCounter, $resignstatsu)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $visatypename)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter, $misData->notes)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter, $misData->degree_custody)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
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
	}	
	
	
	public function visaDocsPend($spreadsheet)
	{
		$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AC1');
			$sheet->setCellValue('A1', 'Visa Docs Pending - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('EMP ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('FINAL DISCUSSION APPROVAL DATE'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('RECRUITER NAME'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('CANDIDATE NAME'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('CANDIDATE MOBILE NUMBER'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('JOB OPENING'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('LOCATION'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('DEPARTMENT'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('CURRENT VISA STATUS'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('CURRENT VISA DETAILS'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('EXPIRY DATE OF CURRENT VISA'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Country'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Skilled/ Unskilled'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Attestation'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		
			$sheet->setCellValue('O'.$indexCounter, strtoupper('CANDIDATE VINTAGE'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('OFFERLETTER STATUS'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('VISA STATUS'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('TRAINING STATUS'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('ONBOARD STATUS'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('PROPOSED SALARY'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('EVISA DATE'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('TOTAL GROSS SALARY'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('EXPECTED DOJ'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Expected joining Type'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('RESIGN STATUS'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('VISA TYPE'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('Notes'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('Fligh Ticket'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter, strtoupper('Degree Custody'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
			$sn = 1;
			$selectedId=DocumentCollectionDetails::select("id")->where("visa_documents_status",1)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->whereIn("visa_process_status",array(0,1))->get();
			foreach ($selectedId as $sid) {
				 $sid=$sid->id;
				//echo $sid;//exit;
				 $misData = DocumentCollectionDetails::where("id",$sid)->first();
				 //print_r($misData);exit;
				$empiddata=EmployeeOnboardData::where("document_id",$sid)->first();
				 //$empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 if($empiddata!=''){
					$empid=$empiddata->emp_id; 
				 }else{
					$empid="NA";  
				 }
				 if(!empty($misData->created_at)){
				 $finalapproveldate=date("d-M-Y",strtotime(str_replace("/","-",$misData->created_at)));
				 }
				 else{
					$finalapproveldate='';
				 }
				 
				 $documentValuespdate = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",83)->first();
				 if($documentValuespdate!=''){
					 $dateofjoining=date("d-M-Y",strtotime(str_replace("/","-",$documentValuespdate->attribute_value)));
				 }
				 else{
					$dateofjoining=''; 
				 }
				 $recruiter_name=$misData->recruiter_name;
				 $rec=RecruiterDetails::where("id",$recruiter_name)->first();
				 if($rec!=''){
				 $recruiter_name=$rec->name;
				 }else{
					 $recruiter_name='';
				 }
				 $cname=$misData->emp_name;
				 $mobile=$misData->mobile_no;
				 $job=$misData->job_opening;
				 $jobOpning=JobOpening::where("id",$job)->first();
				 if($jobOpning!=''){
				 $jobname=$jobOpning->name;
				 $location=$jobOpning->location;
				 }else{
					$jobname=''; 
					$location='';
				 }
				 $department=$misData->department;
				 $departmentMod = Department::where("id",$department)->first();
				 $deptname=$departmentMod->department_name;
				 $current_visa_status=$misData->current_visa_status;
				 $current_visa_details=$misData->current_visa_details;
				 if($misData->visa_expiry_date!=''){
				 $Expirydate=date("d-M-Y",strtotime(str_replace("/","-",$misData->visa_expiry_date)));
				 }
				 else{
					 $Expirydate=''; 
				 }
				 if($misData->created_at != '')
				{
					$doj = $misData->created_at;
					$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 $ventage= $returnData;
				
				}
				else{
					$ventage="";
				}
				if($misData->ok_visa == 1){
						$pipline="NOT Generate";
				}else if($misData->ok_visa == 2){
						$pipline="Approved";
				}else if($misData->ok_visa == 3){
						$pipline="Requested";
				}else if($misData->ok_visa == 4){
						$pipline="DisApproved";
				}
				else{
					$pipline="";
				}
				if($misData->backout_status == 1){
						$backoutd="No";
					
					}else{
						$backoutd="Yes";
					}
					if($misData->offer_letter_onboarding_status == 1){
					 $offerletter="incomplete";
					} else{
					$offerletter="complete";
				    }
					if($misData->visa_process_status == 4){
					 $visaprocess="complete";
					}
					else if($misData->visa_process_status == 2){
						$visaprocess="inprogress";
					}else{	
					 $visaprocess="incomplete";
					}
					if($misData->training_process_status == 4){
					$training="complete";
					}else if($misData->training_process_status == 2){
							$training="inprogress";
					}else{
						$training="incomplete";
					}
					if($misData->onboard_status == 2){
					$onboard="complete";
					 }else{
						$onboard="incomplete";
					 }
					 $proposedsalary=$misData->proposed_salary;
					 $backout=DocumentCollectionBackout::where("document_id",$sid)->where("status",1)->first();
					 if($backout!=''){
						 $backoutData=$backout->backout_reason;
					 }
					 else{
						$backoutData=''; 
					 }
					 if($misData->evisa_start_date!=''){
					 $evisa=date("d-M-Y",strtotime(str_replace("/","-",$misData->evisa_start_date)));
					 }
					 else{
						 $evisa='';
					 }
					 
					 
					 
					 
					 
					   $molsalary = VisaDetails::where("document_collection_id",$sid)->where("attribute_code",132)->first();
					   if($molsalary !='')
					   { 
						$molsalary= "AED ".$molsalary->attribute_value;
					   }
					   else
					   {
							$molsalary='';
					   }

				 if($misData->expected_date_joining!=''){
				 $expected_date_joining=date("d-M-Y",strtotime(str_replace("/","-",$misData->expected_date_joining)));
				 }
				 else{
					 $expected_date_joining='';
				 }
				 if($misData->resign_status!=''){
					   $resignstatsu=$misData->resign_status;
					   }
					   else{
						  $resignstatsu=''; 
					   }
  
				   $visaProcess = Visaprocess::where("document_id",$misData->id)->orderBy('id','DESC')->first();
									
					if($visaProcess!=''){
						$visatypeId=$visaProcess->visa_type;
						$visaTypeData = visaType::where("id",$visatypeId)->first();
						$visatypename=$visaTypeData->title;
					}
					else{
						$visatypename='';
					}
					$cList = WpCountries::where("id",$misData->country)->first();
					if($cList!=''){
						$country=$cList->name;
					}
					else{
						$country='';
					}
				 $skill=$misData->skill;
				 $attestation=$misData->attestation;
				 if($misData->ticket_status==1){
					$fligh= "Flight Ticket Not Received";
				 }else{
					$fligh= "Flight Ticket Received";
				 }
				 
				 $indexCounter++; 	
				 
				 $sheet->setCellValue('A'.$indexCounter, $empid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $finalapproveldate)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($recruiter_name))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($cname))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mobile)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $jobname)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $deptname)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $current_visa_status)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $current_visa_details)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $Expirydate)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('L'.$indexCounter, $country)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $skill)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $attestation)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('O'.$indexCounter, $ventage)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $offerletter)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $visaprocess)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $training)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $onboard)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, "AED ".$proposedsalary)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $evisa)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $molsalary)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $expected_date_joining)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $misData->expected_joining_type)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Y'.$indexCounter, $resignstatsu)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $visatypename)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter, $misData->notes)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter, $fligh)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AC'.$indexCounter, $misData->degree_custody)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
				for($col = 'A'; $col !== 'AC'; $col++) {
					$sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
				$spreadsheet->getActiveSheet()->getStyle('A1:AC1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AC') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
	}
	
	
	public function visaDocsREC($spreadsheet)
	{
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AC1');
			$sheet->setCellValue('A1', 'Visa Docs Received - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('EMP ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('FINAL DISCUSSION APPROVAL DATE'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('RECRUITER NAME'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('CANDIDATE NAME'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('CANDIDATE MOBILE NUMBER'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('JOB OPENING'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('LOCATION'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('DEPARTMENT'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('CURRENT VISA STATUS'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('CURRENT VISA DETAILS'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('EXPIRY DATE OF CURRENT VISA'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Country'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Skilled/ Unskilled'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Attestation'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		
			$sheet->setCellValue('O'.$indexCounter, strtoupper('CANDIDATE VINTAGE'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('OFFERLETTER STATUS'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('VISA STATUS'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('TRAINING STATUS'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('ONBOARD STATUS'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('PROPOSED SALARY'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('EVISA DATE'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('TOTAL GROSS SALARY'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('EXPECTED DOJ'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Expected joining Type'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('RESIGN STATUS'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('VISA TYPE'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('Notes'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('Fligh Ticket'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter, strtoupper('Degree Custody'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			$selectedId=DocumentCollectionDetails::select("id")->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->whereIn("visa_process_status",array(0,1))->get();
			foreach ($selectedId as $sid) {
				 $sid=$sid->id;
				//echo $sid;//exit;
				 $misData = DocumentCollectionDetails::where("id",$sid)->first();
				 //print_r($misData);exit;
				 //$empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 $empiddata=EmployeeOnboardData::where("document_id",$sid)->first();
				 //$empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 if($empiddata!=''){
					$empid=$empiddata->emp_id; 
				 }else{
					$empid="NA";  
				 }
				 
				 if(!empty($misData->created_at)){
				 $finalapproveldate=date("d-M-Y",strtotime(str_replace("/","-",$misData->created_at)));
				 }
				 else{
					$finalapproveldate='';
				 }
				 
				 $documentValuespdate = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",83)->first();
				 if($documentValuespdate!=''){
					 $dateofjoining=date("d-M-Y",strtotime(str_replace("/","-",$documentValuespdate->attribute_value)));
				 }
				 else{
					$dateofjoining=''; 
				 }
				 $recruiter_name=$misData->recruiter_name;
				 $rec=RecruiterDetails::where("id",$recruiter_name)->first();
				 if($rec!=''){
				 $recruiter_name=$rec->name;
				 }else{
					 $recruiter_name='';
				 }
				 $cname=$misData->emp_name;
				 $mobile=$misData->mobile_no;
				 $job=$misData->job_opening;
				 $jobOpning=JobOpening::where("id",$job)->first();
				 if($jobOpning!=''){
				 $jobname=$jobOpning->name;
				 $location=$jobOpning->location;
				 }else{
					$jobname=''; 
					$location='';
				 }
				 $department=$misData->department;
				 $departmentMod = Department::where("id",$department)->first();
				 $deptname=$departmentMod->department_name;
				 $current_visa_status=$misData->current_visa_status;
				 $current_visa_details=$misData->current_visa_details;
				 if($misData->visa_expiry_date!=''){
				 $Expirydate=date("d-M-Y",strtotime(str_replace("/","-",$misData->visa_expiry_date)));
				 }
				 else{
					 $Expirydate=''; 
				 }
				 if($misData->created_at != '')
				{
					$doj = $misData->created_at;
					$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 $ventage= $returnData;
				
				}
				else{
					$ventage="";
				}
				if($misData->ok_visa == 1){
						$pipline="NOT Generate";
				}else if($misData->ok_visa == 2){
						$pipline="Approved";
				}else if($misData->ok_visa == 3){
						$pipline="Requested";
				}else if($misData->ok_visa == 4){
						$pipline="DisApproved";
				}
				else{
					$pipline="";
				}
				if($misData->backout_status == 1){
						$backoutd="No";
					
					}else{
						$backoutd="Yes";
					}
					if($misData->offer_letter_onboarding_status == 1){
					 $offerletter="incomplete";
					} else{
					$offerletter="complete";
				    }
					if($misData->visa_process_status == 4){
					 $visaprocess="complete";
					}
					else if($misData->visa_process_status == 2){
						$visaprocess="inprogress";
					}else{	
					 $visaprocess="incomplete";
					}
					if($misData->training_process_status == 4){
					$training="complete";
					}else if($misData->training_process_status == 2){
							$training="inprogress";
					}else{
						$training="incomplete";
					}
					if($misData->onboard_status == 2){
					$onboard="complete";
					 }else{
						$onboard="incomplete";
					 }
					 $proposedsalary=$misData->proposed_salary;
					 $backout=DocumentCollectionBackout::where("document_id",$sid)->where("status",1)->first();
					 if($backout!=''){
						 $backoutData=$backout->backout_reason;
					 }
					 else{
						$backoutData=''; 
					 }
					 if($misData->evisa_start_date!=''){
					 $evisa=date("d-M-Y",strtotime(str_replace("/","-",$misData->evisa_start_date)));
					 }
					 else{
						 $evisa='';
					 }
					 
					 
					 
					 
					 
					   $molsalary = VisaDetails::where("document_collection_id",$sid)->where("attribute_code",132)->first();
					   if($molsalary !='')
					   { 
						$molsalary= "AED ".$molsalary->attribute_value;
					   }
					   else
					   {
							$molsalary='';
					   }

				 if($misData->expected_date_joining!=''){
				 $expected_date_joining=date("d-M-Y",strtotime(str_replace("/","-",$misData->expected_date_joining)));
				 }
				 else{
					 $expected_date_joining='';
				 }
				 if($misData->resign_status!=''){
					   $resignstatsu=$misData->resign_status;
					   }
					   else{
						  $resignstatsu=''; 
					   }
  
				   $visaProcess = Visaprocess::where("document_id",$misData->id)->orderBy('id','DESC')->first();
									
					if($visaProcess!=''){
						$visatypeId=$visaProcess->visa_type;
						$visaTypeData = visaType::where("id",$visatypeId)->first();
						$visatypename=$visaTypeData->title;
					}
					else{
						$visatypename='';
					}
					$cList = WpCountries::where("id",$misData->country)->first();
					if($cList!=''){
						$country=$cList->name;
					}
					else{
						$country='';
					}
				 $skill=$misData->skill;
				 $attestation=$misData->attestation;
				 if($misData->ticket_status==1){
					$fligh= "Flight Ticket Not Received";
				 }else{
					$fligh= "Flight Ticket Received";
				 }
				 $indexCounter++; 	
				 
				 $sheet->setCellValue('A'.$indexCounter, $empid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $finalapproveldate)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($recruiter_name))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($cname))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mobile)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $jobname)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $deptname)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $current_visa_status)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $current_visa_details)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $Expirydate)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('L'.$indexCounter, $country)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $skill)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $attestation)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('O'.$indexCounter, $ventage)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $offerletter)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $visaprocess)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $training)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $onboard)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, "AED ".$proposedsalary)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $evisa)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $molsalary)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $expected_date_joining)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $misData->expected_joining_type)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Y'.$indexCounter, $resignstatsu)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $visatypename)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter, $misData->notes)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter, $fligh)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AC'.$indexCounter, $misData->degree_custody)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
				for($col = 'A'; $col !== 'AC'; $col++) {
					$sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
				$spreadsheet->getActiveSheet()->getStyle('A1:AC1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AC') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
	}
	
	
	public function dataS1($spreadsheet)
	{
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AE1');
			$sheet->setCellValue('A1', 'Visa Step1 - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('EMP ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('FINAL DISCUSSION APPROVAL DATE'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('RECRUITER NAME'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('CANDIDATE NAME'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('CANDIDATE MOBILE NUMBER'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('JOB OPENING'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('LOCATION'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('DEPARTMENT'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('CURRENT VISA STATUS'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('CURRENT VISA DETAILS'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('EXPIRY DATE OF CURRENT VISA'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Country'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Skilled/ Unskilled'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Attestation'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		
			$sheet->setCellValue('O'.$indexCounter, strtoupper('CANDIDATE VINTAGE'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('OFFERLETTER STATUS'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('VISA STATUS'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('TRAINING STATUS'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('ONBOARD STATUS'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('PROPOSED SALARY'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('MOL DATE'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('TOTAL GROSS SALARY'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('EXPECTED DOJ'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Expected joining Type'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('RESIGN STATUS'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('VISA TYPE'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('CURRENT VISA STAGE'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('CURRENT VISA STAGE DATE'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter, strtoupper('Notes'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AD'.$indexCounter, strtoupper('Fligh Ticket'))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AE'.$indexCounter, strtoupper('Degree Custody'))->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sn = 1;
			$selectedId=DocumentCollectionDetails::select("id")->where("visa_process_status",2)->where("visa_stage_steps","Stage1")->where("backout_status",1)->get();
			foreach ($selectedId as $sid) {
				 $sid=$sid->id;
				//echo $sid;//exit;
				 $misData = DocumentCollectionDetails::where("id",$sid)->first();
				 //print_r($misData);exit;
				 //$empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 $empiddata=EmployeeOnboardData::where("document_id",$sid)->first();
				 //$empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 if($empiddata!=''){
					$empid=$empiddata->emp_id; 
				 }else{
					$empid="NA";  
				 }
				 if(!empty($misData->created_at)){
				 $finalapproveldate=date("d-M-Y",strtotime(str_replace("/","-",$misData->created_at)));
				 }
				 else{
					$finalapproveldate='';
				 }
				 
				 $documentValuespdate = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",83)->first();
				 if($documentValuespdate!=''){
					 $dateofjoining=date("d-M-Y",strtotime(str_replace("/","-",$documentValuespdate->attribute_value)));
				 }
				 else{
					$dateofjoining=''; 
				 }
				 $recruiter_name=$misData->recruiter_name;
				 $rec=RecruiterDetails::where("id",$recruiter_name)->first();
				 if($rec!=''){
				 $recruiter_name=$rec->name;
				 }else{
					 $recruiter_name='';
				 }
				 $cname=$misData->emp_name;
				 $mobile=$misData->mobile_no;
				 $job=$misData->job_opening;
				 $jobOpning=JobOpening::where("id",$job)->first();
				 if($jobOpning!=''){
				 $jobname=$jobOpning->name;
				 $location=$jobOpning->location;
				 }else{
					$jobname=''; 
					$location='';
				 }
				 $department=$misData->department;
				 $departmentMod = Department::where("id",$department)->first();
				 $deptname=$departmentMod->department_name;
				 $current_visa_status=$misData->current_visa_status;
				 $current_visa_details=$misData->current_visa_details;
				 if($misData->visa_expiry_date!=''){
				 $Expirydate=date("d-M-Y",strtotime(str_replace("/","-",$misData->visa_expiry_date)));
				 }
				 else{
					 $Expirydate=''; 
				 }
				 if($misData->created_at != '')
				{
					$doj = $misData->created_at;
					$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 $ventage= $returnData;
				
				}
				else{
					$ventage="";
				}
				if($misData->ok_visa == 1){
						$pipline="NOT Generate";
				}else if($misData->ok_visa == 2){
						$pipline="Approved";
				}else if($misData->ok_visa == 3){
						$pipline="Requested";
				}else if($misData->ok_visa == 4){
						$pipline="DisApproved";
				}
				else{
					$pipline="";
				}
				if($misData->backout_status == 1){
						$backoutd="No";
					
					}else{
						$backoutd="Yes";
					}
					if($misData->offer_letter_onboarding_status == 1){
					 $offerletter="incomplete";
					} else{
					$offerletter="complete";
				    }
					if($misData->visa_process_status == 4){
					 $visaprocess="complete";
					}
					else if($misData->visa_process_status == 2){
						$visaprocess="inprogress";
					}else{	
					 $visaprocess="incomplete";
					}
					if($misData->training_process_status == 4){
					$training="complete";
					}else if($misData->training_process_status == 2){
							$training="inprogress";
					}else{
						$training="incomplete";
					}
					if($misData->onboard_status == 2){
					$onboard="complete";
					 }else{
						$onboard="incomplete";
					 }
					 $proposedsalary=$misData->proposed_salary;
					 $backout=DocumentCollectionBackout::where("document_id",$sid)->where("status",1)->first();
					 if($backout!=''){
						 $backoutData=$backout->backout_reason;
					 }
					 else{
						$backoutData=''; 
					 }
					 if($misData->mol_date!=''){
					 $evisa=date("d-M-Y",strtotime(str_replace("/","-",$misData->mol_date)));
					 }
					 else{
						 $evisa='';
					 }
					 
					 
					 
					 
					 
					   $molsalary = VisaDetails::where("document_collection_id",$sid)->where("attribute_code",132)->first();
					   if($molsalary !='')
					   { 
						$molsalary= "AED ".$molsalary->attribute_value;
					   }
					   else
					   {
							$molsalary='';
					   }

				 if($misData->expected_date_joining!=''){
				 $expected_date_joining=date("d-M-Y",strtotime(str_replace("/","-",$misData->expected_date_joining)));
				 }
				 else{
					 $expected_date_joining='';
				 }
				 if($misData->resign_status!=''){
					   $resignstatsu=$misData->resign_status;
					   }
					   else{
						  $resignstatsu=''; 
					   }
  
				   $visaProcess = Visaprocess::where("document_id",$misData->id)->orderBy('id','DESC')->first();
									
					if($visaProcess!=''){
						$visatypeId=$visaProcess->visa_type;
						$visaTypeData = visaType::where("id",$visatypeId)->first();
						$visatypename=$visaTypeData->title;
					}
					else{
						$visatypename='';
					}
					$cList = WpCountries::where("id",$misData->country)->first();
					if($cList!=''){
						$country=$cList->name;
					}
					else{
						$country='';
					}
				 $skill=$misData->skill;
				 $attestation=$misData->attestation;
				 
				 $visaProcessstage = Visaprocess::where("document_id",$misData->id)->orderBy('id','DESC')->first();
									
					if($visaProcessstage!=''){
						$visastageId=$visaProcessstage->visa_stage;
						$visaTypeData = VisaStage::where("id",$visastageId)->first();
						$visastagename=$visaTypeData->stage_name;
						if($visaProcessstage->closing_date!=''){
						$visastagedate=date("d-M-Y",strtotime(str_replace("/","-",$visaProcessstage->closing_date)));
						}
						else{
						$visastagedate='';	
						}
					}
					else{
						$visastagename='';
						$visastagedate='';
					}
					if($misData->ticket_status==1){
					$fligh= "Flight Ticket Not Received";
				 }else{
					$fligh= "Flight Ticket Received";
				 }
				 $indexCounter++; 	
				 
				 $sheet->setCellValue('A'.$indexCounter, $empid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $finalapproveldate)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($recruiter_name))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($cname))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mobile)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $jobname)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $deptname)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $current_visa_status)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $current_visa_details)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $Expirydate)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('L'.$indexCounter, $country)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $skill)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $attestation)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('O'.$indexCounter, $ventage)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $offerletter)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $visaprocess)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $training)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $onboard)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, "AED ".$proposedsalary)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $evisa)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $molsalary)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $expected_date_joining)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $misData->expected_joining_type)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Y'.$indexCounter, $resignstatsu)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $visatypename)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter, $visastagename)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter, $visastagedate)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AC'.$indexCounter, $misData->notes)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AD'.$indexCounter, $fligh)->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AE'.$indexCounter, $misData->degree_custody)->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
				for($col = 'A'; $col !== 'AE'; $col++) {
					$sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
				$spreadsheet->getActiveSheet()->getStyle('A1:AE1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AE') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
	}
	
	
	public function dataS2($spreadsheet)
	{
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AE1');
			$sheet->setCellValue('A1', 'Visa Step2 - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('EMP ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('FINAL DISCUSSION APPROVAL DATE'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('RECRUITER NAME'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('CANDIDATE NAME'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('CANDIDATE MOBILE NUMBER'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('JOB OPENING'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('LOCATION'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('DEPARTMENT'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('CURRENT VISA STATUS'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('CURRENT VISA DETAILS'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('EXPIRY DATE OF CURRENT VISA'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Country'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Skilled/ Unskilled'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Attestation'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		
			$sheet->setCellValue('O'.$indexCounter, strtoupper('CANDIDATE VINTAGE'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('OFFERLETTER STATUS'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('VISA STATUS'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('TRAINING STATUS'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('ONBOARD STATUS'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('PROPOSED SALARY'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('EVISA DATE'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('TOTAL GROSS SALARY'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('EXPECTED DOJ'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Expected joining Type'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('RESIGN STATUS'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('VISA TYPE'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('CURRENT VISA STAGE'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('CURRENT VISA STAGE DATE'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter, strtoupper('Notes'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AD'.$indexCounter, strtoupper('Fligh Ticket'))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AE'.$indexCounter, strtoupper('Degree Custody'))->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sn = 1;
			$selectedId=DocumentCollectionDetails::select("id")->where("visa_process_status",2)->where("visa_stage_steps","Stage2")->where("backout_status",1)->get();
			foreach ($selectedId as $sid) {
				 $sid=$sid->id;
				//echo $sid;//exit;
				 $misData = DocumentCollectionDetails::where("id",$sid)->first();
				 //print_r($misData);exit;
				 //$empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 $empiddata=EmployeeOnboardData::where("document_id",$sid)->first();
				 //$empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 if($empiddata!=''){
					$empid=$empiddata->emp_id; 
				 }else{
					$empid="NA";  
				 }
				 if(!empty($misData->created_at)){
				 $finalapproveldate=date("d-M-Y",strtotime(str_replace("/","-",$misData->created_at)));
				 }
				 else{
					$finalapproveldate='';
				 }
				 
				 $documentValuespdate = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",83)->first();
				 if($documentValuespdate!=''){
					 $dateofjoining=date("d-M-Y",strtotime(str_replace("/","-",$documentValuespdate->attribute_value)));
				 }
				 else{
					$dateofjoining=''; 
				 }
				 $recruiter_name=$misData->recruiter_name;
				 $rec=RecruiterDetails::where("id",$recruiter_name)->first();
				 if($rec!=''){
				 $recruiter_name=$rec->name;
				 }else{
					 $recruiter_name='';
				 }
				 $cname=$misData->emp_name;
				 $mobile=$misData->mobile_no;
				 $job=$misData->job_opening;
				 $jobOpning=JobOpening::where("id",$job)->first();
				 if($jobOpning!=''){
				 $jobname=$jobOpning->name;
				 $location=$jobOpning->location;
				 }else{
					$jobname=''; 
					$location='';
				 }
				 $department=$misData->department;
				 $departmentMod = Department::where("id",$department)->first();
				 $deptname=$departmentMod->department_name;
				 $current_visa_status=$misData->current_visa_status;
				 $current_visa_details=$misData->current_visa_details;
				 if($misData->visa_expiry_date!=''){
				 $Expirydate=date("d-M-Y",strtotime(str_replace("/","-",$misData->visa_expiry_date)));
				 }
				 else{
					 $Expirydate=''; 
				 }
				 if($misData->created_at != '')
				{
					$doj = $misData->created_at;
					$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 $ventage= $returnData;
				
				}
				else{
					$ventage="";
				}
				if($misData->ok_visa == 1){
						$pipline="NOT Generate";
				}else if($misData->ok_visa == 2){
						$pipline="Approved";
				}else if($misData->ok_visa == 3){
						$pipline="Requested";
				}else if($misData->ok_visa == 4){
						$pipline="DisApproved";
				}
				else{
					$pipline="";
				}
				if($misData->backout_status == 1){
						$backoutd="No";
					
					}else{
						$backoutd="Yes";
					}
					if($misData->offer_letter_onboarding_status == 1){
					 $offerletter="incomplete";
					} else{
					$offerletter="complete";
				    }
					if($misData->visa_process_status == 4){
					 $visaprocess="complete";
					}
					else if($misData->visa_process_status == 2){
						$visaprocess="inprogress";
					}else{	
					 $visaprocess="incomplete";
					}
					if($misData->training_process_status == 4){
					$training="complete";
					}else if($misData->training_process_status == 2){
							$training="inprogress";
					}else{
						$training="incomplete";
					}
					if($misData->onboard_status == 2){
					$onboard="complete";
					 }else{
						$onboard="incomplete";
					 }
					 $proposedsalary=$misData->proposed_salary;
					 $backout=DocumentCollectionBackout::where("document_id",$sid)->where("status",1)->first();
					 if($backout!=''){
						 $backoutData=$backout->backout_reason;
					 }
					 else{
						$backoutData=''; 
					 }
					 if($misData->evisa_start_date!=''){
					 $evisa=date("d-M-Y",strtotime(str_replace("/","-",$misData->evisa_start_date)));
					 }
					 else{
						 $evisa='';
					 }
					 
					 
					 
					 
					 
					   $molsalary = VisaDetails::where("document_collection_id",$sid)->where("attribute_code",132)->first();
					   if($molsalary !='')
					   { 
						$molsalary= "AED ".$molsalary->attribute_value;
					   }
					   else
					   {
							$molsalary='';
					   }

				 if($misData->expected_date_joining!=''){
				 $expected_date_joining=date("d-M-Y",strtotime(str_replace("/","-",$misData->expected_date_joining)));
				 }
				 else{
					 $expected_date_joining='';
				 }
				 if($misData->resign_status!=''){
					   $resignstatsu=$misData->resign_status;
					   }
					   else{
						  $resignstatsu=''; 
					   }
  
				   $visaProcess = Visaprocess::where("document_id",$misData->id)->orderBy('id','DESC')->first();
									
					if($visaProcess!=''){
						$visatypeId=$visaProcess->visa_type;
						$visaTypeData = visaType::where("id",$visatypeId)->first();
						$visatypename=$visaTypeData->title;
					}
					else{
						$visatypename='';
					}
					$cList = WpCountries::where("id",$misData->country)->first();
					if($cList!=''){
						$country=$cList->name;
					}
					else{
						$country='';
					}
				 $skill=$misData->skill;
				 $attestation=$misData->attestation;
				 $visaProcessstage = Visaprocess::where("document_id",$misData->id)->orderBy('id','DESC')->first();
									
					if($visaProcessstage!=''){
						$visastageId=$visaProcessstage->visa_stage;
						$visaTypeData = VisaStage::where("id",$visastageId)->first();
						$visastagename=$visaTypeData->stage_name;
						if($visaProcessstage->closing_date!=''){
						$visastagedate=date("d-M-Y",strtotime(str_replace("/","-",$visaProcessstage->closing_date)));
						}
						else{
						$visastagedate='';	
						}
					}
					else{
						$visastagename='';
						$visastagedate='';
					}
					if($misData->ticket_status==1){
					$fligh= "Flight Ticket Not Received";
				 }else{
					$fligh= "Flight Ticket Received";
				 }
				 $indexCounter++; 	
				 
				 $sheet->setCellValue('A'.$indexCounter, $empid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $finalapproveldate)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($recruiter_name))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($cname))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mobile)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $jobname)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $deptname)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $current_visa_status)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $current_visa_details)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $Expirydate)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('L'.$indexCounter, $country)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $skill)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $attestation)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('O'.$indexCounter, $ventage)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $offerletter)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $visaprocess)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $training)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $onboard)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, "AED ".$proposedsalary)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $evisa)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $molsalary)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $expected_date_joining)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $misData->expected_joining_type)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Y'.$indexCounter, $resignstatsu)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $visatypename)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter, $visastagename)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter, $visastagedate)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AC'.$indexCounter, $misData->notes)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AD'.$indexCounter, $fligh)->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AE'.$indexCounter, $misData->degree_custody)->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
				for($col = 'A'; $col !== 'AE'; $col++) {
					$sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
				$spreadsheet->getActiveSheet()->getStyle('A1:AE1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AE') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
	}
	
	public function ONBCompleted($spreadsheet)
	{
		$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AC1');
			$sheet->setCellValue('A1', 'Onboarded - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('EMP ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('FINAL DISCUSSION APPROVAL DATE'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('RECRUITER NAME'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('CANDIDATE NAME'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('CANDIDATE MOBILE NUMBER'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('JOB OPENING'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('LOCATION'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('DEPARTMENT'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('CURRENT VISA STATUS'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('CURRENT VISA DETAILS'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('EXPIRY DATE OF CURRENT VISA'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Country'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Skilled/ Unskilled'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Attestation'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		
			$sheet->setCellValue('O'.$indexCounter, strtoupper('CANDIDATE VINTAGE'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('OFFERLETTER STATUS'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('VISA STATUS'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('TRAINING STATUS'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('ONBOARD STATUS'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('PROPOSED SALARY'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('EVISA DATE'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('TOTAL GROSS SALARY'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('EXPECTED DOJ'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Expected joining Type'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('RESIGN STATUS'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('VISA TYPE'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('Notes'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('Fligh Ticket'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter, strtoupper('Degree Custody'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sn = 1;
			$selectedId=DocumentCollectionDetails::select("id")->where("onboard_status",2)->get();
			foreach ($selectedId as $sid) {
				 $sid=$sid->id;
				//echo $sid;//exit;
				 $misData = DocumentCollectionDetails::where("id",$sid)->first();
				 //print_r($misData);exit;
				 //$empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 //$data=EmployeeOnboardData::where("document_id",$sid)->first();
				 $empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 if($empiddata!=''){
					$empid=$empiddata->emp_id; 
				 }else{
					$empid="NA";  
				 }
				 if(!empty($misData->created_at)){
				 $finalapproveldate=date("d-M-Y",strtotime(str_replace("/","-",$misData->created_at)));
				 }
				 else{
					$finalapproveldate='';
				 }
				 
				 $documentValuespdate = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",83)->first();
				 if($documentValuespdate!=''){
					 $dateofjoining=date("d-M-Y",strtotime(str_replace("/","-",$documentValuespdate->attribute_value)));
				 }
				 else{
					$dateofjoining=''; 
				 }
				 $recruiter_name=$misData->recruiter_name;
				 $rec=RecruiterDetails::where("id",$recruiter_name)->first();
				 if($rec!=''){
				 $recruiter_name=$rec->name;
				 }else{
					 $recruiter_name='';
				 }
				 $cname=$misData->emp_name;
				 $mobile=$misData->mobile_no;
				 $job=$misData->job_opening;
				 $jobOpning=JobOpening::where("id",$job)->first();
				 if($jobOpning!=''){
				 $jobname=$jobOpning->name;
				 $location=$jobOpning->location;
				 }else{
					$jobname=''; 
					$location='';
				 }
				 $department=$misData->department;
				 $departmentMod = Department::where("id",$department)->first();
				 $deptname=$departmentMod->department_name;
				 $current_visa_status=$misData->current_visa_status;
				 $current_visa_details=$misData->current_visa_details;
				 if($misData->visa_expiry_date!=''){
				 $Expirydate=date("d-M-Y",strtotime(str_replace("/","-",$misData->visa_expiry_date)));
				 }
				 else{
					 $Expirydate=''; 
				 }
				 if($misData->created_at != '')
				{
					$doj = $misData->created_at;
					$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 $ventage= $returnData;
				
				}
				else{
					$ventage="";
				}
				if($misData->ok_visa == 1){
						$pipline="NOT Generate";
				}else if($misData->ok_visa == 2){
						$pipline="Approved";
				}else if($misData->ok_visa == 3){
						$pipline="Requested";
				}else if($misData->ok_visa == 4){
						$pipline="DisApproved";
				}
				else{
					$pipline="";
				}
				if($misData->backout_status == 1){
						$backoutd="No";
					
					}else{
						$backoutd="Yes";
					}
					if($misData->offer_letter_onboarding_status == 1){
					 $offerletter="incomplete";
					} else{
					$offerletter="complete";
				    }
					if($misData->visa_process_status == 4){
					 $visaprocess="complete";
					}
					else if($misData->visa_process_status == 2){
						$visaprocess="inprogress";
					}else{	
					 $visaprocess="incomplete";
					}
					if($misData->training_process_status == 4){
					$training="complete";
					}else if($misData->training_process_status == 2){
							$training="inprogress";
					}else{
						$training="incomplete";
					}
					if($misData->onboard_status == 2){
					$onboard="complete";
					 }else{
						$onboard="incomplete";
					 }
					 $proposedsalary=$misData->proposed_salary;
					 $backout=DocumentCollectionBackout::where("document_id",$sid)->where("status",1)->first();
					 if($backout!=''){
						 $backoutData=$backout->backout_reason;
					 }
					 else{
						$backoutData=''; 
					 }
					 if($misData->evisa_start_date!=''){
					 $evisa=date("d-M-Y",strtotime(str_replace("/","-",$misData->evisa_start_date)));
					 }
					 else{
						 $evisa='';
					 }
					 
					 
					 
					 
					 
					   $molsalary = VisaDetails::where("document_collection_id",$sid)->where("attribute_code",132)->first();
					   if($molsalary !='')
					   { 
						$molsalary= "AED ".$molsalary->attribute_value;
					   }
					   else
					   {
							$molsalary='';
					   }

				 if($misData->expected_date_joining!=''){
				 $expected_date_joining=date("d-M-Y",strtotime(str_replace("/","-",$misData->expected_date_joining)));
				 }
				 else{
					 $expected_date_joining='';
				 }
				 if($misData->resign_status!=''){
					   $resignstatsu=$misData->resign_status;
					   }
					   else{
						  $resignstatsu=''; 
					   }
  
				   $visaProcess = Visaprocess::where("document_id",$misData->id)->orderBy('id','DESC')->first();
									
					if($visaProcess!=''){
						$visatypeId=$visaProcess->visa_type;
						$visaTypeData = visaType::where("id",$visatypeId)->first();
						$visatypename=$visaTypeData->title;
					}
					else{
						$visatypename='';
					}
					$cList = WpCountries::where("id",$misData->country)->first();
					if($cList!=''){
						$country=$cList->name;
					}
					else{
						$country='';
					}
				 $skill=$misData->skill;
				 $attestation=$misData->attestation;
				 if($misData->ticket_status==1){
					$fligh= "Flight Ticket Not Received";
				 }else{
					$fligh= "Flight Ticket Received";
				 }
				 $indexCounter++; 	
				 
				 $sheet->setCellValue('A'.$indexCounter, $empid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $finalapproveldate)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($recruiter_name))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($cname))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mobile)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $jobname)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $deptname)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $current_visa_status)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $current_visa_details)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $Expirydate)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sheet->setCellValue('L'.$indexCounter, $country)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $skill)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $attestation)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('O'.$indexCounter, $ventage)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $offerletter)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $visaprocess)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $training)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $onboard)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, "AED ".$proposedsalary)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $evisa)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $molsalary)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $expected_date_joining)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $misData->expected_joining_type)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Y'.$indexCounter, $resignstatsu)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $visatypename)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter, $misData->notes)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter, $fligh)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AC'.$indexCounter, $misData->degree_custody)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
				for($col = 'A'; $col !== 'AC'; $col++) {
					$sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
				$spreadsheet->getActiveSheet()->getStyle('A1:AC1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AC') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
	}
	
	public function deadlineGet($spreadsheet)
	{
		$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AI1');
			$sheet->setCellValue('A1', 'Deadline - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('EMP ID'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('FINAL DISCUSSION APPROVAL DATE'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('RECRUITER NAME'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('CANDIDATE NAME'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('CANDIDATE MOBILE NUMBER'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('JOB OPENING'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('LOCATION'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('DEPARTMENT'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('CURRENT VISA STATUS'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('CURRENT VISA DETAILS'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('EXPIRY DATE OF CURRENT VISA'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Country'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Skilled/ Unskilled'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Attestation'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
		
			$sheet->setCellValue('O'.$indexCounter, strtoupper('CANDIDATE VINTAGE'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('OFFERLETTER STATUS'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('VISA STATUS'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('TRAINING STATUS'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('ONBOARD STATUS'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('PROPOSED SALARY'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('EVISA DATE'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('TOTAL GROSS SALARY'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('EXPECTED DOJ'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Expected joining Type'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('RESIGN STATUS'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('VISA TYPE'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('Change Status'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('Stamping Deadline'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter, strtoupper('Date of Entry'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AD'.$indexCounter, strtoupper('Reminder'))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AE'.$indexCounter, strtoupper('CURRENT VISA STAGE'))->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AF'.$indexCounter, strtoupper('CURRENT VISA STAGE DATE'))->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AG'.$indexCounter, strtoupper('Notes'))->getStyle('AG'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AH'.$indexCounter, strtoupper('Fligh Ticket'))->getStyle('AH'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AI'.$indexCounter, strtoupper('Degree Custody'))->getStyle('AI'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$sn = 1;
			$selectedId=DocumentCollectionDetails::where("visa_process_status",2)->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->get();
			foreach ($selectedId as $sid) {
				 $sid=$sid->id;
				//echo $sid;//exit;
				 $misData = DocumentCollectionDetails::where("id",$sid)->first();
				 //print_r($misData);exit;
				 //$empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 //$data=EmployeeOnboardData::where("document_id",$sid)->first();
				 $empiddata=Employee_details::where("document_collection_id",$sid)->first();
				 if($empiddata!=''){
					$empid=$empiddata->emp_id; 
				 }else{
					$empid="NA";  
				 }
				 if(!empty($misData->created_at)){
				 $finalapproveldate=date("d-M-Y",strtotime(str_replace("/","-",$misData->created_at)));
				 }
				 else{
					$finalapproveldate='';
				 }
				 
				 $documentValuespdate = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",83)->first();
				 if($documentValuespdate!=''){
					 $dateofjoining=date("d-M-Y",strtotime(str_replace("/","-",$documentValuespdate->attribute_value)));
				 }
				 else{
					$dateofjoining=''; 
				 }
				 $recruiter_name=$misData->recruiter_name;
				 $rec=RecruiterDetails::where("id",$recruiter_name)->first();
				 if($rec!=''){
				 $recruiter_name=$rec->name;
				 }else{
					 $recruiter_name='';
				 }
				 $cname=$misData->emp_name;
				 $mobile=$misData->mobile_no;
				 $job=$misData->job_opening;
				 $jobOpning=JobOpening::where("id",$job)->first();
				 if($jobOpning!=''){
				 $jobname=$jobOpning->name;
				 $location=$jobOpning->location;
				 }else{
					$jobname=''; 
					$location='';
				 }
				 $department=$misData->department;
				 $departmentMod = Department::where("id",$department)->first();
				 $deptname=$departmentMod->department_name;
				 $current_visa_status=$misData->current_visa_status;
				 $current_visa_details=$misData->current_visa_details;
				 if($misData->visa_expiry_date!=''){
				 $Expirydate=date("d-M-Y",strtotime(str_replace("/","-",$misData->visa_expiry_date)));
				 }
				 else{
					 $Expirydate=''; 
				 }
				 if($misData->created_at != '')
				{
					$doj = $misData->created_at;
					$doj = str_replace("/","-",$doj);
						$date1 = date("Y-m-d",strtotime($doj));

						$date2 =  date("Y-m-d");

						$diff = abs(strtotime($date2)-strtotime($date1));

						$years = floor($diff / (365*60*60*24));

						$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));

						$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
						$returnData = '';
						if($years != 0)
						{
						$returnData .=  $years." Years, ";
						}
						if($months != 0)
						{
						$returnData .=  $months." months, ";
						}
						 $returnData .= $days." days.";
						 $ventage= $returnData;
				
				}
				else{
					$ventage="";
				}
				if($misData->ok_visa == 1){
						$pipline="NOT Generate";
				}else if($misData->ok_visa == 2){
						$pipline="Approved";
				}else if($misData->ok_visa == 3){
						$pipline="Requested";
				}else if($misData->ok_visa == 4){
						$pipline="DisApproved";
				}
				else{
					$pipline="";
				}
				if($misData->backout_status == 1){
						$backoutd="No";
					
					}else{
						$backoutd="Yes";
					}
					if($misData->offer_letter_onboarding_status == 1){
					 $offerletter="incomplete";
					} else{
					$offerletter="complete";
				    }
					if($misData->visa_process_status == 4){
					 $visaprocess="complete";
					}
					else if($misData->visa_process_status == 2){
						$visaprocess="inprogress";
					}else{	
					 $visaprocess="incomplete";
					}
					if($misData->training_process_status == 4){
					$training="complete";
					}else if($misData->training_process_status == 2){
							$training="inprogress";
					}else{
						$training="incomplete";
					}
					if($misData->onboard_status == 2){
					$onboard="complete";
					 }else{
						$onboard="incomplete";
					 }
					 $proposedsalary=$misData->proposed_salary;
					 $backout=DocumentCollectionBackout::where("document_id",$sid)->where("status",1)->first();
					 if($backout!=''){
						 $backoutData=$backout->backout_reason;
					 }
					 else{
						$backoutData=''; 
					 }
					 if($misData->evisa_start_date!=''){
					 $evisa=date("d-M-Y",strtotime(str_replace("/","-",$misData->evisa_start_date)));
					 }
					 else{
						 $evisa='';
					 }
					 
					 
					 
					 
					 
					   $molsalary = VisaDetails::where("document_collection_id",$sid)->where("attribute_code",132)->first();
					   if($molsalary !='')
					   { 
						$molsalary= "AED ".$molsalary->attribute_value;
					   }
					   else
					   {
							$molsalary='';
					   }

				 if($misData->expected_date_joining!=''){
				 $expected_date_joining=date("d-M-Y",strtotime(str_replace("/","-",$misData->expected_date_joining)));
				 }
				 else{
					 $expected_date_joining='';
				 }
				 if($misData->resign_status!=''){
					   $resignstatsu=$misData->resign_status;
					   }
					   else{
						  $resignstatsu=''; 
					   }
  
				   $visaProcess = Visaprocess::where("document_id",$misData->id)->orderBy('id','DESC')->first();
									
					if($visaProcess!=''){
						$visatypeId=$visaProcess->visa_type;
						$visaTypeData = visaType::where("id",$visatypeId)->first();
						$visatypename=$visaTypeData->title;
					}
					else{
						$visatypename='';
					}
					$cList = WpCountries::where("id",$misData->country)->first();
					if($cList!=''){
						$country=$cList->name;
					}
					else{
						$country='';
					}
				 $skill=$misData->skill;
				 $attestation=$misData->attestation;
				 $visaProcessstage = Visaprocess::where("document_id",$misData->id)->orderBy('id','DESC')->first();
									
					if($visaProcessstage!=''){
						$visastageId=$visaProcessstage->visa_stage;
						$visaTypeData = VisaStage::where("id",$visastageId)->first();
						$visastagename=$visaTypeData->stage_name;
						if($visaProcessstage->closing_date!=''){
						$visastagedate=date("d-M-Y",strtotime(str_replace("/","-",$visaProcessstage->closing_date)));
						}
						else{
						$visastagedate='';	
						}
					}
					else{
						$visastagename='';
						$visastagedate='';
					}
				$changestatusData = DocumentCollectionDetailsValues::where("document_collection_id",$sid)->where("attribute_code",66)->first(); 
				if($changestatusData != '')
				   {
						$changestatus= date("d M Y",strtotime($changestatusData->attribute_value)) ;
				   }
				   else
				   {
						$changestatus= "--";
				   }
				if($misData->stamping_deadline != '')
				   {
						$Stampingdeadline= date("d M Y",strtotime($misData->stamping_deadline)) ;
				   }
				   else
				   {
						$Stampingdeadline= "--";
				   }
				   
				   if($misData->entry_date != '')
					   {
						   
							$dateofentry=date("d M Y",strtotime($misData->entry_date)) ;
					   }
					   else
					   {
							$dateofentry= "--";
					   }
					   
					
				  
					if($misData->stamping_deadline != '')
					   {
						   $date = $misData->stamping_deadline;
							$newdate = strtotime ( '-10 days' , strtotime ( $date ) ) ;
							$newdate = date ( 'd M Y' , $newdate );
							$Reminder= $newdate ;
					   }
					   else
					   {
							$Reminder=  "--";
					   }
					   if($misData->ticket_status==1){
						$fligh= "Flight Ticket Not Received";
					 }else{
						$fligh= "Flight Ticket Received";
					 }
				 $indexCounter++; 	
				 
				 $sheet->setCellValue('A'.$indexCounter, $empid)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $finalapproveldate)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($recruiter_name))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($cname))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mobile)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $jobname)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $location)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $deptname)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $current_visa_status)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $current_visa_details)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $Expirydate)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sheet->setCellValue('L'.$indexCounter, $country)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $skill)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $attestation)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('O'.$indexCounter, $ventage)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $offerletter)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $visaprocess)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $training)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $onboard)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, "AED ".$proposedsalary)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $evisa)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $molsalary)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('W'.$indexCounter, $expected_date_joining)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $misData->expected_joining_type)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Y'.$indexCounter, $resignstatsu)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $visatypename)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter, $changestatus)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter, $Stampingdeadline)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AC'.$indexCounter, $dateofentry)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AD'.$indexCounter, $Reminder)->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AE'.$indexCounter, $visastagename)->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AF'.$indexCounter, $visastagedate)->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AG'.$indexCounter, $misData->notes)->getStyle('AG'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AH'.$indexCounter, $fligh)->getStyle('AH'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AI'.$indexCounter, $misData->degree_custody)->getStyle('AI'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
				for($col = 'A'; $col !== 'AI'; $col++) {
					$sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
				$spreadsheet->getActiveSheet()->getStyle('A1:AI1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AI') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
	}
	
}
