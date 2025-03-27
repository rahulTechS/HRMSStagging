<?php

namespace App\Http\Controllers\Offerletter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Models\Company\Department;
use App\Models\Company\Product;
use App\Models\Recruiter\Designation;
use App\Models\Offerletter\OfferletterDetails;
use App\Models\Offerletter\SalaryBreakup;
use App\Models\Offerletter\IncentiveBreakup;
use App\Models\Employee\Employee_details;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Employee\Employee_attribute;
use App\Models\Logs\DocumentCollectionDetailsLog;
use Session;


class OfferController extends Controller
{
	private $fpdf;
	public function __construct()
	{
		
		
	}
	public function PdfHeader($pdf)
	{
		
		
		$pdf->Image(secure_asset('hrm/pdf/backgroup.png'),8,1,-430);
		$pdf->Image(secure_asset('hrm/pdf/backgroup.png'),30,1,-430);
		$pdf->Image(secure_asset('hrm/pdf/backgroup.png'),50,1,-430);
		$pdf->Image(secure_asset('hrm/pdf/backgroup.png'),70,1,-430);
		$pdf->Image(secure_asset('hrm/pdf/backgroup.png'),90,1,-430);
		$pdf->Image(secure_asset('hrm/pdf/backgroup.png'),120,1,-430);
		$pdf->Image(secure_asset('hrm/pdf/backgroup.png'),150,1,-430);
		$pdf->Image(secure_asset('hrm/pdf/pdf-logo.png'),14,6,-150);
		$pdf->SetFont('Arial','B',14);
		$pdf->SetTextColor(152,125,74);
		
		$pdf->Image(secure_asset('hrm/pdf/companyname.jpg'),60,10,-100);
		$pdf->SetDrawColor(152,125,74);
		
		$pdf->SetFont('Arial','',10);
		$pdf->SetTextColor(0,0,0);
		$pdf->Text(64,25,'305/501, Business Atrium Building Oud Metha ,Next to Homes R US,');
		$pdf->SetFont('Arial','',8);
		$pdf->Text(64,30,'Dubai, UAE (P.O 243065)');
		$pdf->Text(64,35,'+971 4 384 8484');
		$pdf->SetFont('Arial','B',8);
		$pdf->Text(64,40,'Email: info@mbmuae.ae');
		$pdf->Text(120,40,'Web: www.suranigroup.com');
		
	}
	public function createPDF(Request $request)
	{
		$offerLetterId = $request->offerletterId;
		$mode = $request->mode;
		/*
		*get Offer letter Details
		*start Code
		*/
		$offerLetterDetails = OfferletterDetails::where("id",$offerLetterId)->first();
		
		$joiningDate = $offerLetterDetails->joining_date;
		$empName = $offerLetterDetails->emp_name;
		//$empName = 'rahul srivastava';
		
		$passportNo = $offerLetterDetails->passport_no;
		$mobileNo = $offerLetterDetails->mobile_no;
		$email = $offerLetterDetails->email;
		$designationId = $offerLetterDetails->designation;
		$productId = $offerLetterDetails->product;
		$departmentId = $offerLetterDetails->department;
		$created_at = $offerLetterDetails->created_at;
		$package_id = $offerLetterDetails->package_id;
		$monthlyPackage = $offerLetterDetails->monthly_package;
		
		$departmentMod = Department::where("id",$departmentId)->first();
		$productMod = Product::where("id",$productId)->first();
		$designationMod = Designation::where("id",$designationId)->first();
		
		
		$departmentName = $departmentMod->department_name;
		
		//$productName = $productMod->product_name;
		$productName = '';
		
		$designationName = $designationMod->name;
	
		
		/*
		*get Offer letter Details
		*start Code
		*/
		/*
		*get package lists
		*start code
		*/
		 /*$salaryDetails =  SalaryBreakup::where("id",$package_id)->first();
		   $labelName = $salaryDetails->label_name;
		   $percentange = $salaryDetails->percentange;
		   $labelNameArray = explode(",",$labelName);
		   $percentangeArray = explode(",",$percentange);
		   $breakUpArray = array();
		   for($i=0;$i<count($labelNameArray);$i++)
		   {
			   $percentangeSet = $percentangeArray[$i];
			   $percentangeSet1 =$percentangeSet*$monthlyPackage;
			   $percentangeSetFinal = $percentangeSet1/100;
			   $percentangeSetFinal = round($percentangeSetFinal,2);
			   $labelNameValue = $labelNameArray[$i];
			   $breakUpArray[$labelNameValue] = $percentangeSetFinal;
		   }
		   */
		   $breakUpArray = array();
		/*
		*get package lists
		*end code
		*/
		$this->fpdf = new Fpdf;
		$this->fpdf->AddPage();
		$this->fpdf->Image(secure_asset('hrm/pdf/watermark-suranigroup.jpg'),45,100,-550);
		
		$this->PdfHeader($this->fpdf);
		$this->fpdf->SetDrawColor(0,0,0);
		
		$x = 10;
		$this->PdfFooter($this->fpdf,$x,208);
		$y=55; 
        $this->fpdf->SetFont('Arial','B',14);
		$this->fpdf->Text($x+75,$y,'Letter of Offer');
		$y = $y+1;
		$this->fpdf->line($x+75,$y,$x+108,$y);
		$y = $y+12;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x,$y,'Date:');
		$this->fpdf->Text($x+9.5,$y,date("d M Y",strtotime($created_at)));
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);	
		$this->fpdf->Text($x,$y,'Name:');
		$this->fpdf->Text($x+9.5,$y,strtoupper($empName));
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x,$y,'Passpost no:');
		$this->fpdf->Text($x+18,$y,$passportNo);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x,$y,'Mobile no:');
		$this->fpdf->Text($x+15,$y,$mobileNo);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x,$y,'Email Id:');
		$this->fpdf->Text($x+13,$y,$email);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x,$y,'Strictly Private & Confidential');
		$y = $y+1;
		$this->fpdf->line($x,$y,$x+41,$y);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x,$y,'This Employment Agreement and the other documents comprising the employment Documentation set forth the terms and conditions of the employ-');
		$y = $y+4;
		$this->fpdf->Text($x,$y,'ment of ');
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->SetX(0);
		$this->fpdf->SetY($y-3);
		//$this->fpdf->Text($x+11,$y,strtoupper($empName));
		$this->fpdf->Cell(0,5,'            '.strtoupper($empName).'("Employee") with Smart Union Commercial Brokerage LLC.',0,0,'L',FALSE);
		
		//$this->fpdf->Text($x+11,$y,'("Employee") with Smart Union Commercial Brokerage LLC.');
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x,$y,'This document details the terms and conditions of the employment contract between the company and the employee and sets out the particular emp-');
		$y = $y+4;
		$this->fpdf->Text($x,$y,'loyment with the company pursuant to');
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+48,$y,'Federal Law 8 of 1980 (the "UAE Federal Labour Law")');
		
		 
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'1. Job Title:');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+28,$y,'The company shall employ the employee as an');
		
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+88,$y,$designationName.'-'.$departmentName);
		//$this->fpdf->SetFont('Arial','',8);
		//$this->fpdf->Text($x+160,$y,'');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'and this appointment is subject  to necessary completion of the Labour');
		 
		
		$this->fpdf->Text($x+102,$y,'and Immigration formalitites of the United Arab Emirates. The gen-');
		$y = $y+4;
		$this->fpdf->Text($x+13,$y,'eral terms and conditions of the appointment are as follows.');
		
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'2. Start Date:');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+28,$y,'The employment with the company will start on the date that employee reports to work which is planned to be');
		
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+165,$y,date("d M Y",strtotime($joiningDate)));
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'or a date otherwise mutually agreed.');
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'3. Employment Status:');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+41,$y,'Will be');
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+50,$y,'single.');
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'4. Remuneration:');
		
		$this->fpdf->Text($x+33,$y,'Total monthly Package AED.'.$monthlyPackage.'/- (Three Thousand Dirhams Only) ');
		
		$y = $y+7;
		$indexPage = 1;
		foreach($breakUpArray as $breakKey=>$breakValue)
		{
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,$indexPage.'). '.$breakKey.':');
		
		$this->fpdf->Text($x+43,$y,'AED. '.$breakValue.'/-');
		$indexPage++;
		$y = $y+4;
		
		}
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'5. Medical Coverage:');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+40,$y,'During the employe'.utf8_encode("'").'s Employment he/she will be entitled to medical insurance under  the company'.utf8_encode("'").'s group');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'medical insurance plan in effect and as may be amended from time to time.');
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'6. Duration of Contract : ');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+43,$y,'This agreement shall run for a period of two years from the date of joining and shall be a limited period contract. It');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'will be renewed after the two year term, unless notice of termination has been given by either party.');
		
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'7. Probation Period : ');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+40,$y,'The first six months of your employment shall be treated as probationary period. Your appointment to the permanent');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'staff will be confirmed at the conclusion of the six month period. This agreement may be terminated without notice at any time by either side');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'during the probationary period and  thereafter by giving one month notice in writing.');
		
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'8. Annual Leave Entitlement : ');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+50,$y,'As per UAE labour Law you will be entitled to an annual Leave of 30 working days on confirmation. (prorata).');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'You will be provided with Air ticket (self) to your home airport after complition of two years employment, ');
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'9. Sick Leave : ');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+30,$y,'You will be entitled to sick leave according to the standards laid down by UAE labour law.');
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'10. End of services Gratuity Entitlement: ');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+65,$y,'You will be entitled to the end of service indemnity as stipulated by the UAE Labour Law.');
		
		$this->fpdf->AddPage();
		$this->fpdf->Image(secure_asset('hrm/pdf/watermark-suranigroup.jpg'),45,100,-550);
		$this->PdfHeader($this->fpdf);
		$this->fpdf->SetDrawColor(0,0,0);
		
		$x = 10;
		$this->PdfFooter($this->fpdf,$x,208);
		$y=55; 
		
		
		
		
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'11.Disciplinary and Grievance Code: ');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+60,$y,'The Employee should refer any disciplinary or grievance matter related to their employment to  Human');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'Resources. The disciplinary procedure applicable to the employee is set out in the employee handbook, but the company reserve the right');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'to adpot the procedure most appropriate to deal with any disciplinary matter concerning the employee, provided that such procedure accords');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'with local law. You will be required to sign a Non – Disclosure  and confidentialy agreement and any violation of it would warrant strict');
		
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'disciplinary action as per company HR policy.');
		
		
		 
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'12.Notice: ');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+25,$y,'Notwithstanding any rights or privelages accruing under the Labour Law, the company reserves the right to amend any or all of');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'those terms and conditions  by giving reasonable notice in writing.');
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'13.Repayment of Debts : ');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+45,$y,'Upon termination of this employment contract for whatever reason, the company reserves the right to deduct from');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'the employeeany sums due to the employer whether by way of salary, End of service gratuity or otherwise,any and all debts owing by the');
		
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'employee to the company at the time. This includes the cost incurred for recruitment, logistics and on boarding of employees if the employee');
		
		
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'terminates the contract before completion of one year of service.');
		
		 
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'14.Confidentiality : ');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+38,$y,'You will not either during the continuance of your employment or thereafter use to the detriment or prejudice of the');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'Company or its principals any confidential information or divulged to any person any trade secret or other confidential information con-');
		
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'cerning the business or affairs of the Company or any of its principals which may have come to your knowledge during your employ-');
		
		
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+13,$y,'ment. You shall both during and after your employment take all precautions to keep employment related information confidential:');
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+20,$y,'(i) ');
		$this->fpdf->Text($x+25,$y,'In the event of cessation of your employment for whatever reason you will return to the Company any papers associated with the');
		
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+25,$y,'Company'."'".'s affairs which might be in your possession and acceptance of this offer constitutes an undertaking that you will not re-');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+25,$y,'move or photocopy any such papers for your subsequent use. You will also during the continuance of your employment comply -');
		
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+25,$y,'with the terms of the data production act.');
		
		
		 $y = $y+7;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+20,$y,'(ii) ');
		$this->fpdf->Text($x+25,$y,'You undertake not to make any statement to the press or media about the Company or the Client without having prior written auth-');
		
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+25,$y,'orization from the Company and/ or the Client.');
		
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'15.Appointment Terms: ');
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+42,$y,'Appointment is subject to the employee: ');
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+20,$y,'a)    Being free from any obligations owed to a third party which might prevent from commencing work on the commencement date or ');
		
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+25.5,$y,'from properly performing the duties of the position');
		
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+20,$y,'b)    Fulfilling the necessary Dubai residency and work permit requirements');
		
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+20,$y,'c)    Providing the company with all required documentation.');
		 
		 
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+10,$y,'IN WITNESS WHEROF the parties have executed this agreement by accepting all terms and conditions by signing this employment Agreement ');
		$y = $y+4;
		
		$this->fpdf->Text($x+10,$y,'and returning it to group HR manager (Marked '."'".'private and confidential'."'".') This offer is vaild for 3 days from the date mentioned at the beg-');
		
		$y = $y+4;
		
		$this->fpdf->Text($x+10,$y,'inning of this Agreement.');
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+10,$y,'You'."'".'re sincerely,');
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+10,$y,'For Smart Union Commercial Brokerage LLC');
		
		/* $this->fpdf->AddPage();
		$this->fpdf->Image(secure_asset('hrm/pdf/watermark-suranigroup.jpg'),45,100,-550);
		$this->PdfHeader($this->fpdf);
		$this->fpdf->SetDrawColor(0,0,0);
		$x = 10;
		$y=55; 
		$y = $y+30; */
		$y = $y+12;
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->line($x+10,$y,$x+42,$y);
		$y = $y+4;
		$this->fpdf->Text($x+10,$y,'T.Rajan');
		$y = $y+4;
		$this->fpdf->Text($x+10,$y,'HR Manager');
		$y = $y+12;
		$this->fpdf->Text($x+10,$y,'Acceptance');
		$y = $y+1;
		$this->fpdf->line($x+10,$y,$x+27,$y);
		$y = $y+7;
	/* 	$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+10,$y,'I');
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+12,$y,$empName); */
		$this->fpdf->SetFont('Arial','',8);
		
		$htmlTags = 'I '.strtoupper($empName).' understand and accept the offer of employment and agree to the terms and conditions as detailed here in.';
		//$this->fpdf->Text($x+11,$y,'I '.strtoupper($empName).' understand and accept the offer of employment and agree to the terms and conditions as detailed here in.');
	/* $this->fpdf->SetLineWidth(0.1);
$this->fpdf->SetFillColor(255,255,204);
$this->fpdf->SetDrawColor(102,0,102);
$this->fpdf->WriteTag($x+11,$y,$htmlTags,1,"J",0,7); */
		
		$this->fpdf->SetY($y-3);
		$this->fpdf->Cell(8,3,'', 0, 'L');
		$this->fpdf->SetFont('Arial','',8);
		$cell = 'I ';
		$this->fpdf->Cell($this->fpdf->GetStringWidth($cell),3,$cell, 0, 'L');
		$this->fpdf->SetFont('Arial','B',8);
		$boldCell = strtoupper($empName);
		$this->fpdf->Cell($this->fpdf->GetStringWidth($boldCell),3,$boldCell, 0, 'L');
		$this->fpdf->SetFont('Arial','',8);
		$cell1 = ' understand and accept the offer of employment and agree to the terms and conditions as detailed here in.';
		$this->fpdf->Cell($this->fpdf->GetStringWidth($cell1),3,$cell1, 0, 'L');
		$y = $y+15;
       
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->line($x+10,$y,$x+40,$y);
		$y = $y+4;
		$this->fpdf->SetFont('Arial','',8);
		$this->fpdf->Text($x+10,$y,'Signature of Employee');
		$y = $y+4;
		$this->fpdf->Text($x+10,$y,'Date:');
		$this->fpdf->SetFont('Arial','B',8);
		$this->fpdf->Text($x+18,$y,date("d M Y",strtotime($joiningDate)));
		$this->PdfFooter($this->fpdf,$x,208);
		if($mode == 'D')
		{
			$pdfName = $empName.' - OfferLetter.pdf';
			$this->fpdf->Output('D',$pdfName);
		}
		else
		{
			$this->fpdf->Output();
		}
        exit;
	}
	public function PdfFooter($pdf,$x,$y)
	{
		$y = $y+80;
		
		$pdf->SetFont('Arial','B',8);
		$pdf->SetTextColor(0,0,0);
		
		$pdf->Image(secure_asset('hrm/pdf/border.png'),$x,$y,-140);
	}
	public function createletter(Request $request)
	{
		$documentId = $request->documentId;
		$departmentDetails =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentDetails = DocumentCollectionDetails::where("id",$documentId)->first();
		$offerLetterDetails = OfferletterDetails::where("document_id",$documentId)->first();
		return view("Offerletter/createletter",compact('departmentDetails','productDetails','designationDetails','documentDetails','documentId','offerLetterDetails'));
	}
	public function createletterAjax(Request $request)
	{
		$documentId = $request->documentId;
		$departmentDetails =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentDetails = DocumentCollectionDetails::where("id",$documentId)->first();
		$offerLetterDetails = OfferletterDetails::where("document_id",$documentId)->first();
		return view("Offerletter/createletterAjax",compact('departmentDetails','productDetails','designationDetails','documentDetails','documentId','offerLetterDetails'));
	}
	public function generateOfferLetterPost(Request $request)
	{
		
		$selectedFilter = $request->input();
		/*
		*check for existance of offerletter
		*/
		$docId = $selectedFilter['offerLetterData']['document_id'];
		$offerletterExists = OfferletterDetails::where("document_id",$docId)->first();
		/*
		*check for existance of offerletter
		*/
		if(empty($offerletterExists))
		{
		$modData = new OfferletterDetails();
		$modData->document_id = $selectedFilter['offerLetterData']['document_id'];
		}
		else
		{
			
			$modData = OfferletterDetails::find($offerletterExists->id);
		
		}
	
		$modData->joining_date = date('Y-m-d',strtotime($selectedFilter['offerLetterData']['joining_date']));
		$modData->emp_name = $selectedFilter['offerLetterData']['emp_name'];
		$modData->passport_no = $selectedFilter['offerLetterData']['passport_no'];
		$modData->mobile_no = $selectedFilter['offerLetterData']['mobile_no'];
		$modData->email = $selectedFilter['offerLetterData']['email'];
		$modData->designation = $selectedFilter['offerLetterData']['designation'];
		//$modData->product = $selectedFilter['offerLetterData']['product'];
		$modData->department = $selectedFilter['offerLetterData']['department'];
		//$modData->proposed_salary = $selectedFilter['offerLetterData']['proposed_salary'];
		$modData->monthly_package = $selectedFilter['offerLetterData']['proposed_salary'];
		
		
		$modData->save();
		$documentId= $selectedFilter['offerLetterData']['document_id'];
		$documentCollectionMod = DocumentCollectionDetails::find($documentId);
		$getExistingStatus = DocumentCollectionDetails::where("id",$documentId)->first()->status;
		if($getExistingStatus <4)
		{
		$documentCollectionMod->status = 4;
		
		$documentCollectionMod->serialized_id = 'Doc-Offer-generated-000'.$documentId;
		
		}
		$documentCollectionMod->update_offer_letter_allow = 1;
		$documentCollectionMod->offer_letter_generated_date = date("Y-m-d");
		$documentCollectionMod->offer_letter_status = 2;
		$documentCollectionMod->save();
		$empName = $selectedFilter['offerLetterData']['emp_name'];
		$request->session()->flash('message','Offer Letter generated Successfully For '.$empName.'.');
        return redirect('documentcollection');
	}
	
	
	public function generateOfferLetterPostAjax(Request $request)
	{
		
		$selectedFilter = $request->input();
		/*
		*check for existance of offerletter
		*/
		$docId = $selectedFilter['offerLetterData']['document_id'];
		$offerletterExists = OfferletterDetails::where("document_id",$docId)->first();
		/*
		*check for existance of offerletter
		*/
		if(empty($offerletterExists))
		{
		$modData = new OfferletterDetails();
		$modData->document_id = $selectedFilter['offerLetterData']['document_id'];
		}
		else
		{
			
			$modData = OfferletterDetails::find($offerletterExists->id);
		
		}
	
		$modData->joining_date = date('Y-m-d',strtotime($selectedFilter['offerLetterData']['joining_date']));
		$modData->emp_name = $selectedFilter['offerLetterData']['emp_name'];
		$modData->passport_no = $selectedFilter['offerLetterData']['passport_no'];
		$modData->mobile_no = $selectedFilter['offerLetterData']['mobile_no'];
		$modData->email = $selectedFilter['offerLetterData']['email'];
		$modData->designation = $selectedFilter['offerLetterData']['designation'];
		//$modData->product = $selectedFilter['offerLetterData']['product'];
		$modData->department = $selectedFilter['offerLetterData']['department'];
		//$modData->proposed_salary = $selectedFilter['offerLetterData']['proposed_salary'];
		$modData->monthly_package = $selectedFilter['offerLetterData']['proposed_salary'];
		
		
		if($modData->save()){
			$finaljsondata = json_encode(array('offerLetterData' =>$selectedFilter), JSON_PRETTY_PRINT);
			$logObj = new DocumentCollectionDetailsLog();
			$logObj->document_id =$selectedFilter['offerLetterData']['document_id'];
			$logObj->created_by=$request->session()->get('EmployeeId');
			$logObj->title ="Offer Letter generated";
			$logObj->response =$finaljsondata;
			$logObj->category ="Offer Letter";
			$logObj->save();
		}
		$documentId= $selectedFilter['offerLetterData']['document_id'];
		$documentCollectionMod = DocumentCollectionDetails::find($documentId);
		$getExistingStatus = DocumentCollectionDetails::where("id",$documentId)->first()->status;
		if($getExistingStatus <4)
		{
		$documentCollectionMod->status = 4;
		
		$documentCollectionMod->serialized_id = 'Doc-Offer-generated-000'.$documentId;
		
		}
		$documentCollectionMod->update_offer_letter_allow = 1;
		$documentCollectionMod->offer_letter_generated_date = date("Y-m-d");
			$documentCollectionMod->offer_letter_status = 2;
			$documentCollectionMod->offer_letter_onboarding_status=2;
			$documentCollectionMod->offer_letter_createBy=$request->session()->get('EmployeeId');
		$documentCollectionMod->save();
		$empName = $selectedFilter['offerLetterData']['emp_name'];
		echo "Offer Letter generated Successfully";
        exit;
	}
	public function offerLetterList(Request $req)
	{
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$offerdetails = OfferletterDetails::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['joining_date'] = '';
		if(!empty($req->session()->get('emp_name')))
			{
			
				$emp_name = $req->session()->get('emp_name');
				$filterList['emp_name'] = $emp_name;
				$offerdetails = $offerdetails->where("emp_name","like",$emp_name."%");
			}
		if(!empty($req->session()->get('joining_date')))
			{
			
				$joining_date = date("Y-m-d",strtotime($req->session()->get('joining_date')));
				$filterList['joining_date'] = $joining_date;
				$offerdetails = $offerdetails->whereDate("joining_date",$joining_date);
			}	
			if(!empty($req->session()->get('department')))
			{
			
				$department = $req->session()->get('department');
				$filterList['deptID'] = $department;
				$offerdetails = $offerdetails->where("department",$department);
			}	
			if(!empty($req->session()->get('product')))
			{
			
				$product = $req->session()->get('product');
				$filterList['productID'] = $product;
				$offerdetails = $offerdetails->where("product",$product);
			}			
		if(!empty($req->session()->get('designation')))
			{
			
				$designation = $req->session()->get('designation');
				$filterList['designationID'] = $designation;
				$offerdetails = $offerdetails->where("designation",$designation);
			}					
			$offerdetails = $offerdetails->get();
		
		
		
		return view("Offerletter/offerLetterList",compact('departmentLists','productDetails','designationDetails','offerdetails','filterList'));
	}
	
	public function appliedFilterOnOfferletter(Request $request)
	{
				$selectedFilter = $request->input();
				$request->session()->put('emp_name',$selectedFilter['emp_name']);		
				$request->session()->put('joining_date',$selectedFilter['joining_date']);
				$request->session()->put('department',$selectedFilter['department']);
				$request->session()->put('product',$selectedFilter['product']);
				$request->session()->put('designation',$selectedFilter['designation']);
				return redirect('offerLetterList');
			
	}
	
	public function resetOfferletterFilter(Request $request)
	{
				$request->session()->put('emp_name','');		
				$request->session()->put('joining_date','');
				$request->session()->put('department','');
				$request->session()->put('product','');
				$request->session()->put('designation','');
				$request->session()->flash('message','Filters Reset Successfully.');
				return redirect('offerLetterList');
	}
	public function manageSalaryStructure(Request $req)
	{
		
		$salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC");
		if(!empty($req->session()->get('department_breakup')))
			{
				$department_breakup  =$req->session()->get('department_breakup');
				$salaryBreakUpdetails =  $salaryBreakUpdetails->where("dept_id",$department_breakup);
			}
		
		if(!empty($req->session()->get('designation_breakup')))
			{
				$designation_breakup  =$req->session()->get('designation_breakup');
				$salaryBreakUpdetails =   $salaryBreakUpdetails->where("designation",$designation_breakup);
			}
		$salaryBreakUpdetails =   $salaryBreakUpdetails->get();
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$filterList['deptID'] = $req->session()->get('department_breakup');
		$filterList['designation'] = $req->session()->get('designation_breakup');
		$filterList['designId'] = $req->session()->get('designation_breakup');
		
		return view("Offerletter/manageSalaryStructure",compact('salaryBreakUpdetails','departmentLists','designationDetails','filterList'));
	}
	
	public function addSalaryStructure()
	{
		$departmentDetails =  Department::where("status",1)->orderBy("id","DESC")->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		return view("Offerletter/addSalaryStructure",compact('departmentDetails','designationDetails'));
	}
	
	public function editSalaryBreakup(Request $request)
	{
		$breakUpId = $request->breakupId;
		$salaryBreakUpDetails = SalaryBreakup::where("id",$breakUpId)->first();
		$departmentDetails =  Department::where("status",1)->orderBy("id","DESC")->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		return view("Offerletter/editSalaryBreakup",compact('departmentDetails','designationDetails','salaryBreakUpDetails'));
	}
	
	public function manageSalaryBackupPost(Request $request)
	{
		$selectedFilter = $request->input();
		
		$department = $selectedFilter['salaryBreakup']['department'];
		$designation = $selectedFilter['salaryBreakup']['designation'];
		$caption = $selectedFilter['salaryBreakup']['caption'];
		$monthly_salary = $selectedFilter['salaryBreakup']['monthly_salary'];
		$labelNameStr = implode(",",$selectedFilter['salaryBreakup']['label_name']);
		$percentangeStr = implode(",",$selectedFilter['salaryBreakup']['percentange']);
		$salaryBreakupModel = new SalaryBreakup();
		$salaryBreakupModel->dept_id = $department;
		$salaryBreakupModel->designation = $designation;
		$salaryBreakupModel->caption = $caption;
		$salaryBreakupModel->label_name = $labelNameStr;
		$salaryBreakupModel->percentange = $percentangeStr;
		$salaryBreakupModel->monthly_salary = $monthly_salary;
		$salaryBreakupModel->status = 1;
		$salaryBreakupModel->save();
		
		$request->session()->flash('message','Salary Breakup Saved.');
        return redirect('manageSalaryStructure');
		
	}
	public function manageSalaryBackupPostUpdate(Request $request)
	{
		$selectedFilter = $request->input();
		
		$department = $selectedFilter['salaryBreakup']['department'];
		$designation = $selectedFilter['salaryBreakup']['designation'];
		$caption = $selectedFilter['salaryBreakup']['caption'];
		$breakupid = $selectedFilter['salaryBreakup']['id'];
		$salaryBreakupModelUpdate = SalaryBreakup::find($breakupid);
		$salaryBreakupModelUpdate->dept_id = $department;
		$salaryBreakupModelUpdate->designation = $designation;
		$salaryBreakupModelUpdate->caption = $caption;

		$salaryBreakupModelUpdate->save();
		
		$request->session()->flash('message','Salary Breakup Updated.');
        return redirect('manageSalaryStructure');
		
	}
	public function viewSalaryBreakup(Request $request)
	{
		$breakUpId =  $request->breakupId;
		$breakUpIdModel = SalaryBreakup::where("id",$breakUpId)->first();
		
		return view("Offerletter/viewSalaryBreakup",compact('breakUpIdModel'));
	}
	
	public function checkSalarybreakup(Request $request)
	{
			$deptId =  $request->deptId;
			$designId =  $request->designId;
			$cap =  $request->cap;
			$salaryBreakUpDetails = SalaryBreakup::where("dept_id",$deptId)->where("designation",$designId)->where("caption",$cap)->where("status",1)->first();
			if(empty($salaryBreakUpDetails))
			{
				echo "Allow";
			}
			else
			{
				echo "Not Allow";
			}
			exit;
	}
   public function checkSalarybreakupEdit(Request $request)
	{
			$deptId =  $request->deptId;
			$designId =  $request->designId;
			$cap =  $request->cap;
			$breakupId =  $request->breakupId;
			$salaryBreakUpDetails = SalaryBreakup::where("dept_id",$deptId)->where("designation",$designId)->where("caption",$cap)->where("id","!=",$breakupId)->where("status",1)->first();
			if(empty($salaryBreakUpDetails))
			{
				echo "Allow";
			}
			else
			{
				echo "Not Allow";
			}
			exit;
	}
   public function appliedFilterOnBreakup(Request $request)
   {
				$selectedFilter = $request->input();
				$request->session()->put('department_breakup',$selectedFilter['dept_id']);
				$request->session()->put('designation_breakup',$selectedFilter['designation']);
				$request->session()->flash('message','Filters applied Successfully.');
				return redirect('manageSalaryStructure');
   }
   public function resetBreakupFilter(Request $request)
   {
				$request->session()->put('department_breakup','');
				$request->session()->put('designation_breakup','');
				$request->session()->flash('message','Filters Reset Successfully.');
				return redirect('manageSalaryStructure');
   }
   public function getCaptionOfSalaryBreakup(Request $request)
   {
	   $deptId = $request->deptId;
	   $designId = $request->designId;
	   $salaryDetails =  SalaryBreakup::where("dept_id",$deptId)->where("designation",$designId)->where("status",1)->get();
	   return view("Offerletter/getCaptionOfSalaryBreakup",compact('salaryDetails'));
   }
   public function getSalaryBreakup(Request $request)
   {
	   $deptId = $request->deptId;
	   $designId = $request->designId;
	   $caption = $request->cap;
	   $salaryDetails =  SalaryBreakup::where("dept_id",$deptId)->where("designation",$designId)->where("caption",$caption)->first();
	  
	   return view("Offerletter/getSalaryBreakup",compact('salaryDetails'));
   }
   
   public function getSalaryBreakupFinal(Request $request)
   {
	       $packageId = $request->packageId;
		   $monthlyPackage = $request->monthlyPackage;
		   $salaryDetails =  SalaryBreakup::where("id",$packageId)->first();
		   $labelName = $salaryDetails->label_name;
		   $percentange = $salaryDetails->percentange;
		   $labelNameArray = explode(",",$labelName);
		   $percentangeArray = explode(",",$percentange);
		   $breakUpArray = array();
		   for($i=0;$i<count($labelNameArray);$i++)
		   {
			   $percentangeSet = $percentangeArray[$i];
			   $percentangeSet1 =$percentangeSet*$monthlyPackage;
			   $percentangeSetFinal = $percentangeSet1/100;
			   $percentangeSetFinal = round($percentangeSetFinal,2);
			   $labelNameValue = $labelNameArray[$i];
			   $breakUpArray[$labelNameValue] = $percentangeSetFinal;
		   }
	      return view("Offerletter/getSalaryBreakupFinal",compact('breakUpArray'));
   }
   public function manageIncentive(Request $req)
   {
	   $incentiveBreakUpdetails  = IncentiveBreakup::where("status",1)->orderBy("id","DESC");
	   $filterList = array();
	   $filterList['capID'] = '';
	   if(!empty($req->session()->get('cap_id')))
			{
				$cap_id  =$req->session()->get('cap_id');
				$incentiveBreakUpdetails =  $incentiveBreakUpdetails->where("capId",$cap_id);
				$filterList['capID'] = $cap_id;
			}
		$incentiveBreakUpdetails =  $incentiveBreakUpdetails->get();
	   $salaryBreakupDetails = SalaryBreakup::where("status",1)->get();
	   
	   return view("Offerletter/manageIncentive",compact('incentiveBreakUpdetails','salaryBreakupDetails','filterList'));
   }
   public function addIncentiveStructure()
   {
	   $salaryBreakUpList  = SalaryBreakup::where("status",1)->get();
	   return view("Offerletter/addIncentiveStructure",compact('salaryBreakUpList'));
   }
   public function checkIncentivebreakup(Request $request)
   {
	   $capId = $request->capId;
	   $incentiveBreakupMod = IncentiveBreakup::where("capId",$capId)->where("status",1)->first();
		
	
	   if(empty($incentiveBreakupMod))
	   {
		   echo "Allow";
	   }
	   else
	   {
		   echo "Not Allow";
	   }
	   exit;
   }
   
   public function addIncentiveStructurePost(Request $request)
   {
	   $requestParameters = $request->input();
	   $incentiveMod = new IncentiveBreakup();
	   $incentiveMod->capId = $requestParameters['incentiveBreakup']['breakup_id'];
	   $incentiveMod->incentive_type = $requestParameters['incentiveBreakup']['incentive_type'];
	   $incentiveMod->from_inc = implode(",",$requestParameters['incentiveBreakup']['from_inc']);
	   $incentiveMod->to_inc = implode(",",$requestParameters['incentiveBreakup']['to_inc']);
	   $incentiveMod->values_final = implode(",",$requestParameters['incentiveBreakup']['values']);
	   $incentiveMod->status = 1;
	   $incentiveMod->save();
	   $request->session()->flash('message','Add Incentive Successfully.');
	   return redirect('manageIncentive');
	   
	   
   }
   
   public function viewIncentiveBreakup(Request $request)
   {
	   $incentiveID = $request->incentiveID;
	   $incentiveDetails = IncentiveBreakup::where("id",$incentiveID)->first();
	    return view("Offerletter/viewIncentiveBreakup",compact('incentiveDetails'));
	   
   }
   
   public function appliedFilterOnIncentiveBreakup(Request $request)
   {
				$selectedFilter = $request->input();
				
				$request->session()->put('cap_id',$selectedFilter['cap_id']);
				
				return redirect('manageIncentive');
   }
   public function resetIncentiveBreakupFilter(Request $request)
   {
				$request->session()->put('cap_id','');
				
				$request->session()->flash('message','Filters Reset Successfully.');
				return redirect('manageIncentive');
   }
   
   public function getIncentiveBreakup(Request $request)
   {
	   $deptId = $request->deptId;
	   $designId = $request->designId;
	   $caption = $request->cap;
	   $salaryDetails =  SalaryBreakup::where("dept_id",$deptId)->where("designation",$designId)->where("caption",$caption)->first();
	   $capId =  $salaryDetails->id;
	    $incentiveDetails = IncentiveBreakup::where("capId",$capId)->where("status",1)->first();
	   return view("Offerletter/getIncentiveBreakup",compact('incentiveDetails'));
	   
   }
   
   public function deleteSalaryBreakup(Request $request)
   {
	   $sBreakupId =  $request->sBreakupId;
	   $salaryBreakupUpdate =  SalaryBreakup::find($sBreakupId);
	   $salaryBreakupUpdate->status = 2;
	   $salaryBreakupUpdate->save();
	   
	   /*
	   *update incentive breakup
	   */
	   $incentiveMod = IncentiveBreakup::where("capId",$sBreakupId)->first();
	   if($incentiveMod != '')
	   {
	     $incentiveBreakupUpdate =  IncentiveBreakup::find($incentiveMod->id);
	   $incentiveBreakupUpdate->status = 2;
	   $incentiveBreakupUpdate->save();
	   }
		/*
	   *update incentive breakup
	   */
	   $request->session()->flash('message','salary struture and associated incentive deleted successfully.');
       return redirect('manageSalaryStructure');
   }
   
   public function deleteIncentiveBreakup(Request $request)
   {
	 
	   $iBreakupId =  $request->iBreakupId;
	   $IncentiveBreakupMod =  IncentiveBreakup::find($iBreakupId);
	   $IncentiveBreakupMod->status = 2;
	   $IncentiveBreakupMod->save();
	   $request->session()->flash('message','Incentive struture deleted successfully.');
       return redirect('manageIncentive');
   }
   
   public function generateCompanyId()
   {
	   $emp_details_array = Employee_details::where("status",1)->orderBy("id",'DESC')->get();
	 
	   foreach($emp_details_array as $emp_details)
	   {
	   $deptId = $emp_details->dept_id;
	   $empId = $emp_details->emp_id;
	   $workLocation = Employee_attribute::where("emp_id",$empId)->where("attribute_code","work_location")->first()->attribute_values;
	  // $design = Employee_attribute::where("emp_id",$empId)->where("attribute_code","DESIGN")->first()->attribute_values;
	   $departmentMod = Department::where("id",$deptId)->first();
       $departmentName = $departmentMod->department_name;
			$companyId = strtoupper($departmentName).'-'.strtoupper($workLocation).'-'.$empId;
			$empdetailsMod =  Employee_details::find($emp_details->id);
			
			$empdetailsMod->company_id=$companyId;
			
			$empdetailsMod->save();
			
	   }
	   echo "done";
	   exit;
	   
   }
   
   public function getDesignationOfSalaryBreakUp(Request $request)
   {
			  $deptId = $request->deptId;
			  $designationDetails =  Designation::where("status",1)->where("department_id",$deptId)->orderBy('id','DESC')->get();
			  return view("Offerletter/getDesignationOfSalaryBreakUp",compact('designationDetails'));
   }
}
