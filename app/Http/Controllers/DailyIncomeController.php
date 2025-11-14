<?php

namespace App\Http\Controllers;

use App\Models\DailyIncome;
use App\Services\ActivityLogService;
use App\Http\Requests\CreateDailyIncomeRequest;
use App\Http\Requests\UpdateDailyIncomeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyIncomeController extends Controller
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

        // Only admin outlet can access their own daily incomes
        if ($user->isAdminOutlet()) {
            $query = DailyIncome::where('outlet_id', $user->outlet_id);
        } else {
            // Admin area can see incomes for their outlets
            if ($user->isAdminArea()) {
                $outletIds = $user->office->outlets()->pluck('id');
                $query = DailyIncome::whereIn('outlet_id', $outletIds);
            } 
            // Admin wilayah can see incomes for their area
            elseif ($user->isAdminWilayah()) {
                $outletIds = \App\Models\Outlet::whereHas('office', function($q) use ($user) {
                    $q->where('parent_id', $user->office_id)
                      ->orWhere('id', $user->office_id);
                })->pluck('id');
                $query = DailyIncome::whereIn('outlet_id', $outletIds);
            } 
            // Super admin can see all
            else {
                $query = DailyIncome::query();
            }
        }

        // Add search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('moda', function($modaQuery) use ($search) {
                    $modaQuery->where('name', 'LIKE', "%{$search}%");
                })
                  ->orWhere('date', 'LIKE', "%{$search}%")
                  ->orWhere('income', 'LIKE', "%{$search}%");
            });
        }

        // Add date range filter
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $dailyIncomes = $query->with(['outlet', 'user', 'moda'])->latest()->paginate(10)->appends($request->query());

        return view('daily-incomes.index', compact('dailyIncomes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Only admin outlet can create daily income
        if (!$user->isAdminOutlet()) {
            abort(403, 'Unauthorized access.');
        }

        $modas = \App\Models\Moda::all();

        return view('daily-incomes.create', compact('modas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Authorization check
        if (!$user->isAdminOutlet()) {
            abort(403, 'Unauthorized access.');
        }

        // Get entries from request
        $entries = $request->input('entries', []);
        
        // Debug: Log the request data to see what's being sent
        \Log::info('Daily Income Store Request Data:', [
            'request_all' => $request->all(),
            'entries' => $entries,
        ]);

        // If no dynamic entries, this might be the old format - try to handle it
        if (empty($entries)) {
            // Validate single entry format
            $validatedData = $request->validate([
                'date' => ['required', 'date'],
                'moda_id' => ['required', 'exists:modas,id'],
                'colly' => ['required', 'integer', 'min:0'],
                'weight' => ['required', 'numeric', 'min:0'],
                'income' => ['required', 'numeric', 'min:0'],
            ]);

            $dailyIncome = DailyIncome::create([
                'date' => $validatedData['date'],
                'moda_id' => $validatedData['moda_id'],
                'colly' => $validatedData['colly'],
                'weight' => $validatedData['weight'],
                'income' => $validatedData['income'],
                'outlet_id' => $user->outlet_id,
                'user_id' => $user->id,
            ]);

            // Log daily income creation activity
            $this->activityLogService->logActivity(
                action: 'create',
                module: 'daily_income',
                description: 'Daily income recorded',
                newValues: [
                    'date' => $validatedData['date'],
                    'moda_id' => $validatedData['moda_id'],
                    'colly' => $validatedData['colly'],
                    'weight' => $validatedData['weight'],
                    'income' => $validatedData['income'],
                ]
            );

            return redirect()->route('daily-incomes.index')->with('success', 'Daily income recorded successfully.');
        }

        // Validate multiple entries format
        $request->validate([
            'date' => ['required', 'date'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.moda_id' => ['required', 'exists:modas,id'],
            'entries.*.colly' => ['required', 'integer', 'min:0'],
            'entries.*.weight' => ['required', 'numeric', 'min:0'],
            'entries.*.income' => ['required', 'numeric', 'min:0'],
        ]);

        // Create multiple daily income records
        $createdRecords = 0;
        foreach ($entries as $entry) {
            \Log::info('Creating daily income record:', $entry);
            
            $dailyIncome = DailyIncome::create([
                'date' => $request->date, // Use the main date
                'moda_id' => $entry['moda_id'],
                'colly' => $entry['colly'],
                'weight' => $entry['weight'],
                'income' => $entry['income'],
                'outlet_id' => $user->outlet_id,
                'user_id' => $user->id,
            ]);

            $createdRecords++;
        }

        // Log daily income creation activity
        $this->activityLogService->logActivity(
            action: 'create',
            module: 'daily_income',
            description: 'Daily income records created (' . $createdRecords . ' entries)',
            newValues: [
                'date' => $request->date,
                'entries_count' => $createdRecords,
                'total_income' => collect($entries)->sum('income'),
            ]
        );

        $message = $createdRecords > 1 
            ? "{$createdRecords} daily income records created successfully." 
            : "Daily income record created successfully.";

        return redirect()->route('daily-incomes.index')->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyIncome $dailyIncome)
    {
        $user = Auth::user();

        // Check if user has access to this daily income
        if (!$user->isSuperAdmin() && 
            !$user->hasOutletAccess($dailyIncome->outlet) && 
            !$user->hasOfficeAccess($dailyIncome->outlet->office)) {
            abort(403, 'Unauthorized access.');
        }

        return view('daily-incomes.show', compact('dailyIncome'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyIncome $dailyIncome)
    {
        $user = Auth::user();

        // Only admin outlet can edit their own records
        if (!$user->isAdminOutlet() || $dailyIncome->outlet_id !== $user->outlet_id) {
            abort(403, 'Unauthorized access.');
        }

        $modas = \App\Models\Moda::all();

        return view('daily-incomes.edit', compact('dailyIncome', 'modas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDailyIncomeRequest $request, DailyIncome $dailyIncome)
    {
        $user = Auth::user();

        // Authorization is handled in the Form Request
        $validatedData = $request->validated();

        $oldData = $dailyIncome->toArray();

        $dailyIncome->update($validatedData);

        $newData = $dailyIncome->toArray();

        // Log daily income update activity
        $this->activityLogService->logActivity(
            action: 'update',
            module: 'daily_income',
            description: 'Daily income updated',
            oldValues: $oldData,
            newValues: $newData
        );

        return redirect()->route('daily-incomes.index')->with('success', 'Daily income updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyIncome $dailyIncome)
    {
        $user = Auth::user();

        // Only admin outlet can delete their own records
        if (!$user->isAdminOutlet() || $dailyIncome->outlet_id !== $user->outlet_id) {
            abort(403, 'Unauthorized access.');
        }

        // Log daily income deletion activity
        $this->activityLogService->logActivity(
            action: 'delete',
            module: 'daily_income',
            description: 'Daily income deleted',
            oldValues: $dailyIncome->toArray()
        );

        $dailyIncome->delete();

        return redirect()->route('daily-incomes.index')->with('success', 'Daily income deleted successfully.');
    }
}