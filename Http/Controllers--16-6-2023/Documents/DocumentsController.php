<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\User;
use Illuminate\Support\Facades\Validator;
use UserPermissionAuth;
use App\Models\Employee\Employee_details;
use App\Models\Payroll\AnnualLeaveDetails;
use App\Models\Payroll\AnnualLeave;
use App\Models\Employee\Employee_attribute;
use App\Models\Documents\FoldersManagement;
use App\Models\Documents\FoldersDocument;
use App\Models\Entry\Employee;
use Carbon\Carbon;



class DocumentsController extends Controller
{
    public function manageDoc()
		{
			$folderDetailsParent = FoldersManagement::where("status",1)->where("parent_folder",0)->orderBy("id","DESC")->get();
			return view("Documents/manageDoc",compact('folderDetailsParent'));
			
		}
	
	 public function uploadDoc(Request $request)
		{
			$insertId = $request->insert_id;
			$details = FoldersDocument::where("id",$insertId)->first();
			$breadcrumb  = array();
			$arrangements = explode(",",$details->arrangement);
			foreach($arrangements as $arrange)
			{
				$breadcrumb[] = FoldersManagement::where("id",$arrange)->first()->folder_name;
			}
			
			$emps = Employee::where("status","1")->orderBy("id","DESC")->get();
			return view("Documents/uploadDoc",compact('details','breadcrumb','emps'));
		}
		
	public function createParentFolder(Request $request)
		{
			$parentId = $request->parentId;
			$emps = Employee::where("status","1")->orderBy("id","DESC")->get();
			return view("Documents/createParentFolder",compact('parentId','emps'));
		}
	public function updateSubFolderAccess(Request $request)
		{
			$subfolderId = $request->subfolderId;
			$emps = Employee::where("status","1")->orderBy("id","DESC")->get();
			$subFolderDetails = FoldersManagement::where("id",$subfolderId)->first();
			return view("Documents/updateSubFolderAccess",compact('emps','subFolderDetails'));
		}	
    public function viewDocuments()
	{
		$parentFolderLists = FoldersManagement::where("parent_folder",0)->where("status",1)->orderBy("id","DESC")->get();
		return view("ViewDocuments/documentList",compact('parentFolderLists'));
	}
	
	public function saveFolder(Request $request)
	{
			$inputData = $request->input();
			/* echo '<pre>';
			print_r($inputData);
			exit;	 */
			$parentId = $inputData['parent_id'];
			$folderManagementObj = new FoldersManagement();
			$folderManagementObj->folder_name = $inputData['folder_name'];
			$folderManagementObj->parent_folder = $inputData['parent_id'];
			if($parentId >0)
			{
				$userIdList = implode(",",$inputData['users_id']);
				$createBy = $request->session()->get('EmployeeId');
				$folderManagementObj->users_id = $userIdList.','.$createBy;
			}
			$folderManagementObj->status = 1;
			$folderManagementObj->created_by = $request->session()->get('EmployeeId');
			$folderManagementObj->save();
			 $request->session()->flash('message','Folder Save Successfully.');
			return  redirect()->back();
			
	}
	
	public function updateFolder(Request $request)
	{
		$inputData = $request->input();
			 
			$id = $inputData['id'];
			$folderManagementObjUpdate = FoldersManagement::find($id);
			$folderManagementObjUpdate->folder_name = $inputData['folder_name'];
			
			
				$userIdList = implode(",",$inputData['users_id']);
				$createBy = $request->session()->get('EmployeeId');
				$folderManagementObjUpdate->users_id = $userIdList.','.$createBy;
				
			
			$folderManagementObjUpdate->save();
			 $request->session()->flash('message','Folder Updated Successfully.');
			return  redirect()->back();
	}
	
	public function getSubFolder(Request $request)
	{
		$parentId =  $request->parentId;
		$counting =  $request->counting;
		$folderDetails = FoldersManagement::where("status",1)->where("parent_folder",$parentId)->orderBy("id","DESC")->get();
		return view("Documents/getSubFolder",compact('folderDetails','parentId','counting'));
	}
	
	public function readyForDocuments(Request $request)
	{
		$inputData = $request->input();
		$arrangementarray = array();
		$arrangementarray[] = $inputData['parentFolderId'];
		foreach($inputData['subfolderId']  as $_sub)
		{
			if($_sub !='')
			{
			$arrangementarray[] = $_sub;
			}
		}
	
		$foldersDocObj = new FoldersDocument();
	    $arragement = implode(",",$arrangementarray);
		$foldersDocObj->arrangement = $arragement;
		$foldersDocObj->final_folder_id = end($arrangementarray);
		$foldersDocObj->parent_folder_id = $arrangementarray[0];
		$foldersDocObj->status = 1;
		$foldersDocObj->created_by = $request->session()->get('EmployeeId');
		$foldersDocObj->save();
		return redirect('uploadDoc/'.$foldersDocObj->id);
	}
	
	public function folderDocumentUpload(Request $request)
	{
		$counting =  $request->counting;
		return view("Documents/folderDocumentUpload",compact('counting'));
	}
       
	public function saveFolderDocuments(Request $request)
	{
		$inputData = $request->input();
		/* echo '<pre>';
		print_r($inputData);
		exit; */
		if(!isset($inputData['caption']))
		{
			 $request->session()->flash('message','Please Upload At least one document.');
			return  redirect()->back();
		}
		else
		{
		
			$keys = array_keys($_FILES);
			
			$filesAttributeInfo = array();
			$listOfAttribute = array();
			$fileIndex = 0;
			foreach($keys as $key)
			{
				if($request->file($key))
				{
					
				 $filenameWithExt = $request->file($key)->getClientOriginalName ();
				$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
				$fileExtension =$request->file($key)->getClientOriginalExtension();
				$vKey = $key;
				 $newFileName = $key.'_'.date("Y_m_d_H_s_i").'.'.$fileExtension;
			   
				    if(file_exists(public_path('uploads/foldersdoc/'.$newFileName))){

					  unlink(public_path('uploads/foldersdoc/'.$newFileName));

					}
				
				/*
				*Updating File Name
				*/
				$filesAttributeInfo[$vKey] = $newFileName;
				$listOfAttribute[] = $vKey;
				/*
				*Updating File Name
				*/
				// Get just Extension
				$extension = $request->file($key)->getClientOriginalExtension();
				// Filename To store
				$fileNameToStore = $filename. '_'. time().'.'.$extension;
				$request->file($key)->move(public_path('uploads/foldersdoc/'), $newFileName);
				$fileIndex++;
				}
			}
			
			
			/* first Update*/
				$firstId = $inputData['firstId'];
				$foldersDocObj = FoldersDocument::find($firstId);
				$firstIndex = $listOfAttribute[0];
				$foldersDocObj->document_name = $filesAttributeInfo[$firstIndex];
				$foldersDocObj->description = $inputData['comment'][$firstIndex];
				$foldersDocObj->caption = $inputData['caption'][$firstIndex];
			
				$foldersDocObj->status = 2;
				$foldersDocObj->marked_as_important = 1;
				$foldersDocObj->read_status = 1;
				$foldersDocObj->save();
				
				/* first Update*/
				/*
				*insert Next
				*/
				$previousDetails = FoldersDocument::where("id",$firstId)->first();
				$indexRun = 0;
				foreach($listOfAttribute as $_list)
				{
					if($indexRun >0)
					{
						$insertObj = new FoldersDocument();
						$insertObj->arrangement = $previousDetails->arrangement;
						$insertObj->final_folder_id = $previousDetails->final_folder_id;
						$insertObj->parent_folder_id = $previousDetails->parent_folder_id;
						
						$insertObj->created_by = $previousDetails->created_by;
						$insertObj->marked_as_important = 1;
						$insertObj->read_status = 1;
						$insertObj->document_name = $filesAttributeInfo[$_list];
						$insertObj->description = $inputData['comment'][$_list];
						$insertObj->caption = $inputData['caption'][$_list];
						$insertObj->status = 2;
						$insertObj->save();
					}
					$indexRun++;
				}
				/*
				*insert Next
				*/
				 $request->session()->flash('message','Documents Save Successfully.');
				 return redirect('viewDocuments');
		}
	}

	public static function getTotalDocumentsOfFolder($folderId=NULL)
	{
		return FoldersDocument::where("parent_folder_id",$folderId)->where("status",2)->get()->count();
	}
	
	public static function getTotallastUpdated($folderId=NULL)
	{
		$countDocument = FoldersDocument::where("parent_folder_id",$folderId)->where("status",2)->get()->count();
		if($countDocument >0)
		{
			$createAt = FoldersDocument::where("parent_folder_id",$folderId)->where("status",2)->orderBy("id","DESC")->first()->created_at;
			return date("d M Y",strtotime($createAt));
		}
		else
		{
		return "--";
		}
	}
	public function fillPanel(Request $request)
	{
		$parentFId = $request->parentFId;
		$eId = $request->session()->get('EmployeeId');
		$getSubFolder = FoldersManagement::where("parent_folder",$parentFId)->where("status",1)->orderBy("id","DESC")->get();
		/*
		*check Allow Folders For Documents
		*/
		$subFolderArray = array();
		foreach($getSubFolder as $subFolder)
		{
			$userIdArray = explode(",",$subFolder->users_id);
			if(in_array($eId,$userIdArray))
			{
				$subFolderArray[] = $subFolder->id;
			}
		}
	
		/*
		*check Allow Folders For Documents
		*/
		/*
		*check for Documents
		*/
		$subFolderArrayAllDoc = array();
		$getAllfinalDocs = FoldersDocument::where("parent_folder_id",$parentFId)->where("status",2)->get();
		foreach($getAllfinalDocs as $finalDoc)
		{
			$finalDocId = $finalDoc->final_folder_id;
			$usersSubId = FoldersManagement::where("id",$finalDocId)->first()->users_id;
			$userIdArray = explode(",",$usersSubId);
			if(in_array($eId,$userIdArray))
			{
				$subFolderArrayAllDoc[] = $finalDocId;
			}
		}
		
		/*
		*check for Documents
		*/
		$getFolderDocs = FoldersDocument::where("parent_folder_id",$parentFId)->where("status",2)->whereIn("final_folder_id",$subFolderArrayAllDoc)->orderBy("id","DESC")->get();
		
		return view("ViewDocuments/fillPanel",compact('getSubFolder','getFolderDocs','parentFId','eId'));
	}
	
	public function fillPanelSub(Request $request)
	{
		$subFId = $request->subFId;
		$mainPID = $request->mainPID;
		$eId = $request->session()->get('EmployeeId');
		$getSubFolder = FoldersManagement::where("parent_folder",$subFId)->where("status",1)->orderBy("id","DESC")->get();
		
		$getFolderDocs = FoldersDocument::where("final_folder_id",$subFId)->where("status",2)->orderBy("id","DESC")->get();
		$parentFId = $subFId;
		/*
		*get Back id
		*/
		$backId = FoldersManagement::where("id",$parentFId)->first()->parent_folder;
		$backIdOneStep = FoldersManagement::where("id",$backId)->first()->parent_folder;
		$backBehave = 'sub';
		if($backId == 0 || $backIdOneStep == 0)
		{
			$backBehave = 'main';
		}
		/*
		*get Back id
		*/
		
		$arrangementarrayR = array();
		
		$arrangementarrayR[] = $parentFId;
		
		$getParentId = $this->manageArrangement($parentFId);
		while($getParentId != 0)
		{
			$arrangementarrayR[] = $getParentId;
			$getParentId = $this->manageArrangement($getParentId);
		}
		
		$arrangementarray = array_reverse($arrangementarrayR);
		
		
		return view("ViewDocuments/fillPanelSub",compact('getSubFolder','getFolderDocs','parentFId','eId','mainPID','backId','backBehave','arrangementarray'));
	}
	

	public static function getTotalDocInSub($pid=NULL,$sid=NULL)
	{
		$docs = FoldersDocument::where("final_folder_id",$sid)->where("status",2)->get()->count();
		
		return $docs;
		
	}
	public static function getlastUpdatedSubFolder($pid=NULL,$sid=NULL)
	{
		$docs = FoldersDocument::where("final_folder_id",$sid)->where("status",2)->orderBy("id","DESC")->first();
		
		return date("d M Y",strtotime($docs->created_at));
	}
	
	public static function uploadedBy($eid=NULL)
	{
		return Employee::where("id",$eid)->first()->fullname;
	}
	
	public function readyforUpload(Request $request)
	{
		$fid = $request->fid;
		$arrangementarrayR = array();
		$arrangementarrayR[] = $fid;
		
		$getParentId = $this->manageArrangement($fid);
		while($getParentId != 0)
		{
			$arrangementarrayR[] = $getParentId;
			$getParentId = $this->manageArrangement($getParentId);
		}
		
		$arrangementarray = array_reverse($arrangementarrayR);
		
		
	
		$foldersDocObj = new FoldersDocument();
	    $arragement = implode(",",$arrangementarray);
		$foldersDocObj->arrangement = $arragement;
		$foldersDocObj->final_folder_id = end($arrangementarray);
		$foldersDocObj->parent_folder_id = $arrangementarray[0];
		$foldersDocObj->status = 1;
		$foldersDocObj->created_by = $request->session()->get('EmployeeId');
		$foldersDocObj->save();
		return redirect('uploadDoc/'.$foldersDocObj->id);
	}
	
	protected function manageArrangement($fid)
	{
		
		$parentId = FoldersManagement::where("id",$fid)->first()->parent_folder;
		return $parentId;
	}
	
	public static function folderName($fid = NULL)
	{
		return FoldersManagement::where("id",$fid)->first()->folder_name;
	}
	
	
	
	public static function getPreviousBehave($fid = NULL)
	{
		$pId =  FoldersManagement::where("id",$fid)->first()->parent_folder;
		
		if($pId == 0)
		{
			return 'main';
		}
		else
		{
			return 'sub';
		}
	}
	
	public static function arrangeBreadcrumb($arrangement)
	{
			$arrangements = explode(",",$arrangement);
			foreach($arrangements as $arrange)
			{
				$breadcrumb[] = FoldersManagement::where("id",$arrange)->first()->folder_name;
			}
			$breadcrumbPattern = implode("/",$breadcrumb);
			return $breadcrumbPattern;
	}
}
