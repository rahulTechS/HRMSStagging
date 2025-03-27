<?php

namespace App\Http\Controllers\DBcopy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee\Employee_details;
use Illuminate\Support\Facades\Response;


class IndexController extends Controller
{
    //

    function getEmployee_details(Request $request)
    {
        $employee_details = Employee_details::orderBy("id","desc")->get();
       // return $employee_details;

       if($employee_details)
       {
            return Response::json(array(
                'status'      =>  200,
                'data'   =>  $employee_details
            ), 200);
       }
       else{
        return Response::json(array(
            'status'      =>  500,
            'data'   =>  "No data found"
        ), 200);
       }

       


    }
}
