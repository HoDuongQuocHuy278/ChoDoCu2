<template>
  <div class="sales-history-container">
    <div class="header-section">
      <h1 class="page-title">📊 LỊCH SỬ BÁN HÀNG</h1>
      
      <!-- Tóm tắt -->
      <div class="summary-cards">
        <div class="summary-card revenue">
          <div class="card-icon">💰</div>
          <div class="card-content">
            <div class="card-label">Tổng Doanh Thu</div>
            <div class="card-value">{{ formatPrice(summary.total_revenue || 0) }}</div>
          </div>
        </div>
        <div class="summary-card orders">
          <div class="card-icon">📦</div>
          <div class="card-content">
            <div class="card-label">Tổng Đơn Hàng</div>
            <div class="card-value">{{ summary.total_orders || 0 }}</div>
          </div>
        </div>
        <div class="summary-card completed">
          <div class="card-icon">✅</div>
          <div class="card-content">
            <div class="card-label">Doanh Thu Đã Thanh Toán</div>
            <div class="card-value">{{ formatPrice(summary.completed_revenue || 0) }}</div>
          </div>
        </div>
        <div class="summary-card average">
          <div class="card-icon">📈</div>
          <div class="card-content">
            <div class="card-label">Giá Trị Đơn Trung Bình</div>
            <div class="card-value">{{ formatPrice(summary.average_order_value || 0) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bộ lọc -->
    <div class="filters-section">
      <div class="filter-group">
        <label>🔎 Tìm kiếm:</label>
        <input 
          v-model="searchText" 
          type="text" 
          placeholder="Tìm theo tên người mua, sản phẩm, mã đơn..." 
          class="search-input"
        />
      </div>
      <div class="filter-group">
        <label>📅 Sắp xếp:</label>
        <select v-model="sortBy" class="select-input">
          <option value="date_desc">Mới nhất</option>
          <option value="date_asc">Cũ nhất</option>
          <option value="amount_desc">Giá cao → thấp</option>
          <option value="amount_asc">Giá thấp → cao</option>
        </select>
      </div>
      <div class="filter-group">
        <label>🔍 Trạng thái:</label>
        <select v-model="filterStatus" class="select-input">
          <option value="all">Tất cả</option>
          <option value="paid">Đã thanh toán</option>
          <option value="pending">Chờ thanh toán</option>
          <option value="completed">Hoàn thành</option>
        </select>
      </div>
      <div class="filter-group">
        <label>⭐ Có đánh giá:</label>
        <select v-model="filterReview" class="select-input">
          <option value="all">Tất cả</option>
          <option value="with_review">Có đánh giá</option>
          <option value="no_review">Chưa đánh giá</option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="loading-section">
      <div class="loading-spinner"></div>
      <p>Đang tải lịch sử bán hàng...</p>
    </div>

    <!-- Empty state -->
    <div v-else-if="!filteredSalesHistory.length" class="empty-state">
      <div class="empty-icon">📭</div>
      <p v-if="!getCurrentUser()">⚠️ Vui lòng đăng nhập để xem lịch sử bán hàng.</p>
      <p v-else>😿 Bạn chưa có đơn hàng nào.</p>
    </div>

    <!-- Bảng lịch sử bán hàng -->
    <div v-else class="table-container">
      <table class="sales-table">
        <thead>
          <tr>
            <th>Thời gian</th>
            <th>Mã đơn</th>
            <th>Người mua</th>
            <th>Sản phẩm</th>
            <th>Số lượng</th>
            <th>Đơn giá</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Đánh giá</th>
            <th class="text-center">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="sale in filteredSalesHistory" :key="sale.order_id" class="table-row">
            <td class="date-cell">
              <div class="date-text">{{ formatDate(sale.order_date) }}</div>
              <small class="time-text">{{ formatTime(sale.order_date) }}</small>
            </td>
            <td class="code-cell">
              <strong>{{ sale.order_code }}</strong>
            </td>
            <td class="buyer-cell">
              <div class="buyer-info">
                <div class="buyer-name">{{ sale.buyer_name }}</div>
                <div v-if="sale.buyer_phone" class="buyer-detail">📞 {{ sale.buyer_phone }}</div>
                <div v-if="sale.buyer_email" class="buyer-detail">✉️ {{ sale.buyer_email }}</div>
                <div v-if="sale.buyer_address" class="buyer-detail">📍 {{ sale.buyer_address }}</div>
              </div>
            </td>
            <td class="product-cell">
              <div class="product-info">
                <img 
                  :src="sale.product_image || fallbackImg" 
                  :alt="sale.product_name"
                  class="product-thumb"
                  @error="onImgError($event)"
                />
                <div class="product-details">
                  <div class="product-name">{{ sale.product_name }}</div>
                  <small class="product-id">ID: {{ sale.product_id }}</small>
                </div>
              </div>
            </td>
            <td class="quantity-cell text-center">
              <strong>{{ sale.quantity }}</strong>
            </td>
            <td class="price-cell">
              {{ formatPrice(sale.product_price) }}
            </td>
            <td class="total-cell">
              <strong>{{ formatPrice(sale.total_amount) }}</strong>
            </td>
            <td class="status-cell">
              <span :class="['status-badge', getStatusClass(sale.payment_status)]">
                {{ getStatusLabel(sale.payment_status) }}
              </span>
            </td>
            <td class="review-cell">
              <div v-if="sale.review" class="review-badge">
                <div class="review-rating">
                  <span v-for="n in 5" :key="n" class="star" :class="{ active: n <= sale.review.rating }">
                    ★
                  </span>
                </div>
                <div class="review-comment">{{ sale.review.comment }}</div>
                <small class="review-date">{{ formatDate(sale.review.reviewed_at) }}</small>
              </div>
              <span v-else class="no-review">Chưa đánh giá</span>
            </td>
            <td class="actions-cell text-center">
              <button 
                class="btn-view-detail" 
                @click="viewDetail(sale)"
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
    <div v-if="selectedSale" class="modal-overlay" @click.self="closeDetail">
      <div class="modal-box">
        <button class="close-btn" @click="closeDetail">✖</button>
        <h2>Chi tiết đơn hàng</h2>
        
        <div class="detail-section">
          <h3>📋 Thông tin đơn hàng</h3>
          <div class="detail-grid">
            <div class="detail-item">
              <label>Mã đơn:</label>
              <span>{{ selectedSale.order_code }}</span>
            </div>
            <div class="detail-item">
              <label>Ngày đặt:</label>
              <span>{{ formatDateTime(selectedSale.order_date) }}</span>
            </div>
            <div class="detail-item">
              <label>Trạng thái:</label>
              <span :class="['status-badge', getStatusClass(selectedSale.payment_status)]">
                {{ getStatusLabel(selectedSale.payment_status) }}
              </span>
            </div>
            <div class="detail-item">
              <label>Phương thức thanh toán:</label>
              <span>{{ getPaymentMethodLabel(selectedSale.payment_method) }}</span>
            </div>
          </div>
        </div>

        <div class="detail-section">
          <h3>👤 Thông tin người mua</h3>
          <div class="detail-grid">
            <div class="detail-item">
              <label>Họ tên:</label>
              <span>{{ selectedSale.buyer_name }}</span>
            </div>
            <div class="detail-item" v-if="selectedSale.buyer_phone">
              <label>Số điện thoại:</label>
              <span>{{ selectedSale.buyer_phone }}</span>
            </div>
            <div class="detail-item" v-if="selectedSale.buyer_email">
              <label>Email:</label>
              <span>{{ selectedSale.buyer_email }}</span>
            </div>
            <div class="detail-item" v-if="selectedSale.buyer_address">
              <label>Địa chỉ:</label>
              <span>{{ selectedSale.buyer_address }}</span>
            </div>
          </div>
        </div>

        <div class="detail-section">
          <h3>📦 Thông tin sản phẩm</h3>
          <div class="product-detail-card">
            <img 
              :src="selectedSale.product_image || fallbackImg" 
              :alt="selectedSale.product_name"
              class="product-detail-img"
              @error="onImgError($event)"
            />
            <div class="product-detail-info">
              <h4>{{ selectedSale.product_name }}</h4>
              <p>Số lượng: <strong>{{ selectedSale.quantity }}</strong></p>
              <p>Đơn giá: <strong>{{ formatPrice(selectedSale.product_price) }}</strong></p>
              <p class="total-price">Tổng tiền: <strong>{{ formatPrice(selectedSale.total_amount) }}</strong></p>
            </div>
          </div>
        </div>

        <div class="detail-section" v-if="selectedSale.review">
          <h3>⭐ Đánh giá</h3>
          <div class="review-detail">
            <div class="review-rating">
              <span v-for="n in 5" :key="n" class="star" :class="{ active: n <= selectedSale.review.rating }">
                ★
              </span>
              <span class="rating-text">{{ selectedSale.review.rating }}/5</span>
            </div>
            <p class="review-comment-text">{{ selectedSale.review.comment }}</p>
            <small class="reviewer-info">
              Đánh giá bởi: <strong>{{ selectedSale.review.reviewer_name }}</strong>
              - {{ formatDateTime(selectedSale.review.reviewed_at) }}
            </small>
          </div>
        </div>

        <div v-else class="detail-section">
          <h3>⭐ Đánh giá</h3>
          <p class="no-review-text">Chưa có đánh giá</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "SalesHistory",
  data() {
    return {
      isLoading: false,
      salesHistory: [],
      summary: {
        total_revenue: 0,
        total_orders: 0,
        completed_revenue: 0,
        average_order_value: 0,
      },
      selectedSale: null,
      
      // Filters
      searchText: "",
      sortBy: "date_desc",
      filterStatus: "all",
      filterReview: "all",
      
      fallbackImg: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDYwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI2MDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0zMDAgMTYwQzM0NS4yMjkgMTYwIDM4MiAxMjMuMjI5IDM4MiA3OEMzODIgMzIuNzcwOSAzNDUuMjI5IDYgMzAwIDZDMjU0Ljc3MCA2IDIxOCAzMi43NzA5IDIxOCA3OEMyMTggMTIzLjIyOSAyNTQuNzcwIDE2MCAzMDAgMTYwWiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNMzAwIDI0MEMyNTAgMjQwIDIxMCAyNTYgMTg1IDI4MEg0MTVDMzkwIDI1NiAzNTAgMjQwIDMwMCAyNDBaIiBmaWxsPSIjOUNBM0FGIi8+Cjx0ZXh0IHg9IjMwMCIgeT0iMzIwIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9IiM5Q0EzQUYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPk5vIEltYWdlPC90ZXh0Pgo8L3N2Zz4=",
      API_BASE_URL: import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/client",
    };
  },
  
  computed: {
    filteredSalesHistory() {
      let filtered = [...this.salesHistory];
      
      // Filter theo search text
      if (this.searchText.trim()) {
        const search = this.searchText.toLowerCase();
        filtered = filtered.filter(sale => 
          sale.buyer_name.toLowerCase().includes(search) ||
          sale.product_name.toLowerCase().includes(search) ||
          sale.order_code.toLowerCase().includes(search) ||
          (sale.buyer_email && sale.buyer_email.toLowerCase().includes(search)) ||
          (sale.buyer_phone && sale.buyer_phone.includes(search))
        );
      }
      
      // Filter theo status
      if (this.filterStatus !== 'all') {
        filtered = filtered.filter(sale => {
          if (this.filterStatus === 'paid') return sale.payment_status === 'paid';
          if (this.filterStatus === 'pending') return sale.payment_status === 'pending';
          if (this.filterStatus === 'completed') return sale.order_status === 'completed';
          return true;
        });
      }
      
      // Filter theo đánh giá
      if (this.filterReview !== 'all') {
        if (this.filterReview === 'with_review') {
          filtered = filtered.filter(sale => sale.review !== null);
        } else if (this.filterReview === 'no_review') {
          filtered = filtered.filter(sale => sale.review === null);
        }
      }
      
      // Sort
      filtered.sort((a, b) => {
        if (this.sortBy === 'date_desc') {
          return new Date(b.order_date) - new Date(a.order_date);
        } else if (this.sortBy === 'date_asc') {
          return new Date(a.order_date) - new Date(b.order_date);
        } else if (this.sortBy === 'amount_desc') {
          return b.total_amount - a.total_amount;
        } else if (this.sortBy === 'amount_asc') {
          return a.total_amount - b.total_amount;
        }
        return 0;
      });
      
      return filtered;
    },
  },
  
  mounted() {
    this.fetchSalesHistory();
  },
  
  methods: {
    getCurrentUser() {
      try {
        const token = localStorage.getItem('key_client');
        if (!token) return null;
        const userInfoStr = localStorage.getItem('user_info');
        if (userInfoStr) {
          return JSON.parse(userInfoStr);
        }
      } catch (e) {
        console.warn('Không thể lấy thông tin người dùng', e);
      }
      return null;
    },
    
    async fetchSalesHistory() {
      const userInfo = this.getCurrentUser();
      if (!userInfo?.id) {
        this.salesHistory = [];
        this.isLoading = false;
        return;
      }

      this.isLoading = true;
      try {
        const token = localStorage.getItem('key_client');
        const res = await axios.get(`${this.API_BASE_URL}/seller/sales-history`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        
        if (res?.data?.status && res?.data?.data) {
          this.salesHistory = res.data.data.sales_history || [];
          this.summary = res.data.data.summary || {};
        }
      } catch (e) {
        console.error('Error fetching sales history:', e);
        if (this.$toast) {
          this.$toast.error('Không thể tải lịch sử bán hàng');
        } else {
          alert('Không thể tải lịch sử bán hàng');
        }
        this.salesHistory = [];
      } finally {
        this.isLoading = false;
      }
    },
    
    viewDetail(sale) {
      this.selectedSale = sale;
    },
    
    closeDetail() {
      this.selectedSale = null;
    },
    
    getStatusClass(status) {
      const map = {
        'paid': 'status-paid',
        'completed': 'status-completed',
        'pending': 'status-pending',
        'awaiting_payment': 'status-pending',
      };
      return map[status] || 'status-default';
    },
    
    getStatusLabel(status) {
      const map = {
        'paid': 'Đã thanh toán',
        'completed': 'Hoàn thành',
        'pending': 'Chờ thanh toán',
        'awaiting_payment': 'Chờ thanh toán',
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
    
    formatDate(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleDateString('vi-VN');
    },
    
    formatTime(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    },
    
    formatDateTime(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleString('vi-VN');
    },
    
    onImgError(e) {
      e.target.src = this.fallbackImg;
    },
  },
};
</script>

<style scoped>
.sales-history-container {
  font-family: "Poppins", "Segoe UI", sans-serif;
  background-color: #f5f5f5;
  min-height: 100vh;
  padding: 20px;
  padding-bottom: 40px;
}

.header-section {
  margin-bottom: 30px;
}

.page-title {
  text-align: center;
  color: #ff5722;
  font-size: 32px;
  font-weight: 700;
  margin-bottom: 20px;
  padding: 20px;
}

.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 15px;
  transition: transform 0.3s;
}

.summary-card:hover {
  transform: translateY(-4px);
}

.summary-card.revenue {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.summary-card.orders {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.summary-card.completed {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  color: white;
}

.summary-card.average {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  color: white;
}

.card-icon {
  font-size: 40px;
}

.card-content {
  flex: 1;
}

.card-label {
  font-size: 14px;
  opacity: 0.9;
  margin-bottom: 5px;
}

.card-value {
  font-size: 24px;
  font-weight: bold;
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

.search-input {
  flex: 1;
  min-width: 250px;
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

.sales-table {
  width: 100%;
  border-collapse: collapse;
}

.sales-table thead {
  background: #f8f9fa;
  position: sticky;
  top: 0;
}

.sales-table th {
  padding: 15px;
  text-align: left;
  font-weight: bold;
  color: #333;
  border-bottom: 2px solid #dee2e6;
  white-space: nowrap;
}

.sales-table td {
  padding: 15px;
  border-bottom: 1px solid #dee2e6;
  vertical-align: top;
}

.sales-table tbody tr:hover {
  background: #f8f9fa;
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

.code-cell {
  font-family: monospace;
}

.buyer-info {
  max-width: 250px;
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

.product-details {
  flex: 1;
}

.product-name {
  font-weight: 500;
  margin-bottom: 4px;
}

.product-id {
  color: #666;
  font-size: 12px;
}

.quantity-cell {
  font-size: 16px;
}

.price-cell, .total-cell {
  white-space: nowrap;
}

.status-badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
  white-space: nowrap;
}

.status-paid {
  background: #d4edda;
  color: #155724;
}

.status-completed {
  background: #cce5ff;
  color: #004085;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-default {
  background: #e2e3e5;
  color: #383d41;
}

.review-badge {
  max-width: 200px;
}

.review-rating {
  margin-bottom: 5px;
}

.star {
  color: #ddd;
  font-size: 14px;
}

.star.active {
  color: #ffb400;
}

.review-comment {
  font-size: 12px;
  color: #555;
  margin: 5px 0;
  line-height: 1.4;
}

.review-date {
  color: #999;
  font-size: 11px;
}

.no-review {
  color: #999;
  font-style: italic;
}

.actions-cell {
  white-space: nowrap;
}

.btn-view-detail {
  background: #007bff;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s;
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

.review-detail {
  padding: 15px;
  background: #f9f9f9;
  border-radius: 8px;
}

.rating-text {
  margin-left: 10px;
  font-weight: bold;
  color: #333;
}

.review-comment-text {
  margin: 10px 0;
  color: #555;
  line-height: 1.6;
}

.reviewer-info {
  color: #999;
  font-size: 12px;
}

.no-review-text {
  color: #999;
  font-style: italic;
  padding: 15px;
}

@media (max-width: 768px) {
  .sales-table {
    font-size: 12px;
  }
  
  .sales-table th,
  .sales-table td {
    padding: 8px;
  }
  
  .summary-cards {
    grid-template-columns: 1fr;
  }
}
</style>

