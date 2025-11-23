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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user1_id')->constrained('khach_hangs')->cascadeOnDelete();
            $table->foreignId('user2_id')->constrained('khach_hangs')->cascadeOnDelete();
            $table->foreignId('san_pham_id')->nullable()->constrained('san_phams')->nullOnDelete();
            $table->string('room')->unique()->comment('Room identifier: chat:min_id-max_id');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user1_id', 'user2_id']);
            $table->index('last_message_at');
            $table->index('san_pham_id');
            
            // Prevent duplicate chats between same users
            $table->unique(['user1_id', 'user2_id', 'san_pham_id'], 'unique_chat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};

