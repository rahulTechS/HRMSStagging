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
use App\Models\Employee\EmployeeAttendanceTemp;
use Carbon\Carbon;
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
	public function uploadAttendanceData(Request $request){
		$attarray=array();
		$attdata=EmployeeAttendanceTemp::where("status",1)->first();
		
		if($attdata!=''){
		$month=$attdata->month;
		$year=$attdata->year;
		$days=Carbon::parse($year.'-'.$month)->daysInMonth;
		//echo $days=cal_days_in_month(CAL_GREGORIAN,2,2004);
		$empdetails = Employee_details::where("source_code",$attdata->sourc_code)->first();
			if($empdetails!=''){
				$dept_id=$empdetails->dept_id;
				$emp_id=$empdetails->id;
				$emp_padding_id=$empdetails->emp_id;
				for($i=1;$i<=$days; $i++){
					$empAttendanceModel = new EmployeeAttendanceModel();
					$date=$year.'-'.$month.'-'.$i;
					$empAttendanceModel->attendance_date=date('Y-m-d',strtotime($date));
					$empAttendanceModel->emp_id = $emp_id;
					$empAttendanceModel->dept_id = $dept_id;
					$empAttendanceModel->emp_padding_id = $emp_padding_id;
					//echo $attdata->day_1;
					$data='day_'.$i;
					//$attdata->$data;//exit;
					//echo $attdata->day.'_'.$i;exit;
				if($attdata->$data == 'A')
						{
							
							$empAttendanceModel->mark_attendance = 'absent';
							$empAttendanceModel->leave_type = '';
							$empAttendanceModel->leave_approved = '0';
						}
						else if($attdata->$data  == 'P')
						{
							$empAttendanceModel->mark_attendance = 'present';
							$empAttendanceModel->leave_type = '';
							$empAttendanceModel->leave_approved = '0';
						}
						else if($attdata->$data == 'S')
						{
							$empAttendanceModel->mark_attendance = 'sandwich';
							$empAttendanceModel->leave_type = '';
							$empAttendanceModel->leave_approved = '0';
						}
						else if($attdata->$data == 'L')
						{
							$empAttendanceModel->mark_attendance = 'late';
							$empAttendanceModel->leave_type = '';
							$empAttendanceModel->leave_approved = '0';							
						}
						else if($attdata->$data == 'AL')
						{
							$empAttendanceModel->mark_attendance = 'leave';	
							$empAttendanceModel->leave_type = 'annual_leave';	
							$empAttendanceModel->leave_approved = '2';	
						}
						else if($attdata->$data == 'EL')
						{
							$empAttendanceModel->mark_attendance = 'leave';	
							$empAttendanceModel->leave_type = 'emergency_leave';	
							$empAttendanceModel->leave_approved = '2';	
						}
						else if($attdata->$data == 'HD')
						{
							$empAttendanceModel->mark_attendance = 'leave';	
							$empAttendanceModel->leave_type = 'half_day';	
							$empAttendanceModel->leave_approved = '2';	
						}
						else if($attdata->$data == 'SL')
						{
							$empAttendanceModel->mark_attendance = 'leave';	
							$empAttendanceModel->leave_type = 'sick_leave';	
							$empAttendanceModel->leave_approved = '2';	
						}
						$empAttendanceModel->over_ride_sandwich = '0';
						$empAttendanceModel->save();	
						
					
				}
				
				$obj=EmployeeAttendanceTemp::find($attdata->id);
				$obj->status=2;
				if($obj->save())
				{
					echo "done";
					exit;
				}
				else
				{
					echo "not done";
					exit;
				}
				echo "done";
			}
			else{
				$obj=EmployeeAttendanceTemp::find($attdata->id);
				$obj->status=3;
				$obj->save();
			}
		}
		
	}
}
