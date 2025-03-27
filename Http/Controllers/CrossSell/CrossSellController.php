<?php
namespace App\Http\Controllers\CrossSell;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CrossSell\CrossSellScenarios;
use App\Models\CrossSell\CrossSellScenariosAllocation;
use App\Models\CrossSell\CustomersMasterChild;
use App\Models\CrossSell\TlList;
use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Attribute\CdaDeviationDetails;
use App\Models\Attribute\ENBDDepartmentFormEntry;
use App\Models\MIS\CurrentActivity;
use App\Models\Attribute\EIBDepartmentFormEntry;
use App\Models\MIS\MainMisReportTab;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

use Session;
ini_set("max_execution_time", 0);
class CrossSellController extends Controller
{
   
    public function cross_sell_data()
    {
		$CrossSellDetails = CrossSellScenarios::orderBy("id","DESC")->get();        
        return view("CrossSell/CrossSell",compact('CrossSellDetails'));
    }

	public function cross_sell_data_tl(Request $request)
    {
		$user_id = $request->session()->get('EmployeeId');
		$emp_id = '100902';
		$CrossSellDetails = CrossSellScenariosAllocation::where("emp_id",$emp_id)->where("allocate_to",'TL')->orderBy("id","DESC")->get();        
        return view("CrossSell/CrossSellTL",compact('CrossSellDetails'));
    }

	

	public function loadCrossSellScenarios(Request $request)
	{

		$user_id = $request->session()->get('EmployeeId');
		$username = $request->session()->get('username');
		
		$paginationValue = 100;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}	
		$paginationValue = 100;
		$whereRaw = " active_status='1' ";	
		
		
		$datasCrossSellScenariosCount = DB::table('cross_sell_scenarios')->whereRaw($whereRaw)->orderby('id','DESC')->get()->count();
		
		$datasCrossSellScenarios = DB::table('cross_sell_scenarios')->whereRaw($whereRaw)->orderby('id','DESC')->paginate($paginationValue);

		
		$Employee_details = Employee_details::orderby('first_name','ASC')->get();
		$tl_list = TlList::orderby('tl_name','ASC')->get();

		$message = '';
		if($request->session()->get('message') != '')
		{
			$message = $request->session()->get('message');
			$request->session()->put('message','');
		}

		$form_status_list = DepartmentFormEntry::where("form_status","!=",'')->groupBy('form_status')
		->selectRaw('DISTINCT form_status')
		->get();

		$cda_deviation_list = CdaDeviationDetails::where("cda_deviation","!=",'')->groupBy('cda_deviation')
		->selectRaw('DISTINCT cda_deviation')
		->get();

		$current_activityData = CurrentActivity::select('name')->where("status",1)->get()->unique('name');
		$formidarr = array(6,7);
		$productsData = ENBDDepartmentFormEntry::select('product_name')->whereIn("form_id",$formidarr)->get()->unique('product_name');

		$ENBD_application_status = MainMisReportTab::select('application_status')->get()->unique('application_status');
		$ENBD_card_type = MainMisReportTab::select('card_name')->where("card_name","!=",'')->get()->unique('card_name');

		$card_type_cbd = EIBDepartmentFormEntry::select('card_type')->where("form_id",4)->get()->unique('card_type');
		$card_status = EIBDepartmentFormEntry::select('card_status')->where("form_id",4)->get()->unique('card_status');
		$application_status = EIBDepartmentFormEntry::select('application_status')->where("form_id",4)->get()->unique('application_status');
		$final_decision = EIBDepartmentFormEntry::select('final_decision')->where("form_id",4)->get()->unique('final_decision');
		
		$seller_id = array();
		 return view("CrossSell/loadCrossSellScenarios",compact('datasCrossSellScenarios','searchValues','seller_id','datasCrossSellScenariosCount','Employee_details','user_id','username','message','cda_deviation_list','form_status_list','tl_list','current_activityData','productsData','card_type_cbd','card_status','application_status','final_decision','ENBD_application_status','ENBD_card_type'));
	}

	public function loadCrossSellLeadTL(Request $request)
	{

		$user_id = $request->session()->get('EmployeeId');
		$username = $request->session()->get('username');
		@$getUserInfo = DB::table('users')->where('id', $user_id)->first();
		$emp_id = @$getUserInfo->employee_id;
		//$emp_id = '101250';
		
		$paginationValue = 100;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}	
		$paginationValue = 100;
		$whereRaw = " emp_id='".$emp_id."' ";	
		
		
		$datasCrossSellScenariosCount = DB::table('cross_sell_scenarios_allocation')->whereRaw($whereRaw)->orderby('id','DESC')->get()->count();
		
		$datasCrossSellScenarios = DB::table('cross_sell_scenarios_allocation')->whereRaw($whereRaw)->orderby('id','DESC')->paginate($paginationValue);

		
		$Employee_details = Employee_details::orderby('first_name','ASC')->get();
		$tl_list = TlList::orderby('tl_name','ASC')->get();

		$message = '';
		if($request->session()->get('message') != '')
		{
			$message = $request->session()->get('message');
			$request->session()->put('message','');
		}

		$form_status_list = DepartmentFormEntry::where("form_status","!=",'')->groupBy('form_status')
		->selectRaw('DISTINCT form_status')
		->get();

		$cda_deviation_list = CdaDeviationDetails::where("cda_deviation","!=",'')->groupBy('cda_deviation')
		->selectRaw('DISTINCT cda_deviation')
		->get();

		
		@$getEmpInfo = DB::table('employee_details')->where('emp_id', $emp_id)->first();
		$tl_id = @$getEmpInfo->id;

		$agent_list = Employee_details::where('tl_id', $tl_id)->where('offline_status','1')->orderby('first_name','ASC')->get();
		
		$seller_id = array();
		 return view("CrossSell/loadCrossSellLeadTL",compact('datasCrossSellScenarios','searchValues','seller_id','datasCrossSellScenariosCount','Employee_details','user_id','username','message','cda_deviation_list','form_status_list','tl_list','agent_list'));
	}

	public function loadCrossSellScenariosData(Request $request)
	{

		$user_id = $request->session()->get('EmployeeId');
		$username = $request->session()->get('username');
		
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}	
		//$paginationValue = 20;
		$active_status = '';
		$origin_bank = '';

		$scenario_id = @$request->session()->get('scenario_id');

		if($scenario_id=='')
		{
			$whereRaw = " id='0' AND (lead_status IS NULL OR lead_status='3') ";	
		}
		else
		{
			$whereRaw = " id!='' AND (lead_status IS NULL OR lead_status='3') ";	
			@$CrossSellScenarioData = DB::table('cross_sell_scenarios')->where('id', $scenario_id)->first();
			$active_status = @$CrossSellScenarioData->active_status;
			$origin_bank = @$CrossSellScenarioData->origin_bank;

			if($CrossSellScenarioData->origin_bank !='')
			{
				/*if($CrossSellScenarioData->origin_bank == 'ENBD')
				{
					$origin_bank_ENBD_CARD = 'ENBD-CardTab';
					$whereRaw .= " AND bank_name LIKE '%".$origin_bank_ENBD_CARD."%'";	
				}
				else
				{
					$whereRaw .= " AND bank_name LIKE '%".$CrossSellScenarioData->origin_bank."%'";	
				}*/
				$whereRaw .= " AND bank_name LIKE '%".$CrossSellScenarioData->origin_bank."%'";	
				$request->session()->put('origin_bank',$CrossSellScenarioData->origin_bank);
				$request->session()->put('origin_bank_product',$CrossSellScenarioData->origin_bank_product);
			}

			if($CrossSellScenarioData->submission_date_start !='')
			{
				$submission_date_start = date('Y-m-d',strtotime($CrossSellScenarioData->submission_date_start));
				$submission_date_end = date('Y-m-d');
				if($CrossSellScenarioData->submission_date_end !='')
				{
					$submission_date_end = date('Y-m-d',strtotime($CrossSellScenarioData->submission_date_end));
				}
				$whereRaw .= " AND submission_date >='".$submission_date_start."' AND submission_date<='".$submission_date_end."'";	
				$request->session()->put('submission_date_start',date('d-m-Y',strtotime($submission_date_start)));
				$request->session()->put('submission_date_end',date('d-m-Y',strtotime($submission_date_end)));
			}
			else
			{
				$request->session()->put('submission_date_start','');
				$request->session()->put('submission_date_end','');
			}

			if($CrossSellScenarioData->customer_salary !='')
			{
				$customer_salary_condition = $CrossSellScenarioData->customer_salary_condition;
				$customer_salary = $customer_salary_condition." ".$CrossSellScenarioData->customer_salary;

				$whereRaw .= " AND cm_salary ".$customer_salary;
				$request->session()->put('customer_salary_condition',$CrossSellScenarioData->customer_salary_condition);
				$request->session()->put('customer_salary',$CrossSellScenarioData->customer_salary);
			}
			else
			{
				$request->session()->put('customer_salary_condition','');
				$request->session()->put('customer_salary','');
			}

			if($CrossSellScenarioData->bureau_score !='')
			{
				$bureau_score_condition = $CrossSellScenarioData->bureau_score_condition;
				$bureau_score = $bureau_score_condition." ".$CrossSellScenarioData->bureau_score;

				$whereRaw .= " AND cm_aecb_score ".$bureau_score;	
				$request->session()->put('bureau_score_condition',$CrossSellScenarioData->bureau_score_condition);
				$request->session()->put('bureau_score',$CrossSellScenarioData->bureau_score);
			}
			else
			{
				$request->session()->put('bureau_score_condition','');
				$request->session()->put('bureau_score','');
			}

			if($CrossSellScenarioData->bureau_segmentation !='')
			{
				$bureau_segmentation_exp = explode(",",$CrossSellScenarioData->bureau_segmentation);
				$whereRaw .= " AND (";	
				foreach($bureau_segmentation_exp as $bureau_segmentation_exp_val)
				{
					$whereRaw .= " bureau_aecb_status LIKE '%".$bureau_segmentation_exp_val."%' OR";	
				}
				$whereRaw = substr($whereRaw,0,-2);
				
				$whereRaw .= ")";	
				$request->session()->put('bureau_segmentation',$CrossSellScenarioData->bureau_segmentation);
			}
			else
			{
				$request->session()->put('bureau_segmentation','');
			}

			if($CrossSellScenarioData->form_status !='')
			{
				$form_status_exp = explode(",",$CrossSellScenarioData->form_status);
				$whereRaw .= " AND (";	
				foreach($form_status_exp as $form_status_exp_val)
				{
					$whereRaw .= " submission_status LIKE '%".$form_status_exp_val."%' OR";	
				}				
				$whereRaw = substr($whereRaw,0,-2);
				
				$whereRaw .= ")";
				$request->session()->put('form_status',$CrossSellScenarioData->form_status);
			}
			else
			{
				$request->session()->put('form_status','');
			}

			if($CrossSellScenarioData->all_cda_deviation !='')
			{
				$all_cda_deviation_exp = explode(",",$CrossSellScenarioData->all_cda_deviation);
				$whereRaw .= " AND (";	
				foreach($all_cda_deviation_exp as $all_cda_deviation_exp_val)
				{
					$whereRaw .= " all_cda_deviation LIKE '%".$all_cda_deviation_exp_val."%' OR";	
				}
				$whereRaw = substr($whereRaw,0,-2);
				
				$whereRaw .= ")";				
				$request->session()->put('all_cda_deviation',$CrossSellScenarioData->all_cda_deviation);
			}
			else
			{
				$request->session()->put('all_cda_deviation','');
			}

			if($CrossSellScenarioData->current_activity_enbd !='')
			{
				$current_activity_enbd_exp = explode(",",$CrossSellScenarioData->current_activity_enbd);
				$whereRaw .= " AND (";	
				foreach($current_activity_enbd_exp as $current_activity_enbd_exp_val)
				{
					$whereRaw .= " current_activity_enbd LIKE '%".$current_activity_enbd_exp_val."%' OR";	
				}
				$whereRaw = substr($whereRaw,0,-2);
				
				$whereRaw .= ")";				
				$request->session()->put('current_activity_enbd',$CrossSellScenarioData->current_activity_enbd);
			}
			else
			{
				$request->session()->put('current_activity_enbd','');
			}

			if($CrossSellScenarioData->product_enbd !='')
			{
				$product_enbd_exp = explode(",",$CrossSellScenarioData->product_enbd);
				$whereRaw .= " AND (";	
				foreach($product_enbd_exp as $product_enbd_exp_val)
				{
					$whereRaw .= " product_enbd LIKE '%".$product_enbd_exp_val."%' OR";	
				}
				$whereRaw = substr($whereRaw,0,-2);
				
				$whereRaw .= ")";				
				$request->session()->put('product_enbd',$CrossSellScenarioData->product_enbd);
			}
			else
			{
				$request->session()->put('product_enbd','');
			}

			if($CrossSellScenarioData->card_type !='')
			{
				$card_type_exp = explode(",",$CrossSellScenarioData->card_type);
				$whereRaw .= " AND (";	
				foreach($card_type_exp as $card_type_exp_val)
				{
					$whereRaw .= " card_type LIKE '%".$card_type_exp_val."%' OR";	
				}
				$whereRaw = substr($whereRaw,0,-2);
				
				$whereRaw .= ")";				
				$request->session()->put('card_typeC',$CrossSellScenarioData->card_type);
			}
			else
			{
				$request->session()->put('card_typeC','');
			}
		}

		
		
		
		$datasCrossSellCount = DB::table('Customers_Master_child')->whereRaw($whereRaw)->orderby('submission_date','DESC')->get()->count();

		$datasCrossSellCountID = DB::table('Customers_Master_child')->whereRaw($whereRaw)->orderby('submission_date','DESC')->get(['id']);

		$tableID = array();
		foreach($datasCrossSellCountID as $datasCrossSellCountIDData)
		{
			$tableID[] = $datasCrossSellCountIDData->id;
		}
		$tableIDJSON = json_encode($tableID);

		$dbArray['total_count'] = $datasCrossSellCount;
		$dbArray['customer_ids'] = $tableIDJSON;
		$dbArray['sql_query'] = $whereRaw;

		DB::table('cross_sell_scenarios')->where('id', $scenario_id)->update($dbArray);
		
		$datasCrossSell = DB::table('Customers_Master_child')->whereRaw($whereRaw)->orderby('submission_date','DESC')->paginate($paginationValue);

		
		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		$message = '';
		if($request->session()->get('message') != '')
		{
			$message = $request->session()->get('message');
			$request->session()->put('message','');
		}

		$form_status_list = DepartmentFormEntry::where("form_status","!=",'')->groupBy('form_status')
		->selectRaw('DISTINCT form_status')
		->get();

		$cda_deviation_list = CdaDeviationDetails::where("cda_deviation","!=",'')->groupBy('cda_deviation')
		->selectRaw('DISTINCT cda_deviation')
		->get();
		
		$seller_id = array();
		 return view("CrossSell/loadCrossSellScenariosData",compact('datasCrossSell','searchValues','seller_id','datasCrossSellCount','Employee_details','user_id','username','message','form_status_list','cda_deviation_list','scenario_id','active_status','origin_bank'));
	}

	public function loadCrossSellScenariosDataTL(Request $request)
	{

		$user_id = $request->session()->get('EmployeeId');
		$username = $request->session()->get('username');
		$scenario_id = '';
		$origin_bank = '';
		
		$paginationValue = 20;
		$searchValues = array();
		if(@$request->session()->get('paginationValue') != '')
		{
			$paginationValue = $request->session()->get('paginationValue');
			$searchValues['paginationValue'] = $paginationValue;
		}	
		//$paginationValue = 20;
		$active_status = '';

		$allocation_id = @$request->session()->get('allocation_id');

		if($allocation_id=='')
		{
			$whereRaw = " id='0' ";	
		}
		else
		{
			$whereRaw = " id!='' ";	
			@$CrossSellScenarioData = DB::table('cross_sell_scenarios_allocation')->where('id', $allocation_id)->first();
			$customer_ids = @$CrossSellScenarioData->customer_ids;
			$origin_bank = @$CrossSellScenarioData->origin_bank;
			$scenario_id = @$CrossSellScenarioData->css_id;

			$whereRaw = " id IN (".$customer_ids.")";	
			
		}

		
		
		
		$datasCrossSellCount = DB::table('Customers_Master_child')->whereRaw($whereRaw)->orderby('submission_date','DESC')->get()->count();

		$datasCrossSellCountID = DB::table('Customers_Master_child')->whereRaw($whereRaw)->orderby('submission_date','DESC')->get(['id']);

		
		$datasCrossSell = DB::table('Customers_Master_child')->whereRaw($whereRaw)->orderby('submission_date','DESC')->paginate($paginationValue);

		
		$Employee_details = Employee_details::orderby('first_name','ASC')->get();

		$message = '';
		if($request->session()->get('message') != '')
		{
			$message = $request->session()->get('message');
			$request->session()->put('message','');
		}

		$form_status_list = DepartmentFormEntry::where("form_status","!=",'')->groupBy('form_status')
		->selectRaw('DISTINCT form_status')
		->get();

		$cda_deviation_list = CdaDeviationDetails::where("cda_deviation","!=",'')->groupBy('cda_deviation')
		->selectRaw('DISTINCT cda_deviation')
		->get();
		
		$seller_id = array();
		 return view("CrossSell/loadCrossSellScenariosData",compact('datasCrossSell','searchValues','seller_id','datasCrossSellCount','Employee_details','user_id','username','message','form_status_list','cda_deviation_list','scenario_id','active_status','origin_bank'));
	}

	
	
	
	
	public function setPaginationValueCrossSellScenariosData(Request $request)
	{
		$offSetValueIndex = $request->offSetValueIndex;
		$request->session()->put('paginationValue',$offSetValueIndex);
		return redirect("loadCrossSellScenariosData");
	}

	public function setPaginationValueCrossSellScenariosDataTL(Request $request)
	{
		$offSetValueIndex = $request->offSetValueIndex;
		$request->session()->put('paginationValue',$offSetValueIndex);
		return redirect("loadCrossSellScenariosDataTL");
	}

	
	
	
	public function addNewCrossSellScenario(Request $req)
	{	
		//$postParameters = $req->input();
		
		$origin_bank = $req->input('origin_bank');
		$origin_bank_product = $req->input('origin_bank_product');
		$beneficiary_bank = $req->input('beneficiary_bank');		
		$beneficiary_bank_product = $req->input('beneficiary_bank_product');
		$submission_date_start = $req->input('submission_date_start');
		$submission_date_end = $req->input('submission_date_end');
		$customer_salary_condition = $req->input('customer_salary_condition');
		$customer_salary = $req->input('customer_salary');
		$bureau_segmentation = array();

		if($origin_bank=='CBD')
		{
			$bureau_segmentation = $req->input('aecb_status');
		}
		else if($origin_bank=='Mashreq')
		{
			$bureau_segmentation = $req->input('bureau_segmentation');
		}
		
		$bureau_score_condition = $req->input('bureau_score_condition');
		$bureau_score = @$req->input('bureau_score');
		$form_status = @$req->input('form_status');
		$all_cda_deviation = @$req->input('all_cda_deviation');
		$customer_company = @$req->input('customer_company');
		$app_score_condition = @$req->input('app_score_condition');
		$app_score = @$req->input('app_score');
		$current_activity_enbd = @$req->input('current_activity_enbd');
		$product_enbd = @$req->input('product_enbd');
		$card_type = @$req->input('card_type');

		$form_status_val = '';
		$bureau_segmentation_val = '';
		$all_cda_deviation_val = '';
		$current_activity_enbd_val = '';
		$product_enbd_val = '';
		$card_type_val = '';

		
		
		$whereRaw = "origin_bank='".$origin_bank."' AND origin_bank_product='".$origin_bank_product."'";

		if($submission_date_start !='')
		{
			$submission_date_start = date('Y-m-d',strtotime($submission_date_start));
			
			if($submission_date_end !='')
			{
				$submission_date_end = date('Y-m-d',strtotime($submission_date_end));
			}
			else
			{
				$submission_date_end = date('Y-m-d');
			}
			$whereRaw .= " AND submission_date_start ='".$submission_date_start."' AND submission_date_end='".$submission_date_end."'";	
			
		}
		
		if($customer_salary_condition !='')
		{
			$whereRaw .= " AND customer_salary_condition ='".$customer_salary_condition."'";			
		}
		if($customer_salary !='')
		{
			$whereRaw .= " AND customer_salary ='".$customer_salary."'";			
		}
		if(@sizeof($bureau_segmentation)>0)
		{			
			foreach($bureau_segmentation as $bureau_segmentation_data)
			{
				$bureau_segmentation_val .= $bureau_segmentation_data.',';
			}
			$bureau_segmentation_val = substr($bureau_segmentation_val,0,-1);

			$whereRaw .= " AND bureau_segmentation ='".$bureau_segmentation_val."'";			
		}
		if($bureau_score_condition !='')
		{
			$whereRaw .= " AND bureau_score_condition ='".$bureau_score_condition."'";			
		}
		if($bureau_score !='')
		{
			$whereRaw .= " AND bureau_score ='".$bureau_score."'";			
		}
		if(@sizeof($form_status)>0)
		{			
			foreach($form_status as $form_status_data)
			{
				$form_status_val .= $form_status_data.',';
			}
			$form_status_val = substr($form_status_val,0,-1);
			$whereRaw .= " AND form_status ='".$form_status_val."'";			
		}
		if(@sizeof($all_cda_deviation)>0)
		{			
			foreach($all_cda_deviation as $all_cda_deviation_data)
			{
				$all_cda_deviation_val .= $all_cda_deviation_data.',';
			}
			$all_cda_deviation_val = substr($all_cda_deviation_val,0,-1);

			$whereRaw .= " AND all_cda_deviation ='".$all_cda_deviation_val."'";			
		}

		if(@sizeof($current_activity_enbd)>0)
		{			
			foreach($current_activity_enbd as $current_activity_data)
			{
				$current_activity_enbd_val .= $current_activity_data.',';
			}
			$current_activity_enbd_val = substr($current_activity_enbd_val,0,-1);
			$whereRaw .= " AND current_activity_enbd ='".$current_activity_enbd_val."'";			
		}

		if(@sizeof($product_enbd)>0)
		{			
			foreach($product_enbd as $product_enbd_data)
			{
				$product_enbd_val .= $product_enbd_data.',';
			}
			$product_enbd_val = substr($product_enbd_val,0,-1);
			$whereRaw .= " AND product_enbd ='".$product_enbd_val."'";			
		}

		if(@sizeof($card_type)>0)
		{			
			foreach($card_type as $card_type_data)
			{
				$card_type_val .= $card_type_data.',';
			}
			$card_type_val = substr($card_type_val,0,-1);
			$whereRaw .= " AND card_type ='".$card_type_val."'";			
		}

		if($app_score_condition !='')
		{
			$whereRaw .= " AND app_score_condition ='".$app_score_condition."'";			
		}
		if($app_score !='')
		{
			$whereRaw .= " AND app_score ='".$app_score."'";			
		}

		if($customer_company !='')
		{
			$whereRaw .= " AND customer_company ='".$customer_company."'";			
		}		
		
		
		$datasCrossSellCount = DB::table('cross_sell_scenarios')->whereRaw($whereRaw)->first();
		

		if($datasCrossSellCount)
		{
			$req->session()->put('scenario_id',$datasCrossSellCount->id);
			$req->session()->put('message','Scenario already exist.');
			
		}
		else
		{

			$values = array('origin_bank' => $origin_bank,
				'origin_bank_product' => $origin_bank_product,
				//'beneficiary_bank' => $beneficiary_bank,				
				//'beneficiary_bank_product' => $beneficiary_bank_product,
				'submission_date_start' => date('Y-m-d',strtotime($submission_date_start)),
				'submission_date_end' => date('Y-m-d',strtotime($submission_date_end)),
				'customer_salary_condition' => $customer_salary_condition,
				'customer_salary' => $customer_salary,
				'bureau_segmentation' => $bureau_segmentation_val,
				'bureau_score_condition' => $bureau_score_condition,
				'bureau_score' => $bureau_score,
				'app_score_condition' => $app_score_condition,
				'app_score' => $app_score,
				'form_status' => $form_status_val,
				'all_cda_deviation' => $all_cda_deviation_val,
				'current_activity_enbd' => $current_activity_enbd_val,
				'product_enbd' => $product_enbd_val,
				'card_type' => $card_type_val,
				'customer_company' => $customer_company,
				'created_at' => date('Y-m-d'));

			$scenario_id = DB::table('cross_sell_scenarios')->insertGetId($values);  
			
			$req->session()->put('scenario_id',$scenario_id);
		    //$req->session()->put('message','New Scenario added Successfully.');
			
		}

		$req->session()->put('origin_bank',$origin_bank);
		$req->session()->put('origin_bank_product',$origin_bank_product);
		$req->session()->put('beneficiary_bank',$beneficiary_bank);
		$req->session()->put('beneficiary_bank_product',$beneficiary_bank_product);
		$req->session()->put('submission_date_start',$submission_date_start);
		$req->session()->put('submission_date_end',$submission_date_end);
		$req->session()->put('customer_salary_condition',$customer_salary_condition);
		$req->session()->put('customer_salary',$customer_salary);
		$req->session()->put('bureau_segmentation',$bureau_segmentation);
		$req->session()->put('bureau_score_condition',$bureau_score_condition);
		$req->session()->put('bureau_score',$bureau_score);
		$req->session()->put('app_score_condition',$app_score_condition);
		$req->session()->put('app_score',$app_score);
		$req->session()->put('form_status',$form_status);
		$req->session()->put('all_cda_deviation',$all_cda_deviation);
		$req->session()->put('current_activity_enbd',$current_activity_enbd);
		$req->session()->put('product_enbd',$product_enbd);
		$req->session()->put('card_typeC',$card_type);
		$req->session()->put('customer_company',$customer_company);

		return redirect("loadCrossSellScenariosData");
	}

	public function deleteCrossSellScenarios($id=NULL,Request $request)
    {
		  $CrossSellData = DB::table('cross_sell_scenarios')->where('id', $id)->delete();	
		  
		$request->session()->put('origin_bank','');
		$request->session()->put('origin_bank_product','');
		$request->session()->put('beneficiary_bank','');
		$request->session()->put('beneficiary_bank_product','');
		$request->session()->put('submission_date_start','');
		$request->session()->put('submission_date_end','');
		$request->session()->put('customer_salary_condition','');
		$request->session()->put('customer_salary','');
		$request->session()->put('bureau_segmentation','');
		$request->session()->put('bureau_score_condition','');
		$request->session()->put('bureau_score','');
		$request->session()->put('app_score_condition','');
		$request->session()->put('app_score','');
		$request->session()->put('form_status','');
		$request->session()->put('all_cda_deviation','');
		$request->session()->put('current_activity_enbd','');
		$request->session()->put('product_enbd','');
		$request->session()->put('card_typeC','');
		$request->session()->put('customer_company','');

		$request->session()->put('scenario_id','');

		  return redirect("loadCrossSellScenarios");
    }

	public function viewCrossSellScenariosData($id=NULL,Request $request)
    {
		  $request->session()->put('scenario_id',$id);	  
		  return redirect("loadCrossSellScenariosData");
    }

	public function viewCrossSellScenariosDataTL($id=NULL,Request $request)
    {
		  $request->session()->put('allocation_id',$id);	  
		  return redirect("loadCrossSellScenariosDataTL");
    }

	public function updateStatusCrossSellScenarios($scenario_id=NULL,Request $request)
    {
		 //$postParameters = $request->input();	 
		 //$row_id = $postParameters['row_id'];
		 $whereRaw = "id='".$scenario_id."'";

		 //print_r($postParameters);exit;

		 $dbArray['active_status'] = 1;
		 DB::table('cross_sell_scenarios')->whereRaw($whereRaw)->update($dbArray);
		 echo 'success';
		 exit;
		 //$request->session()->flash('message','Scenario Saved Successfully.');
		 //return redirect("loadCrossSellScenariosData");
    }


	
	
	public function resetCrossSellDataInner(Request $request)
	{	
		$request->session()->put('origin_bank','');
		$request->session()->put('origin_bank_product','');
		$request->session()->put('beneficiary_bank','');
		$request->session()->put('beneficiary_bank_product','');
		$request->session()->put('submission_date_start','');
		$request->session()->put('submission_date_end','');
		$request->session()->put('customer_salary_condition','');
		$request->session()->put('customer_salary','');
		$request->session()->put('bureau_segmentation','');
		$request->session()->put('bureau_score_condition','');
		$request->session()->put('bureau_score','');
		$request->session()->put('app_score_condition','');
		$request->session()->put('app_score','');
		$request->session()->put('form_status','');
		$request->session()->put('all_cda_deviation','');
		$request->session()->put('current_activity_enbd','');
		$request->session()->put('product_enbd','');
		$request->session()->put('card_typeC','');
		$request->session()->put('customer_company','');

		$request->session()->put('scenario_id','');

		return redirect("loadCrossSellScenarios");
	}

	public function saveAllocateTL(Request $request)
	 {
		 $user_id = $request->session()->get('EmployeeId');
		 $postParameters = $request->input();	 
		 $css_id = $postParameters['css_id'];
		 $whereRaw = "id='".$css_id."'";

		 if(count($postParameters['allocated_number'])>0)
		 {
			 
			 $allocate_to = $postParameters['allocate_to'.$css_id];
			 $total_count = $postParameters['total_count'.$css_id];
			 $origin_bank = $postParameters['bank'];
			 $allocate_bank = $postParameters['allocate_bank'.$css_id];
			 $allocated_by = $user_id;

			 $dbArray['assigned_agent'] = $allocate_to;
			 DB::table('cross_sell_scenarios')->whereRaw($whereRaw)->update($dbArray);

			 $CrossSellScenariosInfo =   CrossSellScenarios::where("id",$css_id)->first(); 
			 $start_key_db = $CrossSellScenariosInfo->start_key;
			 $customer_Ids_array = json_decode($CrossSellScenariosInfo->customer_ids);
			 
			 $loop = 0;
			 foreach($postParameters['allocated_number'] as $k=>$v)
			 {
				 $emp_id = $k;
				 $allocated_count = $v;
				 
				if($allocated_count>0)
				 {
					if($loop==0)
					 {
						$startKey = $start_key_db;
					 }
					 else
					 {
						 $startKey = $startKeyNew;
					 }
					 $endKey = ($startKey+$allocated_count)-1;
					 $startKeyNew = $endKey+1;
					
					 $cust_id = '0,';
					 foreach($customer_Ids_array as $arKey=>$customer_Ids_data)
					 {
						 if($arKey>=$startKey && $arKey<=$endKey)
						 {
						  $cust_id .= $customer_Ids_data.',';
						 }
					 }
					 $cust_id = substr($cust_id,0,-1);
					
					 $values = array('css_id' => $css_id,
					'allocate_to' => $allocate_to,				
					'total_count' => $total_count,
					'origin_bank' => $origin_bank,
					'allocate_bank' => $allocate_bank,
					'allocated_by' => $allocated_by,
					'emp_id' => $emp_id,
					'allocated_count' => $allocated_count,
					'customer_ids' => $cust_id);

					$insert_id = DB::table('cross_sell_scenarios_allocation')->insertGetId($values); 
					$dbArrayNew['start_key'] = $startKeyNew;
					$dbArrayNew['beneficiary_bank'] = $allocate_bank;
					DB::table('cross_sell_scenarios')->whereRaw($whereRaw)->update($dbArrayNew);
					
					$dbArrayChild['lead_status'] = '5';
					$whereChildRaw = "id IN (".$cust_id.")";
					DB::table('Customers_Master_child')->whereRaw($whereChildRaw)->update($dbArrayChild);

					$loop++;
				 }
			 }

			 $request->session()->flash('message','TL Allocated Successfully.');
		 }	 
		 
		echo "done";
		exit;
	 }

	 public function saveAllocateAgent(Request $request)
	 {
		 $user_id = $request->session()->get('EmployeeId');
		 $postParameters = $request->input();	 
		 $css_id = $postParameters['css_id'];
		 $table_id = $postParameters['table_id'];
		 $whereRaw = "id='".$table_id."'";

		 if(count($postParameters['allocated_number'])>0)
		 {
			 
			 $allocate_to = 'Agent';
			 $total_count = $postParameters['total_count'.$table_id];
			 $origin_bank = $postParameters['bank'];
			 $allocate_bank = $postParameters['allocate_bank'];
			 $allocated_by = $user_id;

			 //$dbArray['assigned_agent'] = $allocate_to;
			 //DB::table('cross_sell_scenarios')->whereRaw($whereRaw)->update($dbArray);

			 $CrossSellScenariosInfo =   CrossSellScenariosAllocation::where("id",$table_id)->first(); 
			 $start_key_db = $CrossSellScenariosInfo->start_key;
			 $customer_Ids_array = explode(",",substr($CrossSellScenariosInfo->customer_ids,2));
			 
			 $loop = 0;
			 foreach($postParameters['allocated_number'] as $k=>$v)
			 {
				 $emp_id = $k;
				 $allocated_count = $v;
				 
				if($allocated_count>0)
				 {
					if($loop==0)
					 {
						$startKey = $start_key_db;
					 }
					 else
					 {
						 $startKey = $startKeyNew;
					 }
					 $endKey = ($startKey+$allocated_count)-1;
					 $startKeyNew = $endKey+1;
					
					 $cust_id = '0,';
					 foreach($customer_Ids_array as $arKey=>$customer_Ids_data)
					 {
						 if($arKey>=$startKey && $arKey<=$endKey)
						 {
						  $cust_id .= $customer_Ids_data.',';
						 }
					 }
					 $cust_id = substr($cust_id,0,-1);
					
					 $values = array('css_id' => $css_id,
					'parent_id' => $table_id,
					'allocate_to' => 'Agent',				
					'total_count' => $total_count,
					'origin_bank' => $origin_bank,
					'allocate_bank' => $allocate_bank,
					'allocated_by' => $allocated_by,
					'emp_id' => $emp_id,
					'allocated_count' => $allocated_count,
					'customer_ids' => $cust_id);

					$insert_id = DB::table('cross_sell_scenarios_allocation')->insertGetId($values); 
					$dbArrayNew['start_key'] = $startKeyNew;
					DB::table('cross_sell_scenarios_allocation')->whereRaw($whereRaw)->update($dbArrayNew);

					$loop++;
				 }
			 }

			 $request->session()->flash('message','Agents Allocated Successfully.');
		 }	 
		 
		echo "done";
		exit;
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
 public function exportCrossSellData(Request $request)
 {
	
			 $parameters = $request->input(); 
			/*  echo "<pre>";
			 print_r($parameters);
			 exit; */
	         $selectedId = $parameters['selectedIds'];
			 
	        $filename = 'Cross_Sell_Data_'.date("d-m-Y").rand(0,999).'.xlsx';
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:R1');
			$sheet->setCellValue('A1', 'Cross Sell Data - '.date("d/m/Y"))->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('top');
			$indexCounter = 2;

			$columnArray = array('bank_name','submission_date','application_id','submission_status','agent_name','ref_no','cm_name','cm_mobile','cm_salary','cm_employer','cm_aecb_score','bureau_aecb_status','form_status','app_score','all_cda_deviation','current_activity_enbd','product_enbd','card_type');

			//echo '<pre>';
			//print_r($columnArray);

			for($index=0;$index<=17;$index++)
			{
				$colm = $this->getColumnLetter($index).($indexCounter);
				
				$sheet->setCellValue($colm, strtoupper($columnArray[$index]))->getStyle($colm)->getAlignment()->setHorizontal('center')->setVertical('top');
				
			}

			
			
			
			$sn = 1;
			foreach ($selectedId as $sid) 
			{
				
				$mis =  CustomersMasterChild::where("id",$sid)->first();	
				$indexCounter++; 
				 
				for($index=0;$index<=17;$index++)
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
			
			
			for($col = 'A'; $col !== 'R'; $col++) 
			{
			   $sheet->getColumnDimension($col)->setAutoSize(true);
			}
			
			$spreadsheet->getActiveSheet()->getStyle('A1:R1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('cbddf7');
				
				for($index=1;$index<=$indexCounter;$index++)
				{
					  foreach (range('A','R') as $col) {
							$spreadsheet->getActiveSheet()->getStyle($col.$index)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('000000'));
					  }
				}
				$writer = new Xlsx($spreadsheet);
				$writer->save(public_path('uploads/exportEmp/'.$filename));	
				echo $filename;
				exit;
 }

public static function getAllocationInfo($css_id)
{
	return $CrossSellScenariosAllocation = CrossSellScenariosAllocation::where('css_id', $css_id)->get();
}

public static function getAllocationInfoAgent($parent_id)
{
	return $CrossSellScenariosAllocationAgent = CrossSellScenariosAllocation::where('parent_id', $parent_id)->get();
}

public static function getScenarioInfo($id)
{
	return $CrossSellScenarios = CrossSellScenarios::where('id', $id)->get();
}

public static function getEmployeeInfo($emp_id)
{
	return $Employee_details = Employee_details::where('emp_id', $emp_id)->first();
}

public static function getAgentsByTL($tl_id)
{
	return $Employee_details = Employee_details::where('tl_id', $tl_id)->where('offline_status','1')->get();
}

public static function getAfterSubmissionInfo($css_id)
{
	@$getCSSInfo = DB::table('cross_sell_scenarios')->where('id', $css_id)->first();
	$whereRawChild = @$getCSSInfo->sql_query;
	$fetch_date = @$getCSSInfo->created_at;

	$fetch_date = date('Y-m-d',strtotime($fetch_date));
	$dept_id = '';
	$datasChild = DB::table('Customers_Master_child')->whereRaw($whereRawChild)->orderby('submission_date','DESC')->get(['bank_id','cm_mobile']);
	$cm_mobile_str = "'0',";
	foreach($datasChild as $datasChildVal)
	{
		$dept_id = $datasChildVal->bank_id;
		$cm_mobile_str .= "'".$datasChildVal->cm_mobile."',";
	}
	$cm_mobile_str = substr($cm_mobile_str,0,-1);

	$returnCount = array();

	$whereRawSubmission = "id!=''";

		////////// Mashreq ///////
		$dept_id='36';
		$form_id = 1;
		$whereRawSubmission .= " AND form_id='".$form_id."' AND customer_mobile IN (".$cm_mobile_str.") AND submission_date > '".$fetch_date."'";

		$SubmissionData = DB::table('department_form_parent_entry')->whereRaw($whereRawSubmission)->orderby('submission_date','DESC')->get(['ref_no','customer_name','customer_mobile','submission_date']);

		$BookingData = DB::table('department_form_parent_entry')->whereRaw($whereRawSubmission." AND form_status='Booked'")->orderby('submission_date','DESC')->get(['ref_no','customer_name','customer_mobile','submission_date']);

		$returnCount[$dept_id]['SubmissionData'] = $SubmissionData;
		$returnCount[$dept_id]['BookingData'] = $BookingData;

		////////// Mashreq End ///////
	
		////////// CBD ///////
		$dept_id='49';
		$form_id = 2;
		$whereRawSubmission .= " AND form_id='".$form_id."' AND customer_mobile IN (".$cm_mobile_str.") AND application_date > '".$fetch_date."'";

		$SubmissionData = DB::table('department_form_parent_entry')->whereRaw($whereRawSubmission)->orderby('application_date','DESC')->get(['ref_no','customer_name','customer_mobile','application_date']);

		$BookingData = DB::table('department_form_parent_entry')->whereRaw($whereRawSubmission." AND form_status IN ('Missing(Approved)','Welcome Calling,Archive on Approval','Approved','Pending with Onboarder','Pending with COC')")->orderby('submission_date','DESC')->get(['ref_no','customer_name','customer_mobile','submission_date']);


		$returnCount[$dept_id]['SubmissionData'] = $SubmissionData;
		$returnCount[$dept_id]['BookingData'] = $BookingData;

		////////// CBD END ///////

		////////// EIB ///////
		$dept_id='52';
		$form_id = 4;
		$whereRawSubmission .= " AND form_id='".$form_id."' AND customer_mobile IN (".$cm_mobile_str.") AND application_date > '".$fetch_date."'";

		$SubmissionData = DB::table('eib_department_form_parent_entry')->whereRaw($whereRawSubmission)->orderby('application_date','DESC')->get(['application_no','customer_name','customer_mobile','application_date']);

		$BookingData = DB::table('eib_department_form_parent_entry')->whereRaw($whereRawSubmission." AND final_decision='Approve'")->orderby('application_date','DESC')->get(['application_no','customer_name','customer_mobile','application_date']);

		$returnCount[$dept_id]['SubmissionData'] = $SubmissionData;
		$returnCount[$dept_id]['BookingData'] = $BookingData;

		////////// EIB End ///////
	
	return $returnCount;
}



}
