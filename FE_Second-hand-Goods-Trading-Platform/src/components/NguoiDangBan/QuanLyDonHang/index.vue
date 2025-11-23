<template>
  <div class="orders-container">
    <h1 class="page-title">📦 QUẢN LÝ ĐƠN HÀNG</h1>

    <!-- Bộ lọc -->
    <div class="filters-section">
      <div class="filter-group">
        <label>🔎 Tìm kiếm:</label>
        <input 
          v-model="searchText" 
          type="text" 
          placeholder="Tìm theo mã đơn, tên người mua..." 
          class="search-input"
        />
      </div>
      <div class="filter-group">
        <label>📊 Trạng thái:</label>
        <select v-model="filterStatus" class="select-input">
          <option value="all">Tất cả</option>
          <option value="pending">Chờ xử lý</option>
          <option value="processing">Đang xử lý</option>
          <option value="shipped">Đã giao hàng</option>
          <option value="delivered">Đã đến nơi</option>
          <option value="completed">Hoàn thành</option>
          <option value="cancelled">Đã hủy</option>
        </select>
      </div>
      <div class="filter-group">
        <label>💰 Thanh toán:</label>
        <select v-model="filterPayment" class="select-input">
          <option value="all">Tất cả</option>
          <option value="pending">Chờ thanh toán</option>
          <option value="paid">Đã thanh toán</option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="loading-section">
      <div class="loading-spinner"></div>
      <p>Đang tải đơn hàng...</p>
    </div>

    <!-- Empty state -->
    <div v-else-if="!filteredOrders.length" class="empty-state">
      <div class="empty-icon">📭</div>
      <p>Chưa có đơn hàng nào.</p>
    </div>

    <!-- Bảng đơn hàng -->
    <div v-else class="table-container">
      <table class="orders-table">
        <thead>
          <tr>
            <th>Mã đơn</th>
            <th>Sản phẩm</th>
            <th>Người mua</th>
            <th>Số lượng</th>
            <th>Tổng tiền</th>
            <th>Thanh toán</th>
            <th>Trạng thái</th>
            <th>Ngày đặt</th>
            <th class="text-center">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in filteredOrders" :key="order.id">
            <td class="code-cell">
              <strong>{{ order.order_code }}</strong>
            </td>
            <td class="product-cell">
              <div class="product-info">
                <img 
                  :src="order.product_image || fallbackImg" 
                  :alt="order.product_name"
                  class="product-thumb"
                  @error="onImgError($event)"
                />
                <span class="product-name">{{ order.product_name }}</span>
              </div>
            </td>
            <td class="buyer-cell">
              <div class="buyer-info">
                <div class="buyer-name">{{ order.buyer_name }}</div>
                <div v-if="order.buyer_phone" class="buyer-detail">📞 {{ order.buyer_phone }}</div>
                <div v-if="order.buyer_email" class="buyer-detail">✉️ {{ order.buyer_email }}</div>
              </div>
            </td>
            <td class="quantity-cell text-center">
              <strong>{{ order.quantity }}</strong>
            </td>
            <td class="total-cell">
              <strong>{{ formatPrice(order.total_amount) }}</strong>
            </td>
            <td class="payment-cell">
              <span :class="['status-badge', getPaymentClass(order.payment_status)]">
                {{ getPaymentLabel(order.payment_status) }}
              </span>
              <button 
                v-if="order.payment_status !== 'paid'"
                class="btn-mark-paid"
                @click="markAsPaid(order)"
                title="Đánh dấu đã thanh toán"
              >
                ✓
              </button>
            </td>
            <td class="status-cell">
              <select 
                v-model="order.status" 
                class="status-select"
                @change="updateStatus(order)"
              >
                <option value="pending">Chờ xử lý</option>
                <option value="processing">Đang xử lý</option>
                <option value="shipped">Đã giao hàng</option>
                <option value="delivered">Đã đến nơi</option>
                <option value="completed">Hoàn thành</option>
                <option value="cancelled">Đã hủy</option>
              </select>
            </td>
            <td class="date-cell">
              <div class="date-text">{{ formatDate(order.created_at) }}</div>
              <small class="time-text">{{ formatTime(order.created_at) }}</small>
            </td>
            <td class="actions-cell text-center">
              <button 
                class="btn-view-detail" 
                @click="viewDetail(order)"
                title="Xem chi tiết"
              >
                👁️
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal chi tiết -->
    <div v-if="selectedOrder" class="modal-overlay" @click.self="closeDetail">
      <div class="modal-box">
        <button class="close-btn" @click="closeDetail">✖</button>
        <h2>Chi tiết đơn hàng</h2>
        
        <div class="detail-section">
          <h3>📋 Thông tin đơn hàng</h3>
          <div class="detail-grid">
            <div class="detail-item">
              <label>Mã đơn:</label>
              <span>{{ selectedOrder.order_code }}</span>
            </div>
            <div class="detail-item">
              <label>Ngày đặt:</label>
              <span>{{ formatDateTime(selectedOrder.created_at) }}</span>
            </div>
            <div class="detail-item">
              <label>Trạng thái:</label>
              <span :class="['status-badge', getStatusClass(selectedOrder.status)]">
                {{ getStatusLabel(selectedOrder.status) }}
              </span>
            </div>
            <div class="detail-item">
              <label>Thanh toán:</label>
              <span :class="['status-badge', getPaymentClass(selectedOrder.payment_status)]">
                {{ getPaymentLabel(selectedOrder.payment_status) }}
              </span>
            </div>
          </div>
        </div>

        <div class="detail-section">
          <h3>👤 Thông tin người mua</h3>
          <div class="detail-grid">
            <div class="detail-item">
              <label>Họ tên:</label>
              <span>{{ selectedOrder.buyer_name }}</span>
            </div>
            <div class="detail-item" v-if="selectedOrder.buyer_phone">
              <label>Số điện thoại:</label>
              <span>{{ selectedOrder.buyer_phone }}</span>
            </div>
            <div class="detail-item" v-if="selectedOrder.buyer_email">
              <label>Email:</label>
              <span>{{ selectedOrder.buyer_email }}</span>
            </div>
            <div class="detail-item" v-if="selectedOrder.buyer_address">
              <label>Địa chỉ:</label>
              <span>{{ selectedOrder.buyer_address }}</span>
            </div>
          </div>
        </div>

        <div class="detail-section">
          <h3>📦 Thông tin sản phẩm</h3>
          <div class="product-detail-card">
            <img 
              :src="selectedOrder.product_image || fallbackImg" 
              :alt="selectedOrder.product_name"
              class="product-detail-img"
              @error="onImgError($event)"
            />
            <div class="product-detail-info">
              <h4>{{ selectedOrder.product_name }}</h4>
              <p>Số lượng: <strong>{{ selectedOrder.quantity }}</strong></p>
              <p class="total-price">Tổng tiền: <strong>{{ formatPrice(selectedOrder.total_amount) }}</strong></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "SellerOrders",
  data() {
    return {
      isLoading: false,
      orders: [],
      selectedOrder: null,
      searchText: "",
      filterStatus: "all",
      filterPayment: "all",
      fallbackImg: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDYwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI2MDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0zMDAgMTYwQzM0NS4yMjkgMTYwIDM4MiAxMjMuMjI5IDM4MiA3OEMzODIgMzIuNzcwOSAzNDUuMjI5IDYgMzAwIDZDMjU0Ljc3MCA2IDIxOCAzMi43NzA5IDIxOCA3OEMyMTggMTIzLjIyOSAyNTQuNzcwIDE2MCAzMDAgMTYwWiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNMzAwIDI0MEMyNTAgMjQwIDIxMCAyNTYgMTg1IDI4MEg0MTVDMzkwIDI1NiAzNTAgMjQwIDMwMCAyNDBaIiBmaWxsPSIjOUNBM0FGIi8+Cjx0ZXh0IHg9IjMwMCIgeT0iMzIwIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9IiM5Q0EzQUYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPk5vIEltYWdlPC90ZXh0Pgo8L3N2Zz4=",
      API_BASE_URL: import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/client",
    };
  },
  
  computed: {
    filteredOrders() {
      let filtered = [...this.orders];
      
      if (this.searchText.trim()) {
        const search = this.searchText.toLowerCase();
        filtered = filtered.filter(order => 
          order.order_code.toLowerCase().includes(search) ||
          order.buyer_name.toLowerCase().includes(search) ||
          order.product_name.toLowerCase().includes(search)
        );
      }
      
      if (this.filterStatus !== 'all') {
        filtered = filtered.filter(order => order.status === this.filterStatus);
      }
      
      if (this.filterPayment !== 'all') {
        filtered = filtered.filter(order => order.payment_status === this.filterPayment);
      }
      
      return filtered;
    },
  },
  
  mounted() {
    this.fetchOrders();
  },
  
  methods: {
    async fetchOrders() {
      this.isLoading = true;
      try {
        const token = localStorage.getItem('key_client');
        const res = await axios.get(`${this.API_BASE_URL}/don-hang-ban`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        
        if (res?.data?.status) {
          this.orders = res.data.data || [];
        }
      } catch (e) {
        console.error('Error fetching orders:', e);
        if (this.$toast) {
          this.$toast.error('Không thể tải danh sách đơn hàng');
        } else {
          alert('Không thể tải danh sách đơn hàng');
        }
        this.orders = [];
      } finally {
        this.isLoading = false;
      }
    },
    
    async updateStatus(order) {
      try {
        const token = localStorage.getItem('key_client');
        const res = await axios.put(
          `${this.API_BASE_URL}/don-hang/${order.id}/trang-thai`,
          { status: order.status },
          { headers: { 'Authorization': `Bearer ${token}` } }
        );
        
        if (res?.data?.status) {
          if (this.$toast) {
            this.$toast.success('Cập nhật trạng thái thành công');
          }
        }
      } catch (e) {
        const msg = e?.response?.data?.message || 'Không thể cập nhật trạng thái';
        if (this.$toast) {
          this.$toast.error(msg);
        } else {
          alert(msg);
        }
        // Revert status
        await this.fetchOrders();
      }
    },
    
    async markAsPaid(order) {
      if (!confirm(`Xác nhận đã thanh toán cho đơn hàng ${order.order_code}?`)) return;
      
      try {
        const token = localStorage.getItem('key_client');
        const res = await axios.put(
          `${this.API_BASE_URL}/don-hang/${order.id}/thanh-toan`,
          { payment_status: 'paid' },
          { headers: { 'Authorization': `Bearer ${token}` } }
        );
        
        if (res?.data?.status) {
          order.payment_status = 'paid';
          if (this.$toast) {
            this.$toast.success('Đã đánh dấu đã thanh toán');
          }
        }
      } catch (e) {
        const msg = e?.response?.data?.message || 'Không thể cập nhật trạng thái thanh toán';
        if (this.$toast) {
          this.$toast.error(msg);
        } else {
          alert(msg);
        }
      }
    },
    
    viewDetail(order) {
      this.selectedOrder = order;
    },
    
    closeDetail() {
      this.selectedOrder = null;
    },
    
    getStatusClass(status) {
      const map = {
        'pending': 'status-pending',
        'processing': 'status-processing',
        'shipped': 'status-shipped',
        'delivered': 'status-delivered',
        'completed': 'status-completed',
        'cancelled': 'status-cancelled',
      };
      return map[status] || 'status-default';
    },
    
    getStatusLabel(status) {
      const map = {
        'pending': 'Chờ xử lý',
        'processing': 'Đang xử lý',
        'shipped': 'Đã giao hàng',
        'delivered': 'Đã đến nơi',
        'completed': 'Hoàn thành',
        'cancelled': 'Đã hủy',
      };
      return map[status] || status;
    },
    
    getPaymentClass(status) {
      const map = {
        'pending': 'status-pending',
        'awaiting_payment': 'status-pending',
        'paid': 'status-paid',
        'completed': 'status-completed',
        'failed': 'status-cancelled',
      };
      return map[status] || 'status-default';
    },
    
    getPaymentLabel(status) {
      const map = {
        'pending': 'Chờ thanh toán',
        'awaiting_payment': 'Chờ thanh toán',
        'paid': 'Đã thanh toán',
        'completed': 'Hoàn thành',
        'failed': 'Thất bại',
      };
      return map[status] || status;
    },
    
    formatPrice(amount) {
      return (Number(amount) || 0).toLocaleString('vi-VN') + ' ₫';
    },
    
    formatDate(dateStr) {
      if (!dateStr) return '';
      return new Date(dateStr).toLocaleDateString('vi-VN');
    },
    
    formatTime(dateStr) {
      if (!dateStr) return '';
      return new Date(dateStr).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    },
    
    formatDateTime(dateStr) {
      if (!dateStr) return '';
      return new Date(dateStr).toLocaleString('vi-VN');
    },
    
    onImgError(e) {
      e.target.src = this.fallbackImg;
    },
  },
};
</script>

<style scoped>
.orders-container {
  font-family: "Poppins", "Segoe UI", sans-serif;
  background-color: #f5f5f5;
  min-height: 100vh;
  padding: 20px;
  padding-bottom: 40px;
}

.page-title {
  text-align: center;
  color: #ff5722;
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 30px;
  padding: 20px;
}

.filters-section {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  margin-bottom: 20px;
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  align-items: center;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.filter-group label {
  font-weight: bold;
  white-space: nowrap;
}

.search-input, .select-input {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
  min-width: 200px;
}

.loading-section {
  text-align: center;
  padding: 60px 20px;
}

.loading-spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #ff5722;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  overflow-x: auto;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
}

.orders-table thead {
  background: #f8f9fa;
  position: sticky;
  top: 0;
}

.orders-table th {
  padding: 15px;
  text-align: left;
  font-weight: bold;
  color: #333;
  border-bottom: 2px solid #dee2e6;
  white-space: nowrap;
}

.orders-table td {
  padding: 15px;
  border-bottom: 1px solid #dee2e6;
  vertical-align: middle;
}

.orders-table tbody tr:hover {
  background: #f8f9fa;
}

.code-cell {
  font-family: monospace;
  font-weight: 600;
}

.product-info {
  display: flex;
  gap: 10px;
  align-items: center;
}

.product-thumb {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 6px;
}

.product-name {
  font-weight: 500;
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.buyer-info {
  max-width: 200px;
}

.buyer-name {
  font-weight: 600;
  margin-bottom: 4px;
}

.buyer-detail {
  font-size: 12px;
  color: #666;
  margin-top: 2px;
}

.quantity-cell, .total-cell {
  white-space: nowrap;
}

.payment-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
  white-space: nowrap;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-processing {
  background: #cce5ff;
  color: #004085;
}

.status-shipped {
  background: #d1ecf1;
  color: #0c5460;
}

.status-delivered {
  background: #d4edda;
  color: #155724;
}

.status-completed {
  background: #d4edda;
  color: #155724;
}

.status-paid {
  background: #d4edda;
  color: #155724;
}

.status-cancelled {
  background: #f8d7da;
  color: #721c24;
}

.status-default {
  background: #e2e3e5;
  color: #383d41;
}

.status-select {
  padding: 4px 8px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
}

.btn-mark-paid {
  background: #28a745;
  color: white;
  border: none;
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
}

.btn-mark-paid:hover {
  background: #218838;
}

.date-cell {
  white-space: nowrap;
}

.date-text {
  font-weight: 500;
}

.time-text {
  color: #666;
  font-size: 12px;
}

.btn-view-detail {
  background: #007bff;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
}

.btn-view-detail:hover {
  background: #0056b3;
}

.text-center {
  text-align: center;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  padding: 20px;
  overflow-y: auto;
}

.modal-box {
  background: white;
  border-radius: 12px;
  padding: 30px;
  max-width: 700px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 10px 25px rgba(0,0,0,0.3);
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  border: none;
  background: none;
  font-size: 24px;
  color: #999;
  cursor: pointer;
  padding: 5px;
  line-height: 1;
}

.close-btn:hover {
  color: #333;
}

.modal-box h2 {
  margin-bottom: 20px;
  color: #333;
}

.detail-section {
  margin-bottom: 25px;
  padding-bottom: 25px;
  border-bottom: 1px solid #eee;
}

.detail-section:last-child {
  border-bottom: none;
}

.detail-section h3 {
  margin-bottom: 15px;
  color: #ff5722;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.detail-item label {
  font-weight: bold;
  color: #666;
  font-size: 14px;
}

.detail-item span {
  color: #333;
}

.product-detail-card {
  display: flex;
  gap: 15px;
  padding: 15px;
  background: #f9f9f9;
  border-radius: 8px;
}

.product-detail-img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 8px;
}

.product-detail-info h4 {
  margin-bottom: 10px;
  color: #333;
}

.product-detail-info p {
  margin: 5px 0;
  color: #555;
}

.total-price {
  font-size: 18px;
  color: #ff5722;
  margin-top: 10px;
}
</style>

