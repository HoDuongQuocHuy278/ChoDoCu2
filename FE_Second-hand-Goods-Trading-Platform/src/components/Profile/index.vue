<template>
    <div class="account-page">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="profile-mini">
                <div class="avatar-circle">T</div>
                <div>
                    <h3>{{ user.username }}</h3>
                    <p class="edit-link" @click="activeMenu = 'profile'">✏️ Sửa Hồ Sơ</p>
                </div>
            </div>

            <ul class="menu">
                <li class="section-title">Tài Khoản Của Tôi</li>
                <li :class="{ active: activeMenu === 'profile' }" @click="activeMenu = 'profile'">Hồ Sơ</li>
                <li :class="{ active: activeMenu === 'bank' }" @click="activeMenu = 'bank'">Ngân Hàng</li>
                <li :class="{ active: activeMenu === 'address' }" @click="activeMenu = 'address'">Địa Chỉ</li>
                <li :class="{ active: activeMenu === 'password' }" @click="activeMenu = 'password'">Đổi Mật Khẩu</li>

                <li :class="{ active: activeMenu === 'orders' }" @click="activeMenu = 'orders'">Đơn mua</li>

                <li class="section-title">Khác</li>
                <li :class="{ active: activeMenu === 'voucher' }" @click="activeMenu = 'voucher'">Kho Voucher</li>
            </ul>
        </aside>

        <!-- Nội dung -->
        <main class="main-content">
            <transition name="fade" mode="out-in">
                <div :key="activeMenu" class="content-box">
                    <!-- Hồ sơ -->
                    <div v-if="activeMenu === 'profile'">
                        <h2 class="title">Hồ Sơ Của Tôi</h2>
                        <p class="desc">Quản lý thông tin hồ sơ để bảo mật tài khoản của bạn</p>

                        <div class="profile-container">
                            <div class="form-section">
                                <div class="form-group">
                                    <label>Tên đăng nhập</label>
                                    <input type="text" v-model="user.username" disabled />
                                    <small>Tên đăng nhập chỉ có thể thay đổi một lần.</small>
                                </div>

                                <div class="form-group">
                                    <label>Tên</label>
                                    <input type="text" v-model="user.fullName" />
                                </div>

                                <div class="form-group">
                                    <label>Email</label>
                                    <div class="inline-field">
                                        <input type="text" v-model="user.email" />
                                        <a href="#" class="change-link">Thay đổi</a>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Số điện thoại</label>
                                    <div class="inline-field">
                                        <input type="text" v-model="user.phone" />
                                        <a href="#" class="change-link">Thêm</a>
                                    </div>
                                </div>

                                <div class="form-group gender-group">
                                    <label>Giới tính</label>
                                    <div>
                                        <label><input type="radio" value="Nam" v-model="user.gender" /> Nam</label>
                                        <label><input type="radio" value="Nữ" v-model="user.gender" /> Nữ</label>
                                        <label><input type="radio" value="Khác" v-model="user.gender" /> Khác</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Ngày sinh</label>
                                    <div class="dob">
                                        <select v-model="dob.day">
                                            <option disabled>Ngày</option>
                                            <option v-for="d in 31" :key="'day' + d">{{ d }}</option>
                                        </select>

                                        <select v-model="dob.month">
                                            <option disabled>Tháng</option>
                                            <option v-for="m in 12" :key="'month' + m">{{ m }}</option>
                                        </select>

                                        <select v-model="dob.year">
                                            <option disabled>Năm</option>
                                            <option v-for="y in years" :key="'year' + y">{{ y }}</option>
                                        </select>
                                    </div>
                                </div>


                                <button class="save-btn" @click="saveProfile" :disabled="isLoading">💾 Lưu Thay Đổi</button>
                            </div>

                            <div class="avatar-section">
                                <div class="avatar-preview">T</div>
                                <button class="upload-btn">Chọn Ảnh</button>
                                <p class="hint">Dung lượng file tối đa 1 MB<br />Định dạng: .JPEG, .PNG</p>
                            </div>
                        </div>
                    </div>

                    <!-- Ngân hàng -->
                    <div v-else-if="activeMenu === 'bank'">
                        <h2 class="title">Tài Khoản Ngân Hàng</h2>
                        <div class="card-box">
                            <div class="form-group">
                                <label>Tên Ngân Hàng</label>
                                <input type="text" v-model="bank.name" placeholder="Ví dụ: Vietcombank" />
                            </div>
                            <div class="form-group">
                                <label>Số Tài Khoản</label>
                                <input type="text" v-model="bank.number" placeholder="Nhập số tài khoản" />
                            </div>
                            <div class="form-group">
                                <label>Chủ Tài Khoản</label>
                                <input type="text" v-model="bank.owner" placeholder="Tên chủ tài khoản" />
                            </div>
                            <button class="save-btn" @click="saveBank" :disabled="isLoading">💳 Lưu Thông Tin</button>
                        </div>
                    </div>

                    <!-- Địa chỉ -->
                    <div v-else-if="activeMenu === 'address'">
                        <h2 class="title">Địa Chỉ Của Tôi</h2>
                        <div class="card-box">
                            <div class="form-group">
                                <label>Họ Tên Người Nhận</label>
                                <input type="text" v-model="address.name" />
                            </div>
                            <div class="form-group">
                                <label>Số Điện Thoại</label>
                                <input type="text" v-model="address.phone" />
                            </div>
                            <div class="form-group">
                                <label>Địa Chỉ Cụ Thể</label>
                                <textarea v-model="address.full" rows="3"></textarea>
                            </div>
                            <button class="save-btn" @click="saveAddress" :disabled="isLoading">📍 Lưu Địa Chỉ</button>
                        </div>
                    </div>

                    <!-- Đổi mật khẩu -->
                    <div v-else-if="activeMenu === 'password'">
                        <h2 class="title">Đổi Mật Khẩu</h2>
                        <div class="card-box">
                            <div class="form-group">
                                <label>Mật Khẩu Hiện Tại</label>
                                <input type="password" v-model="password.current" />
                            </div>
                            <div class="form-group">
                                <label>Mật Khẩu Mới</label>
                                <input type="password" v-model="password.new" />
                            </div>
                            <div class="form-group">
                                <label>Nhập Lại Mật Khẩu Mới</label>
                                <input type="password" v-model="password.confirm" />
                            </div>
                            <button class="save-btn" @click="changePassword" :disabled="isLoading">🔐 Cập Nhật Mật Khẩu</button>
                        </div>
                    </div>

                    <!-- Đơn hàng -->
                    <div v-else-if="activeMenu === 'orders'">
                        <h2 class="title">Đơn Mua Của Tôi</h2>
                        <p class="desc">Xem lịch sử mua hàng và trạng thái đơn hàng của bạn</p>
                        
                        <!-- Loading -->
                        <div v-if="isLoadingOrders" class="loading-orders">
                            <div class="loading-spinner"></div>
                            <p>Đang tải đơn hàng...</p>
                        </div>
                        
                        <!-- Empty state -->
                        <div v-else-if="!orders.length" class="empty-orders">
                            <div class="empty-icon">📭</div>
                            <p>Bạn chưa có đơn hàng nào.</p>
                            <router-link to="/danh-sach-san-pham" class="btn-shopping">🛍️ Mua sắm ngay</router-link>
                        </div>
                        
                        <!-- Danh sách đơn hàng -->
                        <div v-else class="order-list">
                            <div 
                                class="order-card" 
                                v-for="order in orders" 
                                :key="order.id"
                                @click="viewOrderDetail(order)"
                            >
                                <div class="order-header-info">
                                    <div class="order-code-section">
                                        <strong class="order-code">Mã đơn: {{ order.order_code }}</strong>
                                        <span :class="['status-badge', getStatusClass(order.status)]">
                                            {{ getStatusLabel(order.status) }}
                                        </span>
                                    </div>
                                    <div class="order-date-section">
                                        <span class="order-date-label">📅 Đặt hàng:</span>
                                        <strong class="order-date">{{ order.order_date_formatted || formatDateTime(order.order_date) }}</strong>
                                    </div>
                                </div>
                                
                                <div class="order-body-info">
                                    <img 
                                        :src="order.product_image || fallbackImg" 
                                        :alt="order.product_name"
                                        class="order-img"
                                        @error="onImgError($event)"
                                        @click.stop
                                    />
                                    <div class="order-details">
                                        <h3 class="product-name">{{ order.product_name }}</h3>
                                        <div class="order-meta">
                                            <div class="meta-item">
                                                <span class="meta-label">📦 Số lượng:</span>
                                                <strong>{{ order.quantity }}</strong>
                                            </div>
                                            <div class="meta-item">
                                                <span class="meta-label">💰 Đơn giá:</span>
                                                <strong>{{ formatPrice(order.product_price) }}</strong>
                                            </div>
                                            <div class="meta-item">
                                                <span class="meta-label">💵 Tổng tiền:</span>
                                                <strong class="total-price">{{ formatPrice(order.total_amount) }}</strong>
                                            </div>
                                        </div>
                                        <div class="order-info-row">
                                            <div class="info-item">
                                                <span class="info-icon">📍</span>
                                                <span class="info-label">Mua tại:</span>
                                                <span class="info-value">{{ order.seller_location || 'Chưa cập nhật' }}</span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-icon">👤</span>
                                                <span class="info-label">Người bán:</span>
                                                <span class="info-value">{{ order.seller_name || 'Người bán' }}</span>
                                            </div>
                                        </div>
                                        <div v-if="order.shipping_address" class="order-info-row">
                                            <div class="info-item">
                                                <span class="info-icon">🏠</span>
                                                <span class="info-label">Địa chỉ nhận hàng:</span>
                                                <span class="info-value">{{ order.shipping_address }}</span>
                                            </div>
                                        </div>
                                        <div class="order-info-row">
                                            <div class="info-item">
                                                <span class="info-icon">🚚</span>
                                                <span class="info-label">Dự kiến giao hàng:</span>
                                                <strong class="delivery-date">{{ order.estimated_delivery_date_formatted || calculateDeliveryDate(order.order_date) }}</strong>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-icon">💳</span>
                                                <span class="info-label">Thanh toán:</span>
                                                <span :class="['payment-badge', getPaymentClass(order.payment_status)]">
                                                    {{ getPaymentLabel(order.payment_status) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="order-actions" @click.stop>
                                    <button 
                                        v-if="order.status === 'delivered' || order.status === 'shipped'"
                                        class="btn-confirm"
                                        @click="confirmReceived(order)"
                                    >
                                        ✓ Xác nhận đã nhận hàng
                                    </button>
                                    <button 
                                        class="btn-view-detail"
                                        @click="viewOrderDetail(order)"
                                    >
                                        👁️ Xem chi tiết
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Voucher -->
                    <div v-else-if="activeMenu === 'voucher'">
                        <h2 class="title">Kho Voucher</h2>
                        <div class="voucher-list">
                            <div v-for="(v, i) in vouchers" :key="i" class="voucher-card">
                                <h3>{{ v.title }}</h3>
                                <p>{{ v.desc }}</p>
                                <button class="save-btn small">Dùng Ngay</button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </main>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: "UserProfileShopeeStyle",
    data() {
        return {
            API_BASE_URL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/client',
            activeMenu: "profile",
            isLoading: false,
            user: { username: "toicua_toi", fullName: "Tôi", email: "toi***@gmail.com", phone: "", gender: "Nam" },
            dob: { day: "Ngày", month: "Tháng", year: "Năm" },
            bank: { name: "", number: "", owner: "" },
            address: { name: "", phone: "", full: "" },
            password: { current: "", new: "", confirm: "" },
            orders: [],
            vouchers: [],
            isLoadingOrders: false,
            selectedOrder: null,
            fallbackImg: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDYwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI2MDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0zMDAgMTYwQzM0NS4yMjkgMTYwIDM4MiAxMjMuMjI5IDM4MiA3OEMzODIgMzIuNzcwOSAzNDUuMjI5IDYgMzAwIDZDMjU0Ljc3MCA2IDIxOCAzMi43NzA5IDIxOCA3OEMyMTggMTIzLjIyOSAyNTQuNzcwIDE2MCAzMDAgMTYwWiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNMzAwIDI0MEMyNTAgMjQwIDIxMCAyNTYgMTg1IDI4MEg0MTVDMzkwIDI1NiAzNTAgMjQwIDMwMCAyNDBaIiBmaWxsPSIjOUNBM0FGIi8+Cjx0ZXh0IHg9IjMwMCIgeT0iMzIwIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9IiM5Q0EzQUYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPk5vIEltYWdlPC90ZXh0Pgo8L3N2Zz4=",
        };
    },
    computed: {
        years() {
            const now = new Date().getFullYear();
            return Array.from({ length: 50 }, (_, i) => now - i);
        },
    },
    mounted() {
        this.fetchProfile()
        this.fetchOrders()
        this.fetchVouchers()
    },
    methods: {
        async fetchProfile() {
            this.isLoading = true
            try {
                const token = localStorage.getItem('key_client')
                const { data } = await axios.get(`${this.API_BASE_URL}/profile`, {
                    headers: token ? { 'Authorization': `Bearer ${token}` } : {}
                })
                const payload = data?.data || data
                if (payload) {
                    this.user = {
                        username: payload.username || payload.ten_dang_nhap || this.user.username,
                        fullName: payload.ho_va_ten || payload.full_name || this.user.fullName,
                        email: payload.email || this.user.email,
                        phone: payload.so_dien_thoai || payload.phone || this.user.phone,
                        gender: payload.gioi_tinh || payload.gender || this.user.gender
                    }
                    if (payload.ngay_sinh) {
                        const date = new Date(payload.ngay_sinh)
                        this.dob.day = date.getDate()
                        this.dob.month = date.getMonth() + 1
                        this.dob.year = date.getFullYear()
                    }
                    // Load bank info
                    if (payload.ten_ngan_hang || payload.so_tai_khoan || payload.chu_tai_khoan) {
                        this.bank = {
                            name: payload.ten_ngan_hang || '',
                            number: payload.so_tai_khoan || '',
                            owner: payload.chu_tai_khoan || ''
                        }
                    }
                    // Load address info
                    if (payload.dia_chi_ho_ten || payload.dia_chi_so_dien_thoai || payload.dia_chi_chi_tiet) {
                        this.address = {
                            name: payload.dia_chi_ho_ten || '',
                            phone: payload.dia_chi_so_dien_thoai || '',
                            full: payload.dia_chi_chi_tiet || ''
                        }
                    }
                }
            } catch (err) {
                console.warn('Không thể tải thông tin profile', err)
            } finally {
                this.isLoading = false
            }
        },
        async fetchOrders() {
            this.isLoadingOrders = true
            try {
                const token = localStorage.getItem('key_client')
                if (!token) {
                    console.warn('Chưa đăng nhập, không thể tải đơn hàng')
                    this.orders = []
                    return
                }
                
                const { data } = await axios.get(`${this.API_BASE_URL}/don-hang-mua`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                })
                
                console.log('API Response:', data)
                
                if (data?.status) {
                    this.orders = data.data || []
                    console.log('Đã tải được', this.orders.length, 'đơn hàng')
                } else {
                    this.orders = data?.data || data || []
                    console.warn('API không trả về status: true', data)
                }
            } catch (err) {
                console.error('Lỗi khi tải đơn hàng:', err)
                console.error('Response:', err?.response?.data)
                if (err?.response?.status === 401) {
                    if (window?.$toast) {
                        window.$toast.error('Vui lòng đăng nhập để xem đơn hàng')
                    } else {
                        alert('Vui lòng đăng nhập để xem đơn hàng')
                    }
                }
                this.orders = []
            } finally {
                this.isLoadingOrders = false
            }
        },
        async fetchVouchers() {
            try {
                const { data } = await axios.get(`${this.API_BASE_URL}/voucher`)
                this.vouchers = data?.data || data || []
            } catch (err) {
                console.warn('Không thể tải voucher', err)
                this.vouchers = []
            }
        },
        async saveProfile() {
            this.isLoading = true
            try {
                const token = localStorage.getItem('key_client')
                const payload = {
                    ho_va_ten: this.user.fullName,
                    email: this.user.email,
                    so_dien_thoai: this.user.phone,
                    gioi_tinh: this.user.gender,
                    ngay_sinh: `${this.dob.year}-${String(this.dob.month).padStart(2, '0')}-${String(this.dob.day).padStart(2, '0')}`
                }
                await axios.put(`${this.API_BASE_URL}/profile`, payload, {
                    headers: token ? { 'Authorization': `Bearer ${token}` } : {}
                })
                if (window?.$toast) {
                    window.$toast.success('Cập nhật hồ sơ thành công!')
                } else {
                    alert('Cập nhật hồ sơ thành công!')
                }
            } catch (err) {
                const msg = err?.response?.data?.message || 'Không thể cập nhật hồ sơ.'
                if (window?.$toast) {
                    window.$toast.error(msg)
                } else {
                    alert(msg)
                }
            } finally {
                this.isLoading = false
            }
        },
        async saveBank() {
            this.isLoading = true
            try {
                const token = localStorage.getItem('key_client')
                await axios.put(`${this.API_BASE_URL}/profile/bank`, this.bank, {
                    headers: token ? { 'Authorization': `Bearer ${token}` } : {}
                })
                if (window?.$toast) {
                    window.$toast.success('Cập nhật thông tin ngân hàng thành công!')
                } else {
                    alert('Cập nhật thông tin ngân hàng thành công!')
                }
            } catch (err) {
                const msg = err?.response?.data?.message || 'Không thể cập nhật thông tin ngân hàng.'
                if (window?.$toast) {
                    window.$toast.error(msg)
                } else {
                    alert(msg)
                }
            } finally {
                this.isLoading = false
            }
        },
        async saveAddress() {
            this.isLoading = true
            try {
                const token = localStorage.getItem('key_client')
                await axios.put(`${this.API_BASE_URL}/profile/address`, this.address, {
                    headers: token ? { 'Authorization': `Bearer ${token}` } : {}
                })
                if (window?.$toast) {
                    window.$toast.success('Cập nhật địa chỉ thành công!')
                } else {
                    alert('Cập nhật địa chỉ thành công!')
                }
            } catch (err) {
                const msg = err?.response?.data?.message || 'Không thể cập nhật địa chỉ.'
                if (window?.$toast) {
                    window.$toast.error(msg)
                } else {
                    alert(msg)
                }
            } finally {
                this.isLoading = false
            }
        },
        async changePassword() {
            if (this.password.new !== this.password.confirm) {
                alert('Mật khẩu nhập lại không khớp!')
                return
            }
            this.isLoading = true
            try {
                const token = localStorage.getItem('key_client')
                await axios.put(`${this.API_BASE_URL}/profile/password`, {
                    current_password: this.password.current,
                    new_password: this.password.new,
                    confirm_password: this.password.confirm
                }, {
                    headers: token ? { 'Authorization': `Bearer ${token}` } : {}
                })
                if (window?.$toast) {
                    window.$toast.success('Đổi mật khẩu thành công!')
                } else {
                    alert('Đổi mật khẩu thành công!')
                }
                this.password = { current: "", new: "", confirm: "" }
            } catch (err) {
                const msg = err?.response?.data?.message || 'Không thể đổi mật khẩu.'
                if (window?.$toast) {
                    window.$toast.error(msg)
                } else {
                    alert(msg)
                }
            } finally {
                this.isLoading = false
            }
        },
        
        async confirmReceived(order) {
            if (!confirm(`Xác nhận bạn đã nhận được hàng cho đơn hàng ${order.order_code}?`)) return;
            
            try {
                const token = localStorage.getItem('key_client');
                const res = await axios.post(
                    `${this.API_BASE_URL}/don-hang/${order.id}/xac-nhan-nhan-hang`,
                    {},
                    { headers: { 'Authorization': `Bearer ${token}` } }
                );
                
                if (res?.data?.status) {
                    order.status = 'completed';
                    if (window?.$toast) {
                        window.$toast.success('Đã xác nhận nhận hàng thành công!');
                    } else {
                        alert('Đã xác nhận nhận hàng thành công!');
                    }
                    await this.fetchOrders();
                }
            } catch (e) {
                const msg = e?.response?.data?.message || 'Không thể xác nhận nhận hàng';
                if (window?.$toast) {
                    window.$toast.error(msg);
                } else {
                    alert(msg);
                }
            }
        },
        
        viewOrderDetail(order) {
            this.selectedOrder = order;
            // Có thể mở modal hoặc chuyển đến trang chi tiết
            this.$router.push({
                name: 'buyer.orders',
                query: { order_id: order.id }
            });
        },
        
        getStatusClass(status) {
            const map = {
                'pending': 'status-pending',
                'processing': 'status-processing',
                'shipped': 'status-shipped',
                'delivered': 'status-delivered',
                'completed': 'status-completed',
                'cancelled': 'status-cancelled',
            };
            return map[status] || 'status-default';
        },
        
        getStatusLabel(status) {
            const map = {
                'pending': 'Chờ xử lý',
                'processing': 'Đang xử lý',
                'shipped': 'Đã giao hàng',
                'delivered': 'Đã đến nơi',
                'completed': 'Hoàn thành',
                'cancelled': 'Đã hủy',
            };
            return map[status] || status;
        },
        
        getPaymentClass(status) {
            const map = {
                'pending': 'payment-pending',
                'awaiting_payment': 'payment-pending',
                'paid': 'payment-paid',
                'completed': 'payment-paid',
                'failed': 'payment-failed',
            };
            return map[status] || 'payment-default';
        },
        
        getPaymentLabel(status) {
            const map = {
                'pending': 'Chờ thanh toán',
                'awaiting_payment': 'Chờ thanh toán',
                'paid': 'Đã thanh toán',
                'completed': 'Hoàn thành',
                'failed': 'Thất bại',
            };
            return map[status] || status;
        },
        
        formatPrice(amount) {
            return (Number(amount) || 0).toLocaleString('vi-VN') + ' ₫';
        },
        
        formatDateTime(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleString('vi-VN', { 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        
        calculateDeliveryDate(orderDate) {
            if (!orderDate) return 'Chưa xác định';
            const date = new Date(orderDate);
            date.setDate(date.getDate() + 3); // +3 ngày
            return date.toLocaleDateString('vi-VN');
        },
        
        onImgError(e) {
            e.target.src = this.fallbackImg;
        },
    }
};
</script>

<style scoped>
/* 🌈 Tổng thể */
.account-page {
  display: flex;
  background: #f6f7fb;
  min-height: 100vh;
  font-family: "Inter", "Segoe UI", sans-serif;
  color: #333;
  overflow-x: hidden;
}

/* 🧭 Sidebar */
.sidebar {
  width: 270px;
  background: #fff;
  border-right: 1px solid #eee;
  padding: 30px 22px;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.03);
  position: sticky;
  top: 0;
  height: 100vh;
}

.profile-mini {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 40px;
}

.avatar-circle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #ff784e, #ff5722);
  color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: 700;
  font-size: 22px;
  box-shadow: 0 3px 6px rgba(255, 87, 34, 0.3);
}

.edit-link {
  color: #ff5722;
  font-size: 13px;
  cursor: pointer;
  margin-top: 2px;
  transition: 0.2s;
}

.edit-link:hover {
  color: #e64a19;
  text-decoration: underline;
}

.menu {
  list-style: none;
  padding: 0;
}

.section-title {
  font-size: 12px;
  color: #999;
  margin-top: 18px;
  text-transform: uppercase;
  font-weight: 600;
}

.menu li {
  padding: 10px 12px;
  border-radius: 8px;
  margin-top: 4px;
  color: #444;
  transition: 0.25s;
  cursor: pointer;
}

.menu li:hover {
  background: #fff3e0;
  color: #ff5722;
}

.menu li.active {
  background: #fff1e0;
  color: #ff5722;
  font-weight: 600;
  box-shadow: inset 0 0 0 1px #ffb48a;
}

/* 📦 Main content */
.main-content {
  flex: 1;
  padding: 50px 70px;
}

.content-box {
  background: #fff;
  border-radius: 12px;
  padding: 35px 45px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  animation: fadeIn 0.4s ease;
}

/* ✏️ Tiêu đề */
.title {
  font-size: 24px;
  font-weight: 700;
  color: #222;
  margin-bottom: 8px;
}

.desc {
  font-size: 15px;
  color: #777;
  margin-bottom: 30px;
}

/* 📋 Form */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  font-weight: 600;
  display: block;
  margin-bottom: 6px;
  color: #444;
}

input,
select,
textarea {
  width: 100%;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 10px 12px;
  font-size: 14px;
  transition: all 0.25s ease;
  background-color: #fff;
}

input:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: #ff784e;
  box-shadow: 0 0 6px rgba(255, 112, 67, 0.25);
}

textarea {
  resize: none;
  min-height: 80px;
}

.inline-field {
  display: flex;
  align-items: center;
  gap: 10px;
}

.change-link {
  color: #ff5722;
  font-size: 14px;
  cursor: pointer;
  white-space: nowrap;
  transition: 0.2s;
}

.change-link:hover {
  text-decoration: underline;
  color: #e64a19;
}

/* 🧍 Hồ sơ */
.profile-container {
  display: flex;
  justify-content: space-between;
  gap: 40px;
}

.avatar-section {
  text-align: center;
  border-left: 1px solid #eee;
  padding-left: 40px;
}

.avatar-preview {
  background: linear-gradient(135deg, #ff784e, #ff5722);
  color: #fff;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  font-size: 42px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 15px;
  box-shadow: 0 4px 8px rgba(255, 87, 34, 0.3);
}

.upload-btn {
  background: #fff;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 8px 18px;
  cursor: pointer;
  transition: 0.25s;
}

.upload-btn:hover {
  border-color: #ff784e;
  color: #ff5722;
}

.hint {
  font-size: 13px;
  color: #777;
  margin-top: 8px;
}

/* 💾 Nút lưu */
.save-btn {
  background: linear-gradient(135deg, #ff784e, #ff5722);
  color: #fff;
  border: none;
  padding: 10px 28px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.25s ease;
  box-shadow: 0 3px 8px rgba(255, 87, 34, 0.25);
}

.save-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 5px 12px rgba(255, 87, 34, 0.35);
}

.small {
  padding: 6px 14px;
  font-size: 13px;
}

/* 📅 Ngày sinh */
.dob {
  display: flex;
  gap: 10px;
}

.dob select {
  flex: 1;
}

/* 🛒 Đơn hàng */
.loading-orders {
  text-align: center;
  padding: 60px 20px;
}

.loading-spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #ff5722;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-orders {
  text-align: center;
  padding: 60px 20px;
  background: #fffaf7;
  border-radius: 12px;
  border: 2px dashed #ffe0cc;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.btn-shopping {
  display: inline-block;
  margin-top: 20px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #ff784e, #ff5722);
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  transition: 0.3s;
}

.btn-shopping:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
}

.order-list {
  margin-top: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.order-card {
  background: #fffaf7;
  border: 1px solid #ffe0cc;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(255, 87, 34, 0.1);
  transition: 0.3s;
  cursor: pointer;
}

.order-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 87, 34, 0.2);
}

.order-header-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 1px solid #ffe0cc;
  flex-wrap: wrap;
  gap: 10px;
}

.order-code-section {
  display: flex;
  align-items: center;
  gap: 10px;
}

.order-code {
  font-family: monospace;
  font-size: 16px;
  color: #333;
}

.order-date-section {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #666;
  font-size: 14px;
}

.order-date-label {
  font-weight: normal;
}

.order-date {
  color: #ff5722;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
  white-space: nowrap;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-processing {
  background: #cce5ff;
  color: #004085;
}

.status-shipped {
  background: #d1ecf1;
  color: #0c5460;
}

.status-delivered {
  background: #d4edda;
  color: #155724;
}

.status-completed {
  background: #d4edda;
  color: #155724;
}

.status-cancelled {
  background: #f8d7da;
  color: #721c24;
}

.status-default {
  background: #e2e3e5;
  color: #383d41;
}

.order-body-info {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
}

.order-img {
  width: 120px;
  height: 120px;
  border-radius: 8px;
  object-fit: cover;
  flex-shrink: 0;
}

.order-details {
  flex: 1;
}

.product-name {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin-bottom: 12px;
}

.order-meta {
  display: flex;
  gap: 20px;
  margin-bottom: 12px;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: #666;
}

.meta-label {
  font-weight: normal;
}

.total-price {
  color: #ff5722;
  font-size: 16px;
}

.order-info-row {
  display: flex;
  gap: 20px;
  margin-bottom: 8px;
  flex-wrap: wrap;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: #555;
  flex: 1;
  min-width: 200px;
}

.info-icon {
  font-size: 16px;
}

.info-label {
  font-weight: 500;
  color: #666;
}

.info-value {
  color: #333;
}

.delivery-date {
  color: #ff5722;
  font-weight: 600;
}

.payment-badge {
  padding: 3px 10px;
  border-radius: 10px;
  font-size: 12px;
  font-weight: bold;
}

.payment-pending {
  background: #fff3cd;
  color: #856404;
}

.payment-paid {
  background: #d4edda;
  color: #155724;
}

.payment-failed {
  background: #f8d7da;
  color: #721c24;
}

.payment-default {
  background: #e2e3e5;
  color: #383d41;
}

.order-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  padding-top: 15px;
  border-top: 1px solid #ffe0cc;
}

.btn-confirm {
  background: #28a745;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: 0.3s;
}

.btn-confirm:hover {
  background: #218838;
}

.btn-view-detail {
  background: #007bff;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: 0.3s;
}

.btn-view-detail:hover {
  background: #0056b3;
}

/* 🎟 Voucher */
.voucher-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 20px;
}

.voucher-card {
  background: #fff9f5;
  border: 1px dashed #ff7043;
  border-radius: 10px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 2px 6px rgba(255, 87, 34, 0.05);
  transition: 0.25s;
}

.voucher-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(255, 87, 34, 0.15);
}

.voucher-card h3 {
  color: #ff5722;
  font-weight: 600;
  margin-bottom: 8px;
}

/* ✨ Hiệu ứng chuyển trang */
.fade-enter-active,
.fade-leave-active {
  transition: all 0.4s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translateY(15px);
}

.fade-leave-to {
  opacity: 0;
  transform: translateY(-15px);
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

</style>