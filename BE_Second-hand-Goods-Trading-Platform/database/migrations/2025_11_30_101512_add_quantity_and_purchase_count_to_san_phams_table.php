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
        Schema::table('san_phams', function (Blueprint $table) {
            $table->integer('so_luong')->default(1)->after('gia')->comment('Số lượng sản phẩm còn lại');
            $table->integer('so_luot_mua')->default(0)->after('luot_xem')->comment('Số lượt mua/đã bán');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('san_phams', function (Blueprint $table) {
            $table->dropColumn(['so_luong', 'so_luot_mua']);
        });
    }
};
