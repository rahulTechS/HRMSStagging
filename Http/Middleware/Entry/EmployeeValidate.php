<?php

namespace App\Http\Middleware\Entry;

use Session;
use Closure;

class EmployeeValidate
{
   
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
        $path=$request->path();
        if($path =='dashboard')
        {
            echo 'dfsd--'.Session::get('EmployeeId');
            exit;
        }
        /*
        if (Session::has('allow')) {
            return redirect('login');
        }*/

        
    }

    
}
