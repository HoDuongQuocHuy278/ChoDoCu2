<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <div class="text-center mb-4">
              <h2 class="fw-bold mb-2">Đăng Ký Bán Hàng</h2>
              <p class="text-muted">Đồng ý với các điều khoản để bắt đầu đăng bán sản phẩm</p>
            </div>

            <!-- Điều khoản đăng ký bán -->
            <div class="mb-4">
              <div class="border rounded-3 p-4 mb-4" style="max-height: 400px; overflow-y: auto; background-color: #f8f9fa;">
                <h5 class="fw-bold mb-3">Điều khoản đăng ký bán hàng</h5>
                
                <div class="mb-3">
                  <h6>1. Quy định chung</h6>
                  <p class="small text-muted mb-3">
                    Bằng việc đăng ký bán hàng, bạn đồng ý tuân thủ các quy định và điều khoản của nền tảng. 
                    Bạn có trách nhiệm đảm bảo thông tin sản phẩm chính xác, minh bạch và không vi phạm pháp luật.
                  </p>
                </div>

                <div class="mb-3">
                  <h6>2. Trách nhiệm người bán</h6>
                  <p class="small text-muted mb-3">
                    - Cung cấp thông tin sản phẩm chính xác, đầy đủ, không gây hiểu lầm cho người mua<br>
                    - Đảm bảo chất lượng sản phẩm đúng như mô tả<br>
                    - Giao hàng đúng thời hạn đã cam kết<br>
                    - Giải quyết khiếu nại của người mua một cách công bằng và minh bạch
                  </p>
                </div>

                <div class="mb-3">
                  <h6>3. Hành vi bị cấm</h6>
                  <p class="small text-muted mb-3">
                    - Đăng tin sai sự thật, lừa đảo<br>
                    - Đăng sản phẩm giả, hàng nhái, vi phạm bản quyền<br>
                    - Đăng sản phẩm cấm theo quy định pháp luật<br>
                    - Gian lận trong giao dịch
                  </p>
                </div>

                <div class="mb-3">
                  <h6>4. Xử lý vi phạm</h6>
                  <p class="small text-muted mb-3">
                    Vi phạm điều khoản có thể dẫn đến cảnh báo, khóa tài khoản, hoặc xóa sản phẩm. 
                    Trường hợp nghiêm trọng có thể bị khóa vĩnh viễn tài khoản.
                  </p>
                </div>

                <div class="mb-3">
                  <h6>5. Quyền và lợi ích</h6>
                  <p class="small text-muted">
                    Khi đăng ký bán hàng, bạn được quyền:<br>
                    - Đăng bán sản phẩm trên nền tảng<br>
                    - Quản lý sản phẩm của mình<br>
                    - Nhận thông báo về đơn hàng và giao dịch<br>
                    - Tham gia các chương trình khuyến mãi dành cho người bán
                  </p>
                </div>
              </div>

              <div class="form-check mb-3">
                <input 
                  class="form-check-input" 
                  type="checkbox" 
                  id="agreeTerms" 
                  v-model="agreedToTerms"
                  :class="{ 'is-invalid': errors.terms }"
                >
                <label class="form-check-label" for="agreeTerms">
                  <strong>Tôi đã đọc và đồng ý với tất cả các điều khoản đăng ký bán hàng ở trên</strong>
                </label>
                <div class="invalid-feedback d-block" v-if="errors.terms">
                  {{ errors.terms }}
                </div>
              </div>
            </div>

            <!-- Thông báo nếu đã đăng ký -->
            <div v-if="isSeller" class="alert alert-success mb-4">
              <i class="bi bi-check-circle me-2"></i>
              Bạn đã đăng ký bán hàng thành công! Bạn có thể bắt đầu đăng bán sản phẩm ngay bây giờ.
              <div class="mt-2">
                <router-link to="/sell" class="btn btn-success btn-sm">Đăng bán ngay</router-link>
              </div>
            </div>

            <!-- Form đăng ký -->
            <div v-else>
              <div class="d-grid gap-2">
                <button 
                  type="button" 
                  class="btn btn-success btn-lg" 
                  @click="handleRegister"
                  :disabled="submitting"
                >
                  <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                  {{ submitting ? 'Đang xử lý...' : 'Đồng ý và Đăng ký bán hàng' }}
                </button>
                <button 
                  type="button" 
                  class="btn btn-outline-secondary" 
                  @click="$router.push('/trang-chu')"
                >
                  Hủy
                </button>
              </div>
            </div>

            <!-- Thông báo lỗi -->
            <div v-if="errorMessage" class="alert alert-danger mt-3" role="alert">
              {{ errorMessage }}
            </div>
          </div>
        </div>

        <!-- Lợi ích khi đăng ký bán -->
        <div class="card border-0 shadow-sm mt-4">
          <div class="card-body p-4">
            <h5 class="fw-bold mb-3">Lợi ích khi trở thành người bán</h5>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="d-flex align-items-start">
                  <div class="flex-shrink-0">
                    <div class="bg-success bg-opacity-10 rounded-circle p-2">
                      <i class="bi bi-tag-fill text-success"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Miễn phí đăng tin</h6>
                    <p class="small text-muted mb-0">Đăng tin sản phẩm hoàn toàn miễn phí</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex align-items-start">
                  <div class="flex-shrink-0">
                    <div class="bg-success bg-opacity-10 rounded-circle p-2">
                      <i class="bi bi-people-fill text-success"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Tiếp cận nhiều khách hàng</h6>
                    <p class="small text-muted mb-0">Sản phẩm được hiển thị cho hàng nghìn người dùng</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex align-items-start">
                  <div class="flex-shrink-0">
                    <div class="bg-success bg-opacity-10 rounded-circle p-2">
                      <i class="bi bi-shield-check text-success"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Bảo mật thông tin</h6>
                    <p class="small text-muted mb-0">Thông tin cá nhân được bảo vệ an toàn</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex align-items-start">
                  <div class="flex-shrink-0">
                    <div class="bg-success bg-opacity-10 rounded-circle p-2">
                      <i class="bi bi-graph-up text-success"></i>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Quản lý dễ dàng</h6>
                    <p class="small text-muted mb-0">Công cụ quản lý sản phẩm trực quan, dễ sử dụng</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/client'

const agreedToTerms = ref(false)
const submitting = ref(false)
const isSeller = ref(false)
const errorMessage = ref('')
const errors = reactive({
  terms: ''
})

// Toast notification
function showToast(message, type = 'info') {
  if (window.$toast) {
    if (type === 'error') {
      window.$toast.error(message)
    } else if (type === 'success') {
      window.$toast.success(message)
    } else {
      window.$toast.info(message)
    }
  } else {
    alert(message)
  }
}

// Kiểm tra đăng nhập
function checkAuth() {
  const token = localStorage.getItem('key_client')
  if (!token) {
    showToast('Vui lòng đăng nhập để đăng ký bán hàng', 'error')
    setTimeout(() => {
      router.push('/dang-nhap')
    }, 1500)
    return false
  }
  return true
}

// Kiểm tra trạng thái đăng ký bán
async function checkSellerStatus() {
  const token = localStorage.getItem('key_client')
  if (!token) return

  try {
    const { data } = await axios.get(`${API_BASE_URL}/check-seller-status`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })

    if (data?.status && data?.is_seller) {
      isSeller.value = true
    }
  } catch (err) {
    console.error('Error checking seller status:', err)
  }
}

// Xử lý đăng ký bán hàng
async function handleRegister() {
  // Reset errors
  errors.terms = ''
  errorMessage.value = ''

  // Validate
  if (!agreedToTerms.value) {
    errors.terms = 'Bạn cần đồng ý với điều khoản đăng ký bán hàng'
    return
  }

  // Kiểm tra đăng nhập
  if (!checkAuth()) return

  submitting.value = true

  try {
    const token = localStorage.getItem('key_client')
    
    const { data } = await axios.post(
      `${API_BASE_URL}/dang-ky-ban`,
      {
        agreed_to_terms: true
      },
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }
    )

    if (data?.status) {
      isSeller.value = true
      showToast(data.message || 'Đăng ký bán hàng thành công!', 'success')
      
      // Redirect sau 2 giây
      setTimeout(() => {
        router.push('/sell')
      }, 2000)
    } else {
      errorMessage.value = data?.message || 'Có lỗi xảy ra khi đăng ký bán hàng'
      showToast(errorMessage.value, 'error')
    }
  } catch (err) {
    console.error('Error registering as seller:', err)
    const msg = err?.response?.data?.message || err?.message || 'Có lỗi xảy ra khi đăng ký bán hàng'
    errorMessage.value = msg
    showToast(msg, 'error')

    // Nếu lỗi authentication, redirect về trang đăng nhập
    if (err?.response?.status === 401) {
      setTimeout(() => {
        router.push('/dang-nhap')
      }, 2000)
    }
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  // Kiểm tra đăng nhập
  if (!checkAuth()) {
    return
  }
  
  // Kiểm tra trạng thái đăng ký bán
  checkSellerStatus()
})
</script>

<style scoped>
.form-check-input.is-invalid {
  border-color: #dc3545;
}

.bg-success.bg-opacity-10 {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-success.bg-opacity-10 i {
  font-size: 1.2rem;
}
</style>

