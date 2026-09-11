<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->canAccessAllBranches()) {
            abort(403, 'Akses Terbatas: Halaman ini hanya dapat diakses oleh Kepala Cabang / Super Administrator.');
        }

        return $next($request);
    }
}
