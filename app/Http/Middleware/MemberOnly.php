<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (Auth::user()->role !== 'member') {
            return redirect('/login')->with('error', 'Akses ditolak. Login sebagai member diperlukan.');
        }

        if (Auth::user()->status !== 'active') {
            Auth::logout();
            return redirect('/login')->with('error', 'Akun Anda tidak aktif. Silakan hubungi Admin.');
        }

        return $next($request);
    }
}