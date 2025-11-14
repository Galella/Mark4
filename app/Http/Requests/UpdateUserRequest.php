<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        
        return $user->isSuperAdmin() || 
               $user->isAdminWilayah() || 
               $user->isAdminArea();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = Auth::user();
        
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user->id],
        ];

        // Include password validation if provided
        if ($this->filled('password')) {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        // Role validation varies based on the current user's role
        if ($user->isSuperAdmin()) {
            $rules['role'] = ['required', 'in:admin_wilayah,admin_area,admin_outlet'];
        } elseif ($user->isAdminWilayah()) {
            $rules['role'] = ['required', 'in:admin_area,admin_outlet'];
        } elseif ($user->isAdminArea()) {
            $rules['role'] = ['required', 'in:admin_outlet'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'A user with this email address already exists.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}