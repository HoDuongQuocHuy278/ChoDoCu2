<template>
  <div class="orders-page container py-4">
    <header class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3 mb-4">
      <div>
        <h2 class="fw-bold m-0">Quản lý đơn hàng</h2>
        <p class="text-muted mb-0">Theo dõi trạng thái, xử lý thanh toán và xuất hóa đơn cho khách hàng.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-outline-secondary" type="button" @click="fetchOrders">
          <i class="bx bx-refresh me-1"></i> Làm mới
        </button>
        <button class="btn btn-success" type="button" @click="exportExcel">
          <i class="bx bx-download me-1"></i> Xuất Excel
        </button>
      </div>
    </header>

    <section class="card shadow-sm">
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-lg-4 col-md-6">
            <label class="form-label text-muted small mb-1">Tìm kiếm</label>
            <div class="input-group">
              <input v-model.trim="filters.keyword" type="search" class="form-control" placeholder="Mã đơn, email..." @keyup.enter="fetchOrders">
              <button class="btn btn-outline-secondary" type="button" @click="fetchOrders">
                <i class="bx bx-search"></i>
              </button>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <label class="form-label text-muted small mb-1">Trạng thái đơn</label>
            <select v-model="filters.status" class="form-select" @change="fetchOrders">
              <option value="">Tất cả</option>
              <option value="pending">Chờ xử lý</option>
              <option value="processing">Đang giao</option>
              <option value="completed">Hoàn thành</option>
              <option value="cancelled">Đã hủy</option>
            </select>
          </div>
          <div class="col-lg-3 col-md-6">
            <label class="form-label text-muted small mb-1">Thanh toán</label>
            <select v-model="filters.payment_status" class="form-select" @change="fetchOrders">
              <option value="">Tất cả</option>
              <option value="unpaid">Chưa thanh toán</option>
              <option value="paid">Đã thanh toán</option>
              <option value="refunded">Hoàn tiền</option>
            </select>
          </div>
          <div class="col-lg-2 col-md-6 d-grid">
            <button class="btn btn-outline-secondary" type="button" @click="resetFilters">Đặt lại</button>
          </div>
        </div>

        <div v-if="isLoading" class="text-center py-5">
          <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">Đang tải...</span>
          </div>
        </div>

        <div v-else-if="errorMessage" class="alert alert-danger d-flex justify-content-between align-items-center">
          <span>{{ errorMessage }}</span>
          <button class="btn btn-sm btn-light" type="button" @click="fetchOrders">Thử lại</button>
        </div>

        <template v-else>
          <div v-if="orders.length === 0" class="empty-state text-center p-5 border border-dashed rounded-4">
            <div class="emoji mb-2">🧾</div>
            <h5 class="fw-semibold">Chưa có đơn hàng nào</h5>
            <p class="text-muted mb-0">Các đơn mới sẽ hiển thị tại đây khi khách hàng đặt mua.</p>
          </div>

          <div v-else class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Mã đơn</th>
                  <th>Khách hàng</th>
                  <th>Sản phẩm</th>
                  <th>Tổng tiền</th>
                  <th>Thanh toán</th>
                  <th>Trạng thái</th>
                  <th>Ngày đặt</th>
                  <th class="text-end">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in orders" :key="order.id">
                  <td>
                    <div class="fw-semibold">#{{ order.code || order.id }}</div>
                    <small class="text-muted">Kênh: {{ order.channel || 'Website' }}</small>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ order.customer?.name || order.customer_name || 'Khách lẻ' }}</div>
                    <small class="text-muted">{{ order.customer?.email || order.customer_email }}</small>
                  </td>
                  <td>
                    <ul class="list-unstyled small mb-0">
                      <li v-for="item in order.items?.slice(0, 2) || []" :key="item.id">
                        • {{ item.name }} × {{ item.quantity }}
                      </li>
                    </ul>
                    <small v-if="(order.items?.length || 0) > 2" class="text-muted">+{{ order.items.length - 2 }} sản phẩm khác</small>
                  </td>
                  <td class="fw-semibold text-success">{{ formatCurrency(order.total_amount) }}</td>
                  <td>
                    <span :class="['badge', paymentBadge(order.payment_status)]">{{ paymentLabel(order.payment_status) }}</span>
                  </td>
                  <td>
                    <select v-model="order.status" class="form-select form-select-sm" @change="updateStatus(order)">
                      <option value="pending">Chờ xử lý</option>
                      <option value="processing">Đang giao</option>
                      <option value="completed">Hoàn thành</option>
                      <option value="cancelled">Đã hủy</option>
                    </select>
                  </td>
                  <td>{{ formatDate(order.created_at) }}</td>
                  <td class="text-end">
                    <div class="btn-group">
                      <button class="btn btn-sm btn-outline-primary" type="button" @click="openDetail(order)">Chi tiết</button>
                      <button class="btn btn-sm btn-outline-success" type="button" @click="markPaid(order)" :disabled="order.payment_status === 'paid'">
                        Xác nhận TT
                      </button>
                      <button class="btn btn-sm btn-outline-danger" type="button" @click="cancelOrder(order)" :disabled="order.status === 'cancelled'">
                        Hủy đơn
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
      </div>
    </section>

    <div class="modal fade" tabindex="-1" ref="detailModalRef">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Chi tiết đơn #{{ selectedOrder?.code || selectedOrder?.id }}</h5>
            <button type="button" class="btn-close" @click="closeDetail"></button>
          </div>
          <div class="modal-body" v-if="selectedOrder">
            <div class="mb-3">
              <h6 class="fw-semibold">Thông tin khách hàng</h6>
              <p class="mb-1"><strong>Họ tên:</strong> {{ selectedOrder.customer?.name || selectedOrder.customer_name }}</p>
              <p class="mb-1"><strong>Email:</strong> {{ selectedOrder.customer?.email || selectedOrder.customer_email }}</p>
              <p class="mb-1"><strong>Số điện thoại:</strong> {{ selectedOrder.customer?.phone || selectedOrder.customer_phone || '—' }}</p>
              <p class="mb-1"><strong>Địa chỉ:</strong> {{ selectedOrder.customer?.address || selectedOrder.shipping_address || '—' }}</p>
            </div>

            <div class="mb-3">
              <h6 class="fw-semibold">Danh sách sản phẩm</h6>
              <table class="table table-sm align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Tổng</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in selectedOrder.items || []" :key="item.id">
                    <td>{{ item.name }}</td>
                    <td>{{ item.quantity }}</td>
                    <td>{{ formatCurrency(item.price) }}</td>
                    <td>{{ formatCurrency(item.price * item.quantity) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="row">
              <div class="col-md-6">
                <h6 class="fw-semibold">Thanh toán</h6>
                <p class="mb-1"><strong>Phương thức:</strong> {{ selectedOrder.payment_method || 'Tiền mặt' }}</p>
                <p class="mb-1"><strong>Trạng thái:</strong> {{ paymentLabel(selectedOrder.payment_status) }}</p>
              </div>
              <div class="col-md-6 text-md-end">
                <h6 class="fw-semibold">Tổng cộng</h6>
                <p class="mb-1"><strong>Tạm tính:</strong> {{ formatCurrency(selectedOrder.subtotal || selectedOrder.total_amount) }}</p>
                <p class="mb-1"><strong>Phí ship:</strong> {{ formatCurrency(selectedOrder.shipping_fee || 0) }}</p>
                <p class="mb-1"><strong>Giảm giá:</strong> -{{ formatCurrency(selectedOrder.discount || 0) }}</p>
                <p class="mb-0 fs-5 fw-bold text-success">Thành tiền: {{ formatCurrency(selectedOrder.total_amount) }}</p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" @click="closeDetail">Đóng</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
/* global bootstrap */
import { onMounted, reactive, ref } from 'vue'
import axios from 'axios'

const LIST_API = 'http://127.0.0.1:8000/api/admin/orders'
const UPDATE_STATUS_API = id => `http://127.0.0.1:8000/api/admin/orders/${id}/status`
const UPDATE_PAYMENT_API = id => `http://127.0.0.1:8000/api/admin/orders/${id}/payment`
const EXPORT_API = 'http://127.0.0.1:8000/api/admin/orders/export'
const CANCEL_API = id => `http://127.0.0.1:8000/api/admin/orders/${id}/cancel`

const filters = reactive({
  keyword: '',
  status: '',
  payment_status: ''
})

const orders = ref([])
const isLoading = ref(false)
const errorMessage = ref('')
const detailModalRef = ref(null)
const detailInstance = ref(null)
const selectedOrder = ref(null)

onMounted(() => {
  initModal()
  fetchOrders()
})

function initModal() {
  if (typeof bootstrap !== 'undefined' && detailModalRef.value) {
    detailInstance.value = new bootstrap.Modal(detailModalRef.value)
  }
}

async function fetchOrders() {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const { data } = await axios.get(LIST_API, { params: { ...filters } })
    orders.value = data?.data || data || []
  } catch (err) {
    console.error(err)
    errorMessage.value = err?.response?.data?.message || 'Không thể tải danh sách đơn hàng.'
    orders.value = []
  } finally {
    isLoading.value = false
  }
}

function resetFilters() {
  filters.keyword = ''
  filters.status = ''
  filters.payment_status = ''
  fetchOrders()
}

async function updateStatus(order) {
  try {
    await axios.post(UPDATE_STATUS_API(order.id), { status: order.status })
  } catch (err) {
    alert(err?.response?.data?.message || err.message || 'Không thể cập nhật trạng thái.')
  }
}

async function markPaid(order) {
  try {
    await axios.post(UPDATE_PAYMENT_API(order.id), { status: 'paid' })
    order.payment_status = 'paid'
  } catch (err) {
    alert(err?.response?.data?.message || err.message || 'Không thể cập nhật thanh toán.')
  }
}

async function cancelOrder(order) {
  if (!confirm(`Xác nhận hủy đơn #${order.code || order.id}?`)) return
  try {
    await axios.post(CANCEL_API(order.id))
    order.status = 'cancelled'
  } catch (err) {
    alert(err?.response?.data?.message || err.message || 'Không thể hủy đơn hàng.')
  }
}

function openDetail(order) {
  selectedOrder.value = order
  if (!detailInstance.value) {
    initModal()
  }
  detailInstance.value?.show()
}

function closeDetail() {
  detailInstance.value?.hide()
  selectedOrder.value = null
}

async function exportExcel() {
  try {
    const response = await axios.get(EXPORT_API, { params: { ...filters }, responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'orders.xlsx')
    document.body.appendChild(link)
    link.click()
    link.parentNode.removeChild(link)
  } catch (err) {
    alert(err?.response?.data?.message || err.message || 'Không thể xuất file.')
  }
}

function formatCurrency(value) {
  return (Number(value) || 0).toLocaleString('vi-VN') + ' ₫'
}

function formatDate(value) {
  if (!value) return '—'
  try {
    return new Date(value).toLocaleString('vi-VN')
  } catch {
    return value
  }
}

function paymentBadge(status) {
  switch (status) {
    case 'paid':
      return 'bg-success-subtle text-success'
    case 'refunded':
      return 'bg-warning-subtle text-warning'
    default:
      return 'bg-secondary-subtle text-secondary'
  }
}

function paymentLabel(status) {
  switch (status) {
    case 'paid':
      return 'Đã thanh toán'
    case 'refunded':
      return 'Hoàn tiền'
    case 'unpaid':
    default:
      return 'Chưa thanh toán'
  }
}
</script>

<style scoped>
.orders-page {
  min-height: 70vh;
}
.empty-state {
  background: #f8fafc;
}
.empty-state .emoji {
  font-size: 42px;
}
.table td, .table th {
  vertical-align: middle;
}
.modal .table {
  font-size: 14px;
}
</style>

