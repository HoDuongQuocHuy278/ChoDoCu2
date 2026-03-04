<template>
  <div class="dashboard">
    <div class="header-row">
      <h2 class="page-title">Tổng quan</h2>
      <div class="refresh-info">
        <span class="last-update" v-if="lastUpdated">
          <i class="fas fa-sync-alt" :class="{ 'spinning': isLoading }"></i>
          Cập nhật: {{ lastUpdated }}
        </span>
        <button class="btn-refresh" @click="refreshData" :disabled="isLoading">
          <i class="fas fa-redo"></i>
          Làm mới
        </button>
      </div>
    </div>
    
    <!-- Stats Cards -->
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

    <!-- Charts Section -->
    <div class="charts-section">
      <div class="chart-row">
        <!-- Revenue Chart -->
        <div class="chart-container">
          <h3 class="chart-title">
            <i class="fas fa-chart-line"></i>
            Doanh thu 7 ngày gần nhất
          </h3>
          <div class="chart-wrapper">
            <Line v-if="revenueChartData" :data="revenueChartData" :options="lineChartOptions" />
            <div v-else class="chart-loading">
              <i class="fas fa-spinner fa-spin"></i> Đang tải...
            </div>
          </div>
        </div>

        <!-- Orders Chart -->
        <div class="chart-container">
          <h3 class="chart-title">
            <i class="fas fa-chart-bar"></i>
            Đơn hàng 7 ngày gần nhất
          </h3>
          <div class="chart-wrapper">
            <Bar v-if="ordersChartData" :data="ordersChartData" :options="barChartOptions" />
            <div v-else class="chart-loading">
              <i class="fas fa-spinner fa-spin"></i> Đang tải...
            </div>
          </div>
        </div>
      </div>

      <div class="chart-row">
        <!-- Order Status Pie Chart -->
        <div class="chart-container small">
          <h3 class="chart-title">
            <i class="fas fa-chart-pie"></i>
            Trạng thái đơn hàng
          </h3>
          <div class="chart-wrapper pie-wrapper">
            <Doughnut v-if="orderStatusChartData" :data="orderStatusChartData" :options="pieChartOptions" />
            <div v-else class="chart-loading">
              <i class="fas fa-spinner fa-spin"></i> Đang tải...
            </div>
          </div>
        </div>

        <!-- Top Products -->
        <div class="chart-container">
          <h3 class="chart-title">
            <i class="fas fa-trophy"></i>
            Top sản phẩm bán chạy
          </h3>
          <div class="top-products-list">
            <div v-if="topProducts.length === 0" class="chart-loading">
              <i class="fas fa-spinner fa-spin"></i> Đang tải...
            </div>
            <div v-else v-for="(product, index) in topProducts" :key="product.id" class="product-item">
              <span class="rank" :class="'rank-' + (index + 1)">{{ index + 1 }}</span>
              <span class="product-name">{{ product.name }}</span>
              <span class="product-sales">{{ product.sales }} đơn</span>
            </div>
            <div v-if="topProducts.length === 0 && !isLoading" class="no-data">
              Chưa có dữ liệu
            </div>
          </div>
        </div>

        <!-- Payment Methods -->
        <div class="chart-container small">
          <h3 class="chart-title">
            <i class="fas fa-credit-card"></i>
            Phương thức thanh toán
          </h3>
          <div class="chart-wrapper pie-wrapper">
            <Pie v-if="paymentMethodChartData" :data="paymentMethodChartData" :options="pieChartOptions" />
            <div v-else class="chart-loading">
              <i class="fas fa-spinner fa-spin"></i> Đang tải...
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Auto Refresh Timer -->
    <div class="auto-refresh-notice">
      <i class="fas fa-clock"></i>
      Tự động cập nhật sau: {{ formatTime(nextRefreshIn) }}
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ADMIN_API_URL } from '../../../config';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  PointElement,
  CategoryScale,
  LinearScale,
  ArcElement,
  Filler
} from 'chart.js';
import { Line, Bar, Doughnut, Pie } from 'vue-chartjs';

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  PointElement,
  CategoryScale,
  LinearScale,
  ArcElement,
  Filler
);

export default {
  name: "AdminDashboard",
  components: {
    Line,
    Bar,
    Doughnut,
    Pie
  },
  data() {
    return {
      stats: {
        total_users: 0,
        total_products: 0,
        total_orders: 0,
        total_revenue: 0,
      },
      revenueChartData: null,
      ordersChartData: null,
      orderStatusChartData: null,
      paymentMethodChartData: null,
      topProducts: [],
      lastUpdated: null,
      isLoading: false,
      refreshInterval: null,
      countdownInterval: null,
      nextRefreshIn: 600, // 10 phút = 600 giây
      REFRESH_INTERVAL: 600000, // 10 phút = 600000ms
      lineChartOptions: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: (context) => {
                return this.formatCurrency(context.raw);
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: (value) => {
                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
                return value;
              }
            }
          }
        }
      },
      barChartOptions: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      },
      pieChartOptions: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 15,
              usePointStyle: true
            }
          }
        }
      }
    };
  },
  mounted() {
    this.fetchAllData();
    this.startAutoRefresh();
  },
  beforeUnmount() {
    this.stopAutoRefresh();
  },
  methods: {
    async fetchAllData() {
      this.isLoading = true;
      await Promise.all([
        this.fetchStats(),
        this.fetchChartData()
      ]);
      this.isLoading = false;
      this.lastUpdated = new Date().toLocaleTimeString('vi-VN');
    },
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
    async fetchChartData() {
      try {
        const token = localStorage.getItem("token");
        const response = await axios.get(`${ADMIN_API_URL}/chart-data`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        
        if (response.data.status) {
          const data = response.data.data;
          
          // Process Revenue Chart
          this.processRevenueChart(data.revenue_by_day);
          
          // Process Orders Chart
          this.processOrdersChart(data.orders_by_day);
          
          // Process Order Status Chart
          this.processOrderStatusChart(data.order_status);
          
          // Process Payment Methods Chart
          this.processPaymentMethodsChart(data.payment_methods);
          
          // Process Top Products
          this.topProducts = data.top_products || [];
        }
      } catch (error) {
        console.error("Error fetching chart data:", error);
        // Fallback to individual API calls if combined endpoint fails
        await this.fetchChartDataIndividually();
      }
    },
    async fetchChartDataIndividually() {
      const token = localStorage.getItem("token");
      const headers = { Authorization: `Bearer ${token}` };

      try {
        // Revenue
        const revenueRes = await axios.get(`${ADMIN_API_URL}/revenue-by-day`, { headers });
        if (revenueRes.data.status) this.processRevenueChart(revenueRes.data.data);

        // Orders
        const ordersRes = await axios.get(`${ADMIN_API_URL}/orders-by-day`, { headers });
        if (ordersRes.data.status) this.processOrdersChart(ordersRes.data.data);

        // Order Status
        const statusRes = await axios.get(`${ADMIN_API_URL}/order-status-stats`, { headers });
        if (statusRes.data.status) this.processOrderStatusChart(statusRes.data.data);

        // Payment Methods
        const paymentRes = await axios.get(`${ADMIN_API_URL}/payment-method-stats`, { headers });
        if (paymentRes.data.status) this.processPaymentMethodsChart(paymentRes.data.data);

        // Top Products
        const productsRes = await axios.get(`${ADMIN_API_URL}/top-products`, { headers });
        if (productsRes.data.status) this.topProducts = productsRes.data.data;
      } catch (error) {
        console.error("Error fetching individual chart data:", error);
      }
    },
    processRevenueChart(data) {
      const labels = data.map(item => item.date);
      const values = data.map(item => item.revenue);

      this.revenueChartData = {
        labels,
        datasets: [{
          label: 'Doanh thu',
          data: values,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#3b82f6',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 5,
          pointHoverRadius: 7
        }]
      };
    },
    processOrdersChart(data) {
      const labels = data.map(item => item.date);
      const values = data.map(item => item.orders);

      this.ordersChartData = {
        labels,
        datasets: [{
          label: 'Đơn hàng',
          data: values,
          backgroundColor: 'rgba(16, 185, 129, 0.8)',
          borderColor: '#10b981',
          borderWidth: 1,
          borderRadius: 6
        }]
      };
    },
    processOrderStatusChart(data) {
      const labels = data.map(item => item.label);
      const values = data.map(item => item.count);
      const colors = ['#fbbf24', '#3b82f6', '#8b5cf6', '#10b981', '#ef4444'];

      this.orderStatusChartData = {
        labels,
        datasets: [{
          data: values,
          backgroundColor: colors.slice(0, data.length),
          borderWidth: 0
        }]
      };
    },
    processPaymentMethodsChart(data) {
      const labels = data.map(item => item.label);
      const values = data.map(item => item.count);
      const colors = ['#10b981', '#0066b2', '#ef4444', '#d82d8b'];

      this.paymentMethodChartData = {
        labels,
        datasets: [{
          data: values,
          backgroundColor: colors.slice(0, data.length),
          borderWidth: 0
        }]
      };
    },
    refreshData() {
      this.nextRefreshIn = 600;
      this.fetchAllData();
    },
    startAutoRefresh() {
      // Refresh data every 10 minutes
      this.refreshInterval = setInterval(() => {
        this.fetchAllData();
        this.nextRefreshIn = 600;
      }, this.REFRESH_INTERVAL);

      // Countdown timer
      this.countdownInterval = setInterval(() => {
        if (this.nextRefreshIn > 0) {
          this.nextRefreshIn--;
        }
      }, 1000);
    },
    stopAutoRefresh() {
      if (this.refreshInterval) {
        clearInterval(this.refreshInterval);
      }
      if (this.countdownInterval) {
        clearInterval(this.countdownInterval);
      }
    },
    formatTime(seconds) {
      const mins = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return `${mins}:${secs.toString().padStart(2, '0')}`;
    },
    formatCurrency(value) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    },
  },
};
</script>

<style scoped>
.header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.page-title {
  margin: 0;
  color: #1f2937;
  font-size: 28px;
  font-weight: 700;
}

.refresh-info {
  display: flex;
  align-items: center;
  gap: 15px;
}

.last-update {
  font-size: 13px;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 6px;
}

.last-update i {
  font-size: 12px;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.btn-refresh {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s ease;
}

.btn-refresh:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-refresh:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  display: flex;
  align-items: center;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
  border: 1px solid #f3f4f6;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  margin-right: 20px;
  color: #fff;
}

.blue .icon { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
.green .icon { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.orange .icon { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.red .icon { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }

.info h3 {
  margin: 0;
  font-size: 13px;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.info .number {
  margin: 8px 0 0;
  font-size: 26px;
  font-weight: 700;
  color: #1f2937;
}

/* Charts Section */
.charts-section {
  margin-top: 30px;
}

.chart-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

.chart-container {
  background: #fff;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: 1px solid #f3f4f6;
}

.chart-container.small {
  min-width: 300px;
  max-width: 400px;
}

.chart-title {
  margin: 0 0 20px 0;
  font-size: 16px;
  font-weight: 600;
  color: #374151;
  display: flex;
  align-items: center;
  gap: 10px;
}

.chart-title i {
  color: #6b7280;
}

.chart-wrapper {
  height: 280px;
  position: relative;
}

.pie-wrapper {
  height: 250px;
}

.chart-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #9ca3af;
  font-size: 14px;
  gap: 8px;
}

.no-data {
  text-align: center;
  color: #9ca3af;
  padding: 40px;
  font-size: 14px;
}

/* Top Products List */
.top-products-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.product-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  background: #f9fafb;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.product-item:hover {
  background: #f3f4f6;
  transform: translateX(5px);
}

.rank {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 12px;
  margin-right: 12px;
  color: #fff;
  background: #9ca3af;
}

.rank-1 { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); }
.rank-2 { background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%); }
.rank-3 { background: linear-gradient(135deg, #cd7f32 0%, #a0522d 100%); }

.product-name {
  flex: 1;
  font-size: 14px;
  color: #374151;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-sales {
  font-size: 13px;
  color: #10b981;
  font-weight: 600;
  background: rgba(16, 185, 129, 0.1);
  padding: 4px 10px;
  border-radius: 20px;
}

/* Auto Refresh Notice */
.auto-refresh-notice {
  margin-top: 30px;
  padding: 12px 20px;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 8px;
  font-size: 13px;
  color: #0369a1;
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid #bae6fd;
}

/* Responsive */
@media (max-width: 768px) {
  .header-row {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }

  .chart-row {
    grid-template-columns: 1fr;
  }
  
  .chart-container.small {
    max-width: 100%;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
