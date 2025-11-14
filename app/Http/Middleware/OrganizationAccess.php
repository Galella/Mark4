<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class OrganizationAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $modelType): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Super admin dapat mengakses semua data
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Untuk model office
        if ($modelType === 'office') {
            $officeId = $request->route('office') ? $request->route('office')->id : $request->route('id');
            
            if ($officeId) {
                $office = \App\Models\Office::find($officeId);
                
                if ($office && !$user->hasOfficeAccess($office)) {
                    abort(403, 'Unauthorized access. You do not have access to this office.');
                }
            }
        }
        // Untuk model outlet
        elseif ($modelType === 'outlet') {
            $outletId = $request->route('outlet') ? $request->route('outlet')->id : $request->route('id');
            
            if ($outletId) {
                $outlet = \App\Models\Outlet::find($outletId);
                
                if ($outlet && !$user->hasOutletAccess($outlet)) {
                    abort(403, 'Unauthorized access. You do not have access to this outlet.');
                }
            }
        }

        return $next($request);
    }
}
