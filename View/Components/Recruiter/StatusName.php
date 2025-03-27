<?php

namespace App\View\Components\Recruiter;

use Illuminate\View\Component;
use App\Models\Recruiter\CandidateStatus;

class StatusName extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $statusId;
    public function __construct($statusId)
    {
        $eMod = CandidateStatus::where('id',$statusId)->first();
        $this->statusId = $eMod->status_name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Recruiter.get-status-name');
    }
}
