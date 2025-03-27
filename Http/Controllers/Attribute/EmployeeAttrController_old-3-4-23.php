<?php

namespace App\Http\Controllers\Attribute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  App\Models\Attribute\Attributes;
use  App\Models\Attribute\AttributeType;
use App\Models\Company\Department;
class EmployeeAttrController extends Controller
{
    
        public function empAttributeAdd()
        {
            $attributeTypeDetails = AttributeType::orderBy('attribute_type_id','DESC')->get();

            $departments = Department::where("status",1)->orderBy('id','DESC')->get();
            $conditionalAttributes = Attributes::where(["parent_attribute"=>0,"attrbute_type_id"=>3,"status"=>1])->get();
            $result = array();
            $result['departments'] = $departments;
            $result['conditionalAttributes'] = $conditionalAttributes;
            return view("Attribute/empattradd",compact('attributeTypeDetails'),compact('result'));
        }

        public function addEmployeeAttr(Request $attrReq)
        {
            $attrObj = new Attributes();
            $attrObj->attribute_name = $attrReq->input('attribute_name');
            $attrObj->attribute_code = $attrReq->input('attribute_code');
            $attrObj->tab_name = $attrReq->input('tab_name');
            $attrObj->attrbute_type_id = $attrReq->input('attrbute_type_id');
            $attrTypeId = $attrReq->input('attrbute_type_id');
            if($attrTypeId == 3)
            {
               
               $attrOpts =  $attrReq->input('opt');
               $attrObj->opt_option = json_encode($attrOpts);
            }
            $attrObj->attribute_requirement = $attrReq->input('attribute_requirement');
            $attrObj->department_id = $attrReq->input('department_id');
            $attrObj->attribute_set = 'Employee';
            $attrObj->status = $attrReq->input('status');
            $attrObj->sort_order = $attrReq->input('sort_order');
            $attrObj->onboarding_status = $attrReq->input('onboarding_status');
            $attrObj->conditional_attribute = $attrReq->input('conditional_attribute');
            $cA =  $attrReq->input('conditional_attribute');;
            if($cA == 1)
            {
                $attrObj->parent_attribute = $attrReq->input('parent_attribute');
				$attrObj->parent_attr_opt = json_encode($attrReq->input('parent_attr_opt'));
				
            }
            
            $attrObj->save();
            $attrReq->session()->flash('message','Attribute Saved Successfully.');
            return redirect('empAttributeList');
            
        }

        public function empAttributeList()
        {
			
            $attributeTypeDetails = Attributes::where("attributes.status",1)->orWhere("attributes.status",2)->orderBy('attribute_id','DESC')->select("attribute_type.attribute_type_name","attributes.*")->join("attribute_type","attribute_type.attribute_type_id","=","attributes.attrbute_type_id")->get();
            return view("Attribute/empattributelist",compact('attributeTypeDetails'));
        }

        public function editEmpAttribute($attributeId = NULL)
        {
                

                $attributeDetail = Attributes::where('attribute_id',$attributeId)->first();
                $attributeTypeDetails = AttributeType::orderBy('attribute_type_id','DESC')->get();
                $departments = Department::where("status",1)->orderBy('id','DESC')->get();
				$conditionalAttributes = Attributes::where(["parent_attribute"=>0,"attrbute_type_id"=>3])->get();
                $result = array();
                
                $result['departments'] = $departments;
                $result['attributeTypeDetails'] = $attributeTypeDetails;
                $result['optarr'] = json_decode($attributeDetail->opt_option);
				$result['conditionalAttributes'] = $conditionalAttributes;
                return view("Attribute/editempattribute",compact('result'),compact('attributeDetail'));
        }
		public function deleteEmpAttribute(Request $attrReq)
		{
			
			 $attrObj =  Attributes::find($attrReq->attribute_id);
			 $attrObj->status = 3;
			 $attrObj->save();
             $attrReq->session()->flash('message','Attribute Deleted Successfully.');
             return redirect('empAttributeList');
		}
        public function updateEmployeeAttr(Request $attrReq)
        {
            $attrObj =  Attributes::find($attrReq->attribute_id);
            $attrObj->attribute_name = $attrReq->input('attribute_name');
            $attrObj->attribute_code = $attrReq->input('attribute_code');
			 $attrObj->tab_name = $attrReq->input('tab_name');
            $attrObj->attrbute_type_id = $attrReq->input('attrbute_type_id');
            $attrTypeId = $attrReq->input('attrbute_type_id');
            if($attrTypeId == 3)
            {
               $attrOpts =  $attrReq->input('opt');
               $attrObj->opt_option = json_encode($attrOpts);
            }
            else
            {
                $attrObj->opt_option = '';
            }
            $attrObj->attribute_requirement = $attrReq->input('attribute_requirement');
            $attrObj->department_id = $attrReq->input('department_id');
            $attrObj->attribute_set = 'Employee';
            $attrObj->status = $attrReq->input('status');
            $attrObj->sort_order = $attrReq->input('sort_order');
			 $attrObj->onboarding_status = $attrReq->input('onboarding_status');
			$attrObj->conditional_attribute = $attrReq->input('conditional_attribute');
            $cA =  $attrReq->input('conditional_attribute');;
            if($cA == 1)
            {
                $attrObj->parent_attribute = $attrReq->input('parent_attribute');
				$attrObj->parent_attr_opt = json_encode($attrReq->input('parent_attr_opt'));
				
            }
			else
			{
				
				$attrObj->parent_attribute = 0;
				$attrObj->parent_attr_opt = '';
			}
            
            $attrObj->save();
            $attrReq->session()->flash('message','Attribute Updated Successfully.');
            return redirect('empAttributeList');
        }
		
		public function parentopts($attribute_id = NULL)
		{
			$attrDatas =  Attributes::find($attribute_id);
			$optValues = $attrDatas->opt_option;
			$optValues = json_decode($optValues);
			$optArr = array();
			foreach($optValues as $_opt)
			{
				$optArr[$_opt] = $_opt;
			}
			
			return view("Attribute/parentopts",compact('optArr'));
		}
		
		public function parentoptsselected($parent_attribute_id = NULL,$attribute_id = NULL)
		{
			$attrDatas =  Attributes::find($parent_attribute_id);
			$optValues = $attrDatas->opt_option;
			$optValues = json_decode($optValues);
			$optArr = array();
			foreach($optValues as $_opt)
			{
				$optArr[$_opt] = $_opt;
			}
			$result = array();
			$result['allOptions'] = $optArr;
			$selectedAttr = Attributes::find($attribute_id);
			$parentArrOpts =  json_decode($selectedAttr->parent_attr_opt);
			$optArrSelected = array();
			foreach($parentArrOpts as $_optS)
			{
				$optArrSelected[] = $_optS;
			}
			$result['optArrSelected'] = $optArrSelected;
			return view("Attribute/parentoptsselected",compact('result'));
		}
		
		

}
