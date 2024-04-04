<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'password' => 'required|min:8',
            'nomor_telepon' => 'nullable|string|max:15',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string|max:50',
        ];
    }
}
