<?php

namespace App\Http\Middleware;

use Closure;

class IsOff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null){
    // retrieve setting from database and turn into key value array
	 	if($this->alreadyInstalled()) {
			if( setting('maintenance') == 'yes' && !$request->is('admin/*')){
				return response()->view('errors.maintenance', [], 500);
			}
		}
		return $next($request);
	}
	
	 public function alreadyInstalled()
    {
        return file_exists(storage_path('installed'));
    }

}