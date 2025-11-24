<template>
    <div class="container py-4">
      <div class="row g-4">
        <div class="col-lg-8">
          <form class="card border-0 shadow-sm" @submit.prevent="onSubmit">
            <div class="card-body p-4">
              <!-- Thông báo nếu chưa đăng ký bán -->
              <div v-if="checkingSellerStatus" class="alert alert-info mb-3">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Đang kiểm tra quyền đăng bán...
              </div>
              <div v-else-if="!isSeller" class="alert alert-warning mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Bạn chưa đăng ký bán hàng. Vui lòng <router-link to="/dang-ky-ban" class="alert-link">đăng ký bán hàng</router-link> trước khi đăng sản phẩm.
              </div>
              
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="m-0 fw-bold">Đăng bán sản phẩm</h3>
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-outline-secondary" @click="saveDraft">Lưu nháp</button>
                  <button type="submit" class="btn btn-success" :disabled="submitting || !isSeller || checkingSellerStatus">
                    <span v-if="submitting" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    {{ submitting ? 'Đang đăng...' : 'Đăng bán' }}
                  </button>
                </div>
              </div>
  
              <!-- Thông tin cơ bản -->
              <div class="mb-4">
                <h6 class="text-uppercase text-muted mb-3">Thông tin cơ bản</h6>
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" v-model.trim="form.title" placeholder="VD: iPhone 13 128GB xanh" @blur="touch('title')" :class="invalid('title')" :disabled="!isSeller || checkingSellerStatus">
                    <div class="invalid-feedback" v-if="error('title')">Vui lòng nhập tiêu đề.</div>
                  </div>
  
                  <div class="col-md-6">
                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                    <select class="form-select" v-model="form.category" @change="touch('category')" :class="invalid('category')" :disabled="loadingCategories || !isSeller || checkingSellerStatus">
                      <option value="" disabled>{{ loadingCategories ? 'Đang tải...' : 'Chọn danh mục' }}</option>
                      <option v-for="c in cats" :key="c.slug" :value="c.slug">{{ c.name }}</option>
                    </select>
                    <div class="invalid-feedback" v-if="error('category')">Chọn danh mục.</div>
                  </div>
  
                  <div class="col-md-6">
                    <label class="form-label">Tình trạng</label>
                    <select class="form-select" v-model="form.condition" :disabled="!isSeller || checkingSellerStatus">
                      <option value="new">Mới 100%</option>
                      <option value="likenew">Như mới (99%)</option>
                      <option value="good">Tốt</option>
                      <option value="fair">Khá</option>
                    </select>
                  </div>
  
                  <div class="col-12">
                    <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                    <textarea class="form-control" rows="5" v-model.trim="form.description" placeholder="Tình trạng, phụ kiện, lý do bán..." @blur="touch('description')" :class="invalid('description')" :disabled="!isSeller || checkingSellerStatus"></textarea>
                    <div class="d-flex justify-content-between small mt-1">
                      <span v-if="error('description')" class="text-danger">Vui lòng nhập mô tả.</span>
                      <span class="text-muted">{{ form.description.length }}/2000</span>
                    </div>
                  </div>
  
                  <div class="col-md-6">
                    <label class="form-label">Giá <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text">₫</span>
                      <input type="number" min="0" step="1000" class="form-control" v-model.number="form.price" placeholder="VD: 2500000" @blur="touch('price')" :class="invalid('price')" :disabled="!isSeller || checkingSellerStatus">
                    </div>
                    <div class="form-text">Bật "Thương lượng" nếu linh hoạt giá.</div>
                    <div class="invalid-feedback d-block" v-if="error('price')">Nhập giá hợp lệ &gt; 0.</div>
                    <div class="form-check mt-2">
                      <input class="form-check-input" type="checkbox" v-model="form.negotiable" id="negotiable" :disabled="!isSeller || checkingSellerStatus">
                      <label class="form-check-label" for="negotiable">Cho phép thương lượng</label>
                    </div>
                  </div>
  
                  <div class="col-md-3">
                    <label class="form-label">Số lượng</label>
                    <input type="number" min="1" class="form-control" v-model.number="form.quantity" :disabled="!isSeller || checkingSellerStatus">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Tỉnh/Thành</label>
                    <input type="text" class="form-control" v-model.trim="form.location" placeholder="VD: Đà Nẵng" :disabled="!isSeller || checkingSellerStatus">
                  </div>
                </div>
              </div>
  
              <!-- Ảnh sản phẩm -->
              <div class="mb-4">
                <h6 class="text-uppercase text-muted mb-3">Hình ảnh sản phẩm <span class="text-danger">*</span></h6>
                <div class="p-3 border rounded-3 bg-light d-flex align-items-center gap-2" :class="{ 'border-success': dragOver, 'opacity-50': !isSeller || checkingSellerStatus }" @dragover.prevent="isSeller && !checkingSellerStatus ? dragOver = true : null" @dragleave.prevent="dragOver = false" @drop.prevent="isSeller && !checkingSellerStatus ? onDrop : null">
                  <input ref="fileInput" class="d-none" type="file" accept="image/*" multiple @change="onPick" :disabled="!isSeller || checkingSellerStatus" />
                  <button type="button" class="btn btn-outline-success" @click="isSeller && !checkingSellerStatus ? fileInput?.click() : null" :disabled="!isSeller || checkingSellerStatus">+ Thêm ảnh</button>
                  <span class="text-muted">Kéo thả ảnh vào đây (PNG/JPG, &lt; 5MB)</span>
                </div>
                <div class="row g-3 mt-2" v-if="images.length">
                  <div class="col-6 col-sm-4 col-md-3" v-for="(img, i) in images" :key="img.id">
                    <div class="position-relative border rounded-3 overflow-hidden">
                      <img :src="img.url" class="w-100" style="height:140px;object-fit:cover" :alt="'Ảnh ' + (i+1)">
                      <div class="position-absolute bottom-0 start-0 end-0 p-2 bg-dark bg-opacity-25 d-flex justify-content-between">
                        <button type="button" class="btn btn-sm btn-light" @click="makeCover(i)" :disabled="i===0">Bìa</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" @click="removeImage(i)">Xóa</button>
                      </div>
                    </div>
                    <div class="small text-success mt-1" v-if="i===0">Ảnh bìa</div>
                  </div>
                </div>
                <div class="invalid-feedback d-block" v-if="error('images')">Vui lòng thêm ít nhất 1 ảnh.</div>
              </div>
  
              <!-- Chi tiết bổ sung -->
              <div class="mb-4">
                <h6 class="text-uppercase text-muted mb-3">Chi tiết bổ sung</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Thương hiệu</label>
                    <input type="text" class="form-control" v-model.trim="form.brand" placeholder="Apple, Samsung...">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Model/Mã SP</label>
                    <input type="text" class="form-control" v-model.trim="form.model">
                  </div>
                  <div class="col-12">
                    <label class="form-label">Từ khóa</label>
                    <div class="input-group">
                      <input class="form-control" v-model.trim="tagInput" @keyup.enter="addTag" placeholder="Nhập từ khóa rồi Enter">
                      <button class="btn btn-outline-secondary" type="button" @click="addTag">Thêm</button>
                    </div>
                    <div class="mt-2">
                      <span v-for="(t,i) in form.tags" :key="i" class="badge rounded-pill text-bg-light me-2">#{{ t }}
                        <button type="button" class="btn-close btn-close-white ms-1" aria-label="Remove" @click="removeTag(i)"></button>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
  
              <!-- Vận chuyển & Liên hệ -->
              <div class="mb-3">
                <h6 class="text-uppercase text-muted mb-3">Vận chuyển & Liên hệ</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Hình thức giao</label>
                    <select class="form-select" v-model="form.shipping.method">
                      <option value="meet">Gặp trực tiếp</option>
                      <option value="ship">Ship COD</option>
                      <option value="both">Cả hai</option>
                    </select>
                  </div>
                  <div class="col-md-6" v-if="form.shipping.method !== 'meet'">
                    <label class="form-label">Phí ship (ước tính)</label>
                    <div class="input-group">
                      <span class="input-group-text">₫</span>
                      <input type="number" min="0" step="1000" class="form-control" v-model.number="form.shipping.fee">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">SĐT liên hệ</label>
                    <input type="tel" class="form-control" v-model.trim="form.contact.phone" placeholder="09xx...">
                  </div>
                  <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="hidePhone" v-model="form.contact.hidePhone">
                      <label class="form-check-label" for="hidePhone">Ẩn số, chỉ chat qua ứng dụng</label>
                    </div>
                  </div>
                </div>
              </div>
  
              <!-- Điều khoản -->
              <div class="mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="policy" v-model="policyChecked">
                  <label class="form-check-label" for="policy">
                    Tôi đồng ý với <a href="#">Chính sách đăng bán</a> & <a href="#">Điều khoản sử dụng</a>.
                  </label>
                </div>
                <div class="invalid-feedback d-block" v-if="policyError">Bạn cần đồng ý điều khoản trước khi đăng.</div>
              </div>
  
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" @click="saveDraft">Lưu nháp</button>
                <button type="submit" class="btn btn-success" :disabled="submitting">{{ submitting ? 'Đang đăng...' : 'Đăng bán' }}</button>
              </div>
            </div>
          </form>
        </div>
  
        <!-- Xem nhanh -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm position-sticky" style="top:20px">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="m-0">Xem nhanh</h5>
                <span class="badge text-bg-success-subtle text-success">Bản nháp</span>
              </div>
              <div v-if="images[0]" class="mb-3">
                <img :src="images[0].url" class="w-100 rounded-3" alt="Ảnh bìa">
              </div>
              <ul class="list-unstyled small m-0">
                <li class="mb-1"><span class="text-muted">Tiêu đề:</span> <b>{{ form.title || '—' }}</b></li>
                <li class="mb-1"><span class="text-muted">Danh mục:</span> <b>{{ findCat(form.category) || '—' }}</b></li>
                <li class="mb-1"><span class="text-muted">Giá:</span> <b>{{ priceText }}</b></li>
                <li class="mb-1"><span class="text-muted">Địa điểm:</span> <b>{{ form.location || '—' }}</b></li>
              </ul>
              <div class="mt-2" v-if="form.tags.length">
                <span v-for="(t,i) in form.tags" :key="i" class="badge rounded-pill text-bg-light me-2">#{{ t }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, computed, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import axios from 'axios'
  
  const router = useRouter()
  // Sử dụng relative URL để hoạt động với Vite proxy khi chia sẻ qua mạng
  const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api/client'
  
  const cats = ref([])
  const loadingCategories = ref(false)
  
  const form = reactive({
    title: '', description: '', category: '', condition: 'likenew',
    price: null, negotiable: false, quantity: 1, brand: '', model: '',
    tags: [], location: '', shipping: { method: 'both', fee: 0 }, contact: { phone: '', hidePhone: false },
  })
  
  const touched = reactive({})
  const submitting = ref(false)
  const policyChecked = ref(false)
  const policyError = ref(false)
  const isSeller = ref(false)
  const checkingSellerStatus = ref(false)
  
  // Ảnh
  const fileInput = ref(null)
  const dragOver = ref(false)
  const images = ref([]) // {id, file, url}
  
  // Hàm tạo unique ID (tương thích với mọi trình duyệt)
  function generateId() {
    // Sử dụng crypto.randomUUID nếu có, nếu không thì dùng fallback
    if (typeof crypto !== 'undefined' && crypto.randomUUID) {
      return crypto.randomUUID()
    }
    // Fallback: tạo ID đơn giản
    return 'img_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9)
  }
  
  function onPick(e){ addFiles(Array.from(e.target.files||[])); e.target.value='' }
  function onDrop(e){ dragOver.value=false; addFiles(Array.from(e.dataTransfer.files||[])) }
  function addFiles(files){
    for(const f of files){
      if(!f.type.startsWith('image/')) continue
      if(f.size > 5*1024*1024) continue
      const url = URL.createObjectURL(f)
      images.value.push({ id: generateId(), file: f, url })
    }
    touch('images')
  }
  function removeImage(i){ const [x] = images.value.splice(i,1); if(x) URL.revokeObjectURL(x.url) }
  function makeCover(i){ if(i===0) return; const x = images.value.splice(i,1)[0]; images.value.unshift(x) }
  
  // Tags
  const tagInput = ref('')
  function addTag(){ const t = tagInput.value.trim(); if(!t) return; if(!form.tags.includes(t)) form.tags.push(t); tagInput.value='' }
  function removeTag(i){ form.tags.splice(i,1) }
  
  // Preview
  const priceText = computed(()=> form.price && form.price>0 ? new Intl.NumberFormat('vi-VN').format(form.price) + (form.negotiable ? ' (TL)' : '') : '—')
  function findCat(slug){ return cats.value.find(x=>x.slug===slug)?.name }
  
  // Validate
  function touch(field){ touched[field] = true }
  function error(field){
    switch(field){
      case 'title': return touched.title && !form.title
      case 'category': return touched.category && !form.category
      case 'description': return touched.description && !form.description
      case 'price': return touched.price && (!form.price || form.price<=0)
      case 'images': return touched.images && images.value.length===0
      default: return false
    }
  }
  function invalid(field){ return error(field) ? 'is-invalid' : '' }
  
  // Kiểm tra đăng nhập
  function checkAuth() {
    const token = localStorage.getItem('key_client')
    if (!token) {
      showToast('Vui lòng đăng nhập để đăng bán sản phẩm', 'error')
      setTimeout(() => {
        router.push('/dang-nhap')
      }, 1500)
      return false
    }
    return true
  }

  // Kiểm tra trạng thái đăng ký bán hàng
  async function checkSellerStatus() {
    const token = localStorage.getItem('key_client')
    if (!token) return false

    checkingSellerStatus.value = true
    try {
      const { data } = await axios.get(`${API_BASE_URL}/check-seller-status`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })

      if (data?.status && data?.is_seller) {
        isSeller.value = true
        return true
      } else {
        isSeller.value = false
        showToast('Bạn chưa đăng ký bán hàng. Vui lòng đăng ký bán hàng trước.', 'error')
        setTimeout(() => {
          router.push('/dang-ky-ban')
        }, 2000)
        return false
      }
    } catch (err) {
      console.error('Error checking seller status:', err)
      if (err?.response?.status === 401) {
        showToast('Vui lòng đăng nhập', 'error')
        setTimeout(() => {
          router.push('/dang-nhap')
        }, 1500)
      }
      return false
    } finally {
      checkingSellerStatus.value = false
    }
  }

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

  // Submit
  async function onSubmit(){
    if (!checkAuth()) return
    
    // Kiểm tra đăng ký bán hàng
    if (!isSeller.value) {
      const sellerStatus = await checkSellerStatus()
      if (!sellerStatus) {
        return
      }
    }
    
    ;['title','category','description','price','images'].forEach(touch)
    policyError.value = !policyChecked.value
    if(error('title')||error('category')||error('description')||error('price')||error('images')||policyError.value){
      window.scrollTo({ top: 0, behavior: 'smooth' })
      if (policyError.value) {
        showToast('Vui lòng đồng ý với điều khoản sử dụng', 'error')
      }
      return
    }
    
    submitting.value = true
    try{
      const token = localStorage.getItem('key_client')
      if (!token) {
        showToast('Vui lòng đăng nhập', 'error')
        router.push('/dang-nhap')
        return
      }

      const fd = new FormData()
      fd.append('title', form.title)
      fd.append('description', form.description)
      fd.append('category', form.category)
      fd.append('condition', form.condition)
      fd.append('price', String(form.price))
      // Gửi boolean dưới dạng "1" hoặc "0" để Laravel tự convert
      fd.append('negotiable', form.negotiable ? '1' : '0')
      fd.append('quantity', String(form.quantity || 1))
      if (form.brand) fd.append('brand', form.brand)
      if (form.model) fd.append('model', form.model)
      if (form.location) fd.append('location', form.location)
      fd.append('shipping_method', form.shipping.method)
      fd.append('shipping_fee', String(form.shipping.fee || 0))
      if (form.contact.phone) fd.append('contact_phone', form.contact.phone)
      fd.append('contact_hide', form.contact.hidePhone ? '1' : '0')
      form.tags.forEach((t,i)=> fd.append(`tags[${i}]`, t))
      images.value.forEach((it,i)=> fd.append('images[]', it.file, it.file.name || `image_${i}.jpg`))
  
      const { data } = await axios.post(`${API_BASE_URL}/san-pham`, fd, {
        headers: {
          'Content-Type': 'multipart/form-data',
          'Authorization': `Bearer ${token}`
        }
      })
      
      localStorage.removeItem('sell_draft')
      
      if(data?.status){
        showToast(data.message || 'Đăng bán sản phẩm thành công!', 'success')
        setTimeout(() => {
          router.push('/nguoi-ban/san-pham')
        }, 1500)
      } else {
        showToast('Đăng bán thành công!', 'success')
        setTimeout(() => {
          router.push('/nguoi-ban/san-pham')
        }, 1500)
      }
    }catch(err){
      console.error('Error submitting product:', err)
      const msg = err?.response?.data?.message || err?.message || 'Có lỗi xảy ra khi đăng bán sản phẩm'
      showToast(msg, 'error')
      
      // Nếu lỗi 403 (chưa đăng ký bán), redirect về trang đăng ký bán
      if (err?.response?.status === 403) {
        setTimeout(() => {
          router.push('/dang-ky-ban')
        }, 2000)
      }
      // Nếu lỗi authentication, redirect về trang đăng nhập
      else if (err?.response?.status === 401) {
        setTimeout(() => {
          router.push('/dang-nhap')
        }, 2000)
      }
    }finally{
      submitting.value = false
    }
  }
  
  // Lấy danh mục từ API
  async function fetchCategories() {
    loadingCategories.value = true
    try {
      const { data } = await axios.get(`${API_BASE_URL}/danh-muc`)
      const rawCategories = data?.data || data || []
      cats.value = Array.isArray(rawCategories)
        ? rawCategories.map(cat => ({
            id: cat.id,
            slug: cat.slug,
            name: cat.ten_danh_muc || cat.name,
          }))
        : []
    } catch (err) {
      console.warn('Không thể tải danh mục', err)
      // Fallback categories nếu API lỗi
      cats.value = [
        { slug: 'dien-thoai', name: 'Điện thoại' },
        { slug: 'sach', name: 'Sách' },
        { slug: 'do-dien-tu', name: 'Đồ điện tử' },
        { slug: 'may-tinh', name: 'Máy tính' },
        { slug: 'thoi-trang', name: 'Thời trang' },
        { slug: 'do-gia-dung', name: 'Gia dụng' },
      ]
    } finally {
      loadingCategories.value = false
    }
  }

  // Draft
  function saveDraft(){
    const payload = { 
      ...form, 
      images: [] // Không lưu file vào localStorage
    }
    localStorage.setItem('sell_draft', JSON.stringify(payload))
    showToast('Đã lưu nháp thành công!', 'success')
  }
  
  onMounted(async () => {
    // Kiểm tra đăng nhập
    if (!checkAuth()) {
      return
    }
    
    // Kiểm tra trạng thái đăng ký bán hàng
    await checkSellerStatus()
    
    // Load draft nếu có
    const raw = localStorage.getItem('sell_draft')
    if (raw) {
      try {
        const draft = JSON.parse(raw)
        Object.assign(form, draft)
      } catch (e) {
        console.warn('Không thể load draft', e)
      }
    }
    
    // Load categories
    fetchCategories()
  })
  </script>
  
  <style scoped>
  /* Chỉ thêm ít style nhỏ, phần còn lại dùng Bootstrap */
  .invalid-feedback { display:block; }
  </style>
  