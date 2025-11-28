<template>
  <div class="checkout-page">
    <div v-if="cartMode">
      <div v-if="isLoading" class="loading-container">
        <div class="text-center py-5">
          <div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Đang tải...</span>
          </div>
          <p class="text-muted">Đang tải danh sách sản phẩm đã chọn...</p>
        </div>
      </div>

      <div v-else-if="orderResult" class="order-success-container">
        <div class="container py-5">
          <div class="success-card text-center" v-if="orderResult?.cart_mode">
            <div class="success-icon">
              <i class='bx bx-check-circle'></i>
            </div>
            <h2 class="success-title">Đã tạo {{ orderResult.orders.length }} đơn hàng</h2>
            <p class="success-message">Cảm ơn bạn đã thanh toán nhiều sản phẩm tại Chợ Đồ Cũ</p>

            <div class="cart-order-summary my-4">
              <div 
                v-for="order in orderResult.orders" 
                :key="order.id" 
                class="cart-order-item d-flex justify-content-between align-items-center border rounded-3 p-3 mb-2"
              >
                <div class="text-start">
                  <strong>Đơn #{{ order.ma_don_hang }}</strong>
                  <p class="text-muted small mb-0">{{ order.san_pham?.ten_san_pham || 'Sản phẩm' }}</p>
                </div>
                <span class="fw-semibold">{{ formatCurrency(order.tong_tien) }}</span>
              </div>
            </div>

            <div class="border-top pt-3 mt-3">
              <p class="mb-1">
                Tổng tiền: <strong>{{ formatCurrency(orderResult.total_amount) }}</strong>
              </p>
              <p class="text-muted small mb-0">
                Phương thức thanh toán: {{ getPaymentLabel(orderResult.payment_method || form.payment_method) }}
              </p>
            </div>

            <div v-if="orderResult.payment_method !== 'cash' && cartPaymentResults.length" class="cart-payment-followup mt-4">
              <h6 class="fw-semibold mb-3">Hoàn tất thanh toán online</h6>
              <div 
                v-for="result in cartPaymentResults" 
                :key="`cart-success-${result.orders?.length || Math.random()}`"
                class="payment-result-card border rounded-4 p-3 mb-3"
              >
                <div class="mb-3">
                  <p class="text-muted small mb-1">Tổng hóa đơn cho {{ orderResult.orders?.length || 0 }} đơn hàng</p>
                  <div class="d-flex justify-content-between align-items-center">
                    <strong class="fs-5">Tổng tiền cần thanh toán</strong>
                    <span class="fw-bold fs-4 text-success">{{ formatCurrency(orderResult.total_amount) }}</span>
                  </div>
                </div>
                <div v-if="orderResult.payment_method === 'vnpay'">
                  <a :href="result.paymentUrl" target="_blank" class="btn btn-primary w-100 btn-lg">
                    <i class='bx bx-credit-card me-1'></i> Thanh toán VNPAY ngay
                  </a>
                  <p class="text-muted small mt-2 mb-0 text-center">
                    <i class='bx bx-info-circle me-1'></i> Bạn sẽ thanh toán tổng hóa đơn cho tất cả {{ orderResult.orders?.length || 0 }} đơn hàng
                  </p>
                </div>
                <div v-else class="d-flex flex-column flex-sm-row gap-3 align-items-center">
                  <div v-if="result.paymentData?.qr_code" class="qr-block mb-0">
                    <img :src="result.paymentData.qr_code" alt="QR MBBank">
                  </div>
                  <div class="flex-grow-1 w-100">
                    <a 
                      :href="result.paymentUrl || result.paymentData?.payment_url" 
                      target="_blank" 
                      class="btn btn-primary w-100 mb-2 btn-lg"
                    >
                      <i class='bx bx-mobile me-1'></i> Mở app MBBank để thanh toán
                    </a>
                    <div v-if="result.paymentData?.transaction_id" class="alert alert-info soft mb-0">
                      <small>Mã giao dịch: {{ result.paymentData.transaction_id }}</small>
                    </div>
                    <p class="text-muted small mt-2 mb-0 text-center">
                      <i class='bx bx-info-circle me-1'></i> Quét QR hoặc mở app để thanh toán tổng hóa đơn
                    </p>
                  </div>
                </div>
              </div>
              <div class="alert alert-info soft">
                <h6 class="fw-semibold mb-2">Danh sách đơn hàng trong hóa đơn:</h6>
                <ul class="mb-0 small">
                  <li v-for="order in orderResult.orders" :key="order.id">
                    Đơn #{{ order.ma_don_hang }} - {{ formatCurrency(order.tong_tien) }}
                  </li>
                </ul>
              </div>
              <p class="text-muted small mb-0 mt-3">Sau khi thanh toán thành công, tất cả {{ orderResult.orders?.length || 0 }} đơn hàng sẽ được cập nhật trạng thái thanh toán.</p>
            </div>
            <div v-else class="alert alert-success soft mt-4">
              <i class='bx bx-money me-1'></i> Bạn sẽ thanh toán trực tiếp khi nhận hàng.
            </div>

            <router-link to="/gio-hang" class="btn btn-outline-secondary mt-4">
              Quay lại giỏ hàng
            </router-link>
          </div>
        </div>
      </div>

      <div v-else-if="errorMessage && !cartItems.length" class="error-container">
        <div class="container py-5">
          <div class="alert alert-danger">
            <h5>Không thể tải sản phẩm đã chọn</h5>
            <p>{{ errorMessage }}</p>
            <router-link to="/gio-hang" class="btn btn-primary">Quay lại giỏ hàng</router-link>
          </div>
        </div>
      </div>

      <div v-else class="container py-4 cart-checkout-wrapper">
        <div class="cart-hero card border-0 shadow-sm mb-4">
          <div class="cart-hero-content d-flex flex-wrap gap-3 align-items-center">
            <div class="cart-hero-icon">
              <i class='bx bx-cart'></i>
            </div>
            <div class="flex-grow-1">
              <p class="cart-phase-label mb-1 text-uppercase small fw-semibold">Thanh toán nhiều sản phẩm</p>
              <h2 class="fw-bold mb-1">Hoàn tất đơn hàng trong 3 bước</h2>
              <p class="text-muted mb-0">Đảm bảo an toàn cho {{ cartItems.length }} sản phẩm bạn đã chọn. Tất cả thông tin được mã hóa và bảo mật.</p>
            </div>
            <router-link to="/gio-hang" class="btn btn-outline-light text-white">
              <i class='bx bx-arrow-back me-1'></i> Quay lại giỏ hàng
            </router-link>
          </div>
          <div class="cart-progress mt-4">
            <div class="progress-step" :class="{ active: cartStep >= 1 }">
              <div class="step-circle"><span>1</span></div>
              <p>Kiểm tra sản phẩm</p>
            </div>
            <div class="progress-line" :class="{ active: cartStep > 1 }"></div>
            <div class="progress-step" :class="{ active: cartStep >= 2 }">
              <div class="step-circle"><span>2</span></div>
              <p>Thông tin nhận hàng</p>
            </div>
            <div class="progress-line" :class="{ active: cartStep > 2 }"></div>
            <div class="progress-step" :class="{ active: cartStep >= 3 }">
              <div class="step-circle"><span>3</span></div>
              <p>Xác nhận đơn</p>
            </div>
          </div>
        </div>

        <div class="row g-4 align-items-start">
          <div class="col-xl-8">
            <div v-if="cartStep === 1" class="cart-step-panel card border-0 shadow-sm">
              <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                  <h5 class="mb-1 fw-bold">Bước 1 · Kiểm tra sản phẩm</h5>
                  <p class="text-muted mb-0">Đảm bảo các sản phẩm chính xác trước khi tiếp tục</p>
                </div>
                <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">
                  <i class='bx bx-shield-quarter me-1'></i> Được kiểm duyệt
                </span>
              </div>
              <div class="card-body cart-items-list">
                <div 
                  v-for="item in cartItems" 
                  :key="item.id" 
                  class="cart-item-card rounded-4 border d-flex flex-column flex-md-row gap-3 p-3 mb-3"
                >
                  <div class="cart-item-left d-flex gap-3 flex-grow-1">
                    <div class="cart-item-thumb">
                      <img :src="item.image || getDefaultProductImage()" :alt="item.name" @error="onImgError">
                    </div>
                    <div class="cart-item-info flex-grow-1">
                      <div class="d-flex flex-wrap justify-content-between gap-2">
                        <div>
                          <p class="text-muted mb-1 small text-uppercase fw-semibold">#{{ item.id }}</p>
                          <h6 class="fw-semibold mb-0">{{ item.name }}</h6>
                        </div>
                        <div class="text-end">
                          <p class="text-muted small mb-1">Đơn giá</p>
                          <p class="fw-semibold mb-0 text-primary">{{ formatCurrency(item.price) }}</p>
                        </div>
                      </div>
                      <div class="cart-item-meta mt-3">
                        <span class="meta-badge">
                          <i class='bx bx-package me-1'></i> Số lượng: <strong>{{ item.quantity }}</strong>
                        </span>
                        <span class="meta-badge">
                          <i class='bx bx-store-alt me-1'></i> Giao COD trong 2-4 ngày
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="cart-item-total text-md-end">
                    <p class="text-muted mb-1 small">Thành tiền</p>
                    <p class="fw-bold fs-5 text-success mb-0">{{ formatCurrency(item.price * item.quantity) }}</p>
                  </div>
                </div>
                <p v-if="!cartItems.length" class="text-muted mb-0 text-center py-4">Không có sản phẩm nào được chọn.</p>
              </div>
              <div class="step-actions d-flex justify-content-end gap-2 border-top pt-3 px-3 pb-3">
                <router-link to="/gio-hang" class="btn btn-outline-secondary">
                  <i class='bx bx-arrow-back me-1'></i> Quay lại giỏ hàng
                </router-link>
                <button class="btn btn-primary" type="button" @click="nextCartStep" :disabled="!cartItems.length">
                  Tiếp tục bước 2
                </button>
              </div>
            </div>

            <div v-else-if="cartStep === 2" class="cart-step-panel card border-0 shadow-sm">
              <div class="card-header bg-white border-0">
                <h5 class="fw-bold mb-1">Bước 2 · Thông tin & thanh toán</h5>
                <p class="text-muted mb-0 small">Nhập thông tin nhận hàng và chọn phương thức thanh toán</p>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Họ và tên *</label>
                    <div class="input-icon">
                      <i class='bx bx-user'></i>
                      <input 
                        type="text" 
                        class="form-control" 
                        v-model.trim="form.buyer_name" 
                        :class="{ 'is-invalid': errors.buyer_name }"
                        placeholder="Nguyễn Văn A"
                      >
                    </div>
                    <div v-if="errors.buyer_name" class="invalid-feedback">
                      {{ errors.buyer_name }}
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Số điện thoại *</label>
                    <div class="input-icon">
                      <i class='bx bx-phone'></i>
                      <input 
                        type="tel" 
                        class="form-control" 
                        v-model.trim="form.buyer_phone" 
                        :class="{ 'is-invalid': errors.buyer_phone }"
                        placeholder="0901 234 567"
                      >
                    </div>
                    <div v-if="errors.buyer_phone" class="invalid-feedback">
                      {{ errors.buyer_phone }}
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <div class="input-icon">
                      <i class='bx bx-envelope'></i>
                      <input 
                        type="email" 
                        class="form-control" 
                        v-model.trim="form.buyer_email"
                        placeholder="email@example.com"
                      >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Địa chỉ nhận hàng</label>
                    <div class="input-icon">
                      <i class='bx bx-map'></i>
                      <input 
                        type="text" 
                        class="form-control" 
                        v-model.trim="form.shipping_address"
                        placeholder="Số nhà, phường/xã, quận/huyện, tỉnh/thành phố"
                      >
                    </div>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Ghi chú</label>
                    <textarea 
                      class="form-control"
                      rows="3"
                      v-model.trim="form.notes"
                      placeholder="Yêu cầu giao hàng (ví dụ: gọi trước khi giao...)"
                    ></textarea>
                  </div>
                </div>
                <div class="cart-payment-group mt-4">
                  <h6 class="fw-semibold mb-2">Phương thức thanh toán</h6>
                  <p class="text-muted small mb-3">Bạn có thể thanh toán online (VNPAY, MBBANK) hoặc COD.</p>
                  <div class="payment-options">
                    <button
                      v-for="option in paymentOptions"
                      :key="option.value"
                      type="button"
                      class="payment-option-card"
                      :class="{ active: form.payment_method === option.value }"
                      @click="form.payment_method = option.value"
                    >
                      <div class="payment-option-icon" :class="{
                        'icon-vnpay': option.value === 'vnpay',
                        'icon-mbbank': option.value === 'mbbank',
                        'icon-cash': option.value === 'cash'
                      }">
                        <i :class="option.icon"></i>
                      </div>
                      <div class="payment-option-content">
                        <div class="d-flex align-items-center gap-2">
                          <span class="payment-option-title">{{ option.label }}</span>
                          <span v-if="option.badge" class="badge bg-success-subtle text-success rounded-pill">{{ option.badge }}</span>
                        </div>
                        <p class="payment-option-desc text-muted mb-0">{{ option.desc }}</p>
                      </div>
                    </button>
                  </div>
                  <div v-if="form.payment_method === 'cash'" class="alert alert-success soft mt-3">
                    <i class='bx bx-money me-1'></i> Bạn sẽ thanh toán trực tiếp với nhân viên giao hàng.
                  </div>
                  <div v-else class="alert alert-info soft mt-3">
                    <i class='bx bx-info-circle me-1'></i> Sau khi xác nhận, chúng tôi sẽ tạo link/QR thanh toán cho từng đơn hàng của bạn.
                  </div>
                </div>
              </div>
              <div class="step-actions d-flex justify-content-between gap-2 border-top pt-3 px-3 pb-3">
                <button class="btn btn-outline-secondary" type="button" @click="prevCartStep">
                  <i class='bx bx-chevron-left me-1'></i> Bước 1
                </button>
                <button class="btn btn-primary" type="button" @click="nextCartStep">
                  Tiếp tục bước 3
                </button>
              </div>
            </div>

            <div v-else class="cart-step-panel card border-0 shadow-sm">
              <div class="card-header bg-white border-0">
                <h5 class="fw-bold mb-1">Bước 3 · Kiểm tra & xác nhận</h5>
                <p class="text-muted mb-0 small">Hãy kiểm tra lại trước khi xác nhận thanh toán COD</p>
              </div>
              <div class="card-body">
                <div class="border rounded-4 p-3 mb-3 bg-light-subtle">
                  <h6 class="fw-semibold mb-3">Thông tin giao hàng</h6>
                  <dl class="row mb-0 small">
                    <dt class="col-sm-3 text-muted">Người nhận</dt>
                    <dd class="col-sm-9 fw-semibold">{{ form.buyer_name || 'Chưa cung cấp' }}</dd>
                    <dt class="col-sm-3 text-muted">Số điện thoại</dt>
                    <dd class="col-sm-9 fw-semibold">{{ form.buyer_phone || 'Chưa cung cấp' }}</dd>
                    <dt class="col-sm-3 text-muted">Địa chỉ</dt>
                    <dd class="col-sm-9">{{ form.shipping_address || 'Chưa cung cấp' }}</dd>
                    <dt class="col-sm-3 text-muted">Ghi chú</dt>
                    <dd class="col-sm-9">{{ form.notes || 'Không có' }}</dd>
                  </dl>
                </div>

                <div class="cart-review-list">
                  <h6 class="fw-semibold mb-3">Danh sách sản phẩm</h6>
                  <div 
                    v-for="item in cartItems" 
                    :key="`confirm-${item.id}`" 
                    class="cart-review-item border rounded-4 d-flex justify-content-between p-3 mb-2"
                  >
                    <div class="text-truncate pe-3">
                      <p class="mb-1 fw-semibold">{{ item.name }}</p>
                      <small class="text-muted">x{{ item.quantity }} • {{ formatCurrency(item.price) }}</small>
                    </div>
                    <div class="text-end">
                      <p class="text-muted mb-1 small">Thành tiền</p>
                      <p class="fw-semibold mb-0 text-success">{{ formatCurrency(item.price * item.quantity) }}</p>
                    </div>
                  </div>
                </div>
                <div v-if="form.payment_method !== 'cash'" class="cart-online-payments mt-4">
                  <h6 class="fw-semibold mb-2">Thanh toán online</h6>
                  <p class="text-muted small mb-3">Bạn sẽ thanh toán tổng hóa đơn cho tất cả {{ cartItems.length }} đơn hàng trong một giao dịch.</p>
                  <div v-if="cartPaymentResults.length" class="payment-result-list">
                    <div
                      v-for="result in cartPaymentResults"
                      :key="`payment-${result.orders?.length || Math.random()}`"
                      class="payment-result-card border rounded-4 p-3 mb-3"
                    >
                      <div class="mb-3">
                        <p class="text-muted small mb-1">Tổng hóa đơn cho {{ cartItems.length }} đơn hàng</p>
                        <div class="d-flex justify-content-between align-items-center">
                          <strong class="fs-5">Tổng tiền</strong>
                          <span class="fw-bold fs-4 text-success">{{ formatCurrency(cartTotals.total) }}</span>
                        </div>
                      </div>
                      <div v-if="form.payment_method === 'vnpay'">
                        <a :href="result.paymentUrl" target="_blank" class="btn btn-primary w-100 btn-lg">
                          <i class='bx bx-credit-card me-1'></i> Thanh toán VNPAY ngay
                        </a>
                        <p class="text-muted small mb-0 mt-2 text-center">Link thanh toán cho tổng hóa đơn.</p>
                      </div>
                      <div v-else-if="form.payment_method === 'mbbank'">
                        <div v-if="result.paymentData?.qr_code" class="qr-block mb-3">
                          <img :src="result.paymentData.qr_code" alt="QR MBBank">
                        </div>
                        <div class="d-flex flex-column flex-sm-row gap-2">
                          <a 
                            v-if="result.paymentUrl || result.paymentData?.payment_url" 
                            :href="result.paymentUrl || result.paymentData.payment_url" 
                            target="_blank" 
                            class="btn btn-primary flex-grow-1"
                          >
                            <i class='bx bx-mobile me-1'></i> Mở app MBBank
                          </a>
                          <div v-if="result.paymentData?.transaction_id" class="alert alert-info soft flex-grow-1 mb-0">
                            <small>Mã giao dịch: {{ result.paymentData.transaction_id }}</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="alert alert-warning soft">
                    <i class='bx bx-time-five me-1'></i> Bấm "Xác nhận" để tạo link/QR thanh toán cho tổng hóa đơn.
                  </div>
                </div>
                <div v-else class="alert alert-success soft mt-4">
                  <i class='bx bx-money me-1'></i> Bạn sẽ thanh toán trực tiếp với nhân viên giao hàng khi nhận sản phẩm.
                </div>
              </div>
              <div class="step-actions d-flex justify-content-between gap-2 border-top pt-3 px-3 pb-3">
                <button class="btn btn-outline-secondary" type="button" @click="prevCartStep">
                  <i class='bx bx-chevron-left me-1'></i> Bước 2
                </button>
                <button 
                  class="btn btn-success btn-lg" 
                  type="button"
                  @click="submitCartOrder"
                  :disabled="submitting || !cartItems.length"
                >
                  <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                  <span v-else><i class='bx bx-check me-1'></i> Xác nhận đặt {{ cartItems.length }} đơn</span>
                </button>
              </div>
            </div>
          </div>

          <div class="col-xl-4">
            <div class="cart-summary-card card border-0 shadow-sm mb-4">
              <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <h5 class="fw-bold mb-0">Tổng quan thanh toán</h5>
                  <span class="badge rounded-pill bg-primary-subtle text-primary text-uppercase">
                    <i class='bx bx-bell me-1'></i> {{ getPaymentLabel(form.payment_method) }}
                  </span>
                </div>
                <div class="summary-line">
                  <span>Tạm tính</span>
                  <span class="fw-semibold">{{ formatCurrency(cartTotals.subtotal) }}</span>
                </div>
                <div class="summary-line">
                  <span>Phí vận chuyển</span>
                  <span class="text-success fw-semibold">Miễn phí</span>
                </div>
                <div class="summary-line">
                  <span>Giảm giá</span>
                  <span class="text-danger">-{{ formatCurrency(cartTotals.discount) }}</span>
                </div>
                <div class="summary-total mt-3 border-top pt-3 d-flex justify-content-between align-items-center">
                  <div>
                    <p class="text-muted mb-1 small">Thành tiền</p>
                    <h3 class="mb-0 text-success">{{ formatCurrency(cartTotals.total) }}</h3>
                  </div>
                  <div class="text-end">
                    <p class="text-muted mb-1 small">{{ totalCartQuantity }} sản phẩm</p>
                    <p class="text-muted small mb-0">{{ getPaymentLabel(form.payment_method) }}</p>
                  </div>
                </div>
                <div class="alert alert-info soft mt-3">
                  <i class='bx bx-info-circle me-1'></i> Các đơn sẽ được phân tách theo từng sản phẩm để hệ thống xử lý nhanh chóng.
                </div>
              </div>
            </div>

            <div class="cart-info-card card border-0 shadow-sm">
              <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                  <div class="cart-info-icon">
                    <i class='bx bx-shield'></i>
                  </div>
                  <div>
                    <h6 class="fw-bold mb-1">Chính sách bảo vệ</h6>
                    <p class="text-muted small mb-0">Ưu tiên kiểm tra hàng trước khi thanh toán. Miễn phí đổi trả trong 48 giờ nếu không đúng mô tả.</p>
                  </div>
                </div>
                <ul class="list-unstyled cart-info-list text-muted small mb-0">
                  <li><i class='bx bx-check-circle text-success me-1'></i> Theo dõi từng đơn trong mục Đơn mua</li>
                  <li><i class='bx bx-check-circle text-success me-1'></i> Chuẩn bị tiền mặt đủ khi nhận hàng</li>
                  <li><i class='bx bx-check-circle text-success me-1'></i> Hỗ trợ khách hàng 24/7</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <template v-else>
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
    </template>
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
const cartMode = ref(false)
const cartStep = ref(1)
const cartItems = ref([])
const cartPaymentResults = ref([])
const CART_SELECTION_KEY = 'cart_selected_items'
const CART_STORAGE_KEY = 'fe_marketplace_cart'
const submitting = ref(false)
const currentStep = ref(1)
const paymentUrl = ref(null)
const paymentData = ref(null)
const processingPayment = ref(false)
const cartTotals = computed(() => {
  const subtotal = cartItems.value.reduce((sum, item) => sum + (Number(item.price) * Math.max(1, Number(item.quantity) || 1)), 0)
  return {
    subtotal,
    shipping: 0,
    discount: 0,
    total: subtotal
  }
})
const totalCartQuantity = computed(() => cartItems.value.reduce((sum, item) => sum + Math.max(1, Number(item.quantity) || 1), 0))
const cartIsOnlinePayment = computed(() => cartMode.value && form.payment_method !== 'cash')
const shouldUseCartMode = (query = route.query) => query.cart_mode === '1' || query.from_cart === '1'

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

const loadCartSelection = () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const stored = localStorage.getItem(CART_SELECTION_KEY)
    if (!stored) {
      errorMessage.value = 'Không tìm thấy sản phẩm đã chọn từ giỏ hàng. Vui lòng quay lại giỏ hàng và chọn sản phẩm.'
      cartItems.value = []
      return
    }
    const parsed = JSON.parse(stored)
    if (!Array.isArray(parsed) || !parsed.length) {
      errorMessage.value = 'Danh sách sản phẩm đã chọn không hợp lệ.'
      cartItems.value = []
      return
    }
    cartItems.value = parsed.map((item) => ({
      id: item.id,
      name: item.name || 'Sản phẩm chưa đặt tên',
      price: Number(item.price) || 0,
      quantity: Math.max(1, Number(item.quantity) || 1),
      image: item.image || getDefaultProductImage()
    }))
    form.payment_method = 'cash'
    cartStep.value = 1
  } catch (err) {
    console.error('Không thể tải danh sách sản phẩm đã chọn', err)
    errorMessage.value = 'Không thể tải danh sách sản phẩm đã chọn. Vui lòng thử lại.'
    cartItems.value = []
  } finally {
    isLoading.value = false
  }
}

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

const submitCartOrder = async () => {
  if (!cartItems.value.length) {
    errorMessage.value = 'Không có sản phẩm nào được chọn để thanh toán.'
    return
  }

  if (!validateStep1()) {
    return
  }

  submitting.value = true
  errorMessage.value = ''
  orderResult.value = null
  cartPaymentResults.value = []

  try {
    const token = localStorage.getItem('key_client')
    const headers = token ? { 'Authorization': `Bearer ${token}` } : {}
    const createdOrders = []
    const selectedMethod = form.payment_method || 'cash'

    // Bước 1: Tạo tất cả đơn hàng trước
    for (const item of cartItems.value) {
      const payload = {
        product_id: item.id,
        quantity: Math.max(1, Number(item.quantity) || 1),
        payment_method: selectedMethod,
        buyer_name: form.buyer_name,
        buyer_email: form.buyer_email || null,
        buyer_phone: form.buyer_phone || null,
        shipping_address: form.shipping_address || null,
        notes: form.notes || null,
      }

      const { data } = await axios.post(`${API_BASE_URL}/don-hang`, payload, { headers })
      const orderRecord = data?.data || data
      createdOrders.push(orderRecord)
    }

    // Bước 2: Tính tổng tiền
    const totalAmount = createdOrders.reduce((sum, order) => sum + Number(order?.tong_tien || 0), 0)

    // Bước 3: Nếu là thanh toán online, chỉ tạo 1 giao dịch thanh toán cho tổng hóa đơn
    let paymentResult = null
    if (selectedMethod === 'vnpay' || selectedMethod === 'mbbank') {
      try {
        const orderIds = createdOrders.map(order => order.id)
        paymentResult = await requestCartPayment(orderIds, totalAmount, selectedMethod)
      } catch (paymentErr) {
        console.error('Không thể tạo thanh toán cho tổng hóa đơn', paymentErr)
        throw paymentErr
      }
    }

    orderResult.value = {
      cart_mode: true,
      orders: createdOrders,
      total_amount: totalAmount,
      payment_method: selectedMethod
    }
    if (paymentResult) {
      cartPaymentResults.value = [{
        orders: createdOrders,
        paymentUrl: paymentResult.paymentUrl,
        paymentData: paymentResult.paymentData
      }]
    }
    cartStep.value = 3

    // Xóa các sản phẩm đã thanh toán khỏi giỏ hàng
    try {
      const storedCart = JSON.parse(localStorage.getItem(CART_STORAGE_KEY) || '[]')
      const remaining = storedCart.filter((cartItem) => !cartItems.value.some((selected) => selected.id === cartItem.id))
      localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(remaining))
      localStorage.removeItem(CART_SELECTION_KEY)
      window.dispatchEvent(new CustomEvent('cart-updated'))
    } catch (err) {
      console.warn('Không thể cập nhật giỏ hàng sau khi thanh toán', err)
    }

    window.scrollTo({ top: 0, behavior: 'smooth' })
  } catch (err) {
    console.error(err)
    errorMessage.value = err?.response?.data?.message || 'Không thể tạo đơn hàng cho các sản phẩm đã chọn.'
  } finally {
    submitting.value = false
  }
}

const submitOrder = async () => {
  if (cartMode.value) {
    await submitCartOrder()
    return
  }

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

const requestPayment = async (orderData, paymentMethod) => {
  if (!orderData) throw new Error('Thiếu thông tin đơn hàng để tạo thanh toán')

  const token = localStorage.getItem('key_client')
  const headers = token ? { 'Authorization': `Bearer ${token}` } : {}

  const paymentPayload = {
    order_id: orderData.id,
    amount: orderData.tong_tien,
    order_info: `Thanh toán đơn hàng #${orderData.ma_don_hang}`,
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

  if (!data?.status || !data?.data) {
    throw new Error(data?.message || 'Phản hồi từ server không hợp lệ')
  }

  const result = {
    paymentUrl: null,
    paymentData: null
  }

  if (paymentMethod === 'vnpay') {
    result.paymentUrl = data.data.payment_url || data.data
  } else if (paymentMethod === 'mbbank') {
    result.paymentData = data.data
    if (data.data.payment_url) {
      result.paymentUrl = data.data.payment_url
    }
  }

  return result
}

const requestCartPayment = async (orderIds, totalAmount, paymentMethod) => {
  if (!orderIds || !Array.isArray(orderIds) || orderIds.length === 0) {
    throw new Error('Thiếu danh sách đơn hàng để tạo thanh toán')
  }

  const token = localStorage.getItem('key_client')
  const headers = token ? { 'Authorization': `Bearer ${token}` } : {}

  const paymentPayload = {
    order_ids: orderIds,
    amount: totalAmount,
    order_info: `Thanh toán ${orderIds.length} đơn hàng`,
    customer_name: form.buyer_name,
    customer_email: form.buyer_email || null,
    customer_phone: form.buyer_phone || null,
  }

  const endpoint = paymentMethod === 'vnpay'
    ? `${API_BASE_URL}/payment/vnpay`
    : `${API_BASE_URL}/payment/mbbank`

  console.log('Creating cart payment:', { endpoint, paymentPayload })

  const { data } = await axios.post(endpoint, paymentPayload, { headers })

  console.log('Cart payment response:', data)

  if (!data?.status || !data?.data) {
    throw new Error(data?.message || 'Phản hồi từ server không hợp lệ')
  }

  const result = {
    paymentUrl: null,
    paymentData: null
  }

  if (paymentMethod === 'vnpay') {
    result.paymentUrl = data.data.payment_url || data.data
  } else if (paymentMethod === 'mbbank') {
    result.paymentData = data.data
    if (data.data.payment_url) {
      result.paymentUrl = data.data.payment_url
    }
  }

  return result
}

const createPayment = async (orderId, paymentMethod) => {
  if (!orderId || !orderResult.value) return

  processingPayment.value = true

  try {
    const result = await requestPayment(orderResult.value, paymentMethod)
    if (paymentMethod === 'vnpay') {
      paymentUrl.value = result.paymentUrl
    } else if (paymentMethod === 'mbbank') {
      paymentData.value = result.paymentData
      if (result.paymentUrl) {
        paymentUrl.value = result.paymentUrl
      }
    }
  } catch (err) {
    console.error('Lỗi khi tạo payment:', err)
    const errorMessage = err?.message || 'Không thể tạo link thanh toán. Vui lòng thử lại.'
    if (window?.$toast) {
      window.$toast.error(errorMessage)
    } else {
      alert(errorMessage)
    }
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

const nextCartStep = () => {
  if (cartStep.value === 1) {
    if (!cartItems.value.length) return
    cartStep.value = 2
    return
  }
  if (cartStep.value === 2) {
    if (!validateStep1()) {
      return
    }
    cartStep.value = 3
  }
}

const prevCartStep = () => {
  if (cartStep.value > 1) {
    cartStep.value -= 1
  }
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
  if (cartMode.value) return
  if (newQty) {
    form.quantity = Number(newQty) || 1
    clampQuantity()
  }
})

watch(() => form.payment_method, () => {
  if (cartMode.value) {
    cartPaymentResults.value = []
    if (cartStep.value > 2) {
      cartStep.value = 2
    }
  }
})

watch(() => [route.query.cart_mode, route.query.from_cart], () => {
  if (shouldUseCartMode()) {
    cartMode.value = true
    form.payment_method = 'cash'
    cartStep.value = 1
    loadCartSelection()
  } else if (cartMode.value) {
    cartMode.value = false
    errorMessage.value = ''
    orderResult.value = null
    cartStep.value = 1
    cartPaymentResults.value = []
    loadProduct()
  }
})

onMounted(() => {
  if (shouldUseCartMode()) {
    cartMode.value = true
    form.payment_method = 'cash'
    cartStep.value = 1
    loadCartSelection()
    return
  }

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

.cart-checkout-wrapper {
  position: relative;
  isolation: isolate;
}

.cart-checkout-wrapper::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top right, rgba(99,102,241,0.09), transparent 35%), radial-gradient(circle at top left, rgba(34,197,94,0.08), transparent 45%);
  z-index: -1;
}

.cart-hero {
  background: linear-gradient(135deg, #2563eb, #7c3aed);
  color: #fff;
  border-radius: 30px;
  padding: 2rem 2.5rem;
  box-shadow: 0 18px 40px rgba(79,70,229,0.25);
}

.cart-hero-content {
  flex-wrap: wrap;
}

.cart-hero-icon {
  width: 64px;
  height: 64px;
  border-radius: 20px;
  background: rgba(255,255,255,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  box-shadow: inset 0 0 0 1px rgba(255,255,255,0.25);
}

.cart-phase-label {
  letter-spacing: 0.2em;
  opacity: 0.8;
}

.cart-progress {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.progress-step {
  text-align: center;
  flex: 1;
  opacity: 0.6;
  color: rgba(255,255,255,0.8);
}

.progress-step.active {
  opacity: 1;
  color: #fff;
}

.step-circle {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 0.35rem;
  font-weight: 600;
}

.progress-line {
  flex: 2;
  height: 3px;
  border-radius: 999px;
  background: rgba(255,255,255,0.35);
}

.progress-line.active {
  background: #fff;
}

.cart-section {
  border-radius: 30px;
}

.cart-items-list {
  max-height: 620px;
  overflow-y: auto;
  padding-right: 0.5rem;
}

.cart-selected-item {
  background: #fff;
  border: 1px solid #eef2ff;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.cart-selected-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 40px rgba(79,70,229,0.15);
}

.cart-step-panel {
  border-radius: 28px;
  overflow: hidden;
}

.step-actions {
  background: #f8fafc;
}

.cart-item-thumb {
  width: 90px;
  height: 90px;
  border-radius: 22px;
  overflow: hidden;
  background: #f1f5f9;
  flex-shrink: 0;
  border: 1px solid rgba(148,163,184,0.3);
}

.cart-item-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cart-item-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.6rem;
}

.meta-badge {
  background: rgba(79,70,229,0.08);
  color: #4338ca;
  font-weight: 600;
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
  font-size: 0.85rem;
}

.cart-summary-card {
  border-radius: 28px;
}

.summary-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  color: #475569;
}

.summary-total h3 {
  font-size: 1.75rem;
}

.alert.soft {
  border: none;
  border-radius: 18px;
  background: rgba(79,70,229,0.08);
  color: #4c1d95;
}

.input-icon {
  position: relative;
}

.input-icon i {
  position: absolute;
  left: 0.85rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 1.1rem;
}

.input-icon .form-control {
  padding-left: 2.5rem;
}

.cart-info-card {
  border-radius: 26px;
  background: linear-gradient(135deg, #0f172a, #1e293b);
  color: white;
}

.cart-info-icon {
  width: 48px;
  height: 48px;
  border-radius: 18px;
  background: rgba(255,255,255,0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}

.cart-info-list li {
  margin-bottom: 0.35rem;
}

.cart-payment-group .alert,
.cart-online-payments .alert {
  border-radius: 16px;
  margin-bottom: 0;
}

.cart-review-list .cart-review-item {
  background: #f8fafc;
}

.cart-online-payments .payment-result-card {
  background: #fff;
}

.qr-block {
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 10px;
  background: #fff;
  max-width: 220px;
}

.qr-block img {
  display: block;
  width: 100%;
  height: auto;
}

.payment-result-card .btn {
  text-transform: none;
}

@media (max-width: 991.98px) {
  .cart-selected-item {
    flex-direction: column;
  }
  .cart-item-total {
    text-align: left !important;
  }
  .cart-items-list {
    max-height: none;
  }
  .cart-hero {
    padding: 1.5rem;
  }
  .cart-progress {
    flex-wrap: wrap;
  }
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
