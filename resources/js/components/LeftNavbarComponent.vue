<template>
	<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse text-light">
		<div class="position-sticky">
			<div class="flex-shrink-0 px-3">
				<ul class="navbar-nav nav flex-column">
					<li class="nav-item">
						<a class="nav-link p-0 pt-2 d-flex align-items-center" :class="{ 'active': (currentRoute === 'home' || currentRoute === '/') }" aria-current="page" href="/">
							<i class="ph-gauge me-2"></i><span>Dashboard</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link p-0 pt-2 d-flex align-items-center" :class="{ 'active': currentRoute === 'calendar' }" aria-current="page" href="#">
							<i class="ph-calendar me-2"></i><span>Calendar</span>
						</a>
					</li>
				</ul>
				<h4 class="sidebar-heading d-flex mt-4 mb-1 fw-bolder">
					<span>Example Heading</span>
				</h4>
				<ul class="navbar-nav nav flex-column">
					<li class="nav-item">
						<a class="nav-link p-0 pt-2 d-flex align-items-center" :class="{ 'active': currentRoute === 'exmaple' }" href="#"><i class="ph-user me-2"></i><span>Example</span></a>
					</li>
				</ul>
			</div>
		</div>
	</nav>
</template>
<style>
.sidebar .nav-link.active{
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
		return{
			// For toggling
			salesMasters: true,
			salesTransac: false,
			vendorMasters: false,
			mainMasters: false,
			userMasters: false,
			otherMasters: false,
			currentRoute: firstEndpoint
		}
	}
}
</script>
