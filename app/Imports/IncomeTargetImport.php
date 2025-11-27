<?php

namespace App\Imports;

use App\Models\IncomeTarget;
use App\Models\Outlet;
use App\Models\Moda;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class IncomeTargetImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
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
                    'outlet_code' => 'required|string|exists:outlets,code',
                    'moda_name' => 'required|string|exists:modas,name',
                    'target_year' => 'required|integer|min:2000|max:2100',
                    'target_month' => 'required|integer|min:1|max:12',
                    'target_amount' => 'required|numeric|min:0',
                    'description' => 'nullable|string|max:500',
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

                // Get outlet by code
                $outlet = Outlet::where('code', $validated['outlet_code'])->first();
                if (!$outlet) {
                    $this->errors[] = [
                        'row' => $index + 2,
                        'error' => 'Outlet with code ' . $validated['outlet_code'] . ' not found'
                    ];
                    continue;
                }

                // Get moda by name
                $moda = Moda::where('name', $validated['moda_name'])->first();
                if (!$moda) {
                    $this->errors[] = [
                        'row' => $index + 2,
                        'error' => 'Moda with name ' . $validated['moda_name'] . ' not found'
                    ];
                    continue;
                }

                // Check if target already exists
                $existingTarget = IncomeTarget::where('outlet_id', $outlet->id)
                                             ->where('moda_id', $moda->id)
                                             ->where('target_year', $validated['target_year'])
                                             ->where('target_month', $validated['target_month'])
                                             ->first();

                if ($existingTarget) {
                    $this->errors[] = [
                        'row' => $index + 2,
                        'error' => 'Target already exists for outlet ' . $outlet->name . ', moda ' . $moda->name . ' and month ' . $validated['target_month'] . '/' . $validated['target_year']
                    ];
                    continue;
                }

                // Create the income target
                IncomeTarget::create([
                    'outlet_id' => $outlet->id,
                    'moda_id' => $moda->id,
                    'target_year' => $validated['target_year'],
                    'target_month' => $validated['target_month'],
                    'target_amount' => $validated['target_amount'],
                    'description' => $validated['description'] ?? null,
                    'assigned_by' => $userId,
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
            'outlet_code' => 'required|string|exists:outlets,code',
            'moda_name' => 'required|string|exists:modas,name',
            'target_year' => 'required|integer|min:2000|max:2100',
            'target_month' => 'required|integer|min:1|max:12',
            'target_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
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