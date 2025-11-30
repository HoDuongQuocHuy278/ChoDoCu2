<template>
  <div class="cart-page container py-4">
    <header class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 mb-4">
      <div>
        <h2 class="fw-bold m-0">Giỏ hàng của bạn</h2>
        <p class="text-muted mb-1">Quản lý các sản phẩm đã thêm trước khi tiến hành thanh toán.</p>
        <p v-if="items.length" class="text-muted small mb-0">
          Đã chọn {{ selectedCount }} / {{ items.length }} sản phẩm
        </p>
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
                    <th scope="col" class="text-center select-col">
                      <input 
                        class="form-check-input"
                        type="checkbox"
                        :checked="selectAll"
                        @change="toggleSelectAll"
                      >
                    </th>
                    <th scope="col">Sản phẩm</th>
                    <th scope="col" class="text-center">Giá</th>
                    <th scope="col" class="text-center qty-col">Số lượng</th>
                    <th scope="col" class="text-center">Tổng</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in items" :key="item.id" :class="{ 'table-active': item.selected }">
                    <td class="text-center select-col">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        :checked="item.selected"
                        @change="toggleItemSelection(item, $event)"
                      >
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <img :src="item.image || fallbackImg" class="thumb" alt="" @error="onImgError($event, item)">
                        <div class="flex-grow-1">
                          <h6 class="mb-1 product-title">{{ item.name }}</h6>
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
                    <td class="text-end action-cell">
                      <button class="btn btn-link btn-outline-danger text-danger p-0" @click="removeItem(item)" :disabled="isMutating">Xóa</button>
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
                :disabled="!hasSelection || items.length === 0"
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

<script>
export default {
  data() {
    return {
      CART_STORAGE_KEY: 'fe_marketplace_cart',
      fallbackImg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiByeD0iMjQiIGZpbGw9IiNGRkZGRkYiIHN0cm9rZT0iI0VCRUVGMyIgc3Ryb2tlLXdpZHRoPSIyIi8+CjxwYXRoIGQ9Ik02MCA4MEg2NEw4MCAxMDZMMTAwIDc0TDEyMCAxMDRMMTQ0IDc0TDE2MCA5OUwxNjQgOTRMMTgwIDExMEgxODQiIHN0cm9rZT0iI0Q5REVERCIgc3Ryb2tlLXdpZHRoPSIzIiBzdHJva2UtbGluZWNhcD0icm91bmQiIGZpbGw9Im5vbmUiLz4KPHBhdGggZD0iTTQ0IDE0MEgxNTYiIHN0cm9rZT0iI0Q5REVERCIgc3Ryb2tlLXdpZHRoPSIyLjUiIGZpbGw9Im5vbmUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIvPgo8dGV4dCB4PSI1MCIgeT0iMTYwIiBmaWxsPSIjOURBMEJFIiBmb250LXNpemU9IjE1IiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiPkNoa+G6oW4gY8awIGnhu4duPC90ZXh0Pgo8L3N2Zz4=',
      items: [],
      isLoading: false,
      isMutating: false,
      errorMessage: '',
      voucher: '',
      voucherMessage: '',
      voucherSuccess: false,
      activeVoucher: {
        code: null,
        discount: 0
      },
      cartSummary: {
        subtotal: null,
        shipping_fee: null,
        discount_amount: null,
        total: null
      },
      selectAll: true
    }
  },
  computed: {
    selectedItems() {
      return this.items.filter((item) => item.selected)
    },
    hasSelection() {
      return this.selectedItems.length > 0
    },
    selectedCount() {
      return this.selectedItems.length
    },
    subTotal() {
      if (Number.isFinite(this.cartSummary.subtotal)) return this.cartSummary.subtotal
      return this.selectedItems.reduce((sum, item) => sum + this.lineTotal(item), 0)
    },
    shippingFee() {
      if (Number.isFinite(this.cartSummary.shipping_fee)) return this.cartSummary.shipping_fee
      return 0
    },
    discountAmount() {
      if (Number.isFinite(this.cartSummary.discount_amount)) return this.cartSummary.discount_amount
      return this.activeVoucher.discount
    },
    total() {
      if (Number.isFinite(this.cartSummary.total)) return this.cartSummary.total
      return this.subTotal + this.shippingFee - this.discountAmount
    }
  },
  mounted() {
    this.fetchCart()
  },
  methods: {
    fetchCart() {
      this.isLoading = true
      this.errorMessage = ''
      try {
        const cartData = localStorage.getItem(this.CART_STORAGE_KEY)
        if (cartData) {
          const cartItems = JSON.parse(cartData)
          this.items = Array.isArray(cartItems) ? cartItems.map(this.normalizeItem) : []
        } else {
          this.items = []
        }
        this.selectAll = this.items.length > 0
        this.resetVoucherState()
        this.updateSummary()
      } catch (err) {
        console.error('Không thể tải giỏ hàng', err)
        this.items = []
        this.errorMessage = 'Không thể tải dữ liệu giỏ hàng.'
        this.resetVoucherState()
        this.resetSummary()
      } finally {
        this.isLoading = false
      }
    },
    
    updateSummary() {
      const current = this.selectedItems
      this.cartSummary.subtotal = current.reduce((sum, item) => sum + this.lineTotal(item), 0)
      this.cartSummary.shipping_fee = 0
      this.cartSummary.discount_amount = this.activeVoucher.discount
      this.cartSummary.total = this.cartSummary.subtotal + this.cartSummary.shipping_fee - this.cartSummary.discount_amount
    },
    
    saveCart() {
      try {
        const sanitized = this.items.map(({ selected, ...rest }) => rest)
        localStorage.setItem(this.CART_STORAGE_KEY, JSON.stringify(sanitized))
        this.updateSummary()
        window.dispatchEvent(new CustomEvent('cart-updated'))
      } catch (err) {
        console.error('Không thể lưu giỏ hàng', err)
        this.errorMessage = 'Không thể lưu giỏ hàng.'
      }
    },
    
    resetSummary() {
      this.cartSummary.subtotal = null
      this.cartSummary.shipping_fee = null
      this.cartSummary.discount_amount = null
      this.cartSummary.total = null
    },
    
    resetVoucherState() {
      this.voucher = ''
      this.voucherMessage = ''
      this.voucherSuccess = false
      this.activeVoucher.code = null
      this.activeVoucher.discount = 0
    },
    
    normalizeItem(raw) {
      return {
        id: raw.id,
        name: raw.name || 'Sản phẩm chưa đặt tên',
        price: Number(raw.price) || 0,
        quantity: Math.max(1, Number(raw.quantity) || 1),
        category: raw.category || '',
        image: raw.image || this.fallbackImg,
        line_total: null
      }
    },
    
    lineTotal(item) {
      return Number.isFinite(item.line_total) ? item.line_total : item.price * item.quantity
    },
    
    updateQty(item, delta) {
      const next = Math.max(1, item.quantity + delta)
      if (next === item.quantity) return
      item.quantity = next
      this.saveCart()
    },
    
    setQty(item, val) {
      const parsed = parseInt(val, 10)
      if (!Number.isFinite(parsed) || parsed < 1) {
        item.quantity = 1
      } else {
        item.quantity = Math.min(parsed, 99)
      }
      this.saveCart()
    },
    
    removeItem(item) {
      if (!item?.id) return
      const index = this.items.findIndex(i => i.id === item.id)
      if (index > -1) {
        this.items.splice(index, 1)
        this.saveCart()
        if (this.items.length === 0) {
          this.resetVoucherState()
        }
        this.selectAll = this.items.length > 0 && this.items.every((itm) => itm.selected)
      }
    },
    
    clearCart() {
      if (this.items.length === 0) return
      if (confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?')) {
        this.items = []
        localStorage.removeItem(this.CART_STORAGE_KEY)
        this.resetVoucherState()
        this.resetSummary()
        this.errorMessage = ''
        this.selectAll = false
        window.dispatchEvent(new CustomEvent('cart-updated'))
      }
    },
    
    applyVoucher() {
      const code = this.voucher.trim().toUpperCase()
      if (!code) {
        this.voucherMessage = 'Vui lòng nhập mã khuyến mại.'
        this.voucherSuccess = false
        return
      }

      const vouchers = {
        'FREESHIP': { discount: 0, message: 'Miễn phí vận chuyển (áp dụng khi có sản phẩm)' },
        'DISCOUNT10': { discount: this.subTotal * 0.1, message: 'Giảm 10% cho đơn hàng' },
        'DISCOUNT20': { discount: this.subTotal * 0.2, message: 'Giảm 20% cho đơn hàng' },
      }

      if (vouchers[code]) {
        this.activeVoucher.code = code
        this.activeVoucher.discount = vouchers[code].discount
        this.voucherSuccess = true
        this.voucherMessage = vouchers[code].message
        this.updateSummary()
      } else {
        this.voucherSuccess = false
        this.voucherMessage = 'Mã không hợp lệ hoặc đã hết hạn.'
        this.activeVoucher.code = null
        this.activeVoucher.discount = 0
        this.updateSummary()
      }
    },
    
    formatCurrency(value) {
      return (Number(value) || 0).toLocaleString('vi-VN') + ' ₫'
    },
    
    proceedCheckout() {
      if (this.items.length === 0) {
        this.voucherMessage = 'Giỏ hàng đang trống, hãy thêm sản phẩm trước.'
        this.voucherSuccess = false
        return
      }

      const selected = this.selectedItems
      if (!selected.length) {
        this.voucherMessage = 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.'
        this.voucherSuccess = false
        return
      }

      if (selected.length === 1) {
        this.$router.push({
          name: 'checkout',
          query: {
            product_id: selected[0].id,
            quantity: selected[0].quantity,
            from_cart: '1'
          }
        })
        return
      }

      const payload = selected.map(({ id, name, price, quantity, image }) => ({
        id,
        name,
        price,
        quantity,
        image
      }))
      localStorage.setItem('cart_selected_items', JSON.stringify(payload))
      this.$router.push({
        name: 'checkout',
        query: {
          cart_mode: '1',
          from_cart: '1'
        }
      })
    },
    
    onImgError(event, item) {
      if (event.target.dataset.fallbackSet === 'true') {
        event.target.style.display = 'none'
        return
      }
      event.target.dataset.fallbackSet = 'true'
      event.target.src = this.fallbackImg
      if (item) {
        item.image = this.fallbackImg
      }
      event.target.onerror = null
    },
    
    toggleSelectAll(event) {
      const checked = event?.target?.checked ?? this.selectAll
      this.selectAll = checked
      this.items.forEach((item) => {
        item.selected = checked
      })
      this.updateSummary()
    },
    
    toggleItemSelection(item, event) {
      const checked = event?.target?.checked ?? !item.selected
      item.selected = checked
      const total = this.items.length
      this.selectAll = total > 0 && this.items.every((itm) => itm.selected)
      this.updateSummary()
    }
  }
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
.select-col {
  width: 40px;
}
.select-col .form-check-input {
  cursor: pointer;
}
.product-title {
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
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
.action-cell .btn-outline-success {
  font-weight: 600;
  width: 70px;
}
.action-cell .btn-link {
  display: block;
  width: 115px;
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
