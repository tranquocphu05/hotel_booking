<?php
namespace App\Http\Controllers\Admin;
use App\Models\User;
use App\Models\Phong;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\DatPhong;
use App\Models\ThanhToan;
use Illuminate\Http\Request;
use App\Models\RefundService;
use App\Exports\InvoiceExport;
use App\Models\BookingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Services\BookingPriceCalculator;

class InvoiceController extends Controller
{

    public function index(Request $request)
    {
        $query = Invoice::with(['datPhong' => function($q) {
            $q->with('user', 'loaiPhong');
        }]);

        if ($request->filled('user_id')) {
            $query->whereHas('datPhong', function ($q) use ($request) {
                $q->where('nguoi_dung_id', $request->user_id);
            });
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->status);
        }

        // Filter by invoice type (EXTRA or PREPAID). Map legacy types to PREPAID.
        $typeFilter = $request->input('invoice_type', null);
        if ($typeFilter) {
            $t = strtoupper(trim($typeFilter));
            if ($t === 'EXTRA') {
                $query->where('invoice_type', 'EXTRA');
            } elseif ($t === 'PREPAID') {
                // treat several legacy/alternate values as PREPAID
                $query->whereIn('invoice_type', ['PREPAID', 'STANDARD', 'OFFLINE']);
            }
        }
        // Note: Removed default filter to show all invoices unless explicitly filtered

        // Eager load relationships to avoid N+1 queries
        $query->with(['datPhong.user', 'datPhong.loaiPhong']);

        // Get paginated results
        $invoices = $query->latest()->paginate(5);

        // Cache users list (15 minutes) - rarely changes
        $users = \Illuminate\Support\Facades\Cache::remember('users_khach_hang', 900, function () {
            return User::where('vai_tro', 'khach_hang')->get();
        });

        return view('admin.invoices.index', compact('invoices', 'users'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['datPhong' => function($q) {
            $q->with('user', 'loaiPhong', 'phongs');
        }]);

        $booking = $invoice->datPhong;
        $services = [];
        $serviceTotal = 0;
        $refundedServiceIds = collect(); // Track which services have been refunded
        
        if ($invoice->invoice_type === 'EXTRA') {
            // Only show services for this invoice
            $services = BookingService::with('service', 'phong')
                ->where('invoice_id', $invoice->id)
                ->get();
            $serviceTotal = $services->sum(function($bs) {
                return ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
            });
        } elseif ($invoice->invoice_type === 'REFUND') {
            // REFUND: show only services for this refund invoice (negative quantities)
            $services = BookingService::with('service', 'phong')
                ->where('invoice_id', $invoice->id)
                ->get();
            $serviceTotal = $services->sum(function($bs) {
                return ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
            });
        } else {
            // PREPAID: show all booking services (legacy: invoice_id NULL or matches main invoice)
            // Include all services including those with quantity <= 0 (refunded services)
            $services = BookingService::with('service', 'phong')
                ->where('dat_phong_id', $invoice->dat_phong_id)
                ->where(function($q) use ($invoice) {
                    $q->whereNull('invoice_id')->orWhere('invoice_id', $invoice->id);
                })
                ->get();
            $serviceTotal = $services->sum(function($bs) {
                return ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
            });
            
            // Check which services have been refunded (via refund invoices)
            if ($booking) {
                $refundInvoices = Invoice::where('original_invoice_id', $invoice->id)
                    ->where('invoice_type', 'REFUND')
                    ->pluck('id');
                
                if ($refundInvoices->isNotEmpty()) {
                    // Get all services that have negative quantities in refund invoices
                    $refundedServices = BookingService::where('dat_phong_id', $booking->id)
                        ->whereIn('invoice_id', $refundInvoices)
                        ->where('quantity', '<', 0)
                        ->get();
                    
                    // Create a map: service_id + phong_id + used_at => true if refunded
                    $refundedServiceIds = $refundedServices->mapWithKeys(function($bs) {
                        $key = $bs->service_id . '_' . ($bs->phong_id ?? 'null') . '_' . ($bs->used_at ? date('Y-m-d', strtotime($bs->used_at)) : 'null');
                        return [$key => true];
                    });
                }
            }
        }
        // Compute remaining removable quantity per service across the booking
        $serviceAvailability = [];
        $roomMap = [];
        $bookingServiceOptions = [];
        
        // For REFUND invoices, get options from original invoice
        $sourceInvoice = $invoice;
        if ($invoice->invoice_type === 'REFUND' && $invoice->originalInvoice) {
            $sourceInvoice = $invoice->originalInvoice;
        }
        
        if ($booking) {
            $allSvcRows = BookingService::where('dat_phong_id', $booking->id)->get()->groupBy('service_id');
            foreach ($allSvcRows as $svcId => $rows) {
                $pos = $rows->where('quantity', '>', 0)->sum('quantity');
                $neg = $rows->where('quantity', '<', 0)->sum('quantity'); // negative number
                $remaining = $pos + $neg; // remaining positive usage
                $serviceAvailability[$svcId] = max(0, intval($remaining));
            }

            // Prepare room map for modal dropdown (id => so_phong)
            $phongIds = $booking->getPhongIds();
            if (is_string($phongIds)) {
                $phongIds = json_decode($phongIds, true) ?: [];
            }
            $phongIds = is_array($phongIds) ? array_filter($phongIds) : [];
            if (!empty($phongIds)) {
                $roomMap = Phong::whereIn('id', $phongIds)->pluck('so_phong', 'id')->toArray();
            }

            // Build per-booking-service options
            // For creating refund invoices from original invoice: Show ALL services in the invoice
            // For REFUND invoices: exclude services already refunded in this refund invoice
            
            if (!$invoice->isRefund() && $invoice->trang_thai === 'da_thanh_toan') {
                // When creating refund invoice from original invoice, show ALL services in the invoice
                // Get all services displayed in the original invoice
                $displayedServices = $services; // Services already loaded for display
                
                // Process each displayed service
                $processedServices = [];
                
                foreach ($displayedServices as $displayedService) {
                    $qty = $displayedService->quantity ?? 0;
                    $note = $displayedService->note ?? '';
                    
                    // Check if this service is marked as refunded (for display purposes)
                    $isRefunded = false;
                    
                    // Method 1: quantity <= 0 indicates refunded
                    if ($qty <= 0) {
                        $isRefunded = true;
                    }
                    
                    // Method 2: Check if there are negative adjustments for this service in original invoice
                    $negativeAdjustments = BookingService::where('dat_phong_id', $booking->id)
                        ->where('service_id', $displayedService->service_id)
                        ->where('phong_id', $displayedService->phong_id)
                        ->whereDate('used_at', date('Y-m-d', strtotime($displayedService->used_at)))
                        ->where('invoice_id', $sourceInvoice->id)
                        ->where('quantity', '<', 0)
                        ->sum('quantity');
                    
                    if ($negativeAdjustments < 0) {
                        $isRefunded = true;
                    }
                    
                    // Method 3: Check note for refund keywords (check both displayed service and all related rows)
                    $hasRefundNote = false;
                    if (stripos($note, 'hoàn') !== false || stripos($note, 'refund') !== false || stripos($note, 'Đã hoàn') !== false) {
                        $hasRefundNote = true;
                        $isRefunded = true;
                    } else {
                        // Check all BookingService rows for this service to find refund notes
                        $allServiceRows = BookingService::where('dat_phong_id', $booking->id)
                            ->where('service_id', $displayedService->service_id)
                            ->where('phong_id', $displayedService->phong_id)
                            ->whereDate('used_at', date('Y-m-d', strtotime($displayedService->used_at)))
                            ->where(function($q) use ($sourceInvoice) {
                                $q->whereNull('invoice_id')->orWhere('invoice_id', $sourceInvoice->id);
                            })
                            ->get();
                        
                        foreach ($allServiceRows as $row) {
                            $rowNote = $row->note ?? '';
                            if (stripos($rowNote, 'hoàn') !== false || 
                                stripos($rowNote, 'refund') !== false || 
                                stripos($rowNote, 'Đã hoàn') !== false ||
                                stripos($rowNote, 'Dịch vụ dư') !== false) {
                                $hasRefundNote = true;
                                $isRefunded = true;
                                $note = $rowNote; // Use the note from the row that has refund indication
                                break;
                            }
                        }
                    }
                    
                    // Show ALL services, not just refunded ones
                    // if (!$isRefunded) continue;
                    
                    // Find the original positive row for this service
                    $originalRow = BookingService::where('dat_phong_id', $booking->id)
                        ->where('service_id', $displayedService->service_id)
                        ->where('phong_id', $displayedService->phong_id)
                        ->whereDate('used_at', date('Y-m-d', strtotime($displayedService->used_at)))
                        ->where('quantity', '>', 0)
                        ->where(function($q) use ($sourceInvoice) {
                            $q->whereNull('invoice_id')->orWhere('invoice_id', $sourceInvoice->id);
                        })
                        ->first();
                    
                    // If no original positive row found, use the displayed service itself
                    if (!$originalRow) {
                        $originalRow = $displayedService;
                    }
                    
                    $key = $originalRow->service_id . '_' . ($originalRow->phong_id ?? 'null') . '_' . ($originalRow->used_at ? date('Y-m-d', strtotime($originalRow->used_at)) : 'null');
                    if (isset($processedServices[$key])) continue;
                    
                    // Calculate remaining refundable quantity
                    // Get original quantity (positive quantity from original row)
                    $originalQuantity = $originalRow->quantity ?? 0;
                    if ($originalQuantity <= 0) {
                        // If original row has no positive quantity, try to find it
                        $anyPositive = BookingService::where('dat_phong_id', $booking->id)
                            ->where('service_id', $displayedService->service_id)
                            ->where('phong_id', $displayedService->phong_id)
                            ->whereDate('used_at', date('Y-m-d', strtotime($displayedService->used_at)))
                            ->where('quantity', '>', 0)
                            ->where(function($q) use ($sourceInvoice) {
                                $q->whereNull('invoice_id')->orWhere('invoice_id', $sourceInvoice->id);
                            })
                            ->first();
                        if ($anyPositive) {
                            $originalQuantity = $anyPositive->quantity;
                        } else {
                            // If no positive quantity found, use current quantity if > 0, otherwise default to 1
                            $originalQuantity = max(1, $qty > 0 ? $qty : 1);
                        }
                    }
                    
                    // Calculate already refunded in separate refund invoices
                    $refundInvoices = Invoice::where('original_invoice_id', $sourceInvoice->id)
                        ->where('invoice_type', 'REFUND')
                        ->pluck('id');
                    
                    $alreadyRefundedInRefundInvoices = 0;
                    if ($refundInvoices->isNotEmpty()) {
                        $alreadyRefundedInRefundInvoices = abs(BookingService::where('dat_phong_id', $booking->id)
                            ->where('service_id', $displayedService->service_id)
                            ->where('phong_id', $displayedService->phong_id)
                            ->whereDate('used_at', date('Y-m-d', strtotime($displayedService->used_at)))
                            ->whereIn('invoice_id', $refundInvoices)
                            ->where('quantity', '<', 0)
                            ->sum('quantity'));
                    }
                    
                    // Calculate already refunded in original invoice (negative adjustments)
                    $alreadyRefundedInOriginalInvoice = abs($negativeAdjustments);
                    
                    // Calculate remaining = original - already refunded
                    $remaining = max(0, $originalQuantity - $alreadyRefundedInRefundInvoices - $alreadyRefundedInOriginalInvoice);
                    
                    // Show ALL services, even if remaining is 0 (user can still see what was refunded)
                    // Only skip if we can't determine the service
                    if ($originalQuantity <= 0 && $qty <= 0 && !isset($originalRow->service_id)) {
                        continue;
                    }
                    
                    $svc = $originalRow->service;
                    $bookingServiceOptions[] = [
                        'id' => $originalRow->id,
                        'service_id' => $originalRow->service_id,
                        'service_name' => $svc ? ($svc->name ?? 'Dịch vụ') : ($originalRow->service_name ?? 'Dịch vụ'),
                        'so_phong' => $originalRow->phong ? ($originalRow->phong->so_phong ?? $originalRow->phong_id) : $originalRow->phong_id,
                        'phong_id' => $originalRow->phong_id,
                        'used_at' => date('Y-m-d', strtotime($originalRow->used_at)),
                        'used_at_display' => date('d/m/Y', strtotime($originalRow->used_at)),
                        'remaining' => $remaining, // Show remaining refundable quantity
                        'unit_price' => $originalRow->unit_price ?? 0,
                    ];
                    
                    $processedServices[$key] = true;
                }
            } else {
                // For other cases (viewing refund invoice, etc.), use original logic
                $svcRows = BookingService::where('dat_phong_id', $booking->id)
                    ->where(function($q) use ($sourceInvoice) {
                        if ($sourceInvoice->invoice_type === 'EXTRA') {
                            $q->where('invoice_id', $sourceInvoice->id);
                        } else {
                            $q->whereNull('invoice_id')->orWhere('invoice_id', $sourceInvoice->id);
                        }
                    })->get();

                foreach ($svcRows as $row) {
                    // only consider original positive quantities as selectable
                    if (!isset($row->quantity) || $row->quantity <= 0) continue;

                    // Sum negative adjustments that match same service, room and date
                    $negQuery = BookingService::where('dat_phong_id', $booking->id)
                        ->where('service_id', $row->service_id)
                        ->where('phong_id', $row->phong_id)
                        ->whereDate('used_at', date('Y-m-d', strtotime($row->used_at)))
                        ->where('quantity', '<', 0);
                    
                    // If this is a refund invoice view, exclude services already refunded in this refund invoice
                    if ($invoice->invoice_type === 'REFUND') {
                        $negQuery->where('invoice_id', '!=', $invoice->id);
                    }
                    
                    $negSum = $negQuery->sum('quantity');
                    $remaining = max(0, intval($row->quantity + $negSum));
                    
                    if ($invoice->isRefund() && $remaining <= 0) {
                        continue; // already fully refunded in this refund invoice
                    }

                    $svc = $row->service;
                    $bookingServiceOptions[] = [
                        'id' => $row->id,
                        'service_id' => $row->service_id,
                        'service_name' => $svc ? ($svc->name ?? 'Dịch vụ') : ($row->service_name ?? 'Dịch vụ'),
                        'so_phong' => $row->phong ? ($row->phong->so_phong ?? $row->phong_id) : $row->phong_id,
                        'phong_id' => $row->phong_id,
                        'used_at' => date('Y-m-d', strtotime($row->used_at)),
                        'used_at_display' => date('d/m/Y', strtotime($row->used_at)),
                        'remaining' => $remaining,
                        'unit_price' => $row->unit_price ?? 0,
                    ];
                }
            }
        }

        return view('admin.invoices.show', compact('invoice', 'services', 'serviceTotal', 'serviceAvailability', 'roomMap', 'bookingServiceOptions', 'refundedServiceIds'));
    }

    /**
     * Handle single adjustment (bớt dịch vụ) submitted from the invoice show modal.
     */
    public function adjust(Request $request, Invoice $invoice)
    {
        // Load booking first
        $booking = $invoice->datPhong;
        if (!$booking) {
            return redirect()->back()->with('error', 'Đặt phòng không tồn tại.');
        }

        // Nếu hóa đơn đã thanh toán và không phải EXTRA/REFUND, tự động tạo hóa đơn hoàn tiền
        if ($invoice->trang_thai === 'da_thanh_toan' && !$invoice->isRefund() && !$invoice->isExtra()) {
            // Chuyển hướng sang tạo hóa đơn hoàn tiền tự động
            return $this->createRefundInvoice($request, $invoice);
        }
        
        // Log incoming request for debugging
        try {
            Log::info('InvoiceController:adjust - Request received', [
                'invoice_id' => $invoice->id,
                'invoice_dat_phong_id' => $invoice->dat_phong_id,
                'booking_id' => $booking->id,
                'has_adjustments' => $request->has('adjustments'),
                'adjustments_count' => is_array($request->input('adjustments')) ? count($request->input('adjustments')) : 0,
                'adjustments' => $request->input('adjustments'),
                'all_input' => $request->all()
            ]);
        } catch (\Throwable $e) {
            // Ignore logging errors
        }
        
        // Kiểm tra xem có adjustments trong request không
        $hasAdjustmentsInRequest = $request->has('adjustments');
        $adjustmentsInput = $request->input('adjustments', []);
        
        // Log để debug - log cả raw input để xem Laravel parse như thế nào
        try {
            Log::info('InvoiceController:adjust - Before validation', [
                'hasAdjustmentsInRequest' => $hasAdjustmentsInRequest,
                'adjustmentsInput' => $adjustmentsInput,
                'adjustmentsInput_type' => gettype($adjustmentsInput),
                'adjustmentsInput_is_array' => is_array($adjustmentsInput),
                'adjustmentsInput_count' => is_array($adjustmentsInput) ? count($adjustmentsInput) : 0,
                'all_request_keys' => array_keys($request->all()),
                'raw_post_data' => $request->all(),
                'adjustments_structure' => is_array($adjustmentsInput) ? array_map(function($item) {
                    return is_array($item) ? array_keys($item) : gettype($item);
                }, $adjustmentsInput) : null
            ]);
        } catch (\Throwable $e) {}
        
        $rules = [
            'booking_service_id' => 'nullable|integer|exists:booking_services,id',
            'quantity' => 'nullable|integer|min:1',
            'create_refund' => 'nullable|in:0,1',
            'refund_method' => 'nullable|in:tien_mat,chuyen_khoan,cong_thanh_toan',
            'refund_account_number' => 'required_if:refund_method,chuyen_khoan|nullable|string|max:200',
            'refund_account_name' => 'required_if:refund_method,chuyen_khoan|nullable|string|max:200',
            'refund_bank_name' => 'required_if:refund_method,chuyen_khoan|nullable|string|max:200',
        ];
        
        // Kiểm tra xem có adjustments thực sự không
        $hasValidAdjustments = false;
        if ($hasAdjustmentsInRequest && is_array($adjustmentsInput) && count($adjustmentsInput) > 0) {
            // Kiểm tra xem có ít nhất một entry hợp lệ không
            foreach ($adjustmentsInput as $item) {
                if (is_array($item) && 
                    isset($item['booking_service_id']) && 
                    isset($item['quantity']) &&
                    !empty($item['booking_service_id']) &&
                    !empty($item['quantity']) &&
                    intval($item['booking_service_id']) > 0 &&
                    intval($item['quantity']) > 0) {
                    $hasValidAdjustments = true;
                    break;
                }
            }
        }
        
        // Nếu có adjustments hợp lệ trong request, validate nó
        if ($hasValidAdjustments) {
            $rules['adjustments'] = 'required|array|min:1';
            $rules['adjustments.*.booking_service_id'] = 'required|integer|exists:booking_services,id';
            $rules['adjustments.*.quantity'] = 'required|integer|min:1';
            $rules['adjustments.*.used_at'] = 'nullable|date';
            $rules['adjustments.*.note'] = 'nullable|string|max:500';
        } else {
            // Nếu không có adjustments hợp lệ
            if ($hasAdjustmentsInRequest) {
                // Có key nhưng không có dữ liệu hợp lệ - vẫn yêu cầu
                $rules['adjustments'] = 'required|array|min:1';
                $rules['adjustments.*.booking_service_id'] = 'required|integer|exists:booking_services,id';
                $rules['adjustments.*.quantity'] = 'required|integer|min:1';
            } else {
                // Không có key - yêu cầu phải có
                $rules['adjustments'] = 'required|array|min:1';
            }
        }
        
        try {
            $validated = $request->validate($rules, [
                'adjustments.required' => 'Vui lòng chọn ít nhất một dịch vụ để xóa.',
                'adjustments.min' => 'Vui lòng chọn ít nhất một dịch vụ để xóa.',
                'adjustments.*.booking_service_id.required' => 'Vui lòng chọn dịch vụ để xóa.',
                'adjustments.*.booking_service_id.exists' => 'Dịch vụ không tồn tại.',
                'adjustments.*.quantity.required' => 'Vui lòng nhập số lượng.',
                'adjustments.*.quantity.min' => 'Số lượng phải lớn hơn 0.',
            ]);
            
            // Log sau khi validate thành công
            try {
                Log::info('InvoiceController:adjust - Validation passed', [
                    'validated_adjustments' => $validated['adjustments'] ?? null
                ]);
            } catch (\Throwable $e) {}
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            
            // Log chi tiết để debug
            try {
                Log::error('InvoiceController:adjust - Validation failed', [
                    'errors' => $errors,
                    'input' => $request->all(),
                    'adjustments_input' => $adjustmentsInput,
                    'adjustments_input_dump' => var_export($adjustmentsInput, true),
                    'hasAdjustmentsInRequest' => $hasAdjustmentsInRequest,
                    'hasValidAdjustments' => $hasValidAdjustments ?? false,
                    'request_method' => $request->method(),
                    'content_type' => $request->header('Content-Type'),
                    'all_keys' => array_keys($request->all())
                ]);
            } catch (\Throwable $logErr) {}
            
            // Tạo thông báo lỗi chi tiết hơn
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $errorMessages[] = $message;
                }
            }
            
            $errorMessage = !empty($errorMessages) 
                ? implode(' ', $errorMessages) 
                : 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.';
            
            return redirect()->back()
                ->withErrors($errors)
                ->withInput()
                ->with('error', $errorMessage);
        }

        // Read batch adjustments after validation
        $adjustments = $request->input('adjustments', null);
        $hasAdjustments = is_array($adjustments) && count($adjustments) > 0;
        
        // Log để debug
        try {
            Log::info('InvoiceController:adjust - Processing adjustments after validation', [
                'adjustments_type' => gettype($adjustments),
                'adjustments_is_array' => is_array($adjustments),
                'adjustments_count' => is_array($adjustments) ? count($adjustments) : 0,
                'adjustments_content' => $adjustments,
                'hasAdjustments' => $hasAdjustments
            ]);
        } catch (\Throwable $e) {}

        // If this is NOT a batch request, validate the single booking_service inputs
        if (!$hasAdjustments || !is_array($adjustments) || count($adjustments) === 0) {
            $origId = intval($request->input('booking_service_id'));
            $qty = intval($request->input('quantity'));

            $original = BookingService::find($origId);
            if (!$original || ($original->dat_phong_id ?? null) != ($booking->id ?? null)) {
                return redirect()->back()->with('error', 'Dòng dịch vụ không hợp lệ.');
            }

            // Determine remaining for this exact booking service (match by service_id, phong_id, used_at)
            $pos = $original->quantity > 0 ? intval($original->quantity) : 0;
            $neg = BookingService::where('dat_phong_id', $booking->id)
                ->where('service_id', $original->service_id)
                ->where('phong_id', $original->phong_id)
                ->whereDate('used_at', date('Y-m-d', strtotime($original->used_at)))
                ->where('quantity', '<', 0)
                ->sum('quantity');
            $remaining = max(0, intval($pos + $neg));

            if ($qty > $remaining) {
                return redirect()->back()->with('error', "Số lượng giảm không được vượt quá số lượng đã đặt ({$remaining}).");
            }
        }

        // Support batch adjustments if 'adjustments' array is provided
        if (is_array($adjustments) && count($adjustments) > 0) {
            // Log incoming payload for debugging when admins report "Dòng dịch vụ không hợp lệ"
            try { Log::info('InvoiceController:adjust received adjustments', ['invoice_id' => $invoice->id, 'payload' => $adjustments]); } catch (\Throwable $_) {}

            // Normalize adjustments into sequential array (helps when input keys are booking_service ids)
            $normalized = [];
            foreach ($adjustments as $k => $v) {
                if (is_array($v) && isset($v['booking_service_id'])) {
                    $normalized[] = [
                        'booking_service_id' => intval($v['booking_service_id']),
                        'quantity' => intval($v['quantity'] ?? 0),
                        'used_at' => $v['used_at'] ?? null,
                        'note' => $v['note'] ?? null,
                    ];
                } else {
                    // Handle scalar/legacy submissions gracefully
                    $asInt = intval($v);
                    $qty = intval($request->input("adjustments.$k.quantity", 0));
                    if ($asInt > 0 && $qty <= 0) {
                        // when a scalar id is provided, default quantity to 1 if not present
                        $qty = 1;
                    }
                    $normalized[] = [
                        'booking_service_id' => $asInt,
                        'quantity' => $qty,
                        'used_at' => null,
                        'note' => null,
                    ];
                }
            }
            $adjustments = $normalized;

            DB::beginTransaction();
            try {
                $totalAdjustAmount = 0;
                $notes = [];

                foreach ($adjustments as $key => $adj) {
                    $adjBsId = intval($adj['booking_service_id'] ?? $key);
                    $adjQty = intval($adj['quantity'] ?? 0);
                    if ($adjQty <= 0) continue;

                    $orig = BookingService::find($adjBsId);
                    
                    // Log để debug
                    try {
                        Log::info('InvoiceController:adjust - Processing adjustment', [
                            'adjustment' => $adj,
                            'adjBsId' => $adjBsId,
                            'adjQty' => $adjQty,
                            'orig_found' => !!$orig,
                            'orig_dat_phong_id' => $orig ? $orig->dat_phong_id : null,
                            'booking_id' => $booking->id ?? null,
                            'invoice_id' => $invoice->id,
                            'invoice_dat_phong_id' => $invoice->dat_phong_id ?? null
                        ]);
                    } catch (\Throwable $e) {}
                    
                    if (!$orig) {
                        throw new \Exception('Không tìm thấy dịch vụ với id: ' . $adjBsId . '. Vui lòng kiểm tra lại.');
                    }
                    
                    if (($orig->dat_phong_id ?? null) != ($booking->id ?? null)) {
                        throw new \Exception('Dịch vụ id ' . $adjBsId . ' không thuộc về đặt phòng này. Dịch vụ thuộc booking_id: ' . ($orig->dat_phong_id ?? 'null') . ', nhưng invoice thuộc booking_id: ' . ($booking->id ?? 'null'));
                    }

                    $pos = $orig->quantity > 0 ? intval($orig->quantity) : 0;
                    $neg = BookingService::where('dat_phong_id', $booking->id)
                        ->where('service_id', $orig->service_id)
                        ->where('phong_id', $orig->phong_id)
                        ->whereDate('used_at', date('Y-m-d', strtotime($adj['used_at'] ?? $orig->used_at)))
                        ->where('quantity', '<', 0)
                        ->sum('quantity');
                    $remainingForRow = max(0, intval($pos + $neg));

                    if ($adjQty > $remainingForRow) {
                        throw new \Exception("Số lượng giảm không được vượt quá số lượng đã đặt ({$remainingForRow}) cho dịch vụ id {$adjBsId}.");
                    }

                    $unit = $orig && $orig->unit_price ? $orig->unit_price : (Service::find($orig->service_id)->price ?? 0);

                    // Avoid duplicate unique key error: if a negative adjustment row already exists for the same
                    // dat_phong/service/used_at/phong/invoice, update its quantity instead of inserting
                    $usedDate = date('Y-m-d', strtotime($adj['used_at'] ?? $orig->used_at));
                    $existingNeg = BookingService::where('dat_phong_id', $booking->id)
                        ->where('service_id', $orig->service_id)
                        ->where('phong_id', $orig->phong_id)
                        ->whereDate('used_at', $usedDate)
                        ->where('invoice_id', $invoice->id)
                        ->first();

                    if ($existingNeg) {
                        // extend the negative quantity
                        $existingNeg->quantity = intval($existingNeg->quantity) - abs($adjQty);
                        $existingNeg->note = trim(((string)$existingNeg->note ?: '') . ' ' . ($adj['note'] ?? ($request->input('note') ?? 'Dịch vụ dư – khách không dùng')));
                        $existingNeg->save();
                        try { Log::info('InvoiceController:adjust merged into existing negative row', ['existing_id' => $existingNeg->id, 'invoice_id' => $invoice->id, 'adj_qty' => $adjQty]); } catch (\Throwable $_) {}
                        $amt = -1 * abs($adjQty) * ($existingNeg->unit_price ?? $unit);
                    } else {
                        $bs = BookingService::create([
                            'dat_phong_id' => $booking->id,
                            'phong_id' => $orig->phong_id,
                            'invoice_id' => $invoice->id,
                            'service_id' => $orig->service_id,
                            'quantity' => -1 * abs($adjQty),
                            'unit_price' => $unit,
                            'used_at' => $usedDate,
                            'note' => $adj['note'] ?? ($request->input('note') ?? 'Dịch vụ dư – khách không dùng'),
                        ]);

                        $amt = ($bs->quantity * $bs->unit_price); // negative
                    }

                    $totalAdjustAmount += $amt;
                    $svcName = optional(Service::find($orig->service_id))->name ?? 'Dịch vụ';
                    $notes[] = "Hoàn {$adjQty} x {$svcName} (Phòng: " . ($orig->phong->so_phong ?? $orig->phong_id) . ", ngày: " . $usedDate . ")";
                }

                if ($totalAdjustAmount !== 0) {
                    // Cập nhật tổng tiền theo chênh lệch và ghi chú (hành vi cũ)
                    $invoice->tong_tien = max(0, ($invoice->tong_tien ?? 0) + $totalAdjustAmount);
                    $invoice->ghi_chu = trim(((string)$invoice->ghi_chu ?: '') . ' ' . implode('; ', $notes));
                    $invoice->save();
                    
                    // Tính lại tổng tiền từ services để đảm bảo chính xác
                    $updatedServices = BookingService::where('dat_phong_id', $booking->id)
                        ->where(function($q) use ($invoice) {
                            if ($invoice->invoice_type === 'EXTRA') {
                                $q->where('invoice_id', $invoice->id);
                            } else {
                                $q->whereNull('invoice_id')->orWhere('invoice_id', $invoice->id);
                            }
                        })
                        ->get();
                    
                    $recalculatedServiceTotal = $updatedServices->sum(function($bs) {
                        return ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
                    });
                    
                    // Cập nhật lại tổng tiền dựa trên services thực tế
                    if (!$invoice->isExtra()) {
                        $roomTotal = $invoice->tien_phong ?? 0;
                        $discount = $invoice->giam_gia ?? 0;
                        $phiPhatSinh = $invoice->phi_phat_sinh ?? 0;
                        $phiThemNguoi = $invoice->phi_them_nguoi ?? 0;
                        $invoice->tong_tien = max(0, $roomTotal - $discount + $recalculatedServiceTotal + $phiPhatSinh + $phiThemNguoi);
                    } else {
                        $phiThemNguoi = $invoice->phi_them_nguoi ?? 0;
                        $invoice->tong_tien = max(0, $recalculatedServiceTotal + $phiThemNguoi);
                    }
                    $invoice->tien_dich_vu = $recalculatedServiceTotal;
                    $invoice->save();
                }

                // Optionally create refund records with bank info
                if ($request->input('create_refund')) {
                    $adjustRows = $adjustments;
                    $totalRefundSum = 0;

                    foreach ($adjustRows as $k => $a) {
                        $rowBsId = intval($a['booking_service_id'] ?? $k);
                        $rowQty = intval($a['quantity'] ?? 0);
                        if ($rowQty <= 0) continue;
                        $origRow = BookingService::find($rowBsId);
                        if (!$origRow) continue;

                        $unit = $origRow && $origRow->unit_price ? $origRow->unit_price : (Service::find($origRow->service_id)->price ?? 0);
                        $totalForRow = round($unit * $rowQty, 2);

                        RefundService::create([
                            'hoa_don_id' => $invoice->id,
                            'dat_phong_id' => $booking->id,
                            'booking_service_id' => $rowBsId,
                            'booking_room_ids' => json_encode([['id' => $origRow->phong_id, 'quantity' => $rowQty]]),
                            'total_refund' => $totalForRow,
                            'refund_method' => in_array($request->input('refund_method'), ['tien_mat','chuyen_khoan','cong_thanh_toan']) ? $request->input('refund_method') : 'tien_mat',
                            'refund_status' => 'cho_xu_ly',
                            'bank_account_number' => $request->input('refund_account_number'),
                            'bank_account_name' => $request->input('refund_account_name'),
                            'bank_name' => $request->input('refund_bank_name'),
                            'note' => $request->input('note') ?? null,
                            'created_by' => auth()->id() ?? null,
                        ]);

                        $totalRefundSum += $totalForRow;
                    }

                    if ($totalRefundSum > 0) {
                        // Append refund note to invoice and save (no ThanhToan created here)
                        $invoice->ghi_chu = trim(((string)$invoice->ghi_chu ?: '') . ' Hoàn tiền: ' . number_format($totalRefundSum,0,',','.') . ' đ. ' . ($request->input('refund_bank_name') ? 'Ngân hàng: '.$request->input('refund_bank_name') : ''));
                        $invoice->save();
                    }
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                try { Log::error('InvoiceController:adjust batch failed - ' . $e->getMessage()); } catch (\Throwable $_) {}
                return redirect()->back()->with('error', 'Lỗi khi lưu điều chỉnh: ' . $e->getMessage());
            }

            // Refresh invoice để đảm bảo dữ liệu mới nhất
            $invoice->refresh();
            
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('success', 'Bớt dịch vụ đã được lưu thành công. Tổng tiền đã được cập nhật.');
        }

        // fallback: single-row behavior (legacy single-selection)
        DB::beginTransaction();
        try {
            // Determine unit price from the original row if possible
            $unit = $original && $original->unit_price ? $original->unit_price : (Service::find($original->service_id)->price ?? 0);

            $bs = BookingService::create([
                'dat_phong_id' => $booking->id,
                'phong_id' => $original->phong_id,
                'invoice_id' => $invoice->id,
                'service_id' => $original->service_id,
                'quantity' => -1 * abs($qty),
                'unit_price' => $unit,
                'used_at' => date('Y-m-d', strtotime($original->used_at)),
                'note' => $request->input('note') ?? 'Dịch vụ dư – khách không dùng',
            ]);

            $adjustAmount = ($bs->quantity * $bs->unit_price); // negative value

            // Cập nhật tổng tiền theo chênh lệch và ghi chú (hành vi cũ)
            $invoice->tong_tien = max(0, ($invoice->tong_tien ?? 0) + $adjustAmount);
            $svcName = optional(Service::find($original->service_id))->name ?? 'Dịch vụ';
            $append = "Hoàn {$qty} x {$svcName}.";
            $invoice->ghi_chu = trim(((string)$invoice->ghi_chu ?: '') . ' ' . $append);
            $invoice->save();
            
            // Tính lại tổng tiền từ services để đảm bảo chính xác
            $updatedServices = BookingService::where('dat_phong_id', $booking->id)
                ->where(function($q) use ($invoice) {
                    if ($invoice->invoice_type === 'EXTRA') {
                        $q->where('invoice_id', $invoice->id);
                    } else {
                        $q->whereNull('invoice_id')->orWhere('invoice_id', $invoice->id);
                    }
                })
                ->get();
            
            $recalculatedServiceTotal = $updatedServices->sum(function($bs) {
                return ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
            });
            
            // Cập nhật lại tổng tiền dựa trên services thực tế
            if (!$invoice->isExtra()) {
                $roomTotal = $invoice->tien_phong ?? 0;
                $discount = $invoice->giam_gia ?? 0;
                $phiPhatSinh = $invoice->phi_phat_sinh ?? 0;
                $phiThemNguoi = $invoice->phi_them_nguoi ?? 0;
                $invoice->tong_tien = max(0, $roomTotal - $discount + $recalculatedServiceTotal + $phiPhatSinh + $phiThemNguoi);
            } else {
                $phiThemNguoi = $invoice->phi_them_nguoi ?? 0;
                $invoice->tong_tien = max(0, $recalculatedServiceTotal + $phiThemNguoi);
            }
            $invoice->tien_dich_vu = $recalculatedServiceTotal;
            $invoice->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            try { Log::error('InvoiceController:adjust failed - ' . $e->getMessage()); } catch (\Throwable $_) {}
            return redirect()->back()->with('error', 'Lỗi khi lưu điều chỉnh: ' . $e->getMessage());
        }

        // Refresh invoice để đảm bảo dữ liệu mới nhất
        $invoice->refresh();
        
        return redirect()->route('admin.invoices.show', $invoice->id)
            ->with('success', 'Bớt dịch vụ đã được lưu thành công. Tổng tiền đã được cập nhật.');
    }

    /**
     * Export a combined/summary printable view that includes the main invoice
     * and all PAID EXTRA invoices for the same booking (by invoice id).
     */
    public function exportCombined(Invoice $invoice)
    {
        // Ensure relationships are loaded
        $invoice->load(['datPhong' => function($q) {
            $q->with('user', 'loaiPhong');
        }]);

        // Only allow combined export for the main (non-EXTRA) invoice that is paid
        if ($invoice->isExtra() || $invoice->trang_thai !== 'da_thanh_toan') {
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('error', 'Chỉ có thể xuất hóa đơn tổng cho hóa đơn chính đã thanh toán.');
        }

        // Find PAID EXTRA invoices for the same booking
        // Only include EXTRA invoices with trang_thai = 'da_thanh_toan'
        $extras = Invoice::where('dat_phong_id', $invoice->dat_phong_id)
            ->where('invoice_type', 'EXTRA')
            ->where('trang_thai', 'da_thanh_toan')
            ->where('id', '!=', $invoice->id)
            ->orderBy('ngay_tao', 'asc')
            ->get();

        // Combined total: main invoice total + sum of paid extras
        $combinedTotal = $invoice->tong_tien + $extras->sum('tong_tien');

        return view('admin.invoices.combined_print', compact('invoice', 'extras', 'combinedTotal'));
    }

    /**
     * Render the printable invoice view.
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['datPhong' => function ($q) {
            $q->with('user', 'loaiPhong');
        }]);

        return view('admin.invoices.print', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice = Invoice::findOrFail($id);
        // Prevent editing if invoice already paid or refunded
        if (in_array($invoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('error', 'Hóa đơn đã thanh toán/hoàn tiền không được phép chỉnh sửa.');
        }
        $invoice->load(['datPhong' => function($q) {
            $q->with('user', 'loaiPhong');
        }]);
        // Load services from booking. For EXTRA invoices, only load services linked to this invoice.
        $booking = $invoice->datPhong;
        $invoice = Invoice::findOrFail($id);
        // Prevent editing if invoice already paid or refunded
        if (in_array($invoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
            return redirect()->route('admin.invoices.show', $invoice->id);
        }
        $invoice->load(['datPhong' => function($q) {
            $q->with('user', 'loaiPhong');
        }]);
        $booking = $invoice->datPhong;
        $bookingServices = [];
        $roomTotalCalculated = 0;
        $currentServiceTotal = 0;
        $voucherDiscount = 0;
        $bookingServicesServer = [];
        if ($invoice->invoice_type === 'EXTRA') {
            // Only show/edit services for this invoice
            $bookingServices = BookingService::with(['service', 'phong'])
                ->where('invoice_id', $invoice->id)
                ->get();
            $currentServiceTotal = $bookingServices->sum(function($bs) {
                return ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
            });
            // Build JS structure for prefill: group by service_id and date
            foreach ($bookingServices as $bs) {
                $svcId = $bs->service_id;
                if (!isset($bookingServicesServer[$svcId])) {
                    $bookingServicesServer[$svcId] = [ 'entries' => [] ];
                }
                $bookingServicesServer[$svcId]['entries'][] = [
                    'ngay' => $bs->used_at ? date('Y-m-d', strtotime($bs->used_at)) : '',
                    'so_luong' => $bs->quantity,
                    'phong_ids' => $bs->phong_id ? [$bs->phong_id] : [],
                ];
            }
            // No room price for EXTRA
            $roomTotalCalculated = 0;
            $voucherDiscount = 0;
        } else {
            // PREPAID: show/edit all booking services (legacy: invoice_id NULL or matches main invoice)
            $bookingServices = BookingService::with(['service', 'phong'])
                ->where('dat_phong_id', $invoice->dat_phong_id)
                ->where(function($q) use ($invoice) {
                    $q->whereNull('invoice_id')->orWhere('invoice_id', $invoice->id);
                })
                ->get();
            $currentServiceTotal = $bookingServices->sum(function($bs) {
                return ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
            });
            // Build JS structure for prefill: group by service_id and date
            foreach ($bookingServices as $bs) {
                $svcId = $bs->service_id;
                if (!isset($bookingServicesServer[$svcId])) {
                    $bookingServicesServer[$svcId] = [ 'entries' => [] ];
                }
                $bookingServicesServer[$svcId]['entries'][] = [
                    'ngay' => $bs->used_at ? date('Y-m-d', strtotime($bs->used_at)) : '',
                    'so_luong' => $bs->quantity,
                    'phong_ids' => $bs->phong_id ? [$bs->phong_id] : [],
                ];
            }
            // Calculate room price for PREPAID
            $nights = 1;
            $roomTotalCalculated = 0;
            if ($booking && $booking->ngay_nhan && $booking->ngay_tra) {
                $nights = max(1, \Carbon\Carbon::parse($booking->ngay_tra)->diffInDays(\Carbon\Carbon::parse($booking->ngay_nhan)));
                
                // Calculate room total from room types
                $roomTypes = $booking->getRoomTypes();
                foreach ($roomTypes as $rt) {
                    $soLuong = $rt['so_luong'] ?? 1;
                    $loaiPhongId = $rt['loai_phong_id'] ?? null;
                    if ($loaiPhongId) {
                        $loaiPhong = \App\Models\LoaiPhong::find($loaiPhongId);
                        if ($loaiPhong) {
                            $giaCoBan = $loaiPhong->gia_co_ban ?? 0;
                            $giaKhuyenMai = $loaiPhong->gia_khuyen_mai ?? null;
                            $giaPhong = $giaKhuyenMai && $giaKhuyenMai < $giaCoBan ? $giaKhuyenMai : $giaCoBan;
                            $roomTotalCalculated += $giaPhong * $nights * $soLuong;
                        }
                    }
                }
            }
            // Calculate voucher discount if any
            $voucherDiscount = 0;
            if (!$invoice->isExtra() && $booking && $booking->voucher) {
                $voucher = $booking->voucher;
                $voucherPercent = floatval($voucher->gia_tri ?? 0);
                $voucherLoaiPhongId = $voucher->loai_phong_id ?? null;
                if ($voucherPercent > 0) {
                    $applicableTotal = 0;
                    if ($voucherLoaiPhongId) {
                        $roomTypes = $booking->getRoomTypes();
                        foreach ($roomTypes as $rt) {
                            $lpId = $rt['loai_phong_id'] ?? null;
                            if ($lpId && $lpId == $voucherLoaiPhongId) {
                                $loaiPhong = \App\Models\LoaiPhong::find($lpId);
                                if ($loaiPhong) {
                                    $giaCoBan = $loaiPhong->gia_co_ban ?? 0;
                                    $giaKhuyenMai = $loaiPhong->gia_khuyen_mai ?? null;
                                    $giaPhong = $giaKhuyenMai && $giaKhuyenMai < $giaCoBan ? $giaKhuyenMai : $giaCoBan;
                                    $soLuong = $rt['so_luong'] ?? 1;
                                    $applicableTotal += $giaPhong * $nights * $soLuong;
                                }
                            }
                        }
                    } else {
                        $applicableTotal = $roomTotalCalculated;
                    }
                    if ($applicableTotal > 0) {
                        if ($voucherPercent <= 100) {
                            $voucherDiscount = intval(round($applicableTotal * ($voucherPercent / 100)));
                        } else {
                            $voucherDiscount = intval(min(round($voucherPercent), $applicableTotal));
                        }
                    }
                }
            }
        }
        $services = Service::where('status', 'hoat_dong')->get();
        
        // Build assignedRooms with full room info for JS
        $assignedRooms = [];
        if ($booking) {
            $phongIds = $booking->getPhongIds();
            if (!empty($phongIds)) {
                $phongs = \App\Models\Phong::whereIn('id', $phongIds)
                    ->with('loaiPhong')
                    ->get();
                foreach ($phongs as $phong) {
                    $assignedRooms[] = [
                        'id' => $phong->id,
                        'so_phong' => $phong->so_phong,
                        'ten_loai' => $phong->loaiPhong ? $phong->loaiPhong->ten_loai : 'N/A',
                    ];
                }
            }
        }
        
        // Compute extra guest total from stay_guests (if any)
        $extraGuestTotal = 0;
        try {
            if ($booking) {
                $booking->loadMissing('stayGuests');
                $extraGuestTotal = $booking->stayGuests->sum(function ($g) {
                    return floatval($g->phi_them_nguoi ?? $g->extra_fee ?? $g->phu_phi_them ?? 0);
                });
            }
        } catch (\Throwable $e) {
            $extraGuestTotal = 0;
        }

        // Ensure supporting lookup data exists for view (avoid undefined variables)
        // Load active LoaiPhong collection keyed by id for quick lookups in calculations/views
        $loaiPhongs = \App\Models\LoaiPhong::where('trang_thai', 'hoat_dong')->get()->keyBy('id');

        // Assigned room ids and simple room map for JS/labels used in the edit view
        $assignedPhongIds = [];
        $roomMap = [];
        if ($booking) {
            $assignedPhongIds = $booking->getPhongIds();
            if (is_string($assignedPhongIds)) {
                $assignedPhongIds = json_decode($assignedPhongIds, true);
            }
            if (!is_array($assignedPhongIds)) {
                $assignedPhongIds = [];
            }
            if (!empty($assignedPhongIds)) {
                $phongs = \App\Models\Phong::whereIn('id', $assignedPhongIds)->get();
                foreach ($phongs as $p) {
                    $roomMap[$p->id] = $p->so_phong ?? $p->ten_phong ?? $p->id;
                }
            }
        }

        // Calculate room total properly from booking data (same as show.blade.php)
        $nights = 1;
        $roomTotalCalculated = 0;
        if ($invoice->isExtra()) {
            // For EXTRA invoices, do not include room price
            $roomTotalCalculated = 0;
        } else if ($booking && $booking->ngay_nhan && $booking->ngay_tra) {
            $checkin = \Carbon\Carbon::parse($booking->ngay_nhan);
            $checkout = \Carbon\Carbon::parse($booking->ngay_tra);
            $nights = max(1, $checkin->diffInDays($checkout));
            
            // Get room types and calculate room total using LoaiPhong promotional price
            $roomTypes = $booking->getRoomTypes();
            foreach ($roomTypes as $rt) {
                $soLuong = $rt['so_luong'] ?? 1;
                $loaiPhongId = $rt['loai_phong_id'] ?? null;
                $unit = 0;
                if ($loaiPhongId && isset($loaiPhongs[$loaiPhongId])) {
                    $lp = $loaiPhongs[$loaiPhongId];
                    if (is_object($lp)) {
                        $unit = $lp->gia_khuyen_mai ?? $lp->gia_co_ban ?? 0;
                    } else {
                        $unit = ($lp['gia_khuyen_mai'] ?? $lp['gia_co_ban'] ?? 0);
                    }
                }
                $roomTotalCalculated += $unit * $nights * $soLuong;
            }
        }
        
        // Get current service total from database
        $currentServiceTotal = 0;
        foreach ($bookingServices as $bs) {
            $currentServiceTotal += ($bs->quantity ?? 0) * ($bs->unit_price ?? 0);
        }

        // Calculate voucher discount on room subtotal (if invoice is not EXTRA)
        $voucherDiscount = 0;
        if (!$invoice->isExtra() && $booking && $booking->voucher) {
            $voucher = $booking->voucher;
            $voucherPercent = floatval($voucher->gia_tri ?? 0);
            $voucherLoaiPhongId = $voucher->loai_phong_id ?? null;

            if ($voucherPercent > 0) {
                // If voucher applies to a specific room type, compute subtotal only for that type
                $applicableTotal = 0;
                if ($voucherLoaiPhongId) {
                    $roomTypes = $booking->getRoomTypes();
                    foreach ($roomTypes as $rt) {
                        $lpId = $rt['loai_phong_id'] ?? null;
                        if ($lpId && $lpId == $voucherLoaiPhongId) {
                            $soLuong = $rt['so_luong'] ?? 1;
                            $unit = 0;
                            if ($lpId && isset($loaiPhongs[$lpId])) {
                                $lp = $loaiPhongs[$lpId];
                                if (is_object($lp)) {
                                    $unit = $lp->gia_khuyen_mai ?? $lp->gia_co_ban ?? 0;
                                } else {
                                    $unit = ($lp['gia_khuyen_mai'] ?? $lp['gia_co_ban'] ?? 0);
                                }
                            }
                            $applicableTotal += $unit * $soLuong * $nights;
                        }
                    }
                } else {
                    // Voucher applies to all rooms
                    $applicableTotal = $roomTotalCalculated;
                }

                if ($applicableTotal > 0) {
                    if ($voucherPercent <= 100) {
                        // Percentage discount
                        $voucherDiscount = intval(round($applicableTotal * ($voucherPercent / 100)));
                    } else {
                        // Fixed amount discount (cap at applicable total)
                        $voucherDiscount = intval(min(round($voucherPercent), $applicableTotal));
                    }
                }
            }
        }
        
        return view('admin.invoices.edit', compact(
            'invoice',
            'bookingServices',
            'bookingServicesServer',
            'services',
            'booking',
            'loaiPhongs',
            'assignedPhongIds',
            'roomMap',
            'assignedRooms',
            'nights',
            'roomTotalCalculated',
            'currentServiceTotal',
            'voucherDiscount',
            'extraGuestTotal'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'trang_thai' => 'required|in:cho_thanh_toan,da_thanh_toan,hoan_tien',
        ]);

        $invoice = Invoice::findOrFail($id);
        // Do not allow updating paid/refunded invoices
        if (in_array($invoice->trang_thai, ['da_thanh_toan', 'hoan_tien'])) {
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('error', 'Hóa đơn đã thanh toán/hoàn tiền không được phép chỉnh sửa.');
        }
        $booking = $invoice->datPhong;

        // Update invoice status FIRST
        $invoice->trang_thai = $request->input('trang_thai');
        if ($request->filled('phuong_thuc')) {
            $invoice->phuong_thuc = $request->input('phuong_thuc');
        }
        // Ensure invoice_type matches the invoice kind:
        // - EXTRA invoices should keep/remain 'EXTRA'
        // - Non-EXTRA (main) invoices should be set to 'PREPAID'
        if ($invoice->isExtra()) {
            $invoice->invoice_type = 'EXTRA';
        } else {
            $invoice->invoice_type = 'PREPAID';
        }
        $invoice->save();

        // Handle services if provided. Use has() / input check instead of filled()
        $servicesData = $request->input('services_data', null);
        if ($servicesData !== null) {
            // Log incoming services_data size for debugging
            try {
                Log::info('InvoiceController:update - services_data keys', ['invoice_id' => $invoice->id, 'count' => is_array($servicesData) ? count($servicesData) : 0]);
            } catch (\Throwable $e) {}

            if ($invoice->isExtra()) {
                // For EXTRA invoices: only modify services that belong to this invoice
                BookingService::where('dat_phong_id', $booking->id)
                    ->where('invoice_id', $invoice->id)
                    ->delete();

                $totalServices = 0;
                // Create new invoice-scoped service entries
                foreach ($servicesData as $svcId => $data) {
                    $service = Service::find($svcId);
                    if (!$service) continue;
                    $entries = $data['entries'] ?? [];
                    foreach ($entries as $entry) {
                        $ngay = $entry['ngay'] ?? '';
                        $qty = $entry['so_luong'] ?? 0;
                        if (!$ngay || $qty <= 0) continue;
                        
                        // Support per-entry room selection (phong_ids or phong_id)
                        $entryPhongIds = [];
                        if (isset($entry['phong_ids']) && is_array($entry['phong_ids'])) {
                            $entryPhongIds = array_filter($entry['phong_ids']);
                        } elseif (isset($entry['phong_id'])) {
                            $entryPhongIds = array_filter([$entry['phong_id']]);
                        }
                        
                        // If specific rooms are selected, create or accumulate one record per room
                        if (!empty($entryPhongIds)) {
                            foreach ($entryPhongIds as $phongId) {
                                $existing = BookingService::where('invoice_id', $invoice->id)
                                    ->where('dat_phong_id', $booking->id)
                                    ->where('service_id', $service->id)
                                    ->where('used_at', $ngay)
                                    ->where('phong_id', $phongId)
                                    ->first();

                                if ($existing) {
                                    $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                    $existing->unit_price = $service->price ?? $existing->unit_price;
                                    $existing->save();
                                } else {
                                    BookingService::create([
                                        'invoice_id' => $invoice->id,
                                        'dat_phong_id' => $booking->id,
                                        'service_id' => $service->id,
                                        'quantity' => $qty,
                                        'unit_price' => $service->price ?? 0,
                                        'used_at' => $ngay,
                                        'phong_id' => $phongId,
                                    ]);
                                }
                                $totalServices += ($qty * ($service->price ?? 0));
                            }
                        } else {
                            // No specific rooms: apply to all assigned rooms (create one row per room)
                            $assigned = $booking ? $booking->getPhongIds() : [];
                            if (is_string($assigned)) {
                                $assigned = json_decode($assigned, true) ?: [];
                            }
                            $assigned = is_array($assigned) ? array_filter($assigned) : [];

                            if (!empty($assigned)) {
                                // Create one record per assigned room
                                foreach ($assigned as $phongId) {
                                    $existing = BookingService::where('invoice_id', $invoice->id)
                                        ->where('dat_phong_id', $booking->id)
                                        ->where('service_id', $service->id)
                                        ->where('used_at', $ngay)
                                        ->where('phong_id', $phongId)
                                        ->first();

                                    if ($existing) {
                                        $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                        $existing->unit_price = $service->price ?? $existing->unit_price;
                                        $existing->save();
                                    } else {
                                        BookingService::create([
                                            'invoice_id' => $invoice->id,
                                            'dat_phong_id' => $booking->id,
                                            'service_id' => $service->id,
                                            'quantity' => $qty,
                                            'unit_price' => $service->price ?? 0,
                                            'used_at' => $ngay,
                                            'phong_id' => $phongId,
                                        ]);
                                    }
                                    $totalServices += ($qty * ($service->price ?? 0));
                                }
                            } else {
                                // No assigned rooms: still create one aggregate record with phong_id = NULL
                                $existing = BookingService::where('invoice_id', $invoice->id)
                                    ->where('dat_phong_id', $booking->id)
                                    ->where('service_id', $service->id)
                                    ->where('used_at', $ngay)
                                    ->whereNull('phong_id')
                                    ->first();

                                if ($existing) {
                                    $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                    $existing->unit_price = $service->price ?? $existing->unit_price;
                                    $existing->save();
                                } else {
                                    BookingService::create([
                                        'invoice_id' => $invoice->id,
                                        'dat_phong_id' => $booking->id,
                                        'service_id' => $service->id,
                                        'quantity' => $qty,
                                        'unit_price' => $service->price ?? 0,
                                        'used_at' => $ngay,
                                        'phong_id' => null,
                                    ]);
                                }
                                $totalServices += ($qty * ($service->price ?? 0));
                            }
                        }
                    }
                }

                // Compute extra-guest amount in one place and use it once to avoid double-counting.
                $invoiceExtraGuest = 0;
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('invoice_items')) {
                        $invoiceExtraGuest = \App\Models\InvoiceItem::where('invoice_id', $invoice->id)
                            ->where('type', 'extra_guest')
                            ->sum('amount');
                    }
                } catch (\Throwable $e) {
                    $invoiceExtraGuest = 0;
                }

                // Allow admin to pass 'phi_phat_sinh' (damage) or 'phi_them_nguoi' (extra guests)
                $submittedPhiPhatSinh = $request->input('phi_phat_sinh', null);
                $submittedPhiThemNguoi = $request->input('phi_them_nguoi', null);

                // Save damage fee separately but DO NOT add it to the service total
                if ($submittedPhiPhatSinh !== null) {
                    $invoice->phi_phat_sinh = $submittedPhiPhatSinh;
                }

                // Decide authoritative extra guest amount (preference order):
                // 1) explicitly submitted value in form
                // 2) invoice_items of type 'extra_guest' (detailed lines)
                // 3) existing invoice.phi_them_nguoi (legacy / persisted)
                // 4) aggregate from booking stay guests
                $extraGuestAmount = 0;
                if ($submittedPhiThemNguoi !== null) {
                    $extraGuestAmount = floatval($submittedPhiThemNguoi);
                } elseif ($invoiceExtraGuest > 0) {
                    $extraGuestAmount = floatval($invoiceExtraGuest);
                } elseif (!empty($invoice->phi_them_nguoi)) {
                    $extraGuestAmount = floatval($invoice->phi_them_nguoi);
                } else {
                    $extraGuestAmount = floatval($extraGuestTotal ?? 0);
                }

                // Persist the chosen extra guest amount on the invoice (do not double-add)
                $invoice->phi_them_nguoi = $extraGuestAmount;

                // Clarify semantics: For EXTRA invoices the official total should be
                // service subtotal + extra guest fee. Damage fees are stored on
                // invoice->phi_phat_sinh but NOT included in the main total here
                // (avoids accidental double-counting). Store service subtotal
                // into tien_dich_vu and compute tong_tien accordingly.
                $invoice->tien_dich_vu = $totalServices; // only service rows
                $invoice->tong_tien = ($totalServices + floatval($invoice->phi_them_nguoi ?? 0));
                $invoice->save();
            } else {
                // Existing behavior for non-EXTRA invoices (booking-scoped services)
                // Delete old booking-level and any existing invoice-scoped entries for this booking
                // to avoid duplicate rows when re-saving the invoice.
                $deletedCount = BookingService::where('dat_phong_id', $booking->id)
                    ->where(function($q) use ($invoice) {
                        $q->whereNull('invoice_id')
                          ->orWhere('invoice_id', $invoice->id);
                    })->delete();
                
                Log::info('InvoiceController:update - Deleted services', ['booking_id' => $booking->id, 'invoice_id' => $invoice->id, 'count' => $deletedCount]);
                $totalServices = 0;
                // Create new booking-scoped service entries. Support per-entry room selection
                foreach ($servicesData as $svcId => $data) {
                    $service = Service::find($svcId);
                    if (!$service) continue;

                    $entries = $data['entries'] ?? [];
                    foreach ($entries as $entry) {
                        $ngay = $entry['ngay'] ?? '';
                        $qty = $entry['so_luong'] ?? 0;
                        if (!$ngay || $qty <= 0) continue;


                        // Support per-entry room selection (phong_ids or phong_id)
                        $entryPhongIds = [];
                        if (isset($entry['phong_ids']) && is_array($entry['phong_ids'])) {
                            $entryPhongIds = array_filter($entry['phong_ids']);
                        } elseif (isset($entry['phong_id'])) {
                            $entryPhongIds = array_filter([$entry['phong_id']]);
                        }

                        Log::info('InvoiceController:update - Processing service entry', [
                            'service_id' => $svcId,
                            'service_price' => $service->price,
                            'ngay' => $ngay,
                            'qty' => $qty,
                            'entryPhongIds' => $entryPhongIds,
                        ]);

                        // Create rows based on room selection:
                        // - If specific rooms selected: one row per room
                        // - Otherwise: one row per assigned room (or NULL if none)
                        // Always check for existing (same date, room, service) and accumulate quantity
                        if (!empty($entryPhongIds)) {
                            // Specific rooms selected: create one row per room
                            foreach ($entryPhongIds as $phongId) {
                                $existing = BookingService::where('dat_phong_id', $booking->id)
                                    ->where('service_id', $service->id)
                                    ->where('used_at', $ngay)
                                    ->where('invoice_id', $invoice->id)
                                    ->where('phong_id', $phongId)
                                    ->first();

                                if ($existing) {
                                    $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                    $existing->unit_price = $service->price ?? $existing->unit_price;
                                    $existing->save();
                                } else {
                                    BookingService::create([
                                        'dat_phong_id' => $booking->id,
                                        'service_id' => $service->id,
                                        'used_at' => $ngay,
                                        'invoice_id' => $invoice->id,
                                        'phong_id' => $phongId,
                                        'quantity' => $qty,
                                        'unit_price' => $service->price ?? 0,
                                    ]);
                                }
                                $totalServices += ($qty * ($service->price ?? 0));
                            }
                        } else {
                            // No specific rooms: apply to all assigned rooms
                            $assigned = $booking ? $booking->getPhongIds() : [];
                            if (is_string($assigned)) {
                                $assigned = json_decode($assigned, true) ?: [];
                            }
                            $assigned = is_array($assigned) ? array_filter($assigned) : [];

                            Log::info('InvoiceController:update - Global mode assigned rooms', ['booking_id' => $booking->id, 'assigned' => $assigned]);

                            if (!empty($assigned)) {
                                // Create one row per assigned room
                                foreach ($assigned as $phongId) {
                                    $existing = BookingService::where('dat_phong_id', $booking->id)
                                        ->where('service_id', $service->id)
                                        ->where('used_at', $ngay)
                                        ->where('invoice_id', $invoice->id)
                                        ->where('phong_id', $phongId)
                                        ->first();

                                    if ($existing) {
                                        $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                        $existing->unit_price = $service->price ?? $existing->unit_price;
                                        $existing->save();
                                    } else {
                                        BookingService::create([
                                            'dat_phong_id' => $booking->id,
                                            'service_id' => $service->id,
                                            'used_at' => $ngay,
                                            'invoice_id' => $invoice->id,
                                            'phong_id' => $phongId,
                                            'quantity' => $qty,
                                            'unit_price' => $service->price ?? 0,
                                        ]);
                                    }
                                    $totalServices += ($qty * ($service->price ?? 0));
                                }
                            } else {
                                // No assigned rooms: create aggregate (phong_id = NULL)
                                $existing = BookingService::where('dat_phong_id', $booking->id)
                                    ->where('service_id', $service->id)
                                    ->where('used_at', $ngay)
                                    ->where('invoice_id', $invoice->id)
                                    ->whereNull('phong_id')
                                    ->first();

                                if ($existing) {
                                    $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                    $existing->unit_price = $service->price ?? $existing->unit_price;
                                    $existing->save();
                                } else {
                                    BookingService::create([
                                        'dat_phong_id' => $booking->id,
                                        'service_id' => $service->id,
                                        'used_at' => $ngay,
                                        'invoice_id' => $invoice->id,
                                        'phong_id' => null,
                                        'quantity' => $qty,
                                        'unit_price' => $service->price ?? 0,
                                    ]);
                                }
                                $totalServices += ($qty * ($service->price ?? 0));
                            }
                        }
                    }
                }
                
                Log::info('InvoiceController:update - Total services calculated', [
                    'booking_id' => $booking->id,
                    'invoice_id' => $invoice->id,
                    'totalServices' => $totalServices,
                ]);
            }
        }

        // Handle total calculation for both invoice types
        if ($invoice->isExtra()) {
            // For EXTRA invoice, we already set tong_tien from services above if services_data provided.
            // If no services_data was provided, compute total from existing invoice-scoped booking_services.
            if (!$request->filled('services_data')) {
                $svcRows = BookingService::where('dat_phong_id', $booking->id)
                    ->where('invoice_id', $invoice->id)
                    ->get();
                $serviceSum = $svcRows->reduce(function($carry, $item){
                    return $carry + (($item->quantity ?? 0) * ($item->unit_price ?? 0));
                }, 0);
                // Use stored phi_them_nguoi if present (may have been provided via form or invoice_items)
                $invoice->tien_dich_vu = $serviceSum;
                $invoice->tong_tien = ($serviceSum + floatval($invoice->phi_them_nguoi ?? 0));
                $invoice->save();
            }
            // If the form submitted a tong_tien (for example editing an EXTRA invoice that only
            // contains invoice_items instead of BookingService rows), prefer the submitted total
            // if services_data wasn't present. This allows admins to edit the total price directly
            // in the form even when the invoice doesn't have BookingService rows.
            if (!$request->filled('services_data') && $request->filled('tong_tien')) {
                $submittedTotal = $request->input('tong_tien', 0);
                $invoice->tong_tien = $submittedTotal;
                // Also store service and extra-guest breakdown if provided in the form
                $invoice->tien_dich_vu = $request->input('tien_dich_vu', 0);
                $invoice->phi_phat_sinh = $request->input('phi_phat_sinh', 0);
                $invoice->save();
                try { Log::info('InvoiceController:update - EXTRA invoice used submitted tong_tien', ['invoice_id' => $invoice->id, 'submitted' => $submittedTotal]); } catch (\Throwable $e) {}
            }
            // For EXTRA invoices, no need to update booking - they don't affect it
        } else {
            // For regular (non-EXTRA) invoices:
            // Use the form-submitted tong_tien (from client JS calculation) instead of recalculating
            // This prevents double-counting of room prices
            if ($request->filled('tong_tien')) {
                $submittedTotal = $request->input('tong_tien', 0);
                $invoice->tong_tien = $submittedTotal;
                $invoice->giam_gia = $request->input('giam_gia', 0);
                $invoice->tien_phong = $request->input('tien_phong', 0);
                $invoice->tien_dich_vu = $request->input('tien_dich_vu', 0);
                $invoice->phi_phat_sinh = $request->input('phi_phat_sinh', 0);
                
                // Update booking totals from invoice (sync invoice values to booking)
                // Extract room price, service price from invoice data
                // Note: giam_gia (voucher discount) belongs to Invoice table, not DatPhong
                // Use update() to let Laravel handle type casting properly
                $booking->update([
                    'tien_phong' => $request->input('tien_phong', 0),
                    'tien_dich_vu' => $request->input('tien_dich_vu', 0),
                    'phi_phat_sinh' => $request->input('phi_phat_sinh', 0),
                    'tong_tien' => $submittedTotal,
                ]);
                
                Log::info('InvoiceController:update - Using submitted totals', [
                    'invoice_id' => $invoice->id,
                    'booking_id' => $booking->id,
                    'tong_tien' => $submittedTotal,
                    'tien_phong' => $request->input('tien_phong', 0),
                    'tien_dich_vu' => $request->input('tien_dich_vu', 0),
                ]);
            } else {
                // If no tong_tien in request, fall back to recalculating
                try {
                    BookingPriceCalculator::recalcTotal($booking);
                    $booking = $booking->fresh();
                    $invoice->tong_tien = $booking->tong_tien;
                    
                    Log::info('InvoiceController:update - Fallback to recalcTotal', [
                        'invoice_id' => $invoice->id,
                        'booking_id' => $booking->id,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Recalc booking total failed in InvoiceController:update: ' . $e->getMessage());
                }
            }
            $invoice->save();
        }

        // Đồng bộ trạng thái đặt phòng khi hóa đơn đã thanh toán
        if ($invoice->trang_thai === 'da_thanh_toan' && $invoice->datPhong) {
            if ($invoice->datPhong->trang_thai === 'cho_xac_nhan') {
                $invoice->datPhong->validateStatusTransition('da_xac_nhan');
                $invoice->datPhong->trang_thai = 'da_xac_nhan';
                $invoice->datPhong->save();
            }
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Cập nhật hóa đơn thành công.');
    }

    /**
     * Show form to create an EXTRA invoice (do not persist until user confirms)
     */
    public function createExtra(Invoice $invoice)
    {
        // Only allow creating EXTRA invoice from a paid invoice
        if ($invoice->trang_thai !== 'da_thanh_toan') {
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('error', 'Chỉ có thể tạo hóa đơn phát sinh từ hóa đơn đã thanh toán.');
        }

        $booking = $invoice->datPhong;
        $services = Service::where('status', 'hoat_dong')->get();
        
        // Preload existing booking services so the create-extra UI can show already added services
        // Only show services that are part of the booking (not already assigned to another invoice)
        $bookingServices = BookingService::with('service')
            ->where('dat_phong_id', $booking->id)
            ->whereNull('invoice_id')
            ->get()
            ->groupBy('service_id')
            ->map(function($group) {
                return $group->map(function($row) {
                    return ['ngay' => \Carbon\Carbon::parse($row->used_at)->format('Y-m-d'), 'so_luong' => $row->quantity];
                })->values()->toArray();
            })->toArray();

        // Get assigned rooms for this booking with their types (use pivot helper)
        $assignedRooms = [];
        $phongIds = $booking ? $booking->getPhongIds() : [];
        if (is_string($phongIds)) {
            $phongIds = json_decode($phongIds, true);
        }
        if (is_array($phongIds) && !empty($phongIds)) {
            $assignedRooms = Phong::whereIn('id', $phongIds)
                ->with('loaiPhong:id,ten_loai')
                ->get()
                ->map(function($room) {
                    return [
                        'id' => $room->id,
                        'so_phong' => $room->so_phong,
                        'ten_loai' => $room->loaiPhong ? $room->loaiPhong->ten_loai : 'N/A'
                    ];
                })
                ->toArray();
        }

        return view('admin.invoices.create_extra', compact('invoice', 'booking', 'services', 'bookingServices', 'assignedRooms'));
    }

    /**
     * Store the confirmed EXTRA invoice and the selected service entries
     * For EXTRA invoices: only count services selected, DO NOT include room price
     * Services are linked to the invoice (not the booking) to keep them separate
     */
    public function storeExtra(Request $request, Invoice $invoice)
    {
        // Only allow creating EXTRA invoice from a paid invoice
        if ($invoice->trang_thai !== 'da_thanh_toan') {
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('error', 'Chỉ có thể tạo hóa đơn phát sinh từ hóa đơn đã thanh toán.');
        }

        $booking = $invoice->datPhong;
        if (!$booking) {
            return redirect()->route('admin.invoices.show', $invoice->id)->with('error', 'Đặt phòng không tồn tại.');
        }

        $totalServices = 0;

        // Create a new invoice record now that the admin confirmed
        $new = Invoice::create([
            'dat_phong_id' => $booking->id,
            'tong_tien' => 0,
            'phuong_thuc' => null,
            'trang_thai' => 'cho_thanh_toan',
            'invoice_type' => 'EXTRA',
            'ngay_tao' => now(),
        ]);

        // Chỉ tạo mới dịch vụ phát sinh cho hóa đơn EXTRA, không di chuyển dịch vụ booking-level
        if ($request->filled('services_data')) {
            $servicesData = $request->input('services_data', []);
            Log::info('storeExtra called - services_data received', ['invoice_id' => $invoice->id, 'services_count' => is_array($servicesData) ? count($servicesData) : 0]);

            // Use DB transaction to ensure atomicity
            DB::beginTransaction();
            try {
                foreach ($servicesData as $svcId => $data) {
                    Log::info('Processing service block', ['svcId' => $svcId, 'data' => $data]);
                    $service = Service::find($svcId);
                    if (!$service) continue;
                    $entries = $data['entries'] ?? [];
                    foreach ($entries as $entry) {
                        Log::info('Processing service entry', ['svcId' => $svcId, 'entry' => $entry]);
                        $ngay = $entry['ngay'] ?? '';
                        $qty = $entry['so_luong'] ?? 0;
                        if (!$ngay || $qty <= 0) continue;

                        // Support per-entry room selection (phong_ids or phong_id)
                        $entryPhongIds = [];
                        if (isset($entry['phong_ids']) && is_array($entry['phong_ids'])) {
                            $entryPhongIds = array_filter($entry['phong_ids']);
                        } elseif (isset($entry['phong_id'])) {
                            $entryPhongIds = array_filter([$entry['phong_id']]);
                        }

                        // Normalize assigned rooms fallback to booking pivot
                        if (empty($entryPhongIds)) {
                            $assigned = $booking ? $booking->getPhongIds() : [];
                            if (is_string($assigned)) {
                                $assigned = json_decode($assigned, true) ?: [];
                            }
                            $entryPhongIds = is_array($assigned) ? array_filter($assigned) : [];
                            Log::info('storeExtra - normalized phong_ids from booking', ['booking_id' => $booking->id, 'phong_ids' => $entryPhongIds]);
                        }

                        // Validate that we have rooms to assign to
                        if (empty($entryPhongIds)) {
                            Log::error('storeExtra - no rooms assigned to booking, cannot create service rows', ['booking_id' => $booking->id, 'service_id' => $svcId, 'entry' => $entry]);
                            DB::rollBack();
                            return redirect()->route('admin.invoices.show', $invoice->id)->with('error', 'Lỗi: Đặt phòng không có phòng được gán. Vui lòng kiểm tra lại thông tin đặt phòng.');
                        }

                        // If we have rooms, create per-room service entries
                        if (!empty($entryPhongIds)) {
                            foreach ($entryPhongIds as $phongId) {
                                // Try to find an existing row with exact same: invoice_id + booking_id + service_id + used_at + phong_id
                                // This allows: cùng ngày cùng phòng => cộng dồn số lượng
                                $existing = BookingService::where('invoice_id', $new->id)
                                    ->where('dat_phong_id', $booking->id)
                                    ->where('service_id', $service->id)
                                    ->where('used_at', $ngay) // exact match, not date compare
                                    ->where('phong_id', $phongId)
                                    ->first();

                                if ($existing) {
                                    // Same invoice + date + room + service => cộng dồn số lượng
                                    $existing->quantity = ($existing->quantity ?? 0) + $qty;
                                    $existing->unit_price = $service->price ?? $existing->unit_price;
                                    $existing->save();
                                    Log::info('storeExtra - Merged (accumulated qty)', [
                                        'bs_id' => $existing->id,
                                        'invoice_id' => $new->id,
                                        'svc_id' => $service->id,
                                        'phong_id' => $phongId,
                                        'ngay' => $ngay,
                                        'qty_added' => $qty,
                                        'qty_total' => $existing->quantity
                                    ]);
                                } else {
                                    // New row: different invoice OR different date OR different room
                                    try {
                                        $bs = BookingService::create([
                                            'invoice_id' => $new->id,
                                            'dat_phong_id' => $booking->id,
                                            'service_id' => $service->id,
                                            'quantity' => $qty,
                                            'unit_price' => $service->price ?? 0,
                                            'used_at' => $ngay,
                                            'phong_id' => $phongId,
                                        ]);
                                        Log::info('storeExtra - Created new row', [
                                            'bs_id' => $bs->id ?? null,
                                            'invoice_id' => $new->id,
                                            'svc_id' => $service->id,
                                            'phong_id' => $phongId,
                                            'ngay' => $ngay,
                                            'qty' => $qty
                                        ]);
                                    } catch (QueryException $qe) {
                                        // Race condition: another request inserted same row; merge instead
                                        Log::warning('storeExtra - Create failed, attempting race-merge', [
                                            'error' => $qe->getMessage(),
                                            'code' => $qe->getCode(),
                                            'invoice_id' => $new->id,
                                            'svc_id' => $service->id
                                        ]);
                                        if ($qe->getCode() == 23000) {
                                            $race = BookingService::where('invoice_id', $new->id)
                                                ->where('dat_phong_id', $booking->id)
                                                ->where('service_id', $service->id)
                                                ->where('used_at', $ngay)
                                                ->where('phong_id', $phongId)
                                                ->first();
                                            if ($race) {
                                                $race->quantity = ($race->quantity ?? 0) + $qty;
                                                $race->unit_price = $service->price ?? $race->unit_price;
                                                $race->save();
                                                Log::info('storeExtra - Race-merged', ['bs_id' => $race->id, 'qty_added' => $qty]);
                                            }
                                        } else {
                                            throw $qe;
                                        }
                                    }
                                }
                                $totalServices += ($qty * ($service->price ?? 0));
                            }
                        }
                    }
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Failed to store extra invoice services: ' . $e->getMessage());
                return redirect()->route('admin.invoices.show', $invoice->id)->with('error', 'Lỗi khi lưu dịch vụ phát sinh: ' . $e->getMessage());
            }
        }

        // Set invoice totals: service subtotal and total = services + extra_guest (if provided)
        $new->tien_dich_vu = $totalServices;
        $submittedExtraGuest = $request->input('phi_them_nguoi', null);
        if ($submittedExtraGuest !== null) {
            $new->phi_them_nguoi = floatval($submittedExtraGuest);
        }
        $new->tong_tien = ($totalServices + floatval($new->phi_them_nguoi ?? 0));
        $new->save();

        return redirect()->route('admin.invoices.show', $new->id)->with('success', 'Hóa đơn phát sinh đã được tạo và lưu (chỉ tính tiền dịch vụ).');
    }

    /**
     * Tạo hóa đơn hoàn tiền riêng biệt từ hóa đơn gốc
     * Có thể được gọi trực tiếp hoặc từ method adjust() khi hóa đơn đã thanh toán
     */
    public function createRefundInvoice(Request $request, Invoice $invoice)
    {
        // Kiểm tra quyền
        if (!in_array(auth()->user()->vai_tro ?? '', ['admin', 'nhan_vien'])) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        // Chỉ cho phép tạo hóa đơn hoàn tiền từ hóa đơn đã thanh toán
        if ($invoice->trang_thai !== 'da_thanh_toan') {
            return redirect()->back()->with('error', 'Chỉ có thể tạo hóa đơn hoàn tiền từ hóa đơn đã thanh toán.');
        }

        // Không cho phép tạo refund invoice từ refund invoice hoặc extra invoice
        if ($invoice->isRefund() || $invoice->isExtra()) {
            return redirect()->back()->with('error', 'Chỉ có thể tạo hóa đơn hoàn tiền từ hóa đơn chính đã thanh toán.');
        }

        // Kiểm tra xem đã có hóa đơn hoàn tiền chưa (tùy chọn - có thể cho phép nhiều hóa đơn hoàn tiền)
        // $existingRefund = Invoice::where('original_invoice_id', $invoice->id)->where('invoice_type', 'REFUND')->first();
        // if ($existingRefund) {
        //     return redirect()->route('admin.invoices.show', $existingRefund->id)->with('info', 'Hóa đơn hoàn tiền đã tồn tại.');
        // }

        $booking = $invoice->datPhong;
        if (!$booking) {
            return redirect()->back()->with('error', 'Đặt phòng không tồn tại.');
        }

        // Validate request
        $request->validate([
            'adjustments' => 'required|array|min:1',
            'adjustments.*.booking_service_id' => 'required|integer|exists:booking_services,id',
            'adjustments.*.quantity' => 'required|integer|min:1',
            'adjustments.*.used_at' => 'nullable|date',
            'adjustments.*.note' => 'nullable|string|max:500',
            'refund_method' => 'nullable|in:tien_mat,chuyen_khoan,cong_thanh_toan',
            'refund_account_number' => 'nullable|string|max:200',
            'refund_account_name' => 'nullable|string|max:200',
            'refund_bank_name' => 'nullable|string|max:200',
            'note' => 'nullable|string|max:1000',
        ], [
            'adjustments.required' => 'Vui lòng chọn ít nhất một dịch vụ để hoàn tiền.',
            'adjustments.min' => 'Vui lòng chọn ít nhất một dịch vụ để hoàn tiền.',
        ]);

        $adjustments = $request->input('adjustments', []);
        $totalRefundAmount = 0;
        $refundServices = [];

        DB::beginTransaction();
        try {
            // Tạo hóa đơn hoàn tiền mới
            $refundInvoice = Invoice::create([
                'dat_phong_id' => $booking->id,
                'invoice_type' => 'REFUND',
                'original_invoice_id' => $invoice->id,
                'tong_tien' => 0, // Sẽ tính sau
                'tien_phong' => 0,
                'tien_dich_vu' => 0,
                'phi_phat_sinh' => 0,
                'phi_them_nguoi' => 0,
                'giam_gia' => 0,
                'da_thanh_toan' => 0,
                'con_lai' => 0,
                'phuong_thuc' => $request->input('refund_method', 'tien_mat'),
                'trang_thai' => 'hoan_tien',
                'ghi_chu' => $request->input('note', 'Hóa đơn hoàn tiền cho hóa đơn #' . $invoice->id),
            ]);

            // Xử lý từng dịch vụ cần hoàn tiền
            foreach ($adjustments as $adj) {
                $origBsId = intval($adj['booking_service_id'] ?? 0);
                $adjQty = intval($adj['quantity'] ?? 0);
                if ($adjQty <= 0) continue;

                $orig = BookingService::with('service', 'phong')->find($origBsId);
                if (!$orig) continue;

                // Tìm quantity gốc ban đầu (trước khi adjust trong hóa đơn gốc)
                // Quantity gốc là quantity dương đầu tiên của dịch vụ này
                $originalQuantity = BookingService::where('dat_phong_id', $booking->id)
                    ->where('service_id', $orig->service_id)
                    ->where('phong_id', $orig->phong_id)
                    ->whereDate('used_at', date('Y-m-d', strtotime($orig->used_at)))
                    ->where('quantity', '>', 0)
                    ->where(function($q) use ($invoice) {
                        $q->whereNull('invoice_id')->orWhere('invoice_id', $invoice->id);
                    })
                    ->sum('quantity');
                
                // Tính số lượng đã hoàn tiền từ các refund invoices riêng (không tính từ adjust trong hóa đơn gốc)
                $alreadyRefundedInRefundInvoices = BookingService::where('dat_phong_id', $booking->id)
                    ->where('service_id', $orig->service_id)
                    ->where('phong_id', $orig->phong_id)
                    ->whereDate('used_at', date('Y-m-d', strtotime($orig->used_at)))
                    ->where('invoice_id', '!=', null)
                    ->whereHas('invoice', function($q) {
                        $q->where('invoice_type', 'REFUND');
                    })
                    ->sum(DB::raw('ABS(quantity)'));

                // Tính số lượng đã hoàn tiền trong hóa đơn gốc (qua adjust method - negative adjustments)
                $alreadyRefundedInOriginalInvoice = abs(BookingService::where('dat_phong_id', $booking->id)
                    ->where('service_id', $orig->service_id)
                    ->where('phong_id', $orig->phong_id)
                    ->whereDate('used_at', date('Y-m-d', strtotime($orig->used_at)))
                    ->where('invoice_id', $invoice->id)
                    ->where('quantity', '<', 0)
                    ->sum('quantity'));

                // Kiểm tra nếu dịch vụ có quantity = 0 hoặc <= 0 trong hóa đơn gốc và có note hoàn tiền
                $currentQuantity = $orig->quantity ?? 0;
                $currentNote = $orig->note ?? '';
                $isMarkedAsRefunded = ($currentQuantity <= 0) || 
                                      (stripos($currentNote, 'hoàn') !== false) || 
                                      (stripos($currentNote, 'Đã hoàn') !== false);

                // Tính số lượng có thể hoàn
                $remaining = 0;
                
                if ($isMarkedAsRefunded) {
                    // Nếu dịch vụ đã được đánh dấu hoàn tiền trong hóa đơn gốc
                    if ($alreadyRefundedInOriginalInvoice > 0) {
                        // Có negative adjustments trong hóa đơn gốc
                        $remaining = $alreadyRefundedInOriginalInvoice - abs($alreadyRefundedInRefundInvoices);
                    } elseif ($originalQuantity > 0) {
                        // Không có negative adjustments nhưng có quantity gốc > 0
                        // Có thể dịch vụ được đánh dấu hoàn tiền bằng cách set quantity = 0
                        $remaining = $originalQuantity - abs($alreadyRefundedInRefundInvoices);
                    } else {
                        // Nếu không tìm thấy quantity gốc, sử dụng quantity hiện tại nếu > 0
                        // Hoặc mặc định là 1 nếu quantity = 0 nhưng có note hoàn tiền
                        if ($currentQuantity > 0) {
                            $remaining = $currentQuantity - abs($alreadyRefundedInRefundInvoices);
                        } elseif (stripos($currentNote, 'hoàn') !== false) {
                            // Nếu có note hoàn tiền nhưng quantity = 0, cho phép hoàn 1 (giả định)
                            $remaining = 1 - abs($alreadyRefundedInRefundInvoices);
                        }
                    }
                } else {
                    // Dịch vụ chưa được hoàn tiền trong hóa đơn gốc
                    $remaining = max(0, $originalQuantity - abs($alreadyRefundedInRefundInvoices));
                }
                
                $remaining = max(0, $remaining);
                
                if ($adjQty > $remaining) {
                    DB::rollBack();
                    return redirect()->back()->with('error', "Số lượng hoàn tiền vượt quá số lượng còn lại cho dịch vụ: " . ($orig->service->name ?? 'Dịch vụ') . ". Số lượng còn lại: {$remaining}, Quantity gốc: {$originalQuantity}, Đã hoàn trong hóa đơn gốc: {$alreadyRefundedInOriginalInvoice}, Đã hoàn trong refund invoices: {$alreadyRefundedInRefundInvoices}");
                }

                $unit = $orig->unit_price ?? (Service::find($orig->service_id)->price ?? 0);
                $usedDate = $adj['used_at'] ?? $orig->used_at ?? date('Y-m-d');

                // Tạo booking service với số lượng âm trong hóa đơn hoàn tiền
                $refundBs = BookingService::create([
                    'dat_phong_id' => $booking->id,
                    'phong_id' => $orig->phong_id,
                    'invoice_id' => $refundInvoice->id,
                    'service_id' => $orig->service_id,
                    'quantity' => -1 * abs($adjQty),
                    'unit_price' => $unit,
                    'used_at' => $usedDate,
                    'note' => $adj['note'] ?? 'Hoàn tiền dịch vụ',
                ]);

                $refundAmount = abs($refundBs->quantity * $refundBs->unit_price);
                $totalRefundAmount += $refundAmount;

                $refundServices[] = [
                    'service' => $orig->service->name ?? 'Dịch vụ',
                    'room' => $orig->phong->so_phong ?? $orig->phong_id,
                    'date' => $usedDate,
                    'quantity' => $adjQty,
                    'unit_price' => $unit,
                    'amount' => $refundAmount,
                ];
            }

            // Cập nhật tổng tiền cho hóa đơn hoàn tiền
            $refundInvoice->tien_dich_vu = -1 * $totalRefundAmount;
            $refundInvoice->tong_tien = -1 * $totalRefundAmount;
            $refundInvoice->con_lai = -1 * $totalRefundAmount;
            $refundInvoice->save();

            // Tạo RefundService records nếu có thông tin ngân hàng
            if ($request->filled('refund_method') && $request->input('refund_method') === 'chuyen_khoan') {
                foreach ($refundServices as $rs) {
                    RefundService::create([
                        'hoa_don_id' => $refundInvoice->id,
                        'dat_phong_id' => $booking->id,
                        'booking_service_id' => null, // Có thể để null hoặc lưu ID của refund booking service
                        'booking_room_ids' => json_encode([]),
                        'total_refund' => $rs['amount'],
                        'refund_method' => 'chuyen_khoan',
                        'refund_status' => 'cho_xu_ly',
                        'bank_account_number' => $request->input('refund_account_number'),
                        'bank_account_name' => $request->input('refund_account_name'),
                        'bank_name' => $request->input('refund_bank_name'),
                        'note' => $request->input('note'),
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            Log::info('Refund invoice created', [
                'original_invoice_id' => $invoice->id,
                'refund_invoice_id' => $refundInvoice->id,
                'total_refund' => $totalRefundAmount,
            ]);

            return redirect()->route('admin.invoices.show', $refundInvoice->id)
                ->with('success', 'Hóa đơn hoàn tiền đã được tạo thành công. Hóa đơn gốc #' . $invoice->id . ' vẫn được giữ nguyên.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to create refund invoice: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi tạo hóa đơn hoàn tiền: ' . $e->getMessage());
        }
    }
    // (removed unused empty create() method)
}