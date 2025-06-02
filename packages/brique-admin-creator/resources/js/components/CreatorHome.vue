<template>
    <div class="container">
		<div class="py-2 text-center">
			<h3>Laravel-Vue CRUD generator forms</h3>
		</div>
	</div>
	<div class="container-fluid">
		<div class="row" v-if="create_mode == 1">
			<div class="col-6">
				<!-- First row -->
				<div class="row">
					<div class="col-12">
						<label for="databases" class="form-label small">Select Table</label>
						<div class="input-group mb-2">
							<select id="databases" class="form-select form-select-sm" v-model="tbl">
								<option v-for="tableName in tableNames" :key="tableName" :value="tableName">
									{{ tableName }}
								</option>
							</select>
							<button class="btn btn-primary btn-sm" type="button" @click="getVersions()">Get Version</button>
							<select id="allVersions" class="form-select form-select-sm" v-if="tableVersions?.length > 0" v-model="selected_table_version" 
								@change="version_name = (selected_table_version == 'new_version' ? '' : selected_table_version)">
								<option value="new_version">New Version</option>
								<optgroup label="Previous Versions">
									<option v-for="tableVersions in tableVersions">
										{{ tableVersions.key }}
									</option>
								</optgroup>
							</select>
							<button class="btn btn-outline-success btn-sm" type="button" v-if="tableVersions?.length > 0 ? showBTN : showBTN" @click="getNewTableStructure()">Get Structure</button>
						</div>
						<span class="fst-italic small" v-if="tableVersions?.length == 0">Note: The selected table version is not available. Kindly click on "Get Structure" to proceed.</span>
					</div>
					<div class="col-6">
						<label for="databases" class="form-label small">Navigation Group</label>
						<select class="form-select form-select-sm" v-model="navigation_group">
							<optgroup label="Select one">
								<option value="nav_open">Top Menu</option>
								<option value="nav_masters">Masters Section</option>
							</optgroup>
						</select>
					</div>
				</div>
				<!-- First row -->
			</div>
			<div class="col-6">
				<div class="row">
					<div class="col-6">
						<label for="objectName" class="form-label small">Enter Object Name</label>
						<div class="mb-3">
							<input type="text" class="form-control form-control-sm" id="objectName" v-model="gen_resource_name" :disabled="!showField">
						</div>
						<p class="mb-0 small">Rules: This name is used to create a Model. It should not contain any special characters. Only Characters are allowed.</p>
					</div>
					<div class="col-6">
						<label for="objectName" class="form-label small">Enter Object Label</label>
						<div class="mb-3">
							<input type="text" class="form-control form-control-sm" id="objectLabel" v-model="gen_object_label" :disabled="!showField">
						</div>
						<p class="mb-1 small">Rules: This name is used to represent a resource in the menu and the object screens and buttons. It can contain any characters. Eg. For an Article, the Object Label can be "Magazine Article".</p>
					</div>
				</div>
			</div>
		</div>
		<div class="clear mt-3" v-if="(columns && columns.length > 0) || tbl == 'addnew'">
			<ul class="nav nav-tabs" id="myTab" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="objSet-tab" data-bs-toggle="tab" data-bs-target="#objSet-tab-pane" type="button" role="tab" aria-controls="objSet-tab-pane" aria-selected="true">OBJECT SETTINGS</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">ADD/EDIT</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="view-tab" data-bs-toggle="tab" data-bs-target="#view-tab-pane" type="button" role="tab" aria-controls="view-tab-pane" aria-selected="false">VIEW</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-tab-pane" type="button" role="tab" aria-controls="list-tab-pane" aria-selected="false">LIST TABLE</button>
				</li>
			</ul>
			<div class="tab-content mb-5 pb-3" id="myTabContent">
				<div class="tab-pane fade show active p-3" id="objSet-tab-pane" role="tabpanel" aria-labelledby="objSet-tab" tabindex="0">
					<h5 class="m-0">Object Settings</h5>
					<!-- Install Action -->
					<div class="row mb-4">
						<label class="col-form-label col-2 text-end text-nowrap">Is Install Actions:</label>
						<div class="form-check col-1 py-2">
							<input class="form-check-input" type="radio" value="1" 
							id="installActionYes"  v-model="auth_required">
							<label class="form-check-label" for="installActionYes">Yes</label>
						</div>
						<div class="form-check col-1 py-2">
							<input class="form-check-input" type="radio" value="0" id="installActionNo"  v-model="auth_required">
							<label class="form-check-label" for="installActionNo">No</label>
						</div>
					</div>
					<!-- Install Action -->
					<!-- Event settings -->
					<div class="row mb-4">
						<label class="col-form-label col-2 text-end text-nowrap">Fire Event:</label>
						<div class="form-check col-1 py-2">
							<input class="form-check-input" type="checkbox" value="before_event" id="before_event"  v-model="before_event" disabled>
							<label class="form-check-label" for="before_event">Before Save</label>
						</div>
						<div class="form-check col-1 py-2">
							<input class="form-check-input" type="checkbox" value="after_event" id="after_event"  v-model="after_event">
							<label class="form-check-label" for="after_event">After Save</label>
						</div>
					</div>
					<!-- Event settings -->
				</div>
				<!-- Add setting start -->
				<div class="tab-pane fade p-3" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
					<div class="row">
						<div class="col-lg-6">
							<small class="mb-2">Please note that fields marked with an asterisk (<span class="text-danger">*</span>) are mandatory and must be completed to ensure successful form submission. <strong>None: The row with a grey background is not included in the table.</strong></small>
							<div class="row">
								<div class="col-1 border text-center align-content-around">&nbsp;</div>
								<div class="col-1 border text-center align-content-around">Use</div>
								<div class="col-4 border text-center align-content-around">Field</div>
								<div class="col-2 border text-center align-content-around">Type</div>
								<div class="col-4 border text-center py-1 align-content-around">Actions</div>
							</div>	
							<draggable :list="columns" draggable=".column"  handle=".handle">
								<template v-for="(column, index) in columns" :key="column.name">
									<!-- <template v-if="!['id', 'created_at', 'updated_at', 'deleted_at'].includes(column.name)"> -->
									<div class="row column" v-if="column?.type == 'column'" :class="selected_column == column ? 'bg-danger bg-opacity-50' : '' || ['id', 'created_at', 'updated_at', 'deleted_at'].includes(column.name) ? 'bg-secondary bg-opacity-50' : ''">
										<div class="col-1 border align-content-around text-center">
											<i class="ph ph-list handle text-dark fs-6 handle" v-if="!['id', 'created_at', 'updated_at', 'deleted_at'].includes(column.name)"></i>
										</div>
										<div class="col-1 border align-content-around">
											<div class="d-flex justify-content-center">
												<div v-if="!['id', 'created_at', 'updated_at', 'deleted_at'].includes(column.name)">
													<input class="form-check-input" type="checkbox" v-model="column.use_in_form" :id="'form'+column.name"/>
												</div>
											</div>
										</div>
										<div class="col-4 border align-content-around d-flex justify-content-between align-items-center">
											<span class="me-auto">{{column.name}}</span>
											<span class="text-danger" v-if="column?.form?.required">*</span>
										</div>
										<div class="col-2 border align-content-around text-break text-center p-0">{{column.form.type}}</div>
										<div class="col-4 border py-1 text-center align-content-around">
											<template v-if="['id', 'created_at','updated_at', 'deleted_at'].indexOf(column.name) < 0">
												<a class="btn btn-primary btn-sm action-btn me-2" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample" @click="this.selected_column = column;">Configure</a>
												<a class="btn btn-sm action-btn" :class="!column.show_divider ? 'btn-outline-dark': 'btn-outline-danger' " role="button" @click="addDivider(column)" v-if="index !== columns.length - 1">Add Next Row</a>
											</template>
										</div>
									</div>
									<div v-else class="row">
										<div class="col-12 border border-danger p-1 d-flex align-items-center justify-content-center gap-3">
											<label class="">Next Row</label>
											<button class="btn btn-outline-danger btn-sm" @click="removeDivider(column)">Remove</button>
										</div>
									</div>
									<!-- </template> -->
								</template>
							</draggable>
						</div>
						<div class="col-lg-6">
							<div class="d-flex align-items-center justify-content-between">
								<h3 class="px-2">Add Settings</h3>
								<button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addEditForm" @click="this.addEditForm = true;">Preview Form</button>
							</div>
							<!-- Popup or different Tab -->
							<div class="row mb-4 px-4">
								<label class="form-label col-12 fw-medium m-0 p-0 fs-5">Display as:</label>
								<div class="form-check col-6 py-2">
									<input class="form-check-input" type="radio" value="popup" id="form_as_popup"  v-model="form_mode">
									<label class="form-check-label" for="form_as_popup">Show Form as Popup <br><small class="fst-italic">(Note: Recommend for small masters.)</small></label>
								</div>
								<div class="form-check col-6 py-2">
									<input class="form-check-input" type="radio" value="different_page" id="form_as_different_page"  v-model="form_mode">
									<label class="form-check-label" for="form_as_different_page">Show Form as Different Page</label>
								</div>
							</div>
							<!-- Popup or different Tab -->
							<!-- horizontal vertical selecetion -->
							<div class="row px-4">
								<label for="frm_label_placement" class="form-label col-12 m-0 p-0 fs-5 fw-medium">Label Placement:</label>
								<div class="form-check col-3 py-2">
									<input class="form-check-input" type="radio" value="vertical" id="label_placement_vertical" v-model="form_label_placement" @change="adjustSetColumn">
									<label class="form-check-label" for="label_placement_vertical">Vertical</label>
								</div>
								<div class="form-check col-3 py-2">
									<input class="form-check-input" type="radio" value="horizontal" id="label_placement_horizontal" v-model="form_label_placement" @change="adjustSetColumn">
									<label class="form-check-label" for="label_placement_horizontal">Horizontal</label>
								</div>
							</div>
							<!-- horizontal vertical selecetion -->
							<!-- example -->
							<div class="row mb-4 px-4">
								<div class="col-12 p-0 mb-2">Example :</div>
								<div class="col-12 bg-secondary bg-opacity-25 p-2 rounded-3" v-if="form_label_placement === 'vertical'">
									<div class="">
										<label for="formGroupExampleInput" class="form-label text-nowrap">Label</label>
										<input type="text" class="form-control" id="formGroupExampleInput" placeholder="placeholder" readonly>
									</div>
								</div>
								<div class="col-12 bg-secondary bg-opacity-25 p-2 rounded-3" v-if="form_label_placement === 'horizontal'">
									<div class="row">
										<label for="inputLabel" class="col-sm-3 col-form-label text-nowrap">Label</label>
										<div class="col-sm-9"><input type="text" class="form-control" id="inputLabel" placeholder="placeholder" readonly></div>
									</div>
								</div>
							</div>
							<!-- example -->
						</div>
					</div>
				</div>
				<!-- Add setting End -->
				<!-- View setting start -->
				<div class="tab-pane fade p-3" id="view-tab-pane" role="tabpanel" aria-labelledby="view-tab" tabindex="0">
					<div class="row">
						<div class="col-lg-6">
							<div class="row">
								<div class="col-1 border text-center align-content-around">&nbsp;</div>
								<div class="col-1 border text-center align-content-around">View</div>
								<div class="col-4 border text-center align-content-around">Field</div>
								<div class="col-2 border text-center align-content-around">Type</div>
								<div class="col-4 border text-center py-1 align-content-around">Actions</div>
							</div>
							<draggable :list="columns" draggable=".column" handle=".handle">
								<template  v-for="(column, index) in columns">
									<div class="row column" v-if="column?.type == 'column'" :class="selected_column == column ? 'bg-danger bg-opacity-50' : ''">
										<div class="col-1 border align-content-around text-center">
											<i class="ph ph-list handle text-dark fs-6"></i>
										</div>
										<div class="col-1 border align-content-around">
											<div class="d-flex justify-content-center">
												<div v-if="column?.table_view">
													<input class="form-check-input" type="checkbox" v-model="column.table_view.use_in_view" :id="'view'+column.name"/>
												</div>
											</div>
										</div>
										<div class="col-4 border align-content-around align-items-center">
											{{column.name}}
										</div>
										<div class="col-2 border align-content-around text-break text-center p-0">{{column.table_view.type}}</div>
										<div class="col-4 border py-1 text-center align-content-around">
											<a class="btn btn-primary btn-sm action-btn me-2" data-bs-toggle="offcanvas" href="#offcanvasTableView" role="button" aria-controls="offcanvasTableView" @click="this.selected_column = column;">Configure</a>
											<a class="btn btn-sm action-btn" :class="!column.show_divider ? 'btn-outline-dark': 'btn-outline-danger' " role="button" v-if="['updated_at'].indexOf(column.name) < 0" @click="addDivider(column)">Add Next Row</a>
										</div>
									</div>
									<div v-else class="row">
										<div class="col-12 border border-danger p-1 d-flex align-items-center justify-content-center gap-3">
											<label class="">Next Row</label>
											<button class="btn btn-outline-danger btn-sm" @click="removeDivider(column)">Remove</button>
										</div>
									</div>
								</template>
							</draggable>
						</div>
						<div class="col-lg-6">
							<div class="d-flex align-items-center justify-content-between">
								<h3 class="">View Settings</h3>
								<button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#addEditForm" @click="this.viewForm = true;">Preview Form</button>
							</div>
							<!-- Popup or different Tab -->
							<div class="row mb-4 px-4">
								<label class="form-label col-12 fw-medium m-0 p-0 fs-5">Display as:</label>
								<div class="form-check col-6 py-2">
									<input class="form-check-input" type="radio" value="popup" id="view_form_as_popup"  v-model="view_mode">
									<label class="form-check-label" for="view_form_as_popup">Show Form as Popup <br><small class="fst-italic">(Note: Recommend for small masters.)</small></label>
								</div>
								<div class="form-check col-6 py-2">
									<input class="form-check-input" type="radio" value="different_page" id="view_form_as_different_page"  v-model="view_mode">
									<label class="form-check-label" for="view_form_as_different_page">Show Form as Different Page</label>
								</div>
							</div>
							<!-- Popup or different Tab -->
							<!-- horizontal vertical selecetion -->
							<div class="row px-4">
								<label for="frm_label_placement" class="form-label col-12 fw-medium m-0 p-0 fs-5">View Label Placement:</label>
								<div class="form-check col-3 py-2">
									<input class="form-check-input" type="radio" name="view_label_placement" value="vertical" id="view_label_placement_vertical"  v-model="view_label_placement" @change="viewAdjustSetColumn">
									<label class="form-check-label" for="view_label_placement_vertical">Vertical</label>
								</div>
								<div class="form-check col-3 py-2">
									<input class="form-check-input" type="radio" name="view_label_placement" value="horizontal" id="view_label_placement_horizontal"  v-model="view_label_placement" @change="viewAdjustSetColumn">
									<label class="form-check-label" for="view_label_placement_horizontal">Horizontal</label>
								</div>
							</div>
							<!-- horizontal vertical selecetion -->
							<!-- example -->
							<div class="row mb-4 px-4">
								<div class="col-12 p-0 mb-2">Example :</div>
								<div class="col-12 bg-secondary bg-opacity-25 p-2 rounded-3" v-if="view_label_placement === 'vertical'">
									<div class="">
										<label for="formGroupExampleInput" class="form-label text-uppercase text-nowrap m-0">Label</label>
										<div><span>Example</span></div>
									</div>
								</div>
								<div class="col-5 offset-3 bg-secondary bg-opacity-25 p-2 rounded-3" v-if="view_label_placement === 'horizontal'">
									<div class="row">
										<label for="inputLabel" class="col-sm-3 form-label text-uppercase text-nowrap m-0 py-2">Label</label>
										<div class="col-sm-9 py-2"><span>Example</span></div>
									</div>
								</div>
							</div>
							<!-- example -->
						</div>
					</div>
				</div>
				<!-- View setting End -->
				<!-- List setting Start -->
				<div class="tab-pane fade p-3" id="list-tab-pane" role="tabpanel" aria-labelledby="list-tab" tabindex="0">	
					<div class="row">
						<div class="col-lg-6">
							<!-- Download -->
							<div class="row mb-2"> 
								<div class="col-1">&nbsp;</div>
								<div class="form-check col-6">
									<label :for="'support_export_'+support_download" class="form-label text-end">Support Download</label>
									<input type="checkbox" class="form-check-input" :id="'support_export_'+support_download" v-model="support_download">
								</div>
							</div>
							<!-- Download -->
							<div class="row">
								<div class="col-2 border text-center align-content-around p-0">Visible</div>
								<div class="col-2 border text-center align-content-around">Download</div>
								<div class="col-4 border text-center align-content-around">Field</div>
								<div class="col-2 border text-center align-content-around">Type</div>
								<div class="col-2 border text-center py-1 align-content-around">Actions</div>
							</div>
							<template v-for="(column, index) in columns">
								<template v-if="column.type != 'divider'">
									<div class="row column" :class="selected_column == column ? 'bg-danger bg-opacity-50' : ''">
										<div class="col-2 border align-content-around">
											<div class="d-flex justify-content-center">
												<div v-if="column?.table_view">
													<input class="form-check-input" type="checkbox" v-model="column.table_view.visible_in_table" :id="'view'+column.name">
												</div>
											</div>
										</div>
										<div class="col-2 border align-content-around">
											<div class="d-flex justify-content-center">
												<div v-if="column?.table_view">
													<input class="form-check-input" type="checkbox" :id="'export'+column.name" :disabled="!support_download" :checked="support_download && column.table_view?.use_in_download === true" v-model="column.table_view.use_in_download"/>
												</div>
											</div>
										</div>
										<div class="col-4 border align-content-around align-items-center">{{column.name}}</div>
										<div class="col-2 border align-content-around text-break text-center p-0">{{column.table_view.type}}</div>
										<div class="col-2 border py-1 text-center align-content-around">
											<a class="btn btn-primary btn-sm action-btn" data-bs-toggle="offcanvas" href="#offcanvasTableList" role="button" aria-controls="offcanvasTableList" @click="this.selected_column = column;">Configure</a>
										</div>
									</div>
								</template>
							</template>
						</div>
						<div class="col-lg-6"></div>
					</div>
				</div>
				<!-- List setting End -->
			</div>
		</div>

		<div class="fixed-bottom py-3 bg-light border-top" v-if="(columns && columns.length > 0) || tbl == 'addnew'"	>
			<div class="container">
				<div class="row">
					<div class="col-12 d-flex flex-row justify-content-center gap-2">
						<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateModal" @click="modal_type = 'save'">Save</button>
						<button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#generateModal" @click="modal_type = 'save&export'">Save & Export</button>
						<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateModal" @click="modal_type = 'generate'">Generate</button>
					</div>
				</div>
			</div>
		</div>	
	</div>
    
	<!-- Modal for Preview Form -->
	<div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="addEditForm" tabindex="-1" aria-labelledby="addEditFormLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="addEditFormLabel" v-if="addEditForm">Add/Edit Preview Form</h1>
					<h1 class="modal-title fs-5" id="addEditFormLabel" v-if="viewForm">View Preview Form</h1>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="addEditForm = false; viewForm = false;"></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<template v-for="column in columns">
							<template  v-if="column?.name">
								<div class="mb-3" :class="['fileupload', 'richeditor'].includes(column.form.type) ? 'col-12' : 'col'" v-if="addEditForm && !['id', 'created_at', 'updated_at', 'deleted_at'].includes(column.name)">
									<label for="disabledTextInput" class="form-label">{{ column.name }}</label>
									<input type="text" id="disabledTextInput" class="form-control" :placeholder="column.form.type +' field'" disabled>
								</div>
								<div class="mb-3 col" v-if="viewForm && column.table_view.use_in_view">
									<label for="disabledTextInput" class="form-label mb-1">{{ column.name }}</label>
									<div><span>{{ column.name }} value</span></div>
								</div>
							</template>
							<template  v-else>
								<div class="col-12"></div>
							</template>
						</template>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal" @click="addEditForm = false; viewForm = false;">Close</button>
				</div>
			</div>
		</div>
	</div>
	<!-- Modal for Preview Form -->

	<!-- Modal for Buttons -->
	<div class="modal fade" id="generateModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="generateModal" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h1 class="modal-title fs-5" id="generateModal">
						{{ modal_type === 'save' ? 'Save' : modal_type === 'saveExport' ? 'Save & Export' : 'Generate' }}
					</h1>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<label class="col-form-label">Version Name:</label>
					<div class="mb-3">
						<input type="text" class="form-control" v-model="version_name" placeholder="Enter version name">
					</div>
					<div v-if="modal_type == 'generate'">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" :id="'model'+make_model" v-model="make_model">
							<label class="form-check-label" :for="'model'+make_model">Model</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" :id="'resource'+make_resource" v-model="make_resource">
							<label class="form-check-label" :for="'resource'+make_resource">Resource</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" :id="'component'+make_component" v-model="make_component">
							<label class="form-check-label" :for="'component'+make_component">Component</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" :id="'controller'+make_controller" v-model="make_controller">
							<label class="form-check-label" :for="'controller'+make_controller">Controller</label>
						</div>
					</div>
				</div>
				<div class="modal-footer flex-nowrap">
					<button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary w-100"  data-bs-dismiss="modal" v-if="modal_type == 'save'" @click="saveInFile('save')">Save</button>
					<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" v-if="modal_type == 'save&export'" @click="saveInFile('saveAndExport')">Save & Export</button>
					<button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal" v-if="modal_type == 'generate'" @click="saveInFile('generate')">Generate</button>
				</div>
			</div>
		</div>
	</div>
	<!-- Modal for Buttons -->

	<!-- Add/Edit Offcanvas -->
    <div class="offcanvas offcanvas-end w-50" data-bs-backdrop="static" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
		<div class="offcanvas-header border-bottom shadow-sm">
			<h5 class="offcanvas-title text-capitalize" id="offcanvasExampleLabel">Configure Field: {{selected_column.name}}</h5>
			<div class="">
				<button type="button" class="btn btn-warning me-2" @click="saveCondition()">Save</button>
				<button type="button" class="btn btn-outline-danger" data-bs-dismiss="offcanvas" aria-label="Close" @click="cancelCondition()">Cancel</button>
			</div>
		</div>
		<div class="offcanvas-body">
			<!-- Required -->
			<div class="row mb-2" v-if="selected_column && selected_column.form">
				<label :for="'required'+selected_column.form.required" class="col-form-label col-3 text-end">Required</label>
				<div class="col-4 py-2">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" :id="'required'+selected_column.form.required" v-model="selected_column.form.required">
					</div>
				</div>
			</div>
			<!-- Required -->
			<!-- Unique Column -->
			<div class="row mb-2" v-if="selected_column">
				<label :for="'unique'+selected_column.is_unique" class="col-form-label col-3 text-end">Is unique column</label>
				<div class="col-4 py-2">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" :id="'unique'+selected_column.is_unique" v-model="selected_column.is_unique">
					</div>
				</div>
			</div>
			<!-- Unique Column -->
			<!-- Label -->
			<div class="row mb-2 align-items-center" v-if="selected_column && selected_column.form">
				<label :for="'label'+selected_column.name" class="col-form-label col-3 text-end">Label</label>
				<div class="col-4">
					<input type="text" class="form-control form-control-sm" :id="'label'+selected_column.name" v-model="selected_column.form.label">
				</div>
			</div>
			<!-- Label -->
			<!-- hint -->
			<div class="row mb-2 align-items-center" v-if="selected_column && selected_column.name && selected_column.form">
				<label :for="'hint'+selected_column.name" class="col-form-label col-3 text-end">Hint/Placeholder</label>
				<div class="col-4">
					<input type="text" class="form-control form-control-sm" :id="'hint'+selected_column.name" v-model="selected_column.form.hint">
				</div>
			</div>
			<!-- hint -->
			<!-- Tooltip -->
			<div class="row mb-2 align-items-center" v-if="selected_column && selected_column.form">
				<label :for="'tooltip'+selected_column.name" class="col-form-label col-3 text-end">Tooltip</label>
				<div class="col-4">
					<input type="text" class="form-control form-control-sm" :id="'tooltip'+selected_column.name" v-model="selected_column.form.tooltip">
				</div>
			</div>
			<!-- Tooltip -->
			<!-- Type -->
			<div class="row mb-2 align-items-center" v-if="selected_column && selected_column.form">
				<label :for="'frm_type'+selected_column.name" class="col-form-label col-3 text-end">Type</label>
				<div class="col-4">
					<select class="form-select form-select-sm" v-model="selected_column.form.type" @change="adjustTableType(selected_column.form.type)">
						<option value="text">Text</option>
						<option value="textarea">Textarea</option>
						<option value="richeditor">Rich Editor</option>
						<option value="email">Email</option>
						<option value="numeric">Numeric</option>
						<option value="decimal">Decimal</option>
						<option value="password">Password</option>
						<option value="telephone">Telephone</option>
						<option value="url">URL</option>
						<option value="date">Date</option>
						<option value="time">Time</option>
						<option value="monthpicker">Month Picker</option>
						<option value="checkbox">Checkbox</option>
						<option value="textoptions">Text Options</option>
						<option value="relation">Relation</option>
						<option value="fileupload">File Upload</option>
					</select>
				</div>
			</div>
			<!-- Type -->
			<!-- auto generate -->
			<div class="row mb-2" v-if="selected_column.form && selected_column?.form.type == 'text'">
				<label :for="'autogenerate_'+selected_column.form.autogenerate" class="col-form-label col-3 text-end">Autogenerate Content</label>
				<div class="col-4 py-2">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" :id="'autogenerate_'+selected_column.form.autogenerate" v-model="selected_column.form.autogenerate">
					</div>
				</div>
			</div>
			<!-- auto generate -->
			<!-- Number of Rows -->
			<div class="row mb-2 align-items-center" v-if="selected_column.form?.type == 'textarea'">
				<label class="col-form-label col-3 text-end">No. of Rows</label>
				<div class="col-3">
					<input type="number" class="form-control form-control-sm" v-model="selected_column.form.no_of_rows">
				</div>
			</div>
			<!-- Number of Rows -->
			<!-- Select options -->
			<div class="row g-2 mb-2" v-if="selected_column?.use_in_form && ['textoptions','relation'].indexOf(selected_column.form?.type)>=0">
				<div v-if="selected_column.form?.type=='textoptions'" class="col-9 offset-3">
					<input type="button" class="btn btn-primary btn-sm" @click="addOption(selected_column, 1)" value="Add Option">
					<div v-for="(option,index) of selected_column.options" :key="index" class="input-group my-2">
						<input type="text" class="form-control" v-model="option.key" placeholder="Enter Option Key">
						<input type="text" class="form-control" v-model="option.value" placeholder="Enter Option Value">
						<button type="button" class="btn btn-danger btn-sm" @click="removeOption(option, selected_column, 1)">X</button>
					</div>
				</div>
				<!-- <div v-if="selected_column.form?.type=='relation'" class="d-flex flex-row col-6 offset-3 gap-2">
					<div class="form-floating">
						<input type="text" class="form-control" :id="'relation'+selected_column.name" v-model="selected_column.relation.method">
						<label :for="'relation'+selected_column.name">Relation Method</label>
					</div>
					<div class="form-floating">
						<input type="text" class="form-control" :id="'relationttl'+selected_column.name" v-model="selected_column.relation.title_attribute">
						<label :for="'relationttl'+selected_column.name">Title Attribute</label>
					</div>
				</div> -->
				<div  v-if="['relation'].indexOf(selected_column.form.type) >= 0" class="col-9 gap-2 offset-3">
					<div class="row gy-2">
						<div class="form-group col-12">
							<label :for="'relation'+selected_column.name" class="col-form-label">Related Model</label>
							<div class="input-group">
								<span class="input-group-text">\App\Models\</span>
								<input type="text" class="form-control" :id="'relation'+selected_column.name" v-model="selected_column.relation.related_model">
							</div>
						</div>
						<div class="form-group col-6">
							<label :for="'relationId'+selected_column.name" class="col-form-label">ID Attribute</label>
							<input type="text" class="form-control" :id="'relationId'+selected_column.name" v-model="selected_column.relation.id_attribute">
						</div>
						<div class="form-group col-6">
							<label :for="'relationTitle'+selected_column.name" class="col-form-label">Title Attribute</label>
							<input type="text" class="form-control" :id="'relationTitle'+selected_column.name" v-model="selected_column.relation.title_attribute">
						</div>
					</div>
				</div>
			</div>
			<div class="row mb-2" v-if="selected_column?.use_in_form && ['relation', 'textoptions'].indexOf(selected_column.form?.type)>=0 && selected_column.form?.type=='relation'">
				<div class="col-3 text-end">
					<label class="col-form-label">Need Default Option</label>
				</div>
				<div class="col-9 pt-2">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" v-model="selected_column.frm_popup_select_default_option" :id="'defaultoption'+selected_column.name">
					</div>
				</div>
				<div class="offset-3 col-9" v-if="selected_column.frm_popup_select_default_option">
					<label class="col-form-label text-end">Enter the default option's <b>id</b> and <b>value</b></label>
				</div>
				<div v-if="selected_column.frm_popup_select_default_option" class="d-flex flex-row offset-3 col-6 gap-2">
					<div class="form-floating">
						<input type="text" class="form-control" :id="'popup_selectdefid'+selected_column.name" v-model="selected_column.frm_popup_select_default_option_id">
						<label :for="'popup_selectdefid'+selected_column.name">Option ID</label>
					</div>
					<div class="form-floating">
						<input type="text" class="form-control" :id="'popup_selectdefval'+selected_column.name" v-model="selected_column.frm_popup_select_default_option_value">
						<label :for="'popup_selectdefval'+selected_column.name">Option Value</label>
					</div>
				</div>
			</div>
			<div class="row mb-2" v-if="showError">
				<div class="col-3">&nbsp;</div>
				<div class="col-9">
					<span class="text-danger">Error: The above field must not be empty.</span>
				</div>
			</div>
			<div class="row mb-2 align-items-center" v-if="selected_column?.use_in_form && selected_column.form?.type=='relation' || selected_column.form?.type =='textoptions'">
				<label class="col-form-label col-3 text-end">Display as</label>
				<div class="col-3">
					<select class="form-select form-select-sm" v-model="selected_column.form.options_display_as">
						<option value="radiobtns">Radio buttons</option>
						<option value="dropdown">Dropdown</option>
						<option value="popup">Popup/Coming Soon</option>
					</select>
				</div>
			</div>
			<!-- Select options -->
			<!-- min and max value -->
			<div class="row mb-2" v-if="selected_column && ['numeric','decimal'].includes(selected_column.form?.type)">
				<label for="" class="col-form-label col-3 text-end">Configure Value</label>
				<div class="col-9">
					<div class="form-check my-2" v-if="selected_column && selected_column.form">
						<input type="checkbox" class="form-check-input" :id="'restriction'+selected_column.form.value_restriction" v-model="selected_column.form.value_restriction">
						<label :for="'restriction'+selected_column.form.value_restriction">Use value restriction</label>
					</div>
					<div class="row d-flex flex-row">
						<div class="col-4">
							<div class="form-check">
								<input type="checkbox" class="form-check-input" :id="'restriction'+selected_column.form.use_min_value" v-model="selected_column.form.use_min_value" :disabled="!selected_column.form?.value_restriction">
								<label :for="'restriction'+selected_column.form.use_min_value">Use min value</label>
							</div>
						</div>
						<div class="col-4">
							<div class="form-check">
								<input type="checkbox" class="form-check-input" :id="'restriction'+selected_column.form.use_max_value" v-model="selected_column.form.use_max_value" :disabled="!selected_column.form?.value_restriction">
								<label :for="'restriction'+selected_column.form.use_max_value">Use max value</label>
							</div>
						</div>
					</div>
					<div class="row d-flex flex-row mb-3">
						<div class="col-4">
							<div class="form-floating">
								<input type="number" class="form-control form-control-sm" :id="'min_value'+selected_column.name" v-model="selected_column.form.min_value" min="0" :disabled="!selected_column.form?.use_min_value">
								<label :for="'min_value'+selected_column.name">Min Value</label>
							</div>
						</div>
						<div class="col-4">
							<div class="form-floating">
								<input type="number" class="form-control form-control-sm" :id="'max_value'+selected_column.name" v-model="selected_column.form.max_value" max="100" :disabled="!selected_column.form?.use_max_value">
								<label :for="'max_value'+selected_column.name">Max Value</label>
							</div>
						</div>
						<span class="text-danger" v-if="selected_column.form.use_min_value && selected_column.form.use_max_value &&(selected_column.form.min_value > selected_column.form.max_value)">The minimum value must be less than the maximum value.</span>
					</div>
				</div>
			</div>
			<!-- min and max value -->
			<!-- Length -->
			<div class="row" v-if="selected_column && ['text', 'textarea'].includes(selected_column.form?.type)" :class="selected_column.use_length?'':'mb-2'">
				<label for="" class="col-form-label col-3 text-end" >Configure Length</label>
				<div class="col-9">
					<div class="form-check my-2" v-if="selected_column && selected_column.form">
						<input type="checkbox" class="form-check-input" :id="'restriction'+selected_column.form.length_restriction" v-model="selected_column.form.length_restriction">
						<label :for="'restriction'+selected_column.form.length_restriction">Use length restriction</label>
					</div>
					<div class="row d-flex flex-row mb-2">
						<div class="col-4">
							<div class="form-check">
								<input type="radio" class="form-check-input" id="restriction_fixed" name="flexRadioDefault" value="fixed" v-model="selected_column.form.use_mode_length" :disabled="!selected_column.form?.length_restriction">
								<label for="restriction_fixed">Use fixed length</label>
							</div>
						</div>
						<div class="col-4">
							<div class="form-check">
								<input type="radio" class="form-check-input" id="restriction_range" name="flexRadioDefault" value="range" v-model="selected_column.form.use_mode_length" :disabled="!selected_column.form?.length_restriction">
								<label for="restriction_range">Use range length</label>
							</div>
						</div>
					</div>
					<div class="row d-flex flex-row mb-3">
						<div class="col-3">
							<div class="form-floating">
								<input type="number" class="form-control form-control-sm" :id="'txtlen'+selected_column.name" v-model="selected_column.form.length" min="0" max="100" :disabled="selected_column.form?.use_mode_length != 'fixed'">
								<label :for="'txtlen'+selected_column.name">Length</label>
							</div>
						</div>
						<div class="col-1">&nbsp;</div>
						<div class="col-3">
							<div class="form-floating">
								<input type="number" class="form-control form-control-sm" :id="'txtmin_length'+selected_column.name" v-model="selected_column.form.min_length" min="0" :disabled="selected_column.form?.use_mode_length != 'range' || !selected_column.form.length_restriction">
								<label :for="'txtmin_length'+selected_column.name">Min Length</label>
							</div>
						</div>
						<div class="col-3">
							<div class="form-floating">
								<input type="number" class="form-control form-control-sm" :id="'txtmax_length'+selected_column.name" v-model="selected_column.form.max_length" max="100" :disabled="selected_column.form?.use_mode_length != 'range' || !selected_column.form.length_restriction">
								<label :for="'txtmax_length'+selected_column.name">Max Length</label>
							</div>
						</div>
						<span class="text-danger" v-if="selected_column.form.min_length > selected_column.form.max_length">The minimum value must be less than the maximum value.</span>
					</div>
				</div>
			</div>
			<!-- Length -->
			<!-- Precision -->
			<div class="row mb-2" v-if="selected_column.form?.type == 'decimal'">
				<label class="col-form-label col-3 text-end" >Precision</label>
				<div class="col-4 d-flex align-items-center gap-3">
					<input type="number" class="form-control form-control-sm" v-model="selected_column.form.precision">
					<div class="text-nowrap">Example : 10.<span class="fw-bold">389</span> </div>
				</div>
			</div>
			<!-- Precision -->
			<!-- Password Rule -->
			<div class="row" v-if="selected_column.form?.type == 'password'">
				<div class="row mb-2">
					<div class="col-3">&nbsp;</div>
					<div class="col-6 p-2 px-4">
						<div class="form-check">
							<input type="checkbox" class="form-check-input" :id="'atleast_one_caps'+selected_column.form.atleast_one_caps" v-model="selected_column.form.atleast_one_caps">
							<label :for="'atleast_one_caps'+selected_column.form.atleast_one_caps" class="form-check-label text-end">Atleast One Capital</label>
						</div>
					</div>
				</div>
				<div class="row mb-2">
					<div class="col-3">&nbsp;</div>
					<div class="col-6 p-2 px-4">
						<div class="form-check">
							<input type="checkbox" class="form-check-input" :id="'atleast_one_num'+selected_column.form.atleast_one_num" v-model="selected_column.form.atleast_one_num">
							<label :for="'atleast_one_num'+selected_column.form.atleast_one_num" class="form-check-label text-end">Atleast One Number</label>
						</div>
					</div>
				</div>
				<div class="row mb-2">
					<div class="col-3">&nbsp;</div>
					<div class="col-6 p-2 px-4">
						<div class="form-check">
							<input type="checkbox" class="form-check-input" :id="'atleast_one_spl_char'+selected_column.form.atleast_one_spl_char" v-model="selected_column.form.atleast_one_spl_char">
							<label :for="'atleast_one_spl_char'+selected_column.form.atleast_one_spl_char" class="form-check-label text-end">Atleast one Special Character</label>
						</div>
					</div>
				</div>
				<div class="row mb-2">
					<div class="col-3">&nbsp;</div>
					<div class="col-9 p-2 px-4">
						<div class="row px-3">
							<div class="form-check col-12 mb-2">
								<input type="checkbox" class="form-check-input" :id="'support_spl_char'+selected_column.form.support_spl_char" v-model="selected_column.form.support_spl_char">
								<label :for="'support_spl_char'+selected_column.form.support_spl_char" class="form-check-label text-end">Supported Special Character</label>
							</div>
							<div class="col-4 d-flex gap-4 align-items-center" v-if="selected_column.form?.support_spl_char">
								<div class="form-check">
									<input class="form-check-input" type="radio" :name="'chk'+selected_column.form.support_spl_char" :id="'opt1chk'+selected_column.form.support_spl_char" value="0" v-model="selected_column.form.special_char_select_type" checked>
									<label class="form-check-label" :for="'opt1chk'+selected_column.form.support_spl_char">Any</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" :name="'chk'+selected_column.form.support_spl_char" :id="'opt2chk'+selected_column.form.support_spl_char" value="1" v-model="selected_column.form.special_char_select_type">
									<label class="form-check-label" :for="'opt2chk'+selected_column.form.support_spl_char">Specific</label>
								</div>
							</div>
							<div class="col-4 ms-2" v-if="selected_column.form?.support_spl_char">
								<input type="text" :disabled="selected_column.form.special_char_select_type != 1" class="form-control form-control-sm" v-model="selected_column.support_spl_char_field">
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Password Rule -->
			<!-- Telephone -->
			<template v-if="selected_column.form?.type == 'telephone'">
				<div class="row mb-2">
					<label :for="'country_code_'+selected_column.form.country_code" class="col-form-label col-3 text-end">Country Code</label>
					<div class="col-4 py-2">
						<div class="form-check">
							<input type="checkbox" class="form-check-input" :id="'country_code_'+selected_column.form.country_code" v-model="selected_column.form.country_code">
						</div>
					</div>
				</div>
				<div class="row" v-if="selected_column.form?.country_code">
					<label :for="'country_code_field'+selected_column.form.country_code_field" class="col-form-label col-3 text-end">Country Code Field</label>
					<div class="col-4 py-2 d-flex gap-3 align-items-center">
						<div class="form-check">
							<input type="checkbox" class="form-check-input" :id="'country_code_field'+selected_column.form.country_code_field" v-model="selected_column.form.country_code_field">
						</div>
						<select class="form-select form-select-sm" v-model="selected_column.form.country_code_column_field" v-if="selected_column.form?.country_code_field">
							<option selected>Select field</option>
							<template v-for="(column, index) in columns">
								<option :value="column.name" v-if="column?.type === 'column'">
									{{column.name}}	
								</option>
							</template>
						</select>
					</div>
				</div>
			</template>
			<!-- Telephone -->
			<!-- date -->
			<div v-if="selected_column.form?.type == 'date'">
				<div class="row mb-2 align-items-center">
					<label class="col-form-label col-3 text-end">Date Value Condition</label>
					<div class="col-3">
						<select class="form-select form-select-sm" v-model="selected_column.form.date_type">
							<option value="open">Open</option>
							<option value="min">Min</option>
							<option value="max">Max</option>
							<option value="range">Range</option>
						</select>
					</div>
				</div>
				<div class="row mb-2 align-items-center" v-if="selected_column.form?.date_type == 'min' || selected_column.form?.date_type == 'range' ">
					<label class="col-form-label col-3 text-end">Minimum Date</label>
					<div class="col-3">
						<select class="form-select form-select-sm" v-model="selected_column.form.date_type_min">
							<option value="today">Today</option>
							<option value="other">Other</option>
						</select>
					</div>
					<div class="col-3" v-if="selected_column.form.date_type_min == 'other'">
						<input type="date" class="form-control form-control-sm" v-model="selected_column.form.min_date"/>
					</div>
				</div>
				<div class="row align-items-center" v-if="selected_column.form.date_type == 'max' || selected_column.form.date_type == 'range'">
					<label class="col-form-label col-3 text-end">Maximum Date</label>
					<div class="col-3">
						<select class="form-select form-select-sm" v-model="selected_column.form.date_type_max">
							<option value="today">Today</option>
							<option value="other">Other</option>
						</select>
					</div>
					<div class="col-3" v-if="selected_column.form.date_type_max == 'other'">
						<input type="date" class="form-control form-control-sm" v-model="selected_column.form.max_date"/>
					</div>
				</div>
				<div class="row">
					<div class="col-3">&nbsp;</div>
					<div class="col-9"><span class="text-danger" v-if="selected_column.form.min_date > selected_column.form.max_date">The minimum date must be less than the maximum date.</span></div>
				</div>
			</div>
			<!-- date -->
			<!-- Time -->
			<div v-if="selected_column.form?.type == 'time'">
				<div class="row mb-2 align-items-center">
					<label class="col-form-label col-3 text-end">Time Value Condition</label>
					<div class="col-3">
						<select class="form-select form-select-sm" v-model="selected_column.form.time_type">
							<option value="open">Open</option>
							<option value="min">Min</option>
							<option value="max">Max</option>
							<option value="range">Range</option>
						</select>
					</div>
				</div>
				<div class="row mb-2 align-items-center" v-if="selected_column.form.time_type == 'min' || selected_column.form.time_type == 'range' ">
					<label class="col-form-label col-3 text-end">Minimum Time</label>
					<div class="col-3">
						<select class="form-select form-select-sm" v-model="selected_column.form.time_type_min">
							<option value="current_time">Current Time</option>
							<option value="other">Other</option>
						</select>
					</div>
					<div class="col-3" v-if="selected_column.form.time_type_min == 'other'">
						<input type="time" class="form-control form-control-sm" value="" v-model="selected_column.form.min_time"/>
					</div>
				</div>
				<div class="row align-items-center" v-if="selected_column.form.time_type == 'max' || selected_column.form.time_type == 'range'">
					<label class="col-form-label col-3 text-end">Maximum Time</label>
					<div class="col-3">
						<select class="form-select form-select-sm" v-model="selected_column.form.time_type_max">
							<option value="current_time">Current Time</option>
							<option value="other">Other</option>
						</select>
					</div>
					<div class="col-3" v-if="selected_column.form.time_type_max == 'other'">
						<input type="time" class="form-control form-control-sm" v-model="selected_column.form.max_time"/>
					</div>
				</div>
				<div class="row">
					<div class="col-3">&nbsp;</div>
					<div class="col-9"><span class="text-danger" v-if="selected_column.form.min_time > selected_column.form.max_time">The minimum time must be less than the maximum time.</span></div>
				</div>
			</div>
			<!-- Time -->
			<!-- Month Picker -->
			<div v-if="selected_column.form?.type == 'monthpicker'">
				<div class="row mb-2 align-items-center">
					<label class="col-form-label col-3 text-end px-0">Month Value Condition</label>
					<div class="col-3">
						<select class="form-select form-select-sm" v-model="selected_column.form.month_type">
							<option value="open">Open</option>
							<option value="min">Min</option>
							<option value="max">Max</option>
							<option value="range">Range</option>
						</select>
					</div>
				</div>
				<div class="row mb-2 align-items-center" v-if="selected_column.form.month_type == 'min' || selected_column.form.month_type == 'range' ">
					<label class="col-form-label col-3 text-end">Minimum Month</label>
					<div class="col-3">
						<select class="form-select form-select-sm" v-model="selected_column.form.month_type_min">
							<option value="current">Current</option>
							<option value="other">Other</option>
						</select>
					</div>
					<div class="col-3" v-if="selected_column.form.month_type_min == 'other'">
						<input type="date" class="form-control form-control-sm" value="" v-model="selected_column.form.min_month"/>
					</div>
				</div>
				<div class="row align-items-center" v-if="selected_column.form.month_type == 'max' || selected_column.form.month_type == 'range'">
					<label class="col-form-label col-3 text-end">Maximum Month</label>
					<div class="col-3">
						<select class="form-select form-select-sm" v-model="selected_column.form.month_type_max">
							<option value="current">Current</option>
							<option value="other">Other</option>
						</select>
					</div>
					<div class="col-3" v-if="selected_column.form.month_type_max == 'other'">
						<input type="date" class="form-control form-control-sm" value="" v-model="selected_column.form.max_month"/>
					</div>
				</div>
				<div class="row">
					<div class="col-3">&nbsp;</div>
					<div class="col-9"><span class="text-danger" v-if="selected_column.form.min_month > selected_column.form.max_month">The minimum month must be less than the maximum month.</span></div>
				</div>
			</div>
			<!-- Month Picker -->
			<!-- File upload -->
			<div  v-if="selected_column.form?.type == 'fileupload'">
				<!-- Multiple file upload -->
				<div class="row mb-2">
					<label :for="'multiple_file_upload_'+selected_column.form.multiple_file_upload" class="col-form-label col-3 text-end">Multiple File Upload</label>
					<div class="col-4 py-2">
						<div class="form-check">
							<input type="checkbox" class="form-check-input" :id="'multiple_file_upload_'+selected_column.form.multiple_file_upload" v-model="selected_column.form.multiple_file_upload">
						</div>
					</div>
				</div>
				<!-- Multiple file upload -->
				<!-- File format -->
				<div class="row mb-2 align-items-center">
					<label class="col-form-label col-3 text-end">File Format</label>
					<div class="col-3">
						<select class="form-select form-select-sm" v-model="selected_column.form.format_type">
							<option value="img">Image</option>
							<option value="doc">Document</option>
							<option value="any">Any</option>
						</select>
					</div>
				</div>
				<!-- File format -->
				<!-- max file size -->
				<div class="row mb-2 align-items-center">
					<label class="col-form-label col-3 text-end">Max File Size</label>
					<div class="col-3">
						<input type="number" class="form-control form-control-sm" v-model="selected_column.form.max_file_size">
					</div>
					<div class="col-2">
						<select class="form-select form-select-sm" v-model="selected_column.form.file_size_type">
							<option value="kb">kb</option>
							<option value="mb">mb</option>
						</select>
					</div>
				</div>
				<!-- max file size -->
				<!-- Cropping tool & Dimensions -->
				<div class="row gy-2" v-if="selected_column.form.format_type == 'img'">
					<label class="form-check-label col-3 text-end" :for="'crop_tool_'+selected_column.form.cropping_tool">Cropping Tool</label>
					<div class="col-7">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" :id="'crop_tool_'+selected_column.form.cropping_tool" v-model="selected_column.form.cropping_tool">
						</div>
					</div>
					<label class="form-check-label col-3 text-end" for="Dimension">Dimensions</label>
					<div class="col-9">
						<div class="row gy-2">
							<div class="col-5 d-flex gap-2 align-items-center">
								<div class="form-floating">
									<input type="number" class="form-control" id="width" v-model="selected_column.form.width">
									<label for="width">Width</label>
								</div>
								<div class="">X</div>
								<div class="form-floating">
									<input type="number" class="form-control" id="hieght" v-model="selected_column.form.height">
									<label for="hieght">Height</label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- Cropping tool & Dimensions -->
			</div>
			<!-- File upload -->
			<!-- PREFIX -->
			<div class="row mb-2" v-if="selected_column && selected_column.form && !['fileupload', 'textarea','richeditor','relation','textoptions','date','datetime','checkbox','password','telephone','time','monthpicker'].includes(selected_column.form?.type)">
				<label for="" class="col-form-label col-3 text-end" >Prefix/Suffix</label>
				<div class="col-3">
					<div class="form-floating">
						<input type="text" class="form-control form-control-sm" v-model="selected_column.form.prefix" :id="'txtprefix'+selected_column.name">
						<label :for="'txtprefix'+selected_column.name">Prefix</label>
					</div>
				</div>
				<div class="col-3">
					<div class="form-floating">
						<input type="text" class="form-control form-control-sm" v-model="selected_column.form.suffix" :id="'txtsuffix'+selected_column.name">
						<label :for="'txtsuffix'+selected_column.name">Suffix</label>
					</div>
				</div>
			</div>
			<!-- PREFIX -->
			<!-- MASK -->
			<div class="row mb-2" v-if="selected_column && selected_column.form && !['fileupload', 'textarea','richeditor','relation','textoptions','date','datetime','checkbox','password','telephone','url','time','monthpicker','select'].includes(selected_column.form?.type)">
				<label class="col-form-label col-3 text-end">Mask&nbsp;<a href="https://www.npmjs.com/package/vue-3-mask-updated" target="_blank"><small>(HELP)</small></a></label>
				<div class="col-3">
					<input type="text" class="form-control form-control-sm" v-model="selected_column.form.mask">
				</div>
			</div>
			<!-- MASK -->
		</div>
	</div>
	<!-- Add/Edit Offcanvas -->
	<!-- View Offcanvas -->
	<div class="offcanvas offcanvas-end w-50" data-bs-backdrop="static" tabindex="-1" id="offcanvasTableView" aria-labelledby="offcanvasTableView">
		<div class="offcanvas-header border-bottom shadow-sm">
			<h5 class="offcanvas-title text-capitalize" id="offcanvasTableView">Configure Field: {{selected_column.name}}</h5>
			<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close" @click="this.selected_column = ''"></button>
		</div>
		<div class="offcanvas-body">
			<!-- Label -->
			<div class="row mb-3" v-if="selected_column?.table_view">
				<label class="col-form-label small pt-2 col-3 text-end">Label</label>
				<div class="col-4 py-2">
					<input type="text" class="form-control form-control-sm" v-model="selected_column.table_view.label">
				</div>
			</div>
			<!-- Label -->
			<!-- Type -->
			<div class="row mb-3" v-if="selected_column?.table_view">
				<label class="col-form-label small pt-2 col-3 text-end">Type</label>
				<div class="col-4 py-2">
					<select class="form-select form-select-sm" v-model="selected_column.table_view.type">
						<option value="text">Text</option>
						<option value="textoptions">Text With Options</option>
						<option value="relation">Relationship</option>
						<option value="fileupload">File Upload</option>
						<option value="date">Date</option>
						<option value="time">Time</option>
						<option value="monthpicker">Month Picker</option>
					</select>
				</div>
			</div>
			<!-- Type -->
			<!-- Text if empty -->
			<div class="row mb-3" v-if="selected_column?.table_view">
				<label class="col-form-label small pt-2 col-3 text-end">Text If Empty</label>
				<div class="col-4">
					<input type="text" class="form-control form-control-sm" v-model="selected_column.table_view.text_if_empty">
				</div>
			</div>
			<!-- Text if empty -->
			<!-- Select options -->
			<div class="row mb-3" v-if="['textoptions', 'relation'].indexOf(selected_column.table_view?.type)>=0">
				<div class="col-3 text-end">
					<label class="col-form-label small pt-2 text-end" v-if="selected_column.table_view.type == 'textoptions'">Select Options</label>
					<label class="col-form-label small pt-2 text-end" v-if="selected_column.table_view.type == 'relation'">Select Relationship</label>
					<p v-if="selected_column.table_view.type == 'relation' && selected_column.relation.method.length > 0"><a href="#" class="my-1">What's this?</a></p>
				</div>
				<div class="col-9">
					<div v-if="selected_column.table_view.type == 'textoptions'">
						<input type="button" class="btn btn-primary btn-sm" @click="addOption(selected_column, 1)" value="Add Option">
						<div v-for="(option,index) of selected_column.options" :key="index" class="input-group my-2">
							<input type="text" class="form-control form-control-sm" v-model="option.key" placeholder="Enter Option Key">
							<input type="text" class="form-control form-control-sm" v-model="option.value" placeholder="Enter Option Value">
							<button type="button" class="btn btn-danger btn-sm" @click="removeOption(option, selected_column, 1)">X</button>
						</div>
					</div>
					<div v-if="selected_column.table_view?.type=='relation'" class="row gy-2">
						<div class="col-6">
							<label :for="'relation'+selected_column.name" class="col-form-label small pt-2">Relation Method</label>
							<input type="text" class="form-control form-control-sm" :id="'relation'+selected_column.name" v-model="selected_column.relation.method">
						</div>
						<div class="col-12">
							<label for="model-url" class="col-form-label small pt-2">Related To - Model</label>
							<div class="input-group input-group-sm">
								<span class="input-group-text" id="basic-model-url">\App\Models\</span>
								<input type="text" class="form-control form-control-sm" id="model-url" aria-describedby="basic-model-url" v-model="selected_column.relation.related_model">
							</div>
						</div>
						<div class="col-6">
							<label :for="'relatedtomodelid'+selected_column.name" class="col-form-label small pt-2"><span v-if="selected_column.relation?.related_model?.length > 0">"{{selected_column.relation.related_model}}"</span><span v-else>Related To</span> - Model ID</label>
							<input type="text" class="form-control form-control-sm" :id="'relatedtomodelid'+selected_column.name" v-model="selected_column.relation.id_attribute">
						</div>
						<div class="col-6">
							<label :for="'relatedtotitle'+selected_column.name" class="col-form-label small pt-2">Title from Related Model</label>
							<input type="text" class="form-control form-control-sm" :id="'relatedtotitle'+selected_column.name" v-model="selected_column.relation.title_attribute">
						</div>
					</div>
				</div>
			</div>
			<!-- Select options -->
			<!-- Badge -->
			<div class="row mb-3" v-if="selected_column.table_view && !['date','time','fileupload','relation'].includes(selected_column.table_view?.type)">
				<label class="col-form-label small pt-2 col-3 text-end">Badge</label>
				<div class="col-6 d-flex flex-row gap-4 py-2">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" :id="'display_as_badge_'+selected_column.table_view.badge" v-model="selected_column.table_view.badge">
						<label class="form-check-label" :for="'display_as_badge_'+selected_column.table_view.badge">Display as a Badge</label>
					</div>
				</div>
			</div>
			<!-- Badge -->
			<!-- Currency -->
			<div class="row mb-3" v-if="selected_column.table_view && ['text','textoptions'].includes(selected_column.table_view?.type)">
				<label class="col-form-label small pt-2 col-3 text-end">Currency {{ selected_column.table_view.type }}</label>
				<div class="col-9 d-flex flex-row gap-4 py-2">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" :id="'currency_checkbox_'+selected_column.table_view.use_currency" v-model="selected_column.table_view.use_currency">
						<label class="form-check-label" :for="'currency_checkbox_'+selected_column.table_view.use_currency">Make this column as a Currency</label>
					</div>
				</div>
				<div class="col-3" v-if="selected_column.table_view.use_currency">&nbsp;</div>
				<div class="col-4 d-flex flex-row gap-4 py-2" v-if="selected_column.table_view.use_currency">
					<select class="form-select" v-model="selected_column.table_view.currency_symbol">
						<option value='AED'>Dirhams</option>
						<option value='AUD'>Australian Dollar</option>
						<option value='CAD'>Canadian Dollar</option>
						<option value='EUR'>Euro</option>
						<option value='GBP'>Great British Pound</option>
						<option value='HKD'>Hong Kong Dollar</option>
						<option value='INR'>Indian Rupee</option>
						<option value='JPY'>Japanese Yen</option>
						<option value='RUB'>Russian Rouble</option>
						<option value='SGD'>Singapore Dollar</option>
						<option value='USD'>Dollar</option>
						<option value='OTH'>Other</option>
					</select>
				</div>
			</div>
			<!-- Currency -->
			<!-- date format -->
			<div class="row" v-if="selected_column?.table_view && selected_column.table_view.type == 'date'">
				<label class="col-form-label small pt-2 col-3 text-end">Date Format:</label>
				<div class="col-9">
					<div class="row gap-2 ps-3">
						<div class="col-2 p-0">
							<select class="form-select form-select-sm" v-model="selected_column.table_view.date_format.mmddyyyy_format1">
								<option value="YMD">Y-M-D</option>
								<optgroup label="Month">
									<option value="FM">January</option>
									<option value="HM">Jan</option>
									<option value="M">01</option>
								</optgroup>
								<optgroup label="Day">
									<option value="FD">Monday</option>
									<option value="HD">Mon</option>
									<option value="D">1</option>
									<option value="DD">01</option>
								</optgroup>
								<optgroup label="Year">
									<option value="FY">2025</option>
									<option value="HY">25</option>
								</optgroup>
							</select>
						</div>
						<div class="col-2 p-0">
							<select class="form-select form-select-sm" v-model="selected_column.table_view.date_format.separator1">
								<option value="seperator">Separator</option>
								<option value=" ">Space ( )</option>
								<option value=",">Comma (,)</option>
								<option value=".">Dot (.)</option>
								<option value="/">Front Slash (/)</option>
								<option value="\">Back Slash (\)</option>
								<option value="-">Dash (-)</option>
								<option value="_">Underscore (_)</option>
								<option value="+">Plus (+)</option>
							</select>
						</div>
						<div class="col-2 p-0">
							<select class="form-select form-select-sm" v-model="selected_column.table_view.date_format.mmddyyyy_format2">
								<option value="YMD">Y-M-D</option>
								<optgroup label="Month">
									<option value="FM">January</option>
									<option value="HM">Jan</option>
									<option value="M">01</option>
								</optgroup>
								<optgroup label="Day">
									<option value="FD">Monday</option>
									<option value="HD">Mon</option>
									<option value="D">1</option>
									<option value="DD">01</option>
								</optgroup>
								<optgroup label="Year">
									<option value="FY">2025</option>
									<option value="HY">25</option>
								</optgroup>
							</select>
						</div>
						<div class="col-2 p-0">
							<select class="form-select form-select-sm" v-model="selected_column.table_view.date_format.separator2">
								<option value="seperator">Separator</option>
								<option value=" ">Space ( )</option>
								<option value=",">Comma (,)</option>
								<option value=".">Dot (.)</option>
								<option value="/">Front Slash (/)</option>
								<option value="\">Back Slash (\)</option>
								<option value="-">Dash (-)</option>
								<option value="_">Underscore (_)</option>
								<option value="+">Plus (+)</option>
							</select>
						</div>
						<div class="col-2 p-0">
							<select class="form-select form-select-sm" v-model="selected_column.table_view.date_format.mmddyyyy_format3">
								<option value="YMD">Y-M-D</option>
								<optgroup label="Month">
									<option value="FM">January</option>
									<option value="HM">Jan</option>
									<option value="M">01</option>
								</optgroup>
								<optgroup label="Day">
									<option value="FD">Monday</option>
									<option value="HD">Mon</option>
									<option value="D">1</option>
									<option value="DD">01</option>
								</optgroup>
								<optgroup label="Year">
									<option value="FY">2025</option>
									<option value="HY">25</option>
								</optgroup>
							</select>
						</div>
						<div class="d-flex gap-3 align-items-center">
							<div class="d-flex">
								<span class="me-2">Example: </span>
								<span>"</span>
								<div class="" v-if="selected_column.table_view.date_format.mmddyyyy_format1">
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='FM'">January</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='HM'">Jan</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='M'">01</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='FD'">Monday</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='HD'">Mon</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='DD'">01</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='d'">1</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='FY'">2025</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='HY'">25</span>
								</div>
								<div class="" v-if="selected_column.table_view.date_format.separator1">
									<span v-if="selected_column.table_view.date_format.separator1 == ' '">&nbsp;</span>
									<span v-if="selected_column.table_view.date_format.separator1 == ','">,</span>
									<span v-if="selected_column.table_view.date_format.separator1 == '.'">.</span>
									<span v-if="selected_column.table_view.date_format.separator1 == '/'">/</span>
									<span v-if="selected_column.table_view.date_format.separator1 ==  '\\'">\</span>
									<span v-if="selected_column.table_view.date_format.separator1 == '-'">-</span>
									<span v-if="selected_column.table_view.date_format.separator1 == '_'">_</span>
									<span v-if="selected_column.table_view.date_format.separator1 == '+'">+</span>
								</div>
								<div class="" v-if="selected_column.table_view.date_format.mmddyyyy_format2">
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='FM'">January</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='HM'">Jan</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='M'">01</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='FD'">Monday</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='HD'">Mon</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='DD'">01</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='d'">1</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='FY'">2025</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='HY'">25</span>
								</div>
								<div class="" v-if="selected_column.table_view.date_format.separator2">
									<span v-if="selected_column.table_view.date_format.separator2 == ' '">&nbsp;</span>
									<span v-if="selected_column.table_view.date_format.separator2 == ','">,</span>
									<span v-if="selected_column.table_view.date_format.separator2 == '.'">.</span>
									<span v-if="selected_column.table_view.date_format.separator2 == '/'">/</span>
									<span v-if="selected_column.table_view.date_format.separator2 ==  '\\'">\</span>
									<span v-if="selected_column.table_view.date_format.separator2 == '-'">-</span>
									<span v-if="selected_column.table_view.date_format.separator2 == '_'">_</span>
									<span v-if="selected_column.table_view.date_format.separator2 == '+'">+</span>
								</div>
								<div class="" v-if="selected_column.table_view.date_format.mmddyyyy_format3">
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='FM'">January</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='HM'">Jan</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='M'">01</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='FD'">Monday</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='HD'">Mon</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='DD'">01</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='d'">1</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='FY'">2025</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='HY'">25</span>
								</div>
								<span>"</span>
							</div>

							<button class="btn btn-sm btn-outline-danger py-0" v-if="selected_column?.table_view && selected_column?.table_view.date_format.mmddyyyy_format1 != 'YMD'" @click="this.selected_column.table_view.date_format= {};">Clear</button>
						</div>
					</div>
				</div>
			</div>
			<!-- date format -->
			<!-- Time format -->
			<div class="row" v-if="selected_column?.table_view && selected_column.table_view.type == 'time'">
				<label class="col-form-label small pt-2 col-3 text-end">Time Format:</label>
				<div class="col-4 py-2">
					<select class="form-select form-select-sm" v-model="selected_column.table_view.time_format">
						<option value="format">Select Time Format</option>
						<optgroup label="HHMMSS">
							<option value="HH:MM:SS">HH:MM:SS</option>
							<option value="HH-MM-SS">HH-MM-SS</option>
							<option value="HH.MM.SS">HH.MM.SS</option>
						</optgroup>
						<optgroup label="HHMM">
							<option value="HH:MM">HH:MM</option>
							<option value="HH-MM">HH-MM</option>
							<option value="HH.MM">HH.MM</option>
						</optgroup>
					</select>
				</div>
			</div>
			<!-- Time format -->
		</div>
	</div>
	<!-- View Offcanvas -->
	<!-- Listing Offcanvas -->
	<div class="offcanvas offcanvas-end w-50" data-bs-backdrop="static" tabindex="-1" id="offcanvasTableList" aria-labelledby="offcanvasTableList">
		<div class="offcanvas-header border-bottom shadow-sm">
			<h5 class="offcanvas-title text-capitalize" id="offcanvasTableList">Configure Field: {{selected_column.name}}</h5>
			<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close" @click="this.selected_column = ''"></button>
		</div>
		<div class="offcanvas-body">
			<!-- Label -->
			<div class="row mb-3" v-if="selected_column?.table_view">
				<label class="col-form-label small pt-2 col-3 text-end">Label</label>
				<div class="col-4 py-2">
					<input type="text" class="form-control form-control-sm" v-model="selected_column.table_view.label">
				</div>
			</div>
			<!-- Label -->
			<!-- Type -->
			<div class="row mb-3" v-if="selected_column?.table_view">
				<label class="col-form-label small pt-2 col-3 text-end">Type</label>
				<div class="col-4 py-2">
					<select class="form-select form-select-sm" v-model="selected_column.table_view.type">
						<option value="text">Text</option>
						<option value="textoptions">Text With Options</option>
						<option value="relation">Relationship</option>
						<option value="fileupload">File Upload</option>
						<option value="date">Date</option>
						<option value="time">Time</option>
						<option value="monthpicker">Month Picker</option>
					</select>
				</div>
			</div>
			<!-- Type -->
			<!-- Text if empty -->
			<div class="row mb-3" v-if="selected_column?.table_view">
				<label class="col-form-label small pt-2 col-3 text-end">Text If Empty</label>
				<div class="col-4">
					<input type="text" class="form-control form-control-sm" v-model="selected_column.table_view.text_if_empty">
				</div>
			</div>
			<!-- Text if empty -->
			<!-- Select options -->
			<div class="row mb-3" v-if="['textoptions', 'relation'].indexOf(selected_column.table_view?.type)>=0">
				<div class="col-3 text-end">
					<label class="col-form-label small pt-2 text-end" v-if="selected_column.table_view.type == 'textoptions'">Select Options</label>
					<label class="col-form-label small pt-2 text-end" v-if="selected_column.table_view.type == 'relation'">Select Relationship</label>
					<p v-if="selected_column.table_view.type == 'relation' && selected_column.relation.method.length > 0"><a href="#" class="my-1">What's this?</a></p>
				</div>
				<div class="col-9">
					<div v-if="selected_column.table_view.type == 'textoptions'">
						<input type="button" class="btn btn-primary btn-sm" @click="addOption(selected_column, 1)" value="Add Option">
						<div v-for="(option,index) of selected_column.options" :key="index" class="input-group my-2">
							<input type="text" class="form-control form-control-sm" v-model="option.key" placeholder="Enter Option Key">
							<input type="text" class="form-control form-control-sm" v-model="option.value" placeholder="Enter Option Value">
							<button type="button" class="btn btn-danger btn-sm" @click="removeOption(option, selected_column, 1)">X</button>
						</div>
					</div>
					<div v-if="selected_column.table_view?.type=='relation'" class="row gy-2">
						<div class="col-6">
							<label :for="'relation'+selected_column.name" class="col-form-label small pt-2">Relation Method</label>
							<input type="text" class="form-control form-control-sm" :id="'relation'+selected_column.name" v-model="selected_column.relation.method">
						</div>
						<div class="col-6">
							<label :for="'thismodelid'+selected_column.name" class="col-form-label small pt-2">{{gen_resource_name}} -  ID Column</label>
							<input type="text" class="form-control form-control-sm" :id="'thismodelid'+selected_column.name" readonly :value="selected_column.relation.id_attribute ">
						</div>
						<div class="col-12">
							<label for="model-url" class="col-form-label small pt-2">Related To - Model</label>
							<div class="input-group input-group-sm">
								<span class="input-group-text" id="basic-model-url">\App\Models\</span>
								<input type="text" class="form-control form-control-sm" id="model-url" aria-describedby="basic-model-url" v-model="selected_column.relation.related_model">
							</div>
						</div>
						<div class="col-6">
							<label :for="'relatedtomodelid'+selected_column.name" class="col-form-label small pt-2"><span v-if="selected_column.relation?.related_model?.length > 0">"{{selected_column.relation.related_model}}"</span><span v-else>Related To</span> - Model ID</label>
							<input type="text" class="form-control form-control-sm" :id="'relatedtomodelid'+selected_column.name" v-model="selected_column.relation.id_attribute">
						</div>
						<div class="col-6">
							<label :for="'relatedtotitle'+selected_column.name" class="col-form-label small pt-2">Title from Related Model</label>
							<input type="text" class="form-control form-control-sm" :id="'relatedtotitle'+selected_column.name" v-model="selected_column.relation.title_attribute">
						</div>
					</div>
				</div>
			</div>
			<!-- Select options -->
			<!-- Badge -->
			<div class="row mb-3" v-if="selected_column.table_view && !['date','time','fileupload','relation'].includes(selected_column.table_view?.type)">
				<label class="col-form-label small pt-2 col-3 text-end">Badge</label>
				<div class="col-6 d-flex flex-row gap-4 py-2">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" :id="'display_as_badge_'+selected_column.table_view.badge" v-model="selected_column.table_view.badge">
						<label class="form-check-label" :for="'display_as_badge_'+selected_column.table_view.badge">Display as a Badge</label>
					</div>
				</div>
			</div>
			<!-- Badge -->
			<!-- Currency -->
			<div class="row mb-3" v-if="selected_column.table_view && ['text','textoptions'].includes(selected_column.table_view?.type)">
				<label class="col-form-label small pt-2 col-3 text-end">Currency {{ selected_column.table_view.type }}</label>
				<div class="col-9 d-flex flex-row gap-4 py-2">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" :id="'currency_checkbox_'+selected_column.table_view.use_currency" v-model="selected_column.table_view.use_currency">
						<label class="form-check-label" :for="'currency_checkbox_'+selected_column.table_view.use_currency">Make this column as a Currency</label>
					</div>
				</div>
				<div class="col-3" v-if="selected_column.table_view.use_currency">&nbsp;</div>
				<div class="col-4 d-flex flex-row gap-4 py-2" v-if="selected_column.table_view.use_currency">
					<select class="form-select" v-model="selected_column.table_view.currency_symbol">
						<option value='AED'>Dirhams</option>
						<option value='AUD'>Australian Dollar</option>
						<option value='CAD'>Canadian Dollar</option>
						<option value='EUR'>Euro</option>
						<option value='GBP'>Great British Pound</option>
						<option value='HKD'>Hong Kong Dollar</option>
						<option value='INR'>Indian Rupee</option>
						<option value='JPY'>Japanese Yen</option>
						<option value='RUB'>Russian Rouble</option>
						<option value='SGD'>Singapore Dollar</option>
						<option value='USD'>Dollar</option>
						<option value='OTH'>Other</option>
					</select>
				</div>
			</div>
			<!-- Currency -->
			<!-- date format -->
			<div class="row" v-if="selected_column?.table_view && selected_column.table_view?.type == 'date'">
				<label class="col-form-label small pt-2 col-3 text-end">Date Format:</label>
				<div class="col-9">
					<div class="row gap-2 ps-3">
						<div class="col-2 p-0">
							<select class="form-select form-select-sm" v-model="selected_column.table_view.date_format.mmddyyyy_format1">
								<!-- <option value="YMD">Y-M-D</option> -->
								<optgroup label="Month">
									<option value="FM">January</option>
									<option value="HM">Jan</option>
									<option value="M">01</option>
								</optgroup>
								<optgroup label="Day">
									<option value="FD">Monday</option>
									<option value="HD">Mon</option>
									<option value="D">1</option>
									<option value="DD">01</option>
								</optgroup>
								<optgroup label="Year">
									<option value="FY">2025</option>
									<option value="HY">25</option>
								</optgroup>
							</select>
						</div>
						<div class="col-2 p-0">
							<select class="form-select form-select-sm" v-model="selected_column.table_view.date_format.separator1">
								<!-- <option value="seperator">Separator</option> -->
								<option value=" ">Space ( )</option>
								<option value=",">Comma (,)</option>
								<option value=".">Dot (.)</option>
								<option value="/">Front Slash (/)</option>
								<option value="\">Back Slash (\)</option>
								<option value="-">Dash (-)</option>
								<option value="_">Underscore (_)</option>
								<option value="+">Plus (+)</option>
							</select>
						</div>
						<div class="col-2 p-0">
							<select class="form-select form-select-sm" v-model="selected_column.table_view.date_format.mmddyyyy_format2">
								<!-- <option value="YMD">Y-M-D</option> -->
								<optgroup label="Month">
									<option value="FM">January</option>
									<option value="HM">Jan</option>
									<option value="M">01</option>
								</optgroup>
								<optgroup label="Day">
									<option value="FD">Monday</option>
									<option value="HD">Mon</option>
									<option value="D">1</option>
									<option value="DD">01</option>
								</optgroup>
								<optgroup label="Year">
									<option value="FY">2025</option>
									<option value="HY">25</option>
								</optgroup>
							</select>
						</div>
						<div class="col-2 p-0">
							<select class="form-select form-select-sm" v-model="selected_column.table_view.date_format.separator2">
								<!-- <option value="seperator">Separator</option> -->
								<option value=" ">Space ( )</option>
								<option value=",">Comma (,)</option>
								<option value=".">Dot (.)</option>
								<option value="/">Front Slash (/)</option>
								<option value="\">Back Slash (\)</option>
								<option value="-">Dash (-)</option>
								<option value="_">Underscore (_)</option>
								<option value="+">Plus (+)</option>
							</select>
						</div>
						<div class="col-2 p-0">
							<select class="form-select form-select-sm" v-model="selected_column.table_view.date_format.mmddyyyy_format3">
								<!-- <option value="YMD">Y-M-D</option> -->
								<optgroup label="Month">
									<option value="FM">January</option>
									<option value="HM">Jan</option>
									<option value="M">01</option>
								</optgroup>
								<optgroup label="Day">
									<option value="FD">Monday</option>
									<option value="HD">Mon</option>
									<option value="D">1</option>
									<option value="DD">01</option>
								</optgroup>
								<optgroup label="Year">
									<option value="FY">2025</option>
									<option value="HY">25</option>
								</optgroup>
							</select>
						</div>
						<div class="d-flex gap-3 align-items-center">
							<div class="d-flex">
								<span class="me-2">Example: </span>
								<span>"</span>
								<div class="" v-if="selected_column.table_view.date_format.mmddyyyy_format1">
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='FM'">January</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='HM'">Jan</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='M'">01</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='FD'">Monday</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='HD'">Mon</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='DD'">01</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='d'">1</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='FY'">2025</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format1=='HY'">25</span>
								</div>
								<div class="" v-if="selected_column.table_view.date_format.separator1">
									<span v-if="selected_column.table_view.date_format.separator1 == ' '">&nbsp;</span>
									<span v-if="selected_column.table_view.date_format.separator1 == ','">,</span>
									<span v-if="selected_column.table_view.date_format.separator1 == '.'">.</span>
									<span v-if="selected_column.table_view.date_format.separator1 == '/'">/</span>
									<span v-if="selected_column.table_view.date_format.separator1 ==  '\\'">\</span>
									<span v-if="selected_column.table_view.date_format.separator1 == '-'">-</span>
									<span v-if="selected_column.table_view.date_format.separator1 == '_'">_</span>
									<span v-if="selected_column.table_view.date_format.separator1 == '+'">+</span>
								</div>
								<div class="" v-if="selected_column.table_view.date_format.mmddyyyy_format2">
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='FM'">January</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='HM'">Jan</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='M'">01</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='FD'">Monday</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='HD'">Mon</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='DD'">01</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='d'">1</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='FY'">2025</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format2=='HY'">25</span>
								</div>
								<div class="" v-if="selected_column.table_view.date_format.separator2">
									<span v-if="selected_column.table_view.date_format.separator2 == ' '">&nbsp;</span>
									<span v-if="selected_column.table_view.date_format.separator2 == ','">,</span>
									<span v-if="selected_column.table_view.date_format.separator2 == '.'">.</span>
									<span v-if="selected_column.table_view.date_format.separator2 == '/'">/</span>
									<span v-if="selected_column.table_view.date_format.separator2 ==  '\\'">\</span>
									<span v-if="selected_column.table_view.date_format.separator2 == '-'">-</span>
									<span v-if="selected_column.table_view.date_format.separator2 == '_'">_</span>
									<span v-if="selected_column.table_view.date_format.separator2 == '+'">+</span>
								</div>
								<div class="" v-if="selected_column.table_view.date_format.mmddyyyy_format3">
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='FM'">January</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='HM'">Jan</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='M'">01</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='FD'">Monday</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='HD'">Mon</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='DD'">01</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='d'">1</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='FY'">2025</span>
									<span v-if="selected_column.table_view.date_format.mmddyyyy_format3=='HY'">25</span>
								</div>
								<span>"</span>
							</div>

							<button class="btn btn-sm btn-outline-danger py-0" v-if="selected_column?.table_view && selected_column?.table_view.date_format.mmddyyyy_format1 != 'YMD'" @click="this.selected_column.table_view.date_format= {};">Clear</button>
						</div>
					</div>
				</div>
			</div>
			<!-- date format -->
			<!-- Time format -->
			<div class="row" v-if="selected_column?.table_view && selected_column.table_view?.type == 'time'">
				<label class="col-form-label small pt-2 col-3 text-end">Time Format:</label>
				<div class="col-4 py-2">
					<select class="form-select form-select-sm" v-model="selected_column.table_view.time_format">
						<option value="format">Select Time Format</option>
						<optgroup label="HHMMSS">
							<option value="HH:MM:SS">HH:MM:SS</option>
							<option value="HH-MM-SS">HH-MM-SS</option>
							<option value="HH.MM.SS">HH.MM.SS</option>
						</optgroup>
						<optgroup label="HHMM">
							<option value="HH:MM">HH:MM</option>
							<option value="HH-MM">HH-MM</option>
							<option value="HH.MM">HH.MM</option>
						</optgroup>
					</select>
				</div>
			</div>
			<!-- Time format -->
			<!-- Searchable -->
			<div class="row mb-3" v-if="selected_column?.table_view && !['textoptions','relation'].includes(selected_column.table_view?.type)">
				<label class="col-form-label small pt-2 col-3 text-end">Text Searchable</label>
				<div class="col-6 d-flex flex-row gap-4 py-2">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" v-model="selected_column.table_view.searchable" :id="'searchable1'+selected_column.table_view.searchable">
						<label class="form-check-label" :for="'searchable1'+selected_column.table_view.searchable">Make this column Searchable</label>
					</div>
				</div>
			</div>
			<!-- Searchable -->
			<!-- Sortable -->
			<div class="row mb-3" v-if="selected_column?.table_view">
				<label class="col-form-label small pt-2 col-3 text-end">Sortable</label>
				<div class="col-6 d-flex flex-row gap-4 py-2">
					<div class="form-check">
						<input type="checkbox" class="form-check-input" v-model="selected_column.table_view.sortable" :id="'sortable'+selected_column.name">
						<label class="form-check-label" :for="'sortable'+selected_column.name">Make this column Sortable</label>
					</div>
				</div>
			</div>
			<!-- Sortable -->
		</div>
	</div>
	<!-- Listing Offcanvas -->
</template>

<script >
function initialColumnState() {
		return {
			name: "",
			// For form
			use_in_form: true,
			use_in_list: true,
			use_in_export: true,
			//custom
			form:{
				label: "",
				hint: "",
				type: "text",
				required: false,
				autogenerate:false,//for text
				tooltip: "", //for Tooltip
				no_of_rows: 3, //For text area
				//For Numeric
				value_restriction: false,
				use_min_value:false,
				use_max_value:false,
				min_value: 0, 
				max_value: 0,
				//For Precision
				precision: 2, 
				//For Telephone
				country_code: false,
				country_code_field: false,
				country_code_column_field: "",
				//For Password
				atleast_one_caps: false, 
				atleast_one_num: false,
				atleast_one_spl_char: false,
				support_spl_char: false,
				special_char_select_type: 0, 
				support_spl_char_field: "",
				//For date
				date_type: "open", 
				date_type_min: "today",
				min_date: "",
				date_type_max: "today",
				max_date: "",
				//For Time
				time_type: "open", 
				time_type_min: "current_time",
				min_time: "",
				time_type_max: "current_time",
				max_time: "",
				//For Month Picker
				month_type: "open", 
				month_type_min: "current",
				min_month: "",
				month_type_max: "current",
				max_month: "",
				//For file upload
				multiple_file_upload: false,
				format_type: 'img',
				max_file_size: 2,
				file_size_type: 'mb',
				cropping_tool: false,
				width: 0,
				height: 0,
				//For text
				length_restriction: false,
				use_mode_length: '',
				length: 0, 
				min_length: 0,
				max_length: 0,
				prefix: "",
				suffix: "",
				mask: "",
				options_display_as: "dropdown",
			},
			is_unique: false,
			type: "column",
			options: [],
			relation:{
				method: "",
				related_model: "",	
				id_attribute: "id",
				related_model_id: "id",
				title_attribute: "title",
			},
			default: "",
			table_view:{
				label: "",
				type: "text",
				text_if_empty: "Not Specified",
				date_format: {
					mmddyyyy_format1:"",
					separator1: "",
					mmddyyyy_format2:"",
					separator2: "",
					mmddyyyy_format3:"",
				},
				time_format: "format",
				// for tbl
				use_in_view: false,
				visible_in_table: false,
				hint: "",
				badge: false,
				searchable: false,
				sortable: false,
				use_currency: false,
				currency_symbol: "",
				can_copy: false,
				use_in_download: false
			},
		}
	}
	import { VueDraggableNext } from 'vue-draggable-next';
	import axios from 'axios';
	export default {
		compilerOptions: {
			delimiters: ['((', '))'],
			comments: true
		},
		components: {draggable: VueDraggableNext},
		props: { tableNames:{type: Array} },
		data() {
			return {
				db: "",
				tbl: "",
				frm_sortable: null,
				frm_col_to_add: {},
				frm_row_to_add_to: null,
				frm_columns: {
					rows: [
						[]
					],
				},
				frm_open_column: null,
				navigation_group: "nav_open",
				navigation_badge: "",
				selected_column: {},
				uses_multiple_uploads: false,
				downloadWhat: "",
				enableLinks: false,
				columns: [],
				gen_code: '',
				gen_object_label: "",
				gen_resource_name: "",
				gen_form_columns: [],
				gen_table_columns: [],
				gen_form_content: "",
				gen_table_content: "",
				gen_objcount_content: "",
				gen_multiple_uploads: "",
				create_mode: 1,
				generated: false,
				objectToSend: "",
				//custom
				before_event: false,
				after_event: true,
				showField :false,
				showError :false,
				version_name: "",
				form_mode: "popup",
				form_label_placement: 'vertical',
				view_mode: "popup",
				view_label_placement: 'vertical',
				support_download: true,
				make_model: true,
				make_component: true,
				make_resource: true,
				make_controller: true,
				time: new Date().getTime(),
				tableSelected: false,
				tableVersions:[],
				modal_type:"",
				selected_table_version: "",
				showBTN: false,
				showVersionBTN: true,
				auth_required: 1, // set 1 for true and 0 for false
				addEditForm: false,
				viewForm: false,
			}
		},
		methods: {
			//offcanvas button Save
			saveCondition(){
				if (this.selected_column.form) {
					if (this.selected_column.form.type === 'textoptions') {
						const hasEmptyOption = this.selected_column.options.some(option => !option.key || !option.value);
						if (hasEmptyOption) {
							this.showError = true;
							return;
						} else {
							this.showError = false;
						}
					}else if (this.selected_column.form.type === 'relation') {
						if (this.selected_column.relation && this.selected_column.relation.related_model === null || this.selected_column.relation.related_model === undefined ) {
							console.log("dsadsad", this.selected_column.form.type);
							this.showError = true;
							return;
						} else {
							this.showError = false;
						}
					}
					if( !this.showError ){
						let myOffcanvas = document.getElementById('offcanvasExample');
						let bsOffcanvas = bootstrap.Offcanvas.getInstance(myOffcanvas);
						bsOffcanvas.hide();
						this.selected_column = '';
					}
				}
			},
			//offcanvas button Cancel
			cancelCondition(){
				if(this.selected_column.form){
					if(this.selected_column.form.type === 'relation' || this.selected_column.form.type === 'textoptions'){
							console.log("clciked");
						if(this.selected_column.relation && this.selected_column.relation.related_model === null || this.selected_column.relation.related_model === undefined){
							this.selected_column.form.type = 'text';
							this.showError = false;
						}
						else if(this.selected_column.options && this.selected_column.options.some(option => !option.key || !option.value)){
							this.selected_column.form.type = 'text';
							this.showError = false;
						}
					}
					this.selected_column = '';
				}
			},
			adjustTableType(data) {
				if (this.selected_column.form.type === data && !['numeric', 'decimal', 'email', 'textarea','richeditor','password', 'telephone','url','checkbox'].includes(data)) {
					this.selected_column.table_view.type = this.selected_column.form.type;
				} else {
					this.selected_column.table_view.type = 'text';
				}
			},
			//Tool Divider
			addDivider(column){
				let columnIndex = this.columns.indexOf(column);
				if (columnIndex !== -1) 
					this.columns.splice(columnIndex+1, 0, {type: 'divider', show_divider: true });
				else
					console.error("Column not found:", column);
			},
			removeDivider(column){
				let columnIndex = this.columns.indexOf(column);
				if (columnIndex !== -1) 
					this.columns.splice(columnIndex, 1);
				else 
					console.error("Column not found:", column);
			},
			async getVersions() {
				try {
					const response = await axios.post('/api/creator/get-table-json', { tableName: this.tbl });
					if(Array.isArray(response.data) && response.data.length > 0	){
						this.tableVersions = response.data;
						console.log(this.tableVersions);
						this.showBTN = true;
					}else{
						this.tableVersions = [];
						this.showBTN = true;
						this.showVersionBTN = false;
					}
					
				} catch (error) {
					console.error("Error fetching versions:", error);
				}
			},
			async saveInFile(purpose = ''){
				let totalFormUseableColumns = 0;
				let totalTableUseableColumns = 0;
				for (let column of this.columns) {
					if (column.use_in_form > 0)
						totalFormUseableColumns++;
				}
				if (totalFormUseableColumns == 0 && totalTableUseableColumns == 0) {
					alert("Please use at least one column in form or table");
					return;
				}
				const directory = 'creator';
				const baseFileName = this.tbl;
				this.version_name = this.version_name;
				const versionName = this.version_name;

				const fileContent = {
					[`${versionName}`]: {
						"tbl": this.tbl,
						"columns": this.columns,
						"object_name": this.gen_resource_name,
						"object_label": this.gen_object_label,
						"navigation_group": this.navigation_group,
						"form_mode": this.form_mode,
						"form_label_placement": this.form_label_placement,
						"view_mode": this.view_mode,
						"view_label_placement": this.view_label_placement,
						"before_event": this.before_event,
						"after_event": this.after_event,
						"auth_required": this.auth_required,
					}
				};

				const response = await axios.post('/api/creator/save-file', { directory, baseFileName, fileContent, versionName });
				console.log(response);
				if( response?.data?.status == 'success'){
					if( purpose == 'save' ){
						console.log("save");
					}
					else if( purpose == 'saveAndExport' ){
						let fileName = this.tbl+'.json';
						let versionName = this.version_name;
						const response = await axios.get(`/api/creator/download-file/${fileName}/${versionName}`, {
							responseType: 'blob'
						});
						console.log("saveAndExport::: ", response);
						const url = window.URL.createObjectURL(new Blob([response.data]));
						const link = document.createElement('a');
						link.href = url;
						link.setAttribute('download', `${fileName}`);
						document.body.appendChild(link);
						link.click();
						document.body.removeChild(link);
					}
					else if( purpose == 'generate' ){
						this.generateSourceCode();
					}
				}
				return response.data;
			},
			async getNewTableStructure(){
				if ( this.tableVersions.length > 0 && Array.isArray(this.tableVersions) && this.selected_table_version !="new_version" ) {
					this.showField = true;
					const selectedVersion = this.tableVersions.find(version => version.key === this.selected_table_version);
					const tableObj = selectedVersion.value;
					console.log("tableObj::: ",tableObj);
					this.tbl= tableObj.tbl;
					this.columns= tableObj.columns;
					this.gen_resource_name= this.capitalizedWord(this.tbl, "_", " ");
					this.gen_object_label=this.gen_resource_name;
					this.navigation_group= tableObj.navigation_group;
					this.form_mode= tableObj.form_mode;
					this.form_label_placement= tableObj.form_label_placement;
					this.view_mode= tableObj.view_mode;
					this.view_label_placement= tableObj.view_label_placement;
					this.before_event= tableObj.before_event;
					this.after_event= tableObj.after_event;
					this.auth_required= tableObj.auth_required;
				} else {
					this.getTableStructure();
				}
			},
			async getTableStructure() {
				// console.log(this.columns);
				this.tableSelected= true,
				this.showField = true;
				this.columns = [];
				// this.addColumns = [];
				this.gen_form_content = "";
				this.gen_resource_name = this.capitalizedWord(this.tbl, "_", " ");
				this.gen_object_label = this.gen_resource_name;
				const response = await fetch("/api/creator/get-table-structure", {
					method: "POST",
					headers: {
						"Content-Type": "application/json",
					},
					body: JSON.stringify({
						"db": this.db,
						"tbl": this.tbl
					})
				});
				let responseObj = await response.json();
				if (responseObj.hasOwnProperty("status") && responseObj["status"] == 1 && responseObj.hasOwnProperty("columns")) {
					let _columns = responseObj["columns"];
					console.log(_columns);
					
					_columns.forEach((_column, index) => {
						let column = initialColumnState();
						column.name = _column;
						column.form.label = this.capitalizedWord(_column, "_", " ");
						column.table_view.label = this.capitalizedWord(_column, "_", " ");
						column.form.hint = "Enter " + this.capitalizedWord(_column, "_", " ");
						column.relation.method = "rel_"+column.name;
						column.table_view.use_in_view = true;
						column.table_view.visible_in_table = true;
						column.use_in_form = true;
						if ([1,2,3,4].includes(index) && this.support_download)
							column.table_view.use_in_download = true;

						if (column.name == 'status'){
							column.badge = true;
							column.table_view.use_in_view = true;
							column.table_view.visible_in_table = true;
						}
						if (column.name.indexOf("title") >= 0)
							column.form.required = true;

						['title'].forEach((match) => {
							if (column.name.indexOf(match) >= 0) {
								column.table_view.searchable = true;
								column.table_view.sortable = true;
							}
						});

						if(column.name == 'created_by'){
							column.use_in_form = true;
							column.form.type = 'relation';
							// column.table_view.type = 'relation';
							column.relation.related_model = "User";
							column.tbl_relation_method = 'creator';
							column.relation.related_model = 'User';
							column.relation.related_model_id = 'id';
						}

						let proceed = true;
						// Check for data types
						['period', 'status', 'id', 'type', 'duration', 'tenure', 'size', 'value'].forEach((match) => {
							if (column.name.indexOf(match) >= 0) {
								column.form.type = 'numeric';
								column.table_view.type = 'text';
								proceed = false;
							}
						});

						if (proceed) {
							['price', 'rate', 'amount', 'total'].forEach((match) => {
								if (column.name.indexOf(match) >= 0) {
									column.form.type = 'decimal';
									column.table_view.type = 'text';
									// proceed = false;
								}
							});
						}

						if (proceed) {
							['textoptions'].forEach((match) => {
								if (column.name.indexOf(match) >= 0) {
									column.form.type = 'textoptions';
									column.table_view.type = 'textoptions';
									proceed = false;
								}
							});
						}

						if (proceed) {
							['email'].forEach((match) => {
								if (column.name.indexOf(match) >= 0) {
									column.form.type = 'email';
									column.table_view.type = 'text';
									proceed = false;
								}
							});
						}

						if (proceed) {
							['address', 'comments'].forEach((match) => {
								if (column.name.indexOf(match) >= 0) {
									column.form.type = 'textarea';
									column.table_view.type = 'text';
									proceed = false;
								}
							});
						}

						if (proceed) {
							['date', '_on'].forEach((match) => {
								if (column.name.indexOf(match) >= 0) {
									column.form.type = 'date';
									column.table_view.type = 'date';
								}
							});
							['time'].forEach((match) => {
								if (column.name.indexOf(match) >= 0) {
									column.form.type = 'time';
									column.table_view.type = 'time';
								}
							});
							['monthpicker'].forEach((match) => {
								if (column.name.indexOf(match) >= 0) {
									column.form.type = 'monthpicker';
									column.table_view.type = 'monthpicker';
								}
							});
							['fileupload'].forEach((match) => {
								if (column.name.indexOf(match) >= 0) {
									column.form.type = 'fileupload';
									column.table_view.type = 'fileupload';
								}
							});
						}
						this.columns.push(column);
					});
					for (let i = 3; i < this.columns.length; i+=4) {
						this.columns.splice(i, 0, { show_divider: true, type: "divider" });
					}
					console.log(this.columns);
				}
			},
			downloadSomething(whatToDownload) {
				window.open("/creator/download/" + whatToDownload + "/" + this.gen_code);
			},
			async generateSourceCode() {
				console.log(this.columns);
				let totalFormUseableColumns = 0;
				let totalTableUseableColumns = 0;
				let searchableColumns = 0;
				this.uses_multiple_uploads = false;
				for (let column of this.columns) {
					if (column.use_in_form > 0)
						totalFormUseableColumns++;
					if (column.frm_multiple_uploads == true)
						this.uses_multiple_uploads = true;
					if (column.table_view?.searchable && column.table_view.searchable)
						searchableColumns++;
				}
				if (totalFormUseableColumns == 0 && totalTableUseableColumns == 0) {
					alert("Please use at least one column in form or table");
					return;
				}
				if (searchableColumns == 0) {
					alert("Please mark at least one column to be searchable.");
					return;
				}
				var _columns = this.columns.filter((column) => {
					return column.use_in_form || column.type == 'divider';
				})
				var object = {
					"tbl": this.tbl,
					"columns": _columns,
					"object_name": this.gen_resource_name,
					"object_label": this.gen_object_label,
					"navigation_group": this.navigation_group,
					"make_model": this.make_model,
					"make_component": this.make_component,
					"make_resource": this.make_resource,
					"make_controller": this.make_controller,
					"form_mode": this.form_mode,
					"form_label_placement": this.form_label_placement,
					"view_mode": this.view_mode,
					"view_label_placement": this.view_label_placement,
					"before_event": this.before_event,
					"after_event": this.after_event,
					"auth_required": this.auth_required,
				};
				// this.objectToSend = btoa(JSON.stringify(object));
				this.objectToSend = btoa(unescape(encodeURIComponent(JSON.stringify(object))));
				this.enableLinks = true;
				if (this.gen_resource_name.length > 0) {
					this.gen_form_content = "";
					const response = await fetch("/api/creator/brique/generate-resource-code", {
						method: "POST",
						headers: {
							"Content-Type": "application/json",
						},
						body: JSON.stringify(object)
					});
					console.log(response);
					
					let responseObj = await response.json();
					if( responseObj.hasOwnProperty("status") && responseObj.status == 1 ){
						alert("Your component has been generated successfully!");
						this.generated = true;
					}
				} else {
					alert("Please enter object name");
				}
			},
			async saveResource() {
				let totalFormUseableColumns = 0;
				let totalTableUseableColumns = 0;
				for (column of this.columns) {
					if (column.use_in_form > 0)
						totalFormUseableColumns++;
				}
				if (totalFormUseableColumns == 0 && totalTableUseableColumns == 0) {
					alert("Please use at least one column in form or table");
					return;
				}
				if (this.gen_resource_name.length > 0) {
					const response = await fetch("/api/creator/save-resource", {
						method: "POST",
						headers: {
							"Content-Type": "application/json",
						},
						body: JSON.stringify({
							"db": this.db,
							"tbl": this.tbl,
							"object": {
								"columns": this.columns,
								"object_name": this.gen_resource_name,
								"navigation_group": this.navigation_group
							}
						})
					});
					let responseObj = await response.json();
				} else {
					alert("Please enter object name");
				}
			},
			async loadResource() {
				const response = await fetch("/api/creator/load-resource", {
					method: "POST",
					headers: {
						"Content-Type": "application/json",
					},
					body: JSON.stringify({
						"db": this.db,
						"tbl": this.tbl
					})
				});
				let responseObj = await response.json();
				if (responseObj.hasOwnProperty("object") && responseObj.object.hasOwnProperty("object_config")) {
					let objectConfig = responseObj.object.object_config;
					if (objectConfig.hasOwnProperty("columns"))
						this.columns = objectConfig.columns;
					this.generated = true;
					if (objectConfig.hasOwnProperty("object_name"))
						this.gen_resource_name = objectConfig.object_name;
					if (objectConfig.hasOwnProperty("navigation_group"))
						this.navigation_group = objectConfig.navigation_group;
					if (objectConfig.hasOwnProperty("navigation_badge"))
						this.navigation_badge = objectConfig.navigation_badge;
					this.generateSourceCode();
				} else
					console.log("Test");
				// console.log(responseObj);
			},
			addOption(column, destination) {
				console.log("destination",destination);

				console.log(column,"column");
				
				column.options.push({
					"key": "",
					"value": ""
				});
			},
			removeOption(option, column, destination) {
				if (destination == 1)
					column.options.splice(column.options.indexOf(option), 1);
				else
					column.tbl_options.splice(column.options.indexOf(option), 1);
			},
			capitalizedWord(word, splitBy, joinBy) {
				let tableNameParts = word.split(splitBy);
				let newParts = [];
				tableNameParts.forEach(tableNamePart => {
					newParts.push(tableNamePart.charAt(0).toUpperCase() + tableNamePart.substr(1));
				});
				return newParts.join(joinBy);
			}
		},
	};
</script>
