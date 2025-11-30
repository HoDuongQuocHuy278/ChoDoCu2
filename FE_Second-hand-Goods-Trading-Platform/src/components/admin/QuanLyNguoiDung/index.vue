<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Quản lý tài khoản người dùng</h3>
      <div class="input-group w-50">
        <input v-model="searchQuery" @keyup.enter="searchUsers" type="text" class="form-control" placeholder="Tìm theo tên, email, sđt..." />
        <button class="btn btn-outline-secondary" @click="searchUsers">Tìm</button>
        <button class="btn btn-outline-secondary" @click="resetSearch">Reset</button>
      </div>
    </div>

    <div class="mb-3">
      <button class="btn btn-primary" @click="openAddModal">Thêm tài khoản</button>
      <button class="btn btn-secondary ms-2" @click="fetchUsers">Làm mới</button>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Họ và tên</th>
            <th>Email</th>
            <th>SĐT</th>
            <th>CCCD</th>
            <th>Ngày sinh</th>
            <th>Active</th>
            <th>Blocked</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(user, idx) in users" :key="user.id">
            <td>{{ idx + 1 }}</td>
            <td>{{ user.ho_va_ten }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.so_dien_thoai }}</td>
            <td>{{ user.cccd }}</td>
            <td>{{ formatDate(user.ngay_sinh) }}</td>
            <td>
              <span v-if="user.is_active" class="badge bg-success">Active</span>
              <span v-else class="badge bg-secondary">Inactive</span>
            </td>
            <td>
              <span v-if="user.is_block" class="badge bg-danger">Blocked</span>
              <span v-else class="badge bg-success">Normal</span>
            </td>
            <td>
              <button class="btn btn-sm btn-info me-1" @click="openEditModal(user)">Sửa</button>
              <button class="btn btn-sm btn-danger me-1" @click="confirmDelete(user)">Xóa</button>
              <button class="btn btn-sm btn-warning me-1" @click="toggleBlock(user)">{{ user.is_block ? 'Bỏ block' : 'Block' }}</button>
              <button class="btn btn-sm btn-outline-primary" @click="toggleActive(user)">{{ user.is_active ? 'Vô hiệu' : 'Kích hoạt' }}</button>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="9" class="text-center">Không có dữ liệu</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Add / Edit -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true" ref="userModalEl">
      <div class="modal-dialog">
        <div class="modal-content">
          <form @submit.prevent="saveUser">
            <div class="modal-header">
              <h5 class="modal-title">{{ isEditing ? 'Cập nhật tài khoản' : 'Thêm tài khoản mới' }}</h5>
              <button type="button" class="btn-close" @click="closeModal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-2">
                <label class="form-label">Họ và tên</label>
                <input v-model="form.ho_va_ten" type="text" class="form-control" required />
              </div>
              <div class="mb-2">
                <label class="form-label">Email</label>
                <input v-model="form.email" type="email" class="form-control" required />
              </div>
              <div class="mb-2">
                <label class="form-label">Số điện thoại</label>
                <input v-model="form.so_dien_thoai" type="text" class="form-control" />
              </div>
              <div class="mb-2">
                <label class="form-label">CCCD</label>
                <input v-model="form.cccd" type="text" class="form-control" />
              </div>
              <div class="mb-2">
                <label class="form-label">Ngày sinh</label>
                <input v-model="form.ngay_sinh" type="date" class="form-control" />
              </div>
              <div v-if="isEditing" class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" v-model="form.is_block" />
                <label class="form-check-label">Blocked</label>
              </div>
              <div v-if="isEditing" class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" v-model="form.is_active" />
                <label class="form-check-label">Active</label>
              </div>
              <div v-if="!isEditing" class="mb-2">
                <small class="text-muted">Mật khẩu mặc định khi thêm: "123456" (theo backend của bạn). Nếu bạn muốn đặt khác, hãy chỉnh backend hoặc mở thêm input mật khẩu.</small>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeModal">Hủy</button>
              <button type="submit" class="btn btn-primary">{{ isEditing ? 'Cập nhật' : 'Thêm' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete confirm -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" ref="confirmDeleteModalEl">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-body">
            <p>Bạn có chắc chắn muốn xóa <strong>{{ userToDelete?.ho_va_ten }}</strong> ?</p>
            <div class="d-flex justify-content-end">
              <button class="btn btn-secondary me-2" @click="closeDeleteModal">Hủy</button>
              <button class="btn btn-danger" @click="deleteUserConfirmed">Xóa</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast area -->
    <div aria-live="polite" class="position-relative">
      <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast" ref="toastEl" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="toast-header">
            <strong class="me-auto">Thông báo</strong>
            <small>now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
          </div>
          <div class="toast-body">{{ toastMessage }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      API_GET: '/api/khach-hang/getData',
      API_ADD: '/api/khach-hang/addData',
      API_UPDATE: '/api/khach-hang/update',
      API_DELETE: '/api/khach-hang/destroy',
      API_CHANGE_BLOCK: '/api/khach-hang/changeStatus',
      API_CHANGE_ACTIVE: '/api/khach-hang/changeActive',
      API_SEARCH: '/api/khach-hang/search',
      users: [],
      searchQuery: '',
      isEditing: false,
      form: {
        id: null,
        ho_va_ten: '',
        email: '',
        so_dien_thoai: '',
        password: '',
        cccd: '',
        ngay_sinh: '',
        is_block: 0,
        is_active: 0
      },
      userToDelete: null,
      toastMessage: '',
      userModalInstance: null,
      confirmDeleteModalInstance: null,
      toastInstance: null
    }
  },
  mounted() {
    const TOKEN = null
    if (TOKEN) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${TOKEN}`
    }
    
    // eslint-disable-next-line no-undef
    this.userModalInstance = new bootstrap.Modal(this.$refs.userModalEl)
    // eslint-disable-next-line no-undef
    this.confirmDeleteModalInstance = new bootstrap.Modal(this.$refs.confirmDeleteModalEl)
    // eslint-disable-next-line no-undef
    this.toastInstance = new bootstrap.Toast(this.$refs.toastEl)
    
    this.fetchUsers()
  },
  methods: {
    showToast(msg) {
      this.toastMessage = msg
      this.$refs.toastEl.querySelector('.toast-body').innerText = msg
      this.toastInstance.show()
    },
    
    formatDate(d) {
      if (!d) return ''
      return d.slice(0, 10)
    },
    
    async fetchUsers() {
      try {
        const res = await axios.get(this.API_GET)
        if (res.data && res.data.data) {
          this.users = res.data.data
        } else {
          this.users = res.data || []
        }
      } catch (err) {
        console.error(err)
        this.showToast('Lấy dữ liệu thất bại')
      }
    },
    
    resetSearch() {
      this.searchQuery = ''
      this.fetchUsers()
    },
    
    async searchUsers() {
      try {
        const res = await axios.post(this.API_SEARCH, { noi_dung: this.searchQuery })
        if (res.data && res.data.data) {
          this.users = res.data.data
        }
      } catch (err) {
        console.error(err)
        this.showToast('Tìm kiếm lỗi')
      }
    },
    
    openAddModal() {
      this.isEditing = false
      Object.assign(this.form, {
        id: null,
        ho_va_ten: '',
        email: '',
        so_dien_thoai: '',
        password: '',
        cccd: '',
        ngay_sinh: '',
        is_block: 0,
        is_active: 0
      })
      this.userModalInstance.show()
    },
    
    openEditModal(user) {
      this.isEditing = true
      Object.assign(this.form, {
        id: user.id,
        ho_va_ten: user.ho_va_ten,
        email: user.email,
        so_dien_thoai: user.so_dien_thoai,
        password: user.password || '',
        cccd: user.cccd,
        ngay_sinh: user.ngay_sinh ? user.ngay_sinh.slice(0,10) : '',
        is_block: user.is_block ? 1 : 0,
        is_active: user.is_active ? 1 : 0
      })
      this.userModalInstance.show()
    },
    
    closeModal() {
      this.userModalInstance.hide()
    },
    
    confirmDelete(user) {
      this.userToDelete = user
      this.confirmDeleteModalInstance.show()
    },
    
    closeDeleteModal() {
      this.userToDelete = null
      this.confirmDeleteModalInstance.hide()
    },
    
    async deleteUserConfirmed() {
      try {
        const res = await axios.post(this.API_DELETE, { id: this.userToDelete.id })
        if (res.data && res.data.status) {
          this.showToast(res.data.message || 'Xóa thành công')
          this.fetchUsers()
        } else {
          this.showToast(res.data.message || 'Xóa thất bại')
        }
      } catch (err) {
        console.error(err)
        this.showToast('Lỗi khi xóa')
      } finally {
        this.closeDeleteModal()
      }
    },
    
    async saveUser() {
      try {
        if (this.isEditing) {
          const payload = { ...this.form }
          const res = await axios.post(this.API_UPDATE, payload)
          if (res.data && res.data.status) {
            this.showToast(res.data.message || 'Cập nhật thành công')
            this.fetchUsers()
            this.closeModal()
          } else {
            this.showToast(res.data.message || 'Cập nhật thất bại')
          }
        } else {
          const payload = {
            ho_va_ten: this.form.ho_va_ten,
            email: this.form.email,
            so_dien_thoai: this.form.so_dien_thoai,
            cccd: this.form.cccd,
            ngay_sinh: this.form.ngay_sinh
          }
          const res = await axios.post(this.API_ADD, payload)
          if (res.data && res.data.status) {
            this.showToast(res.data.message || 'Thêm thành công')
            this.fetchUsers()
            this.closeModal()
          } else {
            this.showToast(res.data.message || 'Thêm thất bại')
          }
        }
      } catch (err) {
        console.error(err)
        this.showToast('Lỗi khi lưu dữ liệu')
      }
    },
    
    async toggleBlock(user) {
      try {
        const res = await axios.post(this.API_CHANGE_BLOCK, { id: user.id })
        if (res.data && res.data.status) {
          this.showToast(res.data.message || 'Thay đổi trạng thái block thành công')
          this.fetchUsers()
        } else {
          this.showToast(res.data.message || 'Thao tác thất bại')
        }
      } catch (err) {
        console.error(err)
        this.showToast('Lỗi thay đổi trạng thái')
      }
    },
    
    async toggleActive(user) {
      try {
        const res = await axios.post(this.API_CHANGE_ACTIVE, { id: user.id })
        if (res.data && res.data.status) {
          this.showToast(res.data.message || 'Thay đổi trạng thái active thành công')
          this.fetchUsers()
        } else {
          this.showToast(res.data.message || 'Thao tác thất bại')
        }
      } catch (err) {
        console.error(err)
        this.showToast('Lỗi thay đổi trạng thái')
      }
    }
  }
}
</script>

<style scoped>
.table td, .table th {
  vertical-align: middle;
  white-space: nowrap;
}
</style>
