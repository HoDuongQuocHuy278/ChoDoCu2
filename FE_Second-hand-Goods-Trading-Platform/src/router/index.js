import { createRouter, createWebHistory } from "vue-router"; // cài vue-router: npm install vue-router@next --save

const routes = [
  {
    path: "/",
    redirect: "/trang-chu",
  },
  {
    path: "/trang-chu",
    name: "home",
    component: () => import("../components/TrangChu/index.vue"),
  },
  {
    path: "/danh-sach-san-pham",
    name: "products.list",
    component: () => import("../components/ListSanPham/index.vue"),
  },
  {
    path: "/search",
    name: "search",
    component: () => import("../components/ListSanPham/index.vue"),
  },
  {
    path: "/san-pham/:id",
    name: "products.show",
    component: () => import("../components/SanPham/index.vue"),
    props: true,
  },
  {
    path: "/checkout",
    name: "checkout",
    component: () => import("../components/Checkout/index.vue"),
  },
  {
    path: "/gio-hang",
    name: "cart",
    component: () => import("../components/GioHang/index.vue"),
  },
  {
    path: "/dang-ky",
    name: "auth.register",
    component: () => import("../components/DangKy/index.vue"),
  },
  {
    path: "/dang-nhap",
    name: "auth.login",
    component: () => import("../components/DangNhap/index.vue"),
  },
  {
    path: "/dang-ky-ban",
    name: "seller.register",
    component: () => import("../components/NguoiDangBan/DangKyBan/index.vue"),
  },
  {
    path: "/sell",
    name: "seller.create",
    component: () => import("../components/NguoiDangBan/Sell/index.vue"),
  },
  {
    path: "/nguoi-ban/san-pham",
    name: "seller.products",
    component: () => import("../components/NguoiDangBan/SanPhamCuaToi/index.vue"),
  },
  {
    path: "/nguoi-ban/lich-su-ban-hang",
    name: "seller.sales-history",
    component: () => import("../components/NguoiDangBan/LichSuBanHang/index.vue"),
  },
  {
    path: "/nguoi-ban/quan-ly-don-hang",
    name: "seller.orders",
    component: () => import("../components/NguoiDangBan/QuanLyDonHang/index.vue"),
  },
  {
    path: "/don-mua",
    name: "buyer.orders",
    component: () => import("../components/DonMua/index.vue"),
  },
  {
    path: "/listchat",
    name: "chat.list",
    component: () => import("../components/listchat/index.vue"),
  },
  {
    path: "/chat/:id",
    name: "chat.realtime",
    component: () => import("../components/chatrealtime/index.vue"),
    props: true,
  },

  {
    path: "/client/kich-hoat/:hash_active",
    name: "client.kich-hoat",
    component: () => import("../components/KichHoat/index.vue"),
  },
  {
    path: "/profile",
    name: "client.profile",
    component: () => import("../components/Profile/index.vue"),
  },
  {
    path: "/payment/callback",
    name: "payment.callback",
    component: () => import("../components/PaymentCallback/index.vue"),
  },
  {
    path: "/admin/quan-ly-nguoi-dung",
    name: "admin.qlnguoidung",
    component: () => import("../components/admin/QuanLyNguoiDung/index.vue"),
  },
  {
    path: "/admin/quan-ly-san-pham",
    name: "admin.qlsanpham",
    component: () => import("../components/admin/QuanLySanPham/index.vue"),
  },
  {
    path: "/admin/quan-ly-don-hang",
    name: "admin.qldonhang",
    component: () => import("../components/admin/QuanLyDonHang/index.vue"),
  },
  {
    path: "/:pathMatch(.*)*",
    name: "not-found",
    component: {
      template: `<div class="container py-5 text-center"><h1>404</h1><p>Không tìm thấy trang.</p></div>`,
    },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // Instant scroll, no delay
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0, behavior: 'instant' }
    }
  }
});

export default router;
