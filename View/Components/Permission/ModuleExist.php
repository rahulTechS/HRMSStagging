<?php

namespace App\View\Components\Permission;

use Illuminate\View\Component;
use App\Models\Permission\PermissionGroup;
class ModuleExist extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
	public $showFlag;
	
    public function __construct($gId,$mid)
    {
		if(!empty($gId) && $mid != 0)
		{
		
		$existPermission = PermissionGroup::where("group_id",$gId)->where("module_id",$mid)->count();
		if($existPermission == 0)
		{
		$this->showFlag = 1;
		}
		else
		{
			$this->showFlag = 2;
		}
		}
		else
		{
			$this->showFlag = 2;
		}
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.permission.module-exist');
    }
}
