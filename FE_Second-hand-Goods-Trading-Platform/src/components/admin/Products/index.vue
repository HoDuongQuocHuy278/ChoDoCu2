<template>
  <div class="products-page">
    <h2 class="page-title">Quản lý sản phẩm</h2>

    <div class="toolbar">
      <input 
        type="text" 
        v-model="searchQuery" 
        @keyup.enter="fetchProducts" 
        placeholder="Tên sản phẩm, mô tả..." 
        class="search-input"
      />
      <select v-model="filterStatus" @change="fetchProducts" class="status-filter">
        <option value="all">Tất cả trạng thái</option>
        <option value="1">Đang bán</option>
        <option value="3">Đã ẩn</option>
      </select>
      <button @click="fetchProducts" class="btn btn-primary">Tìm kiếm</button>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Hình ảnh</th>
            <th>Tên sản phẩm</th>
            <th>Giá</th>
            <th>Người bán</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>{{ product.id }}</td>
            <td>
              <img :src="getProductImage(product)" alt="Product" class="product-thumb">
            </td>
            <td>
              <div class="product-name">{{ product.ten_san_pham }}</div>
              <small>{{ product.category }}</small>
            </td>
            <td>{{ formatCurrency(product.gia) }}</td>
            <td>
              <div v-if="product.khach_hang">
                <div>{{ product.khach_hang.ho_va_ten }}</div>
                <small>{{ product.khach_hang.email }}</small>
              </div>
              <span v-else>Admin</span>
            </td>
            <td>
              <span :class="['status-badge', product.trang_thai == 1 ? 'active' : 'inactive']">
                {{ product.trang_thai == 1 ? 'Đang bán' : 'Đã ẩn' }}
              </span>
            </td>
            <td class="actions">
              <button 
                @click="toggleActive(product)" 
                class="btn-icon" 
                :title="product.trang_thai == 1 ? 'Ẩn sản phẩm' : 'Hiện sản phẩm'"
              >
                <i :class="['fas', product.trang_thai == 1 ? 'fa-eye-slash' : 'fa-eye']"></i>
              </button>
              <button @click="deleteProduct(product)" class="btn-icon delete" title="Xóa">
                <i class="fas fa-trash"></i>
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
  </div>
</template>

<script>
import axios from 'axios';
import { ADMIN_API_URL, APP_URL } from '../../../config';

export default {
  name: "AdminProducts",
  data() {
    return {
      products: [],
      searchQuery: "",
      filterStatus: "all",
      currentPage: 1,
      totalPages: 1,
    };
  },
  mounted() {
    this.fetchProducts();
  },
  methods: {
    async fetchProducts() {
      try {
        const token = localStorage.getItem("token");
        const response = await axios.get(`${ADMIN_API_URL}/products`, {
          params: {
            page: this.currentPage,
            q: this.searchQuery,
            trang_thai: this.filterStatus,
          },
          headers: { Authorization: `Bearer ${token}` }
        });
        if (response.data.status) {
          this.products = response.data.data.data;
          this.totalPages = response.data.data.last_page;
        }
      } catch (error) {
        console.error("Error fetching products:", error);
      }
    },
    getProductImage(product) {
      if (product.hinh_anh) {
        try {
          const images = JSON.parse(product.hinh_anh);
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
    async toggleActive(product) {
      try {
        const token = localStorage.getItem("token");
        const response = await axios.post(`${ADMIN_API_URL}/products/${product.id}/toggle`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        if (response.data.status) {
          product.trang_thai = product.trang_thai == 1 ? 3 : 1;
          alert(response.data.message);
        }
      } catch (error) {
        console.error("Error toggling product:", error);
        alert("Lỗi khi thay đổi trạng thái");
      }
    },
    async deleteProduct(product) {
      if (!confirm("Bạn có chắc muốn xóa sản phẩm này?")) return;

      try {
        const token = localStorage.getItem("token");
        await axios.delete(`${ADMIN_API_URL}/products/${product.id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.fetchProducts();
        alert("Xóa sản phẩm thành công");
      } catch (error) {
        console.error("Error deleting product:", error);
        alert("Lỗi khi xóa sản phẩm");
      }
    },
    changePage(page) {
      this.currentPage = page;
      this.fetchProducts();
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

.product-thumb {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 4px;
}

.product-name {
  font-weight: 500;
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.active {
  background-color: #d4edda;
  color: #155724;
}

.status-badge.inactive {
  background-color: #e2e3e5;
  color: #383d41;
}

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

.btn-icon.delete:hover {
  color: #dc3545;
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
</style>
