<?php

namespace App\Exports;

use App\Models\Invoice;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class InvoiceExport
{
    protected $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function generate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hóa đơn');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(18);

        $booking = $this->invoice->datPhong;
        $services = collect();
        
        if ($booking) {
            $services = \App\Models\BookingService::with('service')
                ->where('dat_phong_id', $booking->id)
                ->orderBy('used_at')
                ->get();
        }

        $row = 1;

        // Title
        $sheet->setCellValue('A' . $row, 'HÓA ĐƠN SỐ: ' . $this->invoice->id);
        $sheet->getStyle('A' . $row)->getFont()->setSize(16)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE));
        $row += 2;

        // Customer section
        $sheet->setCellValue('A' . $row, 'Khách hàng:');
        $sheet->setCellValue('B' . $row, $booking ? ($booking->username ?? ($booking->user->ho_ten ?? 'N/A')) : 'N/A');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue('A' . $row, 'Email:');
        $sheet->setCellValue('B' . $row, $booking ? ($booking->email ?? ($booking->user->email ?? 'N/A')) : 'N/A');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue('A' . $row, 'Điện thoại:');
        $sheet->setCellValue('B' . $row, $booking ? ($booking->sdt ?? ($booking->user->sdt ?? 'N/A')) : 'N/A');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row += 2;

        // Services header
        $headerRow = $row;
        $sheet->setCellValue('A' . $row, 'DỊCH VỤ');
        $sheet->setCellValue('B' . $row, 'NGÀY DÙNG');
        $sheet->setCellValue('C' . $row, 'SỐ LƯỢNG');
        $sheet->setCellValue('D' . $row, 'ĐƠN GIÁ');
        $sheet->setCellValue('E' . $row, 'THÀNH TIỀN');

        // Header style
        $headerRange = 'A' . $row . ':E' . $row;
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E78');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        // Services rows
        $servicesTotal = 0;
        $startServiceRow = $row;
        
        if ($services->isEmpty()) {
            $sheet->setCellValue('A' . $row, 'Không có dịch vụ');
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        } else {
            foreach ($services as $s) {
                $svc = $s->service;
                $name = $svc ? ($svc->name ?? 'N/A') : ($s->service_name ?? 'N/A');
                $usedAt = $s->used_at ? date('d/m/Y', strtotime($s->used_at)) : '-';
                $qty = $s->quantity ?? 0;
                $unitPrice = $s->unit_price ?? 0;
                $subtotal = $qty * $unitPrice;
                $servicesTotal += $subtotal;

                $sheet->setCellValue('A' . $row, $name);
                $sheet->setCellValue('B' . $row, $usedAt);
                $sheet->setCellValue('C' . $row, $qty);
                $sheet->setCellValue('D' . $row, $unitPrice);
                $sheet->setCellValue('E' . $row, $subtotal);

                // Format cells
                $sheet->getStyle('C' . $row . ':E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('D' . $row . ':E' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('A' . $row . ':E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');

                $row++;
            }
        }

        $endServiceRow = $row - 1;
        $row += 1;

        // Room total
        $roomTotal = max(0, ($this->invoice->tong_tien ?? 0) - $servicesTotal);

        // Summary section
        $sheet->setCellValue('D' . $row, 'Tiền phòng:');
        $sheet->setCellValue('E' . $row, $roomTotal);
        $sheet->getStyle('D' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('E' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;

        $sheet->setCellValue('D' . $row, 'Tổng dịch vụ:');
        $sheet->setCellValue('E' . $row, $servicesTotal);
        $sheet->getStyle('D' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('E' . $row)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_GREEN));
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $row++;

        // Total row
        $totalAmount = $this->invoice->tong_tien ?? ($roomTotal + $servicesTotal);
        $sheet->setCellValue('D' . $row, 'TỔNG THANH TOÁN:');
        $sheet->setCellValue('E' . $row, $totalAmount);
        $sheet->getStyle('D' . $row . ':E' . $row)->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle('D' . $row . ':E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E78');
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('D' . $row . ':E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('D' . $row . ':E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $row += 2;

        // Status
        $sheet->setCellValue('A' . $row, 'Trạng thái:');
        $sheet->setCellValue('B' . $row, $this->invoice->trang_thai == 'da_thanh_toan' ? '✓ Đã thanh toán' : 'Chờ thanh toán');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        if ($this->invoice->trang_thai == 'da_thanh_toan') {
            $sheet->getStyle('B' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_GREEN));
        }
        $row++;

        $sheet->setCellValue('A' . $row, 'Ngày tạo:');
        $sheet->setCellValue('B' . $row, date('d/m/Y H:i', strtotime($this->invoice->ngay_tao)));
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);

        // Writer
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }
}
