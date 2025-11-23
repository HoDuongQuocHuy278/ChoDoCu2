import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    host: '0.0.0.0', // Cho phép truy cập từ mạng LAN
    port: 5173,
    strictPort: false, // Tự động chuyển port nếu 5173 bị chiếm
  },
})
