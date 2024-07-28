<template>
	<div id="users-main">
		<div class="row">
			<div class="col-sm-12">
				<div class="row mb-1">
					<div class="col-sm-7">
						<div class="d-flex align-items-center mb-2">
							<h4 class="m-0 me-4">USERS</h4>
							<a id="add_users_btn" class="btn btn-success btn-sm" href="#" role="button" data-bs-toggle="modal" data-bs-target="#addUsersModal">ADD</a>
						</div>
					</div>
				</div>
				<DataTableComponent :dataprops="dataprops" @view-object="viewUsers" @edit-object="prepareEditUsers" @toggle-object-status="toggleUsers"></DataTableComponent>
			</div>
		</div>
				<div class="modal fade" ref="addEditModal" id="addUsersModal" tabindex="-1" aria-labelledby="addUsersModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addUsersModalLabel">Add Users</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="cancelAddEdit"></button>
					</div>
					<div class="modal-body">
						<div class='row mb-4'>
<div class="col-4">
	<label for="add_users_name" class="form-label text-uppercase fw-bold me-3">Name <span class="mandatory">*</span></label><a href="#" class="cstooltip" data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote." tabindex="-1"><i class="ph ph-question"></i></a> <span v-if="v$.usersForAdd.name.$error" class="mandatory ms-3">Mandatory</span>
	<div>
		<input type="text" class="form-control"  v-model="usersForAdd.name" id="add_users_name" placeholder="Enter Name">
	</div>
</div>
<div class="col-4">
	<label for="add_users_email" class="form-label text-uppercase fw-bold me-3">Email <span class="mandatory">*</span></label> <span v-if="v$.usersForAdd.email.$error" class="mandatory ms-3">Mandatory</span>
	<div>
		<input type="email" class="form-control"  v-model="usersForAdd.email" id="add_users_email" placeholder="Enter Email">
	</div>
</div>
<div class="col-4">
	<label for="add_users_password" class="form-label text-uppercase fw-bold me-3">Password <span class="mandatory">*</span></label><a href="#" class="cstooltip" data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote." tabindex="-1"><i class="ph ph-question"></i></a> <span v-if="v$.usersForAdd.password.$error" class="mandatory ms-3">Mandatory</span>
	<div>
		<input type="text" class="form-control"  v-model="usersForAdd.password" id="add_users_password" placeholder="Enter Password">
	</div>
</div>
</div>
<div class='row mb-4'>
<div class="col-4">
	<label for="add_users_department" class="form-label text-uppercase fw-bold me-3">Department</label><a href="#" class="cstooltip" data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote." tabindex="-1"><i class="ph ph-question"></i></a>
	<div>
		<input type="text" class="form-control"  v-model="usersForAdd.department" id="add_users_department" placeholder="Enter Department">
	</div>
</div>
<div class="col-4">
	<label for="add_users_employee_code" class="form-label text-uppercase fw-bold me-3">Employee Code <span class="mandatory">*</span></label><a href="#" class="cstooltip" data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote." tabindex="-1"><i class="ph ph-question"></i></a> <span v-if="v$.usersForAdd.employee_code.$error" class="mandatory ms-3">Mandatory</span>
	<div>
		<input type="text" class="form-control"  v-model="usersForAdd.employee_code" id="add_users_employee_code" placeholder="Enter Employee Code">
	</div>
</div>
</div>

					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-dark btn-sm" data-bs-dismiss="modal" @click="cancelAddEdit()">CLOSE</button>
						<button type="button" class="btn btn-dark btn-sm" @click="saveUsers()">SAVE CHANGES</button>
					</div>
				</div>
			</div>
		</div>
				<div class="modal fade" ref="readModal" id="readUsersModal" tabindex="-1" aria-labelledby="readUsersModalLabel" aria-hidden="true" v-if="readUsers">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="readUsersModalLabel">View Users</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class='row mb-4'>
<div class="col-4">
	<label class="form-label text-uppercase fw-bold m-0">Name</label>
	<div>
		<span v-if='readUsers.name'>{{ readUsers.name }}</span><span v-else><i>Not specified</i></span>
	</div>
</div><div class="col-4">
	<label class="form-label text-uppercase fw-bold m-0">Email</label>
	<div>
		<span v-if='readUsers.email'>{{ readUsers.email }}</span><span v-else><i>Not specified</i></span>
	</div>
</div><div class="col-4">
	<label class="form-label text-uppercase fw-bold m-0">Department</label>
	<div>
		<span v-if='readUsers.department'>{{ readUsers.department }}</span><span v-else><i>Not specified</i></span>
	</div>
</div></div>
<div class='row mb-4'>
<div class="col-4">
	<label class="form-label text-uppercase fw-bold m-0">Employee Code</label>
	<div>
		<span v-if='readUsers.employee_code'>{{ readUsers.employee_code }}</span><span v-else><i>Not specified</i></span>
	</div>
</div><div class="col-4">
	<label class="form-label text-uppercase fw-bold m-0">Status</label>
	<div>
				<span class="badge rounded-pill bg-success" v-if="readUsers.status == 1">ACTIVE</span>
		<span class="badge rounded-pill bg-danger" v-if="readUsers.status == 0">INACTIVE</span>
	</div>
</div></div>

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
function initialState(){
	return {
		id: 0,
		name:'',
email:'',
password:'',
department:'',
employee_code:'',

		action: ""
	};
}
function initialStateValidations() {
	return {
		name: {  required, minLengthValue: minLength(1) },
email: {  required, email },
password: {  required, minLengthValue: minLength(1) },
department: { minLengthValue: minLength(1) },
employee_code: {  required, minLengthValue: minLength(1) },

	}
}
export default {
	name: "Usersmaster",
	setup() {
		return {
			v$: useVuelidate()
		}
	},
	data(){
		return{
			dataprops: {
				id: 'users-list',
				class: 'a',
				base_url: '/api/users/',
				columns: [
					{ title: 'Name', property: 'name', sortable: true, },
{ title: 'Email', property: 'email', sortable: true, },
{ title: 'Department', property: 'department', },
{ title: 'Employee Code', property: 'employee_code', },

				],
				reload: false,
				search: 'simple',
				
			},
			addEditModal: null,
			readModal: null,
			usersForAdd: initialState(),
			readUsers: {},
			
		}
	},
	validations() {
		return {
			usersForAdd: initialStateValidations()
		};
	},
	methods: {
		cancelAddEdit(event){
			this.usersForAdd = initialState();
		},
		async saveUsers(event){
			var thisVar = this;
			const result = await this.v$.$validate();
			if (!result) {
				thisVar.showToast("Form validation failed. Please check.", "error", "bottom", 2000);
				console.log(thisVar.v$.$errors);
				return;
			}
			if( !thisVar.usersForAdd.action || thisVar.usersForAdd.action == "" )
				thisVar.usersForAdd.action = "details";
			$("#addUsersModal").modal('hide');
			this.showLoading("Saving ...");
			axios.post('/users/save', { users: thisVar.usersForAdd }).then(async function (response) {
				thisVar.closeSwal();
				var status = response.data.status;
				if( status == 1 ){
					// Ajax to submit
					thisVar.showToast('Users saved successfully', 'success', 'bottom', 3000);
					setTimeout(() => {
						thisVar.dataprops.reload = true;
					}, 1500);
					thisVar.usersForAdd = initialState();
					
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
		prepareEditUsers(users){
			this.usersForAdd = Object.assign({}, users);
			if( this.usersForAdd.is_percentage == 1 )
				this.usersForAdd.is_percentage = true;
			else
				this.usersForAdd.is_percentage = false;
			this.addEditModal.show();
		},
		viewUsers(users){
			this.readUsers = users;
			this.readModal.show();
		},
		toggleUsers(users, status){
			var thisVar = this;
			Swal.fire({
				icon: "question",
				html: "Do you really want to "+(status == 1?"reactivate":"deactivate")+" the Users - <br/>\""+users.email+"\"?",
				showCancelButton: true,
			}).then((result) => {
				if (result.isConfirmed) {
					thisVar.usersForAdd = users;
					thisVar.usersForAdd.status = status;
					thisVar.usersForAdd.action = "status";
					thisVar.saveUsers();
				}
			});
		}
	},
	async mounted() {
		this.addEditModal = new bootstrap.Modal(this.$refs.addEditModal, { backdrop: 'static', keyboard: false });
		this.readModal = new bootstrap.Modal(this.$refs.readModal, { backdrop: 'static', keyboard: false });
		
	}
}
</script>
