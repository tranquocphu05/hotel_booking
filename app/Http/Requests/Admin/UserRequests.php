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
                $isUpdate ? 'sometimes' : 'required', // nếu update có thể không gửi
                'string',
                'max:100',
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
                },
                Rule::unique('nguoi_dung', 'email')->ignore($id),
            ],

            // Khi thêm mới: bắt buộc password, khi cập nhật: có thể bỏ trống
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                'min:6',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],

            'ho_ten' => 'nullable|string|max:100',

            'sdt' => ['nullable', 'regex:/^0(3|5|7|8|9)[0-9]{8}$/'],

            'cccd' => [
                'nullable',
                'regex:/^[0-9]{12}$/',
            ],

            'dia_chi' => 'nullable|string|max:255',
            'vai_tro' => 'required|in:admin,nhan_vien,khach_hang',
            'trang_thai' => 'required|in:hoat_dong,khoa',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.unique' => 'Tên đăng nhập đã tồn tại.',

            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.unique' => 'Email này đã được sử dụng.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',

            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa, chữ thường và 1 chữ số.',

            'sdt.regex' => 'Số điện thoại không hợp lệ. Phải là số di động Việt Nam gồm 10 chữ số.',
            'cccd.regex' => 'Số CCCD không hợp lệ. Phải gồm đúng 12 chữ số.',

            'vai_tro.in' => 'Vai trò không hợp lệ.',
            'trang_thai.in' => 'Trạng thái không hợp lệ.',
        ];
    }
}
