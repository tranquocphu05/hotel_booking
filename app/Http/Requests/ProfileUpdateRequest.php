<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ho_ten' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('nguoi_dung')->ignore($this->user()->id),
            ],
            'sdt' => ['nullable', 'string', 'max:15'],
            'cccd' => ['nullable', 'string', 'max:20'],
            'dia_chi' => ['nullable', 'string', 'max:500'],
        ];
    }
}
