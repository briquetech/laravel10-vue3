# Laravel 10, Vue 3, jQuery, Bootstrap 5.3 Boilerplate

This is a boilerplate project that combines Laravel 10, Vue 3, the latest jQuery, and Bootstrap 5.3 to provide a solid foundation for further development.

## Features

- **Laravel 10:** The latest version of the popular PHP framework for building web applications.

- **Vue 3:** A progressive JavaScript framework for building user interfaces. Leverage the power of reactive data binding and components.

- **jQuery:** A fast, small, and feature-rich JavaScript library. It simplifies tasks like HTML document traversal and manipulation, event handling, and animation.

- **Bootstrap 5.3:** The world's most popular front-end open-source toolkit, featuring responsive and mobile-first design for modern web development.

## Getting Started

### Prerequisites

- [PHP](https://www.php.net/) (Recommended version for PHP is 8.1)
- [Laravel](https://www.laravel.com/) (Recommended version for Laravel is 10)
- [Laravel Mix](https://laravel-mix.com/) (Recommended version for Laravel Mix is 6)
- [Node.js](https://nodejs.org/) (LTS version recommended)
- [Composer](https://getcomposer.org/)
- [Git](https://git-scm.com/)

### Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/briquetech/laravel10-vue3.git
    ```

2. Navigate to the project directory:

    ```bash
    cd your-project
    ```

3. Install PHP dependencies:

    ```bash
    composer install
    ```

4. Install Node.js dependencies:

    ```bash
    npm install
    ```

5. Run Artisan Optimize

    ```bash
    php artisan optimize:clear
    ```

6. Run migrations and seed the database for the admin user:

    ```bash
    php artisan migrate --seed
    ```

7. Compile assets:

    ```bash
    npm run dev
    ```

8. Serve the application:

    ```bash
    php artisan serve
    ```

9. Open a new window:

	```bash
	npm run watch
	```

Visit [http://localhost:8000](http://localhost:8000) in your browser to see the application.

## Usage

To create a new component, following is the procedure:

1. Create a component in the following directory

	```bash
	resources/js/components
	```

if the 'component' directory doesnt exist, create one. For example, to create a Vue3 component called **`SampleComponent`**, create a file called **`SampleComponent.vue`** within the above folder.

2. Copy the following code within the component or create your own,

	```html
	<template>
		<div>
			<h1>Hello {{ greeting }}!</h1>
			<button @click="changeGreeting">Change Greeting</button>
		</div>
	</template>

	<script>
	export default {
		name: "SampleComponent",
		data() {
			return {
				greeting: 'Vue 3',
			};
		},
		methods: {
			changeGreeting() {
				this.greeting = 'Vue 4 (coming soon)';
			},
		},
	};
	</script>
	```

3. Open **`resources/js/app.js`**. To import **`SampleComponent`**, add the **import** statement

	```javascript
	import SampleComponent from "./components/SampleComponent";
	```

	before

	```javascript
	const app = createApp({})
	```

	To register and register **`SampleComponent`**, within the same file, add the following statement before the app mount statement 

	```javascript
	app.component("sample-component", SampleComponent);
	```

	anytime after

	```javascript
	const app = createApp({})
	```

	and before 

	```javascript
	app.mount('#app')
	```

	So your **`app.js`** should look like

	```javascript
	...
	import { createApp } from 'vue'
	import ExampleComponent from "./components/ExampleComponent";
	...

	const app = createApp({})
	...
	app.component("sample-component", SampleComponent);
	...
	app.mount('#app')
	```

Make sure you run *`npm run dev`* if you are not already running *`npm run watch`* in an other terminal window.

4. Now you can use this component like this in any blade page within project folder

	```html
	<sample-component></sample-component>
	```

## Contributing

If you'd like to contribute, please fork the repository and create a pull request. All contributions are welcome!

## License

This project is licensed under the [Apache License](LICENSE).
