<?php

namespace App\Http\Controllers;

use App\Models\Moda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogService;
use App\Http\Requests\CreateModaRequest;
use App\Http\Requests\UpdateModaRequest;

class ModaController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Only super admin, admin wilayah, and admin area can view modas
        if (!($user->isSuperAdmin() || $user->isAdminWilayah() || $user->isAdminArea())) {
            abort(403, 'Unauthorized access.');
        }

        $modas = Moda::paginate(10);

        return view('modas.index', compact('modas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Only super admin, admin wilayah, and admin area can create modas
        if (!($user->isSuperAdmin() || $user->isAdminWilayah() || $user->isAdminArea())) {
            abort(403, 'Unauthorized access.');
        }

        return view('modas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateModaRequest $request)
    {
        $user = Auth::user();

        // Authorization is handled in the Form Request
        $validatedData = $request->validated();

        $moda = Moda::create($validatedData);

        // Log moda creation activity
        $this->activityLogService->logActivity(
            action: 'create',
            module: 'moda',
            description: 'Moda created',
            newValues: [
                'name' => $request->name,
                'description' => $request->description,
            ]
        );

        return redirect()->route('modas.index')->with('success', 'Moda created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Moda $moda)
    {
        $user = Auth::user();

        // Only super admin, admin wilayah, and admin area can view modas
        if (!($user->isSuperAdmin() || $user->isAdminWilayah() || $user->isAdminArea())) {
            abort(403, 'Unauthorized access.');
        }

        return view('modas.show', compact('moda'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Moda $moda)
    {
        $user = Auth::user();

        // Only super admin, admin wilayah, and admin area can edit modas
        if (!($user->isSuperAdmin() || $user->isAdminWilayah() || $user->isAdminArea())) {
            abort(403, 'Unauthorized access.');
        }

        return view('modas.edit', compact('moda'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateModaRequest $request, Moda $moda)
    {
        $user = Auth::user();

        // Authorization is handled in the Form Request
        $validatedData = $request->validated();

        // Log moda update activity
        $oldData = $moda->toArray();
        $newData = array_merge($oldData, $validatedData);

        $moda->update($validatedData);

        $this->activityLogService->logActivity(
            action: 'update',
            module: 'moda',
            description: 'Moda updated',
            oldValues: $oldData,
            newValues: $newData
        );

        return redirect()->route('modas.index')->with('success', 'Moda updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Moda $moda)
    {
        $user = Auth::user();

        // Only super admin, admin wilayah, and admin area can delete modas
        if (!($user->isSuperAdmin() || $user->isAdminWilayah() || $user->isAdminArea())) {
            abort(403, 'Unauthorized access.');
        }

        // Check if moda has associated daily incomes
        if ($moda->dailyIncomes()->count() > 0) {
            return redirect()->route('modas.index')->with('error', 'Cannot delete moda with associated daily incomes.');
        }

        // Log moda deletion activity
        $this->activityLogService->logActivity(
            action: 'delete',
            module: 'moda',
            description: 'Moda deleted',
            oldValues: $moda->toArray()
        );

        $moda->delete();

        return redirect()->route('modas.index')->with('success', 'Moda deleted successfully.');
    }
}