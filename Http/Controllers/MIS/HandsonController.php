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

class HandsonController extends Controller
{
  
			
			
			public function handsonReport(Request $request)
			{
				$whereraw = '';
			  $selectedFilter['submission_from'] = '';
			  $selectedFilter['submission_to'] = '';
			  $selectedFilter['report'] = '';
			 
			
				
				
			//echo $whereraw;exit;
				if(!empty($request->session()->get('offset_hardson')))
				{
					
					$paginationValue = $request->session()->get('offset_hardson');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if($whereraw != '')
				{
					$reports = HandsOnMisReport::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue)->onEachSide(0);
				}
				else
				{
				
					$reports = HandsOnMisReport::orderBy("id","DESC")->paginate($paginationValue)->onEachSide(0);
				}
				$reports->setPath(config('app.url/handsonReport'));
				
				
				
				
				
				if($whereraw != '')
				{
					
					$reportsCount = HandsOnMisReport::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = HandsOnMisReport::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				
				return view("MIS/Handson/handsonReport",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
			}
			
			public function handsonReportFinal(Request $request)
			{
				$whereraw = '';
			  $selectedFilter['submission_from'] = '';
			  $selectedFilter['submission_to'] = '';
			  $selectedFilter['report'] = '';
			 
			
				
				
			//echo $whereraw;exit;
				if(!empty($request->session()->get('offset_hardson_final')))
				{
					
					$paginationValue = $request->session()->get('offset_hardson_final');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if($whereraw != '')
				{
					$reports = HandsOnFinal::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue)->onEachSide(0);
				}
				else
				{
				
					$reports = HandsOnFinal::orderBy("id","DESC")->paginate($paginationValue)->onEachSide(0);
				}
				$reports->setPath(config('app.url/handsonReportFinal'));
				
				
				
				
				
				if($whereraw != '')
				{
					
					$reportsCount = HandsOnFinal::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = HandsOnFinal::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				
				return view("MIS/Handson/handsonReportFinal",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
			}
			
			
			public function handsonReportFinalTab(Request $request)
			{
				$whereraw = '';
			  $selectedFilter['submission_from'] = '';
			  $selectedFilter['submission_to'] = '';
			  $selectedFilter['report'] = '';
			 
			
				
				
			//echo $whereraw;exit;
				if(!empty($request->session()->get('offset_hardson_final_tab')))
				{
					
					$paginationValue = $request->session()->get('offset_hardson_final_tab');
				}
				else
				{
					$paginationValue = 10;
				}
				
				if($whereraw != '')
				{
					$reports = HandsOnFinalTab::orderBy("id","DESC")->whereRaw($whereraw)->paginate($paginationValue)->onEachSide(0);
				}
				else
				{
				
					$reports = HandsOnFinalTab::orderBy("id","DESC")->paginate($paginationValue)->onEachSide(0);
				}
				$reports->setPath(config('app.url/handsonReportFinalTab'));
				
				
				
				
				
				if($whereraw != '')
				{
					
					$reportsCount = HandsOnFinalTab::whereRaw($whereraw)->get()->count();
				}
				else
				{
					$reportsCount = HandsOnFinalTab::get()->count();
				}
				
				$employees = Employee_details::where("status",1)->get();
				
				return view("MIS/Handson/handsonReportFinalTab",compact('reports','reportsCount','paginationValue','employees','selectedFilter'));
			}
			
			public function setOffSetForHandsOn(Request $request)
			{
				$offset = $request->offset;
				$request->session()->put('offset_hardson',$offset);
				 return  redirect('handsonReport');
			}
			public function setOffSetForHandsOnFinal(Request $request)
			{
				$offset = $request->offset;
				$request->session()->put('offset_hardson_final',$offset);
				 return  redirect('handsonReportFinal');
			}
			
			public function setOffSetForHandsOnFinalTab(Request $request)
			{
				$offset = $request->offset;
				$request->session()->put('offset_hardson_final_tab',$offset);
				 return  redirect('handsonReportFinalTab');
			}
				
public function exportHandsonReport(Request $request)
{
	$parameters = $request->input(); 
	
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'hands_on_report_'.date("d-m-Y-h-i-s").'.csv';
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'";'); 
			$header = array();
			$header[] = strtoupper('APPLICATIONSID');
			$header[] = strtoupper('NAME');
			$header[] = strtoupper('FILERECEIPTDTTIME');
			$header[] = strtoupper('OFFER');
			$header[] = strtoupper('CURRENTACTIVITY');
			$header[] = strtoupper('DATEOFSOURCING');
			$header[] = strtoupper('SCHEME');
			$header[] = strtoupper('DME_RBE');
			$header[] = strtoupper('LASTREMARKSADDED');
			$header[] = strtoupper('EVSTATUS');
			$header[] = strtoupper('EVACTIONDATE');
			$header[] = strtoupper('CVSTATUS');
			$header[] = strtoupper('WCSTATUS');
			$header[] = strtoupper('LASTUPDATED');
			$header[] = strtoupper('PRI_SUPP_STANDALONE');
			$header[] = strtoupper('APPROVEDLIMIT');
			$header[] = strtoupper('P1CODE');
			
			$f = fopen(public_path('uploads/exportMIS/'.$filename), 'w');
			fputcsv($f, $header, ',');
			foreach ($selectedId as $sid) {
				 $misData = HandsOnMisReport::where("id",$sid)->first();
				$values = array();
				
				$values[] = $misData->APPLICATIONSID;
				$values[] = strtoupper($misData->NAME);
				$values[] = strtoupper($misData->FILERECEIPTDTTIME);
				$values[] = strtoupper($misData->OFFER);
				$values[] = strtoupper($misData->CURRENTACTIVITY);
				$values[] = strtoupper($misData->DATEOFSOURCING);
				$values[] = strtoupper($misData->SCHEME);
				$values[] = strtoupper($misData->DME_RBE);
				$values[] = strtoupper($misData->LASTREMARKSADDED);
				$values[] = strtoupper($misData->EVSTATUS);
				$values[] = strtoupper($misData->EVACTIONDATE);
				$values[] = strtoupper($misData->CVSTATUS);
				$values[] = strtoupper($misData->WCSTATUS);
				$values[] = strtoupper($misData->LASTUPDATED);
				$values[] = strtoupper($misData->PRI_SUPP_STANDALONE);
				$values[] = strtoupper($misData->APPROVEDLIMIT);
				$values[] = strtoupper($misData->P1CODE);
				
				fputcsv($f, $values, ',');
			}
			
	echo $filename;
	exit;
}


public function exportHandsonReportFinal(Request $request)
{
	$parameters = $request->input(); 
	
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'hands_on_report_'.date("d-m-Y-h-i-s").'.csv';
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'";'); 
			$header = array();
			$header[] = strtoupper('Login Date');
			$header[] = strtoupper('Application Type');
			$header[] = strtoupper('Tracker No');
			$header[] = strtoupper('Customer Name');
			$header[] = strtoupper('Mobile No');
			$header[] = strtoupper('Nationality');
			$header[] = strtoupper('Product Type');
			$header[] = strtoupper('SE Code');
			$header[] = strtoupper('Checklist');
			$header[] = strtoupper('Precalling Template');
			$header[] = strtoupper('Application Form');
			$header[] = strtoupper('V-Passport&V-Visa');
			$header[] = strtoupper('V-EIDA');
			$header[] = strtoupper('Income Proof');
			$header[] = strtoupper('Bank Statements');
			$header[] = strtoupper('Bank Name');
			$header[] = strtoupper('Security Check');
			$header[] = strtoupper('Cheque Number');
			$header[] = strtoupper('Amount');
			$header[] = strtoupper('Type Of Proof');
			$header[] = strtoupper('Remarks');
			
			$f = fopen(public_path('uploads/exportMIS/'.$filename), 'w');
			fputcsv($f, $header, ',');
			foreach ($selectedId as $sid) {
				 $misData = HandsOnFinal::where("id",$sid)->first();
				$values = array();
				
				$values[] = $misData->login_date;
				$values[] = strtoupper($misData->application_type);
				$values[] = strtoupper($misData->tracker_no);
				$values[] = strtoupper($misData->customer_name);
				$values[] = strtoupper($misData->mobile_no);
				$values[] = strtoupper($misData->nationality);
				$values[] = strtoupper($misData->product_type);
				$values[] = strtoupper($misData->se_code);
				$values[] = strtoupper($misData->Checklist);
				$values[] = strtoupper($misData->precalling_template);
				$values[] = strtoupper($misData->application_form);
				$values[] = strtoupper($misData->v_passport_v_visa);
				$values[] = strtoupper($misData->V_EIDA);
				$values[] = strtoupper($misData->income_proof);
				$values[] = strtoupper($misData->bank_statements);
				$values[] = strtoupper($misData->bank_name);
				$values[] = strtoupper($misData->security_check);
				$values[] = strtoupper($misData->cheque_number);
				$values[] = strtoupper($misData->amount);
				$values[] = strtoupper($misData->type_of_proof);
				$values[] = strtoupper($misData->remarks);
				
				fputcsv($f, $values, ',');
			}
			
	echo $filename;
	exit;
}

public function exportHandsonReportFinalTab(Request $request)
{
	$parameters = $request->input(); 
	
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'tab_hands_on_report_'.date("d-m-Y-h-i-s").'.csv';
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename="'.$filename.'";'); 
			$header = array();
			$header[] = strtoupper('Login Date');
			$header[] = strtoupper('Application Type');
			$header[] = strtoupper('Tracker No');
			$header[] = strtoupper('Customer Name');
			$header[] = strtoupper('Mobile No');
			$header[] = strtoupper('Nationality');
			$header[] = strtoupper('Product Type');
			$header[] = strtoupper('SE Code');
			$header[] = strtoupper('Checklist');
			$header[] = strtoupper('Precalling Template');
			$header[] = strtoupper('Application Form');
			$header[] = strtoupper('V-Passport&V-Visa');
			$header[] = strtoupper('V-EIDA');
			$header[] = strtoupper('Income Proof');
			$header[] = strtoupper('Bank Statements');
			$header[] = strtoupper('Bank Name');
			$header[] = strtoupper('Security Check');
			$header[] = strtoupper('Cheque Number');
			$header[] = strtoupper('Amount');
			$header[] = strtoupper('Type Of Proof');
			$header[] = strtoupper('Remarks');
			
			$f = fopen(public_path('uploads/exportMIS/'.$filename), 'w');
			fputcsv($f, $header, ',');
			foreach ($selectedId as $sid) {
				 $misData = HandsOnFinalTab::where("id",$sid)->first();
				$values = array();
				
				$values[] = $misData->login_date;
				$values[] = strtoupper($misData->application_type);
				$values[] = strtoupper($misData->tracker_no);
				$values[] = strtoupper($misData->customer_name);
				$values[] = strtoupper($misData->mobile_no);
				$values[] = strtoupper($misData->nationality);
				$values[] = strtoupper($misData->product_type);
				$values[] = strtoupper($misData->se_code);
				$values[] = strtoupper($misData->Checklist);
				$values[] = strtoupper($misData->precalling_template);
				$values[] = strtoupper($misData->application_form);
				$values[] = strtoupper($misData->v_passport_v_visa);
				$values[] = strtoupper($misData->V_EIDA);
				$values[] = strtoupper($misData->income_proof);
				$values[] = strtoupper($misData->bank_statements);
				$values[] = strtoupper($misData->bank_name);
				$values[] = strtoupper($misData->security_check);
				$values[] = strtoupper($misData->cheque_number);
				$values[] = strtoupper($misData->amount);
				$values[] = strtoupper($misData->type_of_proof);
				$values[] = strtoupper($misData->remarks);
				
				fputcsv($f, $values, ',');
			}
			
	echo $filename;
	exit;
}


public function updateHandsOnFinalReport(Request $request)
{
	$values = $request->values;
	$fieldName = $request->fieldName;
	$dataId = $request->dataId;
	$objUpdate = HandsOnFinal::find($dataId);
	if($fieldName == 'Checklist')
	{
		$objUpdate->Checklist = $values;
	}
	else if($fieldName == 'precalling_template')
	{
		$objUpdate->precalling_template = $values;
	}
	else if($fieldName == 'application_form')
	{
		$objUpdate->application_form = $values;
	}
	else if($fieldName == 'v_passport_v_visa')
	{
		$objUpdate->v_passport_v_visa = $values;
	}
	else if($fieldName == 'V_EIDA')
	{
		$objUpdate->V_EIDA = $values;
	}
	else if($fieldName == 'income_proof')
	{
		$objUpdate->income_proof = $values;
	}
	else if($fieldName == 'bank_statements')
	{
		$objUpdate->bank_statements = $values;
	}
	else if($fieldName == 'bank_name')
	{
		$objUpdate->bank_name = $values;
	}
	else if($fieldName == 'security_check')
	{
		$objUpdate->security_check = $values;
	}
	else if($fieldName == 'cheque_number')
	{
		$objUpdate->cheque_number = $values;
	}
	else if($fieldName == 'amount')
	{
		$objUpdate->amount = $values;
	}
	else if($fieldName == 'type_of_proof')
	{
		$objUpdate->type_of_proof = $values;
	}
	else if($fieldName == 'remark')
	{
		$objUpdate->remarks = $values;
	}
	else
	{
		
	}
	$objUpdate->save();
	echo "done";
	exit;
}


public function updateHandsOnFinalReportTab(Request $request)
{
	$values = $request->values;
	$fieldName = $request->fieldName;
	$dataId = $request->dataId;
	$objUpdate = HandsOnFinalTab::find($dataId);
	if($fieldName == 'Checklist')
	{
		$objUpdate->Checklist = $values;
	}
	else if($fieldName == 'precalling_template')
	{
		$objUpdate->precalling_template = $values;
	}
	else if($fieldName == 'application_form')
	{
		$objUpdate->application_form = $values;
	}
	else if($fieldName == 'v_passport_v_visa')
	{
		$objUpdate->v_passport_v_visa = $values;
	}
	else if($fieldName == 'V_EIDA')
	{
		$objUpdate->V_EIDA = $values;
	}
	else if($fieldName == 'income_proof')
	{
		$objUpdate->income_proof = $values;
	}
	else if($fieldName == 'bank_statements')
	{
		$objUpdate->bank_statements = $values;
	}
	else if($fieldName == 'bank_name')
	{
		$objUpdate->bank_name = $values;
	}
	else if($fieldName == 'security_check')
	{
		$objUpdate->security_check = $values;
	}
	else if($fieldName == 'cheque_number')
	{
		$objUpdate->cheque_number = $values;
	}
	else if($fieldName == 'amount')
	{
		$objUpdate->amount = $values;
	}
	else if($fieldName == 'type_of_proof')
	{
		$objUpdate->type_of_proof = $values;
	}
	else if($fieldName == 'remark')
	{
		$objUpdate->remarks = $values;
	}
	else
	{
		
	}
	$objUpdate->save();
	echo "done";
	exit;
}

}