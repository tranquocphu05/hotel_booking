<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InvoiceController extends Controller
{

    public function index(Request $request)
    {
        $query = Invoice::with('datPhong.user');

        if ($request->filled('user_id')) {
            $query->whereHas('datPhong', function ($q) use ($request) {
                $q->where('nguoi_dung_id', $request->user_id);
            });
        }

        // Mặc định chỉ hiển thị hóa đơn đã thanh toán nếu không truyền filter
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->status);
        } else {
            $query->where('trang_thai', 'da_thanh_toan');
            // Gắn vào request để filter UI (nếu có) hiển thị đúng trạng thái
            $request->merge(['status' => 'da_thanh_toan']);
        }

        $invoices = $query->latest()->paginate(10);
        $users = User::where('vai_tro', 'khach_hang')->get();

        return view('admin.invoices.index', compact('invoices', 'users'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('datPhong.user', 'datPhong.phong.loaiPhong');
        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice = Invoice::findOrFail($id);
        return view('admin.invoices.edit', compact('invoice'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'trang_thai' => 'required|in:chua_thanh_toan,da_thanh_toan,hoan_tien',
        ]);

        $invoice = Invoice::findOrFail($id);
        $invoice->update($request->only('trang_thai'));

        // Đồng bộ trạng thái đặt phòng khi hóa đơn đã thanh toán
        if ($invoice->trang_thai === 'da_thanh_toan' && $invoice->datPhong) {
            if ($invoice->datPhong->trang_thai === 'cho_xac_nhan') {
                $invoice->datPhong->trang_thai = 'da_xac_nhan';
                $invoice->datPhong->save();
            }
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Cập nhật trạng thái hóa đơn thành công.');
    }
    public function create(){

    }

}

