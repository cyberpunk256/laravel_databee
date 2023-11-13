<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        } 
        $roles = empty($roles) ? [] : $roles;
        $role = Auth::guard('admin')->user()->role;
        if(!in_array(strval($role), $roles)) {
            return redirect()->route('admin.index');
        }
        return $next($request);
    }
}
