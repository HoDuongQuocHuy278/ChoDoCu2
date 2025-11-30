<template>
  <div class="dashboard">
    <h2 class="page-title">Tổng quan</h2>
    
    <div class="stats-grid">
      <div class="stat-card blue">
        <div class="icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="info">
          <h3>Người dùng</h3>
          <p class="number">{{ stats.total_users }}</p>
        </div>
      </div>
      
      <div class="stat-card green">
        <div class="icon">
          <i class="fas fa-box"></i>
        </div>
        <div class="info">
          <h3>Sản phẩm</h3>
          <p class="number">{{ stats.total_products }}</p>
        </div>
      </div>
      
      <div class="stat-card orange">
        <div class="icon">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="info">
          <h3>Đơn hàng</h3>
          <p class="number">{{ stats.total_orders }}</p>
        </div>
      </div>
      
      <div class="stat-card red">
        <div class="icon">
          <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="info">
          <h3>Doanh thu</h3>
          <p class="number">{{ formatCurrency(stats.total_revenue) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ADMIN_API_URL } from '../../../config';

export default {
  name: "AdminDashboard",
  data() {
    return {
      stats: {
        total_users: 0,
        total_products: 0,
        total_orders: 0,
        total_revenue: 0,
      },
    };
  },
  mounted() {
    this.fetchStats();
  },
  methods: {
    async fetchStats() {
      try {
        const token = localStorage.getItem("token");
        const response = await axios.get(`${ADMIN_API_URL}/stats`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        if (response.data.status) {
          this.stats = response.data.data;
        }
      } catch (error) {
        console.error("Error fetching stats:", error);
      }
    },
    formatCurrency(value) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    },
  },
};
</script>

<style scoped>
.page-title {
  margin-bottom: 30px;
  color: #333;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.stat-card {
  background: #fff;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  transition: transform 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  margin-right: 20px;
  color: #fff;
}

.blue .icon { background-color: #007bff; }
.green .icon { background-color: #28a745; }
.orange .icon { background-color: #fd7e14; }
.red .icon { background-color: #dc3545; }

.info h3 {
  margin: 0;
  font-size: 14px;
  color: #6c757d;
  text-transform: uppercase;
}

.info .number {
  margin: 5px 0 0;
  font-size: 24px;
  font-weight: 700;
  color: #333;
}
</style>
