<?php

namespace App\Http\Controllers\CronJob;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use App\Models\Company\Department;
use App\Models\Company\Product;
use App\Models\Recruiter\Designation;
use App\Models\Offerletter\SalaryBreakup;
use App\Models\Onboarding\DocumentCollectionDetails;
use App\Models\Onboarding\DocumentCollectionAttributes;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use App\Models\Onboarding\KycDocuments;
use App\Models\Onboarding\HiringSourceDetails;
use App\Models\Onboarding\RecruiterDetails;
use App\Models\Onboarding\VisaDetails;
use App\Models\Onboarding\DocumentCollectionBackout;
use App\Models\Onboarding\DocumentVisaStageStatus;
use App\Models\Onboarding\IncentiveLetterDetails;
use App\Models\Onboarding\VisaSortDate;
use Illuminate\Support\Facades\Validator;
use  App\Models\Attribute\AttributeType;
use App\Models\Offerletter\OfferletterDetails;
use App\Models\Visa\visaType;
use App\Models\Visa\VisaStage;
use App\Models\Visa\Visaprocess;
use App\Models\Visa\VisaPermission;
use App\Models\Onboarding\TrainingProcess;
use UserPermissionAuth;
use App\Models\Entry\Employee;
use App\Models\Employee\Employee_details;
use App\Models\Job\JobOpening;
use App\Models\Employee\Employee_attribute;
use  App\Models\Attribute\Attributes;
use App\Models\Logs\DocumentCollectionDetailsLog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Onboarding\DepartmentPermission;
use App\Models\InterviewProcess\InterviewProcess;
use App\Models\InterviewProcess\InterviewDetailsProcess;
use App\Models\EmpOffline\EmpOffline;
use App\Models\Employee\UpdateLocation;
use App\Models\SEPayout\WorkTimeRange;
use App\Models\JobFunction\JobFunction;
use App\Models\Onboarding\EmployeeIncrement;
use App\Models\Onboarding\EmployeeOnboardData;
use App\Models\Onboarding\EmployeeOnboardLogdata;
use App\Models\Dashboard\MashreqFinalMTD;
use App\Models\ChangeDepartment\AgentMappingDetails;
use App\Models\ChangeDepartment\AgentMappingAttendnace;
use App\Models\Onboarding\OnboardCandidateKyc;
use App\Models\Finance\InsuranceCensusList;
use App\Models\Finance\MashreqFinancePayoutFinal;
use App\Models\Finance\UpdateBasicSalary;
use App\Models\Finance\InsuranceNexusList;
use App\Models\Finance\VisaExpensesDetailsUpdated;
use App\Models\Finance\TLDetails;
use App\Models\Finance\SalesProcesser;
use App\Models\Employee\EmpAppAccess;
use App\Models\Bank\CBD\CBDBankMis;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\cronWork\MasterEmployeeMainland;
use App\Models\Onboarding\MonthWiseData;
use App\Models\Onboarding\OnbordMatchData;
use App\Models\Onboarding\OffbordMatchData;


class CronJobController extends Controller
{
public function updateStatusRecivedOfferLetter(){
	
	$docdata = DocumentCollectionDetails::get();
	if($docdata!=''){
		foreach($docdata As $_data){
			$documentValuescv = DocumentCollectionDetailsValues::where("document_collection_id",$_data->id)->where("attribute_code",14)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$_data->id)->where("attribute_code",15)->first();
			if(($documentValuescv!='' && $documentValuescv!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL)){
				$documentCollectionMod = DocumentCollectionDetails::find($_data->id);
				$documentCollectionMod->offer_letter_relased_status = 3;
				if($documentCollectionMod->save()){
					//echo $_data->id."<br>";
				}
			}
		}
	}
	echo "Data Update";
			
	}
public function updateStatusOfferLetterUpload(){
	
	$docdata = DocumentCollectionDetails::where("offer_letter_relased_status",3)->get();
	if($docdata!=''){
		foreach($docdata As $_data){
			$documentValuesoffer = DocumentCollectionDetailsValues::where("document_collection_id",$_data->id)->where("attribute_code",81)->first();
			
			if(($documentValuesoffer!='' && $documentValuesoffer!=NULL)){
				$documentCollectionMod = DocumentCollectionDetails::find($_data->id);
				$documentCollectionMod->offer_letter_relased_status = 2;
				if($documentCollectionMod->save()){
					//echo $_data->id."<br>";
				}
			}
		}
	}
	echo "Data Update";
			
	}
public function exportDataEXCEL(Request $request){
	$whereraw ='interviewer_name =13 AND interview_type = "final discussion" AND created_at > "2023-06-25 00:00:00" AND created_at < "2023-07-05 00:00:00"';
	$Collection = InterviewDetailsProcess::whereRaw($whereraw)->get();
		 
	         
			 
	        $filename = 'cond_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:K1');
			$sheet->setCellValue('A1', 'Cond List  - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Mobie no'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Email'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('job_opening'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Work Location'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Department'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Recruiter Name'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($Collection as $sid) {
				//echo $sid;
				 $misData =$docCollection = DocumentCollectionDetails::where("interview_id",$sid->interview_id)->first();
				 $empname=$misData->emp_name;
				 $mobile_no=$misData->mobile_no;
				 $email=$misData->email;
				 $job_opening=$misData->job_opening;
				 $location=$misData->location;
				 $department=$misData->department;
				 
				 $jobOpning=JobOpening::where("id",$job_opening)->first();
				if($jobOpning!=''){
				
				$jobname=$jobOpning->name;
					}
					else{
						$jobname='';
					}
				$Recruite =RecruiterDetails::where("id",$misData->recruiter_name)->first();
			  
				  if($recname != '')
				  {
					
				  return $recname->name;
				  }
				  else
				  {
				  return '';
				  }	
				 $indexCounter++; 	
				 $departmentMod = Department::where("id",$department)->first();
				 $deptname=$departmentMod->department_name;
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $empname)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mobile_no)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $email)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $jobname)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $location)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $deptname)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('H'.$indexCounter, $recname)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'H'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','H') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
		}
		public function UpdateApprovedByData(Request $request){
			$docCollection = DocumentCollectionDetails::get();
			foreach($docCollection as $_docCollection){
				if($_docCollection->interview_id!='' || $_docCollection->interview_id!=NULL){
					$data=InterviewDetailsProcess::where("interview_type","final discussion")->where("interview_id",$_docCollection->interview_id)->first();
					if($data!=''){
					$detailsObj = DocumentCollectionDetails::find($_docCollection->id);
					$detailsObj->interview_approved_by =$data->interviewer_name; 
					$detailsObj->save();
					}
				}
				
			}
		}
	public function UpdateVisadocumentData(Request $request){
		
		$docCollection = DocumentCollectionDetails::get();
		foreach($docCollection as $_docCollection){
			$visadetails=$_docCollection->current_visa_details;
			$id=$_docCollection->id;
			$documentCollectionMod = DocumentCollectionDetails::find($id);
			if(($_docCollection->current_visa_details!='' || $_docCollection->current_visa_details!=NULL) && $_docCollection->current_visa_status=="Inside Country"){
			//echo "h1";exit;
			if($visadetails=="Tourist Visa"){
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$documentValuesExistingVisa = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",71)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL) && ($documentValuesExistingVisa!='' && $documentValuesExistingVisa!=NULL)){
				$documentCollectionMod->visa_documents_status = 2;
				$documentCollectionMod->upload_visa_document_date = date("Y-m-d",strtotime($documentValuesphoto->created_at));
				$documentCollectionMod->save();
			}
			else{
				//return "Document Not Received";
			}
		}
		else if($visadetails=="Residence Visa"){
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL)){
				$documentCollectionMod->visa_documents_status = 2;
				$documentCollectionMod->upload_visa_document_date = date("Y-m-d",strtotime($documentValuesphoto->created_at));
				$documentCollectionMod->save();
			}
			else{
				//return "Document Not Received";
			}
		}
		else if($visadetails=="Individual Sponsor"){
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$SponsorDocPassport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",72)->first();
			$SponsorDocVisa = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",73)->first();
			$SponsorDocEmirates = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",74)->first();
			$SponsorNOC = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",75)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL) && ($SponsorDocPassport!='' && $SponsorDocPassport!=NULL) && ($SponsorDocVisa!='' && $SponsorDocVisa!=NULL) && ($SponsorDocEmirates!='' && $SponsorDocEmirates!=NULL) && ($SponsorNOC!='' && $SponsorNOC!=NULL)){
				$documentCollectionMod->visa_documents_status = 2;
				$documentCollectionMod->upload_visa_document_date = date("Y-m-d",strtotime($documentValuespasport->created_at));
				$documentCollectionMod->save();
			}
			else{
				//return "Document Not Received";
			}
		}
		else if($visadetails=="Residence Visa"){
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			$CompanyNOC = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",76)->first();
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL) && ($CompanyNOC!='' && $CompanyNOC!=NULL)){
				$documentCollectionMod->visa_documents_status = 2;
				$documentCollectionMod->upload_visa_document_date = date("Y-m-d",strtotime($documentValuesphoto->created_at));
				$documentCollectionMod->save();
			}
			else{
				//return "Document Not Received";
			}
		}
		}
		else{
			//echo "h2";exit;
			$documentValuesphoto = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",18)->first();
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id",$id)->where("attribute_code",15)->first();
			
			if(($documentValuesphoto!='' && $documentValuesphoto!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL)){
				$documentCollectionMod->visa_documents_status = 2;
				$documentCollectionMod->upload_visa_document_date = date("Y-m-d",strtotime($documentValuesphoto->created_at));
				$documentCollectionMod->save();
			}
		}
		}
	}
	
	public function updateEMPName(Request $request){
		$emp_details = Employee_details::get();
		foreach($emp_details as $_emp_details){
			$name=$_emp_details->first_name." ".$_emp_details->middle_name." ".$_emp_details->last_name;
			$empattributes = Employee_details::find($_emp_details->id);
			$empattributes->emp_name=$name;
			$empattributes->save();
		}
		echo "update";
	}
	public function updateBankCodePerEMP(Request $request){
		$emp_details = Employee_details::get();
		foreach($emp_details as $_emp_details){
		$empid=$_emp_details->emp_id;
		$attr = Employee_attribute::where('emp_id',$empid)->where("attribute_code",'source_code')->first();
		if($attr!=''){
			$empdetails =  Employee_details::find($_emp_details->id);
			$empdetails->source_code=$attr->attribute_values;
			$empdetails->save();
		}
		}
		echo "Updated";
	}
	public function UpdateSpecialSearch0(){
		$docCollection = DocumentCollectionDetails::get();
		foreach($docCollection as $_docCollection){
			
		$empdetails =  DocumentCollectionDetails::find($_docCollection->id);
		$empdetails->special_filter_status=0;
		$empdetails->save();
				
			
		}
	}
	public function UpdateSpecialSearch1(Request $request){
		$whereraw = 'visa_process_status IN (1,0,2)  And onboard_status = 2 ';
		$empattributes = DocumentCollectionDetails::whereRaw($whereraw)->get();
		/* echo "<pre>";
		print_r($empattributes);
		exit; */
		if(count($empattributes) >0){
			
			foreach($empattributes as $_empattributes){
			$docId=$_empattributes->id;
			
			$empdetails =  DocumentCollectionDetails::find($docId);
			$empdetails->special_filter_status=1;
			$empdetails->save();
			}
		}
		echo "Done";
		exit;
	}
	public function UpdateSpecialSearch2(Request $request){
		$whereraw = " visa_process_status = 2 And (visa_inprogress_date<'".date('Y-m-d', strtotime("-30 days"))."' OR visa_inprogress_date IS NULL) AND onboard_status = 1" ;
		$empattributes = DocumentCollectionDetails::whereRaw($whereraw)->get();
	/* 	echo date('Y-m-d', strtotime("-30 days"));
		
		count($empattributes);exit; */
		if($empattributes!=''){
			foreach($empattributes as $_empattributes){
			$docId=$_empattributes->id;
			$empdetails =  DocumentCollectionDetails::find($docId);
			$empdetails->visa_filter_status_30days=2;
			$empdetails->save();
			}
		}
		echo "Done";
	}
	public function UpdateSpecialSearch3(Request $request){
		$whereraw = " (visa_process_status IN (1,0) AND ok_visa = 2) And (visa_approved_date<'".date('Y-m-d', strtotime("-10 days"))."' OR `visa_approved_date` IS NULL)";
		$empattributes = DocumentCollectionDetails::whereRaw($whereraw)->get();
		
		if($empattributes!=''){
			foreach($empattributes as $_empattributes){
			$docId=$_empattributes->id;
			$empdetails =  DocumentCollectionDetails::find($docId);
			$empdetails->special_filter_status=3;
			$empdetails->save();
			}
		}
		echo "Done3";
		/* 
		echo "<pre>";
		print_R($empattributes);
		exit; */
		
	}
	public function UpdateSpecialSearch4(Request $request){
		$whereraw = " offer_letter_details_date IS NULL And offer_letter_onboarding_status=1 And created_at<'".date('Y-m-d', strtotime("-7 days"))."'" ;
		$empattributes = DocumentCollectionDetails::whereRaw($whereraw)->get();
		
		if($empattributes!=''){
			foreach($empattributes as $_empattributes){
			$docId=$_empattributes->id;
			$empdetails =  DocumentCollectionDetails::find($docId);
			$empdetails->special_filter_status=4;
			$empdetails->save();
			}
		}
		echo "Done4";
	}
	public function UpdateSpecialSearch5(Request $request){
		$whereraw =  "offer_letter_onboarding_status=1 And offer_letter_details_date<'".date('Y-m-d', strtotime("-7 days"))."'" ;
		$empattributes = DocumentCollectionDetails::whereRaw($whereraw)->get();
		
		if($empattributes!=''){
			foreach($empattributes as $_empattributes){
			$docId=$_empattributes->id;
			$empdetails =  DocumentCollectionDetails::find($docId);
			$empdetails->special_filter_status=5;
			$empdetails->save();
			}
		}
		echo "Done5";
	}
	public function UpdateSpecialSearch6(Request $request){
		$whereraw = " visa_process_status = 2 And (visa_inprogress_date<'".date('Y-m-d', strtotime("-60 days"))."' OR visa_inprogress_date IS NULL) AND onboard_status = 1" ;
		$empattributes = DocumentCollectionDetails::whereRaw($whereraw)->get();
		/* echo "<pre>";
		print_r($empattributes);
		exit; */
		if($empattributes!=''){
			foreach($empattributes as $_empattributes){
			$docId=$_empattributes->id;
			$empdetails =  DocumentCollectionDetails::find($docId);
			$empdetails->visa_filter_status_60days=2;
			$empdetails->save();
			}
		}
		echo "Done6";
	}
	public function UpdateSpecialSearch7(Request $request){
		$whereraw = " bgverification_response_date IS NULL And bgverification_initiated_date<'".date('Y-m-d', strtotime("-7 days"))."'" ;
		$empattributes = DocumentCollectionDetails::whereRaw($whereraw)->get();
		
		if($empattributes!=''){
			foreach($empattributes as $_empattributes){
			$docId=$_empattributes->id;
			$empdetails =  DocumentCollectionDetails::find($docId);
			$empdetails->special_filter_status=7;
			$empdetails->save();
			}
		}
		echo "Done6";
	}
	
	public function UpdateVisaStageSteps(Request $request){
		$empattributes = DocumentCollectionDetails::get();
		foreach($empattributes as $_data){
			 $documentdata = Visaprocess::where("document_id",$_data->id)->orderBy('id','DESC')->first();
			 if($documentdata !='' && $documentdata !=NULL){
				  $datastage = VisaStage::where("id",$documentdata->visa_stage)->where("visa_type",$documentdata->visa_type)->orderBy('id','DESC')->first();
				  if($datastage!=''){
						$empdetails =  DocumentCollectionDetails::find($_data->id);
						$empdetails->visa_stage_steps=$datastage->stage_type;
						$empdetails->save();
				  }
				 
			 }
		}
		echo "Update..";
		
	}
 public function udateOfferletterData(){
	 $docCollection = DocumentCollectionDetails::get();
		foreach($docCollection as $_docCollection){
			$documentValuespasport = DocumentCollectionDetailsValues::where("document_collection_id", $_docCollection->id)->where("attribute_code",15)->first();
			$documentValuescv = DocumentCollectionDetailsValues::where("document_collection_id",$_docCollection->id)->where("attribute_code",14)->first();
			
			
			if(($documentValuescv!='' && $documentValuescv!=NULL) && ($documentValuespasport!='' && $documentValuespasport!=NULL)){
				$empdetails =  DocumentCollectionDetails::find($_docCollection->id);
				$empdetails->offer_letter_details_date=date("Y-m-d",strtotime($documentValuespasport->created_at));
				$empdetails->save();
				
			}
		}
 }
 
 public function MasterSpecialFilterStatus(){
		$docCollection0 = DocumentCollectionDetails::get();
		foreach($docCollection0 as $_docCollection0){			
		$empdetails0 =  DocumentCollectionDetails::find($_docCollection0->id);
		$empdetails0->special_filter_status=0;
		$empdetails0->visa_filter_status_30days=0;
		$empdetails0->visa_filter_status_60days=0;
		$empdetails0->save();			
		}
		/* start Visa incomplete and on-boarding complete ==*/
		
		$whereraw1 = 'visa_process_status IN (1,0,2)  And onboard_status = 2 ';
		$empattributes1 = DocumentCollectionDetails::whereRaw($whereraw1)->get();
		if(count($empattributes1) >0){
			
			foreach($empattributes1 as $_empattributes1){
			$docId1=$_empattributes1->id;			
			$empdetails1 =  DocumentCollectionDetails::find($docId1);
			$empdetails1->special_filter_status=1;
			$empdetails1->save();
			}
		}
		
		/* Visa in process for more than 30 days and on-boarding incomplete ==*/
		$whereraw2 = " visa_process_status = 2 And (visa_inprogress_date<'".date('Y-m-d', strtotime("-30 days"))."' OR visa_inprogress_date IS NULL) AND onboard_status = 1" ;
		$empattributes2 = DocumentCollectionDetails::whereRaw($whereraw2)->get();
	
		if($empattributes2!=''){
			foreach($empattributes2 as $_empattributes2){
			$docId2=$_empattributes2->id;
			$empdetails2 =  DocumentCollectionDetails::find($docId2);
			$empdetails2->visa_filter_status_30days=2;
			$empdetails2->save();
			}
		}
		/* Visa approved and visa in-complete for over 10 days ==*/
		$whereraw3 = " (visa_process_status IN (1,0) AND ok_visa = 2) And (visa_approved_date<'".date('Y-m-d', strtotime("-10 days"))."' OR `visa_approved_date` IS NULL)";
		$empattributes3 = DocumentCollectionDetails::whereRaw($whereraw3)->get();
		
		if($empattributes3!=''){
			foreach($empattributes3 as $_empattributes3){
			$docId3=$_empattributes3->id;
			$empdetails3 =  DocumentCollectionDetails::find($docId3);
			$empdetails3->special_filter_status=3;
			$empdetails3->save();
			}
		}
		/* Offer Letter documents not received over 7 days ==*/
		$whereraw4 = " offer_letter_details_date IS NULL And offer_letter_onboarding_status=1 And created_at<'".date('Y-m-d', strtotime("-7 days"))."'" ;
		$empattributes4 = DocumentCollectionDetails::whereRaw($whereraw4)->get();
		
		if($empattributes4!=''){
			foreach($empattributes4 as $_empattributes4){
			$docId4=$_empattributes4->id;
			$empdetails4 =  DocumentCollectionDetails::find($docId4);
			$empdetails4->special_filter_status=4;
			$empdetails4->save();
			}
		}
		/* Signed offer letter not received over 7 days ==*/
		$whereraw5 =  "offer_letter_onboarding_status=1 And offer_letter_details_date<'".date('Y-m-d', strtotime("-7 days"))."'" ;
		$empattributes5 = DocumentCollectionDetails::whereRaw($whereraw5)->get();
		
		if($empattributes5!=''){
			foreach($empattributes5 as $_empattributes5){
			$docId5=$_empattributes5->id;
			$empdetails5 =  DocumentCollectionDetails::find($docId5);
			$empdetails5->special_filter_status=5;
			$empdetails5->save();
			}
		}
		/* Visa in process and not complete in 60 days ==*/
		$whereraw6 = " visa_process_status = 2 And (visa_inprogress_date<'".date('Y-m-d', strtotime("-60 days"))."' OR visa_inprogress_date IS NULL) AND onboard_status = 1" ;
		$empattributes6 = DocumentCollectionDetails::whereRaw($whereraw6)->get();
		
		if($empattributes6!=''){
			foreach($empattributes6 as $_empattributes6){
			$docId6=$_empattributes6->id;
			$empdetails6 =  DocumentCollectionDetails::find($docId6);
			$empdetails6->visa_filter_status_60days=2;
			$empdetails6->save();
			}
		}
		/* BGV initiated and not received in 7 days ==*/
		$whereraw7 = " bgverification_response_date IS NULL And bgverification_initiated_date<'".date('Y-m-d', strtotime("-7 days"))."'" ;
		$empattributes7 = DocumentCollectionDetails::whereRaw($whereraw7)->get();
		
		if($empattributes7!=''){
			foreach($empattributes7 as $_empattributes7){
			$docId7=$_empattributes7->id;
			$empdetails7 =  DocumentCollectionDetails::find($docId7);
			$empdetails7->special_filter_status=7;
			$empdetails7->save();
			}
		}
		echo "Done";
		
 }
 public function UpdateVisaStage(){
	 $documentColectionId = DocumentCollectionDetails::whereIn("visa_process_status",array(2,4))->get();
				
				foreach($documentColectionId as $doc){
					$visastage = Visaprocess::where("document_id",$doc->id)->orderBy('id','DESC')->first();					
					if($visastage!=''){
						$docId=$visastage->document_id;
						$visa_stage=$visastage->visa_stage;
						$empdetails =  DocumentCollectionDetails::find($docId);
						$empdetails->current_visa_stage=$visa_stage;
						$empdetails->save();
					}
					
				}
 }
 public function UpdateEVisaStage(){
	 $datastage = VisaStage::where("evisa",1)->orderBy('id','DESC')->get();
	 if($datastage!=''){
		 
		 foreach($datastage as $_stage){
			 $visasprocessData = Visaprocess::where("visa_stage",$_stage->id)->orderBy('id','DESC')->get();
			 //print_r($visasprocessData);
			 if(count($visasprocessData)>0){
				 foreach($visasprocessData as $_visaprocessdata){
					 $empdata=DocumentCollectionDetails::where("id",$_visaprocessdata->document_id)->first();
					 if($empdata!=''){
					 //echo $_visaprocessdata->document_id."<br>";
					  $empdetails =  DocumentCollectionDetails::find($_visaprocessdata->document_id);
						$empdetails->evisa_status=1;
						$empdetails->evisa_start_date=$_visaprocessdata->closing_date;
						$empdetails->save();
					 }
				 }
				 
			 }
			
		 }
	 }
	 	
				
 }
 public function updateTLPerEMPOffBoard(Request $request){
	 $offline=EmpOffline::get();
	 foreach($offline as $_offlinedata){
		$emp_details = Employee_details::where("emp_id",$_offlinedata->emp_id)->first(); 
		if($emp_details!=''){
			$tlid=$emp_details->tl_id;
			$empdetails=EmpOffline::find($_offlinedata->id);
			$empdetails->tl_se=$tlid;
			$empdetails->save();
		}
	 }
		
		echo "Updated";
	}
	public function VisaMOLUpdate(){
		$visasprocessData = Visaprocess::whereIn("visa_stage",array(17,37,42,57,60,75,91,107,138,141,144,147))->orderBy('id','DESC')->get();
		if(count($visasprocessData)>0){
				 foreach($visasprocessData as $_visaprocessdata){
					 $empdata=DocumentCollectionDetails::where("id",$_visaprocessdata->document_id)->first();
					 if($empdata!=''){
					 //echo $_visaprocessdata->document_id."<br>";
					  $empdetails =  DocumentCollectionDetails::find($_visaprocessdata->document_id);
						$empdetails->mol_date=$_visaprocessdata->closing_date;
						$empdetails->save();
					 }
				 }
				 
			 }
	}
	public function UpdateCV(){
		$docdata=DocumentCollectionDetails::get();
		foreach($docdata as $_data){
		$documentValuescv = DocumentCollectionDetailsValues::where("document_collection_id",$_data->id)->where("attribute_code",14)->first();
		
		if($documentValuescv!=''){
			
		$emp_details = Employee_details::where("document_collection_id",$_data->id)->first();
		if($emp_details!=''){
		$empAttrExist = Employee_attribute::where("emp_id",$emp_details->emp_id)->where("dept_id",$emp_details->dept_id)->where("attribute_code","CV")->first();
					if($empAttrExist != '')
					{
						$updateEmpAttr = Employee_attribute::find($empAttrExist->id);
						
					}
					else
					{
						$updateEmpAttr = new Employee_attribute();
					}
					$updateEmpAttr->dept_id = $emp_details->dept_id;
					$updateEmpAttr->emp_id = $emp_details->emp_id;
					$updateEmpAttr->attribute_code = 'CV';
					$updateEmpAttr->attribute_values = $documentValuescv->attribute_value;
					$updateEmpAttr->status = 1;
					$updateEmpAttr->save();		
		}
						
			
		}		
			
		}
		
	}
	public function UpdateRecruiterDetailsCat(){
		$docdata=DocumentCollectionDetails::get();
		foreach($docdata as $_data){
		$recuter=RecruiterDetails::where("id",$_data->recruiter_name)->first();	
		if($recuter!=''){
			$empdetails =  DocumentCollectionDetails::find($_data->id);
			$empdetails->recruiter_cat=$recuter->recruit_cat;
			$empdetails->save();
		}
		}
	}
	public function UpdateJobOpning(){
		$docdata=DocumentCollectionDetails::get();
		foreach($docdata as $_data){
		
		$emp_details = Employee_details::where("document_collection_id",$_data->id)->first();
		if($emp_details!=''){		
					$updateEmpAttr = Employee_details::find($emp_details->id);
						
	
					$updateEmpAttr->job_opening_id = $_data->job_opening;
					$updateEmpAttr->save();		
		}
						
			
		}		
			
		
		
	}
	public function UpdateDOJ(){
	$docdata=DocumentCollectionDetails::get();
		foreach($docdata as $_data){
		$documentValuescv = DocumentCollectionDetailsValues::where("document_collection_id",$_data->id)->where("attribute_code",83)->first();
		
		if($documentValuescv!=''){
			
		$emp_details = Employee_details::where("document_collection_id",$_data->id)->first();
		if($emp_details!=''){
		
						$updateEmpAttr = Employee_details::find($emp_details->id);
						
					
					$updateEmpAttr->doj =date("Y-m-d",strtotime($documentValuescv->attribute_value));
					$updateEmpAttr->save();		
		}
						
			
		}		
			
		}	
	}
	public function updateDOJDOCtable(){
	
	$docdata = DocumentCollectionDetails::get();
	if($docdata!=''){
		foreach($docdata As $_data){
			$documentValuescv=DocumentCollectionDetailsValues::where("document_collection_id",$_data->id)->where("attribute_code",83)->first();
			if($documentValuescv!='' && $documentValuescv!=NULL){
				$documentCollectionMod = DocumentCollectionDetails::find($_data->id);
				$documentCollectionMod->doj =date("Y-m-d",strtotime($documentValuescv->attribute_value));;
				$documentCollectionMod->save();
			}
		}
	}
	echo "Data Update";
			
	}
	
	
	
	
	
	
	
	public function exportDataEXCELAllEMP(Request $request){
	$Collection = Employee_details::where("offline_status",1)->skip(400)->take(155)->get();
		 
	         
			 
	        $filename = 'cond_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:U1');
			$sheet->setCellValue('A1', 'Cond List  - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('EMP Code'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('EMP Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Department'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Visa Title'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Basic MOL'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Other MOL'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Total Gross MOL'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Designation As per MOL'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('MOL Date of Joining'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Labour Card Number'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Visa UID Number'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Permenent Visa Number'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Labour Issue Date'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Labour Exp Date'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Visa Issue Date'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Visa Exp. Date'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Permenent Visa Number'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Emirates ID Number'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('Current Visa Stage'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('Onboarding Status'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($Collection as $sid) {
				//echo $sid;
				 
				 $empname=$sid->emp_name;
				 $empid=$sid->emp_id;
				 
				$basic_salary_mol = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','basic_salary_mol')->where('dept_id',$sid->dept_id)->first();
				 if(!empty($basic_salary_mol)){
				 $basicSalary=$basic_salary_mol->attribute_values;
				 }
				 else{
					 $basicSalary='';
				 }
				 $others_mol = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','others_mol')->where('dept_id',$sid->dept_id)->first();
				 if(!empty($others_mol)){
				 $othersmol=$others_mol->attribute_values;
				 }
				 else{
					 $othersmol='';
				 }
				 $total_gross_salary = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','total_gross_salary')->where('dept_id',$sid->dept_id)->first();
					 if(!empty($total_gross_salary)){
					 $totalgrosssalary=$total_gross_salary->attribute_values;
					 }
					 else{
						 $totalgrosssalary='';
					 }
				$PERMOL = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','PERMOL')->where('dept_id',$sid->dept_id)->first();
					 if(!empty($PERMOL)){
					 $PERMOLdata=$PERMOL->attribute_values;
					 }
					 else{
						 $PERMOLdata='';
					 }
					 $PERMOL = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','PERMOL')->where('dept_id',$sid->dept_id)->first();
					 if(!empty($PERMOL)){
					 $PERMOLdata=$PERMOL->attribute_values;
					 }
					 else{
						 $PERMOLdata='';
					 }
					$documentdatavisa = Visaprocess::where("document_id",$sid->document_collection_id)->orderBy('id','DESC')->first();
					if($documentdatavisa!=''){
						$visatype=visaType::where("id",$documentdatavisa->visa_type)->first();
						$visatitle=$visatype->title;
					}
					else{
						$visatitle='';
					}
				$empattributesMod = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','MOL_DOJ')->where('dept_id',$sid->dept_id)->first();
				 if(!empty($empattributesMod)){
				 $MOL_DOJ=date("d-M-Y",strtotime(str_replace("/","-",$empattributesMod->attribute_values)));
				 }
				 else{
					 $MOL_DOJ='';
				 }
				 $LC_NumberMod = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','LC_Number')->where('dept_id',$sid->dept_id)->first();
				 if(!empty($LC_NumberMod)){
				 $LC_Number=$LC_NumberMod->attribute_values;
				 }
				 else{
					 $LC_Number='';
				 }
				 $visa_uid_noMod = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','visa_uid_no')->where('dept_id',$sid->dept_id)->first();
				 if(!empty($visa_uid_noMod)){
				 $visa_uid_no=$visa_uid_noMod->attribute_values;
				 }
				 else{
					 $visa_uid_no='';
				 }
				 $PVISA_NUMBERMod = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','PVISA_NUMBER')->where('dept_id',$sid->dept_id)->first();
				 if(!empty($PVISA_NUMBERMod)){
				 $PVISA_NUMBER=$PVISA_NUMBERMod->attribute_values;
				 }
				 else{
					 $PVISA_NUMBER='';
				 }
				 $labour_issue_dateMod = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','labour_issue_date')->where('dept_id',$sid->dept_id)->first();
				 if(!empty($labour_issue_dateMod)){
				 $labour_issue_date=date("d-M-Y",strtotime(str_replace("/","-",$labour_issue_dateMod->attribute_values)));
				 }
				 else{
					 $labour_issue_date='';
				 }
				 $labour_expiry_dateMod = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','labour_expiry_date')->where('dept_id',$sid->dept_id)->first();
				 if(!empty($labour_expiry_dateMod)){
				 $labour_expiry_date=date("d-M-Y",strtotime(str_replace("/","-",$labour_expiry_dateMod->attribute_values)));
				 }
				 else{
					 $labour_expiry_date='';
				 }
				 $visa_issue_dateMod = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','visa_issue_date')->where('dept_id',$sid->dept_id)->first();
				 if(!empty($visa_issue_dateMod)){
				 $visa_issue_date=date("d-M-Y",strtotime(str_replace("/","-",$visa_issue_dateMod->attribute_values)));
				 }
				 else{
					 $visa_issue_date='';
				 }
				 $visa_expiry_dateMod = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','visa_expiry_date')->where('dept_id',$sid->dept_id)->first();
				 if(!empty($visa_expiry_dateMod)){
				 $visa_expiry_date=date("d-M-Y",strtotime(str_replace("/","-",$visa_expiry_dateMod->attribute_values)));
				 }
				 else{
					 $visa_expiry_date='';
				 }
				 $PVISA_NUMBERMod = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','PVISA_NUMBER')->where('dept_id',$sid->dept_id)->first();
				 if(!empty($PVISA_NUMBERMod)){
				 $PVISA_NUMBER=$PVISA_NUMBERMod->attribute_values;
				 }
				 else{
					 $PVISA_NUMBER='';
				 }
				 $emirates_id_noMod = Employee_attribute::where('emp_id',$sid->emp_id)->where('attribute_code','emirates_id_no')->where('dept_id',$sid->dept_id)->first();
				 if(!empty($emirates_id_noMod)){
				 $emirates_id_no=$emirates_id_noMod->attribute_values;
				 }
				 else{
					 $emirates_id_no='';
				 }
				 $documentdatavisastage = Visaprocess::where("document_id",$sid->document_collection_id)->orderBy('id','DESC')->first();
					if($documentdatavisastage!=''){
						$datastage = VisaStage::where("id",$documentdatavisastage->visa_stage)->where("visa_type",$documentdatavisastage->visa_type)->orderBy('id','DESC')->first();
					  $stage_type= $datastage->stage_type;
						
					}
					else{
						$stage_type='';
					}
					 if($sid->document_collection_id !=''){
					$onboard="complete";
					 }else{
						$onboard="incomplete";
					 }
				 $indexCounter++; 	
				 $departmentMod = Department::where("id",$sid->dept_id)->first();
				 $deptname=$departmentMod->department_name;
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $empid)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $empname)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $deptname)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $visatitle)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('F'.$indexCounter, $basicSalary)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $othersmol)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $totalgrosssalary)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('I'.$indexCounter, $PERMOLdata)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('J'.$indexCounter, $MOL_DOJ)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('K'.$indexCounter, $LC_Number)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('L'.$indexCounter, $visa_uid_no)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('M'.$indexCounter, $PVISA_NUMBER)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('N'.$indexCounter, $labour_issue_date)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('O'.$indexCounter, $labour_expiry_date)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('P'.$indexCounter, $visa_issue_date)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('Q'.$indexCounter, $visa_expiry_date)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('R'.$indexCounter, $PVISA_NUMBER)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('S'.$indexCounter, $emirates_id_no)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('T'.$indexCounter, $stage_type)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sheet->setCellValue('U'.$indexCounter, $onboard)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
				$sn++;
				
			}
			
			
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
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
		}
		public function UpdateStampingDeadline(Request $req){
			/*$datastage = VisaStage::where("status_change_stage",1)->orderBy('id','DESC')->get();
			 if($datastage!=''){
				 
				 foreach($datastage as $_stage){
					 $visasprocessData = Visaprocess::where("visa_stage",$_stage->id)->orderBy('id','DESC')->get();
					 //print_r($visasprocessData);
					 if(count($visasprocessData)>0){
						 foreach($visasprocessData as $_visaprocessdata){
							 $date = date ('d-m-Y' , strtotime($_visaprocessdata->closing_date));
							$newdate = strtotime ( '+60 days' , strtotime ( $date ) ) ;
							$newdate = date ( 'd-m-Y' , $newdate );
							 
							 $existDocument = DocumentCollectionDetailsValues::where("document_collection_id",$_visaprocessdata->document_id)->where("attribute_code",92)->first();
								if($existDocument == '')
								{
									$objDocument = new DocumentCollectionDetailsValues();
									$objDocument->document_collection_id = $_visaprocessdata->document_id;
									$objDocument->attribute_code = 92;
									$objDocument->attribute_value = $newdate;
									$objDocument->created_by=$req->session()->get('EmployeeId');
									$objDocument->save();
								}
								else
								{
								//$objDocument = new DocumentCollectionDetailsValues();	
								}
								
						 }
						 
					 }
					
				 }
			 }
			 echo "Done";
			 */
			 $docdata = DocumentCollectionDetails::get();
			if($docdata!=''){
			foreach($docdata As $_data){
			 $existDocument = DocumentCollectionDetailsValues::where("document_collection_id",$_data->id)->where("attribute_code",92)->first();
				if($existDocument != '')
				{
					
				 $empdetails =  DocumentCollectionDetails::find($_data->id);
					$empdetails->caption=date("Y-m-d",strtotime($existDocument->attribute_value));
					$empdetails->save();
				}
				}
			}
			/*$docdata = UpdateLocation::get();
			//print_r($docdata);exit;
			if($docdata!=''){
			foreach($docdata As $_data){
				$emp_details = Employee_details::where("emp_id",$_data->emp_id)->first();
				//print_r($emp_details );exit;
			 $empAttrExist = Employee_attribute::where("emp_id",$_data->emp_id)->where("attribute_code","work_location")->first();
					if($empAttrExist != '')
					{
						$empattributes = Employee_details::find($emp_details->id);
						$empattributes->work_location=$empAttrExist->attribute_values;
						$empattributes->save();
						
					}
					
				}
			}*/
			echo "done";
			
		}
		
public function calculateVintageEMPData()
			{
				
				$agentPayoutMod = Employee_details::get();
			       
				$vintageArray = array();
				foreach($agentPayoutMod as $empdata)
				{
					
						$dojEmp = $empdata->doj;
						//echo "hello";exit;
						
						if($dojEmp!='' && $dojEmp != NULL){
							
						$dojd=$dojEmp;	
						}
						else{
							
						$empDOJObj  = Employee_attribute::where("attribute_code","DOJ")->where('emp_id',$empdata->emp_id)->first();	
						
						if($empDOJObj!='' && $empDOJObj->attribute_values!=''){
							
							$dojd=$empDOJObj->attribute_values;
						}
						else{
							
						$dojd='';	
						}
						}
						
						if($dojd != '' && $dojd != NULL)
						{
							$doj = str_replace("/","-",$dojd);//exit;
							$date1 = date("Y-m-d",strtotime($doj));
							$date2 =  date("Y-m-d");
							$daysInterval = abs(strtotime($date2)-strtotime($date1))/ (60 * 60 * 24);
							$agentPUpdate = Employee_details::find($empdata->id);
							$agentPUpdate->vintage_days = $daysInterval;
							//$agentPUpdate->vintage_status = 2;
							$agentPUpdate->save();
							
						}
						else{
							$agentPUpdate = Employee_details::find($empdata->id);
							$agentPUpdate->vintage_days = NULL;
							//$agentPUpdate->vintage_status = 2;
							$agentPUpdate->save();
						}
						
					}								
					
				
		echo "done";
		exit;
			}
			
	public function UpdateTimeRangeEMPData(){
			$data=WorkTimeRange::get();
			foreach($data as $_time){
					$range=$_time->range;
					$rangedata=explode('-',$range);
					//print_r($rangedata);

					$whereraw='vintage_days >='.$rangedata[0].' and vintage_days <='.$rangedata[1].'';
					$PayoutData =Employee_details::whereRaw($whereraw)->get();
					foreach($PayoutData as $_newdata){
						$updateMod = Employee_details::find($_newdata->id);
						$updateMod->range_id=$_time->id;
						//$updateMod->range_status=2;
						$updateMod->save();
					}
					
				
			}
			echo "done";
		exit;
			}
			
	public function UpdteJobFunction(){
		$jobOpeningDetails = JobOpening::whereIn("status",array(1,2))->get();
		if($jobOpeningDetails!=''){
			
			foreach($jobOpeningDetails as $_jobs){
				$design=$_jobs->designation;
				$designationDetails =  Designation::where("status",1)->where("id",$design)->first();
				if($designationDetails!=''){
					$UpdateOBJ=JobOpening::find($_jobs->id);
					$UpdateOBJ->job_function=$designationDetails->job_function;
					$UpdateOBJ->save();
					
				}
				
			}
		}
		echo "Done";
		
	}		
			
public function UpdateOnboardEMPId(){
	$onboardstatusdata=DocumentCollectionDetails::where("visa_process_status",2)->where("onboard_status",1)->get();
		if($onboardstatusdata!=''){
		foreach($onboardstatusdata as $_data){
			$onboardempdata=EmployeeOnboardData::where("document_id",$_data->id)->first();
			$empincid=EmployeeIncrement::where("id",1)->first();
			if($onboardempdata!=''){
				$EMPID = $onboardempdata->emp_id;
			}
			else{
				$EMPID = $empincid->increment_id;
			}
			$EMPonDataOBJ=	new EmployeeOnboardData();
			$EMPonDataOBJ->emp_id=$EMPID;
			$EMPonDataOBJ->document_id=$_data->id;
			if($EMPonDataOBJ->save()){			
			$logonboard= new EmployeeOnboardLogdata();
			$logonboard->document_id=$_data->id;
			$logonboard->emp_id=$EMPID;
			$logonboard->status=1;
			if($logonboard->save()){
				$incOBJ=EmployeeIncrement::find(1);
				$incOBJ->increment_id=$EMPID+1;
				$incOBJ->save();
			}
			}
			
		}	
		
		
		}		
echo "Updated";		
}				

public function calculateVintageAgentMTD()
			{
				/* echo "work";
				exit; */
				//$agentPayoutMod = AgentPayoutMashreq::where("match_employee",1)->get();
				//$agentPayoutMod = AgentPayoutDIB::where("employee_id_status",1)->get();
				$agentPayoutMod =MashreqFinalMTD::whereNull('vintage_status')->get();
				/*  echo '<pre>';
				print_r($agentPayoutMod);
				exit;    */
				$vintageArray = array();
				foreach($agentPayoutMod as $payout)
				{
					$employee_id = trim($payout->emp_id);
					if($employee_id != '' && $employee_id != NULL)
					{
						$employeeData = Employee_details::where("emp_id",$employee_id)->first();
						if($employeeData != '')
						{
							$empId = $employeeData->emp_id;
							$deptId = $employeeData->dept_id;
							$empAttr = Employee_attribute::where("emp_id",$empId)->where("attribute_code","DOJ")->first();
							if($empAttr != '')
							{
								$salesTimeValue = str_replace("/","-",$payout->dateofdisbursal);
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);
									
									$date1 = date("Y-m-d",strtotime($doj));
									$daysInterval = abs(strtotime($salesTimeValue)-strtotime($date1))/ (60 * 60 * 24);
									$agentPUpdate = MashreqFinalMTD::find($payout->id);
									$agentPUpdate->vintage = $daysInterval;
									$agentPUpdate->doj = $date1;
									$agentPUpdate->vintage_status = 1;
									//$agentPUpdate->match_employee = 2;
									$agentPUpdate->save();
									
								}
							}								
							
						}
					}
					
				}
				echo "done";
				exit;
			}
public function UpdateTimeRangeMTD(){
			$data=WorkTimeRange::get();
			foreach($data as $_time){
					$range=$_time->range;
					$rangedata=explode('-',$range);
					//print_r($rangedata);

					$whereraw='vintage >='.$rangedata[0].' and vintage <='.$rangedata[1].'';
					$PayoutData =MashreqFinalMTD::whereRaw($whereraw)->where("vintage_status",1)->get();
					foreach($PayoutData as $_newdata){
						$updateMod = MashreqFinalMTD::find($_newdata->id);
						$updateMod->range_id=$_time->id;
						$updateMod->vintage_status = 2;
						$updateMod->save();
					}
					
				
			}
			
			}
public function ChangeVisaStageSteps(Request $request){
		$empattributes = DocumentCollectionDetails::whereIn("visa_process_status",array(2,4))->get();
		foreach($empattributes as $_data){
			 $documentdata = Visaprocess::where("document_id",$_data->id)->get();
			 if($documentdata !='' && $documentdata !=NULL){
				 $stagearray=array();
				 foreach($documentdata as $_documentdata){
					$datastage = VisaStage::where("id",$_documentdata->visa_stage)->where("visa_type",$_documentdata->visa_type)->first();
						if($datastage!=''){
							if($datastage->stage_type=="Stage2"){
							$empdetails =  DocumentCollectionDetails::find($_data->id);
							$empdetails->visa_stage_steps=$datastage->stage_type;
							$empdetails->save();
							}
						}
				 }
				  
				  
				 
			 }
		}
		echo "Update..";
		
	}
public function exportVisaStage(){
			$Collection = VisaStage::where("status",1)->get();
		 
	         
			 
	        $filename = 'visastage_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:D1');
			$sheet->setCellValue('A1', 'Cond List  - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('visa type'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Stage Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('stage_type'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sn = 1;
			foreach ($Collection as $sid) {
				//echo $sid;
				 
				 $stagename=$sid->stage_name;
				 $visatype=visaType::where("id",$sid->visa_type)->first();
				 if($visatype!=''){
						
						$visatitle=$visatype->title;
					}
					else{
						$visatitle='';
					}
					$stage_type=$sid->stage_type;
				 
				 $indexCounter++; 	
				 
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $visatitle)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $stagename)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $stage_type)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'D'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:D1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','D') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;	
}
public function UpdateempIdDocumentcollection(){
	$onboardstatusdata=EmployeeOnboardData::get();
		if($onboardstatusdata!=''){
		foreach($onboardstatusdata as $_data){
			$existDocument = DocumentCollectionDetails::where("id",$_data->document_id)->first();
			if($existDocument!=''){
			//echo $_data->emp_id;exit;
				$empdetails =  DocumentCollectionDetails::find($existDocument->id);
				$empdetails->employee_id=$_data->emp_id;
				$empdetails->save();
				}
		}
			}
			
			
		
		
		
			
echo "Updated";
}
		public function UpdateStampingDeadlineData(Request $req){
			
			$datastage = VisaStage::whereIn("id",array(25,49,82,98,114,130,240))->orderBy('id','DESC')->get();
			 if($datastage!=''){
				 
				 foreach($datastage as $_stage){
					 $visasprocessData = Visaprocess::where("visa_stage",$_stage->id)->orderBy('id','DESC')->get();
					 //print_r($visasprocessData);
					 if(count($visasprocessData)>0){
						 foreach($visasprocessData as $_visaprocessdata){
							 $date = date ('d-m-Y' , strtotime($_visaprocessdata->closing_date));
							$newdate = strtotime ( '+60 days' , strtotime ( $date ) ) ;
							$newdate = date ( 'd-m-Y' , $newdate );
							$visatype=visaType::where("id",$_visaprocessdata->visa_type)->first();
							 $empdetailsdata =  DocumentCollectionDetails::where("id",$_visaprocessdata->document_id)->first();
							 if($empdetailsdata!=''){
								 if($empdetailsdata->current_visa_status=="Inside Country" && $visatype->behavior==1 ||$visatype->behavior==2 || $visatype->behavior==3){
										$_documentCollectionModcaption = DocumentCollectionDetails::find($empdetailsdata->id);
										$_documentCollectionModcaption->caption =date("Y-m-d",strtotime($newdate));
										$_documentCollectionModcaption->stamping_deadline =date("Y-m-d",strtotime($newdate));
										$_documentCollectionModcaption->save();
								 }
								 
								
							 }	
						 }
						 
					 }
					
				 }
			 }
			 //update outside
			/* $datastage = VisaStage::whereIn("id",array(24,48,81,97,113,129,241))->orderBy('id','DESC')->get();
			 if($datastage!=''){
				 
				 foreach($datastage as $_stage){
					 $visasprocessData = Visaprocess::where("visa_stage",$_stage->id)->orderBy('id','DESC')->get();
					 //print_r($visasprocessData);
					 if(count($visasprocessData)>0){
						 foreach($visasprocessData as $_visaprocessdata){
							 $date = date ('d-m-Y' , strtotime($_visaprocessdata->closing_date));
							$newdate = strtotime ( '+60 days' , strtotime ( $date ) ) ;
							$newdate = date ( 'd-m-Y' , $newdate );
							
							
							 $empdetailsdata =  DocumentCollectionDetails::where("id",$_visaprocessdata->document_id)->first();
							 if($empdetailsdata!=''){
								 if($empdetailsdata->current_visa_status=="Outside Country"){
								 
										$_documentCollectionModcaption = DocumentCollectionDetails::find($empdetailsdata->id);
										//$_documentCollectionModcaption->caption =date("Y-m-d",strtotime($newdate));
										$_documentCollectionModcaption->entry_date =date("Y-m-d",strtotime($newdate));
										//$_documentCollectionModcaption->stamping_deadline =date("Y-m-d",strtotime($newdate));
										$_documentCollectionModcaption->save();	
								 }
								 
								
							 }	
						 }
						 
					 }
					
				 }
			 }*/
			  /*$datastage = DocumentCollectionDetails::get();
			 if($datastage!=''){
				 
				 foreach($datastage as $_stage){
					 if($_stage->current_visa_status=="Outside Country"){
						 $Documentdata = DocumentCollectionDetailsValues::where("document_collection_id",$_stage->id)->where("attribute_code",83)->first(); 
						if($Documentdata != '')
						   {
							   $date = date ('d-m-Y' , strtotime($Documentdata->attribute_value));
								$newdate = strtotime ( '+50 days' , strtotime ( $date ) ) ;
								$newdate = date ( 'd-m-Y' , $newdate );
								$_documentCollectionModcaption = DocumentCollectionDetails::find($_stage->id);
								$_documentCollectionModcaption->caption =date("Y-m-d",strtotime($newdate));
								//$_documentCollectionModcaption->entry_date =date("Y-m-d",strtotime($newdate));
								$_documentCollectionModcaption->stamping_deadline =date("Y-m-d",strtotime($newdate));
								$_documentCollectionModcaption->save();	
								
						   }
						   else
						   {
								$_documentCollectionModcaption = DocumentCollectionDetails::find($_stage->id);
								$_documentCollectionModcaption->caption =NULL;
								//$_documentCollectionModcaption->entry_date =date("Y-m-d",strtotime($newdate));
								$_documentCollectionModcaption->stamping_deadline =NULL;
								$_documentCollectionModcaption->save();	
						   }
					 }
					 
					
				 }
			 }*/
			 
			 
			 echo "Done";
			 
			
		}
public function UpdateVisaSortDate(){
		$todayDate = date('Y-m-d');

		$visasortdate = VisaSortDate::where('date',$todayDate)->orderBy('id','desc')->first();

		//return $empAttendanceCron;
		
		if($visasortdate)
		{
			return response()->json(['success'=>'Cron already Run for '.$todayDate]);
		}
		else
		{
		$documentCollectiondetails = DocumentCollectionDetails::whereIn("visa_process_status",array(0,1,2))->where("visa_documents_status",2)->where("offer_letter_onboarding_status",2)->where("backout_status",1)->get();
		if($documentCollectiondetails!=''){
			foreach($documentCollectiondetails as $_doc){
				if($_doc->current_visa_status=="Inside Country"){
					$visapermission=VisaPermission::where("id",1)->first();
					$arraval=explode(",",$visapermission->stageid);
					$visasprocessData = Visaprocess::whereIN("visa_stage",$arraval)->where("document_id",$_doc->id)->orderBy('id','DESC')->first();
					if($visasprocessData!=''){
						if($_doc->stamping_deadline!=''){
						$_documentCollectionModcaption = DocumentCollectionDetails::find($_doc->id);						
						$_documentCollectionModcaption->sort_date =date("Y-m-d",strtotime($_doc->stamping_deadline));
						$_documentCollectionModcaption->sort_dateBY ="Stamping Deadline";						
						$_documentCollectionModcaption->save();	
						}						
					}
					else{
						$Documentdata = DocumentCollectionDetailsValues::where("document_collection_id",$_doc->id)->where("attribute_code",66)->first(); 
						if($Documentdata != '')
						   {
							   if($Documentdata->attribute_value!=''){
							   $_documentCollectionModcaption = DocumentCollectionDetails::find($_doc->id);						
								$_documentCollectionModcaption->sort_date =date("Y-m-d",strtotime($Documentdata->attribute_value));
								$_documentCollectionModcaption->sort_dateBY ="Change Status";								
								$_documentCollectionModcaption->save();
							   }
								
						   }
						   else{
							   $_documentCollectionModcaption = DocumentCollectionDetails::find($_doc->id);						
								$_documentCollectionModcaption->sort_date =NULL;								
								$_documentCollectionModcaption->save();
						   }
						
						
					}
				}
				else if($_doc->current_visa_status=="Outside Country"){
					
				$DocumentDOJ = DocumentCollectionDetailsValues::where("document_collection_id",$_doc->id)->where("attribute_code",83)->first(); 
				if($DocumentDOJ != ''){
					if($_doc->stamping_deadline!=''){
					$_documentCollectionModcaption = DocumentCollectionDetails::find($_doc->id);						
					$_documentCollectionModcaption->sort_date =date("Y-m-d",strtotime($_doc->stamping_deadline));
					$_documentCollectionModcaption->sort_dateBY ="Stamping Deadline";					
					$_documentCollectionModcaption->save();	
					}
					else{
						$_documentCollectionModcaption = DocumentCollectionDetails::find($_doc->id);						
						$_documentCollectionModcaption->sort_date =NULL;						
						$_documentCollectionModcaption->save();	
					}
				}
				else{
					if($_doc->entry_date!=''){
					$_documentCollectionModcaption = DocumentCollectionDetails::find($_doc->id);						
					$_documentCollectionModcaption->sort_date =date("Y-m-d",strtotime($_doc->entry_date));
					$_documentCollectionModcaption->sort_dateBY ="Date of Entry";
					$_documentCollectionModcaption->save();
					}
					else{
						$_documentCollectionModcaption = DocumentCollectionDetails::find($_doc->id);						
					$_documentCollectionModcaption->sort_date =NULL;						
					$_documentCollectionModcaption->save();
					}
				}
				
				}
				else{
					
				}
				
			}
		}			
		
		}
		
		
			$EMPonDataOBJ=	new VisaSortDate();
			$EMPonDataOBJ->date=$todayDate;
			$EMPonDataOBJ->save();
}
public function CheckAttendanceEMPData(){
	$mappingagent=AgentMappingDetails::get();
	/*if($mappingagent!=''){
		 foreach($mappingagent as $_agent){
			 $attandence=AgentMappingAttendnace::where("empid",$_agent->agent_emp_id)->first();
			 if($attandence!=''){
				if($_agent->Department_current==$attandence->dept_id){
					$mappingOBJ=AgentMappingDetails::find($_agent->id);
					$mappingOBJ->current_status_department=2;
					$mappingOBJ->save();
				}
				else{
					$mappingOBJ=AgentMappingDetails::find($_agent->id);
					$mappingOBJ->current_status_department=3;
					$mappingOBJ->save();	
				}
					
			 }
			 else{
				$mappingOBJ=AgentMappingDetails::find($_agent->id);
				$mappingOBJ->current_status_department=4;
				$mappingOBJ->save();
			 }
			 
		 }
	}
	*/
	//update tlid
	if($mappingagent!=''){
		 foreach($mappingagent as $_agent){
			 $attandence=AgentMappingAttendnace::where("empid",$_agent->agent_emp_id)->first();
			 if($attandence!=''){
				if($_agent->TL_name_emp_id==$attandence->tl_id){
					$mappingOBJ=AgentMappingDetails::find($_agent->id);
					$mappingOBJ->current_status_tl=2;
					$mappingOBJ->save();
				}
				else{
					$mappingOBJ=AgentMappingDetails::find($_agent->id);
					$mappingOBJ->current_status_tl=3;
					$mappingOBJ->save();	
				}
					
			 }
			 else{
				$mappingOBJ=AgentMappingDetails::find($_agent->id);
				$mappingOBJ->current_status_tl=4;
				$mappingOBJ->save();
			 }
			 
		 }
	}
	echo "Done";
}
public function exportVisaInProcessWithCompleteVisa(){
	
		 
	     //print_r($Collection);exit;    
			 
	        $filename = 'VisaInProcess_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:V1');
			$sheet->setCellValue('A1', 'Cond List  - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Department'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Designation As Per Mol '))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('MOL Date of Joining '))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('PERSON NAME AS PER MOL  '))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Basic Salary MOL  '))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Others MOL'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Total Gross Salary (MOL)  '))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Labour Card Number'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Labour Contract '))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Labour Issue Date '))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Labour Expiry Date  '))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('VISA UID NO'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Permanent Visa Number'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Visa Issue Date '))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Visa Expiry Date '))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Emirates ID NO '))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Insurance Number'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('INSURANCE COMPANY'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('INSURANCE CODE / GRADE'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('DocumentId'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$Collectiondata = DocumentCollectionDetails::whereIn("visa_process_status",array(2,4))->where("backout_status",1)->get();
			$sn = 1;
			foreach ($Collectiondata as $sid) {
				//echo $sid;exit;
				 
				 $name=$sid->emp_name;
				 $departmentMod = Department::where("id",$sid->department)->first();
				 if($departmentMod!=''){
				 $deptname=$departmentMod->department_name;
				 }
				 else{
					$deptname=''; 
				 }
				 $PerMol = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",96)->first(); 
				if($PerMol != '')
				   {
						$PerMol= $PerMol->attribute_value ;
				   }
				   else
				   {
						$PerMol= "--";
				   }
				   $MOLDateofJoining = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",81)->first(); 
					if($MOLDateofJoining != '')
				   {
						$MOLDateofJoining= date("d M Y",strtotime($MOLDateofJoining->attribute_value)) ;
				   }
				   else
				   {
						$MOLDateofJoining= "--";
				   }
				   $PERSONNAMEASPERMOL = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",143)->first(); 
					if($PERSONNAMEASPERMOL != '')
				   {
						$PERSONNAMEASPERMOL= $PERSONNAMEASPERMOL->attribute_value;
				   }
				   else
				   {
						$PERSONNAMEASPERMOL= "--";
				   }
				   $BasicSalaryMOL = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",130)->first(); 
					if($BasicSalaryMOL != '')
				   {
						$BasicSalaryMOL= $BasicSalaryMOL->attribute_value;
				   }
				   else
				   {
						$BasicSalaryMOL= "--";
				   }
				   $OthersMOL = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",131)->first(); 
					if($OthersMOL != '')
				   {
						$OthersMOL= $OthersMOL->attribute_value;
				   }
				   else
				   {
						$OthersMOL= "--";
				   }
				   $TotalGrossSalary = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",132)->first(); 
					if($TotalGrossSalary != '')
				   {
						$TotalGrossSalary= $TotalGrossSalary->attribute_value;
				   }
				   else
				   {
						$TotalGrossSalary= "--";
				   }
				   $LabourCardNumber = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",64)->first(); 
					if($LabourCardNumber != '')
				   {
						$LabourCardNumber= $LabourCardNumber->attribute_value;
				   }
				   else
				   {
						$LabourCardNumber= "--";
				   }
				   $LabourContract = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",109)->first(); 
					if($LabourContract != '')
				   {
						$LabourContract= $LabourContract->attribute_value;
				   }
				   else
				   {
						$LabourContract= "--";
				   }
				   $LabourIssueDate = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",104)->first(); 
					if($LabourIssueDate != '')
				   {
						$LabourIssueDate= date("d M Y",strtotime($LabourIssueDate->attribute_value));
				   }
				   else
				   {
						$LabourIssueDate= "--";
				   }
				   $LabourExpiryDate = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",105)->first(); 
					if($LabourExpiryDate != '')
				   {
						$LabourExpiryDate= date("d M Y",strtotime($LabourExpiryDate->attribute_value));
				   }
				   else
				   {
						$LabourExpiryDate= "--";
				   }
				   $VISAUIDNO = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",101)->first(); 
					if($VISAUIDNO != '')
				   {
						$VISAUIDNO= $VISAUIDNO->attribute_value;
				   }
				   else
				   {
						$VISAUIDNO= "--";
				   }
				   $PermanentVisaNumber = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",49)->first(); 
					if($PermanentVisaNumber != '')
				   {
						$PermanentVisaNumber= $PermanentVisaNumber->attribute_value;
				   }
				   else
				   {
						$PermanentVisaNumber= "--";
				   }
				   $VisaIssueDate = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",102)->first(); 
					if($VisaIssueDate != '')
				   {
						$VisaIssueDate= date("d M Y",strtotime($VisaIssueDate->attribute_value));
				   }
				   else
				   {
						$VisaIssueDate= "--";
				   }
				   $VisaExpiryDate = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",103)->first(); 
					if($VisaExpiryDate != '')
				   {
						$VisaExpiryDate= date("d M Y",strtotime($VisaExpiryDate->attribute_value));
				   }
				   else
				   {
						$VisaExpiryDate= "--";
				   }
				   $EmiratesID = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",107)->first(); 
					if($EmiratesID != '')
				   {
						$EmiratesID= $EmiratesID->attribute_value;
				   }
				   else
				   {
						$EmiratesID= "--";
				   }
				   $InsuranceNumber = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",133)->first(); 
					if($InsuranceNumber != '')
				   {
						$InsuranceNumber= $InsuranceNumber->attribute_value;
				   }
				   else
				   {
						$InsuranceNumber= "--";
				   }
				   $INSURANCECOMPANY = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",150)->first(); 
					if($INSURANCECOMPANY != '')
				   {
						$INSURANCECOMPANY= $INSURANCECOMPANY->attribute_value;
				   }
				   else
				   {
						$INSURANCECOMPANY= "--";
				   }
				   $INSURANCECODE = VisaDetails::where("document_collection_id",$sid->id)->where("attribute_code",150)->first(); 
					if($INSURANCECODE != '')
				   {
						$INSURANCECODE= $INSURANCECODE->attribute_value;
				   }
				   else
				   {
						$INSURANCECODE= "--";
				   }
				 $indexCounter++; 	
				 
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $deptname)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $PerMol)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $MOLDateofJoining)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $PERSONNAMEASPERMOL)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $BasicSalaryMOL)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $OthersMOL)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $TotalGrossSalary)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $LabourCardNumber)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('K'.$indexCounter, $LabourContract)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $LabourIssueDate)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $LabourExpiryDate)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $VISAUIDNO)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $PermanentVisaNumber)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $VisaIssueDate)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $VisaExpiryDate)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $EmiratesID)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $InsuranceNumber)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $INSURANCECOMPANY)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $INSURANCECODE)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $sid->id)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			  for($col = 'A'; $col !== 'V'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:V1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','V') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;	
}
public function exportVisaInProcessWithexternallink(){
	
		 
	     //print_r($Collection);exit;    
			 
	        $filename = 'Externallink_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:M1');
			$sheet->setCellValue('A1', 'Cond List  - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Department'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Local Address '))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Employee Date of Birth'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Local Contact Number '))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Emergency Contact Number'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Nationality'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Home Country Address'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Home Country Contact Number'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Gender'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Email Id'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('DocumentId'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$Collectiondata = DocumentCollectionDetails::whereIn("visa_process_status",array(2,4))->where("backout_status",1)->get();
			$sn = 1;
			foreach ($Collectiondata as $sid) {
				//echo $sid;exit;
				 
				 $name=$sid->emp_name;
				 $departmentMod = Department::where("id",$sid->department)->first();
				 if($departmentMod!=''){
				 $deptname=$departmentMod->department_name;
				 }
				 else{
					$deptname=''; 
				 }
				 $PerMol = OnboardCandidateKyc::where("docId",$sid->id)->first(); 
				if($PerMol != '')
				   {
						if($PerMol->onboard_local_address!=''){
						$LocalAddress= $PerMol->onboard_local_address;
						}
						else{
							$LocalAddress='-';
						}
						if($PerMol->onboard_dob!=''){
							$dob= date("d M Y",strtotime($PerMol->onboard_dob));
						}
						else{
							$dob='-';
						}
						if($PerMol->onboard_contactno!=''){
						$onboard_contactno= $PerMol->onboard_contactno;
						}
						else{
							$onboard_contactno='-';
						}
						if($PerMol->onboard_emergency_contactno!=''){
						$onboard_emergency_contactno= $PerMol->onboard_emergency_contactno;
						}
						else{
							$onboard_emergency_contactno='-';
						}
						if($PerMol->country!=''){
						$country= $PerMol->country;
						}
						else{
							$country='-';
						}
						if($PerMol->home_country_address!=''){
						$home_country_address= $PerMol->home_country_address;
						}
						else{
							$home_country_address='-';
						}
						if($PerMol->home_country_contactno!=''){
						$home_country_contactno= $PerMol->home_country_contactno;
						}
						else{
							$home_country_contactno='-';
						}
						if($PerMol->gender!=''){
						$gender= $PerMol->gender;
						}
						else{
							$gender='-';
						}
						if($PerMol->email!=''){
						$email= $PerMol->email;
						}
						else{
							$email='-';
						}
				   }
				   else
				   {
					   $dob='-';
						$email='-';
						$gender='-';
						$home_country_contactno='-';
						$home_country_address='-';
						$country='-';
						$onboard_emergency_contactno='-';
						$onboard_contactno='-';
						$LocalAddress='-';
				   }
				   
				 $indexCounter++; 	
				 
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $deptname)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $LocalAddress)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $dob)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $onboard_contactno)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $onboard_emergency_contactno)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $country)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $home_country_address)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $home_country_contactno)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('K'.$indexCounter, $gender)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $email)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $sid->id)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			  for($col = 'A'; $col !== 'M'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:M1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','M') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;	
}
public function UpdateInsuranceCensusList(){
	$data=InsuranceCensusList::get();
	foreach($data as $empid){
	$onboardempdata=EmployeeOnboardData::where("emp_id",$empid->EMP_ID)->first();	
	//print_r($onboardempdata);exit;
	if($onboardempdata!=''){
			$visastageId = Visaprocess::where("document_id",$onboardempdata->document_id)->orderBy('id','DESC')->first();
			if($visastageId!=''){
				$visatype=$visastageId->visa_type;
			}
			else{
				$visatype='';
			}
				$existDocument = VisaDetails::where("document_collection_id",$onboardempdata->document_id)->where("visa_type_id",$visatype)->where("attribute_code",101)->first();
					if($existDocument != '')
					{
						$objDocument= VisaDetails::find($existDocument->id);
					}
					else
					{
						$objDocument = new VisaDetails();
					}
					$objDocument->document_collection_id = $onboardempdata->document_id;
					$objDocument->visa_type_id = $visatype;
					$objDocument->attribute_code = 101;
					$objDocument->attribute_value = $empid->VISA_UID_NO;
					$objDocument->save();
					
					$existVISA_FILE_NO = VisaDetails::where("document_collection_id",$onboardempdata->document_id)->where("visa_type_id",$visatype)->where("attribute_code",49)->first();
					if($existVISA_FILE_NO != '')
					{
						$objVISA_FILE_NO= VisaDetails::find($existVISA_FILE_NO->id);
					}
					else
					{
						$objVISA_FILE_NO = new VisaDetails();
					}
					$objVISA_FILE_NO->document_collection_id = $onboardempdata->document_id;
					$objVISA_FILE_NO->visa_type_id = $visatype;
					$objVISA_FILE_NO->attribute_code = 49;
					$objVISA_FILE_NO->attribute_value = $empid->VISA_FILE_NO;
					$objVISA_FILE_NO->save();
					
					$existEMIRATES_ID_NO = VisaDetails::where("document_collection_id",$onboardempdata->document_id)->where("visa_type_id",$visatype)->where("attribute_code",107)->first();
					if($existEMIRATES_ID_NO != '')
					{
						$objEMIRATES_ID_NO= VisaDetails::find($existEMIRATES_ID_NO->id);
					}
					else
					{
						$objEMIRATES_ID_NO = new VisaDetails();
					}
					$objEMIRATES_ID_NO->document_collection_id = $onboardempdata->document_id;
					$objEMIRATES_ID_NO->visa_type_id = $visatype;
					$objEMIRATES_ID_NO->attribute_code = 107;
					$objEMIRATES_ID_NO->attribute_value = $empid->EMIRATES_ID_NO;
					$objEMIRATES_ID_NO->save();
					
					$existPOLICY_NUMBER = VisaDetails::where("document_collection_id",$onboardempdata->document_id)->where("visa_type_id",$visatype)->where("attribute_code",133)->first();
					if($existPOLICY_NUMBER != '')
					{
						$objPOLICY_NUMBER= VisaDetails::find($existPOLICY_NUMBER->id);
					}
					else
					{
						$objPOLICY_NUMBER= new VisaDetails();
					}
					$objPOLICY_NUMBER->document_collection_id = $onboardempdata->document_id;
					$objPOLICY_NUMBER->visa_type_id = $visatype;
					$objPOLICY_NUMBER->attribute_code = 133;
					$objPOLICY_NUMBER->attribute_value = $empid->POLICY_NUMBER;
					$objPOLICY_NUMBER->save();
					
					$existCompany = VisaDetails::where("document_collection_id",$onboardempdata->document_id)->where("visa_type_id",$visatype)->where("attribute_code",150)->first();
					if($existCompany != '')
					{
						$objCompany= VisaDetails::find($existCompany->id);
					}
					else
					{
						$objCompany= new VisaDetails();
					}
					$objCompany->document_collection_id = $onboardempdata->document_id;
					$objCompany->visa_type_id = $visatype;
					$objCompany->attribute_code = 150;
					$objCompany->attribute_value = "Takaful";
					$objCompany->save();
					
					$existCATEGORY = VisaDetails::where("document_collection_id",$onboardempdata->document_id)->where("visa_type_id",$visatype)->where("attribute_code",151)->first();
					if($existCATEGORY != '')
					{
						$objCATEGORY= VisaDetails::find($existCATEGORY->id);
					}
					else
					{
						$objCATEGORY= new VisaDetails();
					}
					$objCATEGORY->document_collection_id = $onboardempdata->document_id;
					$objCATEGORY->visa_type_id = $visatype;
					$objCATEGORY->attribute_code = 151;
					$objCATEGORY->attribute_value = $empid->CATEGORY;
					$objCATEGORY->save();
					
					
	}
		
	}
	echo "Update";
}
public function UpdateInsuranceCensusListEMP(){
	$data=InsuranceCensusList::get();
	foreach($data as $empid){
	$emp_details = Employee_details::where("emp_id",$empid->EMP_ID)->first(); 
	//print_r($onboardempdata);exit;
	if($emp_details!=''){
			$empAttrExist = Employee_attribute::where("emp_id",$emp_details->emp_id)->where("dept_id",$emp_details->dept_id)->where("attribute_code","visa_uid_no")->first();
					if($empAttrExist != '')
					{
						$updateEmpAttr = Employee_attribute::find($empAttrExist->id);
						
					}
					else
					{
						$updateEmpAttr = new Employee_attribute();
					}
					$updateEmpAttr->dept_id = $emp_details->dept_id;
					$updateEmpAttr->emp_id = $emp_details->emp_id;
					$updateEmpAttr->attribute_code = 'visa_uid_no';
					$updateEmpAttr->attribute_values = $empid->VISA_UID_NO;
					$updateEmpAttr->status = 1;
					$updateEmpAttr->save();
					
					$empAttrExistPVISA_NUMBER = Employee_attribute::where("emp_id",$emp_details->emp_id)->where("dept_id",$emp_details->dept_id)->where("attribute_code","PVISA_NUMBER")->first();
					if($empAttrExistPVISA_NUMBER != '')
					{
						$updateEmpAttrPVISA_NUMBER = Employee_attribute::find($empAttrExistPVISA_NUMBER->id);
						
					}
					else
					{
						$updateEmpAttrPVISA_NUMBER = new Employee_attribute();
					}
					$updateEmpAttrPVISA_NUMBER->dept_id = $emp_details->dept_id;
					$updateEmpAttrPVISA_NUMBER->emp_id = $emp_details->emp_id;
					$updateEmpAttrPVISA_NUMBER->attribute_code = 'PVISA_NUMBER';
					$updateEmpAttrPVISA_NUMBER->attribute_values = $empid->VISA_FILE_NO;
					$updateEmpAttrPVISA_NUMBER->status = 1;
					$updateEmpAttrPVISA_NUMBER->save();
					
					
				$empAttrExistemirates_id_no = Employee_attribute::where("emp_id",$emp_details->emp_id)->where("dept_id",$emp_details->dept_id)->where("attribute_code","emirates_id_no")->first();
					if($empAttrExistemirates_id_no != '')
					{
						$updateEmpAttremirates_id_no = Employee_attribute::find($empAttrExistemirates_id_no->id);
						
					}
					else
					{
						$updateEmpAttremirates_id_no = new Employee_attribute();
					}
					$updateEmpAttremirates_id_no->dept_id = $emp_details->dept_id;
					$updateEmpAttremirates_id_no->emp_id = $emp_details->emp_id;
					$updateEmpAttremirates_id_no->attribute_code = 'emirates_id_no';
					$updateEmpAttremirates_id_no->attribute_values =  $empid->EMIRATES_ID_NO;
					$updateEmpAttremirates_id_no->status = 1;
					$updateEmpAttremirates_id_no->save();
					
					
					$empAttrExistinsurance = Employee_attribute::where("emp_id",$emp_details->emp_id)->where("dept_id",$emp_details->dept_id)->where("attribute_code","insurance")->first();
					if($empAttrExistinsurance != '')
					{
						$updateEmpAttrinsurance = Employee_attribute::find($empAttrExistinsurance->id);
						
					}
					else
					{
						$updateEmpAttrinsurance = new Employee_attribute();
					}
					$updateEmpAttrinsurance->dept_id = $emp_details->dept_id;
					$updateEmpAttrinsurance->emp_id = $emp_details->emp_id;
					$updateEmpAttrinsurance->attribute_code = 'insurance';
					$updateEmpAttrinsurance->attribute_values =  $empid->POLICY_NUMBER;
					$updateEmpAttrinsurance->status = 1;
					$updateEmpAttrinsurance->save();
					
					$empAttrExistCompany = Employee_attribute::where("emp_id",$emp_details->emp_id)->where("dept_id",$emp_details->dept_id)->where("attribute_code","insurance_company")->first();
					if($empAttrExistCompany != '')
					{
						$updateEmpAttrCompany = Employee_attribute::find($empAttrExistCompany->id);
						
					}
					else
					{
						$updateEmpAttrCompany = new Employee_attribute();
					}
					$updateEmpAttrCompany->dept_id = $emp_details->dept_id;
					$updateEmpAttrCompany->emp_id = $emp_details->emp_id;
					$updateEmpAttrCompany->attribute_code = 'insurance_company';
					$updateEmpAttrCompany->attribute_values =  "Takaful";
					$updateEmpAttrCompany->status = 1;
					$updateEmpAttrCompany->save();
					
					$insurance_code = Employee_attribute::where("emp_id",$emp_details->emp_id)->where("dept_id",$emp_details->dept_id)->where("attribute_code","insurance_code")->first();
					if($insurance_code != '')
					{
						$updateinsurance_code = Employee_attribute::find($insurance_code->id);
						
					}
					else
					{
						$updateinsurance_code = new Employee_attribute();
					}
					$updateinsurance_code->dept_id = $emp_details->dept_id;
					$updateinsurance_code->emp_id = $emp_details->emp_id;
					$updateinsurance_code->attribute_code = 'insurance_code';
					$updateinsurance_code->attribute_values =  $empid->CATEGORY;
					$updateinsurance_code->status = 1;
					$updateinsurance_code->save();
					
					
					
					
					
					
	}
		
	}
	echo "Update";
}
public function UpdateInsuranceCensusListExternalLink(Request $request){
	$data=InsuranceCensusList::get();
	foreach($data as $empid){
	$onboardempdata=EmployeeOnboardData::where("emp_id",$empid->EMP_ID)->first();	
	//print_r($onboardempdata);exit;
	if($onboardempdata!=''){
			
			$empattributesMod = OnboardCandidateKyc::where("docId",$onboardempdata->document_id)->first(); 
			//print_r($empattributesMod);exit;
			if(!empty($empattributesMod))
			{					
			$onboardKYCOBJ = OnboardCandidateKyc::find($empattributesMod->id);
			}
			else
			{
				$onboardKYCOBJ = new OnboardCandidateKyc();
			}
			
			$onboardKYCOBJ->docId =$onboardempdata->document_id;
			$onboardKYCOBJ->onboard_dob =date('d-m-Y', strtotime($empid->DATE_OF_BIRTH));
			$onboardKYCOBJ->onboard_contactno =$empid->BENEFICIARY_MOBILE_NO;
			
			$onboardKYCOBJ->country =$empid->NATIONALITY;
			
			$onboardKYCOBJ->gender =$empid->GENDER;
			$onboardKYCOBJ->email =$empid->BENEFICIARY_EMAIL;
			$onboardKYCOBJ->createdBY=$request->session()->get('EmployeeId');
			$onboardKYCOBJ->save();
					
					
	}
		
	}
	echo "Update";
}
public function UpdateInsuranceCensusListExternalLinkEMP(Request $request){
	$data=InsuranceCensusList::get();
	foreach($data as $empid){
	$emp_details = Employee_details::where("emp_id",$empid->EMP_ID)->first(); 
	//print_r($onboardempdata);exit;
	if($emp_details!=''){
			$empAttrExist = Employee_attribute::where("emp_id",$emp_details->emp_id)->where("dept_id",$emp_details->dept_id)->where("attribute_code","email")->first();
					if($empAttrExist != '')
					{
						$updateEmpAttr = Employee_attribute::find($empAttrExist->id);
						
					}
					else
					{
						$updateEmpAttr = new Employee_attribute();
					}
					$updateEmpAttr->dept_id = $emp_details->dept_id;
					$updateEmpAttr->emp_id = $emp_details->emp_id;
					$updateEmpAttr->attribute_code = 'email';
					$updateEmpAttr->attribute_values = $empid->BENEFICIARY_EMAIL;
					$updateEmpAttr->status = 1;
					$updateEmpAttr->save();
					
					$empAttrExistPVISA_NUMBER = Employee_attribute::where("emp_id",$emp_details->emp_id)->where("dept_id",$emp_details->dept_id)->where("attribute_code","GNDR")->first();
					if($empAttrExistPVISA_NUMBER != '')
					{
						$updateEmpAttrPVISA_NUMBER = Employee_attribute::find($empAttrExistPVISA_NUMBER->id);
						
					}
					else
					{
						$updateEmpAttrPVISA_NUMBER = new Employee_attribute();
					}
					if($empid->GENDER=='M'){
						$gender="Male";
						
					}
					elseif($empid->GENDER=='F'){
						$gender="Female";
					}
					else{
						$gender="";
					}
					$updateEmpAttrPVISA_NUMBER->dept_id = $emp_details->dept_id;
					$updateEmpAttrPVISA_NUMBER->emp_id = $emp_details->emp_id;
					$updateEmpAttrPVISA_NUMBER->attribute_code = 'GNDR';
					$updateEmpAttrPVISA_NUMBER->attribute_values = $gender;
					$updateEmpAttrPVISA_NUMBER->status = 1;
					$updateEmpAttrPVISA_NUMBER->save();
					
					
				$empAttrExistemirates_id_no = Employee_attribute::where("emp_id",$emp_details->emp_id)->where("dept_id",$emp_details->dept_id)->where("attribute_code","NAT")->first();
					if($empAttrExistemirates_id_no != '')
					{
						$updateEmpAttremirates_id_no = Employee_attribute::find($empAttrExistemirates_id_no->id);
						
					}
					else
					{
						$updateEmpAttremirates_id_no = new Employee_attribute();
					}
					$updateEmpAttremirates_id_no->dept_id = $emp_details->dept_id;
					$updateEmpAttremirates_id_no->emp_id = $emp_details->emp_id;
					$updateEmpAttremirates_id_no->attribute_code = 'NAT';
					$updateEmpAttremirates_id_no->attribute_values =  $empid->NATIONALITY;
					$updateEmpAttremirates_id_no->status = 1;
					$updateEmpAttremirates_id_no->save();
					
					
					$empAttrExistinsurance = Employee_attribute::where("emp_id",$emp_details->emp_id)->where("dept_id",$emp_details->dept_id)->where("attribute_code","CONTACT_NUMBER")->first();
					if($empAttrExistinsurance != '')
					{
						$updateEmpAttrinsurance = Employee_attribute::find($empAttrExistinsurance->id);
						
					}
					else
					{
						$updateEmpAttrinsurance = new Employee_attribute();
					}
					$updateEmpAttrinsurance->dept_id = $emp_details->dept_id;
					$updateEmpAttrinsurance->emp_id = $emp_details->emp_id;
					$updateEmpAttrinsurance->attribute_code = 'CONTACT_NUMBER';
					$updateEmpAttrinsurance->attribute_values =  $empid->BENEFICIARY_MOBILE_NO;
					$updateEmpAttrinsurance->status = 1;
					$updateEmpAttrinsurance->save();
					
					$empAttrExistCompany = Employee_attribute::where("emp_id",$emp_details->emp_id)->where("dept_id",$emp_details->dept_id)->where("attribute_code","EMPDOB")->first();
					if($empAttrExistCompany != '')
					{
						$updateEmpAttrCompany = Employee_attribute::find($empAttrExistCompany->id);
						
					}
					else
					{
						$updateEmpAttrCompany = new Employee_attribute();
					}
					$updateEmpAttrCompany->dept_id = $emp_details->dept_id;
					$updateEmpAttrCompany->emp_id = $emp_details->emp_id;
					$updateEmpAttrCompany->attribute_code = 'EMPDOB';
					$updateEmpAttrCompany->attribute_values =  date('d-m-Y', strtotime($empid->DATE_OF_BIRTH));;
					$updateEmpAttrCompany->status = 1;
					$updateEmpAttrCompany->save();
					
					
					
					
					
					
	}
	echo "Update";
}	
}
public function exportVisaInProcessWithexternallinkComplete(){
		        $filename = 'CompleteVisaInProcess_report_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AH1');
			$sheet->setCellValue('A1', 'Cond List  - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Department'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Designation As Per Mol '))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('MOL Date of Joining '))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('PERSON NAME AS PER MOL  '))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Basic Salary MOL  '))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Others MOL'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Total Gross Salary (MOL)  '))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Labour Card Number'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Labour Contract '))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Labour Issue Date '))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Labour Expiry Date  '))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('VISA UID NO'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('Permanent Visa Number'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Visa Issue Date '))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Visa Expiry Date '))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Emirates ID NO '))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Insurance Number'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('INSURANCE COMPANY'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('INSURANCE CODE / GRADE'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('DocumentId'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('W'.$indexCounter, strtoupper('Local Address '))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('Employee Date of Birth'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('Local Contact Number '))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('Emergency Contact Number'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('Nationality'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AB'.$indexCounter, strtoupper('Home Country Address'))->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AC'.$indexCounter, strtoupper('Home Country Contact Number'))->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AD'.$indexCounter, strtoupper('Gender'))->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AE'.$indexCounter, strtoupper('Email Id'))->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AF'.$indexCounter, strtoupper('DocumentId'))->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AG'.$indexCounter, strtoupper('EMPID'))->getStyle('AG'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AH'.$indexCounter, strtoupper('Attrition'))->getStyle('AH'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$Collectiondata =Employee_details::get();
			$sn = 1;
			foreach ($Collectiondata as $sid) {
				//echo $sid;exit;
				 
				 $name=$sid->emp_name;
				 $departmentMod = Department::where("id",$sid->dept_id)->first();
				 if($departmentMod!=''){
				 $deptname=$departmentMod->department_name;
				 }
				 else{
					$deptname=''; 
				 }
				 
				 
				 
				 
				 $DPerMol = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","PERMOL")->first(); 
				if($DPerMol != '')
				   {
						$DPerMol= $DPerMol->attribute_values ;
				   }
				   else
				   {
						$DPerMol= "--";
				   }
				   //echo $DPerMol;exit;
				   $MOLDateofJoining = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","MOL_DOJ")->first(); 
					if($MOLDateofJoining != '')
				   {
						$MOLDateofJoining= date("d M Y",strtotime($MOLDateofJoining->attribute_values)) ;
				   }
				   else
				   {
						$MOLDateofJoining= "--";
				   }
				   $PERSONNAMEASPERMOL = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","personname_as_per_mol_payroll")->first(); 
					if($PERSONNAMEASPERMOL != '')
				   {
						$PERSONNAMEASPERMOL= $PERSONNAMEASPERMOL->attribute_values;
				   }
				   else
				   {
						$PERSONNAMEASPERMOL= "--";
				   }
				   $BasicSalaryMOL = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","basic_salary_mol")->first(); 
					if($BasicSalaryMOL != '')
				   {
						$BasicSalaryMOL= $BasicSalaryMOL->attribute_values;
				   }
				   else
				   {
						$BasicSalaryMOL= "--";
				   }
				   $OthersMOL = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","others_mol")->first(); 
					if($OthersMOL != '')
				   {
						$OthersMOL= $OthersMOL->attribute_values;
				   }
				   else
				   {
						$OthersMOL= "--";
				   }
				   $TotalGrossSalary = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","total_gross_salary")->first(); 
					if($TotalGrossSalary != '')
				   {
						$TotalGrossSalary= $TotalGrossSalary->attribute_values;
				   }
				   else
				   {
						$TotalGrossSalary= "--";
				   }
				   $LabourCardNumber = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","LC_Number")->first(); 
					if($LabourCardNumber != '')
				   {
						$LabourCardNumber= $LabourCardNumber->attribute_values;
				   }
				   else
				   {
						$LabourCardNumber= "--";
				   }
				   $LabourContract = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","contract")->first(); 
					if($LabourContract != '')
				   {
						$LabourContract= $LabourContract->attribute_values;
				   }
				   else
				   {
						$LabourContract= "--";
				   }
				   $LabourIssueDate = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","labour_issue_date")->first(); 
					if($LabourIssueDate != '')
				   {
						$LabourIssueDate= date("d M Y",strtotime($LabourIssueDate->attribute_values));
				   }
				   else
				   {
						$LabourIssueDate= "--";
				   }
				   $LabourExpiryDate = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","labour_expiry_date")->first(); 
					if($LabourExpiryDate != '')
				   {
						$LabourExpiryDate= date("d M Y",strtotime($LabourExpiryDate->attribute_values));
				   }
				   else
				   {
						$LabourExpiryDate= "--";
				   }
				   $VISAUIDNO = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","visa_uid_no")->first(); 
					if($VISAUIDNO != '')
				   {
						$VISAUIDNO= $VISAUIDNO->attribute_values;
				   }
				   else
				   {
						$VISAUIDNO= "--";
				   }
				   $PermanentVisaNumber = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","PVISA_NUMBER")->first(); 
					if($PermanentVisaNumber != '')
				   {
						$PermanentVisaNumber= $PermanentVisaNumber->attribute_values;
				   }
				   else
				   {
						$PermanentVisaNumber= "--";
				   }
				   $VisaIssueDate = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","visa_issue_date")->first(); 
					if($VisaIssueDate != '')
				   {
						$VisaIssueDate= date("d M Y",strtotime($VisaIssueDate->attribute_values));
				   }
				   else
				   {
						$VisaIssueDate= "--";
				   }
				   $VisaExpiryDate = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","visa_expiry_date")->first(); 
					if($VisaExpiryDate != '')
				   {
						$VisaExpiryDate= date("d M Y",strtotime($VisaExpiryDate->attribute_values));
				   }
				   else
				   {
						$VisaExpiryDate= "--";
				   }
				   $EmiratesID = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","emirates_id_no")->first(); 
					if($EmiratesID != '')
				   {
						$EmiratesID= $EmiratesID->attribute_values;
				   }
				   else
				   {
						$EmiratesID= "--";
				   }
				   $InsuranceNumber = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","insurance")->first(); 
					if($InsuranceNumber != '')
				   {
						$InsuranceNumber= $InsuranceNumber->attribute_values;
				   }
				   else
				   {
						$InsuranceNumber= "--";
				   }
				   $INSURANCECOMPANY = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","insurance_company")->first(); 
					if($INSURANCECOMPANY != '')
				   {
						$INSURANCECOMPANY= $INSURANCECOMPANY->attribute_values;
				   }
				   else
				   {
						$INSURANCECOMPANY= "--";
				   }
				   $INSURANCECODE = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","insurance_code")->first(); 
					if($INSURANCECODE != '')
				   {
						$INSURANCECODE= $INSURANCECODE->attribute_values;
				   }
				   else
				   {
						$INSURANCECODE= "--";
				   }
				   
				   //onboardKYC
				   $LocalAddress = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","LOC_ADD")->first(); 
					if($LocalAddress != '')
				   {
						$LocalAddress= $LocalAddress->attribute_values;
				   }
				   else
				   {
						$LocalAddress= "--";
				   }
				   $dob = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","EMPDOB")->first(); 
					if($dob != '')
				   {
						$dob= date("d M Y",strtotime($dob->attribute_values));
				   }
				   else
				   {
						$dob= "--";
				   }
				   $onboard_contactno = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","CONTACT_NUMBER")->first(); 
					if($onboard_contactno != '')
				   {
						$onboard_contactno= $onboard_contactno->attribute_values;
				   }
				   else
				   {
						$onboard_contactno= "--";
				   }
				   $onboard_emergency_contactno = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","emergency_contact_number")->first(); 
					if($onboard_emergency_contactno != '')
				   {
						$onboard_emergency_contactno= $onboard_emergency_contactno->attribute_values;
				   }
				   else
				   {
						$onboard_emergency_contactno= "--";
				   }
				   $country = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","NAT")->first(); 
					if($country != '')
				   {
						$country= $country->attribute_values;
				   }
				   else
				   {
						$country= "--";
				   }
					$home_country_address = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","HOME_COUN_ADD")->first(); 
					if($home_country_address != '')
					   {
							$home_country_address= $home_country_address->attribute_values;
					   }
					   else
					   {
							$home_country_address= "--";
					   }	
					$home_country_contactno = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","HC_CONTACT_NUMBER")->first(); 
					if($home_country_contactno != '')
					   {
							$home_country_contactno= $home_country_contactno->attribute_values;
					   }
					   else
					   {
							$home_country_contactno= "--";
					   }
					$gender = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","GNDR")->first(); 
					if($gender != '')
					   {
							$gender= $gender->attribute_values;
					   }
					   else
					   {
							$gender= "--";
					   }
						
					$email = Employee_attribute::where("emp_id",$sid->emp_id)->where("dept_id",$sid->dept_id)->where("attribute_code","email")->first(); 
					if($email != '')
					   {
							$email= $email->attribute_values;
					   }
					   else
					   {
							$email= "--";
					   }	
						
						if($sid->offline_status ==1)
					   {
							$attration="NO";
					   }
					   else
					   {
							$attration="YES";
					   }
						
				   
					$Empid=$sid->emp_id;
					$docid=$sid->document_collection_id;
					
				 
				 $indexCounter++; 	
				 
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $deptname)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $DPerMol)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $MOLDateofJoining)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $PERSONNAMEASPERMOL)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $BasicSalaryMOL)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $OthersMOL)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $TotalGrossSalary)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $LabourCardNumber)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('K'.$indexCounter, $LabourContract)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $LabourIssueDate)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $LabourExpiryDate)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $VISAUIDNO)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $PermanentVisaNumber)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $VisaIssueDate)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $VisaExpiryDate)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $EmiratesID)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $InsuranceNumber)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $INSURANCECOMPANY)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $INSURANCECODE)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('V'.$indexCounter, $sid->id)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sheet->setCellValue('W'.$indexCounter, $LocalAddress)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('X'.$indexCounter, $dob)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Y'.$indexCounter, $onboard_contactno)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Z'.$indexCounter, $onboard_emergency_contactno)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AA'.$indexCounter, $country)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AB'.$indexCounter, $home_country_address)->getStyle('AB'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AC'.$indexCounter, $home_country_contactno)->getStyle('AC'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('AD'.$indexCounter, $gender)->getStyle('AD'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AE'.$indexCounter, $email)->getStyle('AE'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AF'.$indexCounter, $docid)->getStyle('AF'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AG'.$indexCounter, $Empid)->getStyle('AG'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('AH'.$indexCounter, $attration)->getStyle('AH'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				$sn++;
				
			}
			
			
			  for($col = 'A'; $col !== 'AH'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:AH1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AH') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
}
public static function UpdateActualSalary(){
	$empDetailsdata=UpdateBasicSalary::get();
	if($empDetailsdata!=''){
			
		foreach($empDetailsdata as $_totaldata){
			$emp_details = Employee_details::where("emp_id",$_totaldata->emp_id)->first(); 
			if($emp_details!=''){
				$empAttrExistCompany = Employee_attribute::where("emp_id",$emp_details->emp_id)->where("attribute_code","actual_salary")->first();
					if($empAttrExistCompany != '')
					{
						$updateEmpAttrCompany = Employee_attribute::find($empAttrExistCompany->id);
						
					}
					else
					{
						$updateEmpAttrCompany = new Employee_attribute();
					}
					$updateEmpAttrCompany->dept_id = $emp_details->dept_id;
					$updateEmpAttrCompany->emp_id = $emp_details->emp_id;
					$updateEmpAttrCompany->attribute_code = 'actual_salary';
					$updateEmpAttrCompany->attribute_values = $_totaldata->basic_salary;
					$updateEmpAttrCompany->status = 1;
					if($updateEmpAttrCompany->save()){
						$empOBJ=Employee_details::find($emp_details->id);
						$empOBJ->actual_salary=$_totaldata->basic_salary;
						$empOBJ->save();
					}
					
					
			}
		}
		echo "Update....";
	}
}
public function UpdateVisaStageNexus(){
	$data=InsuranceNexusList::get();
	if($data!=''){
		foreach($data as $_nexus){
			$emp_details = Employee_details::where("emp_id",$_nexus->EMP_ID)->first();
				if($emp_details!='' && $emp_details->document_collection_id!=''){
					$Collectiondata = DocumentCollectionDetails::where("id",$emp_details->document_collection_id)->first();
					if($Collectiondata!=''){
						$visa_process=$Collectiondata->visa_process_status;
						if($visa_process==4){
							$visa_status="Visa Complete";
							$visa_stage="Stamping";	
							
						}
						else{
							$visaprocess = Visaprocess::where("document_id",$emp_details->document_collection_id)->orderBy('id','DESC')->first();
							if($visaprocess!=''){
							$stage=VisaStage::where("id",$visaprocess->visa_stage)->orderBy('id','DESC')->first();
							if($stage!=''){
								$visa_status="Visa InProcess";
								$visa_stage=$stage->stage_name;	
								
							}
							}

						}
						$UpdateOBJ=InsuranceNexusList::find($_nexus->id);
						$UpdateOBJ->visa_status=$visa_status;
						$UpdateOBJ->visa_stage=$visa_stage;
						$UpdateOBJ->emp_status=$emp_details->offline_status;
						$UpdateOBJ->save();						
					}
				}
				else{
					$visaupdated=VisaExpensesDetailsUpdated::where("Employee_id",$_nexus->EMP_ID)->first();
					if($visaupdated!=''){
						$UpdateOBJ=InsuranceNexusList::find($_nexus->id);
						$UpdateOBJ->visa_status="Visa Complete";
						$UpdateOBJ->visa_stage="Stamping";
						$UpdateOBJ->emp_status=$emp_details->offline_status;
						$UpdateOBJ->save();	
					}
				}
		}
		
	}
	echo "Updated.....";
}
public function UpdateTLDetails(){
	$design=Designation::where("tlsm",2)->where("status",1)->get();
		$designarray=array();
		foreach($design as $_design){
			$designarray[]=$_design->id;
		}
		$finalarray=implode(",",$designarray);
		$empdetailsdata = Employee_details::whereIn("designation_by_doc_collection",$designarray)->where("offline_status",1)->get();
		if($empdetailsdata!=''){
			foreach($empdetailsdata as $empdata){
				$TLDetailsOBJ = new TLDetails();
				$TLDetailsOBJ->tl_name=$empdata->emp_name;
				$TLDetailsOBJ->emp_id=$empdata->emp_id;
				$TLDetailsOBJ->location=$empdata->work_location;
				$TLDetailsOBJ->dept_id=$empdata->dept_id;
				$TLDetailsOBJ->s_id=$empdata->id;
				$TLDetailsOBJ->save();
				
				
			}
		}
		echo "updated....";
}
public function UpdateSalesProcesser(){
	
		$empdetailsdata = Employee_details::where("job_function",4)->where("offline_status",1)->get();
		if($empdetailsdata!=''){
			foreach($empdetailsdata as $empdata){
				$TLDetailsOBJ = new SalesProcesser();
				$TLDetailsOBJ->name=$empdata->emp_name;
				$TLDetailsOBJ->emp_id=$empdata->emp_id;
				$TLDetailsOBJ->location=$empdata->work_location;
				$TLDetailsOBJ->dept_id=$empdata->dept_id;
				$TLDetailsOBJ->s_id=$empdata->id;
				$TLDetailsOBJ->tl_id=$empdata->tl_id;
				$TLDetailsOBJ->save();
				
				
			}
		}
		echo "updated....";
}
public function UpdateEmiratesApplication(){
$empDetailsdata=Employee_details::get();
	if($empDetailsdata!=''){
			
		foreach($empDetailsdata as $_totaldata){
			
				$empAttrExistCompany = Employee_attribute::where("emp_id",$_totaldata->emp_id)->where("attribute_code","emirates_id_no")->first();
					if($empAttrExistCompany != '')
					{
						$valdata=$empAttrExistCompany->attribute_values;
						$start=substr($valdata,0,3);
						if($start==800){
							$updateEmpAttrCompany = new Employee_attribute();
							
							$updateEmpAttrCompany->dept_id = $_totaldata->dept_id;
							$updateEmpAttrCompany->emp_id = $_totaldata->emp_id;
							$updateEmpAttrCompany->attribute_code = 'emirates_application_no';
							$updateEmpAttrCompany->attribute_values =$empAttrExistCompany->attribute_values;
							$updateEmpAttrCompany->status = 1;
							if($updateEmpAttrCompany->save()){
								$empOBJ=Employee_attribute::find($empAttrExistCompany->id);								
								$empOBJ->delete();
							}
						}
					}
					
					
			
		}
		echo "Update....";
	}	
}
public function CreateuserNamePassword(){
	
	$empDetailsdata=Employee_details::where("offline_status",1)->whereIn("job_function",[2,3])->whereNull('passwordStatus')->get();
	
	if($empDetailsdata!=''){
			
		foreach($empDetailsdata as $_totaldata){
			
				
				$empcontact = Employee_attribute::where("emp_id",$_totaldata->emp_id)->where("attribute_code","CONTACT_NUMBER")->first();
					if($empcontact!=''){
						$contctno=$empcontact->attribute_values;
					}
					else{
						$contctno='';
					}
					$empAttrExistCompany = Employee::where("employee_id",$_totaldata->emp_id)->first();
					if($empAttrExistCompany != '')
					{
						
							$updateEmpAttrCompany = new EmpAppAccess();
							
							$updateEmpAttrCompany->fullname = $empAttrExistCompany->fullname;
							$updateEmpAttrCompany->username = $empAttrExistCompany->employee_id;
							$updateEmpAttrCompany->password = $empAttrExistCompany->password;
							$updateEmpAttrCompany->passwordtxt =$empAttrExistCompany->passwordtxt;
							$updateEmpAttrCompany->designation = $_totaldata->designation_by_doc_collection;
							$updateEmpAttrCompany->email = $_totaldata->official_email;
							$updateEmpAttrCompany->contact_no = $contctno;
							$updateEmpAttrCompany->employee_id =$empAttrExistCompany->employee_id;
							$updateEmpAttrCompany->group_id =$empAttrExistCompany->group_id;
							$updateEmpAttrCompany->r_id = $empAttrExistCompany->r_id;
							$updateEmpAttrCompany->job_function =$_totaldata->job_function;
							$updateEmpAttrCompany->location = $_totaldata->work_location;
							$updateEmpAttrCompany->passwordStatus = 1;
							$updateEmpAttrCompany->dept_id = $_totaldata->dept_id;
							$updateEmpAttrCompany->tl_id = $_totaldata->tl_id;
							if($updateEmpAttrCompany->save()){
								$empOBJ=Employee_details::find($_totaldata->id);
								$empOBJ->passwordStatus = 1;
								$empOBJ->save();
							}
							
						
					}else{
						
							$updateEmpAttrCompany = new EmpAppAccess();
							
							$updateEmpAttrCompany->fullname = $_totaldata->emp_name;
							$updateEmpAttrCompany->username = $_totaldata->emp_id;
							$updateEmpAttrCompany->password = Crypt::encrypt(ucfirst(strtolower($_totaldata->first_name))."@123");
							$updateEmpAttrCompany->passwordtxt =ucfirst(strtolower($_totaldata->first_name))."@123";
							$updateEmpAttrCompany->designation = $_totaldata->designation_by_doc_collection;
							$updateEmpAttrCompany->email = $_totaldata->official_email;
							$updateEmpAttrCompany->contact_no = $contctno;
							$updateEmpAttrCompany->employee_id =$_totaldata->emp_id;
							$updateEmpAttrCompany->group_id ='';
							$updateEmpAttrCompany->r_id = '';
							$updateEmpAttrCompany->job_function =$_totaldata->job_function;
							$updateEmpAttrCompany->location = $_totaldata->work_location;
							$updateEmpAttrCompany->passwordStatus = 1;
							$updateEmpAttrCompany->dept_id = $_totaldata->dept_id;
							$updateEmpAttrCompany->tl_id = $_totaldata->tl_id;
							if($updateEmpAttrCompany->save()){
								$empOBJ=Employee_details::find($_totaldata->id);
								$empOBJ->passwordStatus = 1;
								$empOBJ->save();
							}
					}
						
					
					
					
			
		}
		echo "Update1....";

}
}

public function UpdateEVisaFreeZone(){
	 //$datastage = VisaStage::where("visa_type",32)->where("evisa",1)->orderBy('id','DESC')->get();
	 $datastage =Visaprocess::where("visa_type",32)->groupBy('document_id')->get();
	 if($datastage!=''){
		 
		 foreach($datastage as $_stage){
			 $visasprocessData = Visaprocess::where("document_id",$_stage->document_id)->whereIn("visa_stage",array(247,249,250,251,252,253))->orderBy('id','ASC')->first();
			 //print_r($visasprocessData);exit;
			 if($visasprocessData!=''){
				 $date = date ('d-m-Y' , strtotime($visasprocessData->closing_date));
				$newdate = strtotime ( '+60 days' , strtotime ( $date ) ) ;
				$newdate = date ( 'd-m-Y' , $newdate );
					 $empdata=DocumentCollectionDetails::where("id",$_stage->document_id)->first();
					 if($empdata!=''){
						 /*if($empdata->current_visa_status=="Outside Country"){
							//echo $_stage->document_id;exit; 
						 $_documentCollectionModcaption = DocumentCollectionDetails::find($_stage->document_id);
						$_documentCollectionModcaption->entry_date =date("Y-m-d",strtotime($newdate));
						$_documentCollectionModcaption->sort_date =date("Y-m-d",strtotime($newdate));
						$_documentCollectionModcaption->stamping_deadline =NULL;
						$_documentCollectionModcaption->sort_dateBY ="Date of Entry";
						$_documentCollectionModcaption->save();		
						 }*/
						if($empdata->current_visa_status=="Inside Country"){
							$_documentCollectionModcaption = DocumentCollectionDetails::find($_stage->document_id);
							$_documentCollectionModcaption->caption =date("Y-m-d",strtotime($newdate));
							$_documentCollectionModcaption->stamping_deadline =date("Y-m-d",strtotime($newdate));
							$_documentCollectionModcaption->sort_date =date("Y-m-d",strtotime($newdate));
							$_documentCollectionModcaption->sort_dateBY ="Stamping Deadline";
							$_documentCollectionModcaption->save();
						} 
						 
					 }
				 
				 
			 }
			
		 }
	 }
	 	
				
 }
 
 public function UpdateAppEmpStatus(){
	$appuser=Employee_details::where("offline_status",1)->where("job_function",2)->where("dept_id",36)->whereNull('app_journey_status')->get();
	if($appuser!=''){
		foreach($appuser as $_app){
			$appdata=EmpAppAccess::where("employee_id",$_app->emp_id)->where("passwordStatus",2)->first();
			if($appdata!=''){
				$appprofilepic=EmpAppAccess::where("employee_id",$_app->emp_id)->whereNotNull("pics")->first();
				if($appprofilepic!=''){
					if(($appprofilepic->emirate_id_path_front!='' && $appprofilepic->emirate_id_path_bank!='' ) || $appprofilepic->is_allow_eid==1){
					$Attributes = Attributes::where("status",1)->where("kyc_status",1)->where("kyc_require_status",1)->get();
					if($Attributes!=''){
						$countvalue=count($Attributes);
						$counter=0;
						foreach($Attributes as $_attribute){
								$empcontact = Employee_attribute::where("emp_id",$_app->emp_id)->where("attribute_code",$_attribute->attribute_code)->first();
								if($empcontact!=''){
									$counter=$counter+1;
								}
							
						}
						if($countvalue==$counter){
							$empOBJ=Employee_details::find($_app->id);
								$empOBJ->app_journey_status = 1;
								$empOBJ->save();
							
						}
					}
					
					}
				}
			}
		}
	}
	echo "Done"; 
 }
 
 public function ExportVisaInProcessData(Request $request){
			 
	        $filename = 'visaindata'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:S1');
			$sheet->setCellValue('A1', 'Cond List  - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Mobie no'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Employee Id'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('job_opening'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Work Location'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Department'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Recruiter Name'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Visa Type'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Visa Stage Name'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Complete Stage'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Transactional Date'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Cost as per HRM '))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('Attrition Status'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('IC/OC'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('DOE/Status Change Date'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Visa Stamping'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('onboard Status'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('onboarding date'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$empdata=DocumentCollectionDetails::where("visa_process_status",4)->where("backout_status",1)->get();
				//print_r($empdata);exit;	 
			$sn = 1;
			foreach ($empdata as $sid) {
				
				//echo $sid."<br>";
				$visaprocess = Visaprocess::where("document_id",$sid->id)->orderBy('id','DESC')->first();
				if($visaprocess!=''){
					$cost=Visaprocess::where("document_id",$sid->id)->sum('cost');
					$fine=Visaprocess::where("document_id",$sid->id)->sum('cost_fine');
					$totalcost=$cost+$fine;
					//echo $sid."<br>";
				$Visaprocesscount=Visaprocess::where("document_id",$sid->id)->get()->count();	
				$visatypeid=$visaprocess->visa_type;
				$visastageid=$visaprocess->visa_stage;
				$trndate= date("d M Y",strtotime($visaprocess->closing_date));
				}
				else{
				$Visaprocesscount=1;	
				$visatypeid='';
				$visastageid='';
				$trndate='';
				$totalcost=0;
				}
				$datastage = VisaStage::where("visa_type",$visatypeid)->where("category",1)->get()->count();
				  
				 
				 $empname=$sid->emp_name;
				 $mobile_no=$sid->mobile_no;
				 
				 $job_opening=$sid->job_opening;
				 $location=$sid->location;
				 $department=$sid->department;
				 
				 $jobOpning=JobOpening::where("id",$job_opening)->first();
				if($jobOpning!=''){
				
				$jobname=$jobOpning->name;
					}
					else{
						$jobname='';
					}
				$Recruite =RecruiterDetails::where("id",$sid->recruiter_name)->first();
			  
				  if($Recruite != '')
				  {
					
				  $recname=$Recruite->name;
				  }
				  else
				  {
				  $recname= '';
				  }	
				  $visaTypeData = visaType::where("id",$visatypeid)->first();
				  if($visaTypeData!=''){
					$visatypename=$visaTypeData->title;  
				  }else{
					 $visatypename=''; 
				  }
				  if($datastage>0){
					  $datastage=$datastage;
				  }
				  else{
					  $datastage=1;
				  }
					$completetotal=100;
					$empiddata=Employee_details::where("document_collection_id",$sid->id)->first();
					 if($empiddata!=''){
						$empid=$empiddata->emp_id; 
					 }else{
					$getEMPVISAINPRO =  EmployeeOnboardLogdata::where("document_id",$sid->id)->first();
					if($getEMPVISAINPRO != '')
					{
						$empid=$getEMPVISAINPRO->emp_id;  
					}
					else
					{
					$empid="NA";  
					}
				 }
				 $visa_stage = VisaStage::where("id",$visastageid)->first();
				 if($visa_stage!=''){
					 $stagename=$visa_stage->stage_name;
				 }else{
					$stagename=''; 
				 }
				 
				 $icoc=$sid->current_visa_status;
				 if($sid->offline_status==1)
					   {
						   $attration="YES";
							
					   }
					   else
					   {
							$attration="NO";
					   }
					   
					   $visapermission=VisaPermission::where("id",1)->first();
					$arraval=explode(",",$visapermission->stageid);
					$visasprocessData = Visaprocess::whereIN("visa_stage",$arraval)->where("document_id",$sid->id)->orderBy('id','DESC')->first();
					if($visasprocessData!=''){
						if($sid->current_visa_status=='Inside Country'){
						$changestatus=date("d M Y",strtotime($visasprocessData->closing_date));
						}
						else{
							$changestatus=date("d M Y",strtotime($sid->onboard_date));
						}
					}
					else{
						$changestatus='';
					}
					$visastampingstage=VisaPermission::where("id",4)->first();
					$arravalst=explode(",",$visastampingstage->stageid);
					$visasprocessDatast = Visaprocess::whereIN("visa_stage",$arravalst)->where("document_id",$sid->id)->orderBy('id','DESC')->first();
					if($visasprocessDatast!=''){
						
						$stampingdate=date("d M Y",strtotime($visasprocessDatast->closing_date));
						
					}
					else{
						$stampingdate='';
					}
					
					if($sid->onboard_status==1){
						$onboard='incomplete';
					}
					else{
						$onboard='complete';
					}
					
					$onboardempdata=EmployeeOnboardData::where("document_id",$sid->id)->first();
					
					if($onboardempdata!=''){
						$EMPID = $onboardempdata->emp_id;
					}
					else{
						$getEMPVISAINPRO =  EmployeeOnboardLogdata::where("document_id",$sid->id)->first();
							if($getEMPVISAINPRO != '')
							{
								$EMPID=$getEMPVISAINPRO->emp_id;  
							}
							else
							{
							$EMPID="NA";  
							}
					}
					if($sid->onboard_date!=''){
					$onboardate=date("d M Y",strtotime($sid->onboard_date));
					}
					else{
					$onboardate='';	
					}
				 $indexCounter++; 	
				 $departmentMod = Department::where("id",$department)->first();
				 $deptname=$departmentMod->department_name;
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $empname)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $mobile_no)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $empid)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $jobname)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $location)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $deptname)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('H'.$indexCounter, $recname)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('I'.$indexCounter, $visatypename)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('J'.$indexCounter, $stagename)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('K'.$indexCounter, $completetotal)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('L'.$indexCounter, $trndate)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('M'.$indexCounter, $totalcost)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('N'.$indexCounter, $attration)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('O'.$indexCounter, $icoc)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('P'.$indexCounter, $changestatus)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('Q'.$indexCounter, $stampingdate)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('R'.$indexCounter, $onboard)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('S'.$indexCounter, $onboardate)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				
				$sn++;
				
			
			}
			
			
			  for($col = 'A'; $col !== 'S'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:S1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','S') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
			
		}
		
		public function ExportCBDData(Request $request){
			 
	        $filename = 'CBDdata'.date("d-m-Y").'.xlsx';
			$currentmonthdate = date("Y-m-d");
			$currentmDate = date('M', strtotime($currentmonthdate));
			$LMoth = date('M', strtotime('first day of -1 month', strtotime($currentmonthdate)));
			$LofMoth = date('M', strtotime('-2 month', strtotime($currentmonthdate)));
			$LofMoth4 = date('M', strtotime('-3 month', strtotime($currentmonthdate)));
			
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:M1');
			$sheet->setCellValue('A1', 'Cond List  - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Employee Id'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('TL name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Performance Month ('.$currentmDate.')'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Bureau_Score ('.$currentmDate.')'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');			
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Performance Month ('.$LMoth.')'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Bureau_Score ('.$LMoth.')'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');			
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Performance Month ('.$LofMoth.')'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Bureau_Score ('.$LofMoth.')'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');			
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Performance Month ('.$LofMoth4.')'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Bureau_Score ('.$LofMoth4.')'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('Range_id '))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			$emptotal=Employee_details::where("job_function",2)->where("offline_status",1)->where("dept_id",49)->get();
			
				//print_r($empdata);exit;	 
			$sn = 1;
			foreach ($emptotal as $sid) {
				
				$toDate1 = date("Y-m-d");
				$fromDate1 = date("Y").'-'.date("m").'-'.'01';
				
				$fromDate2= date('Y-m-01', strtotime('-1 month', strtotime($toDate1)));
				$toDate2= date('Y-m-t', strtotime($fromDate2));
				
				$fromDate3= date('Y-m-01', strtotime('-2 month', strtotime($toDate1)));
				$toDate3= date('Y-m-t', strtotime($fromDate3));
				$fromDate4= date('Y-m-01', strtotime('-3 month', strtotime($toDate1)));
				$toDate4= date('Y-m-t', strtotime($fromDate4));
				
			
				$whereraw1 = "approval_date >= '".$fromDate1."' and approval_date <= '".$toDate1."'";
				$whereraw2 = "approval_date >= '".$fromDate2."' and approval_date <= '".$toDate2."'";
				$whereraw3 = "approval_date >= '".$fromDate3."' and approval_date <= '".$toDate3."'";
				$whereraw4 = "approval_date >= '".$fromDate4."' and approval_date <= '".$toDate4."'";


				$countabcd1= CBDBankMis::whereIn("Status",array("Missing(Approved)","Archive on Approval","Welcome Calling","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw1)->where("employee_id",$sid->emp_id)->get();
				if($countabcd1!='' && count($countabcd1)>0){
				$arrayabcd1=array();
				foreach($countabcd1 as $abcd1){

				$arrayabcd1[]=$abcd1->AECB_Status;

				}
				$abcdlength1=count($arrayabcd1);

				$counabcd1keytvalue=round($abcdlength1/2);
				if($abcdlength1>1){
				$abcd1status= $arrayabcd1[$counabcd1keytvalue-1];
				}
				else{

					//print_r($arrayabcd1);
					$abcd1status= $arrayabcd1[0];
				}
				
				}else{
					$abcd1status='';
				}


				

				$count1= CBDBankMis::whereIn("Status",array("Missing(Approved)","Archive on Approval","Welcome Calling","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw1)->where("employee_id",$sid->emp_id)->get()->count();
				$score1= CBDBankMis::whereIn("Status",array("Missing(Approved)","Archive on Approval","Welcome Calling","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw1)->where("employee_id",$sid->emp_id)->sum('Bureau_Score');
				 if($count1>0 && $score1>0){
				 $score1avg=round($score1/$count1)." AECB_Status=".$abcd1status;
				 }
				 else{
					$score1avg=0; 
				 }

				 $countabcd2= CBDBankMis::whereIn("Status",array("Missing(Approved)","Archive on Approval","Welcome Calling","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw2)->where("employee_id",$sid->emp_id)->get();
				 if($countabcd2!='' && count($countabcd2)>0){
				 $arrayabcd2=array();
				 foreach($countabcd2 as $abcd2){
 
				 $arrayabcd2[]=$abcd2->AECB_Status;
 
				 }
				 $abcdlength2=count($arrayabcd2);
 
				 $counabcd2keytvalue=round($abcdlength2/2);
				 if($abcdlength2>1){
					$abcd2status= $arrayabcd2[$counabcd2keytvalue-1];
				 }else{
					$abcd2status= $arrayabcd2[0]; 
				 }
					
				
				 }
				 else{
					$abcd2status=''; 
				 }
 

				 
				 $count2= CBDBankMis::whereIn("Status",array("Missing(Approved)","Archive on Approval","Welcome Calling","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw2)->where("employee_id",$sid->emp_id)->get()->count();
				$score2= CBDBankMis::whereIn("Status",array("Missing(Approved)","Archive on Approval","Welcome Calling","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw2)->where("employee_id",$sid->emp_id)->sum('Bureau_Score');
				 if($count2>0 && $score2>0){ 
				 $score2avg=round($score2/$count2)." AECB_Status=" .$abcd2status;
				 }
				 else{
					$score2avg=0; 
				 }
				 

				 $countabcd3= CBDBankMis::whereIn("Status",array("Missing(Approved)","Archive on Approval","Welcome Calling","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw3)->where("employee_id",$sid->emp_id)->get();
				if($countabcd3!='' && count($countabcd3)>0){				
				$arrayabcd3=array();
				 foreach($countabcd3 as $abcd3){
 
				 $arrayabcd3[]=$abcd3->AECB_Status;
 
				 }
				 $abcdlength3=count($arrayabcd3);
 
				 $counabcd3keytvalue=round($abcdlength3/2);

				if($abcdlength3>1){
					$abcd3status= $arrayabcd3[$counabcd3keytvalue-1];
				}else{
					$abcd3status= $arrayabcd3[0];
				}
					
 
				 
				}
				else{
					$abcd3status='';
				}
 


				  $count3= CBDBankMis::whereIn("Status",array("Missing(Approved)","Archive on Approval","Welcome Calling","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw3)->where("employee_id",$sid->emp_id)->get()->count();
				$score3= CBDBankMis::whereIn("Status",array("Missing(Approved)","Archive on Approval","Welcome Calling","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw3)->where("employee_id",$sid->emp_id)->sum('Bureau_Score');
				 if($count3>0 && $score3>0){ 
				 $score3avg=round($score3/$count3)." AECB_Status=" .$abcd3status;
				 
				 }
				 else{
					 $score3avg=0;
				 }

				 $countabcd4= CBDBankMis::whereIn("Status",array("Missing(Approved)","Archive on Approval","Welcome Calling","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw4)->where("employee_id",$sid->emp_id)->get();
				 if($countabcd4!='' && count($countabcd4)>0){
				 $arrayabcd4=array();
				 foreach($countabcd4 as $abcd4){
 
				 $arrayabcd4[]=$abcd4->AECB_Status;
 
				 }
				 $abcdlength4=count($arrayabcd4);
 
				 $counabcd4keytvalue=round($abcdlength4/2);


				 if($abcdlength4>1){
					$abcd4status= $arrayabcd4[$counabcd4keytvalue-1];
				 }
				 else{
					$abcd4status= $arrayabcd4[0]; 
				 }
					
 
				
				 }
				 else{
					$abcd4status=''; 
				 }
 

				 
				 $count4= CBDBankMis::whereIn("Status",array("Missing(Approved)","Archive on Approval","Welcome Calling","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw4)->where("employee_id",$sid->emp_id)->get()->count();
				$score4= CBDBankMis::whereIn("Status",array("Missing(Approved)","Archive on Approval","Welcome Calling","Approved","Pending with Onboarder","Pending with COC"))->whereRaw($whereraw4)->where("employee_id",$sid->emp_id)->sum('Bureau_Score');
					if($count4>0 && $score4>0){ 				
				$score4avg=round($score4/$count4)." AECB_Status=" .$abcd4status;
					}
					else{
					$score4avg=0;	
					}
				 
				 
				 $indexCounter++; 	
				 $TLname = Employee_details::where("id",$sid->tl_id)->first();
				 if($TLname!=''){
					 $tl=$TLname->sales_name;
				 }
				 else{
					$tl=''; 
				 }
				 $sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $sid->emp_name)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $sid->emp_id)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $tl)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $count1)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $score1avg)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $count2)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('H'.$indexCounter, $score2avg)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('I'.$indexCounter, $count3)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('J'.$indexCounter, $score3avg)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('K'.$indexCounter, $count4)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('L'.$indexCounter, $score4avg)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('M'.$indexCounter, $sid->range_id)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				

				$sn++;
				
			
			}
			
			
			  for($col = 'A'; $col !== 'M'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:M1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','M') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
			
		}
		
public function updateVintageAsperDisDateCBD()
{
	$departmentD = DepartmentFormEntry::whereIn("form_status",array("Missing(Approved)","Welcome Calling,Archive on Approval","Approved","Pending with Onboarder","Pending with COC"))->where("form_id",2)->whereNull("DVStatus")->whereNotNull("ref_no")->limit(1000)->get();
	/* echo "<pre>";
print_r($departmentD);
exit;	 */
	foreach($departmentD as $dd)
	{
		
		if($dd->emp_id != NULL && $dd->emp_id != '' && $dd->approval_date != NULL && $dd->approval_date != '')
		{
					$employee_id = trim($dd->emp_id);
					if($employee_id != '' && $employee_id != NULL)
					{
						$employeeData = Employee_details::where("emp_id",$employee_id)->first();
						if($employeeData != '')
						{
							$empId = $employeeData->emp_id;
							$deptId = $employeeData->dept_id;
							$empAttr = Employee_attribute::where("emp_id",$empId)->where("attribute_code","DOJ")->first();
							if($empAttr != '')
							{
								$salesTimeValue = $dd->approval_date;
								
								$dojEmp = $empAttr->attribute_values;
								if($dojEmp != '' && $dojEmp != NULL)
								{
									$doj = str_replace("/","-",$dojEmp);//exit;
									
									//$date1 = date("Y-m-d",strtotime($doj));
									$daysInterval = abs(strtotime($salesTimeValue)-strtotime($doj))/ (60 * 60 * 24);
									$agentPUpdate = DepartmentFormEntry::find($dd->id);
									$agentPUpdate->vintage_disbursal = $daysInterval;
									
									$agentPUpdate->DVStatus =3;
									$agentPUpdate->save();
									
								}
							}								
							
						}
					}
		}
	}
	echo "done";
	exit;
}


public function UpdateTimeRangeDisDateCBD(){
			$data=WorkTimeRange::get();
			foreach($data as $_time){
					$range=$_time->range;
					$rangedata=explode('-',$range);
					//print_r($rangedata);

					$whereraw='vintage_disbursal >='.$rangedata[0].' and vintage_disbursal <='.$rangedata[1].' And DVStatus = 3';
					$PayoutData =DepartmentFormEntry::whereRaw($whereraw)->get();
					foreach($PayoutData as $_newdata){
						$updateMod = DepartmentFormEntry::find($_newdata->id);
						$updateMod->range_disbursal=$_time->id;
						$updateMod->DVStatus=4;
						$updateMod->save();
					}
					
				
			}
			echo "done";
			exit;
			}

public function UpdateDocmentcollectionOfflineStatus(){
	$docdata = DocumentCollectionDetails::whereIn("visa_process_status",array(2,3,4))->get();
	
	if($docdata!=''){
		foreach($docdata As $_data){
			$empuser=Employee_details::where("offline_status",2)->where("document_collection_id",$_data->id)->first();
			if($empuser!=''){
				$documentCollectionMod = DocumentCollectionDetails::find($_data->id);
				$documentCollectionMod->offline_status =1;
				if($documentCollectionMod->save()){
					//echo $_data->id."<br>";
				}
			}
		}
	}
	echo "Data Update";
	
	
}

/*public function MasterEmployeeMainlandExport(Request $request){
			 
	       $filename = 'testdata'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:Q1');
			$sheet->setCellValue('A1', 'Cond List  - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Person Code'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Person Name'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Job'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Passport Detail'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Passport'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('FORMULA'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Detail'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Card Detail'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Card no'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Card Name'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Card date'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('LABOUR CARD NUMBER '))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('COMPANY CODE'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('COMPANY NAME'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('EMP ID'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('Attration'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$empdata=MasterEmployeeMainland::get();
				//print_r($empdata);exit;	 
			$sn = 1;
			foreach ($empdata as $sid) {
				
				$pcode=$sid->Person_Code;
				$pnmae=$sid->Person_Name;
				$Job=$sid->Job;
				$Passport_Detail=$sid->Passport_Detail;
				$Passport=$sid->Passport;
				$formula=$sid->formula;
				$country_Detail=$sid->country_Detail;
				$Card_Detail=$sid->Card_Detail;
				
				$carddata=explode(' ',$Card_Detail);
				//print_r($carddata);
				if(count($carddata)>1){
				$newarray=explode("\n",$carddata[0]);
				$card=$newarray[0];
				
				$name=$newarray[1]." ".$carddata[1]." ".$carddata[count($carddata)-2];	
				$date=$carddata[count($carddata)-1];
				$dd=explode("/",$date);
				$finaldate=	$dd[2]."-".$dd[1]."-".$dd[0];			
				$date=date("Y-m-d",strtotime($finaldate));
				
				}
				else{
				$newarray=explode("\n",$carddata[0]);
				$card=$newarray[0];
				$name='';
				//echo $newarray[1];exit;
				if(is_numeric($newarray[1])){
					//echo $newarray[1];exit;
				$name=$newarray[1];	
				$date='';
				}else{
					//echo "bye";exit;
				$dd=explode("/",$newarray[1]);
				$finaldate=	$dd[2]."-".$dd[1]."-".$dd[0];			
				$date=date("Y-m-d",strtotime($finaldate));	
				}	
				}
				$labour_card=$sid->labour_card;
				$company_code=$sid->company_code;
				$company_name=$sid->company_name;
				
				$montdata=MonthWiseData::where("personal_no",$sid->Person_Code)->first();
				if($montdata!=''){
					$emp=$montdata->emp_id;
					$offline=EmpOffline::where("emp_id",$montdata->emp_id)->first();
					if($offline!=""){
						$attration="Yes";
					}else{
						$attration="NO";
					}
					
				}
				else{
					$emp='';
					$attration='';
				}				
					
					
					
					
				 $indexCounter++; 	
				$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $pcode)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $pnmae)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $Job)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $Passport_Detail)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $Passport)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $formula)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('H'.$indexCounter, $country_Detail)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('I'.$indexCounter, $Card_Detail)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('J'.$indexCounter, $card)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('K'.$indexCounter, $name)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('L'.$indexCounter, $date)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('M'.$indexCounter, $labour_card)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('N'.$indexCounter, $company_code)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('O'.$indexCounter, $company_name)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('P'.$indexCounter, $emp)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('Q'.$indexCounter, $attration)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				

				$sn++;
				
			
			}
			
			
			  for($col = 'A'; $col !== 'Q'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:Q1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','Q') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
			
		}*/
	public function UpdateWPSMothdata(){
		
		$maindata=MasterEmployeeMainland::get();
		
		foreach($maindata as $_main){
		$montdata=MonthWiseData::where("personal_no",$_main->Person_Code)->first();	
		if($montdata!=''){
			$empdata=Employee_details::where("emp_id",$montdata->emp_id)->first();
			if($empdata!=''){
				/*$empAttrExistpcode = Employee_attribute::where("emp_id",$empdata->emp_id)->where("attribute_code","PP_NO")->first();
					if($empAttrExistpcode != '' && $empAttrExistpcode->attribute_value !='')
					{
						//$updateEmpAttr = Employee_attribute::find($empAttrExist->id);
						
					}
					else
					{
						$updateEmpAttr = new Employee_attribute();
						$updateEmpAttr->dept_id = $empdata->dept_id;
						$updateEmpAttr->emp_id = $empdata->emp_id;
						$updateEmpAttr->attribute_code = 'PP_NO';
						$updateEmpAttr->attribute_values = $_main->Passport;
						$updateEmpAttr->status = 1;
						$updateEmpAttr->save();
					}*/
					$docdata=DocumentCollectionDetails::where("id",$empdata->document_collection_id)->first();
					if($docdata!=''){
						$visatype = Visaprocess::where("document_id",$docdata->id)->orderBy('id','DESC')->first();
						if($visatype!=''){
							$existCATEGORY = VisaDetails::where("document_collection_id",$docdata->id)->where("visa_type_id",$visatype->visa_type)->where("attribute_code",173)->first();
							if($existCATEGORY != '' && $existCATEGORY->attribute_value!='')
							{
								//$objCATEGORY= VisaDetails::find($existCATEGORY->id);
							}
							else
							{
								$objCATEGORY= new VisaDetails();
								$objCATEGORY->document_collection_id = $docdata->id;
								$objCATEGORY->visa_type_id = $visatype->visa_type;
								$objCATEGORY->attribute_code = 173;
								$objCATEGORY->attribute_value = $_main->Person_Code;
								$objCATEGORY->save();
							}
							
						}
						
						
					}
			
					
					
				
			}
		}
			
		}
	}
public function MasterEmployeeMainlandExport(Request $request){
			 
	       $filename = 'offboardmatchdata'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AA1');
			$sheet->setCellValue('A1', 'Cond List  - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('S.NO.'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('employee_id'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('EMPLOYEE_NAME'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('CONTACT_NUMBER'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('OFF_BOARDING_DATE'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');	
			$sheet->setCellValue('F'.$indexCounter, strtoupper('ONBOARDING_DATE'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('LAST_WORKING_DATE'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('DATE_OF_RESIGN_TERMINATION'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('DEPARTMENT'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('DESIGNATION'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('TL_NAME'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('STATUS'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('TENURE '))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('DATE_OF_JOING'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('VISA_EXPENS'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('RECRUITER'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('INTERVIEW_FINAL_DISCUSSION'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('R'.$indexCounter, strtoupper('RANGE_ID'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('SUGGESTION'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('VISA_CANCEL_STATUS'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('U'.$indexCounter, strtoupper('VISA_TYPE'))->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('V'.$indexCounter, strtoupper('FNF_STATUS'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('W'.$indexCounter, strtoupper('FINANCE_STATUS'))->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('X'.$indexCounter, strtoupper('VISA_DEADLINE_MISSED_FLAG'))->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Y'.$indexCounter, strtoupper('VISA_DEADLINE_MISSED'))->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Z'.$indexCounter, strtoupper('PASSPORT_NO'))->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('AA'.$indexCounter, strtoupper('Match with onboard'))->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$empdata=OffbordMatchData::get();
				//print_r($empdata);exit;	 
			$sn = 1;
			foreach ($empdata as $sid) {
				
				$employee_id=$sid->employee_id;
				$EMPLOYEE_NAME=$sid->EMPLOYEE_NAME;
				$CONTACT_NUMBER=$sid->CONTACT_NUMBER;
				$OFF_BOARDING_DATE=$sid->OFF_BOARDING_DATE;
				$ONBOARDING_DATE=$sid->ONBOARDING_DATE;
				$LAST_WORKING_DATE=$sid->LAST_WORKING_DATE;
				$DATE_OF_RESIGN_TERMINATION=$sid->DATE_OF_RESIGN_TERMINATION;
				$DEPARTMENT=$sid->DEPARTMENT;
				
				
				$DESIGNATION=$sid->DESIGNATION;
				$TL_NAME=$sid->TL_NAME;
				$STATUS=$sid->STATUS;
				$TENURE=$sid->TENURE;
				$DATE_OF_JOING=$sid->DATE_OF_JOING;
				$VISA_EXPENS=$sid->VISA_EXPENS;
				$RECRUITER=$sid->RECRUITER;
				$INTERVIEW_FINAL_DISCUSSION=$sid->INTERVIEW_FINAL_DISCUSSION;
				$RANGE_ID=$sid->RANGE_ID;
				$SUGGESTION=$sid->SUGGESTION;
				$VISA_CANCEL_STATUS=$sid->VISA_CANCEL_STATUS;
				
				$VISA_TYPE=$sid->VISA_TYPE;
				$FNF_STATUS=$sid->FNF_STATUS;
				$FINANCE_STATUS=$sid->FINANCE_STATUS;
				$VISA_DEADLINE_MISSED_FLAG=$sid->VISA_DEADLINE_MISSED_FLAG;
				$VISA_DEADLINE_MISSED=$sid->VISA_DEADLINE_MISSED;
				$PASSPORT_NO=$sid->PASSPORT_NO;
				
				
				
				$montdata=OnbordMatchData::where("employee_id",$sid->employee_id)->first();
				if($montdata!=''){
					$matchdata="Match";
					
				}
				else{
					$matchdata="Not Match";
				}				
					
					
					
					
				 $indexCounter++; 	
				$sheet->setCellValue('A'.$indexCounter, $sn)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $employee_id)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $EMPLOYEE_NAME)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $CONTACT_NUMBER)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $OFF_BOARDING_DATE)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $ONBOARDING_DATE)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $LAST_WORKING_DATE)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('H'.$indexCounter, $DATE_OF_RESIGN_TERMINATION)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('I'.$indexCounter, $DEPARTMENT)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('J'.$indexCounter, $DESIGNATION)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('K'.$indexCounter, $TL_NAME)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('L'.$indexCounter, $STATUS)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('M'.$indexCounter, $TENURE)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('N'.$indexCounter, $DATE_OF_JOING)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('O'.$indexCounter, $VISA_EXPENS)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('P'.$indexCounter, $RECRUITER)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('Q'.$indexCounter, $INTERVIEW_FINAL_DISCUSSION)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				
				$sheet->setCellValue('R'.$indexCounter, $RANGE_ID)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('S'.$indexCounter, $SUGGESTION)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('T'.$indexCounter, $VISA_CANCEL_STATUS)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('U'.$indexCounter, $VISA_TYPE)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('V'.$indexCounter, $FNF_STATUS)->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('W'.$indexCounter, $FINANCE_STATUS)->getStyle('W'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('X'.$indexCounter, $VISA_DEADLINE_MISSED_FLAG)->getStyle('X'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('Y'.$indexCounter, $VISA_DEADLINE_MISSED)->getStyle('Y'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				
				$sheet->setCellValue('Z'.$indexCounter, $PASSPORT_NO)->getStyle('Z'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				
				$sheet->setCellValue('AA'.$indexCounter, $matchdata)->getStyle('AA'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');				

				$sn++;
				
			
			}
			
			
			  for($col = 'A'; $col !== 'AA'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:AA1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','AA') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
			
		}
	

}
