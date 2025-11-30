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
        if (!Schema::hasColumn('khach_hangs', 'role')) {
            Schema::table('khach_hangs', function (Blueprint $table) {
                $table->integer('role')->default(0)->after('password')->comment('0: User, 1: Admin');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('khach_hangs', 'role')) {
            Schema::table('khach_hangs', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
