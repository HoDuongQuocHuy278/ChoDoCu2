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
        // Indexes cho bảng don_hangs
        try {
            if (Schema::hasTable('don_hangs')) {
                Schema::table('don_hangs', function (Blueprint $table) {
                    // Index cho khach_hang_id (thường query khi lấy đơn hàng của buyer)
                    if (!$this->hasIndex('don_hangs', 'don_hangs_khach_hang_id_index')) {
                        $table->index('khach_hang_id', 'don_hangs_khach_hang_id_index');
                    }
                    
                    // Index cho san_pham_id (thường query khi lấy đơn hàng của seller)
                    if (!$this->hasIndex('don_hangs', 'don_hangs_san_pham_id_index')) {
                        $table->index('san_pham_id', 'don_hangs_san_pham_id_index');
                    }
                    
                    // Index cho status và payment_status (thường filter)
                    if (!$this->hasIndex('don_hangs', 'don_hangs_status_index')) {
                        $table->index('status', 'don_hangs_status_index');
                    }
                    
                    if (!$this->hasIndex('don_hangs', 'don_hangs_payment_status_index')) {
                        $table->index('payment_status', 'don_hangs_payment_status_index');
                    }
                    
                    // Composite index cho created_at và status (thường sort và filter)
                    if (!$this->hasIndex('don_hangs', 'don_hangs_created_at_status_index')) {
                        $table->index(['created_at', 'status'], 'don_hangs_created_at_status_index');
                    }
                    
                    // Index cho buyer_email và buyer_phone (để match với user)
                    if (!$this->hasIndex('don_hangs', 'don_hangs_buyer_email_index')) {
                        $table->index('buyer_email', 'don_hangs_buyer_email_index');
                    }
                    
                    if (!$this->hasIndex('don_hangs', 'don_hangs_buyer_phone_index')) {
                        $table->index('buyer_phone', 'don_hangs_buyer_phone_index');
                    }
                });
            }
        } catch (\Exception $e) {
            // Table might not exist yet, skip
        }
        
        // Indexes cho bảng san_phams
        try {
            if (Schema::hasTable('san_phams')) {
                Schema::table('san_phams', function (Blueprint $table) {
                    // Index cho khach_hang_id (thường query khi lấy sản phẩm của seller)
                    if (!$this->hasIndex('san_phams', 'san_phams_khach_hang_id_index')) {
                        $table->index('khach_hang_id', 'san_phams_khach_hang_id_index');
                    }
                    
                    // Index cho danh_muc_id (thường filter)
                    if (!$this->hasIndex('san_phams', 'san_phams_danh_muc_id_index')) {
                        $table->index('danh_muc_id', 'san_phams_danh_muc_id_index');
                    }
                    
                    // Index cho trang_thai (thường filter)
                    if (!$this->hasIndex('san_phams', 'san_phams_trang_thai_index')) {
                        $table->index('trang_thai', 'san_phams_trang_thai_index');
                    }
                    
                    // Composite index cho gia và trang_thai (thường sort và filter)
                    if (!$this->hasIndex('san_phams', 'san_phams_gia_trang_thai_index')) {
                        $table->index(['gia', 'trang_thai'], 'san_phams_gia_trang_thai_index');
                    }
                    
                    // Index cho created_at (thường sort)
                    if (!$this->hasIndex('san_phams', 'san_phams_created_at_index')) {
                        $table->index('created_at', 'san_phams_created_at_index');
                    }
                });
            }
        } catch (\Exception $e) {
            // Table might not exist yet, skip
        }
        
        // Indexes cho bảng danh_gias
        try {
            if (Schema::hasTable('danh_gias')) {
                Schema::table('danh_gias', function (Blueprint $table) {
                    // Index cho san_pham_id (thường query)
                    if (!$this->hasIndex('danh_gias', 'danh_gias_san_pham_id_index')) {
                        $table->index('san_pham_id', 'danh_gias_san_pham_id_index');
                    }
                    
                    // Composite index cho san_pham_id và is_active (thường filter)
                    if (!$this->hasIndex('danh_gias', 'danh_gias_san_pham_id_is_active_index')) {
                        $table->index(['san_pham_id', 'is_active'], 'danh_gias_san_pham_id_is_active_index');
                    }
                });
            }
        } catch (\Exception $e) {
            // Table might not exist yet, skip
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('don_hangs')) {
            Schema::table('don_hangs', function (Blueprint $table) {
                $table->dropIndex('don_hangs_khach_hang_id_index');
                $table->dropIndex('don_hangs_san_pham_id_index');
                $table->dropIndex('don_hangs_status_index');
                $table->dropIndex('don_hangs_payment_status_index');
                $table->dropIndex('don_hangs_created_at_status_index');
                $table->dropIndex('don_hangs_buyer_email_index');
                $table->dropIndex('don_hangs_buyer_phone_index');
            });
        }
        
        if (Schema::hasTable('san_phams')) {
            Schema::table('san_phams', function (Blueprint $table) {
                $table->dropIndex('san_phams_khach_hang_id_index');
                $table->dropIndex('san_phams_danh_muc_id_index');
                $table->dropIndex('san_phams_trang_thai_index');
                $table->dropIndex('san_phams_gia_trang_thai_index');
                $table->dropIndex('san_phams_created_at_index');
            });
        }
        
        if (Schema::hasTable('danh_gias')) {
            Schema::table('danh_gias', function (Blueprint $table) {
                $table->dropIndex('danh_gias_san_pham_id_index');
                $table->dropIndex('danh_gias_san_pham_id_is_active_index');
            });
        }
    }
    
    /**
     * Kiểm tra xem index đã tồn tại chưa
     */
    private function hasIndex($table, $indexName): bool
    {
        if (!Schema::hasTable($table)) {
            return false;
        }
        
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        
        $result = $connection->select(
            "SELECT COUNT(*) as count FROM information_schema.statistics 
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$databaseName, $table, $indexName]
        );
        
        return $result[0]->count > 0;
    }
};

