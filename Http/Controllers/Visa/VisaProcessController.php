<?php

namespace App\Http\Controllers\Visa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee\Employee_details;
use App\Models\Visa\visaType;
use App\Models\Visa\VisaStage;
use App\Models\Visa\Visaprocess;
class VisaProcessController extends Controller
{
    public function selectEmployee()
	{
		$empDetails = Employee_details::where("status",1)->orderBy("id","DESC")->get();
		return view("VisaProcess/selectEmployee",compact('empDetails'));
	}
	public function visaProcess($id=NULL)
	{
		$empDetail = Employee_details::where("id",$id)->first();
		$result = array();
		$result['empDetail'] = $empDetail;
		/*
		*getting Visa Type List
		*/
		$visaTypeList = visaType::where("status",1)->orderBy("id","DESC")->get();
		$result['visaTypeList'] = $visaTypeList;
		/*
		*getting Visa Type List
		*/
		/*
		*checking Visa Process Status for employee
		*Start Code
		*/
		$visaProcessLists = Visaprocess::where("employee_id",$id)->orderBy('id','DESC')->get();
		
		/*
		*checking Visa Process Status for employee
		*End Code
		*/
		return view("VisaProcess/visaProcess_step1",compact('result'),compact('visaProcessLists'));
	}
	public function setVisaStage($visaTypeId = NULL)
	{
		$visaStageLists = VisaStage::where('visa_type',$visaTypeId)->get();
		return view("VisaProcess/setVisaStage",compact('visaStageLists'));
	}
	
	public function empVisaPost(Request $req)
	{
		$requestData = $req->input();
		$visaprocessObj = new Visaprocess();
		$visaprocessObj->employee_id = $requestData['employee_id'];
		$visaprocessObj->visa_type = $requestData['visa_type'];
		$visaprocessObj->visa_stage = $requestData['visa_stage'];
		$visaprocessObj->comment = $requestData['comment'];
		$visaprocessObj->stage_staus = $requestData['stage_staus'];
		if($requestData['stage_staus'] == 2 || $requestData['stage_staus'] == 1)
		{
			$visaprocessObj->cancel_status = 1;
		}
		else
		{
			$visaprocessObj->cancel_status = 2;
		}
		$visaprocessObj->save();
		$req->session()->flash('message','Visa Process setup for Employee.');
        return back();
	}
	public function visaProcessPost(Request $req)
	{
		$requestData = $req->input();
		
		$visaprocessObj = Visaprocess::find($requestData['visa_process_id']);
		$visaprocessObj->cost = $requestData['cost'];
		$visaprocessObj->final_comment = $requestData['final_comment'];
		$visaprocessObj->stage_staus = $requestData['stage_staus'];
		if($requestData['stage_staus'] == 2)
		{
			$visaprocessObj->cancel_status = 1;
		}
		else
		{
			$visaprocessObj->cancel_status = 2;
		}
		$visaprocessObj->closing_date = date("Y-m-d");
		$visaprocessObj->save();
		$req->session()->flash('message','Visa Process setup for Employee.');
        return back();
	}
	
}