import { createApp } from 'vue';
import CreateShowtimeForm from './components/CreateShowtimeForm.vue';

// Chỉ mount khi element #app tồn tại trên trang
const appEl = document.getElementById('app');
if (appEl) {
    const app = createApp({});
    app.component('create-showtime-form', CreateShowtimeForm);
    app.mount('#app');
}
