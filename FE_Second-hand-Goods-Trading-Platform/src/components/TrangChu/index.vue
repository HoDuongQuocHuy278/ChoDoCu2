<template>
  <div class="homepage">
    <!-- HERO SECTION -->
    <section class="hero">
      <div class="hero-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="grid-overlay"></div>
      </div>
      
      <div class="container">
        <div class="hero-content">
          <div class="hero-left">
            <div class="badge">
              <span class="badge-emoji">🎉</span>
              <span>Ưu đãi đặc biệt tháng này</span>
            </div>
            
            <h1 class="hero-title">
              <span class="title-line-1">Chợ Đồ Cũ</span>
              <span class="title-line-2">Mua Bán Thông Minh</span>
            </h1>
            
            <p class="hero-desc">
              Nền tảng mua bán đồ cũ uy tín nhất Việt Nam.
              <br class="d-none d-md-block" />
              Hoàn 100% phí bán hàng · Giao dịch an toàn · Hỗ trợ 24/7
            </p>
            
            <div class="features">
              <div class="feature">
                <span class="feature-emoji">✨</span>
                <span>Miễn phí đăng bán</span>
              </div>
              <div class="feature">
                <span class="feature-emoji">🔒</span>
                <span>Bảo mật tuyệt đối</span>
              </div>
              <div class="feature">
                <span class="feature-emoji">🚀</span>
                <span>Giao dịch nhanh chóng</span>
              </div>
            </div>
            
            <div class="cta-buttons" v-if="isLoggedIn">
              <a class="btn btn-primary" :href="routes.sell">
                <span>＋</span>
                <span>Đăng bán ngay</span>
                <span class="arrow">→</span>
              </a>
            </div>
            <div class="cta-buttons" v-else>
              <a class="btn btn-primary" :href="routes.register">
                <span>Bắt đầu miễn phí</span>
                <span class="arrow">→</span>
              </a>
              <a class="btn btn-secondary" :href="routes.login">Đăng nhập</a>
            </div>
          </div>
          
          <div class="hero-right">
            <div class="card">
              <div class="card-icon">✅</div>
              <div class="card-content">
                <h3>Cam kết chất lượng</h3>
                <p>Nhận sản phẩm đúng như mô tả hoặc được hoàn tiền 100%</p>
              </div>
            </div>
            
            <div class="card card-highlight" role="button" data-bs-toggle="modal" data-bs-target="#qrModal">
              <div class="card-icon">📱</div>
              <div class="card-content">
                <h3>Tải ứng dụng</h3>
                <p>Quét QR code để tải app và mua bán mọi lúc mọi nơi</p>
              </div>
              <div class="card-arrow">→</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- QR MODAL -->
    <div class="modal fade" id="qrModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content qr-modal">
          <div class="modal-header qr-header">
            <h1 class="modal-title">📱 Tải ứng dụng Chợ Đồ Cũ</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
          <div class="modal-body qr-body">
            <div class="qr-wrapper">
              <!-- <img src="../../assets/images/qr.jpg" alt="QR Code" class="qr-image" /> -->
              <div class="qr-placeholder" style="width: 150px; height: 150px; background: #eee; display: flex; align-items: center; justify-content: center;">QR Code</div>
            </div>
            <p class="qr-text">Quét mã QR bằng camera điện thoại để tải ứng dụng</p>
          </div>
          <div class="modal-footer qr-footer">
            <button type="button" class="btn btn-close-modal" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>

    <!-- CATEGORIES -->
    <section class="categories">
      <div class="container">
        <div class="section-header">
          <div>
            <h2 class="section-title">Khám phá danh mục</h2>
            <p class="section-subtitle">Tìm kiếm sản phẩm yêu thích của bạn</p>
          </div>
          <a href="#" class="view-all">
            <span>Xem tất cả</span>
            <span class="arrow">→</span>
          </a>
        </div>

        <div class="categories-grid">
          <a 
            v-for="c in cats" 
            :key="c.slug || c.id"
            class="cat-card" 
            :href="buildSearchUrl(c.slug)"
            :aria-label="`Xem danh mục ${c.name || c.ten_danh_muc}`"
          >
            <div class="cat-icon-wrapper">
              <div class="cat-icon" v-if="c.hinh_anh">
                <img :src="fixImage(c.hinh_anh)" :alt="c.name || c.ten_danh_muc" />
              </div>
              <div class="cat-icon emoji" v-else>
                {{ c.icon || getCategoryIcon(c.slug) }}
              </div>
              <div class="cat-glow"></div>
            </div>
            <h3 class="cat-name">{{ c.name || c.ten_danh_muc }}</h3>
            <div class="cat-hover-bar"></div>
          </a>
        </div>
      </div>
    </section>

    <!-- CHAT WIDGET -->
    <button 
      id="chat-toggle" 
      class="chat-btn" 
      type="button" 
      :aria-expanded="showChat"
      @click="toggleChat"
      title="Mở chatbot (Alt+C)"
    >
      <svg class="chat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
      </svg>
      <span class="chat-pulse" v-show="!showChat && unreadCount > 0"></span>
      <span class="unread" v-show="!showChat && unreadCount > 0">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
    </button>

    <transition name="chat">
      <div v-show="showChat" class="chat-widget" role="dialog">
        <!-- <div class="chat-header">
          <div class="chat-info">
            <div class="chat-avatar">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
              </svg>
            </div>
            <div>
              <h3 class="chat-title">Chatbot Chợ Đồ Cũ</h3>
              <p class="chat-subtitle">
                <span class="status-dot"></span>
                Trực tuyến • Sẵn sàng hỗ trợ
              </p>
            </div>
          </div>
          <button class="chat-close" @click="toggleChat">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div> -->

        <div id="chatbox" ref="chatboxRef" class="chat-messages">
          <div v-if="messages.length === 0" class="welcome">
            <div class="welcome-icon">🛒</div>
            <h4>Chào mừng đến Chợ Đồ Cũ!</h4>
            <p>Tôi là chatbot hỗ trợ của bạn. Tôi có thể giúp bạn:</p>
            <ul class="welcome-list">
              <li>📦 Tìm kiếm sản phẩm</li>
              <li>💰 Hướng dẫn mua bán</li>
              <li>💳 Thông tin thanh toán</li>
              <li>🚚 Vận chuyển</li>
              <li>🔄 Đổi trả hàng</li>
            </ul>
            <p class="welcome-hint">Hỏi tôi bất cứ điều gì bạn muốn biết!</p>
          </div>
          
          <div 
            v-for="(m, i) in messages" 
            :key="i" 
            class="message"
            :class="m.from === 'you' ? 'sent' : 'received'"
          >
            <div class="message-content">
              <p v-html="formatBotMessage(m.text)"></p>
              
              <div v-if="m.products && m.products.length > 0" class="products">
                <div v-for="product in m.products" :key="product.id" class="product-card">
                  <a :href="product.url" target="_blank" class="product-link">
                    <div class="product-img" v-if="product.image">
                      <img :src="fixImage(product.image)" :alt="product.name" />
                    </div>
                    <div class="product-img placeholder" v-else>
                      <i class='bx bx-package'></i>
                    </div>
                    <div class="product-info">
                      <h4>{{ product.name }}</h4>
                      <p class="product-price">{{ product.price_formatted || formatPrice(product.price) }}</p>
                    </div>
                    <div class="product-arrow">
                      <i class='bx bx-arrow-to-right'></i>
                    </div>
                  </a>
                </div>
                
                <a v-if="m.more_url" :href="m.more_url" target="_blank" class="view-all">
                  <i class='bx bx-grid-alt'></i>
                  Xem tất cả sản phẩm
                </a>
              </div>
            </div>
            <span class="message-time">{{ formatMessageTime(m.time) }}</span>
          </div>
          
          <div v-if="typing" class="message received typing">
            <div class="typing-dots">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        </div>

        <div class="chat-input">
          <input 
            v-model="msg" 
            @keyup.enter="sendMessage" 
            type="text" 
            placeholder="Hỏi về sản phẩm, mua bán, thanh toán..."
            class="input-field"
          />
          <button 
            class="send-btn" 
            @click="sendMessage" 
            :disabled="!msg.trim() || typing"
          >
            <svg v-if="!typing" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="22" y1="2" x2="11" y2="13"></line>
              <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
            <div v-else class="spinner"></div>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from 'axios'
import { fixImageUrl } from '../../utils/imageHelper'

export default {
  name: 'TrangChu',
  data() {
    return {
      API_BASE_URL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/client',
      isLoggedIn: false,
      routes: {
        sell: '/sell',
        login: '/dang-nhap',
        register: '/dang-ky',
        search: '/search'
      },
      cats: [],
      showChat: false,
      messages: [],
      msg: '',
      typing: false,
      unreadCount: 0
    }
  },
  mounted() {
    this.isLoggedIn = !!localStorage.getItem('key_client')
    this.fetchCategories()
    window.addEventListener('keydown', this.onKey)
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.onKey)
  },
  watch: {
    messages() {
      this.$nextTick(this.scrollToBottom)
    }
  },
  methods: {
    async fetchCategories() {
      try {
        const { data } = await axios.get(`${this.API_BASE_URL}/danh-muc`)
        const rawCategories = data?.data || data || []
        this.cats = Array.isArray(rawCategories)
          ? rawCategories.map(cat => ({
              id: cat.id,
              slug: cat.slug,
              name: cat.ten_danh_muc || cat.name,
              ten_danh_muc: cat.ten_danh_muc,
              hinh_anh: cat.hinh_anh,
              icon: this.getCategoryIcon(cat.slug),
              mo_ta: cat.mo_ta,
            }))
          : [
              { slug: 'dien-thoai', name: 'Điện thoại', icon: '📱' },
              { slug: 'sach', name: 'Sách', icon: '📚' },
              { slug: 'do-dien-tu', name: 'Đồ điện tử', icon: '💡' },
              { slug: 'may-tinh', name: 'Máy tính', icon: '🖥️' },
              { slug: 'thoi-trang', name: 'Thời trang', icon: '👕' },
              { slug: 'do-gia-dung', name: 'Gia dụng', icon: '🍳' },
            ]
      } catch (err) {
        console.warn('Không thể tải danh mục', err)
        this.cats = [
          { slug: 'dien-thoai', name: 'Điện thoại', icon: '📱' },
          { slug: 'sach', name: 'Sách', icon: '📚' },
          { slug: 'do-dien-tu', name: 'Đồ điện tử', icon: '💡' },
          { slug: 'may-tinh', name: 'Máy tính', icon: '🖥️' },
          { slug: 'thoi-trang', name: 'Thời trang', icon: '👕' },
          { slug: 'do-gia-dung', name: 'Gia dụng', icon: '🍳' },
        ]
      }
    },
    getCategoryIcon(slug) {
      const iconMap = {
        'dien-thoai': '📱',
        'sach': '📚',
        'do-dien-tu': '💡',
        'may-tinh': '🖥️',
        'thoi-trang': '👕',
        'do-gia-dung': '🍳',
      }
      return iconMap[slug] || '📦'
    },
    toggleChat() {
      this.showChat = !this.showChat
      if (this.showChat) this.unreadCount = 0
      this.$nextTick(this.scrollToBottom)
    },
    buildSearchUrl(slug) {
      return `${this.routes.search}?category=${encodeURIComponent(slug)}`
    },
    async sendMessage() {
      const text = this.msg.trim()
      if (!text || this.typing) return

      this.messages.push({ from: 'you', text, time: new Date() })
      this.msg = ''
      this.typing = true

      await this.$nextTick()
      this.scrollToBottom()

      try {
        //đổi api
        const chatbotUrl = import.meta.env.VITE_CHATBOT_URL || 'http://192.168.1.111:5000'
        const res = await fetch(`${chatbotUrl}/chat`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ message: text })
        })
        
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`)
        
        const data = await res.json()
        const botResponse = data.reply || data.response || data.error || 'Xin lỗi, tôi không thể phản hồi ngay bây giờ. Vui lòng thử lại sau.'
        this.messages.push({ 
          from: 'bot', 
          text: botResponse, 
          time: new Date(),
          products: data.products || null,
          more_url: data.more_url || null
        })
      } catch (e) {
        console.error('Chatbot error:', e)
        const fallbackResponses = [
          'Xin lỗi, chatbot đang tạm thời không khả dụng. Vui lòng liên hệ bộ phận hỗ trợ qua email hoặc hotline.',
          'Xin lỗi, tôi không thể kết nối được. Bạn vui lòng thử lại sau hoặc liên hệ trực tiếp với chúng tôi.',
          'Hiện tại chatbot đang bảo trì. Nếu bạn cần hỗ trợ ngay, vui lòng liên hệ qua thông tin trong trang Liên hệ.'
        ]
        const randomResponse = fallbackResponses[Math.floor(Math.random() * fallbackResponses.length)]
        this.messages.push({ from: 'bot', text: randomResponse, time: new Date() })
      } finally {
        this.typing = false
      }

      await this.$nextTick()
      this.scrollToBottom()
      if (!this.showChat) this.unreadCount++
    },
    formatMessageTime(time) {
      if (!time) return ''
      const date = new Date(time)
      const now = new Date()
      const diff = now - date
      const minutes = Math.floor(diff / 60000)
      if (minutes < 1) return 'Vừa xong'
      if (minutes < 60) return `${minutes} phút trước`
      return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
    },
    formatBotMessage(text) {
      if (!text) return ''
      return text.replace(/\n/g, '<br>')
    },
    formatPrice(price) {
      if (!price && price !== 0) return 'Liên hệ'
      return `${Number(price).toLocaleString('vi-VN')} ₫`
    },
    scrollToBottom() {
      const chatbox = this.$refs.chatboxRef
      if (chatbox) chatbox.scrollTop = chatbox.scrollHeight
    },
    onKey(e) {
      if (e.altKey && (e.key === 'c' || e.key === 'C')) {
        e.preventDefault()
        this.toggleChat()
      }
      if (e.key === 'Escape' && this.showChat) {
        this.toggleChat()
      }
    },
    fixImage(url) {
      return fixImageUrl(url)
    }
  }
}
</script>

<style scoped>
/* ===== VARIABLES ===== */
:root {
  --primary: #16a34a;
  --primary-dark: #15803d;
  --primary-light: #22c55e;
  --bg: #ffffff;
  --bg-secondary: #f8fafc;
  --text: #0f172a;
  --text-muted: #64748b;
  --border: #e2e8f0;
  --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.homepage {
  min-height: 100vh;
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
}

/* ===== HERO SECTION ===== */
.hero {
  position: relative;
  padding: 100px 0 120px;
  overflow: hidden;
  background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 50%, #fef3f2 100%);
}

.hero-bg {
  position: absolute;
  inset: 0;
  overflow: hidden;
  z-index: 0;
}

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.6;
  animation: float 20s ease-in-out infinite;
}

.orb-1 {
  width: 500px;
  height: 500px;
  background: linear-gradient(135deg, #a7f3d0, #86efac);
  top: -200px;
  right: -100px;
}

.orb-2 {
  width: 400px;
  height: 400px;
  background: linear-gradient(135deg, #fde68a, #fcd34d);
  bottom: -150px;
  left: -50px;
  animation-delay: -5s;
}

.orb-3 {
  width: 350px;
  height: 350px;
  background: linear-gradient(135deg, #fecdd3, #fda4af);
  top: 50%;
  right: 10%;
  animation-delay: -10s;
}

.grid-overlay {
  position: absolute;
  inset: 0;
  background-image: 
    linear-gradient(rgba(0, 0, 0, 0.02) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0, 0, 0, 0.02) 1px, transparent 1px);
  background-size: 50px 50px;
  opacity: 0.5;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -30px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
}

.hero-content {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 60px;
  align-items: center;
}

.hero-left {
  max-width: 700px;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: linear-gradient(135deg, rgba(22, 163, 74, 0.1), rgba(34, 197, 94, 0.1));
  border: 1px solid rgba(22, 163, 74, 0.2);
  border-radius: 50px;
  font-size: 14px;
  font-weight: 600;
  color: var(--primary-dark);
  margin-bottom: 24px;
  backdrop-filter: blur(10px);
  animation: slideUp 0.6s ease-out;
}

.badge-emoji {
  font-size: 18px;
  animation: bounce 2s infinite;
}

.hero-title {
  margin: 0 0 20px;
  line-height: 1.2;
  animation: slideUp 0.8s ease-out 0.2s both;
}

.title-line-1 {
  display: block;
  font-size: clamp(36px, 7vw, 72px);
  font-weight: 900;
  background: linear-gradient(135deg, #16a34a 0%, #22c55e 50%, #10b981 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -2px;
}

.title-line-2 {
  display: block;
  font-size: clamp(28px, 5vw, 56px);
  font-weight: 700;
  color: var(--text);
  margin-top: 8px;
}

.hero-desc {
  font-size: 18px;
  line-height: 1.7;
  color: var(--text-muted);
  margin-bottom: 32px;
  animation: slideUp 0.8s ease-out 0.4s both;
}

.features {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 40px;
  animation: slideUp 0.8s ease-out 0.6s both;
}

.feature {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid var(--border);
  border-radius: 50px;
  font-size: 14px;
  font-weight: 500;
  color: var(--text);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.feature:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow);
  border-color: var(--primary);
}

.feature-emoji {
  font-size: 18px;
}

.cta-buttons {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  animation: slideUp 0.8s ease-out 0.8s both;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 16px 32px;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary), var(--primary-light));
  color: white;
  box-shadow: 0 10px 25px rgba(22, 163, 74, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 35px rgba(22, 163, 74, 0.4);
}

.btn-primary .arrow {
  transition: transform 0.3s ease;
}

.btn-primary:hover .arrow {
  transform: translateX(4px);
}

.btn-secondary {
  background: white;
  color: var(--text);
  border: 2px solid var(--border);
}

.btn-secondary:hover {
  border-color: var(--primary);
  color: var(--primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow);
}

.hero-right {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.card {
  background: white;
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 24px;
  box-shadow: var(--shadow);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary), var(--primary-light));
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
  border-color: var(--primary);
}

.card:hover::before {
  transform: scaleX(1);
}

.card-highlight {
  background: linear-gradient(135deg, rgba(22, 163, 74, 0.05), rgba(34, 197, 94, 0.05));
  border-color: rgba(22, 163, 74, 0.2);
  cursor: pointer;
}

.card-icon {
  font-size: 40px;
  margin-bottom: 16px;
}

.card-content h3 {
  font-size: 18px;
  font-weight: 700;
  color: var(--text);
  margin: 0 0 8px;
}

.card-content p {
  font-size: 14px;
  color: var(--text-muted);
  margin: 0;
  line-height: 1.6;
}

.card-arrow {
  position: absolute;
  top: 24px;
  right: 24px;
  font-size: 20px;
  color: var(--primary);
  opacity: 0;
  transform: translateX(-10px);
  transition: all 0.3s ease;
}

.card-highlight:hover .card-arrow {
  opacity: 1;
  transform: translateX(0);
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

/* ===== CATEGORIES ===== */
.categories {
  padding: 100px 0;
  background: white;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 48px;
  flex-wrap: wrap;
  gap: 20px;
}

.section-title {
  font-size: clamp(32px, 5vw, 48px);
  font-weight: 800;
  color: var(--text);
  margin: 0 0 8px;
  letter-spacing: -1px;
}

.section-subtitle {
  font-size: 16px;
  color: var(--text-muted);
  margin: 0;
}

.view-all {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: white;
  border: 2px solid var(--border);
  border-radius: 50px;
  color: var(--text);
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
}

.view-all:hover {
  border-color: var(--primary);
  color: var(--primary);
  transform: translateX(4px);
}

.view-all .arrow {
  transition: transform 0.3s ease;
}

.view-all:hover .arrow {
  transform: translateX(4px);
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 24px;
}

.cat-card {
  position: relative;
  background: white;
  border: 2px solid var(--border);
  border-radius: 24px;
  padding: 36px 24px;
  text-align: center;
  text-decoration: none;
  color: var(--text);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.cat-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(22, 163, 74, 0.05), rgba(34, 197, 94, 0.05));
  opacity: 0;
  transition: opacity 0.3s ease;
}

.cat-card:hover {
  transform: translateY(-8px) scale(1.02);
  border-color: var(--primary);
  box-shadow: 0 20px 40px rgba(22, 163, 74, 0.15);
}

.cat-card:hover::before {
  opacity: 1;
}

.cat-icon-wrapper {
  position: relative;
  width: 90px;
  height: 90px;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1;
}

.cat-icon {
  width: 90px;
  height: 90px;
  border-radius: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(22, 163, 74, 0.1), rgba(34, 197, 94, 0.1));
  transition: all 0.3s ease;
  position: relative;
  z-index: 2;
}

.cat-icon img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 24px;
}

.cat-icon.emoji {
  font-size: 44px;
}

.cat-glow {
  position: absolute;
  inset: -12px;
  background: radial-gradient(circle, rgba(22, 163, 74, 0.2), transparent 70%);
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 50%;
}

.cat-card:hover .cat-glow {
  opacity: 1;
}

.cat-card:hover .cat-icon {
  transform: scale(1.1) rotate(5deg);
  background: linear-gradient(135deg, rgba(22, 163, 74, 0.2), rgba(34, 197, 94, 0.2));
}

.cat-name {
  font-size: 16px;
  font-weight: 600;
  color: var(--text);
  margin: 0;
  position: relative;
  z-index: 1;
  transition: color 0.3s ease;
}

.cat-card:hover .cat-name {
  color: var(--primary);
}

.cat-hover-bar {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary), var(--primary-light));
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.3s ease;
}

.cat-card:hover .cat-hover-bar {
  transform: scaleX(1);
}

/* ===== QR MODAL ===== */
.qr-modal {
  border-radius: 24px;
  border: none;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.qr-header {
  background: linear-gradient(135deg, var(--primary), var(--primary-light));
  color: white;
  border: none;
  padding: 24px 32px;
}

.qr-header .modal-title {
  font-size: 24px;
  font-weight: 700;
  margin: 0;
}

.qr-header .btn-close {
  width: 36px;
  height: 36px;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 0;
  opacity: 1;
}

.qr-header .btn-close:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.qr-header .btn-close svg {
  width: 20px;
  height: 20px;
}

.qr-body {
  padding: 40px;
  text-align: center;
  background: white;
}

.qr-wrapper {
  display: inline-flex;
  padding: 20px;
  background: white;
  border: 2px solid var(--border);
  border-radius: 20px;
  margin-bottom: 24px;
  box-shadow: var(--shadow);
}

.qr-image {
  width: 240px;
  height: 240px;
  border-radius: 12px;
  display: block;
}

.qr-text {
  font-size: 16px;
  color: var(--text-muted);
  margin: 0;
  line-height: 1.6;
}

.qr-footer {
  border: none;
  padding: 24px 32px;
  background: var(--bg-secondary);
}

.btn-close-modal {
  padding: 12px 32px;
  background: white;
  border: 2px solid var(--border);
  border-radius: 12px;
  color: var(--text);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-close-modal:hover {
  border-color: var(--primary);
  color: var(--primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow);
}

/* ===== CHAT WIDGET ===== */
.chat-btn {
  position: fixed;
  bottom: 24px;
  right: 24px;
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #16a34a, #22c55e);
  color: #fff;
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.4);
  z-index: 1000;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.chat-btn:hover {
  transform: translateY(-4px) scale(1.05);
  box-shadow: 0 12px 32px rgba(22, 163, 74, 0.5);
}

.chat-icon {
  width: 28px;
  height: 28px;
  stroke: #fff;
}

.chat-pulse {
  position: absolute;
  top: -2px;
  right: -2px;
  width: 16px;
  height: 16px;
  background: #ef4444;
  border-radius: 50%;
  border: 3px solid #fff;
  animation: pulse 2s infinite;
}

.unread {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #ef4444;
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  min-width: 20px;
  height: 20px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 6px;
  border: 2px solid #fff;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.3); opacity: 0.7; }
}

.chat-enter-active,
.chat-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.chat-enter-from,
.chat-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.chat-widget {
  position: fixed;
  bottom: 100px;
  right: 24px;
  width: 400px;
  max-width: calc(100vw - 48px);
  height: 650px;
  max-height: calc(100vh - 120px);
  background: #ffffff;
  border-radius: 24px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  z-index: 1000;
}

.chat-header {
  background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
  padding: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.chat-info {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

.chat-avatar {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
}

.chat-avatar svg {
  width: 28px;
  height: 28px;
}

.chat-title {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  margin: 0 0 4px 0;
}

.chat-subtitle {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

.status-dot {
  width: 8px;
  height: 8px;
  background: #10b981;
  border-radius: 50%;
  display: inline-block;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
  animation: statusPulse 2s infinite;
}

@keyframes statusPulse {
  0%, 100% { box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3); }
  50% { box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }
}

.chat-close {
  width: 36px;
  height: 36px;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.chat-close:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.chat-close svg {
  width: 20px;
  height: 20px;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  background: #f9fafb;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.chat-messages::-webkit-scrollbar {
  width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
  background: transparent;
}

.chat-messages::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

.welcome {
  text-align: center;
  padding: 30px 20px;
  color: #6b7280;
}

.welcome-icon {
  font-size: 56px;
  margin-bottom: 16px;
  animation: welcomeBounce 2s ease-in-out infinite;
}

@keyframes welcomeBounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

.welcome h4 {
  font-size: 20px;
  font-weight: 700;
  color: #16a34a;
  margin: 0 0 12px 0;
}

.welcome > p {
  font-size: 14px;
  margin: 0 0 16px 0;
  color: #6b7280;
}

.welcome-list {
  list-style: none;
  padding: 0;
  margin: 0 0 16px 0;
  text-align: left;
  display: inline-block;
}

.welcome-list li {
  font-size: 13px;
  color: #4b5563;
  padding: 6px 0;
}

.welcome-hint {
  font-size: 13px;
  font-style: italic;
  color: #9ca3af;
  margin: 16px 0 0 0;
}

.message {
  max-width: 75%;
  display: flex;
  flex-direction: column;
  gap: 4px;
  animation: messageAppear 0.3s ease-out;
}

@keyframes messageAppear {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.message.sent {
  align-self: flex-end;
}

.message.received {
  align-self: flex-start;
}

.message-content {
  padding: 12px 16px;
  border-radius: 18px;
  font-size: 14px;
  line-height: 1.5;
  word-wrap: break-word;
}

.message-content p {
  margin: 0;
}

.message.sent .message-content {
  background: linear-gradient(135deg, #16a34a, #22c55e);
  color: #fff;
  border-bottom-right-radius: 4px;
}

.message.received .message-content {
  background: #ffffff;
  color: #111827;
  border: 1px solid #e5e7eb;
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.message-time {
  font-size: 11px;
  color: #9ca3af;
  padding: 0 8px;
}

.message.sent .message-time {
  text-align: right;
}

.products {
  margin-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.product-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.2s ease;
}

.product-card:hover {
  border-color: #16a34a;
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15);
  transform: translateY(-2px);
}

.product-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  text-decoration: none;
  color: inherit;
}

.product-img {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
}

.product-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-img.placeholder {
  font-size: 24px;
  color: #9ca3af;
}

.product-info {
  flex: 1;
  min-width: 0;
}

.product-info h4 {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 4px 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.product-price {
  font-size: 13px;
  font-weight: 600;
  color: #16a34a;
  margin: 0;
}

.product-arrow {
  flex-shrink: 0;
  color: #16a34a;
  font-size: 20px;
  transition: transform 0.2s ease;
}

.product-link:hover .product-arrow {
  transform: translateX(4px);
}

.products .view-all {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: #111827;
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.2s ease;
  margin-top: 4px;
}

.products .view-all:hover {
  background: #16a34a;
  color: #ffffff;
  border-color: #16a34a;
}

.typing {
  padding: 0;
}

.typing-dots {
  display: flex;
  gap: 6px;
  padding: 12px 16px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 18px;
  border-bottom-left-radius: 4px;
}

.typing-dots span {
  width: 8px;
  height: 8px;
  background: #9ca3af;
  border-radius: 50%;
  animation: typingDot 1.4s infinite;
}

.typing-dots span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-dots span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typingDot {
  0%, 60%, 100% {
    transform: translateY(0);
    opacity: 0.7;
  }
  30% {
    transform: translateY(-8px);
    opacity: 1;
  }
}

.chat-input {
  padding: 16px;
  background: #ffffff;
  border-top: 1px solid #e5e7eb;
  display: flex;
  gap: 8px;
  align-items: center;
}

.input-field {
  flex: 1;
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 24px;
  font-size: 14px;
  outline: none;
  transition: all 0.2s;
  background: #f9fafb;
}

.input-field:focus {
  border-color: #16a34a;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.send-btn {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, #16a34a, #22c55e);
  color: #fff;
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}

.send-btn:hover:not(:disabled) {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.4);
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.send-btn svg {
  width: 20px;
  height: 20px;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
  .hero {
    padding: 80px 0 100px;
  }
  
  .hero-content {
    grid-template-columns: 1fr;
    gap: 40px;
  }
  
  .hero-right {
    margin-top: 20px;
  }
  
  .categories {
    padding: 80px 0;
  }
  
  .categories-grid {
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 20px;
  }
}

@media (max-width: 768px) {
  .hero {
    padding: 60px 0 80px;
  }
  
  .hero-title {
    margin-bottom: 16px;
  }
  
  .hero-desc {
    font-size: 16px;
    margin-bottom: 24px;
  }
  
  .features {
    gap: 12px;
    margin-bottom: 32px;
  }
  
  .feature {
    padding: 8px 14px;
    font-size: 13px;
  }
  
  .cta-buttons {
    flex-direction: column;
    width: 100%;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
  
  .categories {
    padding: 60px 0;
  }
  
  .section-header {
    margin-bottom: 32px;
  }
  
  .section-title {
    font-size: 28px;
  }
  
  .categories-grid {
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 16px;
  }
  
  .cat-card {
    padding: 28px 20px;
  }
  
  .cat-icon-wrapper {
    width: 70px;
    height: 70px;
  }
  
  .cat-icon {
    width: 70px;
    height: 70px;
  }
  
  .cat-icon.emoji {
    font-size: 36px;
  }
  
  .cat-name {
    font-size: 14px;
  }
  
  .qr-body {
    padding: 30px 20px;
  }
  
  .qr-image {
    width: 200px;
    height: 200px;
  }
  
  .chat-widget {
    right: 12px;
    bottom: 80px;
    width: calc(100vw - 24px);
    height: calc(100vh - 100px);
    max-height: calc(100vh - 100px);
  }
  
  .chat-btn {
    right: 16px;
    bottom: 16px;
    width: 56px;
    height: 56px;
  }
}

@media (max-width: 480px) {
  .badge {
    padding: 8px 16px;
    font-size: 12px;
  }
  
  .title-line-1 {
    font-size: 32px;
  }
  
  .title-line-2 {
    font-size: 24px;
  }
  
  .hero-desc {
    font-size: 14px;
  }
  
  .categories-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .cat-card {
    padding: 24px 16px;
  }
  
  .cat-icon-wrapper {
    width: 60px;
    height: 60px;
  }
  
  .cat-icon {
    width: 60px;
    height: 60px;
  }
  
  .cat-icon.emoji {
    font-size: 32px;
  }
}
</style>
