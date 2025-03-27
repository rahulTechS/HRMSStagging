<?php

namespace App\Http\Controllers\PerformanceFlagRules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PerformanceFlagRules\FlagRules;
use App\Models\PerformanceFlagRules\FlagTypes;
use App\Models\MIS\BankDetailsUAE;
use App\Models\Company\Department;
use App\Models\SEPayout\WorkTimeRange;


class FlagRuleController extends Controller
{
    public function index()
    {
        $flagRulesdata = FlagRules::orderBy('id','ASC')->get();
        $flagTypesdata = FlagTypes::where('status',1)->orderBy('id','ASC')->get();
       // $banks = BankDetailsUAE::where('status',1)->orderBy('id','ASC')->get();

        $banks = Department::where('status',1)->whereIn('id', [8,9,36,43,46,47,49,52])
        ->orderBy('id','ASC')->get();

        $ranges = WorkTimeRange::orderBy('id','ASC')->get();


       // return $banks;




        


       // return $warningReasonsData;

        return view("PerformanceFlagRules/flagRuleIndex",compact('flagRulesdata','flagTypesdata','banks','ranges'));
    }

    public function createFlagRule(Request $request)
    {
        //return $request->all();






    	$validator = Validator::make($request->all(), [

            'bank_name' => 'required',

            'salary' => 'required',

            'cardpoint' => 'required',
            'acheived' => 'required',
           // 'acheived_percentage' => 'required',
            'flag_type' => 'required',


        
        ]);


if(($validator->fails()))
{
    return response()->json(['error'=>$validator->errors()]);

}
else
{


    $flagRuleRequest = FlagRules::where('bank_name',$request->bank_name)
    ->where('salary',$request->salary)
    ->where('target',$request->target)
    ->where('range_id',$request->rangeid)
    ->orderBy('id','DESC')->first();

    if($flagRuleRequest)
    {
        return response()->json(['exist'=>'Please create another rule because based on this info Rule is already created..']);

    }
    else{

        
        $flagRuleRequest = new FlagRules();
        $flagRuleRequest->bank_name = $request->bank_name;
        $flagRuleRequest->salary = $request->salary;
        $flagRuleRequest->target = $request->target;
        $flagRuleRequest->acheived = $request->acheived;
        //$flagRuleRequest->acheived_percentage_from = $request->acheived_percentage_from;
        //$flagRuleRequest->acheived_percentage_to = $request->acheived_percentage_to;
        $flagRuleRequest->card_points = $request->cardpoint;


        $flagRuleRequest->flag_type = $request->flag_type;
        $flagRuleRequest->status = $request->status;
        $flagRuleRequest->range_id = $request->rangeid;
        $flagRuleRequest->save();
    
    
        return response()->json(['success'=>'Saved Successfully.']);
    }





}
        





        

        














        

    }


    public function listingFlagRulesData()
    {
        $flagRulesData = FlagRules::orderBy('id','ASC')->get();

       // return $warningReasonsData;

        return view("PerformanceFlagRules/listingtblData",compact('flagRulesData'));
    }

    public static function getFlagType($flagid)
    {
        $flagColor = FlagTypes::where('status',1)->where('id',$flagid)->orderBy('id','ASC')->first();
        return $flagColor->name;

    }

    public function delete($id)
    {
        //return $id;
        $flagRules = FlagRules::find($id)->delete();

        $flagRulesdata = FlagRules::orderBy('id','ASC')->get();
        $flagTypesdata = FlagTypes::where('status',1)->orderBy('id','ASC')->get();
        //$banks = BankDetailsUAE::where('status',1)->orderBy('id','ASC')->get();

        
        $banks = Department::where('status',1)->whereIn('id', [8,9,36,43,46,47,49,52])
        ->orderBy('id','ASC')->get();



        return view("PerformanceFlagRules/flagRuleIndex",compact('flagRulesdata','flagTypesdata','banks'));
    }

    public static function getBankData($bankid)
    {
        $bankData = Department::where('status',1)->where('id',$bankid)->orderBy('id','ASC')->first();
        return $bankData->department_name;

    }


    public function getFlagRuleContentData(Request $request)
    {
        $rowid = $request->rowid;
        $flagRulesData = FlagRules::where('id',$rowid)->orderBy('id','DESC')->first();

        $flagTypesdata = FlagTypes::where('status',1)->orderBy('id','ASC')->get();
        //$banks = BankDetailsUAE::where('status',1)->orderBy('id','ASC')->get();

        
        $banks = Department::where('status',1)->whereIn('id', [8,9,36,43,46,47,49,52])
        ->orderBy('id','ASC')->get();

        $ranges = WorkTimeRange::orderBy('id','ASC')->get();


        return view("PerformanceFlagRules/flagRuleContent",compact('flagRulesData','flagTypesdata','banks','ranges'));


    }


    public function updateFlagRuleData(Request $request)
    {
        $flagRulesData = FlagRules::where('id',$request->rowid)->orderBy('id','DESC')->first();





        // $flagRuleRequest = FlagRules::where('bank_name',$request->bank_name)
        // ->where('salary',$request->salary)
        // ->where('target',$request->target)
        // ->where('range_id',$request->rangeid)
        // ->where('id',$request->rowid)
        // ->orderBy('id','DESC')->first();


        // if($flagRuleRequest)
        // {
        //     return response()->json(['exist'=>'Please create another rule because based on this info Rule is already created..']);

        // }
        // else{

        // }







        $flagRulesData->bank_name = $request->bank_name;
        $flagRulesData->salary = $request->salary;
        $flagRulesData->target = $request->target;
        $flagRulesData->acheived = $request->acheived;
        $flagRulesData->acheived_percentage = $request->acheived_percentage;
        $flagRulesData->flag_type = $request->flag_type;
        $flagRulesData->status = $request->status;
        
        $flagRulesData->range_id = $request->rangeid;

        $flagRulesData->save();


        $response['code'] = '200';
		$response['message'] = "Updated Successfully.";
		echo json_encode($response);
    }

}
