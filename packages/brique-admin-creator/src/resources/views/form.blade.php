<div class="row mb-3">
	<div class="col-2 py-2 text-end">
		<label for="objectName" class="form-label">Create/Edit Mode</label>
	</div>
	<div class="col-6 py-2">
		<div class="d-flex flex-row gap-3">
			<div class="form-check">
				<input class="form-check-input" type="radio" name="add_edit_mode" :id="'rad1'+tbl" value="1" v-model="add_edit_mode" disabled>
				<label class="form-check-label" :for="'rad1'+tbl">In separate pages</label>
			</div>
			<div class="form-check">
				<input class="form-check-input" type="radio" name="add_edit_mode" :id="'rad2'+tbl" value="2" v-model="add_edit_mode">
				<label class="form-check-label" :for="'rad2'+tbl">As a popup</label>
			</div>
		</div>
	</div>
</div>
<div class="row mb-3" v-if="columns && columns.length > 0">
	<div class="col-2 py-2 text-end">
		<label for="uniqueCheck" class="form-label">Select Column for Unique Check</label>
	</div>
	<div class="col-3 py-2">
		<select class="form-select" v-model="unique_column" id="uniqueCheck">
			<option value="none">None</option>
			<template v-for="column in columns">
				<option :value="column.name" v-if="['id', 'created_at', 'updated_at'].indexOf(column.name) < 0">(( column.name ))</option>
			</template>
		</select>
		<p class="m-0">Please make sure that the column that you select here, has "text/numeric/date" data type.</p>
	</div>
</div>
<div class="row mb-4">
	<div class="col-2 py-2 text-end">
		<label for="objectName" class="form-label">Columns Per Row</label>
	</div>
	<div class="col-2">
		<select class="form-select" v-model="frm_fields_per_row">
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
		</select>
	</div>
</div>
<div class="accordion" id="accordionColumns">
	<div class="accordion-item" v-for="(column, index) in columns">
		<template v-if="['id', 'created_at', 'updated_at', 'status', 'created_by'].indexOf(column.name) < 0">
			<h2 class="accordion-header" :id="'heading'+index">
				<div class="d-flex flex-row accordion-button p-0 pe-auto" :class="column.use_in_form?'selected_column':''">
					<div class="py-4 px-2 flex-grow-1 fw-normal">
						<ul class="list-inline m-0">
							<li class="list-inline-item me-5">
								<div class="form-check">
									<label class="form-check-label">USE</label>
									<input type="checkbox" class="form-check-input" v-model="column.use_in_form" @click="column.show_in_form=false">
								</div>
							</li>
							<li class="list-inline-item add_form_column_name me-2">Column Name: <b>(( column.name ))</b></li>
							<li class="list-inline-item add_form_column_type me-2">Type: <b>(( column.form_type ))</b></li>
							<li class="list-inline-item me-5" v-if="column.use_in_form">
								<div class="form-check">
									<input type="checkbox" class="form-check-input" v-model="column.required" :id="'add_'+column.name+'_reqd'">
									<label :for="'add_'+column.name+'_reqd'" class="form-check-label text-end" class="form-label">Required</label>
								</div>
							</li>
							<li class="list-inline-item me-5" v-if="column.display_in_row && column.display_in_row > 0">
								<label for="" class="">Row <b>#(( column.display_in_row ))</b></label>
							</li>
							<li class="list-inline-item flex-end">
								<a href="javascript:void(0)" class="py-4" v-if="column.use_in_form" @click="toggleCollapseColumnPanel('collapse'+index, column, 'ae')">Click to Configure</a>
							</li>
						</ul>
					</div>
				</div>	
			</h2>
			<div :id="'collapse'+index" v-if="column.show_in_form">
				<div class="accordion-body">
					<!-- Label -->
					<div class="row mb-3">
						<label :for="'frm_label'+column.name" class="col-form-label col-2 text-end">Label</label>
						<div class="col-4">
							<input type="text" class="form-control" :id="'frm_label'+column.name" v-model="column.frm_label">
						</div>
					</div>
					<!-- Label -->
					<!-- hint -->
					<div class="row mb-3">
						<label :for="'frm_hint'+column.name" class="col-form-label col-2 text-end">Hint</label>
						<div class="col-4">
							<input type="text" class="form-control" :id="'frm_hint'+column.name" v-model="column.frm_hint">
						</div>
					</div>
					<!-- hint -->
					<!-- Type -->
					<div class="row mb-3">
						<label :for="'frm_type'+column.name" class="col-form-label col-2 text-end">Type</label>
						<div class="col-2">
							<select class="form-select" v-model="column.form_type">
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
								<option value="datetime">Datetime</option>
								<option value="yesno">Yes/No</option>
								<option value="radio">Radio</option>
								<option value="checkbox">Checkbox</option>
								<!-- <option value="toggle">Toggle</option> -->
								<option value="select">Select Values/Relation</option>
								<option value="fileupload">File Upload</option>
							</select>
						</div>
					</div>
					<!-- Type -->
					<!-- Required -->
					<!-- <div class="row mb-3">
						<label for="" class="col-form-label col-2 text-end" class="form-label">Required</label>
						<div class="col-4 py-2">
							<div class="form-check">
								<input type="checkbox" class="form-check-input" v-model="column.required">
							</div>
						</div>
					</div> -->
					<!-- Required -->
					<!-- Select options -->
					<div class="row" v-if="column.use_in_form && ['select', 'radio'].indexOf(column.form_type)>=0">
						<div class="col-2">
							<label class="col-form-label text-end">Select Options</label>
							<a href="#" class="my-1">What's this?</a>
						</div>
						<div class="col-6 d-flex flex-row gap-4 py-2">
							<div class="form-check">
								<input class="form-check-input" type="radio" :name="'chk'+column.name" :id="'opt1chk'+column.name" value="fixed" v-model="column.frm_select_type">
								<label class="form-check-label" :for="'opt1chk'+column.name">Fixed Options</label>
							</div>
							<div class="form-check">
								<input class="form-check-input" type="radio" :name="'chk'+column.name" :id="'opt2chk'+column.name" value="relation" v-model="column.frm_select_type">
								<label class="form-check-label" :for="'opt2chk'+column.name">Relationship</label>
							</div>
							<div class="form-check">
								<input class="form-check-input" type="radio" :name="'chk'+column.name" :id="'opt3chk'+column.name" value="popup_select" v-model="column.frm_select_type">
								<label class="form-check-label" :for="'opt3chk'+column.name">Popup Select</label>
							</div>
						</div>
					</div>
					<div class="row mb-3" v-if="column.use_in_form && ['select', 'radio'].indexOf(column.form_type)>=0">
						<div class="col-2">&nbsp;</div>
						<div v-if="column.frm_select_type=='fixed'" class="col-4">
							<input type="button" class="btn btn-primary btn-sm" @click="addOption(column, 1)" value="Add Option">
							<div v-for="(option,index) of column.frm_options" :key="index" class="input-group my-1">
								<input type="text" class="form-control" v-model="option.key" placeholder="Enter Option Key">
								<input type="text" class="form-control" v-model="option.value" placeholder="Enter Option Value">
								<button type="button" class="btn btn-danger btn-sm" @click="removeOption(option, column, 1)">X</button>
							</div>
						</div>
						<!-- <div v-if="column.frm_select_type=='relation'" class="d-flex flex-row col-4 gap-2">
							<div class="form-floating">
								<input type="text" class="form-control" :id="'relation'+column.name" v-model="column.frm_relation_method">
								<label :for="'relation'+column.name">Relation Method</label>
							</div>
							<div class="form-floating">
								<input type="text" class="form-control" :id="'relationttl'+column.name" v-model="column.frm_title_attribute">
								<label :for="'relationttl'+column.name">Title Attribute</label>
							</div>
						</div> -->
						<div v-if="['relation', 'popup_select'].indexOf(column.frm_select_type) >= 0" class="d-flex flex-row col-6 gap-2">
							<div class="form-group">
								<label :for="'popup_select'+column.name" class="form-label">Related Model</label>
								<div class="input-group">
									<span class="input-group-text">\App\Models\</span>
									<input type="text" class="form-control" :id="'popup_select'+column.name" v-model="column.frm_related_model">
								</div>
							</div>
							<div class="form-group">
								<label :for="'popup_selectid'+column.name" class="form-label">ID Attribute</label>
								<input type="text" class="form-control" :id="'popup_selectid'+column.name" v-model="column.frm_popup_select_id_attribute">
							</div>
							<div class="form-group">
								<label :for="'popup_selectttl'+column.name" class="form-label">Title Attribute</label>
								<input type="text" class="form-control" :id="'popup_selectttl'+column.name" v-model="column.frm_popup_select_title_attribute">
							</div>
						</div>
					</div>
					<div class="row mb-3" v-if="column.use_in_form && ['select', 'radio'].indexOf(column.form_type)>=0 && (column.frm_select_type=='relation' || column.frm_select_type=='popup_select')">
						<div class="col-2 text-end">
							<label class="col-form-label">Need Default Option</label>
						</div>
						<div class="col-10 pt-2">
							<div class="form-check mb-2">
								<input class="form-check-input" type="checkbox" v-model="column.frm_popup_select_default_option" :id="'defaultoption'+column.name">
							</div>
						</div>
						<div class="offset-2 col-10" v-if="column.frm_popup_select_default_option">
							<label class="col-form-label text-end">Enter the default option's <b>id</b> and <b>value</b></label>
						</div>
						<div v-if="column.frm_popup_select_default_option" class="d-flex flex-row offset-2 col-4 gap-2">
							<div class="form-floating">
								<input type="text" class="form-control" :id="'popup_selectdefid'+column.name" v-model="column.frm_popup_select_default_option_id">
								<label :for="'popup_selectdefid'+column.name">Option ID</label>
							</div>
							<div class="form-floating">
								<input type="text" class="form-control" :id="'popup_selectdefval'+column.name" v-model="column.frm_popup_select_default_option_value">
								<label :for="'popup_selectdefval'+column.name">Option Value</label>
							</div>
						</div>
					</div>
					<!-- Select options -->
					<!-- Length -->
					<div class="row" v-if="column.form_type=='text'" :class="column.use_length?'':'mb-3'">
						<label for="" class="col-form-label col-2 text-end" class="form-label">Configure Length</label>
						<div class="col-10 py-2">
							<div class="form-check mb-2">
								<input class="form-check-input" type="checkbox" v-model="column.use_length" :id="'chkleng'+column.name">
							</div>
						</div>
					</div>
					<div class="row mb-3" v-if="column.form_type=='text' && column.use_length">
						<div class="col-2">&nbsp;</div>
						<div class="col-9">
							<div class="row d-flex flex-row">
								<div class="col-2">
									<div class="form-floating">
										<input type="number" class="form-control" :id="'txtlen'+column.name" v-model="column.length" min="0" max="100">
										<label :for="'txtlen'+column.name">Length</label>
									</div>
								</div>
								<p class="col-1 d-flex align-items-center fw-bold ">- OR -</p>
								<div class="col-2">
									<div class="form-floating">
										<input type="number" class="form-control" :id="'txtmin_length'+column.name" v-model="column.min_length" min="0">
										<label :for="'txtmin_length'+column.name">Min Length</label>
									</div>
								</div>
								<div class="col-2">
									<div class="form-floating">
										<input type="number" class="form-control" :id="'txtmax_length'+column.name" v-model="column.max_length" max="100">
										<label :for="'txtmax_length'+column.name">Max Length</label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- Length -->
					<!-- PREFIX -->
					<div class="row" :class="column.use_prefix_suffix?'':'mb-3'" v-if="column.form_type !== 'fileupload'">
						<label for="" class="col-form-label col-2 text-end" class="form-label">Prefix/Suffix</label>
						<div class="col-10 py-2">
							<input type="checkbox" class="form-check-input" v-model="column.use_prefix_suffix" :id="'prefsuff'+column.name">
						</div>
					</div>
					<div class="row mb-3" v-if="column.use_prefix_suffix && column.form_type !== 'fileupload'">
						<div class="col-2">&nbsp;</div>
						<div class="col-2">
							<div class="form-floating">
								<input type="text" class="form-control" v-model="column.prefix" :id="'txtprefix'+column.name">
								<label :for="'txtprefix'+column.name">Prefix</label>
							</div>
						</div>
						<div class="col-2">
							<div class="form-floating">
								<input type="text" class="form-control" v-model="column.suffix" :id="'txtsuffix'+column.name">
								<label :for="'txtsuffix'+column.name">Suffix</label>
							</div>
						</div>
					</div>
					<!-- PREFIX -->
					<!-- MASK -->
					<div class="row mb-3" v-if="column.form_type !== 'fileupload'">
						<label class="col-form-label col-2 text-end">Mask&nbsp;<a href="https://alpinejs.dev/plugins/mask#x-mask" target="_blank"><small>(HELP)</small></a></label>
						<div class="col-2">
							<input type="text" class="form-control" v-model="column.mask">
						</div>
					</div>
					<!-- MASK -->
					<!-- date formats -->
					<div class="row mb-3" v-if="column.form_type=='date'">
						<label class="col-form-label col-2 text-end">Date Formats</label>
						<div class="col-2">
							<div class="form-floating">
								<input type="text" class="form-control" :id="'date_display_fmt'+column.name" v-model="column.date_display_fmt">
								<label :for="'date_display_fmt'+column.name">Display Format</label>
							</div>
						</div>
						<div class="col-2">
							<div class="form-floating">
								<input type="text" class="form-control" :id="'date_return_fmt'+column.name" v-model="column.date_return_fmt">
								<label :for="'date_return_fmt'+column.name">Return Format</label>
							</div>
						</div>
					</div>
					<!-- date formats -->
					<div class="row mb-3" v-if="column.form_type=='fileupload'">
						<label class="col-form-label col-2 text-end">File Upload Options</label>
						<div class="col-10 py-2">
							<div class="form-check mb-2">
								<input class="form-check-input" type="checkbox" v-model="column.frm_multiple_uploads" :id="'multiple_uploads'+column.name">
								<label :for="'multiple_uploads'+column.name">Allow multiple file uploads</label>
							</div>
						</div>
					</div>
					<!-- date formats -->
					<!-- <div class="row mb-3">
						<label class="col-form-label col-2 text-end">Display in Row</label>
						<div class="col-2">
							<select class="form-select" v-model="column.display_in_row">
								<option value="0">Default</option>
								<template v-for="i in 25" :key="i">
									<option :value="i">Row #((i))</option>
								</template>
							</select>
						</div>
					</div> -->
				</div>
			</div>
		</template>
	</div>
</div>
