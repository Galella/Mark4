<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateIncomeTargetRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Handle single entry target_amount formatting
        if ($this->has('target_amount')) {
            $targetAmount = $this->target_amount;
            // Remove dots (thousands separator)
            $targetAmount = str_replace('.', '', $targetAmount);
            // Replace comma with dot (for decimal)
            $targetAmount = str_replace(',', '.', $targetAmount);
            
            // Update the request data with the cleaned amount
            $this->merge([
                'target_amount' => $targetAmount
            ]);
        }
        
        // Handle multiple entries target_amount formatting
        if ($this->has('entries')) {
            $entries = $this->entries;
            
            foreach ($entries as $index => $entry) {
                if (isset($entry['target_amount'])) {
                    $targetAmount = $entry['target_amount'];
                    // Remove dots (thousands separator)
                    $targetAmount = str_replace('.', '', $targetAmount);
                    // Replace comma with dot (for decimal)
                    $targetAmount = str_replace(',', '.', $targetAmount);
                    
                    // Update the entry with the cleaned amount
                    $entries[$index]['target_amount'] = $targetAmount;
                }
            }
            
            // Update the request data with cleaned entries
            $this->merge([
                'entries' => $entries
            ]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        return $user->isSuperAdmin() || $user->isAdminWilayah() || $user->isAdminArea();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        if ($this->has('entries')) {
            // For multiple entries, validate the entries array
            return [
                'entries' => ['required', 'array', 'min:1'],
                'entries.*.outlet_id' => ['required', 'exists:outlets,id'],
                'entries.*.moda_id' => ['required', 'exists:modas,id'],
                'entries.*.target_year' => ['required', 'integer', 'min:2000', 'max:2100'],
                'entries.*.target_month' => ['required', 'integer', 'min:1', 'max:12'],
                'entries.*.target_amount' => ['required', 'numeric', 'min:0'],
                'entries.*.description' => ['nullable', 'string', 'max:500'],
            ];
        } else {
            // For single entry, use the original validation rules
            return [
                'outlet_id' => ['required', 'exists:outlets,id'],
                'moda_id' => ['required', 'exists:modas,id'],
                'target_year' => ['required', 'integer', 'min:2000', 'max:2100'],
                'target_month' => ['required', 'integer', 'min:1', 'max:12'],
                'target_amount' => ['required', 'numeric', 'min:0'],
                'description' => ['nullable', 'string', 'max:500'],
            ];
        }
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'outlet_id.exists' => 'The selected outlet does not exist.',
            'target_year.min' => 'The target year must be at least 2000.',
            'target_year.max' => 'The target year must not exceed 2100.',
            'target_month.min' => 'The target month must be between 1 and 12.',
            'target_month.max' => 'The target month must be between 1 and 12.',
            'target_amount.min' => 'The target amount must be at least 0.',
        ];
    }
}