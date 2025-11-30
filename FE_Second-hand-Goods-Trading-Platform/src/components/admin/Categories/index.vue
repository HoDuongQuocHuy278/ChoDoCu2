<template>
  <div class="categories-page">
    <h2 class="page-title">Quản lý danh mục</h2>

    <div class="toolbar">
      <button @click="openModal()" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm danh mục
      </button>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Hình ảnh</th>
            <th>Tên danh mục</th>
            <th>Slug</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id">
            <td>{{ category.id }}</td>
            <td>
              <img :src="category.hinh_anh || 'https://via.placeholder.com/50'" alt="Category" class="category-img">
            </td>
            <td>{{ category.ten_danh_muc }}</td>
            <td>{{ category.slug }}</td>
            <td>
              <span :class="['status-badge', category.is_active ? 'active' : 'inactive']">
                {{ category.is_active ? 'Hiển thị' : 'Ẩn' }}
              </span>
            </td>
            <td class="actions">
              <button @click="openModal(category)" class="btn-icon" title="Sửa">
                <i class="fas fa-edit"></i>
              </button>
              <button @click="deleteCategory(category)" class="btn-icon delete" title="Xóa">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ isEditing ? 'Cập nhật danh mục' : 'Thêm danh mục mới' }}</h3>
        <form @submit.prevent="saveCategory">
          <div class="form-group">
            <label>Tên danh mục</label>
            <input type="text" v-model="form.ten_danh_muc" required class="form-control">
          </div>
          <div class="form-group">
            <label>Link hình ảnh</label>
            <input type="text" v-model="form.hinh_anh" class="form-control">
          </div>
          <div class="form-group checkbox">
            <label>
              <input type="checkbox" v-model="form.is_active"> Hiển thị
            </label>
          </div>
          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn btn-secondary">Hủy</button>
            <button type="submit" class="btn btn-primary">Lưu</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ADMIN_API_URL } from '../../../config';

export default {
  name: "AdminCategories",
  data() {
    return {
      categories: [],
      showModal: false,
      isEditing: false,
      form: {
        id: null,
        ten_danh_muc: "",
        hinh_anh: "",
        is_active: true,
      },
    };
  },
  mounted() {
    this.fetchCategories();
  },
  methods: {
    async fetchCategories() {
      try {
        const token = localStorage.getItem("token");
        const response = await axios.get(`${ADMIN_API_URL}/categories`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        if (response.data.status) {
          this.categories = response.data.data;
        }
      } catch (error) {
        console.error("Error fetching categories:", error);
      }
    },
    openModal(category = null) {
      if (category) {
        this.isEditing = true;
        this.form = { ...category, is_active: !!category.is_active };
      } else {
        this.isEditing = false;
        this.form = {
          id: null,
          ten_danh_muc: "",
          hinh_anh: "",
          is_active: true,
        };
      }
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    async saveCategory() {
      try {
        const token = localStorage.getItem("token");
        if (this.isEditing) {
          await axios.put(`${ADMIN_API_URL}/categories/${this.form.id}`, this.form, {
            headers: { Authorization: `Bearer ${token}` }
          });
        } else {
          await axios.post(`${ADMIN_API_URL}/categories`, this.form, {
            headers: { Authorization: `Bearer ${token}` }
          });
        }
        this.closeModal();
        this.fetchCategories();
        alert(this.isEditing ? "Cập nhật thành công" : "Thêm mới thành công");
      } catch (error) {
        console.error("Error saving category:", error);
        alert("Lỗi khi lưu danh mục");
      }
    },
    async deleteCategory(category) {
      if (!confirm("Bạn có chắc muốn xóa danh mục này?")) return;

      try {
        const token = localStorage.getItem("token");
        await axios.delete(`${ADMIN_API_URL}/categories/${category.id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.fetchCategories();
        alert("Xóa danh mục thành công");
      } catch (error) {
        console.error("Error deleting category:", error);
        alert(error.response?.data?.message || "Lỗi khi xóa danh mục");
      }
    },
  },
};
</script>

<style scoped>
.page-title {
  margin-bottom: 20px;
  color: #333;
}

.toolbar {
  margin-bottom: 20px;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-primary {
  background-color: #007bff;
  color: #fff;
}

.btn-secondary {
  background-color: #6c757d;
  color: #fff;
}

.table-container {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.data-table th {
  background-color: #f8f9fa;
  font-weight: 600;
  color: #495057;
}

.category-img {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 4px;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.active {
  background-color: #d4edda;
  color: #155724;
}

.status-badge.inactive {
  background-color: #e2e3e5;
  color: #383d41;
}

.actions {
  display: flex;
  gap: 5px;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  padding: 5px;
  color: #6c757d;
  transition: color 0.2s;
}

.btn-icon:hover {
  color: #007bff;
}

.btn-icon.delete:hover {
  color: #dc3545;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  width: 400px;
  max-width: 90%;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-control {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.form-group.checkbox {
  display: flex;
  align-items: center;
}

.form-group.checkbox input {
  margin-right: 10px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}
</style>
