<template>
	<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse text-light">
		<div class="position-sticky">
			<div class="flex-shrink-0 px-3">
				<ul class="navbar-nav nav flex-column" id="nav_open">
					<li class="nav-item">
						<a class="nav-link p-0 pt-2 d-flex align-items-center"
							:class="{ 'active': (currentRoute === 'home' || currentRoute === '/') }" aria-current="page"
							href="/">
							<i class="ph-gauge me-2"></i>
							<span>Dashboard</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link p-0 pt-2 d-flex align-items-center"
							:class="{ 'active': currentRoute === 'calendar' }" aria-current="page" href="#">
							<i class="ph-calendar me-2"></i>
							<span>Calendar</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link p-0 pt-2 d-flex align-items-center"
							:class="{ 'active': currentRoute === 'all-masters' }" href="/all-masters">
							<i class="me-2 ph ph-square"></i><span>All Masters</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link p-0 pt-2 d-flex align-items-center"
							:class="{ 'active': currentRoute === 'user' }" href="/user">
							<i class="me-2 ph ph-users"></i><span>All Employees</span>
						</a>
					</li>
				</ul>
				<!-- My Corner -->
				<h5 class="sidebar-heading d-flex mt-4 mb-1 fw-bolder"
					v-if="permittedActions && permittedActions.length > 0">
					<span>Actions</span>
				</h5>
				<ul class="navbar-nav nav flex-column" id="nav_masters"
					v-if="permittedActions && permittedActions.length > 0">
					<li class="nav-item" v-for="permittedObject in permittedActions">
						<a class="nav-link p-0 pt-2 d-flex align-items-center"
							:class="{ 'active': currentRoute === permittedObject.name.toLowerCase() }"
							:href="permittedObject.url">
							<i :class="'me-2 ph ph-'+permittedObject.phicon"></i>
							<span>{{ permittedObject.title }}</span>
						</a>
					</li>
				</ul>
				<!-- My Corner -->
			</div>
		</div>
	</nav>
</template>
<style>
.sidebar .nav-link.active {
	color: rgb(13, 219, 219);
}
</style>
<script>
// Get the current pathname
const currentPath = window.location.pathname;
// Split the pathname into an array of segments
const pathSegments = currentPath.split('/');
// Get the first segment
const firstEndpoint = pathSegments[1]; // Index 0 is an empty string due to leading slash

export default {
   data(){
		return {
			menuState: 0,
			// For toggling
			salesMasters: true,
			salesTransac: false,
			vendorMasters: false,
			mainMasters: false,
			userMasters: false,
			otherMasters: false,
			permittedActions: [],
			permittedInfo: [],
			permittedOthers: [],
			currentRoute: firstEndpoint,
			currentUser: siteUserObject
		}
	},
	mounted() {
		if (this.currentUser) {
			var that = this;
			axios.post("/role/get-permitted-objects", { role_id: this.currentUser.role_id })
				.then(function (response) {
					if (response.data.hasOwnProperty("status") && response.data.status == 1) {
						that.menuState = 1;
						if (response.data.hasOwnProperty("permitted_objects") && response.data.permitted_objects.length > 0) {
							that.permittedActions= JSON.parse(JSON.stringify(response.data.permitted_objects));
							
						}
					}
				})
				.catch(function (error) {
					console.log(error);
				});
		}
	}
}
</script>
