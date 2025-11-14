<?php

namespace App\Exports;

use App\Models\Office;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OfficeExport implements FromCollection, WithHeadings, WithMapping
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
        // Query offices berdasarkan akses pengguna
        $query = Office::query();
        
        if ($this->user && $this->user->isAdminWilayah()) {
            // Admin wilayah hanya bisa mengekspor office di wilayahnya
            $officeIds = $this->user->office->children()->pluck('id');
            $officeIds->push($this->user->office->id);
            $query->whereIn('id', $officeIds);
        }
        
        return $query->with('parent')->get();
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
            'Type',
            'Parent Office',
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
     * @param mixed $office
     * @return array
     */
    public function map($office): array
    {
        return [
            $office->id,
            $office->name,
            $office->code,
            ucfirst($office->type),
            $office->parent ? $office->parent->name : '-',
            $office->address ?: '-',
            $office->phone ?: '-',
            $office->email ?: '-',
            $office->pic_name ?: '-',
            $office->pic_phone ?: '-',
            $office->is_active ? 'Active' : 'Inactive',
            $office->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
