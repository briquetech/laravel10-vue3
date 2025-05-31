import { createApp } from 'vue';
import CreatorHome from './components/CreatorHome.vue';
import MonthYearPicker from './components/MonthYearPicker.vue';

const app = createApp({});

app.component('creator-home', CreatorHome);
app.component('MonthYearPicker', MonthYearPicker);

app.mount('#app');
