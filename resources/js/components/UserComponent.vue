<template>
	<div id="user-main">
		<div class="row">
			<div class="col-sm-12">
				<div class="row mb-1">
					<div class="col-sm-7">
						<div class="d-flex align-items-center mb-2">
							<h4 class="m-0 me-4">USERS</h4>
							<a id="add_user_btn" class="btn btn-success btn-sm" href="#" role="button"
								data-bs-toggle="modal" data-bs-target="#addUserModal"
								v-if="['110', '111'].indexOf(all_permissions) >= 0">ADD</a>
						</div>
						<p class="text-primary">You can add at the most {{ param1 }} employees.</p>
					</div>
				</div>
				<DataTableComponent :dataprops="dataprops" @view-object="viewUser" @edit-object="prepareEditUser"
					@toggle-object-status="toggleUser"></DataTableComponent>
			</div>
		</div>
		<div class="modal fade" ref="addEditModal" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
			aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addUserModalLabel">ADD User</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="cancelAddEdit"></button>
					</div>
					<div class="modal-body">
						<div class='row mb-4'>
							<div class="col-4">
								<label for="add_user_name" class="form-label text-uppercase fw-bold me-3">Name <span
										class="mandatory">*</span></label><a href="#" class="cstooltip"
									data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote."
									tabindex="-1"><i class="ph ph-question"></i></a> 
								<div>
									<input type="text" class="form-control" v-model="userForAdd.name" id="add_user_name" placeholder="Enter Name">
									<div v-if="v$.userForAdd.name.$error" class="mandatory my-2">Mandatory</div>
								</div>
							</div>
							<div class="col-4">
								<label for="add_user_employee_code"
									class="form-label text-uppercase fw-bold me-3">Employee Code <span
										class="mandatory">*</span></label><a href="#" class="cstooltip"
									data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote."
									tabindex="-1"><i class="ph ph-question"></i></a> 
								<div v-if="['110', '111'].indexOf(all_permissions) >= 0">
									<input type="text" class="form-control" v-model="userForAdd.employee_code"
										id="add_user_employee_code" placeholder="Enter Employee Code">
									<div v-if="v$.userForAdd.employee_code.$error" class="mandatory my-2">Mandatory</div>
								</div>
								<div class="py-2" v-else>
									{{ userForAdd.employee_code }}
								</div>
							</div>
						</div>
						<div class='row mb-4'>
							<div class="col-4">
								<label for="add_user_email" class="form-label text-uppercase fw-bold me-3">Date of Joining <span class="mandatory">*</span></label>
								<div v-if="['110', '111'].indexOf(all_permissions) >= 0">
									<input type="date" class="form-control" v-model="userForAdd.date_of_joining"
										id="add_user_doj">
									<div v-if="v$.userForAdd.date_of_joining.$error" class="mandatory my-2">Mandatory</div>
								</div>
								<div class="py-2" v-else>
									{{ formatMySQLDate(userForAdd.date_of_joining, "MMM dd, yyyy") }}
								</div>
							</div>
							<div class="col-4">
								<label for="add_user_department"
									class="form-label text-uppercase fw-bold me-3">Department <span
										class="mandatory">*</span></label><a href="#" class="cstooltip"
									data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote."
									tabindex="-1"><i class="ph ph-question"></i></a>
								<div v-if="['110', '111'].indexOf(all_permissions) >= 0">
									<select class="form-select" v-model="userForAdd.department_id" id="add_department_id">
										<optgroup v-if="allDepartmentList" label="Choose Department">
											<template v-for="department in allDepartmentList" :key="department.id">
												<option :value="department.id">{{ department.title }}</option>
											</template>
										</optgroup>
									</select>
									<div v-if="v$.userForAdd.department_id.$error" class="mandatory my-2">Mandatory</div>
								</div>
								<div class="py-2" v-else>
									{{ userForAdd.department }}
								</div>
							</div>
							<div class="col-4">
								<label for="add_user_designation"
									class="form-label text-uppercase fw-bold me-3">Designation <span
										class="mandatory">*</span></label><a href="#" class="cstooltip"
									data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote."
									tabindex="-1"><i class="ph ph-question"></i></a>
								<div v-if="['110', '111'].indexOf(all_permissions) >= 0">
									<select class="form-select" v-model="userForAdd.designation_id" id="add_designation_id">
										<optgroup v-if="allDesignationList" label="Choose Department">
											<template v-for="designation in allDesignationList" :key="designation.id">
												<option :value="designation.id">{{ designation.title }}</option>
											</template>
										</optgroup>
									</select>
									<div v-if="v$.userForAdd.designation_id.$error" class="mandatory my-2">Mandatory</div>
								</div>
								<div class="py-2" v-else>
									{{ userForAdd.designation }}
								</div>
							</div>
						</div>
						<div class='row mb-4'>
							<div class="col-4">
								<label for="add_user_reporting_to"
									class="form-label text-uppercase fw-bold me-3">Reporting To <span
										class="mandatory">*</span></label>
								<div v-if="['110', '111'].indexOf(all_permissions) >= 0">
									<select class="form-select" v-model="userForAdd.reporting_to" id="add_reporting_to">
										<option value="0" v-if="currentUser.role_id == 1">None</option>
										<option value="0" v-else>Me</option>
										<optgroup v-if="allReportingToList" label="Choose Reporting To">
											<template v-for="reportingTo in allReportingToList" :key="reportingTo.id">
												<option :value="reportingTo.id">{{ reportingTo.name }}</option>
											</template>
										</optgroup>
									</select>
									<div v-if="v$.userForAdd.reporting_to.$error" class="mandatory my-2">Mandatory</div>
								</div>
								<div class="py-2" v-else>
									{{ userForAdd.reporting_to_user?.name }}
								</div>
							</div>
							<div class="col-4">
								<label for="add_user_role_id" class="form-label text-uppercase fw-bold me-3">Role
									<span class="mandatory">*</span></label> 
								<div v-if="['110', '111'].indexOf(all_permissions) >= 0">
									<select class="form-select" v-model="userForAdd.role_id" id="add_role_id">
										<optgroup v-if="allRoleIdList" label="Choose Role Id">
											<template v-for="roleId in allRoleIdList" :key="roleId.id">
												<option :value="roleId.id">{{ roleId.title }}</option>
											</template>
										</optgroup>
									</select>
									<div v-if="v$.userForAdd.role_id.$error" class="mandatory my-2">Mandatory</div>
								</div>
								<div class="py-2" v-else>
									{{ userForAdd.role?.title }}
								</div>
							</div>
						</div>
						<div class='row mb-2'>
							<div class="col-4">
								<h6 class="font-monospace fw-bold text-primary">LOGIN INFO</h6>
							</div>
						</div>
						<div class='row mb-4' v-if="userForAdd.id > 0">
							<div class="col-4">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" v-model="userForAdd.change_password" id="add_change_password"/>
									<label class="form-check-label" for="add_change_password"> Change Password </label>
								</div>
							</div>
						</div>
						<div class='row mb-4'>
							<div class="col-4">
								<label for="add_user_email" class="form-label text-uppercase fw-bold me-3">Email <span class="mandatory">*</span></label>
								<div>
									<input type="email" class="form-control" v-model="userForAdd.email"
										id="add_user_email" placeholder="Enter Email">
									<div v-if="v$.userForAdd.email.$error" class="mandatory my-2">Mandatory</div>
								</div>
							</div>
							<div class="col-4">
								<label for="add_password" class="form-label text-uppercase fw-bold me-3">Password <span class="mandatory">*</span></label>
								<a href="#" class="cstooltip"
									data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote."
									tabindex="-1"><i class="ph ph-question"></i></a>
								<div>
									<input type="text" class="form-control" v-model="userForAdd.password" id="add_password" placeholder="Enter Password" :disabled="userForAdd.id > 0 && !userForAdd.change_password">
									<div v-if="v$.userForAdd.password.$error" class="mandatory my-2">Mandatory</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-dark btn-sm" data-bs-dismiss="modal"
							@click="cancelAddEdit()">CLOSE</button>
						<button type="button" class="btn btn-dark btn-sm" @click="saveUser()">SAVE CHANGES</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" ref="readModal" id="readUserModal" tabindex="-1" aria-labelledby="readUserModalLabel"
			aria-hidden="true" v-if="readUser">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="readUserModalLabel">View User</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class='row mb-4'>
							<div class="col-4">
								<label class="form-label text-uppercase fw-bold m-0">Name</label>
								<div>
									<span v-if='readUser.name'>{{ readUser.name }}</span><span v-else><i>Not
											specified</i></span>
								</div>
							</div>
							<div class="col-4">
								<label class="form-label text-uppercase fw-bold m-0">Email</label>
								<div>
									<span v-if='readUser.email'>{{ readUser.email }}</span><span v-else><i>Not
											specified</i></span>
								</div>
							</div>
							<div class="col-4">
								<label class="form-label text-uppercase fw-bold m-0">Department</label>
								<div>
									<span v-if='readUser.department?.title'>{{ readUser.department?.title }}</span>
									<span v-else><i>Not specified</i></span>
								</div>
							</div>
							<div class="col-4">
								<label class="form-label text-uppercase fw-bold m-0">Designation</label>
								<div>
									<span v-if='readUser.designation?.title'>{{ readUser.designation?.title }}</span>
									<span v-else><i>Not specified</i></span>
								</div>
							</div>
						</div>
						<div class='row mb-4'>
							<div class="col-4">
								<label class="form-label text-uppercase fw-bold m-0">Employee Code</label>
								<div>
									<span v-if='readUser.employee_code'>{{ readUser.employee_code }}</span><span
										v-else><i>Not
											specified</i></span>
								</div>
							</div>
							<div class="col-4">
								<label class="form-label text-uppercase fw-bold m-0">Reporting To</label>
								<div>
									<span v-if='readUser.reporting_to_user?.name'>{{ readUser.reporting_to_user?.name
										}}</span><span
										v-else><i>Not specified</i></span>
								</div>
							</div>
							<div class="col-4">
								<label class="form-label text-uppercase fw-bold m-0">Role</label>
								<div>
									<span v-if='readUser.role?.title'>{{ readUser.role?.title }}</span><span
										v-else><i>Not
											specified</i></span>
								</div>
							</div>
						</div>
						<div class='row mb-4'>
							<div class="col-4">
								<label class="form-label text-uppercase fw-bold m-0">Status</label>
								<div>
									<span class="badge rounded-pill bg-success"
										v-if="readUser.status == 1">ACTIVE</span>
									<span class="badge rounded-pill bg-danger"
										v-if="readUser.status == 0">INACTIVE</span>
								</div>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-dark btn-sm" data-bs-dismiss="modal">CLOSE</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import { useVuelidate } from '@vuelidate/core';
import { required, minValue, alphaNum, numeric, email, requiredIf, minLength } from '@vuelidate/validators';
import * as bootstrap from 'bootstrap';
function initialState() {
	return {
		id: 0,
		name: '',
		email: '',
		password: '',
		change_password: false,
		date_of_joining: null,
		department: '',
		employee_code: '',
		reporting_to: 0,
		role_id: null,
		action: ""
	};
}
function initialStateValidations() {
	return {
		name: { required, minLengthValue: minLength(1) },
		email: { required, email },
		password: { minLengthValue: minLength(3) },
		date_of_joining: { required },
		department_id: { required },
		designation_id: { required },
		employee_code: { required, minLengthValue: minLength(1) },
		reporting_to: { required, },
		role_id: { required, },
	}
}
export default {
	name: "Usermaster",
	props: ['current_user_id', 'all_permissions', 'param1'],
	setup() {
		return {
			v$: useVuelidate()
		}
	},
	data() {
		return {
			dataprops: {
				id: 'user-list',
				class: 'a',
				base_url: '/api/user/',
				columns: [
					{ title: 'Name', property: 'name', },
					{ title: 'Email', property: 'email', },
					{ title: 'Employee Code', property: 'employee_code', },
					{ title: 'Reporting To', property: 'reporting_to_user.name', alt_value: 'No One', },
					{ title: 'Role', property: 'role.title', alt_value: 'Not Specified', },
				],
				data_to_send: { current_user_id: this.current_user_id },
				reload: false,
				search: 'simple',
			},
			addEditModal: null,
			readModal: null,
			currentUser: siteUserObject,
			userForAdd: initialState(),
			readUser: {},
			allReportingToList: [],
			allRoleIdList: [],
			allDepartmentList: [],
			allDesignationList: [],
		}
	},
	validations() {
		return {
			userForAdd: initialStateValidations()
		};
	},
	methods: {
		cancelAddEdit(event) {
			this.userForAdd = initialState();
			this.v$.$reset();
		},
		async saveUser(event) {
			var that = this;
			const result = await this.v$.$validate();
			if (!result) {
				that.showToast("Form validation failed. Please check.", "error", "bottom", 2000);
				console.log(that.v$.$errors);
				return;
			}
			if (this.userForAdd.id == 0 || (this.userForAdd.id > 0 && this.userForAdd.change_password ) ) {
				if( !this.userForAdd.password || this.userForAdd.password.trim().length == 0 ){
					that.showToast("Please set the password.", "error", "bottom", 2000);
					return;
				}
			}
			if (!that.userForAdd.action || that.userForAdd.action == "")
				that.userForAdd.action = "details";
			// return;
			$("#addUserModal").modal('hide');
			this.showLoading("Saving ...");
			axios.post('/user/save', { user: that.userForAdd }).then(async function (response) {
				that.closeSwal();
				var status = response.data.status;
				if (status == 1) {
					// Ajax to submit
					that.showToast('Users saved successfully', 'success', 'bottom', 3000);
					setTimeout(() => {
						that.dataprops.reload = true;
					}, 1500);
					that.userForAdd = initialState();
					if ( that.currentUser.id == 1 ) {
						that.allReportingToList = await that.loadAllUser(true);
					}
					else {
						that.allReportingToList = await that.loadAllReportees(that.currentUser.id);
					}
					that.allRoleIdList = await that.loadAllRole(true);
					that.v$.$reset();
				}
				else
					that.showErrors("User could not be saved successfully", response.data.messages, "bottom", 3000);
			})
				.catch(function (error) {
					console.log(error);
					that.closeSwal();
					that.showToast("User could not be saved successfully", "error", "bottom", 3000);
				});
		},
		prepareEditUser(user) {
			this.userForAdd = Object.assign({}, user);
			if (this.userForAdd.is_percentage == 1)
				this.userForAdd.is_percentage = true;
			else
				this.userForAdd.is_percentage = false;
			this.userForAdd.password = "";
			this.addEditModal.show();
		},
		viewUser(user) {
			this.readUser = user;
			this.readModal.show();
		},
		toggleUser(user, status) {
			var that = this;
			Swal.fire({
				icon: "question",
				html: "Do you really want to " + (status == 1 ? "reactivate" : "deactivate") + " the User - <br/>\"" + user.email + "\"?",
				showCancelButton: true,
			}).then((result) => {
				if (result.isConfirmed) {
					that.userForAdd = user;
					that.userForAdd.status = status;
					that.userForAdd.action = "status";
					that.saveUser();
				}
			});
		}
	},
	async mounted() {
		this.addEditModal = new bootstrap.Modal(this.$refs.addEditModal, { backdrop: 'static', keyboard: false });
		this.readModal = new bootstrap.Modal(this.$refs.readModal, { backdrop: 'static', keyboard: false });
		if ( this.currentUser.role_id == 1 )
			this.allReportingToList = await this.loadAllUser(true);
		else
			this.allReportingToList = await this.loadAllReportees(this.currentUser.id);
		this.allRoleIdList = await this.loadAllRole(true);
	}
}
</script>
