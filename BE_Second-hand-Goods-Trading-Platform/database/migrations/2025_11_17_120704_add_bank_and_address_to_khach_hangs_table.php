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
        Schema::table('khach_hangs', function (Blueprint $table) {
            // Thông tin ngân hàng
            $table->string('ten_ngan_hang')->nullable()->after('ngay_sinh');
            $table->string('so_tai_khoan')->nullable()->after('ten_ngan_hang');
            $table->string('chu_tai_khoan')->nullable()->after('so_tai_khoan');
            
            // Thông tin địa chỉ
            $table->string('dia_chi_ho_ten')->nullable()->after('chu_tai_khoan');
            $table->string('dia_chi_so_dien_thoai')->nullable()->after('dia_chi_ho_ten');
            $table->text('dia_chi_chi_tiet')->nullable()->after('dia_chi_so_dien_thoai');
            
            // Giới tính
            $table->string('gioi_tinh')->nullable()->after('dia_chi_chi_tiet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khach_hangs', function (Blueprint $table) {
            $table->dropColumn([
                'ten_ngan_hang',
                'so_tai_khoan',
                'chu_tai_khoan',
                'dia_chi_ho_ten',
                'dia_chi_so_dien_thoai',
                'dia_chi_chi_tiet',
                'gioi_tinh'
            ]);
        });
    }
};
