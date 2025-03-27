<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;
use App\Models\Permission\Aclmodule;
use App\Models\Permission\PermissionGroup;
use App\Models\Entry\Employee;
class UserPermissionAuth
{
    public static function modulepermission($moduleId,$loginId,$type)
    {
		$employeeDetails = Employee::where('id',$loginId)->first();
		
		if($employeeDetails->designation == 'Admin')
		{
			
			return true;
		}
		else
		{
			if($type == 'normal')
			{
				$employeeDetails = Employee::where('id',$loginId)->first();
				$groupId = $employeeDetails->group_id;
				$permissioncheckCount = PermissionGroup::where('group_id',$groupId)->where('module_id',$moduleId)->count();
				if($permissioncheckCount > 0)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				/*
				*get all child module of parent module
				*start code
				*/
				$childrenModule = Aclmodule::where('parent_module_id',$moduleId)->where('parent_id',0)->get();
				
				$childModuleId = array();
				foreach($childrenModule as $child)
				{
					$childModuleId[] = $child->id;
				}
				
				/*
				*get all child module of parent module
				*end code
				*/
				if(count($childModuleId) >0)
				{
					$employeeDetails = Employee::where('id',$loginId)->first();
					$groupId = $employeeDetails->group_id;
					$permissioncheckCount = PermissionGroup::where('group_id',$groupId)->whereIn('module_id',$childModuleId)->count();
					
					
					if($permissioncheckCount > 0)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
						return false;
				}
			}
		}
		
		
       
    }
	
	
}