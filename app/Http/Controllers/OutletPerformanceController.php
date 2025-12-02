<?php

namespace App\Http\Controllers;

use App\Models\DailyIncome;
use App\Models\OutletPerformance;
use App\Models\IncomeTarget;
use App\Models\Office;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OutletPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user has proper access (same as target realization report)
        if (!$user->isSuperAdmin() && !$user->isAdminWilayah() && !$user->isAdminArea()) {
            abort(403, 'Unauthorized to access this report');
        }

        // Get filter parameters
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $selectedOutlet = $request->input('outlet', '');

        // Get outlets based on user access level
        $query = Outlet::query();

        if ($user->isAdminArea()) {
            $query->where('office_id', $user->office_id);
        } elseif ($user->isAdminWilayah()) {
            $areaIds = $user->office->children()->pluck('id');
            $outletIds = $user->office->outlets()->pluck('id')->merge(
                Outlet::whereIn('office_id', $areaIds)->pluck('id')
            );
            $query->whereIn('id', $outletIds);
        }
        // Super admin can see all outlets

        // Apply outlet filter if provided
        if ($selectedOutlet) {
            $query->where('id', $selectedOutlet);
        }

        $outlets = $query->with(['office', 'outletType'])->get();

        // Get all daily incomes for the selected period
        $dailyIncomesQuery = DailyIncome::whereIn('outlet_id', $outlets->pluck('id'))
            ->whereYear('date', $year);

        // Add month filter only if a specific month is selected
        if (!empty($month)) {
            $dailyIncomesQuery->whereMonth('date', $month);
        }

        $dailyIncomes = $dailyIncomesQuery->get();

        // Get all income targets for the selected period
        $incomeTargetsQuery = IncomeTarget::whereIn('outlet_id', $outlets->pluck('id'))
            ->where('target_year', $year);

        // Add month filter only if a specific month is selected
        if (!empty($month)) {
            $incomeTargetsQuery->where('target_month', $month);
        } else {
            // If no month is selected, we'll get targets for the entire year
            // This might need adjustment based on how targets are stored
        }

        $incomeTargets = $incomeTargetsQuery->get();

        // Calculate performance data
        $performanceData = [];

        foreach ($outlets as $outlet) {
            $outletIncomes = $dailyIncomes->where('outlet_id', $outlet->id);
            $outletTargets = $incomeTargets->where('outlet_id', $outlet->id);

            // Calculate total income for the period
            $totalIncome = $outletIncomes->sum('income');

            // Calculate total target for the period
            $totalTarget = $outletTargets->sum('target_amount');

            // Calculate achievement rate
            $achievementRate = $totalTarget > 0 ? ($totalIncome / $totalTarget) * 100 : 0;

            // Calculate other metrics
            $totalColly = $outletIncomes->sum('colly');
            $totalWeight = $outletIncomes->sum('weight');

            // Calculate performance score (weighted average)
            $performanceScore = $this->calculatePerformanceScore(
                $achievementRate,
                $totalColly,
                $totalWeight
            );

            $performanceData[] = [
                'outlet_id' => $outlet->id,
                'outlet_name' => $outlet->name,
                'outlet_type' => $outlet->outletType->name ?? 'N/A',
                'office_name' => $outlet->office->name ?? 'N/A',
                'total_income' => $totalIncome,
                'total_target' => $totalTarget,
                'achievement_rate' => $achievementRate,
                'total_colly' => $totalColly,
                'total_weight' => $totalWeight,
                'performance_score' => $performanceScore,
                'status' => $this->getStatusByScore($performanceScore)
            ];
        }

        // Sort by performance score (descending) by default
        usort($performanceData, function($a, $b) {
            return $b['performance_score'] <=> $a['performance_score'];
        });

        // For pagination
        $currentPage = $request->get('page', 1);
        $perPage = 10;
        $offset = ($currentPage - 1) * $perPage;
        $paginatedData = array_slice($performanceData, $offset, $perPage);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedData,
            count($performanceData),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
                'query' => $request->query()
            ]
        );

        // For the view, we need to handle the case when month is empty string
        $monthForView = empty($month) ? null : $month;

        // Get available outlets for filtering
        $availableOutlets = Outlet::whereIn('id', $query->pluck('id'))->get();

        // Get available months and years
        $availableMonths = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $availableYears = collect(range(now()->year - 5, now()->year))->reverse();

        return view('outlet-performance.index', compact(
            'paginator',
            'monthForView',
            'year',
            'selectedOutlet',
            'availableOutlets',
            'availableMonths',
            'availableYears'
        ));
    }
    
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        // Check if user has proper access (same as target realization report)
        if (!$user->isSuperAdmin() && !$user->isAdminWilayah() && !$user->isAdminArea()) {
            abort(403, 'Unauthorized to access this report');
        }

        // Get filters
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Get outlets based on user access level for KPI calculation
        $query = Outlet::query();

        if ($user->isAdminArea()) {
            $query->where('office_id', $user->office_id);
        } elseif ($user->isAdminWilayah()) {
            $areaIds = $user->office->children()->pluck('id');
            $outletIds = $user->office->outlets()->pluck('id')->merge(
                Outlet::whereIn('office_id', $areaIds)->pluck('id')
            );
            $query->whereIn('id', $outletIds);
        }

        $outlets = $query->get();

        // Get data for KPIs
        $dailyIncomesQuery = DailyIncome::whereIn('outlet_id', $outlets->pluck('id'))
            ->whereYear('date', $year);

        // Add month filter only if a specific month is selected
        if (!empty($month)) {
            $dailyIncomesQuery->whereMonth('date', $month);
        }

        $dailyIncomes = $dailyIncomesQuery->get();

        $incomeTargetsQuery = IncomeTarget::whereIn('outlet_id', $outlets->pluck('id'))
            ->where('target_year', $year);

        // Add month filter only if a specific month is selected
        if (!empty($month)) {
            $incomeTargetsQuery->where('target_month', $month);
        } else {
            // If no month is selected, we'll get targets for the entire year
            // This might need adjustment based on how targets are stored
        }

        $incomeTargets = $incomeTargetsQuery->get();

        // Calculate KPIs
        $totalActiveOutlets = $outlets->count();
        $totalIncome = $dailyIncomes->sum('income');
        $totalTarget = $incomeTargets->sum('target_amount');
        $avgAchievementRate = $totalTarget > 0 ? ($totalIncome / $totalTarget) * 100 : 0;
        $totalColly = $dailyIncomes->sum('colly');
        $totalWeight = $dailyIncomes->sum('weight');

        // Calculate outlets on track (achievement > 80%)
        $outletsOnTrack = 0;
        foreach ($outlets as $outlet) {
            $outletIncomes = $dailyIncomes->where('outlet_id', $outlet->id);
            $outletTargets = $incomeTargets->where('outlet_id', $outlet->id);

            $outletTotalIncome = $outletIncomes->sum('income');
            $outletTotalTarget = $outletTargets->sum('target_amount');
            $outletAchievementRate = $outletTotalTarget > 0 ? ($outletTotalIncome / $outletTotalTarget) * 100 : 0;

            if ($outletAchievementRate > 80) {
                $outletsOnTrack++;
            }
        }

        // Prepare data for charts
        $chartData = $this->prepareChartData($outlets, $dailyIncomes, $incomeTargets, $year, $month);

        // Make sure month is null when empty string is passed so the view handles it properly
        $monthForView = empty($month) ? null : $month;

        // Get available months for the view
        $availableMonths = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        return view('outlet-performance.dashboard', compact(
            'totalActiveOutlets',
            'totalIncome',
            'totalTarget',
            'avgAchievementRate',
            'totalColly',
            'totalWeight',
            'outletsOnTrack',
            'chartData',
            'monthForView',
            'year',
            'availableMonths'
        ));
    }
    
    private function calculatePerformanceScore($achievementRate, $colly, $weight)
    {
        // Weighted calculation for performance score
        // Achievement rate: 60%, Colly: 25%, Weight: 15%
        $rateScore = min($achievementRate, 100); // Cap at 100%
        $collyScore = min(($colly / 1000) * 100, 100); // Normalize colly
        $weightScore = min(($weight / 1000) * 100, 100); // Normalize weight
        
        return ($rateScore * 0.6) + ($collyScore * 0.25) + ($weightScore * 0.15);
    }
    
    private function getStatusByScore($score)
    {
        if ($score >= 90) return 'Excellent';
        if ($score >= 75) return 'Good';
        if ($score >= 60) return 'Average';
        if ($score >= 40) return 'Below Average';
        return 'Poor';
    }
    
    private function prepareChartData($outlets, $dailyIncomes, $incomeTargets, $year, $month)
    {
        $chartData = [];

        foreach ($outlets->take(10) as $outlet) { // Take top 10 for chart
            $outletIncomes = $dailyIncomes->where('outlet_id', $outlet->id);
            $outletTargets = $incomeTargets->where('outlet_id', $outlet->id);

            $totalIncome = $outletIncomes->sum('income');
            $totalTarget = $outletTargets->sum('target_amount');
            $achievementRate = $totalTarget > 0 ? ($totalIncome / $totalTarget) * 100 : 0;

            $chartData[] = [
                'outlet_name' => $outlet->name,
                'income' => $totalIncome,
                'target' => $totalTarget,
                'achievement_rate' => $achievementRate,
            ];
        }

        return $chartData;
    }

}