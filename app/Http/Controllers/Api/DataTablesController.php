<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Outlet;
use App\Models\Moda;
use App\Models\Office;
use App\Models\OutletType;
use App\Models\DailyIncome;
use App\Models\IncomeTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataTablesController extends Controller
{
    public function users()
    {
        $user = Auth::user();

        // Query users berdasarkan akses pengguna
        $query = User::query();

        if ($user->isAdminWilayah()) {
            // Admin wilayah hanya bisa melihat user di wilayahnya
            $officeIds = $user->office->children()->pluck('id');
            $officeIds->push($user->office->id);

            $query->whereHas('office', function($q) use ($officeIds) {
                $q->whereIn('office_id', $officeIds);
            })->orWhereHas('outlet', function($q) use ($officeIds) {
                $q->whereIn('outlets.office_id', $officeIds);
            });
        } elseif ($user->isAdminArea()) {
            // Admin area hanya bisa melihat user di areanya
            $query->where(function($q) use ($user) {
                $q->where('office_id', $user->office_id)
                  ->orWhereHas('outlet', function($q2) use ($user) {
                      $q2->where('outlet_id', $user->outlet_id);
                  });
            });
        } elseif ($user->isAdminOutlet()) {
            // Admin outlet hanya bisa melihat user di outlenya sendiri
            $query->where('outlet_id', $user->outlet_id);
        }

        $users = $query->with(['office', 'outlet'])->get();

        return response()->json([
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'office' => $user->office ? $user->office->name : '-',
                    'outlet' => $user->outlet ? $user->outlet->name : '-'
                ];
            })
        ]);
    }

    public function outlets()
    {
        $user = Auth::user();

        // Query outlets berdasarkan akses pengguna
        $query = Outlet::query();

        if ($user->isAdminWilayah()) {
            // Admin wilayah hanya bisa melihat outlet di wilayahnya
            $officeIds = $user->office->children()->pluck('id');
            $officeIds->push($user->office->id);
            $query->whereIn('office_id', $officeIds);
        } elseif ($user->isAdminArea()) {
            // Admin area hanya bisa melihat outlet di areanya
            $query->where('office_id', $user->office_id);
        } elseif ($user->isAdminOutlet()) {
            // Admin outlet hanya bisa melihat outletnya sendiri
            $query->where('id', $user->outlet_id);
        }

        $outlets = $query->with(['office', 'outletType'])->get();

        return response()->json([
            'data' => $outlets->map(function ($outlet) {
                return [
                    'id' => $outlet->id,
                    'name' => $outlet->name,
                    'code' => $outlet->code,
                    'office' => $outlet->office->name ?? '-',
                    'outlet_type' => $outlet->outletType->name ?? '-',
                    'address' => $outlet->address ?? '-',
                    'is_active' => $outlet->is_active
                ];
            })
        ]);
    }

    public function modas()
    {
        $modas = Moda::all();

        return response()->json([
            'data' => $modas->map(function ($moda) {
                return [
                    'id' => $moda->id,
                    'name' => $moda->name,
                    'description' => $moda->description
                ];
            })
        ]);
    }

    public function offices()
    {
        $user = Auth::user();

        // Query offices berdasarkan akses pengguna
        $query = Office::query();

        if ($user->isAdminWilayah()) {
            // Admin wilayah hanya bisa melihat office di wilayahnya
            $officeIds = $user->office->children()->pluck('id');
            $officeIds->push($user->office->id);
            $query->whereIn('id', $officeIds);
        } elseif ($user->isAdminArea()) {
            // Admin area hanya bisa melihat office di areanya
            $query->where('id', $user->office_id);
        }

        $offices = $query->with('parent')->get();

        return response()->json([
            'data' => $offices->map(function ($office) {
                return [
                    'id' => $office->id,
                    'name' => $office->name,
                    'code' => $office->code,
                    'type' => $office->type,
                    'parent_office' => $office->parent ? $office->parent->name : '-',
                    'is_active' => $office->is_active
                ];
            })
        ]);
    }

    public function outletTypes()
    {
        $outletTypes = OutletType::all();

        return response()->json([
            'data' => $outletTypes->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'description' => $type->description
                ];
            })
        ]);
    }

    public function dailyIncomes()
    {
        $user = Auth::user();

        // Query daily incomes berdasarkan akses pengguna
        $query = DailyIncome::query();

        if ($user->isAdminWilayah()) {
            // Admin wilayah hanya bisa melihat income dari outlet di wilayahnya
            $officeIds = $user->office->children()->pluck('id');
            $officeIds->push($user->office->id);

            $query->whereHas('outlet', function($q) use ($officeIds) {
                $q->whereIn('outlets.office_id', $officeIds);
            });
        } elseif ($user->isAdminArea()) {
            // Admin area hanya bisa melihat income dari outlet di areanya
            $query->whereHas('outlet', function($q) use ($user) {
                $q->where('outlets.office_id', $user->office_id);
            });
        } elseif ($user->isAdminOutlet()) {
            // Admin outlet hanya bisa melihat income dari outlenya sendiri
            $query->where('outlet_id', $user->outlet_id);
        }

        $incomes = $query->with(['moda', 'outlet'])->get();

        return response()->json([
            'data' => $incomes->map(function ($income) {
                return [
                    'id' => $income->id,
                    'date' => $income->date->format('d M Y'),
                    'moda' => $income->moda ? $income->moda->name : '-',
                    'colly' => $income->colly,
                    'weight' => $income->weight,
                    'income' => $income->income,
                    'outlet' => $income->outlet ? $income->outlet->name : '-'
                ];
            })
        ]);
    }

    public function incomeTargets()
    {
        $user = Auth::user();

        // Query income targets berdasarkan akses pengguna
        $query = IncomeTarget::query();

        if ($user->isAdminWilayah()) {
            // Admin wilayah hanya bisa melihat target dari outlet di wilayahnya
            $officeIds = $user->office->children()->pluck('id');
            $officeIds->push($user->office->id);

            $query->whereHas('outlet', function($q) use ($officeIds) {
                $q->whereIn('outlets.office_id', $officeIds);
            });
        } elseif ($user->isAdminArea()) {
            // Admin area hanya bisa melihat target dari outlet di areanya
            $query->whereHas('outlet', function($q) use ($user) {
                $q->where('outlets.office_id', $user->office_id);
            });
        } elseif ($user->isAdminOutlet()) {
            // Admin outlet hanya bisa melihat target dari outlenya sendiri
            $query->whereHas('outlet', function($q) use ($user) {
                $q->where('outlets.id', $user->outlet_id);
            });
        }

        $targets = $query->with(['outlet', 'moda', 'assignedBy'])->get();

        return response()->json([
            'data' => $targets->map(function ($target) {
                return [
                    'id' => $target->id,
                    'outlet' => $target->outlet->name ?? 'N/A',
                    'moda' => $target->moda->name ?? 'N/A',
                    'target_period' => \DateTime::createFromFormat('!m', $target->target_month)->format('F') . ' ' . $target->target_year,
                    'target_amount' => $target->target_amount,
                    'assigned_by' => $target->assignedBy->name ?? 'N/A'
                ];
            })
        ]);
    }
}
