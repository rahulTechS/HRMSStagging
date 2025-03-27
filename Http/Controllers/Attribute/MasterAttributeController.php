<?php

namespace App\Http\Controllers\Attribute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\MasterAttribute;
use App\Models\Attribute\AttributeType;
use App\Models\Common\MashreqLoginMIS;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Attribute\DepartmentFormChildEntry;
use App\Models\Common\MashreqBookingMIS;

use Session;

class MasterAttributeController extends Controller
{
   
    public function masterAttribute()
    {
		$masterAttributeDetails = MasterAttribute::where("status",1)->orwhere("status",2)->orderBy("attribute_name","ASC")->get();        
        return view("Attribute/masterAttribute",compact('masterAttributeDetails'));
    }

	public function editMasterAttribute($id=NULL)
    {
      $masterAttributeDetails =   MasterAttribute::where("id",$id)->first();
	  $attributeTypeDetails = AttributeType::orderBy('attribute_type_id','DESC')->get();
      return view("Attribute/editMasterAttribute",compact('masterAttributeDetails','attributeTypeDetails'));
    }

	public function editMasterAttributePost(Request $req)
    {
        $divisonObj = MasterAttribute::find($req->id);       
        $divisonObj->attribute_name = $req->attribute_name;
		$divisonObj->attribute_code = $req->attribute_code;
		$divisonObj->attribute_type = $req->attribute_type;
		if($req->option_values!='')
		{
			$divisonObj->option_values = @str_replace(', ',',',@$req->option_values);
		}
		$divisonObj->status = $req->status;
        $divisonObj->save();
        $req->session()->flash('message','Record Updated Successfully.');
        return redirect('masterAttribute');
    }
	
	
	public function deleteMasterAttribute(Request $req)
    {
		$ravi_data_obj = MasterAttribute::find($req->id);
		$ravi_data_obj->status = 3;
        $ravi_data_obj->save();
        $req->session()->flash('message','Record deleted Successfully.');
        return redirect('masterAttribute');
    }

	public function addMasterAttribute()
    {    
		$attributeTypeDetails = AttributeType::orderBy('attribute_type_id','DESC')->get();
        return view('Attribute/addMasterAttribute',compact('attributeTypeDetails'));
    }

    public function addMasterAttributePost(Request $req)
    {
            $divison_obj = new MasterAttribute();			
            $divison_obj->attribute_name = $req->input('attribute_name');
			$divison_obj->attribute_code = $req->input('attribute_code');
			$divison_obj->attribute_type = $req->input('attribute_type');
			if(@$req->option_values!='')
			{				
				$divison_obj->option_values = @str_replace(', ',',',@$req->option_values);
			}
            $divison_obj->save();
            $req->session()->flash('message','Record added Successfully.');
            return redirect('masterAttribute');
    }

	public static function getAttributeName($id=NULL)
    {
      return $attributeTypeDetails =   AttributeType::where("attribute_type_id",$id)->first();
    }

	public static function getLoginInfo($ref_no=NULL)
    {      
	  if($ref_no)
		{
			return $loginDetails =   MashreqLoginMIS::where("ref_no",$ref_no)->selectRaw('customer_name,cdafinalsalary')->first();
		}
		else
		{
			return false;
		}
    }
	public static function getInternalInfo($ref_no=NULL)
    {
		if($ref_no)
		{
			return $internalDetails =   DepartmentFormEntry::where("ref_no",$ref_no)->selectRaw('emp_id,emp_name,team,customer_name,customer_mobile')->first();
		}
		else
		{
			return false;
		}
    }

	public static function getLoginInfoByAppID($applicationid=NULL)
    {
		if($applicationid)
		{
			return $loginDetails =   MashreqLoginMIS::where("applicationid",$applicationid)->selectRaw('ref_no')->first();
		}
		else
		{
			return false;
		}
    }
	public static function getLoginInfoByCIF($cif=NULL)
    {
		if($cif)
		{
			return $loginDetails =   MashreqLoginMIS::where("cif",$cif)->selectRaw('ref_no,application_date,applicationid,bureau_score,bureau_segmentation,mrs_score')->orderBy('application_date','DESC')->get();
		}
		else
		{
			return false;
		}
    }

	public static function getBookingInfoByCIF($cif=NULL)
    {
		if($cif)
		{
			return $bookingDetails =   MashreqBookingMIS::where("cif_cis_number",$cif)->selectRaw('instanceid,ref_no,emp_id,emp_name,customer_mobile')->orderBy('dateofdisbursal','DESC')->get();
		}
		else
		{
			return false;
		}
    }

    
}
