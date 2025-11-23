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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khach_hang_id')->constrained('khach_hangs')->cascadeOnDelete();
            $table->string('type', 50); // order, message, product, system
            $table->string('title', 255);
            $table->text('message');
            $table->string('icon', 50)->nullable(); // emoji hoặc icon class
            $table->string('action_url')->nullable(); // URL để chuyển đến khi click
            $table->json('data')->nullable(); // Dữ liệu bổ sung
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['khach_hang_id', 'is_read']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
