<?php

namespace App\Http\Controllers\AgentTargetUpload;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute\DepartmentForm;
use App\Models\Attribute\DepartmentFormEntry;
use App\Models\Attribute\FormProduct;
use App\Models\Attribute\MasterAttribute;
use App\Models\Attribute\AttributeType;
use App\Models\Attribute\FormSection;
use App\Models\Company\Department;
use App\Models\Employee\Employee_details;
use App\Models\Common\MashreqLoginMIS;
use App\Models\Common\MashreqBankMIS;
use App\Models\Common\MashreqBookingMIS;
use App\Models\Common\MashreqMTDMIS;
use App\Http\Controllers\Attribute\DepartmentFormController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;
use App\Models\AgentTargetUpload\AgentTargetUpload;

use Session;
ini_set("max_execution_time", 0);
class ImportAgentTargetController extends Controller
{
   
    public function index(Request $request)
    {
        return view("AgentTargetUpload/index");
    }


    public function index2(Request $request)
    {
        return view("AgentTargetUpload/index2");
    }

    public function targetFileUploadPost(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [			
			'targetfile' => 'required|mimes:xlsx',
        ],
		[
			'targetfile.required'=> 'Please uplaod file.',
            'targetfile.mimes'=> 'The uploaded file must be a file of type: xlsx.',
		]);

		if(($validator->fails()))
		{
			return response()->json(['error'=>$validator->errors()]);
		}
		else
		{
            $file = $request->file('targetfile');
            $filename = date("Y-m-d_h-i-s").'_'.$file->getClientOriginalName();
            
            
            if(file_exists(public_path('agentTargetUploadDocs/'.$filename)))
            {
                unlink(public_path('agentTargetUploadDocs/'.$filename));
            }

            // File extension
            $extension = $file->getClientOriginalExtension();

            // File upload location
            $location = 'agentTargetUploadDocs';

            // Upload file
            $file->move(public_path('agentTargetUploadDocs/'), $filename);

            // File path
            $filepath = url('agentTargetUploadDocs/'.$filename);
            $inputFileName = '/srv/www/htdocs/hrm/public/agentTargetUploadDocs/'.$filename;
            $inputFileType = 'Xlsx';
            $spreadsheet = new Spreadsheet();


            /*  Create a new Reader of the type defined in $inputFileType  */
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            /*  Advise the Reader that we only want to load cell data  */
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($inputFileName);
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet = $spreadsheet->getActiveSheet()->toArray();
            // Get the highest row number and column letter referenced in the worksheet
            //$highestRow = $worksheet->getHighestRow()-1; // e.g. 10	

            // echo "<pre>";
            // print_r($spreadsheet);
            // exit;
            $user_id = $request->session()->get('EmployeeId');
            $response = array();

            //

            if (!empty($worksheet)) 
            {
                for ($i=1; $i<count($worksheet); $i++) 
                {
                    $emp = $worksheet[$i][0];
                    $product = $worksheet[$i][1];
                    $dept = $worksheet[$i][2];
                    $target = $worksheet[$i][3];
                    
                    $agentTargetData = new AgentTargetUpload();
                    $agentTargetData->emp_id = $emp;
                    $agentTargetData->product = $product;
                    $agentTargetData->department = $dept;
                    $agentTargetData->target = $target;
                    $agentTargetData->created_at = date('Y-m-d H:i:s');
                    $agentTargetData->upload_by = $user_id;
                    $agentTargetData->file_name = $filename;
                    $agentTargetData->save();
                }
            }
        
            return response()->json(['success'=>'File Uploaded Successfully.']);

        }
    }


}