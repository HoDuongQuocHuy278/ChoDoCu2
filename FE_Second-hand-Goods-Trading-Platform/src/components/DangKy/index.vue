<template>
  <div class="auth d-flex align-items-center justify-content-center py-5 py-lg-0" style="min-height:100vh">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
          <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="row g-0 align-items-stretch">
              <!-- Left brand pane (optional) -->
              <div class="col-lg-5 d-none d-lg-flex flex-column justify-content-between p-4 brand-pane">
                <div>
                  <div class="badge bg-success-subtle text-success mb-2">Chợ Đồ Cũ</div>
                  <h3 class="fw-bold m-0">Tạo tài khoản</h3>
                  <p class="text-muted mt-2 mb-0">Bắt đầu mua bán nhanh – an toàn – tiện lợi.</p>
                </div>
                <ul class="small text-muted list-unstyled m-0">
                  <li>• Kích hoạt qua email</li>
                  <li>• Bảo mật tài khoản</li>
                  <li>• Quản lý đơn dễ dàng</li>
                </ul>
              </div>

              <!-- Right form pane -->
              <div class="col-lg-7 p-4 p-lg-5 bg-white">
                <div class="text-center mb-3 d-lg-none">
                  <h3 class="fw-bold">Đăng ký tài khoản</h3>
                </div>

                <div class="text-center mb-3">
                  <span class="text-muted">Bạn đã có tài khoản?</span>
                  <router-link to="/dang-nhap" class="ms-1 fw-semibold">Đăng nhập tại đây</router-link>
                </div>

                <form @submit.prevent="dangKyTaiKhoan" novalidate>
                  <div class="row g-3">
                    <div class="col-sm-6">
                      <label class="form-label">Họ và tên</label>
                      <input v-model.trim="user.ho_va_ten" type="text" class="form-control" :class="{'is-invalid': errors.ho_va_ten}" placeholder="Nguyễn Văn A" @blur="touch('ho_va_ten')">
                      <div class="invalid-feedback">{{ errors.ho_va_ten }}</div>
                    </div>

                    <div class="col-sm-6">
                      <label class="form-label">Email</label>
                      <input v-model.trim="user.email" type="email" class="form-control" :class="{'is-invalid': errors.email}" placeholder="you@example.com" @blur="touch('email')">
                      <div class="invalid-feedback">{{ errors.email }}</div>
                    </div>

                    <div class="col-sm-4">
                      <label class="form-label">Số điện thoại</label>
                      <input v-model.trim="user.so_dien_thoai" type="tel" class="form-control" :class="{'is-invalid': errors.so_dien_thoai}" placeholder="09xx..." @blur="touch('so_dien_thoai')">
                      <div class="invalid-feedback">{{ errors.so_dien_thoai }}</div>
                    </div>

                    <div class="col-sm-4">
                      <label class="form-label">Số CCCD</label>
                      <input v-model.trim="user.cccd" type="text" class="form-control" :class="{'is-invalid': errors.cccd}" placeholder="0123456789" @blur="touch('cccd')">
                      <div class="invalid-feedback">{{ errors.cccd }}</div>
                    </div>

                    <div class="col-sm-4">
                      <label class="form-label">Ngày sinh</label>
                      <input v-model="user.ngay_sinh" type="date" class="form-control" :class="{'is-invalid': errors.ngay_sinh}" min="1900-01-01" max="2030-12-31" @blur="touch('ngay_sinh')">
                      <div class="invalid-feedback">{{ errors.ngay_sinh }}</div>
                    </div>

                    <div class="col-12 col-md-6">
                      <label class="form-label">Mật khẩu</label>
                      <div class="input-group">
                        <input v-model="user.password" :type="showPassword ? 'text' : 'password'" class="form-control border-end-0" :class="{'is-invalid': errors.password}" placeholder="Tối thiểu 6 ký tự" @blur="touch('password')">
                        <button type="button" class="input-group-text bg-transparent" @click="showPassword=!showPassword" :aria-pressed="showPassword?'true':'false'">
                          <i :class="showPassword ? 'bx bx-show' : 'bx bx-hide'"></i>
                        </button>
                        <div class="invalid-feedback">{{ errors.password }}</div>
                      </div>
                      <div class="form-text">Mẹo: dùng chữ hoa + số để mạnh hơn.</div>
                    </div>

                    <div class="col-12 col-md-6">
                      <label class="form-label">Nhập lại mật khẩu</label>
                      <div class="input-group">
                        <input v-model="user.re_password" :type="showRePassword ? 'text' : 'password'" class="form-control border-end-0" :class="{'is-invalid': errors.re_password}" @blur="touch('re_password')">
                        <button type="button" class="input-group-text bg-transparent" @click="showRePassword=!showRePassword" :aria-pressed="showRePassword?'true':'false'">
                          <i :class="showRePassword ? 'bx bx-show' : 'bx bx-hide'"></i>
                        </button>
                        <div class="invalid-feedback">{{ errors.re_password }}</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="agree" v-model="agree">
                        <label class="form-check-label" for="agree">Tôi đồng ý với Điều khoản dịch vụ & Chính sách bảo mật.</label>
                      </div>
                      <div v-if="errors.agree" class="text-danger small mt-1">{{ errors.agree }}</div>
                    </div>

                    <div class="col-12">
                      <div class="d-grid">
                        <button type="submit" class="btn btn-success text-uppercase" :disabled="isLoading">
                          <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                          <i class="bx bx-user me-1"></i> Đăng ký
                        </button>
                      </div>
                    </div>
                  </div>
                </form>

                <p class="small text-muted text-center mt-4 mb-0">Sau khi đăng ký, hệ thống sẽ gửi email để kích hoạt tài khoản.</p>
              </div>
              <!-- /Right form pane -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  name: 'Register',
  data(){
    return {
      user: { ho_va_ten: '', email: '', so_dien_thoai: '', cccd: '', ngay_sinh: '', password: '', re_password: '' },
      showPassword: false,
      showRePassword: false,
      isLoading: false,
      agree: false,
      touched: {},
      errors: { ho_va_ten:'', email:'', so_dien_thoai:'', cccd:'', ngay_sinh:'', password:'', re_password:'', agree:'' },
    }
  },
  methods: {
    touch(field){ this.touched[field] = true; this.validate() },
    validate(){
      const e = { ho_va_ten:'', email:'', so_dien_thoai:'', cccd:'', ngay_sinh:'', password:'', re_password:'', agree:'' }
      // Họ tên
      if(this.touched.ho_va_ten && !this.user.ho_va_ten) e.ho_va_ten = 'Vui lòng nhập họ tên.'
      // Email
      const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if(this.touched.email && !this.user.email) e.email = 'Vui lòng nhập email.'
      else if(this.touched.email && !emailRe.test(this.user.email)) e.email = 'Email không hợp lệ.'
      // SĐT (10-11 số)
      const phoneRe = /^\d{10,11}$/
      if(this.touched.so_dien_thoai && !this.user.so_dien_thoai) e.so_dien_thoai = 'Vui lòng nhập số điện thoại.'
      else if(this.touched.so_dien_thoai && !phoneRe.test(this.user.so_dien_thoai)) e.so_dien_thoai = 'Số điện thoại không hợp lệ.'
      // CCCD (9-12 số tuỳ dữ liệu)
      const cccdRe = /^\d{9,12}$/
      if(this.touched.cccd && !this.user.cccd) e.cccd = 'Vui lòng nhập CCCD.'
      else if(this.touched.cccd && !cccdRe.test(this.user.cccd)) e.cccd = 'CCCD không hợp lệ.'
      // Ngày sinh
      if(this.touched.ngay_sinh && !this.user.ngay_sinh) e.ngay_sinh = 'Vui lòng chọn ngày sinh.'
      // Password
      if(this.touched.password && !this.user.password) e.password = 'Vui lòng nhập mật khẩu.'
      else if(this.touched.password && this.user.password.length < 6) e.password = 'Mật khẩu tối thiểu 6 ký tự.'
      // Re password
      if(this.touched.re_password && !this.user.re_password) e.re_password = 'Vui lòng nhập lại mật khẩu.'
      else if(this.touched.re_password && this.user.re_password !== this.user.password) e.re_password = 'Mật khẩu nhập lại không khớp.'
      // Đồng ý
      if(this.touched.agree && !this.agree) e.agree = 'Bạn cần đồng ý với điều khoản.'

      this.errors = e
      return Object.values(e).every(v => v === '')
    },
    async dangKyTaiKhoan(){
      // Touch tất cả field 1 lần
      Object.keys(this.errors).forEach(k => this.touched[k] = true)
      if(!this.validate()) return
      if(!this.agree){ this.touched.agree = true; this.validate(); return }

      this.isLoading = true
      const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/client'
      try{
        const res = await axios.post(`${API_BASE_URL}/dang-ky`, this.user)
        if(res.data?.status){
          this.$toast?.success(res.data?.message || 'Đăng ký thành công, vui lòng kiểm tra email kích hoạt.')
          // Option: chuyển qua đăng nhập
          this.$router.push('/dang-nhap')
        }else{
          this.$toast?.error(res.data?.message || 'Đăng ký tài khoản thất bại')
        }
      }catch(err){
        const listErr = err?.response?.data?.errors
        if(listErr){
          // map lỗi từ server vào UI
          for(const [k, v] of Object.entries(listErr)){
            this.touched[k] = true
            this.errors[k] = Array.isArray(v) ? v[0] : String(v)
          }
        }
        const msg = err?.response?.data?.message || 'Có lỗi xảy ra. Vui lòng thử lại.'
        this.$toast?.error(msg)
      }finally{
        this.isLoading = false
      }
    },
  }
}
</script>

<style scoped>
.brand-pane{ background: linear-gradient(135deg,#f0f9ff 0%, #ecfeff 40%, #f0fdf4 100%); }
.input-group .form-control:focus{ box-shadow: none; border-color:#86efac; }
.is-invalid + .invalid-feedback{ display:block; }
</style>
