<?php

namespace App\Http\Controllers;

use App\Models\DailyIncome;
use App\Models\Outlet;
use App\Models\Moda;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DailyIncomeReportController extends Controller
{
    /**
     * Display daily income report index page
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Determine which outlets the user can access
        $query = DailyIncome::with(['outlet', 'moda', 'user']);

        // Apply user-based access control
        if ($user->isAdminOutlet()) {
            $query->where('outlet_id', $user->outlet_id);
        } else {
            // Admin area can see incomes for their outlets
            if ($user->isAdminArea()) {
                $outletIds = $user->office->outlets()->pluck('id');
                $query->whereIn('outlet_id', $outletIds);
            }
            // Admin wilayah can see incomes for their area
            elseif ($user->isAdminWilayah()) {
                $outletIds = Outlet::whereHas('office', function($q) use ($user) {
                    $q->where('parent_id', $user->office_id)
                      ->orWhere('id', $user->office_id);
                })->pluck('id');
                $query->whereIn('outlet_id', $outletIds);
            }
            // Super admin can see all
            else {
                // No additional filter needed for super admin
            }
        }

        // Apply date range filter
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Apply outlet filter
        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        // Apply moda filter
        if ($request->filled('moda_id')) {
            $query->where('moda_id', $request->moda_id);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('moda', function($modaQuery) use ($search) {
                    $modaQuery->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('outlet', function($outletQuery) use ($search) {
                    $outletQuery->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhere('date', 'LIKE', "%{$search}%")
                ->orWhere('income', 'LIKE', "%{$search}%");
            });
        }

        // Get paginated results for the table
        $dailyIncomes = $query->orderBy('date', 'desc')->paginate(20)->appends($request->query());

        // Get total counts for all records matching the filters (not paginated)
        $totalQuery = clone $query; // Clone the original query to maintain all filters
        $totalRecords = $totalQuery->count();
        $totalIncome = $totalQuery->sum('income');
        $totalColly = $totalQuery->sum('colly');
        $totalWeight = $totalQuery->sum('weight');

        // Get available outlets and modas for the filters
        $outlets = $this->getAvailableOutlets($user);
        $modas = Moda::all();

        return view('reports.daily-income.index', compact('dailyIncomes', 'outlets', 'modas', 'totalIncome', 'totalColly', 'totalWeight', 'totalRecords'));
    }

    /**
     * Generate summary report
     */
    public function summary(Request $request)
    {
        $user = Auth::user();

        // Determine which outlets the user can access
        $query = DailyIncome::with(['outlet', 'moda', 'user']);

        // Apply user-based access control
        if ($user->isAdminOutlet()) {
            $query->where('outlet_id', $user->outlet_id);
        } else {
            // Admin area can see incomes for their outlets
            if ($user->isAdminArea()) {
                $outletIds = $user->office->outlets()->pluck('id');
                $query->whereIn('outlet_id', $outletIds);
            }
            // Admin wilayah can see incomes for their area
            elseif ($user->isAdminWilayah()) {
                $outletIds = Outlet::whereHas('office', function($q) use ($user) {
                    $q->where('parent_id', $user->office_id)
                      ->orWhere('id', $user->office_id);
                })->pluck('id');
                $query->whereIn('outlet_id', $outletIds);
            }
            // Super admin can see all
            else {
                // No additional filter needed for super admin
            }
        }

        // Apply single date filter with default to today if not specified
        $selectedDate = $request->filled('selected_date') ? $request->selected_date : now()->format('Y-m-d');
        if ($selectedDate) {
            $query->whereDate('date', $selectedDate);
        }

        // Apply outlet filter
        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        // Apply moda filter
        if ($request->filled('moda_id')) {
            $query->where('moda_id', $request->moda_id);
        }

        $dailyIncomes = $query->orderBy('date', 'desc')->get();

        // Group data by moda to create summary by moda
        $summaryByModa = $dailyIncomes->groupBy('moda_id')->map(function ($items) {
            return [
                'moda_name' => $items->first()->moda->name,
                'total_colly' => $items->sum('colly'),
                'total_weight' => $items->sum('weight'),
                'total_income' => $items->sum('income'),
                'count' => $items->count()
            ];
        });

        // Calculate overall totals
        $overallTotal = [
            'total_colly' => $dailyIncomes->sum('colly'),
            'total_weight' => $dailyIncomes->sum('weight'),
            'total_income' => $dailyIncomes->sum('income'),
            'count' => $dailyIncomes->count()
        ];

        // Get available outlets and modas for the filters
        $outlets = $this->getAvailableOutlets($user);
        $modas = Moda::all();

        // Format the selected date for display with default to today
        $selectedDateValue = $request->filled('selected_date') ? $request->selected_date : now()->format('Y-m-d');
        $selectedDate = \Carbon\Carbon::parse($selectedDateValue)->locale('id')->translatedFormat('l, d M Y');

        return view('reports.daily-income.summary', compact('summaryByModa', 'overallTotal', 'dailyIncomes', 'outlets', 'modas', 'user', 'selectedDate'));
    }

    /**
     * Export detailed report to Excel
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();

        // Determine which outlets the user can access
        $query = DailyIncome::with(['outlet', 'moda', 'user']);

        // Apply user-based access control
        if ($user->isAdminOutlet()) {
            $query->where('outlet_id', $user->outlet_id);
        } else {
            // Admin area can see incomes for their outlets
            if ($user->isAdminArea()) {
                $outletIds = $user->office->outlets()->pluck('id');
                $query->whereIn('outlet_id', $outletIds);
            }
            // Admin wilayah can see incomes for their area
            elseif ($user->isAdminWilayah()) {
                $outletIds = Outlet::whereHas('office', function($q) use ($user) {
                    $q->where('parent_id', $user->office_id)
                      ->orWhere('id', $user->office_id);
                })->pluck('id');
                $query->whereIn('outlet_id', $outletIds);
            }
            // Super admin can see all
            else {
                // No additional filter needed for super admin
            }
        }

        // Apply date range filter
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Apply outlet filter
        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        // Apply moda filter
        if ($request->filled('moda_id')) {
            $query->where('moda_id', $request->moda_id);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('moda', function($modaQuery) use ($search) {
                    $modaQuery->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('outlet', function($outletQuery) use ($search) {
                    $outletQuery->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhere('date', 'LIKE', "%{$search}%")
                ->orWhere('income', 'LIKE', "%{$search}%");
            });
        }

        $dailyIncomes = $query->orderBy('date', 'desc')->get();

        // Create the export file
        return Excel::download(new class($dailyIncomes) implements FromCollection, WithHeadings, WithMapping {
            private $dailyIncomes;

            public function __construct($dailyIncomes)
            {
                $this->dailyIncomes = $dailyIncomes;
            }

            public function collection()
            {
                return collect($this->dailyIncomes);
            }

            public function headings(): array
            {
                return [
                    'Date',
                    'Outlet',
                    'Moda',
                    'Colly',
                    'Weight',
                    'Income',
                    'Recorded By',
                    'Created At',
                ];
            }

            public function map($dailyIncome): array
            {
                return [
                    \Carbon\Carbon::parse($dailyIncome->date)->format('Y-m-d'),
                    $dailyIncome->outlet->name,
                    $dailyIncome->moda->name,
                    $dailyIncome->colly,
                    $dailyIncome->weight,
                    $dailyIncome->income,
                    $dailyIncome->user->name,
                    $dailyIncome->created_at->format('Y-m-d H:i:s'),
                ];
            }
        }, 'daily_income_report_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Export summary report to Excel
     */
    public function exportSummaryExcel(Request $request)
    {
        $user = Auth::user();

        // Determine which outlets the user can access
        $query = DailyIncome::selectRaw('date, SUM(income) as total_income, COUNT(*) as record_count, GROUP_CONCAT(outlet_id) as outlet_ids');

        // Apply user-based access control
        if ($user->isAdminOutlet()) {
            $query->where('outlet_id', $user->outlet_id);
        } else {
            // Admin area can see incomes for their outlets
            if ($user->isAdminArea()) {
                $outletIds = $user->office->outlets()->pluck('id');
                $query->whereIn('outlet_id', $outletIds);
            }
            // Admin wilayah can see incomes for their area
            elseif ($user->isAdminWilayah()) {
                $outletIds = Outlet::whereHas('office', function($q) use ($user) {
                    $q->where('parent_id', $user->office_id)
                      ->orWhere('id', $user->office_id);
                })->pluck('id');
                $query->whereIn('outlet_id', $outletIds);
            }
            // Super admin can see all
            else {
                // No additional filter needed for super admin
            }
        }

        // Apply date range filter
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Apply outlet filter
        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        // Apply moda filter
        if ($request->filled('moda_id')) {
            $query->where('moda_id', $request->moda_id);
        }

        $summaryData = $query->groupBy('date')->orderBy('date', 'desc')->get();

        // Create the export file
        return Excel::download(new class($summaryData) implements FromCollection, WithHeadings, WithMapping {
            private $summaryData;

            public function __construct($summaryData)
            {
                $this->summaryData = $summaryData;
            }

            public function collection()
            {
                return collect($this->summaryData);
            }

            public function headings(): array
            {
                return [
                    'Date',
                    'Total Income',
                    'Total Records',
                ];
            }

            public function map($summary): array
            {
                return [
                    \Carbon\Carbon::parse($summary->date)->format('Y-m-d'),
                    $summary->total_income,
                    $summary->record_count,
                ];
            }
        }, 'daily_income_summary_report_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Helper method to get outlets based on user access level
     */
    private function getAvailableOutlets($user)
    {
        if ($user->isAdminOutlet()) {
            return Outlet::where('id', $user->outlet_id)->get();
        } elseif ($user->isAdminArea()) {
            return $user->office->outlets;
        } elseif ($user->isAdminWilayah()) {
            return Outlet::whereHas('office', function($q) use ($user) {
                $q->where('parent_id', $user->office_id)
                  ->orWhere('id', $user->office_id);
            })->get();
        } else {
            // Super admin can see all outlets
            return Outlet::all();
        }
    }
}
