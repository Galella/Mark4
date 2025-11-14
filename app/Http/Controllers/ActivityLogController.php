<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Hanya super admin dan admin wilayah yang bisa melihat semua log
        if ($user->isSuperAdmin() || $user->isAdminWilayah()) {
            $activityLogs = ActivityLog::with('user')
                ->orderBy('logged_at', 'desc')
                ->paginate(20);
        } else {
            // User biasa hanya bisa melihat log miliknya sendiri
            $activityLogs = ActivityLog::with('user')
                ->where('user_id', $user->id)
                ->orderBy('logged_at', 'desc')
                ->paginate(20);
        }
        
        return view('activity-logs.index', compact('activityLogs'));
    }

    /**
     * Show the specified resource.
     */
    public function show(ActivityLog $activityLog)
    {
        $user = Auth::user();
        
        // Cek akses: super admin dan admin wilayah bisa lihat semua, 
        // user biasa hanya bisa lihat miliknya sendiri
        if (!($user->isSuperAdmin() || $user->isAdminWilayah() || $activityLog->user_id === $user->id)) {
            abort(403, 'Unauthorized access.');
        }
        
        return view('activity-logs.show', compact('activityLog'));
    }
}