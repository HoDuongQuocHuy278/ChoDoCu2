<template>
  <div class="topbar-modern">
    <nav class="navbar-modern">
      <!-- Logo & Brand -->
      <div class="navbar-brand-modern">
        <router-link to="/trang-chu" class="brand-link">
          <div class="brand-logo">
            <img src="../../assets/images/logo-icon.png" class="logo-icon" alt="Chợ Đồ Cũ" @error="onLogoError">
          </div>
          <div class="brand-text">
            <h4 class="brand-title">Chợ Đồ Cũ</h4>
            <p class="brand-subtitle">Mua bán đồ cũ uy tín</p>
          </div>
        </router-link>
      </div>

      <!-- Mobile Menu Toggle -->
      <button class="mobile-menu-toggle" type="button" @click="toggleMobileMenu" aria-label="Toggle menu">
        <i class='bx bx-menu'></i>
      </button>

      <!-- Search Bar -->
      <div class="search-container">
        <form @submit.prevent="handleSearch" class="search-form">
          <div class="search-input-wrapper">
            <input
              v-model="searchQuery"
              type="text"
              class="search-input"
              placeholder="Tìm kiếm sản phẩm..."
              @keyup.enter="handleSearch"
            >
            <button type="submit" class="search-btn" aria-label="Search">
              <i class='bx bx-search'></i>
            </button>
          </div>
        </form>
      </div>

      <!-- Right Menu -->
      <div class="navbar-actions">
        <ul class="navbar-nav-modern">
          <!-- Cart Icon (only when logged in) -->
          <li v-if="isLoggedIn" class="nav-item-modern">
            <router-link to="/gio-hang" class="nav-link-modern cart-link" title="Giỏ hàng">
              <div class="icon-wrapper position-relative">
                <i class='bx bx-cart-alt'></i>
                <span v-if="cartCount > 0" class="cart-badge">{{ cartCount > 99 ? '99+' : cartCount }}</span>
              </div>
              <span class="nav-label">Giỏ hàng</span>
            </router-link>
          </li>

          <!-- Messages Icon (only when logged in) -->
          <li v-if="isLoggedIn" class="nav-item-modern">
            <router-link to="/listchat" class="nav-link-modern messages-link" title="Tin nhắn">
              <div class="icon-wrapper position-relative">
                <i class='bx bx-message-rounded-dots'></i>
                <span v-if="unreadMessagesCount > 0" class="messages-badge">
                  {{ unreadMessagesCount > 99 ? '99+' : unreadMessagesCount }}
                </span>
              </div>
              <span class="nav-label">Tin nhắn</span>
            </router-link>
          </li>

          <!-- Notifications -->
          <li v-if="isLoggedIn" class="nav-item-modern dropdown-modern">
            <button
              class="nav-link-modern dropdown-toggle-modern"
              type="button"
              @click="toggleNotifications"
              :aria-expanded="showNotifications"
              aria-label="Notifications"
            >
              <div class="icon-wrapper position-relative">
                <i class='bx bx-bell'></i>
                <span v-if="unreadNotificationsCount > 0" class="notification-badge">
                  {{ unreadNotificationsCount > 99 ? '99+' : unreadNotificationsCount }}
                </span>
              </div>
              <span class="nav-label">Thông báo</span>
            </button>
            <div v-show="showNotifications" class="dropdown-menu-modern notification-dropdown">
              <div class="dropdown-header">
                <h6 class="dropdown-title">Thông báo</h6>
                <button
                  v-if="unreadNotificationsCount > 0"
                  class="btn-mark-all-read"
                  type="button"
                  @click="markAllAsRead"
                >
                  Đánh dấu đã đọc
                </button>
              </div>
              <div class="dropdown-body">
                <div v-if="notifications.length === 0" class="empty-notifications">
                  <i class='bx bx-bell-off'></i>
                  <p>Chưa có thông báo nào</p>
                </div>
                <div v-else class="notifications-list">
                  <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="notification-item"
                    :class="{ unread: !notification.is_read }"
                    @click="handleNotificationClick(notification)"
                  >
                    <div class="notification-icon" :class="getNotificationIconClass(notification.type)">
                      <span v-if="notification.icon">{{ notification.icon }}</span>
                      <i v-else :class="getNotificationIcon(notification.type)"></i>
                    </div>
                    <div class="notification-content">
                      <h6 class="notification-title">{{ notification.title }}</h6>
                      <p class="notification-message">{{ notification.message }}</p>
                      <span class="notification-time">{{ formatTime(notification.created_at) }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="notifications.length > 0" class="dropdown-footer">
                <router-link to="/thong-bao" class="view-all-link">Xem tất cả thông báo</router-link>
              </div>
            </div>
          </li>

          <!-- User Menu -->
          <li class="nav-item-modern dropdown-modern">
            <button
              v-if="isLoggedIn"
              class="nav-link-modern user-menu-toggle"
              type="button"
              @click="toggleUserMenu"
              :aria-expanded="showUserMenu"
            >
              <i class='bx bx-user'></i>
              <div class="user-info">
                <span class="user-name">{{ userName }}</span>
                <span class="user-role">{{ userRole }}</span>
              </div>
              <i class='bx bx-chevron-down'></i>
            </button>
            <router-link v-else to="/dang-nhap" class="nav-link-modern login-link">
              <i class='bx bx-user'></i>
              <span>Đăng nhập</span>
            </router-link>
            <div v-if="isLoggedIn && showUserMenu" class="dropdown-menu-modern user-dropdown">
              <div class="dropdown-header user-header">
                <div class="user-icon-large">
                  <i class='bx bx-user'></i>
                </div>
                <div class="user-details">
                  <h6 class="user-name-large">{{ userName }}</h6>
                  <p class="user-email">{{ userEmail }}</p>
                </div>
              </div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-body">
                <router-link to="/profile" class="dropdown-item-modern" @click="closeUserMenu">
                  <i class='bx bx-user'></i>
                  <span>Thông tin tài khoản</span>
                </router-link>
                <router-link to="/nguoi-ban/san-pham" class="dropdown-item-modern" @click="closeUserMenu">
                  <i class='bx bx-package'></i>
                  <span>Sản phẩm của tôi</span>
                </router-link>
                <router-link to="/trang-ban-hang" class="dropdown-item-modern" @click="closeUserMenu">
                  <i class='bx bx-store'></i>
                  <span>Trang bán hàng</span>
                </router-link>
                <div class="dropdown-divider"></div>
                <button class="dropdown-item-modern logout-item" type="button" @click="handleLogout">
                  <i class='bx bx-log-out'></i>
                  <span>Đăng xuất</span>
                </button>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Overlay for mobile menu -->
    <div v-if="showMobileMenu" class="mobile-overlay" @click="toggleMobileMenu"></div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const CART_STORAGE_KEY = 'fe_marketplace_cart'
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/client'

// State
const searchQuery = ref('')
const isLoggedIn = ref(false)
const cartCount = ref(0)
const unreadMessagesCount = ref(0)
const showNotifications = ref(false)
const showUserMenu = ref(false)
const showMobileMenu = ref(false)
const notifications = ref([])
const userInfo = ref(null)

// Computed
const unreadNotificationsCount = computed(() => {
  return notifications.value.filter(n => !n.is_read).length
})

const userName = computed(() => {
  if (!userInfo.value) return 'Người dùng'
  return userInfo.value.ho_va_ten || userInfo.value.name || 'Người dùng'
})

const userEmail = computed(() => {
  if (!userInfo.value) return ''
  return userInfo.value.email || ''
})

const userRole = computed(() => {
  if (!userInfo.value) return 'Khách'
  // Có thể check role từ userInfo
  return 'Người mua'
})

// Methods
function checkAuth() {
  const token = localStorage.getItem('key_client')
  const wasLoggedIn = isLoggedIn.value
  isLoggedIn.value = !!token
  if (isLoggedIn.value) {
    loadUserInfo()
    loadCart()
    // Only start polling if user just logged in
    if (!wasLoggedIn) {
      loadNotifications()
      loadUnreadMessagesCount()
      startNotificationPolling()
      startMessagesPolling()
    }
    // If already logged in, polling is already running, don't call again
  } else {
    cartCount.value = 0
    unreadMessagesCount.value = 0
    notifications.value = []
    stopNotificationPolling()
    stopMessagesPolling()
    isLoadingNotifications = false
    isLoadingMessages = false
    lastNotificationLoad = 0
    lastMessagesLoad = 0
  }
}

function loadUserInfo() {
  try {
    const stored = localStorage.getItem('user_info')
    if (stored) {
      userInfo.value = JSON.parse(stored)
    } else {
      // Nếu chưa có, có thể gọi API để lấy
      fetchUserInfo()
    }
  } catch (e) {
    console.warn('Không thể load user info', e)
    fetchUserInfo()
  }
}

async function fetchUserInfo() {
  try {
    const token = localStorage.getItem('key_client')
    if (!token) return
    
    const { data } = await axios.get(`${API_BASE_URL}/thong-tin`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    const payload = data?.data || data
    if (payload) {
      userInfo.value = payload
      localStorage.setItem('user_info', JSON.stringify(payload))
    }
  } catch (err) {
    console.warn('Không thể tải thông tin người dùng', err)
  }
}

function loadCart() {
  try {
    const cartData = localStorage.getItem(CART_STORAGE_KEY)
    if (cartData) {
      const cartItems = JSON.parse(cartData)
      cartCount.value = Array.isArray(cartItems) 
        ? cartItems.reduce((sum, item) => sum + (item.quantity || 1), 0)
        : 0
    } else {
      cartCount.value = 0
    }
  } catch (e) {
    console.warn('Không thể load giỏ hàng', e)
    cartCount.value = 0
  }
}

async function loadNotifications() {
  if (!isLoggedIn.value) {
    notifications.value = []
    return
  }
  
  // Prevent duplicate calls
  if (isLoadingNotifications) {
    return
  }
  
  const now = Date.now()
  // Use cache if recent
  if (now - lastNotificationLoad < NOTIFICATION_CACHE_TIME) {
    return
  }
  
  isLoadingNotifications = true
  lastNotificationLoad = now
  
  try {
    const token = localStorage.getItem('key_client')
    const { data } = await axios.get(`${API_BASE_URL}/thong-bao`, {
      headers: {
        'Authorization': `Bearer ${token}`
      },
      params: {
        per_page: 10, // Chỉ lấy 10 thông báo gần nhất cho dropdown
        unread_only: false
      }
    })
    
    if (data?.status) {
      notifications.value = data.data?.data || []
    }
  } catch (err) {
    console.warn('Không thể tải thông báo', err)
    notifications.value = []
  } finally {
    isLoadingNotifications = false
  }
}

async function loadUnreadMessagesCount() {
  if (!isLoggedIn.value) {
    unreadMessagesCount.value = 0
    return
  }
  
  // Prevent duplicate calls
  if (isLoadingMessages) {
    return
  }
  
  const now = Date.now()
  // Use cache if recent
  if (now - lastMessagesLoad < MESSAGES_CACHE_TIME) {
    return
  }
  
  isLoadingMessages = true
  lastMessagesLoad = now
  
  try {
    const token = localStorage.getItem('key_client')
    const { data } = await axios.get(`${API_BASE_URL}/chat/unread-count`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    
    if (data?.status && data?.data) {
      unreadMessagesCount.value = data.data.count || 0
    } else {
      unreadMessagesCount.value = 0
    }
  } catch (err) {
    console.warn('Không thể tải số lượng tin nhắn chưa đọc', err)
    unreadMessagesCount.value = 0
  } finally {
    isLoadingMessages = false
  }
}

function handleSearch() {
  if (!searchQuery.value.trim()) return
  router.push({
    path: '/search',
    query: { q: searchQuery.value.trim() }
  })
}

function toggleNotifications() {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value) {
    showUserMenu.value = false
  }
}

function toggleUserMenu() {
  showUserMenu.value = !showUserMenu.value
  if (showUserMenu.value) {
    showNotifications.value = false
  }
}

function closeUserMenu() {
  showUserMenu.value = false
}

function toggleMobileMenu() {
  showMobileMenu.value = !showMobileMenu.value
}

async function handleNotificationClick(notification) {
  if (notification.action_url) {
    router.push(notification.action_url)
  }
  // Đánh dấu đã đọc
  if (!notification.is_read) {
    await markAsRead(notification.id)
  }
  showNotifications.value = false
}

async function markAsRead(notificationId) {
  const index = notifications.value.findIndex(n => n.id === notificationId)
  if (index > -1 && !notifications.value[index].is_read) {
    try {
      const token = localStorage.getItem('key_client')
      await axios.post(`${API_BASE_URL}/thong-bao/${notificationId}/mark-read`, {}, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      notifications.value[index].is_read = true
      notifications.value[index].read_at = new Date().toISOString()
    } catch (err) {
      console.warn('Không thể đánh dấu đã đọc', err)
    }
  }
}

async function markAllAsRead() {
  try {
    const token = localStorage.getItem('key_client')
    await axios.post(`${API_BASE_URL}/thong-bao/mark-all-read`, {}, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    notifications.value.forEach(n => {
      n.is_read = true
      n.read_at = new Date().toISOString()
    })
  } catch (err) {
    console.warn('Không thể đánh dấu tất cả đã đọc', err)
  }
}

function handleLogout() {
  if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
    localStorage.removeItem('key_client')
    localStorage.removeItem('user_info')
    isLoggedIn.value = false
    userInfo.value = null
    cartCount.value = 0
    notifications.value = []
    showUserMenu.value = false
    router.push('/trang-chu')
  }
}

function getNotificationIcon(type) {
  const icons = {
    'order': 'bx bx-package',
    'product': 'bx bx-box',
    'message': 'bx bx-message',
    'system': 'bx bx-info-circle'
  }
  return icons[type] || 'bx bx-bell'
}

function getNotificationIconClass(type) {
  const classes = {
    'order': 'icon-order',
    'product': 'icon-product',
    'message': 'icon-message',
    'system': 'icon-system'
  }
  return classes[type] || 'icon-default'
}

function formatTime(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)
  
  if (minutes < 1) return 'Vừa xong'
  if (minutes < 60) return `${minutes} phút trước`
  if (hours < 24) return `${hours} giờ trước`
  if (days < 7) return `${days} ngày trước`
  return date.toLocaleDateString('vi-VN')
}

function getDefaultLogo() {
  // Return a data URI for a simple default logo
  return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iMTIiIGZpbGw9IiMxNmEzNGEiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAyTDIgN0wyIDE3TDEyIDIyTDIyIDE3TDIyIDdMMTIgMloiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo8L3N2Zz4K'
}

function onLogoError(event) {
  event.target.src = getDefaultLogo()
  event.target.onerror = null // Prevent infinite loop
}

// Watch for route changes to refresh cart
watch(() => router.currentRoute.value.path, () => {
  loadCart()
})

// Watch for cart changes in localStorage
function watchCartChanges() {
  // Poll for cart changes (localStorage không trigger event trong cùng tab)
  const cartInterval = setInterval(() => {
    loadCart()
  }, 2000) // Check every 2 seconds
  
  // Also listen for storage events from other tabs
  const handleStorageChange = (e) => {
    if (e.key === CART_STORAGE_KEY) {
      loadCart()
    }
  }
  
  window.addEventListener('storage', handleStorageChange)
  
  return () => {
    clearInterval(cartInterval)
    window.removeEventListener('storage', handleStorageChange)
  }
}

// Close dropdowns when clicking outside
function setupClickOutside() {
  const handleClickOutside = (e) => {
    if (!e.target.closest('.dropdown-modern') && !e.target.closest('.nav-item-modern')) {
      showNotifications.value = false
      showUserMenu.value = false
    }
  }
  
  document.addEventListener('click', handleClickOutside)
  
  return () => {
    document.removeEventListener('click', handleClickOutside)
  }
}

// Listen for cart update events
function setupCartEventListeners() {
  const handleCartUpdate = () => {
    loadCart()
  }
  
  // Listen for custom event from other components
  window.addEventListener('cart-updated', handleCartUpdate)
  
  // Reload cart when window gets focus
  const handleFocus = () => {
    if (isLoggedIn.value) {
      loadCart()
    }
  }
  
  window.addEventListener('focus', handleFocus)
  
  return () => {
    window.removeEventListener('cart-updated', handleCartUpdate)
    window.removeEventListener('focus', handleFocus)
  }
}

let cleanupCartWatcher = null
let cleanupClickOutside = null
let cleanupCartEvents = null

// Polling notifications
let notificationInterval = null
let isLoadingNotifications = false
let lastNotificationLoad = 0
const NOTIFICATION_CACHE_TIME = 5000 // Cache for 5 seconds

function startNotificationPolling() {
  if (!isLoggedIn.value) return
  
  // Stop existing interval if any
  stopNotificationPolling()
  
  // Load notifications immediately
  loadNotifications()
  
  // Poll every 60 seconds (reduced frequency)
  notificationInterval = setInterval(() => {
    if (isLoggedIn.value && !isLoadingNotifications) {
      const now = Date.now()
      // Only load if cache expired
      if (now - lastNotificationLoad > NOTIFICATION_CACHE_TIME) {
        loadNotifications()
      }
    }
  }, 60000) // Increased from 30s to 60s
}

function stopNotificationPolling() {
  if (notificationInterval) {
    clearInterval(notificationInterval)
    notificationInterval = null
  }
}

// Polling messages
let messagesInterval = null
let isLoadingMessages = false
let lastMessagesLoad = 0
const MESSAGES_CACHE_TIME = 5000 // Cache for 5 seconds

function startMessagesPolling() {
  if (!isLoggedIn.value) return
  
  // Stop existing interval if any
  stopMessagesPolling()
  
  // Load unread messages count immediately
  loadUnreadMessagesCount()
  
  // Poll every 60 seconds (reduced frequency)
  messagesInterval = setInterval(() => {
    if (isLoggedIn.value && !isLoadingMessages) {
      const now = Date.now()
      // Only load if cache expired
      if (now - lastMessagesLoad > MESSAGES_CACHE_TIME) {
        loadUnreadMessagesCount()
      }
    }
  }, 60000) // Increased from 20s to 60s
}

function stopMessagesPolling() {
  if (messagesInterval) {
    clearInterval(messagesInterval)
    messagesInterval = null
  }
}

onMounted(() => {
  checkAuth()
  loadCart()
  cleanupCartWatcher = watchCartChanges()
  cleanupClickOutside = setupClickOutside()
  cleanupCartEvents = setupCartEventListeners()
  // Polling will be started in checkAuth if user is logged in
  // Don't start here to avoid duplicate intervals
})

onUnmounted(() => {
  if (cleanupCartWatcher) cleanupCartWatcher()
  if (cleanupClickOutside) cleanupClickOutside()
  if (cleanupCartEvents) cleanupCartEvents()
  stopNotificationPolling()
  stopMessagesPolling()
})
</script>

<style scoped>
.topbar-modern {
  background: #ffffff;
  border-bottom: none; /* Remove border, MenuRocker will handle it */
  margin: 0;
  padding: 0;
  /* Removed sticky and shadow - will be handled by header-wrapper */
}

.navbar-modern {
  display: flex;
  align-items: center;
  padding: 12px 24px;
  gap: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

/* Brand */
.navbar-brand-modern {
  flex-shrink: 0;
}

.brand-link {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: inherit;
  transition: opacity 0.2s;
}

.brand-link:hover {
  opacity: 0.8;
}

.brand-logo {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  overflow: hidden;
  background: linear-gradient(135deg, #16a34a, #22c55e);
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-icon {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-title {
  font-size: 20px;
  font-weight: 700;
  color: #16a34a;
  margin: 0;
  line-height: 1.2;
}

.brand-subtitle {
  font-size: 12px;
  color: #6b7280;
  margin: 0;
  line-height: 1;
}

/* Mobile Menu Toggle */
.mobile-menu-toggle {
  display: none;
  background: none;
  border: none;
  font-size: 24px;
  color: #374151;
  cursor: pointer;
  padding: 8px;
}

/* Search */
.search-container {
  flex: 1;
  max-width: 600px;
  margin: 0 auto;
}

.search-form {
  width: 100%;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input {
  width: 100%;
  padding: 10px 48px 10px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 24px;
  font-size: 14px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #16a34a;
  box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.search-btn {
  position: absolute;
  right: 4px;
  background: #16a34a;
  color: white;
  border: none;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
}

.search-btn:hover {
  background: #15803d;
}

/* Navbar Actions */
.navbar-actions {
  flex-shrink: 0;
}

.navbar-nav-modern {
  display: flex;
  align-items: center;
  gap: 8px;
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-item-modern {
  position: relative;
}

.nav-link-modern {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 12px;
  text-decoration: none;
  color: #374151;
  font-size: 14px;
  font-weight: 500;
  transition: background 0.2s, color 0.2s;
  border: none;
  background: none;
  cursor: pointer;
}

.nav-link-modern:hover {
  background: #f3f4f6;
  color: #16a34a;
}

.cart-link,
.messages-link,
.login-link {
  color: #16a34a;
}

.icon-wrapper {
  position: relative;
  font-size: 24px;
}

.cart-badge,
.notification-badge,
.messages-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background: #ef4444;
  color: white;
  border-radius: 10px;
  min-width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 600;
  padding: 0 6px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.nav-label {
  display: none;
}

/* User Menu */
.user-menu-toggle {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 6px 12px;
}

.user-menu-toggle i.bx-user {
  font-size: 24px;
  color: #16a34a;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.user-name {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
  line-height: 1.2;
}

.user-role {
  font-size: 12px;
  color: #6b7280;
  line-height: 1;
}

/* Dropdown */
.dropdown-modern {
  position: relative;
}

.dropdown-menu-modern {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  min-width: 320px;
  max-width: 400px;
  max-height: 500px;
  overflow: hidden;
  z-index: 1001;
  border: 1px solid #e5e7eb;
}

/* Ensure dropdowns appear above sticky header */
.header-wrapper-sticky .dropdown-menu-modern {
  z-index: 1002;
}

.notification-dropdown {
  min-width: 360px;
}

.dropdown-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.dropdown-title {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.btn-mark-all-read {
  background: none;
  border: none;
  color: #16a34a;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background 0.2s;
}

.btn-mark-all-read:hover {
  background: #f3f4f6;
}

.dropdown-body {
  max-height: 400px;
  overflow-y: auto;
}

.empty-notifications {
  padding: 40px 20px;
  text-align: center;
  color: #9ca3af;
}

.empty-notifications i {
  font-size: 48px;
  margin-bottom: 12px;
  opacity: 0.5;
}

.notifications-list {
  padding: 8px;
}

.notification-item {
  display: flex;
  gap: 12px;
  padding: 12px;
  border-radius: 12px;
  cursor: pointer;
  transition: background 0.2s;
}

.notification-item:hover {
  background: #f9fafb;
}

.notification-item.unread {
  background: #eff6ff;
}

.notification-item.unread:hover {
  background: #dbeafe;
}

.notification-icon {
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.icon-order {
  background: #dbeafe;
  color: #2563eb;
}

.icon-product {
  background: #dcfce7;
  color: #16a34a;
}

.icon-message {
  background: #fef3c7;
  color: #d97706;
}

.icon-system {
  background: #e9d5ff;
  color: #9333ea;
}

.icon-default {
  background: #f3f4f6;
  color: #6b7280;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 4px 0;
}

.notification-message {
  font-size: 13px;
  color: #6b7280;
  margin: 0 0 4px 0;
  line-height: 1.4;
}

.notification-time {
  font-size: 12px;
  color: #9ca3af;
}

.dropdown-footer {
  padding: 12px 16px;
  border-top: 1px solid #e5e7eb;
  text-align: center;
}

.view-all-link {
  color: #16a34a;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
}

.view-all-link:hover {
  text-decoration: underline;
}

/* User Dropdown */
.user-dropdown {
  min-width: 280px;
}

.user-header {
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 20px;
}

.user-icon-large {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #16a34a;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
  color: white;
  font-size: 32px;
}

.user-details {
  width: 100%;
}

.user-name-large {
  font-size: 16px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 4px 0;
}

.user-email {
  font-size: 13px;
  color: #6b7280;
  margin: 0;
}

.dropdown-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 8px 0;
}

.dropdown-item-modern {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  color: #374151;
  text-decoration: none;
  font-size: 14px;
  transition: background 0.2s;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
}

.dropdown-item-modern:hover {
  background: #f9fafb;
  color: #16a34a;
}

.dropdown-item-modern i {
  font-size: 20px;
  width: 24px;
}

.logout-item {
  color: #ef4444;
}

.logout-item:hover {
  background: #fef2f2;
  color: #dc2626;
}

/* Mobile Overlay */
.mobile-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
}

/* Responsive */
@media (max-width: 1024px) {
  .navbar-modern {
    padding: 12px 16px;
  }
  
  .search-container {
    max-width: 400px;
  }
  
  .nav-label {
    display: none;
  }
}

@media (max-width: 768px) {
  .mobile-menu-toggle {
    display: block;
  }
  
  .search-container {
    order: 3;
    width: 100%;
    max-width: none;
    margin-top: 12px;
  }
  
  .navbar-modern {
    flex-wrap: wrap;
  }
  
  .nav-label {
    display: none;
  }
  
  .user-info {
    display: none;
  }
  
  .dropdown-menu-modern {
    right: -16px;
    min-width: calc(100vw - 32px);
    max-width: calc(100vw - 32px);
  }
}

@media (max-width: 480px) {
  .brand-title {
    font-size: 16px;
  }
  
  .brand-subtitle {
    font-size: 11px;
  }
  
  .navbar-modern {
    padding: 8px 12px;
  }
}
</style>
