<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateDailyIncomeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        
        // Only admin outlet can update their own records
        return $user->isAdminOutlet() && $this->dailyIncome->outlet_id === $user->outlet_id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'moda_id' => ['required', 'exists:modas,id'],
            'colly' => ['required', 'integer', 'min:0'],
            'weight' => ['required', 'numeric', 'min:0'],
            'income' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'moda_id.exists' => 'The selected transportation mode does not exist.',
            'colly.min' => 'Colly must be at least 0.',
            'weight.min' => 'Weight must be at least 0.',
            'income.min' => 'Income must be at least 0.',
        ];
    }
}