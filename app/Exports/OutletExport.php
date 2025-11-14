<?php

namespace App\Exports;

use App\Models\Outlet;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OutletExport implements FromCollection, WithHeadings, WithMapping
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
        // Query outlets berdasarkan akses pengguna
        $query = Outlet::query();
        
        if ($this->user && $this->user->isAdminWilayah()) {
            // Admin wilayah hanya bisa mengekspor outlet di wilayahnya
            $officeIds = $this->user->office->children()->pluck('id');
            $officeIds->push($this->user->office->id);
            $query->whereIn('office_id', $officeIds);
        } elseif ($this->user && $this->user->isAdminArea()) {
            // Admin area hanya bisa mengekspor outlet di areanya
            $query->where('office_id', $this->user->office_id);
        } elseif ($this->user && $this->user->isAdminOutlet()) {
            // Admin outlet hanya bisa mengekspor outletnya sendiri
            $query->where('id', $this->user->outlet_id);
        }
        
        return $query->with(['office', 'outletType'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Code',
            'Office',
            'Outlet Type',
            'Address',
            'Phone',
            'Email',
            'PIC Name',
            'PIC Phone',
            'Status',
            'Created At',
        ];
    }

    /**
     * @param mixed $outlet
     * @return array
     */
    public function map($outlet): array
    {
        return [
            $outlet->id,
            $outlet->name,
            $outlet->code,
            $outlet->office->name,
            $outlet->outletType->name,
            $outlet->address ?: '-',
            $outlet->phone ?: '-',
            $outlet->email ?: '-',
            $outlet->pic_name ?: '-',
            $outlet->pic_phone ?: '-',
            $outlet->is_active ? 'Active' : 'Inactive',
            $outlet->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
