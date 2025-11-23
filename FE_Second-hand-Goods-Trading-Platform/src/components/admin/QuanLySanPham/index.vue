<template>
  <div class="container py-4">
    <h3 class="text-center mb-4">📦 Quản Lý Sản Phẩm</h3>

    <!-- Form thêm / cập nhật sản phẩm -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <input
              v-model="form.ten_san_pham"
              type="text"
              class="form-control"
              placeholder="Tên sản phẩm"
            />
          </div>
          <div class="col-md-2">
            <input
              v-model="form.gia"
              type="number"
              class="form-control"
              placeholder="Giá"
            />
          </div>
          <div class="col-md-2">
            <input
              v-model="form.so_luong"
              type="number"
              class="form-control"
              placeholder="Số lượng"
            />
          </div>
          <div class="col-md-3">
            <input
              v-model="form.mo_ta"
              type="text"
              class="form-control"
              placeholder="Mô tả"
            />
          </div>
          <div class="col-md-1 d-grid">
            <button
              @click="isEdit ? updateSanPham() : addSanPham()"
              class="btn btn-success"
            >
              {{ isEdit ? "Cập nhật" : "Thêm" }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Ô tìm kiếm -->
    <div class="input-group mb-3">
      <input
        v-model="search"
        @input="searchSanPham"
        type="text"
        class="form-control"
        placeholder="Tìm kiếm sản phẩm..."
      />
      <button class="btn btn-outline-secondary" @click="getSanPhams">Làm mới</button>
    </div>

    <!-- Bảng danh sách sản phẩm -->
    <table class="table table-bordered table-striped align-middle text-center">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Tên sản phẩm</th>
          <th>Giá</th>
          <th>Số lượng</th>
          <th>Mô tả</th>
          <th>Trạng thái</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(sp, index) in sanPhams" :key="sp.id">
          <td>{{ index + 1 }}</td>
          <td>{{ sp.ten_san_pham }}</td>
          <td>{{ sp.gia.toLocaleString() }}₫</td>
          <td>{{ sp.so_luong }}</td>
          <td>{{ sp.mo_ta }}</td>
          <td>
            <span
              class="badge"
              :class="sp.is_active ? 'bg-success' : 'bg-secondary'"
            >
              {{ sp.is_active ? 'Kích hoạt' : 'Ẩn' }}
            </span>
          </td>
          <td>
            <button class="btn btn-warning btn-sm me-2" @click="editSanPham(sp)">
              Sửa
            </button>
            <button class="btn btn-danger btn-sm" @click="deleteSanPham(sp.id)">
              Xóa
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const apiUrl = "http://localhost:8000/api/san-pham"; // đổi URL cho phù hợp

const sanPhams = ref([]);
const search = ref("");
const isEdit = ref(false);

const form = ref({
  id: null,
  ten_san_pham: "",
  gia: "",
  so_luong: "",
  mo_ta: "",
  is_active: 1,
});

//  Lấy danh sách sản phẩm
const getSanPhams = async () => {
  const res = await axios.get(`${apiUrl}/get-data`);
  sanPhams.value = res.data.data;
};

// Thêm sản phẩm
const addSanPham = async () => {
  if (!form.value.ten_san_pham) return alert("Nhập tên sản phẩm!");
  const res = await axios.post(`${apiUrl}/add`, form.value);
  alert(res.data.message);
  getSanPhams();
  resetForm();
};

// Cập nhật sản phẩm
const updateSanPham = async () => {
  const res = await axios.post(`${apiUrl}/update`, form.value);
  alert(res.data.message);
  getSanPhams();
  resetForm();
};

//  Xóa sản phẩm
const deleteSanPham = async (id) => {
  if (confirm("Bạn có chắc muốn xóa sản phẩm này?")) {
    const res = await axios.post(`${apiUrl}/delete`, { id });
    alert(res.data.message);
    getSanPhams();
  }
};

// Tìm kiếm sản phẩm
const searchSanPham = async () => {
  if (!search.value) return getSanPhams();
  const res = await axios.post(`${apiUrl}/search`, { noi_dung: search.value });
  sanPhams.value = res.data.data;
};

//  Chọn sản phẩm để sửa
const editSanPham = (sp) => {
  form.value = { ...sp };
  isEdit.value = true;
};

//  Reset form
const resetForm = () => {
  form.value = {
    id: null,
    ten_san_pham: "",
    gia: "",
    so_luong: "",
    mo_ta: "",
    is_active: 1,
  };
  isEdit.value = false;
};

// Khi tải trang
onMounted(() => {
  getSanPhams();
});
</script>

<style scoped>
.container {
  max-width: 1000px;
}
</style>
