<?php

namespace App\Http\Controllers\EmpTarget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Consultancy\ConsultancyModel;
use App\Models\Consultancy\Resumedetails;

use App\Models\Entry\Employee;
use App\Models\Recruiter\Designation;
use App\Models\Recruiter\Recruiter;
use App\Models\Recruiter\ResumeHistroy;
use App\Models\Onboarding\DocumentCollectionDetailsValues;
use App\Models\Onboarding\DocumentCollectionAttributes;
use Crypt;
use Session;
use Illuminate\Support\Facades\File; 

class EmpTargetController extends Controller
{
	
		public function manageEmpTarget()
		{
			return view("EmpTarget/manageEmpTarget");
		}
		
		public function updateAttributeTypeOnboard()
		{
			
			$detailsAttr = DocumentCollectionDetailsValues::get();
			foreach($detailsAttr as $attr)
			{
				$attribute_code = $attr->attribute_code;
				$attriMod = DocumentCollectionAttributes::where("id",$attribute_code)->first();
				if($attriMod != '')
				{
				$updateAttributeType = DocumentCollectionDetailsValues::find($attr->id);
				$updateAttributeType->attribute_type = $attriMod->attrbute_type_id;
				$updateAttributeType->save();
				}
			}
			echo "done";
			exit;
		}
	
}
