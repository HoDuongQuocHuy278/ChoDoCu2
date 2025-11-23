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
        Schema::create('san_phams', function (Blueprint $table) {
            $table->id();
            $table->string('ten_san_pham');
            $table->text('mo_ta')->nullable();
            $table->decimal('gia', 15, 2);
            $table->string('hinh_anh')->nullable();
            $table->string('tinh_trang')->default('moi')->comment('moi, cu, rat_cu');
            $table->string('category')->nullable();
            $table->unsignedBigInteger('danh_muc_id')->nullable();
            $table->foreign('danh_muc_id')
            ->references('id')
            ->on('danh_mucs')
            ->nullOnDelete();
            $table->string('thuong_hieu')->nullable();
            $table->string('mau_sac')->nullable();
            $table->string('kich_thuoc')->nullable();
            $table->text('dia_chi')->nullable();
            $table->string('tinh_thanh')->nullable();
            $table->string('quan_huyen')->nullable();
            $table->foreignId('khach_hang_id')->constrained('khach_hangs')->onDelete('cascade');
            $table->integer('trang_thai')->default(1)->comment('1: đang bán, 2: đã bán, 3: đã ẩn');
            $table->integer('luot_xem')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('san_phams');
    }
};
