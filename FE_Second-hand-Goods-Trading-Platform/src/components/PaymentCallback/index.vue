<template>
  <div class="payment-callback-page">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-body text-center py-5">
              <!-- Success State -->
              <div v-if="isSuccess" class="success-state">
                <div class="success-icon mb-4">
                  <i class='bx bx-check-circle'></i>
                </div>
                <h2 class="text-success mb-3">Thanh toán thành công!</h2>
                <p class="text-muted mb-4">
                  Cảm ơn bạn đã thanh toán. Đơn hàng của bạn đã được xác nhận và đang được xử lý.
                </p>
                
                <div v-if="orderCode" class="order-info mb-4">
                  <div class="alert alert-info">
                    <strong>Mã đơn hàng:</strong> {{ orderCode }}
                  </div>
                </div>

                <div class="action-buttons">
                  <router-link to="/don-mua" class="btn btn-primary btn-lg me-2">
                    <i class='bx bx-list-ul'></i> Xem đơn hàng của tôi
                  </router-link>
                  <router-link to="/trang-chu" class="btn btn-outline-secondary btn-lg">
                    <i class='bx bx-home'></i> Về trang chủ
                  </router-link>
                </div>
              </div>

              <!-- Failed State -->
              <div v-else-if="isFailed" class="failed-state">
                <div class="failed-icon mb-4">
                  <i class='bx bx-x-circle'></i>
                </div>
                <h2 class="text-danger mb-3">Thanh toán thất bại</h2>
                <p class="text-muted mb-4">
                  {{ errorMessage || 'Có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại.' }}
                </p>

                <div v-if="orderCode" class="order-info mb-4">
                  <div class="alert alert-warning">
                    <strong>Mã đơn hàng:</strong> {{ orderCode }}
                  </div>
                </div>

                <div class="action-buttons">
                  <router-link to="/don-mua" class="btn btn-primary btn-lg me-2">
                    <i class='bx bx-list-ul'></i> Xem đơn hàng của tôi
                  </router-link>
                  <router-link to="/trang-chu" class="btn btn-outline-secondary btn-lg">
                    <i class='bx bx-home'></i> Về trang chủ
                  </router-link>
                </div>
              </div>

              <!-- Loading State -->
              <div v-else class="loading-state">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                  <span class="visually-hidden">Đang xử lý...</span>
                </div>
                <p class="text-muted">Đang xử lý kết quả thanh toán...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const isSuccess = ref(false)
const isFailed = ref(false)
const errorMessage = ref('')
const orderCode = ref('')

onMounted(() => {
  const rspCode = route.query.RspCode
  const message = route.query.Message
  const orderId = route.query.order_id
  const code = route.query.order_code

  if (code) {
    orderCode.value = code
  }

  if (rspCode === '00') {
    isSuccess.value = true
    errorMessage.value = message || 'Thanh toán thành công'
    
    // Hiển thị toast nếu có
    if (window?.$toast) {
      window.$toast.success('Thanh toán thành công!')
    }
  } else {
    isFailed.value = true
    errorMessage.value = message || 'Thanh toán thất bại'
    
    // Hiển thị toast nếu có
    if (window?.$toast) {
      window.$toast.error(errorMessage.value)
    }
  }
})
</script>

<style scoped>
.payment-callback-page {
  min-height: 70vh;
  background: #f9fafb;
  padding: 2rem 0;
}

.success-icon,
.failed-icon {
  font-size: 80px;
  line-height: 1;
}

.success-icon {
  color: #28a745;
}

.failed-icon {
  color: #dc3545;
}

.order-info {
  max-width: 400px;
  margin: 0 auto;
}

.action-buttons {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.loading-state {
  padding: 2rem;
}

@media (max-width: 576px) {
  .action-buttons {
    flex-direction: column;
  }
  
  .action-buttons .btn {
    width: 100%;
  }
}
</style>

