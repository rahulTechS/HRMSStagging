<?php

namespace App\Http\Controllers\Visa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visa\visaType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Session;

class VisaTypeController extends Controller
{
    public function visaType()
	{
		
		return view("Visa/VisaType");
	}
	public function setOffSetForVisaType(Request $request)
			{
				$offset = $request->offset;
				$request->session()->put('offset_visa_filter',$offset);
				 return  redirect('visaTypeList');
			}
	public function visaTypeList(Request $request){
		
			if(!empty($request->session()->get('offset_visa_filter')))
				{
					$paginationValue = $request->session()->get('offset_visa_filter');
				}
				else
				{
					$paginationValue = 10;
				}
				//echo $paginationValue;exit;
				$whereraw='';
				$whereraw1 = '';
				 
				 $selectedFilter['visatitle'] = '';
				 $selectedFilter['Ofine'] = '';
				  
				
				
				if(!empty($request->session()->get('vistatitle_filter_inner_list')) && $request->session()->get('vistatitle_filter_inner_list') != 'All')
				{
					$title = $request->session()->get('vistatitle_filter_inner_list');
					 $selectedFilter['visatitle'] = $title;
					 if($whereraw == '')
					{
						$whereraw = 'title = "'.$title.'"';
					}
					else
					{
						$whereraw .= ' And title = "'.$title.'"';
					}
				}
				//echo $whereraw;exit;
				if(!empty($request->session()->get('oname_emp_filter_inner_list')) && $request->session()->get('oname_emp_filter_inner_list') != 'All')
				{
					$ofine = $request->session()->get('oname_emp_filter_inner_list');
					 $selectedFilter['Ofine'] = $ofine;
					 if($whereraw == '')
					{
						$whereraw = 'overstay_fine = "'.$ofine.'"';
					}
					else
					{
						$whereraw .= ' And overstay_fine = "'.$ofine.'"';
					}
				}
				
				
				if($whereraw != '')
				{
					//echo "h1";exit;
					$visaTypeListing = visaType::whereRaw($whereraw)->whereIn("status",array(1,2))->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCount = visaType::whereRaw($whereraw)->whereIn("status",array(1,2))->get()->count();				
				}
				else
				{
					//echo "h2";exit;
					$visaTypeListing = visaType::whereIn("status",array(1,2))->orderBy("id","DESC")->paginate($paginationValue);
					$reportsCount = visaType::whereIn("status",array(1,2))->get()->count();					
				}
				
				$visaTypeListing->setPath(config('app.url/VisaTypeList'));
				
				
				$visanameArray = array();
				if($whereraw == '')
				{
				$title = visaType::whereIn("status",array(1,2))->get();
				}
				else
				{
					
					$title = visaType::whereRaw($whereraw)->whereIn("status",array(1,2))->get();
					
				}
				
				foreach($title as $_title)
				{
					//echo $_f->first_name;exit;
					$visanameArray[$_title->title] = $_title->title;
				}
				//print_r();exit;
				$OfineArray = array();
				if($whereraw == '')
				{
				$ofine = visaType::whereIn("status",array(1,2))->get();
				}
				else
				{
					
					$ofine = visaType::whereRaw($whereraw)->whereIn("status",array(1,2))->get();
					
				}
				
				foreach($ofine as $_ofine)
				{
					//echo $_lname->last_name;exit;
					$OfineArray[$_ofine->overstay_fine] = $_ofine->overstay_fine;
				}
				
			
			return view("Visa/VisaTypeList",compact('visaTypeListing','paginationValue','reportsCount','OfineArray','visanameArray','selectedFilter'));
		}
		public function setFilterbyVName(Request $request)
		{
			$title = $request->title;
			$request->session()->put('vistatitle_filter_inner_list',$title);
			 return  redirect('visaTypeList');	
		}
		public function setFilterbyOName(Request $request)
		{
			$ofine = $request->ofine;
			$request->session()->put('oname_emp_filter_inner_list',$ofine);
			 return  redirect('visaTypeList');	
		}
		public function exportVisaReport(Request $request){
		$parameters = $request->input(); 
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Visa_Type_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:G1');
			$sheet->setCellValue('A1', 'Visa Type List - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('title'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('overstay_fine'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('updated_at'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('created_at'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
					
				 $misData = visaType::where("id",$sid)->first();
				 $indexCounter++;
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $misData->title)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, strtoupper($misData->overstay_fine))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, strtoupper($misData->updated_at))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, strtoupper($misData->created_at))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
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
				$writer->save(public_path('uploads/exportvisa/'.$filename));	
				echo $filename;
				exit;
		}
	
	
	
	
	public function addVisaType()
	{
		return view("Visa/addVisaType");
	}
	
	public function editVisaType($id=NULL)
	{
		$visaTypeListingdatas = visaType::where("id",$id)->first();
		return view("Visa/updateVisaType",compact('visaTypeListingdatas'));
	}
	
	public function addVisaTypePost(Request $rq)
	{
		$obj = new visaType();
		$obj->onboarding_status = $rq->input('onboarding_status');
		$obj->title = $rq->input('title');
		$obj->overstay_fine = $rq->input('overstay_fine');
		$obj->status = $rq->input('status');
		$obj->save();
		$rq->session()->flash('message','Visa Type Saved Successfully.');
		$response['code'] = '200';
		  $response['message'] = "Visa Type Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
        //return redirect('visaType');
	}
	public function updateVisaTypePost(Request $req)
	{
		$obj = visaType::find($req->input('id'));
		$obj->onboarding_status = $req->input('onboarding_status');
		$obj->title = $req->input('title');
		$obj->overstay_fine = $req->input('overstay_fine');
		$obj->status = $req->input('status');
		$obj->save();
		$req->session()->flash('message','Visa Type Updated Successfully.');
        //return redirect('visaType');
		$response['code'] = '200';
		  $response['message'] = "Visa Type Updated Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
		
	}
	
	public function deleteVisaType(Request $req)
	{
		$visaType_obj = visaType::find($req->id);
       
        $visaType_obj->status = 3;
       
        $visaType_obj->save();
        $req->session()->flash('message','Visa Type deleted Successfully.');
        //return redirect('VisaTypeList');
		$response['code'] = '200';
		  $response['message'] = "Visa Type deleted Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
	}
}
