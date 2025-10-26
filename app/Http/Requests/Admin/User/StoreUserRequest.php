<?php

namespace App\Http\Requests\Admin\User;

use App\Traits\NormalizePhone;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Chỉ cho phép admin thực hiện request này
        return $user->vai_tro === 'admin';

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    use NormalizePhone;
    protected function prepareForValidation()
    {
        $this->normalizePhoneField('sdt');
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:100|unique:nguoi_dung,username',
            'email' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $validator = new \Egulias\EmailValidator\EmailValidator();
                    $multipleValidations = new MultipleValidationWithAnd([
                        new RFCValidation(),
                        new DNSCheckValidation(),
                    ]);
                    if (!$validator->isValid($value, $multipleValidations)) {
                        $fail('Email không hợp lệ hoặc domain không tồn tại.');
                    }
                },
                'unique:nguoi_dung,email',
                // 'regex:/@(gmail\.com|yahoo\.com|outlook\.com)$/i'
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
            'ho_ten' => 'nullable|string|max:100',

            // Regex số điện thoại Việt Nam
            'sdt' => [
                'nullable',
                'regex:/^(\+84|0)(3|5|7|8|9)[0-9]{8}$/',
            ],

            // CCCD đúng 12 chữ số
            'cccd' => [
                'nullable',
                'regex:/^[0-9]{12}$/',
            ],

            'dia_chi' => 'nullable|string|max:255',
            'vai_tro' => 'required|in:admin,nhan_vien,khach_hang',
            'trang_thai' => 'required|in:hoat_dong,khoa',
        ];
    }


    // ✅ Thông báo lỗi tùy chỉnh (hiển thị tiếng Việt)
    public function messages(): array
    {
        return [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.unique' => 'Tên đăng nhập đã tồn tại.',

            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',

            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa, 1 chữ thường và 1 chữ số.',

            'sdt.regex' => 'Số điện thoại không hợp lệ. Phải là số di động Việt Nam gồm 10 chữ số.',

            'cccd.regex' => 'Số CCCD không hợp lệ. Phải gồm đúng 12 chữ số.',

            'vai_tro.in' => 'Vai trò không hợp lệ.',

            'trang_thai.in' => 'Trạng thái không hợp lệ.',

        ];
    }

}
