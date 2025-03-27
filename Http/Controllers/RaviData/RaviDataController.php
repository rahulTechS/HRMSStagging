<?php

namespace App\Http\Controllers\RaviData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RaviData\ravi_data;

use Session;

class RaviDataController extends Controller
{
   
    public function showData()
    {
		$userDetails = ravi_data::where("status",1)->orderBy("id","DESC")->get();        
        return view("RaviData/showData",compact('userDetails'));
    }

	public function editRaviData($id=NULL)
    {
      $userDetails =   ravi_data::where("id",$id)->first();
	  //echo '<pre>';
	  //print_r($userDetails);
      return view("RaviData/editRaviData",compact('userDetails'));
    }

	public function editRaviDataPost(Request $req)
    {
        $divisonObj = ravi_data::find($req->id);       
        $divisonObj->name = $req->name;
        $divisonObj->email = $req->email;
		$divisonObj->age = $req->age;
		$divisonObj->address = $req->address;
        $divisonObj->save();
        $req->session()->flash('message','Record Updated Successfully.');
        return redirect('showData');
    }
	
	
	public function deleteRaviData(Request $req)
    {
		$ravi_data_obj = ravi_data::find($req->id);
		$ravi_data_obj->status = 3;
        $ravi_data_obj->save();
        $req->session()->flash('message','Record deleted Successfully.');
        return redirect('showData');
    }

	public function addRaviData()
    {         
        return view('RaviData/addRaviData');
    }

    public function addRaviDataPost(Request $req)
    {
            $divison_obj = new ravi_data();
            $divison_obj->name = $req->input('name');
            $divison_obj->email = $req->input('email');
            $divison_obj->age = $req->input('age');
			$divison_obj->address = $req->input('address');
            $divison_obj->save();
            $req->session()->flash('message','Record added Successfully.');
            return redirect('showData');
    }

    
}
