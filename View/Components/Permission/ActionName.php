<?php

namespace App\View\Components\Permission;

use Illuminate\View\Component;
use App\Models\Permission\Aclmodule;
class ActionName extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
	public $actionId;
	
    public function __construct($actionId)
    {
		if(!empty($actionId) && $actionId != 0)
		{
		
		$arrayDisplay = '';
		$arrayprivilages = explode(",",$actionId);
				foreach($arrayprivilages as $_privilagesID)
				{
					
					 $actionMod = Aclmodule::where("id",$_privilagesID)->first();
					 if($actionMod != '')
					 {
						$action_name= $actionMod->action_name;
					if(empty($arrayDisplay))
					{
						
						$arrayDisplay = $action_name;
					}
					else
					{
						$arrayDisplay= $arrayDisplay.','.$action_name;
					} 
					 }
				}
		$this->actionId = $arrayDisplay;
		
		}
		else
		{
			$this->actionId = $actionId;
		}
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.permission.action-name');
    }
}
