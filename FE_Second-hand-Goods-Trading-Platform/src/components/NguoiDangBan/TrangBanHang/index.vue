<template>
  <div class="shop-container" @keyup.esc="closeDetail" tabindex="0">
    <h1 class="shop-title">🛍 SẢN PHẨM CỦA TÔI</h1>

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
          <option v-for="(cat, index) in categories" :key="index" :value="cat">
            {{ cat }}
          </option>
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
        <label>🔥 Giảm giá:</label>
        <select v-model="selectedDiscount">
          <option value="all">Tất cả</option>
          <option value="sale">Sản phẩm đang giảm giá</option>
        </select>
      </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <main class="product-list">
      <!-- Loading skeleton -->
      <div v-if="isLoading" class="skeleton-grid">
        <div v-for="n in 8" :key="n" class="product-card skeleton">
          <div class="skeleton-img"></div>
          <div class="skeleton-line w-70"></div>
          <div class="skeleton-line w-40"></div>
          <div class="skeleton-line w-50"></div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="!filteredProducts.length" class="empty-state">
        <p v-if="!getCurrentUser()">⚠️ Vui lòng đăng nhập để xem sản phẩm của bạn.</p>
        <div class="card text-center">
          <div class="card-body">
            <h5 class="card-title">😿 Bạn chưa có sản phẩm nào được đăng bán.</h5>
            <p class="card-text">Đăng sản phẩm đầu tiên để tiếp cận hàng nghìn người mua mỗi ngày.</p>
            <button class="btn btn-primary" @click="resetFilters">Xóa bộ lọc</button>
          </div>
        </div>
      </div>

      <!-- Products -->
      <template v-else>
        <div
          class="product-card"
          v-for="(product, index) in filteredProducts"
          :key="product.id || index"
          @click="showDetail(product)"
        >
          <div v-if="product.discount > 0" class="discount-badge">
            -{{ product.discount }}%
          </div>
          <img
            :src="product.image"
            :alt="product.name"
            class="product-img"
            loading="lazy"
            @error="onImgError($event)"
          />
          <h3 class="line-clamp-2">{{ product.name }}</h3>
          <p class="sold">Đã bán: {{ product.sold?.toLocaleString('vi-VN') || 0 }} sản phẩm</p>

          <div class="price-section">
            <p class="price">{{ formatPrice(finalPrice(product)) }}</p>
            <p v-if="product.discount > 0" class="old-price">
              {{ formatPrice(product.price) }}
            </p>
          </div>

          <div class="btn-container" @click.stop>
            <button class="buy-btn" @click="addToCart(product)">🛒 Thêm vào giỏ</button>
            <button class="buy-btn highlight" @click="buyNow(product)">💰 Mua ngay</button>
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

    <!-- Popup chi tiết sản phẩm -->
    <div v-if="selectedProduct" class="detail-overlay" @click.self="closeDetail">
      <div class="detail-box">
        <button class="close-btn" @click="closeDetail">✖</button>

        <div class="detail-top">
          <img :src="selectedProduct.image" class="detail-img" @error="onImgError($event)" />
          <div class="detail-info">
            <h2>{{ selectedProduct.name }}</h2>

            <div class="rating">
              <span v-for="n in 5" :key="n" class="star">{{ n <= (selectedProduct.rating || 0) ? "★" : "☆" }}</span>
              <span class="rating-text">
                {{ selectedProduct.rating || 0 }}/5 ({{ (selectedProduct.reviews?.length || 0) }} đánh giá)
              </span>
            </div>

            <div class="price-box">
              <p class="price">{{ formatPrice(finalPrice(selectedProduct)) }}</p>
              <p v-if="selectedProduct.discount > 0" class="old-price">
                {{ formatPrice(selectedProduct.price) }}
              </p>
            </div>

            <p class="desc">{{ selectedProduct.description }}</p>

            <div class="actions" @click.stop>
              <button class="buy-btn big" @click="addToCart(selectedProduct)">🛒 Thêm vào giỏ</button>
              <button class="buy-btn highlight big" @click="buyNow(selectedProduct)">💰 Mua ngay</button>
            </div>
          </div>
        </div>

        <div class="detail-bottom">
          <h3>📄 Mô Tả Chi Tiết</h3>
          <p>{{ selectedProduct.detail }}</p>

          <h3 style="margin-top: 20px">⭐ Đánh Giá Sản Phẩm</h3>
          <div v-if="(selectedProduct.reviews?.length || 0) === 0" class="empty-review">
            Chưa có đánh giá nào.
          </div>
          <div class="review" v-for="(r, i) in selectedProduct.reviews || []" :key="i">
            <div class="review-header">
              <strong>{{ r.user }}</strong>
              <span class="stars">
                <span v-for="n in 5" :key="n">{{ n <= r.stars ? "★" : "☆" }}</span>
              </span>
            </div>
            <p>{{ r.comment }}</p>
          </div>

          <!-- 🆕 SẢN PHẨM TƯƠNG TỰ -->
          <h3 style="margin-top: 25px">🛍 Sản Phẩm Tương Tự</h3>
          <div class="similar-list" v-if="similarProducts.length">
            <div
              class="similar-card"
              v-for="(item, idx) in similarProducts"
              :key="item.id || idx"
              @click="showDetail(item)"
            >
              <img :src="item.image" :alt="item.name" @error="onImgError($event)" />
              <p class="name line-clamp-2">{{ item.name }}</p>
              <p class="price">{{ formatPrice(finalPrice(item)) }}</p>
            </div>
          </div>
          <div v-else class="empty-review">Không có sản phẩm tương tự.</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

// Debounce nhỏ để tránh gọi API quá dày
let debounceTimer = null;
const debounce = (fn, delay = 400) => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(fn, delay);
};

export default {
  name: "OnlineShop",
  data() {
    return {
      // filters
      searchText: "",
      selectedCategory: "all",
      selectedPriceSort: "none",
      selectedDiscount: "all",

      // data
      products: [],
      isLoading: false,
      selectedProduct: null,
      similarProducts: [],

      // categories có thể lấy từ API riêng; tạm cứng để khớp UI
      categories: [
        "Đồ điện tử",
        "Gia dụng",
        "Phụ kiện",
        "Thời trang",
        "Thiết bị thông minh",
        "Khác",
      ],

      // pagination (theo chuẩn paginate() của Laravel)
      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 20,
      },

      // fallback image
      fallbackImg: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDYwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI2MDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0zMDAgMTYwQzM0NS4yMjkgMTYwIDM4MiAxMjMuMjI5IDM4MiA3OEMzODIgMzIuNzcwOSAzNDUuMjI5IDYgMzAwIDZDMjU0Ljc3MCA2IDIxOCAzMi43NzA5IDIxOCA3OEMyMTggMTIzLjIyOSAyNTQuNzcwIDE2MCAzMDAgMTYwWiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNMzAwIDI0MEMyNTAgMjQwIDIxMCAyNTYgMTg1IDI4MEg0MTVDMzkwIDI1NiAzNTAgMjQwIDMwMCAyNDBaIiBmaWxsPSIjOUNBM0FGIi8+Cjx0ZXh0IHg9IjMwMCIgeT0iMzIwIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9IiM5Q0EzQUYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPk5vIEltYWdlPC90ZXh0Pgo8L3N2Zz4=",
      API_BASE_URL: import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api/client",
    };
  },

  computed: {
    filteredProducts() {
      // Vì đã lọc/sort ở server theo params, ở client chỉ việc hiển thị.
      return this.products;
    },
  },

  mounted() {
    this.fetchProducts();
    // focus để bắt phím ESC đóng modal
    try { this.$el.focus?.(); } catch {}
  },

  watch: {
    // mỗi thay đổi filter → reset về trang 1 + gọi API (debounce với ô tìm kiếm)
    selectedCategory() { this.goFirstAndFetch(); },
    selectedDiscount() { this.goFirstAndFetch(); },
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
      const params = {
        page: this.pagination.current_page,
      };
      if (this.selectedCategory !== "all") params.category = this.selectedCategory;
      if (this.selectedDiscount === "sale") params.has_discount = 1;
      if (this.selectedPriceSort === "asc") params.sort = "price_asc";
      if (this.selectedPriceSort === "desc") params.sort = "price_desc";
      if (this.searchText?.trim()) params.q = this.searchText.trim();
      
      // Chỉ hiển thị sản phẩm của người dùng hiện tại
      const userInfo = this.getCurrentUser();
      if (userInfo?.id) {
        params.seller_id = userInfo.id;
      }
      
      return params;
    },

    getCurrentUser() {
      try {
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
      // Chỉ gọi API khi đã đăng nhập
      const userInfo = this.getCurrentUser();
      if (!userInfo?.id) {
        this.products = [];
        this.isLoading = false;
        return;
      }

      this.isLoading = true;
      try {
        const res = await axios.get(`${this.API_BASE_URL}/san-pham`, { params: this.paramsForAPI() });
        // Chuẩn paginate của Laravel: { data: { data: [], current_page, last_page, ... } }
        const payload = res?.data?.data;
        this.products = payload?.data || [];
        this.pagination.current_page = payload?.current_page || 1;
        this.pagination.last_page = payload?.last_page || 1;
        this.pagination.total = payload?.total || this.products.length;
        this.pagination.per_page = payload?.per_page || 20;
      } catch (e) {
        this.$toast?.error(e?.response?.data?.message || "Không tải được danh sách sản phẩm");
        this.products = [];
      } finally {
        this.isLoading = false;
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
      this.selectedDiscount = "all";
      this.selectedPriceSort = "none";
    },

    onImgError(e) {
      e.target.src = this.fallbackImg;
    },

    // Popup detail
    async showDetail(p) {
      // Nếu backend có API chi tiết/reviews riêng có thể gọi ở đây:
      // const res = await axios.get(`${this.API_BASE}/san-pham/${p.id}`);
      // const full = res?.data?.data || p;
      const full = { ...p };
      // Bảo vệ nếu API chưa có reviews:
      if (!Array.isArray(full.reviews)) full.reviews = [];
      this.selectedProduct = full;
      this.fetchSimilar(full);
    },

    closeDetail() {
      this.selectedProduct = null;
      this.similarProducts = [];
    },

    fetchSimilar(base) {
      if (!base) return (this.similarProducts = []);
      // lọc theo category + khác id/name
      const sameCat = this.products.filter(
        (x) =>
          x.category === base.category &&
          (base.id ? x.id !== base.id : x.name !== base.name)
      );
      // sắp xếp theo giá gần nhất
      const sortByClosePrice = [...sameCat].sort(
        (a, b) => Math.abs(this.finalPrice(a) - this.finalPrice(base)) - Math.abs(this.finalPrice(b) - this.finalPrice(base))
      );
      this.similarProducts = sortByClosePrice.slice(0, 6);
    },

    // Actions
    addToCart(p) {
      if (!p?.id) return;
      const CART_STORAGE_KEY = 'fe_marketplace_cart';
      const raw = localStorage.getItem(CART_STORAGE_KEY);
      const list = raw ? JSON.parse(raw) : [];
      const idx = Array.isArray(list) ? list.findIndex(item => item.id === p.id) : -1;
      
      if (idx > -1) {
        list[idx].quantity = Math.min(99, Number(list[idx].quantity || 0) + 1);
      } else {
        list.push({
          id: p.id,
          name: p.name,
          price: Number(this.finalPrice(p)),
          quantity: 1,
          image: p.image || this.fallbackImg,
          category: p.category || ''
        });
      }
      localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(list));
      // Dispatch event to update cart count in header
      window.dispatchEvent(new CustomEvent('cart-updated'));
      
      if (this.$toast) {
        this.$toast.success(`Đã thêm "${p.name}" vào giỏ hàng!`);
      } else {
        alert(`Đã thêm "${p.name}" vào giỏ hàng!`);
      }
    },
    buyNow(p) {
      if (!p?.id) return;
      this.$router.push({
        name: "checkout",
        query: {
          product_id: p.id,
        },
      });
    },

    // Helpers
    finalPrice(p) {
      const price = Number(p?.price || 0);
      const discount = Number(p?.discount || 0);
      return Math.round(price * (1 - discount / 100));
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
  min-height: 100vh;
  padding-bottom: 40px;
  outline: none; /* để có thể focus bắt phím ESC */
}

.shop-title {
  text-align: center;
  color: #ff5722;
  padding: 20px;
  font-size: 32px;
  font-weight: 700;
  letter-spacing: 1px;
}

.filter-bar {
  display: flex;
  justify-content: center;
  gap: 20px;
  flex-wrap: wrap;
  margin-bottom: 25px;
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

.product-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 25px;
  padding: 20px 40px;
}

.product-card {
  position: relative;
  background: #fff;
  border-radius: 12px;
  text-align: center;
  padding: 15px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  cursor: pointer;
}

.product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
}

.discount-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background: #e53935;
  color: white;
  font-weight: bold;
  padding: 5px 8px;
  border-radius: 6px;
  font-size: 13px;
}

.product-img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-radius: 10px;
  margin-bottom: 10px;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.price-section {
  display: flex;
  justify-content: center;
  align-items: baseline;
  gap: 8px;
}

.price {
  color: #e53935;
  font-weight: bold;
  font-size: 16px;
}

.old-price {
  color: #999;
  font-size: 13px;
  text-decoration: line-through;
}

.sold {
  font-size: 14px;
  color: #555;
}

.btn-container {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  margin-top: 10px;
}

.buy-btn {
  flex: 1;
  background-color: #ff5722;
  color: #fff;
  border: none;
  padding: 8px 10px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  transition: all 0.25s;
}

.buy-btn:hover {
  background-color: #e64a19;
  transform: scale(1.05);
}

.highlight {
  background: #ff9800;
}

.big {
  padding: 12px 18px;
  font-size: 15px;
}

/* Popup */
.detail-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
  backdrop-filter: blur(2px);
}

.detail-box {
  width: 880px;
  background: #fff;
  border-radius: 14px;
  padding: 30px;
  position: relative;
  animation: zoomIn 0.3s ease;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
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
}
.close-btn:hover { color: #000; }

.detail-top {
  display: flex;
  gap: 25px;
}

.detail-img {
  width: 340px;
  border-radius: 10px;
  object-fit: cover;
}

.detail-info { flex: 1; }

.rating {
  color: #ffb400;
  margin: 8px 0;
}

.rating-text {
  font-size: 13px;
  color: #666;
  margin-left: 8px;
}

.price-box {
  display: flex;
  gap: 10px;
  align-items: center;
}

.desc {
  font-size: 14px;
  color: #555;
  margin: 15px 0;
}

.actions { display: flex; gap: 10px; }

.detail-bottom { margin-top: 25px; }

.review { border-top: 1px solid #eee; padding: 10px 0; }

.review-header { display: flex; justify-content: space-between; }

.stars { color: #ffb400; }

/* Sản phẩm tương tự */
.similar-list {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
  margin-top: 10px;
}

.similar-card {
  width: 150px;
  background: #fffdf8;
  border: 1px solid #eee;
  border-radius: 10px;
  padding: 10px;
  text-align: center;
  cursor: pointer;
  transition: 0.25s;
}
.similar-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
}
.similar-card img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: 8px;
}
.similar-card .name {
  font-size: 14px;
  color: #333;
  margin: 6px 0 2px;
  font-weight: 500;
}
.similar-card .price {
  color: #e53935;
  font-weight: bold;
  font-size: 14px;
}

@keyframes zoomIn {
  from { transform: scale(0.9); opacity: 0; }
  to   { transform: scale(1);   opacity: 1; }
}

/* Skeleton */
.skeleton-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px; padding: 20px 40px; }
.skeleton { overflow: hidden; }
.skeleton-img, .skeleton-line {
  background: linear-gradient(90deg, #eee 25%, #f5f5f5 50%, #eee 75%);
  background-size: 200% 100%;
  animation: shimmer 1.2s infinite;
  border-radius: 10px;
}
.skeleton-img { height: 180px; margin-bottom: 10px; }
.skeleton-line { height: 14px; margin: 8px 0; border-radius: 8px; }
.w-70 { width: 70%; margin: 0 auto; }
.w-50 { width: 50%; margin: 0 auto; }
.w-40 { width: 40%; margin: 0 auto; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

.empty-state, .empty-review {
  text-align: center;
  color: #666;
  margin: 20px 0;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 14px;
  margin: 10px 0 30px;
}
.pagination button {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fff;
  cursor: pointer;
}
.pagination button:disabled { opacity: .5; cursor: not-allowed; }
</style>
