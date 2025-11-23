<template>
  <div class="product-detail container py-4" v-if="!isLoading">
    <div v-if="errorMessage" class="alert alert-danger">
      {{ errorMessage }}
      <button type="button" class="btn btn-sm btn-outline-light ms-2" @click="fetchProduct">Thử lại</button>
    </div>

    <div v-else-if="product" class="row g-4">
      <div class="col-lg-6">
        <div class="gallery card shadow-sm">
          <div class="ratio ratio-4x3">
            <img :src="activeImage" class="main-img" :alt="product.name" @error="onImgError($event)">
          </div>
          <div class="thumb-list mt-3">
            <button
              v-for="(img, idx) in images"
              :key="idx"
              type="button"
              class="thumb-btn"
              :class="{ active: img === activeImage }"
              @click="activeImage = img"
            >
              <img :src="img" :alt="product.name" @error="onImgError($event)">
            </button>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="info card shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                <span class="badge bg-success-subtle text-success mb-2" v-if="product.conditionLabel">
                  {{ product.conditionLabel }}
                </span>
                <h2 class="fw-bold">{{ product.name }}</h2>
                <p class="text-muted mb-2">{{ product.category?.name || product.category || 'Danh mục khác' }}</p>
              </div>
              <button class="btn btn-outline-danger btn-sm" type="button" @click="toggleFavourite">
                <i :class="liked ? 'bx bxs-heart' : 'bx bx-heart'"></i>
              </button>
            </div>

            <div class="price-box my-3">
              <span class="price">{{ formatCurrency(finalPrice(product)) }}</span>
              <span v-if="product.discount" class="text-muted text-decoration-line-through ms-2">
                {{ formatCurrency(product.price) }}
              </span>
              <span v-if="product.discount" class="badge bg-danger-subtle text-danger ms-2">
                -{{ product.discount }}%
              </span>
            </div>

            <ul class="meta list-unstyled small">
              <li><strong>Mã sản phẩm:</strong> #{{ product.slug || product.id }}</li>
              <li><strong>Tình trạng:</strong> {{ product.conditionLabel || 'Đang cập nhật' }}</li>
              <li><strong>Số lượng còn:</strong> {{ product.quantity || product.stock || 1 }}</li>
              <li class="d-flex align-items-center justify-content-between">
                <span>
                  <strong>Đăng bởi:</strong> {{ product.seller?.name || product.khach_hang?.ho_va_ten || 'Người bán ẩn danh' }}
                </span>
                <button 
                  v-if="product?.seller_id || product?.khach_hang_id || product?.seller?.id"
                  class="btn btn-sm btn-success d-flex align-items-center gap-1 ms-2" 
                  type="button" 
                  @click="chatWithSeller"
                  title="Nhắn tin trực tiếp với người bán"
                >
                  <i class="bx bx-message-rounded-dots"></i>
                  <span>Nhắn tin</span>
                </button>
              </li>
              <li><strong>Địa điểm:</strong> {{ product.location || product.dia_chi || 'Liên hệ người bán' }}</li>
            </ul>

            <p class="description mt-3">
              {{ product.description || 'Người bán chưa cung cấp mô tả chi tiết.' }}
            </p>

            <div class="d-flex align-items-center gap-3 my-4 flex-wrap">
              <div class="qty-control">
                <button class="btn btn-sm btn-light" type="button" @click="updateQty(-1)" :disabled="quantity <= 1">−</button>
                <input type="number" min="1" :max="product.quantity || 99" v-model.number="quantity">
                <button class="btn btn-sm btn-light" type="button" @click="updateQty(1)">＋</button>
              </div>

              <button class="btn btn-success flex-grow-1 flex-sm-grow-0" type="button" @click="addToCart">
                <i class="bx bx-cart-alt me-1"></i> Thêm vào giỏ
              </button>

              <button class="btn btn-outline-primary flex-grow-1 flex-sm-grow-0" type="button" @click="buyNow">
                Mua ngay
              </button>

              <button 
                v-if="product?.seller_id || product?.khach_hang_id || product?.seller?.id"
                class="btn btn-success flex-grow-1 flex-sm-grow-0" 
                type="button" 
                @click="chatWithSeller"
                title="Chat với người bán"
              >
                <i class="bx bx-message-rounded-dots me-1"></i> Chat với người bán
              </button>
            </div>

            <div class="card bg-light border-0">
              <div class="card-body d-flex flex-column gap-2">
                <div><i class="bx bx-check-circle text-success me-2"></i> Được kiểm duyệt bởi Chợ Đồ Cũ</div>
                <div><i class="bx bx-shield-quarter text-success me-2"></i> Hỗ trợ hoàn tiền nếu không đúng mô tả</div>
                <div><i class="bx bx-chat text-success me-2"></i> Chat với người bán để thương lượng giá</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section v-if="related.length" class="mt-5">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-semibold m-0">Sản phẩm tương tự</h4>
        <router-link :to="`/danh-sach-san-pham?category=${product?.category?.slug || product?.category}`" class="btn btn-link btn-sm">
          Xem thêm
        </router-link>
      </div>
      <div class="row g-3">
        <article v-for="item in related" :key="item.id" class="col-6 col-md-4 col-lg-3">
          <div class="related-card card h-100">
            <img :src="item.image || fallbackImg" class="card-img-top" :alt="item.name" @error="onImgError($event)">
            <div class="card-body">
              <h6 class="text-truncate">{{ item.name }}</h6>
              <p class="mb-2 text-muted small">{{ formatCurrency(finalPrice(item)) }}</p>
              <router-link class="btn btn-outline-primary btn-sm w-100" :to="`/san-pham/${item.id}`">
                Xem chi tiết
              </router-link>
            </div>
          </div>
        </article>
      </div>
    </section>
  </div>
  <div v-else class="d-flex justify-content-center align-items-center py-5">
    <div class="spinner-border text-success" role="status">
      <span class="visually-hidden">Đang tải...</span>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'SanPham',
  data() {
    return {
      API_BASE_URL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/client',
      CART_STORAGE_KEY: 'fe_marketplace_cart',
      product: null,
      related: [],
      images: [],
      isLoading: true,
      errorMessage: '',
      liked: false,
      quantity: 1
    }
  },
  computed: {
    productId() {
      return this.$route.params?.id
    }
  },
  watch: {
    '$route.params.id'() {
      this.fetchProduct()
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  },
  mounted() {
    this.fetchProduct()
  },
  methods: {
    async fetchProduct() {
      if (!this.productId) return
      this.isLoading = true
      this.errorMessage = ''
      this.quantity = 1
      try {
        const { data } = await axios.get(`${this.API_BASE_URL}/san-pham/${this.productId}`)
        const payload = data?.data || data
        this.product = payload
        this.buildImages(payload)
        await this.fetchRelated(payload?.id)
      } catch (err) {
        console.error(err)
        this.errorMessage = err?.response?.data?.message || 'Không thể tải thông tin sản phẩm.'
        this.product = null
      } finally {
        this.isLoading = false
      }
    },
    buildImages(payload) {
      const gallery = []
      // Nhận image (single URL)
      if (payload?.image) gallery.push(payload.image)
      // Nhận images (array)
      if (Array.isArray(payload?.images)) {
        gallery.push(...payload.images.map(item => (typeof item === 'string' ? item : item.url)))
      }
      // Fallback: nếu không có image/images, thử parse từ hinh_anh (có thể là JSON string hoặc URL string)
      if (!gallery.length && payload?.hinh_anh) {
        try {
          // Thử parse JSON nếu là JSON string
          const parsed = typeof payload.hinh_anh === 'string' && payload.hinh_anh.trim().startsWith('[')
            ? JSON.parse(payload.hinh_anh)
            : payload.hinh_anh
          if (Array.isArray(parsed)) {
            gallery.push(...parsed.filter(item => typeof item === 'string' && item))
          } else if (typeof parsed === 'string' && parsed) {
            gallery.push(parsed)
          }
        } catch (e) {
          // Nếu parse lỗi, coi như là URL string đơn giản
          if (typeof payload.hinh_anh === 'string' && payload.hinh_anh) {
            gallery.push(payload.hinh_anh)
          }
        }
      }
      if (!gallery.length) gallery.push(this.fallbackImg)
      this.images = Array.from(new Set(gallery))
      this.activeImage = this.images[0]
    },
    async fetchRelated(id) {
      if (!id) {
        this.related = []
        return
      }
      try {
        const { data } = await axios.get(`${this.API_BASE_URL}/san-pham/${id}/similar`)
        const rawRelated = data?.data || data || []
        // Map field names từ backend
        this.related = Array.isArray(rawRelated) 
          ? rawRelated.map(item => {
              // Xử lý image từ item.image hoặc item.images hoặc item.hinh_anh
              let image = item.image || null
              if (!image && Array.isArray(item.images) && item.images.length > 0) {
                image = item.images[0]
              }
              if (!image && item.hinh_anh) {
                try {
                  const parsed = typeof item.hinh_anh === 'string' && item.hinh_anh.trim().startsWith('[')
                    ? JSON.parse(item.hinh_anh)
                    : item.hinh_anh
                  if (Array.isArray(parsed) && parsed.length > 0) {
                    image = parsed[0]
                  } else if (typeof parsed === 'string' && parsed) {
                    image = parsed
                  }
                } catch (e) {
                  if (typeof item.hinh_anh === 'string' && item.hinh_anh) {
                    image = item.hinh_anh
                  }
                }
              }
              return {
                id: item.id,
                name: item.ten_san_pham || item.name,
                price: item.gia || item.price,
                discount: item.discount || 0,
                image: image || this.fallbackImg,
              }
            })
          : []
      } catch (err) {
        console.warn('Không thể tải sản phẩm liên quan', err)
        this.related = []
      }
    },
    finalPrice(item) {
      const base = Number(item?.price || 0)
      const discount = Number(item?.discount || 0)
      return base * (1 - discount / 100)
    },
    formatCurrency(value) {
      return (Number(value) || 0).toLocaleString('vi-VN') + ' ₫'
    },
    onImgError(event) {
      // Tránh vòng lặp vô hạn
      if (event.target.dataset.fallbackSet === 'true') {
        event.target.style.display = 'none'
        return
      }
      event.target.dataset.fallbackSet = 'true'
      event.target.src = this.fallbackImg
      event.target.onerror = null
    },
    updateQty(delta) {
      const next = this.quantity + delta
      this.quantity = Math.min(Math.max(next, 1), Number(this.product?.quantity || 99))
    },
    addToCart() {
      if (!this.product) return
      const raw = localStorage.getItem(this.CART_STORAGE_KEY)
      const list = raw ? JSON.parse(raw) : []
      const idx = Array.isArray(list) ? list.findIndex(item => item.id === this.product.id) : -1
      if (idx > -1) {
        list[idx].quantity = Math.min(99, Number(list[idx].quantity || 0) + this.quantity)
      } else {
        list.push({
          id: this.product.id,
          name: this.product.name,
          price: Number(this.finalPrice(this.product)),
          quantity: this.quantity,
          image: this.images[0],
          category: this.product.category?.name || this.product.category || ''
        })
      }
      localStorage.setItem(this.CART_STORAGE_KEY, JSON.stringify(list))
      // Dispatch event to update cart count in header
      window.dispatchEvent(new CustomEvent('cart-updated'))
      if (typeof window !== 'undefined' && window?.$toast) {
        window.$toast.success('Đã thêm vào giỏ hàng!')
      } else {
        alert('Đã thêm vào giỏ hàng!')
      }
    },
    buyNow() {
      if (!this.product) return
      this.$router.push({
        name: 'checkout',
        query: {
          product_id: this.product.id,
          quantity: this.quantity,
        },
      })
    },
    toggleFavourite() {
      this.liked = !this.liked
      // Có thể gọi API đánh dấu yêu thích tại đây.
    },
    async chatWithSeller() {
      const sellerId = this.product?.seller_id || this.product?.khach_hang_id || this.product?.seller?.id
      if (!sellerId) {
        this.showToast('Không tìm thấy thông tin người bán', 'error')
        return
      }

      // Kiểm tra đăng nhập
      const token = localStorage.getItem('key_client')
      if (!token) {
        if (confirm('Bạn cần đăng nhập để nhắn tin. Bạn có muốn chuyển đến trang đăng nhập?')) {
          this.$router.push('/dang-nhap')
        }
        return
      }

      try {
        // Tạo hoặc lấy chat với người bán
        const { data } = await axios.post(
          `${this.API_BASE_URL}/chat/create-or-get`,
          { seller_id: sellerId, product_id: this.productId },
          { headers: { 'Authorization': `Bearer ${token}` } }
        )

        if (data?.status && data?.data?.chat_id) {
          // Chuyển đến trang chat ngay lập tức
          this.$router.push(`/chat/${data.data.chat_id}`)
        } else {
          throw new Error('Không thể tạo cuộc trò chuyện')
        }
      } catch (error) {
        console.error('Lỗi khi tạo chat:', error)
        const errorMsg = error?.response?.data?.message || 'Không thể tạo cuộc trò chuyện. Vui lòng thử lại.'
        this.showToast(errorMsg, 'error')
        
        // Fallback: thử chuyển trực tiếp đến chat room (nếu đã tồn tại)
        // const chatId = `chat-${sellerId}-${this.productId}`
        // this.$router.push(`/chat/${chatId}`)
      }
    },
    showToast(message, type = 'info') {
      if (typeof window !== 'undefined' && window?.$toast) {
        window.$toast[type](message)
      } else {
        alert(message)
      }
    }
  }
}
</script>

<style scoped>
.product-detail {
  min-height: 70vh;
}
.gallery {
  border-radius: 20px;
  overflow: hidden;
}
.main-img {
  object-fit: cover;
  width: 100%;
  height: 100%;
  border-top-left-radius: 20px;
  border-top-right-radius: 20px;
}
.thumb-list {
  display: flex;
  gap: 12px;
  padding: 0 16px 16px;
  flex-wrap: wrap;
}
.thumb-btn {
  border: 2px solid transparent;
  border-radius: 12px;
  padding: 0;
  overflow: hidden;
  width: 72px;
  height: 72px;
  flex: 0 0 auto;
  background: transparent;
}
.thumb-btn img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.thumb-btn.active {
  border-color: #16a34a;
}
.info {
  border-radius: 20px;
  overflow: hidden;
}
.price-box .price {
  font-size: 28px;
  font-weight: 700;
  color: #16a34a;
}
.meta li {
  margin-bottom: 6px;
}
.meta li.d-flex {
  margin-bottom: 10px;
  padding: 8px 0;
  border-bottom: 1px solid #e2e8f0;
}
.meta li.d-flex:last-child {
  border-bottom: none;
}
.description {
  line-height: 1.6;
  color: #475569;
}
.qty-control {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  padding: 4px 12px;
  background: #f8fafc;
}
.qty-control input {
  width: 48px;
  border: none;
  background: transparent;
  text-align: center;
  font-weight: 600;
}
.related-card {
  border-radius: 16px;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.related-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
}
.related-card img {
  height: 160px;
  object-fit: cover;
}
@media (max-width: 767.98px) {
  .thumb-btn {
    width: 64px;
    height: 64px;
  }
}
</style>


