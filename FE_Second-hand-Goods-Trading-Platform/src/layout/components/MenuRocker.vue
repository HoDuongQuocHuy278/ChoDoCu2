<template>
	<div class="menu-modern-container">
		<nav class="menu-modern-nav">
			<div class="menu-modern-wrapper">
				<ul class="menu-modern-list">
					<li class="menu-modern-item">
						<router-link to="/trang-chu" class="menu-modern-link" active-class="active">
							<i class='bx bx-home'></i>
							<span>Trang chủ</span>
						</router-link>
					</li>
					<li class="menu-modern-item">
						<router-link to="/danh-sach-san-pham" class="menu-modern-link" active-class="active">
							<i class='bx bx-grid-alt'></i>
							<span>Sản phẩm</span>
						</router-link>
					</li>

					<li class="menu-modern-item">
						<router-link to="/sell" class="menu-modern-link" active-class="active">
							<i class='bx bx-plus-circle'></i>
							<span>Đăng bán</span>
						</router-link>
					</li>
				</ul>
			</div>
		</nav>
	</div>
</template>
<script>
export default {
	name: 'MenuRocker'
}
</script>
<style scoped>
.menu-modern-container {
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
  border-top: none; /* Remove border-top to connect with TopRocker */
  margin: 0;
  padding: 0;
}

.menu-modern-nav {
  width: 100%;
  background: #ffffff;
}

.menu-modern-wrapper {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
}

.menu-modern-list {
  display: flex;
  align-items: center;
  gap: 8px;
  list-style: none;
  margin: 0;
  padding: 0;
  overflow-x: auto;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.menu-modern-list::-webkit-scrollbar {
  display: none;
}

.menu-modern-item {
  flex-shrink: 0;
}

.menu-modern-link {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  color: #6b7280;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  border-radius: 8px;
  transition: all 0.2s;
  white-space: nowrap;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
}

.menu-modern-link i {
  font-size: 18px;
}

.menu-modern-link:hover {
  color: #16a34a;
  background: #f0fdf4;
}

.menu-modern-link.active {
  color: #16a34a;
  background: #f0fdf4;
  border-bottom-color: #16a34a;
  font-weight: 600;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .menu-modern-wrapper {
    padding: 0 16px;
  }
  
  .menu-modern-link {
    padding: 10px 16px;
    font-size: 13px;
  }
  
  .menu-modern-link span {
    display: none;
  }
  
  .menu-modern-link i {
    font-size: 20px;
  }
}

@media (max-width: 480px) {
  .menu-modern-list {
    gap: 4px;
  }
  
  .menu-modern-link {
    padding: 8px 12px;
  }
}
</style>
