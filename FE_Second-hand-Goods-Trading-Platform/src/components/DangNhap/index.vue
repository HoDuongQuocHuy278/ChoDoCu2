<template>
  <div class="auth gradient-bg d-flex align-items-center justify-content-center py-5 py-lg-0">
    <div class="container py-3">
      <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
          <!-- GLASS CARD -->
          <div class="glass card border-0 shadow-xxl overflow-hidden rounded-4">
            <div class="row g-0 align-items-stretch">

              <!-- LEFT: BRAND / HERO -->
              <div class="col-lg-6 brand-pane d-none d-lg-flex flex-column justify-content-between p-4 p-xxl-5">
                <div>
                  <span class="badge bg-success-subtle text-success fw-semibold mb-2">Chợ Đồ Cũ</span>
                  <h2 class="display-6 fw-extrabold mb-2 text-wrap">
                    Chào mừng trở lại! 🛍️
                  </h2>
                  <p class="text-muted fs-6 mb-4">
                    Mua bán đồ cũ nhanh – an toàn – tiết kiệm.
                  </p>
                  <ul class="list-unstyled text-muted small m-0">
                    <li class="mb-1">• Bảo mật token & lưu phiên</li>
                    <li class="mb-1">• Đăng nhập bằng Google</li>
                    <li>• Khôi phục mật khẩu nhanh</li>
                  </ul>
                </div>
                <div class="hero-art" aria-hidden="true">
                  <div class="blob b1"></div>
                  <div class="blob b2"></div>
                  <div class="blob b3"></div>
                </div>
              </div>

              <!-- RIGHT: FORM -->
              <div class="col-lg-6 bg-white p-4 p-xl-5">
                <div class="text-center mb-3 d-lg-none">
                  <h3 class="fw-bold m-0">Đăng Nhập</h3>
                </div>

                <div class="text-center mb-3">
                  <span class="text-muted">Chưa có tài khoản?</span>
                  <router-link to="/dang-ky" class="ms-1 fw-semibold link-success">Đăng ký ngay</router-link>
                </div>

                <div class="form-body">
                  <div class="row g-3">
                    <div class="col-12">
                      <label class="form-label">Email</label>
                      <div class="input-group input-elev">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fa-solid fa-envelope"></i></span>
                        <input v-model.trim="thong_tin_dang_nhap.email" type="email" class="form-control border-start-0" placeholder="you@example.com" :class="{'is-invalid': errors.email}" @blur="touch()">
                        <div class="invalid-feedback">{{ errors.email }}</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label class="form-label">Mật khẩu</label>
                      <div class="input-group input-elev">
                        <span class="input-group-text bg-transparent border-end-0"><i class="fa-solid fa-lock"></i></span>
                        <input :type="showPass ? 'text' : 'password'" v-model.trim="thong_tin_dang_nhap.password" class="form-control border-start-0" placeholder="••••••••" :class="{'is-invalid': errors.password}" @keydown.enter="dangNhap" @blur="touch()">
                        <button class="btn btn-outline-secondary rounded-end" type="button" @click="showPass=!showPass" :aria-pressed="showPass ? 'true':'false'">
                          <i :class="showPass ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                        </button>
                        <div class="invalid-feedback">{{ errors.password }}</div>
                      </div>
                    </div>

                    <div class="col-6">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" v-model="remember">
                        <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                      </div>
                    </div>
                    <div class="col-6 text-end">
                      <router-link to="/client/quen-mat-khau" class="link-success">Quên mật khẩu?</router-link>
                    </div>

                    <div class="col-12 mt-1 d-grid">
                      <button @click="dangNhap" class="btn btn-success btn-cta rounded-pill py-2" :disabled="loading">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <i class="bx bxs-lock-open me-1"></i>Đăng Nhập
                      </button>
                    </div>
                  </div>
                </div>

                <p class="small text-muted text-center mt-4 mb-0">Bằng cách tiếp tục, bạn đồng ý với Điều khoản & Chính sách bảo mật.</p>
              </div>

            </div>
          </div>
          <!-- /GLASS CARD -->
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ===== Background + Tokens ===== */
:root { --brand:#16a34a; --brand-700:#15803d; }
.gradient-bg{
  min-height: 100vh;
  background:
    radial-gradient(1200px 600px at 10% -10%, #e6fff4 0%, transparent 50%),
    radial-gradient(1000px 500px at 110% 10%, #ffe8f6 0%, transparent 50%),
    linear-gradient(180deg,#f8fafc,#eef2f7);
}

/***** Glass card *****/
.glass{ backdrop-filter: saturate(140%) blur(6px); background: rgba(255,255,255,.6); }
.shadow-xxl{ box-shadow: 0 25px 70px rgba(2,12,27,.15); }

/***** Brand pane with blobs *****/
.brand-pane{ position: relative; background: linear-gradient(135deg,#f0f9ff 0%, #ecfeff 40%, #f0fdf4 100%); border-right: 1px solid rgba(0,0,0,.06); }
.hero-art .blob{ position:absolute; border-radius:50%; filter: blur(20px); opacity:.55; animation: float 12s ease-in-out infinite; }
.b1{ width:220px; height:220px; background:#bbf7d0; right:-40px; top:-40px; }
.b2{ width:200px; height:200px; background:#fde68a; right:120px; bottom:-60px; animation-delay:-3s; }
.b3{ width:160px; height:160px; background:#fecdd3; right:-60px; bottom:40px; animation-delay:-6s; }
@keyframes float{ 0%,100%{ transform: translateY(0) } 50%{ transform: translateY(-10px) } }

/***** Inputs *****/
.input-elev .form-control{ transition: box-shadow .2s ease, border-color .2s ease; }
.input-elev .form-control:focus{ box-shadow: 0 0 0 .25rem rgba(22,163,74,.15); border-color:#a7f3d0; }
.is-invalid + .invalid-feedback{ display:block; }

/***** CTA *****/
.btn-cta{ box-shadow: 0 10px 20px rgba(22,163,74,.25); transition: transform .15s ease, box-shadow .2s ease; }
.btn-cta:hover{ transform: translateY(-1px); box-shadow: 0 16px 30px rgba(22,163,74,.35); }
.btn-google{ transition: transform .15s ease, box-shadow .2s ease; }
.btn-google:hover{ transform: translateY(-1px); box-shadow: 0 10px 18px rgba(0,0,0,.08); }

/***** Separator *****/
.login-separater .sep{ position:absolute; top:-12px; left:50%; transform: translateX(-50%); border-radius: 999px; }
</style>

<script>
import axios from 'axios'
export default {
  data(){
    return {
      thong_tin_dang_nhap: { email: '', password: '' },
      loading: false,
      showPass: false,
      remember: true,
      errors: { email: '', password: '' },
    }
  },
  methods: {
    touch(){ this.validate() },
    validate(){
      this.errors = { email: '', password: '' }
      const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if(!this.thong_tin_dang_nhap.email) this.errors.email = 'Vui lòng nhập email.'
      else if(!emailRe.test(this.thong_tin_dang_nhap.email)) this.errors.email = 'Email không hợp lệ.'
      if(!this.thong_tin_dang_nhap.password) this.errors.password = 'Vui lòng nhập mật khẩu.'
      else if(this.thong_tin_dang_nhap.password.length < 6) this.errors.password = 'Mật khẩu tối thiểu 6 ký tự.'
      return !this.errors.email && !this.errors.password
    },
    async dangNhap(){
      if(!this.validate()) return
      this.loading = true
      const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/client'
      try{
        const res = await axios.post(`${API_BASE_URL}/dang-nhap`, this.thong_tin_dang_nhap)
        if(res.data?.status){
          this.$toast?.success(res.data.message || 'Đăng nhập thành công')
          localStorage.setItem('key_client', res.data.token)
          if(this.remember){ localStorage.setItem('remember_email', this.thong_tin_dang_nhap.email) }
          this.$router.push('/')
        } else {
          this.$toast?.error(res.data?.message || 'Đăng nhập thất bại')
        }
      }catch(err){
        const msg = err?.response?.data?.message || err.message || 'Lỗi kết nối server'
        this.$toast?.error(msg)
      }finally{
        this.loading = false
      }
    },
    loginGoogle(){ this.$toast?.info('Sẽ tích hợp OAuth Google ở bản sau.') }
  },
  mounted(){
    const saved = localStorage.getItem('remember_email')
    if(saved) this.thong_tin_dang_nhap.email = saved
  }
}
</script>


