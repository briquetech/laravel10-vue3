global.$ = global.jQuery = require('jquery');
require('./bootstrap')
import { createApp } from 'vue'
import ExampleComponent from "./components/ExampleComponent";

const app = createApp({})

app.component("example", ExampleComponent);

app.mount('#app')