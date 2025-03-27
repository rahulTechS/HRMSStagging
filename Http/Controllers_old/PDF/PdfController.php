<?php

namespace App\Http\Controllers\PDF;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
use Session;


class PdfController extends Controller
{
private $fpdf;
	public function __construct()
	{
		
		
	}
	
	public function createPDF()
	{
		 $this->fpdf = new Fpdf;
        $this->fpdf->AddPage("L", ['100', '100']);
		$this->fpdf->SetFont('helvetica','',10);
        $this->fpdf->Text(10, 10, "Hello FPDF");       
         
        $this->fpdf->Output();
        exit;
	}
	
   
}
