<template>
  <div class="users-page">
    <h2 class="page-title">Quản lý người dùng</h2>

    <div class="toolbar">
      <input 
        type="text" 
        v-model="searchQuery" 
        @keyup.enter="fetchUsers" 
        placeholder="Tìm kiếm theo tên, email, sđt..." 
        class="search-input"
      />
      <button @click="fetchUsers" class="btn btn-primary">Tìm kiếm</button>
    </div>

    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>SĐT</th>
            <th>Vai trò</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.id }}</td>
            <td>{{ user.ho_va_ten }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.so_dien_thoai }}</td>
            <td>
              <select 
                v-model="user.role" 
                @change="updateRole(user)"
                class="role-select"
                :class="{ 'admin': user.role == 1 }"
              >
                <option :value="0">User</option>
                <option :value="1">Admin</option>
              </select>
            </td>
            <td>
              <span :class="['status-badge', user.is_block ? 'blocked' : 'active']">
                {{ user.is_block ? 'Đã khóa' : 'Hoạt động' }}
              </span>
            </td>
            <td class="actions">
              <button 
                @click="toggleBlock(user)" 
                class="btn-icon" 
                :title="user.is_block ? 'Mở khóa' : 'Khóa'"
              >
                <i :class="['fas', user.is_block ? 'fa-unlock' : 'fa-lock']"></i>
              </button>
              <button 
                @click="deleteUser(user)" 
                class="btn-icon delete" 
                title="Xóa"
              >
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="totalPages > 1">
      <button 
        :disabled="currentPage === 1" 
        @click="changePage(currentPage - 1)"
      >
        &laquo;
      </button>
      <span>Trang {{ currentPage }} / {{ totalPages }}</span>
      <button 
        :disabled="currentPage === totalPages" 
        @click="changePage(currentPage + 1)"
      >
        &raquo;
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ADMIN_API_URL } from '../../../config';

export default {
  name: "AdminUsers",
  data() {
    return {
      users: [],
      searchQuery: "",
      currentPage: 1,
      totalPages: 1,
    };
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      try {
        const token = localStorage.getItem("token");
        const response = await axios.get(`${ADMIN_API_URL}/users`, {
          params: {
            page: this.currentPage,
            q: this.searchQuery,
          },
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        if (response.data.status) {
          this.users = response.data.data.data;
          this.totalPages = response.data.data.last_page;
        }
      } catch (error) {
        console.error("Error fetching users:", error);
      }
    },
    async updateRole(user) {
      try {
        const token = localStorage.getItem("token");
        await axios.post(`${ADMIN_API_URL}/users/${user.id}/role`, {
          role: user.role
        }, {
          headers: { Authorization: `Bearer ${token}` }
        });
        alert("Cập nhật quyền thành công!");
      } catch (error) {
        console.error("Error updating role:", error);
        alert("Lỗi khi cập nhật quyền");
      }
    },
    async toggleBlock(user) {
      if (!confirm(`Bạn có chắc muốn ${user.is_block ? 'mở khóa' : 'khóa'} tài khoản này?`)) return;
      
      try {
        const token = localStorage.getItem("token");
        const response = await axios.post(`${ADMIN_API_URL}/users/${user.id}/block`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        if (response.data.status) {
          user.is_block = !user.is_block;
          alert(response.data.message);
        }
      } catch (error) {
        console.error("Error toggling block:", error);
        alert("Lỗi khi thay đổi trạng thái");
      }
    },
    async deleteUser(user) {
      if (!confirm("Bạn có chắc muốn xóa người dùng này? Hành động này không thể hoàn tác!")) return;

      try {
        const token = localStorage.getItem("token");
        const response = await axios.delete(`${ADMIN_API_URL}/users/${user.id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        if (response.data.status) {
          this.fetchUsers();
          alert("Xóa người dùng thành công");
        }
      } catch (error) {
        console.error("Error deleting user:", error);
        alert("Lỗi khi xóa người dùng");
      }
    },
    changePage(page) {
      this.currentPage = page;
      this.fetchUsers();
    }
  },
};
</script>

<style scoped>
.page-title {
  margin-bottom: 20px;
  color: #333;
}

.toolbar {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.search-input {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  width: 300px;
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

.role-select {
  padding: 4px 8px;
  border-radius: 4px;
  border: 1px solid #ddd;
}

.role-select.admin {
  color: #007bff;
  font-weight: 600;
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

.status-badge.blocked {
  background-color: #f8d7da;
  color: #721c24;
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

.pagination {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
}

.pagination button {
  padding: 5px 10px;
  border: 1px solid #ddd;
  background: #fff;
  cursor: pointer;
}

.pagination button:disabled {
  background: #eee;
  cursor: not-allowed;
}
</style>
