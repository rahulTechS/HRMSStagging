<?php
namespace App\Http\Controllers\Autoloan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AutoloanMIS\AutoloanMIS;
use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use App\Models\Employee\Employee_attribute;
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


use Session;
ini_set("max_execution_time", 0);
class ExportAutoloanController extends Controller
{
   
 
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

	protected function getColumnLetter( $number )
	{
		$prefix = '';
		$suffix = '';
		$prefNum = intval( $number/26 );
		if( $number > 25 ){
			$prefix = $this->getColumnLetter( $prefNum - 1 );
		}
		$suffix = chr( fmod( $number, 26 )+65 );
		return $prefix.$suffix;
	}



	/////////////////// Added on 7 May ///////////////////
	
 public function exportAutoloan(Request $request)
 {
	
			 $parameters = $request->input(); 
			/*  echo "<pre>";
			 print_r($parameters);
			 exit; */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Autoloan_MIS_Data_'.date("d-m-Y").rand(0,999).'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:U1');
			$sheet->setCellValue('A1', 'Autoloan MIS Data - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;

			$columnArray = array('team','emp_id','emp_name','login_date','tracker_id','customer_name','contact','loan','tenure','ro_name','dealer_private','make_and_model','private_dealer','car_value','license','visa','registere','stage','lpo','remark_1','resubmitted');

			//echo '<pre>';
			//print_r($columnArray);

			for($index=0;$index<=20;$index++)
			{
				$colm = $this->getColumnLetter($index).($indexCounter);
				
				$sheet->setCellValue($colm, strtoupper($columnArray[$index]))->getStyle($colm)->getAlignment()->setHorizontal('center')->setVertical('top');
				
			}

			
			
			
			$sn = 1;
			foreach ($selectedId as $sid) 
			{
				
				$mis =  AutoloanMIS::where("id",$sid)->first();	
				$indexCounter++; 
				 
				for($index=0;$index<=20;$index++)
				{
					$colm = $this->getColumnLetter($index).($indexCounter);						
					$columnName = $columnArray[$index];	
					$value = $mis->$columnName;
					
					if($value=='0000-00-00')
					{
						$value = '';
					}
					
					$sheet->setCellValue($colm, $value)->getStyle($colm)->getAlignment()->setHorizontal('center')->setVertical('top');
					
				}
				$sn++;
				
			}
			
			
			for($col = 'A'; $col !== 'U'; $col++) 
			{
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:U1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','U') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 }

 

	
}
