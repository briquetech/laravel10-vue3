<div class="row mb-3">
	<div class="col-3 py-2 text-end">
		<label for="objectName" class="form-label text-secondary">Search Type</label>
	</div>
	<div class="col-4 py-2">
		<div class="form-check">
			<input class="form-check-input" type="radio" v-model="search_type" id="search_type_1" name="search_type" value="simple">
			<label for="search_type_1">Simple Search (just a simple search box)</label>
		</div>
		<div class="form-check">
			<input class="form-check-input" type="radio" v-model="search_type" id="search_type_2" name="search_type" value="advanced">
			<label for="search_type_2">Advanced Search (A set of filters)</label>
		</div>
	</div>
</div>
<div class="row mb-3">
	<div class="col-3 py-2 text-end">
		<label for="objectName" class="form-label text-secondary">Activate/Deactivate Functionality</label>
	</div>
	<div class="col-4 py-2">
		<div class="form-check">
			<input class="form-check-input" type="checkbox" v-model="enable_activate_deactivate" id="act_deact">
			<label for="act_deact">Allow Activate/Deactivate Functionality</label>
		</div>
	</div>
</div>
<div class="row mb-3" v-if="enable_activate_deactivate">
	<div class="col-3 py-2 text-end">
		<label for="objectName" class="form-label">Select Column for Activate/Deactivate</label>
	</div>
	<div class="col-3 py-2">
		<select class="form-select" v-model="activate_deactivate_column">
			<template v-for="column in columns">
				<option :value="column.name" v-if="['id', 'created_at', 'updated_at'].indexOf(column.name) < 0">(( column.name ))</option>
			</template>
		</select>
		<p class="m-0">Please make sure that the column that you select here, has "boolean" data type.</p>
	</div>
</div>
<div class="row mb-3">
	<div class="col-3 py-2 text-end">
		<label for="objectName" class="form-label">View Records - Columns Per Row</label>
	</div>
	<div class="col-2">
		<select class="form-select" v-model="view_fields_per_row">
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
		</select>
	</div>
</div>
<div class="accordion" id="accordionTblColumns">
	<div class="accordion-item" v-for="(column, index) in columns">
		<template v-if="['id', 'created_at', 'updated_at'].indexOf(column.name) < 0">
			<h2 class="accordion-header" :id="'tblheading'+index">
				<div class="d-flex flex-row accordion-button p-0 pe-auto" :class="column.use_in_table_view?'selected_column':''">
					<div class="py-4 px-2 flex-grow-1 fw-normal">
						<ul class="list-inline m-0">
							<li class="list-inline-item me-5">
								<div class="form-check">
									<label class="form-check-label">USE</label>
									<input type="checkbox" class="form-check-input" v-model="column.use_in_table_view">
								</div>
							</li>
							<li class="list-inline-item add_form_column_name me-2">Column: <b>(( column.name ))</b></li>
							<li class="list-inline-item add_form_column_type me-2">Type: <b>(( column.table_type ))</b></li>
							<li class="list-inline-item me-4">
								<div class="form-check" v-if="column.use_in_table_view">
									<input type="checkbox" class="form-check-input" v-model="column.use_in_view" :id="'chk_rec_vw'+column.name">
									<label class="form-check-label" :for="'chk_rec_vw'+column.name">Record View</label>
								</div>
							</li>
							<li class="list-inline-item me-4" v-if="column.table_type != 'icon' && column.table_type != 'image'">
								<div class="form-check" v-if="column.use_in_table_view">
									<input type="checkbox" class="form-check-input" v-model="column.use_in_table" :id="'chk_tbl_vw'+column.name">
									<label class="form-check-label" :for="'chk_tbl_vw'+column.name">Table View</label>
								</div>
							</li>
							<li class="list-inline-item me-5" v-if="column.use_in_table_view">
								<a href="#" class="ms-4 p-4" data-bs-toggle="collapse" :data-bs-target="'#tblcollapse'+index" aria-expanded="true" :aria-controls="'tblcollapse'+index" v-if="column.use_in_table_view">Click to Open/Close</a>
							</li>
						</ul>
					</div>
				</div>
			</h2>
			<div :id="'tblcollapse'+index" class="accordion-collapse collapse" :aria-labelledby="'tblheading'+index" data-bs-parent="#accordionTblColumns">
				<div class="accordion-body">
					<!-- Label -->
					<div class="row mb-3">
						<label class="form-label small pt-2 col-2 text-end">Label</label>
						<div class="col-4">
							<input type="text" class="form-control" v-model="column.tbl_label">
						</div>
					</div>
					<!-- Label -->
					<!-- Type -->
					<div class="row mb-3">
						<label class="form-label small pt-2 col-2 text-end">Type</label>
						<div class="col-2">
							<select class="form-select" v-model="column.table_type">
								<option value="text">Text</option>
								<option value="yesno">Yes/No Options</option>
								<option value="textoptions">Text With Options</option>
								<option value="relation">Relationship</option>
								<option value="image">Image</option>
								<option value="icon">Icon</option>
								<option value="datetime">Date/Datetime</option>
							</select>
						</div>
					</div>
					<!-- Type -->
					<!-- Select options -->
					<div class="row mb-3" v-if="['textoptions', 'relation'].indexOf(column.table_type)>=0">
						<div class="col-2 text-end">
							<label class="form-label small pt-2 text-end" v-if="column.table_type == 'textoptions'">Select Options</label>
							<p v-if="column.table_type == 'textoptions' && column.frm_options.length > 0"><a href="javascript:void(0);" @click="copyOptionFromForm(column, 1)">Copy from form</a></p>
							<label class="form-label small pt-2 text-end" v-if="column.table_type == 'relation'">Select Relationship</label>
							<p v-if="column.table_type == 'relation' && column.frm_relation_method.length > 0"><a href="javascript:void(0);" @click="copyOptionFromForm(column, 2)">Copy from form</a></p>
							<p v-if="column.table_type == 'relation' && column.frm_relation_method.length > 0"><a href="#" class="my-1">What's this?</a></p>
						</div>
						<!-- <div class="col-2">&nbsp;</div> -->
						<div class="col-10">
							<div v-if="column.table_type=='textoptions'">
								<input type="button" class="btn btn-primary btn-sm" @click="addOption(column, 2)" value="Add Option">
								<div v-for="(option, index) of column.tbl_options" :key="index" class="input-group my-1">
									<input type="text" class="form-control" v-model="option.key" placeholder="Enter Option Key">
									<input type="text" class="form-control" v-model="option.value" placeholder="Enter Option Value">
									<button type="button" class="btn btn-danger btn-sm" @click="removeOption(option, column, 1)">X</button>
								</div>
							</div>
							<div v-if="column.table_type=='relation'" class="d-flex flex-row gap-2">
								<div class="col-2">
									<label :for="'relation'+column.name" class="form-label small pt-2">Relation Method</label>
									<input type="text" class="form-control" :id="'relation'+column.name" v-model="column.tbl_relation_method">
								</div>
								<div class="col-3">
									<label for="model-url" class="form-label small pt-2">Related To - Model</label>
									<div class="input-group mb-3">
										<span class="input-group-text" id="basic-model-url">\App\Models\</span>
										<input type="text" class="form-control" id="model-url" aria-describedby="basic-model-url" v-model="column.related_to_model">
									</div>
								</div>
								<div class="col-2">
									<label :for="'thismodelid'+column.name" class="form-label small pt-2">((gen_resource_name)) -  ID Column</label>
									<input type="text" class="form-control" :id="'thismodelid'+column.name" readonly :value="column.name">
								</div>
								<div class="col-2">
									<label :for="'relatedtomodelid'+column.name" class="form-label small pt-2"><span v-if="column.related_to_model.length > 0">"((column.related_to_model))"</span><span v-else>Related To</span> - Model ID</label>
									<input type="text" class="form-control" :id="'relatedtomodelid'+column.name" v-model="column.related_to_model_id">
								</div>
								<div class="col-2">
									<label :for="'relatedtotitle'+column.name" class="form-label small pt-2">Title from Related Model</label>
									<input type="text" class="form-control" :id="'relatedtotitle'+column.name" v-model="column.related_to_model_title">
								</div>
							</div>
						</div>
					</div>
					<!-- Select options -->
					<!-- Text if empty -->
					<div class="row mb-3" v-if="column.table_type == 'textoptions' || column.table_type == 'relation'">
						<label class="form-label small pt-2 col-2 text-end">Text If Empty</label>
						<div class="col-4">
							<input type="text" class="form-control" v-model="column.text_if_empty">
						</div>
					</div>
					<!-- Text if empty -->
					<!-- Common Searchable -->
					<div class="row mb-3" v-if="search_type == 'simple' && column.table_type != 'icon' && column.table_type != 'image'">
						<label class="form-label small pt-2 col-2 text-end">Searchable</label>
						<div class="col-6 d-flex flex-row gap-4 py-2">
							<div class="form-check">
								<input type="checkbox" class="form-check-input" v-model="column.common_searchable" :id="'common_searchable1'+column.name">
								<label class="form-check-label" :for="'common_searchable1'+column.name">Enable text based search in this column</label>
							</div>
						</div>
					</div>
					<!-- Common Searchable -->
					<!-- Searchable -->
					<div class="row mb-3" v-if="search_type == 'advanced' && column.table_type != 'icon' && column.table_type != 'image'">
						<label class="form-label small pt-2 col-2 text-end">Searchable</label>
						<div class="col-6 d-flex flex-row gap-4 py-2">
							<div class="form-check">
								<input type="checkbox" class="form-check-input" v-model="column.searchable" :id="'searchable1'+column.name">
								<label class="form-check-label" :for="'searchable1'+column.name">Make this column Searchable</label>
							</div>
						</div>
					</div>
					<!-- Searchable -->
					<!-- Sortable -->
					<div class="row mb-3" v-if="column.table_type != 'icon' && column.table_type != 'image'">
						<label class="form-label small pt-2 col-2 text-end">Sortable</label>
						<div class="col-6 d-flex flex-row gap-4 py-2">
							<div class="form-check">
								<input type="checkbox" class="form-check-input" v-model="column.sortable" :id="'sortable'+column.name">
								<label class="form-check-label" :for="'sortable'+column.name">Make this column Sortable</label>
							</div>
						</div>
					</div>
					<!-- Sortable -->
					<!-- Badge -->
					<div class="row mb-3" v-if="column.table_type != 'icon' && column.table_type != 'image'">
						<label class="form-label small pt-2 col-2 text-end">Badge</label>
						<div class="col-6 d-flex flex-row gap-4 py-2">
							<div class="form-check">
								<input type="checkbox" class="form-check-input" v-model="column.badge">
								<label class="form-check-label">Display as a Badge</label>
							</div>
						</div>
					</div>
					<!-- Badge -->
					<!-- Date Format -->
					<div class="row mb-3" v-if="column.table_type == 'datetime'">
						<label class="form-label small pt-2 col-2 text-end">Date Format</label>
						<div class="col-6 d-flex flex-row gap-4 py-2">
							<div class="form-check">
								<input class="form-check-input" type="radio" :name="'tbl_date_fmt'+column.name" :id="'opttbl1chk'+column.name" value="1" v-model="column.tbl_date_fmt">
								<label class="form-check-label" :for="'opttbl1chk'+column.name">Date (eg Jan 01, 1970)</label>
							</div>
							<div class="form-check">
								<input class="form-check-input" type="radio" :name="'tbl_date_fmt'+column.name" :id="'opttbl2chk'+column.name" value="2" v-model="column.tbl_date_fmt">
								<label class="form-check-label" :for="'opttbl2chk'+column.name">Date and Time (eg Jan 01, 1970 09:38 AM)</label>
							</div>
						</div>
					</div>
					<!-- Date Format -->
					<!-- Currency -->
					<div class="row mb-3" v-if="column.table_type != 'icon' && column.table_type != 'datetime' && column.table_type != 'image'">
						<label class="form-label small pt-2 col-2 text-end">Currency (( column.table_type ))</label>
						<div class="col-9 d-flex flex-row gap-4 py-2">
							<div class="form-check" v-if="column.use_in_table_view">
								<input type="checkbox" class="form-check-input" v-model="column.use_currency">
								<label class="form-check-label">Make this column as a Currency</label>
							</div>
						</div>
						<div class="col-2" v-if="column.use_currency">&nbsp;</div>
						<div class="col-2 d-flex flex-row gap-4 py-2" v-if="column.use_currency">
							<select class="form-select" v-model="column.currency_symbol">
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
					<!-- Badge -->
					<div class="row mb-3" v-if="column.table_type != 'icon' && column.table_type != 'image'">
						<label class="form-label small pt-2 col-2 text-end">Can Copy</label>
						<div class="col-6 d-flex flex-row gap-4 py-2">
							<div class="form-check" v-if="column.use_in_table_view">
								<input type="checkbox" class="form-check-input" v-model="column.can_copy">
								<label class="form-check-label">Make this column Copyable</label>
							</div>
						</div>
					</div>
					<!-- Badge -->
				</div>
			</div>
		</template>
	</div>
</div>