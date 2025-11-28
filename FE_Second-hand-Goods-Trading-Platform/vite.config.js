import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    host: '0.0.0.0', // Cho phép truy cập từ mạng LAN
    port: 5173,
    strictPort: false, // Tự động chuyển port nếu 5173 bị chiếm
    proxy: {
      // Proxy API requests đến Backend - Cho phép chia sẻ qua 1 URL duy nhất
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
      },
      // Proxy Chatbox requests
      '/chatbox': {
      //đổi api
        target: 'http://127.0.0.1:5000',
        // target: 'http://172.25.15.135:5000',
        changeOrigin: true,
        secure: false,
        rewrite: (path) => path.replace(/^\/chatbox/, ''),
      },
    },
  },
})
