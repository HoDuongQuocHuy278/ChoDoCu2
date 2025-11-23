<template>
  <div class="orders-container">
    <h1 class="page-title">🛒 ĐƠN HÀNG CỦA TÔI</h1>

    <!-- Bộ lọc -->
    <div class="filters-section">
      <div class="filter-group">
        <label>🔎 Tìm kiếm:</label>
        <input 
          v-model="searchText" 
          type="text" 
          placeholder="Tìm theo mã đơn, tên sản phẩm..." 
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
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="loading-section">
      <div class="loading-spinner"></div>
      <p>Đang tải đơn hàng...</p>
    </div>

    <!-- Empty state -->
    <div v-else-if="!filteredOrders.length" class="empty-state">
      <div class="empty-icon">📭</div>
      <p>Bạn chưa có đơn hàng nào.</p>
    </div>

    <!-- Danh sách đơn hàng -->
    <div v-else class="orders-list">
      <div 
        v-for="order in filteredOrders" 
        :key="order.id" 
        class="order-card"
      >
        <div class="order-header">
          <div class="order-code">
            <strong>Mã đơn: {{ order.order_code }}</strong>
            <span :class="['status-badge', getStatusClass(order.status)]">
              {{ getStatusLabel(order.status) }}
            </span>
          </div>
          <div class="order-date">
            Đặt ngày: {{ formatDateTime(order.created_at) }}
          </div>
        </div>

        <div class="order-body">
          <div class="product-section">
            <img 
              :src="order.product_image || fallbackImg" 
              :alt="order.product_name"
              class="product-img"
              @error="onImgError($event)"
            />
            <div class="product-info">
              <h3 class="product-name">{{ order.product_name }}</h3>
              <div class="product-details">
                <span>Số lượng: <strong>{{ order.quantity }}</strong></span>
                <span class="separator">|</span>
                <span>Tổng tiền: <strong class="total-amount">{{ formatPrice(order.total_amount) }}</strong></span>
              </div>
              <div class="payment-info">
                <span>Thanh toán: </span>
                <span :class="['payment-badge', getPaymentClass(order.payment_status)]">
                  {{ getPaymentLabel(order.payment_status) }}
                </span>
                <span class="separator">|</span>
                <span>Phương thức: {{ getPaymentMethodLabel(order.payment_method) }}</span>
              </div>
              <div v-if="order.shipping_address" class="shipping-address">
                📍 Địa chỉ giao hàng: {{ order.shipping_address }}
              </div>
            </div>
          </div>
        </div>

        <div class="order-footer">
          <div class="order-actions">
            <button 
              v-if="order.status === 'delivered' || order.status === 'shipped'"
              class="btn-confirm"
              @click="confirmReceived(order)"
            >
              ✓ Xác nhận đã nhận hàng
            </button>
            <button 
              class="btn-view-detail"
              @click="viewDetail(order)"
            >
              👁️ Xem chi tiết
            </button>
          </div>
        </div>
      </div>
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
              <span :class="['payment-badge', getPaymentClass(selectedOrder.payment_status)]">
                {{ getPaymentLabel(selectedOrder.payment_status) }}
              </span>
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

        <div class="detail-section" v-if="selectedOrder.shipping_address">
          <h3>📍 Địa chỉ giao hàng</h3>
          <p>{{ selectedOrder.shipping_address }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "BuyerOrders",
  data() {
    return {
      isLoading: false,
      orders: [],
      selectedOrder: null,
      searchText: "",
      filterStatus: "all",
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
          order.product_name.toLowerCase().includes(search)
        );
      }
      
      if (this.filterStatus !== 'all') {
        filtered = filtered.filter(order => order.status === this.filterStatus);
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
        const res = await axios.get(`${this.API_BASE_URL}/don-hang-mua`, {
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
    
    async confirmReceived(order) {
      if (!confirm(`Xác nhận bạn đã nhận được hàng cho đơn hàng ${order.order_code}?`)) return;
      
      try {
        const token = localStorage.getItem('key_client');
        const res = await axios.post(
          `${this.API_BASE_URL}/don-hang/${order.id}/xac-nhan-nhan-hang`,
          {},
          { headers: { 'Authorization': `Bearer ${token}` } }
        );
        
        if (res?.data?.status) {
          order.status = 'completed';
          if (this.$toast) {
            this.$toast.success('Đã xác nhận nhận hàng thành công!');
          } else {
            alert('Đã xác nhận nhận hàng thành công!');
          }
          await this.fetchOrders();
        }
      } catch (e) {
        const msg = e?.response?.data?.message || 'Không thể xác nhận nhận hàng';
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
    
    getPaymentMethodLabel(method) {
      const map = {
        'vnpay': 'VNPay',
        'momo': 'MoMo',
        'cash': 'Tiền mặt',
      };
      return map[method] || method;
    },
    
    formatPrice(amount) {
      return (Number(amount) || 0).toLocaleString('vi-VN') + ' ₫';
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

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.order-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
}

.order-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.order-header {
  padding: 15px 20px;
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.order-code {
  display: flex;
  align-items: center;
  gap: 10px;
}

.order-date {
  color: #666;
  font-size: 14px;
}

.order-body {
  padding: 20px;
}

.product-section {
  display: flex;
  gap: 15px;
}

.product-img {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border-radius: 8px;
}

.product-info {
  flex: 1;
}

.product-name {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 10px;
  color: #333;
}

.product-details {
  display: flex;
  gap: 10px;
  align-items: center;
  margin-bottom: 8px;
  font-size: 14px;
  color: #666;
}

.separator {
  color: #ddd;
}

.total-amount {
  color: #ff5722;
  font-size: 16px;
}

.payment-info {
  display: flex;
  gap: 10px;
  align-items: center;
  margin-bottom: 8px;
  font-size: 14px;
  color: #666;
}

.payment-badge {
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
}

.shipping-address {
  margin-top: 10px;
  padding: 10px;
  background: #f8f9fa;
  border-radius: 6px;
  font-size: 14px;
  color: #555;
}

.order-footer {
  padding: 15px 20px;
  background: #f8f9fa;
  border-top: 1px solid #dee2e6;
}

.order-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.btn-confirm {
  background: #28a745;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  transition: background 0.3s;
}

.btn-confirm:hover {
  background: #218838;
}

.btn-view-detail {
  background: #007bff;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  transition: background 0.3s;
}

.btn-view-detail:hover {
  background: #0056b3;
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

