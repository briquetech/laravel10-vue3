<!doctype html>
<html lang="en">

<head>
	<title>Title</title>
	<!-- Required meta tags -->
	<meta charset="utf-8" />
	<meta
		name="viewport"
		content="width=device-width, initial-scale=1, shrink-to-fit=no" />

	<link
		href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
		rel="stylesheet"
		integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
		crossorigin="anonymous" />
	<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
	<style>
		.form-label{
			font-weight: 600;
		}
		.show-table th, .show-table td{
			font-size: small;
		}
		.action-btn{
			--bs-btn-font-size: .75rem;
		}
	</style>
</head>

<body>
	<div id="app">
		<creator-home :table-Names='<?php echo json_encode($tableNames) ?>'></creator-home>
	</div>
	<!-- Bootstrap JavaScript Libraries -->
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
		integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
		crossorigin="anonymous"></script>

	<script
		src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
		integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
		crossorigin="anonymous"></script>

	<script type="module" src="{{ asset('vendor/brique-admin-creator/js/app.js') }}"></script>
	<script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
</body>

</html>