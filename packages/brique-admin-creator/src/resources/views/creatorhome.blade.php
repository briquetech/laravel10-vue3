<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Creator</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
	<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
	<style>
		table {
			text-align: left;
			position: relative;
		}

		th {
			position: sticky;
			top: 0;
		}

		.selected_column {
			background-color: #faebd7 !important;
		}

		.add_form_column_name {
			min-width: 350px;
		}

		.add_form_column_type {
			min-width: 200px;
		}
	</style>
</head>

<body>
	<div id="app" class="mb-5">
		<div class="container">
			<div class="py-2 text-center">
				<h2>Checkout form</h2>
				<p class="lead">Please fill the following form.</p>
			</div>
			<!-- <div class="row">
				<div class="col-1">
					<label for="" class="form-label">Mode</label>
				</div>
				<div class="col-9">
					<div class="d-flex flex-row gap-3">
						<div class="form-check">
							<input class="form-check-input" type="radio" name="create_mode" id="create_mode_1" v-model="create_mode" value="1"><label class="form-check-label" for="create_mode_1">Look in the database</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="create_mode" id="create_mode_2" v-model="create_mode" value="2"><label class="form-check-label" for="create_mode_2">Paste column information</label>
						</div>
					</div>
				</div>
			</div> -->
		</div>
		<!--  -->
		<div class="container-fluid">
			<div class="accordion" id="accordionPanels">
				<div class="accordion-item">
					<h2 class="accordion-header" id="flush-basicDetails">
						<button class="accordion-button collapsed text-bg-info p-2" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">BASIC DETAILS: <span class="mx-2 ps-2" v-if="db.length > 0">Database: <strong>(( db ))</strong></span><span class="ps-2" v-if="tbl.length > 0">Table: <strong>(( tbl ))</strong></span></button>
					</h2>
					<div id="flush-collapseOne" class="accordion-collapse collapse show" aria-labelledby="flush-basicDetails" data-bs-parent="#accordionPanels">
						<div class="accordion-body">
							<div class="row" v-if="create_mode == 1">
								<div class="col-5">
									<label for="databases" class="form-label">Select Table</label>
									<div class="input-group mb-3">
										<select id="databases" class="form-select" v-model="tbl">
											<!-- <option value="addnew">--- Add New ---</option> -->
											@foreach($tableNames as $tableName)
											<option value="{{$tableName}}">{{$tableName}}</option>
											@endforeach
										</select>
										<button class="btn btn-primary" type="button" @click="getTableStructure()" v-if="tbl == 'addnew'">Add Model</button>
										<button class="btn btn-primary" type="button" @click="getTableStructure()" v-else>Get Structure</button>
										<button type="button" class="btn btn-success" @click="loadResource()">Load Resource</button>
									</div>
								</div>
								<div class="col-3">
									<label for="databases" class="form-label">Navigation Group</label>
									<select class="form-select" v-model="navigation_group">
										<optgroup label="Select one">
											<option value="nav_open">Top Menu</option>
											<option value="nav_masters">Masters Section</option>
										</optgroup>
									</select>
								</div>
							</div>
							<!-- <div class="row" v-if="create_mode == 2">
								<div class="col-2">
									<label class="form-label" for="">Paste columns</label>
									<p><i><small>(It should be in the following format: &lt;columnname&gt;:&lt;datatype&gt;)</small></i></p>
									<textarea class="form-control" v-model="pasted-columns" rows="5"></textarea>
								</div>
								<div class="col-2">
									<ul></ul>
								</div>
							</div> -->
							<!--  -->
							<div class="row mb-3" v-if="columns.length > 0 || tbl == 'addnew'">
								<div class="col-3">
									<label for="objectName" class="form-label">Enter Object Name</label>
									<div class="mb-3">
										<input type="text" class="form-control" id="objectName" v-model="gen_resource_name">
									</div>
									<p class="mb-1">Rules:</p>
									<ul>
										<li>This name is used to create a Model. It should not contain any special characters.</li>
										<li>Only Characters are allowed.</li>
										<!-- <li>Typically the name should be the name of the underlying Model.</li> -->
									</ul>
								</div>
								<div class="col-3">
									<label for="objectName" class="form-label">Enter Object Label</label>
									<div class="mb-3">
										<input type="text" class="form-control" id="objectLabel" v-model="gen_object_label">
									</div>
									<p class="mb-1">Rules:</p>
									<ul>
										<li>This name is used to represent a resource in the menu and the object screens and buttons. It can contain any characters.</li>
										<!-- <li>Special characeters, alphabets and numbers are allowed.</li> -->
										<li>Eg. For an Article, the Object Label can be "Magazine Article".</li>
									</ul>
								</div>
							</div>
							<!--  -->
							<div class="row mb-3" v-if="columns.length > 0 || tbl == 'addnew'">
								<div class="col-3">
									<label for="" class="form-label">Generate Add/Edit Form</label>
									<div class="form-check">
										<input type="checkbox" class="form-check-input" v-model="create_add_edit" id="create_add_edit">
										<label class="form-check-label" for="create_add_edit">Create an Add/Edit Form</label>
									</div>
								</div>
								<div class="col-3">
									<label for="" class="form-label">Generate List/View</label>
									<div class="form-check">
										<input type="checkbox" class="form-check-input" v-model="create_list_view" id="create_list_view">
										<label class="form-check-label" for="create_list_view">Create a List/View</label>
									</div>
								</div>
								<div class="col-3">
									<label for="" class="form-label">Generate APIs</label>
									<div class="form-check">
										<input type="checkbox" class="form-check-input" v-model="create_api" id="create_api">
										<label class="form-check-label" for="create_api">Create an API</label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="accordion-item" v-if="tbl == 'addnew'">
					<h2 class="accordion-header" id="flush-headingForm">
						<button class="accordion-button collapsed text-bg-primary p-2" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseColumns" aria-expanded="false" aria-controls="flush-collapseColumns">ADD COLUMNS</button>
					</h2>
					<div id="flush-collapseColumns" class="accordion-collapse collapse" aria-labelledby="flush-headingForm" data-bs-parent="#accordionPanels">
						<div class="accordion-body">
							@include('brique-admin-creator::columns')
						</div>
					</div>
				</div>
				<div class="accordion-item" v-if="columns.length > 0 && create_add_edit">
					<h2 class="accordion-header" id="flush-headingForm">
						<button class="accordion-button collapsed text-bg-primary p-2" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseForm" aria-expanded="false" aria-controls="flush-collapseForm">ADD/EDIT FORM COMPONENTS</button>
					</h2>
					<div id="flush-collapseForm" class="accordion-collapse collapse" aria-labelledby="flush-headingForm" data-bs-parent="#accordionPanels">
						<div class="accordion-body">
							@include('brique-admin-creator::form')
						</div>
					</div>
				</div>
				<div class="accordion-item" v-if="columns.length > 0 && create_list_view">
					<h2 class="accordion-header" id="flush-headingTable">
						<button class="accordion-button collapsed text-bg-success p-2" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTable" aria-expanded="false" aria-controls="flush-collapseTable">LISTING TABLE COLUMNS</button>
					</h2>
					<div id="flush-collapseTable" class="accordion-collapse collapse" aria-labelledby="flush-headingTable" data-bs-parent="#accordionPanels">
						<div class="accordion-body">
							@include('brique-admin-creator::table')
						</div>
					</div>
				</div>
				<div class="accordion-item" v-if="columns.length > 0 && create_api">
					<h2 class="accordion-header" id="flush-headingAPI">
						<button class="accordion-button collapsed text-bg-danger p-2" type="button" data-bs-toggle="collapse" data-bs-target="#flush-headingAPICollapse" aria-expanded="false" aria-controls="flush-headingAPICollapse">APIs</button>
					</h2>
					<div id="flush-headingAPICollapse" class="accordion-collapse collapse" aria-labelledby="flush-headingTable" data-bs-parent="#accordionPanels">
						<div class="accordion-body">
							@include('brique-admin-creator::apis')
						</div>
					</div>
				</div>
				<div class="accordion-item" v-if="columns.length > 0 && (create_list_view || create_add_edit || create_api)">
					<h2 class="accordion-header" id="flush-headingFinalSteps">
						<button class="accordion-button collapsed text-bg-warning p-2" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFinalSteps" aria-expanded="false" aria-controls="flush-collapseFinalSteps">FINAL STEPS</button>
					</h2>
					<div id="flush-collapseFinalSteps" class="accordion-collapse collapse" aria-labelledby="flush-headingFinalSteps" data-bs-parent="#accordionPanels">
						<div class="accordion-body">
							@include('brique-admin-creator::final_steps')
						</div>
					</div>
				</div>
				<div class="accordion-item" v-if="columns.length > 0 && generated">
					<h2 class="accordion-header" id="flush-headingResult">
						<button class="accordion-button collapsed text-bg-dark p-2" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseResult" aria-expanded="false" aria-controls="flush-collapseResult">RESULT</button>
					</h2>
					<div id="flush-collapseResult" class="accordion-collapse collapse" aria-labelledby="flush-headingResult" data-bs-parent="#accordionPanels">
						<div class="accordion-body">
							@include('brique-admin-creator::result')
						</div>
					</div>
				</div>
			</div>
		</div>
		<form action="/creator/brique/generate-code" method="post" id="frm_download">
			@csrf
			<input type="hidden" name="downloadWhat" v-model="downloadWhat">
			<input type="hidden" name="json" v-model="objectToSend">
		</form>
	</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.esm.min.js" integrity="sha512-9IAuCQeqbsF/CP2TJ7avKUW9/+dODxnKuPyj42O++oHkjGuuqj3ZLzTFtCihuRjb5G/aGefieF21ZoRG5kwzwA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->
	<script type="module">
		function initialColumnState() {
			return {
				name: "",
				show: true,
				// For form
				use_in_form: false,
				frm_hint: "",
				frm_label: "",
				form_type: "text",
				required: false,
				frm_select_type: "fixed",
				frm_options: [],
				frm_relation_method: "",
				frm_title_attribute: "title",
				frm_related_model: "",
				frm_popup_select_id_attribute: "",
				frm_popup_select_title_attribute: "",
				use_length: false,
				length: 0,
				min_length: 0,
				max_length: 0,
				default: "",
				use_prefix_suffix: false,
				prefix: "",
				suffix: "",
				mask: "",
				date_display_fmt: "",
				date_return_fmt: "",
				// display_in_row: 0,
				// for tbl
				use_in_table_view: false,
				use_in_table: false,
				use_in_view: false,
				tbl_hint: "",
				tbl_label: "",
				table_type: "text",
				tbl_date_fmt: 1,
				tbl_options: [],
				tbl_relation_method: "",
				// tbl_title_attribute: "",
				related_to_model: "",
				related_to_model_id: "id",
				text_if_empty: "Not Specified",
				badge: false,
				common_searchable: false, 
				searchable: false,
				sortable: false,
				use_currency: false,
				currency_symbol: "",
				can_copy: false,
			}
		}
		const {
			createApp
		} = Vue;
		createApp({
			compilerOptions: {
				delimiters: ['((', '))'],
				comments: true
			},
			components: {},
			data() {
				return {
					drag: false,
					db: "",
					tbl: "",
					create_add_edit: false,
					create_list_view: false,
					create_api: false,
					unique_column: "none",
					frm_sortable: null,
					frm_col_to_add: {},
					frm_row_to_add_to: null,
					frm_columns: {
						rows: [
							[]
						],
					},
					frm_open_column: null,
					frm_fields_per_row: 3,
					view_fields_per_row: 3,
					search_type: "simple",
					navigation_group: "nav_open",
					navigation_badge: "",
					uses_multiple_uploads: false,
					enable_activate_deactivate: true,
					activate_deactivate_column: "status",
					downloadWhat: "",
					enableLinks: false,
					tables: [],
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
					add_edit_mode: 2,
					create_mode: 1,
					generated: false,
					objectToSend: "",
				}
			},
			methods: {
				async getTableStructure() {
					this.columns = [];
					this.gen_form_content = "";
					this.gen_resource_name = this.capitalizedWord(this.tbl, "_", "");
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
					if (responseObj.hasOwnProperty("status") && responseObj["status"] == 1 &&
						responseObj.hasOwnProperty("columns")) {
						// console.log(json_encode(responseObj));
						let _columns = responseObj["columns"];
						_columns.forEach((_column, index) => {
							let column = initialColumnState();
							column.name = _column;
							column.frm_label = this.capitalizedWord(_column, "_", " ");
							column.tbl_label = this.capitalizedWord(_column, "_", " ");
							column.frm_hint = "Enter " + this.capitalizedWord(_column, "_", " ");
							if (['created_at', 'updated_at', 'status'].indexOf(column.name) >= 0 || index == 0)
								column.use_in_form = false;
							else {
								column.use_in_view = true;
								column.use_in_form = true;
								column.use_in_table_view = true;
								column.use_in_table = true;
							}
							// desc
							if (['description', 'excerpt'].indexOf(column.name) >= 0 ){

							}

							if (column.name == 'status'){
								column.show = false;
								column.badge = true;
								column.use_in_table_view = true;
								column.use_in_view = true;
								column.use_in_table = false;
							}
							if (column.name.indexOf("title") >= 0)
								column.required = true;

							['title'].forEach((match) => {
								if (column.name.indexOf(match) >= 0) {
									column.common_searchable = true;
									column.searchable = true;
									column.sortable = true;
								}
							});

							if(column.name == 'created_by'){
								column.show = false;
								column.use_in_form = false;
								column.form_type = 'select';
								column.frm_select_type = 'relation';
								column.frm_related_model = "User";
								column.frm_popup_select_id_attribute = "id";
								column.frm_popup_select_title_attribute = "name";
								column.table_type = 'relation';
								column.tbl_relation_method = 'creator';
								column.related_to_model = 'User';
								column.related_to_model_id = 'id';
								column.related_to_model_title = 'name';
							}

							let proceed = true;
							// Check for data types
							['period', 'status', 'id', 'type', 'duration', 'tenure', 'size', 'value'].forEach((match) => {
								if (column.name.indexOf(match) >= 0) {
									column.form_type = 'numeric';
									column.table_type = 'text';
									proceed = false;
								}
							});

							if (proceed) {
								['price', 'rate', 'amount', 'total'].forEach((match) => {
									if (column.name.indexOf(match) >= 0) {
										column.form_type = 'decimal';
										column.table_type = 'text';
										proceed = false;
									}
								});
							}

							if (proceed) {
								['is_', 'has_'].forEach((match) => {
									if (column.name.indexOf(match) >= 0) {
										column.form_type = 'radio';
										column.table_type = 'text';
										proceed = false;
									}
								});
							}

							if (proceed) {
								['email'].forEach((match) => {
									if (column.name.indexOf(match) >= 0) {
										column.form_type = 'email';
										column.table_type = 'text';
										proceed = false;
									}
								});
							}

							if (proceed) {
								['address', 'comments'].forEach((match) => {
									if (column.name.indexOf(match) >= 0) {
										column.form_type = 'textarea';
										column.table_type = 'text';
										proceed = false;
									}
								});
							}

							if (proceed) {
								['date', '_on'].forEach((match) => {
									if (column.name.indexOf(match) >= 0) {
										column.form_type = 'date';
										column.table_type = 'datetime';
									}
								});
							}
							this.columns.push(column);
						});
						// Coming soon
						// if (this.columns.length > 12)
						// 	this.add_edit_mode = 1;
						// else
						// 	this.add_edit_mode = 2;
					}
				},
				addColumnToFormList() {
					if (!this.frm_row_to_add_to || this.frm_row_to_add_to == null || this.frm_row_to_add_to == undefined) {
						alert("Please select a row");
						return;
					}
					let found = false;
					this.frm_columns.rows.map((rowToAddTo) => {
						rowToAddTo.map((element) => {
							if (element.name == this.frm_col_to_add.name)
								found = true;
						});
					});
					if (!found) {
						this.frm_row_to_add_to.push(Object.assign({}, this.frm_col_to_add));
						$(".frm_columns_list").sortable({
							connectWith: ".connectedSortable"
						}).disableSelection();
					} else
						alert("You cannot add the same column twice");
				},
				addNewRow() {
					console.log(this.frm_columns.rows);
					if (this.frm_columns.rows[this.frm_columns.rows.length - 1].length == 0) {
						alert("You cannot add if there is another row empty");
						return;
					}
					this.frm_columns.rows.push([]);
				},
				toggleCollapseColumnPanel(element, column, purpose) {
					if (purpose == 'ae')
						column.show_in_form = !column.show_in_form;
					else
						column.show = !column.show;
					this.columns.map((_column) => {
						if (_column.name != column.name)
							if (purpose == 'ae')
								_column.show_in_form = false;
							else
								_column.show = false;
						return column;
					})
				},
				downloadSomething(whatToDownload) {
					window.open("/creator/download/" + whatToDownload + "/" + this.gen_code);
				},
				async getResourceCode() {
					let totalFormUseableColumns = 0;
					let totalTableUseableColumns = 0;
					let searchableColumns = 0;
					this.uses_multiple_uploads = false;
					// console.log(this.columns);
					for (let column of this.columns) {
						if (column.use_in_form > 0)
							totalFormUseableColumns++;
						if (column.use_in_table > 0)
							totalTableUseableColumns++;
						if (column.frm_multiple_uploads == true)
							this.uses_multiple_uploads = true;
						if (column.searchable || column.common_searchable)
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
						return column.use_in_form || (column.use_in_table_view && (column.use_in_table || column.use_in_view));
					})
					var object = {
						"tbl": this.tbl,
						"columns": _columns,
						"object_name": this.gen_resource_name,
						"object_label": this.gen_object_label,
						"navigation_group": this.navigation_group,
						"create_add_edit": this.create_add_edit,
						"create_list_view": this.create_list_view,
						"create_api": this.create_api,
						"add_edit_mode": this.add_edit_mode,
						"unique_column": this.unique_column,
						"form_fields_per_row": this.frm_fields_per_row,
						"view_fields_per_row": this.view_fields_per_row,
						"search_type": this.search_type,
						"enable_activate_deactivate": this.enable_activate_deactivate,
						"activate_deactivate_column": this.activate_deactivate_column,
					};
					console.log(JSON.stringify(object));
					
					this.objectToSend = btoa(JSON.stringify(object));
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
						if (column.use_in_table > 0)
							totalTableUseableColumns++;
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
									"add_edit_mode": this.add_edit_mode,
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
						if (objectConfig.hasOwnProperty("add_edit_mode"))
							this.add_edit_mode = objectConfig.add_edit_mode;
						if (objectConfig.hasOwnProperty("object_name"))
							this.gen_resource_name = objectConfig.object_name;
						if (objectConfig.hasOwnProperty("navigation_group"))
							this.navigation_group = objectConfig.navigation_group;
						if (objectConfig.hasOwnProperty("navigation_badge"))
							this.navigation_badge = objectConfig.navigation_badge;
						this.getResourceCode();
					} else
						console.log("Test");
					// console.log(responseObj);
				},
				addOption(column, destination) {
					if (destination == 1)
						column.frm_options.push({
							"key": "",
							"value": ""
						});
					else
						column.tbl_options.push({
							"key": "",
							"value": ""
						});
				},
				copyOptionFromForm(column, destination) {
					if (destination == 1)
						column.tbl_options = Object.assign({}, column.frm_options);
					else {
						column.tbl_relation_method = column.frm_relation_method;
						column.tbl_title_attribute = column.frm_title_attribute;
					}
				},
				removeOption(option, column, destination) {
					if (destination == 1)
						column.frm_options.splice(column.frm_options.indexOf(option), 1);
					else
						column.tbl_options.splice(column.frm_options.indexOf(option), 1);
				},
				copyCommand() {
					navigator.clipboard.writeText('php artisan make:filament-resource ' + this.gen_resource_name);
				},
				copyFormContent() {
					navigator.clipboard.writeText(this.gen_form_content);
				},
				copyTableContent() {
					console.log(this.gen_table_content);
					navigator.clipboard.writeText(this.gen_table_content);
				},
				copyMenuItem() {
					navigator.clipboard.writeText(this.gen_objcount_content);
				},
				copyClassProperties() {
					navigator.clipboard.writeText(
						"protected static ?string $navigationGroup = '" + this.navigation_group + "';\n" +
						"protected static ?string $modelLabel = '" + this.gen_object_label + "';"
					);
				},
				copyModelArray() {
					navigator.clipboard.writeText(this.gen_multiple_uploads);
				},
				nl2br(str, is_xhtml) {
					var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
					return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
				},
				br2(str, toChr) {
					var regex = /<br\s*[\/]?>/gi;
					return str.replace(regex, toChr);
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
			mounted() {},
		}).mount('#app');
	</script>
</body>

</html>
