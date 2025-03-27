<?php

namespace App\View\Components\Recruiter;

use Illuminate\View\Component;
use App\Models\Recruiter\Designation;

class PostName extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public  $postId;
    public function __construct($postId)
    {
        $eMod = Designation::where('id',$postId)->first();
        $this->postId = $eMod->name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.Recruiter.get-post-name');
    }
}
