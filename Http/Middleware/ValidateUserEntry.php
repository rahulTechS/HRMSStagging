<?php

namespace App\Http\Middleware;

use Closure;

class ValidateUserEntry
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
			
		if(!$request->session()->has('EmployeeId') && empty($request->session()->get('EmployeeId')))
		{
			return redirect('/');
		}
	
        return $next($request);
    }
}
