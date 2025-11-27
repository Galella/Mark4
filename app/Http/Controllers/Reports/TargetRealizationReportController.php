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
        $selectedMonth = $request->input('month', now()->month);
        $selectedOutlet = $request->input('outlet', '');
        $selectedOffice = $request->input('office', '');

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

        // If month is selected, narrow the date range
        if ($selectedMonth) {
            $realizationQuery->whereMonth('date', $selectedMonth);
        }

        $realizations = $realizationQuery->get();

        // Calculate target vs realization data
        $fullReportData = [];

        foreach ($outlets as $outlet) {
            $outletTargets = $targets->where('outlet_id', $outlet->id);
            $outletRealizations = $realizations->where('outlet_id', $outlet->id);

            // Calculate target for selected month and year
            $target = $outletTargets->where('target_year', $selectedYear)
                                   ->where('target_month', $selectedMonth)
                                   ->sum('target_amount');

            // Calculate realization for selected month and year
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

        return view('reports.target_realization.index', compact(
            'reportData',
            'selectedYear',
            'selectedMonth',
            'selectedOutlet',
            'selectedOffice',
            'availableYears',
            'availableMonths',
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

        // Get filter parameters (same as index)
        $selectedYear = $request->input('year', now()->year);
        $selectedMonth = $request->input('month', now()->month);
        $selectedOutlet = $request->input('outlet', '');
        $selectedOffice = $request->input('office', '');

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

        // If month is selected, narrow the date range
        if ($selectedMonth) {
            $realizationQuery->whereMonth('date', $selectedMonth);
        }

        $realizations = $realizationQuery->get();

        // Calculate target vs realization data
        $fullReportData = [];

        foreach ($outlets as $outlet) {
            $outletTargets = $targets->where('outlet_id', $outlet->id);
            $outletRealizations = $realizations->where('outlet_id', $outlet->id);

            // Calculate target for selected month and year
            $target = $outletTargets->where('target_year', $selectedYear)
                                   ->where('target_month', $selectedMonth)
                                   ->sum('target_amount');

            // Calculate realization for selected month and year
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

        // Create Excel export using an anonymous class that implements the necessary interfaces
        return Excel::download(new class($fullReportData, $selectedMonth, $selectedYear) implements FromCollection, WithHeadings, WithMapping
        {
            protected $reportData;
            protected $selectedMonth;
            protected $selectedYear;

            public function __construct($reportData, $selectedMonth, $selectedYear)
            {
                $this->reportData = $reportData;
                $this->selectedMonth = $selectedMonth;
                $this->selectedYear = $selectedYear;
            }

            public function collection()
            {
                return collect($this->reportData);
            }

            public function headings(): array
            {
                return [
                    'Nama Outlet',
                    'Periode',
                    'Target (Rp)',
                    'Realisasi (Rp)',
                    'Progres (%)',
                    'Status'
                ];
            }

            public function map($data): array
            {
                return [
                    $data['outlet_name'],
                    \DateTime::createFromFormat('!m', $this->selectedMonth)->format('F') . ' ' . $this->selectedYear,
                    $data['target'],
                    $data['realization'],
                    number_format($data['progress'], 2),
                    $data['status']
                ];
            }
        }, 'target_realization_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }
}