<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Office;
use App\Services\ActivityLogService;
use App\Exports\OfficeExport;
use App\Http\Requests\CreateOfficeRequest;
use App\Http\Requests\UpdateOfficeRequest;
use Maatwebsite\Excel\Facades\Excel;

class OfficeController extends Controller
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

        // Query offices berdasarkan akses pengguna
        $query = Office::query();

        if ($user->isAdminWilayah()) {
            // Admin wilayah hanya bisa melihat office di wilayahnya
            $officeIds = $user->office->children()->pluck('id');
            $officeIds->push($user->office->id);
            $query->whereIn('id', $officeIds);
        }

        // Tambahkan pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        // Tambahkan filter berdasarkan tipe
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Tambahkan filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? true : false);
        }

        $offices = $query->with('parent')->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());

        return view('offices.index', compact('offices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Ambil offices yang bisa menjadi parent berdasarkan akses pengguna
        if ($user->isAdminWilayah()) {
            // Admin wilayah hanya bisa membuat office di wilayahnya
            $offices = Office::where('parent_id', $user->office_id)
                ->orWhere('id', $user->office_id)
                ->where('type', '!=', 'area') // Hanya bisa membuat area di bawah wilayah
                ->get();
        } elseif ($user->isSuperAdmin()) {
            // Super admin bisa membuat office dengan parent apa saja
            $offices = Office::where('type', '!=', 'area')->get(); // Parent hanya bisa pusat atau wilayah
        } else {
            abort(403, 'Unauthorized access.');
        }

        return view('offices.create', compact('offices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateOfficeRequest $request)
    {
        $user = Auth::user();

        $validatedData = $request->validated();

        // Validasi parent_id berdasarkan akses dan aturan hierarki
        $parentId = null;
        if ($request->filled('parent_id')) {
            $parent = Office::find($request->parent_id);
            if (!$parent) {
                return back()->withErrors(['parent_id' => 'Selected parent office does not exist.']);
            }

            // Cek akses ke parent office
            if (!$user->isSuperAdmin() && !$user->hasOfficeAccess($parent)) {
                return back()->withErrors(['parent_id' => 'You do not have access to this parent office.']);
            }

            // Validasi aturan hierarki: pusat -> wilayah -> area
            if ($request->type === 'wilayah' && $parent->type !== 'pusat') {
                return back()->withErrors(['parent_id' => 'Wilayah office must have Pusat as parent.']);
            }
            if ($request->type === 'area' && $parent->type !== 'wilayah') {
                return back()->withErrors(['parent_id' => 'Area office must have Wilayah as parent.']);
            }

            $parentId = $request->parent_id;
        } elseif ($request->type === 'wilayah' || $request->type === 'area') {
            // Kantor wilayah dan area harus memiliki parent
            return back()->withErrors(['parent_id' => 'This office type requires a parent office.']);
        }

        $office = Office::create(array_merge($validatedData, [
            'parent_id' => $parentId,
            'is_active' => $request->filled('is_active'),
        ]));

        // Log office creation activity
        $this->activityLogService->logOfficeCreated([
            'name' => $validatedData['name'],
            'code' => $validatedData['code'],
            'type' => $validatedData['type'],
        ]);

        return redirect()->route('offices.index')->with('success', 'Office created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Office $office)
    {
        $this->authorize('view', $office);

        return view('offices.show', compact('office'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Office $office)
    {
        $this->authorize('update', $office);

        $user = Auth::user();

        // Ambil offices yang bisa menjadi parent berdasarkan akses pengguna
        if ($user->isAdminWilayah()) {
            // Admin wilayah hanya bisa mengedit office di wilayahnya
            if (!$user->hasOfficeAccess($office)) {
                abort(403, 'Unauthorized access.');
            }

            // Hanya bisa memilih parent dari office di wilayahnya
            $offices = Office::where('parent_id', $user->office_id)
                ->orWhere('id', $user->office_id)
                ->where('type', '!=', 'area') // Hanya bisa memilih wilayah sebagai parent untuk area
                ->where('id', '!=', $office->id) // Tidak bisa memilih diri sendiri sebagai parent
                ->get();
        } elseif ($user->isSuperAdmin()) {
            // Super admin bisa mengedit office apa saja
            $offices = Office::where('type', '!=', 'area')
                ->where('id', '!=', $office->id) // Tidak bisa memilih diri sendiri sebagai parent
                ->get();
        } else {
            abort(403, 'Unauthorized access.');
        }

        return view('offices.edit', compact('office', 'offices'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOfficeRequest $request, Office $office)
    {
        $this->authorize('update', $office);

        $user = Auth::user();

        $validatedData = $request->validated();

        // Validasi parent_id berdasarkan akses dan aturan hierarki
        $parentId = null;
        if ($request->filled('parent_id')) {
            $parent = Office::find($request->parent_id);
            if (!$parent) {
                return back()->withErrors(['parent_id' => 'Selected parent office does not exist.']);
            }

            // Cek akses ke parent office
            if (!$user->isSuperAdmin() && !$user->hasOfficeAccess($parent)) {
                return back()->withErrors(['parent_id' => 'You do not have access to this parent office.']);
            }

            // Validasi aturan hierarki: pusat -> wilayah -> area
            if ($office->type === 'wilayah' && $parent->type !== 'pusat') {
                return back()->withErrors(['parent_id' => 'Wilayah office must have Pusat as parent.']);
            }
            if ($office->type === 'area' && $parent->type !== 'wilayah') {
                return back()->withErrors(['parent_id' => 'Area office must have Wilayah as parent.']);
            }

            $parentId = $request->parent_id;
        } elseif ($office->type === 'wilayah' || $office->type === 'area') {
            // Kantor wilayah dan area harus memiliki parent
            return back()->withErrors(['parent_id' => 'This office type requires a parent office.']);
        }

        // Log office update activity
        $oldData = $office->toArray();
        $newData = array_merge($oldData, $validatedData, [
            'parent_id' => $parentId,
            'is_active' => $request->filled('is_active'),
        ]);

        $office->update(array_merge($validatedData, [
            'parent_id' => $parentId,
            'is_active' => $request->filled('is_active'),
        ]));

        $this->activityLogService->logOfficeUpdated($oldData, $newData);

        return redirect()->route('offices.index')->with('success', 'Office updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office)
    {
        $this->authorize('delete', $office);

        // Cek apakah office memiliki child sebelum dihapus
        if ($office->children()->count() > 0) {
            return redirect()->route('offices.index')->with('error', 'Cannot delete office with sub-offices. Please delete sub-offices first.');
        }

        // Cek apakah office memiliki outlet sebelum dihapus
        if ($office->outlets()->count() > 0) {
            return redirect()->route('offices.index')->with('error', 'Cannot delete office with outlets. Please delete outlets first.');
        }

        // Log office deletion activity
        $oldData = $office->toArray();
        $this->activityLogService->logOfficeDeleted($oldData);

        $office->delete();

        return redirect()->route('offices.index')->with('success', 'Office deleted successfully.');
    }

    /**
     * Export offices to Excel.
     */
    public function export()
    {
        $this->authorize('viewAny', Office::class);

        return Excel::download(new OfficeExport, 'offices_' . date('Y-m-d_H-i-s') . '.xlsx');
    }
}