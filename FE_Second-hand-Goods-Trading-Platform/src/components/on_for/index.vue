<template>
    <div class="container">
        <div class="row">
            <template v-for="(value, index) in products" :key="index">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <img v-bind:src="value.image" alt="">
                            <h3 class="text text-center text-truncate">{{ value.name }}</h3>
                            <p class="text text-center text-nowrap text-truncate">{{ value.description }}</p>
                            <div class="d-flex justify-content-center">
                                <h5 class="text text-danger "> {{ value.price }}</h5>
                                <button class="btn btn-primary text-nowrap">thêm vào giỏ hàng</button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <!-- bài 2 -->
            <div class="card">
                <table class="table table-hover table-boredered text-center mt-3 align-middle">
                    <thead>
                        <tr class="text-bg-dark">
                            <th class="text-light">#</th>
                            <th class="text-light">Họ tên</th>
                            <th class="text-light">MSSV</th>
                            <th class="text-light">Lớp</th>
                            <th class="text-light">Điểm TB</th>
                            <th class="text-light">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="(value, index) in students" :key="index">
                            <tr>
                                <td>{{ value.id }}</td>
                                <td>{{ value.name }}</td>
                                <td>{{ value.studentId }}</td>
                                <td>{{ value.class }}</td>
                                <td>{{ value.avgScore }}</td>
                                <td>
                                    <div v-if="value.trang_thai == 1"><button class="btn btn-success">Đạt</button></div>
                                    <div v-if="value.trang_thai == 0"><button class="btn btn-danger"> khong Đạt</button>
                                    </div>

                                </td>

                            </tr>

                        </template>

                    </tbody>
                </table>
            </div>
            <!-- bài 3 -->
             

            <!-- bài 4 -->
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    chọn danh mục
                </button>
                <ul class="dropdown-menu">
                    <template v-for="(value, index) in categories" :key="index">
                        <li class="dropdown-item d-flex align-items-center">
                            <i v-bind:class="value.icon" class="me-2"></i>
                            {{ value.name }}
                        </li>
                    </template>
                </ul>
            </div>
            <!-- Bài 5 -->
             <div>
                <div id="carouselExampleIndicators" class="carousel slide">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="3" aria-label="Slide 4"></button>

  </div>
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="..." class="d-block w-100" alt="...">
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>
             </div>

        </div>
    </div>


</template>
<script>
export default {
    data() {
        return {
            "products": [
                {
                    "id": 1,
                    "name": "Laptop Dell XPS 15",
                    "description": "Laptop cao cấp với màn hình OLED 15 inch, chip Intel Core i7 Gen 13",
                    "price": "45.990.000₫",
                    "image": "https://via.placeholder.com/300x200?text=San+pham+1"
                },
                {
                    "id": 2,
                    "name": "iPhone 15 Pro",
                    "description": "Điện thoại Apple mới nhất với camera chuyên nghiệp, chip A17 Pro",
                    "price": "28.990.000₫",
                    "image": "https://via.placeholder.com/300x200?text=San+pham+2"
                },
                {
                    "id": 3,
                    "name": "Tai nghe Sony WH-1000XM5",
                    "description": "Tai nghe chống ồn cao cấp với âm thanh Hi-Res",
                    "price": "8.490.000₫",
                    "image": "https://via.placeholder.com/300x200?text=San+pham+3"
                }
            ],
            "students": [
                { "id": 1, "name": "Nguyễn Văn A", "studentId": "SV001", "class": "CNTT1", "avgScore": 3.4, "trang_thai": 1 },
                { "id": 2, "name": "Trần Thị B", "studentId": "SV002", "class": "CNTT2", "avgScore": 8.1, "trang_thai": 0 },
                { "id": 3, "name": "Lê Văn C", "studentId": "SV003", "class": "CNTT1", "avgScore": 6.9, "trang_thai": 0 },
                { "id": 4, "name": "Phạm Thị D", "studentId": "SV004", "class": "CNTT3", "avgScore": 2.5, "trang_thai": 1 },
                { "id": 5, "name": "Hoàng Văn E", "studentId": "SV005", "class": "CNTT2", "avgScore": 9.4, "trang_thai": 1 },
            ],
            "categories": [
                { "id": 1, "name": "Công nghệ", "icon": "bi bi-laptop", "count": 42 },
                { "id": 2, "name": "Thể thao", "icon": "bi bi-dribbble", "count": 18 },
                { "id": 3, "name": "Âm nhạc", "icon": "bi bi-music-note-beamed", "count": 24 },
                { "id": 4, "name": "Thời trang", "icon": "bi bi-bag", "count": 36 },
                { "id": 5, "name": "Du lịch", "icon": "bi bi-geo-alt", "count": 15 }
            ],
            "reviews": [
                {
                    "id": 1,
                    "name": "Nguyễn Văn A",
                    "position": "CEO Công ty ABC",
                    "rating": 5,
                    "comment": "Sản phẩm tuyệt vời, đã giúp công ty chúng tôi tăng 200% hiệu quả làm vi",
                    "avatarUrl": "https://via.placeholder.com/60x60?text=NVA"
                },
                {
                    "id": 2,
                    "name": "Trần Thị B",
                    "position": "Marketing Director",
                    "rating": 4,
                    "comment": "Dịch vụ khách hàng chuyên nghiệp, sản phẩm đáp ứng đúng nhu cầu.",
                    "avatarUrl": "https://via.placeholder.com/60x60?text=TTB"
                },
                {
                    "id": 3,
                    "name": "Lê Văn C",
                    "position": "Khách hàng cá nhân",
                    "rating": 5,
                    "comment": "Tôi rất hài lòng với sản phẩm này. Chắc chắn sẽ giới thiệu cho bạn bè!",
                    "avatarUrl": "https://via.placeholder.com/60x60?text=LVC"
                }
            ]



        }
    },

}
</script>
<style></style>