<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\NormalizePhone;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;

class RegisterRequest extends FormRequest
{
    /**
     * Xác định xem user có được phép gửi request này không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn hóa dữ liệu trước khi validate.
     */

    use NormalizePhone;
    protected function prepareForValidation()
    {
        $this->normalizePhoneField('sdt');
    }

    /**
     * Các rule để validate dữ liệu đăng ký.
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $validator = new EmailValidator();
                    $multipleValidations = new MultipleValidationWithAnd([
                        new RFCValidation(),
                        new DNSCheckValidation(),
                    ]);
                    if (!$validator->isValid($value, $multipleValidations)) {
                        $fail('Email không hợp lệ hoặc domain không tồn tại.');
                    }

                    // Kiểm tra phần đuôi domain có hợp lệ theo danh sách IANA
                    $domain = substr(strrchr($value, "@"), 1);
                    $tld = strtolower(pathinfo($domain, PATHINFO_EXTENSION));

                    $validTlds = ['com', 'vn', 'net', 'org', 'edu', 'gov', 'io', 'co']; // bạn có thể mở rộng thêm
                    if (!in_array($tld, $validTlds)) {
                        $fail("Tên miền .{$tld} không hợp lệ hoặc chưa được hỗ trợ.");
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

            'cccd' => [
                'nullable',
                'regex:/^[0-9]{12}$/',
                'unique:nguoi_dung,cccd',
            ],

            'sdt' => [
                'nullable',
                'regex:/^(03|05|07|08|09)[0-9]{8}$/', // Chuẩn số VN
                'unique:nguoi_dung,sdt',
            ],

            'dia_chi' => ['nullable', 'string', 'max:255'],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('password') && $this->filled('password_confirmation') && $this->password !== $this->password_confirmation) {
                $validator->errors()->add('password_confirmation', 'Mật khẩu xác nhận không khớp.');
            }
        });
    }

    /**
     * Các thông báo lỗi tuỳ chỉnh.
     */
    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên.',

            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',

            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa, 1 chữ thường và 1 chữ số.',

            'cccd.regex' => 'Số CCCD phải gồm đúng 12 chữ số.',
            'cccd.unique' => 'Số CCCD này đã được đăng ký.',

            'sdt.regex' => 'Số điện thoại không hợp lệ. Phải là số di động Việt Nam gồm 10 chữ số.',
            'sdt.unique' => 'Số điện thoại này đã được đăng ký.',

            'dia_chi.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
        ];
    }
}
