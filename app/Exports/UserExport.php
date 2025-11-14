<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserExport implements FromCollection, WithHeadings, WithMapping
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Query users berdasarkan akses pengguna
        $query = User::query();
        
        if ($this->user && $this->user->isAdminWilayah()) {
            // Admin wilayah hanya bisa mengekspor user di wilayahnya
            $officeIds = $this->user->office->children()->pluck('id');
            $officeIds->push($this->user->office->id);
            
            $outletIds = \App\Models\Outlet::whereIn('office_id', $officeIds)->pluck('id');
            
            $query->where(function($q) use ($officeIds, $outletIds) {
                $q->whereIn('office_id', $officeIds)
                  ->orWhereIn('outlet_id', $outletIds);
            });
        } elseif ($this->user && $this->user->isAdminArea()) {
            // Admin area hanya bisa mengekspor user di areanya
            $outletIds = $this->user->office->outlets()->pluck('id');
            
            $query->where(function($q) use ($outletIds) {
                $q->where('office_id', $this->user->office_id)
                  ->orWhereIn('outlet_id', $outletIds);
            });
        } elseif ($this->user && $this->user->isAdminOutlet()) {
            // Admin outlet hanya bisa mengekspor dirinya sendiri
            $query->where('id', $this->user->id);
        }
        
        return $query->with(['office', 'outlet'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Role',
            'Office',
            'Outlet',
            'Created At',
        ];
    }

    /**
     * @param mixed $user
     * @return array
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            ucfirst(str_replace('_', ' ', $user->role)),
            $user->office ? $user->office->name : '-',
            $user->outlet ? $user->outlet->name : '-',
            $user->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
