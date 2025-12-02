<?php

namespace App\Http\Controllers;

use App\Models\DailyIncome;
use App\Services\ActivityLogService;
use App\Http\Requests\CreateDailyIncomeRequest;
use App\Http\Requests\UpdateDailyIncomeRequest;
use App\Imports\DailyIncomeImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

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
            $query = DailyIncome::with(['outlet', 'moda', 'user'])->where('outlet_id', $user->outlet_id);
        } else {
            // Admin area can see incomes for their outlets
            if ($user->isAdminArea()) {
                $outletIds = $user->office->outlets()->pluck('id');
                $query = DailyIncome::with(['outlet', 'moda', 'user'])->whereIn('outlet_id', $outletIds);
            }
            // Admin wilayah can see incomes for their area
            elseif ($user->isAdminWilayah()) {
                $outletIds = \App\Models\Outlet::whereHas('office', function($q) use ($user) {
                    $q->where('parent_id', $user->office_id)
                      ->orWhere('id', $user->office_id);
                })->pluck('id');
                $query = DailyIncome::with(['outlet', 'moda', 'user'])->whereIn('outlet_id', $outletIds);
            }
            // Super admin can see all
            else {
                $query = DailyIncome::with(['outlet', 'moda', 'user']);
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

    /**
     * Show the import form for daily incomes
     */
    public function showImportForm()
    {
        $user = Auth::user();

        // Only super admin, admin wilayah, and admin outlet can import daily incomes
        if (!$user->isSuperAdmin() && !$user->isAdminWilayah() && !$user->isAdminOutlet()) {
            abort(403, 'Unauthorized access. Only super admin, admin wilayah, and admin outlet can import daily incomes.');
        }

        return view('import.daily-income');
    }

    /**
     * Import daily incomes from Excel file
     */
    public function import(Request $request)
    {
        $user = Auth::user();

        // Only super admin, admin wilayah, and admin outlet can import daily incomes
        if (!$user->isSuperAdmin() && !$user->isAdminWilayah() && !$user->isAdminOutlet()) {
            abort(403, 'Unauthorized access. Only super admin, admin wilayah, and admin outlet can import daily incomes.');
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // max 10MB
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Initialize import progress service
            $importProgressService = app(\App\Services\ImportProgressService::class);
            $jobId = $importProgressService->generateJobId();

            $userOutletId = null;
            // If user is admin outlet, restrict import to their outlet only
            if ($user->isAdminOutlet()) {
                $userOutletId = $user->outlet_id;
            }

            // Use PhpSpreadsheet directly since Laravel Excel has issues reading the file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('file')->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row (first row)
            $originalHeader = array_shift($rows);

            // Normalize header keys to lowercase and map to expected field names
            $headerMap = [
                'date' => ['date', 'tanggal'],
                'outlet code' => ['outlet code', 'outlet_code', 'outletcode'],
                'moda name' => ['moda name', 'moda_name', 'modaname', 'moda'],
                'colly' => ['colly'],
                'weight' => ['weight', 'berat'],
                'income' => ['income', 'pendapatan', 'total']
            ];

            $header = [];
            foreach ($originalHeader as $col) {
                $normalizedCol = trim(strtolower($col));

                // Find the appropriate mapped header
                $mappedHeader = $normalizedCol; // default
                foreach ($headerMap as $expected => $possibleValues) {
                    if (in_array($normalizedCol, $possibleValues)) {
                        $mappedHeader = $expected;
                        break;
                    }
                }

                $header[] = $mappedHeader;
            }

            $totalRows = count($rows);

            // Initialize progress
            $importProgressService->setProgress($jobId, [
                'total_rows' => $totalRows,
                'processed_rows' => 0,
                'successful_imports' => 0,
                'failed_imports' => 0,
                'status' => 'in_progress',
                'message' => 'Starting import process...'
            ]);

            $successCount = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                // Update progress
                $importProgressService->setProgress($jobId, [
                    'total_rows' => $totalRows,
                    'processed_rows' => $index + 1,
                    'successful_imports' => $successCount,
                    'failed_imports' => count($errors),
                    'status' => 'in_progress',
                    'message' => "Processing row " . ($index + 1) . " of {$totalRows}..."
                ]);
                // Create associative array with header as keys
                $rowData = array_combine($header, $row);

                // Normalize values to ensure they are strings
                $rowData = array_map(function($value) {
                    return is_null($value) ? '' : trim($value);
                }, $rowData);

                // Pre-process date value to remove potential whitespace
                if (isset($rowData['date'])) {
                    $rowData['date'] = trim($rowData['date']);
                }

                // Validate the row data
                $validationRules = [
                    'date' => 'required|date',
                    'moda name' => 'required|string|exists:modas,name',
                    'colly' => 'required|numeric|min:0',  // Changed from integer to numeric to allow decimal values
                    'weight' => 'required|numeric|min:0',
                    'income' => 'required|numeric|min:0',
                ];

                // Only require outlet code if user has access to multiple outlets (not admin outlet)
                if (!$userOutletId) {
                    $validationRules['outlet code'] = 'required|string|exists:outlets,code';
                }

                $validator = Validator::make($rowData, $validationRules);

                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $error) {
                        $errors[] = [
                            'row' => $index + 2, // +2 karena baris pertama adalah header
                            'error' => $error
                        ];
                    }
                    continue;
                }

                $validated = $validator->validated();

                // Convert date to Y-m-d format if needed
                $dateValue = $validated['date'];
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
                    // Try to parse the date using Carbon
                    try {
                        $dateValue = \Carbon\Carbon::parse($dateValue)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $errors[] = [
                            'row' => $index + 2,
                            'error' => 'Invalid date format: ' . $validated['date']
                        ];
                        continue;
                    }
                }

                // Determine outlet based on user type
                if ($userOutletId) {
                    // Admin outlet - use their outlet automatically
                    $outlet = \App\Models\Outlet::find($userOutletId);
                    if (!$outlet) {
                        $errors[] = [
                            'row' => $index + 2,
                            'error' => 'Your outlet does not exist'
                        ];
                        continue;
                    }
                } else {
                    // Super admin/admin wilayah - use outlet from Excel file
                    $outlet = \App\Models\Outlet::where('code', $validated['outlet code'])->first();
                    if (!$outlet) {
                        $errors[] = [
                            'row' => $index + 2,
                            'error' => 'Outlet with code ' . $validated['outlet code'] . ' not found'
                        ];
                        continue;
                    }
                }

                // Get moda by name
                $moda = \App\Models\Moda::where('name', $validated['moda name'])->first();
                if (!$moda) {
                    $errors[] = [
                        'row' => $index + 2,
                        'error' => 'Moda with name ' . $validated['moda name'] . ' not found'
                    ];
                    continue;
                }

                // Check if daily income already exists for the same date, outlet, and moda
                $existingIncome = \App\Models\DailyIncome::where('date', $dateValue)
                                           ->where('outlet_id', $outlet->id)
                                           ->where('moda_id', $moda->id)
                                           ->first();

                if ($existingIncome) {
                    $errors[] = [
                        'row' => $index + 2,
                        'error' => 'Daily income already exists for date ' . $dateValue . ', outlet ' . $outlet->name . ', and moda ' . $moda->name
                    ];
                    continue;
                }

                // Create the daily income
                \App\Models\DailyIncome::create([
                    'date' => $dateValue,
                    'outlet_id' => $outlet->id,
                    'moda_id' => $moda->id,
                    'colly' => $validated['colly'],
                    'weight' => $validated['weight'],
                    'income' => $validated['income'],
                    'user_id' => $user->id, // Set the current user as the creator
                ]);

                $successCount++;
            }

            // Log import activity
            $this->activityLogService->logActivity(
                action: 'import',
                module: 'daily_income',
                description: 'Daily income records imported',
                newValues: [
                    'total_records_imported' => $successCount,
                    'total_errors' => count($errors),
                ]
            );

            // Finalize progress
            $importProgressService->setProgress($jobId, [
                'total_rows' => $totalRows,
                'processed_rows' => $totalRows,
                'successful_imports' => $successCount,
                'failed_imports' => count($errors),
                'status' => 'completed',
                'message' => "Import completed. {$successCount} records imported, " . count($errors) . " errors."
            ]);

            $message = "Import completed successfully. {$successCount} records imported.";

            if (!empty($errors)) {
                $message .= " " . count($errors) . " records had errors.";
                return redirect()->route('import.daily-income.form')
                    ->with('import_errors', $errors)
                    ->with('success', $message);
            }

            return redirect()->route('daily-incomes.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Daily Income Import Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get import progress status via AJAX
     */
    public function getImportProgress(Request $request)
    {
        $jobId = $request->query('job_id');

        if (!$jobId) {
            return response()->json(['error' => 'Job ID is required'], 400);
        }

        $importProgressService = app(\App\Services\ImportProgressService::class);
        $progress = $importProgressService->getProgress($jobId);

        // Calculate percentage
        $percentage = 0;
        if ($progress['total_rows'] > 0) {
            $percentage = ($progress['processed_rows'] / $progress['total_rows']) * 100;
        }

        $progress['percentage'] = round($percentage, 2);

        return response()->json($progress);
    }

    /**
     * Download import template for daily incomes
     */
    public function downloadImportTemplate()
    {
        $user = Auth::user();

        // Only super admin, admin wilayah, and admin outlet can download the template
        if (!$user->isSuperAdmin() && !$user->isAdminWilayah() && !$user->isAdminOutlet()) {
            abort(403, 'Unauthorized access. Only super admin, admin wilayah, and admin outlet can download the template.');
        }

        $headers = [];
        $sampleData = [];

        if ($user->isAdminOutlet()) {
            // For admin outlet: don't require outlet code, use their outlet automatically
            $headers = ['Date', 'Moda Name', 'Colly', 'Weight', 'Income'];
            $sampleData = [
                ['Date' => '2025-01-15', 'Moda Name' => 'Darat', 'Colly' => 10, 'Weight' => 100.5, 'Income' => 5000000],
                ['Date' => '2025-01-16', 'Moda Name' => 'Laut', 'Colly' => 5, 'Weight' => 200.0, 'Income' => 3000000],
            ];
        } else {
            // For super admin and admin wilayah: require outlet code
            $headers = ['Date', 'Outlet Code', 'Moda Name', 'Colly', 'Weight', 'Income'];
            $sampleData = [
                ['Date' => '2025-01-15', 'Outlet Code' => 'OUT001', 'Moda Name' => 'Darat', 'Colly' => 10, 'Weight' => 100.5, 'Income' => 5000000],
                ['Date' => '2025-01-15', 'Outlet Code' => 'OUT002', 'Moda Name' => 'Laut', 'Colly' => 5, 'Weight' => 200.0, 'Income' => 3000000],
            ];
        }

        return response()->streamDownload(function () use ($headers, $sampleData) {
            $handle = fopen('php://output', 'w');

            // Write header
            fputcsv($handle, $headers);

            // Write sample data
            foreach ($sampleData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'daily_income_import_template.csv');
    }
}