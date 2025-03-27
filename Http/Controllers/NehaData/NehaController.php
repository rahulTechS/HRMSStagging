<?php

namespace App\Http\Controllers\nehadata;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\nehadata\neha_model;


use Session;

class NehaController extends Controller
{
  public function showData()
    {
		$emp_details = neha_model::where("status",1)->orderBy("id","DESC")->get();        
        return view("NehaView/ShowData",compact('emp_details'));
    }
	public function addempData()
    {         
        return view('NehaView/addempData');
    }
	public function viewempData($id=NULL)
    {         
        $empDetails =   neha_model::where("id",$id)->first();
	 // echo '<pre>';
	// print_r($empDetails);
      return view("NehaView/ViewDetails",compact('empDetails'));
    }


    
}
