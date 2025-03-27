<?php

namespace App\Http\Controllers\Onboarding;

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
use App\Models\Onboarding\IncentiveLetterDetails;
use Illuminate\Support\Facades\Validator;
use  App\Models\Attribute\AttributeType;
use App\Models\Offerletter\OfferletterDetails;
use App\Models\Visa\visaType;
use App\Models\Visa\VisaStage;
use App\Models\Visa\Visaprocess;
use App\Models\Onboarding\TrainingProcess;
use UserPermissionAuth;
use App\Models\Entry\Employee;
use App\Models\Employee\Employee_details;
use App\Models\Job\JobOpening;
use App\Models\Onboarding\DocumentVisaStageStatus;



class OnboardingController extends Controller
{
    
       public function documentcollection(Request $req)
	   {
		  
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		if(!empty($req->session()->get('serialized_id')))
			{
			
				$serialized_id = $req->session()->get('serialized_id');
				$filterList['serialized_id'] = $serialized_id;
				$documentCollectiondetails = $documentCollectiondetails->where("serialized_id","like",$serialized_id."%");
			}
			
		if(!empty($req->session()->get('emp_name')))
			{
			
				$emp_name = $req->session()->get('emp_name');
				$filterList['emp_name'] = $emp_name;
				$documentCollectiondetails = $documentCollectiondetails->where("emp_name","like",$emp_name."%");
			}
		
			if(!empty($req->session()->get('department')))
			{
			
				$department = $req->session()->get('department');
				$filterList['deptID'] = $department;
				$documentCollectiondetails = $documentCollectiondetails->where("department",$department);
			}	
		if(!empty($req->session()->get('caption')))
			{
			
				$caption = $req->session()->get('caption');
				$filterList['caption'] = $caption;
				$documentCollectiondetails = $documentCollectiondetails->where("caption",$caption);
			}	
		if(!empty($req->session()->get('status')))
			{
			
				$status = $req->session()->get('status');
				$filterList['status'] = $status;
				$documentCollectiondetails = $documentCollectiondetails->where("status",$status);
			}		
		if(!empty($req->session()->get('designation')))
			{
			
				$designation = $req->session()->get('designation');
				$filterList['designationID'] = $designation;
				$documentCollectiondetails = $documentCollectiondetails->where("designation",$designation);
			}		
			if(!empty($req->session()->get('visa_process_status')))
			{
			
				$visa_process_status = $req->session()->get('visa_process_status');
				$filterList['visa_process_status'] = $visa_process_status;
				$documentCollectiondetails = $documentCollectiondetails->where("visa_process_status",$visa_process_status);
			}				
			$documentCollectiondetails = $documentCollectiondetails->get();
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("Onboarding/documentcollection",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','filterList','salaryBreakUpdetails'));
	   }
	   
	   
	   
	   public function documentcollection1(Request $req)
	   {
		  
		$departmentLists =  Department::where("status",1)->orderBy("id","DESC")->get();
		$productDetails =  Product::where("status",1)->orderBy('id','DESC')->get();
		$designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		$documentCollectiondetails = DocumentCollectionDetails::orderBy("id","DESC");
		$filterList = array();
		$filterList['deptID'] = '';
		$filterList['productID'] = '';
		$filterList['designationID'] = '';
		$filterList['emp_name'] = '';
		$filterList['caption'] = '';
		$filterList['status'] = '';
		$filterList['serialized_id'] = '';
		$filterList['visa_process_status'] = '';
		
		if(!empty($req->session()->get('serialized_id')))
			{
			
				$serialized_id = $req->session()->get('serialized_id');
				$filterList['serialized_id'] = $serialized_id;
				$documentCollectiondetails = $documentCollectiondetails->where("serialized_id","like",$serialized_id."%");
			}
			
		if(!empty($req->session()->get('emp_name')))
			{
			
				$emp_name = $req->session()->get('emp_name');
				$filterList['emp_name'] = $emp_name;
				$documentCollectiondetails = $documentCollectiondetails->where("emp_name","like",$emp_name."%");
			}
		
			if(!empty($req->session()->get('department')))
			{
			
				$department = $req->session()->get('department');
				$filterList['deptID'] = $department;
				$documentCollectiondetails = $documentCollectiondetails->where("department",$department);
			}	
		if(!empty($req->session()->get('caption')))
			{
			
				$caption = $req->session()->get('caption');
				$filterList['caption'] = $caption;
				$documentCollectiondetails = $documentCollectiondetails->where("caption",$caption);
			}	
		if(!empty($req->session()->get('status')))
			{
			
				$status = $req->session()->get('status');
				$filterList['status'] = $status;
				$documentCollectiondetails = $documentCollectiondetails->where("status",$status);
			}		
		if(!empty($req->session()->get('designation')))
			{
			
				$designation = $req->session()->get('designation');
				$filterList['designationID'] = $designation;
				$documentCollectiondetails = $documentCollectiondetails->where("designation",$designation);
			}		
			if(!empty($req->session()->get('visa_process_status')))
			{
			
				$visa_process_status = $req->session()->get('visa_process_status');
				$filterList['visa_process_status'] = $visa_process_status;
				$documentCollectiondetails = $documentCollectiondetails->where("visa_process_status",$visa_process_status);
			}				
			$documentCollectiondetails = $documentCollectiondetails->get();
		
		
		 $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		return view("Onboarding/documentcollection",compact('departmentLists','productDetails','designationDetails','documentCollectiondetails','filterList','salaryBreakUpdetails'));
	   }
	   
	   
	    public function addDocumentCollection(Request $request)
	   {
		   $departmentDetails =  Department::where("status",1)->orderBy("id","DESC")->get();
		   $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		   $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		   $hiringSourceList = HiringSourceDetails::where("status",1)->orderBy("id","DESC")->get();
		   $recruiterList = RecruiterDetails::where("status",1)->orderBy("id","DESC")->get();
		    $jobOpeningList = JobOpening::where("status",1)->orderBy("id","DESC")->get();
			return view("Onboarding/adddocumentcollection",compact('departmentDetails','designationDetails','salaryBreakUpdetails','hiringSourceList','recruiterList','jobOpeningList'));
	   }
	  public function getCaptionOfSalaryBreakupforDocument(Request $request)
	   {
		   $deptId = $request->deptId;
		   $designId = $request->designId;
		   $salaryDetails =  SalaryBreakup::where("dept_id",$deptId)->where("designation",$designId)->where("status",1)->get();
		   return view("Onboarding/getCaptionOfSalaryBreakup",compact('salaryDetails'));
	   }
	 function getDesignationOfDocumentation(Request $request)
	   {
		     $deptId = $request->deptId;
			  $designationDetails =  Designation::where("status",1)->where("department_id",$deptId)->orderBy('id','DESC')->get();
			  return view("Onboarding/getDesignationOfDocumentation",compact('designationDetails'));
	   }
	   function getDesignationOfDocumentationList(Request $request)
	   {
		     $deptId = $request->deptId;
			  $designationDetails =  Designation::where("status",1)->where("department_id",$deptId)->orderBy('id','DESC')->get();
			  return view("Onboarding/getDesignationOfDocumentationList",compact('designationDetails'));
	   }
	   
	  public function getSalaryBreakupDocument(Request $request)
	   {
		     $deptId = $request->deptId;
	   $designId = $request->designId;
	   $caption = $request->cap;
	   $salaryDetails =  SalaryBreakup::where("dept_id",$deptId)->where("designation",$designId)->where("caption",$caption)->first();
	  
	   return view("Onboarding/getSalaryBreakup",compact('salaryDetails'));
	   }
	   
	   public function generatedocumentCollectionPost(Request $request)
	   {
		  
		   $selectedFilter = $request->input();
		   $documentCollectionModel = new DocumentCollectionDetails();
		   $documentCollectionModel->emp_name =  $selectedFilter['documentCollection']['emp_name'];
		   $documentCollectionModel->mobile_no =  $selectedFilter['documentCollection']['mobile_no'];
		   $documentCollectionModel->email =  $selectedFilter['documentCollection']['email'];
		   $documentCollectionModel->hiring_source =  $selectedFilter['documentCollection']['hiring_source'];
		   $documentCollectionModel->recruiter_name =  $selectedFilter['documentCollection']['recruiter_name'];
		   $documentCollectionModel->job_opening =  $selectedFilter['documentCollection']['job_opening'];
		   $documentCollectionModel->designation =  $selectedFilter['documentCollection']['designation'];
		   $documentCollectionModel->department =  $selectedFilter['documentCollection']['department'];
		   $documentCollectionModel->caption =  $selectedFilter['documentCollection']['caption'];
		   $documentCollectionModel->monthly_package =  $selectedFilter['documentCollection']['monthly_package'];
		   $documentCollectionModel->package_id =  $selectedFilter['documentCollection']['package_id'];
		   $documentCollectionModel->location =  $selectedFilter['documentCollection']['location'];
		   
		   $documentCollectionModel->status =  1;
		   $documentCollectionModel->visa_process_status =  1;
		   $documentCollectionModel->training_process_status =  1;
		   $documentCollectionModel->kyc_status =  1;
		   $documentCollectionModel->update_offer_letter_allow =  1;
		   $documentCollectionModel->created_by =  $request->session()->get('EmployeeId');
		   $documentCollectionModel->save();
		   
		   $documentCollectionUpdate = DocumentCollectionDetails::find($documentCollectionModel->id);
		   $documentCollectionUpdate->serialized_id =  'DocCollection-Inprogress-000'.$documentCollectionModel->id;
		   $documentCollectionUpdate->save();
		   $request->session()->flash('message','Document Collection Saved.');
			return redirect('documentcollection');
	   }
	   
	   public function editDocumentCollection(Request $request)
	   {
		   $dCollectionId = $request->dCollectionId;
		   $documentCollectionData = DocumentCollectionDetails::where("id",$dCollectionId)->first();
		  
		   $departmentDetails =  Department::where("status",1)->orderBy("id","DESC")->get();
		   $designationDetails =  Designation::where("status",1)->orderBy('id','DESC')->get();
		   $salaryBreakUpdetails =  SalaryBreakup::where("status",1)->orderBy("id","DESC")->get();
		   $hiringSourceList = HiringSourceDetails::where("status",1)->orderBy("id","DESC")->get();
		   $recruiterList = RecruiterDetails::where("status",1)->orderBy("id","DESC")->get();
		    $jobOpeningList = JobOpening::where("status",1)->orderBy("id","DESC")->get();
			return view("Onboarding/editDocumentCollection",compact('departmentDetails','designationDetails','salaryBreakUpdetails','documentCollectionData','hiringSourceList','recruiterList','jobOpeningList'));
	   }
	   
	   public function editdocumentCollectionPost(Request $request)
	   {
		    $selectedFilter = $request->input();
			
			
			 $selectedFilter = $request->input();
		   $documentCollectionModel = DocumentCollectionDetails::find($selectedFilter['documentCollectionEdit']['id']);
		   $documentCollectionModel->emp_name =  $selectedFilter['documentCollectionEdit']['emp_name'];
		   $documentCollectionModel->mobile_no =  $selectedFilter['documentCollectionEdit']['mobile_no'];
		   $documentCollectionModel->email =  $selectedFilter['documentCollectionEdit']['email'];
		   $documentCollectionModel->hiring_source =  $selectedFilter['documentCollectionEdit']['hiring_source'];
		   $documentCollectionModel->recruiter_name =  $selectedFilter['documentCollectionEdit']['recruiter_name'];
		   $documentCollectionModel->job_opening =  $selectedFilter['documentCollectionEdit']['job_opening'];
		   $documentCollectionModel->department =  $selectedFilter['documentCollectionEdit']['department'];
		    $documentCollectionModel->designation =  $selectedFilter['documentCollection']['designation'];
		   $documentCollectionModel->caption =  $selectedFilter['documentCollection']['caption'];
		   $documentCollectionModel->monthly_package =  $selectedFilter['documentCollection']['monthly_package'];
		   $documentCollectionModel->package_id =  $selectedFilter['documentCollection']['package_id'];
		   $documentCollectionModel->location =  $selectedFilter['documentCollection']['location'];
		   $documentCollectionModel->update_offer_letter_allow =  2;
		 
		   $documentCollectionModel->save();
		   $request->session()->flash('message','Document Collection updated.');
			return redirect('documentcollection');
	   }
		public function appliedFilterOnDocumentCollection(Request $request)
			{
						$selectedFilter = $request->input();
						$request->session()->put('emp_name',$selectedFilter['emp_name']);		
						$request->session()->put('department',$selectedFilter['department']);
						$request->session()->put('caption',$selectedFilter['caption']);
						
						$request->session()->put('designation',$selectedFilter['designation']);
						$request->session()->put('status',$selectedFilter['status']);
						$request->session()->put('serialized_id',$selectedFilter['serialized_id']);
						$request->session()->put('visa_process_status',$selectedFilter['visa_process_status']);
						return redirect('documentcollection');
					
			}
		public function resetDocumentCollectionFilter(Request $request)
		{
					$request->session()->put('emp_name','');		
			
					$request->session()->put('department','');
					$request->session()->put('caption','');
					
					$request->session()->put('designation','');
					$request->session()->put('status','');
					$request->session()->put('serialized_id','');
					$request->session()->put('visa_process_status','');
					$request->session()->flash('message','Filters Reset Successfully.');
					return redirect('documentcollection');
		}
		
		public function deleteDocumentCollection(Request $request)
		{
			$documentCollectionId = $request->documentCollectionId;
			$documentCollectionModel = DocumentCollectionDetails::find($documentCollectionId);
			$documentCollectionModel->delete();
			/* delete From values*/
			$documentValues = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectionId)->get();
			foreach($documentValues as $_values)
			{
				DocumentCollectionDetailsValues::find($_values->id)->delete();
			}
			
			$visas = Visaprocess::where("document_id",$documentCollectionId)->get();
			foreach($visas as $_v)
			{
				Visaprocess::find($_v->id)->delete();
			}
			
			$trainingSets = TrainingProcess::where("document_id",$documentCollectionId)->get();
			foreach($trainingSets as $_t)
			{
				TrainingProcess::find($_t->id)->delete();
			}
			/* delete From values*/
			$request->session()->flash('message','Document Collection Deleted Successfully.');
			return redirect('documentcollection');
		}
		
		public function addCollectionAttributes()
		{
			$attributeTypeDetails = AttributeType::orderBy('attribute_type_id','DESC')->get();
			$deptLists = Department::where("status",1)->orderBy('id','DESC')->get();
			return view("Onboarding/DocCollectionAttributeAddForm",compact('attributeTypeDetails','deptLists'));
		}
		
		public function addDocumentCollectionAttrPost(Request $request)
		{
			$selectedFilterInput = $request->input();
		
			$documentAttributeModel = new DocumentCollectionAttributes();
			$documentAttributeModel->attribute_name = $selectedFilterInput['attribute_name'];
			$documentAttributeModel->attribute_code = $selectedFilterInput['attribute_code'];
			$documentAttributeModel->attrbute_type_id = $selectedFilterInput['attrbute_type_id'];
			if($selectedFilterInput['attrbute_type_id'] == 3)
			{
				$documentAttributeModel->opt = implode(",",$selectedFilterInput['opt']);
			}
			$documentAttributeModel->attribute_requirement = $selectedFilterInput['attribute_requirement'];
			$documentAttributeModel->sort_order = $selectedFilterInput['sort_order'];
			$documentAttributeModel->status = $selectedFilterInput['status'];
			$documentAttributeModel->attribute_area = $selectedFilterInput['attribute_area'];
			$documentAttributeModel->attribute_category = $selectedFilterInput['attribute_category'];
			
			if($selectedFilterInput['attribute_area'] == 'kyc')
			{
				$documentAttributeModel->department_id = $selectedFilterInput['department_id'];
			}
			$documentAttributeModel->save();
			//$request->session()->flash('message','Attribute Saved Successfully.');
            //return redirect('dCollectionAttributes');
			$response['code'] = '200';
			$response['message'] = "Attribute Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
			echo json_encode($response);
			   exit;
		}
		
		
		
		public function dCollectionAttributes(Request $req)
	   {
		 
			return view("Onboarding/DocCollectionAttributes");
	   }
	   public function setOffSetInnerDocAttribute(Request $request)
		{
			$offset = $request->offset;
			$request->session()->put('offset_emp_docattribute_filter',$offset);
		}
		public function dCollectionAttributesList(Request $req)
	   {
		  
			$filterList = array();
			$filterList['attribute_name'] = '';
			$filterList['attrbute_type_id'] = '';
			$filterList['attribute_area'] = '';
			$filterList['department_id'] = '';
			
			if(!empty($req->session()->get('offset_emp_docattribute_filter')))
				{
					$paginationValue = $req->session()->get('offset_emp_docattribute_filter');
				}
				else
				{
					$paginationValue = 10;
				}
				$whereraw='';
				$whereraw1 = '';
				$whereraw='';
				$whereraw1 = '';
				$selectedFilter['docAttribute_name'] = '';
				$selectedFilter['docAttribute_code'] = '';
				$selectedFilter['docAttribute_type'] = '';
				$selectedFilter['docAttribute_area'] = '';
				$selectedFilter['docAttribute_dept'] = '';
				if(!empty($req->session()->get('name_doc_attribute_filter_inner_list')) && $req->session()->get('name_doc_attribute_filter_inner_list') != 'All')
				{
					$name = $req->session()->get('name_doc_attribute_filter_inner_list');
					 $selectedFilter['docAttribute_name'] = $name;
					 if($whereraw == '')
					{
						$whereraw = 'attribute_name = "'.$name.'"';
					}
					else
					{
						$whereraw .= ' And attribute_name = "'.$name.'"';
					}
				}
				if(!empty($req->session()->get('code_doc_attribute_filter_inner_list')) && $req->session()->get('code_doc_attribute_filter_inner_list') != 'All')
				{
					$code = $req->session()->get('code_doc_attribute_filter_inner_list');
					 $selectedFilter['docAttribute_code'] = $code;
					 if($whereraw == '')
					{
						$whereraw = 'attribute_code = "'.$code.'"';
					}
					else
					{
						$whereraw .= ' And attribute_code = "'.$code.'"';
					}
				}
				if(!empty($req->session()->get('area_doc_attribute_filter_inner_list')) && $req->session()->get('area_doc_attribute_filter_inner_list') != 'All')
				{
					$area = $req->session()->get('area_doc_attribute_filter_inner_list');
					 $selectedFilter['docAttribute_area'] = $area;
					 if($whereraw == '')
					{
						$whereraw = 'attribute_area = "'.$area.'"';
					}
					else
					{
						$whereraw .= ' And attribute_area = "'.$area.'"';
					}
				}
				if(!empty($req->session()->get('type_doc_attribute_filter_inner_list')) && $req->session()->get('type_doc_attribute_filter_inner_list') != 'All')
				{
					$type = $req->session()->get('type_doc_attribute_filter_inner_list');
					 $selectedFilter['docAttribute_type'] = $type;
					 if($whereraw == '')
					{
						$whereraw = 'attrbute_type_id = "'.$type.'"';
					}
					else
					{
						$whereraw .= ' And attrbute_type_id = "'.$type.'"';
					}
				}
				if(!empty($req->session()->get('dept_doc_attribute_filter_inner_list')) && $req->session()->get('dept_doc_attribute_filter_inner_list') != 'All')
				{
					$dept = $req->session()->get('dept_doc_attribute_filter_inner_list');
					 $selectedFilter['docAttribute_dept'] = $dept;
					 if($whereraw == '')
					{
						$whereraw = 'department_id = "'.$dept.'"';
					}
					else
					{
						$whereraw .= ' And department_id = "'.$dept.'"';
					}
				}
				$docattributeNameArray = array();
				if($whereraw == '')
				{
				$name = DocumentCollectionAttributes::where("status",1)->get();
				}
				else
				{					
				$name = DocumentCollectionAttributes::whereRaw($whereraw)->where("status",1)->get();					
				}				
				foreach($name as $_name)
				{
					$docattributeNameArray[$_name->attribute_name] = $_name->attribute_name;
				}
				$docattributeCodeArray = array();
				if($whereraw == '')
				{
				$code = DocumentCollectionAttributes::where("status",1)->get();
				}
				else
				{					
				$code = DocumentCollectionAttributes::whereRaw($whereraw)->where("status",1)->get();					
				}				
				foreach($code as $_code)
				{
					$docattributeCodeArray[$_code->attribute_code] = $_code->attribute_code;
				}
				$docattributeareaArray = array();
				if($whereraw == '')
				{
				$area = DocumentCollectionAttributes::where("status",1)->get();
				}
				else
				{					
				$area = DocumentCollectionAttributes::whereRaw($whereraw)->where("status",1)->get();					
				}				
				foreach($area as $_tab)
				{
					if(!empty($_tab->attribute_area)){
					$docattributeareaArray[$_tab->attribute_area] = $_tab->attribute_area;
					}
				}
				$docattributeTypeArray = array();
				if($whereraw == '')
				{
				$type = DocumentCollectionAttributes::where("status",1)->get();
				}
				else
				{					
				$type = DocumentCollectionAttributes::whereRaw($whereraw)->where("status",1)->get();					
				}				
				foreach($type as $_type)
				{
					$docattributeTypeArray[$_type->attrbute_type_id] = $_type->attrbute_type_id;
				}
				$docattributeDptNameArray = array();
				if($whereraw == '')
				{
				$dept = DocumentCollectionAttributes::where("status",1)->get();
				}
				else
				{					
				$dept = DocumentCollectionAttributes::whereRaw($whereraw)->where("status",1)->get();					
				}				
				foreach($dept as $_dept)
				{
					$docattributeDptNameArray[$_dept->department_id] = $_dept->department_id;
				}
				if($whereraw != '')
				{
				
				$documentCollectiondetailsAttr = DocumentCollectionAttributes::whereRaw($whereraw)->where("status",1)->orderBy('id','DESC')->paginate($paginationValue);
				$reportsCount = DocumentCollectionAttributes::whereRaw($whereraw)->where("status",1)->get()->count();
				}
				else{
				$documentCollectiondetailsAttr = DocumentCollectionAttributes::orderBy("id","DESC")->paginate($paginationValue);
				$reportsCount = DocumentCollectionAttributes::get()->count();
				}
		
			$attributeTypeDetails = AttributeType::orderBy('attribute_type_id','DESC')->get();
			$deptLists = Department::where("status",1)->orderBy('id','DESC')->get();
			return view("Onboarding/DocCollectionAttributesList",compact('docattributeTypeArray','docattributeDptNameArray','docattributeareaArray','docattributeCodeArray','selectedFilter','docattributeNameArray','documentCollectiondetailsAttr','filterList','attributeTypeDetails','deptLists','reportsCount','paginationValue'));
	   }
	   
	   public function editDocumentCollectionAttr(Request $request)
	   {
		    $attributeId = $request->attrId;
			$attributeTypeDetails = AttributeType::orderBy('attribute_type_id','DESC')->get();
			$documentCollectionDetails =  DocumentCollectionAttributes::where("id",$attributeId)->first();
			$optionArray = array();
			if($documentCollectionDetails->attrbute_type_id == 3)
			{
				$optionsTxt = $documentCollectionDetails->opt;
				$optionArray = explode(",",$optionsTxt);
			}
			$deptLists = Department::where("status",1)->orderBy('id','DESC')->get();
			return view("Onboarding/UpdateDocCollectionAttributeForm",compact('documentCollectionDetails','attributeTypeDetails','optionArray','deptLists'));
	   }
	   
	   public function uploadDocument(Request $request)
	   {
		   $uploadDetails = array();
		   $id = $request->id; 
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   $documentAttributes = DocumentCollectionAttributes::where("status",1)->where("attribute_area","both")->orWhere("attribute_area","offerletter")->orderBy("sort_order","ASC")->get();
		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
		   }
			
			
		   return view("Onboarding/uploadDocument",compact('documentDetails','documentAttributes','uploadDetails'));
	   }
	   public function getInsidecountryData(Request $request)
	   {
		   $uploadDetails = array();
		   $id = $request->id; 
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   $documentAttributes = DocumentCollectionAttributes::where("attribute_area","insideVisa")->where("status",1)->orderBy("sort_order","ASC")->get();
		   $documentAttributesDetails =DocumentCollectionDetailsValues::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
		   }
			
			
		   return view("Onboarding/InsideuploadDocumentAjax",compact('documentDetails','documentAttributes','uploadDetails'));
	   }
	   
	   public function uploadKYC(Request $request)
	   {
		   $uploadDetails = array();
		   $id = $request->id; 
		   $mode = $request->mode; 
		   $redirectMod = $request->redirectMod;
		   $documentDetails = DocumentCollectionDetails::where("id",$id)->first();
		   
		   $documentAttributes = DocumentCollectionAttributes::where("status",1)->Where("attribute_area","kyc")->where("department_id",$documentDetails->department)->get();
		   $documentAttributesDetails =KycDocuments::where("document_collection_id",$id)->get();
		   foreach($documentAttributesDetails as $_documentCUpload)
		   {
			   $uploadDetails[$_documentCUpload->attribute_code] = $_documentCUpload->attribute_value;
		   }
			
				$empRequiredDetails =  Employee_details::where('document_collection_id',$id)->first();
		   return view("Onboarding/uploadKYC",compact('documentDetails','documentAttributes','uploadDetails','mode','empRequiredDetails','redirectMod'));
	   }
	   
	   public function updateDocumentCollectionAttrPost(Request $request)
	   {
		   $selectedFilterInput = $request->input();
		 	
		   $id =  $selectedFilterInput['collectionAttrId'];
		   $documentAttributeModelUpdate = DocumentCollectionAttributes::find($id);
			$documentAttributeModelUpdate->attribute_name = $selectedFilterInput['attribute_name'];
			$documentAttributeModelUpdate->attribute_code = $selectedFilterInput['attribute_code'];
			$documentAttributeModelUpdate->attrbute_type_id = $selectedFilterInput['attrbute_type_id'];
			if($selectedFilterInput['attrbute_type_id'] == 3)
			{
				$documentAttributeModelUpdate->opt = implode(",",$selectedFilterInput['opt']);
			}
			$documentAttributeModelUpdate->attribute_requirement = $selectedFilterInput['attribute_requirement'];
			$documentAttributeModelUpdate->sort_order = $selectedFilterInput['sort_order'];
			$documentAttributeModelUpdate->status = $selectedFilterInput['status'];
			$documentAttributeModelUpdate->attribute_area = $selectedFilterInput['attribute_area'];
			$documentAttributeModelUpdate->attribute_category = $selectedFilterInput['attribute_category'];
			if($selectedFilterInput['attribute_area'] == 'kyc')
			{
				$documentAttributeModelUpdate->department_id = $selectedFilterInput['department_id'];
			}
			else
			{
				$documentAttributeModelUpdate->department_id = NULL;
			}
			$documentAttributeModelUpdate->save();
			//$request->session()->flash('message','Attribute Updated Successfully.');
            //return redirect('dCollectionAttributes');
			$response['code'] = '200';
			$response['message'] = "Attribute Updated Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
			echo json_encode($response);
			   exit;
	   }
	   
	   public function deleteDocumentCollectionAttr(Request $request)
	   {
		    $attributeId = $request->attrId;
			 $documentAttributeModelUpdate = DocumentCollectionAttributes::find($attributeId);
			 $documentAttributeModelUpdate->delete();
			 //$request->session()->flash('message','Attribute Deleted Successfully.');
             //return redirect('dCollectionAttributes');
			 $response['code'] = '200';
			$response['message'] = "Attribute Deleted Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
			echo json_encode($response);
			   exit;
	   }
	   
	   public function appliedFilterOnDocumentCollectionAttribute(Request $request)
	   {
		   $selectedFilter = $request->input();
		   $request->session()->put('attribute_name',$selectedFilter['attribute_name']);		
		   $request->session()->put('attrbute_type_id',$selectedFilter['attrbute_type_id']);
		   $request->session()->put('attribute_area',$selectedFilter['attribute_area']);
		   if($selectedFilter['attribute_area'] == 'kyc')
		   {
				$request->session()->put('department_id',$selectedFilter['department_id']);
		   }
		   else
		   {
			   $request->session()->put('department_id','');
		   }
		   return redirect('dCollectionAttributes');
	   }
	   
	   public function resetDocumentCollectionFilterAttr(Request $request)
	   {
		   $request->session()->put('attribute_name','');		
		   $request->session()->put('attrbute_type_id','');
		    $request->session()->put('attribute_area','');
			$request->session()->put('department_id','');
		   $request->session()->flash('message','Filters Reset Successfully.');
		   return redirect('dCollectionAttributes');
	   }
	   
	   public function uploadDocumentStart(Request $request)
	   {
		   $selectedFilter = $request->input();
		  
		   $saveData = array();
		  
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		   $status = $selectedFilter['status'];
		   $num = $documentCollectionId;
		    unset($selectedFilter['_token']);
		    unset($selectedFilter['status']);
		    unset($selectedFilter['documentCollectionID']);
		    unset($selectedFilter['_url']);
			
		   
			
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				if($request->file($key))
				{
					
				 $filenameWithExt = $request->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$request->file($key)->getClientOriginalExtension();
				$vKey = $key;
				 $newFileName = $key.'-'.$num.'.'.$fileExtension;
			   
				    if(file_exists(public_path('documentCollectionFiles/'.$newFileName))){

					  unlink(public_path('documentCollectionFiles/'.$newFileName));

					}
				
				/*
				*Updating File Name
				*/
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $request->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				$request->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
				$fileIndex++;
				}
			}
			
			
			
			foreach($selectedFilter as $key=>$value)
			{
				if($value != '')
				{
				$existDocument = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectionId)->where("attribute_code",$key)->first();
				if($existDocument != '')
				{
					$objDocument= DocumentCollectionDetailsValues::find($existDocument->id);
				}
				else
				{
				$objDocument = new DocumentCollectionDetailsValues();	
				}	
				
				$objDocument->document_collection_id = $documentCollectionId;
				$objDocument->attribute_code = $key;
				$objDocument->attribute_value = $value;
				$objDocument->save();
				}
				
			}
			
			foreach($keys as $key)
			{
				if(in_array($key,$listOfAttribute))
				{
					
					$existDocument = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectionId)->where("attribute_code",$key)->first();
					if($existDocument != '')
					{
						$objDocument= DocumentCollectionDetailsValues::find($existDocument->id);
					}
					else
					{
						$objDocument = new DocumentCollectionDetailsValues();
					}
					$objDocument->document_collection_id = $documentCollectionId;
					$objDocument->attribute_code = $key;
					$objDocument->attribute_value = $filesAttributeInfo[$key];
					$objDocument->save();
					
				}
			}
			
		
			/*
			*update Status on main Document Collection table
			*/
			$getExistingStatus = DocumentCollectionDetails::where("id",$documentCollectionId)->first()->status;
			$documentCollectionMod = DocumentCollectionDetails::find($documentCollectionId);
			if($getExistingStatus <=3)
			{
				$documentCollectionMod->status = $status;
				if($status == 3)
				{
					$documentCollectionMod->serialized_id = 'Offerletter-DocCollection-Rejected-000'.$documentCollectionId;
				}
				elseif($status == 2)
				{
					$documentCollectionMod->serialized_id = 'Offerletter-DocCollection-Completed-000'.$documentCollectionId;
					$documentCollectionMod->offer_letter_details_date = date("Y-m-d");
				}
				else
				{
					$documentCollectionMod->serialized_id = 'Offerletter-DocCollection-Inprogress-000'.$documentCollectionId;
				}
				
				$documentCollectionMod->save();
			}
			$request->session()->flash('message','Document Upload Successfully.');
		
		   return redirect('documentcollection');
	   }
	   
	   
	   
	   public function KYCStart(Request $request)
	   {
		   $selectedFilter = $request->input();
		   
		   $saveData = array();
		  
		   
		   $documentCollectionId = $selectedFilter['documentCollectionID'];
		   $kyc_status = $selectedFilter['kyc_status'];
		   $num = $documentCollectionId;
		   $mode = $selectedFilter['mode'];
		   $redirectMod = $selectedFilter['redirectMod'];
		    unset($selectedFilter['_token']);
		    unset($selectedFilter['kyc_status']);
		    unset($selectedFilter['documentCollectionID']);
		    unset($selectedFilter['_url']);
		    unset($selectedFilter['mode']);
		    unset($selectedFilter['redirectMod']);
			
		   
			
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				if($request->file($key))
				{
					
				 $filenameWithExt = $request->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$request->file($key)->getClientOriginalExtension();
				$vKey = $key;
				 $newFileName = 'KYC-'.$key.'-'.$num.'.'.$fileExtension;
			   
				    if(file_exists(public_path('documentCollectionFiles/'.$newFileName))){

					  unlink(public_path('documentCollectionFiles/'.$newFileName));

					}
				
				/*
				*Updating File Name
				*/
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $request->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$request->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
				$fileIndex++;
				}
			}
			
			
			
			foreach($selectedFilter as $key=>$value)
			{
				if($value != '')
				{
				$existDocument = KycDocuments::where("document_collection_id",$documentCollectionId)->where("attribute_code",$key)->first();
				if($existDocument != '')
				{
					$objDocument= KycDocuments::find($existDocument->id);
				}
				else
				{
					$objDocument = new KycDocuments();	
				}	
				
				$objDocument->document_collection_id = $documentCollectionId;
				$objDocument->attribute_code = $key;
				$objDocument->attribute_value = $value;
				$objDocument->save();
				}
				
			}
			
			foreach($keys as $key)
			{
				if(in_array($key,$listOfAttribute))
				{
					
					$existDocument = KycDocuments::where("document_collection_id",$documentCollectionId)->where("attribute_code",$key)->first();
					if($existDocument != '')
					{
						$objDocument= KycDocuments::find($existDocument->id);
					}
					else
					{
						$objDocument = new KycDocuments();
					}
					$objDocument->document_collection_id = $documentCollectionId;
					$objDocument->attribute_code = $key;
					$objDocument->attribute_value = $filesAttributeInfo[$key];
					$objDocument->save();
					
				}
			}
			
		
			/*
			*update Status on main Document Collection table
			*/
			$getExistingStatus = DocumentCollectionDetails::where("id",$documentCollectionId)->first()->kyc_status;
			$documentCollectionMod = DocumentCollectionDetails::find($documentCollectionId);
			if($getExistingStatus <=3)
			{
				$documentCollectionMod->kyc_status = $kyc_status;
				if($kyc_status == 2)
				{
					
					//$documentCollectionMod->offer_letter_details_date = date("Y-m-d");
				}
				else
				{
					//$documentCollectionMod->serialized_id = 'Offerletter-DocCollection-Inprogress-000'.$documentCollectionId;
				}
				
				$documentCollectionMod->save();
			}
			$request->session()->flash('message','KYC Document Upload Successfully.');
			if($mode == 'M')
			{
				return redirect('documentcollection');
			}
			else
			{
				if($redirectMod == 'B')
				{
					$empRequiredDetails =  Employee_details::where('document_collection_id',$documentCollectionId)->first();
					if($empRequiredDetails == '')
					{
						return redirect('documentcollection');
					}
					else
					{
					return redirect('viewEmployeeBoarded/'.$empRequiredDetails->emp_id.'/B');
					}
				}
				else
				{
					
					
						return redirect('listEmp');
					
				}
			}
	   }
	   
	   public static function getHiringSourceName($hiringSourceId)
	   {
		  $source=HiringSourceDetails::where("id",$hiringSourceId)->first();
		  if($source!=''){
			  return $source->name;
		  }
		  else{
			  return '';
		  }
	   }
	   public static function getRecruiterName($recruiterId)
	   {
		   $recdata= RecruiterDetails::where("id",$recruiterId)->first();
		   if($recdata != '')
		  {
		  return $recdata->name;
		  }
		  else
		  {
			  return '';
		  }
	   }
	   public static function getOfferId($documentId)
	   {
		  $offerLetterMod =  OfferletterDetails::where("document_id",$documentId)->first();
		  if($offerLetterMod != '')
		  {
		  return $offerLetterMod->id;
		  }
		  else
		  {
			  return '';
		  }
	   }
	   
	   public function getLocation()
	   {
		   return view("Onboarding/getLocation");
	   }
	   public function incentiveLetter(Request $request)
	   {
		   $documentCollectId = $request->documentCollectionId;
		   //$documentCollectionDetails = IncentiveLetterDetails::where("id",$documentCollectId)->first();
		   $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
		   $departmentId = $documentCollectionDetails->department;
		   $designationId = $documentCollectionDetails->designation;
		   $location = $documentCollectionDetails->location;
		  $incentiveLetterDetails = IncentiveLetterDetails::where("department_id",$departmentId)->where("designation_id",$designationId)->where("location",$location)->first();
		  if($incentiveLetterDetails == '')
		  {
			  $request->session()->flash('message','No incentive letter attached with selected department and designation.');
		
		      return redirect('documentcollection');
		  }
		  else
		  {
			  
			   $pathToIncentiveLetter = $incentiveLetterDetails->path.'/'.$incentiveLetterDetails->location.'/'.$documentCollectId;
			   //return redirect()->to($pathToIncentiveLetter);
			   echo "<script>window.open('".url($pathToIncentiveLetter)."', '_blank')</script>";
			   exit;
		  }
		   
	   }
	   
	    public function checkforIncentiveLetter(Request $request)
		   {
			   $documentCollectId = $request->documentCollectionId;
			   
			   $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
			   $departmentId = $documentCollectionDetails->department;
			   $designationId = $documentCollectionDetails->designation;
			   $location = $documentCollectionDetails->location;
			  $incentiveLetterDetails = IncentiveLetterDetails::where("department_id",$departmentId)->where("designation_id",$designationId)->where("location",$location)->first();
			  if($incentiveLetterDetails == '')
			  {
				   echo "Not Allowed";
			  }
			  else
			  {
				   $pathToIncentiveLetter = $incentiveLetterDetails->path.'/'.$incentiveLetterDetails->location.'/'.$documentCollectId;
				   echo $pathToIncentiveLetter;
				   
			  }
			   
			   exit;
		   }
	   public function collectionDetailsTab1(Request $request)
	   {
		    $documentCollectId = $request->documentCollectionId;
		    $documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectId)->first();
			
			/*
			*upload document values with label
			*start code
			*/
				$documentCollectionValues = DocumentCollectionDetailsValues::where("document_collection_id",$documentCollectId)->get();
				/* echo '<pre>';
				print_r($documentCollectionValues);
				exit; */
				$docCollectionDetails = array();
				foreach($documentCollectionValues as $_docCollectionValue)
				{
					
					$attrId = $_docCollectionValue->attribute_code;
					$docAttributes = DocumentCollectionAttributes::where("id",$attrId)->first();
					$attributeName = $docAttributes->attribute_name.'^'.$docAttributes->attrbute_type_id;
					
					
					$attributeValue = $_docCollectionValue->attribute_value;
					$docCollectionDetails[$attributeName] = $attributeValue;
				}
				
			/*
			*upload document values with label
			*end code
			*/
			$visaProcessLists = Visaprocess::where("document_id",$documentCollectId)->orderBy('id','DESC')->get();
			return view("Onboarding/collectionDetailsTab1",compact('documentCollectionDetails','docCollectionDetails','visaProcessLists'));
	   }
	   
	   public static function getFilterValueName($filterCode,$filterValue)
	   {
		  
		   $returnName = 'no';
		   switch($filterCode)
		   {
			   case 'deptID':
				  $returnName = Department::where("id",$filterValue)->first()->department_name;
				 
			   Break;
			   case 'productID':
			    $returnName = Product::where("id",$filterValue)->first()->product_name;
			   Break;
			   case 'designationID':
				$returnName = Designation::where("id",$filterValue)->first()->name;
				
			   Break;
			   case 'emp_name':
				$returnName = $filterValue;
			   Break;
			   case 'caption':
			  
			   $returnName =$filterValue;
			   Break;
			   case 'status':
				if($filterValue == 1)
				{
					$returnName = 'OfferLetter Document Pending';
				}
				else if($filterValue == 2)
				{
					$returnName = 'Ready for Offer Letter';
				}
				else if($filterValue == 4)
				{
					$returnName = 'Offer Letter Generated';
				}
				else if($filterValue == 5)
				{
					$returnName = 'Signed Offerletter Uploaded';
				}
				else if($filterValue == 6)
				{
					$returnName = 'Visa Document Uploaded';
				}
				else if($filterValue == 7)
				{
					$returnName = 'Ready for Onboarding';
				}
				else if($filterValue == 8)
				{
					$returnName = 'On-boarded';
				}
				else
				{
					$returnName = 'Pending';
				}
			   Break;
			   case 'serialized_id':
				$returnName = $filterValue;
			   Break;
			   case 'visa_process_status':
			   if($filterValue == 1)
				{
					$returnName = 'Pending';
				}
				else if($filterValue == 2)
				{
					$returnName = 'Inprogress';
				}
				else if($filterValue == 4)
				{
					$returnName = 'Completed';
				}
				else
				{
					$returnName = 'Pending';
				}
			   Break;
			   
			   
		   }
		   return $returnName;
	   }
	   
	   public function resetRequestFilterStep(Request $request)
	   {
		   $filtername =  $request->nameFilter;
		    switch($filtername)
		   {
			   case 'deptID':
				   $request->session()->put('department','');
				 
			   Break;
			   
			   case 'productID':
			    $request->session()->put('department','');
			   Break;
			   
			   case 'designationID':
				  $request->session()->put('designation','');
				
			   Break;
			   
			   case 'emp_name':
				  $request->session()->put( $filtername,'');
			   Break;
			   
			   case 'caption':
					$request->session()->put( $filtername,'');
			   Break;
			   
			   case 'status':
				$request->session()->put( $filtername,'');
			   Break;
			   
			   case 'serialized_id':
				$request->session()->put( $filtername,'');
			   Break;
			   
			   case 'visa_process_status':
			  $request->session()->put( $filtername,'');
				
			   Break;
			   
			   
		   }
		  
		    return back();
	   }
	   
	   public static function getDesignationForSelectedDepartment($depId)
	   {
		  
		   return Designation::where("department_id",$depId)->where("status",1)->get();
	   }
	   
	   public static function getCreatedByNameFromId($id)
	   {
		  
		   return Employee::where("id",$id)->first()->fullname;
	   }
	   
	   public function bankCodeGeneration(Request $request)
	   {
		   $documentCollectionID = $request->documentCollectionId;
		   $documentDetails = DocumentCollectionDetails::where("id",$documentCollectionID)->first();
		   return view("Onboarding/bankCodeGeneration",compact('documentDetails'));
	   }
	   public function saveBankCode(Request $request)
	   {
		   $parameterInput = $request->input();
		   $documentCollectionID = $parameterInput['documentCollectionID'];
		   $bankGeneratedCode = $parameterInput['bank_generated_code'];
		   $docCollectionMod = DocumentCollectionDetails::find($documentCollectionID);
		   $docCollectionMod->bank_generated_code=$bankGeneratedCode;
		   $docCollectionMod->status=7;
		   $docCollectionMod->serialized_id = 'ReadyForOnboarding-000'.$documentCollectionID;
		   $docCollectionMod->save();
		   $request->session()->flash('message','Bank Generated Code Saved Successfully.');
		
		   return redirect('documentcollection');
	   }
	   
	   public function finalizationOnboarding(Request $request)
	   {
		    $documentCollectionId = $request->documentCollectionId;
			$documentCollectionDetails = DocumentCollectionDetails::where("id",$documentCollectionId)->first();
		     return view("Onboarding/finalizationOnboarding",compact('documentCollectionId','documentCollectionDetails'));
	   }
	   public static function getJobOpening($jobOpeningId = NULL)
	   {
		   $job=JobOpening::where("id",$jobOpeningId)->first();
				if($job != '')
				   {
					 return $job->name." - ".$job->location;  
				   }
				   else
				   {
						return "--";
				   }
		   
	   }
	   public static function getJobdept($jobOpeningId = NULL)
	   {
		   
		   if($jobOpeningId != NULL)
		   {
			   $data=JobOpening::where("id",$jobOpeningId)->first();
			   
			   $deptId=$data->department;
			  
			   $deptname=Department::where("id",$deptId)->first();
			   
			    return $deptname->department_name;;
		   }
		   else
		   {
			    return "";
		   }
	   }
	   public function filterBydocAttributeName(Request $request)
			{
				
				$name = $request->name;
				$request->session()->put('name_doc_attribute_filter_inner_list',$name);	
			}
		public function filterBydocAttributeCode(Request $request)
			{
				$code = $request->code;
				$request->session()->put('code_doc_attribute_filter_inner_list',$code);	
			}	
		public function filterBydocAttributeArea(Request $request)
			{
				$area = $request->area;
				$request->session()->put('area_doc_attribute_filter_inner_list',$area);	
			}
		public function filterBydocAttributeType(Request $request)
			{
				$type = $request->type;
				$request->session()->put('type_doc_attribute_filter_inner_list',$type);	
			}
		public function filterBydocAttributeDptName(Request $request)
			{
				$dept = $request->dept;
				$request->session()->put('dept_doc_attribute_filter_inner_list',$dept);	
			}
			public static function getDocumentVisaStageStatus($status){
			$data=DocumentVisaStageStatus::where('id',$status)->first();
			if($data != '')
		   {
			    return $data->title;
		   }
		   else
		   {
			    return "--";
		   }
		}
}
