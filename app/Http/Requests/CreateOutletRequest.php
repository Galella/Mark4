<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateOutletRequest extends FormRequest
{
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:outlets'],
            'office_id' => ['required', 'exists:offices,id'],
            'outlet_type_id' => ['required', 'exists:outlet_types,id'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'pic_name' => ['nullable', 'string'],
            'pic_phone' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'An outlet with this code already exists.',
            'office_id.exists' => 'The selected office does not exist.',
            'outlet_type_id.exists' => 'The selected outlet type does not exist.',
        ];
    }
}