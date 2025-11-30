<template>
  <div class="orders-page">
    <h2 class="page-title">Quản lý đơn hàng</h2>

    <div class="toolbar">
      <input 
        type="text" 
        v-model="searchQuery" 
        @keyup.enter="fetchOrders" 
        placeholder="Mã đơn, tên người mua, sđt..." 
        class="search-input"
      />
      <select v-model="filterStatus" @change="fetchOrders" class="status-filter">
        <option value="">Tất cả trạng thái</option>
        <option value="pending">Chờ xác nhận</option>
        <option value="processing">Đang xử lý</option>
        <option value="shipped">Đang giao</option>
        <option value="delivered">Đã giao</option>
        <option value="completed">Hoàn thành</option>
        <option value="cancelled">Đã hủy</option>
      </select>
      <button @click="fetchOrders" class="btn btn-primary">Tìm kiếm</button>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Mã đơn</th>
            <th>Người mua</th>
            <th>Sản phẩm</th>
            <th>Tổng tiền</th>
            <th>Thanh toán</th>
            <th>Trạng thái</th>
            <th>Ngày đặt</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id">
            <td>{{ order.ma_don_hang }}</td>
            <td>
              <div>{{ order.buyer_name }}</div>
              <small>{{ order.buyer_phone }}</small>
            </td>
            <td>
              <div class="product-info">
                <img :src="getProductImage(order)" alt="Product" class="product-thumb">
                <span>{{ order.san_pham ? order.san_pham.ten_san_pham : 'Sản phẩm đã xóa' }}</span>
              </div>
            </td>
            <td>{{ formatCurrency(order.tong_tien) }}</td>
            <td>
              <span :class="['payment-badge', order.payment_status]">
                {{ getPaymentStatusLabel(order.payment_status) }}
              </span>
            </td>
            <td>
              <span :class="['status-badge', order.status]">
                {{ getStatusLabel(order.status) }}
              </span>
            </td>
            <td>{{ formatDate(order.created_at) }}</td>
            <td class="actions">
              <button @click="openEditModal(order)" class="btn-icon" title="Cập nhật trạng thái">
                <i class="fas fa-edit"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="totalPages > 1">
      <button 
        :disabled="currentPage === 1" 
        @click="changePage(currentPage - 1)"
      >
        &laquo;
      </button>
      <span>Trang {{ currentPage }} / {{ totalPages }}</span>
      <button 
        :disabled="currentPage === totalPages" 
        @click="changePage(currentPage + 1)"
      >
        &raquo;
      </button>
    </div>

    <!-- Edit Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <h3>Cập nhật đơn hàng #{{ selectedOrder.ma_don_hang }}</h3>
        <form @submit.prevent="updateOrder">
          <div class="form-group">
            <label>Trạng thái đơn hàng</label>
            <select v-model="form.status" class="form-control">
              <option value="pending">Chờ xác nhận</option>
              <option value="processing">Đang xử lý</option>
              <option value="shipped">Đang giao</option>
              <option value="delivered">Đã giao</option>
              <option value="completed">Hoàn thành</option>
              <option value="cancelled">Đã hủy</option>
            </select>
          </div>
          <div class="form-group">
            <label>Trạng thái thanh toán</label>
            <select v-model="form.payment_status" class="form-control">
              <option value="pending">Chờ thanh toán</option>
              <option value="awaiting_payment">Đang chờ thanh toán</option>
              <option value="paid">Đã thanh toán</option>
              <option value="completed">Hoàn tất</option>
              <option value="failed">Thất bại</option>
            </select>
          </div>
          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn btn-secondary">Hủy</button>
            <button type="submit" class="btn btn-primary">Lưu</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ADMIN_API_URL, APP_URL } from '../../../config';

export default {
  name: "AdminOrders",
  data() {
    return {
      orders: [],
      searchQuery: "",
      filterStatus: "",
      currentPage: 1,
      totalPages: 1,
      showModal: false,
      selectedOrder: null,
      form: {
        status: "",
        payment_status: "",
      },
    };
  },
  mounted() {
    this.fetchOrders();
  },
  methods: {
    async fetchOrders() {
      try {
        const token = localStorage.getItem("token");
        const response = await axios.get(`${ADMIN_API_URL}/orders`, {
          params: {
            page: this.currentPage,
            q: this.searchQuery,
            status: this.filterStatus,
          },
          headers: { Authorization: `Bearer ${token}` }
        });
        if (response.data.status) {
          this.orders = response.data.data.data;
          this.totalPages = response.data.data.last_page;
        }
      } catch (error) {
        console.error("Error fetching orders:", error);
      }
    },
    getProductImage(order) {
      if (order.san_pham && order.san_pham.hinh_anh) {
        try {
          const images = JSON.parse(order.san_pham.hinh_anh);
          return Array.isArray(images) && images.length > 0 ? APP_URL + images[0] : 'https://via.placeholder.com/50';
        } catch (e) {
          return 'https://via.placeholder.com/50';
        }
      }
      return 'https://via.placeholder.com/50';
    },
    formatCurrency(value) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    },
    formatDate(dateString) {
      if (!dateString) return '';
      return new Date(dateString).toLocaleString('vi-VN');
    },
    getStatusLabel(status) {
      const map = {
        pending: 'Chờ xác nhận',
        processing: 'Đang xử lý',
        shipped: 'Đang giao',
        delivered: 'Đã giao',
        completed: 'Hoàn thành',
        cancelled: 'Đã hủy',
      };
      return map[status] || status;
    },
    getPaymentStatusLabel(status) {
      const map = {
        pending: 'Chờ thanh toán',
        awaiting_payment: 'Đang chờ',
        paid: 'Đã thanh toán',
        completed: 'Hoàn tất',
        failed: 'Thất bại',
      };
      return map[status] || status;
    },
    openEditModal(order) {
      this.selectedOrder = order;
      this.form = {
        status: order.status,
        payment_status: order.payment_status,
      };
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
      this.selectedOrder = null;
    },
    async updateOrder() {
      try {
        const token = localStorage.getItem("token");
        await axios.put(`${ADMIN_API_URL}/orders/${this.selectedOrder.id}/status`, this.form, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.closeModal();
        this.fetchOrders();
        alert("Cập nhật đơn hàng thành công");
      } catch (error) {
        console.error("Error updating order:", error);
        alert("Lỗi khi cập nhật đơn hàng");
      }
    },
    changePage(page) {
      this.currentPage = page;
      this.fetchOrders();
    }
  },
};
</script>

<style scoped>
.page-title {
  margin-bottom: 20px;
  color: #333;
}

.toolbar {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.search-input {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  width: 250px;
}

.status-filter {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-primary {
  background-color: #007bff;
  color: #fff;
}

.btn-secondary {
  background-color: #6c757d;
  color: #fff;
}

.table-container {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.data-table th {
  background-color: #f8f9fa;
  font-weight: 600;
  color: #495057;
}

.product-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.product-thumb {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 4px;
}

.status-badge, .payment-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.pending { background-color: #fff3cd; color: #856404; }
.status-badge.processing { background-color: #cce5ff; color: #004085; }
.status-badge.shipped { background-color: #d1ecf1; color: #0c5460; }
.status-badge.delivered { background-color: #d4edda; color: #155724; }
.status-badge.completed { background-color: #d4edda; color: #155724; }
.status-badge.cancelled { background-color: #f8d7da; color: #721c24; }

.payment-badge.paid { background-color: #d4edda; color: #155724; }
.payment-badge.pending { background-color: #fff3cd; color: #856404; }
.payment-badge.failed { background-color: #f8d7da; color: #721c24; }

.actions {
  display: flex;
  gap: 5px;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  padding: 5px;
  color: #6c757d;
  transition: color 0.2s;
}

.btn-icon:hover {
  color: #007bff;
}

.pagination {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
}

.pagination button {
  padding: 5px 10px;
  border: 1px solid #ddd;
  background: #fff;
  cursor: pointer;
}

.pagination button:disabled {
  background: #eee;
  cursor: not-allowed;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  width: 400px;
  max-width: 90%;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-control {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}
</style>
