<template>
  <div class="notification-page">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
          <div class="card notification-card shadow-lg border-0">
            <div class="card-body text-center p-5">
              <!-- Icon Animation -->
              <div class="icon-wrapper mb-4" :class="statusClass">
                <i v-if="status === 'success'" class='bx bx-check'></i>
                <i v-else-if="status === 'failed'" class='bx bx-x'></i>
                <i v-else-if="status === 'warning'" class='bx bx-error'></i>
                <i v-else class='bx bx-info-circle'></i>
              </div>

              <!-- Title -->
              <h2 class="notification-title mb-3" :class="textClass">
                {{ title }}
              </h2>

              <!-- Message -->
              <p class="notification-message text-muted mb-4">
                {{ message }}
              </p>

              <!-- Order Info (Optional) -->
              <div v-if="orderCode" class="order-info-box mb-4 p-3 rounded">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="text-muted">Mã đơn hàng:</span>
                  <span class="fw-bold text-primary">{{ orderCode }}</span>
                </div>
                <div v-if="amount" class="d-flex justify-content-between align-items-center mt-2">
                  <span class="text-muted">Số tiền:</span>
                  <span class="fw-bold text-dark">{{ formatCurrency(amount) }}</span>
                </div>
              </div>

              <!-- Actions -->
              <div class="action-buttons d-grid gap-2 d-sm-flex justify-content-center">
                <router-link to="/don-mua" class="btn btn-primary btn-lg px-4 rounded-pill">
                  <i class='bx bx-list-ul me-2'></i>Đơn mua
                </router-link>
                <router-link to="/trang-chu" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">
                  <i class='bx bx-home-alt me-2'></i>Trang chủ
                </router-link>
              </div>
              
              <!-- Support Link -->
              <div class="mt-4">
                <small class="text-muted">
                  Cần hỗ trợ? <a href="#" class="text-decoration-none">Liên hệ CSKH</a>
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      status: 'info',
      title: '',
      message: '',
      orderCode: '',
      amount: 0
    }
  },
  computed: {
    statusClass() {
      return {
        'bg-success-subtle text-success': this.status === 'success',
        'bg-danger-subtle text-danger': this.status === 'failed',
        'bg-warning-subtle text-warning': this.status === 'warning',
        'bg-info-subtle text-info': this.status === 'info'
      }
    },
    textClass() {
      return {
        'text-success': this.status === 'success',
        'text-danger': this.status === 'failed',
        'text-warning': this.status === 'warning',
        'text-info': this.status === 'info'
      }
    }
  },
  mounted() {
    // Get params from URL
    const rspCode = this.$route.query.RspCode
    const msg = this.$route.query.Message
    const code = this.$route.query.order_code || this.$route.query.orderCode
    const amt = this.$route.query.amount || this.$route.query.vnp_Amount
    
    this.orderCode = code || ''
    this.amount = amt ? parseInt(amt) : 0

    // Determine status based on RspCode (VNPay standard)
    if (rspCode) {
      if (rspCode === '00') {
        this.status = 'success'
        this.title = 'Thanh toán thành công!'
        this.message = msg || 'Đơn hàng của bạn đã được thanh toán thành công.'
      } else {
        this.status = 'failed'
        this.title = 'Thanh toán thất bại'
        this.message = msg || `Lỗi thanh toán (Mã lỗi: ${rspCode})`
      }
    } else {
      // Generic usage
      this.status = this.$route.query.status || 'info'
      this.title = this.$route.query.title || 'Thông báo'
      this.message = this.$route.query.message || msg || ''
    }
  },
  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)
    }
  }
}
</script>

<style scoped>
.notification-page {
  min-height: 80vh;
  display: flex;
  align-items: center;
  background-color: #f8f9fa;
  padding: 2rem 0;
}

.notification-card {
  border-radius: 20px;
  overflow: hidden;
  transition: transform 0.3s ease;
}

.notification-card:hover {
  transform: translateY(-5px);
}

.icon-wrapper {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  font-size: 40px;
  animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.notification-title {
  font-weight: 700;
  font-size: 1.75rem;
}

.notification-message {
  font-size: 1.1rem;
  line-height: 1.6;
}

.order-info-box {
  background-color: #f8f9fa;
  border: 1px dashed #dee2e6;
}

.btn-lg {
  padding: 12px 30px;
  font-weight: 600;
}

@keyframes scaleIn {
  from {
    transform: scale(0);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

/* Background colors for icons */
.bg-success-subtle { background-color: #d1e7dd; }
.bg-danger-subtle { background-color: #f8d7da; }
.bg-warning-subtle { background-color: #fff3cd; }
.bg-info-subtle { background-color: #cff4fc; }
</style>
