<?php

namespace App\Http\Controllers\Banks\EIB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\DepartmentForm;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Attribute\DepartmentFormChildEntry;
use App\Models\Attribute\FormProduct;
use App\Models\Attribute\MasterAttribute;
use App\Models\Attribute\AttributeType;
use App\Models\Attribute\FormSection;


use App\Models\Company\Department;
use App\Models\Employee\Employee_details;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Http\Controllers\Attribute\DepartmentFormController;
use App\Models\Bank\CBD\CBDBankMis;
use App\Models\Bank\CBD\BankCBDMTD;

use App\Models\Bank\EIB\EibImportFile;
use App\Models\Bank\EIB\EibBankMis;


use App\Models\Attribute\EIBDepartmentFormEntry;
use App\Models\Attribute\EIBDepartmentFormChildEntry;

use Session;

class EIBController extends Controller
{
 public static function getEmployeeName($empid)
 {
	 if($empid != '' && $empid != NULL)
	 {
		$empName = Employee_details::select("emp_name")->where("emp_id",$empid)->first();
		if($empName != '')
		{
			return $empName->emp_name;
		}
		else
		{
			return '';
		}
	 }
	 else
	 {
		 return '';
	 }
 }
 
 protected function getEmployeeNamelocal($empid)
 {
	 if($empid != '' && $empid != NULL)
	 {
		$empName = Employee_details::select("emp_name")->where("emp_id",$empid)->first();
		if($empName != '')
		{
			return $empName->emp_name;
		}
		else
		{
			return '';
		}
	 }
	 else
	 {
		 return '';
	 }
 }
 public function updateCodeAgentCBD(Request $request)
 {
	 $empid = $request->empid;
	 $empName = Employee_details::select("source_code")->where("emp_id",$empid)->first();
		if($empName != '')
		{
			echo $empName->source_code;
		}
		else
		{
			echo  '';
		}
		exit;
 }
 
 public static function getEmployeeNameWithCode($empid)
 {
	 if($empid != '' && $empid != NULL)
	 {
		$empName = Employee_details::select("emp_name","source_code")->where("emp_id",$empid)->first();
		if($empName != '')
		{
			return $empName->emp_name.'('.$empName->source_code.')';
		}
		else
		{
			return '';
		}
	 }
	 else
	 {
		 return '';
	 }
 }
 
 public static function getEmployeeNameByCode($empCode)
 {
	 
	 if($empCode != '' && $empCode != NULL)
	 {
		$empName = Employee_details::select("emp_name")->where("source_code",$empCode)->first();
		if($empName != '')
		{
			return $empName->emp_name;
		}
		else
		{
			return '';
		}
	 }
	 else
	 {
		 return '';
	 }
 }

 


 
 
 public function addCBDEntryPost(Request $request)
 {
			$postData = $request->input();
			
			$postDataInput = $postData['attribute_value'];
			$entry_obj = new DepartmentFormEntry();			
	
			/*
			*parent entry 
			*start code
			*/
			$entry_obj->ref_no = $postDataInput['ref_no'];
			$entry_obj->form_id = 2;
			$entry_obj->form_title = 'CBD Internal MIS';
			$entry_obj->form_status = $postDataInput['status_cbd'];
			$entry_obj->team = $postDataInput['sm_name_cbd'];
			$entry_obj->customer_name = $postDataInput['customer_name'];
			$entry_obj->customer_mobile = $postDataInput['customer_mobile'];
			$entry_obj->remarks = $postDataInput['CBD_remark'];
			$entry_obj->agent_code = $postDataInput['agent_code_cbd'];
			
				$sourceCode = $postDataInput['agent_code_cbd'];
				$empMod = Employee_details::select("emp_id")->where("source_code",$sourceCode)->first();
				if($empMod != '')
				{
					$entry_obj->emp_id = $empMod->emp_id;
				}
				
			
			
			$entry_obj->channel_cbd = $postDataInput['channel_cbd'];
			$entry_obj->status_AECB_cbd = $postDataInput['aecb_status'];
			$entry_obj->card_type_cbd = $postDataInput['card_type_cbd'];
			$entry_obj->application_date = date("Y-m-d",strtotime($postDataInput['app_date']));
			$entry_obj->status = 1;
			$entry_obj->cbd_marging_status = 1;	
			$entry_obj->missing_internal = 3;	
			$entry_obj->save();
			$insertID = $entry_obj->id;
			/*
			*parent entry 
			*end code
			*/
			
			
			/*
			*child entry 
			*start code
			*/
			$child_obj = new DepartmentFormChildEntry();
			foreach($postDataInput as $key=>$value)
			{
				$child_obj = new DepartmentFormChildEntry();
				$child_obj->parent_id = $insertID;
				$child_obj->form_id = 2;
				$child_obj->attribute_code = $key;
				$child_obj->attribute_value = $value;
				$child_obj->status = 1;
				$child_obj->save();
			}
			
			/*
			*child entry 
			*end code
			*/
            $request->session()->flash('message','Record added Successfully.');
			return redirect('cbdCardsManagement');
	 
 }
 
 

 
 
	
	
	public function searchCBDMTDInner(Request $request)
	{
				$requestParameters = $request->input();
				
				$ref_no_mtd = '';
				$sm_manager_mtd = '';
				$employee_id_mtd = '';
				$start_cd_opn = '';
				$end_cd_opn = '';
				$submission_type_mtd = '';
				
				
				if(isset($requestParameters['ref_no_mtd']))
				{
					$ref_no_mtd = @$requestParameters['ref_no_mtd'];
				}
				if(isset($requestParameters['sm_manager_mtd']))
				{
					$sm_manager_mtd = @$requestParameters['sm_manager_mtd'];
				}
				if(isset($requestParameters['employee_id_mtd']))
				{
					$employee_id_mtd = @$requestParameters['employee_id_mtd'];
				}
				if(isset($requestParameters['start_cd_opn']))
				{
					$start_cd_opn = @$requestParameters['start_cd_opn'];
				}
				if(isset($requestParameters['end_cd_opn']))
				{
					$end_cd_opn = @$requestParameters['end_cd_opn'];
				}
				
				if(isset($requestParameters['submission_type_mtd']))
				{
					$submission_type_mtd = @$requestParameters['submission_type_mtd'];
				}
			
				$request->session()->put('master_cbd_search_MTD','');
				$request->session()->put('ref_no_CBD_mtd',$ref_no_mtd);
				$request->session()->put('smManager_CBD_mtd',$sm_manager_mtd);
				$request->session()->put('employee_id_CBD_mtd',$employee_id_mtd);
				$request->session()->put('start_CD_OPN_DT_CBD_bank',$start_cd_opn);
				$request->session()->put('end_CD_OPN_DT_CBD_bank',$end_cd_opn);
				$request->session()->put('submission_type_CBD_MTD',$submission_type_mtd);
			
				return redirect("loadBankContentsCBDCardBankSideMTD");
	}
	

	
	
	public function resetCBDMTDInnerFilter(Request $request)
	{
				$request->session()->put('ref_no_CBD_mtd','');
				$request->session()->put('smManager_CBD_mtd','');
				$request->session()->put('employee_id_CBD_mtd','');
				$request->session()->put('start_CD_OPN_DT_CBD_bank','');
				$request->session()->put('end_CD_OPN_DT_CBD_bank','');
				$request->session()->put('submission_type_CBD_MTD','');
				$request->session()->put('master_cbd_search_MTD',2);
				return redirect("loadBankContentsCBDCard");
	}
	
	
	
	
	
	
	
	public function loadBankContentsCBDCardBankSideMTD(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}

		$whereRaw = " Appl_Nb !=''";	
		if(@$request->session()->get('master_cbd_search_MTD') != '' && @$request->session()->get('master_cbd_search_MTD') == 2)
		{
				  if(@$request->session()->get('ref_no_CBD_master') != '')
					{
						$refNO = $request->session()->get('ref_no_CBD_master');
						
					
						$whereRaw .= " AND Appl_Nb like '%".$refNO."%'";	
						
						
					}
					
					if(@$request->session()->get('emp_id_CBD_master') != '')
					{
						$employeeMod = $request->session()->get('emp_id_CBD_master');
						$employeeModStr = '';
						foreach($employeeMod  as $modID)
						{
							if($employeeModStr == '')
							{
								$employeeModStr = "'".$modID."'";
							}
							else
							{
								$employeeModStr = $employeeModStr.",'".$modID."'";
							}
						}
					
						$whereRaw .= " AND employee_id IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('team_EIB_master') != '')
					{
						$SMMod = $request->session()->get('team_EIB_master');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND tl_name IN (".$smStr.")";	
						
						
					}
					
					if($request->session()->get('start_date_application_CBD_master') != '')
					{
						$start_date_creation_CBD_bank = $request->session()->get('start_date_application_CBD_master');			
						$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_creation_CBD_bank))."'";
						$searchValues['start_date_application_CBD_master'] = $start_date_creation_CBD_bank;			
					}

					if($request->session()->get('end_date_application_CBD_master') != '')
					{
						$end_date_creation_CBD_bank = $request->session()->get('end_date_application_CBD_master');			
						$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_creation_CBD_bank))."'";
						$searchValues['end_date_application_CBD_master'] = $end_date_creation_CBD_bank;			
					}
		}
		else
		{
					if(@$request->session()->get('ref_no_CBD_mtd') != '')
					{
						$refNO = $request->session()->get('ref_no_CBD_mtd');
						
					
						$whereRaw .= " AND Appl_Nb like '%".$refNO."%'";	
						
						
					}
					
					if(@$request->session()->get('employee_id_CBD_mtd') != '')
					{
						$employeeMod = $request->session()->get('employee_id_CBD_mtd');
						$employeeModStr = '';
						foreach($employeeMod  as $modID)
						{
							if($employeeModStr == '')
							{
								$employeeModStr = "'".$modID."'";
							}
							else
							{
								$employeeModStr = $employeeModStr.",'".$modID."'";
							}
						}
					
						$whereRaw .= " AND employee_id IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('smManager_CBD_mtd') != '')
					{
						$SMMod = $request->session()->get('smManager_CBD_mtd');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND sm_manager IN (".$smStr.")";	
						
						
					}
					
					if($request->session()->get('start_CD_OPN_DT_CBD_bank') != '')
					{
						$start_CD_OPN_DT_CBD_bank = $request->session()->get('start_CD_OPN_DT_CBD_bank');			
						$whereRaw .= " AND CD_OPN_DT >='".date('Y-m-d',strtotime($start_CD_OPN_DT_CBD_bank))."'";
						$searchValues['start_CD_OPN_DT_CBD_bank'] = $start_CD_OPN_DT_CBD_bank;			
					}

					if($request->session()->get('end_CD_OPN_DT_CBD_bank') != '')
					{
						$end_CD_OPN_DT_CBD_bank = $request->session()->get('end_CD_OPN_DT_CBD_bank');			
						$whereRaw .= " AND CD_OPN_DT <='".date('Y-m-d',strtotime($end_CD_OPN_DT_CBD_bank))."'";
						$searchValues['end_CD_OPN_DT_CBD_bank'] = $end_CD_OPN_DT_CBD_bank;			
					}
					if($request->session()->get('submission_type_CBD_MTD') != '')
					{
						
						$submission_type = $request->session()->get('submission_type_CBD_MTD');
						if($submission_type == 'Linked_in_internal')
						{
							$whereRaw .= " AND update_status =2 And match_bank_status =1";
						}
						else if($submission_type == 'Linked_in_bank')
						{
							$whereRaw .= " AND update_status =1 And match_bank_status =2";
						}
						else if($submission_type == 'Linked_in_both')
						{
							$whereRaw .= " AND update_status =2 And match_bank_status =2";
						}
						else if($submission_type == 'Missing_in_both')
						{
							$whereRaw .= " AND update_status =1 And match_bank_status =1";
						}
						else if($submission_type == 'Missing_in_internal')
						{
							$whereRaw .= " AND update_status =1 And match_bank_status =2";
						}
						else if($submission_type == 'Missing_in_bank')
						{
							$whereRaw .= " AND update_status =2 And match_bank_status =1";
						}
						
						else
						{
							
						}
						
					}
					
					

		}
		//echo $whereRaw;exit;
		$datasCBDMainCount = BankCBDMTD::whereRaw($whereRaw)->get()->count();
		
		$datasCBDMain = BankCBDMTD::whereRaw($whereRaw)->orderBy("CD_OPN_DT","DESC")->paginate($paginationValue);

		
		
		/*
		*application  Status
		*/
			$employeeIdList = BankCBDMTD::select('employee_id')->get()->unique('employee_id');
			
		/*
		*application  Status
		*/
		
		/*
		*Sm Manager
		*/
			$smManageData = BankCBDMTD::select('sm_manager')->get()->unique('sm_manager');
			
		/*
		*Sm Manager
		*/
		 return view("Banks/CBD/loadBankContentsCBDCardBankSideMTD",compact('datasCBDMainCount','datasCBDMain','paginationValue','searchValues','employeeIdList','smManageData'));
	}
	
	
	
	
	
	
	
	
	public function updateFinalRemarksCBD(Request $request)
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
	
	
	
	
	
	
	

	
	public static function getChannel($parentId)
	{
		$data = DepartmentFormChildEntry::where("parent_id",$parentId)->where("attribute_code","channel_cbd")->first();
		if($data != '')
		{
			return $data->attribute_value;
		}
		else
		{
			return "";
		}
	}
	public static function getCardType($parentId)
	{
		$data = DepartmentFormChildEntry::where("parent_id",$parentId)->where("attribute_code","card_type_cbd")->first();
		if($data != '')
		{
			return $data->attribute_value;
		}
		else
		{
			return "";
		}
	}
	public static function getAECBStatus($parentId)
	{
		$data = DepartmentFormChildEntry::where("parent_id",$parentId)->where("attribute_code","aecb_status")->first();
		if($data != '')
		{
			return $data->attribute_value;
		}
		else
		{
			return "";
		}
	}
	
	public function linkMISCBD(Request $request)
	{
		$whereRaw = " form_id=2 AND (status='1' OR status='2')";
						
					
				if(@$request->session()->get('team_CBD_master_links') != '')
				{
					$teamL = $request->session()->get('team_CBD_master_links');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND team IN (".$teamstr.")";	
					
					
				}
				
				if(@$request->session()->get('rm_CBD_master_links') != '')
				{
					$rmL = $request->session()->get('rm_CBD_master_links');
					$rmLstr = '';
					foreach($rmL  as $lS)
					{
						if($rmLstr == '')
						{
							$rmLstr = "'".$lS."'";
						}
						else
						{
							$rmLstr = $rmLstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND emp_id IN (".$rmLstr.")";	
					
					
				}
				
				
				if(@$request->session()->get('card_type_links') != '')
				{
					$card_type_linksL = $request->session()->get('card_type_links');
					$card_type_linksstr = '';
					foreach($card_type_linksL  as $lS)
					{
						if($card_type_linksstr == '')
						{
							$card_type_linksstr = "'".$lS."'";
						}
						else
						{
							$card_type_linksstr = $card_type_linksstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND card_type_cbd IN (".$card_type_linksstr.")";	
					
					
				}
				
				
				
				if($request->session()->get('start_date_application_CBD_master_links') != '')
				{
					$start_date_application_CBD_internal = $request->session()->get('start_date_application_CBD_master_links');			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'";
					$searchValues['start_date_application_CBD_master_links'] = $start_date_application_CBD_internal;			
				}

				if($request->session()->get('end_date_application_CBD_master_links') != '')
				{
					$end_date_application_CBD_internal = $request->session()->get('end_date_application_CBD_master_links');			
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_CBD_internal))."'";
					$searchValues['end_date_application_CBD_master_links'] = $end_date_application_CBD_internal;			
				}
		/* echo $whereRaw;exit; */
		$collectionAgentNameInternalMis = DepartmentFormEntry::where("cbd_marging_status",1)
		->whereRaw($whereRaw)
		->groupBy('emp_id')
		->selectRaw('count(*) as totalData, emp_id,agent_code')
		->get();
		
			
				$whereRaw1 = " ref_no != ''";
				if($request->session()->get('start_date_application_CBD_master_links') != '')
				{
					$start_date_application_CBD_internal = $request->session()->get('start_date_application_CBD_master_links');			
					$whereRaw1 .= " AND creation_date >='".date('Y-m-d',strtotime($start_date_application_CBD_internal))."'";
					$searchValues['start_date_application_CBD_master_links'] = $start_date_application_CBD_internal;			
				}

				if($request->session()->get('end_date_application_CBD_master_links') != '')
				{
					$end_date_application_CBD_internal = $request->session()->get('end_date_application_CBD_master_links');			
					$whereRaw1 .= " AND creation_date <='".date('Y-m-d',strtotime($end_date_application_CBD_internal))."'";
					$searchValues['end_date_application_CBD_master_links'] = $end_date_application_CBD_internal;			
				}
				
				if(@$request->session()->get('card_type_links') != '')
				{
					$card_type_linksL = $request->session()->get('card_type_links');
					$card_type_linksstr = '';
					foreach($card_type_linksL  as $lS)
					{
						if($card_type_linksstr == '')
						{
							$card_type_linksstr = "'".$lS."'";
						}
						else
						{
							$card_type_linksstr = $card_type_linksstr.",'".$lS."'";
						}
					}
				
					$whereRaw1 .= " AND card_type IN (".$card_type_linksstr.")";	
					
					
				}
				
				if(@$request->session()->get('rm_CBD_master_links') != '')
				{
					$rmL = $request->session()->get('rm_CBD_master_links');
					
					$rmLstr = '';
					foreach($rmL  as $lS)
					{
						/*
						*getsource code
						*/
						
						$empName = Employee_details::select("source_code")->where("emp_id",$lS)->first();
						if($empName->source_code != NULL && $empName->source_code != '')
						{
							/*
							*getsource code
							*/
							if($rmLstr == '')
							{
								$rmLstr = "'".$empName->source_code."'";
							}
							else
							{
								$rmLstr = $rmLstr.",'".$empName->source_code."'";
							}
						}
					}
				
					$whereRaw1 .= " AND Created_User IN (".$rmLstr.")";	
					
					
				}
		$collectionAgentNameBankMis = CBDBankMis::where("cbd_marging_status",1)
		->whereRaw($whereRaw1)
		->groupBy('Created_User')
		->selectRaw('count(*) as totalData, Created_User')
		->get();
	/* 	echo "<pre>";
		print_r($collectionAgentNameInternalMis);
		exit; */
		/*
		*get SM List
		*/
		$collectionSMNameInternalMis = DepartmentFormEntry::where("form_id",2)->where("cbd_marging_status",1)
		->groupBy('team')
		->selectRaw('team')
		->get();
		/*
		*get SM List
		*/	
		/*
		*get Card Type
		*/
		$collectionCardTypeInternalMis = DepartmentFormEntry::where("form_id",2)->where("cbd_marging_status",1)
		->groupBy('card_type_cbd')
		->selectRaw('card_type_cbd')
		->get();
		/*
		*get Card Type
		*/
		/*
		*get Employee Id
		*/
		$collectionEmployeeIdsInternalMis = DepartmentFormEntry::where("form_id",2)->where("cbd_marging_status",1)
		->groupBy('emp_id')
		->selectRaw('emp_id')
		->get();
		/*
		*get Employee Id
		*/		
		
		return view("Banks/CBD/matchingMIS/linkMISCBD",compact('collectionAgentNameInternalMis','collectionAgentNameBankMis','collectionSMNameInternalMis','collectionCardTypeInternalMis','collectionEmployeeIdsInternalMis'));
	}
	
	
	public function interCBDMisMargeFunc(Request $request)
	{
		
		$empID = $request->empID;
		
				
				
		$reports = DepartmentFormEntry::where("cbd_marging_status",1)->where("emp_id",$empID)->get();
		$reportsCount = DepartmentFormEntry::where("cbd_marging_status",1)->where("emp_id",$empID)->get()->count();
		return view("Banks/CBD/matchingMIS/interCBDMisMargeFunc",compact('reports','reportsCount'));
	}
	
	public function bankCBDMisMargeFunc(Request $request)
	{
		$createdUser = $request->createdUser;
		
		$reports = CBDBankMis::where("cbd_marging_status",1)->where("Created_User",$createdUser)->get();
		$reportsCount = CBDBankMis::where("cbd_marging_status",1)->get()->count();
		return view("Banks/CBD/matchingMIS/bankCBDMisMargeFunc",compact('reports','reportsCount'));
	}
	
	public function margeConfirmationCBD(Request $request)
	{
		$internalMis = explode("_",$request->internalMis);
		$cbdMis =  explode("_",$request->cbdMis);
		$data = array();
		$data['cm_name'] = DepartmentFormEntry::where("id",$internalMis[2])->first()->customer_name;
		$data['employee_id'] = DepartmentFormEntry::where("id",$internalMis[2])->first()->emp_id;
		$data['appId'] = CBDBankMis::where("id",$cbdMis[2])->first()->ref_no;
		echo json_encode($data);
		exit;
	}
	
	public function mergeAppIdWithMISCBD(Request $request)
	{
		
		$misId = explode("_",$request->misId);
		$jonusId =  explode("_",$request->jonusId);
		
		
		/*
		*marging from bank to mis
		*/
		$bankData = CBDBankMis::where("id",$jonusId[2])->first();
		$updateInternalMis = DepartmentFormEntry::find($misId[2]);
		$updateInternalMis->ref_no = $bankData->ref_no;
		$updateInternalMis->customer_name = $bankData->customer_name;
		$updateInternalMis->channel_cbd = $bankData->Channel;
		$updateInternalMis->status_AECB_cbd = $bankData->AECB_Status;
		$updateInternalMis->form_status = $bankData->Status;
		$updateInternalMis->card_type_cbd = $bankData->card_type;
		
		$updateInternalMis->cbd_marging_status = 2;
		$updateInternalMis->cbd_update_status = 2;
		$updateInternalMis->save();
		
			/*
			*update in child
			*/
			$getData = DepartmentFormChildEntry::where("parent_id",$misId[2])->where("attribute_code","customer_name")->first();
			if($getData != '')
			{
				$updateChild = DepartmentFormChildEntry::find($getData->id);
				$updateChild->attribute_value = $bankData->customer_name;
				$updateChild->save();
			}
			/*
			*update in child
			*/
			
			/*
			*update in child
			*/
			
			$getData = DepartmentFormChildEntry::where("parent_id",$misId[2])->where("attribute_code","status_cbd")->first();
			if($getData != '')
			{
				$updateChild = DepartmentFormChildEntry::find($getData->id);
				$updateChild->attribute_value = $bankData->Status;
				$updateChild->save();
			}
			$getData = DepartmentFormChildEntry::where("parent_id",$misId[2])->where("attribute_code","channel_cbd")->first();
			if($getData != '')
			{
				$updateChild = DepartmentFormChildEntry::find($getData->id);
				$updateChild->attribute_value = $bankData->Channel;
				$updateChild->save();
			}
			$getData = DepartmentFormChildEntry::where("parent_id",$misId[2])->where("attribute_code","card_type_cbd")->first();
			if($getData != '')
			{
				$updateChild = DepartmentFormChildEntry::find($getData->id);
				$updateChild->attribute_value = $bankData->card_type;
				$updateChild->save();
			}
			$getData = DepartmentFormChildEntry::where("parent_id",$misId[2])->where("attribute_code","aecb_status")->first();
			if($getData != '')
			{
				$updateChild = DepartmentFormChildEntry::find($getData->id);
				$updateChild->attribute_value = $bankData->AECB_Status;
				$updateChild->save();
			}
			/*
			*update in child
			*/
		
		/*
		*marging from bank to mis
		*/
		
		/*
		*marging from internal to bank
		*/
		$misInternalData = DepartmentFormEntry::where("id",$misId[2])->first();
		
			$updateBankMis = CBDBankMis::find($jonusId[2]);
			$updateBankMis->sm_manager = $misInternalData->team;
			$updateBankMis->employee_id = $misInternalData->emp_id;
			$updateBankMis->cbd_marging_status = 2;
			$updateBankMis->update_emp_status = 2;
			$updateBankMis->save();
		/*
		*marging from internal to bank
		*/
		
	}
	
	
	public function CBDCardsmapped(Request $request)
	{
				$requestParameters = $request->input();
				
				$start_date_application = '';
				$end_date_application = '';
				$team = '';
				$rm = '';
			
				$cardtype = '';

				

				if(isset($requestParameters['team']))
				{
					$team = @$requestParameters['team'];
				}
				if(isset($requestParameters['rm']))
				{
					$rm = @$requestParameters['rm'];
				}
				if(isset($requestParameters['cardtype']))
				{
					$cardtype = @$requestParameters['cardtype'];
				}
				
				
				if(isset($requestParameters['start_date']))
				{
					$start_date_application = @$requestParameters['start_date'];
				}
				if(isset($requestParameters['end_date']))
				{
					$end_date_application = @$requestParameters['end_date'];
				}
				
				
				$request->session()->put('team_CBD_master_links',$team);
				$request->session()->put('rm_CBD_master_links',$rm);
				$request->session()->put('card_type_links',$cardtype);
				
				$request->session()->put('start_date_application_CBD_master_links',$start_date_application);
				$request->session()->put('end_date_application_CBD_master_links',$end_date_application);
				
				return redirect("linkMISCBD");
	}
	
	public function resetCBDMISLink(Request $request)
	{
				$request->session()->put('team_CBD_master_links','');
				$request->session()->put('rm_CBD_master_links','');
				
				$request->session()->put('start_date_application_CBD_master_links','');
				$request->session()->put('end_date_application_CBD_master_links','');
				$request->session()->put('card_type_links','');
				
				return redirect("linkMISCBD");
	}
	
	public function updateAgentCode()
	{
		
		$internalMisMod = DepartmentFormEntry::where("form_id",2)->get();
		foreach($internalMisMod as $internalMis)
		{
			/* echo "<pre>";
			print_r($internalMis);
			exit; */
			$getData = DepartmentFormChildEntry::where("parent_id",$internalMis->id)->where("attribute_code","agent_code_cbd")->first();
			
				if($getData != '')
				{
					$updateMain = DepartmentFormEntry::find($internalMis->id);
					$updateMain->agent_code = $getData->attribute_value;
					$updateMain->save();
				}
		}
		echo "done";
		exit;
	}
	
	public function exportDocReportinternalMisCBDCardsLinking(Request $request)
	{
		$requestPost = $request->input();
		 $parameters = $request->input(); 
			/* echo "<pre>";
			print_r($parameters);
			exit; */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Link_Internal_MIS_CBD_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:J1');
			$sheet->setCellValue('A1', 'Internal MIS CBD Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Row Id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('SM Name'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Agent Code'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Agent Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Application Date'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Card Type'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Customer Name'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Mobile No'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Status'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Application Reference No'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');

			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				
				
				$mis =  DepartmentFormEntry::where("id",$sid)->first();
				if($mis->ref_no == NULL && $mis->ref_no == '')
				{
				/*  $Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	 */

			/* $emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');

			$submission_date = @$mis->submission_date;
			if($submission_date!='0000-00-00')
			{
				$submission_date = date('d-m-Y',strtotime($mis->submission_date));
			}
			else
			{
				$submission_date='';
			}
			
				if($mis->status == 1)
                  $status='Activated';
                else
					$status='Deactivated'; */
               	
				 $indexCounter++; 

				/*
				*agent name
				*/			
				$agentNameMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","agent_name_cbd")->first();
					$agentName = '';
					if($agentNameMod != '')
					{
						$agentName = $agentNameMod->attribute_value;
					}
				/*
				*agent name
				*/
				
				/*
				*agent code
				*/			
				$agentCodeMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","agent_code_cbd")->first();
					$agentCode = '';
					if($agentCodeMod != '')
					{
						$agentCode = $agentCodeMod->attribute_value;
					}
				/*
				*agent code
				*/
				
				
				/*
				*employeer name
				*/			
				$employeeMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","employer_name")->first();
					$employeerName = '';
					if($employeeMod != '')
					{
						$employeerName = $employeeMod->attribute_value;
					}
				/*
				*employeer name
				*/
				
				/*
				*Card Type
				*/			
				$cardTypeMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","card_type_cbd")->first();
					$cardType = '';
					if($cardTypeMod != '')
					{
						$cardType = $cardTypeMod->attribute_value;
					}
				/*
				*Card Type
				*/
				
				
				
				/*
				*status_cbd
				*/			
				$status_cbdMod = 	DepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","status_cbd")->first();
					$status_cbd = '';
					if($status_cbdMod != '')
					{
						$status_cbd = $status_cbdMod->attribute_value;
					}
				/*
				*status_cbd
				*/
				
				
				
				
				$sheet->setCellValue('A'.$indexCounter, $mis->id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $mis->team)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $this->getEmployeeNamelocal($agentName))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $agentCode)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $mis->application_date)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $cardType)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $mis->customer_name)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $mis->customer_mobile)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $status_cbd)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $mis->ref_no)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
				$sn++;
				}
				
			}
			
			
			  for($col = 'A'; $col !== 'J'; $col++) {
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('J3:J'.$indexCounter)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
			
			$spreadsheet->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','J') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
	}




















// 3-6-2024 Start new code

public function eibCardsManagement()
{
	$employeeIdList = DepartmentFormEntry::select('emp_id')->where("form_id",4)->get()->unique('emp_id');	  
	//$teamData = DepartmentFormEntry::select('team')->where("form_id",4)->get()->unique('team');	  


	$teamDataMaster = EIBDepartmentFormEntry::select('tl_name')->where("form_id",4)->get()->unique('tl_name');
	$empNameDataMaster = EIBDepartmentFormEntry::select('se_name')->where("form_id",4)->get()->unique('se_name');
	$actualempNameDataMaster = EIBDepartmentFormEntry::select('actual_se_name')->where("form_id",4)->get()->unique('actual_se_name');

   	return view("Banks/EIB/eibCardsManagement",compact("employeeIdList","teamDataMaster","empNameDataMaster","actualempNameDataMaster"));
}

public function addeibCards()
{
	$departmentFormDetails =   DepartmentForm::where("id",4)->first();
	$DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
	$masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
	$DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
	$FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

	$departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', 4)->groupby('form_section')->orderby('sort_order','ASC')->get(['form_section']);

	$departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', 4)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

	$Employee_details = Employee_details::where("offline_status",1)->where("dept_id",52)->where("job_function",2)->orderby('first_name','ASC')->get();
	
	return view("Banks/EIB/addeibCards",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','DepartmentNameDetails','Employee_details'));
}



public function loadBankContentsEIBCards(Request $request)
{
		 //$request->session()->put('paginationValue','');
		$form_id = 4;
		$searchValues = array();

		$paginationValue = 20;
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}		
		
		$id = $form_id;
		$departmentFormDetails =   DepartmentForm::where("id",$form_id)->first(); 
		$DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		$where_array = array('form_id'=> $form_id);
		$whereRaw = " form_id='".$form_id."' AND (status='1' OR status='2')";

		
		

		

		/* if(@$request->session()->get('form_status') != '')
		{
			$form_status = $request->session()->get('form_status');
			$form_status_str = '';
			foreach($form_status as $form_status_value)
			{
				if($form_status_str == '')
				{
					$form_status_str = "'".$form_status_value."'";
				}
				else
				{
					$form_status_str = $form_status_str.","."'".$form_status_value."'";
				}
			}
			$whereRaw .= " AND form_status IN (".$form_status_str.")";			
		} */
		
		if(@$request->session()->get('master_cbd_search_internal') != '' && @$request->session()->get('master_cbd_search_internal') == 2)
		{
			if(@$request->session()->get('application_no_EIB_master') != '')
			{
				$applicationNO = $request->session()->get('application_no_EIB_master');
				
			
				$whereRaw .= " AND application_no like '%".$applicationNO."%'";	
				
				
			}
				
				
				
				if(@$request->session()->get('team_EIB_master') != '')
				{
					$teamL = $request->session()->get('team_EIB_master');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND tl_name IN (".$teamstr.")";	
					
					
				}



				if(@$request->session()->get('sename_EIB_master') != '')
				{
					$teamL = $request->session()->get('sename_EIB_master');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND se_name IN (".$teamstr.")";	
					
					
				}
				if(@$request->session()->get('actualsename_EIB_master') != '')
				{
					$teamL = $request->session()->get('actualsename_EIB_master');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND actual_se_name IN (".$teamstr.")";	
					
					
				}







				
				if(@$request->session()->get('emp_id_CBD_master') != '')
				{
					$empIds = $request->session()->get('emp_id_CBD_master');
					$empStr = '';
					foreach($empIds  as $eid)
					{
						if($empStr == '')
						{
							$empStr = "'".$eid."'";
						}
						else
						{
							$empStr = $empStr.",'".$eid."'";
						}
					}
				
					$whereRaw .= " AND emp_id IN (".$empStr.")";	
					
					
				}
				
				if($request->session()->get('start_date_application_EIB_master') != '')
				{
					$start_date_application_EIB_internal = $request->session()->get('start_date_application_EIB_master');			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_EIB_internal))."'";
					$searchValues['start_date_application_EIB_master'] = $start_date_application_EIB_internal;			
				}

				if($request->session()->get('end_date_application_EIB_master') != '')
				{
					$end_date_application_EIB_internal = $request->session()->get('end_date_application_EIB_master');			
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_EIB_internal))."'";
					$searchValues['end_date_application_EIB_master'] = $end_date_application_EIB_internal;			
				}
		}
		else
		{
				if(@$request->session()->get('app_no_EIB_internal') != '')
				{
					$appNO = $request->session()->get('app_no_EIB_internal');
					
				
					$whereRaw .= " AND application_no like '%".$appNO."%'";	
					
					
				}
				if(@$request->session()->get('form_status_CBD_internal') != '')
				{
					$status = $request->session()->get('form_status_CBD_internal');
					$strStatus = '';
					foreach($status  as $s)
					{
						if($strStatus == '')
						{
							$strStatus = "'".$s."'";
						}
						else
						{
							$strStatus = $strStatus.",'".$s."'";
						}
					}
				
					$whereRaw .= " AND form_status IN (".$strStatus.")";	
					
					
				}
				
				
				if(@$request->session()->get('team_ENBD_internal') != '')
				{
					$teamL = $request->session()->get('team_ENBD_internal');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND tl_name IN (".$teamstr.")";	
					
					
				}


				if(@$request->session()->get('sename_ENBD_internal') != '')
				{
					$teamL = $request->session()->get('sename_ENBD_internal');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND se_name IN (".$teamstr.")";	
					
					
				}


				if(@$request->session()->get('actual_sename_ENBD_internal') != '')
				{
					$teamL = $request->session()->get('actual_sename_ENBD_internal');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND actual_se_name IN (".$teamstr.")";	
					
					
				}




				
				if(@$request->session()->get('emp_id_CBD_internal') != '')
				{
					$empIds = $request->session()->get('emp_id_CBD_internal');
					$empStr = '';
					foreach($empIds  as $eid)
					{
						if($empStr == '')
						{
							$empStr = "'".$eid."'";
						}
						else
						{
							$empStr = $empStr.",'".$eid."'";
						}
					}
				
					$whereRaw .= " AND emp_id IN (".$empStr.")";	
					
					
				}
				
				if(@$request->session()->get('channel_CBD_internal') != '')
				{
					$channelInternalL = $request->session()->get('channel_CBD_internal');
					$channelInternalstr = '';
					foreach($channelInternalL  as $cL)
					{
						if($channelInternalstr == '')
						{
							$channelInternalstr = "'".$cL."'";
						}
						else
						{
							$channelInternalstr = $channelInternalstr.",'".$cL."'";
						}
					}
				
					$whereRaw .= " AND channel_cbd IN (".$channelInternalstr.")";	
					
					
				}
				if(@$request->session()->get('status_AECB_CBD_internal') != '')
				{
					$statusAECBL = $request->session()->get('status_AECB_CBD_internal');
					$statusAECBstr = '';
					foreach($statusAECBL  as $sL)
					{
						if($statusAECBstr == '')
						{
							$statusAECBstr = "'".$sL."'";
						}
						else
						{
							$statusAECBstr = $statusAECBstr.",'".$sL."'";
						}
					}
				
					$whereRaw .= " AND status_AECB_cbd IN (".$statusAECBstr.")";	
					
					
				}
				
				if(@$request->session()->get('card_type_EIB_internal') != '')
				{
					$cardTypeInterbalL = $request->session()->get('card_type_EIB_internal');
					$cardTypeInterbalstr = '';
					foreach($cardTypeInterbalL  as $CY)
					{
						if($cardTypeInterbalstr == '')
						{
							$cardTypeInterbalstr = "'".$CY."'";
						}
						else
						{
							$cardTypeInterbalstr = $cardTypeInterbalstr.",'".$CY."'";
						}
					}
				
					$whereRaw .= " AND card_type IN (".$cardTypeInterbalstr.")";	
					
					
				}


				if(@$request->session()->get('card_status_EIB_internal') != '')
				{
					$cardTypeInterbalL = $request->session()->get('card_status_EIB_internal');
					$cardTypeInterbalstr = '';
					foreach($cardTypeInterbalL  as $CY)
					{
						if($cardTypeInterbalstr == '')
						{
							$cardTypeInterbalstr = "'".$CY."'";
						}
						else
						{
							$cardTypeInterbalstr = $cardTypeInterbalstr.",'".$CY."'";
						}
					}
				
					$whereRaw .= " AND card_status IN (".$cardTypeInterbalstr.")";	
					
					
				}

				if(@$request->session()->get('application_status_EIB_internal') != '')
				{
					$cardTypeInterbalL = $request->session()->get('application_status_EIB_internal');
					$cardTypeInterbalstr = '';
					foreach($cardTypeInterbalL  as $CY)
					{
						if($cardTypeInterbalstr == '')
						{
							$cardTypeInterbalstr = "'".$CY."'";
						}
						else
						{
							$cardTypeInterbalstr = $cardTypeInterbalstr.",'".$CY."'";
						}
					}
				
					$whereRaw .= " AND application_status IN (".$cardTypeInterbalstr.")";	
					
					
				}


				if(@$request->session()->get('final_decision_EIB_internal') != '')
				{
					$cardTypeInterbalL = $request->session()->get('final_decision_EIB_internal');
					$cardTypeInterbalstr = '';
					foreach($cardTypeInterbalL  as $CY)
					{
						if($cardTypeInterbalstr == '')
						{
							$cardTypeInterbalstr = "'".$CY."'";
						}
						else
						{
							$cardTypeInterbalstr = $cardTypeInterbalstr.",'".$CY."'";
						}
					}
				
					$whereRaw .= " AND final_decision IN (".$cardTypeInterbalstr.")";	
					
					
				}







				if(@$request->session()->get('eib_submission_type_internal') != '')
				{
					$submission_type_internalL = $request->session()->get('eib_submission_type_internal');
					$submission_type_internalStr = '';
					foreach($submission_type_internalL  as $ST)
					{
						if($submission_type_internalStr == '')
						{
							$submission_type_internalStr = "'".$ST."'";
						}
						else
						{
							$submission_type_internalStr = $submission_type_internalStr.",'".$ST."'";
						}
					}
				
					$whereRaw .= " AND matched_status IN (".$submission_type_internalStr.")";	
					
					
				}
				
				if($request->session()->get('start_date_application_EIB_internal') != '')
				{
					$start_date_application_EIB_internal = $request->session()->get('start_date_application_EIB_internal');			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_EIB_internal))."'";
					$searchValues['start_date_application_EIB_internal'] = $start_date_application_EIB_internal;			
				}

				if($request->session()->get('end_date_application_EIB_internal') != '')
				{
					$end_date_application_EIB_internal = $request->session()->get('end_date_application_EIB_internal');			
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_EIB_internal))."'";
					$searchValues['end_date_application_EIB_internal'] = $end_date_application_EIB_internal;			
				}
		
		}



		$endDate = date("Y-m-d");
		$startDate = date("Y").'-'.date("m").'-'.'01';		



				$currentDate = date("d",strtotime(date("Y-m-d")));
				
				if($currentDate<=05){
					$endDate = date("Y").'-'.date("m").'-'.'05';
				}
				else{
					$endDate = date("Y-m-d");
				}
				$startDate = date("Y",strtotime(date("Y-m-d"))).'-'.date("m",strtotime("-1 Month")).'-'.'06';

				
				//$startDate; echo "<br/>";
				//$endDate;echo "<br/>";
	
				//return "Hello";

		

		$departmentFormParentTotal = DB::table('eib_department_form_parent_entry')->whereRaw($whereRaw)->whereBetween('application_date', [$startDate, $endDate])->orderby('id','DESC')->get()->count();

		$departmentFormParentDetails = DB::table('eib_department_form_parent_entry')->whereRaw($whereRaw)->whereBetween('application_date', [$startDate, $endDate])->orderby('id','DESC')->paginate($paginationValue);

		

		

		
		/*
		*employee Id
		*/
			$employeeIdList = DepartmentFormEntry::select('emp_id')->where("form_id",4)->get()->unique('emp_id');
			
		/*
		*employee Id
		*/
		
		
		/*
		*status
		*/
			$formStatusData = DepartmentFormEntry::select('form_status')->where("form_id",4)->get()->unique('form_status');
			
		/*
		*status
		*/
		
		/*
		*Team
		*/
			$teamData = EIBDepartmentFormEntry::select('tl_name')->where("form_id",4)->get()->unique('tl_name');
			$empNameData = EIBDepartmentFormEntry::select('se_name')->where("form_id",4)->get()->unique('se_name');
			$actualempNameData = EIBDepartmentFormEntry::select('actual_se_name')->where("form_id",4)->get()->unique('actual_se_name');
			
		/*
		*Team
		*/
		
		
		/*
		*channel_cbd
		*/
			$channel_cbd = DepartmentFormEntry::select('channel_cbd')->where("form_id",4)->get()->unique('channel_cbd');
			
		/*
		*channel_cbd
		*/
		
		/*
		*status_AECB_cbd
		*/
			$status_AECB_cbd = DepartmentFormEntry::select('status_AECB_cbd')->where("form_id",4)->get()->unique('status_AECB_cbd');
			
		/*
		*status_AECB_cbd
		*/
		
		
		/*
		*card_type_cbd
		*/
			$card_type_cbd = EIBDepartmentFormEntry::select('card_type')->where("form_id",4)->get()->unique('card_type');
			$card_status = EIBDepartmentFormEntry::select('card_status')->where("form_id",4)->get()->unique('card_status');
			$application_status = EIBDepartmentFormEntry::select('application_status')->where("form_id",4)->get()->unique('application_status');
			$final_decision = EIBDepartmentFormEntry::select('final_decision')->where("form_id",4)->get()->unique('final_decision');
			
		/*
		*card_type_cbd
		*/
		//return $card_type_cbd;

        return view("Banks/EIB/loadBankContentsEIBCards",compact('id','departmentFormDetails','DepartmentNameDetails','departmentFormParentDetails','departmentFormParentTotal','searchValues','employeeIdList','formStatusData','teamData','channel_cbd','status_AECB_cbd','card_type_cbd','card_status','application_status','final_decision','empNameData','actualempNameData'));
	}


	public function loadBankContentsEIBCardsCurrentMonth(Request $request)
	{

		$form_id = 4;
		$searchValues = array();

		$paginationValue = 20;
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}		
		
		$id = $form_id;
		$departmentFormDetails =   DepartmentForm::where("id",$form_id)->first(); 
		$DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		$where_array = array('form_id'=> $form_id);
		$whereRaw = " form_id='".$form_id."' AND (status='1' OR status='2')";

		
		

		

		/* if(@$request->session()->get('form_status') != '')
		{
			$form_status = $request->session()->get('form_status');
			$form_status_str = '';
			foreach($form_status as $form_status_value)
			{
				if($form_status_str == '')
				{
					$form_status_str = "'".$form_status_value."'";
				}
				else
				{
					$form_status_str = $form_status_str.","."'".$form_status_value."'";
				}
			}
			$whereRaw .= " AND form_status IN (".$form_status_str.")";			
		} */
		
		if(@$request->session()->get('master_cbd_search_internal') != '' && @$request->session()->get('master_cbd_search_internal') == 2)
		{
			if(@$request->session()->get('application_no_EIB_master') != '')
			{
				$applicationNO = $request->session()->get('application_no_EIB_master');
				
			
				$whereRaw .= " AND application_no like '%".$applicationNO."%'";	
				
				
			}
				
				
				
				if(@$request->session()->get('team_EIB_master') != '')
				{
					$teamL = $request->session()->get('team_EIB_master');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND tl_name IN (".$teamstr.")";	
					
					
				}



				if(@$request->session()->get('sename_EIB_master') != '')
				{
					$teamL = $request->session()->get('sename_EIB_master');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND se_name IN (".$teamstr.")";	
					
					
				}
				if(@$request->session()->get('actualsename_EIB_master') != '')
				{
					$teamL = $request->session()->get('actualsename_EIB_master');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND actual_se_name IN (".$teamstr.")";	
					
					
				}







				
				if(@$request->session()->get('emp_id_CBD_master') != '')
				{
					$empIds = $request->session()->get('emp_id_CBD_master');
					$empStr = '';
					foreach($empIds  as $eid)
					{
						if($empStr == '')
						{
							$empStr = "'".$eid."'";
						}
						else
						{
							$empStr = $empStr.",'".$eid."'";
						}
					}
				
					$whereRaw .= " AND emp_id IN (".$empStr.")";	
					
					
				}
				
				if($request->session()->get('start_date_application_EIB_master') != '')
				{
					$start_date_application_EIB_internal = $request->session()->get('start_date_application_EIB_master');			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_EIB_internal))."'";
					$searchValues['start_date_application_EIB_master'] = $start_date_application_EIB_internal;			
				}

				if($request->session()->get('end_date_application_EIB_master') != '')
				{
					$end_date_application_EIB_internal = $request->session()->get('end_date_application_EIB_master');			
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_EIB_internal))."'";
					$searchValues['end_date_application_EIB_master'] = $end_date_application_EIB_internal;			
				}
		}
		else
		{
				
			// echo "wwwwwwww";
			// exit;
			
			if(@$request->session()->get('app_no_EIB_internal_CurrentMonth') != '')
				{
					$appNO = $request->session()->get('app_no_EIB_internal_CurrentMonth');
					
				
					$whereRaw .= " AND application_no like '%".$appNO."%'";	
					
					
				}
				if(@$request->session()->get('form_status_CBD_internal') != '')
				{
					$status = $request->session()->get('form_status_CBD_internal');
					$strStatus = '';
					foreach($status  as $s)
					{
						if($strStatus == '')
						{
							$strStatus = "'".$s."'";
						}
						else
						{
							$strStatus = $strStatus.",'".$s."'";
						}
					}
				
					$whereRaw .= " AND form_status IN (".$strStatus.")";	
					
					
				}
				
				
				if(@$request->session()->get('team_ENBD_internal_CurrentMonth') != '')
				{
					$teamL = $request->session()->get('team_ENBD_internal_CurrentMonth');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND tl_name IN (".$teamstr.")";	
					
					
				}


				if(@$request->session()->get('sename_ENBD_internal_CurrentMonth') != '')
				{
					$teamL = $request->session()->get('sename_ENBD_internal_CurrentMonth');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND se_name IN (".$teamstr.")";	
					
					
				}


				if(@$request->session()->get('actual_sename_ENBD_internal_CurrentMonth') != '')
				{
					$teamL = $request->session()->get('actual_sename_ENBD_internal_CurrentMonth');
					$teamstr = '';
					foreach($teamL  as $lS)
					{
						if($teamstr == '')
						{
							$teamstr = "'".$lS."'";
						}
						else
						{
							$teamstr = $teamstr.",'".$lS."'";
						}
					}
				
					$whereRaw .= " AND actual_se_name IN (".$teamstr.")";	
					
					
				}




				
				if(@$request->session()->get('emp_id_CBD_internal') != '')
				{
					$empIds = $request->session()->get('emp_id_CBD_internal');
					$empStr = '';
					foreach($empIds  as $eid)
					{
						if($empStr == '')
						{
							$empStr = "'".$eid."'";
						}
						else
						{
							$empStr = $empStr.",'".$eid."'";
						}
					}
				
					$whereRaw .= " AND emp_id IN (".$empStr.")";	
					
					
				}
				
				if(@$request->session()->get('channel_CBD_internal') != '')
				{
					$channelInternalL = $request->session()->get('channel_CBD_internal');
					$channelInternalstr = '';
					foreach($channelInternalL  as $cL)
					{
						if($channelInternalstr == '')
						{
							$channelInternalstr = "'".$cL."'";
						}
						else
						{
							$channelInternalstr = $channelInternalstr.",'".$cL."'";
						}
					}
				
					$whereRaw .= " AND channel_cbd IN (".$channelInternalstr.")";	
					
					
				}
				if(@$request->session()->get('status_AECB_CBD_internal') != '')
				{
					$statusAECBL = $request->session()->get('status_AECB_CBD_internal');
					$statusAECBstr = '';
					foreach($statusAECBL  as $sL)
					{
						if($statusAECBstr == '')
						{
							$statusAECBstr = "'".$sL."'";
						}
						else
						{
							$statusAECBstr = $statusAECBstr.",'".$sL."'";
						}
					}
				
					$whereRaw .= " AND status_AECB_cbd IN (".$statusAECBstr.")";	
					
					
				}
				
				if(@$request->session()->get('card_type_EIB_internal_CurrentMonth') != '')
				{
					$cardTypeInterbalL = $request->session()->get('card_type_EIB_internal_CurrentMonth');
					$cardTypeInterbalstr = '';
					foreach($cardTypeInterbalL  as $CY)
					{
						if($cardTypeInterbalstr == '')
						{
							$cardTypeInterbalstr = "'".$CY."'";
						}
						else
						{
							$cardTypeInterbalstr = $cardTypeInterbalstr.",'".$CY."'";
						}
					}
				
					$whereRaw .= " AND card_type IN (".$cardTypeInterbalstr.")";	
					
					
				}


				if(@$request->session()->get('card_status_EIB_internal_CurrentMonth') != '')
				{
					$cardTypeInterbalL = $request->session()->get('card_status_EIB_internal_CurrentMonth');
					$cardTypeInterbalstr = '';
					foreach($cardTypeInterbalL  as $CY)
					{
						if($cardTypeInterbalstr == '')
						{
							$cardTypeInterbalstr = "'".$CY."'";
						}
						else
						{
							$cardTypeInterbalstr = $cardTypeInterbalstr.",'".$CY."'";
						}
					}
				
					$whereRaw .= " AND card_status IN (".$cardTypeInterbalstr.")";	
					
					
				}

				if(@$request->session()->get('application_status_EIB_internal_CurrentMonth') != '')
				{
					$cardTypeInterbalL = $request->session()->get('application_status_EIB_internal_CurrentMonth');
					$cardTypeInterbalstr = '';
					foreach($cardTypeInterbalL  as $CY)
					{
						if($cardTypeInterbalstr == '')
						{
							$cardTypeInterbalstr = "'".$CY."'";
						}
						else
						{
							$cardTypeInterbalstr = $cardTypeInterbalstr.",'".$CY."'";
						}
					}
				
					$whereRaw .= " AND application_status IN (".$cardTypeInterbalstr.")";	
					
					
				}


				if(@$request->session()->get('final_decision_EIB_internal_CurrentMonth') != '')
				{
					$cardTypeInterbalL = $request->session()->get('final_decision_EIB_internal_CurrentMonth');
					$cardTypeInterbalstr = '';
					foreach($cardTypeInterbalL  as $CY)
					{
						if($cardTypeInterbalstr == '')
						{
							$cardTypeInterbalstr = "'".$CY."'";
						}
						else
						{
							$cardTypeInterbalstr = $cardTypeInterbalstr.",'".$CY."'";
						}
					}
				
					$whereRaw .= " AND final_decision IN (".$cardTypeInterbalstr.")";	
					
					
				}







				if(@$request->session()->get('eib_submission_type_internal_CurrentMonth') != '')
				{
					$submission_type_internalL = $request->session()->get('eib_submission_type_internal_CurrentMonth');
					$submission_type_internalStr = '';
					foreach($submission_type_internalL  as $ST)
					{
						if($submission_type_internalStr == '')
						{
							$submission_type_internalStr = "'".$ST."'";
						}
						else
						{
							$submission_type_internalStr = $submission_type_internalStr.",'".$ST."'";
						}
					}
				
					$whereRaw .= " AND matched_status IN (".$submission_type_internalStr.")";	
					
					
				}
				
				if($request->session()->get('start_date_application_EIB_internal_CurrentMonth') != '')
				{
					$start_date_application_EIB_internal_CurrentMonth = $request->session()->get('start_date_application_EIB_internal_CurrentMonth');			
					$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_EIB_internal_CurrentMonth))."'";
					$searchValues['start_date_application_EIB_internal_CurrentMonth'] = $start_date_application_EIB_internal_CurrentMonth;			
				}

				if($request->session()->get('end_date_application_EIB_internal_CurrentMonth') != '')
				{
					$end_date_application_EIB_internal_CurrentMonth = $request->session()->get('end_date_application_EIB_internal_CurrentMonth');			
					$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_EIB_internal_CurrentMonth))."'";
					$searchValues['end_date_application_EIB_internal_CurrentMonth'] = $end_date_application_EIB_internal_CurrentMonth;			
				}
		
		}

		// echo $whereRaw;
		// exit;

		
		//$endDate = date("Y-m-d");
		//$startDate = date("Y").'-'.date("m").'-'.'01';		

		$departmentFormParentTotal = DB::table('eib_department_form_parent_entry')->whereRaw($whereRaw)->orderby('id','DESC')->get()->count();

		$departmentFormParentDetails = DB::table('eib_department_form_parent_entry')->whereRaw($whereRaw)->orderby('id','DESC')->paginate($paginationValue);

		

		

		
		/*
		*employee Id
		*/
			$employeeIdList = DepartmentFormEntry::select('emp_id')->where("form_id",4)->get()->unique('emp_id');
			
		/*
		*employee Id
		*/
		
		
		/*
		*status
		*/
			$formStatusData = DepartmentFormEntry::select('form_status')->where("form_id",4)->get()->unique('form_status');
			
		/*
		*status
		*/
		
		/*
		*Team
		*/
			$teamData = EIBDepartmentFormEntry::select('tl_name')->where("form_id",4)->get()->unique('tl_name');
			$empNameData = EIBDepartmentFormEntry::select('se_name')->where("form_id",4)->get()->unique('se_name');
			$actualempNameData = EIBDepartmentFormEntry::select('actual_se_name')->where("form_id",4)->get()->unique('actual_se_name');
			
		/*
		*Team
		*/
		
		
		/*
		*channel_cbd
		*/
			$channel_cbd = DepartmentFormEntry::select('channel_cbd')->where("form_id",4)->get()->unique('channel_cbd');
			
		/*
		*channel_cbd
		*/
		
		/*
		*status_AECB_cbd
		*/
			$status_AECB_cbd = DepartmentFormEntry::select('status_AECB_cbd')->where("form_id",4)->get()->unique('status_AECB_cbd');
			
		/*
		*status_AECB_cbd
		*/
		
		
		/*
		*card_type_cbd
		*/
			$card_type_cbd = EIBDepartmentFormEntry::select('card_type')->where("form_id",4)->get()->unique('card_type');
			$card_status = EIBDepartmentFormEntry::select('card_status')->where("form_id",4)->get()->unique('card_status');
			$application_status = EIBDepartmentFormEntry::select('application_status')->where("form_id",4)->get()->unique('application_status');
			$final_decision = EIBDepartmentFormEntry::select('final_decision')->where("form_id",4)->get()->unique('final_decision');
			
		/*
		*card_type_cbd
		*/
		//return $card_type_cbd;

        return view("Banks/EIB/loadBankContentsEIBCardsCurrentMonth",compact('id','departmentFormDetails','DepartmentNameDetails','departmentFormParentDetails','departmentFormParentTotal','searchValues','employeeIdList','formStatusData','teamData','channel_cbd','status_AECB_cbd','card_type_cbd','card_status','application_status','final_decision','empNameData','actualempNameData'));
		
		
		//return view("Banks/EIB/loadBankContentsEIBCardsCurrentMonth");
	}


	public function addEIBEntryPost(Request $request)
 	{
			//return $request->all();
		
			$postData = $request->input();
			
			$postDataInput = $postData['attribute_value'];

			$seempdata = explode("~",$postDataInput['se_name_eib']);
			$actualseempdata = explode("~",$postDataInput['actual_se_name_eib']);
			$entry_obj = new EIBDepartmentFormEntry();			
	
			/*
			*parent entry 
			*start code
			*/
			//$entry_obj->ref_no = $postDataInput['ref_no'];
			$entry_obj->form_id = 4;
			$entry_obj->form_title = 'EIB Internal MIS';
			$entry_obj->card_status = $postDataInput['card_status_eib'];
			//$entry_obj->team = $postDataInput['sm_name_cbd'];
			$entry_obj->customer_name = $postDataInput['cust_name_eib'];
			$entry_obj->customer_mobile = $postDataInput['mobile_eib'];
			$entry_obj->remarks = $postDataInput['remarks_eib'];
			$entry_obj->se_code = $postDataInput['se_code_eib'];
			$entry_obj->emp_id = $seempdata[0];
			$entry_obj->emp_name = $seempdata[1];
			$entry_obj->se_name = $seempdata[1];
			$entry_obj->tl_name = $postDataInput['tl_name_eib'];
			$entry_obj->salary = $postDataInput['salary_eib'];

			
			$entry_obj->actual_se_emp_id = $actualseempdata[0];
			$entry_obj->actual_se_name = $actualseempdata[1];

			
			
				// $sourceCode = $postDataInput['agent_code_cbd'];
				// $empMod = Employee_details::select("emp_id")->where("source_code",$sourceCode)->first();
				// if($empMod != '')
				// {
				// 	$entry_obj->emp_id = $empMod->emp_id;
				// }
				
			
			
			$entry_obj->application_no = $postDataInput['app_id_eib'];
			$entry_obj->bpm_id = $postDataInput['bpm_id_eib'];
			$entry_obj->card_type = trim($postDataInput['card_type_eib']);
			$entry_obj->banking_with = $postDataInput['banking_with_eib'];
			$entry_obj->emirates_id = $postDataInput['emirates_id_eib'];
			$entry_obj->application_date = date("Y-m-d",strtotime($postDataInput['date_eib']));
			$entry_obj->Status = 1;
			// $entry_obj->cbd_marging_status = 1;	
			$entry_obj->missing_internal = 3;	
			$entry_obj->save();
			$insertID = $entry_obj->id;
			/*
			*parent entry 
			*end code
			*/
			
			
			/*
			*child entry 
			*start code
			*/
			$child_obj = new EIBDepartmentFormChildEntry();
			foreach($postDataInput as $key=>$value)
			{
				$child_obj = new EIBDepartmentFormChildEntry();
				$child_obj->parent_id = $insertID;
				$child_obj->form_id = 4;
				$child_obj->attribute_code = $key;
				$child_obj->attribute_value = $value;
				$child_obj->status = 1;
				$child_obj->save();
			}
			
			/*
			*child entry 
			*end code
			*/
            $request->session()->flash('message','Record added Successfully.');
			return redirect('eibCardsManagement');
	 
 	}



	 public function viewPanelAsperFileSourceEIB($parent_id=NULL,$form_id=NULL)
	 {
		$departmentFormDetails =   DepartmentForm::where("id",$form_id)->first();		  
		$DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		$masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
		$DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
		$FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

		$departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', $form_id)->groupby('form_section')->orderby('sort_order','ASC')->get(['form_section']);

		$departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

		$departmentFormParentDetails = DB::table('eib_department_form_parent_entry')->where('id', $parent_id)->first();

		$departmentFormChildDetails = DB::table('eib_department_form_child_entry')->where('parent_id', $parent_id)->where('form_id', $form_id)->get();

		$Employee_details = Employee_details::where("offline_status",1)->orderby('first_name','ASC')->get();
		
		return view("Banks/EIB/viewPanelAsperFileSourceEIB",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','departmentFormParentDetails','departmentFormChildDetails','Employee_details','DepartmentNameDetails'));
	 }


	 public function editEIBCards($parent_id=NULL,$form_id=NULL)
	 {
		$departmentFormDetails =   DepartmentForm::where("id",$form_id)->first();		  
		$DepartmentNameDetails =   Department::where("id",$departmentFormDetails->department_id)->first();
		$masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get(); 
		$DepartmentDetails = Department::where("status",1)->orwhere("status",2)->orderBy('department_name','ASC')->get();
		$FormSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();

		$departmentFormAttributeGroup = DB::table('department_form_attribute')->where('form_id', $form_id)->groupby('form_section')->orderby('sort_order','ASC')->get(['form_section']);

		$departmentFormAttributeDetails = DB::table('department_form_attribute')->where('form_id', $form_id)->orderby('form_section','ASC')->orderby('sort_order','ASC')->get();

		$departmentFormParentDetails = DB::table('eib_department_form_parent_entry')->where('id', $parent_id)->first();

		$departmentFormChildDetails = DB::table('eib_department_form_child_entry')->where('parent_id', $parent_id)->where('form_id', $form_id)->get();

		//return $departmentFormChildDetails;

		$Employee_details = Employee_details::where("offline_status",1)->where("dept_id",52)->where("job_function",2)->orderby('first_name','ASC')->get();
	
		return view("Banks/EIB/editEibCards",compact('departmentFormDetails','departmentFormAttributeDetails','DepartmentDetails','masterAttributeDetails','FormSectionDetails','departmentFormAttributeGroup','departmentFormParentDetails','departmentFormChildDetails','Employee_details','DepartmentNameDetails'));
	 }


	public function editEIBFormEntryPostData(Request $request)
 	{
			//return $request->all();
			$postData = $request->input();
			$postDataInput = $postData['attribute_value'];
			

			$employee_data = Employee_details::where("emp_id",$postDataInput['se_name_eib'])->where("offline_status",1)->where("dept_id",52)->where("job_function",2)->orderby('first_name','ASC')->first();

			$entry_objUpdate = EIBDepartmentFormEntry::find($postData['parent_id']);	
			
			
	
			/*
			*parent entry 
			*start code
			*/
			//$entry_objUpdate->ref_no = $postDataInput['ref_no'];
			$entry_objUpdate->form_id = 4;
			$entry_objUpdate->form_title = 'EIB Internal MIS';
			$entry_objUpdate->card_status = $postDataInput['card_status_eib'];
			//$entry_objUpdate->team = $postDataInput['sm_name_cbd'];
			$entry_objUpdate->customer_name = $postDataInput['cust_name_eib'];
			$entry_objUpdate->customer_mobile = $postDataInput['mobile_eib'];
			$entry_objUpdate->remarks = $postDataInput['remarks_eib'];
			$entry_objUpdate->se_code = $postDataInput['se_code_eib'];
			//$entry_objUpdate->se_name = $postDataInput['se_name_eib'];

			if($employee_data)
			{
				$entry_objUpdate->se_name = $employee_data->emp_name;				
				$entry_objUpdate->emp_name = $employee_data->emp_name;
			}
			
			$entry_objUpdate->emp_id = $postDataInput['se_name_eib'];
			$empName = $this->getEmployeeName($postDataInput['se_name_eib']);
			$entry_objUpdate->se_name = $empName;
			$entry_objUpdate->emp_name = $empName;
			$entry_objUpdate->actual_se_emp_id = $postDataInput['actual_se_name_eib'];
			$actualempName = $this->getEmployeeName($postDataInput['actual_se_name_eib']);
			$entry_objUpdate->actual_se_name = $actualempName;


			$entry_objUpdate->tl_name = $postDataInput['tl_name_eib'];
			$entry_objUpdate->salary = $postDataInput['salary_eib'];
			
			
			// $entry_objUpdate->agent_code = $postDataInput['agent_code_cbd'];
		
			// 	$sourceCode = $postDataInput['agent_code_cbd'];
			// 	$empMod = Employee_details::select("emp_id")->where("source_code",$sourceCode)->first();
			// 	if($empMod != '')
			// 	{
			// 		$entry_objUpdate->emp_id = $empMod->emp_id;
			// 	}
		
			
			$entry_objUpdate->application_no = $postDataInput['app_id_eib'];
			$entry_objUpdate->bpm_id = $postDataInput['bpm_id_eib'];
			$entry_objUpdate->card_type = trim($postDataInput['card_type_eib']);
			$entry_objUpdate->banking_with = $postDataInput['banking_with_eib'];
			$entry_objUpdate->emirates_id = $postDataInput['emirates_id_eib'];
			$entry_objUpdate->application_date = date("Y-m-d",strtotime($postDataInput['date_eib']));

			// $seempdata = explode("~",$postDataInput['se_name_eib']);
			// $actualseempdata = explode("~",$postDataInput['actual_se_name_eib']);
	
			// $entry_objUpdate->actual_se_emp_id = $actualseempdata[0];
			// $entry_objUpdate->actual_se_name = $actualseempdata[1];



			$entry_objUpdate->save();
			$insertID = $entry_objUpdate->id;
			/*
			*parent entry 
			*end code
			*/
			
			
			/*
			*child entry 
			*start code
			*/
			
			foreach($postDataInput as $key=>$value)
			{
				$existChild = EIBDepartmentFormChildEntry::where("parent_id",$postData['parent_id'])->where("attribute_code",$key)->first();
				if($existChild != '')
				{
					$child_obj = EIBDepartmentFormChildEntry::find($existChild->id);
					$child_obj->parent_id = $insertID;
					$child_obj->form_id = 4;
					$child_obj->attribute_code = $key;
					$child_obj->attribute_value = $value;
					$child_obj->status = 1;
					$child_obj->save();
				}
				else
				{
					$child_obj = new EIBDepartmentFormChildEntry();
					$child_obj->parent_id = $insertID;
					$child_obj->form_id = 4;
					$child_obj->attribute_code = $key;
					$child_obj->attribute_value = $value;
					$child_obj->status = 1;
					$child_obj->save();
				}
			}
			
			/*
			*child entry 
			*end code
			*/
            $request->session()->flash('message','Record Updated Successfully.');
			return redirect('eibCardsManagement');
			
 	}




	 public function EIBSearchMasterData(Request $request)
	 {
		 $requestParameters = $request->input();
				 
				 $start_date_application = '';
				 $end_date_application = '';
				 $team = '';
			 
				 $application_no = '';
				 $emp_id = '';
				 $sename = '';
				 $actualsename = '';
 
				 if(@isset($requestParameters['application_no']))
				 {
					 $application_no = @$requestParameters['application_no'];
				 }
 
				 if(isset($requestParameters['teammaster']))
				 {
					 $team = @$requestParameters['teammaster'];
				 }
				 if(isset($requestParameters['senamemaster']))
				 {
					 $sename = @$requestParameters['senamemaster'];
				 }
				 if(isset($requestParameters['actualsenamemaster']))
				 {
					 $actualsename = @$requestParameters['actualsenamemaster'];
				 }
				 
				 
 
				 
				 if(isset($requestParameters['emp_id']))
				 {
					 $emp_id = @$requestParameters['emp_id'];
				 }
 
				 if(isset($requestParameters['start_date']))
				 {
					 $start_date_application = @$requestParameters['start_date'];
				 }
				 if(isset($requestParameters['end_date']))
				 {
					 $end_date_application = @$requestParameters['end_date'];
				 }
				 
				 $request->session()->put('application_no_EIB_master',$application_no);
				 $request->session()->put('team_EIB_master',$team);
				 $request->session()->put('sename_EIB_master',$sename);
				 $request->session()->put('actualsename_EIB_master',$actualsename);
				 $request->session()->put('emp_id_CBD_master',$emp_id);
				 $request->session()->put('start_date_application_EIB_master',$start_date_application);
				 $request->session()->put('end_date_application_EIB_master',$end_date_application);
				 $request->session()->put('master_cbd_search_internal',2);
				 $request->session()->put('master_cbd_search_bank',2);
				 return redirect("eibCardsManagement");
		 
		 
	 }

	 public function resetEIBMasterData(Request $request)
	 {
				 $request->session()->put('application_no_EIB_master','');
				 $request->session()->put('team_EIB_master','');
				 $request->session()->put('sename_EIB_master','');
				 $request->session()->put('actualsename_EIB_master','');
				 $request->session()->put('emp_id_CBD_master','');
				 $request->session()->put('start_date_application_EIB_master','');
				 $request->session()->put('end_date_application_EIB_master','');
				 $request->session()->put('master_cbd_search_internal','');
				 $request->session()->put('master_cbd_search_bank','');
				 return redirect("eibCardsManagement");
	 }




	 public function loadBankContentsEIBBAnkMisData(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}

		$whereRaw = " application_no!=''";	
		if(@$request->session()->get('master_cbd_search_bank') != '' && @$request->session()->get('master_cbd_search_bank') == 2)
		{
				  if(@$request->session()->get('application_no_EIB_master') != '')
					{
						$applicationNO = $request->session()->get('application_no_EIB_master');
						
					
						//$whereRaw .= " AND ref_no like '%".$refNO."%'";	
						$whereRaw .= " AND application_no like '%".$applicationNO."%'";	
						
						
					}
					
					if(@$request->session()->get('emp_id_CBD_master') != '')
					{
						$employeeMod = $request->session()->get('emp_id_CBD_master');
						$employeeModStr = '';
						foreach($employeeMod  as $modID)
						{
							if($employeeModStr == '')
							{
								$employeeModStr = "'".$modID."'";
							}
							else
							{
								$employeeModStr = $employeeModStr.",'".$modID."'";
							}
						}
					
						$whereRaw .= " AND employee_id IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('team_EIB_master') != '')
					{
						$SMMod = $request->session()->get('team_EIB_master');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND tl_name IN (".$smStr.")";	
						
						
					}





					if(@$request->session()->get('sename_EIB_master') != '')
					{
						$SMMod = $request->session()->get('sename_EIB_master');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND se_name IN (".$smStr.")";	
						
						
					}


					if(@$request->session()->get('actualsename_EIB_master') != '')
					{
						$SMMod = $request->session()->get('actualsename_EIB_master');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND actual_se_name IN (".$smStr.")";	
						
						
					}
					
					if($request->session()->get('start_date_application_EIB_master') != '')
					{
						$start_date_application_EIB_internal = $request->session()->get('start_date_application_EIB_master');			
						$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_EIB_internal))."'";
						$searchValues['start_date_application_EIB_master'] = $start_date_application_EIB_internal;			
					}

					if($request->session()->get('end_date_application_EIB_master') != '')
					{
						$end_date_application_EIB_internal = $request->session()->get('end_date_application_EIB_master');			
						$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_EIB_internal))."'";
						$searchValues['end_date_application_EIB_master'] = $end_date_application_EIB_internal;			
					}
		}
		else
		{
					if(@$request->session()->get('app_no_EIB_bank') != '')
					{
						$appNO = $request->session()->get('app_no_EIB_bank');
						
					
						$whereRaw .= " AND application_no like '%".$appNO."%'";	
						
						
					}
					if(@$request->session()->get('status_CBD_bank') != '')
					{
						$status = $request->session()->get('status_CBD_bank');
						$strStatus = '';
						foreach($status  as $s)
						{
							if($strStatus == '')
							{
								$strStatus = "'".$s."'";
							}
							else
							{
								$strStatus = $strStatus.",'".$s."'";
							}
						}
					
						$whereRaw .= " AND status IN (".$strStatus.")";	
						
						
					}
					
					
					if(@$request->session()->get('AECB_Status_CBD_bank') != '')
					{
						$statusAECB = $request->session()->get('AECB_Status_CBD_bank');
						$strStatusAECB = '';
						foreach($statusAECB  as $sAECB)
						{
							if($strStatusAECB == '')
							{
								$strStatusAECB = "'".$sAECB."'";
							}
							else
							{
								$strStatusAECB = $strStatusAECB.",'".$sAECB."'";
							}
						}
					
						$whereRaw .= " AND AECB_Status IN (".$strStatusAECB.")";	
						
						
					}
					if(@$request->session()->get('employee_id_CBD_bank') != '')
					{
						$employeeMod = $request->session()->get('employee_id_CBD_bank');
						$employeeModStr = '';
						foreach($employeeMod  as $modID)
						{
							if($employeeModStr == '')
							{
								$employeeModStr = "'".$modID."'";
							}
							else
							{
								$employeeModStr = $employeeModStr.",'".$modID."'";
							}
						}
					
						$whereRaw .= " AND employee_id IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('team_ENBD_Bank_internal') != '')
					{
						$SMMod = $request->session()->get('team_ENBD_Bank_internal');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND tl_name IN (".$smStr.")";						
					}

					if(@$request->session()->get('seName_ENBD_Bank_internal') != '')
					{
						$SMMod = $request->session()->get('seName_ENBD_Bank_internal');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND se_name IN (".$smStr.")";						
					}


					if(@$request->session()->get('actualseName_ENBD_Bank_internal') != '')
					{
						$SMMod = $request->session()->get('actualseName_ENBD_Bank_internal');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND actual_se_name IN (".$smStr.")";						
					}





					
					if($request->session()->get('start_date_creation_EIB_bank') != '')
					{
						$start_date_creation_EIB_bank = $request->session()->get('start_date_creation_EIB_bank');			
						$whereRaw .= " AND creation_date >='".date('Y-m-d',strtotime($start_date_creation_EIB_bank))."'";
						$searchValues['start_date_creation_EIB_bank'] = $start_date_creation_EIB_bank;			
					}

					if($request->session()->get('end_date_creation_EIB_bank') != '')
					{
						$end_date_creation_EIB_bank = $request->session()->get('end_date_creation_EIB_bank');			
						$whereRaw .= " AND creation_date <='".date('Y-m-d',strtotime($end_date_creation_EIB_bank))."'";
						$searchValues['end_date_creation_EIB_bank'] = $end_date_creation_EIB_bank;			
					}
					if($request->session()->get('submission_type_EIB_Bank') != '')
					{
						
						$submission_type = $request->session()->get('submission_type_EIB_Bank');
						if($submission_type == 'Linked')
						{
							$whereRaw .= " AND matched_status =1";
						}
						else if($submission_type == 'Missing')
						{
							$whereRaw .= " AND matched_status IS NULL";
						}
						else
						{
							
						}
						
					}
					
					

		}
		//echo $whereRaw;exit;
		$datasCBDMainCount = EibBankMis::whereRaw($whereRaw)->get()->count();
		
		$datasCBDMain = EibBankMis::whereRaw($whereRaw)->orderBy("application_date","DESC")->paginate($paginationValue);

		/*
		*application  Status
		*/
			//$appStatusMod = EibBankMis::select('status')->get()->unique('status');
			$appStatusMod = '';
			
		/*
		*application  Status
		*/

		/*
		*application  Status
		*/
			//$appAECB_StatusMod = EibBankMis::select('AECB_Status')->get()->unique('AECB_Status');
			$appAECB_StatusMod = '';
		/*
		*application  Status
		*/
		
		/*
		*application  Status
		*/
			//$employeeIdList = EibBankMis::select('employee_id')->get()->unique('employee_id');
			$employeeIdList = '';
		/*
		*application  Status
		*/
		
		/*
		*Sm Manager
		*/
			//$smManageData = EibBankMis::select('sm_manager')->get()->unique('sm_manager');
			$smManageData = '';
		/*
		*Sm Manager
		*/

		$teamData = EibBankMis::select('tl_name')->get()->unique('tl_name');
		$empNameData = EibBankMis::select('se_name')->get()->unique('se_name');
		$actualempNameData = EibBankMis::select('actual_se_name')->get()->unique('actual_se_name');



		 return view("Banks/EIB/loadBankContentsEIBCardBankSide",compact('datasCBDMainCount','datasCBDMain','paginationValue','searchValues','appStatusMod','appAECB_StatusMod','employeeIdList','smManageData','teamData','empNameData','actualempNameData'));
	}



	public function searchEIBInternalInnerFilter(Request $request)
	{
				$requestParameters = $request->input();
				
				$start_date_application = '';
				$end_date_application = '';
				$team = '';
				$form_status = '';
				$app_no = '';
				$emp_id = '';
				
				$channel_cbd = '';
				$status_AECB_cbd = '';
				$card_type_eib = '';
				$card_status_eib = '';
				$application_status_eib = '';
				$submission_type_inner = '';
				$final_decision_EIB = '';
				$sename = '';
				$actual_sename = '';
				
				if(isset($requestParameters['channel_cbd']))
				{
					$channel_cbd = @$requestParameters['channel_cbd'];
				}
				
				if(isset($requestParameters['status_AECB_cbd']))
				{
					$status_AECB_cbd = @$requestParameters['status_AECB_cbd'];
				}
				
				if(isset($requestParameters['card_type_eib']))
				{
					$card_type_eib = @$requestParameters['card_type_eib'];
				}
				if(isset($requestParameters['card_status_eib']))
				{
					$card_status_eib = @$requestParameters['card_status_eib'];
				}


				if(isset($requestParameters['app_status_eib']))
				{
					$application_status_eib = @$requestParameters['app_status_eib'];
				}
				if(isset($requestParameters['final_decision_eib']))
				{
					$final_decision_EIB = @$requestParameters['final_decision_eib'];
				}

				if(@isset($requestParameters['app_no']))
				{
					$app_no = @$requestParameters['app_no'];
				}

				if(isset($requestParameters['team']))
				{
					$team = @$requestParameters['team'];
				}

				if(isset($requestParameters['sename']))
				{
					$sename = @$requestParameters['sename'];
				}

				if(isset($requestParameters['actualsename']))
				{
					$actual_sename = @$requestParameters['actualsename'];
				}

				

				if(isset($requestParameters['form_status']))
				{
					$form_status = @$requestParameters['form_status'];
				}
				
				if(isset($requestParameters['emp_id']))
				{
					$emp_id = @$requestParameters['emp_id'];
				}

				if(isset($requestParameters['start_date_application']))
				{
					$start_date_application = @$requestParameters['start_date_application'];
				}
				if(isset($requestParameters['end_date_application']))
				{
					$end_date_application = @$requestParameters['end_date_application'];
				}
				if(isset($requestParameters['submission_type_inner']))
				{
					$submission_type_inner = @$requestParameters['submission_type_inner'];
				}
				$request->session()->put('master_cbd_search_internal','');
				$request->session()->put('app_no_EIB_internal',$app_no);
				$request->session()->put('form_status_CBD_internal',$form_status);
				$request->session()->put('team_ENBD_internal',$team);
				$request->session()->put('sename_ENBD_internal',$sename);
				$request->session()->put('actual_sename_ENBD_internal',$actual_sename);
				$request->session()->put('emp_id_CBD_internal',$emp_id);
				$request->session()->put('start_date_application_EIB_internal',$start_date_application);
				$request->session()->put('end_date_application_EIB_internal',$end_date_application);
				$request->session()->put('channel_CBD_internal',$channel_cbd);
				$request->session()->put('status_AECB_CBD_internal',$status_AECB_cbd);
				$request->session()->put('card_type_EIB_internal',$card_type_eib);
				$request->session()->put('card_status_EIB_internal',$card_status_eib);
				$request->session()->put('application_status_EIB_internal',$application_status_eib);
				$request->session()->put('final_decision_EIB_internal',$final_decision_EIB);				
				$request->session()->put('eib_submission_type_internal',$submission_type_inner);
				return redirect("loadBankContentEIBCard");
	}



	public function resetInnerEIBInternalFilter(Request $request)
	{
			$request->session()->put('app_no_EIB_internal','');
				$request->session()->put('form_status_CBD_internal','');
				$request->session()->put('team_ENBD_internal','');
				$request->session()->put('sename_ENBD_internal','');
				$request->session()->put('actual_sename_ENBD_internal','');
				$request->session()->put('emp_id_CBD_internal','');
				$request->session()->put('start_date_application_EIB_internal','');
				$request->session()->put('end_date_application_EIB_internal','');
				$request->session()->put('channel_CBD_internal','');
				$request->session()->put('status_AECB_CBD_internal','');
				$request->session()->put('card_type_EIB_internal','');
				$request->session()->put('card_status_EIB_internal','');
				$request->session()->put('application_status_EIB_internal','');
				$request->session()->put('final_decision_EIB_internal','');
				$request->session()->put('eib_submission_type_internal','');
				
				$request->session()->put('master_cbd_search_internal',2);
				return redirect("loadBankContentEIBCard");
	}










	public function searchEIBInternalInnerFilterCurrentMonth(Request $request)
	{
				$requestParameters = $request->input();
				
				$start_date_application = '';
				$end_date_application = '';
				$team = '';
				$form_status = '';
				$app_noCurrent = '';
				$emp_id = '';
				
				$channel_cbd = '';
				$status_AECB_cbd = '';
				$card_type_eib = '';
				$card_status_eib = '';
				$application_status_eib = '';
				$submission_type_inner = '';
				$final_decision_EIB = '';
				$sename = '';
				$actual_sename = '';
				
				if(isset($requestParameters['channel_cbd']))
				{
					$channel_cbd = @$requestParameters['channel_cbd'];
				}
				
				if(isset($requestParameters['status_AECB_cbd']))
				{
					$status_AECB_cbd = @$requestParameters['status_AECB_cbd'];
				}
				
				if(isset($requestParameters['card_type_eib_CurrentMonth']))
				{
					$card_type_eib = @$requestParameters['card_type_eib_CurrentMonth'];
				}
				if(isset($requestParameters['card_status_eib_CurrentMonth']))
				{
					$card_status_eib = @$requestParameters['card_status_eib_CurrentMonth'];
				}


				if(isset($requestParameters['app_status_eib_CurrentMonth']))
				{
					$application_status_eib = @$requestParameters['app_status_eib_CurrentMonth'];
				}
				if(isset($requestParameters['final_decision_eib_CurrentMonth']))
				{
					$final_decision_EIB = @$requestParameters['final_decision_eib_CurrentMonth'];
				}

				if(@isset($requestParameters['app_no_CurrentMonth']))
				{
					$app_noCurrent = @$requestParameters['app_no_CurrentMonth'];
				}

				if(isset($requestParameters['team_CurrentMonth']))
				{
					$team = @$requestParameters['team_CurrentMonth'];
				}

				if(isset($requestParameters['sename_CurrentMonth']))
				{
					$sename = @$requestParameters['sename_CurrentMonth'];
				}

				if(isset($requestParameters['actualsename_CurrentMonth']))
				{
					$actual_sename = @$requestParameters['actualsename_CurrentMonth'];
				}

				

				if(isset($requestParameters['form_status']))
				{
					$form_status = @$requestParameters['form_status'];
				}
				
				if(isset($requestParameters['emp_id']))
				{
					$emp_id = @$requestParameters['emp_id'];
				}

				if(isset($requestParameters['start_date_application_CurrentMonth']))
				{
					$start_date_application = @$requestParameters['start_date_application_CurrentMonth'];
				}
				if(isset($requestParameters['end_date_application_CurrentMonth']))
				{
					$end_date_application = @$requestParameters['end_date_application_CurrentMonth'];
				}
				if(isset($requestParameters['submission_type_inner_CurrentMonth']))
				{
					$submission_type_inner = @$requestParameters['submission_type_inner_CurrentMonth'];
				}
				$request->session()->put('master_cbd_search_internal','');
				$request->session()->put('app_no_EIB_internal_CurrentMonth',$app_noCurrent);
				$request->session()->put('form_status_CBD_internal',$form_status);
				$request->session()->put('team_ENBD_internal_CurrentMonth',$team);
				$request->session()->put('sename_ENBD_internal_CurrentMonth',$sename);
				$request->session()->put('actual_sename_ENBD_internal_CurrentMonth',$actual_sename);
				$request->session()->put('emp_id_CBD_internal',$emp_id);
				$request->session()->put('start_date_application_EIB_internal_CurrentMonth',$start_date_application);
				$request->session()->put('end_date_application_EIB_internal_CurrentMonth',$end_date_application);
				$request->session()->put('channel_CBD_internal',$channel_cbd);
				$request->session()->put('status_AECB_CBD_internal',$status_AECB_cbd);
				$request->session()->put('card_type_EIB_internal_CurrentMonth',$card_type_eib);
				$request->session()->put('card_status_EIB_internal_CurrentMonth',$card_status_eib);
				$request->session()->put('application_status_EIB_internal_CurrentMonth',$application_status_eib);
				$request->session()->put('final_decision_EIB_internal_CurrentMonth',$final_decision_EIB);				
				$request->session()->put('eib_submission_type_internal_CurrentMonth',$submission_type_inner);
				return redirect("loadBankContentEIBCardCurrentMonth");
	}



	public function resetInnerEIBInternalFilterCurrentMonth(Request $request)
	{
			$request->session()->put('app_no_EIB_internal_CurrentMonth','');
				$request->session()->put('form_status_CBD_internal','');
				$request->session()->put('team_ENBD_internal_CurrentMonth','');
				$request->session()->put('sename_ENBD_internal_CurrentMonth','');
				$request->session()->put('actual_sename_ENBD_internal_CurrentMonth','');
				$request->session()->put('emp_id_CBD_internal','');
				$request->session()->put('start_date_application_EIB_internal_CurrentMonth','');
				$request->session()->put('end_date_application_EIB_internal_CurrentMonth','');
				$request->session()->put('channel_CBD_internal','');
				$request->session()->put('status_AECB_CBD_internal','');
				$request->session()->put('card_type_EIB_internal_CurrentMonth','');
				$request->session()->put('card_status_EIB_internal_CurrentMonth','');
				$request->session()->put('application_status_EIB_internal_CurrentMonth','');
				$request->session()->put('final_decision_EIB_internal_CurrentMonth','');
				$request->session()->put('eib_submission_type_internal_CurrentMonth','');
				
				$request->session()->put('master_cbd_search_internal',2);
				return redirect("loadBankContentEIBCardCurrentMonth");
	}











	public function loadContentBankSideMISDataCurrentMonth(Request $request)
	{
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}

		$whereRaw = " application_no!=''";	
		if(@$request->session()->get('master_cbd_search_bank') != '' && @$request->session()->get('master_cbd_search_bank') == 2)
		{
				  if(@$request->session()->get('application_no_EIB_master') != '')
					{
						$applicationNO = $request->session()->get('application_no_EIB_master');
						
					
						//$whereRaw .= " AND ref_no like '%".$refNO."%'";	
						$whereRaw .= " AND application_no like '%".$applicationNO."%'";	
						
						
					}
					
					if(@$request->session()->get('emp_id_CBD_master') != '')
					{
						$employeeMod = $request->session()->get('emp_id_CBD_master');
						$employeeModStr = '';
						foreach($employeeMod  as $modID)
						{
							if($employeeModStr == '')
							{
								$employeeModStr = "'".$modID."'";
							}
							else
							{
								$employeeModStr = $employeeModStr.",'".$modID."'";
							}
						}
					
						$whereRaw .= " AND employee_id IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('team_EIB_master') != '')
					{
						$SMMod = $request->session()->get('team_EIB_master');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND tl_name IN (".$smStr.")";	
						
						
					}





					if(@$request->session()->get('sename_EIB_master') != '')
					{
						$SMMod = $request->session()->get('sename_EIB_master');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND se_name IN (".$smStr.")";	
						
						
					}


					if(@$request->session()->get('actualsename_EIB_master') != '')
					{
						$SMMod = $request->session()->get('actualsename_EIB_master');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND actual_se_name IN (".$smStr.")";	
						
						
					}
					
					if($request->session()->get('start_date_application_EIB_master') != '')
					{
						$start_date_application_EIB_internal = $request->session()->get('start_date_application_EIB_master');			
						$whereRaw .= " AND application_date >='".date('Y-m-d',strtotime($start_date_application_EIB_internal))."'";
						$searchValues['start_date_application_EIB_master'] = $start_date_application_EIB_internal;			
					}

					if($request->session()->get('end_date_application_EIB_master') != '')
					{
						$end_date_application_EIB_internal = $request->session()->get('end_date_application_EIB_master');			
						$whereRaw .= " AND application_date <='".date('Y-m-d',strtotime($end_date_application_EIB_internal))."'";
						$searchValues['end_date_application_EIB_master'] = $end_date_application_EIB_internal;			
					}
		}
		else
		{
					if(@$request->session()->get('app_no_EIB_bank_currentMonth') != '')
					{
						$appNO = $request->session()->get('app_no_EIB_bank_currentMonth');
						
					
						$whereRaw .= " AND application_no like '%".$appNO."%'";	
						
						
					}
					if(@$request->session()->get('status_CBD_bank') != '')
					{
						$status = $request->session()->get('status_CBD_bank');
						$strStatus = '';
						foreach($status  as $s)
						{
							if($strStatus == '')
							{
								$strStatus = "'".$s."'";
							}
							else
							{
								$strStatus = $strStatus.",'".$s."'";
							}
						}
					
						$whereRaw .= " AND status IN (".$strStatus.")";	
						
						
					}
					
					
					if(@$request->session()->get('AECB_Status_CBD_bank') != '')
					{
						$statusAECB = $request->session()->get('AECB_Status_CBD_bank');
						$strStatusAECB = '';
						foreach($statusAECB  as $sAECB)
						{
							if($strStatusAECB == '')
							{
								$strStatusAECB = "'".$sAECB."'";
							}
							else
							{
								$strStatusAECB = $strStatusAECB.",'".$sAECB."'";
							}
						}
					
						$whereRaw .= " AND AECB_Status IN (".$strStatusAECB.")";	
						
						
					}
					if(@$request->session()->get('employee_id_CBD_bank') != '')
					{
						$employeeMod = $request->session()->get('employee_id_CBD_bank');
						$employeeModStr = '';
						foreach($employeeMod  as $modID)
						{
							if($employeeModStr == '')
							{
								$employeeModStr = "'".$modID."'";
							}
							else
							{
								$employeeModStr = $employeeModStr.",'".$modID."'";
							}
						}
					
						$whereRaw .= " AND employee_id IN (".$employeeModStr.")";	
						
						
					}
					
					if(@$request->session()->get('team_ENBD_Bank_internal_currentMonth') != '')
					{
						$SMMod = $request->session()->get('team_ENBD_Bank_internal_currentMonth');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND tl_name IN (".$smStr.")";						
					}

					if(@$request->session()->get('seName_ENBD_Bank_internal_currentMonth') != '')
					{
						$SMMod = $request->session()->get('seName_ENBD_Bank_internal_currentMonth');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND se_name IN (".$smStr.")";						
					}


					if(@$request->session()->get('actualseName_ENBD_Bank_internal_currentMonth') != '')
					{
						$SMMod = $request->session()->get('actualseName_ENBD_Bank_internal_currentMonth');
						$smStr = '';
						foreach($SMMod  as $SM)
						{
							if($smStr == '')
							{
								$smStr = "'".$SM."'";
							}
							else
							{
								$smStr = $smStr.",'".$SM."'";
							}
						}
					
						$whereRaw .= " AND actual_se_name IN (".$smStr.")";						
					}





					
					if($request->session()->get('start_date_creation_EIB_bank_currentMonth') != '')
					{
						$start_date_creation_EIB_bank_currentMonth = $request->session()->get('start_date_creation_EIB_bank_currentMonth');			
						$whereRaw .= " AND creation_date >='".date('Y-m-d',strtotime($start_date_creation_EIB_bank_currentMonth))."'";
						$searchValues['start_date_creation_EIB_bank_currentMonth'] = $start_date_creation_EIB_bank;			
					}

					if($request->session()->get('end_date_creation_EIB_bank_currentMonth') != '')
					{
						$end_date_creation_EIB_bank_currentMonth = $request->session()->get('end_date_creation_EIB_bank_currentMonth');			
						$whereRaw .= " AND creation_date <='".date('Y-m-d',strtotime($end_date_creation_EIB_bank_currentMonth))."'";
						$searchValues['end_date_creation_EIB_bank_currentMonth'] = $end_date_creation_EIB_bank;			
					}
					if($request->session()->get('submission_type_EIB_Bank_currentMonth') != '')
					{
						
						$submission_type = $request->session()->get('submission_type_EIB_Bank_currentMonth');
						if($submission_type == 'Linked')
						{
							$whereRaw .= " AND matched_status =1";
						}
						else if($submission_type == 'Missing')
						{
							$whereRaw .= " AND matched_status IS NULL";
						}
						else
						{
							
						}
						
					}
					
					

		}



		//echo $whereRaw;exit;

		$endDate = date("Y-m-d");
		$startDate = date("Y").'-'.date("m").'-'.'01';		

		
		$datasCBDMainCount = EibBankMis::whereRaw($whereRaw)->whereBetween('application_date', [$startDate, $endDate])->get()->count();
		
		$datasCBDMain = EibBankMis::whereRaw($whereRaw)->whereBetween('application_date', [$startDate, $endDate])->orderBy("application_date","DESC")->paginate($paginationValue);

		/*
		*application  Status
		*/
			//$appStatusMod = EibBankMis::select('status')->get()->unique('status');
			$appStatusMod = '';
			
		/*
		*application  Status
		*/

		/*
		*application  Status
		*/
			//$appAECB_StatusMod = EibBankMis::select('AECB_Status')->get()->unique('AECB_Status');
			$appAECB_StatusMod = '';
		/*
		*application  Status
		*/
		
		/*
		*application  Status
		*/
			//$employeeIdList = EibBankMis::select('employee_id')->get()->unique('employee_id');
			$employeeIdList = '';
		/*
		*application  Status
		*/
		
		/*
		*Sm Manager
		*/
			//$smManageData = EibBankMis::select('sm_manager')->get()->unique('sm_manager');
			$smManageData = '';
		/*
		*Sm Manager
		*/

		$teamData = EibBankMis::select('tl_name')->get()->unique('tl_name');
		$empNameData = EibBankMis::select('se_name')->get()->unique('se_name');
		$actualempNameData = EibBankMis::select('actual_se_name')->get()->unique('actual_se_name');



		 return view("Banks/EIB/loadBankSideMISDataCurrentMonth",compact('datasCBDMainCount','datasCBDMain','paginationValue','searchValues','appStatusMod','appAECB_StatusMod','employeeIdList','smManageData','teamData','empNameData','actualempNameData'));
	}






	public function searchBankSideMISInnerFilterCurrentMonth(Request $request)
	{
				$requestParameters = $request->input();
				
				$start_date_application = '';
				$end_date_application = '';
				$team = '';
				$form_status = '';
				$app_noCurrent = '';
				$emp_id = '';
				
				$channel_cbd = '';
				$status_AECB_cbd = '';
				$card_type_eib = '';
				$card_status_eib = '';
				$application_status_eib = '';
				$submission_type_inner = '';
				$final_decision_EIB = '';
				$sename = '';
				$actual_sename = '';
				
				if(isset($requestParameters['channel_cbd']))
				{
					$channel_cbd = @$requestParameters['channel_cbd'];
				}
				
				if(isset($requestParameters['status_AECB_cbd']))
				{
					$status_AECB_cbd = @$requestParameters['status_AECB_cbd'];
				}
				
				if(isset($requestParameters['card_type_eib_CurrentMonth']))
				{
					$card_type_eib = @$requestParameters['card_type_eib_CurrentMonth'];
				}
				if(isset($requestParameters['card_status_eib_CurrentMonth']))
				{
					$card_status_eib = @$requestParameters['card_status_eib_CurrentMonth'];
				}


				if(isset($requestParameters['app_status_eib_CurrentMonth']))
				{
					$application_status_eib = @$requestParameters['app_status_eib_CurrentMonth'];
				}
				if(isset($requestParameters['final_decision_eib_CurrentMonth']))
				{
					$final_decision_EIB = @$requestParameters['final_decision_eib_CurrentMonth'];
				}

				if(@isset($requestParameters['application_num_bank__currentMonth']))
				{
					$app_noCurrent = @$requestParameters['application_num_bank__currentMonth'];
				}

				if(isset($requestParameters['teambank_currentMonth']))
				{
					$team = @$requestParameters['teambank_currentMonth'];
				}

				if(isset($requestParameters['senameinternalbank_currentMonth']))
				{
					$sename = @$requestParameters['senameinternalbank_currentMonth'];
				}

				if(isset($requestParameters['actualsenameinternalbank_currentMonth']))
				{
					$actual_sename = @$requestParameters['actualsenameinternalbank_currentMonth'];
				}

				

				if(isset($requestParameters['form_status']))
				{
					$form_status = @$requestParameters['form_status'];
				}
				
				if(isset($requestParameters['emp_id']))
				{
					$emp_id = @$requestParameters['emp_id'];
				}

				if(isset($requestParameters['start_date_creation_Bank_currentMonth']))
				{
					$start_date_application = @$requestParameters['start_date_creation_Bank_currentMonth'];
				}
				if(isset($requestParameters['end_date_creation_Bank_currentMonth']))
				{
					$end_date_application = @$requestParameters['end_date_creation_Bank_currentMonth'];
				}
				if(isset($requestParameters['submission_type_bank_currentMonth']))
				{
					$submission_type_inner = @$requestParameters['submission_type_bank_currentMonth'];
				}
				$request->session()->put('master_cbd_search_internal','');
				$request->session()->put('app_no_EIB_bank_currentMonth',$app_noCurrent);
				$request->session()->put('form_status_CBD_internal',$form_status);
				$request->session()->put('team_ENBD_Bank_internal_currentMonth',$team);
				$request->session()->put('seName_ENBD_Bank_internal_currentMonth',$sename);
				$request->session()->put('actualseName_ENBD_Bank_internal_currentMonth',$actual_sename);
				$request->session()->put('emp_id_CBD_internal',$emp_id);
				$request->session()->put('start_date_creation_EIB_bank_currentMonth',$start_date_application);
				$request->session()->put('end_date_creation_EIB_bank_currentMonth',$end_date_application);
				$request->session()->put('channel_CBD_internal',$channel_cbd);
				$request->session()->put('status_AECB_CBD_internal',$status_AECB_cbd);
				$request->session()->put('card_type_EIB_internal_CurrentMonth',$card_type_eib);
				$request->session()->put('card_status_EIB_internal_CurrentMonth',$card_status_eib);
				$request->session()->put('application_status_EIB_internal_CurrentMonth',$application_status_eib);
				$request->session()->put('final_decision_EIB_internal_CurrentMonth',$final_decision_EIB);				
				$request->session()->put('submission_type_EIB_Bank_currentMonth',$submission_type_inner);
				return redirect("loadBankContentsBankSideMISCurrentMonth");
	}



	public function resetBankSideMISInnerFilterCurrentMonth(Request $request)
	{
			$request->session()->put('app_no_EIB_bank_currentMonth','');
				$request->session()->put('form_status_CBD_internal','');
				$request->session()->put('team_ENBD_Bank_internal_currentMonth','');
				$request->session()->put('seName_ENBD_Bank_internal_currentMonth','');
				$request->session()->put('actualseName_ENBD_Bank_internal_currentMonth','');
				$request->session()->put('emp_id_CBD_internal','');
				$request->session()->put('start_date_creation_EIB_bank_currentMonth','');
				$request->session()->put('end_date_creation_EIB_bank_currentMonth','');
				$request->session()->put('channel_CBD_internal','');
				$request->session()->put('status_AECB_CBD_internal','');
				$request->session()->put('card_type_EIB_internal_CurrentMonth','');
				$request->session()->put('card_status_EIB_internal_CurrentMonth','');
				$request->session()->put('application_status_EIB_internal_CurrentMonth','');
				$request->session()->put('final_decision_EIB_internal_CurrentMonth','');
				$request->session()->put('submission_type_EIB_Bank_currentMonth','');
				
				$request->session()->put('master_cbd_search_internal',2);
				return redirect("loadBankContentsBankSideMISCurrentMonth");
	}



















	public function importCSVEIB()
	{

		$file = public_path('uploads/formFiles/21_import_EIB.csv');
		// Open uploaded CSV file with read-only mode
            $csvFile = fopen($file, 'r');
            
            // Skip the first line
            fgetcsv($csvFile);
            
            // Parse data from CSV file line by line
			$count = 0;
            while(($line = fgetcsv($csvFile)) !== FALSE)
			{				
				// echo "<pre>";
				// print_r($line);
				// exit; 
				/*
				*check for existance
				*start code
				*/
					$appNo = $line[16];
					if($appNo != '')
					{
					$existanceCheck = EIBDepartmentFormEntry::where("application_no",$appNo)->first();
				/*
				*check for existance
				*start code
				*/
				/*
				*import data
				*/
						if($existanceCheck != '')
						{
							$entry_obj = EIBDepartmentFormEntry::find($existanceCheck->id);	
						}
						else
						{
							$entry_obj = new EIBDepartmentFormEntry();	
							//$entry_obj->cbd_marging_status = 1;			
						}
						/*
						*parent entry 
						*start code
						*/
						

						// $empName = $this->getEmployeeName(trim($line[12]));
						// $entry_obj->emp_id = trim($line[12]);
						// $entry_obj->emp_name = $empName;
						$entry_obj->actual_se_name = trim($line[20]);
						//$entry_obj->actual_se_emp_id = trim($line[21]);
						$entry_obj->emirates_id = trim($line[21]);
						

						$entry_obj->application_no = $line[16];
						$entry_obj->form_id = 4;
						$entry_obj->form_title = 'EIB Internal MIS';
						$entry_obj->customer_name = trim($line[2]);
						$entry_obj->customer_mobile = $line[4];
						$entry_obj->nationality = trim($line[3]);
						$entry_obj->remarks = $line[19];
						$entry_obj->card_type = $line[8];
						$entry_obj->salary = $line[7];
						$entry_obj->application_date = date("Y-m-d",strtotime($line[1]));
						$entry_obj->status = 1;
						$entry_obj->card_status = trim($line[9]);
						$entry_obj->se_code = trim($line[11]);
						//$entry_obj->se_name = $empName;
						$entry_obj->se_name = trim($line[12]);
						$entry_obj->tl_name = trim($line[13]);
						$entry_obj->designation = trim($line[6]);
						$entry_obj->employer_name = trim($line[5]);
						$entry_obj->occupation = trim($line[15]);
						$entry_obj->comapany_status = trim($line[10]);
						
						$entry_obj->save();
						
						if($existanceCheck != '')
						{
							$insertID = $existanceCheck->id;
						}
						else
						{
							$insertID = $entry_obj->id;		
						}
						/*
						*parent entry 
						*end code
						*/
						
						
						/*
						*child entry 
						*start code
						*/
						 if($existanceCheck != '')
							{
								$existAttrMod = EIBDepartmentFormChildEntry::where("parent_id",$insertID)->get();
								foreach($existAttrMod as $attr)
								{
									$attr->delete();
								}
							}
							
						
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'card_type_eib';
							$child_obj->attribute_value = $line[8];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'mobile_eib';
							$child_obj->attribute_value = $line[4];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'date_eib';
							$child_obj->attribute_value =  date("Y-m-d",strtotime($line[1]));
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'cust_name_eib';
							$child_obj->attribute_value = $line[2];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'nationality_eib';
							$child_obj->attribute_value = $line[3];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'designation_eib';
							$child_obj->attribute_value = $line[6];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'salary_eib';
							$child_obj->attribute_value = $line[7];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'employer_name_eib';
							$child_obj->attribute_value = $line[5];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'tl_name_eib';
							$child_obj->attribute_value = $line[13];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'occupation_eib';
							$child_obj->attribute_value = $line[15];
							$child_obj->status = 1;
							$child_obj->save();
						
						
						
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'app_id_eib';
							$child_obj->attribute_value = $line[16];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'bpm_id_eib';
							$child_obj->attribute_value = $line[17];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'se_name_eib';
							$child_obj->attribute_value = $line[12];;
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'actual_se_name_eib';
							$child_obj->attribute_value = $line[20];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'case_status_eib';
							$child_obj->attribute_value = $line[18];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'company_status_eib';
							$child_obj->attribute_value = $line[10];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'card_status_eib';
							$child_obj->attribute_value = $line[9];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'agency_eib';
							$child_obj->attribute_value = $line[14];
							$child_obj->status = 1;
							$child_obj->save();
							
							
							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'se_code_eib';
							$child_obj->attribute_value = $line[11];
							$child_obj->status = 1;
							$child_obj->save(); 

							$child_obj = new EIBDepartmentFormChildEntry();
							$child_obj->parent_id = $insertID;
							$child_obj->form_id = 4;
							$child_obj->attribute_code = 'remarks_eib';
							$child_obj->attribute_value = $line[19];
							$child_obj->status = 1;
							$child_obj->save(); 
						
						/*
						*child entry 
						*end code
						*/
				
				/*
				*import data
				*/
               /*  echo "done";
			exit;  */
					}
            }
            echo "done";
			exit;
            // Close opened CSV file
            fclose($csvFile);

			/*
			Array
			(
				[0] => Sahir
				[1] => 17-Jul-2023
				[2] => 91784
				[3] => Suhel
				[4] => Muhammad Umair Nawaz Muhammad Nawaz
				[5] => 0567255705
				[6] => 7000
				[7] => 5460417
				[8] => N1354950
				[9] => Booked
				[10] => Booked
				[11] => CB
			)
			*/

	}


	public function setPaginationValueEIBCardPagination(Request $request)
	{
		$offset = $request->offset;
		$request->session()->put('paginationValue',$offset);
		return redirect('loadBankContentEIBCard');
	}



	public function exportDocReportinternalMisEIBCards(Request $request)
	{
		$requestPost = $request->input();
		 $parameters = $request->input(); 
			/* echo "<pre>";
			print_r($parameters);
			exit; */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Internal_MIS_EIB_Cards_'.date("d-m-Y").'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:U1');
			$sheet->setCellValue('A1', 'Internal MIS EIB Cards - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;
			$sheet->setCellValue('A'.$indexCounter, strtoupper('Id'))->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('B'.$indexCounter, strtoupper('Application No'))->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('C'.$indexCounter, strtoupper('Creation Date'))->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('D'.$indexCounter, strtoupper('Customer Name'))->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('E'.$indexCounter, strtoupper('Nationality'))->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('F'.$indexCounter, strtoupper('Mobile'))->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('G'.$indexCounter, strtoupper('Employer Name'))->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('H'.$indexCounter, strtoupper('Designation'))->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('I'.$indexCounter, strtoupper('Salary'))->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('J'.$indexCounter, strtoupper('Card Type'))->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('K'.$indexCounter, strtoupper('Card Status'))->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('L'.$indexCounter, strtoupper('Company Status'))->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('M'.$indexCounter, strtoupper('SE Code'))->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('N'.$indexCounter, strtoupper('SE Name'))->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('O'.$indexCounter, strtoupper('TL Name'))->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('P'.$indexCounter, strtoupper('Occupation'))->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('Q'.$indexCounter, strtoupper('BPM Id'))->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('R'.$indexCounter, strtoupper('Remarks'))->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('S'.$indexCounter, strtoupper('Final Decision'))->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			$sheet->setCellValue('T'.$indexCounter, strtoupper('Application Status'))->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			$sheet->setCellValue('U'.$indexCounter, strtoupper('Actual SE Name'))->getStyle('V'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
			
			
			$sn = 1;
			foreach ($selectedId as $sid) {
				
				$mis =  EIBDepartmentFormEntry::where("id",$sid)->first();
				
				/*  $Employee_details_data = DepartmentFormController::getEmployeeDetails($mis->emp_id);	 */

			/* $emp_name= @$Employee_details_data->first_name.(@$Employee_details_data->middle_name ? " ".@$Employee_details_data->middle_name:'').(@$Employee_details_data->last_name?" ".@$Employee_details_data->last_name:'');

			$submission_date = @$mis->submission_date;
			if($submission_date!='0000-00-00')
			{
				$submission_date = date('d-m-Y',strtotime($mis->submission_date));
			}
			else
			{
				$submission_date='';
			}
			
				if($mis->status == 1)
                  $status='Activated';
                else
					$status='Deactivated'; */
               	
				 $indexCounter++; 

				/*
				*agent name
				*/			
				$application_num = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","app_id_eib")->first();
					$appnum = '';
					if($application_num != '')
					{
						$appnum = $application_num->attribute_value;
					}
				/*
				*agent name
				*/
				
				/*
				*agent code
				*/			
				$creation_date = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","date_eib")->first();
					$creationDate = '';
					if($creation_date != '')
					{
						$creationDate = $creation_date->attribute_value;
					}
				/*
				*agent code
				*/
				
				
				/*
				*employeer name
				*/			
				$custmer_name = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","cust_name_eib")->first();
					$custmerName = '';
					if($custmer_name != '')
					{
						$custmerName = $custmer_name->attribute_value;
					}
				/*
				*employeer name
				*/
				
				/*
				*Card Type
				*/			
				$national = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","nationality_eib")->first();
					$nationaltyVal = '';
					if($national != '')
					{
						$nationaltyVal = $national->attribute_value;
					}
				/*
				*Card Type
				*/
				
				/*
				*bureau_score
				*/			
				$mobile = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","mobile_eib")->first();
					$mobileNum = '';
					if($mobile != '')
					{
						$mobileNum = $mobile->attribute_value;
					}
				/*
				*bureau_score
				*/
				
				/*
				*app_score
				*/			
				$employer = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","employer_name_eib")->first();
					$employerName = '';
					if($employer != '')
					{
						$employerName = $employer->attribute_value;
					}
				/*
				*app_score
				*/
				
				/*
				*MOB
				*/			
				$desig = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","designation_eib")->first();
					$desigName = '';
					if($desig != '')
					{
						$desigName = $desig->attribute_value;
					}
				/*
				*MOB
				*/
				
				/*
				*declared_salary_cbd
				*/			
				$salary = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","salary_eib")->first();
					$declared_salary = '';
					if($salary != '')
					{
						$declared_salary = $salary->attribute_value;
					}
				/*
				*declared_salary_cbd
				*/
				
				/*
				*eligible_income_cbd
				*/			
				$cardType = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","card_type_eib")->first();
					$cardTypeVal = '';
					if($cardType != '')
					{
						$cardTypeVal = $cardType->attribute_value;
					}
				/*
				*eligible_income_cbd
				*/
				
				/*
				*eligible_income_cbd
				*/			
				$card_statusMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","card_status_eib")->first();
					$card_status = '';
					if($card_statusMod != '')
					{
						$card_status = $card_statusMod->attribute_value;
					}
				/*
				*eligible_income_cbd
				*/
				
				/*
				*eligible_income_cbd
				*/			
				$company_statusMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","company_status_eib")->first();
					$company_status = '';
					if($company_statusMod != '')
					{
						$company_status = $company_statusMod->attribute_value;
					}
				/*
				*eligible_income_cbd
				*/
				
				/*
				*status_cbd
				*/			
				$se_codeMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","se_code_eib")->first();
					$se_code = '';
					if($se_codeMod != '')
					{
						$se_code = $se_codeMod->attribute_value;
					}
				/*
				*status_cbd
				*/
				
				/*
				*CBD_remark
				*/			
				$se_nameMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","se_name_eib")->first();
					$se_name = '';
					if($se_nameMod != '')
					{
						$se_name = $se_nameMod->attribute_value;
					}
				/*
				*CBD_remark
				*/
				
				/*
				*status_cbd
				*/			
				$tl_nameMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","tl_name_eib")->first();
					$tl_name = '';
					if($tl_nameMod != '')
					{
						$tl_name = $tl_nameMod->attribute_value;
					}


					$occuMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","occupation_eib")->first();
					$occupation = '';
					if($occuMod != '')
					{
						$occupation = $occuMod->attribute_value;
					}


					$bpmMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","bpm_id_eib")->first();
					$bpm_id = '';
					if($bpmMod != '')
					{
						$bpm_id = $bpmMod->attribute_value;
					}


					$remarkMod = 	EIBDepartmentFormChildEntry::where("parent_id",$mis->id)->where("attribute_code","remarks_eib")->first();
					$remark = '';
					if($remarkMod != '')
					{
						$remark = $remarkMod->attribute_value;
					}
				/*
				*status_cbd
				*/
				$sheet->setCellValue('A'.$indexCounter, $mis->id)->getStyle('A'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('B'.$indexCounter, $appnum)->getStyle('B'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('C'.$indexCounter, $creationDate)->getStyle('C'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('D'.$indexCounter, $custmerName)->getStyle('D'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('E'.$indexCounter, $nationaltyVal)->getStyle('E'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('F'.$indexCounter, $mobileNum)->getStyle('F'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('G'.$indexCounter, $employerName)->getStyle('G'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('H'.$indexCounter, $desigName)->getStyle('H'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('I'.$indexCounter, $declared_salary)->getStyle('I'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('J'.$indexCounter, $cardTypeVal)->getStyle('J'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('K'.$indexCounter, $card_status)->getStyle('K'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('L'.$indexCounter, $company_status)->getStyle('L'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('M'.$indexCounter, $se_code)->getStyle('M'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('N'.$indexCounter, $se_name)->getStyle('N'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('O'.$indexCounter, $tl_name)->getStyle('O'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('P'.$indexCounter, $occupation)->getStyle('P'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('Q'.$indexCounter, $bpm_id)->getStyle('Q'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('R'.$indexCounter, $remark)->getStyle('R'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('S'.$indexCounter, $mis->final_decision)->getStyle('S'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('T'.$indexCounter, $mis->application_status)->getStyle('T'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				$sheet->setCellValue('U'.$indexCounter, $mis->actual_se_name)->getStyle('U'.$indexCounter)->getAlignment()->setHorizontal('center')->setVertical('top');
				
				
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


	public function updateApplicationStatusMISDatafromBankMIS(Request $request)
	{
		$eibParentForm = EIBDepartmentFormEntry::where("matched_status",2)->get();
		//$eibParentForm = EIBDepartmentFormEntry::where("id",450)->get();


		if(count($eibParentForm) == 0) 
		{
			return response()->json(['success'=>'No Any Records found to Update.']);
		}
		else
		{
			foreach($eibParentForm as $eib)
			{
				$final_status = $eib->final_decision;
				if($final_status==NULL || $final_status=='')
				{
					$eibBankMod = EibBankMis::where('application_no',$eib->application_no)->where("update_status",2)->where("matched_status",1)->first();

					$file_values = array();
					$file_values['final_decision'] = $eibBankMod->final_decision;
					$file_values['application_status'] = $eibBankMod->application_status;			
					DB::table('eib_department_form_parent_entry')->where('application_no', $eibBankMod->application_no)->update($file_values);
				}
			}
			return response()->json(['success'=>'Updated Successfully.']);
		}
		
		

	}



// 3-6-2024 End new code


	public function preMasterPayoutCronEIBDataOLD(Request $request)
	{
		// $toDate = date("Y-m-d");
		// $fromDate = date("Y").'-'.date("m").'-'.'01';
		$dateC = date("Y-m-d");
		
		$fromDate = '2024-06-01';
		$toDate = '2024-06-30';

		$salesTime = date("n-Y", strtotime($fromDate));	
		$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";

		//$empData = SCBDepartmentFormParentEntry::whereRaw($whereraw)->groupby('emp_id')->get();

		$empData = EIBDepartmentFormEntry::whereRaw($whereraw)->get();

		//$empData = SCBDepartmentFormParentEntry::whereRaw($whereraw)->where('emp_id','102027')->get();
		$successarr = array();
		$failedarr = array();
		$tlName =array();

		//echo "<pre>";
		//print_r($empData);

		foreach($empData as $emp)
		{
			$cardData = EibBankMis::where('application_no',$emp->application_no)->where('application_status','COMPLETED')->first();

			if($cardData)
			{
				$successarr[$emp->emp_id][] = $cardData->id;
				$tlName[] = $cardData->TL;
			}
			else
			{
				$failedarr[$emp->emp_id][] = $emp->emp_id;
				$tlName[] = $emp->TL;
			}
		}
		// print_r($successarr);		
		// print_r($tlName);
		// exit;

		foreach($successarr as $key=>$value)
		{
			$agentid = $key;
			$tl = end($tlName); 
			$acheivedCount = count($value); 

			$empattributesMod = Employee_attribute::where('emp_id',$agentid)->where('attribute_code','DOJ')->first();
			
			if($empattributesMod)
			{
				$empdoj= date("Y-m-d", strtotime($empattributesMod->attribute_values));
			}
			else
			{
				$empdoj= NULL;
			}

			if(!empty($empattributesMod)){				 
			$createdAT = $empattributesMod->attribute_values;
			}else{
			$createdAT=0;
			}
			
			$doj = str_replace("/","-",$createdAT);				
			$daysInterval = abs(strtotime($dateC)-strtotime($doj))/ (60 * 60 * 24);	
			
			$empInfo = Employee_details::where('emp_id',$agentid)->first();

			if($empInfo)
			{
				$empDept = $empInfo->dept_id;
				$empName = $empInfo->emp_name;
			}
			else
			{
				$empDept = NULL;
				$empName = NULL;
			}


			DB::table('master_payout_pre')->insert(
				array('agent_id' => $agentid, 'tc' => $acheivedCount, 'vintage' => $daysInterval, 'agent_product' => 'Card', 'agent_name' => $empName, 'sales_time' => $salesTime, 'dept_id' => $empDept, 'bank_name' => 'SCB', 'doj' => $empdoj, 'TL' => $tl)
			);			
		}

		foreach($failedarr as $key=>$value)
		{
			$agentid = $key;
			$tl = end($tlName); 
			$acheivedCount = 0; 

			$empattributesMod = Employee_attribute::where('emp_id',$agentid)->where('attribute_code','DOJ')->first();
			
			if($empattributesMod)
			{
				$empdoj= date("Y-m-d", strtotime($empattributesMod->attribute_values));
			}
			else
			{
				$empdoj= NULL;
			}

			if(!empty($empattributesMod)){				 
			$createdAT = $empattributesMod->attribute_values;
			}else{
			$createdAT=0;
			}
			
			$doj = str_replace("/","-",$createdAT);				
			$daysInterval = abs(strtotime($dateC)-strtotime($doj))/ (60 * 60 * 24);	
			
			$empInfo = Employee_details::where('emp_id',$agentid)->first();

			if($empInfo)
			{
				$empDept = $empInfo->dept_id;
				$empName = $empInfo->emp_name;
			}
			else
			{
				$empDept = NULL;
				$empName = NULL;
			}


			DB::table('master_payout_pre')->insert(
				array('agent_id' => $agentid, 'tc' => $acheivedCount, 'vintage' => $daysInterval, 'agent_product' => 'Card', 'agent_name' => $empName, 'sales_time' => $salesTime, 'dept_id' => $empDept, 'bank_name' => 'SCB', 'doj' => $empdoj, 'TL' => $tl)
			);			
		}

		
		
		//print_r($activeemp);

		return response()->json(['success'=>'SCB Data Added Successfully for the month of '.$salesTime.'.']);
	}


	public function preMasterPayoutCronEIBData(Request $request)
	{
		// $toDate = date("Y-m-d");
		// $fromDate = date("Y").'-'.date("m").'-'.'01';

		$dateC = date("Y-m-d");
		
		$fromDate = '2024-06-01';
		$toDate = '2024-06-30';

		$salesTime = date("n-Y", strtotime($fromDate));	
		$whereraw = "application_date >= '".$fromDate."' and application_date <= '".$toDate."'";

		

		//$empData = EIBDepartmentFormEntry::whereRaw($whereraw)->groupby('emp_id')->get();

		$empData = EIBDepartmentFormEntry::whereRaw($whereraw)->get();

		//$empData = SCBDepartmentFormParentEntry::whereRaw($whereraw)->where('emp_id','102027')->get();
		$successarr = array();
		$failedarr = array();
		

		// echo "<pre>";
		// print_r($empData);
		// exit;

		foreach($empData as $emp)
		{
			$cardData = EibBankMis::where('application_no',$emp->application_no)->where('application_status','COMPLETED')->first();

			if($cardData)
			{
				$successarr[$emp->emp_id][] = $cardData->tl_name;
				//$successtlName[] = $cardData->TL;
			}
			else
			{
				$failedarr[$emp->emp_id][] = $emp->tl_name;
				//$failedtlName[] = $emp->team;
			}
		}

		// print_r($successarr);
		// print_r($failedarr);
		// exit;
		

		foreach($successarr as $key=>$value)
		{
			$agentid = $key;
			$tl = end($value); 
			$acheivedCount = count($value); 

			$empattributesMod = Employee_attribute::where('emp_id',$agentid)->where('attribute_code','DOJ')->first();
			
			if($empattributesMod)
			{
				$empdoj= date("Y-m-d", strtotime($empattributesMod->attribute_values));
			}
			else
			{
				$empdoj= NULL;
			}

			if(!empty($empattributesMod)){				 
			$createdAT = $empattributesMod->attribute_values;
			}else{
			$createdAT=0;
			}
			
			$doj = str_replace("/","-",$createdAT);				
			$daysInterval = abs(strtotime($dateC)-strtotime($doj))/ (60 * 60 * 24);	
			
			$empInfo = Employee_details::where('emp_id',$agentid)->first();

			if($empInfo)
			{
				$empDept = $empInfo->dept_id;
				$empName = $empInfo->emp_name;
			}
			else
			{
				$empDept = NULL;
				$empName = NULL;
			}


			DB::table('master_payout_pre')->insert(
				array('agent_id' => $agentid, 'tc' => $acheivedCount, 'vintage' => $daysInterval, 'agent_product' => 'Card', 'agent_name' => $empName, 'sales_time' => $salesTime, 'dept_id' => $empDept, 'bank_name' => 'SCB', 'doj' => $empdoj, 'TL' => $tl)
			);			
		}

		foreach($failedarr as $key=>$value)
		{
			$agentid = $key;
			$tl = end($value); 
			$acheivedCount = 0; 
			
			$agentData = DB::table('master_payout_pre')->where('agent_id', $agentid)->where('sales_time', $salesTime)->first();
			
			if(!$agentData)
			{
				$empattributesMod = Employee_attribute::where('emp_id',$agentid)->where('attribute_code','DOJ')->first();
			
				if($empattributesMod)
				{
					$empdoj= date("Y-m-d", strtotime($empattributesMod->attribute_values));
				}
				else
				{
					$empdoj= NULL;
				}
	
				if(!empty($empattributesMod)){				 
				$createdAT = $empattributesMod->attribute_values;
				}else{
				$createdAT=0;
				}
				
				$doj = str_replace("/","-",$createdAT);				
				$daysInterval = abs(strtotime($dateC)-strtotime($doj))/ (60 * 60 * 24);	
				
				$empInfo = Employee_details::where('emp_id',$agentid)->first();
	
				if($empInfo)
				{
					$empDept = $empInfo->dept_id;
					$empName = $empInfo->emp_name;
				}
				else
				{
					$empDept = NULL;
					$empName = NULL;
				}
	
	
				DB::table('master_payout_pre')->insert(
					array('agent_id' => $agentid, 'tc' => $acheivedCount, 'vintage' => $daysInterval, 'agent_product' => 'Card', 'agent_name' => $empName, 'sales_time' => $salesTime, 'dept_id' => $empDept, 'bank_name' => 'SCB', 'doj' => $empdoj, 'TL' => $tl)
				);	
			}
			
			
			
			

					
		}

		
		
		//print_r($activeemp);

		return response()->json(['success'=>'SCB Data Added Successfully for the month of '.$salesTime.'.']);
	}




	public function updateLinkedData()
	{
		$eibParentData = EIBDepartmentFormEntry::where('matched_status',2)->whereNull('bankDataStatus')->get();

		if (count($eibParentData) === 0) 
		{
			return response()->json(['success'=>'Nothing to Update In Parent Table.']);
		}
		else
		{
			foreach($eibParentData as $eib)
			{
				$eibBankData = EibBankMis::where('application_no',$eib->application_no)->orderBy("id","DESC")->first();

				$eibParentDataRequest = EIBDepartmentFormEntry::where('application_no',$eibBankData->application_no)
				->where('matched_status',2)
				->update(['bidayaPid' => $eibBankData->BIDAYA_pid,'workflowStatus' => $eibBankData->application_workflow_status, 'dsaName' => $eibBankData->dsa_name,'stpNstp' => $eibBankData->stp_nstp_flag,'aleNonale' => $eibBankData->ale_nonale,'dmsOutcome' => $eibBankData->dms_outcome,'ch' => $eibBankData->ch,'code' => $eibBankData->code,'dms_decision' => $eibBankData->dms_decision,'bankDataStatus' => 1]);
			}
			return response()->json(['success'=>'Data Updated Successfully for EIB Parent Table.']);
		}
	}




	

}
