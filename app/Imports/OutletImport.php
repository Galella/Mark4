<?php

namespace App\Imports;

use App\Models\Outlet;
use App\Models\Office;
use App\Models\OutletType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class OutletImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    public $errors = [];
    public $successCount = 0;

    public function collection(Collection $rows)
    {
        $userId = Auth::id();

        foreach ($rows as $index => $row) {
            try {
                // Normalize the row keys to lowercase to handle different cases
                $row = collect($row)->mapWithKeys(function ($value, $key) {
                    return [strtolower($key) => $value];
                })->toArray();

                // Validate each row
                $validator = Validator::make($row, [
                    'name' => 'required|string|max:255',
                    'code' => 'required|string|max:50|unique:outlets,code',
                    'office_code' => 'required|string|exists:offices,code',
                    'outlet_type_name' => 'required|string|exists:outlet_types,name',
                    'description' => 'nullable|string|max:500',
                    'address' => 'nullable|string|max:500',
                    'phone' => 'nullable|string|max:20',
                    'email' => 'nullable|email|max:255',
                    'pic_name' => 'nullable|string|max:255',
                    'pic_phone' => 'nullable|string|max:20',
                    'is_active' => 'nullable|boolean',
                ]);

                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $error) {
                        $this->errors[] = [
                            'row' => $index + 2, // +2 karena baris pertama adalah header
                            'error' => $error
                        ];
                    }
                    continue;
                }

                $validated = $validator->validated();

                // Get office by code
                $office = Office::where('code', $validated['office_code'])->first();
                if (!$office) {
                    $this->errors[] = [
                        'row' => $index + 2,
                        'error' => 'Office with code ' . $validated['office_code'] . ' not found'
                    ];
                    continue;
                }

                // Get outlet type by name
                $outletType = OutletType::where('name', $validated['outlet_type_name'])->first();
                if (!$outletType) {
                    $this->errors[] = [
                        'row' => $index + 2,
                        'error' => 'Outlet type with name ' . $validated['outlet_type_name'] . ' not found'
                    ];
                    continue;
                }

                // Create the outlet
                Outlet::create([
                    'name' => $validated['name'],
                    'code' => $validated['code'],
                    'office_id' => $office->id,
                    'outlet_type_id' => $outletType->id,
                    'description' => $validated['description'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'pic_name' => $validated['pic_name'] ?? null,
                    'pic_phone' => $validated['pic_phone'] ?? null,
                    'is_active' => filter_var($validated['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ]);

                $this->successCount++;
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $index + 2,
                    'error' => $e->getMessage()
                ];
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:outlets,code',
            'office_code' => 'required|string|exists:offices,code',
            'outlet_type_name' => 'required|string|exists:outlet_types,name',
            'description' => 'nullable|string|max:500',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            // Handle the failures how you'd like
            // You can log them, store them in session, etc.
        }
    }
}