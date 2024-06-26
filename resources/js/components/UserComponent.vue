<template>
	<div id="user-main">
		<div class="row">
			<div class="col-sm-12">
				<div class="row mb-1">
					<div class="col-sm-7">
						<div class="d-flex align-items-center mb-2">
							<h4 class="m-0 me-4">USERS</h4>
							<a id="add_user_btn" class="btn btn-success btn-sm" href="#" role="button"
								data-bs-toggle="modal" data-bs-target="#addUserModal" v-if="currentUser?.role_id == 1">ADD</a>
						</div>
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
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class='row mb-4'>
							<div class="col-4">
								<label for="add_user_name" class="form-label text-uppercase fw-bold me-3">Name <span
										class="mandatory">*</span></label><a href="#" class="cstooltip"
									data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote."
									tabindex="-1"><i class="ph ph-question"></i></a> <span
									v-if="v$.userForAdd.name.$error" class="mandatory ms-3">Mandatory</span>
								<div>
									<input type="text" class="form-control" v-model="userForAdd.name" id="add_user_name"
										placeholder="Enter Name">
								</div>
							</div>
							<div class="col-4">
								<label for="add_user_email" class="form-label text-uppercase fw-bold me-3">Email <span
										class="mandatory">*</span></label> <span v-if="v$.userForAdd.email.$error"
									class="mandatory ms-3">Mandatory</span>
								<div>
									<input type="email" class="form-control" v-model="userForAdd.email"
										id="add_user_email" placeholder="Enter Email">
								</div>
							</div>
							<div class="col-4">
								<label for="add_user_employee_code"
									class="form-label text-uppercase fw-bold me-3">Employee Code <span
										class="mandatory">*</span></label><a href="#" class="cstooltip"
									data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote."
									tabindex="-1"><i class="ph ph-question"></i></a> <span
									v-if="v$.userForAdd.employee_code.$error" class="mandatory ms-3">Mandatory</span>
								<div>
									<input type="text" class="form-control" v-model="userForAdd.employee_code"
										id="add_user_employee_code" placeholder="Enter Employee Code">
								</div>
							</div>
						</div>
						<div class='row mb-4'>
							<div class="col-4">
								<label for="add_user_department"
									class="form-label text-uppercase fw-bold me-3">Department <span
										class="mandatory">*</span></label><a href="#" class="cstooltip"
									data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote."
									tabindex="-1"><i class="ph ph-question"></i></a> <span
									v-if="v$.userForAdd.department.$error" class="mandatory ms-3">Mandatory</span>
								<div>
									<input type="text" class="form-control" v-model="userForAdd.department"
										id="add_user_department" placeholder="Enter Department">
								</div>
							</div>
							<div class="col-4">
								<label for="add_user_reporting_to"
									class="form-label text-uppercase fw-bold me-3">Reporting To <span
										class="mandatory">*</span></label> <span
									v-if="v$.userForAdd.reporting_to.$error" class="mandatory ms-3">Mandatory</span>
								<div>
									<select class="form-select" v-model="userForAdd.reporting_to" id="add_reporting_to">
										<option value="0">None</option>
										<optgroup v-if="allReportingToList" label="Choose Reporting To">
											<template v-for="reportingTo in allReportingToList" :key="reportingTo.id">
												<option :value="reportingTo.id">{{ reportingTo.name }}</option>
											</template>
										</optgroup>
									</select>
								</div>
							</div>
							<div class="col-4">
								<label for="add_user_role_id" class="form-label text-uppercase fw-bold me-3">Role Id
									<span
										class="mandatory">*</span></label> <span v-if="v$.userForAdd.role_id.$error"
									class="mandatory ms-3">Mandatory</span>
								<div>
									<select class="form-select" v-model="userForAdd.role_id" id="add_role_id">
										<optgroup v-if="allRoleIdList" label="Choose Role Id">
											<template v-for="roleId in allRoleIdList" :key="roleId.id">
												<option :value="roleId.id">{{ roleId.title }}</option>
											</template>
										</optgroup>
									</select>
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
									<span v-if='readUser.department'>{{ readUser.department }}</span><span v-else><i>Not
											specified</i></span>
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
		department: '',
		employee_code: '',
		reporting_to: null,
		role_id: null,
		action: ""
	};
}
function initialStateValidations() {
	return {
		name: { required, minLengthValue: minLength(1) },
		email: { required, email },
		password: { required, minLengthValue: minLength(1) },
		department: { required, minLengthValue: minLength(1) },
		employee_code: { required, minLengthValue: minLength(1) },
		reporting_to: { required, },
		role_id: { required, },

	}
}
export default {
	name: "Usermaster",
	props: ['current_user_id'],
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
					{ title: 'Department', property: 'department', },
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
		},
		async saveUser(event) {
			var thisVar = this;
			const result = await this.v$.$validate();
			if (!result) {
				thisVar.showToast("Form validation failed. Please check.", "error", "bottom", 2000);
				console.log(thisVar.v$.$errors);
				return;
			}
			if (!thisVar.userForAdd.action || thisVar.userForAdd.action == "")
				thisVar.userForAdd.action = "details";
			$("#addUserModal").modal('hide');
			this.showLoading("Saving ...");
			axios.post('/user/save', { user: thisVar.userForAdd }).then(async function (response) {
				thisVar.closeSwal();
				var status = response.data.status;
				if (status == 1) {
					// Ajax to submit
					thisVar.showToast('Users saved successfully', 'success', 'bottom', 3000);
					setTimeout(() => {
						thisVar.dataprops.reload = true;
					}, 1500);
					thisVar.userForAdd = initialState();
					thisVar.allReportingToList = await thisVar.loadAllUser(true);
					thisVar.allRoleIdList = await thisVar.loadAllRole(true);
				}
				else
					thisVar.showErrors("Users could not be saved successfully", response.data.messages, "bottom", 3000);
			})
				.catch(function (error) {
					console.log(error);
					thisVar.closeSwal();
					thisVar.showToast("Users could not be saved successfully", "error", "bottom", 3000);
				});
		},
		prepareEditUser(user) {
			this.userForAdd = Object.assign({}, user);
			if (this.userForAdd.is_percentage == 1)
				this.userForAdd.is_percentage = true;
			else
				this.userForAdd.is_percentage = false;
			this.addEditModal.show();
		},
		viewUser(user) {
			this.readUser = user;
			this.readModal.show();
		},
		toggleUser(user, status) {
			var thisVar = this;
			Swal.fire({
				icon: "question",
				html: "Do you really want to " + (status == 1 ? "reactivate" : "deactivate") + " the Users - <br/>\"" + user.email + "\"?",
				showCancelButton: true,
			}).then((result) => {
				if (result.isConfirmed) {
					thisVar.userForAdd = user;
					thisVar.userForAdd.status = status;
					thisVar.userForAdd.action = "status";
					thisVar.saveUser();
				}
			});
		}
	},
	async mounted() {
		this.addEditModal = new bootstrap.Modal(this.$refs.addEditModal, { backdrop: 'static', keyboard: false });
		this.readModal = new bootstrap.Modal(this.$refs.readModal, { backdrop: 'static', keyboard: false });
		this.allReportingToList = await this.loadAllUser(true);
		console.log(this.currentUser);
		this.allRoleIdList = await this.loadAllRole(true);
	}
}
</script>
