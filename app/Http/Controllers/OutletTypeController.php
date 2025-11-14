<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\OutletType;
use App\Services\ActivityLogService;
use App\Exports\OutletTypeExport;
use App\Http\Requests\CreateOutletTypeRequest;
use App\Http\Requests\UpdateOutletTypeRequest;
use Maatwebsite\Excel\Facades\Excel;

class OutletTypeController extends Controller
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
        // Query outlet types
        $query = OutletType::query();

        // Tambahkan pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
        }

        $outletTypes = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());

        return view('outlet-types.index', compact('outletTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', OutletType::class);

        return view('outlet-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateOutletTypeRequest $request)
    {
        $this->authorize('create', OutletType::class);

        $validatedData = $request->validated();

        $outletType = OutletType::create($validatedData);

        // Log outlet type creation activity
        $this->activityLogService->logOutletTypeCreated([
            'name' => $request->name,
        ]);

        return redirect()->route('outlet-types.index')->with('success', 'Outlet type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(OutletType $outletType)
    {
        $this->authorize('view', $outletType);

        return view('outlet-types.show', compact('outletType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OutletType $outletType)
    {
        $this->authorize('update', $outletType);

        return view('outlet-types.edit', compact('outletType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OutletType $outletType)
    {
        $this->authorize('update', $outletType);

        $validatedData = $request->validated();

        // Log outlet type update activity
        $oldData = $outletType->toArray();
        $newData = array_merge($oldData, $validatedData);

        $outletType->update($validatedData);

        $this->activityLogService->logOutletTypeUpdated($oldData, $newData);

        return redirect()->route('outlet-types.index')->with('success', 'Outlet type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OutletType $outletType)
    {
        $this->authorize('delete', $outletType);

        // Cek apakah outlet type memiliki outlet yang terkait
        if ($outletType->outlets()->count() > 0) {
            return redirect()->route('outlet-types.index')->with('error', 'Cannot delete outlet type with associated outlets. Please update outlets first.');
        }

        // Log outlet type deletion activity
        $oldData = $outletType->toArray();
        $this->activityLogService->logOutletTypeDeleted($oldData);

        $outletType->delete();

        return redirect()->route('outlet-types.index')->with('success', 'Outlet type deleted successfully.');
    }

    /**
     * Export outlet types to Excel.
     */
    public function export()
    {
        $this->authorize('viewAny', OutletType::class);

        return Excel::download(new OutletTypeExport, 'outlet_types_' . date('Y-m-d_H-i-s') . '.xlsx');
    }
}