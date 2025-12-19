<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Phong;
use App\Models\LoaiPhong;
use Illuminate\Support\Facades\DB;

class PhongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Tạo các phòng cụ thể cho từng loại phòng
     */
    public function run(): void
    {
        // Xóa dữ liệu cũ nếu có (không dùng truncate vì có foreign key)
        // Chỉ xóa nếu không có booking nào đang sử dụng
        $phongsInUse = DB::table('dat_phong')
            ->whereNotNull('phong_id')
            ->whereIn('trang_thai', ['cho_xac_nhan', 'da_xac_nhan'])
            ->pluck('phong_id')
            ->toArray();
        
        DB::table('phong')
            ->whereNotIn('id', $phongsInUse)
            ->delete();

        $loaiPhongs = LoaiPhong::all();

        foreach ($loaiPhongs as $loaiPhong) {
            $soLuongPhong = $loaiPhong->so_luong_phong ?? 0;
            
            if ($soLuongPhong <= 0) {
                continue;
            }

            // Xác định tầng và số phòng bắt đầu dựa trên loại phòng
            $tang = $this->getTangForLoaiPhong($loaiPhong->ten_loai);
            $soPhongBatDau = $this->getSoPhongBatDau($loaiPhong->ten_loai);

            for ($i = 0; $i < $soLuongPhong; $i++) {
                $soPhong = $soPhongBatDau + $i;
                $soPhongFormatted = str_pad($soPhong, 3, '0', STR_PAD_LEFT); // VD: 101, 102, ...

                // Xác định các đặc điểm của phòng
                $coViewDep = $this->hasViewDep($loaiPhong->ten_loai, $soPhong);
                $huongCuaSo = $this->getHuongCuaSo($loaiPhong->ten_loai, $soPhong);
                $coBanCong = $this->hasBanCong($loaiPhong->ten_loai);
                $giaBoSung = $this->getGiaBoSung($loaiPhong->ten_loai, $coViewDep);
                $giaRieng = null; // Có thể set giá riêng cho một số phòng đặc biệt

                // Tìm phòng đã tồn tại
                $phong = Phong::where('so_phong', $soPhongFormatted)->first();
                
                if ($phong) {
                    // Phòng đã tồn tại, chỉ cập nhật thông tin (không thay đổi trạng thái nếu đang được sử dụng)
                    $phong->update([
                        'loai_phong_id' => $loaiPhong->id,
                        'ten_phong' => $this->getTenPhong($loaiPhong->ten_loai, $soPhongFormatted),
                        'tang' => $tang,
                        'huong_cua_so' => $huongCuaSo,
                        'co_ban_cong' => $coBanCong,
                        'co_view_dep' => $coViewDep,
                        'gia_rieng' => $giaRieng,
                        'gia_bo_sung' => $giaBoSung,
                        // Chỉ cập nhật trạng thái nếu phòng đang trống hoặc không có booking nào
                        'trang_thai' => $phong->trang_thai === 'trong' ? 'trong' : $phong->trang_thai,
                        'ghi_chu' => $this->getGhiChu($loaiPhong->ten_loai, $soPhongFormatted, $coViewDep),
                    ]);
                } else {
                    // Phòng mới, tạo mới
                    Phong::create([
                        'loai_phong_id' => $loaiPhong->id,
                        'so_phong' => $soPhongFormatted,
                        'ten_phong' => $this->getTenPhong($loaiPhong->ten_loai, $soPhongFormatted),
                        'tang' => $tang,
                        'huong_cua_so' => $huongCuaSo,
                        'co_ban_cong' => $coBanCong,
                        'co_view_dep' => $coViewDep,
                        'gia_rieng' => $giaRieng,
                        'gia_bo_sung' => $giaBoSung,
                        'trang_thai' => 'trong',
                        'ghi_chu' => $this->getGhiChu($loaiPhong->ten_loai, $soPhongFormatted, $coViewDep),
                    ]);
                }
            }
        }

        // Tính lại số lượng phòng trống cho tất cả loại phòng sau khi seed
        foreach ($loaiPhongs as $loaiPhong) {
            $trongCount = Phong::where('loai_phong_id', $loaiPhong->id)
                ->where('trang_thai', 'trong')
                ->count();
            
            $loaiPhong->update(['so_luong_trong' => $trongCount]);
        }

        $totalRooms = Phong::count();
        $this->command->info("Đã seed {$totalRooms} phòng thành công!");
        $this->command->info("Đã cập nhật số lượng phòng trống cho tất cả loại phòng!");
    }

    /**
     * Xác định tầng cho loại phòng
     */
    private function getTangForLoaiPhong(string $tenLoai): int
    {
        $tangMap = [
            'Phòng Standard' => 1,
            'Phòng Deluxe' => 2,
            'Phòng Suite' => 3,
            'Phòng Family' => 2,
            'Phòng Executive' => 3,
            'Phòng Ocean View' => 4,
        ];

        return $tangMap[$tenLoai] ?? 1;
    }

    /**
     * Xác định số phòng bắt đầu
     */
    private function getSoPhongBatDau(string $tenLoai): int
    {
        $soPhongMap = [
            'Phòng Standard' => 101,
            'Phòng Deluxe' => 201,
            'Phòng Suite' => 301,
            'Phòng Family' => 210,
            'Phòng Executive' => 310,
            'Phòng Ocean View' => 401,
        ];

        return $soPhongMap[$tenLoai] ?? 101;
    }

    /**
     * Kiểm tra phòng có view đẹp không
     * Phòng số chẵn thường có view đẹp hơn
     */
    private function hasViewDep(string $tenLoai, int $soPhong): bool
    {
        // Phòng Ocean View: tất cả đều có view đẹp
        if ($tenLoai === 'Phòng Ocean View') {
            return true;
        }

        // Phòng Suite: tầng cao hơn có view đẹp
        if ($tenLoai === 'Phòng Suite') {
            return $soPhong >= 305;
        }

        // Phòng Deluxe: số phòng chẵn có view đẹp hơn
        if ($tenLoai === 'Phòng Deluxe') {
            return $soPhong % 2 === 0;
        }

        // Các loại khác: một số phòng có view đẹp
        return $soPhong % 3 === 0;
    }

    /**
     * Xác định hướng cửa sổ
     */
    private function getHuongCuaSo(string $tenLoai, int $soPhong): ?string
    {
        if ($tenLoai === 'Phòng Ocean View') {
            return 'bien';
        }

        // Phòng số lẻ: view thành phố, số chẵn: view núi
        if ($soPhong % 2 === 0) {
            return 'nui';
        }

        return 'thanh_pho';
    }

    /**
     * Kiểm tra phòng có ban công không
     */
    private function hasBanCong(string $tenLoai): bool
    {
        $coBanCong = [
            'Phòng Deluxe',
            'Phòng Suite',
            'Phòng Ocean View',
        ];

        return in_array($tenLoai, $coBanCong);
    }

    /**
     * Tính giá bổ sung dựa trên view
     */
    private function getGiaBoSung(string $tenLoai, bool $coViewDep): ?float
    {
        if (!$coViewDep) {
            return null;
        }

        // Giá bổ sung cho view đẹp
        $giaBoSungMap = [
            'Phòng Standard' => 50000,
            'Phòng Deluxe' => 100000,
            'Phòng Suite' => 200000,
            'Phòng Family' => 80000,
            'Phòng Executive' => 150000,
            'Phòng Ocean View' => 300000,
        ];

        return $giaBoSungMap[$tenLoai] ?? null;
    }

    /**
     * Tạo tên phòng
     */
    private function getTenPhong(string $tenLoai, string $soPhong): ?string
    {
        // Một số phòng đặc biệt có tên riêng
        if ($tenLoai === 'Phòng Suite' && $soPhong === '301') {
            return 'Phòng Suite Honeymoon';
        }

        if ($tenLoai === 'Phòng Ocean View' && $soPhong === '401') {
            return 'Phòng Ocean View Premium';
        }

        return null;
    }

    /**
     * Tạo ghi chú cho phòng
     */
    private function getGhiChu(string $tenLoai, string $soPhong, bool $coViewDep): ?string
    {
        $notes = [];

        if ($coViewDep) {
            $notes[] = 'View đẹp';
        }

        if ($tenLoai === 'Phòng Suite' && $soPhong === '301') {
            $notes[] = 'Phòng đặc biệt cho cặp đôi';
        }

        if ($tenLoai === 'Phòng Ocean View') {
            $notes[] = 'View biển tuyệt đẹp';
        }

        return !empty($notes) ? implode(', ', $notes) : null;
    }
}
