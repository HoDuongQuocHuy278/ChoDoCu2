<template>
  <div class="product-page container py-4">
    <section class="d-flex flex-column flex-lg-row gap-3 gap-lg-4 mb-4">
      <aside class="filter-card card shadow-filter">
        <div class="card-header filter-header">
          <h5 class="m-0 fw-bold filter-title">
            <i class="bx bx-filter-alt me-2"></i>Bộ lọc
          </h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label filter-label">
              <i class="bx bx-search me-2"></i>Tìm kiếm
            </label>
            <div class="input-group search-wrapper">
              <span class="input-group-text search-icon-wrapper">
                <i class="bx bx-search search-icon"></i>
              </span>
              <input v-model="filters.keyword" type="search" class="form-control search-input" placeholder="Từ khóa, thương hiệu..." @keyup.enter="fetchProducts">
              <button class="btn btn-search" type="button" @click="fetchProducts">
                <i class="bx bx-search-alt-2"></i>
              </button>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label filter-label">
              <i class="bx bx-category me-2"></i>Danh mục
            </label>
            <select v-model="filters.category" class="form-select filter-select" @change="fetchProducts">
              <option value="">Tất cả</option>
              <option v-for="cat in categories" :key="cat.slug || cat.id" :value="cat.slug || cat.id">
                {{ cat.name || cat.ten_danh_muc }}
              </option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label filter-label">
              <i class="bx bx-money me-2"></i>Mức giá
            </label>
            <select v-model="filters.price" class="form-select filter-select" @change="fetchProducts">
              <option value="all">Tất cả</option>
              <option value="0-500">Dưới 500.000đ</option>
              <option value="500-1000">500.000đ - 1.000.000đ</option>
              <option value="1000-3000">1.000.000đ - 3.000.000đ</option>
              <option value="3000-99999">Trên 3.000.000đ</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label filter-label">
              <i class="bx bx-check-circle me-2"></i>Tình trạng
            </label>
            <select v-model="filters.condition" class="form-select filter-select" @change="fetchProducts">
              <option value="">Tất cả</option>
              <option value="new">Mới 100%</option>
              <option value="likenew">Như mới (99%)</option>
              <option value="good">Tốt</option>
              <option value="fair">Khá</option>
            </select>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-reset w-50" type="button" @click="resetFilters">
              <i class="bx bx-refresh me-1"></i>Đặt lại
            </button>
            <button class="btn btn-apply w-50" type="button" @click="fetchProducts">
              <i class="bx bx-check me-1"></i>Áp dụng
            </button>
          </div>
        </div>
      </aside>

      <section class="flex-grow-1">
        <header class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 mb-4">
          <div class="header-content">
            <h2 class="fw-bold m-0 page-title">Danh sách sản phẩm</h2>
            <p class="page-subtitle mb-0">Khám phá những món đồ cũ chất lượng được cập nhật mỗi ngày.</p>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <label class="sort-label fw-semibold small m-0">
              <i class="bx bx-sort me-1"></i>Sắp xếp:
            </label>
            <select v-model="filters.sort" class="form-select form-select-sm sort-select" style="width: 200px;" @change="fetchProducts">
              <option value="newest">Mới nhất</option>
              <option value="price_asc">Giá tăng dần</option>
              <option value="price_desc">Giá giảm dần</option>
              <option value="popular">Bán chạy</option>
            </select>
          </div>
        </header>

        <div v-if="isLoading" class="row g-3">
          <div v-for="n in 8" :key="n" class="col-6 col-md-4 col-lg-3">
            <div class="skeleton-card">
              <div class="sk-img"></div>
              <div class="sk-line w-75"></div>
              <div class="sk-line w-50"></div>
            </div>
          </div>
        </div>

        <div v-else-if="errorMessage" class="alert alert-error">
          <i class="bx bx-error-circle me-2"></i>{{ errorMessage }}
          <button type="button" class="btn btn-sm btn-error-retry ms-2" @click="fetchProducts">
            <i class="bx bx-refresh me-1"></i>Thử lại
          </button>
        </div>

        <template v-else>
          <div v-if="products.length === 0" class="empty-state text-center p-5 border rounded-4">
            <div class="empty-icon">
              <i class="bx bx-search-alt-2"></i>
            </div>
            <h5 class="fw-bold empty-title">Không tìm thấy sản phẩm phù hợp</h5>
            <p class="empty-text">Thử thay đổi bộ lọc hoặc tìm kiếm từ khóa khác.</p>
            <button class="btn btn-empty-reset" type="button" @click="resetFilters">
              <i class="bx bx-refresh me-1"></i>Xóa bộ lọc
            </button>
          </div>

          <div v-else class="row g-3 g-lg-4 product-grid">
            <article v-for="(product, index) in products" :key="product.id" class="col-12 col-sm-6 col-lg-4 col-xl-3 product-item" :style="{ animationDelay: `${index * 0.05}s` }">
              <div class="product-card card h-100 shadow-product">
                <div class="ratio ratio-4x3 product-image-wrapper">
                  <img :src="getProductImage(product) || fallbackImg" class="card-img-top product-image" :alt="product.name" @error="replaceImage($event)">
                  <div class="image-overlay"></div>
                  <span v-if="product.discount" class="badge badge-discount">
                    <i class="bx bx-tag me-1"></i>-{{ product.discount }}%
                  </span>
                </div>
                <div class="card-body d-flex flex-column">
                  <h6 class="card-title product-name text-truncate" :title="product.name">{{ product.name }}</h6>
                  <p class="card-text small mb-2 line-clamp-2">{{ product.description }}</p>
                  <div class="d-flex align-items-baseline gap-2 mb-3">
                    <span class="price price-current" :class="index % 2 === 0 ? 'price-red' : 'price-orange'">{{ formatCurrency(finalPrice(product)) }}</span>
                    <span v-if="product.discount" class="price-old text-decoration-line-through small">{{ formatCurrency(product.price) }}</span>
                  </div>
                  <div class="mt-auto d-flex flex-wrap gap-2 product-actions">
                    <router-link :to="`/san-pham/${product.id}`" class="btn btn-detail btn-sm flex-grow-1">
                      <i class="bx bx-show me-1"></i>Chi tiết
                    </router-link>
                    <button class="btn btn-cart btn-sm flex-grow-1" type="button" @click="addToCart(product)">
                      <i class="bx bx-cart-add me-1"></i>Giỏ hàng
                    </button>
                    <button class="btn btn-buy btn-sm flex-grow-1" type="button" @click="buyNow(product)">
                      <i class="bx bx-purchase-tag me-1"></i>Mua ngay
                    </button>
                  </div>
                </div>
              </div>
            </article>
          </div>

          <nav v-if="pagination.last_page > 1" class="pagination-wrap d-flex justify-content-center align-items-center gap-3 mt-5">
            <button class="btn btn-pagination btn-prev" type="button" :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">
              <i class="bx bx-chevron-left me-1"></i>Trước
            </button>
            <span class="pagination-info">
              <span class="pagination-current">{{ pagination.current_page }}</span>
              <span class="pagination-separator">/</span>
              <span class="pagination-total">{{ pagination.last_page }}</span>
            </span>
            <button class="btn btn-pagination btn-next" type="button" :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)">
              Sau<i class="bx bx-chevron-right ms-1"></i>
            </button>
          </nav>
        </template>
      </section>
    </section>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/client'
const PRODUCTS_API = `${API_BASE_URL}/san-pham`
const CATEGORY_API = `${API_BASE_URL}/danh-muc`
const CART_STORAGE_KEY = 'fe_marketplace_cart'

const products = ref([])
const categories = ref([])
const isLoading = ref(false)
const errorMessage = ref('')
// Tạo data URI SVG cho placeholder image (hoạt động offline)
function getDefaultImage() {
  return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yMDAgMTIwQzIxNS40NjQgMTIwIDIyOCAxMDcuNDY0IDIyOCA5MkMyMjggNzYuNTM2IDIxNS40NjQgNjQgMjAwIDY0QzE4NC41MzYgNjQgMTcyIDc2LjUzNiAxNzIgOTJDMTcyIDEwNy40NjQgMTg0LjUzNiAxMjAgMjAwIDEyMFoiIGZpbGw9IiM5Q0EzQUYiLz4KPHBhdGggZD0iTTIwMCAxODBDMTcwIDE4MCAxNDQgMTk2IDEzMCAyMjBIMjcwQzI1NiAxOTYgMjMwIDE4MCAyMDAgMTgwWiIgZmlsbD0iIzlDQTNBRiIvPgo8dGV4dCB4PSIyMDAiIHk9IjI1MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjE0IiBmaWxsPSIjOUNBM0FGIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5ObyBJbWFnZTwvdGV4dD4KPC9zdmc+'
}

const fallbackImg = getDefaultImage()
const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 12
})

const filters = reactive({
  keyword: '',
  category: '',
  price: 'all',
  condition: '',
  sort: 'newest'
})

// Đọc query parameters từ URL khi component mount hoặc route thay đổi
function loadQueryParams() {
  const query = route.query
  if (query.category) {
    filters.category = query.category
  }
  if (query.q) {
    filters.keyword = query.q
  }
  if (query.sort) {
    filters.sort = query.sort
  }
  if (query.page) {
    pagination.current_page = parseInt(query.page) || 1
  }
}

onMounted(() => {
  loadQueryParams()
  fetchInitialData()
})

// Watch route changes để cập nhật filters khi URL thay đổi
watch(() => route.query, () => {
  loadQueryParams()
  fetchProducts()
}, { deep: true })

async function fetchInitialData() {
  await Promise.allSettled([fetchCategories(), fetchProducts()])
}

async function fetchCategories() {
  try {
    const { data } = await axios.get(CATEGORY_API)
    const rawCategories = data?.data || data || []
    // Map field names từ backend (ten_danh_muc -> name)
    categories.value = Array.isArray(rawCategories) 
      ? rawCategories.map(cat => ({
          id: cat.id,
          name: cat.ten_danh_muc || cat.name,
          slug: cat.slug,
          mo_ta: cat.mo_ta,
          hinh_anh: cat.hinh_anh,
        }))
      : []
  } catch (err) {
    console.warn('Không thể tải danh mục', err)
    categories.value = []
  }
}

function buildParams() {
  const params = {
    page: pagination.current_page,
    sort: filters.sort
  }
  if (filters.keyword) params.q = filters.keyword
  if (filters.category) params.category = filters.category
  if (filters.condition) params.condition = filters.condition
  if (filters.price && filters.price !== 'all') {
    const [min, max] = filters.price.split('-').map(Number)
    params.price_min = min * 1000
    if (max && max < 99999) {
      params.price_max = max * 1000
    }
  }
  return params
}

async function fetchProducts() {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const { data } = await axios.get(PRODUCTS_API, { params: buildParams() })
    const payload = data?.data || data
    if (payload?.data) {
      products.value = payload.data
      pagination.current_page = Number(payload.current_page || 1)
      pagination.last_page = Number(payload.last_page || 1)
      pagination.total = Number(payload.total || payload.data.length)
      pagination.per_page = Number(payload.per_page || pagination.per_page)
    } else if (Array.isArray(payload)) {
      products.value = payload
      pagination.current_page = 1
      pagination.last_page = 1
      pagination.total = payload.length
    } else {
      products.value = []
    }
  } catch (err) {
    console.error(err)
    errorMessage.value = err?.response?.data?.message || 'Không thể tải danh sách sản phẩm.'
  } finally {
    isLoading.value = false
  }
}

function resetFilters() {
  filters.keyword = ''
  filters.category = ''
  filters.price = 'all'
  filters.condition = ''
  filters.sort = 'newest'
  pagination.current_page = 1
  fetchProducts()
}

function changePage(next) {
  if (next < 1 || next > pagination.last_page) return
  pagination.current_page = next
  fetchProducts()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function finalPrice(product) {
  const base = Number(product?.price || 0)
  const discount = Number(product?.discount || 0)
  return base * (1 - discount / 100)
}

function formatCurrency(value) {
  return (Number(value) || 0).toLocaleString('vi-VN') + ' ₫'
}

function getProductImage(product) {
  // Ưu tiên: image field từ API
  if (product?.image) return product.image
  
  // Thứ 2: images array từ API
  if (Array.isArray(product?.images) && product.images.length > 0) {
    return product.images[0]
  }
  
  // Thứ 3: parse từ hinh_anh (JSON array string)
  if (product?.hinh_anh) {
    try {
      const parsed = typeof product.hinh_anh === 'string' && product.hinh_anh.trim().startsWith('[')
        ? JSON.parse(product.hinh_anh)
        : product.hinh_anh
      if (Array.isArray(parsed) && parsed.length > 0) {
        return parsed[0]
      } else if (typeof parsed === 'string' && parsed) {
        return parsed
      }
    } catch (e) {
      if (typeof product.hinh_anh === 'string' && product.hinh_anh) {
        return product.hinh_anh
      }
    }
  }
  
  return null
}

function replaceImage(event) {
  // Tránh vòng lặp vô hạn - chỉ set một lần
  if (event.target.dataset.fallbackSet === 'true') {
    event.target.style.display = 'none' // Ẩn image nếu fallback cũng lỗi
    return
  }
  event.target.dataset.fallbackSet = 'true'
  event.target.src = fallbackImg
  event.target.onerror = null // Ngăn không cho trigger error handler lần nữa
}

function addToCart(product) {
  const raw = localStorage.getItem(CART_STORAGE_KEY)
  const list = raw ? JSON.parse(raw) : []
  const idx = Array.isArray(list) ? list.findIndex(item => item.id === product.id) : -1
  if (idx > -1) {
    list[idx].quantity = Math.min(99, Number(list[idx].quantity || 0) + 1)
  } else {
    list.push({
      id: product.id,
      name: product.name,
      price: Number(finalPrice(product)),
      quantity: 1,
      image: getProductImage(product) || fallbackImg,
      category: product.category || ''
    })
  }
  localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(list))
  // Dispatch event to update cart count in header
  window.dispatchEvent(new CustomEvent('cart-updated'))
  // Nếu có plugin toast có thể gọi, còn không dùng alert
  if (typeof window !== 'undefined' && window?.$toast) {
    window.$toast.success('Đã thêm vào giỏ hàng!')
  } else {
    alert('Đã thêm vào giỏ hàng!')
  }
}

function buyNow(product) {
  if (!product?.id) return
  router.push({
    name: 'checkout',
    query: {
      product_id: product.id,
    },
  })
}
</script>

<style scoped>
/* ===== Color Variables ===== */
:root {
  --primary-color: #6366f1;
  --primary-dark: #4f46e5;
  --primary-light: #818cf8;
  --success-color: #10b981;
  --success-dark: #059669;
  --danger-color: #ef4444;
  --danger-dark: #dc2626;
  --warning-color: #f59e0b;
  --warning-dark: #d97706;
  --info-color: #3b82f6;
  --info-dark: #2563eb;
  --text-primary: #1e293b;
  --text-secondary: #475569;
  --text-muted: #64748b;
  --border-color: #e2e8f0;
  --bg-light: #f8fafc;
  --primary-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
  --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --card-shadow-hover: 0 20px 25px -5px rgba(99, 102, 241, 0.15), 0 10px 10px -5px rgba(99, 102, 241, 0.1);
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.product-page {
  min-height: 70vh;
  background: linear-gradient(to bottom, #f8fafc 0%, #ffffff 100%);
  padding-top: 2rem;
  padding-bottom: 3rem;
}

/* ===== Filter Card ===== */
.filter-card {
  width: 100%;
  max-width: 280px;
  border: none;
  border-radius: 20px;
  overflow: hidden;
  background: #ffffff;
  animation: slideInLeft 0.5s ease-out;
}

.shadow-filter {
  box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.12), 0 4px 6px -2px rgba(99, 102, 241, 0.08);
}

.filter-header {
  background: var(--primary-gradient);
  border: none;
  padding: 1.25rem 1.5rem;
  border-radius: 0;
}

.filter-title {
  color: #ffffff;
  font-size: 1.1rem;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  letter-spacing: 0.3px;
}

.filter-title i {
  animation: rotate 3s linear infinite;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

/* ===== Filter Labels ===== */
.filter-label {
  color: var(--text-primary);
  font-weight: 600;
  font-size: 0.95rem;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  letter-spacing: 0.2px;
}

.filter-label i {
  color: var(--primary-color);
  font-size: 1.1rem;
}

/* ===== Search Input ===== */
.search-wrapper {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  border: 2px solid #e5e7eb;
  transition: all 0.3s ease;
  background: #ffffff;
}

.search-wrapper:focus-within {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1), 0 4px 12px rgba(99, 102, 241, 0.15);
  transform: translateY(-1px);
}

.search-icon-wrapper {
  background: var(--primary-gradient);
  border: none;
  color: #ffffff;
  padding: 0.75rem 1rem;
  box-shadow: 0 2px 4px rgba(99, 102, 241, 0.2);
}

.search-icon {
  font-size: 1.2rem;
  animation: searchPulse 2s ease-in-out infinite;
}

.search-input {
  border: none;
  padding: 0.75rem 1rem;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  color: var(--text-primary);
  background: #ffffff;
}

.search-input::placeholder {
  color: var(--text-muted);
  opacity: 0.7;
}

.search-input:focus {
  outline: none;
  box-shadow: none;
  color: var(--text-primary);
}

.btn-search {
  background: var(--primary-gradient) !important;
  background-color: #6366f1 !important;
  border: none !important;
  color: #ffffff !important;
  padding: 0.75rem 1.25rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.btn-search:hover {
  transform: scale(1.05) translateY(-1px);
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
  color: #ffffff !important;
  background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%) !important;
}

.btn-search:active {
  transform: scale(0.98);
  box-shadow: 0 2px 4px rgba(99, 102, 241, 0.3);
  color: #ffffff !important;
}

/* ===== Filter Selects ===== */
.filter-select {
  border: 2px solid var(--border-color);
  border-radius: 12px;
  padding: 0.7rem 1rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 0.95rem;
  color: var(--text-primary);
  background: #ffffff;
  font-weight: 500;
  box-shadow: var(--shadow-sm);
}

.filter-select:hover {
  border-color: var(--primary-light);
  box-shadow: var(--shadow-md);
}

.filter-select:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1), var(--shadow-md);
  outline: none;
  color: var(--text-primary);
}

.filter-select option {
  color: var(--text-primary);
  padding: 0.5rem;
  background: #ffffff;
}

/* ===== Filter Buttons ===== */
.btn-reset {
  background: linear-gradient(135deg, #64748b 0%, #475569 100%) !important;
  background-color: #64748b !important;
  border: none !important;
  color: #ffffff !important;
  font-weight: 600;
  border-radius: 12px;
  padding: 0.7rem 1rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 8px rgba(100, 116, 139, 0.25);
  letter-spacing: 0.3px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.btn-reset:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(100, 116, 139, 0.35);
  color: #ffffff !important;
  background: linear-gradient(135deg, #475569 0%, #334155 100%) !important;
}

.btn-reset:active {
  transform: translateY(0);
  color: #ffffff !important;
}

.btn-apply {
  background: var(--success-gradient) !important;
  background-color: #10b981 !important;
  border: none !important;
  color: #ffffff !important;
  font-weight: 600;
  border-radius: 12px;
  padding: 0.7rem 1rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
  letter-spacing: 0.3px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.btn-apply:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
  color: #ffffff !important;
  background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
}

.btn-apply:active {
  transform: translateY(0);
  color: #ffffff !important;
}

/* ===== Page Header ===== */
.page-title {
  background: var(--primary-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 2rem;
  margin-bottom: 0.5rem;
  animation: fadeInDown 0.6s ease-out;
  font-weight: 800;
  letter-spacing: -0.5px;
}

.page-subtitle {
  font-size: 0.95rem;
  color: #475569 !important;
  animation: fadeInDown 0.6s ease-out 0.1s both;
  font-weight: 400;
}

.sort-label {
  color: var(--text-primary);
  font-weight: 600;
  display: flex;
  align-items: center;
}

.sort-label i {
  color: var(--primary-color);
  font-size: 1rem;
}

.sort-select {
  border: 2px solid var(--border-color);
  border-radius: 12px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-weight: 500;
  color: var(--text-primary);
  background: #ffffff;
  box-shadow: var(--shadow-sm);
}

.sort-select:hover {
  border-color: var(--primary-light);
  box-shadow: var(--shadow-md);
}

.sort-select:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1), var(--shadow-md);
  outline: none;
  color: var(--text-primary);
}

.sort-select option {
  color: var(--text-primary);
  background: #ffffff;
}

/* ===== Product Cards ===== */
.product-grid {
  animation: fadeIn 0.5s ease-out;
}

.product-item {
  animation: fadeInUp 0.6s ease-out both;
}

.product-card {
  border-radius: 20px;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  border: none;
  background: #ffffff;
  position: relative;
}

.shadow-product {
  box-shadow: var(--card-shadow);
}

.product-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: var(--card-shadow-hover);
}

.product-image-wrapper {
  position: relative;
  overflow: hidden;
  background: #f3f4f6;
}

.product-image {
  object-fit: cover;
  border-top-left-radius: 20px;
  border-top-right-radius: 20px;
  transition: all 0.5s ease;
  width: 100%;
  height: 100%;
}

.product-card:hover .product-image {
  transform: scale(1.1);
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.1) 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.product-card:hover .image-overlay {
  opacity: 1;
}

.badge-discount {
  position: absolute;
  top: 12px;
  left: 12px;
  background: var(--danger-gradient);
  color: #ffffff;
  padding: 8px 14px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4), 0 2px 4px rgba(239, 68, 68, 0.2);
  z-index: 10;
  animation: pulseBadge 2s ease-in-out infinite;
  display: flex;
  align-items: center;
  gap: 4px;
  letter-spacing: 0.3px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.product-name {
  font-weight: 700;
  font-size: 1rem;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  line-height: 1.4;
  letter-spacing: -0.2px;
}

.product-card:hover .product-name {
  color: var(--primary-color);
}

.card-text {
  color: #64748b !important;
  line-height: 1.5;
  font-weight: 400;
}

.price-current {
  font-weight: 800;
  font-size: 1.3rem;
  letter-spacing: -0.5px;
  display: inline-block;
}

.price-red {
  color: #dc2626 !important;
  text-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
}

.price-orange {
  color: #ea580c !important;
  text-shadow: 0 2px 4px rgba(234, 88, 12, 0.2);
}

.price-old {
  font-size: 0.9rem;
  color: #94a3b8 !important;
  font-weight: 500;
}

/* ===== Product Action Buttons ===== */
.product-actions {
  margin-top: auto;
  padding-top: 0.5rem;
}

.btn-detail {
  background: var(--info-gradient) !important;
  background-color: #3b82f6 !important;
  border: none !important;
  color: #ffffff !important;
  font-weight: 600;
  border-radius: 10px;
  padding: 0.55rem 0.75rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 0.85rem;
  box-shadow: 0 2px 6px rgba(59, 130, 246, 0.25);
  letter-spacing: 0.2px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
}

.btn-detail:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(59, 130, 246, 0.4);
  color: #ffffff !important;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
}

.btn-detail:active {
  transform: translateY(0);
  color: #ffffff !important;
}

.btn-cart {
  background: var(--success-gradient) !important;
  background-color: #10b981 !important;
  border: none !important;
  color: #ffffff !important;
  font-weight: 600;
  border-radius: 10px;
  padding: 0.55rem 0.75rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 0.85rem;
  box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
  letter-spacing: 0.2px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
}

.btn-cart:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(16, 185, 129, 0.4);
  color: #ffffff !important;
  background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
}

.btn-cart:active {
  transform: translateY(0);
  color: #ffffff !important;
}

.btn-buy {
  background: var(--warning-gradient) !important;
  background-color: #f59e0b !important;
  border: none !important;
  color: #ffffff !important;
  font-weight: 600;
  border-radius: 10px;
  padding: 0.55rem 0.75rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 0.85rem;
  box-shadow: 0 2px 6px rgba(245, 158, 11, 0.25);
  letter-spacing: 0.2px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
}

.btn-buy:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(245, 158, 11, 0.4);
  color: #ffffff !important;
  background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
}

.btn-buy:active {
  transform: translateY(0);
  color: #ffffff !important;
}

/* ===== Pagination ===== */
.pagination-wrap {
  padding: 1.5rem 0;
}

.btn-pagination {
  background: var(--primary-gradient) !important;
  background-color: #6366f1 !important;
  border: none !important;
  color: #ffffff !important;
  font-weight: 600;
  border-radius: 12px;
  padding: 0.7rem 1.5rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 8px rgba(99, 102, 241, 0.25);
  letter-spacing: 0.3px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
}

.btn-pagination:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(99, 102, 241, 0.35);
  color: #ffffff !important;
  background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%) !important;
}

.btn-pagination:active:not(:disabled) {
  transform: translateY(0);
  color: #ffffff !important;
}

.btn-pagination:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%) !important;
  background-color: #e2e8f0 !important;
  color: #64748b !important;
  box-shadow: none;
  text-shadow: none;
}

.pagination-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  padding: 0.6rem 1.2rem;
  background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
}

.pagination-current {
  color: var(--primary-color);
  font-size: 1.15rem;
  font-weight: 700;
}

.pagination-separator {
  color: var(--text-muted);
  font-weight: 500;
}

.pagination-total {
  color: var(--text-secondary);
  font-weight: 600;
}

/* ===== Empty State ===== */
.empty-state {
  background: linear-gradient(135deg, #f8fafc 0%, #e5e7eb 100%);
  border: 2px dashed #d1d5db;
  border-radius: 20px;
  padding: 3rem 2rem;
  animation: fadeIn 0.6s ease-out;
}

.empty-icon {
  font-size: 4rem;
  color: #667eea;
  margin-bottom: 1rem;
  animation: float 3s ease-in-out infinite;
}

.empty-title {
  color: var(--text-primary);
  margin-bottom: 0.75rem;
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.3px;
}

.empty-text {
  font-size: 1rem;
  margin-bottom: 1.5rem;
  color: #64748b !important;
  font-weight: 400;
}

.btn-empty-reset {
  background: var(--primary-gradient) !important;
  background-color: #6366f1 !important;
  border: none !important;
  color: #ffffff !important;
  font-weight: 600;
  border-radius: 12px;
  padding: 0.75rem 2rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
}

.btn-empty-reset:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
  color: #ffffff !important;
  background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%) !important;
}

/* ===== Error State ===== */
.alert-error {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  border: 2px solid var(--danger-color);
  border-radius: 12px;
  color: #991b1b;
  padding: 1rem 1.5rem;
  display: flex;
  align-items: center;
  animation: shake 0.5s ease-out;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
}

.alert-error i {
  font-size: 1.25rem;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
}

.btn-error-retry {
  background: var(--danger-gradient) !important;
  background-color: #ef4444 !important;
  border: none !important;
  color: #ffffff !important;
  font-weight: 600;
  border-radius: 10px;
  padding: 0.55rem 1.2rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
  letter-spacing: 0.2px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
}

.btn-error-retry:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(239, 68, 68, 0.4);
  color: #ffffff !important;
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
}

.btn-error-retry:active {
  transform: translateY(0);
  color: #ffffff !important;
}

/* ===== Skeleton Loading ===== */
.skeleton-card {
  border-radius: 20px;
  background: linear-gradient(135deg, #f8fafc 0%, #e5e7eb 100%);
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-height: 280px;
  animation: shimmer 1.5s infinite;
  border: 1px solid #e5e7eb;
}

.sk-img {
  flex: 1;
  border-radius: 12px;
  background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.sk-line {
  height: 12px;
  border-radius: 999px;
  background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.w-75 {
  width: 75%;
}

.w-50 {
  width: 50%;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-clamp: 2;
}

/* ===== Animations ===== */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes rotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@keyframes searchPulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

@keyframes pulseBadge {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

@keyframes shake {
  0%, 100% {
    transform: translateX(0);
  }
  25% {
    transform: translateX(-10px);
  }
  75% {
    transform: translateX(10px);
  }
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

/* ===== Responsive ===== */
@media (max-width: 992px) {
  .filter-card {
    max-width: none;
    margin-bottom: 1.5rem;
  }

  .page-title {
    font-size: 1.75rem;
  }

  .product-card:hover {
    transform: translateY(-4px) scale(1.01);
  }
}

@media (max-width: 576px) {
  .product-actions {
    flex-direction: column;
  }

  .btn-detail,
  .btn-cart,
  .btn-buy {
    width: 100%;
  }
}
</style>

