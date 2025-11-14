<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Office;
use App\Models\Outlet;
use App\Services\ActivityLogService;
use App\Exports\UserExport;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Query users berdasarkan akses pengguna
        $query = User::query();

        if ($user->isAdminWilayah()) {
            // Admin wilayah hanya bisa melihat user di wilayahnya
            $officeIds = $user->office->children()->pluck('id');
            $officeIds->push($user->office->id);

            $outletIds = Outlet::whereIn('office_id', $officeIds)->pluck('id');

            $query->where(function($q) use ($officeIds, $outletIds) {
                $q->whereIn('office_id', $officeIds)
                  ->orWhereIn('outlet_id', $outletIds);
            });
        } elseif ($user->isAdminArea()) {
            // Admin area hanya bisa melihat user di areanya
            $outletIds = $user->office->outlets()->pluck('id');

            $query->where(function($q) use ($outletIds) {
                $q->where('office_id', auth()->user()->office_id)
                  ->orWhereIn('outlet_id', $outletIds);
            });
        } elseif ($user->isAdminOutlet()) {
            // Admin outlet hanya bisa melihat dirinya sendiri
            $query->where('id', $user->id);
        }

        // Tambahkan pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Tambahkan filter berdasarkan role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->with(['office', 'outlet'])->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currentUser = Auth::user();

        // Aturan berdasarkan role
        if ($currentUser->isSuperAdmin()) {
            // Super admin bisa membuat user dengan role apa saja
            $offices = Office::whereIn('type', ['wilayah', 'area'])->get();
            $outlets = Outlet::all();
        } elseif ($currentUser->isAdminWilayah()) {
            // Admin wilayah hanya bisa membuat user admin_area dan admin_outlet di wilayahnya
            $offices = Office::where('parent_id', $currentUser->office_id)
                ->where('type', 'area') // Hanya area office
                ->get();
            $outlets = Outlet::whereHas('office', function($query) use ($currentUser) {
                $query->where('parent_id', $currentUser->office_id)
                      ->orWhere('id', $currentUser->office_id);
            })->get();
        } elseif ($currentUser->isAdminArea()) {
            // Admin area hanya bisa membuat user admin_outlet di areanya
            $offices = collect(); // Tidak bisa membuat user office-level
            $outlets = Outlet::where('office_id', $currentUser->office_id)->get();
        } else {
            // Admin outlet tidak bisa membuat user
            abort(403, 'Unauthorized access.');
        }

        return view('users.create', compact('offices', 'outlets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUserRequest $request)
    {
        $currentUser = Auth::user();

        // Get validated data from the Form Request
        $validatedData = $request->validated();

        // Aturan berdasarkan role yang membuat user
        if ($currentUser->isSuperAdmin()) {
            // Super admin bisa membuat user dengan role apa saja kecuali super_admin
            $roleValidationRules = [
                'role' => ['required', 'in:admin_wilayah,admin_area,admin_outlet'],
            ];
        } elseif ($currentUser->isAdminWilayah()) {
            // Admin wilayah hanya bisa membuat admin_area dan admin_outlet
            $roleValidationRules = [
                'role' => ['required', 'in:admin_area,admin_outlet'],
            ];
        } elseif ($currentUser->isAdminArea()) {
            // Admin area hanya bisa membuat admin_outlet
            $roleValidationRules = [
                'role' => ['required', 'in:admin_outlet'],
            ];
        } else {
            // Admin outlet tidak bisa membuat user
            abort(403, 'Unauthorized access.');
        }
        
        // Validate role-specific rules
        $validatedRoleData = $request->validate($roleValidationRules);
        
        // Merge role validation result with the original validated data
        $validatedData = array_merge($validatedData, $validatedRoleData);

        // Validasi office_id dan outlet_id berdasarkan role dan akses
        $officeId = null;
        $outletId = null;

        if (in_array($request->role, ['admin_wilayah', 'admin_area'])) {
            $officeId = $request->office_id;

            // Validasi office_id wajib untuk admin_wilayah dan admin_area
            if (!$officeId) {
                return back()->withErrors(['office_id' => 'Office is required for this role.']);
            }

            // Cek akses ke office
            $office = Office::find($officeId);
            if (!$office) {
                return back()->withErrors(['office_id' => 'Selected office does not exist.']);
            }

            if (!$currentUser->isSuperAdmin() && !$currentUser->hasOfficeAccess($office)) {
                return back()->withErrors(['office_id' => 'You do not have access to this office.']);
            }
        } elseif ($request->role === 'admin_outlet') {
            $outletId = $request->outlet_id;

            // Validasi outlet_id wajib untuk admin_outlet
            if (!$outletId) {
                return back()->withErrors(['outlet_id' => 'Outlet is required for this role.']);
            }

            // Cek akses ke outlet
            $outlet = Outlet::find($outletId);
            if (!$outlet) {
                return back()->withErrors(['outlet_id' => 'Selected outlet does not exist.']);
            }

            if (!$currentUser->isSuperAdmin() && !$currentUser->hasOutletAccess($outlet)) {
                return back()->withErrors(['outlet_id' => 'You do not have access to this outlet.']);
            }
        }

        $newUser = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
            'office_id' => $officeId,
            'outlet_id' => $outletId,
        ]);

        // Log user creation activity
        $this->activityLogService->logUserCreated([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'role' => $validatedData['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $currentUser = Auth::user();

        // Ambil offices dan outlets berdasarkan akses pengguna
        if ($currentUser->isAdminWilayah()) {
            $offices = Office::where('parent_id', $currentUser->office_id)
                ->orWhere('id', $currentUser->office_id)
                ->get();
            $outlets = Outlet::whereHas('office', function($query) use ($currentUser) {
                $query->where('parent_id', $currentUser->office_id)
                      ->orWhere('id', $currentUser->office_id);
            })->get();
        } elseif ($currentUser->isSuperAdmin()) {
            $offices = Office::whereIn('type', ['wilayah', 'area'])->get();
            $outlets = Outlet::all();
        } else {
            abort(403, 'Unauthorized access.');
        }

        return view('users.edit', compact('user', 'offices', 'outlets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $currentUser = Auth::user();

        // Get basic validated data
        $validatedData = $request->validated();

        // Aturan berdasarkan role yang mengupdate user
        $roleValidationRules = [];
        if ($currentUser->isSuperAdmin()) {
            // Super admin bisa mengupdate ke role apa saja kecuali super_admin
            $roleValidationRules = [
                'role' => ['required', 'in:admin_wilayah,admin_area,admin_outlet'],
            ];
        } elseif ($currentUser->isAdminWilayah()) {
            // Admin wilayah hanya bisa mengupdate ke admin_area dan admin_outlet
            $roleValidationRules = [
                'role' => ['required', 'in:admin_area,admin_outlet'],
            ];
        } elseif ($currentUser->isAdminArea()) {
            // Admin area hanya bisa mengupdate ke admin_outlet
            $roleValidationRules = [
                'role' => ['required', 'in:admin_outlet'],
            ];
        } else {
            // Admin outlet tidak bisa mengupdate user
            abort(403, 'Unauthorized access.');
        }

        // Validate role-specific rules
        $validatedRoleData = $request->validate($roleValidationRules);
        
        // Merge role validation result with the basic validated data
        $validatedData = array_merge($validatedData, $validatedRoleData);

        // Jika password diisi, validasi dan update
        $updateData = [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'role' => $validatedData['role'],
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', 'min:8'],
            ]);
            $updateData['password'] = Hash::make($request->password);
        }

        // Validasi office_id dan outlet_id berdasarkan role dan akses
        if (in_array($request->role, ['admin_wilayah', 'admin_area'])) {
            $officeId = $request->office_id;

            // Validasi office_id wajib untuk admin_wilayah dan admin_area
            if (!$officeId) {
                return back()->withErrors(['office_id' => 'Office is required for this role.']);
            }

            // Cek akses ke office
            $office = Office::find($officeId);
            if (!$office) {
                return back()->withErrors(['office_id' => 'Selected office does not exist.']);
            }

            if (!$currentUser->isSuperAdmin() && !$currentUser->hasOfficeAccess($office)) {
                return back()->withErrors(['office_id' => 'You do not have access to this office.']);
            }

            $updateData['office_id'] = $officeId;
            $updateData['outlet_id'] = null; // Reset outlet_id jika role berubah
        } elseif ($request->role === 'admin_outlet') {
            $outletId = $request->outlet_id;

            // Validasi outlet_id wajib untuk admin_outlet
            if (!$outletId) {
                return back()->withErrors(['outlet_id' => 'Outlet is required for this role.']);
            }

            // Cek akses ke outlet
            $outlet = Outlet::find($outletId);
            if (!$outlet) {
                return back()->withErrors(['outlet_id' => 'Selected outlet does not exist.']);
            }

            if (!$currentUser->isSuperAdmin() && !$currentUser->hasOutletAccess($outlet)) {
                return back()->withErrors(['outlet_id' => 'You do not have access to this outlet.']);
            }

            $updateData['outlet_id'] = $outletId;
            $updateData['office_id'] = null; // Reset office_id jika role berubah
        }

        // Log user update activity
        $oldData = $user->toArray();
        $newData = array_merge($oldData, $updateData);

        $user->update($updateData);

        $this->activityLogService->logUserUpdated(
            $oldData,
            $newData
        );

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Tidak bisa menghapus diri sendiri
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'Cannot delete yourself.');
        }

        // Log user deletion activity
        $oldData = $user->toArray();
        $this->activityLogService->logUserDeleted($oldData);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Export users to Excel.
     */
    public function export()
    {
        $this->authorize('viewAny', User::class);

        return Excel::download(new UserExport, 'users_' . date('Y-m-d_H-i-s') . '.xlsx');
    }
}