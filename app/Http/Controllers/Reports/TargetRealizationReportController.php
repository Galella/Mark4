<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\DailyIncome;
use App\Models\IncomeTarget;
use App\Models\Office;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TargetRealizationReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user has proper access
        if (!$user->isSuperAdmin() && !$user->isAdminWilayah() && !$user->isAdminArea()) {
            abort(403, 'Unauthorized to access this report');
        }

        // Get filter parameters
        $selectedYear = $request->input('year', now()->year);
        // Check if 'month' parameter was provided in the request at all
        $selectedMonth = $request->has('month') ? $request->input('month') : now()->month;
        $selectedOutlet = $request->input('outlet', '');
        $selectedOffice = $request->input('office', '');
        $viewMode = $request->input('view', 'detailed'); // Default to detailed view
        $sortColumn = $request->input('sort', 'progress'); // Default sort by progress
        $sortDirection = $request->input('direction', 'desc'); // Default sort direction is descending

        // Handle the case when "All Months" is selected (empty string)
        $selectedMonthForQuery = empty($selectedMonth) ? null : $selectedMonth;

        // Get outlets based on user access level
        $query = Outlet::query();

        if ($user->isAdminArea()) {
            // Admin area can see their outlets
            $query->where('office_id', $user->office_id);
        } elseif ($user->isAdminWilayah()) {
            // Admin wilayah can see outlets in their wilayah
            $areaIds = $user->office->children()->pluck('id');
            $outletIds = $user->office->outlets()->pluck('id')->merge(
                Outlet::whereIn('office_id', $areaIds)->pluck('id')
            );
            $query->whereIn('id', $outletIds);
        }
        // Super admin can see all outlets

        // Apply filters if provided
        if ($selectedOutlet) {
            $query->where('id', $selectedOutlet);
        }

        if ($selectedOffice) {
            $query->where('office_id', $selectedOffice);
        }

        $outlets = $query->with(['office', 'outletType'])->get();

        // Get offices for filtering dropdown
        $offices = Office::where(function($q) use ($user) {
            if ($user->isAdminArea()) {
                $q->where('id', $user->office_id);
            } elseif ($user->isAdminWilayah()) {
                $q->where('id', $user->office_id)
                  ->orWhere('parent_id', $user->office_id);
            }
        })
        ->orderBy('name')
        ->get();

        // Get all target data based on selected year and month
        $targetQuery = IncomeTarget::whereIn('outlet_id', $outlets->pluck('id'));

        if ($selectedYear) {
            $targetQuery->where('target_year', $selectedYear);
        }

        $targets = $targetQuery->get();

        // Get all realization data based on selected year and month
        $realizationQuery = DailyIncome::whereIn('outlet_id', $outlets->pluck('id'));

        if ($selectedYear) {
            $realizationQuery->whereYear('date', $selectedYear);
        }

        // If a specific month is selected, narrow the date range
        if ($selectedMonthForQuery) {
            $realizationQuery->whereMonth('date', $selectedMonthForQuery);
        }

        $realizations = $realizationQuery->get();

        // Calculate target vs realization data
        $fullReportData = [];

        foreach ($outlets as $outlet) {
            $outletTargets = $targets->where('outlet_id', $outlet->id);
            $outletRealizations = $realizations->where('outlet_id', $outlet->id);

            // Calculate target for selected month and year (or all months if no month specified)
            if ($selectedMonthForQuery) {
                // Calculate target for specific month and year
                $target = $outletTargets->where('target_year', $selectedYear)
                                       ->where('target_month', $selectedMonthForQuery)
                                       ->sum('target_amount');
            } else {
                // Calculate target for all months in the selected year
                $target = $outletTargets->where('target_year', $selectedYear)
                                       ->sum('target_amount');
            }

            // Calculate realization for selected month and year (or all months if no month specified)
            $realization = $outletRealizations->sum('income');

            // Calculate progress percentage
            $progressPercentage = $target > 0 ? ($realization / $target) * 100 : 0;

            $fullReportData[] = [
                'outlet_name' => $outlet->name,
                'outlet_type' => $outlet->outletType->name ?? 'N/A',
                'office_name' => $outlet->office->name ?? 'N/A',
                'target' => $target,
                'realization' => $realization,
                'progress' => $progressPercentage,
                'status' => $progressPercentage >= 100 ? 'Achieved' : ($progressPercentage >= 80 ? 'On Track' : 'Below Target')
            ];
        }

        // Calculate dashboard KPIs when in dashboard mode
        $dashboardData = null;
        if ($viewMode === 'dashboard') {
            // Calculate KPIs for dashboard view
            $totalActiveOutlets = $outlets->count();
            $totalIncome = $realizations->sum('income');
            $totalTarget = $targets->sum('target_amount');
            $avgAchievementRate = $totalTarget > 0 ? ($totalIncome / $totalTarget) * 100 : 0;
            $totalColly = $realizations->sum('colly');
            $totalWeight = $realizations->sum('weight');

            // Calculate outlets on track (achievement > 80%)
            $outletsOnTrack = 0;
            foreach ($outlets as $outlet) {
                $outletIncomes = $realizations->where('outlet_id', $outlet->id);
                $outletTargets = $targets->where('outlet_id', $outlet->id);

                // Calculate target for selected month and year (or all months if no month specified)
                if ($selectedMonthForQuery) {
                    // Calculate target for specific month and year
                    $outletTarget = $outletTargets->where('target_year', $selectedYear)
                                                   ->where('target_month', $selectedMonthForQuery)
                                                   ->sum('target_amount');
                } else {
                    // Calculate target for all months in the selected year
                    $outletTarget = $outletTargets->where('target_year', $selectedYear)
                                                   ->sum('target_amount');
                }

                $outletRealization = $outletIncomes->sum('income');
                $outletAchievementRate = $outletTarget > 0 ? ($outletRealization / $outletTarget) * 100 : 0;

                if ($outletAchievementRate > 80) {
                    $outletsOnTrack++;
                }
            }

            // Prepare data for charts
            $chartData = $this->prepareChartData($outlets, $realizations, $targets, $selectedYear, $selectedMonthForQuery);

            $dashboardData = [
                'totalActiveOutlets' => $totalActiveOutlets,
                'totalIncome' => $totalIncome,
                'totalTarget' => $totalTarget,
                'avgAchievementRate' => $avgAchievementRate,
                'totalColly' => $totalColly,
                'totalWeight' => $totalWeight,
                'outletsOnTrack' => $outletsOnTrack,
                'chartData' => $chartData,
            ];
        }

        // Urutkan data berdasarkan kolom dan arah yang ditentukan
        usort($fullReportData, function($a, $b) use ($sortColumn, $sortDirection) {
            $result = 0;

            switch ($sortColumn) {
                case 'outlet_name':
                    $result = $a['outlet_name'] <=> $b['outlet_name'];
                    break;
                case 'target':
                    $result = $a['target'] <=> $b['target'];
                    break;
                case 'realization':
                    $result = $a['realization'] <=> $b['realization'];
                    break;
                case 'progress':
                    $result = $a['progress'] <=> $b['progress'];
                    break;
                case 'status':
                    $result = $a['status'] <=> $b['status'];
                    break;
                case 'office_name':
                    $result = $a['office_name'] <=> $b['office_name'];
                    break;
                case 'outlet_type':
                    $result = $a['outlet_type'] <=> $b['outlet_type'];
                    break;
                default: // Default to progress
                    $result = $a['progress'] <=> $b['progress'];
            }

            // Jika arah sorting adalah ascending, kembalikan hasil seperti biasa
            // Jika arah sorting adalah descending, balikkan hasilnya
            if ($sortDirection === 'desc') {
                $result = -$result;
            }

            return $result;
        });

        // For pagination, we need to convert our array to a Laravel Paginator instance
        $currentPage = request()->get('page', 1);
        $perPage = 10; // Number of items per page
        $offset = ($currentPage - 1) * $perPage;

        // Split the report data into pages
        $paginatedReportData = array_slice($fullReportData, $offset, $perPage);

        // Create a paginator instance
        $reportData = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedReportData,
            count($fullReportData),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
                'query' => request()->query()
            ]
        );

        // Get all available years for the dropdown
        $availableYears = collect([$selectedYear]); // Add the selected year
        $targetYears = IncomeTarget::selectRaw('DISTINCT target_year')
                                   ->orderBy('target_year', 'desc')
                                   ->pluck('target_year');
        $availableYears = $availableYears->merge($targetYears)->unique()->sort()->values();

        // Get all available months for the dropdown
        $availableMonths = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        // Get all outlets for filtering dropdown
        $allOutlets = Outlet::whereIn('id', $query->pluck('id'))->get();

        // Format the month name for dashboard display
        $monthNameForDashboard = $selectedMonth ? ($availableMonths[$selectedMonth] ?? 'Unknown Month') : 'All Months';

        return view('reports.target_realization.index', compact(
            'reportData',
            'dashboardData',
            'selectedYear',
            'selectedMonth',
            'selectedOutlet',
            'selectedOffice',
            'viewMode',
            'availableYears',
            'availableMonths',
            'monthNameForDashboard',
            'allOutlets',
            'offices'
        ));
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();

        // Check if user has proper access
        if (!$user->isSuperAdmin() && !$user->isAdminWilayah() && !$user->isAdminArea()) {
            abort(403, 'Unauthorized to access this report');
        }

        // Get filter and sort parameters (same as index)
        $selectedYear = $request->input('year', now()->year);
        // Check if 'month' parameter was provided in the request at all
        $selectedMonth = $request->has('month') ? $request->input('month') : now()->month;
        $selectedOutlet = $request->input('outlet', '');
        $selectedOffice = $request->input('office', '');
        $viewMode = $request->input('view', 'detailed'); // Default to detailed view
        $sortColumn = $request->input('sort', 'progress'); // Default sort by progress
        $sortDirection = $request->input('direction', 'desc'); // Default sort direction is descending

        // Handle the case when "All Months" is selected (empty string)
        $selectedMonthForQuery = empty($selectedMonth) ? null : $selectedMonth;

        // Get outlets based on user access level
        $query = Outlet::query();

        if ($user->isAdminArea()) {
            // Admin area can see their outlets
            $query->where('office_id', $user->office_id);
        } elseif ($user->isAdminWilayah()) {
            // Admin wilayah can see outlets in their wilayah
            $areaIds = $user->office->children()->pluck('id');
            $outletIds = $user->office->outlets()->pluck('id')->merge(
                Outlet::whereIn('office_id', $areaIds)->pluck('id')
            );
            $query->whereIn('id', $outletIds);
        }
        // Super admin can see all outlets

        // Apply filters if provided
        if ($selectedOutlet) {
            $query->where('id', $selectedOutlet);
        }

        if ($selectedOffice) {
            $query->where('office_id', $selectedOffice);
        }

        $outlets = $query->with(['office', 'outletType'])->get();

        // Get all target data based on selected year and month
        $targetQuery = IncomeTarget::whereIn('outlet_id', $outlets->pluck('id'));

        if ($selectedYear) {
            $targetQuery->where('target_year', $selectedYear);
        }

        $targets = $targetQuery->get();

        // Get all realization data based on selected year and month
        $realizationQuery = DailyIncome::whereIn('outlet_id', $outlets->pluck('id'));

        if ($selectedYear) {
            $realizationQuery->whereYear('date', $selectedYear);
        }

        // If a specific month is selected, narrow the date range
        if ($selectedMonthForQuery) {
            $realizationQuery->whereMonth('date', $selectedMonthForQuery);
        }

        $realizations = $realizationQuery->get();

        // Calculate target vs realization data
        $fullReportData = [];

        foreach ($outlets as $outlet) {
            $outletTargets = $targets->where('outlet_id', $outlet->id);
            $outletRealizations = $realizations->where('outlet_id', $outlet->id);

            // Calculate target for selected month and year (or all months if no month specified)
            if ($selectedMonthForQuery) {
                // Calculate target for specific month and year
                $target = $outletTargets->where('target_year', $selectedYear)
                                       ->where('target_month', $selectedMonthForQuery)
                                       ->sum('target_amount');
            } else {
                // Calculate target for all months in the selected year
                $target = $outletTargets->where('target_year', $selectedYear)
                                       ->sum('target_amount');
            }

            // Calculate realization for selected month and year (or all months if no month specified)
            $realization = $outletRealizations->sum('income');

            // Calculate progress percentage
            $progressPercentage = $target > 0 ? ($realization / $target) * 100 : 0;

            $fullReportData[] = [
                'outlet_name' => $outlet->name,
                'outlet_type' => $outlet->outletType->name ?? 'N/A',
                'office_name' => $outlet->office->name ?? 'N/A',
                'target' => $target,
                'realization' => $realization,
                'progress' => $progressPercentage,
                'status' => $progressPercentage >= 100 ? 'Achieved' : ($progressPercentage >= 80 ? 'On Track' : 'Below Target')
            ];
        }

        // Urutkan data berdasarkan kolom dan arah yang ditentukan (sama seperti di index)
        usort($fullReportData, function($a, $b) use ($sortColumn, $sortDirection) {
            $result = 0;

            switch ($sortColumn) {
                case 'outlet_name':
                    $result = $a['outlet_name'] <=> $b['outlet_name'];
                    break;
                case 'target':
                    $result = $a['target'] <=> $b['target'];
                    break;
                case 'realization':
                    $result = $a['realization'] <=> $b['realization'];
                    break;
                case 'progress':
                    $result = $a['progress'] <=> $b['progress'];
                    break;
                case 'status':
                    $result = $a['status'] <=> $b['status'];
                    break;
                case 'office_name':
                    $result = $a['office_name'] <=> $b['office_name'];
                    break;
                case 'outlet_type':
                    $result = $a['outlet_type'] <=> $b['outlet_type'];
                    break;
                default: // Default to progress
                    $result = $a['progress'] <=> $b['progress'];
            }

            // Jika arah sorting adalah ascending, kembalikan hasil seperti biasa
            // Jika arah sorting adalah descending, balikkan hasilnya
            if ($sortDirection === 'desc') {
                $result = -$result;
            }

            return $result;
        });

        // Create Excel export
        return Excel::download(new class($fullReportData, $selectedYear, $selectedMonth) implements FromCollection, WithHeadings, WithMapping {
            protected $data;
            protected $year;
            protected $month;

            public function __construct($data, $year, $month)
            {
                $this->data = $data;
                $this->year = $year;
                $this->month = $month;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return [
                    'Nama Outlet',
                    'Tipe Outlet',
                    'Nama Office',
                    'Target',
                    'Realisasi',
                    'Progres (%)',
                    'Status'
                ];
            }

            public function map($row): array
            {
                return [
                    $row['outlet_name'],
                    $row['outlet_type'],
                    $row['office_name'],
                    $row['target'],
                    $row['realization'],
                    number_format($row['progress'], 2),
                    $row['status']
                ];
            }
        }, "target_realization_report_{$selectedYear}" . ($selectedMonth ? "_" . $selectedMonth : "") . ".xlsx");
    }

    private function prepareChartData($outlets, $dailyIncomes, $incomeTargets, $year, $month)
    {
        $chartData = [];

        foreach ($outlets->take(10) as $outlet) { // Take top 10 for chart
            $outletIncomes = $dailyIncomes->where('outlet_id', $outlet->id);
            $outletTargets = $incomeTargets->where('outlet_id', $outlet->id);

            // Calculate target for selected month and year (or all months if no month specified)
            if ($month) {
                // Calculate target for specific month and year
                $target = $outletTargets->where('target_year', $year)
                                       ->where('target_month', $month)
                                       ->sum('target_amount');
            } else {
                // Calculate target for all months in the selected year
                $target = $outletTargets->where('target_year', $year)
                                       ->sum('target_amount');
            }

            $income = $outletIncomes->sum('income');
            $achievementRate = $target > 0 ? ($income / $target) * 100 : 0;

            $chartData[] = [
                'outlet_name' => $outlet->name,
                'income' => $income,
                'target' => $target,
                'achievement_rate' => $achievementRate,
            ];
        }

        return $chartData;
    }
}