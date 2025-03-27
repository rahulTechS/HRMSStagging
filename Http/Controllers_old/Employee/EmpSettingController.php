<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company\Subsidiary;
use App\Models\Company\Divison;
use App\Models\Company\Department;
use  App\Models\Attribute\Attributes;
use App\Models\Employee\Employee_attribute;
use App\Models\Employee\Employee_details;
use App\Models\Employee\EmployeeImportFiles;
use App\Models\Employee\EmployeeAttendanceModel;
use App\Models\Onboarding\KycDocuments;
use App\Models\Onboarding\DocumentCollectionAttributes;
use App\Models\Visa\Visaprocess;
use App\Models\Visa\DocumentUploadVisaStage;
use Session;


class EmpSettingController extends Controller
{
    
   public function viewEmployee($empid=NULL)
	{
		$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','p_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();

		$empDetailsSection2 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','v_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	

		$empDetailsSection3 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','c_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empDetailsSection4 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','b_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
					   /* echo "<pre>";
					   print_r($empRequiredDetails);
					  exit;  */
					 $document_collection_id = $empRequiredDetails->document_collection_id;
					  if($document_collection_id != '' && $document_collection_id != NULL)
					  {
			$kycSection5 = DocumentCollectionAttributes::join('kyc_documents', 'kyc_documents.attribute_code', '=', 'document_collection_attributes.id')
              		->where('kyc_documents.document_collection_id',$document_collection_id)
					->where('document_collection_attributes.attribute_area','kyc')
					
					  ->orderBy('document_collection_attributes.sort_order', 'ASC')
					  ->get();
					  }
					  else
					  {
						 $kycSection5 = array(); 
					  } 
					  
			return view("Employee/Setting/viewEmployee",compact('empDetails'),compact('empRequiredDetails','empDetailsSection2','empDetailsSection3','empDetailsSection4','kycSection5'));
	
	}
	
	public function viewEmployeeBoarded($empid=NULL,$mode=NULL)
	{
		
		$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','p_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();

		$empDetailsSection2 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','v_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	

		$empDetailsSection3 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','c_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();	
			$empDetailsSection4 = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empid)
					->where('attributes.tab_name','b_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			$empRequiredDetails =  Employee_details::where('emp_id',$empid)->first();
			
					   /* echo "<pre>";
					   print_r($empRequiredDetails);
					  exit;  */
					 $document_collection_id = $empRequiredDetails->document_collection_id;
					  if($document_collection_id != '' && $document_collection_id != NULL)
					  {
						$kycSection5 = DocumentCollectionAttributes::join('kyc_documents', 'kyc_documents.attribute_code', '=', 'document_collection_attributes.id')
						  ->where('kyc_documents.document_collection_id',$document_collection_id)
						  ->where('document_collection_attributes.attribute_area','kyc')
						  ->orderBy('document_collection_attributes.sort_order', 'ASC')
						  ->get();
					  }
					  else
					  {
						 $kycSection5 = array(); 
					  }
					 	 /*
					*get Visa Document
					*start Coding
					*/	
					if($document_collection_id != '' && $document_collection_id != NULL)
					  {
						$visaprocesIdListMod = Visaprocess::where("document_id",$document_collection_id)->get();
						$visaprocesIdList = array();
						foreach($visaprocesIdListMod as $_visaP)
						{
							$visaprocesIdList[] = $_visaP->id;
						}
						if(count($visaprocesIdList) >0)
						{
							$VisaDocList = DocumentUploadVisaStage::whereIn("visaprocess_id",$visaprocesIdList)->get();
							if(count($VisaDocList) > 0)
							{
								foreach($VisaDocList as $_doc)
								{
									$visaDoc[] = $_doc->file_name; 	
								}
							}
							else
							{
								$visaDoc = array(); 
							}
						}
						else
						{
							$visaDoc = array(); 
						}
						
					  }
					  else
					  {
						 $visaDoc = array(); 
					  }

					/*
					*get Visa Document
					*End Coding
					*/		
			return view("Employee/Setting/viewEmployeeBoarded",compact('empDetails'),compact('empRequiredDetails','empDetailsSection2','empDetailsSection3','empDetailsSection4','kycSection5','mode','visaDoc'));
	
	}
	public function updateEmpOnboarded($empId = NULL,$mode=NULL,$redirectMod=NULL)
		{
			
			if($mode == 'A')
			{
					$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empId)
					->where('attributes.status',1)
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			}
			else
			{
				
				$empDetails = Attributes::join('employee_attribute', 'employee_attribute.attribute_code', '=', 'attributes.attribute_code')
              		->where('employee_attribute.emp_id',$empId)
					->where('attributes.status',1)
					->where('attributes.tab_name','p_d')
					  ->orderBy('attributes.sort_order', 'ASC')
					  ->get();
			}
					//   echo "<pre>";
					//   print_r($empDetails);
					//   exit;
			
			$empRequiredDetails =  Employee_details::where('emp_id',$empId)->first();
			return view("Employee/Setting/updateEmpOnboarded",compact('empDetails'),compact('empRequiredDetails','mode','redirectMod'));
		}
		
   public function updateEmployeeDataOnboarded(Request $req)
		{
			$inputData = $req->input();
		/* 	echo '<pre>';
			print_r($inputData);
			exit; */
			$empdetails =  Employee_details::find($req->input('id'));
			
			$empdetails->onboarding_status=$req->input('onboarding_status');
			$empdetails->first_name=$req->input('first_name');
			$empdetails->middle_name=$req->input('middle_name');
			$empdetails->last_name=$req->input('last_name');
			$empdetails->status=1;
			$empdetails->save();
			
			$empIdPadding = $req->input('emp_id');
			$num = $req->input('emp_id');
			/*
			*delete rows from attribute]
			*start code
			*/
			
			//Employee_attribute::where('emp_id', $empIdPadding)->delete();
			
			/*
			*delete rows from attribute]
			*end code
			*/
			$keys = array_keys($_FILES);
			
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				
				if(!empty($req->file($key)))
				{
				$filenameWithExt = $req->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$req->file($key)->getClientOriginalExtension();
				$vKey = $keys[$fileIndex];
				$newFileName = $keys[$fileIndex].'-'.$num.'.'.$fileExtension;
				
				/*
				*Updating File Name
				*/
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $req->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				
				
				$req->file($key)->move(public_path('documentCollectionFiles/'), $newFileName);
				$fileIndex++;
				}
				else
				{
					
					$vKey = $keys[$fileIndex];
					$filesAttributeInfo[$vKey] = '';
					$listOfAttribute[] = $vKey;
					$fileIndex++;
					
				}
			}


			
			$attributesValues = $req->input();	
			$redirectMod = $attributesValues['redirectMod'];
			
			unset($attributesValues['_token']);
			unset($attributesValues['dept_id']);
			unset($attributesValues['onboarding_status']);
			unset($attributesValues['first_name']);
			unset($attributesValues['middle_name']);
			unset($attributesValues['last_name']);
			unset($attributesValues['_url']);
			unset($attributesValues['id']);
			unset($attributesValues['emp_id']);
			unset($attributesValues['redirectMod']);
			
			
			
			foreach($attributesValues as $key=>$value)
			{
				if(in_array($key,$listOfAttribute))
				{
					if($filesAttributeInfo[$key] != '')
					{
					$dpid = $req->input('dept_id');
					$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
										->where('attribute_code',$key)
										->where('dept_id',$dpid)
										->first();
							if(!empty($empattributesMod))
							{						
								$empattributes = Employee_attribute::find($empattributesMod->id);
								$empattributes->attribute_code = $key;
								$empattributes->attribute_values = $filesAttributeInfo[$key];
								$empattributes->status = 1;
								$empattributes->emp_id = $empIdPadding;
								$empattributes->dept_id = $req->input('dept_id');
								$empattributes->save();
							}
							else
							{
								$empattributes = new Employee_attribute();
								$empattributes->attribute_code = $key;
								$empattributes->attribute_values = $filesAttributeInfo[$key];
								$empattributes->status = 1;
								$empattributes->emp_id = $empIdPadding;
								$empattributes->dept_id = $req->input('dept_id');
								$empattributes->save();
							}
					}
					
				}
				else{
					
					$dpid = $req->input('dept_id');
					$empattributesMod = Employee_attribute::where('emp_id',$empIdPadding)
										->where('attribute_code',$key)
										->where('dept_id',$dpid)
										->first();
										
					
					if(!empty($empattributesMod))
					{
					$empattributes = Employee_attribute::find($empattributesMod->id);
					$empattributes->attribute_code = $key;
					$empattributes->attribute_values = $value;
					$empattributes->status = 1;
					$empattributes->emp_id = $empIdPadding;
					$empattributes->dept_id = $req->input('dept_id');
					$empattributes->save();
					}
					else if(empty($empattributesMod) && !empty($attributesValues[$key]))
					{
						$empattributes = new Employee_attribute();
						$empattributes->attribute_code = $key;
						$empattributes->attribute_values = $attributesValues[$key];
						$empattributes->status = 1;
						$empattributes->emp_id = $empIdPadding;
						$empattributes->dept_id = $dpid;
						$empattributes->save();
					}
					else
					{
						//nothing to do
					}
					
				}
				
			}


			
			
			
			$req->session()->flash('message','Employee Updated Successfully.');
			if($redirectMod == 'M')
			{
				 return redirect('listEmp');
			}
			else
			{
            return redirect('viewEmployeeBoarded/'.$empIdPadding.'/B');
			}
		}
		
public static function checkInfoBehave($eid,$mode)
{
	
	$allAttributes = Attributes::where("tab_name",$mode)->where("status",1)->get();
	$behave = 1;
	foreach($allAttributes as $_attr)
	{
		
		$countEmpAttr =  Employee_attribute::where("emp_id",$eid)->where("attribute_code",$_attr->attribute_code)->count();
		if($countEmpAttr == 0)
		{
			/* echo $_attr->attribute_name;
			echo '<br/>'; */
			$behave = 2;
		}
	}
	return $behave;
	
}

public static function  checkKycBehave($eid,$mode)
{
	$empRequiredDetails =  Employee_details::where('emp_id',$eid)->first();
			
					   /* echo "<pre>";
					   print_r($empRequiredDetails);
					  exit;  */
	$document_collection_id = $empRequiredDetails->document_collection_id;
	
	 $allAttributes = DocumentCollectionAttributes::where("attribute_area",$mode)->where("status",1)->get();
	 $behave = 1;
	foreach($allAttributes as $_attr)
	{
		
		$countKYCAttr =  KycDocuments::where("document_collection_id",$document_collection_id)->where("attribute_code",$_attr->id)->count();
		if($countKYCAttr == 0)
		{
			/* echo $_attr->attribute_name;
			echo '<br/>'; */
			$behave = 2;
		}
	}
	return $behave;
}

	
}
