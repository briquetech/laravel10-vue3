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

// Users
import UsersComponent from "./components/UsersComponent";

const app = createApp({})

// Components
app.component("DataTableComponent", DatatableComponent);
app.component("error", ErrorComponent);

// Nav
app.component('left-nav-component', LeftNavbarComponent);

// Pages
app.component("example", ExampleComponent);

// Users
app.component("users-component", UsersComponent);

// Mixins
app.mixin(SWAlert);
app.mixin(SWUtils);
app.mixin(Masters);

// DIRECTIVES
// Use v-empty-zero
app.directive("emptyZero", {
    mounted(el) {
        // Add a focus event listener
        el.addEventListener("focus", function () {
            if (parseFloat(el.value) == 0) {
                el.value = "";
            }
        });

        // Add a blur event listener
        el.addEventListener("blur", function () {
            if (el.value === "") {
                el.value = "0";
            }
        });
    },
});

app.mount('#app');
