<template>
  <footer class="modern-footer">
    <div class="footer-background">
      <div class="footer-gradient"></div>
    </div>
    
    <div class="footer-container">
      <!-- Main Footer Content -->
      <div class="footer-main">
        <!-- Brand Section -->
        <div class="footer-brand">
          <div class="brand-logo">
            <h2 class="logo-text">🛒 Chợ Đồ Cũ</h2>
          </div>
          <p class="brand-description">
            Nền tảng mua bán đồ cũ uy tín nhất Việt Nam. 
            Kết nối người mua và người bán một cách nhanh chóng, an toàn và tiện lợi.
          </p>
          <div class="social-links">
            <a href="#" class="social-link" aria-label="Facebook">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </a>
            <a href="#" class="social-link" aria-label="Instagram">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              </svg>
            </a>
            <a href="#" class="social-link" aria-label="Twitter">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
              </svg>
            </a>
            <a href="#" class="social-link" aria-label="YouTube">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
              </svg>
            </a>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-column">
          <h3 class="footer-title">Khám phá</h3>
          <ul class="footer-links">
            <li><router-link to="/trang-chu" class="footer-link">Trang chủ</router-link></li>
            <li><router-link to="/danh-sach-san-pham" class="footer-link">Sản phẩm</router-link></li>
            <li><router-link to="/search" class="footer-link">Tìm kiếm</router-link></li>
            <li><router-link to="/sell" class="footer-link">Đăng bán</router-link></li>
          </ul>
        </div>

        <!-- For Sellers -->
        <div class="footer-column">
          <h3 class="footer-title">Dành cho người bán</h3>
          <ul class="footer-links">
            <li><router-link to="/dang-ky-ban" class="footer-link">Đăng ký bán hàng</router-link></li>
            <li><router-link to="/nguoi-ban/san-pham" class="footer-link">Quản lý sản phẩm</router-link></li>
            <li><router-link to="/nguoi-ban/quan-ly-don-hang" class="footer-link">Quản lý đơn hàng</router-link></li>
            <li><router-link to="/nguoi-ban/lich-su-ban-hang" class="footer-link">Lịch sử bán hàng</router-link></li>
          </ul>
        </div>

        <!-- Support -->
        <div class="footer-column">
          <h3 class="footer-title">Hỗ trợ</h3>
          <ul class="footer-links">
            <li><a href="#" class="footer-link">Câu hỏi thường gặp</a></li>
            <li><a href="#" class="footer-link">Hướng dẫn mua hàng</a></li>
            <li><a href="#" class="footer-link">Hướng dẫn bán hàng</a></li>
            <li><a href="#" class="footer-link">Chính sách bảo mật</a></li>
            <li><a href="#" class="footer-link">Điều khoản sử dụng</a></li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="footer-column">
          <h3 class="footer-title">Liên hệ</h3>
          <ul class="footer-contact">
            <li class="contact-item">
              <span class="contact-icon">📧</span>
              <span>support@chodocu.vn</span>
            </li>
            <li class="contact-item">
              <span class="contact-icon">📞</span>
              <span>1900 1234</span>
            </li>
            <li class="contact-item">
              <span class="contact-icon">📍</span>
              <span>123 Đường ABC, Quận XYZ, TP.HCM</span>
            </li>
          </ul>
          <div class="newsletter">
            <h4 class="newsletter-title">Đăng ký nhận tin</h4>
            <div class="newsletter-form">
              <input type="email" placeholder="Email của bạn" class="newsletter-input" />
              <button class="newsletter-btn">Gửi</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Bottom -->
      <div class="footer-bottom">
        <div class="footer-bottom-content">
          <p class="copyright">
            Copyright © 2025 <strong>Chợ Đồ Cũ</strong>. Tất cả quyền được bảo lưu.
          </p>
          <div class="footer-bottom-links">
            <a href="#" class="bottom-link">Chính sách</a>
            <span class="separator">•</span>
            <a href="#" class="bottom-link">Điều khoản</a>
            <span class="separator">•</span>
            <a href="#" class="bottom-link">Bảo mật</a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'BotRocker'
}
</script>

<style scoped>
/* ===== MODERN FOOTER DESIGN ===== */
.modern-footer {
  position: relative;
  background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
  color: #e2e8f0;
  margin-top: 80px;
  overflow: hidden;
  z-index: 10;
  width: 100%;
}

.footer-background {
  position: absolute;
  inset: 0;
  overflow: hidden;
  z-index: 0;
}

.footer-gradient {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, 
    transparent 0%, 
    rgba(22, 163, 74, 0.5) 50%, 
    transparent 100%);
}

.footer-container {
  position: relative;
  z-index: 1;
  max-width: 1400px;
  margin: 0 auto;
  padding: 60px 24px 0;
}

/* Main Footer Content */
.footer-main {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr 1.5fr;
  gap: 48px;
  padding-bottom: 48px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-brand {
  max-width: 350px;
}

.brand-logo {
  margin-bottom: 16px;
}

.logo-text {
  font-size: 28px;
  font-weight: 800;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
}

.brand-description {
  font-size: 14px;
  line-height: 1.7;
  color: #94a3b8;
  margin-bottom: 24px;
}

.social-links {
  display: flex;
  gap: 12px;
}

.social-link {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  color: #94a3b8;
  transition: all 0.3s ease;
  text-decoration: none;
}

.social-link svg {
  width: 20px;
  height: 20px;
}

.social-link:hover {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  border-color: transparent;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(22, 163, 74, 0.3);
}

/* Footer Columns */
.footer-column {
  display: flex;
  flex-direction: column;
}

.footer-title {
  font-size: 16px;
  font-weight: 700;
  color: white;
  margin: 0 0 20px;
  letter-spacing: 0.5px;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.footer-link {
  color: #94a3b8;
  text-decoration: none;
  font-size: 14px;
  transition: all 0.3s ease;
  display: inline-block;
  position: relative;
  padding-left: 0;
}

.footer-link::before {
  content: '→';
  position: absolute;
  left: -20px;
  opacity: 0;
  transition: all 0.3s ease;
  color: #22c55e;
}

.footer-link:hover {
  color: #22c55e;
  padding-left: 20px;
  transform: translateX(4px);
}

.footer-link:hover::before {
  opacity: 1;
  left: 0;
}

/* Contact Section */
.footer-contact {
  list-style: none;
  padding: 0;
  margin: 0 0 32px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.contact-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  font-size: 14px;
  color: #94a3b8;
  line-height: 1.6;
}

.contact-icon {
  font-size: 18px;
  flex-shrink: 0;
  margin-top: 2px;
}

/* Newsletter */
.newsletter {
  margin-top: auto;
}

.newsletter-title {
  font-size: 14px;
  font-weight: 600;
  color: white;
  margin: 0 0 12px;
}

.newsletter-form {
  display: flex;
  gap: 8px;
}

.newsletter-input {
  flex: 1;
  padding: 12px 16px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  color: white;
  font-size: 14px;
  outline: none;
  transition: all 0.3s ease;
}

.newsletter-input::placeholder {
  color: #64748b;
}

.newsletter-input:focus {
  background: rgba(255, 255, 255, 0.08);
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.newsletter-btn {
  padding: 12px 24px;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.newsletter-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(22, 163, 74, 0.4);
}

/* Footer Bottom */
.footer-bottom {
  padding: 32px 0;
}

.footer-bottom-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

.copyright {
  margin: 0;
  font-size: 14px;
  color: #64748b;
}

.copyright strong {
  color: #94a3b8;
  font-weight: 600;
}

.footer-bottom-links {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.bottom-link {
  color: #94a3b8;
  text-decoration: none;
  font-size: 14px;
  transition: color 0.3s ease;
}

.bottom-link:hover {
  color: #22c55e;
}

.separator {
  color: #475569;
  font-size: 14px;
}

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 1200px) {
  .footer-main {
    grid-template-columns: 2fr 1fr 1fr 1.5fr;
    gap: 40px;
  }
  
  .footer-column:nth-child(5) {
    grid-column: 1 / -1;
    margin-top: 20px;
  }
  
  .newsletter-form {
    max-width: 400px;
  }
}

@media (max-width: 968px) {
  .footer-main {
    grid-template-columns: 1fr 1fr;
    gap: 40px;
  }
  
  .footer-brand {
    grid-column: 1 / -1;
    max-width: 100%;
  }
  
  .footer-column:nth-child(5) {
    grid-column: 1 / -1;
  }
}

@media (max-width: 768px) {
  .modern-footer {
    margin-top: 60px;
  }
  
  .footer-container {
    padding: 40px 20px 0;
  }
  
  .footer-main {
    grid-template-columns: 1fr;
    gap: 32px;
    padding-bottom: 32px;
  }
  
  .footer-brand {
    grid-column: 1;
  }
  
  .footer-column:nth-child(5) {
    grid-column: 1;
    margin-top: 0;
  }
  
  .footer-bottom-content {
    flex-direction: column;
    text-align: center;
  }
  
  .newsletter-form {
    max-width: 100%;
  }
}

@media (max-width: 480px) {
  .logo-text {
    font-size: 24px;
  }
  
  .brand-description {
    font-size: 13px;
  }
  
  .footer-title {
    font-size: 15px;
  }
  
  .footer-link {
    font-size: 13px;
  }
  
  .contact-item {
    font-size: 13px;
  }
}
</style>