<template>
  <div class="shop-container" @keyup.esc="closeDetail" tabindex="0">
    <div class="header-section">
      <h1 class="shop-title">🛍 QUẢN LÝ SẢN PHẨM</h1>
      
      <!-- Tổng doanh thu -->
      <div class="stats-summary">
        <div class="stat-card total-revenue">
          <div class="stat-icon">💰</div>
          <div class="stat-content">
            <div class="stat-label">Tổng Doanh Thu</div>
            <div class="stat-value">{{ formatPrice(totalRevenue) }}</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">📦</div>
          <div class="stat-content">
            <div class="stat-label">Tổng Đơn Hàng</div>
            <div class="stat-value">{{ totalOrders }}</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">⭐</div>
          <div class="stat-content">
            <div class="stat-label">Đánh Giá Trung Bình</div>
            <div class="stat-value">{{ averageRating }}/5</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bộ lọc -->
    <div class="filter-bar">
      <div class="filter-group">
        <label>🔎 Tìm kiếm:</label>
        <input v-model="searchText" type="text" placeholder="Nhập tên sản phẩm…" />
      </div>
      <div class="filter-group">
        <label>🔍 Loại sản phẩm:</label>
        <select v-model="selectedCategory">
          <option value="all">Tất cả</option>
          <option v-for="(cat, index) in categories" :key="index" :value="cat">{{ cat }}</option>
        </select>
      </div>
      <div class="filter-group">
        <label>💰 Sắp xếp theo giá:</label>
        <select v-model="selectedPriceSort">
          <option value="none">Không sắp xếp</option>
          <option value="asc">Tăng dần</option>
          <option value="desc">Giảm dần</option>
        </select>
      </div>
      <div class="filter-group">
        <router-link to="/sell" class="btn-add-product">
          ➕ Đăng Sản Phẩm Mới
        </router-link>
      </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <main class="product-list">
      <div v-if="isLoading" class="skeleton-grid">
        <div v-for="n in 8" :key="n" class="product-card skeleton">
          <div class="skeleton-img"></div>
          <div class="skeleton-line w-70"></div>
          <div class="skeleton-line w-40"></div>
        </div>
      </div>

      <div v-else-if="!filteredProducts.length" class="empty-state">
        <p v-if="!getCurrentUser()">⚠️ Vui lòng đăng nhập để xem sản phẩm của bạn.</p>
        <p v-else>😿 Bạn chưa có sản phẩm nào được đăng bán.</p>
        <router-link v-if="getCurrentUser()" to="/sell" class="buy-btn">Đăng Sản Phẩm Ngay</router-link>
      </div>

      <template v-else>
        <div
          class="product-card"
          v-for="(product, index) in filteredProducts"
          :key="product.id || index"
        >
          <div class="product-header">
            <img
              :src="product.image"
              :alt="product.name"
              class="product-img"
              loading="lazy"
              @error="onImgError($event)"
              @click="showDetail(product)"
            />
            <div class="product-status" :class="product.trang_thai === 1 ? 'active' : 'hidden'">
              {{ product.trang_thai === 1 ? 'Đang bán' : 'Đã ẩn' }}
            </div>
          </div>
          
          <div class="product-body" @click="showDetail(product)">
            <h3 class="product-name" :title="product.name">{{ product.name || product.ten_san_pham || 'Chưa có tên' }}</h3>
            
            <!-- Thống kê sản phẩm -->
            <div class="product-stats" v-if="productStats[product.id]">
              <div class="stat-item">
                <span class="stat-icon-small">📦</span>
                <span>{{ productStats[product.id].total_orders || 0 }} đơn</span>
              </div>
              <div class="stat-item">
                <span class="stat-icon-small">💰</span>
                <span>{{ formatPrice(productStats[product.id].total_revenue || 0) }}</span>
              </div>
              <div class="stat-item">
                <span class="stat-icon-small">⭐</span>
                <span>{{ productStats[product.id].average_rating || 0 }}/5</span>
              </div>
            </div>

            <div class="price-section">
              <p class="price">{{ formatPrice(product.price) }}</p>
            </div>
          </div>

          <div class="product-actions" @click.stop>
            <button class="action-btn status-btn" @click="toggleProductStatus(product)" :title="product.trang_thai === 1 ? 'Ẩn sản phẩm' : 'Hiện sản phẩm'">
              {{ product.trang_thai === 1 ? '👁️ Ẩn' : '👁️‍🗨️ Hiện' }}
            </button>
            <button class="action-btn edit-btn" @click="editProduct(product)" title="Sửa sản phẩm">
              ✏️ Sửa
            </button>
            <button class="action-btn delete-btn" @click="confirmDelete(product)" title="Xóa sản phẩm">
              🗑️ Xóa
            </button>
            <button class="action-btn view-btn" @click="showDetail(product)" title="Xem chi tiết">
              👁️ Chi tiết
            </button>
          </div>
        </div>
      </template>
    </main>

    <!-- Phân trang -->
    <div v-if="!isLoading && pagination.last_page > 1" class="pagination">
      <button :disabled="pagination.current_page === 1" @click="goPage(pagination.current_page - 1)">« Trước</button>
      <span>Trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>
      <button :disabled="pagination.current_page === pagination.last_page" @click="goPage(pagination.current_page + 1)">Sau »</button>
    </div>

    <!-- Modal chi tiết sản phẩm -->
    <div v-if="selectedProduct" class="detail-overlay" @click.self="closeDetail">
      <div class="detail-box">
        <button class="close-btn" @click="closeDetail">✖</button>

        <div class="detail-top">
          <img :src="selectedProduct.image" class="detail-img" @error="onImgError($event)" />
          <div class="detail-info">
            <h2>{{ selectedProduct.name }}</h2>
            <p class="desc">{{ selectedProduct.description }}</p>
            <div class="price-box">
              <p class="price">{{ formatPrice(selectedProduct.price) }}</p>
            </div>
          </div>
        </div>

        <div class="detail-tabs">
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'orders' }"
            @click="activeTab = 'orders'"
          >
            📦 Đơn Hàng ({{ currentOrders.length }})
          </button>
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'buyers' }"
            @click="activeTab = 'buyers'"
          >
            👥 Người Mua ({{ currentBuyers.length }})
          </button>
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'reviews' }"
            @click="activeTab = 'reviews'"
          >
            ⭐ Đánh Giá ({{ currentReviews.length }})
          </button>
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'stats' }"
            @click="activeTab = 'stats'"
          >
            📊 Thống Kê
          </button>
        </div>

        <div class="detail-bottom">
          <!-- Tab Đơn hàng -->
          <div v-if="activeTab === 'orders'" class="tab-content">
            <div v-if="loadingOrders" class="loading-text">Đang tải...</div>
            <div v-else-if="!currentOrders.length" class="empty-review">Chưa có đơn hàng nào.</div>
            <div v-else class="orders-list">
              <div v-for="order in currentOrders" :key="order.id" class="order-item">
                <div class="order-header">
                  <strong>Mã đơn: {{ order.order_code }}</strong>
                  <span class="order-status" :class="order.status">{{ order.status }}</span>
                </div>
                <div class="order-info">
                  <p><strong>Người mua:</strong> {{ order.buyer_name }}</p>
                  <p><strong>Số lượng:</strong> {{ order.quantity }}</p>
                  <p><strong>Tổng tiền:</strong> {{ formatPrice(order.total) }}</p>
                  <p><strong>Thanh toán:</strong> {{ order.payment_method }} - {{ order.payment_status }}</p>
                  <p><strong>Ngày đặt:</strong> {{ order.created_at }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab Người mua -->
          <div v-if="activeTab === 'buyers'" class="tab-content">
            <div v-if="loadingOrders" class="loading-text">Đang tải...</div>
            <div v-else-if="!currentBuyers.length" class="empty-review">Chưa có người mua nào.</div>
            <div v-else class="buyers-list">
              <div v-for="(buyer, idx) in currentBuyers" :key="idx" class="buyer-item">
                <div class="buyer-header">
                  <strong>{{ buyer.name }}</strong>
                  <span class="buyer-order-code">Đơn: {{ buyer.order_code }}</span>
                </div>
                <div class="buyer-info">
                  <p><strong>📞 SĐT:</strong> {{ buyer.phone || 'Chưa có' }}</p>
                  <p><strong>✉️ Email:</strong> {{ buyer.email || 'Chưa có' }}</p>
                  <p><strong>📍 Địa chỉ:</strong> {{ buyer.address || 'Chưa có' }}</p>
                  <p><strong>📦 Số lượng:</strong> {{ buyer.quantity }}</p>
                  <p><strong>💰 Tổng tiền:</strong> {{ formatPrice(buyer.total) }}</p>
                  <p><strong>📅 Ngày mua:</strong> {{ buyer.order_date }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab Đánh giá -->
          <div v-if="activeTab === 'reviews'" class="tab-content">
            <div v-if="loadingReviews" class="loading-text">Đang tải...</div>
            <div v-else-if="!currentReviews.length" class="empty-review">Chưa có đánh giá nào.</div>
            <div v-else class="reviews-list">
              <div v-for="review in currentReviews" :key="review.id" class="review-item">
                <div class="review-header">
                  <strong>{{ review.buyer_name }}</strong>
                  <span class="stars">
                    <span v-for="n in 5" :key="n">{{ n <= review.rating ? "★" : "☆" }}</span>
                  </span>
                </div>
                <p class="review-comment">{{ review.comment }}</p>
                <small class="review-date">{{ review.created_at }}</small>
              </div>
            </div>
          </div>

          <!-- Tab Thống kê -->
          <div v-if="activeTab === 'stats'" class="tab-content">
            <div v-if="selectedProductStats" class="stats-detail">
              <div class="stat-row">
                <span class="stat-label">📦 Tổng đơn hàng:</span>
                <span class="stat-value">{{ selectedProductStats.total_orders || 0 }}</span>
              </div>
              <div class="stat-row">
                <span class="stat-label">💰 Tổng doanh thu:</span>
                <span class="stat-value">{{ formatPrice(selectedProductStats.total_revenue || 0) }}</span>
              </div>
              <div class="stat-row">
                <span class="stat-label">⭐ Đánh giá trung bình:</span>
                <span class="stat-value">{{ selectedProductStats.average_rating || 0 }}/5</span>
              </div>
              <div class="stat-row">
                <span class="stat-label">📝 Số đánh giá:</span>
                <span class="stat-value">{{ selectedProductStats.total_reviews || 0 }}</span>
              </div>
            </div>
            <div v-else class="empty-review">Chưa có thống kê.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal xác nhận xóa -->
    <div v-if="productToDelete" class="modal-overlay" @click.self="productToDelete = null">
      <div class="modal-box">
        <h3>Xác nhận xóa sản phẩm</h3>
        <p>Bạn có chắc chắn muốn xóa sản phẩm <strong>{{ productToDelete.name }}</strong>?</p>
        <p class="warning-text">⚠️ Hành động này không thể hoàn tác!</p>
        <div class="modal-actions">
          <button class="btn-cancel" @click="productToDelete = null">Hủy</button>
          <button class="btn-confirm" @click="deleteProduct">Xóa</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

let debounceTimer = null;
const debounce = (fn, delay = 400) => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(fn, delay);
};

export default {
  name: "SellerProducts",
  data() {
    return {
      searchText: "",
      selectedCategory: "all",
      selectedPriceSort: "none",
      
      products: [],
      productStats: {}, // { product_id: { total_orders, total_revenue, average_rating, buyers } }
      isLoading: false,
      selectedProduct: null,
      selectedProductStats: null,
      productToDelete: null,
      
      activeTab: 'orders',
      currentOrders: [],
      currentBuyers: [],
      currentReviews: [],
      loadingOrders: false,
      loadingReviews: false,
      
      categories: ["Đồ điện tử", "Gia dụng", "Phụ kiện", "Thời trang", "Thiết bị thông minh", "Khác"],
      
      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 20,
      },
      
      totalRevenue: 0,
      totalOrders: 0,
      averageRating: 0,
      
      fallbackImg: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDYwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI2MDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0zMDAgMTYwQzM0NS4yMjkgMTYwIDM4MiAxMjMuMjI5IDM4MiA3OEMzODIgMzIuNzcwOSAzNDUuMjI5IDYgMzAwIDZDMjU0Ljc3MCA2IDIxOCAzMi43NzA5IDIxOCA3OEMyMTggMTIzLjIyOSAyNTQuNzcwIDE2MCAzMDAgMTYwWiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNMzAwIDI0MEMyNTAgMjQwIDIxMCAyNTYgMTg1IDI4MEg0MTVDMzkwIDI1NiAzNTAgMjQwIDMwMCAyNDBaIiBmaWxsPSIjOUNBM0FGIi8+Cjx0ZXh0IHg9IjMwMCIgeT0iMzIwIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9IiM5Q0EzQUYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPk5vIEltYWdlPC90ZXh0Pgo8L3N2Zz4=",
      API_BASE_URL: import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/client",
    };
  },
  
  computed: {
    filteredProducts() {
      return this.products;
    },
  },
  
  mounted() {
    this.fetchProducts();
    this.fetchProductStats();
    try { this.$el.focus?.(); } catch {}
  },
  
  watch: {
    selectedCategory() { this.goFirstAndFetch(); },
    selectedPriceSort() { this.goFirstAndFetch(); },
    searchText() {
      debounce(() => this.goFirstAndFetch());
    },
  },
  
  methods: {
    goFirstAndFetch() {
      this.pagination.current_page = 1;
      this.fetchProducts();
    },
    
    paramsForAPI() {
      const params = { page: this.pagination.current_page };
      if (this.selectedCategory !== "all") params.category = this.selectedCategory;
      if (this.selectedPriceSort === "asc") params.sort = "price_asc";
      if (this.selectedPriceSort === "desc") params.sort = "price_desc";
      if (this.searchText?.trim()) params.q = this.searchText.trim();
      
      const userInfo = this.getCurrentUser();
      if (userInfo?.id) {
        params.seller_id = userInfo.id;
      }
      
      return params;
    },
    
    getCurrentUser() {
      try {
        const token = localStorage.getItem('key_client');
        if (!token) return null;
        
        // Lấy từ user_info hoặc call API
        const userInfoStr = localStorage.getItem('user_info');
        if (userInfoStr) {
          return JSON.parse(userInfoStr);
        }
      } catch (e) {
        console.warn('Không thể lấy thông tin người dùng', e);
      }
      return null;
    },
    
    async fetchProducts() {
      const userInfo = this.getCurrentUser();
      if (!userInfo?.id) {
        this.products = [];
        this.isLoading = false;
        return;
      }

      this.isLoading = true;
      try {
        const res = await axios.get(`${this.API_BASE_URL}/san-pham`, { 
          params: this.paramsForAPI() 
        });
        const payload = res?.data?.data;
        const rawProducts = payload?.data || [];
        
        // Map và normalize dữ liệu sản phẩm
        this.products = rawProducts.map(product => ({
          id: product.id,
          name: product.name || product.ten_san_pham || 'Chưa có tên',
          ten_san_pham: product.ten_san_pham || product.name,
          description: product.description || product.mo_ta,
          price: product.price || product.gia || 0,
          image: product.image || (product.images && product.images[0]) || null,
          images: product.images || [],
          category: product.category,
          tinh_trang: product.condition || product.tinh_trang,
          trang_thai: product.trang_thai !== undefined ? product.trang_thai : 1, // 1: đang bán, 3: ẩn
          hinh_anh: product.hinh_anh || product.image
        }));
        
        this.pagination.current_page = payload?.current_page || 1;
        this.pagination.last_page = payload?.last_page || 1;
        this.pagination.total = payload?.total || this.products.length;
        this.pagination.per_page = payload?.per_page || 20;
      } catch (e) {
        console.error('Error fetching products:', e);
        this.products = [];
      } finally {
        this.isLoading = false;
      }
    },
    
    async fetchProductStats() {
      const userInfo = this.getCurrentUser();
      if (!userInfo?.id) return;

      try {
        const token = localStorage.getItem('key_client');
        const res = await axios.get(`${this.API_BASE_URL}/seller/product-stats`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        
        if (res?.data?.status && res?.data?.data) {
          const stats = {};
          let totalRevenue = 0;
          let totalOrders = 0;
          let totalRating = 0;
          let ratingCount = 0;
          
          res.data.data.forEach(stat => {
            stats[stat.product_id] = stat;
            totalRevenue += stat.total_revenue || 0;
            totalOrders += stat.total_orders || 0;
            if (stat.average_rating > 0) {
              totalRating += stat.average_rating;
              ratingCount++;
            }
          });
          
          this.productStats = stats;
          this.totalRevenue = totalRevenue;
          this.totalOrders = totalOrders;
          this.averageRating = ratingCount > 0 ? (totalRating / ratingCount).toFixed(1) : 0;
        }
      } catch (e) {
        console.error('Error fetching product stats:', e);
      }
    },
    
    async showDetail(product) {
      this.selectedProduct = product;
      this.selectedProductStats = this.productStats[product.id] || null;
      this.activeTab = 'orders';
      
      // Load orders và reviews
      await Promise.all([
        this.fetchProductOrders(product.id),
        this.fetchProductReviews(product.id)
      ]);
    },
    
    async fetchProductOrders(productId) {
      this.loadingOrders = true;
      try {
        const token = localStorage.getItem('key_client');
        const res = await axios.get(`${this.API_BASE_URL}/seller/san-pham/${productId}/orders`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        
        if (res?.data?.status) {
          this.currentOrders = res.data.data || [];
          // Lấy danh sách người mua từ orders
          this.currentBuyers = this.currentOrders.map(order => ({
            name: order.buyer_name,
            phone: order.buyer_phone,
            email: order.buyer_email,
            address: order.shipping_address,
            order_code: order.order_code,
            quantity: order.quantity,
            total: order.total,
            order_date: order.created_at,
          }));
        }
      } catch (e) {
        console.error('Error fetching orders:', e);
        this.currentOrders = [];
        this.currentBuyers = [];
      } finally {
        this.loadingOrders = false;
      }
    },
    
    async fetchProductReviews(productId) {
      this.loadingReviews = true;
      try {
        const token = localStorage.getItem('key_client');
        const res = await axios.get(`${this.API_BASE_URL}/seller/san-pham/${productId}/reviews`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        
        if (res?.data?.status) {
          this.currentReviews = res.data.data?.reviews || [];
        }
      } catch (e) {
        console.error('Error fetching reviews:', e);
        this.currentReviews = [];
      } finally {
        this.loadingReviews = false;
      }
    },
    
    closeDetail() {
      this.selectedProduct = null;
      this.selectedProductStats = null;
      this.currentOrders = [];
      this.currentBuyers = [];
      this.currentReviews = [];
    },
    
    editProduct(product) {
      this.$router.push({
        name: 'seller.create',
        query: { edit: product.id }
      });
    },
    
    confirmDelete(product) {
      this.productToDelete = product;
    },
    
    async deleteProduct() {
      if (!this.productToDelete) return;
      
      try {
        const token = localStorage.getItem('key_client');
        const res = await axios.delete(
          `${this.API_BASE_URL}/seller/san-pham/${this.productToDelete.id}`,
          {
            headers: { 'Authorization': `Bearer ${token}` }
          }
        );
        
        if (res?.data?.status) {
          if (this.$toast) {
            this.$toast.success('Đã xóa sản phẩm thành công!');
          } else {
            alert('Đã xóa sản phẩm thành công!');
          }
          this.productToDelete = null;
          await this.fetchProducts();
          await this.fetchProductStats();
        }
      } catch (e) {
        const msg = e?.response?.data?.message || 'Có lỗi xảy ra khi xóa sản phẩm';
        if (this.$toast) {
          this.$toast.error(msg);
        } else {
          alert(msg);
        }
      }
    },
    
    async toggleProductStatus(product) {
      if (!product || !product.id) return;
      
      const newStatus = product.trang_thai === 1 ? 3 : 1; // 1: đang bán, 3: ẩn
      const statusText = newStatus === 1 ? 'hiển thị' : 'ẩn';
      
      if (!confirm(`Bạn có chắc chắn muốn ${statusText} sản phẩm "${product.name || product.ten_san_pham}"?`)) {
        return;
      }
      
      try {
        const token = localStorage.getItem('key_client');
        const res = await axios.put(
          `${this.API_BASE_URL}/seller/san-pham/${product.id}`,
          {
            trang_thai: newStatus
          },
          {
            headers: { 
              'Authorization': `Bearer ${token}`,
              'Content-Type': 'application/json'
            }
          }
        );
        
        if (res?.data?.status) {
          // Cập nhật trạng thái trong danh sách
          const index = this.products.findIndex(p => p.id === product.id);
          if (index !== -1) {
            this.products[index].trang_thai = newStatus;
          }
          
          // Cập nhật trong filteredProducts nếu có
          const filteredIndex = this.filteredProducts.findIndex(p => p.id === product.id);
          if (filteredIndex !== -1) {
            this.filteredProducts[filteredIndex].trang_thai = newStatus;
          }
          
          if (this.$toast) {
            this.$toast.success(`Đã ${statusText} sản phẩm thành công!`);
          } else {
            alert(`Đã ${statusText} sản phẩm thành công!`);
          }
        }
      } catch (e) {
        const msg = e?.response?.data?.message || `Có lỗi xảy ra khi ${statusText} sản phẩm`;
        if (this.$toast) {
          this.$toast.error(msg);
        } else {
          alert(msg);
        }
      }
    },
    
    async goPage(p) {
      if (p < 1 || p > this.pagination.last_page) return;
      this.pagination.current_page = p;
      await this.fetchProducts();
      window.scrollTo({ top: 0, behavior: "smooth" });
    },
    
    resetFilters() {
      this.searchText = "";
      this.selectedCategory = "all";
      this.selectedPriceSort = "none";
    },
    
    onImgError(e) {
      e.target.src = this.fallbackImg;
    },
    
    formatPrice(v) {
      return (Number(v) || 0).toLocaleString("vi-VN") + " ₫";
    },
  },
};
</script>

<style scoped>
.shop-container {
  font-family: "Poppins", "Segoe UI", sans-serif;
  background-color: #f9f9f9;
  padding: 20px;
  padding-bottom: 100px;
  margin-bottom: 0;
  outline: none;
  position: relative;
  z-index: 1;
  width: 100%;
  box-sizing: border-box;
  overflow: visible !important;
  overflow-y: visible !important;
  overflow-x: hidden !important;
  height: auto !important;
  max-height: none !important;
}

.header-section {
  margin-bottom: 30px;
}

.shop-title {
  text-align: center;
  color: #ff5722;
  padding: 20px;
  font-size: 32px;
  font-weight: 700;
  letter-spacing: 1px;
  margin-bottom: 20px;
}

.stats-summary {
  display: flex;
  gap: 20px;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 15px;
  min-width: 200px;
}

.stat-card.total-revenue {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.stat-icon {
  font-size: 36px;
}

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 14px;
  opacity: 0.8;
  margin-bottom: 5px;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
}

.filter-bar {
  display: flex;
  justify-content: center;
  gap: 20px;
  flex-wrap: wrap;
  margin-bottom: 25px;
  align-items: center;
}

.filter-group label {
  font-weight: bold;
  margin-right: 6px;
  color: #444;
}

.filter-group select,
.filter-group input[type="text"] {
  padding: 8px 14px;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 15px;
  background-color: #fff;
}

.btn-add-product {
  background: #28a745;
  color: white;
  padding: 10px 20px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: bold;
  transition: background 0.3s;
}

.btn-add-product:hover {
  background: #218838;
}

.product-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 25px;
  overflow: visible !important;
  overflow-y: visible !important;
}

.product-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 20px;
  overflow: visible;
  box-shadow: 
    0 4px 6px rgba(0, 0, 0, 0.07),
    0 1px 3px rgba(0, 0, 0, 0.06),
    0 0 0 1px rgba(0, 0, 0, 0.05);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  position: relative;
  border: 1px solid rgba(226, 232, 240, 0.8);
}

.product-card::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 20px;
  padding: 2px;
  background: linear-gradient(135deg, rgba(22, 163, 74, 0.1), rgba(34, 197, 94, 0.1));
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask-composite: exclude;
  opacity: 0;
  transition: opacity 0.4s ease;
}

.product-card:hover::before {
  opacity: 1;
}

.product-card .product-header {
  overflow: hidden;
  border-radius: 20px 20px 0 0;
  position: relative;
}

.product-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 
    0 20px 40px rgba(22, 163, 74, 0.15),
    0 10px 20px rgba(0, 0, 0, 0.1),
    0 0 0 1px rgba(22, 163, 74, 0.2);
  border-color: rgba(22, 163, 74, 0.3);
}

.product-header {
  position: relative;
}

.product-img {
  width: 100%;
  height: 240px;
  object-fit: cover;
  cursor: pointer;
  transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
}

.product-card:hover .product-img {
  transform: scale(1.1);
}

.product-status {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 8px 16px;
  font-size: 12px;
  font-weight: 700;
  border-radius: 20px;
  z-index: 10;
  color: #fff;
  background: linear-gradient(135deg, #64748b, #475569);
  box-shadow: 
    0 4px 12px rgba(0, 0, 0, 0.15),
    0 2px 4px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
  letter-spacing: 0.5px;
  text-transform: uppercase;
  transition: all 0.3s ease;
}

.product-card:hover .product-status {
  transform: scale(1.05);
  box-shadow: 
    0 6px 16px rgba(0, 0, 0, 0.2),
    0 2px 6px rgba(0, 0, 0, 0.15);
}

/* Sản phẩm đang bán */
.product-status.active {
  background: linear-gradient(135deg, #22c55e, #16a34a) !important;
  color: #fff !important;
  box-shadow: 
    0 4px 12px rgba(22, 163, 74, 0.3),
    0 2px 4px rgba(22, 163, 74, 0.2) !important;
}

/* Sản phẩm đã ẩn */
.product-status.hidden {
  background: linear-gradient(135deg, #fbbf24, #f59e0b) !important;
  color: #fff !important;
  box-shadow: 
    0 4px 12px rgba(251, 191, 36, 0.3),
    0 2px 4px rgba(251, 191, 36, 0.2) !important;
}
.product-body {
  padding: 20px;
  cursor: pointer;
  margin-bottom: -20px;
  flex: 1;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 252, 0.9) 100%);
  transition: background 0.3s ease;
}

.product-card:hover .product-body {
  background: linear-gradient(180deg, rgba(255, 255, 255, 1) 0%, rgba(240, 253, 244, 0.5) 100%);
}

.product-body h3 {
  font-size: 16px;
  margin-bottom: 10px;
  color: #333;
}

.product-name {
  font-size: 17px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 12px;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  min-height: 51px;
  transition: color 0.3s ease;
  letter-spacing: -0.3px;
}

.product-card:hover .product-name {
  color: #16a34a;
}

.product-stats {
  display: flex;
  gap: 12px;
  margin: 12px 0;
  font-size: 13px;
  flex-wrap: wrap;
}

.stat-item {
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: linear-gradient(135deg, rgba(241, 245, 249, 0.8), rgba(226, 232, 240, 0.8));
  border-radius: 12px;
  border: 1px solid rgba(226, 232, 240, 0.6);
  transition: all 0.3s ease;
  font-weight: 600;
  color: #475569;
}

.product-card:hover .stat-item {
  background: linear-gradient(135deg, rgba(240, 253, 244, 0.9), rgba(220, 252, 231, 0.9));
  border-color: rgba(22, 163, 74, 0.2);
  color: #16a34a;
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(22, 163, 74, 0.1);
}

.stat-icon-small {
  font-size: 18px;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
}

.price-section {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 2px solid rgba(226, 232, 240, 0.5);
}

.price {
  color: #dc2626;
  font-weight: 800;
  font-size: 22px;
  background: linear-gradient(135deg, #dc2626, #ef4444);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: 0 2px 4px rgba(220, 38, 38, 0.1);
  letter-spacing: -0.5px;
  transition: all 0.3s ease;
}

.product-card:hover .price {
  transform: scale(1.05);
}

.product-actions {
  display: flex !important;
  flex-direction: row !important;
  gap: 10px;
  padding: 16px 20px;
  border-top: 2px solid rgba(226, 232, 240, 0.6);
  background: linear-gradient(180deg, rgba(249, 250, 251, 0.95), rgba(241, 245, 249, 0.95));
  backdrop-filter: blur(10px);
  visibility: visible !important;
  opacity: 1 !important;
  position: relative !important;
  z-index: 10 !important;
  width: 100% !important;
  box-sizing: border-box !important;
  margin-top: auto !important;
  flex-shrink: 0 !important;
  border-radius: 0 0 20px 20px;
  transition: all 0.3s ease;
}

.product-card:hover .product-actions {
  background: linear-gradient(180deg, rgba(255, 255, 255, 1), rgba(240, 253, 244, 0.8));
  border-top-color: rgba(22, 163, 74, 0.2);
  box-shadow: 0 -4px 12px rgba(22, 163, 74, 0.05);
}

.action-btn {
  flex: 1;
  padding: 10px 8px;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  visibility: visible !important;
  opacity: 1 !important;
  white-space: nowrap;
  min-width: 60px;
  position: relative !important;
  z-index: 11 !important;
  box-shadow: 
    0 2px 4px rgba(0, 0, 0, 0.1),
    0 1px 2px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.action-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.action-btn:hover::before {
  opacity: 1;
}

.action-btn:hover {
  transform: translateY(-2px) scale(1.05);
  box-shadow: 
    0 8px 16px rgba(0, 0, 0, 0.15),
    0 4px 8px rgba(0, 0, 0, 0.1);
}

.action-btn:active {
  transform: translateY(0) scale(1);
}

.status-btn {
  background: linear-gradient(135deg, #f59e0b, #d97706) !important;
  color: white !important;
  display: flex !important;
  visibility: visible !important;
  opacity: 1 !important;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3) !important;
}

.status-btn:hover {
  background: linear-gradient(135deg, #d97706, #b45309) !important;
  box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4) !important;
}

.edit-btn {
  background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
  color: white !important;
  display: flex !important;
  visibility: visible !important;
  opacity: 1 !important;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3) !important;
}

.edit-btn:hover {
  background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4) !important;
}

.delete-btn {
  background: linear-gradient(135deg, #ef4444, #dc2626) !important;
  color: white !important;
  display: flex !important;
  visibility: visible !important;
  opacity: 1 !important;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
}

.delete-btn:hover {
  background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
  box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4) !important;
}

.view-btn {
  background: linear-gradient(135deg, #06b6d4, #0891b2) !important;
  color: white !important;
  display: flex !important;
  visibility: visible !important;
  opacity: 1 !important;
  box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3) !important;
}

.view-btn:hover {
  background: linear-gradient(135deg, #0891b2, #0e7490) !important;
  box-shadow: 0 6px 20px rgba(6, 182, 212, 0.4) !important;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.detail-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: flex-start;
  z-index: 9999;
  backdrop-filter: blur(4px);
  overflow-y: scroll;
  padding: 20px;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.detail-overlay::-webkit-scrollbar {
  width: 8px;
}

.detail-overlay::-webkit-scrollbar-track {
  background: transparent;
}

.detail-overlay::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
}

.detail-overlay::-webkit-scrollbar-thumb:hover {
  background: rgba(0, 0, 0, 0.5);
}

.detail-box {
  width: 90%;
  max-width: 900px;
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 24px;
  padding: 40px;
  position: relative;
  animation: zoomIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  margin: 40px auto 80px;
  box-shadow: 
    0 25px 50px rgba(0, 0, 0, 0.25),
    0 10px 20px rgba(0, 0, 0, 0.15),
    0 0 0 1px rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(226, 232, 240, 0.8);
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 20px;
  border: none;
  background: none;
  font-size: 22px;
  color: #777;
  cursor: pointer;
  z-index: 1;
}

.close-btn:hover {
  color: #000;
}

.detail-top {
  display: flex;
  gap: 25px;
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #eee;
}

.detail-img {
  width: 300px;
  height: 300px;
  object-fit: cover;
  border-radius: 10px;
}

.detail-info {
  flex: 1;
}

.detail-info h2 {
  margin-bottom: 10px;
}

.desc {
  color: #555;
  margin: 15px 0;
  line-height: 1.6;
}

.price-box {
  margin: 15px 0;
}

.detail-tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  border-bottom: 2px solid #eee;
}

.tab-btn {
  padding: 10px 20px;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 14px;
  color: #666;
  border-bottom: 2px solid transparent;
  transition: all 0.3s;
}

.tab-btn:hover {
  color: #007bff;
}

.tab-btn.active {
  color: #007bff;
  border-bottom-color: #007bff;
  font-weight: bold;
}

.tab-content {
  min-height: 300px;
}

.loading-text {
  text-align: center;
  padding: 40px;
  color: #999;
}

.empty-review {
  text-align: center;
  color: #666;
  padding: 40px;
}

.orders-list, .buyers-list, .reviews-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.order-item, .buyer-item, .review-item {
  background: #f9f9f9;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #eee;
}

.order-header, .buyer-header, .review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.order-status {
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
}

.buyer-order-code {
  font-size: 12px;
  color: #666;
}

.buyer-info p, .order-info p {
  margin: 5px 0;
  font-size: 14px;
}

.stars {
  color: #ffb400;
}

.review-comment {
  margin: 10px 0;
  color: #555;
}

.review-date {
  color: #999;
}

.stats-detail {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  padding: 15px;
  background: #f9f9f9;
  border-radius: 8px;
}

.stat-label {
  font-weight: bold;
  color: #333;
}

.stat-value {
  color: #007bff;
  font-weight: bold;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10000;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.modal-box {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  padding: 40px;
  border-radius: 20px;
  max-width: 450px;
  width: 90%;
  box-shadow: 
    0 20px 40px rgba(0, 0, 0, 0.2),
    0 10px 20px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(226, 232, 240, 0.8);
  animation: zoomIn 0.3s ease;
}

.modal-box h3 {
  margin-bottom: 15px;
}

.warning-text {
  color: #dc3545;
  margin: 10px 0;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
}

.btn-cancel, .btn-confirm {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
}

.btn-cancel {
  background: #6c757d;
  color: white;
}

.btn-confirm {
  background: #dc3545;
  color: white;
}

.btn-cancel:hover {
  background: #5a6268;
}

.btn-confirm:hover {
  background: #c82333;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 14px;
  margin: 30px 0;
}

.pagination button {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fff;
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.empty-state p {
  margin-bottom: 20px;
  font-size: 18px;
}

.skeleton-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 25px;
  padding: 20px;
}

.skeleton {
  background: white;
  border-radius: 12px;
  padding: 15px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.skeleton-img {
  width: 100%;
  height: 200px;
  background: linear-gradient(90deg, #eee 25%, #f5f5f5 50%, #eee 75%);
  background-size: 200% 100%;
  animation: shimmer 1.2s infinite;
  border-radius: 8px;
  margin-bottom: 10px;
}

.skeleton-line {
  height: 14px;
  margin: 8px 0;
  background: linear-gradient(90deg, #eee 25%, #f5f5f5 50%, #eee 75%);
  background-size: 200% 100%;
  animation: shimmer 1.2s infinite;
  border-radius: 8px;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

@keyframes zoomIn {
  from {
    transform: scale(0.9);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
/* Đảm bảo không có thanh cuộn dọc */
.shop-container,
.shop-container * {
  overflow-y: visible !important;
}

.shop-container .product-list,
.shop-container .header-section,
.shop-container .filter-bar {
  overflow: visible !important;
  overflow-y: visible !important;
}

/* Chỉ modal mới có scroll */
.detail-overlay {
  overflow-y: auto !important;
}

.detail-box {
  overflow-y: auto !important;
}
</style>
