<?php

namespace App\Http\Controllers\SIF;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
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

use App\Models\Entry\Employee;

use App\Models\Attribute\Attributes;
use App\Models\SIF\SifTemplateDetails;
use App\Models\SIF\RandomPadddingSif;
use App\Models\EmpOffline\EmpOffline;
use App\Models\Recruiter\Designation;

use App\Models\WarningLetter\WarningLetterRequest;


class SifTemplateController extends Controller
{
  
			
			private $fpdf;
			public function sifTemplate(Request $request)
			{
				$employeeAttributesDetails = Employee_attribute::select("attribute_values")->distinct()->where("attribute_code","company_name_issue_issued")->get();
			    $departmentDetails = Department::where("status",1)->get();
				return view("SIF/sifTemplate",compact('employeeAttributesDetails','departmentDetails'));
			}
			
			public function listingEmployeeSIF(Request $request)
			{
				$selectedCompany = $request->selectedCompany;
				$departmentId = $request->departmentId;
				if($departmentId == 'all')
				{
				$employeeLists = Employee_attribute::where("attribute_code","company_name_issue_issued")->where("attribute_values",$selectedCompany)->get();
				}
				else
				{
					$employeeLists = Employee_attribute::where("attribute_code","company_name_issue_issued")->where("attribute_values",$selectedCompany)->where("dept_id",$departmentId)->get();
				}
				$employeeIdArray= array();
				foreach($employeeLists as $_empL)
				{
					$employeeIdArray[$_empL->emp_id] = $_empL->emp_id;
				}
				
				
				return view("SIF/listingEmployeeSIF",compact('employeeIdArray'));
			}
			
			public static function getEmpId($empPadding)
			{
				return Employee_details::where("emp_id",$empPadding)->first()->id;
			}
			public static function getEmpName($empPadding)
			{
				$empDat =  Employee_details::where("emp_id",$empPadding)->first();
				return $empDat->first_name.' '.$empDat->middle_name.' '.$empDat->last_name;
			}
			public static function getpermit($empPadding)
			{
				$data =  Employee_attribute::where("attribute_code","LC_Number")->where("emp_id",$empPadding)->first();
				if($data != '')
				{
					return $data->attribute_values;
				}
				else
				{
					return '';
				}	
				
			}
			public static function getpersoncode($empPadding)
			{
				  $data =  Employee_attribute::where("attribute_code","person_code")->where("emp_id",$empPadding)->first();
				  if($data != '')
					{
						return $data->attribute_values;
					}
					else
					{
						return '';
					}	
			}
			public static function getBank($empPadding)
			{
				  $data =  Employee_attribute::where("attribute_code","EBN")->where("emp_id",$empPadding)->first();
				  if($data != '')
					{
						return $data->attribute_values;
					}
					else
					{
						return '';
					}	
			}
			public static function getIBAN($empPadding)
			{
				  $data =  Employee_attribute::where("attribute_code","EMP_IBAN")->where("emp_id",$empPadding)->first();
				  if($data != '')
					{
						return $data->attribute_values;
					}
					else
					{
						return '';	
					}	
			}
			public static function getActualSalary($empPadding)
			{
				  $empDat =  Employee_details::where("emp_id",$empPadding)->first();
				  return $empDat->actual_salary;
			}
			
			
			public function selectedEmployee(Request $request)
			{
				$parameters = $request->input();
				
				$selectedEmp = $parameters['selectedEmp'];
				$empArr = explode(",",$selectedEmp);
				$employeeIdArray= array();
				foreach($empArr as $_empL)
				{
					$empId = Employee_details::where("id",$_empL)->first()->emp_id;
					$employeeIdArray[$empId] = $empId;
				}
				return view("SIF/selectedEmployee",compact('employeeIdArray'));
			}
			
			public function saveSIFTemplate(Request $request)
			{
				$parameters = $request->input();
				$paddding = RandomPadddingSif::where("id",1)->first()->padding;
				$totalData = $parameters['singlecheck'];
				foreach($totalData as $sid)
				{
					$sifObj = new SifTemplateDetails();
					$sifObj->sid_value = $sid;
					$sifObj->template_name = $parameters['sif_template_name'];
					$sifObj->template_id = $paddding;
					$sifObj->name = $parameters['name'][$sid];
					$sifObj->permit = $parameters['permit'][$sid];
					$sifObj->person_code = $parameters['personcode'][$sid];
					$sifObj->bank_name =$parameters['bankname'][$sid];
					$sifObj->iban = $parameters['iban'][$sid];
					$sifObj->days_absent = $parameters['days_absent'][$sid];
					$sifObj->fixed = $parameters['fixed'][$sid];
					$sifObj->variable = $parameters['variable'][$sid];
					$sifObj->total = $parameters['total'][$sid];
					$sifObj->fixed_count = $parameters['fixed_count'];
					$sifObj->variable_count = $parameters['variable_count'];
					$sifObj->total_count = $parameters['main_count'];
					$sifObj->created_by = $request->session()->get('EmployeeId');
					$sifObj->company_name = $parameters['company_name'];
					$sifObj->payroll_month = $parameters['payroll_month'];
					$sifObj->save();
				
				}
				$paddingUpdate = RandomPadddingSif::find(1);
				$paddingUpdate->padding = $paddding+1;
				$paddingUpdate->save();
				echo $paddding;
				exit;
			}
			protected function pageHeader($x,$sif)
			{
				$this->fpdf->SetFont('Arial','',9);
				$l = strlen($sif->company_name);
				$xI = $l+20;
				$this->fpdf->Text($x+55,10,'COMPANY NAME: ');
				$this->fpdf->Text($x+83,10,$sif->company_name);
				
				//$this->fpdf->line($x+20,11.5,$x+162,11.5);
				$this->fpdf->Text($x+75,16,'MOL ID No. 1133993');
				$this->fpdf->line($x+75,17.5,$x+105,17.5);
				$this->fpdf->Text($x+55,21,'PAYROLL FOR THE MONTH OF '.date("F Y",strtotime($sif->payroll_month)));
				$this->fpdf->line($x+55,22.5,$x+128,22.5);
			}
			
			protected function pdfStrc($x,$y)
			{
				$this->fpdf->rect($x,$y,$x+182,230); //whole structure
				$this->fpdf->Text($x+2,$y+10,'Sl.No');
				$this->fpdf->line($x+10,$y,$x+10,$y+230);//SN line
				$this->fpdf->Text($x+15,$y+10,'NAME OF THE EMPLOYEE');
				$this->fpdf->line($x+50,$y,$x+50,$y+230);//name line
				$this->fpdf->Text($x+52,$y+10,'WORK PERMIT');
				$this->fpdf->Text($x+52,$y+13,'NO (8 DIGIT NO)');
				$this->fpdf->line($x+70,$y,$x+70,$y+230);//work permit line
				$this->fpdf->Text($x+72,$y+10,'PERSONAL NO');
				$this->fpdf->Text($x+72,$y+13,'(14 DIGIT NO)');
				$this->fpdf->line($x+90,$y,$x+90,$y+230);//person code line
				$this->fpdf->Text($x+94,$y+10,'BANK NAME');
				$this->fpdf->line($x+110,$y,$x+110,$y+230);//Bank name
				$this->fpdf->Text($x+111,$y+3,'FAB CARD NO');
				$this->fpdf->Text($x+111,$y+6,'(16DIGITS) OR');
				$this->fpdf->Text($x+111,$y+9,'IBAN FOR');
				$this->fpdf->Text($x+111,$y+12,'PERSONAL ACC-');
				$this->fpdf->Text($x+111,$y+15,'0UNT(23DIGITS)');
				$this->fpdf->Text($x+111,$y+18,'OR C3/RAK(15DIGIT)');
				$this->fpdf->line($x+135,$y,$x+135,$y+230);//Iban
				$this->fpdf->Text($x+137,$y+8,'NO OF');
				$this->fpdf->Text($x+137,$y+11,' DAYS');
				$this->fpdf->Text($x+137,$y+13,'ABSENT');
				$this->fpdf->line($x+145,$y,$x+145,$y+230);//NO OF DAYS ABSENT
				$this->fpdf->Text($x+158,$y+5,"Employee's Net Salary");
				$this->fpdf->line($x+145,$y,$x+145,$y+230);//Employee's Net Salary
				$this->fpdf->line($x,$y+20,$x+192,$y+20);
				$this->fpdf->line($x+145,$y+10,$x+192,$y+10); //Fixed Portion
				$this->fpdf->Text($x+147,$y+15,"Fixed Portion");
				$this->fpdf->line($x+160,$y+10,$x+160,$y+230);//Variable Portion
				$this->fpdf->Text($x+162,$y+15,"Variable Portion");
				$this->fpdf->line($x+175,$y+10,$x+175,$y+230);//Total Payment
				$this->fpdf->Text($x+177,$y+15,"Total Payment");
				/*
				*values
				*/
			}
			public function CreateSIFPDFTemplate(Request $request)
			{
				$template_id = $request->template_id;
				$sifDataOne = SifTemplateDetails::where("template_id",$template_id)->first();
				$this->fpdf = new Fpdf;
				$this->fpdf->AddPage();
				$x = 10;
				$this->pageHeader($x,$sifDataOne);
				$y=30; 
				$this->fpdf->SetFont('Arial','',5);
				$this->pdfStrc($x,$y);
				$sifData = SifTemplateDetails::where("template_id",$template_id)->get();
				//$sifData = SifTemplateDetails::get();
				$i=1;
				$newPage = 1;
				foreach($sifData as $_sif)
				{
					if($i == 1 )
					{
						$y=$y+13; 
					}
					else
					{
						$y=$y+5; 
					}
				if($newPage > 30)
				{
					$newPage = 1;
					$this->fpdf->AddPage();
					
				$x = 10;
				$y=10; 
				$this->pdfStrc($x,$y);
				$y=$y+13; 
				}					
				$this->fpdf->Text($x+2,$y+10,$i);
				$this->fpdf->Text($x+15,$y+10,$_sif->name);
				$this->fpdf->Text($x+52,$y+10,$_sif->permit);
				$this->fpdf->Text($x+72,$y+10,$_sif->person_code);
				$this->fpdf->Text($x+94,$y+10,$_sif->bank_name);
				$this->fpdf->Text($x+111,$y+10,$_sif->iban);
				$this->fpdf->Text($x+140,$y+10,$_sif->days_absent);
				$this->fpdf->Text($x+150,$y+10,'AED '.$_sif->fixed);
				$this->fpdf->Text($x+165,$y+10,'AED '.$_sif->variable);
				$this->fpdf->Text($x+180,$y+10,'AED '.$_sif->total);
				$y = $y+2;
				if($newPage <30)
				{
					$this->fpdf->line($x,$y+10,$x+192,$y+10);
				}
				
					$i++;
					$newPage++;
				}
				$this->fpdf->Text($x+150,265,"AED ".$_sif->fixed_count);
				$this->fpdf->Text($x+165,265,"AED ".$_sif->variable_count);
				$this->fpdf->Text($x+180,265,"AED ".$_sif->total_count);
				$this->fpdf->rect($x+145,260,$x+37,10);
				$this->fpdf->line($x+160,260,$x+160,270);
				$this->fpdf->line($x+175,260,$x+175,270);
				/*
				*values
				*/	
				
				$this->fpdf->SetFont('Arial','',9);
				$this->fpdf->Text($x,270,"CONTACT PERSON - Rajan");
					
				$this->fpdf->Text($x,275,"TELEPHONE - +971 4 392 1484");
					
				$this->fpdf->Text($x,280,"EMAIL -  rajan@mbmuae.ae");
					
				$pdfName = 'SIF.pdf';
				$this->fpdf->Output();
			}
			
			public function finalStepSIF(Request $request)
			{
				$tId =  $request->template_id;
					return view("SIF/finalStepSIF",compact('tId'));
			}
		
			public function csvSIF(Request $request)
			{
				$template_id =  $request->template_id;
				$sifDataOne = SifTemplateDetails::where("template_id",$template_id)->first();
				$sifData = SifTemplateDetails::where("template_id",$template_id)->get();
				$filename = 'SIF.csv';
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'";'); 
			
			$header1 = array();
			$header1[] = 'Company Name : '.$sifDataOne->company_name."\nMOL ID No. 1133993\nPAYROLL FOR THE MONTH OF ".date("F Y",strtotime($sifDataOne->payroll_month));
			
		
			$f = fopen('php://output', 'w');
			fputcsv($f, $header1, ',');
       
			
			$header = array();
			$header[] = 'S.N';
			$header[] = 'NAME OF THE EMPLOYEE';
			$header[] = 'WORK PERMIT NO (8 DIGIT NO)';
			$header[] = 'PERSONAL NO (14 DIGIT NO)';
			$header[] = 'BANK NAME';
			$header[] = 'FAB CARD NO(16 DIGITS) OR IBAN FOR PERSONAL ACCOUNT (23 DIGITS) OR C3/RAK (15 DIGIT)';
			$header[] = 'NO OF DAYS ABSENT';
			$header[] = 'Fixed Portion';
			$header[] = 'Variable Portion';
			$header[] = 'Total Payment';
		
			$f = fopen('php://output', 'w');
			fputcsv($f, $header, ',');
			/*
			*get List of holidays
			*/
			
			
						$index=1;
			foreach ($sifData as $sif) {
				$values = array();
				$values[] = $index;
				$values[] = $sif->name;
				$values[] =  $sif->permit;
				$values[] =  $sif->person_code;
				$values[] =  $sif->bank_name;
				$values[] =  $sif->iban;
				$values[] =  $sif->days_absent;
				$values[] =  'AED '.$sif->fixed;
				$values[] = 'AED '.$sif->variable;
				$values[] =  'AED '.$sif->total;
				
				fputcsv($f, $values, ',');
				/* echo '<pre>';
				print_r($values);
				exit; */
			}
				$values = array();
				$values[] = '';
				$values[] = '';
				$values[] = '';
				$values[] = '';
				$values[] = '';
				$values[] = '';
				$values[] = '';
				$values[] =  'AED '.$sif->fixed_count;
				$values[] =  'AED '.$sif->variable_count;
				$values[] =  'AED '.$sif->total_count;
				
				fputcsv($f, $values, ',');
			exit();
			}













			public function downloadExitFormDataProcess(Request $request)
			{
				$rowid = $request->rowid;
				$offEmpDetails = EmpOffline::where("id",$rowid)->orderBy("id","DESC")->first();

				if($offEmpDetails->department)
				{
					$deptDetails = Department::where("id",$offEmpDetails->department)->orderBy("id","DESC")->first();

					if($deptDetails)
					{
						$deptName = $deptDetails->department_name;
					}
					else
					{
						$deptName = "--";
					}
				}


				if($offEmpDetails->designation)
				{
					$desigDetails = Designation::where("id",$offEmpDetails->designation)->orderBy("id","DESC")->first();

					if($desigDetails)
					{
						$desigName = $desigDetails->name;
					}
					else
					{
						$desigName = "--";
					}
				}

				$emailDetails = Employee_attribute::where("emp_id",$offEmpDetails->emp_id)->where("attribute_code","email")->orderBy("id","DESC")->first();

				if($emailDetails)
				{
					$email = $emailDetails->attribute_values;
				}
				else
				{
					$email = "--";
				}


				if($offEmpDetails->leaving_type==1)
				{
					$leaveType = "Resign";
				}
				elseif($offEmpDetails->leaving_type==2)
				{
					$leaveType = "Terminate";
				}
				elseif($offEmpDetails->leaving_type==6)
				{
					$leaveType = "Abscond";
				}
				else
				{
					$leaveType = "--";
				}


				

				
				
				
				
				
				$filepath = public_path('exitform/EXITFORM.pdf');
				$filepath2 = public_path('exitform/');
				
				$this->fpdf = new Fpdf;
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->AddPage();
				//$this->fpdf->Text(10, 10, "Hello World!"); 

				$imgpath1 = public_path('exitform/image-1.jpg');
				$imgpath2 = public_path('exitform/image-22.jpg');
				$imgpath3 = public_path('exitform/image-11.jpg');
				$imgpath4 = public_path('exitform/image-3.jpg');
				$imgpath5 = public_path('exitform/image-4.jpg');
				$imgpath7 = public_path('exitform/image_7.png');
				$imgpath8 = public_path('exitform/image_8.png');

				

				
				$x = 10;
				//$this->pageHeader($x,$sifDataOne);
				$y=30; 
				$this->fpdf->Image($imgpath1, 5, $y-30, 203);

				$this->fpdf->rect($x,$y+40,$x+90,10); //whole structure
				$this->fpdf->Text($x+2,$y+45,'Mr./ Mrs./ Ms.:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->emp_name)
				{
					$this->fpdf->Text($x+26,$y+45,$offEmpDetails->emp_name);
				}
				else
				{
					$this->fpdf->Text($x+26,$y+45,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line



				$this->fpdf->rect($x,$y+40,$x+185,10); //whole structure
				$this->fpdf->Text($x+102,$y+45,'Emp ID:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->emp_id)
				{
					$this->fpdf->Text($x+116,$y+45,$offEmpDetails->emp_id);
				}
				else
				{
					$this->fpdf->Text($x+116,$y+45,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line


				$this->fpdf->rect($x,$y+50,$x+90,10); //whole structure
				$this->fpdf->Text($x+2,$y+55,'Date of Resignation:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->date_of_resign)
				{
					$emp_resign = date("d M, Y", strtotime($offEmpDetails->date_of_resign));
					$this->fpdf->Text($x+35,$y+55,$emp_resign);
				}
				else
				{
					$this->fpdf->Text($x+35,$y+55,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line


				$this->fpdf->rect($x,$y+50,$x+185,10); //whole structure
				$this->fpdf->Text($x+102,$y+55,'Last Working Day:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->date_of_resign)
				{
					$this->fpdf->Text($x+132,$y+55,$offEmpDetails->date_of_resign);
				}
				else
				{
					$this->fpdf->Text($x+132,$y+55,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line







				$this->fpdf->rect($x,$y+60,$x+90,10); //whole structure
				$this->fpdf->Text($x+2,$y+65,'Department:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->department)
				{
					$this->fpdf->Text($x+23,$y+65,$deptName);
				}
				else
				{
					$this->fpdf->Text($x+45,$y+65,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line




				$this->fpdf->rect($x,$y+60,$x+185,10); //whole structure
				$this->fpdf->Text($x+102,$y+65,'Date of Joining:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->date_of_joining)
				{
					$emp_doj = date("d M, Y", strtotime($offEmpDetails->date_of_joining));
					$this->fpdf->Text($x+128,$y+65,$emp_doj);
				}
				else
				{
					$this->fpdf->Text($x+128,$y+65,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line







				$this->fpdf->rect($x,$y+70,$x+90,10); //whole structure
				$this->fpdf->Text($x+2,$y+75,'Designation:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->designation)
				{
					$this->fpdf->Text($x+23,$y+75,$desigName);
				}
				else
				{
					$this->fpdf->Text($x+23,$y+75,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line











				$this->fpdf->rect($x,$y+70,$x+185,10); //whole structure
				$this->fpdf->Text($x+102,$y+75,'Location:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->location)
				{
					$this->fpdf->Text($x+117,$y+75,$offEmpDetails->location);
				}
				else
				{
					$this->fpdf->Text($x+117,$y+75,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line











				$this->fpdf->rect($x,$y+80,$x+90,10); //whole structure
				$this->fpdf->Text($x+2,$y+85,'Personal Email id:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				
				$this->fpdf->Text($x+32,$y+85,$email);
				
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line







				$this->fpdf->rect($x,$y+80,$x+185,10); //whole structure
				$this->fpdf->Text($x+102,$y+85,'Official Email ID:');
				$this->fpdf->SetFont('Arial', 'B', 10);
				if($offEmpDetails->date_of_resign)
				{
					$this->fpdf->Text($x+132,$y+85,$offEmpDetails->date_of_resign);
				}
				else
				{
					$this->fpdf->Text($x+132,$y+85,'--');
				}
				
				$this->fpdf->SetFont('Arial', '', 10);
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line






				$this->fpdf->Image($imgpath7, 5, $y+92, 209);







				$this->fpdf->rect($x-1,$y+114,$x+71.7,8); //whole structure
				$this->fpdf->SetFont('Arial', '', 9);
				$this->fpdf->Text($x+8,$y+119,'1');
				$this->fpdf->line(21,144,21,166);//SN line
				$this->fpdf->SetFont('Arial', '', 9);
				$this->fpdf->Text($x+13,$y+119,'Reason for leaving.');



				$this->fpdf->rect($x-1,$y+114,$x+186,8); //whole structure	
				$this->fpdf->Text($x+82,$y+119,$leaveType);		
				$this->fpdf->SetFont('Arial', 'B', 9);	
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line



				$this->fpdf->rect($x-1,$y+122,$x+71.7,7); //whole structure
				$this->fpdf->SetFont('Arial', '', 9);
				$this->fpdf->Text($x+8,$y+127,'2');
				$this->fpdf->SetFont('Arial', '', 9);
				$this->fpdf->Text($x+13,$y+127,'No. of working days (current month)');

				$this->fpdf->rect($x-1,$y+122,$x+186,7); //whole structure	
				$this->fpdf->Text($x+82,$y+127,"Test Reason");			
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line







				$this->fpdf->rect($x-1,$y+129,$x+71.7,7); //whole structure
				$this->fpdf->SetFont('Arial', '', 9);
				$this->fpdf->Text($x+8,$y+134,'3');
				$this->fpdf->SetFont('Arial', '', 9);
				$this->fpdf->Text($x+13,$y+134,'All official documents/ Warning letters');

				$this->fpdf->rect($x-1,$y+129,$x+186,7); //whole structure
				$this->fpdf->Text($x+82,$y+134,"Test Reason");				
				$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line
				
				
				$this->fpdf->Image($imgpath8, 5, $y+135, 209);

				









				
				//$this->fpdf->line($x-10,$y,$x-10,$y-20);//SN line





				// $this->fpdf->Image($imgpath2, 5, $y+92, 209);

				// $this->fpdf->AddPage();
				// //$this->fpdf->Image($imgpath3, 5, $y+92, 209);
				// $this->fpdf->Image($imgpath3, 5, $y-30, 203);

				// $this->fpdf->Image($imgpath4, 5, $y+22, 209);

				// $this->fpdf->AddPage();
				// //$this->fpdf->Image($imgpath3, 5, $y+92, 209);
				// $this->fpdf->Image($imgpath3, 5, $y-30, 203);

				// $this->fpdf->Image($imgpath5, 5, $y+22, 209);







				// $this->fpdf->Text($x+15,$y+10,'NAME OF THE EMPLOYEE');
				// $this->fpdf->line($x+50,$y,$x+50,$y+230);//name line
				// $this->fpdf->Text($x+52,$y+10,'WORK PERMIT');
				// $this->fpdf->Text($x+52,$y+13,'NO (8 DIGIT NO)');
				// $this->fpdf->line($x+70,$y,$x+70,$y+230);//work permit line
				// $this->fpdf->Text($x+72,$y+10,'PERSONAL NO');
				// $this->fpdf->Text($x+72,$y+13,'(14 DIGIT NO)');
				// $this->fpdf->line($x+90,$y,$x+90,$y+230);//person code line
				// $this->fpdf->Text($x+94,$y+10,'BANK NAME');
				// $this->fpdf->line($x+110,$y,$x+110,$y+230);//Bank name




				
				$filename=public_path('exitform/exitform-'.$rowid.'.pdf');
				$this->fpdf->Output($filename,'F');
				
				
				
				
				
				
				// $file =  "EXITFORM.pdf";

				// $fileName = public_path("/exitform");
				// $newf = $fileName."/".$file;
				// //return $newf;
				// $headers = ['Content-Type: application/pdf'];
				$newName = 'exitform-'.$rowid.'.pdf';
			  
				//return response()->download($newf, $newName, $headers);


				return $newName;

				
				
				
				//return "Hello";




			}


}