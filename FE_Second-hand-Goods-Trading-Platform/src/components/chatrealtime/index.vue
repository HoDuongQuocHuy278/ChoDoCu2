<template>
  <div class="chat-realtime-container">
    <!-- Chat Header -->
    <div class="chat-header">
      <button 
        class="btn-back"
        @click="$router.push('/listchat')"
        title="Quay lại danh sách chat"
      >
        <i class='bx bx-arrow-back'></i>
      </button>
      <div class="header-info" @click="viewProfile">
        <div class="header-avatar">
          <img 
            v-if="chatUser.avatar"
            :src="chatUser.avatar" 
            :alt="chatUser.name"
            @error="onAvatarError"
          />
          <div v-else class="avatar-placeholder">
            <i class='bx bx-user'></i>
          </div>
          <span v-if="chatUser.is_online" class="online-indicator"></span>
        </div>
        <div class="header-text">
          <h3 class="header-name">{{ chatUser.name || 'Người dùng' }}</h3>
          <p class="header-status">
            <span v-if="chatUser.is_online" class="status-online">Đang hoạt động</span>
            <span v-else class="status-offline">
              Hoạt động {{ formatLastSeen(chatUser.last_seen) }}
            </span>
          </p>
        </div>
      </div>
      <div class="header-actions">
        <button 
          class="btn-header-action"
          @click="handleCall"
          title="Gọi điện"
        >
          <i class='bx bx-phone'></i>
        </button>
        <button 
          class="btn-header-action"
          @click="handleVideoCall"
          title="Gọi video"
        >
          <i class='bx bx-video'></i>
        </button>
        <button 
          class="btn-header-action"
          @click="toggleMenu"
          title="Tùy chọn"
        >
          <i class='bx bx-dots-vertical-rounded'></i>
        </button>
      </div>
    </div>

    <!-- Product Info (if chatting about a product) -->
    <div v-if="productInfo" class="product-info-bar">
      <img 
        :src="productInfo.image" 
        :alt="productInfo.title"
        class="product-thumb"
      />
      <div class="product-details">
        <h4 class="product-title">{{ productInfo.title }}</h4>
        <p class="product-price">{{ formatPrice(productInfo.price) }}</p>
      </div>
      <router-link 
        :to="`/san-pham/${productInfo.id}`"
        class="btn-view-product"
      >
        <i class='bx bx-show'></i>
        Xem sản phẩm
      </router-link>
    </div>

    <!-- Messages Area -->
    <div 
      ref="messagesContainer"
      class="messages-container"
      @scroll="handleScroll"
    >
      <div v-if="loading && messages.length === 0" class="loading-messages">
        <div class="loading-spinner"></div>
        <p>Đang tải tin nhắn...</p>
      </div>

      <div v-else-if="messages.length === 0" class="empty-messages">
        <div class="empty-icon">💬</div>
        <h3>Chưa có tin nhắn nào</h3>
        <p>Bắt đầu cuộc trò chuyện với {{ chatUser.name }}</p>
      </div>

      <div v-else class="messages-list">
        <!-- Product Card (nếu chat về sản phẩm) -->
        <div v-if="productInfo" class="product-card-container">
          <div class="product-card">
            <div class="product-card-header">
              <i class='bx bx-package'></i>
              <span>Sản phẩm đang quan tâm</span>
            </div>
            <router-link 
              :to="`/san-pham/${productInfo.id}`"
              class="product-card-body"
              @click.stop
            >
              <div class="product-card-image">
                <img 
                  :src="productInfo.image || fallbackImg" 
                  :alt="productInfo.title"
                  @error="onProductImgError"
                />
                <div class="product-card-overlay">
                  <i class='bx bx-show'></i>
                  <span>Xem chi tiết</span>
                </div>
              </div>
              <div class="product-card-content">
                <h4 class="product-card-title">{{ productInfo.title }}</h4>
                <p class="product-card-price">{{ formatPrice(productInfo.price) }}</p>
                <div class="product-card-footer">
                  <span class="product-card-link">
                    <i class='bx bx-link-external'></i>
                    Mở trang sản phẩm
                  </span>
                </div>
              </div>
            </router-link>
          </div>
        </div>

        <div
          v-for="(message, index) in messages"
          :key="message.id || index"
          class="message-wrapper"
          :class="{
            'message-sent': message.sender_id === currentUserId,
            'message-received': message.sender_id !== currentUserId
          }"
        >
          <div class="message-content-wrapper">
            <!-- Avatar (for received messages) -->
            <div 
              v-if="message.sender_id !== currentUserId && shouldShowAvatar(message, index)"
              class="message-avatar"
            >
              <img 
                v-if="chatUser.avatar"
                :src="chatUser.avatar" 
                :alt="chatUser.name"
              />
              <div v-else class="avatar-placeholder-small">
                <i class='bx bx-user'></i>
              </div>
            </div>
            
            <!-- Message Bubble -->
            <div class="message-bubble-wrapper">
              <div 
                class="message-bubble"
                :class="{
                  'bubble-sent': message.sender_id === currentUserId,
                  'bubble-received': message.sender_id !== currentUserId
                }"
              >
                <!-- Image Message -->
                <img 
                  v-if="message.type === 'image'"
                  :src="message.content"
                  :alt="message.caption"
                  class="message-image"
                  @click="previewImage(message.content)"
                />
                
                <!-- File Message -->
                <div v-else-if="message.type === 'file'" class="message-file">
                  <i class='bx bx-file'></i>
                  <div class="file-info">
                    <p class="file-name">{{ message.file_name }}</p>
                    <p class="file-size">{{ formatFileSize(message.file_size) }}</p>
                  </div>
                  <a 
                    :href="message.content" 
                    download
                    class="btn-download"
                  >
                    <i class='bx bx-download'></i>
                  </a>
                </div>
                
                <!-- Text Message -->
                <div v-else class="message-text">
                  <p v-html="formatMessage(message.content)"></p>
                </div>
                
                <!-- Message Footer -->
                <div class="message-footer">
                  <span class="message-time">{{ formatMessageTime(message.created_at) }}</span>
                  <i 
                    v-if="message.sender_id === currentUserId"
                    class="message-status"
                    :class="{
                      'bx-check': message.status === 'sent',
                      'bx-check-double': message.status === 'delivered',
                      'bx-check-double': message.status === 'read' && message.is_read
                    }"
                    :style="message.status === 'read' && message.is_read ? 'color: #4a90e2;' : ''"
                  ></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Typing Indicator -->
        <div v-if="isTyping" class="typing-indicator-wrapper">
          <div class="message-avatar">
            <img 
              v-if="chatUser.avatar"
              :src="chatUser.avatar" 
              :alt="chatUser.name"
            />
            <div v-else class="avatar-placeholder-small">
              <i class='bx bx-user'></i>
            </div>
          </div>
          <div class="typing-indicator">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Input Area -->
    <div class="input-area">
      <div class="input-wrapper">
        <!-- Attachment Button -->
        <button 
          class="btn-attachment"
          @click="toggleAttachmentMenu"
          title="Đính kèm"
        >
          <i class='bx bx-paperclip'></i>
        </button>
        
        <!-- Emoji Button -->
        <button 
          class="btn-emoji"
          @click="toggleEmojiPicker"
          title="Emoji"
        >
          <i class='bx bx-smile'></i>
        </button>
        
        <!-- Message Input -->
        <textarea
          ref="messageInput"
          v-model="messageText"
          @input="handleTyping"
          @keydown.enter.exact="handleEnterKey"
          @keydown.shift.enter.exact="handleShiftEnter"
          type="text"
          placeholder="Nhập tin nhắn..."
          class="message-input"
          rows="1"
        ></textarea>
        
        <!-- Send Button -->
        <button 
          class="btn-send"
          :disabled="!canSend"
          @click="sendMessage"
          :title="canSend ? 'Gửi tin nhắn' : 'Nhập tin nhắn để gửi'"
        >
          <i v-if="!canSend" class='bx bx-send'></i>
          <i v-else class='bx bx-send' style="color: #16a34a;"></i>
        </button>
      </div>
      
      <!-- Attachment Menu -->
      <div v-if="showAttachmentMenu" class="attachment-menu">
        <button class="attachment-item" @click="handleImageUpload">
          <i class='bx bx-image'></i>
          <span>Ảnh</span>
        </button>
        <button class="attachment-item" @click="handleFileUpload">
          <i class='bx bx-file'></i>
          <span>Tệp tin</span>
        </button>
        <button class="attachment-item" @click="handleProductShare">
          <i class='bx bx-package'></i>
          <span>Sản phẩm</span>
        </button>
      </div>
      
      <!-- Emoji Picker -->
      <div v-if="showEmojiPicker" class="emoji-picker">
        <div class="emoji-grid">
          <span
            v-for="emoji in commonEmojis"
            :key="emoji"
            class="emoji-item"
            @click="insertEmoji(emoji)"
          >{{ emoji }}</span>
        </div>
      </div>
    </div>

    <!-- Image Preview Modal -->
    <div v-if="previewImageUrl" class="image-preview-modal" @click="closeImagePreview">
      <div class="preview-content" @click.stop>
        <button class="btn-close-preview" @click="closeImagePreview">
          <i class='bx bx-x'></i>
        </button>
        <img :src="previewImageUrl" alt="Preview" />
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'

export default {
  name: 'ChatRealtime',
  setup() {
    const route = useRoute()
    const router = useRouter()
    return { route, router }
  },
  data() {
    return {
      API_BASE_URL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/client',
      chatId: null,
      currentUserId: null,
      chatUser: {
        id: null,
        name: '',
        avatar: null,
        is_online: false,
        last_seen: null
      },
      productInfo: null,
      messages: [],
      messageText: '',
      loading: false,
      isTyping: false,
      showAttachmentMenu: false,
      showEmojiPicker: false,
      previewImageUrl: null,
      commonEmojis: ['😀', '😂', '😍', '🤔', '👍', '❤️', '🎉', '🔥', '💯', '👌', '😊', '😢', '😡', '👏', '🙏'],
      typingTimeout: null,
      lastTypingTime: null,
      pollInterval: null,
      fallbackImg: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQ1MCIgdmlld0JveD0iMCAwIDYwMCA0NTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI2MDAiIGhlaWdodD0iNDUwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0zMDAgMTgwQzM0NS4yMjkgMTgwIDM4MiAxNDMuMjI5IDM4MiA5OEMzODIgNTIuNzcwOSAzNDUuMjI5IDE2IDMwMCAxNkMyNTQuNzcwIDE2IDIxOCA1Mi43NzA5IDIxOCA5OEMyMTggMTQzLjIyOSAyNTQuNzcwIDE4MCAzMDAgMTgwWiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNMzAwIDI3MEMyNTAgMjcwIDIxMCAyOTAgMTg1IDMyMEg0MTVDMzkwIDI5MCAzNTAgMjcwIDMwMCAyNzBaIiBmaWxsPSIjOUNBM0FGIi8+Cjx0ZXh0IHg9IjMwMCIgeT0iMzQwIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9IiM5Q0EzQUYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPk5vIEltYWdlPC90ZXh0Pgo8L3N2Zz4='
    }
  },
  computed: {
    canSend() {
      return this.messageText.trim().length > 0 && !this.loading
    }
  },
  mounted() {
    this.chatId = this.route.params.id
    // If chatId is a string like "chat-{id}-{id}", extract numeric ID if possible
    // Otherwise, we'll need to handle it differently
    if (typeof this.chatId === 'string' && this.chatId.startsWith('chat-')) {
      // Try to extract numeric ID from room identifier
      // For now, keep it as is - backend should handle it
    }
    this.currentUserId = this.getCurrentUserId()
    this.checkAuth()
    // Load data in parallel for faster page load
    Promise.all([
      this.loadChatInfo(),
      this.loadMessages()
    ]).catch(err => {
      console.error('Error loading chat data:', err)
    })
    // TODO: Setup WebSocket connection for real-time messages
    // this.setupWebSocket()
    this.autoResizeTextarea()
    
    // Poll for new messages every 5 seconds (reduced frequency)
    this.pollInterval = setInterval(() => {
      if (this.chatId && !this.loading) {
        this.loadMessages()
      }
    }, 5000) // Increased from 3s to 5s
  },
  beforeUnmount() {
    // TODO: Close WebSocket connection
    // this.closeWebSocket()
    if (this.typingTimeout) {
      clearTimeout(this.typingTimeout)
    }
    if (this.pollInterval) {
      clearInterval(this.pollInterval)
    }
  },
  methods: {
    checkAuth() {
      const token = localStorage.getItem('key_client')
      if (!token) {
        this.$router.push('/dang-nhap')
      }
    },
    getCurrentUserId() {
      try {
        const userInfo = localStorage.getItem('user_info')
        if (userInfo) {
          return JSON.parse(userInfo).id
        }
      } catch (e) {
        console.warn('Cannot get user ID', e)
      }
      return null
    },
    async loadChatInfo() {
      try {
        const token = localStorage.getItem('key_client')
        if (!token) {
          this.$router.push('/dang-nhap')
          return
        }

        // Handle both numeric ID and string room identifier
        const chatIdOrRoom = this.chatId
        
        // Try to get chat by ID first (if numeric), otherwise use room
        let apiUrl = `${this.API_BASE_URL}/chat/${chatIdOrRoom}`
        if (isNaN(chatIdOrRoom)) {
          // If it's a room identifier (chat-{id}-{id}), try to find chat by room
          // For now, we'll use the chat ID directly
          apiUrl = `${this.API_BASE_URL}/chat/${chatIdOrRoom}`
        }

        const { data } = await axios.get(apiUrl, {
          headers: { 'Authorization': `Bearer ${token}` }
        })
        
        if (data?.status && data?.data) {
          const chatData = data.data
          if (chatData.other_user) {
            this.chatUser = {
              id: chatData.other_user.id,
              name: chatData.other_user.name,
              avatar: null,
              is_online: false, // TODO: Implement online status
              last_seen: null
            }
          }
          if (chatData.product) {
            this.productInfo = {
              id: chatData.product.id,
              title: chatData.product.title,
              price: chatData.product.price,
              image: Array.isArray(chatData.product.image) 
                ? chatData.product.image[0] 
                : (typeof chatData.product.image === 'string' 
                  ? JSON.parse(chatData.product.image || '[]')[0] || null
                  : chatData.product.image)
            }
          }
        }
      } catch (error) {
        console.error('Lỗi khi tải thông tin chat:', error)
        if (error.response?.status === 401 || error.response?.status === 403) {
          this.$router.push('/dang-nhap')
        }
      }
    },
    async loadMessages() {
      // Only show loading on initial load, not on polling
      const isInitialLoad = this.messages.length === 0
      if (isInitialLoad) {
        this.loading = true
      }
      
      try {
        const token = localStorage.getItem('key_client')
        if (!token) {
          this.$router.push('/dang-nhap')
          return
        }

        const chatIdOrRoom = this.chatId
        const { data } = await axios.get(`${this.API_BASE_URL}/chat/${chatIdOrRoom}/messages`, {
          headers: { 'Authorization': `Bearer ${token}` },
          params: { per_page: 100 }
        })
        
        if (data?.status && data?.data?.data) {
          this.messages = data.data.data.map(msg => ({
            ...msg,
            status: msg.is_read ? 'read' : 'delivered'
          }))
        } else {
          if (isInitialLoad) {
            this.messages = []
          }
        }
        
        this.$nextTick(() => {
          this.scrollToBottom()
        })
      } catch (error) {
        console.error('Lỗi khi tải tin nhắn:', error)
        if (error.response?.status === 401 || error.response?.status === 403) {
          this.$router.push('/dang-nhap')
        }
        if (isInitialLoad) {
          this.messages = []
        }
      } finally {
        if (isInitialLoad) {
          this.loading = false
        }
      }
    },
    async sendMessage() {
      if (!this.canSend) return
      
      const content = this.messageText.trim()
      this.messageText = ''
      this.showEmojiPicker = false
      this.showAttachmentMenu = false
      this.autoResizeTextarea()
      
      const tempId = Date.now()
      const newMessage = {
        id: tempId,
        sender_id: this.currentUserId,
        content,
        type: 'text',
        created_at: new Date().toISOString(),
        status: 'sending',
        is_read: false
      }
      
      this.messages.push(newMessage)
      this.$nextTick(() => {
        this.scrollToBottom()
      })
      
      try {
        const token = localStorage.getItem('key_client')
        if (!token) {
          this.$router.push('/dang-nhap')
          return
        }

        const chatIdOrRoom = this.chatId
        const { data } = await axios.post(
          `${this.API_BASE_URL}/chat/${chatIdOrRoom}/messages`,
          { content, type: 'text' },
          { headers: { 'Authorization': `Bearer ${token}` } }
        )
        
        if (data?.status && data?.data) {
          const index = this.messages.findIndex(m => m.id === tempId)
          if (index > -1) {
            this.messages[index] = {
              id: data.data.id,
              sender_id: data.data.sender_id,
              content: data.data.content,
              type: data.data.type,
              created_at: data.data.created_at,
              status: 'delivered',
              is_read: false
            }
          } else {
            // If temp message not found, add new message
            this.messages.push({
              id: data.data.id,
              sender_id: data.data.sender_id,
              content: data.data.content,
              type: data.data.type,
              created_at: data.data.created_at,
              status: 'delivered',
              is_read: false
            })
          }
          this.$nextTick(() => {
            this.scrollToBottom()
          })
        }
      } catch (error) {
        console.error('Lỗi khi gửi tin nhắn:', error)
        const index = this.messages.findIndex(m => m.id === tempId)
        if (index > -1) {
          this.messages[index].status = 'error'
        }
        if (error.response?.status === 401 || error.response?.status === 403) {
          this.$router.push('/dang-nhap')
        } else {
          this.$toast?.error?.('Không thể gửi tin nhắn. Vui lòng thử lại.')
        }
      }
    },
    handleTyping() {
      // Auto-resize textarea
      this.autoResizeTextarea()
      
      // Send typing indicator
      const now = Date.now()
      if (!this.lastTypingTime || now - this.lastTypingTime > 3000) {
        // TODO: Send typing indicator via WebSocket
        // this.sendTypingIndicator()
        this.lastTypingTime = now
      }
      
      if (this.typingTimeout) {
        clearTimeout(this.typingTimeout)
      }
      
      this.typingTimeout = setTimeout(() => {
        // Stop typing indicator
        // this.stopTypingIndicator()
      }, 3000)
    },
    handleEnterKey(e) {
      if (!e.shiftKey) {
        e.preventDefault()
        this.sendMessage()
      }
    },
    handleShiftEnter(e) {
      // Allow new line
    },
    autoResizeTextarea() {
      this.$nextTick(() => {
        const textarea = this.$refs.messageInput
        if (textarea) {
          textarea.style.height = 'auto'
          textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px'
        }
      })
    },
    scrollToBottom() {
      const container = this.$refs.messagesContainer
      if (container) {
        container.scrollTop = container.scrollHeight
      }
    },
    handleScroll() {
      // TODO: Load more messages when scrolling up
    },
    shouldShowAvatar(message, index) {
      if (message.sender_id === this.currentUserId) return false
      if (index === 0) return true
      const prevMessage = this.messages[index - 1]
      if (!prevMessage) return true
      if (prevMessage.sender_id !== message.sender_id) return true
      const timeDiff = new Date(message.created_at) - new Date(prevMessage.created_at)
      return timeDiff > 300000 // 5 minutes
    },
    toggleAttachmentMenu() {
      this.showAttachmentMenu = !this.showAttachmentMenu
      this.showEmojiPicker = false
    },
    toggleEmojiPicker() {
      this.showEmojiPicker = !this.showEmojiPicker
      this.showAttachmentMenu = false
    },
    insertEmoji(emoji) {
      const textarea = this.$refs.messageInput
      if (textarea) {
        const cursorPos = textarea.selectionStart
        const textBefore = this.messageText.substring(0, cursorPos)
        const textAfter = this.messageText.substring(cursorPos)
        this.messageText = textBefore + emoji + textAfter
        this.$nextTick(() => {
          textarea.focus()
          textarea.setSelectionRange(cursorPos + emoji.length, cursorPos + emoji.length)
          this.autoResizeTextarea()
        })
      }
    },
    handleImageUpload() {
      const input = document.createElement('input')
      input.type = 'file'
      input.accept = 'image/*'
      input.onchange = (e) => {
        const file = e.target.files[0]
        if (file) {
          // TODO: Upload image and send message
          this.$toast?.info?.('Tính năng đang phát triển')
        }
      }
      input.click()
      this.showAttachmentMenu = false
    },
    handleFileUpload() {
      const input = document.createElement('input')
      input.type = 'file'
      input.onchange = (e) => {
        const file = e.target.files[0]
        if (file) {
          // TODO: Upload file and send message
          this.$toast?.info?.('Tính năng đang phát triển')
        }
      }
      input.click()
      this.showAttachmentMenu = false
    },
    handleProductShare() {
      // TODO: Open product selection modal
      this.$toast?.info?.('Tính năng đang phát triển')
      this.showAttachmentMenu = false
    },
    previewImage(url) {
      this.previewImageUrl = url
    },
    closeImagePreview() {
      this.previewImageUrl = null
    },
    handleCall() {
      // TODO: Initiate voice call
      this.$toast?.info?.('Tính năng đang phát triển')
    },
    handleVideoCall() {
      // TODO: Initiate video call
      this.$toast?.info?.('Tính năng đang phát triển')
    },
    toggleMenu() {
      // TODO: Show menu (block, report, etc.)
      this.$toast?.info?.('Tính năng đang phát triển')
    },
    viewProfile() {
      // TODO: Navigate to user profile
      this.$toast?.info?.('Tính năng đang phát triển')
    },
    formatMessageTime(timeString) {
      if (!timeString) return ''
      const date = new Date(timeString)
      const now = new Date()
      const diff = now - date
      const minutes = Math.floor(diff / 60000)
      const hours = Math.floor(diff / 3600000)
      const days = Math.floor(diff / 86400000)
      
      if (minutes < 1) return 'Vừa xong'
      if (minutes < 60) return `${minutes} phút`
      if (hours < 24) {
        if (hours === 1) return '1 giờ'
        return `${hours} giờ`
      }
      if (days === 0) return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
      if (days === 1) return 'Hôm qua'
      if (days < 7) return `${days} ngày trước`
      return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' })
    },
    formatLastSeen(timeString) {
      if (!timeString) return ''
      const date = new Date(timeString)
      const now = new Date()
      const diff = now - date
      const minutes = Math.floor(diff / 60000)
      const hours = Math.floor(diff / 3600000)
      
      if (minutes < 1) return 'vừa xong'
      if (minutes < 60) return `${minutes} phút trước`
      if (hours < 24) return `${hours} giờ trước`
      return date.toLocaleDateString('vi-VN')
    },
    formatPrice(price) {
      return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
      }).format(price)
    },
    formatFileSize(bytes) {
      if (!bytes) return '0 B'
      const k = 1024
      const sizes = ['B', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
    },
    formatMessage(text) {
      if (!text) return ''
      // Simple URL detection
      const urlRegex = /(https?:\/\/[^\s]+)/g
      return text.replace(urlRegex, '<a href="$1" target="_blank">$1</a>')
    },
    onAvatarError(event) {
      event.target.style.display = 'none'
      if (event.target.nextElementSibling) {
        event.target.nextElementSibling.style.display = 'flex'
      }
    },
    onProductImgError(event) {
      if (event.target.dataset.fallbackSet === 'true') {
        event.target.style.display = 'none'
        return
      }
      event.target.dataset.fallbackSet = 'true'
      event.target.src = this.fallbackImg
      event.target.onerror = null
    }
  },
  watch: {
    messages() {
      this.$nextTick(() => {
        this.scrollToBottom()
      })
    }
  }
}
</script>

<style scoped>
.chat-realtime-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #f0f2f5;
}

/* Header */
.chat-header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px 20px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.btn-back {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: none;
  border: none;
  color: #4a5568;
  cursor: pointer;
  border-radius: 10px;
  transition: background 0.2s;
}

.btn-back:hover {
  background: #f7fafc;
}

.btn-back i {
  font-size: 24px;
}

.header-info {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  padding: 4px;
  border-radius: 10px;
  transition: background 0.2s;
}

.header-info:hover {
  background: #f7fafc;
}

.header-avatar {
  position: relative;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
  background: linear-gradient(135deg, #16a34a, #22c55e);
  flex-shrink: 0;
}

.header-avatar img {
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

.avatar-placeholder-small {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 14px;
  background: linear-gradient(135deg, #16a34a, #22c55e);
}

.online-indicator {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #22c55e;
  border: 2px solid white;
}

.header-text {
  flex: 1;
  min-width: 0;
}

.header-name {
  font-size: 16px;
  font-weight: 600;
  color: #1a202c;
  margin: 0 0 2px 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.header-status {
  font-size: 12px;
  margin: 0;
  color: #718096;
}

.status-online {
  color: #22c55e;
  font-weight: 500;
}

.status-offline {
  color: #a0aec0;
}

.header-actions {
  display: flex;
  gap: 8px;
}

.btn-header-action {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: none;
  border: none;
  color: #4a5568;
  cursor: pointer;
  border-radius: 10px;
  transition: background 0.2s, color 0.2s;
}

.btn-header-action:hover {
  background: #f7fafc;
  color: #16a34a;
}

.btn-header-action i {
  font-size: 20px;
}

/* Product Info Bar */
.product-info-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  background: white;
  border-bottom: 1px solid #e2e8f0;
}

.product-thumb {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  object-fit: cover;
}

.product-details {
  flex: 1;
  min-width: 0;
}

.product-title {
  font-size: 14px;
  font-weight: 600;
  color: #1a202c;
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

.btn-view-product {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #16a34a;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  transition: background 0.2s;
}

.btn-view-product:hover {
  background: #15803d;
}

/* Product Card in Messages */
.product-card-container {
  padding: 16px 0;
  margin-bottom: 8px;
}

.product-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
  border: 1px solid #e2e8f0;
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.product-card-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: linear-gradient(135deg, #f0fdf4, #dcfce7);
  border-bottom: 1px solid #e2e8f0;
  font-size: 13px;
  font-weight: 600;
  color: #16a34a;
}

.product-card-header i {
  font-size: 18px;
}

.product-card-body {
  display: flex;
  gap: 16px;
  padding: 16px;
  text-decoration: none;
  color: inherit;
  transition: background 0.2s;
}

.product-card-body:hover {
  background: #f9fafb;
  color: inherit;
}

.product-card-image {
  position: relative;
  flex-shrink: 0;
  width: 120px;
  height: 120px;
  border-radius: 12px;
  overflow: hidden;
  background: #f3f4f6;
}

.product-card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.product-card-body:hover .product-card-image img {
  transform: scale(1.05);
}

.product-card-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  opacity: 0;
  transition: opacity 0.2s;
  color: white;
  font-size: 14px;
  font-weight: 600;
}

.product-card-overlay i {
  font-size: 24px;
}

.product-card-body:hover .product-card-overlay {
  opacity: 1;
}

.product-card-content {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.product-card-title {
  font-size: 16px;
  font-weight: 600;
  color: #1a202c;
  margin: 0 0 8px 0;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  line-height: 1.4;
}

.product-card-price {
  font-size: 18px;
  font-weight: 700;
  color: #16a34a;
  margin: 0 0 12px 0;
}

.product-card-footer {
  margin-top: auto;
}

.product-card-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #16a34a;
  font-weight: 500;
  transition: color 0.2s;
}

.product-card-body:hover .product-card-link {
  color: #15803d;
}

.product-card-link i {
  font-size: 16px;
}

/* Messages Container */
.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  background: linear-gradient(180deg, #f0f2f5 0%, #e8ecf1 100%);
}

.loading-messages,
.empty-messages {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  text-align: center;
  color: #718096;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.empty-messages h3 {
  font-size: 18px;
  font-weight: 600;
  color: #2d3748;
  margin: 0 0 8px 0;
}

.empty-messages p {
  font-size: 14px;
  margin: 0;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e2e8f0;
  border-top-color: #16a34a;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.messages-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.message-wrapper {
  display: flex;
  width: 100%;
}

.message-wrapper.message-sent {
  justify-content: flex-end;
}

.message-wrapper.message-received {
  justify-content: flex-start;
}

.message-content-wrapper {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  max-width: 70%;
}

.message-wrapper.message-sent .message-content-wrapper {
  flex-direction: row-reverse;
}

.message-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  background: linear-gradient(135deg, #16a34a, #22c55e);
}

.message-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.message-bubble-wrapper {
  flex: 1;
}

.message-bubble {
  padding: 10px 14px;
  border-radius: 16px;
  position: relative;
  word-wrap: break-word;
}

.bubble-sent {
  background: linear-gradient(135deg, #16a34a, #22c55e);
  color: white;
  border-bottom-right-radius: 4px;
}

.bubble-received {
  background: white;
  color: #1a202c;
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message-image {
  max-width: 300px;
  max-height: 300px;
  border-radius: 8px;
  cursor: pointer;
  margin-bottom: 8px;
}

.message-file {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 8px;
  margin-bottom: 8px;
}

.message-file i {
  font-size: 32px;
  color: #4a5568;
}

.file-info {
  flex: 1;
  min-width: 0;
}

.file-name {
  font-size: 14px;
  font-weight: 600;
  margin: 0 0 4px 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-size {
  font-size: 12px;
  color: #718096;
  margin: 0;
}

.btn-download {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  background: rgba(0, 0, 0, 0.1);
  color: #4a5568;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-download:hover {
  background: rgba(0, 0, 0, 0.15);
}

.message-text {
  font-size: 14px;
  line-height: 1.5;
}

.message-text p {
  margin: 0;
}

.message-text a {
  color: inherit;
  text-decoration: underline;
}

.bubble-sent .message-text a {
  color: rgba(255, 255, 255, 0.9);
}

.message-footer {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 6px;
  font-size: 11px;
  opacity: 0.7;
}

.message-time {
  font-size: 11px;
}

.message-status {
  font-size: 14px;
  margin-left: auto;
}

/* Typing Indicator */
.typing-indicator-wrapper {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  margin-top: 8px;
}

.typing-indicator {
  display: flex;
  gap: 4px;
  padding: 12px 16px;
  background: white;
  border-radius: 16px;
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.typing-indicator span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #a0aec0;
  animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  0%, 60%, 100% {
    transform: translateY(0);
    opacity: 0.5;
  }
  30% {
    transform: translateY(-10px);
    opacity: 1;
  }
}

/* Input Area */
.input-area {
  position: relative;
  padding: 16px 20px;
  background: white;
  border-top: 1px solid #e2e8f0;
}

.input-wrapper {
  display: flex;
  align-items: flex-end;
  gap: 8px;
}

.btn-attachment,
.btn-emoji {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: none;
  border: none;
  color: #4a5568;
  cursor: pointer;
  border-radius: 10px;
  transition: background 0.2s, color 0.2s;
  flex-shrink: 0;
}

.btn-attachment:hover,
.btn-emoji:hover {
  background: #f7fafc;
  color: #16a34a;
}

.btn-attachment i,
.btn-emoji i {
  font-size: 22px;
}

.message-input {
  flex: 1;
  min-height: 40px;
  max-height: 120px;
  padding: 10px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 20px;
  font-size: 14px;
  font-family: inherit;
  color: #1a202c;
  resize: none;
  outline: none;
  transition: border-color 0.2s;
  line-height: 1.5;
}

.message-input:focus {
  border-color: #16a34a;
}

.message-input::placeholder {
  color: #a0aec0;
}

.btn-send {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: #f7fafc;
  border: none;
  color: #a0aec0;
  cursor: pointer;
  border-radius: 50%;
  transition: background 0.2s, transform 0.2s;
  flex-shrink: 0;
}

.btn-send:not(:disabled):hover {
  background: #16a34a;
  color: white;
  transform: scale(1.05);
}

.btn-send:disabled {
  cursor: not-allowed;
}

.btn-send i {
  font-size: 20px;
}

/* Attachment Menu */
.attachment-menu {
  position: absolute;
  bottom: 100%;
  left: 20px;
  margin-bottom: 8px;
  display: flex;
  gap: 8px;
  padding: 8px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 100;
}

.attachment-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: #f7fafc;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: background 0.2s, transform 0.2s;
  min-width: 80px;
}

.attachment-item:hover {
  background: #e2e8f0;
  transform: translateY(-2px);
}

.attachment-item i {
  font-size: 24px;
  color: #4a5568;
}

.attachment-item span {
  font-size: 12px;
  color: #4a5568;
  font-weight: 500;
}

/* Emoji Picker */
.emoji-picker {
  position: absolute;
  bottom: 100%;
  left: 60px;
  margin-bottom: 8px;
  padding: 12px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 100;
  max-width: 300px;
}

.emoji-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 8px;
}

.emoji-item {
  font-size: 24px;
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: background 0.2s, transform 0.2s;
  text-align: center;
}

.emoji-item:hover {
  background: #f7fafc;
  transform: scale(1.2);
}

/* Image Preview Modal */
.image-preview-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.preview-content {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
}

.preview-content img {
  max-width: 100%;
  max-height: 90vh;
  border-radius: 8px;
}

.btn-close-preview {
  position: absolute;
  top: -40px;
  right: 0;
  width: 36px;
  height: 36px;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.btn-close-preview:hover {
  background: rgba(255, 255, 255, 0.3);
}

.btn-close-preview i {
  font-size: 20px;
}

/* Scrollbar */
.messages-container::-webkit-scrollbar {
  width: 6px;
}

.messages-container::-webkit-scrollbar-track {
  background: transparent;
}

.messages-container::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
}

.messages-container::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}

/* Responsive */
@media (max-width: 768px) {
  .chat-header {
    padding: 10px 16px;
  }

  .messages-container {
    padding: 16px;
  }

  .message-content-wrapper {
    max-width: 85%;
  }

  .input-area {
    padding: 12px 16px;
  }

  .attachment-menu {
    left: 16px;
  }

  .emoji-picker {
    left: 56px;
  }
}
</style>
