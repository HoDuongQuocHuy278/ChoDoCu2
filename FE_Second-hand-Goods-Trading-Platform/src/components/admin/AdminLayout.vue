<template>
  <div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">
        <h2>Admin Panel</h2>
      </div>
      <nav class="nav-menu">
        <router-link to="/admin/dashboard" class="nav-item" active-class="active">
          <i class="fas fa-chart-line"></i> Dashboard
        </router-link>
        <router-link to="/admin/users" class="nav-item" active-class="active">
          <i class="fas fa-users"></i> Người dùng
        </router-link>
        <router-link to="/admin/categories" class="nav-item" active-class="active">
          <i class="fas fa-list"></i> Danh mục
        </router-link>
        <router-link to="/admin/products" class="nav-item" active-class="active">
          <i class="fas fa-box"></i> Sản phẩm
        </router-link>
        <router-link to="/admin/orders" class="nav-item" active-class="active">
          <i class="fas fa-shopping-cart"></i> Đơn hàng
        </router-link>
      </nav>
      <div class="logout">
        <button @click="logout" class="logout-btn">
          <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="header">
        <div class="user-info">
          <span>Xin chào, Admin</span>
        </div>
      </header>
      <div class="content-body">
        <router-view></router-view>
      </div>
    </main>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "AdminLayout",
  methods: {
    logout() {
      if (confirm("Bạn có chắc muốn đăng xuất?")) {
        localStorage.removeItem("token");
        localStorage.removeItem("key_client");
        localStorage.removeItem("user_role");
        localStorage.removeItem("user_info");
        this.$router.push("/dang-nhap");
      }
    },
  },
};
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f4f6f9;
  font-family: 'Inter', sans-serif;
}

.sidebar {
  width: 250px;
  background-color: #343a40;
  color: #fff;
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  left: 0;
  top: 0;
}

.logo {
  padding: 20px;
  text-align: center;
  border-bottom: 1px solid #4b545c;
}

.logo h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
}

.nav-menu {
  flex: 1;
  padding: 20px 0;
}

.nav-item {
  display: block;
  padding: 12px 20px;
  color: #c2c7d0;
  text-decoration: none;
  transition: all 0.3s;
  font-size: 1rem;
}

.nav-item:hover,
.nav-item.active {
  background-color: #007bff;
  color: #fff;
}

.nav-item i {
  margin-right: 10px;
  width: 20px;
  text-align: center;
}

.logout {
  padding: 20px;
  border-top: 1px solid #4b545c;
}

.logout-btn {
  width: 100%;
  padding: 10px;
  background-color: #dc3545;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.3s;
}

.logout-btn:hover {
  background-color: #c82333;
}

.main-content {
  flex: 1;
  margin-left: 250px;
  display: flex;
  flex-direction: column;
}

.header {
  background-color: #fff;
  padding: 15px 30px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.user-info {
  font-weight: 600;
  color: #333;
}

.content-body {
  padding: 30px;
  flex: 1;
  overflow-y: auto;
}
</style>
