<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds fields for tracking children (6-12 years) and infants (0-5 years)
     * along with their surcharges for hotel bookings.
     */
    public function up(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            // Rename so_nguoi to so_nguoi_lon (adults) for clarity
            // We'll keep so_nguoi as is for backward compatibility and add new fields
            
            // Number of children (6-12 years old)
            $table->integer('so_tre_em')->default(0)->after('so_nguoi')->comment('Number of children (6-12 years)');
            
            // Number of infants (0-5 years old) 
            $table->integer('so_em_be')->default(0)->after('so_tre_em')->comment('Number of infants (0-5 years)');
            
            // Surcharge for children (calculated based on hotel policy)
            $table->decimal('phu_phi_tre_em', 15, 2)->default(0)->after('so_em_be')->comment('Total surcharge for children');
            
            // Surcharge for infants (usually free or minimal)
            $table->decimal('phu_phi_em_be', 15, 2)->default(0)->after('phu_phi_tre_em')->comment('Total surcharge for infants');
        });

        // Also add to loai_phong table for configuring surcharge rates per room type
        Schema::table('loai_phong', function (Blueprint $table) {
            // Surcharge rate for children per night (e.g., 340,000 VND/night as shown in reference)
            $table->decimal('phi_tre_em', 15, 2)->default(0)->after('gia_khuyen_mai')->comment('Child surcharge per night');
            
            // Surcharge rate for infants per night (usually 0 or minimal)
            $table->decimal('phi_em_be', 15, 2)->default(0)->after('phi_tre_em')->comment('Infant surcharge per night');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_phong', function (Blueprint $table) {
            $table->dropColumn(['so_tre_em', 'so_em_be', 'phu_phi_tre_em', 'phu_phi_em_be']);
        });

        Schema::table('loai_phong', function (Blueprint $table) {
            $table->dropColumn(['phi_tre_em', 'phi_em_be']);
        });
    }
};
