/**
 * API Configuration Service
 * Centralized API base URL configuration
 */

// Sử dụng relative URL để hoạt động với Vite proxy khi chia sẻ qua mạng
// Nếu cần dùng absolute URL, set trong .env: VITE_API_BASE_URL=http://192.168.1.100:8000/api/client
export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api/client'

/**
 * Get full API URL
 * @param {string} endpoint - API endpoint (e.g., '/san-pham')
 * @returns {string} Full API URL
 */
export function getApiUrl(endpoint) {
  // Remove leading slash if present to avoid double slashes
  const cleanEndpoint = endpoint.startsWith('/') ? endpoint.slice(1) : endpoint
  
  // If API_BASE_URL is relative, combine properly
  if (API_BASE_URL.startsWith('/')) {
    return `${API_BASE_URL}/${cleanEndpoint}`
  }
  
  // If API_BASE_URL is absolute, combine with endpoint
  const baseUrl = API_BASE_URL.endsWith('/') ? API_BASE_URL.slice(0, -1) : API_BASE_URL
  return `${baseUrl}/${cleanEndpoint}`
}

/**
 * Common API endpoints
 */
export const API_ENDPOINTS = {
  // Authentication
  LOGIN: '/dang-nhap',
  REGISTER: '/dang-ky',
  LOGOUT: '/dang-xuat',
  PROFILE: '/thong-tin',
  CHECK_TOKEN: '/check-token',
  CHECK_SELLER_STATUS: '/check-seller-status',
  REGISTER_SELLER: '/dang-ky-ban',
  
  // Products
  PRODUCTS: '/san-pham',
  PRODUCT_DETAIL: (id) => `/san-pham/${id}`,
  CREATE_PRODUCT: '/san-pham',
  UPDATE_PRODUCT: (id) => `/seller/san-pham/${id}`,
  DELETE_PRODUCT: (id) => `/seller/san-pham/${id}`,
  PRODUCT_STATS: '/seller/product-stats',
  SELLER_PRODUCTS: '/seller/san-pham',
  
  // Categories
  CATEGORIES: '/danh-muc',
  CATEGORY_DETAIL: (slug) => `/danh-muc/${slug}`,
  
  // Orders
  ORDERS: '/don-hang',
  ORDER_DETAIL: (id) => `/don-hang/${id}`,
  BUYER_ORDERS: '/don-hang-mua',
  SELLER_ORDERS: '/don-hang-ban',
  
  // Cart
  CART: '/gio-hang',
  
  // Chat
  CHAT: '/chat',
  CHAT_MESSAGES: (chatId) => `/chat/${chatId}/messages`,
  
  // Notifications
  NOTIFICATIONS: '/thong-bao',
  NOTIFICATION_UNREAD_COUNT: '/thong-bao/unread-count',
}

