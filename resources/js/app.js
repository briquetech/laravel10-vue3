global.$ = global.jQuery = require('jquery');
require('./bootstrap')
import { createApp } from 'vue'
import LeftNavbarComponent from './components/LeftNavbarComponent';
import ExampleComponent from "./components/ExampleComponent";

const app = createApp({})

// Nav
app.component('left-nav-component', LeftNavbarComponent);

// Pages
app.component("example", ExampleComponent);

app.mount('#app');