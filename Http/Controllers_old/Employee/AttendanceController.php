<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company\Department;
use  App\Models\Attribute\Attributes;
use App\Models\Employee\Employee_attribute;
use App\Models\Employee\Employee_details;
use App\Models\Employee\EmployeeAttendanceImportFiles;
use App\Models\Employee\EmployeeAttendanceModel;
use Session;


class AttendanceController extends Controller
{

	public function __construct()
	{
		
		
	}
	
   	public function importEmp()
		{
			$empFImport = EmployeeAttendanceImportFiles::orderBy("id","DESC")->get();
			$attrFImport = array();
            return view("Attendance/importEmp",compact('empFImport','empFImport') );
		}
		
		public function empFileUpload(Request $request)
        {
			
          $request->validate([

            'file' => 'required|mimes:csv,txt|max:2048',

        ]);

  

        $fileName = time().'_EmployeeAttendance.csv';  

   

        $request->file->move(public_path('uploads/empAttendanceImport'), $fileName);

			$empAttendanceObjImport = new EmployeeAttendanceImportFiles();
            $empAttendanceObjImport->file_name = $fileName;
            $empAttendanceObjImport->save();

        return back()

            ->with('success','You have successfully upload file.')

            ->with('file',$fileName);
        }
		
		public function empAttendanceFileImport(Request $request)
		{
			$detailsV = $request->input();
			$attr_f_import = $detailsV['attr_f_import'];
			$empDetailsDat = EmployeeAttendanceImportFiles::find($attr_f_import);
			$filename = $empDetailsDat->file_name;
			$uploadPath = '/srv/www/htdocs/hrm/public/uploads/empAttendanceImport/';
			$fullpathFileName = $uploadPath . $filename;
			$file = fopen($fullpathFileName, "r");
			$i = 1;
			$dataFromCsv = array();
			while (!feof($file)) {

				$dataFromCsv[$i] = fgetcsv($file);

				$i++;
			}
			/* echo '<pre>';
			print_r($dataFromCsv);
			exit; */
			
			fclose($file);
			$empAttendanceModel = new EmployeeAttendanceModel();
			$iCsv = 0;
			foreach ($dataFromCsv as $fromCsv) {
				/* if($iCsv == 13)
				{
					echo '<pre>';
					print_r($fromCsv);
					exit;
				} */
				if ($iCsv != 0) {
					/* 	echo "done";exit; */
					/* echo '<pre>';	
					print_r($fromCsv);
					exit; */
					/*
					*Mark Attendance Code
					*Start Code
					*/
					
					$empDetailsDatas =  Employee_details::where("emp_id",$fromCsv[0])->first();
					
					$empid = $empDetailsDatas->id;
					$dept_id = $empDetailsDatas->dept_id;
					$markAttendanceEmp = array();
					$indexCount = 0;
					$markDate = '01-03-2022';
					for($index=1;$index <= 31;$index++)
					{
						$_dateSet = date('Y-m-d',strtotime($markDate));
						$markDate = date('d-m-Y', strtotime( $markDate . " +1 days"));
						if(!empty($fromCsv[$index]))
						{
						
						$markAttendanceEmp[$indexCount]['emp_id'] = $empid;
						$markAttendanceEmp[$indexCount]['dept_id'] = $dept_id;
						$markAttendanceEmp[$indexCount]['emp_padding_id'] = $fromCsv[0];
						$markAttendanceEmp[$indexCount]['attendance_date'] = $_dateSet;
						
						if($fromCsv[$index] == 'A')
						{
							$markAttendanceEmp[$indexCount]['mark_attendance'] = 'absent';
							$markAttendanceEmp[$indexCount]['leave_type'] = '';
							$markAttendanceEmp[$indexCount]['leave_approved'] = '0';
						}
						else if($fromCsv[$index] == 'P')
						{
							$markAttendanceEmp[$indexCount]['mark_attendance'] = 'present';
							$markAttendanceEmp[$indexCount]['leave_type'] = '';
							$markAttendanceEmp[$indexCount]['leave_approved'] = '0';
						}
						else if($fromCsv[$index] == 'S')
						{
							$markAttendanceEmp[$indexCount]['mark_attendance'] = 'sandwich';
							$markAttendanceEmp[$indexCount]['leave_type'] = '';
							$markAttendanceEmp[$indexCount]['leave_approved'] = '0';
						}
						else if($fromCsv[$index] == 'L')
						{
							$markAttendanceEmp[$indexCount]['mark_attendance'] = 'late';
							$markAttendanceEmp[$indexCount]['leave_type'] = '';
							$markAttendanceEmp[$indexCount]['leave_approved'] = '0';							
						}
						else if($fromCsv[$index] == 'AL')
						{
							$markAttendanceEmp[$indexCount]['mark_attendance'] = 'leave';	
							$markAttendanceEmp[$indexCount]['leave_type'] = 'annual_leave';	
							$markAttendanceEmp[$indexCount]['leave_approved'] = '1';	
						}
						else if($fromCsv[$index] == 'EL')
						{
							$markAttendanceEmp[$indexCount]['mark_attendance'] = 'leave';	
							$markAttendanceEmp[$indexCount]['leave_type'] = 'emergency_leave';	
							$markAttendanceEmp[$indexCount]['leave_approved'] = '1';	
						}
						else if($fromCsv[$index] == 'HD')
						{
							$markAttendanceEmp[$indexCount]['mark_attendance'] = 'leave';	
							$markAttendanceEmp[$indexCount]['leave_type'] = 'half_day';	
							$markAttendanceEmp[$indexCount]['leave_approved'] = '1';	
						}
						else if($fromCsv[$index] == 'SL')
						{
							$markAttendanceEmp[$indexCount]['mark_attendance'] = 'leave';	
							$markAttendanceEmp[$indexCount]['leave_type'] = 'sick_leave';	
							$markAttendanceEmp[$indexCount]['leave_approved'] = '1';	
						}
						else
						{
							/* $markAttendanceEmp[$indexCount]['mark_attendance'] = $fromCsv[$index];	
							$markAttendanceEmp[$indexCount]['leave_type'] = 'check';	
							$markAttendanceEmp[$indexCount]['leave_approved'] = '1';	 */
						}
						$markAttendanceEmp[$indexCount]['over_ride_sandwich'] = '0';	
						$markAttendanceEmp[$indexCount]['updated_at'] = date('Y-m-d');	
						$markAttendanceEmp[$indexCount]['created_at'] = date('Y-m-d');	
						$markAttendanceEmp[$indexCount]['created_by'] = $request->session()->get('EmployeeId');	
						
						$indexCount++;
						}
						
					}
					  
					$empAttendanceModel->insert($markAttendanceEmp);
					
					/*
					*Mark Attendance Code
					*End Code
					*/
					
				}
				$iCsv++;
			}
			echo "Done";
			exit;
		}
}
