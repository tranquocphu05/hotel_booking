<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loai_phong', function (Blueprint $table) {
            // Sức chứa tối đa của loại phòng (số người lớn)
            $table->integer('suc_chua')->default(2)->after('mo_ta')
                ->comment('Số người lớn tối đa');
            // Số trẻ em tối đa
            $table->integer('suc_chua_tre_em')->default(1)->after('suc_chua')
                ->comment('Số trẻ em tối đa (6-12 tuổi)');
            // Số em bé tối đa
            $table->integer('suc_chua_em_be')->default(1)->after('suc_chua_tre_em')
                ->comment('Số em bé tối đa (0-5 tuổi)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loai_phong', function (Blueprint $table) {
            $table->dropColumn(['suc_chua', 'suc_chua_tre_em', 'suc_chua_em_be']);
        });
    }
};
