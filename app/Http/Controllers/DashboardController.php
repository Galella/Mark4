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
        // Build base query with user-based access control
        $baseQuery = function() use ($user) {
            $query = DailyIncome::query();

            if ($user->isAdminOutlet()) {
                $query->where('outlet_id', $user->outlet_id);
            } else {
                if ($user->isAdminArea()) {
                    $outletIds = $user->office->outlets()->pluck('id');
                    $query->whereIn('outlet_id', $outletIds);
                } elseif ($user->isAdminWilayah()) {
                    $outletIds = Outlet::whereHas('office', function ($q) use ($user) {
                        $q->where('parent_id', $user->office_id)
                            ->orWhere('id', $user->office_id);
                    })->pluck('id');
                    $query->whereIn('outlet_id', $outletIds);
                }
                // Super admin can see all
            }

            return $query;
        };

        // Calculate today's income
        $todayQuery = $baseQuery();
        $todayIncome = $todayQuery->whereDate('date', today())->sum('income');

        // Calculate this week's income
        $weekQuery = $baseQuery();
        $weekIncome = $weekQuery->whereBetween('date', [today()->startOfWeek(), today()->endOfWeek()])->sum('income');

        // Calculate this month's income
        $monthQuery = $baseQuery();
        $monthIncome = $monthQuery->whereMonth('date', today()->month)->whereYear('date', today()->year)->sum('income');

        // Calculate total income
        $totalQuery = $baseQuery();
        $totalIncome = $totalQuery->sum('income');

        // Get income trend for last 7 days
        $incomeTrend = [];
        $incomeLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $dailyQuery = $baseQuery();
            $dayIncome = $dailyQuery->whereDate('date', $date)->sum('income');

            $incomeTrend[] = (float)$dayIncome; // Ensure it's a float value
            $incomeLabels[] = $date->format('D');
        }

        // Debug log untuk admin outlet
        if ($user->isAdminOutlet()) {
            \Log::info('Admin Outlet Income Trend Debug', [
                'user_id' => $user->id,
                'outlet_id' => $user->outlet_id,
                'outlet_name' => $user->outlet ? $user->outlet->name : 'No outlet assigned',
                'dates_checked' => array_map(function($i) { return today()->subDays($i)->format('Y-m-d'); }, range(6, 0)),
                'income_trend_values' => $incomeTrend,
                'income_trend_labels' => $incomeLabels,
                'query_sql' => $baseQuery()->toSql(),
                'query_bindings' => $baseQuery()->getBindings()
            ]);
        }

        // Get income by moda
        $modaQuery = $baseQuery();
        $modaData = $modaQuery->selectRaw('moda_id, SUM(income) as total_income')
            ->groupBy('moda_id')
            ->with(['moda'])
            ->get();

        $incomeByModa = [];
        $modaLabels = [];
        foreach ($modaData as $modaIncome) {
            if ($modaIncome->moda) {
                $incomeByModa[] = $modaIncome->total_income;
                $modaLabels[] = $modaIncome->moda->name;
            }
        }

        // Get income by outlet (for other charts)
        $outletQuery = $baseQuery();
        $outletData = $outletQuery->selectRaw('outlet_id, SUM(income) as total_income')
            ->groupBy('outlet_id')
            ->with(['outlet'])
            ->get();

        $incomeByOutlet = [];
        $outletLabels = [];
        foreach ($outletData as $outletIncome) {
            if ($outletIncome->outlet) {
                $incomeByOutlet[] = $outletIncome->total_income;
                $outletLabels[] = $outletIncome->outlet->name;
            }
        }

        // Get total income by month (simple bar chart showing total income per month)
        $monthlyBaseQuery = $baseQuery();
        $monthlyIncomeData = $monthlyBaseQuery
            ->selectRaw("
                YEAR(date) as year,
                MONTH(date) as month,
                SUM(income) as total_income
            ")
            ->where('date', '>=', now()->subMonths(11)) // Last 12 months
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // For all users (admin outlet, admin area, admin wilayah, super admin), include target indicators to maintain consistency
        // First get target data for all user types
        $targetBaseQuery = function() use ($user) {
            $targetQuery = IncomeTarget::query();

            if ($user->isAdminOutlet()) {
                $targetQuery->where('outlet_id', $user->outlet_id);
            } else {
                if ($user->isAdminArea()) {
                    $outletIds = $user->office->outlets()->pluck('id');
                    $targetQuery->whereIn('outlet_id', $outletIds);
                } elseif ($user->isAdminWilayah()) {
                    $outletIds = Outlet::whereHas('office', function ($q) use ($user) {
                        $q->where('parent_id', $user->office_id)
                            ->orWhere('id', $user->office_id);
                    })->pluck('id');
                    $targetQuery->whereIn('outlet_id', $outletIds);
                }
                // Super admin can see all targets
            }

            return $targetQuery;
        };

        // Get target by month for the last 12 months
        $targetQuery = $targetBaseQuery();
        $monthlyTargetData = $targetQuery
            ->selectRaw("
                target_year as year,
                target_month as month,
                SUM(target_amount) as total_target
            ")
            ->where(function ($query) {
                $query->where('target_year', '>=', now()->year - 1) // Last 2 years
                      ->orWhere(function ($q) {
                          $q->where('target_year', now()->year)
                            ->where('target_month', '>=', now()->month - 11);
                      });
            })
            ->groupBy('target_year', 'target_month')
            ->orderBy('target_year', 'asc')
            ->orderBy('target_month', 'asc')
            ->get();

        // Organize the data for the chart with both income and target
        $incomeByOutletPerMonth = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Monthly Income',
                    'data' => [],
                    'backgroundColor' => 'rgba(230, 117, 20, 0.2)',
                    'borderColor' => 'rgb(230, 117, 20)',
                    'borderWidth' => 2,
                    'borderDash' => [], // Solid line for actual income
                    'fill' => false,
                    'yAxisID' => 'y',
                    'showLine' => true // Show line for income
                ],
                [
                    'label' => 'Monthly Target',
                    'data' => [],
                    'backgroundColor' => 'rgb(0, 123, 255)',
                    'borderColor' => 'rgb(0, 123, 255)',
                    'borderWidth' => 2,
                    'pointStyle' => 'star', // Use star shape for target markers
                    'pointRadius' => 6,
                    'pointHoverRadius' => 8,
                    'showLine' => false, // Don't show line, only points for target
                    'yAxisID' => 'y'
                ]
            ]
        ];

        // Prepare income and target data arrays
        $incomeData = [];
        $targetData = [];

        foreach ($monthlyIncomeData as $item) {
            $monthYear = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            $incomeData[$monthYear] = $item->total_income;
        }

        foreach ($monthlyTargetData as $item) {
            $monthYear = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            $targetData[$monthYear] = $item->total_target;
        }

        // Fill in missing months with 0
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthYear = $month->format('Y-m');
            $monthLabel = $month->format('M Y');

            $incomeByOutletPerMonth['labels'][] = $monthLabel;

            $incomeValue = isset($incomeData[$monthYear]) ? $incomeData[$monthYear] : 0;
            $targetValue = isset($targetData[$monthYear]) ? $targetData[$monthYear] : 0;

            $incomeByOutletPerMonth['datasets'][0]['data'][] = $incomeValue;
            $incomeByOutletPerMonth['datasets'][1]['data'][] = $targetValue;
        }

        // Calculate today's colly and weight based on user access level
        $todayStatsBaseQuery = function() use ($user) {
            $todayStatsQuery = DailyIncome::query();

            if ($user->isAdminOutlet()) {
                $todayStatsQuery->where('outlet_id', $user->outlet_id);
            } else {
                if ($user->isAdminArea()) {
                    $outletIds = $user->office->outlets()->pluck('id');
                    $todayStatsQuery->whereIn('outlet_id', $outletIds);
                } elseif ($user->isAdminWilayah()) {
                    $outletIds = Outlet::whereHas('office', function ($q) use ($user) {
                        $q->where('parent_id', $user->office_id)
                            ->orWhere('id', $user->office_id);
                    })->pluck('id');
                    $todayStatsQuery->whereIn('outlet_id', $outletIds);
                }
                // Super admin can see all
            }

            return $todayStatsQuery;
        };

        $todayStatsQuery = $todayStatsBaseQuery();
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
     * Get percentage of income by moda per month data for dashboard table
     */
    public function getIncomeByModaPerMonthPercentageAjax()
    {
        try {
            $user = auth()->user();

            $dailyIncomeQuery = DailyIncome::query();

            // Apply user-based access control
            if ($user->isAdminOutlet()) {
                // For admin outlet, only get their outlet's data
                $dailyIncomeQuery->where('outlet_id', $user->outlet_id);
            } else {
                // Admin area can see incomes for their outlets
                if ($user->isAdminArea()) {
                    $outletIds = $user->office->outlets()->pluck('id');
                    $dailyIncomeQuery->whereIn('outlet_id', $outletIds);
                }
                // Admin wilayah can see incomes for their area
                elseif ($user->isAdminWilayah()) {
                    $outletIds = \App\Models\Outlet::whereHas('office', function ($q) use ($user) {
                        $q->where('parent_id', $user->office_id)
                            ->orWhere('id', $user->office_id);
                    })->pluck('id');
                    $dailyIncomeQuery->whereIn('outlet_id', $outletIds);
                }
                // Super admin can see all
                else {
                    // No additional filter needed for super admin
                }
            }

            // Get income by moda for the last 12 months grouped by month and moda
            $modaIncomeData = $dailyIncomeQuery
                ->selectRaw("
                    daily_incomes.moda_id,
                    YEAR(daily_incomes.date) as year,
                    MONTH(daily_incomes.date) as month,
                    SUM(daily_incomes.income) as total_income
                ")
                ->join('modas', 'daily_incomes.moda_id', '=', 'modas.id') // Join with modas table
                ->where('daily_incomes.date', '>=', now()->subMonths(11)->startOfMonth()) // Last 12 months
                ->groupBy('daily_incomes.moda_id', 'year', 'month')
                ->get();

            // Calculate total income per month for percentage calculation
            $monthlyTotals = [];
            foreach ($modaIncomeData as $item) {
                $monthKey = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                if (!isset($monthlyTotals[$monthKey])) {
                    $monthlyTotals[$monthKey] = 0;
                }
                $monthlyTotals[$monthKey] += $item->total_income;
            }

            // Get all distinct moda with their names - this will be filtered below based on available data
            $allModas = \App\Models\Moda::all()->keyBy('id');
            $allMonths = [];

            // Get unique months for income data
            foreach ($modaIncomeData as $item) {
                if ($item->moda_id) { // Only add if moda_id exists
                    $monthKey = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                    if (!in_array($monthKey, $allMonths)) {
                        $allMonths[] = $monthKey;
                    }
                }
            }

            // Sort months chronologically
            sort($allMonths);

            // Prepare table data
            $tableData = [
                'modas' => [],
                'months' => array_map(function ($month) {
                    $date = \DateTime::createFromFormat('Y-m', $month);
                    return $date ? $date->format('M Y') : '';
                }, $allMonths),
                'data' => []
            ];

            // Get only modas that have income data for the user's accessible outlets
            $modasWithData = $modaIncomeData->pluck('moda_id')->unique()->values();
            $relevantModas = $allModas->filter(function ($moda, $key) use ($modasWithData) {
                return $modasWithData->contains($moda->id);
            });

            // Add each relevant moda to the table
            foreach ($relevantModas as $moda) {
                $modaRow = [
                    'name' => $moda->name,
                    'data' => []
                ];

                foreach ($allMonths as $month) {
                    // Get income for this moda and month
                    $monthIncome = $modaIncomeData->first(function ($item) use ($moda, $month) {
                        if (!$item->moda_id) return false;
                        $itemMonth = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                        return $item->moda_id == $moda->id && $itemMonth == $month;
                    });

                    $actualIncome = $monthIncome ? $monthIncome->total_income : 0;
                    $totalMonthIncome = isset($monthlyTotals[$month]) ? $monthlyTotals[$month] : 0;

                    // Calculate percentage of total income for the month - if no total income for the month, set to 0
                    $percentage = $totalMonthIncome > 0 ? ($actualIncome / $totalMonthIncome) * 100 : 0;

                    $modaRow['data'][] = [
                        'actual' => $actualIncome,
                        'total_month_income' => $totalMonthIncome,
                        'percentage' => round($percentage),
                        'formatted_percentage' => round($percentage) . '%'
                    ];
                }

                $tableData['modas'][] = $moda->name;
                $tableData['data'][] = $modaRow;
            }

            return response()->json($tableData);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error in getIncomeByModaPerMonthPercentageAjax: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            // Return empty data in case of error
            return response()->json([
                'modas' => [],
                'months' => [],
                'data' => []
            ]);
        }
    }

    /**
     * Get income per month for admin outlet with target indicators
     */
    public function getIncomePerMonthForOutletAjax()
    {
        $user = auth()->user();

        // Only for admin outlet users
        if (!$user->isAdminOutlet()) {
            return response()->json([
                'labels' => [],
                'datasets' => []
            ]);
        }

        $dailyIncomeQuery = DailyIncome::where('outlet_id', $user->outlet_id);
        $incomeTargetQuery = IncomeTarget::where('outlet_id', $user->outlet_id);

        // Get income by month for the last 12 months
        $monthlyIncomeData = $dailyIncomeQuery
            ->selectRaw("
                YEAR(date) as year,
                MONTH(date) as month,
                SUM(income) as total_income
            ")
            ->where('date', '>=', now()->subMonths(11)->startOfMonth()) // Last 12 months
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Get target by month for the last 12 months
        $monthlyTargetData = $incomeTargetQuery
            ->selectRaw("
                target_year as year,
                target_month as month,
                SUM(target_amount) as total_target
            ")
            ->where(function ($query) {
                $query->where('target_year', '>=', now()->year - 1) // Last 2 years
                      ->orWhere(function ($q) {
                          $q->where('target_year', now()->year)
                            ->where('target_month', '>=', now()->month - 11);
                      });
            })
            ->groupBy('target_year', 'target_month')
            ->orderBy('target_year', 'asc')
            ->orderBy('target_month', 'asc')
            ->get();

        // Prepare labels and data
        $labels = [];
        $incomeData = [];
        $targetData = [];

        foreach ($monthlyIncomeData as $item) {
            $monthYear = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            $date = \DateTime::createFromFormat('Y-m', $monthYear);
            $labels[] = $date ? $date->format('M Y') : '';
            $incomeData[$monthYear] = $item->total_income;
        }

        foreach ($monthlyTargetData as $item) {
            $monthYear = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            $targetData[$monthYear] = $item->total_target;
        }

        // Fill in missing months with 0
        $allMonths = [];
        $allIncomeData = [];
        $allTargetData = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthYear = $month->format('Y-m');
            $monthLabel = $month->format('M Y');

            $allMonths[] = $monthLabel;

            $incomeValue = isset($incomeData[$monthYear]) ? $incomeData[$monthYear] : 0;
            $targetValue = isset($targetData[$monthYear]) ? $targetData[$monthYear] : 0;

            $allIncomeData[] = $incomeValue;
            $allTargetData[] = $targetValue;
        }

        $chartData = [
            'labels' => $allMonths,
            'datasets' => [
                [
                    'label' => 'Monthly Income',
                    'data' => $allIncomeData,
                    'backgroundColor' => 'rgba(230, 117, 20, 0.2)',
                    'borderColor' => 'rgb(230, 117, 20)',
                    'borderWidth' => 2,
                    'borderDash' => [], // Solid line for actual income
                    'fill' => false,
                    'yAxisID' => 'y',
                    'showLine' => true // Show line for income
                ],
                [
                    'label' => 'Monthly Target',
                    'data' => $allTargetData,
                    'backgroundColor' => 'rgb(0, 123, 255)',
                    'borderColor' => 'rgb(0, 123, 255)',
                    'borderWidth' => 2,
                    'pointStyle' => 'star', // Use star shape for target markers
                    'pointRadius' => 6,
                    'pointHoverRadius' => 8,
                    'showLine' => false, // Don't show line, only points for target
                    'yAxisID' => 'y'
                ]
            ]
        ];

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
