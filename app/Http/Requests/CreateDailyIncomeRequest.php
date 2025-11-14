<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateDailyIncomeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        
        return $user->isAdminOutlet();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'moda_id' => ['required_without:entries', 'exists:modas,id'],
            'colly' => ['required_without:entries', 'integer', 'min:0'],
            'weight' => ['required_without:entries', 'numeric', 'min:0'],
            'income' => ['required_without:entries', 'numeric', 'min:0'],
            'entries' => ['nullable', 'array'],
            'entries.*.moda_id' => ['required', 'exists:modas,id'],
            'entries.*.colly' => ['required', 'integer', 'min:0'],
            'entries.*.weight' => ['required', 'numeric', 'min:0'],
            'entries.*.income' => ['required', 'numeric', 'min:0'],
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