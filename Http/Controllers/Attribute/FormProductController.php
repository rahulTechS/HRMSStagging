<?php

namespace App\Http\Controllers\Attribute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\FormProduct;

use Session;

class FormProductController extends Controller
{
   
    public function formProduct()
    {
		$formProductDetails = FormProduct::where("status",1)->orwhere("status",2)->orderBy("product","ASC")->get();        
        return view("Attribute/formProduct",compact('formProductDetails'));
    }

	public function editFormProduct($id=NULL)
    {
      $formProductDetails =   FormProduct::where("id",$id)->first();	  
      return view("Attribute/editFormProduct",compact('formProductDetails'));
    }

	public function editFormProductPost(Request $req)
    {
        $divisonObj = FormProduct::find($req->id);       
        $divisonObj->product = $req->product;
		$divisonObj->status = $req->status;
        $divisonObj->save();
        $req->session()->flash('message','Record Updated Successfully.');
        return redirect('formProduct');
    }
	
	
	public function deleteFormProduct(Request $req)
    {
		$ravi_data_obj = FormProduct::find($req->id);
		$ravi_data_obj->status = 3;
        $ravi_data_obj->save();
        $req->session()->flash('message','Record deleted Successfully.');
        return redirect('formProduct');
    }

	public function addFormProduct()
    {         
        return view('Attribute/addFormProduct');
    }

    public function addFormProductPost(Request $req)
    {
            $divison_obj = new FormProduct();
            $divison_obj->product = $req->input('product');
            $divison_obj->save();
            $req->session()->flash('message','Record added Successfully.');
            return redirect('formProduct');
    }

    
}
