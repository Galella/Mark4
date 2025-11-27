<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Office;
use App\Models\Outlet;
use App\Models\User;
use App\Models\OutletType;
use App\Models\DailyIncome;
use App\Models\Moda;
use App\Models\IncomeTarget;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Inisialisasi variabel
        $totalOffices = 0;
        $totalOutlets = 0;
        $totalUsers = 0;
        $totalOutletTypes = OutletType::count();
        $offices = collect();
        $outlets = collect();
        $recentActivities = collect();

        if ($user->isSuperAdmin()) {
            // Super admin melihat data nasional
            $totalOffices = Office::count();
            $totalOutlets = Outlet::count();
            $totalUsers = User::count();
            $offices = Office::with('parent')->limit(5)->get();
            $outlets = Outlet::with(['office', 'outletType'])->limit(5)->get();
            $recentActivities = collect(); // Kita akan implementasi log aktivitas nanti

        } elseif ($user->isAdminWilayah()) {
            // Admin wilayah melihat data di wilayahnya
            $officeIds = $user->office->children()->pluck('id');
            $officeIds[] = $user->office->id; // Tambahkan office wilayah sendiri

            $totalOffices = Office::whereIn('id', $officeIds)->count();
            $totalOutlets = Outlet::whereIn('office_id', $officeIds)->count();
            $totalUsers = User::where(function ($query) use ($officeIds) {
                $query->whereIn('office_id', $officeIds)
                    ->orWhereIn('outlet_id', Outlet::whereIn('office_id', $officeIds)->pluck('id'));
            })->count();

            $offices = Office::whereIn('id', $officeIds)->with('parent')->limit(5)->get();
            $outlets = Outlet::whereIn('office_id', $officeIds)
                ->with(['office', 'outletType'])
                ->limit(5)
                ->get();
        } elseif ($user->isAdminArea()) {
            // Admin area melihat data di areanya
            $totalOffices = 1; // Office area sendiri
            $totalOutlets = $user->office->outlets()->count();
            $totalUsers = User::where('office_id', $user->office_id)
                ->orWhereIn('outlet_id', $user->office->outlets()->pluck('id'))
                ->count();

            $offices = collect([$user->office]); // Hanya office area sendiri
            $outlets = $user->office->outlets()->with('outletType')->limit(5)->get();
        } elseif ($user->isAdminOutlet()) {
            // Admin outlet melihat data outletnya
            $totalOffices = 0; // Tidak perlu menampilkan office
            $totalOutlets = 1; // Outlet sendiri
            $totalUsers = User::where('outlet_id', $user->outlet_id)->count();
            $totalOutletTypes = OutletType::count();

            $outlets = collect([$user->outlet]); // Hanya outlet sendiri
        }

        // Ambil data untuk grafik - bervariasi berdasarkan role
        $chartData = $this->getChartData($user);

        // Calculate target progress data
        $targetProgressData = $this->getTargetProgressData($user);

        return view('dashboard', compact(
            'totalOffices',
            'totalOutlets',
            'totalUsers',
            'totalOutletTypes',
            'offices',
            'outlets',
            'recentActivities',
            'chartData',
            'targetProgressData'
        ));
    }

    private function getChartData($user)
    {
        // Data chart akan bervariasi berdasarkan role
        if ($user->isSuperAdmin()) {
            // Data untuk super admin - tampilkan data nasional
            $offices = Office::where('type', 'wilayah')->withCount(['children as areas_count'])->get();
            $labels = $offices->pluck('name')->toArray();
            $data = $offices->pluck('areas_count')->toArray();
        } elseif ($user->isAdminWilayah()) {
            // Data untuk admin wilayah - tampilkan data area di wilayahnya
            $areas = $user->office->children;
            $labels = $areas->pluck('name')->toArray();
            $data = $areas->map(function ($area) {
                return $area->outlets()->count();
            })->toArray();
        } elseif ($user->isAdminArea()) {
            // Data untuk admin area - tampilkan tipe outlet di areanya
            $outletTypes = OutletType::withCount(['outlets' => function ($query) use ($user) {
                $query->where('office_id', $user->office_id);
            }])->get();

            $labels = $outletTypes->pluck('name')->toArray();
            $data = $outletTypes->pluck('outlets_count')->toArray();
        } else {
            // Data untuk admin outlet - tampilkan data outlet sendiri
            $labels = [$user->outlet->name];
            $data = [1];
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getTargetProgressData($user)
    {
        // Query untuk menghitung actual income dan target untuk bulan ini
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $query = DailyIncome::query();

        // Apply user-based access control
        if ($user->isAdminOutlet()) {
            $query->where('outlet_id', $user->outlet_id);
        } elseif ($user->isAdminArea()) {
            $outletIds = $user->office->outlets()->pluck('id');
            $query->whereIn('outlet_id', $outletIds);
        } elseif ($user->isAdminWilayah()) {
            $outletIds = Outlet::whereHas('office', function ($q) use ($user) {
                $q->where('parent_id', $user->office_id)
                    ->orWhere('id', $user->office_id);
            })->pluck('id');
            $query->whereIn('outlet_id', $outletIds);
        }
        // Super admin gets all

        $actualIncome = $query
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('income');

        // Get target for the same period
        $targetQuery = IncomeTarget::query();

        if ($user->isAdminOutlet()) {
            $targetQuery->where('outlet_id', $user->outlet_id);
        } elseif ($user->isAdminArea()) {
            $outletIds = $user->office->outlets()->pluck('id');
            $targetQuery->whereIn('outlet_id', $outletIds);
        } elseif ($user->isAdminWilayah()) {
            $outletIds = Outlet::whereHas('office', function ($q) use ($user) {
                $q->where('parent_id', $user->office_id)
                    ->orWhere('id', $user->office_id);
            })->pluck('id');
            $targetQuery->whereIn('outlet_id', $outletIds);
        }
        // Super admin gets all

        $targetAmount = $targetQuery
            ->where('target_year', $currentYear)
            ->where('target_month', $currentMonth)
            ->sum('target_amount');

        // Calculate progress percentage
        $progressPercentage = $targetAmount > 0 ? ($actualIncome / $targetAmount) * 100 : 0;
        $progressPercentage = min($progressPercentage, 100); // Cap at 100%

        // Calculate additional target statistics
        $targetRemaining = max(0, $targetAmount - $actualIncome);
        $targetDifference = $actualIncome - $targetAmount; // Positive if exceeded, negative if below

        // Get targets for next month if available
        $nextMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
        $nextYear = $currentMonth == 12 ? $currentYear + 1 : $currentYear;

        $nextMonthTarget = $targetQuery
            ->where('target_year', $nextYear)
            ->where('target_month', $nextMonth)
            ->sum('target_amount');

        // Count outlets with targets vs total outlets
        $outletsWithTargets = $targetQuery
            ->where('target_year', $currentYear)
            ->where('target_month', $currentMonth)
            ->distinct('outlet_id')
            ->count('outlet_id');

        $totalOutletsForUser = 0;
        if ($user->isAdminOutlet()) {
            $totalOutletsForUser = 1;
        } elseif ($user->isAdminArea()) {
            $totalOutletsForUser = $user->office->outlets()->count();
        } elseif ($user->isAdminWilayah()) {
            $totalOutletsForUser = Outlet::whereHas('office', function ($q) use ($user) {
                $q->where('parent_id', $user->office_id)
                    ->orWhere('id', $user->office_id);
            })->count();
        } elseif ($user->isSuperAdmin()) {
            $totalOutletsForUser = Outlet::count();
        }

        return [
            'actual_income' => $actualIncome,
            'target_amount' => $targetAmount,
            'progress_percentage' => $progressPercentage,
            'is_achieved' => $actualIncome >= $targetAmount,
            'target_remaining' => $targetRemaining,
            'target_difference' => $targetDifference,
            'next_month_target' => $nextMonthTarget,
            'outlets_with_targets' => $outletsWithTargets,
            'total_outlets_for_user' => $totalOutletsForUser
        ];
    }

    /**
     * Get income statistics based on user access level
     */
    private function getIncomeStats($user)
    {
        $query = DailyIncome::query();

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
                $outletIds = Outlet::whereHas('office', function ($q) use ($user) {
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

        // Calculate today's income
        $todayIncome = clone $query;
        $todayIncome = $todayIncome->whereDate('date', today())->sum('income');

        // Calculate this week's income
        $weekIncome = clone $query;
        $weekIncome = $weekIncome->whereBetween('date', [today()->startOfWeek(), today()->endOfWeek()])->sum('income');

        // Calculate this month's income
        $monthIncome = clone $query;
        $monthIncome = $monthIncome->whereMonth('date', today()->month)->whereYear('date', today()->year)->sum('income');

        // Calculate total income
        $totalIncome = clone $query;
        $totalIncome = $totalIncome->sum('income');

        // Get income trend for last 7 days
        $incomeTrend = [];
        $incomeLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $incomeQuery = clone $query;
            $dayIncome = $incomeQuery->whereDate('date', $date)->sum('income');

            $incomeTrend[] = $dayIncome;
            $incomeLabels[] = $date->format('D');
        }

        // Get income by moda
        $incomeByModa = [];
        $modaLabels = [];

        $modaQuery = clone $query;
        $modaData = $modaQuery->selectRaw('moda_id, SUM(income) as total_income')
            ->groupBy('moda_id')
            ->with(['moda'])
            ->get();

        foreach ($modaData as $modaIncome) {
            if ($modaIncome->moda) {
                $incomeByModa[] = $modaIncome->total_income;
                $modaLabels[] = $modaIncome->moda->name;
            }
        }

        // Get income by outlet (for other charts)
        $incomeByOutlet = [];
        $outletLabels = [];

        $outletQuery = clone $query;
        $outletData = $outletQuery->selectRaw('outlet_id, SUM(income) as total_income')
            ->groupBy('outlet_id')
            ->with(['outlet'])
            ->get();

        foreach ($outletData as $outletIncome) {
            if ($outletIncome->outlet) {
                $incomeByOutlet[] = $outletIncome->total_income;
                $outletLabels[] = $outletIncome->outlet->name;
            }
        }

        // Get total income by month (simple bar chart showing total income per month)
        $monthlyIncomeData = $query
            ->selectRaw("
                YEAR(date) as year,
                MONTH(date) as month,
                SUM(income) as total_income
            ")
            ->where('date', '>=', now()->subMonths(12)->startOfMonth()) // Last 6 months
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Organize the data for the simple bar chart (total income per month)
        $incomeByOutletPerMonth = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Total Income',
                    'data' => [],
                    'backgroundColor' => 'rgb(230, 117, 20)',
                    'borderColor' => 'rgb(230, 117, 20)',
                    'borderWidth' => 1
                ]
            ]
        ];

        // Populate the labels and data arrays
        foreach ($monthlyIncomeData as $item) {
            $monthYear = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            $date = \DateTime::createFromFormat('Y-m', $monthYear);
            $incomeByOutletPerMonth['labels'][] = $date ? $date->format('M Y') : '';
            $incomeByOutletPerMonth['datasets'][0]['data'][] = $item->total_income;
        }

        // Calculate today's colly and weight based on user access level
        $todayColly = 0;
        $todayWeight = 0;

        // Create a fresh query for today's stats
        $todayStatsQuery = DailyIncome::query();

        // Apply the same access control as the main query
        if ($user->isAdminOutlet()) {
            $todayStatsQuery->where('outlet_id', $user->outlet_id);
        } else {
            // Admin area can see incomes for their outlets
            if ($user->isAdminArea()) {
                $outletIds = $user->office->outlets()->pluck('id');
                $todayStatsQuery->whereIn('outlet_id', $outletIds);
            }
            // Admin wilayah can see incomes for their area
            elseif ($user->isAdminWilayah()) {
                $outletIds = Outlet::whereHas('office', function ($q) use ($user) {
                    $q->where('parent_id', $user->office_id)
                        ->orWhere('id', $user->office_id);
                })->pluck('id');
                $todayStatsQuery->whereIn('outlet_id', $outletIds);
            }
            // Super admin can see all
            else {
                // No additional filter needed for super admin
            }
        }

        // Add date filter for today
        $todayStatsQuery->whereDate('date', today());

        $todayColly = $todayStatsQuery->sum('colly');
        $todayWeight = $todayStatsQuery->sum('weight');

        return [
            'today_income' => $todayIncome,
            'week_income' => $weekIncome,
            'month_income' => $monthIncome,
            'total_income' => $totalIncome,
            'today_colly' => $todayColly,
            'today_weight' => $todayWeight,
            'income_trend' => [
                'labels' => $incomeLabels,
                'data' => $incomeTrend,
            ],
            'income_by_moda' => [
                'labels' => $modaLabels,
                'data' => $incomeByModa,
            ],
            'income_by_outlet' => [
                'labels' => $outletLabels,
                'data' => $incomeByOutlet,
            ],
            'income_by_outlet_per_month' => $incomeByOutletPerMonth,
        ];
    }

    /**
     * Get income statistics via AJAX for dashboard widgets
     */
    public function getIncomeStatsAjax()
    {
        $user = auth()->user();
        $incomeStats = $this->getIncomeStats($user);

        return response()->json($incomeStats);
    }

    /**
     * Get income trend data via AJAX for dashboard chart
     */
    public function getIncomeTrendAjax()
    {
        $user = auth()->user();
        $incomeStats = $this->getIncomeStats($user);

        return response()->json($incomeStats['income_trend']);
    }

    /**
     * Get income by moda data via AJAX for dashboard chart
     */
    public function getIncomeByModaAjax()
    {
        $user = auth()->user();
        $incomeStats = $this->getIncomeStats($user);

        return response()->json($incomeStats['income_by_moda']);
    }

    /**
     * Get income by outlet data via AJAX for dashboard chart
     */
    public function getIncomeByOutletAjax()
    {
        $user = auth()->user();
        $incomeStats = $this->getIncomeStats($user);

        return response()->json($incomeStats['income_by_outlet']);
    }

    /**
     * Get income by outlet per month data via AJAX for dashboard chart
     */
    public function getIncomeByOutletPerMonthAjax()
    {
        $user = auth()->user();
        $incomeStats = $this->getIncomeStats($user);

        return response()->json($incomeStats['income_by_outlet_per_month']);
    }

    /**
     * Get income by moda per month data via AJAX for dashboard chart
     */
    public function getIncomeByModaPerMonthAjax()
    {
        $user = auth()->user();

        $query = DailyIncome::query();

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
                $outletIds = \App\Models\Outlet::whereHas('office', function ($q) use ($user) {
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

        // Get income by moda for the last 6 months grouped by month
        $modaIncomeData = $query
            ->selectRaw("
                moda_id, 
                YEAR(date) as year, 
                MONTH(date) as month,
                SUM(income) as total_income
            ")
            ->where('date', '>=', now()->subMonths(5)->startOfMonth()) // Last 6 months
            ->groupBy('moda_id', 'year', 'month')
            ->with(['moda'])
            ->get();

        // Organize the data for the chart
        $allModas = \App\Models\Moda::all();
        $allMonths = [];

        // Get unique months in the data
        foreach ($modaIncomeData as $item) {
            $monthKey = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            if (!in_array($monthKey, $allMonths)) {
                $allMonths[] = $monthKey;
            }
        }

        // Sort months chronologically
        sort($allMonths);

        // Prepare chart data
        $chartData = [
            'labels' => array_map(function ($month) {
                $date = \DateTime::createFromFormat('Y-m', $month);
                return $date ? $date->format('M Y') : '';
            }, $allMonths),
            'datasets' => []
        ];

        // Create a dataset for each moda
        foreach ($allModas as $moda) {
            $data = [];
            foreach ($allMonths as $month) {
                $monthIncome = $modaIncomeData->first(function ($item) use ($moda, $month) {
                    $itemMonth = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                    return $item->moda_id == $moda->id && $itemMonth == $month;
                });

                $data[] = $monthIncome ? $monthIncome->total_income : 0;
            }

            // Only add dataset if there's data for this moda
            if (array_sum($data) > 0) {
                $chartData['datasets'][] = [
                    'label' => $moda->name,
                    'data' => $data,
                    'backgroundColor' => $this->getModaColor($moda->id),
                    'borderColor' => $this->getModaBorderColor($moda->id),
                    'borderWidth' => 1
                ];
            }
        }

        return response()->json($chartData);
    }

    /**
     * Helper method to generate consistent colors for moda
     */
    private function getModaColor($modaId)
    {
        $colors = [
            '#9966FF',
            '#FFCE56',
            '#36A2EB',
            '#FF6384'

        ];

        return $colors[$modaId % count($colors)];
    }

    /**
     * Helper method to generate consistent border colors for moda
     */
    private function getModaBorderColor($modaId)
    {
        $colors = [
            '#9966FF',
            '#FFCE56',
            '#36A2EB',
            '#FF6384'
        ];

        return $colors[$modaId % count($colors)];
    }

    /**
     * Helper method to generate consistent colors for outlets
     */
    private function getOutletColor($outletId)
    {
        $colors = [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40',
            '#FF6384',
            '#C9CBCF',
            '#4BC0C0',
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40'
        ];

        return $colors[$outletId % count($colors)];
    }

    /**
     * Helper method to generate consistent border colors for outlets
     */
    private function getOutletBorderColor($outletId)
    {
        $colors = [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40',
            '#FF6384',
            '#C9CBCF',
            '#4BC0C0',
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40'
        ];

        return $colors[$outletId % count($colors)];
    }
}
