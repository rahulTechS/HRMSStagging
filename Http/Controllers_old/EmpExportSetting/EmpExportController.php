<?php
namespace App\Http\Controllers\EmpExportSetting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  App\Models\Attribute\Attributes;
use App\Models\Employee\Employee_attribute;
use App\Models\EmpExportSetting\EmpExportSetting;
use Session;


class EmpExportController extends Controller
{
	public function empExportSetting(Request $request)
	{			
		return view("EmpExportSetting/ExportAttributes");
		
	}
	public function setOffSetForAttribute(Request $request)
	{
		$offset = $request->offset;
		$request->session()->put('offset_visa_filter',$offset);
		 return  redirect('attributesTypeList');
	}
	public function attributesTypeList(Request $request){
		
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

		if($whereraw != '')
		{
			//echo "h1";exit;
			$attributeListing = EmpExportSetting::whereRaw($whereraw)->where("status",1)->orderBy("id","DESC")->paginate($paginationValue);
			$reportsCount = EmpExportSetting::whereRaw($whereraw)->whereIn("status",1)->get()->count();				
		}
		else
		{
			//echo "h2";exit;
			$attributeListing = EmpExportSetting::where("status",1)->orderBy("id","DESC")->paginate($paginationValue);
			$reportsCount = EmpExportSetting::where("status",1)->get()->count();					
		}
		
		$attributeListing->setPath(config('app.url/attributesTypeList'));
		
		
		
	
		return view("EmpExportSetting/ExportAttribuesList",compact('attributeListing','paginationValue','reportsCount'));
		}
	public function addFilterAttributes(){
		$array = array();

			$array[] = 'p_d';
			$array[] = 'b_d';
			$array[] = 'c_d';
			$array[] = 'v_d';
		$attributes=Attributes::where("department_id",'All')->where(["parent_attribute"=>0])->whereIn("tab_name",$array)->where("status",1)->orderBy("sort_order","ASC")->get();
		$allattributeArray=array();
		foreach($attributes as $_filter){
			$allattributeArray[$_filter->attribute_code]=$_filter->attribute_name;
		}
		return view("EmpExportSetting/addAttributesForm", compact('allattributeArray'));
	}
	public function deleteAttributes(Request $req)
	{
		$visaType_obj = EmpExportSetting::find($req->id);
       
        $visaType_obj->status = 2;
       
        $visaType_obj->save();
        $req->session()->flash('message','Attribute deleted Successfully.');
        //return redirect('VisaTypeList');
		$response['code'] = '200';
		   
		echo json_encode($response);
		   exit;
	}
	public function addFilterAttributesPost(Request $rq)
	{
		
		
		$attribute_code=$rq->input('attribute_code');
		//print_r($attribute_code);exit;
		foreach($attribute_code as $_attrbute){
		$data = EmpExportSetting::where("attribute_code",$_attrbute)->where('status',1)->first();
		if(!empty($data)){
			//echo "hello";
		}
		else{
			//echo "hello1";
		$attributename = Attributes::where('attribute_code',$_attrbute)->first();
		//exit;		 	
		$obj = new EmpExportSetting();
		$obj->attribute_code = $_attrbute;
		$obj->attribute_name = $attributename->attribute_name;
		$obj->status = 1;
		$obj->save();
		}
		}
		//exit;
		$rq->session()->flash('message','Filter Attribute Saved Successfully.');
		$response['code'] = '200';
		  $response['message'] = "Filter Attribute Saved Successfully.";
		   //$response['empid'] = $empIdPadding;
		   
		echo json_encode($response);
		   exit;
        //return redirect('visaType');
	}
	public static function getAttributeListValue($key){
		//echo $key;exit;
		$attribute=EmpExportSetting::where("attribute_code",$key)->where('status',1)->first();
		if(!empty($attribute)){
		return $attribute->attribute_code;//exit;
		}
		else{
			return '';
		}
	}
	
	
		
}
