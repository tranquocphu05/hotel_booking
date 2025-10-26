<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = auth()->id();

        $rules = [
            'email' => [
                'required',
                'email',
                Rule::unique('nguoi_dung', 'email')->ignore($userId),
            ],
            'ho_ten' => ['nullable', 'string', 'max:100'],
            'sdt' => [
                'nullable',
                'regex:/^(0|\+84)(3[2-9]|5[689]|7[06789]|8[1-9]|9[0-9])[0-9]{7}$/',
            ],
            'cccd' => ['nullable', 'digits:12'],
            'dia_chi' => ['nullable', 'string', 'max:255'],
        ];

        // Nếu user nhập mật khẩu mới thì thêm validate đổi mật khẩu
        if ($this->filled('password')) {
            $rules['current_password'] = ['required'];
            $rules['password'] = [
                'required',
                'confirmed',
                'min:8',
                'regex:/[a-z]/', // ít nhất 1 chữ thường
                'regex:/[A-Z]/', // ít nhất 1 chữ hoa
                'regex:/[0-9]/', // ít nhất 1 số
            ];
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('password')) {
                $user = $this->user();
                if (!Hash::check($this->input('current_password'), $user->password)) {
                    $validator->errors()->add('current_password', 'Mật khẩu hiện tại không chính xác.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email đã tồn tại.',
            'sdt.regex' => 'Số điện thoại không hợp lệ.',
            'cccd.digits' => 'CCCD phải đúng 12 chữ số.',
            'password.regex' => 'Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường và 1 số.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại để đổi mật khẩu.',
        ];
    }
}
