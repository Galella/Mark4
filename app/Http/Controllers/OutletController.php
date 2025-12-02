<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Outlet;
use App\Models\Office;
use App\Models\OutletType;
use App\Services\ActivityLogService;
use App\Exports\OutletExport;
use App\Http\Requests\CreateOutletRequest;
use App\Http\Requests\UpdateOutletRequest;
use Maatwebsite\Excel\Facades\Excel;

class OutletController extends Controller
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

        // Query outlets berdasarkan akses pengguna
        $query = Outlet::query();

        if ($user->isAdminWilayah()) {
            // Admin wilayah hanya bisa melihat outlet di wilayahnya
            $officeIds = $user->office->children()->pluck('id');
            $officeIds->push($user->office->id);
            $query->whereIn('office_id', $officeIds);
        } elseif ($user->isAdminArea()) {
            // Admin area hanya bisa melihat outlet di areanya
            $query->where('office_id', $user->office_id);
        } elseif ($user->isAdminOutlet()) {
            // Admin outlet hanya bisa melihat outletnya sendiri
            $query->where('id', $user->outlet_id);
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
        
        // Tambahkan filter berdasarkan office
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }
        
        // Tambahkan filter berdasarkan tipe outlet
        if ($request->filled('outlet_type_id')) {
            $query->where('outlet_type_id', $request->outlet_type_id);
        }
        
        // Tambahkan filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? true : false);
        }

        $outlets = $query->with(['office', 'outletType'])->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());

        // Ambil offices dan outlet types untuk filter
        $offices = Office::all();
        $outletTypes = OutletType::all();

        // Hitung jumlah outlet berdasarkan tipe
        $outletTypeStats = [];
        foreach ($outletTypes as $type) {
            $countQuery = clone $query;
            $count = $countQuery->where('outlet_type_id', $type->id)->count();
            $outletTypeStats[] = [
                'type' => $type,
                'count' => $count
            ];
        }

        return view('outlets.index', compact('outlets', 'offices', 'outletTypes', 'outletTypeStats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Outlet::class);

        $user = Auth::user();

        // Ambil offices dan outlet types berdasarkan akses pengguna
        if ($user->isAdminWilayah()) {
            // Admin wilayah hanya bisa membuat outlet di wilayahnya
            $offices = Office::where('parent_id', $user->office_id)
                ->orWhere('id', $user->office_id)
                ->where('type', 'area') // Hanya bisa membuat outlet di area
                ->get();
        } elseif ($user->isAdminArea()) {
            // Admin area hanya bisa membuat outlet di areanya sendiri
            $offices = Office::where('id', $user->office_id)->get();
        } elseif ($user->isSuperAdmin()) {
            // Super admin bisa membuat outlet di semua area
            $offices = Office::where('type', 'area')->get();
        } elseif ($user->isAdminOutlet()) {
            // Admin outlet tidak bisa membuat outlet lain
            abort(403, 'Unauthorized access.');
        } else {
            // Jika role tidak dikenali atau tidak memenuhi syarat
            abort(403, 'Unauthorized access.');
        }

        $outletTypes = OutletType::all();

        return view('outlets.create', compact('offices', 'outletTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateOutletRequest $request)
    {
        $this->authorize('create', Outlet::class);

        $user = Auth::user();

        $validatedData = $request->validated();

        // Validasi office_id berdasarkan akses pengguna
        $office = Office::find($request->office_id);
        if (!$office) {
            return back()->withErrors(['office_id' => 'Selected office does not exist.']);
        }

        // Cek akses ke office - for admin area users, they should only be able to select their own office
        if ($user->isAdminArea()) {
            if ($office->id !== $user->office_id) {
                return back()->withErrors(['office_id' => 'You can only create outlets in your assigned office.']);
            }
        } elseif (!$user->isSuperAdmin() && !$user->hasOfficeAccess($office)) {
            return back()->withErrors(['office_id' => 'You do not have access to this office.']);
        }

        // Validasi outlet_type_id
        $outletType = OutletType::find($request->outlet_type_id);
        if (!$outletType) {
            return back()->withErrors(['outlet_type_id' => 'Selected outlet type does not exist.']);
        }

        $outlet = Outlet::create(array_merge($validatedData, [
            'is_active' => $request->filled('is_active'),
        ]));

        // Log outlet creation activity
        $this->activityLogService->logOutletCreated([
            'name' => $validatedData['name'],
            'code' => $validatedData['code'],
            'office_id' => $validatedData['office_id'],
            'outlet_type_id' => $validatedData['outlet_type_id'],
        ]);

        return redirect()->route('outlets.index')->with('success', 'Outlet created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Outlet $outlet)
    {
        $this->authorize('view', $outlet);

        return view('outlets.show', compact('outlet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Outlet $outlet)
    {
        $this->authorize('update', $outlet);

        $user = Auth::user();

        // Ambil offices dan outlet types berdasarkan akses pengguna
        if ($user->isAdminWilayah()) {
            // Admin wilayah hanya bisa mengedit outlet di wilayahnya
            if (!$user->hasOutletAccess($outlet)) {
                abort(403, 'Unauthorized access.');
            }

            $offices = Office::where('parent_id', $user->office_id)
                ->orWhere('id', $user->office_id)
                ->where('type', 'area') // Hanya bisa memilih area office
                ->get();
        } elseif ($user->isAdminArea()) {
            // Admin area hanya bisa mengedit outlet di areanya
            if ($outlet->office_id !== $user->office_id) {
                abort(403, 'Unauthorized access.');
            }

            $offices = Office::where('id', $user->office_id)->get();
        } elseif ($user->isSuperAdmin()) {
            // Super admin bisa mengedit outlet apa saja
            $offices = Office::where('type', 'area')->get();
        } else {
            abort(403, 'Unauthorized access.');
        }

        $outletTypes = OutletType::all();

        return view('outlets.edit', compact('outlet', 'offices', 'outletTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOutletRequest $request, Outlet $outlet)
    {
        $this->authorize('update', $outlet);

        $user = Auth::user();

        $validatedData = $request->validated();

        // Validasi office_id berdasarkan akses pengguna (jika diubah)
        if ($request->filled('office_id')) {
            $office = Office::find($request->office_id);
            if (!$office) {
                return back()->withErrors(['office_id' => 'Selected office does not exist.']);
            }

            // Cek akses ke office
            if (!$user->isSuperAdmin() && !$user->hasOfficeAccess($office)) {
                return back()->withErrors(['office_id' => 'You do not have access to this office.']);
            }
        }

        // Validasi outlet_type_id
        $outletType = OutletType::find($request->outlet_type_id);
        if (!$outletType) {
            return back()->withErrors(['outlet_type_id' => 'Selected outlet type does not exist.']);
        }

        $updateData = $validatedData;

        // Hanya update office_id jika diisi
        if ($request->filled('office_id')) {
            $updateData['office_id'] = $request->office_id;
        } else {
            // Ensure office_id is not overwritten if not provided
            unset($updateData['office_id']);
        }

        // Add is_active based on form input
        $updateData['is_active'] = $request->filled('is_active');

        // Log outlet update activity
        $oldData = $outlet->toArray();
        $newData = array_merge($oldData, $updateData);

        $outlet->update($updateData);

        $this->activityLogService->logOutletUpdated($oldData, $newData);

        return redirect()->route('outlets.index')->with('success', 'Outlet updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Outlet $outlet)
    {
        $this->authorize('delete', $outlet);

        // Cek apakah outlet memiliki user yang terkait
        if ($outlet->users()->count() > 0) {
            return redirect()->route('outlets.index')->with('error', 'Cannot delete outlet with assigned users. Please reassign users first.');
        }

        // Log outlet deletion activity
        $oldData = $outlet->toArray();
        $this->activityLogService->logOutletDeleted($oldData);

        $outlet->delete();

        return redirect()->route('outlets.index')->with('success', 'Outlet deleted successfully.');
    }

    /**
     * Show the import form.
     */
    public function showImportForm()
    {
        $user = Auth::user();

        // Only allow users who can create outlets (all roles except admin outlet)
        $canCreate = $user->can('create', Outlet::class);

        if (!$canCreate) {
            abort(403, 'Unauthorized access to import feature');
        }

        return view('outlets.import');
    }

    /**
     * Import outlets from Excel file.
     */
    public function import(Request $request)
    {
        $user = Auth::user();

        // Only allow users who can create outlets (all roles except admin outlet)
        $canCreate = $user->can('create', Outlet::class);

        if (!$canCreate) {
            abort(403, 'Unauthorized access to import feature');
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // max 10MB
        ]);

        try {
            $file = $request->file('file');

            // Import the file using our custom import class
            $import = new \App\Imports\OutletImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $file);

            $successCount = $import->successCount ?? 0;
            $errors = $import->errors ?? [];

            if (!empty($errors)) {
                return back()->withErrors(['errors' => $errors])
                             ->withInput()
                             ->with('error_count', count($errors))
                             ->with('success_count', $successCount);
            }

            return redirect()->route('outlets.index')
                             ->with('success', "Successfully imported {$successCount} outlet(s) from Excel file.");
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Error importing file: ' . $e->getMessage()]);
        }
    }

    /**
     * Export outlets to Excel.
     */
    public function export()
    {
        $this->authorize('viewAny', Outlet::class);

        return Excel::download(new OutletExport, 'outlets_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Download import template for outlets
     */
    public function downloadImportTemplate()
    {
        $user = Auth::user();

        // Only allow users who can create outlets (all roles except admin outlet)
        $canCreate = $user->can('create', Outlet::class);

        if (!$canCreate) {
            abort(403, 'Unauthorized access. Only users with create permissions can download the template.');
        }

        $headers = [
            'ID',
            'Name',
            'Code',
            'Office Code',      // This matches what the import expects
            'Outlet Type Name', // This matches what the import expects
            'Description',
            'Address',
            'Phone',
            'Email',
            'PIC Name',
            'PIC Phone',
            'is_active',
        ];

        $templateData = [
            $headers,
            [
                '', // ID will be auto-generated
                'Jakarta Pusat',
                'JKT001',
                'GWB-1', // Office code that exists in system (type must be 'area')
                'Reguler', // Outlet type name that exists in system
                'Outlet di Jakarta Pusat',
                'Jl. Sudirman No. 1, Jakarta',
                '02112345678',
                'outlet@example.com',
                'Budi Santoso',
                '081234567890',
                '1', // 1 for active, 0 for inactive (matches column name 'is_active')
            ]
        ];

        $fileName = 'outlet_import_template.csv';
        $filePath = storage_path('app/' . $fileName);

        // Create CSV content
        $file = fopen($filePath, 'w');
        foreach ($templateData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}