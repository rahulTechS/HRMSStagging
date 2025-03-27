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
use App\Models\Attribute\MashreqCardsLogs;
use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use App\Models\Common\MashreqLoginMIS;
use App\Models\Common\MashreqBookingMIS;
use App\Models\Common\MashreqBankMIS;
use App\Models\Common\MashreqMTDMIS;
use App\Models\Common\MashreqMasterMIS;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Http\Controllers\Attribute\DepartmentFormController;

use Session;

class FiltersMController extends Controller
{
   
 public function filterrefNOSuggestion(Request $request)
 {
	$refno = $request->refno;
	$mashreqData = MashreqLoginMIS::where("ref_no","like","%".$refno."%")->select("id","ref_no")->get();
	return view("Attribute/Mashreq/filterrefNOSuggestion",compact('mashreqData'));
 }
 
 public function filterCustomerNameSuggestion(Request $request)
 {
	 $cusName = $request->cusName;
	 $mashreqData = MashreqLoginMIS::where("customer_name","like","%".$cusName."%")->select("id","customer_name")->get();
	 return view("Attribute/Mashreq/filterCustomerNameSuggestion",compact('mashreqData'));
 }
 
 public function filterCustomerNameSuggestionUpdate(Request $request)
 {
	 $cusName = $request->cusName;
	 $values = $request->values;
	 $valuesArray = explode(",",$values);
	 $mashreqData = MashreqLoginMIS::where("customer_name","like","%".$cusName."%")->select("id","customer_name")->get();
	 return view("Attribute/Mashreq/filterCustomerNameSuggestionUpdate",compact('mashreqData','valuesArray'));
 }
  public function filterrefNOSuggestionUpdate(Request $request)
 {
	$refno = $request->refno;
		 $values = $request->values;
	 $valuesArray = explode(",",$values);
	$mashreqData = MashreqLoginMIS::where("ref_no","like","%".$refno."%")->select("id","ref_no")->get();
	return view("Attribute/Mashreq/filterrefNOSuggestionUpdate",compact('mashreqData','valuesArray'));
 }
 
 public function updateRemarkInner(Request $request)
 {
	 $internalID = $request->internalID;
	 $formEntryInternal = DepartmentFormEntry::where("id",$internalID)->first();
	 return view("Attribute/Mashreq/updateRemarkInner",compact('formEntryInternal'));
 }
 
 public function updateFinalRemarks(Request $request)
 {
	
	 $postParameters = $request->input();
	 $row_id = $postParameters['row_id'];
	  
	  $oldValue = DepartmentFormEntry::where("id",$row_id)->first()->remarks;
	 
	 $updateMod = DepartmentFormEntry::find($row_id);
	  $updateMod->remarks = $postParameters['remarks'];
	  if($updateMod->save())
	  {
		  $childData = DepartmentFormChildEntry::where("parent_id",$row_id)->where("attribute_code","remarks")->first();
		  if($childData != '')
		  {
			  $updateChild =DepartmentFormChildEntry::find($childData->id);
			   $updateChild->attribute_value = $postParameters['remarks'];
			   $updateChild->save();
			   
		  }
		  
		  /**
		  *log
		  */
		  $empsessionIdGet=$request->session()->get('EmployeeId');
		  $logAddMod = new MashreqCardsLogs();
		   $logAddMod->internal_mis_id = $row_id;
		   $logAddMod->value = 'remarks';
		   $logAddMod->old_value = $oldValue;
		   $logAddMod->new_value = $postParameters['remarks'];
		   $logAddMod->updated_by = $empsessionIdGet;
		   $logAddMod->save();
		  /**
		  *log
		  */
	  }
	echo "done";
	exit;
 }

 public function updateLoginInner(Request $request)
 {
	 $internalID = $request->internalID;
	 $application_status = MashreqLoginMIS::where("application_status","!=",'')->groupBy('application_status')
		->selectRaw('count(*) as total, application_status')
		->get();
	 $bureau_segmentation = MashreqLoginMIS::where("bureau_segmentation","!=",'')->groupBy('bureau_segmentation')
		->selectRaw('count(*) as total, bureau_segmentation')
		->get();

	 $formEntryInternal = DepartmentFormEntry::where("id",$internalID)->first();
	 return view("Attribute/Mashreq/updateLoginInner",compact('formEntryInternal','application_status','bureau_segmentation'));
 }

 public function updateLoginInnerBooking(Request $request)
 {
	 $internalID = $request->internalID;
	 $application_status = MashreqLoginMIS::where("application_status","!=",'')->groupBy('application_status')
		->selectRaw('count(*) as total, application_status')
		->get();
	 $bureau_segmentation = MashreqLoginMIS::where("bureau_segmentation","!=",'')->groupBy('bureau_segmentation')
		->selectRaw('count(*) as total, bureau_segmentation')
		->get();

	 $formEntryInternal = MashreqBookingMIS::where("id",$internalID)->first();
	 return view("Attribute/Mashreq/updateLoginInnerBooking",compact('formEntryInternal','application_status','bureau_segmentation'));
 }
 
 public function updateFinalLogin(Request $request)
 {
	
	 $postParameters = $request->input();
	 $row_id = $postParameters['row_id']; 
	 $applicationid = $postParameters['applicationid']; 
	 if(trim($postParameters['applicationid'])=='' || trim($postParameters['ref_no'])=='')
	 {
		 echo "error";
		 exit;
	 }

	 if(trim($row_id)!='')
	 {	   
	 
	  $updateMod = DepartmentFormEntry::find($row_id);
	  $updateMod->application_id = $postParameters['applicationid'];
	  $updateMod->save();
	 }

	  $entry_obj = new MashreqLoginMIS();
	  $entry_obj->ref_no = $postParameters['ref_no']; 
	  $entry_obj->emp_id = $postParameters['emp_id'];	 
	  $entry_obj->team = $postParameters['team'];
	  $entry_obj->emp_name = $postParameters['emp_name'];
	  $entry_obj->customer_name = $postParameters['customer_name'];
	  $entry_obj->customer_mobile = $postParameters['customer_mobile'];
	  $entry_obj->applicationid = $postParameters['applicationid'];
	  $entry_obj->seller_id = $postParameters['seller_id'];
	  $entry_obj->agent_full_name = $postParameters['agent_full_name'];
	  $entry_obj->application_date = $postParameters['application_date1']?date('Y-m-d',(strtotime($postParameters['application_date1']))):'0000-00-00';
	  $entry_obj->application_status = $postParameters['application_status'];
	  $entry_obj->cif = $postParameters['cif'];
	  $entry_obj->bureau_score = $postParameters['bureau_score'];
	  $entry_obj->bureau_segmentation = $postParameters['bureau_segmentation'];
	  $entry_obj->card_type = $postParameters['card_type'];
	  $entry_obj->mrs_score = $postParameters['mrs_score'];
	  $entry_obj->save();
	  $insert_id = $entry_obj->id;
	  
	echo "done";
	exit;
 }

 public function updateFinalLoginBooking(Request $request)
 {
	
	 $postParameters = $request->input();
	 $row_id = $postParameters['row_id']; 
	 $applicationid = $postParameters['applicationid']; 
	 if(trim($postParameters['applicationid'])=='' || trim($postParameters['ref_no'])=='')
	 {
		 echo "error";
		 exit;
	 }
	 
	 if(trim($applicationid)!='')
	 {	   
	 
	  $updateMod1 = MashreqBookingMIS::find($row_id);
	  $updateMod1->ref_no = $postParameters['ref_no'];
	  $updateMod1->save();
	 }

	  $entry_obj = new MashreqLoginMIS();
	  $entry_obj->ref_no = $postParameters['ref_no']; 
	  $entry_obj->emp_id = $postParameters['emp_id'];	 
	  $entry_obj->team = $postParameters['team'];
	  $entry_obj->emp_name = $postParameters['emp_name'];
	  $entry_obj->customer_name = $postParameters['customer_name'];
	  $entry_obj->customer_mobile = $postParameters['customer_mobile'];
	  $entry_obj->applicationid = $postParameters['applicationid'];
	  $entry_obj->seller_id = $postParameters['seller_id'];
	  $entry_obj->agent_full_name = $postParameters['agent_full_name'];
	  $entry_obj->application_date = $postParameters['application_date1']?date('Y-m-d',(strtotime($postParameters['application_date1']))):'0000-00-00';
	  $entry_obj->application_status = $postParameters['application_status'];
	  $entry_obj->cif = $postParameters['cif'];
	  $entry_obj->bureau_score = $postParameters['bureau_score'];
	  $entry_obj->bureau_segmentation = $postParameters['bureau_segmentation'];
	  $entry_obj->card_type = $postParameters['card_type'];
	  $entry_obj->mrs_score = $postParameters['mrs_score'];
	  $entry_obj->save();
	  $insert_id = $entry_obj->id;
	  
	echo "done";
	exit;
 }
 
}
