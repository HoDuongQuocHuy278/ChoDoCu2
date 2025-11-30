import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import Default from './layout/wrapper/index.vue'
import Blank from './layout/wrapper/BlankLayout.vue'
import VCalendar from 'v-calendar';
import 'v-calendar/style.css';
const app = createApp(App)

app.use(router)
app.component("default-layout", Default);
app.component("blank-layout", Blank);
app.use(VCalendar, {})
app.mount("#app")