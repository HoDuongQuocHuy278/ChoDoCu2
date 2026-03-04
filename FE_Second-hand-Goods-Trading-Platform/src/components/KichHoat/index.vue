<template>

</template>
<script>
import axios from 'axios';
export default {
    props: ["hash_active"],
    data() {
        return {
            hash_active: this.$route.params.hash_active,
        }
    },
    mounted() {
        this.kichHoat();
    },
    methods: {
        kichHoat() {
            var payload = {
                hash_active: this.hash_active
            };
            // đổi api
            axios.post('http://192.168.1.61:8000/api/client/kich-hoat', payload)
                .then((res) => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.$router.push('/dang-nhap');
                    } else {
                        this.$toast.error(res.data.message);
                    }
                });
        }
    },
}
</script>
<style></style>