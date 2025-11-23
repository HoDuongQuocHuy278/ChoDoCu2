<template>
  <div class="cart-page container py-4">
    <header class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 mb-4">
      <div>
        <h2 class="fw-bold m-0">Giỏ hàng của bạn</h2>
        <p class="text-muted mb-0">Quản lý các sản phẩm đã thêm trước khi tiến hành thanh toán.</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" type="button" @click="fetchCart" :disabled="isLoading || isMutating">
          Tải lại dữ liệu
        </button>
        <button class="btn btn-outline-danger" type="button" @click="clearCart" :disabled="items.length === 0 || isMutating">
          Xóa giỏ hàng
        </button>
      </div>
    </header>

    <div v-if="errorMessage" class="alert alert-danger d-flex align-items-center justify-content-between gap-3">
      <span>{{ errorMessage }}</span>
      <button class="btn btn-outline-light btn-sm" type="button" @click="fetchCart" :disabled="isLoading">
        Thử lại
      </button>
    </div>

    <section v-if="isLoading" class="loading-state text-center py-5">
      <div class="spinner-border text-success mb-3" role="status" aria-hidden="true"></div>
      <h4 class="fw-semibold">Đang tải giỏ hàng...</h4>
      <p class="text-muted mb-0">Vui lòng chờ trong giây lát.</p>
    </section>

    <section v-else-if="items.length === 0" class="empty-state text-center py-5">
      <div class="empty-icon mb-3">🛒</div>
      <h4 class="fw-semibold">Giỏ hàng đang trống</h4>
      <p class="text-muted mb-4">Bắt đầu khám phá để tìm những món đồ phù hợp với bạn.</p>
      <router-link class="btn btn-success" to="/danh-sach-san-pham">
        Xem sản phẩm
      </router-link>
    </section>

    <section v-else class="row g-4">
      <div class="col-lg-8">
        <article class="card shadow-sm h-100">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th scope="col">Sản phẩm</th>
                    <th scope="col" class="text-center">Giá</th>
                    <th scope="col" class="text-center qty-col">Số lượng</th>
                    <th scope="col" class="text-center">Tổng</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in items" :key="item.id">
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <img :src="item.image" class="thumb" alt="" @error="onImgError($event)">
                        <div class="flex-grow-1">
                          <h6 class="mb-1 text-truncate">{{ item.name }}</h6>
                          <p class="text-muted small mb-0">{{ item.category || 'Danh mục khác' }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">{{ formatCurrency(item.price) }}</td>
                    <td class="text-center">
                      <div class="qty-control mx-auto">
                        <button type="button" class="btn btn-sm btn-light" @click="updateQty(item, -1)" :disabled="item.quantity <= 1 || isMutating">−</button>
                        <input type="number" min="1" :value="item.quantity" @input="setQty(item, $event.target.value)" :disabled="isMutating">
                        <button type="button" class="btn btn-sm btn-light" @click="updateQty(item, 1)" :disabled="isMutating">＋</button>
                      </div>
                    </td>
                    <td class="text-center fw-semibold">{{ formatCurrency(lineTotal(item)) }}</td>
                    <td class="text-end">
                      <button class="btn btn-link text-danger p-0" @click="removeItem(item)" :disabled="isMutating">Xóa</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </article>
      </div>

      <div class="col-lg-4">
        <aside class="card shadow-sm sticky-top" style="top: 90px;">
          <div class="card-body">
            <h5 class="fw-semibold mb-3">Tóm tắt đơn hàng</h5>

            <div class="d-flex justify-content-between mb-2">
              <span>Tạm tính</span>
              <span class="fw-semibold">{{ formatCurrency(subTotal) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Phí vận chuyển</span>
              <span>{{ shippingFee > 0 ? formatCurrency(shippingFee) : 'Miễn phí' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span>Giảm giá</span>
              <span>-{{ formatCurrency(discountAmount) }}</span>
            </div>

            <div class="border-top pt-3 d-flex justify-content-between align-items-center">
              <span class="fw-bold">Thành tiền</span>
              <span class="fw-bold fs-5 text-success">{{ formatCurrency(total) }}</span>
            </div>

            <div class="mt-3">
              <label class="form-label">Mã giảm giá</label>
              <div class="input-group">
                <input v-model.trim="voucher" type="text" class="form-control" placeholder="Nhập mã (ví dụ: FREESHIP)">
                <button class="btn btn-outline-success" type="button" @click="applyVoucher">Áp dụng</button>
              </div>
              <p v-if="voucherMessage" class="small mt-2" :class="voucherSuccess ? 'text-success' : 'text-danger'">
                {{ voucherMessage }}
              </p>
            </div>

            <div class="mt-4 d-grid gap-2">
              <button 
                class="btn btn-success btn-lg" 
                type="button"
                @click="proceedCheckout"
                :disabled="items.length === 0"
              >
                Tiếp tục thanh toán
              </button>
              <router-link to="/danh-sach-san-pham" class="btn btn-outline-secondary">
                Tiếp tục mua sắm
              </router-link>
            </div>
          </div>
        </aside>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/client'
const CART_STORAGE_KEY = 'fe_marketplace_cart'

const items = ref([])
const isLoading = ref(false)
const isMutating = ref(false)
const errorMessage = ref('')

const voucher = ref('')
const voucherMessage = ref('')
const voucherSuccess = ref(false)

const activeVoucher = reactive({
  code: null,
  discount: 0
})

const cartSummary = reactive({
  subtotal: null,
  shipping_fee: null,
  discount_amount: null,
  total: null
})

onMounted(() => {
  fetchCart()
})

function fetchCart() {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const cartData = localStorage.getItem(CART_STORAGE_KEY)
    if (cartData) {
      const cartItems = JSON.parse(cartData)
      items.value = Array.isArray(cartItems) ? cartItems.map(normalizeItem) : []
    } else {
      items.value = []
    }
    resetVoucherState()
    updateSummary()
  } catch (err) {
    console.error('Không thể tải giỏ hàng', err)
    items.value = []
    errorMessage.value = 'Không thể tải dữ liệu giỏ hàng.'
    resetVoucherState()
    resetSummary()
  } finally {
    isLoading.value = false
  }
}

function updateSummary() {
  cartSummary.subtotal = items.value.reduce((sum, item) => sum + lineTotal(item), 0)
  cartSummary.shipping_fee = 0
  cartSummary.discount_amount = activeVoucher.discount
  cartSummary.total = cartSummary.subtotal + cartSummary.shipping_fee - cartSummary.discount_amount
}

function saveCart() {
  try {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(items.value))
    updateSummary()
    // Dispatch event to update cart count in header
    window.dispatchEvent(new CustomEvent('cart-updated'))
  } catch (err) {
    console.error('Không thể lưu giỏ hàng', err)
    errorMessage.value = 'Không thể lưu giỏ hàng.'
  }
}

function resetSummary() {
  cartSummary.subtotal = null
  cartSummary.shipping_fee = null
  cartSummary.discount_amount = null
  cartSummary.total = null
}

function resetVoucherState() {
  voucher.value = ''
  voucherMessage.value = ''
  voucherSuccess.value = false
  activeVoucher.code = null
  activeVoucher.discount = 0
}

function normalizeItem(raw) {
  // Normalize item từ localStorage
  return {
    id: raw.id,
    name: raw.name || 'Sản phẩm chưa đặt tên',
    price: Number(raw.price) || 0,
    quantity: Math.max(1, Number(raw.quantity) || 1),
    category: raw.category || '',
    line_total: null // Sẽ tính toán
  }
}

function lineTotal(item) {
  return Number.isFinite(item.line_total) ? item.line_total : item.price * item.quantity
}

const subTotal = computed(() => {
  if (Number.isFinite(cartSummary.subtotal)) return cartSummary.subtotal
  return items.value.reduce((sum, item) => sum + lineTotal(item), 0)
})

const shippingFee = computed(() => {
  if (Number.isFinite(cartSummary.shipping_fee)) return cartSummary.shipping_fee
  return 0
})

const discountAmount = computed(() => {
  if (Number.isFinite(cartSummary.discount_amount)) return cartSummary.discount_amount
  return activeVoucher.discount
})

const total = computed(() => {
  if (Number.isFinite(cartSummary.total)) return cartSummary.total
  return subTotal.value + shippingFee.value - discountAmount.value
})

function updateQty(item, delta) {
  const next = Math.max(1, item.quantity + delta)
  if (next === item.quantity) return
  item.quantity = next
  saveCart()
}

function setQty(item, val) {
  const parsed = parseInt(val, 10)
  if (!Number.isFinite(parsed) || parsed < 1) {
    item.quantity = 1
  } else {
    item.quantity = Math.min(parsed, 99)
  }
  saveCart()
}

function removeItem(item) {
  if (!item?.id) return
  const index = items.value.findIndex(i => i.id === item.id)
  if (index > -1) {
    items.value.splice(index, 1)
    saveCart()
    if (items.value.length === 0) {
      resetVoucherState()
    }
  }
}

function clearCart() {
  if (items.value.length === 0) return
  if (confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?')) {
    items.value = []
    localStorage.removeItem(CART_STORAGE_KEY)
    resetVoucherState()
    resetSummary()
    errorMessage.value = ''
  }
}

function applyVoucher() {
  const code = voucher.value.trim().toUpperCase()
  if (!code) {
    voucherMessage.value = 'Vui lòng nhập mã khuyến mại.'
    voucherSuccess.value = false
    return
  }

  // Demo voucher codes (có thể tích hợp API sau)
  const vouchers = {
    'FREESHIP': { discount: 0, message: 'Miễn phí vận chuyển (áp dụng khi có sản phẩm)' },
    'DISCOUNT10': { discount: subTotal.value * 0.1, message: 'Giảm 10% cho đơn hàng' },
    'DISCOUNT20': { discount: subTotal.value * 0.2, message: 'Giảm 20% cho đơn hàng' },
  }

  if (vouchers[code]) {
    activeVoucher.code = code
    activeVoucher.discount = vouchers[code].discount
    voucherSuccess.value = true
    voucherMessage.value = vouchers[code].message
    updateSummary()
  } else {
    voucherSuccess.value = false
    voucherMessage.value = 'Mã không hợp lệ hoặc đã hết hạn.'
    activeVoucher.code = null
    activeVoucher.discount = 0
    updateSummary()
  }
}

function formatCurrency(value) {
  return (Number(value) || 0).toLocaleString('vi-VN') + ' ₫'
}

function toNullableNumber(value) {
  const num = Number(value)
  return Number.isFinite(num) ? num : null
}

function proceedCheckout() {
  if (items.value.length === 0) {
    voucherMessage.value = 'Giỏ hàng đang trống, hãy thêm sản phẩm trước.'
    voucherSuccess.value = false
    return
  }
  // Chuyển đến trang checkout với giỏ hàng
  // Có thể tạo route checkout với cart mode
  alert('Chức năng thanh toán từ giỏ hàng đang được phát triển. Vui lòng thanh toán từng sản phẩm.')
}

function onImgError(event) {
  // Tránh vòng lặp vô hạn
  if (event.target.dataset.fallbackSet === 'true') {
    event.target.style.display = 'none'
    return
  }
  event.target.dataset.fallbackSet = 'true'
  event.target.onerror = null
}
</script>

<style scoped>
.cart-page {
  min-height: 70vh;
}
.thumb {
  width: 70px;
  height: 70px;
  object-fit: cover;
  border-radius: 10px;
  border: 1px solid #eee;
}
.qty-control {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 4px 8px;
  background: #f9fafb;
}
.qty-control input {
  width: 48px;
  border: none;
  background: transparent;
  text-align: center;
  font-weight: 600;
}
.qty-control input:focus {
  outline: none;
}
.loading-state,
.empty-state {
  border: 1px dashed #cbd5f5;
  border-radius: 18px;
  background: #f8fafc;
}
.loading-state .spinner-border {
  width: 3rem;
  height: 3rem;
}
.empty-icon {
  font-size: 48px;
}
.qty-col {
  width: 160px;
}
@media (max-width: 767.98px) {
  .thumb {
    width: 56px;
    height: 56px;
  }
  .qty-col {
    width: auto;
  }
  .qty-control input {
    width: 40px;
  }
}
</style>


