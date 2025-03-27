<?php

namespace App\Http\Controllers\Attribute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  App\Models\Attribute\Attributes;
use  App\Models\Attribute\AttributeType;
use App\Models\Company\Department;
use App\Models\Attribute\AttributeImportDetails;
class AttributeImpController extends Controller
{
    
        public function empAttributeImport()
        {
			$attrFImport = AttributeImportDetails::orderBy("id","DESC")->get();
            return view("Attribute/empAttributeImport",compact('attrFImport') );
        }
		
		public function attrFileUpload(Request $request)
        {
			
          $request->validate([

            'file' => 'required|mimes:csv,txt|max:2048',

        ]);

  

        $fileName = time().'_AttributeDetails.csv';  

   

        $request->file->move(public_path('uploads'), $fileName);

			$attrObjImport = new AttributeImportDetails();
            $attrObjImport->f_name = $fileName;
            $attrObjImport->save();

        return back()

            ->with('success','You have successfully upload file.')

            ->with('file',$fileName);
        }
}