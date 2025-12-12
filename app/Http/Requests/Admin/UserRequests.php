<?php

namespace App\Http\Requests\Admin;

use App\Traits\NormalizePhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Illuminate\Support\Facades\Auth;

class UserRequests extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Chỉ cho phép admin thực hiện request này
        return $user && in_array($user->vai_tro, ['admin', 'nhan_vien']);
    }

    use NormalizePhone;
    protected function prepareForValidation()
    {
        $this->normalizePhoneField('sdt');
    }

    public function rules(): array
    {
        // Nếu đang cập nhật user → lấy ID hiện tại để bỏ qua unique
        $id = $this->route('user') ?? null;

        // Kiểm tra xem request là thêm mới hay cập nhật
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);

        return [
            'username' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:100',
                'regex:/^[A-Za-zÀ-ỹ\s]+$/u', // ❗ chỉ cho phép chữ cái và khoảng trắng, không số
                Rule::unique('nguoi_dung', 'username')->ignore($id),
            ],

            'email' => [
                $isUpdate ? 'sometimes' : 'required',
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

                    // Kiểm tra phần đuôi domain hợp lệ
                    $domain = substr(strrchr($value, "@"), 1);
                    $tld = strtolower(pathinfo($domain, PATHINFO_EXTENSION));
                    $validTlds = ['com', 'vn', 'net', 'org', 'edu', 'gov', 'io', 'co'];
                    if (!in_array($tld, $validTlds)) {
                        $fail("Tên miền .{$tld} không hợp lệ hoặc chưa được hỗ trợ.");
                    }
                },
                Rule::unique('nguoi_dung', 'email')->ignore($id),
            ],

            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                'min:6',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],

            'ho_ten' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[A-Za-zÀ-ỹ\s]+$/u', // ❗ chỉ cho phép chữ cái + khoảng trắng, không số
            ],

            'sdt' => ['nullable', 'regex:/^0(3|5|7|8|9)[0-9]{8}$/'],

            'cccd' => ['nullable', 'regex:/^[0-9]{12}$/'],

            'dia_chi' => [
                'nullable',
                'string',
                'max:255',
                // ❗ yêu cầu có ít nhất 1 chữ cái và 1 số
                'regex:/^(?!\d+$)[A-Za-zÀ-ỹ0-9\s,.-]+$/u',
            ],

            'vai_tro' => 'required|in:admin,nhan_vien,le_tan,khach_hang',
            'trang_thai' => 'required|in:hoat_dong,khoa',
        ];

    }

    public function messages(): array
    {
        return [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.unique' => 'Tên đăng nhập đã tồn tại.',
            'username.regex' => 'Tên đăng nhập chỉ được chứa chữ cái, không được có số hoặc ký tự đặc biệt.',

            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.unique' => 'Email này đã được sử dụng.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',

            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa, 1 chữ thường và 1 chữ số.',

            'ho_ten.regex' => 'Họ tên chỉ được chứa chữ cái và khoảng trắng, không được có số hoặc ký tự đặc biệt.',

            'sdt.regex' => 'Số điện thoại không hợp lệ. Phải là số di động Việt Nam gồm 10 chữ số.',
            'cccd.regex' => 'Số CCCD không hợp lệ. Phải gồm đúng 12 chữ số.',

            'dia_chi.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'dia_chi.regex' => 'Địa chỉ không được chỉ toàn số.',

            'vai_tro.in' => 'Vai trò không hợp lệ.',
            
            'trang_thai.in' => 'Trạng thái không hợp lệ.',
        ];
    }

}
