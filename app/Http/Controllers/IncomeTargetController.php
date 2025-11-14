<?php

namespace App\Http\Controllers;

use App\Models\IncomeTarget;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogService;
use App\Http\Requests\CreateIncomeTargetRequest;
use App\Http\Requests\UpdateIncomeTargetRequest;

class IncomeTargetController extends Controller
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

        // Only authorized users can view targets
        $this->authorize('viewAny', IncomeTarget::class);

        // Build query
        $query = IncomeTarget::with(['outlet', 'moda', 'assignedBy']);

        // Apply user-based access control
        if ($user->isAdminWilayah()) {
            // Admin wilayah can only see targets for outlets in their wilayah
            $outletIds = \App\Models\Outlet::whereHas('office', function($q) use ($user) {
                $q->where('parent_id', $user->office_id)
                  ->orWhere('id', $user->office_id);
            })->pluck('id');

            $query->whereIn('outlet_id', $outletIds);
        } elseif ($user->isAdminArea()) {
            // Admin area can only see targets for outlets in their area
            $outletIds = $user->office->outlets()->pluck('id');
            $query->whereIn('outlet_id', $outletIds);
        }

        // Apply filters
        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        if ($request->filled('moda_id')) {
            $query->where('moda_id', $request->moda_id);
        }

        if ($request->filled('year')) {
            $query->where('target_year', $request->year);
        }

        if ($request->filled('month')) {
            $query->where('target_month', $request->month);
        }

        $targets = $query->orderBy('target_year', 'desc')
                        ->orderBy('target_month', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->paginate(15)
                        ->appends($request->query());

        // Get outlets for filter dropdown (based on user's access)
        $outlets = collect();
        if ($user->isAdminWilayah()) {
            $outlets = \App\Models\Outlet::whereHas('office', function($q) use ($user) {
                $q->where('parent_id', $user->office_id)
                  ->orWhere('id', $user->office_id);
            })->with('office')->get();
        } elseif ($user->isAdminArea()) {
            $outlets = $user->office->outlets;
        } elseif ($user->isSuperAdmin()) {
            $outlets = \App\Models\Outlet::with('office')->get();
        }

        // Get modas
        $modas = \App\Models\Moda::all();

        return view('income-targets.index', compact('targets', 'outlets', 'modas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Only authorized users can create targets
        $this->authorize('create', IncomeTarget::class);

        // Get outlets based on user's access level
        $outlets = collect();
        if ($user->isAdminWilayah()) {
            $outlets = \App\Models\Outlet::whereHas('office', function($q) use ($user) {
                $q->where('parent_id', $user->office_id)
                  ->orWhere('id', $user->office_id);
            })->with('office')->get();
        } elseif ($user->isAdminArea()) {
            $outlets = $user->office->outlets;
        } elseif ($user->isSuperAdmin()) {
            $outlets = \App\Models\Outlet::with('office')->get();
        }

        // Get modas
        $modas = \App\Models\Moda::all();

        return view('income-targets.create', compact('outlets', 'modas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateIncomeTargetRequest $request)
    {
        $user = Auth::user();

        // Always expect entries format now that the form has been updated
        $entries = $request->input('entries', []);

        // If there are no entries in the new format, check if old format was sent (for backward compatibility)
        if (empty($entries)) {
            // Try to create from old format (shouldn't happen with the new form, but just in case)
            $validatedData = $request->validated();

            // Check if a target already exists for the same outlet, moda, and month
            $existingTarget = IncomeTarget::where('outlet_id', $validatedData['outlet_id'] ?? $request->outlet_id)
                                         ->where('moda_id', $validatedData['moda_id'] ?? $request->moda_id)
                                         ->where('target_year', $validatedData['target_year'] ?? $request->target_year)
                                         ->where('target_month', $validatedData['target_month'] ?? $request->target_month)
                                         ->first();

            if ($existingTarget) {
                return back()->withErrors(['outlet_id' => 'Target already exists for this outlet, moda, and month.']);
            }

            $target = IncomeTarget::create(array_merge($validatedData, [
                'assigned_by' => $user->id,
            ]));

            // Log target creation activity
            $this->activityLogService->logActivity(
                action: 'create',
                module: 'income_target',
                description: 'Income target created',
                newValues: [
                    'outlet_id' => $validatedData['outlet_id'] ?? $request->outlet_id,
                    'moda_id' => $validatedData['moda_id'] ?? $request->moda_id,
                    'target_year' => $validatedData['target_year'] ?? $request->target_year,
                    'target_month' => $validatedData['target_month'] ?? $request->target_month,
                    'target_amount' => $validatedData['target_amount'] ?? $request->target_amount,
                ]
            );

            return redirect()->route('income-targets.index')->with('success', 'Income target created successfully.');
        } else {
            // Process multiple entries
            $createdRecords = 0;

            foreach ($entries as $index => $entry) {
                // Validate each entry individually to provide better error messages
                $entryValidation = [
                    'outlet_id' => ['required', 'exists:outlets,id'],
                    'moda_id' => ['required', 'exists:modas,id'],
                    'target_year' => ['required', 'integer', 'min:2000', 'max:2100'],
                    'target_month' => ['required', 'integer', 'min:1', 'max:12'],
                    'target_amount' => ['required', 'numeric', 'min:0'],
                    'description' => ['nullable', 'string', 'max:500'],
                ];

                $validator = \Validator::make($entry, $entryValidation);
                if ($validator->fails()) {
                    return back()->withErrors($validator->errors())->withInput();
                }

                $validatedEntry = $validator->validated();

                // Check if a target already exists for the same outlet, moda, and month
                $existingTarget = IncomeTarget::where('outlet_id', $validatedEntry['outlet_id'])
                                             ->where('moda_id', $validatedEntry['moda_id'])
                                             ->where('target_year', $validatedEntry['target_year'])
                                             ->where('target_month', $validatedEntry['target_month'])
                                             ->first();

                if ($existingTarget) {
                    return back()->withErrors([
                        'entries' => "Target already exists for outlet ID {$validatedEntry['outlet_id']}, moda ID {$validatedEntry['moda_id']}, and month {$validatedEntry['target_month']}/{$validatedEntry['target_year']}."
                    ])->withInput();
                }

                $target = IncomeTarget::create(array_merge($validatedEntry, [
                    'assigned_by' => $user->id,
                ]));

                // Log target creation activity for each entry
                $this->activityLogService->logActivity(
                    action: 'create',
                    module: 'income_target',
                    description: 'Income target created',
                    newValues: [
                        'outlet_id' => $validatedEntry['outlet_id'],
                        'moda_id' => $validatedEntry['moda_id'],
                        'target_year' => $validatedEntry['target_year'],
                        'target_month' => $validatedEntry['target_month'],
                        'target_amount' => $validatedEntry['target_amount'],
                    ]
                );

                $createdRecords++;
            }

            $message = $createdRecords > 1
                ? "{$createdRecords} income targets created successfully."
                : "Income target created successfully.";

            return redirect()->route('income-targets.index')->with('success', $message);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(IncomeTarget $incomeTarget)
    {
        $this->authorize('view', $incomeTarget);

        return view('income-targets.show', compact('incomeTarget'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IncomeTarget $incomeTarget)
    {
        // Load the outlet relationship to avoid lazy loading issues
        $incomeTarget->load('outlet');

        $user = Auth::user();

        // Only authorized users can edit targets
        $this->authorize('update', $incomeTarget);

        // Get outlets based on user's access level
        $outlets = collect();
        if ($user->isAdminWilayah()) {
            $outlets = \App\Models\Outlet::whereHas('office', function($q) use ($user) {
                $q->where('parent_id', $user->office_id)
                  ->orWhere('id', $user->office_id);
            })->with('office')->get();
        } elseif ($user->isAdminArea()) {
            $outlets = $user->office->outlets;
        } elseif ($user->isSuperAdmin()) {
            $outlets = \App\Models\Outlet::with('office')->get();
        }

        // Get modas
        $modas = \App\Models\Moda::all();

        return view('income-targets.edit', compact('incomeTarget', 'outlets', 'modas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIncomeTargetRequest $request, IncomeTarget $incomeTarget)
    {
        $user = Auth::user();

        // Load the outlet relationship to avoid lazy loading issues
        $incomeTarget->load('outlet');

        // Perform authorization check here
        $this->authorize('update', $incomeTarget);

        // Get validated data - cleaning is handled in the Form Request
        $validatedData = $request->validated();

        // Check if changing outlet/moda and a target already exists for the new combination
        if ($incomeTarget->outlet_id != $request->outlet_id ||
            $incomeTarget->moda_id != $request->moda_id ||
            $incomeTarget->target_year != $request->target_year ||
            $incomeTarget->target_month != $request->target_month) {

            $existingTarget = IncomeTarget::where('outlet_id', $request->outlet_id)
                                         ->where('moda_id', $request->moda_id)
                                         ->where('target_year', $request->target_year)
                                         ->where('target_month', $request->target_month)
                                         ->where('id', '!=', $incomeTarget->id)
                                         ->first();

            if ($existingTarget) {
                return back()->withErrors(['outlet_id' => 'Target already exists for this outlet, moda, and month.']);
            }
        }

        // Log target update activity
        $oldData = $incomeTarget->toArray();
        $newData = $validatedData;

        $incomeTarget->update($validatedData);

        $this->activityLogService->logActivity(
            action: 'update',
            module: 'income_target',
            description: 'Income target updated',
            oldValues: $oldData,
            newValues: $newData
        );

        return redirect()->route('income-targets.index')->with('success', 'Income target updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IncomeTarget $incomeTarget)
    {
        $user = Auth::user();

        // Only authorized users can delete targets
        $this->authorize('delete', $incomeTarget);

        // Log target deletion activity
        $this->activityLogService->logActivity(
            action: 'delete',
            module: 'income_target',
            description: 'Income target deleted',
            oldValues: $incomeTarget->toArray()
        );

        $incomeTarget->delete();

        return redirect()->route('income-targets.index')->with('success', 'Income target deleted successfully.');
    }
}