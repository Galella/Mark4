<?php

namespace App\Exports;

use App\Models\OutletType;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OutletTypeExport implements FromCollection, WithHeadings, WithMapping
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
        // Semua role yang bisa mengakses outlet type bisa mengekspor semua
        // Kita hanya batasi akses ke fungsi export itu sendiri melalui middleware
        return OutletType::all();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Created At',
        ];
    }

    /**
     * @param mixed $outletType
     * @return array
     */
    public function map($outletType): array
    {
        return [
            $outletType->id,
            $outletType->name,
            $outletType->description ?: '-',
            $outletType->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
