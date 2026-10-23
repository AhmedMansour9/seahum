<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InstallationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('purchase_key') == false && config('software.purchase_code') == null) {
            session()->flash('error', base64_decode('SW52YWxpZCBwdXJjaGFzZSBjb2RlIGZvciB0aGlzIHNvZnR3YXJlLg=='));
            return redirect('step2');
        }elseif(config('software.purchase_code') != null){
            return $next($request);
        }

        return $next($request);
    }
}
