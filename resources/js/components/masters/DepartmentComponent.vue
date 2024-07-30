<template>
	<div id="department-main">
		<div class="row">
			<div class="col-sm-12">
				<div class="row mb-1">
					<div class="col-sm-7">
						<div class="d-flex align-items-center mb-2">
							<h4 class="m-0 me-4">DEPARTMENT</h4>
							<a id="add_department_btn" class="btn btn-success btn-sm" href="#" role="button" data-bs-toggle="modal" data-bs-target="#addDepartmentModal" v-if="['110', '111'].indexOf(all_permissions) >= 0">ADD</a>
						</div>
					</div>
				</div>
				<DataTableComponent :dataprops="dataprops" @view-object="viewDepartment" @edit-object="prepareEditDepartment" @toggle-object-status="toggleDepartment"></DataTableComponent>
			</div>
		</div>
				<div class="modal fade" ref="addEditModal" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addDepartmentModalLabel">ADD Department</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="cancelAddEdit"></button>
					</div>
					<div class="modal-body">
						<div class='row mb-4'>
<div class="col-4">
	<label for="add_department_title" class="form-label text-uppercase fw-bold me-3">Title <span class="mandatory">*</span></label><a href="#" class="cstooltip" data-tooltip="Allowed characters are A-Z, 0-9 and space, comma, full stop, underscore, dash and single quote." tabindex="-1"><i class="ph ph-question"></i></a> <span v-if="v$.departmentForAdd.title.$error" class="mandatory ms-3">Mandatory</span>
	<div>
		<input type="text" class="form-control"  v-model="departmentForAdd.title" id="add_department_title" placeholder="Enter Title">
	</div>
</div>
</div>

					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-dark btn-sm" data-bs-dismiss="modal" @click="cancelAddEdit()">CLOSE</button>
						<button type="button" class="btn btn-dark btn-sm" @click="saveDepartment()">SAVE CHANGES</button>
					</div>
				</div>
			</div>
		</div>
				<div class="modal fade" ref="readModal" id="readDepartmentModal" tabindex="-1" aria-labelledby="readDepartmentModalLabel" aria-hidden="true" v-if="readDepartment">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="readDepartmentModalLabel">View Department</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="cancelAddEdit"></button>
					</div>
					<div class="modal-body">
						<div class='row mb-4'>
<div class="col-4">
	<label class="form-label text-uppercase fw-bold m-0">Title</label>
	<div>
		<span v-if='readDepartment.title'>{{ readDepartment.title }}</span><span v-else><i>Not specified</i></span>
	</div>
</div><div class="col-4">
	<label class="form-label text-uppercase fw-bold m-0">Status</label>
	<div>
				<span class="badge rounded-pill bg-success" v-if="readDepartment.status == 1">ACTIVE</span>
		<span class="badge rounded-pill bg-danger" v-if="readDepartment.status == 0">INACTIVE</span>
	</div>
</div><div class="col-4">
	<label class="form-label text-uppercase fw-bold m-0">Created By</label>
	<div>
		<span v-if='readDepartment.creator?.name'>{{ readDepartment.creator?.name }}</span><span v-else><i>Not specified</i></span>
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
		title:'',
		action: ""
	};
}
function initialStateValidations() {
	return {
		title: {  required, minLengthValue: minLength(1) },

	}
}
export default {
	name: "Departmentmaster",
	props: ['current_user_id', 'all_permissions'],
	setup() {
		return {
			v$: useVuelidate()
		}
	},
	data(){
		return{
			dataprops: {
				id: 'department-list',
				class: 'a',
				base_url: '/api/department/',
				columns: [
					{ title: 'Title', property: 'title', sortable: true, },
{ title: 'Created By', property: 'creator.name', alt_value: 'Not Specified', },

				],
				data_to_send: { current_user_id: this.current_user_id } ,
				reload: false,
				search: 'simple',
				
			},
			addEditModal: null,
			readModal: null,
			currentUser: siteUserObject,
			departmentForAdd: initialState(),
			readDepartment: {},
			
		}
	},
	validations() {
		return {
			departmentForAdd: initialStateValidations()
		};
	},
	methods: {
		cancelAddEdit(event){
			this.departmentForAdd = initialState();
			this.v$.$reset();
		},
		async saveDepartment(event){
			var thisVar = this;
			const result = await this.v$.$validate();
			if (!result) {
				thisVar.showToast("Form validation failed. Please check.", "error", "bottom", 2000);
				console.log(thisVar.v$.$errors);
				return;
			}
			if( !thisVar.departmentForAdd.action || thisVar.departmentForAdd.action == "" )
				thisVar.departmentForAdd.action = "details";
			thisVar.departmentForAdd.created_by = thisVar.current_user_id;
			$("#addDepartmentModal").modal('hide');
			this.showLoading("Saving ...");
			axios.post('/department/save', { department: thisVar.departmentForAdd }).then(async function (response) {
				thisVar.closeSwal();
				var status = response.data.status;
				if( status == 1 ){
					// Ajax to submit
					thisVar.showToast('Department saved successfully', 'success', 'bottom', 3000);
					setTimeout(() => {
						thisVar.dataprops.reload = true;
					}, 1500);
					thisVar.departmentForAdd = initialState();
					thisVar.v$.$reset();
					
				}
				else
					thisVar.showErrors("Department could not be saved successfully", response.data.messages, "bottom", 3000);
			})
			.catch(function (error) {
				console.log(error);
				thisVar.closeSwal();
				thisVar.showToast("Department could not be saved successfully", "error", "bottom", 3000);
			});
		},
		prepareEditDepartment(department){
			this.departmentForAdd = Object.assign({}, department);
			if( this.departmentForAdd.is_percentage == 1 )
				this.departmentForAdd.is_percentage = true;
			else
				this.departmentForAdd.is_percentage = false;
			this.addEditModal.show();
		},
		viewDepartment(department){
			this.readDepartment = department;
			this.readModal.show();
		},
		toggleDepartment(department, status){
			var thisVar = this;
			Swal.fire({
				icon: "question",
				html: "Do you really want to "+(status == 1?"reactivate":"deactivate")+" the Department - <br/>\""+department.title+"\"?",
				showCancelButton: true,
			}).then((result) => {
				if (result.isConfirmed) {
					thisVar.departmentForAdd = department;
					thisVar.departmentForAdd.status = status;
					thisVar.departmentForAdd.action = "status";
					thisVar.saveDepartment();
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