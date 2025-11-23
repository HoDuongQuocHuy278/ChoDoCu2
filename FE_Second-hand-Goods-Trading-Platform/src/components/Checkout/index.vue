<template>
  <div class="checkout-page">
    <!-- Loading State -->
    <div v-if="isLoading" class="loading-container">
      <div class="text-center py-5">
        <div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;">
          <span class="visually-hidden">Đang tải...</span>
        </div>
        <p class="text-muted">Đang tải thông tin sản phẩm...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage && !product" class="error-container">
      <div class="container py-5">
        <div class="alert alert-danger">
          <h5>Không thể tải thông tin sản phẩm</h5>
          <p>{{ errorMessage }}</p>
          <button class="btn btn-primary" type="button" @click="loadProduct">Thử lại</button>
          <router-link to="/danh-sach-san-pham" class="btn btn-outline-secondary ms-2">Xem sản phẩm khác</router-link>
        </div>
      </div>
    </div>

    <!-- Order Success State -->
    <div v-else-if="orderResult" class="order-success-container">
      <div class="container py-5">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="success-card text-center">
              <div class="success-icon">
                <i class='bx bx-check-circle'></i>
              </div>
              <h2 class="success-title">Đặt hàng thành công!</h2>
              <p class="success-message">Cảm ơn bạn đã mua sắm tại Chợ Đồ Cũ</p>
              
              <div class="order-details-card">
                <div class="order-header">
                  <h5>Thông tin đơn hàng</h5>
                  <span class="order-code">Mã đơn: <strong>{{ orderResult.ma_don_hang }}</strong></span>
                </div>
                <div class="order-body">
                  <div class="order-item">
                    <div class="order-item-image">
                      <img :src="orderProductImage" :alt="orderResult?.san_pham?.ten_san_pham || product?.name" @error="onImgError">
                    </div>
                    <div class="order-item-info">
                      <h6>{{ orderResult?.san_pham?.ten_san_pham || product?.name }}</h6>
                      <p class="text-muted small">Số lượng: {{ orderResult.so_luong }}</p>
                    </div>
                    <div class="order-item-price">
                      <strong>{{ formatCurrency(orderResult.tong_tien) }}</strong>
                    </div>
                  </div>
                </div>
                <div class="order-footer">
                  <div class="order-summary">
                    <div class="summary-row">
                      <span>Tạm tính:</span>
                      <span>{{ formatCurrency(orderResult.tong_tien) }}</span>
                    </div>
                    <div class="summary-row">
                      <span>Phí vận chuyển:</span>
                      <span>Miễn phí</span>
                    </div>
                    <div class="summary-row total">
                      <span>Tổng tiền:</span>
                      <span>{{ formatCurrency(orderResult.tong_tien) }}</span>
                    </div>
                  </div>
                  <div class="payment-info">
                    <p><strong>Phương thức thanh toán:</strong> {{ getPaymentLabel(orderResult.payment_method) }}</p>
                    <p class="text-muted small">{{ paymentInstruction(orderResult.payment_method) }}</p>
                    
                    <!-- VNPAY Payment -->
                    <div v-if="paymentUrl && (orderResult.payment_method === 'vnpay')" class="payment-action mt-3">
                      <a :href="paymentUrl" target="_blank" class="btn btn-primary btn-lg w-100">
                        <i class='bx bx-credit-card'></i> Thanh toán VNPAY ngay
                      </a>
                      <p class="text-muted small mt-2">Hoặc quét QR code bên dưới</p>
                    </div>
                    
                    <!-- MBBank Payment -->
                    <div v-if="orderResult.payment_method === 'mbbank'" class="payment-action mt-3">
                      <div v-if="processingPayment" class="text-center py-3">
                        <div class="spinner-border text-primary mb-2" role="status">
                          <span class="visually-hidden">Đang tạo giao dịch...</span>
                        </div>
                        <p class="text-muted">Đang tạo giao dịch MBBank...</p>
                      </div>
                      
                      <div v-else-if="paymentData" class="mbbank-payment-info">
                        <div v-if="paymentData.qr_code || paymentData.qrCode" class="mb-3">
                          <p class="text-center"><strong>Quét QR code để thanh toán:</strong></p>
                          <div class="text-center">
                            <img 
                              :src="paymentData.qr_code || paymentData.qrCode" 
                              alt="MBBank QR Code" 
                              class="qr-code-img" 
                              style="max-width: 300px; border: 1px solid #ddd; padding: 10px; background: white; border-radius: 8px;"
                            >
                          </div>
                        </div>
                        <div v-if="paymentData.payment_url || paymentData.paymentUrl" class="mb-3">
                          <a 
                            :href="paymentData.payment_url || paymentData.paymentUrl" 
                            target="_blank" 
                            class="btn btn-primary btn-lg w-100"
                          >
                            <i class='bx bx-mobile'></i> Thanh toán MBBank ngay
                          </a>
                        </div>
                        <div v-if="paymentData.transaction_id || paymentData.transactionId || paymentData.id" class="alert alert-info">
                          <small>
                            <strong>Mã giao dịch:</strong> 
                            {{ paymentData.transaction_id || paymentData.transactionId || paymentData.id }}
                          </small>
                        </div>
                        <div v-if="paymentData.status" class="alert" :class="paymentData.status === 'success' ? 'alert-success' : 'alert-info'">
                          <small><strong>Trạng thái:</strong> {{ paymentData.status }}</small>
                        </div>
                      </div>
                      
                      <div v-else class="alert alert-warning">
                        <p class="mb-2">Chưa tạo được giao dịch thanh toán.</p>
                        <button 
                          class="btn btn-sm btn-primary" 
                          @click="createPayment(orderResult.id, 'mbbank')"
                          :disabled="processingPayment"
                        >
                          <i class='bx bx-refresh'></i> Thử lại
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="success-actions">
                <button class="btn btn-success btn-lg" type="button" @click="router.push('/trang-chu')">
                  <i class='bx bx-home'></i> Về trang chủ
                </button>
                <button class="btn btn-outline-primary btn-lg" type="button" @click="router.push(`/san-pham/${orderResult.san_pham_id || productId}`)">
                  <i class='bx bx-show'></i> Xem lại sản phẩm
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Checkout Form -->
    <div v-else-if="product" class="checkout-form-container">
      <div class="container py-4">
        <!-- Breadcrumb -->
        <nav class="breadcrumb-nav mb-4">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <router-link to="/trang-chu">Trang chủ</router-link>
            </li>
            <li class="breadcrumb-item">
              <router-link to="/danh-sach-san-pham">Sản phẩm</router-link>
            </li>
            <li class="breadcrumb-item active">Thanh toán</li>
          </ol>
        </nav>

        <!-- Progress Steps -->
        <div class="checkout-steps mb-4">
          <div class="step" :class="{ active: currentStep >= 1, completed: currentStep > 1 }">
            <div class="step-number">1</div>
            <div class="step-label">Thông tin đơn hàng</div>
          </div>
          <div class="step-connector"></div>
          <div class="step" :class="{ active: currentStep >= 2, completed: currentStep > 2 }">
            <div class="step-number">2</div>
            <div class="step-label">Phương thức thanh toán</div>
          </div>
          <div class="step-connector"></div>
          <div class="step" :class="{ active: currentStep >= 3, completed: currentStep > 3 }">
            <div class="step-number">3</div>
            <div class="step-label">Xác nhận</div>
          </div>
        </div>

        <div class="row g-4">
          <!-- Left Column: Form -->
          <div class="col-lg-8">
            <!-- Step 1: Order Information -->
            <div v-show="currentStep === 1" class="checkout-step">
              <div class="card shadow-sm">
                <div class="card-header">
                  <h5 class="mb-0">
                    <i class='bx bx-package'></i> Thông tin đơn hàng
                  </h5>
                </div>
                <div class="card-body">
                  <!-- Product Info -->
                  <div class="product-summary mb-4">
                    <div class="d-flex align-items-center gap-3">
                      <img :src="productImage" :alt="product.name" class="product-thumb" @error="onImgError">
                      <div class="flex-grow-1">
                        <h6 class="mb-1">{{ product.name }}</h6>
                        <p class="text-muted small mb-2">{{ product.category?.name || product.category || 'Danh mục khác' }}</p>
                        <div class="d-flex align-items-center gap-3">
                          <span class="text-success fw-bold">{{ formatCurrency(product.price) }}</span>
                          <div class="quantity-selector">
                            <button class="btn btn-sm btn-light" type="button" @click="changeQuantity(-1)" :disabled="form.quantity <= 1">−</button>
                            <input type="number" min="1" max="99" v-model.number="form.quantity" @input="clampQuantity" class="quantity-input">
                            <button class="btn btn-sm btn-light" type="button" @click="changeQuantity(1)">＋</button>
                          </div>
                        </div>
                      </div>
                      <div class="text-end">
                        <p class="text-muted small mb-1">Tổng tiền</p>
                        <h5 class="text-success mb-0">{{ formatCurrency(totalAmount) }}</h5>
                      </div>
                    </div>
                  </div>

                  <hr>

                  <!-- Buyer Information -->
                  <h6 class="mb-3">
                    <i class='bx bx-user'></i> Thông tin người mua
                  </h6>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                      <input 
                        v-model="form.buyer_name" 
                        type="text" 
                        class="form-control" 
                        placeholder="Nguyễn Văn A" 
                        required
                        :class="{ 'is-invalid': errors.buyer_name }"
                      >
                      <div v-if="errors.buyer_name" class="invalid-feedback">{{ errors.buyer_name }}</div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                      <input 
                        v-model="form.buyer_phone" 
                        type="tel" 
                        class="form-control" 
                        placeholder="0123 456 789"
                        :class="{ 'is-invalid': errors.buyer_phone }"
                      >
                      <div v-if="errors.buyer_phone" class="invalid-feedback">{{ errors.buyer_phone }}</div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Email</label>
                      <input 
                        v-model="form.buyer_email" 
                        type="email" 
                        class="form-control" 
                        placeholder="example@email.com"
                        :class="{ 'is-invalid': errors.buyer_email }"
                      >
                      <div v-if="errors.buyer_email" class="invalid-feedback">{{ errors.buyer_email }}</div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Địa chỉ nhận hàng</label>
                      <input 
                        v-model="form.shipping_address" 
                        type="text" 
                        class="form-control" 
                        placeholder="Số nhà, đường, quận/huyện, thành phố"
                      >
                    </div>
                    <div class="col-12">
                      <label class="form-label">Ghi chú cho người bán</label>
                      <textarea 
                        v-model="form.notes" 
                        class="form-control" 
                        rows="3" 
                        placeholder="Ví dụ: Giao chiều thứ 7, gọi mình trước 30 phút..."
                      ></textarea>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 2: Payment Method -->
            <div v-show="currentStep === 2" class="checkout-step">
              <div class="card shadow-sm">
                <div class="card-header">
                  <h5 class="mb-0">
                    <i class='bx bx-credit-card'></i> Phương thức thanh toán
                    <span class="badge bg-success-subtle text-success ms-2">Bảo mật 100%</span>
                  </h5>
                </div>
                <div class="card-body">
                  <div class="payment-options">
                    <label
                      v-for="option in paymentOptions"
                      :key="option.value"
                      class="payment-option-card"
                      :class="{ active: form.payment_method === option.value }"
                      @click="form.payment_method = option.value"
                    >
                      <input
                        type="radio"
                        :value="option.value"
                        v-model="form.payment_method"
                        class="form-check-input"
                      >
                      <div class="payment-option-icon" :class="`icon-${option.value}`">
                        <i :class="option.icon"></i>
                      </div>
                      <div class="payment-option-content">
                        <div class="payment-option-title">{{ option.label }}</div>
                        <div class="payment-option-desc">{{ option.desc }}</div>
                      </div>
                      <span class="payment-option-badge" v-if="option.badge">{{ option.badge }}</span>
                    </label>
                  </div>
                  <div class="payment-instruction mt-4 p-3 bg-light rounded">
                    <i class='bx bx-info-circle text-primary'></i>
                    <span class="ms-2">{{ paymentInstruction(form.payment_method) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 3: Confirmation -->
            <div v-show="currentStep === 3" class="checkout-step">
              <div class="card shadow-sm">
                <div class="card-header">
                  <h5 class="mb-0">
                    <i class='bx bx-check-circle'></i> Xác nhận đơn hàng
                  </h5>
                </div>
                <div class="card-body">
                  <div class="order-review">
                    <div class="review-section">
                      <h6>Thông tin sản phẩm</h6>
                      <div class="d-flex align-items-center gap-3 mb-3">
                        <img :src="productImage" :alt="product.name" class="review-thumb" @error="onImgError">
                        <div class="flex-grow-1">
                          <h6 class="mb-1">{{ product.name }}</h6>
                          <p class="text-muted small mb-0">Số lượng: {{ form.quantity }} x {{ formatCurrency(product.price) }}</p>
                        </div>
                        <div class="text-end">
                          <strong class="text-success">{{ formatCurrency(totalAmount) }}</strong>
                        </div>
                      </div>
                    </div>

                    <hr>

                    <div class="review-section">
                      <h6>Thông tin người mua</h6>
                      <div class="review-info">
                        <p><strong>Họ tên:</strong> {{ form.buyer_name }}</p>
                        <p v-if="form.buyer_phone"><strong>Số điện thoại:</strong> {{ form.buyer_phone }}</p>
                        <p v-if="form.buyer_email"><strong>Email:</strong> {{ form.buyer_email }}</p>
                        <p v-if="form.shipping_address"><strong>Địa chỉ:</strong> {{ form.shipping_address }}</p>
                        <p v-if="form.notes"><strong>Ghi chú:</strong> {{ form.notes }}</p>
                      </div>
                    </div>

                    <hr>

                    <div class="review-section">
                      <h6>Phương thức thanh toán</h6>
                      <div class="d-flex align-items-center gap-2">
                        <i :class="getPaymentIcon(form.payment_method)"></i>
                        <span>{{ getPaymentLabel(form.payment_method) }}</span>
                      </div>
                    </div>

                    <hr>

                    <div class="review-section">
                      <div class="order-total">
                        <div class="total-row">
                          <span>Tạm tính:</span>
                          <span>{{ formatCurrency(totalAmount) }}</span>
                        </div>
                        <div class="total-row">
                          <span>Phí vận chuyển:</span>
                          <span>Miễn phí</span>
                        </div>
                        <div class="total-row final">
                          <span>Tổng tiền:</span>
                          <span>{{ formatCurrency(totalAmount) }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Order Summary -->
          <div class="col-lg-4">
            <div class="order-summary-card sticky-top">
              <div class="card shadow-sm">
                <div class="card-header">
                  <h5 class="mb-0">Tóm tắt đơn hàng</h5>
                </div>
                <div class="card-body">
                  <div class="summary-item">
                    <img :src="productImage" :alt="product.name" class="summary-thumb" @error="onImgError">
                    <div class="summary-item-info">
                      <p class="summary-item-name">{{ product.name }}</p>
                      <p class="text-muted small">Số lượng: {{ form.quantity }}</p>
                    </div>
                  </div>
                  <hr>
                  <div class="summary-totals">
                    <div class="summary-row">
                      <span>Đơn giá:</span>
                      <span>{{ formatCurrency(product.price) }}</span>
                    </div>
                    <div class="summary-row">
                      <span>Số lượng:</span>
                      <span>{{ form.quantity }}</span>
                    </div>
                    <div class="summary-row">
                      <span>Phí vận chuyển:</span>
                      <span class="text-success">Miễn phí</span>
                    </div>
                    <hr>
                    <div class="summary-row total">
                      <span>Tổng tiền:</span>
                      <span class="total-amount">{{ formatCurrency(totalAmount) }}</span>
                    </div>
                  </div>
                </div>
                <div class="card-footer">
                  <button
                    v-if="currentStep === 1"
                    class="btn btn-primary w-100"
                    type="button"
                    @click="nextStep"
                    :disabled="!canProceedToStep2"
                  >
                    Tiếp tục <i class='bx bx-chevron-right'></i>
                  </button>
                  <button
                    v-else-if="currentStep === 2"
                    class="btn btn-primary w-100"
                    type="button"
                    @click="nextStep"
                  >
                    Tiếp tục <i class='bx bx-chevron-right'></i>
                  </button>
                  <button
                    v-else-if="currentStep === 3"
                    class="btn btn-success w-100 btn-lg"
                    type="button"
                    @click="submitOrder"
                    :disabled="submitting"
                  >
                    <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                    <span v-else><i class='bx bx-check'></i> Xác nhận đặt hàng</span>
                  </button>
                </div>
              </div>

              <!-- Navigation Buttons -->
              <div class="step-navigation mt-3">
                <button
                  v-if="currentStep > 1"
                  class="btn btn-outline-secondary w-100"
                  type="button"
                  @click="prevStep"
                >
                  <i class='bx bx-chevron-left'></i> Quay lại
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/client'

const product = ref(null)
const isLoading = ref(true)
const errorMessage = ref('')
const orderResult = ref(null)
const submitting = ref(false)
const currentStep = ref(1)
const paymentUrl = ref(null)
const paymentData = ref(null)
const processingPayment = ref(false)

const form = reactive({
  buyer_name: '',
  buyer_email: '',
  buyer_phone: '',
  shipping_address: '',
  notes: '',
  quantity: Number(route.query.quantity || 1) || 1,
  payment_method: 'vnpay'
})

const errors = reactive({
  buyer_name: '',
  buyer_email: '',
  buyer_phone: ''
})

const paymentOptions = [
  { 
    value: 'vnpay', 
    label: 'Ví VNPAY', 
    desc: 'Thanh toán online qua QR hoặc thẻ liên kết',
    icon: 'bx bx-credit-card',
    badge: 'Khuyến nghị'
  },
  { 
    value: 'mbbank', 
    label: 'Ví MBBANK', 
    desc: 'Thanh toán online qua QR hoặc thẻ liên kết',
    icon: 'bx bx-mobile',
    badge: 'Phổ biến'
  },
  { 
    value: 'cash', 
    label: 'Tiền mặt (COD)', 
    desc: 'Thanh toán khi nhận hàng',
    icon: 'bx bx-money',
    badge: 'Tiện lợi'
  }
]

const productId = computed(() => route.query.product_id || route.query.product || null)

const totalAmount = computed(() => {
  if (!product.value) return 0
  const price = Number(product.value.price || 0)
  return price * Math.max(1, form.quantity)
})

// Tạo data URI SVG cho placeholder image
function getDefaultProductImage() {
  return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDE2MCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxNjAiIGhlaWdodD0iMTIwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik04MCA0OEM4Ny45NTU4IDQ4IDk0IDQxLjk1NTggOTQgMzRDOTQgMjYuMDQ0MiA4Ny45NTU4IDIwIDgwIDIwQzcyLjA0NDIgMjAgNjYgMjYuMDQ0MiA2NiAzNEM2NiA0MS45NTU4IDcyLjA0NDIgNDggODAgNDhaIiBmaWxsPSIjOUNBM0FGIi8+CjxwYXRoIGQ9Ik04MCA3MkM2OCA3MiA1OCA3OCA1MCA4OEgxMTBDMTAyIDc4IDkyIDcyIDgwIDcyWiIgZmlsbD0iIzlDQTNBRiIvPgo8L3N2Zz4='
}

const productImage = computed(() => {
  if (!product.value) return getDefaultProductImage()
  if (product.value.image) return product.value.image
  if (Array.isArray(product.value.images) && product.value.images.length) {
    return product.value.images[0]
  }
  // Fallback: thử parse từ hinh_anh
  if (product.value.hinh_anh) {
    try {
      const parsed = typeof product.value.hinh_anh === 'string' && product.value.hinh_anh.trim().startsWith('[')
        ? JSON.parse(product.value.hinh_anh)
        : product.value.hinh_anh
      if (Array.isArray(parsed) && parsed.length > 0) {
        return parsed[0]
      } else if (typeof parsed === 'string' && parsed) {
        return parsed
      }
    } catch (e) {
      if (typeof product.value.hinh_anh === 'string' && product.value.hinh_anh) {
        return product.value.hinh_anh
      }
    }
  }
  return getDefaultProductImage()
})

const orderProductImage = computed(() => {
  if (orderResult.value?.san_pham?.hinh_anh) {
    try {
      const hinhAnh = orderResult.value.san_pham.hinh_anh
      let images = []
      if (Array.isArray(hinhAnh)) {
        images = hinhAnh
      } else if (typeof hinhAnh === 'string') {
        if (hinhAnh.trim().startsWith('[')) {
          images = JSON.parse(hinhAnh)
        } else {
          images = [hinhAnh]
        }
      }
      return images[0] || productImage.value
    } catch (e) {
      // Nếu parse lỗi, dùng productImage
      return productImage.value
    }
  }
  return productImage.value
})

const canProceedToStep2 = computed(() => {
  return form.buyer_name.trim() && form.buyer_phone.trim()
})

const loadProduct = async () => {
  if (!productId.value) {
    errorMessage.value = 'Không tìm thấy sản phẩm để mua. Vui lòng chọn sản phẩm từ danh sách.'
    product.value = null
    isLoading.value = false
    return
  }
  
  isLoading.value = true
  errorMessage.value = ''
  orderResult.value = null
  currentStep.value = 1
  
  try {
    const response = await axios.get(`${API_BASE_URL}/san-pham/${productId.value}`)
    const payload = response.data?.data || response.data
    
    if (payload && payload.id) {
      product.value = payload
      
      // Auto-fill thông tin người mua từ localStorage nếu có
      const userInfo = localStorage.getItem('user_info')
      if (userInfo) {
        try {
          const user = JSON.parse(userInfo)
          if (user.ho_va_ten) form.buyer_name = user.ho_va_ten
          if (user.email) form.buyer_email = user.email
          if (user.so_dien_thoai) form.buyer_phone = user.so_dien_thoai
        } catch (e) {
          console.warn('Không thể parse user info', e)
        }
      }
    } else {
      errorMessage.value = 'Không tìm thấy thông tin sản phẩm. Dữ liệu trả về không hợp lệ.'
      product.value = null
    }
  } catch (err) {
    console.error('Error loading product:', err)
    errorMessage.value = err?.response?.data?.message || err?.message || 'Không thể tải thông tin sản phẩm. Vui lòng thử lại.'
    product.value = null
  } finally {
    isLoading.value = false
  }
}

const validateStep1 = () => {
  errors.buyer_name = ''
  errors.buyer_phone = ''
  errors.buyer_email = ''
  
  if (!form.buyer_name.trim()) {
    errors.buyer_name = 'Vui lòng nhập họ tên'
    return false
  }
  
  if (!form.buyer_phone.trim()) {
    errors.buyer_phone = 'Vui lòng nhập số điện thoại'
    return false
  }
  
  if (form.buyer_phone && !/^[0-9]{10,11}$/.test(form.buyer_phone.replace(/\s/g, ''))) {
    errors.buyer_phone = 'Số điện thoại không hợp lệ'
    return false
  }
  
  if (form.buyer_email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.buyer_email)) {
    errors.buyer_email = 'Email không hợp lệ'
    return false
  }
  
  return true
}

const nextStep = () => {
  if (currentStep.value === 1) {
    if (validateStep1()) {
      currentStep.value = 2
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  } else if (currentStep.value === 2) {
    currentStep.value = 3
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const changeQuantity = (delta) => {
  form.quantity = Math.min(Math.max(form.quantity + delta, 1), 99)
}

const clampQuantity = () => {
  form.quantity = Math.min(Math.max(form.quantity || 1, 1), 99)
}

const submitOrder = async () => {
  if (!validateStep1()) {
    currentStep.value = 1
    return
  }
  
  if (!product.value) {
    errorMessage.value = 'Không tìm thấy sản phẩm.'
    return
  }

  submitting.value = true
  errorMessage.value = ''
  paymentUrl.value = null
  paymentData.value = null
  
  try {
    const payload = {
      product_id: product.value.id,
      quantity: form.quantity,
      payment_method: form.payment_method,
      buyer_name: form.buyer_name,
      buyer_email: form.buyer_email || null,
      buyer_phone: form.buyer_phone || null,
      shipping_address: form.shipping_address || null,
      notes: form.notes || null,
    }

    // Gửi token nếu có (để lưu khach_hang_id)
    const token = localStorage.getItem('key_client')
    const headers = token ? { 'Authorization': `Bearer ${token}` } : {}
    
    const { data } = await axios.post(`${API_BASE_URL}/don-hang`, payload, { headers })
    orderResult.value = data?.data || data
    
    // Nếu là thanh toán online (vnpay hoặc mbbank), tạo payment
    if (form.payment_method === 'vnpay' || form.payment_method === 'mbbank') {
      await createPayment(orderResult.value.id, form.payment_method)
    }
    
    // Scroll to top to show success message
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } catch (err) {
    console.error(err)
    errorMessage.value = err?.response?.data?.message || 'Không thể tạo đơn hàng. Vui lòng thử lại.'
    // Show error and go back to step 1
    currentStep.value = 1
  } finally {
    submitting.value = false
  }
}

const createPayment = async (orderId, paymentMethod) => {
  if (!orderId || !orderResult.value) return
  
  processingPayment.value = true
  
  try {
    const token = localStorage.getItem('key_client')
    const headers = token ? { 'Authorization': `Bearer ${token}` } : {}
    
    const paymentPayload = {
      order_id: orderId,
      amount: orderResult.value.tong_tien,
      order_info: `Thanh toán đơn hàng #${orderResult.value.ma_don_hang}`,
      customer_name: form.buyer_name,
      customer_email: form.buyer_email || null,
      customer_phone: form.buyer_phone || null,
    }
    
    const endpoint = paymentMethod === 'vnpay' 
      ? `${API_BASE_URL}/payment/vnpay`
      : `${API_BASE_URL}/payment/mbbank`
    
    console.log('Creating payment:', { endpoint, paymentPayload })
    
    const { data } = await axios.post(endpoint, paymentPayload, { headers })
    
    console.log('Payment response:', data)
    
    if (data?.status && data?.data) {
      if (paymentMethod === 'vnpay') {
        paymentUrl.value = data.data.payment_url || data.data
      } else if (paymentMethod === 'mbbank') {
        paymentData.value = data.data
        // Nếu có payment_url, tự động mở trong tab mới
        if (data.data.payment_url) {
          paymentUrl.value = data.data.payment_url
        }
        // Nếu có QR code URL hoặc base64
        if (data.data.qr_code) {
          paymentData.value.qr_code = data.data.qr_code
        }
      }
    } else {
      throw new Error(data?.message || 'Phản hồi từ server không hợp lệ')
    }
  } catch (err) {
    console.error('Lỗi khi tạo payment:', err)
    const errorMessage = err?.response?.data?.message 
      || err?.response?.data?.error?.message 
      || err?.message 
      || 'Không thể tạo link thanh toán. Vui lòng thử lại.'
    
    const errorDetails = err?.response?.data?.error || err?.response?.data?.debug
    
    if (window?.$toast) {
      window.$toast.error(errorMessage)
    } else {
      alert(errorMessage + (errorDetails ? '\n\nChi tiết: ' + JSON.stringify(errorDetails) : ''))
    }
    
    // Log chi tiết để debug
    console.error('Payment error details:', {
      message: errorMessage,
      response: err?.response?.data,
      status: err?.response?.status,
    })
  } finally {
    processingPayment.value = false
  }
}

const paymentInstruction = (method) => {
  switch (method) {
    case 'vnpay':
      return 'Sau khi xác nhận, hệ thống sẽ gửi mã QR/Link VNPAY để bạn hoàn tất thanh toán.'
    case 'mbbank':
      return 'Bạn sẽ nhận được hướng dẫn thanh toán qua ví MBBank. Vui lòng hoàn tất trong 15 phút.'
    default:
      return 'Thanh toán trực tiếp cho người giao hàng hoặc người bán khi nhận sản phẩm.'
  }
}

const getPaymentLabel = (method) => {
  return paymentOptions.find((item) => item.value === method)?.label || method
}

const getPaymentIcon = (method) => {
  return paymentOptions.find((item) => item.value === method)?.icon || 'bx bx-credit-card'
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(amount || 0)
}

const onImgError = (event) => {
  // Tránh vòng lặp vô hạn
  if (event.target.dataset.fallbackSet === 'true') {
    event.target.style.display = 'none'
    return
  }
  event.target.dataset.fallbackSet = 'true'
  event.target.src = getDefaultProductImage()
  event.target.onerror = null
}

watch(() => route.query.product_id, (newId, oldId) => {
  if (newId !== oldId) {
    form.quantity = Number(route.query.quantity || 1) || 1
    clampQuantity()
    orderResult.value = null
    product.value = null
    currentStep.value = 1
    loadProduct()
  }
})

watch(() => route.query.quantity, (newQty) => {
  if (newQty) {
    form.quantity = Number(newQty) || 1
    clampQuantity()
  }
})

onMounted(() => {
  if (route.query.quantity) {
    form.quantity = Number(route.query.quantity) || 1
    clampQuantity()
  }
  loadProduct()
})
</script>

<style scoped>
.checkout-page {
  min-height: 70vh;
  background: #f9fafb;
}

.loading-container,
.error-container {
  min-height: 50vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Breadcrumb */
.breadcrumb-nav {
  background: white;
  padding: 1rem;
  border-radius: 8px;
}

.breadcrumb {
  margin: 0;
  padding: 0;
}

/* Checkout Steps */
.checkout-steps {
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  padding: 2rem;
  border-radius: 8px;
  gap: 1rem;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
  max-width: 200px;
}

.step-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #e5e7eb;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  transition: all 0.3s;
}

.step.active .step-number {
  background: #16a34a;
  color: white;
}

.step.completed .step-number {
  background: #22c55e;
  color: white;
}

.step.completed .step-number::before {
  content: '✓';
}

.step-label {
  font-size: 14px;
  color: #6b7280;
  text-align: center;
}

.step.active .step-label {
  color: #16a34a;
  font-weight: 600;
}

.step-connector {
  flex: 1;
  height: 2px;
  background: #e5e7eb;
  margin: 0 1rem;
}

/* Product Summary */
.product-summary {
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
}

.product-thumb {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
}

.quantity-selector {
  display: flex;
  align-items: center;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  overflow: hidden;
}

.quantity-selector button {
  border: none;
  background: white;
  padding: 4px 12px;
  cursor: pointer;
}

.quantity-selector button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity-input {
  width: 50px;
  border: none;
  text-align: center;
  padding: 4px;
}

/* Payment Options */
.payment-options {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.payment-option-card {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
  transition: all 0.3s;
  background: white;
}

.payment-option-card:hover {
  border-color: #16a34a;
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.1);
}

.payment-option-card.active {
  border-color: #16a34a;
  background: #f0fdf4;
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.2);
}

.payment-option-icon {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.icon-vnpay {
  background: linear-gradient(135deg, #0052a5, #007bff);
}

.icon-mbbank {
  background: linear-gradient(135deg, #a50064, #d91a72);
}

.icon-cash {
  background: linear-gradient(135deg, #16a34a, #22c55e);
}

.payment-option-content {
  flex: 1;
}

.payment-option-title {
  font-weight: 600;
  margin-bottom: 4px;
}

.payment-option-desc {
  font-size: 14px;
  color: #6b7280;
}

.payment-option-badge {
  padding: 4px 8px;
  background: #e5e7eb;
  border-radius: 4px;
  font-size: 12px;
  color: #6b7280;
}

.payment-option-card.active .payment-option-badge {
  background: #16a34a;
  color: white;
}

/* Order Summary Card */
.order-summary-card {
  top: 100px;
}

.summary-item {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.summary-thumb {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
}

.summary-item-info {
  flex: 1;
}

.summary-item-name {
  font-weight: 600;
  margin-bottom: 4px;
  font-size: 14px;
}

.summary-totals {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
}

.summary-row.total {
  font-size: 18px;
  font-weight: 700;
  color: #16a34a;
  padding-top: 0.5rem;
  border-top: 2px solid #e5e7eb;
  margin-top: 0.5rem;
}

.total-amount {
  color: #16a34a;
  font-size: 20px;
}

/* Order Review */
.review-section {
  margin-bottom: 1.5rem;
}

.review-section h6 {
  margin-bottom: 1rem;
  color: #374151;
}

.review-thumb {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
}

.review-info p {
  margin-bottom: 0.5rem;
  font-size: 14px;
}

.order-total {
  background: #f9fafb;
  padding: 1rem;
  border-radius: 8px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 14px;
}

.total-row.final {
  font-size: 18px;
  font-weight: 700;
  color: #16a34a;
  padding-top: 0.5rem;
  border-top: 2px solid #e5e7eb;
  margin-top: 0.5rem;
}

/* Order Success */
.order-success-container {
  min-height: 70vh;
  background: #f9fafb;
  padding: 2rem 0;
}

.success-card {
  background: white;
  border-radius: 16px;
  padding: 3rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.success-icon {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: #dcfce7;
  color: #16a34a;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  font-size: 60px;
}

.success-title {
  color: #16a34a;
  margin-bottom: 0.5rem;
}

.success-message {
  color: #6b7280;
  margin-bottom: 2rem;
}

.order-details-card {
  background: #f9fafb;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  text-align: left;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.order-code {
  font-size: 14px;
  color: #6b7280;
}

.order-item {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1rem;
}

.order-item-image img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
}

.order-item-info {
  flex: 1;
}

.order-item-price {
  font-size: 18px;
  font-weight: 700;
  color: #16a34a;
}

.order-summary {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.payment-info {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.success-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.success-actions button {
  min-width: 200px;
}

/* Responsive */
@media (max-width: 768px) {
  .checkout-steps {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .step-connector {
    width: 2px;
    height: 30px;
    margin: 0;
  }
  
  .success-actions {
    flex-direction: column;
  }
  
  .success-actions button {
    width: 100%;
  }
  
  .order-summary-card {
    position: relative !important;
    top: 0 !important;
  }
}
</style>
