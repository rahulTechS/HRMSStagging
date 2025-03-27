<?php

namespace App\Http\Controllers\Attribute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\FormSection;

use Session;

class FormSectionController extends Controller
{
   
    public function formSection()
    {
		$formSectionDetails = FormSection::where("status",1)->orwhere("status",2)->orderBy("section","ASC")->get();        
        return view("Attribute/formSection",compact('formSectionDetails'));
    }

	public function editFormSection($id=NULL)
    {
      $formSectionDetails =   FormSection::where("id",$id)->first();	  
      return view("Attribute/editFormSection",compact('formSectionDetails'));
    }

	public function editFormSectionPost(Request $req)
    {
        $divisonObj = FormSection::find($req->id);       
        $divisonObj->section = $req->section;
		$divisonObj->status = $req->status;
        $divisonObj->save();
        $req->session()->flash('message','Record Updated Successfully.');
        return redirect('formSection');
    }
	
	
	public function deleteFormSection(Request $req)
    {
		$ravi_data_obj = FormSection::find($req->id);
		$ravi_data_obj->status = 3;
        $ravi_data_obj->save();
        $req->session()->flash('message','Record deleted Successfully.');
        return redirect('formSection');
    }

	public function addFormSection()
    {         
        return view('Attribute/addFormSection');
    }

    public function addFormSectionPost(Request $req)
    {
            $divison_obj = new FormSection();
            $divison_obj->section = $req->input('section');
            $divison_obj->save();
            $req->session()->flash('message','Record added Successfully.');
            return redirect('formSection');
    }

    
}
