<?php

namespace App\Http\Controllers\EmployeePerformanceReview;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WarningLetter\WarningLetterRequest;
use App\Models\WarningLetter\WarningLetterReasons;
use Illuminate\Support\Facades\DB;

class WarningLetterReasonsController extends Controller
{
    
    
    
    public function index()
    {
        $warningReasonsData = WarningLetterReasons::orderBy('id','ASC')->get();

       // return $warningReasonsData;

        return view("EmployeePerformanceReview/WarningLetterReasons/reasonsListing",compact('warningReasonsData'));
    }
    public function listingReasonsData()
    {
        $warningReasonsData = WarningLetterReasons::orderBy('id','ASC')->get();

       // return $warningReasonsData;

        return view("EmployeePerformanceReview/WarningLetterReasons/listingtblData",compact('warningReasonsData'));
    }

    public function getReasonContentData(Request $request)
    {
        $rowid = $request->rowid;
        $warningReasonsData = WarningLetterReasons::where('id',$rowid)->orderBy('id','DESC')->first();
        return view("EmployeePerformanceReview/WarningLetterReasons/reasonContent",compact('warningReasonsData'));


    }

    public function updateWarningReasonData(Request $request)
    {
        //return $request->all();
        $warningReasonsData = WarningLetterReasons::where('id',$request->reason_id)->orderBy('id','DESC')->first();
        $warningReasonsData->name = $request->reason;
        $warningReasonsData->status = $request->reason_status;
        $warningReasonsData->save();

        $response['code'] = '200';
		$response['message'] = "Updated Successfully.";
		echo json_encode($response);




    }


    public function createWarningReason(Request $request)
    {

        $warningreasonRequest = new WarningLetterReasons();
        $warningreasonRequest->name = $request->reason;
        $warningreasonRequest->status =$request->reason_status;
        $warningreasonRequest->save();

        
        $response['code'] = '200';
		$response['message'] = "Saved Successfully.";
		echo json_encode($response);

    }

    
    public function delete($id)
    {
        //return $id;
        $flagRules = WarningLetterReasons::find($id)->delete();

        $warningReasonsData = WarningLetterReasons::orderBy('id','ASC')->get();

       // return $warningReasonsData;

        return view("EmployeePerformanceReview/WarningLetterReasons/reasonsListing",compact('warningReasonsData'));

    }
}
