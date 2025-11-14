<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateIncomeTargetRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean the target_amount input: remove dots (thousands separator) and replace comma with dot (decimal separator)
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
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Defer authorization to the controller and policy
        // This avoids complex route binding issues
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'outlet_id' => ['required', 'exists:outlets,id'],
            'moda_id' => ['required', 'exists:modas,id'],
            'target_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'target_month' => ['required', 'integer', 'min:1', 'max:12'],
            'target_amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
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