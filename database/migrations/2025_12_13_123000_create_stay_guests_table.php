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
        if (!Schema::hasTable('stay_guests')) {
            Schema::create('stay_guests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('dat_phong_id');
                $table->unsignedBigInteger('phong_id')->nullable();
                $table->string('full_name');
                $table->date('dob')->nullable();
                $table->integer('age')->nullable();
                $table->decimal('extra_fee', 15, 2)->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('dat_phong_id')->references('id')->on('dat_phong')->onDelete('cascade');
                $table->foreign('phong_id')->references('id')->on('phong')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stay_guests')) {
            Schema::dropIfExists('stay_guests');
        }
    }
};
