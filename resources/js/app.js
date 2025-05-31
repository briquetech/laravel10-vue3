global.$ = global.jQuery = require('jquery');
require('./bootstrap')
import { createApp } from 'vue'
import LeftNavbarComponent from './components/LeftNavbarComponent';
import ExampleComponent from "./components/ExampleComponent";

// Mixins
import SWAlert from "./mixins/swal";
import SWUtils from "./mixins/utils";
import Masters from "./mixins/masters";
import CKEditor from "@ckeditor/ckeditor5-vue";

// Datatables
import DatatableComponent from './components/common/DatatableComponent.vue';
// All form errors
import ErrorComponent from "./components/common/ErrorComponent.vue";
import ObjectNotFoundComponent from "./components/common/ObjectNotFoundComponent.vue";
import ObjectNotEditableComponent from "./components/common/ObjectNotEditableComponent.vue";


// Role
import RoleComponent from "./components/RoleComponent";

// Users
import UserComponent from "./components/UserComponent";

// Department
import DepartmentComponent from "./components/masters/DepartmentComponent";

const app = createApp({})

// Components
app.component("DataTableComponent", DatatableComponent);
app.component("error", ErrorComponent);
app.component("object-not-found", ObjectNotFoundComponent);
app.component("object-not-editable", ObjectNotEditableComponent);

// Nav
app.component('left-nav-component', LeftNavbarComponent);

// Pages
app.component("example", ExampleComponent);

// Role
app.component("role-component", RoleComponent);

// Users
app.component("user-component", UserComponent);

// Department
app.component("department-component", DepartmentComponent);

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
app.use(CKEditor);
app.config.globalProperties.docRoot = window.location.origin;

app.mount('#app');
