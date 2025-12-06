<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yeu_cau_doi_phong', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('dat_phong_id');
            $table->unsignedBigInteger('phong_cu_id');
            $table->unsignedBigInteger('phong_moi_id');

            $table->text('ly_do');

            $table->enum('trang_thai', ['cho_duyet', 'da_duyet', 'bi_tu_choi'])
                ->default('cho_duyet');

            $table->unsignedBigInteger('nguoi_duyet')->nullable();
            $table->text('ghi_chu_admin')->nullable();

            $table->timestamps();

            $table->foreign('dat_phong_id')
                ->references('id')
                ->on('dat_phong')
                ->onDelete('cascade');

            $table->foreign('phong_cu_id')
                ->references('id')
                ->on('phong')
                ->onDelete('cascade');

            $table->foreign('phong_moi_id')
                ->references('id')
                ->on('phong')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yeu_cau_doi_phong');
    }
};
