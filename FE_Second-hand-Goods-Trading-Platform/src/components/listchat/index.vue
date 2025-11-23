<template>
  <div class="chat-list-container">
    <!-- Header -->
    <div class="chat-list-header">
      <div class="header-content">
        <h1 class="page-title">
          <i class='bx bx-message-rounded-dots'></i>
          Tin nhắn của tôi
        </h1>
        <p class="page-subtitle">Quản lý các cuộc trò chuyện của bạn</p>
      </div>
      <button 
        class="btn-new-chat" 
        @click="handleNewChat"
        title="Tạo cuộc trò chuyện mới"
      >
        <i class='bx bx-plus'></i>
        <span>Tin nhắn mới</span>
      </button>
    </div>

    <!-- Search Bar -->
    <div class="search-section">
      <div class="search-wrapper">
        <i class='bx bx-search search-icon'></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Tìm kiếm cuộc trò chuyện..."
          class="search-input"
          @input="handleSearch"
        />
        <button 
          v-if="searchQuery"
          class="btn-clear-search"
          @click="clearSearch"
        >
          <i class='bx bx-x'></i>
        </button>
      </div>
    </div>

    <!-- Chat List -->
    <div class="chat-list-wrapper">
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner"></div>
        <p>Đang tải danh sách chat...</p>
      </div>

      <div v-else-if="filteredChats.length === 0" class="empty-state">
        <div class="empty-icon">
          <i class='bx bx-message-square-x'></i>
        </div>
        <h3>{{ searchQuery ? 'Không tìm thấy cuộc trò chuyện' : 'Chưa có cuộc trò chuyện nào' }}</h3>
        <p>{{ searchQuery ? 'Thử tìm kiếm với từ khóa khác' : 'Bắt đầu trò chuyện với người bán hoặc người mua' }}</p>
        <button v-if="!searchQuery" class="btn-start-chat" @click="handleNewChat">
          <i class='bx bx-plus'></i>
          Tạo cuộc trò chuyện đầu tiên
        </button>
      </div>

      <div v-else class="chat-list">
        <div
          v-for="chat in filteredChats"
          :key="chat.id"
          class="chat-item"
          :class="{ 
            'active': selectedChatId === chat.id,
            'unread': chat.unread_count > 0 
          }"
          @click="selectChat(chat)"
        >
          <!-- Avatar -->
          <div class="chat-avatar">
            <img 
              v-if="chat.avatar"
              :src="chat.avatar" 
              :alt="chat.name"
              @error="onAvatarError"
            />
            <div v-else class="avatar-placeholder">
              <i class='bx bx-user'></i>
            </div>
            <span 
              v-if="chat.is_online" 
              class="online-indicator"
            ></span>
          </div>

          <!-- Chat Info -->
          <div class="chat-info">
            <div class="chat-header-row">
              <h3 class="chat-name">{{ chat.name || chat.participant_name }}</h3>
              <span class="chat-time">{{ formatTime(chat.last_message_time) }}</span>
            </div>
            <div class="chat-preview-row">
              <p class="chat-preview">
                <i 
                  v-if="chat.last_message_type === 'image'"
                  class='bx bx-image'
                ></i>
                <i 
                  v-else-if="chat.last_message_type === 'file'"
                  class='bx bx-file'
                ></i>
                <span v-html="formatPreview(chat.last_message)"></span>
              </p>
              <span v-if="chat.unread_count > 0" class="unread-badge">
                {{ chat.unread_count > 99 ? '99+' : chat.unread_count }}
              </span>
            </div>
            <div v-if="chat.product_title" class="chat-product">
              <i class='bx bx-package'></i>
              <span>{{ chat.product_title }}</span>
            </div>
          </div>

          <!-- Action Button -->
          <button 
            class="btn-chat-action"
            @click.stop="handleChatAction(chat)"
            title="Tùy chọn"
          >
            <i class='bx bx-dots-vertical-rounded'></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { useRouter } from 'vue-router'

export default {
  name: 'ListChat',
  setup() {
    const router = useRouter()
    return { router }
  },
  data() {
    return {
      API_BASE_URL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/client',
      chats: [],
      loading: false,
      searchQuery: '',
      selectedChatId: null,
    }
  },
  computed: {
    filteredChats() {
      if (!this.searchQuery.trim()) {
        return this.chats
      }
      const query = this.searchQuery.toLowerCase()
      return this.chats.filter(chat => {
        const name = (chat.name || chat.participant_name || '').toLowerCase()
        const preview = (chat.last_message || '').toLowerCase()
        const product = (chat.product_title || '').toLowerCase()
        return name.includes(query) || preview.includes(query) || product.includes(query)
      })
    }
  },
  mounted() {
    this.checkAuth()
    this.fetchChats()
  },
  methods: {
    checkAuth() {
      const token = localStorage.getItem('key_client')
      if (!token) {
        this.$router.push('/dang-nhap')
      }
    },
    async fetchChats() {
      this.loading = true
      try {
        const token = localStorage.getItem('key_client')
        if (!token) {
          this.$router.push('/dang-nhap')
          return
        }

        const { data } = await axios.get(`${this.API_BASE_URL}/chat`, {
          headers: { 'Authorization': `Bearer ${token}` }
        })
        
        if (data?.status && data?.data?.data) {
          this.chats = data.data.data
        } else {
          this.chats = []
        }
      } catch (error) {
        console.error('Lỗi khi tải danh sách chat:', error)
        if (error.response?.status === 401) {
          this.$router.push('/dang-nhap')
        } else {
          this.$toast?.error?.('Không thể tải danh sách chat. Vui lòng thử lại sau.')
        }
        this.chats = []
      } finally {
        this.loading = false
      }
    },
    selectChat(chat) {
      this.selectedChatId = chat.id
      // Navigate immediately without delay
      this.$router.push(`/chat/${chat.id}`)
    },
    handleNewChat() {
      // TODO: Open modal to select user/product to chat with
      this.$toast?.info?.('Tính năng đang phát triển')
    },
    handleSearch() {
      // Search is handled by computed property
    },
    clearSearch() {
      this.searchQuery = ''
    },
    handleChatAction(chat) {
      // TODO: Show dropdown menu (delete, mute, etc.)
      this.$toast?.info?.('Tính năng đang phát triển')
    },
    formatTime(timeString) {
      if (!timeString) return ''
      const date = new Date(timeString)
      const now = new Date()
      const diff = now - date
      const minutes = Math.floor(diff / 60000)
      const hours = Math.floor(diff / 3600000)
      const days = Math.floor(diff / 86400000)
      
      if (minutes < 1) return 'Vừa xong'
      if (minutes < 60) return `${minutes} phút`
      if (hours < 24) return `${hours} giờ`
      if (days < 7) return `${days} ngày`
      return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' })
    },
    formatPreview(text) {
      if (!text) return ''
      if (text.length > 50) {
        return text.substring(0, 50) + '...'
      }
      return text
    },
    onAvatarError(event) {
      event.target.style.display = 'none'
      if (event.target.nextElementSibling) {
        event.target.nextElementSibling.style.display = 'flex'
      }
    }
  }
}
</script>

<style scoped>
.chat-list-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 24px;
}

.chat-list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding: 24px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.header-content {
  flex: 1;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1a202c;
  margin: 0 0 8px 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.page-title i {
  color: #16a34a;
  font-size: 32px;
}

.page-subtitle {
  font-size: 14px;
  color: #718096;
  margin: 0;
}

.btn-new-chat {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #16a34a, #22c55e);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

.btn-new-chat:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(22, 163, 74, 0.4);
}

.btn-new-chat i {
  font-size: 20px;
}

.search-section {
  margin-bottom: 24px;
}

.search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background: white;
  border-radius: 12px;
  padding: 0 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.search-icon {
  font-size: 20px;
  color: #a0aec0;
  margin-right: 12px;
}

.search-input {
  flex: 1;
  padding: 14px 0;
  border: none;
  outline: none;
  font-size: 14px;
  color: #2d3748;
  background: transparent;
}

.search-input::placeholder {
  color: #a0aec0;
}

.btn-clear-search {
  background: none;
  border: none;
  color: #a0aec0;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  transition: color 0.2s;
}

.btn-clear-search:hover {
  color: #718096;
}

.chat-list-wrapper {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  min-height: 500px;
}

.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 24px;
  text-align: center;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e2e8f0;
  border-top-color: #16a34a;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #e2e8f0, #cbd5e0);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 24px;
}

.empty-icon i {
  font-size: 48px;
  color: #718096;
}

.empty-state h3 {
  font-size: 20px;
  font-weight: 600;
  color: #2d3748;
  margin: 0 0 8px 0;
}

.empty-state p {
  font-size: 14px;
  color: #718096;
  margin: 0 0 24px 0;
}

.btn-start-chat {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: #16a34a;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-start-chat:hover {
  background: #15803d;
}

.chat-list {
  max-height: calc(100vh - 280px);
  overflow-y: auto;
}

.chat-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 20px;
  cursor: pointer;
  transition: background 0.2s;
  border-bottom: 1px solid #e2e8f0;
  position: relative;
}

.chat-item:hover {
  background: #f7fafc;
}

.chat-item.active {
  background: #eff6ff;
  border-left: 4px solid #16a34a;
}

.chat-item.unread {
  background: #f0fdf4;
}

.chat-item.unread .chat-name {
  font-weight: 700;
  color: #1a202c;
}

.chat-avatar {
  position: relative;
  flex-shrink: 0;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  overflow: hidden;
  background: linear-gradient(135deg, #16a34a, #22c55e);
}

.chat-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
}

.online-indicator {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: #22c55e;
  border: 2px solid white;
}

.chat-info {
  flex: 1;
  min-width: 0;
}

.chat-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.chat-name {
  font-size: 16px;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.chat-time {
  font-size: 12px;
  color: #718096;
  flex-shrink: 0;
  margin-left: 8px;
}

.chat-preview-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.chat-preview {
  flex: 1;
  font-size: 14px;
  color: #718096;
  margin: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  display: flex;
  align-items: center;
  gap: 4px;
}

.chat-preview i {
  font-size: 16px;
  color: #a0aec0;
}

.unread-badge {
  flex-shrink: 0;
  min-width: 24px;
  height: 24px;
  padding: 0 8px;
  background: #16a34a;
  color: white;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chat-product {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 6px;
  font-size: 12px;
  color: #718096;
}

.chat-product i {
  font-size: 14px;
}

.btn-chat-action {
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  color: #a0aec0;
  cursor: pointer;
  border-radius: 8px;
  transition: background 0.2s, color 0.2s;
}

.btn-chat-action:hover {
  background: #e2e8f0;
  color: #2d3748;
}

.btn-chat-action i {
  font-size: 20px;
}

/* Scrollbar */
.chat-list::-webkit-scrollbar {
  width: 6px;
}

.chat-list::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.chat-list::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
}

.chat-list::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}

/* Responsive */
@media (max-width: 768px) {
  .chat-list-container {
    padding: 16px;
  }

  .chat-list-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .btn-new-chat {
    width: 100%;
    justify-content: center;
  }
}
</style>
