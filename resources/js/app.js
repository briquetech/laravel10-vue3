global.$ = global.jQuery = require('jquery');
require('./bootstrap')
import { createApp } from 'vue'
import LeftNavbarComponent from './components/LeftNavbarComponent';
import ExampleComponent from "./components/ExampleComponent";

// Mixins
import SWAlert from "./mixins/swal";
import SWUtils from "./mixins/utils";
import Masters from "./mixins/masters";

// Datatables
import DatatableComponent from './components/common/DatatableComponent.vue';
// All form errors
import ErrorComponent from "./components/common/ErrorComponent.vue";

const app = createApp({})

// Components
app.component("DataTableComponent", DatatableComponent);
app.component("error", ErrorComponent);

// Nav
app.component('left-nav-component', LeftNavbarComponent);

// Pages
app.component("example", ExampleComponent);

// Mixins
app.mixin(SWAlert);
app.mixin(SWUtils);
app.mixin(Masters);

app.mount('#app');