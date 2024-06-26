<template>
	<div id="role-main">
		<div class="row">
			<div class="col-sm-12">
				<div class="row mb-1">
					<div class="col-sm-7">
						<div class="d-flex align-items-center mb-2">
							<h4 class="m-0 me-4">ROLE</h4>
							<a id="add_role_btn" class="btn btn-success btn-sm" href="#" role="button"
								data-bs-toggle="modal" data-bs-target="#addRoleModal">ADD</a>
						</div>
					</div>
				</div>
				<DataTableComponent :dataprops="dataprops" @view-object="viewRole" @edit-object="prepareEditRole"
					@toggle-object-status="toggleRole"></DataTableComponent>
			</div>
		</div>
		<div class="modal fade" ref="addEditModal" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel"
			aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addRoleModalLabel">ADD Role</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class='row mb-4'>
							<div class="col-4">
								<label for="add_role_title" class="form-label text-uppercase fw-bold me-3">Title <span
										class="mandatory">*</span></label><a href="#" class="cstooltip"
									data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote."
									tabindex="-1"><i class="ph ph-question"></i></a> <span
									v-if="v$.roleForAdd.title.$error" class="mandatory ms-3">Mandatory</span>
								<div>
									<input type="text" class="form-control" v-model="roleForAdd.title"
										id="add_role_title" placeholder="Enter Title">
								</div>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-dark btn-sm" data-bs-dismiss="modal"
							@click="cancelAddEdit()">CLOSE</button>
						<button type="button" class="btn btn-dark btn-sm" @click="saveRole()">SAVE CHANGES</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" ref="readModal" id="readRoleModal" tabindex="-1" aria-labelledby="readRoleModalLabel"
			aria-hidden="true" v-if="readRole">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="readRoleModalLabel">View Role</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class='row mb-4'>
							<div class="col-4">
								<label class="form-label text-uppercase fw-bold m-0">Title</label>
								<div>
									<span v-if='readRole.title'>{{ readRole.title }}</span><span v-else><i>Not
											specified</i></span>
								</div>
							</div>
							<div class="col-4">
								<label class="form-label text-uppercase fw-bold m-0">Status</label>
								<div>
									<span class="badge rounded-pill bg-success"
										v-if="readRole.status == 1">ACTIVE</span>
									<span class="badge rounded-pill bg-danger"
										v-if="readRole.status == 0">INACTIVE</span>
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
		title: '',
		action: ""
	};
}
function initialStateValidations() {
	return {
		title: { required, minLengthValue: minLength(1) },

	}
}
export default {
	name: "Rolemaster",
	props: ['current_user_id'],
	setup() {
		return {
			v$: useVuelidate()
		}
	},
	data() {
		return {
			dataprops: {
				id: 'role-list',
				class: 'a',
				base_url: '/api/role/',
				columns: [
					{ title: 'Title', property: 'title', sortable: true, },

				],
				data_to_send: { current_user_id: this.current_user_id },
				reload: false,
				search: 'simple',

			},
			addEditModal: null,
			readModal: null,
			currentUser: siteUserObject,
			roleForAdd: initialState(),
			readRole: {},

		}
	},
	validations() {
		return {
			roleForAdd: initialStateValidations()
		};
	},
	methods: {
		cancelAddEdit(event) {
			this.roleForAdd = initialState();
		},
		async saveRole(event) {
			var thisVar = this;
			const result = await this.v$.$validate();
			if (!result) {
				thisVar.showToast("Form validation failed. Please check.", "error", "bottom", 2000);
				console.log(thisVar.v$.$errors);
				return;
			}
			if (!thisVar.roleForAdd.action || thisVar.roleForAdd.action == "")
				thisVar.roleForAdd.action = "details";
			$("#addRoleModal").modal('hide');
			this.showLoading("Saving ...");
			axios.post('/role/save', { role: thisVar.roleForAdd }).then(async function (response) {
				thisVar.closeSwal();
				var status = response.data.status;
				if (status == 1) {
					// Ajax to submit
					thisVar.showToast('Role saved successfully', 'success', 'bottom', 3000);
					setTimeout(() => {
						thisVar.dataprops.reload = true;
					}, 1500);
					thisVar.roleForAdd = initialState();

				}
				else
					thisVar.showErrors("Role could not be saved successfully", response.data.messages, "bottom", 3000);
			})
				.catch(function (error) {
					console.log(error);
					thisVar.closeSwal();
					thisVar.showToast("Role could not be saved successfully", "error", "bottom", 3000);
				});
		},
		prepareEditRole(role) {
			this.roleForAdd = Object.assign({}, role);
			if (this.roleForAdd.is_percentage == 1)
				this.roleForAdd.is_percentage = true;
			else
				this.roleForAdd.is_percentage = false;
			this.addEditModal.show();
		},
		viewRole(role) {
			this.readRole = role;
			this.readModal.show();
		},
		toggleRole(role, status) {
			var thisVar = this;
			Swal.fire({
				icon: "question",
				html: "Do you really want to " + (status == 1 ? "reactivate" : "deactivate") + " the Role - <br/>\"" + role.title + "\"?",
				showCancelButton: true,
			}).then((result) => {
				if (result.isConfirmed) {
					thisVar.roleForAdd = role;
					thisVar.roleForAdd.status = status;
					thisVar.roleForAdd.action = "status";
					thisVar.saveRole();
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