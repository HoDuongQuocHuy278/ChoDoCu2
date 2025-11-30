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
    path: "/thong-bao",
    name: "notification",
    component: () => import("../components/ThongBao/index.vue"),
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
  {
    path: "/admin",
    component: () => import("../components/Admin/AdminLayout.vue"),
    meta: { requiresAdmin: true, layout: 'blank' },
    children: [
      {
        path: "dashboard",
        name: "admin.dashboard",
        component: () => import("../components/Admin/Dashboard/index.vue"),
      },
      {
        path: "users",
        name: "admin.users",
        component: () => import("../components/Admin/Users/index.vue"),
      },
      {
        path: "categories",
        name: "admin.categories",
        component: () => import("../components/Admin/Categories/index.vue"),
      },
      {
        path: "products",
        name: "admin.products",
        component: () => import("../components/Admin/Products/index.vue"),
      },
      {
        path: "orders",
        name: "admin.orders",
        component: () => import("../components/Admin/Orders/index.vue"),
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem("token") || localStorage.getItem("key_client");
  const role = localStorage.getItem("user_role");

  if (to.matched.some((record) => record.meta.requiresAdmin)) {
    if (!token || role != 1) {
      next({ name: "auth.login" });
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;
