<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * 🔹 Hiển thị danh sách dịch vụ
     */
    public function index(Request $request)
    {
        $query = Service::query();

        if ($keyword = $request->input('keyword')) {
            $query->where('name', 'like', "%{$keyword}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $services = $query->orderBy('id', 'asc')->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.service.partials.table', compact('services'))->render()
            ]);
        }

        return view('admin.service.index', compact('services'));
    }

    /**
     * 🔹 Hiển thị form tạo dịch vụ (full page)
     */
    public function create()
    {
        return view('admin.Service.create');
    }

    /**
     * 🔹 Hiển thị form sửa dịch vụ (full page)
     */
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        return view('admin.Service.edit', compact('service'));
    }

    /**
     * 🔹 Thêm dịch vụ mới
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'unit' => 'nullable|string|max:50',
                'describe' => 'nullable|string|max:255',
                'status' => 'in:hoat_dong,ngung',
            ],
            [
                'name.required' => 'Vui lòng nhập tên dịch vụ.',
                'name.string' => 'Tên dịch vụ không hợp lệ.',
                'name.max' => 'Tên dịch vụ không được vượt quá 255 ký tự.',

                'price.required' => 'Vui lòng nhập giá dịch vụ.',
                'price.numeric' => 'Giá dịch vụ phải là số.',
                'price.min' => 'Giá dịch vụ không được nhỏ hơn 0.',

                'unit.string' => 'Đơn vị tính không hợp lệ.',
                'unit.max' => 'Đơn vị tính không được vượt quá 50 ký tự.',

                'describe.string' => 'Mô tả tính không hợp lệ.',
                'describe.max' => 'Mô tả không được vượt quá 255 ký tự.',

                'status.in' => 'Trạng thái không hợp lệ.',
            ]
        ); 

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $service = Service::create($validator->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Thêm dịch vụ thành công!',
                'data' => $service
            ]);
        }

        return redirect()->route('admin.service.index')->with('success', 'Thêm dịch vụ thành công!');
    }

    /**
     * 🔹 Cập nhật dịch vụ
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        if ($request->has('toggle')) {
            $service->status = $service->status === 'hoat_dong' ? 'ngung' : 'hoat_dong';
            $service->save();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $service->status === 'hoat_dong'
                        ? 'Dịch vụ đã được kích hoạt lại.'
                        : 'Dịch vụ đã được ngừng hoạt động.',
                    'new_status' => $service->status
                ]);
            }

            return redirect()->back()->with('success', $service->status === 'hoat_dong'
                ? 'Dịch vụ đã được kích hoạt lại.'
                : 'Dịch vụ đã được ngừng hoạt động.');
        }

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'unit' => 'nullable|string|max:50',
                'describe' => 'nullable|string|max:255',
                'status' => 'in:hoat_dong,ngung',
            ],
            [
                'name.required' => 'Vui lòng nhập tên dịch vụ.',
                'name.string' => 'Tên dịch vụ không hợp lệ.',
                'name.max' => 'Tên dịch vụ không được vượt quá 255 ký tự.',

                'price.required' => 'Vui lòng nhập giá dịch vụ.',
                'price.numeric' => 'Giá dịch vụ phải là số.',
                'price.min' => 'Giá dịch vụ không được nhỏ hơn 0.',

                'unit.string' => 'Đơn vị tính không hợp lệ.',
                'unit.max' => 'Đơn vị tính không được vượt quá 50 ký tự.',

                'describe.string' => 'Mô tả tính không hợp lệ.',
                'describe.max' => 'Mô tả không được vượt quá 255 ký tự.',

                'status.in' => 'Trạng thái không hợp lệ.',
            ]
        );

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        $service->update($validator->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật dịch vụ thành công!',
                'data' => $service
            ]);
        }

        return redirect()->route('admin.service.index')->with('success', 'Cập nhật dịch vụ thành công!');
    }

}
