<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use App\Services\ActivityLogService;

class AuthController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Log user login activity
            $this->activityLogService->logUserLogin();

            // Redirect berdasarkan role
            $user = Auth::user();
            
            return $this->redirectTo($user);
        }

        // Log failed login attempt
        $this->activityLogService->logError(
            module: 'auth',
            operation: 'login',
            message: 'Failed login attempt',
            context: [
                'email' => $request->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Redirect user based on their role.
     */
    protected function redirectTo($user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->route('dashboard');
        } elseif ($user->isAdminWilayah()) {
            return redirect()->route('dashboard');
        } elseif ($user->isAdminArea()) {
            return redirect()->route('dashboard');
        } elseif ($user->isAdminOutlet()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('dashboard');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        // Log user logout activity before logging out
        $this->activityLogService->logUserLogout();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Show the registration form for admins.
     */
    public function showRegistrationForm()
    {
        $user = Auth::user();
        
        // Hanya super admin dan admin wilayah yang bisa membuat user
        if (!$user || !($user->isSuperAdmin() || $user->isAdminWilayah())) {
            abort(403, 'Unauthorized access.');
        }

        // Ambil offices jika user adalah admin wilayah
        $offices = collect();
        if ($user->isAdminWilayah()) {
            $offices = \App\Models\Office::where('parent_id', $user->office_id)
                ->orWhere('id', $user->office_id)
                ->get();
        } elseif ($user->isSuperAdmin()) {
            $offices = \App\Models\Office::whereIn('type', ['wilayah', 'area'])->get();
        }

        $outlets = $user->isAdminWilayah() ? \App\Models\Outlet::whereHas('office', function($query) use ($user) {
            $query->where('parent_id', $user->office_id)
                  ->orWhere('id', $user->office_id);
        })->get() : collect();

        if ($user->isSuperAdmin()) {
            $outlets = \App\Models\Outlet::all();
        }

        return view('auth.register', compact('offices', 'outlets'));
    }

    /**
     * Handle a registration request for admins.
     */
    public function register(Request $request)
    {
        $user = Auth::user();
        
        // Hanya super admin dan admin wilayah yang bisa membuat user
        if (!$user || !($user->isSuperAdmin() || $user->isAdminWilayah())) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:super_admin,admin_wilayah,admin_area,admin_outlet'],
        ]);

        // Validasi office_id dan outlet_id berdasarkan role dan akses
        $officeId = null;
        $outletId = null;

        if (in_array($request->role, ['admin_wilayah', 'admin_area'])) {
            $officeId = $request->office_id;
            
            // Validasi office_id
            if (!$officeId) {
                return back()->withErrors(['office_id' => 'Office is required for this role.']);
            }

            // Cek akses ke office
            $office = \App\Models\Office::find($officeId);
            if (!$office) {
                return back()->withErrors(['office_id' => 'Selected office does not exist.']);
            }

            if (!$user->isSuperAdmin() && !$user->hasOfficeAccess($office)) {
                return back()->withErrors(['office_id' => 'You do not have access to this office.']);
            }
        } elseif ($request->role === 'admin_outlet') {
            $outletId = $request->outlet_id;
            
            // Validasi outlet_id
            if (!$outletId) {
                return back()->withErrors(['outlet_id' => 'Outlet is required for this role.']);
            }

            // Cek akses ke outlet
            $outlet = \App\Models\Outlet::find($outletId);
            if (!$outlet) {
                return back()->withErrors(['outlet_id' => 'Selected outlet does not exist.']);
            }

            if (!$user->isSuperAdmin() && !$user->hasOutletAccess($outlet)) {
                return back()->withErrors(['outlet_id' => 'You do not have access to this outlet.']);
            }
        }

        try {
            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'office_id' => $officeId,
                'outlet_id' => $outletId,
            ]);

            // Log user creation activity
            $this->activityLogService->logUserCreated([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
            ]);

            return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            // Log registration error
            $this->activityLogService->logError(
                module: 'auth',
                operation: 'registration',
                message: $e->getMessage(),
                context: [
                    'user_id' => $user->id ?? null,
                    'email' => $request->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );
            
            throw $e; // Re-throw so it's also caught by global exception handler
        }
    }
}
