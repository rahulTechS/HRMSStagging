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

use Session;


class IncentiveController extends Controller
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
	public function incentiveletterDeemCreditCard(Request $request)
	{
		/*
		*get Offer letter Details
		*start Code
		*/
		$documentCollectionId = $request->documentCollectId;
		$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		/*   echo '<pre>';
		 print_r($documentCollectionDetails);
		 exit;  */
		$empName = $documentCollectionDetails->emp_name;
	
		/*
		*@get passport number
		*@start code
		*/
		$documentCollectionMod = OfferletterDetails::where("document_id",$documentCollectionId)->first();
		/*
		*@get passport number
		*@end code
		*/
		$passportNo = $documentCollectionMod->passport_no;
		$mobileNo = $documentCollectionDetails->mobile_no;
		$email = $documentCollectionDetails->email;
		$created_at = date("d M Y",strtotime($documentCollectionDetails->created_at));
	    $productDetails = 'DEEM - Credit Card';
		if($request->location == 'DXB')
		{
			$location = 'Dubai';
		}
		else
		{
			$location = 'Abu Dhabi';
		}
		$areaName = $request->location;
		/*
		*get Offer letter Details
		*start Code
		*/
		
		$this->fpdf = new Fpdf;
		$this->fpdf->AddPage();
		$this->fpdf->Image(secure_asset('hrm/pdf/watermark-suranigroup.jpg'),45,100,-550);
		
		$this->PdfHeader($this->fpdf);
		$this->fpdf->SetDrawColor(0,0,0);
		
		$x = 10;
		$this->PdfFooter($this->fpdf,$x,208);
		$y=55; 
        
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x,$y,'Name:');
		$this->fpdf->Text($x+10.5,$y,$empName);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Date:');
		$this->fpdf->Text($x+135,$y,$created_at);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Passpost no:');
		$this->fpdf->Text($x+22,$y,$passportNo);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Location:');
		$this->fpdf->Text($x+135,$y,$location);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Mobile no:');
		$this->fpdf->Text($x+18,$y,$mobileNo);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Email Id:');
		$this->fpdf->Text($x+15,$y,$email);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Product:');
		$this->fpdf->Text($x+15,$y,$productDetails);
		
		$y = $y+15;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Strictly Private & Confidential');
		$y = $y+1;
		$this->fpdf->line($x,$y,$x+41,$y);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'1. Job Title:');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+18,$y,'The company shall employ the employee as an');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+85,$y,'"Relationship Officer - Credit Cards" (Deem -'.$areaName.')');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+162,$y,'under the spon-');
		$y = $y+5;
		$this->fpdf->Text($x+3,$y,'sorship of');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+18,$y,'Smart Union commercial Brokerage LLC.');
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'2.');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+3,$y,'The Incentive terms and condition is subject to changes as per compnay policy.');
		
		
	
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+17,$y,'The incentive terms and conditions of the appointment are as follows.');
		
		
		$y = $y+10;
		$this->fpdf->rect($x+25,$y,$x+125,40);
		$this->fpdf->line($x+70,$y,$x+70,$y+40);
		$this->fpdf->line($x+120,$y,$x+120,$y+40);
		$this->fpdf->SetFont('Arial','B',10);
		$this->fpdf->Text($x+39,$y+5,'Salary');
		$this->fpdf->line($x+39,$y+6,$x+50,$y+6);
		$this->fpdf->Text($x+89,$y+5,'Target');
		$this->fpdf->line($x+89,$y+6,$x+100,$y+6);
		$this->fpdf->Text($x+130,$y+5,'Commission');
		$this->fpdf->line($x+130,$y+6,$x+152,$y+6);
		$y = $y+10;
		$this->fpdf->SetFont('Arial','',10);
		$this->fpdf->line($x+25,$y,$x+160,$y);
		$this->fpdf->Text($x+37,$y+7.5,'AED 2,500');
		$this->fpdf->Text($x+88,$y+7.5,'4 Cards');
		$this->fpdf->Text($x+127,$y+7.5,'AED 800 Per Card');
		
		$y = $y+15;
		$this->fpdf->line($x+25,$y,$x+160,$y);
		$this->fpdf->line($x+25,$y,$x+160,$y);
		$this->fpdf->Text($x+37,$y+7.5,'AED 3,000');
		$this->fpdf->Text($x+88,$y+7.5,'5 Cards');
		$this->fpdf->Text($x+127,$y+7.5,'AED 800 Per Card');
		$y = $y+40;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Acceptance	');
		$this->fpdf->line($x,$y+1,$x+17,$y+1);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x,$y,'I');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+2,$y,'Name');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+12,$y,'understand and accept the incentive structure and agree to the terms and conditions as detailed here in.');
		
		$y = $y+15;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Signature of Employee');
		$y = $y+15;
	    $this->fpdf->Text($x,$y,'Date:');
		$this->PdfFooter($this->fpdf,$x,208);
		$this->fpdf->Output();
        exit;
	}
	
	
	public function incentiveletterDeemLoan(Request $request)
	{
		/*
		*get Offer letter Details
		*start Code
		*/
		$documentCollectionId = $request->documentCollectId;
		$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		/*  echo '<pre>';
		 print_r($documentCollectionDetails);
		 exit; */
		$empName = $documentCollectionDetails->emp_name;
		$passportNo = '--';
		$mobileNo = $documentCollectionDetails->mobile_no;
		$email = $documentCollectionDetails->email;
		$created_at = date("d M Y",strtotime($documentCollectionDetails->created_at));
	    $productDetails = 'DEEM - Credit Card';
		if($request->location == 'DXB')
		{
			$location = 'Dubai';
		}
		else
		{
			$location = 'Abu Dhabi';
		}
		$areaName = $request->location;
		/*
		*get Offer letter Details
		*start Code
		*/
		
		$this->fpdf = new Fpdf;
		$this->fpdf->AddPage();
		$this->fpdf->Image(secure_asset('hrm/pdf/watermark-suranigroup.jpg'),45,100,-550);
		
		$this->PdfHeader($this->fpdf);
		$this->fpdf->SetDrawColor(0,0,0);
		
		$x = 10;
		$this->PdfFooter($this->fpdf,$x,208);
		$y=55; 
        
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x,$y,'Name:');
		$this->fpdf->Text($x+10.5,$y,$empName);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Date:');
		$this->fpdf->Text($x+135,$y,$created_at);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Passpost no:');
		$this->fpdf->Text($x+22,$y,$passportNo);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Location:');
		$this->fpdf->Text($x+135,$y,$location);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Mobile no:');
		$this->fpdf->Text($x+18,$y,$mobileNo);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Email Id:');
		$this->fpdf->Text($x+15,$y,$email);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Product:');
		$this->fpdf->Text($x+15,$y,$productDetails);
		
		$y = $y+15;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Strictly Private & Confidential');
		$y = $y+1;
		$this->fpdf->line($x,$y,$x+41,$y);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'1. Job Title:');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+18,$y,'The company shall employ the employee as an');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+85,$y,'"Relationship Officer - Personal Loan" (Deem -'.$areaName.')');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+164,$y,'under the sp-');
		$y = $y+5;
		$this->fpdf->Text($x+3,$y,'onsorship of');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+22,$y,'Smart Union commercial Brokerage LLC.');
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'2.');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+3,$y,'The Incentive terms and condition is subject to changes as per compnay policy.');
		
		
	
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+17,$y,'The incentive terms and conditions of the appointment are as follows.');
		
		
		$y = $y+10;
		$this->fpdf->rect($x+25,$y,$x+125,70);
		$this->fpdf->line($x+50,$y,$x+50,$y+70);
		$this->fpdf->line($x+80,$y,$x+80,$y+70);
		$this->fpdf->line($x+120,$y,$x+120,$y+70);
		$this->fpdf->SetFont('Arial','B',10);
		$this->fpdf->Text($x+32,$y+5,'Salary');
		$this->fpdf->line($x+32,$y+6,$x+43,$y+6);
		$this->fpdf->Text($x+57.5,$y+5,'Target');
		$this->fpdf->line($x+57.5,$y+6,$x+68.5,$y+6);
		$this->fpdf->Text($x+95,$y+5,'Slab');
		$this->fpdf->line($x+95,$y+6,$x+102.5,$y+6);
		$this->fpdf->Text($x+130,$y+5,'Commission');
		$this->fpdf->line($x+130,$y+6,$x+152,$y+6);
		$y = $y+10;
		$this->fpdf->SetFont('Arial','',10);
		$this->fpdf->line($x+25,$y,$x+160,$y);
		$this->fpdf->Text($x+30,$y+6,'AED3,000');
		$this->fpdf->Text($x+56,$y+6,'AED 150k');
		$this->fpdf->Text($x+85,$y+6,'AED 150k - 200k');
		$this->fpdf->Text($x+135,$y+6,'2.50%');
		
		$y = $y+10;
		$this->fpdf->line($x+25,$y,$x+160,$y);
		$this->fpdf->line($x+25,$y,$x+160,$y);
		$this->fpdf->Text($x+85,$y+6,'200k+');
		$this->fpdf->Text($x+135,$y+6,'2.75%');
		$y = $y+10;
		$this->fpdf->line($x+25,$y,$x+160,$y);
		$this->fpdf->line($x+25,$y,$x+160,$y);
		$this->fpdf->Text($x+30,$y+6,'AED3,500');
		$this->fpdf->Text($x+56,$y+6,'AED 180k');
		$this->fpdf->Text($x+85,$y+6,'AED 180k-200k');
		$this->fpdf->Text($x+135,$y+6,'2.50%');
		$y = $y+10;
		$this->fpdf->line($x+25,$y,$x+160,$y);
		$this->fpdf->line($x+25,$y,$x+160,$y);
		$this->fpdf->Text($x+85,$y+6,'200k+');
		$this->fpdf->Text($x+135,$y+6,'2.75%');
		$y = $y+10;
		$this->fpdf->line($x+25,$y,$x+160,$y);
		$this->fpdf->line($x+25,$y,$x+160,$y);
		
		$y = $y+10;
		$this->fpdf->line($x+25,$y,$x+160,$y);
		$this->fpdf->line($x+25,$y,$x+160,$y);
		$this->fpdf->Text($x+30,$y+6,'AED4,000');
		$this->fpdf->Text($x+56,$y+6,'AED 200k');
		$this->fpdf->Text($x+85,$y+6,'AED 200k+');
		$this->fpdf->Text($x+135,$y+6,'2.75%');
		$y = $y+40;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Acceptance	');
		$this->fpdf->line($x,$y+1,$x+17,$y+1);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x,$y,'I');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+2,$y,'Name');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+12,$y,'understand and accept the incentive structure and agree to the terms and conditions as detailed here in.');
		
		$y = $y+15;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Signature of Employee');
		$y = $y+15;
	    $this->fpdf->Text($x,$y,'Date:');
		$this->PdfFooter($this->fpdf,$x,208);
		$this->fpdf->Output();
        exit;
	}
	
	public function incentiveletterENDBCreditCard(Request $request)
	{
		/*
		*get Offer letter Details
		*start Code
		*/
		$documentCollectionId = $request->documentCollectId;
		$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		/*  echo '<pre>';
		 print_r($documentCollectionDetails);
		 exit; */
		$empName = $documentCollectionDetails->emp_name;
		$passportNo = '--';
		$mobileNo = $documentCollectionDetails->mobile_no;
		$email = $documentCollectionDetails->email;
		$created_at = date("d M Y",strtotime($documentCollectionDetails->created_at));
	    $productDetails = 'ENDB - Credit Card';
		if($request->location == 'DXB')
		{
			$location = 'Dubai';
		}
		else
		{
			$location = 'Abu Dhabi';
		}
		$areaName = $request->location;
		/*
		*get Offer letter Details
		*start Code
		*/
		
		$this->fpdf = new Fpdf;
		$this->fpdf->AddPage();
		$this->fpdf->Image(secure_asset('hrm/pdf/watermark-suranigroup.jpg'),45,100,-550);
		
		$this->PdfHeader($this->fpdf);
		$this->fpdf->SetDrawColor(0,0,0);
		
		$x = 10;
		$this->PdfFooter($this->fpdf,$x,208);
		$y=55; 
        
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x,$y,'Name:');
		$this->fpdf->Text($x+10.5,$y,$empName);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Date:');
		$this->fpdf->Text($x+135,$y,$created_at);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Passpost no:');
		$this->fpdf->Text($x+22,$y,$passportNo);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Location:');
		$this->fpdf->Text($x+135,$y,$location);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Mobile no:');
		$this->fpdf->Text($x+18,$y,$mobileNo);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Email Id:');
		$this->fpdf->Text($x+15,$y,$email);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Product:');
		$this->fpdf->Text($x+15,$y,$productDetails);
		
		$y = $y+15;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Strictly Private & Confidential');
		$y = $y+1;
		$this->fpdf->line($x,$y,$x+41,$y);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'1. Job Title:');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+18,$y,'The company shall employ the employee as an');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+85,$y,'"Relationship Officer - Credit Card" (ENBD -'.$areaName.')');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+160,$y,'under the spo-');
		$y = $y+5;
		$this->fpdf->Text($x+3,$y,'nsorship of');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+20,$y,'Smart Union commercial Brokerage LLC.');
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'2.');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+3,$y,'The Incentive terms and condition is subject to changes as per compnay policy.');
		
		
	
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+17,$y,'The incentive terms and conditions of the appointment are as follows.');
		$y = $y+10;
		$this->fpdf->rect($x+15,$y,$x+25,10);
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+15.5,$y+5,'Fixed Salary Structure');
		$this->fpdf->line($x+50,$y+10,$x+50,$y+45);
		$this->fpdf->Text($x+25,$y+15,'Salary');
		$this->fpdf->Text($x+56,$y+15,'Justification');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+25,$y+25,'4000');
		$this->fpdf->Text($x+56,$y+25,'10 cards');
		
		$this->fpdf->Text($x+25,$y+34,'4500');
		$this->fpdf->Text($x+56,$y+34,'12 cards');
		
		$this->fpdf->Text($x+25,$y+42,'5000');
		$this->fpdf->Text($x+56,$y+42,'15 cards');
		
		$this->fpdf->line($x+15,$y+18,$x+85,$y+18);
		$this->fpdf->line($x+15,$y+28,$x+85,$y+28);
		$this->fpdf->line($x+15,$y+37,$x+85,$y+37);
		$y = $y+10;
		$this->fpdf->rect($x+15,$y,$x+60,35);
		$y = $y+45;
		$this->fpdf->SetFont('Arial','B',10);
		$this->fpdf->Text($x+15,$y,'Card Incentive Structure:');
		$y = $y+1;
		$this->fpdf->line($x+15,$y,$x+58,$y);
		$y = $y+5;
		$this->fpdf->rect($x+15,$y,$x+150,50);
		$this->fpdf->line($x+30,$y,$x+30,$y+50);
		$this->fpdf->line($x+65,$y,$x+65,$y+50);
		$this->fpdf->line($x+100,$y,$x+100,$y+50);
		$this->fpdf->line($x+125,$y,$x+125,$y+50);
		
		$this->fpdf->Text($x+18,$y+5,'AUH');
	
		$this->fpdf->Text($x+32,$y+5,'Card Commission');
	
		$this->fpdf->Text($x+67,$y+5,'Total Commission');
		
		$this->fpdf->Text($x+103,$y+5,'Prm Cards');
		$this->fpdf->Text($x+140,$y+5,'S.prm cards');
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',10);
		$this->fpdf->line($x+15,$y,$x+175,$y);
		$this->fpdf->Text($x+18,$y+5,'10');
		$this->fpdf->Text($x+44,$y+5,'250');
		$this->fpdf->Text($x+78,$y+5,'2500');
		$this->fpdf->Text($x+110,$y+5,'150');
		$this->fpdf->Text($x+146,$y+5,'300');
		
		$y = $y+7;
		$this->fpdf->line($x+15,$y,$x+175,$y);
		
		$this->fpdf->Text($x+18,$y+5,'13');
		$this->fpdf->Text($x+44,$y+5,'375');
		$this->fpdf->Text($x+78,$y+5,'4875');
		$this->fpdf->Text($x+110,$y+5,'150');
		$this->fpdf->Text($x+146,$y+5,'300');
		$y = $y+7;
		$this->fpdf->line($x+15,$y,$x+175,$y);
		
		$this->fpdf->Text($x+18,$y+5,'15');
		$this->fpdf->Text($x+44,$y+5,'400');
		$this->fpdf->Text($x+78,$y+5,'6000');
		$this->fpdf->Text($x+110,$y+5,'150');
		$this->fpdf->Text($x+146,$y+5,'300');
		$y = $y+7;
		$this->fpdf->line($x+15,$y,$x+175,$y);
	
		$this->fpdf->Text($x+18,$y+5,'20');
		$this->fpdf->Text($x+44,$y+5,'500');
		$this->fpdf->Text($x+78,$y+5,'10000');
		$this->fpdf->Text($x+110,$y+5,'150');
		$this->fpdf->Text($x+146,$y+5,'300');
		$y = $y+7;
		
		$this->fpdf->line($x+15,$y,$x+175,$y);
		$this->fpdf->Text($x+18,$y+5,'25');
		$this->fpdf->Text($x+44,$y+5,'600');
		$this->fpdf->Text($x+78,$y+5,'15000');
		$this->fpdf->Text($x+110,$y+5,'150');
		$this->fpdf->Text($x+146,$y+5,'300');
		$y = $y+7;
		$this->fpdf->line($x+15,$y,$x+175,$y);
		
	$this->fpdf->Text($x+18,$y+5,'30');
		$this->fpdf->Text($x+44,$y+5,'700');
		$this->fpdf->Text($x+78,$y+5,'21000');
		$this->fpdf->Text($x+110,$y+5,'150');
		$this->fpdf->Text($x+146,$y+5,'300');
		$y = $y+15;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Acceptance	');
		$this->fpdf->line($x,$y+1,$x+17,$y+1);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x,$y,'I');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+2,$y,'Name');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+12,$y,'understand and accept the incentive structure and agree to the terms and conditions as detailed here in.');
		$y = $y+10;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Signature of Employee');
		$y = $y+10;
	    $this->fpdf->Text($x,$y,'Date:');
		$this->PdfFooter($this->fpdf,$x,208);
		$this->fpdf->Output();
        exit;
	}
	
	public function incentiveletterENDBAutoLoan(Request $request)
	{
		/*
		*get Offer letter Details
		*start Code
		*/
		$documentCollectionId = $request->documentCollectId;
		$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		/*  echo '<pre>';
		 print_r($documentCollectionDetails);
		 exit; */
		$empName = $documentCollectionDetails->emp_name;
		$passportNo = '--';
		$mobileNo = $documentCollectionDetails->mobile_no;
		$email = $documentCollectionDetails->email;
		$created_at = date("d M Y",strtotime($documentCollectionDetails->created_at));
	    $productDetails = 'DEEM - Credit Card';
		if($request->location == 'DXB')
		{
			$location = 'Dubai';
		}
		else
		{
			$location = 'Abu Dhabi';
		}
		$areaName = $request->location;
		/*
		*get Offer letter Details
		*start Code
		*/
		
		$this->fpdf = new Fpdf;
		$this->fpdf->AddPage();
		$this->fpdf->Image(secure_asset('hrm/pdf/watermark-suranigroup.jpg'),45,100,-550);
		
		$this->PdfHeader($this->fpdf);
		$this->fpdf->SetDrawColor(0,0,0);
		
		$x = 10;
		$this->PdfFooter($this->fpdf,$x,208);
		$y=55; 
        
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x,$y,'Name:');
		$this->fpdf->Text($x+10.5,$y,$empName);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Date:');
		$this->fpdf->Text($x+135,$y,$created_at);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Passpost no:');
		$this->fpdf->Text($x+22,$y,$passportNo);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Location:');
		$this->fpdf->Text($x+135,$y,$location);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Mobile no:');
		$this->fpdf->Text($x+18,$y,$mobileNo);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Email Id:');
		$this->fpdf->Text($x+15,$y,$email);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Product:');
		$this->fpdf->Text($x+15,$y,$productDetails);
		
		$y = $y+15;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Strictly Private & Confidential');
		$y = $y+1;
		$this->fpdf->line($x,$y,$x+41,$y);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'1. Job Title:');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+18,$y,'The company shall employ the employee as an');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+85,$y,'"Relationship Officer - Auto Loan" (ENBD -'.$areaName.')');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+159,$y,'under the spon-');
		$y = $y+5;
		$this->fpdf->Text($x+3,$y,'sorship of');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+18,$y,'Smart Union commercial Brokerage LLC.');
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'2.');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+3,$y,'The Incentive terms and condition is subject to changes as per compnay policy.');
		
		
	
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+17,$y,'The incentive terms and conditions of the appointment are as follows.');
		$y = $y+10;
		$this->fpdf->rect($x+15,$y,$x+25,10);
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+15.5,$y+5,'Fixed Salary Structure');
		$this->fpdf->line($x+50,$y+10,$x+50,$y+45);
		$this->fpdf->Text($x+25,$y+15,'Salary');
		$this->fpdf->Text($x+56,$y+15,'Justification');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+25,$y+25,'AED.4000');
		$this->fpdf->Text($x+56,$y+25,'AED.500000');
		
		$this->fpdf->Text($x+25,$y+34,'AED.5000');
		$this->fpdf->Text($x+56,$y+34,'AED.600000');
		
		$this->fpdf->Text($x+25,$y+42,'AED.6000');
		$this->fpdf->Text($x+56,$y+42,'AED.700000');
		
		$this->fpdf->line($x+15,$y+18,$x+85,$y+18);
		$this->fpdf->line($x+15,$y+28,$x+85,$y+28);
		$this->fpdf->line($x+15,$y+37,$x+85,$y+37);
		$y = $y+10;
		$this->fpdf->rect($x+15,$y,$x+60,35);
		
		$y = $y+40;
		$this->fpdf->SetFont('Arial','B',10);
		$this->fpdf->rect($x+15,$y,$x+50,35);
		$this->fpdf->Text($x+27,$y+6,'Auto Loan Incentive');
		
		$this->fpdf->line($x+27,$y+7,$x+62,$y+7);
			$y = $y+12;	
			$this->fpdf->SetFont('Arial','',10);
		$this->fpdf->line($x+15,$y,$x+75,$y);
		$this->fpdf->Text($x+27,$y+6,'500K - 800K - 0.75%');
		$y = $y+12;	
		$this->fpdf->line($x+15,$y,$x+75,$y);
	$this->fpdf->Text($x+27,$y+6,'800k - 1 Mil - 1%');
		
		
		$y = $y+20;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Acceptance	');
		$this->fpdf->line($x,$y+1,$x+17,$y+1);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x,$y,'I');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+2,$y,'Name');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+12,$y,'understand and accept the incentive structure and agree to the terms and conditions as detailed here in.');
		$y = $y+20;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Signature of Employee');
		$y = $y+10;
	    $this->fpdf->Text($x,$y,'Date:');
		$this->PdfFooter($this->fpdf,$x,208);
		$this->fpdf->Output();
        exit;
	}
	
	public function incentiveletterENDBLoan(Request $request)
	{
		/*
		*get Offer letter Details
		*start Code
		*/
		$documentCollectionId = $request->documentCollectId;
		$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		/*  echo '<pre>';
		 print_r($documentCollectionDetails);
		 exit; */
		$empName = $documentCollectionDetails->emp_name;
		$passportNo = '--';
		$mobileNo = $documentCollectionDetails->mobile_no;
		$email = $documentCollectionDetails->email;
		$created_at = date("d M Y",strtotime($documentCollectionDetails->created_at));
	    $productDetails = 'DEEM - Credit Card';
		if($request->location == 'DXB')
		{
			$location = 'Dubai';
		}
		else
		{
			$location = 'Abu Dhabi';
		}
		$areaName = $request->location;
		/*
		*get Offer letter Details
		*start Code
		*/
		
		$this->fpdf = new Fpdf;
		$this->fpdf->AddPage();
		$this->fpdf->Image(secure_asset('hrm/pdf/watermark-suranigroup.jpg'),45,100,-550);
		
		$this->PdfHeader($this->fpdf);
		$this->fpdf->SetDrawColor(0,0,0);
		
		$x = 10;
		$this->PdfFooter($this->fpdf,$x,208);
		$y=55; 
        
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x,$y,'Name:');
		$this->fpdf->Text($x+10.5,$y,$empName);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Date:');
		$this->fpdf->Text($x+135,$y,$created_at);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Passpost no:');
		$this->fpdf->Text($x+22,$y,$passportNo);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Location:');
		$this->fpdf->Text($x+135,$y,$location);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Mobile no:');
		$this->fpdf->Text($x+18,$y,$mobileNo);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Email Id:');
		$this->fpdf->Text($x+15,$y,$email);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Product:');
		$this->fpdf->Text($x+15,$y,$productDetails);
		
		$y = $y+10;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Strictly Private & Confidential');
		$y = $y+1;
		$this->fpdf->line($x,$y,$x+45,$y);
		
		$y = $y+5;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'1. Job Title:');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+18,$y,'The company shall employ the employee as an');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+85,$y,'"Relationship Officer - Personal Loan" (ENBD -'.$areaName.')');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+165,$y,'under the');
		$y = $y+5;
		$this->fpdf->Text($x+3,$y,' sponsorship of');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+26,$y,'Smart Union commercial Brokerage LLC.');
		$y = $y+5;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'2.');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+3,$y,'The Incentive terms and condition is subject to changes as per compnay policy.');
		
		
	
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+17,$y,'The incentive terms and conditions of the appointment are as follows.');
		$y = $y+7;
		$this->fpdf->rect($x+15,$y,$x+25,10);
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+15.5,$y+5,'Loan Justification');
		$this->fpdf->line($x+50,$y+10,$x+50,$y+72);
		$this->fpdf->Text($x+25,$y+15,'Salary');
		$this->fpdf->Text($x+56,$y+15,'Justification');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+25,$y+25,'AED.4000');
		$this->fpdf->Text($x+56,$y+25,'AED.400000');
		
		$this->fpdf->Text($x+25,$y+34,'AED.4500');
		$this->fpdf->Text($x+56,$y+34,'AED.450000');
		
		$this->fpdf->Text($x+25,$y+42,'AED.5000');
		$this->fpdf->Text($x+56,$y+42,'AED.500000');
		
		
		$this->fpdf->Text($x+25,$y+51,'AED.5500');
		$this->fpdf->Text($x+56,$y+51,'AED.550000');
		
		
		$this->fpdf->Text($x+25,$y+60,'AED.6000');
		$this->fpdf->Text($x+56,$y+60,'AED.600000');
		
		$this->fpdf->Text($x+25,$y+69,'AED.7000');
		$this->fpdf->Text($x+56,$y+69,'AED.700000');
		
		$this->fpdf->line($x+15,$y+18,$x+85,$y+18);
		$this->fpdf->line($x+15,$y+28,$x+85,$y+28);
		$this->fpdf->line($x+15,$y+37,$x+85,$y+37);
		$this->fpdf->line($x+15,$y+46,$x+85,$y+46);
		$this->fpdf->line($x+15,$y+55,$x+85,$y+55);
		$this->fpdf->line($x+15,$y+64,$x+85,$y+64);
		$y = $y+10;
		$this->fpdf->rect($x+15,$y,$x+60,62);
		
		$y = $y+65;
		$this->fpdf->SetFont('Arial','B',10);
		$this->fpdf->rect($x+15,$y,$x+50,45);
		$this->fpdf->Text($x+27,$y+6,'Loan Incentive');
		
		$this->fpdf->line($x+27,$y+7,$x+58,$y+7);
			$y = $y+7;	
			$this->fpdf->SetFont('Arial','',10);
		$this->fpdf->line($x+15,$y,$x+75,$y);
		$this->fpdf->Text($x+27,$y+6,'Up-to 599K - 1%');
		$y = $y+7;	
		$this->fpdf->line($x+15,$y,$x+75,$y);
	$this->fpdf->Text($x+27,$y+6,'600K -999K - 1.1%');
	$y = $y+7;	
		$this->fpdf->line($x+15,$y,$x+75,$y);
	$this->fpdf->Text($x+27,$y+6,'1 Mil - 1,499,999 - 1.25%');
	$y = $y+7;	
		$this->fpdf->line($x+15,$y,$x+75,$y);
	$this->fpdf->Text($x+27,$y+6,'1.5Mil - 1,999,999 - 1.35%');
	$y = $y+7;	
		$this->fpdf->line($x+15,$y,$x+75,$y);
	$this->fpdf->Text($x+27,$y+6,'2 Mil and above - 1.5%');
		
		
		$y = $y+15;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Acceptance	');
		$this->fpdf->line($x,$y+1,$x+17,$y+1);
		$y = $y+5;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x,$y,'I');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+2,$y,'Name');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+12,$y,'understand and accept the incentive structure and agree to the terms and conditions as detailed here in.');
		$y = $y+10;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Signature of Employee');
		$y = $y+10;
	    $this->fpdf->Text($x,$y,'Date:');
		$this->PdfFooter($this->fpdf,$x,208);
		$this->fpdf->Output();
        exit;
	}
	
	public function incentiveletterENDBPosLoan(Request $request)
	{
		/*
		*get Offer letter Details
		*start Code
		*/
		$documentCollectionId = $request->documentCollectId;
		$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		/*  echo '<pre>';
		 print_r($documentCollectionDetails);
		 exit; */
		$empName = $documentCollectionDetails->emp_name;
		$passportNo = '--';
		$mobileNo = $documentCollectionDetails->mobile_no;
		$email = $documentCollectionDetails->email;
		$created_at = date("d M Y",strtotime($documentCollectionDetails->created_at));
	    $productDetails = 'DEEM - Credit Card';
		if($request->location == 'DXB')
		{
			$location = 'Dubai';
		}
		else
		{
			$location = 'Abu Dhabi';
		}
		$areaName = $request->location;
		/*
		*get Offer letter Details
		*start Code
		*/
		
		$this->fpdf = new Fpdf;
		$this->fpdf->AddPage();
		$this->fpdf->Image(secure_asset('hrm/pdf/watermark-suranigroup.jpg'),45,100,-550);
		
		$this->PdfHeader($this->fpdf);
		$this->fpdf->SetDrawColor(0,0,0);
		
		$x = 10;
		$this->PdfFooter($this->fpdf,$x,208);
		$y=55; 
        
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x,$y,'Name:');
		$this->fpdf->Text($x+10.5,$y,$empName);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Date:');
		$this->fpdf->Text($x+135,$y,$created_at);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Passpost no:');
		$this->fpdf->Text($x+22,$y,$passportNo);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Location:');
		$this->fpdf->Text($x+135,$y,$location);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Mobile no:');
		$this->fpdf->Text($x+18,$y,$mobileNo);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Email Id:');
		$this->fpdf->Text($x+15,$y,$email);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Product:');
		$this->fpdf->Text($x+15,$y,$productDetails);
		
		$y = $y+15;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Strictly Private & Confidential');
		$y = $y+1;
		$this->fpdf->line($x,$y,$x+45,$y);
		
		$y = $y+5;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'1. Job Title:');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+18,$y,'The company shall employ the employee as an');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+85,$y,'"Relationship Officer - POS Loan" (ENBD -'.$areaName.')');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+158,$y,'under the spo-');
		$y = $y+5;
		$this->fpdf->Text($x+3,$y,'nsorship of');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+20,$y,'Smart Union commercial Brokerage LLC.');
		$y = $y+5;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'2.');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+3,$y,'The Incentive terms and condition is subject to changes as per compnay policy.');
		
		
	
		$y = $y+7;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+17,$y,'The incentive terms and conditions of the appointment are as follows.');
		$y = $y+15;
		$this->fpdf->rect($x+15,$y,$x+26,10);
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+15.5,$y+5,'POS Loan Justification');
		$this->fpdf->line($x+51,$y+10,$x+51,$y+45);
		$this->fpdf->Text($x+25,$y+15,'Salary');
		$this->fpdf->Text($x+56,$y+15,'Justification');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+25,$y+25,'AED.6000');
		$this->fpdf->Text($x+56,$y+25,'AED.600000');
		
		$this->fpdf->Text($x+25,$y+34,'AED.7000');
		$this->fpdf->Text($x+56,$y+34,'AED.700000');
		
		$this->fpdf->Text($x+25,$y+42,'AED.8000');
		$this->fpdf->Text($x+56,$y+42,'AED.800000');
		
		
		
		
		$this->fpdf->line($x+15,$y+18,$x+85,$y+18);
		$this->fpdf->line($x+15,$y+28,$x+85,$y+28);
		$this->fpdf->line($x+15,$y+37,$x+85,$y+37);

		$y = $y+10;
		$this->fpdf->rect($x+15,$y,$x+60,35);
		
		$y = $y+40;
		$this->fpdf->SetFont('Arial','B',10);
		$this->fpdf->rect($x+15,$y,$x+50,36);
		$this->fpdf->Text($x+27,$y+6,'POS Loan Incentive');
		
		$this->fpdf->line($x+27,$y+7,$x+58,$y+7);
			$y = $y+7;	
			$this->fpdf->SetFont('Arial','',10);
		$this->fpdf->line($x+15,$y,$x+75,$y);
		$this->fpdf->Text($x+27,$y+6,'600K -999K - 1.1%');
		$y = $y+7;	
		$this->fpdf->line($x+15,$y,$x+75,$y);
	$this->fpdf->Text($x+27,$y+6,'1 Mil - 1,499,999 - 1.25%');
	$y = $y+7;	
		$this->fpdf->line($x+15,$y,$x+75,$y);
	$this->fpdf->Text($x+27,$y+6,'1.5Mil - 1,999,999 - 1.35%');
	$y = $y+7;	
		$this->fpdf->line($x+15,$y,$x+75,$y);
	$this->fpdf->Text($x+27,$y+6,'2 Mil and above - 1.5%');
	
		
		
		$y = $y+20;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Acceptance	');
		$this->fpdf->line($x,$y+1,$x+17,$y+1);
		$y = $y+10;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x,$y,'I');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+2,$y,'Name');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+12,$y,'understand and accept the incentive structure and agree to the terms and conditions as detailed here in.');
		$y = $y+10;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Signature of Employee');
		$y = $y+10;
	    $this->fpdf->Text($x,$y,'Date:');
		$this->PdfFooter($this->fpdf,$x,208);
		$this->fpdf->Output();
        exit;
	}
	
	
	public function incentiveletterMashreqCreditcard(Request $request)
	{
		/*
		*get Offer letter Details
		*start Code
		*/
		$documentCollectionId = $request->documentCollectId;
		$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		/*  echo '<pre>';
		 print_r($documentCollectionDetails);
		 exit; */
		$empName = $documentCollectionDetails->emp_name;
		$passportNo = '--';
		$mobileNo = $documentCollectionDetails->mobile_no;
		$email = $documentCollectionDetails->email;
		$created_at = date("d M Y",strtotime($documentCollectionDetails->created_at));
	    $productDetails = 'DEEM - Credit Card';
		if($request->location == 'DXB')
		{
			$location = 'Dubai';
		}
		else
		{
			$location = 'Abu Dhabi';
		}
		$areaName = $request->location;
		/*
		*get Offer letter Details
		*start Code
		*/
		
		$this->fpdf = new Fpdf;
		$this->fpdf->AddPage();
		$this->fpdf->Image(secure_asset('hrm/pdf/watermark-suranigroup.jpg'),45,100,-550);
		
		$this->PdfHeader($this->fpdf);
		$this->fpdf->SetDrawColor(0,0,0);
		
		$x = 10;
		$this->PdfFooter($this->fpdf,$x,208);
		$y=55; 
        
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x,$y,'Name:');
		$this->fpdf->Text($x+10.5,$y,$empName);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Date:');
		$this->fpdf->Text($x+135,$y,$created_at);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Passpost no:');
		$this->fpdf->Text($x+22,$y,$passportNo);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Location:');
		$this->fpdf->Text($x+135,$y,$location);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Mobile no:');
		$this->fpdf->Text($x+18,$y,$mobileNo);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Email Id:');
		$this->fpdf->Text($x+15,$y,$email);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Product:');
		$this->fpdf->Text($x+15,$y,$productDetails);
		
		$y = $y+15;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Strictly Private & Confidential');
		$y = $y+1;
		$this->fpdf->line($x,$y,$x+45,$y);
		
		$y = $y+5;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'1. Job Title:');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+18,$y,'The company shall employ the employee as an');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+85,$y,'"Relationship Officer - Credit Card" (Mashreq -'.$areaName.')');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+169,$y,'under the');
		$y = $y+5;
		$this->fpdf->Text($x+3,$y,'sponsorship of');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+25,$y,'Smart Union commercial Brokerage LLC.');
		$y = $y+10;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'2.');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+3,$y,'The Incentive terms and condition is subject to changes as per compnay policy.');
		
		
	
		$y = $y+15;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+17,$y,'The incentive terms and conditions of the appointment are as follows.');
		$y = $y+15;
		$this->fpdf->SetFont('Arial','B',10);
		$this->fpdf->line($x+95,$y+10,$x+95,$y+65);
		$this->fpdf->Text($x+55,$y+15,'Product');
		$this->fpdf->Text($x+110,$y+15,'Fixed Salary Points');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+55,$y+25,'Cashback < AED 7k Salary');
		$this->fpdf->Text($x+110,$y+25,'3.5');
		
		$this->fpdf->Text($x+55,$y+34,'Cashback > AED 7k Salary');
		$this->fpdf->Text($x+110,$y+34,'5');
		
		$this->fpdf->Text($x+55,$y+42,'Platinum < AED 10k Salary');
		$this->fpdf->Text($x+110,$y+42,'5');
		
		$this->fpdf->Text($x+55,$y+51,'Platinum > AED 10k Salary');
		$this->fpdf->Text($x+110,$y+51,'6');
		
		$this->fpdf->Text($x+55,$y+60,'Solitaire > AED 25k Salary');
		$this->fpdf->Text($x+110,$y+60,'8.5');
		
		
		$this->fpdf->line($x+45,$y+18,$x+145,$y+18);
		$this->fpdf->line($x+45,$y+28,$x+145,$y+28);
		$this->fpdf->line($x+45,$y+37,$x+145,$y+37);
		$this->fpdf->line($x+45,$y+46,$x+145,$y+46);
		$this->fpdf->line($x+45,$y+55,$x+145,$y+55);
		

		$y = $y+10;
		$this->fpdf->rect($x+45,$y,$x+90,55);
		$this->PdfFooter($this->fpdf,$x,208);
		$this->fpdf->AddPage();
		$this->fpdf->Image(secure_asset('hrm/pdf/watermark-suranigroup.jpg'),45,100,-550);
		$this->PdfHeader($this->fpdf);
		$this->fpdf->SetDrawColor(0,0,0);
		$x = 10;
		$y=55; 
		$y = $y+10;
	
		$this->fpdf->SetFont('Arial','B',10);
		$this->fpdf->line($x+65,$y+10,$x+65,$y+95);
		$this->fpdf->line($x+100,$y+10,$x+100,$y+95);
		$this->fpdf->Text($x+35,$y+15,'Fixed Salary');
		$this->fpdf->Text($x+70,$y+15,'Target Points');
		$this->fpdf->Text($x+110,$y+15,'Bonus Points');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+40,$y+25,'AED 2,500.00');
		$this->fpdf->Text($x+80,$y+25,'25');
		
		
		$this->fpdf->Text($x+40,$y+34,'AED 3,000.00');
		$this->fpdf->Text($x+80,$y+34,'35');
		$this->fpdf->Text($x+120,$y+34,'5');
		
		$this->fpdf->Text($x+40,$y+42,'AED 3,500.00');
		$this->fpdf->Text($x+80,$y+42,'40');
		$this->fpdf->Text($x+120,$y+42,'5');
		
		$this->fpdf->Text($x+40,$y+51,'AED 4,000.00');
		$this->fpdf->Text($x+80,$y+51,'50');
		$this->fpdf->Text($x+120,$y+51,'10');
		
		$this->fpdf->Text($x+40,$y+60,'AED 4,500.00');
		$this->fpdf->Text($x+80,$y+60,'60');
		$this->fpdf->Text($x+120,$y+60,'15');
		
		
		$this->fpdf->Text($x+40,$y+69,'AED 5,000.00');
		$this->fpdf->Text($x+80,$y+69,'70');
		$this->fpdf->Text($x+120,$y+69,'20');
		
		
		
		$this->fpdf->Text($x+40,$y+78,'AED 5,500.00');
		$this->fpdf->Text($x+80,$y+78,'80');
		$this->fpdf->Text($x+120,$y+78,'25');
		
		$this->fpdf->Text($x+40,$y+87,'AED 6,500.00');
		$this->fpdf->Text($x+80,$y+87,'100');
		$this->fpdf->Text($x+120,$y+87,'35');
		
		
		
		$this->fpdf->line($x+30,$y+18,$x+150,$y+18);
		$this->fpdf->line($x+30,$y+28,$x+150,$y+28);
		$this->fpdf->line($x+30,$y+37,$x+150,$y+37);
		$this->fpdf->line($x+30,$y+46,$x+150,$y+46);
		$this->fpdf->line($x+30,$y+55,$x+150,$y+55);
		$this->fpdf->line($x+30,$y+64,$x+150,$y+64);
		$this->fpdf->line($x+30,$y+73,$x+150,$y+73);
		$this->fpdf->line($x+30,$y+82,$x+150,$y+82);
		
		

		$y = $y+10;
		$this->fpdf->rect($x+30,$y,$x+110,85);
		
		
		$y = $y+120;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Acceptance	');
		$this->fpdf->line($x,$y+1,$x+17,$y+1);
		$y = $y+10;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x,$y,'I');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+2,$y,'Name');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+12,$y,'understand and accept the incentive structure and agree to the terms and conditions as detailed here in.');
		$y = $y+20;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Signature of Employee');
		$y = $y+10;
	    $this->fpdf->Text($x,$y,'Date:');
		$this->PdfFooter($this->fpdf,$x,208);
		$this->fpdf->Output();
        exit;
	}
	
	public function incentiveletterMashreqLoan(Request $request)
	{
		/*
		*get Offer letter Details
		*start Code
		*/
		$documentCollectionId = $request->documentCollectId;
		$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		/*  echo '<pre>';
		 print_r($documentCollectionDetails);
		 exit; */
		$empName = $documentCollectionDetails->emp_name;
		$passportNo = '--';
		$mobileNo = $documentCollectionDetails->mobile_no;
		$email = $documentCollectionDetails->email;
		$created_at = date("d M Y",strtotime($documentCollectionDetails->created_at));
	    $productDetails = 'DEEM - Credit Card';
		if($request->location == 'DXB')
		{
			$location = 'Dubai';
		}
		else
		{
			$location = 'Abu Dhabi';
		}
		$areaName = $request->location;
		/*
		*get Offer letter Details
		*start Code
		*/
		
		$this->fpdf = new Fpdf;
		$this->fpdf->AddPage();
		$this->fpdf->Image(secure_asset('hrm/pdf/watermark-suranigroup.jpg'),45,100,-550);
		
		$this->PdfHeader($this->fpdf);
		$this->fpdf->SetDrawColor(0,0,0);
		
		$x = 10;
		$this->PdfFooter($this->fpdf,$x,208);
		$y=55; 
        
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x,$y,'Name:');
		$this->fpdf->Text($x+10.5,$y,$empName);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Date:');
		$this->fpdf->Text($x+135,$y,$created_at);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Passpost no:');
		$this->fpdf->Text($x+22,$y,$passportNo);
		
		
		$this->fpdf->SetFont('Arial','B',9);	
		$this->fpdf->Text($x+120,$y,'Location:');
		$this->fpdf->Text($x+135,$y,$location);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Mobile no:');
		$this->fpdf->Text($x+18,$y,$mobileNo);
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Email Id:');
		$this->fpdf->Text($x+15,$y,$email);
		
		$y = $y+7;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Product:');
		$this->fpdf->Text($x+15,$y,$productDetails);
		
		$y = $y+15;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Strictly Private & Confidential');
		$y = $y+1;
		$this->fpdf->line($x,$y,$x+45,$y);
		
		$y = $y+5;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'1. Job Title:');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+18,$y,'The company shall employ the employee as an');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+85,$y,'"Relationship Officer - Personal Loan" (Mashreq -'.$areaName.')');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+169,$y,'under the');
		$y = $y+5;
		$this->fpdf->Text($x+3,$y,'sponsorship of');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+25,$y,'Smart Union commercial Brokerage LLC.');
		$y = $y+10;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'2.');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+3,$y,'The Incentive terms and condition is subject to changes as per compnay policy.');
		
		
	
		$y = $y+15;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+17,$y,'The incentive terms and conditions of the appointment are as follows.');
		$y = $y+15;
		$this->fpdf->SetFont('Arial','B',10);
		$this->fpdf->line($x+95,$y+10,$x+95,$y+75);
		$this->fpdf->Text($x+55,$y+15,'Fixed Salary');
		$this->fpdf->Text($x+110,$y+15,'Justification');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+55,$y+25,'AED 2500');
		$this->fpdf->Text($x+110,$y+25,'AED 250000');
		
		$this->fpdf->Text($x+55,$y+34,'AED 3000');
		$this->fpdf->Text($x+110,$y+34,'AED 300000');
		
		$this->fpdf->Text($x+55,$y+42,'AED 3500');
		$this->fpdf->Text($x+110,$y+42,'AED 350000');
		
		$this->fpdf->Text($x+55,$y+51,'AED 4000');
		$this->fpdf->Text($x+110,$y+51,'AED 450000');
		
		$this->fpdf->Text($x+55,$y+60,'AED 4500');
		$this->fpdf->Text($x+110,$y+60,'AED 550000');
		
		$this->fpdf->Text($x+55,$y+69,'AED 7500');
		$this->fpdf->Text($x+110,$y+69,'AED 1050000');
		
		
		$this->fpdf->line($x+45,$y+18,$x+145,$y+18);
		$this->fpdf->line($x+45,$y+28,$x+145,$y+28);
		$this->fpdf->line($x+45,$y+37,$x+145,$y+37);
		$this->fpdf->line($x+45,$y+46,$x+145,$y+46);
		$this->fpdf->line($x+45,$y+55,$x+145,$y+55);
		$this->fpdf->line($x+45,$y+64,$x+145,$y+64);

		$y = $y+10;
		$this->fpdf->rect($x+45,$y,$x+90,65);
		$this->PdfFooter($this->fpdf,$x,208);
		$this->fpdf->AddPage();
		$this->fpdf->Image(secure_asset('hrm/pdf/watermark-suranigroup.jpg'),45,100,-550);
		$this->PdfHeader($this->fpdf);
		$this->fpdf->SetDrawColor(0,0,0);
		$x = 10;
		$y=55; 
		$y = $y+10;
	
		$this->fpdf->SetFont('Arial','B',10);
		$this->fpdf->line($x+95,$y+10,$x+95,$y+65);
		$this->fpdf->Text($x+55,$y+15,'Beyond Target');
		$this->fpdf->Text($x+110,$y+15,'Commission');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+55,$y+25,'100% - 120%');
		$this->fpdf->Text($x+110,$y+25,'1.10%');
		
		$this->fpdf->Text($x+55,$y+34,'121 - 140 %');
		$this->fpdf->Text($x+110,$y+34,'1.25%');
		
		$this->fpdf->Text($x+55,$y+42,'141 - 160 %');
		$this->fpdf->Text($x+110,$y+42,'1.30%');
		
		$this->fpdf->Text($x+55,$y+51,'161 - 180 %');
		$this->fpdf->Text($x+110,$y+51,'1.35%');
		
		$this->fpdf->Text($x+55,$y+60,'180 % +');
		$this->fpdf->Text($x+110,$y+60,'1.50%');
		
		
		
		$this->fpdf->line($x+45,$y+18,$x+145,$y+18);
		$this->fpdf->line($x+45,$y+28,$x+145,$y+28);
		$this->fpdf->line($x+45,$y+37,$x+145,$y+37);
		$this->fpdf->line($x+45,$y+46,$x+145,$y+46);
		$this->fpdf->line($x+45,$y+55,$x+145,$y+55);
		

		$y = $y+10;
		$this->fpdf->rect($x+45,$y,$x+90,55);
		
		
		$y = $y+90;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Acceptance	');
		$this->fpdf->line($x,$y+1,$x+17,$y+1);
		$y = $y+10;
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x,$y,'I');
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x+2,$y,'Name');
		$this->fpdf->SetFont('Arial','',9);
		$this->fpdf->Text($x+12,$y,'understand and accept the incentive structure and agree to the terms and conditions as detailed here in.');
		$y = $y+10;
		$this->fpdf->SetFont('Arial','B',9);
		$this->fpdf->Text($x,$y,'Signature of Employee');
		$y = $y+10;
	    $this->fpdf->Text($x,$y,'Date:');
		$this->PdfFooter($this->fpdf,$x,208);
		$this->fpdf->Output();
        exit;
	}
	public function PdfFooter($pdf,$x,$y)
	{
		$y = $y+80;
		
		$pdf->SetFont('Arial','B',8);
		$pdf->SetTextColor(0,0,0);
		
		$pdf->Image('http://54.229.106.191/app/webroot/pdf/border.png',$x,$y,-140);
	}
	
}
