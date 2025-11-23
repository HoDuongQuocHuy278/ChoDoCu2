<template>
    <div class="wrapper">
		<div class="header-wrapper-sticky">
            <TopRocker></TopRocker>
            <MenuRocker></MenuRocker>
		</div>
		<div class="page-wrapper">
			<div class="page-content">
                <router-view> </router-view>
            </div>
		</div>
        <BotRocker></BotRocker>
	</div>
</template>
<script>

import TopRocker from "../components/TopRocker.vue";
import BotRocker from "../components/BotRocker.vue";
import MenuRocker from "../components/MenuRocker.vue";
import "../../assets/js/bootstrap.bundle.min.js";
import "../../assets/js/jquery.min.js";
import "../../assets/plugins/simplebar/js/simplebar.min.js";
import "../../assets/plugins/metismenu/js/metisMenu.min.js";
import "../../assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js";
import "../../assets/js/index.js";
import "../../assets/js/app.js";
import "../../assets/js/pace.min.js";

export default {
    name        :   "app",
    components  :   {
        TopRocker, MenuRocker, BotRocker
    }
}
</script>
<style>
@import "../../assets/plugins/simplebar/css/simplebar.css";
@import "../../assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css";
@import "../../assets/plugins/metismenu/css/metisMenu.min.css";
@import "../../assets/css/pace.min.css";
@import "../../assets/css/bootstrap.min.css";
@import "../../assets/css/bootstrap-extended.css";
@import url("https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap");
@import "../../assets/css/app.css";
@import "../../assets/css/icons.css";
@import "../../assets/css/dark-theme.css";
@import "../../assets/css/semi-dark.css";
@import "../../assets/css/header-colors.css";
@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css");

/* Header wrapper sticky - keeps TopRocker and MenuRocker together */
.header-wrapper-sticky {
  position: sticky;
  top: 0;
  z-index: 1000;
  background: #ffffff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  width: 100%;
  display: flex;
  flex-direction: column;
}

/* Ensure TopRocker and MenuRocker are connected with no gap */
.header-wrapper-sticky > * {
  margin: 0 !important;
  padding: 0 !important;
}
/* Ensure page content has proper margin */
.page-wrapper {
  background: #ffffff !important;
  overflow-y: visible !important;
  overflow-x: hidden !important;
}

.page-content {
  padding: 0;
  max-width: 100%;
  margin: 0;
  background: transparent;
  overflow-y: visible !important;
  overflow-x: hidden !important;
  width: 100%;
}

.wrapper {
  overflow-y: visible !important;
  overflow-x: hidden !important;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
</style>