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
        Schema::create('don_hangs', function (Blueprint $table) {
            $table->id();
            $table->string('ma_don_hang', 20)->unique();
            $table->foreignId('san_pham_id')->constrained('san_phams')->cascadeOnDelete();
            $table->foreignId('khach_hang_id')->nullable()->constrained('khach_hangs')->nullOnDelete();

            $table->unsignedInteger('so_luong')->default(1);
            $table->decimal('tong_tien', 12, 2);

            $table->string('buyer_name');
            $table->string('buyer_email')->nullable();
            $table->string('buyer_phone', 20)->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('notes')->nullable();

            $table->string('payment_method', 20);
            $table->string('payment_status', 30)->default('pending');
            $table->string('status', 30)->default('pending');
            $table->json('payment_payload')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('don_hangs');
    }
};
